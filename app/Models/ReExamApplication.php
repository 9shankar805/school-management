<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReExamApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'course_id', 'class_id', 'section_id',
        'semester_id', 'session_id', 'status',
        'reason', 'admin_notes',
        'original_marks', 're_exam_marks', 're_exam_date',
        'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        're_exam_date' => 'date',
        'reviewed_at'  => 'datetime',
    ];

    const STATUSES = [
        'pending'        => 'Pending',
        'approved'       => 'Approved',
        'rejected'       => 'Rejected',
        'scheduled'      => 'Scheduled',
        'result_entered' => 'Result Entered',
        'completed'      => 'Completed',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function student()   { return $this->belongsTo(User::class, 'student_id'); }
    public function course()    { return $this->belongsTo(Course::class, 'course_id'); }
    public function schoolClass(){ return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function semester()  { return $this->belongsTo(Semester::class, 'semester_id'); }
    public function reviewer()  { return $this->belongsTo(User::class, 'reviewed_by'); }

    // ── Computed attributes ───────────────────────────────────────────────────

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending'        => 'bg-amber-100 text-amber-700',
            'approved'       => 'bg-emerald-100 text-emerald-700',
            'rejected'       => 'bg-rose-100 text-rose-700',
            'scheduled'      => 'bg-blue-100 text-blue-700',
            'result_entered' => 'bg-violet-100 text-violet-700',
            'completed'      => 'bg-slate-100 text-slate-600',
            default          => 'bg-slate-100 text-slate-500',
        };
    }

    public function getIsPendingAttribute(): bool { return $this->status === 'pending'; }
    public function getIsApprovedAttribute(): bool { return $this->status === 'approved'; }
    public function getIsRejectedAttribute(): bool { return $this->status === 'rejected'; }
}
