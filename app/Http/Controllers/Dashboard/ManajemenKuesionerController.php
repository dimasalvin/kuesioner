<?php
// app/Http/Controllers/Dashboard/ManajemenKuesionerController.php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PertanyaanKuesioner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ManajemenKuesionerController extends Controller
{
    private array $kategoriLabel = [
        'klinik'  => '🏥 Klinik',
        'dokter'  => '👨‍⚕️ Dokter',
        'perawat' => '👩‍⚕️ Perawat',
    ];

    // ── Index: tampilkan semua pertanyaan per kategori ────────────────
    public function index(Request $request)
    {
        $kategori = $request->get('kategori', 'klinik');
        if (!array_key_exists($kategori, $this->kategoriLabel)) {
            $kategori = 'klinik';
        }

        $pertanyaan = PertanyaanKuesioner::where('kategori', $kategori)
            ->orderBy('urutan')
            ->get();

        return view('dashboard.admin.manajemen-kuesioner', [
            'pertanyaan'    => $pertanyaan,
            'kategori'      => $kategori,
            'kategoriLabel' => $this->kategoriLabel,
        ]);
    }

    // ── Store: tambah pertanyaan baru ─────────────────────────────────
    public function store(Request $request)
    {
        $data = $request->validate([
            'kategori' => 'required|in:klinik,dokter,perawat',
            'teks'     => 'required|string|max:500',
        ]);

        // Urutan = terakhir + 1
        $maxUrutan = PertanyaanKuesioner::where('kategori', $data['kategori'])->max('urutan') ?? 0;

        PertanyaanKuesioner::create([
            'kategori' => $data['kategori'],
            'teks'     => $data['teks'],
            'urutan'   => $maxUrutan + 1,
            'aktif'    => true,
        ]);

        Cache::forget("pertanyaan:{$data['kategori']}");

        return redirect()
            ->route('dashboard.admin.manajemen-kuesioner', ['kategori' => $data['kategori']])
            ->with('success', 'Pertanyaan berhasil ditambahkan.');
    }

    // ── Update: edit teks pertanyaan ──────────────────────────────────
    public function update(Request $request, PertanyaanKuesioner $pertanyaan)
    {
        $data = $request->validate([
            'teks'  => 'required|string|max:500',
            'aktif' => 'boolean',
        ]);

        $pertanyaan->update([
            'teks'  => $data['teks'],
            'aktif' => $request->boolean('aktif'),
        ]);

        Cache::forget("pertanyaan:{$pertanyaan->kategori}");

        return redirect()
            ->route('dashboard.admin.manajemen-kuesioner', ['kategori' => $pertanyaan->kategori])
            ->with('success', 'Pertanyaan berhasil diperbarui.');
    }

    // ── Toggle aktif/nonaktif (AJAX) ──────────────────────────────────
    public function toggleAktif(PertanyaanKuesioner $pertanyaan)
    {
        $pertanyaan->update(['aktif' => !$pertanyaan->aktif]);
        Cache::forget("pertanyaan:{$pertanyaan->kategori}");

        return response()->json([
            'aktif'   => $pertanyaan->aktif,
            'message' => $pertanyaan->aktif ? 'Pertanyaan diaktifkan.' : 'Pertanyaan dinonaktifkan.',
        ]);
    }

    // ── Destroy: hapus pertanyaan ─────────────────────────────────────
    public function destroy(PertanyaanKuesioner $pertanyaan)
    {
        $kategori = $pertanyaan->kategori;
        $pertanyaan->delete();
        Cache::forget("pertanyaan:{$kategori}");

        // Reorder urutan setelah hapus
        $this->reorderAfterDelete($kategori);

        return redirect()
            ->route('dashboard.admin.manajemen-kuesioner', ['kategori' => $kategori])
            ->with('success', 'Pertanyaan dihapus.');
    }

    // ── Reorder via drag-drop (AJAX) ──────────────────────────────────
    public function reorder(Request $request)
    {
        $data = $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:pertanyaan_kuesioner,id',
        ]);

        // 1 query CASE WHEN menggantikan N individual UPDATE
        $ids   = $data['ids'];
        $cases = [];
        $binds = [];
        foreach ($ids as $urutan => $id) {
            $cases[] = "WHEN ? THEN ?";
            $binds[] = $id;
            $binds[] = $urutan + 1;
        }
        $binds = array_merge($binds, $ids);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        \DB::update(
            "UPDATE pertanyaan_kuesioner SET urutan = CASE id "
            . implode(' ', $cases)
            . " END WHERE id IN ($placeholders)",
            $binds
        );

        return response()->json(['message' => 'Urutan berhasil disimpan.']);
    }

    // ── Helper ────────────────────────────────────────────────────────
    private function reorderAfterDelete(string $kategori): void
    {
        // Ambil ID dalam urutan yang benar, lalu bulk update dengan CASE WHEN (1 query)
        $ids = PertanyaanKuesioner::where('kategori', $kategori)
            ->orderBy('urutan')
            ->pluck('id');

        if ($ids->isEmpty()) return;

        $cases = [];
        $binds = [];
        foreach ($ids as $i => $id) {
            $cases[] = "WHEN ? THEN ?";
            $binds[] = $id;
            $binds[] = $i + 1;
        }
        $binds = array_merge($binds, $ids->toArray());
        $placeholders = implode(',', array_fill(0, $ids->count(), '?'));

        \DB::update(
            "UPDATE pertanyaan_kuesioner SET urutan = CASE id "
            . implode(' ', $cases)
            . " END WHERE id IN ($placeholders)",
            $binds
        );
    }
}
