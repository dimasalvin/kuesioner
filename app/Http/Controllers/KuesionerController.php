<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Dokter, Perawat, Kuesioner, JawabanKuesioner, PertanyaanKuesioner};
use App\Services\NotificationService;

class KuesionerController extends Controller
{
    // ── Step 1: Identitas ─────────────────────────────────────────────
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

    // ── Step 2: Klinik ────────────────────────────────────────────────
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

    // ── Step 3: Dokter ────────────────────────────────────────────────
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

    // ── Step 4: Perawat ───────────────────────────────────────────────
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

    // ── Step 5: Komplain ──────────────────────────────────────────────
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

        $identitas   = session('identitas');
        $klinikData  = session('klinik');
        $dokterData  = session('dokter');
        $perawatData = session('perawat');

        $hasComplain  = ($data['has_complain'] ?? '0') == '1';
        $komplainTeks = $hasComplain ? ($data['komplain'] ?? null) : null;

        // ── Simpan header kuesioner ──────────────────────────────────
        $kuesioner = Kuesioner::create([
            'nama'         => $identitas['nama'],
            'no_telp'      => $identitas['no_telp'],
            'has_complain' => $hasComplain ? 1 : 0,
            'komplain'     => $komplainTeks,
        ]);

        // ── Simpan jawaban klinik ke jawaban_kuesioner ───────────────
        $klinikPertanyaan = PertanyaanKuesioner::aktif()->kategori('klinik')->get();
        foreach ($klinikPertanyaan as $p) {
            $key = "q{$p->id}";
            if (isset($klinikData[$key])) {
                JawabanKuesioner::create([
                    'kuesioner_id'  => $kuesioner->id,
                    'kategori'      => 'klinik',
                    'nakes_id'      => null,
                    'pertanyaan_id' => $p->id,
                    'nilai'         => (int) $klinikData[$key],
                ]);
            }
        }

        // ── Simpan jawaban dokter ─────────────────────────────────────
        $dokterPertanyaan = PertanyaanKuesioner::aktif()->kategori('dokter')->get();
        $dokterId = $dokterData['nama_dokter'];
        foreach ($dokterPertanyaan as $p) {
            $key = "q{$p->id}";
            if (isset($dokterData[$key])) {
                JawabanKuesioner::create([
                    'kuesioner_id'  => $kuesioner->id,
                    'kategori'      => 'dokter',
                    'nakes_id'      => $dokterId,
                    'pertanyaan_id' => $p->id,
                    'nilai'         => (int) $dokterData[$key],
                ]);
            }
        }

        // Simpan kritik saran dokter ke tabel lama (tetap dipakai)
        \DB::table('kuesioner_dokters')->insert([
            'kuesioner_id' => $kuesioner->id,
            'dokter_id'    => $dokterId,
            'kritik_saran' => $dokterData['kritik_saran'] ?? null,
            // q1-q15 diisi 0 sebagai placeholder (data asli di jawaban_kuesioner)
            'q1'=>0,'q2'=>0,'q3'=>0,'q4'=>0,'q5'=>0,'q6'=>0,'q7'=>0,'q8'=>0,
            'q9'=>0,'q10'=>0,'q11'=>0,'q12'=>0,'q13'=>0,'q14'=>0,'q15'=>0,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // ── Simpan jawaban perawat ────────────────────────────────────
        $perawatPertanyaan = PertanyaanKuesioner::aktif()->kategori('perawat')->get();
        $perawatId = $perawatData['nama_perawat'];
        foreach ($perawatPertanyaan as $p) {
            $key = "q{$p->id}";
            if (isset($perawatData[$key])) {
                JawabanKuesioner::create([
                    'kuesioner_id'  => $kuesioner->id,
                    'kategori'      => 'perawat',
                    'nakes_id'      => $perawatId,
                    'pertanyaan_id' => $p->id,
                    'nilai'         => (int) $perawatData[$key],
                ]);
            }
        }

        \DB::table('kuesioner_perawats')->insert([
            'kuesioner_id' => $kuesioner->id,
            'perawat_id'   => $perawatId,
            'kritik_saran' => $perawatData['kritik_saran'] ?? null,
            'q1'=>0,'q2'=>0,'q3'=>0,'q4'=>0,'q5'=>0,'q6'=>0,'q7'=>0,'q8'=>0,
            'q9'=>0,'q10'=>0,'q11'=>0,'q12'=>0,'q13'=>0,'q14'=>0,'q15'=>0,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // ── Simpan placeholder klinik lama ────────────────────────────
        \DB::table('kuesioner_kliniks')->insert([
            'kuesioner_id' => $kuesioner->id,
            'q1'=>0,'q2'=>0,'q3'=>0,'q4'=>0,'q5'=>0,'q6'=>0,'q7'=>0,'q8'=>0,
            'q9'=>0,'q10'=>0,'q11'=>0,'q12'=>0,'q13'=>0,'q14'=>0,'q15'=>0,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // ── Notifikasi jika ada komplain ──────────────────────────────
        if ($hasComplain && $komplainTeks) {
            NotificationService::createKomplainNotif($kuesioner);
        }

        session()->forget(['identitas', 'klinik', 'dokter', 'perawat', 'komplain']);
        return redirect()->route('kuesioner.thankyou');
    }

    // ── Step 6: Thank You ─────────────────────────────────────────────
    public function thankyou()
    {
        return view('kuesioner.step6-thankyou');
    }
}
