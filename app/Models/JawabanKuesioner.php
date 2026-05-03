<?php
// app/Models/JawabanKuesioner.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JawabanKuesioner extends Model
{
    protected $table    = 'jawaban_kuesioner';
    protected $fillable = ['kuesioner_id', 'kategori', 'nakes_id', 'pertanyaan_id', 'nilai'];
    protected $casts    = ['nilai' => 'integer'];

    public function kuesioner()
    {
        return $this->belongsTo(Kuesioner::class);
    }

    public function pertanyaan()
    {
        return $this->belongsTo(PertanyaanKuesioner::class, 'pertanyaan_id');
    }

    // ── Helper: hitung rata-rata semua jawaban dalam satu kuesioner+kategori ──
    public static function rataRata(int $kuesionerId, string $kategori): float
    {
        $avg = self::where('kuesioner_id', $kuesionerId)
                   ->where('kategori', $kategori)
                   ->avg('nilai');
        return round((float) $avg, 2);
    }

    // ── Helper: hitung rata-rata per pertanyaan (untuk semua kuesioner nakes) ──
    public static function rataPerPertanyaan(string $kategori, ?int $nakesId = null)
{
    return self::query()
        ->selectRaw('pertanyaan_id, ROUND(AVG(nilai), 2) as rata_rata')
        ->where('kategori', $kategori)
        ->when($nakesId, fn($q) => $q->where('nakes_id', $nakesId))
        ->groupBy('pertanyaan_id')
        ->get()
        ->keyBy('pertanyaan_id');
}

    // ── Helper: distribusi baik/cukup/kurang ─────────────────────────────────
    public static function distribusi(string $kategori, ?int $nakesId = null): array
    {
        $sub = self::where('kategori', $kategori)
            ->when($nakesId, fn($q) => $q->where('nakes_id', $nakesId))
            ->selectRaw('kuesioner_id, AVG(nilai) as avg_nilai')
            ->groupBy('kuesioner_id');

        $result = \DB::query()
            ->fromSub($sub, 't')
            ->selectRaw("
                COALESCE(SUM(CASE WHEN avg_nilai >= 3.5 THEN 1 ELSE 0 END), 0) as baik,
                COALESCE(SUM(CASE WHEN avg_nilai >= 2.5 AND avg_nilai < 3.5 THEN 1 ELSE 0 END), 0) as cukup,
                COALESCE(SUM(CASE WHEN avg_nilai < 2.5 THEN 1 ELSE 0 END), 0) as kurang
            ")
            ->first();

        return [
            'labels' => ['Baik (4–5 ⭐)', 'Cukup (3 ⭐)', 'Kurang (1–2 ⭐)'],
            'data'   => [
                (int) $result->baik,
                (int) $result->cukup,
                (int) $result->kurang
            ],
            'total'  => (int) $result->baik + (int) $result->cukup + (int) $result->kurang,
        ];
    }

    /**
     * Distribusi baik/cukup/kurang untuk beberapa kategori sekaligus (1 query).
     * Menggantikan N kali panggil distribusi() terpisah.
     *
     * @param  array  $kategoriList  ['klinik', 'dokter', 'perawat']
     * @return array  keyed by kategori
     */
    public static function distribusiMulti(array $kategoriList): array
    {
        $sub = self::whereIn('kategori', $kategoriList)
            ->selectRaw('kategori, kuesioner_id, AVG(nilai) as avg_nilai')
            ->groupBy('kategori', 'kuesioner_id');

        $rows = \DB::query()
            ->fromSub($sub, 't')
            ->selectRaw("
                kategori,
                COALESCE(SUM(CASE WHEN avg_nilai >= 3.5 THEN 1 ELSE 0 END), 0) as baik,
                COALESCE(SUM(CASE WHEN avg_nilai >= 2.5 AND avg_nilai < 3.5 THEN 1 ELSE 0 END), 0) as cukup,
                COALESCE(SUM(CASE WHEN avg_nilai < 2.5 THEN 1 ELSE 0 END), 0) as kurang
            ")
            ->groupBy('kategori')
            ->get()
            ->keyBy('kategori');

        $labels = ['Baik (4–5 ⭐)', 'Cukup (3 ⭐)', 'Kurang (1–2 ⭐)'];
        $result = [];

        foreach ($kategoriList as $kat) {
            $r = $rows->get($kat);
            $baik   = (int) ($r->baik ?? 0);
            $cukup  = (int) ($r->cukup ?? 0);
            $kurang = (int) ($r->kurang ?? 0);

            $result[$kat] = [
                'labels' => $labels,
                'data'   => [$baik, $cukup, $kurang],
                'total'  => $baik + $cukup + $kurang,
            ];
        }

        return $result;
    }
}
