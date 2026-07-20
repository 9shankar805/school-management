@php
    $__promo = \App\Models\Promotion::where('student_id', $child->id)
        ->with('schoolClass','section')
        ->latest()
        ->first();
@endphp
<div class="mb-6 flex items-center gap-3 flex-wrap">
    <img src="{{ $child->avatar }}" class="w-10 h-10 rounded-full object-cover border-2 border-white shadow" alt="">
    <div>
        <h1 class="text-xl font-bold text-slate-800 tracking-tight">{{ $title }} — {{ $child->full_name }}</h1>
        <p class="text-slate-400 text-xs mt-0.5">
            {{ $__promo?->schoolClass?->name ?? '—' }}
            @if($__promo?->section) · {{ $__promo->section->name }} @endif
        </p>
    </div>
</div>
