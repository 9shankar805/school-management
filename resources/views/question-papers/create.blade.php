@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <nav class="text-xs text-slate-400 mb-4">
            <a href="{{ route('question-papers.index') }}" class="hover:text-indigo-600">Question Papers</a>
            <span class="mx-1">/</span>
            <span class="text-slate-600">New Paper</span>
        </nav>

        <h1 class="text-2xl font-bold text-slate-800 mb-6"><i class="bi bi-file-earmark-plus me-2 text-indigo-500"></i>Create Question Paper</h1>

        @include('session-messages')

        <div class="max-w-3xl">
            <form method="POST" action="{{ route('question-papers.store') }}"
                  class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
                @csrf

                {{-- Template picker --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Template</label>
                    <select name="template_id" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="">— No template (blank paper) —</option>
                        @foreach($templates as $tpl)
                        <option value="{{ $tpl->id }}" {{ old('template_id') == $tpl->id ? 'selected' : '' }}>
                            {{ $tpl->name }}
                            ({{ strtoupper($tpl->paper_size) }} · {{ ucfirst($tpl->orientation) }})
                        </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-slate-400 mt-1">
                        Templates pre-fill header, footer, and school details.
                        <a href="{{ route('question-paper-templates.create') }}" class="text-indigo-500 hover:underline">Create template</a>
                    </p>
                </div>

                <hr class="border-slate-100">

                {{-- Paper identity --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Paper Title <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                           placeholder="e.g. First Terminal Examination — Mathematics Class 10">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Exam</label>
                        <select name="exam_id" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="">— Link to exam (optional) —</option>
                            @foreach($exams as $exam)
                            <option value="{{ $exam->id }}" {{ old('exam_id') == $exam->id ? 'selected' : '' }}>
                                {{ $exam->exam_name }}
                                @if($exam->course) · {{ $exam->course->course_name }}@endif
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Subject / Course</label>
                        <select name="course_id" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="">— Select course —</option>
                            @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                {{ $course->course_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Class</label>
                        <select name="class_id" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="">— Select class —</option>
                            @foreach($classes as $cls)
                            <option value="{{ $cls->id }}" {{ old('class_id') == $cls->id ? 'selected' : '' }}>{{ $cls->class_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Semester</label>
                        <select name="semester_id" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="">— Select semester —</option>
                            @foreach($semesters as $sem)
                            <option value="{{ $sem->id }}" {{ old('semester_id') == $sem->id ? 'selected' : '' }}>{{ $sem->semester_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Exam Date</label>
                        <input type="date" name="exam_date" value="{{ old('exam_date') }}"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                </div>

                <hr class="border-slate-100">

                {{-- Paper header fields (placeholders) --}}
                <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Paper Header Info</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Exam Name (on paper)</label>
                        <input type="text" name="exam_name" value="{{ old('exam_name') }}"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                               placeholder="First Terminal Examination">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Subject (on paper)</label>
                        <input type="text" name="subject" value="{{ old('subject') }}"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                               placeholder="Mathematics">
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Class Label</label>
                        <input type="text" name="class_label" value="{{ old('class_label') }}"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                               placeholder="Class 10">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Duration</label>
                        <input type="text" name="duration" value="{{ old('duration') }}"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                               placeholder="3 Hours">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Full Marks</label>
                        <input type="number" name="full_marks" value="{{ old('full_marks', 100) }}" min="0" step="0.5"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Pass Marks</label>
                        <input type="number" name="pass_marks" value="{{ old('pass_marks', 40) }}" min="0" step="0.5"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                </div>

                <hr class="border-slate-100">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Paper Size</label>
                        <select name="paper_size" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="A4" {{ old('paper_size', 'A4') === 'A4' ? 'selected' : '' }}>A4</option>
                            <option value="Letter" {{ old('paper_size') === 'Letter' ? 'selected' : '' }}>Letter</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Orientation</label>
                        <select name="orientation" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="portrait" {{ old('orientation', 'portrait') === 'portrait' ? 'selected' : '' }}>Portrait</option>
                            <option value="landscape" {{ old('orientation') === 'landscape' ? 'selected' : '' }}>Landscape</option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition">
                        <i class="bi bi-pencil-square me-1"></i> Create &amp; Open Editor
                    </button>
                    <a href="{{ route('question-papers.index') }}" class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-medium transition">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
