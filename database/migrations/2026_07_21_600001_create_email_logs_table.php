<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable()->index();
            $table->unsignedBigInteger('sender_id')->nullable()->index();   // User who triggered
            $table->string('recipient_email');
            $table->unsignedBigInteger('recipient_id')->nullable()->index(); // User recipient
            $table->string('mailer', 50)->default('smtp');                  // smtp|mailgun|ses|log
            $table->string('message_id')->nullable()->comment('Mail server message-id header');
            $table->enum('type', [
                'attendance_alert',
                'fee_reminder',
                'general',
                'otp',
                'exam_result',
                'event',
                'emergency',
                'welcome',
                'password_reset',
                'report',
            ])->default('general');
            $table->string('subject');
            $table->text('body')->nullable();
            $table->json('attachments')->nullable()->comment('Array of file paths/names');
            $table->enum('status', ['pending', 'sent', 'failed', 'bounced'])->default('pending')->index();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->nullableMorphs('notifiable');  // polymorphic — student, teacher, etc.
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'status']);
            $table->index(['school_id', 'type']);
            $table->index('recipient_email');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
