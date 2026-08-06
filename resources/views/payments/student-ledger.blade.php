@extends('layouts.app')
@section('content')
<div class="px-4 lg:px-8 py-6 max-w-5xl mx-auto">

    {{-- Header --}}
    <div class="mb-6 flex items-start justify-between gap-4 flex-wrap">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <div class="w-10 h-10 rounded-2xl bg-indigo-600/10 text-indigo-600 flex items-center justify-center font-bold text-xl">
                    <i class="bi bi-journal-text"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Fee Ledger</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $student->full_name }} · {{ $student->admission_no ?? 'No Admission No.' }}</p>
                </div>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('payments.index') }}" class="btn-secondary text-sm px-4 py-2 rounded-xl">
                <i class="bi bi-arrow-left mr-1"></i> Back
            </a>
        </div>
    </div>

    @include('session-messages')

    {{-- KPI row --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        @php $kpis = [
            ['label' => 'Total Charged',   'val' => 'Rs. '.number_format($totalCharged, 2), 'color' => 'rose',    'icon' => 'bi-file-earmark-text'],
            ['label' => 'Total Paid',      'val' => 'Rs. '.number_format($totalPaid, 2),    'color' => 'emerald', 'icon' => 'bi-check-circle'],
            ['label' => 'Current Balance', 'val' => 'Rs. '.number_format($balance, 2),      'color' => $balance > 0 ? 'amber' : 'emerald', 'icon' => 'bi-wallet2'],
        ]; @endphp
        @foreach($kpis as $k)
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 flex items-center gap-3">
            <span class="w-10 h-10 rounded-xl bg-{{ $k['color'] }}-500/10 text-{{ $k['color'] }}-600 flex items-center justify-center">
                <i class="bi {{ $k['icon'] }} text-lg"></i>
            </span>
            <div>
                <div class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ $k['val'] }}</div>
                <div class="text-xs text-slate-500">{{ $k['label'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Ledger table --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800">
            <h2 class="text-sm font-bold text-slate-700 dark:text-slate-200">Transaction History</h2>
            <p class="text-xs text-slate-400 mt-0.5">Append-only ledger — every charge and payment is recorded here.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-xs text-slate-500 dark:text-slate-400">
                    <tr>
                        <th class="text-left px-5 py-3">Date</th>
                        <th class="text-left px-5 py-3">Type</th>
                        <th class="text-left px-5 py-3">Description</th>
                        <th class="text-right px-5 py-3">Debit</th>
                        <th class="text-right px-5 py-3">Credit</th>
                        <th class="text-right px-5 py-3">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ledger as $entry)
                    <tr class="border-t border-slate-100 dark:border-slate-800 hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                        <td class="px-5 py-3 text-slate-500 whitespace-nowrap">{{ $entry->entry_date->format('d M Y') }}</td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                {{ match($entry->type) {
                                    'invoice'    => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300',
                                    'payment'    => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                                    'discount'   => 'bg-blue-100 text-blue-700',
                                    'fine'       => 'bg-orange-100 text-orange-700',
                                    'refund'     => 'bg-purple-100 text-purple-700',
                                    'opening'    => 'bg-slate-100 text-slate-600',
                                    default      => 'bg-slate-100 text-slate-600',
                                } }}">
                                {{ ucfirst($entry->type) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-slate-700 dark:text-slate-200">{{ $entry->description }}</td>
                        <td class="px-5 py-3 text-right {{ $entry->debit > 0 ? 'text-rose-600 font-semibold' : 'text-slate-300 dark:text-slate-600' }}">
                            {{ $entry->debit > 0 ? 'Rs. '.number_format($entry->debit, 2) : '—' }}
                        </td>
                        <td class="px-5 py-3 text-right {{ $entry->credit > 0 ? 'text-emerald-600 font-semibold' : 'text-slate-300 dark:text-slate-600' }}">
                            {{ $entry->credit > 0 ? 'Rs. '.number_format($entry->credit, 2) : '—' }}
                        </td>
                        <td class="px-5 py-3 text-right font-bold {{ $entry->balance > 0 ? 'text-amber-600' : ($entry->balance < 0 ? 'text-emerald-600' : 'text-slate-500') }}">
                            Rs. {{ number_format($entry->balance, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-slate-400">No transactions yet.</td></tr>
                    @endforelse
                </tbody>
                @if($ledger->isNotEmpty())
                <tfoot class="bg-slate-50 dark:bg-slate-800/40 border-t-2 border-slate-200 dark:border-slate-700 font-semibold text-sm">
                    <tr>
                        <td colspan="3" class="px-5 py-4 text-right text-slate-600 dark:text-slate-300">Totals</td>
                        <td class="px-5 py-4 text-right text-rose-600">Rs. {{ number_format($totalCharged, 2) }}</td>
                        <td class="px-5 py-4 text-right text-emerald-600">Rs. {{ number_format($totalPaid, 2) }}</td>
                        <td class="px-5 py-4 text-right {{ $balance > 0 ? 'text-amber-600' : 'text-emerald-600' }}">
                            Rs. {{ number_format($balance, 2) }}
                            <div class="text-xs font-normal text-slate-400">{{ $balance > 0 ? 'Due' : ($balance < 0 ? 'Advance' : 'Cleared') }}</div>
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
