@extends('layouts.app')
@section('title', 'Bulk Student ID Cards')

@section('breadcrumb')
<x-breadcrumb :items="[
    ['label' => 'Home',     'url' => route('home'), 'icon' => 'bi-house'],
    ['label' => 'Students', 'url' => route('student.list.show')],
    ['label' => 'Bulk ID Cards'],
]"/>
@endsection

@section('content')
<div class="page-wrapper max-w-5xl mx-auto py-6">

    {{-- Page Header --}}
    <div class="page-header mb-6">
        <div>
            <h1 class="page-title text-2xl font-extrabold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <i class="bi bi-person-vcard-fill text-indigo-600"></i> Bulk Student ID Cards Generator
            </h1>
            <p class="page-subtitle text-slate-500 dark:text-slate-400 text-sm mt-1">
                Generate high-resolution printable ID cards in Microsoft Word (.docx) or PDF format with Instant QR Login
            </p>
        </div>
    </div>

    {{-- Options Form Card --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden mb-8">
        
        <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 p-6 text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white">
                    <i class="bi bi-gear-wide-connected text-2xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg">ID Card Configuration &amp; Export Options</h3>
                    <p class="text-xs text-indigo-100">Select class, layout preferences, and document format</p>
                </div>
            </div>
            <div class="text-xs font-semibold bg-white/15 px-3 py-1.5 rounded-full backdrop-blur-sm">
                Session: {{ $schoolSession?->session_name ?? date('Y') }}
            </div>
        </div>

        <form id="bulkGenerateForm" action="{{ route('student.id-cards.bulk') }}" method="GET" target="_blank" class="p-6 sm:p-8 space-y-6">
            
            {{-- 1. Format Choice (Word vs PDF) --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">
                    1. Choose Output Format
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <label class="format-card relative flex items-center gap-4 p-5 rounded-2xl border-2 border-indigo-600 bg-indigo-50/50 dark:bg-indigo-950/30 cursor-pointer transition">
                        <input type="radio" name="format" value="pdf" checked class="sr-only" onchange="switchFormat(this)">
                        <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center flex-shrink-0">
                            <i class="bi bi-file-earmark-pdf-fill text-2xl"></i>
                        </div>
                        <div>
                            <span class="font-extrabold text-sm text-slate-900 dark:text-slate-100 block">PDF Document (.pdf)</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400">High-resolution, print-ready A4 sheets with zero clipping</span>
                        </div>
                        <div class="active-pill absolute top-3 right-3 w-5 h-5 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs">
                            <i class="bi bi-check"></i>
                        </div>
                    </label>

                    <label class="format-card relative flex items-center gap-4 p-5 rounded-2xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 cursor-pointer transition">
                        <input type="radio" name="format" value="docx" class="sr-only" onchange="switchFormat(this)">
                        <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0">
                            <i class="bi bi-file-earmark-word-fill text-2xl"></i>
                        </div>
                        <div>
                            <span class="font-extrabold text-sm text-slate-900 dark:text-slate-100 block">Microsoft Word (.docx)</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400">Fully editable Word tables with photos, student data &amp; QR codes</span>
                        </div>
                        <div class="active-pill hidden absolute top-3 right-3 w-5 h-5 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs">
                            <i class="bi bi-check"></i>
                        </div>
                    </label>
                </div>
            </div>

            {{-- 2. Layout Choice --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">
                    2. Print &amp; Card Layout
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <label class="layout-card flex items-start gap-3 p-4 rounded-2xl border-2 border-indigo-600 bg-indigo-50/40 dark:bg-indigo-950/20 cursor-pointer transition">
                        <input type="radio" name="layout" value="dualsided" checked class="sr-only" onchange="switchLayout(this)">
                        <div class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="bi bi-front text-base"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-800 dark:text-slate-100">Dual Sided (Front &amp; Back Pair)</p>
                            <p class="text-xs text-slate-500">Includes Instant Login QR code &amp; institutional terms on the back card</p>
                        </div>
                    </label>

                    <label class="layout-card flex items-start gap-3 p-4 rounded-2xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 cursor-pointer transition">
                        <input type="radio" name="layout" value="front" class="sr-only" onchange="switchLayout(this)">
                        <div class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="bi bi-grid-fill text-base"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-800 dark:text-slate-100">Front Only Grid (8 Cards / Page)</p>
                            <p class="text-xs text-slate-500">Standard 2×4 compact grid on A4 for high-speed batch cutting</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- 3. Filter Target --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Class Filter</label>
                    <select name="class_id" id="page_class_select" onchange="loadSectionsForPage(this)" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Classes</option>
                        @foreach($classes as $c)
                        <option value="{{ $c->id }}">{{ $c->class_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Section Filter</label>
                    <select name="section_id" id="page_section_select" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Sections</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Quantity Limit</label>
                    <select name="limit" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="50">50 Students</option>
                        <option value="100" selected>100 Students</option>
                        <option value="200">200 Students</option>
                    </select>
                </div>
            </div>

            {{-- 4. Action Buttons --}}
            <div class="flex flex-wrap items-center gap-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="submitDirect('docx')" class="flex-1 py-3 px-6 bg-blue-50 dark:bg-blue-950/60 hover:bg-blue-100 dark:hover:bg-blue-900/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 rounded-2xl font-bold text-sm flex items-center justify-center gap-2 transition shadow-sm cursor-pointer">
                    <i class="bi bi-file-earmark-word text-lg text-blue-600"></i> Download Microsoft Word (.docx)
                </button>
                <button type="button" onclick="submitDirect('pdf')" class="flex-1 py-3 px-6 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-bold text-sm flex items-center justify-center gap-2 shadow-lg shadow-indigo-600/30 transition cursor-pointer">
                    <i class="bi bi-file-earmark-pdf text-lg"></i> Generate &amp; Print PDF
                </button>
            </div>

        </form>

    </div>

</div>
@endsection

@push('scripts')
<script>
function switchFormat(radio) {
    document.querySelectorAll('.format-card').forEach(function(card) {
        card.classList.remove('border-indigo-600', 'bg-indigo-50/50', 'dark:bg-indigo-950/30');
        card.classList.add('border-slate-200', 'dark:border-slate-700', 'bg-white', 'dark:bg-slate-800');
        var p = card.querySelector('.active-pill');
        if (p) p.classList.add('hidden');
    });
    var parent = radio.closest('.format-card');
    if (parent) {
        parent.classList.add('border-indigo-600', 'bg-indigo-50/50', 'dark:bg-indigo-950/30');
        parent.classList.remove('border-slate-200', 'dark:border-slate-700', 'bg-white', 'dark:bg-slate-800');
        var p = parent.querySelector('.active-pill');
        if (p) p.classList.remove('hidden');
    }
}

function switchLayout(radio) {
    document.querySelectorAll('.layout-card').forEach(function(card) {
        card.classList.remove('border-indigo-600', 'bg-indigo-50/40', 'dark:bg-indigo-950/20');
        card.classList.add('border-slate-200', 'dark:border-slate-700', 'bg-white', 'dark:bg-slate-800');
    });
    var parent = radio.closest('.layout-card');
    if (parent) {
        parent.classList.add('border-indigo-600', 'bg-indigo-50/40', 'dark:bg-indigo-950/20');
        parent.classList.remove('border-slate-200', 'dark:border-slate-700', 'bg-white', 'dark:bg-slate-800');
    }
}

function submitDirect(format) {
    var form = document.getElementById('bulkGenerateForm');
    if (form) {
        var radio = form.querySelector('input[name="format"][value="' + format + '"]');
        if (radio) {
            radio.checked = true;
            switchFormat(radio);
        }
        form.submit();
    }
}

function loadSectionsForPage(obj) {
    var class_id = obj.value;
    var select = document.getElementById('page_section_select');
    if (!class_id) {
        select.innerHTML = '<option value="">All Sections</option>';
        return;
    }

    select.innerHTML = '<option>Loading…</option>';
    fetch("{{ route('get.sections.courses.by.classId') }}?class_id=" + class_id)
        .then(r => r.json())
        .then(function(data) {
            select.innerHTML = '<option value="">All Sections</option>';
            (data.sections || []).forEach(function(s) {
                select.add(new Option(s.section_name, s.id));
            });
        })
        .catch(function() {
            select.innerHTML = '<option value="">All Sections</option>';
        });
}
</script>
@endpush
