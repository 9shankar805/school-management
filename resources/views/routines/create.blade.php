@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <nav class="text-xs text-slate-400 mb-4">
            <a href="{{ route('routine.index') }}" class="hover:text-indigo-600">Timetable</a>
            <span class="mx-1">/</span>
            <span class="text-slate-600">Add Slot</span>
        </nav>

        <h1 class="text-2xl font-bold text-slate-800 mb-6">Add Timetable Slot</h1>

        @include('session-messages')

        {{-- Conflict alert --}}
        @if($errors->has('conflict'))
        <div class="mb-5 p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-sm flex items-start gap-2">
            <i class="bi bi-exclamation-triangle-fill mt-0.5"></i>
            <div>
                <p class="font-semibold">Scheduling Conflict Detected</p>
                <p>{{ $errors->first('conflict') }}</p>
            </div>
        </div>
        @endif

        <div class="max-w-2xl bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <form action="{{ route('section.routine.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="session_id" value="{{ $current_school_session_id }}">

                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">Class <span class="text-rose-400">*</span></label>
                    <select name="class_id" required onchange="loadSectionsAndCourses(this.value)"
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
                        <select name="section_id" id="section-select" required
                                class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="">Select class first…</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Course <span class="text-rose-400">*</span></label>
                        <select name="course_id" id="course-select" required
                                class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="">Select class first…</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">Day of Week <span class="text-rose-400">*</span></label>
                    <select name="weekday" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        @foreach([1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday',7=>'Sunday'] as $num => $name)
                        <option value="{{ $num }}" {{ old('weekday') == $num ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Start Time <span class="text-rose-400">*</span></label>
                        <input type="time" name="start" value="{{ old('start') }}" required
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">End Time <span class="text-rose-400">*</span></label>
                        <input type="time" name="end" value="{{ old('end') }}" required
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                </div>

                {{-- Teacher (optional) --}}
                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">Assigned Teacher</label>
                    <select name="teacher_id" id="teacher-select"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="">— None / Auto from course —</option>
                        @foreach(\App\Models\User::whereHas('roles', fn($q)=>$q->whereIn('name',['teacher','class-teacher']))->orderBy('first_name')->get() as $t)
                        <option value="{{ $t->id }}" {{ old('teacher_id') == $t->id ? 'selected' : '' }}>{{ $t->full_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Room / Venue</label>
                        <input type="text" name="room" value="{{ old('room') }}" placeholder="e.g. Room 101"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Slot Color</label>
                        <div class="flex items-center gap-3">
                            <input type="color" name="color" value="{{ old('color', '#6366f1') }}"
                                   class="h-9 w-14 rounded-lg border border-slate-200 cursor-pointer p-1">
                            <span class="text-xs text-slate-400">Timetable color</span>
                        </div>
                    </div>
                </div>

                {{-- Conflict info banner --}}
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs text-amber-700 flex items-start gap-2">
                    <i class="bi bi-shield-check mt-0.5"></i>
                    <p>Conflict detection is active. The system will block the slot if this section or teacher is already scheduled at the same time on the selected day.</p>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition">
                        <i class="bi bi-check2 me-1"></i> Save Slot
                    </button>
                    <a href="{{ route('section.routine.show') }}" class="px-6 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl text-sm font-medium hover:bg-slate-50 transition">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function loadSectionsAndCourses(classId) {
    if (!classId) return;
    fetch(`/sections?class_id=${classId}`)
        .then(r => r.json())
        .then(data => {
            const sec = document.getElementById('section-select');
            sec.innerHTML = '<option value="">Select section…</option>';
            (data.sections || []).forEach(s => sec.innerHTML += `<option value="${s.id}">${s.section_name}</option>`);

            const crs = document.getElementById('course-select');
            crs.innerHTML = '<option value="">Select course…</option>';
            (data.courses || []).forEach(c => crs.innerHTML += `<option value="${c.id}">${c.course_name}</option>`);
        });
}

// Re-populate if old input present (after validation fail)
@if(old('class_id'))
loadSectionsAndCourses({{ old('class_id') }});
@endif
</script>
@endsection
