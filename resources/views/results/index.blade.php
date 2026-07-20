@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">
        <div class="mb-7">
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight"><i class="bi bi-clipboard-data me-2"></i>Results</h1>
            <p class="text-slate-400 text-sm mt-0.5">Select class, section and semester to view results</p>
        </div>
        @include('session-messages')
        <div class="max-w-lg bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Class <span class="text-rose-500">*</span></label>
                <select id="rClassId" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" onchange="loadSections(this.value)">
                    <option value="">— Select class —</option>
                    @foreach($classes as $c)<option value="{{ $c->id }}">{{ $c->class_name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Section <span class="text-rose-500">*</span></label>
                <select id="rSectionId" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    <option value="0">— Select section —</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Semester <span class="text-rose-500">*</span></label>
                <select id="rSemId" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    <option value="">— Select semester —</option>
                    @foreach($semesters as $s)<option value="{{ $s->id }}">{{ $s->semester_name }}</option>@endforeach
                </select>
            </div>
            <div class="flex flex-wrap gap-3 pt-1">
                <button onclick="go('class')"  class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition"><i class="bi bi-people me-1"></i> Class Result Sheet</button>
                <button onclick="go('merit')"  class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium transition"><i class="bi bi-trophy me-1"></i> Merit List</button>
                <button onclick="go('pdf')"    class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-sm font-medium transition"><i class="bi bi-file-earmark-pdf me-1"></i> PDF</button>
                <button onclick="go('excel')"  class="px-5 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-sm font-medium transition"><i class="bi bi-file-earmark-excel me-1"></i> Excel</button>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
function loadSections(classId) {
    if (!classId) return;
    fetch(`/sections?class_id=${classId}`)
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('rSectionId');
            sel.innerHTML = '<option value="0">— All sections —</option>';
            (data.sections || []).forEach(s => {
                sel.insertAdjacentHTML('beforeend', `<option value="${s.id}">${s.section_name}</option>`);
            });
        });
}
function go(type) {
    const c = document.getElementById('rClassId').value;
    const s = document.getElementById('rSectionId').value;
    const sem = document.getElementById('rSemId').value;
    if (!c || !sem) { alert('Please select class and semester.'); return; }
    const p = `?class_id=${c}&section_id=${s}&semester_id=${sem}`;
    const urls = {
        class:  '{{ route("results.class") }}' + p,
        merit:  '{{ route("results.merit") }}' + p,
        pdf:    '{{ route("results.class.pdf") }}' + p,
        excel:  '{{ route("results.class.excel") }}' + p,
    };
    window.open(urls[type], '_blank');
}
</script>
@endpush
@endsection
