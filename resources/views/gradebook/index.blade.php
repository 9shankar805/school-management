@extends('layouts.app')
@section('content')
<div class="flex-1 p-6 lg:p-8 overflow-auto">

    {{-- Header --}}
    <div class="flex flex-wrap justify-between items-start mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Gradebook</h1>
            <p class="text-slate-400 text-sm mt-0.5">Unified marks grid across all exams for a class</p>
        </div>
    </div>

    @include('session-messages')

    {{-- Filter form --}}
    <form method="GET" action="{{ route('gradebook.index') }}"
          class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-6 flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[160px]">
            <label class="text-xs font-medium text-slate-600 block mb-1">Class <span class="text-rose-400">*</span></label>
            <select name="class_id" id="gb_class" required onchange="loadGbSections(this.value)"
                    class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">Select class…</option>
                @foreach($classes as $cls)
                <option value="{{ $cls->id }}" {{ $classId == $cls->id ? 'selected' : '' }}>{{ $cls->class_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1 min-w-[160px]">
            <label class="text-xs font-medium text-slate-600 block mb-1">Section <span class="text-rose-400">*</span></label>
            <select name="section_id" id="gb_section" required
                    class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">Select class first…</option>
                @if($classId)
                    @foreach($classes->firstWhere('id', $classId)?->sections ?? [] as $sec)
                    <option value="{{ $sec->id }}" {{ $sectionId == $sec->id ? 'selected' : '' }}>{{ $sec->section_name }}</option>
                    @endforeach
                @endif
            </select>
        </div>
        <button type="submit"
                class="px-5 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
            <i class="bi bi-table me-1"></i> Load Gradebook
        </button>
    </form>

    @if(isset($students))
    {{-- Summary bar --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-indigo-600">{{ $students->count() }}</p>
            <p class="text-xs text-slate-400 mt-1">Students</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-slate-800">{{ $exams->count() }}</p>
            <p class="text-xs text-slate-400 mt-1">Exams</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-emerald-600">{{ round($studentAverages->avg(), 1) }}</p>
            <p class="text-xs text-slate-400 mt-1">Class Avg</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-amber-600">{{ $studentAverages->filter(fn($a) => $a < 40)->count() }}</p>
            <p class="text-xs text-slate-400 mt-1">Below 40%</p>
        </div>
    </div>

    {{-- Marks grid --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="sticky left-0 bg-slate-50 text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase z-10">Student</th>
                    @foreach($exams as $exam)
                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase text-center" title="{{ $exam->start_date->format('M d, Y') }}">
                        {{ \Str::limit($exam->exam_name, 18) }}
                        <div class="text-[10px] font-normal text-slate-400 mt-0.5">{{ $exam->start_date->format('M d') }}</div>
                    </th>
                    @endforeach
                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase text-center bg-indigo-50">Avg</th>
                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase text-center">Progress</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($students as $student)
                @php
                    $studentMarks = $marks->get($student->id, collect());
                    $avg = $studentAverages->get($student->id, 0);
                    $avgColor = $avg >= 75 ? 'text-emerald-600' : ($avg >= 50 ? 'text-amber-600' : 'text-rose-600');
                @endphp
                <tr class="hover:bg-slate-50 transition">
                    <td class="sticky left-0 bg-white hover:bg-slate-50 px-5 py-3 z-10">
                        <a href="{{ route('student.progress', $student->id) }}"
                           class="flex items-center gap-2 group">
                            <img src="{{ $student->avatar }}" class="w-7 h-7 rounded-full object-cover flex-shrink-0" alt="">
                            <span class="font-medium text-slate-700 group-hover:text-indigo-600 transition">{{ $student->full_name }}</span>
                        </a>
                    </td>
                    @foreach($exams as $exam)
                    @php $mark = $studentMarks->get($exam->id); @endphp
                    <td class="px-4 py-3 text-center">
                        @if($mark)
                        <span class="font-semibold {{ $mark->marks >= 75 ? 'text-emerald-600' : ($mark->marks >= 40 ? 'text-slate-700' : 'text-rose-600') }}">
                            {{ $mark->marks }}
                        </span>
                        @else
                        <span class="text-slate-300">—</span>
                        @endif
                    </td>
                    @endforeach
                    <td class="px-4 py-3 text-center bg-indigo-50/40 font-bold {{ $avgColor }}">{{ $avg }}</td>
                    <td class="px-4 py-3 text-center">
                        <div class="w-20 bg-slate-200 rounded-full h-1.5 mx-auto">
                            <div class="h-1.5 rounded-full {{ $avg >= 75 ? 'bg-emerald-500' : ($avg >= 50 ? 'bg-amber-500' : 'bg-rose-500') }}"
                                 style="width:{{ min($avg, 100) }}%"></div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-14 text-center">
        <i class="bi bi-table text-5xl text-slate-200 block mb-3"></i>
        <p class="text-slate-500 font-medium">Select a class and section to load the gradebook.</p>
    </div>
    @endif

</div>
</div>

<script>
function loadGbSections(classId) {
    const sel = document.getElementById('gb_section');
    sel.innerHTML = '<option value="">Loading…</option>';
    if (!classId) { sel.innerHTML = '<option value="">Select class first…</option>'; return; }
    fetch(`/sections?class_id=${classId}`)
        .then(r => r.json())
        .then(data => {
            sel.innerHTML = '<option value="">Select section…</option>';
            (data.sections || []).forEach(s => {
                sel.innerHTML += `<option value="${s.id}">${s.section_name}</option>`;
            });
        });
}
</script>
@endsection
