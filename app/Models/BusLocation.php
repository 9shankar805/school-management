<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Real-time GPS breadcrumb from the driver app.
 *
 * @property int         $id
 * @property int         $school_id
 * @property int         $vehicle_id
 * @property int|null    $driver_id
 * @property int|null    $route_id
 * @property float       $latitude
 * @property float       $longitude
 * @property float|null  $accuracy_meters
 * @property float|null  $speed_kmh
 * @property float|null  $heading
 * @property float|null  $altitude_m
 * @property string      $trip_type
 * @property bool        $is_active
 * @property string|null $device_id
 * @property string|null $battery_level
 * @property \Carbon\Carbon $recorded_at
 */
class BusLocation extends Model
{
    use HasTenant, HasFactory;

    protected $fillable = [
        'school_id',
        'vehicle_id',
        'driver_id',
        'route_id',
        'latitude',
        'longitude',
        'accuracy_meters',
        'speed_kmh',
        'heading',
        'altitude_m',
        'trip_type',
        'is_active',
        'device_id',
        'battery_level',
        'recorded_at',
    ];

    protected $casts = [
        'latitude'        => 'decimal:7',
        'longitude'       => 'decimal:7',
        'accuracy_meters' => 'decimal:2',
        'speed_kmh'       => 'decimal:2',
        'heading'         => 'decimal:2',
        'altitude_m'      => 'decimal:2',
        'is_active'       => 'boolean',
        'recorded_at'     => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function route()
    {
        return $this->belongsTo(TransportRoute::class, 'route_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────

    /** Latest active ping per vehicle. */
    public function scopeLatestPerVehicle($query)
    {
        return $query->where('is_active', true)
                     ->orderByDesc('recorded_at');
    }

    /** Only pings from the last N minutes (for "live" tracking). */
    public function scopeRecent($query, int $minutes = 5)
    {
        return $query->where('recorded_at', '>=', now()->subMinutes($minutes));
    }
}
