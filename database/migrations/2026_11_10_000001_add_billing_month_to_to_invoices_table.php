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
            if (! Schema::hasColumn('invoices', 'billing_month_to')) {
                $table->unsignedTinyInteger('billing_month_to')->nullable()->after('billing_month')
                    ->comment('End month of multi-month advance billing range (e.g. 3=Ashadh or 12=Chaitra)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'billing_month_to')) {
                $table->dropColumn('billing_month_to');
            }
        });
    }
};

