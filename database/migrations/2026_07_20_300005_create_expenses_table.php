<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpensesTable extends Migration
{
    public function up()
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category', 100);   // Utilities, Salaries, Maintenance, etc.
            $table->decimal('amount', 12, 2);
            $table->date('expense_date');
            $table->string('payment_method', 50)->default('cash'); // cash|bank|cheque
            $table->string('reference')->nullable();               // cheque/txn number
            $table->unsignedBigInteger('recorded_by');
            $table->text('notes')->nullable();
            $table->string('attachment')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('recorded_by')->references('id')->on('users')->onDelete('cascade');
            $table->index(['expense_date', 'category']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('expenses');
    }
}
