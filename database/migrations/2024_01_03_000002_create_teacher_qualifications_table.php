<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('teacher_qualifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id');
            $table->enum('type', ['degree', 'diploma', 'certification', 'training', 'other'])->default('degree');
            $table->string('title');            // e.g. "BSc Mathematics"
            $table->string('institution');
            $table->string('field_of_study')->nullable();
            $table->year('start_year')->nullable();
            $table->year('end_year')->nullable();
            $table->string('grade')->nullable();
            $table->string('attachment_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('teacher_id');
            $table->foreign('teacher_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
    public function down(): void { Schema::dropIfExists('teacher_qualifications'); }
};
