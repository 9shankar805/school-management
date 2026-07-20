@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <nav class="text-xs text-slate-400 mb-4">
            <a href="{{ route('lesson-plans.index') }}" class="hover:text-indigo-600">Lesson Plans</a>
            <span class="mx-1">/</span>
            <span class="text-slate-600">{{ $lessonPlan->title }}</span>
        </nav>

        @include('session-messages')

        <div class="max-w-4xl space-y-5">
            {{-- Header card --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <div class="flex flex-wrap justify-between items-start gap-4">
                    <div>
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <h1 class="text-xl font-bold text-slate-800">{{ $lessonPlan->title }}</h1>
                            <span class="text-[11px] px-2.5 py-0.5 rounded-full font-medium
                                @if($lessonPlan->status === 'completed') bg-emerald-100 text-emerald-700
                                @elseif($lessonPlan->status === 'approved') bg-blue-100 text-blue-700
                                @else bg-amber-100 text-amber-700 @endif">
                                {{ ucfirst($lessonPlan->status) }}
                            </span>
                        </div>
                        <div class="flex flex-wrap gap-4 text-xs text-slate-500 mt-1">
                            <span><i class="bi bi-person me-1"></i>{{ $lessonPlan->teacher->full_name ?? '—' }}</span>
                            <span><i class="bi bi-calendar3 me-1"></i>{{ $lessonPlan->planned_date->format('l, M d Y') }}</span>
                            <span><i class="bi bi-clock me-1"></i>{{ $lessonPlan->duration_minutes }} min</span>
                            <span><i class="bi bi-book me-1"></i>{{ $lessonPlan->course->course_name ?? '—' }}</span>
                            <span><i class="bi bi-layers me-1"></i>{{ $lessonPlan->schoolClass->class_name ?? '—' }}
                                @if($lessonPlan->section) · {{ $lessonPlan->section->section_name }}@endif
                            </span>
                            @if($lessonPlan->term)<span><i class="bi bi-calendar2-range me-1"></i>{{ $lessonPlan->term->name }}</span>@endif
                        </div>
                    </div>
                    @if(auth()->id() === $lessonPlan->teacher_id)
                    <a href="{{ route('lesson-plans.edit', $lessonPlan->id) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-slate-200 text-sm font-medium rounded-lg hover:bg-slate-50 transition text-slate-700">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                @if($lessonPlan->objectives)
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                    <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Objectives</h3>
                    <p class="text-sm text-slate-700 whitespace-pre-line">{{ $lessonPlan->objectives }}</p>
                </div>
                @endif
                @if($lessonPlan->content)
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                    <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Lesson Content</h3>
                    <p class="text-sm text-slate-700 whitespace-pre-line">{{ $lessonPlan->content }}</p>
                </div>
                @endif
                @if($lessonPlan->teaching_methods)
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                    <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Teaching Methods</h3>
                    <p class="text-sm text-slate-700 whitespace-pre-line">{{ $lessonPlan->teaching_methods }}</p>
                </div>
                @endif
                @if($lessonPlan->resources)
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                    <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Resources &amp; Materials</h3>
                    <p class="text-sm text-slate-700 whitespace-pre-line">{{ $lessonPlan->resources }}</p>
                </div>
                @endif
                @if($lessonPlan->homework_description)
                <div class="bg-amber-50 rounded-2xl border border-amber-100 shadow-sm p-5">
                    <h3 class="text-xs font-semibold text-amber-600 uppercase tracking-wide mb-2"><i class="bi bi-pencil-square me-1"></i>Homework</h3>
                    <p class="text-sm text-slate-700 whitespace-pre-line">{{ $lessonPlan->homework_description }}</p>
                </div>
                @endif
                @if($lessonPlan->notes && auth()->id() === $lessonPlan->teacher_id)
                <div class="bg-slate-50 rounded-2xl border border-slate-200 shadow-sm p-5">
                    <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2"><i class="bi bi-lock me-1"></i>Private Notes</h3>
                    <p class="text-sm text-slate-600 whitespace-pre-line">{{ $lessonPlan->notes }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
