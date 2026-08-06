@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/fullcalendar5.9.0.min.css') }}">
<script src="{{ asset('js/fullcalendar5.9.0.main.min.js') }}"></script>

<div class="flex-1 p-6 lg:p-8 overflow-auto">

    {{-- Breadcrumb & Header --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <nav class="text-xs text-slate-400 mb-2">
                <a href="{{ route('home') }}" class="hover:text-indigo-600">Home</a>
                <span class="mx-1">/</span>
                <span class="text-slate-600">Attendance Breakdown</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">
                <i class="bi bi-calendar-check-fill me-2 text-indigo-600"></i>Student Attendance Record
            </h1>
        </div>
    </div>

    @include('session-messages')

    {{-- Student Profile & Class Context Header --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mb-6 flex flex-wrap items-center justify-between gap-5">
        <div class="flex items-center gap-4">
            <img src="{{ $student->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($student->first_name.' '.$student->last_name).'&background=6366f1&color=fff' }}"
                 class="w-16 h-16 rounded-2xl object-cover border-2 border-indigo-100 shadow-xs"
                 alt="{{ $student->first_name }}">
            <div>
                <h2 class="text-xl font-bold text-slate-800">{{ $student->first_name }} {{ $student->last_name }}</h2>
                <div class="flex flex-wrap items-center gap-2 mt-1">
                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700">
                        <i class="bi bi-mortarboard"></i> {{ $classInfo?->schoolClass?->class_name ?? 'Class' }} — {{ $classInfo?->section?->section_name ?? 'Section' }}
                    </span>
                    @if($student->email)
                    <span class="text-xs text-slate-400">
                        <i class="bi bi-envelope me-1"></i>{{ $student->email }}
                    </span>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <div class="text-right hidden sm:block">
                <p class="text-xs text-slate-400 font-medium uppercase tracking-wide">Status</p>
                <p class="text-sm font-bold {{ $attendancePct >= 75 ? 'text-emerald-600' : 'text-rose-600' }}">
                    {{ $attendancePct >= 75 ? 'Eligible (≥75%)' : 'Shortage Warning' }}
                </p>
            </div>
            <span class="w-12 h-12 rounded-2xl {{ $attendancePct >= 75 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }} flex items-center justify-center text-xl font-bold">
                <i class="bi {{ $attendancePct >= 75 ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' }}"></i>
            </span>
        </div>
    </div>

    {{-- KPI Metric Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 transition hover:shadow-md">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Overall Rate</p>
                <span class="w-9 h-9 rounded-xl {{ $attendancePct >= 75 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }} flex items-center justify-center text-base">
                    <i class="bi bi-pie-chart-fill"></i>
                </span>
            </div>
            <p class="text-3xl font-bold {{ $attendancePct >= 75 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $attendancePct }}%</p>
            <p class="mt-1 text-xs text-slate-400">{{ $presentCount }} present / {{ $totalAttendance }} sessions</p>
            <div class="mt-3 h-2 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-500 {{ $attendancePct >= 75 ? 'bg-emerald-500' : 'bg-rose-500' }}" style="width: {{ $attendancePct }}%"></div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 transition hover:shadow-md">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Present Days</p>
                <span class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base">
                    <i class="bi bi-check-circle-fill"></i>
                </span>
            </div>
            <p class="text-3xl font-bold text-slate-800">{{ $presentCount }}</p>
            <p class="mt-1 text-xs text-emerald-600 font-medium">Classes attended</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 transition hover:shadow-md">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Absent Days</p>
                <span class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-base">
                    <i class="bi bi-x-circle-fill"></i>
                </span>
            </div>
            <p class="text-3xl font-bold text-rose-600">{{ $absentCount }}</p>
            <p class="mt-1 text-xs text-slate-400">Classes missed</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 transition hover:shadow-md">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Total Recorded</p>
                <span class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-base">
                    <i class="bi bi-calendar-event"></i>
                </span>
            </div>
            <p class="text-3xl font-bold text-slate-800">{{ $totalAttendance }}</p>
            <p class="mt-1 text-xs text-slate-400">Total sessions</p>
        </div>
    </div>

    {{-- Attendance Shortage Alert Banner --}}
    @if($attendancePct < 75 && $totalAttendance > 0)
    <div class="bg-rose-50 border border-rose-200 rounded-2xl p-5 mb-6 flex items-start gap-4 shadow-xs">
        <span class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center text-xl flex-shrink-0">
            <i class="bi bi-exclamation-triangle-fill"></i>
        </span>
        <div class="flex-1">
            <h3 class="font-bold text-rose-800 text-sm">Attendance Shortage Alert</h3>
            <p class="text-rose-700 text-xs mt-1 leading-relaxed">
                Current attendance rate is <strong>{{ $attendancePct }}%</strong>, which is below the mandatory <strong>75%</strong> requirement. 
                Approximately <strong>{{ $shortageDeficit }}</strong> additional present day(s) are required to reach the qualification criteria for upcoming examinations.
            </p>
        </div>
    </div>
    @endif

    {{-- Monthly Calendar & FullCalendar Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
        
        {{-- Interactive Month Grid Calendar --}}
        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            @php
                $targetDate  = \Carbon\Carbon::createFromDate($year, $month, 1);
                $prevMonth   = (clone $targetDate)->subMonth();
                $nextMonth   = (clone $targetDate)->addMonth();
                $daysInMonth = $targetDate->daysInMonth;
                $startDay    = $targetDate->dayOfWeekIso; // 1=Mon

                $monthlyLogs = $attendances->filter(function($a) use ($year, $month) {
                    $d = \Carbon\Carbon::parse($a->created_at);
                    return $d->year === $year && $d->month === $month;
                })->groupBy(fn($a) => \Carbon\Carbon::parse($a->created_at)->day);
            @endphp

            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-slate-800">
                    <i class="bi bi-calendar3 me-1.5 text-indigo-500"></i>{{ $targetDate->format('F Y') }}
                </h3>
                <div class="flex gap-1">
                    <a href="{{ route('student.attendance.show', [$student->id, 'month' => $prevMonth->month, 'year' => $prevMonth->year]) }}"
                       class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs transition">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                    <a href="{{ route('student.attendance.show', [$student->id, 'month' => $nextMonth->month, 'year' => $nextMonth->year]) }}"
                       class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs transition">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
            </div>

            {{-- Weekday Headers --}}
            <div class="grid grid-cols-7 gap-1 text-center mb-2">
                @foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $day)
                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">{{ $day }}</p>
                @endforeach
            </div>

            {{-- Day Cells --}}
            <div class="grid grid-cols-7 gap-1 text-center">
                @for($i = 1; $i < $startDay; $i++)
                <div></div>
                @endfor

                @for($d = 1; $d <= $daysInMonth; $d++)
                    @php
                        $dayLogs = $monthlyLogs->get($d, collect());
                        $isPresent = $dayLogs->contains(fn($a) => in_array(strtolower((string)$a->status), ['present', 'on', '1']));
                        $isAbsent  = $dayLogs->contains(fn($a) => in_array(strtolower((string)$a->status), ['absent', 'off', '0']));
                        
                        if ($isPresent)      $cls = 'bg-emerald-100 text-emerald-800 font-bold border border-emerald-200';
                        elseif ($isAbsent)   $cls = 'bg-rose-100 text-rose-800 font-bold border border-rose-200';
                        else                 $cls = 'text-slate-500 hover:bg-slate-50';
                    @endphp
                    <div class="rounded-xl py-2 text-xs transition {{ $cls }}" title="Day {{ $d }}">
                        {{ $d }}
                    </div>
                @endfor
            </div>

            {{-- Legend --}}
            <div class="flex items-center gap-4 mt-4 pt-3 border-t border-slate-100 text-xs text-slate-500 justify-center">
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-emerald-400 inline-block"></span>Present</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-rose-400 inline-block"></span>Absent</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-slate-200 inline-block"></span>No record</span>
            </div>
        </div>

        {{-- FullCalendar Interactive Box --}}
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-slate-800 mb-3">
                <i class="bi bi-grid-3x3-gap-fill me-1.5 text-indigo-500"></i>Full Attendance Calendar
            </h3>
            <div id="attendanceCalendar" class="rounded-xl overflow-hidden min-h-[340px]"></div>
        </div>
    </div>

    {{-- Attendance History Logs Table --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-800"><i class="bi bi-clock-history me-1.5 text-indigo-600"></i>Attendance Logs</h3>
            <span class="text-xs text-slate-400 font-medium">{{ $attendances->count() }} entries recorded</span>
        </div>

        @if($attendances->isEmpty())
            <div class="p-10 text-center text-slate-400 text-sm">
                <i class="bi bi-calendar-x text-4xl text-slate-200 block mb-2"></i>
                No attendance logs found for this student.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50/80 text-xs text-slate-500 font-semibold uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Date & Time</th>
                            <th class="px-5 py-3">Subject / Section</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 text-slate-700">
                        @foreach ($attendances as $attendance)
                        @php
                            $isPres = in_array(strtolower((string)$attendance->status), ['present', 'on', '1']);
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2.5 py-1 rounded-full {{ $isPres ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                    <i class="bi {{ $isPres ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }}"></i>
                                    {{ $isPres ? 'PRESENT' : 'ABSENT' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 font-medium text-slate-800">
                                {{ \Carbon\Carbon::parse($attendance->created_at)->format('M d, Y') }}
                                <span class="text-xs text-slate-400 font-normal ms-1">({{ \Carbon\Carbon::parse($attendance->created_at)->format('h:i A') }})</span>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-600">
                                {{ $attendance->course?->course_name ?? $attendance->section?->section_name ?? 'Regular Class' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>

@php
$events = array();
if(count($attendances) > 0){
    foreach ($attendances as $attendance){
        $isPres = in_array(strtolower((string)$attendance->status), ['present', 'on', '1']);
        $events[] = [
            'title' => $isPres ? 'Present' : 'Absent',
            'start' => \Carbon\Carbon::parse($attendance->created_at)->toIso8601String(),
            'color' => $isPres ? '#10b981' : '#f43f5e',
        ];
    }
}
@endphp

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('attendanceCalendar');
    var attEvents = @json($events);
                            
    if (calendarEl && typeof FullCalendar !== 'undefined') {
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            height: 380,
            headerToolbar: {
                left: 'title',
                right: 'prev,next'
            },
            events: attEvents,
        });
        calendar.render();
    }
});
</script>
@endsection
