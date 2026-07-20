<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vehicle_id', 'type', 'title', 'description', 'service_date',
        'next_service_date', 'odometer_reading', 'service_provider',
        'cost', 'status', 'recorded_by', 'notes',
    ];

    protected $casts = [
        'service_date'      => 'date',
        'next_service_date' => 'date',
        'cost'              => 'decimal:2',
    ];

    const TYPES = [
        'oil_change'    => 'Oil Change',
        'tyre'          => 'Tyre Service',
        'brake'         => 'Brake Service',
        'engine'        => 'Engine Repair',
        'body'          => 'Body & Paint',
        'electrical'    => 'Electrical',
        'ac'            => 'AC Service',
        'inspection'    => 'Routine Inspection',
        'other'         => 'Other',
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

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'scheduled'   => '<span class="badge bg-info text-dark">Scheduled</span>',
            'in_progress' => '<span class="badge bg-warning text-dark">In Progress</span>',
            'completed'   => '<span class="badge bg-success">Completed</span>',
            'cancelled'   => '<span class="badge bg-secondary">Cancelled</span>',
            default       => '<span class="badge bg-light text-dark">' . ucfirst($this->status) . '</span>',
        };
    }

    public function getIsDueForServiceAttribute(): bool
    {
        return $this->next_service_date && $this->next_service_date->isPast();
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForVehicle($query, int $vehicleId)
    {
        return $query->where('vehicle_id', $vehicleId);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'scheduled')
                     ->where('service_date', '>=', now()->toDateString());
    }
}
