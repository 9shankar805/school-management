@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">
        @include('layouts.left-menu')
    </div>

    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Organization Dashboard</h1>
                <p class="text-slate-400 text-sm mt-0.5">{{ now()->format('l, F j, Y') }}</p>
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Students</p>
                    <span class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 text-sm"><i class="bi bi-people-fill"></i></span>
                </div>
                <p class="text-3xl font-bold text-slate-800">{{ number_format($studentCount) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Teachers</p>
                    <span class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 text-sm"><i class="bi bi-person-badge-fill"></i></span>
                </div>
                <p class="text-3xl font-bold text-slate-800">{{ number_format($teacherCount) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Revenue / Month</p>
                    <span class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600 text-sm"><i class="bi bi-currency-dollar"></i></span>
                </div>
                <p class="text-3xl font-bold text-emerald-600">${{ number_format($monthRevenue) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-rose-50 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-rose-400 uppercase tracking-wide">Pending Fees</p>
                    <span class="w-8 h-8 rounded-lg bg-rose-50 flex items-center justify-center text-rose-600 text-sm"><i class="bi bi-receipt"></i></span>
                </div>
                <p class="text-3xl font-bold text-rose-600">{{ number_format($pendingInvoices) }}</p>
            </div>
        </div>

        {{-- Revenue + Attendance Charts --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-bar-chart-fill me-1 text-emerald-500"></i>Monthly Revenue — {{ now()->year }}</p>
                <div id="chart-oo-revenue" style="min-height:180px"></div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-graph-up-arrow me-1 text-indigo-500"></i>Attendance Trend (7 Days)</p>
                <div id="chart-oo-attendance" style="min-height:180px"></div>
            </div>
        </div>

        {{-- Notices + Recent Admissions --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100">
                    <p class="text-sm font-semibold text-slate-700"><i class="bi bi-person-check me-1 text-emerald-500"></i>Recent Admissions</p>
                </div>
                @if($recentAdmissions->count())
                <div class="divide-y divide-slate-50">
                    @foreach($recentAdmissions as $s)
                    <div class="px-5 py-3 flex items-center gap-3">
                        <img src="{{ $s->avatar }}" class="w-8 h-8 rounded-full object-cover flex-shrink-0" alt="">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-700 truncate">{{ $s->full_name }}</p>
                            <p class="text-xs text-slate-400">{{ $s->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-400 text-center py-8">No recent admissions.</p>
                @endif
            </div>

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100">
                    <p class="text-sm font-semibold text-slate-700"><i class="bi bi-megaphone me-1 text-amber-500"></i>Notice Board</p>
                </div>
                @if($notices->count())
                <div class="divide-y divide-slate-50">
                    @foreach($notices->take(5) as $notice)
                    <div class="px-5 py-3 text-sm text-slate-600 line-clamp-2">
                        {!! \Stevebauman\Purify\Facades\Purify::clean(strip_tags($notice->notice)) !!}
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-400 text-center py-6">No notices.</p>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var months  = @json($monthlyRevenue->pluck('month')->map(fn($m) => \Carbon\Carbon::create()->month((int)$m)->format('M')));
    var totals  = @json($monthlyRevenue->pluck('total')->map(fn($v) => (float)$v));
    var days    = @json($attendanceTrend->pluck('day')->map(fn($d) => \Carbon\Carbon::parse($d)->format('D M d')));
    var present = @json($attendanceTrend->pluck('present')->map(fn($v) => (int)$v));
    var absent  = @json($attendanceTrend->pluck('absent')->map(fn($v) => (int)$v));

    if (document.getElementById('chart-oo-revenue')) {
        new ApexCharts(document.getElementById('chart-oo-revenue'), {
            chart: { type: 'bar', height: 180, toolbar: { show: false } },
            series: [{ name: 'Revenue ($)', data: totals }],
            xaxis: { categories: months, labels: { style: { fontSize: '10px' } } },
            yaxis: { labels: { style: { fontSize: '10px' }, formatter: v => '$' + (v >= 1000 ? (v/1000).toFixed(1)+'k' : v) } },
            colors: ['#22c55e'], plotOptions: { bar: { borderRadius: 4, columnWidth: '55%' } },
            dataLabels: { enabled: false }, grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
        }).render();
    }
    if (document.getElementById('chart-oo-attendance')) {
        new ApexCharts(document.getElementById('chart-oo-attendance'), {
            chart: { type: 'area', height: 180, toolbar: { show: false } },
            series: [{ name: 'Present', data: present }, { name: 'Absent', data: absent }],
            xaxis: { categories: days, labels: { style: { fontSize: '10px' } } },
            colors: ['#22c55e', '#f43f5e'],
            fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.05 } },
            stroke: { curve: 'smooth', width: 2 }, dataLabels: { enabled: false },
            legend: { position: 'top', fontSize: '12px' }, grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
        }).render();
    }
})();
</script>
@endpush
