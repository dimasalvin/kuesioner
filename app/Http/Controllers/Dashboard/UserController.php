<?php
namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PertanyaanKuesioner;
use App\Services\KuesionerStatsService;
use Illuminate\Support\Facades\{Auth, DB};

class UserController extends Controller
{
    // ── Penilaian Saya: chart pribadi ─────────────────────────────────
    public function index()
    {
        $user = Auth::user();

        if ($user->isDokter()) {
            $nakes    = $user->dokter;
            $kategori = 'dokter';
        } elseif ($user->isPerawat()) {
            $nakes    = $user->perawat;
            $kategori = 'perawat';
        } else {
            return view('dashboard.user.no-nakes');
        }

        // Jika nakes_id belum terhubung, tampilkan dashboard kosong
        if (!$nakes) {
            $pertanyaan = PertanyaanKuesioner::aktif()->kategori($kategori)->get();
            return view('dashboard.user.index', [
                'nakes'      => null,
                'chart'      => [
                    'labels' => ['Baik (4–5 ⭐)', 'Cukup (3 ⭐)', 'Kurang (1–2 ⭐)'],
                    'data'   => [0, 0, 0],
                    'total'  => 0,
                ],
                'rataRata'   => 0,
                'total'      => 0,
                'pertanyaan' => $pertanyaan,
                'perQRaw'    => collect(),
                'kategori'   => $kategori,
                'kritik'     => collect(),
                'tipe'       => $kategori,
            ]);
        }

        $chart     = KuesionerStatsService::distribusi($kategori, $nakes->id);

        // Summary: rata-rata + total responden
        $summary   = KuesionerStatsService::summary($kategori, $nakes->id);
        $rataRata  = round((float) ($summary->rata_rata ?? 0), 2);
        $total     = (int) ($summary->total ?? 0);

        // Rata-rata per pertanyaan
        $perQRaw   = KuesionerStatsService::rataPerPertanyaan($kategori, $nakes->id);
        $pertanyaan = PertanyaanKuesioner::aktif()->kategori($kategori)->get();

        // Kritik saran terbaru
        $tabel   = $kategori === 'dokter' ? 'kuesioner_dokters' : 'kuesioner_perawats';
        $kolom   = $kategori === 'dokter' ? 'dokter_id' : 'perawat_id';
        $kritik  = DB::table($tabel)->where($kolom,$nakes->id)
            ->whereNotNull('kritik_saran')->where('kritik_saran','!=','')
            ->latest('created_at')->limit(5)->pluck('kritik_saran');

        return view('dashboard.user.index', compact(
    'nakes','chart','rataRata','total','pertanyaan','perQRaw','kategori','kritik'
))->with('tipe', $kategori);
    }

    // ── Kritik & Saran milik sendiri ──────────────────────────────────
    public function kritikSaran()
    {
        $user  = Auth::user();
        $nakes = $user->isDokter() ? $user->dokter : $user->perawat;

        // Jika nakes belum terhubung, tampilkan halaman kosong
        if (!$nakes) {
            return view('dashboard.user.kritik-saran', [
                'nakes'  => null,
                'kritik' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
            ]);
        }

        $tabel = $user->isDokter() ? 'kuesioner_dokters' : 'kuesioner_perawats';
        $kolom = $user->isDokter() ? 'dokter_id' : 'perawat_id';

        $kritik = DB::table($tabel.' as kt')
            ->join('kuesioners as k','k.id','=','kt.kuesioner_id')
            ->where('kt.'.$kolom, $nakes->id)
            ->whereNotNull('kt.kritik_saran')->where('kt.kritik_saran','!=','')
            ->select('kt.kritik_saran','k.nama as pasien_nama','k.created_at')
            ->orderByDesc('k.created_at')->paginate(15);

        return view('dashboard.user.kritik-saran', compact('nakes','kritik'));
    }
}
