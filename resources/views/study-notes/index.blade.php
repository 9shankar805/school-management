@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Study Materials</h1>
                <p class="text-slate-400 text-sm mt-0.5">Notes, handouts, and reference materials</p>
            </div>
            @can('create study notes')
            <a href="{{ route('study-notes.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                <i class="bi bi-plus-lg"></i> Upload Material
            </a>
            @endcan
        </div>

        @include('session-messages')

        {{-- Filters --}}
        <form method="GET" action="{{ route('study-notes.index') }}" class="flex flex-wrap gap-2 mb-6">
            <select name="type" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">All Types</option>
                <option value="note" {{ request('type') === 'note' ? 'selected' : '' }}>Notes</option>
                <option value="handout" {{ request('type') === 'handout' ? 'selected' : '' }}>Handouts</option>
                <option value="reference" {{ request('type') === 'reference' ? 'selected' : '' }}>References</option>
                <option value="video_link" {{ request('type') === 'video_link' ? 'selected' : '' }}>Video Links</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-lg text-sm hover:bg-slate-50 transition">Filter</button>
            @if(request('type') || request('course_id'))
            <a href="{{ route('study-notes.index') }}" class="px-4 py-2 bg-white border border-slate-200 text-slate-500 rounded-lg text-sm hover:bg-slate-50 transition">Clear</a>
            @endif
        </form>

        @php
            $typeColors = ['note' => 'bg-blue-100 text-blue-700', 'handout' => 'bg-emerald-100 text-emerald-700', 'reference' => 'bg-amber-100 text-amber-700', 'video_link' => 'bg-rose-100 text-rose-700'];
            $typeIcons  = ['note' => 'file-text', 'handout' => 'file-earmark-pdf', 'reference' => 'book', 'video_link' => 'play-btn'];
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($notes as $note)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 hover:shadow-md transition flex flex-col">
                <div class="flex items-start gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0
                        {{ $typeColors[$note->type] ?? 'bg-slate-100 text-slate-600' }}">
                        <i class="bi bi-{{ $typeIcons[$note->type] ?? 'file' }}"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-slate-800 text-sm leading-snug">{{ $note->title }}</h3>
                        <div class="flex flex-wrap gap-2 mt-1">
                            <span class="text-[10px] px-1.5 py-0.5 rounded-full font-medium {{ $typeColors[$note->type] ?? 'bg-slate-100 text-slate-500' }}">
                                {{ ucfirst(str_replace('_', ' ', $note->type)) }}
                            </span>
                            @if(!$note->is_published)
                            <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-slate-100 text-slate-500">Hidden</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="text-xs text-slate-500 space-y-1 mb-3">
                    <p><i class="bi bi-book me-1"></i>{{ $note->course->course_name ?? '—' }}</p>
                    <p><i class="bi bi-layers me-1"></i>{{ $note->schoolClass->class_name ?? '—' }}</p>
                    @if($note->term)<p><i class="bi bi-calendar2-range me-1"></i>{{ $note->term->name }}</p>@endif
                    <p><i class="bi bi-person me-1"></i>{{ $note->uploader->full_name ?? '—' }}</p>
                </div>

                @if($note->description)
                <p class="text-xs text-slate-400 mb-3 line-clamp-2">{{ $note->description }}</p>
                @endif

                <div class="mt-auto flex gap-2">
                    @if($note->file_path)
                    <a href="{{ asset('storage/' . $note->file_path) }}" target="_blank"
                       class="flex-1 text-center py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-medium transition">
                        <i class="bi bi-download me-0.5"></i> Download
                    </a>
                    @elseif($note->external_url)
                    <a href="{{ $note->external_url }}" target="_blank"
                       class="flex-1 text-center py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg text-xs font-medium transition">
                        <i class="bi bi-box-arrow-up-right me-0.5"></i> Open Link
                    </a>
                    @endif

                    @if(auth()->id() === $note->uploaded_by || auth()->user()->can('view academic settings'))
                    <form action="{{ route('study-notes.toggle', $note->id) }}" method="POST">
                        @csrf
                        <button class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition" title="{{ $note->is_published ? 'Hide' : 'Publish' }}">
                            <i class="bi bi-{{ $note->is_published ? 'eye-slash' : 'eye' }} text-sm"></i>
                        </button>
                    </form>
                    <form action="{{ route('study-notes.destroy', $note->id) }}" method="POST" onsubmit="return confirm('Delete this material?')">
                        @csrf @method('DELETE')
                        <button class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition">
                            <i class="bi bi-trash text-sm"></i>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @empty
            <div class="col-span-3 bg-white rounded-2xl border border-slate-100 shadow-sm p-12 text-center">
                <i class="bi bi-folder-symlink text-5xl text-slate-200"></i>
                <p class="mt-3 text-slate-400">No study materials found.</p>
                @can('create study notes')
                <a href="{{ route('study-notes.create') }}" class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">
                    <i class="bi bi-plus-lg"></i> Upload First Material
                </a>
                @endcan
            </div>
            @endforelse
        </div>

        <div class="mt-4">{{ $notes->links() }}</div>
    </div>
</div>
@endsection
