<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('student_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->enum('document_type', [
                'birth_certificate', 'national_id', 'passport',
                'previous_marksheet', 'transfer_certificate',
                'medical_certificate', 'photo', 'other',
            ]);
            $table->string('title');
            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable(); // bytes
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('uploaded_by');
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('student_id');
            $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('uploaded_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_documents');
    }
};
