<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * GET /api/v1/notifications
     * Returns paginated notifications for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->paginate($request->integer('per_page', 20));

        return response()->json([
            'status' => 'success',
            'data'   => $notifications->items(),
            'meta'   => [
                'total'        => $notifications->total(),
                'unread_count' => $request->user()->unreadNotifications()->count(),
                'current_page' => $notifications->currentPage(),
                'last_page'    => $notifications->lastPage(),
            ],
        ]);
    }

    /**
     * POST /api/v1/notifications/{id}/read
     */
    public function markRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['status' => 'success', 'message' => 'Notification marked as read.']);
    }

    /**
     * POST /api/v1/notifications/read-all
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['status' => 'success', 'message' => 'All notifications marked as read.']);
    }

    /**
     * DELETE /api/v1/notifications/{id}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $request->user()->notifications()->findOrFail($id)->delete();

        return response()->json(['status' => 'success', 'message' => 'Notification deleted.']);
    }
}
