@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <nav class="text-xs text-slate-400 mb-4">
            <a href="{{ route('question-paper-templates.index') }}" class="hover:text-indigo-600">Templates</a>
            <span class="mx-1">/</span>
            <span class="text-slate-600">Edit: {{ $template->name }}</span>
        </nav>

        <h1 class="text-2xl font-bold text-slate-800 mb-6"><i class="bi bi-pencil me-2 text-indigo-500"></i>Edit Template</h1>

        @include('session-messages')

        <div class="max-w-3xl">
            <form method="POST" action="{{ route('question-paper-templates.update', $template->id) }}"
                  class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
                @csrf @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Template Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $template->name) }}" required
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Description</label>
                        <input type="text" name="description" value="{{ old('description', $template->description) }}"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">School Name</label>
                        <input type="text" name="school_name" value="{{ old('school_name', $template->school_name) }}"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">School Address</label>
                        <input type="text" name="school_address" value="{{ old('school_address', $template->school_address) }}"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Header HTML</label>
                    <textarea name="header_html" rows="4"
                              class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ old('header_html', $template->header_html) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Instructions HTML</label>
                    <textarea name="instructions_html" rows="3"
                              class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ old('instructions_html', $template->instructions_html) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Footer HTML</label>
                    <textarea name="footer_html" rows="2"
                              class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ old('footer_html', $template->footer_html) }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Signature Name</label>
                        <input type="text" name="signature_name" value="{{ old('signature_name', $template->signature_name) }}"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Signature Title</label>
                        <input type="text" name="signature_title" value="{{ old('signature_title', $template->signature_title) }}"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                </div>

                <div class="flex items-start gap-6">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="show_watermark" name="show_watermark" value="1" class="rounded text-indigo-600"
                               {{ old('show_watermark', $template->show_watermark) ? 'checked' : '' }}>
                        <label for="show_watermark" class="text-sm text-slate-700">Add watermark</label>
                    </div>
                    <div class="flex-1">
                        <input type="text" name="watermark_text" value="{{ old('watermark_text', $template->watermark_text) }}"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Paper Size</label>
                        <select name="paper_size" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            @foreach(['A4','Letter'] as $s)
                            <option value="{{ $s }}" {{ old('paper_size', $template->paper_size) === $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Orientation</label>
                        <select name="orientation" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            @foreach(['portrait','landscape'] as $o)
                            <option value="{{ $o }}" {{ old('orientation', $template->orientation) === $o ? 'selected' : '' }}>{{ ucfirst($o) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="is_active" name="is_active" value="1" class="rounded text-indigo-600"
                           {{ old('is_active', $template->is_active) ? 'checked' : '' }}>
                    <label for="is_active" class="text-sm text-slate-700">Active (available for new papers)</label>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition">Save Changes</button>
                    <a href="{{ route('question-paper-templates.index') }}" class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-medium transition">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
