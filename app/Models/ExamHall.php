<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ExamHall extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'hall_name', 'building', 'floor',
        'capacity', 'columns', 'benches_per_column', 'students_per_bench',
        'notes', 'is_active', 'session_id',
    ];

    protected $casts = ['is_active' => 'boolean'];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function schedules()
    {
        return $this->hasMany(ExamSchedule::class, 'hall_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * How many seats are still unallocated for a given schedule.
     */
    public function availableSeats(int $scheduleId): int
    {
        $used = ExamSeatAllocation::where('schedule_id', $scheduleId)->count();
        return max(0, $this->capacity - $used);
    }
}
