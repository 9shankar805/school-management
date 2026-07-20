@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight"><i class="bi bi-layout-text-window-reverse me-2 text-indigo-500"></i>Paper Templates</h1>
                <p class="text-slate-400 text-sm mt-0.5">Reusable headers, footers and watermarks for question papers</p>
            </div>
            @can('create exams')
            <a href="{{ route('question-paper-templates.create') }}"
               class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition flex items-center gap-1.5">
                <i class="bi bi-plus-lg"></i> New Template
            </a>
            @endcan
        </div>

        @include('session-messages')

        @if($templates->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-12 text-center text-slate-400">
            <i class="bi bi-layout-text-window-reverse text-5xl mb-3 block"></i>
            <p class="text-sm">No templates yet.</p>
            @can('create exams')
            <a href="{{ route('question-paper-templates.create') }}" class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm rounded-xl hover:bg-indigo-700 transition">
                <i class="bi bi-plus-lg"></i> Create First Template
            </a>
            @endcan
        </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($templates as $tpl)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 hover:shadow-md transition flex flex-col">
                <div class="flex items-start justify-between gap-2 mb-3">
                    <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="bi bi-layout-text-window text-indigo-500"></i>
                    </div>
                    <div class="flex gap-1">
                        @can('create exams')
                        <a href="{{ route('question-paper-templates.edit', $tpl->id) }}"
                           class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition">
                            <i class="bi bi-pencil text-xs"></i>
                        </a>
                        <form method="POST" action="{{ route('question-paper-templates.destroy', $tpl->id) }}"
                              onsubmit="return confirm('Delete this template?')">
                            @csrf @method('DELETE')
                            <button class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition">
                                <i class="bi bi-trash text-xs"></i>
                            </button>
                        </form>
                        @endcan
                    </div>
                </div>
                <h3 class="font-semibold text-slate-800 text-sm mb-1">{{ $tpl->name }}</h3>
                @if($tpl->description)
                <p class="text-xs text-slate-400 mb-3 line-clamp-2">{{ $tpl->description }}</p>
                @endif
                <div class="flex flex-wrap gap-2 mt-auto">
                    <span class="text-[10px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full">{{ strtoupper($tpl->paper_size) }}</span>
                    <span class="text-[10px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full capitalize">{{ $tpl->orientation }}</span>
                    @if($tpl->show_watermark)
                    <span class="text-[10px] bg-violet-100 text-violet-600 px-2 py-0.5 rounded-full">Watermark</span>
                    @endif
                    @if($tpl->school_name)
                    <span class="text-[10px] bg-blue-100 text-blue-600 px-2 py-0.5 rounded-full truncate max-w-[120px]">{{ $tpl->school_name }}</span>
                    @endif
                </div>
                <div class="mt-3 pt-3 border-t border-slate-50 flex items-center justify-between text-[10px] text-slate-400">
                    <span>{{ $tpl->papers_count ?? $tpl->papers->count() }} paper(s)</span>
                    <a href="{{ route('question-paper-templates.show', $tpl->id) }}" class="text-indigo-500 hover:underline">View</a>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
