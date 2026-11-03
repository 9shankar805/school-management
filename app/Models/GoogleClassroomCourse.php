<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoogleClassroomCourse extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'google_classroom_courses';

    protected $fillable = [
        'school_id',
        'class_id',
        'section_id',
        'course_id',
        'session_id',
        'google_course_id',
        'name',
        'section_name',
        'description_heading',
        'owner_google_id',
        'enrollment_code',
        'alternate_link',
        'teacher_folder_id',
        'calendar_id',
        'course_state',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function rosters()
    {
        return $this->hasMany(GoogleClassroomRoster::class, 'google_classroom_course_id');
    }

    public function studentRosters()
    {
        return $this->hasMany(GoogleClassroomRoster::class, 'google_classroom_course_id')->where('role', 'STUDENT');
    }

    public function teacherRosters()
    {
        return $this->hasMany(GoogleClassroomRoster::class, 'google_classroom_course_id')->where('role', 'TEACHER');
    }
}

