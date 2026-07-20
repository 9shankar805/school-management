<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountingLedgerTable extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_ledger', function (Blueprint $table) {
            $table->id();
            $table->date('transaction_date');
            $table->string('description');
            $table->enum('type', ['debit', 'credit']);          // debit = expense/outflow, credit = income/inflow
            $table->decimal('amount', 12, 2);
            $table->decimal('balance', 12, 2)->default(0);      // running balance (updated on insert)
            $table->string('reference_type')->nullable();       // Payment, Expense, IncomeEntry, etc. (morphable label)
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('category')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['transaction_date', 'type']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_ledger');
    }
}
