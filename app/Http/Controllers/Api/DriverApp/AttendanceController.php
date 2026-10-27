<?php

namespace App\Http\Controllers\Api\DriverApp;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\TransportAttendance;
use App\Models\TransportRoute;
use App\Models\StudentTransport;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    /**
     * GET /driver-app/attendance/today
     * Today's attendance sheet for the driver's active route.
     * Shows each student and their current boarding status.
     */
    public function today(Request $request): JsonResponse
    {
        $driver = $this->resolveDriver($request);
        if ($driver instanceof JsonResponse) return $driver;

        $today   = Carbon::today()->toDateString();
        $routeId = $request->query('route_id');

        $routeQuery = TransportRoute::where('driver_id', $driver->id)
            ->where('status', 'active');

        if ($routeId) {
            $route = $routeQuery->findOrFail($routeId);
        } else {
            $route = $routeQuery->first();
        }

        if (! $route) {
            return response()->json(['status' => 'error', 'message' => 'No active route found.'], 404);
        }

        // Get all students on this route
        $students = StudentTransport::where('route_id', $route->id)
            ->where('status', 'active')
            ->with(['student:id,first_name,last_name,photo', 'stop'])
            ->get();

        // Get today's attendance keyed by student_id
        $todayAttendance = TransportAttendance::where('route_id', $route->id)
            ->whereDate('date', $today)
            ->get()
            ->keyBy('student_id');

        $sheet = $students->sortBy('stop.stop_order')->values()->map(function ($st) use ($todayAttendance) {
            $att = $todayAttendance->get($st->student_id);
            return [
                'student_id'   => $st->student_id,
                'name'         => $st->student?->full_name,
                'photo'        => $st->student?->avatar,
                'stop_name'    => $st->stop?->name,
                'stop_order'   => $st->stop?->stop_order,
                'status'       => $att?->status ?? 'not_marked',    // present | absent | not_marked
                'actual_time'  => $att?->actual_time,
                'remarks'      => $att?->remarks,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => [
                'date'      => $today,
                'route_id'  => $route->id,
                'route_name'=> $route->name,
                'students'  => $sheet,
            ],
        ]);
    }

    /**
     * POST /driver-app/attendance/mark
     * Mark a single student as present or absent.
     *
     * Body: { route_id, student_id, status: "present"|"absent", trip_type: "morning"|"afternoon" }
     */
    public function mark(Request $request): JsonResponse
    {
        $driver = $this->resolveDriver($request);
        if ($driver instanceof JsonResponse) return $driver;

        $validated = $request->validate([
            'route_id'   => ['required', 'integer', 'exists:transport_routes,id'],
            'student_id' => ['required', 'integer', 'exists:users,id'],
            'status'     => ['required', 'in:present,absent'],
            'trip_type'  => ['required', 'in:morning,afternoon'],
            'remarks'    => ['nullable', 'string', 'max:500'],
        ]);

        // Verify route belongs to this driver
        TransportRoute::where('driver_id', $driver->id)
            ->where('id', $validated['route_id'])
            ->firstOrFail();

        $today = Carbon::today()->toDateString();

        $attendance = TransportAttendance::updateOrCreate(
            [
                'route_id'   => $validated['route_id'],
                'student_id' => $validated['student_id'],
                'date'       => $today,
                'trip'       => $validated['trip_type'],  // column is 'trip'
                'school_id'  => $request->user()->school_id,
            ],
            [
                'marked_by'   => $driver->user_id ?? $request->user()->id,
                'status'      => $validated['status'],
                'actual_time' => now()->format('H:i:s'),
                'remarks'     => $validated['remarks'] ?? null,
            ]
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Attendance marked.',
            'data'    => $attendance,
        ]);
    }

    /**
     * POST /driver-app/attendance/bulk-mark
     * Mark attendance for multiple students at once (e.g., after reaching a stop).
     *
     * Body: {
     *   route_id, trip_type,
     *   attendance: [{ student_id, status, remarks }]
     * }
     */
    public function bulkMark(Request $request): JsonResponse
    {
        $driver = $this->resolveDriver($request);
        if ($driver instanceof JsonResponse) return $driver;

        $validated = $request->validate([
            'route_id'                 => ['required', 'integer', 'exists:transport_routes,id'],
            'trip_type'                => ['required', 'in:morning,afternoon'],
            'attendance'               => ['required', 'array', 'min:1'],
            'attendance.*.student_id'  => ['required', 'integer', 'exists:users,id'],
            'attendance.*.status'      => ['required', 'in:present,absent'],
            'attendance.*.remarks'     => ['nullable', 'string', 'max:500'],
        ]);

        // Verify route belongs to this driver
        TransportRoute::where('driver_id', $driver->id)
            ->where('id', $validated['route_id'])
            ->firstOrFail();

        $today   = Carbon::today()->toDateString();
        $saved   = 0;
        $errors  = [];

        foreach ($validated['attendance'] as $row) {
            try {
                TransportAttendance::updateOrCreate(
                    [
                        'route_id'   => $validated['route_id'],
                        'student_id' => $row['student_id'],
                        'date'       => $today,
                        'trip'       => $validated['trip_type'],  // column is 'trip'
                        'school_id'  => $request->user()->school_id,
                    ],
                    [
                        'marked_by'   => $driver->user_id ?? $request->user()->id,
                        'status'      => $row['status'],
                        'actual_time' => now()->format('H:i:s'),
                        'remarks'     => $row['remarks'] ?? null,
                    ]
                );
                $saved++;
            } catch (\Throwable $e) {
                $errors[] = ['student_id' => $row['student_id'], 'error' => $e->getMessage()];
            }
        }

        return response()->json([
            'status'  => $errors ? 'partial' : 'success',
            'message' => "Attendance saved: {$saved} records" . ($errors ? ' (' . count($errors) . ' failed)' : '.'),
            'saved'   => $saved,
            'errors'  => $errors,
        ], $errors && $saved === 0 ? 422 : 200);
    }

    /**
     * GET /driver-app/attendance/history
     * Last 30 days of attendance records for the driver's routes.
     */
    public function history(Request $request): JsonResponse
    {
        $driver = $this->resolveDriver($request);
        if ($driver instanceof JsonResponse) return $driver;

        $routeIds = TransportRoute::where('driver_id', $driver->id)->pluck('id');

        $history = TransportAttendance::whereIn('route_id', $routeIds)
            ->where('date', '>=', now()->subDays(30)->toDateString())
            ->with(['student:id,first_name,last_name'])
            ->orderByDesc('date')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $history,
        ]);
    }

    /**
     * GET /driver-app/attendance/summary/{date}
     * Summary stats for a specific date.
     */
    public function summary(Request $request, string $date): JsonResponse
    {
        $driver = $this->resolveDriver($request);
        if ($driver instanceof JsonResponse) return $driver;

        try {
            $parsedDate = Carbon::parse($date)->toDateString();
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => 'Invalid date format. Use YYYY-MM-DD.'], 422);
        }

        $routeIds = TransportRoute::where('driver_id', $driver->id)->pluck('id');

        $records  = TransportAttendance::whereIn('route_id', $routeIds)
            ->whereDate('date', $parsedDate)
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'date'    => $parsedDate,
                'total'   => $records->count(),
                'present' => $records->where('status', 'present')->count(),
                'absent'  => $records->where('status', 'absent')->count(),
            ],
        ]);
    }

    // ── Helper ────────────────────────────────────────────────────────────

    private function resolveDriver(Request $request): Driver|JsonResponse
    {
        $driver = Driver::where('user_id', $request->user()->id)->first();

        if (! $driver) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Driver profile not found for this account.',
            ], 404);
        }

        return $driver;
    }
}
