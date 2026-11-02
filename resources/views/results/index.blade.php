@extends('layouts.app')
@section('title', 'Examination Results')

@section('breadcrumb')
<x-breadcrumb :items="[
    ['label' => 'Home',     'url' => route('home'), 'icon' => 'bi-house'],
    ['label' => 'Examinations'],
    ['label' => 'Results'],
]"/>
@endsection

@section('content')
<div class="flex-1 p-6 lg:p-8 overflow-auto max-w-4xl">
    
    {{-- Header --}}
    <div class="mb-7">
        <h1 class="text-2xl font-extrabold text-slate-800 dark:text-slate-100 tracking-tight flex items-center gap-2">
            <i class="bi bi-clipboard-data-fill text-indigo-600"></i> Examination Results Portal
        </h1>
        <p class="text-slate-500 dark:text-slate-400 text-sm mt-0.5">
            Select class, section and terminal examination to generate sheets, merit lists, or export reports
        </p>
    </div>

    @include('session-messages')

    {{-- Main Filter & Action Card --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden mb-6">
        
        <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 p-6 text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white">
                    <i class="bi bi-funnel-fill text-lg"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base">Select Examination Criteria</h3>
                    <p class="text-xs text-indigo-100">Choose class, section and term to view results</p>
                </div>
            </div>
            <a href="{{ route('results.analytics') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white/15 hover:bg-white/25 rounded-xl text-xs font-semibold text-white transition backdrop-blur-sm">
                <i class="bi bi-graph-up-arrow"></i> Performance Analytics
            </a>
        </div>

        <div class="p-6 sm:p-8 space-y-5">
            
            {{-- Class Selector --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                    Class <span class="text-rose-500">*</span>
                </label>
                <select id="rClassId" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-sm font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500" onchange="loadSections(this.value)">
                    <option value="">— Select class —</option>
                    @foreach($classes as $c)
                    <option value="{{ $c->id }}">{{ $c->class_name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Section Selector --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                    Section <span class="text-rose-500">*</span>
                </label>
                <select id="rSectionId" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-sm font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="0">— Select section (or All) —</option>
                </select>
            </div>

            {{-- Semester / Term Selector --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                    Examination Term <span class="text-rose-500">*</span>
                </label>
                <select id="rSemId" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-sm font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">— Select examination term —</option>
                    @foreach($semesters as $s)
                    <option value="{{ $s->id }}">{{ $s->semester_name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Action Buttons --}}
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Available Result Actions</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    
                    {{-- 1. Class Result Sheet --}}
                    <button type="button" onclick="go('class')" class="btn py-2.5 px-4 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 shadow-sm cursor-pointer" style="background-color: #4f46e5 !important; color: #ffffff !important; border: 1px solid #4338ca !important;">
                        <i class="bi bi-people-fill text-sm"></i> Class Result Sheet
                    </button>

                    {{-- 2. Merit List --}}
                    <button type="button" onclick="go('merit')" class="btn py-2.5 px-4 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 shadow-sm cursor-pointer" style="background-color: #059669 !important; color: #ffffff !important; border: 1px solid #047857 !important;">
                        <i class="bi bi-trophy-fill text-sm"></i> Merit List
                    </button>

                    {{-- 3. PDF Sheet --}}
                    <button type="button" onclick="go('pdf')" class="btn py-2.5 px-4 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 shadow-sm cursor-pointer" style="background-color: #e11d48 !important; color: #ffffff !important; border: 1px solid #be123c !important;">
                        <i class="bi bi-download text-sm"></i> Download PDF
                    </button>

                    {{-- 4. Excel Export --}}
                    <button type="button" onclick="go('excel')" class="btn py-2.5 px-4 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 shadow-sm cursor-pointer" style="background-color: #0d9488 !important; color: #ffffff !important; border: 1px solid #0f766e !important;">
                        <i class="bi bi-file-earmark-excel-fill text-sm"></i> Excel Export
                    </button>

                    {{-- 5. All Individual Report Cards PDF --}}
                    <button type="button" onclick="go('reportcards')" class="btn py-2.5 px-4 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 shadow-sm cursor-pointer sm:col-span-2 lg:col-span-4" style="background-color: #4f46e5 !important; color: #ffffff !important; border: 1px solid #4338ca !important;">
                        <i class="bi bi-file-earmark-person-fill text-base"></i> Generate Class-Wise Individual Report Cards (PDF)
                    </button>

                </div>
            </div>

        </div>
    </div>

    {{-- Student Quick Search --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-6">
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-1 flex items-center gap-2">
                <i class="bi bi-person-search text-indigo-500"></i> Quick Student Search
            </h3>
            <p class="text-xs text-slate-400 mb-4">Search any student by name to jump directly to their individual result</p>
            <div class="flex gap-2">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-3 flex items-center text-slate-400 pointer-events-none">
                        <i class="bi bi-search text-sm"></i>
                    </span>
                    <input
                        type="text"
                        id="quickStudentSearch"
                        placeholder="Type student name…"
                        autocomplete="off"
                        class="w-full pl-9 pr-4 py-2.5 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-400"
                    >
                </div>
                <button type="button" id="quickSearchBtn"
                    class="px-4 py-2.5 text-sm font-bold text-white rounded-xl transition shadow-sm"
                    style="background-color:#4f46e5;">
                    Search
                </button>
            </div>
            {{-- Results dropdown --}}
            <div id="quickSearchResults" class="mt-2 hidden rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-lg max-h-64 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800"></div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function loadSections(classId) {
    var sel = document.getElementById('rSectionId');
    if (!classId) {
        sel.innerHTML = '<option value="0">— All sections —</option>';
        return;
    }
    sel.innerHTML = '<option>Loading sections…</option>';
    fetch(`/sections?class_id=${classId}`)
        .then(r => r.json())
        .then(data => {
            sel.innerHTML = '<option value="0">— All sections —</option>';
            (data.sections || []).forEach(s => {
                sel.insertAdjacentHTML('beforeend', `<option value="${s.id}">${s.section_name}</option>`);
            });
        })
        .catch(function() {
            sel.innerHTML = '<option value="0">— All sections —</option>';
        });
}

function go(type) {
    const c = document.getElementById('rClassId').value;
    const s = document.getElementById('rSectionId').value;
    const sem = document.getElementById('rSemId').value;
    if (!c || !sem) {
        alert('Please select both Class and Examination Term.');
        return;
    }
    const p = `?class_id=${c}&section_id=${s}&semester_id=${sem}`;
    const urls = {
        class:       '{{ route("results.class") }}' + p,
        merit:       '{{ route("results.merit") }}' + p,
        pdf:         '{{ route("results.class.pdf") }}' + p,
        excel:       '{{ route("results.class.excel") }}' + p,
        reportcards: '{{ route("results.class.report-cards") }}' + p + '&stream=1',
    };

    if (type === 'pdf' || type === 'excel') {
        const a = document.createElement('a');
        a.href = urls[type];
        a.setAttribute('data-no-loader', 'true');
        a.style.display = 'none';
        document.body.appendChild(a);
        a.click();
        setTimeout(() => a.remove(), 100);
    } else if (urls[type]) {
        window.open(urls[type], '_blank');
    }
}

// ── Quick Student Search ─────────────────────────────────────────────────────
(function () {
    const input   = document.getElementById('quickStudentSearch');
    const btn     = document.getElementById('quickSearchBtn');
    const box     = document.getElementById('quickSearchResults');
    const semSel  = document.getElementById('rSemId');

    if (!input || !btn || !box) return;

    let debounce;

    function doSearch() {
        const query = input.value.trim();
        if (query.length < 2) { box.innerHTML = ''; box.classList.add('hidden'); return; }

        const semId = semSel.value || '';

        fetch(`/results/students/search?q=${encodeURIComponent(query)}`)
            .then(r => r.json())
            .then(data => {
                const students = data.students || data || [];
                if (!students.length) {
                    box.innerHTML = '<p class="px-4 py-3 text-xs text-slate-400">No students found.</p>';
                    box.classList.remove('hidden');
                    return;
                }
                box.innerHTML = students.map(s => {
                    const url = `/results/student/${s.id}` + (semId ? `?semester_id=${semId}` : '');
                    return `<a href="${url}" class="flex items-center gap-3 px-4 py-2.5 hover:bg-indigo-50 dark:hover:bg-slate-800 transition group">
                        <img src="${s.avatar || '/imgs/default-avatar.png'}" class="w-8 h-8 rounded-full object-cover flex-shrink-0 border border-slate-200" onerror="this.src='/imgs/default-avatar.png'" alt="">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 group-hover:text-indigo-600 truncate">${s.name || s.full_name}</p>
                            <p class="text-xs text-slate-400 truncate">${s.class_name || ''} ${s.section_name ? '· Sec ' + s.section_name : ''}</p>
                        </div>
                        <i class="bi bi-arrow-right-short ml-auto text-slate-300 group-hover:text-indigo-500 text-lg flex-shrink-0"></i>
                    </a>`;
                }).join('');
                box.classList.remove('hidden');
            })
            .catch(() => {
                box.innerHTML = '<p class="px-4 py-3 text-xs text-rose-400">Search failed. Please try again.</p>';
                box.classList.remove('hidden');
            });
    }

    input.addEventListener('input', function () {
        clearTimeout(debounce);
        debounce = setTimeout(doSearch, 300);
    });

    btn.addEventListener('click', doSearch);

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') doSearch();
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function (e) {
        if (!input.contains(e.target) && !box.contains(e.target) && !btn.contains(e.target)) {
            box.classList.add('hidden');
        }
    });
})();
</script>
@endpush
@endsection
