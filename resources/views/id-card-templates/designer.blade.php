@extends('layouts.app')
@section('title', 'ID Card Designer')

@section('content')
<div class="min-h-screen bg-slate-100">

{{-- ═══════════════════════════ TOP BAR ═══════════════════════════ --}}
<div class="bg-white border-b border-slate-200 sticky top-0 z-40">
  <div class="flex items-center justify-between px-4 py-2.5 gap-3">
    <div class="flex items-center gap-3 min-w-0">
      <a href="{{ route('id-card-templates.index') }}"
         class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-500 transition flex-shrink-0">
        <i class="bi bi-arrow-left text-lg"></i>
      </a>
      <div class="w-px h-5 bg-slate-200 flex-shrink-0"></div>
      <i class="bi bi-credit-card-2-front-fill text-indigo-600 text-lg flex-shrink-0"></i>
      <div class="min-w-0">
        <h1 class="text-sm font-bold text-slate-800 truncate">ID Card Designer</h1>
        <p class="text-xs text-slate-400 truncate">
          {{ $template->id ? 'Editing: ' . $template->name : 'New Template' }}
        </p>
      </div>
    </div>
    <div class="flex items-center gap-2 flex-shrink-0">
      <button type="button" id="btn-refresh-preview"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold
               bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition">
        <i class="bi bi-arrow-clockwise"></i> Refresh
      </button>
      <button type="submit" form="designer-form"
        class="inline-flex items-center gap-1.5 px-4 py-1.5 text-xs font-bold
               bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow transition">
        <i class="bi bi-floppy-fill"></i> Save Template
      </button>
    </div>
  </div>
</div>

{{-- ═══════════════ MAIN LAYOUT: LEFT PANELS + CANVAS ════════════ --}}
<div class="flex h-[calc(100vh-52px)]">

{{-- ═══════════ LEFT SIDEBAR (tabbed panels) ═══════════ --}}
<div class="w-72 flex-shrink-0 bg-white border-r border-slate-200 flex flex-col overflow-hidden">

  {{-- Tab bar --}}
  <div class="flex border-b border-slate-200 bg-slate-50 text-xs font-semibold">
    @foreach([
      ['header','bi-layout-text-window','Header'],
      ['photo','bi-person-square','Photo'],
      ['fields','bi-list-ul','Fields'],
      ['footer','bi-layout-text-sidebar-reverse','Footer'],
      ['style','bi-palette-fill','Style'],
    ] as [$tab,$icon,$label])
    <button type="button"
      data-tab="{{ $tab }}"
      class="tab-btn flex-1 flex flex-col items-center gap-0.5 py-2 border-b-2
             text-slate-400 hover:text-indigo-600 transition
             {{ $loop->first ? 'border-indigo-600 text-indigo-600' : 'border-transparent' }}">
      <i class="bi {{ $icon }} text-base"></i>
      <span class="text-[10px]">{{ $label }}</span>
    </button>
    @endforeach
  </div>

  {{-- Scrollable panel area --}}
  <div class="flex-1 overflow-y-auto p-4 space-y-4 text-xs" id="panel-container">

    {{-- Form wraps ALL panels and the hidden inputs --}}
    <form id="designer-form" method="POST"
      action="{{ $template->id ? route('id-card-templates.save', $template->id) : route('id-card-templates.store') }}"
      enctype="multipart/form-data">
      @csrf
      @if($template->id) @method('PUT') @endif
      <input type="hidden" name="name" id="inp-name" value="{{ old('name', $template->name ?? 'My ID Card Template') }}">


      {{-- ══ PANEL: HEADER ══ --}}
      <div data-panel="header" class="panel-section space-y-3">

        <div class="designer-field-group">
          <label class="designer-label">Template Name</label>
          <input type="text" id="disp-name"
            class="designer-input"
            value="{{ old('name', $template->name ?? 'My ID Card Template') }}"
            oninput="document.getElementById('inp-name').value=this.value"
            placeholder="e.g. Standard Blue">
        </div>

        <div class="designer-field-group">
          <label class="designer-label">Header Background</label>
          <div class="flex gap-2 items-center">
            <input type="color" name="header_bg_color" id="header_bg_color"
              class="designer-color" value="{{ old('header_bg_color',$template->header_bg_color) }}">
            <input type="text" id="header_bg_color_txt"
              class="designer-input flex-1" value="{{ old('header_bg_color',$template->header_bg_color) }}">
          </div>
        </div>

        <div class="designer-field-group">
          <label class="designer-label">Header Text Color</label>
          <div class="flex gap-2 items-center">
            <input type="color" name="header_text_color" id="header_text_color"
              class="designer-color" value="{{ old('header_text_color',$template->header_text_color) }}">
            <input type="text" id="header_text_color_txt"
              class="designer-input flex-1" value="{{ old('header_text_color',$template->header_text_color) }}">
          </div>
        </div>

        <div class="designer-field-group">
          <label class="designer-label">Accent / Stripe Color</label>
          <div class="flex gap-2 items-center">
            <input type="color" name="accent_color" id="accent_color"
              class="designer-color" value="{{ old('accent_color',$template->accent_color) }}">
            <input type="text" id="accent_color_txt"
              class="designer-input flex-1" value="{{ old('accent_color',$template->accent_color) }}">
          </div>
        </div>

        <div class="designer-field-group">
          <label class="designer-label">Header Height
            <span class="text-slate-400 ml-1" id="header_height_val">{{ $template->header_height }}pt</span>
          </label>
          <input type="range" name="header_height" id="header_height" min="28" max="60" step="2"
            class="w-full accent-indigo-600"
            value="{{ old('header_height',$template->header_height) }}"
            oninput="document.getElementById('header_height_val').textContent=this.value+'pt'">
        </div>

        <div class="space-y-2">
          @foreach([['show_logo','Show Logo'],['show_school_name','Show School Name'],['show_tagline','Show Tagline']] as [$n,$l])
          <label class="designer-toggle">
            <input type="checkbox" name="{{ $n }}" id="{{ $n }}" class="toggle-input"
              {{ old($n, $template->$n ?? true) ? 'checked' : '' }}>
            <span class="toggle-thumb"></span>
            <span>{{ $l }}</span>
          </label>
          @endforeach
        </div>

        <div class="designer-field-group" id="tagline-text-wrap">
          <label class="designer-label">Tagline Text</label>
          <input type="text" name="tagline_text"
            class="designer-input" value="{{ old('tagline_text',$template->tagline_text) }}">
        </div>

      </div>{{-- /panel header --}}


      {{-- ══ PANEL: PHOTO ══ --}}
      <div data-panel="photo" class="panel-section space-y-3 hidden">

        <label class="designer-toggle">
          <input type="checkbox" name="show_photo" id="show_photo" class="toggle-input"
            {{ old('show_photo', $template->show_photo ?? true) ? 'checked' : '' }}>
          <span class="toggle-thumb"></span>
          <span>Show Student Photo</span>
        </label>

        <div class="designer-field-group">
          <label class="designer-label">Photo Size
            <span class="text-slate-400 ml-1" id="photo_size_val">{{ $template->photo_size }}pt</span>
          </label>
          <input type="range" name="photo_size" id="photo_size" min="30" max="70" step="2"
            class="w-full accent-indigo-600"
            value="{{ old('photo_size',$template->photo_size) }}"
            oninput="document.getElementById('photo_size_val').textContent=this.value+'pt'">
        </div>

        <div class="designer-field-group">
          <label class="designer-label">Photo Shape</label>
          <div class="flex gap-2">
            @foreach(['square'=>'Square / Rounded','circle'=>'Circle'] as $val=>$lbl)
            <label class="flex-1 flex flex-col items-center gap-1 p-2 rounded-lg border-2 cursor-pointer
              {{ old('photo_shape',$template->photo_shape)==$val ? 'border-indigo-500 bg-indigo-50' : 'border-slate-200 hover:border-indigo-300' }}
              photo-shape-opt" data-val="{{ $val }}">
              <input type="radio" name="photo_shape" value="{{ $val }}" class="sr-only"
                {{ old('photo_shape',$template->photo_shape)==$val ? 'checked' : '' }}>
              <div class="w-8 h-8 bg-slate-300
                {{ $val=='circle' ? 'rounded-full' : 'rounded' }}"></div>
              <span class="text-[10px] font-medium text-slate-600">{{ $lbl }}</span>
            </label>
            @endforeach
          </div>
        </div>

        <label class="designer-toggle">
          <input type="checkbox" name="show_photo_corner_marks" id="show_photo_corner_marks" class="toggle-input"
            {{ old('show_photo_corner_marks', $template->show_photo_corner_marks ?? true) ? 'checked' : '' }}>
          <span class="toggle-thumb"></span>
          <span>Corner Accent Marks</span>
        </label>

        <label class="designer-toggle">
          <input type="checkbox" name="show_blood_type" id="show_blood_type" class="toggle-input"
            {{ old('show_blood_type', $template->show_blood_type ?? true) ? 'checked' : '' }}>
          <span class="toggle-thumb"></span>
          <span>Show Blood Type Badge</span>
        </label>

      </div>{{-- /panel photo --}}


      {{-- ══ PANEL: FIELDS ══ --}}
      <div data-panel="fields" class="panel-section space-y-3 hidden">

        <p class="text-slate-500 text-[11px] leading-relaxed">
          Drag to reorder. Toggle to show/hide.
        </p>

        <div class="designer-field-group">
          <label class="designer-label">Name Font Size
            <span class="text-slate-400 ml-1" id="name_font_size_val">{{ $template->name_font_size }}pt</span>
          </label>
          <input type="range" name="name_font_size" id="name_font_size" min="7" max="16" step="1"
            class="w-full accent-indigo-600"
            value="{{ old('name_font_size',$template->name_font_size) }}"
            oninput="document.getElementById('name_font_size_val').textContent=this.value+'pt'">
        </div>

        <div class="designer-field-group">
          <label class="designer-label">Name Color</label>
          <div class="flex gap-2 items-center">
            <input type="color" name="name_text_color" id="name_text_color"
              class="designer-color" value="{{ old('name_text_color',$template->name_text_color) }}">
            <input type="text" id="name_text_color_txt"
              class="designer-input flex-1" value="{{ old('name_text_color',$template->name_text_color) }}">
          </div>
        </div>

        <div class="space-y-2">
          @foreach([['name_uppercase','Uppercase Name'],['show_role_badge','Show "Student" Badge']] as [$n,$l])
          <label class="designer-toggle">
            <input type="checkbox" name="{{ $n }}" id="{{ $n }}" class="toggle-input"
              {{ old($n, $template->$n ?? true) ? 'checked' : '' }}>
            <span class="toggle-thumb"></span>
            <span>{{ $l }}</span>
          </label>
          @endforeach
        </div>

        <div class="designer-field-group">
          <label class="designer-label">Body Background</label>
          <div class="flex gap-2 items-center">
            <input type="color" name="body_bg_color" id="body_bg_color"
              class="designer-color" value="{{ old('body_bg_color',$template->body_bg_color) }}">
            <input type="text" id="body_bg_color_txt"
              class="designer-input flex-1" value="{{ old('body_bg_color',$template->body_bg_color) }}">
          </div>
        </div>

        <div class="designer-field-group">
          <label class="designer-label mb-1">Visible Info Fields</label>
          <div id="sortable-fields" class="space-y-1.5">
            @php
              $orderedFields = $visibleFields;
              foreach(array_keys($allFields) as $fk) {
                  if(!in_array($fk,$orderedFields)) $orderedFields[]=$fk;
              }
            @endphp
            @foreach($orderedFields as $fk)
            @if(isset($allFields[$fk]))
            <div class="sortable-field-row flex items-center gap-2 bg-slate-50 border border-slate-200
                        rounded-lg px-2.5 py-2 cursor-grab active:cursor-grabbing"
                 data-field="{{ $fk }}">
              <i class="bi bi-grip-vertical text-slate-300 flex-shrink-0"></i>
              <label class="flex items-center gap-2 flex-1 cursor-pointer">
                <input type="checkbox" name="visible_fields[]" value="{{ $fk }}" class="field-checkbox"
                  {{ in_array($fk,$visibleFields) ? 'checked' : '' }}>
                <span class="font-medium text-slate-700">{{ $allFields[$fk] }}</span>
              </label>
            </div>
            @endif
            @endforeach
          </div>
        </div>

      </div>{{-- /panel fields --}}


      {{-- ══ PANEL: FOOTER ══ --}}
      <div data-panel="footer" class="panel-section space-y-3 hidden">

        <div class="designer-field-group">
          <label class="designer-label">Footer Background</label>
          <div class="flex gap-2 items-center">
            <input type="color" name="footer_bg_color" id="footer_bg_color"
              class="designer-color" value="{{ old('footer_bg_color',$template->footer_bg_color) }}">
            <input type="text" id="footer_bg_color_txt"
              class="designer-input flex-1" value="{{ old('footer_bg_color',$template->footer_bg_color) }}">
          </div>
        </div>

        <div class="designer-field-group">
          <label class="designer-label">Footer Text Color</label>
          <div class="flex gap-2 items-center">
            <input type="color" name="footer_text_color" id="footer_text_color"
              class="designer-color" value="{{ old('footer_text_color',$template->footer_text_color) }}">
            <input type="text" id="footer_text_color_txt"
              class="designer-input flex-1" value="{{ old('footer_text_color',$template->footer_text_color) }}">
          </div>
        </div>

        <div class="space-y-2">
          @foreach([
            ['show_id_number','Show ID Number'],
            ['show_session','Show Session Year'],
            ['show_qr','Show QR Code'],
            ['show_signature_line','Show Signature Line'],
          ] as [$n,$l])
          <label class="designer-toggle">
            <input type="checkbox" name="{{ $n }}" id="{{ $n }}" class="toggle-input"
              {{ old($n, $template->$n ?? true) ? 'checked' : '' }}>
            <span class="toggle-thumb"></span>
            <span>{{ $l }}</span>
          </label>
          @endforeach
        </div>

        <div class="designer-field-group" id="sig-label-wrap">
          <label class="designer-label">Signature Label</label>
          <input type="text" name="signature_label"
            class="designer-input" value="{{ old('signature_label',$template->signature_label ?? 'Principal') }}"
            placeholder="e.g. Principal">
        </div>

        <div class="designer-field-group">
          <label class="designer-label">Footer Left Text <span class="text-slate-400">(optional)</span></label>
          <input type="text" name="footer_left_text"
            class="designer-input" value="{{ old('footer_left_text',$template->footer_left_text) }}"
            placeholder="e.g. +977-01-XXXXXXX">
        </div>

        <div class="designer-field-group">
          <label class="designer-label">Footer Center Text <span class="text-slate-400">(optional)</span></label>
          <input type="text" name="footer_center_text"
            class="designer-input" value="{{ old('footer_center_text',$template->footer_center_text) }}"
            placeholder="e.g. www.school.edu.np">
        </div>

      </div>{{-- /panel footer --}}


      {{-- ══ PANEL: STYLE (background) ══ --}}
      <div data-panel="style" class="panel-section space-y-3 hidden">

        <div class="designer-field-group">
          <label class="designer-label">Background Image <span class="text-slate-400">(optional)</span></label>
          <label class="flex flex-col items-center justify-center w-full h-20 border-2 border-dashed
                        border-slate-300 rounded-xl cursor-pointer hover:border-indigo-400
                        hover:bg-indigo-50 transition text-slate-400">
            <i class="bi bi-image text-2xl mb-1"></i>
            <span class="text-[11px]">Click to upload</span>
            <input type="file" name="background_image" accept="image/*" class="sr-only"
              id="bg-image-input">
          </label>
          @if(isset($template->background_image_path) && $template->background_image_path)
          <div class="flex items-center gap-2 mt-1.5 text-xs text-slate-500">
            <i class="bi bi-check-circle-fill text-green-500"></i>
            <span class="truncate">Current: {{ basename($template->background_image_path) }}</span>
            <label class="ml-auto flex items-center gap-1 text-rose-500 cursor-pointer">
              <input type="checkbox" name="remove_background" value="1"> Remove
            </label>
          </div>
          @endif
        </div>

        <div class="designer-field-group">
          <label class="designer-label">Background Opacity
            <span class="text-slate-400 ml-1" id="bg_opacity_val">{{ $template->background_opacity ?? 10 }}%</span>
          </label>
          <input type="range" name="background_opacity" id="background_opacity" min="0" max="80" step="5"
            class="w-full accent-indigo-600"
            value="{{ old('background_opacity',$template->background_opacity ?? 10) }}"
            oninput="document.getElementById('bg_opacity_val').textContent=this.value+'%'">
        </div>

        <div class="pt-2 border-t border-slate-100">
          <p class="text-[11px] font-semibold text-slate-500 mb-2">Quick Presets</p>
          <div class="grid grid-cols-3 gap-2" id="preset-swatches">
            @foreach([
              ['Classic Blue','#1a56db','#f59e0b'],
              ['Forest Green','#16a34a','#f59e0b'],
              ['Royal Purple','#7c3aed','#f59e0b'],
              ['Deep Red','#dc2626','#fbbf24'],
              ['Ocean Teal','#0891b2','#f59e0b'],
              ['Dark Slate','#1e293b','#64748b'],
            ] as [$pname,$hdr,$acc])
            <button type="button"
              class="preset-btn flex flex-col items-center gap-1.5 p-2 rounded-xl border-2
                     border-slate-200 hover:border-indigo-400 transition"
              data-hdr="{{ $hdr }}" data-acc="{{ $acc }}">
              <div class="w-8 h-8 rounded-lg shadow-sm" style="background:{{ $hdr }};"></div>
              <span class="text-[9px] text-slate-500 font-medium leading-tight text-center">{{ $pname }}</span>
            </button>
            @endforeach
          </div>
        </div>

      </div>{{-- /panel style --}}

      </form>{{-- end form --}}
    </div>{{-- /panel-container --}}
  </div>{{-- /LEFT SIDEBAR --}}


  {{-- ══════════ CANVAS AREA ══════════ --}}
  <div class="flex-1 flex flex-col overflow-hidden bg-slate-200">

    {{-- Canvas toolbar --}}
    <div class="flex items-center justify-between px-4 py-2 bg-white border-b border-slate-200 text-xs">
      <div class="flex items-center gap-3 text-slate-500">
        <i class="bi bi-display text-base"></i>
        <span class="font-medium">Live Preview  &mdash;  CR-80 (85.6 × 54 mm)</span>
        <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full font-mono text-[10px]">
          242 × 153 pt
        </span>
      </div>
      <div class="flex items-center gap-2">
        <button type="button" id="zoom-out" class="p-1 hover:bg-slate-100 rounded text-slate-500">
          <i class="bi bi-dash-lg"></i>
        </button>
        <span id="zoom-label" class="text-[11px] font-mono text-slate-600 w-12 text-center">150%</span>
        <button type="button" id="zoom-in" class="p-1 hover:bg-slate-100 rounded text-slate-500">
          <i class="bi bi-plus-lg"></i>
        </button>
        <button type="button" id="zoom-reset" class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200
               text-slate-600 rounded text-[10px] font-medium">Reset</button>
      </div>
    </div>

    {{-- Canvas scroll container --}}
    <div class="flex-1 overflow-auto flex items-center justify-center p-10" id="canvas-scroll">
      <div id="canvas-outer" style="position:relative;">
        <div id="canvas-wrap" style="transform:scale(1.5); transform-origin:top left; display:inline-block;">

          {{-- Card preview rendered via AJAX --}}
          <div id="card-preview-container">
            @include('id-card-templates._card-preview-shell', ['t' => $template, 'school' => config('tenant.school')])
          </div>

        </div>

        {{-- Loading overlay --}}
        <div id="preview-loading"
             class="hidden" style="position:absolute;inset:0;display:flex;align-items:center;
                    justify-content:center;background:rgba(255,255,255,0.85);border-radius:12px;z-index:10;">
          <div style="display:flex;flex-direction:column;align-items:center;gap:8px;color:#94a3b8;">
            <svg class="animate-spin" style="width:24px;height:24px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
            <span style="font-size:12px;">Updating preview…</span>
          </div>
        </div>

      </div>
    </div>

    {{-- Front / Back toggle hint --}}
    <div class="bg-white border-t border-slate-200 px-4 py-2 text-xs text-slate-400 flex items-center gap-2">
      <i class="bi bi-info-circle"></i>
      Changes are previewed live. Click <strong class="text-slate-600">Save Template</strong> to persist,
      then <strong class="text-slate-600">Set Active</strong> on the templates list to use it for PDF generation.
    </div>

  </div>{{-- /canvas --}}

</div>{{-- /main layout --}}
</div>{{-- /min-h-screen --}}


@push('styles')
<style>
/* ── Designer UI chrome only ──────────────────────────────── */
.designer-label {
  display:block; font-size:11px; font-weight:600;
  color:#475569; margin-bottom:4px;
}
.designer-input {
  width:100%; font-size:11px; padding:6px 8px;
  border:1px solid #e2e8f0; border-radius:8px;
  background:#fff; color:#1e293b; outline:none;
  transition:border-color .15s;
}
.designer-input:focus { border-color:#6366f1; box-shadow:0 0 0 2px rgba(99,102,241,.15); }
.designer-color {
  width:32px; height:32px; border:none; background:none;
  cursor:pointer; border-radius:6px; padding:0; flex-shrink:0;
}
.designer-field-group { margin-bottom:4px; }
.designer-toggle {
  display:flex; align-items:center; gap:8px; cursor:pointer;
  font-size:11px; color:#334155; font-weight:500;
  padding:4px 0;
}
.toggle-input { display:none; }
.toggle-thumb {
  width:32px; height:18px; background:#cbd5e1; border-radius:20px;
  position:relative; flex-shrink:0; transition:background .2s;
}
.toggle-thumb::after {
  content:''; position:absolute; top:2px; left:2px;
  width:14px; height:14px; border-radius:50%; background:#fff;
  transition:transform .2s; box-shadow:0 1px 3px rgba(0,0,0,.2);
}
.toggle-input:checked ~ .toggle-thumb { background:#4f46e5; }
.toggle-input:checked ~ .toggle-thumb::after { transform:translateX(14px); }
.sortable-field-row { user-select:none; }
.sortable-field-row.drag-over { border-color:#6366f1; background:#eef2ff; }

/* Canvas area */
#canvas-outer {
  display:inline-block;
  /* Give it space for the scaled card (242px * 1.333 * 1.5 ≈ 484px wide) */
  width: 484px;
  height: 308px;
}
</style>
@endpush


@push('scripts')
<script>
(function () {
  /* ── Helpers ─────────────────────────────────────────────────── */
  const q  = (s, ctx=document) => ctx.querySelector(s);
  const qa = (s, ctx=document) => [...ctx.querySelectorAll(s)];

  /* ── Tab switching ───────────────────────────────────────────── */
  qa('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      qa('.tab-btn').forEach(b => {
        b.classList.replace('border-indigo-600','border-transparent');
        b.classList.replace('text-indigo-600','text-slate-400');
      });
      btn.classList.replace('border-transparent','border-indigo-600');
      btn.classList.replace('text-slate-400','text-indigo-600');
      const target = btn.dataset.tab;
      qa('.panel-section').forEach(p => p.classList.add('hidden'));
      q(`[data-panel="${target}"]`)?.classList.remove('hidden');
    });
  });

  /* ── Color pickers sync with text inputs ─────────────────────── */
  const colorPairs = [
    ['header_bg_color',   'header_bg_color_txt'],
    ['header_text_color', 'header_text_color_txt'],
    ['accent_color',      'accent_color_txt'],
    ['name_text_color',   'name_text_color_txt'],
    ['body_bg_color',     'body_bg_color_txt'],
    ['footer_bg_color',   'footer_bg_color_txt'],
    ['footer_text_color', 'footer_text_color_txt'],
  ];
  colorPairs.forEach(([pickId, txtId]) => {
    const pick = q(`#${pickId}`);
    const txt  = q(`#${txtId}`);
    if (!pick || !txt) return;
    pick.addEventListener('input', () => { txt.value = pick.value; schedulePreview(); });
    txt.addEventListener('input', () => {
      if (/^#[0-9a-fA-F]{6}$/.test(txt.value)) {
        pick.value = txt.value;
        schedulePreview();
      }
    });
  });

  /* ── Preset swatches ─────────────────────────────────────────── */
  qa('.preset-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const hdr = btn.dataset.hdr;
      const acc = btn.dataset.acc;
      ['header_bg_color','header_text_color'].forEach(id => {
        const p = q(`#${id}`);
        const t = q(`#${id}_txt`);
        if (id === 'header_bg_color') { if(p) p.value=hdr; if(t) t.value=hdr; }
      });
      const ap = q('#accent_color');
      const at = q('#accent_color_txt');
      if(ap) ap.value=acc; if(at) at.value=acc;
      schedulePreview();
    });
  });

  /* ── Photo shape toggle visual ───────────────────────────────── */
  qa('.photo-shape-opt').forEach(opt => {
    opt.addEventListener('click', () => {
      qa('.photo-shape-opt').forEach(o => {
        o.classList.replace('border-indigo-500','border-slate-200');
        o.classList.remove('bg-indigo-50');
      });
      opt.classList.replace('border-slate-200','border-indigo-500');
      opt.classList.add('bg-indigo-50');
      opt.querySelector('input').checked = true;
      schedulePreview();
    });
  });

  /* ── Zoom control ────────────────────────────────────────────── */
  let zoom = 1.5;
  const wrap  = q('#canvas-wrap');
  const outer = q('#canvas-outer');
  // Card native size in px (242pt * 1.333)
  const CARD_W = Math.round(242 * 1.333);
  const CARD_H = Math.round(153 * 1.333);
  const updateZoom = () => {
    wrap.style.transform = `scale(${zoom})`;
    wrap.style.transformOrigin = 'top left';
    // Resize outer so the scroll container accounts for scaled size
    if (outer) {
      outer.style.width  = Math.round(CARD_W * zoom) + 'px';
      outer.style.height = Math.round(CARD_H * zoom) + 'px';
    }
    q('#zoom-label').textContent = Math.round(zoom*100)+'%';
  };
  q('#zoom-in') .addEventListener('click', () => { zoom = Math.min(zoom+0.25,3);   updateZoom(); });
  q('#zoom-out').addEventListener('click', () => { zoom = Math.max(zoom-0.25,0.5); updateZoom(); });
  q('#zoom-reset').addEventListener('click', () => { zoom=1.5; updateZoom(); });
  updateZoom(); // set initial outer size

  /* ── Drag-to-reorder fields ──────────────────────────────────── */
  const sortable = q('#sortable-fields');
  let dragSrc = null;
  sortable?.addEventListener('dragstart', e => {
    dragSrc = e.target.closest('.sortable-field-row');
    e.dataTransfer.effectAllowed = 'move';
  });
  sortable?.addEventListener('dragover', e => {
    e.preventDefault();
    const target = e.target.closest('.sortable-field-row');
    if (target && target !== dragSrc) {
      target.classList.add('drag-over');
    }
  });
  sortable?.addEventListener('dragleave', e => {
    e.target.closest('.sortable-field-row')?.classList.remove('drag-over');
  });
  sortable?.addEventListener('drop', e => {
    e.preventDefault();
    const target = e.target.closest('.sortable-field-row');
    if (target && target !== dragSrc) {
      target.classList.remove('drag-over');
      const items = qa('.sortable-field-row', sortable);
      const srcIdx = items.indexOf(dragSrc);
      const tgtIdx = items.indexOf(target);
      if (srcIdx < tgtIdx) target.after(dragSrc);
      else target.before(dragSrc);
      schedulePreview();
    }
  });
  qa('.sortable-field-row').forEach(r => r.setAttribute('draggable','true'));

  /* ── Live preview ────────────────────────────────────────────── */
  let previewTimer = null;
  const previewUrl = '{{ route("id-card-templates.preview") }}';

  function schedulePreview(delay=400) {
    clearTimeout(previewTimer);
    previewTimer = setTimeout(doPreview, delay);
  }

  function doPreview() {
    const form = q('#designer-form');
    const data = new FormData(form);
    // Include all checkboxes as booleans
    [
      'show_logo','show_school_name','show_tagline','show_photo',
      'show_photo_corner_marks','show_blood_type','name_uppercase',
      'show_role_badge','show_id_number','show_session','show_qr',
      'show_signature_line',
    ].forEach(n => {
      if (!data.has(n)) data.set(n, '0');
    });
    // Send ordered visible_fields
    data.delete('visible_fields[]');
    qa('.sortable-field-row .field-checkbox:checked').forEach(cb => {
      data.append('visible_fields[]', cb.value);
    });
    // Also include template name
    data.set('name', q('#inp-name').value);

    const container = q('#card-preview-container');
    const loading   = q('#preview-loading');
    if(loading) loading.classList.remove('hidden');

    fetch(previewUrl, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
      body: data,
    })
    .then(r => r.text())
    .then(html => {
      container.innerHTML = html;
    })
    .catch(console.error)
    .finally(() => { if(loading) loading.classList.add('hidden'); });
  }

  /* ── Auto-trigger preview on any form change ─────────────────── */
  q('#designer-form')?.addEventListener('input',  () => schedulePreview());
  q('#designer-form')?.addEventListener('change', () => schedulePreview());
  q('#btn-refresh-preview')?.addEventListener('click', () => doPreview());

  /* Initial preview */
  doPreview();
})();
</script>
@endpush

@endsection
