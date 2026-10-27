@extends('layouts.app')
@section('title', 'Exams & Examinations')

@section('content')
<div class="px-4 lg:px-8 py-6 max-w-full">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-500/30 flex-shrink-0">
                <i class="bi bi-journal-check text-white text-xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Exams & Examinations</h1>
                <p class="text-xs text-slate-400 mt-0.5">Manage term exams, subject rules, gradebooks and mark sheets</p>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @unlessrole('student|parent')
            <a href="{{ route('exam.create.show') }}"
               class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-sm transition flex items-center gap-1.5">
                <i class="bi bi-plus-circle"></i> Create Exam
            </a>
            <button onclick="openBulkModal()"
                    class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition flex items-center gap-1.5">
                <i class="bi bi-lightning-fill"></i> Bulk Create
            </button>
            <a href="{{ route('exam.grade.system.index') }}"
               class="px-4 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-50 transition flex items-center gap-1.5">
                <i class="bi bi-award text-indigo-500"></i> Grading Systems
            </a>
            @endunlessrole
        </div>
    </div>

    @include('session-messages')

    {{-- Filters --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-4 mb-5">
        <form action="{{ route('exam.list.show') }}" method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Filter Class</label>
                <select name="class_id" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 outline-none focus:border-indigo-500">
                    <option value="0">-- All Classes --</option>
                    @foreach($classes as $sc)
                    <option value="{{ $sc->id }}" {{ (request('class_id') == $sc->id || ($class_id ?? 0) == $sc->id) ? 'selected' : '' }}>
                        {{ $sc->class_name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Filter Term / Semester</label>
                <select name="semester_id" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 outline-none focus:border-indigo-500">
                    <option value="0">-- All Terms --</option>
                    @foreach($semesters as $sem)
                    <option value="{{ $sem->id }}" {{ (request('semester_id') == $sem->id || ($semester_id ?? 0) == $sem->id) ? 'selected' : '' }}>
                        {{ $sem->semester_name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition flex items-center gap-1.5">
                    <i class="bi bi-funnel-fill"></i> Filter
                </button>
                @if(request('class_id') || request('semester_id'))
                <a href="{{ route('exam.list.show') }}" class="px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-500 hover:bg-slate-50 text-xs font-semibold transition" title="Clear">
                    <i class="bi bi-x-lg"></i>
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Scheduled Examinations</h2>
                <p class="text-xs text-slate-400 mt-0.5">All exams across classes and academic terms</p>
            </div>
            <span class="text-[11px] font-bold px-3 py-1.5 rounded-full bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-400">
                Total: {{ count($exams ?? []) }} Exams
            </span>
        </div>

        @if(isset($exams) && count($exams) > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-xs text-slate-400 uppercase tracking-wider font-semibold border-b border-slate-100 dark:border-slate-800">
                    <tr>
                        <th class="px-5 py-3">Exam Title</th>
                        <th class="px-4 py-3">Class</th>
                        <th class="px-4 py-3">Subject</th>
                        <th class="px-4 py-3">Term</th>
                        <th class="px-4 py-3">Start Date</th>
                        <th class="px-4 py-3">End Date</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                    @foreach($exams as $exam)
                    @php
                        $now = now();
                        $startDate = $exam->start_date ? \Carbon\Carbon::parse($exam->start_date) : null;
                        $endDate   = $exam->end_date   ? \Carbon\Carbon::parse($exam->end_date)   : null;
                        if ($startDate && $now->lt($startDate))
                            [$statusClass, $statusIcon, $statusLabel] = ['bg-amber-100 text-amber-700', 'bi-clock', 'Upcoming'];
                        elseif ($startDate && $endDate && $now->between($startDate, $endDate))
                            [$statusClass, $statusIcon, $statusLabel] = ['bg-emerald-100 text-emerald-700', 'bi-play-circle-fill', 'Ongoing'];
                        else
                            [$statusClass, $statusIcon, $statusLabel] = ['bg-slate-100 text-slate-500', 'bi-check-all', 'Completed'];
                    @endphp
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                        <td class="px-5 py-3.5 font-bold text-slate-800 dark:text-slate-100">
                            <i class="bi bi-file-earmark-text text-indigo-400 me-1.5"></i>{{ $exam->exam_name }}
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="text-[11px] font-semibold px-2 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-400">
                                {{ $exam->schoolClass->class_name ?? '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="text-[11px] font-semibold px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                {{ $exam->course->course_name ?? '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-xs text-slate-500 dark:text-slate-400 font-medium">
                            {{ $exam->semester->semester_name ?? '—' }}
                        </td>
                        <td class="px-4 py-3.5 text-xs text-slate-600 dark:text-slate-400">
                            @if($startDate)
                                <span class="font-medium">{{ $startDate->format('M d, Y') }}</span>
                                <span class="text-slate-400 block">{{ $startDate->format('h:i A') }}</span>
                            @else —
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-xs text-slate-600 dark:text-slate-400">
                            @if($endDate)
                                <span class="font-medium">{{ $endDate->format('M d, Y') }}</span>
                                <span class="text-slate-400 block">{{ $endDate->format('h:i A') }}</span>
                            @else —
                            @endif
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full {{ $statusClass }}">
                                <i class="bi {{ $statusIcon }} me-1"></i>{{ $statusLabel }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                {{-- Admit Card --}}
                                <button type="button"
                                        onclick="openAdmitModal({{ $exam->id }})"
                                        class="p-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition" title="Admit Card">
                                    <i class="bi bi-person-badge-fill"></i>
                                </button>
                                {{-- Rule --}}
                                @can('create exams rule')
                                <a href="{{ route('exam.rule.create', ['exam_id' => $exam->id]) }}"
                                   class="p-1.5 text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition" title="Add Rule">
                                    <i class="bi bi-plus-circle"></i>
                                </a>
                                @endcan
                                @can('view exams rule')
                                <a href="{{ route('exam.rule.show', ['exam_id' => $exam->id]) }}"
                                   class="p-1.5 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition" title="View Rules">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @endcan
                                @can('edit exams')
                                <a href="{{ route('exam.edit', $exam->id) }}"
                                   class="p-1.5 text-xs font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 rounded-lg transition" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endcan
                                @can('delete exams')
                                <form action="{{ route('exam.delete') }}" method="POST" class="inline"
                                      onsubmit="return confirm('Delete this exam? This cannot be undone.')">
                                    @csrf
                                    <input type="hidden" name="exam_id" value="{{ $exam->id }}">
                                    <button type="submit"
                                            class="p-1.5 text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg transition" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-16">
            <i class="bi bi-journal-x text-5xl text-slate-200 dark:text-slate-700 block mb-3"></i>
            <p class="font-bold text-slate-500 dark:text-slate-400">No Exams Scheduled</p>
            <p class="text-xs text-slate-400 mt-1">No exams found. Clear filters or create a new examination.</p>
            @can('create exams')
            <a href="{{ route('exam.create.show') }}"
               class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                <i class="bi bi-plus-circle"></i> Schedule Exam Now
            </a>
            @endcan
        </div>
        @endif
    </div>

    @include('layouts.footer')
</div>
@endsection

@push('modals')
{{-- ⚡ Bulk Exam Creator Modal --}}
<div id="bulkExamModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[9999] items-center justify-center p-4"
     onclick="if(event.target===this)closeBulkModal()">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="px-6 py-4 bg-emerald-600 flex items-center justify-between">
            <div>
                <h3 class="text-white font-bold text-sm flex items-center gap-2">
                    <i class="bi bi-lightning-fill"></i> Bulk Exam Creator
                </h3>
                <p class="text-emerald-100 text-[11px] mt-0.5">Create exams for all subjects of a class in one click</p>
            </div>
            <button onclick="closeBulkModal()" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/20 hover:bg-white/30 text-white font-bold transition">&times;</button>
        </div>
        <form method="POST" action="{{ route('exam.bulk.create') }}" class="p-6 space-y-4 bg-white dark:bg-slate-900">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Term / Semester <span class="text-rose-500">*</span></label>
                    <select name="semester_id" id="bulk_sem" required onchange="loadBulkSubjects()"
                            class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                        <option value="">-- Select Term --</option>
                        @foreach($semesters as $sem)
                        <option value="{{ $sem->id }}">{{ $sem->semester_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Class <span class="text-rose-500">*</span></label>
                    <select name="class_id" id="bulk_class" required onchange="loadBulkSubjects()"
                            class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                        <option value="">-- Select Class --</option>
                        @foreach($classes as $cls)
                        <option value="{{ $cls->id }}">{{ $cls->class_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Exam Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="exam_name" required placeholder="e.g. Second Terminal, Unit Test 1, Mid-Term"
                           class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Start Date & Time <span class="text-rose-500">*</span></label>
                    <input type="datetime-local" name="start_date" required
                           class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">End Date & Time <span class="text-rose-500">*</span></label>
                    <input type="datetime-local" name="end_date" required
                           class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                </div>
            </div>
            {{-- Exam Rules --}}
            <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/40 space-y-3">
                <div class="flex items-center gap-2 mb-1">
                    <i class="bi bi-shield-check text-indigo-500 text-sm"></i>
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-200">Exam Rules (applied to all subjects)</span>
                    <span class="text-[10px] text-slate-400 font-normal ml-1">Optional — leave blank to set per-subject later</span>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Full / Total Marks</label>
                        <input type="number" name="total_marks" min="1" max="1000" step="0.5" placeholder="e.g. 100"
                               class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Pass Marks</label>
                        <input type="number" name="pass_marks" min="0" max="1000" step="0.5" placeholder="e.g. 40"
                               class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Pass % <span class="text-slate-400 font-normal">(auto)</span></label>
                        <input type="text" id="bulk_pass_pct" readonly placeholder="—"
                               class="w-full text-xs px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 outline-none cursor-default">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Marks Distribution <span class="text-slate-400 font-normal">(optional — e.g. Written: 70, MCQ: 30)</span></label>
                    <input type="text" name="marks_distribution_note" placeholder='e.g. {"Written": 70, "MCQ": 30}'
                           class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 font-mono">
                </div>
            </div>
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Subjects <span class="text-rose-500">*</span></label>
                    <div class="flex gap-3">
                        <button type="button" onclick="toggleAllSubjects(true)" class="text-[11px] text-emerald-600 hover:underline font-semibold">Select All</button>
                        <button type="button" onclick="toggleAllSubjects(false)" class="text-[11px] text-slate-400 hover:underline font-semibold">Deselect All</button>
                    </div>
                </div>
                <div id="bulk_subjects"
                     class="min-h-[60px] max-h-52 overflow-y-auto rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 p-3 grid grid-cols-2 gap-1.5 text-xs">
                    <p class="col-span-2 text-slate-400 text-center py-4">Select class and term above to load subjects…</p>
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeBulkModal()"
                        class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-700 transition">
                    Cancel
                </button>
                <button type="submit" id="bulk_submit_btn" disabled
                        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center gap-1.5">
                    <i class="bi bi-lightning-fill"></i> Create All Exams
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Admit Card Modal --}}
<div id="admitCardModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[9999] items-center justify-center p-4"
     onclick="if(event.target===this)closeAdmitModal()">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="px-6 py-4 bg-slate-900 flex items-center justify-between">
            <div>
                <h3 class="text-white font-bold text-sm flex items-center gap-2">
                    <i class="bi bi-person-badge-fill text-emerald-400"></i> Print Admit Cards
                </h3>
                <p id="admitModalExamName" class="text-slate-400 text-[11px] mt-0.5"></p>
            </div>
            <button onclick="closeAdmitModal()" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/10 hover:bg-white/20 text-white font-bold transition">&times;</button>
        </div>
        <form id="admitCardForm" method="GET" target="_blank" class="p-6 space-y-4 bg-white dark:bg-slate-900">
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Select Class <span class="text-rose-500">*</span></label>
                <select name="class_id" id="admitClassSelect" required
                        class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:border-indigo-500">
                    <option value="">-- Choose Class --</option>
                    @foreach($classes as $sc)
                    <option value="{{ $sc->id }}">{{ $sc->class_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Section (Optional)</label>
                <select name="section_id"
                        class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:border-indigo-500">
                    <option value="0">All Sections</option>
                </select>
            </div>
            <div class="p-3 bg-indigo-50 dark:bg-indigo-950/40 rounded-xl border border-indigo-100 dark:border-indigo-900 text-[11px] text-indigo-700 dark:text-indigo-300">
                <i class="bi bi-info-circle-fill me-1"></i>
                Admit cards include school branding, student photo, roll number, and QR code.
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeAdmitModal()"
                        class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg border border-slate-200 transition">
                    Cancel
                </button>
                <button type="submit"
                        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center gap-1.5">
                    <i class="bi bi-printer-fill"></i> Generate PDF
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Bulk Modal
function openBulkModal() {
    const m = document.getElementById('bulkExamModal');
    m.classList.remove('hidden'); m.classList.add('flex');
}
function closeBulkModal() {
    const m = document.getElementById('bulkExamModal');
    m.classList.add('hidden'); m.classList.remove('flex');
}
function loadBulkSubjects() {
    const classId = document.getElementById('bulk_class').value;
    const semId   = document.getElementById('bulk_sem').value;
    const box     = document.getElementById('bulk_subjects');
    const btn     = document.getElementById('bulk_submit_btn');
    if (!classId || !semId) {
        box.innerHTML = '<p class="col-span-2 text-slate-400 text-center py-4">Select class and term above…</p>';
        btn.disabled = true; return;
    }
    box.innerHTML = '<p class="col-span-2 text-slate-400 text-center py-4"><i class="bi bi-arrow-repeat me-1"></i>Loading subjects…</p>';
    fetch(`{{ route('get.sections.courses.by.classId') }}?class_id=${classId}&semester_id=${semId}&_t=${Date.now()}`)
        .then(r => r.json())
        .then(data => {
            const courses = data.courses || [];
            if (!courses.length) {
                box.innerHTML = '<p class="col-span-2 text-slate-400 text-center py-4">No subjects found.</p>';
                btn.disabled = true; return;
            }
            box.innerHTML = courses.map(c => `
                <label class="flex items-center gap-2 cursor-pointer px-2.5 py-2 rounded-lg hover:bg-white dark:hover:bg-slate-700 transition select-none">
                    <input type="checkbox" name="course_ids[]" value="${c.id}" checked
                           class="w-3.5 h-3.5 rounded accent-emerald-600 subject-checkbox">
                    <span class="text-slate-700 dark:text-slate-200 font-medium truncate">${c.course_name}</span>
                </label>`).join('');
            btn.disabled = false;
        })
        .catch(() => {
            box.innerHTML = '<p class="col-span-2 text-rose-500 text-center py-4">Failed to load. Try again.</p>';
            btn.disabled = true;
        });
}
function toggleAllSubjects(check) {
    document.querySelectorAll('.subject-checkbox').forEach(cb => cb.checked = check);
}

// Auto-calculate pass percentage
document.addEventListener('DOMContentLoaded', () => {
    const totalInput = document.querySelector('input[name="total_marks"]');
    const passInput  = document.querySelector('input[name="pass_marks"]');
    const pctField   = document.getElementById('bulk_pass_pct');
    function calcPct() {
        const t = parseFloat(totalInput?.value);
        const p = parseFloat(passInput?.value);
        if (t > 0 && p >= 0) {
            pctField.value = Math.round((p / t) * 100) + '%';
        } else {
            pctField.value = '—';
        }
    }
    totalInput?.addEventListener('input', calcPct);
    passInput?.addEventListener('input', calcPct);
});

// Admit Card Modal
const admitRoutes = @json($exams->mapWithKeys(fn($e) => [$e->id => route('exam.admit-card.batch', $e->id)]));
const admitNames  = @json($exams->mapWithKeys(fn($e) => [$e->id => $e->exam_name]));
const admitClasses = @json($exams->mapWithKeys(fn($e) => [$e->id => $e->class_id]));

function openAdmitModal(examId) {
    document.getElementById('admitModalExamName').textContent = admitNames[examId] || '';
    document.getElementById('admitCardForm').action = admitRoutes[examId] || '#';
    const sel = document.getElementById('admitClassSelect');
    if (admitClasses[examId]) sel.value = admitClasses[examId];
    const m = document.getElementById('admitCardModal');
    m.classList.remove('hidden'); m.classList.add('flex');
}
function closeAdmitModal() {
    const m = document.getElementById('admitCardModal');
    m.classList.add('hidden'); m.classList.remove('flex');
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeBulkModal(); closeAdmitModal(); }
});
</script>
@endpush
@endpush
