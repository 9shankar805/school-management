@extends('layouts.app')

@section('content')
<div class="flex-1 p-6 lg:p-8 overflow-auto">

    {{-- Header --}}
    <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Teacher Dashboard</h1>
            <p class="text-slate-400 text-sm mt-0.5">{{ now()->format('l, F j, Y') }} &middot; Welcome, <span class="font-medium text-slate-600">{{ auth()->user()->first_name }}</span></p>
        </div>
        <div class="flex flex-wrap gap-2">
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
            <a href="{{ route('leave.apply') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 text-sm font-medium rounded-lg hover:bg-slate-50 transition text-slate-700">
                <i class="bi bi-calendar-plus"></i> Apply Leave
            </a>
        </div>
    </div>

    {{-- KPI row 1: existing 4 + 2 new --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
        {{-- My Courses --}}
        <a href="{{ route('course.teacher.list.show', ['teacher_id' => auth()->id()]) }}"
           class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition block">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">My Courses</p>
                <span class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 text-sm"><i class="bi bi-journal-medical"></i></span>
            </div>
            <p class="text-3xl font-bold text-slate-800">{{ $myCourses->count() }}</p>
            <p class="mt-1 text-xs text-indigo-600">This session</p>
        </a>
        {{-- Upcoming Exams --}}
        <a href="{{ route('exam.list.show') }}"
           class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition block">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Upcoming Exams</p>
                <span class="w-8 h-8 rounded-lg bg-rose-50 flex items-center justify-center text-rose-600 text-sm"><i class="bi bi-file-earmark-text"></i></span>
            </div>
            <p class="text-3xl font-bold text-slate-800">{{ $upcomingExams->count() }}</p>
            <p class="mt-1 text-xs text-rose-600">Next 30 days</p>
        </a>
        {{-- Today's Classes --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Today's Classes</p>
                <span class="w-8 h-8 rounded-lg bg-violet-50 flex items-center justify-center text-violet-600 text-sm"><i class="bi bi-calendar4-week"></i></span>
            </div>
            <p class="text-3xl font-bold text-slate-800">{{ $todaySchedule->count() }}</p>
            <p class="mt-1 text-xs text-violet-600">{{ now()->format('l') }}</p>
        </div>
        {{-- Attendance Days --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Attendance Days</p>
                <span class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600 text-sm"><i class="bi bi-calendar-check"></i></span>
            </div>
            <p class="text-3xl font-bold text-slate-800">{{ $recentAttendance->count() }}</p>
            <p class="mt-1 text-xs text-emerald-600">Last 7 days</p>
        </div>
        {{-- Parent Messages --}}
        <a href="{{ route('teacher.messages') }}"
           class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition block {{ $unreadMessageCount > 0 ? 'ring-2 ring-blue-200' : '' }}">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Messages</p>
                <span class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 text-sm"><i class="bi bi-chat-dots-fill"></i></span>
            </div>
            <p class="text-3xl font-bold text-slate-800">{{ $unreadMessageCount }}</p>
            <p class="mt-1 text-xs text-blue-600">Unread from parents</p>
        </a>
        {{-- Leave Balance --}}
        <a href="{{ route('leave.index') }}"
           class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition block {{ $pendingLeaves > 0 ? 'ring-2 ring-amber-200' : '' }}">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Leave Balance</p>
                <span class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600 text-sm"><i class="bi bi-calendar-x-fill"></i></span>
            </div>
            <p class="text-3xl font-bold text-slate-800">{{ $leaveBalances->sum('remaining_days') }}</p>
            <p class="mt-1 text-xs {{ $pendingLeaves > 0 ? 'text-amber-600 font-medium' : 'text-slate-400' }}">
                Days remaining{{ $pendingLeaves > 0 ? ' · '.$pendingLeaves.' pending' : '' }}
            </p>
        </a>
    </div>{{-- /KPI grid --}}

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
