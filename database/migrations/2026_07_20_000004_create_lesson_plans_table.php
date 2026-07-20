<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lesson_plans', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('objectives')->nullable();
            $table->text('content')->nullable();          // lesson body / materials
            $table->text('teaching_methods')->nullable();
            $table->text('resources')->nullable();
            $table->text('homework_description')->nullable();
            $table->text('notes')->nullable();
            $table->date('planned_date');
            $table->integer('duration_minutes')->default(45);
            $table->string('status')->default('draft');   // draft, approved, completed
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('section_id')->nullable();
            $table->unsignedBigInteger('term_id')->nullable();
            $table->unsignedBigInteger('curriculum_topic_id')->nullable();
            $table->unsignedBigInteger('session_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('teacher_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('course_id')->references('id')->on('courses')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_plans');
    }
};
