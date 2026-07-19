<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('admission_applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_number')->unique();
            // Applicant info (before user account exists)
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->date('birthday')->nullable();
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable();
            $table->string('nationality')->nullable();
            $table->string('religion')->nullable();
            $table->string('blood_type')->nullable();
            $table->text('address')->nullable();
            // Academic details requested
            $table->unsignedBigInteger('session_id')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();
            // Guardian info
            $table->string('guardian_name')->nullable();
            $table->string('guardian_phone')->nullable();
            $table->string('guardian_email')->nullable();
            $table->string('guardian_relation')->nullable();
            // Previous school
            $table->string('previous_school')->nullable();
            $table->string('previous_class')->nullable();
            // Workflow
            $table->enum('status', ['pending', 'under_review', 'approved', 'rejected', 'enrolled'])->default('pending');
            $table->text('reviewer_notes')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            // Linked student after enrollment
            $table->unsignedBigInteger('student_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('session_id');
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('student_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_applications');
    }
};
