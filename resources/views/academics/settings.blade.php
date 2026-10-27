@extends('layouts.app')
@section('title', 'School Setup')

@section('breadcrumb')
<x-breadcrumb :items="[
    ['label' => 'Home', 'url' => route('home'), 'icon' => 'bi-house'],
    ['label' => 'School Setup'],
]"/>
@endsection

@section('content')
<div class="flex-1 p-4 sm:p-6 lg:p-8 overflow-auto max-w-7xl mx-auto space-y-6">

    {{-- ── Page header ── --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 dark:text-slate-100 tracking-tight flex items-center gap-2.5">
                <span class="w-10 h-10 rounded-2xl bg-indigo-600/10 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xl shadow-xs">
                    <i class="bi bi-gear-wide-connected"></i>
                </span>
                School Setup
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Configure academic sessions, terms, classes, sections, subjects, teacher assignments, and academic policies
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <span class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-800/80 text-indigo-700 dark:text-indigo-300 rounded-xl text-xs font-bold shadow-xs">
                <i class="bi bi-calendar-check text-indigo-500"></i> Active Session ID: #{{ $current_school_session_id }}
            </span>
            @if($latest_school_session_id != $current_school_session_id)
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 dark:bg-amber-950/50 border border-amber-200 text-amber-700 dark:text-amber-300 rounded-xl text-xs font-semibold">
                <i class="bi bi-clock-history"></i> Browsing Previous Session
            </span>
            @endif
        </div>
    </div>

    {{-- ── Session messages ── --}}
    @include('session-messages')

    @php
    $steps = [
        ['icon' => 'bi-calendar3',        'label' => 'Academic Session'],
        ['icon' => 'bi-calendar-range',   'label' => 'Semesters & Terms'],
        ['icon' => 'bi-mortarboard',      'label' => 'Classes & Grades'],
        ['icon' => 'bi-grid-3x3-gap',     'label' => 'Sections & Rooms'],
        ['icon' => 'bi-book',             'label' => 'Courses & Subjects'],
        ['icon' => 'bi-person-badge',     'label' => 'Teacher Assignment'],
        ['icon' => 'bi-sliders',          'label' => 'Academic Rules'],
    ];
    $activeStep = (int)(request('step', old('active_step', 1)));
    $activeStep = max(1, min(count($steps), $activeStep));
    $progressPct = (int) round(($activeStep / count($steps)) * 100);
    @endphp

    {{-- ── Step progress bar & stepper wrap ── --}}
    <div class="profile-stepper-wrap" id="setupStepperContainer">
        
        {{-- Header with Step status and percentage --}}
        <div class="stepper-header flex items-center justify-between px-5 pt-4 pb-3 border-b border-slate-100 dark:border-slate-800/80">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 font-bold text-xs shadow-sm ring-1 ring-indigo-500/20">
                    <span id="stepperStepNum">{{ $activeStep }}</span>/{{ count($steps) }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Current Section:</span>
                        <span class="text-xs font-extrabold text-slate-800 dark:text-slate-200" id="stepperActiveTitle">{{ $steps[$activeStep-1]['label'] }}</span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <div class="hidden sm:flex flex-col items-end">
                    <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Academic Setup</span>
                </div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800/60 shadow-sm" id="stepperProgressBadge">
                    <i class="bi bi-speedometer2 text-indigo-500"></i>
                    <span id="stepperPercentText">{{ $progressPct }}%</span> Complete
                </div>
            </div>
        </div>

        {{-- Continuous Progress Track Bar --}}
        <div class="stepper-track-wrap w-full bg-slate-100 dark:bg-slate-800/80 h-1.5 relative overflow-hidden">
            <div class="stepper-progress-fill h-full bg-gradient-to-r from-indigo-500 via-indigo-600 to-emerald-500 transition-all duration-500 ease-out"
                 id="stepperProgressFill"
                 style="width: {{ $progressPct }}%;"></div>
        </div>

        {{-- Interactive Step Buttons Grid --}}
        <div class="profile-stepper-grid p-2 sm:p-2.5">
            @foreach($steps as $i => $step)
            @php
                $n = $i + 1;
                $done = $n < $activeStep;
                $active = $n === $activeStep;
                $stateClass = $active ? 'is-active' : ($done ? 'is-completed' : 'is-idle');
            @endphp
            <button type="button" onclick="goStep({{ $n }})"
                    class="profile-step-btn {{ $stateClass }} step-btn group"
                    data-step="{{ $n }}"
                    id="step-tab-btn-{{ $n }}"
                    title="{{ $step['label'] }} (Step {{ $n }})"
                    aria-label="Step {{ $n }}: {{ $step['label'] }}">
                <div class="step-icon-badge" data-ring>
                    @if($done)
                        <i class="bi bi-check2 text-base"></i>
                    @else
                        <i class="bi {{ $step['icon'] }}"></i>
                    @endif
                </div>
                <div class="step-info-col">
                    <div class="flex items-center gap-1.5">
                        <span class="step-sub" data-sub>{{ $done ? 'Completed' : 'Step ' . $n }}</span>
                        @if($done)
                            <i class="bi bi-check-circle-fill text-[9px] text-emerald-500 dark:text-emerald-400"></i>
                        @endif
                    </div>
                    <span class="step-title" data-label>{{ $step['label'] }}</span>
                </div>
                {{-- Active indicator bar --}}
                <div class="step-active-indicator"></div>
            </button>
            @endforeach
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         STEP 1: ACADEMIC SESSIONS
         ═══════════════════════════════════════════════════════════════ --}}
    <div class="step-panel" id="step-1">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            {{-- Left: Create Session Card --}}
            <div class="lg:col-span-5 space-y-6">
                @if ($latest_school_session_id == $current_school_session_id)
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
                    <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <span class="w-2 h-5 bg-indigo-600 rounded-full"></span>
                        <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <i class="bi bi-plus-circle text-indigo-500"></i> Create New Academic Session
                        </h2>
                    </div>

                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4 leading-relaxed">
                        Create one session per academic year (e.g., <code class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 rounded font-mono text-indigo-600">2026 - 2027</code> or <code class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 rounded font-mono text-indigo-600">2083/2084</code>). The latest created session is treated as the active academic year.
                    </p>

                    <form action="{{ route('school.session.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Session Name <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="session_name" required placeholder="e.g. 2026 - 2027"
                                   class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                        </div>

                        <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-600/25 transition flex items-center justify-center gap-2 cursor-pointer">
                            <i class="bi bi-check2"></i> Create Academic Session
                        </button>
                    </form>
                </div>
                @endif

                {{-- Browse by Session Card --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
                    <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <span class="w-2 h-5 bg-amber-500 rounded-full"></span>
                        <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <i class="bi bi-arrow-repeat text-amber-500"></i> Browse Data by Session
                        </h2>
                    </div>

                    <p class="text-xs text-amber-600 dark:text-amber-400 mb-4 bg-amber-50/70 dark:bg-amber-950/40 p-3 rounded-xl border border-amber-200 dark:border-amber-800/60 leading-relaxed">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Use this when you need to inspect or audit data from previous academic sessions.
                    </p>

                    <form action="{{ route('school.session.browse') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Select Academic Session to Browse
                            </label>
                            <select name="session_id" required
                                    class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                                @isset($school_sessions)
                                    @foreach ($school_sessions as $sess)
                                        <option value="{{ $sess->id }}" {{ $sess->id == $current_school_session_id ? 'selected' : '' }}>
                                            {{ $sess->session_name }} {{ $sess->id == $latest_school_session_id ? '(Latest / Current)' : '' }}
                                        </option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>

                        <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white text-xs font-semibold transition flex items-center justify-center gap-2 cursor-pointer">
                            <i class="bi bi-check-lg"></i> Switch Active Session
                        </button>
                    </form>
                </div>
            </div>

            {{-- Right: All Sessions List Table --}}
            <div class="lg:col-span-7">
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6 h-full flex flex-col">
                    <div class="flex items-center justify-between gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-5 bg-blue-600 rounded-full"></span>
                            <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">All Academic Sessions</h2>
                        </div>
                        <span class="text-xs font-semibold text-slate-400">{{ count($school_sessions ?? []) }} Session(s) Registered</span>
                    </div>

                    <div class="overflow-x-auto rounded-2xl border border-slate-100 dark:border-slate-800 flex-1">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300">
                                <tr>
                                    <th class="p-3 font-semibold w-12 text-center">#</th>
                                    <th class="p-3 font-semibold">Session Name</th>
                                    <th class="p-3 font-semibold">Status</th>
                                    <th class="p-3 font-semibold text-right">Created</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-600 dark:text-slate-300">
                                @forelse($school_sessions ?? [] as $i => $sess)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                    <td class="p-3 text-center text-slate-400 font-mono">{{ $i + 1 }}</td>
                                    <td class="p-3 font-bold text-slate-800 dark:text-slate-200">
                                        {{ $sess->session_name }}
                                    </td>
                                    <td class="p-3">
                                        @if($sess->id == $current_school_session_id)
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                                Active Working Session
                                            </span>
                                        @elseif($sess->id == $latest_school_session_id)
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800">
                                                Latest Year
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-500">
                                                Archived
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-right text-slate-400">
                                        {{ $sess->created_at ? $sess->created_at->format('M d, Y') : 'N/A' }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="p-6 text-center text-slate-400">No academic sessions found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         STEP 2: SEMESTERS & TERMS
         ═══════════════════════════════════════════════════════════════ --}}
    <div class="step-panel hidden" id="step-2">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            {{-- Left: Create Semester Form --}}
            <div class="lg:col-span-5">
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
                    <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <span class="w-2 h-5 bg-indigo-600 rounded-full"></span>
                        <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <i class="bi bi-plus-circle text-indigo-500"></i> Create Semester / Term
                        </h2>
                    </div>

                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                        Add terms/semesters for current session (e.g. <em>First Term, Mid-Term, Final Term</em>).
                    </p>

                    <form action="{{ route('school.semester.create') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="session_id" value="{{ $current_school_session_id }}">

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Semester / Term Name <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="semester_name" required placeholder="e.g. First Semester / First Term"
                                   class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Start Date <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="start_date" required
                                   class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                End Date <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="end_date" required
                                   class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                        </div>

                        <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-600/25 transition flex items-center justify-center gap-2 cursor-pointer">
                            <i class="bi bi-check2"></i> Create Semester / Term
                        </button>
                    </form>
                </div>
            </div>

            {{-- Right: Semesters List Table --}}
            <div class="lg:col-span-7">
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6 h-full flex flex-col">
                    <div class="flex items-center justify-between gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-5 bg-violet-600 rounded-full"></span>
                            <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Semesters in Current Session</h2>
                        </div>
                        <span class="text-xs font-semibold text-slate-400">{{ count($semesters ?? []) }} Term(s)</span>
                    </div>

                    <div class="overflow-x-auto rounded-2xl border border-slate-100 dark:border-slate-800 flex-1">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300">
                                <tr>
                                    <th class="p-3 font-semibold w-12 text-center">#</th>
                                    <th class="p-3 font-semibold">Semester / Term Name</th>
                                    <th class="p-3 font-semibold">Starts</th>
                                    <th class="p-3 font-semibold">Ends</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-600 dark:text-slate-300">
                                @forelse($semesters ?? [] as $i => $sem)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                    <td class="p-3 text-center text-slate-400 font-mono">{{ $i + 1 }}</td>
                                    <td class="p-3 font-bold text-slate-800 dark:text-slate-200">
                                        <i class="bi bi-calendar2-range text-violet-500 me-1.5"></i> {{ $sem->semester_name }}
                                    </td>
                                    <td class="p-3 text-slate-600 dark:text-slate-300">{{ $sem->start_date }}</td>
                                    <td class="p-3 text-slate-600 dark:text-slate-300">{{ $sem->end_date }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="p-6 text-center text-slate-400">No semesters created yet for this session.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         STEP 3: CLASSES & GRADES
         ═══════════════════════════════════════════════════════════════ --}}
    <div class="step-panel hidden" id="step-3">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            {{-- Left: Create Class Form --}}
            <div class="lg:col-span-5">
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
                    <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <span class="w-2 h-5 bg-indigo-600 rounded-full"></span>
                        <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <i class="bi bi-mortarboard text-indigo-500"></i> Create Academic Class
                        </h2>
                    </div>

                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                        Add classes or grades offered by the school (e.g. <em>Class 1, Class 10, Playgroup</em>).
                    </p>

                    <form action="{{ route('school.class.create') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="session_id" value="{{ $current_school_session_id }}">

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Class Name(s) / तहहरू <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="academics_class_names" name="bulk_class_names" required placeholder="e.g. Class 1, Class 2, Class 3 or Class 10"
                                   class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                            <p class="text-[10px] text-slate-400 mt-1">कमा (,) दिएर एकैपटक धेरै कक्षाहरू हाल्न सकिन्छ। (e.g. Class 1, Class 2, Class 3)</p>
                        </div>

                        {{-- Quick preset chips --}}
                        <div class="flex flex-wrap gap-1 text-[10px]">
                            <button type="button" onclick="document.getElementById('academics_class_names').value='Playgroup, Nursery, LKG, UKG, Class 1, Class 2, Class 3, Class 4, Class 5, Class 6, Class 7, Class 8, Class 9, Class 10'" class="px-2 py-0.5 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded-lg border border-indigo-200">
                                + PG to 10
                            </button>
                            <button type="button" onclick="document.getElementById('academics_class_names').value='Class 1, Class 2, Class 3, Class 4, Class 5, Class 6, Class 7, Class 8'" class="px-2 py-0.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg border border-emerald-200">
                                + Class 1 to 8
                            </button>
                            <button type="button" onclick="document.getElementById('academics_class_names').value='ECD, Nursery, Class 1, Class 2, Class 3, Class 4, Class 5'" class="px-2 py-0.5 bg-amber-50 text-amber-700 hover:bg-amber-100 rounded-lg border border-amber-200">
                                + ECD to 5
                            </button>
                        </div>

                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-500">Initial Sections</label>
                                <input type="text" name="initial_sections_str" value="A, B" placeholder="A, B" class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700">
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-500">Start Room</label>
                                <input type="text" name="default_room_no" value="101" placeholder="101" class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700">
                            </div>
                        </div>

                        <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/25 transition flex items-center justify-center gap-2 cursor-pointer">
                            <i class="bi bi-check2-circle"></i> कक्षाहरू सिर्जना गर्नुहोस् (Create Classes)
                        </button>
                    </form>
                </div>
            </div>

            {{-- Right: Classes List Table --}}
            <div class="lg:col-span-7">
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6 h-full flex flex-col">
                    <div class="flex items-center justify-between gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-5 bg-indigo-600 rounded-full"></span>
                            <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Classes in Current Session</h2>
                        </div>
                        <span class="text-xs font-semibold text-slate-400">{{ count($school_classes ?? []) }} Class(es)</span>
                    </div>

                    <div class="overflow-x-auto rounded-2xl border border-slate-100 dark:border-slate-800 flex-1">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300">
                                <tr>
                                    <th class="p-3 font-semibold w-12 text-center">#</th>
                                    <th class="p-3 font-semibold">Class Name</th>
                                    <th class="p-3 font-semibold text-center">Sections</th>
                                    <th class="p-3 font-semibold text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-600 dark:text-slate-300">
                                @forelse($school_classes ?? [] as $i => $cls)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                    <td class="p-3 text-center text-slate-400 font-mono">{{ $i + 1 }}</td>
                                    <td class="p-3 font-bold text-slate-800 dark:text-slate-200">
                                        <i class="bi bi-mortarboard text-indigo-500 me-1.5"></i> {{ $cls->class_name }}
                                    </td>
                                    <td class="p-3 text-center">
                                        @php
                                            $secCount = ($school_sections ?? collect())->where('class_id', $cls->id)->count();
                                        @endphp
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                            {{ $secCount }} Section(s)
                                        </span>
                                    </td>
                                    <td class="p-3 text-right">
                                        <button type="button" onclick="goStep(4)" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-semibold">
                                            + Add Section
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="p-6 text-center text-slate-400">No classes registered yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         STEP 4: SECTIONS & ROOMS
         ═══════════════════════════════════════════════════════════════ --}}
    <div class="step-panel hidden" id="step-4">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            {{-- Left: Create Section Form --}}
            <div class="lg:col-span-5">
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
                    <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <span class="w-2 h-5 bg-indigo-600 rounded-full"></span>
                        <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <i class="bi bi-grid-3x3-gap text-indigo-500"></i> Create Section
                        </h2>
                    </div>

                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                        Add sections and room numbers to existing classes (e.g. <em>Section A, Room 101</em>).
                    </p>

                    <form action="{{ route('school.section.create') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="session_id" value="{{ $current_school_session_id }}">

                        {{-- Mode Selector --}}
                        <div class="flex p-1 bg-slate-100 dark:bg-slate-800 rounded-xl text-xs font-semibold">
                            <button type="button" onclick="setAcadSecMode('multi')" id="acad-sec-mode-multi-btn" class="flex-1 py-1.5 rounded-lg bg-white dark:bg-slate-700 text-emerald-600 shadow-xs font-bold">
                                <i class="bi bi-check2-square"></i> छानिएका (e.g. 2, 7)
                            </button>
                            <button type="button" onclick="setAcadSecMode('single')" id="acad-sec-mode-single-btn" class="flex-1 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 font-semibold">
                                <i class="bi bi-card-list"></i> एकल कक्षा (Single)
                            </button>
                        </div>

                        {{-- Multi-class selection box --}}
                        <div id="acad-sec-multi-box" class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                    कक्षाहरू छान्नुहोस् (Select Target Classes) <span class="text-rose-500">*</span>
                                </label>
                                <div class="flex items-center gap-2 text-[10px]">
                                    <button type="button" onclick="selectAllAcadSecClasses(true)" class="text-indigo-600 font-bold hover:underline">
                                        सबै (All)
                                    </button>
                                    <span class="text-slate-300">|</span>
                                    <button type="button" onclick="selectAllAcadSecClasses(false)" class="text-slate-400 font-semibold hover:underline">
                                        हटाउनुहोस् (Clear)
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5 max-h-36 overflow-y-auto p-2 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-700">
                                @isset($school_classes)
                                    @foreach ($school_classes as $cls)
                                        <label class="acad-sec-chip p-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-emerald-400 flex items-center gap-1.5 cursor-pointer transition select-none">
                                            <input type="checkbox" name="class_ids[]" value="{{ $cls->id }}" class="acad-sec-checkbox rounded text-emerald-600 focus:ring-emerald-500 w-3.5 h-3.5" onchange="updateAcadSecChipStyle(this)">
                                            <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-200 truncate">{{ $cls->class_name }}</span>
                                        </label>
                                    @endforeach
                                @endisset
                            </div>
                        </div>

                        {{-- Single-class selection dropdown --}}
                        <div id="acad-sec-single-box" class="space-y-1 hidden">
                            <label for="acad_sec_single_class" class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                                कक्षा छान्नुहोस् (Select Single Class) <span class="text-rose-500">*</span>
                            </label>
                            <select id="acad_sec_single_class" name="class_id"
                                    class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition font-semibold">
                                <option value="" disabled selected>— कक्षा छान्नुहोस् —</option>
                                <option value="all">🌟 सबै कक्षाहरू (All Classes in Session)</option>
                                @isset($school_classes)
                                    @foreach ($school_classes as $cls)
                                        <option value="{{ $cls->id }}">{{ $cls->class_name }}</option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Section Name(s) / सेक्सनहरू <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="academics_sec_name" name="section_name" required placeholder="e.g. A, B or Red, Blue" value="A, B"
                                   class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition font-bold text-emerald-700">
                            
                            {{-- Quick Chips --}}
                            <div class="flex flex-wrap gap-1 mt-1.5 text-[10px]">
                                <button type="button" onclick="document.getElementById('academics_sec_name').value='A, B'" class="px-2 py-0.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg border border-emerald-200 font-bold">
                                    + A, B
                                </button>
                                <button type="button" onclick="document.getElementById('academics_sec_name').value='A, B, C'" class="px-2 py-0.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg border border-emerald-200 font-bold">
                                    + A, B, C
                                </button>
                                <button type="button" onclick="document.getElementById('academics_sec_name').value='A, B, C, D'" class="px-2 py-0.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg border border-emerald-200 font-bold">
                                    + A, B, C, D
                                </button>
                                <button type="button" onclick="document.getElementById('academics_sec_name').value='Red, Green, Blue'" class="px-2 py-0.5 bg-purple-50 text-purple-700 hover:bg-purple-100 rounded-lg border border-purple-200 font-bold">
                                    + Red, Green, Blue
                                </button>
                                <button type="button" onclick="document.getElementById('academics_sec_name').value='Morning, Day'" class="px-2 py-0.5 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded-lg border border-indigo-200 font-bold">
                                    + Morning, Day
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Starting Room Number <span class="text-[10px] text-emerald-600 font-normal">✨ (e.g. 103 → Auto-increments: 103, 104, 105...)</span>
                            </label>
                            <input type="text" name="room_no" placeholder="e.g. 101 or 103" value="101"
                                   class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition font-semibold">
                        </div>

                        <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/25 transition flex items-center justify-center gap-2 cursor-pointer">
                            <i class="bi bi-grid-plus"></i> सेक्सनहरू सिर्जना गर्नुहोस् (Create Sections)
                        </button>
                    </form>

                    <script>
                        function setAcadSecMode(mode) {
                            const multiBox = document.getElementById('acad-sec-multi-box');
                            const singleBox = document.getElementById('acad-sec-single-box');
                            const multiBtn = document.getElementById('acad-sec-mode-multi-btn');
                            const singleBtn = document.getElementById('acad-sec-mode-single-btn');
                            const singleSelect = document.getElementById('acad_sec_single_class');

                            if (mode === 'multi') {
                                multiBox.classList.remove('hidden');
                                singleBox.classList.add('hidden');
                                multiBtn.className = 'flex-1 py-1.5 rounded-lg bg-white dark:bg-slate-700 text-emerald-600 shadow-xs font-bold';
                                singleBtn.className = 'flex-1 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 font-semibold';
                                singleSelect.removeAttribute('required');
                            } else {
                                multiBox.classList.add('hidden');
                                singleBox.classList.remove('hidden');
                                singleBtn.className = 'flex-1 py-1.5 rounded-lg bg-white dark:bg-slate-700 text-emerald-600 shadow-xs font-bold';
                                multiBtn.className = 'flex-1 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 font-semibold';
                                singleSelect.setAttribute('required', 'required');
                            }
                        }

                        function selectAllAcadSecClasses(checked) {
                            document.querySelectorAll('.acad-sec-checkbox').forEach(cb => {
                                cb.checked = checked;
                                updateAcadSecChipStyle(cb);
                            });
                        }

                        function updateAcadSecChipStyle(cb) {
                            const parent = cb.closest('.acad-sec-chip');
                            if (cb.checked) {
                                parent.classList.add('border-emerald-500', 'bg-emerald-50/60', 'dark:bg-emerald-950/30', 'font-bold');
                            } else {
                                parent.classList.remove('border-emerald-500', 'bg-emerald-50/60', 'dark:bg-emerald-950/30', 'font-bold');
                            }
                        }
                    </script>
                </div>
            </div>

            {{-- Right: Sections List Table --}}
            <div class="lg:col-span-7">
                <form id="acadSecBulkDeleteForm" action="{{ route('school.section.bulk.delete') }}" method="POST" onsubmit="return confirm('के तपाईं छानिएका सबै सेक्सनहरू मेटाउन चाहनुहुन्छ? (Delete selected sections?');" class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6 h-full flex flex-col">
                    @csrf
                    <div class="flex items-center justify-between gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-5 bg-emerald-600 rounded-full"></span>
                            <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Sections in Current Session</h2>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="submit" id="acadSecBulkDelBtn" class="hidden px-3 py-1 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-xs transition items-center gap-1.5 cursor-pointer">
                                <i class="bi bi-trash3-fill"></i> Delete Selected (<span id="acadSecBulkCount">0</span>)
                            </button>
                            <span class="text-xs font-semibold text-slate-400">{{ count($school_sections ?? []) }} Section(s)</span>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-2xl border border-slate-100 dark:border-slate-800 flex-1">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300">
                                <tr>
                                    <th class="p-3 w-10 text-center">
                                        <input type="checkbox" id="acadSecSelectAllCb" onchange="toggleAllAcadSecRows(this)" class="rounded text-rose-600 focus:ring-rose-500 w-3.5 h-3.5 cursor-pointer" title="Select All">
                                    </th>
                                    <th class="p-3 font-semibold">Section</th>
                                    <th class="p-3 font-semibold">Assigned Class</th>
                                    <th class="p-3 font-semibold">Room No</th>
                                    <th class="p-3 font-semibold text-right w-16">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-600 dark:text-slate-300">
                                @forelse($school_sections ?? [] as $i => $sec)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                    <td class="p-3 text-center">
                                        <input type="checkbox" name="section_ids[]" value="{{ $sec->id }}" class="acad-sec-row-cb rounded text-rose-600 focus:ring-rose-500 w-3.5 h-3.5 cursor-pointer" onchange="updateAcadSecBulkUI()">
                                    </td>
                                    <td class="p-3 font-bold text-slate-800 dark:text-slate-200">
                                        <i class="bi bi-grid text-emerald-500 me-1.5"></i> {{ $sec->section_name }}
                                    </td>
                                    <td class="p-3">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                            {{ $sec->schoolClass->class_name ?? 'Class #' . $sec->class_id }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-slate-500 dark:text-slate-400 font-mono">
                                        {{ $sec->room_no ?? 'N/A' }}
                                    </td>
                                    <td class="p-3 text-right">
                                        <form action="{{ route('school.section.delete', ['id' => $sec->id]) }}" method="POST" onsubmit="return confirm('Delete Section {{ $sec->section_name }}?');" class="inline m-0">
                                            @csrf
                                            <button type="submit" class="p-1 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition cursor-pointer border-0 bg-transparent" title="Delete duplicate or unwanted section">
                                                <i class="bi bi-trash3 text-xs"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="p-6 text-center text-slate-400">No sections created yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </form>

                <script>
                    function updateAcadSecBulkUI() {
                        const checked = document.querySelectorAll('.acad-sec-row-cb:checked');
                        const count = checked.length;
                        const btn = document.getElementById('acadSecBulkDelBtn');
                        const countSpan = document.getElementById('acadSecBulkCount');
                        const masterCb = document.getElementById('acadSecSelectAllCb');
                        const all = document.querySelectorAll('.acad-sec-row-cb');

                        if (countSpan) countSpan.innerText = count;

                        if (count > 0) {
                            if (btn) {
                                btn.classList.remove('hidden');
                                btn.classList.add('inline-flex');
                            }
                        } else {
                            if (btn) {
                                btn.classList.remove('inline-flex');
                                btn.classList.add('hidden');
                            }
                        }

                        if (masterCb && all.length > 0) {
                            masterCb.checked = (checked.length === all.length);
                        }
                    }

                    function toggleAllAcadSecRows(masterCb) {
                        document.querySelectorAll('.acad-sec-row-cb').forEach(cb => {
                            cb.checked = masterCb.checked;
                        });
                        updateAcadSecBulkUI();
                    }
                </script>
            </div>

        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         STEP 5: COURSES & SUBJECTS
         ═══════════════════════════════════════════════════════════════ --}}
    <div class="step-panel hidden" id="step-5">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            {{-- Left: Create Course Form --}}
            <div class="lg:col-span-5">
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
                    <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <span class="w-2 h-5 bg-indigo-600 rounded-full"></span>
                        <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <i class="bi bi-book text-indigo-500"></i> Create Course / Subject
                        </h2>
                    </div>

                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                        Add subjects and assign them to classes. Enable <strong>"All Terms"</strong> to auto-create the subject for every semester/term.
                    </p>

                    <form action="{{ route('school.course.create') }}" method="POST" class="space-y-4" id="courseCreateForm">
                        @csrf
                        <input type="hidden" name="session_id" value="{{ $current_school_session_id }}">

                        {{-- Subject Name --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Course / Subject Name <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="course_name" required placeholder="e.g. Mathematics, English, Science"
                                   class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                        </div>

                        {{-- Course Type --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Course Type <span class="text-rose-500">*</span>
                            </label>
                            <select name="course_type" required
                                    class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                                <option value="Core">Core</option>
                                <option value="General">General</option>
                                <option value="Elective">Elective</option>
                                <option value="Optional">Optional</option>
                            </select>
                        </div>

                        {{-- Assign to Classes (Multi-select with checkboxes) --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Assign to Class(es) <span class="text-rose-500">*</span>
                            </label>
                            <div class="border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 max-h-48 overflow-y-auto p-2.5 space-y-1">
                                {{-- Select All Toggle --}}
                                @isset($school_classes)
                                @if(count($school_classes) > 1)
                                <label class="flex items-center gap-2 px-2 py-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/50 cursor-pointer hover:bg-indigo-100/80 dark:hover:bg-indigo-950/60 transition mb-1">
                                    <input type="checkbox" id="courseClassSelectAll" onchange="toggleAllCourseClasses(this)"
                                           class="w-3.5 h-3.5 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500/30">
                                    <span class="text-xs font-bold text-indigo-700 dark:text-indigo-300">Select All Classes</span>
                                    <span class="ml-auto text-[10px] text-indigo-500 font-semibold">{{ count($school_classes) }} classes</span>
                                </label>
                                @endif
                                @foreach ($school_classes as $cls)
                                <label class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/40 cursor-pointer transition course-class-label">
                                    <input type="checkbox" name="class_ids[]" value="{{ $cls->id }}"
                                           class="course-class-cb w-3.5 h-3.5 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500/30"
                                           onchange="updateCourseClassSelectAll()">
                                    <span class="text-xs text-slate-700 dark:text-slate-300 font-medium">{{ $cls->class_name }}</span>
                                </label>
                                @endforeach
                                @endisset
                            </div>
                            <p class="text-[10px] text-slate-400 mt-1"><i class="bi bi-info-circle"></i> Select one or more classes for this subject.</p>
                        </div>

                        {{-- All Terms Toggle --}}
                        <div class="bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/50 rounded-xl px-3.5 py-3">
                            <label class="flex items-center gap-2.5 cursor-pointer">
                                <input type="checkbox" name="all_terms" value="1" id="courseAllTermsToggle"
                                       onchange="toggleSemesterSelect()"
                                       class="w-4 h-4 rounded text-emerald-600 border-slate-300 focus:ring-emerald-500/30"
                                       checked>
                                <div>
                                    <span class="text-xs font-bold text-emerald-800 dark:text-emerald-200">Apply to All Terms / Semesters</span>
                                    <p class="text-[10px] text-emerald-600 dark:text-emerald-400 mt-0.5">Subject will be created for every term in the current session</p>
                                </div>
                            </label>
                        </div>

                        {{-- Semester Select (shown only when "All Terms" is unchecked) --}}
                        <div id="semesterSelectWrapper" style="display: none;">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Assign to Semester / Term <span class="text-rose-500">*</span>
                            </label>
                            <select name="semester_id" id="courseSemesterSelect"
                                    class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                                <option value="" disabled selected>— Select Semester —</option>
                                @isset($semesters)
                                    @foreach ($semesters as $sem)
                                        <option value="{{ $sem->id }}">{{ $sem->semester_name }}</option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>

                        <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-600/25 transition flex items-center justify-center gap-2 cursor-pointer">
                            <i class="bi bi-check2"></i> Create Course / Subject
                        </button>
                    </form>
                </div>
            </div>

            {{-- Right: Courses List Table (Grouped) --}}
            <div class="lg:col-span-7">
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6 h-full flex flex-col">
                    <div class="flex items-center justify-between gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-5 bg-cyan-600 rounded-full"></span>
                            <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Subjects in Current Session</h2>
                        </div>
                        @php
                            $grouped = collect($courses ?? [])->groupBy(function($c) {
                                return $c->course_name . '___' . $c->class_id;
                            });
                        @endphp
                        <span class="text-xs font-semibold text-slate-400">{{ $grouped->count() }} Subject(s)</span>
                    </div>

                    <div class="overflow-x-auto rounded-2xl border border-slate-100 dark:border-slate-800 flex-1">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300">
                                <tr>
                                    <th class="p-3 font-semibold w-12 text-center">#</th>
                                    <th class="p-3 font-semibold">Subject / Course</th>
                                    <th class="p-3 font-semibold">Type</th>
                                    <th class="p-3 font-semibold">Class</th>
                                    <th class="p-3 font-semibold">Semesters / Terms</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-600 dark:text-slate-300">
                                @forelse($grouped as $key => $group)
                                @php $first = $group->first(); @endphp
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                    <td class="p-3 text-center text-slate-400 font-mono">{{ $loop->iteration }}</td>
                                    <td class="p-3 font-bold text-slate-800 dark:text-slate-200">
                                        <i class="bi bi-book text-cyan-500 me-1.5"></i> {{ $first->course_name }}
                                    </td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                            {{ $first->course_type }}
                                        </span>
                                    </td>
                                    <td class="p-3 font-medium text-slate-700 dark:text-slate-300">
                                        {{ $first->schoolClass->class_name ?? 'Class #' . $first->class_id }}
                                    </td>
                                    <td class="p-3">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($group as $crs)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-semibold bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-900/50">
                                                {{ $crs->semester->semester_name ?? 'Sem #' . $crs->semester_id }}
                                            </span>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="p-6 text-center text-slate-400">No courses/subjects added yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        {{-- Course Form JS --}}
        <script>
            function toggleAllCourseClasses(masterCb) {
                document.querySelectorAll('.course-class-cb').forEach(cb => {
                    cb.checked = masterCb.checked;
                });
            }

            function updateCourseClassSelectAll() {
                const allCbs = document.querySelectorAll('.course-class-cb');
                const allChecked = Array.from(allCbs).every(cb => cb.checked);
                const masterCb = document.getElementById('courseClassSelectAll');
                if (masterCb) masterCb.checked = allChecked;
            }

            function toggleSemesterSelect() {
                const allTerms = document.getElementById('courseAllTermsToggle');
                const wrapper = document.getElementById('semesterSelectWrapper');
                const semSelect = document.getElementById('courseSemesterSelect');

                if (allTerms && allTerms.checked) {
                    wrapper.style.display = 'none';
                    if (semSelect) semSelect.removeAttribute('required');
                } else {
                    wrapper.style.display = 'block';
                    if (semSelect) semSelect.setAttribute('required', 'required');
                }
            }

            // Initialize on page load
            document.addEventListener('DOMContentLoaded', function() {
                toggleSemesterSelect();
            });
        </script>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         STEP 6: TEACHER ASSIGNMENT
         ═══════════════════════════════════════════════════════════════ --}}
    <div class="step-panel hidden" id="step-6">
        
        {{-- Header & Mode Switcher Bar --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-5 mb-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <span class="w-2.5 h-6 bg-indigo-600 rounded-full inline-block"></span>
                        <i class="bi bi-person-badge text-indigo-500"></i> Subject Teacher Mapping
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Map teachers to classes, sections, and subjects in seconds with rapid matrix and bulk tools.
                    </p>
                </div>

                <div class="flex items-center flex-wrap gap-2">
                    {{-- Mode Switcher Tabs --}}
                    <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-800 rounded-2xl text-xs font-semibold">
                        <button type="button" onclick="switchAssignMode('matrix')" id="btn-mode-matrix" class="px-3.5 py-1.5 rounded-xl bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-300 shadow-xs transition flex items-center gap-1.5">
                            <i class="bi bi-grid-3x3-gap"></i> Quick Matrix
                        </button>
                        <button type="button" onclick="switchAssignMode('bulk')" id="btn-mode-bulk" class="px-3.5 py-1.5 rounded-xl text-slate-600 dark:text-slate-300 hover:text-indigo-600 transition flex items-center gap-1.5">
                            <i class="bi bi-check2-all"></i> Bulk Assign
                        </button>
                        <button type="button" onclick="switchAssignMode('single')" id="btn-mode-single" class="px-3.5 py-1.5 rounded-xl text-slate-600 dark:text-slate-300 hover:text-indigo-600 transition flex items-center gap-1.5">
                            <i class="bi bi-plus-circle"></i> Single
                        </button>
                    </div>

                    {{-- Clone / Copy from Previous Term Button --}}
                    <button type="button" onclick="openCloneModal()" class="px-3.5 py-2 rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 text-indigo-600 dark:text-indigo-300 text-xs font-bold border border-indigo-200/60 dark:border-indigo-800/60 transition flex items-center gap-1.5">
                        <i class="bi bi-copy"></i> Copy from Another Term
                    </button>
                </div>
            </div>
        </div>

        {{-- MODE 1: CLASS-WISE SUBJECT MATRIX (10x FASTER) --}}
        <div id="panel-assign-matrix" class="space-y-6">
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
                {{-- Matrix Controls Bar --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pb-5 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            1. Select Class <span class="text-rose-500">*</span>
                        </label>
                        <select id="matrix_class_id" onchange="loadClassMatrix();" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                            <option value="">— Choose Class —</option>
                            @isset($school_classes)
                                @foreach ($school_classes as $cls)
                                    <option value="{{ $cls->id }}">{{ $cls->class_name }}</option>
                                @endforeach
                            @endisset
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            2. Semester / Term <span class="text-rose-500">*</span>
                        </label>
                        <select id="matrix_semester_id" onchange="loadClassMatrix();" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                            @isset($semesters)
                                @foreach ($semesters as $sem)
                                    <option value="{{ $sem->id }}" {{ $loop->first ? 'selected' : '' }}>{{ $sem->semester_name }}</option>
                                @endforeach
                            @endisset
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            3. Section Filter
                        </label>
                        <select id="matrix_section_filter" onchange="filterMatrixSections();" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                            <option value="all">All Sections (Side-by-Side)</option>
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button type="button" id="btn-save-matrix" onclick="saveClassMatrix();" disabled class="w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-bold shadow-md shadow-indigo-600/20 transition flex items-center justify-center gap-2">
                            <i class="bi bi-check2-all text-base"></i> <span id="matrix-save-text">Save All Changes</span>
                        </button>
                    </div>
                </div>

                {{-- Action Helper Bar (Copy section A to others) --}}
                <div id="matrix-helpers-bar" class="hidden mt-4 pt-4 border-t border-slate-100 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3 text-xs">
                    <div class="flex items-center gap-2 text-slate-500 dark:text-slate-400">
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-600 text-[11px] font-bold">✓</span>
                        <span>Assign teachers directly in the grid. Click <strong>Save All Changes</strong> or save individual rows.</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="copyFirstSectionToAll();" class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-200 font-semibold transition flex items-center gap-1.5">
                            <i class="bi bi-arrows-angle-expand text-indigo-500"></i> Copy 1st Section to All Sections
                        </button>
                        <button type="button" onclick="clearAllMatrixSelections();" class="px-3 py-1.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 text-rose-600 font-semibold transition flex items-center gap-1">
                            <i class="bi bi-x-circle"></i> Clear Grid
                        </button>
                    </div>
                </div>

                {{-- Matrix Table Container --}}
                <div class="mt-4">
                    <div id="matrix-loading" class="hidden py-12 text-center">
                        <div class="inline-block animate-spin w-8 h-8 border-3 border-indigo-600 border-t-transparent rounded-full mb-2"></div>
                        <p class="text-xs text-slate-500">Loading subjects and section grid...</p>
                    </div>

                    <div id="matrix-empty" class="py-12 text-center border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-2xl">
                        <i class="bi bi-grid-3x3-gap text-3xl text-slate-300 dark:text-slate-600 mb-2 block"></i>
                        <h3 class="text-xs font-bold text-slate-700 dark:text-slate-300">Select a Class above</h3>
                        <p class="text-[11px] text-slate-400 mt-1 max-w-sm mx-auto">
                            The subject matrix will immediately display all subjects and sections, allowing you to assign teachers in under 30 seconds.
                        </p>
                    </div>

                    <div id="matrix-table-wrapper" class="hidden overflow-x-auto rounded-2xl border border-slate-100 dark:border-slate-800">
                        <table class="w-full text-xs text-left" id="matrix-table">
                            <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300">
                                <tr id="matrix-thead-row">
                                    <th class="p-3 w-10 text-center">#</th>
                                    <th class="p-3 min-w-[200px]">Subject / Course</th>
                                    {{-- Section columns dynamically injected --}}
                                </tr>
                            </thead>
                            <tbody id="matrix-tbody" class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                                {{-- Rows dynamically injected --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODE 2: BULK ASSIGN BY TEACHER --}}
        <div id="panel-assign-bulk" class="hidden">
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
                <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <span class="w-2 h-5 bg-indigo-600 rounded-full"></span>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Assign Teacher to Multiple Sections / Subjects</h3>
                </div>

                <form id="bulk-assign-form" onsubmit="submitBulkAssign(event);" class="space-y-5">
                    @csrf
                    <input type="hidden" name="session_id" value="{{ $current_school_session_id }}">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                Select Teacher <span class="text-rose-500">*</span>
                            </label>
                            <select name="teacher_id" id="bulk_teacher_id" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20">
                                <option value="" disabled selected>— Choose Teacher —</option>
                                @isset($teachers)
                                    @foreach ($teachers as $teacher)
                                        <option value="{{ $teacher->id }}">{{ $teacher->first_name }} {{ $teacher->last_name }} ({{ $teacher->email }})</option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                Semester / Term <span class="text-rose-500">*</span>
                            </label>
                            <select name="semester_id" id="bulk_semester_id" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20">
                                @isset($semesters)
                                    @foreach ($semesters as $sem)
                                        <option value="{{ $sem->id }}" {{ $loop->first ? 'selected' : '' }}>{{ $sem->semester_name }}</option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                Target Class <span class="text-rose-500">*</span>
                            </label>
                            <select name="class_id" id="bulk_class_id" onchange="loadBulkOptions(this.value);" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20">
                                <option value="" disabled selected>— Choose Class —</option>
                                @isset($school_classes)
                                    @foreach ($school_classes as $cls)
                                        <option value="{{ $cls->id }}">{{ $cls->class_name }}</option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>
                    </div>

                    {{-- Multi-Select Sections & Subjects --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-3 border-t border-slate-100 dark:border-slate-800">
                        {{-- Sections Checkboxes --}}
                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-700/60">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-200">
                                    Apply to Section(s) <span class="text-rose-500">*</span>
                                </span>
                                <label class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-indigo-600 cursor-pointer">
                                    <input type="checkbox" id="bulk-select-all-sections" onchange="toggleSelectAll('bulk_section', this.checked)"> Select All
                                </label>
                            </div>
                            <div id="bulk-sections-list" class="space-y-2 max-h-48 overflow-y-auto">
                                <p class="text-[11px] text-slate-400">Select a class first to load sections.</p>
                            </div>
                        </div>

                        {{-- Subjects Checkboxes --}}
                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-700/60">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-200">
                                    Assign Subject(s) <span class="text-rose-500">*</span>
                                </span>
                                <label class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-indigo-600 cursor-pointer">
                                    <input type="checkbox" id="bulk-select-all-courses" onchange="toggleSelectAll('bulk_course', this.checked)"> Select All
                                </label>
                            </div>
                            <div id="bulk-courses-list" class="space-y-2 max-h-48 overflow-y-auto">
                                <p class="text-[11px] text-slate-400">Select a class first to load subjects.</p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-700 dark:text-slate-300 cursor-pointer">
                            <input type="checkbox" name="is_class_teacher" value="1" class="rounded text-indigo-600">
                            Also designate this teacher as Class Teacher for selected section(s)
                        </label>
                        <button type="submit" id="btn-submit-bulk" class="py-2.5 px-6 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/20 transition flex items-center justify-center gap-2">
                            <i class="bi bi-check2-all text-base"></i> Assign Selected Combinations
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODE 3: CLASSIC SINGLE FORM --}}
        <div id="panel-assign-single" class="hidden">
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6 max-w-xl">
                <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <span class="w-2 h-5 bg-indigo-600 rounded-full"></span>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Single Teacher Assignment</h3>
                </div>

                <form action="{{ route('school.teacher.assign') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="session_id" value="{{ $current_school_session_id }}">

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Select Teacher <span class="text-rose-500">*</span>
                        </label>
                        <select name="teacher_id" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20">
                            <option value="" disabled selected>— Select Teacher —</option>
                            @isset($teachers)
                                @foreach ($teachers as $teacher)
                                    <option value="{{ $teacher->id }}">{{ $teacher->first_name }} {{ $teacher->last_name }} ({{ $teacher->email }})</option>
                                @endforeach
                            @endisset
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Semester / Term <span class="text-rose-500">*</span>
                        </label>
                        <select name="semester_id" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20">
                            <option value="" disabled selected>— Select Semester —</option>
                            @isset($semesters)
                                @foreach ($semesters as $sem)
                                    <option value="{{ $sem->id }}">{{ $sem->semester_name }}</option>
                                @endforeach
                            @endisset
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Class <span class="text-rose-500">*</span>
                        </label>
                        <select onchange="getSectionsAndCourses(this);" name="class_id" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20">
                            <option value="" disabled selected>— Select Class —</option>
                            @isset($school_classes)
                                @foreach ($school_classes as $cls)
                                    <option value="{{ $cls->id }}">{{ $cls->class_name }}</option>
                                @endforeach
                            @endisset
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Section <span class="text-rose-500">*</span>
                        </label>
                        <select id="section-select" name="section_id" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20">
                            <option value="" disabled selected>Select class first</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Subject / Course <span class="text-rose-500">*</span>
                        </label>
                        <select id="course-select" name="course_id" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20">
                            <option value="" disabled selected>Select class first</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-600/25 transition flex items-center justify-center gap-2">
                        <i class="bi bi-check2"></i> Assign Teacher
                    </button>
                </form>
            </div>
        </div>

        {{-- Current Teacher Assignments Table (Below all modes) --}}
        <div class="mt-8">
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-5 bg-pink-600 rounded-full"></span>
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">All Current Teacher Assignments</h3>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500">{{ count($assigned_teachers ?? []) }} total</span>
                    </div>

                    <div class="w-full sm:w-64">
                        <input type="text" id="assignment-search" onkeyup="filterAssignmentsTable()" placeholder="Search teacher, class, subject..." class="w-full text-xs px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20">
                    </div>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-slate-100 dark:border-slate-800">
                    <table class="w-full text-xs text-left" id="assigned-teachers-table">
                        <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300">
                            <tr>
                                <th class="p-3 font-semibold w-12 text-center">#</th>
                                <th class="p-3 font-semibold">Teacher</th>
                                <th class="p-3 font-semibold">Subject</th>
                                <th class="p-3 font-semibold">Class / Section</th>
                                <th class="p-3 font-semibold">Term</th>
                                <th class="p-3 font-semibold text-center w-20">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-600 dark:text-slate-300" id="assigned-teachers-tbody">
                            @forelse($assigned_teachers ?? [] as $i => $at)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition assignment-row">
                                <td class="p-3 text-center text-slate-400 font-mono">{{ $i + 1 }}</td>
                                <td class="p-3 font-bold text-slate-800 dark:text-slate-200 search-teacher">
                                    <div class="flex items-center gap-1.5">
                                        <i class="bi bi-person-circle text-indigo-500"></i>
                                        <span>{{ $at->teacher->first_name ?? '' }} {{ $at->teacher->last_name ?? '' }}</span>
                                        @if($at->is_class_teacher)
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300" title="Class Teacher">CT</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="p-3 font-semibold text-indigo-600 dark:text-indigo-400 search-subject">
                                    {{ $at->course->course_name ?? 'N/A' }}
                                </td>
                                <td class="p-3 text-slate-700 dark:text-slate-300 search-class">
                                    {{ $at->schoolClass->class_name ?? '' }} &bull; {{ $at->section->section_name ?? '' }}
                                </td>
                                <td class="p-3 text-slate-500 text-[11px] search-term">
                                    {{ $at->semester->semester_name ?? 'All' }}
                                </td>
                                <td class="p-3 text-center">
                                    <form action="{{ route('school.teacher.assign.destroy', $at->id) }}" method="POST" onsubmit="return confirm('Remove assignment of {{ $at->teacher->first_name ?? 'Teacher' }} for {{ $at->course->course_name ?? 'this subject' }}?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/30 transition" title="Delete Assignment">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="p-6 text-center text-slate-400">No teachers assigned yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    {{-- Clone Term Modal --}}
    <div id="clone-term-modal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-md w-full p-6 border border-slate-200 dark:border-slate-800 shadow-2xl">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                        <i class="bi bi-copy"></i>
                    </span>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Copy Term Assignments</h3>
                </div>
                <button type="button" onclick="closeCloneModal()" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
            </div>

            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                Instantly copy all subject-teacher mappings from an existing term to a new term. Saves hours of re-entry!
            </p>

            <form action="{{ route('school.teacher.clone.assignments') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Copy From (Source Term) <span class="text-rose-500">*</span>
                    </label>
                    <select name="from_semester_id" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20">
                        <option value="" disabled selected>— Select Source Term —</option>
                        @isset($semesters)
                            @foreach ($semesters as $sem)
                                <option value="{{ $sem->id }}">{{ $sem->semester_name }}</option>
                            @endforeach
                        @endisset
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Copy To (Target Term) <span class="text-rose-500">*</span>
                    </label>
                    <select name="to_semester_id" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20">
                        <option value="" disabled selected>— Select Target Term —</option>
                        @isset($semesters)
                            @foreach ($semesters as $sem)
                                <option value="{{ $sem->id }}">{{ $sem->semester_name }}</option>
                            @endforeach
                        @endisset
                    </select>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeCloneModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/20">
                        <i class="bi bi-copy me-1"></i> Copy All Mappings
                    </button>
                </div>
            </form>
        </div>
    </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         STEP 7: ACADEMIC RULES & CONTROLS
         ═══════════════════════════════════════════════════════════════ --}}
    <div class="step-panel hidden" id="step-7">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            {{-- Attendance Mode Card --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
                <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <span class="w-2 h-5 bg-indigo-600 rounded-full"></span>
                    <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <i class="bi bi-calendar-check text-indigo-500"></i> Attendance Mode Policy
                    </h2>
                </div>

                <p class="text-xs text-rose-600 dark:text-rose-400 bg-rose-50/70 dark:bg-rose-950/40 p-3 rounded-xl border border-rose-200 dark:border-rose-800/60 mb-4 leading-relaxed">
                    <i class="bi bi-exclamation-diamond-fill me-1"></i> Avoid changing the attendance type in the middle of an ongoing active term.
                </p>

                <form action="{{ route('school.attendance.type.update') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 p-3.5 rounded-2xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition cursor-pointer">
                            <input type="radio" name="attendance_type" value="section" {{ ($academic_setting->attendance_type ?? 'section') == 'section' ? 'checked' : '' }}
                                   class="text-indigo-600 focus:ring-indigo-500">
                            <div>
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-200 block">Attendance by Section</span>
                                <span class="text-[11px] text-slate-500">Class teacher takes one overall attendance per day per class/section.</span>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 p-3.5 rounded-2xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition cursor-pointer">
                            <input type="radio" name="attendance_type" value="course" {{ ($academic_setting->attendance_type ?? 'section') == 'course' ? 'checked' : '' }}
                                   class="text-indigo-600 focus:ring-indigo-500">
                            <div>
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-200 block">Attendance by Course / Period</span>
                                <span class="text-[11px] text-slate-500">Subject teachers record individual attendance during each course period.</span>
                            </div>
                        </label>
                    </div>

                    <button type="submit" class="py-2.5 px-6 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-600/25 transition flex items-center gap-2 cursor-pointer">
                        <i class="bi bi-check2"></i> Save Attendance Policy
                    </button>
                </form>
            </div>

            {{-- Final Marks Submission Toggle --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
                <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <span class="w-2 h-5 bg-emerald-600 rounded-full"></span>
                    <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <i class="bi bi-award text-emerald-500"></i> Final Marks Submission Control
                    </h2>
                </div>

                <p class="text-xs text-slate-500 dark:text-slate-400 mb-4 leading-relaxed">
                    Toggle whether teachers can finalize and submit final grade marks to the examination controller. Typically opened near exam completion and locked otherwise.
                </p>

                <form action="{{ route('school.final.marks.submission.status.update') }}" method="POST" class="space-y-5">
                    @csrf
                    
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200 block">Marks Submission Window</span>
                            <span class="text-[11px] text-slate-500">Current status: 
                                <strong class="{{ ($academic_setting->marks_submission_status ?? 'off') == 'on' ? 'text-emerald-600' : 'text-slate-400' }}">
                                    {{ ($academic_setting->marks_submission_status ?? 'off') == 'on' ? 'OPEN / ALLOWED' : 'LOCKED / DISALLOWED' }}
                                </strong>
                            </span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer" onclick="toggleMarkSwitch(this)">
                            <input type="checkbox" name="marks_submission_status" value="on" {{ ($academic_setting->marks_submission_status ?? 'off') == 'on' ? 'checked' : '' }} class="sr-only" id="marksToggle">
                            <div id="marksToggleTrack"
                                 class="w-11 h-6 rounded-full transition-colors duration-200 relative"
                                 style="background-color: {{ ($academic_setting->marks_submission_status ?? 'off') == 'on' ? '#10b981' : '#cbd5e1' }}">
                                <span id="marksToggleThumb"
                                      class="absolute top-[2px] h-5 w-5 bg-white border border-slate-300 rounded-full shadow transition-all duration-200"
                                      style="left: {{ ($academic_setting->marks_submission_status ?? 'off') == 'on' ? '22px' : '2px' }}"></span>
                            </div>
                        </label>
                    </div>

                    <button type="submit" class="py-2.5 px-6 rounded-xl bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white text-xs font-semibold transition flex items-center gap-2 cursor-pointer">
                        <i class="bi bi-check2"></i> Save Marks Submission Status
                    </button>
                </form>
            </div>

            {{-- Academic Health Checklist summary card --}}
            <div class="md:col-span-2 bg-gradient-to-br from-indigo-50/80 to-white dark:from-indigo-950/30 dark:to-slate-900 border border-indigo-100 dark:border-indigo-900/60 rounded-3xl p-6">
                <h3 class="text-sm font-bold text-indigo-950 dark:text-indigo-200 mb-3 flex items-center gap-2">
                    <i class="bi bi-shield-check text-indigo-600 dark:text-indigo-400"></i> Setup Health Checklist
                </h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3 text-center">
                    <div class="p-3 rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-xs">
                        <div class="w-6 h-6 rounded-full {{ count($school_sessions ?? []) > 0 ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600' }} flex items-center justify-center mx-auto mb-1 text-xs">
                            <i class="bi {{ count($school_sessions ?? []) > 0 ? 'bi-check' : 'bi-x' }}"></i>
                        </div>
                        <span class="text-[11px] font-bold block text-slate-700 dark:text-slate-300">Session</span>
                        <span class="text-[10px] text-slate-400">{{ count($school_sessions ?? []) }} defined</span>
                    </div>

                    <div class="p-3 rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-xs">
                        <div class="w-6 h-6 rounded-full {{ count($semesters ?? []) > 0 ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600' }} flex items-center justify-center mx-auto mb-1 text-xs">
                            <i class="bi {{ count($semesters ?? []) > 0 ? 'bi-check' : 'bi-x' }}"></i>
                        </div>
                        <span class="text-[11px] font-bold block text-slate-700 dark:text-slate-300">Terms</span>
                        <span class="text-[10px] text-slate-400">{{ count($semesters ?? []) }} defined</span>
                    </div>

                    <div class="p-3 rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-xs">
                        <div class="w-6 h-6 rounded-full {{ count($school_classes ?? []) > 0 ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600' }} flex items-center justify-center mx-auto mb-1 text-xs">
                            <i class="bi {{ count($school_classes ?? []) > 0 ? 'bi-check' : 'bi-x' }}"></i>
                        </div>
                        <span class="text-[11px] font-bold block text-slate-700 dark:text-slate-300">Classes</span>
                        <span class="text-[10px] text-slate-400">{{ count($school_classes ?? []) }} defined</span>
                    </div>

                    <div class="p-3 rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-xs">
                        <div class="w-6 h-6 rounded-full {{ count($school_sections ?? []) > 0 ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600' }} flex items-center justify-center mx-auto mb-1 text-xs">
                            <i class="bi {{ count($school_sections ?? []) > 0 ? 'bi-check' : 'bi-x' }}"></i>
                        </div>
                        <span class="text-[11px] font-bold block text-slate-700 dark:text-slate-300">Sections</span>
                        <span class="text-[10px] text-slate-400">{{ count($school_sections ?? []) }} defined</span>
                    </div>

                    <div class="p-3 rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-xs">
                        <div class="w-6 h-6 rounded-full {{ count($courses ?? []) > 0 ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600' }} flex items-center justify-center mx-auto mb-1 text-xs">
                            <i class="bi {{ count($courses ?? []) > 0 ? 'bi-check' : 'bi-x' }}"></i>
                        </div>
                        <span class="text-[11px] font-bold block text-slate-700 dark:text-slate-300">Subjects</span>
                        <span class="text-[10px] text-slate-400">{{ count($courses ?? []) }} defined</span>
                    </div>

                    <div class="p-3 rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-xs">
                        <div class="w-6 h-6 rounded-full {{ count($assigned_teachers ?? []) > 0 ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600' }} flex items-center justify-center mx-auto mb-1 text-xs">
                            <i class="bi {{ count($assigned_teachers ?? []) > 0 ? 'bi-check' : 'bi-x' }}"></i>
                        </div>
                        <span class="text-[11px] font-bold block text-slate-700 dark:text-slate-300">Teachers</span>
                        <span class="text-[10px] text-slate-400">{{ count($assigned_teachers ?? []) }} mapped</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ── Stepper Navigation Footer Bar ── --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-4 flex items-center justify-between gap-4">
        <button type="button" id="btnPrev" onclick="goStep(currentStep - 1)"
                class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-bold transition flex items-center gap-2 cursor-pointer">
            <i class="bi bi-arrow-left"></i> Previous Step
        </button>

        {{-- Dots Indicator --}}
        <div class="flex items-center gap-1.5 hidden sm:flex" id="dotsContainer">
            @for($d = 1; $d <= count($steps); $d++)
            <button type="button" onclick="goStep({{ $d }})"
                    class="dot-indicator h-2.5 rounded-full transition-all duration-300 {{ $d === $activeStep ? 'w-8 bg-indigo-600 ring-2 ring-indigo-500/30' : ($d < $activeStep ? 'w-2.5 bg-emerald-500' : 'w-2.5 bg-slate-300 dark:bg-slate-600') }}"
                    title="Jump to step {{ $d }}: {{ $steps[$d-1]['label'] }}"></button>
            @endfor
        </div>

        <button type="button" id="btnNext" onclick="goStep(currentStep + 1)"
                class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/25 transition flex items-center gap-2 cursor-pointer">
            Next Step <i class="bi bi-arrow-right"></i>
        </button>
    </div>

</div>

{{-- ═══════════════════════════════════════════════════════════════
     STYLES & SCRIPT
     ═══════════════════════════════════════════════════════════════ --}}
<style>
.profile-stepper-wrap {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.06), 0 2px 6px -1px rgba(15, 23, 42, 0.04);
    overflow: hidden;
    transition: all 0.3s ease;
}
html[data-theme="dark"] .profile-stepper-wrap,
html.dark .profile-stepper-wrap {
    background: #0f172a !important;
    border-color: #1e293b !important;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.5) !important;
}

.profile-stepper-grid {
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
    gap: 8px;
}
@media (max-width: 1200px) {
    .profile-stepper-grid {
        display: flex;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        scrollbar-width: thin;
        -webkit-overflow-scrolling: touch;
        gap: 8px;
        padding: 10px 8px;
    }
    .profile-stepper-grid::-webkit-scrollbar {
        height: 4px;
    }
    .profile-stepper-grid::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    html[data-theme="dark"] .profile-stepper-grid::-webkit-scrollbar-thumb,
    html.dark .profile-stepper-grid::-webkit-scrollbar-thumb {
        background: #334155;
    }
}

.profile-step-btn {
    appearance: none !important;
    -webkit-appearance: none !important;
    background: #ffffff !important;
    border: 1.5px solid #f1f5f9 !important;
    outline: none !important;
    padding: 10px 12px;
    border-radius: 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    position: relative;
    overflow: hidden;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    text-align: left;
    min-width: 140px;
    width: 100%;
    user-select: none;
    scroll-snap-align: start;
}
html[data-theme="dark"] .profile-step-btn,
html.dark .profile-step-btn {
    background: #131d31 !important;
    border-color: #1e293b !important;
}

.profile-step-btn.is-idle {
    background: #f8fafc !important;
    border-color: #f1f5f9 !important;
}
html[data-theme="dark"] .profile-step-btn.is-idle,
html.dark .profile-step-btn.is-idle {
    background: #141f33 !important;
    border-color: #1e293b !important;
}
.profile-step-btn.is-idle:hover {
    background: #f1f5f9 !important;
    border-color: #e2e8f0 !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px -2px rgba(15, 23, 42, 0.08);
}
html[data-theme="dark"] .profile-step-btn.is-idle:hover,
html.dark .profile-step-btn.is-idle:hover {
    background: #192742 !important;
    border-color: #2e3e5b !important;
}
.profile-step-btn.is-idle .step-icon-badge {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    color: #64748b;
}
html[data-theme="dark"] .profile-step-btn.is-idle .step-icon-badge,
html.dark .profile-step-btn.is-idle .step-icon-badge {
    background: #1a263d !important;
    border-color: #273754 !important;
    color: #94a3b8 !important;
}
.profile-step-btn.is-idle .step-sub {
    color: #94a3b8;
}
.profile-step-btn.is-idle .step-title {
    color: #475569;
}
html[data-theme="dark"] .profile-step-btn.is-idle .step-title {
    color: #94a3b8 !important;
}

.profile-step-btn.is-active {
    background: linear-gradient(145deg, #ffffff, #eff6ff) !important;
    border-color: #6366f1 !important;
    box-shadow: 0 6px 18px -2px rgba(79, 70, 229, 0.18), 0 2px 6px -1px rgba(79, 70, 229, 0.12) !important;
    transform: translateY(-1px);
}
html[data-theme="dark"] .profile-step-btn.is-active,
html.dark .profile-step-btn.is-active {
    background: linear-gradient(145deg, #18233c, #1f2e4e) !important;
    border-color: #6366f1 !important;
    box-shadow: 0 6px 20px -2px rgba(99, 102, 241, 0.35) !important;
}
.profile-step-btn.is-active .step-icon-badge {
    background: linear-gradient(135deg, #4f46e5, #6366f1);
    color: #ffffff;
    box-shadow: 0 4px 14px rgba(79, 70, 229, 0.45);
    border: none;
    transform: scale(1.04);
}
.profile-step-btn.is-active .step-sub {
    color: #4f46e5;
    font-weight: 800;
}
html[data-theme="dark"] .profile-step-btn.is-active .step-sub {
    color: #a5b4fc !important;
}
.profile-step-btn.is-active .step-title {
    color: #0f172a;
    font-weight: 800;
}
html[data-theme="dark"] .profile-step-btn.is-active .step-title {
    color: #ffffff !important;
}

.step-active-indicator {
    position: absolute;
    bottom: 0;
    left: 10px;
    right: 10px;
    height: 3px;
    background: linear-gradient(90deg, #4f46e5, #06b6d4);
    border-radius: 3px 3px 0 0;
    opacity: 0;
    transform: scaleX(0.4);
    transition: all 0.25s ease;
}
.profile-step-btn.is-active .step-active-indicator {
    opacity: 1;
    transform: scaleX(1);
}

.profile-step-btn.is-completed {
    background: #fcfdfc !important;
    border-color: #dcfce7 !important;
}
html[data-theme="dark"] .profile-step-btn.is-completed,
html.dark .profile-step-btn.is-completed {
    background: #112224 !important;
    border-color: #173b32 !important;
}
.profile-step-btn.is-completed:hover {
    background: #f0fdf4 !important;
    border-color: #bbf7d0 !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px -2px rgba(16, 185, 129, 0.12);
}
.profile-step-btn.is-completed .step-icon-badge {
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    color: #059669;
}
html[data-theme="dark"] .profile-step-btn.is-completed .step-icon-badge {
    background: #064e3b !important;
    border-color: #047857 !important;
    color: #34d399 !important;
}
.profile-step-btn.is-completed .step-sub {
    color: #059669;
    font-weight: 700;
}
html[data-theme="dark"] .profile-step-btn.is-completed .step-sub {
    color: #34d399 !important;
}
.profile-step-btn.is-completed .step-title {
    color: #1e293b;
    font-weight: 700;
}
html[data-theme="dark"] .profile-step-btn.is-completed .step-title {
    color: #cbd5e1 !important;
}

.step-icon-badge {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
    transition: all 0.25s ease;
}
.step-info-col {
    display: flex;
    flex-direction: column;
    min-width: 0;
    overflow: hidden;
}
.step-sub {
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    line-height: 1.2;
}
.step-title {
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.3;
    margin-top: 2px;
}
</style>

<script>
const STEP_ICONS  = @json(collect($steps)->pluck('icon')->values());
const STEP_TITLES = @json(collect($steps)->pluck('label')->values());
const TOTAL_STEPS = {{ count($steps) }};
let currentStep   = {{ $activeStep }};

function goStep(n) {
    n = Math.max(1, Math.min(TOTAL_STEPS, n));

    // Hide all panels, show target
    document.querySelectorAll('.step-panel').forEach(p => p.classList.add('hidden'));
    const target = document.getElementById('step-' + n);
    if (target) target.classList.remove('hidden');

    // Update progress percentage
    const pct = Math.round((n / TOTAL_STEPS) * 100);
    const progressFill = document.getElementById('stepperProgressFill');
    if (progressFill) progressFill.style.width = pct + '%';
    
    const pctText = document.getElementById('stepperPercentText');
    if (pctText) pctText.textContent = pct + '%';

    const stepNum = document.getElementById('stepperStepNum');
    if (stepNum) stepNum.textContent = n;

    const activeTitle = document.getElementById('stepperActiveTitle');
    if (activeTitle && STEP_TITLES[n-1]) activeTitle.textContent = STEP_TITLES[n-1];

    // Update step buttons
    document.querySelectorAll('.step-btn').forEach(btn => {
        const s    = parseInt(btn.dataset.step);
        const ring = btn.querySelector('[data-ring]');
        const sub  = btn.querySelector('[data-sub]');

        btn.classList.remove('is-active', 'is-completed', 'is-idle');

        if (s < n) {
            btn.classList.add('is-completed');
            if (ring) ring.innerHTML = '<i class="bi bi-check2 text-base"></i>';
            if (sub)  sub.innerHTML = 'Done <i class="bi bi-check-circle-fill text-[9px] text-emerald-500"></i>';
        } else if (s === n) {
            btn.classList.add('is-active');
            if (ring) ring.innerHTML = '<i class="bi ' + STEP_ICONS[s-1] + '"></i>';
            if (sub)  sub.textContent = 'Step ' + s;
            btn.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
        } else {
            btn.classList.add('is-idle');
            if (ring) ring.innerHTML = '<i class="bi ' + STEP_ICONS[s-1] + '"></i>';
            if (sub)  sub.textContent = 'Step ' + s;
        }
    });

    // Update dots in footer
    document.querySelectorAll('.dot-indicator').forEach((d, i) => {
        const s = i + 1;
        d.classList.remove('w-8', 'w-2.5', 'bg-indigo-600', 'bg-emerald-500', 'bg-slate-300', 'dark:bg-slate-600', 'ring-2', 'ring-indigo-500/30');
        if (s === n) {
            d.classList.add('w-8', 'bg-indigo-600', 'ring-2', 'ring-indigo-500/30');
        } else if (s < n) {
            d.classList.add('w-2.5', 'bg-emerald-500');
        } else {
            d.classList.add('w-2.5', 'bg-slate-300', 'dark:bg-slate-600');
        }
    });

    // Prev / Next button visibility
    const prev = document.getElementById('btnPrev');
    const next = document.getElementById('btnNext');
    if (prev) {
        if (n <= 1) {
            prev.classList.add('invisible', 'pointer-events-none');
        } else {
            prev.classList.remove('invisible', 'pointer-events-none');
        }
    }
    if (next) {
        if (n >= TOTAL_STEPS) {
            next.classList.add('hidden');
        } else {
            next.classList.remove('hidden');
        }
    }

    currentStep = n;

    // Update URL query parameter without full reload
    const url = new URL(window.location);
    url.searchParams.set('step', n);
    window.history.replaceState({}, '', url);

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Dynamic Section and Course Loader for Step 6
function getSectionsAndCourses(obj) {
    const class_id = obj.options[obj.selectedIndex].value;
    const sectionSelect = document.getElementById('section-select');
    const courseSelect = document.getElementById('course-select');

    if (!class_id || !sectionSelect || !courseSelect) return;

    sectionSelect.innerHTML = '<option disabled selected value="">Loading sections...</option>';
    courseSelect.innerHTML = '<option disabled selected value="">Loading courses...</option>';

    const url = "{{ route('get.sections.courses.by.classId') }}?class_id=" + class_id;

    fetch(url)
        .then(resp => resp.json())
        .then(data => {
            sectionSelect.options.length = 0;
            sectionSelect.options[0] = new Option('— Select Section —', '');
            sectionSelect.options[0].disabled = true;
            sectionSelect.options[0].selected = true;

            if (data.sections && data.sections.length > 0) {
                data.sections.forEach(sec => {
                    sectionSelect.add(new Option(sec.section_name, sec.id));
                });
            } else {
                sectionSelect.options[0] = new Option('No sections found', '');
            }

            courseSelect.options.length = 0;
            courseSelect.options[0] = new Option('— Select Subject / Course —', '');
            courseSelect.options[0].disabled = true;
            courseSelect.options[0].selected = true;

            if (data.courses && data.courses.length > 0) {
                data.courses.forEach(crs => {
                    courseSelect.add(new Option(crs.course_name, crs.id));
                });
            } else {
                courseSelect.options[0] = new Option('No courses found', '');
            }
        })
        .catch(err => {
            console.error('Error loading sections and courses:', err);
            sectionSelect.innerHTML = '<option disabled selected value="">Failed to load sections</option>';
            courseSelect.innerHTML = '<option disabled selected value="">Failed to load courses</option>';
        });
}

// ═══════════════════════════════════════════════════════════════
// Step 6: Rapid Subject-Teacher Mapping JavaScript
// ═══════════════════════════════════════════════════════════════
const ALL_TEACHERS = @json(($teachers ?? collect())->map(function($t) {
    return [
        'id' => $t->id,
        'name' => trim(($t->first_name ?? '') . ' ' . ($t->last_name ?? '')),
        'email' => $t->email ?? ''
    ];
}));

const MATRIX_DATA_URL     = "{{ route('school.teacher.matrix.data') }}";
const MATRIX_SAVE_URL     = "{{ route('school.teacher.assign.matrix') }}";
const BULK_ASSIGN_URL     = "{{ route('school.teacher.assign.bulk') }}";
const SECTIONS_COURSES_URL = "{{ route('get.sections.courses.by.classId') }}";
const CURRENT_SESSION_ID  = "{{ $current_school_session_id }}";
const CSRF_TOKEN          = "{{ csrf_token() }}";

let currentMatrixData = null;

function switchAssignMode(mode) {
    const modes = ['matrix', 'bulk', 'single'];
    modes.forEach(m => {
        const panel = document.getElementById('panel-assign-' + m);
        const btn = document.getElementById('btn-mode-' + m);
        if (panel) {
            if (m === mode) {
                panel.classList.remove('hidden');
            } else {
                panel.classList.add('hidden');
            }
        }
        if (btn) {
            if (m === mode) {
                btn.className = 'px-3.5 py-1.5 rounded-xl bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-300 shadow-xs transition flex items-center gap-1.5 font-bold';
            } else {
                btn.className = 'px-3.5 py-1.5 rounded-xl text-slate-600 dark:text-slate-300 hover:text-indigo-600 transition flex items-center gap-1.5 font-normal';
            }
        }
    });
}

function openCloneModal() {
    const modal = document.getElementById('clone-term-modal');
    if (modal) modal.classList.remove('hidden');
}

function closeCloneModal() {
    const modal = document.getElementById('clone-term-modal');
    if (modal) modal.classList.add('hidden');
}

function loadClassMatrix() {
    const classId = document.getElementById('matrix_class_id')?.value;
    const semesterId = document.getElementById('matrix_semester_id')?.value;
    const emptyState = document.getElementById('matrix-empty');
    const loadingState = document.getElementById('matrix-loading');
    const tableWrapper = document.getElementById('matrix-table-wrapper');
    const helpersBar = document.getElementById('matrix-helpers-bar');
    const saveBtn = document.getElementById('btn-save-matrix');

    if (!classId) {
        if (emptyState) emptyState.classList.remove('hidden');
        if (loadingState) loadingState.classList.add('hidden');
        if (tableWrapper) tableWrapper.classList.add('hidden');
        if (helpersBar) helpersBar.classList.add('hidden');
        if (saveBtn) saveBtn.disabled = true;
        currentMatrixData = null;
        return;
    }

    if (emptyState) emptyState.classList.add('hidden');
    if (loadingState) loadingState.classList.remove('hidden');
    if (tableWrapper) tableWrapper.classList.add('hidden');
    if (helpersBar) helpersBar.classList.add('hidden');
    if (saveBtn) saveBtn.disabled = true;

    const url = `${MATRIX_DATA_URL}?class_id=${encodeURIComponent(classId)}&semester_id=${encodeURIComponent(semesterId || '')}&_t=${Date.now()}`;

    fetch(url, {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        currentMatrixData = data;
        renderMatrix(data);
    })
    .catch(err => {
        console.error('Error fetching matrix data:', err);
        if (loadingState) loadingState.classList.add('hidden');
        if (emptyState) {
            emptyState.classList.remove('hidden');
            emptyState.innerHTML = '<i class="bi bi-exclamation-triangle text-amber-500 text-3xl mb-2 block"></i><h3 class="text-xs font-bold text-rose-600">Failed to load subjects and sections</h3>';
        }
    });
}

function renderMatrix(data) {
    const loadingState = document.getElementById('matrix-loading');
    const tableWrapper = document.getElementById('matrix-table-wrapper');
    const helpersBar = document.getElementById('matrix-helpers-bar');
    const saveBtn = document.getElementById('btn-save-matrix');
    const theadRow = document.getElementById('matrix-thead-row');
    const tbody = document.getElementById('matrix-tbody');
    const sectionFilter = document.getElementById('matrix_section_filter');

    if (loadingState) loadingState.classList.add('hidden');

    if (!data.sections || data.sections.length === 0 || !data.courses || data.courses.length === 0) {
        if (tableWrapper) tableWrapper.classList.add('hidden');
        if (helpersBar) helpersBar.classList.add('hidden');
        const emptyState = document.getElementById('matrix-empty');
        if (emptyState) {
            emptyState.classList.remove('hidden');
            emptyState.innerHTML = `
                <i class="bi bi-info-circle text-indigo-400 text-3xl mb-2 block"></i>
                <h3 class="text-xs font-bold text-slate-700 dark:text-slate-300">
                    ${!data.sections || data.sections.length === 0 ? 'No sections found for this class' : 'No subjects found for this class'}
                </h3>
                <p class="text-[11px] text-slate-400 mt-1 max-w-sm mx-auto">
                    Please ensure you have configured sections and subjects for this class in previous steps.
                </p>`;
        }
        return;
    }

    // Populate Section Filter Dropdown
    if (sectionFilter) {
        sectionFilter.innerHTML = '<option value="all">All Sections (Side-by-Side)</option>';
        data.sections.forEach(sec => {
            const opt = document.createElement('option');
            opt.value = sec.id;
            opt.textContent = `Section: ${sec.section_name}`;
            sectionFilter.appendChild(opt);
        });
    }

    // Construct thead
    theadRow.innerHTML = `
        <th class="p-3 w-10 text-center font-bold">#</th>
        <th class="p-3 min-w-[200px] font-bold">Subject / Course</th>
    `;
    data.sections.forEach(sec => {
        const th = document.createElement('th');
        th.className = `p-3 min-w-[210px] text-left font-bold border-l border-slate-100 dark:border-slate-800 matrix-sec-col matrix-sec-${sec.id}`;
        th.dataset.secId = sec.id;
        th.innerHTML = `
            <div class="flex items-center justify-between">
                <span>Section ${escapeHtml(sec.section_name)}</span>
                <span class="text-[10px] text-slate-400 font-normal">Col</span>
            </div>
        `;
        theadRow.appendChild(th);
    });

    // Teachers list: use data.teachers or fallback to ALL_TEACHERS
    const teachersList = (data.teachers && data.teachers.length > 0) ? data.teachers : ALL_TEACHERS;

    // Fast lookup for current assignments: key = `${course_id}_${section_id}`
    const assignmentMap = {};
    if (data.assignments && Array.isArray(data.assignments)) {
        data.assignments.forEach(a => {
            assignmentMap[`${a.course_id}_${a.section_id}`] = a.teacher_id;
        });
    }

    // Construct tbody
    tbody.innerHTML = '';
    data.courses.forEach((course, idx) => {
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition course-row';
        tr.dataset.courseId = course.id;

        let rowHtml = `
            <td class="p-3 text-center text-slate-400 font-mono text-[11px]">${idx + 1}</td>
            <td class="p-3 font-semibold text-slate-800 dark:text-slate-100">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    <span>${escapeHtml(course.course_name)}</span>
                    ${course.course_code ? `<span class="px-1.5 py-0.5 rounded text-[10px] font-mono bg-slate-100 dark:bg-slate-800 text-slate-500">${escapeHtml(course.course_code)}</span>` : ''}
                </div>
            </td>
        `;

        data.sections.forEach(sec => {
            const assignedTeacherId = assignmentMap[`${course.id}_${sec.id}`] || '';
            let optionsHtml = '<option value="">— Unassigned —</option>';
            teachersList.forEach(t => {
                const isSelected = String(t.id) === String(assignedTeacherId);
                const tName = t.name || `${t.first_name || ''} ${t.last_name || ''}`.trim();
                optionsHtml += `<option value="${t.id}" ${isSelected ? 'selected' : ''}>${escapeHtml(tName)}</option>`;
            });

            rowHtml += `
                <td class="p-2 border-l border-slate-100 dark:border-slate-800 matrix-sec-col matrix-sec-${sec.id}" data-sec-id="${sec.id}">
                    <select class="matrix-teacher-select w-full text-xs px-2.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition"
                            data-course-id="${course.id}"
                            data-section-id="${sec.id}"
                            onchange="markMatrixCellChanged(this);">
                        ${optionsHtml}
                    </select>
                </td>
            `;
        });

        tr.innerHTML = rowHtml;
        tbody.appendChild(tr);
    });

    if (tableWrapper) tableWrapper.classList.remove('hidden');
    if (helpersBar) helpersBar.classList.remove('hidden');
    if (saveBtn) saveBtn.disabled = false;
}

function markMatrixCellChanged(selectEl) {
    if (selectEl.value) {
        selectEl.classList.add('border-indigo-500', 'bg-indigo-50/30', 'dark:bg-indigo-950/20');
    } else {
        selectEl.classList.remove('border-indigo-500', 'bg-indigo-50/30', 'dark:bg-indigo-950/20');
    }
}

function filterMatrixSections() {
    const filter = document.getElementById('matrix_section_filter')?.value;
    const allCols = document.querySelectorAll('.matrix-sec-col');

    if (!filter || filter === 'all') {
        allCols.forEach(col => col.classList.remove('hidden'));
    } else {
        allCols.forEach(col => {
            if (col.dataset.secId === filter) {
                col.classList.remove('hidden');
            } else {
                col.classList.add('hidden');
            }
        });
    }
}

function copyFirstSectionToAll() {
    const rows = document.querySelectorAll('#matrix-tbody tr.course-row');
    let copyCount = 0;
    rows.forEach(tr => {
        const selects = tr.querySelectorAll('.matrix-teacher-select');
        if (selects.length > 1) {
            const firstVal = selects[0].value;
            for (let i = 1; i < selects.length; i++) {
                if (selects[i].value !== firstVal) {
                    selects[i].value = firstVal;
                    markMatrixCellChanged(selects[i]);
                    copyCount++;
                }
            }
        }
    });

    if (copyCount > 0) {
        showToast(`Copied Section 1 teacher to other sections across ${copyCount} assignments! Click Save All Changes.`, 'info');
    } else {
        showToast('All sections already match Section 1.', 'info');
    }
}

function clearAllMatrixSelections() {
    if (!confirm('Clear all teacher selections in this matrix grid?')) return;
    document.querySelectorAll('.matrix-teacher-select').forEach(sel => {
        sel.value = '';
        markMatrixCellChanged(sel);
    });
}

function saveClassMatrix() {
    const classId = document.getElementById('matrix_class_id')?.value;
    const semesterId = document.getElementById('matrix_semester_id')?.value;
    const saveBtn = document.getElementById('btn-save-matrix');
    const saveText = document.getElementById('matrix-save-text');

    if (!classId) return;

    const selects = document.querySelectorAll('.matrix-teacher-select');
    const assignments = [];
    selects.forEach(s => {
        assignments.push({
            course_id: s.dataset.courseId,
            section_id: s.dataset.sectionId,
            teacher_id: s.value ? s.value : null
        });
    });

    if (saveBtn) {
        saveBtn.disabled = true;
        if (saveText) saveText.innerHTML = '<span class="inline-block animate-spin mr-1">⏳</span> Saving...';
    }

    fetch(MATRIX_SAVE_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN
        },
        body: JSON.stringify({
            class_id: classId,
            semester_id: semesterId,
            session_id: CURRENT_SESSION_ID,
            assignments: assignments
        })
    })
    .then(res => res.json())
    .then(data => {
        if (saveBtn) saveBtn.disabled = false;
        if (saveText) saveText.textContent = 'Save All Changes';

        if (data.status) {
            showToast('✓ Successfully updated subject teacher assignments!', 'success');
            setTimeout(() => {
                const url = new URL(window.location);
                url.searchParams.set('step', '6');
                window.location.href = url.toString();
            }, 800);
        } else {
            showToast(data.message || 'Failed to save assignments', 'error');
        }
    })
    .catch(err => {
        console.error('Save matrix error:', err);
        if (saveBtn) saveBtn.disabled = false;
        if (saveText) saveText.textContent = 'Save All Changes';
        showToast('Network error while saving matrix', 'error');
    });
}

function loadBulkOptions(classId) {
    const secList = document.getElementById('bulk-sections-list');
    const crsList = document.getElementById('bulk-courses-list');
    if (!classId || !secList || !crsList) return;

    secList.innerHTML = '<p class="text-[11px] text-slate-400">Loading sections...</p>';
    crsList.innerHTML = '<p class="text-[11px] text-slate-400">Loading subjects...</p>';

    fetch(`${SECTIONS_COURSES_URL}?class_id=${encodeURIComponent(classId)}`)
        .then(res => res.json())
        .then(data => {
            // Sections
            if (data.sections && data.sections.length > 0) {
                secList.innerHTML = '';
                data.sections.forEach(sec => {
                    const label = document.createElement('label');
                    label.className = 'flex items-center gap-2 p-2 rounded-xl hover:bg-white dark:hover:bg-slate-700/60 cursor-pointer text-xs text-slate-700 dark:text-slate-300 transition';
                    label.innerHTML = `
                        <input type="checkbox" name="bulk_section[]" value="${sec.id}" class="rounded text-indigo-600 focus:ring-indigo-500">
                        <span class="font-semibold">${escapeHtml(sec.section_name)}</span>
                    `;
                    secList.appendChild(label);
                });
            } else {
                secList.innerHTML = '<p class="text-[11px] text-slate-400">No sections found for this class.</p>';
            }

            // Courses
            if (data.courses && data.courses.length > 0) {
                crsList.innerHTML = '';
                data.courses.forEach(crs => {
                    const label = document.createElement('label');
                    label.className = 'flex items-center gap-2 p-2 rounded-xl hover:bg-white dark:hover:bg-slate-700/60 cursor-pointer text-xs text-slate-700 dark:text-slate-300 transition';
                    label.innerHTML = `
                        <input type="checkbox" name="bulk_course[]" value="${crs.id}" class="rounded text-indigo-600 focus:ring-indigo-500">
                        <div class="flex items-center gap-1.5">
                            <span class="font-semibold">${escapeHtml(crs.course_name)}</span>
                            ${crs.course_code ? `<span class="text-[10px] text-slate-400 font-mono">(${escapeHtml(crs.course_code)})</span>` : ''}
                        </div>
                    `;
                    crsList.appendChild(label);
                });
            } else {
                crsList.innerHTML = '<p class="text-[11px] text-slate-400">No subjects found for this class.</p>';
            }
        })
        .catch(err => {
            console.error('Error loading bulk options:', err);
            secList.innerHTML = '<p class="text-[11px] text-rose-500">Error loading sections.</p>';
            crsList.innerHTML = '<p class="text-[11px] text-rose-500">Error loading subjects.</p>';
        });
}

function toggleSelectAll(prefix, checked) {
    document.querySelectorAll(`input[name="${prefix}[]"]`).forEach(cb => {
        cb.checked = checked;
    });
}

function submitBulkAssign(event) {
    event.preventDefault();
    const form = event.target;
    const teacherId = document.getElementById('bulk_teacher_id')?.value;
    const classId = document.getElementById('bulk_class_id')?.value;
    const checkedSecs = form.querySelectorAll('input[name="bulk_section[]"]:checked');
    const checkedCourses = form.querySelectorAll('input[name="bulk_course[]"]:checked');

    if (!teacherId) {
        showToast('Please select a teacher', 'error');
        return;
    }
    if (!classId) {
        showToast('Please select a class', 'error');
        return;
    }
    if (checkedSecs.length === 0) {
        showToast('Please check at least one section', 'error');
        return;
    }
    if (checkedCourses.length === 0) {
        showToast('Please check at least one subject', 'error');
        return;
    }

    const btn = document.getElementById('btn-submit-bulk');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="inline-block animate-spin mr-1">⏳</span> Assigning...';
    }

    const formData = new FormData(form);

    fetch(BULK_ASSIGN_URL, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check2-all text-base"></i> Assign Selected Combinations';
        }
        if (data.status) {
            showToast(`✓ Assigned ${data.assigned_count || ''} combinations successfully!`, 'success');
            setTimeout(() => {
                const url = new URL(window.location);
                url.searchParams.set('step', '6');
                window.location.href = url.toString();
            }, 800);
        } else {
            showToast(data.message || 'Bulk assignment failed', 'error');
        }
    })
    .catch(err => {
        console.error('Bulk assign error:', err);
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check2-all text-base"></i> Assign Selected Combinations';
        }
        showToast('Network error while assigning in bulk', 'error');
    });
}

function filterAssignmentsTable() {
    const q = (document.getElementById('assignment-search')?.value || '').toLowerCase().trim();
    const rows = document.querySelectorAll('#assigned-teachers-tbody .assignment-row');

    rows.forEach(r => {
        if (!q) {
            r.style.display = '';
            return;
        }
        const teacher = r.querySelector('.search-teacher')?.textContent.toLowerCase() || '';
        const subject = r.querySelector('.search-subject')?.textContent.toLowerCase() || '';
        const cls = r.querySelector('.search-class')?.textContent.toLowerCase() || '';
        const term = r.querySelector('.search-term')?.textContent.toLowerCase() || '';

        if (teacher.includes(q) || subject.includes(q) || cls.includes(q) || term.includes(q)) {
            r.style.display = '';
        } else {
            r.style.display = 'none';
        }
    });
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function showToast(message, type = 'info') {
    let toast = document.getElementById('ag-global-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'ag-global-toast';
        toast.className = 'fixed bottom-5 right-5 z-50 px-4 py-3 rounded-2xl shadow-xl text-xs font-bold text-white transition-all transform duration-300 translate-y-12 opacity-0 pointer-events-none flex items-center gap-2';
        document.body.appendChild(toast);
    }

    if (type === 'success') {
        toast.className = 'fixed bottom-5 right-5 z-50 px-4 py-3 rounded-2xl shadow-xl text-xs font-bold text-white transition-all transform duration-300 bg-emerald-600 flex items-center gap-2 translate-y-0 opacity-100 pointer-events-auto';
    } else if (type === 'error') {
        toast.className = 'fixed bottom-5 right-5 z-50 px-4 py-3 rounded-2xl shadow-xl text-xs font-bold text-white transition-all transform duration-300 bg-rose-600 flex items-center gap-2 translate-y-0 opacity-100 pointer-events-auto';
    } else {
        toast.className = 'fixed bottom-5 right-5 z-50 px-4 py-3 rounded-2xl shadow-xl text-xs font-bold text-white transition-all transform duration-300 bg-indigo-600 flex items-center gap-2 translate-y-0 opacity-100 pointer-events-auto';
    }

    toast.textContent = message;

    setTimeout(() => {
        toast.classList.remove('translate-y-0', 'opacity-100', 'pointer-events-auto');
        toast.classList.add('translate-y-12', 'opacity-0', 'pointer-events-none');
    }, 3500);
}

document.addEventListener('DOMContentLoaded', () => {
    goStep({{ $activeStep }});
});

function toggleMarkSwitch(label) {
    const checkbox = document.getElementById('marksToggle');
    const track    = document.getElementById('marksToggleTrack');
    const thumb    = document.getElementById('marksToggleThumb');
    const status   = label.closest('div').querySelector('strong');

    checkbox.checked = !checkbox.checked;

    if (checkbox.checked) {
        track.style.backgroundColor = '#10b981';
        thumb.style.left = '22px';
        if (status) { status.textContent = 'OPEN / ALLOWED'; status.className = 'text-emerald-600'; }
    } else {
        track.style.backgroundColor = '#cbd5e1';
        thumb.style.left = '2px';
        if (status) { status.textContent = 'LOCKED / DISALLOWED'; status.className = 'text-slate-400'; }
    }
}
</script>
@endsection

