<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('scholarships', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('name');                        // e.g. "Merit Scholarship 2024"
            $table->enum('type', ['merit', 'need_based', 'sports', 'arts', 'other'])->default('merit');
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('percentage')->nullable();       // e.g. "50%" fee discount
            $table->date('awarded_date');
            $table->date('expiry_date')->nullable();
            $table->enum('status', ['active', 'expired', 'revoked'])->default('active');
            $table->text('criteria')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('awarded_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('student_id');
            $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('awarded_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scholarships');
    }
};
