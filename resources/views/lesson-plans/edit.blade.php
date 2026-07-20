@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <nav class="text-xs text-slate-400 mb-4">
            <a href="{{ route('lesson-plans.index') }}" class="hover:text-indigo-600">Lesson Plans</a>
            <span class="mx-1">/</span>
            <a href="{{ route('lesson-plans.show', $lessonPlan->id) }}" class="hover:text-indigo-600">{{ Str::limit($lessonPlan->title, 40) }}</a>
            <span class="mx-1">/</span>
            <span class="text-slate-600">Edit</span>
        </nav>

        <h1 class="text-2xl font-bold text-slate-800 mb-6">Edit Lesson Plan</h1>

        @include('session-messages')

        <form action="{{ route('lesson-plans.update', $lessonPlan->id) }}" method="POST">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

                {{-- Main fields --}}
                <div class="xl:col-span-2 space-y-5">
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
                        <h2 class="text-sm font-semibold text-slate-600 uppercase tracking-wide">Plan Details</h2>

                        <div>
                            <label class="text-xs font-medium text-slate-600 block mb-1">Title <span class="text-rose-400">*</span></label>
                            <input type="text" name="title" value="{{ old('title', $lessonPlan->title) }}" required
                                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div>
                                <label class="text-xs font-medium text-slate-600 block mb-1">Date <span class="text-rose-400">*</span></label>
                                <input type="date" name="planned_date" value="{{ old('planned_date', $lessonPlan->planned_date->format('Y-m-d')) }}" required
                                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            </div>
                            <div>
                                <label class="text-xs font-medium text-slate-600 block mb-1">Duration (min)</label>
                                <input type="number" name="duration_minutes" value="{{ old('duration_minutes', $lessonPlan->duration_minutes) }}" min="1" max="480" required
                                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            </div>
                            <div>
                                <label class="text-xs font-medium text-slate-600 block mb-1">Status</label>
                                <select name="status" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                    @foreach(['draft' => 'Draft', 'approved' => 'Approved', 'completed' => 'Completed'] as $val => $label)
                                    <option value="{{ $val }}" {{ old('status', $lessonPlan->status) === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-slate-600 block mb-1">Term</label>
                                <select name="term_id" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                    <option value="">— None —</option>
                                    @foreach($terms as $term)
                                    <option value="{{ $term->id }}" {{ old('term_id', $lessonPlan->term_id) == $term->id ? 'selected' : '' }}>{{ $term->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        @if($topics->count())
                        <div>
                            <label class="text-xs font-medium text-slate-600 block mb-1">Curriculum Topic</label>
                            <select name="curriculum_topic_id" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                <option value="">— None —</option>
                                @foreach($topics as $topic)
                                <option value="{{ $topic->id }}" {{ old('curriculum_topic_id', $lessonPlan->curriculum_topic_id) == $topic->id ? 'selected' : '' }}>
                                    #{{ $topic->order }} — {{ $topic->title }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div>
                            <label class="text-xs font-medium text-slate-600 block mb-1">Objectives</label>
                            <textarea name="objectives" rows="2"
                                      class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ old('objectives', $lessonPlan->objectives) }}</textarea>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-slate-600 block mb-1">Lesson Content</label>
                            <textarea name="content" rows="5"
                                      class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ old('content', $lessonPlan->content) }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-medium text-slate-600 block mb-1">Teaching Methods</label>
                                <textarea name="teaching_methods" rows="2"
                                          class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ old('teaching_methods', $lessonPlan->teaching_methods) }}</textarea>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-slate-600 block mb-1">Resources / Materials</label>
                                <textarea name="resources" rows="2"
                                          class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ old('resources', $lessonPlan->resources) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-5">
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 space-y-3">
                        <h2 class="text-sm font-semibold text-slate-600 uppercase tracking-wide">Homework Note</h2>
                        <textarea name="homework_description" rows="3"
                                  class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ old('homework_description', $lessonPlan->homework_description) }}</textarea>
                    </div>
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 space-y-3">
                        <h2 class="text-sm font-semibold text-slate-600 uppercase tracking-wide">Private Notes</h2>
                        <textarea name="notes" rows="3"
                                  class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ old('notes', $lessonPlan->notes) }}</textarea>
                    </div>
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-xs text-slate-500 space-y-1">
                        <p><i class="bi bi-book me-1"></i><strong>Course:</strong> {{ $lessonPlan->course->course_name ?? '—' }}</p>
                        <p><i class="bi bi-layers me-1"></i><strong>Class:</strong> {{ $lessonPlan->schoolClass->class_name ?? '—' }}</p>
                        @if($lessonPlan->section)<p><i class="bi bi-grid me-1"></i><strong>Section:</strong> {{ $lessonPlan->section->section_name }}</p>@endif
                    </div>
                    <div class="flex flex-col gap-2">
                        <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition">
                            <i class="bi bi-check2 me-1"></i> Save Changes
                        </button>
                        <a href="{{ route('lesson-plans.show', $lessonPlan->id) }}" class="w-full py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl text-sm font-medium hover:bg-slate-50 transition text-center">Cancel</a>
                        <form action="{{ route('lesson-plans.destroy', $lessonPlan->id) }}" method="POST" onsubmit="return confirm('Delete this lesson plan?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-full py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-xl text-sm font-medium transition">
                                <i class="bi bi-trash me-1"></i> Delete Plan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
