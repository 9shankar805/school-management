@extends('layouts.app')
@section('content')
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Class Result Sheet</h1>
                <p class="text-slate-400 text-sm mt-0.5">
                    {{ $class?->class_name }} · {{ $section?->section_name ?? 'All Sections' }} · {{ $semester?->semester_name }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('results.class.report-cards', array_merge(request()->query(), ['stream' => 1])) }}"
                   data-no-loader
                   target="_blank"
                   class="btn px-4 py-2 text-white rounded-xl text-sm font-bold transition flex items-center gap-1.5 shadow-sm"
                   style="background-color: #4f46e5 !important; color: #ffffff !important;"
                   title="Print individual official marksheets / report cards for all students in this class on separate A4 pages">
                    <i class="bi bi-printer-fill"></i> Print All Marksheets (Individual A4)
                </a>
                <a href="{{ route('results.class.pdf', request()->query()) }}"
                   data-no-loader
                   class="btn px-4 py-2 text-white rounded-xl text-sm font-semibold transition flex items-center gap-1.5 shadow-sm"
                   style="background-color: #e11d48 !important; color: #ffffff !important;">
                    <i class="bi bi-download"></i> Sheet PDF
                </a>
                <a href="{{ route('results.class.excel', request()->query()) }}"
                   data-no-loader
                   class="btn px-4 py-2 text-white rounded-xl text-sm font-semibold transition flex items-center gap-1.5 shadow-sm"
                   style="background-color: #0d9488 !important; color: #ffffff !important;">
                    <i class="bi bi-file-earmark-excel"></i> Excel
                </a>
            </div>
        </div>

        @if($results->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-10 text-center text-slate-400">
            <i class="bi bi-clipboard-x text-5xl mb-3 block"></i>
            <p class="text-sm">No results found. Ensure final marks have been submitted for this class and semester.</p>
        </div>
        @else
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-xs text-slate-400 bg-slate-50 text-left">
                        <th class="px-4 py-3 font-medium">Rank</th>
                        <th class="px-4 py-3 font-medium">Student</th>
                        @foreach($courses as $course)
                        <th class="px-3 py-3 font-medium text-center">{{ $course?->course_name }}<br><span class="font-normal text-slate-300">FM / Grade</span></th>
                        @endforeach
                        <th class="px-4 py-3 font-medium text-center">GPA</th>
                        <th class="px-4 py-3 font-medium text-center">Total</th>
                        <th class="px-4 py-3 font-medium text-center">Result</th>
                        <th class="px-4 py-3 font-medium">Action</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($results as $row)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-center">
                                @if($row['rank'] <= 3)
                                <span class="text-lg">{{ ['🥇','🥈','🥉'][$row['rank']-1] }}</span>
                                @else
                                <span class="text-slate-500 font-semibold">{{ $row['rank'] }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <img src="{{ $row['student']?->avatar }}" class="w-7 h-7 rounded-full object-cover flex-shrink-0" alt="">
                                    <a href="{{ route('results.student', $row['student']->id) . '?' . http_build_query(request()->query()) }}"
                                       class="font-medium text-slate-700 hover:text-indigo-600">{{ $row['student']?->full_name }}</a>
                                </div>
                            </td>
                            @foreach($row['courses'] as $c)
                            <td class="px-3 py-3 text-center">
                                <p class="font-semibold {{ $c['passed'] ? 'text-slate-700' : 'text-rose-600' }}">{{ $c['final_marks'] }}</p>
                                <p class="text-xs {{ $c['passed'] ? 'text-emerald-600' : 'text-rose-400' }}">{{ $c['grade'] }}</p>
                            </td>
                            @endforeach
                            <td class="px-4 py-3 text-center font-bold text-indigo-600">{{ $row['gpa'] }}</td>
                            <td class="px-4 py-3 text-center text-slate-600">{{ round($row['totalMarks'], 1) }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold {{ $row['failed'] === 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                    {{ $row['failed'] === 0 ? 'PASS' : 'FAIL' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('results.report-card', $row['student']->id) . '?' . http_build_query(array_merge(request()->query(), ['stream' => '1'])) }}"
                                   target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:text-rose-300 transition" title="View / Print Marksheet">
                                    <i class="bi bi-file-earmark-person"></i> Report Card
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
