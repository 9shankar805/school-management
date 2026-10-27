<?php

namespace App\Http\Controllers\Api\StudentApp;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**
     * GET /student-app/notifications
     * All notifications for the authenticated student.
     */
    public function index(Request $request): JsonResponse
    {
        $notifications = DatabaseNotification::where('notifiable_id', $request->user()->id)
            ->where('notifiable_type', get_class($request->user()))
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'data'   => $notifications->items(),
            'meta'   => [
                'total'        => $notifications->total(),
                'current_page' => $notifications->currentPage(),
                'last_page'    => $notifications->lastPage(),
            ],
        ]);
    }

    /**
     * GET /student-app/notifications/unread
     * Unread notifications only.
     */
    public function unread(Request $request): JsonResponse
    {
        $notifications = DatabaseNotification::where('notifiable_id', $request->user()->id)
            ->where('notifiable_type', get_class($request->user()))
            ->whereNull('read_at')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $notifications,
            'count'  => $notifications->count(),
        ]);
    }

    /**
     * PUT /student-app/notifications/{id}/read
     * Mark a single notification as read.
     */
    public function markRead(Request $request, string $id): JsonResponse
    {
        $notification = DatabaseNotification::where('notifiable_id', $request->user()->id)
            ->where('notifiable_type', get_class($request->user()))
            ->findOrFail($id);

        $notification->markAsRead();

        return response()->json([
            'status'  => 'success',
            'message' => 'Notification marked as read.',
        ]);
    }

    /**
     * PUT /student-app/notifications/mark-all-read
     * Mark all notifications as read.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json([
            'status'  => 'success',
            'message' => 'All notifications marked as read.',
        ]);
    }

    /**
     * DELETE /student-app/notifications/{id}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        DatabaseNotification::where('notifiable_id', $request->user()->id)
            ->where('notifiable_type', get_class($request->user()))
            ->findOrFail($id)
            ->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Notification deleted.',
        ]);
    }

    /**
     * POST /student-app/notifications/device-token
     * Register FCM push notification device token.
     */
    public function registerDeviceToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token'    => ['required', 'string'],
            'platform' => ['required', 'in:android,ios,web'],
        ]);

        DeviceToken::updateOrCreate(
            ['token' => $validated['token']],
            [
                'user_id'   => $request->user()->id,
                'platform'  => $validated['platform'],
                'school_id' => $request->user()->school_id,
            ]
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Device token registered.',
        ]);
    }
}
