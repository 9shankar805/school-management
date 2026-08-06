<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            // Theme & colors
            $table->string('primary_color',   7)->nullable()->default('#1e4080')->after('description');
            $table->string('secondary_color', 7)->nullable()->default('#e6b800')->after('primary_color');
            $table->string('preset_theme',   30)->nullable()->default('royal_blue')->after('secondary_color');

            // Social links
            $table->string('facebook_url',  255)->nullable()->after('website');
            $table->string('twitter_url',   255)->nullable()->after('facebook_url');
            $table->string('instagram_url', 255)->nullable()->after('twitter_url');
            $table->string('youtube_url',   255)->nullable()->after('instagram_url');
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn([
                'primary_color', 'secondary_color', 'preset_theme',
                'facebook_url', 'twitter_url', 'instagram_url', 'youtube_url',
            ]);
        });
    }
};
