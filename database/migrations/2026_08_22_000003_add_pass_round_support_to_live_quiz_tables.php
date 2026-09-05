<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPassRoundSupportToLiveQuizTables extends Migration
{
    public function up(): void
    {
        // 1. Update live_quiz_questions
        if (Schema::hasTable('live_quiz_questions')) {
            Schema::table('live_quiz_questions', function (Blueprint $table) {
                if (!Schema::hasColumn('live_quiz_questions', 'direct_answer')) {
                    $table->text('direct_answer')->nullable()->after('correct_option');
                }
                if (!Schema::hasColumn('live_quiz_questions', 'pass_time_seconds')) {
                    $table->integer('pass_time_seconds')->default(15)->after('time_limit_seconds');
                }
                if (!Schema::hasColumn('live_quiz_questions', 'pass_points')) {
                    $table->integer('pass_points')->default(50)->after('points');
                }
            });
        }

        // 2. Update live_quiz_sessions
        if (Schema::hasTable('live_quiz_sessions')) {
            Schema::table('live_quiz_sessions', function (Blueprint $table) {
                if (!Schema::hasColumn('live_quiz_sessions', 'active_turn_team_id')) {
                    $table->unsignedBigInteger('active_turn_team_id')->nullable()->after('fast_buzzer_team_id');
                }
                if (!Schema::hasColumn('live_quiz_sessions', 'is_passed')) {
                    $table->boolean('is_passed')->default(false)->after('active_turn_team_id');
                }
                if (!Schema::hasColumn('live_quiz_sessions', 'pass_sequence')) {
                    $table->json('pass_sequence')->nullable()->after('is_passed');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('live_quiz_questions')) {
            Schema::table('live_quiz_questions', function (Blueprint $table) {
                $table->dropColumn(['direct_answer', 'pass_time_seconds', 'pass_points']);
            });
        }
        if (Schema::hasTable('live_quiz_sessions')) {
            Schema::table('live_quiz_sessions', function (Blueprint $table) {
                $table->dropColumn(['active_turn_team_id', 'is_passed', 'pass_sequence']);
            });
        }
    }
}
