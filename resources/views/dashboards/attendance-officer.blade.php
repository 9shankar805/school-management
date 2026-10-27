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
.iemis-badge-rose  { font-size: 10px !important; font-weight: 700 !important; color: #ef4444 !important; }
.iemis-badge-indigo{ font-size: 10px !important; font-weight: 700 !important; color: #6366f1 !important; }
</style>
@endpush

@section('content')
<div class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto space-y-6 bg-slate-50/50 dark:bg-slate-950 min-h-screen">

        {{-- Header & Quick Actions --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-teal-600 to-emerald-600 text-white flex items-center justify-center shadow-lg shadow-teal-500/25 flex-shrink-0">
                    <i class="bi bi-calendar-check-fill text-xl"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Attendance Officer Dashboard</h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">{{ now()->format('l, F j, Y') }} &bull; Daily Roster Tracking</p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                @can('take attendances')
                <a href="{{ route('attendance.create.show') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-sm shadow-emerald-600/25 transition active:scale-95">
                    <i class="bi bi-check2-square text-sm"></i>
                    <span>Take Attendance</span>
                </a>
                @endcan
                @can('view attendances')
                <a href="{{ route('attendance.list.show') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm font-semibold rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 transition active:scale-95">
                    <i class="bi bi-calendar-week text-slate-500 text-sm"></i>
                    <span>View Records</span>
                </a>
                @endcan
            </div>
        </div>

        {{-- CEHRD IEMIS 3D Stat Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
            <div class="iemis-stat-card">
                <div class="iemis-icon-wrapper">✅</div>
                <div class="iemis-card-details">
                    <span class="iemis-title">Today Present</span>
                    <div class="iemis-num-group">
                        <span class="iemis-value">{{ number_format($todayPresent) }}</span>
                        <span class="iemis-badge-green">Present</span>
                    </div>
                </div>
            </div>
            <div class="iemis-stat-card">
                <div class="iemis-icon-wrapper">❌</div>
                <div class="iemis-card-details">
                    <span class="iemis-title">Today Absent</span>
                    <div class="iemis-num-group">
                        <span class="iemis-value text-rose-600">{{ number_format($todayAbsent) }}</span>
                        <span class="iemis-badge-rose">Absent</span>
                    </div>
                </div>
            </div>
            <div class="iemis-stat-card">
                <div class="iemis-icon-wrapper">📊</div>
                <div class="iemis-card-details">
                    <span class="iemis-title">Attendance Rate</span>
                    <div class="iemis-num-group">
                        <span class="iemis-value">{{ $attendancePct }}%</span>
                        <span class="{{ $attendancePct >= 75 ? 'iemis-badge-green' : 'iemis-badge-rose' }}">Rate</span>
                    </div>
                </div>
            </div>
            <a href="{{ route('student.list.show') }}" class="iemis-stat-card">
                <div class="iemis-icon-wrapper">🎓</div>
                <div class="iemis-card-details">
                    <span class="iemis-title">Total Enrolled</span>
                    <div class="iemis-num-group">
                        <span class="iemis-value">{{ number_format($studentCount) }}</span>
                        <span class="iemis-badge-indigo">Roster</span>
                    </div>
                </div>
            </a>
        </div>

        {{-- Attendance Gauge --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-5 sm:p-6">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <i class="bi bi-speedometer2 text-teal-500"></i>
                    <p class="text-sm font-bold text-slate-800 dark:text-slate-100">Today's Institutional Attendance Gauge</p>
                </div>
                <span class="text-xs font-bold px-2.5 py-0.5 rounded-full {{ $attendancePct >= 75 ? 'bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300' : 'bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300' }}">
                    {{ $attendancePct }}% Reached
                </span>
            </div>
            <div class="h-4 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden p-0.5 border border-slate-200 dark:border-slate-700">
                <div class="h-full rounded-full transition-all duration-500 {{ $attendancePct >= 75 ? 'bg-gradient-to-r from-emerald-500 to-teal-500' : 'bg-gradient-to-r from-rose-500 to-amber-500' }}" style="width:{{ max(2, $attendancePct) }}%"></div>
            </div>
            <div class="flex justify-between mt-2.5 text-[11px] text-slate-400 dark:text-slate-500 font-medium">
                <span>0% Min</span>
                <span class="{{ $attendancePct < 75 ? 'text-rose-500 font-bold' : 'text-slate-400' }}">75% Standard Benchmark</span>
                <span>100% Full Attendance</span>
            </div>
        </div>

        {{-- Trend Chart --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-5 sm:p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <i class="bi bi-graph-up-arrow text-indigo-500"></i>
                    <p class="text-sm font-bold text-slate-800 dark:text-slate-100">14-Day Attendance Trend</p>
                </div>
            </div>
            <div id="chart-ao-trend" style="min-height:220px"></div>
        </div>

        {{-- Low Attendance Alert --}}
        @if($attendancePct < 75 && ($todayPresent + $todayAbsent) > 0)
        <div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 rounded-2xl p-4 flex items-start gap-3 shadow-xs">
            <i class="bi bi-exclamation-triangle-fill text-rose-500 text-lg mt-0.5 flex-shrink-0"></i>
            <div>
                <p class="font-bold text-rose-800 dark:text-rose-300 text-sm">Low Attendance Warning</p>
                <p class="text-rose-700 dark:text-rose-400 text-xs mt-0.5">Today's recorded attendance is {{ $attendancePct }}%, which is under the 75% institutional threshold. Consider following up with absent students or sending automated parent notifications.</p>
            </div>
        </div>
        @endif

    </div>
@endsection

@push('scripts')
<script>
(function () {
    @php
        $trendCol   = collect($weekTrend ?? []);
        $daysArr    = $trendCol->pluck('day')->map(fn($d) => $d ? \Carbon\Carbon::parse($d)->format('D M d') : '')->values()->toArray();
        $presentArr = $trendCol->pluck('present')->map(fn($v) => (int)$v)->values()->toArray();
        $absentArr  = $trendCol->pluck('absent')->map(fn($v) => (int)$v)->values()->toArray();
    @endphp
    var days    = @json($daysArr);
    var present = @json($presentArr);
    var absent  = @json($absentArr);
    if (document.getElementById('chart-ao-trend')) {
        new ApexCharts(document.getElementById('chart-ao-trend'), {
            chart: { 
                type: 'bar', 
                height: 220, 
                stacked: true, 
                toolbar: { show: false },
                fontFamily: 'Inter, system-ui, sans-serif'
            },
            plotOptions: { 
                bar: { 
                    horizontal: false, 
                    borderRadius: 6, 
                    columnWidth: '50%' 
                } 
            },
            colors: ['#10b981', '#f43f5e'],
            series: [
                { name: 'Present', data: present },
                { name: 'Absent',  data: absent }
            ],
            xaxis: { 
                categories: days, 
                labels: { style: { fontSize: '11px', colors: '#94a3b8' } } 
            },
            yaxis: { 
                labels: { style: { fontSize: '11px', colors: '#94a3b8' } } 
            },
            legend: { 
                position: 'top', 
                fontSize: '12px',
                labels: { colors: '#64748b' }
            },
            dataLabels: { enabled: false },
            grid: { borderColor: 'rgba(148, 163, 184, 0.15)' }
        }).render();
    }
})();
</script>
@endpush
