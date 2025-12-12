<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    // Ambil daftar notifikasi
    public function index()
    {
        // Tandai semua notifikasi sebagai sudah dibaca saat halaman dibuka
        Notification::where('user_id', Auth::id())
                    ->where('is_read', 0)
                    ->update(['is_read' => 1]);

        $notifications = Notification::where('user_id', Auth::id())
                            ->orderBy('created_at', 'desc')
                            ->get();

        return response()->json(['success' => true, 'data' => $notifications]);
    }

    // Ambil jumlah belum dibaca (Untuk Badge)
    public function unreadCount()
    {
        $count = Notification::where('user_id', Auth::id())
                    ->where('is_read', 0)
                    ->count();

        return response()->json(['success' => true, 'count' => $count]);
    }

    // Tandai sudah dibaca
    public function markRead($id)
    {
        $notif = Notification::where('user_id', Auth::id())->where('id', $id)->first();
        if ($notif) {
            $notif->update(['is_read' => 1]);
        }
        return response()->json(['success' => true]);
    }
    
    // Tandai SEMUA sudah dibaca
    public function markAllRead()
    {
        Notification::where('user_id', Auth::id())->update(['is_read' => 1]);
        return response()->json(['success' => true]);
    }
}