<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Finance extras:
 *  - salary_slips (staff payroll)
 *  - tax_rates
 *  - ledger_entries (accounting journal)
 */
class CreateFinanceExtrasTables extends Migration
{
    public function up(): void
    {
        // ── Salary Slips (non-teacher staff) ─────────────────────────────────
        Schema::create('salary_slips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->decimal('allowances', 12, 2)->default(0);
            $table->decimal('overtime', 12, 2)->default(0);
            $table->decimal('bonus', 12, 2)->default(0);
            $table->decimal('gross_salary', 12, 2)->default(0);
            $table->decimal('tax_deduction', 12, 2)->default(0);
            $table->decimal('provident_fund', 12, 2)->default(0);
            $table->decimal('other_deductions', 12, 2)->default(0);
            $table->decimal('net_salary', 12, 2)->default(0);
            $table->unsignedTinyInteger('working_days')->default(0);
            $table->unsignedTinyInteger('present_days')->default(0);
            $table->unsignedTinyInteger('absent_days')->default(0);
            $table->unsignedTinyInteger('leave_days')->default(0);
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
            $table->enum('payment_method', ['cash', 'bank_transfer', 'cheque'])->nullable();
            $table->date('payment_date')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['staff_id', 'month', 'year']);
            $table->index('status');
        });

        // ── Tax Rates ─────────────────────────────────────────────────────────
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['income_tax', 'vat', 'provident_fund', 'other'])->default('income_tax');
            $table->decimal('rate', 8, 4)->comment('Percentage 0-100');
            $table->decimal('min_salary', 12, 2)->default(0);
            $table->decimal('max_salary', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // ── Ledger / Journal Entries ──────────────────────────────────────────
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->date('entry_date');
            $table->string('type', 50);
            $table->string('account', 150);
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->decimal('balance', 15, 2)->default(0);
            $table->nullableMorphs('reference');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index('entry_date');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
        Schema::dropIfExists('tax_rates');
        Schema::dropIfExists('salary_slips');
    }
}
