<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Online exam module tables:
 *  - online_exams            (exam definition with time limit, attempts)
 *  - online_exam_questions   (MCQ / essay questions linked to online exam)
 *  - online_exam_attempts    (a student's attempt session)
 *  - online_exam_answers     (per-question answers within an attempt)
 *  - practical_exams         (practical exam records)
 *  - practical_exam_marks    (per-student marks for a practical exam)
 */
class CreateOnlineExamTables extends Migration
{
    public function up(): void
    {
        // ── Online Exams ──────────────────────────────────────────────────────
        if (!Schema::hasTable('online_exams')) {
            Schema::create('online_exams', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('instructions')->nullable();
                $table->foreignId('exam_id')->nullable()->constrained('exams')->nullOnDelete();
                $table->foreignId('course_id')->nullable()->constrained('courses')->nullOnDelete();
                $table->foreignId('class_id')->nullable()->constrained('school_classes')->nullOnDelete();
                $table->foreignId('session_id')->nullable()->constrained('school_sessions')->nullOnDelete();
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->integer('duration_minutes')->default(60);
                $table->integer('total_marks')->default(100);
                $table->integer('pass_marks')->default(40);
                $table->integer('max_attempts')->default(1);
                $table->boolean('shuffle_questions')->default(false);
                $table->boolean('show_result_immediately')->default(false);
                $table->boolean('is_published')->default(false);
                $table->dateTime('available_from')->nullable();
                $table->dateTime('available_to')->nullable();
                $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
                $table->timestamps();
                $table->index(['class_id', 'session_id']);
            });
        }

        // ── Online Exam Questions ─────────────────────────────────────────────
        if (!Schema::hasTable('online_exam_questions')) {
            Schema::create('online_exam_questions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('online_exam_id')->constrained('online_exams')->cascadeOnDelete();
                $table->text('question_text');
                $table->enum('question_type', ['mcq', 'true_false', 'short_answer', 'essay'])->default('mcq');
                $table->integer('marks')->default(1);
                $table->integer('sort_order')->default(0);
                $table->json('options')->nullable();
                $table->text('correct_answer')->nullable();
                $table->text('explanation')->nullable();
                $table->timestamps();
                $table->index(['online_exam_id', 'sort_order']);
            });
        }

        // ── Online Exam Attempts ──────────────────────────────────────────────
        if (!Schema::hasTable('online_exam_attempts')) {
            Schema::create('online_exam_attempts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('online_exam_id')->constrained('online_exams')->cascadeOnDelete();
                $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
                $table->integer('attempt_number')->default(1);
                $table->enum('status', ['in_progress', 'submitted', 'timed_out', 'graded'])->default('in_progress');
                $table->dateTime('started_at')->useCurrent();
                $table->dateTime('submitted_at')->nullable();
                $table->integer('score')->nullable();
                $table->decimal('percentage', 5, 2)->nullable();
                $table->boolean('passed')->nullable();
                $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->dateTime('graded_at')->nullable();
                $table->text('teacher_feedback')->nullable();
                $table->timestamps();
                $table->unique(['online_exam_id', 'student_id', 'attempt_number'], 'exam_attempts_uniq');
                $table->index('status');
            });
        }

        // ── Online Exam Answers ───────────────────────────────────────────────
        if (!Schema::hasTable('online_exam_answers')) {
            Schema::create('online_exam_answers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('attempt_id')->constrained('online_exam_attempts')->cascadeOnDelete();
                $table->foreignId('question_id')->constrained('online_exam_questions')->cascadeOnDelete();
                $table->text('answer_text')->nullable();
                $table->integer('marks_awarded')->nullable();
                $table->boolean('is_correct')->nullable();
                $table->text('feedback')->nullable();
                $table->timestamps();
                $table->unique(['attempt_id', 'question_id']);
            });
        }

        // ── Practical Exams ───────────────────────────────────────────────────
        if (!Schema::hasTable('practical_exams')) {
            Schema::create('practical_exams', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->foreignId('course_id')->nullable()->constrained('courses')->nullOnDelete();
                $table->foreignId('class_id')->nullable()->constrained('school_classes')->nullOnDelete();
                $table->foreignId('session_id')->nullable()->constrained('school_sessions')->nullOnDelete();
                $table->date('exam_date');
                $table->integer('total_marks')->default(50);
                $table->integer('pass_marks')->default(20);
                $table->string('venue')->nullable();
                $table->text('equipment_required')->nullable();
                $table->foreignId('examiner_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->index(['class_id', 'session_id']);
            });
        }

        // ── Practical Exam Marks ──────────────────────────────────────────────
        if (!Schema::hasTable('practical_exam_marks')) {
            Schema::create('practical_exam_marks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('practical_exam_id')->constrained('practical_exams')->cascadeOnDelete();
                $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
                $table->integer('marks_obtained')->default(0);
                $table->boolean('passed')->nullable();
                $table->text('remarks')->nullable();
                $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->unique(['practical_exam_id', 'student_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('practical_exam_marks');
        Schema::dropIfExists('practical_exams');
        Schema::dropIfExists('online_exam_answers');
        Schema::dropIfExists('online_exam_attempts');
        Schema::dropIfExists('online_exam_questions');
        Schema::dropIfExists('online_exams');
    }
}
