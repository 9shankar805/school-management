<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ExamSeatAllocation extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'schedule_id', 'student_id', 'seat_number',
        'column_no', 'bench_no', 'bench_position',
        'notes',
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
