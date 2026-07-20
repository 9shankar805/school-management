<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockTransactionsTable extends Migration
{
    public function up()
    {
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inventory_item_id');
            $table->unsignedBigInteger('purchase_order_id')->nullable();
            $table->unsignedBigInteger('processed_by');
            $table->enum('type', ['in','out','adjustment','return'])->default('in');
            $table->integer('quantity');         // positive = in, negative = out
            $table->integer('quantity_before');
            $table->integer('quantity_after');
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->string('reference')->nullable();  // PO number, dept name, etc.
            $table->text('notes')->nullable();
            $table->timestamp('transacted_at')->useCurrent();
            $table->timestamps();

            $table->foreign('inventory_item_id')->references('id')->on('inventory_items')->onDelete('cascade');
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->onDelete('set null');
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('cascade');
            $table->index(['inventory_item_id','type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_transactions');
    }
}
