@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <nav class="text-xs text-slate-400 mb-4">
            <a href="{{ route('homework.index') }}" class="hover:text-indigo-600">My Homework</a>
            <span class="mx-1">/</span>
            <span class="text-slate-600">{{ $homework->title }}</span>
        </nav>

        @include('session-messages')

        <div class="max-w-3xl space-y-5">

            {{-- Homework details --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <h1 class="text-xl font-bold text-slate-800">{{ $homework->title }}</h1>
                    <span class="text-[11px] px-2.5 py-0.5 rounded-full font-medium
                        {{ $homework->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                        {{ ucfirst($homework->status) }}
                    </span>
                </div>
                <div class="flex flex-wrap gap-4 text-xs text-slate-500 mb-3">
                    <span><i class="bi bi-book me-1"></i>{{ $homework->course->course_name ?? '—' }}</span>
                    <span><i class="bi bi-person me-1"></i>{{ $homework->teacher->full_name ?? '—' }}</span>
                    <span><i class="bi bi-calendar-event me-1"></i>Due: <strong class="{{ $homework->due_date->isPast() ? 'text-rose-600' : 'text-slate-700' }}">{{ $homework->due_date->format('M d, Y') }}</strong></span>
                    <span><i class="bi bi-award me-1"></i>{{ $homework->total_marks }} marks</span>
                </div>
                @if($homework->description)
                <p class="text-sm text-slate-600 mb-3">{{ $homework->description }}</p>
                @endif
                @if($homework->file_path)
                <a href="{{ asset('storage/' . $homework->file_path) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-medium transition">
                    <i class="bi bi-download"></i> Download Attachment
                </a>
                @endif
            </div>

            {{-- Submission result if graded --}}
            @if($mySubmission?->status === 'graded')
            <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5">
                <h2 class="text-sm font-semibold text-emerald-700 mb-2"><i class="bi bi-patch-check me-1"></i>Graded</h2>
                <div class="flex gap-6 text-sm mb-2">
                    <div>
                        <p class="text-3xl font-bold text-emerald-700">{{ $mySubmission->marks_obtained }}</p>
                        <p class="text-xs text-slate-500">out of {{ $homework->total_marks }}</p>
                    </div>
                    <div class="flex-1">
                        @if($mySubmission->teacher_feedback)
                        <p class="text-xs font-medium text-slate-500 mb-1">Teacher Feedback</p>
                        <p class="text-sm text-slate-700">{{ $mySubmission->teacher_feedback }}</p>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- My submission or submit form --}}
            @if($mySubmission)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-slate-600 mb-3"><i class="bi bi-check2-circle me-1 text-blue-500"></i>Your Submission</h2>
                <div class="flex flex-wrap gap-4 text-xs text-slate-500 mb-3">
                    <span><i class="bi bi-clock me-1"></i>Submitted: {{ $mySubmission->submitted_at?->format('M d, Y H:i') }}</span>
                    <span class="text-[11px] px-2 py-0.5 rounded-full font-medium
                        {{ $mySubmission->status === 'graded' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700' }}">
                        {{ ucfirst($mySubmission->status) }}
                    </span>
                </div>
                @if($mySubmission->remarks)
                <p class="text-sm text-slate-600 mb-3">{{ $mySubmission->remarks }}</p>
                @endif
                @if($mySubmission->file_path)
                <a href="{{ asset('storage/' . $mySubmission->file_path) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-xs font-medium transition">
                    <i class="bi bi-file-earmark"></i> View Submitted File
                </a>
                @endif
            </div>

            @if($homework->status === 'active' && $mySubmission->status !== 'graded')
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-slate-600 mb-3">Re-submit</h2>
                <form action="{{ route('homework.submit', $homework->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">New File <span class="text-rose-400">*</span></label>
                        <input type="file" name="file" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.zip"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Note to teacher</label>
                        <textarea name="remarks" rows="2"
                                  class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></textarea>
                    </div>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">
                        Re-submit
                    </button>
                </form>
            </div>
            @endif

            @elseif($homework->status === 'active')
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <h2 class="text-sm font-semibold text-slate-600 mb-4"><i class="bi bi-upload me-1 text-indigo-500"></i>Submit Your Work</h2>
                <form action="{{ route('homework.submit', $homework->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">File <span class="text-rose-400">*</span></label>
                        <input type="file" name="file" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.zip"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
                        <p class="text-[11px] text-slate-400 mt-1">PDF, Word, image, or ZIP. Max 20 MB.</p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Remarks (optional)</label>
                        <textarea name="remarks" rows="2"
                                  class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                  placeholder="Note to your teacher…"></textarea>
                    </div>
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition">
                        <i class="bi bi-upload me-1"></i> Submit Homework
                    </button>
                </form>
            </div>
            @else
            <div class="bg-rose-50 border border-rose-200 rounded-2xl p-5 text-sm text-rose-700">
                <i class="bi bi-lock me-1"></i> Submissions are closed for this homework.
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
