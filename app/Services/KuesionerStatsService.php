<?php
// app/Services/KuesionerStatsService.php
//
// Service pengganti JawabanKuesioner model.
// Semua query sekarang langsung dari tabel kuesioner_kliniks/dokters/perawats
// menggunakan kolom `rata_rata` (pre-calculated) dan `jawaban` (JSON).

namespace App\Services;

use Illuminate\Support\Facades\DB;

class KuesionerStatsService
{
    /**
     * Distribusi Baik/Cukup/Kurang untuk satu kategori.
     * Opsional filter per nakes_id.
     */
    public static function distribusi(string $kategori, ?int $nakesId = null): array
    {
        $table = self::getTable($kategori);
        $nakesCol = self::getNakesCol($kategori);

        $query = DB::table($table);
        if ($nakesId && $nakesCol) {
            $query->where($nakesCol, $nakesId);
        }

        $result = $query->selectRaw("
            COALESCE(SUM(CASE WHEN rata_rata >= 3.5 THEN 1 ELSE 0 END), 0) as baik,
            COALESCE(SUM(CASE WHEN rata_rata >= 2.5 AND rata_rata < 3.5 THEN 1 ELSE 0 END), 0) as cukup,
            COALESCE(SUM(CASE WHEN rata_rata < 2.5 THEN 1 ELSE 0 END), 0) as kurang
        ")->first();

        return [
            'labels' => ['Baik (4–5 ⭐)', 'Cukup (3 ⭐)', 'Kurang (1–2 ⭐)'],
            'data'   => [
                (int) $result->baik,
                (int) $result->cukup,
                (int) $result->kurang,
            ],
            'total' => (int) $result->baik + (int) $result->cukup + (int) $result->kurang,
        ];
    }

    /**
     * Distribusi untuk beberapa kategori sekaligus (3 query ringan, bukan 1 heavy subquery).
     */
    public static function distribusiMulti(array $kategoriList): array
    {
        $result = [];
        foreach ($kategoriList as $kat) {
            $result[$kat] = self::distribusi($kat);
        }
        return $result;
    }

    /**
     * Rata-rata per pertanyaan untuk satu kategori (opsional per nakes).
     * Parse JSON `jawaban` column dan hitung AVG per pertanyaan_id.
     */
    public static function rataPerPertanyaan(string $kategori, ?int $nakesId = null): \Illuminate\Support\Collection
    {
        $table = self::getTable($kategori);
        $nakesCol = self::getNakesCol($kategori);

        $query = DB::table($table)->select('jawaban');
        if ($nakesId && $nakesCol) {
            $query->where($nakesCol, $nakesId);
        }

        $rows = $query->get();

        // Aggregate per pertanyaan_id
        $sums = [];
        $counts = [];

        foreach ($rows as $row) {
            $jawaban = json_decode($row->jawaban, true);
            if (!$jawaban) continue;
            foreach ($jawaban as $pertanyaanId => $nilai) {
                $pid = (int) $pertanyaanId;
                $sums[$pid] = ($sums[$pid] ?? 0) + (int) $nilai;
                $counts[$pid] = ($counts[$pid] ?? 0) + 1;
            }
        }

        $result = collect();
        foreach ($sums as $pid => $total) {
            $result->put($pid, (object) [
                'pertanyaan_id' => $pid,
                'rata_rata'     => round($total / $counts[$pid], 2),
            ]);
        }

        return $result;
    }

    /**
     * Summary: rata-rata keseluruhan + total responden untuk satu kategori.
     */
    public static function summary(string $kategori, ?int $nakesId = null): object
    {
        $table = self::getTable($kategori);
        $nakesCol = self::getNakesCol($kategori);

        $query = DB::table($table);
        if ($nakesId && $nakesCol) {
            $query->where($nakesCol, $nakesId);
        }

        return $query->selectRaw('ROUND(AVG(rata_rata), 2) as rata_rata, COUNT(*) as total')->first();
    }

    /**
     * Ambil jawaban detail untuk satu kuesioner + kategori.
     * Return: Collection keyed by pertanyaan_id => nilai.
     */
    public static function jawabanDetail(int $kuesionerId, string $kategori): \Illuminate\Support\Collection
    {
        $table = self::getTable($kategori);

        $row = DB::table($table)->where('kuesioner_id', $kuesionerId)->first();
        if (!$row || !$row->jawaban) return collect();

        $jawaban = json_decode($row->jawaban, true);

        return collect($jawaban)->map(function ($nilai, $pertanyaanId) {
            return (object) [
                'pertanyaan_id' => (int) $pertanyaanId,
                'nilai'         => (int) $nilai,
            ];
        })->keyBy('pertanyaan_id');
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    private static function getTable(string $kategori): string
    {
        return match ($kategori) {
            'klinik'  => 'kuesioner_kliniks',
            'dokter'  => 'kuesioner_dokters',
            'perawat' => 'kuesioner_perawats',
            default   => throw new \InvalidArgumentException("Kategori tidak valid: {$kategori}"),
        };
    }

    private static function getNakesCol(string $kategori): ?string
    {
        return match ($kategori) {
            'dokter'  => 'dokter_id',
            'perawat' => 'perawat_id',
            default   => null,
        };
    }
}
