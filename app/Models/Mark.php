<?php

namespace App\Models;
use App\Traits\HasTenant;

use App\Models\Exam;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Mark extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'marks',
        'student_id',
        'class_id',
        'section_id',
        'course_id',
        'exam_id',
        'session_id'
    ];

    /**
     * Get the student for attendances.
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the schoolClass.
     */
    public function schoolClass() {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Get the section.
     */
    public function section() {
        return $this->belongsTo(Section::class, 'section_id');
    }

    /**
     * Get the course.
     */
    public function course() {
        return $this->belongsTo(Course::class, 'course_id');
    }

    /**
     * Get the exam.
     */
    public function exam() {
        return $this->belongsTo(Exam::class, 'exam_id');
    }
}
