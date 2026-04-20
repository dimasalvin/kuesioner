<?php
// app/Models/KuesionerPerawat.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class KuesionerPerawat extends Model
{
    protected $table = 'kuesioner_perawats';
    protected $guarded = [];

    public function kuesioner() { return $this->belongsTo(Kuesioner::class); }
    public function perawat()   { return $this->belongsTo(Perawat::class); }

    public function rataRata(): float
    {
        $total = 0;
        for ($i = 1; $i <= 15; $i++) $total += $this->{"q{$i}"};
        return round($total / 15, 2);
    }
}
