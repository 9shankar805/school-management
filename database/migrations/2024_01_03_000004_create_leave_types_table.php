<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');             // e.g. "Annual Leave"
            $table->string('code')->nullable(); // e.g. "AL"
            $table->integer('days_allowed')->default(0); // per year
            $table->boolean('is_paid')->default(true);
            $table->boolean('carry_forward')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('leave_type_id');
            $table->integer('year');
            $table->integer('total_days')->default(0);
            $table->integer('used_days')->default(0);
            $table->integer('remaining_days')->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'leave_type_id', 'year']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('leave_type_id')->references('id')->on('leave_types')->cascadeOnDelete();
        });
    }
    public function down(): void {
        Schema::dropIfExists('leave_balances');
        Schema::dropIfExists('leave_types');
    }
};
