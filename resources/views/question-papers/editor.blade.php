@extends('layouts.app')
@push('head-scripts')
{{-- Tiptap + extensions via CDN --}}
<script src="https://cdn.jsdelivr.net/npm/@tiptap/core@2/dist/index.umd.min.js" defer></script>
{{-- KaTeX for math rendering --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16/dist/katex.min.css">
<script src="https://cdn.jsdelivr.net/npm/katex@0.16/dist/katex.min.js" defer></script>
@endpush
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        {{-- Header --}}
        <div class="flex flex-wrap justify-between items-start mb-6 gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-800 tracking-tight">
                    <i class="bi bi-pencil-square me-2 text-indigo-500"></i>{{ $paper->title }}
                </h1>
                <p class="text-slate-400 text-xs mt-0.5">
                    {{ $paper->subject ?? '—' }} · {{ $paper->duration ?? '—' }} ·
                    Full Marks: {{ $paper->full_marks ?: $paper->total_marks }} ·
                    Pass: {{ $paper->pass_marks }}
                </p>
            </div>
            <div class="flex gap-2 flex-wrap">
                {{-- Status badge --}}
                <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-semibold {{ $paper->status_badge }}">
                    {{ \App\Models\QuestionPaper::STATUSES[$paper->status] }}
                </span>
                {{-- Submit for review --}}
                @if($paper->status === 'draft')
                <form method="POST" action="{{ route('question-papers.submit', $paper->id) }}">
                    @csrf
                    <button class="px-4 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-medium transition">
                        <i class="bi bi-send me-1"></i> Submit for Review
                    </button>
                </form>
                @endif
                {{-- Export PDF --}}
                <a href="{{ route('question-papers.pdf', $paper->id) }}" target="_blank"
                   class="px-4 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-medium transition">
                    <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                </a>
                {{-- Version history --}}
                <a href="{{ route('question-papers.versions', $paper->id) }}"
                   class="px-4 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-medium transition">
                    <i class="bi bi-clock-history me-1"></i> History
                </a>
            </div>
        </div>

        @include('session-messages')

        {{-- Paper meta edit form --}}
        <details class="bg-white rounded-2xl border border-slate-100 shadow-sm mb-6 p-5">
            <summary class="text-sm font-semibold text-slate-700 cursor-pointer">Paper Details</summary>
            <form method="POST" action="{{ route('question-papers.update', $paper->id) }}" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-3">
                @csrf @method('PUT')
                <div>
                    <label class="block text-xs text-slate-500 mb-1">Exam Name</label>
                    <input type="text" name="exam_name" value="{{ $paper->exam_name }}" class="w-full border border-slate-200 rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1">Subject</label>
                    <input type="text" name="subject" value="{{ $paper->subject }}" class="w-full border border-slate-200 rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1">Duration</label>
                    <input type="text" name="duration" value="{{ $paper->duration }}" placeholder="e.g. 3 Hours" class="w-full border border-slate-200 rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1">Full Marks</label>
                    <input type="number" name="full_marks" value="{{ $paper->full_marks }}" class="w-full border border-slate-200 rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1">Pass Marks</label>
                    <input type="number" name="pass_marks" value="{{ $paper->pass_marks }}" class="w-full border border-slate-200 rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1">Exam Date</label>
                    <input type="date" name="exam_date" value="{{ $paper->exam_date?->format('Y-m-d') }}" class="w-full border border-slate-200 rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1">Paper Size</label>
                    <select name="paper_size" class="w-full border border-slate-200 rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="A4" {{ $paper->paper_size=='A4'?'selected':'' }}>A4</option>
                        <option value="Letter" {{ $paper->paper_size=='Letter'?'selected':'' }}>Letter</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1">Orientation</label>
                    <select name="orientation" class="w-full border border-slate-200 rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="portrait"  {{ $paper->orientation=='portrait'?'selected':'' }}>Portrait</option>
                        <option value="landscape" {{ $paper->orientation=='landscape'?'selected':'' }}>Landscape</option>
                    </select>
                </div>
                <div class="col-span-2 md:col-span-4">
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition">Save Details</button>
                </div>
            </form>
        </details>

        {{-- Sections + Questions --}}
        <div id="sectionsContainer" class="space-y-5">
            @foreach($paper->sections as $section)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm" data-section-id="{{ $section->id }}">
                <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-700 text-xs font-bold flex items-center justify-center">{{ $loop->iteration }}</span>
                        <span class="font-semibold text-slate-700">{{ $section->title }}</span>
                        @if($section->instructions)<span class="text-xs text-slate-400 ml-2">{{ $section->instructions }}</span>@endif
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-slate-400">{{ $section->total_marks }} marks</span>
                        @if($paper->is_editable)
                        <button onclick="openAddQuestion({{ $section->id }})" class="px-3 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 rounded-lg text-xs font-medium transition">+ Question</button>
                        <button onclick="deleteSection({{ $section->id }})" class="text-slate-300 hover:text-rose-500 transition text-xs"><i class="bi bi-trash"></i></button>
                        @endif
                    </div>
                </div>
                <div class="p-4 space-y-3">
                    @foreach($section->questions as $q)
                    <div class="flex gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100" data-question-id="{{ $q->id }}">
                        <span class="font-mono text-sm font-bold text-indigo-500 w-8 flex-shrink-0">{{ $q->numbering }}.</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-slate-800 leading-relaxed">{!! $q->question_text !!}</p>
                            @if($q->options)
                            <ol class="mt-2 space-y-0.5 pl-4" style="list-style-type:upper-alpha">
                                @foreach($q->options as $opt)
                                <li class="text-sm text-slate-600">{{ $opt }}</li>
                                @endforeach
                            </ol>
                            @endif
                            <div class="flex gap-2 mt-1.5 flex-wrap">
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700">{{ \App\Models\QuestionBank::QUESTION_TYPES[$q->question_type] ?? $q->question_type }}</span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full {{ match($q->difficulty){ 'easy'=>'bg-emerald-100 text-emerald-700','hard'=>'bg-rose-100 text-rose-700',default=>'bg-amber-100 text-amber-700' } }}">{{ ucfirst($q->difficulty) }}</span>
                                @if($q->chapter)<span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">{{ $q->chapter }}</span>@endif
                            </div>
                        </div>
                        <div class="flex-shrink-0 text-right">
                            <p class="font-bold text-indigo-600">{{ $q->allocated_marks }}</p>
                            <p class="text-[10px] text-slate-400">marks</p>
                            @if($paper->is_editable)
                            <button onclick="deleteQuestion({{ $q->id }})" class="text-slate-300 hover:text-rose-500 transition mt-1"><i class="bi bi-x-lg text-xs"></i></button>
                            @endif
                        </div>
                    </div>
                    @endforeach
                    @if($section->questions->isEmpty())
                    <p class="text-sm text-slate-300 text-center py-4">No questions yet. Click "+ Question" to add.</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        @if($paper->is_editable)
        {{-- Add Section button --}}
        <button onclick="addSection()" class="mt-5 w-full py-3 border-2 border-dashed border-slate-200 hover:border-indigo-300 text-slate-400 hover:text-indigo-500 rounded-2xl text-sm font-medium transition flex items-center justify-center gap-2">
            <i class="bi bi-plus-lg"></i> Add Section
        </button>

        {{-- Add Question Modal --}}
        <div id="addQuestionModal" class="hidden fixed inset-0 bg-black/50 flex items-start justify-center z-50 pt-10 px-4 pb-4 overflow-y-auto">
            <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-2xl">
                <h3 class="text-base font-semibold text-slate-800 mb-4">Add Question</h3>
                <div class="space-y-4">
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Type</label>
                            <select id="qType" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm">
                                @foreach($bankTypes as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Marks</label>
                            <input type="number" id="qMarks" value="5" min="0.25" step="0.25" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Difficulty</label>
                            <select id="qDiff" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm">
                                @foreach($bankDiffs as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Chapter</label>
                        <input type="text" id="qChapter" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm" placeholder="Optional chapter name">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Question Text (HTML / plain) <span class="text-rose-500">*</span></label>
                        <div id="questionEditor" class="border border-slate-200 rounded-xl p-3 min-h-[100px] text-sm focus:outline-none" contenteditable="true"></div>
                        <input type="hidden" id="qText">
                    </div>
                </div>
                <div class="flex gap-2 justify-end mt-4">
                    <button onclick="closeAddQuestion()" class="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-xl transition">Cancel</button>
                    <button onclick="submitQuestion()" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition">Add Question</button>
                </div>
            </div>
        </div>
        @endif

        {{-- Auto-save indicator --}}
        <div id="autoSaveIndicator" class="fixed bottom-4 right-4 text-xs text-slate-400 bg-white border border-slate-100 rounded-full px-3 py-1.5 shadow-sm opacity-0 transition-opacity duration-300">
            <i class="bi bi-cloud-check me-1"></i> Saved
        </div>
    </div>
</div>

@push('scripts')
<script>
const PAPER_ID   = {{ $paper->id }};
const CSRF_TOKEN = '{{ csrf_token() }}';
let activeSectionId = null;

function showSaved() {
    const el = document.getElementById('autoSaveIndicator');
    el.style.opacity = '1';
    setTimeout(() => el.style.opacity = '0', 2000);
}

// ── Auto-save every 60s ─────────────────────────────────────────────────────
setInterval(() => {
    fetch(`/question-papers/${PAPER_ID}/auto-save`, {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': CSRF_TOKEN, 'Content-Type':'application/json'},
    }).then(() => showSaved());
}, 60000);

// ── Add Section ─────────────────────────────────────────────────────────────
function addSection() {
    const title = prompt('Section title (e.g. Section A, Group 1):');
    if (!title) return;
    fetch(`/question-papers/${PAPER_ID}/sections`, {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': CSRF_TOKEN, 'Content-Type':'application/json'},
        body: JSON.stringify({title, instructions: ''})
    })
    .then(r => r.json())
    .then(() => location.reload());
}

// ── Delete Section ───────────────────────────────────────────────────────────
function deleteSection(id) {
    if (!confirm('Delete this section and all its questions?')) return;
    fetch(`/question-papers/sections/${id}`, {
        method: 'DELETE',
        headers: {'X-CSRF-TOKEN': CSRF_TOKEN}
    }).then(() => location.reload());
}

// ── Open Add-Question modal ──────────────────────────────────────────────────
function openAddQuestion(sectionId) {
    activeSectionId = sectionId;
    document.getElementById('addQuestionModal').classList.remove('hidden');
}
function closeAddQuestion() {
    document.getElementById('addQuestionModal').classList.add('hidden');
    document.getElementById('questionEditor').innerHTML = '';
}

// ── Submit question ───────────────────────────────────────────────────────────
function submitQuestion() {
    const text = document.getElementById('questionEditor').innerHTML.trim();
    if (!text) { alert('Question text is required.'); return; }
    fetch(`/question-papers/sections/${activeSectionId}/questions`, {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': CSRF_TOKEN, 'Content-Type':'application/json'},
        body: JSON.stringify({
            question_type:   document.getElementById('qType').value,
            question_text:   text,
            allocated_marks: parseFloat(document.getElementById('qMarks').value),
            difficulty:      document.getElementById('qDiff').value,
            chapter:         document.getElementById('qChapter').value,
        })
    })
    .then(r => r.json())
    .then(() => { closeAddQuestion(); location.reload(); });
}

// ── Delete question ───────────────────────────────────────────────────────────
function deleteQuestion(id) {
    if (!confirm('Remove this question?')) return;
    fetch(`/question-papers/questions/${id}`, {
        method: 'DELETE',
        headers: {'X-CSRF-TOKEN': CSRF_TOKEN}
    }).then(() => location.reload());
}
</script>
@endpush
@endsection
