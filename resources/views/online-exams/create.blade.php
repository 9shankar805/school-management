@extends('layouts.app')
@section('content')
<div class="flex-1 p-6 lg:p-8 overflow-auto">

    <nav class="text-xs text-slate-400 mb-4">
        <a href="{{ route('online-exams.index') }}" class="hover:text-indigo-600">Online Exams</a>
        <span class="mx-1">/</span>
        <span class="text-slate-600">Create</span>
    </nav>

    <h1 class="text-2xl font-bold text-slate-800 mb-6">Create Online Exam</h1>

    @include('session-messages')

    <div class="max-w-3xl bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <form method="POST" action="{{ route('online-exams.store') }}" class="space-y-5">
            @csrf

            {{-- Mode toggle — Practice vs Formal --}}
            <div class="flex gap-3 p-1 bg-slate-100 rounded-xl w-fit mb-2">
                <label class="flex items-center gap-2 px-4 py-2 rounded-lg cursor-pointer has-[:checked]:bg-white has-[:checked]:shadow has-[:checked]:text-indigo-700 transition text-sm font-medium text-slate-600">
                    <input type="radio" name="is_practice" value="0" class="hidden" id="mode_formal" checked>
                    <i class="bi bi-file-earmark-text-fill"></i> Formal Exam
                </label>
                <label class="flex items-center gap-2 px-4 py-2 rounded-lg cursor-pointer has-[:checked]:bg-white has-[:checked]:shadow has-[:checked]:text-emerald-700 transition text-sm font-medium text-slate-600">
                    <input type="radio" name="is_practice" value="1" class="hidden" id="mode_practice">
                    <i class="bi bi-lightning-fill"></i> Practice Quiz
                </label>
            </div>

            {{-- Practice info banner (hidden by default) --}}
            <div id="practice_banner" class="hidden bg-emerald-50 border border-emerald-200 rounded-xl p-3 text-xs text-emerald-800 flex items-start gap-2">
                <i class="bi bi-lightning-fill text-emerald-500 mt-0.5"></i>
                <div>
                    <p class="font-semibold">Practice Quiz mode</p>
                    <p>No marks are recorded. Students get unlimited attempts and see results instantly. Great for revision and self-assessment.</p>
                </div>
            </div>

            <div>
                <label class="text-xs font-medium text-slate-600 block mb-1">Title <span class="text-rose-400">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                       placeholder="e.g. Chapter 3 Practice Quiz">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">Course</label>
                    <select name="course_id" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="">— All Courses —</option>
                        @foreach($courses as $c)
                        <option value="{{ $c->id }}" @selected(old('course_id') == $c->id)>{{ $c->course_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">Class</label>
                    <select name="class_id" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="">— All Classes —</option>
                        @foreach($classes as $cl)
                        <option value="{{ $cl->id }}" @selected(old('class_id') == $cl->id)>{{ $cl->class_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="formal_exam_link">
                    <label class="text-xs font-medium text-slate-600 block mb-1">Link to Exam (optional)</label>
                    <select name="exam_id" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="">— None —</option>
                        @foreach($exams as $e)
                        <option value="{{ $e->id }}" @selected(old('exam_id') == $e->id)>{{ $e->exam_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">Duration (min) <span class="text-rose-400">*</span></label>
                    <input type="number" name="duration_minutes" value="{{ old('duration_minutes', 30) }}" min="5" max="300" required
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">Total Marks <span class="text-rose-400">*</span></label>
                    <input type="number" name="total_marks" value="{{ old('total_marks', 20) }}" min="1" required
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <div id="pass_marks_field">
                    <label class="text-xs font-medium text-slate-600 block mb-1">Pass Marks <span class="text-rose-400">*</span></label>
                    <input type="number" name="pass_marks" value="{{ old('pass_marks', 8) }}" min="0" required
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <div id="max_attempts_field">
                    <label class="text-xs font-medium text-slate-600 block mb-1">Max Attempts</label>
                    <input type="number" name="max_attempts" value="{{ old('max_attempts', 1) }}" min="1" max="10"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" id="availability_fields">
                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">Available From</label>
                    <input type="datetime-local" name="available_from" value="{{ old('available_from') }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">Available To</label>
                    <input type="datetime-local" name="available_to" value="{{ old('available_to') }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
            </div>

            <div>
                <label class="text-xs font-medium text-slate-600 block mb-1">Instructions</label>
                <textarea name="instructions" rows="3" placeholder="Instructions shown to students before they start…"
                          class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ old('instructions') }}</textarea>
            </div>

            <div class="flex flex-wrap gap-6">
                <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer">
                    <input type="hidden" name="shuffle_questions" value="0">
                    <input type="checkbox" name="shuffle_questions" value="1" @checked(old('shuffle_questions'))
                           class="w-4 h-4 rounded accent-indigo-600">
                    Shuffle questions per attempt
                </label>
                <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer" id="show_result_toggle">
                    <input type="hidden" name="show_result_immediately" value="0">
                    <input type="checkbox" name="show_result_immediately" value="1" id="show_result_cb" @checked(old('show_result_immediately'))
                           class="w-4 h-4 rounded accent-indigo-600">
                    Show result immediately after submission
                </label>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition">
                    <i class="bi bi-check2 me-1"></i> Create & Add Questions
                </button>
                <a href="{{ route('online-exams.index') }}"
                   class="px-6 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl text-sm font-medium hover:bg-slate-50 transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
</div>

<script>
const formalRadio   = document.getElementById('mode_formal');
const practiceRadio = document.getElementById('mode_practice');

function applyMode() {
    const isPractice = practiceRadio.checked;
    document.getElementById('practice_banner').classList.toggle('hidden', !isPractice);
    document.getElementById('formal_exam_link').classList.toggle('hidden', isPractice);
    document.getElementById('pass_marks_field').classList.toggle('opacity-40', isPractice);
    document.getElementById('max_attempts_field').classList.toggle('opacity-40', isPractice);
    // Force show_result_immediately on for practice
    if (isPractice) {
        document.getElementById('show_result_cb').checked = true;
    }
}

formalRadio.addEventListener('change', applyMode);
practiceRadio.addEventListener('change', applyMode);
applyMode();
</script>
@endsection
