@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex justify-between items-start mb-7 gap-4 flex-wrap">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Academic Transcript</h1>
                <p class="text-slate-400 text-sm mt-0.5">{{ $student->full_name }} · {{ $session?->session_name }}</p>
            </div>
            <a href="{{ route('results.transcript.pdf', $student->id) }}" target="_blank"
               class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-sm font-medium transition flex items-center gap-1.5">
                <i class="bi bi-file-earmark-pdf"></i> Download PDF
            </a>
        </div>

        {{-- Student info card --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-6 flex items-center gap-4">
            <img src="{{ $student->avatar }}" class="w-16 h-16 rounded-full object-cover flex-shrink-0" alt="">
            <div class="flex-1 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div><p class="text-xs text-slate-400">Name</p><p class="font-semibold text-slate-800">{{ $student->full_name }}</p></div>
                <div><p class="text-xs text-slate-400">Session</p><p class="font-semibold text-slate-800">{{ $session?->session_name }}</p></div>
                <div><p class="text-xs text-slate-400">CGPA</p><p class="text-2xl font-bold text-indigo-600">{{ $cgpaData['cgpa'] }}</p></div>
                <div><p class="text-xs text-slate-400">Total Courses</p><p class="font-semibold text-slate-800">{{ $cgpaData['total_courses'] }}</p></div>
            </div>
        </div>

        {{-- Per-semester results --}}
        @foreach($semesterResults as $result)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mb-5">
            <div class="px-5 py-3 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <p class="text-sm font-semibold text-slate-700">{{ $result['semester']?->semester_name ?? 'Semester' }}</p>
                <span class="text-sm font-bold text-indigo-600">GPA: {{ $result['gpa'] }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-xs text-slate-400 text-left">
                        <th class="px-5 py-2.5 font-medium">Course</th>
                        <th class="px-4 py-2.5 font-medium text-center">Final Marks</th>
                        <th class="px-4 py-2.5 font-medium text-center">Grade</th>
                        <th class="px-4 py-2.5 font-medium text-center">Points</th>
                        <th class="px-4 py-2.5 font-medium text-center">Result</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($result['courses'] as $c)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-2.5 font-medium text-slate-700">{{ $c['course']?->course_name ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-center">{{ $c['final_marks'] }}</td>
                            <td class="px-4 py-2.5 text-center font-bold {{ $c['passed'] ? 'text-emerald-600' : 'text-rose-600' }}">{{ $c['grade'] }}</td>
                            <td class="px-4 py-2.5 text-center text-indigo-600 font-semibold">{{ $c['point'] }}</td>
                            <td class="px-4 py-2.5 text-center">
                                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold {{ $c['passed'] ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                    {{ $c['passed'] ? 'PASS' : 'FAIL' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
