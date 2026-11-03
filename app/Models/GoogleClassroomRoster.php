<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoogleClassroomRoster extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'google_classroom_rosters';

    protected $fillable = [
        'school_id',
        'google_classroom_course_id',
        'user_id',
        'google_user_id',
        'user_email',
        'role',
        'enrollment_state',
    ];

    public function course()
    {
        return $this->belongsTo(GoogleClassroomCourse::class, 'google_classroom_course_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

