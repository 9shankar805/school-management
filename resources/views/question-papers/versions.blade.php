@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <nav class="text-xs text-slate-400 mb-4">
            <a href="{{ route('question-papers.index') }}" class="hover:text-indigo-600">Question Papers</a>
            <span class="mx-1">/</span>
            <a href="{{ route('question-papers.show', $paper->id) }}" class="hover:text-indigo-600">{{ Str::limit($paper->title, 40) }}</a>
            <span class="mx-1">/</span>
            <span class="text-slate-600">Version History</span>
        </nav>

        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight"><i class="bi bi-clock-history me-2 text-indigo-500"></i>Version History</h1>
                <p class="text-slate-400 text-sm mt-0.5">{{ $paper->title }}</p>
            </div>
            <a href="{{ route('question-papers.show', $paper->id) }}"
               class="px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-xl text-sm font-medium hover:bg-slate-50 transition">
                <i class="bi bi-arrow-left me-1"></i> Back to Paper
            </a>
        </div>

        @include('session-messages')

        @if($versions->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-10 text-center text-slate-400">
            <i class="bi bi-clock-history text-5xl mb-3 block"></i>
            <p class="text-sm">No saved versions yet. Versions are created automatically on each save.</p>
        </div>
        @else
        <div class="space-y-3">
            @foreach($versions as $version)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <div class="flex flex-wrap justify-between items-start gap-3">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-sm font-bold text-indigo-600">v{{ $version->version_number }}</span>
                            @if($loop->first)
                            <span class="text-[10px] bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-semibold">Current</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500">
                            Saved by <strong>{{ $version->savedBy?->full_name ?? 'System' }}</strong>
                            on {{ $version->created_at->format('M d, Y \a\t H:i') }}
                        </p>
                        @if($version->change_summary)
                        <p class="text-xs text-slate-400 mt-1 italic">"{{ $version->change_summary }}"</p>
                        @endif
                        @php
                            $snap = is_array($version->snapshot) ? $version->snapshot : json_decode($version->snapshot, true);
                            $sectionCount = count($snap['sections'] ?? []);
                            $questionCount = array_sum(array_map(fn($s) => count($s['questions'] ?? []), $snap['sections'] ?? []));
                        @endphp
                        <p class="text-xs text-slate-400 mt-1">
                            {{ $sectionCount }} section(s) · {{ $questionCount }} question(s)
                        </p>
                    </div>
                    @if(!$loop->first && $paper->is_editable)
                    <form method="POST" action="{{ route('question-papers.versions.restore', $version->id) }}"
                          onsubmit="return confirm('Restore this version? Current changes will be overwritten.')">
                        @csrf
                        <button class="px-4 py-2 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-xl text-xs font-medium transition">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Restore
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-4">{{ $versions->links() }}</div>
        @endif
    </div>
</div>
@endsection
