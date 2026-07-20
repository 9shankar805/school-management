<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransportRoute extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'code', 'description', 'vehicle_id', 'driver_id', 'conductor_id',
        'morning_departure', 'morning_arrival', 'afternoon_departure', 'afternoon_arrival',
        'distance_km', 'monthly_fee', 'status',
    ];

    protected $casts = [
        'monthly_fee'  => 'decimal:2',
        'distance_km'  => 'decimal:2',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function stops()
    {
        return $this->hasMany(RouteStop::class, 'route_id')->orderBy('stop_order');
    }

    public function studentTransports()
    {
        return $this->hasMany(StudentTransport::class, 'route_id');
    }

    public function activeStudents()
    {
        return $this->hasMany(StudentTransport::class, 'route_id')
                    ->where('status', 'active');
    }

    public function attendanceRecords()
    {
        return $this->hasMany(TransportAttendance::class, 'route_id');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'active'       => '<span class="badge bg-success">Active</span>',
            'suspended'    => '<span class="badge bg-warning text-dark">Suspended</span>',
            'discontinued' => '<span class="badge bg-danger">Discontinued</span>',
            default        => '<span class="badge bg-secondary">' . ucfirst($this->status) . '</span>',
        };
    }

    public function getStudentCountAttribute(): int
    {
        return $this->activeStudents()->count();
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
