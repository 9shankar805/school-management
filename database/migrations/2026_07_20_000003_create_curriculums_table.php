<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('curriculums', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('program_id')->nullable();
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('session_id');
            $table->string('status')->default('draft'); // draft, published, archived
            $table->text('objectives')->nullable();
            $table->text('learning_outcomes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('program_id')->references('id')->on('programs')->nullOnDelete();
        });

        Schema::create('curriculum_topics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('curriculum_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('term_id')->nullable();
            $table->integer('order')->default(0);
            $table->integer('estimated_hours')->default(1);
            $table->timestamps();

            $table->foreign('curriculum_id')->references('id')->on('curriculums')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curriculum_topics');
        Schema::dropIfExists('curriculums');
    }
};
