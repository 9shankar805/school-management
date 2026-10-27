@extends('layouts.app')
@section('title', 'Teacher Dashboard - NepalSchoolHub')

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
.iemis-badge-green { font-size: 10px !important; font-weight: 700 !important; color: #10b981 !important; }
.iemis-badge-amber { font-size: 10px !important; font-weight: 700 !important; color: #f59e0b !important; }
.iemis-badge-rose  { font-size: 10px !important; font-weight: 700 !important; color: #ef4444 !important; }
.iemis-badge-indigo{ font-size: 10px !important; font-weight: 700 !important; color: #6366f1 !important; }
.iemis-badge-blue  { font-size: 10px !important; font-weight: 700 !important; color: #3b82f6 !important; }
</style>
@endpush

@section('content')
<div class="flex-1 p-4 sm:p-6 lg:p-8 overflow-auto bg-slate-50/60 dark:bg-slate-950 min-h-screen space-y-6">

    {{-- Header --}}
    <div class="flex flex-wrap justify-between items-center gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                Good {{ now()->hour < 12 ? 'Morning' : (now()->hour < 18 ? 'Afternoon' : 'Evening') }}, {{ auth()->user()->first_name }}! 👋
            </h1>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                {{ now()->format('l, F j, Y') }} &bull; {{ now()->format('g:i A') }}
                &bull; <span class="text-indigo-600 dark:text-indigo-400 font-bold">आ.व. {{ \App\Services\NepaliDateService::currentFiscalYear() }}</span>
                &bull; <span class="font-semibold text-slate-600 dark:text-slate-300">Teacher Portal</span>
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            @can('take attendances')
            <a href="{{ route('attendance.create.show') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl shadow-sm transition active:scale-95">
                <i class="bi bi-check2-square text-xs"></i> Take Attendance
            </a>
            @endcan
            @can('save marks')
            <a href="{{ route('course.mark.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-semibold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition text-slate-700 dark:text-slate-200 active:scale-95">
                <i class="bi bi-pencil-square text-xs text-indigo-500"></i> Enter Marks
            </a>
            @endcan
            <a href="{{ route('leave.apply') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-semibold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition text-slate-700 dark:text-slate-200 active:scale-95">
                <i class="bi bi-calendar-plus text-xs text-amber-500"></i> Apply Leave
            </a>
        </div>
    </div>

    {{-- Class In-Charge Special Banner --}}
    @if(!empty($inChargeAssignment))
    <div style="background: linear-gradient(135deg, #d97706 0%, #ea580c 50%, #b45309 100%) !important; color: #ffffff !important; border-radius: 1.25rem; box-shadow: 0 10px 25px -5px rgba(217, 119, 6, 0.35);" class="p-5 md:p-6 text-white flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <span style="background: rgba(255, 255, 255, 0.2); border: 1.5px solid rgba(255, 255, 255, 0.4); box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);" class="w-13 h-13 rounded-2xl flex items-center justify-center text-2xl font-black text-amber-200">
                <i class="bi bi-star-fill text-amber-200"></i>
            </span>
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span style="background: rgba(255, 255, 255, 0.25); color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.35);" class="text-[11px] font-black uppercase tracking-wider px-3 py-0.5 rounded-full inline-flex items-center gap-1">
                        <i class="bi bi-shield-check"></i> Class In-Charge (कक्षा शिक्षक)
                    </span>
                    <span style="background: rgba(0, 0, 0, 0.2); color: #fef3c7 !important;" class="text-[10px] font-bold px-2 py-0.5 rounded-md">
                        Official Attendance Gate
                    </span>
                </div>
                <h2 style="color: #ffffff !important;" class="text-xl font-black tracking-tight">
                    {{ $inChargeAssignment->schoolClass?->class_name }} — Section {{ preg_replace('/^Section\s+/i', '', $inChargeAssignment->section?->section_name ?? 'A') }}
                </h2>
                <p style="color: #fef3c7 !important;" class="text-xs font-medium mt-0.5">You are designated as the Class Teacher for this section with full authority for Attendance, Daily Diary & Leave Approvals.</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('class-incharge.dashboard') }}" style="background: #ffffff !important; color: #b45309 !important; font-weight: 800;" class="px-4 py-2.5 rounded-xl text-xs font-extrabold transition shadow-md hover:bg-amber-50 flex items-center gap-1.5">
                <i class="bi bi-speedometer2 text-amber-600"></i> Open In-Charge Center
            </a>
            <a href="{{ route('attendance.create.show', ['class_id' => $inChargeAssignment->class_id, 'section_id' => $inChargeAssignment->section_id]) }}" style="background: rgba(255, 255, 255, 0.2) !important; color: #ffffff !important; border: 1.5px solid rgba(255, 255, 255, 0.35);" class="px-3.5 py-2.5 hover:bg-white/30 rounded-xl text-xs font-bold transition flex items-center gap-1">
                <i class="bi bi-check2-square me-1"></i> Take Attendance
            </a>
            <a href="{{ route('class-incharge.gradebook') }}" style="background: rgba(255, 255, 255, 0.2) !important; color: #ffffff !important; border: 1.5px solid rgba(255, 255, 255, 0.35);" class="px-3.5 py-2.5 hover:bg-white/30 rounded-xl text-xs font-bold transition flex items-center gap-1">
                <i class="bi bi-card-checklist me-1"></i> Marksheet
            </a>
        </div>
    </div>
    @endif

    {{-- CEHRD IEMIS 3D Stat Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5 sm:gap-4">
        {{-- Card 1: My Courses --}}
        <a href="{{ route('course.teacher.list.show', ['teacher_id' => auth()->id()]) }}" class="iemis-stat-card">
            <div class="iemis-icon-wrapper">📚</div>
            <div class="iemis-card-details">
                <div class="iemis-title">Courses</div>
                <div class="iemis-num-group">
                    <span class="iemis-value">{{ $myCourses->count() }}</span>
                    <span class="iemis-badge-indigo">Assigned</span>
                </div>
            </div>
        </a>

        {{-- Card 2: Upcoming Exams --}}
        <a href="{{ route('exam.list.show') }}" class="iemis-stat-card">
            <div class="iemis-icon-wrapper">📋</div>
            <div class="iemis-card-details">
                <div class="iemis-title">Exams</div>
                <div class="iemis-num-group">
                    <span class="iemis-value">{{ $upcomingExams->count() }}</span>
                    <span class="iemis-badge-rose">30 Days</span>
                </div>
            </div>
        </a>

        {{-- Card 3: Today's Classes --}}
        <div class="iemis-stat-card">
            <div class="iemis-icon-wrapper">⏰</div>
            <div class="iemis-card-details">
                <div class="iemis-title">Classes</div>
                <div class="iemis-num-group">
                    <span class="iemis-value">{{ $todaySchedule->count() }}</span>
                    <span class="iemis-badge-green">Today</span>
                </div>
            </div>
        </div>

        {{-- Card 4: Attendance Days --}}
        <div class="iemis-stat-card">
            <div class="iemis-icon-wrapper">✅</div>
            <div class="iemis-card-details">
                <div class="iemis-title">Attendance</div>
                <div class="iemis-num-group">
                    <span class="iemis-value">{{ $recentAttendance->count() }}</span>
                    <span class="iemis-badge-green">7 Days</span>
                </div>
            </div>
        </div>

        {{-- Card 5: Parent Messages --}}
        <a href="{{ route('teacher.messages') }}" class="iemis-stat-card">
            <div class="iemis-icon-wrapper">💬</div>
            <div class="iemis-card-details">
                <div class="iemis-title">Messages</div>
                <div class="iemis-num-group">
                    <span class="iemis-value">{{ $unreadMessageCount }}</span>
                    <span class="iemis-badge-blue">Unread</span>
                </div>
            </div>
        </a>

        {{-- Card 6: Leave Balance --}}
        <a href="{{ route('leave.index') }}" class="iemis-stat-card">
            <div class="iemis-icon-wrapper">🏖️</div>
            <div class="iemis-card-details">
                <div class="iemis-title">Leaves</div>
                <div class="iemis-num-group">
                    <span class="iemis-value">{{ $leaveBalances->sum('remaining_days') }}</span>
                    <span class="iemis-badge-amber">Days</span>
                </div>
            </div>
        </a>
    </div>

    {{-- Today's Schedule + My Courses (side by side) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- Today's Schedule --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 flex justify-between items-center">
                <p class="text-sm font-semibold text-slate-700"><i class="bi bi-calendar4-week me-1 text-violet-500"></i>Today's Schedule</p>
                <a href="{{ route('routine.teacher-timetable') }}?teacher_id={{ auth()->id() }}" class="text-xs text-indigo-600 hover:underline">Full timetable</a>
            </div>
            @if($todaySchedule->count())
            <div class="divide-y divide-slate-50">
                @foreach($todaySchedule as $slot)
                <div class="px-5 py-3 flex items-center gap-4">
                    <div class="w-14 flex-shrink-0 text-center">
                        <p class="text-xs font-bold text-slate-700">{{ \Carbon\Carbon::parse($slot->start)->format('H:i') }}</p>
                        <p class="text-[10px] text-slate-400">{{ \Carbon\Carbon::parse($slot->end)->format('H:i') }}</p>
                    </div>
                    <div class="w-1 self-stretch rounded-full" style="background:{{ $slot->color ?? '#6366f1' }}"></div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-700 truncate">{{ $slot->course?->course_name ?? '—' }}</p>
                        <p class="text-xs text-slate-400">{{ $slot->schoolClass?->class_name ?? '' }} {{ $slot->section?->section_name ?? '' }}{{ $slot->room ? ' · '.$slot->room : '' }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="px-5 py-10 text-center">
                <i class="bi bi-calendar-check text-3xl text-slate-200 mb-2 block"></i>
                <p class="text-sm text-slate-400">No classes scheduled for today.</p>
            </div>
            @endif
        </div>

        {{-- My Courses --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
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
                        <p class="text-sm font-medium text-slate-700">{{ $ac->course->name ?? $ac->course->course_name ?? 'Course' }}</p>
                        <p class="text-xs text-slate-400">{{ $ac->section->name ?? $ac->section->section_name ?? '—' }}
                            @if($summary)
                            &middot; <span class="text-emerald-600">{{ $summary->present }} present</span> / <span class="text-rose-500">{{ $summary->absent }} absent</span>
                            @endif
                        </p>
                    </div>
                    <div class="flex gap-2">
                        @can('take attendances')
                        <a href="{{ route('attendance.create.show') }}" class="text-xs px-2.5 py-1.5 bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 transition">Attendance</a>
                        @endcan
                        @can('save marks')
                        <a href="{{ route('course.mark.create') }}" class="text-xs px-2.5 py-1.5 bg-emerald-50 text-emerald-700 rounded-lg hover:bg-emerald-100 transition">Marks</a>
                        @endcan
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-slate-400 text-center py-10">No courses assigned this session.</p>
            @endif
        </div>
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
                        <p class="text-xs text-slate-400">{{ $exam->course?->name ?? $exam->course?->course_name ?? '—' }}</p>
                    </div>
                    <span class="text-xs font-medium text-rose-700 bg-rose-50 px-2 py-1 rounded-lg flex-shrink-0">
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

    {{-- Leave + Payslip (side by side) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- Leave Status --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 flex justify-between items-center">
                <p class="text-sm font-semibold text-slate-700"><i class="bi bi-calendar-x-fill me-1 text-amber-500"></i>Leave</p>
                <div class="flex gap-2">
                    <a href="{{ route('leave.apply') }}" class="text-xs text-indigo-600 hover:underline">Apply</a>
                    <span class="text-slate-300">|</span>
                    <a href="{{ route('leave.index') }}" class="text-xs text-indigo-600 hover:underline">History</a>
                </div>
            </div>
            {{-- Balance pills --}}
            @if($leaveBalances->count())
            <div class="px-5 py-3 flex flex-wrap gap-2 border-b border-slate-50">
                @foreach($leaveBalances as $bal)
                <div class="flex-1 min-w-[120px] bg-slate-50 rounded-xl p-3 text-center">
                    <p class="text-xs text-slate-400 mb-1 truncate">{{ $bal->leaveType?->name ?? 'Leave' }}</p>
                    <p class="text-lg font-bold text-slate-800">{{ $bal->remaining_days }}</p>
                    <p class="text-[10px] text-slate-400">of {{ $bal->total_days }} days left</p>
                </div>
                @endforeach
            </div>
            @endif
            {{-- Recent applications --}}
            @if($myLeaveApplications->count())
            <div class="divide-y divide-slate-50">
                @foreach($myLeaveApplications as $app)
                <div class="px-5 py-2.5 flex justify-between items-center">
                    <div>
                        <p class="text-xs font-medium text-slate-700">{{ $app->leaveType?->name ?? 'Leave' }}
                            <span class="text-slate-400 font-normal">· {{ \Carbon\Carbon::parse($app->from_date)->format('M d') }}–{{ \Carbon\Carbon::parse($app->to_date)->format('M d') }}</span>
                        </p>
                        <p class="text-[10px] text-slate-400">{{ $app->total_days }} day(s)</p>
                    </div>
                    @php $badge = match($app->status) { 'approved'=>'bg-emerald-100 text-emerald-700', 'rejected'=>'bg-rose-100 text-rose-700', default=>'bg-amber-100 text-amber-700' }; @endphp
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $badge }}">{{ ucfirst($app->status) }}</span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-xs text-slate-400 text-center py-5">No leave applications yet.</p>
            @endif
        </div>

        {{-- Latest Payslip --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 flex justify-between items-center">
                <p class="text-sm font-semibold text-slate-700"><i class="bi bi-cash-stack me-1 text-emerald-500"></i>Latest Payslip</p>
                <a href="{{ route('teacher.payroll.index') }}" class="text-xs text-indigo-600 hover:underline">All payslips</a>
            </div>
            @if($latestPayslip)
            <div class="p-5">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-lg font-bold text-slate-800">{{ $latestPayslip->monthName }} {{ $latestPayslip->year }}</p>
                        @php $slipBadge = match($latestPayslip->status) { 'paid'=>'bg-emerald-100 text-emerald-700', 'pending'=>'bg-amber-100 text-amber-700', default=>'bg-slate-100 text-slate-600' }; @endphp
                        <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full {{ $slipBadge }}">{{ ucfirst($latestPayslip->status) }}</span>
                    </div>
                    <a href="{{ route('teacher.payroll.slip', $latestPayslip->id) }}"
                       class="inline-flex items-center gap-1 text-xs px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 transition">
                        <i class="bi bi-download"></i> Download
                    </a>
                </div>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div class="bg-slate-50 rounded-xl p-3">
                        <p class="text-xs text-slate-400 mb-0.5">Basic Salary</p>
                        <p class="font-semibold text-slate-700">Rs {{ number_format($latestPayslip->basic_salary) }}</p>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-3">
                        <p class="text-xs text-slate-400 mb-0.5">Allowances</p>
                        <p class="font-semibold text-slate-700">Rs {{ number_format($latestPayslip->allowances ?? 0) }}</p>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-3">
                        <p class="text-xs text-slate-400 mb-0.5">Deductions</p>
                        <p class="font-semibold text-rose-600">- Rs {{ number_format(($latestPayslip->tax_deduction ?? 0) + ($latestPayslip->other_deductions ?? 0)) }}</p>
                    </div>
                    <div class="bg-emerald-50 rounded-xl p-3">
                        <p class="text-xs text-emerald-600 mb-0.5">Net Salary</p>
                        <p class="font-bold text-emerald-700 text-base">Rs {{ number_format($latestPayslip->net_salary) }}</p>
                    </div>
                </div>
            </div>
            @else
            <div class="px-5 py-10 text-center">
                <i class="bi bi-receipt text-3xl text-slate-200 mb-2 block"></i>
                <p class="text-sm text-slate-400">No payslips generated yet.</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Notice Board --}}
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

</div>{{-- /flex-1 --}}
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
