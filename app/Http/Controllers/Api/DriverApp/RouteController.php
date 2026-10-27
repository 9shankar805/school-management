<?php

namespace App\Http\Controllers\Api\DriverApp;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\TransportRoute;
use App\Models\StudentTransport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    /**
     * GET /driver-app/route
     * Active routes assigned to the authenticated driver.
     */
    public function index(Request $request): JsonResponse
    {
        $driver = $this->resolveDriver($request);
        if ($driver instanceof JsonResponse) return $driver;

        $routes = TransportRoute::where('driver_id', $driver->id)
            ->where('status', 'active')
            ->with(['vehicle', 'stops' => fn($q) => $q->orderBy('stop_order')])
            ->get()
            ->map(fn($r) => $this->formatRoute($r));

        return response()->json([
            'status' => 'success',
            'data'   => $routes,
        ]);
    }

    /**
     * GET /driver-app/route/{id}
     * Full details of a single route.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $driver = $this->resolveDriver($request);
        if ($driver instanceof JsonResponse) return $driver;

        $route = TransportRoute::where('driver_id', $driver->id)
            ->with(['vehicle', 'stops' => fn($q) => $q->orderBy('stop_order')])
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $this->formatRoute($route),
        ]);
    }

    /**
     * GET /driver-app/route/{id}/students
     * All students assigned to a route, with their boarding stop.
     */
    public function students(Request $request, int $id): JsonResponse
    {
        $driver = $this->resolveDriver($request);
        if ($driver instanceof JsonResponse) return $driver;

        // Verify route belongs to this driver
        TransportRoute::where('driver_id', $driver->id)->findOrFail($id);

        $students = StudentTransport::where('route_id', $id)
            ->where('status', 'active')
            ->with(['student:id,first_name,last_name,photo,phone', 'stop'])
            ->get()
            ->map(fn($st) => [
                'student_id'   => $st->student_id,
                'name'         => $st->student?->full_name,
                'photo'        => $st->student?->avatar,
                'phone'        => $st->student?->phone,
                'stop_id'      => $st->stop_id,
                'stop_name'    => $st->stop?->name,
                'stop_order'   => $st->stop?->stop_order,
                'pickup_type'  => $st->pickup_type ?? 'both',  // morning / afternoon / both
            ]);

        // Sort by stop order so driver sees them in route sequence
        $students = $students->sortBy('stop_order')->values();

        return response()->json([
            'status' => 'success',
            'data'   => $students,
        ]);
    }

    /**
     * GET /driver-app/route/{id}/stops
     * Ordered list of stops with student counts.
     */
    public function stops(Request $request, int $id): JsonResponse
    {
        $driver = $this->resolveDriver($request);
        if ($driver instanceof JsonResponse) return $driver;

        $route = TransportRoute::where('driver_id', $driver->id)
            ->with(['stops' => fn($q) => $q->orderBy('stop_order')])
            ->findOrFail($id);

        $stops = $route->stops->map(function ($stop) use ($id) {
            $studentCount = StudentTransport::where('route_id', $id)
                ->where('stop_id', $stop->id)
                ->where('status', 'active')
                ->count();

            return [
                'id'           => $stop->id,
                'name'         => $stop->name,
                'order'        => $stop->stop_order,
                'latitude'     => $stop->latitude,
                'longitude'    => $stop->longitude,
                'student_count'=> $studentCount,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $stops,
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

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

    private function formatRoute(TransportRoute $route): array
    {
        return [
            'id'                  => $route->id,
            'name'                => $route->name,
            'code'                => $route->code,
            'morning_departure'   => $route->morning_departure,
            'morning_arrival'     => $route->morning_arrival,
            'afternoon_departure' => $route->afternoon_departure,
            'afternoon_arrival'   => $route->afternoon_arrival,
            'distance_km'         => $route->distance_km,
            'vehicle' => $route->vehicle ? [
                'id'                  => $route->vehicle->id,
                'name'                => $route->vehicle->name,
                'registration_number' => $route->vehicle->registration_number,
                'capacity'            => $route->vehicle->capacity,
            ] : null,
            'stops' => $route->stops->map(fn($s) => [
                'id'        => $s->id,
                'name'      => $s->name,
                'order'     => $s->stop_order,
                'latitude'  => $s->latitude,
                'longitude' => $s->longitude,
            ]),
        ];
    }
}
