<?php

namespace App\Http\Controllers;

use App\Models\IncomeRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IncomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:manage finance');
    }

    public function index(Request $request)
    {
        $query = IncomeRecord::with('recordedBy');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%");
            });
        }
        if ($cat = $request->input('category')) {
            $query->where('category', $cat);
        }
        if ($from = $request->input('date_from')) {
            $query->where('income_date', '>=', $from);
        }
        if ($to = $request->input('date_to')) {
            $query->where('income_date', '<=', $to);
        }

        $records        = $query->latest('income_date')->paginate(25)->withQueryString();
        $categories     = IncomeRecord::select('category')->distinct()->pluck('category')->sort()->values();
        $thisMonthTotal = IncomeRecord::forMonth(now()->year, now()->month)->sum('amount');

        return view('finance.income.index', compact('records', 'categories', 'thisMonthTotal'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'          => 'required|string|max:200',
            'category'       => 'required|string|max:100',
            'amount'         => 'required|numeric|min:0.01',
            'income_date'    => 'required|date',
            'payment_method' => 'required|in:cash,bank,cheque',
            'reference'      => 'nullable|string|max:100',
            'notes'          => 'nullable|string|max:1000',
            'attachment'     => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('income', 'public');
        }

        $data['recorded_by'] = auth()->id();
        IncomeRecord::create($data);

        return back()->with('status', 'Income record added.');
    }

    public function update(Request $request, int $id)
    {
        $record = IncomeRecord::findOrFail($id);
        $data   = $request->validate([
            'title'          => 'required|string|max:200',
            'category'       => 'required|string|max:100',
            'amount'         => 'required|numeric|min:0.01',
            'income_date'    => 'required|date',
            'payment_method' => 'required|in:cash,bank,cheque',
            'reference'      => 'nullable|string|max:100',
            'notes'          => 'nullable|string|max:1000',
        ]);
        $record->update($data);
        return back()->with('status', 'Income record updated.');
    }

    public function destroy(int $id)
    {
        $record = IncomeRecord::findOrFail($id);
        if ($record->attachment) Storage::disk('public')->delete($record->attachment);
        $record->delete();
        return back()->with('status', 'Income record deleted.');
    }
}
