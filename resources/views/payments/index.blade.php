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
                                {{-- Fee Demand Slip --}}
                                <a href="{{ route('payments.demand-slip', $invoice->id) }}"
                                   class="text-slate-400 hover:text-teal-600 transition" title="Print Fee Demand Slip (शुल्क माग भौचर)" target="_blank">
                                    <i class="bi bi-receipt-cutoff text-sm"></i>
                                </a>
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

{{-- Bulk Generate Modal (Nepali Bikram Sambat & Range Capable) --}}
<div id="bulkModal" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl w-full max-w-lg p-6 border border-slate-200 dark:border-slate-800">
        <div class="flex items-center justify-between mb-3 pb-3 border-b border-slate-100 dark:border-slate-800">
            <h2 class="text-base font-extrabold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <i class="bi bi-calendar-range-fill text-indigo-600"></i> Bulk Fee Generation (नेपाली क्यालेन्डर)
            </h2>
            <button onclick="document.getElementById('bulkModal').classList.add('hidden')"
                    class="text-slate-400 hover:text-slate-600 transition">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <p class="text-xs text-slate-500 mb-4">Generate fee statements for all active students. Select a single month or multi-month advance period (e.g. Baisakh to Ashadh, or Baisakh to Chaitra yearly).</p>
        <form method="POST" action="{{ route('payments.bulk-generate') }}">
            @csrf

            {{-- BS Year & Months Range --}}
            <div class="grid grid-cols-3 gap-3 mb-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">BS Year (वि.सं.) <span class="text-rose-500">*</span></label>
                    <input type="number" name="year" value="{{ \App\Services\NepaliDateService::currentBsYear() }}" min="2000" max="2100"
                           class="input-modern w-full text-xs font-bold" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">From Month (देखि) <span class="text-rose-500">*</span></label>
                    <select name="month" id="bulk_from_month" class="select-modern w-full text-xs font-bold" required>
                        @foreach(\App\Services\NepaliDateService::getBsMonths(true) as $mNum => $mName)
                        <option value="{{ $mNum }}" {{ $mNum === \App\Services\NepaliDateService::currentBsMonth() ? 'selected' : '' }}>
                            {{ $mNum }}. {{ $mName }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">To Month (सम्म)</label>
                    <select name="month_to" id="bulk_to_month" class="select-modern w-full text-xs font-bold">
                        @foreach(\App\Services\NepaliDateService::getBsMonths(true) as $mNum => $mName)
                        <option value="{{ $mNum }}" {{ $mNum === \App\Services\NepaliDateService::currentBsMonth() ? 'selected' : '' }}>
                            {{ $mNum }}. {{ $mName }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Quick Academic Term Presets --}}
            <div class="mb-4">
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Academic Presets</label>
                <div class="flex flex-wrap gap-1.5">
                    <button type="button" onclick="setBulkRange(1, 3)" class="text-[11px] font-bold px-2.5 py-1 rounded-lg bg-teal-50 text-teal-800 dark:bg-teal-950/60 dark:text-teal-300 border border-teal-200 hover:bg-teal-100 transition">
                        बैशाख – असार (1st Term)
                    </button>
                    <button type="button" onclick="setBulkRange(4, 6)" class="text-[11px] font-bold px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200 hover:bg-indigo-100 transition">
                        साउन – असोज (2nd Term)
                    </button>
                    <button type="button" onclick="setBulkRange(7, 9)" class="text-[11px] font-bold px-2.5 py-1 rounded-lg bg-purple-50 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200 hover:bg-purple-100 transition">
                        कात्तिक – पुस (3rd Term)
                    </button>
                    <button type="button" onclick="setBulkRange(10, 12)" class="text-[11px] font-bold px-2.5 py-1 rounded-lg bg-amber-50 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 hover:bg-amber-100 transition">
                        माघ – चैत (4th Term)
                    </button>
                    <button type="button" onclick="setBulkRange(1, 12)" class="text-[11px] font-bold px-2.5 py-1 rounded-lg bg-rose-50 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 hover:bg-rose-100 transition">
                        बैशाख – चैत्र (Full Academic Year / वार्षिक)
                    </button>
                </div>
            </div>

            {{-- Advance Payment Discount/Concession (Optional) --}}
            <div class="mb-4">
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Advance Concession / Discount per Student (Rs. Optional)</label>
                <input type="number" step="0.01" name="advance_discount" placeholder="0.00" class="input-modern w-full text-xs font-bold">
            </div>

            <div class="bg-amber-50 dark:bg-amber-900/20 rounded-xl p-3 text-xs text-amber-800 dark:text-amber-300 mb-4 border border-amber-200 dark:border-amber-800/50">
                <i class="bi bi-info-circle-fill mr-1"></i>
                Multi-month selections will multiply recurring monthly fees by the number of months and pull past unpaid arrears into Previous Dues.
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn-primary flex-1 py-2.5 text-xs font-extrabold rounded-xl">
                    <i class="bi bi-lightning-charge-fill mr-1"></i> Generate Statements
                </button>
                <button type="button" onclick="document.getElementById('bulkModal').classList.add('hidden')"
                        class="btn-secondary px-5 text-xs font-bold rounded-xl">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function setBulkRange(from, to) {
    document.getElementById('bulk_from_month').value = from;
    document.getElementById('bulk_to_month').value = to;
}
</script>
@endsection
