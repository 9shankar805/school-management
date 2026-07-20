@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Timetable Overview</h1>
                <p class="text-slate-400 text-sm mt-0.5">View &amp; manage all class timetables</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('section.routine.create') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                    <i class="bi bi-plus-lg"></i> Add Slot
                </a>
            </div>
        </div>

        @include('session-messages')

        {{-- Filter by class --}}
        <form method="GET" action="{{ route('section.routine.show') }}" class="flex flex-wrap gap-2 mb-6" id="filterForm">
            <select name="class_id" id="filter_class" required onchange="loadSections(this.value)"
                    class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">Select Class…</option>
                @foreach($classes as $cls)
                <option value="{{ $cls->id }}">{{ $cls->class_name }}</option>
                @endforeach
            </select>
            <select name="section_id" id="filter_section"
                    class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">Select Section…</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                <i class="bi bi-search me-1"></i> View Timetable
            </button>
        </form>

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
