<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driver extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'employee_id', 'phone', 'email', 'address', 'date_of_birth',
        'joining_date', 'license_number', 'license_type', 'license_expiry',
        'national_id', 'photo', 'current_vehicle_id', 'status', 'salary', 'notes',
    ];

    protected $casts = [
        'date_of_birth'  => 'date',
        'joining_date'   => 'date',
        'license_expiry' => 'date',
        'salary'         => 'decimal:2',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function currentVehicle()
    {
        return $this->belongsTo(Vehicle::class, 'current_vehicle_id');
    }

    public function routes()
    {
        return $this->hasMany(TransportRoute::class, 'driver_id');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'active'     => '<span class="badge bg-success">Active</span>',
            'on_leave'   => '<span class="badge bg-warning text-dark">On Leave</span>',
            'terminated' => '<span class="badge bg-danger">Terminated</span>',
            default      => '<span class="badge bg-secondary">' . ucfirst($this->status) . '</span>',
        };
    }

    public function getIsLicenseExpiredAttribute(): bool
    {
        return $this->license_expiry && $this->license_expiry->isPast();
    }

    public function getIsLicenseExpiringSoonAttribute(): bool
    {
        return $this->license_expiry
            && $this->license_expiry->isFuture()
            && $this->license_expiry->diffInDays(now()) <= 30;
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
              ->orWhere('phone', 'like', "%{$term}%")
              ->orWhere('license_number', 'like', "%{$term}%")
              ->orWhere('employee_id', 'like', "%{$term}%");
        });
    }
}
