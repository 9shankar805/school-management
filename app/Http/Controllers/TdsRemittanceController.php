<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\LedgerEntry;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * TdsRemittanceController
 *
 * Tracks TDS (Tax Deducted at Source) deposited to the Nepal IRD.
 *
 * Workflow:
 *  1. Each month payroll runs → SalaryObserver posts "TDS Payable" Cr entries
 *  2. School accountant visits this page to see how much TDS is pending
 *  3. After depositing to IRD bank, clicks "Record Deposit"
 *  4. System posts:
 *       Dr: TDS Payable    (clears the liability)
 *       Cr: Bank Account   (cash out)
 *     and records an Expense (TDS Deposit) for the accounting reports
 */
class TdsRemittanceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'can:manage finance']);
    }

    public function index(Request $request)
    {
        $year  = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        // TDS that has accumulated as a payable (from payroll journal entries)
        $tdsAccrued = LedgerEntry::where('account', 'TDS Payable')
            ->whereYear('entry_date', $year)
            ->whereMonth('entry_date', $month)
            ->sum('credit');

        // TDS already deposited this month (debit side of TDS Payable)
        $tdsDeposited = LedgerEntry::where('account', 'TDS Payable')
            ->where('type', 'tds_remittance')
            ->whereYear('entry_date', $year)
            ->whereMonth('entry_date', $month)
            ->sum('debit');

        $tdsPending = max(0, $tdsAccrued - $tdsDeposited);

        // History of remittances
        $remittances = LedgerEntry::where('account', 'TDS Payable')
            ->where('type', 'tds_remittance')
            ->orderByDesc('entry_date')
            ->paginate(20)
            ->withQueryString();

        // Monthly breakdown (current year)
        $monthlyBreakdown = [];
        for ($m = 1; $m <= 12; $m++) {
            $accrued   = LedgerEntry::where('account', 'TDS Payable')
                ->whereYear('entry_date', $year)->whereMonth('entry_date', $m)
                ->sum('credit');
            $deposited = LedgerEntry::where('account', 'TDS Payable')
                ->where('type', 'tds_remittance')
                ->whereYear('entry_date', $year)->whereMonth('entry_date', $m)
                ->sum('debit');
            $monthlyBreakdown[] = [
                'month'     => Carbon::create($year, $m)->format('M'),
                'accrued'   => $accrued,
                'deposited' => $deposited,
                'pending'   => max(0, $accrued - $deposited),
            ];
        }

        return view('finance.tds-remittance', compact(
            'year', 'month', 'tdsAccrued', 'tdsDeposited', 'tdsPending',
            'remittances', 'monthlyBreakdown'
        ));
    }

    /**
     * Record a TDS deposit to IRD.
     * Posts Dr: TDS Payable / Cr: Bank and creates an expense record.
     */
    public function deposit(Request $request)
    {
        $data = $request->validate([
            'amount'         => 'required|numeric|min:1',
            'deposit_date'   => 'required|date',
            'challan_number' => 'required|string|max:50',
            'bank'           => 'nullable|string|max:100',
            'notes'          => 'nullable|string|max:500',
            'period_month'   => 'required|integer|min:1|max:12',
            'period_year'    => 'required|integer|min:2000|max:2100',
        ]);

        DB::transaction(function () use ($data) {
            $desc = "TDS Deposit to IRD — Challan #{$data['challan_number']} "
                  . "({$data['period_month']}/{$data['period_year']})";

            // Dr: TDS Payable (clears the liability)
            LedgerEntry::create([
                'entry_date'  => $data['deposit_date'],
                'type'        => 'tds_remittance',
                'account'     => 'TDS Payable',
                'debit'       => $data['amount'],
                'credit'      => 0,
                'balance'     => 0,
                'description' => $desc,
                'created_by'  => auth()->id(),
            ]);

            // Cr: Bank Account (cash out)
            LedgerEntry::create([
                'entry_date'  => $data['deposit_date'],
                'type'        => 'tds_remittance',
                'account'     => $data['bank'] ?? 'Bank Account',
                'debit'       => 0,
                'credit'      => $data['amount'],
                'balance'     => 0,
                'description' => $desc,
                'created_by'  => auth()->id(),
            ]);

            // Also record as an Expense so it appears in P&L
            Expense::create([
                'title'        => 'TDS Deposit to IRD',
                'amount'       => $data['amount'],
                'expense_date' => $data['deposit_date'],
                'category'     => 'Tax & Government Dues',
                'description'  => $desc . ($data['notes'] ? ' — ' . $data['notes'] : ''),
                'created_by'   => auth()->id(),
            ]);
        });

        return back()->with('status',
            'TDS deposit of Rs. ' . number_format($data['amount'], 2) .
            ' recorded. Challan: ' . $data['challan_number']
        );
    }
}
