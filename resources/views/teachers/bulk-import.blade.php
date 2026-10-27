@extends('layouts.app')
@section('title', 'Bulk Teacher Import')

@section('breadcrumb')
<x-breadcrumb :items="[
    ['label' => 'Home',     'url' => route('home'),            'icon' => 'bi-house'],
    ['label' => 'Teachers', 'url' => route('teachers.index'),  'icon' => 'bi-person-badge'],
    ['label' => 'Bulk Import'],
]"/>
@endsection

@section('content')
<div class="px-4 lg:px-8 py-6 max-w-7xl mx-auto space-y-6">

    {{-- ── Page Header ─────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-2xl bg-emerald-600/10 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xl shadow-xs">
                    <i class="bi bi-file-earmark-person-fill"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Bulk Teacher Import</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Quickly register multiple teachers, assign subjects, class-teacher roles and timetable periods from a single CSV</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('teachers.import.sample') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 text-xs font-semibold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition shadow-xs">
                <i class="bi bi-download text-emerald-600 dark:text-emerald-400"></i>
                Download Sample CSV
            </a>
            <a href="{{ route('teachers.index') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-semibold rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                <i class="bi bi-arrow-left"></i> Back to Teachers
            </a>
        </div>
    </div>

    {{-- ── Session Messages & Import Feedback ─────────────────── --}}
    @include('session-messages')

    @if(session('import_errors') && count(session('import_errors')) > 0)
    <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60">
        <div class="flex items-center gap-2 text-amber-800 dark:text-amber-200 font-semibold text-xs mb-2">
            <i class="bi bi-exclamation-triangle-fill text-amber-600"></i>
            <span>Import Warnings / Skipped Rows ({{ count(session('import_errors')) }}):</span>
        </div>
        <ul class="max-h-48 overflow-y-auto space-y-1 text-xs text-amber-700 dark:text-amber-300 list-disc ps-5">
            @foreach(session('import_errors') as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- ── Tabs: CSV Upload vs Direct Multi-Row Entry ─────────── --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">

        <div class="border-b border-slate-100 dark:border-slate-800 px-6 pt-5 pb-0">
            <div class="flex space-x-6 text-sm font-semibold" id="importTabs">
                <button type="button" onclick="switchTab('csv-tab')" id="csv-tab-btn"
                        class="pb-3 border-b-2 border-emerald-600 text-emerald-600 dark:text-emerald-400 flex items-center gap-2 transition-all">
                    <i class="bi bi-cloud-arrow-up text-base"></i>
                    Upload CSV File
                </button>
                <button type="button" onclick="switchTab('direct-tab')" id="direct-tab-btn"
                        class="pb-3 border-b-2 border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 flex items-center gap-2 transition-all">
                    <i class="bi bi-table text-base"></i>
                    Direct Multi-Row Entry
                </button>
            </div>
        </div>

        <div class="p-6">

            {{-- ──────────────── TAB 1: CSV FILE UPLOAD ──────────────── --}}
            <div id="csv-tab-content">
                <form action="{{ route('teachers.import.process') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    {{-- Session & Password row --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 p-5 rounded-2xl bg-slate-50/60 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Academic Session <span class="text-rose-500">*</span>
                            </label>
                            <select name="session_id"
                                    class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition outline-none">
                                @foreach($sessions as $session)
                                    <option value="{{ $session->id }}" {{ $session->id == $current_school_session_id ? 'selected' : '' }}>
                                        {{ $session->school_year }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="text-[11px] text-slate-400 mt-1 block">Used to link class-teacher assignments and timetable slots.</span>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Default Password <span class="text-slate-400 font-normal">(Min 6 chars)</span>
                            </label>
                            <input type="text" name="default_password" value="Teacher@123" required
                                   class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition outline-none">
                            <span class="text-[11px] text-slate-400 mt-1 block">Teachers will be prompted to change password on first login.</span>
                        </div>
                    </div>

                    {{-- Drag & Drop CSV Box --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            Select CSV File <span class="text-rose-500">*</span>
                        </label>

                        <div id="dropzone"
                             class="relative border-2 border-dashed border-slate-200 dark:border-slate-700 hover:border-emerald-500 dark:hover:border-emerald-400 rounded-3xl p-8 flex flex-col items-center justify-center text-center cursor-pointer transition-all bg-slate-50/50 dark:bg-slate-800/30 group">

                            <input type="file" name="csv_file" id="csv_file" accept=".csv,text/csv,text/plain" required
                                   onchange="handleFileSelect(this)"
                                   class="absolute inset-0 opacity-0 cursor-pointer w-full h-full z-10">

                            <div class="w-14 h-14 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform shadow-xs">
                                <i class="bi bi-file-earmark-arrow-up text-3xl"></i>
                            </div>

                            <p class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-1" id="file_label">
                                Drag & drop your CSV file here, or <span class="text-emerald-600 dark:text-emerald-400 underline">browse</span>
                            </p>
                            <p class="text-xs text-slate-400" id="file_sublabel">
                                Standard comma-separated (.csv) file up to 5 MB
                            </p>
                        </div>
                    </div>

                    {{-- Submit Action Bar --}}
                    <div class="flex items-center justify-between pt-2">
                        <a href="{{ route('teachers.import.sample') }}" class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                            <i class="bi bi-download"></i> Need a format example? Download Sample CSV
                        </a>
                        <button type="submit" id="btnUploadSubmit"
                                class="px-7 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-lg shadow-emerald-600/25 hover:shadow-emerald-600/35 transition-all flex items-center gap-2 cursor-pointer">
                            <i class="bi bi-upload text-sm"></i> Upload & Import Teachers
                        </button>
                    </div>
                </form>
            </div>

            {{-- ──────────────── TAB 2: DIRECT MULTI-ROW ENTRY ──────── --}}
            <div id="direct-tab-content" class="hidden">
                <form action="{{ route('teachers.import.direct') }}" method="POST" id="directEntryForm" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 p-5 rounded-2xl bg-slate-50/60 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Academic Session <span class="text-rose-500">*</span>
                            </label>
                            <select name="session_id"
                                    class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition outline-none">
                                @foreach($sessions as $session)
                                    <option value="{{ $session->id }}" {{ $session->id == $current_school_session_id ? 'selected' : '' }}>
                                        {{ $session->school_year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Default Password <span class="text-slate-400 font-normal">(Min 6 chars)</span>
                            </label>
                            <input type="text" name="default_password" value="Teacher@123" required
                                   class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition outline-none">
                        </div>
                    </div>

                    {{-- Dynamic Multi-Row Table --}}
                    <div class="overflow-x-auto rounded-2xl border border-slate-200/80 dark:border-slate-700">
                        <table class="w-full text-xs text-left border-collapse" id="teachersTable">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 border-b border-slate-200 dark:border-slate-700">
                                    <th class="p-3 font-semibold text-center w-12">#</th>
                                    <th class="p-3 font-semibold min-w-[130px]">First Name <span class="text-rose-500">*</span></th>
                                    <th class="p-3 font-semibold min-w-[130px]">Last Name <span class="text-rose-500">*</span></th>
                                    <th class="p-3 font-semibold min-w-[100px]">Gender</th>
                                    <th class="p-3 font-semibold min-w-[160px]">Email <span class="text-slate-400 font-normal">(Optional)</span></th>
                                    <th class="p-3 font-semibold min-w-[120px]">Phone</th>
                                    <th class="p-3 font-semibold min-w-[110px]">Department</th>
                                    <th class="p-3 font-semibold min-w-[110px]">Joining Date</th>
                                    <th class="p-3 font-semibold min-w-[110px]">Basic Salary</th>
                                    <th class="p-3 font-semibold text-center w-12">Action</th>
                                </tr>
                            </thead>
                            <tbody id="teacherRowsBody" class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                                {{-- Rows injected via JS --}}
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <button type="button" onclick="addNewRow()"
                                class="px-4 py-2 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 rounded-xl text-xs font-bold hover:bg-emerald-100 transition flex items-center gap-1.5 cursor-pointer">
                            <i class="bi bi-plus-lg"></i> Add Another Row
                        </button>

                        <button type="submit"
                                class="px-7 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-lg shadow-emerald-600/25 hover:shadow-emerald-600/35 transition-all flex items-center gap-2 cursor-pointer">
                            <i class="bi bi-person-check text-sm"></i> Save All Teachers
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    {{-- ── CSV Column Guide ─────────────────────────────────────── --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
        <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
            <span class="w-2 h-5 bg-emerald-600 rounded-full"></span>
            <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <i class="bi bi-info-circle text-emerald-500"></i> Supported CSV Columns & Guidelines
            </h2>
        </div>

        <p class="text-xs text-slate-500 dark:text-slate-400 mb-4 leading-relaxed">
            Your CSV file header row can include the columns listed below. <strong>first_name</strong> and <strong>last_name</strong> are the only required columns — everything else defaults sensibly if omitted.
            The <code class="bg-slate-100 dark:bg-slate-800 px-1 rounded">period_N_*</code> columns let you define a teacher's timetable (up to 10 periods) directly in the same row.
        </p>

        {{-- Personal fields --}}
        <h3 class="text-xs font-bold text-slate-700 dark:text-slate-200 mb-2 mt-4">Personal & Payroll Fields</h3>
        <div class="overflow-x-auto rounded-xl border border-slate-100 dark:border-slate-800 mb-5">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300">
                    <tr>
                        <th class="px-3.5 py-2.5 font-semibold">Column Header</th>
                        <th class="px-3.5 py-2.5 font-semibold">Required</th>
                        <th class="px-3.5 py-2.5 font-semibold">Example / Accepted Values</th>
                        <th class="px-3.5 py-2.5 font-semibold">Default if Empty</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-600 dark:text-slate-300">
                    @foreach([
                        ['first_name',        'Required', 'Ramesh',                          '—'],
                        ['last_name',         'Required', 'Poudel',                          '—'],
                        ['gender',            'Optional', 'Male / Female / Other',           'Other'],
                        ['email',             'Optional', 'ramesh@school.edu',               'Auto-generated (e.g. ramesh.poudel.342@staff.local)'],
                        ['phone',             'Optional', '9841100001',                      'N/A'],
                        ['birthday',          'Optional', '1985-07-12 (YYYY-MM-DD)',         'Null'],
                        ['blood_type',        'Optional', 'O+, A-, B+, AB+',                'Null'],
                        ['religion',          'Optional', 'Hindu',                           'Null'],
                        ['nationality',       'Optional', 'Nepali',                          'Nepali'],
                        ['address',           'Optional', 'Baneshwor-10',                   'N/A'],
                        ['city',              'Optional', 'Kathmandu',                       'N/A'],
                        ['zip',               'Optional', '44600',                           '00000'],
                        ['pan_no',            'Optional', 'PAN123456',                      'Null'],
                        ['bank_name',         'Optional', 'Nepal Bank',                     'Null'],
                        ['bank_account_no',   'Optional', '0201234567890',                  'Null'],
                        ['basic_salary',      'Optional', '45000.00',                       'Null'],
                        ['joining_date',      'Optional', '2018-06-01 (YYYY-MM-DD)',        'Null'],
                        ['department',        'Optional', 'Science',                        'Null'],
                    ] as $col)
                    <tr>
                        <td class="px-3.5 py-2 font-mono text-emerald-600 dark:text-emerald-400 font-semibold">{{ $col[0] }}</td>
                        <td class="px-3.5 py-2">
                            @if($col[1] === 'Required')
                                <span class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-700 text-[10px] font-bold">Required</span>
                            @else
                                <span class="text-slate-400">Optional</span>
                            @endif
                        </td>
                        <td class="px-3.5 py-2">{{ $col[2] }}</td>
                        <td class="px-3.5 py-2 text-slate-400">{{ $col[3] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Class teacher & subjects --}}
        <h3 class="text-xs font-bold text-slate-700 dark:text-slate-200 mb-2 mt-4">Class Teacher & Subject Assignment Fields</h3>
        <div class="overflow-x-auto rounded-xl border border-slate-100 dark:border-slate-800 mb-5">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300">
                    <tr>
                        <th class="px-3.5 py-2.5 font-semibold">Column Header</th>
                        <th class="px-3.5 py-2.5 font-semibold">Example</th>
                        <th class="px-3.5 py-2.5 font-semibold">Description</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-600 dark:text-slate-300">
                    <tr>
                        <td class="px-3.5 py-2 font-mono text-emerald-600 dark:text-emerald-400 font-semibold">class_teacher_class</td>
                        <td class="px-3.5 py-2">Class 8</td>
                        <td class="px-3.5 py-2">Exact class name in the system. Marks this teacher as Class Teacher for that class.</td>
                    </tr>
                    <tr>
                        <td class="px-3.5 py-2 font-mono text-emerald-600 dark:text-emerald-400 font-semibold">class_teacher_section</td>
                        <td class="px-3.5 py-2">A</td>
                        <td class="px-3.5 py-2">Exact section name (paired with class_teacher_class).</td>
                    </tr>
                    <tr>
                        <td class="px-3.5 py-2 font-mono text-emerald-600 dark:text-emerald-400 font-semibold">subject_assignments</td>
                        <td class="px-3.5 py-2">Class 8|A|Mathematics,Class 9|B|Science</td>
                        <td class="px-3.5 py-2">Comma-separated list of <code>class|section|course</code> triplets. Names must match exactly.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Timetable / periods --}}
        <h3 class="text-xs font-bold text-slate-700 dark:text-slate-200 mb-2 mt-4">Timetable / Period Slot Columns (period_N_*)</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 mb-3 leading-relaxed">
            Add up to <strong>10</strong> periods per teacher row using the <code class="bg-slate-100 dark:bg-slate-800 px-1 rounded">period_1_*</code> … <code class="bg-slate-100 dark:bg-slate-800 px-1 rounded">period_10_*</code> column groups.
            Each group defines one timetable slot. Leave the group empty to skip that period number.
        </p>
        <div class="overflow-x-auto rounded-xl border border-slate-100 dark:border-slate-800 mb-3">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300">
                    <tr>
                        <th class="px-3.5 py-2.5 font-semibold">Column</th>
                        <th class="px-3.5 py-2.5 font-semibold">Example</th>
                        <th class="px-3.5 py-2.5 font-semibold">Description</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-600 dark:text-slate-300">
                    @foreach([
                        ['period_N_weekday', '1',          '1 = Monday, 2 = Tuesday … 7 = Sunday'],
                        ['period_N_class',   'Class 8',    'Exact class name in the system'],
                        ['period_N_section', 'A',          'Exact section name'],
                        ['period_N_course',  'Mathematics','Exact course/subject name'],
                        ['period_N_start',   '08:00',      '24-hour time HH:MM'],
                        ['period_N_end',     '08:45',      '24-hour time HH:MM'],
                        ['period_N_room',    'Room 101',   'Optional room label'],
                    ] as $col)
                    <tr>
                        <td class="px-3.5 py-2 font-mono text-emerald-600 dark:text-emerald-400 font-semibold">{{ $col[0] }}</td>
                        <td class="px-3.5 py-2">{{ $col[1] }}</td>
                        <td class="px-3.5 py-2">{{ $col[2] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
            <strong class="text-slate-700 dark:text-slate-200">Tips:</strong>
            <ul class="list-disc ps-5 mt-1.5 space-y-1">
                <li>Class, section and course names are matched case-insensitively.</li>
                <li>Conflicting timetable slots (same section or same teacher already booked at that time) are silently skipped — the teacher account is still created.</li>
                <li>If <code class="bg-slate-100 dark:bg-slate-800 px-1 rounded">email</code> is left blank a unique <em>@staff.local</em> address is generated automatically.</li>
                <li>Only one Class Teacher per section is allowed — if another teacher already holds the role it is removed from them first.</li>
                <li><strong>Period numbering</strong> is purely for organization within the CSV. The actual "1st period / 2nd period" order shown on the timetable grid is determined by the <em>start time</em>, not by the column number.</li>
            </ul>
        </div>
    </div>

</div>

<script>
    // ── Tab Switching ────────────────────────────────────────────────────────
    function switchTab(tabId) {
        const csvBtn    = document.getElementById('csv-tab-btn');
        const dirBtn    = document.getElementById('direct-tab-btn');
        const csvContent = document.getElementById('csv-tab-content');
        const dirContent = document.getElementById('direct-tab-content');

        const activeClass   = 'pb-3 border-b-2 border-emerald-600 text-emerald-600 dark:text-emerald-400 flex items-center gap-2 transition-all';
        const inactiveClass = 'pb-3 border-b-2 border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 flex items-center gap-2 transition-all';

        if (tabId === 'csv-tab') {
            csvBtn.className = activeClass;
            dirBtn.className = inactiveClass;
            csvContent.classList.remove('hidden');
            dirContent.classList.add('hidden');
        } else {
            dirBtn.className  = activeClass;
            csvBtn.className  = inactiveClass;
            dirContent.classList.remove('hidden');
            csvContent.classList.add('hidden');

            if (document.querySelectorAll('#teacherRowsBody tr').length === 0) {
                for (let i = 0; i < 3; i++) addNewRow();
            }
        }
    }

    // ── File Selector Feedback ────────────────────────────────────────────────
    function handleFileSelect(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            document.getElementById('file_label').innerHTML =
                `<span class="text-emerald-600 dark:text-emerald-400 font-bold"><i class="bi bi-check-circle me-1"></i>${file.name}</span>`;
            document.getElementById('file_sublabel').innerText =
                `Size: ${(file.size / 1024).toFixed(1)} KB — Ready to import`;
        }
    }

    // ── Dynamic Row Builder ──────────────────────────────────────────────────
    let rowIndex = 0;

    function addNewRow() {
        const tbody = document.getElementById('teacherRowsBody');
        const tr    = document.createElement('tr');
        tr.id       = `row_${rowIndex}`;
        tr.className = 'hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition';

        const inp = (name, placeholder, type = 'text', extra = '') =>
            `<input type="${type}" name="teachers[${rowIndex}][${name}]" placeholder="${placeholder}" ${extra}
                    class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:border-emerald-500">`;

        tr.innerHTML = `
            <td class="p-2 text-center text-slate-400 font-mono row-number">${rowIndex + 1}</td>
            <td class="p-2">${inp('first_name', 'First Name', 'text', 'required')}</td>
            <td class="p-2">${inp('last_name',  'Last Name',  'text', 'required')}</td>
            <td class="p-2">
                <select name="teachers[${rowIndex}][gender]"
                        class="w-full text-xs px-2 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:border-emerald-500">
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </td>
            <td class="p-2">${inp('email',       'Auto if empty', 'email')}</td>
            <td class="p-2">${inp('phone',        '9800000000')}</td>
            <td class="p-2">${inp('department',   'Science')}</td>
            <td class="p-2">${inp('joining_date', 'YYYY-MM-DD', 'date')}</td>
            <td class="p-2">${inp('basic_salary', '0.00', 'number')}</td>
            <td class="p-2 text-center">
                <button type="button" onclick="removeRow('row_${rowIndex}')"
                        class="w-7 h-7 flex items-center justify-center rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-500 hover:bg-rose-100 transition cursor-pointer mx-auto">
                    <i class="bi bi-x-lg text-xs"></i>
                </button>
            </td>
        `;

        tbody.appendChild(tr);
        rowIndex++;
        reNumberRows();
    }

    function removeRow(id) {
        const row = document.getElementById(id);
        if (row) row.remove();
        reNumberRows();
    }

    function reNumberRows() {
        document.querySelectorAll('#teacherRowsBody tr .row-number').forEach((cell, idx) => {
            cell.textContent = idx + 1;
        });
    }
</script>
@endsection
