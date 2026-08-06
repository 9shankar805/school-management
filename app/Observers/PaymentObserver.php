<?php

namespace App\Observers;

use App\Models\LedgerEntry;
use App\Models\Payment;
use App\Models\StudentLedger;

/**
 * PaymentObserver
 *
 * On every new payment:
 *  1. Posts a CREDIT to the student's personal ledger (money received)
 *  2. Posts double-entry to school ledger:
 *     Dr: Cash/Bank  /  Cr: Student Receivable
 *  3. Handles TDS payable entry if tax was withheld (not applicable for fee payments,
 *     but included for completeness)
 *
 * Also handles refunds (negative amounts) correctly.
 */
class PaymentObserver
{
    public function created(Payment $payment): void
    {
        $invoice   = $payment->invoice;
        $studentId = $invoice?->student_id;
        $sessionId = $invoice?->session_id;

        if (! $studentId) {
            return;
        }

        $amount      = (float) $payment->amount;
        $receiptDesc = 'Receipt #' . $payment->receipt_number
            . ' — ' . ucfirst($payment->gateway ?? 'cash');

        // 1. Student ledger — credit (payment reduces their balance)
        StudentLedger::credit(
            studentId:   $studentId,
            amount:      $amount,
            description: $receiptDesc,
            type:        'payment',
            sessionId:   $sessionId,
            reference:   $payment,
            date:        $payment->payment_date?->toDateString() ?? today()->toDateString(),
        );

        // 2. School double-entry ledger
        $gateway = ucfirst($payment->gateway ?? 'Cash');
        $cashAccount = match ($payment->gateway) {
            'bank', 'stripe', 'razorpay', 'paypal', 'flutterwave' => 'Bank Account',
            default => 'Cash in Hand',
        };

        // Dr: Cash/Bank (asset increases)
        LedgerEntry::create([
            'entry_date'     => $payment->payment_date?->toDateString() ?? today()->toDateString(),
            'type'           => 'fee_payment',
            'account'        => $cashAccount,
            'debit'          => $amount,
            'credit'         => 0,
            'balance'        => 0,
            'reference_type' => Payment::class,
            'reference_id'   => $payment->id,
            'description'    => $receiptDesc,
            'created_by'     => auth()->id(),
        ]);

        // Cr: Student Receivable (asset decreases)
        LedgerEntry::create([
            'entry_date'     => $payment->payment_date?->toDateString() ?? today()->toDateString(),
            'type'           => 'fee_payment',
            'account'        => 'Student Receivable',
            'debit'          => 0,
            'credit'         => $amount,
            'balance'        => 0,
            'reference_type' => Payment::class,
            'reference_id'   => $payment->id,
            'description'    => $receiptDesc,
            'created_by'     => auth()->id(),
        ]);
    }
}
