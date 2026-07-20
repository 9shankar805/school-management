<?php

namespace App\Http\Controllers;

use App\Models\FeeCategory;
use App\Models\FeeDiscount;
use App\Models\FeeStructure;
use App\Models\FeeStructureItem;
use App\Models\InstallmentPlan;
use App\Models\InstallmentPlanItem;
use App\Models\SchoolClass;
use App\Models\SchoolSession;
use App\Models\Program;
use App\Models\Term;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FinanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // FEE CATEGORIES
    // ═══════════════════════════════════════════════════════════════════════════

    public function categoriesIndex()
    {
        $this->authorize('view invoices');
        $categories = FeeCategory::orderBy('sort_order')->orderBy('name')->get();
        return view('finance.categories.index', compact('categories'));
    }

    public function categoriesStore(Request $request)
    {
        $this->authorize('create invoices');
        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:fee_categories,name',
            'description' => 'nullable|string|max:500',
            'sort_order'  => 'nullable|integer|min:0',
        ]);
        $data['slug'] = Str::slug($data['name']);
        FeeCategory::create($data);
        return back()->with('status', 'Fee category created.');
    }

    public function categoriesUpdate(Request $request, int $id)
    {
        $this->authorize('create invoices');
        $cat  = FeeCategory::findOrFail($id);
        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:fee_categories,name,' . $id,
            'description' => 'nullable|string|max:500',
            'sort_order'  => 'nullable|integer|min:0',
            'is_active'   => 'nullable|boolean',
        ]);
        $data['slug']      = Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active', true);
        $cat->update($data);
        return back()->with('status', 'Fee category updated.');
    }

    public function categoriesDestroy(int $id)
    {
        $this->authorize('create invoices');
        FeeCategory::findOrFail($id)->delete();
        return back()->with('status', 'Fee category deleted.');
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // FEE STRUCTURES
    // ═══════════════════════════════════════════════════════════════════════════

    public function structuresIndex()
    {
        $this->authorize('view invoices');
        $structures = FeeStructure::with(['schoolClass', 'session', 'term', 'items'])
            ->latest()->paginate(20);
        return view('finance.structures.index', compact('structures'));
    }

    public function structuresCreate()
    {
        $this->authorize('create invoices');
        $sessions    = SchoolSession::latest()->get();
        $classes     = SchoolClass::orderBy('name')->get();
        $programs    = Program::orderBy('name')->get();
        $terms       = Term::orderBy('name')->get();
        $categories  = FeeCategory::where('is_active', true)->orderBy('sort_order')->get();
        return view('finance.structures.create', compact('sessions', 'classes', 'programs', 'terms', 'categories'));
    }

    public function structuresStore(Request $request)
    {
        $this->authorize('create invoices');
        $data = $request->validate([
            'name'         => 'required|string|max:200',
            'session_id'   => 'nullable|exists:school_sessions,id',
            'class_id'     => 'nullable|exists:school_classes,id',
            'program_id'   => 'nullable|exists:programs,id',
            'term_id'      => 'nullable|exists:terms,id',
            'notes'        => 'nullable|string|max:1000',
            // line items
            'items'                    => 'required|array|min:1',
            'items.*.fee_category_id'  => 'required|exists:fee_categories,id',
            'items.*.amount'           => 'required|numeric|min:0',
            'items.*.is_mandatory'     => 'nullable|boolean',
            'items.*.notes'            => 'nullable|string|max:255',
        ]);

        $structure = FeeStructure::create([
            'name'       => $data['name'],
            'session_id' => $data['session_id'] ?? null,
            'class_id'   => $data['class_id']   ?? null,
            'program_id' => $data['program_id'] ?? null,
            'term_id'    => $data['term_id']    ?? null,
            'notes'      => $data['notes']      ?? null,
        ]);

        foreach ($data['items'] as $item) {
            $structure->items()->create([
                'fee_category_id' => $item['fee_category_id'],
                'amount'          => $item['amount'],
                'is_mandatory'    => (bool) ($item['is_mandatory'] ?? true),
                'notes'           => $item['notes'] ?? null,
            ]);
        }
        // syncTotal is triggered automatically by FeeStructureItem::saved observer
        return redirect()->route('finance.structures.index')
            ->with('status', 'Fee structure "' . $structure->name . '" created.');
    }

    public function structuresEdit(int $id)
    {
        $this->authorize('create invoices');
        $structure  = FeeStructure::with('items.feeCategory')->findOrFail($id);
        $sessions   = SchoolSession::latest()->get();
        $classes    = SchoolClass::orderBy('name')->get();
        $programs   = Program::orderBy('name')->get();
        $terms      = Term::orderBy('name')->get();
        $categories = FeeCategory::where('is_active', true)->orderBy('sort_order')->get();
        return view('finance.structures.edit', compact('structure', 'sessions', 'classes', 'programs', 'terms', 'categories'));
    }

    public function structuresUpdate(Request $request, int $id)
    {
        $this->authorize('create invoices');
        $structure = FeeStructure::findOrFail($id);
        $data = $request->validate([
            'name'       => 'required|string|max:200',
            'session_id' => 'nullable|exists:school_sessions,id',
            'class_id'   => 'nullable|exists:school_classes,id',
            'program_id' => 'nullable|exists:programs,id',
            'term_id'    => 'nullable|exists:terms,id',
            'is_active'  => 'nullable|boolean',
            'notes'      => 'nullable|string|max:1000',
            'items'                    => 'required|array|min:1',
            'items.*.fee_category_id'  => 'required|exists:fee_categories,id',
            'items.*.amount'           => 'required|numeric|min:0',
            'items.*.is_mandatory'     => 'nullable|boolean',
            'items.*.notes'            => 'nullable|string|max:255',
        ]);

        $structure->update([
            'name'       => $data['name'],
            'session_id' => $data['session_id'] ?? null,
            'class_id'   => $data['class_id']   ?? null,
            'program_id' => $data['program_id'] ?? null,
            'term_id'    => $data['term_id']    ?? null,
            'is_active'  => $request->boolean('is_active', true),
            'notes'      => $data['notes']      ?? null,
        ]);

        // Rebuild items
        $structure->items()->delete();
        foreach ($data['items'] as $item) {
            $structure->items()->create([
                'fee_category_id' => $item['fee_category_id'],
                'amount'          => $item['amount'],
                'is_mandatory'    => (bool) ($item['is_mandatory'] ?? true),
                'notes'           => $item['notes'] ?? null,
            ]);
        }

        return redirect()->route('finance.structures.index')
            ->with('status', 'Fee structure updated.');
    }

    public function structuresDestroy(int $id)
    {
        $this->authorize('create invoices');
        FeeStructure::findOrFail($id)->delete();
        return back()->with('status', 'Fee structure deleted.');
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // DISCOUNTS
    // ═══════════════════════════════════════════════════════════════════════════

    public function discountsIndex()
    {
        $this->authorize('view invoices');
        $discounts = FeeDiscount::with(['feeCategory', 'student', 'createdBy'])
            ->latest()->paginate(25);
        return view('finance.discounts.index', compact('discounts'));
    }

    public function discountsCreate()
    {
        $this->authorize('create invoices');
        $categories = FeeCategory::where('is_active', true)->orderBy('name')->get();
        $structures = FeeStructure::where('is_active', true)->orderBy('name')->get();
        $students   = User::role('student')->orderBy('first_name')->get();
        return view('finance.discounts.create', compact('categories', 'structures', 'students'));
    }

    public function discountsStore(Request $request)
    {
        $this->authorize('create invoices');
        $data = $request->validate([
            'name'              => 'required|string|max:150',
            'type'              => 'required|in:percentage,fixed',
            'value'             => 'required|numeric|min:0',
            'fee_category_id'   => 'nullable|exists:fee_categories,id',
            'student_id'        => 'nullable|exists:users,id',
            'fee_structure_id'  => 'nullable|exists:fee_structures,id',
            'valid_from'        => 'nullable|date',
            'valid_until'       => 'nullable|date|after_or_equal:valid_from',
            'reason'            => 'nullable|string|max:500',
        ]);
        $data['created_by'] = auth()->id();
        FeeDiscount::create($data);
        return redirect()->route('finance.discounts.index')
            ->with('status', 'Discount created.');
    }

    public function discountsUpdate(Request $request, int $id)
    {
        $this->authorize('create invoices');
        $discount = FeeDiscount::findOrFail($id);
        $data = $request->validate([
            'name'             => 'required|string|max:150',
            'type'             => 'required|in:percentage,fixed',
            'value'            => 'required|numeric|min:0',
            'fee_category_id'  => 'nullable|exists:fee_categories,id',
            'student_id'       => 'nullable|exists:users,id',
            'fee_structure_id' => 'nullable|exists:fee_structures,id',
            'valid_from'       => 'nullable|date',
            'valid_until'      => 'nullable|date|after_or_equal:valid_from',
            'is_active'        => 'nullable|boolean',
            'reason'           => 'nullable|string|max:500',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $discount->update($data);
        return back()->with('status', 'Discount updated.');
    }

    public function discountsDestroy(int $id)
    {
        $this->authorize('create invoices');
        FeeDiscount::findOrFail($id)->delete();
        return back()->with('status', 'Discount deleted.');
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // INSTALLMENT PLANS
    // ═══════════════════════════════════════════════════════════════════════════

    public function installmentsIndex()
    {
        $this->authorize('view invoices');
        $plans = InstallmentPlan::with(['student', 'invoice', 'items'])
            ->latest()->paginate(20);
        return view('finance.installments.index', compact('plans'));
    }

    public function installmentsCreate()
    {
        $this->authorize('create invoices');
        $students   = User::role('student')->orderBy('first_name')->get();
        $structures = FeeStructure::where('is_active', true)->orderBy('name')->get();
        return view('finance.installments.create', compact('students', 'structures'));
    }

    public function installmentsStore(Request $request)
    {
        $this->authorize('create invoices');
        $data = $request->validate([
            'name'              => 'required|string|max:200',
            'student_id'        => 'required|exists:users,id',
            'fee_structure_id'  => 'nullable|exists:fee_structures,id',
            'invoice_id'        => 'nullable|exists:invoices,id',
            'total_amount'      => 'required|numeric|min:0.01',
            'late_fee'          => 'nullable|numeric|min:0',
            'installments'      => 'required|array|min:1',
            'installments.*.amount'   => 'required|numeric|min:0.01',
            'installments.*.due_date' => 'required|date',
        ]);

        $plan = InstallmentPlan::create([
            'name'             => $data['name'],
            'student_id'       => $data['student_id'],
            'fee_structure_id' => $data['fee_structure_id'] ?? null,
            'invoice_id'       => $data['invoice_id']       ?? null,
            'total_amount'     => $data['total_amount'],
            'late_fee'         => $data['late_fee'] ?? 0,
            'num_installments' => count($data['installments']),
        ]);

        foreach ($data['installments'] as $i => $inst) {
            $plan->items()->create([
                'installment_no' => $i + 1,
                'amount'         => $inst['amount'],
                'due_date'       => $inst['due_date'],
            ]);
        }

        return redirect()->route('finance.installments.index')
            ->with('status', 'Installment plan created.');
    }

    public function installmentsShow(int $id)
    {
        $this->authorize('view invoices');
        $plan = InstallmentPlan::with(['student', 'invoice', 'items.payment'])->findOrFail($id);
        return view('finance.installments.show', compact('plan'));
    }

    public function installmentsMarkPaid(Request $request, int $itemId)
    {
        $this->authorize('create invoices');
        $item = InstallmentPlanItem::with('installmentPlan')->findOrFail($itemId);

        $data = $request->validate([
            'payment_method'          => 'required|string',
            'transaction_reference'   => 'nullable|string|max:200',
            'paid_date'               => 'required|date',
        ]);

        // Create a payment record linked to the parent invoice
        $plan    = $item->installmentPlan;
        $invoice = $plan->invoice;

        $payment = null;
        if ($invoice) {
            $payment = \App\Models\Payment::create([
                'invoice_id'              => $invoice->id,
                'amount_paid'             => $item->amount + $item->late_fee_charged,
                'payment_date'            => $data['paid_date'],
                'payment_method'          => $data['payment_method'],
                'transaction_reference'   => $data['transaction_reference'] ?? null,
                'received_by'             => auth()->id(),
            ]);
            // Update invoice status
            app(PaymentController::class)->refreshInvoiceStatus($invoice);
        }

        $item->update([
            'status'     => 'paid',
            'paid_date'  => $data['paid_date'],
            'payment_id' => $payment?->id,
        ]);

        return back()->with('status', 'Installment #' . $item->installment_no . ' marked as paid.');
    }

    public function installmentsDestroy(int $id)
    {
        $this->authorize('create invoices');
        InstallmentPlan::findOrFail($id)->delete();
        return back()->with('status', 'Installment plan deleted.');
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // AJAX helpers
    // ═══════════════════════════════════════════════════════════════════════════

    /** Return fee structure items as JSON for auto-fill on invoice create. */
    public function structureItems(int $id)
    {
        $structure = FeeStructure::with('items.feeCategory')->findOrFail($id);
        return response()->json([
            'total'  => $structure->total_amount,
            'items'  => $structure->items->map(fn ($i) => [
                'category' => $i->feeCategory->name,
                'amount'   => $i->amount,
                'mandatory'=> $i->is_mandatory,
            ]),
        ]);
    }
}
