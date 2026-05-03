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
        // Ambil hanya ID user target (lebih ringan dari get() full model)
        $targetIds = User::whereIn('role', ['administrator', 'management'])
            ->where('aktif', true)
            ->pluck('id');

        if ($targetIds->isEmpty()) return;

        $now   = now();
        $pesan = 'Pasien atas nama ' . $kuesioner->nama
                 . ' menyampaikan komplain: '
                 . \Str::limit($kuesioner->komplain, 100);

        // 1 bulk insert menggantikan N individual INSERT
        $rows = $targetIds->map(fn($userId) => [
            'user_id'      => $userId,
            'kuesioner_id' => $kuesioner->id,
            'judul'        => 'Komplain Baru Masuk',
            'pesan'        => $pesan,
            'read_at'      => null,
            'created_at'   => $now,
            'updated_at'   => $now,
        ])->toArray();

        Notification::insert($rows);
    }

    /**
     * Ambil notifikasi untuk user tertentu (untuk ditampilkan di topbar).
     * Optimasi: tanpa eager load kuesioner (tidak dipakai di dropdown).
     */
    public static function getForUser(int $userId, int $limit = 10): \Illuminate\Support\Collection
    {
        return Notification::forUser($userId)
            ->select(['id', 'user_id', 'kuesioner_id', 'judul', 'pesan', 'read_at', 'created_at'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Hitung notifikasi belum dibaca untuk user.
     * Di-cache 15 detik agar tidak query setiap page load.
     */
    public static function unreadCount(int $userId): int
    {
        return \Illuminate\Support\Facades\Cache::remember(
            "notif:unread:{$userId}",
            15,
            fn() => Notification::forUser($userId)->unread()->count()
        );
    }

    /**
     * Invalidate unread count cache untuk user.
     */
    public static function clearUnreadCache(int $userId): void
    {
        \Illuminate\Support\Facades\Cache::forget("notif:unread:{$userId}");
    }

    /**
     * Tandai semua notif user sebagai sudah dibaca.
     */
    public static function markAllRead(int $userId): void
    {
        Notification::forUser($userId)
            ->unread()
            ->update(['read_at' => now()]);
        self::clearUnreadCache($userId);
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
        self::clearUnreadCache($userId);
    }
}
