<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->enum('category', [
                'academic', 'sports', 'arts', 'science', 'community',
                'leadership', 'competition', 'other',
            ])->default('other');
            $table->string('title');
            $table->string('award_type')->nullable();    // e.g. "Gold Medal", "1st Place", "Certificate"
            $table->text('description')->nullable();
            $table->string('issuing_body')->nullable();  // e.g. "National Science Olympiad"
            $table->enum('level', ['school', 'district', 'state', 'national', 'international'])->default('school');
            $table->date('awarded_date');
            $table->string('attachment_path')->nullable(); // uploaded certificate/photo
            $table->unsignedBigInteger('recorded_by');
            $table->timestamps();
            $table->softDeletes();

            $table->index('student_id');
            $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('recorded_by')->references('id')->on('users');
        });
    }

    public function down(): void { Schema::dropIfExists('achievements'); }
};
