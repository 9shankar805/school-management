<?php

namespace App\Http\Controllers\Api\DriverApp;

use App\Http\Controllers\Controller;
use App\Events\BusLocationUpdated;
use App\Models\BusLocation;
use App\Models\Driver;
use App\Models\TransportRoute;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * POST /driver-app/location/update
     *
     * The driver app calls this every N seconds (recommended: 10–30 s) to push GPS position.
     * This is the core of the real-time tracking pipeline:
     *
     *   Driver App → POST /driver-app/location/update
     *       → stores row in bus_locations
     *       → updates vehicles.current_lat / current_lng / gps_updated_at
     *       → fires BusLocationUpdated event (broadcast on channel bus.{vehicle_id})
     *       → parent app polls GET /api/bus/location OR listens on the broadcast channel
     *
     * Body:
     * {
     *   "latitude":  27.7172,
     *   "longitude": 85.3240,
     *   "accuracy":  10.5,
     *   "speed_kmh": 35.2,
     *   "heading":   182.0,
     *   "altitude":  1350.0,
     *   "trip_type": "morning",        // morning | afternoon | other
     *   "route_id":  3,                // optional — which route is active
     *   "device_id": "device-abc123",  // optional
     *   "battery":   "78%",            // optional
     *   "recorded_at": "2026-09-20T08:15:00+05:45"  // optional device timestamp
     * }
     */
    public function update(Request $request): JsonResponse
    {
        $driver = $this->resolveDriver($request);
        if ($driver instanceof JsonResponse) return $driver;

        $validated = $request->validate([
            'latitude'    => ['required', 'numeric', 'between:-90,90'],
            'longitude'   => ['required', 'numeric', 'between:-180,180'],
            'accuracy'    => ['nullable', 'numeric', 'min:0'],
            'speed_kmh'   => ['nullable', 'numeric', 'min:0', 'max:200'],
            'heading'     => ['nullable', 'numeric', 'between:0,360'],
            'altitude'    => ['nullable', 'numeric'],
            'trip_type'   => ['nullable', 'in:morning,afternoon,other'],
            'route_id'    => ['nullable', 'integer', 'exists:transport_routes,id'],
            'device_id'   => ['nullable', 'string', 'max:100'],
            'battery'     => ['nullable', 'string', 'max:10'],
            'recorded_at' => ['nullable', 'date'],
        ]);

        $vehicle = $driver->currentVehicle;

        if (! $vehicle) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No vehicle assigned to this driver.',
            ], 422);
        }

        // Resolve route_id from request or fall back to driver's active route
        $routeId = $validated['route_id']
            ?? TransportRoute::where('driver_id', $driver->id)
                ->where('status', 'active')
                ->value('id');

        // 1. Store breadcrumb in bus_locations
        $location = BusLocation::create([
            'school_id'       => $request->user()->school_id,
            'vehicle_id'      => $vehicle->id,
            'driver_id'       => $driver->id,
            'route_id'        => $routeId,
            'latitude'        => $validated['latitude'],
            'longitude'       => $validated['longitude'],
            'accuracy_meters' => $validated['accuracy'] ?? null,
            'speed_kmh'       => $validated['speed_kmh'] ?? null,
            'heading'         => $validated['heading'] ?? null,
            'altitude_m'      => $validated['altitude'] ?? null,
            'trip_type'       => $validated['trip_type'] ?? 'morning',
            'is_active'       => true,
            'device_id'       => $validated['device_id'] ?? null,
            'battery_level'   => $validated['battery'] ?? null,
            'recorded_at'     => isset($validated['recorded_at'])
                                    ? Carbon::parse($validated['recorded_at'])
                                    : now(),
        ]);

        // 2. Update the Vehicle's live position (single-row fast lookup for parent app polling)
        $vehicle->update([
            'current_lat'     => $validated['latitude'],
            'current_lng'     => $validated['longitude'],
            'gps_updated_at'  => now(),
        ]);

        // 3. Broadcast event so the parent app can receive it over a WebSocket channel
        //    (works with Pusher, Reverb, or any Laravel broadcaster)
        event(new BusLocationUpdated($location, $vehicle, $driver));

        return response()->json([
            'status'     => 'success',
            'message'    => 'Location updated.',
            'location_id'=> $location->id,
            'server_time'=> now()->toIso8601String(),
        ]);
    }

    /**
     * GET /driver-app/location/current
     * Last known position of the driver's vehicle.
     */
    public function current(Request $request): JsonResponse
    {
        $driver = $this->resolveDriver($request);
        if ($driver instanceof JsonResponse) return $driver;

        $vehicle = $driver->currentVehicle;

        if (! $vehicle) {
            return response()->json(['status' => 'error', 'message' => 'No vehicle assigned.'], 404);
        }

        $latest = BusLocation::where('vehicle_id', $vehicle->id)
            ->orderByDesc('recorded_at')
            ->first();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'vehicle_id'         => $vehicle->id,
                'registration_number'=> $vehicle->registration_number,
                'current_lat'        => $latest?->latitude  ?? $vehicle->current_lat,
                'current_lng'        => $latest?->longitude ?? $vehicle->current_lng,
                'speed_kmh'          => $latest?->speed_kmh,
                'heading'            => $latest?->heading,
                'last_updated'       => $latest?->recorded_at ?? $vehicle->gps_updated_at,
            ],
        ]);
    }

    /**
     * GET /driver-app/location/history
     * Breadcrumb trail for the current day's trip.
     */
    public function history(Request $request): JsonResponse
    {
        $driver = $this->resolveDriver($request);
        if ($driver instanceof JsonResponse) return $driver;

        $vehicle = $driver->currentVehicle;

        if (! $vehicle) {
            return response()->json(['status' => 'error', 'message' => 'No vehicle assigned.'], 404);
        }

        $tripType = $request->query('trip_type', 'morning');
        $date     = $request->query('date', now()->toDateString());

        $breadcrumbs = BusLocation::where('vehicle_id', $vehicle->id)
            ->whereDate('recorded_at', $date)
            ->where('trip_type', $tripType)
            ->orderBy('recorded_at')
            ->get(['id', 'latitude', 'longitude', 'speed_kmh', 'heading', 'recorded_at']);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'vehicle_id'  => $vehicle->id,
                'date'        => $date,
                'trip_type'   => $tripType,
                'points'      => $breadcrumbs,
            ],
        ]);
    }

    // ── Helper ────────────────────────────────────────────────────────────

    private function resolveDriver(Request $request): Driver|JsonResponse
    {
        $driver = Driver::where('user_id', $request->user()->id)
            ->with('currentVehicle')
            ->first();

        if (! $driver) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Driver profile not found for this account.',
            ], 404);
        }

        return $driver;
    }
}
