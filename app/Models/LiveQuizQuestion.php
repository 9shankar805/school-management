<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveQuizQuestion extends Model
{
    use HasFactory;

    protected $table = 'live_quiz_questions';

    protected $fillable = [
        'live_quiz_id',
        'question_text',
        'image_url',
        'question_type',
        'options',
        'correct_option',
        'direct_answer',
        'points',
        'time_limit_seconds',
        'pass_time_seconds',
        'pass_points',
        'explanation',
        'sort_order',
    ];

    protected $casts = [
        'options'            => 'array',
        'points'             => 'integer',
        'time_limit_seconds' => 'integer',
        'pass_time_seconds'  => 'integer',
        'pass_points'        => 'integer',
        'sort_order'         => 'integer',
    ];

    public function quiz()
    {
        return $this->belongsTo(LiveQuiz::class, 'live_quiz_id');
    }

    public function answers()
    {
        return $this->hasMany(LiveQuizAnswer::class, 'live_quiz_question_id');
    }
}
