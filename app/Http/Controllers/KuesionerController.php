<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Dokter, Perawat, Kuesioner, KuesionerKlinik, KuesionerDokter, KuesionerPerawat, PertanyaanKuesioner};
use App\Services\NotificationService;

class KuesionerController extends Controller
{
    // ─── Step 1: Identitas ───────────────────────────────────────────
    public function index()
    {
        session()->forget(['identitas', 'klinik', 'dokter', 'perawat', 'komplain']);
        return view('kuesioner.step1-identitas');
    }

    public function storeIdentitas(Request $request)
    {
        $data = $request->validate([
            'nama'    => 'required|string|max:255',
            'no_telp' => 'required|string|max:20',
        ]);
        session(['identitas' => $data]);
        return redirect()->route('kuesioner.klinik');
    }

    // ─── Step 2: Klinik ───────────────────────────────────────────────
    public function klinik()
    {
        if (!session('identitas')) return redirect()->route('kuesioner.index');
        $pertanyaan = PertanyaanKuesioner::aktif()->kategori('klinik')->get();
        return view('kuesioner.step2-klinik', compact('pertanyaan'));
    }

    public function storeKlinik(Request $request)
    {
        $pertanyaan = PertanyaanKuesioner::aktif()->kategori('klinik')->get();
        $rules = [];
        foreach ($pertanyaan as $p) {
            $rules["q{$p->id}"] = 'required|integer|between:1,5';
        }
        $data = $request->validate($rules);
        session(['klinik' => $data]);
        return redirect()->route('kuesioner.dokter');
    }

    // ─── Step 3: Dokter ───────────────────────────────────────────────
    public function dokter()
    {
        if (!session('klinik')) return redirect()->route('kuesioner.klinik');
        $pertanyaan = PertanyaanKuesioner::aktif()->kategori('dokter')->get();
        return view('kuesioner.step3-dokter', [
            'dokter'     => Dokter::orderBy('nama')->get(),
            'pertanyaan' => $pertanyaan,
        ]);
    }

    public function storeDokter(Request $request)
    {
        $pertanyaan = PertanyaanKuesioner::aktif()->kategori('dokter')->get();
        $rules = ['nama_dokter' => 'required|exists:dokters,id'];
        foreach ($pertanyaan as $p) {
            $rules["q{$p->id}"] = 'required|integer|between:1,5';
        }
        $data = $request->validate($rules);
        $data['kritik_saran'] = $request->kritik_saran;
        session(['dokter' => $data]);
        return redirect()->route('kuesioner.perawat');
    }

    // ─── Step 4: Perawat ──────────────────────────────────────────────
    public function perawat()
    {
        if (!session('dokter')) return redirect()->route('kuesioner.dokter');
        $pertanyaan = PertanyaanKuesioner::aktif()->kategori('perawat')->get();
        return view('kuesioner.step4-perawat', [
            'perawat'    => Perawat::orderBy('nama')->get(),
            'pertanyaan' => $pertanyaan,
        ]);
    }

    public function storePerawat(Request $request)
    {
        $pertanyaan = PertanyaanKuesioner::aktif()->kategori('perawat')->get();
        $rules = ['nama_perawat' => 'required|exists:perawats,id'];
        foreach ($pertanyaan as $p) {
            $rules["q{$p->id}"] = 'required|integer|between:1,5';
        }
        $data = $request->validate($rules);
        $data['kritik_saran'] = $request->kritik_saran;
        session(['perawat' => $data]);
        return redirect()->route('kuesioner.komplain');
    }

    // ─── Step 5: Komplain ─────────────────────────────────────────────
    public function komplain()
    {
        if (!session('perawat')) return redirect()->route('kuesioner.perawat');
        return view('kuesioner.step5-komplain');
    }

    public function storeKomplain(Request $request)
    {
        $data = $request->validate([
            'has_complain' => 'nullable|in:0,1',
            'komplain'     => 'nullable|string|max:2000',
        ]);

        session(['komplain' => $data]);

        $identitas = session('identitas');
        $klinik    = session('klinik');
        $dokter    = session('dokter');
        $perawat   = session('perawat');

        $hasComplain = ($data['has_complain'] ?? '0') == '1';
        $komplainTeks = $hasComplain ? ($data['komplain'] ?? null) : null;

        $kuesioner = Kuesioner::create([
            'nama'         => $identitas['nama'],
            'no_telp'      => $identitas['no_telp'],
            'has_complain' => $hasComplain ? 1 : 0,
            'komplain'     => $komplainTeks,
        ]);

        // ── Simpan jawaban klinik (pakai pertanyaan_id sebagai key) ──
        $klinikData = ['kuesioner_id' => $kuesioner->id];
        $klinikPertanyaan = PertanyaanKuesioner::aktif()->kategori('klinik')->get();
        $slot = 1;
        foreach ($klinikPertanyaan as $p) {
            if ($slot > 15) break;
            $klinikData["q{$slot}"] = $klinik["q{$p->id}"] ?? 3;
            $slot++;
        }
        // isi slot kosong jika pertanyaan aktif < 15
        for ($i = $slot; $i <= 15; $i++) { $klinikData["q{$i}"] = 0; }
        KuesionerKlinik::create($klinikData);

        // ── Simpan jawaban dokter ─────────────────────────────────
        $dokterData = [
            'kuesioner_id' => $kuesioner->id,
            'dokter_id'    => $dokter['nama_dokter'],
            'kritik_saran' => $dokter['kritik_saran'] ?? null,
        ];
        $dokterPertanyaan = PertanyaanKuesioner::aktif()->kategori('dokter')->get();
        $slot = 1;
        foreach ($dokterPertanyaan as $p) {
            if ($slot > 15) break;
            $dokterData["q{$slot}"] = $dokter["q{$p->id}"] ?? 3;
            $slot++;
        }
        for ($i = $slot; $i <= 15; $i++) { $dokterData["q{$i}"] = 0; }
        KuesionerDokter::create($dokterData);

        // ── Simpan jawaban perawat ────────────────────────────────
        $perawatData = [
            'kuesioner_id' => $kuesioner->id,
            'perawat_id'   => $perawat['nama_perawat'],
            'kritik_saran' => $perawat['kritik_saran'] ?? null,
        ];
        $perawatPertanyaan = PertanyaanKuesioner::aktif()->kategori('perawat')->get();
        $slot = 1;
        foreach ($perawatPertanyaan as $p) {
            if ($slot > 15) break;
            $perawatData["q{$slot}"] = $perawat["q{$p->id}"] ?? 3;
            $slot++;
        }
        for ($i = $slot; $i <= 15; $i++) { $perawatData["q{$i}"] = 0; }
        KuesionerPerawat::create($perawatData);

        // ── Kirim notifikasi jika ada komplain ────────────────────
        if ($hasComplain && $komplainTeks) {
            NotificationService::createKomplainNotif($kuesioner);
        }

        session()->forget(['identitas', 'klinik', 'dokter', 'perawat', 'komplain']);
        return redirect()->route('kuesioner.thankyou');
    }

    // ─── Step 6: Thank You ────────────────────────────────────────────
    public function thankyou()
    {
        return view('kuesioner.step6-thankyou');
    }
}
