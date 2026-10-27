<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_admit_card_locked')) {
                $table->boolean('is_admit_card_locked')->default(false)->after('student_status');
            }
            if (!Schema::hasColumn('users', 'admit_card_lock_reason')) {
                $table->string('admit_card_lock_reason', 255)->nullable()->after('is_admit_card_locked');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_admit_card_locked')) {
                $table->dropColumn('is_admit_card_locked');
            }
            if (Schema::hasColumn('users', 'admit_card_lock_reason')) {
                $table->dropColumn('admit_card_lock_reason');
            }
        });
    }
};
