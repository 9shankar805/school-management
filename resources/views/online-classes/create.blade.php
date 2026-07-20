@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <nav class="text-xs text-slate-400 mb-4">
            <a href="{{ route('online-classes.index') }}" class="hover:text-indigo-600">Online Classes</a>
            <span class="mx-1">/</span>
            <span class="text-slate-600">Schedule</span>
        </nav>

        <h1 class="text-2xl font-bold text-slate-800 mb-6">Schedule Online Class</h1>

        @include('session-messages')

        <div class="max-w-2xl bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <form action="{{ route('online-classes.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">Title <span class="text-rose-400">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                           placeholder="e.g. Calculus Live Session — Week 3">
                </div>

                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">Description</label>
                    <textarea name="description" rows="2"
                              class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                              placeholder="Topics to cover, pre-reading instructions…">{{ old('description') }}</textarea>
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
                        <label class="text-xs font-medium text-slate-600 block mb-1">Section</label>
                        <select name="section_id" id="section_id"
                                class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="">All sections</option>
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

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Platform <span class="text-rose-400">*</span></label>
                        <select name="platform" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="google_meet" {{ old('platform') === 'google_meet' ? 'selected' : '' }}>🟢 Google Meet</option>
                            <option value="zoom" {{ old('platform') === 'zoom' ? 'selected' : '' }}>🔵 Zoom</option>
                            <option value="teams" {{ old('platform') === 'teams' ? 'selected' : '' }}>🟣 Microsoft Teams</option>
                            <option value="custom" {{ old('platform') === 'custom' ? 'selected' : '' }}>⚪ Custom</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Duration (min) <span class="text-rose-400">*</span></label>
                        <input type="number" name="duration_minutes" value="{{ old('duration_minutes', 60) }}" min="15" max="300" required
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                </div>

                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">Meeting URL <span class="text-rose-400">*</span></label>
                    <input type="url" name="meeting_url" value="{{ old('meeting_url') }}" required
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                           placeholder="https://meet.google.com/abc-defg-hij">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Meeting ID</label>
                        <input type="text" name="meeting_id" value="{{ old('meeting_id') }}"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                               placeholder="123 456 7890">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Password</label>
                        <input type="text" name="meeting_password" value="{{ old('meeting_password') }}"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                               placeholder="Optional">
                    </div>
                </div>

                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">Scheduled Date &amp; Time <span class="text-rose-400">*</span></label>
                    <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" required
                           min="{{ now()->format('Y-m-d\TH:i') }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition">
                        <i class="bi bi-calendar-plus me-1"></i> Schedule
                    </button>
                    <a href="{{ route('online-classes.index') }}" class="px-6 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl text-sm font-medium hover:bg-slate-50 transition">Cancel</a>
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
            sec.innerHTML = '<option value="">All sections</option>';
            (data.sections || []).forEach(s => sec.innerHTML += `<option value="${s.id}">${s.section_name}</option>`);
            const crs = document.getElementById('course_id');
            crs.innerHTML = '<option value="">Select course…</option>';
            (data.courses || []).forEach(c => crs.innerHTML += `<option value="${c.id}">${c.course_name}</option>`);
        });
}
</script>
@endsection
