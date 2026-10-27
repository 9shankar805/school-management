<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('live_quiz_questions')) {
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE `live_quiz_questions` MODIFY COLUMN `correct_option` VARCHAR(255) NULL");
            } else {
                Schema::table('live_quiz_questions', function (Blueprint $table) {
                    $table->string('correct_option', 255)->nullable()->change();
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('live_quiz_questions')) {
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE `live_quiz_questions` MODIFY COLUMN `correct_option` VARCHAR(10) NULL");
            }
        }
    }
};
