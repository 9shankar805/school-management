@extends('layouts.app')
@section('title', 'TDS Remittance Tracker')

@section('content')
<div class="px-6 py-5 max-w-7xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="bi bi-bank2 text-blue-600"></i> TDS Remittance Tracker
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Track Tax Deducted at Source and record IRD deposits
            </p>
        </div>
        <a href="{{ route('finance.overview') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-blue-400 transition">
            <i class="bi bi-arrow-left"></i> Finance Overview
        </a>
    </div>

    @include('layouts.session-messages')

    {{-- Period filter --}}
    <form method="GET" action="{{ route('finance.tds.index') }}" class="mb-6 flex items-center gap-3">
        <select name="month" class="border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400">
            @foreach(range(1,12) as $m)
                <option value="{{ $m }}" @selected($month==$m)>{{ \Carbon\Carbon::create(2000,$m)->format('F') }}</option>
            @endforeach
        </select>
        <input type="number" name="year" value="{{ $year }}" min="2020" max="2030"
            class="w-24 border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400">
        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition">
            <i class="bi bi-filter"></i> Filter
        </button>
    </form>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5 shadow-sm">
            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mb-1">TDS Accrued This Month</p>
            <p class="text-3xl font-extrabold text-slate-900 dark:text-white">Rs. {{ number_format($tdsAccrued, 2) }}</p>
            <p class="text-xs text-slate-400 mt-1">Withheld from employee salaries</p>
        </div>
        <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-2xl p-5 shadow-sm">
            <p class="text-xs text-emerald-700 dark:text-emerald-400 font-medium mb-1">Deposited to IRD</p>
            <p class="text-3xl font-extrabold text-emerald-700 dark:text-emerald-400">Rs. {{ number_format($tdsDeposited, 2) }}</p>
            <p class="text-xs text-emerald-600/70 dark:text-emerald-500/70 mt-1">Challan payments recorded</p>
        </div>
        <div class="bg-{{ $tdsPending > 0 ? 'amber' : 'slate' }}-50 dark:bg-{{ $tdsPending > 0 ? 'amber' : 'slate' }}-900/20 border border-{{ $tdsPending > 0 ? 'amber' : 'slate' }}-200 dark:border-{{ $tdsPending > 0 ? 'amber' : 'slate' }}-800 rounded-2xl p-5 shadow-sm">
            <p class="text-xs text-{{ $tdsPending > 0 ? 'amber' : 'slate' }}-700 dark:text-{{ $tdsPending > 0 ? 'amber' : 'slate' }}-400 font-medium mb-1">Pending Deposit</p>
            <p class="text-3xl font-extrabold text-{{ $tdsPending > 0 ? 'amber' : 'slate' }}-700 dark:text-{{ $tdsPending > 0 ? 'amber' : 'slate' }}-400">Rs. {{ number_format($tdsPending, 2) }}</p>
            <p class="text-xs mt-1 text-{{ $tdsPending > 0 ? 'amber' : 'slate' }}-500">{{ $tdsPending > 0 ? 'Must be deposited by the 15th of next month' : 'All deposited ✓' }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        {{-- Record IRD Deposit --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                    <i class="bi bi-send text-emerald-500"></i> Record TDS Deposit to IRD
                </h3>
            </div>
            <div class="px-6 py-4">
                <form method="POST" action="{{ route('finance.tds.deposit') }}">
                    @csrf
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Amount (NPR) <span class="text-red-500">*</span></label>
                            <input type="number" name="amount" value="{{ number_format($tdsPending, 2, '.', '') }}" step="0.01" min="1"
                                class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Deposit Date <span class="text-red-500">*</span></label>
                            <input type="date" name="deposit_date" value="{{ today()->format('Y-m-d') }}"
                                class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">IRD Challan No. <span class="text-red-500">*</span></label>
                            <input type="text" name="challan_number" placeholder="e.g. TDS-2083-001"
                                class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Bank</label>
                            <input type="text" name="bank" placeholder="e.g. NIC Asia Bank"
                                class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Period Month <span class="text-red-500">*</span></label>
                            <select name="period_month" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                                @foreach(range(1,12) as $m)
                                    <option value="{{ $m }}" @selected($month==$m)>{{ \Carbon\Carbon::create(2000,$m)->format('F') }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Period Year <span class="text-red-500">*</span></label>
                            <input type="number" name="period_year" value="{{ $year }}" min="2020" max="2030"
                                class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-slate-500 mb-1">Notes</label>
                        <textarea name="notes" rows="2"
                            class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400"
                            placeholder="Optional notes..."></textarea>
                    </div>
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 transition">
                        <i class="bi bi-check-circle"></i> Record IRD Deposit
                    </button>
                </form>
            </div>
        </div>

        {{-- Monthly Breakdown --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                    <i class="bi bi-bar-chart text-blue-500"></i> {{ $year }} Monthly Breakdown
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 dark:bg-slate-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Month</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Accrued</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Deposited</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Pending</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach($monthlyBreakdown as $row)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                            <td class="px-4 py-2.5 text-sm text-slate-800 dark:text-white font-medium">{{ $row['month'] }}</td>
                            <td class="px-4 py-2.5 text-sm text-right text-slate-600 dark:text-slate-300">Rs. {{ number_format($row['accrued'], 2) }}</td>
                            <td class="px-4 py-2.5 text-sm text-right text-emerald-600 dark:text-emerald-400">Rs. {{ number_format($row['deposited'], 2) }}</td>
                            <td class="px-4 py-2.5 text-sm text-right font-semibold {{ $row['pending'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-400 dark:text-slate-500' }}">
                                Rs. {{ number_format($row['pending'], 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Remittance History --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <i class="bi bi-clock-history text-slate-500"></i> Deposit History
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 dark:bg-slate-900/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Description</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($remittances as $r)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                        <td class="px-6 py-3 text-sm text-slate-600 dark:text-slate-300">{{ \Carbon\Carbon::parse($r->entry_date)->format('d M Y') }}</td>
                        <td class="px-6 py-3 text-sm text-slate-800 dark:text-white">{{ $r->description }}</td>
                        <td class="px-6 py-3 text-sm text-right font-semibold text-slate-900 dark:text-white">Rs. {{ number_format($r->debit, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">No deposits recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($remittances->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-700">{{ $remittances->links() }}</div>
        @endif
    </div>

</div>
@endsection
