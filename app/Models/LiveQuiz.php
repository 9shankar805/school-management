<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasTenant;

class LiveQuiz extends Model
{
    use HasFactory, SoftDeletes, HasTenant;

    protected $table = 'live_quizzes';

    protected $fillable = [
        'school_id',
        'created_by',
        'course_id',
        'class_id',
        'title',
        'description',
        'category',
        'mode',
        'default_time_limit',
        'default_points',
        'speed_bonus',
        'is_active',
    ];

    protected $casts = [
        'speed_bonus' => 'boolean',
        'is_active'   => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function questions()
    {
        return $this->hasMany(LiveQuizQuestion::class, 'live_quiz_id')->orderBy('sort_order', 'asc');
    }

    public function sessions()
    {
        return $this->hasMany(LiveQuizSession::class, 'live_quiz_id')->latest();
    }
}
