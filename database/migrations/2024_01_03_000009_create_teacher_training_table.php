<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('teacher_training', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id');
            $table->string('title');
            $table->string('organizer')->nullable();
            $table->enum('type', ['workshop', 'seminar', 'online_course', 'conference', 'certification', 'other'])->default('workshop');
            $table->date('from_date');
            $table->date('to_date')->nullable();
            $table->integer('hours')->nullable();
            $table->string('certificate_no')->nullable();
            $table->string('attachment_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('teacher_id');
            $table->foreign('teacher_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
    public function down(): void { Schema::dropIfExists('teacher_training'); }
};
