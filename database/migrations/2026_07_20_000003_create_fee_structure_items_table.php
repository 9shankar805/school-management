<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeeStructureItemsTable extends Migration
{
    public function up(): void
    {
        Schema::create('fee_structure_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_structure_id')->constrained('fee_structures')->onDelete('cascade');
            $table->foreignId('fee_category_id')->constrained('fee_categories')->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->boolean('is_mandatory')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('fee_structure_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_structure_items');
    }
}
