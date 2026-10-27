<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('homework_acknowledgements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->default(1);
            $table->unsignedBigInteger('homework_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('parent_id');
            $table->timestamp('acknowledged_at');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['homework_id', 'student_id']);
            $table->foreign('homework_id')->references('id')->on('homeworks')->cascadeOnDelete();
            $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('parent_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homework_acknowledgements');
    }
};
