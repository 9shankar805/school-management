@extends('layouts.app')
@section('title', 'Accounting Ledger')

@section('content')
<div class="px-6 py-5 max-w-7xl mx-auto">
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white flex items-center gap-2 tracking-tight">
                <i class="bi bi-journal-text text-teal-500"></i> Accounting Ledger
            </h1>
            <nav class="flex text-xs text-slate-500 dark:text-slate-400 mt-1" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-2">
                    <li class="inline-flex items-center">
                        <a href="{{ route('home') }}" class="hover:text-blue-600">Home</a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <span class="mx-2">/</span>
                            <a href="{{ route('finance.overview') }}" class="hover:text-blue-600">Finance</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <span class="mx-2">/</span>
                            <span class="text-slate-700 dark:text-slate-300 font-medium">Ledger</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('finance.profit-loss') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-teal-700 bg-teal-100 hover:bg-teal-200 dark:bg-teal-900/30 dark:text-teal-400 dark:hover:bg-teal-900/50 transition">
                <i class="bi bi-graph-up"></i> P&L Report
            </a>
            <form method="POST" action="{{ route('finance.ledger.sync') }}" class="inline-block">
                @csrf
                <input type="hidden" name="month" value="{{ now()->month }}">
                <input type="hidden" name="year" value="{{ now()->year }}">
                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-amber-700 bg-amber-100 hover:bg-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:hover:bg-amber-900/50 transition shadow-sm">
                    <i class="bi bi-arrow-repeat"></i> Sync Month
                </button>
            </form>
        </div>
    </div>

    @if(session('status'))
    <div class="mb-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 flex items-center gap-2">
        <i class="bi bi-check-circle text-emerald-600"></i>
        <span class="text-sm text-emerald-800 dark:text-emerald-200">{{ session('status') }}</span>
    </div>
    @endif

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-red-50 dark:bg-red-900/20 rounded-2xl p-5 border border-red-100 dark:border-red-800/50 flex flex-col items-center justify-center text-center">
            <div class="text-2xl font-black text-red-600 dark:text-red-400">Rs. {{ number_format($totalDebit, 2) }}</div>
            <div class="text-xs font-semibold text-red-800/70 dark:text-red-300 uppercase tracking-wide mt-1">Total Debits</div>
        </div>
        <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-2xl p-5 border border-emerald-100 dark:border-emerald-800/50 flex flex-col items-center justify-center text-center">
            <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400">Rs. {{ number_format($totalCredit, 2) }}</div>
            <div class="text-xs font-semibold text-emerald-800/70 dark:text-emerald-300 uppercase tracking-wide mt-1">Total Credits</div>
        </div>
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-2xl p-5 border border-blue-100 dark:border-blue-800/50 flex flex-col items-center justify-center text-center">
            <div class="text-2xl font-black text-blue-600 dark:text-blue-400">Rs. {{ number_format($totalCredit - $totalDebit, 2) }}</div>
            <div class="text-xs font-semibold text-blue-800/70 dark:text-blue-300 uppercase tracking-wide mt-1">Net Balance</div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl shadow-sm p-4 mb-6">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div class="w-32">
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">From</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-900/50 text-slate-800 dark:text-slate-100">
            </div>
            <div class="w-32">
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">To</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-900/50 text-slate-800 dark:text-slate-100">
            </div>
            <div class="w-48">
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Type</label>
                <select name="type" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-900/50 text-slate-800 dark:text-slate-100">
                    <option value="all" @selected($type==='all')>All</option>
                    <option value="fee_payment" @selected($type==='fee_payment')>Fee Payments</option>
                    <option value="expense" @selected($type==='expense')>Expenses</option>
                    <option value="income" @selected($type==='income')>Income</option>
                    <option value="payroll" @selected($type==='payroll')>Payroll</option>
                </select>
            </div>
            <button class="px-4 py-2 rounded-lg text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition border border-slate-200">
                <i class="bi bi-funnel"></i> Filter
            </button>
        </form>
    </div>

    {{-- Ledger Table --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Account</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Description</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-red-600 dark:text-red-400 uppercase tracking-wider">Debit</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Credit</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse($entries as $entry)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition">
                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400 whitespace-nowrap">{{ $entry->entry_date->format('d M Y') }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300">
                                {{ str_replace('_',' ',ucfirst($entry->type)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm font-semibold text-slate-900 dark:text-white">{{ $entry->account }}</td>
                        <td class="px-4 py-3 text-xs text-slate-500 dark:text-slate-400">{{ Str::limit($entry->description, 60) }}</td>
                        <td class="px-4 py-3 text-right text-sm text-red-600 dark:text-red-400">{{ $entry->debit > 0 ? 'Rs. '.number_format($entry->debit,2) : '' }}</td>
                        <td class="px-4 py-3 text-right text-sm text-emerald-600 dark:text-emerald-400">{{ $entry->credit > 0 ? 'Rs. '.number_format($entry->credit,2) : '' }}</td>
                        <td class="px-4 py-3 text-right text-sm font-bold {{ $entry->balance >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                            Rs. {{ number_format($entry->balance,2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400">
                            <i class="bi bi-journal-text text-4xl mb-2 block text-slate-300 dark:text-slate-600"></i>
                            <span class="text-sm">No ledger entries found. Click "Sync" to build entries from transactions.</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700">
            {{ $entries->links() }}
        </div>
    </div>
</div>
@endsection
