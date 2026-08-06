@extends('layouts.app')
@section('title', 'Custom Report Builder')

@section('content')
<div class="flex-1 p-6 lg:p-8 overflow-auto">

    {{-- Header --}}
    <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight flex items-center gap-2">
                <i class="bi bi-magic text-indigo-500"></i> Custom Report Builder
            </h1>
            <p class="text-slate-400 text-sm mt-0.5">Select your data source and columns to build a custom export.</p>
        </div>
        <a href="{{ route('reports.index') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-700 text-white text-sm font-medium rounded-lg hover:bg-slate-800 transition">
            <i class="bi bi-arrow-left"></i> Back to Report Center
        </a>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- ── Configuration panel ── --}}
        <div class="xl:col-span-1">
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-6">
                <h2 class="text-base font-semibold text-slate-700 dark:text-slate-300 mb-5">Configuration</h2>

                {{-- 1. Data source --}}
                <div class="mb-5">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-2">1. Data Source</label>
                    <select id="dataSource" onchange="updateColumns()"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-sm px-3 py-2.5 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                        <option value="students">Students Directory</option>
                        <option value="staff">Staff Directory</option>
                        <option value="invoices">Invoices / Finance</option>
                    </select>
                </div>

                {{-- 2. Columns --}}
                <div class="mb-5">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-2">2. Select Columns</label>
                    <div id="columnSelector"
                         class="rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 p-3 space-y-1.5 max-h-56 overflow-y-auto">
                        {{-- populated by JS --}}
                    </div>
                    <div class="flex gap-2 mt-2">
                        <button onclick="selectAll(true)"
                                class="text-xs text-indigo-600 hover:underline">Select all</button>
                        <span class="text-slate-300">|</span>
                        <button onclick="selectAll(false)"
                                class="text-xs text-slate-400 hover:underline">Clear</button>
                    </div>
                </div>

                {{-- 3. Date range --}}
                <div class="mb-6">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-2">3. Date Range <span class="font-normal normal-case">(optional)</span></label>
                    <div class="flex items-center gap-2">
                        <input type="date" id="dateFrom"
                               class="flex-1 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-sm px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                        <span class="text-slate-400 text-sm">to</span>
                        <input type="date" id="dateTo"
                               class="flex-1 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-sm px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    </div>
                </div>

                {{-- Generate button --}}
                <button onclick="generatePreview()"
                        class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-sm transition flex items-center justify-center gap-2">
                    <i class="bi bi-play-fill"></i> Generate Preview
                </button>
            </div>
        </div>

        {{-- ── Preview / Export panel ── --}}
        <div class="xl:col-span-2">
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm flex flex-col h-full">

                {{-- Panel header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                    <h2 class="text-base font-semibold text-slate-700 dark:text-slate-300">Data Preview</h2>
                    <div class="flex items-center gap-2">
                        <span id="exportSpinner" class="hidden">
                            <svg class="animate-spin h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                            </svg>
                        </span>
                        <button id="btnPdf" onclick="exportReport('pdf')" disabled
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800 rounded-lg text-xs font-semibold hover:bg-rose-100 transition disabled:opacity-40 disabled:cursor-not-allowed">
                            <i class="bi bi-file-pdf-fill"></i> PDF
                        </button>
                        <button id="btnExcel" onclick="exportReport('excel')" disabled
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 rounded-lg text-xs font-semibold hover:bg-emerald-100 transition disabled:opacity-40 disabled:cursor-not-allowed">
                            <i class="bi bi-file-earmark-excel-fill"></i> Excel
                        </button>
                    </div>
                </div>

                {{-- Panel body --}}
                <div class="p-6 flex-1 overflow-auto">

                    {{-- Empty state --}}
                    <div id="previewEmpty" class="flex flex-col items-center justify-center py-16 text-slate-300 dark:text-slate-600">
                        <i class="bi bi-table text-5xl mb-3"></i>
                        <p class="text-sm">Configure your report and click <strong>Generate Preview</strong>.</p>
                    </div>

                    {{-- Loading --}}
                    <div id="previewLoading" class="hidden flex flex-col items-center justify-center py-16 text-slate-400">
                        <svg class="animate-spin h-8 w-8 text-indigo-500 mb-3" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                        <p class="text-sm">Building report…</p>
                    </div>

                    {{-- Error --}}
                    <div id="previewError" class="hidden py-4 px-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-xl text-rose-700 dark:text-rose-400 text-sm">
                        <i class="bi bi-exclamation-circle-fill me-1"></i>
                        <span id="previewErrorMsg">Failed to load preview.</span>
                    </div>

                    {{-- Table --}}
                    <div id="previewTableWrap" class="hidden overflow-x-auto">
                        <table class="min-w-full text-sm border-collapse">
                            <thead id="previewThead" class="bg-slate-50 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs uppercase tracking-wide">
                            </thead>
                            <tbody id="previewTbody" class="divide-y divide-slate-100 dark:divide-slate-700">
                            </tbody>
                        </table>
                        <p id="previewMeta" class="text-xs text-slate-400 mt-3 px-1"></p>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

<script>
const COLUMNS = {
    students: {
        id:             'ID',
        first_name:     'First Name',
        last_name:      'Last Name',
        gender:         'Gender',
        class:          'Grade / Class',
        section:        'Section',
        admission_no:   'Admission No.',
        enrollment_date:'Enrollment Date',
        roll_no:        'Roll No.',
        email:          'Email',
        phone:          'Phone',
        father_name:    "Father's Name",
        mother_name:    "Mother's Name",
    },
    staff: {
        id:        'ID',
        name:      'Full Name',
        email:     'Email',
        role:      'Role',
        phone:     'Phone',
        join_date: 'Join Date',
    },
    invoices: {
        invoice_number: 'Invoice No.',
        student_name:   'Student Name',
        title:          'Title',
        amount:         'Amount',
        total_amount:   'Total',
        status:         'Status',
        due_date:       'Due Date',
        billing_month:  'Month',
        billing_year:   'Year',
    },
};

// Populate column checkboxes when source changes
function updateColumns() {
    const source = document.getElementById('dataSource').value;
    const cols   = COLUMNS[source] || {};
    let html = '';
    Object.entries(cols).forEach(([key, label]) => {
        html += `<label class="flex items-center gap-2 cursor-pointer text-sm text-slate-700 dark:text-slate-200 hover:text-indigo-600 transition">
            <input type="checkbox" class="col-checkbox rounded accent-indigo-600" value="${key}" checked>
            ${label}
        </label>`;
    });
    document.getElementById('columnSelector').innerHTML = html;
    // Reset preview
    showState('empty');
    setExportEnabled(false);
}

function selectAll(state) {
    document.querySelectorAll('.col-checkbox').forEach(cb => cb.checked = state);
}

function showState(state) {
    ['empty','loading','error','tableWrap'].forEach(id => {
        const el = document.getElementById('preview' + id.charAt(0).toUpperCase() + id.slice(1));
        if (!el) return;
        el.classList.add('hidden');
    });
    // also handle special IDs
    document.getElementById('previewEmpty').classList.toggle('hidden',  state !== 'empty');
    document.getElementById('previewLoading').classList.toggle('hidden', state !== 'loading');
    document.getElementById('previewError').classList.toggle('hidden',   state !== 'error');
    document.getElementById('previewTableWrap').classList.toggle('hidden', state !== 'table');
}

function setExportEnabled(enabled) {
    ['btnPdf','btnExcel'].forEach(id => {
        document.getElementById(id).disabled = !enabled;
    });
}

function buildParams(extraFormat) {
    const source  = document.getElementById('dataSource').value;
    const checked = Array.from(document.querySelectorAll('.col-checkbox:checked')).map(cb => cb.value);
    const dateFrom= document.getElementById('dateFrom').value;
    const dateTo  = document.getElementById('dateTo').value;

    const params = new URLSearchParams();
    params.set('source', source);
    checked.forEach(c => params.append('columns[]', c));
    if (dateFrom) params.set('date_from', dateFrom);
    if (dateTo)   params.set('date_to', dateTo);
    if (extraFormat) params.set('format', extraFormat);
    return params;
}

async function generatePreview() {
    showState('loading');
    setExportEnabled(false);

    try {
        const params = buildParams(null);
        const res = await fetch(`{{ route('reports.builder.preview') }}?${params}`);
        if (!res.ok) throw new Error('Server returned ' + res.status);
        const data = await res.json();

        // Build header
        let thead = '<tr>';
        thead += '<th class="px-3 py-2 text-left font-semibold w-8">#</th>';
        data.columns.forEach(col => {
            thead += `<th class="px-3 py-2 text-left font-semibold whitespace-nowrap">${col}</th>`;
        });
        thead += '</tr>';
        document.getElementById('previewThead').innerHTML = thead;

        // Build body
        let tbody = '';
        if (data.rows.length === 0) {
            tbody = `<tr><td colspan="${data.columns.length + 1}" class="px-3 py-6 text-center text-slate-400 italic">No records found.</td></tr>`;
        } else {
            data.rows.forEach((row, i) => {
                const bg = i % 2 === 0 ? '' : 'bg-slate-50 dark:bg-slate-700/40';
                tbody += `<tr class="${bg}">`;
                tbody += `<td class="px-3 py-2 text-slate-400 text-xs">${i + 1}</td>`;
                data.keys.forEach(key => {
                    tbody += `<td class="px-3 py-2 text-slate-700 dark:text-slate-200">${row[key] ?? '—'}</td>`;
                });
                tbody += '</tr>';
            });
        }
        document.getElementById('previewTbody').innerHTML = tbody;
        document.getElementById('previewMeta').textContent =
            data.rows.length === 20
                ? `Showing first 20 rows. Use Export to download the full dataset.`
                : `${data.rows.length} record(s) found.`;

        showState('table');
        setExportEnabled(data.rows.length > 0);

    } catch (err) {
        document.getElementById('previewErrorMsg').textContent = err.message || 'Failed to load preview.';
        showState('error');
    }
}

function exportReport(format) {
    const params = buildParams(format);
    document.getElementById('exportSpinner').classList.remove('hidden');
    // Navigate — browser will prompt the download
    const url = `{{ route('reports.builder.export') }}?${params}`;
    const a = document.createElement('a');
    a.href = url;
    a.download = '';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    setTimeout(() => document.getElementById('exportSpinner').classList.add('hidden'), 2000);
}

// Init
document.addEventListener('DOMContentLoaded', updateColumns);
</script>
@endsection
