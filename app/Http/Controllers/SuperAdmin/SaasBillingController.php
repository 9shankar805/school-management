<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SaasAgreement;
use App\Models\SaasInvoice;
use App\Models\SaasInvoiceItem;
use App\Models\SaasPayment;
use App\Models\SaasService;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaasBillingController extends Controller
{
    public function index(Request $request)
    {
        $query = SaasInvoice::with(['school', 'agreement', 'payments']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('school_name', 'like', "%{$search}%")
                  ->orWhere('school_pan', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        $invoices = $query->latest('invoice_date')->paginate(15)->withQueryString();

        $stats = [
            'total_billed'      => SaasInvoice::where('status', '!=', 'cancelled')->sum('total_amount'),
            'total_collected'   => SaasInvoice::where('status', '!=', 'cancelled')->sum('paid_amount'),
            'total_outstanding' => SaasInvoice::where('status', '!=', 'cancelled')->sum('due_amount'),
            'total_invoices'    => SaasInvoice::count(),
            'paid_count'        => SaasInvoice::where('status', 'paid')->count(),
            'unpaid_count'      => SaasInvoice::whereIn('status', ['unpaid', 'overdue'])->count(),
        ];

        $schools = School::orderBy('name')->select('id', 'name')->get();

        return view('super-admin.billing.index', compact('invoices', 'stats', 'schools'));
    }

    public function create(Request $request)
    {
        $schools = School::orderBy('name')->get();
        $services = SaasService::where('is_active', true)->orderBy('category')->get();
        $agreements = SaasAgreement::where('status', 'active')->orderBy('school_name')->get();

        $selectedSchool = null;
        $selectedAgreement = null;

        if ($request->filled('school_id')) {
            $selectedSchool = School::find($request->school_id);
        }

        if ($request->filled('agreement_id')) {
            $selectedAgreement = SaasAgreement::with('items')->find($request->agreement_id);
            if ($selectedAgreement && !$selectedSchool && $selectedAgreement->school_id) {
                $selectedSchool = $selectedAgreement->school;
            }
        }

        $nextInvoiceNumber = SaasInvoice::generateInvoiceNumber();

        return view('super-admin.billing.create', compact(
            'schools', 'services', 'agreements', 'selectedSchool', 'selectedAgreement', 'nextInvoiceNumber'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_number'  => 'nullable|string|max:100|unique:saas_invoices,invoice_number',
            'school_id'       => 'nullable|exists:schools,id',
            'agreement_id'    => 'nullable|exists:saas_agreements,id',
            'school_name'     => 'required|string|max:255',
            'school_pan'      => 'nullable|string|max:50',
            'school_address'  => 'nullable|string|max:255',
            'school_email'    => 'nullable|email|max:255',
            'school_phone'    => 'nullable|string|max:100',
            'invoice_date'    => 'required|date',
            'due_date'        => 'nullable|date',
            'invoice_date_bs' => 'nullable|string|max:50',
            'due_date_bs'     => 'nullable|string|max:50',
            'discount_amount' => 'nullable|numeric|min:0',
            'advance_amount'  => 'nullable|numeric|min:0',
            'payment_method'  => 'nullable|string|in:cash,bank_transfer,esewa,khalti,cheque,connect_ips,other',
            'tax_percent'     => 'nullable|numeric|min:0|max:100',
            'notes'           => 'nullable|string',
            'terms'           => 'nullable|string',
            // Line items
            'items'           => 'required|array|min:1',
            'items.*.service_id'   => 'nullable|exists:saas_services,id',
            'items.*.title'        => 'required|string|max:255',
            'items.*.description'  => 'nullable|string',
            'items.*.quantity'     => 'required|numeric|min:0.01',
            'items.*.unit_price'   => 'required|numeric|min:0',
        ]);

        if (empty($validated['invoice_number'])) {
            $validated['invoice_number'] = SaasInvoice::generateInvoiceNumber();
        }

        $validated['created_by'] = auth()->id();

        DB::beginTransaction();
        try {
            $subtotal = 0;
            $itemsData = $request->input('items', []);

            foreach ($itemsData as $it) {
                $qty = (float)$it['quantity'];
                $price = (float)$it['unit_price'];
                $subtotal += ($qty * $price);
            }

            $discount = (float)($validated['discount_amount'] ?? 0);
            $afterDiscount = max(0, $subtotal - $discount);
            $taxPercent = (float)($validated['tax_percent'] ?? 0);
            $taxAmount = round($afterDiscount * ($taxPercent / 100), 2);
            $totalAmount = $afterDiscount + $taxAmount;

            $advance = (float)($request->input('advance_amount', 0));
            $paid = min($advance, $totalAmount);
            $due = max(0, $totalAmount - $paid);

            $status = 'unpaid';
            if ($paid >= $totalAmount && $totalAmount > 0) {
                $status = 'paid';
            } elseif ($paid > 0) {
                $status = 'partial';
            }

            $validated['subtotal']     = $subtotal;
            $validated['tax_amount']   = $taxAmount;
            $validated['total_amount'] = $totalAmount;
            $validated['paid_amount']  = $paid;
            $validated['due_amount']   = $due;
            $validated['status']       = $status;

            // Remove non-model attributes from $validated
            unset($validated['advance_amount'], $validated['payment_method']);

            $invoice = SaasInvoice::create($validated);

            foreach ($itemsData as $it) {
                $qty = (float)$it['quantity'];
                $price = (float)$it['unit_price'];
                $invoice->items()->create([
                    'service_id'   => !empty($it['service_id']) ? $it['service_id'] : null,
                    'title'        => $it['title'],
                    'description'  => $it['description'] ?? null,
                    'quantity'     => $qty,
                    'unit_price'   => $price,
                    'total_amount' => $qty * $price,
                ]);
            }

            // Create initial payment record if advance amount was given
            if ($paid > 0) {
                $invoice->payments()->create([
                    'payment_number'        => SaasPayment::generatePaymentNumber(),
                    'payment_date'          => $validated['invoice_date'],
                    'payment_date_bs'       => $validated['invoice_date_bs'] ?? null,
                    'amount'                => $paid,
                    'payment_method'        => $request->input('payment_method', 'cash'),
                    'transaction_reference' => 'Advance Payment',
                    'note'                  => 'Advance payment recorded during invoice generation',
                    'received_by'           => auth()->id(),
                ]);
            }

            DB::commit();

            return redirect()->route('super.billing.show', $invoice->id)
                ->with('success', 'SaaS Invoice created successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create invoice: ' . $e->getMessage());
        }
    }

    public function show(SaasInvoice $invoice)
    {
        $invoice->load(['school', 'agreement', 'items.service', 'payments']);
        return view('super-admin.billing.show', compact('invoice'));
    }

    public function print(SaasInvoice $invoice)
    {
        $invoice->load(['school', 'agreement', 'items.service', 'payments']);
        return view('super-admin.billing.print', compact('invoice'));
    }

    public function edit(SaasInvoice $invoice)
    {
        $invoice->load(['school', 'agreement', 'items']);
        $schools = School::orderBy('name')->get();
        $services = SaasService::where('is_active', true)->orderBy('category')->get();
        $agreements = SaasAgreement::where('status', 'active')->orderBy('school_name')->get();

        return view('super-admin.billing.edit', compact('invoice', 'schools', 'services', 'agreements'));
    }

    public function update(Request $request, SaasInvoice $invoice)
    {
        $validated = $request->validate([
            'invoice_number'  => 'required|string|max:100|unique:saas_invoices,invoice_number,' . $invoice->id,
            'school_id'       => 'nullable|exists:schools,id',
            'agreement_id'    => 'nullable|exists:saas_agreements,id',
            'school_name'     => 'required|string|max:255',
            'school_pan'      => 'nullable|string|max:50',
            'school_address'  => 'nullable|string|max:255',
            'school_email'    => 'nullable|email|max:255',
            'school_phone'    => 'nullable|string|max:100',
            'invoice_date'    => 'required|date',
            'due_date'        => 'nullable|date',
            'invoice_date_bs' => 'nullable|string|max:50',
            'due_date_bs'     => 'nullable|string|max:50',
            'discount_amount' => 'nullable|numeric|min:0',
            'advance_amount'  => 'nullable|numeric|min:0',
            'tax_percent'     => 'nullable|numeric|min:0|max:100',
            'notes'           => 'nullable|string',
            'terms'           => 'nullable|string',
            // Line items
            'items'           => 'required|array|min:1',
            'items.*.service_id'   => 'nullable|exists:saas_services,id',
            'items.*.title'        => 'required|string|max:255',
            'items.*.description'  => 'nullable|string',
            'items.*.quantity'     => 'required|numeric|min:0.01',
            'items.*.unit_price'   => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $invoice->items()->delete();
            $itemsData = $request->input('items', []);

            $subtotal = 0;
            foreach ($itemsData as $it) {
                $qty = (float)$it['quantity'];
                $price = (float)$it['unit_price'];
                $lineTotal = $qty * $price;
                $subtotal += $lineTotal;

                $invoice->items()->create([
                    'service_id'   => !empty($it['service_id']) ? $it['service_id'] : null,
                    'title'        => $it['title'],
                    'description'  => $it['description'] ?? null,
                    'quantity'     => $qty,
                    'unit_price'   => $price,
                    'total_amount' => $lineTotal,
                ]);
            }

            $discount = (float)($validated['discount_amount'] ?? 0);
            $afterDiscount = max(0, $subtotal - $discount);
            $taxPercent = (float)($validated['tax_percent'] ?? 0);
            $taxAmount = round($afterDiscount * ($taxPercent / 100), 2);
            $totalAmount = $afterDiscount + $taxAmount;

            $existingPaymentsCount = $invoice->payments()->count();
            if ($existingPaymentsCount === 0 && $request->filled('advance_amount') && (float)$request->advance_amount > 0) {
                $advance = min((float)$request->advance_amount, $totalAmount);
                $invoice->payments()->create([
                    'payment_number'        => SaasPayment::generatePaymentNumber(),
                    'payment_date'          => $validated['invoice_date'],
                    'payment_date_bs'       => $validated['invoice_date_bs'] ?? null,
                    'amount'                => $advance,
                    'payment_method'        => 'cash',
                    'transaction_reference' => 'Advance Payment',
                    'note'                  => 'Advance payment recorded during invoice update',
                    'received_by'           => auth()->id(),
                ]);
            }

            $paid = $invoice->payments()->sum('amount');
            $due = max(0, $totalAmount - $paid);

            $status = 'unpaid';
            if ($paid >= $totalAmount && $totalAmount > 0) {
                $status = 'paid';
            } elseif ($paid > 0 && $paid < $totalAmount) {
                $status = 'partial';
            } elseif (!empty($validated['due_date']) && strtotime($validated['due_date']) < time() && $paid < $totalAmount) {
                $status = 'overdue';
            }

            unset($validated['advance_amount']);

            $validated['subtotal']     = $subtotal;
            $validated['tax_amount']   = $taxAmount;
            $validated['total_amount'] = $totalAmount;
            $validated['paid_amount']  = $paid;
            $validated['due_amount']   = $due;
            $validated['status']       = $status;

            $invoice->update($validated);

            DB::commit();

            return redirect()->route('super.billing.show', $invoice->id)
                ->with('success', 'SaaS Invoice updated successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update invoice: ' . $e->getMessage());
        }
    }

    public function destroy(SaasInvoice $invoice)
    {
        $invoice->delete();
        return redirect()->route('super.billing.index')
            ->with('success', 'Invoice deleted successfully.');
    }

    public function recordPayment(Request $request, SaasInvoice $invoice)
    {
        $validated = $request->validate([
            'amount'                => 'required|numeric|min:1|max:' . ($invoice->due_amount > 0 ? $invoice->due_amount : $invoice->total_amount),
            'payment_date'          => 'required|date',
            'payment_date_bs'       => 'nullable|string|max:50',
            'payment_method'        => 'required|string|in:bank_transfer,esewa,khalti,cash,cheque,connect_ips,other',
            'transaction_reference' => 'nullable|string|max:100',
            'bank_name'             => 'nullable|string|max:100',
            'note'                  => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $paymentNumber = SaasPayment::generatePaymentNumber();

            $invoice->payments()->create([
                'payment_number'        => $paymentNumber,
                'payment_date'          => $validated['payment_date'],
                'payment_date_bs'       => $validated['payment_date_bs'] ?? null,
                'amount'                => $validated['amount'],
                'payment_method'        => $validated['payment_method'],
                'transaction_reference' => $validated['transaction_reference'] ?? null,
                'bank_name'             => $validated['bank_name'] ?? null,
                'note'                  => $validated['note'] ?? null,
                'received_by'           => auth()->id(),
            ]);

            $invoice->recalculate();

            DB::commit();

            return redirect()->route('super.billing.show', $invoice->id)
                ->with('success', 'Payment of Rs. ' . number_format($validated['amount'], 2) . ' recorded successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to record payment: ' . $e->getMessage());
        }
    }
}

