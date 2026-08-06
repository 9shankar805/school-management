@extends('layouts.app')
@section('content')
<div class="flex-1 p-6 lg:p-8 overflow-auto">

    {{-- Header --}}
    <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Assignments</h1>
            <p class="text-slate-400 text-sm mt-0.5">
                @if(isset($isMine) && $isMine)
                    Assignments you have uploaded for your classes
                @else
                    Course assignments
                @endif
            </p>
        </div>
        @can('create assignment')
        <a href="{{ route('assignment.create') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
            <i class="bi bi-plus-lg"></i> New Assignment
        </a>
        @endcan
    </div>

    @include('session-messages')

    @if($assignments->isEmpty())
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-14 text-center">
        <i class="bi bi-file-earmark-check text-5xl text-slate-200 block mb-3"></i>
        <p class="text-slate-500 font-medium">No assignments yet.</p>
        <p class="text-slate-400 text-sm mt-1">Create one to share with your students.</p>
        @can('create assignment')
        <a href="{{ route('assignment.create') }}"
           class="mt-5 inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">
            <i class="bi bi-plus-lg"></i> Create First Assignment
        </a>
        @endcan
    </div>
    @else
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Assignment</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide hidden sm:table-cell">Course</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide hidden md:table-cell">Class / Section</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide hidden lg:table-cell">Uploaded</th>
                    <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($assignments as $assignment)
                <tr class="hover:bg-slate-50 transition group">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-500 flex-shrink-0">
                                <i class="bi bi-file-earmark-check-fill text-sm"></i>
                            </span>
                            <span class="font-medium text-slate-700">{{ $assignment->assignment_name }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-slate-500 hidden sm:table-cell">
                        {{ $assignment->course->course_name ?? '—' }}
                    </td>
                    <td class="px-5 py-3.5 text-slate-500 hidden md:table-cell">
                        {{ $assignment->schoolClass->class_name ?? '—' }}
                        @if($assignment->section)
                        <span class="text-slate-400">· {{ $assignment->section->section_name }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-slate-400 text-xs hidden lg:table-cell">
                        {{ $assignment->created_at->format('M d, Y') }}
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2 justify-end">
                            {{-- Download --}}
                            @if($assignment->assignment_file_path)
                            <a href="{{ asset('storage/' . $assignment->assignment_file_path) }}"
                               target="_blank"
                               title="Download"
                               class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-medium transition">
                                <i class="bi bi-download"></i>
                                <span class="hidden sm:inline">Download</span>
                            </a>
                            @endif

                            {{-- Delete (own assignments only) --}}
                            @if(isset($isMine) && $isMine)
                            <form action="{{ route('assignment.destroy', $assignment->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Delete this assignment?')">
                                @csrf @method('DELETE')
                                <button title="Delete"
                                        class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition">
                                    <i class="bi bi-trash text-sm"></i>
                                </button>
                            </form>
                            @endif
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
@endsection
