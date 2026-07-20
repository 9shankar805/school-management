@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="mb-7">
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">My Homework</h1>
            <p class="text-slate-400 text-sm mt-0.5">Pending and past homework assignments</p>
        </div>

        @include('session-messages')

        <div class="space-y-3">
            @forelse($homeworks as $hw)
            @php $sub = $hw->mySubmission; @endphp
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 hover:shadow-md transition">
                <div class="flex flex-wrap justify-between items-start gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <h3 class="font-semibold text-slate-800">{{ $hw->title }}</h3>
                            @if($sub)
                                @if($sub->status === 'graded')
                                <span class="text-[11px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">
                                    Graded: {{ $sub->marks_obtained }}/{{ $hw->total_marks }}
                                </span>
                                @else
                                <span class="text-[11px] px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">Submitted</span>
                                @endif
                            @elseif($hw->due_date->isPast())
                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-rose-100 text-rose-700">Overdue</span>
                            @else
                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">Pending</span>
                            @endif
                        </div>
                        <div class="flex flex-wrap gap-4 text-xs text-slate-500">
                            <span><i class="bi bi-book me-1"></i>{{ $hw->course->course_name ?? '—' }}</span>
                            <span><i class="bi bi-calendar-event me-1"></i>Due: {{ $hw->due_date->format('M d, Y') }}</span>
                            <span><i class="bi bi-award me-1"></i>{{ $hw->total_marks }} marks</span>
                        </div>
                        @if($hw->description)
                        <p class="mt-1.5 text-xs text-slate-400 line-clamp-2">{{ $hw->description }}</p>
                        @endif
                        @if($sub?->teacher_feedback)
                        <div class="mt-2 bg-emerald-50 rounded-lg px-3 py-2 text-xs text-emerald-700">
                            <i class="bi bi-chat-text me-1"></i><strong>Feedback:</strong> {{ $sub->teacher_feedback }}
                        </div>
                        @endif
                    </div>
                    <div class="flex-shrink-0">
                        <a href="{{ route('homework.show', $hw->id) }}"
                           class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-medium transition">
                            <i class="bi bi-arrow-right-circle text-xs"></i>
                            {{ $sub ? 'View' : 'Submit' }}
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-12 text-center">
                <i class="bi bi-check2-all text-5xl text-slate-200"></i>
                <p class="mt-3 text-slate-400">No homework assigned to your class yet.</p>
            </div>
            @endforelse
        </div>

        <div class="mt-4">{{ $homeworks->links() }}</div>
    </div>
</div>
@endsection
