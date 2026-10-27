@extends('layouts.app')
@section('title', 'Class In-Charge — Consolidated Marksheet')

@section('content')
<div class="flex-1 p-6 lg:p-8 overflow-auto">

    <nav class="text-xs text-slate-400 mb-5 flex items-center gap-1.5">
        <a href="{{ route('home') }}" class="hover:text-indigo-600">Home</a>
        <i class="bi bi-chevron-right text-[9px]"></i>
        <a href="{{ route('class-incharge.dashboard') }}" class="hover:text-indigo-600">Class In-Charge</a>
        <i class="bi bi-chevron-right text-[9px]"></i>
        <span class="text-slate-800 font-bold">Consolidated Marksheet</span>
    </nav>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-extrabold text-slate-800 dark:text-slate-100">
                Consolidated Class Marksheet & Remarks
            </h1>
            <p class="text-xs text-slate-500">
                {{ $assignment->schoolClass?->class_name }} — Section {{ preg_replace('/^Section\s+/i', '', $assignment->section?->section_name ?? 'A') }} &middot; Cross-Subject Terminal Ledger
            </p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Exam selector dropdown --}}
            <form method="GET" action="{{ route('class-incharge.gradebook') }}" class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-500 uppercase">Exam:</label>
                <select name="exam_id" onchange="this.form.submit()" class="text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-amber-500 focus:outline-none shadow-sm">
                    @foreach($exams as $ex)
                    <option value="{{ $ex->id }}" {{ $examId == $ex->id ? 'selected' : '' }}>
                        {{ $ex->name }}
                    </option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('class-incharge.dashboard') }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold hover:bg-slate-200 transition">
                ← Overview
            </a>
        </div>
    </div>

    {{-- Marksheet Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs min-w-[700px]">
                <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left font-bold w-12">Roll</th>
                        <th class="px-4 py-3 text-left font-bold min-w-[180px]">Student Name</th>
                        @foreach($courses as $c)
                        <th class="px-3 py-3 text-center font-bold">{{ $c->course_code ?: substr($c->course_name, 0, 8) }}</th>
                        @endforeach
                        <th class="px-4 py-3 text-center font-bold">Total</th>
                        <th class="px-4 py-3 text-center font-bold">GPA</th>
                        <th class="px-4 py-3 text-center font-bold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($students as $p)
                    @php
                        $stu = $p->student;
                        $stuMarks = $marks->get($p->student_id, collect());
                        $totalMarks = $stuMarks->sum('marks');
                    @endphp
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40 transition">
                        <td class="px-4 py-3 font-bold text-slate-700 dark:text-slate-200">#{{ $p->roll_no ?? '—' }}</td>
                        <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-100">
                            {{ $stu?->full_name }}
                        </td>
                        @foreach($courses as $c)
                        @php
                            $m = $stuMarks->firstWhere('course_id', $c->id);
                        @endphp
                        <td class="px-3 py-3 text-center font-mono font-medium {{ $m ? 'text-slate-800 dark:text-slate-100' : 'text-slate-300 dark:text-slate-600' }}">
                            {{ $m ? $m->marks : '—' }}
                        </td>
                        @endforeach
                        <td class="px-4 py-3 text-center font-bold text-slate-800 dark:text-slate-100">
                            {{ $totalMarks ?: '—' }}
                        </td>
                        <td class="px-4 py-3 text-center font-bold text-indigo-600">
                            {{ $totalMarks > 0 ? number_format(min(4.0, max(1.6, ($totalMarks / (max($courses->count(), 1) * 100)) * 4)), 2) : '—' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Passed
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ 5 + $courses->count() }}" class="px-4 py-8 text-center text-slate-400">No marks recorded yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
