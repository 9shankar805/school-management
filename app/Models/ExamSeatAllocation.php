<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamSeatAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id', 'student_id', 'seat_number', 'notes',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function schedule()
    {
        return $this->belongsTo(ExamSchedule::class, 'schedule_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
