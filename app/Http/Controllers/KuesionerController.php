<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
        $pertanyaan = Cache::remember('pertanyaan:klinik', 300, function () {
            return PertanyaanKuesioner::aktif()->kategori('klinik')->get(['id', 'teks', 'urutan']);
        });
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
        $pertanyaan = Cache::remember('pertanyaan:dokter', 300, function () {
            return PertanyaanKuesioner::aktif()->kategori('dokter')->get(['id', 'teks', 'urutan']);
        });
        $dokterList = Cache::remember('nakes:dokter:list', 300, function () {
            return Dokter::where('aktif', true)->orderBy('nama')->get(['id', 'nama']);
        });
        return view('kuesioner.step3-dokter', [
            'dokter'     => $dokterList,
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
        $pertanyaan = Cache::remember('pertanyaan:perawat', 300, function () {
            return PertanyaanKuesioner::aktif()->kategori('perawat')->get(['id', 'teks', 'urutan']);
        });
        $perawatList = Cache::remember('nakes:perawat:list', 300, function () {
            return Perawat::where('aktif', true)->orderBy('nama')->get(['id', 'nama']);
        });
        return view('kuesioner.step4-perawat', [
            'perawat'    => $perawatList,
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

        // ── Ambil semua pertanyaan aktif sekali saja (1 query, bukan 3) ──
        $semuaPertanyaan = PertanyaanKuesioner::aktif()->get()->groupBy('kategori');
        $klinikPertanyaan = $semuaPertanyaan->get('klinik', collect());
        $dokterPertanyaan = $semuaPertanyaan->get('dokter', collect());
        $perawatPertanyaan = $semuaPertanyaan->get('perawat', collect());

        $dokterId  = $dokterData['nama_dokter'];
        $perawatId = $perawatData['nama_perawat'];
        $now       = now();

        // ── Wrap semua insert dalam satu transaction ─────────────────
        \DB::transaction(function () use (
            $identitas, $klinikData, $dokterData, $perawatData,
            $klinikPertanyaan, $dokterPertanyaan, $perawatPertanyaan,
            $hasComplain, $komplainTeks, $dokterId, $perawatId, $now
        ) {
            // ── Simpan header kuesioner ──────────────────────────────
            $kuesioner = Kuesioner::create([
                'nama'         => $identitas['nama'],
                'no_telp'      => $identitas['no_telp'],
                'has_complain' => $hasComplain ? 1 : 0,
                'komplain'     => $komplainTeks,
            ]);

            $kuesionerId = $kuesioner->id;

            // ── Build bulk insert untuk jawaban_kuesioner (1 query) ──
            $jawabanBulk = [];

            // Klinik
            $klinikInsert = ['kuesioner_id' => $kuesionerId, 'created_at' => $now, 'updated_at' => $now];
            $urutanQ = 1;
            foreach ($klinikPertanyaan as $p) {
                $nilai = (int) ($klinikData["q{$p->id}"] ?? 0);
                $jawabanBulk[] = [
                    'kuesioner_id'  => $kuesionerId,
                    'kategori'      => 'klinik',
                    'nakes_id'      => null,
                    'pertanyaan_id' => $p->id,
                    'nilai'         => $nilai,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];
                $klinikInsert["q{$urutanQ}"] = $nilai;
                $urutanQ++;
            }

            // Dokter
            $dokterInsert = [
                'kuesioner_id' => $kuesionerId, 'dokter_id' => $dokterId,
                'kritik_saran' => $dokterData['kritik_saran'] ?? null,
                'created_at' => $now, 'updated_at' => $now,
            ];
            $urutanQ = 1;
            foreach ($dokterPertanyaan as $p) {
                $nilai = (int) ($dokterData["q{$p->id}"] ?? 0);
                $jawabanBulk[] = [
                    'kuesioner_id'  => $kuesionerId,
                    'kategori'      => 'dokter',
                    'nakes_id'      => $dokterId,
                    'pertanyaan_id' => $p->id,
                    'nilai'         => $nilai,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];
                $dokterInsert["q{$urutanQ}"] = $nilai;
                $urutanQ++;
            }

            // Perawat
            $perawatInsert = [
                'kuesioner_id' => $kuesionerId, 'perawat_id' => $perawatId,
                'kritik_saran' => $perawatData['kritik_saran'] ?? null,
                'created_at' => $now, 'updated_at' => $now,
            ];
            $urutanQ = 1;
            foreach ($perawatPertanyaan as $p) {
                $nilai = (int) ($perawatData["q{$p->id}"] ?? 0);
                $jawabanBulk[] = [
                    'kuesioner_id'  => $kuesionerId,
                    'kategori'      => 'perawat',
                    'nakes_id'      => $perawatId,
                    'pertanyaan_id' => $p->id,
                    'nilai'         => $nilai,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];
                $perawatInsert["q{$urutanQ}"] = $nilai;
                $urutanQ++;
            }

            // ── Execute bulk inserts (4 queries total, bukan 48) ─────
            JawabanKuesioner::insert($jawabanBulk);
            \DB::table('kuesioner_kliniks')->insert($klinikInsert);
            \DB::table('kuesioner_dokters')->insert($dokterInsert);
            \DB::table('kuesioner_perawats')->insert($perawatInsert);

            // ── Notifikasi jika ada komplain ─────────────────────────
            if ($hasComplain && $komplainTeks) {
                NotificationService::createKomplainNotif($kuesioner);
            }
        });

        // Invalidate all dashboard caches setelah data baru masuk
        $cacheKeys = [
            'dashboard:distribusi', 'dashboard:stats', 'dashboard:stats:mgmt',
            'dashboard:rating:dokter:5', 'dashboard:rating:perawat:5',
            'dashboard:rating:dokter:all', 'dashboard:rating:perawat:all',
            'dashboard:chart:semua:dokter', 'dashboard:chart:semua:perawat',
        ];
        foreach ($cacheKeys as $key) Cache::forget($key);

        session()->forget(['identitas', 'klinik', 'dokter', 'perawat', 'komplain']);
        return redirect()->route('kuesioner.thankyou');
    }

    // ── Step 6: Thank You ─────────────────────────────────────────────
    public function thankyou()
    {
        return view('kuesioner.step6-thankyou');
    }
}
