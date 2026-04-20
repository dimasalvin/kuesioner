<?php
// app/Models/Dokter.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Dokter extends Model
{
    protected $fillable = ['nama', 'spesialisasi', 'aktif'];
    protected $table = 'dokters';

    public function kuesionerDokter() { return $this->hasMany(KuesionerDokter::class); }
}
