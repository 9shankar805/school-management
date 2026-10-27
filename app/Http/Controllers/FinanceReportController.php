<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\IncomeRecord;
use App\Models\Invoice;
use App\Models\Payment;
use App\Exports\FinanceReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class FinanceReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:manage finance');
    }

    public function form()
    {
        return view('finance.reports');
    }

    public function export(Request $request)
    {
        $data = $request->validate([
            'report_type' => 'required|in:fee_collection,expenses,income,outstanding,ledger',
            'format'      => 'required|in:pdf,excel',
            'date_from'   => 'nullable|date',
            'date_to'     => 'nullable|date|after_or_equal:date_from',
        ]);

        $type     = $data['report_type'];
        $format   = $data['format'];
        $dateFrom = $data['date_from'] ?? null;
        $dateTo   = $data['date_to']   ?? null;

        $records = match ($type) {
            'fee_collection' => Payment::with(['invoice.student'])
                ->when($dateFrom, fn($q) => $q->where('payment_date', '>=', $dateFrom))
                ->when($dateTo,   fn($q) => $q->where('payment_date', '<=', $dateTo))
                ->latest('payment_date')->get(),

            'expenses' => Expense::with('recordedBy')
                ->when($dateFrom, fn($q) => $q->where('expense_date', '>=', $dateFrom))
                ->when($dateTo,   fn($q) => $q->where('expense_date', '<=', $dateTo))
                ->latest('expense_date')->get(),

            'income' => IncomeRecord::with('recordedBy')
                ->when($dateFrom, fn($q) => $q->where('income_date', '>=', $dateFrom))
                ->when($dateTo,   fn($q) => $q->where('income_date', '<=', $dateTo))
                ->latest('income_date')->get(),

            'outstanding' => Invoice::with('student')
                ->where('status', 'unpaid')
                ->when($dateTo, fn($q) => $q->where('due_date', '<=', $dateTo))
                ->orderBy('due_date')->get(),

            'ledger' => collect([
                'income'   => IncomeRecord::when($dateFrom, fn($q) => $q->where('income_date', '>=', $dateFrom))->when($dateTo, fn($q) => $q->where('income_date', '<=', $dateTo))->sum('amount'),
                'fees'     => Payment::when($dateFrom, fn($q) => $q->where('payment_date', '>=', $dateFrom))->when($dateTo, fn($q) => $q->where('payment_date', '<=', $dateTo))->sum('amount'),
                'expenses' => Expense::when($dateFrom, fn($q) => $q->where('expense_date', '>=', $dateFrom))->when($dateTo, fn($q) => $q->where('expense_date', '<=', $dateTo))->sum('amount'),
            ]),
        };

        $title = ucwords(str_replace('_', ' ', $type)) . ' Report';

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('finance.reports-pdf', compact('records', 'type', 'title', 'dateFrom', 'dateTo'))
                      ->setPaper('a4', 'landscape');
            return $pdf->download("finance-{$type}-report.pdf");
        }

        return Excel::download(new FinanceReportExport($records, $type), "finance-{$type}-report.xlsx");
    }

    // PDF & Print receipt for a single payment (Standard A5, Dual-Copy A4, 80mm POS Thermal)
    public function receipt(Request $request, int $paymentId)
    {
        $payment = Payment::with(['invoice.student', 'invoice.items.feeType', 'recordedBy'])->findOrFail($paymentId);
        $school = \App\Models\School::find(
            $payment->invoice?->school_id
            ?? optional(auth()->user())->school_id
            ?? optional($payment->invoice?->student)->school_id
        );

        $format = $request->query('format', 'standard');

        if ($format === 'dual') {
            $pdf = Pdf::loadView('finance.receipt-dual-copy', compact('payment', 'school'))
                      ->setPaper('a4', 'portrait');
            return $pdf->stream("receipt-dual-{$payment->id}.pdf");
        }

        if ($format === 'thermal') {
            return view('finance.receipt-thermal', compact('payment', 'school'));
        }

        $pdf = Pdf::loadView('finance.receipt-pdf', compact('payment', 'school'))
                  ->setPaper('a5');
        return $pdf->stream("receipt-{$payment->id}.pdf");
    }
}
