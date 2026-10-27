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
        if (!Schema::hasTable('school_bank_accounts')) {
            Schema::create('school_bank_accounts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('school_id')->nullable();
                $table->string('bank_name'); // e.g., Nabil Bank Limited, Global IME Bank, NIMB
                $table->string('bank_code', 30)->nullable(); // e.g., NABIL, GLOBAL, NIMB, NICASIA, RBB
                $table->string('account_name'); // e.g., Shree Shanti Secondary School
                $table->string('account_number', 50);
                $table->string('branch_name')->nullable(); // e.g., New Road Branch
                $table->string('account_type', 30)->default('Current'); // Current, Savings
                $table->string('fonepay_merchant_id', 50)->nullable();
                $table->string('fonepay_pan', 30)->nullable();
                $table->text('qr_payload')->nullable();
                $table->string('qr_image', 500)->nullable();
                $table->boolean('is_default')->default(false);
                $table->boolean('is_active')->default(true);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('school_id')->references('id')->on('schools')->nullOnDelete();
            });
        }

        if (Schema::hasTable('payments') && !Schema::hasColumn('payments', 'bank_account_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->unsignedBigInteger('bank_account_id')->nullable()->after('payment_method');
                $table->foreign('bank_account_id')->references('id')->on('school_bank_accounts')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'bank_account_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropForeign(['bank_account_id']);
                $table->dropColumn('bank_account_id');
            });
        }

        Schema::dropIfExists('school_bank_accounts');
    }
};
