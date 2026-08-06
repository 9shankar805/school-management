<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Models\LedgerEntry;
use App\Models\StudentLedger;

/**
 * InvoiceObserver
 *
 * On every new invoice:
 *  1. Posts a DEBIT to the student's personal ledger (money owed)
 *  2. Posts a CREDIT to the school's double-entry ledger
 *     (Dr: Student Receivable  /  Cr: Fee Income)
 *
 * Uses the append-only pattern — never edits existing rows.
 */
class InvoiceObserver
{
    public function created(Invoice $invoice): void
    {
        // 1. Student ledger — debit (student owes money)
        StudentLedger::debit(
            studentId:   $invoice->student_id,
            amount:      (float) $invoice->amount,
            description: $invoice->title . ' (#' . $invoice->invoice_number . ')',
            type:        'invoice',
            sessionId:   $invoice->session_id,
            reference:   $invoice,
            date:        $invoice->created_at?->toDateString() ?? today()->toDateString(),
        );

        // 2. School accounting ledger — fee income credit
        LedgerEntry::create([
            'entry_date'     => $invoice->created_at?->toDateString() ?? today()->toDateString(),
            'type'           => 'fee_invoice',
            'account'        => 'Student Receivable',
            'debit'          => $invoice->amount,   // Dr: Student Receivable (asset increases)
            'credit'         => 0,
            'balance'        => 0,                  // LedgerController::sync() computes running balance
            'reference_type' => Invoice::class,
            'reference_id'   => $invoice->id,
            'description'    => 'Invoice ' . $invoice->invoice_number . ' — ' . $invoice->title,
            'created_by'     => auth()->id(),
        ]);

        LedgerEntry::create([
            'entry_date'     => $invoice->created_at?->toDateString() ?? today()->toDateString(),
            'type'           => 'fee_invoice',
            'account'        => 'Fee Income',
            'debit'          => 0,
            'credit'         => $invoice->amount,   // Cr: Fee Income (revenue)
            'balance'        => 0,
            'reference_type' => Invoice::class,
            'reference_id'   => $invoice->id,
            'description'    => 'Invoice ' . $invoice->invoice_number . ' — ' . $invoice->title,
            'created_by'     => auth()->id(),
        ]);
    }
}
