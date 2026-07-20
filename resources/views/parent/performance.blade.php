@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">
        @include('layouts.left-menu')
    </div>

    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        @include('parent.partials.child-selector')
        @include('parent.partials.page-header', ['title' => 'Performance Trends'])

        @if($markCount < 2)
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 flex items-start gap-3">
            <i class="bi bi-info-circle-fill text-blue-400 mt-0.5"></i>
            <div>
                <p class="font-semibold text-blue-800 text-sm">Insufficient data for trend analysis</p>
                <p class="text-blue-700 text-xs mt-1">More exam results need to be recorded before performance charts can be displayed.</p>
            </div>
        </div>
        @else

        {{-- Exam line trend --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5">
                <p class="text-sm font-semibold text-slate-700 mb-3">Performance by Exam (Line)</p>
                <div id="examLineChart"></div>
            </div>
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5">
                <p class="text-sm font-semibold text-slate-700 mb-3">Average Marks by Course (Bar)</p>
                <div id="courseBarChart"></div>
            </div>
        </div>

        {{-- Multi-session comparison --}}
        @if(count($multiSession) > 1)
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5">
            <p class="text-sm font-semibold text-slate-700 mb-3">Multi-Session Comparison</p>
            <div id="multiSessionChart"></div>
        </div>
        @endif

        @endif

    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const examLabels = @json($examTrend->pluck('exam_name'));
    const examData   = @json($examTrend->pluck('avg')->map(fn($v) => (float)$v));
    const courseLabels = @json($courseTrend->pluck('course_name'));
    const courseData   = @json($courseTrend->pluck('avg')->map(fn($v) => (float)$v));

    if (document.getElementById('examLineChart') && examLabels.length) {
        new ApexCharts(document.getElementById('examLineChart'), {
            chart: { type: 'line', height: 230, toolbar: { show: false } },
            series: [{ name: 'Avg Marks', data: examData }],
            xaxis: { categories: examLabels, labels: { style: { fontSize: '10px' } } },
            colors: ['#6366f1'],
            stroke: { curve: 'smooth', width: 2.5 },
            markers: { size: 5 },
            dataLabels: { enabled: true, style: { fontSize: '10px' } },
            tooltip: { theme: 'light' },
            yaxis: { min: 0 },
        }).render();
    }

    if (document.getElementById('courseBarChart') && courseLabels.length) {
        new ApexCharts(document.getElementById('courseBarChart'), {
            chart: { type: 'bar', height: 230, toolbar: { show: false } },
            series: [{ name: 'Avg Marks', data: courseData }],
            xaxis: { categories: courseLabels, labels: { style: { fontSize: '10px' } } },
            colors: ['#10b981'],
            plotOptions: { bar: { borderRadius: 4, horizontal: false } },
            dataLabels: { enabled: true, style: { fontSize: '10px' } },
            tooltip: { theme: 'light' },
            yaxis: { min: 0 },
        }).render();
    }

    @if(count($multiSession) > 1)
    const multiData = @json($multiSession);
    const seriesArr = Object.entries(multiData).map(([sid, rows]) => ({
        name: 'Session ' + sid,
        data: rows.map(r => parseFloat(r.avg)),
    }));
    const allLabels = Object.values(multiData)[0]?.map(r => r.exam_name) ?? [];
    if (document.getElementById('multiSessionChart')) {
        new ApexCharts(document.getElementById('multiSessionChart'), {
            chart: { type: 'line', height: 250, toolbar: { show: false } },
            series: seriesArr,
            xaxis: { categories: allLabels, labels: { style: { fontSize: '10px' } } },
            stroke: { curve: 'smooth', width: 2 },
            markers: { size: 4 },
            dataLabels: { enabled: false },
            legend: { position: 'top', fontSize: '11px' },
            tooltip: { theme: 'light' },
            yaxis: { min: 0 },
        }).render();
    }
    @endif
});
</script>
@endpush
