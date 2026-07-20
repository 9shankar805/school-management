@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <nav class="text-xs text-slate-400 mb-4">
            <a href="{{ route('projects.index') }}" class="hover:text-indigo-600">My Projects</a>
            <span class="mx-1">/</span>
            <span class="text-slate-600">{{ $project->title }}</span>
        </nav>

        @include('session-messages')

        <div class="max-w-3xl space-y-5">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <h1 class="text-xl font-bold text-slate-800">{{ $project->title }}</h1>
                    <span class="text-[11px] px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-700">{{ ucfirst($project->type) }}</span>
                </div>
                <div class="flex flex-wrap gap-4 text-xs text-slate-500 mb-3">
                    <span><i class="bi bi-book me-1"></i>{{ $project->course->course_name ?? '—' }}</span>
                    <span><i class="bi bi-person me-1"></i>{{ $project->teacher->full_name ?? '—' }}</span>
                    <span><i class="bi bi-calendar-range me-1"></i>{{ $project->start_date->format('M d') }} – <strong class="{{ $project->due_date->isPast() ? 'text-rose-600' : 'text-slate-700' }}">{{ $project->due_date->format('M d, Y') }}</strong></span>
                    <span><i class="bi bi-award me-1"></i>{{ $project->total_marks }} marks</span>
                </div>
                @if($project->description)
                <p class="text-sm text-slate-600">{{ $project->description }}</p>
                @endif
                @if($project->file_path)
                <a href="{{ asset('storage/' . $project->file_path) }}" target="_blank"
                   class="mt-3 inline-flex items-center gap-1.5 px-3 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-medium transition">
                    <i class="bi bi-download"></i> Download Brief
                </a>
                @endif
            </div>

            @if($mySubmission?->status === 'graded')
            <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5">
                <h2 class="text-sm font-semibold text-emerald-700 mb-2"><i class="bi bi-patch-check me-1"></i>Graded</h2>
                <div class="flex gap-6">
                    <div>
                        <p class="text-3xl font-bold text-emerald-700">{{ $mySubmission->marks_obtained }}</p>
                        <p class="text-xs text-slate-500">out of {{ $project->total_marks }}</p>
                    </div>
                    @if($mySubmission->teacher_feedback)
                    <div class="flex-1">
                        <p class="text-xs font-medium text-slate-500 mb-1">Feedback</p>
                        <p class="text-sm text-slate-700">{{ $mySubmission->teacher_feedback }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            @if($mySubmission)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-slate-600 mb-2"><i class="bi bi-check2-circle me-1 text-blue-500"></i>Your Submission</h2>
                <p class="text-xs text-slate-400 mb-2">{{ $mySubmission->submitted_at?->format('M d, Y H:i') }}</p>
                @if($mySubmission->description)<p class="text-sm text-slate-600 mb-3">{{ $mySubmission->description }}</p>@endif
                @if($mySubmission->file_path)
                <a href="{{ asset('storage/' . $mySubmission->file_path) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-xs font-medium transition">
                    <i class="bi bi-file-earmark"></i> View File
                </a>
                @endif
            </div>
            @endif

            @if($project->status !== 'closed' && $mySubmission?->status !== 'graded')
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <h2 class="text-sm font-semibold text-slate-600 mb-4">
                    <i class="bi bi-upload me-1 text-indigo-500"></i>{{ $mySubmission ? 'Re-submit' : 'Submit Project' }}
                </h2>
                <form action="{{ route('projects.submit', $project->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">File <span class="text-rose-400">*</span></label>
                        <input type="file" name="file" required accept=".pdf,.doc,.docx,.zip,.jpg,.jpeg,.png"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
                        <p class="text-[11px] text-slate-400 mt-1">PDF, Word, ZIP, or image. Max 50 MB.</p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Description / Notes</label>
                        <textarea name="description" rows="3"
                                  class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                  placeholder="Brief summary of your work…"></textarea>
                    </div>
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition">
                        <i class="bi bi-upload me-1"></i> Submit Project
                    </button>
                </form>
            </div>
            @elseif($project->status === 'closed')
            <div class="bg-rose-50 border border-rose-200 rounded-2xl p-5 text-sm text-rose-700">
                <i class="bi bi-lock me-1"></i> Submissions are closed.
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
