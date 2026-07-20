<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Exam Schedules / Timetable
 * One row = one sitting of one exam in one hall with one invigilator.
 * An exam may have multiple schedule rows (parallel halls, written + practical).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exam_id');
            $table->unsignedBigInteger('hall_id')->nullable();
            $table->date('exam_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedBigInteger('invigilator_id')->nullable(); // references users
            $table->text('notes')->nullable();
            $table->unsignedInteger('session_id');
            $table->timestamps();

            $table->index(['exam_id', 'exam_date']);
            $table->index('session_id');

            $table->foreign('exam_id')->references('id')->on('exams')->cascadeOnDelete();
            $table->foreign('hall_id')->references('id')->on('exam_halls')->nullOnDelete();
            $table->foreign('invigilator_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_schedules');
    }
};
