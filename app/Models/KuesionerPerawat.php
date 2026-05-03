<?php
// app/Models/KuesionerPerawat.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class KuesionerPerawat extends Model
{
    protected $table = 'kuesioner_perawats';
    protected $guarded = [];
    protected $casts = [
        'jawaban'   => 'array',
        'rata_rata' => 'float',
    ];

    public function kuesioner() { return $this->belongsTo(Kuesioner::class); }
    public function perawat()   { return $this->belongsTo(Perawat::class); }

    public function getRataRataAttribute($value): float
    {
        return round((float) $value, 2);
    }
}
