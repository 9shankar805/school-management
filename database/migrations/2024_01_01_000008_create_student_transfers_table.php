<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('student_transfers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->enum('transfer_type', ['inter_class', 'inter_section', 'inter_school'])->default('inter_class');
            // From
            $table->unsignedBigInteger('from_session_id')->nullable();
            $table->unsignedBigInteger('from_class_id')->nullable();
            $table->unsignedBigInteger('from_section_id')->nullable();
            $table->string('from_school')->nullable();   // for inter_school
            // To
            $table->unsignedBigInteger('to_session_id')->nullable();
            $table->unsignedBigInteger('to_class_id')->nullable();
            $table->unsignedBigInteger('to_section_id')->nullable();
            $table->string('to_school')->nullable();     // for inter_school
            // Meta
            $table->date('transfer_date');
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('student_id');
            $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_transfers');
    }
};
