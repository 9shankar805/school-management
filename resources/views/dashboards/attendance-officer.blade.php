@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">
        @include('layouts.left-menu')
    </div>

    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Attendance Dashboard</h1>
                <p class="text-slate-400 text-sm mt-0.5">{{ now()->format('l, F j, Y') }}</p>
            </div>
            <div class="flex gap-2">
                @can('take attendances')
                <a href="{{ route('attendance.create.show') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                    <i class="bi bi-check2-square"></i> Take Attendance
                </a>
                @endcan
                @can('view attendances')
                <a href="{{ route('attendance.list.show') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 text-sm font-medium rounded-lg hover:bg-slate-50 transition text-slate-700">
                    <i class="bi bi-calendar-week"></i> View Records
                </a>
                @endcan
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Today Present</p>
                    <span class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600 text-sm"><i class="bi bi-person-check"></i></span>
                </div>
                <p class="text-3xl font-bold text-emerald-600">{{ number_format($todayPresent) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Today Absent</p>
                    <span class="w-8 h-8 rounded-lg bg-rose-50 flex items-center justify-center text-rose-600 text-sm"><i class="bi bi-person-x"></i></span>
                </div>
                <p class="text-3xl font-bold text-rose-600">{{ number_format($todayAbsent) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Rate Today</p>
                    <span class="w-8 h-8 rounded-lg {{ $attendancePct >= 75 ? 'bg-emerald-50' : 'bg-rose-50' }} flex items-center justify-center {{ $attendancePct >= 75 ? 'text-emerald-600' : 'text-rose-600' }} text-sm"><i class="bi bi-percent"></i></span>
                </div>
                <p class="text-3xl font-bold {{ $attendancePct >= 75 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $attendancePct }}%</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Students</p>
                    <span class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 text-sm"><i class="bi bi-people"></i></span>
                </div>
                <p class="text-3xl font-bold text-slate-800">{{ number_format($studentCount) }}</p>
            </div>
        </div>

        {{-- Attendance % gauge --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-6">
            <p class="text-sm font-semibold text-slate-700 mb-3">Today's Attendance Rate</p>
            <div class="h-4 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all {{ $attendancePct >= 75 ? 'bg-emerald-500' : 'bg-rose-500' }}" style="width:{{ $attendancePct }}%"></div>
            </div>
            <div class="flex justify-between mt-2 text-xs text-slate-400">
                <span>0%</span>
                <span class="{{ $attendancePct < 75 ? 'text-rose-500 font-medium' : 'text-slate-400' }}">75% threshold</span>
                <span>100%</span>
            </div>
        </div>

        {{-- 14-Day Trend Chart --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-6">
            <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-graph-up-arrow me-1 text-indigo-500"></i>14-Day Attendance Trend</p>
            <div id="chart-ao-trend" style="min-height:200px"></div>
        </div>

        {{-- Alert if low attendance --}}
        @if($attendancePct < 75 && ($todayPresent + $todayAbsent) > 0)
        <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 flex items-start gap-3">
            <i class="bi bi-exclamation-triangle-fill text-rose-500 text-lg mt-0.5"></i>
            <div>
                <p class="font-semibold text-rose-800 text-sm">Low Attendance Alert</p>
                <p class="text-rose-700 text-xs mt-0.5">Today's attendance is {{ $attendancePct }}%, below the 75% threshold. Consider sending parent notifications.</p>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var days    = @json($weekTrend->pluck('day')->map(fn($d) => \Carbon\Carbon::parse($d)->format('D M d')));
    var present = @json($weekTrend->pluck('present')->map(fn($v) => (int)$v));
    var absent  = @json($weekTrend->pluck('absent')->map(fn($v) => (int)$v));
    if (document.getElementById('chart-ao-trend')) {
        new ApexCharts(document.getElementById('chart-ao-trend'), {
            chart: { type: 'bar', height: 200, stacked: true, toolbar: { show: false } },
            series: [{ name: 'Present', data: present }, { name: 'Absent', data: absent }],
            xaxis: { categories: days, labels: { style: { fontSize: '10px' } } },
            yaxis: { labels: { style: { fontSize: '10px' } } },
            colors: ['#22c55e', '#f43f5e'],
            plotOptions: { bar: { borderRadius: 3, columnWidth: '55%' } },
            dataLabels: { enabled: false },
            legend: { position: 'top', fontSize: '12px' },
            grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
        }).render();
    }
})();
</script>
@endpush
