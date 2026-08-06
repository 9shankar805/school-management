<?php

namespace App\Http\Controllers\Api\TeacherApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * GET /api/teacher-app/notifications
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'notifications' => [
                [
                    'id' => 1,
                    'title' => 'New message from Principal',
                    'body' => 'Please review the updated school policies.',
                    'is_read' => false,
                    'created_at' => '2026-08-04T09:00:00Z',
                    'type' => 'message'
                ],
                [
                    'id' => 2,
                    'title' => 'Assignment Graded',
                    'body' => 'All students have submitted Algebra Chapter 1.',
                    'is_read' => true,
                    'created_at' => '2026-08-03T10:00:00Z',
                    'type' => 'alert'
                ]
            ]
        ]);
    }

    /**
     * PUT /api/teacher-app/notifications/{id}/read
     */
    public function markAsRead(Request $request, $id): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Notification marked as read.'
        ]);
    }

    /**
     * PUT /api/teacher-app/notifications/read-all
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'All notifications marked as read.'
        ]);
    }

    /**
     * GET /api/teacher-app/notifications/unread-count
     */
    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'unread_count' => 1
        ]);
    }

    /**
     * DELETE /api/teacher-app/notifications/{id}
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Notification deleted successfully.'
        ]);
    }
}
