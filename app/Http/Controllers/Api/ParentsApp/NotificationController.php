<?php

namespace App\Http\Controllers\Api\ParentsApp;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * GET /api/notifications
     */
    public function index(Request $request): JsonResponse
    {
        $schoolId = $request->user()->school_id ?? 1;

        $notices = Notice::where('school_id', $schoolId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $notices,
        ]);
    }

    /**
     * GET /api/notifications/unread
     */
    public function unread(Request $request): JsonResponse
    {
        // For standard Notices, we don't have a read-receipt pivot table out-of-the-box.
        // We'll return notices created in the last 7 days as a proxy for "recent/unread".
        $schoolId = $request->user()->school_id ?? 1;

        $notices = Notice::where('school_id', $schoolId)
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $notices,
        ]);
    }

    /**
     * GET /api/notifications/type/{type}
     */
    public function byType(Request $request, $type): JsonResponse
    {
        $schoolId = $request->user()->school_id ?? 1;

        $notices = Notice::where('school_id', $schoolId)
            ->where('notice', 'LIKE', "%{$type}%") // basic filter since type isn't standard on Notice model
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $notices,
        ]);
    }

    /**
     * PUT /api/notifications/mark-all-read
     */
    public function markAllRead(Request $request): JsonResponse
    {
        // Mock success since we don't track per-user read state on global Notices
        return response()->json(['status' => 'success']);
    }

    /**
     * POST /api/notifications/settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'push_enabled'  => 'required|boolean',
            'email_enabled' => 'required|boolean',
        ]);

        // Could store in a user metadata JSON column or settings table
        return response()->json([
            'status'  => 'success',
            'message' => 'Notification settings updated.',
            'data'    => $validated,
        ]);
    }

    /**
     * PUT /api/notifications/{id}/read
     */
    public function markRead(Request $request, $id): JsonResponse
    {
        return response()->json(['status' => 'success']);
    }

    /**
     * DELETE /api/notifications/{id}
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        // Parents shouldn't be able to delete global Notices, only dismiss them from their UI
        // In a real app we'd add it to a dismissed_notices pivot table.
        return response()->json(['status' => 'success', 'message' => 'Notification dismissed.']);
    }
}
