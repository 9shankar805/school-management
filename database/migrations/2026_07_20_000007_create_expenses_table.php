<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpensesTable extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category');                          // Salaries, Utilities, Maintenance, Supplies, Transport, Other
            $table->decimal('amount', 12, 2);
            $table->date('expense_date');
            $table->string('payment_method')->default('cash'); // cash, bank_transfer, cheque, card
            $table->string('reference_no')->nullable();        // cheque/transaction ref
            $table->string('vendor')->nullable();
            $table->text('description')->nullable();
            $table->string('receipt_path')->nullable();        // uploaded receipt file
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('approved');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['expense_date', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
}
