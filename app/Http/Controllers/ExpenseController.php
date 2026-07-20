<?php

namespace App\Http\Controllers;

use App\Models\AccountingLedger;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $this->authorize('view invoices');

        $query = Expense::with('createdBy', 'approvedBy')->latest('expense_date');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from')) {
            $query->whereDate('expense_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('expense_date', '<=', $request->to);
        }

        $expenses   = $query->paginate(25)->withQueryString();
        $categories = Expense::CATEGORIES;
        $totalMonth = Expense::where('status', 'approved')
            ->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('amount');

        return view('finance.expenses.index', compact('expenses', 'categories', 'totalMonth'));
    }

    public function create()
    {
        $this->authorize('create invoices');
        return view('finance.expenses.create', [
            'categories'     => Expense::CATEGORIES,
            'paymentMethods' => Expense::PAYMENT_METHODS,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create invoices');

        $data = $request->validate([
            'title'          => 'required|string|max:200',
            'category'       => 'required|string',
            'amount'         => 'required|numeric|min:0.01',
            'expense_date'   => 'required|date',
            'payment_method' => 'required|string',
            'reference_no'   => 'nullable|string|max:100',
            'vendor'         => 'nullable|string|max:200',
            'description'    => 'nullable|string|max:1000',
            'receipt'        => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($request->hasFile('receipt')) {
            $data['receipt_path'] = $request->file('receipt')
                ->store('expenses/receipts', 'public');
        }

        $data['created_by']  = auth()->id();
        $data['approved_by'] = auth()->id();    // auto-approve; can add workflow later
        $data['status']      = 'approved';

        $expense = Expense::create($data);

        // Record debit entry in ledger
        AccountingLedger::record(
            type:          'debit',
            amount:        (float) $data['amount'],
            description:   $data['title'],
            date:          $data['expense_date'],
            referenceType: 'Expense',
            referenceId:   $expense->id,
            category:      $data['category'],
            createdBy:     auth()->id(),
        );

        return redirect()->route('finance.expenses.index')
            ->with('status', 'Expense recorded successfully.');
    }

    public function edit(int $id)
    {
        $this->authorize('create invoices');
        $expense = Expense::findOrFail($id);
        return view('finance.expenses.edit', [
            'expense'        => $expense,
            'categories'     => Expense::CATEGORIES,
            'paymentMethods' => Expense::PAYMENT_METHODS,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $this->authorize('create invoices');
        $expense = Expense::findOrFail($id);

        $data = $request->validate([
            'title'          => 'required|string|max:200',
            'category'       => 'required|string',
            'amount'         => 'required|numeric|min:0.01',
            'expense_date'   => 'required|date',
            'payment_method' => 'required|string',
            'reference_no'   => 'nullable|string|max:100',
            'vendor'         => 'nullable|string|max:200',
            'description'    => 'nullable|string|max:1000',
            'receipt'        => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($request->hasFile('receipt')) {
            if ($expense->receipt_path) {
                Storage::disk('public')->delete($expense->receipt_path);
            }
            $data['receipt_path'] = $request->file('receipt')
                ->store('expenses/receipts', 'public');
        }

        $expense->update($data);

        return redirect()->route('finance.expenses.index')
            ->with('status', 'Expense updated.');
    }

    public function destroy(int $id)
    {
        $this->authorize('create invoices');
        $expense = Expense::findOrFail($id);
        if ($expense->receipt_path) {
            Storage::disk('public')->delete($expense->receipt_path);
        }
        $expense->delete();
        return back()->with('status', 'Expense deleted.');
    }
}
