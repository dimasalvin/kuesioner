<?php
// database/migrations/2024_01_01_000003_create_pertanyaan_and_notifications.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Tabel master pertanyaan kuesioner ────────────────────
        Schema::create('pertanyaan_kuesioner', function (Blueprint $table) {
            $table->id();
            // 'klinik' | 'dokter' | 'perawat'
            $table->enum('kategori', ['klinik', 'dokter', 'perawat']);
            $table->string('teks');                  // isi pertanyaan
            $table->unsignedSmallInteger('urutan');  // urutan tampil (1,2,3...)
            $table->boolean('aktif')->default(true); // tampil/sembunyi di form
            $table->timestamps();
        });

        // ── Tabel notifikasi per-user ─────────────────────────────
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('kuesioner_id')->constrained('kuesioners')->cascadeOnDelete();
            $table->string('judul');
            $table->text('pesan');
            $table->timestamp('read_at')->nullable(); // null = belum dibaca
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('pertanyaan_kuesioner');
    }
};
