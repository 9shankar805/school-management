<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryItemsTable extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->unique();           // e.g. ITM-001
            $table->string('name');
            $table->string('category');                      // Stationery, Cleaning, Lab, Sports, Medical, etc.
            $table->string('unit');                          // pcs, kg, litre, box, ream, etc.
            $table->integer('quantity_in_stock')->default(0);
            $table->integer('reorder_level')->default(0);    // triggers low-stock alert
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->text('description')->nullable();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category', 'status']);
            $table->index('warehouse_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
}
