@extends('layouts.app')

@section('content')
<div class="px-4 lg:px-8 py-6 max-w-5xl mx-auto">

    {{-- Header --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <span class="w-10 h-10 rounded-2xl bg-indigo-600/10 text-indigo-600 flex items-center justify-center font-bold text-xl shadow-xs">
                    <i class="bi bi-pencil-square"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Edit Online Exam: {{ $exam->title }}</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Modify exam settings, instructions, duration, and availability window</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('online-exams.questions', $exam->id) }}" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs">
                <i class="bi bi-question-circle"></i> Manage Questions
            </a>
            <a href="{{ route('online-exams.index') }}" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 text-xs font-semibold transition">
                Back
            </a>
        </div>
    </div>

    @include('session-messages')

    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6 md:p-8">
        <form action="{{ route('online-exams.update', $exam->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label for="title" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Exam Title <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="title" name="title" required value="{{ old('title', $exam->title) }}"
                           class="w-full text-sm px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none">
                </div>

                <div>
                    <label for="class_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Target Class</label>
                    <select name="class_id" id="class_id" class="w-full text-xs px-3.5 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100">
                        <option value="">All Classes</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ $exam->class_id == $c->id ? 'selected' : '' }}>{{ $c->class_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="course_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Subject / Course</label>
                    <select name="course_id" id="course_id" class="w-full text-xs px-3.5 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100">
                        <option value="">All Subjects</option>
                        @foreach($courses as $cr)
                            <option value="{{ $cr->id }}" {{ $exam->course_id == $cr->id ? 'selected' : '' }}>{{ $cr->course_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="duration_minutes" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Duration (Minutes) <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" id="duration_minutes" name="duration_minutes" required value="{{ old('duration_minutes', $exam->duration_minutes) }}" min="5" max="300"
                           class="w-full text-xs px-3.5 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none">
                </div>

                <div>
                    <label for="total_marks" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Total Marks <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" id="total_marks" name="total_marks" required value="{{ old('total_marks', $exam->total_marks) }}" min="1"
                           class="w-full text-xs px-3.5 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none">
                </div>

                <div>
                    <label for="pass_marks" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Pass Marks <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" id="pass_marks" name="pass_marks" required value="{{ old('pass_marks', $exam->pass_marks) }}" min="0"
                           class="w-full text-xs px-3.5 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none">
                </div>

                <div>
                    <label for="max_attempts" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Max Allowed Attempts <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" id="max_attempts" name="max_attempts" required value="{{ old('max_attempts', $exam->max_attempts ?? 1) }}" min="1" max="10"
                           class="w-full text-xs px-3.5 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none">
                </div>

                <div>
                    <label for="available_from" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Available From</label>
                    <input type="datetime-local" id="available_from" name="available_from"
                           value="{{ $exam->available_from ? \Carbon\Carbon::parse($exam->available_from)->format('Y-m-d\TH:i') : '' }}"
                           class="w-full text-xs px-3.5 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none">
                </div>

                <div>
                    <label for="available_to" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Available To</label>
                    <input type="datetime-local" id="available_to" name="available_to"
                           value="{{ $exam->available_to ? \Carbon\Carbon::parse($exam->available_to)->format('Y-m-d\TH:i') : '' }}"
                           class="w-full text-xs px-3.5 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none">
                </div>

                <div class="md:col-span-2">
                    <label for="instructions" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Exam Instructions</label>
                    <textarea id="instructions" name="instructions" rows="4"
                              class="w-full text-xs px-3.5 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none">{{ old('instructions', $exam->instructions) }}</textarea>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <a href="{{ route('online-exams.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 text-xs font-semibold transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md transition flex items-center gap-2">
                    <i class="bi bi-check2"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
