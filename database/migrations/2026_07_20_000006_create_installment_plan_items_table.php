<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInstallmentPlanItemsTable extends Migration
{
    public function up(): void
    {
        Schema::create('installment_plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('installment_plan_id')->constrained('installment_plans')->onDelete('cascade');
            $table->integer('installment_no');
            $table->decimal('amount', 12, 2);
            $table->date('due_date');
            $table->enum('status', ['pending', 'paid', 'overdue', 'waived'])->default('pending');
            $table->decimal('late_fee_charged', 10, 2)->default(0);
            $table->date('paid_date')->nullable();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->onDelete('set null');
            $table->timestamps();

            $table->index(['installment_plan_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installment_plan_items');
    }
}
