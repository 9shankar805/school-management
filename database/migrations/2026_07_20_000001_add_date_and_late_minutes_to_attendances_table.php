<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Dedicated date column (backfill from created_at below)
            $table->date('date')->nullable()->after('session_id');
            // Late arrival tracking (minutes after school start; 0 = on time)
            $table->unsignedSmallInteger('late_minutes')->default(0)->after('date');
            // Check-in time for QR / manual time-tracking
            $table->time('check_in')->nullable()->after('late_minutes');
            // Index for date-range queries
            $table->index(['student_id', 'date']);
            $table->index(['class_id', 'date']);
        });

        // Back-fill date from created_at for all existing rows
        DB::statement('UPDATE attendances SET date = DATE(created_at) WHERE date IS NULL');

        // Make date non-nullable now that it's filled
        Schema::table('attendances', function (Blueprint $table) {
            $table->date('date')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['student_id', 'date']);
            $table->dropIndex(['class_id', 'date']);
            $table->dropColumn(['date', 'late_minutes', 'check_in']);
        });
    }
};
