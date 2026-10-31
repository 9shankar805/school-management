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
        if (Schema::hasTable('academic_settings') && !Schema::hasColumn('academic_settings', 'roll_number_mode')) {
            Schema::table('academic_settings', function (Blueprint $table) {
                $table->string('roll_number_mode', 30)->default('yearly')->after('marks_submission_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('academic_settings') && Schema::hasColumn('academic_settings', 'roll_number_mode')) {
            Schema::table('academic_settings', function (Blueprint $table) {
                $table->dropColumn('roll_number_mode');
            });
        }
    }
};

