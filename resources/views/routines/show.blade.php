@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <nav class="text-xs text-slate-400 mb-4">
            <a href="{{ route('routine.index') }}" class="hover:text-indigo-600">Timetable</a>
            <span class="mx-1">/</span>
            <span class="text-slate-600">Class Timetable</span>
        </nav>

        <div class="flex flex-wrap justify-between items-start mb-6 gap-4">
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Class Timetable</h1>
            @can('create routines')
            <a href="{{ route('section.routine.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                <i class="bi bi-plus-lg"></i> Add Slot
            </a>
            @endcan
        </div>

        @include('session-messages')

        {{-- Filter form --}}
        <form method="GET" action="{{ route('section.routine.show') }}" class="flex flex-wrap gap-2 mb-6">
            <select name="class_id" id="class_select" required onchange="loadSections(this.value, {{ $section_id ?? 0 }})"
                    class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">Select Class…</option>
                @foreach($classes as $cls)
                <option value="{{ $cls->id }}" {{ ($class_id ?? 0) == $cls->id ? 'selected' : '' }}>{{ $cls->class_name }}</option>
                @endforeach
            </select>
            <select name="section_id" id="section_select"
                    class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">Select Section…</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                <i class="bi bi-search me-1"></i> View
            </button>
        </form>

        @php
            $dayNames = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];
        @endphp

        @if($routines->isNotEmpty())
        <div class="space-y-5">
            @foreach($dayNames as $dayNum => $dayName)
            @if(isset($routines[$dayNum]))
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 bg-indigo-50 border-b border-indigo-100 flex items-center gap-2">
                    <i class="bi bi-calendar-day text-indigo-500"></i>
                    <h3 class="font-semibold text-indigo-700 text-sm">{{ $dayName }}</h3>
                    <span class="ml-auto text-[11px] text-indigo-400">{{ $routines[$dayNum]->count() }} slot(s)</span>
                </div>
                <div class="p-4 flex flex-wrap gap-3">
                    @foreach($routines[$dayNum]->sortBy('start') as $slot)
                    <div class="relative group border rounded-xl p-3 min-w-[140px] flex-1"
                         style="border-color: {{ $slot->color ?? '#6366f1' }}22; background: {{ $slot->color ?? '#6366f1' }}11">
                        <div class="flex justify-between items-start gap-2 mb-1">
                            <span class="text-xs font-bold" style="color: {{ $slot->color ?? '#6366f1' }}">
                                {{ $slot->start }} – {{ $slot->end }}
                            </span>
                            @can('create routines')
                            <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition">
                                <a href="{{ route('routine.edit', $slot->id) }}"
                                   class="p-0.5 text-slate-400 hover:text-amber-600 transition" title="Edit">
                                    <i class="bi bi-pencil text-xs"></i>
                                </a>
                                <form action="{{ route('routine.destroy', $slot->id) }}" method="POST" onsubmit="return confirm('Remove slot?')">
                                    @csrf @method('DELETE')
                                    <button class="p-0.5 text-slate-400 hover:text-rose-600 transition" title="Remove">
                                        <i class="bi bi-x text-xs"></i>
                                    </button>
                                </form>
                            </div>
                            @endcan
                        </div>
                        <p class="text-sm font-semibold text-slate-800 leading-snug">{{ $slot->course->course_name ?? '—' }}</p>
                        @if($slot->teacher)
                        <p class="text-[11px] text-slate-500 mt-0.5"><i class="bi bi-person me-0.5"></i>{{ $slot->teacher->full_name }}</p>
                        @endif
                        @if($slot->room)
                        <p class="text-[11px] text-slate-400 mt-0.5"><i class="bi bi-door-open me-0.5"></i>{{ $slot->room }}</p>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            @endforeach
        </div>
        @elseif(isset($class_id) && $class_id)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-12 text-center">
            <i class="bi bi-calendar-x text-5xl text-slate-200"></i>
            <p class="mt-3 text-slate-400">No timetable slots for this class &amp; section yet.</p>
            @can('create routines')
            <a href="{{ route('section.routine.create') }}" class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">
                <i class="bi bi-plus-lg"></i> Add First Slot
            </a>
            @endcan
        </div>
        @else
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-12 text-center">
            <i class="bi bi-calendar4-range text-5xl text-slate-200"></i>
            <p class="mt-3 text-slate-400">Select a class and section above to view the timetable.</p>
        </div>
        @endif
    </div>
</div>

<script>
function loadSections(classId, preselect) {
    if (!classId) return;
    fetch(`/sections?class_id=${classId}`)
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('section_select');
            sel.innerHTML = '<option value="">Select Section…</option>';
            (data.sections || []).forEach(s => {
                const opt = new Option(s.section_name, s.id);
                if (preselect && s.id == preselect) opt.selected = true;
                sel.add(opt);
            });
        });
}

// Auto-load sections if class_id is already set
const classId = {{ $class_id ?? 0 }};
if (classId) loadSections(classId, {{ $section_id ?? 0 }});
</script>
@endsection
