<?php
// database/migrations/2024_01_01_000005_add_performance_indexes.php
//
// TUJUAN: Menambahkan index untuk optimasi query pada skala 100k+ data.
// Tanpa index, query JOIN + GROUP BY + ORDER BY akan full table scan.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Schema, DB};

return new class extends Migration
{
    public function up(): void
    {
        // Helper: cek apakah index sudah ada
        $hasIndex = function (string $table, string $indexName): bool {
            $indexes = collect(DB::select("SHOW INDEX FROM {$table}"))
                ->pluck('Key_name')->unique()->values();
            return $indexes->contains($indexName);
        };

        // ── kuesioners: sering di-filter has_complain + ORDER BY created_at ──
        Schema::table('kuesioners', function (Blueprint $table) use ($hasIndex) {
            if (!$hasIndex('kuesioners', 'idx_kuesioners_complain_date')) {
                $table->index(['has_complain', 'created_at'], 'idx_kuesioners_complain_date');
            }
            if (!$hasIndex('kuesioners', 'idx_kuesioners_created_at')) {
                $table->index('created_at', 'idx_kuesioners_created_at');
            }
        });

        // ── kuesioner_dokters: JOIN on dokter_id, kuesioner_id ───────────────
        Schema::table('kuesioner_dokters', function (Blueprint $table) use ($hasIndex) {
            if (!$hasIndex('kuesioner_dokters', 'idx_kd_dokter_id')) {
                $table->index('dokter_id', 'idx_kd_dokter_id');
            }
            if (!$hasIndex('kuesioner_dokters', 'idx_kd_kuesioner_dokter')) {
                $table->index(['kuesioner_id', 'dokter_id'], 'idx_kd_kuesioner_dokter');
            }
        });

        // ── kuesioner_perawats: JOIN on perawat_id, kuesioner_id ─────────────
        Schema::table('kuesioner_perawats', function (Blueprint $table) use ($hasIndex) {
            if (!$hasIndex('kuesioner_perawats', 'idx_kp_perawat_id')) {
                $table->index('perawat_id', 'idx_kp_perawat_id');
            }
            if (!$hasIndex('kuesioner_perawats', 'idx_kp_kuesioner_perawat')) {
                $table->index(['kuesioner_id', 'perawat_id'], 'idx_kp_kuesioner_perawat');
            }
        });

        // ── kuesioner_kliniks: FK lookup ─────────────────────────────────────
        Schema::table('kuesioner_kliniks', function (Blueprint $table) use ($hasIndex) {
            if (!$hasIndex('kuesioner_kliniks', 'idx_kk_kuesioner_id')) {
                $table->index('kuesioner_id', 'idx_kk_kuesioner_id');
            }
        });

        // ── jawaban_kuesioner: composite index untuk distribusi query ─────────
        // Query paling berat: GROUP BY kuesioner_id WHERE kategori + nakes_id
        Schema::table('jawaban_kuesioner', function (Blueprint $table) use ($hasIndex) {
            // Covering index untuk distribusi() — kategori + nakes_id + kuesioner_id + nilai
            if (!$hasIndex('jawaban_kuesioner', 'idx_jk_kategori_nakes_kuesioner_nilai')) {
                $table->index(
                    ['kategori', 'nakes_id', 'kuesioner_id', 'nilai'],
                    'idx_jk_kategori_nakes_kuesioner_nilai'
                );
            }
            // Untuk rataPerPertanyaan() — kategori + nakes_id + pertanyaan_id
            if (!$hasIndex('jawaban_kuesioner', 'idx_jk_kategori_nakes_pertanyaan')) {
                $table->index(
                    ['kategori', 'nakes_id', 'pertanyaan_id'],
                    'idx_jk_kategori_nakes_pertanyaan'
                );
            }
        });

        // ── notifications: query per user + unread ───────────────────────────
        Schema::table('notifications', function (Blueprint $table) use ($hasIndex) {
            if (!$hasIndex('notifications', 'idx_notif_user_read_date')) {
                $table->index(['user_id', 'read_at', 'created_at'], 'idx_notif_user_read_date');
            }
        });

        // ── users: filter by role + aktif ────────────────────────────────────
        Schema::table('users', function (Blueprint $table) use ($hasIndex) {
            if (!$hasIndex('users', 'idx_users_role_aktif')) {
                $table->index(['role', 'aktif'], 'idx_users_role_aktif');
            }
            if (!$hasIndex('users', 'idx_users_role_name')) {
                $table->index(['role', 'name'], 'idx_users_role_name');
            }
        });

        // ── pertanyaan_kuesioner: filter kategori + aktif + urutan ────────────
        Schema::table('pertanyaan_kuesioner', function (Blueprint $table) use ($hasIndex) {
            if (!$hasIndex('pertanyaan_kuesioner', 'idx_pk_kategori_aktif_urutan')) {
                $table->index(['kategori', 'aktif', 'urutan'], 'idx_pk_kategori_aktif_urutan');
            }
        });
    }

    public function down(): void
    {
        $hasIndex = function (string $table, string $indexName): bool {
            $indexes = collect(DB::select("SHOW INDEX FROM {$table}"))
                ->pluck('Key_name')->unique()->values();
            return $indexes->contains($indexName);
        };

        Schema::table('kuesioners', function (Blueprint $table) use ($hasIndex) {
            if ($hasIndex('kuesioners', 'idx_kuesioners_complain_date')) $table->dropIndex('idx_kuesioners_complain_date');
            if ($hasIndex('kuesioners', 'idx_kuesioners_created_at')) $table->dropIndex('idx_kuesioners_created_at');
        });
        Schema::table('kuesioner_dokters', function (Blueprint $table) use ($hasIndex) {
            if ($hasIndex('kuesioner_dokters', 'idx_kd_dokter_id')) $table->dropIndex('idx_kd_dokter_id');
            if ($hasIndex('kuesioner_dokters', 'idx_kd_kuesioner_dokter')) $table->dropIndex('idx_kd_kuesioner_dokter');
        });
        Schema::table('kuesioner_perawats', function (Blueprint $table) use ($hasIndex) {
            if ($hasIndex('kuesioner_perawats', 'idx_kp_perawat_id')) $table->dropIndex('idx_kp_perawat_id');
            if ($hasIndex('kuesioner_perawats', 'idx_kp_kuesioner_perawat')) $table->dropIndex('idx_kp_kuesioner_perawat');
        });
        Schema::table('kuesioner_kliniks', function (Blueprint $table) use ($hasIndex) {
            if ($hasIndex('kuesioner_kliniks', 'idx_kk_kuesioner_id')) $table->dropIndex('idx_kk_kuesioner_id');
        });
        Schema::table('jawaban_kuesioner', function (Blueprint $table) use ($hasIndex) {
            if ($hasIndex('jawaban_kuesioner', 'idx_jk_kategori_nakes_kuesioner_nilai')) $table->dropIndex('idx_jk_kategori_nakes_kuesioner_nilai');
            if ($hasIndex('jawaban_kuesioner', 'idx_jk_kategori_nakes_pertanyaan')) $table->dropIndex('idx_jk_kategori_nakes_pertanyaan');
        });
        Schema::table('notifications', function (Blueprint $table) use ($hasIndex) {
            if ($hasIndex('notifications', 'idx_notif_user_read_date')) $table->dropIndex('idx_notif_user_read_date');
        });
        Schema::table('users', function (Blueprint $table) use ($hasIndex) {
            if ($hasIndex('users', 'idx_users_role_aktif')) $table->dropIndex('idx_users_role_aktif');
            if ($hasIndex('users', 'idx_users_role_name')) $table->dropIndex('idx_users_role_name');
        });
        Schema::table('pertanyaan_kuesioner', function (Blueprint $table) use ($hasIndex) {
            if ($hasIndex('pertanyaan_kuesioner', 'idx_pk_kategori_aktif_urutan')) $table->dropIndex('idx_pk_kategori_aktif_urutan');
        });
    }
};
