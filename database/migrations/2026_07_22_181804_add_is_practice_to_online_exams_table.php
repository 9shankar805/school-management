<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('online_exams', function (Blueprint $table) {
            $table->boolean('is_practice')->default(false)->after('is_published')
                ->comment('Practice quizzes are ungraded, unlimited attempts, no marks recorded');
        });
    }

    public function down(): void
    {
        Schema::table('online_exams', function (Blueprint $table) {
            $table->dropColumn('is_practice');
        });
    }
};
