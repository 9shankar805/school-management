<?php

namespace App\Http\Controllers;

use App\Models\FeeStructure;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SchoolSession;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ── Invoice list ──────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $this->authorize('view invoices');

        $query = Invoice::with(['student', 'payments', 'feeStructure', 'session'])
            ->whereNull('deleted_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('student', fn ($s) =>
                        $s->where('first_name', 'like', "%{$search}%")
                          ->orWhere('last_name', 'like', "%{$search}%")
                  );
            });
        }

        $invoices    = $query->latest()->paginate(25)->withQueryString();
        $totalPaid   = Invoice::where('status', 'paid')->whereNull('deleted_at')->count();
        $totalUnpaid = Invoice::where('status', 'unpaid')->whereNull('deleted_at')->count();
        $totalPartial= Invoice::where('status', 'partial')->whereNull('deleted_at')->count();

        return view('payments.index', compact('invoices', 'totalPaid', 'totalUnpaid', 'totalPartial'));
    }

    // ── Create invoice form ───────────────────────────────────────────────────
    public function create()
    {
        $this->authorize('create invoices');
        $students    = User::role('student')->orderBy('first_name')->get();
        $sessions    = SchoolSession::latest()->get();
        $structures  = FeeStructure::where('is_active', true)->orderBy('name')->get();
        return view('payments.create', compact('students', 'sessions', 'structures'));
    }

    // ── Store invoice ─────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $this->authorize('create invoices');

        $data = $request->validate([
            'student_id'       => 'required|exists:users,id',
            'title'            => 'required|string|max:255',
            'amount'           => 'required|numeric|min:0',
            'discount_amount'  => 'nullable|numeric|min:0',
            'tax_amount'       => 'nullable|numeric|min:0',
            'fee_structure_id' => 'nullable|exists:fee_structures,id',
            'session_id'       => 'nullable|exists:school_sessions,id',
            'due_date'         => 'nullable|date',
            'description'      => 'nullable|string|max:1000',
        ]);

        $data['discount_amount'] = $data['discount_amount'] ?? 0;
        $data['tax_amount']      = $data['tax_amount']      ?? 0;
        $data['net_amount']      = $data['amount'] - $data['discount_amount'] + $data['tax_amount'];
        $data['created_by']      = auth()->id();

        $invoice = Invoice::create($data);

        // Notify parents
        $student = User::find($data['student_id']);
        if ($student) {
            foreach ($student->parents as $parent) {
                $parent->notify(new \App\Notifications\FeeReminderNotification(
                    invoiceNumber: $invoice->invoice_number,
                    amount:        number_format($invoice->net_amount, 2),
                    dueDate:       $invoice->due_date?->format('d M Y') ?? 'N/A',
                ));
            }
        }

        return redirect()->route('payments.index')->with('status', 'Invoice created.');
    }

    // ── Show invoice (pay screen) ─────────────────────────────────────────────
    public function pay(int $id)
    {
        $invoice = Invoice::with(['student', 'payments', 'feeStructure'])->findOrFail($id);
        $this->authorize('view invoices');
        $paymentMethods = Payment::PAYMENT_METHODS;
        return view('payments.pay', compact('invoice', 'paymentMethods'));
    }

    // ── Process a payment ─────────────────────────────────────────────────────
    public function processPayment(Request $request, int $id)
    {
        $this->authorize('create invoices');

        $invoice = Invoice::findOrFail($id);

        $data = $request->validate([
            'amount_paid'           => 'required|numeric|min:0.01',
            'payment_date'          => 'required|date',
            'payment_method'        => 'required|string',
            'transaction_reference' => 'nullable|string|max:200',
            'bank_name'             => 'nullable|string|max:200',
            'cheque_number'         => 'nullable|string|max:100',
            'notes'                 => 'nullable|string|max:500',
        ]);

        $data['invoice_id']   = $id;
        $data['received_by']  = auth()->id();

        $payment = Payment::create($data);

        $this->refreshInvoiceStatus($invoice);

        return redirect()->route('payments.receipt', $payment->id)
            ->with('status', 'Payment recorded. Receipt generated.');
    }

    // ── PDF Receipt ───────────────────────────────────────────────────────────
    public function receipt(int $paymentId)
    {
        $payment = Payment::with(['invoice.student', 'invoice.feeStructure', 'receivedBy'])
            ->findOrFail($paymentId);

        $pdf = Pdf::loadView('finance.receipt', compact('payment'))
            ->setPaper([0, 0, 595, 420]) // A5 landscape
            ->setOptions(['dpi' => 120, 'defaultFont' => 'sans-serif']);

        return $pdf->stream('receipt-' . $payment->receipt_number . '.pdf');
    }

    // ── Edit invoice ──────────────────────────────────────────────────────────
    public function edit(int $id)
    {
        $this->authorize('create invoices');
        $invoice    = Invoice::findOrFail($id);
        $students   = User::role('student')->orderBy('first_name')->get();
        $sessions   = SchoolSession::latest()->get();
        $structures = FeeStructure::where('is_active', true)->orderBy('name')->get();
        return view('payments.edit', compact('invoice', 'students', 'sessions', 'structures'));
    }

    public function update(Request $request, int $id)
    {
        $this->authorize('create invoices');
        $invoice = Invoice::findOrFail($id);
        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'amount'           => 'required|numeric|min:0',
            'discount_amount'  => 'nullable|numeric|min:0',
            'tax_amount'       => 'nullable|numeric|min:0',
            'due_date'         => 'nullable|date',
            'description'      => 'nullable|string|max:1000',
        ]);
        $data['discount_amount'] = $data['discount_amount'] ?? 0;
        $data['tax_amount']      = $data['tax_amount']      ?? 0;
        $data['net_amount']      = $data['amount'] - $data['discount_amount'] + $data['tax_amount'];
        $invoice->update($data);
        return redirect()->route('payments.index')->with('status', 'Invoice updated.');
    }

    public function destroy(int $id)
    {
        $this->authorize('create invoices');
        Invoice::findOrFail($id)->delete();
        return back()->with('status', 'Invoice deleted.');
    }

    // ── Public helper used by FinanceController::installmentsMarkPaid ─────────
    public function refreshInvoiceStatus(Invoice $invoice): void
    {
        $invoice->refresh();
        $totalPaid = $invoice->payments()->whereNull('deleted_at')->sum('amount_paid');
        $net       = (float) $invoice->net_amount ?: (float) $invoice->amount;

        $status = match (true) {
            $totalPaid >= $net => 'paid',
            $totalPaid > 0    => 'partial',
            default           => 'unpaid',
        };

        $invoice->update(['status' => $status]);
    }
}
