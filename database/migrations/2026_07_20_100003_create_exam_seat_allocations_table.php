<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Exam Seat Allocations
 * Maps one student → one seat number within one exam schedule sitting.
 * Unique constraints prevent double-booking.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_seat_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schedule_id');
            $table->unsignedBigInteger('student_id');
            $table->string('seat_number', 20);
            $table->text('notes')->nullable();
            $table->timestamps();

            // One student one seat per sitting; one seat number per sitting
            $table->unique(['schedule_id', 'student_id']);
            $table->unique(['schedule_id', 'seat_number']);
            $table->index('student_id');

            $table->foreign('schedule_id')->references('id')->on('exam_schedules')->cascadeOnDelete();
            $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_seat_allocations');
    }
};
