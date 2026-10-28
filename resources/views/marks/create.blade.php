@extends('layouts.app')

@section('content')
<style>
/* Prevent iOS auto-zoom on inputs and selects */
@media screen and (max-width: 767.98px) {
    input.mark-input,
    input.single-mark-input,
    input.calc-mark-input,
    input.final-mark-input,
    #marksFilterForm select,
    #studentQuickFilter,
    #singleStudentQuickFilter {
        font-size: 16px !important; /* 16px stops iOS Safari from auto-zooming */
        min-height: 42px !important;
    }
}

/* Horizontal scroll styling */
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

/* Base Card View Layout (Default on mobile < 768px unless force-table-view is toggled) */
@media (max-width: 767.98px) {
    .marks-card-layout:not(.force-table-view),
    .marks-card-layout:not(.force-table-view) tbody,
    .marks-card-layout:not(.force-table-view) tr {
        display: block !important;
        width: 100% !important;
    }

    .marks-card-layout:not(.force-table-view) thead {
        display: none !important;
    }

    .marks-card-layout:not(.force-table-view) tbody {
        padding: 0.5rem 0.25rem !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 0.875rem !important;
    }

    .marks-card-layout:not(.force-table-view) tr.student-mark-row {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 1.25rem !important;
        padding: 0.875rem !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.03) !important;
        display: flex !important;
        flex-wrap: wrap !important;
        width: 100% !important;
        box-sizing: border-box !important;
        transition: box-shadow 0.2s ease, border-color 0.2s ease !important;
    }

    .dark .marks-card-layout:not(.force-table-view) tr.student-mark-row {
        background: #1e293b !important;
        border-color: #334155 !important;
    }

    .marks-card-layout:not(.force-table-view) tr.student-mark-row.has-unsaved {
        border-color: #f59e0b !important;
        box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2) !important;
    }

    /* Un-stick table cells on mobile so they stack cleanly inside the card */
    .marks-card-layout:not(.force-table-view) td.sticky {
        position: static !important;
        left: auto !important;
        background: transparent !important;
        box-shadow: none !important;
        width: 100% !important;
    }

    .marks-card-layout:not(.force-table-view) td.cell-roll {
        display: none !important;
    }

    .marks-card-layout:not(.force-table-view) td.cell-student {
        display: block !important;
        border-bottom: 1px solid #f1f5f9 !important;
        padding: 0 0 0.75rem 0 !important;
        margin-bottom: 0.35rem !important;
        width: 100% !important;
    }
    .dark .marks-card-layout:not(.force-table-view) td.cell-student {
        border-bottom-color: #334155 !important;
    }

    .marks-card-layout:not(.force-table-view) td.cell-mark {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        padding: 0.5rem 0.25rem !important;
        border-left: none !important;
        border-bottom: 1px dashed #f1f5f9 !important;
        width: 100% !important;
    }
    .dark .marks-card-layout:not(.force-table-view) td.cell-mark {
        border-bottom-color: #334155 !important;
    }

    .marks-card-layout:not(.force-table-view) td.cell-mark .mark-input {
        width: 120px !important;
        max-width: 130px !important;
        padding: 0.5rem 0.75rem !important;
        font-size: 16px !important;
        border-radius: 0.75rem !important;
        text-align: center !important;
        touch-action: manipulation !important;
        position: relative !important;
        z-index: 2 !important;
    }

    /* Mini-Dashboard calculation summary at bottom of card */
    .marks-card-layout:not(.force-table-view) td.cell-calc {
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        flex: 1 1 0% !important;
        width: auto !important;
        float: none !important;
        box-sizing: border-box !important;
        padding: 0.5rem 0.2rem !important;
        margin-top: 0.5rem !important;
        background: #f8fafc !important;
        border-left: none !important;
        border-top: 1px solid #f1f5f9 !important;
        border-bottom: none !important;
        text-align: center !important;
    }
    .dark .marks-card-layout:not(.force-table-view) td.cell-calc {
        background: #0f172a !important;
        border-top-color: #334155 !important;
    }
    .marks-card-layout:not(.force-table-view) td.cell-calc-total {
        border-top-left-radius: 0.75rem !important;
        border-bottom-left-radius: 0.75rem !important;
    }
    .marks-card-layout:not(.force-table-view) td.cell-calc-grade {
        border-top-right-radius: 0.75rem !important;
        border-bottom-right-radius: 0.75rem !important;
    }
    .marks-card-layout:not(.force-table-view) {
        overflow-x: visible !important;
    }
}

/* Force Card View if user toggles Card mode on tablet/desktop */
.force-card-view,
.force-card-view tbody,
.force-card-view tr {
    display: block !important;
    width: 100% !important;
}
.force-card-view thead {
    display: none !important;
}
.force-card-view tbody {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)) !important;
    gap: 1rem !important;
    padding: 0.75rem !important;
}
.force-card-view tr.student-mark-row {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 1.25rem !important;
    padding: 1rem !important;
    box-shadow: 0 2px 6px -1px rgba(0, 0, 0, 0.06) !important;
    display: flex !important;
    flex-wrap: wrap !important;
    width: 100% !important;
    box-sizing: border-box !important;
}
.dark .force-card-view tr.student-mark-row {
    background: #1e293b !important;
    border-color: #334155 !important;
}
.force-card-view td.sticky {
    position: static !important;
    left: auto !important;
    background: transparent !important;
    box-shadow: none !important;
    width: 100% !important;
}
.force-card-view td.cell-roll {
    display: none !important;
}
.force-card-view td.cell-student {
    display: block !important;
    border-bottom: 1px solid #f1f5f9 !important;
    padding: 0 0 0.75rem 0 !important;
    margin-bottom: 0.25rem !important;
    width: 100% !important;
}
.force-card-view .mobile-only-roll,
.force-card-view .mobile-field-label {
    display: block !important;
}
.force-card-view td.cell-mark {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    padding: 0.5rem 0.25rem !important;
    border-left: none !important;
    border-bottom: 1px dashed #f1f5f9 !important;
    width: 100% !important;
}
.force-card-view td.cell-mark .mark-input {
    width: 110px !important;
    font-size: 16px !important;
    touch-action: manipulation !important;
    position: relative !important;
    z-index: 2 !important;
}
.force-card-view td.cell-calc {
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    justify-content: center !important;
    flex: 1 1 0% !important;
    width: auto !important;
    float: none !important;
    background: #f8fafc !important;
    border-top: 1px solid #f1f5f9 !important;
    border-left: none !important;
    border-bottom: none !important;
    padding: 0.4rem 0.2rem !important;
    font-size: 0.75rem !important;
    text-align: center !important;
    margin-top: 0.5rem !important;
    box-sizing: border-box !important;
}
.dark .force-card-view td.cell-calc {
    background: #0f172a !important;
    border-top-color: #334155 !important;
}
.force-card-view td.cell-calc-total {
    border-top-left-radius: 0.75rem !important;
    border-bottom-left-radius: 0.75rem !important;
}
.force-card-view td.cell-calc-grade {
    border-top-right-radius: 0.75rem !important;
    border-bottom-right-radius: 0.75rem !important;
}

/* Force Table / Grid View (Spreadsheet mode on any screen size) */
.force-table-view {
    display: table !important;
    width: 100% !important;
}
.force-table-view thead {
    display: table-header-group !important;
}
.force-table-view tbody {
    display: table-row-group !important;
    padding: 0 !important;
}
.force-table-view tr.student-mark-row {
    display: table-row !important;
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
    padding: 0 !important;
}
.force-table-view td {
    display: table-cell !important;
    width: auto !important;
    float: none !important;
}
.force-table-view td.cell-roll {
    display: table-cell !important;
}
.force-table-view td.cell-student {
    display: table-cell !important;
    border-bottom: none !important;
    padding: 0.75rem 1rem !important;
    margin-bottom: 0 !important;
}
.force-table-view td.cell-mark {
    display: table-cell !important;
    padding: 0.6rem 0.75rem !important;
    border-left: 1px solid rgba(226, 232, 240, 0.6) !important;
    border-bottom: none !important;
    text-align: center !important;
}
.dark .force-table-view td.cell-mark {
    border-left-color: rgba(51, 65, 85, 0.6) !important;
}
.force-table-view td.cell-mark .mark-input {
    width: 90px !important;
    max-width: 100px !important;
    font-size: 0.75rem !important;
}
.force-table-view td.cell-calc {
    display: table-cell !important;
    width: auto !important;
    float: none !important;
    padding: 0.75rem !important;
    margin-top: 0 !important;
    border-radius: 0 !important;
}
.force-table-view .mobile-only-roll,
.force-table-view .mobile-field-label {
    display: none !important;
}
</style>

<div class="px-3 sm:px-4 lg:px-8 py-4 sm:py-6 max-w-7xl mx-auto space-y-4 sm:space-y-6 overflow-x-hidden w-full max-w-full">

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 sm:gap-4">
        <div>
            <div class="flex items-start sm:items-center gap-2.5 sm:gap-3">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl sm:rounded-2xl bg-gradient-to-tr from-indigo-600 to-indigo-500 text-white flex items-center justify-center font-bold text-lg sm:text-xl shadow-md shadow-indigo-500/20 flex-shrink-0 mt-0.5 sm:mt-0">
                    <i class="bi bi-pencil-square"></i>
                </span>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                        <h1 class="text-lg sm:text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">
                            Enter Marks & Grades
                        </h1>
                        @if(!empty($isAdmin))
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-950/80 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 whitespace-nowrap">
                            <i class="bi bi-shield-lock-fill text-indigo-500"></i> Admin Mode
                        </span>
                        @elseif(!empty($isClassTeacher))
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-950/80 dark:text-amber-300 border border-amber-200 dark:border-amber-800 whitespace-nowrap">
                            <i class="bi bi-star-fill text-amber-500"></i> Class Teacher
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 whitespace-nowrap">
                            <i class="bi bi-person-badge-fill text-emerald-500"></i> Subject Teacher
                        </span>
                        @endif
                    </div>
                    <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate">
                        @if(!empty($isAdmin) || !empty($isClassTeacher))
                            Master grade ledger across all subjects with individual class marksheet printing & evaluations
                        @else
                            Record marks and submit evaluations for your assigned subjects
                        @endif
                    </p>
                </div>
            </div>
        </div>
        <nav aria-label="breadcrumb" class="hidden sm:block">
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
        $marksSubmissionStatus = is_array($academic_setting)
            ? ($academic_setting['marks_submission_status'] ?? 'off')
            : ($academic_setting->marks_submission_status ?? 'off');
        $isWindowOpen = ($marksSubmissionStatus === 'on');
    @endphp

    @if($isWindowOpen)
        <div class="p-2.5 sm:p-3.5 rounded-2xl bg-emerald-50/80 dark:bg-emerald-950/30 border border-emerald-200/80 dark:border-emerald-800/50 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 shadow-xs">
            <div class="flex items-center gap-2.5 min-w-0">
                <span class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-900/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm flex-shrink-0">
                    <i class="bi bi-unlock-fill"></i>
                </span>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-xs font-bold text-emerald-900 dark:text-emerald-200">Marks Submission Window: OPEN</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-emerald-200/70 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300">Active</span>
                    </div>
                    <p class="text-[11px] text-emerald-700/80 dark:text-emerald-400 hidden sm:block">Teachers and administrators can record and submit term evaluations.</p>
                </div>
            </div>
            @if(!empty($isAdmin))
                <form action="{{ route('final.marks.submission.status.update') }}" method="POST" class="flex-shrink-0 self-start sm:self-center pl-10 sm:pl-0">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 border border-slate-200 dark:border-slate-700 transition shadow-xs flex items-center gap-1.5" onclick="return confirm('Lock submission window for teachers?')">
                        <i class="bi bi-lock"></i> Close for Teachers
                    </button>
                </form>
            @endif
        </div>
    @else
        <div class="p-2.5 sm:p-3.5 rounded-2xl bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-800/50 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 shadow-xs">
            <div class="flex items-center gap-2.5 min-w-0">
                <span class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-900/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm flex-shrink-0">
                    <i class="bi bi-shield-lock-fill"></i>
                </span>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-xs font-bold text-amber-900 dark:text-amber-200">Submission Window Closed for Teachers</span>
                        @if(!empty($isAdmin))
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300">⚡ Admin Access Authorized</span>
                        @endif
                    </div>
                    <p class="text-[11px] text-amber-700/80 dark:text-amber-400 hidden sm:block">
                        @if(!empty($isAdmin))
                            As an administrator, you have full privileges to enter and update marks directly anytime.
                        @else
                            Marks submission is currently locked by administration. Please contact your Exam Controller.
                        @endif
                    </p>
                </div>
            </div>
            @if(!empty($isAdmin))
                <form action="{{ route('final.marks.submission.status.update') }}" method="POST" class="flex-shrink-0 self-start sm:self-center pl-10 sm:pl-0">
                    @csrf
                    <input type="hidden" name="marks_submission_status" value="on">
                    <button type="submit" class="px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-sm flex items-center gap-1.5">
                        <i class="bi bi-unlock-fill"></i> Open for Teachers
                    </button>
                </form>
            @endif
        </div>
    @endif

    {{-- Filter Card --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl sm:rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-4 sm:p-6">
        <div class="flex items-center justify-between gap-4 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2.5">
                <span class="w-2.5 h-6 bg-indigo-600 rounded-full"></span>
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">
                    @if(!empty($canEditAllSubjects))
                        Select Examination, Class & Entry Scope
                    @else
                        Select Your Assigned Class & Subject
                    @endif
                </h2>
            </div>
            <div class="flex items-center gap-2 flex-wrap text-xs text-slate-500 dark:text-slate-400 font-medium">
                {{-- Network Status Pill --}}
                <div class="network-status-pill inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-[11px] font-semibold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 shadow-2xs" title="Live Connection Quality">
                    <span class="status-dot w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span class="status-text">Online</span>
                </div>
                {{-- Auto-Draft Cache Pill --}}
                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-[11px] font-medium text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 shadow-2xs" title="Continuous Local Auto-Save (Zero Data Loss in Low Internet)">
                    <span class="draft-status-text">💾 Auto-Draft: Active</span>
                </div>
                <div class="hidden sm:flex items-center gap-1.5 text-xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                    <span>Session: <strong class="text-slate-700 dark:text-slate-200">{{ session('current_session_name', '2083-2084') }}</strong></span>
                </div>
            </div>
        </div>

        @if(empty($isAdmin) && (!isset($school_classes) || count($school_classes) === 0))
            <div class="mb-4 p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200 text-xs flex items-start gap-3">
                <i class="bi bi-exclamation-triangle-fill text-amber-500 text-lg flex-shrink-0 mt-0.5"></i>
                <div>
                    <strong class="font-bold block mb-0.5">No Classes Assigned to You Yet</strong>
                    <span>You do not have any classes assigned for teaching in the current academic session. Please contact your School Administrator or Academic Coordinator to assign your classes and subjects in <strong>Academic Settings &rarr; Assign Teachers</strong>.</span>
                </div>
            </div>
        @endif

        <form action="{{ route('course.mark.create') }}" method="GET" id="marksFilterForm" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Semester / Terminal Exam --}}
            <div>
                <label for="filter_semester_id" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                    Terminal Exam / Semester <span class="text-rose-500">*</span>
                </label>
                <select id="filter_semester_id" name="semester_id" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                    @isset($semesters)
                        @foreach ($semesters as $sem)
                            <option value="{{ $sem->id }}" {{ $semester_id == $sem->id ? 'selected' : '' }}>
                                {{ $sem->semester_name }}
                            </option>
                        @endforeach
                    @endisset
                </select>
            </div>

            {{-- Class / Grade --}}
            <div>
                <label for="filter_class_id" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                    Class / Grade <span class="text-rose-500">*</span>
                </label>
                <select id="filter_class_id" name="class_id" required onchange="handleClassChange(this.value)" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                    <option value="">-- Choose Class --</option>
                    @isset($school_classes)
                        @foreach ($school_classes as $sc)
                            <option value="{{ $sc->id }}" {{ $class_id == $sc->id ? 'selected' : '' }}>
                                {{ $sc->class_name }}
                            </option>
                        @endforeach
                    @endisset
                </select>
            </div>

            {{-- Section --}}
            <div>
                <label for="filter_section_id" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 flex items-center justify-between">
                    <span>Section</span>
                    <span id="sectionLoadingSpinner" class="hidden text-indigo-600 dark:text-indigo-400 text-xs">
                        <i class="bi bi-arrow-repeat animate-spin"></i>
                    </span>
                </label>
                <select id="filter_section_id" name="section_id" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                    <option value="0">{{ !empty($canEditAllSubjects) ? 'All Sections (Entire Class)' : 'All Assigned Sections' }}</option>
                    @if(isset($classSections) && count($classSections) > 0)
                        @foreach($classSections as $sec)
                            <option value="{{ $sec->id }}" {{ (string)$section_id === (string)$sec->id ? 'selected' : '' }}>
                                {{ $sec->section_name }} (Room {{ $sec->room_no ?? 'N/A' }})
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>

            {{-- Scope / Subject --}}
            <div>
                <label for="filter_course_id" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 flex items-center justify-between">
                    <span>Subject / Scope <span class="text-rose-500">*</span></span>
                    <span id="courseLoadingSpinner" class="hidden text-indigo-600 dark:text-indigo-400 text-xs">
                        <i class="bi bi-arrow-repeat animate-spin"></i>
                    </span>
                </label>
                <select id="filter_course_id" name="course_id" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-indigo-200 dark:border-indigo-800/80 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-medium transition">
                    @if(!empty($canEditAllSubjects))
                        <option value="all" {{ ($course_id === 'all' || empty($course_id) || $course_id == '0') ? 'selected' : '' }}>
                            ⚡ All Subjects at Once (Master Grade Ledger)
                        </option>
                    @else
                        <option value="">-- Choose Your Assigned Subject --</option>
                    @endif
                    @if(isset($classCourses) && count($classCourses) > 0)
                        @if(!empty($canEditAllSubjects))
                            <optgroup label="Individual Subjects">
                        @endif
                        @foreach($classCourses as $crs)
                            <option value="{{ $crs->id }}" {{ (string)$course_id === (string)$crs->id ? 'selected' : '' }}>
                                {{ $crs->course_name }} ({{ $crs->course_type ?? 'Theory' }})
                            </option>
                        @endforeach
                        @if(!empty($canEditAllSubjects))
                            </optgroup>
                        @endif
                    @endif
                </select>
            </div>

            <div class="sm:col-span-2 lg:col-span-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                <div class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                    <i class="bi bi-info-circle text-indigo-500"></i>
                    @if(!empty($canEditAllSubjects))
                        <span>Selecting <strong>All Subjects</strong> enables master bulk grade entry across all courses. Only assigned Class Teachers and Administrators have this privilege.</span>
                    @else
                        <span>You are viewing your assigned subjects. Only the designated <strong>Class Teacher</strong> or an Administrator can edit marks for all subjects and print class results.</span>
                    @endif
                </div>
                <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/20 transition flex items-center justify-center gap-2">
                    <i class="bi bi-table"></i> Load Grade Sheet
                </button>
            </div>
        </form>
    </div>

    @if($class_id)
        {{-- Professional Class & Evaluation Breadcrumb Hero Card --}}
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-3xl p-6 shadow-xl border border-indigo-900/50 relative">
            <div class="absolute inset-0 rounded-3xl overflow-hidden pointer-events-none">
                <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-indigo-500/10 rounded-full blur-3xl"></div>
            </div>

            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
                <div>
                    <div class="flex items-center gap-2 mb-2 flex-wrap">
                        <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-indigo-500/30 text-indigo-200 border border-indigo-400/30 flex items-center gap-1.5">
                            <i class="bi bi-mortarboard-fill text-indigo-300"></i>
                            Class: {{ $selectedClass->class_name ?? 'Class #'.$class_id }}
                        </span>
                        <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-white/10 text-slate-200 border border-white/10 flex items-center gap-1.5">
                            <i class="bi bi-diagram-3"></i>
                            Section: {{ $selectedSection->section_name ?? 'All Sections' }}
                        </span>
                        <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 flex items-center gap-1.5">
                            <i class="bi bi-calendar2-check"></i>
                            {{ $selectedSemester->semester_name ?? 'Terminal Examination' }}
                        </span>
                    </div>

                    <h2 class="text-xl md:text-2xl font-black text-white tracking-tight">
                        @if($isAllSubjectsMode)
                            Master Grade Ledger — {{ $selectedClass->class_name ?? 'Selected Class' }}
                        @else
                            Single Subject Entry — {{ $selectedCourse->course_name ?? 'Subject Evaluation' }}
                        @endif
                    </h2>
                    <p class="text-xs text-indigo-200/80 mt-1 flex items-center gap-3 flex-wrap">
                        <span><strong class="text-white">{{ count($sectionStudents) }}</strong> Students Enrolled</span>
                        <span>•</span>
                        <span><strong class="text-white">{{ count($classCourses) }}</strong> {{ !empty($isAdmin) ? 'Total Subjects' : 'Assigned Subjects' }}</span>
                        @if(!$isAllSubjectsMode && $selectedCourse)
                            <span>•</span>
                            <span>Course Type: <strong class="text-white">{{ $selectedCourse->course_type ?? 'Theory' }}</strong></span>
                        @endif
                    </p>
                </div>

                <div class="flex items-center gap-3 flex-wrap">
                    @if(!empty($canEditAllSubjects))
                        <a href="{{ route('results.class.report-cards', ['class_id' => $class_id, 'section_id' => $section_id, 'semester_id' => $semester_id, 'stream' => 1]) }}" 
                           target="_blank" 
                           data-no-loader
                           class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-xs font-bold shadow-lg transition flex items-center gap-2 flex-shrink-0"
                           title="Print individual official report cards for every student in the whole class on separate A4 pages">
                            <i class="bi bi-printer-fill"></i>
                            <span class="hidden sm:inline">Print Class Marksheets</span>
                            <span class="sm:hidden">Print Marksheets</span>
                        </a>
                    @endif

                    {{-- Mode Switcher Tabs --}}
                    @if(!empty($canEditAllSubjects))
                        <div class="flex items-center gap-2 bg-white/10 p-1.5 rounded-2xl backdrop-blur-md border border-white/10 flex-shrink-0">
                            <a href="{{ route('course.mark.create', ['semester_id' => $semester_id, 'class_id' => $class_id, 'section_id' => $section_id, 'course_id' => 'all']) }}" 
                               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ $isAllSubjectsMode ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                                <i class="bi bi-grid-3x3-gap-fill text-[11px]"></i>
                                <span>All Subjects (Ledger)</span>
                            </a>
                            @if(count($classCourses) > 0)
                                <div class="relative">
                                    <button type="button" onclick="toggleSubjectDropdown(event, this)" class="subject-dropdown-toggle px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ !$isAllSubjectsMode ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                                        <i class="bi bi-journal-text text-[11px]"></i>
                                        <span class="max-w-[120px] truncate">{{ !$isAllSubjectsMode && $selectedCourse ? $selectedCourse->course_name : 'Single Subject' }}</span>
                                        <i class="bi bi-chevron-down text-[10px]"></i>
                                    </button>
                                    <div class="dropdown-menu-card absolute right-0 mt-2 w-72 max-w-[calc(100vw-2.5rem)] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 py-2 hidden z-50 max-h-72 overflow-y-auto">
                                        <div class="px-3 py-1 text-[10px] uppercase font-bold text-slate-400">Switch to Specific Subject</div>
                                        @foreach($classCourses as $courseItem)
                                            <a href="{{ route('course.mark.create', ['semester_id' => $semester_id, 'class_id' => $class_id, 'section_id' => $section_id, 'course_id' => $courseItem->id]) }}" 
                                               class="px-3 py-2 text-xs text-slate-700 dark:text-slate-200 hover:bg-indigo-50 dark:hover:bg-slate-800 hover:text-indigo-600 flex items-center justify-between {{ (!$isAllSubjectsMode && (string)$course_id === (string)$courseItem->id) ? 'bg-indigo-50 dark:bg-slate-800 text-indigo-600 font-bold' : '' }}">
                                                <span class="truncate">{{ $courseItem->course_name }}</span>
                                                <span class="text-[10px] text-slate-400">({{ $courseItem->course_type ?? 'Theory' }})</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        @if(count($classCourses) > 1)
                            <div class="relative flex-shrink-0">
                                <button type="button" onclick="toggleSubjectDropdown(event, this)" class="subject-dropdown-toggle px-3.5 py-2 rounded-xl text-xs font-bold bg-white/10 hover:bg-white/20 text-white backdrop-blur-md border border-white/10 transition flex items-center gap-2">
                                    <i class="bi bi-journal-check text-indigo-300"></i>
                                    <span class="max-w-[140px] truncate">{{ $selectedCourse ? $selectedCourse->course_name : 'Select Subject' }}</span>
                                    <i class="bi bi-chevron-down text-[10px]"></i>
                                </button>
                                <div class="dropdown-menu-card absolute right-0 mt-2 w-72 max-w-[calc(100vw-2.5rem)] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 py-2 hidden z-50 max-h-72 overflow-y-auto">
                                    <div class="px-3 py-1 text-[10px] uppercase font-bold text-slate-400">Your Assigned Subjects</div>
                                    @foreach($classCourses as $courseItem)
                                        <a href="{{ route('course.mark.create', ['semester_id' => $semester_id, 'class_id' => $class_id, 'section_id' => $section_id, 'course_id' => $courseItem->id]) }}" 
                                           class="px-3 py-2 text-xs text-slate-700 dark:text-slate-200 hover:bg-indigo-50 dark:hover:bg-slate-800 hover:text-indigo-600 flex items-center justify-between {{ ((string)$course_id === (string)$courseItem->id) ? 'bg-indigo-50 dark:bg-slate-800 text-indigo-600 font-bold' : '' }}">
                                            <span class="truncate">{{ $courseItem->course_name }}</span>
                                            <span class="text-[10px] text-slate-400">({{ $courseItem->course_type ?? 'Theory' }})</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Fast Horizontal Touch Subject Switcher Bar (Mobile & Desktop) --}}
            @if(count($classCourses) > 0)
                <div class="mt-4 pt-3 border-t border-white/10">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-200/80 flex items-center gap-1">
                            <i class="bi bi-lightning-charge-fill text-amber-400"></i> Fast Switch Scope:
                        </span>
                        <span class="text-[10px] text-indigo-300/60 hidden sm:inline">1-tap to switch subject</span>
                    </div>
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 no-scrollbar -mx-2 px-2 touch-pan-x">
                        @if(!empty($canEditAllSubjects))
                            <a href="{{ route('course.mark.create', ['semester_id' => $semester_id, 'class_id' => $class_id, 'section_id' => $section_id, 'course_id' => 'all']) }}" 
                               class="px-3 py-1.5 rounded-xl text-xs whitespace-nowrap transition flex items-center gap-1.5 flex-shrink-0 {{ $isAllSubjectsMode ? 'bg-indigo-600 text-white font-bold shadow-md ring-2 ring-indigo-400/50' : 'bg-white/10 text-slate-200 hover:bg-white/20' }}">
                                <i class="bi bi-grid-3x3-gap-fill text-[10px]"></i>
                                <span>⚡ All Subjects</span>
                            </a>
                        @endif
                        @foreach($classCourses as $courseItem)
                            @php
                                $isCur = (!$isAllSubjectsMode && (string)$course_id === (string)$courseItem->id);
                            @endphp
                            <a href="{{ route('course.mark.create', ['semester_id' => $semester_id, 'class_id' => $class_id, 'section_id' => $section_id, 'course_id' => $courseItem->id]) }}" 
                               class="px-3 py-1.5 rounded-xl text-xs whitespace-nowrap transition flex items-center gap-1.5 flex-shrink-0 {{ $isCur ? 'bg-emerald-600 text-white font-bold shadow-md ring-2 ring-emerald-400/50' : 'bg-white/10 text-slate-200 hover:bg-white/20' }}">
                                <i class="bi {{ $isCur ? 'bi-check-circle-fill text-emerald-200' : 'bi-journal-text text-slate-400' }} text-[10px]"></i>
                                <span>{{ $courseItem->course_name }}</span>
                                <span class="text-[10px] {{ $isCur ? 'text-emerald-100' : 'text-slate-400' }}">({{ $courseItem->course_type ?? 'Theory' }})</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Enterprise Veda-Style Unsaved Draft Recovery Banner --}}
        <div id="draftRecoveryBanner" class="hidden mb-4 p-3.5 sm:p-4 rounded-2xl bg-indigo-50/95 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-sm transition">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-lg flex-shrink-0 shadow-sm">
                    <i class="bi bi-cloud-arrow-down-fill"></i>
                </span>
                <div>
                    <div class="flex items-center gap-2">
                        <strong class="text-xs font-bold text-indigo-950 dark:text-indigo-100">Unsaved Local Draft Recovered</strong>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-200 dark:bg-indigo-800 text-indigo-800 dark:text-indigo-200">Offline Protected</span>
                    </div>
                    <p class="text-[11px] text-indigo-700/90 dark:text-indigo-300 mt-0.5">
                        Found <span id="draftCountText" class="font-bold">0</span> unsaved mark(s) preserved in your browser cache from <span id="draftTimeText" class="font-medium">earlier</span>.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 self-end sm:self-center">
                <button type="button" onclick="LocalDraftManager.applyDraft()" class="px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition shadow-xs flex items-center gap-1.5">
                    <i class="bi bi-arrow-repeat"></i> Restore Draft
                </button>
                <button type="button" onclick="LocalDraftManager.clearDraft()" class="px-3 py-1.5 rounded-xl bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 border border-slate-200 dark:border-slate-700 text-xs font-semibold transition">
                    Discard
                </button>
            </div>
        </div>

        {{-- Floating Sync HUD / Status Notification --}}
        <div id="syncStatusHud" class="hidden fixed bottom-6 right-6 z-50 max-w-sm rounded-2xl p-4 shadow-2xl border transition-all duration-300 flex items-center gap-3 backdrop-blur-md">
            <div id="syncStatusIcon" class="text-xl flex-shrink-0"></div>
            <div class="min-w-0 flex-1">
                <div id="syncStatusTitle" class="text-xs font-bold"></div>
                <div id="syncStatusDesc" class="text-[11px] opacity-90 mt-0.5 leading-snug"></div>
            </div>
            <button id="syncStatusActionBtn" type="button" class="hidden px-3 py-1.5 rounded-xl text-xs font-bold bg-white/20 hover:bg-white/30 transition shadow-xs"></button>
        </div>

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

                    {{-- Toolbar: Search, Autofill Tools & Live Stats --}}
                    <div class="p-3 md:p-5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/95 dark:bg-slate-800/95 backdrop-blur-md flex flex-col md:flex-row md:items-center justify-between gap-3 sticky top-14 z-20 shadow-xs">
                        <div class="flex items-center gap-3 flex-1 max-w-md">
                            <div class="relative w-full">
                                <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                <input type="text" id="studentQuickFilter" placeholder="Search student name or roll..." class="w-full text-xs pl-9 pr-8 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                                <button type="button" id="clearMasterStudentFilter" class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1" title="Clear search">
                                    <i class="bi bi-x-circle-fill text-xs"></i>
                                </button>
                            </div>
                            <span id="masterCountBadge" class="text-xs text-slate-500 font-medium whitespace-nowrap">
                                {{ count($sectionStudents) }} students
                            </span>
                        </div>

                        <div class="flex items-center gap-2 flex-wrap">
                            {{-- Mobile/Desktop View Mode Toggle --}}
                            <div class="inline-flex rounded-xl border border-slate-200 dark:border-slate-700 p-0.5 bg-slate-100 dark:bg-slate-800 text-xs font-semibold">
                                <button type="button" onclick="setMarksViewMode('card')" id="btnViewCardMaster" class="px-2.5 py-1 rounded-lg text-slate-600 dark:text-slate-300 hover:text-indigo-600 flex items-center gap-1 transition" title="Vertical Card View (Best for Mobile)">
                                    <i class="bi bi-grid"></i>
                                    <span class="text-[11px]">Cards</span>
                                </button>
                                <button type="button" onclick="setMarksViewMode('table')" id="btnViewTableMaster" class="px-2.5 py-1 rounded-lg text-slate-600 dark:text-slate-300 hover:text-indigo-600 flex items-center gap-1 transition" title="Spreadsheet Grid View">
                                    <i class="bi bi-table"></i>
                                    <span class="text-[11px]">Grid</span>
                                </button>
                            </div>

                            @if(!empty($canEditAllSubjects))
                                <a href="{{ route('results.class.report-cards', ['class_id' => $class_id, 'section_id' => $section_id, 'semester_id' => $semester_id, 'stream' => 1]) }}"
                                   target="_blank"
                                   data-no-loader
                                   class="px-3.5 py-1.5 rounded-xl border border-indigo-200 dark:border-indigo-800/80 bg-indigo-50/70 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 text-xs font-bold transition flex items-center gap-1.5 shadow-xs"
                                   title="Print individual official report cards for every student in the whole class on separate A4 pages">
                                    <i class="bi bi-printer-fill text-indigo-600 dark:text-indigo-400"></i>
                                    <span class="hidden sm:inline">Print Marksheets</span>
                                </a>
                            @endif
                            <button type="button" onclick="fillZeroForEmpty()" class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-semibold transition flex items-center gap-1.5 shadow-xs" title="Set any remaining empty marks as 0 (Absent)">
                                <i class="bi bi-magic text-indigo-500"></i>
                                <span>Fill 0 Absent</span>
                            </button>
                            <button type="button" onclick="calculateAllRows()" class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-semibold transition flex items-center gap-1.5 shadow-xs">
                                <i class="bi bi-arrow-clockwise text-emerald-500"></i>
                                <span class="hidden sm:inline">Recalculate</span>
                            </button>
                            <button type="submit" class="px-4 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/20 transition flex items-center gap-2">
                                <i class="bi bi-cloud-arrow-up-fill"></i> Save Marks
                            </button>
                        </div>
                    </div>

                    @if(count($classCourses) > 0 && count($sectionStudents) > 0)
                        {{-- No student match search placeholder --}}
                        <div id="masterNoMatchMessage" class="hidden p-8 text-center bg-white dark:bg-slate-900">
                            <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center text-xl mx-auto mb-2">
                                <i class="bi bi-search"></i>
                            </div>
                            <h4 class="text-xs font-bold text-slate-700 dark:text-slate-200">No matching student found</h4>
                            <p class="text-[11px] text-slate-400 mt-0.5">No students match "<span class="query-term font-semibold text-slate-600 dark:text-slate-300"></span>"</p>
                            <button type="button" onclick="document.getElementById('clearMasterStudentFilter').click()" class="mt-3 px-3 py-1 text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-semibold">
                                Clear search
                            </button>
                        </div>

                        <div class="overflow-x-auto relative marks-card-scroll-wrapper">
                            <table class="w-full text-left text-xs border-collapse marks-card-layout" id="masterMarksTable">
                                <thead>
                                    <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-100/70 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 font-bold">
                                        <th class="py-3 px-2 sm:px-4 min-w-[50px] sm:min-w-[70px] sticky left-0 bg-slate-100 dark:bg-slate-800 z-10 text-center">
                                            Roll
                                        </th>
                                        <th class="py-3 px-4 min-w-[200px] sticky left-[70px] bg-slate-100 dark:bg-slate-800 z-10">
                                            Student Details
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

                                            // Pre-compute row stats in PHP Blade for instant zero-lag rendering
                                            $totalObtained = 0;
                                            $totalMax = 0;
                                            $hasAnyMark = false;
                                            $anyFailed = false;
                                            foreach ($classCourses as $c) {
                                                $v = $marksMatrix[$stuId][$c->id] ?? null;
                                                $maxM = $courseMaxMarks[$c->id] ?? 100;
                                                $totalMax += $maxM;
                                                if ($v !== null && $v !== '' && is_numeric($v)) {
                                                    $hasAnyMark = true;
                                                    $num = (float)$v;
                                                    $totalObtained += $num;
                                                    if ($num < ($maxM * 0.35)) {
                                                        $anyFailed = true;
                                                    }
                                                }
                                            }
                                            $rowPct = ($hasAnyMark && $totalMax > 0) ? ($totalObtained / $totalMax) * 100 : null;
                                            $rowGrade = '—';
                                            $rowGpa = '—';
                                            $rowBadgeClass = 'bg-slate-100 dark:bg-slate-800 text-slate-400';
                                            if ($rowPct !== null) {
                                                if ($rowPct >= 90) { $rowGpa = '4.00'; $rowGrade = 'A+'; $rowBadgeClass = 'bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800'; }
                                                elseif ($rowPct >= 80) { $rowGpa = '3.60'; $rowGrade = 'A'; $rowBadgeClass = 'bg-teal-100 dark:bg-teal-950 text-teal-700 dark:text-teal-300 border border-teal-300 dark:border-teal-800'; }
                                                elseif ($rowPct >= 70) { $rowGpa = '3.20'; $rowGrade = 'B+'; $rowBadgeClass = 'bg-cyan-100 dark:bg-cyan-950 text-cyan-700 dark:text-cyan-300 border border-cyan-300 dark:border-cyan-800'; }
                                                elseif ($rowPct >= 60) { $rowGpa = '2.80'; $rowGrade = 'B'; $rowBadgeClass = 'bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 border border-blue-300 dark:border-blue-800'; }
                                                elseif ($rowPct >= 50) { $rowGpa = '2.40'; $rowGrade = 'C+'; $rowBadgeClass = 'bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300 border border-amber-300 dark:border-amber-800'; }
                                                elseif ($rowPct >= 40) { $rowGpa = '2.00'; $rowGrade = 'C'; $rowBadgeClass = 'bg-orange-100 dark:bg-orange-950 text-orange-700 dark:text-orange-300 border border-orange-300 dark:border-orange-800'; }
                                                elseif ($rowPct >= 35) { $rowGpa = '1.60'; $rowGrade = 'D'; $rowBadgeClass = 'bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300 border border-rose-300 dark:border-rose-800'; }
                                                else { $rowGpa = '0.00'; $rowGrade = 'NG'; $rowBadgeClass = 'bg-rose-200 dark:bg-rose-900 text-rose-800 dark:text-rose-200 border border-rose-400 font-extrabold'; }
                                                if ($anyFailed && $rowGrade !== 'NG') {
                                                    $rowGrade .= '*';
                                                }
                                            }
                                        @endphp
                                        <tr class="student-mark-row hover:bg-indigo-50/20 dark:hover:bg-slate-800/40 transition group" data-student-id="{{ $stuId }}" data-student-name="{{ strtolower($fullName) }}" data-roll-no="{{ $rollNo }}">
                                            {{-- Roll No (Desktop column) --}}
                                            <td class="cell-roll py-3 px-4 font-mono font-bold text-center text-indigo-600 dark:text-indigo-400 sticky left-0 bg-white dark:bg-slate-900 group-hover:bg-indigo-50/20 dark:group-hover:bg-slate-800/40 z-10">
                                                {{ $rollNo }}
                                            </td>

                                            {{-- Student Details (Header in Card View) --}}
                                            <td class="cell-student py-3 px-4 font-semibold text-slate-800 dark:text-slate-100 sticky left-[70px] bg-white dark:bg-slate-900 group-hover:bg-indigo-50/20 dark:group-hover:bg-slate-800/40 z-10">
                                                <div class="flex items-center justify-between w-full">
                                                    <div class="flex items-center gap-2.5">
                                                        <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-indigo-500 to-indigo-600 text-white font-bold flex items-center justify-center text-xs shadow-xs flex-shrink-0">
                                                            {{ strtoupper(substr($fullName, 0, 1)) }}
                                                        </div>
                                                        <div class="truncate">
                                                            <span class="block truncate font-bold text-sm text-slate-800 dark:text-slate-100">{{ $fullName }}</span>
                                                            <span class="text-[10px] text-slate-400 font-normal">ID: #{{ $stuId }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="mobile-only-roll md:hidden flex items-center">
                                                        <span class="px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 font-mono font-bold text-xs">
                                                            Roll #{{ $rollNo }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>

                                            {{-- Course Marks Inputs --}}
                                            @foreach($classCourses as $course)
                                                @php
                                                    $val = $marksMatrix[$stuId][$course->id] ?? null;
                                                    $maxM = $courseMaxMarks[$course->id] ?? 100;
                                                @endphp
                                                <td class="cell-mark py-2.5 px-3 border-l border-slate-100 dark:border-slate-800/80 text-center">
                                                    <div class="mobile-field-label md:hidden text-left">
                                                        <span class="font-bold text-slate-800 dark:text-slate-100 text-xs">{{ $course->course_name }}</span>
                                                        <span class="text-[10px] text-slate-400 font-medium">Max: {{ $maxM }}</span>
                                                    </div>
                                                    <div>
                                                        <input type="number" 
                                                               step="0.01" 
                                                               min="0" 
                                                               max="{{ $maxM }}" 
                                                               inputmode="decimal"
                                                               name="student_course_mark[{{ $stuId }}][{{ $course->id }}]" 
                                                               value="{{ $val !== null ? $val : '' }}"
                                                               placeholder="—"
                                                               data-student-id="{{ $stuId }}"
                                                               data-course-id="{{ $course->id }}"
                                                               data-max-marks="{{ $maxM }}"
                                                               class="mark-input w-full max-w-[90px] text-center font-mono text-xs font-bold py-1.5 px-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                                                    </div>
                                                </td>
                                            @endforeach

                                            {{-- Dynamic Row Calculations --}}
                                            <td class="cell-calc cell-calc-total py-2.5 px-3 text-center font-mono font-bold text-slate-700 dark:text-slate-200 bg-indigo-50/20 dark:bg-indigo-950/10 border-l border-indigo-100/50 dark:border-indigo-900/30">
                                                <span class="md:hidden text-[10px] text-slate-500 font-semibold uppercase mb-0.5">Total</span>
                                                <span class="row-total-marks">{{ $hasAnyMark ? number_format($totalObtained, 1) . ' / ' . $totalMax : '—' }}</span>
                                            </td>
                                            <td class="cell-calc cell-calc-percent py-2.5 px-3 text-center font-mono font-semibold text-slate-700 dark:text-slate-200 bg-indigo-50/20 dark:bg-indigo-950/10">
                                                <span class="md:hidden text-[10px] text-slate-500 font-semibold uppercase mb-0.5">%</span>
                                                <span class="row-percentage">{{ $rowPct !== null ? number_format($rowPct, 1) . '%' : '—' }}</span>
                                            </td>
                                            <td class="cell-calc cell-calc-gpa py-2.5 px-3 text-center font-mono font-bold bg-indigo-50/20 dark:bg-indigo-950/10">
                                                <span class="md:hidden text-[10px] text-slate-500 font-semibold uppercase mb-0.5">GPA</span>
                                                <span class="row-gpa {{ $rowGrade === 'NG' ? 'text-rose-600 font-bold' : 'text-slate-800 dark:text-slate-100' }}">{{ $rowGpa }}</span>
                                            </td>
                                            <td class="cell-calc cell-calc-grade py-2.5 px-3 text-center bg-indigo-50/20 dark:bg-indigo-950/10">
                                                <span class="md:hidden text-[10px] text-slate-500 font-semibold uppercase mb-0.5">Grade</span>
                                                <span class="row-grade-badge px-2 py-0.5 rounded-md text-[10px] font-bold {{ $rowBadgeClass }}">
                                                    {{ $rowGrade }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Bottom Sticky Bar with Action Buttons --}}
                        <div class="p-3 md:p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/95 dark:bg-slate-800/95 backdrop-blur-md sticky bottom-0 z-30 shadow-lg flex flex-col sm:flex-row items-center justify-between gap-3">
                            <div class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                <span class="hidden sm:inline">Tip: Use <kbd class="px-1.5 py-0.5 rounded bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 font-mono text-[10px]">Tab</kbd> or <kbd class="px-1.5 py-0.5 rounded bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 font-mono text-[10px]">Arrow Keys</kbd> to quickly navigate cells. Press <kbd class="px-1.5 py-0.5 rounded bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 font-mono text-[10px]">Ctrl+S</kbd> / <kbd class="px-1.5 py-0.5 rounded bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 font-mono text-[10px]">⌘+S</kbd> to save.</span>
                                <span class="sm:hidden">Press <kbd class="px-1 py-0.5 rounded bg-white dark:bg-slate-700 border text-[10px]">Ctrl+S</kbd> to save.</span>
                            </div>

                            <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                                <a href="{{ route('course.mark.index') }}" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 text-xs font-semibold transition">
                                    Cancel
                                </a>
                                <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-xs font-bold shadow-lg shadow-indigo-600/30 transition flex items-center justify-center gap-2">
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
                <form action="{{ route('course.mark.store') }}" method="POST" id="singleMarksForm">
                    @csrf
                    <input type="hidden" name="session_id" value="{{ $current_school_session_id }}">
                    <input type="hidden" name="semester_id" value="{{ $semester_id }}">
                    <input type="hidden" name="class_id" value="{{ $class_id }}">
                    <input type="hidden" name="section_id" value="{{ $section_id }}">
                    <input type="hidden" name="course_id" value="{{ $course_id }}">

                    {{-- Header Toolbar: Search & Action Tools --}}
                    <div class="p-3 md:p-5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/95 dark:bg-slate-800/95 backdrop-blur-md flex flex-col md:flex-row md:items-center justify-between gap-3 sticky top-14 z-20 shadow-xs">
                        <div class="flex items-center gap-3 flex-1 max-w-md">
                            <div class="relative w-full">
                                <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                <input type="text" id="singleStudentQuickFilter" placeholder="Search student name or roll..." class="w-full text-xs pl-9 pr-8 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                                <button type="button" id="clearSingleStudentFilter" class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1" title="Clear search">
                                    <i class="bi bi-x-circle-fill text-xs"></i>
                                </button>
                            </div>
                            <span id="singleCountBadge" class="text-xs text-slate-500 font-medium whitespace-nowrap">
                                {{ count($sectionStudents) }} students
                            </span>
                        </div>

                        <div class="flex items-center gap-2 flex-wrap">
                            {{-- Mobile/Desktop View Mode Toggle --}}
                            <div class="inline-flex rounded-xl border border-slate-200 dark:border-slate-700 p-0.5 bg-slate-100 dark:bg-slate-800 text-xs font-semibold">
                                <button type="button" onclick="setMarksViewMode('card')" id="btnViewCardSingle" class="px-2.5 py-1 rounded-lg text-slate-600 dark:text-slate-300 hover:text-indigo-600 flex items-center gap-1 transition" title="Vertical Card View (Best for Mobile)">
                                    <i class="bi bi-grid"></i>
                                    <span class="text-[11px]">Cards</span>
                                </button>
                                <button type="button" onclick="setMarksViewMode('table')" id="btnViewTableSingle" class="px-2.5 py-1 rounded-lg text-slate-600 dark:text-slate-300 hover:text-indigo-600 flex items-center gap-1 transition" title="Spreadsheet Grid View">
                                    <i class="bi bi-table"></i>
                                    <span class="text-[11px]">Grid</span>
                                </button>
                            </div>

                            <button type="button" onclick="fillZeroForSingleEmpty()" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold transition flex items-center gap-1.5" title="Fill 0 for all currently empty cells (Absent students)">
                                <i class="bi bi-slash-circle text-amber-500"></i>
                                <span>Fill 0 Absent</span>
                            </button>

                            @if(!empty($canEditAllSubjects))
                                <a href="{{ route('results.class.report-cards', ['class_id' => $class_id, 'section_id' => $section_id, 'semester_id' => $semester_id, 'stream' => 1]) }}"
                                   target="_blank"
                                   data-no-loader
                                   class="px-3.5 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 border border-emerald-200 dark:border-emerald-800 text-xs font-bold transition flex items-center gap-1.5 shadow-xs">
                                    <i class="bi bi-printer-fill text-emerald-600"></i>
                                    <span class="hidden sm:inline">Print Marksheets</span>
                                </a>
                            @endif

                            @if (!$final_marks_submitted && count($exams) > 0 && ($isWindowOpen || !empty($isAdmin)))
                                <a href="{{ route('course.final.mark.submit.show', ['class_id' => $class_id, 'class_name' => $selectedClass->class_name ?? '', 'section_id' => $section_id, 'section_name' => $selectedSection->section_name ?? '', 'course_id' => $course_id, 'course_name' => $selectedCourse->course_name ?? '', 'semester_id' => $semester_id]) }}" 
                                   class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5" 
                                   onclick="return confirm('Are you sure you want to proceed to submit final marks for this course?')">
                                    <i class="bi bi-check2-all"></i> Submit Final
                                </a>
                            @endif
                        </div>
                    </div>

                    @if(count($exams) > 0 && count($sectionStudents) > 0)
                        {{-- No student match search placeholder --}}
                        <div id="singleNoMatchMessage" class="hidden p-8 text-center bg-white dark:bg-slate-900">
                            <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center text-xl mx-auto mb-2">
                                <i class="bi bi-search"></i>
                            </div>
                            <h4 class="text-xs font-bold text-slate-700 dark:text-slate-200">No matching student found</h4>
                            <p class="text-[11px] text-slate-400 mt-0.5">No students match "<span class="query-term font-semibold text-slate-600 dark:text-slate-300"></span>"</p>
                            <button type="button" onclick="document.getElementById('clearSingleStudentFilter').click()" class="mt-3 px-3 py-1 text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-semibold">
                                Clear search
                            </button>
                        </div>

                        {{-- Excel-like Spreadsheet Grid / Mobile Card View for Single Subject --}}
                        <div class="overflow-x-auto relative marks-card-scroll-wrapper">
                            <table class="w-full text-left text-xs border-collapse marks-card-layout" id="singleMarksTable">
                                <thead>
                                    <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 font-bold uppercase tracking-wider text-[11px]">
                                        <th class="py-3 px-4 w-16 text-center sticky left-0 bg-slate-50 dark:bg-slate-800 z-20 shadow-sm">
                                            Roll
                                        </th>
                                        <th class="py-3 px-4 min-w-[200px] sticky left-[64px] bg-slate-50 dark:bg-slate-800 z-20 shadow-sm">
                                            Student Details
                                        </th>
                                        @foreach ($exams as $exam)
                                            @php $examMaxMarks = $exam_rules_by_exam_id[$exam->id] ?? 100; @endphp
                                            <th class="py-3 px-3 min-w-[130px] text-center border-l border-slate-200 dark:border-slate-700">
                                                <a href="{{ route('exam.rule.show', ['exam_id' => $exam->id]) }}" class="hover:text-indigo-600 block font-bold truncate" title="View exam rules">
                                                    {{ $exam->exam_name }}
                                                </a>
                                                <span class="inline-block mt-0.5 text-[10px] font-mono px-2 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 font-medium">
                                                    Max: {{ $examMaxMarks }} pts
                                                </span>
                                            </th>
                                        @endforeach
                                        <th class="py-3 px-3 min-w-[100px] text-center bg-indigo-50/60 dark:bg-indigo-950/30 text-indigo-950 dark:text-indigo-200 font-bold border-l border-indigo-100 dark:border-indigo-900/50">
                                            Total
                                        </th>
                                        <th class="py-3 px-3 min-w-[80px] text-center bg-indigo-50/60 dark:bg-indigo-950/30 text-indigo-950 dark:text-indigo-200 font-bold">
                                            %
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

                                            // Pre-compute row stats in PHP Blade for Single Subject mode
                                            $totalObtained = 0;
                                            $totalMax = 0;
                                            $hasAnyMark = false;
                                            $anyFailed = false;
                                            foreach ($exams as $exam) {
                                                $val = $marksByStudentAndExam[$stuId][$exam->id] ?? null;
                                                $maxM = $exam_rules_by_exam_id[$exam->id] ?? 100;
                                                $totalMax += $maxM;
                                                if ($val !== null && $val !== '' && is_numeric($val)) {
                                                    $hasAnyMark = true;
                                                    $num = (float)$val;
                                                    $totalObtained += $num;
                                                    if ($num < ($maxM * 0.35)) {
                                                        $anyFailed = true;
                                                    }
                                                }
                                            }
                                            $rowPct = ($hasAnyMark && $totalMax > 0) ? ($totalObtained / $totalMax) * 100 : null;
                                            $rowGrade = '—';
                                            $rowBadgeClass = 'bg-slate-100 dark:bg-slate-800 text-slate-400';
                                            if ($rowPct !== null) {
                                                if ($rowPct >= 90) { $rowGrade = 'A+'; $rowBadgeClass = 'bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800'; }
                                                elseif ($rowPct >= 80) { $rowGrade = 'A'; $rowBadgeClass = 'bg-teal-100 dark:bg-teal-950 text-teal-700 dark:text-teal-300 border border-teal-300 dark:border-teal-800'; }
                                                elseif ($rowPct >= 70) { $rowGrade = 'B+'; $rowBadgeClass = 'bg-cyan-100 dark:bg-cyan-950 text-cyan-700 dark:text-cyan-300 border border-cyan-300 dark:border-cyan-800'; }
                                                elseif ($rowPct >= 60) { $rowGrade = 'B'; $rowBadgeClass = 'bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 border border-blue-300 dark:border-blue-800'; }
                                                elseif ($rowPct >= 50) { $rowGrade = 'C+'; $rowBadgeClass = 'bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300 border border-amber-300 dark:border-amber-800'; }
                                                elseif ($rowPct >= 40) { $rowGrade = 'C'; $rowBadgeClass = 'bg-orange-100 dark:bg-orange-950 text-orange-700 dark:text-orange-300 border border-orange-300 dark:border-orange-800'; }
                                                elseif ($rowPct >= 35) { $rowGrade = 'D'; $rowBadgeClass = 'bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300 border border-rose-300 dark:border-rose-800'; }
                                                else { $rowGrade = 'NG'; $rowBadgeClass = 'bg-rose-200 dark:bg-rose-900 text-rose-800 dark:text-rose-200 border border-rose-400 font-extrabold'; }
                                                if ($anyFailed && $rowGrade !== 'NG') {
                                                    $rowGrade .= '*';
                                                }
                                            }
                                        @endphp
                                        <tr class="student-mark-row single-student-row hover:bg-indigo-50/20 dark:hover:bg-slate-800/40 transition group" data-student-id="{{ $stuId }}" data-student-name="{{ strtolower($fullName) }}" data-roll-no="{{ $rollNo }}">
                                            {{-- Roll No (Desktop column) --}}
                                            <td class="cell-roll py-3 px-4 font-mono font-bold text-center text-indigo-600 dark:text-indigo-400 sticky left-0 bg-white dark:bg-slate-900 group-hover:bg-indigo-50/20 dark:group-hover:bg-slate-800/40 z-10">
                                                {{ $rollNo }}
                                            </td>

                                            {{-- Student Details (Header in Card View) --}}
                                            <td class="cell-student py-3 px-4 font-semibold text-slate-800 dark:text-slate-100 sticky left-[64px] bg-white dark:bg-slate-900 group-hover:bg-indigo-50/20 dark:group-hover:bg-slate-800/40 z-10">
                                                <div class="flex items-center justify-between w-full">
                                                    <div class="flex items-center gap-2.5">
                                                        <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-indigo-500 to-indigo-600 text-white font-bold flex items-center justify-center text-xs shadow-xs flex-shrink-0">
                                                            {{ strtoupper(substr($fullName, 0, 1)) }}
                                                        </div>
                                                        <div class="truncate">
                                                            <span class="block truncate font-bold text-sm text-slate-800 dark:text-slate-100">{{ $fullName }}</span>
                                                            <span class="text-[10px] text-slate-400 font-normal">ID: #{{ $stuId }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="mobile-only-roll md:hidden flex items-center">
                                                        <span class="px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 font-mono font-bold text-xs">
                                                            Roll #{{ $rollNo }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>

                                            {{-- Exam Marks Inputs --}}
                                            @foreach ($exams as $exam)
                                                @php
                                                    $val = $marksByStudentAndExam[$stuId][$exam->id] ?? null;
                                                    $maxM = $exam_rules_by_exam_id[$exam->id] ?? 100;
                                                @endphp
                                                <td class="cell-mark py-2.5 px-3 border-l border-slate-100 dark:border-slate-800/80 text-center">
                                                    <div class="mobile-field-label md:hidden text-left">
                                                        <span class="font-bold text-slate-800 dark:text-slate-100 text-xs">{{ $exam->exam_name }}</span>
                                                        <span class="text-[10px] text-slate-400 font-medium">Max: {{ $maxM }}</span>
                                                    </div>
                                                    <div>
                                                        <input type="number" 
                                                               step="0.01" 
                                                               min="0" 
                                                               max="{{ $maxM }}" 
                                                               inputmode="decimal"
                                                               name="student_mark[{{ $stuId }}][{{ $exam->id }}]" 
                                                               value="{{ $val !== null ? $val : '' }}"
                                                               placeholder="—"
                                                               data-student-id="{{ $stuId }}"
                                                               data-exam-id="{{ $exam->id }}"
                                                               data-max-marks="{{ $maxM }}"
                                                               class="mark-input single-mark-input w-full max-w-[100px] text-center font-mono text-xs font-bold py-1.5 px-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                                                    </div>
                                                </td>
                                            @endforeach

                                            {{-- Dynamic Row Calculations --}}
                                            <td class="cell-calc cell-calc-total py-2.5 px-3 text-center font-mono font-bold text-slate-700 dark:text-slate-200 bg-indigo-50/20 dark:bg-indigo-950/10 border-l border-indigo-100/50 dark:border-indigo-900/30">
                                                <span class="md:hidden text-[10px] text-slate-500 font-semibold uppercase mb-0.5">Total</span>
                                                <span class="row-total-marks">{{ $hasAnyMark ? number_format($totalObtained, 1) . ' / ' . $totalMax : '—' }}</span>
                                            </td>
                                            <td class="cell-calc cell-calc-percent py-2.5 px-3 text-center font-mono font-semibold text-slate-700 dark:text-slate-200 bg-indigo-50/20 dark:bg-indigo-950/10">
                                                <span class="md:hidden text-[10px] text-slate-500 font-semibold uppercase mb-0.5">%</span>
                                                <span class="row-percentage">{{ $rowPct !== null ? number_format($rowPct, 1) . '%' : '—' }}</span>
                                            </td>
                                            <td class="cell-calc cell-calc-grade py-2.5 px-3 text-center bg-indigo-50/20 dark:bg-indigo-950/10">
                                                <span class="md:hidden text-[10px] text-slate-500 font-semibold uppercase mb-0.5">Grade</span>
                                                <span class="row-grade-badge px-2 py-0.5 rounded-md text-[10px] font-bold {{ $rowBadgeClass }}">
                                                    {{ $rowGrade }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Bottom Sticky Bar with Action Buttons --}}
                        <div class="p-3 md:p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/95 dark:bg-slate-800/95 backdrop-blur-md sticky bottom-0 z-30 shadow-lg flex flex-col sm:flex-row items-center justify-between gap-3">
                            <div class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                <span class="hidden sm:inline">Tip: Use <kbd class="px-1.5 py-0.5 rounded bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 font-mono text-[10px]">Tab</kbd>, <kbd class="px-1.5 py-0.5 rounded bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 font-mono text-[10px]">Enter</kbd> or <kbd class="px-1.5 py-0.5 rounded bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 font-mono text-[10px]">Arrow Keys</kbd> to navigate cells. Press <kbd class="px-1.5 py-0.5 rounded bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 font-mono text-[10px]">Ctrl+S</kbd> / <kbd class="px-1.5 py-0.5 rounded bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 font-mono text-[10px]">⌘+S</kbd> to save.</span>
                                <span class="sm:hidden">Press <kbd class="px-1 py-0.5 rounded bg-white dark:bg-slate-700 border text-[10px]">Ctrl+S</kbd> to save.</span>
                            </div>

                            <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                                <a href="{{ route('course.mark.index') }}" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 text-xs font-semibold transition">
                                    Cancel
                                </a>
                                @if(!$final_marks_submitted)
                                    <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-xs font-bold shadow-lg shadow-indigo-600/30 transition flex items-center justify-center gap-2">
                                        <i class="bi bi-check-circle-fill text-sm"></i>
                                        <span>Save Subject Marks</span>
                                    </button>
                                @else
                                    <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 dark:bg-emerald-950/40 px-3 py-1.5 rounded-xl border border-emerald-200 dark:border-emerald-900">
                                        <i class="bi bi-check-circle me-1"></i> Final Marks Already Submitted
                                    </span>
                                @endif
                            </div>
                        </div>
                    @elseif(count($exams) === 0)
                        <div class="py-16 text-center">
                            <span class="w-16 h-16 rounded-full bg-amber-50 dark:bg-amber-950/40 text-amber-500 flex items-center justify-center text-3xl mx-auto mb-3">
                                <i class="bi bi-journal-x"></i>
                            </span>
                            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">No Exams Configured</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                                No examination rules found for this semester. Please configure exams in Examination Settings before entering marks.
                            </p>
                        </div>
                    @else
                        <div class="py-16 text-center">
                            <span class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center text-3xl mx-auto mb-3">
                                <i class="bi bi-people"></i>
                            </span>
                            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">No Students Enrolled</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                                No active student enrollments found for {{ $selectedClass->class_name ?? 'this class' }} in the selected section.
                            </p>
                        </div>
                    @endif
                </form>
            </div>
        @endif
    @endif

    {{-- Floating Quick Save for Mobile --}}
    <div id="mobileFloatingSave" class="sm:hidden fixed bottom-20 right-4 z-40 pointer-events-none">
        <button type="button" id="mobileFloatingSaveBtn" onclick="submitActiveMarksForm()" class="pointer-events-auto h-11 px-3.5 rounded-2xl bg-indigo-600 active:bg-indigo-700 text-white font-bold text-xs shadow-2xl flex items-center gap-1.5 border border-white/20 transition-all opacity-90 shadow-indigo-600/30">
            <i class="bi bi-cloud-arrow-up-fill text-sm"></i>
            <span>Save Marks</span>
        </button>
    </div>

    @include('layouts.footer')
</div>

{{-- Scripts for dynamic Section/Course loading, keyboard shortcuts, and live GPA calculations --}}
<script>
const IS_ADMIN = {{ !empty($isAdmin) ? 'true' : 'false' }};
const IS_CLASS_TEACHER = {{ !empty($isClassTeacher) ? 'true' : 'false' }};
const CAN_EDIT_ALL = {{ !empty($canEditAllSubjects) ? 'true' : 'false' }};
const PRESELECTED_SECTION = "{{ $section_id }}";
const PRESELECTED_COURSE = "{{ $course_id }}";

// In-memory instant client cache for sections and courses
const classDataCache = window._marksClassCache || {};
window._marksClassCache = classDataCache;

@if(!empty($class_id) && isset($classSections) && isset($classCourses))
classDataCache["{{ $class_id }}"] = {
    sections: @json($classSections->map(fn($s) => ['id' => $s->id, 'section_name' => $s->section_name, 'room_no' => $s->room_no ?? 'N/A'])),
    courses: @json($classCourses->map(fn($c) => ['id' => $c->id, 'course_name' => $c->course_name, 'course_type' => $c->course_type ?? 'Theory'])),
    is_class_teacher: {{ !empty($isClassTeacher) ? 'true' : 'false' }}
};
@endif

function handleClassChange(classId) {
    if (!classId) {
        populateSectionsAndCourses([], [], false);
        return;
    }
    const semSelect = document.getElementById('filter_semester_id');
    const semId = semSelect ? semSelect.value : '';
    const cacheKey = classId + (semId ? '_' + semId : '');

    // Instant cache hit: 0ms delay!
    if (classDataCache[cacheKey] || classDataCache[classId]) {
        const cached = classDataCache[cacheKey] || classDataCache[classId];
        populateSectionsAndCourses(cached.sections, cached.courses, cached.is_class_teacher, '0', (IS_ADMIN || cached.is_class_teacher) ? 'all' : '');
        return;
    }
    loadSectionsAndCourses(classId, '0', (IS_ADMIN || IS_CLASS_TEACHER) ? 'all' : '');
}

function populateSectionsAndCourses(sections, courses, isClassTeacher, preselectedSection = '0', preselectedCourse = '') {
    const selectSection = document.getElementById('filter_section_id');
    const selectCourse = document.getElementById('filter_course_id');
    if (!selectSection || !selectCourse) return;

    const hasAllPrivilege = IS_ADMIN || !!isClassTeacher || CAN_EDIT_ALL;

    // Populate Sections
    let secHtml = '<option value="0">' + (hasAllPrivilege ? 'All Sections (Entire Class)' : 'All Assigned Sections') + '</option>';
    if (sections && sections.length > 0) {
        sections.forEach(sec => {
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
    
    if (courses && courses.length > 0) {
        if (hasAllPrivilege) {
            crsHtml += `<optgroup label="Individual Subjects">`;
        }
        courses.forEach((crs, idx) => {
            const shouldSelect = (String(crs.id) === String(preselectedCourse)) || (!hasAllPrivilege && (courses.length === 1 || (!preselectedCourse && idx === 0)));
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
}

function loadSectionsAndCourses(classId, preselectedSection, preselectedCourse) {
    const secSpinner = document.getElementById('sectionLoadingSpinner');
    const crsSpinner = document.getElementById('courseLoadingSpinner');
    if (secSpinner) secSpinner.classList.remove('hidden');
    if (crsSpinner) crsSpinner.classList.remove('hidden');

    const semSelect = document.getElementById('filter_semester_id');
    const semId = semSelect ? semSelect.value : '';
    const url = '{{ route("get.sections.courses.by.classId") }}?class_id=' + encodeURIComponent(classId) + (semId ? '&semester_id=' + encodeURIComponent(semId) : '');

    fetch(url)
        .then(res => res.json())
        .then(data => {
            const cacheKey = classId + (semId ? '_' + semId : '');
            const obj = {
                sections: data.sections || [],
                courses: data.courses || [],
                is_class_teacher: !!data.is_class_teacher
            };
            classDataCache[cacheKey] = obj;
            classDataCache[classId] = obj;
            populateSectionsAndCourses(data.sections || [], data.courses || [], data.is_class_teacher, preselectedSection, preselectedCourse);
        })
        .catch(err => {
            console.error('Error fetching class sections and courses:', err);
        })
        .finally(() => {
            if (secSpinner) secSpinner.classList.add('hidden');
            if (crsSpinner) crsSpinner.classList.add('hidden');
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

// 60fps Debounced row update to prevent typing stutter
const rowUpdateTimers = new WeakMap();
function debouncedUpdateStudentRowStats(row) {
    if (rowUpdateTimers.has(row)) {
        cancelAnimationFrame(rowUpdateTimers.get(row));
    }
    const rafId = requestAnimationFrame(() => {
        updateStudentRowStats(row);
        markRowUnsaved(row);
        rowUpdateTimers.delete(row);
    });
    rowUpdateTimers.set(row, rafId);
}

// --- ENTERPRISE VEDA-GRADE LOW-INTERNET & OFFLINE DRAFT ENGINE ---
const CURRENT_DRAFT_KEY = 'marks_draft_v2_s{{ $current_school_session_id }}_sem{{ $semester_id }}_c{{ $class_id }}_sec{{ $section_id }}_crs{{ $course_id }}';

// 1. Live Network Quality & Heartbeat Monitor
const NetworkMonitor = {
    isOnline: navigator.onLine,
    isSlow: false,
    latency: 0,
    async checkConnection() {
        if (!navigator.onLine) {
            this.setOffline();
            return;
        }
        const start = performance.now();
        try {
            await fetch('/favicon.ico?_v=' + Date.now(), { method: 'HEAD', cache: 'no-store' });
            this.latency = Math.round(performance.now() - start);
            if (this.latency > 1200) {
                this.setSlow();
            } else {
                this.setOnline();
            }
        } catch (e) {
            this.setSlow();
        }
    },
    setOnline() {
        this.isOnline = true;
        this.isSlow = false;
        this.updatePills('online', 'Online (Fast)', 'bg-emerald-500', 'text-emerald-700 dark:text-emerald-300');
    },
    setSlow() {
        this.isOnline = true;
        this.isSlow = true;
        this.updatePills('slow', 'Slow Internet (' + this.latency + 'ms)', 'bg-amber-500 animate-pulse', 'text-amber-700 dark:text-amber-300');
    },
    setOffline() {
        this.isOnline = false;
        this.updatePills('offline', 'Offline (Draft Mode)', 'bg-rose-500', 'text-rose-700 dark:text-rose-300');
    },
    updatePills(mode, label, dotBg, textCls) {
        document.querySelectorAll('.network-status-pill').forEach(pill => {
            const dot = pill.querySelector('.status-dot');
            const txt = pill.querySelector('.status-text');
            if (dot) dot.className = 'status-dot w-2 h-2 rounded-full ' + dotBg;
            if (txt) txt.textContent = label;
        });
    }
};

// 2. High-speed Local Draft Engine (Preserves every keystroke in browser storage)
const LocalDraftManager = {
    timer: null,
    saveDraft() {
        clearTimeout(this.timer);
        this.timer = setTimeout(() => {
            const inputs = document.querySelectorAll('.mark-input');
            const data = {};
            let count = 0;
            inputs.forEach(inp => {
                if (inp.name && inp.value !== '') {
                    data[inp.name] = inp.value;
                    count++;
                }
            });
            const draft = {
                updatedAt: new Date().toISOString(),
                count: count,
                marks: data
            };
            try {
                localStorage.setItem(CURRENT_DRAFT_KEY, JSON.stringify(draft));
                this.updateDraftStatus(count);
            } catch (err) {
                console.warn('LocalStorage save failed:', err);
            }
        }, 150);
    },
    getDraft() {
        try {
            const raw = localStorage.getItem(CURRENT_DRAFT_KEY);
            return raw ? JSON.parse(raw) : null;
        } catch (e) {
            return null;
        }
    },
    clearDraft() {
        try {
            localStorage.removeItem(CURRENT_DRAFT_KEY);
            this.updateDraftStatus(0);
            const banner = document.getElementById('draftRecoveryBanner');
            if (banner) banner.classList.add('hidden');
        } catch (e) {}
    },
    checkExistingDraft() {
        const draft = this.getDraft();
        if (!draft || !draft.marks || Object.keys(draft.marks).length === 0) return;

        let diffCount = 0;
        Object.keys(draft.marks).forEach(name => {
            const inp = document.querySelector(`input[name="${name}"]`);
            if (inp && inp.value !== draft.marks[name]) {
                diffCount++;
            }
        });

        if (diffCount > 0) {
            const banner = document.getElementById('draftRecoveryBanner');
            const countSpan = document.getElementById('draftCountText');
            const timeSpan = document.getElementById('draftTimeText');
            if (banner && countSpan) {
                countSpan.textContent = `${diffCount} unsaved mark(s)`;
                if (timeSpan && draft.updatedAt) {
                    const d = new Date(draft.updatedAt);
                    timeSpan.textContent = d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                }
                banner.classList.remove('hidden');
            }
        }
    },
    applyDraft() {
        const draft = this.getDraft();
        if (!draft || !draft.marks) return;
        let applied = 0;
        Object.keys(draft.marks).forEach(name => {
            const inp = document.querySelector(`input[name="${name}"]`);
            if (inp) {
                inp.value = draft.marks[name];
                inp.classList.add('bg-amber-50', 'dark:bg-amber-950/40');
                applied++;
            }
        });
        calculateAllRows();
        const banner = document.getElementById('draftRecoveryBanner');
        if (banner) banner.classList.add('hidden');
        showFloatingHud('Draft Restored', `✓ Restored ${applied} marks from local storage!`, 'success');
    },
    updateDraftStatus(count) {
        document.querySelectorAll('.draft-status-text').forEach(el => {
            if (count > 0) {
                el.textContent = `💾 Draft: ${count} cached`;
                el.classList.add('text-indigo-600', 'dark:text-indigo-400', 'font-bold');
            } else {
                el.textContent = `💾 Auto-Draft: Active`;
                el.classList.remove('text-indigo-600', 'dark:text-indigo-400', 'font-bold');
            }
        });
    }
};

// 3. Floating HUD / Toast Notification Engine
let hudTimeout = null;
function showFloatingHud(title, desc, type = 'info', actionText = null, actionCallback = null) {
    const hud = document.getElementById('syncStatusHud');
    if (!hud) return;

    clearTimeout(hudTimeout);

    const iconEl = document.getElementById('syncStatusIcon');
    const titleEl = document.getElementById('syncStatusTitle');
    const descEl = document.getElementById('syncStatusDesc');
    const actBtn = document.getElementById('syncStatusActionBtn');

    titleEl.textContent = title;
    descEl.textContent = desc;

    hud.className = 'fixed bottom-6 right-6 z-50 max-w-sm rounded-2xl p-4 shadow-2xl border transition-all duration-300 flex items-center gap-3 backdrop-blur-md ';

    if (type === 'syncing') {
        hud.classList.add('bg-slate-900/95', 'text-white', 'border-slate-700');
        iconEl.innerHTML = '<span class="inline-block animate-spin text-indigo-400"><i class="bi bi-arrow-repeat"></i></span>';
    } else if (type === 'success') {
        hud.classList.add('bg-emerald-950/95', 'text-emerald-100', 'border-emerald-700/60');
        iconEl.innerHTML = '<i class="bi bi-check-circle-fill text-emerald-400"></i>';
        hudTimeout = setTimeout(() => hud.classList.add('hidden'), 4000);
    } else if (type === 'error') {
        hud.classList.add('bg-rose-950/95', 'text-rose-100', 'border-rose-700/60');
        iconEl.innerHTML = '<i class="bi bi-exclamation-octagon-fill text-rose-400"></i>';
    } else if (type === 'warning') {
        hud.classList.add('bg-amber-950/95', 'text-amber-100', 'border-amber-700/60');
        iconEl.innerHTML = '<i class="bi bi-exclamation-triangle-fill text-amber-400"></i>';
        hudTimeout = setTimeout(() => hud.classList.add('hidden'), 4000);
    }

    if (actionText && typeof actionCallback === 'function') {
        actBtn.textContent = actionText;
        actBtn.classList.remove('hidden');
        actBtn.onclick = () => {
            actionCallback();
            hud.classList.add('hidden');
        };
    } else {
        actBtn.classList.add('hidden');
        actBtn.onclick = null;
    }

    hud.classList.remove('hidden');
}

// 4. Asynchronous High-Speed Sync with Low-Internet Auto-Retry
async function saveMarksWithLowInternetSync(form, retryAttempt = 0) {
    if (!form) return;

    // Client-side bounds check to prevent server reject
    const inputs = form.querySelectorAll('.mark-input');
    let hasInvalid = false;
    inputs.forEach(inp => {
        const max = parseFloat(inp.dataset.maxMarks) || 100;
        const val = parseFloat(inp.value);
        if (!isNaN(val) && val > max) {
            inp.classList.add('ring-2', 'ring-rose-500', 'border-rose-500', 'bg-rose-50');
            hasInvalid = true;
        } else {
            inp.classList.remove('ring-2', 'ring-rose-500', 'border-rose-500', 'bg-rose-50');
        }
    });

    if (hasInvalid) {
        showFloatingHud('Validation Error', 'One or more entries exceed maximum allowed marks. Please correct highlighted cells.', 'warning');
        return;
    }

    const saveBtns = form.querySelectorAll('button[type="submit"]');
    saveBtns.forEach(b => {
        b.disabled = true;
        b.dataset.origHtml = b.innerHTML;
        b.innerHTML = '<span class="inline-block animate-spin mr-1.5"><i class="bi bi-arrow-repeat"></i></span> Syncing...';
    });

    const isRetry = retryAttempt > 0;
    showFloatingHud(
        isRetry ? 'Reconnecting to Database...' : 'Syncing Marks to Database...',
        isRetry ? `Slow internet detected. Retrying sync (attempt ${retryAttempt + 1} of 3)...` : 'Transmitting marks securely...',
        'syncing'
    );

    try {
        const formData = new FormData(form);
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 14000); // 14s timeout for low bandwidth

        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            signal: controller.signal
        });
        clearTimeout(timeoutId);

        if (!response.ok) {
            const errData = await response.json().catch(() => ({}));
            throw new Error(errData.message || `Server responded with HTTP ${response.status}`);
        }

        const data = await response.json();

        // 1. Clear synchronized entries from local storage draft
        LocalDraftManager.clearDraft();

        // 2. Visually confirm rows saved with smooth green pulse
        document.querySelectorAll('.has-unsaved').forEach(row => {
            row.classList.remove('has-unsaved');
            row.classList.add('bg-emerald-500/10');
            setTimeout(() => row.classList.remove('bg-emerald-500/10'), 1500);
        });

        // 3. Reset floating mobile save indicator
        const floatBtn = document.getElementById('mobileFloatingSaveBtn');
        if (floatBtn) {
            floatBtn.classList.remove('ring-4', 'ring-amber-400', 'bg-amber-600');
            floatBtn.classList.add('opacity-90', 'bg-indigo-600');
            floatBtn.innerHTML = '<i class="bi bi-check2-circle text-base"></i><span>All Saved ✓</span>';
        }

        // 4. Update save button feedback
        saveBtns.forEach(b => {
            b.disabled = false;
            b.innerHTML = '<i class="bi bi-check2-all text-sm"></i> Saved ✓';
            setTimeout(() => {
                b.innerHTML = b.dataset.origHtml || 'Save Marks';
            }, 2500);
        });

        showFloatingHud('All Marks Saved!', data.message || `Successfully synced ${data.count || ''} marks to the database!`, 'success');
    } catch (err) {
        console.warn('Sync attempt failed:', err);

        // Auto-retry up to 2 times for transient network fluctuations
        if (retryAttempt < 2 && navigator.onLine) {
            const waitTime = (retryAttempt + 1) * 1500;
            setTimeout(() => saveMarksWithLowInternetSync(form, retryAttempt + 1), waitTime);
            return;
        }

        // Fallback: Ensure draft is securely cached in LocalStorage
        LocalDraftManager.saveDraft();

        showFloatingHud(
            'Connection Drop Detected',
            'Your marks are safely kept in your browser draft and will not be lost. Click to retry sync.',
            'error',
            'Retry Sync',
            () => saveMarksWithLowInternetSync(form, 0)
        );

        saveBtns.forEach(b => {
            b.disabled = false;
            b.innerHTML = b.dataset.origHtml || 'Save Marks';
        });
    }
}

function markRowUnsaved(row) {
    if (row) row.classList.add('has-unsaved');
    LocalDraftManager.saveDraft();
    const floatBtn = document.getElementById('mobileFloatingSaveBtn');
    if (floatBtn) {
        floatBtn.classList.remove('opacity-90');
        floatBtn.classList.add('ring-4', 'ring-amber-400', 'bg-amber-600');
        floatBtn.innerHTML = '<i class="bi bi-exclamation-circle-fill text-base"></i><span>Save Changes*</span>';
    }
}

function submitActiveMarksForm() {
    const masterForm = document.getElementById('masterMarksForm');
    const singleForm = document.getElementById('singleMarksForm');
    const activeForm = masterForm || singleForm;
    if (activeForm) {
        saveMarksWithLowInternetSync(activeForm);
    }
}

function calculateAllRows() {
    const rows = document.querySelectorAll('.student-mark-row');
    rows.forEach(row => updateStudentRowStats(row));
}

function fillZeroForEmpty() {
    if (!confirm('Fill 0 (Absent) for all currently blank mark inputs in this sheet?')) return;
    const inputs = document.querySelectorAll('#masterMarksTable .mark-input');
    inputs.forEach(inp => {
        if (inp.value.trim() === '') {
            inp.value = '0';
        }
    });
    calculateAllRows();
}

function fillZeroForSingleEmpty() {
    if (!confirm('Fill 0 (Absent) for all currently blank mark inputs in this subject sheet?')) return;
    const inputs = document.querySelectorAll('#singleMarksTable .mark-input');
    inputs.forEach(inp => {
        if (inp.value.trim() === '') {
            inp.value = '0';
        }
    });
    calculateAllRows();
}

function bindExcelNavigation(table) {
    if (!table) return;

    table.addEventListener('input', function(e) {
        if (e.target && e.target.classList.contains('mark-input')) {
            const row = e.target.closest('.student-mark-row');
            if (row) debouncedUpdateStudentRowStats(row);
        }
    });

    table.addEventListener('focusin', function(e) {
        if (e.target && e.target.classList.contains('mark-input')) {
            // Auto select value on tap ONLY on desktop mouse devices.
            // On mobile touchscreens, calling .select() triggers the iOS Safari text selection loupe
            // which can swallow touches on the right side and delay virtual keyboard display.
            if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
                setTimeout(() => e.target.select(), 50);
            }
        }
    });

    table.addEventListener('keydown', function(e) {
        if (!e.target.classList.contains('mark-input')) return;

        const currentInput = e.target;
        const currentCell = currentInput.closest('td');
        const currentRow = currentInput.closest('tr');
        if (!currentRow || !currentCell) return;

        const isCardMode = table.classList.contains('force-card-view') || (window.innerWidth < 768 && !table.classList.contains('force-table-view'));
        let targetInput = null;

        if (e.key === 'Enter') {
            e.preventDefault();
            if (isCardMode) {
                // In Card View: Enter advances to next subject of this student, or next student
                const allInputs = Array.from(table.querySelectorAll('.mark-input:not([disabled])'));
                const currentIndex = allInputs.indexOf(currentInput);
                if (currentIndex !== -1 && currentIndex + 1 < allInputs.length) {
                    targetInput = allInputs[currentIndex + 1];
                }
            } else {
                // In Spreadsheet View: Enter advances to same subject in next row
                const cellIndex = Array.from(currentRow.children).indexOf(currentCell);
                const nextRow = currentRow.nextElementSibling;
                if (nextRow && nextRow.children[cellIndex]) {
                    targetInput = nextRow.children[cellIndex].querySelector('.mark-input');
                }
            }
        } else if (e.key === 'ArrowDown') {
            e.preventDefault();
            const cellIndex = Array.from(currentRow.children).indexOf(currentCell);
            const nextRow = currentRow.nextElementSibling;
            if (nextRow && nextRow.children[cellIndex]) {
                targetInput = nextRow.children[cellIndex].querySelector('.mark-input');
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            const cellIndex = Array.from(currentRow.children).indexOf(currentCell);
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

document.addEventListener('DOMContentLoaded', function() {
    // 1. Initial Load of Sections & Courses (ONLY if not already populated from Blade server-side)
    const classSelect = document.getElementById('filter_class_id');
    const courseSelect = document.getElementById('filter_course_id');
    if (classSelect && classSelect.value && (!courseSelect || courseSelect.options.length <= 1)) {
        loadSectionsAndCourses(classSelect.value, PRESELECTED_SECTION, PRESELECTED_COURSE);
    }

    // 2. Bind Excel / Touch Navigation to Master & Single tables
    bindExcelNavigation(document.getElementById('masterMarksTable'));
    bindExcelNavigation(document.getElementById('singleMarksTable'));

    // 3. View Mode Switcher (Card vs Table Grid)
    window.setMarksViewMode = function(mode) {
        const masterTable = document.getElementById('masterMarksTable');
        const singleTable = document.getElementById('singleMarksTable');
        const tables = [masterTable, singleTable].filter(Boolean);

        const cardBtns = [document.getElementById('btnViewCardMaster'), document.getElementById('btnViewCardSingle')].filter(Boolean);
        const tableBtns = [document.getElementById('btnViewTableMaster'), document.getElementById('btnViewTableSingle')].filter(Boolean);

        if (mode === 'card') {
            tables.forEach(t => {
                t.classList.add('force-card-view');
                t.classList.remove('force-table-view');
            });
            cardBtns.forEach(b => {
                b.classList.add('bg-white', 'dark:bg-slate-700', 'text-indigo-600', 'dark:text-indigo-400', 'shadow-xs');
                b.classList.remove('text-slate-600', 'dark:text-slate-300');
            });
            tableBtns.forEach(b => {
                b.classList.remove('bg-white', 'dark:bg-slate-700', 'text-indigo-600', 'dark:text-indigo-400', 'shadow-xs');
                b.classList.add('text-slate-600', 'dark:text-slate-300');
            });
        } else {
            tables.forEach(t => {
                t.classList.remove('force-card-view');
                t.classList.add('force-table-view');
            });
            tableBtns.forEach(b => {
                b.classList.add('bg-white', 'dark:bg-slate-700', 'text-indigo-600', 'dark:text-indigo-400', 'shadow-xs');
                b.classList.remove('text-slate-600', 'dark:text-slate-300');
            });
            cardBtns.forEach(b => {
                b.classList.remove('bg-white', 'dark:bg-slate-700', 'text-indigo-600', 'dark:text-indigo-400', 'shadow-xs');
                b.classList.add('text-slate-600', 'dark:text-slate-300');
            });
        }
        localStorage.setItem('marks_entry_view_mode', mode);
    };

    // Initialize preferred view mode (default: Card on mobile <768px, Table Grid on >=768px)
    const savedViewMode = localStorage.getItem('marks_entry_view_mode');
    if (savedViewMode) {
        setMarksViewMode(savedViewMode);
    } else {
        const initialMode = window.innerWidth < 768 ? 'card' : 'table';
        setMarksViewMode(initialMode);
    }

    // 4. Quick Student Filter Search for Master Table
    const searchMaster = document.getElementById('studentQuickFilter');
    const clearMaster = document.getElementById('clearMasterStudentFilter');
    const masterCountBadge = document.getElementById('masterCountBadge');
    const masterNoMatch = document.getElementById('masterNoMatchMessage');

    function filterMasterStudents(query) {
        const q = (query || '').toLowerCase().trim();
        if (clearMaster) {
            clearMaster.classList.toggle('hidden', q.length === 0);
        }
        const rows = document.querySelectorAll('#masterMarksTable .student-mark-row');
        let visibleCount = 0;
        rows.forEach(row => {
            const name = (row.dataset.studentName || '').toLowerCase();
            const roll = String(row.dataset.rollNo || '');
            if (!q || name.includes(q) || roll.includes(q)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        if (masterCountBadge) {
            if (q) {
                masterCountBadge.textContent = `${visibleCount} of ${rows.length} students`;
                masterCountBadge.classList.add('text-indigo-600', 'font-bold');
            } else {
                masterCountBadge.textContent = `${rows.length} students`;
                masterCountBadge.classList.remove('text-indigo-600', 'font-bold');
            }
        }

        if (masterNoMatch) {
            masterNoMatch.classList.toggle('hidden', visibleCount > 0 || rows.length === 0);
            const querySpan = masterNoMatch.querySelector('.query-term');
            if (querySpan) querySpan.textContent = q;
        }
    }

    if (searchMaster) {
        searchMaster.addEventListener('input', function() {
            filterMasterStudents(this.value);
        });
    }
    if (clearMaster) {
        clearMaster.addEventListener('click', function() {
            if (searchMaster) {
                searchMaster.value = '';
                searchMaster.focus();
            }
            filterMasterStudents('');
        });
    }

    // 5. Quick Student Filter Search for Single Table
    const searchSingle = document.getElementById('singleStudentQuickFilter');
    const clearSingle = document.getElementById('clearSingleStudentFilter');
    const singleCountBadge = document.getElementById('singleCountBadge');
    const singleNoMatch = document.getElementById('singleNoMatchMessage');

    function filterSingleStudents(query) {
        const q = (query || '').toLowerCase().trim();
        if (clearSingle) {
            clearSingle.classList.toggle('hidden', q.length === 0);
        }
        const rows = document.querySelectorAll('#singleMarksTable .single-student-row');
        let visibleCount = 0;
        rows.forEach(row => {
            const name = (row.dataset.studentName || '').toLowerCase();
            const roll = String(row.dataset.rollNo || '');
            if (!q || name.includes(q) || roll.includes(q)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        if (singleCountBadge) {
            if (q) {
                singleCountBadge.textContent = `${visibleCount} of ${rows.length} students`;
                singleCountBadge.classList.add('text-indigo-600', 'font-bold');
            } else {
                singleCountBadge.textContent = `${rows.length} students`;
                singleCountBadge.classList.remove('text-indigo-600', 'font-bold');
            }
        }

        if (singleNoMatch) {
            singleNoMatch.classList.toggle('hidden', visibleCount > 0 || rows.length === 0);
            const querySpan = singleNoMatch.querySelector('.query-term');
            if (querySpan) querySpan.textContent = q;
        }
    }

    if (searchSingle) {
        searchSingle.addEventListener('input', function() {
            filterSingleStudents(this.value);
        });
    }
    if (clearSingle) {
        clearSingle.addEventListener('click', function() {
            if (searchSingle) {
                searchSingle.value = '';
                searchSingle.focus();
            }
            filterSingleStudents('');
        });
    }

    // 6. Dropdown toggle & outside-tap dismissal
    window.toggleSubjectDropdown = function(event, btn) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        const container = btn.closest('.relative');
        if (!container) return;
        const menu = container.querySelector('.dropdown-menu-card');
        if (!menu) return;

        const willOpen = menu.classList.contains('hidden');
        document.querySelectorAll('.dropdown-menu-card').forEach(m => m.classList.add('hidden'));
        if (willOpen) {
            menu.classList.remove('hidden');
        }
    };

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown-menu-card') && !e.target.closest('.subject-dropdown-toggle')) {
            document.querySelectorAll('.dropdown-menu-card').forEach(m => m.classList.add('hidden'));
        }
    });

    // 7. Global Shortcut: Ctrl+S / Cmd+S to submit active form
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            submitActiveMarksForm();
        }
    });

    // 8. Attach Asynchronous Low-Internet Submit Handlers
    const masterForm = document.getElementById('masterMarksForm');
    if (masterForm) {
        masterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            saveMarksWithLowInternetSync(this);
        });
    }

    const singleForm = document.getElementById('singleMarksForm');
    if (singleForm) {
        singleForm.addEventListener('submit', function(e) {
            e.preventDefault();
            saveMarksWithLowInternetSync(this);
        });
    }

    // 9. Initialize Network Monitor & Check Existing Drafts
    NetworkMonitor.checkConnection();
    window.addEventListener('online', () => NetworkMonitor.setOnline());
    window.addEventListener('offline', () => NetworkMonitor.setOffline());
    setInterval(() => NetworkMonitor.checkConnection(), 30000);

    LocalDraftManager.checkExistingDraft();
});
</script>
@endsection
