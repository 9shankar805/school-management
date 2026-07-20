@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="mb-7">
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight"><i class="bi bi-file-earmark-spreadsheet me-2"></i>Monthly Attendance Report</h1>
            <p class="text-slate-400 text-sm mt-0.5">Generate and download attendance summaries per class.</p>
        </div>

        @include('session-messages')

        <div class="max-w-lg">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Class <span class="text-rose-500">*</span></label>
                    <select name="class_id" id="classId" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="">— Select class —</option>
                        @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->class_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Month <span class="text-rose-500">*</span></label>
                        <select name="month" id="month" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            @foreach(range(1,12) as $m)
                            <option value="{{ $m }}" {{ now()->month == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::createFromDate(null, $m, 1)->format('F') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Year <span class="text-rose-500">*</span></label>
                        <select name="year" id="year" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            @foreach(range(now()->year - 2, now()->year + 1) as $y)
                            <option value="{{ $y }}" {{ now()->year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex gap-3 pt-1">
                    <button onclick="submitReport('pdf')" type="button"
                        class="flex-1 flex items-center justify-center gap-1.5 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-sm font-medium transition">
                        <i class="bi bi-file-earmark-pdf"></i> Download PDF
                    </button>
                    <button onclick="submitReport('excel')" type="button"
                        class="flex-1 flex items-center justify-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium transition">
                        <i class="bi bi-file-earmark-excel"></i> Download Excel
                    </button>
                </div>
            </div>

            {{-- Sample CSV template download --}}
            <div class="mt-4 bg-indigo-50 border border-indigo-100 rounded-2xl p-4 flex items-start gap-3">
                <i class="bi bi-info-circle text-indigo-400 text-lg mt-0.5 flex-shrink-0"></i>
                <div>
                    <p class="text-sm font-medium text-indigo-800">Need to import attendance from a CSV?</p>
                    <p class="text-xs text-indigo-600 mt-0.5">
                        <a href="{{ route('attendance.import.form') }}" class="underline">Go to the bulk import page</a> to upload attendance records from a spreadsheet.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function submitReport(type) {
    const classId = document.getElementById('classId').value;
    const month   = document.getElementById('month').value;
    const year    = document.getElementById('year').value;

    if (!classId) { alert('Please select a class.'); return; }

    const route = type === 'pdf'
        ? '{{ route("attendance.report.pdf") }}'
        : '{{ route("attendance.report.excel") }}';

    const url = `${route}?class_id=${classId}&month=${month}&year=${year}`;
    window.open(url, '_blank');
}
</script>
@endpush
@endsection
