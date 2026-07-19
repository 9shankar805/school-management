<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('student_statuses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->enum('status', ['active', 'graduated', 'dropped_out', 'withdrawn', 'alumni', 'suspended', 'transferred']);
            $table->unsignedBigInteger('session_id')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();   // last class
            $table->date('effective_date');
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->string('graduation_certificate_no')->nullable();
            $table->string('alumni_batch')->nullable();           // e.g. "Class of 2024"
            $table->string('destination_school')->nullable();     // for transfers/dropouts
            $table->boolean('is_current')->default(true);        // only one current per student
            $table->unsignedBigInteger('processed_by');
            $table->timestamps();

            $table->index(['student_id', 'is_current']);
            $table->index('status');
            $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('processed_by')->references('id')->on('users');
        });
    }

    public function down(): void { Schema::dropIfExists('student_statuses'); }
};
