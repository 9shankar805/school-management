<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OnlineExam extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'title', 'instructions', 'exam_id', 'course_id', 'class_id', 'session_id',
        'created_by', 'duration_minutes', 'total_marks', 'pass_marks', 'max_attempts',
        'shuffle_questions', 'show_result_immediately', 'is_published', 'is_practice',
        'available_from', 'available_to', 'status',
    ];

    protected $casts = [
        'shuffle_questions'        => 'boolean',
        'show_result_immediately'  => 'boolean',
        'is_published'             => 'boolean',
        'is_practice'              => 'boolean',
        'available_from'           => 'datetime',
        'available_to'             => 'datetime',
    ];

    public function exam()       { return $this->belongsTo(Exam::class); }
    public function course()     { return $this->belongsTo(Course::class); }
    public function schoolClass(){ return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function session()    { return $this->belongsTo(SchoolSession::class, 'session_id'); }
    public function createdBy()  { return $this->belongsTo(User::class, 'created_by'); }
    public function questions()  { return $this->hasMany(OnlineExamQuestion::class)->orderBy('sort_order'); }
    public function attempts()   { return $this->hasMany(OnlineExamAttempt::class); }

    public function attemptsFor(int $studentId) {
        return $this->attempts()->where('student_id', $studentId);
    }

    public function getIsAvailableAttribute(): bool
    {
        $now = Carbon::now();
        if ($this->status !== 'published') return false;
        if ($this->available_from && $now->lt($this->available_from)) return false;
        if ($this->available_to   && $now->gt($this->available_to))   return false;
        return true;
    }
}
