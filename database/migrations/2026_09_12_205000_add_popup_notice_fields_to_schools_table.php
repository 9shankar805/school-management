<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->boolean('popup_active')->default(false)->after('notice_board');
            $table->string('popup_title')->nullable()->after('popup_active');
            $table->text('popup_subtitle')->nullable()->after('popup_title');
            $table->string('popup_image', 500)->nullable()->after('popup_subtitle');
            $table->string('popup_btn_text')->nullable()->after('popup_image');
            $table->string('popup_btn_url', 500)->nullable()->after('popup_btn_text');
            $table->date('popup_expires_at')->nullable()->after('popup_btn_url');
            $table->boolean('popup_show_once')->default(true)->after('popup_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn([
                'popup_active',
                'popup_title',
                'popup_subtitle',
                'popup_image',
                'popup_btn_text',
                'popup_btn_url',
                'popup_expires_at',
                'popup_show_once',
            ]);
        });
    }
};
