<?php
// app/Models/Kuesioner.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Kuesioner extends Model
{
    protected $fillable = ['nama', 'no_telp', 'has_complain', 'komplain'];

    public function klinik()       { return $this->hasOne(KuesionerKlinik::class); }
    public function dokterRel()    { return $this->hasOne(KuesionerDokter::class); }
    public function perawatRel()   { return $this->hasOne(KuesionerPerawat::class); }

    public function scopeWhereHasComplain($query)
    {
        return $query->where('has_complain', 1)->whereNotNull('komplain');
    }
}
