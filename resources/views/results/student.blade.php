@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <nav class="text-xs text-slate-400 mb-4">
            <a href="{{ route('results.index') }}" class="hover:text-indigo-600">Results</a>
            <span class="mx-1">/</span>
            <span class="text-slate-600">{{ $result['student']->full_name }}</span>
        </nav>

        @include('session-messages')

        {{-- Student header --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mb-6 flex flex-wrap items-start gap-5">
            <img src="{{ $result['student']->avatar }}" class="w-16 h-16 rounded-2xl object-cover border-2 border-indigo-100" alt="">
            <div class="flex-1 min-w-0">
                <h1 class="text-xl font-bold text-slate-800">{{ $result['student']->full_name }}</h1>
                <p class="text-sm text-slate-400 mt-0.5">{{ $result['semester']?->semester_name ?? '—' }}</p>
                <div class="flex flex-wrap gap-5 mt-3">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-indigo-600">{{ $result['gpa'] }}</p>
                        <p class="text-[10px] text-slate-400 uppercase tracking-wide">GPA</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-slate-700">{{ round($result['totalMarks'], 1) }}</p>
                        <p class="text-[10px] text-slate-400 uppercase tracking-wide">Total Marks</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-emerald-600">{{ $result['passed'] }}</p>
                        <p class="text-[10px] text-slate-400 uppercase tracking-wide">Passed</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold {{ $result['failed'] > 0 ? 'text-rose-600' : 'text-slate-300' }}">{{ $result['failed'] }}</p>
                        <p class="text-[10px] text-slate-400 uppercase tracking-wide">Failed</p>
                    </div>
                    @if($result['rank'])
                    <div class="text-center">
                        <p class="text-2xl font-bold text-amber-600">#{{ $result['rank'] }}</p>
                        <p class="text-[10px] text-slate-400 uppercase tracking-wide">Class Rank</p>
                    </div>
                    @endif
                </div>
            </div>
            <div class="flex gap-2 flex-shrink-0">
                <a href="{{ route('results.report-card', $result['student']->id) . '?' . http_build_query(request()->query()) }}"
                   target="_blank"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium rounded-lg transition">
                    <i class="bi bi-file-earmark-pdf"></i> Report Card PDF
                </a>
            </div>
        </div>

        {{-- Overall result banner --}}
        <div class="mb-6 p-4 rounded-2xl border {{ $result['failed'] === 0 ? 'bg-emerald-50 border-emerald-200' : 'bg-rose-50 border-rose-200' }}">
            <div class="flex items-center gap-3">
                <i class="bi bi-{{ $result['failed'] === 0 ? 'patch-check-fill text-emerald-500' : 'x-circle-fill text-rose-500' }} text-2xl"></i>
                <div>
                    <p class="font-semibold {{ $result['failed'] === 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                        {{ $result['failed'] === 0 ? 'PASSED — All courses cleared' : 'FAILED — ' . $result['failed'] . ' course(s) below passing grade' }}
                    </p>
                    <p class="text-xs {{ $result['failed'] === 0 ? 'text-emerald-500' : 'text-rose-500' }} mt-0.5">
                        GPA: {{ $result['gpa'] }} · Total: {{ round($result['totalMarks'], 1) }} marks
                    </p>
                </div>
            </div>
        </div>

        {{-- Course-wise result table --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h2 class="text-sm font-semibold text-slate-700">Course-wise Result</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-xs text-slate-400 bg-slate-50 text-left">
                            <th class="px-5 py-3 font-medium">Course</th>
                            <th class="px-4 py-3 font-medium text-center">Full Marks</th>
                            <th class="px-4 py-3 font-medium text-center">Obtained</th>
                            <th class="px-4 py-3 font-medium text-center">Grade</th>
                            <th class="px-4 py-3 font-medium text-center">GPA Point</th>
                            <th class="px-4 py-3 font-medium text-center">Result</th>
                            @if(auth()->user()->hasRole('student'))
                            <th class="px-4 py-3 font-medium text-center">Re-Exam</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($result['courses'] as $c)
                        <tr class="hover:bg-slate-50 {{ !$c['passed'] ? 'bg-rose-50/30' : '' }}">
                            <td class="px-5 py-3 font-medium text-slate-800">{{ $c['course']?->course_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-center text-slate-500">{{ $c['full_marks'] ?? '—' }}</td>
                            <td class="px-4 py-3 text-center font-semibold {{ $c['passed'] ? 'text-slate-700' : 'text-rose-600' }}">
                                {{ $c['final_marks'] }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold
                                    {{ $c['passed'] ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                    {{ $c['grade'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center font-semibold text-indigo-600">{{ $c['point'] }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-[10px] font-bold {{ $c['passed'] ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $c['passed'] ? 'PASS' : 'FAIL' }}
                                </span>
                            </td>
                            @if(auth()->user()->hasRole('student'))
                            <td class="px-4 py-3 text-center">
                                @if(!$c['passed'])
                                <a href="{{ route('re-exam.create') }}" class="text-xs text-indigo-500 hover:underline">Apply</a>
                                @else
                                <span class="text-slate-300 text-xs">—</span>
                                @endif
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(!empty($result['note']))
            <div class="px-5 py-3 border-t border-slate-100 text-xs text-slate-400">
                <i class="bi bi-info-circle me-1"></i>{{ $result['note'] ?? '' }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
