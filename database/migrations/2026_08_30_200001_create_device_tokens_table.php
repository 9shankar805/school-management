<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stores FCM / push notification device tokens per user.
 * One user can have multiple tokens (phone, tablet, different browsers).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('school_id')->nullable();
            $table->string('token', 512);              // FCM registration token
            $table->string('provider', 20)->default('fcm'); // fcm | onesignal
            $table->string('platform', 20)->default('web'); // web | android | ios
            $table->string('device_name')->nullable(); // e.g. "Chrome on Windows"
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            // One user can have the same token only once
            $table->unique(['user_id', 'token']);
            $table->index('user_id');
            $table->index('school_id');

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
    }
};
