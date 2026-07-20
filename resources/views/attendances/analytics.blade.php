@extends('layouts.app')
@push('head-scripts')
{{-- ApexCharts already loaded in layouts.app --}}
@endpush
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        {{-- Header --}}
        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight"><i class="bi bi-bar-chart-line me-2"></i>Attendance Analytics</h1>
                <p class="text-slate-400 text-sm mt-0.5">Overview of student attendance trends and alerts</p>
            </div>
            {{-- Filters --}}
            <form method="GET" action="{{ route('attendance.analytics') }}" class="flex flex-wrap gap-2 items-end">
                <div>
                    <label class="block text-xs text-slate-500 mb-1">Class</label>
                    <select name="class_id" class="border border-slate-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        @foreach($classes as $cls)
                        <option value="{{ $cls->id }}" {{ $classId == $cls->id ? 'selected' : '' }}>{{ $cls->class_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1">Trend Days</label>
                    <select name="days" class="border border-slate-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        @foreach([7,14,30,60] as $d)
                        <option value="{{ $d }}" {{ $days == $d ? 'selected' : '' }}>Last {{ $d }} days</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">Apply</button>
            </form>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            @php
                $rate = $todaySummary['total'] > 0
                    ? round($todaySummary['present'] / $todaySummary['total'] * 100, 1)
                    : 0;
            @endphp
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
                <p class="text-2xl font-bold text-indigo-600">{{ $todaySummary['total'] }}</p>
                <p class="text-xs text-slate-400 mt-0.5">Total Records Today</p>
            </div>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
                <p class="text-2xl font-bold text-emerald-600">{{ $todaySummary['present'] }}</p>
                <p class="text-xs text-slate-400 mt-0.5">Present Today</p>
            </div>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
                <p class="text-2xl font-bold text-rose-600">{{ $todaySummary['absent'] }}</p>
                <p class="text-xs text-slate-400 mt-0.5">Absent Today</p>
            </div>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
                <p class="text-2xl font-bold {{ $rate >= 75 ? 'text-emerald-600' : 'text-amber-600' }}">{{ $rate }}%</p>
                <p class="text-xs text-slate-400 mt-0.5">Today's Rate</p>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">

            {{-- Trend Chart --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <p class="text-sm font-semibold text-slate-700 mb-4">Daily Attendance Trend (last {{ $days }} days)</p>
                <div id="trendChart"></div>
            </div>

            {{-- Monthly Heatmap --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <p class="text-sm font-semibold text-slate-700 mb-4">Monthly Heatmap — {{ now()->format('F Y') }}</p>
                <div id="heatmapChart"></div>
            </div>
        </div>

        {{-- Class Summary Table --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mb-6">
            <div class="px-5 py-3 border-b border-slate-100">
                <p class="text-sm font-semibold text-slate-700">Class Summary — Today ({{ today()->format('d M Y') }})</p>
            </div>
            @if($classSummary->isEmpty())
            <div class="p-6 text-center text-sm text-slate-400">No attendance has been taken today yet.</div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-xs text-slate-400 bg-slate-50">
                        <th class="px-5 py-2.5 font-medium">Class</th>
                        <th class="px-4 py-2.5 font-medium text-center">Total</th>
                        <th class="px-4 py-2.5 font-medium text-center">Present</th>
                        <th class="px-4 py-2.5 font-medium text-center">Absent</th>
                        <th class="px-4 py-2.5 font-medium text-center">Late</th>
                        <th class="px-4 py-2.5 font-medium text-center">Rate</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($classSummary as $row)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-medium text-slate-700">{{ $row->class_name }}</td>
                            <td class="px-4 py-3 text-center text-slate-500">{{ $row->total }}</td>
                            <td class="px-4 py-3 text-center"><span class="text-emerald-600 font-semibold">{{ $row->present }}</span></td>
                            <td class="px-4 py-3 text-center"><span class="text-rose-600 font-semibold">{{ $row->absent }}</span></td>
                            <td class="px-4 py-3 text-center"><span class="text-amber-600">{{ $row->late }}</span></td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $row->rate >= 75 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                    {{ $row->rate }}%
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- Shortage List --}}
        @if(count($shortageStudents) > 0)
        <div class="bg-white rounded-2xl border border-rose-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-rose-100 flex justify-between items-center bg-rose-50">
                <p class="text-sm font-semibold text-rose-700"><i class="bi bi-exclamation-triangle me-2"></i>Below 75% Attendance ({{ count($shortageStudents) }} student(s))</p>
                <a href="{{ route('attendance.shortage') }}" class="text-xs text-rose-500 hover:underline">View full list →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-xs text-slate-400 bg-slate-50">
                        <th class="px-5 py-2.5 font-medium">Student</th>
                        <th class="px-4 py-2.5 font-medium">Class</th>
                        <th class="px-4 py-2.5 font-medium text-center">Present</th>
                        <th class="px-4 py-2.5 font-medium text-center">Total</th>
                        <th class="px-4 py-2.5 font-medium text-center">Attendance</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach(array_slice($shortageStudents, 0, 10) as $s)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <img src="{{ $s['student']?->avatar }}" class="w-7 h-7 rounded-full object-cover flex-shrink-0" alt="">
                                    <span class="font-medium text-slate-700">{{ $s['student']?->full_name ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $s['schoolClass']?->class_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-center text-emerald-600 font-semibold">{{ $s['present'] }}</td>
                            <td class="px-4 py-3 text-center text-slate-500">{{ $s['total'] }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-100 text-rose-700">
                                    {{ $s['percentage'] }}%
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Trend chart ─────────────────────────────────────────────────────────
    const trendData = @json($trend);
    new ApexCharts(document.getElementById('trendChart'), {
        chart: { type: 'area', height: 220, toolbar: { show: false }, animations: { enabled: true } },
        colors: ['#10b981', '#f43f5e'],
        stroke: { curve: 'smooth', width: 2 },
        fill:   { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.3, opacityTo: 0.05 } },
        series: [
            { name: 'Present', data: trendData.map(r => r.present) },
            { name: 'Absent',  data: trendData.map(r => r.absent)  },
        ],
        xaxis: {
            categories: trendData.map(r => r.date),
            labels: { style: { fontSize: '11px' }, rotate: -30 },
        },
        yaxis: { labels: { style: { fontSize: '11px' } } },
        tooltip: { shared: true, intersect: false },
        legend: { position: 'top', fontSize: '12px' },
        grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
    }).render();

    // ── Heatmap chart ────────────────────────────────────────────────────────
    const heatmapRaw = @json($heatmap);
    const heatRows   = [{ name: '{{ now()->format("F") }}', data: [] }];
    for (const [day, val] of Object.entries(heatmapRaw)) {
        heatRows[0].data.push({ x: 'Day ' + day, y: val ?? 0 });
    }
    new ApexCharts(document.getElementById('heatmapChart'), {
        chart: { type: 'heatmap', height: 220, toolbar: { show: false } },
        series: heatRows,
        colors: ['#4f46e5'],
        dataLabels: { enabled: false },
        plotOptions: { heatmap: { shadeIntensity: 0.8, colorScale: {
            ranges: [
                { from: 0,  to: 0,   name: 'No class',  color: '#f1f5f9' },
                { from: 1,  to: 60,  name: 'Low',       color: '#fca5a5' },
                { from: 61, to: 80,  name: 'Medium',    color: '#fcd34d' },
                { from: 81, to: 100, name: 'High',      color: '#6ee7b7' },
            ],
        }}},
        xaxis: { labels: { style: { fontSize: '9px' } } },
        grid: { borderColor: '#f1f5f9' },
    }).render();
});
</script>
@endpush
@endsection
