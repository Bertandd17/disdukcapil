<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);
        $offset = $request->get('offset', 0);

        $notifications = AdminNotification::query()
            ->orderBy('created_at', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();

        $unreadCount = AdminNotification::unread()->count();

        return response()->json([
            'success' => true,
            'data' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->notification_id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'link' => $notification->link,
                    'is_read' => $notification->is_read,
                    'icon' => $notification->icon,
                    'badge_color' => $notification->badge_color,
                    'time_ago' => $notification->time_ago,
                    'created_at' => $notification->created_at->toISOString(),
                ];
            }),
            'unread_count' => $unreadCount,
            'total_count' => AdminNotification::count(),
        ]);
    }

    public function unread(): JsonResponse
    {
        $notifications = AdminNotification::unread()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->notification_id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'link' => $notification->link,
                    'is_read' => $notification->is_read,
                    'icon' => $notification->icon,
                    'badge_color' => $notification->badge_color,
                    'time_ago' => $notification->time_ago,
                    'created_at' => $notification->created_at->toISOString(),
                ];
            }),
            'unread_count' => $notifications->count(),
        ]);
    }

    public function count(): JsonResponse
    {
        $count = AdminNotification::unread()->count();

        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }

    public function markAsRead(Request $request): JsonResponse
    {
        $notificationId = $request->get('notification_id');

        if ($notificationId) {
            $notification = AdminNotification::find($notificationId);
            if ($notification) {
                $notification->markAsRead();
            }
        } else {
            AdminNotification::unread()->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'unread_count' => AdminNotification::unread()->count(),
        ]);
    }

    public static function createNotification(
        string $type,
        string $title,
        string $message,
        ?string $link = null,
        ?string $userId = null
    ): AdminNotification {
        return AdminNotification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'is_read' => false,
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $notificationId = $request->get('notification_id');

        if ($notificationId) {
            $notification = AdminNotification::find($notificationId);
            if ($notification) {
                $notification->delete();
            }
        }

        return response()->json([
            'success' => true,
            'unread_count' => AdminNotification::unread()->count(),
        ]);
    }

    public function clearRead(): JsonResponse
    {
        AdminNotification::read()->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
