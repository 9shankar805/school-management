<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 1C — Fix invoices + payments schema.
 *
 * invoices: add invoice_number, session_id, class_id, section_id, fee_type_id,
 *           total_amount, discount_amount, tax_amount, billing_month/year, is_auto_generated
 *
 * payments: rename amount_paid → amount (via raw SQL — no doctrine/dbal needed),
 *           add receipt_number, gateway, gateway_transaction_id, notes, recorded_by, verified_by
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── payments: rename amount_paid → amount via raw SQL ─────────────────
        // Check the actual column name first (may already be 'amount' if partially run)
        $paymentCols = Schema::getColumnListing('payments');

        if (in_array('amount_paid', $paymentCols) && ! in_array('amount', $paymentCols)) {
            // MySQL ALTER TABLE CHANGE renames + redefines in one step — no doctrine needed
            DB::statement('ALTER TABLE `payments` CHANGE `amount_paid` `amount` DECIMAL(10,2) NOT NULL DEFAULT 0');
        }

        // ── invoices: add new columns ─────────────────────────────────────────
        Schema::table('invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('invoices', 'invoice_number')) {
                $table->string('invoice_number', 30)->nullable()->after('id');
                // Add unique index separately (can't inline when guarding)
            }
            if (! Schema::hasColumn('invoices', 'session_id')) {
                $table->unsignedBigInteger('session_id')->nullable()->after('student_id');
            }
            if (! Schema::hasColumn('invoices', 'class_id')) {
                $table->unsignedBigInteger('class_id')->nullable()->after('session_id');
            }
            if (! Schema::hasColumn('invoices', 'section_id')) {
                $table->unsignedBigInteger('section_id')->nullable()->after('class_id');
            }
            if (! Schema::hasColumn('invoices', 'fee_type_id')) {
                $table->unsignedBigInteger('fee_type_id')->nullable()->after('section_id');
            }
            if (! Schema::hasColumn('invoices', 'total_amount')) {
                $table->decimal('total_amount', 12, 2)->default(0)->after('amount');
            }
            if (! Schema::hasColumn('invoices', 'discount_amount')) {
                $table->decimal('discount_amount', 12, 2)->default(0)->after('total_amount');
            }
            if (! Schema::hasColumn('invoices', 'tax_amount')) {
                $table->decimal('tax_amount', 12, 2)->default(0)->after('discount_amount');
            }
            if (! Schema::hasColumn('invoices', 'billing_month')) {
                $table->unsignedTinyInteger('billing_month')->nullable()->after('tax_amount');
            }
            if (! Schema::hasColumn('invoices', 'billing_year')) {
                $table->unsignedSmallInteger('billing_year')->nullable()->after('billing_month');
            }
            if (! Schema::hasColumn('invoices', 'is_auto_generated')) {
                $table->boolean('is_auto_generated')->default(false)->after('billing_year');
            }
        });

        // Add unique index on invoice_number (separate call to avoid Blueprint issues)
        try {
            DB::statement('ALTER TABLE `invoices` ADD UNIQUE INDEX `invoices_invoice_number_unique` (`invoice_number`)');
        } catch (\Throwable $e) {
            // Index may already exist — ignore
        }

        // Add FKs if not already present
        try {
            DB::statement('ALTER TABLE `invoices` ADD CONSTRAINT `invoices_session_id_foreign` FOREIGN KEY (`session_id`) REFERENCES `school_sessions` (`id`) ON DELETE SET NULL');
        } catch (\Throwable) {}
        try {
            DB::statement('ALTER TABLE `invoices` ADD CONSTRAINT `invoices_fee_type_id_foreign` FOREIGN KEY (`fee_type_id`) REFERENCES `fee_types` (`id`) ON DELETE SET NULL');
        } catch (\Throwable) {}

        // Add composite indexes
        try {
            DB::statement('ALTER TABLE `invoices` ADD INDEX `invoices_student_status_index` (`student_id`, `status`)');
        } catch (\Throwable) {}
        try {
            DB::statement('ALTER TABLE `invoices` ADD INDEX `invoices_billing_index` (`billing_month`, `billing_year`)');
        } catch (\Throwable) {}

        // ── payments: add new columns ─────────────────────────────────────────
        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'receipt_number')) {
                $table->string('receipt_number', 30)->nullable()->after('id');
            }
            if (! Schema::hasColumn('payments', 'gateway')) {
                $table->string('gateway', 30)->default('cash')->after('payment_method');
            }
            if (! Schema::hasColumn('payments', 'gateway_transaction_id')) {
                $table->string('gateway_transaction_id')->nullable()->after('gateway');
            }
            if (! Schema::hasColumn('payments', 'notes')) {
                $table->text('notes')->nullable()->after('gateway_transaction_id');
            }
            if (! Schema::hasColumn('payments', 'recorded_by')) {
                $table->unsignedBigInteger('recorded_by')->nullable()->after('notes');
            }
            if (! Schema::hasColumn('payments', 'verified_by')) {
                $table->unsignedBigInteger('verified_by')->nullable()->after('recorded_by');
            }
        });

        // Add unique + FK + index for payments
        try {
            DB::statement('ALTER TABLE `payments` ADD UNIQUE INDEX `payments_receipt_number_unique` (`receipt_number`)');
        } catch (\Throwable) {}
        try {
            DB::statement('ALTER TABLE `payments` ADD CONSTRAINT `payments_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL');
        } catch (\Throwable) {}
        try {
            DB::statement('ALTER TABLE `payments` ADD CONSTRAINT `payments_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL');
        } catch (\Throwable) {}
        try {
            DB::statement('ALTER TABLE `payments` ADD INDEX `payments_invoice_date_index` (`invoice_id`, `payment_date`)');
        } catch (\Throwable) {}
    }

    public function down(): void
    {
        // Reverse payments: rename amount → amount_paid
        $paymentCols = Schema::getColumnListing('payments');
        if (in_array('amount', $paymentCols) && ! in_array('amount_paid', $paymentCols)) {
            DB::statement('ALTER TABLE `payments` CHANGE `amount` `amount_paid` DECIMAL(10,2) NOT NULL DEFAULT 0');
        }

        Schema::table('payments', function (Blueprint $table) {
            foreach (['receipt_number','gateway','gateway_transaction_id','notes','recorded_by','verified_by'] as $col) {
                if (Schema::hasColumn('payments', $col)) $table->dropColumn($col);
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            foreach (['invoice_number','session_id','class_id','section_id','fee_type_id',
                      'total_amount','discount_amount','tax_amount',
                      'billing_month','billing_year','is_auto_generated'] as $col) {
                if (Schema::hasColumn('invoices', $col)) $table->dropColumn($col);
            }
        });
    }
};
