<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FinanceReportExport;

class ExpenseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:manage finance');
    }

    public function index(Request $request)
    {
        $query = Expense::with('recordedBy');

        if ($search = $request->input('search')) {
            $query->search($search);
        }
        if ($cat = $request->input('category')) {
            $query->where('category', $cat);
        }
        if ($from = $request->input('date_from')) {
            $query->where('expense_date', '>=', $from);
        }
        if ($to = $request->input('date_to')) {
            $query->where('expense_date', '<=', $to);
        }

        $expenses       = $query->latest('expense_date')->paginate(25)->withQueryString();
        $categories     = Expense::select('category')->distinct()->pluck('category')->sort()->values();
        $totalFiltered  = $query->sum('amount');
        $thisMonthTotal = Expense::forMonth(now()->year, now()->month)->sum('amount');

        return view('finance.expenses.index', compact('expenses', 'categories', 'totalFiltered', 'thisMonthTotal'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'          => 'required|string|max:200',
            'category'       => 'required|string|max:100',
            'amount'         => 'required|numeric|min:0.01',
            'expense_date'   => 'required|date',
            'payment_method' => 'required|in:cash,bank,cheque',
            'reference'      => 'nullable|string|max:100',
            'notes'          => 'nullable|string|max:1000',
            'attachment'     => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('expenses', 'public');
        }

        $data['recorded_by'] = auth()->id();
        Expense::create($data);

        return back()->with('status', 'Expense recorded.');
    }

    public function update(Request $request, int $id)
    {
        $expense = Expense::findOrFail($id);

        $data = $request->validate([
            'title'          => 'required|string|max:200',
            'category'       => 'required|string|max:100',
            'amount'         => 'required|numeric|min:0.01',
            'expense_date'   => 'required|date',
            'payment_method' => 'required|in:cash,bank,cheque',
            'reference'      => 'nullable|string|max:100',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $expense->update($data);
        return back()->with('status', 'Expense updated.');
    }

    public function destroy(int $id)
    {
        $expense = Expense::findOrFail($id);
        if ($expense->attachment) Storage::disk('public')->delete($expense->attachment);
        $expense->delete();
        return back()->with('status', 'Expense deleted.');
    }
}
