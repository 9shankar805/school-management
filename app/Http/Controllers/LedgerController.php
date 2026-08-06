<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\IncomeRecord;
use App\Models\LedgerEntry;
use App\Models\Payment;
use App\Models\SalarySlip;
use App\Models\TeacherPayroll;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Accounting Ledger — view journal entries, generate P&L and Balance Sheet.
 */
class LedgerController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'can:manage finance']);
    }

    public function index(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo   = $request->input('date_to',   now()->toDateString());
        $type     = $request->input('type', 'all');

        $query = LedgerEntry::with('createdBy')
            ->whereBetween('entry_date', [$dateFrom, $dateTo])
            ->orderBy('entry_date', 'desc');

        if ($type !== 'all') {
            $query->where('type', $type);
        }

        $entries = $query->paginate(30)->withQueryString();

        // Summary totals for the date range
        $totalDebit  = LedgerEntry::whereBetween('entry_date', [$dateFrom, $dateTo])->sum('debit');
        $totalCredit = LedgerEntry::whereBetween('entry_date', [$dateFrom, $dateTo])->sum('credit');

        return view('finance.ledger', compact('entries', 'dateFrom', 'dateTo', 'type', 'totalDebit', 'totalCredit'));
    }

    /**
     * Auto-sync ledger from all source tables (idempotent).
     * Deletes existing entries for the period and rebuilds from scratch.
     */
    public function sync(Request $request)
    {
        $year  = (int) $request->input('year',  now()->year);
        $month = (int) $request->input('month', now()->month);

        // Delete existing entries for this month
        LedgerEntry::whereYear('entry_date', $year)->whereMonth('entry_date', $month)->delete();

        $entries = [];

        // Fee payments → income (credit)
        Payment::whereYear('payment_date', $year)->whereMonth('payment_date', $month)
            ->each(function ($p) use (&$entries) {
                $entries[] = [
                    'entry_date'     => $p->payment_date,
                    'type'           => 'fee_payment',
                    'account'        => 'Tuition & Fee Income',
                    'debit'          => 0,
                    'credit'         => $p->amount ?? $p->amount_paid,
                    'balance'        => 0,
                    'reference_type' => Payment::class,
                    'reference_id'   => $p->id,
                    'description'    => 'Fee payment #' . $p->id,
                    'created_by'     => auth()->id(),
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ];
            });

        // Expenses → debit
        Expense::whereYear('expense_date', $year)->whereMonth('expense_date', $month)
            ->each(function ($e) use (&$entries) {
                $entries[] = [
                    'entry_date'     => $e->expense_date,
                    'type'           => 'expense',
                    'account'        => $e->category ?? 'General Expenses',
                    'debit'          => $e->amount,
                    'credit'         => 0,
                    'balance'        => 0,
                    'reference_type' => Expense::class,
                    'reference_id'   => $e->id,
                    'description'    => $e->description ?? $e->title ?? 'Expense',
                    'created_by'     => auth()->id(),
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ];
            });

        // Other income → credit
        IncomeRecord::whereYear('income_date', $year)->whereMonth('income_date', $month)
            ->each(function ($i) use (&$entries) {
                $entries[] = [
                    'entry_date'     => $i->income_date,
                    'type'           => 'income',
                    'account'        => $i->category ?? 'Other Income',
                    'debit'          => 0,
                    'credit'         => $i->amount,
                    'balance'        => 0,
                    'reference_type' => IncomeRecord::class,
                    'reference_id'   => $i->id,
                    'description'    => $i->description ?? 'Income',
                    'created_by'     => auth()->id(),
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ];
            });

        // Teacher payrolls (paid) → expense
        TeacherPayroll::where('status', 'paid')
            ->whereYear('payment_date', $year)->whereMonth('payment_date', $month)
            ->each(function ($p) use (&$entries) {
                $entries[] = [
                    'entry_date'     => $p->payment_date,
                    'type'           => 'payroll',
                    'account'        => 'Staff Salaries',
                    'debit'          => $p->net_salary,
                    'credit'         => 0,
                    'balance'        => 0,
                    'reference_type' => TeacherPayroll::class,
                    'reference_id'   => $p->id,
                    'description'    => "Teacher payroll #{$p->id}",
                    'created_by'     => auth()->id(),
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ];
            });

        // Staff salary slips (paid) → expense
        SalarySlip::where('status', 'paid')
            ->whereYear('payment_date', $year)->whereMonth('payment_date', $month)
            ->each(function ($s) use (&$entries) {
                $entries[] = [
                    'entry_date'     => $s->payment_date,
                    'type'           => 'payroll',
                    'account'        => 'Staff Salaries',
                    'debit'          => $s->net_salary,
                    'credit'         => 0,
                    'balance'        => 0,
                    'reference_type' => SalarySlip::class,
                    'reference_id'   => $s->id,
                    'description'    => "Staff salary slip #{$s->id}",
                    'created_by'     => auth()->id(),
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ];
            });

        // Insert all at once, compute running balance
        $balance  = 0;
        $schoolId = config('tenant.school_id');
        foreach ($entries as &$entry) {
            $balance           += $entry['credit'] - $entry['debit'];
            $entry['balance']   = $balance;
            if ($schoolId) {
                $entry['school_id'] = $schoolId;
            }
        }

        LedgerEntry::insert($entries);

        return back()->with('status', 'Ledger synced for ' . Carbon::create($year, $month)->format('F Y') . '.');
    }

    /** Profit & Loss summary view */
    public function profitLoss(Request $request)
    {
        $year      = (int) $request->input('year', now()->year);
        $dateFrom  = Carbon::create($year)->startOfYear()->toDateString();
        $dateTo    = Carbon::create($year)->endOfYear()->toDateString();

        $income    = Payment::whereBetween('payment_date', [$dateFrom, $dateTo])->sum('amount');
        $otherInc  = IncomeRecord::whereBetween('income_date', [$dateFrom, $dateTo])->sum('amount');
        $expenses  = Expense::whereBetween('expense_date', [$dateFrom, $dateTo])->sum('amount');
        $payroll   = TeacherPayroll::where('status', 'paid')->whereBetween('payment_date', [$dateFrom, $dateTo])->sum('net_salary');
        $staffPay  = SalarySlip::where('status', 'paid')->whereBetween('payment_date', [$dateFrom, $dateTo])->sum('net_salary');

        $totalIncome  = $income + $otherInc;
        $totalExpenses = $expenses + $payroll + $staffPay;
        $netProfit     = $totalIncome - $totalExpenses;

        // Monthly breakdown for chart
        $monthlyData = [];
        for ($m = 1; $m <= 12; $m++) {
            $mFrom = Carbon::create($year, $m)->startOfMonth()->toDateString();
            $mTo   = Carbon::create($year, $m)->endOfMonth()->toDateString();
            $monthlyData[] = [
                'month'    => Carbon::create($year, $m)->format('M'),
                'income'   => Payment::whereBetween('payment_date', [$mFrom, $mTo])->sum('amount')
                            + IncomeRecord::whereBetween('income_date', [$mFrom, $mTo])->sum('amount'),
                'expenses' => Expense::whereBetween('expense_date', [$mFrom, $mTo])->sum('amount')
                            + TeacherPayroll::where('status', 'paid')->whereBetween('payment_date', [$mFrom, $mTo])->sum('net_salary')
                            + SalarySlip::where('status', 'paid')->whereBetween('payment_date', [$mFrom, $mTo])->sum('net_salary'),
            ];
        }

        return view('finance.profit-loss', compact(
            'year', 'totalIncome', 'totalExpenses', 'netProfit',
            'income', 'otherInc', 'expenses', 'payroll', 'staffPay', 'monthlyData'
        ));
    }

    /** Generate P&L PDF */
    public function profitLossPdf(Request $request)
    {
        $year     = (int) $request->input('year', now()->year);
        $dateFrom = Carbon::create($year)->startOfYear()->toDateString();
        $dateTo   = Carbon::create($year)->endOfYear()->toDateString();

        $income    = Payment::whereBetween('payment_date', [$dateFrom, $dateTo])->sum('amount');
        $otherInc  = IncomeRecord::whereBetween('income_date', [$dateFrom, $dateTo])->sum('amount');
        $expenses  = Expense::whereBetween('expense_date', [$dateFrom, $dateTo])->sum('amount');
        $payroll   = TeacherPayroll::where('status', 'paid')->whereBetween('payment_date', [$dateFrom, $dateTo])->sum('net_salary');
        $staffPay  = SalarySlip::where('status', 'paid')->whereBetween('payment_date', [$dateFrom, $dateTo])->sum('net_salary');
        $totalIncome   = $income + $otherInc;
        $totalExpenses = $expenses + $payroll + $staffPay;
        $netProfit     = $totalIncome - $totalExpenses;

        $pdf = Pdf::loadView('finance.profit-loss-pdf', compact(
            'year', 'totalIncome', 'totalExpenses', 'netProfit',
            'income', 'otherInc', 'expenses', 'payroll', 'staffPay'
        ))->setPaper('A4');

        return $pdf->download("profit-loss-{$year}.pdf");
    }
}
