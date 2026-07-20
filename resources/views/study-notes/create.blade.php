@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <nav class="text-xs text-slate-400 mb-4">
            <a href="{{ route('study-notes.index') }}" class="hover:text-indigo-600">Study Materials</a>
            <span class="mx-1">/</span>
            <span class="text-slate-600">Upload</span>
        </nav>

        <h1 class="text-2xl font-bold text-slate-800 mb-6">Upload Study Material</h1>

        @include('session-messages')

        <div class="max-w-2xl bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <form action="{{ route('study-notes.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">Title <span class="text-rose-400">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                           placeholder="e.g. Chapter 4 Notes - Photosynthesis">
                </div>

                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">Type <span class="text-rose-400">*</span></label>
                    <select name="type" id="typeSelect" required onchange="toggleUrlField(this.value)"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="note" {{ old('type') === 'note' ? 'selected' : '' }}>Note</option>
                        <option value="handout" {{ old('type') === 'handout' ? 'selected' : '' }}>Handout</option>
                        <option value="reference" {{ old('type') === 'reference' ? 'selected' : '' }}>Reference</option>
                        <option value="video_link" {{ old('type') === 'video_link' ? 'selected' : '' }}>Video Link</option>
                    </select>
                </div>

                <div id="fileField">
                    <label class="text-xs font-medium text-slate-600 block mb-1">File</label>
                    <input type="file" name="file" accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png,.mp4"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
                    <p class="text-[11px] text-slate-400 mt-1">PDF, Word, PowerPoint, image, or video. Max 20 MB.</p>
                </div>

                <div id="urlField" class="hidden">
                    <label class="text-xs font-medium text-slate-600 block mb-1">Video / External URL <span class="text-rose-400">*</span></label>
                    <input type="url" name="external_url" value="{{ old('external_url') }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                           placeholder="https://youtube.com/watch?v=...">
                </div>

                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">Class <span class="text-rose-400">*</span></label>
                    <select name="class_id" required onchange="loadCourses(this.value)"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="">Select class…</option>
                        @foreach($classes as $cls)
                        <option value="{{ $cls->id }}" {{ old('class_id') == $cls->id ? 'selected' : '' }}>{{ $cls->class_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Course <span class="text-rose-400">*</span></label>
                        <select name="course_id" id="course_id" required
                                class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="">Select class first…</option>
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
                    <label class="text-xs font-medium text-slate-600 block mb-1">Description</label>
                    <textarea name="description" rows="2"
                              class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                              placeholder="Brief note about this material…">{{ old('description') }}</textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="is_published" name="is_published" value="1" class="rounded text-indigo-600"
                           {{ old('is_published', true) ? 'checked' : '' }}>
                    <label for="is_published" class="text-sm text-slate-600">Publish immediately (visible to students)</label>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition">
                        <i class="bi bi-upload me-1"></i> Upload Material
                    </button>
                    <a href="{{ route('study-notes.index') }}" class="px-6 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl text-sm font-medium hover:bg-slate-50 transition">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
function toggleUrlField(type) {
    const fileField = document.getElementById('fileField');
    const urlField  = document.getElementById('urlField');
    if (type === 'video_link') {
        fileField.classList.add('hidden');
        urlField.classList.remove('hidden');
    } else {
        fileField.classList.remove('hidden');
        urlField.classList.add('hidden');
    }
}

function loadCourses(classId) {
    if (!classId) return;
    fetch(`/sections?class_id=${classId}`)
        .then(r => r.json())
        .then(data => {
            const crs = document.getElementById('course_id');
            crs.innerHTML = '<option value="">Select course…</option>';
            (data.courses || []).forEach(c => crs.innerHTML += `<option value="${c.id}">${c.course_name}</option>`);
        });
}

// Init
toggleUrlField('{{ old('type', 'note') }}');
</script>
@endsection
