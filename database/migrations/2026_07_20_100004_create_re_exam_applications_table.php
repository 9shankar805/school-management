<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Re-Exam / Supplementary Exam Applications
 * A student (or admin) applies for a re-sit on a specific course/semester.
 * Workflow: pending → approved → scheduled → result_entered → completed | rejected
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('re_exam_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedInteger('course_id');
            $table->unsignedInteger('class_id');
            $table->unsignedInteger('section_id')->default(0);
            $table->unsignedInteger('semester_id');
            $table->unsignedInteger('session_id');
            $table->enum('status', [
                'pending', 'approved', 'rejected', 'scheduled', 'result_entered', 'completed'
            ])->default('pending');
            $table->text('reason')->nullable();              // student's reason
            $table->text('admin_notes')->nullable();         // reviewer notes
            $table->float('original_marks')->default(0);     // marks in original exam
            $table->float('re_exam_marks')->nullable();      // marks after re-sit
            $table->date('re_exam_date')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'session_id']);
            $table->index('status');

            $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('re_exam_applications');
    }
};
