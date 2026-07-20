@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Curriculums</h1>
                <p class="text-slate-400 text-sm mt-0.5">Subject curriculum per class &amp; program</p>
            </div>
            @can('view academic settings')
            <a href="{{ route('curriculums.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                <i class="bi bi-plus-lg"></i> New Curriculum
            </a>
            @endcan
        </div>

        @include('session-messages')

        {{-- Filters --}}
        <form method="GET" action="{{ route('curriculums.index') }}" class="flex flex-wrap gap-2 mb-6">
            <select name="class_id" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">All Classes</option>
                @foreach($classes as $cls)
                <option value="{{ $cls->id }}" {{ $class_id == $cls->id ? 'selected' : '' }}>{{ $cls->class_name }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-lg text-sm hover:bg-slate-50 transition">Filter</button>
            @if($class_id || $course_id)
            <a href="{{ route('curriculums.index') }}" class="px-4 py-2 bg-white border border-slate-200 text-slate-500 rounded-lg text-sm hover:bg-slate-50 transition">Clear</a>
            @endif
        </form>

        <div class="space-y-3">
            @forelse($curriculums as $cur)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 hover:shadow-md transition">
                <div class="flex flex-wrap justify-between items-start gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <h3 class="font-semibold text-slate-800">{{ $cur->title }}</h3>
                            <span class="text-[11px] px-2 py-0.5 rounded-full font-medium
                                @if($cur->status === 'published') bg-emerald-100 text-emerald-700
                                @elseif($cur->status === 'archived') bg-slate-100 text-slate-500
                                @else bg-amber-100 text-amber-700 @endif">
                                {{ ucfirst($cur->status) }}
                            </span>
                        </div>
                        <div class="flex flex-wrap gap-4 text-xs text-slate-500">
                            <span><i class="bi bi-layers me-1"></i>{{ $cur->schoolClass->class_name ?? '—' }}</span>
                            <span><i class="bi bi-book me-1"></i>{{ $cur->course->course_name ?? '—' }}</span>
                            @if($cur->program)
                            <span><i class="bi bi-mortarboard me-1"></i>{{ $cur->program->name }}</span>
                            @endif
                            <span><i class="bi bi-list-ol me-1"></i>{{ $cur->topics_count }} topics</span>
                        </div>
                        @if($cur->description)
                        <p class="mt-1.5 text-xs text-slate-400 line-clamp-2">{{ $cur->description }}</p>
                        @endif
                    </div>
                    <div class="flex gap-2 flex-shrink-0">
                        <a href="{{ route('curriculums.show', $cur->id) }}"
                           class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="View">
                            <i class="bi bi-eye text-sm"></i>
                        </a>
                        @can('view academic settings')
                        <form action="{{ route('curriculums.destroy', $cur->id) }}" method="POST" onsubmit="return confirm('Delete this curriculum?')">
                            @csrf @method('DELETE')
                            <button class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition">
                                <i class="bi bi-trash text-sm"></i>
                            </button>
                        </form>
                        @endcan
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-12 text-center">
                <i class="bi bi-journal-richtext text-5xl text-slate-200"></i>
                <p class="mt-3 text-slate-400">No curriculums found.</p>
                @can('view academic settings')
                <a href="{{ route('curriculums.create') }}" class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">
                    <i class="bi bi-plus-lg"></i> Create First Curriculum
                </a>
                @endcan
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
