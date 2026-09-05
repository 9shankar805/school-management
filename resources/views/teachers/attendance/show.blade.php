@extends('layouts.app')

@section('content')
<div class="px-4 lg:px-8 py-6 max-w-7xl mx-auto">

    {{-- Page Header --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <span class="w-10 h-10 rounded-2xl bg-indigo-600/10 text-indigo-600 flex items-center justify-center font-bold text-xl shadow-xs">
                    <i class="bi bi-calendar-check"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Teacher Attendance Report</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Monthly attendance record for {{ $teacher->first_name }} {{ $teacher->last_name }}</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <form method="GET" class="flex items-center gap-2">
                <select name="month" class="text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>{{ DateTime::createFromFormat('!m', $m)->format('F') }}</option>
                    @endforeach
                </select>
                <select name="year" class="text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100">
                    @foreach(range(now()->year - 2, now()->year + 1) as $y)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs">Filter</button>
            </form>
            <a href="{{ route('teachers.attendance.index') }}" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 text-xs font-semibold transition">
                Back
            </a>
        </div>
    </div>

    @include('session-messages')

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 text-center">
            <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">Present</span>
            <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-1">{{ $summary['present'] ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 text-center">
            <span class="text-xs font-medium text-rose-600 dark:text-rose-400">Absent</span>
            <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-1">{{ $summary['absent'] ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 text-center">
            <span class="text-xs font-medium text-amber-600 dark:text-amber-400">Late</span>
            <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-1">{{ $summary['late'] ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 text-center">
            <span class="text-xs font-medium text-indigo-600 dark:text-indigo-400">Half Day</span>
            <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-1">{{ $summary['half_day'] ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 text-center col-span-2 sm:col-span-1">
            <span class="text-xs font-medium text-sky-600 dark:text-sky-400">On Leave</span>
            <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-1">{{ $summary['on_leave'] ?? 0 }}</p>
        </div>
    </div>

    {{-- Calendar Grid --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center gap-2">
            <span class="w-2 h-5 bg-indigo-600 rounded-full"></span>
            Daily Attendance Log ({{ $startOfMonth->format('F Y') }})
        </h3>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
            @for ($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
                    $rec = $records->get($dateStr);
                    $status = $rec ? $rec->status : 'unmarked';
                    $badgeClass = match($status) {
                        'present'  => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
                        'absent'   => 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300',
                        'late'     => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                        'half_day' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
                        'on_leave' => 'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300',
                        default    => 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'
                    };
                @endphp
                <div class="p-3 rounded-2xl border border-slate-200/70 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Day {{ $day }}</span>
                        <p class="text-[10px] text-slate-400">{{ \Carbon\Carbon::parse($dateStr)->format('D') }}</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold capitalize {{ $badgeClass }}">
                        {{ str_replace('_', ' ', $status) }}
                    </span>
                </div>
            @endfor
        </div>
    </div>
</div>
@endsection
