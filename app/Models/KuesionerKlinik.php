<?php
// app/Models/KuesionerKlinik.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class KuesionerKlinik extends Model
{
    protected $table = 'kuesioner_kliniks';
    protected $guarded = [];
    protected $casts = [
        'jawaban'   => 'array',
        'rata_rata' => 'float',
    ];

    public function kuesioner() { return $this->belongsTo(Kuesioner::class); }

    public function getRataRataAttribute($value): float
    {
        return round((float) $value, 2);
    }
}
