@extends('layouts.app')
@section('title', 'ID Card Templates')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">

  {{-- Header --}}
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-2xl font-bold text-slate-800">ID Card Templates</h1>
      <p class="text-sm text-slate-500 mt-0.5">Design and manage card layouts for student ID generation.</p>
    </div>
    <a href="{{ route('id-card-templates.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700
              text-white text-sm font-semibold rounded-xl shadow transition">
      <i class="bi bi-plus-lg"></i> New Template
    </a>
  </div>

  @if(session('status'))
  <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl">
    <i class="bi bi-check-circle-fill mr-1"></i> {{ session('status') }}
  </div>
  @endif

  @if($templates->isEmpty())
  <div class="flex flex-col items-center justify-center py-20 text-center text-slate-400">
    <i class="bi bi-credit-card-2-front text-6xl mb-4"></i>
    <h3 class="text-lg font-semibold text-slate-600 mb-1">No templates yet</h3>
    <p class="text-sm mb-4">Create your first ID card template to get started.</p>
    <a href="{{ route('id-card-templates.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white
              text-sm font-semibold rounded-xl shadow hover:bg-indigo-700 transition">
      <i class="bi bi-plus-lg"></i> Create Template
    </a>
  </div>
  @else
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
    @foreach($templates as $tpl)
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden
                hover:shadow-md transition flex flex-col">

      {{-- Color preview swatch --}}
      <div class="h-14 relative flex items-center px-4 gap-3"
           style="background:{{ $tpl->header_bg_color }};">
        <div class="w-8 h-8 rounded-full bg-white/20 border border-white/40
                    flex items-center justify-center flex-shrink-0">
          <i class="bi bi-credit-card-2-front text-white text-sm"></i>
        </div>
        <div>
          <div class="text-sm font-bold text-white truncate">{{ $tpl->name }}</div>
          <div class="text-[11px] text-white/60">{{ $tpl->tagline_text }}</div>
        </div>
        @if($tpl->is_active)
        <span class="absolute top-2 right-2 bg-green-400 text-white
                     text-[9px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wide">
          Active
        </span>
        @endif
        <div class="absolute bottom-0 left-0 right-0 h-1"
             style="background:{{ $tpl->accent_color }};"></div>
      </div>

      {{-- Details --}}
      <div class="px-4 py-3 flex-1 text-xs text-slate-500 space-y-1">
        <div class="flex items-center gap-1.5">
          <span class="w-2 h-2 rounded-full inline-block"
                style="background:{{ $tpl->accent_color }}"></span>
          Accent: <span class="font-mono">{{ $tpl->accent_color }}</span>
        </div>
        <div class="flex items-center gap-1.5">
          <i class="bi bi-person-square text-slate-400"></i>
          Photo: {{ $tpl->photo_size }}pt {{ $tpl->photo_shape }}
          @if($tpl->show_photo_corner_marks) · corner marks @endif
        </div>
        <div class="flex items-center gap-1.5">
          <i class="bi bi-list-ul text-slate-400"></i>
          Fields: {{ implode(', ', array_map(fn($f) => \App\Models\IdCardTemplate::ALL_FIELDS[$f] ?? $f, $tpl->visible_fields ?? [])) ?: '—' }}
        </div>
      </div>

      {{-- Actions --}}
      <div class="border-t border-slate-100 px-4 py-2.5 flex items-center gap-2">
        <a href="{{ route('id-card-templates.edit', $tpl->id) }}"
           class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-1.5
                  bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold
                  rounded-lg transition">
          <i class="bi bi-pencil-fill"></i> Edit
        </a>
        @unless($tpl->is_active)
        <form method="POST" action="{{ route('id-card-templates.activate', $tpl->id) }}" class="flex-1">
          @csrf @method('PATCH')
          <button type="submit"
            class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-1.5
                   bg-green-50 hover:bg-green-100 text-green-700 text-xs font-semibold
                   rounded-lg transition">
            <i class="bi bi-check-circle-fill"></i> Set Active
          </button>
        </form>
        @else
        <span class="flex-1 inline-flex items-center justify-center gap-1 text-xs
                     text-green-600 font-semibold py-1.5">
          <i class="bi bi-check-circle-fill"></i> In Use
        </span>
        @endunless
        <form method="POST" action="{{ route('id-card-templates.destroy', $tpl->id) }}"
              onsubmit="return confirm('Delete this template?')">
          @csrf @method('DELETE')
          <button type="submit"
            class="inline-flex items-center justify-center w-8 h-8
                   bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg transition">
            <i class="bi bi-trash3-fill text-xs"></i>
          </button>
        </form>
      </div>

    </div>
    @endforeach
  </div>
  @endif

</div>
@endsection
