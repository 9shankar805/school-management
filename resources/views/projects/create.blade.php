@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <nav class="text-xs text-slate-400 mb-4">
            <a href="{{ route('projects.index') }}" class="hover:text-indigo-600">Projects</a>
            <span class="mx-1">/</span>
            <span class="text-slate-600">New Project</span>
        </nav>

        <h1 class="text-2xl font-bold text-slate-800 mb-6">Create New Project</h1>

        @include('session-messages')

        <div class="max-w-2xl bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <form action="{{ route('projects.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">Title <span class="text-rose-400">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                           placeholder="e.g. Science Fair Project">
                </div>

                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">Description / Guidelines</label>
                    <textarea name="description" rows="3"
                              class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                              placeholder="Project objectives, requirements…">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">Class <span class="text-rose-400">*</span></label>
                    <select name="class_id" required onchange="loadSections(this.value)"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="">Select class…</option>
                        @foreach($classes as $cls)
                        <option value="{{ $cls->id }}" {{ old('class_id') == $cls->id ? 'selected' : '' }}>{{ $cls->class_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Section <span class="text-rose-400">*</span></label>
                        <select name="section_id" id="section_id" required
                                class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="">Select class first…</option>
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

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Start Date <span class="text-rose-400">*</span></label>
                        <input type="date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Due Date <span class="text-rose-400">*</span></label>
                        <input type="date" name="due_date" value="{{ old('due_date') }}" required
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Total Marks</label>
                        <input type="number" name="total_marks" value="{{ old('total_marks', 20) }}" min="1" max="100" required
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Type</label>
                        <select name="type" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="individual" {{ old('type') === 'individual' ? 'selected' : '' }}>Individual</option>
                            <option value="group" {{ old('type') === 'group' ? 'selected' : '' }}>Group</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">Project Brief / Guidelines (file)</label>
                    <input type="file" name="file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
                    <p class="text-[11px] text-slate-400 mt-1">PDF or Word. Max 10 MB.</p>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition">
                        <i class="bi bi-check2 me-1"></i> Create Project
                    </button>
                    <a href="{{ route('projects.index') }}" class="px-6 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl text-sm font-medium hover:bg-slate-50 transition">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
function loadSections(classId) {
    if (!classId) return;
    fetch(`/sections?class_id=${classId}`)
        .then(r => r.json())
        .then(data => {
            const sec = document.getElementById('section_id');
            sec.innerHTML = '<option value="">Select section…</option>';
            (data.sections || []).forEach(s => sec.innerHTML += `<option value="${s.id}">${s.section_name}</option>`);
            const crs = document.getElementById('course_id');
            crs.innerHTML = '<option value="">Select course…</option>';
            (data.courses || []).forEach(c => crs.innerHTML += `<option value="${c.id}">${c.course_name}</option>`);
        });
}
</script>
@endsection
