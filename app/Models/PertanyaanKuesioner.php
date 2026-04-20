<?php
// app/Models/PertanyaanKuesioner.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PertanyaanKuesioner extends Model
{
    protected $table    = 'pertanyaan_kuesioner';
    protected $fillable = ['kategori', 'teks', 'urutan', 'aktif'];
    protected $casts    = ['aktif' => 'boolean'];

    public function scopeAktif($q)
    {
        return $q->where('aktif', true)->orderBy('urutan');
    }

    public function scopeKategori($q, string $kat)
    {
        return $q->where('kategori', $kat);
    }
}
