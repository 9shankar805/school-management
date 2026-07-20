<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id', 'hall_id', 'exam_date',
        'start_time', 'end_time', 'invigilator_id',
        'notes', 'session_id',
    ];

    protected $casts = ['exam_date' => 'date'];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public function hall()
    {
        return $this->belongsTo(ExamHall::class, 'hall_id');
    }

    public function invigilator()
    {
        return $this->belongsTo(User::class, 'invigilator_id');
    }

    public function seatAllocations()
    {
        return $this->hasMany(ExamSeatAllocation::class, 'schedule_id');
    }

    // ── Computed attributes ───────────────────────────────────────────────────

    /** Duration in minutes. */
    public function getDurationMinutesAttribute(): int
    {
        $start = Carbon::parse($this->start_time);
        $end   = Carbon::parse($this->end_time);
        return (int) $start->diffInMinutes($end);
    }

    /** Human-readable duration string. */
    public function getDurationLabelAttribute(): string
    {
        $mins = $this->duration_minutes;
        $h    = intdiv($mins, 60);
        $m    = $mins % 60;
        return $h > 0
            ? ($m > 0 ? "{$h}h {$m}m" : "{$h}h")
            : "{$m}m";
    }

    /** Is the exam happening today? */
    public function getIsTodayAttribute(): bool
    {
        return $this->exam_date->isToday();
    }

    /** Is the exam upcoming (today or future)? */
    public function getIsUpcomingAttribute(): bool
    {
        return $this->exam_date->isFuture() || $this->exam_date->isToday();
    }

    /** Seats allocated / capacity label. */
    public function getSeatStatusAttribute(): string
    {
        $allocated = $this->seatAllocations()->count();
        $capacity  = $this->hall?->capacity ?? 0;
        return "{$allocated} / {$capacity}";
    }
}
