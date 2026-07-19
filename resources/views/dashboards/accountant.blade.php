@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">
        @include('layouts.left-menu')
    </div>

    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Finance Dashboard</h1>
                <p class="text-slate-400 text-sm mt-0.5">{{ now()->format('l, F j, Y') }}</p>
            </div>
            <div class="flex gap-2">
                @can('create invoices')
                <a href="{{ route('payments.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                    <i class="bi bi-receipt"></i> New Invoice
                </a>
                @endcan
                <a href="{{ route('payments.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 text-sm font-medium rounded-lg hover:bg-slate-50 transition text-slate-700">
                    <i class="bi bi-list-ul"></i> All Payments
                </a>
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Total Invoices</p>
                    <span class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-600 text-sm"><i class="bi bi-file-earmark-text"></i></span>
                </div>
                <p class="text-3xl font-bold text-slate-800">{{ number_format($totalInvoices) }}</p>
                <p class="mt-1 text-xs text-slate-400">All time</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-rose-50 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-rose-400 uppercase tracking-wide">Unpaid</p>
                    <span class="w-8 h-8 rounded-lg bg-rose-50 flex items-center justify-center text-rose-600 text-sm"><i class="bi bi-exclamation-circle"></i></span>
                </div>
                <p class="text-3xl font-bold text-rose-600">{{ number_format($unpaidInvoices) }}</p>
                <p class="mt-1 text-xs text-rose-500">Pending collection</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">This Month</p>
                    <span class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600 text-sm"><i class="bi bi-calendar-month"></i></span>
                </div>
                <p class="text-3xl font-bold text-emerald-600">${{ number_format($monthRevenue) }}</p>
                <p class="mt-1 text-xs text-emerald-500">{{ now()->format('F Y') }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">This Year</p>
                    <span class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 text-sm"><i class="bi bi-graph-up-arrow"></i></span>
                </div>
                <p class="text-3xl font-bold text-slate-800">${{ number_format($yearRevenue) }}</p>
                <p class="mt-1 text-xs text-slate-400">{{ now()->year }}</p>
            </div>
        </div>

        {{-- Revenue Chart --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-6">
            <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-bar-chart-fill me-1 text-emerald-500"></i>Monthly Revenue — {{ now()->year }}</p>
            <div id="chart-monthly-revenue" style="min-height:200px"></div>
        </div>

        {{-- Overdue Invoices + Recent Payments --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            {{-- Overdue --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 flex justify-between items-center">
                    <p class="text-sm font-semibold text-slate-700"><i class="bi bi-exclamation-triangle me-1 text-rose-500"></i>Overdue Invoices</p>
                    <span class="text-xs bg-rose-100 text-rose-700 px-2 py-0.5 rounded-full font-medium">{{ $overdueInvoices->count() }}</span>
                </div>
                @if($overdueInvoices->count())
                <div class="divide-y divide-slate-50">
                    @foreach($overdueInvoices as $inv)
                    <div class="px-5 py-3 flex justify-between items-center">
                        <div>
                            <p class="text-sm font-medium text-slate-700">{{ $inv->student?->full_name ?? '—' }}</p>
                            <p class="text-xs text-slate-400">{{ $inv->title }} · Due {{ \Carbon\Carbon::parse($inv->due_date)->format('M d, Y') }}</p>
                        </div>
                        <span class="text-sm font-semibold text-rose-600">${{ number_format($inv->amount) }}</span>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-400 text-center py-8">No overdue invoices.</p>
                @endif
            </div>

            {{-- Recent Payments --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 flex justify-between items-center">
                    <p class="text-sm font-semibold text-slate-700"><i class="bi bi-credit-card me-1 text-indigo-500"></i>Recent Payments</p>
                    <a href="{{ route('payments.index') }}" class="text-xs text-indigo-600 hover:underline">View all</a>
                </div>
                @if($recentPayments->count())
                <div class="divide-y divide-slate-50">
                    @foreach($recentPayments as $p)
                    @php $student = $p->invoice?->student; @endphp
                    <div class="px-5 py-3 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2 min-w-0">
                            @if($student)<img src="{{ $student->avatar }}" class="w-7 h-7 rounded-full object-cover flex-shrink-0" alt="">@endif
                            <p class="text-sm text-slate-700 truncate">{{ $student?->full_name ?? '—' }}</p>
                        </div>
                        <div class="flex-shrink-0 text-right">
                            <p class="text-sm font-semibold text-emerald-600">${{ number_format($p->amount_paid) }}</p>
                            <p class="text-xs text-slate-400">{{ $p->created_at->format('M d') }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-400 text-center py-8">No payments recorded.</p>
                @endif
            </div>
        </div>

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
            chart: { type: 'area', height: 200, toolbar: { show: false } },
            series: [{ name: 'Revenue ($)', data: totals }],
            xaxis: { categories: months, labels: { style: { fontSize: '11px' } } },
            yaxis: { labels: { style: { fontSize: '11px' }, formatter: v => '$' + (v >= 1000 ? (v/1000).toFixed(1)+'k' : v) } },
            colors: ['#22c55e'],
            fill: { type: 'gradient', gradient: { opacityFrom: 0.5, opacityTo: 0.05 } },
            stroke: { curve: 'smooth', width: 2 },
            dataLabels: { enabled: false },
            grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
            tooltip: { y: { formatter: v => '$' + Number(v).toLocaleString() } },
        }).render();
    }
})();
</script>
@endpush
