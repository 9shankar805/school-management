@extends('layouts.app')
@section('title', 'Finance & Accounts Dashboard - NepalSchoolHub')

@push('head-scripts')
<style>
.iemis-stat-card {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 16px !important;
    padding: 14px 16px !important;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04) !important;
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    gap: 14px !important;
    text-decoration: none !important;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
    position: relative !important;
    overflow: hidden !important;
}
.iemis-stat-card:hover {
    box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08) !important;
    transform: translateY(-2px) !important;
    border-color: #cbd5e1 !important;
}
[data-theme="dark"] .iemis-stat-card,
html.dark .iemis-stat-card {
    background: #111927 !important;
    border-color: #223049 !important;
    box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.4) !important;
}
[data-theme="dark"] .iemis-stat-card:hover,
html.dark .iemis-stat-card:hover {
    border-color: #3b82f6 !important;
}
.iemis-icon-wrapper {
    font-size: 2.25rem !important;
    line-height: 1 !important;
    flex-shrink: 0 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}
.iemis-card-details {
    display: flex !important;
    flex-direction: column !important;
    justify-content: center !important;
    align-items: flex-start !important;
    flex: 1 !important;
    min-width: 0 !important;
}
.iemis-title {
    font-size: 11px !important;
    font-weight: 700 !important;
    color: #64748b !important;
    text-transform: uppercase !important;
    letter-spacing: 0.03em !important;
    line-height: 1.1 !important;
    margin-bottom: 2px !important;
}
[data-theme="dark"] .iemis-title,
html.dark .iemis-title {
    color: #94a3b8 !important;
}
.iemis-num-group {
    display: flex !important;
    align-items: baseline !important;
    gap: 6px !important;
}
.iemis-value {
    font-size: 20px !important;
    font-weight: 800 !important;
    color: #0f172a !important;
    line-height: 1.1 !important;
}
[data-theme="dark"] .iemis-value,
html.dark .iemis-value {
    color: #f8fafc !important;
}
.iemis-badge-green { font-size: 10px !important; font-weight: 700 !important; color: #10b981 !important; }
.iemis-badge-amber { font-size: 10px !important; font-weight: 700 !important; color: #f59e0b !important; }
.iemis-badge-rose  { font-size: 10px !important; font-weight: 700 !important; color: #ef4444 !important; }
.iemis-badge-indigo{ font-size: 10px !important; font-weight: 700 !important; color: #6366f1 !important; }
.iemis-badge-blue  { font-size: 10px !important; font-weight: 700 !important; color: #3b82f6 !important; }
</style>
@endpush

@section('content')

<div class="flex-1 p-4 sm:p-6 lg:p-8 overflow-auto bg-slate-50/60 dark:bg-slate-950 min-h-screen space-y-6">

    {{-- ── Page Header & Quick Action Row ────────────────────────────────── --}}
    <div class="flex flex-wrap justify-between items-center gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                Good {{ now()->hour < 12 ? 'Morning' : (now()->hour < 18 ? 'Afternoon' : 'Evening') }}, {{ auth()->user()->first_name }}! 👋
            </h1>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                {{ now()->format('l, F j, Y') }} &bull; {{ now()->format('g:i A') }}
                &bull; <span class="text-indigo-600 dark:text-indigo-400 font-bold">आ.व. {{ \App\Services\NepaliDateService::currentFiscalYear() }}</span>
                &bull; <span class="font-semibold text-slate-600 dark:text-slate-300">Finance & Billing Command</span>
            </p>
        </div>

        {{-- ── Action Row Buttons ────────────────────────────────────────── --}}
        <div class="flex flex-wrap items-center gap-2">
            @can('create invoices')
            <a href="{{ route('payments.create') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white text-xs font-bold rounded-xl shadow-sm transition active:scale-95">
                <i class="bi bi-plus-circle-fill text-xs"></i> New Invoice
            </a>

            <button type="button" onclick="document.getElementById('collectFeeModal').classList.remove('hidden')"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-xs font-bold rounded-xl shadow-sm transition active:scale-95 cursor-pointer">
                <i class="bi bi-currency-dollar text-xs"></i> Quick Collect
            </button>

            <button type="button" onclick="document.getElementById('bulkGenerateModal').classList.remove('hidden')"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold rounded-xl shadow-sm transition active:scale-95 cursor-pointer">
                <i class="bi bi-lightning-charge-fill text-xs"></i> Bulk Generate
            </button>
            @endcan

            @if(Route::has('fee-types.index'))
            <a href="{{ route('fee-types.index') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-semibold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 transition active:scale-95">
                <i class="bi bi-tags-fill text-sky-500 text-xs"></i> Categories
            </a>
            @endif

            <a href="{{ route('payments.index') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-semibold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 transition active:scale-95">
                <i class="bi bi-receipt text-indigo-500 text-xs"></i> Ledger
            </a>
        </div>
    </div>

    @if(session('status'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-xs font-semibold text-emerald-800 flex items-center gap-2 shadow-xs">
        <i class="bi bi-check-circle-fill text-emerald-600 text-base"></i> {{ session('status') }}
    </div>
    @endif
    @if(session('error'))
    <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-xs font-semibold text-rose-800 flex items-center gap-2 shadow-xs">
        <i class="bi bi-exclamation-circle-fill text-rose-600 text-base"></i> {{ session('error') }}
    </div>
    @endif

    {{-- ── 6 CEHRD IEMIS 3D Financial Stat Cards ────────────────────────── --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5 sm:gap-4">

        {{-- Card 1: Total Invoices --}}
        <a href="{{ route('payments.index') }}" class="iemis-stat-card">
            <div class="iemis-icon-wrapper">🧾</div>
            <div class="iemis-card-details">
                <div class="iemis-title">Invoices</div>
                <div class="iemis-num-group">
                    <span class="iemis-value">{{ number_format($totalInvoices) }}</span>
                    <span class="iemis-badge-indigo">Billed</span>
                </div>
            </div>
        </a>

        {{-- Card 2: This Month Collected --}}
        <div class="iemis-stat-card">
            <div class="iemis-icon-wrapper">💰</div>
            <div class="iemis-card-details">
                <div class="iemis-title">This Month</div>
                <div class="iemis-num-group">
                    <span class="iemis-value text-emerald-600 dark:text-emerald-400">Rs. {{ number_format($monthRevenue) }}</span>
                    <span class="iemis-badge-green">Recv</span>
                </div>
            </div>
        </div>

        {{-- Card 3: Outstanding Dues --}}
        <div class="iemis-stat-card">
            <div class="iemis-icon-wrapper">⚠️</div>
            <div class="iemis-card-details">
                <div class="iemis-title">Outstanding</div>
                <div class="iemis-num-group">
                    <span class="iemis-value text-rose-600 dark:text-rose-400">{{ number_format($unpaidInvoices) }}</span>
                    <span class="iemis-badge-rose">Unpaid</span>
                </div>
            </div>
        </div>

        {{-- Card 4: Collection Rate / Efficiency --}}
        <div class="iemis-stat-card">
            <div class="iemis-icon-wrapper">📈</div>
            <div class="iemis-card-details">
                <div class="iemis-title">Efficiency</div>
                <div class="iemis-num-group">
                    <span class="iemis-value">{{ $collectionRate }}%</span>
                    <span class="iemis-badge-blue">Rate</span>
                </div>
            </div>
        </div>

        {{-- Card 5: Paid Invoices --}}
        <div class="iemis-stat-card">
            <div class="iemis-icon-wrapper">✅</div>
            <div class="iemis-card-details">
                <div class="iemis-title">Settled</div>
                <div class="iemis-num-group">
                    <span class="iemis-value">{{ number_format($paidInvoices) }}</span>
                    <span class="iemis-badge-green">Paid</span>
                </div>
            </div>
        </div>

        {{-- Card 6: Yearly Revenue --}}
        <div class="iemis-stat-card">
            <div class="iemis-icon-wrapper">🏦</div>
            <div class="iemis-card-details">
                <div class="iemis-title">Year Total</div>
                <div class="iemis-num-group">
                    <span class="iemis-value text-sky-600 dark:text-sky-400">Rs. {{ number_format($yearRevenue) }}</span>
                    <span class="iemis-badge-amber">YTD</span>
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
    @php
        $mRevCol = collect($monthlyRevenue ?? []);
        $monthsArr = $mRevCol->pluck('month')->map(fn($m) => $m ? \Carbon\Carbon::create()->month((int)$m)->format('M') : '')->values()->toArray();
        $totalsArr = $mRevCol->pluck('total')->map(fn($v) => (float)$v)->values()->toArray();
        if (empty($monthsArr)) {
            $monthsArr = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
            $totalsArr = [0, 0, 0, 0, 0, 0];
        }
    @endphp
    var months = @json($monthsArr);
    var totals = @json($totalsArr);

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
