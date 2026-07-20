@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <nav class="text-xs text-slate-400 mb-4">
            <a href="{{ route('question-bank.index') }}" class="hover:text-indigo-600">Question Bank</a>
            <span class="mx-1">/</span>
            <a href="{{ route('question-bank.show', $question->id) }}" class="hover:text-indigo-600">Q#{{ $question->id }}</a>
            <span class="mx-1">/</span>
            <span class="text-slate-600">Edit</span>
        </nav>

        <h1 class="text-2xl font-bold text-slate-800 mb-6"><i class="bi bi-pencil me-2 text-indigo-500"></i>Edit Question</h1>

        @include('session-messages')

        <div class="max-w-3xl">
            <form method="POST" action="{{ route('question-bank.update', $question->id) }}" enctype="multipart/form-data"
                  class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
                @csrf @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Question Type <span class="text-rose-500">*</span></label>
                        <select name="question_type" id="question_type" required onchange="toggleOptions(this.value)"
                                class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            @foreach($types as $key => $label)
                            <option value="{{ $key }}" {{ old('question_type', $question->question_type) === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Difficulty <span class="text-rose-500">*</span></label>
                        <select name="difficulty" required
                                class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            @foreach($diffs as $key => $label)
                            <option value="{{ $key }}" {{ old('difficulty', $question->difficulty) === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Marks <span class="text-rose-500">*</span></label>
                        <input type="number" name="allocated_marks" value="{{ old('allocated_marks', $question->allocated_marks) }}"
                               min="0.25" max="100" step="0.25" required
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Question Text <span class="text-rose-500">*</span></label>
                    @include('question-papers.partials.rich-editor', [
                        'fieldId'   => 'question_text',
                        'editorId'  => 're_bank_edit',
                        'initValue' => old('question_text', $question->question_text),
                    ])
                    <p class="text-xs text-slate-400 mt-1">Use <strong>∑</strong> for math equations (LaTeX/KaTeX).</p>
                </div>

                {{-- MCQ Options --}}
                <div id="mcqOptions" class="{{ old('question_type', $question->question_type) === 'mcq' ? '' : 'hidden' }}">
                    <label class="block text-sm font-medium text-slate-700 mb-2">MCQ Options</label>
                    <div class="space-y-2">
                        @foreach(['A','B','C','D'] as $i => $letter)
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-500 text-xs flex items-center justify-center font-bold flex-shrink-0">{{ $letter }}</span>
                            <input type="text" name="options[]"
                                   value="{{ old('options.'.$i, $question->options[$i] ?? '') }}"
                                   class="flex-1 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                   placeholder="Option {{ $letter }}">
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-2">
                        <label class="block text-xs text-slate-500 mb-1">Correct Answer</label>
                        <input type="text" name="correct_answer" value="{{ old('correct_answer', $question->correct_answer) }}" maxlength="10"
                               class="w-24 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                </div>

                <div id="tfOptions" class="{{ old('question_type', $question->question_type) === 'true_false' ? '' : 'hidden' }}">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Correct Answer</label>
                    <select name="correct_answer_tf"
                            class="w-40 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="True"  {{ old('correct_answer_tf', $question->correct_answer) === 'True'  ? 'selected' : '' }}>True</option>
                        <option value="False" {{ old('correct_answer_tf', $question->correct_answer) === 'False' ? 'selected' : '' }}>False</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Model Answer / Marking Scheme</label>
                    @include('question-papers.partials.rich-editor', [
                        'fieldId'   => 'answer_text',
                        'editorId'  => 're_bank_edit_ans',
                        'initValue' => old('answer_text', $question->answer_text),
                    ])
                </div>

                <hr class="border-slate-100">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Subject</label>
                        <input type="text" name="subject" value="{{ old('subject', $question->subject) }}" list="subjectList"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <datalist id="subjectList">@foreach($subjects as $s)<option value="{{ $s }}">@endforeach</datalist>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Chapter</label>
                        <input type="text" name="chapter" value="{{ old('chapter', $question->chapter) }}"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Bloom's Taxonomy Level</label>
                        <select name="bloom_taxonomy"
                                class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="">— None —</option>
                            @foreach($blooms as $key => $label)
                            <option value="{{ $key }}" {{ old('bloom_taxonomy', $question->bloom_taxonomy) === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Category</label>
                        <select name="category_id"
                                class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="">— None —</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $question->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Learning Outcome</label>
                    <input type="text" name="learning_outcome" value="{{ old('learning_outcome', $question->learning_outcome) }}"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>

                @if($tags->count())
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Tags</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($tags as $tag)
                        <label class="flex items-center gap-1.5 text-xs text-slate-600 cursor-pointer">
                            <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}" class="rounded text-indigo-600"
                                   {{ $question->tags->contains($tag->id) || in_array($tag->id, old('tag_ids', [])) ? 'checked' : '' }}>
                            {{ $tag->name }}
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Existing images --}}
                @if($question->images->count())
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Existing Images</label>
                    <div class="flex flex-wrap gap-3">
                        @foreach($question->images as $img)
                        <div class="relative group">
                            <img src="{{ asset('storage/' . $img->file_path) }}" class="w-24 h-24 object-cover rounded-xl border border-slate-200">
                            <form method="POST" action="{{ route('question-bank.images.destroy', $img->id) }}"
                                  class="absolute top-1 right-1 hidden group-hover:block"
                                  onsubmit="return confirm('Remove this image?')">
                                @csrf @method('DELETE')
                                <button class="w-5 h-5 bg-rose-500 text-white rounded-full flex items-center justify-center text-xs">×</button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Add More Images</label>
                    <input type="file" name="images[]" multiple accept="image/*"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm">
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition">Save Changes</button>
                    <a href="{{ route('question-bank.show', $question->id) }}" class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-medium transition">Cancel</a>
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
toggleOptions(document.getElementById('question_type').value);
</script>
@endsection
