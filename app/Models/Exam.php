<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_name', 'start_date', 'end_date',
        'semester_id', 'class_id', 'course_id', 'session_id',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date'   => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function examRule()
    {
        return $this->hasOne(ExamRule::class, 'exam_id');
    }

    public function schedules()
    {
        return $this->hasMany(ExamSchedule::class, 'exam_id');
    }

    public function marks()
    {
        return $this->hasMany(Mark::class, 'exam_id');
    }

    // ── Computed attributes ───────────────────────────────────────────────────

    public function getIsUpcomingAttribute(): bool
    {
        return $this->start_date?->isFuture() ?? false;
    }

    public function getIsOngoingAttribute(): bool
    {
        $now = now();
        return $this->start_date?->lte($now) && $this->end_date?->gte($now);
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->end_date?->isPast() ?? false;
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->is_upcoming)  return 'Upcoming';
        if ($this->is_ongoing)   return 'Ongoing';
        if ($this->is_completed) return 'Completed';
        return 'Unknown';
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status_label) {
            'Upcoming'  => 'bg-blue-100 text-blue-700',
            'Ongoing'   => 'bg-emerald-100 text-emerald-700',
            'Completed' => 'bg-slate-100 text-slate-500',
            default     => 'bg-slate-100 text-slate-400',
        };
    }
}
