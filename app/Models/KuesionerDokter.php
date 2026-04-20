<?php
// app/Models/KuesionerDokter.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class KuesionerDokter extends Model
{
    protected $table = 'kuesioner_dokters';
    protected $guarded = [];

    public function kuesioner() { return $this->belongsTo(Kuesioner::class); }
    public function dokter()    { return $this->belongsTo(Dokter::class); }

    public function rataRata(): float
    {
        $total = 0;
        for ($i = 1; $i <= 15; $i++) $total += $this->{"q{$i}"};
        return round($total / 15, 2);
    }
}
