<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetsTable extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code')->unique();         // e.g. AST-2026-001
            $table->string('name');
            $table->string('category');                     // Furniture, Electronics, Vehicle, Lab Equipment, etc.
            $table->text('description')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->decimal('current_value', 12, 2)->nullable();
            $table->enum('condition', ['new', 'good', 'fair', 'poor', 'damaged', 'disposed'])->default('new');
            $table->enum('status', ['available', 'in_use', 'under_maintenance', 'disposed'])->default('available');
            $table->string('location')->nullable();         // Room / block / building
            $table->string('assigned_to')->nullable();      // Department / person
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
            $table->string('image_path')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category', 'status']);
            $table->index('warehouse_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
}
