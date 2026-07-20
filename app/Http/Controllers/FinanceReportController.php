<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\IncomeEntry;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\SchoolSession;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FinanceReportExport;

class FinanceReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ── Report selection form ─────────────────────────────────────────────────
    public function index()
    {
        $this->authorize('view invoices');
        $sessions = SchoolSession::latest()->get();
        $classes  = SchoolClass::orderBy('name')->get();
        return view('finance.reports.index', compact('sessions', 'classes'));
    }

    // ── Fee Collection Report ─────────────────────────────────────────────────
    public function feeCollection(Request $request)
    {
        $this->authorize('view invoices');
        [$from, $to, $data] = $this->buildFeeCollectionData($request);

        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView('finance.reports.fee-collection-pdf', array_merge($data, compact('from', 'to')))
                ->setPaper('A4', 'landscape')
                ->setOptions(['dpi' => 110, 'defaultFont' => 'sans-serif']);
            return $pdf->stream("fee-collection-{$from}-to-{$to}.pdf");
        }

        if ($request->format === 'excel') {
            return Excel::download(
                new FinanceReportExport('fee_collection', array_merge($data, compact('from', 'to'))),
                "fee-collection-{$from}-to-{$to}.xlsx"
            );
        }

        return view('finance.reports.fee-collection', array_merge($data, compact('from', 'to')));
    }

    // ── Outstanding Fees Report ───────────────────────────────────────────────
    public function outstanding(Request $request)
    {
        $this->authorize('view invoices');

        $sessionId = $request->input('session_id');
        $classId   = $request->input('class_id');

        $query = Invoice::with(['student', 'payments'])
            ->whereIn('status', ['unpaid', 'partial'])
            ->whereNull('deleted_at');

        if ($sessionId) {
            $query->where('session_id', $sessionId);
        }

        $invoices = $query->latest()->get()->map(function ($inv) {
            $inv->balance_due = $inv->balance_due;
            return $inv;
        });

        $totalOutstanding = $invoices->sum('balance_due');

        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView('finance.reports.outstanding-pdf', compact('invoices', 'totalOutstanding'))
                ->setPaper('A4')
                ->setOptions(['dpi' => 110, 'defaultFont' => 'sans-serif']);
            return $pdf->stream('outstanding-fees.pdf');
        }

        $sessions = SchoolSession::latest()->get();
        $classes  = SchoolClass::orderBy('name')->get();

        return view('finance.reports.outstanding',
            compact('invoices', 'totalOutstanding', 'sessions', 'classes', 'sessionId', 'classId')
        );
    }

    // ── Expense Report ────────────────────────────────────────────────────────
    public function expenseReport(Request $request)
    {
        $this->authorize('view invoices');

        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to   = $request->input('to',   now()->toDateString());

        $expenses = Expense::with('createdBy')
            ->where('status', 'approved')
            ->whereBetween('expense_date', [$from, $to])
            ->orderBy('expense_date')
            ->get();

        $byCategory = $expenses->groupBy('category')->map->sum('amount');
        $total      = $expenses->sum('amount');

        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView('finance.reports.expense-pdf',
                compact('expenses', 'byCategory', 'total', 'from', 'to'))
                ->setPaper('A4')
                ->setOptions(['dpi' => 110, 'defaultFont' => 'sans-serif']);
            return $pdf->stream("expense-report-{$from}-to-{$to}.pdf");
        }

        return view('finance.reports.expenses', compact('expenses', 'byCategory', 'total', 'from', 'to'));
    }

    // ── Income Report ─────────────────────────────────────────────────────────
    public function incomeReport(Request $request)
    {
        $this->authorize('view invoices');

        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to   = $request->input('to',   now()->toDateString());

        $feePayments = Payment::with('invoice.student')
            ->whereBetween('payment_date', [$from, $to])
            ->whereNull('deleted_at')
            ->get();

        $otherIncome = IncomeEntry::whereBetween('income_date', [$from, $to])
            ->whereNull('deleted_at')
            ->get();

        $totalFees   = $feePayments->sum('amount_paid');
        $totalOther  = $otherIncome->sum('amount');
        $totalIncome = $totalFees + $totalOther;

        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView('finance.reports.income-pdf',
                compact('feePayments', 'otherIncome', 'totalFees', 'totalOther', 'totalIncome', 'from', 'to'))
                ->setPaper('A4', 'landscape')
                ->setOptions(['dpi' => 110, 'defaultFont' => 'sans-serif']);
            return $pdf->stream("income-report-{$from}-to-{$to}.pdf");
        }

        return view('finance.reports.income',
            compact('feePayments', 'otherIncome', 'totalFees', 'totalOther', 'totalIncome', 'from', 'to'));
    }

    // ── Private helpers ───────────────────────────────────────────────────────
    private function buildFeeCollectionData(Request $request): array
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to   = $request->input('to',   now()->toDateString());

        $payments = Payment::with(['invoice.student', 'receivedBy'])
            ->whereBetween('payment_date', [$from, $to])
            ->whereNull('deleted_at')
            ->orderBy('payment_date')
            ->get();

        $byMethod  = $payments->groupBy('payment_method')->map->sum('amount_paid');
        $total     = $payments->sum('amount_paid');
        $invoices  = Invoice::whereNull('deleted_at')
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->get();

        return [
            $from,
            $to,
            compact('payments', 'byMethod', 'total', 'invoices'),
        ];
    }
}
