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
            'student_id'     => 'required|exists:users,id',
            'title'          => 'required|string|max:255',
            'amount'         => 'required|numeric|min:0.01',
            'due_date'       => 'nullable|date',
            'description'    => 'nullable|string|max:1000',
            'session_id'     => 'nullable|exists:school_sessions,id',
            'payment_status' => 'nullable|string|in:unpaid,paid',
            'payment_method' => 'nullable|string|in:cash,bank,cheque,stripe,razorpay,paypal,flutterwave,esewa,khalti,fonepay,connectips,online',
            'notes'          => 'nullable|string|max:500',
            'items'          => 'nullable|array',
            'items.*.name'   => 'nullable|string|max:255',
            'items.*.amount' => 'nullable|numeric|min:0',
        ]);

        $invoice = Invoice::create([
            'student_id'   => $data['student_id'],
            'session_id'   => $data['session_id'] ?? null,
            'title'        => $data['title'],
            'amount'       => $data['amount'],
            'total_amount' => $data['amount'],
            'status'       => 'unpaid',
            'due_date'     => $data['due_date'] ?? null,
            'description'  => $data['description'] ?? null,
        ]);

        // Save itemized fee breakdown
        if (!empty($data['items'])) {
            foreach ($data['items'] as $item) {
                if (!empty($item['name']) && (float)($item['amount'] ?? 0) > 0) {
                    $feeTypeObj = DB::table('fee_types')->where('name', 'like', '%' . explode(' ', $item['name'])[0] . '%')->first();
                    DB::table('invoice_items')->insert([
                        'invoice_id'  => $invoice->id,
                        'fee_type_id' => $feeTypeObj ? $feeTypeObj->id : null,
                        'name'        => $item['name'],
                        'unit_price'  => (float)$item['amount'],
                        'quantity'    => 1,
                        'amount'      => (float)$item['amount'],
                        'total'       => (float)$item['amount'],
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }
            }
        }

        $paymentRecorded = false;
        $receiptNo = '';

        if (($request->input('payment_status') === 'paid') || $request->filled('payment_method')) {
            $method = $data['payment_method'] ?? 'cash';
            $payment = Payment::create([
                'invoice_id'     => $invoice->id,
                'amount'         => $data['amount'],
                'payment_date'   => now()->toDateString(),
                'payment_method' => $method,
                'gateway'        => $method,
                'notes'          => $data['notes'] ?? 'Immediate payment recorded at POS creation desk',
                'recorded_by'    => auth()->id(),
            ]);
            $paymentRecorded = true;
            $receiptNo = $payment->receipt_number;
        } else {
            // Dispatch fee reminder notification for unpaid invoice
            SendFeeReminderNotification::dispatch($invoice->id);
        }

        if ($paymentRecorded) {
            return redirect()->route('payments.index')
                ->with('status', 'Invoice #' . $invoice->invoice_number . ' created with itemized fee breakdown and Payment of Rs. ' . number_format($data['amount'], 2) . ' recorded cleanly! Receipt: ' . $receiptNo);
        }

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

        // Resolve school for QR display — try invoice school_id first,
        // then authenticated user's school, then invoice student's school
        $paySchool = \App\Models\School::find(
            $invoice->school_id
            ?? $user->school_id
            ?? optional($invoice->student)->school_id
        );

        return view('payments.pay', compact('invoice', 'paySchool'));
    }

    public function processPayment(Request $request, int $id)
    {
        $invoice = Invoice::findOrFail($id);

        $user = auth()->user();
        if ($user->hasRole('student') && $invoice->student_id !== $user->id) {
            abort(403);
        }

        $data = $request->validate([
            'amount'                 => 'required|numeric|min:0.01|max:' . ($invoice->amount_due + 0.01),
            'payment_date'           => 'required|date',
            'payment_method'         => 'required|in:cash,bank,cheque,stripe,razorpay,paypal,flutterwave,esewa,khalti,fonepay,connectips,online',
            'notes'                  => 'nullable|string|max:500',
            'gateway_transaction_id' => 'nullable|string|max:100',
        ]);

        $paymentId = null;

        DB::transaction(function () use ($invoice, $data, &$paymentId) {
            $payment = Payment::create([
                'invoice_id'             => $invoice->id,
                'amount'                 => $data['amount'],
                'payment_date'           => $data['payment_date'],
                'payment_method'         => $data['payment_method'],
                'gateway'                => $data['payment_method'],
                'gateway_transaction_id' => $data['gateway_transaction_id'] ?? null,
                'notes'                  => $data['notes'] ?? null,
                'recorded_by'            => auth()->id(),
                'school_id'              => $invoice->school_id ?? auth()->user()->school_id,
            ]);
            // Invoice status updated by Payment::booted() → updateInvoiceStatus()
            // Student ledger credit posted by Payment::booted() → postLedgerCredit()
            $paymentId = $payment->id;
        });

        return redirect()->route('payments.receipt', $paymentId)
            ->with('status', '✅ Payment recorded successfully.');
    }

    // ── HTML receipt page (web view) ─────────────────────────────────────────

    public function receiptPage(int $paymentId)
    {
        $payment = Payment::with([
            'invoice.student',
            'invoice.items.feeType',
            'invoice.payments',
            'recordedBy',
        ])->findOrFail($paymentId);

        $user = auth()->user();
        if ($user->hasRole('student') && $payment->invoice?->student_id !== $user->id) {
            abort(403);
        }
        if ($user->hasRole('parent')) {
            $childIds = $user->children()->pluck('users.id');
            if ($payment->invoice && !$childIds->contains($payment->invoice->student_id)) {
                abort(403);
            }
        }

        $school = \App\Models\School::find(
            $payment->invoice?->school_id
            ?? $user->school_id
            ?? optional($payment->invoice?->student)->school_id
        );

        return view('payments.receipt', compact('payment', 'school'));
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

        $paySchool = \App\Models\School::find(
            $invoice->school_id
            ?? $user->school_id
            ?? optional($invoice->student)->school_id
        );

        return view('payments.invoice-detail', compact('invoice', 'paySchool'));
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

    public function studentFeesJson(int $studentId)
    {
        $student  = User::with('promotions.schoolClass')->findOrFail($studentId);
        $invoices = Invoice::where('student_id', $studentId)
            ->with(['items', 'payments'])
            ->orderBy('id')
            ->get()
            ->map(function ($inv) {
                $paid = $inv->payments->sum('amount');
                $due  = max(0, $inv->amount - $paid);
                return [
                    'id'             => $inv->id,
                    'invoice_number' => $inv->invoice_number,
                    'title'          => $inv->title,
                    'amount'         => (float)$inv->amount,
                    'paid'           => (float)$paid,
                    'due'            => (float)$due,
                    'status'         => $inv->status,
                    'due_date'       => $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('M d, Y') : 'N/A',
                    'items'          => $inv->items->map(fn($item) => [
                        'name'   => $item->name,
                        'amount' => (float)$item->amount,
                    ]),
                ];
            });

        $totalOutstanding = $invoices->where('status', '!=', 'paid')->sum('due');
        $latestPromo = optional($student->promotions)->last();
        $className = $latestPromo?->schoolClass?->class_name ?? 'N/A';

        return response()->json([
            'success'          => true,
            'student'          => [
                'id'            => $student->id,
                'name'          => $student->full_name,
                'email'         => $student->email,
                'class'         => $className,
            ],
            'total_outstanding'=> (float)$totalOutstanding,
            'invoices'         => $invoices,
        ]);
    }
}
