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
            <a href="{{ route('student.id-cards.bulk') }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-50 text-indigo-700 border border-indigo-200 text-xs font-bold rounded-xl hover:bg-indigo-100 transition shadow-sm">
                <i class="bi bi-person-vcard-fill text-sm"></i> Bulk ID Cards
            </a>
            @can('create students')
            @unless(session()->has('browse_session_id'))
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
