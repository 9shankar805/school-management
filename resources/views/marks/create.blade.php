@extends('layouts.app')

@section('content')
<div class="px-4 lg:px-8 py-6 max-w-7xl mx-auto">

    {{-- Page Header --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <span class="w-10 h-10 rounded-2xl bg-indigo-600/10 text-indigo-600 flex items-center justify-center font-bold text-xl shadow-xs">
                    <i class="bi bi-pencil-square"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Enter Marks & Grades</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Record exam marks and calculate final grades per course and class section</p>
                </div>
            </div>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800/80 px-3 py-1.5 rounded-xl">
                <li><a href="{{route('home')}}" class="hover:text-indigo-600 transition">Home</a></li>
                <li class="text-slate-300 dark:text-slate-600">/</li>
                <li class="font-semibold text-slate-800 dark:text-slate-200">Enter Marks</li>
            </ol>
        </nav>
    </div>

    @include('session-messages')

    @if(isset($academic_setting) && is_array($academic_setting) && isset($academic_setting['marks_submission_status']) && $academic_setting['marks_submission_status'] == "on")
    <div class="mb-6 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900 flex items-center gap-3">
        <i class="bi bi-check-circle-fill text-emerald-600 text-lg"></i>
        <div>
            <p class="text-xs font-bold text-emerald-800 dark:text-emerald-200">Marks Submission Window is Currently OPEN</p>
            <p class="text-[11px] text-emerald-600 dark:text-emerald-400">You can record exam marks and submit final semester marks.</p>
        </div>
    </div>
    @else
    <div class="mb-6 p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900 flex items-center gap-3">
        <i class="bi bi-exclamation-triangle-fill text-amber-600 text-lg"></i>
        <div>
            <p class="text-xs font-bold text-amber-800 dark:text-amber-200">Marks Submission Window is Closed</p>
            <p class="text-[11px] text-amber-600 dark:text-amber-400">Final marks submission can only be performed when the window is enabled in Academic Settings.</p>
        </div>
    </div>
    @endif

    {{-- Class & Course Filter Card --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6 mb-6">
        <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
            <span class="w-2 h-5 bg-indigo-600 rounded-full"></span>
            <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Select Class, Section & Subject</h2>
        </div>

        <form action="{{ route('course.mark.create') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label for="filter_semester_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    Semester
                </label>
                <select id="filter_semester_id" name="semester_id" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none">
                    <option value="0">All / Default Semester</option>
                    @isset($semesters)
                        @foreach ($semesters as $sem)
                            <option value="{{ $sem->id }}" {{ $semester_id == $sem->id ? 'selected' : '' }}>{{ $sem->semester_name }}</option>
                        @endforeach
                    @endisset
                </select>
            </div>

            <div>
                <label for="filter_class_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    Class / Grade <span class="text-rose-500">*</span>
                </label>
                <select id="filter_class_id" name="class_id" required onchange="loadSectionsAndCourses(this.value)" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none">
                    <option value="">-- Select Class --</option>
                    @isset($school_classes)
                        @foreach ($school_classes as $sc)
                            <option value="{{ $sc->id }}" {{ $class_id == $sc->id ? 'selected' : '' }}>{{ $sc->class_name }}</option>
                        @endforeach
                    @endisset
                </select>
            </div>

            <div>
                <label for="filter_section_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    Section
                </label>
                <select id="filter_section_id" name="section_id" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none">
                    <option value="0">All / Select Section</option>
                </select>
            </div>

            <div>
                <label for="filter_course_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    Course / Subject <span class="text-rose-500">*</span>
                </label>
                <select id="filter_course_id" name="course_id" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none">
                    <option value="0">Select Class First</option>
                </select>
            </div>

            <div class="sm:col-span-2 lg:col-span-4 text-right pt-2">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md transition flex items-center gap-2 inline-flex">
                    <i class="bi bi-search"></i> Load Grade Sheet
                </button>
            </div>
        </form>
    </div>

    @if($class_id && $course_id)
        {{-- STEP 2: Marks Entry Matrix --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
            
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">
                        Grade Entry Sheet
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Class #{{ request()->query('class_name', $class_id) }} | Course #{{ request()->query('course_name', $course_id) }}
                    </p>
                </div>

                @if (!$final_marks_submitted && count($exams) > 0 && isset($academic_setting) && is_array($academic_setting) && isset($academic_setting['marks_submission_status']) && $academic_setting['marks_submission_status'] == "on")
                <a href="{{ route('course.final.mark.submit.show', ['class_id' => $class_id, 'class_name' => request()->query('class_name'), 'section_id' => $section_id, 'section_name' => request()->query('section_name'), 'course_id' => $course_id, 'course_name' => request()->query('course_name'), 'semester_id' => $semester_id]) }}" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md transition flex items-center gap-1.5" onclick="return confirm('Are you sure you want to submit final marks?')">
                    <i class="bi bi-check2-all"></i> Submit Final Marks
                </a>
                @endif
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
                                <th class="py-3 px-4 min-w-[180px]">Student Name</th>
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
                                    @endphp
                                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                                        <td class="py-3 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                            {{ $stu?->roll_no ?? '—' }}
                                        </td>
                                        <td class="py-3 px-4 font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-full bg-indigo-50 text-indigo-600 font-bold flex items-center justify-center text-xs">
                                                {{ substr($stu->first_name ?? 'S', 0, 1) }}
                                            </div>
                                            {{ $stu->first_name ?? '' }} {{ $stu->last_name ?? '' }}
                                        </td>
                                        @foreach ($students_with_mark as $st)
                                            @php $currentExamId = $exams[$markedExamCount]->id; @endphp
                                            <td class="py-3 px-4">
                                                <input type="number" step="0.01" min="0" @if($exam_rules_by_exam_id->has($currentExamId)) max="{{ $exam_rules_by_exam_id[$currentExamId] }}" @endif class="w-full text-xs px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20" name="student_mark[{{ $students_with_mark[0]->student->id }}][{{ $currentExamId }}]" value="{{ $st->marks }}">
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
                                                <input type="number" step="0.01" min="0" @if($exam_rules_by_exam_id->has($currentExamId)) max="{{ $exam_rules_by_exam_id[$currentExamId] }}" @endif class="w-full text-xs px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20" name="student_mark[{{ $students_with_mark[0]->student->id }}][{{ $currentExamId }}]">
                                            </td>
                                            @php $markedExamCount++; @endphp
                                        @endfor
                                    </tr>
                                @endforeach
                            @elseif(count($sectionStudents) > 0)
                                @foreach ($sectionStudents as $sectionStudent)
                                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                                        <td class="py-3 px-4 font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-full bg-indigo-50 text-indigo-600 font-bold flex items-center justify-center text-xs">
                                                {{ substr($sectionStudent->student->first_name ?? 'S', 0, 1) }}
                                            </div>
                                            {{ $sectionStudent->student->first_name ?? '' }} {{ $sectionStudent->student->last_name ?? '' }}
                                        </td>
                                        @foreach ($exams as $exam)
                                            <td class="py-3 px-4">
                                                <input type="number" step="0.01" min="0" @if($exam_rules_by_exam_id->has($exam->id)) max="{{ $exam_rules_by_exam_id[$exam->id] }}" @endif class="w-full text-xs px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20" name="student_mark[{{ $sectionStudent->student->id }}][{{ $exam->id }}]">
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
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md transition flex items-center gap-2">
                        <i class="bi bi-check2 text-base"></i> Save Marks
                    </button>
                    @else
                    <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-xl">
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

    @include('layouts.footer')
</div>

<script>
function loadSectionsAndCourses(classId) {
    const selectSection = document.getElementById('filter_section_id');
    const selectCourse = document.getElementById('filter_course_id');

    selectSection.innerHTML = '<option value="0">Loading sections...</option>';
    selectCourse.innerHTML = '<option value="0">Loading courses...</option>';

    if (!classId) {
        selectSection.innerHTML = '<option value="0">All / Select Section</option>';
        selectCourse.innerHTML = '<option value="0">Select Class First</option>';
        return;
    }

    fetch('{{ route("get.sections.courses.by.classId") }}?class_id=' + classId)
        .then(res => res.json())
        .then(data => {
            let secHtml = '<option value="0">All / Select Section</option>';
            if (data.sections && data.sections.length > 0) {
                data.sections.forEach(sec => {
                    secHtml += `<option value="${sec.id}">${sec.section_name} (Room ${sec.room_no || 'N/A'})</option>`;
                });
            }
            selectSection.innerHTML = secHtml;

            let crsHtml = '<option value="0">-- Select Course --</option>';
            if (data.courses && data.courses.length > 0) {
                data.courses.forEach(crs => {
                    crsHtml += `<option value="${crs.id}">${crs.course_name} (${crs.course_type || 'Theory'})</option>`;
                });
            }
            selectCourse.innerHTML = crsHtml;
        })
        .catch(err => {
            selectSection.innerHTML = '<option value="0">All / Select Section</option>';
            selectCourse.innerHTML = '<option value="0">-- Select Course --</option>';
        });
}

// Auto load sections/courses if class_id is preselected
document.addEventListener('DOMContentLoaded', function() {
    const classSelect = document.getElementById('filter_class_id');
    if (classSelect && classSelect.value) {
        loadSectionsAndCourses(classSelect.value);
    }
});
</script>
@endsection
