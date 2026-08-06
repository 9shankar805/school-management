<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class OnlineExamAttempt extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'online_exam_id', 'student_id', 'attempt_number', 'status',
        'started_at', 'submitted_at', 'score', 'percentage', 'passed',
        'graded_by', 'graded_at', 'teacher_feedback',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'submitted_at' => 'datetime',
        'graded_at'    => 'datetime',
        'passed'       => 'boolean',
    ];

    public function onlineExam() { return $this->belongsTo(OnlineExam::class); }
    public function student()    { return $this->belongsTo(User::class, 'student_id'); }
    public function gradedBy()   { return $this->belongsTo(User::class, 'graded_by'); }
    public function answers()    { return $this->hasMany(OnlineExamAnswer::class, 'attempt_id'); }

    /** Remaining seconds for this attempt (null if timed out or submitted) */
    public function remainingSeconds(): ?int
    {
        if (in_array($this->status, ['submitted', 'timed_out', 'graded'], true)) {
            return 0;
        }
        $elapsed  = $this->started_at->diffInSeconds(now());
        $duration = $this->onlineExam->duration_minutes * 60;
        return max(0, $duration - $elapsed);
    }
}
