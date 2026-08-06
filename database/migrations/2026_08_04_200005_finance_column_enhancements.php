<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 1E — Finance-wide column enhancements.
 * Every column addition is guarded with hasColumn() so this is safe to re-run.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── fee_structures ────────────────────────────────────────────────────
        Schema::table('fee_structures', function (Blueprint $table) {
            if (! Schema::hasColumn('fee_structures', 'fee_type_id')) {
                $table->unsignedBigInteger('fee_type_id')->nullable()->after('category_id');
            }
            if (! Schema::hasColumn('fee_structures', 'is_taxable')) {
                $table->boolean('is_taxable')->default(false)->after('is_active');
            }
            if (! Schema::hasColumn('fee_structures', 'tax_rate')) {
                $table->decimal('tax_rate', 6, 2)->default(0)->after('is_taxable');
            }
            if (! Schema::hasColumn('fee_structures', 'applies_to')) {
                $table->enum('applies_to', ['all', 'day_scholar', 'hostel', 'transport'])
                      ->default('all')->after('tax_rate');
            }
        });

        // FK for fee_type_id (add raw to avoid Blueprint FK duplicate errors)
        try {
            DB::statement('ALTER TABLE `fee_structures` ADD CONSTRAINT `fee_structures_fee_type_id_foreign` FOREIGN KEY (`fee_type_id`) REFERENCES `fee_types` (`id`) ON DELETE SET NULL');
        } catch (\Throwable) {}

        // ── expenses ──────────────────────────────────────────────────────────
        Schema::table('expenses', function (Blueprint $table) {
            if (! Schema::hasColumn('expenses', 'vendor')) {
                $table->string('vendor', 150)->nullable()->after('title');
            }
            if (! Schema::hasColumn('expenses', 'vendor_invoice_no')) {
                $table->string('vendor_invoice_no', 80)->nullable()->after('vendor');
            }
            if (! Schema::hasColumn('expenses', 'amount_before_tax')) {
                $table->decimal('amount_before_tax', 12, 2)->nullable()->after('amount');
            }
            if (! Schema::hasColumn('expenses', 'expense_tax_rate')) {
                $table->decimal('expense_tax_rate', 6, 2)->default(0)->after('amount_before_tax');
            }
            if (! Schema::hasColumn('expenses', 'expense_tax_amount')) {
                $table->decimal('expense_tax_amount', 12, 2)->default(0)->after('expense_tax_rate');
            }
            if (! Schema::hasColumn('expenses', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('recorded_by');
            }
        });

        try {
            DB::statement('ALTER TABLE `expenses` ADD CONSTRAINT `expenses_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL');
        } catch (\Throwable) {}

        // ── tax_rates ─────────────────────────────────────────────────────────
        Schema::table('tax_rates', function (Blueprint $table) {
            if (! Schema::hasColumn('tax_rates', 'code')) {
                $table->string('code', 30)->nullable()->after('name');
            }
            if (! Schema::hasColumn('tax_rates', 'effective_from')) {
                $table->date('effective_from')->nullable()->after('description');
            }
            if (! Schema::hasColumn('tax_rates', 'effective_to')) {
                $table->date('effective_to')->nullable()->after('effective_from');
            }
            if (! Schema::hasColumn('tax_rates', 'applies_to')) {
                $table->enum('applies_to', ['teacher', 'staff', 'all'])->default('all')->after('effective_to');
            }
            // school_id already added by 2026_07_21_700000_add_school_id_to_all_tables — skip
        });

        // Unique index on code (tax_rates)
        try {
            DB::statement('ALTER TABLE `tax_rates` ADD UNIQUE INDEX `tax_rates_code_unique` (`code`)');
        } catch (\Throwable) {}

        // Composite index on school_id + is_active
        try {
            DB::statement('ALTER TABLE `tax_rates` ADD INDEX `tax_rates_school_active_index` (`school_id`, `is_active`)');
        } catch (\Throwable) {}

        // ── salary_slips ──────────────────────────────────────────────────────
        Schema::table('salary_slips', function (Blueprint $table) {
            foreach ([
                'cit_deduction'      => ['decimal', 12, 2],
                'ssf_deduction'      => ['decimal', 12, 2],
                'late_fine'          => ['decimal', 12, 2],
                'loan_deduction'     => ['decimal', 12, 2],
                'advance_deduction'  => ['decimal', 12, 2],
                'insurance_deduction'=> ['decimal', 12, 2],
                'tds_payable'        => ['decimal', 12, 2],
            ] as $col => $def) {
                if (! Schema::hasColumn('salary_slips', $col)) {
                    $table->decimal($col, $def[1], $def[2])->default(0)->after('provident_fund');
                }
            }
        });

        // ── teacher_payrolls ──────────────────────────────────────────────────
        Schema::table('teacher_payrolls', function (Blueprint $table) {
            foreach ([
                'cit_deduction'      => 'decimal',
                'ssf_deduction'      => 'decimal',
                'late_fine'          => 'decimal',
                'loan_deduction'     => 'decimal',
                'advance_deduction'  => 'decimal',
                'insurance_deduction'=> 'decimal',
                'tds_payable'        => 'decimal',
            ] as $col => $type) {
                if (! Schema::hasColumn('teacher_payrolls', $col)) {
                    $table->decimal($col, 12, 2)->default(0)->after('other_deductions');
                }
            }
            if (! Schema::hasColumn('teacher_payrolls', 'is_approved')) {
                $table->boolean('is_approved')->default(false)->after('status');
            }
            if (! Schema::hasColumn('teacher_payrolls', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('is_approved');
            }
        });

        try {
            DB::statement('ALTER TABLE `teacher_payrolls` ADD CONSTRAINT `teacher_payrolls_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL');
        } catch (\Throwable) {}
    }

    public function down(): void
    {
        // teacher_payrolls
        try {
            DB::statement('ALTER TABLE `teacher_payrolls` DROP FOREIGN KEY `teacher_payrolls_approved_by_foreign`');
        } catch (\Throwable) {}
        Schema::table('teacher_payrolls', function (Blueprint $table) {
            foreach (['cit_deduction','ssf_deduction','late_fine','loan_deduction',
                      'advance_deduction','insurance_deduction','tds_payable','is_approved','approved_by'] as $col) {
                if (Schema::hasColumn('teacher_payrolls', $col)) $table->dropColumn($col);
            }
        });

        // salary_slips
        Schema::table('salary_slips', function (Blueprint $table) {
            foreach (['cit_deduction','ssf_deduction','late_fine','loan_deduction',
                      'advance_deduction','insurance_deduction','tds_payable'] as $col) {
                if (Schema::hasColumn('salary_slips', $col)) $table->dropColumn($col);
            }
        });

        // tax_rates
        Schema::table('tax_rates', function (Blueprint $table) {
            foreach (['code','effective_from','effective_to','applies_to'] as $col) {
                if (Schema::hasColumn('tax_rates', $col)) $table->dropColumn($col);
            }
        });

        // expenses
        try {
            DB::statement('ALTER TABLE `expenses` DROP FOREIGN KEY `expenses_approved_by_foreign`');
        } catch (\Throwable) {}
        Schema::table('expenses', function (Blueprint $table) {
            foreach (['vendor','vendor_invoice_no','amount_before_tax',
                      'expense_tax_rate','expense_tax_amount','approved_by'] as $col) {
                if (Schema::hasColumn('expenses', $col)) $table->dropColumn($col);
            }
        });

        // fee_structures
        try {
            DB::statement('ALTER TABLE `fee_structures` DROP FOREIGN KEY `fee_structures_fee_type_id_foreign`');
        } catch (\Throwable) {}
        Schema::table('fee_structures', function (Blueprint $table) {
            foreach (['fee_type_id','is_taxable','tax_rate','applies_to'] as $col) {
                if (Schema::hasColumn('fee_structures', $col)) $table->dropColumn($col);
            }
        });
    }
};
