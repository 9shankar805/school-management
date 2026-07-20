@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <nav class="text-xs text-slate-400 mb-4">
            <a href="{{ route('curriculums.index') }}" class="hover:text-indigo-600">Curriculums</a>
            <span class="mx-1">/</span>
            <span class="text-slate-600">New Curriculum</span>
        </nav>

        <h1 class="text-2xl font-bold text-slate-800 mb-6">New Curriculum</h1>

        @include('session-messages')

        <form action="{{ route('curriculums.store') }}" method="POST" class="space-y-6">
            @csrf
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
                <h2 class="text-sm font-semibold text-slate-600 uppercase tracking-wide">Basic Info</h2>

                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">Title <span class="text-rose-400">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="e.g. Mathematics Grade 10 Curriculum">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Class <span class="text-rose-400">*</span></label>
                        <select name="class_id" id="class_id" required onchange="loadCourses(this.value)"
                                class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="">Select class…</option>
                            @foreach($classes as $cls)
                            <option value="{{ $cls->id }}" {{ old('class_id') == $cls->id ? 'selected' : '' }}>{{ $cls->class_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Course <span class="text-rose-400">*</span></label>
                        <select name="course_id" id="course_id" required
                                class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="">Select class first…</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Program</label>
                        <select name="program_id" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="">— None —</option>
                            @foreach($programs as $prog)
                            <option value="{{ $prog->id }}" {{ old('program_id') == $prog->id ? 'selected' : '' }}>{{ $prog->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">Status</label>
                    <select name="status" class="w-full sm:w-48 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published</option>
                        <option value="archived" {{ old('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                </div>

                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">Description</label>
                    <textarea name="description" rows="2"
                              class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="Brief overview…">{{ old('description') }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Learning Objectives</label>
                        <textarea name="objectives" rows="3"
                                  class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="What students will learn…">{{ old('objectives') }}</textarea>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Learning Outcomes</label>
                        <textarea name="learning_outcomes" rows="3"
                                  class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="What students will be able to do…">{{ old('learning_outcomes') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Topics --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-sm font-semibold text-slate-600 uppercase tracking-wide">Topics / Chapters</h2>
                    <button type="button" onclick="addTopic()"
                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-lg text-xs font-medium hover:bg-indigo-100 transition">
                        <i class="bi bi-plus-lg"></i> Add Topic
                    </button>
                </div>
                <div id="topics-container" class="space-y-3">
                    <p class="text-xs text-slate-400 text-center py-4" id="topics-empty">No topics yet — click "Add Topic" to begin.</p>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">
                    <i class="bi bi-check2 me-1"></i>Create Curriculum
                </button>
                <a href="{{ route('curriculums.index') }}" class="px-6 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition">Cancel</a>
            </div>
        </form>
    </div>
</div>

<template id="topic-template">
    <div class="topic-row border border-slate-200 rounded-xl p-4 bg-slate-50 space-y-3">
        <div class="flex justify-between items-center">
            <span class="text-xs font-semibold text-slate-600 topic-label">Topic #1</span>
            <button type="button" onclick="removeTopic(this)" class="text-slate-400 hover:text-rose-500 transition">
                <i class="bi bi-x-circle text-sm"></i>
            </button>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="sm:col-span-2">
                <input type="text" name="topics[0][title]" placeholder="Topic title *" required
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
            </div>
            <div>
                <input type="number" name="topics[0][estimated_hours]" placeholder="Hours" min="1" value="1"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
            </div>
        </div>
        <div>
            <select name="topics[0][term_id]"
                    class="w-full sm:w-48 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                <option value="">— No term —</option>
                @foreach($terms as $term)
                <option value="{{ $term->id }}">{{ $term->name }}</option>
                @endforeach
            </select>
        </div>
        <textarea name="topics[0][description]" rows="2" placeholder="Description (optional)"
                  class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white"></textarea>
    </div>
</template>

<script>
let topicIndex = 0;

function addTopic() {
    document.getElementById('topics-empty')?.remove();
    const tpl = document.getElementById('topic-template').innerHTML;
    const idx = topicIndex++;
    const html = tpl
        .replace(/topics\[0\]/g, `topics[${idx}]`)
        .replace('Topic #1', `Topic #${idx + 1}`);
    const div = document.createElement('div');
    div.innerHTML = html;
    document.getElementById('topics-container').appendChild(div.firstElementChild);
}

function removeTopic(btn) {
    btn.closest('.topic-row').remove();
    const container = document.getElementById('topics-container');
    if (!container.querySelector('.topic-row')) {
        container.innerHTML = '<p class="text-xs text-slate-400 text-center py-4" id="topics-empty">No topics yet — click "Add Topic" to begin.</p>';
    }
}

// Load courses when class changes
function loadCourses(classId) {
    const sel = document.getElementById('course_id');
    sel.innerHTML = '<option>Loading…</option>';
    fetch(`/sections?class_id=${classId}`)
        .then(r => r.json())
        .then(data => {
            sel.innerHTML = '<option value="">Select course…</option>';
            (data.courses || []).forEach(c => {
                sel.innerHTML += `<option value="${c.id}">${c.course_name}</option>`;
            });
        });
}
</script>
@endsection
