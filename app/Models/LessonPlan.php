<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LessonPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'objectives', 'content', 'teaching_methods',
        'resources', 'homework_description', 'notes',
        'planned_date', 'duration_minutes', 'status',
        'teacher_id', 'course_id', 'class_id', 'section_id',
        'term_id', 'curriculum_topic_id', 'session_id',
    ];

    protected $casts = ['planned_date' => 'date'];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function curriculumTopic()
    {
        return $this->belongsTo(CurriculumTopic::class);
    }
}
