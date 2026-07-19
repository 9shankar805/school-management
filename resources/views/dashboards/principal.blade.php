@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">
        @include('layouts.left-menu')
    </div>

    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Principal Dashboard</h1>
                <p class="text-slate-400 text-sm mt-0.5">{{ now()->format('l, F j, Y') }} &middot; Welcome, {{ auth()->user()->first_name }}</p>
            </div>
            <div class="flex gap-2">
                @can('create notices')
                <a href="{{ route('notice.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                    <i class="bi bi-megaphone"></i> Post Notice
                </a>
                @endcan
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Students</p>
                    <span class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600"><i class="bi bi-people-fill"></i></span>
                </div>
                <p class="text-3xl font-bold text-slate-800">{{ number_format($studentCount) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Teachers</p>
                    <span class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600"><i class="bi bi-person-badge-fill"></i></span>
                </div>
                <p class="text-3xl font-bold text-slate-800">{{ number_format($teacherCount) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Attendance</p>
                    <span class="w-8 h-8 rounded-lg {{ $attendancePct >= 75 ? 'bg-emerald-50' : 'bg-rose-50' }} flex items-center justify-center {{ $attendancePct >= 75 ? 'text-emerald-600' : 'text-rose-600' }}"><i class="bi bi-calendar-check"></i></span>
                </div>
                <p class="text-3xl font-bold {{ $attendancePct >= 75 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $attendancePct }}%</p>
                <p class="mt-1 text-xs text-slate-400">Today</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Classes</p>
                    <span class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600"><i class="bi bi-diagram-3"></i></span>
                </div>
                <p class="text-3xl font-bold text-slate-800">{{ number_format($classCount) }}</p>
            </div>
        </div>

        {{-- Attendance Chart + Upcoming Exams --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-graph-up-arrow me-1 text-indigo-500"></i>7-Day Attendance Trend</p>
                <div id="chart-pr-attendance" style="min-height:180px"></div>
            </div>
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
                        <span class="text-xs font-medium text-rose-700 bg-rose-50 px-2 py-1 rounded-lg">
                            {{ \Carbon\Carbon::parse($exam->start_date)->format('M d') }}
                        </span>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-400 text-center py-8">No upcoming exams.</p>
                @endif
            </div>
        </div>

        {{-- Notices + Recent Admissions --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 flex justify-between items-center">
                    <p class="text-sm font-semibold text-slate-700"><i class="bi bi-megaphone me-1 text-amber-500"></i>Notice Board</p>
                    @can('create notices')<a href="{{ route('notice.create') }}" class="text-xs text-indigo-600 hover:underline">+ New</a>@endcan
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
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100">
                    <p class="text-sm font-semibold text-slate-700"><i class="bi bi-person-check me-1 text-emerald-500"></i>Recent Admissions</p>
                </div>
                @if($recentAdmissions->count())
                <div class="divide-y divide-slate-50">
                    @foreach($recentAdmissions as $s)
                    <div class="px-5 py-3 flex items-center gap-3">
                        <img src="{{ $s->avatar }}" class="w-8 h-8 rounded-full object-cover flex-shrink-0" alt="">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-700 truncate">{{ $s->full_name }}</p>
                            <p class="text-xs text-slate-400">{{ $s->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-400 text-center py-8">No recent admissions.</p>
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
    if (document.getElementById('chart-pr-attendance')) {
        new ApexCharts(document.getElementById('chart-pr-attendance'), {
            chart: { type: 'area', height: 180, toolbar: { show: false } },
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
