<?php
// app/Http/Controllers/Dashboard/UserController.php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isDokter()) {
            return $this->dokterDashboard($user);
        }

        if ($user->isPerawat()) {
            return $this->perawatDashboard($user);
        }

        abort(403, 'Tipe user tidak dikenali.');
    }

    // ── Dokter dashboard ─────────────────────────────────────────
    private function dokterDashboard($user)
    {
        $dokter = $user->dokter;

        if (!$dokter) {
            return view('dashboard.user.no-nakes');
        }

        $rows = DB::table('kuesioner_dokters')->where('dokter_id', $dokter->id)->get();

        return view('dashboard.user.index', [
            'tipe'      => 'dokter',
            'nakes'     => $dokter,
            'chart'     => $this->buildChart($rows),
            'rataRata'  => $this->rataRataAll($rows),
            'total'     => $rows->count(),
            'perQ'      => $this->rataPerPertanyaan($rows),
            'kritik'    => DB::table('kuesioner_dokters')
                            ->where('dokter_id', $dokter->id)
                            ->whereNotNull('kritik_saran')
                            ->where('kritik_saran', '!=', '')
                            ->latest('created_at')
                            ->limit(5)
                            ->pluck('kritik_saran'),
        ]);
    }

    // ── Perawat dashboard ─────────────────────────────────────────
    private function perawatDashboard($user)
    {
        $perawat = $user->perawat;

        if (!$perawat) {
            return view('dashboard.user.no-nakes');
        }

        $rows = DB::table('kuesioner_perawats')->where('perawat_id', $perawat->id)->get();

        return view('dashboard.user.index', [
            'tipe'      => 'perawat',
            'nakes'     => $perawat,
            'chart'     => $this->buildChart($rows),
            'rataRata'  => $this->rataRataAll($rows),
            'total'     => $rows->count(),
            'perQ'      => $this->rataPerPertanyaan($rows),
            'kritik'    => DB::table('kuesioner_perawats')
                            ->where('perawat_id', $perawat->id)
                            ->whereNotNull('kritik_saran')
                            ->where('kritik_saran', '!=', '')
                            ->latest('created_at')
                            ->limit(5)
                            ->pluck('kritik_saran'),
        ]);
    }

    // ── Chart: Baik / Cukup / Kurang ─────────────────────────────
    private function buildChart($rows): array
    {
        $baik = $cukup = $kurang = 0;

        foreach ($rows as $row) {
            $total = 0;
            for ($i = 1; $i <= 15; $i++) $total += $row->{"q{$i}"};
            $avg = $total / 15;

            if ($avg >= 3.5)     $baik++;
            elseif ($avg >= 2.5) $cukup++;
            else                 $kurang++;
        }

        return [
            'labels' => ['Baik (4–5 ⭐)', 'Cukup (3 ⭐)', 'Kurang (1–2 ⭐)'],
            'data'   => [$baik, $cukup, $kurang],
            'total'  => $rows->count(),
        ];
    }

    // ── Helpers ──────────────────────────────────────────────────
    private function rataRataAll($rows): float
    {
        if ($rows->isEmpty()) return 0;

        $sum = 0;
        foreach ($rows as $row) {
            for ($i = 1; $i <= 15; $i++) $sum += $row->{"q{$i}"};
        }
        return round($sum / ($rows->count() * 15), 2);
    }

    private function rataPerPertanyaan($rows): array
    {
        if ($rows->isEmpty()) return array_fill(1, 15, 0);

        $sums = array_fill(1, 15, 0);
        foreach ($rows as $row) {
            for ($i = 1; $i <= 15; $i++) $sums[$i] += $row->{"q{$i}"};
        }

        $count = $rows->count();
        return array_map(fn($s) => round($s / $count, 2), $sums);
    }
    // ── Kritik & Saran milik sendiri ──────────────────────────────────
    public function kritikSaran()
    {
        $user = auth()->user();
        if ($user->isDokter()) {
            $nakes = $user->dokter;
            $kritik = \DB::table('kuesioner_dokters as kd')
                ->join('kuesioners as k','k.id','=','kd.kuesioner_id')
                ->where('kd.dokter_id', $nakes->id)
                ->whereNotNull('kd.kritik_saran')->where('kd.kritik_saran','!=','')
                ->select('kd.kritik_saran','k.nama as pasien_nama','k.created_at')
                ->orderByDesc('k.created_at')->paginate(15);
        } else {
            $nakes = $user->perawat;
            $kritik = \DB::table('kuesioner_perawats as kp')
                ->join('kuesioners as k','k.id','=','kp.kuesioner_id')
                ->where('kp.perawat_id', $nakes->id)
                ->whereNotNull('kp.kritik_saran')->where('kp.kritik_saran','!=','')
                ->select('kp.kritik_saran','k.nama as pasien_nama','k.created_at')
                ->orderByDesc('k.created_at')->paginate(15);
        }
    
        return view('dashboard.user.kritik-saran', compact('nakes','kritik'));
    }
}

