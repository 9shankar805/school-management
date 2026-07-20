<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIncomeEntriesTable extends Migration
{
    public function up(): void
    {
        Schema::create('income_entries', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category');                          // Fees, Donations, Grants, Events, Canteen, Other
            $table->decimal('amount', 12, 2);
            $table->date('income_date');
            $table->string('payment_method')->default('cash');
            $table->string('reference_no')->nullable();
            $table->string('source')->nullable();               // donor name, grant body, etc.
            $table->text('description')->nullable();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['income_date', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('income_entries');
    }
}
