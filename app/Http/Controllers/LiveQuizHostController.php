<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LiveQuiz;
use App\Models\LiveQuizSession;
use App\Models\LiveQuizQuestion;
use App\Models\LiveQuizTeam;
use App\Models\LiveQuizAnswer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class LiveQuizHostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Create a new live session and redirect to the host lobby.
     */
    public function createSession($quizId)
    {
        $quiz = LiveQuiz::with('questions')->findOrFail($quizId);

        if ($quiz->questions->isEmpty()) {
            return redirect()->route('live-quiz.show', $quizId)
                ->with('error', 'Cannot host a quiz with no questions. Please add questions first.');
        }

        // Generate unique 6-digit PIN code
        do {
            $pin = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        } while (LiveQuizSession::where('pin_code', $pin)->whereIn('status', ['lobby', 'question_active', 'question_locked', 'question_result', 'leaderboard'])->exists());

        $session = LiveQuizSession::create([
            'live_quiz_id'           => $quiz->id,
            'host_user_id'           => Auth::id(),
            'pin_code'               => $pin,
            'status'                 => 'lobby',
            'current_question_index' => 0,
            'current_question_id'    => null,
            'max_teams'              => 8,
            'settings'               => [
                'speed_bonus'        => $quiz->speed_bonus,
                'default_time_limit' => $quiz->default_time_limit,
                'mode'               => $quiz->mode,
            ],
        ]);

        return redirect()->route('live-quiz.host.lobby', $session->id);
    }

    /**
     * Host Big Screen Lobby View.
     */
    public function lobbyView($sessionId)
    {
        $session = LiveQuizSession::with(['quiz.questions', 'teams', 'host'])->findOrFail($sessionId);

        if ($session->status !== 'lobby' && $session->status !== 'completed') {
            return redirect()->route('live-quiz.host.game', $sessionId);
        }

        $joinUrl = route('live-quiz.player.join', ['pin' => $session->pin_code]);

        return view('live-quiz.host.lobby', compact('session', 'joinUrl'));
    }

    /**
     * Host Big Screen Game View.
     */
    public function gameView($sessionId)
    {
        $session = LiveQuizSession::with(['quiz.questions', 'teams', 'currentQuestion', 'host'])->findOrFail($sessionId);

        if ($session->status === 'lobby') {
            return redirect()->route('live-quiz.host.lobby', $sessionId);
        }

        $totalQuestions = $session->quiz->questions->count();

        return view('live-quiz.host.game', compact('session', 'totalQuestions'));
    }

    /**
     * Start the competition from the lobby (Moves to Question 1).
     */
    public function startLobbyGame(Request $request, $sessionId)
    {
        $session = LiveQuizSession::with('quiz.questions')->findOrFail($sessionId);

        if ($session->teams()->count() === 0) {
            return response()->json(['error' => 'At least 1 group must join before starting!'], 422);
        }

        $firstQuestion = $session->quiz->questions->first();

        if (!$firstQuestion) {
            return response()->json(['error' => 'No questions found in this quiz.'], 422);
        }

        $duration = $firstQuestion->time_limit_seconds ?: 30;
        $now = Carbon::now();
        $teams = $session->teams()->get();
        $firstTeamId = $teams->isNotEmpty() ? $teams->first()->id : null;

        $session->update([
            'status'                 => 'question_active',
            'current_question_index' => 1,
            'current_question_id'    => $firstQuestion->id,
            'question_started_at'    => $now,
            'question_expires_at'    => $now->copy()->addSeconds($duration),
            'fast_buzzer_team_id'    => null,
            'active_turn_team_id'    => $firstTeamId,
            'is_passed'              => false,
            'pass_sequence'          => $firstTeamId ? [$firstTeamId] : [],
        ]);

        return response()->json([
            'success'  => true,
            'redirect' => route('live-quiz.host.game', $session->id),
        ]);
    }

    /**
     * Advance to the next question.
     */
    public function nextQuestion(Request $request, $sessionId)
    {
        $session = LiveQuizSession::with('quiz.questions')->findOrFail($sessionId);

        $nextIndex = $session->current_question_index + 1;
        $nextQuestion = $session->quiz->questions->slice($nextIndex - 1, 1)->first();

        if (!$nextQuestion) {
            // End of quiz -> mark completed
            $session->update([
                'status'       => 'completed',
                'completed_at' => Carbon::now(),
            ]);

            return response()->json(['success' => true, 'completed' => true]);
        }

        $duration = $nextQuestion->time_limit_seconds ?: 30;
        $now = Carbon::now();

        // Cycle turn team in order (Team 1 for Q1, Team 2 for Q2...)
        $teams = $session->teams()->get();
        $turnTeamId = null;
        if ($teams->isNotEmpty()) {
            $turnIndex = ($nextIndex - 1) % $teams->count();
            $turnTeamId = $teams->values()[$turnIndex]->id;
        }

        $session->update([
            'status'                 => 'question_active',
            'current_question_index' => $nextIndex,
            'current_question_id'    => $nextQuestion->id,
            'question_started_at'    => $now,
            'question_expires_at'    => $now->copy()->addSeconds($duration),
            'fast_buzzer_team_id'    => null,
            'active_turn_team_id'    => $turnTeamId,
            'is_passed'              => false,
            'pass_sequence'          => $turnTeamId ? [$turnTeamId] : [],
        ]);

        return response()->json(['success' => true, 'question_index' => $nextIndex]);
    }

    /**
     * Pass / Bounce the Question to the Next Group (Bonus Round with 15s Timer).
     */
    public function passQuestion(Request $request, $sessionId)
    {
        $session = LiveQuizSession::with(['currentQuestion', 'teams'])->findOrFail($sessionId);
        return $this->processPass($session);
    }

    /**
     * Internal Pass Question Processor reusable across Host and Player triggers.
     */
    public function processPass(LiveQuizSession $session)
    {
        $session->loadMissing(['currentQuestion', 'teams']);
        $question = $session->currentQuestion;

        $teams = $session->teams;
        if ($teams->isEmpty()) {
            return response()->json(['error' => 'No teams connected'], 422);
        }

        $passSeq = $session->pass_sequence ?: [];
        if ($session->active_turn_team_id && !in_array($session->active_turn_team_id, $passSeq)) {
            $passSeq[] = $session->active_turn_team_id;
        }

        // Find next team that hasn't had the question yet
        $nextTeam = null;
        foreach ($teams as $t) {
            if (!in_array($t->id, $passSeq)) {
                $nextTeam = $t;
                break;
            }
        }

        if ($nextTeam) {
            $passSeq[] = $nextTeam->id;
            $passDuration = ($question && $question->pass_time_seconds) ? $question->pass_time_seconds : 15;
            $now = Carbon::now();

            $session->update([
                'status'              => 'question_active',
                'active_turn_team_id' => $nextTeam->id,
                'is_passed'           => true,
                'pass_sequence'       => $passSeq,
                'question_started_at' => $now,
                'question_expires_at' => $now->copy()->addSeconds($passDuration),
            ]);

            return response()->json([
                'success'           => true,
                'passed_to_team_id' => $nextTeam->id,
                'team_name'         => $nextTeam->team_name,
                'duration'          => $passDuration,
            ]);
        } else {
            // All teams passed -> lock question
            $session->update([
                'status' => 'question_locked',
            ]);

            return response()->json([
                'success'    => true,
                'all_passed' => true,
                'message'    => 'All teams have passed this question.'
            ]);
        }
    }

    /**
     * Judge Direct Answer (Teacher Awards Points with 1-Click).
     */
    public function judgeDirectAnswer(Request $request, $sessionId)
    {
        $session = LiveQuizSession::with(['currentQuestion', 'teams'])->findOrFail($sessionId);
        $question = $session->currentQuestion;

        if (!$question) {
            return response()->json(['error' => 'No active question.'], 422);
        }

        $teamId = $request->input('team_id') ?: $session->active_turn_team_id;
        $team = LiveQuizTeam::where('live_quiz_session_id', $session->id)->findOrFail($teamId);

        $isCorrect = $request->boolean('is_correct');
        $customPoints = $request->input('points');

        $pointsAwarded = 0;
        if ($isCorrect) {
            if ($customPoints !== null) {
                $pointsAwarded = (int) $customPoints;
            } else {
                $pointsAwarded = $session->is_passed ? ($question->pass_points ?: 50) : ($question->points ?: 100);
            }
            $team->increment('total_score', $pointsAwarded);
            $team->increment('correct_count');
        } else {
            $team->increment('wrong_count');
        }

        // Record Answer
        LiveQuizAnswer::updateOrCreate(
            [
                'live_quiz_session_id'  => $session->id,
                'live_quiz_question_id' => $question->id,
                'live_quiz_team_id'     => $team->id,
            ],
            [
                'selected_option'       => $isCorrect ? 'CORRECT' : 'WRONG',
                'is_correct'            => $isCorrect,
                'points_awarded'        => $pointsAwarded,
                'response_time_ms'      => 0,
                'submitted_at'          => Carbon::now(),
            ]
        );

        // Recalculate ranks
        $rankedTeams = LiveQuizTeam::where('live_quiz_session_id', $session->id)
            ->orderBy('total_score', 'desc')
            ->get();

        $rank = 1;
        foreach ($rankedTeams as $t) {
            $t->update(['current_rank' => $rank++]);
        }

        $session->update([
            'status' => 'question_result',
        ]);

        return response()->json([
            'success'        => true,
            'points_awarded' => $pointsAwarded,
            'team_name'      => $team->team_name,
            'total_score'    => $team->fresh()->total_score,
        ]);
    }


    /**
     * Lock the active question (Stop submissions / Time Up).
     */
    public function lockQuestion(Request $request, $sessionId)
    {
        $session = LiveQuizSession::findOrFail($sessionId);

        $session->update([
            'status' => 'question_locked',
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Reveal the answer and compute scores.
     */
    public function revealAnswer(Request $request, $sessionId)
    {
        $session = LiveQuizSession::with(['currentQuestion', 'teams'])->findOrFail($sessionId);
        $question = $session->currentQuestion;

        if (!$question) {
            return response()->json(['error' => 'No active question.'], 422);
        }

        // Evaluate all answers for this question
        $answers = LiveQuizAnswer::where('live_quiz_session_id', $session->id)
            ->where('live_quiz_question_id', $question->id)
            ->get();

        $correctOption = strtoupper((string)($question->correct_option ?? ''));
        $basePoints = $question->points ?: 100;
        $timeLimit = $question->time_limit_seconds ?: 30;
        $speedBonus = $session->settings['speed_bonus'] ?? true;

        foreach ($session->teams as $team) {
            $answer = $answers->firstWhere('live_quiz_team_id', $team->id);

            if ($answer) {
                $isCorrect = (strtoupper($answer->selected_option) === $correctOption);
                $points = 0;

                if ($isCorrect) {
                    // Calculate speed bonus: faster submission gets higher fraction of bonus
                    if ($speedBonus && $answer->response_time_ms > 0) {
                        $secondsTaken = min($timeLimit, $answer->response_time_ms / 1000);
                        $fractionLeft = max(0.2, 1 - ($secondsTaken / $timeLimit));
                        $points = (int) round($basePoints * (0.6 + 0.4 * $fractionLeft));
                    } else {
                        $points = $basePoints;
                    }
                }

                $answer->update([
                    'is_correct'     => $isCorrect,
                    'points_awarded' => $points,
                ]);

                // Update team stats
                if ($isCorrect) {
                    $team->increment('total_score', $points);
                    $team->increment('correct_count');
                } else {
                    $team->increment('wrong_count');
                }
            } else {
                // Team didn't answer in time
                $team->increment('wrong_count');
            }
        }

        // Recalculate ranks
        $rankedTeams = LiveQuizTeam::where('live_quiz_session_id', $session->id)
            ->orderBy('total_score', 'desc')
            ->get();

        $rank = 1;
        foreach ($rankedTeams as $t) {
            $t->update(['current_rank' => $rank++]);
        }

        $session->update([
            'status' => 'question_result',
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Show leaderboard screen.
     */
    public function showLeaderboard(Request $request, $sessionId)
    {
        $session = LiveQuizSession::findOrFail($sessionId);

        $session->update([
            'status' => 'leaderboard',
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Finish game.
     */
    public function finishGame(Request $request, $sessionId)
    {
        $session = LiveQuizSession::findOrFail($sessionId);

        $session->update([
            'status'       => 'completed',
            'completed_at' => Carbon::now(),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Reset / Kick team from lobby.
     */
    public function removeTeam(Request $request, $sessionId, $teamId)
    {
        $team = LiveQuizTeam::where('live_quiz_session_id', $sessionId)->findOrFail($teamId);
        $team->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Live State Polling Endpoint for Host Big Screen.
     */
    public function getHostLiveState(Request $request, $sessionId)
    {
        if ($sessionId instanceof LiveQuizSession) {
            $sessionId = $sessionId->id;
        }

        $session = LiveQuizSession::with([
            'quiz.questions',
            'teams' => function ($q) {
                $q->orderBy('total_score', 'desc');
            },
            'currentQuestion',
            'buzzerTeam',
        ])->findOrFail($sessionId);

        $teamsData = [];
        $submittedTeamIds = [];
        $optionBreakdown = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0];

        if ($session->current_question_id) {
            $answers = LiveQuizAnswer::where('live_quiz_session_id', $session->id)
                ->where('live_quiz_question_id', $session->current_question_id)
                ->get();

            foreach ($answers as $ans) {
                $submittedTeamIds[] = $ans->live_quiz_team_id;
                $opt = strtoupper($ans->selected_option);
                if (isset($optionBreakdown[$opt])) {
                    $optionBreakdown[$opt]++;
                }
            }
        }

        foreach ($session->teams as $team) {
            $hasAnswered = in_array($team->id, $submittedTeamIds, true);
            $teamsData[] = [
                'id'                  => $team->id,
                'team_name'           => $team->team_name,
                'captain_name'        => $team->captain_name,
                'team_color'          => $team->team_color,
                'avatar_icon'         => $team->avatar_icon,
                'total_score'         => $team->total_score,
                'current_rank'        => $team->current_rank,
                'tab_switch_count'    => $team->tab_switch_count ?? 0,
                'last_security_event' => $team->last_security_event,
                'has_answered'        => $hasAnswered,
                'is_connected'        => $team->is_connected,
            ];
        }

        $now = Carbon::now();
        $secondsRemaining = 0;
        if ($session->question_expires_at && $session->status === 'question_active') {
            $secondsRemaining = max(0, $now->diffInSeconds($session->question_expires_at, false));
        }

        $currentQuestionData = null;
        if ($session->currentQuestion) {
            $currentQuestionData = [
                'id'                 => $session->currentQuestion->id,
                'question_text'      => $session->currentQuestion->question_text,
                'image_url'          => $session->currentQuestion->image_url,
                'question_type'      => $session->currentQuestion->question_type,
                'options'            => $session->currentQuestion->options,
                'correct_option'     => $session->currentQuestion->correct_option,
                'direct_answer'      => $session->currentQuestion->direct_answer,
                'points'             => $session->currentQuestion->points,
                'time_limit_seconds' => $session->currentQuestion->time_limit_seconds,
                'pass_time_seconds'  => $session->currentQuestion->pass_time_seconds ?: 15,
                'pass_points'        => $session->currentQuestion->pass_points ?: 50,
                'explanation'        => $session->currentQuestion->explanation,
                'sort_order'         => $session->currentQuestion->sort_order,
            ];
        }

        $activeTurnTeamData = null;
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
        }

        return response()->json([
            'session_id'             => $session->id,
            'pin_code'               => $session->pin_code,
            'status'                 => $session->status,
            'current_question_index' => $session->current_question_index,
            'total_questions'        => $session->quiz->questions->count(),
            'seconds_remaining'      => $secondsRemaining,
            'server_time'            => $now->toIso8601String(),
            'current_question'       => $currentQuestionData,
            'active_turn_team'       => $activeTurnTeamData,
            'is_passed'              => (bool) $session->is_passed,
            'pass_sequence'          => $session->pass_sequence ?: [],
            'teams'                  => $teamsData,
            'teams_count'            => count($teamsData),
            'submitted_count'        => count($submittedTeamIds),
            'option_breakdown'       => $optionBreakdown,
            'fast_buzzer_team'       => $session->buzzerTeam ? [
                'id'        => $session->buzzerTeam->id,
                'team_name' => $session->buzzerTeam->team_name,
            ] : null,
        ]);
    }
}
