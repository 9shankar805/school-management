<?php

namespace App\Http\Controllers;

use App\Jobs\SendFeeReminderNotification;
use App\Models\FeeStructure;
use App\Models\FeeType;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\SchoolSession;
use App\Models\SchoolBankAccount;
use App\Models\StudentLedger;
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
                    InvoiceItem::create([
                        'invoice_id'      => $invoice->id,
                        'fee_type_id'     => $feeTypeObj ? $feeTypeObj->id : null,
                        'description'     => $item['name'],
                        'unit_price'      => (float) $item['amount'],
                        'quantity'        => 1,
                        'discount_amount' => 0,
                        'tax_rate'        => 0,
                        'tax_amount'      => 0,
                        'total'           => (float) $item['amount'],
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
            'bankAccount',
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
        $session = SchoolSession::current() ?? SchoolSession::latest('id')->first();

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

    public function receipt(Request $request, int $paymentId)
    {
        $payment = Payment::with(['invoice.student', 'invoice.items.feeType', 'recordedBy', 'bankAccount'])->findOrFail($paymentId);

        $user = auth()->user();
        if ($user->hasRole('student') && $payment->invoice?->student_id !== $user->id) {
            abort(403, 'Unauthorized: you can only view your own receipts.');
        }

        if ($user->hasRole('parent')) {
            $childIds = $user->children()->pluck('users.id');
            if ($payment->invoice && !$childIds->contains($payment->invoice->student_id)) {
                abort(403, 'Unauthorized: you can only view receipts for your registered children.');
            }
        }

        // Multi-tenant check for staff/accountants
        if ($user->school_id && !$user->hasRole('super-admin') && !$user->hasRole(['student', 'parent'])) {
            $receiptSchoolId = $payment->invoice?->school_id ?? $payment->school_id ?? $payment->invoice?->student?->school_id;
            if ($receiptSchoolId && $receiptSchoolId !== $user->school_id) {
                abort(403, 'Unauthorized: cross-school receipt access is forbidden.');
            }
        }

        $school = \App\Models\School::find(
            $payment->invoice?->school_id
            ?? $user->school_id
            ?? optional($payment->invoice?->student)->school_id
        );

        $format = $request->query('format', 'standard');

        if ($format === 'dual') {
            $pdf = Pdf::loadView('finance.receipt-dual-copy', compact('payment', 'school'))
                ->setPaper('a4', 'portrait');
            return $pdf->stream('receipt-dual-' . $payment->receipt_number . '.pdf');
        }

        if ($format === 'thermal') {
            return view('finance.receipt-thermal', compact('payment', 'school'));
        }

        $pdf = Pdf::loadView('finance.receipt-pdf', compact('payment', 'school'))
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

        // Calculate student wallet / advance prepaid credit
        $ledgerEntries = \App\Models\StudentLedger::where('student_id', $studentId)->orderBy('id')->get();
        $totalCredits = (float)$ledgerEntries->sum('credit');
        $totalDebits  = (float)$ledgerEntries->sum('debit');
        $walletCredit = max(0, $totalCredits - $totalDebits);

        return response()->json([
            'success'               => true,
            'student'               => [
                'id'            => $student->id,
                'name'          => $student->full_name,
                'email'         => $student->email,
                'class'         => $className,
            ],
            'total_outstanding'     => (float)$totalOutstanding,
            'wallet_credit_balance' => (float)$walletCredit,
            'invoices'              => $invoices,
        ]);
    }

    // ── FAST FEE COLLECTION POS TERMINAL ──────────────────────────────────────

    public function posIndex(Request $request)
    {
        $this->authorize('manage finance');

        $today = today()->toDateString();
        $todayPayments = Payment::whereDate('payment_date', $today)->get();

        $todayTotalCollected = (float) $todayPayments->sum('amount');
        $todayCashCollected = (float) $todayPayments->whereIn('payment_method', ['cash'])->sum('amount');
        $todayDigitalCollected = (float) $todayPayments->whereNotIn('payment_method', ['cash'])->sum('amount');
        $todayReceiptsCount = $todayPayments->count();

        // Recent 5 collections today
        $recentCollections = Payment::with(['invoice.student', 'recordedBy'])
            ->whereDate('payment_date', $today)
            ->latest('id')
            ->take(6)
            ->get();

        // Initial 50 students
        $initialStudents = User::role('student')
            ->with(['promotions.schoolClass', 'promotions.section'])
            ->orderBy('first_name')
            ->take(50)
            ->get();

        // Active fee structures / presets
        $feePresets = FeeType::where('is_active', true)->orderBy('name')->take(8)->get();

        // Multi-Bank Accounts for Fonepay QR & Bank Transfer
        SchoolBankAccountController::ensureDefaultBanksExist();
        $bankAccounts = SchoolBankAccount::active()->defaultFirst()->get();
        $defaultBank = $bankAccounts->firstWhere('is_default', true) ?? $bankAccounts->first();

        $selectedStudentId = $request->input('student_id');

        return view('finance.pos', compact(
            'todayTotalCollected',
            'todayCashCollected',
            'todayDigitalCollected',
            'todayReceiptsCount',
            'recentCollections',
            'initialStudents',
            'feePresets',
            'bankAccounts',
            'defaultBank',
            'selectedStudentId'
        ));
    }

    public function posSearchStudents(Request $request)
    {
        $this->authorize('manage finance');
        $q = trim((string) $request->input('q', ''));

        if (empty($q)) {
            $students = User::role('student')
                ->with(['promotions.schoolClass', 'promotions.section', 'parents', 'parent_info'])
                ->orderBy('first_name')
                ->take(15)
                ->get();
        } else {
            $students = User::role('student')
                ->where(function ($query) use ($q) {
                    $query->where('first_name', 'like', "%{$q}%")
                          ->orWhere('last_name', 'like', "%{$q}%")
                          ->orWhere('admission_no', 'like', "%{$q}%")
                          ->orWhere('roll_no', 'like', "%{$q}%")
                          ->orWhere('email', 'like', "%{$q}%")
                          ->orWhere('phone', 'like', "%{$q}%");
                })
                ->with(['promotions.schoolClass', 'promotions.section', 'parents', 'parent_info'])
                ->take(20)
                ->get();
        }

        $formatted = $students->map(function ($s) {
            $promo = optional($s->promotions)->last();
            $className = $promo?->schoolClass?->class_name ?? 'Class N/A';
            $sectionName = $promo?->section?->section_name ?? '';

            $unpaidInvoices = Invoice::where('student_id', $s->id)->where('status', '!=', 'paid')->get();
            $totalDue = (float) $unpaidInvoices->sum('amount_due');

            $ledger = StudentLedger::where('student_id', $s->id)->get();
            $totCredits = (float) $ledger->sum('credit');
            $totDebits  = (float) $ledger->sum('debit');
            $wallet = max(0, $totCredits - $totDebits);

            $guardian = $s->parents->first();
            $guardianPhone = $guardian?->phone ?? $s->parent_info?->father_mobile ?? $s->parent_info?->mother_mobile ?? $s->phone ?? '—';
            $guardianName = $guardian?->full_name ?? $s->parent_info?->father_name ?? $s->parent_info?->guardian_name ?? '—';

            return [
                'id'              => $s->id,
                'name'            => $s->full_name,
                'admission_no'    => $s->admission_no ?? '—',
                'roll_no'         => $s->roll_no ?? '—',
                'class_name'      => $className,
                'section_name'    => $sectionName,
                'avatar'          => $s->photo ? asset('storage/' . $s->photo) : null,
                'guardian_name'   => $guardianName,
                'guardian_phone'  => $guardianPhone,
                'total_due'       => $totalDue,
                'unpaid_count'    => $unpaidInvoices->count(),
                'wallet_credit'   => $wallet,
            ];
        });

        return response()->json([
            'success'  => true,
            'students' => $formatted,
        ]);
    }

    public function posStudentDetails(int $id)
    {
        $this->authorize('manage finance');

        $student = User::with([
            'promotions.schoolClass', 'promotions.section',
            'parents', 'parent_info'
        ])->findOrFail($id);

        $promo = optional($student->promotions)->last();
        $classId = $promo?->class_id;
        $className = $promo?->schoolClass?->class_name ?? 'Class N/A';
        $sectionName = $promo?->section?->section_name ?? '';

        // Unpaid & Partial Invoices
        $invoices = Invoice::where('student_id', $student->id)
            ->with(['items.feeType', 'payments'])
            ->orderBy('due_date')
            ->orderBy('id')
            ->get()
            ->map(function ($inv) {
                $paid = (float) $inv->payments->sum('amount');
                $due = max(0, (float) $inv->amount_due);
                return [
                    'id'             => $inv->id,
                    'invoice_number' => $inv->invoice_number,
                    'title'          => $inv->title,
                    'amount'         => (float) $inv->amount,
                    'paid'           => $paid,
                    'due'            => $due,
                    'status'         => $inv->status,
                    'is_overdue'     => $inv->due_date && \Carbon\Carbon::parse($inv->due_date)->isPast() && $inv->status !== 'paid',
                    'due_date'       => $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('M d, Y') : 'N/A',
                    'items'          => $inv->items->map(fn($it) => [
                        'name'   => $it->description ?? $it->feeType?->name ?? 'Fee Item',
                        'amount' => (float) ($it->total ?? $it->unit_price ?? 0),
                    ]),
                ];
            });

        // Student running wallet balance
        $ledger = StudentLedger::where('student_id', $student->id)->get();
        $totCredits = (float) $ledger->sum('credit');
        $totDebits  = (float) $ledger->sum('debit');
        $walletCredit = max(0, $totCredits - $totDebits);

        // Recent 5 receipts
        $recentReceipts = Payment::whereIn('invoice_id', Invoice::where('student_id', $student->id)->pluck('id'))
            ->with('invoice')
            ->latest('id')
            ->take(5)
            ->get()
            ->map(fn($p) => [
                'id'             => $p->id,
                'receipt_number' => $p->receipt_number,
                'amount'         => (float) $p->amount,
                'method'         => strtoupper($p->payment_method ?? 'CASH'),
                'date'           => $p->payment_date ? \Carbon\Carbon::parse($p->payment_date)->format('M d, Y') : 'N/A',
            ]);

        // Applicable fee structures for quick add
        $feeStructures = FeeStructure::with('feeType')
            ->where('is_active', true)
            ->when($classId, fn($q) => $q->where(fn($sq) => $sq->where('class_id', $classId)->orWhereNull('class_id')))
            ->get()
            ->map(fn($fs) => [
                'id'     => $fs->id,
                'name'   => $fs->name ?? $fs->feeType?->name ?? 'Fee',
                'amount' => (float) $fs->amount,
            ]);

        $guardian = $student->parents->first();
        $guardianPhone = $guardian?->phone ?? $student->parent_info?->father_mobile ?? $student->parent_info?->mother_mobile ?? $student->phone ?? '';
        $guardianName = $guardian?->full_name ?? $student->parent_info?->father_name ?? $student->parent_info?->guardian_name ?? 'Parent / Guardian';

        $totalOutstanding = $invoices->where('status', '!=', 'paid')->sum('due');

        return response()->json([
            'success'           => true,
            'student'           => [
                'id'              => $student->id,
                'name'            => $student->full_name,
                'email'           => $student->email,
                'admission_no'    => $student->admission_no ?? '—',
                'roll_no'         => $student->roll_no ?? '—',
                'class_name'      => $className,
                'section_name'    => $sectionName,
                'avatar'          => $student->photo ? asset('storage/' . $student->photo) : null,
                'guardian_name'   => $guardianName,
                'guardian_phone'  => $guardianPhone,
                'address'         => $student->address ?? 'Kathmandu, Nepal',
            ],
            'invoices'          => $invoices,
            'wallet_credit'     => (float) $walletCredit,
            'total_outstanding' => (float) $totalOutstanding,
            'recent_receipts'   => $recentReceipts,
            'fee_structures'    => $feeStructures,
        ]);
    }

    public function posCollect(Request $request)
    {
        $this->authorize('manage finance');

        $data = $request->validate([
            'student_id'                => 'required|integer|exists:users,id',
            'settlement_mode'           => 'required|in:invoices,instant_fee,advance_deposit',
            'invoice_ids'               => 'nullable|array',
            'invoice_ids.*'             => 'integer|exists:invoices,id',
            'items'                     => 'nullable|array',
            'items.*.name'              => 'nullable|string|max:255',
            'items.*.amount'            => 'nullable|numeric|min:0',
            'invoice_title'             => 'nullable|string|max:255',
            'amount_paid'               => 'required|numeric|min:0.01',
            'payment_method'            => 'required|string|in:cash,esewa,khalti,fonepay,connectips,stripe,paypal,bank,cheque,wallet_credit',
            'bank_account_id'           => 'nullable|integer|exists:school_bank_accounts,id',
            'transaction_reference'     => 'nullable|string|max:100',
            'cash_tendered'             => 'nullable|numeric|min:0',
            'deposit_change_to_wallet'  => 'nullable|boolean',
            'fine_waiver'               => 'nullable|numeric|min:0',
            'fine_waiver_reason'        => 'nullable|string|max:255',
            'notes'                     => 'nullable|string|max:500',
            'due_date'                  => 'nullable|date',
        ]);

        $student = User::with(['promotions.schoolClass', 'promotions.section'])->findOrFail($data['student_id']);
        $schoolId = $student->school_id ?? auth()->user()->school_id;
        $currentSession = SchoolSession::current() ?? SchoolSession::latest('id')->first();
        $sessionId = $currentSession?->id;

        $paymentRecord = null;
        $excessChangeDeposited = 0;
        $changeReturned = 0;
        $primaryInvoice = null;

        DB::transaction(function () use (
            $data, $student, $schoolId, $sessionId,
            &$paymentRecord, &$excessChangeDeposited, &$changeReturned, &$primaryInvoice
        ) {
            $amountToPay = (float) $data['amount_paid'];
            $method = $data['payment_method'];
            $cashTendered = isset($data['cash_tendered']) ? (float)$data['cash_tendered'] : $amountToPay;
            $depositChange = !empty($data['deposit_change_to_wallet']);
            $bankAccountId = $data['bank_account_id'] ?? null;
            $traceId = $data['transaction_reference'] ?? null;
            $bankObj = $bankAccountId ? SchoolBankAccount::find($bankAccountId) : null;
            $bankTag = '';
            if ($traceId) $bankTag .= " [Ref/Trace: {$traceId}]";
            if ($bankObj) $bankTag .= " [Bank: {$bankObj->bank_name}]";

            // 1. Advance wallet deposit
            if ($data['settlement_mode'] === 'advance_deposit') {
                $primaryInvoice = Invoice::create([
                    'student_id'         => $student->id,
                    'session_id'         => $sessionId,
                    'school_id'          => $schoolId,
                    'title'              => 'Advance Fee Wallet Deposit',
                    'amount'             => 0,
                    'total_amount'       => 0,
                    'status'             => 'paid',
                    'description'        => $data['notes'] ?? 'Advance prepaid fee credit deposit at POS counter',
                    'is_auto_generated'  => false,
                ]);

                $paymentRecord = Payment::create([
                    'invoice_id'             => $primaryInvoice->id,
                    'school_id'              => $schoolId,
                    'bank_account_id'        => $bankAccountId,
                    'gateway_transaction_id' => $traceId,
                    'amount'                 => $amountToPay,
                    'payment_date'           => today()->toDateString(),
                    'payment_method'         => $method,
                    'gateway'                => $method,
                    'notes'                  => 'Advance Wallet Deposit: ' . ($data['notes'] ?? '') . $bankTag,
                    'recorded_by'            => auth()->id(),
                ]);

            } elseif ($data['settlement_mode'] === 'invoices' && !empty($data['invoice_ids'])) {
                // 2. Settle against selected existing unpaid invoices
                $invoices = Invoice::whereIn('id', $data['invoice_ids'])
                    ->where('student_id', $student->id)
                    ->orderBy('due_date')
                    ->orderBy('id')
                    ->get();

                $remainingPayment = $amountToPay;

                foreach ($invoices as $inv) {
                    if ($remainingPayment <= 0) break;

                    $invDue = (float) $inv->amount_due;
                    $payForThis = min($remainingPayment, $invDue);

                    if ($payForThis > 0) {
                        $p = Payment::create([
                            'invoice_id'             => $inv->id,
                            'school_id'              => $schoolId,
                            'bank_account_id'        => $bankAccountId,
                            'gateway_transaction_id' => $traceId,
                            'amount'                 => $payForThis,
                            'payment_date'           => today()->toDateString(),
                            'payment_method'         => $method,
                            'gateway'                => $method,
                            'notes'                  => ($data['notes'] ?? 'Settled via POS Billing Terminal') . $bankTag,
                            'recorded_by'            => auth()->id(),
                        ]);

                        if (!$paymentRecord) {
                            $paymentRecord = $p;
                            $primaryInvoice = $inv;
                        }

                        $remainingPayment -= $payForThis;
                    }
                }

                // If extra amount remained above invoices, record as advance wallet deposit
                if ($remainingPayment > 0) {
                    $advanceInv = Invoice::create([
                        'student_id'         => $student->id,
                        'session_id'         => $sessionId,
                        'school_id'          => $schoolId,
                        'title'              => 'Advance Fee Wallet Deposit',
                        'amount'             => 0,
                        'total_amount'       => 0,
                        'status'             => 'paid',
                        'description'        => 'Excess fee payment credited to Student Wallet',
                        'is_auto_generated'  => false,
                    ]);

                    $advPayment = Payment::create([
                        'invoice_id'             => $advanceInv->id,
                        'school_id'              => $schoolId,
                        'bank_account_id'        => $bankAccountId,
                        'gateway_transaction_id' => $traceId,
                        'amount'                 => $remainingPayment,
                        'payment_date'           => today()->toDateString(),
                        'payment_method'         => $method,
                        'gateway'                => $method,
                        'notes'                  => 'Excess fee payment credited to Student Wallet' . $bankTag,
                        'recorded_by'            => auth()->id(),
                    ]);

                    if (!$paymentRecord) {
                        $paymentRecord = $advPayment;
                        $primaryInvoice = $advanceInv;
                    }

                    $excessChangeDeposited += $remainingPayment;
                }

            } else {
                // 3. Instant custom fee line items
                $items = $data['items'] ?? [];
                $title = !empty($data['invoice_title']) ? $data['invoice_title'] : 'Fee Payment';

                $totalAmount = 0;
                if (!empty($items)) {
                    foreach ($items as $it) {
                        $totalAmount += (float) ($it['amount'] ?? 0);
                    }
                }
                if ($totalAmount <= 0) {
                    $totalAmount = $amountToPay;
                }

                $fineWaiver = (float)($data['fine_waiver'] ?? 0);
                $finalInvoiceAmount = max(0, $totalAmount - $fineWaiver);

                $primaryInvoice = Invoice::create([
                    'student_id'         => $student->id,
                    'session_id'         => $sessionId,
                    'school_id'          => $schoolId,
                    'title'              => $title,
                    'amount'             => $finalInvoiceAmount,
                    'total_amount'       => $finalInvoiceAmount,
                    'discount_amount'    => $fineWaiver,
                    'status'             => 'unpaid',
                    'due_date'           => $data['due_date'] ?? today()->toDateString(),
                    'description'        => ($data['notes'] ?? 'Instant POS Fee Settlement') . $bankTag,
                    'is_auto_generated'  => false,
                ]);

                if (!empty($items)) {
                    foreach ($items as $it) {
                        if (!empty($it['name']) && (float)($it['amount'] ?? 0) > 0) {
                            $feeType = FeeType::where('name', 'like', '%' . explode(' ', $it['name'])[0] . '%')->first();
                            InvoiceItem::create([
                                'invoice_id'      => $primaryInvoice->id,
                                'fee_type_id'     => $feeType?->id,
                                'description'     => $it['name'],
                                'unit_price'      => (float) $it['amount'],
                                'quantity'        => 1,
                                'discount_amount' => 0,
                                'tax_rate'        => 0,
                                'tax_amount'      => 0,
                                'total'           => (float) $it['amount'],
                            ]);
                        }
                    }
                }

                $paymentRecord = Payment::create([
                    'invoice_id'             => $primaryInvoice->id,
                    'school_id'              => $schoolId,
                    'bank_account_id'        => $bankAccountId,
                    'gateway_transaction_id' => $traceId,
                    'amount'                 => min($amountToPay, $finalInvoiceAmount),
                    'payment_date'           => today()->toDateString(),
                    'payment_method'         => $method,
                    'gateway'                => $method,
                    'notes'                  => ($data['notes'] ?? 'POS instant counter payment') . $bankTag,
                    'recorded_by'            => auth()->id(),
                ]);
            }

            // Cash Tendered & Change handling
            if ($cashTendered > $amountToPay) {
                $diff = $cashTendered - $amountToPay;
                if ($depositChange) {
                    $changeDepositInv = Invoice::create([
                        'student_id'         => $student->id,
                        'session_id'         => $sessionId,
                        'school_id'          => $schoolId,
                        'title'              => 'Change Balance Deposit to Wallet',
                        'amount'             => 0,
                        'total_amount'       => 0,
                        'status'             => 'paid',
                        'description'        => 'Change balance deposit from POS Receipt #' . $paymentRecord->receipt_number,
                        'is_auto_generated'  => false,
                    ]);

                    Payment::create([
                        'invoice_id'     => $changeDepositInv->id,
                        'school_id'      => $schoolId,
                        'amount'         => $diff,
                        'payment_date'   => today()->toDateString(),
                        'payment_method' => 'cash',
                        'gateway'        => 'cash',
                        'notes'          => 'Change balance deposit into Student Wallet',
                        'recorded_by'    => auth()->id(),
                    ]);

                    $excessChangeDeposited += $diff;
                    $changeReturned = 0;
                } else {
                    $changeReturned = $diff;
                }
            }
        });

        // Compute updated student metrics
        $updatedLedger = StudentLedger::where('student_id', $student->id)->get();
        $totCred = (float) $updatedLedger->sum('credit');
        $totDeb  = (float) $updatedLedger->sum('debit');
        $currentWalletBalance = max(0, $totCred - $totDeb);
        $totalOutstanding = (float) Invoice::where('student_id', $student->id)->where('status', '!=', 'paid')->get()->sum('amount_due');

        return response()->json([
            'success'               => true,
            'message'               => 'Payment recorded cleanly and receipt generated.',
            'payment_id'            => $paymentRecord->id,
            'receipt_number'        => $paymentRecord->receipt_number,
            'student_name'          => $student->full_name,
            'admission_no'          => $student->admission_no ?? $student->roll_no ?? 'N/A',
            'amount_paid'           => (float) $data['amount_paid'],
            'payment_method'        => strtoupper($data['payment_method']),
            'bank_name'             => $bankObj?->bank_name,
            'transaction_reference' => $traceId,
            'change_returned'       => (float) $changeReturned,
            'wallet_deposited'      => (float) $excessChangeDeposited,
            'new_wallet_balance'    => (float) $currentWalletBalance,
            'total_outstanding'     => (float) $totalOutstanding,
            'thermal_receipt_url'   => route('payments.receipt-download', ['paymentId' => $paymentRecord->id, 'format' => 'thermal']),
            'dual_receipt_url'      => route('payments.receipt-download', ['paymentId' => $paymentRecord->id, 'format' => 'dual']),
            'standard_receipt_url'  => route('payments.receipt-download', ['paymentId' => $paymentRecord->id]),
            'web_receipt_url'       => route('payments.receipt.page', ['paymentId' => $paymentRecord->id]),
        ]);
    }
}

