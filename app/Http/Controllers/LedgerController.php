<?php

namespace App\Http\Controllers;

use App\Models\AccountingLedger;
use App\Models\Expense;
use App\Models\IncomeEntry;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LedgerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ── Accounting Ledger (full transaction log) ──────────────────────────────
    public function index(Request $request)
    {
        $this->authorize('view invoices');

        $from  = $request->input('from', now()->startOfMonth()->toDateString());
        $to    = $request->input('to',   now()->toDateString());
        $type  = $request->input('type'); // debit | credit | null

        $query = AccountingLedger::with('createdBy')
            ->whereBetween('transaction_date', [$from, $to])
            ->orderBy('transaction_date')
            ->orderBy('id');

        if ($type) {
            $query->where('type', $type);
        }

        $entries      = $query->paginate(50)->withQueryString();
        $totalCredit  = AccountingLedger::whereBetween('transaction_date', [$from, $to])
            ->where('type', 'credit')->sum('amount');
        $totalDebit   = AccountingLedger::whereBetween('transaction_date', [$from, $to])
            ->where('type', 'debit')->sum('amount');
        $netBalance   = AccountingLedger::latest('id')->value('balance') ?? 0;

        return view('finance.ledger.index', compact(
            'entries', 'from', 'to', 'type', 'totalCredit', 'totalDebit', 'netBalance'
        ));
    }

    // ── Balance Sheet ─────────────────────────────────────────────────────────
    public function balanceSheet(Request $request)
    {
        $this->authorize('view invoices');

        $year  = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        // Income breakdown by category
        $incomeByCategory = IncomeEntry::whereBetween('income_date', [$start, $end])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        // Also count fee payments
        $feePayments = Payment::whereBetween('payment_date', [$start, $end])
            ->whereNull('deleted_at')
            ->sum('amount_paid');

        // Expense breakdown by category
        $expenseByCategory = Expense::where('status', 'approved')
            ->whereBetween('expense_date', [$start, $end])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        $totalIncome  = $incomeByCategory->sum() + $feePayments;
        $totalExpense = $expenseByCategory->sum();
        $netProfit    = $totalIncome - $totalExpense;

        // Outstanding receivables
        $outstanding = Invoice::whereIn('status', ['unpaid', 'partial'])
            ->whereNull('deleted_at')
            ->sum('net_amount')
            - Invoice::whereIn('status', ['unpaid', 'partial'])
                ->join('payments', 'invoices.id', '=', 'payments.invoice_id')
                ->whereNull('payments.deleted_at')
                ->sum('payments.amount_paid');

        return view('finance.ledger.balance-sheet', compact(
            'year', 'month', 'start', 'end',
            'incomeByCategory', 'expenseByCategory',
            'feePayments', 'totalIncome', 'totalExpense',
            'netProfit', 'outstanding'
        ));
    }

    // ── P&L Report (year-over-month chart data) ───────────────────────────────
    public function profitLoss(Request $request)
    {
        $this->authorize('view invoices');

        $year = (int) $request->input('year', now()->year);

        // Build monthly income vs expense for the year
        $months = collect(range(1, 12))->map(function (int $m) use ($year) {
            $start = Carbon::create($year, $m, 1)->startOfMonth();
            $end   = $start->copy()->endOfMonth();

            $income = Payment::whereBetween('payment_date', [$start, $end])
                ->whereNull('deleted_at')->sum('amount_paid')
                + IncomeEntry::whereBetween('income_date', [$start, $end])
                ->whereNull('deleted_at')->sum('amount');

            $expense = Expense::where('status', 'approved')
                ->whereBetween('expense_date', [$start, $end])
                ->whereNull('deleted_at')->sum('amount');

            return [
                'month'   => $start->format('M'),
                'income'  => round($income, 2),
                'expense' => round($expense, 2),
                'net'     => round($income - $expense, 2),
            ];
        });

        $years = range(now()->year - 3, now()->year + 1);

        return view('finance.ledger.profit-loss', compact('year', 'months', 'years'));
    }
}
