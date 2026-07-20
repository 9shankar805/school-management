@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">
        @include('layouts.left-menu')
    </div>

    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        @include('parent.partials.child-selector')
        @include('parent.partials.page-header', ['title' => 'Assignments'])

        @include('session-messages')

        @if(!$promotion)
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 flex items-start gap-3">
            <i class="bi bi-exclamation-triangle-fill text-amber-500 mt-0.5"></i>
            <div>
                <p class="font-semibold text-amber-800 text-sm">Class information not configured</p>
                <p class="text-amber-700 text-xs mt-1">{{ $child->first_name }}'s class and section have not been assigned yet. Please contact school administration.</p>
            </div>
        </div>
        @elseif($assignments->isEmpty())
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-10 text-center">
            <i class="bi bi-file-earmark-text text-slate-200 text-5xl"></i>
            <p class="text-slate-500 mt-3 text-sm">No assignments posted for {{ $child->first_name }}'s class yet.</p>
        </div>
        @else
        <div class="space-y-3">
            @foreach($assignments as $assignment)
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-4 flex items-start gap-4">
                <div class="w-9 h-9 rounded-lg bg-indigo-50 flex items-center justify-center flex-shrink-0">
                    <i class="bi bi-file-earmark-text text-indigo-500"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-slate-800 text-sm">{{ $assignment->assignment_name }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">
                        <span class="font-medium text-slate-600">{{ $assignment->course?->name ?? '—' }}</span>
                        &middot;
                        Teacher: {{ $assignment->teacher?->full_name ?? '—' }}
                        &middot;
                        Posted: {{ $assignment->created_at->format('d M Y') }}
                    </p>
                </div>
                @if($assignment->assignment_file_path)
                <a href="{{ route('file.serve', basename($assignment->assignment_file_path)) }}"
                   target="_blank"
                   class="flex-shrink-0 text-xs px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 font-medium">
                    <i class="bi bi-download me-1"></i>Download
                </a>
                @endif
            </div>
            @endforeach
        </div>
        @endif

    </div>
</div>
@endsection
