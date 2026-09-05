<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `live_quiz_questions` MODIFY COLUMN `question_type` VARCHAR(50) NOT NULL DEFAULT 'mcq'");
            DB::statement("ALTER TABLE `live_quizzes` MODIFY COLUMN `mode` VARCHAR(50) NOT NULL DEFAULT 'mixed'");
            DB::statement("ALTER TABLE `live_quiz_sessions` MODIFY COLUMN `status` VARCHAR(50) NOT NULL DEFAULT 'lobby'");
        }
    }

    public function down(): void
    {
        // No-op
    }
};
