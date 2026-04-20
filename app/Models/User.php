<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'tipe_nakes', 'nakes_id', 'aktif'];
    protected $hidden   = ['password', 'remember_token'];
    protected $casts    = ['password' => 'hashed', 'aktif' => 'boolean'];

    // ── Role helpers ─────────────────────────────────────────────
    public function isAdmin():      bool { return $this->role === 'administrator'; }
    public function isManagement(): bool { return $this->role === 'management'; }
    public function isUser():       bool { return $this->role === 'user'; }
    public function isDokter():     bool { return $this->role === 'user' && $this->tipe_nakes === 'dokter'; }
    public function isPerawat():    bool { return $this->role === 'user' && $this->tipe_nakes === 'perawat'; }

    // ── Relations ─────────────────────────────────────────────────
    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'nakes_id');
    }

    public function perawat()
    {
        return $this->belongsTo(Perawat::class, 'nakes_id');
    }

    // ── Convenience: get linked nakes name ───────────────────────
    public function getNakesNameAttribute(): ?string
    {
        if ($this->isDokter())  return $this->dokter?->nama;
        if ($this->isPerawat()) return $this->perawat?->nama;
        return null;
    }
}
