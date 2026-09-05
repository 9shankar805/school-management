@extends('layouts.app')

@section('content')

<div class="flex-1 p-6 lg:p-8 overflow-auto">

    {{-- ── Page Header & Quick Action Row ────────────────────────────────── --}}
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-8 gap-4 pb-6 border-b border-slate-200 dark:border-slate-700">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Finance Command Center
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                    BS 2081–82
                </span>
            </div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight flex items-center gap-2">
                Finance Dashboard
            </h1>
            <p class="text-slate-400 text-xs mt-0.5">{{ now()->format('l, F j, Y') }} &middot; Real-time billing & ledger operations</p>
        </div>

        {{-- ── Action Row Buttons ────────────────────────────────────────── --}}
        <div class="flex flex-wrap items-center gap-2.5 w-full xl:w-auto">
            @can('create invoices')
            <a href="{{ route('payments.create') }}"
               style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: #ffffff !important;"
               class="inline-flex items-center gap-2 px-4 py-2.5 text-white text-xs font-black rounded-xl shadow-sm transition-all transform hover:-translate-y-0.5 cursor-pointer">
                <i class="bi bi-plus-circle-fill text-sm"></i> New Invoice
            </a>

            <button type="button" onclick="document.getElementById('collectFeeModal').classList.remove('hidden')"
               style="background: linear-gradient(135deg, #059669 0%, #0d9488 100%); color: #ffffff !important;"
               class="inline-flex items-center gap-2 px-4 py-2.5 text-white text-xs font-black rounded-xl shadow-sm transition-all transform hover:-translate-y-0.5 cursor-pointer">
                <i class="bi bi-currency-dollar text-sm"></i> Quick Collect Fee
            </button>

            <button type="button" onclick="document.getElementById('bulkGenerateModal').classList.remove('hidden')"
               style="background: linear-gradient(135deg, #d97706 0%, #ea580c 100%); color: #ffffff !important;"
               class="inline-flex items-center gap-2 px-4 py-2.5 text-white text-xs font-black rounded-xl shadow-sm transition-all transform hover:-translate-y-0.5 cursor-pointer">
                <i class="bi bi-lightning-charge-fill text-sm"></i> Bulk Generate Fees
            </button>
            @endcan

            @if(Route::has('fee-types.index'))
            <a href="{{ route('fee-types.index') }}"
               style="background: #ffffff; border: 1.5px solid #e2e8f0; color: #1e293b !important;"
               class="inline-flex items-center gap-2 px-3.5 py-2.5 text-xs font-bold rounded-xl shadow-xs transition hover:bg-slate-50">
                <i class="bi bi-tags-fill text-sky-500"></i> Fee Categories
            </a>
            @endif

            <a href="{{ route('payments.index') }}"
               style="background: #ffffff; border: 1.5px solid #e2e8f0; color: #1e293b !important;"
               class="inline-flex items-center gap-2 px-3.5 py-2.5 text-xs font-bold rounded-xl shadow-xs transition hover:bg-slate-50">
                <i class="bi bi-receipt text-indigo-500"></i> All Invoices
            </a>

            <a href="{{ route('payments.index') }}"
               style="background: #1e293b; color: #ffffff !important;"
               class="inline-flex items-center gap-2 px-3.5 py-2.5 text-white text-xs font-bold rounded-xl shadow-xs transition hover:bg-slate-900">
                <i class="bi bi-credit-card-fill text-emerald-400"></i> All Payments
            </a>
        </div>
    </div>

    @if(session('status'))
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-xs font-semibold text-emerald-800 flex items-center gap-2 shadow-xs">
        <i class="bi bi-check-circle-fill text-emerald-600 text-base"></i> {{ session('status') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-2xl text-xs font-semibold text-rose-800 flex items-center gap-2 shadow-xs">
        <i class="bi bi-exclamation-circle-fill text-rose-600 text-base"></i> {{ session('error') }}
    </div>
    @endif

    {{-- ── KPI Financial Stat Cards ──────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">

        {{-- Total Invoices --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-100 dark:border-slate-700 shadow-xs flex flex-col justify-between hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 dark:text-slate-400 uppercase tracking-wider">Total Invoices</span>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-lg">
                    <i class="bi bi-file-earmark-text-fill"></i>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-3xl font-black text-slate-800 dark:text-slate-100 tracking-tight">{{ number_format($totalInvoices) }}</p>
                <div class="flex items-center justify-between mt-2 text-xs text-slate-400">
                    <span>Total Billed:</span>
                    <span class="font-bold text-slate-700 dark:text-slate-200">Rs. {{ number_format($totalBilled) }}</span>
                </div>
            </div>
        </div>

        {{-- Pending Collection / Unpaid --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-rose-100 dark:border-rose-900/40 shadow-xs flex flex-col justify-between hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-rose-500 dark:text-rose-400 uppercase tracking-wider">Pending Collection</span>
                <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-900/30 flex items-center justify-center text-rose-600 dark:text-rose-400 text-lg">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-3xl font-black text-rose-600 dark:text-rose-400 tracking-tight">{{ number_format($unpaidInvoices) }} <span class="text-xs font-normal text-rose-400">Invoices</span></p>
                <div class="flex items-center justify-between mt-2 text-xs text-rose-500">
                    <span>Outstanding Dues:</span>
                    <span class="font-bold text-rose-700 dark:text-rose-300">Rs. {{ number_format($totalOutstanding) }}</span>
                </div>
            </div>
        </div>

        {{-- This Month Collection --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-emerald-100 dark:border-emerald-900/40 shadow-xs flex flex-col justify-between hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">This Month Collected</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-lg">
                    <i class="bi bi-calendar-check-fill"></i>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-3xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight">Rs. {{ number_format($monthRevenue) }}</p>
                <div class="flex items-center justify-between mt-2 text-xs text-emerald-600">
                    <span>Month: {{ now()->format('F Y') }}</span>
                    <span class="font-bold bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded text-[10px]">Active</span>
                </div>
            </div>
        </div>

        {{-- Yearly Collection & Efficiency Rate --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-100 dark:border-slate-700 shadow-xs flex flex-col justify-between hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 dark:text-slate-400 uppercase tracking-wider">Yearly Collection</span>
                <div class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-900/30 flex items-center justify-center text-sky-600 dark:text-sky-400 text-lg">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-3xl font-black text-slate-800 dark:text-slate-100 tracking-tight">Rs. {{ number_format($yearRevenue) }}</p>
                <div class="flex items-center justify-between mt-2 text-xs text-slate-400">
                    <span>Collection Efficiency:</span>
                    <span class="font-bold text-sky-600 dark:text-sky-400">{{ $collectionRate }}%</span>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Monthly Revenue Chart ──────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-xs p-6 mb-8">
        <div class="flex flex-wrap justify-between items-center mb-5 gap-2">
            <div>
                <h2 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                    <i class="bi bi-bar-chart-fill text-emerald-500"></i> Monthly Fee Collection & Revenue Trend ({{ now()->year }})
                </h2>
                <p class="text-xs text-slate-400">Track total revenue collected by month</p>
            </div>
            <span class="text-xs font-semibold px-3 py-1 bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 rounded-full">
                Rs. {{ number_format($yearRevenue) }} Total YTD
            </span>
        </div>
        <div id="chart-monthly-revenue" style="min-height:230px"></div>
    </div>

    {{-- ── Overdue Invoices & Recent Payments Split View ────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

        {{-- Overdue Invoices --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-xs overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill text-rose-500"></i> Overdue Fee Defaulters
                    </h3>
                    <p class="text-xs text-slate-400">Unpaid invoices past due date</p>
                </div>
                <span class="text-xs bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-300 px-2.5 py-1 rounded-full font-bold">
                    {{ $overdueInvoices->count() }} Defaulters
                </span>
            </div>

            @if($overdueInvoices->count())
            <div class="divide-y divide-slate-100 dark:divide-slate-700/60 overflow-y-auto max-h-[360px]">
                @foreach($overdueInvoices as $inv)
                <div class="px-6 py-3.5 flex justify-between items-center hover:bg-slate-50 dark:hover:bg-slate-700/40 transition">
                    <div>
                        <p class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ $inv->student?->full_name ?? '—' }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">
                            {{ $inv->title }} &middot; <span class="text-rose-500 font-semibold">Due {{ \Carbon\Carbon::parse($inv->due_date)->format('M d, Y') }}</span>
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-black text-rose-600 dark:text-rose-400">Rs. {{ number_format($inv->amount) }}</span>
                        <a href="{{ route('payments.show', $inv->id) }}"
                           class="px-3 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300 rounded-lg text-xs font-semibold transition">
                           Collect
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="p-8 text-center text-slate-400 text-xs">
                <i class="bi bi-shield-check text-2xl text-emerald-500 block mb-2"></i>
                No overdue fee defaulters at this time.
            </div>
            @endif
        </div>

        {{-- Recent Payments Ledger --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-xs overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <i class="bi bi-credit-card-fill text-emerald-500"></i> Recent Payment Receipts
                    </h3>
                    <p class="text-xs text-slate-400">Latest fee transactions recorded</p>
                </div>
                <a href="{{ route('payments.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-bold">
                    View Ledger &rarr;
                </a>
            </div>

            @if($recentPayments->count())
            <div class="divide-y divide-slate-100 dark:divide-slate-700/60 overflow-y-auto max-h-[360px]">
                @foreach($recentPayments as $p)
                @php $student = $p->invoice?->student; @endphp
                <div class="px-6 py-3.5 flex items-center justify-between gap-3 hover:bg-slate-50 dark:hover:bg-slate-700/40 transition">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 flex items-center justify-center font-bold text-xs flex-shrink-0">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-slate-800 dark:text-slate-100 truncate">{{ $student?->full_name ?? '—' }}</p>
                            <p class="text-xs text-slate-400 truncate">{{ $p->invoice?->title ?? 'Fee Payment' }} &middot; {{ $p->payment_method ?? 'Cash/Online' }}</p>
                        </div>
                    </div>
                    <div class="flex-shrink-0 text-right">
                        <p class="text-sm font-black text-emerald-600 dark:text-emerald-400">+ Rs. {{ number_format($p->amount) }}</p>
                        <p class="text-[10px] text-slate-400">{{ $p->created_at->format('M d, H:i') }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="p-8 text-center text-slate-400 text-xs">
                <i class="bi bi-receipt text-2xl text-slate-300 block mb-2"></i>
                No recent payment transactions recorded.
            </div>
            @endif
        </div>

    </div>

</div>

{{-- ── MODAL 1: BULK GENERATE FEES ──────────────────────────────────────── --}}
<div id="bulkGenerateModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-5 border border-slate-100 dark:border-slate-700">
        <div class="flex justify-between items-center pb-3 border-b border-slate-100 dark:border-slate-700">
            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <i class="bi bi-lightning-charge-fill text-amber-500"></i> Bulk Generate Class Fee Invoices
            </h3>
            <button type="button" onclick="document.getElementById('bulkGenerateModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
        </div>

        <form method="POST" action="{{ route('payments.bulk-generate') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Target Class <span class="text-rose-500">*</span></label>
                <select name="class_id" required class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100">
                    <option value="">— Select Class —</option>
                    @foreach($classes ?? [] as $c)
                    <option value="{{ $c->id }}">{{ $c->class_name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Fee Category <span class="text-rose-500">*</span></label>
                <select name="fee_type_id" required class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100">
                    <option value="">— Select Fee Type —</option>
                    @foreach($feeTypes ?? [] as $ft)
                    <option value="{{ $ft->id }}">{{ $ft->name }} (Rs. {{ number_format($ft->amount) }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Amount (Rs.) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" name="amount" required placeholder="e.g. 2500"
                           class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Due Date <span class="text-rose-500">*</span></label>
                    <input type="date" name="due_date" value="{{ now()->addDays(15)->toDateString() }}" required
                           class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100">
                </div>
            </div>

            <div class="pt-3 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('bulkGenerateModal').classList.add('hidden')"
                        class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold hover:bg-slate-200 transition">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold shadow-xs transition">
                    Generate Bulk Invoices
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── MODAL 2: QUICK COLLECT FEE ──────────────────────────────────────── --}}
<div id="collectFeeModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-5 border border-slate-100 dark:border-slate-700">
        <div class="flex justify-between items-center pb-3 border-b border-slate-100 dark:border-slate-700">
            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <i class="bi bi-currency-dollar text-emerald-500"></i> Quick Student Fee Collection
            </h3>
            <button type="button" onclick="document.getElementById('collectFeeModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
        </div>

        <form method="GET" action="{{ route('payments.create') }}" class="space-y-4">
            <p class="text-xs text-slate-400">Create a quick fee payment entry or student invoice receipt.</p>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('collectFeeModal').classList.add('hidden')"
                        class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold hover:bg-slate-200 transition">Close</button>
                <a href="{{ route('payments.create') }}" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs transition inline-flex items-center gap-1.5">
                    Open Payment Collector &rarr;
                </a>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    var months = @json($monthlyRevenue->pluck('month')->map(fn($m) => \Carbon\Carbon::create()->month((int)$m)->format('M')));
    var totals = @json($monthlyRevenue->pluck('total')->map(fn($v) => (float)$v));

    if (document.getElementById('chart-monthly-revenue')) {
        new ApexCharts(document.getElementById('chart-monthly-revenue'), {
            chart: { type: 'area', height: 230, toolbar: { show: false } },
            series: [{ name: 'Revenue (Rs.)', data: totals }],
            xaxis: { categories: months, labels: { style: { fontSize: '11px', colors: '#64748b' } } },
            yaxis: { labels: { style: { fontSize: '11px', colors: '#64748b' }, formatter: v => 'Rs. ' + Number(v).toLocaleString() } },
            colors: ['#10b981'],
            fill: { type: 'gradient', gradient: { opacityFrom: 0.45, opacityTo: 0.05 } },
            stroke: { curve: 'smooth', width: 2.5 },
            dataLabels: { enabled: false },
            grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
            tooltip: { y: { formatter: v => 'Rs. ' + Number(v).toLocaleString() } },
        }).render();
    }
})();
</script>
@endpush
