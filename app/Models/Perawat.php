<?php
// app/Models/Perawat.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Perawat extends Model
{
    protected $fillable = ['nama', 'aktif'];
    protected $table = 'perawats';
}
