<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffAttendance extends Model
{
    use HasFactory;

    protected $table = 'staff_attendance';

    protected $fillable = [
        'staff_id',
        'date',
        'status',
        'check_in',
        'check_out',
        'late_minutes',
        'notes',
        'marked_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /** Mirrors the same statuses used in TeacherAttendance for consistency. */
    const STATUSES = [
        'present'  => 'Present',
        'absent'   => 'Absent',
        'late'     => 'Late',
        'half_day' => 'Half Day',
        'on_leave' => 'On Leave',
    ];

    // ──────────────────────────────────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────────────────────────────────

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function markedBy()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Computed attributes
    // ──────────────────────────────────────────────────────────────────────────

    /** Tailwind badge classes — same colour scheme as TeacherAttendance. */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'present'  => 'bg-emerald-100 text-emerald-700',
            'absent'   => 'bg-rose-100 text-rose-700',
            'late'     => 'bg-amber-100 text-amber-700',
            'half_day' => 'bg-blue-100 text-blue-700',
            'on_leave' => 'bg-violet-100 text-violet-700',
            default    => 'bg-slate-100 text-slate-600',
        };
    }

    /** Bootstrap badge classes for views that use Bootstrap rather than Tailwind. */
    public function getStatusBsBadgeAttribute(): string
    {
        return match ($this->status) {
            'present'  => 'success',
            'absent'   => 'danger',
            'late'     => 'warning',
            'half_day' => 'info',
            'on_leave' => 'secondary',
            default    => 'light',
        };
    }
}
