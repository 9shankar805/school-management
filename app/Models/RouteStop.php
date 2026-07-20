<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RouteStop extends Model
{
    use HasFactory;

    protected $fillable = [
        'route_id', 'name', 'stop_order', 'morning_pickup', 'afternoon_dropoff',
        'latitude', 'longitude', 'landmark', 'stop_fee',
    ];

    protected $casts = [
        'stop_fee'  => 'decimal:2',
        'latitude'  => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function route()
    {
        return $this->belongsTo(TransportRoute::class, 'route_id');
    }

    public function studentTransports()
    {
        return $this->hasMany(StudentTransport::class, 'stop_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function getStudentCountAttribute(): int
    {
        return $this->studentTransports()->where('status', 'active')->count();
    }
}
