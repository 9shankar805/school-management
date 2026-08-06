@extends('layouts.app')
@section('content')
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <nav class="text-xs text-slate-400 mb-4">
            <a href="{{ route('question-paper-templates.index') }}" class="hover:text-indigo-600">Templates</a>
            <span class="mx-1">/</span>
            <span class="text-slate-600">New Template</span>
        </nav>

        <h1 class="text-2xl font-bold text-slate-800 mb-6"><i class="bi bi-layout-text-window-reverse me-2 text-indigo-500"></i>New Paper Template</h1>

        @include('session-messages')

        <div class="max-w-3xl">
            <form method="POST" action="{{ route('question-paper-templates.store') }}"
                  class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Template Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                               placeholder="e.g. Standard A4 — School Header">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Description</label>
                        <input type="text" name="description" value="{{ old('description') }}"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                               placeholder="Brief description…">
                    </div>
                </div>

                <hr class="border-slate-100">
                <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide">School Info</h3>

                {{-- School profile preview --}}
                @if($school)
                <div class="flex items-center gap-3 p-3 bg-indigo-50 border border-indigo-100 rounded-xl">
                    <img src="{{ $school->logo_url }}" alt="{{ $school->name }}"
                         class="w-10 h-10 rounded-lg object-cover border border-indigo-200 flex-shrink-0">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-indigo-800 truncate">{{ $school->name }}</p>
                        @if($school->address || $school->city)
                        <p class="text-[11px] text-indigo-500 truncate">{{ implode(', ', array_filter([$school->address, $school->city])) }}</p>
                        @endif
                    </div>
                    <span class="ml-auto text-[10px] text-indigo-400 flex-shrink-0">from school profile</span>
                </div>
                <p class="text-[11px] text-slate-400 -mt-1">
                    Leave the fields below blank to use the school profile values automatically.
                    Fill them in only if you want to override for this template.
                </p>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">School Name
                            <span class="text-xs font-normal text-slate-400 ml-1">(leave blank to use profile)</span>
                        </label>
                        <input type="text" name="school_name" value="{{ old('school_name') }}"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                               placeholder="{{ $school?->name ?? 'ABC Secondary School' }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">School Address
                            <span class="text-xs font-normal text-slate-400 ml-1">(leave blank to use profile)</span>
                        </label>
                        <input type="text" name="school_address" value="{{ old('school_address') }}"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                               placeholder="{{ implode(', ', array_filter([$school?->address, $school?->city])) ?: 'City, Country' }}">
                    </div>
                </div>

                <hr class="border-slate-100">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide">HTML Sections</h3>
                    <p class="text-xs text-slate-400">Supports HTML. Use placeholders: <code class="bg-slate-100 px-1 rounded">@{{school_name}}</code> <code class="bg-slate-100 px-1 rounded">@{{exam_name}}</code> etc.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Header HTML</label>
                    <textarea name="header_html" rows="4"
                              class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-400"
                              placeholder="&lt;div class='text-center'&gt;&lt;h2&gt;@{{school_name}}&lt;/h2&gt;&lt;p&gt;@{{school_address}}&lt;/p&gt;&lt;/div&gt;">{{ old('header_html') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Instructions HTML</label>
                    <textarea name="instructions_html" rows="3"
                              class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-400"
                              placeholder="&lt;p&gt;Attempt all questions. Time: @{{time}}. Full Marks: @{{full_marks}}&lt;/p&gt;">{{ old('instructions_html') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Footer HTML</label>
                    <textarea name="footer_html" rows="2"
                              class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-400"
                              placeholder="<p>Examiner: _____________ &nbsp; Principal: _____________</p>">{{ old('footer_html') }}</textarea>
                </div>

                <hr class="border-slate-100">
                <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Signature &amp; Watermark</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Signature Name</label>
                        <input type="text" name="signature_name" value="{{ old('signature_name') }}"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                               placeholder="Exam Controller">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Signature Title</label>
                        <input type="text" name="signature_title" value="{{ old('signature_title') }}"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                               placeholder="HOD / Principal">
                    </div>
                </div>

                <div class="flex items-start gap-6">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="show_watermark" name="show_watermark" value="1" class="rounded text-indigo-600"
                               {{ old('show_watermark') ? 'checked' : '' }}>
                        <label for="show_watermark" class="text-sm text-slate-700">Add watermark</label>
                    </div>
                    <div class="flex-1">
                        <input type="text" name="watermark_text" value="{{ old('watermark_text', 'CONFIDENTIAL') }}"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                               placeholder="Watermark text">
                    </div>
                </div>

                <hr class="border-slate-100">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Paper Size</label>
                        <select name="paper_size" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="A4" {{ old('paper_size', 'A4') === 'A4' ? 'selected' : '' }}>A4</option>
                            <option value="Letter" {{ old('paper_size') === 'Letter' ? 'selected' : '' }}>Letter</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Default Orientation</label>
                        <select name="orientation" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="portrait" {{ old('orientation', 'portrait') === 'portrait' ? 'selected' : '' }}>Portrait</option>
                            <option value="landscape" {{ old('orientation') === 'landscape' ? 'selected' : '' }}>Landscape</option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition">
                        <i class="bi bi-check2 me-1"></i> Create Template
                    </button>
                    <a href="{{ route('question-paper-templates.index') }}" class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-medium transition">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
