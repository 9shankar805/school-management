@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Lesson Plans</h1>
                <p class="text-slate-400 text-sm mt-0.5">Teacher planning &amp; daily lesson records</p>
            </div>
            @can('create lesson plans')
            <a href="{{ route('lesson-plans.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                <i class="bi bi-plus-lg"></i> New Plan
            </a>
            @endcan
        </div>

        @include('session-messages')

        {{-- Filters --}}
        <form method="GET" action="{{ route('lesson-plans.index') }}" class="flex flex-wrap gap-2 mb-6">
            <select name="class_id" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">All Classes</option>
                @foreach($classes as $cls)
                <option value="{{ $cls->id }}" {{ request('class_id') == $cls->id ? 'selected' : '' }}>{{ $cls->class_name }}</option>
                @endforeach
            </select>
            <select name="status" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">All Statuses</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-lg text-sm hover:bg-slate-50 transition">Filter</button>
            @if(request()->hasAny(['class_id','status']))
            <a href="{{ route('lesson-plans.index') }}" class="px-4 py-2 bg-white border border-slate-200 text-slate-500 rounded-lg text-sm hover:bg-slate-50 transition">Clear</a>
            @endif
        </form>

        <div class="space-y-3">
            @forelse($lessonPlans as $plan)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 hover:shadow-md transition">
                <div class="flex flex-wrap justify-between items-start gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <h3 class="font-semibold text-slate-800">{{ $plan->title }}</h3>
                            <span class="text-[11px] px-2 py-0.5 rounded-full font-medium
                                @if($plan->status === 'completed') bg-emerald-100 text-emerald-700
                                @elseif($plan->status === 'approved') bg-blue-100 text-blue-700
                                @else bg-amber-100 text-amber-700 @endif">
                                {{ ucfirst($plan->status) }}
                            </span>
                        </div>
                        <div class="flex flex-wrap gap-4 text-xs text-slate-500">
                            <span><i class="bi bi-calendar3 me-1"></i>{{ $plan->planned_date->format('M d, Y') }}</span>
                            <span><i class="bi bi-book me-1"></i>{{ $plan->course->course_name ?? '—' }}</span>
                            <span><i class="bi bi-layers me-1"></i>{{ $plan->schoolClass->class_name ?? '—' }}</span>
                            @if($plan->section)<span><i class="bi bi-grid me-1"></i>{{ $plan->section->section_name }}</span>@endif
                            <span><i class="bi bi-clock me-1"></i>{{ $plan->duration_minutes }}min</span>
                            @if($plan->term)<span><i class="bi bi-calendar2-range me-1"></i>{{ $plan->term->name }}</span>@endif
                        </div>
                        @if($plan->objectives)
                        <p class="mt-1.5 text-xs text-slate-400 line-clamp-1">{{ $plan->objectives }}</p>
                        @endif
                    </div>
                    <div class="flex gap-2 flex-shrink-0">
                        <a href="{{ route('lesson-plans.show', $plan->id) }}"
                           class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="View">
                            <i class="bi bi-eye text-sm"></i>
                        </a>
                        @if(auth()->id() === $plan->teacher_id)
                        <a href="{{ route('lesson-plans.edit', $plan->id) }}"
                           class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition" title="Edit">
                            <i class="bi bi-pencil text-sm"></i>
                        </a>
                        <form action="{{ route('lesson-plans.destroy', $plan->id) }}" method="POST" onsubmit="return confirm('Delete this plan?')">
                            @csrf @method('DELETE')
                            <button class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition">
                                <i class="bi bi-trash text-sm"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-12 text-center">
                <i class="bi bi-journal-plus text-5xl text-slate-200"></i>
                <p class="mt-3 text-slate-400">No lesson plans found.</p>
                @can('create lesson plans')
                <a href="{{ route('lesson-plans.create') }}" class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">
                    <i class="bi bi-plus-lg"></i> Create First Plan
                </a>
                @endcan
            </div>
            @endforelse
        </div>

        <div class="mt-4">{{ $lessonPlans->links() }}</div>
    </div>
</div>
@endsection
