@php
    $__siblings = auth()->user()->children()->get();
    $__currentRoute = request()->route()->getName();
    // Map route names to their URL parameter pattern
    $__routeMap = [
        'parent.attendance'  => 'parent.attendance',
        'parent.results'     => 'parent.results',
        'parent.fees'        => 'parent.fees',
        'parent.assignments' => 'parent.assignments',
        'parent.leave'       => 'parent.leave',
        'parent.performance' => 'parent.performance',
    ];
    $__targetRoute = $__routeMap[$__currentRoute] ?? 'parent.attendance';
@endphp

@if($__siblings->count() > 1)
<div class="mb-5 bg-white rounded-xl border border-slate-200 shadow-sm p-3">
    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-2">Switch Child</p>
    <div class="flex flex-wrap gap-2">
        @foreach($__siblings as $__sib)
        <a href="{{ route($__targetRoute, $__sib->id) }}"
           class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium border transition
                  {{ $child->id === $__sib->id
                     ? 'bg-indigo-600 text-white border-indigo-600'
                     : 'bg-white text-slate-600 border-slate-200 hover:border-indigo-300 hover:text-indigo-600' }}">
            <img src="{{ $__sib->avatar }}" class="w-5 h-5 rounded-full object-cover" alt="">
            {{ $__sib->first_name }}
        </a>
        @endforeach
    </div>
</div>
@endif
