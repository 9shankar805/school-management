@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">
        @include('layouts.left-menu')
    </div>

    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Exam Controller Dashboard</h1>
                <p class="text-slate-400 text-sm mt-0.5">{{ now()->format('l, F j, Y') }}</p>
            </div>
            <div class="flex gap-2">
                @can('create exams')
                <a href="{{ route('exam.create.show') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                    <i class="bi bi-file-plus"></i> Create Exam
                </a>
                @endcan
                @can('view exams')
                <a href="{{ route('exam.list.show') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 text-sm font-medium rounded-lg hover:bg-slate-50 transition text-slate-700">
                    <i class="bi bi-list-ul"></i> All Exams
                </a>
                @endcan
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Total Exams</p>
                    <span class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 text-sm"><i class="bi bi-file-earmark-text"></i></span>
                </div>
                <p class="text-3xl font-bold text-slate-800">{{ $totalExams }}</p>
                <p class="mt-1 text-xs text-indigo-600">This session</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Upcoming</p>
                    <span class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600 text-sm"><i class="bi bi-calendar-event"></i></span>
                </div>
                <p class="text-3xl font-bold text-amber-600">{{ $upcomingExams->count() }}</p>
                <p class="mt-1 text-xs text-amber-500">Next 30 days</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Students</p>
                    <span class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 text-sm"><i class="bi bi-people"></i></span>
                </div>
                <p class="text-3xl font-bold text-slate-800">{{ number_format($studentCount) }}</p>
                <p class="mt-1 text-xs text-blue-500">Enrolled this session</p>
            </div>
        </div>

        {{-- Upcoming Exams + Past Exams --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 flex justify-between items-center">
                    <p class="text-sm font-semibold text-slate-700"><i class="bi bi-clock me-1 text-amber-500"></i>Upcoming Exams</p>
                    <span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-medium">{{ $upcomingExams->count() }}</span>
                </div>
                @if($upcomingExams->count())
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-slate-400 bg-slate-50">
                                <th class="px-5 py-2.5 font-medium">Exam</th>
                                <th class="px-5 py-2.5 font-medium">Course</th>
                                <th class="px-5 py-2.5 font-medium">Start</th>
                                <th class="px-5 py-2.5 font-medium">End</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($upcomingExams as $exam)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3 font-medium text-slate-700">{{ $exam->exam_name }}</td>
                                <td class="px-5 py-3 text-slate-500">{{ $exam->course?->name ?? '—' }}</td>
                                <td class="px-5 py-3 text-slate-700">{{ \Carbon\Carbon::parse($exam->start_date)->format('M d, Y') }}</td>
                                <td class="px-5 py-3 text-slate-400">{{ $exam->end_date ? \Carbon\Carbon::parse($exam->end_date)->format('M d') : '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-sm text-slate-400 text-center py-10">No upcoming exams.</p>
                @endif
            </div>

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100">
                    <p class="text-sm font-semibold text-slate-700"><i class="bi bi-check-circle me-1 text-emerald-500"></i>Recently Completed</p>
                </div>
                @if($pastExams->count())
                <div class="divide-y divide-slate-50">
                    @foreach($pastExams as $exam)
                    <div class="px-5 py-3 flex justify-between items-center">
                        <div>
                            <p class="text-sm font-medium text-slate-700">{{ $exam->exam_name }}</p>
                            <p class="text-xs text-slate-400">{{ $exam->course?->name ?? '—' }}</p>
                        </div>
                        <span class="text-xs text-emerald-700 bg-emerald-50 px-2 py-1 rounded-lg">
                            {{ \Carbon\Carbon::parse($exam->end_date)->format('M d') }}
                        </span>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-400 text-center py-8">No completed exams yet.</p>
                @endif
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mt-6">
            <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-lightning-fill me-1 text-amber-500"></i>Quick Actions</p>
            <div class="flex flex-wrap gap-2">
                @can('view grading systems')
                <a href="{{ route('exam.grade.system.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-indigo-50 hover:text-indigo-700 text-slate-700 rounded-lg text-sm font-medium transition">
                    <i class="bi bi-bar-chart-steps"></i> Grading Systems
                </a>
                @endcan
                @can('save marks')
                <a href="{{ route('course.mark.create') }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 text-slate-700 rounded-lg text-sm font-medium transition">
                    <i class="bi bi-pencil-square"></i> Enter Marks
                </a>
                @endcan
                @can('view marks')
                <a href="{{ route('course.mark.show') }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-blue-50 hover:text-blue-700 text-slate-700 rounded-lg text-sm font-medium transition">
                    <i class="bi bi-clipboard-data"></i> View Marks
                </a>
                @endcan
            </div>
        </div>

    </div>
</div>
@endsection
