<?php

namespace App\Events;

use App\Models\BusLocation;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired every time the driver app pushes a GPS location ping.
 *
 * Broadcast channel:  bus.{vehicle_id}   (public channel — no auth required
 *                     so the parent app can subscribe without extra auth)
 *
 * The event payload is kept minimal so the WebSocket frame stays small.
 * The parent app receives it and updates the map marker in real time.
 *
 * To enable real-time delivery:
 *   - Set BROADCAST_DRIVER=pusher / reverb in .env
 *   - Configure PUSHER_* or REVERB_* keys
 *   - Parent app subscribes: Echo.channel('bus.' + vehicleId).listen('BusLocationUpdated', cb)
 *
 * Until a broadcaster is configured, the event is handled by the 'log' driver
 * (writes to storage/logs/laravel.log) which is harmless. The parent app
 * falls back to polling GET /api/bus/location.
 */
class BusLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly BusLocation $location,
        public readonly Vehicle     $vehicle,
        public readonly Driver      $driver,
    ) {}

    /**
     * The broadcast channel name.
     * Public channel so parent / student apps can listen without auth overhead.
     */
    public function broadcastOn(): Channel
    {
        return new Channel('bus.' . $this->vehicle->id);
    }

    /**
     * The event name as seen by the client-side Echo listener.
     */
    public function broadcastAs(): string
    {
        return 'BusLocationUpdated';
    }

    /**
     * Data pushed to the channel.
     */
    public function broadcastWith(): array
    {
        return [
            'vehicle_id'          => $this->vehicle->id,
            'registration_number' => $this->vehicle->registration_number,
            'route_id'            => $this->location->route_id,
            'driver_id'           => $this->driver->id,
            'driver_name'         => $this->driver->name,
            'latitude'            => (float) $this->location->latitude,
            'longitude'           => (float) $this->location->longitude,
            'speed_kmh'           => $this->location->speed_kmh ? (float) $this->location->speed_kmh : null,
            'heading'             => $this->location->heading ? (float) $this->location->heading : null,
            'trip_type'           => $this->location->trip_type,
            'recorded_at'         => $this->location->recorded_at->toIso8601String(),
            'server_time'         => now()->toIso8601String(),
        ];
    }
}
