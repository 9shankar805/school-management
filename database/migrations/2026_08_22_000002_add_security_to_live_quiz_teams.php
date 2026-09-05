<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSecurityToLiveQuizTeams extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('live_quiz_teams')) {
            Schema::table('live_quiz_teams', function (Blueprint $table) {
                if (!Schema::hasColumn('live_quiz_teams', 'tab_switch_count')) {
                    $table->integer('tab_switch_count')->default(0)->after('wrong_count');
                }
                if (!Schema::hasColumn('live_quiz_teams', 'last_security_event')) {
                    $table->string('last_security_event')->nullable()->after('tab_switch_count');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('live_quiz_teams')) {
            Schema::table('live_quiz_teams', function (Blueprint $table) {
                if (Schema::hasColumn('live_quiz_teams', 'tab_switch_count')) {
                    $table->dropColumn('tab_switch_count');
                }
                if (Schema::hasColumn('live_quiz_teams', 'last_security_event')) {
                    $table->dropColumn('last_security_event');
                }
            });
        }
    }
}
