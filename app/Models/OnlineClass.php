<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OnlineClass extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'description', 'platform', 'meeting_url',
        'meeting_id', 'meeting_password', 'scheduled_at',
        'duration_minutes', 'status', 'teacher_id', 'course_id',
        'class_id', 'section_id', 'session_id', 'recording_url',
    ];

    protected $casts = ['scheduled_at' => 'datetime'];

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
}
