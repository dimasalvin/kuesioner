<?php
namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\{Dokter, Perawat, Kuesioner, PertanyaanKuesioner};
use App\Services\KuesionerStatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DetailPenilaianController extends Controller
{
    // ── Index: router utama ───────────────────────────────────────────
    public function index(Request $request)
    {
        $user = auth()->user();
        $tipe = $request->get('tipe', 'klinik');

        if ($user->isUser()) {
            return $tipe === 'klinik'
                ? $this->klinikList(true)
                : $this->indexNakes($request, $user);
        }

        if ($tipe === 'klinik') return $this->klinikList(false);

        if (!in_array($tipe, ['dokter','perawat'])) $tipe = 'dokter';

        // Grid card per nakes — query langsung dari tabel kuesioner_*
        if ($tipe === 'dokter') {
            $list = DB::table('dokters as d')
                ->leftJoin('kuesioner_dokters as kd', 'd.id', '=', 'kd.dokter_id')
                ->selectRaw('d.id, d.nama,
                    COUNT(kd.id) as total,
                    ROUND(AVG(kd.rata_rata), 2) as rata_rata')
                ->groupBy('d.id', 'd.nama')
                ->orderBy('d.nama')->get();
        } else {
            $list = DB::table('perawats as p')
                ->leftJoin('kuesioner_perawats as kp', 'p.id', '=', 'kp.perawat_id')
                ->selectRaw('p.id, p.nama,
                    COUNT(kp.id) as total,
                    ROUND(AVG(kp.rata_rata), 2) as rata_rata')
                ->groupBy('p.id', 'p.nama')
                ->orderBy('p.nama')->get();
        }

        return view('dashboard.shared.detail-penilaian-index', compact('list','tipe'));
    }

    // ── Klinik: list semua kuesioner ─────────────────────────────────
    private function klinikList(bool $userMode = false)
    {
        $rows = DB::table('kuesioners as k')
            ->join('kuesioner_kliniks as kk', 'k.id', '=', 'kk.kuesioner_id')
            ->select('k.id', 'k.nama as pasien_nama', 'k.no_telp', 'k.created_at', 'kk.rata_rata')
            ->orderByDesc('k.id')
            ->paginate(20);

        $tipe = 'klinik';
        return view('dashboard.shared.detail-penilaian-klinik-list', compact('rows','tipe','userMode'));
    }

    // ── Klinik: detail satu kuesioner ────────────────────────────────
    public function showKlinik(int $id)
    {
        $kuesioner = Kuesioner::findOrFail($id);

        $pertanyaan = PertanyaanKuesioner::where('kategori','klinik')
            ->orderBy('urutan')->get();

        // Ambil jawaban dari JSON column
        $jawaban = KuesionerStatsService::jawabanDetail($id, 'klinik');

        return view('dashboard.shared.detail-penilaian-klinik-show', compact('kuesioner','pertanyaan','jawaban'));
    }

    // ── Klinik chart: untuk halaman Penilaian Klinik nakes ───────────
    public function klinik()
    {
        $chart      = KuesionerStatsService::distribusi('klinik');
        $pertanyaan = PertanyaanKuesioner::aktif()->kategori('klinik')->get();
        $perQ       = KuesionerStatsService::rataPerPertanyaan('klinik');

        $summary  = KuesionerStatsService::summary('klinik');
        $total    = (int) ($summary->total ?? 0);
        $avgTotal = round((float) ($summary->rata_rata ?? 0), 2);

        return view('dashboard.shared.detail-penilaian-klinik-nakes', compact('chart','pertanyaan','perQ','total','avgTotal'));
    }

    // ── Nakes: list kuesioner milik sendiri ───────────────────────────
    private function indexNakes(Request $request, $user)
    {
        $nakes    = $user->isDokter() ? $user->dokter : $user->perawat;
        $kategori = $user->isDokter() ? 'dokter' : 'perawat';
        $table    = $kategori === 'dokter' ? 'kuesioner_dokters' : 'kuesioner_perawats';
        $nakesCol = $kategori === 'dokter' ? 'dokter_id' : 'perawat_id';

        $rows = DB::table('kuesioners as k')
            ->join("{$table} as kt", function($join) use($nakesCol, $nakes) {
                $join->on('k.id', '=', 'kt.kuesioner_id')
                     ->where("kt.{$nakesCol}", '=', $nakes->id);
            })
            ->select('k.id', 'k.nama as pasien_nama', 'k.no_telp', 'k.created_at', 'kt.rata_rata')
            ->orderByDesc('k.id')
            ->paginate(15);

        $tipe = $kategori;
        return view('dashboard.shared.detail-penilaian-nakes', compact('rows','nakes','tipe'));
    }

    // ── Admin/Management: drill down ke satu nakes ────────────────────
    public function byNakes(Request $request, int $id)
    {
        $tipe = $request->get('tipe','dokter');
        $user = auth()->user();

        if ($user->isUser()) {
            $myId = $user->isDokter() ? $user->dokter?->id : $user->perawat?->id;
            if ($myId != $id) abort(403);
        }

        $nakes    = $tipe === 'dokter' ? Dokter::findOrFail($id) : Perawat::findOrFail($id);
        $table    = $tipe === 'dokter' ? 'kuesioner_dokters' : 'kuesioner_perawats';
        $nakesCol = $tipe === 'dokter' ? 'dokter_id' : 'perawat_id';

        $rows = DB::table('kuesioners as k')
            ->join("{$table} as kt", function($join) use($nakesCol, $id) {
                $join->on('k.id', '=', 'kt.kuesioner_id')
                     ->where("kt.{$nakesCol}", '=', $id);
            })
            ->select('k.id', 'k.nama as pasien_nama', 'k.no_telp', 'k.created_at', 'kt.rata_rata')
            ->orderByDesc('k.id')
            ->paginate(15);

        return view('dashboard.shared.detail-penilaian-list', compact('rows','nakes','tipe'));
    }

    // ── Detail satu kuesioner nakes ───────────────────────────────────
    public function show(Request $request, int $id)
    {
        $tipe = $request->get('tipe','dokter');
        $user = auth()->user();

        $kuesioner = Kuesioner::findOrFail($id);

        // Ambil data nakes dari tabel kuesioner_dokters/perawats
        if ($tipe === 'dokter') {
            $nakesRow = DB::table('kuesioner_dokters as kd')
                ->join('dokters as d', 'd.id', '=', 'kd.dokter_id')
                ->where('kd.kuesioner_id', $id)
                ->select('d.nama as nakes_nama', 'kd.dokter_id as nakes_id', 'kd.kritik_saran')
                ->first();
        } else {
            $nakesRow = DB::table('kuesioner_perawats as kp')
                ->join('perawats as p', 'p.id', '=', 'kp.perawat_id')
                ->where('kp.kuesioner_id', $id)
                ->select('p.nama as nakes_nama', 'kp.perawat_id as nakes_id', 'kp.kritik_saran')
                ->first();
        }

        if (!$nakesRow) abort(404);

        // Validasi akses nakes
        if ($user->isUser()) {
            $myNakesId = $user->isDokter() ? $user->dokter?->id : $user->perawat?->id;
            if ($myNakesId != $nakesRow->nakes_id) abort(403);
        }

        $kritikRow = $nakesRow->kritik_saran;

        // Pertanyaan aktif + jawaban dari JSON
        $pertanyaan = PertanyaanKuesioner::where('kategori', $tipe)
            ->where('aktif', true)->orderBy('urutan')->get();

        $jawaban = KuesionerStatsService::jawabanDetail($id, $tipe);

        return view('dashboard.shared.detail-penilaian-show', compact(
            'kuesioner','tipe','pertanyaan','jawaban','nakesRow','kritikRow'
        ));
    }
}
