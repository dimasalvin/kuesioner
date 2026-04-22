<?php
namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\{User, Dokter, Perawat, Kuesioner, JawabanKuesioner};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Hash, DB};
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function index()
    {
        return view('dashboard.admin.index', [
            'stats'        => $this->getStats(),
            'chartKlinik'  => JawabanKuesioner::distribusi('klinik'),
            'chartDokter'  => JawabanKuesioner::distribusi('dokter'),
            'chartPerawat' => JawabanKuesioner::distribusi('perawat'),
            'komplain'     => Kuesioner::whereHasComplain()->latest()->take(5)->get(),
        ]);
    }

    // ── User Management ───────────────────────────────────────────────
    public function users(Request $request)
    {
        $users = User::query()
            ->when($request->role, fn($q,$r) => $q->where('role',$r))
            ->when($request->search, fn($q,$s) => $q->where('name','like',"%$s%")->orWhere('email','like',"%$s%"))
            ->orderBy('role')->orderBy('name')
            ->paginate(15)->withQueryString();
        return view('dashboard.admin.users', compact('users'));
    }

    public function createUser()
    {
        return view('dashboard.admin.user-form', [
            'user'=>null, 'dokters'=>Dokter::orderBy('nama')->get(), 'perawats'=>Perawat::orderBy('nama')->get(),
        ]);
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name'=>'required|string|max:255', 'email'=>'required|email|unique:users',
            'password'=>'required|min:8|confirmed',
            'role'=>'required|in:administrator,management,user',
            'tipe_nakes'=>'required_if:role,user|nullable|in:dokter,perawat',
            'nakes_id'=>'required_if:tipe_nakes,dokter,perawat|nullable|integer',
        ]);
        User::create([...$data, 'password'=>Hash::make($data['password']), 'aktif'=>$request->boolean('aktif',true)]);
        return redirect()->route('dashboard.admin.users')->with('success','User berhasil ditambahkan.');
    }

    public function editUser(User $user)
    {
        return view('dashboard.admin.user-form', [
            'user'=>$user, 'dokters'=>Dokter::orderBy('nama')->get(), 'perawats'=>Perawat::orderBy('nama')->get(),
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
            'nakes_id'=>'required_if:tipe_nakes,dokter,perawat|nullable|integer',
        ]);
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
            ->when($request->search, fn($q,$s) => $q->where('nama','like',"%$s%")->orWhere('komplain','like',"%$s%"))
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
                ->select('kd.id','d.id as nakes_id','d.nama as nakes_nama','d.spesialisasi','kd.kritik_saran','k.nama as pasien_nama','k.created_at')
                ->whereNotNull('kd.kritik_saran')->where('kd.kritik_saran','!=','');
        } else {
            $tipe = 'perawat';
            $nakesList = Perawat::orderBy('nama')->get();
            $query = DB::table('kuesioner_perawats as kp')
                ->join('perawats as p','p.id','=','kp.perawat_id')
                ->join('kuesioners as k','k.id','=','kp.kuesioner_id')
                ->select('kp.id','p.id as nakes_id','p.nama as nakes_nama',DB::raw('NULL as spesialisasi'),'kp.kritik_saran','k.nama as pasien_nama','k.created_at')
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
        return [
            'total_kuesioner' => Kuesioner::count(),
            'total_komplain'  => Kuesioner::whereHasComplain()->count(),
            'total_dokter'    => Dokter::count(),
            'total_perawat'   => Perawat::count(),
            'total_user'      => User::count(),
        ];
    }

    private function kritikSummary(string $tipe): \Illuminate\Support\Collection
    {
        if ($tipe === 'dokter') {
            return DB::table('kuesioner_dokters as kd')->join('dokters as d','d.id','=','kd.dokter_id')
                ->selectRaw('d.id, d.nama, d.spesialisasi, COUNT(kd.kritik_saran) as total')
                ->whereNotNull('kd.kritik_saran')->where('kd.kritik_saran','!=','')
                ->groupBy('d.id','d.nama','d.spesialisasi')->orderByDesc('total')->get();
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
