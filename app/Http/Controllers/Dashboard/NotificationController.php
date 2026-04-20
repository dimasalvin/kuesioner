<?php
// app/Http/Controllers/Dashboard/NotificationController.php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // Tandai semua sebagai dibaca (dipanggil saat buka dropdown)
    public function markAllRead()
    {
        NotificationService::markAllRead(auth()->id());
        return response()->json(['message' => 'ok']);
    }

    // Tandai satu notif sebagai dibaca
    public function markRead(Request $request)
    {
        $request->validate(['id' => 'required|integer']);
        NotificationService::markRead($request->id, auth()->id());
        return response()->json(['message' => 'ok']);
    }
}
