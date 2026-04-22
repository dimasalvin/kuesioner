<?php
// database/migrations/2024_01_01_000004_create_jawaban_kuesioner_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jawaban_kuesioner', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kuesioner_id')
                  ->constrained('kuesioners')
                  ->cascadeOnDelete();
            $table->enum('kategori', ['klinik', 'dokter', 'perawat']);
            // null untuk klinik, diisi untuk dokter/perawat
            $table->unsignedBigInteger('nakes_id')->nullable();
            $table->foreignId('pertanyaan_id')
                  ->constrained('pertanyaan_kuesioner')
                  ->cascadeOnDelete();
            $table->tinyInteger('nilai')->unsigned(); // 1-5
            $table->timestamps();

            $table->index(['kuesioner_id', 'kategori']);
            $table->index(['kategori', 'nakes_id']);
            $table->index('pertanyaan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jawaban_kuesioner');
    }
};
