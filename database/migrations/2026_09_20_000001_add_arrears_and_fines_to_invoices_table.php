<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('invoices', 'previous_due')) {
                $table->decimal('previous_due', 12, 2)->default(0)->after('amount')
                    ->comment('Unpaid balance carried forward from previous months (arrears/बक्यौता)');
            }
            if (! Schema::hasColumn('invoices', 'fine_amount')) {
                $table->decimal('fine_amount', 12, 2)->default(0)->after('previous_due')
                    ->comment('Late payment penalty/fine accrued');
            }
            if (! Schema::hasColumn('invoices', 'paid_amount')) {
                $table->decimal('paid_amount', 12, 2)->default(0)->after('fine_amount')
                    ->comment('Cached total payments made toward this invoice');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'paid_amount')) {
                $table->dropColumn('paid_amount');
            }
            if (Schema::hasColumn('invoices', 'fine_amount')) {
                $table->dropColumn('fine_amount');
            }
            if (Schema::hasColumn('invoices', 'previous_due')) {
                $table->dropColumn('previous_due');
            }
        });
    }
};
