<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Curriculum extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'description', 'program_id', 'class_id',
        'course_id', 'session_id', 'status', 'objectives', 'learning_outcomes',
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function topics()
    {
        return $this->hasMany(CurriculumTopic::class)->orderBy('order');
    }
}
