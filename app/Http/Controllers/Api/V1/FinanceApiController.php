<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\InvoiceResource;
use App\Http\Resources\Api\V1\PaymentResource;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\StudentLedger;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Finance REST API — invoices, payments, student ledger.
 * Used by the mobile app and external integrations.
 */
class FinanceApiController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'can:manage finance']);
    }

    // ── Invoices ──────────────────────────────────────────────────────────────

    public function invoices(Request $request)
    {
        $query = Invoice::with('student', 'items.feeType', 'payments')
            ->latest();

        if ($studentId = $request->input('student_id')) {
            $query->where('student_id', $studentId);
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($from = $request->input('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $invoices = $query->paginate($request->input('per_page', 25));

        return InvoiceResource::collection($invoices);
    }

    public function invoice(int $id)
    {
        $invoice = Invoice::with('student', 'items.feeType', 'payments', 'installments')->findOrFail($id);
        return new InvoiceResource($invoice);
    }

    public function invoicePayments(int $id)
    {
        $invoice  = Invoice::findOrFail($id);
        $payments = Payment::with('recordedBy')
            ->where('invoice_id', $id)
            ->latest()
            ->get();

        return PaymentResource::collection($payments);
    }

    // ── Student Ledger ────────────────────────────────────────────────────────

    public function studentLedger(int $studentId)
    {
        $student = User::findOrFail($studentId);

        $ledger       = StudentLedger::where('student_id', $studentId)->orderBy('id')->get();
        $balance      = $ledger->last()?->balance ?? 0;
        $totalCharged = $ledger->sum('debit');
        $totalPaid    = $ledger->sum('credit');

        return response()->json([
            'student'       => ['id' => $student->id, 'name' => $student->full_name],
            'balance'       => (float) $balance,
            'total_charged' => (float) $totalCharged,
            'total_paid'    => (float) $totalPaid,
            'entries'       => $ledger->map(fn($e) => [
                'id'          => $e->id,
                'date'        => $e->entry_date?->format('Y-m-d'),
                'type'        => $e->type,
                'description' => $e->description,
                'debit'       => (float) $e->debit,
                'credit'      => (float) $e->credit,
                'balance'     => (float) $e->balance,
            ]),
        ]);
    }

    // ── Payments ──────────────────────────────────────────────────────────────

    public function payments(Request $request)
    {
        $query = Payment::with('invoice.student')->latest();

        if ($studentId = $request->input('student_id')) {
            $query->whereHas('invoice', fn($q) => $q->where('student_id', $studentId));
        }
        if ($from = $request->input('from')) {
            $query->whereDate('payment_date', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('payment_date', '<=', $to);
        }

        $payments = $query->paginate($request->input('per_page', 25));

        return PaymentResource::collection($payments);
    }

    public function storePayment(Request $request)
    {
        $data = $request->validate([
            'invoice_id'     => 'required|exists:invoices,id',
            'amount'         => 'required|numeric|min:0.01',
            'payment_date'   => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,cheque,esewa,khalti,stripe',
            'notes'          => 'nullable|string|max:500',
            'gateway_transaction_id' => 'nullable|string|max:100',
        ]);

        $invoice = Invoice::findOrFail($data['invoice_id']);

        if ($invoice->status === 'paid') {
            return response()->json(['message' => 'Invoice already paid.'], 422);
        }

        $payment = Payment::create(array_merge($data, [
            'gateway'    => $data['payment_method'],
            'recorded_by'=> auth()->id(),
        ]));

        return new PaymentResource($payment->load('invoice.student'));
    }

    // ── Summary ───────────────────────────────────────────────────────────────

    public function summary(Request $request)
    {
        $year  = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $from = \Carbon\Carbon::create($year, $month)->startOfMonth()->toDateString();
        $to   = \Carbon\Carbon::create($year, $month)->endOfMonth()->toDateString();

        return response()->json([
            'period'           => ['year' => $year, 'month' => $month],
            'invoiced'         => (float) Invoice::whereBetween('created_at', [$from, $to])->sum('amount'),
            'collected'        => (float) Payment::whereBetween('payment_date', [$from, $to])->sum('amount'),
            'outstanding'      => (float) Invoice::where('status', '!=', 'paid')->sum(\DB::raw('amount - COALESCE((SELECT SUM(amount) FROM payments WHERE payments.invoice_id = invoices.id), 0)')),
            'overdue_count'    => Invoice::where('status', '!=', 'paid')
                                    ->where('due_date', '<', today())->count(),
        ]);
    }
}
