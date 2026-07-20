@extends('layouts.app')
@push('head-scripts'){{-- ApexCharts loaded in layout --}}@endpush
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight"><i class="bi bi-graph-up-arrow me-2"></i>Exam Performance Analytics</h1>
                <p class="text-slate-400 text-sm mt-0.5">Subject averages, grade distribution, and top performers</p>
            </div>
            <form method="GET" action="{{ route('results.analytics') }}" class="flex flex-wrap gap-2">
                <select name="class_id" class="border border-slate-200 rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    @foreach($classes as $c)
                    <option value="{{ $c->id }}" {{ $classId == $c->id ? 'selected' : '' }}>{{ $c->class_name }}</option>
                    @endforeach
                </select>
                <select name="semester_id" class="border border-slate-200 rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    @foreach($semesters as $s)
                    <option value="{{ $s->id }}" {{ $semesterId == $s->id ? 'selected' : '' }}>{{ $s->semester_name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition">Apply</button>
            </form>
        </div>

        {{-- KPI --}}
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
                <p class="text-2xl font-bold text-indigo-600">{{ count($subjectAverages) }}</p>
                <p class="text-xs text-slate-400 mt-0.5">Subjects</p>
            </div>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
                <p class="text-2xl font-bold text-emerald-600">{{ $avgGpa }}</p>
                <p class="text-xs text-slate-400 mt-0.5">Class Avg GPA</p>
            </div>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
                <p class="text-2xl font-bold text-amber-600">{{ $topStudents->count() }}</p>
                <p class="text-xs text-slate-400 mt-0.5">Top Performers</p>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">
            {{-- Subject avg bar chart --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <p class="text-sm font-semibold text-slate-700 mb-4">Average Marks per Subject</p>
                <div id="subjectChart"></div>
            </div>
            {{-- Grade distribution pie --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <p class="text-sm font-semibold text-slate-700 mb-4">Grade Distribution</p>
                <div id="gradeChart"></div>
            </div>
        </div>

        {{-- Top performers --}}
        @if($topStudents->isNotEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100">
                <p class="text-sm font-semibold text-slate-700">Top 5 Students</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-xs text-slate-400 bg-slate-50 text-left">
                        <th class="px-5 py-2.5 font-medium">Rank</th>
                        <th class="px-4 py-2.5 font-medium">Student</th>
                        <th class="px-4 py-2.5 font-medium text-center">GPA</th>
                        <th class="px-4 py-2.5 font-medium text-center">Total Marks</th>
                        <th class="px-4 py-2.5 font-medium text-center">Passed</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($topStudents as $row)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-bold text-amber-500">
                                @if($row['rank']==1)🥇@elseif($row['rank']==2)🥈@elseif($row['rank']==3)🥉@else #{{ $row['rank'] }} @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <img src="{{ $row['student']?->avatar }}" class="w-7 h-7 rounded-full object-cover" alt="">
                                    <span class="font-medium text-slate-700">{{ $row['student']?->full_name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center font-bold text-indigo-600">{{ $row['gpa'] }}</td>
                            <td class="px-4 py-3 text-center text-slate-600">{{ round($row['totalMarks'], 1) }}</td>
                            <td class="px-4 py-3 text-center text-emerald-600 font-semibold">{{ $row['passed'] }}</td>
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
    const subjectData = @json($subjectAverages);
    const gradeData   = @json($gradeDistribution);

    // Subject averages bar chart
    if (subjectData.length) {
        new ApexCharts(document.getElementById('subjectChart'), {
            chart:   { type: 'bar', height: 260, toolbar: { show: false } },
            colors:  ['#6366f1'],
            plotOptions: { bar: { borderRadius: 6, horizontal: true } },
            series:  [{ name: 'Avg Marks', data: subjectData.map(r => parseFloat(r.avg_marks)) }],
            xaxis:   { categories: subjectData.map(r => r.course_name), labels: { style: { fontSize: '11px' } } },
            tooltip: { y: { formatter: v => v + ' marks' } },
            grid:    { borderColor: '#f1f5f9' },
        }).render();
    }

    // Grade distribution donut
    if (gradeData.length) {
        new ApexCharts(document.getElementById('gradeChart'), {
            chart:  { type: 'donut', height: 260 },
            series: gradeData.map(r => r.count),
            labels: gradeData.map(r => 'Grade ' + r.grade),
            colors: ['#6366f1','#10b981','#f59e0b','#f43f5e','#8b5cf6','#06b6d4'],
            legend: { position: 'bottom', fontSize: '12px' },
        }).render();
    }
});
</script>
@endpush
@endsection
