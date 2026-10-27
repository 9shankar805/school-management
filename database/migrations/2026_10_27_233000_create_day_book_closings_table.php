<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('day_book_closings')) {
            Schema::create('day_book_closings', function (Blueprint $table) {
                $table->id();
                $table->date('date')->unique();
                $table->decimal('opening_balance', 14, 2)->default(0);
                $table->decimal('cash_inflow', 14, 2)->default(0);
                $table->decimal('online_inflow', 14, 2)->default(0);
                $table->decimal('total_inflow', 14, 2)->default(0);
                $table->decimal('cash_outflow', 14, 2)->default(0);
                $table->decimal('online_outflow', 14, 2)->default(0);
                $table->decimal('total_outflow', 14, 2)->default(0);
                $table->decimal('expected_cash', 14, 2)->default(0);
                $table->decimal('actual_cash', 14, 2)->default(0);
                $table->decimal('difference', 14, 2)->default(0);
                $table->decimal('closing_balance', 14, 2)->default(0);
                $table->json('denominations')->nullable();
                $table->text('notes')->nullable();
                $table->string('status', 20)->default('locked'); // 'locked' or 'unlocked'
                $table->unsignedBigInteger('closed_by')->nullable();
                $table->timestamp('closed_at')->nullable();
                $table->unsignedBigInteger('unlocked_by')->nullable();
                $table->timestamp('unlocked_at')->nullable();
                $table->timestamps();

                $table->foreign('closed_by')->references('id')->on('users')->nullOnDelete();
                $table->foreign('unlocked_by')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('day_book_closings');
    }
};
