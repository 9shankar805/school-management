<?php

namespace App\Http\Controllers\Api\TeacherApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    /**
     * GET /api/teacher-app/support/faqs
     */
    public function faqs(Request $request): JsonResponse
    {
        return response()->json([
            'faqs' => [
                [
                    'question' => 'How do I reset my password?',
                    'answer' => 'Go to Settings -> Security -> Change Password.'
                ],
                [
                    'question' => 'How do I take attendance?',
                    'answer' => 'Navigate to the Attendance tab and select your class.'
                ]
            ]
        ]);
    }

    /**
     * POST /api/teacher-app/support/contact
     */
    public function contact(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Support ticket created successfully.',
            'ticket' => array_merge(['id' => rand(1000, 9999), 'status' => 'open'], $validated)
        ]);
    }

    /**
     * GET /api/teacher-app/support/tickets
     */
    public function tickets(Request $request): JsonResponse
    {
        return response()->json([
            'tickets' => [
                [
                    'id' => 1024,
                    'subject' => 'App crashes when opening gradebook',
                    'status' => 'open',
                    'created_at' => '2026-08-04T10:00:00Z'
                ],
                [
                    'id' => 980,
                    'subject' => 'Cannot view student profile',
                    'status' => 'resolved',
                    'created_at' => '2026-07-20T14:30:00Z'
                ]
            ]
        ]);
    }

    /**
     * GET /api/teacher-app/support/tickets/{id}
     */
    public function showTicket(Request $request, $id): JsonResponse
    {
        return response()->json([
            'ticket' => [
                'id' => $id,
                'subject' => 'App crashes when opening gradebook',
                'message' => 'Every time I try to open the 10th grade math gradebook, the app closes.',
                'status' => 'open',
                'responses' => [
                    [
                        'sender' => 'Support Team',
                        'message' => 'We are looking into this issue right now. Please ensure you are on version 1.0.1.',
                        'created_at' => '2026-08-04T11:00:00Z'
                    ]
                ],
                'created_at' => '2026-08-04T10:00:00Z'
            ]
        ]);
    }
}
