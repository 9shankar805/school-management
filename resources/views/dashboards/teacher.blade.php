@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">
        @include('layouts.left-menu')
    </div>

    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        {{-- Header --}}
        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Teacher Dashboard</h1>
                <p class="text-slate-400 text-sm mt-0.5">{{ now()->format('l, F j, Y') }} &middot; Welcome, <span class="font-medium text-slate-600">{{ auth()->user()->first_name }}</span></p>
            </div>
            <div class="flex gap-2">
                @can('take attendances')
                <a href="{{ route('attendance.create.show') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                    <i class="bi bi-check2-square"></i> Take Attendance
                </a>
                @endcan
                @can('save marks')
                <a href="{{ route('course.mark.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 text-sm font-medium rounded-lg hover:bg-slate-50 transition text-slate-700">
                    <i class="bi bi-pencil-square"></i> Enter Marks
                </a>
                @endcan
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">My Courses</p>
                    <span class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 text-sm"><i class="bi bi-journal-medical"></i></span>
                </div>
                <p class="text-3xl font-bold text-slate-800">{{ $myCourses->count() }}</p>
                <p class="mt-1 text-xs text-indigo-600">This session</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Upcoming Exams</p>
                    <span class="w-8 h-8 rounded-lg bg-rose-50 flex items-center justify-center text-rose-600 text-sm"><i class="bi bi-file-earmark-text"></i></span>
                </div>
                <p class="text-3xl font-bold text-slate-800">{{ $upcomingExams->count() }}</p>
                <p class="mt-1 text-xs text-rose-600">Next 30 days</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Attendance Days</p>
                    <span class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600 text-sm"><i class="bi bi-calendar-check"></i></span>
                </div>
                <p class="text-3xl font-bold text-slate-800">{{ $recentAttendance->count() }}</p>
                <p class="mt-1 text-xs text-emerald-600">Last 7 days</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Notices</p>
                    <span class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600 text-sm"><i class="bi bi-megaphone"></i></span>
                </div>
                <p class="text-3xl font-bold text-slate-800">{{ $notices->count() }}</p>
                <p class="mt-1 text-xs text-amber-600">Active</p>
            </div>
        </div>

        {{-- My Courses --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mb-6">
            <div class="px-5 py-3 border-b border-slate-100 flex justify-between items-center">
                <p class="text-sm font-semibold text-slate-700"><i class="bi bi-journal-medical me-1 text-indigo-500"></i>My Courses</p>
                <a href="{{ route('course.teacher.list.show', ['teacher_id' => auth()->id()]) }}" class="text-xs text-indigo-600 hover:underline">View all</a>
            </div>
            @if($myCourses->count())
            <div class="divide-y divide-slate-50">
                @foreach($myCourses as $ac)
                @php $summary = $todaySummary[$ac->course_id] ?? null; @endphp
                <div class="px-5 py-3 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-medium text-slate-700">{{ $ac->course->name ?? 'Course' }}</p>
                        <p class="text-xs text-slate-400">Section: {{ $ac->section->name ?? '—' }}
                            @if($summary)
                            &middot; Today: <span class="text-emerald-600">{{ $summary->present }} present</span> / <span class="text-rose-500">{{ $summary->absent }} absent</span>
                            @endif
                        </p>
                    </div>
                    <div class="flex gap-2">
                        @can('take attendances')
                        <a href="{{ route('attendance.create.show') }}" class="text-xs px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 transition">Take Attendance</a>
                        @endcan
                        @can('save marks')
                        <a href="{{ route('course.mark.create') }}" class="text-xs px-3 py-1.5 bg-emerald-50 text-emerald-700 rounded-lg hover:bg-emerald-100 transition">Enter Marks</a>
                        @endcan
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-slate-400 text-center py-10">No courses assigned this session.</p>
            @endif
        </div>

        {{-- Attendance Trend + Upcoming Exams --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-graph-up me-1 text-emerald-500"></i>Attendance Trend (7 Days)</p>
                <div id="chart-teacher-attendance" style="min-height:160px"></div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100">
                    <p class="text-sm font-semibold text-slate-700"><i class="bi bi-file-earmark-text me-1 text-rose-500"></i>Upcoming Exams</p>
                </div>
                @if($upcomingExams->count())
                <div class="divide-y divide-slate-50">
                    @foreach($upcomingExams as $exam)
                    <div class="px-5 py-3 flex justify-between items-center">
                        <div>
                            <p class="text-sm font-medium text-slate-700">{{ $exam->exam_name }}</p>
                            <p class="text-xs text-slate-400">{{ $exam->course?->name ?? '—' }}</p>
                        </div>
                        <span class="text-xs font-medium text-rose-700 bg-rose-50 px-2 py-1 rounded-lg">
                            {{ \Carbon\Carbon::parse($exam->start_date)->format('M d') }}
                        </span>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-400 text-center py-10">No upcoming exams.</p>
                @endif
            </div>
        </div>

        {{-- Notices --}}
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
@endsection

@push('scripts')
<script>
(function () {
    var days    = @json($recentAttendance->pluck('day')->map(fn($d) => \Carbon\Carbon::parse($d)->format('D M d')));
    var present = @json($recentAttendance->pluck('present')->map(fn($v) => (int)$v));
    var absent  = @json($recentAttendance->pluck('absent')->map(fn($v) => (int)$v));

    if (document.getElementById('chart-teacher-attendance')) {
        new ApexCharts(document.getElementById('chart-teacher-attendance'), {
            chart: { type: 'bar', height: 160, stacked: true, toolbar: { show: false } },
            series: [
                { name: 'Present', data: present },
                { name: 'Absent',  data: absent  },
            ],
            xaxis: { categories: days, labels: { style: { fontSize: '10px' } } },
            yaxis: { labels: { style: { fontSize: '10px' } } },
            colors: ['#22c55e', '#f43f5e'],
            plotOptions: { bar: { borderRadius: 3, columnWidth: '55%' } },
            dataLabels: { enabled: false },
            legend: { position: 'top', fontSize: '12px' },
            grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
            tooltip: { shared: true, intersect: false },
        }).render();
    }
})();
</script>
@endpush
