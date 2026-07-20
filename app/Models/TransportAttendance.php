<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransportAttendance extends Model
{
    use HasFactory;

    protected $table = 'transport_attendance';

    protected $fillable = [
        'student_id', 'route_id', 'date', 'trip', 'status', 'actual_time', 'marked_by', 'remarks',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function route()
    {
        return $this->belongsTo(TransportRoute::class, 'route_id');
    }

    public function markedByUser()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForRoute($query, int $routeId)
    {
        return $query->where('route_id', $routeId);
    }

    public function scopeForDate($query, string $date)
    {
        return $query->where('date', $date);
    }

    public function scopeMorning($query)
    {
        return $query->where('trip', 'morning');
    }

    public function scopeAfternoon($query)
    {
        return $query->where('trip', 'afternoon');
    }
}
