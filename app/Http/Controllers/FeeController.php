<?php

namespace App\Http\Controllers;

use App\Models\FeeCategory;
use App\Models\FeeDiscount;
use App\Models\FeeStructure;
use App\Models\SchoolClass;
use App\Models\SchoolSession;
use App\Models\StudentFeeDiscount;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\StudentLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:manage finance');
    }

    // ── Fee Categories ────────────────────────────────────────────────────────

    public function categories()
    {
        $categories = FeeCategory::withCount('feeStructures')->orderBy('name')->get();
        $warningFeeHeads = $categories->filter(function ($cat) {
            return !FeeCategory::isLegalHeadName($cat->name);
        })->values();
        return view('finance.fee-categories', compact('categories', 'warningFeeHeads'));
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:fee_categories,name',
            'code'        => 'nullable|string|max:20|unique:fee_categories,code',
            'description' => 'nullable|string|max:500',
        ]);
        FeeCategory::create(array_merge($data, ['is_active' => true]));
        $redirect = back()->with('status', 'Fee category created.');
        if (!FeeCategory::isLegalHeadName($data['name'])) {
            $redirect->with('info', "Note: category '{$data['name']}' is not one of Nepal's 11 legal fee heads (Tuition/Annual/Admission/Deposit/Exam/Computer/Special/Hostel/Meal/Transport/Tour). This may trigger audit findings by Local Government.");
        }
        return $redirect;
    }

    public function updateCategory(Request $request, int $id)
    {
        $cat = FeeCategory::findOrFail($id);
        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:fee_categories,name,' . $id,
            'code'        => 'nullable|string|max:20|unique:fee_categories,code,' . $id,
            'description' => 'nullable|string|max:500',
            'is_active'   => 'boolean',
        ]);
        $cat->update(array_merge($data, ['is_active' => $request->boolean('is_active')]));
        $redirect = back()->with('status', 'Category updated.');
        if (!FeeCategory::isLegalHeadName($data['name'])) {
            $redirect->with('info', "Note: category '{$data['name']}' is not one of Nepal's 11 legal fee heads (Tuition/Annual/Admission/Deposit/Exam/Computer/Special/Hostel/Meal/Transport/Tour). This may trigger audit findings by Local Government.");
        }
        return $redirect;
    }

    public function destroyCategory(int $id)
    {
        $cat = FeeCategory::withCount('feeStructures')->findOrFail($id);
        if ($cat->fee_structures_count > 0) {
            return back()->with('error', 'Cannot delete: category has ' . $cat->fee_structures_count . ' fee structure(s).');
        }
        $cat->delete();
        return back()->with('status', 'Category deleted.');
    }

    // ── Fee Structures ────────────────────────────────────────────────────────

    public function structures(Request $request)
    {
        $query = FeeStructure::with(['category', 'session', 'schoolClass']);

        if ($sessionId = $request->input('session_id')) {
            $query->where('session_id', $sessionId);
        }
        if ($classId = $request->input('class_id')) {
            $query->where('class_id', $classId);
        }

        $structures = $query->orderBy('name')->paginate(20)->withQueryString();
        $categories = FeeCategory::active()->orderBy('name')->get();
        $feeTypes   = \App\Models\FeeType::active()->orderBy('name')->get();
        $sessions   = SchoolSession::orderByDesc('id')->get();
        $classes    = SchoolClass::orderBy('class_name')->get();

        return view('finance.fee-structures', compact('structures', 'categories', 'feeTypes', 'sessions', 'classes'));
    }

    public function storeStructure(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:150',
            'fee_type_id' => 'nullable|integer|exists:fee_types,id',
            'session_id'  => 'nullable|integer|exists:school_sessions,id',
            'class_id'    => 'nullable|integer|exists:school_classes,id',
            'category_id' => 'required|integer|exists:fee_categories,id',
            'amount'      => 'required|numeric|min:0',
            'frequency'   => 'required|in:once,monthly,quarterly,half_yearly,yearly',
            'due_date'    => 'nullable|date',
            'is_taxable'  => 'boolean',
            'tax_rate'    => 'nullable|numeric|min:0|max:100',
            'applies_to'  => 'nullable|in:all,day_scholar,hostel,transport',
            'description' => 'nullable|string',
        ]);

        FeeStructure::create(array_merge($data, [
            'is_active'  => true,
            'is_taxable' => $request->boolean('is_taxable'),
            'tax_rate'   => $data['tax_rate'] ?? 0,
            'applies_to' => $data['applies_to'] ?? 'all',
        ]));
        return back()->with('status', 'Fee structure created.');
    }

    public function updateStructure(Request $request, int $id)
    {
        $structure = FeeStructure::findOrFail($id);
        $data = $request->validate([
            'name'        => 'required|string|max:150',
            'fee_type_id' => 'nullable|integer|exists:fee_types,id',
            'session_id'  => 'nullable|integer|exists:school_sessions,id',
            'class_id'    => 'nullable|integer|exists:school_classes,id',
            'category_id' => 'required|integer|exists:fee_categories,id',
            'amount'      => 'required|numeric|min:0',
            'frequency'   => 'required|in:once,monthly,quarterly,half_yearly,yearly',
            'due_date'    => 'nullable|date',
            'is_active'   => 'boolean',
            'is_taxable'  => 'boolean',
            'tax_rate'    => 'nullable|numeric|min:0|max:100',
        ]);
        $structure->update(array_merge($data, [
            'is_active'  => $request->boolean('is_active'),
            'is_taxable' => $request->boolean('is_taxable'),
        ]));
        return back()->with('status', 'Fee structure updated.');
    }

    public function destroyStructure(int $id)
    {
        $structure = FeeStructure::findOrFail($id);

        // Prevent deletion if invoices have been generated from this structure
        $invoiceCount = \App\Models\InvoiceItem::where('fee_structure_id', $id)->count();
        if ($invoiceCount > 0) {
            return back()->with('error',
                "Cannot delete: {$invoiceCount} invoice line item(s) reference this structure. Deactivate it instead."
            );
        }

        $structure->delete();
        return back()->with('status', 'Fee structure deleted.');
    }

    // ── Fee Types (master lookup) ─────────────────────────────────────────────

    public function feeTypes()
    {
        $feeTypes   = \App\Models\FeeType::with('category')->orderBy('name')->paginate(20);
        $categories = FeeCategory::active()->orderBy('name')->get();
        return view('finance.fee-types', compact('feeTypes', 'categories'));
    }

    public function storeFeeType(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:fee_types,name',
            'code'        => 'nullable|string|max:20|unique:fee_types,code',
            'category_id' => 'nullable|integer|exists:fee_categories,id',
            'frequency'   => 'required|in:once,monthly,quarterly,half_yearly,yearly',
            'trigger'     => 'required|in:manual,admission,monthly,exam_publish,annual',
            'is_taxable'  => 'boolean',
            'tax_rate'    => 'nullable|numeric|min:0|max:100',
            'description' => 'nullable|string|max:500',
        ]);
        \App\Models\FeeType::create(array_merge($data, [
            'is_active'  => true,
            'is_taxable' => $request->boolean('is_taxable'),
            'tax_rate'   => $data['tax_rate'] ?? 0,
        ]));
        return back()->with('status', 'Fee type created.');
    }

    public function updateFeeType(Request $request, int $id)
    {
        \App\Models\FeeType::findOrFail($id)->update($request->validate([
            'name'       => 'required|string|max:100',
            'frequency'  => 'required|in:once,monthly,quarterly,half_yearly,yearly',
            'trigger'    => 'required|in:manual,admission,monthly,exam_publish,annual',
            'is_taxable' => 'boolean',
            'tax_rate'   => 'nullable|numeric|min:0|max:100',
            'is_active'  => 'boolean',
        ]) + ['is_active' => $request->boolean('is_active'), 'is_taxable' => $request->boolean('is_taxable')]);
        return back()->with('status', 'Fee type updated.');
    }

    public function destroyFeeType(int $id)
    {
        \App\Models\FeeType::findOrFail($id)->delete();
        return back()->with('status', 'Fee type deleted.');
    }

    // ── Discounts ─────────────────────────────────────────────────────────────

    public function discounts()
    {
        $discounts = FeeDiscount::withCount('studentDiscounts')->orderBy('name')->get();
        $students  = User::role('student')->orderBy('first_name')->get();
        $sessions  = SchoolSession::orderByDesc('id')->take(5)->get();
        $studentDiscounts = StudentFeeDiscount::with(['student', 'discount', 'session'])->latest()->get();
        return view('finance.fee-discounts', compact('discounts', 'students', 'sessions', 'studentDiscounts'));
    }

    public function storeDiscount(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'type'        => 'required|in:percentage,fixed',
            'value'       => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500',
        ]);
        FeeDiscount::create(array_merge($data, ['is_active' => true]));
        return back()->with('status', 'Discount created.');
    }

    public function updateDiscount(Request $request, int $id)
    {
        FeeDiscount::findOrFail($id)->update($request->validate([
            'name'      => 'required|string|max:100',
            'type'      => 'required|in:percentage,fixed',
            'value'     => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]) + ['is_active' => $request->boolean('is_active')]);
        return back()->with('status', 'Discount updated.');
    }

    public function destroyDiscount(int $id)
    {
        FeeDiscount::findOrFail($id)->delete();
        return back()->with('status', 'Discount deleted.');
    }

    public function assignDiscount(Request $request)
    {
        $data = $request->validate([
            'student_id'  => 'required|integer|exists:users,id',
            'discount_id' => 'required|integer|exists:fee_discounts,id',
            'session_id'  => 'nullable|integer|exists:school_sessions,id',
            'valid_from'  => 'nullable|date',
            'valid_to'    => 'nullable|date|after_or_equal:valid_from',
            'notes'       => 'nullable|string|max:500',
        ]);
        StudentFeeDiscount::updateOrCreate(
            ['student_id' => $data['student_id'], 'discount_id' => $data['discount_id'], 'session_id' => $data['session_id']],
            $data
        );
        return back()->with('status', 'Discount assigned to student.');
    }

    public function revokeDiscount(int $id)
    {
        StudentFeeDiscount::findOrFail($id)->delete();
        return back()->with('status', 'Discount revoked.');
    }

    // ── Finance Overview (dashboard) ──────────────────────────────────────────

    public function overview()
    {
        try {
            $this->middleware('can:manage finance');

            $totalInvoiced  = \App\Models\Invoice::sum('amount');
            $totalCollected = \App\Models\Payment::sum('amount');
            $totalDue       = $totalInvoiced - $totalCollected;
            $overdueCount   = \App\Models\Invoice::where('status', 'unpaid')
                                ->where('due_date', '<', today())->count();

            // Monthly fee collection (12 months)
            $monthlyCollection = \App\Models\Payment::selectRaw("DATE_FORMAT(payment_date,'%Y-%m') as month, SUM(amount) as total")
                ->where('payment_date', '>=', now()->subMonths(12))
                ->groupBy('month')->orderBy('month')
                ->pluck('total', 'month');

            // Expense breakdown (last 30 days)
            $expenseByCategory = \App\Models\Expense::selectRaw('category, SUM(amount) as total')
                ->where('expense_date', '>=', now()->subDays(30))
                ->groupBy('category')->pluck('total', 'category');

            $totalExpense = \App\Models\Expense::where('expense_date', '>=', now()->startOfMonth())->sum('amount');
            $totalIncome  = \App\Models\IncomeRecord::where('income_date', '>=', now()->startOfMonth())->sum('amount');

            $recentPayments  = \App\Models\Payment::with('invoice.student')->latest()->take(10)->get();
            $overdueInvoices = \App\Models\Invoice::with('student')
                ->where('status', 'unpaid')->where('due_date', '<', today())
                ->orderBy('due_date')->take(10)->get();

            return view('finance.overview', compact(
                'totalInvoiced', 'totalCollected', 'totalDue', 'overdueCount',
                'monthlyCollection', 'expenseByCategory',
                'totalExpense', 'totalIncome',
                'recentPayments', 'overdueInvoices'
            ));
        } catch (\Exception $e) {
            return view('finance.overview', [
                'totalInvoiced' => 0,
                'totalCollected' => 0,
                'totalDue' => 0,
                'overdueCount' => 0,
                'monthlyCollection' => collect(),
                'expenseByCategory' => collect(),
                'totalExpense' => 0,
                'totalIncome' => 0,
                'recentPayments' => collect(),
                'overdueInvoices' => collect(),
                'error' => 'Unable to load finance overview: ' . $e->getMessage()
            ]);
        }
    }

    // ── Fee Profiles (Student Fee Profile / Inkwelly SaaS View) ───────────────

    public function feeProfiles(Request $request, ?int $studentId = null)
    {
        try {
            $studentQuery = User::role('student')->with(['promotions.schoolClass', 'promotions.section']);

            $selectedStudent = null;
            if ($studentId) {
                $selectedStudent = $studentQuery->find($studentId);
            } elseif ($request->filled('student_id')) {
                $selectedStudent = $studentQuery->find($request->input('student_id'));
            }

            if (!$selectedStudent) {
                // Find student with existing invoices first, or first available student
                $studentWithInvoices = Invoice::whereNotNull('student_id')->latest()->value('student_id');
                $selectedStudent = $studentWithInvoices ? $studentQuery->find($studentWithInvoices) : $studentQuery->first();
            }

            $allStudents = User::role('student')
                ->select('id', 'first_name', 'last_name', 'admission_no', 'email', 'photo')
                ->with(['promotions.schoolClass', 'promotions.section'])
                ->orderBy('first_name')
                ->take(150)
                ->get();

            if (!$selectedStudent) {
                return view('finance.fee-profiles', [
                    'selectedStudent' => null,
                    'allStudents'     => $allStudents,
                    'invoices'        => collect(),
                    'payments'        => collect(),
                    'ledger'          => collect(),
                    'totalCharged'    => 0,
                    'totalPaid'       => 0,
                    'totalDue'        => 0,
                    'collectionRate'  => 0,
                    'baseFee'         => 0,
                    'fineAmount'      => 0,
                    'discountAmount'  => 0,
                    'pendingInvoicesCount' => 0,
                    'paidInvoicesCount'    => 0,
                    'discountsCount'       => 0,
                    'activeStructures'     => collect(),
                ]);
            }

            $invoices = Invoice::where('student_id', $selectedStudent->id)
                ->with(['items.feeType', 'payments.recordedBy'])
                ->latest()
                ->get();

            $payments = Payment::whereIn('invoice_id', $invoices->pluck('id'))
                ->with(['invoice', 'recordedBy'])
                ->latest()
                ->get();

            $ledger = StudentLedger::where('student_id', $selectedStudent->id)
                ->orderBy('entry_date')
                ->get();

            // Calculate summary metrics
            $totalCharged = (float)$invoices->sum('amount');
            $totalPaid = (float)$payments->sum('amount');
            $totalDue = max(0, $totalCharged - $totalPaid);
            $collectionRate = $totalCharged > 0 ? min(100, round(($totalPaid / $totalCharged) * 100)) : 0;

            $fineAmount = (float)$invoices->sum('fine_amount');
            $discountAmount = (float)$invoices->sum('discount_amount');
            $baseFee = max(0, $totalCharged - $fineAmount);

            $pendingInvoicesCount = $invoices->where('status', '!=', 'paid')->count();
            $paidInvoicesCount = $invoices->where('status', 'paid')->count();
            $discountsCount = StudentFeeDiscount::where('student_id', $selectedStudent->id)->count();

            $classId = $selectedStudent->class_id ?? optional($selectedStudent->promotions->last())->class_id;
            $activeStructures = FeeStructure::with(['category'])
                ->when($classId, fn($q) => $q->where('class_id', $classId))
                ->get();

            return view('finance.fee-profiles', compact(
                'selectedStudent', 'allStudents', 'invoices', 'payments', 'ledger',
                'totalCharged', 'totalPaid', 'totalDue', 'collectionRate',
                'baseFee', 'fineAmount', 'discountAmount',
                'pendingInvoicesCount', 'paidInvoicesCount', 'discountsCount',
                'activeStructures'
            ));
        } catch (\Exception $e) {
            return view('finance.fee-profiles', [
                'selectedStudent' => null,
                'allStudents'     => collect(),
                'invoices'        => collect(),
                'payments'        => collect(),
                'ledger'          => collect(),
                'totalCharged'    => 0,
                'totalPaid'       => 0,
                'totalDue'        => 0,
                'collectionRate'  => 0,
                'baseFee'         => 0,
                'fineAmount'      => 0,
                'discountAmount'  => 0,
                'pendingInvoicesCount' => 0,
                'paidInvoicesCount'    => 0,
                'discountsCount'       => 0,
                'activeStructures'     => collect(),
                'error'           => 'Unable to load fee profile: ' . $e->getMessage(),
            ]);
        }
    }
}
