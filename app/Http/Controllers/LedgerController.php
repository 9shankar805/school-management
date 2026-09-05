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
                    'credit'         => $p->amount,
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

    /**
     * Balance Sheet — Assets vs Liabilities + Equity snapshot for a given year-end.
     *
     * Assets:
     *   - Cash / Bank (net fee income collected)
     *   - Fixed Assets (from assets table at purchase price)
     *   - Receivables (unpaid invoice outstanding)
     *
     * Liabilities:
     *   - Salaries Payable (unpaid salary slips)
     *   - TDS Payable (accrued, not yet remitted)
     *
     * Equity:
     *   - Retained Earnings = cumulative net profit (all-time P&L)
     */
    public function balanceSheet(Request $request)
    {
        $year    = (int) $request->input('year', now()->year);
        $asOf    = Carbon::create($year)->endOfYear()->toDateString();
        $dateFrom = Carbon::create($year)->startOfYear()->toDateString();

        // ── ASSETS ──────────────────────────────────────────────────────────
        // Cash/Bank: total fees collected up to year-end
        $cashAndBank = Payment::where('payment_date', '<=', $asOf)->sum('amount');

        // Add other income
        $otherIncomeCash = IncomeRecord::where('income_date', '<=', $asOf)->sum('amount');
        $cashAndBank += $otherIncomeCash;

        // Subtract expenses paid (cash outflows)
        $expensesPaid   = Expense::where('expense_date', '<=', $asOf)->sum('amount');
        $payrollPaid    = TeacherPayroll::where('status', 'paid')->where('payment_date', '<=', $asOf)->sum('net_salary');
        $staffPayPaid   = SalarySlip::where('status', 'paid')->where('payment_date', '<=', $asOf)->sum('net_salary');
        $cashAndBank    = max(0, $cashAndBank - $expensesPaid - $payrollPaid - $staffPayPaid);

        // Fixed Assets (at book value — purchase price, no depreciation yet)
        $fixedAssets = \App\Models\Asset::where('purchase_date', '<=', $asOf)
            ->whereIn('status', ['active', 'in_use'])
            ->sum('purchase_price');

        // Accounts Receivable (unpaid invoices outstanding as of year-end)
        $receivables = \App\Models\Invoice::where('status', '!=', 'paid')
            ->where('created_at', '<=', $asOf)
            ->sum('amount');

        $totalAssets = $cashAndBank + $fixedAssets + $receivables;

        // ── LIABILITIES ──────────────────────────────────────────────────────
        // Unpaid salary slips (draft / approved, not yet paid)
        $salariesPayable = SalarySlip::whereIn('status', ['draft', 'approved'])
            ->where('year', '<=', $year)
            ->sum('net_salary');
        $teacherSalariesPayable = TeacherPayroll::where('status', 'pending')
            ->where('year', '<=', $year)
            ->sum('net_salary');

        // TDS payable (estimated at 15% of payroll costs — Nepal flat rate)
        $tdsRate        = 0.15;
        $tdsPayable     = round(($payrollPaid + $staffPayPaid) * $tdsRate, 2);

        $totalLiabilities = $salariesPayable + $teacherSalariesPayable + $tdsPayable;

        // ── EQUITY ────────────────────────────────────────────────────────────
        // Retained earnings = all-time P&L (income - expenses)
        $allTimeIncome    = Payment::sum('amount') + IncomeRecord::sum('amount');
        $allTimeExpenses  = Expense::sum('amount')
                          + TeacherPayroll::where('status', 'paid')->sum('net_salary')
                          + SalarySlip::where('status', 'paid')->sum('net_salary');
        $retainedEarnings = $allTimeIncome - $allTimeExpenses;

        $totalEquity    = $retainedEarnings;
        $balanceCheck   = $totalAssets - ($totalLiabilities + $totalEquity); // should be ~0

        return view('finance.balance-sheet', compact(
            'year', 'asOf',
            'cashAndBank', 'fixedAssets', 'receivables', 'totalAssets',
            'salariesPayable', 'teacherSalariesPayable', 'tdsPayable', 'totalLiabilities',
            'retainedEarnings', 'totalEquity',
            'balanceCheck'
        ));
    }

    /** PDF version of the balance sheet */
    public function balanceSheetPdf(Request $request)
    {
        $year    = (int) $request->input('year', now()->year);
        $asOf    = Carbon::create($year)->endOfYear()->toDateString();

        $cashAndBank    = max(0,
            Payment::where('payment_date', '<=', $asOf)->sum('amount')
            + IncomeRecord::where('income_date', '<=', $asOf)->sum('amount')
            - Expense::where('expense_date', '<=', $asOf)->sum('amount')
            - TeacherPayroll::where('status', 'paid')->where('payment_date', '<=', $asOf)->sum('net_salary')
            - SalarySlip::where('status', 'paid')->where('payment_date', '<=', $asOf)->sum('net_salary')
        );
        $fixedAssets    = \App\Models\Asset::where('purchase_date', '<=', $asOf)->whereIn('status', ['active', 'in_use'])->sum('purchase_price');
        $receivables    = \App\Models\Invoice::where('status', '!=', 'paid')->where('created_at', '<=', $asOf)->sum('amount');
        $totalAssets    = $cashAndBank + $fixedAssets + $receivables;

        $salariesPayable        = SalarySlip::whereIn('status', ['draft', 'approved'])->where('year', '<=', $year)->sum('net_salary');
        $teacherSalariesPayable = TeacherPayroll::where('status', 'pending')->where('year', '<=', $year)->sum('net_salary');
        $payrollPaid            = TeacherPayroll::where('status', 'paid')->where('payment_date', '<=', $asOf)->sum('net_salary')
                                + SalarySlip::where('status', 'paid')->where('payment_date', '<=', $asOf)->sum('net_salary');
        $tdsPayable             = round($payrollPaid * 0.15, 2);
        $totalLiabilities       = $salariesPayable + $teacherSalariesPayable + $tdsPayable;

        $retainedEarnings = (Payment::sum('amount') + IncomeRecord::sum('amount'))
                          - (Expense::sum('amount')
                             + TeacherPayroll::where('status', 'paid')->sum('net_salary')
                             + SalarySlip::where('status', 'paid')->sum('net_salary'));
        $totalEquity  = $retainedEarnings;
        $balanceCheck = $totalAssets - ($totalLiabilities + $totalEquity);

        $pdf = Pdf::loadView('finance.balance-sheet-pdf', compact(
            'year', 'asOf',
            'cashAndBank', 'fixedAssets', 'receivables', 'totalAssets',
            'salariesPayable', 'teacherSalariesPayable', 'tdsPayable', 'totalLiabilities',
            'retainedEarnings', 'totalEquity', 'balanceCheck'
        ))->setPaper('A4');

        return $pdf->download("balance-sheet-{$year}.pdf");
    }
}
