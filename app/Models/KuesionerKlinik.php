<?php
// app/Models/KuesionerKlinik.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class KuesionerKlinik extends Model
{
    protected $table = 'kuesioner_kliniks';
    protected $guarded = [];

    public function kuesioner() { return $this->belongsTo(Kuesioner::class); }

    // Helper: rata-rata semua pertanyaan
    public function rataRata(): float
    {
        $total = 0;
        for ($i = 1; $i <= 15; $i++) $total += $this->{"q{$i}"};
        return round($total / 15, 2);
    }
}
