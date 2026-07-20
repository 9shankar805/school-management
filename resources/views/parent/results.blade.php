@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">
        @include('layouts.left-menu')
    </div>

    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        @include('parent.partials.child-selector')
        @include('parent.partials.page-header', ['title' => 'Exam Results'])

        @include('session-messages')

        {{-- Filters --}}
        <form method="GET" class="bg-white rounded-xl border border-slate-100 shadow-sm p-4 mb-6 flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-semibold text-slate-500 mb-1">Filter by Exam</label>
                <select name="exam_id" class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    <option value="">All Exams</option>
                    @foreach($exams as $exam)
                    <option value="{{ $exam->id }}" {{ $examId == $exam->id ? 'selected' : '' }}>{{ $exam->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700">Filter</button>
            @if($examId || $courseId)
            <a href="{{ route('parent.results', $child->id) }}" class="px-4 py-2 bg-slate-100 text-slate-600 text-sm rounded-lg hover:bg-slate-200">Clear</a>
            @endif
        </form>

        {{-- Marks table --}}
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden mb-6">
            <div class="px-5 py-3 border-b border-slate-100">
                <p class="text-sm font-semibold text-slate-700">Mark Records ({{ $marks->count() }})</p>
            </div>
            @if($marks->isEmpty())
            <p class="text-sm text-slate-400 text-center py-10">No marks found for the selected filters.</p>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="text-left px-4 py-2 text-xs font-semibold text-slate-500">Exam</th>
                            <th class="text-left px-4 py-2 text-xs font-semibold text-slate-500">Course</th>
                            <th class="text-center px-4 py-2 text-xs font-semibold text-slate-500">Marks</th>
                            <th class="text-center px-4 py-2 text-xs font-semibold text-slate-500">%</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($marks as $mark)
                        @php
                            $pct = $mark->exam?->examRule?->total_marks > 0
                                 ? round($mark->marks / $mark->exam->examRule->total_marks * 100, 1)
                                 : null;
                        @endphp
                        <tr>
                            <td class="px-4 py-2 font-medium text-slate-700">{{ $mark->exam?->name ?? '—' }}</td>
                            <td class="px-4 py-2 text-slate-600">{{ $mark->course?->name ?? '—' }}</td>
                            <td class="px-4 py-2 text-center font-bold text-slate-800">{{ $mark->marks }}</td>
                            <td class="px-4 py-2 text-center">
                                @if($pct !== null)
                                <span class="text-xs font-semibold {{ $pct >= 50 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $pct }}%</span>
                                @else
                                <span class="text-xs text-slate-400">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        @if($trend->count() >= 2)
        {{-- Performance trend chart --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5">
                <p class="text-sm font-semibold text-slate-700 mb-3">Performance Trend (by Exam)</p>
                <div id="examTrendChart"></div>
            </div>
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5">
                <p class="text-sm font-semibold text-slate-700 mb-3">Average Marks by Course</p>
                <div id="courseTrendChart"></div>
            </div>
        </div>
        @elseif($marks->isEmpty())
        {{-- nothing --}}
        @else
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-700">
            <i class="bi bi-info-circle me-1"></i> Insufficient data for trend analysis. More exam results are needed.
        </div>
        @endif

    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const examLabels = @json($trend->pluck('exam_name'));
    const examData   = @json($trend->pluck('avg')->map(fn($v) => (float)$v));
    const courseLabels = @json($courseTrend->pluck('course_name'));
    const courseData   = @json($courseTrend->pluck('avg')->map(fn($v) => (float)$v));

    if (document.getElementById('examTrendChart') && examLabels.length) {
        new ApexCharts(document.getElementById('examTrendChart'), {
            chart: { type: 'line', height: 200, toolbar: { show: false } },
            series: [{ name: 'Avg Marks', data: examData }],
            xaxis: { categories: examLabels, labels: { style: { fontSize: '10px' } } },
            colors: ['#6366f1'],
            stroke: { curve: 'smooth', width: 2 },
            markers: { size: 4 },
            dataLabels: { enabled: false },
            tooltip: { theme: 'light' },
        }).render();
    }

    if (document.getElementById('courseTrendChart') && courseLabels.length) {
        new ApexCharts(document.getElementById('courseTrendChart'), {
            chart: { type: 'bar', height: 200, toolbar: { show: false } },
            series: [{ name: 'Avg Marks', data: courseData }],
            xaxis: { categories: courseLabels, labels: { style: { fontSize: '10px' } } },
            colors: ['#10b981'],
            plotOptions: { bar: { borderRadius: 4, horizontal: false } },
            dataLabels: { enabled: false },
            tooltip: { theme: 'light' },
        }).render();
    }
});
</script>
@endpush
