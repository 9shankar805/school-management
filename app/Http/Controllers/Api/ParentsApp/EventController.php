<?php

namespace App\Http\Controllers\Api\ParentsApp;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EventController extends Controller
{
    /**
     * GET /api/events
     */
    public function index(Request $request): JsonResponse
    {
        $schoolId = $request->user()->school_id ?? 1;

        $events = Event::where('school_id', $schoolId)
            ->orderBy('start', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $events,
        ]);
    }

    /**
     * GET /api/events/upcoming
     */
    public function upcoming(Request $request): JsonResponse
    {
        $schoolId = $request->user()->school_id ?? 1;

        $events = Event::where('school_id', $schoolId)
            ->where('start', '>=', now())
            ->orderBy('start', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $events,
        ]);
    }

    /**
     * GET /api/events/past
     */
    public function past(Request $request): JsonResponse
    {
        $schoolId = $request->user()->school_id ?? 1;

        $events = Event::where('school_id', $schoolId)
            ->where('end', '<', now())
            ->orderBy('start', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $events,
        ]);
    }

    /**
     * GET /api/events/type/{type}
     */
    public function byType(Request $request, $type): JsonResponse
    {
        $schoolId = $request->user()->school_id ?? 1;

        // Since Event doesn't have a type column, we'll do a basic filter on title
        $events = Event::where('school_id', $schoolId)
            ->where('title', 'LIKE', "%{$type}%")
            ->orderBy('start', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $events,
        ]);
    }

    /**
     * GET /api/events/month/{month}
     * Format: YYYY-MM
     */
    public function byMonth(Request $request, $month): JsonResponse
    {
        try {
            $parsedDate = Carbon::createFromFormat('Y-m', $month);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Invalid month format. Use YYYY-MM'], 400);
        }

        $schoolId = $request->user()->school_id ?? 1;

        $events = Event::where('school_id', $schoolId)
            ->whereYear('start', $parsedDate->year)
            ->whereMonth('start', $parsedDate->month)
            ->orderBy('start', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $events,
        ]);
    }

    /**
     * GET /api/events/{id}
     */
    public function show(Request $request, $id): JsonResponse
    {
        $schoolId = $request->user()->school_id ?? 1;

        $event = Event::where('school_id', $schoolId)->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => collect($event)->merge(['is_rsvped' => false]), // Mock RSVP status
        ]);
    }

    /**
     * POST /api/events/{id}/rsvp
     */
    public function rsvp(Request $request, $id): JsonResponse
    {
        $schoolId = $request->user()->school_id ?? 1;
        $event = Event::where('school_id', $schoolId)->findOrFail($id);

        // Mock RSVP creation
        return response()->json([
            'status'  => 'success',
            'message' => 'RSVP confirmed successfully (mocked).',
            'data'    => collect($event)->merge(['is_rsvped' => true]),
        ], 201);
    }

    /**
     * DELETE /api/events/{id}/rsvp
     */
    public function cancelRsvp(Request $request, $id): JsonResponse
    {
        $schoolId = $request->user()->school_id ?? 1;
        $event = Event::where('school_id', $schoolId)->findOrFail($id);

        // Mock RSVP cancellation
        return response()->json([
            'status'  => 'success',
            'message' => 'RSVP cancelled successfully (mocked).',
            'data'    => collect($event)->merge(['is_rsvped' => false]),
        ]);
    }

    /**
     * POST /api/events/{id}/reminder
     */
    public function reminder(Request $request, $id): JsonResponse
    {
        $schoolId = $request->user()->school_id ?? 1;
        $event = Event::where('school_id', $schoolId)->findOrFail($id);

        // Mock reminder setting
        return response()->json([
            'status'  => 'success',
            'message' => 'Reminder set successfully (mocked).',
            'data'    => $event,
        ]);
    }
}
