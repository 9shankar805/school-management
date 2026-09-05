<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTenant;

class LiveQuizSession extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'live_quiz_sessions';

    protected $fillable = [
        'school_id',
        'live_quiz_id',
        'host_user_id',
        'pin_code',
        'status',
        'current_question_index',
        'current_question_id',
        'question_started_at',
        'question_expires_at',
        'fast_buzzer_team_id',
        'active_turn_team_id',
        'is_passed',
        'pass_sequence',
        'max_teams',
        'settings',
        'completed_at',
    ];

    protected $casts = [
        'current_question_index' => 'integer',
        'max_teams'              => 'integer',
        'is_passed'              => 'boolean',
        'pass_sequence'          => 'array',
        'settings'               => 'array',
        'question_started_at'    => 'datetime',
        'question_expires_at'    => 'datetime',
        'completed_at'           => 'datetime',
    ];

    public function quiz()
    {
        return $this->belongsTo(LiveQuiz::class, 'live_quiz_id');
    }

    public function host()
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    public function currentQuestion()
    {
        return $this->belongsTo(LiveQuizQuestion::class, 'current_question_id');
    }

    public function teams()
    {
        return $this->hasMany(LiveQuizTeam::class, 'live_quiz_session_id')->orderBy('total_score', 'desc');
    }

    public function buzzerTeam()
    {
        return $this->belongsTo(LiveQuizTeam::class, 'fast_buzzer_team_id');
    }

    public function activeTurnTeam()
    {
        return $this->belongsTo(LiveQuizTeam::class, 'active_turn_team_id');
    }

    public function answers()
    {
        return $this->hasMany(LiveQuizAnswer::class, 'live_quiz_session_id');
    }
}
