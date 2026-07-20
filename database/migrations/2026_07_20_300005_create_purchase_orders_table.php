<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseOrdersTable extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();           // e.g. PO-2026-0001
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
            $table->date('order_date');
            $table->date('expected_delivery')->nullable();
            $table->date('delivered_date')->nullable();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->enum('status', ['draft', 'submitted', 'approved', 'received', 'cancelled'])->default('draft');
            $table->string('payment_method')->nullable();    // cash, bank_transfer, cheque, credit
            $table->string('reference_no')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'order_date']);
            $table->index('supplier_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
}
