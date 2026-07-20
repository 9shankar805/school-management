@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <nav class="text-xs text-slate-400 mb-4">
            <a href="{{ route('lesson-plans.index') }}" class="hover:text-indigo-600">Lesson Plans</a>
            <span class="mx-1">/</span>
            <span class="text-slate-600">New Plan</span>
        </nav>

        <h1 class="text-2xl font-bold text-slate-800 mb-6">New Lesson Plan</h1>

        @include('session-messages')

        <form action="{{ route('lesson-plans.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

                {{-- Main fields --}}
                <div class="xl:col-span-2 space-y-5">
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
                        <h2 class="text-sm font-semibold text-slate-600 uppercase tracking-wide">Plan Details</h2>

                        <div>
                            <label class="text-xs font-medium text-slate-600 block mb-1">Title <span class="text-rose-400">*</span></label>
                            <input type="text" name="title" value="{{ old('title') }}" required
                                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="e.g. Introduction to Algebra">
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div class="sm:col-span-2">
                                <label class="text-xs font-medium text-slate-600 block mb-1">Class <span class="text-rose-400">*</span></label>
                                <select name="class_id" required onchange="loadSectionsCourses(this.value)"
                                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                    <option value="">Select class…</option>
                                    @foreach($classes as $cls)
                                    <option value="{{ $cls->id }}" {{ old('class_id') == $cls->id ? 'selected' : '' }}>{{ $cls->class_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-slate-600 block mb-1">Section</label>
                                <select name="section_id" id="section_id"
                                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                    <option value="">— Any —</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-slate-600 block mb-1">Course <span class="text-rose-400">*</span></label>
                                <select name="course_id" id="course_id" required
                                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                    <option value="">Select class first…</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div>
                                <label class="text-xs font-medium text-slate-600 block mb-1">Date <span class="text-rose-400">*</span></label>
                                <input type="date" name="planned_date" value="{{ old('planned_date', date('Y-m-d')) }}" required
                                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            </div>
                            <div>
                                <label class="text-xs font-medium text-slate-600 block mb-1">Duration (min)</label>
                                <input type="number" name="duration_minutes" value="{{ old('duration_minutes', 45) }}" min="1" max="480" required
                                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            </div>
                            <div>
                                <label class="text-xs font-medium text-slate-600 block mb-1">Status</label>
                                <select name="status" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                    <option value="draft">Draft</option>
                                    <option value="approved">Approved</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-slate-600 block mb-1">Term</label>
                                <select name="term_id" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                    <option value="">— None —</option>
                                    @foreach($terms as $term)
                                    <option value="{{ $term->id }}" {{ old('term_id') == $term->id ? 'selected' : '' }}>{{ $term->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-slate-600 block mb-1">Objectives</label>
                            <textarea name="objectives" rows="2"
                                      class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="What students will learn…">{{ old('objectives') }}</textarea>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-slate-600 block mb-1">Lesson Content</label>
                            <textarea name="content" rows="4"
                                      class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="Main content, activities, explanations…">{{ old('content') }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-medium text-slate-600 block mb-1">Teaching Methods</label>
                                <textarea name="teaching_methods" rows="2"
                                          class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="Lecture, group work, demonstration…">{{ old('teaching_methods') }}</textarea>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-slate-600 block mb-1">Resources / Materials</label>
                                <textarea name="resources" rows="2"
                                          class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="Textbooks, videos, worksheets…">{{ old('resources') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-5">
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 space-y-3">
                        <h2 class="text-sm font-semibold text-slate-600 uppercase tracking-wide">Homework Note</h2>
                        <textarea name="homework_description" rows="3"
                                  class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="Homework or assignment for students…">{{ old('homework_description') }}</textarea>
                    </div>
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 space-y-3">
                        <h2 class="text-sm font-semibold text-slate-600 uppercase tracking-wide">Teacher Notes</h2>
                        <textarea name="notes" rows="3"
                                  class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="Private notes for next class…">{{ old('notes') }}</textarea>
                    </div>
                    <div class="flex flex-col gap-2">
                        <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition">
                            <i class="bi bi-check2 me-1"></i> Save Plan
                        </button>
                        <a href="{{ route('lesson-plans.index') }}" class="w-full py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl text-sm font-medium hover:bg-slate-50 transition text-center">Cancel</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
function loadSectionsCourses(classId) {
    if (!classId) return;
    fetch(`/sections?class_id=${classId}`)
        .then(r => r.json())
        .then(data => {
            const sec = document.getElementById('section_id');
            sec.innerHTML = '<option value="">— Any —</option>';
            (data.sections || []).forEach(s => sec.innerHTML += `<option value="${s.id}">${s.section_name}</option>`);

            const crs = document.getElementById('course_id');
            crs.innerHTML = '<option value="">Select course…</option>';
            (data.courses || []).forEach(c => crs.innerHTML += `<option value="${c.id}">${c.course_name}</option>`);
        });
}
</script>
@endsection
