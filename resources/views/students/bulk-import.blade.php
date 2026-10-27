@extends('layouts.app')
@section('title', 'Bulk Student Import')

@section('breadcrumb')
<x-breadcrumb :items="[
    ['label' => 'Home',     'url' => route('home'),             'icon' => 'bi-house'],
    ['label' => 'Students', 'url' => route('students.index'),   'icon' => 'bi-people'],
    ['label' => 'Bulk Import'],
]"/>
@endsection

@section('content')
<div class="px-4 lg:px-8 py-6 max-w-7xl mx-auto space-y-6">

    {{-- ── Page Header ─────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-2xl bg-indigo-600/10 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xl shadow-xs">
                    <i class="bi bi-file-earmark-spreadsheet-fill"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Bulk Student Import</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Quickly import or batch-register students into target class and section</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('students.import.sample') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 text-xs font-semibold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition shadow-xs">
                <i class="bi bi-download text-indigo-600 dark:text-indigo-400"></i>
                Download Sample CSV
            </a>
            <a href="{{ route('students.index') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-semibold rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                <i class="bi bi-arrow-left"></i> Back to Students
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
                        class="pb-3 border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400 flex items-center gap-2 transition-all">
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
                <form action="{{ route('students.import.process') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    {{-- Target Class & Section Selector --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 p-5 rounded-2xl bg-slate-50/60 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Target Class <span class="text-rose-500">*</span>
                            </label>
                            <select name="class_id" id="csv_class_id" required onchange="loadSections(this, 'csv_section_id')"
                                    class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition outline-none">
                                <option value="" disabled selected>— Select Class —</option>
                                @foreach($school_classes as $class)
                                    <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                        {{ $class->class_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Target Section <span class="text-rose-500">*</span>
                            </label>
                            <select name="section_id" id="csv_section_id" required
                                    class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition outline-none">
                                <option value="" disabled selected>Select class first</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Default Student Password <span class="text-slate-400 font-normal">(Min 6 chars)</span>
                            </label>
                            <input type="text" name="default_password" value="Student@123" required
                                   class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition outline-none">
                            <span class="text-[11px] text-slate-400 mt-1 block">Students will be prompted to change password on first login.</span>
                        </div>

                        <input type="hidden" name="session_id" value="{{ $current_school_session_id }}">
                    </div>

                    {{-- Drag & Drop CSV Box --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            Select CSV File <span class="text-rose-500">*</span>
                        </label>

                        <div id="dropzone"
                             class="relative border-2 border-dashed border-slate-200 dark:border-slate-700 hover:border-indigo-500 dark:hover:border-indigo-400 rounded-3xl p-8 flex flex-col items-center justify-center text-center cursor-pointer transition-all bg-slate-50/50 dark:bg-slate-800/30 group">
                            
                            <input type="file" name="csv_file" id="csv_file" accept=".csv,text/csv,text/plain" required
                                   onchange="handleFileSelect(this)"
                                   class="absolute inset-0 opacity-0 cursor-pointer w-full h-full z-10">

                            <div class="w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform shadow-xs">
                                <i class="bi bi-file-earmark-arrow-up text-3xl"></i>
                            </div>

                            <p class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-1" id="file_label">
                                Drag & drop your CSV file here, or <span class="text-indigo-600 dark:text-indigo-400 underline">browse</span>
                            </p>
                            <p class="text-xs text-slate-400" id="file_sublabel">
                                Standard comma-separated (.csv) file up to 5 MB
                            </p>
                        </div>
                    </div>

                    {{-- Submit Action Bar --}}
                    <div class="flex items-center justify-between pt-2">
                        <a href="{{ route('students.import.sample') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                            <i class="bi bi-download"></i> Need a format example? Download Sample CSV
                        </a>
                        <button type="submit" id="btnUploadSubmit"
                                class="px-7 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-lg shadow-indigo-600/25 hover:shadow-indigo-600/35 transition-all flex items-center gap-2 cursor-pointer">
                            <i class="bi bi-upload text-sm"></i> Upload & Import Students
                        </button>
                    </div>
                </form>
            </div>

            {{-- ──────────────── TAB 2: DIRECT MULTI-ROW ENTRY ──────── --}}
            <div id="direct-tab-content" class="hidden">
                <form action="{{ route('students.import.direct') }}" method="POST" id="directEntryForm" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 p-5 rounded-2xl bg-slate-50/60 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Target Class <span class="text-rose-500">*</span>
                            </label>
                            <select name="class_id" id="direct_class_id" required onchange="loadSections(this, 'direct_section_id')"
                                    class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition outline-none">
                                <option value="" disabled selected>— Select Class —</option>
                                @foreach($school_classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->class_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Target Section <span class="text-rose-500">*</span>
                            </label>
                            <select name="section_id" id="direct_section_id" required
                                    class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition outline-none">
                                <option value="" disabled selected>Select class first</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Default Student Password <span class="text-slate-400 font-normal">(Min 6 chars)</span>
                            </label>
                            <input type="text" name="default_password" value="Student@123" required
                                   class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition outline-none">
                        </div>

                        <input type="hidden" name="session_id" value="{{ $current_school_session_id }}">
                    </div>

                    {{-- Dynamic Multi-Row Table --}}
                    <div class="overflow-x-auto rounded-2xl border border-slate-200/80 dark:border-slate-700">
                        <table class="w-full text-xs text-left border-collapse" id="studentsTable">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 border-b border-slate-200 dark:border-slate-700">
                                    <th class="p-3 font-semibold text-center w-12">#</th>
                                    <th class="p-3 font-semibold min-w-[130px]">First Name <span class="text-rose-500">*</span></th>
                                    <th class="p-3 font-semibold min-w-[130px]">Last Name <span class="text-rose-500">*</span></th>
                                    <th class="p-3 font-semibold min-w-[100px]">Gender <span class="text-rose-500">*</span></th>
                                    <th class="p-3 font-semibold min-w-[90px]">Roll No</th>
                                    <th class="p-3 font-semibold min-w-[150px]">Email <span class="text-slate-400 font-normal">(Optional)</span></th>
                                    <th class="p-3 font-semibold min-w-[120px]">Phone</th>
                                    <th class="p-3 font-semibold min-w-[140px]">Father's Name</th>
                                    <th class="p-3 font-semibold min-w-[120px]">Father's Phone</th>
                                    <th class="p-3 font-semibold min-w-[130px]">Address</th>
                                    <th class="p-3 font-semibold text-center w-12">Action</th>
                                </tr>
                            </thead>
                            <tbody id="studentRowsBody" class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                                {{-- Initial Rows injected via JS --}}
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <button type="button" onclick="addNewRow()"
                                class="px-4 py-2 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 rounded-xl text-xs font-bold hover:bg-indigo-100 transition flex items-center gap-1.5 cursor-pointer">
                            <i class="bi bi-plus-lg"></i> Add Another Row
                        </button>

                        <button type="submit"
                                class="px-7 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-lg shadow-indigo-600/25 hover:shadow-indigo-600/35 transition-all flex items-center gap-2 cursor-pointer">
                            <i class="bi bi-person-check text-sm"></i> Save All Students
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    {{-- ── CSV Guide & Column Specification Card ───────────────── --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
        <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
            <span class="w-2 h-5 bg-indigo-600 rounded-full"></span>
            <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <i class="bi bi-info-circle text-indigo-500"></i> Supported CSV Columns & Guidelines
            </h2>
        </div>

        <p class="text-xs text-slate-500 dark:text-slate-400 mb-4 leading-relaxed">
            Your CSV file header row can contain the following columns. <strong>First Name</strong> and <strong>Last Name</strong> are required; all other fields are optional and will use smart defaults if omitted.
        </p>

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
                    <tr>
                        <td class="px-3.5 py-2 font-mono text-indigo-600 dark:text-indigo-400 font-semibold">first_name</td>
                        <td class="px-3.5 py-2"><span class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-700 text-[10px] font-bold">Required</span></td>
                        <td class="px-3.5 py-2">Aarav</td>
                        <td class="px-3.5 py-2 text-slate-400">—</td>
                    </tr>
                    <tr>
                        <td class="px-3.5 py-2 font-mono text-indigo-600 dark:text-indigo-400 font-semibold">last_name</td>
                        <td class="px-3.5 py-2"><span class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-700 text-[10px] font-bold">Required</span></td>
                        <td class="px-3.5 py-2">Sharma</td>
                        <td class="px-3.5 py-2 text-slate-400">—</td>
                    </tr>
                    <tr>
                        <td class="px-3.5 py-2 font-mono text-indigo-600 dark:text-indigo-400 font-semibold">gender</td>
                        <td class="px-3.5 py-2 text-slate-400">Optional</td>
                        <td class="px-3.5 py-2">Male, Female, Other</td>
                        <td class="px-3.5 py-2 text-slate-400">Other</td>
                    </tr>
                    <tr>
                        <td class="px-3.5 py-2 font-mono text-indigo-600 dark:text-indigo-400 font-semibold">email</td>
                        <td class="px-3.5 py-2 text-slate-400">Optional</td>
                        <td class="px-3.5 py-2">aarav@school.edu</td>
                        <td class="px-3.5 py-2 text-slate-400">Auto-generated (e.g. aarav.sharma.101@student.local)</td>
                    </tr>
                    <tr>
                        <td class="px-3.5 py-2 font-mono text-indigo-600 dark:text-indigo-400 font-semibold">phone</td>
                        <td class="px-3.5 py-2 text-slate-400">Optional</td>
                        <td class="px-3.5 py-2">9801122334</td>
                        <td class="px-3.5 py-2 text-slate-400">N/A</td>
                    </tr>
                    <tr>
                        <td class="px-3.5 py-2 font-mono text-indigo-600 dark:text-indigo-400 font-semibold">roll_no</td>
                        <td class="px-3.5 py-2 text-slate-400">Optional</td>
                        <td class="px-3.5 py-2">101</td>
                        <td class="px-3.5 py-2 text-slate-400">Null</td>
                    </tr>
                    <tr>
                        <td class="px-3.5 py-2 font-mono text-indigo-600 dark:text-indigo-400 font-semibold">birthday</td>
                        <td class="px-3.5 py-2 text-slate-400">Optional</td>
                        <td class="px-3.5 py-2">2012-05-15 (YYYY-MM-DD)</td>
                        <td class="px-3.5 py-2 text-slate-400">Null</td>
                    </tr>
                    <tr>
                        <td class="px-3.5 py-2 font-mono text-indigo-600 dark:text-indigo-400 font-semibold">father_name</td>
                        <td class="px-3.5 py-2 text-slate-400">Optional</td>
                        <td class="px-3.5 py-2">Rajesh Sharma</td>
                        <td class="px-3.5 py-2 text-slate-400">N/A</td>
                    </tr>
                    <tr>
                        <td class="px-3.5 py-2 font-mono text-indigo-600 dark:text-indigo-400 font-semibold">father_phone</td>
                        <td class="px-3.5 py-2 text-slate-400">Optional</td>
                        <td class="px-3.5 py-2">9841000001</td>
                        <td class="px-3.5 py-2 text-slate-400">N/A</td>
                    </tr>
                    <tr>
                        <td class="px-3.5 py-2 font-mono text-indigo-600 dark:text-indigo-400 font-semibold">mother_name</td>
                        <td class="px-3.5 py-2 text-slate-400">Optional</td>
                        <td class="px-3.5 py-2">Sunita Sharma</td>
                        <td class="px-3.5 py-2 text-slate-400">N/A</td>
                    </tr>
                    <tr>
                        <td class="px-3.5 py-2 font-mono text-indigo-600 dark:text-indigo-400 font-semibold">address</td>
                        <td class="px-3.5 py-2 text-slate-400">Optional</td>
                        <td class="px-3.5 py-2">Baluwatar-4, Kathmandu</td>
                        <td class="px-3.5 py-2 text-slate-400">N/A</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Tab Switching
    function switchTab(tabId) {
        const csvTabBtn = document.getElementById('csv-tab-btn');
        const directTabBtn = document.getElementById('direct-tab-btn');
        const csvContent = document.getElementById('csv-tab-content');
        const directContent = document.getElementById('direct-tab-content');

        if (tabId === 'csv-tab') {
            csvTabBtn.className = 'pb-3 border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400 flex items-center gap-2 transition-all';
            directTabBtn.className = 'pb-3 border-b-2 border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 flex items-center gap-2 transition-all';
            csvContent.classList.remove('hidden');
            directContent.classList.add('hidden');
        } else {
            directTabBtn.className = 'pb-3 border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400 flex items-center gap-2 transition-all';
            csvTabBtn.className = 'pb-3 border-b-2 border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 flex items-center gap-2 transition-all';
            directContent.classList.remove('hidden');
            csvContent.classList.add('hidden');

            if (document.querySelectorAll('#studentRowsBody tr').length === 0) {
                for (let i = 0; i < 3; i++) {
                    addNewRow();
                }
            }
        }
    }

    // Dynamic Section Fetcher
    function loadSections(classSelect, targetSectionSelectId) {
        const classId = classSelect.value;
        const sectionSelect = document.getElementById(targetSectionSelectId);
        if (!classId || !sectionSelect) return;

        sectionSelect.innerHTML = '<option disabled selected value="">Loading sections...</option>';

        fetch("{{ route('get.sections.courses.by.classId') }}?class_id=" + classId)
            .then(res => res.json())
            .then(data => {
                sectionSelect.options.length = 0;
                sectionSelect.options[0] = new Option('— Select Section —', '');
                sectionSelect.options[0].disabled = true;
                sectionSelect.options[0].selected = true;

                if (data.sections && data.sections.length > 0) {
                    data.sections.forEach(sec => {
                        sectionSelect.add(new Option(sec.section_name, sec.id));
                    });
                } else {
                    sectionSelect.options[0] = new Option('No sections found for this class', '');
                }
            })
            .catch(err => {
                console.error(err);
                sectionSelect.innerHTML = '<option disabled selected value="">Failed to load sections</option>';
            });
    }

    // Drag and Drop File Selector
    function handleFileSelect(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            document.getElementById('file_label').innerHTML = `<span class="text-emerald-600 dark:text-emerald-400 font-bold"><i class="bi bi-check-circle me-1"></i> ${file.name}</span>`;
            document.getElementById('file_sublabel').innerText = `Size: ${(file.size / 1024).toFixed(1)} KB — Ready to import`;
        }
    }

    // Dynamic Multi-Row Table Handler
    let rowIndex = 0;

    function addNewRow() {
        const tbody = document.getElementById('studentRowsBody');
        const tr = document.createElement('tr');
        tr.id = `row_${rowIndex}`;
        tr.className = 'hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition';

        tr.innerHTML = `
            <td class="p-2 text-center text-slate-400 font-mono row-number">${rowIndex + 1}</td>
            <td class="p-2">
                <input type="text" name="students[${rowIndex}][first_name]" placeholder="First Name" required
                       class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:border-indigo-500">
            </td>
            <td class="p-2">
                <input type="text" name="students[${rowIndex}][last_name]" placeholder="Last Name" required
                       class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:border-indigo-500">
            </td>
            <td class="p-2">
                <select name="students[${rowIndex}][gender]" required
                        class="w-full text-xs px-2 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:border-indigo-500">
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </td>
            <td class="p-2">
                <input type="text" name="students[${rowIndex}][roll_no]" placeholder="101"
                       class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:border-indigo-500">
            </td>
            <td class="p-2">
                <input type="email" name="students[${rowIndex}][email]" placeholder="Auto if empty"
                       class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:border-indigo-500">
            </td>
            <td class="p-2">
                <input type="text" name="students[${rowIndex}][phone]" placeholder="9800000000"
                       class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:border-indigo-500">
            </td>
            <td class="p-2">
                <input type="text" name="students[${rowIndex}][father_name]" placeholder="Father Name"
                       class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:border-indigo-500">
            </td>
            <td class="p-2">
                <input type="text" name="students[${rowIndex}][father_phone]" placeholder="Father Phone"
                       class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:border-indigo-500">
            </td>
            <td class="p-2">
                <input type="text" name="students[${rowIndex}][address]" placeholder="City / Address"
                       class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:border-indigo-500">
            </td>
            <td class="p-2 text-center">
                <button type="button" onclick="removeRow(${rowIndex})"
                        class="w-7 h-7 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 inline-flex items-center justify-center transition">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;

        tbody.appendChild(tr);
        rowIndex++;
        updateRowNumbers();
    }

    function removeRow(idx) {
        const row = document.getElementById(`row_${idx}`);
        if (row) {
            row.remove();
            updateRowNumbers();
        }
    }

    function updateRowNumbers() {
        const rows = document.querySelectorAll('#studentRowsBody .row-number');
        rows.forEach((el, index) => {
            el.innerText = index + 1;
        });
    }
</script>
@endsection
