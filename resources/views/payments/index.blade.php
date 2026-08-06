@extends('layouts.app')
@section('content')
<div class="px-4 lg:px-8 py-6 max-w-7xl mx-auto">

    {{-- Header --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <span class="w-10 h-10 rounded-2xl bg-emerald-600/10 text-emerald-600 flex items-center justify-center text-xl shadow-xs">
                    <i class="bi bi-currency-exchange"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Invoices & Payments</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Manage student fee invoices and record payments</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('payments.create') }}" class="btn-primary text-sm px-4 py-2.5 rounded-xl">
                <i class="bi bi-plus-circle mr-1"></i> Create Invoice
            </a>
            <button onclick="document.getElementById('bulkModal').classList.remove('hidden')"
                    class="btn-secondary text-sm px-4 py-2.5 rounded-xl">
                <i class="bi bi-lightning-charge mr-1"></i> Bulk Generate
            </button>
            <a href="{{ route('finance.overview') }}" class="btn-secondary text-sm px-4 py-2.5 rounded-xl">
                <i class="bi bi-graph-up mr-1"></i> Finance Overview
            </a>
        </div>
    </div>

    @include('session-messages')

    {{-- Filters --}}
    <form method="GET" class="mb-4 flex flex-wrap gap-2 items-end">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search student, invoice #…"
               class="input-modern text-sm w-56">
        <select name="status" class="select-modern text-sm w-32">
            <option value="">All Status</option>
            <option value="unpaid"  {{ request('status') === 'unpaid'  ? 'selected' : '' }}>Unpaid</option>
            <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial</option>
            <option value="paid"    {{ request('status') === 'paid'    ? 'selected' : '' }}>Paid</option>
        </select>
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="input-modern text-sm">
        <input type="date" name="date_to"   value="{{ request('date_to') }}"   class="input-modern text-sm">
        <button type="submit" class="btn-primary text-sm px-4 py-2 rounded-xl">Filter</button>
        <a href="{{ route('payments.index') }}" class="btn-secondary text-sm px-3 py-2 rounded-xl">Clear</a>
    </form>

    {{-- Invoice table --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-xs text-slate-500 dark:text-slate-400">
                    <tr>
                        <th class="text-left px-5 py-3">Invoice #</th>
                        <th class="text-left px-5 py-3">Student</th>
                        <th class="text-left px-5 py-3">Title</th>
                        <th class="text-right px-5 py-3">Amount</th>
                        <th class="text-right px-5 py-3">Paid</th>
                        <th class="text-right px-5 py-3">Due</th>
                        <th class="text-center px-5 py-3">Status</th>
                        <th class="text-left px-5 py-3">Due Date</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                    <tr class="border-t border-slate-100 dark:border-slate-800 hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                        <td class="px-5 py-3">
                            <span class="font-mono text-xs text-indigo-600 dark:text-indigo-400">{{ $invoice->invoice_number ?? '—' }}</span>
                            @if($invoice->is_auto_generated)
                            <span class="ml-1 text-xs text-slate-400" title="Auto-generated"><i class="bi bi-lightning-charge"></i></span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <a href="{{ route('payments.student-ledger', $invoice->student_id) }}"
                               class="font-medium text-slate-700 dark:text-slate-200 hover:text-indigo-600 transition">
                                {{ $invoice->student?->full_name ?? '—' }}
                            </a>
                            @if($invoice->student?->admission_no)
                            <div class="text-xs text-slate-400">{{ $invoice->student->admission_no }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-300">{{ $invoice->title }}</td>
                        <td class="px-5 py-3 text-right font-semibold">Rs. {{ number_format($invoice->amount, 2) }}</td>
                        <td class="px-5 py-3 text-right text-emerald-600">Rs. {{ number_format($invoice->amount_paid, 2) }}</td>
                        <td class="px-5 py-3 text-right {{ $invoice->amount_due > 0 ? 'text-rose-600 font-semibold' : 'text-slate-400' }}">
                            Rs. {{ number_format($invoice->amount_due, 2) }}
                        </td>
                        <td class="px-5 py-3 text-center">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                                {{ $invoice->status === 'paid' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' :
                                   ($invoice->status === 'partial' ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700') }}">
                                {{ ucfirst($invoice->status) }}
                                @if($invoice->is_overdue) ⚠ @endif
                            </span>
                        </td>
                        <td class="px-5 py-3 {{ $invoice->is_overdue ? 'text-rose-600' : 'text-slate-500' }}">
                            {{ $invoice->due_date?->format('d M Y') ?? '—' }}
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-1.5 justify-end">
                                <a href="{{ route('payments.show', $invoice->id) }}" class="text-slate-400 hover:text-indigo-600 transition" title="View">
                                    <i class="bi bi-eye text-sm"></i>
                                </a>
                                @if($invoice->status !== 'paid')
                                <a href="{{ route('payments.pay', $invoice->id) }}" class="text-slate-400 hover:text-emerald-600 transition" title="Pay">
                                    <i class="bi bi-cash-coin text-sm"></i>
                                </a>
                                @endif
                                @if($invoice->payments->isNotEmpty())
                                <a href="{{ route('finance.receipt', $invoice->payments->last()->id) }}"
                                   class="text-slate-400 hover:text-blue-600 transition" title="Receipt" target="_blank">
                                    <i class="bi bi-receipt text-sm"></i>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="px-5 py-12 text-center text-slate-400">No invoices found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
        <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800">
            {{ $invoices->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Bulk Generate Modal --}}
<div id="bulkModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">
                <i class="bi bi-lightning-charge mr-2 text-amber-500"></i> Bulk Invoice Generation
            </h2>
            <button onclick="document.getElementById('bulkModal').classList.add('hidden')"
                    class="text-slate-400 hover:text-slate-600 transition">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <p class="text-xs text-slate-500 mb-4">Generates monthly fee invoices for ALL active students based on their class fee structures. Existing invoices for the same period are skipped.</p>
        <form method="POST" action="{{ route('payments.bulk-generate') }}">
            @csrf
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Month <span class="text-rose-500">*</span></label>
                    <select name="month" class="select-modern w-full text-sm" required>
                        @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $m === now()->month ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                        </option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Year <span class="text-rose-500">*</span></label>
                    <input type="number" name="year" value="{{ now()->year }}" min="2020" max="2100"
                           class="input-modern w-full text-sm" required>
                </div>
            </div>
            <div class="bg-amber-50 dark:bg-amber-900/20 rounded-xl p-3 text-xs text-amber-700 dark:text-amber-300 mb-4">
                <i class="bi bi-exclamation-triangle mr-1"></i>
                This will generate invoices based on fee structures. This process is idempotent — re-running will not create duplicates.
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn-primary flex-1 py-2.5 text-sm rounded-xl">
                    <i class="bi bi-lightning-charge mr-1"></i> Generate Invoices
                </button>
                <button type="button" onclick="document.getElementById('bulkModal').classList.add('hidden')"
                        class="btn-secondary px-5 text-sm rounded-xl">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endsection
