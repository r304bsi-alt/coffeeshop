<?php

namespace App\Http\Controllers;

use App\Models\ShopNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Poll unread notifications for a specific role (dapur, kasir, owner).
     */
    public function poll(Request $request): JsonResponse
    {
        $role = $request->query('role');
        $sinceId = (int) $request->query('since_id', 0);

        $query = ShopNotification::query();

        if ($role) {
            $query->where('target_role', $role);
        }

        if ($sinceId > 0) {
            $query->where('id', '>', $sinceId);
        } else {
            // Get unread or latest 10
            $query->where('is_read', false)->latest()->take(10);
        }

        $notifications = $query->orderBy('id', 'asc')->get();

        return response()->json([
            'count' => $notifications->count(),
            'notifications' => $notifications,
            'latest_id' => $notifications->max('id') ?? $sinceId,
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(ShopNotification $notification): JsonResponse
    {
        $notification->update(['is_read' => true]);
        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read for a role.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $role = $request->input('role');
        if ($role) {
            ShopNotification::where('target_role', $role)->update(['is_read' => true]);
        }
        return response()->json(['success' => true]);
    }
}
