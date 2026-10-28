@extends('layouts.app')

@section('content')
<style>
@media (max-width: 767.98px) {
    #finalMarksTable,
    #finalMarksTable tbody,
    #finalMarksTable tr {
        display: block !important;
        width: 100% !important;
    }
    #finalMarksTable thead {
        display: none !important;
    }
    #finalMarksTable tbody {
        padding: 0.5rem !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 0.75rem !important;
    }
    #finalMarksTable tr {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 1.25rem !important;
        padding: 1rem !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05) !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 0.5rem !important;
    }
    .dark #finalMarksTable tr {
        background: #1e293b !important;
        border-color: #334155 !important;
    }
    #finalMarksTable td {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        padding: 0.35rem 0 !important;
        border: none !important;
        width: 100% !important;
    }
    #finalMarksTable td.cell-roll {
        display: none !important;
    }
    #finalMarksTable td.cell-student {
        border-bottom: 1px solid #f1f5f9 !important;
        padding-bottom: 0.6rem !important;
        margin-bottom: 0.2rem !important;
    }
    .dark #finalMarksTable td.cell-student {
        border-bottom-color: #334155 !important;
    }
    input.final-mark-input,
    input.calc-mark-input {
        font-size: 16px !important;
        min-height: 42px !important;
        width: 130px !important;
        text-align: center !important;
    }
}
</style>
<div class="px-3 sm:px-4 lg:px-8 py-4 sm:py-6 max-w-7xl mx-auto space-y-4 sm:space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <span class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white flex items-center justify-center font-bold text-xl shadow-md shadow-emerald-500/20">
                    <i class="bi bi-award-fill"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight flex items-center gap-2">
                        Submit Final Marks & Evaluations
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Review calculated term marks and assign official final subject scores
                    </p>
                </div>
            </div>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-800/80 px-3.5 py-2 rounded-xl border border-slate-200/70 dark:border-slate-800 shadow-xs">
                <li><a href="{{ route('home') }}" class="hover:text-indigo-600 transition flex items-center gap-1"><i class="bi bi-house-door"></i> Home</a></li>
                <li class="text-slate-300 dark:text-slate-600">/</li>
                <li><a href="{{ route('course.mark.create') }}" class="hover:text-indigo-600 transition">Enter Marks</a></li>
                <li class="text-slate-300 dark:text-slate-600">/</li>
                <li class="font-semibold text-slate-800 dark:text-slate-200">Final Marks</li>
            </ol>
        </nav>
    </div>

    @include('session-messages')

    {{-- Breadcrumb Banner Card --}}
    <div class="bg-gradient-to-r from-slate-900 via-emerald-950 to-slate-900 text-white rounded-3xl p-6 shadow-xl border border-emerald-900/50 relative overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2 flex-wrap">
                    <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-white/10 text-emerald-200 border border-white/10 flex items-center gap-1.5">
                        <i class="bi bi-mortarboard-fill"></i>
                        Class: {{ $class_name ?: 'Class #'.$class_id }}
                    </span>
                    <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-white/10 text-slate-200 border border-white/10 flex items-center gap-1.5">
                        <i class="bi bi-diagram-3"></i>
                        Section: {{ $section_name ?: 'All Sections' }}
                    </span>
                    <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 flex items-center gap-1.5">
                        <i class="bi bi-journal-check"></i>
                        Course: {{ $course_name ?: 'Course #'.$course_id }}
                    </span>
                </div>
                <h2 class="text-xl font-black text-white tracking-tight">
                    Official Term Evaluation Review
                </h2>
                <p class="text-xs text-emerald-200/80 mt-1">
                    Calculated scores are aggregated from individual examination entries. You can adjust final marks or add special evaluation notes.
                </p>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" onclick="copyAllCalculatedToFinal()" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold backdrop-blur-md border border-white/10 transition flex items-center gap-2" title="Auto-populate final marks from calculated marks">
                    <i class="bi bi-magic text-emerald-300"></i>
                    <span>Copy Calculated to Final</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Evaluation Table Form --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
        <form action="{{ route('course.final.mark.submit.store') }}" method="POST" id="finalMarksForm">
            @csrf
            <input type="hidden" name="session_id" value="{{ $current_school_session_id }}">
            <input type="hidden" name="semester_id" value="{{ $semester_id }}">
            <input type="hidden" name="class_id" value="{{ $class_id }}">
            <input type="hidden" name="section_id" value="{{ $section_id }}">
            <input type="hidden" name="course_id" value="{{ $course_id }}">

            @php
                // Build a combined list of students
                $studentsList = collect();
                if (isset($sectionStudents) && count($sectionStudents) > 0) {
                    $studentsList = $sectionStudents;
                } elseif (isset($students_with_marks) && count($students_with_marks) > 0) {
                    // Extract student wrappers from students_with_marks
                    foreach ($students_with_marks as $sid => $mRows) {
                        $first = $mRows->first();
                        $studentsList->push((object)[
                            'student_id' => $sid,
                            'student'    => $first->student ?? null,
                            'roll_no'    => $first->student->roll_no ?? null,
                        ]);
                    }
                }

                // Map calculated marks: student_id => sum of marks
                $calculatedMarksMap = [];
                if (isset($students_with_marks)) {
                    foreach ($students_with_marks as $sid => $mRows) {
                        $calculatedMarksMap[$sid] = $mRows->sum('marks');
                    }
                }
            @endphp

            @if(count($studentsList) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse" id="finalMarksTable">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 font-bold uppercase tracking-wider text-[11px]">
                                <th class="py-3 px-4 w-16 text-center">Roll</th>
                                <th class="py-3 px-4 min-w-[200px]">Student Details</th>
                                <th class="py-3 px-4 w-36 text-center">Calculated Marks</th>
                                <th class="py-3 px-4 w-44 text-center">Official Final Marks <span class="text-rose-500">*</span></th>
                                <th class="py-3 px-4 min-w-[250px]">Remarks / Evaluation Note</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                            @foreach ($studentsList as $sIndex => $secStudent)
                                @php
                                    $stu = $secStudent->student ?? null;
                                    $stuId = $stu->id ?? $secStudent->student_id;
                                    $rollNo = $secStudent->roll_no ?? $stu?->roll_no ?? ($sIndex + 1);
                                    $fullName = trim(($stu->first_name ?? '') . ' ' . ($stu->last_name ?? ''));
                                    if (empty($fullName)) {
                                        $fullName = 'Student #' . $stuId;
                                    }
                                    $calc = $calculatedMarksMap[$stuId] ?? 0;
                                @endphp
                                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                                    {{-- Roll No --}}
                                    <td class="cell-roll py-3 px-4 font-mono font-bold text-center text-indigo-600 dark:text-indigo-400">
                                        {{ $rollNo }}
                                    </td>

                                    {{-- Student Name --}}
                                    <td class="cell-student py-3 px-4 font-semibold text-slate-800 dark:text-slate-100">
                                        <div class="flex items-center justify-between w-full">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-emerald-500 to-teal-600 text-white font-bold flex items-center justify-center text-xs shadow-xs flex-shrink-0">
                                                    {{ strtoupper(substr($fullName, 0, 1)) }}
                                                </div>
                                                <div class="truncate">
                                                    <span class="block truncate font-bold text-sm text-slate-800 dark:text-slate-100">{{ $fullName }}</span>
                                                    <span class="text-[10px] text-slate-400 font-normal">ID: #{{ $stuId }}</span>
                                                </div>
                                            </div>
                                            <div class="md:hidden">
                                                <span class="px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 font-mono font-bold text-xs">
                                                    Roll #{{ $rollNo }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Calculated Marks --}}
                                    <td class="py-3 px-4 text-center">
                                        <span class="md:hidden text-xs text-slate-500 font-semibold">Calculated:</span>
                                        <input type="number" 
                                               step="0.01" 
                                               name="calculated_mark[{{ $stuId }}]" 
                                               value="{{ $calc }}" 
                                               readonly 
                                               class="calc-mark-input w-28 text-center font-mono text-xs font-bold py-1.5 px-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 outline-none">
                                    </td>

                                    {{-- Final Marks Input --}}
                                    <td class="py-3 px-4 text-center">
                                        <span class="md:hidden text-xs text-slate-700 dark:text-slate-200 font-bold">Final Mark *:</span>
                                        <input type="number" 
                                               step="0.01" 
                                               min="0" 
                                               name="final_mark[{{ $stuId }}]" 
                                               value="{{ $calc > 0 ? $calc : '' }}" 
                                               required 
                                               placeholder="0.00"
                                               class="final-mark-input w-36 text-center font-mono text-xs font-bold py-1.5 px-3 rounded-lg border border-indigo-300 dark:border-indigo-700 bg-white dark:bg-slate-800 text-indigo-700 dark:text-indigo-300 outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                                    </td>

                                    {{-- Note / Remarks --}}
                                    <td class="py-3 px-4">
                                        <span class="md:hidden text-xs text-slate-500 font-semibold mb-1 block">Remarks:</span>
                                        <input type="text" 
                                               name="note[{{ $stuId }}]" 
                                               placeholder="Optional remark (e.g. Best 2 of 3 quizzes counted)..."
                                               class="w-full text-xs py-1.5 px-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Action Bar --}}
                <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/80 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>Official submission will record final term marks for report card and transcript generation.</span>
                    </div>

                    <div class="flex items-center gap-3">
                        <a href="{{ route('course.mark.create', ['semester_id' => $semester_id, 'class_id' => $class_id, 'section_id' => $section_id, 'course_id' => $course_id]) }}" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 text-xs font-semibold transition">
                            Back to Marks Entry
                        </a>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-xs font-bold shadow-lg shadow-emerald-600/30 transition flex items-center gap-2">
                            <i class="bi bi-check2-all text-sm"></i>
                            <span>Confirm & Submit Final Marks</span>
                        </button>
                    </div>
                </div>
            @else
                <div class="py-16 text-center">
                    <span class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center text-3xl mx-auto mb-3">
                        <i class="bi bi-people"></i>
                    </span>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">No Student Records Found</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                        Please record and save exam marks in the Marks Entry screen before submitting final marks.
                    </p>
                    <div class="mt-4">
                        <a href="{{ route('course.mark.create', ['semester_id' => $semester_id, 'class_id' => $class_id, 'section_id' => $section_id, 'course_id' => $course_id]) }}" class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-bold inline-flex items-center gap-1.5">
                            <i class="bi bi-arrow-left"></i> Go to Marks Entry
                        </a>
                    </div>
                </div>
            @endif
        </form>
    </div>

    @include('layouts.footer')
</div>

<script>
function copyAllCalculatedToFinal() {
    const rows = document.querySelectorAll('#finalMarksTable tbody tr');
    rows.forEach(row => {
        const calcInput = row.querySelector('.calc-mark-input');
        const finalInput = row.querySelector('.final-mark-input');
        if (calcInput && finalInput) {
            finalInput.value = calcInput.value;
        }
    });
}
</script>
@endsection
