@extends('layouts.app')

@section('title', 'Calendar & Events')

@section('content')
<div class="p-4 lg:p-6 space-y-6 max-w-[1400px] mx-auto">

    {{-- ── Page header ──────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl flex items-center justify-center flex-shrink-0"
                 style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
                <i class="bi bi-calendar3 text-white text-base"></i>
            </div>
            <div>
                <h1 class="text-xl font-black text-slate-800 leading-tight">Calendar &amp; Events</h1>
                <p class="text-xs text-slate-500 mt-0.5">Manage and schedule school events for this academic session</p>
            </div>
        </div>
        @can('create events')
        <button onclick="openCreateEventModal()"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white shadow-sm hover:opacity-90 active:scale-95 transition-all"
                style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
            <i class="bi bi-plus-lg text-xs"></i> New Event
        </button>
        @endcan
    </div>

    {{-- ── Stat cards ───────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

        {{-- Total events --}}
        <div class="bg-white rounded-2xl p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0"
                 style="background:#eef2ff">
                <i class="bi bi-calendar2-check text-indigo-600 text-lg"></i>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-800 leading-none">{{ $totalEvents }}</p>
                <p class="text-xs text-slate-500 mt-1">Total Events</p>
            </div>
        </div>

        {{-- This month --}}
        <div class="bg-white rounded-2xl p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0"
                 style="background:#f0fdf4">
                <i class="bi bi-calendar-week text-emerald-600 text-lg"></i>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-800 leading-none">{{ $thisMonthEvents }}</p>
                <p class="text-xs text-slate-500 mt-1">This Month</p>
            </div>
        </div>

        {{-- Upcoming --}}
        <div class="bg-white rounded-2xl p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0"
                 style="background:#fff7ed">
                <i class="bi bi-alarm text-amber-600 text-lg"></i>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-800 leading-none">{{ $upcomingEvents->count() }}</p>
                <p class="text-xs text-slate-500 mt-1">Upcoming</p>
            </div>
        </div>

    </div>

    {{-- ── Main two-column layout ────────────────────────────────────── --}}
    <div class="grid grid-cols-1 xl:grid-cols-[1fr_300px] gap-6 items-start">

        {{-- Left: Calendar --}}
        <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #e2e8f0">
            @include('components.events.event-calendar', ['editable' => 'true', 'selectable' => 'true'])
        </div>

        {{-- Right: Upcoming events sidebar --}}
        <div class="space-y-4">

            {{-- Section title --}}
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-700">Upcoming Events</h2>
                <span class="text-xs text-slate-400">Next {{ $upcomingEvents->count() }} events</span>
            </div>

            @forelse($upcomingEvents as $event)
            @php
                $color   = $event->color ?? '#6366f1';
                $start   = \Carbon\Carbon::parse($event->start);
                $isToday = $start->isToday();
                $isTomorrow = $start->isTomorrow();
                $label   = $isToday ? 'Today' : ($isTomorrow ? 'Tomorrow' : $start->format('M d'));
            @endphp
            <div class="bg-white rounded-2xl p-4 flex gap-3 hover:shadow-md transition-shadow" style="border:1px solid #e2e8f0">
                {{-- Colour dot / date block --}}
                <div class="w-11 h-11 rounded-xl flex-shrink-0 flex flex-col items-center justify-center text-white leading-tight"
                     style="background:{{ $color }}">
                    <span class="text-[10px] font-bold uppercase">{{ $start->format('M') }}</span>
                    <span class="text-base font-black leading-none">{{ $start->format('d') }}</span>
                </div>
                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-800 truncate">{{ $event->title }}</p>
                    <p class="text-xs text-slate-500 mt-0.5 flex items-center gap-1">
                        <i class="bi bi-clock text-[10px]"></i>
                        {{ $start->format('g:i A') }}
                        @if($event->end)
                         – {{ \Carbon\Carbon::parse($event->end)->format('g:i A') }}
                        @endif
                    </p>
                    @if($event->description)
                    <p class="text-[11px] text-slate-400 mt-1 line-clamp-1">{{ $event->description }}</p>
                    @endif
                </div>
                {{-- Badge --}}
                <span class="self-start text-[10px] font-bold px-2 py-0.5 rounded-full flex-shrink-0"
                      style="background:{{ $color }}22;color:{{ $color }}">
                    {{ $label }}
                </span>
            </div>
            @empty
            <div class="bg-white rounded-2xl p-8 text-center" style="border:1px solid #e2e8f0">
                <div class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <i class="bi bi-calendar-x text-slate-300 text-xl"></i>
                </div>
                <p class="text-sm font-semibold text-slate-600">No upcoming events</p>
                <p class="text-xs text-slate-400 mt-1">Click "New Event" or select a date on the calendar to add one.</p>
            </div>
            @endforelse

            {{-- Legend --}}
            <div class="bg-white rounded-2xl p-4" style="border:1px solid #e2e8f0">
                <p class="text-xs font-bold text-slate-600 mb-3">How to use</p>
                <ul class="space-y-2 text-xs text-slate-500">
                    <li class="flex items-start gap-2"><i class="bi bi-mouse2 text-indigo-400 mt-0.5"></i> <span>Click &amp; drag on the calendar to create an event</span></li>
                    <li class="flex items-start gap-2"><i class="bi bi-arrow-left-right text-indigo-400 mt-0.5"></i> <span>Drag events to reschedule them</span></li>
                    <li class="flex items-start gap-2"><i class="bi bi-hand-index text-indigo-400 mt-0.5"></i> <span>Click an event to view, edit or delete</span></li>
                    <li class="flex items-start gap-2"><i class="bi bi-plus-circle text-indigo-400 mt-0.5"></i> <span>Use "New Event" button to add directly</span></li>
                </ul>
            </div>

        </div>
    </div>

    @include('layouts.footer')
</div>
@endsection
