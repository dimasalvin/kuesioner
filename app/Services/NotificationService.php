<?php
// app/Services/NotificationService.php

namespace App\Services;

use App\Models\{Notification, User, Kuesioner};

class NotificationService
{
    /**
     * Buat notifikasi untuk semua admin & management
     * saat ada komplain baru masuk.
     */
    public static function createKomplainNotif(Kuesioner $kuesioner): void
    {
        // Ambil semua user dengan role administrator atau management
        $targets = User::whereIn('role', ['administrator', 'management'])
            ->where('aktif', true)
            ->get();

        foreach ($targets as $user) {
            Notification::create([
                'user_id'      => $user->id,
                'kuesioner_id' => $kuesioner->id,
                'judul'        => 'Komplain Baru Masuk',
                'pesan'        => 'Pasien atas nama ' . $kuesioner->nama
                                  . ' menyampaikan komplain: '
                                  . \Str::limit($kuesioner->komplain, 100),
                'read_at'      => null,
            ]);
        }
    }

    /**
     * Ambil notifikasi untuk user tertentu (untuk ditampilkan di topbar).
     */
    public static function getForUser(int $userId, int $limit = 10): \Illuminate\Support\Collection
    {
        return Notification::forUser($userId)
            ->with('kuesioner')
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Hitung notifikasi belum dibaca untuk user.
     */
    public static function unreadCount(int $userId): int
    {
        return Notification::forUser($userId)->unread()->count();
    }

    /**
     * Tandai semua notif user sebagai sudah dibaca.
     */
    public static function markAllRead(int $userId): void
    {
        Notification::forUser($userId)
            ->unread()
            ->update(['read_at' => now()]);
    }

    /**
     * Tandai satu notif sebagai dibaca.
     */
    public static function markRead(int $notifId, int $userId): void
    {
        Notification::where('id', $notifId)
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
