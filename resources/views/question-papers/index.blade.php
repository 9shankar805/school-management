@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight"><i class="bi bi-file-earmark-text me-2"></i>Question Papers</h1>
                <p class="text-slate-400 text-sm mt-0.5">Create, edit, and manage exam question papers</p>
            </div>
            @can('create exams')
            <a href="{{ route('question-papers.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition flex items-center gap-1.5">
                <i class="bi bi-plus-lg"></i> New Paper
            </a>
            @endcan
        </div>

        @if(session('status'))
        <div class="mb-5 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm"><i class="bi bi-check-circle me-1"></i>{{ session('status') }}</div>
        @endif
        @include('session-messages')

        @if($papers->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-10 text-center text-slate-400">
            <i class="bi bi-file-earmark-text text-5xl mb-3 block"></i>
            <p class="text-sm">No question papers yet. <a href="{{ route('question-papers.create') }}" class="text-indigo-500 hover:underline">Create one now.</a></p>
        </div>
        @else
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-xs text-slate-400 bg-slate-50 text-left">
                        <th class="px-5 py-3 font-medium">Title</th>
                        <th class="px-4 py-3 font-medium">Subject</th>
                        <th class="px-4 py-3 font-medium">Exam</th>
                        <th class="px-4 py-3 font-medium text-center">Full Marks</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Author</th>
                        <th class="px-4 py-3 font-medium">Updated</th>
                        <th class="px-4 py-3 font-medium">Actions</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($papers as $p)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-medium text-slate-800">{{ $p->title }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $p->subject ?? $p->course?->course_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $p->exam?->exam_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-slate-700">{{ $p->full_marks ?: $p->total_marks }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $p->status_badge }}">
                                    {{ \App\Models\QuestionPaper::STATUSES[$p->status] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $p->creator?->full_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-400 text-xs">{{ $p->updated_at->diffForHumans() }}</td>
                            <td class="px-4 py-3">
                                <div class="flex gap-1.5 flex-wrap">
                                    <a href="{{ route('question-papers.show', $p->id) }}" class="text-xs px-2 py-1 rounded-lg bg-slate-50 hover:bg-indigo-50 text-slate-600 hover:text-indigo-600 transition">View</a>
                                    @if($p->is_editable && auth()->id() == $p->created_by)
                                    <a href="{{ route('question-papers.edit', $p->id) }}" class="text-xs px-2 py-1 rounded-lg bg-slate-50 hover:bg-blue-50 text-slate-600 hover:text-blue-600 transition">Edit</a>
                                    @endif
                                    <a href="{{ route('question-papers.pdf', $p->id) }}" target="_blank" class="text-xs px-2 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 transition">PDF</a>
                                </div>
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
