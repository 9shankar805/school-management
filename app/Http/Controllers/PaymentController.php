<?php

namespace App\Http\Controllers;

use App\Jobs\SendFeeReminderNotification;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SchoolSession;
use App\Models\User;
use App\Services\BillingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function __construct(private readonly BillingService $billing)
    {
        $this->middleware('auth');
    }

    // ── Invoice listing ───────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $user = auth()->user();

        // Students see only their own invoices
        if ($user && $user->hasRole('student')) {
            $invoices = Invoice::with('items.feeType', 'payments')
                ->where('student_id', $user->id)
                ->latest()
                ->get();

            $totalOutstanding = $invoices->where('status', '!=', 'paid')->sum('amount');
            $paymentHistory   = Payment::whereIn('invoice_id', $invoices->pluck('id'))
                ->with('invoice')
                ->latest()
                ->get();

            return view('payments.student-index', compact('invoices', 'totalOutstanding', 'paymentHistory'));
        }

        // Admin / accountant — full listing with filters
        $query = Invoice::with('student', 'items', 'payments');

        if ($search = $request->input('search')) {
            $query->whereHas('student', fn($q) =>
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
            )->orWhere('invoice_number', 'like', "%{$search}%");
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($from = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $invoices = $query->latest()->paginate(25)->withQueryString();

        return view('payments.index', compact('invoices'));
    }

    // ── Create manual invoice ─────────────────────────────────────────────────

    public function create()
    {
        $students = User::role('student')->orderBy('first_name')->get();
        $sessions = SchoolSession::orderByDesc('id')->take(5)->get();
        return view('payments.create', compact('students', 'sessions'));
    }

    public function store(Request $request)
    {
        $this->authorize('manage finance');

        $data = $request->validate([
            'student_id'  => 'required|exists:users,id',
            'title'       => 'required|string|max:255',
            'amount'      => 'required|numeric|min:0.01',
            'due_date'    => 'nullable|date',
            'description' => 'nullable|string|max:1000',
            'session_id'  => 'nullable|exists:school_sessions,id',
        ]);

        $invoice = Invoice::create([
            'student_id'  => $data['student_id'],
            'session_id'  => $data['session_id'] ?? null,
            'title'       => $data['title'],
            'amount'      => $data['amount'],
            'total_amount'=> $data['amount'],
            'status'      => 'unpaid',
            'due_date'    => $data['due_date'] ?? null,
            'description' => $data['description'] ?? null,
        ]);

        // Dispatch fee reminder notification
        SendFeeReminderNotification::dispatch($invoice->id);

        return redirect()->route('payments.index')
            ->with('status', 'Invoice #' . $invoice->invoice_number . ' created successfully.');
    }

    // ── Bulk invoice generation (from fee structures) ─────────────────────────

    public function bulkGenerate(Request $request)
    {
        $this->authorize('manage finance');

        $data = $request->validate([
            'month'      => 'required|integer|min:1|max:12',
            'year'       => 'required|integer|min:2000|max:2100',
            'session_id' => 'nullable|exists:school_sessions,id',
        ]);

        $result = $this->billing->generateMonthlyForAll(
            (int) $data['month'],
            (int) $data['year'],
            $data['session_id'] ? (int) $data['session_id'] : null,
        );

        return redirect()->route('payments.index')->with('status',
            "Bulk invoices generated: {$result['generated']} created, {$result['skipped']} skipped, {$result['errors']} errors."
        );
    }

    // ── Payment form & processing ─────────────────────────────────────────────

    public function pay(int $id)
    {
        $invoice = Invoice::with('student', 'items.feeType', 'payments')->findOrFail($id);

        // Students can only pay their own invoices
        $user = auth()->user();
        if ($user->hasRole('student') && $invoice->student_id !== $user->id) {
            abort(403);
        }

        return view('payments.pay', compact('invoice'));
    }

    public function processPayment(Request $request, int $id)
    {
        $invoice = Invoice::findOrFail($id);

        $user = auth()->user();
        if ($user->hasRole('student') && $invoice->student_id !== $user->id) {
            abort(403);
        }

        $data = $request->validate([
            'amount'         => 'required|numeric|min:0.01|max:' . ($invoice->amount_due + 0.01),
            'payment_date'   => 'required|date',
            'payment_method' => 'required|in:cash,bank,cheque,stripe,razorpay,paypal,flutterwave',
            'notes'          => 'nullable|string|max:500',
            'gateway_transaction_id' => 'nullable|string|max:100',
        ]);

        DB::transaction(function () use ($invoice, $data) {
            Payment::create([
                'invoice_id'             => $invoice->id,
                'amount'                 => $data['amount'],
                'payment_date'           => $data['payment_date'],
                'payment_method'         => $data['payment_method'],
                'gateway'                => $data['payment_method'],
                'gateway_transaction_id' => $data['gateway_transaction_id'] ?? null,
                'notes'                  => $data['notes'] ?? null,
                'recorded_by'            => auth()->id(),
            ]);
            // Invoice status update is handled by Payment::booted() → updateInvoiceStatus()
            // Student ledger + school ledger entries handled by PaymentObserver
        });

        return redirect()->route('payments.index')
            ->with('status', 'Payment recorded. Receipt: ' . Payment::latest()->first()?->receipt_number);
    }

    // ── Student ledger view ───────────────────────────────────────────────────

    public function studentLedger(int $studentId)
    {
        $this->authorize('manage finance');

        $student = User::findOrFail($studentId);
        $session = SchoolSession::where('is_current', true)->first();

        $ledger = \App\Models\StudentLedger::where('student_id', $studentId)
            ->orderBy('id')
            ->get();

        $balance        = $ledger->last()?->balance ?? 0;
        $totalCharged   = $ledger->sum('debit');
        $totalPaid      = $ledger->sum('credit');

        return view('payments.student-ledger', compact(
            'student', 'ledger', 'balance', 'totalCharged', 'totalPaid', 'session'
        ));
    }

    // ── Invoice detail with line items ────────────────────────────────────────

    public function show(int $id)
    {
        $invoice = Invoice::with('student', 'items.feeType', 'payments.recordedBy')->findOrFail($id);

        $user = auth()->user();
        if ($user->hasRole('student') && $invoice->student_id !== $user->id) {
            abort(403);
        }

        return view('payments.invoice-detail', compact('invoice'));
    }

    // ── PDF receipt ───────────────────────────────────────────────────────────

    public function receipt(int $paymentId)
    {
        $payment = Payment::with(['invoice.student', 'invoice.items.feeType'])->findOrFail($paymentId);

        $user = auth()->user();
        if ($user->hasRole('student') && $payment->invoice?->student_id !== $user->id) {
            abort(403);
        }

        $pdf = Pdf::loadView('finance.receipt-pdf', compact('payment'))
            ->setPaper('a5');
        return $pdf->stream('receipt-' . $payment->receipt_number . '.pdf');
    }
}
