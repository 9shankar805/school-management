<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('teacher_payrolls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id');
            $table->integer('month');       // 1-12
            $table->integer('year');
            $table->decimal('basic_salary', 10, 2)->default(0);
            $table->decimal('allowances', 10, 2)->default(0);
            $table->decimal('overtime', 10, 2)->default(0);
            $table->decimal('gross_salary', 10, 2)->default(0);
            $table->decimal('tax_deduction', 10, 2)->default(0);
            $table->decimal('other_deductions', 10, 2)->default(0);
            $table->decimal('net_salary', 10, 2)->default(0);
            $table->integer('working_days')->default(0);
            $table->integer('present_days')->default(0);
            $table->integer('absent_days')->default(0);
            $table->integer('leave_days')->default(0);
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'paid', 'cancelled'])->default('draft');
            $table->date('payment_date')->nullable();
            $table->unsignedBigInteger('processed_by');
            $table->timestamps();
            $table->unique(['teacher_id', 'month', 'year']);
            $table->index(['month', 'year']);
            $table->foreign('teacher_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('processed_by')->references('id')->on('users');
        });
    }
    public function down(): void { Schema::dropIfExists('teacher_payrolls'); }
};
