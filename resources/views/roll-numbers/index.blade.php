@extends('layouts.app')
@section('title', 'Roll Number Management')

@section('breadcrumb')
<x-breadcrumb :items="[
    ['label' => 'Home',     'url' => route('home'), 'icon' => 'bi-house'],
    ['label' => 'Students', 'url' => route('student.list.show')],
    ['label' => 'Roll Numbers'],
]"/>
@endsection

@section('content')
<div class="px-4 lg:px-8 py-6 max-w-7xl mx-auto space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <span class="w-10 h-10 rounded-2xl bg-indigo-600/10 text-indigo-600 flex items-center justify-center font-bold text-xl shadow-xs dark:bg-indigo-500/20 dark:text-indigo-400">
                    <i class="bi bi-card-heading"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Roll Number Management</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Assign, sequence, and update student roll numbers by class and section</p>
                </div>
            </div>
        </div>

        @if($promotions->count())
        <div class="flex items-center gap-2">
            <button type="button" onclick="document.getElementById('autoAssignModal').classList.remove('hidden')" class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white text-xs font-bold shadow-md hover:shadow-indigo-500/25 transition flex items-center gap-2">
                <i class="bi bi-magic text-sm"></i> Auto-Generate Roll Numbers
            </button>
        </div>
        @endif
    </div>

    @include('session-messages')

    {{-- Filter Panel --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
        <form action="{{ route('roll-numbers.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            <div class="md:col-span-5">
                <label for="class_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Select Class <span class="text-rose-500">*</span>
                </label>
                <select name="class_id" id="class_id" onchange="this.form.submit()" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all">
                    <option value="">-- Select Class --</option>
                    @foreach($classes as $cls)
                        <option value="{{ $cls->id }}" {{ $classId == $cls->id ? 'selected' : '' }}>
                            {{ $cls->class_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-5">
                <label for="section_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Select Section <span class="text-rose-500">*</span>
                </label>
                <select name="section_id" id="section_id" onchange="this.form.submit()" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all">
                    <option value="">-- Select Section --</option>
                    @foreach($sections as $sec)
                        <option value="{{ $sec->id }}" {{ $sectionId == $sec->id ? 'selected' : '' }}>
                            Section {{ $sec->section_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2 flex gap-2">
                <button type="submit" class="w-full px-4 py-2.5 rounded-xl bg-slate-800 dark:bg-slate-700 hover:bg-slate-900 text-white text-xs font-bold shadow-xs transition flex items-center justify-center gap-1.5">
                    <i class="bi bi-filter"></i> Filter
                </button>
            </div>
        </form>
    </div>

    {{-- Main Roster Sheet --}}
    @if($selectedClass && $selectedSection)
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
            
            {{-- Header info bar --}}
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-3 py-1 rounded-xl text-xs font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">
                            {{ $selectedClass->class_name }}
                        </span>
                        <span class="px-3 py-1 rounded-xl text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                            Section {{ $selectedSection->section_name }}
                        </span>
                        <span class="px-3 py-1 rounded-xl text-xs font-bold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                            {{ $promotions->count() }} Students
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Assigned: <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ $promotions->whereNotNull('roll_no')->where('roll_no', '!=', '')->count() }}</span> | 
                        Unassigned: <span class="font-semibold text-rose-500 dark:text-rose-400">{{ $promotions->filter(fn($p) => empty($p->roll_no))->count() }}</span>
                    </p>
                </div>

                @if($promotions->count())
                <div class="flex items-center gap-2">
                    <button type="button" onclick="quickFillSequential()" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold transition flex items-center gap-1.5">
                        <i class="bi bi-sort-numeric-down"></i> Quick 1..N Fill
                    </button>
                    <button type="submit" form="bulkRollForm" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/20 transition flex items-center gap-2">
                        <i class="bi bi-check-lg text-sm"></i> Save Roll Numbers
                    </button>
                </div>
                @endif
            </div>

            @if($promotions->count())
            <form action="{{ route('roll-numbers.update-bulk') }}" method="POST" id="bulkRollForm">
                @csrf
                <input type="hidden" name="class_id" value="{{ $classId }}">
                <input type="hidden" name="section_id" value="{{ $sectionId }}">

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-semibold bg-slate-50/50 dark:bg-slate-800/20 uppercase tracking-wider">
                                <th class="py-3 px-4 w-12 text-center">#</th>
                                <th class="py-3 px-4 min-w-[200px]">Roll No. (Editable)</th>
                                <th class="py-3 px-4 min-w-[220px]">Student Name</th>
                                <th class="py-3 px-4">Admission No.</th>
                                <th class="py-3 px-4">ID Card No.</th>
                                <th class="py-3 px-4">Gender</th>
                                <th class="py-3 px-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($promotions as $index => $promo)
                            @php $student = $promo->student; @endphp
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                                <td class="py-3 px-4 text-center text-slate-400 font-mono">
                                    {{ $index + 1 }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="relative max-w-[160px]">
                                        <input type="text" 
                                               name="roll_no[{{ $promo->student_id }}]" 
                                               value="{{ old("roll_no.{$promo->student_id}", $promo->roll_no ?? $student?->roll_no) }}" 
                                               placeholder="e.g. 1 or 01"
                                               class="roll-input w-full text-xs font-mono font-bold px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all">
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2.5">
                                        @if($student?->photo)
                                            <img src="{{ asset('storage/' . ltrim($student->photo, '/')) }}" class="w-8 h-8 rounded-full object-cover border border-slate-200 dark:border-slate-700" alt="">
                                        @else
                                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 font-bold flex items-center justify-center text-xs">
                                                {{ strtoupper(substr($student?->first_name ?? 'S', 0, 1)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <p class="font-bold text-slate-800 dark:text-slate-100 text-xs">
                                                {{ $student?->first_name }} {{ $student?->last_name }}
                                            </p>
                                            <p class="text-[10px] text-slate-400">{{ $student?->email ?? 'No email' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-600 dark:text-slate-300">
                                    {{ $student?->academic_info?->admission_no ?? $student?->admission_no ?? '—' }}
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-500 dark:text-slate-400">
                                    {{ $promo->id_card_number ?? '—' }}
                                </td>
                                <td class="py-3 px-4 capitalize text-slate-600 dark:text-slate-300">
                                    {{ $student?->gender ?? '—' }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    @if($student)
                                    <a href="{{ route('student.profile.show', $student->id) }}" target="_blank" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                                        Profile <i class="bi bi-box-arrow-up-right text-[10px]"></i>
                                    </a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Bottom Action Bar --}}
                <div class="p-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20 flex items-center justify-end gap-3">
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/25 transition flex items-center gap-2">
                        <i class="bi bi-check-lg"></i> Save All Roll Numbers
                    </button>
                </div>
            </form>
            @else
            <div class="py-16 text-center text-slate-400">
                <i class="bi bi-people text-4xl block mb-2 opacity-30"></i>
                <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">No students enrolled in this section yet.</p>
                <p class="text-xs text-slate-400 mt-1">Enroll or promote students to this class & section to assign roll numbers.</p>
            </div>
            @endif
        </div>
    @else
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-12 text-center text-slate-400">
            <i class="bi bi-funnel text-4xl block mb-3 text-indigo-500 opacity-40"></i>
            <h3 class="text-base font-bold text-slate-700 dark:text-slate-200 mb-1">Select Class and Section</h3>
            <p class="text-xs text-slate-400 max-w-sm mx-auto">Choose a class and section from the filter above to view and assign roll numbers.</p>
        </div>
    @endif
</div>

{{-- Auto-Assign Modal --}}
@if($selectedClass && $selectedSection && $promotions->count())
<div id="autoAssignModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs hidden p-4">
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-md w-full p-6 space-y-5">
        
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-indigo-600/10 text-indigo-600 flex items-center justify-center font-bold">
                    <i class="bi bi-magic"></i>
                </span>
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Auto-Generate Roll Numbers</h3>
            </div>
            <button type="button" onclick="document.getElementById('autoAssignModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form action="{{ route('roll-numbers.auto-assign') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="class_id" value="{{ $classId }}">
            <input type="hidden" name="section_id" value="{{ $sectionId }}">

            <div>
                <label for="sort_by" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    Sort Students By <span class="text-rose-500">*</span>
                </label>
                <select name="sort_by" id="sort_by" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none">
                    <option value="name_asc">First Name & Last Name (A to Z)</option>
                    <option value="name_desc">First Name & Last Name (Z to A)</option>
                    <option value="admission_no">Admission Number</option>
                    <option value="id_card_number">ID Card Number</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="start_no" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Start Number <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" name="start_no" id="start_no" value="1" min="1" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none">
                </div>
                <div>
                    <label for="pad_length" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Zero Padding
                    </label>
                    <select name="pad_length" id="pad_length" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none">
                        <option value="0">None (1, 2, 3...)</option>
                        <option value="2">2 Digits (01, 02, 03...)</option>
                        <option value="3">3 Digits (001, 002...)</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="prefix" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    Optional Prefix <span class="text-slate-400 font-normal">(e.g. "R-" or "10-")</span>
                </label>
                <input type="text" name="prefix" id="prefix" placeholder="Prefix (optional)" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none">
            </div>

            <div class="pt-2 flex items-center justify-end gap-2">
                <button type="button" onclick="document.getElementById('autoAssignModal').classList.add('hidden')" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md transition">
                    <i class="bi bi-lightning-fill"></i> Generate & Apply
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
function quickFillSequential() {
    const inputs = document.querySelectorAll('.roll-input');
    inputs.forEach((input, index) => {
        input.value = index + 1;
    });
}
</script>
@endsection
