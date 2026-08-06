<?php

namespace App\Http\Controllers\Api\TeacherApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    /**
     * GET /api/teacher-app/leaves
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'leaves' => [
                [
                    'id' => 1,
                    'leave_type' => 'sick',
                    'start_date' => '2026-07-01',
                    'end_date' => '2026-07-02',
                    'status' => 'approved',
                    'reason' => 'Fever'
                ],
                [
                    'id' => 2,
                    'leave_type' => 'casual',
                    'start_date' => '2026-08-25',
                    'end_date' => '2026-08-26',
                    'status' => 'pending',
                    'reason' => 'Personal work'
                ]
            ]
        ]);
    }

    /**
     * POST /api/teacher-app/leaves
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'leave_type' => 'required|string|in:sick,casual,maternity,other',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Leave request submitted successfully.',
            'leave' => array_merge(['id' => rand(10, 100), 'status' => 'pending'], $validated)
        ]);
    }

    /**
     * GET /api/teacher-app/leaves/{id}
     */
    public function show(Request $request, $id): JsonResponse
    {
        return response()->json([
            'leave' => [
                'id' => $id,
                'leave_type' => 'casual',
                'start_date' => '2026-08-25',
                'end_date' => '2026-08-26',
                'status' => 'pending',
                'reason' => 'Personal work',
                'created_at' => '2026-08-01T10:00:00Z'
            ]
        ]);
    }

    /**
     * PUT /api/teacher-app/leaves/{id}/status
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:cancelled'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Leave status updated successfully.'
        ]);
    }
}
