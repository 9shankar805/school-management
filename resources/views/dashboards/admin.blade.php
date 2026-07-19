@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-50">
    {{-- Sidebar --}}
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">
        @include('layouts.left-menu')
    </div>

    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        {{-- ── Page header ── --}}
        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Dashboard</h1>
                <p class="text-slate-400 text-sm mt-0.5">
                    {{ now()->format('l, F j, Y') }} &middot; Welcome back, <span class="font-medium text-slate-600">{{ auth()->user()->first_name }}</span>
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                @can('create students')
                <a href="{{ route('student.create.show') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="bi bi-person-plus"></i> New Student
                </a>
                @endcan
                @can('create notices')
                <a href="{{ route('notice.create') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 text-sm font-medium rounded-lg hover:bg-slate-50 transition-colors text-slate-700">
                    <i class="bi bi-megaphone"></i> Notice
                </a>
                @endcan
                @can('create exams')
                <a href="{{ route('exam.create.show') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 text-sm font-medium rounded-lg hover:bg-slate-50 transition-colors text-slate-700">
                    <i class="bi bi-file-plus"></i> New Exam
                </a>
                @endcan
            </div>
        </div>

        {{-- ── KPI Cards ── --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Students</p>
                    <span class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 text-sm"><i class="bi bi-people-fill"></i></span>
                </div>
                <p class="text-3xl font-bold text-slate-800">{{ number_format($studentCount) }}</p>
                <p class="mt-1 text-xs text-emerald-600"><i class="bi bi-arrow-up-short"></i> Enrolled this session</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Teachers</p>
                    <span class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 text-sm"><i class="bi bi-person-badge-fill"></i></span>
                </div>
                <p class="text-3xl font-bold text-slate-800">{{ number_format($teacherCount) }}</p>
                <p class="mt-1 text-xs text-blue-600"><i class="bi bi-check-circle"></i> Active faculty</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Staff</p>
                    <span class="w-8 h-8 rounded-lg bg-violet-50 flex items-center justify-center text-violet-600 text-sm"><i class="bi bi-building"></i></span>
                </div>
                <p class="text-3xl font-bold text-slate-800">{{ number_format($staffCount) }}</p>
                <p class="mt-1 text-xs text-violet-600"><i class="bi bi-diagram-3"></i> All departments</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Classes</p>
                    <span class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600 text-sm"><i class="bi bi-mortarboard-fill"></i></span>
                </div>
                <p class="text-3xl font-bold text-slate-800">{{ number_format($classCount) }}</p>
                <p class="mt-1 text-xs text-amber-600"><i class="bi bi-calendar3"></i> This session</p>
            </div>
        </div>

        {{-- ── Attendance + Revenue + Gender ── --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            {{-- Today's Attendance --}}
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">Today's Attendance</p>
                <div class="flex items-end gap-3 mb-3">
                    <span class="text-4xl font-bold {{ $attendancePct >= 75 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $attendancePct }}%</span>
                    <span class="text-xs text-slate-400 pb-1">{{ $todayPresent }} present · {{ $todayAbsent }} absent</span>
                </div>
                <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all {{ $attendancePct >= 75 ? 'bg-emerald-500' : 'bg-rose-500' }}" style="width:{{ $attendancePct }}%"></div>
                </div>
                <p class="mt-2 text-xs {{ $attendancePct < 75 ? 'text-rose-500' : 'text-slate-400' }}">
                    @if($attendancePct < 75) <i class="bi bi-exclamation-triangle-fill"></i> Below 75% threshold @else <i class="bi bi-check-circle"></i> Healthy attendance @endif
                </p>
            </div>

            {{-- Monthly Revenue --}}
            @can('view financial reports')
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">Revenue This Month</p>
                <p class="text-4xl font-bold text-slate-800">${{ number_format($monthRevenue) }}</p>
                <p class="mt-2 text-xs {{ $pendingInvoices > 0 ? 'text-rose-500' : 'text-emerald-600' }}">
                    <i class="bi bi-receipt"></i> {{ $pendingInvoices }} invoice{{ $pendingInvoices !== 1 ? 's' : '' }} pending
                </p>
                <a href="{{ route('payments.index') }}" class="mt-3 inline-block text-xs text-indigo-600 hover:underline">View all payments →</a>
            </div>
            @endcan

            {{-- Gender Distribution --}}
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">Gender Distribution</p>
                <div class="flex gap-5 mb-3">
                    <div>
                        <p class="text-2xl font-bold text-blue-600">{{ $malePct }}%</p>
                        <p class="text-xs text-slate-400"><i class="bi bi-gender-male"></i> Male ({{ number_format($maleStudents) }})</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-pink-500">{{ 100 - $malePct }}%</p>
                        <p class="text-xs text-slate-400"><i class="bi bi-gender-female"></i> Female ({{ number_format($female) }})</p>
                    </div>
                </div>
                <div class="h-2 bg-slate-100 rounded-full overflow-hidden flex">
                    <div class="h-full bg-blue-500" style="width:{{ $malePct }}%"></div>
                    <div class="h-full bg-pink-500" style="width:{{ 100 - $malePct }}%"></div>
                </div>
            </div>
        </div>

        {{-- ── Charts Row ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            {{-- Attendance Trend (ApexCharts) --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-graph-up-arrow me-1 text-indigo-500"></i>7-Day Attendance Trend</p>
                <div id="chart-attendance" style="min-height:180px"></div>
            </div>

            {{-- Monthly Revenue Chart --}}
            @can('view financial reports')
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-bar-chart-fill me-1 text-emerald-500"></i>Monthly Revenue</p>
                <div id="chart-revenue" style="min-height:180px"></div>
            </div>
            @endcan
        </div>

        {{-- ── Upcoming Exams + Events ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            {{-- Upcoming Exams --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 flex justify-between items-center">
                    <p class="text-sm font-semibold text-slate-700"><i class="bi bi-file-earmark-text me-1 text-rose-500"></i>Upcoming Exams</p>
                    @can('view exams')
                    <a href="{{ route('exam.list.show') }}" class="text-xs text-indigo-600 hover:underline">View all</a>
                    @endcan
                </div>
                @if($upcomingExams->count())
                <div class="divide-y divide-slate-50">
                    @foreach($upcomingExams as $exam)
                    <div class="px-5 py-3 flex justify-between items-center">
                        <div>
                            <p class="text-sm font-medium text-slate-700">{{ $exam->exam_name }}</p>
                            <p class="text-xs text-slate-400">{{ $exam->course?->name ?? '—' }}</p>
                        </div>
                        <span class="text-xs font-medium text-indigo-700 bg-indigo-50 px-2 py-1 rounded-lg">
                            {{ \Carbon\Carbon::parse($exam->start_date)->format('M d') }}
                        </span>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-400 text-center py-8">No upcoming exams.</p>
                @endif
            </div>

            {{-- Upcoming Events --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 flex justify-between items-center">
                    <p class="text-sm font-semibold text-slate-700"><i class="bi bi-calendar-event me-1 text-amber-500"></i>Upcoming Events</p>
                    @can('view events')
                    <a href="{{ route('events.show') }}" class="text-xs text-indigo-600 hover:underline">Calendar</a>
                    @endcan
                </div>
                @if($upcomingEvents->count())
                <div class="divide-y divide-slate-50">
                    @foreach($upcomingEvents as $event)
                    <div class="px-5 py-3 flex justify-between items-center">
                        <p class="text-sm text-slate-700">{{ $event->title }}</p>
                        <span class="text-xs text-amber-700 bg-amber-50 px-2 py-1 rounded-lg">
                            {{ \Carbon\Carbon::parse($event->start)->format('M d') }}
                        </span>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-400 text-center py-8">No upcoming events.</p>
                @endif
            </div>
        </div>

        {{-- ── Recent Admissions + Recent Payments ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            {{-- Recent Admissions --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 flex justify-between items-center">
                    <p class="text-sm font-semibold text-slate-700"><i class="bi bi-person-check me-1 text-emerald-500"></i>Recent Admissions</p>
                    @can('view students')
                    <a href="{{ route('student.list.show') }}" class="text-xs text-indigo-600 hover:underline">View all</a>
                    @endcan
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
                        @can('view students')
                        <a href="{{ route('student.profile.show', $s->id) }}" class="text-xs text-indigo-600 hover:underline flex-shrink-0">Profile</a>
                        @endcan
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-400 text-center py-8">No recent admissions.</p>
                @endif
            </div>

            {{-- Recent Payments --}}
            @can('view payments')
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 flex justify-between items-center">
                    <p class="text-sm font-semibold text-slate-700"><i class="bi bi-credit-card me-1 text-indigo-500"></i>Recent Payments</p>
                    <a href="{{ route('payments.index') }}" class="text-xs text-indigo-600 hover:underline">View all</a>
                </div>
                @if($recentPayments->count())
                <div class="divide-y divide-slate-50">
                    @foreach($recentPayments as $pmt)
                    @php $student = $pmt->invoice?->student; @endphp
                    <div class="px-5 py-3 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2 min-w-0">
                            @if($student)
                            <img src="{{ $student->avatar }}" class="w-7 h-7 rounded-full object-cover flex-shrink-0" alt="">
                            @endif
                            <p class="text-sm text-slate-700 truncate">{{ $student?->full_name ?? '—' }}</p>
                        </div>
                        <div class="flex-shrink-0 text-right">
                            <p class="text-sm font-semibold text-emerald-600">${{ number_format($pmt->amount_paid) }}</p>
                            <p class="text-xs text-slate-400">{{ $pmt->created_at->format('M d') }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-400 text-center py-8">No payments recorded.</p>
                @endif
            </div>
            @endcan
        </div>

        {{-- ── Birthday Widget + Activity Log ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            {{-- Birthdays (next 7 days) --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100">
                    <p class="text-sm font-semibold text-slate-700"><i class="bi bi-cake me-1 text-pink-500"></i>Upcoming Birthdays</p>
                </div>
                @if($birthdayUsers->count())
                <div class="divide-y divide-slate-50">
                    @foreach($birthdayUsers as $u)
                    <div class="px-5 py-3 flex items-center gap-3">
                        <img src="{{ $u->avatar }}" class="w-8 h-8 rounded-full object-cover flex-shrink-0" alt="">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-700 truncate">{{ $u->full_name }}</p>
                            <p class="text-xs text-slate-400 capitalize">{{ $u->primary_role }}</p>
                        </div>
                        <span class="text-xs font-medium text-pink-700 bg-pink-50 px-2 py-1 rounded-lg flex-shrink-0">
                            {{ \Carbon\Carbon::parse($u->birthday)->format('M d') }}
                        </span>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-400 text-center py-8">No birthdays in the next 7 days.</p>
                @endif
            </div>

            {{-- Recent Activity Log --}}
            @can('view audit logs')
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100">
                    <p class="text-sm font-semibold text-slate-700"><i class="bi bi-clock-history me-1 text-slate-400"></i>Recent Activity</p>
                </div>
                @if($activityLog->count())
                <div class="divide-y divide-slate-50">
                    @foreach($activityLog as $log)
                    <div class="px-5 py-3 flex items-start gap-3">
                        <span class="mt-0.5 w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center flex-shrink-0 text-xs text-slate-500">
                            <i class="bi bi-activity"></i>
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-slate-600">
                                <span class="font-medium text-slate-700">{{ $log->user?->full_name ?? 'System' }}</span>
                                {{ $log->event }}
                                @if($log->auditable_type)
                                <span class="text-slate-400">{{ class_basename($log->auditable_type) }}</span>
                                @endif
                            </p>
                            <p class="text-[11px] text-slate-400 mt-0.5">{{ $log->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-400 text-center py-8">No activity recorded.</p>
                @endif
            </div>
            @endcan
        </div>

        {{-- ── Notices Feed ── --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mb-6">
            <div class="px-5 py-3 border-b border-slate-100 flex justify-between items-center">
                <p class="text-sm font-semibold text-slate-700"><i class="bi bi-megaphone me-1 text-amber-500"></i>Notice Board</p>
                @can('create notices')
                <a href="{{ route('notice.create') }}" class="text-xs text-indigo-600 hover:underline">+ New notice</a>
                @endcan
            </div>
            @if($notices->count())
            <div class="divide-y divide-slate-50">
                @foreach($notices->take(5) as $notice)
                <div class="px-5 py-3">
                    <p class="text-sm text-slate-600 line-clamp-2">
                        {!! \Stevebauman\Purify\Facades\Purify::clean(strip_tags($notice->notice)) !!}
                    </p>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-slate-400 text-center py-6">No notices.</p>
            @endif
        </div>

        {{-- ── Quick Actions ── --}}
        @canany(['manage roles','view academic settings','promote students','view audit logs'])
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-6">
            <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-lightning-fill me-1 text-amber-500"></i>Quick Actions</p>
            <div class="flex flex-wrap gap-2">
                @can('manage roles')
                <a href="{{ route('roles.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-indigo-50 hover:text-indigo-700 text-slate-700 rounded-lg text-sm font-medium transition">
                    <i class="bi bi-shield-check"></i> Manage Roles
                </a>
                <a href="{{ route('roles.matrix') }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-indigo-50 hover:text-indigo-700 text-slate-700 rounded-lg text-sm font-medium transition">
                    <i class="bi bi-grid-3x3"></i> Permission Matrix
                </a>
                @endcan
                @can('view academic settings')
                <a href="{{ url('academics/settings') }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-amber-50 hover:text-amber-700 text-slate-700 rounded-lg text-sm font-medium transition">
                    <i class="bi bi-tools"></i> Academic Settings
                </a>
                @endcan
                @can('promote students')
                <a href="{{ url('promotions/index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 text-slate-700 rounded-lg text-sm font-medium transition">
                    <i class="bi bi-arrow-up-circle"></i> Promotions
                </a>
                @endcan
                @can('create teachers')
                <a href="{{ route('teacher.create.show') }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-blue-50 hover:text-blue-700 text-slate-700 rounded-lg text-sm font-medium transition">
                    <i class="bi bi-person-plus"></i> Add Teacher
                </a>
                @endcan
                @can('create invoices')
                <a href="{{ route('payments.create') }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-rose-50 hover:text-rose-700 text-slate-700 rounded-lg text-sm font-medium transition">
                    <i class="bi bi-receipt"></i> New Invoice
                </a>
                @endcan
            </div>
        </div>
        @endcanany

        {{-- ── Today's Class Schedule ── --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mb-6" data-widget="schedule">
            <div class="px-5 py-3 border-b border-slate-100 flex justify-between items-center">
                <p class="text-sm font-semibold text-slate-700"><i class="bi bi-calendar4-range me-1 text-indigo-500"></i>Today's Class Schedule
                    <span class="ml-2 text-xs font-normal text-slate-400">{{ now()->format('l') }}</span>
                </p>
                @can('view routines')
                <a href="{{ route('section.routine.show') }}" class="text-xs text-indigo-600 hover:underline">Full timetable</a>
                @endcan
            </div>
            @if($todaySchedule->count())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-slate-400 bg-slate-50">
                            <th class="px-5 py-2.5 font-medium">Time</th>
                            <th class="px-5 py-2.5 font-medium">Course</th>
                            <th class="px-5 py-2.5 font-medium">Class</th>
                            <th class="px-5 py-2.5 font-medium">Section</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($todaySchedule as $slot)
                        @php
                            $slotStart = \Carbon\Carbon::today()->setTimeFromTimeString($slot->start);
                            $slotEnd   = \Carbon\Carbon::today()->setTimeFromTimeString($slot->end);
                            $isNow     = now()->between($slotStart, $slotEnd);
                        @endphp
                        <tr class="hover:bg-slate-50 {{ $isNow ? 'bg-indigo-50' : '' }}">
                            <td class="px-5 py-3 font-mono text-xs text-slate-600 whitespace-nowrap">
                                {{ $slot->start }} – {{ $slot->end }}
                                @if($isNow)<span class="ml-2 inline-flex items-center gap-1 text-[10px] bg-indigo-600 text-white px-1.5 py-0.5 rounded-full"><span class="w-1.5 h-1.5 rounded-full bg-white inline-block"></span>Now</span>@endif
                            </td>
                            <td class="px-5 py-3 font-medium text-slate-700">{{ $slot->course?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $slot->schoolClass?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-slate-400">{{ $slot->section?->name ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-sm text-slate-400 text-center py-8">No classes scheduled for today.</p>
            @endif
        </div>

        {{-- ── Top Performers + Teacher Performance ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            {{-- Top Performers --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden" data-widget="top-performers">
                <div class="px-5 py-3 border-b border-slate-100">
                    <p class="text-sm font-semibold text-slate-700"><i class="bi bi-trophy me-1 text-amber-500"></i>Top Performers</p>
                </div>
                @if($topPerformers->count())
                <div class="divide-y divide-slate-50">
                    @foreach($topPerformers as $i => $tp)
                    @php $student = $tp->student; @endphp
                    @if($student)
                    <div class="px-5 py-3 flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-bold
                            {{ $i === 0 ? 'bg-amber-100 text-amber-700' : ($i === 1 ? 'bg-slate-200 text-slate-600' : ($i === 2 ? 'bg-orange-100 text-orange-700' : 'bg-slate-100 text-slate-500')) }}">
                            {{ $i + 1 }}
                        </span>
                        <img src="{{ $student->avatar }}" class="w-8 h-8 rounded-full object-cover flex-shrink-0" alt="">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-700 truncate">{{ $student->full_name }}</p>
                            <p class="text-xs text-slate-400">{{ $tp->exams_count }} exam{{ $tp->exams_count !== 1 ? 's' : '' }} taken</p>
                        </div>
                        <span class="text-sm font-bold {{ $tp->avg_marks >= 80 ? 'text-emerald-600' : ($tp->avg_marks >= 60 ? 'text-amber-600' : 'text-rose-600') }} flex-shrink-0">
                            {{ $tp->avg_marks }}%
                        </span>
                    </div>
                    @endif
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-400 text-center py-8">No marks recorded this session.</p>
                @endif
            </div>

            {{-- Teacher Performance --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden" data-widget="teacher-performance">
                <div class="px-5 py-3 border-b border-slate-100">
                    <p class="text-sm font-semibold text-slate-700"><i class="bi bi-person-badge me-1 text-blue-500"></i>Teacher Activity</p>
                </div>
                @if($teacherPerformance->count())
                <div class="divide-y divide-slate-50">
                    @foreach($teacherPerformance as $tp)
                    @if($tp->teacher)
                    <div class="px-5 py-3 flex items-center gap-3">
                        <img src="{{ $tp->teacher->avatar }}" class="w-8 h-8 rounded-full object-cover flex-shrink-0" alt="">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-700 truncate">{{ $tp->teacher->full_name }}</p>
                        </div>
                        <div class="flex gap-3 flex-shrink-0 text-right">
                            <div class="text-center">
                                <p class="text-sm font-bold text-indigo-600">{{ $tp->marks_entered }}</p>
                                <p class="text-[10px] text-slate-400">Marks</p>
                            </div>
                            <div class="text-center">
                                <p class="text-sm font-bold text-emerald-600">{{ $tp->attendance_days }}</p>
                                <p class="text-[10px] text-slate-400">Att. days</p>
                            </div>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-400 text-center py-8">No teacher activity recorded yet.</p>
                @endif
            </div>
        </div>

        {{-- ── Student Performance Chart + Attendance Heatmap ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            {{-- Student Performance by Course --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5" data-widget="performance-chart">
                <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-bar-chart-steps me-1 text-violet-500"></i>Avg Marks by Course</p>
                <div id="chart-course-performance" style="min-height:200px"></div>
            </div>

            {{-- Attendance Heatmap --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5" data-widget="heatmap">
                <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-calendar3 me-1 text-indigo-500"></i>Attendance Heatmap (70 days)</p>
                <div id="chart-heatmap" style="min-height:200px"></div>
            </div>
        </div>

        {{-- ── Widget Toggle Panel (Personalization) ── --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-6">
            <p class="text-sm font-semibold text-slate-700 mb-3"><i class="bi bi-toggles me-1 text-slate-400"></i>Customize Dashboard</p>
            <div class="flex flex-wrap gap-2" id="widget-toggles">
                @foreach([
                    ['schedule',           'Class Schedule'],
                    ['top-performers',     'Top Performers'],
                    ['teacher-performance','Teacher Activity'],
                    ['performance-chart',  'Course Performance'],
                    ['heatmap',            'Attendance Heatmap'],
                ] as [$key, $label])
                <button data-toggle="{{ $key }}"
                    class="widget-toggle-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium border transition
                           border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100">
                    <i class="bi bi-eye-fill"></i> {{ $label }}
                </button>
                @endforeach
            </div>
            <p class="text-[11px] text-slate-400 mt-2">Preferences saved in your browser.</p>
        </div>

    </div>{{-- /main content --}}
</div>{{-- /flex wrapper --}}
@endsection

@push('scripts')
<script>
(function () {
    // ── Attendance Trend (area chart) ──────────────────────────────────────
    var attendanceDays    = @json($attendanceTrend->pluck('day')->map(fn($d) => \Carbon\Carbon::parse($d)->format('D M d')));
    var attendancePresent = @json($attendanceTrend->pluck('present')->map(fn($v) => (int)$v));
    var attendanceAbsent  = @json($attendanceTrend->pluck('absent')->map(fn($v) => (int)$v));

    if (document.getElementById('chart-attendance')) {
        new ApexCharts(document.getElementById('chart-attendance'), {
            chart: { type: 'area', height: 180, toolbar: { show: false }, sparkline: { enabled: false } },
            series: [
                { name: 'Present', data: attendancePresent },
                { name: 'Absent',  data: attendanceAbsent  },
            ],
            xaxis: { categories: attendanceDays, labels: { style: { fontSize: '10px' } } },
            yaxis: { labels: { style: { fontSize: '10px' } } },
            colors: ['#22c55e', '#f43f5e'],
            fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.05 } },
            stroke: { curve: 'smooth', width: 2 },
            dataLabels: { enabled: false },
            legend: { position: 'top', fontSize: '12px' },
            tooltip: { x: { format: 'dd MMM' } },
            grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
        }).render();
    }

    // ── Monthly Revenue (bar chart) ────────────────────────────────────────
    var revenueMonths = @json($monthlyRevenue->pluck('month')->map(fn($m) => \Carbon\Carbon::create()->month((int)$m)->format('M')));
    var revenueTotals = @json($monthlyRevenue->pluck('total')->map(fn($v) => (float)$v));

    if (document.getElementById('chart-revenue')) {
        new ApexCharts(document.getElementById('chart-revenue'), {
            chart: { type: 'bar', height: 180, toolbar: { show: false } },
            series: [{ name: 'Revenue ($)', data: revenueTotals }],
            xaxis: { categories: revenueMonths, labels: { style: { fontSize: '10px' } } },
            yaxis: { labels: { style: { fontSize: '10px' }, formatter: v => '$' + (v >= 1000 ? (v/1000).toFixed(1) + 'k' : v) } },
            colors: ['#22c55e'],
            dataLabels: { enabled: false },
            plotOptions: { bar: { borderRadius: 4, columnWidth: '55%' } },
            grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
            tooltip: { y: { formatter: v => '$' + Number(v).toLocaleString() } },
        }).render();
    }

    // ── Course Performance (horizontal bar) ───────────────────────────────
    var courseNames = @json($coursePerformance->map(fn($c) => $c->course?->name ?? 'Unknown'));
    var courseAvgs  = @json($coursePerformance->pluck('avg_marks')->map(fn($v) => (float)$v));

    if (document.getElementById('chart-course-performance')) {
        new ApexCharts(document.getElementById('chart-course-performance'), {
            chart: { type: 'bar', height: 200, toolbar: { show: false } },
            plotOptions: { bar: { horizontal: true, borderRadius: 4 } },
            series: [{ name: 'Avg Marks (%)', data: courseAvgs }],
            xaxis: { categories: courseNames, labels: { style: { fontSize: '10px' } }, max: 100 },
            yaxis: { labels: { style: { fontSize: '10px' } } },
            colors: ['#8b5cf6'],
            dataLabels: { enabled: true, formatter: v => v + '%', style: { fontSize: '10px' } },
            grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
            tooltip: { y: { formatter: v => v + '%' } },
        }).render();
    }

    // ── Attendance Heatmap ────────────────────────────────────────────────
    var heatmapRaw = @json($heatmapData);
    var weekdays = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
    var heatSeries = weekdays.map(function(dayName, dayIdx) {
        var values = [];
        for (var w = 9; w >= 0; w--) {
            var d = new Date();
            var day = d.getDate() - (d.getDay() === 0 ? 6 : d.getDay() - 1) - w * 7 + dayIdx;
            var nd = new Date(d.getFullYear(), d.getMonth(), day);
            var iso = nd.toISOString().slice(0, 10);
            var entry = heatmapRaw[iso];
            values.push({ x: 'W' + (10 - w), y: entry ? parseInt(entry.pct) : 0 });
        }
        return { name: dayName, data: values };
    });

    if (document.getElementById('chart-heatmap')) {
        new ApexCharts(document.getElementById('chart-heatmap'), {
            chart: { type: 'heatmap', height: 200, toolbar: { show: false } },
            series: heatSeries,
            dataLabels: { enabled: false },
            xaxis: { labels: { style: { fontSize: '9px' } } },
            yaxis: { labels: { style: { fontSize: '10px' } } },
            tooltip: { y: { formatter: v => v > 0 ? v + '% attendance' : 'No data' } },
            plotOptions: {
                heatmap: {
                    shadeIntensity: 0.85,
                    radius: 4,
                    colorScale: {
                        ranges: [
                            { from: 0,  to: 0,   color: '#f1f5f9', name: 'No data' },
                            { from: 1,  to: 50,  color: '#fca5a5', name: 'Low (<50%)' },
                            { from: 51, to: 74,  color: '#fbbf24', name: 'Below 75%' },
                            { from: 75, to: 89,  color: '#86efac', name: 'Good' },
                            { from: 90, to: 100, color: '#16a34a', name: 'Excellent' },
                        ]
                    }
                }
            },
        }).render();
    }

    // ── Widget Toggle / Personalization ───────────────────────────────────
    var STORAGE_KEY = 'dashboard_hidden_widgets';
    function getHidden() {
        try { return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]'); } catch(e) { return []; }
    }
    function saveHidden(arr) { localStorage.setItem(STORAGE_KEY, JSON.stringify(arr)); }

    function applyVisibility() {
        var hidden = getHidden();
        document.querySelectorAll('[data-widget]').forEach(function(el) {
            el.style.display = hidden.includes(el.dataset.widget) ? 'none' : '';
        });
        document.querySelectorAll('.widget-toggle-btn').forEach(function(btn) {
            var isHidden = hidden.includes(btn.dataset.toggle);
            var label = btn.getAttribute('data-label');
            btn.innerHTML = (isHidden ? '<i class="bi bi-eye-slash"></i> ' : '<i class="bi bi-eye-fill"></i> ') + label;
            btn.classList.toggle('opacity-40', isHidden);
            btn.classList.toggle('line-through', isHidden);
        });
    }

    document.querySelectorAll('.widget-toggle-btn').forEach(function(btn) {
        btn.setAttribute('data-label', btn.textContent.trim());
        btn.addEventListener('click', function() {
            var key = btn.dataset.toggle;
            var hidden = getHidden();
            saveHidden(hidden.includes(key) ? hidden.filter(k => k !== key) : [...hidden, key]);
            applyVisibility();
        });
    });

    applyVisibility();
})();
</script>
@endpush
