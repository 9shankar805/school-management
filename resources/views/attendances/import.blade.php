@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="mb-7">
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight"><i class="bi bi-upload me-2"></i>Bulk Attendance Import</h1>
            <p class="text-slate-400 text-sm mt-0.5">Upload a CSV file to import multiple attendance records at once.</p>
        </div>

        @include('session-messages')

        <div class="max-w-xl space-y-6">

            {{-- Upload form --}}
            <form method="POST" action="{{ route('attendance.import') }}" enctype="multipart/form-data"
                  class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Class <span class="text-rose-500">*</span></label>
                    <select name="class_id" required
                            class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="">— Select class —</option>
                        @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>{{ $class->class_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">CSV File <span class="text-rose-500">*</span></label>
                    <input type="file" name="csv_file" accept=".csv,.txt" required
                           class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                    <p class="text-xs text-slate-400 mt-1">Max 2 MB. Accepted: .csv, .txt</p>
                </div>

                <button type="submit"
                    class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition flex items-center gap-1.5">
                    <i class="bi bi-upload"></i> Import Records
                </button>
            </form>

            {{-- CSV format guide --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <p class="text-sm font-semibold text-slate-700 mb-3"><i class="bi bi-info-circle me-1 text-indigo-400"></i>CSV Format</p>
                <p class="text-xs text-slate-500 mb-3">Your CSV must include a header row with these columns (in any order):</p>

                <div class="overflow-x-auto rounded-lg border border-slate-100">
                    <table class="w-full text-xs">
                        <thead><tr class="bg-slate-50 text-left">
                            <th class="px-3 py-2 font-semibold text-slate-600">Column</th>
                            <th class="px-3 py-2 font-semibold text-slate-600">Required</th>
                            <th class="px-3 py-2 font-semibold text-slate-600">Accepted Values</th>
                        </tr></thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr><td class="px-3 py-2 font-mono text-indigo-600">student_id</td><td class="px-3 py-2 text-rose-500">Yes</td><td class="px-3 py-2 text-slate-500">User ID integer</td></tr>
                            <tr><td class="px-3 py-2 font-mono text-indigo-600">date</td><td class="px-3 py-2 text-rose-500">Yes</td><td class="px-3 py-2 text-slate-500">YYYY-MM-DD (e.g. 2026-07-20)</td></tr>
                            <tr><td class="px-3 py-2 font-mono text-indigo-600">status</td><td class="px-3 py-2 text-rose-500">Yes</td><td class="px-3 py-2 text-slate-500">present / absent / on / off / p / a / 1 / 0</td></tr>
                            <tr><td class="px-3 py-2 font-mono text-indigo-600">late_minutes</td><td class="px-3 py-2 text-slate-400">No</td><td class="px-3 py-2 text-slate-500">Integer (0 = on time)</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 bg-slate-50 rounded-xl p-3">
                    <p class="text-xs font-semibold text-slate-600 mb-1">Example:</p>
                    <pre class="text-xs text-slate-500 leading-relaxed">student_id,date,status,late_minutes
42,2026-07-20,present,0
43,2026-07-20,absent,0
44,2026-07-20,present,12</pre>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
