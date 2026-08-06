<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIncomeRecordsTable extends Migration
{
    public function up()
    {
        Schema::create('income_records', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category', 100);  // Fees, Donations, Grants, Events, etc.
            $table->decimal('amount', 12, 2);
            $table->date('income_date');
            $table->string('payment_method', 50)->default('cash');
            $table->string('reference')->nullable();
            $table->unsignedBigInteger('recorded_by');
            $table->text('notes')->nullable();
            $table->string('attachment')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('recorded_by')->references('id')->on('users')->onDelete('cascade');
            $table->index(['income_date', 'category']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('income_records');
    }
}
