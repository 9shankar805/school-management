<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('teacher_contracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id');
            $table->enum('contract_type', ['permanent', 'temporary', 'part_time', 'visiting', 'probation'])->default('permanent');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('basic_salary', 10, 2)->default(0);
            $table->string('position')->nullable();    // e.g. "Senior Teacher"
            $table->text('terms')->nullable();
            $table->string('attachment_path')->nullable();
            $table->enum('status', ['active', 'expired', 'terminated', 'renewed'])->default('active');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();
            $table->index('teacher_id');
            $table->foreign('teacher_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users');
        });
    }
    public function down(): void { Schema::dropIfExists('teacher_contracts'); }
};
