<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('terminal_roll_numbers')) {
            Schema::create('terminal_roll_numbers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('school_id')->nullable();
                $table->unsignedInteger('session_id');
                $table->unsignedInteger('semester_id'); // target terminal
                $table->unsignedInteger('class_id');
                $table->unsignedInteger('section_id');
                $table->unsignedInteger('student_id');
                $table->string('roll_no', 30);
                $table->unsignedInteger('source_semester_id')->nullable(); // source terminal used for ranking
                $table->unsignedInteger('rank')->nullable();
                $table->decimal('total_marks', 10, 2)->nullable();
                $table->decimal('gpa', 4, 2)->nullable();
                $table->decimal('percentage', 6, 2)->nullable();
                $table->softDeletes();
                $table->timestamps();

                $table->index(['session_id', 'semester_id', 'class_id', 'section_id'], 'term_roll_session_sem_class_sec_idx');
                $table->index(['session_id', 'student_id'], 'term_roll_session_student_idx');
                $table->unique(['session_id', 'semester_id', 'student_id'], 'term_roll_session_sem_student_uniq');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terminal_roll_numbers');
    }
};

