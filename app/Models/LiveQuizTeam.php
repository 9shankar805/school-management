<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTenant;

class LiveQuizTeam extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'live_quiz_teams';

    protected $fillable = [
        'school_id',
        'live_quiz_session_id',
        'team_name',
        'captain_name',
        'captain_token',
        'team_color',
        'avatar_icon',
        'total_score',
        'current_rank',
        'correct_count',
        'wrong_count',
        'tab_switch_count',
        'last_security_event',
        'is_connected',
        'last_ping_at',
    ];

    protected $casts = [
        'total_score'      => 'integer',
        'current_rank'     => 'integer',
        'correct_count'    => 'integer',
        'wrong_count'      => 'integer',
        'tab_switch_count' => 'integer',
        'is_connected'     => 'boolean',
        'last_ping_at'     => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(LiveQuizSession::class, 'live_quiz_session_id');
    }

    public function answers()
    {
        return $this->hasMany(LiveQuizAnswer::class, 'live_quiz_team_id');
    }
}
