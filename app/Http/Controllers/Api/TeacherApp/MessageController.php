<?php

namespace App\Http\Controllers\Api\TeacherApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * GET /api/teacher-app/messages
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'messages' => [
                [
                    'id' => 1,
                    'sender' => 'Admin',
                    'subject' => 'Staff Meeting Tomorrow',
                    'snippet' => 'Please be advised that the staff meeting...',
                    'is_read' => false,
                    'created_at' => '2026-08-04T08:00:00Z'
                ],
                [
                    'id' => 2,
                    'sender' => 'Babita devi yadav',
                    'subject' => 'Question about math homework',
                    'snippet' => 'Hello Mr. Smith, could you clarify...',
                    'is_read' => true,
                    'created_at' => '2026-08-03T15:30:00Z'
                ]
            ]
        ]);
    }

    /**
     * POST /api/teacher-app/messages
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'recipient_id' => 'required|integer',
            'subject' => 'required|string|max:255',
            'body' => 'required|string'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Message sent successfully.',
            'data' => array_merge(['id' => rand(10, 100), 'is_read' => false], $validated)
        ]);
    }

    /**
     * GET /api/teacher-app/messages/{id}
     */
    public function show(Request $request, $id): JsonResponse
    {
        return response()->json([
            'message' => [
                'id' => $id,
                'sender' => 'Admin',
                'subject' => 'Staff Meeting Tomorrow',
                'body' => 'Please be advised that the staff meeting is moved to 9 AM in the main hall.',
                'is_read' => true,
                'created_at' => '2026-08-04T08:00:00Z'
            ]
        ]);
    }

    /**
     * PUT /api/teacher-app/messages/{id}/read
     */
    public function markAsRead(Request $request, $id): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Message marked as read.'
        ]);
    }

    /**
     * DELETE /api/teacher-app/messages/{id}
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Message deleted successfully.'
        ]);
    }
}
