<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * Cek notifikasi baru untuk admin
     * Dipanggil via polling dari frontend
     */
    public function checkNew(Request $request)
    {
        // Validasi user harus admin
        if (!auth()->check() || !auth()->user()->hasRole('Admin')) {
            return response()->json(['new' => false], 403);
        }

        // Ambil timestamp terakhir dari request
        $lastCheck = $request->input('last_check', now()->subMinutes(5)->timestamp);

        // Query notifikasi baru
        $notifications = DB::table('admin_notifications')
            ->where('created_at', '>', date('Y-m-d H:i:s', $lastCheck))
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->get(['notification_id as id', 'title', 'message', 'type', 'link', 'created_at']);

        return response()->json([
            'new' => $notifications->count() > 0,
            'notifications' => $notifications,
            'last_check' => now()->timestamp,
        ]);
    }

    /**
     * Tandai notifikasi sebagai sudah dibaca
     */
    public function markAsRead($id)
    {
        if (!auth()->check() || !auth()->user()->hasRole('Admin')) {
            return response()->json(['success' => false], 403);
        }

        DB::table('admin_notifications')
            ->where('notification_id', $id)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return response()->json(['success' => true]);
    }

    /**
     * Tandai semua notifikasi sebagai dibaca
     */
    public function markAllAsRead()
    {
        if (!auth()->check() || !auth()->user()->hasRole('Admin')) {
            return response()->json(['success' => false], 403);
        }

        DB::table('admin_notifications')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Get semua notifikasi unread
     */
    public function getUnread()
    {
        if (!auth()->check() || !auth()->user()->hasRole('Admin')) {
            return response()->json(['notifications' => []], 403);
        }

        $notifications = DB::table('admin_notifications')
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'notifications' => $notifications,
            'count' => $notifications->count(),
        ]);
    }
}
