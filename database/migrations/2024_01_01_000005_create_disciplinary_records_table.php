<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('disciplinary_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->date('incident_date');
            $table->enum('severity', ['minor', 'moderate', 'major'])->default('minor');
            $table->string('incident_type');  // e.g. "Late arrival", "Misconduct"
            $table->text('description');
            $table->text('action_taken')->nullable();
            $table->text('parent_notified')->nullable();
            $table->boolean('resolved')->default(false);
            $table->unsignedBigInteger('reported_by');
            $table->timestamps();
            $table->softDeletes();

            $table->index('student_id');
            $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('reported_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disciplinary_records');
    }
};
