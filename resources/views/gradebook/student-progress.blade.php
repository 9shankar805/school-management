@extends('layouts.app')
@section('content')
<div class="flex-1 p-6 lg:p-8 overflow-auto">

    <nav class="text-xs text-slate-400 mb-4">
        <a href="{{ route('gradebook.index') }}" class="hover:text-indigo-600">Gradebook</a>
        <span class="mx-1">/</span>
        <span class="text-slate-600">{{ $student->full_name }}</span>
    </nav>

    @include('session-messages')

    {{-- Student header --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mb-6 flex flex-wrap items-center gap-5">
        <img src="{{ $student->avatar }}" class="w-16 h-16 rounded-2xl object-cover border-2 border-indigo-100 flex-shrink-0" alt="">
        <div class="flex-1 min-w-0">
            <h1 class="text-xl font-bold text-slate-800">{{ $student->full_name }}</h1>
            <div class="flex flex-wrap gap-4 text-xs text-slate-500 mt-1">
                <span><i class="bi bi-envelope me-1"></i>{{ $student->email }}</span>
                @if($promotion)
                <span><i class="bi bi-layers me-1"></i>{{ $promotion->schoolClass->class_name ?? '—' }} · {{ $promotion->section->section_name ?? '—' }}</span>
                @endif
            </div>
        </div>
        <a href="{{ route('student.profile.show', $student->id) }}"
           class="flex-shrink-0 inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-slate-200 text-slate-700 text-xs font-medium rounded-lg hover:bg-slate-50 transition">
            <i class="bi bi-person"></i> Full Profile
        </a>
    </div>

    {{-- KPI row --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm text-center">
            <p class="text-3xl font-bold {{ $avgMarks >= 75 ? 'text-emerald-600' : ($avgMarks >= 40 ? 'text-amber-600' : 'text-rose-600') }}">
                {{ round($avgMarks, 1) }}
            </p>
            <p class="text-xs text-slate-400 mt-1">Avg Marks</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm text-center">
            <p class="text-3xl font-bold {{ $attendanceStats['percentage'] >= 75 ? 'text-emerald-600' : 'text-rose-600' }}">
                {{ $attendanceStats['percentage'] }}%
            </p>
            <p class="text-xs text-slate-400 mt-1">Attendance</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm text-center">
            <p class="text-3xl font-bold text-indigo-600">{{ $homeworkSubmissions->count() }}</p>
            <p class="text-xs text-slate-400 mt-1">HW Submitted</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm text-center">
            <p class="text-3xl font-bold text-violet-600">{{ $projectSubmissions->count() }}</p>
            <p class="text-xs text-slate-400 mt-1">Projects Done</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- Marks chart --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-graph-up me-1 text-indigo-500"></i>Marks per Exam</p>
            <div id="chart-marks" style="min-height:180px"></div>
        </div>

        {{-- Attendance breakdown --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-calendar-check me-1 text-emerald-500"></i>Attendance Breakdown</p>
            <div class="space-y-3 mt-2">
                @foreach([['Present', $attendanceStats['present'], 'bg-emerald-500'], ['Absent', $attendanceStats['absent'], 'bg-rose-500'], ['Late', $attendanceStats['late'], 'bg-amber-500']] as [$label, $count, $color])
                <div class="flex items-center gap-3">
                    <span class="text-xs text-slate-500 w-16">{{ $label }}</span>
                    <div class="flex-1 bg-slate-100 rounded-full h-2">
                        @php $pct = $attendanceStats['total'] > 0 ? round($count/$attendanceStats['total']*100) : 0; @endphp
                        <div class="h-2 rounded-full {{ $color }}" style="width:{{ $pct }}%"></div>
                    </div>
                    <span class="text-xs font-semibold text-slate-700 w-12 text-right">{{ $count }} <span class="text-slate-400 font-normal">({{ $pct }}%)</span></span>
                </div>
                @endforeach
            </div>
            <p class="text-[11px] text-slate-400 mt-4">Total days recorded: {{ $attendanceStats['total'] }}</p>
        </div>
    </div>

    {{-- Homework submissions --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mb-6">
        <div class="px-5 py-3 border-b border-slate-100 flex justify-between items-center">
            <p class="text-sm font-semibold text-slate-700"><i class="bi bi-pencil-fill me-1 text-amber-500"></i>Homework Submissions</p>
            <span class="text-xs text-slate-400">{{ $homeworkSubmissions->count() }} total</span>
        </div>
        @if($homeworkSubmissions->isEmpty())
        <p class="text-sm text-slate-400 text-center py-8">No homework submitted yet.</p>
        @else
        <div class="divide-y divide-slate-50">
            @foreach($homeworkSubmissions as $sub)
            <div class="px-5 py-3 flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-slate-700">{{ $sub->homework->title ?? '—' }}</p>
                    <p class="text-xs text-slate-400">
                        Submitted: {{ $sub->submitted_at?->format('M d, Y') ?? '—' }}
                        @if($sub->homework)
                         · Due: {{ $sub->homework->due_date->format('M d, Y') }}
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    @if($sub->marks_obtained !== null)
                    <span class="text-sm font-bold {{ $sub->marks_obtained >= ($sub->homework->total_marks * 0.75) ? 'text-emerald-600' : ($sub->marks_obtained >= ($sub->homework->total_marks * 0.4) ? 'text-amber-600' : 'text-rose-600') }}">
                        {{ $sub->marks_obtained }}/{{ $sub->homework->total_marks ?? '?' }}
                    </span>
                    @endif
                    <span class="text-[11px] px-2 py-0.5 rounded-full font-medium {{ $sub->status === 'graded' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700' }}">
                        {{ ucfirst($sub->status) }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Project submissions --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100 flex justify-between items-center">
            <p class="text-sm font-semibold text-slate-700"><i class="bi bi-folder-fill me-1 text-violet-500"></i>Project Submissions</p>
            <span class="text-xs text-slate-400">{{ $projectSubmissions->count() }} total</span>
        </div>
        @if($projectSubmissions->isEmpty())
        <p class="text-sm text-slate-400 text-center py-8">No projects submitted yet.</p>
        @else
        <div class="divide-y divide-slate-50">
            @foreach($projectSubmissions as $sub)
            <div class="px-5 py-3 flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-slate-700">{{ $sub->project->title ?? '—' }}</p>
                    <p class="text-xs text-slate-400">Submitted: {{ $sub->submitted_at?->format('M d, Y') ?? '—' }}</p>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    @if($sub->marks_obtained !== null)
                    <span class="text-sm font-bold text-slate-700">
                        {{ $sub->marks_obtained }}/{{ $sub->project->total_marks ?? '?' }}
                    </span>
                    @endif
                    <span class="text-[11px] px-2 py-0.5 rounded-full font-medium {{ $sub->status === 'graded' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700' }}">
                        {{ ucfirst($sub->status) }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var examMarks   = @json($marks->values()->map(fn($m) => (float)($m->marks ?? 0)));
    var examLabels  = @json($marks->values()->map(fn($m) => $m->exam?->exam_name ?? ''));

    if (document.getElementById('chart-marks') && examLabels.length) {
        new ApexCharts(document.getElementById('chart-marks'), {
            chart: { type: 'bar', height: 180, toolbar: { show: false } },
            series: [{ name: 'Marks', data: examMarks }],
            xaxis: { categories: examLabels, labels: { style: { fontSize: '10px' }, rotate: -30 } },
            yaxis: { min: 0, max: 100, labels: { style: { fontSize: '10px' } } },
            colors: ['#6366f1'],
            plotOptions: { bar: { borderRadius: 4, columnWidth: '50%' } },
            dataLabels: { enabled: false },
            grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
        }).render();
    }
})();
</script>
@endpush
