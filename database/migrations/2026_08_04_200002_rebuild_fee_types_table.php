<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 1B — Rebuild fee_types table with proper columns.
 *
 * fee_types is a master lookup table for every kind of fee that a school can charge.
 * Examples: Tuition Fee, Exam Fee, Transport Fee, Hostel Fee, Library Fee, etc.
 *
 * Each FeeType can be:
 *  - linked to a FeeCategory (for grouping)
 *  - marked as taxable (VAT applicable)
 *  - set as one-time or recurring
 *  - triggered automatically on specific events (admission, exam_publish, monthly)
 */
return new class extends Migration
{
    public function up(): void
    {
        // Drop existing empty stub and recreate with full schema
        Schema::dropIfExists('fee_types');

        Schema::create('fee_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // "Tuition Fee", "Exam Fee", etc.
            $table->string('code', 20)->nullable()->unique(); // TUT, EXAM, TRP
            $table->unsignedBigInteger('category_id')->nullable();
            $table->text('description')->nullable();

            // Billing behaviour
            $table->enum('frequency', [
                'once', 'monthly', 'quarterly', 'half_yearly', 'yearly'
            ])->default('monthly');

            // Auto-trigger: when should this fee be generated?
            $table->enum('trigger', [
                'manual',          // Only via bulk invoice generation
                'admission',       // Auto on student enroll
                'monthly',         // Auto on 1st of each month
                'exam_publish',    // Auto when an exam is published
                'annual',          // Auto once per academic year
            ])->default('manual');

            // Tax configuration (Nepal VAT = 13% where applicable)
            $table->boolean('is_taxable')->default(false);
            $table->decimal('tax_rate', 6, 2)->default(0.00)
                  ->comment('Tax percentage (e.g. 13 for VAT 13%). 0 = no tax');

            // Status
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('school_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('category_id')->references('id')->on('fee_categories')->nullOnDelete();
            $table->index(['school_id', 'is_active']);
            $table->index('trigger');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_types');

        // Restore empty stub
        Schema::create('fee_types', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }
};
