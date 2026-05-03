<?php
namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\{Kuesioner, Dokter, Perawat};
use App\Services\KuesionerStatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Cache, DB};

class ManagementController extends Controller
{
    public function index()
    {
        // Cache heavy aggregation queries — 60 detik
        $stats = Cache::remember('dashboard:stats:mgmt', 60, function () {
            $row = DB::selectOne("
                SELECT
                    (SELECT COUNT(*) FROM kuesioners) as total_kuesioner,
                    (SELECT COUNT(*) FROM kuesioners WHERE has_complain = 1 AND komplain IS NOT NULL) as total_komplain,
                    (SELECT COUNT(*) FROM dokters) as total_dokter,
                    (SELECT COUNT(*) FROM perawats) as total_perawat
            ");
            return [
                'total_kuesioner' => (int) $row->total_kuesioner,
                'total_komplain'  => (int) $row->total_komplain,
                'total_dokter'    => (int) $row->total_dokter,
                'total_perawat'   => (int) $row->total_perawat,
            ];
        });

        $distribusi = Cache::remember('dashboard:distribusi', 60, function () {
            return KuesionerStatsService::distribusiMulti(['klinik', 'dokter', 'perawat']);
        });

        $ratingDokter = Cache::remember('dashboard:rating:dokter:5', 60, function () {
            return $this->topRatings('dokter');
        });

        $ratingPerawat = Cache::remember('dashboard:rating:perawat:5', 60, function () {
            return $this->topRatings('perawat');
        });

        return view('dashboard.management.index', [
            'stats'         => $stats,
            'chartKlinik'   => $distribusi['klinik'],
            'chartDokter'   => $distribusi['dokter'],
            'chartPerawat'  => $distribusi['perawat'],
            'ratingDokter'  => $ratingDokter,
            'ratingPerawat' => $ratingPerawat,
        ]);
    }

    // Penilaian Tenaga Kesehatan (chart + rating list)
    public function penilaianNakes(Request $request)
    {
        // Cache daftar nakes (jarang berubah)
        $dokterList = Cache::remember('nakes:dokter:list', 300, function () {
            return Dokter::orderBy('nama')->get(['id', 'nama']);
        });
        $perawatList = Cache::remember('nakes:perawat:list', 300, function () {
            return Perawat::orderBy('nama')->get(['id', 'nama']);
        });

        // Cache rating (heavy AVG query)
        $ratingDokter = Cache::remember('dashboard:rating:dokter:all', 120, function () {
            return $this->topRatings('dokter', 999);
        });
        $ratingPerawat = Cache::remember('dashboard:rating:perawat:all', 120, function () {
            return $this->topRatings('perawat', 999);
        });

        // Cache chart data
        $chartSemuaDokter = Cache::remember('dashboard:chart:semua:dokter', 120, function () {
            return $this->chartSemuaNakes('dokter');
        });
        $chartSemuaPerawat = Cache::remember('dashboard:chart:semua:perawat', 120, function () {
            return $this->chartSemuaNakes('perawat');
        });

        return view('dashboard.management.penilaian-nakes', [
            'dokterList'        => $dokterList,
            'perawatList'       => $perawatList,
            'ratingDokter'      => $ratingDokter,
            'ratingPerawat'     => $ratingPerawat,
            'chartSemuaDokter'  => $chartSemuaDokter,
            'chartSemuaPerawat' => $chartSemuaPerawat,
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
            ->when($request->search, fn($q,$s) => $q->where(function($sub) use($s) {
                $sub->where('nama','like',"%$s%")->orWhere('komplain','like',"%$s%");
            }))
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
                         'kd.kritik_saran','k.nama as pasien_nama','k.created_at')
                ->whereNotNull('kd.kritik_saran')->where('kd.kritik_saran','!=','');
        } else {
            $tipe = 'perawat';
            $nakesList = Perawat::orderBy('nama')->get();
            $query = DB::table('kuesioner_perawats as kp')
                ->join('perawats as p','p.id','=','kp.perawat_id')
                ->join('kuesioners as k','k.id','=','kp.kuesioner_id')
                ->select('kp.id','p.id as nakes_id','p.nama as nakes_nama',
                         'kp.kritik_saran','k.nama as pasien_nama','k.created_at')
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
                ->selectRaw('d.id, d.nama, COUNT(kd.id) as total,
                    ROUND(AVG(kd.rata_rata), 2) as rata_rata')
                ->groupBy('d.id','d.nama')
                ->orderByDesc('rata_rata')->limit($limit)->get();
        }
        return DB::table('kuesioner_perawats as kp')
            ->join('perawats as p','p.id','=','kp.perawat_id')
            ->selectRaw('p.id, p.nama, COUNT(kp.id) as total,
                ROUND(AVG(kp.rata_rata), 2) as rata_rata')
            ->groupBy('p.id','p.nama')
            ->orderByDesc('rata_rata')->limit($limit)->get();
    }

    // Chart rata-rata semua nakes (bar per individu)
    private function chartSemuaNakes(string $type): array
    {
        if ($type === 'dokter') {
            $rows = DB::table('kuesioner_dokters as kd')
                ->join('dokters as d','d.id','=','kd.dokter_id')
                ->selectRaw('d.nama, ROUND(AVG(kd.rata_rata), 2) as rata_rata')
                ->groupBy('d.id','d.nama')
                ->orderByDesc('rata_rata')->get();
        } else {
            $rows = DB::table('kuesioner_perawats as kp')
                ->join('perawats as p','p.id','=','kp.perawat_id')
                ->selectRaw('p.nama, ROUND(AVG(kp.rata_rata), 2) as rata_rata')
                ->groupBy('p.id','p.nama')
                ->orderByDesc('rata_rata')->get();
        }

        return [
            'type'   => 'semua',
            'labels' => $rows->pluck('nama')->toArray(),
            'data'   => $rows->pluck('rata_rata')->map(fn($v) => (float)$v)->toArray(),
        ];
    }

    // Chart distribusi Baik/Cukup/Kurang untuk 1 nakes — pakai kolom rata_rata
    private function chartDistribusiNakes(string $tipe, int $id): array
    {
        $table = $tipe === 'dokter' ? 'kuesioner_dokters' : 'kuesioner_perawats';
        $col   = $tipe === 'dokter' ? 'dokter_id' : 'perawat_id';

        $result = DB::table($table)->where($col, $id)
            ->selectRaw("
                SUM(CASE WHEN rata_rata >= 3.5 THEN 1 ELSE 0 END) as baik,
                SUM(CASE WHEN rata_rata >= 2.5 AND rata_rata < 3.5 THEN 1 ELSE 0 END) as cukup,
                SUM(CASE WHEN rata_rata < 2.5 THEN 1 ELSE 0 END) as kurang
            ")
            ->first();

        $baik   = (int) ($result->baik ?? 0);
        $cukup  = (int) ($result->cukup ?? 0);
        $kurang = (int) ($result->kurang ?? 0);

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
                ->selectRaw('d.id, d.nama, COUNT(kd.kritik_saran) as total')
                ->whereNotNull('kd.kritik_saran')->where('kd.kritik_saran','!=','')
                ->groupBy('d.id','d.nama')->orderByDesc('total')->get();
        }
        return DB::table('kuesioner_perawats as kp')
            ->join('perawats as p','p.id','=','kp.perawat_id')
            ->selectRaw('p.id, p.nama, COUNT(kp.kritik_saran) as total')
            ->whereNotNull('kp.kritik_saran')->where('kp.kritik_saran','!=','')
            ->groupBy('p.id','p.nama')->orderByDesc('total')->get();
    }
}
