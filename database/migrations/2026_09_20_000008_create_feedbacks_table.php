<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->default(1);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('submitter_name')->nullable();
            $table->string('submitter_role')->default('parent'); // student, parent, teacher, staff, visitor
            $table->boolean('is_anonymous')->default(false);
            $table->string('category')->default('general'); // academic, transport, fee_billing, facilities, canteen, other
            $table->string('priority')->default('normal'); // normal, high, urgent
            $table->string('title');
            $table->text('description');
            $table->string('status')->default('pending'); // pending, in_review, resolved, rejected
            $table->text('admin_response')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
};
