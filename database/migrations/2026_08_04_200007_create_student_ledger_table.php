<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 3A — Per-student financial ledger.
 *
 * student_ledger is an APPEND-ONLY ledger (never edit/delete a row).
 * Every financial event for a student creates one row:
 *
 * DateTypeDescriptionDebitCreditBalance
 * 2026-08-01  invoice  August Tuition      2,000      0     2,000
 * 2026-08-01  invoice  August Transport    1,000      0     3,000
 * 2026-08-05  payment  Cash Receipt            0  2,500       500
 * 2026-08-15  invoice  First Terminal Exam   500      0     1,000
 * 2026-08-20  payment  Cash Receipt            0  1,000         0
 *
 * Debit  = money owed by student (invoice generated)
 * Credit = money received from student (payment)
 * Balance = running_balance = previous_balance + debit - credit
 *
 * This makes:
 *  - Due reports: WHERE balance > 0
 *  - Payment history: all rows for a student
 *  - Receipt regeneration: credit rows
 *  - Audit: never changes
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_ledger', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('school_id')->nullable();
            $table->unsignedBigInteger('session_id')->nullable();

            $table->date('entry_date');
            $table->enum('type', [
                'invoice',     // Fee charged (debit)
                'payment',     // Payment received (credit)
                'discount',    // Discount applied (credit)
                'refund',      // Refund issued (credit)
                'fine',        // Late fine / penalty (debit)
                'opening',     // Opening balance entry
                'adjustment',  // Manual adjustment (needs notes)
            ])->default('invoice');

            $table->string('description');       // "August Tuition Fee", "Cash Receipt #RCP-2026-001"
            $table->decimal('debit', 12, 2)->default(0)
                  ->comment('Amount added to what student owes');
            $table->decimal('credit', 12, 2)->default(0)
                  ->comment('Amount received from student');
            $table->decimal('balance', 12, 2)->default(0)
                  ->comment('Running balance (cumulative). Positive = student owes money.');

            // Polymorphic reference (Invoice, Payment, etc.)
            $table->nullableMorphs('reference');

            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();
            // No soft deletes — this is immutable append-only

            $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('session_id')->references('id')->on('school_sessions')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['student_id', 'entry_date']);
            $table->index(['student_id', 'session_id']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_ledger');
    }
};
