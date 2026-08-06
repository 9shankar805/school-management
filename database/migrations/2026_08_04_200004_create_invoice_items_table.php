<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 1D — invoice_items table.
 *
 * An invoice can have multiple line items (one per fee type).
 * Example:
 *   Invoice #INV-2026-000001 for Student A (August)
 *     Line 1: Tuition Fee   Rs. 2,000
 *     Line 2: Transport Fee Rs. 1,000
 *     Line 3: Exam Fee      Rs.   500
 *   Total: Rs. 3,500
 *
 * The parent invoice.amount = sum of all line items (after discount + tax).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('fee_type_id')->nullable();
            $table->unsignedBigInteger('fee_structure_id')->nullable();

            $table->string('description');              // "Tuition Fee — August 2026"
            $table->decimal('unit_price', 12, 2);       // Base amount before discount/tax
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_rate', 6, 2)->default(0);    // % (e.g. 13.00 for VAT)
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2);            // (unit_price * qty - discount) + tax

            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('invoices')->cascadeOnDelete();
            $table->foreign('fee_type_id')->references('id')->on('fee_types')->nullOnDelete();
            $table->foreign('fee_structure_id')->references('id')->on('fee_structures')->nullOnDelete();
            $table->index('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
