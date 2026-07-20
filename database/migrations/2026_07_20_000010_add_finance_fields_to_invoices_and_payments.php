<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFinanceFieldsToInvoicesAndPayments extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('invoice_number')->unique()->nullable()->after('id');
            $table->foreignId('fee_structure_id')->nullable()->after('student_id')
                  ->constrained('fee_structures')->onDelete('set null');
            $table->foreignId('session_id')->nullable()->after('fee_structure_id')
                  ->constrained('school_sessions')->onDelete('set null');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('amount');
            $table->decimal('tax_amount', 10, 2)->default(0)->after('discount_amount');
            $table->decimal('net_amount', 10, 2)->default(0)->after('tax_amount'); // amount - discount + tax
            $table->string('payment_method')->nullable()->after('status');         // cash, bank_transfer, cheque, online
            $table->string('reference_no')->nullable()->after('payment_method');
            $table->foreignId('created_by')->nullable()->after('description')
                  ->constrained('users')->onDelete('set null');
            $table->softDeletes();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('receipt_number')->unique()->nullable()->after('id');
            $table->string('transaction_reference')->nullable()->after('payment_method');
            $table->string('bank_name')->nullable()->after('transaction_reference');
            $table->string('cheque_number')->nullable()->after('bank_name');
            $table->text('notes')->nullable()->after('cheque_number');
            $table->foreignId('received_by')->nullable()->after('notes')
                  ->constrained('users')->onDelete('set null');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'invoice_number','fee_structure_id','session_id',
                'discount_amount','tax_amount','net_amount',
                'payment_method','reference_no','created_by','deleted_at',
            ]);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'receipt_number','transaction_reference','bank_name',
                'cheque_number','notes','received_by','deleted_at',
            ]);
        });
    }
}
