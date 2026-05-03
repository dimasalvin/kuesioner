<?php
// app/Models/KuesionerDokter.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class KuesionerDokter extends Model
{
    protected $table = 'kuesioner_dokters';
    protected $guarded = [];
    protected $casts = [
        'jawaban'   => 'array',
        'rata_rata' => 'float',
    ];

    public function kuesioner() { return $this->belongsTo(Kuesioner::class); }
    public function dokter()    { return $this->belongsTo(Dokter::class); }

    public function getRataRataAttribute($value): float
    {
        return round((float) $value, 2);
    }
}
