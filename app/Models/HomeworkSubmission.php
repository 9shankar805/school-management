<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HomeworkSubmission extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'homework_id', 'student_id', 'file_path',
        'annotated_file_path', 'annotations_json',
        'remarks', 'status', 'marks_obtained',
        'teacher_feedback', 'submitted_at',
    ];

    protected $casts = ['submitted_at' => 'datetime'];

    public function homework()
    {
        return $this->belongsTo(Homework::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
