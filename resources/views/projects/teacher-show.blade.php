@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <nav class="text-xs text-slate-400 mb-4">
            <a href="{{ route('projects.index') }}" class="hover:text-indigo-600">Projects</a>
            <span class="mx-1">/</span>
            <span class="text-slate-600">{{ $project->title }}</span>
        </nav>

        @include('session-messages')

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mb-6">
            <div class="flex flex-wrap justify-between items-start gap-4">
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <h1 class="text-xl font-bold text-slate-800">{{ $project->title }}</h1>
                        <span class="text-[11px] px-2.5 py-0.5 rounded-full font-medium bg-purple-100 text-purple-700">{{ ucfirst($project->type) }}</span>
                    </div>
                    <div class="flex flex-wrap gap-4 text-xs text-slate-500 mt-1">
                        <span><i class="bi bi-book me-1"></i>{{ $project->course->course_name ?? '—' }}</span>
                        <span><i class="bi bi-layers me-1"></i>{{ $project->schoolClass->class_name ?? '—' }}
                            @if($project->section) · {{ $project->section->section_name }}@endif
                        </span>
                        <span><i class="bi bi-calendar-range me-1"></i>{{ $project->start_date->format('M d') }} – {{ $project->due_date->format('M d, Y') }}</span>
                        <span><i class="bi bi-award me-1"></i>{{ $project->total_marks }} marks</span>
                        <span><i class="bi bi-people me-1"></i>{{ $project->submissions->count() }} submissions</span>
                    </div>
                    @if($project->description)
                    <p class="mt-3 text-sm text-slate-600">{{ $project->description }}</p>
                    @endif
                </div>
                @if($project->file_path)
                <a href="{{ asset('storage/' . $project->file_path) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-slate-200 text-sm font-medium rounded-lg hover:bg-slate-50 transition text-slate-700">
                    <i class="bi bi-download"></i> Brief
                </a>
                @endif
            </div>
        </div>

        <h2 class="text-base font-semibold text-slate-700 mb-3">Submissions</h2>

        @if($project->submissions->isEmpty())
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
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500">Score</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($project->submissions as $sub)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <img src="{{ $sub->student->avatar }}" class="w-7 h-7 rounded-full object-cover" alt="">
                                <span class="font-medium text-slate-700">{{ $sub->student->full_name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-slate-500 text-xs">{{ $sub->submitted_at?->format('M d, Y H:i') ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <span class="text-[11px] px-2 py-0.5 rounded-full font-medium
                                {{ $sub->status === 'graded' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700' }}">
                                {{ ucfirst($sub->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 font-medium text-slate-700">
                            {{ $sub->marks_obtained !== null ? $sub->marks_obtained . '/' . $project->total_marks : '—' }}
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

<div id="gradeModal" class="fixed inset-0 z-50 hidden bg-black/40 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
        <h2 class="text-base font-semibold text-slate-700 mb-4">Grade Submission</h2>
        <form id="gradeForm" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="text-xs font-medium text-slate-600 block mb-1">Score (out of {{ $project->total_marks }})</label>
                <input type="number" id="gradeMarks" name="marks_obtained" required min="0" max="{{ $project->total_marks }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
            <div>
                <label class="text-xs font-medium text-slate-600 block mb-1">Feedback</label>
                <textarea id="gradeFeedback" name="teacher_feedback" rows="3"
                          class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></textarea>
            </div>
            <div class="flex gap-3 pt-1">
                <button type="submit" class="flex-1 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">Save</button>
                <button type="button" onclick="document.getElementById('gradeModal').classList.add('hidden')"
                        class="flex-1 py-2 bg-white border border-slate-200 text-slate-700 rounded-lg text-sm hover:bg-slate-50 transition">Cancel</button>
            </div>
        </form>
    </div>
</div>
<script>
function openGradeModal(subId, marks, feedback) {
    document.getElementById('gradeForm').action = '/projects/submissions/' + subId + '/grade';
    document.getElementById('gradeMarks').value = marks || '';
    document.getElementById('gradeFeedback').value = feedback || '';
    document.getElementById('gradeModal').classList.remove('hidden');
}
</script>
@endsection
