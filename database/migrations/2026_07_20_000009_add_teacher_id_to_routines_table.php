<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Add teacher_id and room columns to routines for advanced timetable support.
     */
    public function up(): void
    {
        Schema::table('routines', function (Blueprint $table) {
            $table->unsignedBigInteger('teacher_id')->nullable()->after('course_id');
            $table->string('room')->nullable()->after('teacher_id');
            $table->string('color')->nullable()->after('room'); // per-course color for timetable UI
        });
    }

    public function down(): void
    {
        Schema::table('routines', function (Blueprint $table) {
            $table->dropColumn(['teacher_id', 'room', 'color']);
        });
    }
};
