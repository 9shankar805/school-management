<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInstallmentPlansTable extends Migration
{
    public function up(): void
    {
        Schema::create('installment_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');                                               // "Monthly 4-Part Plan"
            $table->foreignId('fee_structure_id')->nullable()->constrained('fee_structures')->onDelete('set null');
            $table->foreignId('student_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('cascade');
            $table->integer('num_installments');
            $table->decimal('total_amount', 12, 2);
            $table->decimal('late_fee', 10, 2)->default(0);                       // per overdue installment
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installment_plans');
    }
}
