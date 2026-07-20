@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">
        <div class="mb-7">
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight"><i class="bi bi-calendar-plus me-2"></i>Add Exam Schedule Entry</h1>
        </div>
        @include('session-messages')
        <div class="max-w-2xl">
            <form method="POST" action="{{ route('exam.schedule.store') }}" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
                @csrf
                {{-- Exam selector: load dynamically by class+semester --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Class</label>
                        <select id="schedClassId" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="">— Select class —</option>
                            @foreach($classes as $cls)<option value="{{ $cls->id }}">{{ $cls->class_name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Semester</label>
                        <select id="schedSemId" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="">— Select semester —</option>
                            @foreach($semesters as $sem)<option value="{{ $sem->id }}">{{ $sem->semester_name }}</option>@endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Exam <span class="text-rose-500">*</span></label>
                    <select name="exam_id" id="examSelect" required class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="">— Select class + semester first —</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Exam Date <span class="text-rose-500">*</span></label>
                        <input type="date" name="exam_date" required value="{{ old('exam_date') }}" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Start Time <span class="text-rose-500">*</span></label>
                            <input type="time" name="start_time" required value="{{ old('start_time', '09:00') }}" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">End Time <span class="text-rose-500">*</span></label>
                            <input type="time" name="end_time" required value="{{ old('end_time', '12:00') }}" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Hall</label>
                        <select name="hall_id" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="">— None —</option>
                            @foreach($halls as $h)<option value="{{ $h->id }}">{{ $h->hall_name }} (cap. {{ $h->capacity }})</option>@endforeach
                        </select>
                        <p class="text-xs text-amber-600 mt-1"><i class="bi bi-exclamation-triangle me-1"></i>Conflict detection runs on save.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Invigilator</label>
                        <select name="invigilator_id" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="">— None —</option>
                            @foreach($invigilators as $inv)<option value="{{ $inv->id }}">{{ $inv->full_name }}</option>@endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Notes</label>
                    <textarea name="notes" rows="2" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="Optional notes…">{{ old('notes') }}</textarea>
                </div>
                <div class="flex gap-3 pt-1">
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition">Save Entry</button>
                    <a href="{{ route('exam.schedule.index') }}" class="px-5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-medium transition">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
<script>
const sessionId = {{ $sessionId }};
function loadExams() {
    const classId = document.getElementById('schedClassId').value;
    const semId   = document.getElementById('schedSemId').value;
    if (!classId || !semId) return;
    fetch(`/sections?class_id=${classId}`)
        .then(() => {
            // Load exams via existing list endpoint
            return fetch(`/exams/view?class_id=${classId}&semester_id=${semId}`, {
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            });
        }).catch(() => {});
    // Direct approach: use the by-exam JSON endpoint
    fetch(`/exam/schedule/exams-for?class_id=${classId}&semester_id=${semId}`)
        .then(r => r.json())
        .then(exams => {
            const sel = document.getElementById('examSelect');
            sel.innerHTML = '<option value="">— Select exam —</option>';
            exams.forEach(e => {
                sel.insertAdjacentHTML('beforeend', `<option value="${e.id}">${e.exam_name} – ${e.course?.course_name ?? ''}</option>`);
            });
        }).catch(() => {});
}
document.getElementById('schedClassId').addEventListener('change', loadExams);
document.getElementById('schedSemId').addEventListener('change', loadExams);
</script>
@endpush
@endsection
