@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex items-center gap-3 mb-7">
            <a href="{{ route('staff.attendance.index') }}" class="text-slate-400 hover:text-slate-600 transition"><i class="bi bi-arrow-left text-lg"></i></a>
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">{{ $staffMember->full_name }} — Attendance</h1>
                <p class="text-slate-400 text-sm mt-0.5 capitalize">{{ str_replace('-', ' ', $staffMember->primary_role) }}</p>
            </div>
        </div>

        {{-- Month/year picker --}}
        <form method="GET" action="{{ route('staff.attendance.show', $staffMember->id) }}" class="flex gap-2 mb-6">
            <select name="month" class="border border-slate-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                @foreach(range(1,12) as $m)
                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::createFromDate(null,$m,1)->format('F') }}</option>
                @endforeach
            </select>
            <select name="year" class="border border-slate-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                @foreach(range(now()->year - 2, now()->year + 1) as $y)
                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-1.5 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition">Load</button>
        </form>

        {{-- Summary chips --}}
        <div class="flex flex-wrap gap-3 mb-6">
            @foreach(['present'=>['emerald','Present'],'absent'=>['rose','Absent'],'late'=>['amber','Late'],'half_day'=>['blue','Half Day'],'on_leave'=>['violet','On Leave']] as $k=>[$c,$l])
            <div class="flex items-center gap-2 bg-white rounded-xl border border-slate-100 shadow-sm px-4 py-2.5">
                <span class="text-xl font-bold text-{{ $c }}-600">{{ $summary[$k] }}</span>
                <span class="text-xs text-slate-400">{{ $l }}</span>
            </div>
            @endforeach
        </div>

        {{-- Calendar grid --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <p class="text-sm font-semibold text-slate-700 mb-4">{{ $startOfMonth->format('F Y') }}</p>

            {{-- Day-of-week headers --}}
            <div class="grid grid-cols-7 gap-1 mb-1">
                @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d)
                <div class="text-center text-xs font-semibold text-slate-400 py-1">{{ $d }}</div>
                @endforeach
            </div>

            {{-- Blank days before the 1st --}}
            <div class="grid grid-cols-7 gap-1">
                @for($i = 0; $i < $startOfMonth->dayOfWeek; $i++)
                <div></div>
                @endfor

                @for($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $dateStr = $startOfMonth->copy()->day($day)->format('Y-m-d');
                    $rec     = $records[$dateStr] ?? null;
                    $isToday = $dateStr === now()->toDateString();
                    $bsColour = $rec?->status_bs_badge ?? 'light';
                    $colours  = [
                        'success'   => 'bg-emerald-100 text-emerald-700',
                        'danger'    => 'bg-rose-100 text-rose-700',
                        'warning'   => 'bg-amber-100 text-amber-700',
                        'info'      => 'bg-blue-100 text-blue-700',
                        'secondary' => 'bg-violet-100 text-violet-700',
                        'light'     => 'bg-slate-100 text-slate-400',
                    ];
                    $cls = $colours[$bsColour] ?? 'bg-slate-100 text-slate-400';
                @endphp
                <div class="rounded-xl p-1.5 text-center {{ $cls }} {{ $isToday ? 'ring-2 ring-indigo-400' : '' }}">
                    <p class="text-xs font-bold">{{ $day }}</p>
                    @if($rec)
                    <p class="text-[9px] leading-tight mt-0.5 capitalize">{{ str_replace('_',' ',$rec->status) }}</p>
                    @if($rec->late_minutes > 0)
                    <p class="text-[8px] opacity-70">+{{ $rec->late_minutes }}m</p>
                    @endif
                    @else
                    <p class="text-[9px] text-slate-300 mt-0.5">—</p>
                    @endif
                </div>
                @endfor
            </div>
        </div>
    </div>
</div>
@endsection
