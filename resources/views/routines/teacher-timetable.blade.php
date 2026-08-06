@extends('layouts.app')

@section('content')
<div class="flex-1 p-6 lg:p-8 overflow-auto">

    <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
        <div>
            <nav class="text-xs text-slate-400 mb-2">
                <a href="{{ route('home') }}" class="hover:text-indigo-600">Home</a>
                <span class="mx-1">/</span>
                <span class="text-slate-600">My Timetable</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">
                <i class="bi bi-clock-history me-2 text-indigo-600"></i>My Teaching Timetable
            </h1>
            <p class="text-slate-400 text-sm mt-0.5">Weekly class schedule & period slots for <span class="font-medium text-slate-700">{{ $teacher->full_name ?? $teacher->name }}</span></p>
        </div>
    </div>

    @include('session-messages')

    {{-- Weekday Schedule Grid --}}
    @php
        $days = [
            1 => 'Sunday',
            2 => 'Monday',
            3 => 'Tuesday',
            4 => 'Wednesday',
            5 => 'Thursday',
            6 => 'Friday',
            7 => 'Saturday',
        ];

        $groupedSlots = $slots->groupBy('weekday');
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        @foreach($days as $dayNum => $dayName)
        @php
            $daySlots = $groupedSlots->get($dayNum, collect());
        @endphp
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden flex flex-col">
            <div class="px-5 py-3.5 bg-slate-50/80 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-sm text-slate-800 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full {{ $daySlots->count() > 0 ? 'bg-indigo-500' : 'bg-slate-300' }}"></span>
                    {{ $dayName }}
                </h3>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $daySlots->count() > 0 ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-400' }}">
                    {{ $daySlots->count() }} class{{ $daySlots->count() === 1 ? '' : 'es' }}
                </span>
            </div>

            <div class="p-4 flex-1 space-y-3">
                @forelse($daySlots as $slot)
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 hover:border-indigo-200 transition">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md">
                            <i class="bi bi-clock me-1"></i>{{ $slot->start }} – {{ $slot->end }}
                        </span>
                        <span class="text-xs font-semibold text-slate-600">
                            {{ $slot->schoolClass?->class_name }} {{ $slot->section ? '('.$slot->section->section_name.')' : '' }}
                        </span>
                    </div>
                    <p class="font-bold text-sm text-slate-800 mt-1">
                        {{ $slot->course?->course_name ?? 'Scheduled Class' }}
                    </p>
                    @if($slot->room_number)
                    <p class="text-xs text-slate-400 mt-1">
                        <i class="bi bi-geo-alt me-1"></i>Room: {{ $slot->room_number }}
                    </p>
                    @endif
                </div>
                @empty
                <div class="py-8 text-center text-slate-400 text-xs">
                    <i class="bi bi-calendar-x text-2xl text-slate-300 block mb-1"></i>
                    No classes scheduled for {{ $dayName }}.
                </div>
                @endforelse
            </div>
        </div>
        @endforeach
    </div>

</div>
@endsection
