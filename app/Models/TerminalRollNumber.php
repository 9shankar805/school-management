<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TerminalRollNumber extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'session_id',
        'semester_id',
        'class_id',
        'section_id',
        'student_id',
        'roll_no',
        'source_semester_id',
        'rank',
        'total_marks',
        'gpa',
        'percentage',
    ];

    protected $casts = [
        'total_marks' => 'float',
        'gpa'         => 'float',
        'percentage'  => 'float',
        'rank'        => 'integer',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function sourceSemester()
    {
        return $this->belongsTo(Semester::class, 'source_semester_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function session()
    {
        return $this->belongsTo(SchoolSession::class, 'session_id');
    }
}

