<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProjectSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'student_id', 'file_path',
        'description', 'status', 'marks_obtained',
        'teacher_feedback', 'submitted_at',
    ];

    protected $casts = ['submitted_at' => 'datetime'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
