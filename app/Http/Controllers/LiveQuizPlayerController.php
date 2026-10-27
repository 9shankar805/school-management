<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LiveQuizSession;
use App\Models\LiveQuizTeam;
use App\Models\LiveQuizAnswer;
use App\Models\LiveQuizQuestion;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LiveQuizPlayerController extends Controller
{
    /**
     * Mobile Join View for Team Captains.
     */
    public function joinView(Request $request)
    {
        $presetPin = $request->query('pin', '');
        return view('live-quiz.player.join', compact('presetPin'));
    }

    /**
     * Join a Live Quiz Session with PIN and Group Details.
     */
    public function joinSession(Request $request)
    {
        $validated = $request->validate([
            'pin_code'     => 'required|string|size:6',
            'team_name'    => 'required|string|max:40',
            'captain_name' => 'required|string|max:40',
            'team_color'   => 'nullable|string|max:20',
            'avatar_icon'  => 'nullable|string|max:30',
        ]);

        $pin = trim($validated['pin_code']);
        $session = LiveQuizSession::withoutGlobalScopes()->where('pin_code', $pin)->first();

        if (!$session) {
            return response()->json(['error' => 'Quiz session not found with PIN: ' . $pin], 404);
        }

        if ($session->status === 'completed') {
            return response()->json(['error' => 'This quiz competition has already ended.'], 422);
        }

        // Limit teams
        $existingTeamsCount = $session->teams()->count();
        if ($existingTeamsCount >= ($session->max_teams ?: 8)) {
            return response()->json(['error' => 'Maximum number of groups reached for this quiz.'], 422);
        }

        // Distinct Team Colors Palette
        $defaultColors = ['#ef4444', '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4', '#f97316'];
        $assignedColor = $validated['team_color'] ?? $defaultColors[$existingTeamsCount % count($defaultColors)];

        $token = (string) Str::uuid();

        $team = LiveQuizTeam::withoutGlobalScopes()->create([
            'school_id'            => $session->school_id,
            'live_quiz_session_id' => $session->id,
            'team_name'            => $validated['team_name'],
            'captain_name'         => $validated['captain_name'],
            'captain_token'        => $token,
            'team_color'           => $assignedColor,
            'avatar_icon'          => $validated['avatar_icon'] ?? 'star',
            'total_score'          => 0,
            'current_rank'         => $existingTeamsCount + 1,
            'is_connected'         => true,
            'last_ping_at'         => Carbon::now(),
        ]);

        return response()->json([
            'success'       => true,
            'captain_token' => $token,
            'team_id'       => $team->id,
            'team_name'     => $team->team_name,
            'pin_code'      => $pin,
            'redirect'      => route('live-quiz.player.play', ['pin' => $pin]) . '?token=' . $token,
        ]);
    }

    /**
     * Mobile Game Screen View for Team Captains.
     */
    public function playerGameView(Request $request, $pin)
    {
        $session = LiveQuizSession::withoutGlobalScopes()->where('pin_code', $pin)->firstOrFail();
        $token = $request->query('token', '');

        return view('live-quiz.player.play', compact('session', 'pin', 'token'));
    }

    /**
     * Submit Captain's Answer.
     */
    public function submitAnswer(Request $request)
    {
        $validated = $request->validate([
            'captain_token' => 'required|string|size:36',
            'pin_code'      => 'required|string|size:6',
            'option'        => 'required|string|max:255',
        ]);

        $session = LiveQuizSession::withoutGlobalScopes()->where('pin_code', $validated['pin_code'])->first();
        if (!$session) {
            return response()->json(['error' => 'Invalid session PIN.'], 404);
        }

        if ($session->status !== 'question_active') {
            return response()->json(['error' => 'Submissions are not active at this time.'], 422);
        }

        $team = LiveQuizTeam::withoutGlobalScopes()->where('captain_token', $validated['captain_token'])
            ->where('live_quiz_session_id', $session->id)
            ->first();

        if (!$team) {
            return response()->json(['error' => 'Unauthorized captain session token.'], 403);
        }

        $questionId = $session->current_question_id;
        if (!$questionId) {
            return response()->json(['error' => 'No active question.'], 422);
        }

        $now = Carbon::now();

        // Check if question already expired
        if ($session->question_expires_at && $now->greaterThan($session->question_expires_at->addSeconds(2))) {
            return response()->json(['error' => 'Time is up!'], 422);
        }

        // Calculate response latency in milliseconds
        $responseTimeMs = 0;
        if ($session->question_started_at) {
            $responseTimeMs = max(100, $session->question_started_at->diffInMilliseconds($now));
        }

        // Atomic upsert of answer
        $selectedOpt = strtoupper(trim($validated['option']));

        $answer = LiveQuizAnswer::updateOrCreate(
            [
                'live_quiz_session_id'  => $session->id,
                'live_quiz_question_id' => $questionId,
                'live_quiz_team_id'     => $team->id,
            ],
            [
                'selected_option'       => $selectedOpt,
                'response_time_ms'      => $responseTimeMs,
                'submitted_at'          => $now,
            ]
        );

        $team->update(['last_ping_at' => $now]);

        return response()->json([
            'success'         => true,
            'selected_option' => $selectedOpt,
            'time_taken_ms'   => $responseTimeMs,
            'message'         => 'Answer locked in! Waiting for host to reveal results...',
        ]);
    }

    /**
     * Fast-Buzzer Press Action (Atomic Lock).
     */
    public function buzzIn(Request $request)
    {
        $validated = $request->validate([
            'captain_token' => 'required|string|size:36',
            'pin_code'      => 'required|string|size:6',
        ]);

        $session = LiveQuizSession::withoutGlobalScopes()->where('pin_code', $validated['pin_code'])->first();
        if (!$session || $session->status !== 'question_active') {
            return response()->json(['error' => 'Buzzer is not active.'], 422);
        }

        $team = LiveQuizTeam::withoutGlobalScopes()->where('captain_token', $validated['captain_token'])
            ->where('live_quiz_session_id', $session->id)
            ->first();

        if (!$team) {
            return response()->json(['error' => 'Invalid team.'], 403);
        }

        $wasFirst = false;

        DB::transaction(function () use ($session, $team, &$wasFirst) {
            $lockedSession = LiveQuizSession::withoutGlobalScopes()->where('id', $session->id)->lockForUpdate()->first();
            if (empty($lockedSession->fast_buzzer_team_id)) {
                $lockedSession->update([
                    'fast_buzzer_team_id' => $team->id,
                ]);
                $wasFirst = true;
            }
        });

        return response()->json([
            'success'   => true,
            'was_first' => $wasFirst,
            'message'   => $wasFirst ? '🎯 BUZZ LOCKED! Your group has the floor!' : '⚡ Another group buzzed in first!',
        ]);
    }

    /**
     * Player Real-Time State Sync Polling Endpoint.
     */
    public function getPlayerLiveState(Request $request, $pin)
    {
        $session = LiveQuizSession::withoutGlobalScopes()->where('pin_code', $pin)->firstOrFail();
        $token = $request->query('token');

        $team = null;
        if ($token) {
            $team = LiveQuizTeam::withoutGlobalScopes()->where('captain_token', $token)
                ->where('live_quiz_session_id', $session->id)
                ->first();
        }

        if ($team) {
            $team->update(['last_ping_at' => Carbon::now(), 'is_connected' => true]);
        }

        $now = Carbon::now();
        $secondsRemaining = 0;
        if ($session->question_expires_at && $session->status === 'question_active') {
            $secondsRemaining = max(0, $now->diffInSeconds($session->question_expires_at, false));
        }

        $submittedAnswer = null;
        $answerOutcome = null;

        if ($session->current_question_id && $team) {
            $answer = LiveQuizAnswer::where('live_quiz_session_id', $session->id)
                ->where('live_quiz_question_id', $session->current_question_id)
                ->where('live_quiz_team_id', $team->id)
                ->first();

            if ($answer) {
                $submittedAnswer = $answer->selected_option;
                if ($session->status === 'question_result' || $session->status === 'leaderboard') {
                    $answerOutcome = [
                        'is_correct'     => $answer->is_correct,
                        'points_awarded' => $answer->points_awarded,
                    ];
                }
            }
        }

        $currentQuestionData = null;
        if ($session->currentQuestion && in_array($session->status, ['question_active', 'question_locked', 'question_result'])) {
            $currentQuestionData = [
                'id'                 => $session->currentQuestion->id,
                'question_text'      => $session->currentQuestion->question_text,
                'question_type'      => $session->currentQuestion->question_type,
                'options'            => $session->currentQuestion->options,
                'points'             => $session->currentQuestion->points,
                'time_limit_seconds' => $session->currentQuestion->time_limit_seconds,
                'correct_option'     => ($session->status === 'question_result') ? $session->currentQuestion->correct_option : null,
                'explanation'        => ($session->status === 'question_result') ? $session->currentQuestion->explanation : null,
            ];
        }

        $activeTurnTeamData = null;
        $isMyTurn = false;
        if ($session->active_turn_team_id) {
            $t = $session->teams->firstWhere('id', $session->active_turn_team_id);
            if ($t) {
                $activeTurnTeamData = [
                    'id'           => $t->id,
                    'team_name'    => $t->team_name,
                    'captain_name' => $t->captain_name,
                    'team_color'   => $t->team_color,
                ];
            }
            if ($team && $session->active_turn_team_id == $team->id) {
                $isMyTurn = true;
            }
        }

        return response()->json([
            'session_status'         => $session->status,
            'current_question_index' => $session->current_question_index,
            'seconds_remaining'      => $secondsRemaining,
            'current_question'       => $currentQuestionData,
            'active_turn_team'       => $activeTurnTeamData,
            'is_my_turn'             => $isMyTurn,
            'is_passed'              => (bool) $session->is_passed,
            'my_team'                => $team ? [
                'id'           => $team->id,
                'team_name'    => $team->team_name,
                'captain_name' => $team->captain_name,
                'team_color'   => $team->team_color,
                'avatar_icon'  => $team->avatar_icon,
                'total_score'  => $team->total_score,
                'current_rank' => $team->current_rank,
            ] : null,
            'submitted_answer'       => $submittedAnswer,
            'answer_outcome'         => $answerOutcome,
            'buzzer_winner_id'       => $session->fast_buzzer_team_id,
            'is_buzzer_mine'         => ($team && $session->fast_buzzer_team_id == $team->id),
        ]);
    }

    /**
     * Captain voluntarily passes their turn to the next group.
     */
    public function passMyTurn(Request $request)
    {
        $validated = $request->validate([
            'captain_token' => 'required|string|size:36',
            'pin_code'      => 'required|string|size:6',
        ]);

        $session = LiveQuizSession::withoutGlobalScopes()->where('pin_code', $validated['pin_code'])->first();
        if (!$session) {
            return response()->json(['error' => 'Invalid session'], 404);
        }

        $team = LiveQuizTeam::withoutGlobalScopes()->where('captain_token', $validated['captain_token'])
            ->where('live_quiz_session_id', $session->id)
            ->first();

        if (!$team || $session->active_turn_team_id != $team->id) {
            return response()->json(['error' => 'Not your turn to pass'], 403);
        }

        // Delegate to host pass method
        $hostCtrl = new LiveQuizHostController();
        return $hostCtrl->processPass($session);
    }

    /**
     * Report Anti-Cheat Event (Tab Switch / App Blur / Window Leave).
     */
    public function recordSecurityEvent(Request $request)
    {
        $validated = $request->validate([
            'captain_token' => 'required|string|size:36',
            'pin_code'      => 'required|string|size:6',
            'event_type'    => 'nullable|string|max:50',
        ]);

        $session = LiveQuizSession::withoutGlobalScopes()->where('pin_code', $validated['pin_code'])->first();
        if (!$session) {
            return response()->json(['error' => 'Invalid session'], 404);
        }

        $team = LiveQuizTeam::withoutGlobalScopes()->where('captain_token', $validated['captain_token'])
            ->where('live_quiz_session_id', $session->id)
            ->first();

        if ($team) {
            $team->increment('tab_switch_count');
            $team->update([
                'last_security_event' => ($validated['event_type'] ?? 'tab_switch') . ' at ' . Carbon::now()->toTimeString(),
            ]);
        }

        return response()->json(['success' => true, 'tab_switch_count' => $team ? $team->tab_switch_count : 0]);
    }
}

