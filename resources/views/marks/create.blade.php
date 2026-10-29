@extends('layouts.app')

@section('content')
<style>
/* ── Mobile select fix: prevent iOS zoom + ensure tappable ── */
@media (max-width: 768px) {
    select, input[type="number"], input[type="text"], input[type="date"], textarea {
        font-size: 16px !important; /* prevents iOS auto-zoom */
        -webkit-appearance: none;
        min-height: 44px; /* Apple HIG minimum touch target */
    }
    select { -webkit-appearance: menulist; }
    .mark-input { min-height: 40px !important; font-size: 16px !important; }
    /* Ensure filter card doesn't clip dropdown */
    #marksFilterForm { overflow: visible !important; }
    .overflow-x-auto { -webkit-overflow-scrolling: touch; }
}
</style>
{{-- Auto-save indicator --}}
<div id="autoSaveIndicator" class="fixed bottom-20 right-4 sm:bottom-6 sm:right-6 z-50 px-3 py-1.5 rounded-xl text-[11px] font-bold shadow-lg bg-emerald-600 text-white" style="opacity:0;transition:opacity 0.3s;"></div>

<div class="px-2 sm:px-4 lg:px-8 py-2 sm:py-3 max-w-7xl mx-auto space-y-2 sm:space-y-3">

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <span class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-indigo-600 to-indigo-500 text-white flex items-center justify-center font-bold text-xl shadow-md shadow-indigo-500/20">
                    <i class="bi bi-pencil-square"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight flex items-center gap-2">
                        Enter Marks & Grades
                        @if(!empty($isAdmin))
                        <span class="text-[11px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 flex items-center gap-1">
                            <i class="bi bi-shield-lock-fill text-indigo-500"></i> Admin Mode (All Subjects)
                        </span>
                        @elseif(!empty($isClassTeacher))
                        <span class="text-[11px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800 flex items-center gap-1">
                            <i class="bi bi-star-fill text-amber-500"></i> Class Teacher Mode (Class In-Charge · All Subjects)
                        </span>
                        @else
                        <span class="text-[11px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 flex items-center gap-1">
                            <i class="bi bi-person-badge-fill text-emerald-500"></i> Subject Teacher Mode (Assigned Subject Only)
                        </span>
                        @endif
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        @if(!empty($isAdmin) || !empty($isClassTeacher))
                            Direct master grade ledger across all subjects with individual class marksheet printing & evaluations
                        @else
                            Record marks and submit evaluations for your assigned subjects
                        @endif
                    </p>
                </div>
            </div>
        </div>
        <nav aria-label="breadcrumb" class="hidden sm:flex">
            <ol class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-800/80 px-3.5 py-2 rounded-xl border border-slate-200/70 dark:border-slate-800 shadow-xs">
                <li><a href="{{ route('home') }}" class="hover:text-indigo-600 transition flex items-center gap-1"><i class="bi bi-house-door"></i> Home</a></li>
                <li class="text-slate-300">/</li>
                <li><a href="{{ route('course.mark.index') }}" class="hover:text-indigo-600 transition">Marks</a></li>
                <li class="text-slate-300">/</li>
                <li class="font-semibold text-slate-800 dark:text-slate-200">Enter</li>
            </ol>
        </nav>
    </div>

    @include('session-messages')

    {{-- Marks Submission Window Status Bar --}}
    @php
        $isWindowOpen = isset($academic_setting) && is_array($academic_setting) && isset($academic_setting['marks_submission_status']) && $academic_setting['marks_submission_status'] === 'on';
    @endphp

    @if($isWindowOpen)
        <div class="p-3 rounded-2xl bg-gradient-to-r from-emerald-50 via-teal-50 to-white dark:from-emerald-950/30 dark:via-teal-950/20 dark:to-slate-900 border border-emerald-200 dark:border-emerald-800/60 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-900/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg flex-shrink-0">
                    <i class="bi bi-unlock-fill"></i>
                </span>
                <div>
                    <p class="text-xs font-bold text-emerald-900 dark:text-emerald-200 flex items-center gap-2">
                        Marks Submission Window is OPEN
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/80 dark:text-emerald-300">
                            Active
                        </span>
                    </p>
                    <p class="text-[11px] text-emerald-700/80 dark:text-emerald-400">All teachers and administrators can record and submit term evaluations.</p>
                </div>
            </div>
            @if(!empty($isAdmin))
                <form action="{{ route('school.final.marks.submission.status.update') }}" method="POST" class="flex-shrink-0">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 transition shadow-xs flex items-center gap-1.5" onclick="return confirm('Lock submission window for teachers?')">
                        <i class="bi bi-lock"></i> Close Window for Teachers
                    </button>
                </form>
            @endif
        </div>
    @else
        <div class="p-3 rounded-2xl bg-gradient-to-r from-amber-50 via-orange-50 to-white dark:from-amber-950/30 dark:via-orange-950/20 dark:to-slate-900 border border-amber-200 dark:border-amber-800/60 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-900/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg flex-shrink-0">
                    <i class="bi bi-shield-lock-fill"></i>
                </span>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="text-xs font-bold text-amber-900 dark:text-amber-200">
                            Marks Submission Window is Closed for Teachers
                        </p>
                        @if(!empty($isAdmin))
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                <i class="bi bi-lightning-charge-fill text-amber-500"></i> Admin Direct Access Authorized
                            </span>
                        @endif
                    </div>
                    <p class="text-[11px] text-amber-700/80 dark:text-amber-400">
                        @if(!empty($isAdmin))
                            As an administrator, you have full privileges to enter and update marks directly anytime.
                        @else
                            Marks submission is currently locked by administration. Please contact your Exam Controller.
                        @endif
                    </p>
                </div>
            </div>
            @if(!empty($isAdmin))
                <form action="{{ route('school.final.marks.submission.status.update') }}" method="POST" class="flex-shrink-0">
                    @csrf
                    <input type="hidden" name="marks_submission_status" value="on">
                    <button type="submit" class="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-xs flex items-center gap-1.5">
                        <i class="bi bi-unlock-fill"></i> Open Window for Teachers
                    </button>
                </form>
            @endif
        </div>
    @endif


    {{-- Filter Card --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="px-4 py-3 flex items-center gap-2 border-b border-slate-100 dark:border-slate-800">
            <i class="bi bi-funnel-fill text-indigo-600 text-base"></i>
            <span class="text-sm font-bold text-slate-800 dark:text-slate-100">Select Examination, Class &amp; Entry Scope</span>
        </div>
        <form action="{{ route('course.mark.create') }}" method="GET" id="marksFilterForm" class="p-4">
            <div class="grid grid-cols-2 gap-3 mb-3">
                {{-- Semester --}}
                <div>
                    <label for="filter_semester_id" class="flex items-center gap-1.5 text-xs font-semibold text-indigo-700 dark:text-indigo-400 mb-1">
                        <i class="bi bi-calendar2-check text-sm"></i> Terminal Exam / Semester <span class="text-rose-500">*</span>
                    </label>
                    <select id="filter_semester_id" name="semester_id" required
                            class="w-full px-3 py-2.5 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium outline-none focus:border-indigo-500 transition"
                            style="font-size:16px;-webkit-appearance:auto;">
                        @isset($semesters)
                            @foreach ($semesters as $sem)
                                <option value="{{ $sem->id }}" {{ $semester_id == $sem->id ? 'selected' : '' }}>{{ $sem->semester_name }}</option>
                            @endforeach
                        @endisset
                    </select>
                </div>
                {{-- Class --}}
                <div>
                    <label for="filter_class_id" class="flex items-center gap-1.5 text-xs font-semibold text-indigo-700 dark:text-indigo-400 mb-1">
                        <i class="bi bi-mortarboard text-sm"></i> Class / Grade <span class="text-rose-500">*</span>
                    </label>
                    <select id="filter_class_id" name="class_id" required onchange="handleClassChange(this.value)"
                            class="w-full px-3 py-2.5 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium outline-none focus:border-indigo-500 transition"
                            style="font-size:16px;-webkit-appearance:auto;">
                        <option value="">-- Choose Class --</option>
                        @isset($school_classes)
                            @foreach ($school_classes as $sc)
                                <option value="{{ $sc->id }}" {{ $class_id == $sc->id ? 'selected' : '' }}>{{ $sc->class_name }}</option>
                            @endforeach
                        @endisset
                    </select>
                </div>
                {{-- Section --}}
                <div>
                    <label for="filter_section_id" class="flex items-center gap-1.5 text-xs font-semibold text-indigo-700 dark:text-indigo-400 mb-1">
                        <i class="bi bi-people text-sm"></i> Section
                    </label>
                    <select id="filter_section_id" name="section_id"
                            class="w-full px-3 py-2.5 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium outline-none focus:border-indigo-500 transition"
                            style="font-size:16px;-webkit-appearance:menulist;min-height:48px;">
                        <option value="0">{{ !empty($canEditAllSubjects) ? 'All Sections' : 'All Assigned' }}</option>
                    </select>
                </div>
                {{-- Subject --}}
                <div>
                    <label for="filter_course_id" class="flex items-center gap-1.5 text-xs font-semibold text-indigo-700 dark:text-indigo-400 mb-1">
                        <i class="bi bi-journal-bookmark text-sm"></i> Subject / Scope <span class="text-rose-500">*</span>
                    </label>
                    <select id="filter_course_id" name="course_id" required
                            class="w-full px-3 py-2.5 rounded-xl border-2 border-indigo-200 dark:border-indigo-800 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium outline-none focus:border-indigo-500 transition"
                            style="font-size:16px;-webkit-appearance:menulist;min-height:48px;">
                        @if(!empty($canEditAllSubjects))
                            <option value="all" {{ ($course_id === 'all' || empty($course_id) || $course_id == '0') ? 'selected' : '' }}>All Subjects</option>
                        @else
                            <option value="">-- Select Subject --</option>
                        @endif
                    </select>
                </div>
            </div>
            <button type="submit" class="w-full py-3.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 active:scale-[0.98] text-white font-bold text-sm shadow-lg shadow-indigo-600/25 transition flex items-center justify-center gap-2">
                <i class="bi bi-grid-3x3-gap-fill"></i> Load Grade Sheet <i class="bi bi-arrow-right"></i>
            </button>
        </form>
    </div>
    @if($class_id)
        {{-- MAIN EVALUATION FORM CONTAINER --}}
        @if($isAllSubjectsMode)
            {{-- MODE 1: MASTER GRADE LEDGER (ALL SUBJECTS SIMULTANEOUSLY) --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
                <form action="{{ route('course.mark.store') }}" method="POST" id="masterMarksForm">
                    @csrf
                    <input type="hidden" name="session_id" value="{{ $current_school_session_id }}">
                    <input type="hidden" name="semester_id" value="{{ $semester_id }}">
                    <input type="hidden" name="class_id" value="{{ $class_id }}">
                    <input type="hidden" name="section_id" value="{{ $section_id }}">
                    <input type="hidden" name="course_id" value="all">

                    {{-- Toolbar: Autofill Tools & Actions --}}
                    <div class="px-4 py-2.5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 flex flex-wrap items-center justify-end gap-2">

                        <div class="flex items-center gap-2 flex-wrap">
                            @if(!empty($canEditAllSubjects))
                                <a href="{{ route('results.class.report-cards', ['class_id' => $class_id, 'section_id' => $section_id, 'semester_id' => $semester_id, 'stream' => 1]) }}"
                                   target="_blank"
                                   data-no-loader
                                   class="px-3.5 py-1.5 rounded-xl border border-indigo-200 dark:border-indigo-800/80 bg-indigo-50/70 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 text-xs font-bold transition flex items-center gap-1.5 shadow-xs"
                                   title="Print individual official report cards for every student in the whole class on separate A4 pages">
                                    <i class="bi bi-printer-fill text-indigo-600 dark:text-indigo-400"></i>
                                    <span>Print Class Marksheets (Individual A4)</span>
                                </a>
                            @endif
                            <button type="button" onclick="fillZeroForEmpty()" class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-semibold transition flex items-center gap-1.5 shadow-xs" title="Set any remaining empty marks as 0 (Absent)">
                                <i class="bi bi-magic text-indigo-500"></i> <span class="hidden sm:inline">Set Empty to 0</span><span class="sm:hidden">Absent</span>
                            </button>
                            <button type="button" onclick="calculateAllRows()" class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-semibold transition flex items-center gap-1.5 shadow-xs">
                                <i class="bi bi-arrow-clockwise text-emerald-500"></i> <span class="hidden sm:inline">Recalculate</span>
                            </button>
                            <button type="submit" class="px-4 sm:px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/20 transition flex items-center gap-2">
                                <i class="bi bi-cloud-arrow-up-fill"></i> <span>Save All</span>
                            </button>
                        </div>
                    </div>

                    @if(count($classCourses) > 0 && count($sectionStudents) > 0)
                    {{-- Search: between Save All and table --}}
                    <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900">
                        <div class="relative w-full max-w-sm">
                            <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input type="text" id="studentQuickFilter" placeholder="Filter by student name or roll..." class="w-full text-xs pl-9 pr-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500" style="font-size:16px !important; min-height:40px;">
                        </div>
                    </div>
                        <div class="overflow-x-auto relative" style="-webkit-overflow-scrolling:touch; touch-action:pan-x pan-y;">
                            <table class="w-full text-left text-xs border-collapse" id="masterMarksTable">
                                <thead>
                                    <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-100/70 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 font-bold">
                                        <th class="py-3 px-2 sm:px-4 min-w-[50px] sm:min-w-[70px] sticky left-0 bg-slate-100 dark:bg-slate-800 z-10 text-center">
                                            Roll
                                        </th>
                                        <th class="py-3 px-2 sm:px-4 min-w-[130px] sm:min-w-[200px] sticky left-[50px] sm:left-[70px] bg-slate-100 dark:bg-slate-800 z-10">
                                            Student
                                        </th>
                                        @foreach($classCourses as $course)
                                            <th class="py-3 px-3 min-w-[120px] text-center border-l border-slate-200/60 dark:border-slate-800">
                                                <div class="font-bold text-slate-800 dark:text-slate-100 truncate" title="{{ $course->course_name }}">
                                                    {{ $course->course_name }}
                                                </div>
                                                <div class="flex items-center justify-center gap-1 text-[10px] font-normal text-slate-500 dark:text-slate-400 mt-0.5">
                                                    <span>Max: <strong class="text-indigo-600 dark:text-indigo-400">{{ $courseMaxMarks[$course->id] ?? 100 }}</strong></span>
                                                </div>
                                            </th>
                                        @endforeach
                                        <th class="py-3 px-3 min-w-[100px] text-center bg-indigo-50/60 dark:bg-indigo-950/30 border-l border-indigo-100 dark:border-indigo-900/40 text-indigo-950 dark:text-indigo-200 font-bold hidden sm:table-cell">
                                            Total
                                        </th>
                                        <th class="py-3 px-3 min-w-[80px] text-center bg-indigo-50/60 dark:bg-indigo-950/30 text-indigo-950 dark:text-indigo-200 font-bold hidden sm:table-cell">
                                            %
                                        </th>
                                        <th class="py-3 px-3 min-w-[90px] text-center bg-indigo-50/60 dark:bg-indigo-950/30 text-indigo-950 dark:text-indigo-200 font-bold hidden md:table-cell">
                                            GPA
                                        </th>
                                        <th class="py-3 px-3 min-w-[90px] text-center bg-indigo-50/60 dark:bg-indigo-950/30 text-indigo-950 dark:text-indigo-200 font-bold">
                                            Grade
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                                    @foreach($sectionStudents as $sIndex => $secStudent)
                                        @php
                                            $stu = $secStudent->student ?? null;
                                            $stuId = $stu->id ?? $secStudent->student_id;
                                            $rollNo = $secStudent->roll_no ?? $stu?->roll_no ?? ($sIndex + 1);
                                            $fullName = trim(($stu->first_name ?? '') . ' ' . ($stu->last_name ?? ''));
                                            if (empty($fullName)) {
                                                $fullName = 'Student #' . $stuId;
                                            }
                                        @endphp
                                        <tr class="student-mark-row hover:bg-indigo-50/20 dark:hover:bg-slate-800/40 transition group" data-student-id="{{ $stuId }}" data-student-name="{{ strtolower($fullName) }}" data-roll-no="{{ $rollNo }}">
                                            {{-- Roll No --}}
                                            <td class="py-3 px-4 font-mono font-bold text-center text-indigo-600 dark:text-indigo-400 sticky left-0 bg-white dark:bg-slate-900 group-hover:bg-indigo-50/20 dark:group-hover:bg-slate-800/40 z-10">
                                                {{ $rollNo }}
                                            </td>

                                            {{-- Student Name & Avatar --}}
                                            <td class="py-3 px-4 font-semibold text-slate-800 dark:text-slate-100 sticky left-[50px] sm:left-[70px] bg-white dark:bg-slate-900 group-hover:bg-indigo-50/20 dark:group-hover:bg-slate-800/40 z-10">
                                                <div class="flex items-center gap-2.5">
                                                    <div class="w-7 h-7 rounded-full bg-gradient-to-tr from-indigo-500 to-indigo-600 text-white font-bold flex items-center justify-center text-xs shadow-xs flex-shrink-0">
                                                        {{ strtoupper(substr($fullName, 0, 1)) }}
                                                    </div>
                                                    <div class="truncate">
                                                        <span class="block truncate">{{ $fullName }}</span>
                                                        <span class="text-[10px] text-slate-400 font-normal">ID: #{{ $stuId }}</span>
                                                    </div>
                                                </div>
                                            </td>

                                            {{-- Course Marks Inputs --}}
                                            @foreach($classCourses as $course)
                                                @php
                                                    $val = $marksMatrix[$stuId][$course->id] ?? null;
                                                    $maxM = $courseMaxMarks[$course->id] ?? 100;
                                                @endphp
                                                <td class="py-2.5 px-3 border-l border-slate-100 dark:border-slate-800/80 text-center">
                                                    <input type="number" 
                                                           step="0.01" 
                                                           min="0" 
                                                           max="{{ $maxM }}" 
                                                           name="student_course_mark[{{ $stuId }}][{{ $course->id }}]" 
                                                           value="{{ $val !== null ? $val : '' }}"
                                                           placeholder="—"
                                                           data-student-id="{{ $stuId }}"
                                                           data-course-id="{{ $course->id }}"
                                                           data-max-marks="{{ $maxM }}"
                                                           class="mark-input w-full max-w-[70px] sm:max-w-[90px] text-center font-mono text-xs font-bold py-1.5 px-1 sm:px-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                                                </td>
                                            @endforeach

                                            {{-- Dynamic Row Calculations --}}
                                            <td class="py-3 px-3 text-center font-mono font-bold text-slate-700 dark:text-slate-200 bg-indigo-50/20 dark:bg-indigo-950/10 border-l border-indigo-100/50 dark:border-indigo-900/30 hidden sm:table-cell">
                                                <span class="row-total-marks">—</span>
                                            </td>
                                            <td class="py-3 px-3 text-center font-mono font-semibold text-slate-700 dark:text-slate-200 bg-indigo-50/20 dark:bg-indigo-950/10 hidden sm:table-cell">
                                                <span class="row-percentage">—</span>
                                            </td>
                                            <td class="py-3 px-3 text-center font-mono font-bold bg-indigo-50/20 dark:bg-indigo-950/10 hidden md:table-cell">
                                                <span class="row-gpa text-slate-400">—</span>
                                            </td>
                                            <td class="py-3 px-3 text-center bg-indigo-50/20 dark:bg-indigo-950/10">
                                                <span class="row-grade-badge px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-400">
                                                    —
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Bottom Sticky Bar with Action Buttons --}}
                        <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/80 flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                <span>Tip: Use <kbd class="px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 font-mono text-[10px] text-slate-700 dark:text-slate-200 font-semibold shadow-sm">Tab</kbd> or <kbd class="px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 font-mono text-[10px] text-slate-700 dark:text-slate-200 font-semibold shadow-sm">Arrow Keys</kbd> to quickly navigate cells. Press <kbd class="px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 font-mono text-[10px] text-slate-700 dark:text-slate-200 font-semibold shadow-sm">Ctrl+S</kbd> / <kbd class="px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 font-mono text-[10px] text-slate-700 dark:text-slate-200 font-semibold shadow-sm">⌘+S</kbd> to save.</span>
                            </div>

                            <div class="flex items-center gap-3">
                                <a href="{{ route('course.mark.index') }}" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 text-xs font-semibold transition">
                                    Cancel
                                </a>
                                <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-xs font-bold shadow-lg shadow-indigo-600/30 transition flex items-center gap-2">
                                    <i class="bi bi-check-circle-fill text-sm"></i>
                                    <span>Save All Marks</span>
                                </button>
                            </div>
                        </div>
                    @elseif(count($classCourses) === 0)
                        <div class="py-16 text-center">
                            <span class="w-16 h-16 rounded-full bg-amber-50 dark:bg-amber-950/40 text-amber-500 flex items-center justify-center text-3xl mx-auto mb-3">
                                <i class="bi bi-journal-x"></i>
                            </span>
                            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">No Subjects Assigned to this Class</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                                Please assign subjects/courses to {{ $selectedClass->class_name ?? 'this class' }} in Academic Settings before entering marks.
                            </p>
                        </div>
                    @else
                        <div class="py-16 text-center">
                            <span class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center text-3xl mx-auto mb-3">
                                <i class="bi bi-people"></i>
                            </span>
                            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">No Students Enrolled</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                                No active student enrollments found for {{ $selectedClass->class_name ?? 'this class' }} in the current academic session.
                            </p>
                        </div>
                    @endif
                </form>
            </div>
        @else
            {{-- MODE 2: SINGLE SUBJECT EVALUATION --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <span>Subject: {{ $selectedCourse->course_name ?? 'Course #'.$course_id }}</span>
                            <span class="text-xs font-normal text-slate-400">({{ $selectedCourse->course_type ?? 'Theory' }})</span>
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Entering marks for Class: <strong>{{ $selectedClass->class_name ?? 'Class #'.$class_id }}</strong> 
                            • Section: <strong>{{ $selectedSection->section_name ?? 'All Sections' }}</strong>
                        </p>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        @if(!empty($canEditAllSubjects))
                            <a href="{{ route('results.class.report-cards', ['class_id' => $class_id, 'section_id' => $section_id, 'semester_id' => $semester_id, 'stream' => 1]) }}" 
                               target="_blank" 
                               data-no-loader
                               class="px-4 py-2 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 border border-indigo-200 dark:border-indigo-800 text-xs font-bold transition flex items-center gap-1.5 shadow-xs"
                               title="Print individual official report cards for every student in the whole class on separate A4 pages">
                                <i class="bi bi-printer-fill text-indigo-600 dark:text-indigo-400"></i>
                                <span>Print Class Marksheets (Individual A4)</span>
                            </a>
                        @endif

                        @if (!$final_marks_submitted && count($exams) > 0 && ($isWindowOpen || !empty($isAdmin)))
                            <a href="{{ route('course.final.mark.submit.show', ['class_id' => $class_id, 'class_name' => $selectedClass->class_name ?? '', 'section_id' => $section_id, 'section_name' => $selectedSection->section_name ?? '', 'course_id' => $course_id, 'course_name' => $selectedCourse->course_name ?? '', 'semester_id' => $semester_id]) }}" 
                               class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5" 
                               onclick="return confirm('Are you sure you want to submit final marks for this course?')">
                                <i class="bi bi-check2-all"></i> Submit Final Marks
                            </a>
                        @endif
                    </div>
                </div>

                <form action="{{ route('course.mark.store') }}" method="POST" class="p-6">
                    @csrf
                    <input type="hidden" name="session_id" value="{{ $current_school_session_id }}">
                    <input type="hidden" name="semester_id" value="{{ $semester_id }}">
                    <input type="hidden" name="class_id" value="{{ $class_id }}">
                    <input type="hidden" name="section_id" value="{{ $section_id }}">
                    <input type="hidden" name="course_id" value="{{ $course_id }}">

                    @if(count($exams) > 0)
                        <div class="overflow-x-auto mb-6">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-semibold bg-slate-50/50 dark:bg-slate-800/20">
                                        <th class="py-3 px-4 min-w-[80px]">Roll No.</th>
                                        <th class="py-3 px-4 min-w-[200px]">Student Name</th>
                                        @foreach ($exams as $exam)
                                            <th class="py-3 px-4 min-w-[140px]">
                                                <a href="{{ route('exam.rule.show', ['exam_id' => $exam->id]) }}" class="hover:text-indigo-600 font-bold" title="View exam rules">
                                                    {{ $exam->exam_name }}
                                                </a>
                                                @if($exam_rules_by_exam_id->has($exam->id))
                                                    <span class="block text-[10px] text-slate-400 font-normal">Max: {{ $exam_rules_by_exam_id[$exam->id] }} pts</span>
                                                @endif
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @if(count($students_with_marks) > 0)
                                        @foreach ($students_with_marks as $id => $students_with_mark)
                                            @php 
                                                $markedExamCount = 0; 
                                                $stu = $students_with_mark[0]->student ?? null;
                                                $stuName = trim(($stu->first_name ?? '') . ' ' . ($stu->last_name ?? ''));
                                            @endphp
                                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                                                <td class="py-3 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                                    {{ $stu?->roll_no ?? '—' }}
                                                </td>
                                                <td class="py-3 px-4 font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                                    <div class="w-7 h-7 rounded-full bg-indigo-50 text-indigo-600 font-bold flex items-center justify-center text-xs">
                                                        {{ strtoupper(substr($stuName ?: 'S', 0, 1)) }}
                                                    </div>
                                                    {{ $stuName }}
                                                </td>
                                                @foreach ($students_with_mark as $st)
                                                    @php $currentExamId = $exams[$markedExamCount]->id; @endphp
                                                    <td class="py-3 px-4">
                                                        <input type="number" step="0.01" min="0" @if($exam_rules_by_exam_id->has($currentExamId)) max="{{ $exam_rules_by_exam_id[$currentExamId] }}" @endif class="w-full text-xs px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20" name="student_mark[{{ $stu->id ?? $st->student_id }}][{{ $currentExamId }}]" value="{{ $st->marks }}">
                                                    </td>
                                                    @php $markedExamCount++; @endphp
                                                @endforeach

                                                @php
                                                    $students_with_markCount = count($students_with_mark);
                                                    $examCount = count($exams);
                                                    $gt = ($students_with_markCount < $examCount) ? ($examCount - $students_with_markCount) : 0;
                                                @endphp
                                                @for ($i = 0; $i < $gt; $i++)
                                                    @php $currentExamId = $exams[$markedExamCount]->id; @endphp
                                                    <td class="py-3 px-4">
                                                        <input type="number" step="0.01" min="0" @if($exam_rules_by_exam_id->has($currentExamId)) max="{{ $exam_rules_by_exam_id[$currentExamId] }}" @endif class="w-full text-xs px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20" name="student_mark[{{ $stu->id ?? $students_with_mark[0]->student_id }}][{{ $currentExamId }}]">
                                                    </td>
                                                    @php $markedExamCount++; @endphp
                                                @endfor
                                            </tr>
                                        @endforeach
                                    @elseif(count($sectionStudents) > 0)
                                        @foreach ($sectionStudents as $sectionStudent)
                                            @php
                                                $stu = $sectionStudent->student ?? null;
                                                $rollNo = $sectionStudent->roll_no ?? $stu?->roll_no ?? '—';
                                                $stuName = trim(($stu->first_name ?? '') . ' ' . ($stu->last_name ?? ''));
                                            @endphp
                                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                                                <td class="py-3 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                                    {{ $rollNo }}
                                                </td>
                                                <td class="py-3 px-4 font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                                    <div class="w-7 h-7 rounded-full bg-indigo-50 text-indigo-600 font-bold flex items-center justify-center text-xs">
                                                        {{ strtoupper(substr($stuName ?: 'S', 0, 1)) }}
                                                    </div>
                                                    {{ $stuName }}
                                                </td>
                                                @foreach ($exams as $exam)
                                                    <td class="py-3 px-4">
                                                        <input type="number" step="0.01" min="0" @if($exam_rules_by_exam_id->has($exam->id)) max="{{ $exam_rules_by_exam_id[$exam->id] }}" @endif class="w-full text-xs px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20" name="student_mark[{{ $stu->id ?? $sectionStudent->student_id }}][{{ $exam->id }}]">
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                            @if(!$final_marks_submitted)
                                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/20 transition flex items-center gap-2">
                                    <i class="bi bi-check2 text-base"></i> Save Subject Marks
                                </button>
                            @else
                                <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 dark:bg-emerald-950/40 px-3 py-1.5 rounded-xl border border-emerald-200 dark:border-emerald-900">
                                    <i class="bi bi-check-circle me-1"></i> Final Marks Already Submitted
                                </span>
                            @endif
                        </div>
                    @else
                        <div class="py-12 text-center">
                            <i class="bi bi-journal-x text-4xl text-slate-300 dark:text-slate-600 mb-2 block"></i>
                            <p class="text-xs font-bold text-slate-700 dark:text-slate-300">No exams configured for this session/semester.</p>
                            <p class="text-[11px] text-slate-400 mt-1">Please create exams first before entering marks.</p>
                        </div>
                    @endif
                </form>
            </div>
        @endif
    @endif

    @include('layouts.footer')
</div>

{{-- Scripts for dynamic Section/Course loading, keyboard shortcuts, and live GPA calculations --}}
<script>
const IS_ADMIN = {{ !empty($isAdmin) ? 'true' : 'false' }};
const IS_CLASS_TEACHER = {{ !empty($isClassTeacher) ? 'true' : 'false' }};
const CAN_EDIT_ALL = {{ !empty($canEditAllSubjects) ? 'true' : 'false' }};
const PRESELECTED_SECTION = "{{ $section_id }}";
const PRESELECTED_COURSE = "{{ $course_id }}";

function handleClassChange(classId) {
    loadSectionsAndCourses(classId, '0', (IS_ADMIN || IS_CLASS_TEACHER) ? 'all' : '');
}

function loadSectionsAndCourses(classId, preselectedSection, preselectedCourse) {
    const selectSection = document.getElementById('filter_section_id');
    const selectCourse = document.getElementById('filter_course_id');

    if (!selectSection || !selectCourse) return;

    if (!classId) {
        selectSection.innerHTML = '<option value="0">' + (CAN_EDIT_ALL ? 'All Sections (Entire Class)' : 'All Assigned Sections') + '</option>';
        selectCourse.innerHTML = CAN_EDIT_ALL ? '<option value="all">⚡ All Subjects at Once (Master Grade Ledger)</option>' : '<option value="">-- Choose Your Assigned Subject --</option>';
        return;
    }

     selectSection.innerHTML = '<option value="0">Loading sections...</option>';
     selectCourse.innerHTML = '<option value="">Loading courses...</option>';

     const _ctrl = new AbortController();
     const _tout = setTimeout(() => {
         _ctrl.abort();
         selectSection.innerHTML = '<option value="0">All Sections</option>';
         selectCourse.innerHTML = CAN_EDIT_ALL ? '<option value="all">⚡ All Subjects at Once</option>' : '<option value="">-- Select Subject --</option>';
     }, 8000);

     fetch('{{ route("get.sections.courses.by.classId") }}?class_id=' + classId + '&semester_id=' + (document.getElementById('filter_semester_id')?.value||'') + '&_t=' + Date.now(), { signal: _ctrl.signal })
         .then(res => res.json())
         .then(data => {
             clearTimeout(_tout);
            const hasAllPrivilege = IS_ADMIN || !!data.is_class_teacher || CAN_EDIT_ALL;

            // Populate Sections
            let secHtml = '<option value="0">' + (hasAllPrivilege ? 'All Sections (Entire Class)' : 'All Assigned Sections') + '</option>';
            if (data.sections && data.sections.length > 0) {
                data.sections.forEach(sec => {
                    const isSel = String(sec.id) === String(preselectedSection) ? 'selected' : '';
                    secHtml += `<option value="${sec.id}" ${isSel}>${sec.section_name} (Room ${sec.room_no || 'N/A'})</option>`;
                });
            }
            selectSection.innerHTML = secHtml;

            // Populate Courses
            let crsHtml = '';
            if (hasAllPrivilege) {
                const isAllSelected = (!preselectedCourse || preselectedCourse === 'all' || preselectedCourse === '0') ? 'selected' : '';
                crsHtml = `<option value="all" ${isAllSelected}>⚡ All Subjects at Once (Master Grade Ledger)</option>`;
            } else {
                crsHtml = `<option value="">-- Choose Your Assigned Subject --</option>`;
            }
            
            if (data.courses && data.courses.length > 0) {
                if (hasAllPrivilege) {
                    crsHtml += `<optgroup label="Individual Subjects">`;
                }
                data.courses.forEach((crs, idx) => {
                    const shouldSelect = (String(crs.id) === String(preselectedCourse)) || (!hasAllPrivilege && (data.courses.length === 1 || (!preselectedCourse && idx === 0)));
                    const isSel = shouldSelect ? 'selected' : '';
                    crsHtml += `<option value="${crs.id}" ${isSel}>${crs.course_name} (${crs.course_type || 'Theory'})</option>`;
                });
                if (hasAllPrivilege) {
                    crsHtml += `</optgroup>`;
                }
            } else if (!hasAllPrivilege) {
                crsHtml = `<option value="">No subjects assigned to you in this class</option>`;
            }
            selectCourse.innerHTML = crsHtml;
        })
        .catch(err => {
            console.error('Error fetching class sections and courses:', err);
            selectSection.innerHTML = '<option value="0">All Sections</option>';
            selectCourse.innerHTML = CAN_EDIT_ALL ? '<option value="all">⚡ All Subjects at Once (Master Grade Ledger)</option>' : '<option value="">-- Select Assigned Subject --</option>';
        });
}

// Live GPA and Grade Calculation according to Nepal NEB / CDC grading standard
function computeGradeInfo(percentage) {
    if (isNaN(percentage) || percentage < 0) return { gpa: '—', grade: '—', badgeClass: 'bg-slate-100 text-slate-500' };

    if (percentage >= 90) return { gpa: '4.00', grade: 'A+', badgeClass: 'bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800' };
    if (percentage >= 80) return { gpa: '3.60', grade: 'A', badgeClass: 'bg-teal-100 dark:bg-teal-950 text-teal-700 dark:text-teal-300 border border-teal-300 dark:border-teal-800' };
    if (percentage >= 70) return { gpa: '3.20', grade: 'B+', badgeClass: 'bg-cyan-100 dark:bg-cyan-950 text-cyan-700 dark:text-cyan-300 border border-cyan-300 dark:border-cyan-800' };
    if (percentage >= 60) return { gpa: '2.80', grade: 'B', badgeClass: 'bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 border border-blue-300 dark:border-blue-800' };
    if (percentage >= 50) return { gpa: '2.40', grade: 'C+', badgeClass: 'bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300 border border-amber-300 dark:border-amber-800' };
    if (percentage >= 40) return { gpa: '2.00', grade: 'C', badgeClass: 'bg-orange-100 dark:bg-orange-950 text-orange-700 dark:text-orange-300 border border-orange-300 dark:border-orange-800' };
    if (percentage >= 35) return { gpa: '1.60', grade: 'D', badgeClass: 'bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300 border border-rose-300 dark:border-rose-800' };
    return { gpa: '0.00', grade: 'NG', badgeClass: 'bg-rose-200 dark:bg-rose-900 text-rose-800 dark:text-rose-200 border border-rose-400 font-extrabold' };
}

function updateStudentRowStats(row) {
    const inputs = row.querySelectorAll('.mark-input');
    let totalObtained = 0;
    let totalMax = 0;
    let hasAnyMark = false;
    let anyFailed = false;

    inputs.forEach(input => {
        const val = input.value.trim();
        const max = parseFloat(input.dataset.maxMarks) || 100;
        totalMax += max;

        if (val !== '' && !isNaN(val)) {
            hasAnyMark = true;
            const num = parseFloat(val);
            totalObtained += num;
            if (num < (max * 0.35)) {
                anyFailed = true;
            }
        }
    });

    const totalSpan = row.querySelector('.row-total-marks');
    const pctSpan = row.querySelector('.row-percentage');
    const gpaSpan = row.querySelector('.row-gpa');
    const gradeBadge = row.querySelector('.row-grade-badge');

    if (!hasAnyMark || totalMax === 0) {
        if (totalSpan) totalSpan.textContent = '—';
        if (pctSpan) pctSpan.textContent = '—';
        if (gpaSpan) gpaSpan.textContent = '—';
        if (gradeBadge) {
            gradeBadge.textContent = '—';
            gradeBadge.className = 'row-grade-badge px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-400';
        }
        return;
    }

    const percentage = (totalObtained / totalMax) * 100;
    const gradeInfo = computeGradeInfo(percentage);

    if (totalSpan) totalSpan.textContent = totalObtained.toFixed(1) + ' / ' + totalMax;
    if (pctSpan) pctSpan.textContent = percentage.toFixed(1) + '%';
    if (gpaSpan) {
        gpaSpan.textContent = gradeInfo.gpa;
        gpaSpan.className = 'row-gpa ' + (gradeInfo.grade === 'NG' ? 'text-rose-600 font-bold' : 'text-slate-800 dark:text-slate-100');
    }
    if (gradeBadge) {
        gradeBadge.textContent = anyFailed && gradeInfo.grade !== 'NG' ? gradeInfo.grade + '*' : gradeInfo.grade;
        gradeBadge.className = 'row-grade-badge px-2 py-0.5 rounded-md text-[10px] font-bold ' + gradeInfo.badgeClass;
    }
}

function calculateAllRows() {
    const rows = document.querySelectorAll('.student-mark-row');
    rows.forEach(row => updateStudentRowStats(row));
}

function fillZeroForEmpty() {
    if (!confirm('Fill 0 (Absent) for all currently blank mark inputs in this sheet?')) return;
    const inputs = document.querySelectorAll('.mark-input');
    inputs.forEach(inp => {
        if (inp.value.trim() === '') {
            inp.value = '0';
        }
    });
    calculateAllRows();
}

document.addEventListener('DOMContentLoaded', function() {
    // 1. Initial Load of Sections & Courses
    const classSelect = document.getElementById('filter_class_id');
    if (classSelect && classSelect.value) {
        loadSectionsAndCourses(classSelect.value, PRESELECTED_SECTION, PRESELECTED_COURSE);
    }

    // 2. Initial Row Calculations
    calculateAllRows();

    // 3. Live Row Recalculation on keystroke + max marks enforcement
    const table = document.getElementById('masterMarksTable');
    if (table) {
        table.addEventListener('input', function(e) {
            if (!e.target.classList.contains('mark-input')) return;
            // Enforce max
            const max = parseFloat(e.target.dataset.maxMarks) || 100;
            const val = parseFloat(e.target.value);
            if (!isNaN(val) && val > max) {
                e.target.value = max;
                e.target.style.outline = '2px solid #ef4444';
                e.target.title = `Max: ${max}`;
                setTimeout(() => { e.target.style.outline = ''; }, 1000);
            } else if (!isNaN(val) && val < 0) {
                e.target.value = 0;
            } else {
                e.target.style.outline = '';
            }
            // Recalculate row stats
            const row = e.target.closest('.student-mark-row');
            if (row) updateStudentRowStats(row);
        });

        // 4. Keyboard Arrow Key & Enter Navigation (Excel-like feel)
        table.addEventListener('keydown', function(e) {
            if (!e.target.classList.contains('mark-input')) return;

            const currentInput = e.target;
            const currentCell = currentInput.closest('td');
            const currentRow = currentInput.closest('tr');
            const cellIndex = Array.from(currentRow.children).indexOf(currentCell);

            let targetInput = null;

            if (e.key === 'ArrowDown' || e.key === 'Enter') {
                e.preventDefault();
                const nextRow = currentRow.nextElementSibling;
                if (nextRow && nextRow.children[cellIndex]) {
                    targetInput = nextRow.children[cellIndex].querySelector('.mark-input');
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                const prevRow = currentRow.previousElementSibling;
                if (prevRow && prevRow.children[cellIndex]) {
                    targetInput = prevRow.children[cellIndex].querySelector('.mark-input');
                }
            } else if (e.key === 'ArrowRight' && currentInput.selectionEnd === currentInput.value.length) {
                const nextCell = currentCell.nextElementSibling;
                if (nextCell) targetInput = nextCell.querySelector('.mark-input');
            } else if (e.key === 'ArrowLeft' && currentInput.selectionStart === 0) {
                const prevCell = currentCell.previousElementSibling;
                if (prevCell) targetInput = prevCell.querySelector('.mark-input');
            }

            if (targetInput) {
                targetInput.focus();
                targetInput.select();
            }
        });
    }

    // 5. Quick Student Filter Search
    const searchInput = document.getElementById('studentQuickFilter');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('.student-mark-row');
            rows.forEach(row => {
                const name = row.dataset.studentName || '';
                const roll = String(row.dataset.rollNo || '');
                if (name.includes(query) || roll.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // 6. Global Shortcut: Ctrl+S / Cmd+S to submit form
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            const masterForm = document.getElementById('masterMarksForm');
            if (masterForm) {
                e.preventDefault();
                masterForm.submit();
            }
        }
    });

    // 7. ── AUTO-SAVE to localStorage (survives refresh) ──────────────────
    const STORAGE_KEY = 'marks_draft_{{ $class_id }}_{{ $section_id }}_{{ $semester_id }}';
    const saveIndicator = document.getElementById('autoSaveIndicator');

    function showSaveIndicator(msg, color) {
        if (!saveIndicator) return;
        saveIndicator.textContent = msg;
        saveIndicator.className = `fixed bottom-20 right-4 sm:bottom-6 sm:right-6 z-50 px-3 py-1.5 rounded-xl text-[11px] font-bold shadow-lg transition-all ${color}`;
        saveIndicator.style.opacity = '1';
        clearTimeout(saveIndicator._timer);
        saveIndicator._timer = setTimeout(() => { saveIndicator.style.opacity = '0'; }, 3000);
    }

    function saveDraftToLocal() {
        const inputs = document.querySelectorAll('.mark-input');
        const draft = {};
        inputs.forEach(inp => {
            const key = inp.name;
            if (inp.value.trim() !== '') draft[key] = inp.value;
        });
        localStorage.setItem(STORAGE_KEY, JSON.stringify(draft));
        showSaveIndicator('✓ Draft saved locally', 'bg-emerald-600 text-white');
    }

    function loadDraftFromLocal() {
        const raw = localStorage.getItem(STORAGE_KEY);
        if (!raw) return;
        try {
            const draft = JSON.parse(raw);
            let restored = 0;
            Object.entries(draft).forEach(([name, value]) => {
                const inp = document.querySelector(`input[name="${CSS.escape(name)}"]`);
                if (inp && inp.value.trim() === '') {
                    inp.value = value;
                    restored++;
                }
            });
            if (restored > 0) {
                showSaveIndicator(`↺ Restored ${restored} draft marks`, 'bg-indigo-600 text-white');
                calculateAllRows();
            }
        } catch(e) {}
    }

    // Load draft on page load
    loadDraftFromLocal();

    // Auto-save every 30 seconds
    setInterval(saveDraftToLocal, 30000);

    // Save on every input change (debounced)
    let draftTimer;
    const masterTable = document.getElementById('masterMarksTable');
    if (masterTable) {
        masterTable.addEventListener('input', function(e) {
            if (e.target.classList.contains('mark-input')) {
                clearTimeout(draftTimer);
                draftTimer = setTimeout(saveDraftToLocal, 1500);
            }
        });
    }

    // Clear draft on successful form submit
    const masterForm = document.getElementById('masterMarksForm');
    if (masterForm) {
        masterForm.addEventListener('submit', function() {
            localStorage.removeItem(STORAGE_KEY);
        });
    }
});
</script>
@endsection
