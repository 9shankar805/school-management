@extends('layouts.app')

@php
    $pageW = $template->paperWidthMm();
    $pageH = $template->paperHeightMm();
@endphp

@section('content')
<div class="p-4 lg:p-6 min-h-screen bg-slate-100 dark:bg-slate-900">

    {{-- ── Top bar ──────────────────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 rounded-t-xl shadow-sm mb-4">
        <div class="flex items-center justify-between px-4 py-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('certificates.index') }}" class="text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 text-sm font-medium">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
                <div class="h-6 w-px bg-slate-200 dark:bg-slate-600"></div>
                <div>
                    <h1 class="text-lg font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <i class="bi bi-sliders text-fuchsia-500"></i> Certificate Builder
                    </h1>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-500 dark:text-slate-400">{{ $template->name }}</span>
            </div>
        </div>

        {{-- Toolbar with centered editing controls and dropdown menus --}}
        <div class="flex items-center justify-center gap-2 px-4 py-2 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-700 flex-wrap">
            {{-- Background Dropdown --}}
            <div class="relative">
                <button id="bgDropdownBtn" class="text-xs px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    <i class="bi bi-card-image me-1"></i> Background
                </button>
                <div id="bgDropdown" class="hidden absolute bottom-full left-0 mb-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-lg shadow-lg p-3 z-50 min-w-[200px]">
                    <label class="block w-full text-center text-xs py-2 border border-dashed border-slate-300 dark:border-slate-600 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-fuchsia-50 hover:text-fuchsia-700 cursor-pointer dark:hover:bg-fuchsia-900/20">
                        <i class="bi bi-cloud-upload me-1"></i>
                        <span>Upload Background</span>
                        <input id="bgFile" type="file" accept="image/png,image/jpeg" class="hidden">
                    </label>
                </div>
            </div>

            <div class="h-5 w-px bg-slate-300 dark:bg-slate-600"></div>

            {{-- Text Elements Dropdown --}}
            <div class="relative">
                <button id="textDropdownBtn" class="text-xs px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    <i class="bi bi-type me-1"></i> Text
                </button>
                <div id="textDropdown" class="hidden absolute bottom-full left-0 mb-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-lg shadow-lg p-2 z-50 min-w-[180px]">
                    <button class="palette-btn w-full text-left text-xs py-1.5 px-2 rounded hover:bg-slate-100 dark:hover:bg-slate-700" data-type="text" data-content="Title Text" data-fontSize="28" data-fontWeight="bold" data-align="center" data-color="#1e1b4b">
                        <i class="bi bi-type-h1 me-2"></i> Big Title
                    </button>
                    <button class="palette-btn w-full text-left text-xs py-1.5 px-2 rounded hover:bg-slate-100 dark:hover:bg-slate-700" data-type="text" data-content="Subtitle" data-fontSize="14" data-align="center" data-color="#475569">
                        <i class="bi bi-type me-2"></i> Subtitle
                    </button>
                    <button class="palette-btn w-full text-left text-xs py-1.5 px-2 rounded hover:bg-slate-100 dark:hover:bg-slate-700" data-type="text" data-content="Body paragraph text. Double-click to edit inline." data-fontSize="11" data-color="#334155">
                        <i class="bi bi-paragraph me-2"></i> Paragraph
                    </button>
                    <button class="palette-btn w-full text-left text-xs py-1.5 px-2 rounded hover:bg-slate-100 dark:hover:bg-slate-700" data-type="line" data-color="#4f46e5">
                        <i class="bi bi-dash-lg me-2"></i> Line / Divider
                    </button>
                </div>
            </div>

            {{-- Dynamic Fields Dropdown --}}
            <div class="relative">
                <button id="fieldDropdownBtn" class="text-xs px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    <i class="bi bi-database me-1"></i> Fields
                </button>
                <div id="fieldDropdown" class="hidden absolute bottom-full left-0 mb-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-lg shadow-lg p-2 z-50 min-w-[200px] max-h-[300px] overflow-y-auto">
                    @foreach($dynamicFields as $key => $label)
                        <button class="palette-btn w-full text-left text-xs py-1.5 px-2 rounded hover:bg-slate-100 dark:hover:bg-slate-700"
                                data-type="field"
                                data-field="{{ $key }}"
                                data-content="[{{ $label }}]"
                                data-fontSize="14"
                                data-color="{{ str_starts_with($key,'custom') ? '#be123c' : '#1e293b' }}">
                            <i class="bi bi-braces me-1.5 text-indigo-500"></i> {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Images Dropdown --}}
            <div class="relative">
                <button id="imageDropdownBtn" class="text-xs px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    <i class="bi bi-images me-1"></i> Images
                </button>
                <div id="imageDropdown" class="hidden absolute bottom-full left-0 mb-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-lg shadow-lg p-2 z-50 min-w-[180px]">
                    <button class="palette-btn w-full text-left text-xs py-1.5 px-2 rounded hover:bg-slate-100 dark:hover:bg-slate-700" data-type="image" data-role="logo" data-content="LOGO" data-fontSize="10" data-align="center">
                        <i class="bi bi-patch-check me-2"></i> School Logo
                    </button>
                    <button class="palette-btn w-full text-left text-xs py-1.5 px-2 rounded hover:bg-slate-100 dark:hover:bg-slate-700" data-type="image" data-role="signature" data-content="SIGNATURE" data-fontSize="10" data-align="center">
                        <i class="bi bi-pen me-2"></i> Signature Image
                    </button>
                    <button class="palette-btn w-full text-left text-xs py-1.5 px-2 rounded hover:bg-slate-100 dark:hover:bg-slate-700" data-type="student_photo" data-content="PHOTO" data-fontSize="10" data-align="center">
                        <i class="bi bi-person-square me-2"></i> Student Photo
                    </button>
                    <button class="palette-btn w-full text-left text-xs py-1.5 px-2 rounded hover:bg-slate-100 dark:hover:bg-slate-700" data-type="qr" data-content="QR" data-fontSize="10" data-align="center">
                        <i class="bi bi-qr-code-scan me-2"></i> QR Code
                    </button>
                </div>
            </div>

            <div class="h-5 w-px bg-slate-300 dark:bg-slate-600"></div>

            <div class="flex items-center gap-2">
                <label class="text-xs text-slate-600 dark:text-slate-300 font-medium">Paper:</label>
                <select id="paperSize" class="text-xs border border-slate-300 dark:border-slate-600 rounded px-2 py-1.5 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-medium">
<?php
$ps = old('paper_size', $template->paper_size);
?>
                    <option value="A4"<?= $ps==='A4' ? ' selected' : '' ?>>A4</option>
                    <option value="Letter"<?= $ps==='Letter' ? ' selected' : '' ?>>Letter</option>
                </select>
            </div>

            <div class="h-5 w-px bg-slate-300 dark:bg-slate-600"></div>

            <div class="flex items-center gap-2">
                <label class="text-xs text-slate-600 dark:text-slate-300 font-medium">Orientation:</label>
                <select id="orientation" class="text-xs border border-slate-300 dark:border-slate-600 rounded px-2 py-1.5 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-medium">
<?php
$or = old('orientation', $template->orientation);
?>
                    <option value="portrait"<?= $or==='portrait' ? ' selected' : '' ?>>Portrait</option>
                    <option value="landscape"<?= $or==='landscape' ? ' selected' : '' ?>>Landscape</option>
                </select>
            </div>

            <div class="h-5 w-px bg-slate-300 dark:bg-slate-600"></div>

            <div class="flex items-center gap-2">
                <label class="inline-flex items-center gap-1.5 text-xs text-slate-600 dark:text-slate-300">
                    <input type="checkbox" id="snapGrid" checked class="w-3.5 h-3.5 rounded">
                    Snap
                </label>
            </div>

            <div class="h-5 w-px bg-slate-300 dark:bg-slate-600"></div>

            <div class="flex items-center gap-2">
                <a href="{{ route('certificates.preview', $template->id) }}" target="_blank"
                   class="text-xs px-3 py-1.5 bg-slate-600 hover:bg-slate-700 text-white rounded font-medium transition-colors">
                    <i class="bi bi-eye-fill me-1"></i>Preview PDF
                </a>
                <button id="saveBtn"
                        class="text-xs px-4 py-1.5 bg-fuchsia-600 hover:bg-fuchsia-700 text-white rounded font-medium shadow-sm transition-colors disabled:opacity-60">
                    <i class="bi bi-check-lg me-1"></i>Save Template
                </button>
            </div>
        </div>
    </div>

    {{-- ── Layout: canvas | properties ──────────────────────────────────────── --}}
    <div class="grid grid-cols-12 gap-4 items-start">

        {{-- CENTER CANVAS --}}
        <main class="col-span-12 md:col-span-9 lg:col-span-9 overflow-auto">
            <div class="bg-slate-300 dark:bg-slate-800 p-5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-inner" id="canvasWrap">
                <div id="pageCanvas"
                     class="relative mx-auto bg-white shadow-2xl shadow-slate-500/40 select-none"
                     style="width:{{ $pageW * 2.8 }}px; height:{{ $pageH * 2.8 }}px;
                            {{ $bgUrl ? "background-image:url('".e($bgUrl)."'); background-size:100% 100%; background-repeat:no-repeat;": '' }}">
                </div>
                <p id="dimLine" class="mt-3 text-center text-[11px] text-slate-500 dark:text-slate-400">
                    {{ $template->paper_size }} · {{ ucfirst($template->orientation) }} · {{ $pageW }} × {{ $pageH }} mm · Scale ≈ {{ number_format(2.8, 2) }}×
                </p>
            </div>
        </main>

        {{-- RIGHT PROPERTIES PANEL --}}
        <aside class="col-span-12 md:col-span-3 lg:col-span-3 space-y-3 sticky top-4 max-h-[calc(100vh-8rem)] overflow-y-auto pl-1 custom-scroll">
            <div id="noSelection" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs p-4">
                <p class="text-sm text-slate-500 dark:text-slate-400 text-center">
                    <i class="bi bi-hand-index text-2xl block mb-2 text-slate-300"></i>
                    Click any element on the canvas to edit it here.
                </p>
            </div>

            <div id="properties" class="hidden bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Properties</h4>
                    <button id="delEl" class="text-xs text-rose-600 hover:text-rose-700 font-medium" title="Delete element">
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>

                <div>
                    <label class="block text-[11px] mb-1 text-slate-500 dark:text-slate-400">Type</label>
                    <input id="p_type" disabled class="w-full text-xs px-2 py-1 border border-slate-200 dark:border-slate-600 rounded bg-slate-50 dark:bg-slate-700/50">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[11px] mb-1 text-slate-500">X (mm)</label>
                        <input id="p_x" type="number" step="0.5" class="num-prop">
                    </div>
                    <div>
                        <label class="block text-[11px] mb-1 text-slate-500">Y (mm)</label>
                        <input id="p_y" type="number" step="0.5" class="num-prop">
                    </div>
                    <div>
                        <label class="block text-[11px] mb-1 text-slate-500">Width (mm)</label>
                        <input id="p_w" type="number" step="0.5" class="num-prop">
                    </div>
                    <div>
                        <label class="block text-[11px] mb-1 text-slate-500">Height (mm)</label>
                        <input id="p_h" type="number" step="0.5" class="num-prop">
                    </div>
                </div>

                <div id="textProps" class="space-y-3 pt-2 border-t border-slate-100 dark:border-slate-700">
                    <div>
                        <label class="block text-[11px] mb-1 text-slate-500">Content</label>
                        <textarea id="p_content" rows="3" class="w-full text-xs px-2 py-1.5 border border-slate-200 dark:border-slate-600 rounded bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100"></textarea>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="block text-[11px] mb-1 text-slate-500">Font size</label>
                            <input id="p_fontSize" type="number" min="6" max="80" step="1" class="num-prop">
                        </div>
                        <div>
                            <label class="block text-[11px] mb-1 text-slate-500">Color</label>
                            <input id="p_color" type="color" class="w-full h-7 p-0 border border-slate-200 dark:border-slate-600 rounded">
                        </div>
                        <div>
                            <label class="block text-[11px] mb-1 text-slate-500">Align</label>
                            <select id="p_align" class="w-full text-xs px-1 py-1 border border-slate-200 dark:border-slate-600 rounded bg-white dark:bg-slate-700">
                                <option value="left">Left</option>
                                <option value="center">Center</option>
                                <option value="right">Right</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="inline-flex items-center gap-1.5 text-xs text-slate-600 dark:text-slate-300">
                            <input type="checkbox" id="p_bold" class="w-3.5 h-3.5 rounded"> <b>Bold</b>
                        </label>
                        <label class="inline-flex items-center gap-1.5 text-xs text-slate-600 dark:text-slate-300">
                            <input type="checkbox" id="p_italic" class="w-3.5 h-3.5 rounded"> <i>Italic</i>
                        </label>
                    </div>
                    <div>
                        <label class="block text-[11px] mb-1 text-slate-500">Z-index (layer order)</label>
                        <input id="p_zIndex" type="number" class="num-prop">
                    </div>
                </div>

                <div id="fieldProps" class="hidden pt-2 border-t border-slate-100 dark:border-slate-700 space-y-2">
                    <div>
                        <label class="block text-[11px] mb-1 text-slate-500">Field</label>
                        <select id="p_field" class="w-full text-xs px-2 py-1 border border-slate-200 dark:border-slate-600 rounded bg-white dark:bg-slate-700"></select>
                        <p class="text-[10px] mt-1 text-emerald-600 dark:text-emerald-400">
                            Filled automatically at issue time from student/class data.
                        </p>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="block text-[11px] mb-1 text-slate-500">Size</label>
                            <input id="p_fontSize2" type="number" min="6" max="80" step="1" class="num-prop">
                        </div>
                        <div>
                            <label class="block text-[11px] mb-1 text-slate-500">Color</label>
                            <input id="p_color2" type="color" class="w-full h-7 p-0 border border-slate-200 dark:border-slate-600 rounded">
                        </div>
                        <div>
                            <label class="block text-[11px] mb-1 text-slate-500">Align</label>
                            <select id="p_align2" class="w-full text-xs px-1 py-1 border border-slate-200 dark:border-slate-600 rounded bg-white dark:bg-slate-700">
                                <option value="left">Left</option>
                                <option value="center">Center</option>
                                <option value="right">Right</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="inline-flex items-center gap-1.5 text-xs text-slate-600 dark:text-slate-300">
                            <input type="checkbox" id="p_bold2" class="w-3.5 h-3.5 rounded"> <b>Bold</b>
                        </label>
                        <label class="inline-flex items-center gap-1.5 text-xs text-slate-600 dark:text-slate-300">
                            <input type="checkbox" id="p_italic2" class="w-3.5 h-3.5 rounded"> <i>Italic</i>
                        </label>
                    </div>
                    <div>
                        <label class="block text-[11px] mb-1 text-slate-500">Z-index</label>
                        <input id="p_zIndex2" type="number" class="num-prop">
                    </div>
                </div>

                <div id="lineProps" class="hidden pt-2 border-t border-slate-100 dark:border-slate-700 space-y-2">
                    <div>
                        <label class="block text-[11px] mb-1 text-slate-500">Border thickness (px)</label>
                        <input id="p_border" type="number" min="1" max="20" step="1" class="num-prop">
                    </div>
                    <div>
                        <label class="block text-[11px] mb-1 text-slate-500">Line color</label>
                        <input id="p_borderColor" type="color" class="w-full h-7 p-0 border border-slate-200 dark:border-slate-600 rounded">
                    </div>
                </div>
            </div>

            {{-- Editable fields --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs p-4">
                <h4 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-2">
                    <i class="bi bi-pencil-square me-1 text-rose-500"></i> Editable Per Issuance
                </h4>
                <p class="text-[10px] text-slate-400 mb-2 leading-relaxed">
                    Tick fields that the user should be able to type/override each time they issue a certificate (e.g. name, custom lines).
                </p>
                <div id="editableList" class="max-h-48 overflow-auto pr-1 space-y-1">
                    @foreach($dynamicFields as $k => $v)
                        <label class="flex items-center gap-1.5 text-[11px] text-slate-600 dark:text-slate-300">
                            <input type="checkbox" value="{{ $k }}"
                                   class="editable-check w-3.5 h-3.5 rounded"
                                   @checked(in_array($k, $editable))>
                            {{ $v }}
                        </label>
                    @endforeach
                </div>
            </div>
        </aside>
    </div>
</div>

<style>
    .palette-btn{
        @apply w-full text-xs py-2 px-2.5 border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-100 hover:bg-fuchsia-50 hover:text-fuchsia-700 hover:border-fuchsia-300 dark:hover:bg-fuchsia-900/20 dark:hover:text-fuchsia-300 transition cursor-grab active:cursor-grabbing;
    }
    .num-prop{
        @apply w-full text-xs px-2 py-1 border border-slate-200 dark:border-slate-600 rounded bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100;
    }
    .canvas-el{
        position:absolute;
        box-sizing:border-box;
        cursor:move;
        user-select:none;
        display:flex;
        align-items:flex-start;
        outline:1px dashed transparent;
        overflow:hidden;
        word-break:break-word;
        line-height:1.25;
        padding:1px;
    }
    .canvas-el:hover{ outline-color:#6366f1; }
    .canvas-el.selected{ outline:2px solid #d946ef !important; box-shadow:0 0 0 2px rgba(217,70,239,.25); }
    .canvas-el > .resize-handle{
        position:absolute; right:-5px; bottom:-5px; width:12px; height:12px; background:#d946ef;
        border-radius:2px; cursor:nwse-resize; display:none; border:1px solid #fff; z-index:5;
    }
    .canvas-el.selected > .resize-handle{ display:block; }
    .canvas-el[data-type="line"]{ padding:0; border-bottom-width:1px; }
    .canvas-el[data-type="image"],
    .canvas-el[data-type="qr"],
    .canvas-el[data-type="student_photo"]{
        align-items:center; justify-content:center;
        background-image:linear-gradient(45deg,#e2e8f0 25%,transparent 25%),linear-gradient(-45deg,#e2e8f0 25%,transparent 25%),linear-gradient(45deg,transparent 75%,#e2e8f0 75%),linear-gradient(-45deg,transparent 75%,#e2e8f0 75%);
        background-size:16px 16px; background-position:0 0,0 8px,8px -8px,-8px 0;
        background-color:#f8fafc;
        color:#475569;
        border:1px dashed #cbd5e1;
    }
    #saveBtn.saving{ opacity:.7; }
    #saveBtn.saving::after{ content:' Saving…'; }
    #toast{ position:fixed; top:1.25rem; right:1.25rem; padding:.6rem 1rem; border-radius:.5rem; color:#fff; font-size:.8rem; font-weight:500; box-shadow:0 10px 25px rgba(0,0,0,.15); z-index:9999; transform:translateY(-10px); opacity:0; transition:.2s; pointer-events:none; }
    #toast.show{ transform:none; opacity:1; }
    #toast.ok{ background:#16a34a; }
    #toast.err{ background:#dc2626; }
    .custom-scroll::-webkit-scrollbar{ width:6px; height:6px; }
    .custom-scroll::-webkit-scrollbar-track{ background:transparent; }
    .custom-scroll::-webkit-scrollbar-thumb{ background:#cbd5e1; border-radius:3px; }
    .dark .custom-scroll::-webkit-scrollbar-thumb{ background:#475569; }
    .custom-scroll::-webkit-scrollbar-thumb:hover{ background:#94a3b8; }
    .dark .custom-scroll::-webkit-scrollbar-thumb:hover{ background:#64748b; }
</style>
<div id="toast"></div>
@endsection

@push('scripts')
<script>
(function () {
    const PAGE_SIZES_MM = {
        'A4':     { portrait:  [210, 297], landscape: [297, 210] },
        'Letter': { portrait:  [216, 279], landscape: [279, 216] },
    };
    let CURRENT_PAPER = document.getElementById('paperSize').value;
    let CURRENT_ORIENT = document.getElementById('orientation').value;
    let PAGE_W_MM = {{ $pageW }};
    let PAGE_H_MM = {{ $pageH }};
    const SCALE    = 2.8;             // px per mm for canvas display
    const PX_PER_MM = SCALE;

    const canvas   = document.getElementById('pageCanvas');
    const dimLine  = document.getElementById('dimLine');
    const propsEl  = document.getElementById('properties');
    const noSel    = document.getElementById('noSelection');
    const saveBtn  = document.getElementById('saveBtn');
    const toast    = document.getElementById('toast');

    let elements = @json($layout);
    let selectedId = null;

    function showToast(msg, type='ok') {
        toast.className = 'show ' + type;
        toast.textContent = msg;
        setTimeout(() => toast.className = type, 1800);
    }

    // ── Page size / orientation ─────────────────────────────────────
    function applyPageSize(paper, orient, opts = {}) {
        const doSwap   = opts.swapWH ?? false;
        const doClamp  = opts.clamp ?? true;
        const prevW = PAGE_W_MM, prevH = PAGE_H_MM;
        const [w, h] = PAGE_SIZES_MM[paper][orient];
        PAGE_W_MM = w; PAGE_H_MM = h;
        CURRENT_PAPER = paper; CURRENT_ORIENT = orient;

        // Swap all element W/H when user toggles orientation (rotates layout)
        if (doSwap) {
            elements.forEach(el => {
                const ew = el.w ?? defaultWidth(el);
                const eh = el.h ?? defaultHeight(el);
                const ex = el.x, ey = el.y;
                el.x = ey;
                el.y = ex;
                if (el.w !== undefined || el.h !== undefined) {
                    el.w = eh;
                    el.h = ew;
                }
            });
        }
        canvas.style.width  = (PAGE_W_MM * PX_PER_MM) + 'px';
        canvas.style.height = (PAGE_H_MM * PX_PER_MM) + 'px';
        // Update footer dim line
        if (dimLine) {
            const ucfirst = s => s.charAt(0).toUpperCase() + s.slice(1);
            dimLine.textContent = paper + ' · ' + ucfirst(orient) + ' · ' + PAGE_W_MM + ' × ' + PAGE_H_MM + ' mm · Scale ≈ ' + SCALE.toFixed(2) + '×';
        }
        // Clamp any elements now outside the new canvas bounds
        if (doClamp) {
            elements.forEach(el => {
                const ew = el.w ?? defaultWidth(el);
                const eh = el.h ?? defaultHeight(el);
                el.x = Math.max(0, Math.min(PAGE_W_MM - ew, el.x));
                el.y = Math.max(0, Math.min(PAGE_H_MM - eh, el.y));
                if (ew > PAGE_W_MM) el.w = PAGE_W_MM;
                if (eh > PAGE_H_MM) el.h = PAGE_H_MM;
            });
        }
        render();
        if (selectedId) select(selectedId);
    }

    // ── Render all elements to canvas ────────────────────────────────
    function render() {
        canvas.querySelectorAll('.canvas-el').forEach(e => e.remove());
        const sorted = [...elements].sort((a,b) => (a.zIndex||1)-(b.zIndex||1));
        sorted.forEach(draw);
    }

    function newElId() { return 'el_' + Math.random().toString(36).slice(2, 9); }

    function draw(el) {
        const node = document.createElement('div');
        node.className = 'canvas-el';
        node.dataset.id = el.id;
        node.dataset.type = el.type;

        const w = el.w ?? defaultWidth(el);
        const h = el.h ?? defaultHeight(el);
        node.style.left   = (el.x * PX_PER_MM) + 'px';
        node.style.top    = (el.y * PX_PER_MM) + 'px';
        node.style.width  = (w * PX_PER_MM) + 'px';
        node.style.height = (h * PX_PER_MM) + 'px';
        node.style.zIndex = el.zIndex || 1;

        applyStyleToNode(node, el, w, h);
        applyContentToNode(node, el);

        const handle = document.createElement('div');
        handle.className = 'resize-handle';
        node.appendChild(handle);

        node.addEventListener('mousedown', startDrag);
        handle.addEventListener('mousedown', startResize);
        node.addEventListener('dblclick', (e) => {
            if (el.type === 'text') {
                e.preventDefault();
                e.stopPropagation();
                const newVal = prompt('Edit text:', el.content);
                if (newVal !== null) { el.content = newVal; saveBtn.dataset.dirty = 1; render(); select(el.id); }
            }
        });
        canvas.appendChild(node);

        if (el.id === selectedId) node.classList.add('selected');
    }

    function defaultWidth(el){
        switch(el.type){
            case 'image':          return el.role==='signature' ? 50 : 35;
            case 'qr':             return 28;
            case 'student_photo':  return 32;
            case 'line':           return 100;
            case 'field':          return 70;
            default:               return 120;
        }
    }
    function defaultHeight(el){
        switch(el.type){
            case 'image':          return el.role==='signature' ? 18 : 28;
            case 'qr':             return 28;
            case 'student_photo':  return 40;
            case 'line':           return 1;
            default:               return 10;
        }
    }

    function applyStyleToNode(node, el) {
        const fs = (el.fontSize ?? 12) * 1.0;  // keep 1:1 font size visually
        node.style.fontSize = fs + 'px';
        node.style.color    = el.color || '#1e293b';
        node.style.textAlign = el.align || 'left';
        node.style.fontWeight = el.fontWeight || 'normal';
        node.style.fontStyle  = el.fontStyle  || 'normal';

        if (el.type === 'line') {
            node.style.borderBottom = (el.border ?? 1) + 'px solid ' + (el.borderColor || '#000000');
        }
    }

    function applyContentToNode(node, el) {
        switch (el.type) {
            case 'text':
                node.style.whiteSpace = 'pre-wrap';
                node.textContent = el.content || '';
                break;
            case 'field':
                const labels = @json($dynamicFields);
                node.innerHTML = `<span style="background:rgba(99,102,241,.12);color:#4338ca;padding:1px 5px;border-radius:4px;border:1px dashed #818cf8;">${labels[el.field]||el.field||'[Field]'}</span>`;
                break;
            case 'image':
                node.textContent = el.role === 'signature' ? '🖋  Signature' : '🏫  Logo';
                break;
            case 'qr':
                node.textContent = '▦ QR';
                break;
            case 'student_photo':
                node.textContent = '📷 Photo';
                break;
            case 'line':
                node.textContent = '';
                break;
        }
    }

    // ── Dropdown toggles ─────────────────────────────────────────────
    function setupDropdown(btnId, dropdownId) {
        const btn = document.getElementById(btnId);
        const dropdown = document.getElementById(dropdownId);
        
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            // Close all other dropdowns
            document.querySelectorAll('[id$="Dropdown"]').forEach(d => {
                if (d.id !== dropdownId) d.classList.add('hidden');
            });
            dropdown.classList.toggle('hidden');
        });

        // Close when clicking outside
        document.addEventListener('click', (e) => {
            if (!btn.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    }

    setupDropdown('bgDropdownBtn', 'bgDropdown');
    setupDropdown('textDropdownBtn', 'textDropdown');
    setupDropdown('fieldDropdownBtn', 'fieldDropdown');
    setupDropdown('imageDropdownBtn', 'imageDropdown');

    // ── Drag & drop from palette ─────────────────────────────────────
    let newElPending = null;
    document.querySelectorAll('.palette-btn').forEach(btn => {
        btn.addEventListener('mousedown', (e) => {
            const ds = btn.dataset;
            newElPending = {
                id: newElId(),
                type: ds.type,
                x: 20, y: 30,
                w: null, h: null,
                fontSize: parseInt(ds.fontsize || 12),
                fontWeight: ds.fontweight || 'normal',
                fontStyle: 'normal',
                color: ds.color || '#1e293b',
                align: ds.align || 'left',
                content: ds.content || '',
                field: ds.field || '',
                role: ds.role || '',
                zIndex: Math.max(1, ...elements.map(x=>x.zIndex||1)) + 1,
                border: 1,
                borderColor: ds.color || '#000000',
            };
            const p = canvas.getBoundingClientRect();
            const syntheticEvent = {
                clientX: e.clientX,
                clientY: e.clientY,
                currentTarget: { dataset: { id: newElPending.id } },
                __newElInitial: true,
                __absX: e.clientX - p.left,
                __absY: e.clientY - p.top,
                preventDefault: ()=>{},
                stopPropagation: ()=>{}
            };
            startDrag(syntheticEvent);
        });
    });

    // ── Drag within canvas ───────────────────────────────────────────
    let dragState = null;
    function startDrag(e) {
        e.preventDefault();
        e.stopPropagation();

        let elId = e.currentTarget.dataset.id;
        const isNew = !!e.__newElInitial;
        if (isNew) {
            elId = newElPending.id;
            newElPending.x = Math.max(0, e.__absX / PX_PER_MM - 30);
            newElPending.y = Math.max(0, e.__absY / PX_PER_MM - 5);
            elements.push(newElPending);
            saveBtn.dataset.dirty = 1;
            render();
        }
        const elData = elements.find(x => x.id === elId);
        if (!elData) return;
        select(elId);
        const dx = e.clientX, dy = e.clientY;
        const node = [...canvas.children].find(c => c.dataset.id === elId);
        const origX = parseFloat(node.style.left);
        const origY = parseFloat(node.style.top);
        dragState = { mode:'move', elId, dx, dy, origX, origY };
        window.addEventListener('mousemove', onDrag);
        window.addEventListener('mouseup', endDrag);
    }

    function onDrag(e) {
        if (!dragState) return;
        const el = elements.find(x => x.id === dragState.elId);
        if (!el) return;
        const snap = document.getElementById('snapGrid').checked ? PX_PER_MM : 0.1;
        if (dragState.mode === 'move') {
            let nx = dragState.origX + (e.clientX - dragState.dx);
            let ny = dragState.origY + (e.clientY - dragState.dy);
            if (snap > 1) { nx = Math.round(nx/snap)*snap; ny = Math.round(ny/snap)*snap; }
            el.x = Math.max(0, Math.min(PAGE_W_MM - (el.w||10),  nx / PX_PER_MM));
            el.y = Math.max(0, Math.min(PAGE_H_MM - (el.h||10),  ny / PX_PER_MM));
        } else {
            let nw = dragState.origW + (e.clientX - dragState.dx);
            let nh = dragState.origH + (e.clientY - dragState.dy);
            el.w = Math.max(2, (nw / PX_PER_MM));
            el.h = Math.max(1, (nh / PX_PER_MM));
            if (snap > 1) {
                el.w = Math.round(el.w);
                el.h = Math.round(el.h);
            }
        }
        saveBtn.dataset.dirty = 1;
        render();
        if (el.id === selectedId) select(el.id);
    }

    function endDrag() {
        dragState = null;
        window.removeEventListener('mousemove', onDrag);
        window.removeEventListener('mouseup', endDrag);
    }

    function startResize(e) {
        e.preventDefault();
        e.stopPropagation();
        const node = e.currentTarget.parentElement;
        const elId = node.dataset.id;
        const el = elements.find(x => x.id === elId);
        if (!el) return;
        const w = parseFloat(node.style.width);
        const h = parseFloat(node.style.height);
        dragState = { mode:'resize', elId, dx:e.clientX, dy:e.clientY, origW:w, origH:h };
        window.addEventListener('mousemove', onDrag);
        window.addEventListener('mouseup', endDrag);
    }

    canvas.addEventListener('mousedown', (e) => {
        if (e.target === canvas) select(null);
    });

    // ── Select + Properties panel ────────────────────────────────────
    const fieldSelect = document.getElementById('p_field');
    Object.entries(@json($dynamicFields)).forEach(([k,v]) => {
        const opt = document.createElement('option');
        opt.value = k; opt.textContent = v;
        fieldSelect.appendChild(opt);
    });

    function select(id) {
        selectedId = id;
        canvas.querySelectorAll('.canvas-el').forEach(n => n.classList.toggle('selected', n.dataset.id === id));
        if (!id) {
            propsEl.classList.add('hidden');
            noSel.classList.remove('hidden');
            return;
        }
        noSel.classList.add('hidden');
        propsEl.classList.remove('hidden');
        document.getElementById('textProps').classList.add('hidden');
        document.getElementById('fieldProps').classList.add('hidden');
        document.getElementById('lineProps').classList.add('hidden');
        const el = elements.find(x => x.id === id); if (!el) return;
        document.getElementById('p_type').value = el.type
            + (el.role ? ' ('+el.role+')' : '') + (el.field ? ' ('+el.field+')' : '');
        document.getElementById('p_x').value = +el.x.toFixed(1);
        document.getElementById('p_y').value = +el.y.toFixed(1);
        const w = el.w ?? defaultWidth(el);
        const h = el.h ?? defaultHeight(el);
        document.getElementById('p_w').value = +w.toFixed(1);
        document.getElementById('p_h').value = +h.toFixed(1);

        if (el.type === 'text') {
            document.getElementById('textProps').classList.remove('hidden');
            document.getElementById('p_content').value = el.content || '';
            document.getElementById('p_fontSize').value = el.fontSize ?? 12;
            document.getElementById('p_color').value = el.color || '#1e293b';
            document.getElementById('p_align').value = el.align || 'left';
            document.getElementById('p_bold').checked = el.fontWeight === 'bold';
            document.getElementById('p_italic').checked = el.fontStyle === 'italic';
            document.getElementById('p_zIndex').value = el.zIndex || 1;
        } else if (el.type === 'field') {
            document.getElementById('fieldProps').classList.remove('hidden');
            fieldSelect.value = el.field || '';
            document.getElementById('p_fontSize2').value = el.fontSize ?? 12;
            document.getElementById('p_color2').value = el.color || '#1e293b';
            document.getElementById('p_align2').value = el.align || 'left';
            document.getElementById('p_bold2').checked = el.fontWeight === 'bold';
            document.getElementById('p_italic2').checked = el.fontStyle === 'italic';
            document.getElementById('p_zIndex2').value = el.zIndex || 1;
        } else if (el.type === 'line') {
            document.getElementById('lineProps').classList.remove('hidden');
            document.getElementById('p_border').value = el.border || 1;
            document.getElementById('p_borderColor').value = el.borderColor || '#000000';
        }
    }

    function propBind(id, key, cast = v => v, isTextProps = true) {
        document.getElementById(id).addEventListener('input', () => {
            const el = elements.find(x => x.id === selectedId); if (!el) return;
            const map = isTextProps ? {
                'p_content':'content','p_fontSize':'fontSize','p_color':'color','p_align':'align',
                'p_zIndex':'zIndex'
            } : {
                'p_fontSize2':'fontSize','p_color2':'color','p_align2':'align','p_zIndex2':'zIndex'
            };
            const k = key || map[id];
            if (id === 'p_bold')   el.fontWeight = document.getElementById('p_bold').checked ? 'bold' : 'normal';
            else if (id === 'p_bold2') el.fontWeight = document.getElementById('p_bold2').checked ? 'bold' : 'normal';
            else if (id === 'p_italic')   el.fontStyle = document.getElementById('p_italic').checked ? 'italic' : 'normal';
            else if (id === 'p_italic2')  el.fontStyle = document.getElementById('p_italic2').checked ? 'italic' : 'normal';
            else if (id === 'p_field')    el.field = fieldSelect.value;
            else el[k] = cast(document.getElementById(id).value);
            saveBtn.dataset.dirty = 1;
            render();
        });
    }
    ['p_x','p_y','p_w','p_h'].forEach(id => {
        document.getElementById(id).addEventListener('input', () => {
            const el = elements.find(x => x.id === selectedId); if (!el) return;
            const k = id.slice(2);
            const v = parseFloat(document.getElementById(id).value);
            if (!isNaN(v)) {
                el[k] = v;
                saveBtn.dataset.dirty = 1;
                render();
            }
        });
    });
    propBind('p_content');
    propBind('p_fontSize', 'fontSize', v => parseInt(v)||12, true);
    propBind('p_color','color');
    propBind('p_align','align');
    document.getElementById('p_bold').addEventListener('change', () => {
        const el = elements.find(x => x.id === selectedId); if (!el) return;
        el.fontWeight = document.getElementById('p_bold').checked ? 'bold':'normal';
        saveBtn.dataset.dirty = 1; render();
    });
    document.getElementById('p_italic').addEventListener('change', () => {
        const el = elements.find(x => x.id === selectedId); if (!el) return;
        el.fontStyle = document.getElementById('p_italic').checked ? 'italic':'normal';
        saveBtn.dataset.dirty = 1; render();
    });
    propBind('p_zIndex','zIndex', v => parseInt(v)||1);

    propBind('p_fontSize2','fontSize', v => parseInt(v)||12, false);
    propBind('p_color2','color', v => v, false);
    propBind('p_align2','align', v => v, false);
    document.getElementById('p_bold2').addEventListener('change', () => {
        const el = elements.find(x => x.id === selectedId); if (!el) return;
        el.fontWeight = document.getElementById('p_bold2').checked ? 'bold':'normal';
        saveBtn.dataset.dirty = 1; render();
    });
    document.getElementById('p_italic2').addEventListener('change', () => {
        const el = elements.find(x => x.id === selectedId); if (!el) return;
        el.fontStyle = document.getElementById('p_italic2').checked ? 'italic':'normal';
        saveBtn.dataset.dirty = 1; render();
    });
    propBind('p_zIndex2','zIndex', v => parseInt(v)||1, false);

    fieldSelect.addEventListener('change', () => {
        const el = elements.find(x => x.id === selectedId); if (!el) return;
        el.field = fieldSelect.value; saveBtn.dataset.dirty = 1; render();
    });

    document.getElementById('p_border').addEventListener('input', () => {
        const el = elements.find(x => x.id === selectedId); if (!el) return;
        el.border = parseInt(document.getElementById('p_border').value)||1;
        saveBtn.dataset.dirty = 1; render();
    });
    document.getElementById('p_borderColor').addEventListener('input', () => {
        const el = elements.find(x => x.id === selectedId); if (!el) return;
        el.borderColor = document.getElementById('p_borderColor').value;
        saveBtn.dataset.dirty = 1; render();
    });

    document.getElementById('delEl').addEventListener('click', () => {
        if (!selectedId) return;
        elements = elements.filter(x => x.id !== selectedId);
        saveBtn.dataset.dirty = 1;
        select(null);
        render();
    });

    // ── Paper size / orientation change (LIVE resize) ─────────────────
    document.getElementById('paperSize').addEventListener('change', () => {
        const paper = document.getElementById('paperSize').value;
        applyPageSize(paper, CURRENT_ORIENT, { clamp: true });
        saveBtn.dataset.dirty = 1;
        showToast('Paper size changed to ' + paper);
    });
    document.getElementById('orientation').addEventListener('change', () => {
        const orient = document.getElementById('orientation').value;
        applyPageSize(CURRENT_PAPER, orient, { swapWH: true, clamp: true });
        saveBtn.dataset.dirty = 1;
        showToast('Orientation: ' + orient.charAt(0).toUpperCase() + orient.slice(1));
    });

    // ── Background upload ────────────────────────────────────────────
    document.getElementById('bgFile').addEventListener('change', async (e) => {
        const f = e.target.files[0]; if (!f) return;
        const fd = new FormData();
        fd.append('background', f);
        try {
            const r = await fetch("{{ route('certificates.builder.background', $template->id) }}", {
                method: 'POST', headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }, body: fd
            });
            const j = await r.json();
            if (j.ok) {
                document.getElementById('bgThumb').src = j.url;
                document.getElementById('bgCurrent').classList.remove('hidden');
                canvas.style.backgroundImage = "url('"+j.url+"')";
                showToast('Background uploaded.');
            } else {
                showToast('Upload failed', 'err');
            }
        } catch(err){ showToast('Network error', 'err'); }
    });

    // ── Save ─────────────────────────────────────────────────────────
    saveBtn.addEventListener('click', async () => {
        saveBtn.classList.add('saving'); saveBtn.disabled = true;
        const editable = [...document.querySelectorAll('.editable-check:checked')].map(c => c.value);
        const payload = {
            builder_layout:  elements,
            editable_fields: editable,
            paper_size:      document.getElementById('paperSize').value,
            orientation:     document.getElementById('orientation').value,
            _token: "{{ csrf_token() }}"
        };
        try {
            const r = await fetch("{{ route('certificates.builder.save', $template->id) }}", {
                method: 'POST', headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                }, body: JSON.stringify(payload)
            });
            const j = await r.json();
            if (j.ok) {
                saveBtn.dataset.dirty = '';
                showToast('Template saved · ' + j.count + ' elements');
            } else showToast('Save failed', 'err');
        } catch(err){ showToast('Network error', 'err'); }
        finally { saveBtn.classList.remove('saving'); saveBtn.disabled = false; }
    });

    window.addEventListener('keydown', (e) => {
        if (!selectedId) return;
        const el = elements.find(x => x.id === selectedId); if (!el) return;
        const step = e.shiftKey ? 5 : 1;
        if (e.key === 'Delete' || e.key === 'Backspace') {
            if (document.activeElement && ['INPUT','TEXTAREA','SELECT'].includes(document.activeElement.tagName)) return;
            e.preventDefault();
            elements = elements.filter(x => x.id !== selectedId);
            saveBtn.dataset.dirty = 1;
            select(null); render();
        } else if (e.key === 'ArrowLeft')  { el.x = Math.max(0, el.x-step); saveBtn.dataset.dirty=1; render(); select(el.id); }
        else if (e.key === 'ArrowRight') { el.x = Math.min(PAGE_W_MM-(el.w||defaultWidth(el)), el.x+step); saveBtn.dataset.dirty=1; render(); select(el.id); }
        else if (e.key === 'ArrowUp')    { el.y = Math.max(0, el.y-step); saveBtn.dataset.dirty=1; render(); select(el.id); }
        else if (e.key === 'ArrowDown')  { el.y = Math.min(PAGE_H_MM-(el.h||defaultHeight(el)), el.y+step); saveBtn.dataset.dirty=1; render(); select(el.id); }
    });

    render();
})();
</script>
@endpush
