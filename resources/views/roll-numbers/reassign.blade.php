@extends('layouts.app')
@section('title', 'Roll Number Reassignment by Exam Rank')

@section('breadcrumb')
<x-breadcrumb :items="[
    ['label' => 'Home',         'url' => route('home'), 'icon' => 'bi-house'],
    ['label' => 'Roll Numbers', 'url' => route('roll-numbers.index')],
    ['label' => 'Reassign by Exam Rank'],
]"/>
@endsection

@section('content')
<div class="px-4 lg:px-8 py-6 max-w-7xl mx-auto space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <span class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-purple-600 to-indigo-600 text-white flex items-center justify-center font-bold text-xl shadow-md shadow-purple-500/20">
                    <i class="bi bi-trophy"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight flex items-center gap-2">
                        Roll Number Reassignment by Exam Rank
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Re-assign roll numbers for an upcoming term (e.g., Terminal 3) based on student merit rank from a previous term (e.g., Terminal 2 results)
                    </p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold {{ $rollNumberMode === 'terminal' ? 'bg-purple-50 dark:bg-purple-950/50 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}">
                <i class="bi {{ $rollNumberMode === 'terminal' ? 'bi-check-circle-fill text-purple-600' : 'bi-info-circle text-slate-400' }}"></i>
                Mode: {{ $rollNumberMode === 'terminal' ? 'Terminal / Semester-wise' : 'Yearly' }}
            </span>
            <a href="{{ route('roll-numbers.index', ['class_id' => $classId, 'section_id' => $sectionId]) }}" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold transition flex items-center gap-1.5">
                <i class="bi bi-arrow-left"></i> Roster View
            </a>
            <a href="{{ route('school.setup', ['step' => 7]) }}" class="px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold transition flex items-center gap-1.5" title="Change Roll Number Mode Policy">
                <i class="bi bi-sliders"></i> Policy Settings
            </a>
        </div>
    </div>

    @include('session-messages')

    {{-- Configuration Form / Filter Panel --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
        <form action="{{ route('roll-numbers.reassign') }}" method="GET" id="reassignFilterForm" class="space-y-5">

            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-4 bg-purple-600 rounded-full"></span>
                    <h3 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">
                        1. Select Session, Class & Terminals
                    </h3>
                </div>
                <span class="text-[11px] text-slate-400">
                    Highest exam rank gets Roll 1, 2nd gets Roll 2, etc.
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                {{-- Session Selector --}}
                <div>
                    <label for="session_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Academic Session <span class="text-rose-500">*</span>
                    </label>
                    <select name="session_id" id="session_id" onchange="this.form.submit()" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 outline-none transition">
                        @foreach($allSessions as $s)
                            <option value="{{ $s->id }}" {{ $sessionId == $s->id ? 'selected' : '' }}>
                                {{ $s->session_name }} {{ $s->id == $sessionId ? '(Active)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Class Selector --}}
                <div>
                    <label for="class_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Class / Grade <span class="text-rose-500">*</span>
                    </label>
                    <select name="class_id" id="class_id" onchange="this.form.submit()" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 outline-none transition">
                        <option value="">-- Select Class --</option>
                        @foreach($classes as $cls)
                            <option value="{{ $cls->id }}" {{ $classId == $cls->id ? 'selected' : '' }}>
                                {{ $cls->class_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Section Selector --}}
                <div>
                    <label for="section_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Section
                    </label>
                    <select name="section_id" id="section_id" onchange="this.form.submit()" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 outline-none transition">
                        <option value="0" {{ $sectionId == 0 ? 'selected' : '' }}>All Sections (Per Section 1..N)</option>
                        @foreach($sections as $sec)
                            <option value="{{ $sec->id }}" {{ $sectionId == $sec->id ? 'selected' : '' }}>
                                Section {{ $sec->section_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Source Terminal (Exam Results) --}}
                <div>
                    <label for="source_semester_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 flex items-center justify-between">
                        <span>Source Terminal <span class="text-rose-500">*</span></span>
                        <span class="text-[10px] text-purple-600 font-bold">Basis for Rank</span>
                    </label>
                    <select name="source_semester_id" id="source_semester_id" onchange="this.form.submit()" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-purple-200 dark:border-purple-800 bg-purple-50/30 dark:bg-purple-950/20 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 outline-none transition">
                        <option value="">-- Select Source Term --</option>
                        @foreach($semesters as $sem)
                            <option value="{{ $sem->id }}" {{ $sourceSemesterId == $sem->id ? 'selected' : '' }}>
                                {{ $sem->semester_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Target Terminal (Destination) --}}
                <div>
                    <label for="target_semester_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 flex items-center justify-between">
                        <span>Target Terminal <span class="text-rose-500">*</span></span>
                        <span class="text-[10px] text-emerald-600 font-bold">New Rolls Assigned</span>
                    </label>
                    <select name="target_semester_id" id="target_semester_id" onchange="this.form.submit()" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50/30 dark:bg-emerald-950/20 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition">
                        <option value="">-- Select Target Term --</option>
                        @foreach($semesters as $sem)
                            <option value="{{ $sem->id }}" {{ $targetSemesterId == $sem->id ? 'selected' : '' }}>
                                {{ $sem->semester_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Advanced Options Collapsible / Toolbar --}}
            <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                        <i class="bi bi-gear text-purple-500"></i> Roll Number Formatting & Ranking Rules
                    </span>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold shadow-md shadow-purple-600/20 transition flex items-center gap-1.5 cursor-pointer">
                        <i class="bi bi-arrow-clockwise"></i> Recalculate Preview
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 bg-slate-50/60 dark:bg-slate-800/40 p-4 rounded-2xl border border-slate-200/60 dark:border-slate-700/60">
                    <div>
                        <label for="start_no" class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">
                            Start Roll Number
                        </label>
                        <input type="number" name="start_no" id="start_no" value="{{ $startNo }}" min="1" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none">
                    </div>

                    <div>
                        <label for="pad_length" class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">
                            Zero Padding
                        </label>
                        <select name="pad_length" id="pad_length" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none">
                            <option value="0" {{ $padLength == 0 ? 'selected' : '' }}>None (1, 2, 3...)</option>
                            <option value="2" {{ $padLength == 2 ? 'selected' : '' }}>2 Digits (01, 02, 03...)</option>
                            <option value="3" {{ $padLength == 3 ? 'selected' : '' }}>3 Digits (001, 002...)</option>
                        </select>
                    </div>

                    <div>
                        <label for="prefix" class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">
                            Optional Prefix
                        </label>
                        <input type="text" name="prefix" id="prefix" value="{{ $prefix }}" placeholder="e.g. R- (optional)" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none">
                    </div>

                    <div>
                        <label for="unranked_rule" class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">
                            Students Without Exam Marks
                        </label>
                        <select name="unranked_rule" id="unranked_rule" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none">
                            <option value="end_by_name" {{ $unrankedRule === 'end_by_name' ? 'selected' : '' }}>Place at end (sorted A-Z)</option>
                            <option value="end_by_roll" {{ $unrankedRule === 'end_by_roll' ? 'selected' : '' }}>Place at end (by old roll)</option>
                            <option value="keep_existing" {{ $unrankedRule === 'keep_existing' ? 'selected' : '' }}>Keep their old roll number</option>
                        </select>
                    </div>
                </div>

                <div class="mt-3 flex items-center justify-between text-xs">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="update_active_roster" value="1" {{ $updateActiveRoster ? 'checked' : '' }} class="rounded text-purple-600 focus:ring-purple-500">
                        <span class="text-slate-700 dark:text-slate-300 font-medium">
                            Also update active roster roll numbers in student profiles and class attendance
                        </span>
                    </label>
                </div>
            </div>

        </form>
    </div>

    {{-- Metrics / KPI Bar --}}
    @if($previewData['total_students'] > 0)
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Total Students</span>
            <div class="text-2xl font-extrabold text-slate-800 dark:text-slate-100 mt-1">
                {{ $previewData['total_students'] }}
            </div>
            <span class="text-[10px] text-slate-400">Enrolled in selected class</span>
        </div>

        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs">
            <span class="text-[11px] font-semibold text-emerald-600 uppercase tracking-wider block">Ranked by Exam</span>
            <div class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1">
                {{ $previewData['ranked_count'] }}
            </div>
            <span class="text-[10px] text-slate-400">Has marks in {{ $sourceSemester?->semester_name ?? 'Source Term' }}</span>
        </div>

        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs">
            <span class="text-[11px] font-semibold text-amber-600 uppercase tracking-wider block">Unranked Students</span>
            <div class="text-2xl font-extrabold text-amber-600 dark:text-amber-400 mt-1">
                {{ $previewData['unranked_count'] }}
            </div>
            <span class="text-[10px] text-slate-400">No exam marks found</span>
        </div>

        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs">
            <span class="text-[11px] font-semibold text-purple-600 uppercase tracking-wider block">Target Assignment</span>
            <div class="text-sm font-extrabold text-purple-700 dark:text-purple-300 mt-1 truncate">
                {{ $targetSemester?->semester_name ?? 'Target Term' }}
            </div>
            <span class="text-[10px] text-slate-400">From #{{ $startNo }} sequential</span>
        </div>
    </div>
    @endif

    {{-- Main Preview Roster & Apply Form --}}
    @if($classId && $sourceSemesterId && $targetSemesterId)

        @if($sourceSemesterId === $targetSemesterId)
            <div class="p-6 rounded-3xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200 text-xs flex items-center gap-3">
                <i class="bi bi-exclamation-triangle-fill text-xl text-amber-500"></i>
                <div>
                    <strong class="block font-bold">Source Terminal and Target Terminal cannot be the same!</strong>
                    <span>Please pick the source terminal whose exam results you want to use (e.g., Terminal 2) and a different target terminal for the new roll numbers (e.g., Terminal 3).</span>
                </div>
            </div>
        @elseif($previewData['total_students'] === 0)
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-12 text-center text-slate-400">
                <i class="bi bi-people text-4xl block mb-2 opacity-30"></i>
                <h3 class="text-base font-bold text-slate-700 dark:text-slate-200 mb-1">No Enrolled Students Found</h3>
                <p class="text-xs text-slate-400 max-w-sm mx-auto">There are no students enrolled in the selected class and section for session #{{ $sessionId }}.</p>
            </div>
        @else
            @if(!$previewData['has_exam_data'])
                <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 text-amber-800 dark:text-amber-300 text-xs flex items-center gap-2.5">
                    <i class="bi bi-info-circle-fill text-base text-amber-500 shrink-0"></i>
                    <span>
                        <strong>Note:</strong> No finalized marks or exam marks were found for <strong>{{ $sourceSemester?->semester_name }}</strong> in this class. Students are listed in fallback alphabetical order. You can enter or finalize marks in the Marks module to rank students by performance.
                    </span>
                </div>
            @endif

            <form action="{{ route('roll-numbers.reassign.apply') }}" method="POST" id="applyReassignForm">
                @csrf
                <input type="hidden" name="session_id" value="{{ $sessionId }}">
                <input type="hidden" name="class_id" value="{{ $classId }}">
                <input type="hidden" name="section_id" value="{{ $sectionId }}">
                <input type="hidden" name="source_semester_id" value="{{ $sourceSemesterId }}">
                <input type="hidden" name="target_semester_id" value="{{ $targetSemesterId }}">
                <input type="hidden" name="update_active_roster" value="{{ $updateActiveRoster ? '1' : '0' }}">

                <div class="space-y-6">
                    @foreach($previewData['groups'] as $groupIndex => $group)
                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
                        
                        {{-- Group Header bar --}}
                        <div class="p-5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/30 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="px-3 py-1 rounded-xl text-xs font-bold bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300">
                                    {{ $selectedClass->class_name }}
                                </span>
                                @if($group['section'])
                                <span class="px-3 py-1 rounded-xl text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                                    Section {{ $group['section']->section_name }}
                                </span>
                                @endif
                                <span class="text-xs text-slate-500 font-semibold">
                                    {{ count($group['rows']) }} Students
                                </span>
                            </div>

                            <div class="flex items-center gap-3 text-xs">
                                <span class="text-slate-500">
                                    Ranked from: <strong class="text-purple-600 dark:text-purple-400">{{ $sourceSemester?->semester_name }}</strong>
                                </span>
                                <span class="text-slate-300 dark:text-slate-700">|</span>
                                <span class="text-slate-500">
                                    Applying to: <strong class="text-emerald-600 dark:text-emerald-400">{{ $targetSemester?->semester_name }}</strong>
                                </span>
                            </div>
                        </div>

                        {{-- Comparison Table --}}
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-semibold bg-slate-50/30 dark:bg-slate-800/10 uppercase tracking-wider">
                                        <th class="py-3 px-4 w-12 text-center">New Seq</th>
                                        <th class="py-3 px-4 min-w-[130px]">Source Rank</th>
                                        <th class="py-3 px-4 min-w-[220px]">Student Name</th>
                                        <th class="py-3 px-4 min-w-[140px]">{{ $sourceSemester?->semester_name }} Total & GPA</th>
                                        <th class="py-3 px-4 min-w-[110px] text-center">Old Roll</th>
                                        <th class="py-3 px-4 min-w-[90px] text-center">Trend</th>
                                        <th class="py-3 px-4 min-w-[180px]">New Roll No. (Editable)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @foreach($group['rows'] as $idx => $row)
                                    @php $st = $row['student']; @endphp
                                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                                        
                                        {{-- Sequential Number --}}
                                        <td class="py-3 px-4 text-center text-slate-400 font-mono">
                                            {{ $idx + 1 }}
                                        </td>

                                        {{-- Source Rank Badge --}}
                                        <td class="py-3 px-4">
                                            @if($row['is_ranked'] && $row['rank'])
                                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-bold {{ $row['rank'] <= 3 ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300 ring-1 ring-amber-500/30' : 'bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300' }}">
                                                    @if($row['rank'] === 1)
                                                        <i class="bi bi-trophy-fill text-amber-500"></i> Rank #1
                                                    @elseif($row['rank'] === 2)
                                                        <i class="bi bi-award-fill text-slate-400"></i> Rank #2
                                                    @elseif($row['rank'] === 3)
                                                        <i class="bi bi-award-fill text-amber-700"></i> Rank #3
                                                    @else
                                                        Rank #{{ $row['rank'] }}
                                                    @endif
                                                </div>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">
                                                    Unranked
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Student Info --}}
                                        <td class="py-3 px-4">
                                            <div class="flex items-center gap-2.5">
                                                @if($st?->photo)
                                                    <img src="{{ asset('storage/' . ltrim($st->photo, '/')) }}" class="w-8 h-8 rounded-full object-cover border border-slate-200 dark:border-slate-700" alt="">
                                                @else
                                                    <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300 font-bold flex items-center justify-center text-xs">
                                                        {{ strtoupper(substr($st?->first_name ?? 'S', 0, 1)) }}
                                                    </div>
                                                @endif
                                                <div>
                                                    <p class="font-bold text-slate-800 dark:text-slate-100 text-xs">
                                                        {{ $st?->first_name }} {{ $st?->last_name }}
                                                    </p>
                                                    <p class="text-[10px] text-slate-400 font-mono">
                                                        Adm: {{ $st?->academic_info?->admission_no ?? $st?->admission_no ?? '—' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Source Terminal Marks & GPA --}}
                                        <td class="py-3 px-4">
                                            @if($row['is_ranked'])
                                                <div class="space-y-0.5">
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-bold text-slate-800 dark:text-slate-200">
                                                            {{ round($row['total_marks'], 1) }}
                                                        </span>
                                                        <span class="text-[10px] text-slate-400">
                                                            / {{ round($row['total_max']) }} ({{ round($row['percentage'], 1) }}%)
                                                        </span>
                                                    </div>
                                                    <div class="flex items-center gap-2 text-[10px]">
                                                        <span class="px-1.5 py-0.2 rounded font-mono font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300">
                                                            GPA {{ number_format($row['gpa'], 2) }}
                                                        </span>
                                                        @if($row['failed'] > 0)
                                                            <span class="text-rose-500 font-semibold">{{ $row['failed'] }} failed</span>
                                                        @else
                                                            <span class="text-emerald-500 font-semibold">Passed</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-slate-400 text-[11px] italic">No exam marks</span>
                                            @endif
                                        </td>

                                        {{-- Old Roll Number --}}
                                        <td class="py-3 px-4 text-center font-mono font-bold text-slate-600 dark:text-slate-400">
                                            {{ $row['old_roll_no'] ?? '—' }}
                                        </td>

                                        {{-- Movement Trend --}}
                                        <td class="py-3 px-4 text-center">
                                            @if($row['trend'] === 'up')
                                                <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300" title="Climbed up {{ $row['trend_diff'] }} position(s)">
                                                    <i class="bi bi-arrow-up"></i> +{{ $row['trend_diff'] }}
                                                </span>
                                            @elseif($row['trend'] === 'down')
                                                <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300" title="Dropped {{ $row['trend_diff'] }} position(s)">
                                                    <i class="bi bi-arrow-down"></i> -{{ $row['trend_diff'] }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold text-slate-400 bg-slate-100 dark:bg-slate-800">
                                                    =
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Proposed New Roll Number (Editable Input) --}}
                                        <td class="py-3 px-4">
                                            <div class="relative max-w-[150px]">
                                                <input type="text"
                                                       name="roll_no[{{ $row['student_id'] }}]"
                                                       value="{{ old('roll_no.' . $row['student_id'], $row['new_roll_no']) }}"
                                                       required
                                                       class="w-full text-xs font-mono font-bold px-3 py-2 rounded-xl border border-purple-300 dark:border-purple-700 bg-white dark:bg-slate-800 text-purple-700 dark:text-purple-300 focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 outline-none transition">
                                            </div>

                                            {{-- Metadata for persistence --}}
                                            <input type="hidden" name="meta[{{ $row['student_id'] }}][rank]" value="{{ $row['rank'] }}">
                                            <input type="hidden" name="meta[{{ $row['student_id'] }}][total_marks]" value="{{ $row['total_marks'] }}">
                                            <input type="hidden" name="meta[{{ $row['student_id'] }}][gpa]" value="{{ $row['gpa'] }}">
                                            <input type="hidden" name="meta[{{ $row['student_id'] }}][percentage]" value="{{ $row['percentage'] }}">
                                            <input type="hidden" name="meta[{{ $row['student_id'] }}][section_id]" value="{{ $row['section_id'] }}">
                                        </td>

                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    </div>
                    @endforeach
                </div>

                {{-- Floating / Fixed Bottom Action Bar --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-lg p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 mt-6">
                    <div>
                        <h4 class="text-xs font-bold text-slate-800 dark:text-slate-100">
                            Ready to Apply New Roll Numbers?
                        </h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">
                            This will save roll numbers for <strong>{{ $targetSemester?->semester_name }}</strong> based on <strong>{{ $sourceSemester?->semester_name }}</strong> exam ranks.
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <a href="{{ route('roll-numbers.index', ['class_id' => $classId, 'section_id' => $sectionId]) }}" class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                            Cancel
                        </a>
                        <button type="submit" onclick="return confirm('Are you sure you want to reassign and apply these roll numbers for {{ $targetSemester?->semester_name }}?');" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white text-xs font-bold shadow-md shadow-purple-600/25 transition flex items-center gap-2 cursor-pointer">
                            <i class="bi bi-check-circle-fill"></i> Apply Roll Number Reassignment
                        </button>
                    </div>
                </div>

            </form>
        @endif

    @else
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-12 text-center text-slate-400">
            <i class="bi bi-funnel text-4xl block mb-3 text-purple-500 opacity-40"></i>
            <h3 class="text-base font-bold text-slate-700 dark:text-slate-200 mb-1">Select Academic Parameters</h3>
            <p class="text-xs text-slate-400 max-w-sm mx-auto">Please select a Class, Source Terminal, and Target Terminal from the filter panel above.</p>
        </div>
    @endif

</div>
@endsection

