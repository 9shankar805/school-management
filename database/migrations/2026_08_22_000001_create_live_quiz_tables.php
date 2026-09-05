<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration for Interactive School Group Live Quiz Competition System.
 */
class CreateLiveQuizTables extends Migration
{
    public function up(): void
    {
        // 1. Live Quizzes (Master Quizzes)
        if (!Schema::hasTable('live_quizzes')) {
            Schema::create('live_quizzes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('school_id')->nullable()->index();
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->foreignId('course_id')->nullable()->constrained('courses')->nullOnDelete();
                $table->foreignId('class_id')->nullable()->constrained('school_classes')->nullOnDelete();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('category')->default('General');
                $table->enum('mode', ['mcq', 'buzzer', 'mixed'])->default('mcq');
                $table->integer('default_time_limit')->default(30); // in seconds
                $table->integer('default_points')->default(100);
                $table->boolean('speed_bonus')->default(true); // faster answers earn more points
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 2. Live Quiz Questions
        if (!Schema::hasTable('live_quiz_questions')) {
            Schema::create('live_quiz_questions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('live_quiz_id')->constrained('live_quizzes')->cascadeOnDelete();
                $table->text('question_text');
                $table->string('image_url')->nullable();
                $table->enum('question_type', ['mcq', 'buzzer', 'true_false'])->default('mcq');
                $table->json('options')->nullable(); // [{"key":"A","text":"Option 1"},{"key":"B","text":"Option 2"}...]
                $table->string('correct_option', 10)->nullable(); // A, B, C, D, TRUE, FALSE
                $table->integer('points')->default(100);
                $table->integer('time_limit_seconds')->default(30);
                $table->text('explanation')->nullable();
                $table->integer('sort_order')->default(1);
                $table->timestamps();
            });
        }

        // 3. Live Quiz Sessions (Running Game Instances)
        if (!Schema::hasTable('live_quiz_sessions')) {
            Schema::create('live_quiz_sessions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('school_id')->nullable()->index();
                $table->foreignId('live_quiz_id')->constrained('live_quizzes')->cascadeOnDelete();
                $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
                $table->string('pin_code', 10)->unique();
                $table->enum('status', ['lobby', 'question_active', 'question_locked', 'question_result', 'leaderboard', 'completed'])->default('lobby');
                $table->integer('current_question_index')->default(0);
                $table->foreignId('current_question_id')->nullable()->constrained('live_quiz_questions')->nullOnDelete();
                $table->timestamp('question_started_at')->nullable();
                $table->timestamp('question_expires_at')->nullable();
                $table->unsignedBigInteger('fast_buzzer_team_id')->nullable();
                $table->integer('max_teams')->default(8);
                $table->json('settings')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
            });
        }

        // 4. Live Quiz Teams (4-5 Groups with Captain)
        if (!Schema::hasTable('live_quiz_teams')) {
            Schema::create('live_quiz_teams', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('school_id')->nullable()->index();
                $table->foreignId('live_quiz_session_id')->constrained('live_quiz_sessions')->cascadeOnDelete();
                $table->string('team_name');
                $table->string('captain_name');
                $table->string('captain_token', 64)->unique();
                $table->string('team_color', 20)->default('#ef4444');
                $table->string('avatar_icon', 50)->default('lion');
                $table->integer('total_score')->default(0);
                $table->integer('current_rank')->default(1);
                $table->integer('correct_count')->default(0);
                $table->integer('wrong_count')->default(0);
                $table->boolean('is_connected')->default(true);
                $table->timestamp('last_ping_at')->nullable();
                $table->timestamps();
            });
        }

        // 5. Live Quiz Answers (Submissions per Question per Team)
        if (!Schema::hasTable('live_quiz_answers')) {
            Schema::create('live_quiz_answers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('live_quiz_session_id')->constrained('live_quiz_sessions')->cascadeOnDelete();
                $table->foreignId('live_quiz_question_id')->constrained('live_quiz_questions')->cascadeOnDelete();
                $table->foreignId('live_quiz_team_id')->constrained('live_quiz_teams')->cascadeOnDelete();
                $table->string('selected_option', 10)->nullable();
                $table->boolean('is_correct')->default(false);
                $table->integer('points_awarded')->default(0);
                $table->integer('response_time_ms')->default(0); // milliseconds from question start
                $table->boolean('is_buzzer_winner')->default(false);
                $table->timestamp('submitted_at')->nullable();
                $table->timestamps();

                $table->unique(['live_quiz_session_id', 'live_quiz_question_id', 'live_quiz_team_id'], 'unique_team_answer');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('live_quiz_answers');
        Schema::dropIfExists('live_quiz_teams');
        Schema::dropIfExists('live_quiz_sessions');
        Schema::dropIfExists('live_quiz_questions');
        Schema::dropIfExists('live_quizzes');
    }
}
