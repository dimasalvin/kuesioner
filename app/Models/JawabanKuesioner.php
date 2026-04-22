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
            SUM(CASE WHEN avg_nilai >= 3.5 THEN 1 ELSE 0 END) as baik,
            SUM(CASE WHEN avg_nilai >= 2.5 AND avg_nilai < 3.5 THEN 1 ELSE 0 END) as cukup,
            SUM(CASE WHEN avg_nilai < 2.5 THEN 1 ELSE 0 END) as kurang
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
}
