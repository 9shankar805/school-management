<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Curriculum extends Model
{
    use HasTenant;
    use HasFactory, SoftDeletes;

    protected $table = 'curriculums';

    protected $fillable = [
        'school_id',
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
        return $this->hasMany(CurriculumTopic::class, 'curriculum_id')->orderBy('order');
    }
}
