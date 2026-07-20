<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeeDiscountsTable extends Migration
{
    public function up(): void
    {
        Schema::create('fee_discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');                                               // "Sibling Discount", "Merit Scholarship", etc.
            $table->enum('type', ['percentage', 'fixed']);
            $table->decimal('value', 10, 2);                                      // % or flat amount
            $table->foreignId('fee_category_id')->nullable()->constrained('fee_categories')->onDelete('set null');
            // When set, discount applies only to that category; null = applies to total
            $table->foreignId('student_id')->nullable()->constrained('users')->onDelete('cascade');
            // When null = global / template discount; when set = student-specific
            $table->foreignId('fee_structure_id')->nullable()->constrained('fee_structures')->onDelete('cascade');
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['student_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_discounts');
    }
}
