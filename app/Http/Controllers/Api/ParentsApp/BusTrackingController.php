<?php

namespace App\Http\Controllers\Api\ParentsApp;

use App\Http\Controllers\Controller;
use App\Models\BusLocation;
use App\Models\StudentTransport;
use App\Models\TransportRoute;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Real-time bus tracking for the Parent App.
 *
 * Data source:
 *  - bus_locations table (breadcrumbs inserted by the Driver App)
 *  - vehicles.current_lat / current_lng / gps_updated_at  (latest position cache)
 *
 * Real-time push (optional):
 *  When BROADCAST_DRIVER is configured (Pusher / Reverb), the parent app
 *  can subscribe to the public channel:
 *    Echo.channel('bus.' + vehicleId).listen('BusLocationUpdated', callback)
 *
 * Polling fallback:
 *  If no broadcaster is set, the app polls GET /api/bus/location every N seconds.
 *  The endpoint uses the cached vehicles.current_lat / gps_updated_at columns
 *  for a single-row fast lookup, so polling is cheap.
 */
class BusTrackingController extends Controller
{
    /** Maximum age (minutes) before a GPS fix is considered stale. */
    private const STALE_MINUTES = 5;

    /**
     * GET /api/bus/location
     * Current live position for each bus carrying one of the parent's children.
     */
    public function location(Request $request): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');

        $transports = StudentTransport::with(['route.vehicle', 'route.driver'])
            ->whereIn('student_id', $childIds)
            ->where('status', 'active')
            ->get();

        $locations = $transports->map(function ($transport) {
            $vehicle = $transport->route?->vehicle;
            $driver  = $transport->route?->driver;

            if (! $vehicle) {
                return null;
            }

            // Try the latest breadcrumb first (most accurate)
            $latest = BusLocation::where('vehicle_id', $vehicle->id)
                ->orderByDesc('recorded_at')
                ->first();

            // Fall back to the cached columns on the Vehicle record
            $lat       = $latest?->latitude  ?? $vehicle->current_lat;
            $lng       = $latest?->longitude ?? $vehicle->current_lng;
            $updatedAt = $latest?->recorded_at ?? $vehicle->gps_updated_at;
            $isStale   = $updatedAt === null
                         || Carbon::parse($updatedAt)->diffInMinutes(now()) > self::STALE_MINUTES;

            return [
                'student_id'          => $transport->student_id,
                'route_id'            => $transport->route_id,
                'route_name'          => $transport->route?->name,
                'vehicle_id'          => $vehicle->id,
                'vehicle_name'        => $vehicle->name,
                'registration_number' => $vehicle->registration_number,
                'driver_name'         => $driver?->name,
                'driver_phone'        => $driver?->phone,
                'current_lat'         => $lat ? (float) $lat : null,
                'current_lng'         => $lng ? (float) $lng : null,
                'speed_kmh'           => $latest?->speed_kmh ? (float) $latest->speed_kmh : null,
                'heading'             => $latest?->heading   ? (float) $latest->heading    : null,
                'trip_type'           => $latest?->trip_type ?? 'morning',
                'last_updated'        => $updatedAt?->toIso8601String(),
                'is_stale'            => $isStale,
                'has_gps'             => $lat !== null,
                // WebSocket channel the parent app can subscribe to for real-time updates
                'broadcast_channel'   => 'bus.' . $vehicle->id,
            ];
        })->filter()->values();

        return response()->json([
            'status'             => 'success',
            'data'               => $locations,
            'broadcast_driver'   => config('broadcasting.default'),
        ]);
    }

    /**
     * GET /api/bus/stops
     * Route stops for all routes carrying the parent's children.
     */
    public function stops(Request $request): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');

        $routeIds = StudentTransport::whereIn('student_id', $childIds)
            ->where('status', 'active')
            ->pluck('route_id');

        $routes = TransportRoute::with(['stops' => fn($q) => $q->orderBy('stop_order')])
            ->whereIn('id', $routeIds)
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $routes,
        ]);
    }

    /**
     * GET /api/bus/status
     * Overall fleet status summary.
     */
    public function status(Request $request): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');

        $routeIds = StudentTransport::whereIn('student_id', $childIds)
            ->where('status', 'active')
            ->pluck('route_id');

        $vehicles = Vehicle::whereHas('routes', fn($q) => $q->whereIn('id', $routeIds))
            ->get();

        $activeCount = 0;
        $staleCount  = 0;

        foreach ($vehicles as $v) {
            if ($v->gps_updated_at && Carbon::parse($v->gps_updated_at)->diffInMinutes(now()) <= self::STALE_MINUTES) {
                $activeCount++;
            } else {
                $staleCount++;
            }
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'overall_status' => $staleCount === 0 && $activeCount > 0 ? 'on_time' : 'unknown',
                'active_buses'   => $activeCount,
                'stale_buses'    => $staleCount,
                'message'        => $staleCount === 0
                    ? 'All buses are reporting live location.'
                    : "{$staleCount} bus(es) have not reported location in the last " . self::STALE_MINUTES . " minutes.",
            ],
        ]);
    }

    /**
     * GET /api/bus/child/{childId}
     * Bus and location details for a specific child.
     */
    public function byChild(Request $request, $childId): JsonResponse
    {
        $request->user()->children()->findOrFail($childId);

        $transport = StudentTransport::with(['route.driver', 'route.vehicle', 'stop'])
            ->where('student_id', $childId)
            ->where('status', 'active')
            ->first();

        if (! $transport) {
            return response()->json(['status' => 'success', 'data' => null, 'message' => 'No active transport assignment.']);
        }

        $vehicle = $transport->route?->vehicle;
        $latest  = $vehicle
            ? BusLocation::where('vehicle_id', $vehicle->id)->orderByDesc('recorded_at')->first()
            : null;

        return response()->json([
            'status' => 'success',
            'data'   => [
                'route'              => $transport->route,
                'stop'               => $transport->stop,
                'vehicle_id'         => $vehicle?->id,
                'registration_number'=> $vehicle?->registration_number,
                'current_lat'        => $latest?->latitude  ?? $vehicle?->current_lat,
                'current_lng'        => $latest?->longitude ?? $vehicle?->current_lng,
                'speed_kmh'          => $latest?->speed_kmh,
                'last_updated'       => $latest?->recorded_at ?? $vehicle?->gps_updated_at,
                'broadcast_channel'  => $vehicle ? 'bus.' . $vehicle->id : null,
            ],
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
     * Estimated arrival time at a stop based on live GPS data.
     *
     * Algorithm:
     *   1. Find which route serves this stop.
     *   2. Get the vehicle's current position.
     *   3. Find remaining stops between current position and the target stop.
     *   4. Estimate using average speed from the last 3 pings or a default.
     */
    public function eta(Request $request, $stopId): JsonResponse
    {
        // Find the route for this stop
        $stop = \App\Models\RouteStop::findOrFail($stopId);
        $route = TransportRoute::with('stops')->find($stop->route_id);

        if (! $route?->vehicle_id) {
            return response()->json([
                'status' => 'success',
                'data'   => [
                    'stop_id'        => $stopId,
                    'eta_minutes'    => null,
                    'estimated_time' => null,
                    'status'         => 'unknown',
                    'message'        => 'No vehicle assigned to this route.',
                ],
            ]);
        }

        // Get the latest GPS fix
        $latest = BusLocation::where('vehicle_id', $route->vehicle_id)
            ->orderByDesc('recorded_at')
            ->first();

        if (! $latest || Carbon::parse($latest->recorded_at)->diffInMinutes(now()) > self::STALE_MINUTES) {
            // No live data — use schedule-based fallback
            return response()->json([
                'status' => 'success',
                'data'   => [
                    'stop_id'        => $stopId,
                    'eta_minutes'    => null,
                    'estimated_time' => null,
                    'status'         => 'no_gps',
                    'message'        => 'Bus location not available. Check the schedule.',
                ],
            ]);
        }

        // Remaining stops after the vehicle's current position in stop_order
        // Use the closest stop to the current position as proxy for "current stop"
        $orderedStops = $route->stops->sortBy('stop_order');
        $targetOrder  = $stop->stop_order;

        // Average speed over last 3 pings; default to 30 km/h
        $recentPings = BusLocation::where('vehicle_id', $route->vehicle_id)
            ->orderByDesc('recorded_at')
            ->take(3)
            ->pluck('speed_kmh')
            ->filter()
            ->values();

        $avgSpeedKmh = $recentPings->isNotEmpty()
            ? max(5, $recentPings->average())
            : 30;

        // Haversine distance from current vehicle position to target stop
        $distanceKm = $this->haversine(
            (float) $latest->latitude, (float) $latest->longitude,
            (float) $stop->latitude,  (float) $stop->longitude
        );

        $etaMinutes = (int) ceil($distanceKm / $avgSpeedKmh * 60);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'stop_id'        => $stopId,
                'stop_name'      => $stop->name,
                'eta_minutes'    => $etaMinutes,
                'estimated_time' => now()->addMinutes($etaMinutes)->format('H:i'),
                'distance_km'    => round($distanceKm, 2),
                'avg_speed_kmh'  => round($avgSpeedKmh, 1),
                'status'         => 'live',
                'last_updated'   => $latest->recorded_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * GET /api/bus/trail/{vehicleId}
     * Today's GPS breadcrumb trail for a vehicle (for map replay).
     */
    public function trail(Request $request, int $vehicleId): JsonResponse
    {
        $tripType = $request->query('trip_type', 'morning');

        $points = BusLocation::where('vehicle_id', $vehicleId)
            ->whereDate('recorded_at', now()->toDateString())
            ->where('trip_type', $tripType)
            ->orderBy('recorded_at')
            ->get(['latitude', 'longitude', 'speed_kmh', 'recorded_at']);

        return response()->json([
            'status' => 'success',
            'data'   => $points,
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    /**
     * Haversine great-circle distance between two lat/lng points in km.
     */
    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
