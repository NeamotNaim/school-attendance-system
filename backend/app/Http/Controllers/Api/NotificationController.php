<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * Get notifications for authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        
        $notifications = Notification::forUser($userId)
            ->recent(30) // Last 30 days
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        $unreadCount = Notification::forUser($userId)
            ->unread()
            ->count();

        return response()->json([
            'data' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Get unread count
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        
        $count = Notification::forUser($userId)
            ->unread()
            ->count();

        return response()->json([
            'count' => $count,
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, $id): JsonResponse
    {
        $notification = Notification::findOrFail($id);
        
        // Check if user has access to this notification
        if ($notification->user_id && $notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read',
            'data' => $notification,
        ]);
    }

    /**
     * Mark all as read
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        
        Notification::forUser($userId)
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'message' => 'All notifications marked as read',
        ]);
    }

    /**
     * Delete notification
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $notification = Notification::findOrFail($id);
        
        // Check if user has access to this notification
        if ($notification->user_id && $notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $notification->delete();

        return response()->json([
            'message' => 'Notification deleted',
        ]);
    }

    /**
     * Clear all read notifications
     */
    public function clearRead(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        
        Notification::forUser($userId)
            ->where('is_read', true)
            ->delete();

        return response()->json([
            'message' => 'Read notifications cleared',
        ]);
    }
}
