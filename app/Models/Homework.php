<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Homework extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'description', 'file_path', 'due_date',
        'total_marks', 'teacher_id', 'course_id',
        'class_id', 'section_id', 'session_id', 'status',
    ];

    protected $casts = ['due_date' => 'date'];

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

    public function submissions()
    {
        return $this->hasMany(HomeworkSubmission::class);
    }

    public function mySubmission()
    {
        return $this->hasOne(HomeworkSubmission::class)
                    ->where('student_id', auth()->id());
    }
}
