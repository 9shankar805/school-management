@extends('layouts.app')
@push('head-scripts')
{{-- MathJax v3 for robust LaTeX math typesetting --}}
<script>
window.MathJax = {
  tex: {
    inlineMath: [['$', '$'], ['\\(', '\\)']],
    displayMath: [['$$', '$$'], ['\\[', '\\]']],
    processEscapes: true
  },
  options: {
    processHtmlClass: 'math-render|math-content'
  }
};
</script>
<script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
{{-- KaTeX --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.css">
<script src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/contrib/auto-render.min.js"></script>
{{-- MathLive — UMD build (no type="module") so it doesn't interfere with Tiptap UMD globals --}}
<script src="https://cdn.jsdelivr.net/npm/mathlive@0.100/dist/mathlive.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/mathlive@0.100/dist/mathlive-static.css">
{{-- Cropper.js for image editing --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6/dist/cropper.min.css">
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6/dist/cropper.min.js"></script>
{{-- SortableJS for drag-and-drop reordering --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15/Sortable.min.js"></script>
{{-- Tiptap + extensions --}}
<script src="https://cdn.jsdelivr.net/npm/@tiptap/core@2.4/dist/index.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/pm@2.4/dist/index.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/extension-document@2.4/dist/index.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/extension-paragraph@2.4/dist/index.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/extension-text@2.4/dist/index.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/extension-bold@2.4/dist/index.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/extension-italic@2.4/dist/index.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/extension-underline@2.4/dist/index.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/extension-heading@2.4/dist/index.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/extension-bullet-list@2.4/dist/index.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/extension-ordered-list@2.4/dist/index.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/extension-list-item@2.4/dist/index.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/extension-text-align@2.4/dist/index.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/extension-image@2.4/dist/index.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/extension-table@2.4/dist/index.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/extension-table-row@2.4/dist/index.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/extension-table-cell@2.4/dist/index.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/extension-table-header@2.4/dist/index.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/extension-subscript@2.4/dist/index.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/extension-superscript@2.4/dist/index.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/extension-color@2.4/dist/index.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/extension-text-style@2.4/dist/index.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/extension-highlight@2.4/dist/index.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/starter-kit@2.4/dist/index.umd.min.js"></script>
@endpush
<style>
/* ── Tiptap editor ─────────────────────────────────────────────────────────── */
.tiptap-editor { outline:none; min-height:90px; padding:10px; font-size:14px; line-height:1.7; }
.tiptap-editor p { margin:0 0 4px; }
.tiptap-editor h1,.tiptap-editor h2,.tiptap-editor h3 { font-weight:700; margin:6px 0 2px; }
.tiptap-editor ul,.tiptap-editor ol { padding-left:18px; }
.tiptap-editor table { border-collapse:collapse; width:100%; margin:8px 0; }
.tiptap-editor td,.tiptap-editor th { border:1px solid #cbd5e1; padding:4px 8px; }
.tiptap-editor .math-inline { background:#f0f4ff; border-radius:4px; padding:1px 5px; font-family:monospace; color:#3730a3; cursor:pointer; }
/* toolbar */
.tiptap-toolbar { display:flex; flex-wrap:wrap; gap:2px; padding:6px 8px; background:#f8fafc; border-bottom:1px solid #e2e8f0; border-radius:12px 12px 0 0; }
.tiptap-toolbar button { min-width:28px; height:28px; border:none; background:transparent; border-radius:6px; font-size:12px; font-weight:600; color:#475569; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; padding:0 6px; transition:background .15s,color .15s; white-space:nowrap; }
.tiptap-toolbar button:hover,.tiptap-toolbar button.active { background:#e0e7ff; color:#4f46e5; }
.tiptap-toolbar .sep { width:1px; background:#e2e8f0; margin:2px 4px; align-self:stretch; }
/* modal backdrop */
.qm-modal-bg { position:fixed; inset:0; background:rgba(0,0,0,.55); z-index:100; display:flex; align-items:flex-start; justify-content:center; padding:40px 16px; overflow-y:auto; }
.qm-modal   { background:#fff; border-radius:20px; padding:24px; width:100%; max-width:700px; box-shadow:0 20px 60px rgba(0,0,0,.2); position:relative; }
/* Desmos + GeoGebra iframes */
#desmosFrame, #geogebraFrame, #excalidrawFrame { width:100%; height:440px; border:1px solid #e2e8f0; border-radius:12px; }
/* Cropper modal */
#cropperImgWrap { max-height:380px; overflow:hidden; background:#000; border-radius:12px; }
#cropperImgWrap img { display:block; max-width:100%; }
</style>
@section('content')
<div class="flex-1 flex flex-col overflow-auto">

{{-- ── Top action bar ─────────────────────────────────────────────────────── --}}
<div class="sticky top-0 z-40 bg-white border-b border-slate-200 px-5 py-3 flex flex-wrap justify-between items-center gap-3 shadow-sm">
  <div class="flex items-center gap-3 min-w-0">
    <a href="{{ route('question-papers.index') }}" class="text-slate-400 hover:text-indigo-600 transition"><i class="bi bi-arrow-left-short text-xl"></i></a>
    <div class="min-w-0">
      <h1 class="font-bold text-slate-800 text-sm truncate">{{ $paper->title }}</h1>
      <p class="text-[11px] text-slate-400 truncate">{{ $paper->subject ?? '' }}{{ $paper->subject && $paper->duration ? ' · ' : '' }}{{ $paper->duration ?? '' }} · FM {{ $paper->full_marks ?: $paper->total_marks }}</p>
    </div>
    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $paper->status_badge }} flex-shrink-0">
      {{ \App\Models\QuestionPaper::STATUSES[$paper->status] }}
    </span>
  </div>
  <div class="flex gap-2 flex-wrap">
    @if(!$paper->is_locked)
    <button onclick="openAiGeneratorModal()" type="button"
       class="px-3.5 py-1.5 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white rounded-xl text-xs font-semibold shadow-sm transition flex items-center gap-1.5 cursor-pointer">
      <i class="bi bi-magic text-sm"></i> AI Question Generator
    </button>
    @endif
    @if($paper->status==='draft')
    <form method="POST" action="{{ route('question-papers.submit',$paper->id) }}" class="inline">
      @csrf
      <button class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-medium transition"><i class="bi bi-send me-1"></i>Submit</button>
    </form>
    @endif
    <a href="{{ route('question-papers.pdf',$paper->id) }}" target="_blank"
       class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-medium transition"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</a>
    <a href="{{ route('question-papers.docx',$paper->id) }}"
       class="px-3 py-1.5 bg-indigo-700 hover:bg-indigo-800 text-white rounded-xl text-xs font-medium transition"><i class="bi bi-file-earmark-word me-1"></i>DOCX</a>
    <a href="{{ route('question-papers.versions',$paper->id) }}"
       class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-medium transition"><i class="bi bi-clock-history me-1"></i>History</a>
    <div id="autoSaveIndicator" class="px-3 py-1.5 text-xs text-slate-400 bg-slate-50 border border-slate-100 rounded-xl opacity-0 transition-opacity duration-300 flex items-center gap-1">
      <i class="bi bi-cloud-check"></i> Saved
    </div>
  </div>
</div>

<div class="flex-1 p-5 lg:p-7 max-w-5xl w-full mx-auto">

@include('session-messages')

{{-- ── Paper meta (collapsible) ─────────────────────────────────────────── --}}
<details class="bg-white rounded-2xl border border-slate-100 shadow-sm mb-5 p-5">
  <summary class="text-sm font-semibold text-slate-700 cursor-pointer select-none">
    <i class="bi bi-info-circle me-1 text-indigo-400"></i>Paper Details
    <span class="text-xs text-slate-400 font-normal ml-2">(click to expand / collapse)</span>
  </summary>
  <form method="POST" action="{{ route('question-papers.update',$paper->id) }}" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-3">
    @csrf @method('PUT')
    <div><label class="block text-xs text-slate-500 mb-1">Exam Name</label>
      <input type="text" name="exam_name" value="{{ $paper->exam_name }}" class="w-full border border-slate-200 rounded-xl px-3 py-1.5 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none"></div>
    <div><label class="block text-xs text-slate-500 mb-1">Subject</label>
      <input type="text" name="subject" value="{{ $paper->subject }}" class="w-full border border-slate-200 rounded-xl px-3 py-1.5 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none"></div>
    <div><label class="block text-xs text-slate-500 mb-1">Class Label</label>
      <input type="text" name="class_label" value="{{ $paper->class_label }}" class="w-full border border-slate-200 rounded-xl px-3 py-1.5 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none"></div>
    <div><label class="block text-xs text-slate-500 mb-1">Duration</label>
      <input type="text" name="duration" value="{{ $paper->duration }}" placeholder="3 Hours" class="w-full border border-slate-200 rounded-xl px-3 py-1.5 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none"></div>
    <div><label class="block text-xs text-slate-500 mb-1">Full Marks</label>
      <input type="number" name="full_marks" value="{{ $paper->full_marks }}" class="w-full border border-slate-200 rounded-xl px-3 py-1.5 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none"></div>
    <div><label class="block text-xs text-slate-500 mb-1">Pass Marks</label>
      <input type="number" name="pass_marks" value="{{ $paper->pass_marks }}" class="w-full border border-slate-200 rounded-xl px-3 py-1.5 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none"></div>
    <div><label class="block text-xs text-slate-500 mb-1">Exam Date</label>
      <input type="date" name="exam_date" value="{{ $paper->exam_date?->format('Y-m-d') }}" class="w-full border border-slate-200 rounded-xl px-3 py-1.5 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none"></div>
    <div><label class="block text-xs text-slate-500 mb-1">Paper Size</label>
      <select name="paper_size" class="w-full border border-slate-200 rounded-xl px-3 py-1.5 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
        <option value="A4" {{ $paper->paper_size=='A4'?'selected':'' }}>A4</option>
        <option value="Letter" {{ $paper->paper_size=='Letter'?'selected':'' }}>Letter</option>
      </select></div>
    <div><label class="block text-xs text-slate-500 mb-1">Orientation</label>
      <select name="orientation" class="w-full border border-slate-200 rounded-xl px-3 py-1.5 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
        <option value="portrait" {{ $paper->orientation=='portrait'?'selected':'' }}>Portrait</option>
        <option value="landscape" {{ $paper->orientation=='landscape'?'selected':'' }}>Landscape</option>
      </select></div>
    <div class="col-span-2 md:col-span-4">
      <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition">Save Details</button>
    </div>
  </form>
</details>

{{-- ── Sections list ──────────────────────────────────────────────────────── --}}
<div id="sectionsContainer" class="space-y-5">
  @foreach($paper->sections as $section)
  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm" data-section-id="{{ $section->id }}">
    <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between gap-3 flex-wrap">
      <div class="flex items-center gap-3">
        @if($paper->is_editable)
        <span class="section-drag-handle cursor-grab text-slate-300 hover:text-slate-500 active:cursor-grabbing" title="Drag to reorder section">
          <i class="bi bi-grip-vertical text-base"></i>
        </span>
        @endif
        <span class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-700 text-xs font-bold flex items-center justify-center flex-shrink-0">{{ $loop->iteration }}</span>
        <div>
          <span class="font-semibold text-slate-700 text-sm">{{ $section->title }}</span>
          @if($section->instructions)<span class="text-xs text-slate-400 ml-2">{{ $section->instructions }}</span>@endif
        </div>
      </div>
      <div class="flex items-center gap-2 flex-wrap">
        <span class="text-xs font-semibold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full">{{ $section->total_marks }} marks</span>
        @if($paper->is_editable)
        <button onclick="openAddQuestion({{ $section->id }})" class="px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-medium transition"><i class="bi bi-plus-lg me-1"></i>Question</button>
        <button onclick="openBankPicker({{ $section->id }})" class="px-3 py-1 bg-violet-100 hover:bg-violet-200 text-violet-700 rounded-lg text-xs font-medium transition"><i class="bi bi-collection me-1"></i>From Bank</button>
        <button onclick="deleteSection({{ $section->id }})" class="text-slate-300 hover:text-rose-500 transition text-xs" title="Delete section"><i class="bi bi-trash"></i></button>
        @endif
      </div>
    </div>
    <div class="p-4 space-y-3">
      @foreach($section->questions as $q)
      <div class="flex gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100 group" data-question-id="{{ $q->id }}">
        @if($paper->is_editable)
        <span class="question-drag-handle cursor-grab text-slate-200 hover:text-slate-400 active:cursor-grabbing self-start pt-1 flex-shrink-0" title="Drag to reorder">
          <i class="bi bi-grip-vertical text-sm"></i>
        </span>
        @endif
        <span class="font-mono text-sm font-bold text-indigo-500 w-8 flex-shrink-0 pt-0.5">{{ $q->numbering }}.</span>
        <div class="flex-1 min-w-0">
          <div class="text-sm text-slate-800 leading-relaxed math-render">{!! $q->question_text !!}</div>
          @if($q->options)
          <ol class="mt-2 space-y-0.5 pl-4 text-sm text-slate-600" style="list-style-type:upper-alpha">
            @foreach($q->options as $opt)<li class="math-render">{{ $opt }}</li>@endforeach
          </ol>
          @endif
          <div class="flex gap-2 mt-2 flex-wrap">
            <span class="text-[10px] px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700">{{ \App\Models\QuestionBank::QUESTION_TYPES[$q->question_type] ?? $q->question_type }}</span>
            <span class="text-[10px] px-2 py-0.5 rounded-full {{ match($q->difficulty){'easy'=>'bg-emerald-100 text-emerald-700','hard'=>'bg-rose-100 text-rose-700',default=>'bg-amber-100 text-amber-700'} }}">{{ ucfirst($q->difficulty) }}</span>
            @if($q->chapter)<span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">{{ $q->chapter }}</span>@endif
            @if($q->bloom_taxonomy)<span class="text-[10px] px-2 py-0.5 rounded-full bg-violet-100 text-violet-600">{{ ucfirst($q->bloom_taxonomy) }}</span>@endif
          </div>
        </div>
        <div class="flex-shrink-0 text-right">
          <p class="font-bold text-indigo-600 text-sm">{{ $q->allocated_marks }}</p>
          <p class="text-[10px] text-slate-400">marks</p>
          @if($paper->is_editable)
          <div class="flex items-center gap-1 mt-1 justify-end">
            <button onclick='openEditQuestion({{ json_encode($q) }})' type="button" class="p-1.5 text-slate-400 hover:text-indigo-600 transition rounded-lg hover:bg-indigo-50" title="Edit Question">
              <i class="bi bi-pencil-square text-xs"></i>
            </button>
            <button onclick="deleteQuestion({{ $q->id }})" type="button" class="p-1.5 text-slate-400 hover:text-rose-600 transition rounded-lg hover:bg-rose-50" title="Delete Question">
              <i class="bi bi-trash text-xs"></i>
            </button>
          </div>
          @endif
        </div>
      </div>
      @endforeach
      @if($section->questions->isEmpty())
      <p class="text-sm text-slate-300 text-center py-6">No questions yet. Use the buttons above to add questions.</p>
      @endif
    </div>
  </div>
  @endforeach
  @if($paper->sections->isEmpty())
  <div class="bg-white rounded-2xl border-2 border-dashed border-slate-200 p-10 text-center text-slate-400">
    <i class="bi bi-layout-text-window text-4xl block mb-2"></i>
    <p class="text-sm">No sections yet. Click "Add Section" below to start building your paper.</p>
  </div>
  @endif
</div>

@if($paper->is_editable)
<button onclick="addSection()" class="mt-5 w-full py-3 border-2 border-dashed border-slate-200 hover:border-indigo-300 text-slate-400 hover:text-indigo-500 rounded-2xl text-sm font-medium transition flex items-center justify-center gap-2">
  <i class="bi bi-plus-lg"></i> Add Section
</button>
@endif

</div>{{-- /max-w-5xl --}}
</div>{{-- /flex-1 --}}
</div>{{-- /flex min-h-screen --}}

{{-- ══════════════════════════════════════════════════════════════════════════
     ADD QUESTION MODAL
══════════════════════════════════════════════════════════════════════════ --}}
<div id="addQuestionModal" class="qm-modal-bg hidden">
<div class="qm-modal">
  <div class="flex items-center justify-between mb-5">
    <h3 class="text-base font-bold text-slate-800"><i class="bi bi-patch-plus me-2 text-indigo-500"></i>Add Question</h3>
    <button onclick="closeAddQuestion()" class="text-slate-400 hover:text-slate-700"><i class="bi bi-x-lg"></i></button>
  </div>

  {{-- Row 1: type / marks / difficulty / chapter --}}
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
    <div>
      <label class="block text-xs text-slate-500 mb-1">Type</label>
      <select id="qType" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" onchange="handleTypeChange(this.value)">
        @foreach($bankTypes as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
      </select>
    </div>
    <div>
      <label class="block text-xs text-slate-500 mb-1">Marks</label>
      <input type="number" id="qMarks" value="5" min="0.25" step="0.25" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
    </div>
    <div>
      <label class="block text-xs text-slate-500 mb-1">Difficulty</label>
      <select id="qDiff" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
        @foreach($bankDiffs as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
      </select>
    </div>
    <div>
      <label class="block text-xs text-slate-500 mb-1">Chapter</label>
      <input type="text" id="qChapter" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="Optional">
    </div>
  </div>

  {{-- Row 2: Bloom --}}
  <div class="mb-4">
    <label class="block text-xs text-slate-500 mb-1">Bloom's Level</label>
    <select id="qBloom" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
      <option value="">— None —</option>
      @foreach($blooms as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
    </select>
  </div>

  {{-- Rich text toolbar + editor --}}
  <div class="mb-1">
    <label class="block text-xs text-slate-500 mb-1">Question Text <span class="text-rose-500">*</span></label>
  </div>
  <div class="border border-slate-200 rounded-xl overflow-hidden mb-4">
    {{-- Toolbar --}}
    <div class="tiptap-toolbar" id="qToolbar">
      <button type="button" onclick="qCmd('bold')"           title="Bold"       class="font-bold">B</button>
      <button type="button" onclick="qCmd('italic')"         title="Italic"     class="italic">I</button>
      <button type="button" onclick="qCmd('underline')"      title="Underline"  class="underline">U</button>
      <div class="sep"></div>
      <button type="button" onclick="qCmdLevel(1)"           title="H1">H1</button>
      <button type="button" onclick="qCmdLevel(2)"           title="H2">H2</button>
      <div class="sep"></div>
      <button type="button" onclick="qCmd('bulletList')"     title="Bullet list"><i class="bi bi-list-ul"></i></button>
      <button type="button" onclick="qCmd('orderedList')"    title="Ordered list"><i class="bi bi-list-ol"></i></button>
      <div class="sep"></div>
      <button type="button" onclick="qAlign('left')"         title="Align left"><i class="bi bi-text-left"></i></button>
      <button type="button" onclick="qAlign('center')"       title="Center"><i class="bi bi-text-center"></i></button>
      <button type="button" onclick="qAlign('right')"        title="Align right"><i class="bi bi-text-right"></i></button>
      <div class="sep"></div>
      <button type="button" onclick="qCmd('subscript')"      title="Subscript">X₂</button>
      <button type="button" onclick="qCmd('superscript')"    title="Superscript">X²</button>
      <div class="sep"></div>
      <button type="button" onclick="qInsertTable()"         title="Insert table"><i class="bi bi-table"></i></button>
      <div class="sep"></div>
      <button type="button" onclick="openMathModal()"        title="Insert math equation" class="text-indigo-700 font-bold">∑</button>
      <button type="button" onclick="openGeogebraModal()"    title="Insert geometry (GeoGebra)" class="text-emerald-700">△</button>
      <button type="button" onclick="openDesmosModal()"      title="Insert function graph (Desmos)" class="text-amber-700">f(x)</button>
      <button type="button" onclick="openExcalidrawModal()"  title="Freehand drawing (Excalidraw)" class="text-rose-600">✏</button>
      <div class="sep"></div>
      <button type="button" onclick="triggerImageUpload()"   title="Upload image (with crop)"><i class="bi bi-image"></i></button>
      <input type="file" id="qImageFile" accept="image/*" class="hidden" onchange="handleImageUpload(this)">
    </div>
    {{-- Editor area --}}
    <div id="questionEditor" class="tiptap-editor"></div>
  </div>

  {{-- MCQ options (shown when type=mcq) --}}
  <div id="mcqBlock" class="hidden mb-4 space-y-2">
    <label class="block text-xs text-slate-500 mb-1">MCQ Options</label>
    @foreach(['A','B','C','D'] as $i => $letter)
    <div class="flex items-center gap-2">
      <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-500 text-xs font-bold flex items-center justify-center flex-shrink-0">{{ $letter }}</span>
      <input type="text" id="mcqOpt{{ $i }}" class="flex-1 border border-slate-200 rounded-xl px-3 py-1.5 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none" placeholder="Option {{ $letter }}">
    </div>
    @endforeach
    <div>
      <label class="block text-xs text-slate-500 mb-1">Correct Answer</label>
      <input type="text" id="mcqCorrect" maxlength="4" placeholder="A" class="w-20 border border-slate-200 rounded-xl px-3 py-1.5 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
    </div>
  </div>

  {{-- True/False correct answer --}}
  <div id="tfBlock" class="hidden mb-4">
    <label class="block text-xs text-slate-500 mb-1">Correct Answer</label>
    <select id="tfCorrect" class="w-32 border border-slate-200 rounded-xl px-3 py-1.5 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
      <option value="True">True</option>
      <option value="False">False</option>
    </select>
  </div>

  {{-- Save to bank checkbox --}}
  <div class="flex items-center gap-2 mb-4">
    <input type="checkbox" id="saveToBank" class="rounded text-indigo-600">
    <label for="saveToBank" class="text-xs text-slate-600">Also save this question to the Question Bank</label>
  </div>

  <div class="flex gap-2 justify-end">
    <button onclick="closeAddQuestion()" class="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-xl transition">Cancel</button>
    <button onclick="submitQuestion()" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition"><i class="bi bi-check2 me-1"></i>Add Question</button>
  </div>
</div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     MATH / EQUATION MODAL  (MathLive)
══════════════════════════════════════════════════════════════════════════ --}}
<div id="mathModal" class="qm-modal-bg hidden">
<div class="qm-modal" style="max-width:560px">
  <div class="flex items-center justify-between mb-4">
    <h3 class="font-bold text-slate-800"><i class="bi bi-calculator me-2 text-indigo-500"></i>Insert Math Equation</h3>
    <button onclick="closeMathModal()" class="text-slate-400 hover:text-slate-700"><i class="bi bi-x-lg"></i></button>
  </div>
  {{-- Quick templates --}}
  <div class="mb-3 flex flex-wrap gap-2">
    @foreach(['x^2','\\frac{a}{b}','\\sqrt{x}','\\int_a^b','\\sum_{i=1}^n','\\pi r^2','\\Sigma x','\\alpha\\beta','\\vec{v}','\\begin{pmatrix}a&b\\\\c&d\\end{pmatrix}'] as $tpl)
    <button type="button" onclick="insertMathTemplate('{{ $tpl }}')"
            class="px-2 py-1 text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg font-mono transition">{{ $tpl }}</button>
    @endforeach
  </div>
  <label class="block text-xs text-slate-500 mb-1">LaTeX Expression</label>
  <math-field id="mathField" class="w-full border border-slate-200 rounded-xl p-3 text-lg" style="min-height:60px;"></math-field>
  <p class="text-xs text-slate-400 mt-1">Type LaTeX directly or use the equation keyboard that appears below the field.</p>
  <div class="flex gap-2 justify-end mt-4">
    <button onclick="closeMathModal()" class="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-xl transition">Cancel</button>
    <button onclick="insertMath()" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition">Insert</button>
  </div>
</div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     GEOGEBRA MODAL  (Geometry + Graphs)
══════════════════════════════════════════════════════════════════════════ --}}
<div id="geogebraModal" class="qm-modal-bg hidden">
<div class="qm-modal">
  <div class="flex items-center justify-between mb-4">
    <h3 class="font-bold text-slate-800"><i class="bi bi-diagram-3 me-2 text-emerald-500"></i>GeoGebra — Geometry &amp; Graphs</h3>
    <button onclick="closeGeogebraModal()" class="text-slate-400 hover:text-slate-700"><i class="bi bi-x-lg"></i></button>
  </div>
  <p class="text-xs text-slate-500 mb-3">Draw shapes, angles, constructions, function graphs, and coordinate geometry. When done, click <strong>Insert Screenshot</strong>.</p>
  <iframe id="geogebraFrame"
          src="https://www.geogebra.org/classic?lang=en"
          allow="fullscreen" loading="lazy"
          title="GeoGebra Editor"></iframe>
  <p class="text-xs text-slate-400 mt-2">Tip: Use <em>File → Export → PNG</em> inside GeoGebra, then upload it via the image button — or capture a screenshot and paste it into the editor.</p>
  <div class="flex gap-2 justify-end mt-4">
    <button onclick="closeGeogebraModal()" class="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-xl transition">Close</button>
  </div>
</div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     DESMOS MODAL  (Function graphs)
══════════════════════════════════════════════════════════════════════════ --}}
<div id="desmosModal" class="qm-modal-bg hidden">
<div class="qm-modal">
  <div class="flex items-center justify-between mb-4">
    <h3 class="font-bold text-slate-800"><i class="bi bi-graph-up me-2 text-amber-500"></i>Desmos — Function Grapher</h3>
    <button onclick="closeDesmosModal()" class="text-slate-400 hover:text-slate-700"><i class="bi bi-x-lg"></i></button>
  </div>
  <p class="text-xs text-slate-500 mb-3">Plot functions (y=x², y=sin(x), etc.), inequalities, and data. Screenshot and paste into the editor when done.</p>
  <iframe id="desmosFrame"
          src="https://www.desmos.com/calculator?embed"
          allow="fullscreen" loading="lazy"
          title="Desmos Calculator"></iframe>
  <p class="text-xs text-slate-400 mt-2">Tip: Press <kbd class="bg-slate-100 px-1 rounded">Share</kbd> inside Desmos to get a PNG link, then upload it via the image button.</p>
  <div class="flex gap-2 justify-end mt-4">
    <button onclick="closeDesmosModal()" class="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-xl transition">Close</button>
  </div>
</div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     EXCALIDRAW MODAL  (Freehand drawing / diagrams)
══════════════════════════════════════════════════════════════════════════ --}}
<div id="excalidrawModal" class="qm-modal-bg hidden">
<div class="qm-modal">
  <div class="flex items-center justify-between mb-4">
    <h3 class="font-bold text-slate-800"><i class="bi bi-pencil-square me-2 text-rose-500"></i>Excalidraw — Freehand Drawing</h3>
    <button onclick="closeExcalidrawModal()" class="text-slate-400 hover:text-slate-700"><i class="bi bi-x-lg"></i></button>
  </div>
  <p class="text-xs text-slate-500 mb-3">Draw diagrams, arrows, shapes, flowcharts, and annotations. Export as PNG and upload it into the editor.</p>
  <iframe id="excalidrawFrame"
          src="https://excalidraw.com/"
          allow="clipboard-read; clipboard-write; fullscreen"
          loading="lazy"
          title="Excalidraw Drawing"></iframe>
  <div class="mt-3 p-3 bg-amber-50 border border-amber-100 rounded-xl text-xs text-amber-700">
    <strong>How to insert:</strong>
    In Excalidraw click <em>⋮ → Export image → PNG</em>, save the file, then click
    <button type="button" onclick="closeExcalidrawModal();triggerImageUpload()"
            class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-100 hover:bg-amber-200 rounded text-amber-800 font-medium transition">
      <i class="bi bi-image"></i> Upload Image
    </button>
    in the toolbar to add it to your question.
  </div>
  <div class="flex justify-end mt-4">
    <button onclick="closeExcalidrawModal()" class="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-xl transition">Close</button>
  </div>
</div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     CROPPER.JS IMAGE EDIT MODAL
══════════════════════════════════════════════════════════════════════════ --}}
<div id="cropperModal" class="qm-modal-bg hidden">
<div class="qm-modal" style="max-width:620px">
  <div class="flex items-center justify-between mb-4">
    <h3 class="font-bold text-slate-800"><i class="bi bi-crop me-2 text-teal-500"></i>Edit Image</h3>
    <button onclick="closeCropperModal()" class="text-slate-400 hover:text-slate-700"><i class="bi bi-x-lg"></i></button>
  </div>

  {{-- Controls --}}
  <div class="flex flex-wrap gap-2 mb-3">
    <button type="button" onclick="cropperAction('rotateLeft')"
            class="px-3 py-1.5 text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition flex items-center gap-1">
      <i class="bi bi-arrow-counterclockwise"></i> Rotate Left
    </button>
    <button type="button" onclick="cropperAction('rotateRight')"
            class="px-3 py-1.5 text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition flex items-center gap-1">
      <i class="bi bi-arrow-clockwise"></i> Rotate Right
    </button>
    <button type="button" onclick="cropperAction('flipX')"
            class="px-3 py-1.5 text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition">
      ↔ Flip H
    </button>
    <button type="button" onclick="cropperAction('flipY')"
            class="px-3 py-1.5 text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition">
      ↕ Flip V
    </button>
    <button type="button" onclick="cropperAction('reset')"
            class="px-3 py-1.5 text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition">
      ↺ Reset
    </button>
    <div class="flex items-center gap-2 ml-auto">
      <label class="text-xs text-slate-500">Quality</label>
      <input type="range" id="cropQuality" min="0.3" max="1" step="0.1" value="0.85"
             class="w-24 accent-indigo-600">
      <span id="cropQualityLabel" class="text-xs text-slate-500 w-8">85%</span>
    </div>
  </div>

  {{-- Aspect ratio presets --}}
  <div class="flex gap-2 mb-3 flex-wrap">
    <span class="text-xs text-slate-500 self-center">Aspect:</span>
    <button type="button" onclick="setCropRatio(NaN)"   class="px-2 py-1 text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg transition">Free</button>
    <button type="button" onclick="setCropRatio(1)"     class="px-2 py-1 text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg transition">1:1</button>
    <button type="button" onclick="setCropRatio(4/3)"   class="px-2 py-1 text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg transition">4:3</button>
    <button type="button" onclick="setCropRatio(16/9)"  class="px-2 py-1 text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg transition">16:9</button>
    <button type="button" onclick="setCropRatio(3/4)"   class="px-2 py-1 text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg transition">3:4</button>
  </div>

  {{-- Cropper preview area --}}
  <div id="cropperImgWrap">
    <img id="cropperImg" src="" alt="crop preview">
  </div>

  <div class="flex gap-2 justify-end mt-4">
    <button onclick="closeCropperModal()" class="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-xl transition">Cancel</button>
    <button onclick="insertCroppedImage()" class="px-5 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-sm font-medium transition">
      <i class="bi bi-check2 me-1"></i> Insert Image
    </button>
  </div>
</div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     QUESTION BANK PICKER MODAL
══════════════════════════════════════════════════════════════════════════ --}}
<div id="bankPickerModal" class="qm-modal-bg hidden">
<div class="qm-modal">
  <div class="flex items-center justify-between mb-4">
    <h3 class="font-bold text-slate-800"><i class="bi bi-collection me-2 text-violet-500"></i>Insert from Question Bank</h3>
    <button onclick="closeBankPicker()" class="text-slate-400 hover:text-slate-700"><i class="bi bi-x-lg"></i></button>
  </div>
  <div class="flex flex-wrap gap-2 mb-4">
    <input type="text" id="bankSearch" placeholder="Search question text…" class="flex-1 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none" oninput="searchBank()">
    <select id="bankSubject" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" onchange="searchBank()">
      <option value="">All subjects</option>
      @foreach($subjects as $s)<option value="{{ $s }}">{{ $s }}</option>@endforeach
    </select>
    <select id="bankType" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" onchange="searchBank()">
      <option value="">All types</option>
      @foreach($bankTypes as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
    </select>
    <select id="bankDiff" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" onchange="searchBank()">
      <option value="">All difficulties</option>
      @foreach($bankDiffs as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
    </select>
  </div>
  <div id="bankResults" class="space-y-2 max-h-96 overflow-y-auto">
    <p class="text-xs text-slate-400 text-center py-6">Start typing to search the question bank…</p>
  </div>
</div>
</div>

@push('scripts')
<script>
const PAPER_ID={{ $paper->id }}, CSRF='{{ csrf_token() }}';
let activeSectionId=null, editor=null, mathField=null;
let cropperInstance=null, cropperScaleX=1, cropperScaleY=1;

// ── Auto-save every 60s ────────────────────────────────────────────────────
setInterval(()=>{
  fetch(`/question-papers/${PAPER_ID}/auto-save`,{method:'POST',headers:{'X-CSRF-TOKEN':CSRF}})
    .then(()=>{let el=document.getElementById('autoSaveIndicator');el.style.opacity='1';setTimeout(()=>el.style.opacity='0',2000)});
},60000);

// ── DOM ready: math render + SortableJS drag-drop ─────────────────────────
window.renderAllMath = function(container, retryCount) {
  container = container || document;
  retryCount = retryCount || 0;

  // MathJax Typesetting
  if (window.MathJax && window.MathJax.typesetPromise) {
    window.MathJax.typesetPromise([container]).catch(function(err) {
      console.warn('MathJax error:', err);
    });
  }

  // KaTeX Typesetting
  if (typeof renderMathInElement !== 'undefined') {
    const elements = container.querySelectorAll('.math-render, .math-content, [data-section-id], #sectionsContainer');
    elements.forEach(function(el) {
      try {
        renderMathInElement(el, {
          delimiters: [
            {left: '$$', right: '$$', display: true},
            {left: '\\[', right: '\\]', display: true},
            {left: '$', right: '$', display: false},
            {left: '\\(', right: '\\)', display: false}
          ],
          throwOnError: false
        });
      } catch(e) { console.warn('KaTeX render error:', e); }
    });
  } else if (retryCount < 30) {
    setTimeout(function() { window.renderAllMath(container, retryCount + 1); }, 150);
  }
};

document.addEventListener('DOMContentLoaded', function() {
  window.renderAllMath();
  setTimeout(window.renderAllMath, 300);
  setTimeout(window.renderAllMath, 1000);

  // Quality slider label
  const qs=document.getElementById('cropQuality');
  if(qs) qs.addEventListener('input',()=>{ document.getElementById('cropQualityLabel').textContent=Math.round(qs.value*100)+'%'; });

  @if($paper->is_editable)
  // SortableJS — section reorder
  const sec=document.getElementById('sectionsContainer');
  if(sec && typeof Sortable!=='undefined'){
    Sortable.create(sec,{
      handle:'.section-drag-handle', animation:150, ghostClass:'opacity-40',
      onEnd(){ const ids=[...sec.querySelectorAll('[data-section-id]')].map(e=>+e.dataset.sectionId);
        fetch(`/question-papers/${PAPER_ID}/reorder-sections`,{method:'POST',headers:{'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json'},body:JSON.stringify({order:ids})});
      }
    });
    // Question reorder per section
    document.querySelectorAll('[data-section-id]').forEach(sEl=>{
      const qWrap=sEl.querySelector('.p-4');
      if(!qWrap)return;
      const sid=+sEl.dataset.sectionId;
      Sortable.create(qWrap,{
        handle:'.question-drag-handle', animation:150, ghostClass:'opacity-40',
        onEnd(){ const ids=[...qWrap.querySelectorAll('[data-question-id]')].map(e=>+e.dataset.questionId);
          fetch(`/question-papers/sections/${sid}/reorder`,{method:'POST',headers:{'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json'},body:JSON.stringify({order:ids})});
        }
      });
    });
  }
  @endif
});

window.addEventListener('load', window.renderAllMath);

// ── Section actions ─────────────────────────────────────────────────────────
function addSection(){
  let t=prompt('Section title (e.g. Section A):');
  if(!t)return;
  fetch(`/question-papers/${PAPER_ID}/sections`,{method:'POST',headers:{'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json'},body:JSON.stringify({title:t,instructions:''})})
    .then(r=>r.json()).then(()=>location.reload());
}
function deleteSection(id){
  if(!confirm('Delete section + questions?'))return;
  fetch(`/question-papers/sections/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':CSRF}}).then(()=>location.reload());
}
function deleteQuestion(id){
  if(!confirm('Delete question?'))return;
  fetch(`/question-papers/questions/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':CSRF}}).then(()=>location.reload());
}

// ── Tiptap editor ─────────────────────────────────────────────────────────
function initTiptap(){
  const el=document.querySelector('#questionEditor');
  if(!el||editor)return;
  try{
    editor=new window['@tiptap/core'].Editor({
      element:el,
      extensions:[
        window['@tiptap/starter-kit'].StarterKit,
        window['@tiptap/extension-underline'].Underline,
        window['@tiptap/extension-image'].Image,
        window['@tiptap/extension-text-align'].TextAlign.configure({types:['heading','paragraph']}),
        window['@tiptap/extension-subscript'].Subscript,
        window['@tiptap/extension-superscript'].Superscript,
        window['@tiptap/extension-table'].Table.configure({resizable:false}),
        window['@tiptap/extension-table-row'].TableRow,
        window['@tiptap/extension-table-cell'].TableCell,
        window['@tiptap/extension-table-header'].TableHeader,
      ],
      content:'<p>Enter question here…</p>'
    });
  }catch(err){
    // Fallback: plain contenteditable
    console.warn('Tiptap init failed, using plain editor',err);
    el.contentEditable='true';el.innerHTML='<p>Enter question here…</p>';
    editor={getHTML:()=>el.innerHTML,commands:{setContent:h=>el.innerHTML=h},destroy:()=>{},
      chain:()=>({focus:()=>({toggleMark:()=>({run:()=>{}}),toggleHeading:()=>({run:()=>{}}),
        setTextAlign:()=>({run:()=>{}}),toggleBulletList:()=>({run:()=>{}}),toggleOrderedList:()=>({run:()=>{}}),
        insertTable:()=>({run:()=>{}}),setImage:({src})=>({run:()=>el.innerHTML+=`<img src="${src}" style="max-width:100%">`}),
        insertContent:h=>({run:()=>el.innerHTML+=h})})})};
  }
  // ── paste image ──────────────────────────────────────────────────────────
  el.addEventListener('paste',evt=>{
    const img=[...(evt.clipboardData?.items||[])].find(i=>i.type.startsWith('image/'));
    if(!img)return; evt.preventDefault(); openCropperModal(img.getAsFile());
  });
  // ── drag-drop image ──────────────────────────────────────────────────────
  el.addEventListener('dragover',e=>e.preventDefault());
  el.addEventListener('drop',evt=>{
    const img=[...(evt.dataTransfer?.files||[])].find(f=>f.type.startsWith('image/'));
    if(!img)return; evt.preventDefault(); openCropperModal(img);
  });
}
function qCmd(c){
  if(!editor)return;
  const ch=editor.chain().focus();
  if(c==='bulletList')ch.toggleBulletList().run();
  else if(c==='orderedList')ch.toggleOrderedList().run();
  else ch.toggleMark(c).run();
}
function qCmdLevel(l){editor?.chain().focus().toggleHeading({level:l}).run();}
function qAlign(a){editor?.chain().focus().setTextAlign(a).run();}
function qInsertTable(){editor?.chain().focus().insertTable({rows:3,cols:3,withHeaderRow:true}).run();}

// ── Cropper.js image upload / edit ────────────────────────────────────────
function triggerImageUpload(){document.getElementById('qImageFile').click();}
function handleImageUpload(input){
  const file=input.files[0];if(!file)return;
  input.value='';
  openCropperModal(file);
}
function openCropperModal(file){
  const reader=new FileReader();
  reader.onload=e=>{
    const img=document.getElementById('cropperImg');
    img.src=e.target.result;
    document.getElementById('cropperModal').classList.remove('hidden');
    if(cropperInstance){cropperInstance.destroy();cropperInstance=null;}
    cropperScaleX=1;cropperScaleY=1;
    cropperInstance=new Cropper(img,{viewMode:1,autoCropArea:1,movable:true,zoomable:true,rotatable:true,scalable:true,responsive:true});
  };
  reader.readAsDataURL(file);
}
function closeCropperModal(){
  document.getElementById('cropperModal').classList.add('hidden');
  if(cropperInstance){cropperInstance.destroy();cropperInstance=null;}
}
function cropperAction(a){
  if(!cropperInstance)return;
  if(a==='rotateLeft')cropperInstance.rotate(-45);
  else if(a==='rotateRight')cropperInstance.rotate(45);
  else if(a==='flipX'){cropperScaleX*=-1;cropperInstance.scaleX(cropperScaleX);}
  else if(a==='flipY'){cropperScaleY*=-1;cropperInstance.scaleY(cropperScaleY);}
  else if(a==='reset'){cropperInstance.reset();cropperScaleX=1;cropperScaleY=1;}
}
function setCropRatio(r){if(cropperInstance)cropperInstance.setAspectRatio(isNaN(r)?NaN:r);}
function insertCroppedImage(){
  if(!cropperInstance||!editor)return;
  const q=parseFloat(document.getElementById('cropQuality').value)||0.85;
  const src=cropperInstance.getCroppedCanvas({maxWidth:1200,maxHeight:1200,fillColor:'#fff'}).toDataURL('image/jpeg',q);
  editor.chain().focus().setImage({src}).run();
  closeCropperModal();
}

// ── Add question modal ────────────────────────────────────────────────────
function openAddQuestion(sid){
  activeSectionId=sid;
  document.getElementById('addQuestionModal').classList.remove('hidden');
  setTimeout(()=>{
    if(!editor)initTiptap();
    else editor.commands.setContent('<p>Enter question here…</p>');
    handleTypeChange('essay');
  },50);
}
function closeAddQuestion(){
  document.getElementById('addQuestionModal').classList.add('hidden');
  if(editor){editor.destroy();editor=null;}
}
function handleTypeChange(t){
  document.getElementById('mcqBlock').classList.toggle('hidden',t!=='mcq');
  document.getElementById('tfBlock').classList.toggle('hidden',t!=='true_false');
}

// ── Submit question ───────────────────────────────────────────────────────
function submitQuestion(){
  const html=editor?editor.getHTML().trim():'';
  if(!html||html==='<p>Enter question here…</p>'){alert('Question text required.');return;}
  const type=document.getElementById('qType').value;
  let opts=null,correct=null;
  if(type==='mcq'){
    opts=[0,1,2,3].map(i=>document.getElementById('mcqOpt'+i).value).filter(x=>x);
    correct=document.getElementById('mcqCorrect').value;
  }else if(type==='true_false'){
    correct=document.getElementById('tfCorrect').value;
  }
  fetch(`/question-papers/sections/${activeSectionId}/questions`,{
    method:'POST',headers:{'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json'},
    body:JSON.stringify({
      question_text:html,question_type:type,
      allocated_marks:parseFloat(document.getElementById('qMarks').value)||5,
      difficulty:document.getElementById('qDiff').value,
      chapter:document.getElementById('qChapter').value,
      bloom_taxonomy:document.getElementById('qBloom').value,
      options:opts,correct_answer:correct
    })
  }).then(r=>r.json()).then(()=>location.reload());
}

// ── Math modal ────────────────────────────────────────────────────────────
function openMathModal(){
  document.getElementById('mathModal').classList.remove('hidden');
  mathField=document.getElementById('mathField');
  mathField.value='';
}
function closeMathModal(){document.getElementById('mathModal').classList.add('hidden');}
function insertMathTemplate(latex){if(mathField)mathField.value=latex;}
function insertMath(){
  const latex=mathField?.value?.trim();
  if(!latex){alert('Enter LaTeX.');return;}
  editor?.chain().focus().insertContent('<span class="math-inline">$'+latex+'$</span>&nbsp;').run();
  closeMathModal();
}

// ── GeoGebra, Desmos, Excalidraw modals ──────────────────────────────────
function openGeogebraModal(){document.getElementById('geogebraModal').classList.remove('hidden');}
function closeGeogebraModal(){document.getElementById('geogebraModal').classList.add('hidden');}
function openDesmosModal(){document.getElementById('desmosModal').classList.remove('hidden');}
function closeDesmosModal(){document.getElementById('desmosModal').classList.add('hidden');}
function openExcalidrawModal(){document.getElementById('excalidrawModal').classList.remove('hidden');}
function closeExcalidrawModal(){document.getElementById('excalidrawModal').classList.add('hidden');}

// ── Bank picker modal ─────────────────────────────────────────────────────
function openBankPicker(sid){
  activeSectionId=sid;
  document.getElementById('bankPickerModal').classList.remove('hidden');
  document.getElementById('bankResults').innerHTML='<p class="text-xs text-slate-400 text-center py-6">Start typing to search…</p>';
}
function closeBankPicker(){document.getElementById('bankPickerModal').classList.add('hidden');}

let _bankTimer=null;
function searchBank(){
  clearTimeout(_bankTimer);
  _bankTimer=setTimeout(()=>{
    const q=encodeURIComponent(document.getElementById('bankSearch').value);
    const subj=encodeURIComponent(document.getElementById('bankSubject').value);
    const type=encodeURIComponent(document.getElementById('bankType').value);
    const diff=encodeURIComponent(document.getElementById('bankDiff').value);
    if(!q&&!subj&&!type&&!diff)return;
    fetch(`/question-papers/${PAPER_ID}/bank-search?search=${q}&subject=${subj}&question_type=${type}&difficulty=${diff}`)
      .then(r=>r.json()).then(data=>{
        const rows=data.data||[];
        if(!rows.length){
          document.getElementById('bankResults').innerHTML='<p class="text-xs text-slate-400 text-center py-6">No results.</p>';
          return;
        }
        document.getElementById('bankResults').innerHTML=rows.map(b=>`
          <div class="border border-slate-100 rounded-xl p-3 hover:bg-indigo-50 cursor-pointer transition group"
               onclick="insertBankQuestion(${b.id})">
            <p class="text-sm text-slate-700 line-clamp-2 group-hover:text-indigo-700">${b.question_text.replace(/<[^>]*>/g,'').substring(0,120)}</p>
            <div class="flex gap-2 mt-2 flex-wrap">
              <span class="text-[10px] px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700">${b.question_type}</span>
              <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">${b.allocated_marks} marks</span>
              ${b.difficulty?`<span class="text-[10px] px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">${b.difficulty}</span>`:''}
            </div>
          </div>`).join('');
      });
  },300);
}
function insertBankQuestion(bankId){
  fetch(`/question-papers/sections/${activeSectionId}/questions`,{
    method:'POST',headers:{'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json'},
    body:JSON.stringify({bank_id:bankId,question_text:'',question_type:'essay',allocated_marks:1,difficulty:'medium'})
  }).then(r=>r.json()).then(()=>location.reload());
}

// ── AI Question Generator Modal ──────────────────────────────────────────
function openAiGeneratorModal(){
  document.getElementById('aiGeneratorModal').classList.remove('hidden');
  document.getElementById('aiGenError').classList.add('hidden');
}
function closeAiGeneratorModal(){
  document.getElementById('aiGeneratorModal').classList.add('hidden');
}
function updateFileName(input){
  const nameEl = document.getElementById('aiFileName');
  if(input.files && input.files[0]){
    nameEl.textContent = 'Selected: ' + input.files[0].name;
  } else {
    nameEl.textContent = 'Click to select file or drag & drop';
  }
}
function submitAiGenerator(e){
  e.preventDefault();
  const form = document.getElementById('aiGenForm');
  const errDiv = document.getElementById('aiGenError');
  const btn = document.getElementById('aiSubmitBtn');

  errDiv.classList.add('hidden');
  errDiv.textContent = '';

  const formData = new FormData(form);

  btn.disabled = true;
  btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> Reading file & generating questions...';

  fetch(`/question-papers/${PAPER_ID}/ai-generate`, {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': CSRF },
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if(data.success){
      btn.innerHTML = '<i class="bi bi-check-circle"></i> Success! Reloading...';
      location.reload();
    } else {
      errDiv.textContent = data.error || 'Failed to generate questions.';
      errDiv.classList.remove('hidden');
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-magic"></i> Generate & Populate Questions';
    }
  })
  .catch(err => {
    errDiv.textContent = 'Server error occurred during generation: ' + err.message;
    errDiv.classList.remove('hidden');
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-magic"></i> Generate & Populate Questions';
  });
}

// ── Individual Question Edit ────────────────────────────────────────────────
let currentEditingQ = null;

function openEditQuestion(q){
  currentEditingQ = q;
  document.getElementById('eqId').value = q.id;
  document.getElementById('eqType').value = q.question_type || 'short_answer';
  document.getElementById('eqMarks').value = q.allocated_marks || 1;
  document.getElementById('eqDiff').value = q.difficulty || 'medium';
  document.getElementById('eqBloom').value = q.bloom_taxonomy || '';
  document.getElementById('eqText').value = (q.question_text || '').replace(/<[^>]*>/g, '');
  document.getElementById('eqAnswer').value = q.answer_text || '';

  renderEditOptions(q.options || []);
  toggleEditOptions(q.question_type || 'short_answer');

  document.getElementById('editQuestionModal').classList.remove('hidden');
}

function closeEditQuestion(){
  document.getElementById('editQuestionModal').classList.add('hidden');
}

function toggleEditOptions(type){
  const sec = document.getElementById('eqOptionsSection');
  if(type === 'mcq' || type === 'single_choice' || type === 'match'){
    sec.classList.remove('hidden');
  } else {
    sec.classList.add('hidden');
  }
}

function renderEditOptions(opts){
  const container = document.getElementById('eqOptionsContainer');
  if(!opts || !opts.length) opts = ['', ''];
  container.innerHTML = opts.map((opt, idx) => `
    <div class="flex items-center gap-2 eq-opt-row">
      <span class="w-6 text-center text-xs font-bold text-slate-500">${String.fromCharCode(65 + idx)}.</span>
      <input type="text" value="${(opt || '').replace(/"/g, '&quot;')}" class="flex-1 border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs focus:ring-2 focus:ring-indigo-400 outline-none eq-opt-input" placeholder="Option ${idx + 1}">
      <button type="button" onclick="this.closest('.eq-opt-row').remove()" class="text-slate-300 hover:text-rose-500 p-1 text-xs"><i class="bi bi-trash"></i></button>
    </div>
  `).join('');
}

function addEditOption(){
  const container = document.getElementById('eqOptionsContainer');
  const count = container.querySelectorAll('.eq-opt-row').length;
  const div = document.createElement('div');
  div.className = 'flex items-center gap-2 eq-opt-row';
  div.innerHTML = `
    <span class="w-6 text-center text-xs font-bold text-slate-500">${String.fromCharCode(65 + count)}.</span>
    <input type="text" value="" class="flex-1 border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs focus:ring-2 focus:ring-indigo-400 outline-none eq-opt-input" placeholder="Option ${count + 1}">
    <button type="button" onclick="this.closest('.eq-opt-row').remove()" class="text-slate-300 hover:text-rose-500 p-1 text-xs"><i class="bi bi-trash"></i></button>
  `;
  container.appendChild(div);
}

function saveEditQuestion(e){
  e.preventDefault();
  const qId = document.getElementById('eqId').value;
  const qType = document.getElementById('eqType').value;
  const qMarks = parseFloat(document.getElementById('eqMarks').value) || 1;
  const qDiff = document.getElementById('eqDiff').value;
  const qBloom = document.getElementById('eqBloom').value;
  const qText = document.getElementById('eqText').value;
  const qAnswer = document.getElementById('eqAnswer').value;

  const optInputs = document.querySelectorAll('.eq-opt-input');
  let options = null;
  if(qType === 'mcq' || qType === 'single_choice' || qType === 'match'){
    options = Array.from(optInputs).map(inp => inp.value.trim()).filter(val => val.length > 0);
  }

  const btn = document.getElementById('eqSaveBtn');
  btn.disabled = true;
  btn.textContent = 'Saving...';

  fetch(`/question-papers/questions/${qId}`, {
    method: 'PUT',
    headers: {
      'X-CSRF-TOKEN': CSRF,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      question_type: qType,
      question_text: qText,
      answer_text: qAnswer,
      options: options,
      allocated_marks: qMarks,
      difficulty: qDiff,
      bloom_taxonomy: qBloom
    })
  })
  .then(res => res.json())
  .then(data => {
    if(data.success){
      location.reload();
    } else {
      alert('Error updating question: ' + (data.error || 'Unknown error'));
      btn.disabled = false;
      btn.textContent = 'Save Changes';
    }
  })
  .catch(err => {
    alert('Failed to update question: ' + err.message);
    btn.disabled = false;
    btn.textContent = 'Save Changes';
  });
}
</script>

{{-- ── AI Question Generator Modal Markup ────────────────────────────────── --}}
<div id="aiGeneratorModal" class="qm-modal-bg hidden z-50">
  <div class="qm-modal max-w-xl">
    <div class="flex justify-between items-center pb-3 border-b border-slate-100 mb-4">
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center font-bold text-lg">
          <i class="bi bi-magic"></i>
        </div>
        <div>
          <h3 class="font-bold text-slate-800 text-base">AI Section Question Generator</h3>
          <p class="text-xs text-slate-500">Upload paper/document or enter topic to auto-generate section questions</p>
        </div>
      </div>
      <button onclick="closeAiGeneratorModal()" class="text-slate-400 hover:text-slate-600 text-lg"><i class="bi bi-x-lg"></i></button>
    </div>

    <form id="aiGenForm" onsubmit="submitAiGenerator(event)" class="space-y-4">
      <div>
        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
          Upload Reference Document (PDF, DOCX, TXT, Image)
        </label>
        <div class="border-2 border-dashed border-purple-200 hover:border-purple-400 bg-purple-50/40 rounded-xl p-4 text-center cursor-pointer transition relative" onclick="document.getElementById('aiDocFile').click()">
          <input type="file" id="aiDocFile" name="document_file" accept=".txt,.pdf,.docx,.doc,.jpg,.jpeg,.png,.webp" class="hidden" onchange="updateFileName(this)">
          <i class="bi bi-cloud-arrow-up text-2xl text-purple-500"></i>
          <p class="text-xs font-semibold text-purple-700 mt-1" id="aiFileName">Click to select file or drag & drop</p>
          <p class="text-[11px] text-slate-400">Supported: PDF, Word (.docx), Plain Text (.txt), Images</p>
        </div>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-700 mb-1">
          Paste Raw Unformatted Questions / Topic / Instructions
        </label>
        <textarea id="aiPromptText" name="prompt_text" rows="4" class="w-full text-xs p-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none" placeholder="Paste your raw, messy, or unformatted questions here (e.g. copied from chat, notes, or Word). The AI will clean up grammar, format MCQs & options, assign marks, and organize them into proper sections automatically."></textarea>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">Difficulty</label>
          <select id="aiDifficulty" name="difficulty" class="w-full text-xs p-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none bg-white">
            <option value="mixed">Mixed (Balanced)</option>
            <option value="easy">Easy</option>
            <option value="medium">Medium</option>
            <option value="hard">Hard / Challenging</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">Action Mode</label>
          <select id="aiMode" name="mode" class="w-full text-xs p-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none bg-white">
            <option value="append">Append to existing paper</option>
            <option value="replace">Replace existing questions</option>
          </select>
        </div>
      </div>

      <div id="aiGenError" class="hidden text-xs text-rose-600 bg-rose-50 border border-rose-100 rounded-xl p-3"></div>

      <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
        <button type="button" onclick="closeAiGeneratorModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-medium transition">Cancel</button>
        <button type="submit" id="aiSubmitBtn" class="px-5 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-semibold shadow-md transition flex items-center gap-2">
          <i class="bi bi-magic"></i> Generate & Populate Questions
        </button>
      </div>
    </form>
  </div>
</div>

{{-- ── EDIT QUESTION MODAL ────────────────────────────────────────────────── --}}
<div id="editQuestionModal" class="qm-modal-bg hidden z-50">
  <div class="qm-modal max-w-2xl">
    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
      <h3 class="text-base font-bold text-slate-800"><i class="bi bi-pencil-square me-2 text-indigo-600"></i>Edit Question</h3>
      <button onclick="closeEditQuestion()" class="text-slate-400 hover:text-slate-700 text-lg"><i class="bi bi-x-lg"></i></button>
    </div>

    <form id="editQuestionForm" onsubmit="saveEditQuestion(event)" class="space-y-4">
      <input type="hidden" id="eqId">

      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Type</label>
          <select id="eqType" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-400 outline-none" onchange="toggleEditOptions(this.value)">
            @foreach($bankTypes as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Marks</label>
          <input type="number" id="eqMarks" min="0.25" step="0.25" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-400 outline-none">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Difficulty</label>
          <select id="eqDiff" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-400 outline-none">
            @foreach($bankDiffs as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Bloom's Taxonomy</label>
          <select id="eqBloom" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-400 outline-none">
            <option value="">None</option>
            @foreach($blooms as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
          </select>
        </div>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Question Text</label>
        <textarea id="eqText" rows="4" class="w-full border border-slate-200 rounded-xl p-3 text-xs focus:ring-2 focus:ring-indigo-400 outline-none" placeholder="Enter question wording..."></textarea>
      </div>

      {{-- MCQ Options Section --}}
      <div id="eqOptionsSection" class="hidden space-y-2 bg-slate-50 p-3 rounded-xl border border-slate-100">
        <div class="flex items-center justify-between">
          <label class="block text-xs font-semibold text-slate-700">Multiple Choice Options</label>
          <button type="button" onclick="addEditOption()" class="text-xs text-indigo-600 font-semibold hover:underline"><i class="bi bi-plus-circle me-1"></i>Add Option</button>
        </div>
        <div id="eqOptionsContainer" class="space-y-2"></div>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Answer Key / Explanation (Optional)</label>
        <textarea id="eqAnswer" rows="2" class="w-full border border-slate-200 rounded-xl p-2.5 text-xs focus:ring-2 focus:ring-indigo-400 outline-none" placeholder="Sample answer or solution notes..."></textarea>
      </div>

      <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
        <button type="button" onclick="closeEditQuestion()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-medium transition">Cancel</button>
        <button type="submit" id="eqSaveBtn" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-md transition">Save Changes</button>
      </div>
    </form>
  </div>
</div>
@endpush
@endsection
