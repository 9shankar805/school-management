<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeeStructuresTable extends Migration
{
    public function up(): void
    {
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->string('name');                                              // e.g. "Grade 10 — 2026 Term 1"
            $table->foreignId('session_id')->nullable()->constrained('school_sessions')->onDelete('set null');
            $table->foreignId('class_id')->nullable()->constrained('school_classes')->onDelete('set null');
            $table->foreignId('program_id')->nullable()->constrained('programs')->onDelete('set null');
            $table->foreignId('term_id')->nullable()->constrained('terms')->onDelete('set null');
            $table->decimal('total_amount', 12, 2)->default(0);                 // auto-computed from items
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['session_id', 'class_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_structures');
    }
}
