<?php
namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\{Kuesioner, Dokter, Perawat};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManagementController extends Controller
{
    public function index()
    {
        return view('dashboard.management.index', [
            'stats'         => [
                'total_kuesioner' => Kuesioner::count(),
                'total_komplain'  => Kuesioner::whereHasComplain()->count(),
                'total_dokter'    => Dokter::count(),
                'total_perawat'   => Perawat::count(),
            ],
            'chartKlinik'   => AdminController::chartData('klinik'),
            'chartDokter'   => AdminController::chartData('dokter'),
            'chartPerawat'  => AdminController::chartData('perawat'),
            'ratingDokter'  => $this->topRatings('dokter'),
            'ratingPerawat' => $this->topRatings('perawat'),
        ]);
    }

    // Penilaian Tenaga Kesehatan (chart + rating list)
    public function penilaianNakes(Request $request)
    {
        $dokterList  = Dokter::orderBy('nama')->get();
        $perawatList = Perawat::orderBy('nama')->get();

        return view('dashboard.management.penilaian-nakes', [
            'dokterList'    => $dokterList,
            'perawatList'   => $perawatList,
            'ratingDokter'  => $this->topRatings('dokter', 999),
            'ratingPerawat' => $this->topRatings('perawat', 999),
            // chart data semua (dipakai JS via chartApiNakes)
            'chartSemuaDokter'  => $this->chartSemuaNakes('dokter'),
            'chartSemuaPerawat' => $this->chartSemuaNakes('perawat'),
        ]);
    }

    // API: chart untuk 1 nakes (distribusi Baik/Cukup/Kurang)
    public function chartNakesApi(Request $request, string $tipe, int $id)
    {
        $data = $this->chartDistribusiNakes($tipe, $id);
        return response()->json($data);
    }

    // Data Kuesioner (read-only)
    public function kuesionerList()
    {
        $list = Kuesioner::with(['klinik','dokterRel.dokter','perawatRel.perawat'])
            ->latest()->paginate(20);
        return view('dashboard.management.kuesioner-list', compact('list'));
    }

    public function komplain(Request $request)
    {
        $komplain = Kuesioner::whereHasComplain()
            ->when($request->search, fn($q,$s) =>
                $q->where('nama','like',"%$s%")->orWhere('komplain','like',"%$s%"))
            ->latest()->paginate(20)->withQueryString();
        return view('dashboard.management.komplain', compact('komplain'));
    }

    public function kritikSaran(Request $request)
    {
        $tipe     = $request->get('tipe','dokter');
        $nakesId  = $request->get('nakes_id');
        $search   = $request->get('search');

        if ($tipe === 'dokter') {
            $nakesList = Dokter::orderBy('nama')->get();
            $query = DB::table('kuesioner_dokters as kd')
                ->join('dokters as d','d.id','=','kd.dokter_id')
                ->join('kuesioners as k','k.id','=','kd.kuesioner_id')
                ->select('kd.id','d.id as nakes_id','d.nama as nakes_nama',
                         'd.spesialisasi','kd.kritik_saran','k.nama as pasien_nama','k.created_at')
                ->whereNotNull('kd.kritik_saran')->where('kd.kritik_saran','!=','');
        } else {
            $tipe = 'perawat';
            $nakesList = Perawat::orderBy('nama')->get();
            $query = DB::table('kuesioner_perawats as kp')
                ->join('perawats as p','p.id','=','kp.perawat_id')
                ->join('kuesioners as k','k.id','=','kp.kuesioner_id')
                ->select('kp.id','p.id as nakes_id','p.nama as nakes_nama',
                         DB::raw('NULL as spesialisasi'),'kp.kritik_saran','k.nama as pasien_nama','k.created_at')
                ->whereNotNull('kp.kritik_saran')->where('kp.kritik_saran','!=','');
        }

        if ($nakesId) {
            $col = $tipe==='dokter' ? 'kd.dokter_id' : 'kp.perawat_id';
            $query->where($col,$nakesId);
        }
        if ($search) {
            $query->where(function($q) use($search,$tipe) {
                $col = $tipe==='dokter' ? 'kd.kritik_saran' : 'kp.kritik_saran';
                $q->where($col,'like',"%$search%")->orWhere('k.nama','like',"%$search%");
            });
        }

        $kritik  = $query->orderByDesc('k.created_at')->paginate(20)->withQueryString();
        $summary = $this->kritikSummary($tipe);

        return view('dashboard.management.kritik-saran', compact('kritik','tipe','nakesList','nakesId','search','summary'));
    }

    public function chartApi(string $type)
    {
        return response()->json(AdminController::chartData($type));
    }

    private function topRatings(string $type, int $limit = 5): \Illuminate\Support\Collection
    {
        if ($type === 'dokter') {
            return DB::table('kuesioner_dokters as kd')
                ->join('dokters as d','d.id','=','kd.dokter_id')
                ->selectRaw('d.id, d.nama, d.spesialisasi, COUNT(kd.id) as total,
                    ROUND(AVG((kd.q1+kd.q2+kd.q3+kd.q4+kd.q5+kd.q6+kd.q7+kd.q8+kd.q9+kd.q10+kd.q11+kd.q12+kd.q13+kd.q14+kd.q15)/15.0),2) as rata_rata')
                ->groupBy('d.id','d.nama','d.spesialisasi')
                ->orderByDesc('rata_rata')->limit($limit)->get();
        }
        return DB::table('kuesioner_perawats as kp')
            ->join('perawats as p','p.id','=','kp.perawat_id')
            ->selectRaw('p.id, p.nama, COUNT(kp.id) as total,
                ROUND(AVG((kp.q1+kp.q2+kp.q3+kp.q4+kp.q5+kp.q6+kp.q7+kp.q8+kp.q9+kp.q10+kp.q11+kp.q12+kp.q13+kp.q14+kp.q15)/15.0),2) as rata_rata')
            ->groupBy('p.id','p.nama')
            ->orderByDesc('rata_rata')->limit($limit)->get();
    }

    // Chart rata-rata semua nakes (bar per individu)
    private function chartSemuaNakes(string $type): array
    {
        if ($type === 'dokter') {
            $rows = DB::table('kuesioner_dokters as kd')
                ->join('dokters as d','d.id','=','kd.dokter_id')
                ->selectRaw('d.nama,
                    ROUND(AVG((kd.q1+kd.q2+kd.q3+kd.q4+kd.q5+kd.q6+kd.q7+kd.q8+kd.q9+kd.q10+kd.q11+kd.q12+kd.q13+kd.q14+kd.q15)/15.0),2) as rata_rata')
                ->groupBy('d.id','d.nama')
                ->orderByDesc('rata_rata')->get();
        } else {
            $rows = DB::table('kuesioner_perawats as kp')
                ->join('perawats as p','p.id','=','kp.perawat_id')
                ->selectRaw('p.nama,
                    ROUND(AVG((kp.q1+kp.q2+kp.q3+kp.q4+kp.q5+kp.q6+kp.q7+kp.q8+kp.q9+kp.q10+kp.q11+kp.q12+kp.q13+kp.q14+kp.q15)/15.0),2) as rata_rata')
                ->groupBy('p.id','p.nama')
                ->orderByDesc('rata_rata')->get();
        }

        return [
            'type'   => 'semua',
            'labels' => $rows->pluck('nama')->toArray(),
            'data'   => $rows->pluck('rata_rata')->map(fn($v) => (float)$v)->toArray(),
        ];
    }

    // Chart distribusi Baik/Cukup/Kurang untuk 1 nakes
    private function chartDistribusiNakes(string $tipe, int $id): array
    {
        $avg = 'ROUND((q1+q2+q3+q4+q5+q6+q7+q8+q9+q10+q11+q12+q13+q14+q15)/15.0,2)';
        if ($tipe === 'dokter') {
            $rows = DB::table('kuesioner_dokters')->where('dokter_id',$id)
                ->selectRaw("$avg as avg_val")->get();
        } else {
            $rows = DB::table('kuesioner_perawats')->where('perawat_id',$id)
                ->selectRaw("$avg as avg_val")->get();
        }

        $baik = $cukup = $kurang = 0;
        foreach ($rows as $r) {
            $v = (float)$r->avg_val;
            if ($v >= 4)      $baik++;
            elseif ($v >= 3)  $cukup++;
            else               $kurang++;
        }

        return [
            'type'   => 'individu',
            'labels' => ['Baik (4–5 ⭐)', 'Cukup (3 ⭐)', 'Kurang (1–2 ⭐)'],
            'data'   => [$baik, $cukup, $kurang],
            'total'  => $baik + $cukup + $kurang,
        ];
    }

    private function kritikSummary(string $tipe): \Illuminate\Support\Collection
    {
        if ($tipe === 'dokter') {
            return DB::table('kuesioner_dokters as kd')
                ->join('dokters as d','d.id','=','kd.dokter_id')
                ->selectRaw('d.id, d.nama, d.spesialisasi, COUNT(kd.kritik_saran) as total')
                ->whereNotNull('kd.kritik_saran')->where('kd.kritik_saran','!=','')
                ->groupBy('d.id','d.nama','d.spesialisasi')->orderByDesc('total')->get();
        }
        return DB::table('kuesioner_perawats as kp')
            ->join('perawats as p','p.id','=','kp.perawat_id')
            ->selectRaw('p.id, p.nama, COUNT(kp.kritik_saran) as total')
            ->whereNotNull('kp.kritik_saran')->where('kp.kritik_saran','!=','')
            ->groupBy('p.id','p.nama')->orderByDesc('total')->get();
    }
}
