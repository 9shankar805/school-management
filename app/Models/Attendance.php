<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'class_id',
        'section_id',
        'session_id',
        'course_id',
        'status',       // "on" = present | "off" = absent  (legacy; kept for BC)
        'date',
        'late_minutes',
        'check_in',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // ──────────────────────────────────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────────────────────────────────

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────────────────────────────────

    /** Filter by a specific date (uses the new `date` column). */
    public function scopeOnDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    /** Only present records (status == "on"). */
    public function scopePresent($query)
    {
        return $query->where('status', 'on');
    }

    /** Only absent records (status == "off"). */
    public function scopeAbsent($query)
    {
        return $query->where('status', 'off');
    }

    /** Only late arrivals (status "on" but late_minutes > 0). */
    public function scopeLate($query)
    {
        return $query->where('status', 'on')->where('late_minutes', '>', 0);
    }

    /** Records within a month (YYYY-MM). */
    public function scopeInMonth($query, int $year, int $month)
    {
        return $query->whereYear('date', $year)->whereMonth('date', $month);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Computed attributes
    // ──────────────────────────────────────────────────────────────────────────

    /** Human-readable status. */
    public function getStatusLabelAttribute(): string
    {
        if ($this->status === 'on') {
            return $this->late_minutes > 0 ? 'Late' : 'Present';
        }

        return 'Absent';
    }

    /** Bootstrap badge colour. */
    public function getStatusBadgeAttribute(): string
    {
        if ($this->status === 'on') {
            return $this->late_minutes > 0 ? 'warning' : 'success';
        }

        return 'danger';
    }

    /** Is the student present? */
    public function isPresentAttribute(): bool
    {
        return $this->status === 'on';
    }

    /** Is the student late? */
    public function isLateAttribute(): bool
    {
        return $this->status === 'on' && $this->late_minutes > 0;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Static helpers
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Compute attendance percentage for a student within a session.
     * Returns ['total', 'present', 'absent', 'late', 'percentage'].
     */
    public static function statsFor(int $studentId, int $sessionId): array
    {
        $records = static::where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->get();

        $total   = $records->count();
        $present = $records->where('status', 'on')->count();
        $absent  = $total - $present;
        $late    = $records->where('status', 'on')->where('late_minutes', '>', 0)->count();

        return [
            'total'      => $total,
            'present'    => $present,
            'absent'     => $absent,
            'late'       => $late,
            'percentage' => $total > 0 ? round($present / $total * 100, 1) : 0,
        ];
    }
}
