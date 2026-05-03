<?php
// database/migrations/2024_01_01_000006_refactor_to_json_jawaban.php
//
// REFACTOR: Migrasi dari kolom q1-q15 + tabel jawaban_kuesioner
//           ke JSON column + pre-calculated rata_rata.
// Mengurangi ~92% rows di database.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Schema, DB};

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Tambah kolom JSON + rata_rata ke tabel kuesioner ──────────

        Schema::table('kuesioner_kliniks', function (Blueprint $table) {
            $table->json('jawaban')->nullable()->after('kuesioner_id');
            $table->decimal('rata_rata', 4, 2)->default(0)->after('jawaban');
        });

        Schema::table('kuesioner_dokters', function (Blueprint $table) {
            $table->json('jawaban')->nullable()->after('dokter_id');
            $table->decimal('rata_rata', 4, 2)->default(0)->after('jawaban');
        });

        Schema::table('kuesioner_perawats', function (Blueprint $table) {
            $table->json('jawaban')->nullable()->after('perawat_id');
            $table->decimal('rata_rata', 4, 2)->default(0)->after('jawaban');
        });

        // ── 2. Migrasi data existing dari q1-q15 ke JSON ─────────────────

        // Klinik
        $kliniks = DB::table('kuesioner_kliniks')->get();
        foreach ($kliniks as $row) {
            $jawaban = [];
            $total = 0;
            $count = 0;
            for ($i = 1; $i <= 15; $i++) {
                $col = "q{$i}";
                if (isset($row->$col) && $row->$col > 0) {
                    $jawaban[(string)$i] = (int) $row->$col;
                    $total += (int) $row->$col;
                    $count++;
                }
            }
            $rataRata = $count > 0 ? round($total / $count, 2) : 0;
            DB::table('kuesioner_kliniks')->where('id', $row->id)->update([
                'jawaban'   => json_encode($jawaban),
                'rata_rata' => $rataRata,
            ]);
        }

        // Dokter
        $dokters = DB::table('kuesioner_dokters')->get();
        foreach ($dokters as $row) {
            $jawaban = [];
            $total = 0;
            $count = 0;
            for ($i = 1; $i <= 15; $i++) {
                $col = "q{$i}";
                if (isset($row->$col) && $row->$col > 0) {
                    $jawaban[(string)$i] = (int) $row->$col;
                    $total += (int) $row->$col;
                    $count++;
                }
            }
            $rataRata = $count > 0 ? round($total / $count, 2) : 0;
            DB::table('kuesioner_dokters')->where('id', $row->id)->update([
                'jawaban'   => json_encode($jawaban),
                'rata_rata' => $rataRata,
            ]);
        }

        // Perawat
        $perawats = DB::table('kuesioner_perawats')->get();
        foreach ($perawats as $row) {
            $jawaban = [];
            $total = 0;
            $count = 0;
            for ($i = 1; $i <= 15; $i++) {
                $col = "q{$i}";
                if (isset($row->$col) && $row->$col > 0) {
                    $jawaban[(string)$i] = (int) $row->$col;
                    $total += (int) $row->$col;
                    $count++;
                }
            }
            $rataRata = $count > 0 ? round($total / $count, 2) : 0;
            DB::table('kuesioner_perawats')->where('id', $row->id)->update([
                'jawaban'   => json_encode($jawaban),
                'rata_rata' => $rataRata,
            ]);
        }

        // ── 3. Hapus kolom q1-q15 ────────────────────────────────────────

        Schema::table('kuesioner_kliniks', function (Blueprint $table) {
            for ($i = 1; $i <= 15; $i++) {
                $table->dropColumn("q{$i}");
            }
        });

        Schema::table('kuesioner_dokters', function (Blueprint $table) {
            for ($i = 1; $i <= 15; $i++) {
                $table->dropColumn("q{$i}");
            }
        });

        Schema::table('kuesioner_perawats', function (Blueprint $table) {
            for ($i = 1; $i <= 15; $i++) {
                $table->dropColumn("q{$i}");
            }
        });

        // ── 4. Tambah index pada rata_rata ───────────────────────────────

        Schema::table('kuesioner_kliniks', function (Blueprint $table) {
            $table->index('rata_rata', 'idx_kk_rata_rata');
        });

        Schema::table('kuesioner_dokters', function (Blueprint $table) {
            $table->index(['dokter_id', 'rata_rata'], 'idx_kd_dokter_rata');
        });

        Schema::table('kuesioner_perawats', function (Blueprint $table) {
            $table->index(['perawat_id', 'rata_rata'], 'idx_kp_perawat_rata');
        });

        // ── 5. Drop tabel jawaban_kuesioner (sudah tidak diperlukan) ─────

        Schema::dropIfExists('jawaban_kuesioner');
    }

    public function down(): void
    {
        // Recreate jawaban_kuesioner
        Schema::create('jawaban_kuesioner', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kuesioner_id')->constrained('kuesioners')->cascadeOnDelete();
            $table->enum('kategori', ['klinik', 'dokter', 'perawat']);
            $table->unsignedBigInteger('nakes_id')->nullable();
            $table->foreignId('pertanyaan_id')->constrained('pertanyaan_kuesioner')->cascadeOnDelete();
            $table->tinyInteger('nilai')->unsigned();
            $table->timestamps();
            $table->index(['kuesioner_id', 'kategori']);
            $table->index(['kategori', 'nakes_id']);
            $table->index('pertanyaan_id');
        });

        // Drop new columns & re-add q1-q15
        Schema::table('kuesioner_kliniks', function (Blueprint $table) {
            $table->dropIndex('idx_kk_rata_rata');
            $table->dropColumn(['jawaban', 'rata_rata']);
            for ($i = 1; $i <= 15; $i++) {
                $table->tinyInteger("q{$i}")->unsigned()->default(0);
            }
        });

        Schema::table('kuesioner_dokters', function (Blueprint $table) {
            $table->dropIndex('idx_kd_dokter_rata');
            $table->dropColumn(['jawaban', 'rata_rata']);
            for ($i = 1; $i <= 15; $i++) {
                $table->tinyInteger("q{$i}")->unsigned()->default(0);
            }
        });

        Schema::table('kuesioner_perawats', function (Blueprint $table) {
            $table->dropIndex('idx_kp_perawat_rata');
            $table->dropColumn(['jawaban', 'rata_rata']);
            for ($i = 1; $i <= 15; $i++) {
                $table->tinyInteger("q{$i}")->unsigned()->default(0);
            }
        });
    }
};
