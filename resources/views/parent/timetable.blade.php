@extends('layouts.app')
@section('content')
<div class="flex-1 p-6 lg:p-8 overflow-auto">

    <nav class="text-xs text-slate-400 mb-4">
        <a href="{{ url('home') }}" class="hover:text-indigo-600">Dashboard</a>
        <span class="mx-1">/</span>
        <span class="text-slate-600">{{ $child->full_name }} — Timetable</span>
    </nav>

    <div class="flex flex-wrap justify-between items-start mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Class Timetable</h1>
            <p class="text-slate-400 text-sm mt-0.5">
                Weekly schedule for {{ $child->first_name }}
                @if($promotion)
                &middot; {{ $promotion->schoolClass->class_name ?? '' }} {{ $promotion->section->section_name ?? '' }}
                @endif
            </p>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
            <img src="{{ $child->avatar }}" class="w-8 h-8 rounded-full object-cover border border-indigo-100" alt="">
            <span class="text-sm font-medium text-slate-700">{{ $child->full_name }}</span>
        </div>
    </div>

    @if(!$promotion)
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 text-sm text-amber-800">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>No class/section linked for this session.
    </div>
    @elseif($slots->isEmpty())
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-14 text-center">
        <i class="bi bi-calendar4-week text-5xl text-slate-200 block mb-3"></i>
        <p class="text-slate-500 font-medium">No timetable slots found for this class.</p>
    </div>
    @else

    {{-- Today's highlight --}}
    @if($slots->has($todayWeekday))
    <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-4 mb-5">
        <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wide mb-3">
            <i class="bi bi-sun-fill me-1"></i>Today — {{ $days[$todayWeekday] }}
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($slots[$todayWeekday] as $slot)
            <div class="bg-white rounded-xl border border-indigo-100 p-3 flex gap-3 items-center">
                <div class="w-1 self-stretch rounded-full flex-shrink-0" style="background:{{ $slot->color ?? '#6366f1' }}"></div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-slate-800 truncate">{{ $slot->course->course_name ?? '—' }}</p>
                    <p class="text-xs text-slate-500">{{ $slot->start }} – {{ $slot->end }}</p>
                    @if($slot->teacher)
                    <p class="text-xs text-slate-400 truncate"><i class="bi bi-person me-0.5"></i>{{ $slot->teacher->full_name }}</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Full week grid --}}
    <div class="space-y-4">
        @foreach($days as $dayNum => $dayName)
        @if($slots->has($dayNum))
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 flex items-center gap-2 {{ $dayNum === $todayWeekday ? 'bg-indigo-50' : 'bg-slate-50' }}">
                <span class="text-sm font-semibold {{ $dayNum === $todayWeekday ? 'text-indigo-700' : 'text-slate-700' }}">
                    {{ $dayName }}
                </span>
                @if($dayNum === $todayWeekday)
                <span class="text-[10px] bg-indigo-500 text-white px-2 py-0.5 rounded-full font-medium">Today</span>
                @endif
                <span class="ml-auto text-xs text-slate-400">{{ $slots[$dayNum]->count() }} classes</span>
            </div>
            <div class="divide-y divide-slate-50">
                @foreach($slots[$dayNum] as $slot)
                <div class="px-5 py-3 flex items-center gap-4">
                    <div class="w-1 self-stretch rounded-full flex-shrink-0 min-h-[36px]"
                         style="background:{{ $slot->color ?? '#6366f1' }}"></div>
                    <div class="w-20 flex-shrink-0">
                        <p class="text-xs font-bold text-slate-700">{{ \Carbon\Carbon::parse($slot->start)->format('H:i') }}</p>
                        <p class="text-[10px] text-slate-400">{{ \Carbon\Carbon::parse($slot->end)->format('H:i') }}</p>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-700">{{ $slot->course->course_name ?? '—' }}</p>
                        @if($slot->teacher)
                        <p class="text-xs text-slate-400"><i class="bi bi-person me-0.5"></i>{{ $slot->teacher->full_name }}</p>
                        @endif
                    </div>
                    @if($slot->room)
                    <span class="flex-shrink-0 text-xs text-slate-400 bg-slate-50 border border-slate-100 px-2 py-0.5 rounded-lg">
                        <i class="bi bi-geo-alt me-0.5"></i>{{ $slot->room }}
                    </span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
        @endforeach
    </div>
    @endif

</div>
</div>
@endsection
