@extends('layouts.app')

@section('content')
<style>
/* ── Marks entry fast-input helpers ── */
.mark-input.mark-absent { background-color: #fee2e2 !important; color: #b91c1c !important; font-weight: 800; }
.dark .mark-input.mark-absent { background-color: rgba(244,63,94,0.18) !important; color: #fca5a5 !important; }
.mark-input.mark-over { outline: 2px solid #ef4444 !important; }
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

    {{-- Page Header — compact on mobile --}}
    <div class="flex items-center justify-between gap-2">
        <div class="flex items-center gap-2.5 min-w-0">
            <span class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-base shadow-md flex-shrink-0">
                <i class="bi bi-pencil-square"></i>
            </span>
            <div class="min-w-0">
                <h1 class="text-base sm:text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight leading-tight truncate">
                    Enter Marks & Grades
                </h1>
                <p class="text-[11px] text-slate-400 hidden sm:block">Direct master grade ledger with real-time GPA</p>
            </div>
        </div>
        @if(!empty($isAdmin))
        <span class="text-[10px] font-bold px-2 py-1 rounded-lg bg-indigo-100 text-indigo-700 border border-indigo-200 flex-shrink-0 flex items-center gap-1">
            <i class="bi bi-shield-lock-fill"></i> Admin
        </span>
        @elseif(!empty($isClassTeacher))
        <span class="text-[10px] font-bold px-2 py-1 rounded-lg bg-amber-100 text-amber-700 border border-amber-200 flex-shrink-0 flex items-center gap-1">
            <i class="bi bi-star-fill"></i> Class Teacher
        </span>
        @endif
    </div>
    @include('session-messages')

    {{-- Marks Submission Window Status Bar — compact --}}
    @php
        $isWindowOpen = isset($academic_setting) && is_array($academic_setting) && isset($academic_setting['marks_submission_status']) && $academic_setting['marks_submission_status'] === 'on';
    @endphp
    @if($isWindowOpen)
    <div class="flex items-center justify-between gap-2 px-3 py-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/60">
        <div class="flex items-center gap-2">
            <i class="bi bi-unlock-fill text-emerald-600 text-sm flex-shrink-0"></i>
            <p class="text-xs font-bold text-emerald-800 dark:text-emerald-200">Submission Window is <span class="text-emerald-600">OPEN</span></p>
        </div>
        @if(!empty($isAdmin))
        <form action="{{ route('school.final.marks.submission.status.update') }}" method="POST">
            @csrf
            <button type="submit" class="text-[11px] font-semibold px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-slate-700 flex items-center gap-1" onclick="return confirm('Lock submission window?')">
                <i class="bi bi-lock"></i> Close
            </button>
        </form>
        @endif
    </div>
    @else
    <div class="flex items-center justify-between gap-2 px-3 py-2 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60">
        <div class="flex items-center gap-2">
            <i class="bi bi-shield-lock-fill text-amber-600 text-sm flex-shrink-0"></i>
            <p class="text-xs font-bold text-amber-800 dark:text-amber-200">
                @if(!empty($isAdmin))
                    Window Closed — <span class="text-indigo-600">Admin Access Active</span>
                @else
                    Submission Window Closed
                @endif
            </p>
        </div>
        @if(!empty($isAdmin))
        <form action="{{ route('school.final.marks.submission.status.update') }}" method="POST">
            @csrf
            <input type="hidden" name="marks_submission_status" value="on">
            <button type="submit" class="text-[11px] font-bold px-2.5 py-1 rounded-lg bg-emerald-600 text-white flex items-center gap-1">
                <i class="bi bi-unlock-fill"></i> Open
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
                    <div class="px-4 py-2.5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 flex flex-wrap items-center justify-between gap-2">

                        {{-- LEFT: Excel Import / Export --}}
                        <div class="flex items-center gap-2 flex-wrap">
                            {{-- Download Template — CSV / XLSX split button --}}
                            <div class="relative inline-flex rounded-xl overflow-hidden border border-emerald-200 dark:border-emerald-800/80 shadow-xs" x-data="{ open: false }" @click.outside="open = false">
                                {{-- Primary: CSV (default) --}}
                                <a href="{{ route('marks.template.download', ['class_id' => $class_id, 'section_id' => $section_id, 'semester_id' => $semester_id, 'course_id' => 'all', 'format' => 'csv']) }}"
                                   data-no-loader
                                   class="px-3 py-1.5 bg-emerald-50/70 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 text-xs font-semibold transition flex items-center gap-1.5"
                                   title="Download CSV template">
                                    <i class="bi bi-file-earmark-arrow-down text-emerald-600"></i>
                                    <span class="hidden sm:inline">CSV Template</span>
                                    <span class="sm:hidden">CSV</span>
                                </a>
                                {{-- Dropdown toggle --}}
                                <button type="button" @click="open = !open"
                                    class="px-1.5 py-1.5 bg-emerald-100/70 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-200 dark:hover:bg-emerald-800 border-l border-emerald-200 dark:border-emerald-800 transition text-xs">
                                    <i class="bi bi-chevron-down text-[10px]"></i>
                                </button>
                                {{-- Dropdown: XLSX option --}}
                                <div x-show="open" x-cloak
                                    class="absolute left-0 top-full mt-1 z-30 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg overflow-hidden min-w-[160px]">
                                    <a href="{{ route('marks.template.download', ['class_id' => $class_id, 'section_id' => $section_id, 'semester_id' => $semester_id, 'course_id' => 'all', 'format' => 'csv']) }}"
                                       data-no-loader @click="open=false"
                                       class="flex items-center gap-2 px-4 py-2.5 text-xs font-semibold text-emerald-700 dark:text-emerald-300 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 transition">
                                        <i class="bi bi-filetype-csv text-emerald-500"></i> Download as CSV
                                    </a>
                                    <a href="{{ route('marks.template.download', ['class_id' => $class_id, 'section_id' => $section_id, 'semester_id' => $semester_id, 'course_id' => 'all', 'format' => 'xlsx']) }}"
                                       data-no-loader @click="open=false"
                                       class="flex items-center gap-2 px-4 py-2.5 text-xs font-semibold text-indigo-700 dark:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 transition">
                                        <i class="bi bi-filetype-xlsx text-indigo-500"></i> Download as Excel (.xlsx)
                                    </a>
                                </div>
                            </div>
                            {{-- Upload Filled Template --}}
                            <button type="button" onclick="document.getElementById('bulkImportModal').classList.remove('hidden')"
                                class="px-3 py-1.5 rounded-xl border border-amber-200 dark:border-amber-800/80 bg-amber-50/70 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-900/50 text-xs font-semibold transition flex items-center gap-1.5 shadow-xs"
                                title="Upload filled Excel marks ledger">
                                <i class="bi bi-file-earmark-arrow-up text-amber-600"></i>
                                <span class="hidden sm:inline">Upload Marks</span>
                                <span class="sm:hidden">Upload</span>
                            </button>
                        </div>

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
                                {{-- Reorder Subjects button (admin / class-teacher only) --}}
                                <button type="button" onclick="openReorderModal()"
                                    class="px-3 py-1.5 rounded-xl border border-violet-200 dark:border-violet-800/80 bg-violet-50/70 dark:bg-violet-950/40 text-violet-700 dark:text-violet-300 hover:bg-violet-100 dark:hover:bg-violet-900/50 text-xs font-semibold transition flex items-center gap-1.5 shadow-xs"
                                    title="Drag subjects to change their column order">
                                    <i class="bi bi-arrow-down-up text-violet-600 dark:text-violet-400"></i>
                                    <span class="hidden sm:inline">Reorder Subjects</span>
                                    <span class="sm:hidden">Order</span>
                                </button>
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
                                                    <span>FM: <strong class="text-indigo-600 dark:text-indigo-400" id="fm-{{ $course->id }}">{{ $courseMaxMarks[$course->id] ?? 100 }}</strong></span>
                                                    <span class="text-slate-300">|</span>
                                                    <span>PM: <strong class="text-rose-500" id="pm-{{ $course->id }}">{{ $coursePassMarks[$course->id] ?? 40 }}</strong></span>
                                                    @if(!empty($isAdmin) || !empty($isClassTeacher))
                                                    <button type="button"
                                                        onclick="openRuleEdit({{ $course->id }}, '{{ addslashes($course->course_name) }}', {{ $courseMaxMarks[$course->id] ?? 100 }}, {{ $coursePassMarks[$course->id] ?? 40 }}, {{ $courseExamIds[$course->id] ?? 0 }}, {{ $courseRuleIds[$course->id] ?? 0 }})"
                                                        class="ml-0.5 p-0.5 text-slate-400 hover:text-indigo-600 rounded transition" title="Edit FM/PM">
                                                        <i class="bi bi-pencil-fill text-[9px]"></i>
                                                    </button>
                                                    @endif
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
                        {{-- Download Template (single subject) — CSV / XLSX split button --}}
                        <div class="relative inline-flex rounded-xl overflow-hidden border border-emerald-200 dark:border-emerald-800/80 shadow-xs" x-data="{ open: false }" @click.outside="open = false">
                            <a href="{{ route('marks.template.download', ['class_id' => $class_id, 'section_id' => $section_id, 'semester_id' => $semester_id, 'course_id' => $course_id, 'format' => 'csv']) }}"
                               data-no-loader
                               class="px-3 py-1.5 bg-emerald-50/70 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 text-xs font-semibold transition flex items-center gap-1.5"
                               title="Download CSV template for this subject">
                                <i class="bi bi-file-earmark-arrow-down text-emerald-600"></i>
                                <span class="hidden sm:inline">CSV Template</span>
                                <span class="sm:hidden">CSV</span>
                            </a>
                            <button type="button" @click="open = !open"
                                class="px-1.5 py-1.5 bg-emerald-100/70 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-200 dark:hover:bg-emerald-800 border-l border-emerald-200 dark:border-emerald-800 transition text-xs">
                                <i class="bi bi-chevron-down text-[10px]"></i>
                            </button>
                            <div x-show="open" x-cloak
                                class="absolute left-0 top-full mt-1 z-30 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg overflow-hidden min-w-[160px]">
                                <a href="{{ route('marks.template.download', ['class_id' => $class_id, 'section_id' => $section_id, 'semester_id' => $semester_id, 'course_id' => $course_id, 'format' => 'csv']) }}"
                                   data-no-loader @click="open=false"
                                   class="flex items-center gap-2 px-4 py-2.5 text-xs font-semibold text-emerald-700 dark:text-emerald-300 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 transition">
                                    <i class="bi bi-filetype-csv text-emerald-500"></i> Download as CSV
                                </a>
                                <a href="{{ route('marks.template.download', ['class_id' => $class_id, 'section_id' => $section_id, 'semester_id' => $semester_id, 'course_id' => $course_id, 'format' => 'xlsx']) }}"
                                   data-no-loader @click="open=false"
                                   class="flex items-center gap-2 px-4 py-2.5 text-xs font-semibold text-indigo-700 dark:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 transition">
                                    <i class="bi bi-filetype-xlsx text-indigo-500"></i> Download as Excel (.xlsx)
                                </a>
                            </div>
                        </div>
                        {{-- Upload Filled Template --}}
                        <button type="button" onclick="document.getElementById('bulkImportModal').classList.remove('hidden')"
                            class="px-3 py-1.5 rounded-xl border border-amber-200 dark:border-amber-800/80 bg-amber-50/70 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 hover:bg-amber-100 text-xs font-semibold transition flex items-center gap-1.5 shadow-xs"
                            title="Upload filled Excel marks file">
                            <i class="bi bi-file-earmark-arrow-up text-amber-600"></i>
                            <span class="hidden sm:inline">Upload Marks</span>
                            <span class="sm:hidden">Upload</span>
                        </button>
                        @if(count($exams) > 0)
                            <button type="button" onclick="fillZeroForEmpty()" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-semibold transition flex items-center gap-1.5 shadow-xs" title="Set any remaining empty marks as 0 (Absent)">
                                <i class="bi bi-magic text-indigo-500"></i> <span>Set Empty → Absent</span>
                            </button>
                        @endif
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
                        {{-- Top Save button --}}
                        <div class="flex justify-end mb-3">
                            <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/20 transition flex items-center gap-2">
                                <i class="bi bi-check2 text-base"></i> Save Subject Marks
                            </button>
                        </div>
                        {{-- Search bar for single subject mode --}}
                        <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900">
                            <div class="relative w-full max-w-sm">
                                <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                <input type="text" id="studentQuickFilter" placeholder="Filter by student name or roll..." class="w-full text-xs pl-9 pr-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500" style="font-size:16px !important; min-height:40px;">
                            </div>
                        </div>
                        <div class="overflow-x-auto mb-6">
                            <table class="w-full text-left text-xs" id="singleMarksTable">
                                <thead>
                                    <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-semibold bg-slate-50/50 dark:bg-slate-800/20">
                                        <th class="py-3 px-4 min-w-[80px]">Roll No.</th>
                                        <th class="py-3 px-4 min-w-[200px]">Student Name</th>
                                        @foreach ($exams as $exam)
                                            @php
                                                // FM/PM per exam — same source (exam_rules) used by the
                                                // Master Grade Ledger so the two modes stay consistent.
                                                $examFM  = (float)($exam_rules_by_exam_id->has($exam->id) ? $exam_rules_by_exam_id[$exam->id] : 100);
                                                $examPM  = (float)((isset($exam_pass_marks_by_exam_id) && $exam_pass_marks_by_exam_id->has($exam->id)) ? $exam_pass_marks_by_exam_id[$exam->id] : 40);
                                                $examRID = (int)((isset($exam_rule_ids_by_exam_id) && $exam_rule_ids_by_exam_id->has($exam->id)) ? $exam_rule_ids_by_exam_id[$exam->id] : 0);
                                            @endphp
                                            <th class="py-3 px-4 min-w-[150px]">
                                                <span class="block text-[11px] font-bold text-slate-700 dark:text-slate-200 mb-0.5">
                                                    {{ $selectedCourse->course_name ?? '' }}
                                                </span>
                                                <a href="{{ route('exam.rule.show', ['exam_id' => $exam->id]) }}" class="hover:text-indigo-600 font-bold" title="View exam rules">
                                                    {{ $exam->exam_name }}
                                                </a>
                                                <div class="flex items-center justify-center gap-1 text-[10px] font-normal text-slate-500 dark:text-slate-400 mt-0.5">
                                                    <span>FM: <strong class="text-indigo-600 dark:text-indigo-400" id="fm-exam-{{ $exam->id }}">{{ $examFM }}</strong></span>
                                                    <span class="text-slate-300">|</span>
                                                    <span>PM: <strong class="text-rose-500" id="pm-exam-{{ $exam->id }}">{{ $examPM }}</strong></span>
                                                    @if(!empty($isAdmin) || !empty($isClassTeacher))
                                                    <button type="button"
                                                        onclick="openRuleEdit({{ $course_id }}, '{{ addslashes($selectedCourse->course_name ?? '') }}', {{ $examFM }}, {{ $examPM }}, {{ $exam->id }}, {{ $examRID }})"
                                                        class="ml-0.5 p-0.5 text-slate-400 hover:text-indigo-600 rounded transition" title="Edit FM/PM">
                                                        <i class="bi bi-pencil-fill text-[9px]"></i>
                                                    </button>
                                                    @endif
                                                </div>
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    {{-- ── Always iterate the full enrolled roster (like master mode).
                                         Saved marks are overlaid from $marksByStudentAndExam.
                                         This ensures every student is shown regardless of whether
                                         marks have been entered for some of them already. ── --}}
                                    @if(count($sectionStudents) > 0)
                                        @foreach ($sectionStudents as $sectionStudent)
                                            @php
                                                $stu    = $sectionStudent->student ?? null;
                                                $stuId  = $stu?->id ?? $sectionStudent->student_id;
                                                $rollNo = $sectionStudent->roll_no ?? $stu?->roll_no ?? '—';
                                                $stuName = trim(($stu?->first_name ?? '') . ' ' . ($stu?->last_name ?? ''));
                                                if (empty(trim($stuName))) $stuName = 'Student #' . $stuId;
                                            @endphp
                                            <tr class="student-mark-row hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition"
                                                data-student-name="{{ strtolower($stuName) }}"
                                                data-roll-no="{{ $rollNo }}">
                                                <td class="py-3 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                                    {{ $rollNo }}
                                                </td>
                                                <td class="py-3 px-4 font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                                    <div class="w-7 h-7 rounded-full bg-indigo-50 text-indigo-600 font-bold flex items-center justify-center text-xs flex-shrink-0">
                                                        {{ strtoupper(substr($stuName, 0, 1)) }}
                                                    </div>
                                                    {{ $stuName }}
                                                </td>
                                                @foreach ($exams as $exam)
                                                    @php
                                                        $savedMark = $marksByStudentAndExam[$stuId][$exam->id] ?? null;
                                                        $maxM = $exam_rules_by_exam_id->has($exam->id) ? $exam_rules_by_exam_id[$exam->id] : 100;
                                                    @endphp
                                                    <td class="py-3 px-4">
                                                        <input type="number"
                                                               step="0.01" min="0" max="{{ $maxM }}"
                                                               data-student-id="{{ $stuId }}"
                                                               data-exam-id="{{ $exam->id }}"
                                                               data-max-marks="{{ $maxM }}"
                                                               class="mark-input w-full text-xs px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20"
                                                               name="student_mark[{{ $stuId }}][{{ $exam->id }}]"
                                                               value="{{ $savedMark !== null ? $savedMark : '' }}">
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    @elseif(count($students_with_marks) > 0)
                                        {{-- Fallback: no section-enrollment data but marks exist (legacy data) --}}
                                        @foreach ($students_with_marks as $stuId => $stuMarkRows)
                                            @php
                                                $stu     = $stuMarkRows->first()?->student ?? null;
                                                $stuName = trim(($stu?->first_name ?? '') . ' ' . ($stu?->last_name ?? ''));
                                                if (empty(trim($stuName))) $stuName = 'Student #' . $stuId;
                                            @endphp
                                            <tr class="student-mark-row hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition"
                                                data-student-name="{{ strtolower($stuName) }}"
                                                data-roll-no="{{ $stu?->roll_no ?? '' }}">
                                                <td class="py-3 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                                    {{ $stu?->roll_no ?? '—' }}
                                                </td>
                                                <td class="py-3 px-4 font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                                    <div class="w-7 h-7 rounded-full bg-indigo-50 text-indigo-600 font-bold flex items-center justify-center text-xs flex-shrink-0">
                                                        {{ strtoupper(substr($stuName, 0, 1)) }}
                                                    </div>
                                                    {{ $stuName }}
                                                </td>
                                                @foreach ($exams as $exam)
                                                    @php
                                                        $savedMark = $marksByStudentAndExam[$stuId][$exam->id] ?? null;
                                                        $maxM = $exam_rules_by_exam_id->has($exam->id) ? $exam_rules_by_exam_id[$exam->id] : 100;
                                                    @endphp
                                                    <td class="py-3 px-4">
                                                        <input type="number"
                                                               step="0.01" min="0" max="{{ $maxM }}"
                                                               data-student-id="{{ $stuId }}"
                                                               data-exam-id="{{ $exam->id }}"
                                                               data-max-marks="{{ $maxM }}"
                                                               class="mark-input w-full text-xs px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20"
                                                               name="student_mark[{{ $stuId }}][{{ $exam->id }}]"
                                                               value="{{ $savedMark !== null ? $savedMark : '' }}">
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="{{ count($exams) + 2 }}" class="py-12 text-center text-xs text-slate-400">
                                                <i class="bi bi-people text-2xl block mb-2"></i>
                                                No students enrolled in this class/section.
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-3 flex-wrap">
                            @if($final_marks_submitted)
                                <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 dark:bg-emerald-950/40 px-3 py-1.5 rounded-xl border border-emerald-200 dark:border-emerald-900 flex items-center gap-1">
                                    <i class="bi bi-check-circle"></i> Final Marks Already Submitted
                                </span>
                            @endif
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/20 transition flex items-center gap-2">
                                <i class="bi bi-check2 text-base"></i> Save Subject Marks
                            </button>
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

{{-- ── Bulk Excel Import Modal ──────────────────────────────────────────── --}}
<div id="bulkImportModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in-95">

        {{-- Header --}}
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/40">
            <div class="flex items-center gap-2.5">
                <span class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 flex items-center justify-center text-lg">
                    <i class="bi bi-file-earmark-spreadsheet"></i>
                </span>
                <div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Upload Marks from Excel</h3>
                    <p class="text-[10px] text-slate-400">Import filled ledger — existing marks will be overwritten</p>
                </div>
            </div>
            <button onclick="document.getElementById('bulkImportModal').classList.add('hidden')"
                class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xl leading-none w-7 h-7 flex items-center justify-center rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                &times;
            </button>
        </div>

        {{-- Body --}}
        <form action="{{ route('marks.import') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
            @csrf
            <input type="hidden" name="class_id"    value="{{ $class_id }}">
            <input type="hidden" name="section_id"  value="{{ $section_id }}">
            <input type="hidden" name="semester_id" value="{{ $semester_id }}">
            <input type="hidden" name="course_id"   value="{{ $course_id ?? 'all' }}">

            {{-- Step guide --}}
            <div class="bg-indigo-50/60 dark:bg-indigo-950/30 rounded-2xl p-4 border border-indigo-100 dark:border-indigo-900/40 text-[11px] text-indigo-700 dark:text-indigo-300 space-y-1.5">
                <p class="font-bold text-xs text-indigo-800 dark:text-indigo-200 mb-2 flex items-center gap-1.5"><i class="bi bi-info-circle-fill"></i> How to use</p>
                <div class="flex items-start gap-2"><span class="w-5 h-5 rounded-full bg-indigo-600 text-white font-bold text-[9px] flex items-center justify-center flex-shrink-0">1</span><span>Click <strong>Download Template</strong> to get the Excel file pre-filled with student names.</span></div>
                <div class="flex items-start gap-2"><span class="w-5 h-5 rounded-full bg-indigo-600 text-white font-bold text-[9px] flex items-center justify-center flex-shrink-0">2</span><span>Fill in marks in the coloured subject columns. Leave blank for absent.</span></div>
                <div class="flex items-start gap-2"><span class="w-5 h-5 rounded-full bg-indigo-600 text-white font-bold text-[9px] flex items-center justify-center flex-shrink-0">3</span><span>Upload the saved file here. <strong>Do not modify student_id, roll_no or name columns.</strong></span></div>
            </div>

            {{-- File picker --}}
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Select Excel File <span class="text-rose-500">*</span>
                </label>
                <label class="flex flex-col items-center justify-center w-full h-28 border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl cursor-pointer bg-slate-50 dark:bg-slate-800/50 hover:border-indigo-400 hover:bg-indigo-50/40 dark:hover:bg-indigo-950/20 transition group" id="dropZoneLabel">
                    <div class="flex flex-col items-center gap-1 pointer-events-none" id="dropZoneText">
                        <i class="bi bi-cloud-upload text-2xl text-slate-400 group-hover:text-indigo-500 transition"></i>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 group-hover:text-indigo-600">Click to browse or drag & drop</p>
                        <p class="text-[10px] text-slate-400">.xlsx, .xls, .csv — max 10 MB</p>
                    </div>
                    <input type="file" name="marks_file" id="marksFileInput" accept=".xlsx,.xls,.csv"
                           class="hidden" onchange="updateDropZone(this)">
                </label>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-between gap-3 pt-1">
                <div class="flex items-center gap-2 flex-wrap">
                    <a href="{{ route('marks.template.download', ['class_id' => $class_id, 'section_id' => $section_id, 'semester_id' => $semester_id, 'course_id' => $course_id ?? 'all', 'format' => 'csv']) }}"
                       data-no-loader
                       class="px-4 py-2 rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 text-xs font-semibold transition flex items-center gap-1.5">
                        <i class="bi bi-filetype-csv"></i> CSV Template
                    </a>
                    <a href="{{ route('marks.template.download', ['class_id' => $class_id, 'section_id' => $section_id, 'semester_id' => $semester_id, 'course_id' => $course_id ?? 'all', 'format' => 'xlsx']) }}"
                       data-no-loader
                       class="px-4 py-2 rounded-xl border border-indigo-200 dark:border-indigo-800 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 text-xs font-semibold transition flex items-center gap-1.5">
                        <i class="bi bi-filetype-xlsx"></i> Excel Template
                    </a>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="document.getElementById('bulkImportModal').classList.add('hidden')"
                        class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-md shadow-amber-500/20 transition flex items-center gap-2">
                        <i class="bi bi-upload"></i> Import Marks
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function updateDropZone(input) {
    const label = document.getElementById('dropZoneText');
    if (input.files && input.files[0]) {
        const f = input.files[0];
        label.innerHTML = `
            <i class="bi bi-file-earmark-spreadsheet text-2xl text-emerald-500"></i>
            <p class="text-xs font-bold text-emerald-600">${f.name}</p>
            <p class="text-[10px] text-slate-400">${(f.size / 1024).toFixed(1)} KB — click to change</p>
        `;
        document.getElementById('dropZoneLabel').classList.replace('border-slate-300', 'border-emerald-400');
        document.getElementById('dropZoneLabel').classList.add('bg-emerald-50/40');
    }
}
// Close modal on backdrop click
document.getElementById('bulkImportModal').addEventListener('click', function(e) {
    if (e.target === this) this.classList.add('hidden');
});
</script>

{{-- Scripts for dynamic Section/Course loading, keyboard shortcuts, and live GPA calculations --}}
<script>
const IS_ADMIN = {{ !empty($isAdmin) ? 'true' : 'false' }};
const IS_CLASS_TEACHER = {{ !empty($isClassTeacher) ? 'true' : 'false' }};
const CAN_EDIT_ALL = {{ !empty($canEditAllSubjects) ? 'true' : 'false' }};
const IS_ALL_SUBJECTS_MODE = {{ !empty($isAllSubjectsMode) ? 'true' : 'false' }};
const STORE_URL = "{{ route('course.mark.store') }}";
const RULE_UPDATE_URL = "{{ route('exam.rule.update') }}";
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
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
    let n = 0;
    inputs.forEach(inp => {
        if (inp.value.trim() === '') {
            inp.value = '0';
            inp.classList.add('mark-absent');
            inp.setAttribute('data-absent', '1');
            inp.dataset.dirty = '1';           // queue for auto-save (0 = absent)
            n++;
            const row = inp.closest('.student-mark-row');
            if (row) updateStudentRowStats(row);
        }
    });
    calculateAllRows();
    if (n > 0 && window.marksSetStatus) window.marksSetStatus('Marked ' + n + ' as Absent (0)', 'bg-amber-600 text-white', false);
}

document.addEventListener('DOMContentLoaded', function() {
    // 1. Initial Load of Sections & Courses
    const classSelect = document.getElementById('filter_class_id');
    if (classSelect && classSelect.value) {
        loadSectionsAndCourses(classSelect.value, PRESELECTED_SECTION, PRESELECTED_COURSE);
    }

    // 2. Initial Row Calculations
    calculateAllRows();

    // 3. Live Row Recalculation + max enforcement + dirty tracking (works for both modes)
    document.addEventListener('input', function(e) {
        const inp = e.target;
        if (!inp.classList || !inp.classList.contains('mark-input')) return;

        // Enforce 0 .. Full Marks
        const max = parseFloat(inp.dataset.maxMarks) || 100;
        const val = parseFloat(inp.value);
        if (!isNaN(val) && val > max) {
            inp.value = max;
            inp.classList.add('mark-over');
            inp.title = 'Max: ' + max;
            setTimeout(() => { inp.classList.remove('mark-over'); }, 1200);
        } else if (!isNaN(val) && val < 0) {
            inp.value = 0;
        } else {
            inp.classList.remove('mark-over');
        }
        // A genuine typed value clears any prior "absent" flag
        inp.classList.remove('mark-absent');
        inp.removeAttribute('data-absent');

        markDirty(inp);

        const row = inp.closest('.student-mark-row');
        if (row) updateStudentRowStats(row);
    });

    // 4. Keyboard navigation (Excel-like): Enter / ↓ = next student (down),
    //    ↑ = previous student, ← / → = across the row. Tab / Shift+Tab use the
    //    browser's native field order so users can move freely.
    document.addEventListener('keydown', function(e) {
        const inp = e.target;
        if (!inp.classList || !inp.classList.contains('mark-input')) return;

        const currentCell = inp.closest('td');
        const currentRow = inp.closest('tr');
        if (!currentCell || !currentRow) return;
        const cellIndex = Array.from(currentRow.children).indexOf(currentCell);

        let targetInput = null;

        if (e.key === 'ArrowDown' || e.key === 'Enter') {
            e.preventDefault();
            let nextRow = currentRow.nextElementSibling;
            while (nextRow && nextRow.style.display === 'none') nextRow = nextRow.nextElementSibling;
            if (nextRow && nextRow.children[cellIndex]) {
                targetInput = nextRow.children[cellIndex].querySelector('.mark-input');
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            let prevRow = currentRow.previousElementSibling;
            while (prevRow && prevRow.style.display === 'none') prevRow = prevRow.previousElementSibling;
            if (prevRow && prevRow.children[cellIndex]) {
                targetInput = prevRow.children[cellIndex].querySelector('.mark-input');
            }
        } else if (e.key === 'ArrowRight' && (inp.selectionEnd === inp.value.length)) {
            const nextCell = currentCell.nextElementSibling;
            if (nextCell) targetInput = nextCell.querySelector('.mark-input');
        } else if (e.key === 'ArrowLeft' && (inp.selectionStart === 0)) {
            const prevCell = currentCell.previousElementSibling;
            if (prevCell) targetInput = prevCell.querySelector('.mark-input');
        }

        if (targetInput) {
            targetInput.focus();
            targetInput.select();
        }
    });

    // 4b. Excel / Google Sheets bulk paste — distribute a tab/newline grid
    //     starting from the focused cell, moving right then down. Each value
    //     is clamped to [0, Full Marks]; non-numeric cells are skipped.
    document.addEventListener('paste', function(e) {
        const inp = e.target;
        if (!inp.classList || !inp.classList.contains('mark-input')) return;
        const text = (e.clipboardData || window.clipboardData).getData('text');
        if (!text || (text.indexOf('\t') === -1 && text.indexOf('\n') === -1)) return; // single value → native paste
        e.preventDefault();

        const lines = text.replace(/\r/g, '').split('\n');
        while (lines.length && lines[lines.length - 1] === '') lines.pop();
        if (!lines.length) return;

        const startCell = inp.closest('td');
        const startRow = inp.closest('tr');
        const startCellIndex = Array.from(startRow.children).indexOf(startCell);

        let row = startRow;
        for (let r = 0; r < lines.length && row; r++) {
            const cells = lines[r].split('\t');
            let cell = (r === 0) ? startCell : (row.children[startCellIndex] || null);
            for (let c = 0; c < cells.length && cell; c++) {
                const target = cell.querySelector('.mark-input');
                if (target) {
                    const raw = cells[c].trim();
                    if (raw !== '' && !isNaN(raw)) {
                        const num = parseFloat(raw);
                        const mx = parseFloat(target.dataset.maxMarks) || 100;
                        target.value = Math.min(Math.max(num, 0), mx);
                        target.classList.remove('mark-absent');
                        target.removeAttribute('data-absent');
                        markDirty(target);
                        const rr = target.closest('.student-mark-row');
                        if (rr) updateStudentRowStats(rr);
                    }
                }
                cell = cell.nextElementSibling;
            }
            row = row.nextElementSibling;
        }
        saveDraftToLocal();
        if (typeof scheduleAutoSave === 'function') scheduleAutoSave();
    });

    // 4c. Auto-focus the first marks field so entry can start immediately
    const firstMarkInput = document.querySelector('.mark-input');
    if (firstMarkInput) {
        firstMarkInput.focus({ preventScroll: false });
        try { firstMarkInput.select(); } catch (e) {}
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

    // 6. Global Shortcut: Ctrl+S / Cmd+S to submit the active marks form
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            const firstInput = document.querySelector('.mark-input');
            const activeForm = firstInput ? firstInput.closest('form') : document.getElementById('masterMarksForm');
            if (activeForm) {
                e.preventDefault();
                activeForm.submit();
            }
        }
    });

    // 7. ── AUTO-SAVE + FAST ENTRY ────────────────────────────────────────
    // Two layers:
    //   (a) localStorage draft  — survives refresh / network drops
    //   (b) AJAX DB auto-save   — flushes changed marks to course.mark.store every 5s
    //        with Saving / Saved / Failed status, offline queue + retry on reconnect.
    const STORAGE_KEY = 'marks_draft_{{ $class_id }}_{{ $section_id }}_{{ $semester_id }}';
    const saveIndicator = document.getElementById('autoSaveIndicator');
    let isSaving = false;
    let pendingFlush = false;
    let formSubmitting = false;

    function setSaveStatus(msg, color, sticky) {
        if (!saveIndicator) return;
        saveIndicator.textContent = msg;
        saveIndicator.className = 'fixed bottom-20 right-4 sm:bottom-6 sm:right-6 z-50 px-3 py-1.5 rounded-xl text-[11px] font-bold shadow-lg transition-all ' + color;
        saveIndicator.style.opacity = '1';
        clearTimeout(saveIndicator._timer);
        if (!sticky) {
            saveIndicator._timer = setTimeout(() => { saveIndicator.style.opacity = '0'; }, 3000);
        }
    }

    // Track a cell as changed (dirty) so the next flush sends it.
    function markDirty(inp) { inp.dataset.dirty = '1'; }
    function clearDirty(inp) { delete inp.dataset.dirty; }
    function dirtyInputs() {
        return Array.prototype.slice.call(document.querySelectorAll('.mark-input[data-dirty="1"]'));
    }

    // ── (a) localStorage draft ─────────────────────────────────────────────
    function saveDraftToLocal() {
        const inputs = document.querySelectorAll('.mark-input');
        const draft = {};
        inputs.forEach(inp => {
            if (inp.value.trim() !== '') draft[inp.name] = inp.value;
        });
        try { localStorage.setItem(STORAGE_KEY, JSON.stringify(draft)); } catch (e) {}
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
                    markDirty(inp);
                    restored++;
                }
            });
            if (restored > 0) {
                setSaveStatus(`↺ Restored ${restored} draft marks`, 'bg-indigo-600 text-white', false);
                calculateAllRows();
            }
        } catch(e) {}
    }

    loadDraftFromLocal();

    // ── (b) AJAX auto-save to the database ────────────────────────────────
    function getHidden(name) {
        // Scope to the active marks form (the one containing .mark-input) so we never
        // accidentally read the FM/PM modal's hidden fields of the same name.
        const firstInp = document.querySelector('.mark-input');
        const scope = (firstInp && firstInp.closest('form')) || document;
        const el = scope.querySelector(`input[type="hidden"][name="${name}"]`);
        return el ? el.value : '';
    }

    function flushDirtyToServer() {
        // Prevent duplicate / conflicting saves: if a save is in flight, queue another.
        if (isSaving) { pendingFlush = true; return; }
        if (typeof navigator !== 'undefined' && navigator.onLine === false) {
            setSaveStatus('⚠ Offline — changes saved locally', 'bg-amber-600 text-white', true);
            return; // keep dirty cells; retry on reconnect
        }

        // Only send changed cells whose value is valid (blank & over-max are skipped).
        const cells = dirtyInputs().filter(inp => {
            const v = inp.value.trim();
            if (v === '') return false;                 // blank = not entered → skip (distinct from zero)
            const num = parseFloat(v);
            const max = parseFloat(inp.dataset.maxMarks) || 100;
            if (isNaN(num)) return false;
            return num <= max;                            // over-max cells are NOT auto-saved
        });
        if (cells.length === 0) return;

        isSaving = true;
        setSaveStatus('Saving…', 'bg-indigo-600 text-white', true);

        const fd = new FormData();
        ['session_id','semester_id','class_id','section_id','course_id'].forEach(n => {
            const v = getHidden(n);
            if (v !== null && v !== '') fd.append(n, v);
        });
        fd.append('_token', CSRF_TOKEN);

        // Snapshot each cell's sent value so a mid-flight edit isn't cleared wrongly.
        const sent = cells.map(inp => {
            const sid = inp.dataset.studentId;
            const cid = inp.dataset.courseId;
            const eid = inp.dataset.examId;
            const key = IS_ALL_SUBJECTS_MODE
                ? `student_course_mark[${sid}][${cid}]`
                : `student_mark[${sid}][${eid}]`;
            const value = inp.value;
            fd.append(key, value);
            return { inp, value };
        });

        fetch(STORE_URL, {
            method: 'POST',
            body: fd,
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        })
        .then(r => r.json().then(body => ({ ok: r.ok, body })))
        .then(({ ok, body }) => {
            if (ok && body && (body.status === 'success' || body.status === 'info')) {
                sent.forEach(s => { if (s.inp.value === s.value) clearDirty(s.inp); });
                setSaveStatus('✓ Saved (' + (body.count || sent.length) + ')', 'bg-emerald-600 text-white', false);
                saveDraftToLocal();
            } else {
                setSaveStatus('✗ ' + ((body && body.message) ? body.message : 'Save failed — will retry'), 'bg-rose-600 text-white', false);
            }
        })
        .catch(() => {
            setSaveStatus('✗ Network error — will retry', 'bg-rose-600 text-white', false);
        })
        .finally(() => {
            isSaving = false;
            if (pendingFlush) { pendingFlush = false; setTimeout(flushDirtyToServer, 600); }
        });
    }

    // Debounced trigger used by paste / FM-PM rule edits for snappier saves.
    let autoSaveTimer;
    function scheduleAutoSave() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(flushDirtyToServer, 1500);
    }

    // Expose hooks for the FM/PM modal, which lives in a separate <script> scope.
    window.marksFlushDirty = flushDirtyToServer;
    window.marksSetStatus  = setSaveStatus;
    window.marksMarkDirty  = markDirty;

    // Debounced localStorage draft save on every keystroke.
    let draftTimer;
    document.addEventListener('input', function(e) {
        if (e.target.classList && e.target.classList.contains('mark-input')) {
            clearTimeout(draftTimer);
            draftTimer = setTimeout(saveDraftToLocal, 1500);
        }
    });

    // Heartbeat: auto-save every 5 seconds (localStorage draft + DB flush).
    setInterval(function() { saveDraftToLocal(); flushDirtyToServer(); }, 5000);

    // Also flush when a cell loses focus (change event) for responsiveness.
    document.addEventListener('change', function(e) {
        if (e.target.classList && e.target.classList.contains('mark-input')) {
            scheduleAutoSave();
        }
    });

    // ── Offline / online handling: preserve pending changes, retry on reconnect
    window.addEventListener('online', function() {
        setSaveStatus('↻ Back online — syncing…', 'bg-indigo-600 text-white', true);
        flushDirtyToServer();
    });
    window.addEventListener('offline', function() {
        setSaveStatus('⚠ Offline — changes saved locally', 'bg-amber-600 text-white', true);
    });

    // ── Warn before leaving with unsaved changes ──────────────────────────
    window.addEventListener('beforeunload', function(e) {
        if (formSubmitting) return;
        if (isSaving || dirtyInputs().length > 0) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    // ── Manual submit: suppress the warning + clear the local draft ────────
    const firstMarkForForm = document.querySelector('.mark-input');
    const activeForm = firstMarkForForm ? firstMarkForForm.closest('form') : document.getElementById('masterMarksForm');
    if (activeForm) {
        activeForm.addEventListener('submit', function() {
            formSubmitting = true;
            try { localStorage.removeItem(STORAGE_KEY); } catch (e) {}
        });
    }
});
</script>

@push('modals')
{{-- FM/PM Edit Modal --}}
<div id="ruleEditModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[9999] items-center justify-center p-4"
     onclick="if(event.target===this)closeRuleEdit()">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="px-5 py-3.5 bg-indigo-600 flex items-center justify-between">
            <h3 class="text-white font-bold text-sm flex items-center gap-2">
                <i class="bi bi-pencil-square"></i> Edit Marks Rule
            </h3>
            <button onclick="closeRuleEdit()" class="w-7 h-7 flex items-center justify-center rounded-lg bg-white/20 hover:bg-white/30 text-white font-bold">&times;</button>
        </div>
        <form id="ruleEditForm" method="POST" action="{{ route('exam.rule.update') }}" class="p-5 space-y-4 bg-white dark:bg-slate-900">
            @csrf
            <input type="hidden" name="exam_id" id="ruleExamId">
            <input type="hidden" name="exam_rule_id" id="ruleRuleId">
            <input type="hidden" name="course_id" id="ruleCourseId">
            <input type="hidden" name="class_id" value="{{ $class_id }}">
            <input type="hidden" name="semester_id" value="{{ $semester_id }}">

            <div>
                <p class="text-xs font-bold text-slate-700 dark:text-slate-300 mb-3">
                    Subject: <span id="ruleSubjectName" class="text-indigo-600"></span>
                </p>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Full Marks (FM) <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" name="total_marks" id="ruleFM" min="1" max="1000" step="0.5" required
                           class="w-full px-3 py-2 rounded-xl border-2 border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-bold text-center outline-none focus:border-indigo-500"
                           style="font-size:16px;">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Pass Marks (PM) <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" name="pass_marks" id="rulePM" min="0" max="1000" step="0.5" required
                           class="w-full px-3 py-2 rounded-xl border-2 border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-bold text-center outline-none focus:border-rose-500"
                           style="font-size:16px;">
                </div>
            </div>
            <div id="ruleNote" class="text-[11px] text-slate-400 bg-slate-50 dark:bg-slate-800 rounded-lg p-2">
                <i class="bi bi-info-circle text-indigo-500 me-1"></i>
                <span id="ruleNoteText">Changes apply to this subject's exam rule immediately.</span>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="closeRuleEdit()"
                        class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl border border-slate-200 transition">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                    <i class="bi bi-check2 me-1"></i> Save Rule
                </button>
            </div>
        </form>
    </div>
</div>
<script>
function openRuleEdit(courseId, courseName, fm, pm, examId, ruleId) {
    document.getElementById('ruleCourseId').value  = courseId;
    document.getElementById('ruleExamId').value    = examId;
    document.getElementById('ruleRuleId').value    = ruleId;
    document.getElementById('ruleSubjectName').textContent = courseName;
    document.getElementById('ruleFM').value        = fm;
    document.getElementById('rulePM').value        = pm;
    const m = document.getElementById('ruleEditModal');
    m.classList.remove('hidden'); m.classList.add('flex');
}
function closeRuleEdit() {
    const m = document.getElementById('ruleEditModal');
    m.classList.add('hidden'); m.classList.remove('flex');
}
// Save FM/PM via AJAX (no page reload, so unsaved mark drafts are preserved) and
// update the table in place. Works for both Master Grade Ledger (course-keyed
// #fm-/#pm-) and Single Subject Entry (exam-keyed #fm-exam-/#pm-exam-).
document.getElementById('ruleEditForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const form = e.target;
    const courseId = document.getElementById('ruleCourseId').value;
    const examId   = document.getElementById('ruleExamId').value;
    const fm = parseFloat(document.getElementById('ruleFM').value);
    const pm = parseFloat(document.getElementById('rulePM').value);
    const noteText = document.getElementById('ruleNoteText');
    const noteBox  = document.getElementById('ruleNote');

    function showNote(msg, isError) {
        if (noteText) noteText.textContent = msg;
        if (noteBox) {
            noteBox.className = 'text-[11px] rounded-lg p-2 ' + (isError
                ? 'text-rose-600 bg-rose-50 dark:bg-rose-950/40 font-semibold'
                : 'text-slate-400 bg-slate-50 dark:bg-slate-800');
        }
    }

    // Client-side guard: Pass Marks cannot exceed Full Marks (requirement #7)
    if (isNaN(fm) || isNaN(pm) || pm > fm) {
        showNote('Pass Marks cannot exceed Full Marks.', true);
        return;
    }

    const fd = new FormData(form);
    fd.append('_token', CSRF_TOKEN);
    showNote('Saving rule…', false);

    fetch(RULE_UPDATE_URL, {
        method: 'POST',
        body: fd,
        headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
    })
    .then(r => r.json().then(body => ({ ok: r.ok, body })))
    .then(({ ok, body }) => {
        if (!ok || (body && body.status === 'error')) {
            showNote((body && body.message) ? body.message : 'Failed to update rule.', true);
            return;
        }

        // Update displayed FM/PM in the table headers (course-keyed + exam-keyed)
        [['fm-' + courseId, fm], ['fm-exam-' + examId, fm]].forEach(([id, v]) => { const el = document.getElementById(id); if (el) el.textContent = v; });
        [['pm-' + courseId, pm], ['pm-exam-' + examId, pm]].forEach(([id, v]) => { const el = document.getElementById(id); if (el) el.textContent = v; });

        // Update max + data-max-marks for the affected inputs and re-clamp over-max values
        const affected = new Set();
        document.querySelectorAll(`input.mark-input[data-course-id="${courseId}"]`).forEach(inp => affected.add(inp));
        document.querySelectorAll(`input.mark-input[data-exam-id="${examId}"]`).forEach(inp => affected.add(inp));
        affected.forEach(inp => {
            inp.max = fm;
            inp.dataset.maxMarks = fm;
            const cur = parseFloat(inp.value);
            if (!isNaN(cur) && cur > fm) {
                inp.value = fm;                 // keep marks consistent with the new Full Marks
                if (window.marksMarkDirty) window.marksMarkDirty(inp); else inp.dataset.dirty = '1';
            }
        });

        if (typeof calculateAllRows === 'function') calculateAllRows(); // recompute live totals/%/GPA/grade
        if (window.marksFlushDirty) window.marksFlushDirty();             // push re-clamped marks to the DB now
        if (window.marksSetStatus) window.marksSetStatus('✓ Marks rule updated', 'bg-emerald-600 text-white', false);
        closeRuleEdit();
    })
    .catch(() => {
        showNote('Network error — please try again.', true);
    });
});
document.addEventListener('keydown', e => { if(e.key==='Escape') closeRuleEdit(); });
</script>

{{-- ════════════════════════════════════════════════════════════
     SUBJECT REORDER MODAL (drag-and-drop + ↑↓ buttons)
     Visible only to Admins and Class Teachers
     ═══════════════════════════════════════════════════════════ --}}
@if(!empty($canEditAllSubjects) && isset($classCourses) && count($classCourses) > 0)
<div id="reorderModal"
     class="hidden fixed inset-0 z-[9999] items-center justify-center bg-black/40 backdrop-blur-sm p-4"
     role="dialog" aria-modal="true" aria-labelledby="reorderModalTitle">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-sm flex flex-col overflow-hidden border border-slate-200 dark:border-slate-700">

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200 dark:border-slate-700 bg-violet-50/60 dark:bg-violet-950/30">
            <div class="flex items-center gap-2 min-w-0">
                <i class="bi bi-arrow-down-up text-violet-600 dark:text-violet-400 text-base flex-shrink-0"></i>
                <div class="min-w-0">
                    <h3 id="reorderModalTitle" class="font-bold text-slate-800 dark:text-slate-100 text-sm leading-tight">Reorder Subjects</h3>
                    <p class="text-[11px] text-violet-600 dark:text-violet-400 font-semibold truncate mt-0.5">
                        <i class="bi bi-building-fill mr-0.5"></i>
                        {{ $selectedClass->class_name ?? ('Class #' . $class_id) }}
                        @if(!empty($selectedSection->section_name))
                            &bull; {{ $selectedSection->section_name }}
                        @endif
                    </p>
                </div>
            </div>
            <button type="button" onclick="closeReorderModal()" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 transition flex-shrink-0 ml-2" aria-label="Close">
                <i class="bi bi-x-lg text-sm"></i>
            </button>
        </div>

        {{-- Hint --}}
        <div class="px-4 py-2.5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40">
            <p class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                <i class="bi bi-grip-vertical text-slate-400"></i>
                Drag rows or use <strong>↑ ↓</strong> to reorder. Numbers update automatically.
                Click <strong>Save Order</strong> when done.
            </p>
        </div>

        {{-- Apply-to-other-classes panel --}}
        @if(isset($school_classes) && $school_classes->count() > 1)
        <div class="border-b border-slate-100 dark:border-slate-800">
            <button type="button" onclick="toggleClassPanel()"
                    id="classPanelToggle"
                    aria-expanded="false"
                    class="w-full flex items-center justify-between px-4 py-2.5 text-[11px] font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition">
                <span class="flex items-center gap-1.5">
                    <i class="bi bi-layers-fill text-violet-500"></i>
                    Also apply this order to other classes
                    <span id="classPanelCount" class="hidden ml-1 px-1.5 py-0.5 rounded-full bg-violet-500 text-white text-[9px] font-bold leading-none">0</span>
                </span>
                <i id="classPanelChevron" class="bi bi-chevron-down text-slate-400 text-xs transition-transform"></i>
            </button>
            <div id="classPanel" class="hidden px-4 pb-3 pt-1 max-h-44 overflow-y-auto bg-slate-50/60 dark:bg-slate-800/30">
                <p class="text-[10px] text-slate-400 mb-2 mt-0.5">
                    Same subject order will be applied to each selected class. Only subjects with matching names are updated.
                </p>
                <div class="space-y-0.5">
                @foreach($school_classes as $sc)
                <label class="flex items-center gap-2.5 py-1.5 px-2 rounded-lg cursor-pointer transition
                              hover:bg-white dark:hover:bg-slate-800
                              {{ $sc->id == $class_id ? 'opacity-50 pointer-events-none' : '' }}">
                    <input type="checkbox"
                           value="{{ $sc->id }}"
                           data-class-name="{{ $sc->class_name }}"
                           {{ $sc->id == $class_id ? 'checked disabled' : '' }}
                           onchange="updateClassPanelCount()"
                           class="apply-class-cb w-3.5 h-3.5 rounded border-slate-300 dark:border-slate-600 accent-violet-600">
                    <span class="text-xs font-medium text-slate-700 dark:text-slate-200 flex-1 truncate">
                        {{ $sc->class_name }}
                    </span>
                    @if($sc->id == $class_id)
                    <span class="text-[9px] text-violet-500 font-bold flex-shrink-0">Current</span>
                    @endif
                </label>
                @endforeach
                </div>
            </div>
        </div>
        @endif

        <ul id="reorderList" class="divide-y divide-slate-100 dark:divide-slate-800 overflow-y-auto max-h-72 select-none"
            aria-label="Subject list — drag to reorder">
            @foreach($classCourses as $loop_idx => $course)
            <li class="reorder-item flex items-center gap-3 px-4 py-2.5 bg-white dark:bg-slate-900 hover:bg-violet-50/30 dark:hover:bg-violet-950/20 transition cursor-grab active:cursor-grabbing"
                data-course-name="{{ $course->course_name }}"
                draggable="true">
                {{-- Position number badge — updated by JS on every move --}}
                <span class="reorder-pos w-5 h-5 rounded-full bg-violet-100 dark:bg-violet-900/50 text-violet-700 dark:text-violet-300 text-[10px] font-bold flex items-center justify-center flex-shrink-0 tabular-nums">
                    {{ $loop_idx + 1 }}
                </span>
                <span class="reorder-handle text-slate-300 dark:text-slate-600 text-base flex-shrink-0" aria-hidden="true">
                    <i class="bi bi-grip-vertical"></i>
                </span>
                <span class="flex-1 text-xs font-semibold text-slate-700 dark:text-slate-200 truncate">{{ $course->course_name }}</span>
                <div class="flex flex-col gap-0.5 flex-shrink-0">
                    <button type="button" onclick="moveReorderItem(this, -1)"
                        class="p-0.5 rounded hover:bg-violet-100 dark:hover:bg-violet-900/40 text-violet-500 transition"
                        aria-label="Move {{ $course->course_name }} up">
                        <i class="bi bi-chevron-up text-[11px]"></i>
                    </button>
                    <button type="button" onclick="moveReorderItem(this, 1)"
                        class="p-0.5 rounded hover:bg-violet-100 dark:hover:bg-violet-900/40 text-violet-500 transition"
                        aria-label="Move {{ $course->course_name }} down">
                        <i class="bi bi-chevron-down text-[11px]"></i>
                    </button>
                </div>
            </li>
            @endforeach
        </ul>

        {{-- Footer --}}
        <div class="flex items-start justify-between gap-3 px-5 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60">
            <div id="reorderStatus" class="text-[10px] text-slate-400 dark:text-slate-500 flex flex-col gap-0.5 min-w-0 flex-1"></div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <button type="button" onclick="closeReorderModal()"
                    class="px-4 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-semibold hover:bg-slate-50 transition">
                    Cancel
                </button>
                <button type="button" onclick="saveSubjectOrder()"
                    id="reorderSaveBtn"
                    class="px-4 py-1.5 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold shadow-md shadow-violet-600/20 transition flex items-center gap-1.5">
                    <i class="bi bi-check-circle-fill"></i> Save Order
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// ── Reorder Modal ──────────────────────────────────────────────────────────
const _reorderClassId = {{ (int)$class_id }};
const _reorderRoute   = '{{ route("school.course.reorder") }}';
const _csrfToken      = '{{ csrf_token() }}';

function openReorderModal() {
    const m = document.getElementById('reorderModal');
    m.classList.remove('hidden');
    m.classList.add('flex');
    document.getElementById('reorderStatus').innerHTML = '';
    initDragAndDrop();
}

function closeReorderModal() {
    const m = document.getElementById('reorderModal');
    m.classList.add('hidden');
    m.classList.remove('flex');
}

// ↑↓ button handler
function moveReorderItem(btn, direction) {
    const li = btn.closest('li');
    const list = document.getElementById('reorderList');
    const items = [...list.querySelectorAll('li.reorder-item')];
    const idx = items.indexOf(li);
    const targetIdx = idx + direction;
    if (targetIdx < 0 || targetIdx >= items.length) return;
    if (direction === -1) {
        list.insertBefore(li, items[targetIdx]);
    } else {
        items[targetIdx].insertAdjacentElement('afterend', li);
    }
    li.scrollIntoView({ block: 'nearest' });
    updatePositionNumbers();
}

// Auto-update the numbered badge on every item after any reorder
function updatePositionNumbers() {
    document.querySelectorAll('#reorderList li.reorder-item').forEach((li, idx) => {
        const badge = li.querySelector('.reorder-pos');
        if (badge) badge.textContent = idx + 1;
    });
}

// Drag-and-drop
let _dragSrc = null;
function initDragAndDrop() {
    const list = document.getElementById('reorderList');
    list.querySelectorAll('li.reorder-item').forEach(item => {
        item.addEventListener('dragstart', function(e) {
            _dragSrc = this;
            this.classList.add('opacity-50', 'ring-2', 'ring-violet-400');
            e.dataTransfer.effectAllowed = 'move';
        });
        item.addEventListener('dragend', function() {
            this.classList.remove('opacity-50', 'ring-2', 'ring-violet-400');
            list.querySelectorAll('li.reorder-item').forEach(i => i.classList.remove('border-t-2', 'border-violet-400'));
        });
        item.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            list.querySelectorAll('li.reorder-item').forEach(i => i.classList.remove('border-t-2', 'border-violet-400'));
            if (this !== _dragSrc) this.classList.add('border-t-2', 'border-violet-400');
        });
        item.addEventListener('drop', function(e) {
            e.preventDefault();
            if (_dragSrc && this !== _dragSrc) {
                const allItems = [...list.querySelectorAll('li.reorder-item')];
                const srcIdx  = allItems.indexOf(_dragSrc);
                const destIdx = allItems.indexOf(this);
                if (srcIdx < destIdx) {
                    this.insertAdjacentElement('afterend', _dragSrc);
                } else {
                    list.insertBefore(_dragSrc, this);
                }
            }
            list.querySelectorAll('li.reorder-item').forEach(i => i.classList.remove('border-t-2', 'border-violet-400'));
            updatePositionNumbers();
        });
    });
}

// Save order to server — loops through the current class + any additionally selected classes
function saveSubjectOrder() {
    const list  = document.getElementById('reorderList');
    const items = [...list.querySelectorAll('li.reorder-item')];
    const order = items.map((el, idx) => ({ course_name: el.dataset.courseName, position: idx }));

    // Build list of class IDs to update: current class first, then checked extras
    const classIds = [_reorderClassId];
    document.querySelectorAll('.apply-class-cb:not([disabled]):checked').forEach(cb => {
        const id = parseInt(cb.value);
        if (!isNaN(id) && !classIds.includes(id)) classIds.push(id);
    });

    const btn    = document.getElementById('reorderSaveBtn');
    const status = document.getElementById('reorderStatus');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Saving…';
    status.innerHTML = '';

    // Show per-class progress inside status
    const classNames = {};
    document.querySelectorAll('.apply-class-cb[data-class-name]').forEach(cb => {
        classNames[parseInt(cb.value)] = cb.dataset.className;
    });
    classNames[_reorderClassId] = classNames[_reorderClassId] || 'Current Class';

    let results = {};
    classIds.forEach(id => { results[id] = 'pending'; });

    function renderStatus() {
        status.innerHTML = classIds.map(id => {
            const name = classNames[id] || ('Class #' + id);
            if (results[id] === 'pending')  return `<span class="flex items-center gap-1"><i class="bi bi-hourglass-split text-slate-400 text-[10px]"></i><span class="text-slate-400">${name}</span></span>`;
            if (results[id] === 'ok')       return `<span class="flex items-center gap-1"><i class="bi bi-check-circle-fill text-emerald-500 text-[10px]"></i><span class="text-emerald-600 dark:text-emerald-400">${name}</span></span>`;
            return `<span class="flex items-center gap-1"><i class="bi bi-x-circle-fill text-rose-500 text-[10px]"></i><span class="text-rose-500">${name}</span></span>`;
        }).join('');
    }
    renderStatus();

    // Sequential saves — one per class
    function saveNext(idx) {
        if (idx >= classIds.length) {
            // All done
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Save Order';
            const allOk = Object.values(results).every(v => v === 'ok');
            if (allOk) {
                status.innerHTML += '<span class="block text-emerald-600 dark:text-emerald-400 text-[10px] mt-1 font-semibold">All saved — reloading…</span>';
                setTimeout(() => window.location.reload(), 900);
            }
            return;
        }
        const classId = classIds[idx];
        fetch(_reorderRoute, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ class_id: classId, order: order }),
        })
        .then(r => r.json())
        .then(data => {
            results[classId] = data.success ? 'ok' : 'error';
            renderStatus();
            saveNext(idx + 1);
        })
        .catch(() => {
            results[classId] = 'error';
            renderStatus();
            saveNext(idx + 1);
        });
    }
    saveNext(0);
}

// Toggle the class panel open/closed
function toggleClassPanel() {
    const panel   = document.getElementById('classPanel');
    const chevron = document.getElementById('classPanelChevron');
    const toggle  = document.getElementById('classPanelToggle');
    const hidden  = panel.classList.toggle('hidden');
    chevron.style.transform = hidden ? '' : 'rotate(-180deg)';
    toggle.setAttribute('aria-expanded', hidden ? 'false' : 'true');
}

// Update the badge showing how many extra classes are selected
function updateClassPanelCount() {
    const count = document.querySelectorAll('.apply-class-cb:not([disabled]):checked').length;
    const badge = document.getElementById('classPanelCount');
    if (!badge) return;
    if (count > 0) {
        badge.textContent = count;
        badge.classList.remove('hidden');
    } else {
        badge.classList.add('hidden');
    }
}

document.addEventListener('keydown', e => { if(e.key==='Escape') closeReorderModal(); });
// Close on backdrop click
document.getElementById('reorderModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeReorderModal();
});
</script>
@endif

@endpush
@endsection
