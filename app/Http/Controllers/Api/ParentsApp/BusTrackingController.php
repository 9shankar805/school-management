<?php

namespace App\Http\Controllers\Api\ParentsApp;

use App\Http\Controllers\Controller;
use App\Models\StudentTransport;
use App\Models\TransportRoute;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BusTrackingController extends Controller
{
    /**
     * GET /api/bus/location
     */
    public function location(Request $request): JsonResponse
    {
        // Mock GPS coordinates based on active transport routes for children
        $childIds = $request->user()->children()->pluck('users.id');
        
        $transports = StudentTransport::with('route.vehicle')
            ->whereIn('student_id', $childIds)
            ->where('status', 'active')
            ->get();

        $locations = $transports->map(function ($transport) {
            // Mock dynamic GPS coordinate drifting around a fixed point
            $lat = 34.0522 + (rand(-100, 100) / 10000);
            $lng = -118.2437 + (rand(-100, 100) / 10000);

            return [
                'student_id'   => $transport->student_id,
                'route_id'     => $transport->route_id,
                'route_name'   => $transport->route ? $transport->route->name : null,
                'vehicle_no'   => $transport->route && $transport->route->vehicle ? $transport->route->vehicle->vehicle_number : null,
                'current_lat'  => $lat,
                'current_lng'  => $lng,
                'speed_kmh'    => rand(20, 60),
                'last_updated' => now()->toIso8601String(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $locations,
        ]);
    }

    /**
     * GET /api/bus/stops
     */
    public function stops(Request $request): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');
        
        $routeIds = StudentTransport::whereIn('student_id', $childIds)
            ->where('status', 'active')
            ->pluck('route_id');

        $routes = TransportRoute::with('stops')
            ->whereIn('id', $routeIds)
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $routes,
        ]);
    }

    /**
     * GET /api/bus/status
     */
    public function status(Request $request): JsonResponse
    {
        // General status (on_time, delayed)
        return response()->json([
            'status' => 'success',
            'data'   => [
                'overall_status' => 'on_time',
                'message'        => 'All buses are operating on normal schedule.',
            ],
        ]);
    }

    /**
     * GET /api/bus/child/{childId}
     */
    public function byChild(Request $request, $childId): JsonResponse
    {
        $request->user()->children()->findOrFail($childId);

        $transport = StudentTransport::with(['route.driver', 'route.vehicle', 'stop'])
            ->where('student_id', $childId)
            ->where('status', 'active')
            ->first();

        return response()->json([
            'status' => 'success',
            'data'   => $transport,
        ]);
    }

    /**
     * GET /api/bus/route/{routeId}
     */
    public function byRoute(Request $request, $routeId): JsonResponse
    {
        $route = TransportRoute::with(['stops', 'driver', 'vehicle'])->findOrFail($routeId);

        return response()->json([
            'status' => 'success',
            'data'   => $route,
        ]);
    }

    /**
     * GET /api/bus/driver/{driverId}
     */
    public function byDriver(Request $request, $driverId): JsonResponse
    {
        $routes = TransportRoute::with(['stops', 'vehicle'])
            ->where('driver_id', $driverId)
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $routes,
        ]);
    }

    /**
     * GET /api/bus/eta/{stopId}
     */
    public function eta(Request $request, $stopId): JsonResponse
    {
        // Mock ETA 
        $minutes = rand(5, 30);
        
        return response()->json([
            'status' => 'success',
            'data'   => [
                'stop_id'        => $stopId,
                'eta_minutes'    => $minutes,
                'estimated_time' => now()->addMinutes($minutes)->format('H:i A'),
                'status'         => 'on_time',
            ]
        ]);
    }
}
