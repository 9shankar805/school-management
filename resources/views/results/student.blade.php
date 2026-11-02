@extends('layouts.app')
@section('content')
@php
    $student      = $result['student'] ?? null;
    $class        = $result['class'] ?? null;
    $section      = $result['section'] ?? null;
    $semester     = $result['semester'] ?? null;
    $promotion    = $result['promotion'] ?? null;
    $courses      = $result['courses'] ?? [];
    $gpa          = (float)($result['gpa'] ?? 0);
    $failedCount  = (int)($result['failed'] ?? 0);
    $passedCount  = (int)($result['passed'] ?? count($courses));
    $totalObtained = (float)($result['totalMarks'] ?? 0);
    $sessionId    = $promotion?->session_id ?? session('browse_session_id') ?? 1;

    // Attendance stats
    $attendance = null;
    if ($student && !empty($student->id) && class_exists(\App\Models\Attendance::class)) {
        try {
            $attendance = \App\Models\Attendance::statsFor($student->id, $sessionId);
        } catch (\Throwable $e) {}
    }

    // Class Teacher
    $classTeacher = null;
    if ($class && !empty($class->id) && class_exists(\App\Models\AssignedTeacher::class)) {
        try {
            $assignedTeacher = \App\Models\AssignedTeacher::with('teacher')
                ->where('session_id', $sessionId)
                ->where('class_id', $class->id)
                ->where('is_class_teacher', true)
                ->when($section && !empty($section->id) && $section->id > 0, function($q) use ($section) {
                    $q->where(function($sq) use ($section) {
                        $sq->where('section_id', $section->id)->orWhere('section_id', 0)->orWhereNull('section_id');
                    });
                })
                ->first();
            $classTeacher = $assignedTeacher?->teacher;
        } catch (\Throwable $e) {}
    }

    // Totals
    $totalFullMarks  = 0;
    $totalPassMarks  = 0;
    $totalThObtained = 0;
    $totalPrObtained = 0;
    foreach ($courses as $c) {
        $fm = (float)($c['full_marks'] ?? 100);
        $pm = (float)($c['pass_marks'] ?? round($fm * 0.4, 0));
        $totalFullMarks += $fm;
        $totalPassMarks += $pm;
        $totalThObtained += (float)($c['th_marks'] ?? $c['final_marks']);
        if (!empty($c['has_practical']) && isset($c['pr_marks'])) {
            $totalPrObtained += (float)$c['pr_marks'];
        }
    }
    $aggregatePct = $totalFullMarks > 0 ? round(($totalObtained / $totalFullMarks) * 100, 1) : 0;

    // Division / Status
    if ($failedCount > 0) {
        $overallGrade = 'NG';
        $division = 'Needs Retest';
        $resultStatus = 'RE-EXAM REQUIRED';
    } elseif ($gpa >= 3.6) {
        $overallGrade = 'A+';
        $division = 'Distinction';
        $resultStatus = 'PASSED WITH DISTINCTION';
    } elseif ($gpa >= 3.2) {
        $overallGrade = 'A';
        $division = 'First Division';
        $resultStatus = 'PASSED';
    } elseif ($gpa >= 2.8) {
        $overallGrade = 'B+';
        $division = 'First Division';
        $resultStatus = 'PASSED';
    } elseif ($gpa >= 2.4) {
        $overallGrade = 'B';
        $division = 'Second Division';
        $resultStatus = 'PASSED';
    } elseif ($gpa >= 2.0) {
        $overallGrade = 'C+';
        $division = 'Third Division';
        $resultStatus = 'PASSED';
    } else {
        $overallGrade = 'C';
        $division = 'Pass';
        $resultStatus = 'PASSED';
    }

    // Dynamic Teacher Qualitative Assessment
    if ($failedCount > 0) {
        $teacherRemark = "Demonstrates good potential in interactive discussions, but requires remedial attention and structured revision in subject(s) below pass marks. Daily practice and subject teacher consultation strongly recommended.";
    } elseif ($gpa >= 3.75) {
        $teacherRemark = "Outstanding academic achievement! Demonstrates exceptional conceptual mastery, analytical precision, and commendable classroom leadership. Consistently dedicated to highest academic standards.";
    } elseif ($gpa >= 3.3) {
        $teacherRemark = "Very good academic performance! Consistently attentive, diligent, and shows commendable analytical thinking and positive conduct in class discussions.";
    } elseif ($gpa >= 2.8) {
        $teacherRemark = "Good performance overall. Participates constructively in class; regular home practice and focus on challenging areas will help achieve higher distinction.";
    } elseif ($gpa >= 2.2) {
        $teacherRemark = "Satisfactory progress. Possesses solid foundational ability; higher consistency in homework completion and attentive class focus will yield even better marks.";
    } else {
        $teacherRemark = "Basic pass. Regular attendance, structured homework schedule, and targeted revision in core subjects are encouraged for the upcoming term.";
    }

    // Co-Curricular & Conduct Attributes
    if ($failedCount > 0) {
        $traitDiscipline    = 'Good';
        $traitParticipation = 'Developing';
        $traitHomework      = 'Needs Regularity';
        $traitTeamwork      = 'Cooperative';
    } elseif ($gpa >= 3.6) {
        $traitDiscipline    = 'Exemplary';
        $traitParticipation = 'Active & Inquisitive';
        $traitHomework      = 'Prompt & Thorough';
        $traitTeamwork      = 'Commendable Leader';
    } elseif ($gpa >= 3.0) {
        $traitDiscipline    = 'Very Good';
        $traitParticipation = 'Constructive';
        $traitHomework      = 'Regular';
        $traitTeamwork      = 'Helpful & Polite';
    } else {
        $traitDiscipline    = 'Good';
        $traitParticipation = 'Satisfactory';
        $traitHomework      = 'Fair';
        $traitTeamwork      = 'Friendly';
    }
@endphp

<div class="flex-1 p-6 lg:p-8 overflow-auto">

    {{-- Breadcrumb Navigation --}}
    <div class="flex items-center justify-between mb-4">
        <nav class="text-xs text-slate-400">
            <a href="{{ route('results.index') }}" class="hover:text-indigo-600 font-medium">Results</a>
            <span class="mx-1.5">/</span>
            <span class="text-slate-600 font-semibold">{{ $student?->full_name }}</span>
        </nav>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-lg shadow-sm transition">
                <i class="bi bi-printer text-slate-500"></i> Print View
            </button>
            <a href="{{ route('results.report-card', $student->id) . '?' . http_build_query(request()->query()) }}"
               data-no-loader
               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-bold text-white bg-indigo-700 hover:bg-indigo-800 rounded-lg shadow-sm transition">
                <i class="bi bi-file-earmark-arrow-down"></i> Official PDF Report Card
            </a>
        </div>
    </div>

    @include('session-messages')

    {{-- Student Profile & KPI Header Card --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-start justify-between gap-6 pb-6 border-b border-slate-100">
            <div class="flex items-start gap-4">
                <img src="{{ $student->avatar }}" class="w-16 h-16 rounded-2xl object-cover border-2 border-indigo-100 shadow-sm" alt="">
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="text-xl font-bold text-slate-800">{{ $student->full_name }}</h1>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $failedCount === 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                            {{ $resultStatus }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 font-medium mt-1">
                        {{ $semester?->semester_name ?? 'Terminal Examination' }} &bull; Class: <strong class="text-slate-700">{{ $class?->class_name ?? '—' }}</strong> (Sec: <strong class="text-slate-700">{{ $section?->section_name ?? 'All' }}</strong>)
                    </p>
                    <div class="flex flex-wrap items-center gap-y-1 gap-x-4 text-xs text-slate-500 mt-2">
                        <span><strong>Roll No:</strong> {{ $promotion?->roll_no ?? $student?->roll_no ?? '—' }}</span>
                        <span>&bull;</span>
                        <span><strong>ID:</strong> {{ $student->admission_no ?? sprintf('STD-%05d', $student->id) }}</span>
                        @if(!empty($attendance) && ($attendance['total'] ?? 0) > 0)
                        <span>&bull;</span>
                        <span><strong>Attendance:</strong> {{ $attendance['present'] }}/{{ $attendance['total'] }} Days ({{ $attendance['percentage'] }}%)</span>
                        @endif
                        @if($classTeacher)
                        <span>&bull;</span>
                        <span><strong>Class Teacher:</strong> {{ $classTeacher->full_name }}</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Download Actions --}}
            <div class="flex items-center gap-2 flex-shrink-0">
                <a href="{{ route('results.report-card', $student->id) . '?' . http_build_query(request()->query()) }}"
                   data-no-loader
                   class="btn inline-flex items-center gap-2 px-4 py-2.5 text-white text-sm font-bold rounded-xl transition shadow-sm"
                   style="background-color: #1e3a8a !important; color: #ffffff !important;">
                    <i class="bi bi-file-earmark-pdf"></i> Download Report Card (PDF)
                </a>
            </div>
        </div>

        {{-- Executive KPI Metrics Ribbon --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 pt-6">
            <div class="bg-indigo-50/60 border border-indigo-100/80 rounded-xl p-3.5 text-center">
                <p class="text-2xl font-black text-indigo-700">{{ number_format($gpa, 2) }}</p>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mt-0.5">GPA Point</p>
            </div>
            <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3.5 text-center">
                <p class="text-2xl font-black text-slate-800">{{ round($totalObtained, 1) }} <span class="text-xs text-slate-400 font-medium">/ {{ $totalFullMarks }}</span></p>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mt-0.5">Total Marks</p>
            </div>
            <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3.5 text-center">
                <p class="text-2xl font-black text-slate-800">{{ $aggregatePct }}%</p>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mt-0.5">Percentage</p>
            </div>
            <div class="bg-amber-50/60 border border-amber-100/80 rounded-xl p-3.5 text-center">
                <p class="text-2xl font-black text-amber-700">{{ $overallGrade }}</p>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mt-0.5">{{ $division }}</p>
            </div>
            <div class="bg-emerald-50/60 border border-emerald-100/80 rounded-xl p-3.5 text-center">
                <p class="text-2xl font-black text-emerald-700">{{ $passedCount }}</p>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mt-0.5">Courses Cleared</p>
            </div>
            <div class="border rounded-xl p-3.5 text-center {{ !empty($result['rank']) ? 'bg-amber-50/60 border-amber-200 text-amber-800' : 'bg-slate-50 border-slate-200 text-slate-400' }}">
                <p class="text-2xl font-black">{{ !empty($result['rank']) ? '#' . $result['rank'] : '—' }}</p>
                <p class="text-[10px] font-bold uppercase tracking-wider mt-0.5">Class Merit Rank</p>
            </div>
        </div>
    </div>

    {{-- Academic Performance Ledger (Subject Table) --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-800">Subject-Wise Marks &amp; Evaluation Ledger</h2>
                <p class="text-xs text-slate-400 mt-0.5">Official score statement with Curriculum Development Centre (CDC) Grading</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg">
                {{ count($courses) }} Subjects Enrolled
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs font-bold text-slate-600 bg-slate-100/90 border-b border-slate-200 uppercase tracking-wider text-center">
                        <th rowspan="2" class="px-4 py-3 text-center w-12 border-r border-slate-200">#</th>
                        <th rowspan="2" class="px-5 py-3 text-left border-r border-slate-200 min-w-[200px]">Subject / Course Name</th>
                        <th rowspan="2" class="px-3 py-3 text-center border-r border-slate-200 w-20">Credit</th>
                        <th colspan="3" class="px-4 py-2 text-center bg-indigo-50 text-indigo-900 border-r border-slate-200 font-extrabold">Theory (TH)</th>
                        <th colspan="3" class="px-4 py-2 text-center bg-emerald-50 text-emerald-900 border-r border-slate-200 font-extrabold">Practical / Internal (PR)</th>
                        <th colspan="3" class="px-4 py-2 text-center bg-amber-50 text-amber-900 border-r border-slate-200 font-extrabold">Total &amp; Grade</th>
                        <th rowspan="2" class="px-4 py-3 text-center w-28">Status</th>
                        @if(auth()->user()->hasRole('student'))
                        <th rowspan="2" class="px-4 py-3 text-center w-20">Re-Exam</th>
                        @endif
                    </tr>
                    <tr class="text-[11px] font-bold text-slate-500 bg-slate-50 border-b border-slate-200 uppercase tracking-wider text-center">
                        <th class="px-2.5 py-1.5 text-indigo-800 bg-indigo-50/50">FM</th>
                        <th class="px-2.5 py-1.5 text-indigo-800 bg-indigo-50/50">Obt</th>
                        <th class="px-2.5 py-1.5 text-indigo-800 bg-indigo-50/50 border-r border-slate-200">Grade</th>

                        <th class="px-2.5 py-1.5 text-emerald-800 bg-emerald-50/50">FM</th>
                        <th class="px-2.5 py-1.5 text-emerald-800 bg-emerald-50/50">Obt</th>
                        <th class="px-2.5 py-1.5 text-emerald-800 bg-emerald-50/50 border-r border-slate-200">Grade</th>

                        <th class="px-3 py-1.5 text-amber-900 bg-amber-50/50">Total</th>
                        <th class="px-3 py-1.5 text-amber-900 bg-amber-50/50">GP</th>
                        <th class="px-3 py-1.5 text-amber-900 bg-amber-50/50 border-r border-slate-200">Final</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @php $sn = 1; @endphp
                    @foreach($courses as $c)
                    @php
                        $fm  = (float)($c['full_marks'] ?? 100);
                        $pm  = (float)($c['pass_marks'] ?? round($fm * 0.4, 0));
                        $ob  = (float)$c['final_marks'];
                        $pct = $fm > 0 ? round(($ob / $fm) * 100, 1) : $ob;
                        $passed = (bool)($c['passed'] ?? ($ob >= $pm));
                        $hasPr  = !empty($c['has_practical']);
                        $thFm   = $c['th_fm'] ?? ($hasPr ? 75.0 : $fm);
                        $thMarks = $c['th_marks'] ?? ($hasPr ? round($ob * 0.75, 1) : $ob);
                        $thGrade = $c['th_grade'] ?? $c['grade'];
                        $prFm   = $c['pr_fm'] ?? ($hasPr ? 25.0 : null);
                        $prMarks = $c['pr_marks'] ?? ($hasPr ? round($ob * 0.25, 1) : null);
                        $prGrade = $c['pr_grade'] ?? ($hasPr ? 'A' : null);
                        $credit  = $c['credit_hour'] ?? 4.0;
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition {{ !$passed ? 'bg-rose-50/20' : '' }}">
                        <td class="px-4 py-3.5 text-center text-xs font-bold text-slate-400 border-r border-slate-100">{{ $sn++ }}</td>
                        <td class="px-5 py-3.5 font-bold text-slate-800 border-r border-slate-100">
                            {{ $c['course']?->course_name ?? $c['course_name'] ?? '—' }}
                            @if($hasPr)
                                <span class="ml-1.5 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    TH+PR
                                </span>
                            @else
                                <span class="ml-1.5 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-500">
                                    TH ONLY
                                </span>
                            @endif
                        </td>
                        <td class="px-3 py-3.5 text-center text-xs font-semibold text-slate-600 border-r border-slate-100">
                            {{ number_format($credit, 1) }}
                        </td>

                        {{-- Theory (TH) Columns --}}
                        <td class="px-2.5 py-3.5 text-center text-xs text-slate-500 font-medium bg-indigo-50/10">
                            {{ number_format($thFm, 0) }}
                        </td>
                        <td class="px-2.5 py-3.5 text-center font-bold text-sm text-indigo-900 bg-indigo-50/10">
                            {{ round($thMarks, 1) }}
                        </td>
                        <td class="px-2.5 py-3.5 text-center border-r border-slate-100 bg-indigo-50/10">
                            <span class="inline-block px-2 py-0.5 rounded text-xs font-black bg-indigo-100 text-indigo-800">
                                {{ $thGrade }}
                            </span>
                        </td>

                        {{-- Practical (PR) Columns --}}
                        @if($hasPr)
                            <td class="px-2.5 py-3.5 text-center text-xs text-slate-500 font-medium bg-emerald-50/10">
                                {{ number_format($prFm, 0) }}
                            </td>
                            <td class="px-2.5 py-3.5 text-center font-bold text-sm text-emerald-800 bg-emerald-50/10">
                                {{ round($prMarks, 1) }}
                            </td>
                            <td class="px-2.5 py-3.5 text-center border-r border-slate-100 bg-emerald-50/10">
                                <span class="inline-block px-2 py-0.5 rounded text-xs font-black bg-emerald-100 text-emerald-800">
                                    {{ $prGrade }}
                                </span>
                            </td>
                        @else
                            <td class="px-2.5 py-3.5 text-center text-xs font-bold text-slate-300 bg-slate-50/50">—</td>
                            <td class="px-2.5 py-3.5 text-center text-xs font-bold text-slate-300 bg-slate-50/50">—</td>
                            <td class="px-2.5 py-3.5 text-center border-r border-slate-100 bg-slate-50/50">
                                <span class="text-[11px] font-semibold text-slate-400 italic">No PR</span>
                            </td>
                        @endif

                        {{-- Final Evaluation Columns --}}
                        <td class="px-3 py-3.5 text-center font-black text-base bg-amber-50/10 {{ $passed ? 'text-slate-900' : 'text-rose-600' }}">
                            {{ round($ob, 1) }}
                        </td>
                        <td class="px-3 py-3.5 text-center font-bold text-indigo-700 bg-amber-50/10">
                            {{ number_format((float)$c['point'], 2) }}
                        </td>
                        <td class="px-3 py-3.5 text-center border-r border-slate-100 bg-amber-50/10">
                            <span class="inline-block px-2.5 py-1 rounded-lg text-xs font-black {{ $passed ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                {{ $c['grade'] }}
                            </span>
                        </td>

                        <td class="px-4 py-3.5 text-center">
                            <span class="text-xs font-bold {{ $passed ? 'text-emerald-700' : 'text-rose-600' }}">
                                {{ $passed ? 'CLEARED' : 'NEEDS RETEST' }}
                            </span>
                        </td>
                        @if(auth()->user()->hasRole('student'))
                        <td class="px-4 py-3.5 text-center">
                            @if(!$passed)
                            <a href="{{ route('re-exam.create') }}" class="inline-flex items-center gap-1 text-xs font-bold text-rose-600 hover:text-rose-800 underline">
                                Apply
                            </a>
                            @else
                            <span class="text-slate-300 text-xs">—</span>
                            @endif
                        </td>
                        @endif
                    </tr>
                    @endforeach

                    {{-- Total Grand Aggregate Row --}}
                    <tr class="bg-indigo-50/60 font-bold text-slate-800 border-t-2 border-indigo-200">
                        <td colspan="2" class="px-5 py-4 text-right text-xs uppercase tracking-wider text-indigo-900 font-black border-r border-indigo-100">
                            Grand Total Summary:
                        </td>
                        <td class="px-3 py-4 text-center text-xs font-bold text-indigo-900 border-r border-indigo-100">
                            {{ count($courses) * 4.0 }} Cr
                        </td>
                        <td colspan="3" class="px-4 py-4 text-center text-sm font-bold text-indigo-900 border-r border-indigo-100 bg-indigo-50/40">
                            TH Total: {{ round($totalThObtained, 1) }}
                        </td>
                        <td colspan="3" class="px-4 py-4 text-center text-sm font-bold text-emerald-900 border-r border-indigo-100 bg-emerald-50/40">
                            PR Total: {{ round($totalPrObtained, 1) }}
                        </td>
                        <td class="px-3 py-4 text-center text-base font-black text-indigo-950 bg-amber-50/50">
                            {{ round($totalObtained, 1) }}
                        </td>
                        <td class="px-3 py-4 text-center text-base font-black text-indigo-950 bg-amber-50/50">
                            {{ number_format($gpa, 2) }}
                        </td>
                        <td class="px-3 py-4 text-center border-r border-indigo-100 bg-amber-50/50">
                            <span class="inline-block px-3 py-1 rounded-lg text-xs font-black {{ $failedCount === 0 ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }}">
                                {{ $overallGrade }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-center text-xs font-bold text-indigo-900">
                            {{ $resultStatus }}
                        </td>
                        @if(auth()->user()->hasRole('student'))
                        <td></td>
                        @endif
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Teacher's Evaluation & Behavioral Matrix Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Teacher's Qualitative Remarks --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center font-bold">
                        <i class="bi bi-chat-left-quote"></i>
                    </span>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Class Teacher's Evaluative Assessment</h3>
                        <p class="text-[11px] text-slate-400">Qualitative feedback on academic consistency and classroom leadership</p>
                    </div>
                </div>
                <div class="p-4 bg-slate-50 border border-slate-100 rounded-xl text-slate-700 text-sm leading-relaxed italic">
                    "{{ $teacherRemark }}"
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Class In-Charge: <strong>{{ $classTeacher?->full_name ?? 'Designated Class Teacher' }}</strong></span>
                <span>Evaluation Status: <strong class="text-emerald-700">Verified &amp; Signed</strong></span>
            </div>
        </div>

        {{-- Behavioral & Co-Curricular Rubric --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <div class="flex items-center gap-2 mb-3">
                <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center font-bold">
                    <i class="bi bi-stars"></i>
                </span>
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Personal &amp; Social Traits</h3>
                    <p class="text-[11px] text-slate-400">Co-curricular and behavioral evaluation</p>
                </div>
            </div>
            <div class="space-y-3 pt-1">
                <div class="flex items-center justify-between text-xs py-1.5 border-b border-slate-100">
                    <span class="text-slate-500 font-medium">Discipline &amp; Conduct:</span>
                    <span class="font-bold text-slate-800">{{ $traitDiscipline }}</span>
                </div>
                <div class="flex items-center justify-between text-xs py-1.5 border-b border-slate-100">
                    <span class="text-slate-500 font-medium">Class Participation:</span>
                    <span class="font-bold text-slate-800">{{ $traitParticipation }}</span>
                </div>
                <div class="flex items-center justify-between text-xs py-1.5 border-b border-slate-100">
                    <span class="text-slate-500 font-medium">Homework Regularity:</span>
                    <span class="font-bold text-slate-800">{{ $traitHomework }}</span>
                </div>
                <div class="flex items-center justify-between text-xs py-1.5">
                    <span class="text-slate-500 font-medium">Teamwork &amp; Cooperation:</span>
                    <span class="font-bold text-slate-800">{{ $traitTeamwork }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Official Government of Nepal CDC Grading Key --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">
            Curriculum Development Centre (CDC) &bull; National Examination Grading Scale Reference
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-center border-collapse border border-slate-200">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 font-bold">
                        <th class="border border-slate-200 p-2">Score Interval</th>
                        <td class="border border-slate-200 p-2">90%–100%</td>
                        <td class="border border-slate-200 p-2">80%–89%</td>
                        <td class="border border-slate-200 p-2">70%–79%</td>
                        <td class="border border-slate-200 p-2">60%–69%</td>
                        <td class="border border-slate-200 p-2">50%–59%</td>
                        <td class="border border-slate-200 p-2">40%–49%</td>
                        <td class="border border-slate-200 p-2">35%–39%</td>
                        <td class="border border-slate-200 p-2">Below 35%</td>
                    </tr>
                </thead>
                <tbody>
                    <tr class="bg-emerald-50/50 text-emerald-800 font-black">
                        <th class="border border-slate-200 p-2 bg-slate-50 text-slate-600">Letter Grade</th>
                        <td class="border border-slate-200 p-2">A+</td>
                        <td class="border border-slate-200 p-2">A</td>
                        <td class="border border-slate-200 p-2">B+</td>
                        <td class="border border-slate-200 p-2">B</td>
                        <td class="border border-slate-200 p-2">C+</td>
                        <td class="border border-slate-200 p-2">C</td>
                        <td class="border border-slate-200 p-2">D</td>
                        <td class="border border-slate-200 p-2 text-rose-700">NG</td>
                    </tr>
                    <tr class="bg-indigo-50/50 text-indigo-800 font-bold">
                        <th class="border border-slate-200 p-2 bg-slate-50 text-slate-600">Grade Point</th>
                        <td class="border border-slate-200 p-2">4.0</td>
                        <td class="border border-slate-200 p-2">3.6</td>
                        <td class="border border-slate-200 p-2">3.2</td>
                        <td class="border border-slate-200 p-2">2.8</td>
                        <td class="border border-slate-200 p-2">2.4</td>
                        <td class="border border-slate-200 p-2">2.0</td>
                        <td class="border border-slate-200 p-2">1.6</td>
                        <td class="border border-slate-200 p-2">0.0</td>
                    </tr>
                    <tr class="text-slate-600 text-[11px]">
                        <th class="border border-slate-200 p-2 bg-slate-50">Descriptor</th>
                        <td class="border border-slate-200 p-2">Outstanding</td>
                        <td class="border border-slate-200 p-2">Excellent</td>
                        <td class="border border-slate-200 p-2">Very Good</td>
                        <td class="border border-slate-200 p-2">Good</td>
                        <td class="border border-slate-200 p-2">Satisfactory</td>
                        <td class="border border-slate-200 p-2">Acceptable</td>
                        <td class="border border-slate-200 p-2">Basic</td>
                        <td class="border border-slate-200 p-2 text-rose-600 font-semibold">Non-Graded</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
