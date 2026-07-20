@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <nav class="text-xs text-slate-400 mb-4">
            <a href="{{ route('re-exam.index') }}" class="hover:text-indigo-600">Re-Exam Applications</a>
            <span class="mx-1">/</span>
            <span class="text-slate-600">Apply</span>
        </nav>

        <div class="mb-7">
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight"><i class="bi bi-arrow-repeat me-2"></i>Apply for Re-Exam</h1>
            <p class="text-slate-400 text-sm mt-0.5">Submit a supplementary exam request for a failed course</p>
        </div>

        @include('session-messages')

        <div class="max-w-2xl">
            @if($failedCourses->isEmpty())
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-10 text-center">
                <i class="bi bi-patch-check text-5xl text-emerald-200"></i>
                <p class="mt-3 text-slate-500 font-medium">No failed courses found.</p>
                <p class="text-sm text-slate-400 mt-1">You have no courses below the passing threshold in the current session.</p>
                <a href="{{ route('re-exam.index') }}" class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-medium transition">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
            @else
            <form method="POST" action="{{ route('re-exam.store') }}"
                  class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Course (Failed) <span class="text-rose-500">*</span></label>
                    <select name="course_id" id="courseSelect" required onchange="fillClassFromCourse(this)"
                            class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="">— Select failed course —</option>
                        @foreach($failedCourses as $mark)
                        <option value="{{ $mark->course_id }}"
                                data-class="{{ $mark->class_id ?? '' }}"
                                data-marks="{{ $mark->final_marks }}">
                            {{ $mark->course->course_name ?? 'Course #'.$mark->course_id }}
                            — {{ $mark->semester->semester_name ?? '' }}
                            ({{ $mark->final_marks }} marks)
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Hidden class_id / section_id filled from selection --}}
                <input type="hidden" name="class_id" id="classIdInput" value="">

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Semester <span class="text-rose-500">*</span></label>
                    <select name="semester_id" required
                            class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="">— Select semester —</option>
                        @foreach($semesters as $sem)
                        <option value="{{ $sem->id }}" {{ old('semester_id') == $sem->id ? 'selected' : '' }}>
                            {{ $sem->semester_name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Reason for Re-Exam Application <span class="text-rose-500">*</span></label>
                    <textarea name="reason" rows="4" required
                              class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                              placeholder="Explain why you are applying for a re-exam (e.g. illness during exam, extenuating circumstances)…">{{ old('reason') }}</textarea>
                    <p class="text-xs text-slate-400 mt-1">Maximum 1000 characters.</p>
                </div>

                {{-- Current marks info box --}}
                <div id="marksInfoBox" class="hidden bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-700">
                    <i class="bi bi-info-circle me-1"></i>
                    Your current marks: <strong id="marksDisplay">—</strong>. Re-exam marks will replace this score if higher.
                </div>

                <div class="flex gap-3 pt-1">
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition">
                        <i class="bi bi-send me-1"></i> Submit Application
                    </button>
                    <a href="{{ route('re-exam.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-medium transition">Cancel</a>
                </div>
            </form>
            @endif
        </div>
    </div>
</div>
<script>
function fillClassFromCourse(select) {
    const opt = select.options[select.selectedIndex];
    document.getElementById('classIdInput').value = opt.dataset.class || '';
    const marks = opt.dataset.marks;
    if (marks !== undefined) {
        document.getElementById('marksDisplay').textContent = marks;
        document.getElementById('marksInfoBox').classList.remove('hidden');
    } else {
        document.getElementById('marksInfoBox').classList.add('hidden');
    }
}
</script>
@endsection
