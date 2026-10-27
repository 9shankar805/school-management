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
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            {{-- Left: Assign Teacher Form --}}
            <div class="lg:col-span-5">
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
                    <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <span class="w-2 h-5 bg-indigo-600 rounded-full"></span>
                        <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <i class="bi bi-person-badge text-indigo-500"></i> Assign Subject Teacher
                        </h2>
                    </div>

                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                        Map teachers to classes, sections, and subjects for the current term.
                    </p>

                    <form action="{{ route('school.teacher.assign') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="session_id" value="{{ $current_school_session_id }}">

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Select Teacher <span class="text-rose-500">*</span>
                            </label>
                            <select name="teacher_id" required
                                    class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
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
                            <select name="semester_id" required
                                    class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
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
                            <select onchange="getSectionsAndCourses(this);" name="class_id" required
                                    class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
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
                            <select id="section-select" name="section_id" required
                                    class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                                <option value="" disabled selected>Select class first</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Subject / Course <span class="text-rose-500">*</span>
                            </label>
                            <select id="course-select" name="course_id" required
                                    class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                                <option value="" disabled selected>Select class first</option>
                            </select>
                        </div>

                        <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-600/25 transition flex items-center justify-center gap-2 cursor-pointer">
                            <i class="bi bi-check2"></i> Assign Teacher
                        </button>
                    </form>
                </div>
            </div>

            {{-- Right: Assigned Teachers List Table --}}
            <div class="lg:col-span-7">
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6 h-full flex flex-col">
                    <div class="flex items-center justify-between gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-5 bg-pink-600 rounded-full"></span>
                            <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Current Teacher Assignments</h2>
                        </div>
                        <span class="text-xs font-semibold text-slate-400">{{ count($assigned_teachers ?? []) }} Assignment(s)</span>
                    </div>

                    <div class="overflow-x-auto rounded-2xl border border-slate-100 dark:border-slate-800 flex-1">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300">
                                <tr>
                                    <th class="p-3 font-semibold w-12 text-center">#</th>
                                    <th class="p-3 font-semibold">Teacher</th>
                                    <th class="p-3 font-semibold">Subject</th>
                                    <th class="p-3 font-semibold">Class / Section</th>
                                    <th class="p-3 font-semibold">Term</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-600 dark:text-slate-300">
                                @forelse($assigned_teachers ?? [] as $i => $at)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                    <td class="p-3 text-center text-slate-400 font-mono">{{ $i + 1 }}</td>
                                    <td class="p-3 font-bold text-slate-800 dark:text-slate-200">
                                        <i class="bi bi-person-circle text-indigo-500 me-1"></i> {{ $at->teacher->first_name ?? '' }} {{ $at->teacher->last_name ?? '' }}
                                    </td>
                                    <td class="p-3 font-semibold text-indigo-600 dark:text-indigo-400">
                                        {{ $at->course->course_name ?? 'N/A' }}
                                    </td>
                                    <td class="p-3 text-slate-700 dark:text-slate-300">
                                        {{ $at->schoolClass->class_name ?? '' }} &bull; {{ $at->section->section_name ?? '' }}
                                    </td>
                                    <td class="p-3 text-slate-500 text-[11px]">
                                        {{ $at->semester->semester_name ?? 'All' }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="p-6 text-center text-slate-400">No teachers assigned yet.</td>
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
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="marks_submission_status" value="on" {{ ($academic_setting->marks_submission_status ?? 'off') == 'on' ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
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

document.addEventListener('DOMContentLoaded', () => {
    goStep({{ $activeStep }});
});
</script>
@endsection
