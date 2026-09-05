<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsClassTeacherToAssignedTeachersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('assigned_teachers')) {
            Schema::table('assigned_teachers', function (Blueprint $table) {
                if (!Schema::hasColumn('assigned_teachers', 'is_class_teacher')) {
                    $table->boolean('is_class_teacher')->default(false)->after('course_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('assigned_teachers')) {
            Schema::table('assigned_teachers', function (Blueprint $table) {
                if (Schema::hasColumn('assigned_teachers', 'is_class_teacher')) {
                    $table->dropColumn('is_class_teacher');
                }
            });
        }
    }
}
