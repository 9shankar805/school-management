<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->string('name');             // e.g. "First Term", "Mid-Year", "Final"
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedBigInteger('semester_id');
            $table->unsignedBigInteger('session_id');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('semester_id')->references('id')->on('semesters')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('terms');
    }
};
