@extends('layouts.app')

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
    font-size: 22px !important;
    font-weight: 800 !important;
    color: #0f172a !important;
    line-height: 1.1 !important;
}
[data-theme="dark"] .iemis-value,
html.dark .iemis-value {
    color: #f8fafc !important;
}
.iemis-badge-green { font-size: 10px !important; font-weight: 700 !important; color: #10b981 !important; }
.iemis-badge-blue  { font-size: 10px !important; font-weight: 700 !important; color: #3b82f6 !important; }
.iemis-badge-rose  { font-size: 10px !important; font-weight: 700 !important; color: #ef4444 !important; }
</style>
@endpush

@section('content')
<div class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto space-y-6 bg-slate-50/50 dark:bg-slate-950 min-h-screen">

        {{-- Header --}}
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-yellow-500 to-amber-600 text-white flex items-center justify-center shadow-lg shadow-yellow-500/25 flex-shrink-0">
                <i class="bi bi-award-fill text-xl"></i>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Organization Owner Dashboard</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">{{ now()->format('l, F j, Y') }} &bull; Executive Institutional Oversight</p>
            </div>
        </div>

        {{-- CEHRD IEMIS 3D Stat Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
            <a href="{{ route('student.list.show') }}" class="iemis-stat-card">
                <div class="iemis-icon-wrapper">🎓</div>
                <div class="iemis-card-details">
                    <span class="iemis-title">Students</span>
                    <div class="iemis-num-group">
                        <span class="iemis-value">{{ number_format($studentCount) }}</span>
                        <span class="iemis-badge-green">Enrolled</span>
                    </div>
                </div>
            </a>
            <a href="{{ route('teacher.list.show') }}" class="iemis-stat-card">
                <div class="iemis-icon-wrapper">🧑‍🏫</div>
                <div class="iemis-card-details">
                    <span class="iemis-title">Teachers</span>
                    <div class="iemis-num-group">
                        <span class="iemis-value">{{ number_format($teacherCount) }}</span>
                        <span class="iemis-badge-blue">Faculty</span>
                    </div>
                </div>
            </a>
            <div class="iemis-stat-card">
                <div class="iemis-icon-wrapper">💰</div>
                <div class="iemis-card-details">
                    <span class="iemis-title">Monthly Revenue</span>
                    <div class="iemis-num-group">
                        <span class="iemis-value text-emerald-600">Rs. {{ number_format($monthRevenue) }}</span>
                    </div>
                </div>
            </div>
            <div class="iemis-stat-card">
                <div class="iemis-icon-wrapper">🧾</div>
                <div class="iemis-card-details">
                    <span class="iemis-title">Unpaid Invoices</span>
                    <div class="iemis-num-group">
                        <span class="iemis-value text-rose-600">{{ number_format($pendingInvoices) }}</span>
                        <span class="iemis-badge-rose">Dues</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Charts Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 sm:gap-6 mb-6">
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-5 sm:p-6">
                <div class="flex items-center gap-2 mb-4">
                    <i class="bi bi-bar-chart-fill text-emerald-500"></i>
                    <p class="text-sm font-bold text-slate-800 dark:text-slate-100">Monthly Collections — {{ now()->year }}</p>
                </div>
                <div id="chart-oo-revenue" style="min-height:220px"></div>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-5 sm:p-6">
                <div class="flex items-center gap-2 mb-4">
                    <i class="bi bi-graph-up-arrow text-indigo-500"></i>
                    <p class="text-sm font-bold text-slate-800 dark:text-slate-100">Attendance Trend (7 Days)</p>
                </div>
                <div id="chart-oo-attendance" style="min-height:220px"></div>
            </div>
        </div>

        {{-- Recent Admissions & Notices --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 sm:gap-6">
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center gap-2 bg-slate-50/60 dark:bg-slate-800/40">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <p class="text-sm font-bold text-slate-800 dark:text-slate-100">Recent Student Admissions</p>
                </div>
                <div class="p-4 flex-1">
                    @if($recentAdmissions->count())
                    <div class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        @foreach($recentAdmissions as $s)
                        <div class="py-2.5 px-2 flex items-center gap-3 rounded-xl hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                            <img src="{{ $s->avatar }}" class="w-9 h-9 rounded-2xl object-cover flex-shrink-0 border border-slate-200 dark:border-slate-700" alt="">
                            <div class="flex-1 min-w-0">
                                <p class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100 truncate">{{ $s->full_name }}</p>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500">{{ $s->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-10">
                        <p class="text-xs text-slate-400 font-medium">No recent admissions.</p>
                    </div>
                    @endif
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center gap-2 bg-slate-50/60 dark:bg-slate-800/40">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <p class="text-sm font-bold text-slate-800 dark:text-slate-100">Campus Notice Board</p>
                </div>
                <div class="p-4 flex-1">
                    @if($notices->count())
                    <div class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        @foreach($notices->take(4) as $notice)
                        <div class="py-3 px-2 rounded-xl hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                            <div class="text-xs sm:text-sm text-slate-700 dark:text-slate-300 line-clamp-2 leading-relaxed">
                                {!! \Stevebauman\Purify\Facades\Purify::clean(strip_tags($notice->notice)) !!}
                            </div>
                            <span class="text-[10px] text-slate-400 dark:text-slate-500 mt-1 block">{{ $notice->created_at->diffForHumans() }}</span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-10">
                        <p class="text-xs text-slate-400 font-medium">No notices published.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
<script>
(function () {
    @php
        $monthNames = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'];
        $revMonths = [];
        $revValues = [];
        if (isset($monthlyRevenue)) {
            $mRevCol = is_array($monthlyRevenue) ? collect($monthlyRevenue) : $monthlyRevenue;
            foreach ($mRevCol as $item) {
                $mNum = is_object($item) ? ($item->month ?? 1) : ($item['month'] ?? 1);
                $mTot = is_object($item) ? ($item->total ?? 0) : ($item['total'] ?? 0);
                $revMonths[] = $monthNames[(int)$mNum] ?? 'M'.$mNum;
                $revValues[] = (float)$mTot;
            }
        }
        if (empty($revMonths)) {
            $revMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
            $revValues = [0, 0, 0, 0, 0, 0];
        }

        $attTrendCol = collect($attendanceTrend ?? []);
        $attDays = $attTrendCol->pluck('day')->map(fn($d) => $d ? \Carbon\Carbon::parse($d)->format('D M d') : '')->values()->toArray();
        $attPct = $attTrendCol->map(function($r) {
            $pres = (int)(is_object($r) ? ($r->present ?? 0) : ($r['present'] ?? 0));
            $abs  = (int)(is_object($r) ? ($r->absent ?? 0) : ($r['absent'] ?? 0));
            $tot = $pres + $abs;
            return $tot > 0 ? round(($pres / $tot) * 100) : 0;
        })->values()->toArray();

        if (empty($attDays)) {
            $attDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            $attPct = [0, 0, 0, 0, 0, 0, 0];
        }
    @endphp

    var revMonths = @json($revMonths);
    var revValues = @json($revValues);

    if (document.getElementById('chart-oo-revenue')) {
        new ApexCharts(document.getElementById('chart-oo-revenue'), {
            chart: { type: 'bar', height: 220, toolbar: { show: false }, fontFamily: 'Inter, system-ui, sans-serif' },
            plotOptions: { bar: { borderRadius: 6, columnWidth: '45%' } },
            colors: ['#10b981'],
            series: [{ name: 'Revenue (Rs)', data: revValues }],
            xaxis: { categories: revMonths, labels: { style: { fontSize: '10px', colors: '#94a3b8' } } },
            yaxis: { labels: { formatter: v => 'Rs. ' + (v >= 1000 ? (v/1000).toFixed(0) + 'k' : v), style: { fontSize: '10px', colors: '#94a3b8' } } },
            dataLabels: { enabled: false },
            grid: { borderColor: 'rgba(148, 163, 184, 0.15)' }
        }).render();
    }

    var attDays = @json($attDays);
    var attPct  = @json($attPct);

    if (document.getElementById('chart-oo-attendance')) {
        new ApexCharts(document.getElementById('chart-oo-attendance'), {
            chart: { type: 'area', height: 220, toolbar: { show: false }, fontFamily: 'Inter, system-ui, sans-serif' },
            colors: ['#6366f1'],
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05, stops: [0, 100] } },
            stroke: { curve: 'smooth', width: 2.5 },
            series: [{ name: 'Attendance %', data: attPct }],
            xaxis: { categories: attDays, labels: { style: { fontSize: '10px', colors: '#94a3b8' } } },
            yaxis: { min: 0, max: 100, labels: { formatter: v => v + '%', style: { fontSize: '10px', colors: '#94a3b8' } } },
            dataLabels: { enabled: false },
            grid: { borderColor: 'rgba(148, 163, 184, 0.15)' }
        }).render();
    }
})();
</script>
@endpush
