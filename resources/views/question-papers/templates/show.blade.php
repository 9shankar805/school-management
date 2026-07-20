@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <nav class="text-xs text-slate-400 mb-4">
            <a href="{{ route('question-paper-templates.index') }}" class="hover:text-indigo-600">Templates</a>
            <span class="mx-1">/</span>
            <span class="text-slate-600">{{ $template->name }}</span>
        </nav>

        @include('session-messages')

        <div class="flex flex-wrap justify-between items-start mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">{{ $template->name }}</h1>
                @if($template->description)<p class="text-slate-400 text-sm mt-0.5">{{ $template->description }}</p>@endif
            </div>
            <div class="flex gap-2">
                @can('create exams')
                <a href="{{ route('question-paper-templates.edit', $template->id) }}"
                   class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
                @endcan
                <a href="{{ route('question-papers.create', ['template_id' => $template->id]) }}"
                   class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium transition">
                    <i class="bi bi-plus-lg me-1"></i> Use This Template
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

            {{-- Details --}}
            <div class="xl:col-span-2 space-y-4">
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                    <h2 class="text-sm font-semibold text-slate-600 mb-4 uppercase tracking-wide">Template Details</h2>
                    <dl class="grid grid-cols-2 gap-3 text-sm">
                        <div><dt class="text-xs text-slate-400">School Name</dt><dd class="text-slate-700">{{ $template->school_name ?: '—' }}</dd></div>
                        <div><dt class="text-xs text-slate-400">School Address</dt><dd class="text-slate-700">{{ $template->school_address ?: '—' }}</dd></div>
                        <div><dt class="text-xs text-slate-400">Paper Size</dt><dd class="text-slate-700">{{ strtoupper($template->paper_size) }}</dd></div>
                        <div><dt class="text-xs text-slate-400">Orientation</dt><dd class="text-slate-700 capitalize">{{ $template->orientation }}</dd></div>
                        <div><dt class="text-xs text-slate-400">Watermark</dt><dd class="text-slate-700">{{ $template->show_watermark ? ($template->watermark_text ?: 'Yes') : 'None' }}</dd></div>
                        <div><dt class="text-xs text-slate-400">Signature</dt><dd class="text-slate-700">{{ $template->signature_name ?: '—' }} {{ $template->signature_title ? "/ {$template->signature_title}" : '' }}</dd></div>
                        <div><dt class="text-xs text-slate-400">Status</dt><dd><span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $template->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $template->is_active ? 'Active' : 'Inactive' }}</span></dd></div>
                        <div><dt class="text-xs text-slate-400">Created by</dt><dd class="text-slate-700">{{ $template->creator?->full_name ?? '—' }}</dd></div>
                    </dl>
                </div>

                @if($template->header_html)
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                    <h2 class="text-sm font-semibold text-slate-600 mb-3 uppercase tracking-wide">Header Preview</h2>
                    <div class="border border-slate-200 rounded-xl p-4 bg-slate-50 text-sm overflow-auto">
                        {!! $template->header_html !!}
                    </div>
                </div>
                @endif

                @if($template->instructions_html)
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                    <h2 class="text-sm font-semibold text-slate-600 mb-3 uppercase tracking-wide">Instructions Preview</h2>
                    <div class="border border-slate-200 rounded-xl p-4 bg-slate-50 text-sm overflow-auto">
                        {!! $template->instructions_html !!}
                    </div>
                </div>
                @endif
            </div>

            {{-- Papers using this template --}}
            <div>
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                    <h2 class="text-sm font-semibold text-slate-600 mb-3 uppercase tracking-wide">Papers Using Template</h2>
                    @if($template->papers->isEmpty())
                    <p class="text-xs text-slate-400">No papers yet.</p>
                    @else
                    <div class="space-y-2">
                        @foreach($template->papers->take(10) as $paper)
                        <a href="{{ route('question-papers.show', $paper->id) }}"
                           class="block p-2.5 rounded-xl hover:bg-slate-50 transition border border-slate-100">
                            <p class="text-xs font-medium text-slate-700 truncate">{{ $paper->title }}</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">{{ \App\Models\QuestionPaper::STATUSES[$paper->status] }} · {{ $paper->updated_at->diffForHumans() }}</p>
                        </a>
                        @endforeach
                        @if($template->papers->count() > 10)
                        <p class="text-xs text-slate-400 text-center pt-1">+{{ $template->papers->count() - 10 }} more</p>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
