<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('performance_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedBigInteger('reviewer_id');
            $table->string('review_period');   // e.g. "2024-Q1", "2024 Annual"
            $table->date('review_date');
            // Ratings 1-5
            $table->unsignedTinyInteger('teaching_quality')->nullable();
            $table->unsignedTinyInteger('punctuality')->nullable();
            $table->unsignedTinyInteger('student_engagement')->nullable();
            $table->unsignedTinyInteger('communication')->nullable();
            $table->unsignedTinyInteger('professionalism')->nullable();
            $table->decimal('overall_rating', 3, 1)->nullable(); // computed avg
            $table->text('strengths')->nullable();
            $table->text('areas_for_improvement')->nullable();
            $table->text('goals')->nullable();
            $table->text('reviewer_comments')->nullable();
            $table->enum('status', ['draft', 'submitted', 'acknowledged'])->default('draft');
            $table->timestamps();
            $table->index('teacher_id');
            $table->foreign('teacher_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('reviewer_id')->references('id')->on('users');
        });
    }
    public function down(): void { Schema::dropIfExists('performance_reviews'); }
};
