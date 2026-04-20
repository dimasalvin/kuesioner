<?php
// app/Models/Notification.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = ['user_id', 'kuesioner_id', 'judul', 'pesan', 'read_at'];
    protected $casts    = ['read_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kuesioner()
    {
        return $this->belongsTo(Kuesioner::class);
    }

    public function scopeUnread($q)
    {
        return $q->whereNull('read_at');
    }

    public function scopeForUser($q, int $userId)
    {
        return $q->where('user_id', $userId);
    }

    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }
}
