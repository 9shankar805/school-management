@extends('layouts.app')
@section('title', 'Students')

@section('breadcrumb')
<x-breadcrumb :items="[
    ['label' => 'Home',     'url' => route('home'),             'icon' => 'bi-house'],
    ['label' => 'Students'],
]"/>
@endsection

@section('content')
<div class="page-wrapper">

    {{-- ── Page header ───────────────────────────────────────── --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">Students</h1>
            <p class="page-subtitle">
                Manage enrolled students — filter by class and section
            </p>
        </div>
        <div class="page-actions flex items-center gap-2">
            <button type="button" onclick="openBulkIdModal()" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 text-xs font-bold rounded-xl hover:bg-indigo-100 dark:hover:bg-indigo-900/60 transition shadow-sm cursor-pointer">
                <i class="bi bi-person-vcard-fill text-sm"></i> Bulk ID Cards
            </button>
            @can('create students')
            @unless(session()->has('browse_session_id'))
            <a href="{{ route('students.import.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-xs font-bold rounded-xl hover:bg-emerald-100 dark:hover:bg-emerald-900/60 transition shadow-sm cursor-pointer">
                <i class="bi bi-file-earmark-spreadsheet-fill text-sm"></i> Bulk Import
            </a>
            <a href="{{ route('student.create.show') }}" class="btn-primary">
                <i class="bi bi-person-plus"></i> Add Student
            </a>
            @endunless
            @endcan
        </div>
    </div>

    {{-- ── Session messages ───────────────────────────────────── --}}
    @if(session('status'))
    <div class="alert-modern alert-success mb-5" data-auto-close="5000">
        <i class="bi bi-check-circle-fill flex-shrink-0"></i>
        <span>{{ session('status') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="alert-modern alert-error mb-5" data-auto-close="7000">
        <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif
    @if($errors->any())
    <div class="alert-modern alert-error mb-5">
        <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
        <ul class="mb-0 ps-3 list-disc">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    {{-- ── Filter form ────────────────────────────────────────── --}}
    <div class="card-modern mb-6">
        <div class="card-header-modern">
            <span class="card-title-modern">
                <i class="bi bi-funnel text-indigo-500"></i> Filter Students
            </span>
        </div>
        <div class="card-body-modern">
            <form class="flex flex-wrap gap-3 items-end" action="{{ route('student.list.show') }}" method="GET">
                <div class="form-group-modern flex-1 min-w-40">
                    <label class="label-modern" for="class-select">Class</label>
                    <select id="class-select" onchange="getSections(this)"
                            class="select-modern" name="class_id" required>
                        <option disabled selected>Select a class…</option>
                        @isset($school_classes)
                            @foreach($school_classes as $class)
                            <option value="{{ $class->id }}"
                                {{ $class->id == request('class_id') ? 'selected' : '' }}>
                                {{ $class->class_name }}
                            </option>
                            @endforeach
                        @endisset
                    </select>
                </div>

                <div class="form-group-modern flex-1 min-w-40">
                    <label class="label-modern" for="section-select">Section</label>
                    <select id="section-select" class="select-modern" name="section_id" required>
                        <option value="{{ request('section_id') }}">
                            {{ request('section_name', 'Select a section…') }}
                        </option>
                    </select>
                </div>

                <div class="flex gap-2 pb-0.5">
                    <button type="submit" class="btn-primary">
                        <i class="bi bi-arrow-counterclockwise"></i> Load
                    </button>
                    @if(request('class_id'))
                    <a href="{{ route('student.list.show') }}" class="btn-secondary">
                        <i class="bi bi-x"></i> Clear
                    </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- ── Results ─────────────────────────────────────────────── --}}
    @if($studentList->count())

    {{-- Section label + count --}}
    @php $section = $studentList->first()->section ?? null; @endphp
    <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
        <div class="flex items-center gap-2">
            @if($section)
            <span class="badge-primary">
                <i class="bi bi-diagram-3"></i> {{ $section->section_name }}
            </span>
            @endif
            <span class="text-xs" style="color:var(--text-muted)">
                {{ $studentList->count() }} student{{ $studentList->count() !== 1 ? 's' : '' }}
            </span>
        </div>

        {{-- View mode toggle --}}
        <div class="flex items-center gap-1 border rounded-lg p-0.5" style="border-color:var(--border-card)" data-view-group>
            <button data-view-toggle="table" data-view-target="students-view"
                    class="px-2.5 py-1 rounded-md text-xs font-medium transition view-toggle-btn"
                    data-tooltip="Table view">
                <i class="bi bi-table"></i>
            </button>
            <button data-view-toggle="grid" data-view-target="students-view"
                    class="px-2.5 py-1 rounded-md text-xs font-medium transition view-toggle-btn"
                    data-tooltip="Card view">
                <i class="bi bi-grid-3x3-gap"></i>
            </button>
        </div>
    </div>

    {{-- TABLE VIEW --}}
    <div id="students-view" class="view-table">

        {{-- ─ Table ─────────────────────────────────────────── --}}
        <div class="table-wrapper mb-6 view-table-content">
            <div class="flex items-center gap-2 px-4 py-2.5 border-b" style="border-color:var(--border-card);background:var(--bg-surface)">
                <div class="relative flex-1 max-w-xs">
                    <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:var(--text-muted)"></i>
                    <input type="text"
                           data-table-search="students-table"
                           placeholder="Search in this list…"
                           class="input-modern pl-8 py-1.5 text-xs w-full"
                           autocomplete="off">
                </div>
                @can('create students')
                <a href="{{ route('student.create.show') }}" class="btn-primary text-xs py-1.5">
                    <i class="bi bi-person-plus"></i> Add
                </a>
                @endcan
            </div>

            <div class="table-scroll">
                <table class="table-modern" id="students-table">
                    <thead>
                        <tr>
                            <th style="width:3rem"></th>
                            <th class="sortable">Roll No <span class="sort-icon"></span></th>
                            <th class="sortable">ID Card <span class="sort-icon"></span></th>
                            <th class="sortable">Name <span class="sort-icon"></span></th>
                            <th class="sortable">Email <span class="sort-icon"></span></th>
                            <th class="sortable">Phone <span class="sort-icon"></span></th>
                            <th align="right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($studentList as $student)
                        <tr>
                            <td>
                                @if(isset($student->student->photo) && $student->student->photo)
                                    <img src="{{ asset('storage/' . ltrim($student->student->photo, '/')) }}"
                                         class="w-8 h-8 rounded-lg object-cover border" style="border-color:var(--border-card)" alt="">
                                @else
                                    <div class="avatar avatar-sm">
                                        {{ strtoupper(substr($student->student->first_name ?? '?', 0, 1)) }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="font-mono text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                    {{ $student->roll_no ?? $student->student->roll_no ?? '—' }}
                                </span>
                            </td>
                            <td>
                                <span class="font-mono text-xs badge-neutral">
                                    {{ $student->id_card_number ?? '—' }}
                                </span>
                            </td>
                            <td>
                                <p class="font-medium text-sm" style="color:var(--text-heading)">
                                    {{ $student->student->first_name }} {{ $student->student->last_name }}
                                </p>
                            </td>
                            <td>
                                <span class="text-xs" style="color:var(--text-muted)">
                                    {{ $student->student->email ?? '—' }}
                                </span>
                            </td>
                            <td>
                                <span class="text-xs" style="color:var(--text-muted)">
                                    {{ $student->student->phone ?? '—' }}
                                </span>
                            </td>
                            <td align="right">
                                <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                    <a href="{{ route('student.attendance.show', ['id' => $student->student->id]) }}"
                                       class="btn-icon" data-tooltip="Attendance">
                                        <i class="bi bi-calendar-check text-xs"></i>
                                    </a>
                                    <a href="{{ url('students/view/profile/' . $student->student->id) }}"
                                       class="btn-secondary text-xs py-1 px-2.5">
                                        <i class="bi bi-eye"></i> Profile
                                    </a>
                                    @can('manage roles')
                                    <a href="{{ route('roles.user-roles', $student->student->id) }}"
                                       class="btn-icon" data-tooltip="Assign Roles">
                                        <i class="bi bi-person-check text-xs" style="color:#059669"></i>
                                    </a>
                                    @endcan
                                    @can('edit users')
                                    <a href="{{ route('student.edit.show', ['id' => $student->student->id]) }}"
                                       class="btn-icon" data-tooltip="Edit">
                                        <i class="bi bi-pencil text-xs"></i>
                                    </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ─ Card grid ───────────────────────────────────────── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-6 view-grid-content hidden">
            @foreach($studentList as $student)
            <div class="card-modern p-4 flex flex-col gap-3">
                <div class="flex items-start gap-3">
                    @if(isset($student->student->photo) && $student->student->photo)
                        <img src="{{ asset('storage/' . ltrim($student->student->photo, '/')) }}"
                             class="w-12 h-12 rounded-xl object-cover flex-shrink-0 border" style="border-color:var(--border-card)" alt="">
                    @else
                        <div class="avatar avatar-lg flex-shrink-0">
                            {{ strtoupper(substr($student->student->first_name ?? '?', 0, 1)) }}
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-sm truncate" style="color:var(--text-heading)">
                            {{ $student->student->first_name }} {{ $student->student->last_name }}
                        </p>
                        <p class="text-xs mt-0.5 font-mono flex items-center gap-1.5" style="color:var(--text-muted)">
                            <span class="font-bold text-indigo-600 dark:text-indigo-400">Roll: {{ $student->roll_no ?? $student->student->roll_no ?? '—' }}</span>
                            <span>•</span>
                            <span>ID: {{ $student->id_card_number ?? '—' }}</span>
                        </p>
                    </div>
                </div>

                <div class="space-y-1.5 text-xs" style="color:var(--text-muted)">
                    @if($student->student->email)
                    <div class="flex items-center gap-1.5 truncate">
                        <i class="bi bi-envelope flex-shrink-0"></i>
                        <span class="truncate">{{ $student->student->email }}</span>
                    </div>
                    @endif
                    @if($student->student->phone)
                    <div class="flex items-center gap-1.5">
                        <i class="bi bi-telephone flex-shrink-0"></i>
                        <span>{{ $student->student->phone }}</span>
                    </div>
                    @endif
                </div>

                <div class="flex gap-2 mt-auto pt-2 border-t" style="border-color:var(--border-card)">
                    <a href="{{ url('students/view/profile/' . $student->student->id) }}"
                       class="flex-1 text-center py-1.5 rounded-lg text-xs font-medium transition"
                       style="background:var(--color-primary-light);color:var(--color-primary-dark)">
                        Profile
                    </a>
                    <a href="{{ route('student.id-card', $student->student->id) }}" target="_blank"
                       class="btn-icon flex-shrink-0 text-blue-600" data-tooltip="Print ID Card">
                        <i class="bi bi-person-badge text-xs"></i>
                    </a>
                    <a href="{{ route('student.attendance.show', ['id' => $student->student->id]) }}"
                       class="btn-icon flex-shrink-0" data-tooltip="Attendance">
                        <i class="bi bi-calendar-check text-xs"></i>
                    </a>
                    @can('manage roles')
                    <a href="{{ route('roles.user-roles', $student->student->id) }}"
                       class="btn-icon flex-shrink-0" data-tooltip="Assign Roles">
                        <i class="bi bi-person-check text-xs" style="color:#059669"></i>
                    </a>
                    @endcan
                    @can('edit users')
                    <a href="{{ route('student.edit.show', ['id' => $student->student->id]) }}"
                       class="btn-icon flex-shrink-0" data-tooltip="Edit">
                        <i class="bi bi-pencil text-xs"></i>
                    </a>
                    @endcan
                </div>
            </div>
            @endforeach
        </div>

    </div>{{-- /students-view --}}

    @else

    {{-- Empty state --}}
    @if(request('class_id'))
    <div class="card-modern p-16 text-center">
        <i class="bi bi-people text-4xl mb-3 block" style="color:var(--border-card)"></i>
        <p class="font-medium mb-1" style="color:var(--text-heading)">No students found</p>
        <p class="text-sm" style="color:var(--text-muted)">
            This class/section has no enrolled students yet.
        </p>
        @can('create students')
        <a href="{{ route('student.create.show') }}" class="btn-primary mt-4 inline-flex">
            <i class="bi bi-person-plus"></i> Add First Student
        </a>
        @endcan
    </div>
    @else
    <div class="card-modern p-16 text-center">
        <i class="bi bi-funnel text-4xl mb-3 block" style="color:var(--border-card)"></i>
        <p class="font-medium mb-1" style="color:var(--text-heading)">Select a class to view students</p>
        <p class="text-sm" style="color:var(--text-muted)">
            Use the filter above to choose a class and section.
        </p>
    </div>
    @endif

    @endif

    {{-- ── Bulk ID Card Export Modal ─────────────────────────────── --}}
    <div id="bulkIdModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden transform transition-all">
            
            {{-- Modal Header --}}
            <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 p-6 text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center">
                        <i class="bi bi-person-vcard text-xl text-white"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-lg tracking-tight">Bulk Student ID Cards</h3>
                        <p class="text-xs text-indigo-100">Choose export format & print layout</p>
                    </div>
                </div>
                <button type="button" onclick="closeBulkIdModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/25 flex items-center justify-center text-white transition cursor-pointer">
                    <i class="bi bi-x-lg text-sm"></i>
                </button>
            </div>

            {{-- Modal Body / Form --}}
            <form id="bulkIdCardForm" action="{{ route('student.id-cards.bulk') }}" method="GET" target="_blank" class="p-6 space-y-5">
                
                {{-- Format Selector (PDF vs Word) --}}
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">
                        1. Select Export Format
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="format-card relative flex flex-col items-center p-4 rounded-2xl border-2 border-indigo-600 bg-indigo-50/50 dark:bg-indigo-950/30 cursor-pointer transition hover:border-indigo-600">
                            <input type="radio" name="format" value="pdf" checked class="sr-only" onchange="updateFormatSelection(this)">
                            <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center mb-2">
                                <i class="bi bi-file-earmark-pdf-fill text-xl"></i>
                            </div>
                            <span class="font-bold text-sm text-slate-800 dark:text-slate-100">PDF Document</span>
                            <span class="text-[11px] text-slate-400 text-center mt-0.5">Print-ready A4 grid</span>
                            <div class="active-badge absolute top-2 right-2 w-4 h-4 rounded-full bg-indigo-600 text-white flex items-center justify-center text-[10px]">
                                <i class="bi bi-check"></i>
                            </div>
                        </label>

                        <label class="format-card relative flex flex-col items-center p-4 rounded-2xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 cursor-pointer transition hover:border-indigo-400">
                            <input type="radio" name="format" value="docx" class="sr-only" onchange="updateFormatSelection(this)">
                            <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center mb-2">
                                <i class="bi bi-file-earmark-word-fill text-xl"></i>
                            </div>
                            <span class="font-bold text-sm text-slate-800 dark:text-slate-100">Word (.docx)</span>
                            <span class="text-[11px] text-slate-400 text-center mt-0.5">Editable Microsoft Word</span>
                            <div class="active-badge hidden absolute top-2 right-2 w-4 h-4 rounded-full bg-indigo-600 text-white flex items-center justify-center text-[10px]">
                                <i class="bi bi-check"></i>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Layout Selector --}}
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">
                        2. Choose Card Layout
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="layout-card flex items-center gap-3 p-3 rounded-xl border-2 border-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/20 cursor-pointer transition">
                            <input type="radio" name="layout" value="dualsided" checked class="sr-only" onchange="updateLayoutSelection(this)">
                            <i class="bi bi-front text-indigo-600 text-lg"></i>
                            <div>
                                <p class="text-xs font-bold text-slate-800 dark:text-slate-200">Dual Sided</p>
                                <p class="text-[10px] text-slate-400">Front + Back with QR</p>
                            </div>
                        </label>
                        <label class="layout-card flex items-center gap-3 p-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 cursor-pointer transition">
                            <input type="radio" name="layout" value="front" class="sr-only" onchange="updateLayoutSelection(this)">
                            <i class="bi bi-grid-fill text-slate-500 text-lg"></i>
                            <div>
                                <p class="text-xs font-bold text-slate-800 dark:text-slate-200">Front Only Grid</p>
                                <p class="text-[10px] text-slate-400">8 cards per A4 page</p>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Class & Section Filter --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Target Class</label>
                        <select name="class_id" id="modal_class_select" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">All Classes</option>
                            @isset($school_classes)
                                @foreach($school_classes as $c)
                                <option value="{{ $c->id }}" {{ $c->id == request('class_id') ? 'selected' : '' }}>
                                    {{ $c->class_name }}
                                </option>
                                @endforeach
                            @endisset
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Max Students</label>
                        <select name="limit" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="50">50 Students</option>
                            <option value="100" selected>100 Students</option>
                            <option value="200">200 Students</option>
                        </select>
                    </div>
                </div>

                {{-- Modal Action Buttons --}}
                <div class="flex items-center gap-3 pt-2">
                    <button type="button" onclick="submitBulkForm('docx')" class="flex-1 py-2.5 px-4 bg-blue-50 dark:bg-blue-950/60 hover:bg-blue-100 dark:hover:bg-blue-900/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 rounded-xl font-bold text-xs flex items-center justify-center gap-2 transition cursor-pointer">
                        <i class="bi bi-file-earmark-word text-sm"></i> Download Word
                    </button>
                    <button type="button" onclick="submitBulkForm('pdf')" class="flex-1 py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs flex items-center justify-center gap-2 shadow-lg shadow-indigo-600/30 transition cursor-pointer">
                        <i class="bi bi-file-earmark-pdf text-sm"></i> Generate PDF
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>{{-- /page-wrapper --}}
@endsection

@push('scripts')
<script>
// ── Section loader ────────────────────────────────────────────
function getSections(obj) {
    var class_id = obj.options[obj.selectedIndex].value;
    var url = "{{ route('get.sections.courses.by.classId') }}?class_id=" + class_id;
    var select = document.getElementById('section-select');

    // Show loading
    select.disabled = true;
    select.innerHTML = '<option>Loading…</option>';

    fetch(url)
        .then(r => r.json())
        .then(function(data) {
            select.innerHTML = '';
            select.disabled = false;
            var placeholder = new Option('Select a section…', '', true, false);
            placeholder.disabled = true;
            select.add(placeholder);
            (data.sections || []).forEach(function(s) {
                select.add(new Option(s.section_name, s.id));
            });
        })
        .catch(function() {
            select.disabled = false;
            select.innerHTML = '<option>Failed to load</option>';
            showToast('Could not load sections. Please try again.', 'error');
        });
}

// ── Bulk ID Card Modal Handlers ────────────────────────────────
function openBulkIdModal() {
    var modal = document.getElementById('bulkIdModal');
    if (modal) {
        modal.classList.remove('hidden');
        var pageClass = document.getElementById('class-select');
        var modalClass = document.getElementById('modal_class_select');
        if (pageClass && modalClass && pageClass.value) {
            modalClass.value = pageClass.value;
        }
    }
}

function closeBulkIdModal() {
    var modal = document.getElementById('bulkIdModal');
    if (modal) modal.classList.add('hidden');
}

function updateFormatSelection(radio) {
    document.querySelectorAll('.format-card').forEach(function(card) {
        card.classList.remove('border-indigo-600', 'bg-indigo-50/50', 'dark:bg-indigo-950/30');
        card.classList.add('border-slate-200', 'dark:border-slate-700', 'bg-white', 'dark:bg-slate-800');
        var b = card.querySelector('.active-badge');
        if (b) b.classList.add('hidden');
    });
    var parent = radio.closest('.format-card');
    if (parent) {
        parent.classList.add('border-indigo-600', 'bg-indigo-50/50', 'dark:bg-indigo-950/30');
        parent.classList.remove('border-slate-200', 'dark:border-slate-700', 'bg-white', 'dark:bg-slate-800');
        var b = parent.querySelector('.active-badge');
        if (b) b.classList.remove('hidden');
    }
}

function updateLayoutSelection(radio) {
    document.querySelectorAll('.layout-card').forEach(function(card) {
        card.classList.remove('border-indigo-600', 'bg-indigo-50/30', 'dark:bg-indigo-950/20');
        card.classList.add('border-slate-200', 'dark:border-slate-700', 'bg-white', 'dark:bg-slate-800');
    });
    var parent = radio.closest('.layout-card');
    if (parent) {
        parent.classList.add('border-indigo-600', 'bg-indigo-50/30', 'dark:bg-indigo-950/20');
        parent.classList.remove('border-slate-200', 'dark:border-slate-700', 'bg-white', 'dark:bg-slate-800');
    }
}

function submitBulkForm(format) {
    var form = document.getElementById('bulkIdCardForm');
    if (form) {
        var radio = form.querySelector('input[name="format"][value="' + format + '"]');
        if (radio) {
            radio.checked = true;
            updateFormatSelection(radio);
        }
        form.submit();
        setTimeout(closeBulkIdModal, 600);
    }
}

// ── View toggle (table ↔ card) ────────────────────────────────
(function() {
    var STORAGE_KEY = 'erp_students_view';
    var saved = localStorage.getItem(STORAGE_KEY) || 'table';
    var tableContent = document.querySelector('.view-table-content');
    var gridContent  = document.querySelector('.view-grid-content');
    var btns         = document.querySelectorAll('.view-toggle-btn');

    function setView(v) {
        localStorage.setItem(STORAGE_KEY, v);
        if (tableContent) tableContent.classList.toggle('hidden', v === 'grid');
        if (gridContent)  gridContent.classList.toggle('hidden', v === 'table');
        btns.forEach(function(btn) {
            var isActive = btn.dataset.viewToggle === v;
            btn.style.background = isActive ? 'var(--color-primary)' : 'transparent';
            btn.style.color      = isActive ? '#fff' : 'var(--text-muted)';
        });
    }

    btns.forEach(function(btn) {
        btn.addEventListener('click', function() { setView(btn.dataset.viewToggle); });
    });

    setView(saved);
})();
</script>
@endpush
