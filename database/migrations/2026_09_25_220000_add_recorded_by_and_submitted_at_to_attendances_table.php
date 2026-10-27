<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'recorded_by')) {
                $table->unsignedBigInteger('recorded_by')->nullable()->after('status')->index();
            }
            if (!Schema::hasColumn('attendances', 'submitted_at')) {
                $table->dateTime('submitted_at')->nullable()->after('recorded_by');
            }
            if (!Schema::hasColumn('attendances', 'taken_time')) {
                $table->string('taken_time', 20)->nullable()->after('submitted_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['recorded_by', 'submitted_at', 'taken_time']);
        });
    }
};
