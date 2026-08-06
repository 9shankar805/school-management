<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 4A — Configurable salary components.
 *
 * salary_components  — master list of all salary heads (Basic, HRA, Medical, etc.)
 * staff_salary_structures — per-staff assignment of components with amounts
 *
 * This replaces the single 'allowances' lump sum with a proper breakdown.
 *
 * Example:
 *   Teacher Ram — Monthly Salary
 *     Basic Salary          30,000   (earning)
 *     House Rent Allowance   4,000   (earning)
 *     Medical Allowance      1,500   (earning)
 *     Transport Allowance    2,000   (earning)
 *     ────────────────────────────
 *     Gross                 37,500
 *     Income Tax TDS        -2,000   (deduction)
 *     Provident Fund        -1,500   (deduction)
 *     CIT                   -  750   (deduction)
 *     ────────────────────────────
 *     Net                   33,250
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── salary_components (master lookup) ─────────────────────────────────
        Schema::create('salary_components', function (Blueprint $table) {
            $table->id();
            $table->string('name');                   // "Basic Salary", "HRA", "Medical Allowance"
            $table->string('code', 20)->unique();     // BASIC, HRA, MED, TRP, TDS, PF, CIT, SSF
            $table->enum('type', ['earning', 'deduction'])->default('earning');
            $table->enum('calculation', [
                'fixed',          // Fixed amount set per employee
                'percentage',     // % of basic salary
                'attendance',     // Computed from attendance (absent day deduction)
                'formula',        // Custom formula string
            ])->default('fixed');
            $table->decimal('default_value', 12, 2)->default(0)
                  ->comment('Default amount or percentage — overridden per employee');
            $table->string('formula')->nullable()
                  ->comment('e.g. "basic * 0.10" for formula-based');
            $table->boolean('is_taxable')->default(false)
                  ->comment('Is this component included in taxable income?');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false)
                  ->comment('System components (Basic, TDS, PF) cannot be deleted');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->unsignedBigInteger('school_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'type', 'is_active']);
        });

        // ── staff_salary_structures (per-employee component amounts) ──────────
        Schema::create('staff_salary_structures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');        // staff / teacher
            $table->unsignedBigInteger('component_id');
            $table->decimal('amount', 12, 2)->default(0)
                  ->comment('Fixed amount, or % value for percentage-type components');
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable()
                  ->comment('NULL = currently active');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('component_id')->references('id')->on('salary_components')->cascadeOnDelete();
            $table->index(['user_id', 'component_id']);
            $table->index('effective_from');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_salary_structures');
        Schema::dropIfExists('salary_components');
    }
};
