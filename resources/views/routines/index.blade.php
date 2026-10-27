@extends('layouts.app')
@section('content')
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700 shadow-sm p-6 mb-7">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-slate-800 dark:bg-slate-700 text-white flex items-center justify-center shadow-sm">
                        <i class="bi bi-grid-3x3-gap-fill text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Time Table Management</h1>
                        <p class="text-xs text-slate-400">Class routines, teacher schedules &amp; period matrix</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('section.routine.show') }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition shadow-xs">
                        <i class="bi bi-lightning-charge-fill text-amber-300"></i> Rapid Matrix Editor
                    </a>
                    <a href="{{ route('section.routine.show') }}?view=grid"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl transition shadow-xs">
                        <i class="bi bi-table"></i> Grid View
                    </a>
                    <a href="{{ route('section.routine.create') }}"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                        <i class="bi bi-plus-lg"></i> Single Slot
                    </a>
                </div>
            </div>

            {{-- Filter by class --}}
            <form method="GET" action="{{ route('section.routine.show') }}" class="flex flex-wrap gap-3 mt-5 pt-4 border-t border-slate-100 dark:border-slate-700/80" id="filterForm">
                <div class="flex-1 min-w-[200px]">
                    <select name="class_id" id="filter_class" required onchange="loadSections(this.value)"
                            class="w-full bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-xl px-3.5 py-2 text-xs font-medium text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select Class…</option>
                        @foreach($classes as $cls)
                        <option value="{{ $cls->id }}">{{ $cls->class_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <select name="section_id" id="filter_section"
                            class="w-full bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-xl px-3.5 py-2 text-xs font-medium text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select Section…</option>
                    </select>
                </div>
                <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-xl text-xs font-semibold hover:bg-indigo-700 transition shadow-xs flex items-center gap-1.5">
                    <i class="bi bi-search"></i> View Timetable
                </button>
            </form>
        </div>

        {{-- Quick stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
                <p class="text-2xl font-bold text-indigo-600">{{ $classes->count() }}</p>
                <p class="text-xs text-slate-400 mt-1">Classes</p>
            </div>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
                <p class="text-2xl font-bold text-emerald-600">{{ \App\Models\Routine::where('session_id', $session_id)->count() }}</p>
                <p class="text-xs text-slate-400 mt-1">Total Slots</p>
            </div>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
                <p class="text-2xl font-bold text-amber-600">{{ \App\Models\Routine::where('session_id', $session_id)->distinct('teacher_id')->count('teacher_id') }}</p>
                <p class="text-xs text-slate-400 mt-1">Teachers Scheduled</p>
            </div>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
                <p class="text-2xl font-bold text-blue-600">{{ \App\Models\Routine::where('session_id', $session_id)->distinct('course_id')->count('course_id') }}</p>
                <p class="text-xs text-slate-400 mt-1">Courses Covered</p>
            </div>
        </div>

        {{-- Per-class summary --}}
        <h2 class="text-base font-semibold text-slate-700 mb-3">Classes at a Glance</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($classes as $cls)
            @php $slotCount = \App\Models\Routine::where('session_id', $session_id)->where('class_id', $cls->id)->count(); @endphp
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="font-semibold text-slate-700 text-sm">{{ $cls->class_name }}</h3>
                    <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full">{{ $slotCount }} slots</span>
                </div>
                @if($cls->sections->count())
                <div class="flex flex-wrap gap-1">
                    @foreach($cls->sections as $sec)
                    <a href="{{ route('section.routine.show', ['class_id' => $cls->id, 'section_id' => $sec->id]) }}"
                       class="text-[11px] bg-slate-100 hover:bg-indigo-100 hover:text-indigo-700 text-slate-600 px-2 py-0.5 rounded-full transition">
                        {{ $sec->section_name }}
                    </a>
                    @endforeach
                </div>
                @else
                <p class="text-xs text-slate-400">No sections</p>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>

<script>
function loadSections(classId) {
    if (!classId) return;
    fetch(`/sections?class_id=${classId}`)
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('filter_section');
            sel.innerHTML = '<option value="">Select Section…</option>';
            (data.sections || []).forEach(s => sel.innerHTML += `<option value="${s.id}">${s.section_name}</option>`);
        });
}
</script>
@endsection
