@extends('layouts.app')

@section('title', 'Academic Coordinator Dashboard - NepalSchoolHub')

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
    flex-wrap: wrap !important;
    align-items: baseline !important;
    gap: 2px 6px !important;
    width: 100% !important;
}
.iemis-value {
    font-size: 18px !important;
    font-weight: 800 !important;
    color: #0f172a !important;
    line-height: 1.15 !important;
    letter-spacing: -0.01em !important;
    white-space: nowrap !important;
}
[data-theme="dark"] .iemis-value,
html.dark .iemis-value {
    color: #f8fafc !important;
}
.iemis-badge-green {
    font-size: 10.5px !important;
    font-weight: 700 !important;
    color: #10b981 !important;
    white-space: nowrap !important;
}
.iemis-badge-amber {
    font-size: 10.5px !important;
    font-weight: 700 !important;
    color: #f59e0b !important;
    white-space: nowrap !important;
}
.iemis-badge-rose {
    font-size: 10.5px !important;
    font-weight: 700 !important;
    color: #ef4444 !important;
    white-space: nowrap !important;
}
.iemis-badge-indigo {
    font-size: 10.5px !important;
    font-weight: 700 !important;
    color: #6366f1 !important;
    white-space: nowrap !important;
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
                &bull; <span class="font-semibold text-slate-600 dark:text-slate-300">Academic Coordinator</span>
            </p>
        </div>
        <div class="flex flex-wrap gap-2 items-center">
            @can('view classes')
            <a href="{{ url('classes') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 shadow-sm shadow-indigo-600/25 transition active:scale-95">
                <i class="bi bi-diagram-3-fill text-xs"></i> Classes & Sections
            </a>
            @endcan
            @can('view routines')
            <a href="{{ route('section.routine.show') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 transition active:scale-95">
                <i class="bi bi-calendar4-range text-indigo-500"></i> Timetable
            </a>
            @endcan
            @if(\Illuminate\Support\Facades\Route::has('question-papers.create'))
            <a href="{{ route('question-papers.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 hover:bg-amber-100 transition active:scale-95">
                <i class="bi bi-stars text-amber-500"></i> AI Question Paper
            </a>
            @endif
        </div>
    </div>

    {{-- ── 2. CEHRD IEMIS 6 STAT METRIC CARDS ── --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5 sm:gap-4">
        {{-- Card 1: Students --}}
        <a href="{{ url('students') }}" class="iemis-stat-card">
            <div class="iemis-icon-wrapper">🎓</div>
            <div class="iemis-card-details">
                <div class="iemis-title">Students</div>
                <div class="iemis-num-group">
                    <span class="iemis-value">{{ number_format($studentCount ?? 0) }}</span>
                    <span class="iemis-badge-green">Active</span>
                </div>
            </div>
        </a>

        {{-- Card 2: Teachers --}}
        <a href="{{ url('teachers') }}" class="iemis-stat-card">
            <div class="iemis-icon-wrapper">🧑‍🏫</div>
            <div class="iemis-card-details">
                <div class="iemis-title">Faculty</div>
                <div class="iemis-num-group">
                    <span class="iemis-value">{{ number_format($teacherCount ?? 0) }}</span>
                    <span class="iemis-badge-indigo">Assigned</span>
                </div>
            </div>
        </a>

        {{-- Card 3: Classes --}}
        <a href="{{ url('classes') }}" class="iemis-stat-card">
            <div class="iemis-icon-wrapper">🏫</div>
            <div class="iemis-card-details">
                <div class="iemis-title">Classes</div>
                <div class="iemis-num-group">
                    <span class="iemis-value">{{ number_format($classCount ?? 0) }}</span>
                    <span class="iemis-badge-amber">Streams</span>
                </div>
            </div>
        </a>

        {{-- Card 4: Attendance --}}
        <a href="{{ url('attendance') }}" class="iemis-stat-card">
            <div class="iemis-icon-wrapper">📅</div>
            <div class="iemis-card-details">
                <div class="iemis-title">Attendance</div>
                <div class="iemis-num-group">
                    <span class="iemis-value">{{ $attendancePct ?? 0 }}%</span>
                    <span class="iemis-badge-green">{{ $todayPresent ?? 0 }} Pres</span>
                </div>
            </div>
        </a>

        {{-- Card 5: Routines --}}
        <a href="{{ route('section.routine.show') }}" class="iemis-stat-card">
            <div class="iemis-icon-wrapper">⏰</div>
            <div class="iemis-card-details">
                <div class="iemis-title">Routines</div>
                <div class="iemis-num-group">
                    <span class="iemis-value">{{ $timetableCount ?? $classCount ?? 0 }}</span>
                    <span class="iemis-badge-indigo">Schedules</span>
                </div>
            </div>
        </a>

        {{-- Card 6: Syllabus / Subjects --}}
        <a href="{{ url('courses') }}" class="iemis-stat-card">
            <div class="iemis-icon-wrapper">📋</div>
            <div class="iemis-card-details">
                <div class="iemis-title">Subjects</div>
                <div class="iemis-num-group">
                    <span class="iemis-value">{{ $subjectCount ?? 'CDC 2078' }}</span>
                    <span class="iemis-badge-amber">Curriculum</span>
                </div>
            </div>
        </a>
    </div>

    {{-- ── 3. Quick Operations Grid ── --}}
    <div class="bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 sm:p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                <i class="bi bi-lightning-charge-fill text-amber-500"></i> Academic Quick Shortcuts
            </span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5">
            <a href="{{ route('section.routine.show') }}" class="flex items-center gap-2.5 p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-700/60 hover:border-indigo-300 dark:hover:border-indigo-600 hover:shadow-xs transition text-slate-700 dark:text-slate-200 text-xs font-medium">
                <div class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-950 flex items-center justify-center text-indigo-600 dark:text-indigo-400 flex-shrink-0">
                    <i class="bi bi-calendar3"></i>
                </div>
                <span class="truncate">Timetable</span>
            </a>
            @if(\Illuminate\Support\Facades\Route::has('exam.list.show'))
            <a href="{{ route('exam.list.show') }}" class="flex items-center gap-2.5 p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-700/60 hover:border-rose-300 dark:hover:border-rose-600 hover:shadow-xs transition text-slate-700 dark:text-slate-200 text-xs font-medium">
                <div class="w-7 h-7 rounded-lg bg-rose-50 dark:bg-rose-950 flex items-center justify-center text-rose-600 dark:text-rose-400 flex-shrink-0">
                    <i class="bi bi-journal-text"></i>
                </div>
                <span class="truncate">Exam Center</span>
            </a>
            @endif
            @if(\Illuminate\Support\Facades\Route::has('exam.halls.index'))
            <a href="{{ route('exam.halls.index') }}" class="flex items-center gap-2.5 p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-700/60 hover:border-amber-300 dark:hover:border-amber-600 hover:shadow-xs transition text-slate-700 dark:text-slate-200 text-xs font-medium">
                <div class="w-7 h-7 rounded-lg bg-amber-50 dark:bg-amber-950 flex items-center justify-center text-amber-600 dark:text-amber-400 flex-shrink-0">
                    <i class="bi bi-diagram-2"></i>
                </div>
                <span class="truncate">Seat Planner</span>
            </a>
            @endif
            <a href="{{ url('students/id-card') }}" class="flex items-center gap-2.5 p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-700/60 hover:border-blue-300 dark:hover:border-blue-600 hover:shadow-xs transition text-slate-700 dark:text-slate-200 text-xs font-medium">
                <div class="w-7 h-7 rounded-lg bg-blue-50 dark:bg-blue-950 flex items-center justify-center text-blue-600 dark:text-blue-400 flex-shrink-0">
                    <i class="bi bi-person-badge"></i>
                </div>
                <span class="truncate">QR ID Cards</span>
            </a>
            <a href="{{ url('grades') }}" class="flex items-center gap-2.5 p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-700/60 hover:border-emerald-300 dark:hover:border-emerald-600 hover:shadow-xs transition text-slate-700 dark:text-slate-200 text-xs font-medium">
                <div class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-950 flex items-center justify-center text-emerald-600 dark:text-emerald-400 flex-shrink-0">
                    <i class="bi bi-award"></i>
                </div>
                <span class="truncate">CDC Grading</span>
            </a>
            <a href="{{ route('events.show') }}" class="flex items-center gap-2.5 p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-700/60 hover:border-purple-300 dark:hover:border-purple-600 hover:shadow-xs transition text-slate-700 dark:text-slate-200 text-xs font-medium">
                <div class="w-7 h-7 rounded-lg bg-purple-50 dark:bg-purple-950 flex items-center justify-center text-purple-600 dark:text-purple-400 flex-shrink-0">
                    <i class="bi bi-calendar-event"></i>
                </div>
                <span class="truncate">Calendar</span>
            </a>
        </div>
    </div>

    {{-- ── 4. Main Two-Column Layout ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Upcoming Exams --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/30">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-950 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                        <i class="bi bi-file-earmark-text-fill text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-800 dark:text-white">Upcoming Examinations</h2>
                        <p class="text-[11px] text-slate-400">Scheduled for the next 30 days</p>
                    </div>
                </div>
                @can('view exams')
                @if(\Illuminate\Support\Facades\Route::has('exam.list.show'))
                <a href="{{ route('exam.list.show') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                    View all <i class="bi bi-arrow-right text-[10px]"></i>
                </a>
                @endif
                @endcan
            </div>

            <div class="p-4 flex-1">
                @if(isset($upcomingExams) && $upcomingExams->count())
                <div class="space-y-3">
                    @foreach($upcomingExams as $exam)
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 flex items-center justify-between hover:border-slate-300 dark:hover:border-slate-700 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-rose-100/70 dark:bg-rose-900/40 text-rose-700 dark:text-rose-300 flex flex-col items-center justify-center text-center flex-shrink-0">
                                <span class="text-[9px] font-bold uppercase leading-none">{{ \Carbon\Carbon::parse($exam->start_date)->format('M') }}</span>
                                <span class="text-sm font-black leading-none mt-0.5">{{ \Carbon\Carbon::parse($exam->start_date)->format('d') }}</span>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ $exam->exam_name }}</h4>
                                <p class="text-xs text-slate-400 flex items-center gap-2 mt-0.5">
                                    <span><i class="bi bi-book me-1"></i>{{ $exam->course?->course_name ?? 'All Courses' }}</span>
                                    @if($exam->session)
                                    <span>• {{ $exam->session->session_name }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <span class="text-xs font-semibold text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-900/50 px-2.5 py-1 rounded-lg">
                            {{ \Carbon\Carbon::parse($exam->start_date)->diffForHumans() }}
                        </span>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="py-10 text-center flex flex-col items-center justify-center">
                    <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 mb-3">
                        <i class="bi bi-calendar-x text-2xl"></i>
                    </div>
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">No Upcoming Exams Scheduled</p>
                    <p class="text-xs text-slate-400 mt-1 max-w-xs">All current term exams are completed or not yet scheduled.</p>
                    @if(\Illuminate\Support\Facades\Route::has('exam.list.show'))
                    <a href="{{ route('exam.list.show') }}" class="mt-3 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                        + Schedule New Exam
                    </a>
                    @endif
                </div>
                @endif
            </div>
        </div>

        {{-- Upcoming Events --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/30">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                        <i class="bi bi-calendar-event-fill text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-800 dark:text-white">Academic Calendar & Events</h2>
                        <p class="text-[11px] text-slate-400">Next 14 days activities</p>
                    </div>
                </div>
                @can('view events')
                <a href="{{ route('events.show') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                    Open Calendar <i class="bi bi-arrow-right text-[10px]"></i>
                </a>
                @endcan
            </div>

            <div class="p-4 flex-1">
                @if(isset($upcomingEvents) && $upcomingEvents->count())
                <div class="space-y-3">
                    @foreach($upcomingEvents as $event)
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 flex items-center justify-between hover:border-slate-300 dark:hover:border-slate-700 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-100/70 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 flex flex-col items-center justify-center text-center flex-shrink-0">
                                <span class="text-[9px] font-bold uppercase leading-none">{{ \Carbon\Carbon::parse($event->start)->format('M') }}</span>
                                <span class="text-sm font-black leading-none mt-0.5">{{ \Carbon\Carbon::parse($event->start)->format('d') }}</span>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ $event->title }}</h4>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    <i class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::parse($event->start)->format('g:i A') }}
                                </p>
                            </div>
                        </div>
                        <span class="text-xs font-semibold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-900/50 px-2.5 py-1 rounded-lg">
                            {{ \Carbon\Carbon::parse($event->start)->diffForHumans() }}
                        </span>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="py-10 text-center flex flex-col items-center justify-center">
                    <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 mb-3">
                        <i class="bi bi-calendar4-week text-2xl"></i>
                    </div>
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">No Upcoming Events</p>
                    <p class="text-xs text-slate-400 mt-1 max-w-xs">There are no upcoming PTMs, vacations, or ceremonies in the next two weeks.</p>
                    <a href="{{ route('events.show') }}" class="mt-3 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                        + Add Event to Calendar
                    </a>
                </div>
                @endif
            </div>
        </div>

    </div>

    {{-- ── 5. Analytics & Notice Board ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Attendance Trend (2 Columns) --}}
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-5 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                        <i class="bi bi-graph-up text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-800 dark:text-white">Attendance Trajectory (Last 7 Days)</h2>
                        <p class="text-[11px] text-slate-400">Daily student presence vs absence trends</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 text-xs">
                    <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-medium">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Present
                    </span>
                    <span class="inline-flex items-center gap-1 text-rose-500 dark:text-rose-400 font-medium ms-2">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span> Absent
                    </span>
                </div>
            </div>

            <div class="flex-1 min-h-[220px]">
                <div id="chart-ac-attendance" class="w-full"></div>
            </div>
        </div>

        {{-- Notice Board (1 Column) --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/30">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                        <i class="bi bi-megaphone-fill text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-800 dark:text-white">Notice Board</h2>
                        <p class="text-[11px] text-slate-400">Institutional circulars</p>
                    </div>
                </div>
                @can('view notices')
                <a href="{{ url('notices') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">All</a>
                @endcan
            </div>

            <div class="p-4 flex-1">
                @if(isset($notices) && $notices->count())
                <div class="space-y-3">
                    @foreach($notices->take(5) as $notice)
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 text-xs">
                        <div class="flex items-center justify-between text-[11px] text-slate-400 mb-1">
                            <span class="font-semibold text-indigo-600 dark:text-indigo-400"><i class="bi bi-pin-angle me-1"></i>Notice</span>
                            <span>{{ $notice->created_at ? $notice->created_at->format('M d, Y') : '' }}</span>
                        </div>
                        <div class="text-slate-700 dark:text-slate-300 line-clamp-2 leading-relaxed">
                            {!! \Stevebauman\Purify\Facades\Purify::clean(strip_tags($notice->notice)) !!}
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="py-12 text-center flex flex-col items-center justify-center">
                    <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 mb-2">
                        <i class="bi bi-bell-slash text-xl"></i>
                    </div>
                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-300">No Active Notices</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Notices posted by administration will show here.</p>
                </div>
                @endif
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
(function () {
    @php
        $attTrendCol = collect($attendanceTrend ?? []);
        $daysArr    = $attTrendCol->pluck('day')->map(fn($d) => $d ? \Carbon\Carbon::parse($d)->format('D M d') : '')->values()->toArray();
        $presentArr = $attTrendCol->pluck('present')->map(fn($v) => (int)$v)->values()->toArray();
        $absentArr  = $attTrendCol->pluck('absent')->map(fn($v) => (int)$v)->values()->toArray();
    @endphp

    var days    = @json($daysArr);
    var present = @json($presentArr);
    var absent  = @json($absentArr);

    if (days.length === 0) {
        days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        present = [0, 0, 0, 0, 0, 0, 0];
        absent = [0, 0, 0, 0, 0, 0, 0];
    }

    if (document.getElementById('chart-ac-attendance')) {
        new ApexCharts(document.getElementById('chart-ac-attendance'), {
            chart: { 
                type: 'area', 
                height: 220, 
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif',
                sparkline: { enabled: false }
            },
            series: [
                { name: 'Present Students', data: present }, 
                { name: 'Absent Students', data: absent }
            ],
            xaxis: { 
                categories: days, 
                labels: { style: { fontSize: '11px', colors: '#94a3b8' } },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: { style: { fontSize: '11px', colors: '#94a3b8' } }
            },
            colors: ['#10b981', '#f43f5e'],
            fill: { 
                type: 'gradient', 
                gradient: { 
                    shadeIntensity: 1, 
                    opacityFrom: 0.45, 
                    opacityTo: 0.05, 
                    stops: [0, 95, 100] 
                } 
            },
            stroke: { curve: 'smooth', width: 2.5 }, 
            dataLabels: { enabled: false },
            legend: { show: false }, 
            grid: { 
                borderColor: 'rgba(226, 232, 240, 0.6)', 
                strokeDashArray: 4,
                xaxis: { lines: { show: false } }
            },
            tooltip: {
                theme: 'dark',
                y: {
                    formatter: function (val) {
                        return val + " students";
                    }
                }
            }
        }).render();
    }
})();
</script>
@endpush
