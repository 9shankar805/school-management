<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * payment_proofs — stores parent-submitted QR payment evidence.
 *
 * Flow:
 *   1. Parent scans school QR, pays on eSewa/Khalti/bank.
 *   2. Parent opens their invoice, uploads screenshot + transaction ID.
 *   3. Admin sees pending proofs in Finance → Pending Approvals.
 *   4. Admin approves → invoice auto-marked paid.
 *      Admin rejects → parent notified with reason.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_proofs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('student_id');     // the student whose fee is being paid
            $table->unsignedBigInteger('submitted_by');   // parent or student who submitted
            $table->unsignedBigInteger('reviewed_by')->nullable();

            $table->string('gateway', 50);               // esewa | khalti | bank | fonepay | other
            $table->string('transaction_id', 200)->nullable(); // eSewa ref, Khalti idx, etc.
            $table->decimal('amount', 12, 2);
            $table->string('screenshot', 500)->nullable();    // uploaded image path

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();

            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('invoices')->cascadeOnDelete();
            $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('submitted_by')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['status', 'school_id']);
            $table->index('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_proofs');
    }
};
