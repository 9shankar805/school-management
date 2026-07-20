@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <nav class="text-xs text-slate-400 mb-4">
            <a href="{{ route('homework.index') }}" class="hover:text-indigo-600">Homework</a>
            <span class="mx-1">/</span>
            <span class="text-slate-600">{{ $homework->title }}</span>
        </nav>

        @include('session-messages')

        {{-- Header --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mb-6">
            <div class="flex flex-wrap justify-between items-start gap-4">
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <h1 class="text-xl font-bold text-slate-800">{{ $homework->title }}</h1>
                        <span class="text-[11px] px-2.5 py-0.5 rounded-full font-medium
                            {{ $homework->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ ucfirst($homework->status) }}
                        </span>
                    </div>
                    <div class="flex flex-wrap gap-4 text-xs text-slate-500 mt-1">
                        <span><i class="bi bi-book me-1"></i>{{ $homework->course->course_name ?? '—' }}</span>
                        <span><i class="bi bi-layers me-1"></i>{{ $homework->schoolClass->class_name ?? '—' }}
                            @if($homework->section) · {{ $homework->section->section_name }}@endif
                        </span>
                        <span><i class="bi bi-calendar-event me-1"></i>Due: {{ $homework->due_date->format('M d, Y') }}</span>
                        <span><i class="bi bi-award me-1"></i>{{ $homework->total_marks }} marks total</span>
                        <span><i class="bi bi-people me-1"></i>{{ $homework->submissions->count() }} submitted</span>
                    </div>
                    @if($homework->description)
                    <p class="mt-3 text-sm text-slate-600">{{ $homework->description }}</p>
                    @endif
                </div>
                <div class="flex gap-2">
                    @if($homework->file_path)
                    <a href="{{ asset('storage/' . $homework->file_path) }}" target="_blank"
                       class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-slate-200 text-sm font-medium rounded-lg hover:bg-slate-50 transition text-slate-700">
                        <i class="bi bi-download"></i> Attachment
                    </a>
                    @endif
                    <form action="{{ route('homework.toggle-status', $homework->id) }}" method="POST">
                        @csrf
                        <button class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-slate-200 text-sm font-medium rounded-lg hover:bg-slate-50 transition text-slate-700">
                            <i class="bi bi-{{ $homework->status === 'active' ? 'lock' : 'unlock' }}"></i>
                            {{ $homework->status === 'active' ? 'Close Submissions' : 'Reopen' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Submissions --}}
        <h2 class="text-base font-semibold text-slate-700 mb-3">
            Submissions
            <span class="ml-2 text-xs font-normal text-slate-400">{{ $homework->submissions->count() }} total</span>
        </h2>

        @if($homework->submissions->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-10 text-center">
            <i class="bi bi-inbox text-5xl text-slate-200"></i>
            <p class="mt-3 text-slate-400 text-sm">No submissions yet.</p>
        </div>
        @else
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500">Student</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500">Submitted</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500">Status</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500">Marks</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($homework->submissions as $sub)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <img src="{{ $sub->student->avatar }}" class="w-7 h-7 rounded-full object-cover" alt="">
                                <span class="font-medium text-slate-700">{{ $sub->student->full_name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-slate-500 text-xs">
                            {{ $sub->submitted_at?->format('M d, Y H:i') ?? '—' }}
                        </td>
                        <td class="px-5 py-3">
                            <span class="text-[11px] px-2 py-0.5 rounded-full font-medium
                                {{ $sub->status === 'graded' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700' }}">
                                {{ ucfirst($sub->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-slate-700 font-medium">
                            {{ $sub->marks_obtained !== null ? $sub->marks_obtained . '/' . $homework->total_marks : '—' }}
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2 justify-end">
                                @if($sub->file_path)
                                <a href="{{ asset('storage/' . $sub->file_path) }}" target="_blank"
                                   class="text-xs text-indigo-600 hover:underline"><i class="bi bi-download me-0.5"></i>File</a>
                                @endif
                                <button onclick="openGradeModal({{ $sub->id }}, {{ $sub->marks_obtained ?? 'null' }}, '{{ addslashes($sub->teacher_feedback ?? '') }}')"
                                        class="text-xs px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-lg transition">
                                    {{ $sub->status === 'graded' ? 'Re-grade' : 'Grade' }}
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

{{-- Grade Modal --}}
<div id="gradeModal" class="fixed inset-0 z-50 hidden bg-black/40 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
        <h2 class="text-base font-semibold text-slate-700 mb-4">Grade Submission</h2>
        <form id="gradeForm" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="text-xs font-medium text-slate-600 block mb-1">
                    Marks Obtained <span class="text-rose-400">*</span>
                    <span class="text-slate-400">(out of {{ $homework->total_marks }})</span>
                </label>
                <input type="number" id="gradeMarks" name="marks_obtained" required min="0" max="{{ $homework->total_marks }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
            <div>
                <label class="text-xs font-medium text-slate-600 block mb-1">Feedback</label>
                <textarea id="gradeFeedback" name="teacher_feedback" rows="3"
                          class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                          placeholder="Optional comments for student…"></textarea>
            </div>
            <div class="flex gap-3 pt-1">
                <button type="submit" class="flex-1 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">Save Grade</button>
                <button type="button" onclick="document.getElementById('gradeModal').classList.add('hidden')"
                        class="flex-1 py-2 bg-white border border-slate-200 text-slate-700 rounded-lg text-sm hover:bg-slate-50 transition">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openGradeModal(subId, marks, feedback) {
    document.getElementById('gradeForm').action = '/homework/submissions/' + subId + '/grade';
    document.getElementById('gradeMarks').value = marks || '';
    document.getElementById('gradeFeedback').value = feedback || '';
    document.getElementById('gradeModal').classList.remove('hidden');
}
</script>
@endsection
