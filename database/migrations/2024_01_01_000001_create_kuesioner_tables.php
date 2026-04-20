<?php
// database/migrations/2024_01_01_000001_create_kuesioner_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Dokter ────────────────────────────────────────────────
        Schema::create('dokters', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('spesialisasi')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        // ── Perawat ───────────────────────────────────────────────
        Schema::create('perawats', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        // ── Main Kuesioner ────────────────────────────────────────
        Schema::create('kuesioners', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('no_telp');
            $table->tinyInteger('has_complain')->nullable(); // 0=tidak, 1=ya
            $table->text('komplain')->nullable();
            $table->timestamps();
        });

        // ── Kuesioner Klinik ──────────────────────────────────────
        Schema::create('kuesioner_kliniks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kuesioner_id')->constrained('kuesioners')->cascadeOnDelete();
            for ($i = 1; $i <= 15; $i++) {
                $table->tinyInteger("q{$i}")->unsigned()->comment("Penilaian pertanyaan {$i}");
            }
            $table->timestamps();
        });

        // ── Kuesioner Dokter ──────────────────────────────────────
        Schema::create('kuesioner_dokters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kuesioner_id')->constrained('kuesioners')->cascadeOnDelete();
            $table->foreignId('dokter_id')->constrained('dokters');
            for ($i = 1; $i <= 15; $i++) {
                $table->tinyInteger("q{$i}")->unsigned()->comment("Penilaian pertanyaan {$i}");
            }
            $table->text('kritik_saran')->nullable();
            $table->timestamps();
        });

        // ── Kuesioner Perawat ─────────────────────────────────────
        Schema::create('kuesioner_perawats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kuesioner_id')->constrained('kuesioners')->cascadeOnDelete();
            $table->foreignId('perawat_id')->constrained('perawats');
            for ($i = 1; $i <= 15; $i++) {
                $table->tinyInteger("q{$i}")->unsigned()->comment("Penilaian pertanyaan {$i}");
            }
            $table->text('kritik_saran')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kuesioner_perawats');
        Schema::dropIfExists('kuesioner_dokters');
        Schema::dropIfExists('kuesioner_kliniks');
        Schema::dropIfExists('kuesioners');
        Schema::dropIfExists('perawats');
        Schema::dropIfExists('dokters');
    }
};
