@extends('layouts.app')
@section('title', 'Exam Details — ' . $exam->exam_name)

@section('content')
<div class="flex-1 overflow-y-auto bg-slate-50 dark:bg-slate-950 min-h-screen p-6 lg:p-8">
    <div class="max-w-5xl mx-auto space-y-6">

        {{-- Header Breadcrumb & Actions --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('exams.index') }}" class="w-10 h-10 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl flex items-center justify-center text-slate-500 hover:text-indigo-600 transition shadow-sm">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">{{ $exam->exam_name }}</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Examination Overview & Details</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @can('edit exams')
                <a href="{{ route('exams.edit', $exam->id) }}" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5 shadow-sm">
                    <i class="bi bi-pencil-square"></i> Edit Exam
                </a>
                @endcan
            </div>
        </div>

        {{-- Details Card --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-slate-800">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Class / Grade</span>
                    <span class="text-base font-black text-slate-800 dark:text-slate-100 mt-1 block">
                        {{ $exam->schoolClass->class_name ?? 'All Classes' }}
                    </span>
                </div>
                <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-slate-800">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Start Date</span>
                    <span class="text-base font-black text-slate-800 dark:text-slate-100 mt-1 block">
                        {{ $exam->start_date ? \Carbon\Carbon::parse($exam->start_date)->format('M d, Y') : 'N/A' }}
                    </span>
                </div>
                <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-slate-800">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">End Date</span>
                    <span class="text-base font-black text-slate-800 dark:text-slate-100 mt-1 block">
                        {{ $exam->end_date ? \Carbon\Carbon::parse($exam->end_date)->format('M d, Y') : 'N/A' }}
                    </span>
                </div>
            </div>

            @if($exam->description)
            <div class="space-y-2">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Description / Instructions</h3>
                <div class="p-4 bg-slate-50 dark:bg-slate-800/40 rounded-2xl text-sm text-slate-700 dark:text-slate-300 leading-relaxed">
                    {!! nl2br(e($exam->description)) !!}
                </div>
            </div>
            @endif
        </div>

    </div>
</div>
@endsection
