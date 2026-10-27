<?php

namespace App\Http\Controllers\Api\DriverApp;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**
     * GET /driver-app/notifications
     * All notifications for the authenticated driver's user account.
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
     * PUT /driver-app/notifications/{id}/read
     * Mark a single notification as read.
     */
    public function markRead(Request $request, string $id): JsonResponse
    {
        DatabaseNotification::where('notifiable_id', $request->user()->id)
            ->where('notifiable_type', get_class($request->user()))
            ->findOrFail($id)
            ->markAsRead();

        return response()->json(['status' => 'success', 'message' => 'Notification marked as read.']);
    }

    /**
     * PUT /driver-app/notifications/mark-all-read
     * Mark all notifications as read.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['status' => 'success', 'message' => 'All notifications marked as read.']);
    }

    /**
     * POST /driver-app/notifications/device-token
     * Register FCM device token for push notifications.
     */
    public function registerDeviceToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token'    => ['required', 'string'],
            'platform' => ['required', 'in:android,ios'],
        ]);

        DeviceToken::updateOrCreate(
            ['token' => $validated['token']],
            [
                'user_id'   => $request->user()->id,
                'platform'  => $validated['platform'],
                'school_id' => $request->user()->school_id,
            ]
        );

        return response()->json(['status' => 'success', 'message' => 'Device token registered.']);
    }
}
