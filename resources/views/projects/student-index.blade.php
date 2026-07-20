@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">
        <div class="mb-7">
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">My Projects</h1>
            <p class="text-slate-400 text-sm mt-0.5">Projects assigned to your class</p>
        </div>

        @include('session-messages')

        <div class="space-y-3">
            @forelse($projects as $proj)
            @php $sub = $proj->mySubmission; @endphp
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 hover:shadow-md transition">
                <div class="flex flex-wrap justify-between items-start gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <h3 class="font-semibold text-slate-800">{{ $proj->title }}</h3>
                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-purple-100 text-purple-700">{{ ucfirst($proj->type) }}</span>
                            @if($sub)
                                @if($sub->status === 'graded')
                                <span class="text-[11px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">Graded: {{ $sub->marks_obtained }}/{{ $proj->total_marks }}</span>
                                @else
                                <span class="text-[11px] px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">Submitted</span>
                                @endif
                            @else
                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">Pending</span>
                            @endif
                        </div>
                        <div class="flex flex-wrap gap-4 text-xs text-slate-500">
                            <span><i class="bi bi-book me-1"></i>{{ $proj->course->course_name ?? '—' }}</span>
                            <span><i class="bi bi-calendar-event me-1"></i>Due: {{ $proj->due_date->format('M d, Y') }}</span>
                            <span><i class="bi bi-award me-1"></i>{{ $proj->total_marks }} marks</span>
                        </div>
                    </div>
                    <a href="{{ route('projects.show', $proj->id) }}"
                       class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-medium transition">
                        <i class="bi bi-arrow-right-circle text-xs"></i> {{ $sub ? 'View' : 'Submit' }}
                    </a>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-12 text-center">
                <i class="bi bi-folder-check text-5xl text-slate-200"></i>
                <p class="mt-3 text-slate-400">No projects assigned yet.</p>
            </div>
            @endforelse
        </div>
        <div class="mt-4">{{ $projects->links() }}</div>
    </div>
</div>
@endsection
