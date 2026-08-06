@extends('layouts.app')
@section('content')
<div class="flex-1 p-6 lg:p-8 overflow-auto">

    <nav class="text-xs text-slate-400 mb-4">
        <a href="{{ url('home') }}" class="hover:text-indigo-600">Dashboard</a>
        <span class="mx-1">/</span>
        <span class="text-slate-600">{{ $child->full_name }} — Homework</span>
    </nav>

    <div class="flex flex-wrap justify-between items-start mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Homework</h1>
            <p class="text-slate-400 text-sm mt-0.5">All homework assigned to {{ $child->first_name }}</p>
        </div>
        <div class="flex items-center gap-2">
            <img src="{{ $child->avatar }}" class="w-8 h-8 rounded-full object-cover border border-indigo-100" alt="">
            <span class="text-sm font-medium text-slate-700">{{ $child->full_name }}</span>
        </div>
    </div>

    @include('session-messages')

    @if(!$promotion)
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 text-sm text-amber-800">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>No class/section linked for this session.
    </div>
    @elseif($homeworks->isEmpty())
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-14 text-center">
        <i class="bi bi-pencil-square text-5xl text-slate-200 block mb-3"></i>
        <p class="text-slate-500 font-medium">No homework assigned yet.</p>
    </div>
    @else

    {{-- Summary strip --}}
    @php
        $submitted = $homeworks->filter(fn($h) => $h->mySubmission)->count();
        $pending   = $homeworks->filter(fn($h) => !$h->mySubmission && $h->status === 'active')->count();
        $graded    = $homeworks->filter(fn($h) => $h->mySubmission?->status === 'graded')->count();
    @endphp
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-slate-800">{{ $homeworks->count() }}</p>
            <p class="text-xs text-slate-400 mt-1">Total Assigned</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-emerald-600">{{ $submitted }}</p>
            <p class="text-xs text-slate-400 mt-1">Submitted</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold {{ $pending > 0 ? 'text-rose-600' : 'text-slate-400' }}">{{ $pending }}</p>
            <p class="text-xs text-slate-400 mt-1">Pending</p>
        </div>
    </div>

    <div class="space-y-3">
        @foreach($homeworks as $hw)
        @php
            $sub   = $hw->mySubmission;
            $isPast = $hw->due_date->isPast();
            $isToday = $hw->due_date->isToday();
        @endphp
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <div class="flex flex-wrap justify-between items-start gap-3">
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <h3 class="font-semibold text-slate-800">{{ $hw->title }}</h3>
                        {{-- Status badge --}}
                        @if($sub && $sub->status === 'graded')
                        <span class="text-[11px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 font-medium">Graded</span>
                        @elseif($sub)
                        <span class="text-[11px] px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 font-medium">Submitted</span>
                        @elseif($isPast)
                        <span class="text-[11px] px-2 py-0.5 rounded-full bg-rose-100 text-rose-700 font-medium">Missed</span>
                        @elseif($isToday)
                        <span class="text-[11px] px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 font-medium">Due Today</span>
                        @else
                        <span class="text-[11px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 font-medium">Pending</span>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-4 text-xs text-slate-500">
                        <span><i class="bi bi-book me-1"></i>{{ $hw->course->course_name ?? '—' }}</span>
                        <span><i class="bi bi-person me-1"></i>{{ $hw->teacher->full_name ?? '—' }}</span>
                        <span class="{{ $isPast && !$sub ? 'text-rose-500 font-medium' : '' }}">
                            <i class="bi bi-calendar-event me-1"></i>Due: {{ $hw->due_date->format('M d, Y') }}
                        </span>
                        <span><i class="bi bi-award me-1"></i>{{ $hw->total_marks }} marks</span>
                    </div>
                    @if($hw->description)
                    <p class="mt-1.5 text-xs text-slate-400 line-clamp-2">{{ $hw->description }}</p>
                    @endif
                </div>

                {{-- Grade box --}}
                @if($sub && $sub->marks_obtained !== null)
                <div class="flex-shrink-0 text-center bg-indigo-50 rounded-xl px-4 py-2">
                    <p class="text-xl font-bold text-indigo-700">{{ $sub->marks_obtained }}<span class="text-sm font-normal text-indigo-400">/{{ $hw->total_marks }}</span></p>
                    <p class="text-[10px] text-indigo-500">Score</p>
                </div>
                @endif
            </div>

            @if($sub && $sub->teacher_feedback)
            <div class="mt-3 bg-emerald-50 border border-emerald-100 rounded-xl px-4 py-2 text-xs text-emerald-800">
                <i class="bi bi-chat-quote me-1"></i><span class="font-medium">Teacher feedback:</span> {{ $sub->teacher_feedback }}
            </div>
            @endif

            @if($hw->file_path)
            <div class="mt-3">
                <a href="{{ asset('storage/'.$hw->file_path) }}" target="_blank"
                   class="inline-flex items-center gap-1 text-xs text-indigo-600 hover:underline">
                    <i class="bi bi-download"></i> Download question file
                </a>
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif

</div>
</div>
@endsection
