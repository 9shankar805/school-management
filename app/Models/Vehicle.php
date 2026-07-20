<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'registration_number', 'type', 'make', 'model', 'year', 'color',
        'capacity', 'fuel_type', 'insurance_expiry', 'fitness_expiry', 'permit_expiry',
        'status', 'gps_device_id', 'current_lat', 'current_lng', 'gps_updated_at', 'notes',
    ];

    protected $casts = [
        'insurance_expiry' => 'date',
        'fitness_expiry'   => 'date',
        'permit_expiry'    => 'date',
        'gps_updated_at'   => 'datetime',
        'current_lat'      => 'decimal:7',
        'current_lng'      => 'decimal:7',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function driver()
    {
        return $this->hasOne(Driver::class, 'current_vehicle_id');
    }

    public function routes()
    {
        return $this->hasMany(TransportRoute::class, 'vehicle_id');
    }

    public function fuelLogs()
    {
        return $this->hasMany(FuelLog::class, 'vehicle_id');
    }

    public function maintenanceLogs()
    {
        return $this->hasMany(MaintenanceLog::class, 'vehicle_id');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'active'      => '<span class="badge bg-success">Active</span>',
            'maintenance' => '<span class="badge bg-warning text-dark">Maintenance</span>',
            'retired'     => '<span class="badge bg-secondary">Retired</span>',
            default       => '<span class="badge bg-light text-dark">' . ucfirst($this->status) . '</span>',
        };
    }

    public function getIsInsuranceExpiringSoonAttribute(): bool
    {
        return $this->insurance_expiry && $this->insurance_expiry->diffInDays(now()) <= 30
            && $this->insurance_expiry->isFuture();
    }

    public function getIsInsuranceExpiredAttribute(): bool
    {
        return $this->insurance_expiry && $this->insurance_expiry->isPast();
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('registration_number', 'like', "%{$term}%")
              ->orWhere('make', 'like', "%{$term}%")
              ->orWhere('model', 'like', "%{$term}%");
        });
    }
}
