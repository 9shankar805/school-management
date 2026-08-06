<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable()->index();
            $table->unsignedBigInteger('sender_id')->nullable()->index();   // User who triggered
            $table->string('recipient_phone', 30);
            $table->unsignedBigInteger('recipient_id')->nullable()->index(); // User recipient
            $table->string('gateway', 50)->default('twilio');               // twilio|vonage|custom
            $table->string('gateway_message_id')->nullable();
            $table->enum('type', [
                'attendance_alert',
                'fee_reminder',
                'general',
                'otp',
                'exam_result',
                'event',
                'emergency',
            ])->default('general');
            $table->text('message');
            $table->enum('status', ['pending', 'sent', 'delivered', 'failed'])->default('pending')->index();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->decimal('cost', 8, 4)->nullable()->comment('Gateway cost per SMS');
            $table->nullableMorphs('notifiable');  // polymorphic — student, teacher, etc.
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'status']);
            $table->index(['school_id', 'type']);
            $table->index('recipient_phone');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
