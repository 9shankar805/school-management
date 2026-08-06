<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColorDescriptionToEventsTable extends Migration
{
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'color')) {
                $table->string('color')->nullable()->default('#6366f1')->after('end');
            }
            if (!Schema::hasColumn('events', 'description')) {
                $table->text('description')->nullable()->after('color');
            }
            if (!Schema::hasColumn('events', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(array_filter(['color', 'description', 'deleted_at'], function ($col) {
                return Schema::hasColumn('events', $col);
            }));
        });
    }
}
