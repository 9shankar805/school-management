<?php

namespace App\Observers;

use App\Models\FeeCategory;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\SchoolSession;
use App\Models\StudentLedger;
use App\Models\StudentTransport;
use Illuminate\Support\Facades\Log;

/**
 * StudentTransportObserver
 *
 * When a student is allocated to a transport route:
 *  - If the allocation has a monthly_fee > 0, auto-generate a Transport Fee invoice
 *    for the current month.
 *
 * When a student's transport is cancelled/suspended, no action (fees already billed
 * stand; prorated refunds must be done manually via adjustment entries).
 */
class StudentTransportObserver
{
    public function created(StudentTransport $transport): void
    {
        if ((float) $transport->monthly_fee <= 0) {
            return;
        }

        $this->generateTransportInvoice($transport);
    }

    private function generateTransportInvoice(StudentTransport $transport): void
    {
        try {
            $session = SchoolSession::current() ?? SchoolSession::latest('id')->first();

            $month = now()->month;
            $year  = now()->year;

            // Idempotency: only one transport invoice per student per month
            $exists = Invoice::where('student_id', $transport->student_id)
                ->where('session_id', $session?->id)
                ->where('billing_month', $month)
                ->where('billing_year', $year)
                ->where('title', 'like', '%Transport Fee%')
                ->exists();

            if ($exists) {
                return;
            }

            // Resolve or create "Transport" fee category
            $category = FeeCategory::firstOrCreate(
                ['name' => 'Transport Fee'],
                ['code' => 'TRNS', 'is_active' => true]
            );

            $amount = (float) $transport->monthly_fee;

            $invoice = Invoice::create([
                'student_id'       => $transport->student_id,
                'session_id'       => $session?->id,
                'title'            => 'Transport Fee — ' . now()->format('F Y'),
                'description'      => 'Route: ' . ($transport->route?->name ?? 'N/A')
                                    . ', Stop: ' . ($transport->stop?->name ?? 'N/A'),
                'total_amount'     => $amount,
                'discount_amount'  => 0,
                'tax_amount'       => 0,
                'amount'           => $amount,
                'status'           => 'unpaid',
                'due_date'         => now()->startOfMonth()->addDays(9)->toDateString(),
                'billing_month'    => $month,
                'billing_year'     => $year,
                'is_auto_generated'=> true,
            ]);

            InvoiceItem::create([
                'invoice_id'   => $invoice->id,
                'description'  => 'Monthly Transport Fee',
                'unit_price'   => $amount,
                'quantity'     => 1,
                'discount_amount' => 0,
                'tax_rate'     => 0,
                'tax_amount'   => 0,
                'total'        => $amount,
            ]);

            // Student ledger debit
            StudentLedger::debit(
                studentId:   $transport->student_id,
                amount:      $amount,
                description: $invoice->title . ' (#' . $invoice->invoice_number . ')',
                type:        'invoice',
                sessionId:   $session?->id,
                reference:   $invoice,
                date:        today()->toDateString(),
            );

            Log::info("StudentTransportObserver: transport invoice #{$invoice->id} generated for student #{$transport->student_id}");

        } catch (\Throwable $e) {
            Log::error("StudentTransportObserver: failed to generate transport invoice: " . $e->getMessage());
        }
    }
}
