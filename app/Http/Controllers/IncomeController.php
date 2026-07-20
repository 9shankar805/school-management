<?php

namespace App\Http\Controllers;

use App\Models\AccountingLedger;
use App\Models\IncomeEntry;
use App\Models\Invoice;
use Illuminate\Http\Request;

class IncomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $this->authorize('view invoices');

        $query = IncomeEntry::with('createdBy', 'invoice')->latest('income_date');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('from')) {
            $query->whereDate('income_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('income_date', '<=', $request->to);
        }

        $entries    = $query->paginate(25)->withQueryString();
        $categories = IncomeEntry::CATEGORIES;
        $totalMonth = IncomeEntry::whereMonth('income_date', now()->month)
            ->whereYear('income_date', now()->year)
            ->sum('amount');

        return view('finance.income.index', compact('entries', 'categories', 'totalMonth'));
    }

    public function create()
    {
        $this->authorize('create invoices');
        $invoices   = Invoice::where('status', '!=', 'paid')->with('student')->latest()->get();
        $categories = IncomeEntry::CATEGORIES;
        return view('finance.income.create', compact('invoices', 'categories'));
    }

    public function store(Request $request)
    {
        $this->authorize('create invoices');

        $data = $request->validate([
            'title'          => 'required|string|max:200',
            'category'       => 'required|string',
            'amount'         => 'required|numeric|min:0.01',
            'income_date'    => 'required|date',
            'payment_method' => 'required|string',
            'reference_no'   => 'nullable|string|max:100',
            'source'         => 'nullable|string|max:200',
            'description'    => 'nullable|string|max:1000',
            'invoice_id'     => 'nullable|exists:invoices,id',
        ]);

        $data['created_by'] = auth()->id();
        $entry = IncomeEntry::create($data);

        // Record credit entry in ledger
        AccountingLedger::record(
            type:          'credit',
            amount:        (float) $data['amount'],
            description:   $data['title'],
            date:          $data['income_date'],
            referenceType: 'IncomeEntry',
            referenceId:   $entry->id,
            category:      $data['category'],
            createdBy:     auth()->id(),
        );

        return redirect()->route('finance.income.index')
            ->with('status', 'Income entry recorded.');
    }

    public function edit(int $id)
    {
        $this->authorize('create invoices');
        $entry      = IncomeEntry::findOrFail($id);
        $invoices   = Invoice::with('student')->latest()->get();
        $categories = IncomeEntry::CATEGORIES;
        return view('finance.income.edit', compact('entry', 'invoices', 'categories'));
    }

    public function update(Request $request, int $id)
    {
        $this->authorize('create invoices');
        $entry = IncomeEntry::findOrFail($id);

        $data = $request->validate([
            'title'          => 'required|string|max:200',
            'category'       => 'required|string',
            'amount'         => 'required|numeric|min:0.01',
            'income_date'    => 'required|date',
            'payment_method' => 'required|string',
            'reference_no'   => 'nullable|string|max:100',
            'source'         => 'nullable|string|max:200',
            'description'    => 'nullable|string|max:1000',
            'invoice_id'     => 'nullable|exists:invoices,id',
        ]);

        $entry->update($data);
        return redirect()->route('finance.income.index')->with('status', 'Income entry updated.');
    }

    public function destroy(int $id)
    {
        $this->authorize('create invoices');
        IncomeEntry::findOrFail($id)->delete();
        return back()->with('status', 'Income entry deleted.');
    }
}
