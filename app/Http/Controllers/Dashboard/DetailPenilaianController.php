<?php
// app/Http/Controllers/Dashboard/DetailPenilaianController.php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\{Dokter, Perawat, Kuesioner, PertanyaanKuesioner};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DetailPenilaianController extends Controller
{
    // ── Index utama: router berdasarkan tipe & role ───────────────────
    public function index(Request $request)
    {
        $user = auth()->user();
        $tipe = $request->get('tipe', 'klinik');

        // Nakes: klinik = list kuesioner klinik (sama seperti admin)
        //        pribadi = list kuesioner milik sendiri langsung
        if ($user->isUser()) {
    if ($tipe === 'klinik') {
        return $this->klinikList(true);
    }

    if (!in_array($tipe, ['dokter','perawat'])) {
        $tipe = $user->isDokter() ? 'dokter' : 'perawat';
    }

    return $this->indexNakes($request->merge(['tipe'=>$tipe]), $user);
}

        // Admin / Management: klinik, dokter, perawat
        if ($tipe === 'klinik') {
            return $this->klinikList(false);
        }

        if (!in_array($tipe, ['dokter', 'perawat'])) $tipe = 'dokter';

        if ($tipe === 'dokter') {
            $list = DB::table('dokters as d')
                ->leftJoin('kuesioner_dokters as kd', 'd.id', '=', 'kd.dokter_id')
                ->selectRaw('d.id, d.nama, d.spesialisasi,
                    COUNT(kd.id) as total,
                    ROUND(AVG((kd.q1+kd.q2+kd.q3+kd.q4+kd.q5+kd.q6+kd.q7+kd.q8+kd.q9+kd.q10+kd.q11+kd.q12+kd.q13+kd.q14+kd.q15)/15.0),2) as rata_rata')
                ->groupBy('d.id','d.nama','d.spesialisasi')
                ->orderBy('d.nama')->get();
        } else {
            $list = DB::table('perawats as p')
                ->leftJoin('kuesioner_perawats as kp', 'p.id', '=', 'kp.perawat_id')
                ->selectRaw('p.id, p.nama, NULL as spesialisasi,
                    COUNT(kp.id) as total,
                    ROUND(AVG((kp.q1+kp.q2+kp.q3+kp.q4+kp.q5+kp.q6+kp.q7+kp.q8+kp.q9+kp.q10+kp.q11+kp.q12+kp.q13+kp.q14+kp.q15)/15.0),2) as rata_rata')
                ->groupBy('p.id','p.nama')
                ->orderBy('p.nama')->get();
        }

        return view('dashboard.shared.detail-penilaian-index', compact('list','tipe'));
    }

    // ── Klinik: list semua kuesioner klinik ─────────────────────────
    private function klinikList(bool $userMode = false)
    {
        $rows = DB::table('kuesioner_kliniks as kk')
            ->join('kuesioners as k', 'k.id', '=', 'kk.kuesioner_id')
            ->selectRaw('kk.id, k.nama as pasien_nama, k.no_telp, k.created_at,
                ROUND((kk.q1+kk.q2+kk.q3+kk.q4+kk.q5+kk.q6+kk.q7+kk.q8+kk.q9+kk.q10+kk.q11+kk.q12+kk.q13+kk.q14+kk.q15)/15.0,2) as rata_rata')
            ->orderByDesc('kk.id')
            ->paginate(20);

        $tipe = 'klinik';
        return view('dashboard.shared.detail-penilaian-klinik-list', compact('rows','tipe','userMode'));
    }

    // ── Klinik: detail satu kuesioner klinik ─────────────────────────
    public function showKlinik(int $id)
    {
        $row = DB::table('kuesioner_kliniks as kk')
            ->join('kuesioners as k', 'k.id', '=', 'kk.kuesioner_id')
            ->where('kk.id', $id)
            ->selectRaw('kk.*, k.nama as pasien_nama, k.no_telp, k.created_at')
            ->first();

        if (!$row) abort(404);

        $pertanyaan = PertanyaanKuesioner::where('kategori','klinik')
            ->where('aktif', true)->orderBy('urutan')->get();

        return view('dashboard.shared.detail-penilaian-klinik-show', compact('row','pertanyaan'));
    }

    // ── Klinik: tampilan chart + per-Q untuk nakes ───────────────────
    private function klinikDetail()
    {
        $chart      = AdminController::chartData('klinik');
        $pertanyaan = PertanyaanKuesioner::aktif()->kategori('klinik')->get();
        $rows       = DB::table('kuesioner_kliniks')->get();
        $perQ       = [];
        for ($i = 1; $i <= 15; $i++) {
            $perQ[$i] = $rows->isNotEmpty() ? round($rows->avg("q{$i}"), 2) : 0;
        }
        $total = $rows->count();
        $tipe  = 'klinik';

        return view('dashboard.shared.detail-penilaian-klinik-nakes', compact(
            'chart','pertanyaan','perQ','total','tipe'
        ));
    }

    // ── Index nakes: list kuesioner pribadi ───────────────────────────
    private function indexNakes(Request $request, $user)
    {
        if ($user->isDokter()) {
            $nakes = $user->dokter;
            $rows  = DB::table('kuesioner_dokters as kd')
                ->join('kuesioners as k', 'k.id', '=', 'kd.kuesioner_id')
                ->where('kd.dokter_id', $nakes->id)
                ->selectRaw('kd.id, k.nama as pasien_nama, k.no_telp, k.created_at,
                    ROUND((kd.q1+kd.q2+kd.q3+kd.q4+kd.q5+kd.q6+kd.q7+kd.q8+kd.q9+kd.q10+kd.q11+kd.q12+kd.q13+kd.q14+kd.q15)/15.0,2) as rata_rata')
                ->orderByDesc('kd.id')->paginate(15);
        } else {
            $nakes = $user->perawat;
            $rows  = DB::table('kuesioner_perawats as kp')
                ->join('kuesioners as k', 'k.id', '=', 'kp.kuesioner_id')
                ->where('kp.perawat_id', $nakes->id)
                ->selectRaw('kp.id, k.nama as pasien_nama, k.no_telp, k.created_at,
                    ROUND((kp.q1+kp.q2+kp.q3+kp.q4+kp.q5+kp.q6+kp.q7+kp.q8+kp.q9+kp.q10+kp.q11+kp.q12+kp.q13+kp.q14+kp.q15)/15.0,2) as rata_rata')
                ->orderByDesc('kp.id')->paginate(15);
        }

        $tipe = $user->isDokter() ? 'dokter' : 'perawat';
        return view('dashboard.shared.detail-penilaian-nakes', compact('rows','nakes','tipe'));
    }

    // ── List kuesioner satu nakes (admin/management drill-down) ───────
    public function byNakes(Request $request, int $id)
    {
        $tipe = $request->get('tipe', 'dokter');
        $user = auth()->user();

        if ($user->isUser()) {
            $myId = $user->isDokter() ? $user->dokter?->id : $user->perawat?->id;
            if ($myId != $id) abort(403);
        }

        if ($tipe === 'dokter') {
            $nakes = Dokter::findOrFail($id);
            $rows  = DB::table('kuesioner_dokters as kd')
                ->join('kuesioners as k', 'k.id', '=', 'kd.kuesioner_id')
                ->where('kd.dokter_id', $id)
                ->selectRaw('kd.id, k.nama as pasien_nama, k.no_telp, k.created_at,
                    ROUND((kd.q1+kd.q2+kd.q3+kd.q4+kd.q5+kd.q6+kd.q7+kd.q8+kd.q9+kd.q10+kd.q11+kd.q12+kd.q13+kd.q14+kd.q15)/15.0,2) as rata_rata')
                ->orderByDesc('kd.id')->paginate(15);
        } else {
            $nakes = Perawat::findOrFail($id);
            $rows  = DB::table('kuesioner_perawats as kp')
                ->join('kuesioners as k', 'k.id', '=', 'kp.kuesioner_id')
                ->where('kp.perawat_id', $id)
                ->selectRaw('kp.id, k.nama as pasien_nama, k.no_telp, k.created_at,
                    ROUND((kp.q1+kp.q2+kp.q3+kp.q4+kp.q5+kp.q6+kp.q7+kp.q8+kp.q9+kp.q10+kp.q11+kp.q12+kp.q13+kp.q14+kp.q15)/15.0,2) as rata_rata')
                ->orderByDesc('kp.id')->paginate(15);
        }

        return view('dashboard.shared.detail-penilaian-list', compact('rows','nakes','tipe'));
    }

    // ── Detail satu kuesioner nakes ───────────────────────────────────
    public function show(Request $request, int $id)
    {
        $tipe = $request->get('tipe', 'dokter');
        $user = auth()->user();

        if ($tipe === 'dokter') {
            $row = DB::table('kuesioner_dokters as kd')
                ->join('kuesioners as k', 'k.id', '=', 'kd.kuesioner_id')
                ->join('dokters as d', 'd.id', '=', 'kd.dokter_id')
                ->where('kd.id', $id)
                ->selectRaw('kd.*, k.nama as pasien_nama, k.no_telp, k.created_at,
                             d.nama as nakes_nama, d.spesialisasi, kd.dokter_id as nakes_id')
                ->first();

            if ($user->isUser() && $user->isDokter()) {
                if ($row?->nakes_id != $user->nakes_id) abort(403);
            }
        } else {
            $row = DB::table('kuesioner_perawats as kp')
                ->join('kuesioners as k', 'k.id', '=', 'kp.kuesioner_id')
                ->join('perawats as p', 'p.id', '=', 'kp.perawat_id')
                ->where('kp.id', $id)
                ->selectRaw('kp.*, k.nama as pasien_nama, k.no_telp, k.created_at,
                             p.nama as nakes_nama, NULL as spesialisasi, kp.perawat_id as nakes_id')
                ->first();

            if ($user->isUser() && $user->isPerawat()) {
                if ($row?->nakes_id != $user->nakes_id) abort(403);
            }
        }

        if (!$row) abort(404);

        $pertanyaan = PertanyaanKuesioner::where('kategori', $tipe)
            ->where('aktif', true)->orderBy('urutan')->get();

        return view('dashboard.shared.detail-penilaian-show', compact('row','tipe','pertanyaan'));
    }

    // ── Penilaian Klinik menu khusus nakes ───────────────────────────
    public function klinik()
    {
        return $this->klinikDetail();
    }
}
