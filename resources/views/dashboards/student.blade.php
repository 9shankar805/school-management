@extends('layouts.app')
@section('title', 'Student Dashboard')

@section('content')
<style>
  .student-hero-banner {
    background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%);
    position: relative;
    overflow: hidden;
  }
  .student-hero-banner::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 320px;
    height: 320px;
    background: radial-gradient(circle, rgba(99, 102, 241, 0.25) 0%, rgba(99, 102, 241, 0) 70%);
    border-radius: 50%;
    pointer-events: none;
  }
  .hero-quick-btn {
    background: rgba(255, 255, 255, 0.12);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.18);
    color: #ffffff !important;
    transition: all 0.2s ease-in-out;
  }
  .hero-quick-btn:hover {
    background: rgba(255, 255, 255, 0.22);
    border-color: rgba(255, 255, 255, 0.35);
    transform: translateY(-1px);
  }
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
    flex-wrap: wrap !important;
  }
  .iemis-value {
    font-size: 20px !important;
    font-weight: 800 !important;
    color: #0f172a !important;
    line-height: 1.1 !important;
  }
  [data-theme="dark"] .iemis-value,
  html.dark .iemis-value {
    color: #f8fafc !important;
  }
  .iemis-badge-green { font-size: 10px !important; font-weight: 700 !important; color: #10b981 !important; }
  .iemis-badge-amber { font-size: 10px !important; font-weight: 700 !important; color: #f59e0b !important; }
  .iemis-badge-rose  { font-size: 10px !important; font-weight: 700 !important; color: #ef4444 !important; }
  .iemis-badge-indigo{ font-size: 10px !important; font-weight: 700 !important; color: #6366f1 !important; }
  .iemis-badge-blue  { font-size: 10px !important; font-weight: 700 !important; color: #3b82f6 !important; }
  .iemis-badge-violet{ font-size: 10px !important; font-weight: 700 !important; color: #8b5cf6 !important; }
</style>

<div class="flex-1 p-4 sm:p-6 lg:p-8 overflow-auto max-w-7xl mx-auto">

    {{-- 1. Student Hero Profile Banner --}}
    <div class="student-hero-banner rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-indigo-950/10 mb-6 border border-indigo-900/40">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
            
            {{-- Profile Left --}}
            <div class="flex items-center gap-4 sm:gap-5">
                <div class="relative">
                    @if($student->photo)
                        <img src="{{ asset('storage/' . ltrim($student->photo, '/')) }}" alt="{{ $student->full_name }}" class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover ring-4 ring-white/20 shadow-md">
                    @else
                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-indigo-500/30 backdrop-blur-md ring-4 ring-white/20 flex items-center justify-center text-white text-2xl font-extrabold shadow-md">
                            {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name ?? '', 0, 1) }}
                        </div>
                    @endif
                    <span class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-emerald-500 border-2 border-indigo-900 flex items-center justify-center text-[10px]" title="Active Student">
                        <i class="bi bi-check text-white"></i>
                    </span>
                </div>

                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white">
                            {{ $student->full_name }}
                        </h1>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-400/20 text-indigo-200 border border-indigo-300/20">
                            {{ $classInfo?->schoolClass?->class_name ?? 'Student' }}
                            @if($classInfo?->section)
                                · {{ $classInfo->section->section_name }}
                            @endif
                        </span>
                    </div>

                    <p class="text-xs sm:text-sm text-indigo-200/90 font-medium flex flex-wrap items-center gap-x-3 gap-y-1">
                        <span><i class="bi bi-calendar3 me-1 text-indigo-300"></i>{{ now()->format('l, M d, Y') }} ({{ bsDate(now()->format('Y-m-d'), true, 'short') }})</span>
                        @if($student->academic_info?->roll_number)
                        <span>&bull; Roll No: <strong class="text-white">#{{ $student->academic_info->roll_number }}</strong></span>
                        @endif
                        @if($schoolSession)
                        <span>&bull; Session: <strong class="text-indigo-100">{{ $schoolSession->year_title ?? $schoolSession->name }}</strong></span>
                        @endif
                    </p>
                </div>
            </div>

            {{-- Quick Action Buttons Right --}}
            <div class="flex flex-wrap items-center gap-2.5 w-full lg:w-auto">
                <a href="{{ route('student.id-cards.bulk') }}" class="hero-quick-btn px-3.5 py-2 rounded-xl text-xs font-bold inline-flex items-center gap-2">
                    <i class="bi bi-person-badge-fill text-indigo-300 text-sm"></i> My ID Card
                </a>
                <a href="{{ route('section.routine.show') }}" class="hero-quick-btn px-3.5 py-2 rounded-xl text-xs font-bold inline-flex items-center gap-2">
                    <i class="bi bi-calendar4-week text-emerald-300 text-sm"></i> Timetable
                </a>
                <a href="{{ route('leave.index') }}" class="hero-quick-btn px-3.5 py-2 rounded-xl text-xs font-bold inline-flex items-center gap-2">
                    <i class="bi bi-send-fill text-amber-300 text-sm"></i> Apply Leave
                </a>
                <a href="{{ route('online-exams.student.index') }}" class="hero-quick-btn px-3.5 py-2 rounded-xl text-xs font-bold inline-flex items-center gap-2">
                    <i class="bi bi-laptop text-rose-300 text-sm"></i> Online Tests
                </a>
            </div>

        </div>
    </div>

    @include('session-messages')

    {{-- 2. CEHRD IEMIS 3D Stat Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5 sm:gap-4 mb-6">
        
        {{-- Attendance --}}
        <a href="{{ route('student.attendance.show', $student->id) }}" class="iemis-stat-card">
            <div class="iemis-icon-wrapper">📈</div>
            <div class="iemis-card-details">
                <span class="iemis-title">Attendance</span>
                <div class="iemis-num-group">
                    <span class="iemis-value {{ $attendancePct >= 75 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">{{ $attendancePct }}%</span>
                    @if($attendancePct >= 75)
                        <span class="iemis-badge-green">{{ $presentCount }}/{{ $totalAttendance }}d</span>
                    @else
                        <span class="iemis-badge-rose">{{ $presentCount }}/{{ $totalAttendance }}d</span>
                    @endif
                </div>
            </div>
        </a>

        {{-- Class & Section --}}
        <div class="iemis-stat-card">
            <div class="iemis-icon-wrapper">🎓</div>
            <div class="iemis-card-details">
                <span class="iemis-title">Class</span>
                <div class="iemis-num-group">
                    <span class="iemis-value truncate max-w-[90px]">{{ $classInfo?->schoolClass?->class_name ?? 'Grade 1' }}</span>
                    <span class="iemis-badge-indigo">{{ preg_replace('/^Section\s+/i', '', $classInfo?->section?->section_name ?? 'A') }}</span>
                </div>
            </div>
        </div>

        {{-- Results --}}
        <a href="{{ route('results.index') }}" class="iemis-stat-card">
            <div class="iemis-icon-wrapper">📊</div>
            <div class="iemis-card-details">
                <span class="iemis-title">Results</span>
                <div class="iemis-num-group">
                    <span class="iemis-value">{{ $marks->count() }}</span>
                    <span class="iemis-badge-blue">Marks</span>
                </div>
            </div>
        </a>

        {{-- Homework Due --}}
        <a href="{{ route('homework.index') }}" class="iemis-stat-card">
            <div class="iemis-icon-wrapper">📝</div>
            <div class="iemis-card-details">
                <span class="iemis-title">Homework</span>
                <div class="iemis-num-group">
                    <span class="iemis-value {{ $homeworkDueCount > 0 ? 'text-amber-600 dark:text-amber-400' : '' }}">{{ $homeworkDueCount }}</span>
                    @if($homeworkDueCount > 0)
                        <span class="iemis-badge-amber">Pending</span>
                    @else
                        <span class="iemis-badge-green">Done</span>
                    @endif
                </div>
            </div>
        </a>

        {{-- Fees Due --}}
        <a href="{{ route('payments.index') }}" class="iemis-stat-card">
            <div class="iemis-icon-wrapper">💳</div>
            <div class="iemis-card-details">
                <span class="iemis-title">Fee Status</span>
                <div class="iemis-num-group">
                    <span class="iemis-value {{ $dueBalance > 0 ? 'text-rose-600 dark:text-rose-400 text-[16px]' : 'text-emerald-600 dark:text-emerald-400' }}">
                        {{ $dueBalance > 0 ? 'Rs.' . number_format($dueBalance, 0) : 'Clear' }}
                    </span>
                    @if($dueBalance > 0)
                        <span class="iemis-badge-rose">Due</span>
                    @else
                        <span class="iemis-badge-green">Paid</span>
                    @endif
                </div>
            </div>
        </a>

        {{-- Leave --}}
        <a href="{{ route('leave.index') }}" class="iemis-stat-card">
            <div class="iemis-icon-wrapper">🗓️</div>
            <div class="iemis-card-details">
                <span class="iemis-title">Leave</span>
                <div class="iemis-num-group">
                    <span class="iemis-value text-violet-600 dark:text-violet-400">{{ $pendingLeaveCount }}</span>
                    @if($pendingLeaveCount > 0)
                        <span class="iemis-badge-violet">Pending</span>
                    @else
                        <span class="iemis-badge-green">Active</span>
                    @endif
                </div>
            </div>
        </a>

    </div>

    {{-- 3. Attendance Trend Analytics Card --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 flex items-center justify-center text-lg">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 dark:text-slate-100 text-base">Monthly Attendance Trends</h3>
                    <p class="text-xs text-slate-400">Presence and absence overview across the last 6 months</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-full text-xs font-bold {{ $attendancePct >= 75 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/40' }}">
                    <i class="bi bi-{{ $attendancePct >= 75 ? 'shield-check' : 'exclamation-triangle' }} me-1"></i>
                    {{ $attendancePct >= 75 ? 'Good Standing ('. $attendancePct .'%)' : 'Attendance Low ('. $attendancePct .'%)' }}
                </span>
                <a href="{{ route('student.attendance.show', $student->id) }}" class="btn btn-sm btn-light border border-slate-200 text-slate-600 text-xs px-3 py-1.5 rounded-xl font-semibold hover:bg-slate-100">
                    Detailed Log
                </a>
            </div>
        </div>

        <div id="chart-student-attendance" class="w-full" style="min-height: 200px;"></div>
    </div>

    {{-- 4. Today's Timetable + Pending Homework (Grid) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- Today's Timetable --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/30">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-lg bg-violet-100 text-violet-700 flex items-center justify-center text-sm"><i class="bi bi-clock-history"></i></span>
                    <h4 class="font-bold text-slate-800 dark:text-slate-100 text-sm">Today's Class Schedule</h4>
                </div>
                <a href="{{ route('section.routine.show') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">Full Timetable &rarr;</a>
            </div>

            <div class="p-5 flex-1">
                @if($todaySchedule->count())
                <div class="space-y-3">
                    @foreach($todaySchedule as $slot)
                    <div class="p-3.5 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 flex items-center justify-between gap-3 hover:bg-indigo-50/40 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-1.5 h-10 rounded-full" style="background: {{ $slot->color ?? '#4f46e5' }}"></div>
                            <div>
                                <h5 class="font-bold text-slate-800 dark:text-slate-100 text-sm mb-0.5">{{ $slot->course?->course_name ?? 'Class Subject' }}</h5>
                                <p class="text-xs text-slate-400 mb-0 flex items-center gap-1.5">
                                    <i class="bi bi-person text-indigo-500"></i> {{ $slot->teacher->full_name ?? 'Assigned Instructor' }}
                                    @if($slot->room)
                                    <span>&bull; Room: {{ $slot->room }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-2xs flex-shrink-0">
                            {{ \Carbon\Carbon::parse($slot->start)->format('h:i A') }} - {{ \Carbon\Carbon::parse($slot->end)->format('h:i A') }}
                        </span>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="py-10 text-center text-slate-400">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-2xl text-slate-300 mb-2">
                        <i class="bi bi-cup-hot-fill"></i>
                    </div>
                    <p class="font-bold text-slate-600 dark:text-slate-300 text-sm mb-0.5">No Classes Scheduled for Today</p>
                    <p class="text-xs text-slate-400">Enjoy your break or review upcoming study materials.</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Pending Homework --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/30">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-sm"><i class="bi bi-pencil-fill"></i></span>
                    <h4 class="font-bold text-slate-800 dark:text-slate-100 text-sm">Pending Homework & Assignments</h4>
                </div>
                <a href="{{ route('homework.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">View All &rarr;</a>
            </div>

            <div class="p-5 flex-1">
                @if($pendingHomework->count())
                <div class="space-y-3">
                    @foreach($pendingHomework as $hw)
                    <a href="{{ route('homework.show', $hw->id) }}" class="p-3.5 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 flex items-center justify-between gap-3 hover:border-amber-300 transition block">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="badge bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 text-[10px] px-2 py-0.5 rounded-md font-semibold">
                                    {{ $hw->course?->course_name ?? 'Subject' }}
                                </span>
                                <h5 class="font-bold text-slate-800 dark:text-slate-100 text-sm truncate mb-0">{{ $hw->title }}</h5>
                            </div>
                            <p class="text-xs text-slate-400 truncate mb-0">{{ Str::limit(strip_tags($hw->description ?? ''), 60) }}</p>
                        </div>
                        <div class="flex-shrink-0 text-end">
                            <span class="text-[11px] font-bold px-2.5 py-1 rounded-lg {{ $hw->due_date->isToday() ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-800' }}">
                                {{ $hw->due_date->isToday() ? 'Due Today' : 'Due ' . $hw->due_date->format('M d') }}
                            </span>
                        </div>
                    </a>
                    @endforeach
                </div>
                @else
                <div class="py-10 text-center text-slate-400">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 flex items-center justify-center text-2xl text-emerald-500 mb-2">
                        <i class="bi bi-patch-check-fill"></i>
                    </div>
                    <p class="font-bold text-slate-700 dark:text-slate-200 text-sm mb-0.5">All Caught Up!</p>
                    <p class="text-xs text-slate-400">You have completed all pending homework submissions.</p>
                </div>
                @endif
            </div>
        </div>

    </div>

    {{-- 5. Upcoming Exams & Practice Quizzes --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- Upcoming Exams --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/30">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center text-sm"><i class="bi bi-journal-text"></i></span>
                    <h4 class="font-bold text-slate-800 dark:text-slate-100 text-sm">Upcoming Examinations</h4>
                </div>
                <a href="{{ route('exam.list.show') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">Exam Routine &rarr;</a>
            </div>

            <div class="p-5">
                @if($upcomingExams->count())
                <div class="space-y-3">
                    @foreach($upcomingExams as $exam)
                    <div class="p-3.5 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 flex items-center justify-between gap-3">
                        <div>
                            <h5 class="font-bold text-slate-800 dark:text-slate-100 text-sm mb-0.5">{{ $exam->exam_name }}</h5>
                            <p class="text-xs text-slate-400 mb-0">
                                {{ $exam->course?->course_name ?? 'Subject' }} &bull; {{ \Carbon\Carbon::parse($exam->start_date)->format('h:i A') }}
                            </p>
                        </div>
                        <span class="text-xs font-bold text-rose-700 bg-rose-50 dark:bg-rose-950/50 px-3 py-1.5 rounded-xl border border-rose-200 dark:border-rose-900 flex-shrink-0">
                            {{ \Carbon\Carbon::parse($exam->start_date)->format('M d, Y') }}
                        </span>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="py-8 text-center text-slate-400">
                    <i class="bi bi-calendar2-check text-3xl text-slate-300 block mb-1"></i>
                    <p class="text-xs font-medium">No upcoming exams scheduled at this time.</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Online Exams & Quizzes --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/30">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm"><i class="bi bi-lightning-charge-fill"></i></span>
                    <h4 class="font-bold text-slate-800 dark:text-slate-100 text-sm">Online Tests & Practice Quizzes</h4>
                </div>
                <a href="{{ route('online-exams.student.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">View All &rarr;</a>
            </div>

            <div class="p-5">
                @if($practiceQuizzes->count() || $formalExams->count())
                <div class="space-y-3">
                    @foreach($practiceQuizzes->take(3) as $quiz)
                    <div class="p-3.5 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 flex items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <h5 class="font-bold text-slate-800 dark:text-slate-100 text-sm truncate mb-0.5">{{ $quiz->title }}</h5>
                            <p class="text-xs text-slate-400 mb-0">
                                {{ $quiz->course?->course_name ?? 'General' }} &bull; {{ $quiz->duration_minutes }} min
                            </p>
                        </div>
                        <a href="{{ route('online-exams.student.start', $quiz->id) }}" class="btn btn-sm text-xs font-bold px-3 py-1.5 rounded-xl shadow-xs text-white" style="background-color: #059669 !important; color: #fff !important;">
                            <i class="bi bi-play-fill"></i> Start Quiz
                        </a>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="py-8 text-center text-slate-400">
                    <i class="bi bi-laptop text-3xl text-slate-300 block mb-1"></i>
                    <p class="text-xs font-medium">No live quizzes or online exams active.</p>
                </div>
                @endif
            </div>
        </div>

    </div>

    {{-- 6. School Notices & Circulars --}}
    @if($notices->count())
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm"><i class="bi bi-megaphone-fill"></i></span>
                <h4 class="font-bold text-slate-800 dark:text-slate-100 text-sm">School Notices & Announcements</h4>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($notices->take(3) as $notice)
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                <p class="text-xs text-indigo-600 font-semibold mb-1 flex items-center gap-1">
                    <i class="bi bi-calendar-event"></i> {{ \Carbon\Carbon::parse($notice->created_at)->format('M d, Y') }}
                </p>
                <div class="text-xs text-slate-700 dark:text-slate-300 line-clamp-3 leading-relaxed">
                    {!! \Stevebauman\Purify\Facades\Purify::clean(strip_tags($notice->notice)) !!}
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
(function () {
    const rawTrend = @json($attendanceTrend ?? []);
    
    // Ensure we always have 6 distinct month categories
    const categories = rawTrend.map(item => item.label || 'Month');
    const presentData = rawTrend.map(item => parseInt(item.present) || 0);
    const absentData  = rawTrend.map(item => parseInt(item.absent) || 0);

    const chartEl = document.getElementById('chart-student-attendance');
    if (chartEl && typeof ApexCharts !== 'undefined') {
        const options = {
            chart: {
                type: 'bar',
                height: 200,
                stacked: true,
                toolbar: { show: false },
                fontFamily: 'inherit',
                animations: { enabled: true }
            },
            series: [
                { name: 'Present Days', data: presentData },
                { name: 'Absent Days',  data: absentData  }
            ],
            xaxis: {
                categories: categories,
                labels: {
                    style: { fontSize: '11px', fontWeight: 600, colors: '#64748b' }
                },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: {
                    style: { fontSize: '11px', colors: '#94a3b8' }
                },
                min: 0
            },
            colors: ['#4f46e5', '#f43f5e'],
            plotOptions: {
                bar: {
                    borderRadius: 5,
                    columnWidth: '40%',
                    borderRadiusApplication: 'end'
                }
            },
            dataLabels: { enabled: false },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
                fontSize: '12px',
                fontWeight: 600,
                markers: { radius: 12 }
            },
            grid: {
                borderColor: '#f1f5f9',
                strokeDashArray: 4,
                yaxis: { lines: { show: true } }
            },
            tooltip: {
                theme: 'dark',
                y: {
                    formatter: function (val) {
                        return val + " days";
                    }
                }
            }
        };

        const chart = new ApexCharts(chartEl, options);
        chart.render();
    }
})();
</script>
@endpush
