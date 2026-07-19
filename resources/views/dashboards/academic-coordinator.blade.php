@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">
        @include('layouts.left-menu')
    </div>

    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Academic Coordinator</h1>
                <p class="text-slate-400 text-sm mt-0.5">{{ now()->format('l, F j, Y') }}</p>
            </div>
            <div class="flex gap-2">
                @can('view classes')
                <a href="{{ url('classes') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                    <i class="bi bi-diagram-3"></i> Classes
                </a>
                @endcan
                @can('view routines')
                <a href="{{ route('section.routine.show') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 text-sm font-medium rounded-lg hover:bg-slate-50 transition text-slate-700">
                    <i class="bi bi-calendar4-range"></i> Timetable
                </a>
                @endcan
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">Students</p>
                <p class="text-3xl font-bold text-slate-800">{{ number_format($studentCount) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">Teachers</p>
                <p class="text-3xl font-bold text-slate-800">{{ number_format($teacherCount) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">Classes</p>
                <p class="text-3xl font-bold text-slate-800">{{ number_format($classCount) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">Today Attendance</p>
                <p class="text-3xl font-bold {{ $attendancePct >= 75 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $attendancePct }}%</p>
            </div>
        </div>

        {{-- Upcoming Exams + Events --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 flex justify-between items-center">
                    <p class="text-sm font-semibold text-slate-700"><i class="bi bi-file-earmark-text me-1 text-rose-500"></i>Upcoming Exams</p>
                    @can('view exams')<a href="{{ route('exam.list.show') }}" class="text-xs text-indigo-600 hover:underline">View all</a>@endcan
                </div>
                @if($upcomingExams->count())
                <div class="divide-y divide-slate-50">
                    @foreach($upcomingExams as $exam)
                    <div class="px-5 py-3 flex justify-between items-center">
                        <div>
                            <p class="text-sm font-medium text-slate-700">{{ $exam->exam_name }}</p>
                            <p class="text-xs text-slate-400">{{ $exam->course?->name ?? '—' }}</p>
                        </div>
                        <span class="text-xs font-medium text-rose-700 bg-rose-50 px-2 py-1 rounded-lg">{{ \Carbon\Carbon::parse($exam->start_date)->format('M d') }}</span>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-400 text-center py-8">No upcoming exams.</p>
                @endif
            </div>

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 flex justify-between items-center">
                    <p class="text-sm font-semibold text-slate-700"><i class="bi bi-calendar-event me-1 text-amber-500"></i>Upcoming Events</p>
                    @can('view events')<a href="{{ route('events.show') }}" class="text-xs text-indigo-600 hover:underline">Calendar</a>@endcan
                </div>
                @if($upcomingEvents->count())
                <div class="divide-y divide-slate-50">
                    @foreach($upcomingEvents as $event)
                    <div class="px-5 py-3 flex justify-between items-center">
                        <p class="text-sm text-slate-700">{{ $event->title }}</p>
                        <span class="text-xs text-amber-700 bg-amber-50 px-2 py-1 rounded-lg">{{ \Carbon\Carbon::parse($event->start)->format('M d') }}</span>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-400 text-center py-8">No upcoming events.</p>
                @endif
            </div>
        </div>

        {{-- Attendance Trend + Notices --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-graph-up-arrow me-1 text-indigo-500"></i>Attendance Trend (7 Days)</p>
                <div id="chart-ac-attendance" style="min-height:160px"></div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100">
                    <p class="text-sm font-semibold text-slate-700"><i class="bi bi-megaphone me-1 text-amber-500"></i>Notice Board</p>
                </div>
                @if($notices->count())
                <div class="divide-y divide-slate-50">
                    @foreach($notices->take(5) as $notice)
                    <div class="px-5 py-3 text-sm text-slate-600 line-clamp-2">{!! \Stevebauman\Purify\Facades\Purify::clean(strip_tags($notice->notice)) !!}</div>
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
    var days    = @json($attendanceTrend->pluck('day')->map(fn($d) => \Carbon\Carbon::parse($d)->format('D M d')));
    var present = @json($attendanceTrend->pluck('present')->map(fn($v) => (int)$v));
    var absent  = @json($attendanceTrend->pluck('absent')->map(fn($v) => (int)$v));
    if (document.getElementById('chart-ac-attendance')) {
        new ApexCharts(document.getElementById('chart-ac-attendance'), {
            chart: { type: 'area', height: 160, toolbar: { show: false } },
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
