@extends('layouts.app')

@section('content')
<div class="px-4 lg:px-8 py-6 max-w-7xl mx-auto">

    {{-- Page Header --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <span class="w-10 h-10 rounded-2xl bg-indigo-600/10 text-indigo-600 flex items-center justify-center font-bold text-xl shadow-xs">
                    <i class="bi bi-calendar2-check-fill"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Take Student Attendance</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Mark daily attendance for students per grade level and section</p>
                </div>
            </div>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800/80 px-3 py-1.5 rounded-xl">
                <li><a href="{{route('home')}}" class="hover:text-indigo-600 transition">Home</a></li>
                <li class="text-slate-300 dark:text-slate-600">/</li>
                <li class="font-semibold text-slate-800 dark:text-slate-200">Take Attendance</li>
            </ol>
        </nav>
    </div>

    @include('session-messages')

    @if(!$student_list)
        {{-- STEP 1: Select Class & Section --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6 md:p-8 max-w-2xl mx-auto">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
                <span class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400 flex items-center justify-center font-bold text-lg">
                    <i class="bi bi-filter-square"></i>
                </span>
                <div>
                    <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">Select Class & Section</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Choose grade and section to open the student attendance roster</p>
                </div>
            </div>

            <form action="{{ route('attendance.create.show') }}" method="GET" class="space-y-4">
                <div>
                    <label for="select_class_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Select Class <span class="text-rose-500">*</span>
                    </label>
                    <select id="select_class_id" name="class_id" required onchange="loadSectionsForClass(this.value)" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                        <option value="">-- Choose Class --</option>
                        @isset($school_classes)
                            @foreach ($school_classes as $sc)
                                <option value="{{ $sc->id }}">{{ $sc->class_name }}</option>
                            @endforeach
                        @endisset
                    </select>
                </div>

                <div>
                    <label for="select_section_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Select Section <span class="text-rose-500">*</span>
                    </label>
                    <select id="select_section_id" name="section_id" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                        <option value="0">-- Select Class First --</option>
                    </select>
                </div>

                <div class="pt-4 text-right">
                    <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-lg shadow-indigo-600/25 transition flex items-center justify-center gap-2">
                        <i class="bi bi-arrow-right-circle"></i> Load Student Roster
                    </button>
                </div>
            </form>
        </div>
    @else
        {{-- STEP 2: Attendance Roster Sheet --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
            
            {{-- Header info bar --}}
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">
                            {{ $school_class->class_name ?? 'Class' }}
                        </span>
                        @if($school_section)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                            Section {{ $school_section->section_name }} (Room {{ $school_section->room_no ?? 'N/A' }})
                        </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        <i class="bi bi-clock me-1"></i> Date & Time: <span class="font-semibold text-slate-700 dark:text-slate-300">{{ date('l, d M Y - H:i') }}</span>
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" onclick="setAllAttendance(true)" class="px-3 py-1.5 rounded-xl border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold transition flex items-center gap-1.5">
                        <i class="bi bi-check-all"></i> Mark All Present
                    </button>
                    <button type="button" onclick="setAllAttendance(false)" class="px-3 py-1.5 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold transition flex items-center gap-1.5">
                        <i class="bi bi-x-circle"></i> Mark All Absent
                    </button>
                    <a href="{{ route('attendance.create.show') }}" class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-xs font-semibold hover:bg-slate-100 transition">
                        Change Class
                    </a>
                </div>
            </div>

            {{-- Form Table --}}
            <form action="{{ route('attendances.store') }}" method="POST">
                @csrf
                <input type="hidden" name="session_id" value="{{ $current_school_session_id }}">
                <input type="hidden" name="class_id" value="{{ request()->query('class_id') }}">
                @if ($academic_setting->attendance_type == 'course')
                    <input type="hidden" name="course_id" value="{{ request()->query('course_id') }}">
                    <input type="hidden" name="section_id" value="0">
                @else
                    <input type="hidden" name="course_id" value="0">
                    <input type="hidden" name="section_id" value="{{ request()->query('section_id') }}">
                @endif

                @if(count($student_list) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-semibold bg-slate-50/50 dark:bg-slate-800/20">
                                <th class="py-3 px-4">Roll No.</th>
                                <th class="py-3 px-4"># ID Card</th>
                                <th class="py-3 px-4">Student Name</th>
                                <th class="py-3 px-4 text-center">Status (Present / Absent)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($student_list as $student)
                            <input type="hidden" name="student_ids[]" value="{{ $student->student_id }}">
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                                <td class="py-3 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                    {{ $student->roll_no ?? $student->student->roll_no ?? '—' }}
                                </td>
                                <td class="py-3 px-4 font-mono font-bold text-slate-700 dark:text-slate-300">
                                    {{ $student->id_card_number ?? '—' }}
                                </td>
                                <td class="py-3 px-4 font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-indigo-600/10 text-indigo-600 font-bold flex items-center justify-center text-xs">
                                        {{ substr($student->student->first_name ?? 'S', 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-800 dark:text-slate-100">
                                            {{ $student->student->first_name ?? '' }} {{ $student->student->last_name ?? '' }}
                                        </p>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                                        <input type="checkbox" name="status[{{ $student->student_id }}]" checked class="attendance-checkbox w-4 h-4 text-emerald-600 rounded focus:ring-emerald-500 border-slate-300">
                                        <span class="text-xs font-bold text-emerald-600 status-label">Present</span>
                                    </label>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-6 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <p class="text-xs text-slate-400">Total Students: <span class="font-bold text-slate-700 dark:text-slate-200">{{ count($student_list) }}</span></p>
                    
                    @if($attendance_count < 1)
                    <button type="submit" id="attendanceSubmitBtn"
                            class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-lg shadow-indigo-600/25 transition flex items-center gap-2">
                        <i class="bi bi-check2-circle text-base"></i>
                        <span id="attendanceSubmitLabel">Save Attendance</span>
                    </button>
                    @else
                    <span class="text-xs font-semibold text-amber-600 bg-amber-50 px-3 py-1.5 rounded-xl">
                        <i class="bi bi-exclamation-triangle me-1"></i> Attendance already submitted for today
                    </span>
                    @endif
                </div>
                @else
                <div class="p-12 text-center">
                    <i class="bi bi-people text-4xl text-slate-300 dark:text-slate-600 mb-2 block"></i>
                    <p class="text-xs font-bold text-slate-700 dark:text-slate-300">No students enrolled in this class/section.</p>
                </div>
                @endif
            </form>
        </div>
    @endif

    @include('layouts.footer')
</div>

<script>
function loadSectionsForClass(classId) {
    const selectSection = document.getElementById('select_section_id');
    selectSection.innerHTML = '<option value="0">Loading sections...</option>';

    if (!classId) {
        selectSection.innerHTML = '<option value="0">-- Select Class First --</option>';
        return;
    }

    fetch('{{ route("get.sections.courses.by.classId") }}?class_id=' + classId)
        .then(res => res.json())
        .then(data => {
            let html = '<option value="0">-- All Sections / Select Section --</option>';
            if (data.sections && data.sections.length > 0) {
                data.sections.forEach(sec => {
                    html += `<option value="${sec.id}">${sec.section_name} (Room ${sec.room_no || 'N/A'})</option>`;
                });
            }
            selectSection.innerHTML = html;
        })
        .catch(err => {
            selectSection.innerHTML = '<option value="0">-- All Sections --</option>';
        });
}

function setAllAttendance(isPresent) {
    document.querySelectorAll('.attendance-checkbox').forEach(cb => {
        cb.checked = isPresent;
        const label = cb.closest('label').querySelector('.status-label');
        if (label) {
            label.textContent = isPresent ? 'Present' : 'Absent';
            label.className = 'text-xs font-bold ' + (isPresent ? 'text-emerald-600' : 'text-rose-600');
        }
    });
}

// ── Offline-first attendance submit ─────────────────────────────────────────
(function () {
    const form = document.querySelector('form[action*="attendances/store"], form[action*="attendances.store"]');
    if (!form) return;

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const btn   = document.getElementById('attendanceSubmitBtn');
        const label = document.getElementById('attendanceSubmitLabel');

        if (btn) { btn.disabled = true; }
        if (label) { label.textContent = 'Saving…'; }

        // Read hidden meta fields from the form
        const sessionId  = parseInt(form.querySelector('[name="session_id"]')?.value  || 0);
        const classId    = parseInt(form.querySelector('[name="class_id"]')?.value    || 0);
        const sectionId  = parseInt(form.querySelector('[name="section_id"]')?.value  || 0);
        const courseId   = parseInt(form.querySelector('[name="course_id"]')?.value   || 0);

        // Collect student IDs and their status (checked = 'on', unchecked = 'off')
        const studentIds = [...form.querySelectorAll('[name="student_ids[]"]')].map(i => parseInt(i.value));
        const statusMap  = {};
        form.querySelectorAll('.attendance-checkbox').forEach(cb => {
            const sid = parseInt(cb.name.replace('status[', '').replace(']', ''));
            statusMap[sid] = cb.checked ? 'on' : 'off';
        });

        const payload = {
            session_id:  sessionId,
            class_id:    classId,
            section_id:  sectionId,
            course_id:   courseId,
            student_ids: studentIds,
            status:      statusMap,
        };

        try {
            // window.submitAttendance is defined in attendance-sync.js
            if (typeof window.submitAttendance === 'function') {
                const result = await window.submitAttendance(payload);

                if (result.synced) {
                    // Online path — reload to show server-rendered confirmation
                    window.location.reload();
                } else {
                    // Offline path — show saved-offline confirmation in-page
                    if (label) { label.textContent = 'Saved offline ✓'; }
                    if (btn)   { btn.className = btn.className.replace('bg-indigo-600 hover:bg-indigo-700', 'bg-emerald-600'); }

                    // Replace the submit button area with a clear offline notice
                    const submitArea = btn?.closest('div');
                    if (submitArea) {
                        submitArea.innerHTML = `
                            <span class="inline-flex items-center gap-2 text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-200 px-4 py-2 rounded-xl">
                                <i class="bi bi-cloud-slash"></i>
                                Attendance saved offline — will sync when connected
                            </span>`;
                    }
                }
            } else {
                // Fallback: sync JS not loaded, submit the form normally
                form.submit();
            }
        } catch (err) {
            console.error('[Attendance] Submit error:', err);
            if (btn)   { btn.disabled = false; }
            if (label) { label.textContent = 'Save Attendance'; }
            alert('Error saving attendance: ' + err.message);
        }
    });

    // Live checkbox label update
    form.querySelectorAll('.attendance-checkbox').forEach(cb => {
        cb.addEventListener('change', function () {
            const lbl = this.closest('label')?.querySelector('.status-label');
            if (lbl) {
                lbl.textContent  = this.checked ? 'Present' : 'Absent';
                lbl.className    = 'text-xs font-bold ' + (this.checked ? 'text-emerald-600' : 'text-rose-600');
            }
        });
    });
}());
</script>
@endsection
