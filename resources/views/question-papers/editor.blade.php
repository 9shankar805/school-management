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
{{-- Tesseract.js v5 for In-Browser WebAssembly OCR (100% Free, Unlimited) --}}
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
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
/* ── Mobile utilities ──────────────────────────────────────────────────────── */
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

/* ── Tiptap editor ─────────────────────────────────────────────────────────── */
.tiptap-editor { outline:none; min-height:90px; padding:10px; font-size:14px; line-height:1.7; }
.tiptap-editor p { margin:0 0 4px; }
.tiptap-editor h1,.tiptap-editor h2,.tiptap-editor h3 { font-weight:700; margin:6px 0 2px; }
.tiptap-editor ul,.tiptap-editor ol { padding-left:18px; }
.tiptap-editor table { border-collapse:collapse; width:100%; margin:8px 0; }
.tiptap-editor td,.tiptap-editor th { border:1px solid #cbd5e1; padding:4px 8px; }
.tiptap-editor .math-inline { background:#f0f4ff; border-radius:4px; padding:1px 5px; font-family:monospace; color:#3730a3; cursor:pointer; }
/* toolbar with mobile swipe */
.tiptap-toolbar { display:flex; flex-wrap:nowrap; overflow-x:auto; -webkit-overflow-scrolling:touch; gap:2px; padding:6px 8px; background:#f8fafc; border-bottom:1px solid #e2e8f0; border-radius:12px 12px 0 0; }
@media (min-width: 640px) {
  .tiptap-toolbar { flex-wrap:wrap; overflow-x:visible; }
}
.tiptap-toolbar button { min-width:30px; height:30px; border:none; background:transparent; border-radius:6px; font-size:12px; font-weight:600; color:#475569; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; padding:0 6px; transition:background .15s,color .15s; white-space:nowrap; flex-shrink:0; }
.tiptap-toolbar button:hover,.tiptap-toolbar button.active { background:#e0e7ff; color:#4f46e5; }
.tiptap-toolbar .sep { width:1px; background:#e2e8f0; margin:2px 3px; align-self:stretch; flex-shrink:0; }
/* modal backdrop responsive */
.qm-modal-bg { position:fixed; inset:0; background:rgba(0,0,0,.6); z-index:100; display:flex; align-items:flex-start; justify-content:center; padding:12px 8px; overflow-y:auto; -webkit-overflow-scrolling:touch; }
@media (min-width: 640px) {
  .qm-modal-bg { padding: 36px 16px; }
}
.qm-modal { background:#fff; border-radius:18px; padding:16px; width:100%; max-width:700px; box-shadow:0 20px 60px rgba(0,0,0,.25); position:relative; }
@media (min-width: 640px) {
  .qm-modal { border-radius: 24px; padding: 24px; }
}
/* Desmos + GeoGebra iframes */
#desmosFrame, #geogebraFrame, #excalidrawFrame { width:100%; height:320px; border:1px solid #e2e8f0; border-radius:12px; }
@media (min-width: 640px) {
  #desmosFrame, #geogebraFrame, #excalidrawFrame { height:440px; }
}
/* Cropper modal */
#cropperImgWrap { width:100%; height:58vh; max-height:540px; min-height:260px; overflow:hidden; background:#090d16; border-radius:12px; display:flex; align-items:center; justify-content:center; }
@media (min-width: 640px) {
  #cropperImgWrap { height:70vh; max-height:620px; }
}
#cropperImgWrap img { display:block; max-width:100%; max-height:100%; }

/* Full image display for questions, math render, and rich editor */
.math-render img,
.math-content img,
.section-questions-list img,
.tiptap-editor img,
.ProseMirror img,
.re-area img,
#sectionsContainer img,
.q-body img,
.question-text-content img {
  max-width: 100% !important;
  width: auto !important;
  height: auto !important;
  max-height: none !important;
  object-fit: contain;
  border-radius: 8px;
  display: block;
  margin: 10px auto;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  cursor: zoom-in;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.math-render img:hover,
.section-questions-list img:hover,
#sectionsContainer img:hover {
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.16);
  transform: scale(1.005);
}

/* ── Google Lens Scanner ───────────────────────────────────────────────────── */
.lens-scan-line {
  position: absolute;
  left: 0; right: 0;
  height: 3px;
  background: linear-gradient(90deg, transparent, #10b981 30%, #34d399 50%, #10b981 70%, transparent);
  box-shadow: 0 0 16px #10b981, 0 0 30px #34d399;
  animation: lensScanning 2s ease-in-out infinite alternate;
  z-index: 30;
  pointer-events: none;
}
@keyframes lensScanning {
  0% { top: 4%; opacity: 0.85; }
  50% { opacity: 1; }
  100% { top: 96%; opacity: 0.85; }
}

/* Google Lens Cropper Viewfinder Styling */
#lensCropStep {
  position: relative !important;
  width: 100% !important;
  height: 100% !important;
  min-height: 0 !important;
  flex: 1 1 0% !important;
  overflow: hidden !important;
  background: #020617 !important;
}
#lensCropStep.hidden {
  display: none !important;
}
#lensCropperWrap {
  position: absolute !important;
  inset: 0 !important;
  width: 100% !important;
  height: 100% !important;
  overflow: hidden !important;
  background: #020617 !important;
}
#lensCropperWrap .cropper-container {
  position: absolute !important;
  inset: 0 !important;
  width: 100% !important;
  height: 100% !important;
  max-width: 100% !important;
  max-height: 100% !important;
}
#lensCropperImg {
  display: block !important;
  max-width: 100% !important;
  max-height: 100% !important;
  position: absolute !important;
  inset: 0 !important;
  opacity: 0;
}
#lensCropperWrap .cropper-bg {
  background-image: none !important;
  background-color: #020617 !important;
}
#lensCropperWrap .cropper-modal {
  opacity: 0.65 !important;
  background-color: #000 !important;
}
#lensCropperWrap .cropper-view-box {
  outline: 2.5px solid #10b981 !important;
  outline-color: #10b981 !important;
  box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.6), 0 0 24px rgba(16, 185, 129, 0.4) !important;
}
#lensCropperWrap .cropper-dashed {
  border-color: rgba(52, 211, 153, 0.45) !important;
}
#lensCropperWrap .cropper-center:before,
#lensCropperWrap .cropper-center:after {
  background-color: #34d399 !important;
}
#lensCropperWrap .cropper-line {
  background-color: #10b981 !important;
  opacity: 0.5 !important;
}
#lensCropperWrap .cropper-point {
  background-color: #10b981 !important;
  width: 10px !important;
  height: 10px !important;
  opacity: 0.95 !important;
  border: 1.5px solid #ffffff !important;
  border-radius: 2px !important;
}
#lensCropperWrap .cropper-point.point-se {
  width: 13px !important;
  height: 13px !important;
}

/* Google Lens Text Box Highlight */
.lens-text-box {
  position: absolute;
  cursor: pointer;
  border-radius: 4px;
  background: rgba(255, 255, 255, 0.18);
  border: 1.5px solid rgba(255, 255, 255, 0.5);
  transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
  touch-action: manipulation;
  user-select: none;
  -webkit-tap-highlight-color: transparent;
  z-index: 15;
}
.lens-text-box:hover {
  background: rgba(56, 189, 248, 0.32);
  border-color: #38bdf8;
}
.lens-text-box.selected {
  background: rgba(14, 165, 233, 0.44) !important;
  border: 2px solid #38bdf8 !important;
  box-shadow: 0 0 12px rgba(56, 189, 248, 0.65), inset 0 0 8px rgba(14, 165, 233, 0.4);
}

/* Modal full-screen on mobile, spacious split-screen workbench on desktop */
#lensScannerModal.lens-fullscreen-mobile {
  padding: 0 !important;
  align-items: stretch !important;
  overflow: hidden !important;
}
#lensScannerModal .lens-modal-container {
  height: 100dvh !important;
  max-height: 100dvh !important;
  overflow: hidden !important;
}
@media (min-width: 768px) {
  #lensScannerModal.lens-fullscreen-mobile {
    padding: 20px 24px !important;
    align-items: center !important;
    justify-content: center !important;
    background: rgba(10, 15, 29, 0.88) !important;
    backdrop-filter: blur(10px) !important;
    overflow: hidden !important;
  }
  #lensScannerModal .lens-modal-container {
    height: 90vh !important;
    max-height: 880px !important;
    max-width: 1200px !important;
    width: 95vw !important;
    border-radius: 24px !important;
    box-shadow: 0 30px 100px -15px rgba(0, 0, 0, 0.85), 0 0 0 1px rgba(255, 255, 255, 0.1) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    overflow: hidden !important;
  }
}
</style>
@section('content')
<div class="flex-1 flex flex-col overflow-auto">

{{-- ── Top action bar (Mobile-optimized) ─────────────────────────────────── --}}
<div class="sticky top-0 z-40 bg-white border-b border-slate-200 px-3 py-2.5 sm:px-5 sm:py-3 flex flex-col sm:flex-row justify-between sm:items-center gap-2 sm:gap-3 shadow-sm">
  <div class="flex items-center justify-between gap-2 min-w-0">
    <div class="flex items-center gap-2 min-w-0">
      <a href="{{ route('question-papers.index') }}" class="text-slate-400 hover:text-indigo-600 transition p-1"><i class="bi bi-arrow-left-short text-2xl sm:text-xl"></i></a>
      <div class="min-w-0">
        <h1 class="font-bold text-slate-800 text-xs sm:text-sm truncate">{{ $paper->title }}</h1>
        <p class="text-[10px] sm:text-[11px] text-slate-400 truncate">{{ $paper->subject ?? '' }}{{ $paper->subject && $paper->duration ? ' · ' : '' }}{{ $paper->duration ?? '' }} · FM {{ $paper->full_marks ?: $paper->total_marks }}</p>
      </div>
    </div>
    <div class="flex items-center gap-1.5 flex-shrink-0">
      <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $paper->status_badge }}">
        {{ \App\Models\QuestionPaper::STATUSES[$paper->status] }}
      </span>
      <div id="autoSaveIndicator" class="px-2 py-0.5 text-[10px] text-slate-400 bg-slate-50 border border-slate-100 rounded-lg opacity-0 transition-opacity duration-300 flex items-center gap-1">
        <i class="bi bi-cloud-check text-emerald-500"></i> <span class="hidden sm:inline">Saved</span>
      </div>
    </div>
  </div>

  <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-0.5 sm:pb-0 flex-nowrap sm:flex-wrap">
    @if(!$paper->is_locked)
    <button onclick="openLensScannerModal(null, '')" type="button"
       class="px-2.5 sm:px-3.5 py-1.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white rounded-xl text-[11px] sm:text-xs font-semibold shadow-sm transition flex items-center gap-1.5 cursor-pointer whitespace-nowrap flex-shrink-0" title="Free & Unlimited Lens Scanner (100% Free, Zero Limits)">
      <i class="bi bi-camera text-sm"></i> <span>📷 Scan</span>
    </button>
    <button onclick="openAiGeneratorModal()" type="button"
       class="px-2.5 sm:px-3.5 py-1.5 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white rounded-xl text-[11px] sm:text-xs font-semibold shadow-sm transition flex items-center gap-1.5 cursor-pointer whitespace-nowrap flex-shrink-0">
      <i class="bi bi-magic text-sm"></i> <span>AI Gen</span>
    </button>
    @endif
    @if($paper->status==='draft')
    <form method="POST" action="{{ route('question-papers.submit',$paper->id) }}" class="inline flex-shrink-0">
      @csrf
      <button class="px-2.5 sm:px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-[11px] sm:text-xs font-medium transition whitespace-nowrap"><i class="bi bi-send me-1"></i>Submit</button>
    </form>
    @endif
    <a href="{{ route('question-papers.pdf',$paper->id) }}" target="_blank"
       class="px-2.5 sm:px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-[11px] sm:text-xs font-medium transition whitespace-nowrap flex-shrink-0"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</a>
    <a href="{{ route('question-papers.docx',$paper->id) }}"
       class="px-2.5 sm:px-3 py-1.5 bg-indigo-700 hover:bg-indigo-800 text-white rounded-xl text-[11px] sm:text-xs font-medium transition whitespace-nowrap flex-shrink-0"><i class="bi bi-file-earmark-word me-1"></i>DOCX</a>
    <a href="{{ route('question-papers.versions',$paper->id) }}"
       class="px-2.5 sm:px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-[11px] sm:text-xs font-medium transition whitespace-nowrap flex-shrink-0"><i class="bi bi-clock-history me-1"></i>History</a>
  </div>
</div>

<div class="flex-1 p-3 sm:p-5 lg:p-7 max-w-5xl w-full mx-auto">

@include('session-messages')

{{-- ── Paper meta (collapsible) ─────────────────────────────────────────── --}}
<details class="bg-white rounded-xl sm:rounded-2xl border border-slate-100 shadow-sm mb-4 sm:mb-5 p-3.5 sm:p-5" {{ $errors->any() ? 'open' : '' }}>
  <summary class="text-xs sm:text-sm font-semibold text-slate-700 cursor-pointer select-none">
    <i class="bi bi-info-circle me-1 text-indigo-500"></i>Paper Details
    <span class="text-[11px] text-slate-400 font-normal ml-1 sm:ml-2">(tap to expand / collapse)</span>
  </summary>
  <form method="POST" action="{{ route('question-papers.update',$paper->id) }}" class="mt-3.5 sm:mt-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2.5 sm:gap-3">
    @csrf @method('PUT')
    <div class="col-span-1 sm:col-span-2 md:col-span-4">
      <label class="block text-xs text-slate-500 mb-1">Paper Title <span class="text-rose-500">*</span></label>
      <input type="text" name="title" value="{{ old('title', $paper->title) }}" required class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs sm:text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none" placeholder="e.g. 3rd Terminal Examination — C. Math Class 8">
    </div>
    <div><label class="block text-xs text-slate-500 mb-1">Exam Name</label>
      <input type="text" name="exam_name" value="{{ old('exam_name', $paper->exam_name) }}" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs sm:text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none"></div>
    <div><label class="block text-xs text-slate-500 mb-1">Subject</label>
      <input type="text" name="subject" value="{{ old('subject', $paper->subject) }}" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs sm:text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none"></div>
    <div><label class="block text-xs text-slate-500 mb-1">Class Label</label>
      <input type="text" name="class_label" value="{{ old('class_label', $paper->class_label) }}" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs sm:text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none"></div>
    <div><label class="block text-xs text-slate-500 mb-1">Duration</label>
      <input type="text" name="duration" value="{{ old('duration', $paper->duration) }}" placeholder="3 Hours" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs sm:text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none"></div>
    <div><label class="block text-xs text-slate-500 mb-1">Full Marks</label>
      <input type="number" name="full_marks" value="{{ old('full_marks', $paper->full_marks) }}" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs sm:text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none"></div>
    <div><label class="block text-xs text-slate-500 mb-1">Pass Marks</label>
      <input type="number" name="pass_marks" value="{{ old('pass_marks', $paper->pass_marks) }}" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs sm:text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none"></div>
    <div><label class="block text-xs text-slate-500 mb-1">Exam Date</label>
      <input type="date" name="exam_date" value="{{ old('exam_date', $paper->exam_date?->format('Y-m-d')) }}" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs sm:text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none"></div>
    <div><label class="block text-xs text-slate-500 mb-1">Paper Size</label>
      <select name="paper_size" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs sm:text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
        <option value="A4" {{ old('paper_size', $paper->paper_size)=='A4'?'selected':'' }}>A4</option>
        <option value="Letter" {{ old('paper_size', $paper->paper_size)=='Letter'?'selected':'' }}>Letter</option>
      </select></div>
    <div><label class="block text-xs text-slate-500 mb-1">Orientation</label>
      <select name="orientation" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs sm:text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
        <option value="portrait" {{ old('orientation', $paper->orientation)=='portrait'?'selected':'' }}>Portrait</option>
        <option value="landscape" {{ old('orientation', $paper->orientation)=='landscape'?'selected':'' }}>Landscape</option>
      </select></div>
    <div class="col-span-1 sm:col-span-2 md:col-span-4 pt-1">
      <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs sm:text-sm font-semibold transition shadow-sm">Save Details</button>
    </div>
  </form>
</details>

{{-- ── Sections list ──────────────────────────────────────────────────────── --}}
<div id="sectionsContainer" class="space-y-4 sm:space-y-5">
  @foreach($paper->sections as $section)
  <div class="bg-white rounded-xl sm:rounded-2xl border border-slate-100 shadow-sm overflow-hidden" data-section-id="{{ $section->id }}">
    <div class="px-3.5 sm:px-5 py-2.5 sm:py-3 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2 sm:gap-3 bg-white">
      <div class="flex items-center justify-between sm:justify-start gap-2.5 min-w-0">
        <div class="flex items-center gap-2 min-w-0">
          @if($paper->is_editable)
          <span class="section-drag-handle cursor-grab text-slate-300 hover:text-slate-500 active:cursor-grabbing hidden sm:inline-block" title="Drag to reorder section">
            <i class="bi bi-grip-vertical text-base"></i>
          </span>
          @endif
          <span class="w-6 h-6 sm:w-7 sm:h-7 rounded-lg bg-indigo-100 text-indigo-700 text-xs font-bold flex items-center justify-center flex-shrink-0">{{ $loop->iteration }}</span>
          <div class="min-w-0">
            <span class="font-semibold text-slate-800 text-xs sm:text-sm truncate block">{{ $section->title }}</span>
            @if($section->instructions)<span class="text-[10px] sm:text-xs text-slate-400 truncate block">{{ $section->instructions }}</span>@endif
          </div>
        </div>
        <span class="text-[11px] sm:text-xs font-semibold text-indigo-600 bg-indigo-50 px-2.5 py-0.5 rounded-full flex-shrink-0 sm:hidden">{{ $section->total_marks }} m</span>
      </div>

      <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-0.5 sm:pb-0 flex-nowrap sm:flex-wrap">
        <span class="text-xs font-semibold text-indigo-600 bg-indigo-50 px-2.5 py-0.5 rounded-full hidden sm:inline-block flex-shrink-0">{{ $section->total_marks }} marks</span>
        @if($paper->is_editable)
        <button onclick="openLensScannerModal({{ $section->id }}, '{{ addslashes($section->title) }}')" type="button" class="px-2.5 sm:px-3 py-1.5 sm:py-1 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white rounded-lg text-xs font-semibold shadow-sm transition flex items-center gap-1.5 cursor-pointer flex-shrink-0 whitespace-nowrap" title="Free & Unlimited Lens Scanner for {{ $section->title }}">
          <i class="bi bi-camera"></i> <span>Scan</span>
        </button>
        <button onclick="openAddQuestion({{ $section->id }})" class="px-2.5 sm:px-3 py-1.5 sm:py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-medium transition flex-shrink-0 whitespace-nowrap"><i class="bi bi-plus-lg me-1"></i>Question</button>
        <button onclick="openSectionAiModal({{ $section->id }}, '{{ addslashes($section->title) }}')" type="button" class="px-2.5 sm:px-3 py-1.5 sm:py-1 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-xs font-semibold shadow-sm transition flex items-center gap-1.5 flex-shrink-0 whitespace-nowrap" title="Direct paste or AI generate questions for this section">
          <i class="bi bi-magic"></i> AI Question
        </button>
        <button onclick="openBankPicker({{ $section->id }})" class="px-2.5 sm:px-3 py-1.5 sm:py-1 bg-violet-100 hover:bg-violet-200 text-violet-700 rounded-lg text-xs font-medium transition flex-shrink-0 whitespace-nowrap"><i class="bi bi-collection me-1"></i>Bank</button>
        <button onclick="deleteSection({{ $section->id }})" class="p-1.5 text-slate-300 hover:text-rose-500 transition text-xs flex-shrink-0" title="Delete section"><i class="bi bi-trash"></i></button>
        @endif
      </div>
    </div>
    <div class="section-questions-list p-3 sm:p-4 space-y-2.5 sm:space-y-3">
      @foreach($section->questions as $q)
      <div class="p-3 sm:p-3.5 rounded-xl bg-slate-50 border border-slate-100 group transition-all" data-question-id="{{ $q->id }}">
        <div class="flex items-start gap-2 sm:gap-3">
          @if($paper->is_editable)
          <span class="question-drag-handle cursor-grab text-slate-200 hover:text-slate-400 active:cursor-grabbing self-start pt-0.5 flex-shrink-0 hidden sm:inline-block" title="Drag to reorder">
            <i class="bi bi-grip-vertical text-sm"></i>
          </span>
          @endif
          <span class="font-mono text-xs sm:text-sm font-bold text-indigo-500 w-6 sm:w-8 flex-shrink-0 pt-0.5">{{ $q->numbering }}.</span>
          <div class="flex-1 min-w-0">
            <div class="text-xs sm:text-sm text-slate-800 leading-relaxed math-render break-words">{!! $q->question_text !!}</div>
            @if($q->options)
            <ol class="mt-2 space-y-1 pl-4 text-xs sm:text-sm text-slate-600" style="list-style-type:upper-alpha">
              @foreach($q->options as $opt)<li class="math-render break-words">{{ $opt }}</li>@endforeach
            </ol>
            @endif
          </div>
          <div class="flex-shrink-0 text-right ml-2">
            <span class="font-bold text-indigo-600 text-xs sm:text-sm">{{ $q->allocated_marks }}</span>
            <span class="text-[9px] sm:text-[10px] text-slate-400 block sm:inline">m</span>
          </div>
        </div>

        {{-- Meta & Action Buttons Row --}}
        <div class="flex items-center justify-between gap-2 mt-2 pt-2 border-t border-slate-100/80 flex-wrap">
          <div class="flex gap-1.5 flex-wrap items-center">
            <span class="text-[9px] sm:text-[10px] px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700">{{ \App\Models\QuestionBank::QUESTION_TYPES[$q->question_type] ?? $q->question_type }}</span>
            <span class="text-[9px] sm:text-[10px] px-2 py-0.5 rounded-full {{ match($q->difficulty){'easy'=>'bg-emerald-100 text-emerald-700','hard'=>'bg-rose-100 text-rose-700',default=>'bg-amber-100 text-amber-700'} }}">{{ ucfirst($q->difficulty) }}</span>
            @if($q->chapter)<span class="text-[9px] sm:text-[10px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 truncate max-w-[120px]">{{ $q->chapter }}</span>@endif
            @if($q->bloom_taxonomy)<span class="text-[9px] sm:text-[10px] px-2 py-0.5 rounded-full bg-violet-100 text-violet-600">{{ ucfirst($q->bloom_taxonomy) }}</span>@endif
          </div>
          @if($paper->is_editable)
          <div class="flex items-center gap-1">
            <button onclick='openEditQuestion({{ json_encode($q) }})' type="button" class="px-2 py-1 text-slate-500 hover:text-indigo-600 transition rounded-lg hover:bg-indigo-50 text-xs flex items-center gap-1" title="Edit Question">
              <i class="bi bi-pencil-square"></i> <span class="hidden sm:inline">Edit</span>
            </button>
            <button onclick="deleteQuestion({{ $q->id }})" type="button" class="px-2 py-1 text-slate-400 hover:text-rose-600 transition rounded-lg hover:bg-rose-50 text-xs flex items-center gap-1" title="Delete Question">
              <i class="bi bi-trash"></i> <span class="hidden sm:inline">Delete</span>
            </button>
          </div>
          @endif
        </div>
      </div>
      @endforeach
      @if($section->questions->isEmpty())
      <div class="text-center py-5 sm:py-6">
        <p class="text-xs sm:text-sm text-slate-400 mb-3">No questions yet in this section.</p>
        <div class="flex justify-center gap-1.5 sm:gap-2 flex-wrap">
          <button type="button" onclick="openLensScannerModal({{ $section->id }}, '{{ addslashes($section->title) }}')" class="px-3 sm:px-3.5 py-1.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white rounded-xl text-xs font-semibold shadow-sm flex items-center gap-1.5 cursor-pointer"><i class="bi bi-camera"></i>📷 Scan Question (Lens)</button>
          <button type="button" onclick="openAddQuestion({{ $section->id }})" class="px-3 sm:px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-medium"><i class="bi bi-plus-lg me-1"></i>Add Manually</button>
          <button type="button" onclick="openSectionAiModal({{ $section->id }}, '{{ addslashes($section->title) }}')" class="px-3 sm:px-3.5 py-1.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-semibold shadow-sm flex items-center gap-1.5"><i class="bi bi-magic"></i>AI Question</button>
          <button type="button" onclick="openBankPicker({{ $section->id }})" class="px-3 sm:px-3.5 py-1.5 bg-violet-100 hover:bg-violet-200 text-violet-700 rounded-xl text-xs font-medium"><i class="bi bi-collection me-1"></i>Bank</button>
        </div>
      </div>
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
<button onclick="openAddSectionModal()" type="button" class="mt-5 w-full py-3.5 border-2 border-dashed border-slate-200 hover:border-purple-300 text-slate-500 hover:text-purple-600 rounded-2xl text-sm font-semibold transition flex items-center justify-center gap-2 bg-slate-50/50 hover:bg-purple-50/30 shadow-sm">
  <i class="bi bi-plus-circle text-base"></i> Add Section (with Direct AI Paste)
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
<div class="qm-modal max-w-4xl w-full">
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
  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2 mb-4">
    <input type="text" id="bankSearch" placeholder="Search question text…" class="w-full col-span-1 sm:col-span-2 md:col-span-1 border border-slate-200 rounded-xl px-3 py-2 text-xs sm:text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none" oninput="searchBank()">
    <select id="bankSubject" class="w-full border border-slate-200 rounded-xl px-2.5 py-2 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" onchange="searchBank()">
      <option value="">All subjects</option>
      @foreach($subjects as $s)<option value="{{ $s }}">{{ $s }}</option>@endforeach
    </select>
    <select id="bankType" class="w-full border border-slate-200 rounded-xl px-2.5 py-2 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" onchange="searchBank()">
      <option value="">All types</option>
      @foreach($bankTypes as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
    </select>
    <select id="bankDiff" class="w-full border border-slate-200 rounded-xl px-2.5 py-2 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" onchange="searchBank()">
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

// ── Section actions & AI Direct Paste ───────────────────────────────────────
function addSection(){
  openAddSectionModal();
}

function openAddSectionModal(){
  const modal = document.getElementById('addSectionModal');
  if(!modal) return;
  const err = document.getElementById('addSecError');
  if(err) err.classList.add('hidden');
  modal.classList.remove('hidden');
}

function closeAddSectionModal(){
  const modal = document.getElementById('addSectionModal');
  if(modal) modal.classList.add('hidden');
}

function setSecTitle(title){
  const el = document.getElementById('newSecTitle');
  if(el) el.value = title;
}

function submitAddSectionForm(e){
  e.preventDefault();
  const form = document.getElementById('addSectionForm');
  const errDiv = document.getElementById('addSecError');
  const btn = document.getElementById('addSecSubmitBtn');

  if(errDiv) {
    errDiv.classList.add('hidden');
    errDiv.textContent = '';
  }

  const formData = new FormData(form);
  const promptText = (formData.get('prompt_text') || '').trim();

  btn.disabled = true;
  if(promptText) {
    btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> Creating section & generating questions with AI...';
  } else {
    btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> Creating section...';
  }

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
      if(errDiv) {
        errDiv.textContent = data.error || 'Failed to create section.';
        errDiv.classList.remove('hidden');
      }
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-check-circle"></i> Create Section';
    }
  })
  .catch(err => {
    if(errDiv) {
      errDiv.textContent = 'Server error: ' + err.message;
      errDiv.classList.remove('hidden');
    }
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-check-circle"></i> Create Section';
  });
}

function openSectionAiModal(sectionId, sectionTitle){
  const modal = document.getElementById('sectionAiModal');
  if(!modal) return;
  document.getElementById('secAiTargetId').value = sectionId;
  document.getElementById('secAiTargetTitle').textContent = sectionTitle;
  const promptEl = document.getElementById('secAiPromptText');
  if(promptEl) promptEl.value = '';
  const errDiv = document.getElementById('secAiError');
  if(errDiv) errDiv.classList.add('hidden');
  modal.classList.remove('hidden');
}

function closeSectionAiModal(){
  const modal = document.getElementById('sectionAiModal');
  if(modal) modal.classList.add('hidden');
}

function submitSectionAiForm(e){
  e.preventDefault();
  const form = document.getElementById('sectionAiForm');
  const errDiv = document.getElementById('secAiError');
  const btn = document.getElementById('secAiSubmitBtn');

  if(errDiv) {
    errDiv.classList.add('hidden');
    errDiv.textContent = '';
  }

  const formData = new FormData(form);
  btn.disabled = true;
  btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> Generating questions with AI...';

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
      if(errDiv) {
        errDiv.textContent = data.error || 'Failed to generate questions.';
        errDiv.classList.remove('hidden');
      }
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-magic"></i> Generate & Add to Section';
    }
  })
  .catch(err => {
    if(errDiv) {
      errDiv.textContent = 'Server error: ' + err.message;
      errDiv.classList.remove('hidden');
    }
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-magic"></i> Generate & Add to Section';
  });
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

  // Extract images from question_text if any
  const qHtml = q.question_text || '';
  const imgMatches = qHtml.match(/<img[^>]*>/gi) || [];
  currentEditingQ.extractedImages = imgMatches;

  const textWithoutImg = qHtml.replace(/<img[^>]*>/gi, '').replace(/<p[^>]*>/gi, '').replace(/<\/p>/gi, '\n').replace(/<br\s*\/?>/gi, '\n').replace(/<[^>]*>/g, '').trim();
  document.getElementById('eqText').value = textWithoutImg || qHtml;
  document.getElementById('eqAnswer').value = q.answer_text || '';

  // Display full size image preview in edit modal
  const imgWrap = document.getElementById('eqImagePreviewWrap');
  const imgContent = document.getElementById('eqImagePreviewContent');
  if (imgWrap && imgContent) {
    if (imgMatches.length > 0) {
      imgContent.innerHTML = imgMatches.map(imgTag => `<div class="max-w-full text-center my-1">${imgTag}</div>`).join('');
      imgWrap.classList.remove('hidden');
    } else {
      imgContent.innerHTML = '';
      imgWrap.classList.add('hidden');
    }
  }

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
  let qText = document.getElementById('eqText').value.trim();
  if (currentEditingQ && currentEditingQ.extractedImages && currentEditingQ.extractedImages.length > 0) {
    if (!qText.includes('<img')) {
      qText = qText + '<br>' + currentEditingQ.extractedImages.join(' ');
    }
  }
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

// ── Free & Unlimited Google Lens Scanner (In-Browser Tesseract WASM + Pollinations AI) ──
let lensCropper = null;
let currentLensImageFile = null;
let lensWebcamStream = null;
let lensDetectedLines = [];

function openLensScannerModal(sectionId, sectionTitle) {
  const modal = document.getElementById('lensScannerModal');
  if (!modal) return;
  modal.scrollTop = 0;

  const select = document.getElementById('lensTargetSectionSelect');
  if (select && sectionId) {
    select.value = sectionId;
  }
  updateLensActiveSection();

  // Clear previous alerts
  const succ = document.getElementById('lensSuccessAlert');
  const err = document.getElementById('lensErrorAlert');
  if (succ) succ.classList.add('hidden');
  if (err) err.classList.add('hidden');

  modal.classList.remove('hidden');
}

function updateLensActiveSection() {
  const sel = document.getElementById('lensTargetSectionSelect');
  const label = document.getElementById('lensActiveSectionLabel');
  if (sel && label) {
    label.textContent = sel.selectedOptions[0]?.dataset.title || sel.selectedOptions[0]?.text || 'Section';
  }
}

function closeLensScannerModal() {
  const modal = document.getElementById('lensScannerModal');
  if (modal) modal.classList.add('hidden');
  stopLensWebcam();
}

function resetLensToUpload() {
  if (lensCropper) {
    lensCropper.destroy();
    lensCropper = null;
  }
  zoomLensInteractive(0);
  document.getElementById('lensUploadCards').classList.remove('hidden');
  document.getElementById('lensCropStep').classList.add('hidden');
  document.getElementById('lensCropBottomBar').classList.add('hidden');
  document.getElementById('lensInteractiveStep').classList.add('hidden');
  document.getElementById('lensSelectBottomBar').classList.add('hidden');
  document.getElementById('lensOcrProgressWrap').classList.add('hidden');
  document.getElementById('lensScanLaser').classList.add('hidden');
  const sub = document.getElementById('lensTopSubText');
  if (sub) sub.textContent = 'Snap photo, select question text, and auto-add';
}

function handleLensFileInput(e) {
  const file = e.target.files && e.target.files[0];
  if (file) {
    loadLensImage(file);
  }
}

function selectFullLensCrop() {
  if (!lensCropper) return;
  const canvasData = lensCropper.getCanvasData();
  lensCropper.setCropBoxData({
    left: canvasData.left,
    top: canvasData.top,
    width: canvasData.width,
    height: canvasData.height
  });
}

function loadLensImage(fileOrBlob) {
  currentLensImageFile = fileOrBlob;
  const reader = new FileReader();
  reader.onload = function(evt) {
    const img = document.getElementById('lensCropperImg');
    const modal = document.getElementById('lensScannerModal');
    if (modal) modal.scrollTop = 0;

    document.getElementById('lensUploadCards').classList.add('hidden');
    document.getElementById('lensCropStep').classList.remove('hidden');
    document.getElementById('lensCropBottomBar').classList.remove('hidden');
    document.getElementById('lensInteractiveStep').classList.add('hidden');
    document.getElementById('lensSelectBottomBar').classList.add('hidden');
    document.getElementById('lensOcrProgressWrap').classList.add('hidden');
    document.getElementById('lensScanLaser').classList.add('hidden');
    const sub = document.getElementById('lensTopSubText');
    if (sub) sub.textContent = 'Drag crop box over the question';

    if (lensCropper) {
      lensCropper.destroy();
      lensCropper = null;
    }

    img.onload = function() {
      requestAnimationFrame(() => {
        if (lensCropper) {
          lensCropper.destroy();
          lensCropper = null;
        }
        lensCropper = new Cropper(img, {
          viewMode: 1,
          dragMode: 'crop',
          autoCropArea: 1,
          movable: true,
          zoomable: true,
          rotatable: true,
          scalable: true,
          responsive: true,
          restore: false,
          ready: function() {
            // Guarantee full-size crop box covering 100% of the image
            selectFullLensCrop();
          }
        });
      });
    };

    img.src = evt.target.result;
  };
  reader.readAsDataURL(fileOrBlob);
}

function lensCropperAction(act) {
  if (!lensCropper) return;
  if (act === 'rotateLeft') lensCropper.rotate(-90);
  else if (act === 'rotateRight') lensCropper.rotate(90);
  else if (act === 'reset') {
    lensCropper.reset();
    setTimeout(selectFullLensCrop, 30);
  }
  else if (act === 'zoomIn') lensCropper.zoom(0.1);
  else if (act === 'zoomOut') lensCropper.zoom(-0.1);
}

// In-Browser OCR execution using Tesseract.js WebAssembly
async function runLensOcr() {
  if (!lensCropper) return;

  const scanBtn = document.getElementById('lensScanBtn');
  const progressWrap = document.getElementById('lensOcrProgressWrap');
  const statusText = document.getElementById('lensOcrStatusText');
  const percentText = document.getElementById('lensOcrPercentText');
  const progressBar = document.getElementById('lensOcrProgressBar');
  const laser = document.getElementById('lensScanLaser');
  const errAlert = document.getElementById('lensErrorAlert');

  if (errAlert) errAlert.classList.add('hidden');
  scanBtn.disabled = true;
  scanBtn.classList.add('opacity-60');
  progressWrap.classList.remove('hidden');
  laser.classList.remove('hidden');

  const lang = document.getElementById('lensOcrLang').value || 'eng';
  const boostContrast = document.getElementById('lensHighContrast').checked;

  try {
    let canvas = lensCropper.getCroppedCanvas({
      maxWidth: 2400,
      maxHeight: 2400,
      fillColor: '#fff',
      imageSmoothingQuality: 'high',
    });

    if (boostContrast) {
      canvas = getEnhancedLensCanvas(canvas, 1.25);
    }

    statusText.innerHTML = '<i class="bi bi-cpu animate-spin text-emerald-400"></i> Initializing WebAssembly OCR engine...';
    progressBar.style.width = '10%';
    percentText.textContent = '10%';

    const result = await Tesseract.recognize(
      canvas,
      lang,
      {
        logger: m => {
          if (m && m.status) {
            let pct = Math.round((m.progress || 0) * 100);
            if (m.status === 'recognizing text') {
              statusText.innerHTML = `<i class="bi bi-eye animate-pulse text-emerald-400"></i> Reading text with WebAssembly (${pct}%)...`;
              progressBar.style.width = Math.max(10, pct) + '%';
              percentText.textContent = pct + '%';
            } else if (m.status && m.status.includes('loading')) {
              statusText.innerHTML = `<i class="bi bi-cloud-arrow-down text-emerald-400"></i> Loading language weights (${pct}%)...`;
              progressBar.style.width = Math.max(5, Math.round(pct * 0.4)) + '%';
              percentText.textContent = Math.round(pct * 0.4) + '%';
            } else {
              statusText.textContent = m.status;
            }
          }
        }
      }
    );

    const fullText = (result.data.text || '').trim();
    if (!fullText) {
      throw new Error('No text detected in the cropped area. Please drag the crop box directly over the question text and try again.');
    }

    // Set cropped image for interactive Google Lens text selection
    const croppedDataUrl = canvas.toDataURL('image/jpeg', 0.92);
    const interactiveImg = document.getElementById('lensInteractiveImg');
    interactiveImg.src = croppedDataUrl;

    // Collect line objects with bounding boxes
    let lines = [];
    if (result.data.lines && result.data.lines.length > 0) {
      lines = result.data.lines.filter(l => l.text && l.text.trim().length > 0);
    }
    // Fallback if Tesseract didn't return bounding lines
    if (lines.length === 0) {
      const splitLines = fullText.split('\n').map(s => s.trim()).filter(Boolean);
      const rowHeight = canvas.height / Math.max(1, splitLines.length);
      lines = splitLines.map((t, idx) => ({
        text: t,
        bbox: {
          x0: 5,
          y0: Math.round(idx * rowHeight),
          x1: canvas.width - 5,
          y1: Math.round((idx + 1) * rowHeight - 2)
        }
      }));
    }

    lensDetectedLines = lines;

    // Switch view to Step 3: Google Lens Interactive Text Selection
    document.getElementById('lensCropStep').classList.add('hidden');
    document.getElementById('lensCropBottomBar').classList.add('hidden');
    document.getElementById('lensInteractiveStep').classList.remove('hidden');
    document.getElementById('lensSelectBottomBar').classList.remove('hidden');
    const sub = document.getElementById('lensTopSubText');
    if (sub) sub.textContent = 'Google Lens: Tap text on image to select';

    // Render interactive overlay bounding boxes over the image
    renderLensInteractiveBoxes(canvas.width, canvas.height);

  } catch (err) {
    if (errAlert) {
      errAlert.textContent = 'OCR Error: ' + err.message;
      errAlert.classList.remove('hidden');
    }
    alert(err.message);
  } finally {
    scanBtn.disabled = false;
    scanBtn.classList.remove('opacity-60');
    laser.classList.add('hidden');
    progressWrap.classList.add('hidden');
  }
}

function renderLensInteractiveBoxes(canvasW, canvasH) {
  const layer = document.getElementById('lensBoxesLayer');
  if (!layer) return;
  layer.innerHTML = '';

  lensDetectedLines.forEach((line, idx) => {
    const box = document.createElement('div');
    box.className = 'lens-text-box selected'; // Selected by default
    box.dataset.index = idx;
    box.title = line.text.trim();

    const x0 = (line.bbox.x0 / canvasW) * 100;
    const y0 = (line.bbox.y0 / canvasH) * 100;
    const w = Math.max(2, ((line.bbox.x1 - line.bbox.x0) / canvasW) * 100);
    const h = Math.max(2, ((line.bbox.y1 - line.bbox.y0) / canvasH) * 100);

    box.style.left = `${x0}%`;
    box.style.top = `${y0}%`;
    box.style.width = `${w}%`;
    box.style.height = `${h}%`;

    box.onclick = function(e) {
      e.stopPropagation();
      this.classList.toggle('selected');
      updateLensSelectedText();
    };

    layer.appendChild(box);
  });

  updateLensSelectedText();
}

let lensInteractiveZoom = 1;
function zoomLensInteractive(delta) {
  if (delta === 0) {
    lensInteractiveZoom = 1;
  } else {
    lensInteractiveZoom = Math.min(3, Math.max(0.5, Math.round((lensInteractiveZoom + delta) * 10) / 10));
  }
  const wrapper = document.getElementById('lensInteractiveWrapper');
  if (wrapper) {
    wrapper.style.transform = `scale(${lensInteractiveZoom})`;
    wrapper.style.transformOrigin = 'center center';
  }
  const zoomIndicator = document.getElementById('lensZoomIndicator');
  if (zoomIndicator) {
    zoomIndicator.textContent = Math.round(lensInteractiveZoom * 100) + '%';
  }
}

function toggleLensFullMode() {
  const bar = document.getElementById('lensSelectBottomBar');
  const btn = document.getElementById('lensFullModeBtn');
  const txt = document.getElementById('lensFullModeText');
  if (!bar) return;
  const isHidden = bar.classList.contains('hidden');
  if (isHidden) {
    bar.classList.remove('hidden');
    if (txt) txt.textContent = 'Full Image';
    if (btn) {
      btn.classList.remove('bg-sky-600', 'text-white');
      btn.classList.add('bg-slate-800', 'text-slate-300');
    }
  } else {
    bar.classList.add('hidden');
    if (txt) txt.textContent = 'Split View';
    if (btn) {
      btn.classList.add('bg-sky-600', 'text-white');
      btn.classList.remove('bg-slate-800', 'text-slate-300');
    }
  }
}

function copyLensExtractedText() {
  const ta = document.getElementById('lensExtractedText');
  if (!ta || !ta.value) return;
  navigator.clipboard.writeText(ta.value).then(() => {
    const btn = document.getElementById('lensCopyBtn');
    if (btn) {
      const orig = btn.innerHTML;
      btn.innerHTML = '<i class="bi bi-check2 text-emerald-400"></i> Copied';
      setTimeout(() => { btn.innerHTML = orig; }, 1500);
    }
  });
}

function escapeHtml(str) {
  const div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}

function onLensTextInput() {
  const ta = document.getElementById('lensExtractedText');
  const previewWrap = document.getElementById('lensMathPreviewWrap');
  const preview = document.getElementById('lensMathPreview');
  if (!ta || !previewWrap || !preview) return;

  const val = ta.value;
  const hasMath = /[\\$^_\{\}]|frac|sqrt|times|div|pm|alpha|beta|theta|pi|int|sum/.test(val);
  if (hasMath && val.trim().length > 0) {
    previewWrap.classList.remove('hidden');
    preview.innerHTML = escapeHtml(val).replace(/\n/g, '<br>');
    if (typeof window.renderAllMath === 'function') {
      window.renderAllMath(previewWrap);
    }
  } else {
    if (!previewWrap.dataset.manualOpen) {
      previewWrap.classList.add('hidden');
    }
  }
}

function toggleLensMathPreview() {
  const previewWrap = document.getElementById('lensMathPreviewWrap');
  if (!previewWrap) return;
  if (previewWrap.classList.contains('hidden')) {
    previewWrap.classList.remove('hidden');
    previewWrap.dataset.manualOpen = 'true';
    onLensTextInput();
  } else {
    previewWrap.classList.add('hidden');
    delete previewWrap.dataset.manualOpen;
  }
}

function updateLensSelectedText() {
  const selectedBoxes = Array.from(document.querySelectorAll('#lensBoxesLayer .lens-text-box.selected'));
  const indices = selectedBoxes.map(b => parseInt(b.dataset.index, 10)).sort((a,b) => a - b);
  const selectedTexts = indices.map(i => lensDetectedLines[i] ? lensDetectedLines[i].text.trim() : '').filter(Boolean);

  const combined = selectedTexts.join('\n');
  const extractedArea = document.getElementById('lensExtractedText');
  if (extractedArea) extractedArea.value = combined;

  const wordCount = combined ? combined.split(/\s+/).filter(Boolean).length : 0;
  const statsEl = document.getElementById('lensSelectionStats');
  if (statsEl) {
    if (selectedBoxes.length === 0) {
      statsEl.textContent = '0 lines selected (tap image to select)';
      statsEl.className = 'text-xs font-bold text-amber-400 truncate';
    } else {
      statsEl.textContent = `${selectedBoxes.length} line(s) selected (${wordCount} words)`;
      statsEl.className = 'text-xs font-bold text-sky-400 truncate';
    }
  }

  updateLensActiveSection();
  onLensTextInput();
}

function selectAllLensBoxes() {
  document.querySelectorAll('#lensBoxesLayer .lens-text-box').forEach(b => b.classList.add('selected'));
  updateLensSelectedText();
}

function clearAllLensBoxes() {
  document.querySelectorAll('#lensBoxesLayer .lens-text-box').forEach(b => b.classList.remove('selected'));
  updateLensSelectedText();
}

function returnToLensCrop() {
  document.getElementById('lensCropStep').classList.remove('hidden');
  document.getElementById('lensCropBottomBar').classList.remove('hidden');
  document.getElementById('lensInteractiveStep').classList.add('hidden');
  document.getElementById('lensSelectBottomBar').classList.add('hidden');
  const sub = document.getElementById('lensTopSubText');
  if (sub) sub.textContent = 'Drag crop box over the question';
  if (lensCropper) {
    lensCropper.resize();
  }
}

function toggleLensTextEditor() {
  const ta = document.getElementById('lensExtractedText');
  const label = document.getElementById('lensEditBtnLabel');
  if (!ta) return;
  if (ta.rows <= 3) {
    ta.rows = 7;
    if (label) label.textContent = 'Compact';
  } else {
    ta.rows = 3;
    if (label) label.textContent = 'Expand';
  }
}

// Enhance canvas contrast for better OCR accuracy
function getEnhancedLensCanvas(originalCanvas, contrastFactor = 1.25) {
  const c = document.createElement('canvas');
  c.width = originalCanvas.width;
  c.height = originalCanvas.height;
  const ctx = c.getContext('2d');
  ctx.drawImage(originalCanvas, 0, 0);

  try {
    const imgData = ctx.getImageData(0, 0, c.width, c.height);
    const d = imgData.data;
    const factor = (259 * (contrastFactor * 255 + 255)) / (255 * (259 - contrastFactor * 255));
    for (let i = 0; i < d.length; i += 4) {
      const gray = 0.299 * d[i] + 0.587 * d[i+1] + 0.114 * d[i+2];
      const contrasted = Math.max(0, Math.min(255, factor * (gray - 128) + 128));
      d[i] = contrasted;
      d[i+1] = contrasted;
      d[i+2] = contrasted;
    }
    ctx.putImageData(imgData, 0, 0);
  } catch (e) {
    console.warn('Contrast enhancement skipped:', e);
  }
  return c;
}

// Action 1: Free AI Clean-up & Auto-Add (Pollinations AI via backend)
function submitLensAiAdd() {
  const text = document.getElementById('lensExtractedText').value.trim();
  const select = document.getElementById('lensTargetSectionSelect');
  const sectionId = select.value;
  const sectionTitle = select.selectedOptions[0]?.dataset.title || select.selectedOptions[0]?.text || 'Selected Section';
  const btn = document.getElementById('lensAiAddBtn');
  const errAlert = document.getElementById('lensErrorAlert');
  const successAlert = document.getElementById('lensSuccessAlert');
  const successMsg = document.getElementById('lensSuccessMsg');

  if (!text) {
    alert('Please ensure question text is recognized before adding.');
    return;
  }

  errAlert.classList.add('hidden');
  successAlert.classList.add('hidden');

  btn.disabled = true;
  btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> Formatting with Free AI...';

  const formData = new FormData();
  formData.append('section_id', sectionId);
  formData.append('prompt_text', text);
  formData.append('difficulty', 'mixed');

  fetch(`/question-papers/${PAPER_ID}/ai-generate`, {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': CSRF },
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      successMsg.innerHTML = `<i class="bi bi-check-circle-fill text-emerald-600 text-sm"></i> Successfully added ${data.questions_created || 1} question(s) into <strong>${sectionTitle}</strong>!`;
      successAlert.classList.remove('hidden');

      if (data.questions && data.questions.length) {
        insertQuestionsIntoSectionDom(sectionId, data.questions, data.section_total_marks);
      } else {
        setTimeout(() => location.reload(), 1200);
      }
    } else {
      errAlert.textContent = data.error || 'Failed to auto-format question.';
      errAlert.classList.remove('hidden');
    }
  })
  .catch(err => {
    errAlert.textContent = 'Server error: ' + err.message;
    errAlert.classList.remove('hidden');
  })
  .finally(() => {
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-magic text-sm text-purple-200"></i> <span>✨ Auto-Format & Add (Free AI)</span>';
  });
}

// Action 2: Direct Raw Add
function submitLensDirectAdd() {
  const text = document.getElementById('lensExtractedText').value.trim();
  const select = document.getElementById('lensTargetSectionSelect');
  const sectionId = select.value;
  const sectionTitle = select.selectedOptions[0]?.dataset.title || select.selectedOptions[0]?.text || 'Selected Section';
  const btn = document.getElementById('lensDirectAddBtn');
  const errAlert = document.getElementById('lensErrorAlert');
  const successAlert = document.getElementById('lensSuccessAlert');
  const successMsg = document.getElementById('lensSuccessMsg');

  if (!text) {
    alert('Please ensure question text is recognized before adding.');
    return;
  }

  errAlert.classList.add('hidden');
  successAlert.classList.add('hidden');

  btn.disabled = true;
  btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> Adding...';

  fetch(`/question-papers/sections/${sectionId}/questions`, {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': CSRF,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      question_type: 'short_answer',
      question_text: text,
      allocated_marks: 2,
      difficulty: 'medium'
    })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      successMsg.innerHTML = `<i class="bi bi-check-circle-fill text-emerald-600 text-sm"></i> Question added directly to <strong>${sectionTitle}</strong>!`;
      successAlert.classList.remove('hidden');

      if (data.question) {
        insertQuestionsIntoSectionDom(sectionId, [data.question]);
      } else {
        setTimeout(() => location.reload(), 1000);
      }
    } else {
      errAlert.textContent = data.error || 'Failed to add question.';
      errAlert.classList.remove('hidden');
    }
  })
  .catch(err => {
    errAlert.textContent = 'Server error: ' + err.message;
    errAlert.classList.remove('hidden');
  })
  .finally(() => {
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-plus-lg"></i> Direct Add';
  });
}

// Action 3: Move crop box to next question on the same page
function prepareNextQuestionCrop() {
  returnToLensCrop();
  const succ = document.getElementById('lensSuccessAlert');
  if (succ) succ.classList.add('hidden');
  const err = document.getElementById('lensErrorAlert');
  if (err) err.classList.add('hidden');
  const ta = document.getElementById('lensExtractedText');
  if (ta) ta.value = '';
}

// Dynamically insert question cards into section DOM and trigger math typesetting
function insertQuestionsIntoSectionDom(sectionId, questions, newTotalMarks) {
  const secEl = document.querySelector(`[data-section-id="${sectionId}"]`);
  if (!secEl) return;

  if (newTotalMarks !== undefined) {
    const marksBadge = secEl.querySelector('.bg-indigo-50');
    if (marksBadge) marksBadge.textContent = newTotalMarks + ' marks';
  }

  const qWrap = secEl.querySelector('.section-questions-list') || secEl.querySelector('.space-y-2\\.5') || secEl.querySelector('.space-y-3') || secEl.querySelector('.p-4.space-y-3') || secEl.querySelector('.p-3');
  if (!qWrap) return;

  // Remove empty state message if present
  const emptyState = qWrap.querySelector('.text-center.py-6');
  if (emptyState) emptyState.remove();

  questions.forEach(q => {
    const card = document.createElement('div');
    card.className = 'flex gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100 group transition-all duration-300 ring-2 ring-emerald-400/50';
    card.setAttribute('data-question-id', q.id);

    let optionsHtml = '';
    if (q.options && Array.isArray(q.options) && q.options.length) {
      optionsHtml = `<ol class="mt-2 space-y-0.5 pl-4 text-sm text-slate-600" style="list-style-type:upper-alpha">` +
        q.options.map(opt => `<li class="math-render">${typeof opt === 'object' ? (opt.text || JSON.stringify(opt)) : opt}</li>`).join('') +
        `</ol>`;
    }

    card.innerHTML = `
      <span class="font-mono text-sm font-bold text-indigo-500 w-8 flex-shrink-0 pt-0.5">${q.numbering ? q.numbering + '.' : '•'}</span>
      <div class="flex-1 min-w-0">
        <div class="text-sm text-slate-800 leading-relaxed math-render">${q.question_text}</div>
        ${optionsHtml}
        <div class="flex gap-2 mt-2 flex-wrap">
          <span class="text-[10px] px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700">${q.question_type}</span>
          <span class="text-[10px] px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">${q.difficulty || 'medium'}</span>
          ${q.bloom_taxonomy ? `<span class="text-[10px] px-2 py-0.5 rounded-full bg-violet-100 text-violet-600">${q.bloom_taxonomy}</span>` : ''}
        </div>
      </div>
      <div class="flex-shrink-0 text-right">
        <p class="font-bold text-indigo-600 text-sm">${q.allocated_marks}</p>
        <p class="text-[10px] text-slate-400">marks</p>
        <div class="flex items-center gap-1 mt-1 justify-end">
          <button onclick="deleteQuestion(${q.id})" type="button" class="p-1.5 text-slate-400 hover:text-rose-600 transition rounded-lg hover:bg-rose-50" title="Delete Question">
            <i class="bi bi-trash text-xs"></i>
          </button>
        </div>
      </div>
    `;

    qWrap.appendChild(card);
  });

  // Re-run math typesetting for the new questions
  if (typeof window.renderAllMath === 'function') {
    window.renderAllMath(secEl);
  }
}

// Webcam stream controls for desktop
async function startLensWebcam() {
  const video = document.getElementById('lensWebcamVideo');
  const wrap = document.getElementById('lensWebcamWrap');
  try {
    lensWebcamStream = await navigator.mediaDevices.getUserMedia({
      video: { facingMode: 'environment', width: { ideal: 1920 }, height: { ideal: 1080 } }
    });
    video.srcObject = lensWebcamStream;
    wrap.classList.remove('hidden');
  } catch (err) {
    alert('Webcam access error: ' + err.message);
  }
}

function stopLensWebcam() {
  if (lensWebcamStream) {
    lensWebcamStream.getTracks().forEach(track => track.stop());
    lensWebcamStream = null;
  }
  const wrap = document.getElementById('lensWebcamWrap');
  if (wrap) wrap.classList.add('hidden');
}

function captureLensWebcam() {
  const video = document.getElementById('lensWebcamVideo');
  if (!video || !lensWebcamStream) return;
  const canvas = document.createElement('canvas');
  canvas.width = video.videoWidth || 1280;
  canvas.height = video.videoHeight || 720;
  const ctx = canvas.getContext('2d');
  ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
  canvas.toBlob(blob => {
    stopLensWebcam();
    loadLensImage(blob);
  }, 'image/jpeg', 0.95);
}

// Clipboard Paste listener (Ctrl + V)
window.addEventListener('paste', function(e) {
  const modal = document.getElementById('lensScannerModal');
  if (!modal || modal.classList.contains('hidden')) return;
  const items = (e.clipboardData || e.originalEvent.clipboardData)?.items;
  if (!items) return;
  for (let item of items) {
    if (item.type && item.type.indexOf('image') !== -1) {
      const blob = item.getAsFile();
      if (blob) {
        loadLensImage(blob);
        e.preventDefault();
        break;
      }
    }
  }
});

// Desktop Keyboard shortcuts: Esc to close, Enter to scan, Ctrl+Enter to auto-add
window.addEventListener('keydown', function(e) {
  const modal = document.getElementById('lensScannerModal');
  if (!modal || modal.classList.contains('hidden')) return;

  // Esc to close
  if (e.key === 'Escape') {
    closeLensScannerModal();
    return;
  }

  // Enter to scan when in crop view (unless typing in an input/textarea)
  if (e.key === 'Enter' && !e.ctrlKey && !e.metaKey) {
    const cropStep = document.getElementById('lensCropStep');
    if (cropStep && !cropStep.classList.contains('hidden')) {
      const activeTag = document.activeElement?.tagName?.toLowerCase();
      if (activeTag !== 'input' && activeTag !== 'textarea') {
        e.preventDefault();
        runLensOcr();
      }
    }
  }

  // Ctrl+Enter or Cmd+Enter to Auto-Format & Add
  if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
    const interactiveStep = document.getElementById('lensInteractiveStep');
    if (interactiveStep && !interactiveStep.classList.contains('hidden')) {
      e.preventDefault();
      submitLensAiAdd();
    }
  }
});

// Drag and drop image upload handlers for desktop dropzone
document.addEventListener('DOMContentLoaded', function() {
  const dropZone = document.getElementById('lensDropZone');
  if (dropZone) {
    ['dragenter', 'dragover'].forEach(evtName => {
      dropZone.addEventListener(evtName, e => {
        e.preventDefault();
        e.stopPropagation();
        dropZone.classList.add('border-emerald-400', 'bg-slate-900/90');
      });
    });
    ['dragleave', 'drop'].forEach(evtName => {
      dropZone.addEventListener(evtName, e => {
        e.preventDefault();
        e.stopPropagation();
        dropZone.classList.remove('border-emerald-400', 'bg-slate-900/90');
      });
    });
    dropZone.addEventListener('drop', e => {
      const dt = e.dataTransfer;
      const files = dt && dt.files;
      if (files && files[0] && files[0].type && files[0].type.startsWith('image/')) {
        loadLensImage(files[0]);
      }
    });
  }
});
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

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
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

      <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 pt-2 border-t border-slate-100">
        <button type="button" onclick="closeAiGeneratorModal()" class="w-full sm:w-auto px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-medium transition text-center">Cancel</button>
        <button type="submit" id="aiSubmitBtn" class="w-full sm:w-auto px-5 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-semibold shadow-md transition flex items-center justify-center gap-2">
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

      {{-- Question Image Preview (Full Size) --}}
      <div id="eqImagePreviewWrap" class="hidden space-y-1.5 bg-slate-50 p-3 rounded-xl border border-slate-200">
        <div class="flex items-center justify-between text-xs font-semibold text-slate-700">
          <span><i class="bi bi-image me-1 text-indigo-500"></i>Question Image (Full Size)</span>
          <span class="text-[10px] font-normal text-slate-400">Preserved automatically</span>
        </div>
        <div id="eqImagePreviewContent" class="overflow-auto max-h-[260px] p-2 bg-white rounded-lg border border-slate-200/80"></div>
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

{{-- ── ADD SECTION MODAL (WITH DIRECT AI PASTE) ────────────────────────── --}}
<div id="addSectionModal" class="qm-modal-bg hidden" style="z-index: 9999;" onclick="if(event.target===this)closeAddSectionModal()">
  <div class="qm-modal max-w-xl">
    <div class="flex justify-between items-center pb-3 border-b border-slate-100 mb-4">
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center font-bold text-lg">
          <i class="bi bi-plus-square"></i>
        </div>
        <div>
          <h3 class="font-bold text-slate-800 text-base">Create New Section</h3>
          <p class="text-xs text-slate-500">Create a section and optionally direct-paste questions to auto-populate</p>
        </div>
      </div>
      <button onclick="closeAddSectionModal()" class="text-slate-400 hover:text-slate-600 text-lg"><i class="bi bi-x-lg"></i></button>
    </div>

    <form id="addSectionForm" onsubmit="submitAddSectionForm(event)" class="space-y-4">
      @php
        $nextLetter = chr(65 + $paper->sections->count());
      @endphp
      <div>
        <label class="block text-xs font-semibold text-slate-700 mb-1">Section Title <span class="text-rose-500">*</span></label>
        <input type="text" id="newSecTitle" name="new_section_title" required value="Group {{ $nextLetter }}: Long Answer Questions" class="w-full text-xs p-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none" placeholder="e.g. Group {{ $nextLetter }}: Long Answer Questions">
        <div class="flex items-center gap-1.5 mt-2 flex-wrap">
          <span class="text-[11px] text-slate-400">Suggestions:</span>
          <button type="button" onclick="setSecTitle('Group {{ $nextLetter }}: Long Answer Questions')" class="text-[11px] px-2 py-0.5 bg-slate-100 hover:bg-purple-100 text-slate-600 hover:text-purple-700 rounded-lg transition font-medium">Group {{ $nextLetter }}: Long Answer</button>
          <button type="button" onclick="setSecTitle('Group {{ $nextLetter }}: Numerical Questions')" class="text-[11px] px-2 py-0.5 bg-slate-100 hover:bg-purple-100 text-slate-600 hover:text-purple-700 rounded-lg transition font-medium">Group {{ $nextLetter }}: Numerical</button>
          <button type="button" onclick="setSecTitle('Section {{ $nextLetter }}')" class="text-[11px] px-2 py-0.5 bg-slate-100 hover:bg-purple-100 text-slate-600 hover:text-purple-700 rounded-lg transition font-medium">Section {{ $nextLetter }}</button>
        </div>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-700 mb-1">Section Instructions (Optional)</label>
        <input type="text" id="newSecInstructions" name="new_section_instructions" class="w-full text-xs p-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none" placeholder="e.g. Answer any 4 questions. [4 &times; 5 = 20]">
      </div>

      {{-- AI Direct Paste Area --}}
      <div class="bg-gradient-to-br from-purple-50/80 to-indigo-50/50 border border-purple-200 rounded-2xl p-4 space-y-2.5">
        <div class="flex items-center justify-between">
          <label class="text-xs font-bold text-purple-900 flex items-center gap-1.5">
            <i class="bi bi-magic text-purple-600 text-sm"></i> Direct Paste / AI Question Generator (Optional)
          </label>
          <span class="text-[10px] text-purple-700 bg-purple-100 border border-purple-200 px-2 py-0.5 rounded-full font-semibold">Direct AI Import</span>
        </div>
        <p class="text-[11px] text-slate-600">
          Directly paste questions here (raw notes, Word doc, rough text, or questions from chat). AI will automatically clean up formatting, format math formulas & options, assign marks, and put them straight into this section!
        </p>
        <textarea id="newSecPromptText" name="prompt_text" rows="4" class="w-full text-xs p-3 border border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none bg-white placeholder:text-slate-400" placeholder="Paste your questions directly here... (Leave empty to create an empty section)"></textarea>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
          <div>
            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Difficulty</label>
            <select name="difficulty" class="w-full text-xs p-2 border border-purple-200 rounded-xl bg-white focus:ring-2 focus:ring-purple-500 outline-none">
              <option value="mixed">Mixed (Balanced)</option>
              <option value="easy">Easy</option>
              <option value="medium">Medium</option>
              <option value="hard">Hard / Challenging</option>
            </select>
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Upload File (Optional)</label>
            <input type="file" name="document_file" accept=".txt,.pdf,.docx,.doc,.jpg,.jpeg,.png,.webp" class="w-full text-xs file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-purple-100 file:text-purple-700 hover:file:bg-purple-200">
          </div>
        </div>
      </div>

      <div id="addSecError" class="hidden text-xs text-rose-600 bg-rose-50 border border-rose-100 rounded-xl p-3"></div>

      <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 pt-2 border-t border-slate-100">
        <button type="button" onclick="closeAddSectionModal()" class="w-full sm:w-auto px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-medium transition text-center">Cancel</button>
        <button type="submit" id="addSecSubmitBtn" class="w-full sm:w-auto px-5 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-semibold shadow-md transition flex items-center justify-center gap-1.5">
          <i class="bi bi-check-circle"></i> Create Section
        </button>
      </div>
    </form>
  </div>
</div>

{{-- ── SECTION AI DIRECT PASTE MODAL (FOR EXISTING SECTIONS) ────────────── --}}
<div id="sectionAiModal" class="qm-modal-bg hidden" style="z-index: 9999;" onclick="if(event.target===this)closeSectionAiModal()">
  <div class="qm-modal max-w-xl">
    <div class="flex justify-between items-center pb-3 border-b border-slate-100 mb-4">
      <div class="flex items-center gap-2 min-w-0">
        <div class="w-8 h-8 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center font-bold text-lg flex-shrink-0">
          <i class="bi bi-magic"></i>
        </div>
        <div class="min-w-0">
          <h3 class="font-bold text-slate-800 text-base truncate">Direct Paste / AI Questions</h3>
          <p class="text-xs text-slate-500 truncate">Add questions directly to <span id="secAiTargetTitle" class="font-semibold text-purple-700"></span></p>
        </div>
      </div>
      <button onclick="closeSectionAiModal()" class="text-slate-400 hover:text-slate-600 text-lg flex-shrink-0"><i class="bi bi-x-lg"></i></button>
    </div>

    <form id="sectionAiForm" onsubmit="submitSectionAiForm(event)" class="space-y-4">
      <input type="hidden" id="secAiTargetId" name="section_id">

      <div>
        <label class="block text-xs font-semibold text-slate-700 mb-1">
          Directly Paste Raw Questions / Topic Instructions
        </label>
        <textarea id="secAiPromptText" name="prompt_text" rows="5" class="w-full text-xs p-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none" placeholder="Directly paste your questions here (e.g. copied from Word, PDF, notes, or chat). AI will clean up formatting, format math formulas & options, assign marks, and put them straight into this section!"></textarea>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">Difficulty</label>
          <select name="difficulty" class="w-full text-xs p-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none bg-white">
            <option value="mixed">Mixed (Balanced)</option>
            <option value="easy">Easy</option>
            <option value="medium">Medium</option>
            <option value="hard">Hard / Challenging</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">Or Upload Reference File</label>
          <input type="file" name="document_file" accept=".txt,.pdf,.docx,.doc,.jpg,.jpeg,.png,.webp" class="w-full text-xs mt-1 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-purple-100 file:text-purple-700 hover:file:bg-purple-200">
        </div>
      </div>

      <div id="secAiError" class="hidden text-xs text-rose-600 bg-rose-50 border border-rose-100 rounded-xl p-3"></div>

      <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 pt-2 border-t border-slate-100">
        <button type="button" onclick="closeSectionAiModal()" class="w-full sm:w-auto px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-medium transition text-center">Cancel</button>
        <button type="submit" id="secAiSubmitBtn" class="w-full sm:w-auto px-5 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-semibold shadow-md transition flex items-center justify-center gap-2">
          <i class="bi bi-magic"></i> Generate & Add to Section
        </button>
      </div>
    </form>
  </div>
</div>

{{-- ── FREE & UNLIMITED GOOGLE LENS SCANNER MODAL (FULLSCREEN MOBILE + INTERACTIVE TEXT SELECT) ── --}}
<div id="lensScannerModal" class="qm-modal-bg hidden lens-fullscreen-mobile" style="z-index: 9999;" onclick="if(event.target===this)closeLensScannerModal()">
  <div class="lens-modal-container flex flex-col w-full h-[100dvh] bg-slate-900 text-slate-100 sm:rounded-3xl shadow-2xl border-0 sm:border sm:border-slate-800 overflow-hidden select-none">
    
    {{-- ── Top Navigation Bar ── --}}
    <div class="flex items-center justify-between px-3.5 py-2.5 bg-slate-900/95 backdrop-blur border-b border-slate-800/80 flex-shrink-0 z-30">
      <div class="flex items-center gap-2.5 min-w-0">
        <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-500 text-white flex items-center justify-center font-bold text-base shadow-sm flex-shrink-0">
          <i class="bi bi-camera"></i>
        </div>
        <div class="min-w-0">
          <div class="flex items-center gap-1.5 flex-wrap">
            <h3 class="font-bold text-white text-sm truncate">Google Lens Scanner</h3>
            <span class="bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[9px] font-bold px-1.5 py-0.2 rounded-full flex items-center gap-1"><i class="bi bi-infinity"></i> 100% Free</span>
          </div>
          <p id="lensTopSubText" class="text-[10px] text-slate-400 truncate">Snap photo, select question text, and auto-add</p>
        </div>
      </div>
      <button type="button" onclick="closeLensScannerModal()" class="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white flex items-center justify-center transition text-base flex-shrink-0" title="Close (Esc)">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    {{-- ── Configuration Bar (Section & Language) ── --}}
    <div class="flex items-center gap-2.5 px-3.5 py-2 bg-slate-950/95 border-b border-slate-800/80 flex-shrink-0 text-xs z-20">
      <div class="flex items-center gap-1.5 flex-1 min-w-0 overflow-hidden">
        <span class="text-[11px] font-bold text-slate-400 whitespace-nowrap flex-shrink-0 flex items-center gap-1">
          <i class="bi bi-folder-check text-indigo-400"></i> Section:
        </span>
        <div class="flex-1 min-w-0 overflow-hidden">
          <select id="lensTargetSectionSelect" onchange="updateLensActiveSection()" class="w-full text-xs font-semibold bg-slate-850 border border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-200 outline-none truncate focus:ring-1 focus:ring-emerald-500 block">
            @foreach($paper->sections as $sec)
            <option value="{{ $sec->id }}" data-title="{{ $sec->title }}">{{ $sec->title }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="flex items-center gap-1.5 flex-shrink-0">
        <span class="text-[11px] font-bold text-slate-400 whitespace-nowrap flex items-center gap-1">
          <i class="bi bi-translate text-emerald-400"></i> Lang:
        </span>
        <select id="lensOcrLang" class="text-xs font-semibold bg-slate-850 border border-slate-700 rounded-lg px-2 py-1.5 text-slate-200 outline-none focus:ring-1 focus:ring-emerald-500">
          <option value="eng" selected>English</option>
          <option value="nep">Nepali (नेपाली)</option>
          <option value="eng+nep">Eng+Nepali</option>
        </select>
      </div>
    </div>

    {{-- Hidden File Inputs --}}
    <input type="file" id="lensNativeCameraInput" accept="image/*" capture="environment" class="hidden" onchange="handleLensFileInput(event)">
    <input type="file" id="lensRegularFileInput" accept="image/*" class="hidden" onchange="handleLensFileInput(event)">

    {{-- ── Main Flexible Body Viewport (Step 1, Step 2, Step 3) ── --}}
    <div class="flex-1 min-h-0 relative flex flex-col overflow-hidden bg-slate-950">

      {{-- ── STEP 1: Upload / Camera Selection Screen (Desktop & Mobile Optimized) ── --}}
      <div id="lensUploadCards" class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 flex flex-col justify-center max-w-2xl mx-auto w-full space-y-4">
        
        {{-- Large Drag & Drop Dropzone for Desktop & Mobile --}}
        <div id="lensDropZone" class="border-2 border-dashed border-slate-700 hover:border-emerald-500/70 bg-slate-900/40 hover:bg-slate-900/80 rounded-2xl p-6 sm:p-8 text-center transition-all cursor-pointer group" onclick="document.getElementById('lensRegularFileInput').click()">
          <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-gradient-to-tr from-emerald-500/20 to-teal-500/20 text-emerald-400 flex items-center justify-center text-3xl group-hover:scale-110 transition-transform shadow-inner border border-emerald-500/30">
            <i class="bi bi-cloud-arrow-up"></i>
          </div>
          <h4 class="text-sm sm:text-base font-bold text-white group-hover:text-emerald-300 transition">
            Drop textbook image here, or <span class="text-emerald-400 underline decoration-emerald-500/50">browse files</span>
          </h4>
          <p class="text-xs text-slate-400 mt-1">Supports textbook photos, scans, PNG, JPG, and WEBP</p>
          <div class="mt-3 flex items-center justify-center gap-2 text-[11px] text-slate-400 flex-wrap">
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-slate-800 border border-slate-700">
              <kbd class="text-slate-300 font-mono">Ctrl+V</kbd> paste screenshot
            </span>
            <span class="text-slate-600 hidden sm:inline">•</span>
            <span>100% Free & Unlimited</span>
          </div>
        </div>

        {{-- Quick Option Buttons Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          {{-- Option A: Take Photo / Phone Camera --}}
          <button type="button" onclick="document.getElementById('lensNativeCameraInput').click()"
               class="p-3.5 rounded-2xl border border-emerald-500/30 bg-slate-900/90 hover:bg-slate-850 hover:border-emerald-500/60 transition cursor-pointer text-left group flex items-center gap-3 shadow active:scale-[0.98]">
            <div class="w-11 h-11 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xl flex-shrink-0 group-hover:scale-105 transition-transform">
              <i class="bi bi-camera-fill"></i>
            </div>
            <div class="min-w-0">
              <h5 class="text-xs font-bold text-white group-hover:text-emerald-300">Take Photo</h5>
              <p class="text-[10px] text-slate-400 truncate">Snap page with mobile or tablet camera</p>
            </div>
          </button>

          {{-- Option B: Desktop Webcam --}}
          <button type="button" onclick="startLensWebcam()"
               class="p-3.5 rounded-2xl border border-indigo-500/30 bg-slate-900/90 hover:bg-slate-850 hover:border-indigo-500/60 transition cursor-pointer text-left group flex items-center gap-3 shadow active:scale-[0.98]">
            <div class="w-11 h-11 rounded-xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center text-xl flex-shrink-0 group-hover:scale-105 transition-transform">
              <i class="bi bi-webcam-fill"></i>
            </div>
            <div class="min-w-0">
              <h5 class="text-xs font-bold text-white group-hover:text-indigo-300">Desktop Webcam</h5>
              <p class="text-[10px] text-slate-400 truncate">Hold textbook page up to laptop camera</p>
            </div>
          </button>
        </div>

        {{-- Live Webcam Container (Initially hidden) --}}
        <div id="lensWebcamWrap" class="hidden space-y-2 p-3 bg-black rounded-2xl border border-slate-800 text-center">
          <video id="lensWebcamVideo" autoplay playsinline class="w-full max-h-[260px] rounded-xl mx-auto bg-black object-contain"></video>
          <div class="flex justify-center gap-2">
            <button type="button" onclick="captureLensWebcam()" class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white font-semibold text-xs rounded-xl shadow transition flex items-center gap-1.5">
              <i class="bi bi-camera"></i> Capture Snapshot
            </button>
            <button type="button" onclick="stopLensWebcam()" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white text-xs rounded-xl transition">
              Cancel
            </button>
          </div>
        </div>

      </div>

      {{-- ── STEP 2: Draggable Crop Viewfinder Screen ── --}}
      <div id="lensCropStep" class="hidden flex-1 min-h-0 w-full h-full relative overflow-hidden bg-slate-950">
        {{-- Floating crop controls bar at the top of the viewfinder --}}
        <div class="absolute top-2 left-2 right-2 z-20 flex items-center justify-between gap-1.5 pointer-events-none">
          <span class="px-2.5 py-1 rounded-lg bg-slate-900/90 backdrop-blur text-[11px] font-semibold text-emerald-400 border border-slate-800 pointer-events-auto flex items-center gap-1.5 shadow-lg">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span class="hidden xs:inline">Drag box over question</span>
            <span class="xs:hidden">Crop Box</span>
          </span>
          <div class="flex items-center gap-1 pointer-events-auto bg-slate-900/90 backdrop-blur p-1 rounded-xl border border-slate-800 shadow-lg">
            <button type="button" onclick="selectFullLensCrop()" class="px-2 py-1 text-emerald-300 hover:text-white bg-emerald-500/25 hover:bg-emerald-500/40 rounded-lg text-xs font-semibold transition flex items-center gap-1" title="Select entire image (100%)">
              <i class="bi bi-arrows-fullscreen text-xs"></i>
              <span class="hidden sm:inline">Full Image</span>
            </button>
            <button type="button" onclick="lensCropperAction('rotateLeft')" class="w-7 h-7 flex items-center justify-center text-slate-300 hover:text-white rounded-lg hover:bg-slate-800 transition" title="Rotate Left (-90°)">
              <i class="bi bi-arrow-counterclockwise text-sm"></i>
            </button>
            <button type="button" onclick="lensCropperAction('rotateRight')" class="w-7 h-7 flex items-center justify-center text-slate-300 hover:text-white rounded-lg hover:bg-slate-800 transition" title="Rotate Right (+90°)">
              <i class="bi bi-arrow-clockwise text-sm"></i>
            </button>
            <button type="button" onclick="lensCropperAction('zoomIn')" class="w-7 h-7 flex items-center justify-center text-slate-300 hover:text-white rounded-lg hover:bg-slate-800 transition hidden sm:flex" title="Zoom In">
              <i class="bi bi-zoom-in text-sm"></i>
            </button>
            <button type="button" onclick="lensCropperAction('zoomOut')" class="w-7 h-7 flex items-center justify-center text-slate-300 hover:text-white rounded-lg hover:bg-slate-800 transition hidden sm:flex" title="Zoom Out">
              <i class="bi bi-zoom-out text-sm"></i>
            </button>
            <button type="button" onclick="lensCropperAction('reset')" class="w-7 h-7 flex items-center justify-center text-slate-300 hover:text-white rounded-lg hover:bg-slate-800 transition" title="Reset to Full Crop">
              <i class="bi bi-arrow-repeat text-sm"></i>
            </button>
            <button type="button" onclick="resetLensToUpload()" class="px-2 py-1 text-rose-400 hover:text-rose-300 hover:bg-rose-500/20 rounded-lg text-xs font-semibold transition flex items-center gap-0.5" title="New Photo">
              <i class="bi bi-arrow-left"></i><span>Retake</span>
            </button>
          </div>
        </div>

        {{-- The Cropper container filling available flex height --}}
        <div id="lensCropperWrap" class="absolute inset-0 w-full h-full overflow-hidden bg-slate-950">
          <img id="lensCropperImg" src="" alt="Book Page" class="max-w-full max-h-full block">
          {{-- Laser scanner line --}}
          <div id="lensScanLaser" class="lens-scan-line hidden"></div>
        </div>

        {{-- OCR Progress Overlay during recognition --}}
        <div id="lensOcrProgressWrap" class="hidden absolute inset-x-3 bottom-3 z-30 p-3 rounded-2xl bg-slate-900/95 border border-emerald-500/50 shadow-2xl space-y-2 backdrop-blur">
          <div class="flex justify-between text-xs font-semibold text-emerald-300">
            <span id="lensOcrStatusText" class="flex items-center gap-1.5 truncate">
              <i class="bi bi-arrow-repeat animate-spin text-emerald-400"></i> Reading text with WebAssembly...
            </span>
            <span id="lensOcrPercentText">0%</span>
          </div>
          <div class="w-full bg-slate-800 rounded-full h-2 overflow-hidden">
            <div id="lensOcrProgressBar" class="bg-gradient-to-r from-emerald-500 to-teal-400 h-2 rounded-full transition-all duration-200" style="width: 10%"></div>
          </div>
        </div>
      </div>

      {{-- ── STEP 3: Google Lens Interactive Text Selection Screen (DESKTOP SPLIT WORKBENCH + MOBILE DRAWER) ── --}}
      <div id="lensInteractiveStep" class="hidden flex-1 min-h-0 flex flex-col md:flex-row relative bg-slate-950 overflow-hidden">
        
        {{-- Left Column: Interactive Image Viewer & Selection Canvas --}}
        <div class="flex-1 min-w-0 flex flex-col h-full relative overflow-hidden bg-slate-950">
          {{-- Floating Google Lens toolstrip at top of image --}}
          <div class="px-3.5 py-2 bg-slate-900/90 backdrop-blur border-b border-slate-800/80 flex items-center justify-between gap-2 flex-shrink-0 z-20">
            <div class="flex items-center gap-1.5 min-w-0">
              <span class="w-2 h-2 rounded-full bg-sky-400 animate-pulse flex-shrink-0"></span>
              <span id="lensSelectionStats" class="text-xs font-bold text-sky-400 truncate">Tap text lines on image to select</span>
            </div>
            <div class="flex items-center gap-1.5 flex-shrink-0">
              <button type="button" onclick="selectAllLensBoxes()" class="px-2.5 py-1 bg-sky-500/20 hover:bg-sky-500/30 text-sky-300 border border-sky-500/40 rounded-lg text-xs font-semibold transition">
                Select All
              </button>
              <button type="button" onclick="clearAllLensBoxes()" class="px-2 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg text-xs font-medium transition">
                Clear
              </button>
              {{-- Zoom controls --}}
              <div class="flex items-center bg-slate-800/90 rounded-lg p-0.5 border border-slate-700/60">
                <button type="button" onclick="zoomLensInteractive(-0.2)" class="w-6 h-6 flex items-center justify-center text-slate-300 hover:text-white rounded transition" title="Zoom Out">
                  <i class="bi bi-dash text-xs"></i>
                </button>
                <span id="lensZoomIndicator" class="text-[10px] text-slate-400 font-mono px-1">100%</span>
                <button type="button" onclick="zoomLensInteractive(0.2)" class="w-6 h-6 flex items-center justify-center text-slate-300 hover:text-white rounded transition" title="Zoom In">
                  <i class="bi bi-plus text-xs"></i>
                </button>
              </div>
              {{-- Full View Toggle --}}
              <button type="button" onclick="toggleLensFullMode()" id="lensFullModeBtn" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700/60 rounded-lg text-xs font-medium transition flex items-center gap-1" title="Toggle full image view">
                <i class="bi bi-arrows-fullscreen text-xs"></i> <span id="lensFullModeText" class="hidden sm:inline">Full Image</span>
              </button>
              <button type="button" onclick="returnToLensCrop()" class="px-2.5 py-1 bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 border border-emerald-500/30 rounded-lg text-xs font-medium transition flex items-center gap-1" title="Adjust crop box">
                <i class="bi bi-crop"></i> Re-crop
              </button>
            </div>
          </div>

          {{-- Interactive Image Container with pixel-perfect overlay bounding boxes --}}
          <div class="flex-1 min-h-0 overflow-auto flex items-center justify-center p-2 sm:p-4 relative touch-pan-x touch-pan-y">
            <div id="lensInteractiveWrapper" class="relative inline-flex items-center justify-center max-w-full max-h-full mx-auto shadow-2xl rounded-lg overflow-hidden border border-slate-800 transition-transform duration-150">
              <img id="lensInteractiveImg" src="" alt="Cropped Question" class="block max-w-full max-h-full w-auto h-auto object-contain mx-auto pointer-events-none select-none">
              {{-- Glowing interactive bounding boxes layer --}}
              <div id="lensBoxesLayer" class="absolute inset-0 pointer-events-auto"></div>
            </div>
          </div>

          {{-- Desktop Helper footer note --}}
          <div class="hidden md:flex items-center justify-between px-4 py-2 bg-slate-900/80 border-t border-slate-800/80 text-[11px] text-slate-400 flex-shrink-0">
            <span><i class="bi bi-info-circle text-sky-400 me-1"></i>Click lines on the image to toggle selection</span>
            <span class="text-slate-500">Shortcut: <kbd class="px-1 py-0.5 bg-slate-800 border border-slate-700 rounded text-[10px] text-slate-300">Ctrl+Enter</kbd> to Auto-Add</span>
          </div>
        </div>

        {{-- Right Column (Desktop) / Bottom Drawer (Mobile): Extracted Text Workbench --}}
        <div id="lensSelectBottomBar" class="hidden flex flex-col w-full md:w-[420px] lg:w-[460px] flex-shrink-0 bg-slate-900/98 backdrop-blur border-t md:border-t-0 md:border-l border-slate-800 z-30 p-3 sm:p-4 gap-2.5 max-h-[48vh] md:max-h-none overflow-y-auto">
          
          {{-- Section Indicator --}}
          <div class="flex items-center justify-between text-xs pb-1.5 border-b border-slate-800">
            <span class="text-[11px] font-bold text-slate-400 flex items-center gap-1.5">
              <i class="bi bi-folder-check text-indigo-400"></i> Section:
            </span>
            <span id="lensActiveSectionLabel" class="font-bold text-emerald-400 text-xs truncate max-w-[220px]">Section</span>
          </div>

          {{-- Editable Question Text Area --}}
          <div class="space-y-1.5 flex-1 flex flex-col min-h-0">
            <div class="flex items-center justify-between text-xs">
              <span class="text-[11px] font-bold text-slate-300 flex items-center gap-1">
                <i class="bi bi-file-earmark-text text-sky-400"></i> Question & Options:
              </span>
              <div class="flex items-center gap-2">
                <button type="button" onclick="copyLensExtractedText()" id="lensCopyBtn" class="text-[11px] text-slate-400 hover:text-sky-300 transition flex items-center gap-1" title="Copy to clipboard">
                  <i class="bi bi-clipboard"></i> Copy
                </button>
                <button type="button" onclick="toggleLensMathPreview()" id="lensMathToggleBtn" class="text-[11px] text-slate-400 hover:text-emerald-300 transition flex items-center gap-1" title="Toggle LaTeX/KaTeX math formula preview">
                  <i class="bi bi-calculator"></i> Math
                </button>
                <button type="button" onclick="toggleLensTextEditor()" id="lensToggleEditBtn" class="text-[11px] text-sky-400 hover:underline flex items-center gap-1 md:hidden">
                  <i class="bi bi-arrows-expand"></i> <span id="lensEditBtnLabel">Expand</span>
                </button>
              </div>
            </div>

            {{-- Textarea with comfortable desktop sizing --}}
            <textarea id="lensExtractedText" rows="3" class="w-full text-xs font-sans p-2.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-100 focus:ring-2 focus:ring-sky-500 outline-none leading-relaxed transition-all md:flex-1 md:min-h-[140px]" placeholder="Tap lines on the image to select text..." oninput="onLensTextInput()"></textarea>

            {{-- Live Math Preview Box (auto-shows when math formulas are detected) --}}
            <div id="lensMathPreviewWrap" class="hidden p-2.5 rounded-xl bg-slate-950 border border-indigo-500/30 text-xs">
              <div class="text-[10px] font-bold text-indigo-400 mb-1 flex items-center justify-between">
                <span><i class="bi bi-calculator"></i> Live Math Preview</span>
                <span class="text-slate-500 font-mono text-[9px]">KaTeX Render</span>
              </div>
              <div id="lensMathPreview" class="math-render text-slate-200 break-words leading-relaxed text-xs"></div>
            </div>
          </div>

          {{-- Success & Error Alerts --}}
          <div id="lensSuccessAlert" class="hidden p-2.5 bg-emerald-950/80 border border-emerald-500/40 text-emerald-300 rounded-xl text-xs font-medium flex items-center justify-between gap-2">
            <span id="lensSuccessMsg" class="flex items-center gap-1.5 truncate">
              <i class="bi bi-check-circle-fill text-emerald-400"></i> Added to section!
            </span>
            <button type="button" onclick="document.getElementById('lensSuccessAlert').classList.add('hidden')" class="text-emerald-400"><i class="bi bi-x"></i></button>
          </div>
          <div id="lensErrorAlert" class="hidden p-2.5 bg-rose-950/80 border border-rose-500/40 text-rose-300 rounded-xl text-xs font-medium"></div>

          {{-- Action Buttons --}}
          <div class="flex flex-col gap-2 pt-1">
            {{-- Primary: Free AI Auto-Format & Add --}}
            <button type="button" id="lensAiAddBtn" onclick="submitLensAiAdd()"
                    class="w-full py-2.5 sm:py-3 px-4 bg-gradient-to-r from-purple-600 via-indigo-600 to-sky-600 hover:from-purple-700 hover:to-indigo-700 active:scale-[0.98] text-white font-bold text-xs sm:text-sm rounded-xl shadow-lg shadow-purple-950/50 transition flex items-center justify-center gap-2 cursor-pointer">
              <i class="bi bi-magic text-sm text-amber-300"></i>
              <span>✨ Auto-Format & Add (Free AI)</span>
            </button>

            {{-- Secondary Row --}}
            <div class="grid grid-cols-2 gap-2">
              <button type="button" id="lensDirectAddBtn" onclick="submitLensDirectAdd()"
                      class="py-2 px-3 bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-xs rounded-xl transition flex items-center justify-center gap-1.5" title="Direct raw add without AI">
                <i class="bi bi-plus-lg"></i> Direct Add
              </button>
              <button type="button" onclick="prepareNextQuestionCrop()"
                      class="py-2 px-3 bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 border border-emerald-500/30 font-semibold text-xs rounded-xl transition flex items-center justify-center gap-1.5" title="Crop another question on this page">
                <i class="bi bi-crop"></i> Next Question
              </button>
            </div>
          </div>

        </div>

      </div>

    </div>

    {{-- ── Fixed Bottom Bar for STEP 2 (Crop View) ── --}}
    <div id="lensCropBottomBar" class="hidden px-4 py-2.5 bg-slate-900 border-t border-slate-800 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5 flex-shrink-0 z-30">
      <div class="hidden sm:flex items-center gap-2 text-xs text-slate-400">
        <i class="bi bi-lightbulb text-amber-400 text-sm"></i>
        <span>Drag the crop handles over the question and options. Press <kbd class="px-1.5 py-0.5 bg-slate-800 border border-slate-700 rounded text-[10px] text-slate-300">Enter</kbd> or click Scan.</span>
      </div>
      <div class="flex items-center gap-2 flex-1 sm:flex-initial justify-end">
        <label class="flex items-center gap-1.5 px-3 py-2.5 bg-slate-800 hover:bg-slate-750 rounded-xl cursor-pointer text-xs font-semibold text-slate-300 transition flex-shrink-0" title="Boost paper contrast for low-light camera photos">
          <input type="checkbox" id="lensHighContrast" checked class="rounded text-emerald-500 focus:ring-emerald-500 bg-slate-700 border-slate-600">
          <span>High Contrast</span>
        </label>
        <button type="button" id="lensScanBtn" onclick="runLensOcr()"
                class="flex-1 sm:flex-initial px-6 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 active:scale-[0.98] text-white font-bold text-xs sm:text-sm rounded-xl shadow-lg shadow-emerald-950/60 transition flex items-center justify-center gap-2 cursor-pointer">
          <i class="bi bi-lightning-charge-fill text-amber-300 text-base"></i>
          <span>Scan Text (Google Lens OCR)</span>
        </button>
      </div>
    </div>

  </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     FULL-SCREEN IMAGE LIGHTBOX VIEWER MODAL
══════════════════════════════════════════════════════════════════════════ --}}
<div id="fullImageViewerModal" class="fixed inset-0 bg-black/90 backdrop-blur-md z-[99999] hidden flex flex-col items-center justify-center p-3 sm:p-6" onclick="closeFullImageViewer()">
  <div class="absolute top-4 right-4 flex items-center gap-2.5 z-10" onclick="event.stopPropagation()">
    <a id="fullImageViewerDownload" href="#" download="question-image.jpg" class="w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center text-lg transition shadow" title="Download Full Image">
      <i class="bi bi-download"></i>
    </a>
    <button type="button" onclick="closeFullImageViewer()" class="w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center text-lg transition shadow" title="Close">
      <i class="bi bi-x-lg"></i>
    </button>
  </div>
  <div class="relative max-w-full max-h-full flex items-center justify-center overflow-auto p-2" onclick="event.stopPropagation()">
    <img id="fullImageViewerImg" src="" alt="Question Image Full View" class="max-w-full max-h-[92vh] w-auto h-auto object-contain rounded-xl shadow-2xl border border-white/10 select-none">
  </div>
</div>

<script>
function openFullImageViewer(src) {
  const modal = document.getElementById('fullImageViewerModal');
  const img = document.getElementById('fullImageViewerImg');
  const dl = document.getElementById('fullImageViewerDownload');
  if (!modal || !img) return;
  img.src = src;
  if (dl) dl.href = src;
  modal.classList.remove('hidden');
}
function closeFullImageViewer() {
  const modal = document.getElementById('fullImageViewerModal');
  if (modal) modal.classList.add('hidden');
}
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') closeFullImageViewer();
});
document.addEventListener('click', function(e) {
  const target = e.target.closest('.math-render img, .math-content img, .section-questions-list img, #sectionsContainer img, .q-body img, .question-content img');
  if (target && target.src && target.id !== 'lensInteractiveImg' && target.id !== 'lensCropperImg' && target.id !== 'cropperImg' && target.id !== 'fullImageViewerImg') {
    e.preventDefault();
    openFullImageViewer(target.src);
  }
});
</script>
@endpush
@endsection
