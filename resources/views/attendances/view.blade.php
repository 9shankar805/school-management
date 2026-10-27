@extends('layouts.app')

@section('content')
<div class="px-4 lg:px-8 py-6 max-w-7xl mx-auto">

    {{-- Page Header --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <span class="w-10 h-10 rounded-2xl bg-indigo-600/10 text-indigo-600 flex items-center justify-center font-bold text-xl shadow-xs">
                    <i class="bi bi-calendar-range-fill"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">View Student Attendance Logs</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">View daily attendance logs, presence history, and total attendance records</p>
                </div>
            </div>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800/80 px-3 py-1.5 rounded-xl">
                <li><a href="{{route('home')}}" class="hover:text-indigo-600 transition">Home</a></li>
                <li class="text-slate-300 dark:text-slate-600">/</li>
                <li class="font-semibold text-slate-800 dark:text-slate-200">View Attendance</li>
            </ol>
        </nav>
    </div>

    @include('session-messages')

    @if(!$attendances)
        {{-- STEP 1: Select Class & Section --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6 md:p-8 max-w-2xl mx-auto">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
                <span class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400 flex items-center justify-center font-bold text-lg">
                    <i class="bi bi-search"></i>
                </span>
                <div>
                    <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">Select Class & Section to View Logs</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Choose class and section to inspect student attendance history</p>
                </div>
            </div>

            <form action="{{ route('attendance.list.show') }}" method="GET" class="space-y-4">
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
                        <i class="bi bi-eye"></i> View Attendance Records
                    </button>
                </div>
            </form>
        </div>
    @else
        {{-- STEP 2: Attendance Records Table --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
            
            {{-- Header info bar --}}
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-1">
                        Attendance Logs: 
                        @if(request()->query('course_name'))
                            Course {{ request()->query('course_name') }}
                        @elseif(request()->query('section_name'))
                            Section {{ request()->query('section_name') }}
                        @else
                            Class ID #{{ request()->query('class_id') }}
                        @endif
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Current Session Date: {{ date('l, d M Y') }}</p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('attendance.list.show') }}" class="px-3.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-xs font-semibold hover:bg-slate-100 transition">
                        Change Filter
                    </a>
                    <a href="{{ route('attendance.create.show', ['class_id' => request()->query('class_id'), 'section_id' => request()->query('section_id')]) }}" class="px-3.5 py-1.5 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 transition flex items-center gap-1.5">
                        <i class="bi bi-plus-lg"></i> Take New Attendance
                    </a>
                </div>
            </div>

            @if(count($attendances) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-semibold bg-slate-50/50 dark:bg-slate-800/20">
                            <th class="py-3 px-4">Student Name</th>
                            <th class="py-3 px-4">Today's Status</th>
                            <th class="py-3 px-4">Taken Time & Clock</th>
                            <th class="py-3 px-4">Recorded By (In-Charge)</th>
                            <th class="py-3 px-4 text-right">Total Days Attended</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($attendances as $attendance)
                            @php
                                $total_attended = \App\Models\Attendance::where('student_id', $attendance->student_id)->where('session_id', $attendance->session_id)->count();
                            @endphp
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                                <td class="py-3 px-4 font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-indigo-600/10 text-indigo-600 font-bold flex items-center justify-center text-xs">
                                        {{ substr($attendance->student->first_name ?? 'S', 0, 1) }}
                                    </div>
                                    <span>{{ $attendance->student->first_name ?? '' }} {{ $attendance->student->last_name ?? '' }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    @if ($attendance->status == "on" || $attendance->status == 1)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                                            PRESENT
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300">
                                            ABSENT
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-600 dark:text-slate-300">
                                    <span class="inline-flex items-center gap-1">
                                        <i class="bi bi-clock text-indigo-500"></i>
                                        {{ $attendance->taken_time ?? ($attendance->submitted_at ? $attendance->submitted_at->format('h:i A') : ($attendance->created_at ? $attendance->created_at->format('h:i A') : '—')) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-600 dark:text-slate-300">
                                    <span class="inline-flex items-center gap-1 font-medium">
                                        <i class="bi bi-person-badge text-emerald-600"></i>
                                        {{ $attendance->recordedBy?->name ?? 'Class In-Charge' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-slate-700 dark:text-slate-300">
                                    {{ $total_attended }} Days
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="p-12 text-center">
                <i class="bi bi-calendar-x text-4xl text-slate-300 dark:text-slate-600 mb-2 block"></i>
                <p class="text-xs font-bold text-slate-700 dark:text-slate-300">No attendance logs found for this selection.</p>
            </div>
            @endif
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

    fetch('{{ route("get.sections.courses.by.classId") }}?class_id=' + classId + '&for_attendance=1')
        .then(res => res.json())
        .then(data => {
            let html = '';
            if (data.sections && data.sections.length > 0) {
                data.sections.forEach((sec, idx) => {
                    const selected = idx === 0 ? 'selected' : '';
                    html += `<option value="${sec.id}" ${selected}>Section ${sec.section_name} (Room ${sec.room_no || 'N/A'})</option>`;
                });
            } else {
                html = '<option value="0">-- No In-Charge Section Found --</option>';
            }
            selectSection.innerHTML = html;
        })
        .catch(err => {
            selectSection.innerHTML = '<option value="0">-- Error loading sections --</option>';
        });
}
</script>
@endsection
