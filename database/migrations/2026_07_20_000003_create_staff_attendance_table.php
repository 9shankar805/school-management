<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Staff Attendance
 * ─────────────────────────────────────────────────────────────────────────────
 * Covers all non-teacher staff (admin, accountant, librarian, receptionist,
 * HR manager, transport manager, hostel manager, etc.).
 * Teacher attendance lives in the separate `teacher_attendance` table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_attendance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_id');
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'late', 'half_day', 'on_leave'])
                  ->default('present');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->unsignedSmallInteger('late_minutes')->default(0);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('marked_by')->nullable();
            $table->timestamps();

            $table->unique(['staff_id', 'date']);
            $table->index(['staff_id', 'date']);
            $table->foreign('staff_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('marked_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_attendance');
    }
};
