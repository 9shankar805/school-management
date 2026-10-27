<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EnhanceEventsTableForSchoolCalendar extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'category')) {
                $table->string('category', 50)->default('academic')->after('title'); // holiday, exam, academic, sports, celebration, meeting, vacation, other
            }
            if (!Schema::hasColumn('events', 'is_holiday')) {
                $table->boolean('is_holiday')->default(false)->after('category');
            }
            if (!Schema::hasColumn('events', 'start_bs')) {
                $table->string('start_bs', 20)->nullable()->after('end');
            }
            if (!Schema::hasColumn('events', 'end_bs')) {
                $table->string('end_bs', 20)->nullable()->after('start_bs');
            }
            if (!Schema::hasColumn('events', 'audience')) {
                $table->string('audience', 30)->default('all')->after('end_bs'); // all, students, teachers, staff, parents
            }
            if (!Schema::hasColumn('events', 'academic_term')) {
                $table->string('academic_term', 50)->nullable()->after('audience');
            }
            if (!Schema::hasColumn('events', 'venue')) {
                $table->string('venue', 150)->nullable()->after('academic_term');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            $cols = ['category', 'is_holiday', 'start_bs', 'end_bs', 'audience', 'academic_term', 'venue'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('events', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
