<?php

namespace App\Http\Controllers;

use App\Models\FeeInstallment;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\StudentLedger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * FeeInstallmentController
 *
 * Breaks a large invoice into smaller scheduled instalments.
 *
 * Example: Annual fee Rs. 12,000 split into 4 instalments of Rs. 3,000
 * due on 1 Jan, 1 Apr, 1 Jul, 1 Oct.
 *
 * The original invoice stays as-is; instalments are child records that
 * track their own due dates, paid status and partial payments.
 */
class FeeInstallmentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'can:manage finance']);
    }

    /**
     * List all instalments for an invoice.
     */
    public function index(int $invoiceId)
    {
        $invoice      = Invoice::with('student', 'items.feeType', 'payments')->findOrFail($invoiceId);
        $installments = FeeInstallment::where('invoice_id', $invoiceId)
            ->orderBy('installment_number')
            ->get();

        $totalInstalled = $installments->sum('amount');
        $totalPaid      = $installments->where('status', 'paid')->sum('amount');

        return view('finance.installments.index', compact(
            'invoice', 'installments', 'totalInstalled', 'totalPaid'
        ));
    }

    /**
     * Create instalment plan for an invoice.
     * Validates that the sum of instalments equals the invoice amount.
     */
    public function store(Request $request, int $invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);

        // Remove existing draft instalments before recreating
        FeeInstallment::where('invoice_id', $invoiceId)
            ->where('status', 'pending')
            ->delete();

        $data = $request->validate([
            'installments'              => 'required|array|min:2|max:12',
            'installments.*.amount'     => 'required|numeric|min:1',
            'installments.*.due_date'   => 'required|date',
        ]);

        $total = collect($data['installments'])->sum('amount');

        // Allow a 1-rupee rounding tolerance
        if (abs($total - $invoice->amount) > 1) {
            return back()
                ->withErrors(['installments' => "Sum of instalments (Rs. {$total}) must equal invoice amount (Rs. {$invoice->amount})."])
                ->withInput();
        }

        DB::transaction(function () use ($invoice, $data) {
            foreach ($data['installments'] as $i => $row) {
                FeeInstallment::create([
                    'invoice_id'         => $invoice->id,
                    'installment_number' => $i + 1,
                    'amount'             => $row['amount'],
                    'due_date'           => $row['due_date'],
                    'status'             => 'pending',
                ]);
            }
        });

        return redirect()->route('finance.installments.index', $invoiceId)
            ->with('status', count($data['installments']) . ' instalments created.');
    }

    /**
     * Mark a single instalment as paid.
     * Posts a payment and updates the parent invoice status.
     */
    public function markPaid(Request $request, int $id)
    {
        $installment = FeeInstallment::with('invoice')->findOrFail($id);

        if ($installment->status === 'paid') {
            return back()->with('error', 'Instalment already marked as paid.');
        }

        $data = $request->validate([
            'payment_method'         => 'required|in:cash,bank_transfer,cheque,esewa,khalti,stripe,razorpay',
            'payment_date'           => 'required|date',
            'gateway_transaction_id' => 'nullable|string|max:100',
        ]);

        DB::transaction(function () use ($installment, $data) {
            $invoice = $installment->invoice;

            // Create payment record
            $payment = Payment::create([
                'invoice_id'             => $invoice->id,
                'amount'                 => $installment->amount,
                'payment_date'           => $data['payment_date'],
                'payment_method'         => $data['payment_method'],
                'gateway'                => $data['payment_method'],
                'gateway_transaction_id' => $data['gateway_transaction_id'] ?? null,
                'notes'                  => "Instalment #{$installment->installment_number}",
                'recorded_by'            => auth()->id(),
            ]);

            // Mark instalment paid
            $installment->update([
                'status'    => 'paid',
                'paid_date' => $data['payment_date'],
            ]);

            // Update parent invoice status
            $totalPaid  = $invoice->payments()->sum('amount');
            $newStatus  = $totalPaid >= $invoice->amount ? 'paid' : 'partial';
            $invoice->update(['status' => $newStatus]);
        });

        return back()->with('status', "Instalment #{$installment->installment_number} marked as paid.");
    }

    /**
     * Auto-generate equal instalments (AJAX helper).
     * Returns JSON split amounts and due dates.
     */
    public function autoSplit(Request $request, int $invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);
        $count   = (int) $request->input('count', 3);
        $start   = $request->input('start_date', today()->toDateString());

        $count   = max(2, min(12, $count));
        $base    = floor(($invoice->amount / $count) * 100) / 100;
        $last    = round($invoice->amount - ($base * ($count - 1)), 2);

        $result = [];
        for ($i = 0; $i < $count; $i++) {
            $result[] = [
                'installment_number' => $i + 1,
                'amount'             => $i === $count - 1 ? $last : $base,
                'due_date'           => Carbon::parse($start)->addMonths($i)->format('Y-m-d'),
            ];
        }

        return response()->json($result);
    }

    /**
     * Delete a pending instalment plan (only if none are paid yet).
     */
    public function destroy(int $invoiceId)
    {
        $hasPaid = FeeInstallment::where('invoice_id', $invoiceId)
            ->where('status', 'paid')
            ->exists();

        if ($hasPaid) {
            return back()->with('error', 'Cannot delete plan — some instalments are already paid.');
        }

        FeeInstallment::where('invoice_id', $invoiceId)->delete();

        return redirect()->route('payments.show', $invoiceId)
            ->with('status', 'Instalment plan deleted.');
    }
}
