<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLandingPageFieldsToSchoolsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->text('about_us')->nullable()->after('description');
            $table->text('mission')->nullable()->after('about_us');
            $table->text('vision')->nullable()->after('mission');
            $table->text('history')->nullable()->after('vision');
            $table->text('principal_message')->nullable()->after('history');
            $table->json('gallery_images')->nullable()->after('principal_message');
            $table->text('admission_info')->nullable()->after('gallery_images');
            $table->text('result_info')->nullable()->after('admission_info');
            $table->text('notice_board')->nullable()->after('result_info');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn([
                'about_us',
                'mission',
                'vision',
                'history',
                'principal_message',
                'gallery_images',
                'admission_info',
                'result_info',
                'notice_board'
            ]);
        });
    }
}
