<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The payments table was migrated to use 'amount' (canonical name),
 * but ~30 places in controllers/views still reference 'amount_paid'.
 *
 * Adding a VIRTUAL generated column 'amount_paid' that mirrors 'amount'
 * makes all existing code work with zero further changes.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('payments', 'amount_paid')) {
            return; // already exists — nothing to do
        }

        DB::statement(
            'ALTER TABLE `payments` ADD COLUMN `amount_paid` DECIMAL(12,2) GENERATED ALWAYS AS (`amount`) VIRTUAL'
        );
    }

    public function down(): void
    {
        if (Schema::hasColumn('payments', 'amount_paid')) {
            DB::statement('ALTER TABLE `payments` DROP COLUMN `amount_paid`');
        }
    }
};
