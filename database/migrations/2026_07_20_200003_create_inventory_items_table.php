<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryItemsTable extends Migration
{
    public function up()
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku', 100)->unique()->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('unit', 30)->default('piece'); // piece, kg, litre, box, ream…
            $table->integer('quantity_in_stock')->default(0);
            $table->integer('reorder_level')->default(5);   // alert when <= this
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->string('location')->nullable();          // store / shelf
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('category_id')->references('id')->on('asset_categories')->onDelete('set null');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');
            $table->index('is_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_items');
    }
}
