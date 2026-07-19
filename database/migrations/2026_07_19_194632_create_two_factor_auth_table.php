<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTwoFactorAuthTable extends Migration
{
    public function up()
    {
        Schema::create('two_factor_auth', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique()->index();
            $table->boolean('enabled')->default(false);
            $table->string('method', 20)->default('totp'); // totp|sms|email
            $table->text('secret')->nullable();             // encrypted TOTP secret
            $table->text('recovery_codes')->nullable();     // JSON encrypted recovery codes
            $table->timestamp('confirmed_at')->nullable();  // null = setup not confirmed yet
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('two_factor_auth');
    }
}
