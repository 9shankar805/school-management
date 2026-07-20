@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <nav class="text-xs text-slate-400 mb-4">
            <a href="{{ route('curriculums.index') }}" class="hover:text-indigo-600">Curriculums</a>
            <span class="mx-1">/</span>
            <span class="text-slate-600">{{ $curriculum->title }}</span>
        </nav>

        @include('session-messages')

        {{-- Header --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mb-6">
            <div class="flex flex-wrap justify-between items-start gap-4">
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <h1 class="text-xl font-bold text-slate-800">{{ $curriculum->title }}</h1>
                        <span class="text-[11px] px-2.5 py-0.5 rounded-full font-medium
                            @if($curriculum->status === 'published') bg-emerald-100 text-emerald-700
                            @elseif($curriculum->status === 'archived') bg-slate-100 text-slate-500
                            @else bg-amber-100 text-amber-700 @endif">
                            {{ ucfirst($curriculum->status) }}
                        </span>
                    </div>
                    <div class="flex flex-wrap gap-4 text-xs text-slate-500 mt-1">
                        <span><i class="bi bi-layers me-1"></i>{{ $curriculum->schoolClass->class_name ?? '—' }}</span>
                        <span><i class="bi bi-book me-1"></i>{{ $curriculum->course->course_name ?? '—' }}</span>
                        @if($curriculum->program)
                        <span><i class="bi bi-mortarboard me-1"></i>{{ $curriculum->program->name }}</span>
                        @endif
                        <span><i class="bi bi-list-ol me-1"></i>{{ $curriculum->topics->count() }} topics</span>
                        <span><i class="bi bi-clock me-1"></i>{{ $curriculum->topics->sum('estimated_hours') }}h total</span>
                    </div>
                </div>
                @can('view academic settings')
                <div class="flex gap-2 flex-shrink-0">
                    {{-- Inline status change --}}
                    <form action="{{ route('curriculums.update', $curriculum->id) }}" method="POST">
                        @csrf @method('PUT')
                        <input type="hidden" name="title" value="{{ $curriculum->title }}">
                        <input type="hidden" name="status" value="{{ $curriculum->status === 'draft' ? 'published' : 'draft' }}">
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-slate-200 text-sm font-medium rounded-lg hover:bg-slate-50 transition text-slate-700">
                            <i class="bi bi-{{ $curriculum->status === 'published' ? 'eye-slash' : 'check2-circle' }}"></i>
                            {{ $curriculum->status === 'published' ? 'Unpublish' : 'Publish' }}
                        </button>
                    </form>
                    <form action="{{ route('curriculums.destroy', $curriculum->id) }}" method="POST" onsubmit="return confirm('Delete this curriculum?')">
                        @csrf @method('DELETE')
                        <button class="inline-flex items-center gap-1.5 px-3 py-2 bg-rose-50 border border-rose-200 text-sm font-medium rounded-lg hover:bg-rose-100 transition text-rose-700">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </form>
                </div>
                @endcan
            </div>

            @if($curriculum->description)
            <p class="mt-3 text-sm text-slate-600">{{ $curriculum->description }}</p>
            @endif

            @if($curriculum->objectives || $curriculum->learning_outcomes)
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                @if($curriculum->objectives)
                <div class="bg-blue-50 rounded-xl p-4">
                    <h3 class="text-xs font-semibold text-blue-700 mb-1">Learning Objectives</h3>
                    <p class="text-xs text-slate-600 whitespace-pre-line">{{ $curriculum->objectives }}</p>
                </div>
                @endif
                @if($curriculum->learning_outcomes)
                <div class="bg-emerald-50 rounded-xl p-4">
                    <h3 class="text-xs font-semibold text-emerald-700 mb-1">Learning Outcomes</h3>
                    <p class="text-xs text-slate-600 whitespace-pre-line">{{ $curriculum->learning_outcomes }}</p>
                </div>
                @endif
            </div>
            @endif
        </div>

        {{-- Topics --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2">
                <h2 class="text-base font-semibold text-slate-700 mb-3">Topics &amp; Chapters</h2>
                @if($curriculum->topics->isEmpty())
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-8 text-center">
                    <i class="bi bi-list-ol text-4xl text-slate-200"></i>
                    <p class="mt-2 text-slate-400 text-sm">No topics yet.</p>
                </div>
                @else
                <div class="space-y-2">
                    @foreach($curriculum->topics as $topic)
                    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-4 flex gap-4 items-start">
                        <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <span class="text-xs font-bold text-indigo-600">{{ $loop->iteration }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h4 class="font-medium text-slate-800 text-sm">{{ $topic->title }}</h4>
                                @if($topic->term)
                                <span class="text-[10px] bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded-full">{{ $topic->term->name }}</span>
                                @endif
                                <span class="text-[10px] text-slate-400"><i class="bi bi-clock me-0.5"></i>{{ $topic->estimated_hours }}h</span>
                            </div>
                            @if($topic->description)
                            <p class="text-xs text-slate-500 mt-1">{{ $topic->description }}</p>
                            @endif
                        </div>
                        @can('view academic settings')
                        <form action="{{ route('curriculums.topics.destroy', $topic->id) }}" method="POST" onsubmit="return confirm('Remove topic?')">
                            @csrf @method('DELETE')
                            <button class="p-1 text-slate-300 hover:text-rose-500 transition flex-shrink-0">
                                <i class="bi bi-x text-sm"></i>
                            </button>
                        </form>
                        @endcan
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Add topic form --}}
            @can('view academic settings')
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-plus-circle me-1 text-indigo-500"></i>Add Topic</h2>
                <form action="{{ route('curriculums.topics.store', $curriculum->id) }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Title <span class="text-rose-400">*</span></label>
                        <input type="text" name="title" required
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="Topic title">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Term</label>
                        <select name="term_id" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="">— No term —</option>
                            @foreach($terms as $term)
                            <option value="{{ $term->id }}">{{ $term->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Estimated Hours</label>
                        <input type="number" name="estimated_hours" value="1" min="1"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Description</label>
                        <textarea name="description" rows="2"
                                  class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="Optional…"></textarea>
                    </div>
                    <button type="submit" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">Add Topic</button>
                </form>
            </div>
            @endcan
        </div>
    </div>
</div>
@endsection
