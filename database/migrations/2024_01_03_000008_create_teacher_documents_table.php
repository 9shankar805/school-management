<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('teacher_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id');
            $table->enum('document_type', [
                'degree_certificate', 'national_id', 'passport',
                'experience_letter', 'appointment_letter',
                'police_clearance', 'medical_certificate', 'photo', 'other',
            ]);
            $table->string('title');
            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->unsignedBigInteger('uploaded_by');
            $table->timestamps();
            $table->softDeletes();
            $table->index('teacher_id');
            $table->foreign('teacher_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('uploaded_by')->references('id')->on('users');
        });
    }
    public function down(): void { Schema::dropIfExists('teacher_documents'); }
};
