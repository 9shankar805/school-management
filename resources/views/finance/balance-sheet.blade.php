@extends('layouts.app')
@section('title', 'Balance Sheet ' . $year)

@section('content')
<div class="flex-1 p-6 lg:p-8 overflow-auto">

    {{-- Header --}}
    <div class="flex flex-wrap justify-between items-start mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight flex items-center gap-2">
                <i class="bi bi-clipboard2-data-fill text-indigo-600"></i> Balance Sheet
            </h1>
            <p class="text-slate-400 text-sm mt-0.5">As of {{ \Carbon\Carbon::parse($asOf)->format('d F Y') }}</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <form method="GET" class="flex items-center gap-2">
                <select name="year" onchange="this.form.submit()"
                    class="border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    @for($y = now()->year; $y >= now()->year - 5; $y--)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>FY {{ $y }}</option>
                    @endfor
                </select>
            </form>
            <a href="{{ route('finance.balance-sheet.pdf', ['year' => $year]) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-xl transition shadow-sm">
                <i class="bi bi-file-pdf"></i> Download PDF
            </a>
        </div>
    </div>

    @include('session-messages')

    {{-- Balance check alert --}}
    @if(abs($balanceCheck) > 1)
    <div class="mb-5 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-2xl flex items-start gap-3">
        <i class="bi bi-exclamation-triangle-fill text-amber-500 text-lg flex-shrink-0 mt-0.5"></i>
        <div>
            <p class="font-bold text-amber-800 dark:text-amber-300 text-sm">Balance discrepancy of NPR {{ number_format(abs($balanceCheck), 2) }}</p>
            <p class="text-amber-700 dark:text-amber-400 text-xs mt-0.5">Assets do not equal Liabilities + Equity. This may be due to unrecorded transactions. Run a ledger sync to reconcile.</p>
        </div>
    </div>
    @endif

    {{-- Two-column layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- ── ASSETS ─────────────────────────────────────────────────── --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-6 py-4 bg-emerald-50 dark:bg-emerald-900/20 border-b border-emerald-100 dark:border-emerald-800 flex items-center gap-2">
                <i class="bi bi-plus-circle-fill text-emerald-600 text-lg"></i>
                <h2 class="font-black text-emerald-800 dark:text-emerald-300 text-base tracking-tight">Assets</h2>
            </div>
            <div class="divide-y divide-slate-50 dark:divide-slate-800">
                {{-- Current Assets --}}
                <div class="px-6 py-3">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Current Assets</p>
                    <div class="flex justify-between py-1.5 text-sm">
                        <span class="text-slate-600 dark:text-slate-300 flex items-center gap-1.5"><i class="bi bi-cash text-emerald-500"></i> Cash & Bank (Net)</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-100">NPR {{ number_format($cashAndBank, 2) }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 text-sm">
                        <span class="text-slate-600 dark:text-slate-300 flex items-center gap-1.5"><i class="bi bi-file-earmark-text text-blue-500"></i> Accounts Receivable</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-100">NPR {{ number_format($receivables, 2) }}</span>
                    </div>
                </div>
                {{-- Non-Current Assets --}}
                <div class="px-6 py-3">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Non-Current Assets</p>
                    <div class="flex justify-between py-1.5 text-sm">
                        <span class="text-slate-600 dark:text-slate-300 flex items-center gap-1.5"><i class="bi bi-building text-violet-500"></i> Fixed Assets (at cost)</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-100">NPR {{ number_format($fixedAssets, 2) }}</span>
                    </div>
                </div>
                {{-- Total --}}
                <div class="px-6 py-4 bg-emerald-50 dark:bg-emerald-900/20">
                    <div class="flex justify-between text-base font-black">
                        <span class="text-emerald-800 dark:text-emerald-300">Total Assets</span>
                        <span class="text-emerald-700 dark:text-emerald-400">NPR {{ number_format($totalAssets, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── LIABILITIES + EQUITY ────────────────────────────────────── --}}
        <div class="space-y-6">

            {{-- Liabilities --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-6 py-4 bg-rose-50 dark:bg-rose-900/20 border-b border-rose-100 dark:border-rose-800 flex items-center gap-2">
                    <i class="bi bi-dash-circle-fill text-rose-600 text-lg"></i>
                    <h2 class="font-black text-rose-800 dark:text-rose-300 text-base tracking-tight">Liabilities</h2>
                </div>
                <div class="divide-y divide-slate-50 dark:divide-slate-800">
                    <div class="px-6 py-3">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Current Liabilities</p>
                        <div class="flex justify-between py-1.5 text-sm">
                            <span class="text-slate-600 dark:text-slate-300 flex items-center gap-1.5"><i class="bi bi-person-badge text-amber-500"></i> Staff Salaries Payable</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-100">NPR {{ number_format($salariesPayable + $teacherSalariesPayable, 2) }}</span>
                        </div>
                        <div class="flex justify-between py-1.5 text-sm">
                            <span class="text-slate-600 dark:text-slate-300 flex items-center gap-1.5"><i class="bi bi-bank text-rose-500"></i> TDS Payable (est. 15%)</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-100">NPR {{ number_format($tdsPayable, 2) }}</span>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-rose-50 dark:bg-rose-900/20">
                        <div class="flex justify-between text-base font-black">
                            <span class="text-rose-800 dark:text-rose-300">Total Liabilities</span>
                            <span class="text-rose-700 dark:text-rose-400">NPR {{ number_format($totalLiabilities, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Equity --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-6 py-4 bg-indigo-50 dark:bg-indigo-900/20 border-b border-indigo-100 dark:border-indigo-800 flex items-center gap-2">
                    <i class="bi bi-graph-up-arrow text-indigo-600 text-lg"></i>
                    <h2 class="font-black text-indigo-800 dark:text-indigo-300 text-base tracking-tight">Equity</h2>
                </div>
                <div class="divide-y divide-slate-50 dark:divide-slate-800">
                    <div class="px-6 py-3">
                        <div class="flex justify-between py-1.5 text-sm">
                            <span class="text-slate-600 dark:text-slate-300 flex items-center gap-1.5">
                                <i class="bi bi-piggy-bank {{ $retainedEarnings >= 0 ? 'text-emerald-500' : 'text-rose-500' }}"></i>
                                Retained Earnings
                            </span>
                            <span class="font-semibold {{ $retainedEarnings >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                NPR {{ number_format($retainedEarnings, 2) }}
                            </span>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-indigo-50 dark:bg-indigo-900/20">
                        <div class="flex justify-between text-base font-black">
                            <span class="text-indigo-800 dark:text-indigo-300">Total Equity</span>
                            <span class="text-indigo-700 dark:text-indigo-400">NPR {{ number_format($totalEquity, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Balance check --}}
            <div class="p-4 rounded-2xl border-2 {{ abs($balanceCheck) <= 1 ? 'border-emerald-200 dark:border-emerald-700 bg-emerald-50 dark:bg-emerald-900/20' : 'border-amber-300 dark:border-amber-600 bg-amber-50 dark:bg-amber-900/20' }}">
                <div class="flex justify-between text-sm font-bold">
                    <span class="{{ abs($balanceCheck) <= 1 ? 'text-emerald-700 dark:text-emerald-300' : 'text-amber-700 dark:text-amber-300' }}">
                        Liabilities + Equity
                    </span>
                    <span class="{{ abs($balanceCheck) <= 1 ? 'text-emerald-700 dark:text-emerald-300' : 'text-amber-700 dark:text-amber-300' }}">
                        NPR {{ number_format($totalLiabilities + $totalEquity, 2) }}
                    </span>
                </div>
                @if(abs($balanceCheck) <= 1)
                <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-1 flex items-center gap-1">
                    <i class="bi bi-check-circle-fill"></i> Balanced ✓
                </p>
                @endif
            </div>

        </div>
    </div>

    {{-- Footer note --}}
    <p class="text-xs text-slate-400 mt-6 text-center">
        Balance Sheet as at {{ \Carbon\Carbon::parse($asOf)->format('d F Y') }} · Generated {{ now()->format('d M Y, h:i A') }} · {{ config('app.name') }}
    </p>

</div>
@endsection
