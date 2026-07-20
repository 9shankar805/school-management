@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <nav class="text-xs text-slate-400 mb-4">
            <a href="{{ route('question-bank.index') }}" class="hover:text-indigo-600">Question Bank</a>
            <span class="mx-1">/</span>
            <span class="text-slate-600">Add Question</span>
        </nav>

        <h1 class="text-2xl font-bold text-slate-800 mb-6"><i class="bi bi-patch-plus me-2 text-indigo-500"></i>Add Question to Bank</h1>

        @include('session-messages')

        <div class="max-w-3xl">
            <form method="POST" action="{{ route('question-bank.store') }}" enctype="multipart/form-data"
                  class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
                @csrf

                {{-- Type + Difficulty --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Question Type <span class="text-rose-500">*</span></label>
                        <select name="question_type" id="question_type" required onchange="toggleOptions(this.value)"
                                class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            @foreach($types as $key => $label)
                            <option value="{{ $key }}" {{ old('question_type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Difficulty <span class="text-rose-500">*</span></label>
                        <select name="difficulty" required
                                class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            @foreach($diffs as $key => $label)
                            <option value="{{ $key }}" {{ old('difficulty', 'medium') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Marks <span class="text-rose-500">*</span></label>
                        <input type="number" name="allocated_marks" value="{{ old('allocated_marks', 2) }}"
                               min="0.25" max="100" step="0.25" required
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                </div>

                {{-- Question text (rich editor) --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Question Text <span class="text-rose-500">*</span></label>
                    @include('question-papers.partials.rich-editor', [
                        'fieldId'   => 'question_text',
                        'editorId'  => 're_bank_create',
                        'initValue' => old('question_text', ''),
                    ])
                    <p class="text-xs text-slate-400 mt-1">Use <strong>∑</strong> to insert math equations (LaTeX/KaTeX). Supports bold, italic, tables, images.</p>
                </div>

                {{-- MCQ Options --}}
                <div id="mcqOptions" class="{{ old('question_type', 'essay') === 'mcq' ? '' : 'hidden' }}">
                    <label class="block text-sm font-medium text-slate-700 mb-2">MCQ Options</label>
                    <div class="space-y-2" id="optionsList">
                        @foreach(['A','B','C','D'] as $i => $letter)
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-500 text-xs flex items-center justify-center font-bold flex-shrink-0">{{ $letter }}</span>
                            <input type="text" name="options[]" value="{{ old('options.'.$i) }}"
                                   class="flex-1 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                   placeholder="Option {{ $letter }}">
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-2">
                        <label class="block text-xs text-slate-500 mb-1">Correct Answer (e.g. A or B)</label>
                        <input type="text" name="correct_answer" value="{{ old('correct_answer') }}" maxlength="10"
                               class="w-24 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                               placeholder="A">
                    </div>
                </div>

                {{-- True/False correct answer --}}
                <div id="tfOptions" class="{{ old('question_type') === 'true_false' ? '' : 'hidden' }}">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Correct Answer</label>
                    <select name="correct_answer_tf"
                            class="w-40 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="True">True</option>
                        <option value="False">False</option>
                    </select>
                </div>

                {{-- Answer text (rich editor) --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Model Answer / Marking Scheme</label>
                    @include('question-papers.partials.rich-editor', [
                        'fieldId'   => 'answer_text',
                        'editorId'  => 're_bank_create_ans',
                        'initValue' => old('answer_text', ''),
                    ])
                </div>

                <hr class="border-slate-100">

                {{-- Metadata --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Subject</label>
                        <input type="text" name="subject" value="{{ old('subject') }}" list="subjectList"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                               placeholder="Mathematics, Science…">
                        <datalist id="subjectList">
                            @foreach($subjects as $s)
                            <option value="{{ $s }}">
                            @endforeach
                        </datalist>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Chapter</label>
                        <input type="text" name="chapter" value="{{ old('chapter') }}"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                               placeholder="Chapter 3 — Algebra">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Bloom's Taxonomy Level</label>
                        <select name="bloom_taxonomy"
                                class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="">— None —</option>
                            @foreach($blooms as $key => $label)
                            <option value="{{ $key }}" {{ old('bloom_taxonomy') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Category</label>
                        <select name="category_id"
                                class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="">— None —</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Learning Outcome</label>
                    <input type="text" name="learning_outcome" value="{{ old('learning_outcome') }}"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                           placeholder="Students will be able to…">
                </div>

                {{-- Tags --}}
                @if($tags->count())
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Tags</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($tags as $tag)
                        <label class="flex items-center gap-1.5 text-xs text-slate-600 cursor-pointer">
                            <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}"
                                   class="rounded text-indigo-600"
                                   {{ in_array($tag->id, old('tag_ids', [])) ? 'checked' : '' }}>
                            {{ $tag->name }}
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Image uploads --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Images (optional)</label>
                    <input type="file" name="images[]" multiple accept="image/*"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm">
                    <p class="text-xs text-slate-400 mt-1">PNG, JPG, SVG, WEBP. Multiple files accepted.</p>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition">
                        <i class="bi bi-check2 me-1"></i> Save to Bank
                    </button>
                    <a href="{{ route('question-bank.index') }}" class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-medium transition">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
function toggleOptions(type) {
    document.getElementById('mcqOptions').classList.toggle('hidden', type !== 'mcq');
    document.getElementById('tfOptions').classList.toggle('hidden',  type !== 'true_false');
}
// Init on page load
toggleOptions('{{ old('question_type', 'essay') }}');
</script>
@endsection
