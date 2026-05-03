<?php
namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\{Dokter, Perawat, Kuesioner, JawabanKuesioner, PertanyaanKuesioner};
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

        // Grid card per nakes
        if ($tipe === 'dokter') {
            $list = DB::table('dokters as d')
                ->leftJoin('jawaban_kuesioner as j', function($join) {
                    $join->on('d.id','=','j.nakes_id')->where('j.kategori','=','dokter');
                })
                ->selectRaw('d.id, d.nama,
                    COUNT(DISTINCT j.kuesioner_id) as total,
                    ROUND(AVG(j.nilai),2) as rata_rata')
                ->groupBy('d.id','d.nama')
                ->orderBy('d.nama')->get();
        } else {
            $list = DB::table('perawats as p')
                ->leftJoin('jawaban_kuesioner as j', function($join) {
                    $join->on('p.id','=','j.nakes_id')->where('j.kategori','=','perawat');
                })
                ->selectRaw('p.id, p.nama,
                    COUNT(DISTINCT j.kuesioner_id) as total,
                    ROUND(AVG(j.nilai),2) as rata_rata')
                ->groupBy('p.id','p.nama')
                ->orderBy('p.nama')->get();
        }

        return view('dashboard.shared.detail-penilaian-index', compact('list','tipe'));
    }

    // ── Klinik: list semua kuesioner ─────────────────────────────────
    private function klinikList(bool $userMode = false)
    {
        // Ambil kuesioner yang punya jawaban klinik, hitung rata-rata dari jawaban_kuesioner
        $rows = DB::table('kuesioners as k')
            ->join('jawaban_kuesioner as j', function($join) {
                $join->on('k.id','=','j.kuesioner_id')->where('j.kategori','=','klinik');
            })
            ->selectRaw('k.id, k.nama as pasien_nama, k.no_telp, k.created_at,
                ROUND(AVG(j.nilai),2) as rata_rata')
            ->groupBy('k.id','k.nama','k.no_telp','k.created_at')
            ->orderByDesc('k.id')
            ->paginate(20);

        $tipe = 'klinik';
        return view('dashboard.shared.detail-penilaian-klinik-list', compact('rows','tipe','userMode'));
    }

    // ── Klinik: detail satu kuesioner ────────────────────────────────
    public function showKlinik(int $id)
    {
        // $id = kuesioner_id (bukan kuesioner_kliniks.id lagi)
        $kuesioner = Kuesioner::findOrFail($id);

        $pertanyaan = PertanyaanKuesioner::where('kategori','klinik')
            ->orderBy('urutan')->get();

        // Ambil jawaban untuk kuesioner ini, key by pertanyaan_id
        $jawaban = JawabanKuesioner::where('kuesioner_id', $id)
            ->where('kategori','klinik')
            ->get()->keyBy('pertanyaan_id');

        return view('dashboard.shared.detail-penilaian-klinik-show', compact('kuesioner','pertanyaan','jawaban'));
    }

    // ── Klinik chart: untuk halaman Penilaian Klinik nakes ───────────
    public function klinik()
    {
        $chart      = JawabanKuesioner::distribusi('klinik');
        $pertanyaan = PertanyaanKuesioner::aktif()->kategori('klinik')->get();
        $perQ       = JawabanKuesioner::rataPerPertanyaan('klinik');

        // 1 query menggantikan 2 query terpisah (COUNT DISTINCT + AVG)
        $summary = DB::table('jawaban_kuesioner')
            ->where('kategori', 'klinik')
            ->selectRaw('COUNT(DISTINCT kuesioner_id) as total, ROUND(AVG(nilai), 2) as avg_total')
            ->first();

        $total    = (int) $summary->total;
        $avgTotal = round((float) ($summary->avg_total ?? 0), 2);

        return view('dashboard.shared.detail-penilaian-klinik-nakes', compact('chart','pertanyaan','perQ','total','avgTotal'));
    }

    // ── Nakes: list kuesioner milik sendiri ───────────────────────────
    private function indexNakes(Request $request, $user)
    {
        $nakes    = $user->isDokter() ? $user->dokter : $user->perawat;
        $kategori = $user->isDokter() ? 'dokter' : 'perawat';

        $rows = DB::table('kuesioners as k')
            ->join('jawaban_kuesioner as j', function($join) use($kategori, $nakes) {
                $join->on('k.id','=','j.kuesioner_id')
                     ->where('j.kategori','=',$kategori)
                     ->where('j.nakes_id','=',$nakes->id);
            })
            ->selectRaw('k.id, k.nama as pasien_nama, k.no_telp, k.created_at,
                ROUND(AVG(j.nilai),2) as rata_rata')
            ->groupBy('k.id','k.nama','k.no_telp','k.created_at')
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

        $nakes = $tipe === 'dokter' ? Dokter::findOrFail($id) : Perawat::findOrFail($id);

        $rows = DB::table('kuesioners as k')
            ->join('jawaban_kuesioner as j', function($join) use($tipe, $id) {
                $join->on('k.id','=','j.kuesioner_id')
                     ->where('j.kategori','=',$tipe)
                     ->where('j.nakes_id','=',$id);
            })
            ->selectRaw('k.id, k.nama as pasien_nama, k.no_telp, k.created_at,
                ROUND(AVG(j.nilai),2) as rata_rata')
            ->groupBy('k.id','k.nama','k.no_telp','k.created_at')
            ->orderByDesc('k.id')
            ->paginate(15);

        return view('dashboard.shared.detail-penilaian-list', compact('rows','nakes','tipe'));
    }

    // ── Detail satu kuesioner nakes ───────────────────────────────────
    public function show(Request $request, int $id)
    {
        // $id = kuesioners.id
        $tipe = $request->get('tipe','dokter');
        $user = auth()->user();

        $kuesioner = Kuesioner::findOrFail($id);

        // Validasi akses nakes
        if ($user->isUser()) {
            $myNakesId = $user->isDokter() ? $user->dokter?->id : $user->perawat?->id;
            $check = JawabanKuesioner::where('kuesioner_id',$id)
                ->where('kategori',$tipe)
                ->where('nakes_id',$myNakesId)
                ->exists();
            if (!$check) abort(403);
        }

        // Info nakes + kritik saran dalam 1 query (bukan 2 terpisah)
        if ($tipe === 'dokter') {
            $nakesRow = DB::table('jawaban_kuesioner as j')
                ->join('dokters as d','d.id','=','j.nakes_id')
                ->leftJoin('kuesioner_dokters as kd', function($join) use($id) {
                    $join->on('kd.dokter_id','=','j.nakes_id')
                         ->where('kd.kuesioner_id','=',$id);
                })
                ->where('j.kuesioner_id',$id)->where('j.kategori','dokter')
                ->selectRaw('d.nama as nakes_nama, j.nakes_id, kd.kritik_saran')
                ->first();
        } else {
            $nakesRow = DB::table('jawaban_kuesioner as j')
                ->join('perawats as p','p.id','=','j.nakes_id')
                ->leftJoin('kuesioner_perawats as kp', function($join) use($id) {
                    $join->on('kp.perawat_id','=','j.nakes_id')
                         ->where('kp.kuesioner_id','=',$id);
                })
                ->where('j.kuesioner_id',$id)->where('j.kategori','perawat')
                ->selectRaw('p.nama as nakes_nama, j.nakes_id, kp.kritik_saran')
                ->first();
        }

        if (!$nakesRow) abort(404);
        $kritikRow = $nakesRow->kritik_saran;

        // Pertanyaan aktif + jawaban
        $pertanyaan = PertanyaanKuesioner::where('kategori',$tipe)
            ->where('aktif',true)->orderBy('urutan')->get();

        $jawaban = JawabanKuesioner::where('kuesioner_id',$id)
            ->where('kategori',$tipe)
            ->get()->keyBy('pertanyaan_id');

        return view('dashboard.shared.detail-penilaian-show', compact(
            'kuesioner','tipe','pertanyaan','jawaban','nakesRow','kritikRow'
        ));
    }
}
