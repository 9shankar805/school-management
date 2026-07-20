<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * QR Attendance Tokens
 * ─────────────────────────────────────────────────────────────────────────────
 * A teacher generates a session-token for a specific class/course/date.
 * The token encodes to a QR code which students scan. The system then marks
 * the scanning student present automatically (optionally within a time window).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qr_attendance_tokens', function (Blueprint $table) {
            $table->id();
            // Who generated this token (teacher)
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedInteger('class_id');
            $table->unsignedInteger('section_id')->default(0);
            $table->unsignedInteger('course_id')->default(0);
            $table->unsignedInteger('session_id');
            $table->date('date');
            // Unique random token embedded in the QR code URL
            $table->string('token', 64)->unique();
            // Window: how many minutes the QR code remains valid (0 = unlimited)
            $table->unsignedSmallInteger('valid_minutes')->default(30);
            // School-start reference for late calculation (e.g. "08:00")
            $table->time('school_start')->default('08:00:00');
            // Whether the token has been manually revoked
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['token']);
            $table->index(['class_id', 'date']);
            $table->foreign('teacher_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_attendance_tokens');
    }
};
