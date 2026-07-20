<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id', 'date', 'litres', 'cost_per_litre', 'total_cost',
        'odometer_reading', 'fuel_station', 'recorded_by', 'notes',
    ];

    protected $casts = [
        'date'           => 'date',
        'litres'         => 'decimal:2',
        'cost_per_litre' => 'decimal:2',
        'total_cost'     => 'decimal:2',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function recordedByUser()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForVehicle($query, int $vehicleId)
    {
        return $query->where('vehicle_id', $vehicleId);
    }
}
