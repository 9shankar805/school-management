<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveQuizAnswer extends Model
{
    use HasFactory;

    protected $table = 'live_quiz_answers';

    protected $fillable = [
        'live_quiz_session_id',
        'live_quiz_question_id',
        'live_quiz_team_id',
        'selected_option',
        'is_correct',
        'points_awarded',
        'response_time_ms',
        'is_buzzer_winner',
        'submitted_at',
    ];

    protected $casts = [
        'is_correct'       => 'boolean',
        'is_buzzer_winner' => 'boolean',
        'points_awarded'   => 'integer',
        'response_time_ms' => 'integer',
        'submitted_at'     => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(LiveQuizSession::class, 'live_quiz_session_id');
    }

    public function question()
    {
        return $this->belongsTo(LiveQuizQuestion::class, 'live_quiz_question_id');
    }

    public function team()
    {
        return $this->belongsTo(LiveQuizTeam::class, 'live_quiz_team_id');
    }
}
