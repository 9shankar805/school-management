@extends('layouts.app')
@section('title', 'Vice Principal Dashboard - NepalSchoolHub')

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
[data-theme="dark"] .iemis-stat-card:hover,
html.dark .iemis-stat-card:hover {
    border-color: #3b82f6 !important;
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
.iemis-badge-green {
    font-size: 10px !important;
    font-weight: 700 !important;
    color: #10b981 !important;
}
.iemis-badge-amber {
    font-size: 10px !important;
    font-weight: 700 !important;
    color: #f59e0b !important;
}
.iemis-badge-rose {
    font-size: 10px !important;
    font-weight: 700 !important;
    color: #ef4444 !important;
}
.iemis-badge-indigo {
    font-size: 10px !important;
    font-weight: 700 !important;
    color: #6366f1 !important;
}
</style>
@endpush

@section('content')
<div class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto space-y-6 bg-slate-50/60 dark:bg-slate-950 min-h-screen">

    {{-- ── HEADER ── --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                Good {{ now()->hour < 12 ? 'Morning' : (now()->hour < 18 ? 'Afternoon' : 'Evening') }}, {{ auth()->user()->first_name }}! 👋
            </h1>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                {{ now()->format('l, F j, Y') }} &bull; {{ now()->format('g:i A') }}
                &bull; <span class="text-indigo-600 dark:text-indigo-400 font-bold">आ.व. {{ \App\Services\NepaliDateService::currentFiscalYear() }}</span>
                &bull; <span class="font-semibold text-slate-600 dark:text-slate-300">Vice Principal</span>
            </p>
        </div>
        <div class="flex flex-wrap gap-2 items-center">
            <a href="{{ url('classes') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 shadow-sm shadow-indigo-600/25 transition active:scale-95">
                <i class="bi bi-diagram-3-fill text-xs"></i> Classes & Sections
            </a>
            @can('view attendances')
            <a href="{{ route('attendance.list.show') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 transition active:scale-95">
                <i class="bi bi-calendar-check text-emerald-500"></i> Attendance
            </a>
            @endcan
            @can('create notices')
            <a href="{{ route('notice.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 transition active:scale-95">
                <i class="bi bi-megaphone text-amber-500"></i> Notice
            </a>
            @endcan
        </div>
    </div>

    {{-- ── 2. CEHRD IEMIS 6 STAT METRIC CARDS ── --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5 sm:gap-4">
        {{-- Card 1: Students --}}
        <a href="{{ route('student.list.show') }}" class="iemis-stat-card">
            <div class="iemis-icon-wrapper">🎓</div>
            <div class="iemis-card-details">
                <span class="iemis-title">Students</span>
                <div class="iemis-num-group">
                    <span class="iemis-value">{{ number_format($studentCount) }}</span>
                    @if(isset($enrollmentGrowthPct) && $enrollmentGrowthPct !== null)
                        <span class="{{ $enrollmentGrowthPct >= 0 ? 'iemis-badge-green' : 'iemis-badge-rose' }}">
                            {{ $enrollmentGrowthPct >= 0 ? '+' : '' }}{{ $enrollmentGrowthPct }}%
                        </span>
                    @else
                        <span class="iemis-badge-green">Active</span>
                    @endif
                </div>
            </div>
        </a>

        {{-- Card 2: Teachers --}}
        <a href="{{ route('teacher.list.show') }}" class="iemis-stat-card">
            <div class="iemis-icon-wrapper">🧑‍🏫</div>
            <div class="iemis-card-details">
                <span class="iemis-title">Teachers</span>
                <div class="iemis-num-group">
                    <span class="iemis-value">{{ number_format($teacherCount) }}</span>
                    <span class="iemis-badge-green">Active</span>
                </div>
            </div>
        </a>

        {{-- Card 3: Staff --}}
        <div class="iemis-stat-card">
            <div class="iemis-icon-wrapper">💼</div>
            <div class="iemis-card-details">
                <span class="iemis-title">Staff</span>
                <div class="iemis-num-group">
                    <span class="iemis-value">{{ number_format($staffCount ?? 0) }}</span>
                    <span class="iemis-badge-indigo">On Duty</span>
                </div>
            </div>
        </div>

        {{-- Card 4: Classes --}}
        <a href="{{ url('classes') }}" class="iemis-stat-card">
            <div class="iemis-icon-wrapper">🏫</div>
            <div class="iemis-card-details">
                <span class="iemis-title">Classes</span>
                <div class="iemis-num-group">
                    <span class="iemis-value">{{ number_format($classCount) }}</span>
                    <span class="iemis-badge-amber">Sections</span>
                </div>
            </div>
        </a>

        {{-- Card 5: Attendance --}}
        <div class="iemis-stat-card">
            <div class="iemis-icon-wrapper">📅</div>
            <div class="iemis-card-details">
                <span class="iemis-title">Attendance</span>
                <div class="iemis-num-group">
                    <span class="iemis-value">{{ $attendancePct }}%</span>
                    <span class="{{ $attendancePct >= 75 ? 'iemis-badge-green' : 'iemis-badge-rose' }}">Today</span>
                </div>
            </div>
        </div>

        {{-- Card 6: Upcoming Exams --}}
        <div class="iemis-stat-card">
            <div class="iemis-icon-wrapper">📋</div>
            <div class="iemis-card-details">
                <span class="iemis-title">Exams</span>
                <div class="iemis-num-group">
                    <span class="iemis-value">{{ $upcomingExams->count() }}</span>
                    <span class="iemis-badge-amber">Scheduled</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 3. Charts & Analytics Grid ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 sm:gap-6">
        {{-- Attendance Trend Area Chart --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-5 sm:p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">7-Day Attendance Trend</h3>
                        <p class="text-[11px] text-slate-400">Institutional daily attendance tracking</p>
                    </div>
                </div>
                <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $attendancePct >= 75 ? 'bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-rose-50 dark:bg-rose-950 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800' }}">
                    {{ $attendancePct }}% Today
                </span>
            </div>
            <div id="chart-vp-attendance" style="min-height:220px"></div>
        </div>

        {{-- Upcoming Exams --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50/60 dark:bg-slate-800/40">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-sm">
                        <i class="bi bi-file-earmark-text-fill"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Upcoming Exams Schedule</h3>
                        <p class="text-[11px] text-slate-400">Next 30 days assessment roadmap</p>
                    </div>
                </div>
                @can('view exams')
                <a href="{{ route('exam.list.show') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">View all &rarr;</a>
                @endcan
            </div>
            <div class="p-4 flex-1">
                @if($upcomingExams->count())
                <div class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    @foreach($upcomingExams as $exam)
                    <div class="py-3 flex justify-between items-center hover:bg-slate-50/70 dark:hover:bg-slate-800/40 px-2 rounded-xl transition-colors">
                        <div>
                            <p class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100">{{ $exam->exam_name }}</p>
                            <p class="text-[11px] text-slate-400 dark:text-slate-500">{{ $exam->course?->name ?? 'Assessment' }}</p>
                        </div>
                        <span class="text-xs font-bold text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-900/60 px-2.5 py-1 rounded-xl">
                            {{ \Carbon\Carbon::parse($exam->start_date)->format('M d') }}
                        </span>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-10">
                    <i class="bi bi-calendar2-check text-3xl text-slate-300 dark:text-slate-600 block mb-2"></i>
                    <p class="text-xs text-slate-400 font-medium">No upcoming exams scheduled.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── 4. Notice Board & Recent Admissions ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 sm:gap-6">
        {{-- Campus Notice Board --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50/60 dark:bg-slate-800/40">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm">
                        <i class="bi bi-megaphone-fill"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Notice Board</h3>
                        <p class="text-[11px] text-slate-400">Campus notifications</p>
                    </div>
                </div>
                @can('create notices')
                <a href="{{ route('notice.create') }}" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">+ New notice</a>
                @endcan
            </div>
            <div class="p-4 flex-1">
                @if($notices->count())
                <div class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    @foreach($notices->take(4) as $notice)
                    <div class="py-3 px-2 rounded-xl hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                        <div class="text-xs sm:text-sm text-slate-700 dark:text-slate-300 line-clamp-2 leading-relaxed">
                            {!! \Stevebauman\Purify\Facades\Purify::clean(strip_tags($notice->notice)) !!}
                        </div>
                        <span class="text-[10px] text-slate-400 dark:text-slate-500 mt-1 block">{{ $notice->created_at->diffForHumans() }}</span>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-10">
                    <p class="text-xs text-slate-400 font-medium">No notices published yet.</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Recent Admissions --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50/60 dark:bg-slate-800/40">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm">
                        <i class="bi bi-person-check-fill"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Recent Student Admissions</h3>
                        <p class="text-[11px] text-slate-400">Newly enrolled students</p>
                    </div>
                </div>
                <a href="{{ route('student.list.show') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">View all &rarr;</a>
            </div>
            <div class="p-4 flex-1">
                @if(isset($recentAdmissions) && $recentAdmissions->count())
                <div class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    @foreach($recentAdmissions->take(4) as $s)
                    <div class="py-2.5 px-2 flex items-center gap-3 rounded-xl hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                        <img src="{{ $s->avatar }}" class="w-9 h-9 rounded-2xl object-cover flex-shrink-0 border border-slate-200 dark:border-slate-700" alt="">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100 truncate">{{ $s->full_name }}</p>
                            <p class="text-[11px] text-slate-400 dark:text-slate-500">Admitted {{ $s->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-10">
                    <p class="text-xs text-slate-400 font-medium">No recent student admissions recorded.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
(function () {
    var attDays = @json($attendanceTrend->pluck('day')->map(fn($d) => \Carbon\Carbon::parse($d)->format('D M d')));
    var attPct  = @json($attendanceTrend->pluck('pct'));

    if (document.getElementById('chart-vp-attendance')) {
        new ApexCharts(document.getElementById('chart-vp-attendance'), {
            chart: { type: 'area', height: 220, toolbar: { show: false }, fontFamily: 'Inter, system-ui, sans-serif' },
            colors: ['#6366f1'],
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05, stops: [0, 100] } },
            stroke: { curve: 'smooth', width: 2.5 },
            series: [{ name: 'Attendance %', data: attPct }],
            xaxis: { categories: attDays, labels: { style: { fontSize: '10px', colors: '#94a3b8' } } },
            yaxis: { min: 0, max: 100, labels: { formatter: v => v + '%', style: { fontSize: '10px', colors: '#94a3b8' } } },
            dataLabels: { enabled: false },
            grid: { borderColor: 'rgba(148, 163, 184, 0.15)' }
        }).render();
    }
})();
</script>
@endpush
@endsection
