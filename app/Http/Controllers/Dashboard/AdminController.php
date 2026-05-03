<?php
namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\{User, Dokter, Perawat, Kuesioner, JawabanKuesioner};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Cache, Hash, DB};
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function index()
    {
        // Cache distribusi 60 detik — query aggregation berat, tidak perlu realtime
        $distribusi = Cache::remember('dashboard:distribusi', 60, function () {
            return JawabanKuesioner::distribusiMulti(['klinik', 'dokter', 'perawat']);
        });

        $stats = Cache::remember('dashboard:stats', 60, function () {
            return $this->getStats();
        });

        return view('dashboard.admin.index', [
            'stats'        => $stats,
            'chartKlinik'  => $distribusi['klinik'],
            'chartDokter'  => $distribusi['dokter'],
            'chartPerawat' => $distribusi['perawat'],
            'komplain'     => Kuesioner::whereHasComplain()->latest()->take(5)->get(),
        ]);
    }

    // ── User Management ───────────────────────────────────────────────
    public function users(Request $request)
    {
        $users = User::query()
            ->when($request->role, fn($q,$r) => $q->where('role',$r))
            ->when($request->search, fn($q,$s) => $q->where(function($sub) use($s) {
                $sub->where('name','like',"%$s%")->orWhere('email','like',"%$s%");
            }))
            ->orderBy('role')->orderBy('name')
            ->paginate(15)->withQueryString();
        return view('dashboard.admin.users', compact('users'));
    }

    public function createUser()
    {
        return view('dashboard.admin.user-form', [
            'user'=>null,
        ]);
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name'=>'required|string|max:255', 'email'=>'required|email|unique:users',
            'password'=>'required|min:8|confirmed',
            'role'=>'required|in:administrator,management,user',
            'tipe_nakes'=>'required_if:role,user|nullable|in:dokter,perawat',
        ]);
        $data['nakes_id'] = null;
        User::create([...$data, 'password'=>Hash::make($data['password']), 'aktif'=>$request->boolean('aktif',true)]);
        return redirect()->route('dashboard.admin.users')->with('success','User berhasil ditambahkan.');
    }

    public function editUser(User $user)
    {
        return view('dashboard.admin.user-form', [
            'user'=>$user,
        ]);
    }

    public function updateUser(Request $request, User $user)
    {
        $data = $request->validate([
            'name'=>'required|string|max:255',
            'email'=>['required','email',Rule::unique('users')->ignore($user->id)],
            'password'=>'nullable|min:8|confirmed',
            'role'=>'required|in:administrator,management,user',
            'tipe_nakes'=>'required_if:role,user|nullable|in:dokter,perawat',
        ]);
        $data['nakes_id'] = null;
        if (!empty($data['password'])) $data['password'] = Hash::make($data['password']);
        else unset($data['password']);
        $data['aktif'] = $request->boolean('aktif');
        $user->update($data);
        return redirect()->route('dashboard.admin.users')->with('success','User berhasil diperbarui.');
    }

    public function destroyUser(User $user)
    {
        if ($user->id === auth()->id()) return back()->withErrors(['error'=>'Tidak bisa menghapus akun sendiri.']);
        $user->delete();
        return redirect()->route('dashboard.admin.users')->with('success','User dihapus.');
    }

    // ── Kuesioner ─────────────────────────────────────────────────────
    public function kuesionerList()
    {
        $list = Kuesioner::with(['klinik','dokterRel.dokter','perawatRel.perawat'])->latest()->paginate(20);
        return view('dashboard.admin.kuesioner-list', compact('list'));
    }

    public function destroyKuesioner(Kuesioner $kuesioner)
    {
        $kuesioner->delete();
        return back()->with('success','Data kuesioner dihapus.');
    }

    // ── Komplain ──────────────────────────────────────────────────────
    public function komplain(Request $request)
    {
        $komplain = Kuesioner::whereHasComplain()
            ->when($request->search, fn($q,$s) => $q->where(function($sub) use($s) {
                $sub->where('nama','like',"%$s%")->orWhere('komplain','like',"%$s%");
            }))
            ->latest()->paginate(20)->withQueryString();
        return view('dashboard.admin.komplain', compact('komplain'));
    }

    // ── Kritik & Saran ────────────────────────────────────────────────
    public function kritikSaran(Request $request)
    {
        $tipe    = $request->get('tipe','dokter');
        $nakesId = $request->get('nakes_id');
        $search  = $request->get('search');

        if ($tipe === 'dokter') {
            $nakesList = Dokter::orderBy('nama')->get();
            $query = DB::table('kuesioner_dokters as kd')
                ->join('dokters as d','d.id','=','kd.dokter_id')
                ->join('kuesioners as k','k.id','=','kd.kuesioner_id')
                ->select('kd.id','d.id as nakes_id','d.nama as nakes_nama','kd.kritik_saran','k.nama as pasien_nama','k.created_at')
                ->whereNotNull('kd.kritik_saran')->where('kd.kritik_saran','!=','');
        } else {
            $tipe = 'perawat';
            $nakesList = Perawat::orderBy('nama')->get();
            $query = DB::table('kuesioner_perawats as kp')
                ->join('perawats as p','p.id','=','kp.perawat_id')
                ->join('kuesioners as k','k.id','=','kp.kuesioner_id')
                ->select('kp.id','p.id as nakes_id','p.nama as nakes_nama','kp.kritik_saran','k.nama as pasien_nama','k.created_at')
                ->whereNotNull('kp.kritik_saran')->where('kp.kritik_saran','!=','');
        }
        if ($nakesId) $query->where($tipe==='dokter'?'kd.dokter_id':'kp.perawat_id', $nakesId);
        if ($search)  $query->where(fn($q) => $q->where($tipe==='dokter'?'kd.kritik_saran':'kp.kritik_saran','like',"%$search%")->orWhere('k.nama','like',"%$search%"));

        $kritik  = $query->orderByDesc('k.created_at')->paginate(20)->withQueryString();
        $summary = $this->kritikSummary($tipe);
        return view('dashboard.admin.kritik-saran', compact('kritik','tipe','nakesList','nakesId','search','summary'));
    }

    public function chartApi(string $type)
    {
        return response()->json(JawabanKuesioner::distribusi($type));
    }

    // ── Helpers ───────────────────────────────────────────────────────
    private function getStats(): array
    {
        // 1 query gabungan menggantikan 5 query terpisah
        $row = DB::selectOne("
            SELECT
                (SELECT COUNT(*) FROM kuesioners) as total_kuesioner,
                (SELECT COUNT(*) FROM kuesioners WHERE has_complain = 1 AND komplain IS NOT NULL) as total_komplain,
                (SELECT COUNT(*) FROM dokters) as total_dokter,
                (SELECT COUNT(*) FROM perawats) as total_perawat,
                (SELECT COUNT(*) FROM users) as total_user
        ");

        return [
            'total_kuesioner' => (int) $row->total_kuesioner,
            'total_komplain'  => (int) $row->total_komplain,
            'total_dokter'    => (int) $row->total_dokter,
            'total_perawat'   => (int) $row->total_perawat,
            'total_user'      => (int) $row->total_user,
        ];
    }

    private function kritikSummary(string $tipe): \Illuminate\Support\Collection
    {
        if ($tipe === 'dokter') {
            return DB::table('kuesioner_dokters as kd')->join('dokters as d','d.id','=','kd.dokter_id')
                ->selectRaw('d.id, d.nama, COUNT(kd.kritik_saran) as total')
                ->whereNotNull('kd.kritik_saran')->where('kd.kritik_saran','!=','')
                ->groupBy('d.id','d.nama')->orderByDesc('total')->get();
        }
        return DB::table('kuesioner_perawats as kp')->join('perawats as p','p.id','=','kp.perawat_id')
            ->selectRaw('p.id, p.nama, COUNT(kp.kritik_saran) as total')
            ->whereNotNull('kp.kritik_saran')->where('kp.kritik_saran','!=','')
            ->groupBy('p.id','p.nama')->orderByDesc('total')->get();
    }

    // ── Static: dipakai ManagementController ─────────────────────────
    public static function chartData(string $type): array
    {
        return JawabanKuesioner::distribusi($type);
    }
}
