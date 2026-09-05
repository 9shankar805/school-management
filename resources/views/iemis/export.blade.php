@extends('layouts.app')
@section('title', 'IEMIS Data Export')
@section('content')
<div class="px-4 lg:px-8 py-6 max-w-3xl mx-auto">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
            <i class="bi bi-file-earmark-spreadsheet text-blue-600"></i>
            IEMIS Data Export
        </h1>
        <p class="text-xs text-slate-500 mt-1">
            Export student enrolment data in CEHRD / Nepal Ministry of Education IEMIS format.
            Use this to prepare the annual EMIS Flash Report.
        </p>
    </div>

    @include('session-messages')

    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 mb-4">
        <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-4 flex items-center gap-2">
            <i class="bi bi-funnel text-indigo-500"></i> Select Export Parameters
        </h3>
        <form method="POST" id="exportForm">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Academic Session <span class="text-rose-500">*</span></label>
                    <select name="session_id" class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                        @foreach($sessions as $session)
                        <option value="{{ $session->id }}" {{ $session->id == $currentSessionId ? 'selected' : '' }}>
                            {{ $session->session_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Class / Grade <span class="text-slate-400 font-normal">(optional — all classes if blank)</span></label>
                    <select name="class_id" class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                        <option value="">— All Classes —</option>
                        @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->class_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="button"
                        onclick="submitExport('{{ route('iemis.export.excel') }}')"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 transition shadow-sm">
                    <i class="bi bi-file-earmark-excel-fill"></i> Download Excel (.xlsx)
                </button>
                <button type="button"
                        onclick="submitExport('{{ route('iemis.export.csv') }}')"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition shadow-sm">
                    <i class="bi bi-filetype-csv"></i> Download CSV
                </button>
                <button type="button"
                        onclick="submitExport('{{ route('iemis.export.pdf') }}')"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 transition shadow-sm">
                    <i class="bi bi-file-earmark-pdf-fill"></i> Download PDF
                </button>
            </div>
        </form>
    </div>

    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-2xl p-5">
        <p class="text-xs font-bold text-blue-700 dark:text-blue-400 mb-2 flex items-center gap-1.5">
            <i class="bi bi-info-circle-fill"></i> Fields Included in the Export
        </p>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-1 text-[11px] text-blue-700 dark:text-blue-300">
            @foreach(['Student ID', 'Admission No', 'Full Name', 'DOB (AD & BS)', 'Gender', 'Ethnicity/Caste', 'Religion', 'Mother Tongue', 'Nationality', 'Differently Abled', 'Province/District/Municipality', 'Ward No', 'Phone & Email', 'Class & Section', 'Roll No', "Father's/Mother's Name & Phone", 'Guardian Details', 'Board Reg No', 'Scholarship Status', 'Admission Date'] as $field)
            <div class="flex items-center gap-1"><i class="bi bi-check-circle-fill text-blue-500 text-[10px]"></i> {{ $field }}</div>
            @endforeach
        </div>
    </div>

</div>

<script>
function submitExport(action) {
    const form = document.getElementById('exportForm');
    form.action = action;
    form.submit();
    form.action = '';
}
</script>
@endsection
