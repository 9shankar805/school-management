<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\IncomeRecord;
use App\Models\LedgerEntry;
use App\Models\Payment;
use App\Models\SalarySlip;
use App\Models\TeacherPayroll;
use App\Models\DayBookClosing;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\NepaliDateService;

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
        $periodType = $request->input('period_type', 'fy'); // 'fy' or 'ad'
        $fyList     = NepaliDateService::getFiscalYearList(7);
        $currentFy  = NepaliDateService::currentFiscalYear();
        $selectedFy = $request->input('fy', $currentFy);
        $year       = (int) $request->input('year', now()->year);

        if ($periodType === 'fy') {
            $fyDetails   = NepaliDateService::getFiscalYearDetails($selectedFy);
            $dateFrom    = $fyDetails['start_ad'];
            $dateTo      = $fyDetails['end_ad'];
            $periodLabel = $fyDetails['fy_np'] . " ({$selectedFy})";
            $periodSub   = "साउन १, " . NepaliDateService::toNepaliDigits($fyDetails['start_year_bs']) . " – असार मसान्त, " . NepaliDateService::toNepaliDigits($fyDetails['end_year_bs']) . " ({$dateFrom} to {$dateTo})";

            // Monthly breakdown for 12 Nepali months (Shrawan -> Ashadh)
            $monthlyData = [];
            $quarterlyData = [
                'Q1' => ['name' => 'प्रथम त्रैमासिक (Q1: श्रावण–आश्विन)', 'income' => 0, 'expenses' => 0, 'net' => 0],
                'Q2' => ['name' => 'दोस्रो त्रैमासिक (Q2: कार्तिक–पौष)', 'income' => 0, 'expenses' => 0, 'net' => 0],
                'Q3' => ['name' => 'तेस्रो त्रैमासिक (Q3: माघ–चैत्र)', 'income' => 0, 'expenses' => 0, 'net' => 0],
                'Q4' => ['name' => 'चौथो त्रैमासिक (Q4: वैशाख–असार)', 'income' => 0, 'expenses' => 0, 'net' => 0],
            ];

            foreach ($fyDetails['months'] as $m) {
                $mFrom = $m['start_ad'];
                $mTo   = $m['end_ad'];

                $mIncome = Payment::whereBetween('payment_date', [$mFrom, $mTo])->sum('amount')
                         + IncomeRecord::whereBetween('income_date', [$mFrom, $mTo])->sum('amount');
                $mExpense = Expense::whereBetween('expense_date', [$mFrom, $mTo])->sum('amount')
                          + TeacherPayroll::where('status', 'paid')->whereBetween('payment_date', [$mFrom, $mTo])->sum('net_salary')
                          + SalarySlip::where('status', 'paid')->whereBetween('payment_date', [$mFrom, $mTo])->sum('net_salary');

                $monthlyData[] = [
                    'month'       => $m['name_np'] . ' (' . $m['name_en'] . ')',
                    'month_short' => $m['name_np'],
                    'quarter'     => $m['quarter'],
                    'quarter_np'  => $m['quarter_np'],
                    'start_ad'    => $mFrom,
                    'end_ad'      => $mTo,
                    'income'      => (float) $mIncome,
                    'expenses'    => (float) $mExpense,
                    'net'         => (float) ($mIncome - $mExpense),
                ];

                if (isset($quarterlyData[$m['quarter']])) {
                    $quarterlyData[$m['quarter']]['income']   += $mIncome;
                    $quarterlyData[$m['quarter']]['expenses'] += $mExpense;
                    $quarterlyData[$m['quarter']]['net']      += ($mIncome - $mExpense);
                }
            }
        } else {
            $fyDetails   = null;
            $dateFrom    = Carbon::create($year)->startOfYear()->toDateString();
            $dateTo      = Carbon::create($year)->endOfYear()->toDateString();
            $periodLabel = "Calendar Year {$year}";
            $periodSub   = "Jan 01, {$year} – Dec 31, {$year}";

            $monthlyData = [];
            $quarterlyData = [
                'Q1' => ['name' => 'Q1 (Jan – Mar)', 'income' => 0, 'expenses' => 0, 'net' => 0],
                'Q2' => ['name' => 'Q2 (Apr – Jun)', 'income' => 0, 'expenses' => 0, 'net' => 0],
                'Q3' => ['name' => 'Q3 (Jul – Sep)', 'income' => 0, 'expenses' => 0, 'net' => 0],
                'Q4' => ['name' => 'Q4 (Oct – Dec)', 'income' => 0, 'expenses' => 0, 'net' => 0],
            ];

            for ($m = 1; $m <= 12; $m++) {
                $mFrom = Carbon::create($year, $m)->startOfMonth()->toDateString();
                $mTo   = Carbon::create($year, $m)->endOfMonth()->toDateString();
                $mIncome = Payment::whereBetween('payment_date', [$mFrom, $mTo])->sum('amount')
                         + IncomeRecord::whereBetween('income_date', [$mFrom, $mTo])->sum('amount');
                $mExpense = Expense::whereBetween('expense_date', [$mFrom, $mTo])->sum('amount')
                          + TeacherPayroll::where('status', 'paid')->whereBetween('payment_date', [$mFrom, $mTo])->sum('net_salary')
                          + SalarySlip::where('status', 'paid')->whereBetween('payment_date', [$mFrom, $mTo])->sum('net_salary');

                $qKey = $m <= 3 ? 'Q1' : ($m <= 6 ? 'Q2' : ($m <= 9 ? 'Q3' : 'Q4'));

                $monthlyData[] = [
                    'month'       => Carbon::create($year, $m)->format('M'),
                    'month_short' => Carbon::create($year, $m)->format('M'),
                    'quarter'     => $qKey,
                    'quarter_np'  => $qKey,
                    'start_ad'    => $mFrom,
                    'end_ad'      => $mTo,
                    'income'      => (float) $mIncome,
                    'expenses'    => (float) $mExpense,
                    'net'         => (float) ($mIncome - $mExpense),
                ];

                $quarterlyData[$qKey]['income']   += $mIncome;
                $quarterlyData[$qKey]['expenses'] += $mExpense;
                $quarterlyData[$qKey]['net']      += ($mIncome - $mExpense);
            }
        }

        $income    = Payment::whereBetween('payment_date', [$dateFrom, $dateTo])->sum('amount');
        $otherInc  = IncomeRecord::whereBetween('income_date', [$dateFrom, $dateTo])->sum('amount');
        $expenses  = Expense::whereBetween('expense_date', [$dateFrom, $dateTo])->sum('amount');
        $payroll   = TeacherPayroll::where('status', 'paid')->whereBetween('payment_date', [$dateFrom, $dateTo])->sum('net_salary');
        $staffPay  = SalarySlip::where('status', 'paid')->whereBetween('payment_date', [$dateFrom, $dateTo])->sum('net_salary');

        $totalIncome   = $income + $otherInc;
        $totalExpenses = $expenses + $payroll + $staffPay;
        $netProfit     = $totalIncome - $totalExpenses;

        [$school, $logoSrc] = $this->resolveSchoolAndLogo();

        return view('finance.profit-loss', compact(
            'periodType', 'selectedFy', 'fyList', 'fyDetails', 'year', 'periodLabel', 'periodSub',
            'dateFrom', 'dateTo', 'school', 'logoSrc',
            'totalIncome', 'totalExpenses', 'netProfit',
            'income', 'otherInc', 'expenses', 'payroll', 'staffPay',
            'monthlyData', 'quarterlyData'
        ));
    }

    /** Generate P&L PDF */
    public function profitLossPdf(Request $request)
    {
        $periodType = $request->input('period_type', 'fy');
        $currentFy  = NepaliDateService::currentFiscalYear();
        $selectedFy = $request->input('fy', $currentFy);
        $year       = (int) $request->input('year', now()->year);

        if ($periodType === 'fy') {
            $fyDetails   = NepaliDateService::getFiscalYearDetails($selectedFy);
            $dateFrom    = $fyDetails['start_ad'];
            $dateTo      = $fyDetails['end_ad'];
            $periodLabel = $fyDetails['fy_np'] . " ({$selectedFy})";
            $periodSub   = "साउन १, " . NepaliDateService::toNepaliDigits($fyDetails['start_year_bs']) . " – असार मसान्त, " . NepaliDateService::toNepaliDigits($fyDetails['end_year_bs']) . " ({$dateFrom} to {$dateTo})";
            $filename    = "profit-loss-FY-" . str_replace('/', '-', $selectedFy) . ".pdf";
        } else {
            $dateFrom    = Carbon::create($year)->startOfYear()->toDateString();
            $dateTo      = Carbon::create($year)->endOfYear()->toDateString();
            $periodLabel = "Calendar Year {$year}";
            $periodSub   = "Jan 01, {$year} – Dec 31, {$year}";
            $filename    = "profit-loss-{$year}.pdf";
        }

        $income    = Payment::whereBetween('payment_date', [$dateFrom, $dateTo])->sum('amount');
        $otherInc  = IncomeRecord::whereBetween('income_date', [$dateFrom, $dateTo])->sum('amount');
        $expenses  = Expense::whereBetween('expense_date', [$dateFrom, $dateTo])->sum('amount');
        $payroll   = TeacherPayroll::where('status', 'paid')->whereBetween('payment_date', [$dateFrom, $dateTo])->sum('net_salary');
        $staffPay  = SalarySlip::where('status', 'paid')->whereBetween('payment_date', [$dateFrom, $dateTo])->sum('net_salary');

        $totalIncome   = $income + $otherInc;
        $totalExpenses = $expenses + $payroll + $staffPay;
        $netProfit     = $totalIncome - $totalExpenses;

        [$school, $logoSrc] = $this->resolveSchoolAndLogo();

        $pdf = Pdf::loadView('finance.profit-loss-pdf', compact(
            'periodType', 'selectedFy', 'year', 'periodLabel', 'periodSub', 'dateFrom', 'dateTo', 'school', 'logoSrc',
            'totalIncome', 'totalExpenses', 'netProfit',
            'income', 'otherInc', 'expenses', 'payroll', 'staffPay'
        ))->setPaper('A4');

        return $pdf->download($filename);
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
        $periodType = $request->input('period_type', 'fy'); // 'fy' or 'ad'
        $fyList     = NepaliDateService::getFiscalYearList(7);
        $currentFy  = NepaliDateService::currentFiscalYear();
        $selectedFy = $request->input('fy', $currentFy);
        $year       = (int) $request->input('year', now()->year);

        if ($periodType === 'fy') {
            $fyDetails   = NepaliDateService::getFiscalYearDetails($selectedFy);
            $asOf        = $fyDetails['as_of_ad'];
            $asOfLabel   = $fyDetails['as_of_bs_np'] . " (" . $fyDetails['as_of_bs'] . " BS / " . Carbon::parse($asOf)->format('d M Y') . " AD)";
            $periodLabel = $fyDetails['fy_np'] . " ({$selectedFy})";
            $cutoffYear  = $fyDetails['end_year_bs'];
        } else {
            $fyDetails   = null;
            $asOf        = Carbon::create($year)->endOfYear()->toDateString();
            $asOfLabel   = "31 Dec {$year} (" . Carbon::parse($asOf)->format('d M Y') . " AD)";
            $periodLabel = "Calendar Year {$year}";
            $cutoffYear  = $year;
        }

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
            ->where('created_at', '<=', $asOf . ' 23:59:59')
            ->sum('amount');

        $totalAssets = $cashAndBank + $fixedAssets + $receivables;

        // ── LIABILITIES ──────────────────────────────────────────────────────
        // Unpaid salary slips (draft / approved, not yet paid)
        $salariesPayable = SalarySlip::whereIn('status', ['draft', 'approved'])
            ->where('created_at', '<=', $asOf . ' 23:59:59')
            ->sum('net_salary');
        $teacherSalariesPayable = TeacherPayroll::where('status', 'pending')
            ->where('created_at', '<=', $asOf . ' 23:59:59')
            ->sum('net_salary');

        // TDS payable (estimated at 15% of payroll costs — Nepal flat rate)
        $tdsRate        = 0.15;
        $tdsPayable     = round(($payrollPaid + $staffPayPaid) * $tdsRate, 2);

        $totalLiabilities = $salariesPayable + $teacherSalariesPayable + $tdsPayable;

        // ── EQUITY ────────────────────────────────────────────────────────────
        // Retained earnings up to asOf
        $cumulativeIncome   = Payment::where('payment_date', '<=', $asOf)->sum('amount')
                            + IncomeRecord::where('income_date', '<=', $asOf)->sum('amount');
        $cumulativeExpenses = Expense::where('expense_date', '<=', $asOf)->sum('amount')
                            + TeacherPayroll::where('status', 'paid')->where('payment_date', '<=', $asOf)->sum('net_salary')
                            + SalarySlip::where('status', 'paid')->where('payment_date', '<=', $asOf)->sum('net_salary');
        $retainedEarnings   = $cumulativeIncome - $cumulativeExpenses;

        $totalEquity    = $retainedEarnings;
        $balanceCheck   = $totalAssets - ($totalLiabilities + $totalEquity);

        [$school, $logoSrc] = $this->resolveSchoolAndLogo();

        return view('finance.balance-sheet', compact(
            'periodType', 'selectedFy', 'fyList', 'fyDetails', 'year', 'asOf', 'asOfLabel', 'periodLabel', 'school', 'logoSrc',
            'cashAndBank', 'fixedAssets', 'receivables', 'totalAssets',
            'salariesPayable', 'teacherSalariesPayable', 'tdsPayable', 'totalLiabilities',
            'retainedEarnings', 'totalEquity',
            'balanceCheck'
        ));
    }

    /** PDF version of the balance sheet */
    public function balanceSheetPdf(Request $request)
    {
        $periodType = $request->input('period_type', 'fy');
        $currentFy  = NepaliDateService::currentFiscalYear();
        $selectedFy = $request->input('fy', $currentFy);
        $year       = (int) $request->input('year', now()->year);

        if ($periodType === 'fy') {
            $fyDetails   = NepaliDateService::getFiscalYearDetails($selectedFy);
            $asOf        = $fyDetails['as_of_ad'];
            $asOfLabel   = $fyDetails['as_of_bs_np'] . " (" . $fyDetails['as_of_bs'] . " BS / " . Carbon::parse($asOf)->format('d M Y') . " AD)";
            $periodLabel = $fyDetails['fy_np'] . " ({$selectedFy})";
            $filename    = "balance-sheet-FY-" . str_replace('/', '-', $selectedFy) . ".pdf";
        } else {
            $fyDetails   = null;
            $asOf        = Carbon::create($year)->endOfYear()->toDateString();
            $asOfLabel   = "31 Dec {$year} (" . Carbon::parse($asOf)->format('d M Y') . " AD)";
            $periodLabel = "Calendar Year {$year}";
            $filename    = "balance-sheet-{$year}.pdf";
        }

        $cashAndBank    = max(0,
            Payment::where('payment_date', '<=', $asOf)->sum('amount')
            + IncomeRecord::where('income_date', '<=', $asOf)->sum('amount')
            - Expense::where('expense_date', '<=', $asOf)->sum('amount')
            - TeacherPayroll::where('status', 'paid')->where('payment_date', '<=', $asOf)->sum('net_salary')
            - SalarySlip::where('status', 'paid')->where('payment_date', '<=', $asOf)->sum('net_salary')
        );
        $fixedAssets    = \App\Models\Asset::where('purchase_date', '<=', $asOf)->whereIn('status', ['active', 'in_use'])->sum('purchase_price');
        $receivables    = \App\Models\Invoice::where('status', '!=', 'paid')->where('created_at', '<=', $asOf . ' 23:59:59')->sum('amount');
        $totalAssets    = $cashAndBank + $fixedAssets + $receivables;

        $salariesPayable        = SalarySlip::whereIn('status', ['draft', 'approved'])->where('created_at', '<=', $asOf . ' 23:59:59')->sum('net_salary');
        $teacherSalariesPayable = TeacherPayroll::where('status', 'pending')->where('created_at', '<=', $asOf . ' 23:59:59')->sum('net_salary');
        $payrollPaid            = TeacherPayroll::where('status', 'paid')->where('payment_date', '<=', $asOf)->sum('net_salary')
                                + SalarySlip::where('status', 'paid')->where('payment_date', '<=', $asOf)->sum('net_salary');
        $tdsPayable             = round($payrollPaid * 0.15, 2);
        $totalLiabilities       = $salariesPayable + $teacherSalariesPayable + $tdsPayable;

        $cumulativeIncome   = Payment::where('payment_date', '<=', $asOf)->sum('amount')
                            + IncomeRecord::where('income_date', '<=', $asOf)->sum('amount');
        $cumulativeExpenses = Expense::where('expense_date', '<=', $asOf)->sum('amount')
                            + TeacherPayroll::where('status', 'paid')->where('payment_date', '<=', $asOf)->sum('net_salary')
                            + SalarySlip::where('status', 'paid')->where('payment_date', '<=', $asOf)->sum('net_salary');
        $retainedEarnings   = $cumulativeIncome - $cumulativeExpenses;

        $totalEquity  = $retainedEarnings;
        $balanceCheck = $totalAssets - ($totalLiabilities + $totalEquity);

        [$school, $logoSrc] = $this->resolveSchoolAndLogo();

        $pdf = Pdf::loadView('finance.balance-sheet-pdf', compact(
            'periodType', 'selectedFy', 'year', 'asOf', 'asOfLabel', 'periodLabel', 'school', 'logoSrc',
            'cashAndBank', 'fixedAssets', 'receivables', 'totalAssets',
            'salariesPayable', 'teacherSalariesPayable', 'tdsPayable', 'totalLiabilities',
            'retainedEarnings', 'totalEquity', 'balanceCheck'
        ))->setPaper('A4');

        return $pdf->download($filename);
    }

    /**
     * Day Book (रोजनाम्चा / दैनिक हिसाब) — Daily Cash & Bank Reconciliation.
     */
    public function dayBook(Request $request)
    {
        $date = $request->input('date', today()->toDateString());

        // Nepali BS conversions
        $bsDateEn    = NepaliDateService::adToBS($date);
        $bsDateNp    = NepaliDateService::formatBS($date, true);
        $dayOfWeekNp = NepaliDateService::getDayOfWeekNepali($date);
        $currentFy   = NepaliDateService::currentFiscalYear();
        $fyDetails   = NepaliDateService::getFiscalYearDetails($currentFy);
        $currentFyNp = $fyDetails['fy_np'];

        // 1. Fee Payments received on $date
        $payments = Payment::with(['invoice.student', 'invoice.schoolClass'])
            ->whereDate('payment_date', $date)
            ->latest('id')
            ->get();

        // 2. Miscellaneous Incomes on $date
        $incomes = IncomeRecord::whereDate('income_date', $date)
            ->latest('id')
            ->get();

        // 3. Expenses on $date
        $expenses = Expense::whereDate('expense_date', $date)
            ->latest('id')
            ->get();

        // 4. Payrolls paid on $date
        $teacherPayrolls = TeacherPayroll::with('teacher')
            ->where('status', 'paid')
            ->whereDate('payment_date', $date)
            ->get();

        $staffPayrolls = SalarySlip::with('user')
            ->where('status', 'paid')
            ->whereDate('payment_date', $date)
            ->get();

        // Overall Totals
        $feeTotal     = $payments->sum('amount');
        $incomeTotal  = $incomes->sum('amount');
        $totalInflow  = $feeTotal + $incomeTotal;

        $expenseTotal = $expenses->sum('amount');
        $payrollTotal = $teacherPayrolls->sum('net_salary') + $staffPayrolls->sum('net_salary');
        $totalOutflow = $expenseTotal + $payrollTotal;

        $netDailyChange = $totalInflow - $totalOutflow;

        // Cash vs Digital Inflows
        $cashInflow   = $payments->where('payment_method', 'cash')->sum('amount') + $incomes->where('payment_method', 'cash')->sum('amount');
        $onlineInflow = max(0, $totalInflow - $cashInflow);

        // Cash vs Bank Outflows
        $cashExpense    = $expenses->filter(fn($e) => ($e->payment_method ?? 'cash') === 'cash')->sum('amount');
        $cashTeacherPay = $teacherPayrolls->filter(fn($p) => ($p->payment_method ?? 'cash') === 'cash')->sum('net_salary');
        $cashStaffPay   = $staffPayrolls->filter(fn($s) => ($s->payment_method ?? 'cash') === 'cash')->sum('net_salary');
        $cashOutflow    = $cashExpense + $cashTeacherPay + $cashStaffPay;
        $onlineOutflow  = max(0, $totalOutflow - $cashOutflow);

        // Opening total balance
        $priorInflow = Payment::whereDate('payment_date', '<', $date)->sum('amount')
                     + IncomeRecord::whereDate('income_date', '<', $date)->sum('amount');
        $priorOutflow = Expense::whereDate('expense_date', '<', $date)->sum('amount')
                      + TeacherPayroll::where('status', 'paid')->whereDate('payment_date', '<', $date)->sum('net_salary')
                      + SalarySlip::where('status', 'paid')->whereDate('payment_date', '<', $date)->sum('net_salary');
        $openingBalance = $priorInflow - $priorOutflow;
        $closingBalance = $openingBalance + $netDailyChange;

        // Opening cash-in-hand brought forward
        $priorCashInflow = Payment::whereDate('payment_date', '<', $date)->where('payment_method', 'cash')->sum('amount')
                         + IncomeRecord::whereDate('income_date', '<', $date)->where('payment_method', 'cash')->sum('amount');
        $priorCashOutflow = Expense::whereDate('expense_date', '<', $date)->where('payment_method', 'cash')->sum('amount')
                          + TeacherPayroll::where('status', 'paid')->whereDate('payment_date', '<', $date)->where('payment_method', 'cash')->sum('net_salary')
                          + SalarySlip::where('status', 'paid')->whereDate('payment_date', '<', $date)->where('payment_method', 'cash')->sum('net_salary');
        $openingCash  = max(0, $priorCashInflow - $priorCashOutflow);
        $expectedCash = max(0, $openingCash + $cashInflow - $cashOutflow);

        // Day End Closing record
        $closing = DayBookClosing::with(['closedByUser', 'unlockedByUser'])->where('date', $date)->first();
        $isLocked = $closing && $closing->status === 'locked';

        $defaultDenominations = [
            '1000'  => 0,
            '500'   => 0,
            '100'   => 0,
            '50'    => 0,
            '20'    => 0,
            '10'    => 0,
            '5'     => 0,
            '2'     => 0,
            '1'     => 0,
            'coins' => 0,
        ];
        $denominations = ($closing && is_array($closing->denominations)) ? array_merge($defaultDenominations, $closing->denominations) : $defaultDenominations;

        return view('finance.day-book', compact(
            'date', 'bsDateEn', 'bsDateNp', 'dayOfWeekNp', 'currentFy', 'currentFyNp',
            'payments', 'incomes', 'expenses', 'teacherPayrolls', 'staffPayrolls',
            'feeTotal', 'incomeTotal', 'totalInflow', 'expenseTotal', 'payrollTotal', 'totalOutflow',
            'netDailyChange', 'openingBalance', 'closingBalance',
            'cashInflow', 'onlineInflow', 'cashOutflow', 'onlineOutflow',
            'openingCash', 'expectedCash',
            'closing', 'isLocked', 'denominations'
        ));
    }

    /**
     * Submit Day-End Closing & Physical Cash Denomination Lock.
     */
    public function closeDayBook(Request $request)
    {
        $validated = $request->validate([
            'date'                  => 'required|date',
            'denominations'         => 'required|array',
            'denominations.1000'    => 'nullable|integer|min:0',
            'denominations.500'     => 'nullable|integer|min:0',
            'denominations.100'     => 'nullable|integer|min:0',
            'denominations.50'      => 'nullable|integer|min:0',
            'denominations.20'      => 'nullable|integer|min:0',
            'denominations.10'      => 'nullable|integer|min:0',
            'denominations.5'       => 'nullable|integer|min:0',
            'denominations.2'       => 'nullable|integer|min:0',
            'denominations.1'       => 'nullable|integer|min:0',
            'denominations.coins'   => 'nullable|numeric|min:0',
            'notes'                 => 'nullable|string|max:1000',
        ]);

        $date = $validated['date'];
        $denoms = $validated['denominations'];

        // Compute physical cash counted
        $actualCash = (1000 * (int)($denoms['1000'] ?? 0))
                    + (500  * (int)($denoms['500']  ?? 0))
                    + (100  * (int)($denoms['100']  ?? 0))
                    + (50   * (int)($denoms['50']   ?? 0))
                    + (20   * (int)($denoms['20']   ?? 0))
                    + (10   * (int)($denoms['10']   ?? 0))
                    + (5    * (int)($denoms['5']    ?? 0))
                    + (2    * (int)($denoms['2']    ?? 0))
                    + (1    * (int)($denoms['1']    ?? 0))
                    + (float)($denoms['coins']      ?? 0);

        // Recalculate official day book metrics
        $payments = Payment::whereDate('payment_date', $date)->get();
        $incomes  = IncomeRecord::whereDate('income_date', $date)->get();
        $expenses = Expense::whereDate('expense_date', $date)->get();
        $tPay     = TeacherPayroll::where('status', 'paid')->whereDate('payment_date', $date)->get();
        $sPay     = SalarySlip::where('status', 'paid')->whereDate('payment_date', $date)->get();

        $feeTotal     = $payments->sum('amount');
        $incomeTotal  = $incomes->sum('amount');
        $totalInflow  = $feeTotal + $incomeTotal;
        $cashInflow   = $payments->where('payment_method', 'cash')->sum('amount') + $incomes->where('payment_method', 'cash')->sum('amount');
        $onlineInflow = max(0, $totalInflow - $cashInflow);

        $expenseTotal = $expenses->sum('amount');
        $payrollTotal = $tPay->sum('net_salary') + $sPay->sum('net_salary');
        $totalOutflow = $expenseTotal + $payrollTotal;

        $cashExpense    = $expenses->filter(fn($e) => ($e->payment_method ?? 'cash') === 'cash')->sum('amount');
        $cashTeacherPay = $tPay->filter(fn($p) => ($p->payment_method ?? 'cash') === 'cash')->sum('net_salary');
        $cashStaffPay   = $sPay->filter(fn($s) => ($s->payment_method ?? 'cash') === 'cash')->sum('net_salary');
        $cashOutflow    = $cashExpense + $cashTeacherPay + $cashStaffPay;
        $onlineOutflow  = max(0, $totalOutflow - $cashOutflow);

        $priorInflow = Payment::whereDate('payment_date', '<', $date)->sum('amount')
                     + IncomeRecord::whereDate('income_date', '<', $date)->sum('amount');
        $priorOutflow = Expense::whereDate('expense_date', '<', $date)->sum('amount')
                      + TeacherPayroll::where('status', 'paid')->whereDate('payment_date', '<', $date)->sum('net_salary')
                      + SalarySlip::where('status', 'paid')->whereDate('payment_date', '<', $date)->sum('net_salary');
        $openingBalance = $priorInflow - $priorOutflow;
        $closingBalance = $openingBalance + ($totalInflow - $totalOutflow);

        $priorCashInflow = Payment::whereDate('payment_date', '<', $date)->where('payment_method', 'cash')->sum('amount')
                         + IncomeRecord::whereDate('income_date', '<', $date)->where('payment_method', 'cash')->sum('amount');
        $priorCashOutflow = Expense::whereDate('expense_date', '<', $date)->where('payment_method', 'cash')->sum('amount')
                          + TeacherPayroll::where('status', 'paid')->whereDate('payment_date', '<', $date)->where('payment_method', 'cash')->sum('net_salary')
                          + SalarySlip::where('status', 'paid')->whereDate('payment_date', '<', $date)->where('payment_method', 'cash')->sum('net_salary');
        $openingCash  = max(0, $priorCashInflow - $priorCashOutflow);
        $expectedCash = max(0, $openingCash + $cashInflow - $cashOutflow);

        $difference = $actualCash - $expectedCash;

        DayBookClosing::updateOrCreate(
            ['date' => $date],
            [
                'opening_balance' => $openingBalance,
                'cash_inflow'     => $cashInflow,
                'online_inflow'   => $onlineInflow,
                'total_inflow'    => $totalInflow,
                'cash_outflow'    => $cashOutflow,
                'online_outflow'  => $onlineOutflow,
                'total_outflow'   => $totalOutflow,
                'expected_cash'   => $expectedCash,
                'actual_cash'     => $actualCash,
                'difference'      => $difference,
                'closing_balance' => $closingBalance,
                'denominations'   => $denoms,
                'notes'           => $validated['notes'] ?? null,
                'status'          => 'locked',
                'closed_by'       => auth()->id(),
                'closed_at'       => now(),
            ]
        );

        return back()->with('status', 'दिनको हिसाब र भौतिक नगद दरबन्दी सफलतापूर्वक बन्द र लक गरियो (Day Book & Cash Denominations locked successfully).');
    }

    /**
     * Unlock Day-End Closing for administrative corrections.
     */
    public function unlockDayBook(Request $request)
    {
        $date = $request->validate(['date' => 'required|date'])['date'];

        $closing = DayBookClosing::where('date', $date)->first();
        if ($closing) {
            $closing->update([
                'status'      => 'unlocked',
                'unlocked_by' => auth()->id(),
                'unlocked_at' => now(),
            ]);
        }

        return back()->with('status', 'दिनको हिसाब सफलतापूर्वक अनलक गरियो (Day Book unlocked for editing).');
    }

    /**
     * Official Cash Handover & Denomination Reconciliation Slip (PDF / Print).
     */
    public function reconciliationSlip(Request $request)
    {
        $date = $request->input('date', today()->toDateString());

        $closing = DayBookClosing::with(['closedByUser'])->where('date', $date)->firstOrFail();

        $bsDateEn    = NepaliDateService::adToBS($date);
        $bsDateNp    = NepaliDateService::formatBS($date, true);
        $dayOfWeekNp = NepaliDateService::getDayOfWeekNepali($date);
        $currentFy   = NepaliDateService::currentFiscalYear();
        $fyDetails   = NepaliDateService::getFiscalYearDetails($currentFy);
        $currentFyNp = $fyDetails['fy_np'];

        $school = auth()->user()->school ?? \App\Models\School::first();

        $pdf = Pdf::loadView('finance.reconciliation-slip-pdf', compact(
            'date', 'bsDateEn', 'bsDateNp', 'dayOfWeekNp', 'currentFy', 'currentFyNp',
            'closing', 'school'
        ))->setPaper('A5', 'portrait');

        return $pdf->stream("cash-reconciliation-{$date}.pdf");
    }

    /**
     * Printable Official Day Book PDF.
     */
    public function dayBookPdf(Request $request)
    {
        $date = $request->input('date', today()->toDateString());

        // Nepali BS conversions
        $bsDateEn    = NepaliDateService::adToBS($date);
        $bsDateNp    = NepaliDateService::formatBS($date, true);
        $dayOfWeekNp = NepaliDateService::getDayOfWeekNepali($date);
        $currentFy   = NepaliDateService::currentFiscalYear();
        $fyDetails   = NepaliDateService::getFiscalYearDetails($currentFy);
        $currentFyNp = $fyDetails['fy_np'];

        $payments = Payment::with(['invoice.student', 'invoice.schoolClass'])
            ->whereDate('payment_date', $date)->get();
        $incomes = IncomeRecord::whereDate('income_date', $date)->get();
        $expenses = Expense::whereDate('expense_date', $date)->get();
        $teacherPayrolls = TeacherPayroll::with('teacher')->where('status', 'paid')->whereDate('payment_date', $date)->get();
        $staffPayrolls = SalarySlip::with('user')->where('status', 'paid')->whereDate('payment_date', $date)->get();

        $feeTotal     = $payments->sum('amount');
        $incomeTotal  = $incomes->sum('amount');
        $totalInflow  = $feeTotal + $incomeTotal;
        $expenseTotal = $expenses->sum('amount');
        $payrollTotal = $teacherPayrolls->sum('net_salary') + $staffPayrolls->sum('net_salary');
        $totalOutflow = $expenseTotal + $payrollTotal;
        $netDailyChange = $totalInflow - $totalOutflow;

        $priorInflow = Payment::whereDate('payment_date', '<', $date)->sum('amount')
                     + IncomeRecord::whereDate('income_date', '<', $date)->sum('amount');
        $priorOutflow = Expense::whereDate('expense_date', '<', $date)->sum('amount')
                      + TeacherPayroll::where('status', 'paid')->whereDate('payment_date', '<', $date)->sum('net_salary')
                      + SalarySlip::where('status', 'paid')->whereDate('payment_date', '<', $date)->sum('net_salary');
        $openingBalance = $priorInflow - $priorOutflow;
        $closingBalance = $openingBalance + $netDailyChange;

        $closing = DayBookClosing::with(['closedByUser'])->where('date', $date)->first();

        [$school, $logoSrc] = $this->resolveSchoolAndLogo();

        $pdf = Pdf::loadView('finance.day-book-pdf', compact(
            'date', 'bsDateEn', 'bsDateNp', 'dayOfWeekNp', 'currentFy', 'currentFyNp',
            'school', 'logoSrc', 'payments', 'incomes', 'expenses', 'teacherPayrolls', 'staffPayrolls',
            'feeTotal', 'incomeTotal', 'totalInflow', 'expenseTotal', 'payrollTotal', 'totalOutflow',
            'netDailyChange', 'openingBalance', 'closingBalance', 'closing'
        ))->setPaper('A4', 'portrait');

        return $pdf->stream("day-book-{$date}.pdf");
    }

    /**
     * Resolve current tenant school and generate Base64 data URI for DomPDF logo rendering.
     */
    protected function resolveSchoolAndLogo(): array
    {
        $user = auth()->user();
        $schoolId = $user ? $user->school_id : null;
        $school = ($schoolId ? \App\Models\School::find($schoolId) : null)
            ?? optional($user)->school
            ?? config('tenant.school')
            ?? \App\Models\School::first();

        $logoSrc = '';
        if ($school && $school->logo) {
            $clean = ltrim(str_replace('storage/', '', $school->logo), '/');
            $candidates = [
                storage_path('app/public/' . $clean),
                public_path('storage/' . $clean),
                public_path($clean),
            ];
            foreach ($candidates as $filePath) {
                if (file_exists($filePath)) {
                    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                    if ($ext === 'webp' && function_exists('imagecreatefromwebp')) {
                        $im = @imagecreatefromwebp($filePath);
                        if ($im) {
                            ob_start();
                            imagepng($im);
                            $pngData = ob_get_clean();
                            imagedestroy($im);
                            if ($pngData) {
                                $logoSrc = 'data:image/png;base64,' . base64_encode($pngData);
                                break;
                            }
                        }
                    }
                    $data = @file_get_contents($filePath);
                    if ($data) {
                        $mime = ($ext === 'png') ? 'image/png' : (($ext === 'svg') ? 'image/svg+xml' : 'image/jpeg');
                        $logoSrc = 'data:' . $mime . ';base64,' . base64_encode($data);
                        break;
                    }
                }
            }
        }

        return [$school, $logoSrc];
    }
}
