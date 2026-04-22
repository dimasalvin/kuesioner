<?php
namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\{JawabanKuesioner, PertanyaanKuesioner};
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

        if (!$nakes) return view('dashboard.user.no-nakes');

        $chart     = JawabanKuesioner::distribusi($kategori, $nakes->id);
        $rataRata  = DB::table('jawaban_kuesioner')
                       ->where('kategori',$kategori)->where('nakes_id',$nakes->id)
                       ->avg('nilai') ?? 0;
        $rataRata  = round((float)$rataRata, 2);
        $total     = DB::table('jawaban_kuesioner')
                       ->where('kategori',$kategori)->where('nakes_id',$nakes->id)
                       ->distinct('kuesioner_id')->count('kuesioner_id');

        // Rata-rata per pertanyaan
        $perQRaw   = JawabanKuesioner::rataPerPertanyaan($kategori, $nakes->id);
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
