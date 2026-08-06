<?php

namespace App\Http\Controllers\Api\ParentsApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
    /**
     * GET /api/meetings
     */
    public function index(Request $request): JsonResponse
    {
        // Mock meetings
        return response()->json([
            'status' => 'success',
            'data'   => $this->getMockedMeetings(),
        ]);
    }

    /**
     * POST /api/meetings
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'child_id'   => 'required|integer',
            'teacher_id' => 'required|integer',
            'date_time'  => 'required|date',
            'reason'     => 'required|string',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Meeting requested successfully (mocked).',
            'data'    => array_merge([
                'id'     => rand(100, 999),
                'status' => 'pending'
            ], $validated),
        ], 201);
    }

    /**
     * GET /api/meetings/upcoming
     */
    public function upcoming(Request $request): JsonResponse
    {
        $meetings = collect($this->getMockedMeetings())->filter(function($meeting) {
            return \Carbon\Carbon::parse($meeting['date_time'])->isFuture();
        })->values();

        return response()->json([
            'status' => 'success',
            'data'   => $meetings,
        ]);
    }

    /**
     * GET /api/meetings/past
     */
    public function past(Request $request): JsonResponse
    {
        $meetings = collect($this->getMockedMeetings())->filter(function($meeting) {
            return \Carbon\Carbon::parse($meeting['date_time'])->isPast();
        })->values();

        return response()->json([
            'status' => 'success',
            'data'   => $meetings,
        ]);
    }

    /**
     * GET /api/meetings/available-slots
     */
    public function availableSlots(Request $request): JsonResponse
    {
        $teacherId = $request->query('teacher_id');
        if (!$teacherId) {
            return response()->json(['status' => 'error', 'message' => 'teacher_id is required'], 400);
        }

        // Mock available slots
        $slots = collect();
        $date = now()->addDay()->startOfDay()->addHours(14); // 2 PM tomorrow

        for ($i = 0; $i < 5; $i++) {
            $slots->push($date->copy()->addMinutes($i * 30)->format('Y-m-d H:i:s'));
        }

        return response()->json([
            'status' => 'success',
            'data'   => $slots,
        ]);
    }

    /**
     * GET /api/meetings/child/{childId}
     */
    public function byChild(Request $request, $childId): JsonResponse
    {
        $meetings = collect($this->getMockedMeetings())->filter(function($meeting) use ($childId) {
            return $meeting['child_id'] == $childId;
        })->values();

        return response()->json([
            'status' => 'success',
            'data'   => $meetings,
        ]);
    }

    /**
     * GET /api/meetings/{id}
     */
    public function show(Request $request, $id): JsonResponse
    {
        $meeting = collect($this->getMockedMeetings())->firstWhere('id', $id);

        if (!$meeting) {
            return response()->json(['status' => 'error', 'message' => 'Meeting not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $meeting,
        ]);
    }

    /**
     * PUT /api/meetings/{id}/reschedule
     */
    public function reschedule(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'new_date_time' => 'required|date',
        ]);

        $meeting = collect($this->getMockedMeetings())->firstWhere('id', $id);

        if (!$meeting) {
            return response()->json(['status' => 'error', 'message' => 'Meeting not found'], 404);
        }

        $meeting['date_time'] = $validated['new_date_time'];
        $meeting['status'] = 'pending'; // Re-pending after reschedule

        return response()->json([
            'status'  => 'success',
            'message' => 'Meeting rescheduled successfully.',
            'data'    => $meeting,
        ]);
    }

    /**
     * PUT /api/meetings/{id}/cancel
     */
    public function cancel(Request $request, $id): JsonResponse
    {
        $meeting = collect($this->getMockedMeetings())->firstWhere('id', $id);

        if (!$meeting) {
            return response()->json(['status' => 'error', 'message' => 'Meeting not found'], 404);
        }

        $meeting['status'] = 'cancelled';

        return response()->json([
            'status'  => 'success',
            'message' => 'Meeting cancelled.',
            'data'    => $meeting,
        ]);
    }

    /**
     * PUT /api/meetings/{id}/confirm
     */
    public function confirm(Request $request, $id): JsonResponse
    {
        return response()->json([
            'status'  => 'error', 
            'message' => 'Parents cannot confirm meetings, only teachers/admins can.'
        ], 403);
    }

    private function getMockedMeetings(): array
    {
        return [
            [
                'id'         => 1,
                'child_id'   => 1,
                'teacher_id' => 2,
                'teacher_name' => 'John Doe',
                'date_time'  => now()->addDays(2)->format('Y-m-d 14:00:00'),
                'reason'     => 'Discuss academic performance',
                'status'     => 'confirmed',
                'link'       => 'https://zoom.us/j/123456789', // Virtual meeting link
            ],
            [
                'id'         => 2,
                'child_id'   => 1,
                'teacher_id' => 3,
                'teacher_name' => 'Jane Smith',
                'date_time'  => now()->subDays(5)->format('Y-m-d 10:00:00'),
                'reason'     => 'Behavioral review',
                'status'     => 'completed',
                'link'       => null, // In-person
            ],
        ];
    }
}
