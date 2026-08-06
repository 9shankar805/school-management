@extends('layouts.app')
@section('title', $schoolClass->class_name . ' — Class Detail')

@section('content')
<div class="flex-1 p-6 lg:p-8 overflow-auto">

{{-- Breadcrumb --}}
<nav class="text-xs text-slate-400 mb-5 flex items-center gap-1.5">
    <a href="{{ route('home') }}" class="hover:text-indigo-600">Home</a>
    <i class="bi bi-chevron-right text-[9px]"></i>
    <a href="{{ url('/classes') }}" class="hover:text-indigo-600">Classes</a>
    <i class="bi bi-chevron-right text-[9px]"></i>
    <span class="text-slate-600 font-medium">{{ $schoolClass->class_name }}</span>
</nav>

@if(session('status'))
<div class="mb-5 flex items-center gap-2 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm">
    <i class="bi bi-check-circle-fill"></i> {{ session('status') }}
</div>
@endif

{{-- ── Hero header ── --}}
<div class="bg-gradient-to-br from-indigo-600 to-indigo-800 rounded-2xl p-6 mb-6 text-white shadow-lg">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-5">
            <div class="w-16 h-16 rounded-2xl bg-white/15 border border-white/20 flex items-center justify-center text-2xl font-extrabold shadow-inner">
                {{ strtoupper(substr($schoolClass->class_name, 0, 2)) }}
            </div>
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight">{{ $schoolClass->class_name }}</h1>
                <div class="flex flex-wrap items-center gap-3 mt-1 text-indigo-200 text-xs">
                    <span><i class="bi bi-people-fill me-1"></i>{{ $promotions->count() }} Students</span>
                    <span><i class="bi bi-diagram-2 me-1"></i>{{ $sections->count() }} Section{{ $sections->count() !== 1 ? 's' : '' }}</span>
                    <span><i class="bi bi-journal-medical me-1"></i>{{ $schoolClass->courses->count() }} Subject{{ $schoolClass->courses->count() !== 1 ? 's' : '' }}</span>
                    <span><i class="bi bi-person-badge me-1"></i>{{ $assignedTeachers->unique('teacher_id')->count() }} Teacher{{ $assignedTeachers->unique('teacher_id')->count() !== 1 ? 's' : '' }}</span>
                </div>
            </div>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('class.edit', $schoolClass->id) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-white/15 hover:bg-white/25 border border-white/20 rounded-xl text-xs font-semibold transition">
                <i class="bi bi-pencil"></i> Edit Class
            </a>
            <a href="{{ route('section.routine.show', ['class_id' => $schoolClass->id, 'section_id' => $sections->first()?->id]) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-white/15 hover:bg-white/25 border border-white/20 rounded-xl text-xs font-semibold transition">
                <i class="bi bi-calendar3-week"></i> Full Timetable
            </a>
        </div>
    </div>
</div>

{{-- ── Sections tab bar ── --}}
@php $activeSection = request('section', $sections->first()?->id); @endphp
@if($sections->count() > 1)
<div class="flex gap-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-1 mb-6 overflow-x-auto shadow-sm w-fit">
    @foreach($sections as $sec)
    <a href="{{ route('class.detail.show', [$schoolClass->id, 'section' => $sec->id]) }}"
       class="flex-shrink-0 px-4 py-2 rounded-lg text-xs font-semibold transition whitespace-nowrap
              {{ $activeSection == $sec->id ? 'bg-indigo-600 text-white shadow' : 'text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700 dark:text-slate-400' }}">
        Section {{ $sec->section_name }}
    </a>
    @endforeach
</div>
@endif

{{-- ── 3-column layout ── --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

{{-- ══════════════════════════════════════
     LEFT COL: Students
══════════════════════════════════════ --}}
<div class="xl:col-span-2 space-y-6">

    {{-- Student list --}}
    @php
        $sectionStudents = $activeSection
            ? $promotions->where('section_id', $activeSection)
            : $promotions;
    @endphp
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
            <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                <i class="bi bi-people-fill text-indigo-500"></i>
                Students
                @if($activeSection && $sections->count() > 1)
                    — Section {{ $sections->firstWhere('id', $activeSection)?->section_name }}
                @endif
            </p>
            <div class="flex items-center gap-2">
                <span class="text-xs bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 px-2.5 py-0.5 rounded-full font-semibold">
                    {{ $sectionStudents->count() }} enrolled
                </span>
                <a href="{{ route('student.list.show', ['class_id' => $schoolClass->id]) }}"
                   class="text-xs text-indigo-600 hover:underline">View all →</a>
            </div>
        </div>
        @if($sectionStudents->count())
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-700/50 text-xs text-slate-400 uppercase tracking-wide">
                    <tr>
                        <th class="px-4 py-2.5 text-left font-medium w-8">#</th>
                        <th class="px-4 py-2.5 text-left font-medium">Student</th>
                        <th class="px-4 py-2.5 text-left font-medium">Roll No.</th>
                        <th class="px-4 py-2.5 text-left font-medium">Admission No.</th>
                        <th class="px-4 py-2.5 text-left font-medium">Section</th>
                        <th class="px-4 py-2.5 text-left font-medium">Gender</th>
                        <th class="px-4 py-2.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-700">
                    @foreach($sectionStudents as $i => $promo)
                    @php $stu = $promo->student; @endphp
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40 transition">
                        <td class="px-4 py-2.5 text-slate-400 text-xs">{{ $i + 1 }}</td>
                        <td class="px-4 py-2.5">
                            <div class="flex items-center gap-2.5">
                                <img src="{{ $stu?->avatar ?? '' }}" alt=""
                                     class="w-7 h-7 rounded-full object-cover border border-slate-100 flex-shrink-0">
                                <div>
                                    <p class="font-medium text-slate-700 dark:text-slate-200 text-xs leading-tight">{{ $stu?->full_name ?? '—' }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $stu?->email ?? '' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300 font-mono text-xs">{{ $promo->roll_no ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-slate-500 text-xs">{{ $stu?->academic_info?->admission_no ?? $promo->id_card_number ?? '—' }}</td>
                        <td class="px-4 py-2.5">
                            <span class="text-[10px] bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 px-2 py-0.5 rounded-full font-medium">
                                {{ $promo->section?->section_name ?? '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-2.5 text-slate-500 dark:text-slate-400 text-xs capitalize">{{ $stu?->gender ?? '—' }}</td>
                        <td class="px-4 py-2.5">
                            @if($stu)
                            <a href="{{ route('student.profile.show', $stu->id) }}"
                               class="text-xs text-indigo-600 hover:underline">Profile</a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="py-12 text-center text-slate-400">
            <i class="bi bi-people text-3xl block mb-2 opacity-30"></i>
            <p class="text-sm">No students enrolled in this section.</p>
        </div>
        @endif
    </div>

    {{-- ── Timetable for this class ── --}}
    @php
        $days = [1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday',0=>'Sunday'];
        $sectionTimetable = $activeSection && isset($timetableSlots[$activeSection])
            ? $timetableSlots[$activeSection]->groupBy('weekday')
            : collect();
    @endphp
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
            <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                <i class="bi bi-calendar3-week text-violet-500"></i> Weekly Timetable
            </p>
            <a href="{{ route('section.routine.show', ['class_id' => $schoolClass->id, 'section_id' => $activeSection]) }}"
               class="text-xs text-indigo-600 hover:underline">Manage →</a>
        </div>
        @if($sectionTimetable->count())
        <div class="overflow-x-auto">
            <table class="w-full text-xs min-w-[540px]">
                <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-400 uppercase tracking-wide">
                    <tr>
                        <th class="px-4 py-2.5 text-left font-medium w-24">Day</th>
                        <th class="px-4 py-2.5 text-left font-medium">Time</th>
                        <th class="px-4 py-2.5 text-left font-medium">Subject</th>
                        <th class="px-4 py-2.5 text-left font-medium">Teacher</th>
                        <th class="px-4 py-2.5 text-left font-medium">Room</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-700">
                    @foreach($days as $dayNum => $dayName)
                        @if(isset($sectionTimetable[$dayNum]))
                        @foreach($sectionTimetable[$dayNum]->sortBy('start') as $slot)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40">
                            @if($loop->first)
                            <td class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300"
                                rowspan="{{ $sectionTimetable[$dayNum]->count() }}">
                                {{ $dayName }}
                            </td>
                            @endif
                            <td class="px-4 py-2.5 font-mono text-slate-500 dark:text-slate-400">{{ $slot->start }}–{{ $slot->end }}</td>
                            <td class="px-4 py-2.5">
                                <span class="font-medium text-slate-700 dark:text-slate-200">{{ $slot->course?->course_name ?? '—' }}</span>
                            </td>
                            <td class="px-4 py-2.5 text-slate-500 dark:text-slate-400">
                                @if($slot->teacher)
                                <a href="{{ route('teacher.profile.show', $slot->teacher->id) }}"
                                   class="text-indigo-600 hover:underline">{{ $slot->teacher->full_name }}</a>
                                @else <span class="text-slate-300">—</span> @endif
                            </td>
                            <td class="px-4 py-2.5 text-slate-400">{{ $slot->room ?? '—' }}</td>
                        </tr>
                        @endforeach
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="py-10 text-center text-slate-400">
            <i class="bi bi-calendar3-week text-3xl block mb-2 opacity-30"></i>
            <p class="text-sm">No timetable slots configured.</p>
            <a href="{{ route('section.routine.create') }}" class="mt-2 inline-block text-xs text-indigo-600 hover:underline">Add timetable →</a>
        </div>
        @endif
    </div>

</div>{{-- /left col --}}

{{-- ══════════════════════════════════════
     RIGHT COL: Teachers & Subjects
══════════════════════════════════════ --}}
<div class="xl:col-span-1 space-y-5">

    {{-- Class overview stats --}}
    <div class="grid grid-cols-2 gap-3">
        @foreach([
            ['indigo', 'bi-people-fill',      $promotions->count(),                        'Students'],
            ['violet', 'bi-journal-medical',   $schoolClass->courses->count(),              'Subjects'],
            ['emerald','bi-person-badge',       $assignedTeachers->unique('teacher_id')->count(), 'Teachers'],
            ['amber',  'bi-diagram-2',          $sections->count(),                          'Sections'],
        ] as [$color, $icon, $val, $lbl])
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-4 text-center">
            <i class="bi {{ $icon }} text-xl text-{{ $color }}-500 block mb-1"></i>
            <p class="text-2xl font-extrabold text-slate-800 dark:text-slate-100 leading-none">{{ $val }}</p>
            <p class="text-[10px] text-slate-400 mt-0.5 uppercase tracking-wide">{{ $lbl }}</p>
        </div>
        @endforeach
    </div>

    {{-- Subject → Teacher mapping --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-700">
            <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                <i class="bi bi-book-half text-blue-500"></i> Subject Teachers
            </p>
        </div>
        @php
            $subjectTeacherMap = $assignedTeachers
                ->when($activeSection, fn($c) => $c->where('section_id', $activeSection))
                ->groupBy(fn($a) => $a->course_id);
        @endphp
        @if($subjectTeacherMap->count())
        <div class="divide-y divide-slate-50 dark:divide-slate-700">
            @foreach($subjectTeacherMap as $courseId => $assignments)
            @php $courseName = $assignments->first()?->course?->course_name ?? 'Unknown Subject'; @endphp
            <div class="px-5 py-3">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="bi bi-book text-blue-600 dark:text-blue-400 text-sm"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-slate-700 dark:text-slate-200 leading-tight">{{ $courseName }}</p>
                        @foreach($assignments->unique('teacher_id') as $assign)
                        @if($assign->teacher)
                        <div class="flex items-center gap-2 mt-1.5">
                            <img src="{{ $assign->teacher->avatar }}" alt=""
                                 class="w-5 h-5 rounded-full object-cover border border-slate-100 flex-shrink-0">
                            <a href="{{ route('teacher.profile.show', $assign->teacher->id) }}"
                               class="text-[11px] text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
                                {{ $assign->teacher->full_name }}
                            </a>
                            @if(!$activeSection && $assign->section)
                            <span class="text-[10px] text-slate-400 bg-slate-100 dark:bg-slate-700 px-1.5 py-0.5 rounded-full">
                                {{ $assign->section->section_name }}
                            </span>
                            @endif
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="py-8 text-center text-slate-400">
            <i class="bi bi-book text-2xl block mb-2 opacity-30"></i>
            <p class="text-xs">No teachers assigned to subjects yet.</p>
        </div>
        @endif
    </div>

    {{-- All teachers in this class --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-700">
            <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                <i class="bi bi-person-badge text-emerald-500"></i>
                Teaching Staff
                <span class="text-xs font-normal text-slate-400">({{ $assignedTeachers->unique('teacher_id')->count() }})</span>
            </p>
        </div>
        @php $uniqueTeachers = $assignedTeachers->unique('teacher_id'); @endphp
        @if($uniqueTeachers->count())
        <div class="divide-y divide-slate-50 dark:divide-slate-700">
            @foreach($uniqueTeachers as $at)
            @if(!$at->teacher) @continue @endif
            <div class="px-5 py-3 flex items-center gap-3 hover:bg-slate-50 dark:hover:bg-slate-700/40 transition">
                <img src="{{ $at->teacher->avatar }}" alt=""
                     class="w-9 h-9 rounded-xl object-cover border border-slate-100 flex-shrink-0">
                <div class="flex-1 min-w-0">
                    <a href="{{ route('teacher.profile.show', $at->teacher->id) }}"
                       class="text-xs font-semibold text-slate-700 dark:text-slate-200 hover:text-indigo-600 transition truncate block">
                        {{ $at->teacher->full_name }}
                    </a>
                    {{-- All subjects this teacher handles in this class --}}
                    @php
                        $teacherSubjects = $assignedTeachers
                            ->where('teacher_id', $at->teacher_id)
                            ->map(fn($a) => $a->course?->course_name)
                            ->filter()->unique()->values();
                    @endphp
                    <p class="text-[10px] text-slate-400 truncate mt-0.5">
                        {{ $teacherSubjects->implode(' · ') ?: '—' }}
                    </p>
                </div>
                <a href="{{ route('teacher.profile.show', $at->teacher->id) }}"
                   class="flex-shrink-0 text-xs text-slate-400 hover:text-indigo-600 transition">
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            @endforeach
        </div>
        @else
        <div class="py-8 text-center text-slate-400">
            <i class="bi bi-person-badge text-2xl block mb-2 opacity-30"></i>
            <p class="text-xs">No teachers assigned yet.</p>
        </div>
        @endif
    </div>

    {{-- Sections quick overview --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-700">
            <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                <i class="bi bi-diagram-2 text-amber-500"></i> Sections
            </p>
        </div>
        @if($sections->count())
        <div class="divide-y divide-slate-50 dark:divide-slate-700">
            @foreach($sections as $sec)
            @php $secCount = $promotions->where('section_id', $sec->id)->count(); @endphp
            <a href="{{ route('class.detail.show', [$schoolClass->id, 'section' => $sec->id]) }}"
               class="px-5 py-3 flex items-center justify-between gap-3 hover:bg-slate-50 dark:hover:bg-slate-700/40 transition block">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-xs font-bold text-amber-600 dark:text-amber-400 flex-shrink-0">
                        {{ $sec->section_name }}
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-700 dark:text-slate-200">Section {{ $sec->section_name }}</p>
                        @if($sec->room_no)<p class="text-[10px] text-slate-400"><i class="bi bi-door-open me-0.5"></i>Room {{ $sec->room_no }}</p>@endif
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">{{ $secCount }}</span>
                    <span class="text-[10px] text-slate-400">students</span>
                    <i class="bi bi-chevron-right text-[10px] text-slate-300"></i>
                </div>
            </a>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Subjects list --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-700">
            <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                <i class="bi bi-journal-medical text-violet-500"></i> Subjects
                <span class="text-xs font-normal text-slate-400">({{ $schoolClass->courses->count() }})</span>
            </p>
        </div>
        @if($schoolClass->courses->count())
        <div class="flex flex-wrap gap-2 p-4">
            @foreach($schoolClass->courses as $course)
            <span class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 bg-violet-50 dark:bg-violet-900/20 text-violet-700 dark:text-violet-300 border border-violet-100 dark:border-violet-800 rounded-full">
                <i class="bi bi-dot text-violet-400"></i> {{ $course->course_name }}
            </span>
            @endforeach
        </div>
        @else
        <p class="text-xs text-slate-400 text-center py-6">No subjects added.</p>
        @endif
    </div>

</div>{{-- /right col --}}
</div>{{-- /grid --}}
</div>{{-- /content --}}
@endsection
