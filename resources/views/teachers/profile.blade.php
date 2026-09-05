@extends('layouts.app')
@section('content')
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <nav class="text-xs text-slate-400 mb-4">
            <a href="{{ route('home') }}" class="hover:text-indigo-600">Home</a> <span class="mx-1">/</span>
            <a href="{{ route('teacher.list.show') }}" class="hover:text-indigo-600">Teachers</a> <span class="mx-1">/</span>
            <span class="text-slate-600">{{ $teacher->full_name }}</span>
        </nav>

        @if(session('status'))
        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm"><i class="bi bi-check-circle me-1"></i>{{ session('status') }}</div>
        @endif
        @if($errors->any())
        <div class="mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-sm">@foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>
        @endif

        {{-- Profile header --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mb-6 flex flex-wrap items-start gap-6">
            <img src="{{ $teacher->avatar }}" class="w-20 h-20 rounded-2xl object-cover flex-shrink-0 border-2 border-indigo-100" alt="">
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-3 mb-1">
                    <h1 class="text-xl font-bold text-slate-800">{{ $teacher->full_name }}</h1>
                    <span class="text-xs bg-blue-100 text-blue-700 px-2.5 py-0.5 rounded-full font-medium capitalize">{{ str_replace('-',' ',$teacher->primary_role) }}</span>
                    @php $ctAssignments = $teacher->assignedCourses->where('is_class_teacher', true); @endphp
                    @if($ctAssignments->isNotEmpty())
                    <span class="text-xs bg-amber-100 text-amber-900 border border-amber-300 px-2.5 py-0.5 rounded-full font-bold flex items-center gap-1 shadow-sm">
                        <i class="bi bi-star-fill text-amber-500"></i> Class Teacher: {{ $ctAssignments->map(fn($a) => ($a->schoolClass?->class_name ?? '').' '.($a->section?->section_name ? '('.$a->section->section_name.')' : ''))->unique()->join(', ') }}
                    </span>
                    @endif
                    @if($teacher->departments->count())
                    @foreach($teacher->departments as $d)
                    <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full">{{ $d->name }}</span>
                    @endforeach
                    @endif
                </div>
                <div class="flex flex-wrap gap-4 text-xs text-slate-500 mt-1">
                    <span><i class="bi bi-envelope me-1"></i>{{ $teacher->email }}</span>
                    @if($teacher->phone)<span><i class="bi bi-telephone me-1"></i>{{ $teacher->phone }}</span>@endif
                    @if($activeContract)<span><i class="bi bi-briefcase me-1"></i>{{ ucfirst($activeContract->position ?? $activeContract->contract_type) }} · Rs. {{ number_format($activeContract->basic_salary) }}/mo</span>@endif
                </div>
                <div class="flex flex-wrap gap-4 mt-3">
                    <div class="text-center"><p class="text-lg font-bold text-slate-800">{{ $teacher->qualifications->count() }}</p><p class="text-[10px] text-slate-400">Qualifications</p></div>
                    <div class="text-center"><p class="text-lg font-bold text-indigo-600">{{ $teacher->assignedCourses->count() }}</p><p class="text-[10px] text-slate-400">Courses</p></div>
                    <div class="text-center"><p class="text-lg font-bold {{ ($attendanceStats['pct'] ?? 100) >= 75 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $attendanceStats['pct'] ?? '—' }}%</p><p class="text-[10px] text-slate-400">Attendance</p></div>
                    <div class="text-center"><p class="text-lg font-bold text-violet-600">{{ $teacher->performanceReviews->first()?->overall_rating ?? '—' }}</p><p class="text-[10px] text-slate-400">Last Review</p></div>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 flex-shrink-0">
                @can('assign teachers')
                <button type="button" onclick="openAssignTeacherModal()" class="inline-flex items-center gap-1.5 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold transition shadow-sm">
                    <i class="bi bi-person-plus-fill"></i> Assign Subject / Class Teacher
                </button>
                @endcan
                <a href="{{ route('teacher.id-card', $teacher->id) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-medium transition shadow-sm">
                    <i class="bi bi-person-badge"></i> ID Card
                </a>
                @can('create teachers')
                <a href="{{ route('teacher.edit.show', $teacher->id) }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-medium transition"><i class="bi bi-pencil"></i> Edit</a>
                @endcan
            </div>

        </div>

        {{-- Tab nav --}}
        <div class="flex gap-1 overflow-x-auto mb-6 bg-white rounded-xl border border-slate-100 shadow-sm p-1">
            @foreach([
                ['overview','bi-person-lines-fill','Overview'],
                ['timetable','bi-calendar3-week','Timetable'],
                ['qualifications','bi-award','Qualifications'],
                ['contracts','bi-file-earmark-text','Contracts'],
                ['documents','bi-folder2-open','Documents'],
                ['leave','bi-calendar-x','Leave'],
                ['attendance','bi-calendar-check','Attendance'],
                ['payroll','bi-cash-stack','Payroll'],
                ['performance','bi-graph-up','Performance'],
                ['training','bi-book','Training'],
            ] as [$id,$icon,$label])
            <button onclick="showTab('{{ $id }}')" id="tab-btn-{{ $id }}"
                class="tab-btn flex-shrink-0 flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-medium transition whitespace-nowrap text-slate-500 hover:text-slate-700 hover:bg-slate-50">
                <i class="bi {{ $icon }}"></i> {{ $label }}
            </button>
            @endforeach
        </div>

        {{-- TAB: OVERVIEW --}}
        <div id="tab-overview" class="tab-panel">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-3 border-b border-slate-100 text-sm font-semibold text-slate-700"><i class="bi bi-person me-1 text-indigo-500"></i>Personal Info</div>
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-slate-50">
                            @foreach([['Gender',$teacher->gender],['Birthday',$teacher->birthday?->format('d M Y')],['Nationality',$teacher->nationality],['Religion',$teacher->religion],['Blood Type',$teacher->blood_type],['Address',trim(($teacher->address??'').' '.($teacher->city??''))]] as [$l,$v])
                            <tr class="hover:bg-slate-50"><td class="px-5 py-2.5 text-slate-400 font-medium w-36">{{ $l }}</td><td class="px-5 py-2.5 text-slate-700">{{ $v ?: '—' }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="space-y-4">
                    {{-- Contract summary --}}
                    @if($activeContract)
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-5 py-3 border-b border-slate-100 text-sm font-semibold text-slate-700"><i class="bi bi-briefcase me-1 text-emerald-500"></i>Active Contract</div>
                        <table class="w-full text-sm">
                            <tbody class="divide-y divide-slate-50">
                                <tr class="hover:bg-slate-50"><td class="px-5 py-2.5 text-slate-400 font-medium w-36">Type</td><td class="px-5 py-2.5 text-slate-700">{{ ucfirst($activeContract->contract_type) }}</td></tr>
                                <tr class="hover:bg-slate-50"><td class="px-5 py-2.5 text-slate-400 font-medium">Position</td><td class="px-5 py-2.5 text-slate-700">{{ $activeContract->position ?? '—' }}</td></tr>
                                <tr class="hover:bg-slate-50"><td class="px-5 py-2.5 text-slate-400 font-medium">Salary</td><td class="px-5 py-2.5 text-slate-700">Rs. {{ number_format($activeContract->basic_salary) }}/month</td></tr>
                                <tr class="hover:bg-slate-50"><td class="px-5 py-2.5 text-slate-400 font-medium">Start</td><td class="px-5 py-2.5 text-slate-700">{{ $activeContract->start_date->format('d M Y') }}</td></tr>
                                <tr class="hover:bg-slate-50"><td class="px-5 py-2.5 text-slate-400 font-medium">End</td>
                                    <td class="px-5 py-2.5">
                                        @if($activeContract->end_date)
                                        <span class="{{ $activeContract->is_expiring ? 'text-amber-600 font-medium' : 'text-slate-700' }}">
                                            {{ $activeContract->end_date->format('d M Y') }}
                                            @if($activeContract->is_expiring) <i class="bi bi-exclamation-triangle-fill text-amber-500 ms-1" title="Expiring soon"></i>@endif
                                        </span>
                                        @else <span class="text-slate-400">Permanent</span>@endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    @endif
                    {{-- Assigned courses & Class Teacher status --}}
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between text-sm font-semibold text-slate-700">
                            <span class="flex items-center gap-1.5"><i class="bi bi-journal-medical text-blue-500"></i>Assigned Subjects & Roles</span>
                            @can('assign teachers')
                            <button type="button" onclick="openAssignTeacherModal()" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-1">
                                <i class="bi bi-plus-circle-fill"></i> Assign New
                            </button>
                            @endcan
                        </div>
                        @if($teacher->assignedCourses->count())
                        <div class="divide-y divide-slate-50">
                            @foreach($teacher->assignedCourses->take(10) as $ac)
                            <div class="px-5 py-3 flex items-center justify-between text-sm gap-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="text-slate-800 font-semibold truncate">{{ $ac->course?->course_name ?? 'Subject' }}</span>
                                    @if($ac->is_class_teacher)
                                    <span class="text-[10px] bg-amber-100 text-amber-900 border border-amber-300 px-2 py-0.5 rounded-full font-bold flex items-center gap-1 flex-shrink-0 shadow-sm">
                                        <i class="bi bi-star-fill text-amber-500"></i> Class Teacher
                                    </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    @if($ac->schoolClass)
                                    <span class="text-[10px] bg-indigo-50 text-indigo-700 font-medium px-2 py-0.5 rounded-full">{{ $ac->schoolClass->class_name }}</span>
                                    @endif
                                    @if($ac->section)
                                    <span class="text-[10px] bg-slate-100 text-slate-600 font-medium px-2 py-0.5 rounded-full">Sec {{ $ac->section->section_name }}</span>
                                    @endif
                                    @can('assign teachers')
                                    <form action="{{ route('teacher.toggle-class-teacher', $ac->id) }}" method="POST" class="inline">
                                        @csrf
                                        @if($ac->is_class_teacher)
                                        <button type="submit" title="Remove Class Teacher Designation" class="text-[10px] bg-rose-50 hover:bg-rose-100 text-rose-600 px-2 py-0.5 rounded-full font-semibold transition">
                                            <i class="bi bi-x-circle me-1"></i>Unset
                                        </button>
                                        @else
                                        <button type="submit" title="Set as Class Teacher for this section" class="text-[10px] bg-amber-50 hover:bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-semibold transition border border-amber-200">
                                            <i class="bi bi-star me-1"></i>Make Class Teacher
                                        </button>
                                        @endif
                                    </form>
                                    @endcan
                                </div>
                            </div>
                            @endforeach
                            @if($teacher->assignedCourses->count() > 10)
                            <div class="px-5 py-2 text-xs text-slate-400">+{{ $teacher->assignedCourses->count() - 10 }} more subjects</div>
                            @endif
                        </div>
                        @else<p class="text-sm text-slate-400 text-center py-6">No subjects assigned yet.</p>@endif
                    </div>

                </div>
            </div>
        </div>

        {{-- TAB: TIMETABLE --}}
        <div id="tab-timetable" class="tab-panel hidden">
            @php
                $days = [1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday',0=>'Sunday'];
                $totalSlots = collect($timetableSlots)->flatten()->count();
            @endphp

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

                {{-- ── Left: Weekly timetable grid ── --}}
                <div class="xl:col-span-2 space-y-4">
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
                            <p class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                                <i class="bi bi-calendar3-week text-indigo-500"></i>
                                Weekly Schedule
                            </p>
                            <span class="text-xs bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-full font-medium">
                                {{ $totalSlots }} period{{ $totalSlots !== 1 ? 's' : '' }} total
                            </span>
                        </div>

                        @if($totalSlots > 0)
                        <div class="divide-y divide-slate-50">
                            @foreach($days as $dayNum => $dayName)
                                @if(isset($timetableSlots[$dayNum]) && $timetableSlots[$dayNum]->count())
                                <div class="px-5 py-3">
                                    <div class="flex items-center gap-2 mb-2.5">
                                        <span class="w-2 h-2 rounded-full bg-indigo-500 flex-shrink-0"></span>
                                        <p class="text-xs font-bold text-slate-600 uppercase tracking-wider">{{ $dayName }}</p>
                                        <span class="text-[10px] text-slate-400">{{ $timetableSlots[$dayNum]->count() }} period{{ $timetableSlots[$dayNum]->count() > 1 ? 's' : '' }}</span>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($timetableSlots[$dayNum]->sortBy('start') as $slot)
                                        @php
                                            $colors = ['bg-indigo-50 border-indigo-200 text-indigo-700','bg-emerald-50 border-emerald-200 text-emerald-700','bg-amber-50 border-amber-200 text-amber-700','bg-sky-50 border-sky-200 text-sky-700','bg-violet-50 border-violet-200 text-violet-700','bg-rose-50 border-rose-200 text-rose-700'];
                                            $color = $colors[$slot->course_id % count($colors)];
                                        @endphp
                                        <div class="flex-1 min-w-[160px] max-w-[220px] border rounded-xl p-3 {{ $color }}">
                                            <div class="flex items-start justify-between gap-2 mb-1">
                                                <p class="text-xs font-bold leading-tight">{{ $slot->course?->course_name ?? 'Free' }}</p>
                                                @can('create teachers')
                                                <form method="POST" action="{{ route('teacher.timetable.destroy', $slot->id) }}" class="flex-shrink-0">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" onclick="return confirm('Remove this slot?')"
                                                            class="text-[10px] opacity-50 hover:opacity-100 transition"
                                                            title="Remove slot">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                </form>
                                                @endcan
                                            </div>
                                            <p class="text-[10px] font-mono font-semibold opacity-80">{{ $slot->start }} – {{ $slot->end }}</p>
                                            <div class="flex items-center gap-1 mt-1 flex-wrap">
                                                <span class="text-[9px] font-semibold opacity-70">{{ $slot->schoolClass?->class_name ?? '' }}</span>
                                                @if($slot->section)<span class="text-[9px] opacity-50">· {{ $slot->section->section_name }}</span>@endif
                                                @if($slot->room)<span class="text-[9px] opacity-60 ml-auto"><i class="bi bi-door-open"></i> {{ $slot->room }}</span>@endif
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                        @else
                        <div class="py-14 text-center text-slate-400">
                            <i class="bi bi-calendar3-week text-4xl block mb-2 opacity-30"></i>
                            <p class="text-sm font-medium">No timetable slots assigned yet.</p>
                            <p class="text-xs mt-1 opacity-60">Use the form on the right to add periods.</p>
                        </div>
                        @endif
                    </div>

                    {{-- Summary table --}}
                    @if($totalSlots > 0)
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-5 py-3 border-b border-slate-100 text-sm font-semibold text-slate-700">
                            <i class="bi bi-table me-1 text-slate-400"></i> Period Summary
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-slate-50 text-xs text-slate-400 uppercase tracking-wide">
                                    <tr>
                                        <th class="px-4 py-2.5 text-left font-medium">Day</th>
                                        <th class="px-4 py-2.5 text-left font-medium">Subject</th>
                                        <th class="px-4 py-2.5 text-left font-medium">Class</th>
                                        <th class="px-4 py-2.5 text-left font-medium">Section</th>
                                        <th class="px-4 py-2.5 text-left font-medium">Time</th>
                                        <th class="px-4 py-2.5 text-left font-medium">Room</th>
                                        @can('create teachers')<th class="px-4 py-2.5"></th>@endcan
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    @foreach($days as $dayNum => $dayName)
                                        @if(isset($timetableSlots[$dayNum]))
                                        @foreach($timetableSlots[$dayNum]->sortBy('start') as $slot)
                                        <tr class="hover:bg-slate-50">
                                            <td class="px-4 py-2.5 font-medium text-slate-600">{{ $dayName }}</td>
                                            <td class="px-4 py-2.5 text-slate-700">{{ $slot->course?->course_name ?? '—' }}</td>
                                            <td class="px-4 py-2.5 text-slate-500">{{ $slot->schoolClass?->class_name ?? '—' }}</td>
                                            <td class="px-4 py-2.5 text-slate-500">{{ $slot->section?->section_name ?? '—' }}</td>
                                            <td class="px-4 py-2.5 font-mono text-xs text-slate-600">{{ $slot->start }} – {{ $slot->end }}</td>
                                            <td class="px-4 py-2.5 text-slate-400 text-xs">{{ $slot->room ?? '—' }}</td>
                                            @can('create teachers')
                                            <td class="px-4 py-2.5">
                                                <form method="POST" action="{{ route('teacher.timetable.destroy', $slot->id) }}">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" onclick="return confirm('Remove?')"
                                                            class="text-xs text-rose-400 hover:text-rose-600 transition">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                            @endcan
                                        </tr>
                                        @endforeach
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- ── Right: Add slot form (admin only) ── --}}
                @can('create teachers')
                <div class="xl:col-span-1">
                    <div class="sticky top-24 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-5 py-3.5 border-b border-slate-100 bg-indigo-50">
                            <p class="text-sm font-semibold text-indigo-700 flex items-center gap-2">
                                <i class="bi bi-plus-circle-fill"></i> Add Timetable Slot
                            </p>
                            <p class="text-xs text-indigo-500 mt-0.5">Assign a period to this teacher's weekly schedule</p>
                        </div>
                        <form method="POST" action="{{ route('teacher.timetable.store', $teacher->id) }}" class="p-5 space-y-3">
                            @csrf

                            @if($errors->has('conflict'))
                            <div class="p-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-lg text-xs flex items-start gap-2">
                                <i class="bi bi-exclamation-circle-fill flex-shrink-0 mt-0.5"></i>
                                {{ $errors->first('conflict') }}
                            </div>
                            @endif

                            {{-- Day --}}
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Day of Week *</label>
                                <select name="weekday" required
                                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                    @foreach($days as $num => $name)
                                    <option value="{{ $num }}" {{ old('weekday') == $num ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Time --}}
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500 mb-1">Start Time *</label>
                                    <input type="time" name="start" value="{{ old('start', '08:00') }}" required
                                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500 mb-1">End Time *</label>
                                    <input type="time" name="end" value="{{ old('end', '09:00') }}" required
                                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                </div>
                            </div>

                            {{-- Class --}}
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Class *</label>
                                <select name="class_id" id="tt_class_id" required
                                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                        onchange="loadCoursesForClass(this.value)">
                                    <option value="">— Select class —</option>
                                    @foreach($allClasses as $cls)
                                    <option value="{{ $cls->id }}" {{ old('class_id') == $cls->id ? 'selected' : '' }}>{{ $cls->class_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Section --}}
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Section *</label>
                                <select name="section_id" id="tt_section_id" required
                                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                    <option value="">— Select class first —</option>
                                    @foreach($allClasses as $cls)
                                        @foreach($cls->sections as $sec)
                                        <option value="{{ $sec->id }}" data-class="{{ $cls->id }}"
                                            {{ old('section_id') == $sec->id ? 'selected' : '' }}>
                                            {{ $cls->class_name }} · {{ $sec->section_name }}
                                        </option>
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>

                            {{-- Subject / Course --}}
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Subject *</label>
                                <select name="course_id" id="tt_course_id" required
                                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                    <option value="">— Select class first —</option>
                                    @foreach($allCourses as $c)
                                    <option value="{{ $c->id }}" data-class="{{ $c->class_id }}"
                                            {{ old('course_id') == $c->id ? 'selected' : '' }}>
                                        {{ $c->course_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Room (optional) --}}
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Room <span class="font-normal opacity-60">(optional)</span></label>
                                <input type="text" name="room" value="{{ old('room') }}" placeholder="e.g. Room 101"
                                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            </div>

                            <button type="submit"
                                    class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition flex items-center justify-center gap-2">
                                <i class="bi bi-plus-lg"></i> Add Slot
                            </button>
                        </form>
                    </div>
                </div>
                @endcan

            </div>{{-- /grid --}}
        </div>{{-- /tab-timetable --}}

        {{-- TAB: QUALIFICATIONS --}}
        <div id="tab-qualifications" class="tab-panel hidden">
            @can('create teachers')
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-5">
                <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-plus-lg me-1 text-indigo-500"></i>Add Qualification</p>
                <form method="POST" action="{{ route('teacher.qualifications.store', $teacher->id) }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Type</label>
                    <select name="type" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        @foreach(\App\Models\TeacherQualification::TYPES as $v=>$l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
                    </select></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Title *</label><input type="text" name="title" required placeholder="e.g. BSc Mathematics" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Institution *</label><input type="text" name="institution" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Field of Study</label><input type="text" name="field_of_study" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Start Year</label><input type="number" name="start_year" min="1950" max="{{ now()->year }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">End Year</label><input type="number" name="end_year" min="1950" max="{{ now()->year + 5 }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Grade / Result</label><input type="text" name="grade" placeholder="e.g. First Class, 3.8 GPA" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Attachment (optional)</label><input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm"></div>
                    <div class="md:col-span-2"><button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">Add Qualification</button></div>
                </form>
            </div>
            @endcan
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 text-sm font-semibold text-slate-700">Academic Qualifications ({{ $teacher->qualifications->count() }})</div>
                @if($teacher->qualifications->count())
                <div class="divide-y divide-slate-50">
                    @foreach($teacher->qualifications as $q)
                    <div class="px-5 py-4 flex items-start gap-3">
                        <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm flex-shrink-0"><i class="bi bi-mortarboard"></i></span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-700">{{ $q->title }}</p>
                            <p class="text-xs text-slate-500">{{ $q->institution }}@if($q->field_of_study) · {{ $q->field_of_study }}@endif</p>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $q->start_year ?? '' }} @if($q->end_year) – {{ $q->end_year }} @endif @if($q->grade) · {{ $q->grade }} @endif</p>
                            @if($q->attachment_path)<a href="{{ $q->attachment_url }}" target="_blank" class="text-xs text-indigo-600 hover:underline mt-0.5 inline-block"><i class="bi bi-paperclip me-1"></i>Certificate</a>@endif
                        </div>
                        <span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full flex-shrink-0">{{ \App\Models\TeacherQualification::TYPES[$q->type] ?? $q->type }}</span>
                        @can('create teachers')
                        <form method="POST" action="{{ route('teacher.qualifications.destroy', $q->id) }}">@csrf @method('DELETE')
                            <button class="text-xs text-rose-400 hover:text-rose-600 flex-shrink-0" onclick="return confirm('Remove?')"><i class="bi bi-trash"></i></button>
                        </form>
                        @endcan
                    </div>
                    @endforeach
                </div>
                @else<p class="text-sm text-slate-400 text-center py-10">No qualifications added.</p>@endif
            </div>
        </div>

        {{-- TAB: CONTRACTS --}}
        <div id="tab-contracts" class="tab-panel hidden">
            @can('create teachers')
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-5">
                <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-file-earmark-plus me-1 text-emerald-500"></i>New Contract</p>
                <form method="POST" action="{{ route('teacher.contracts.store', $teacher->id) }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Contract Type</label>
                    <select name="contract_type" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        @foreach(\App\Models\TeacherContract::TYPES as $v=>$l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
                    </select></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Position</label><input type="text" name="position" placeholder="e.g. Senior Teacher" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Start Date *</label><input type="date" name="start_date" required value="{{ now()->toDateString() }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">End Date</label><input type="date" name="end_date" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Basic Salary ($/month) *</label><input type="number" name="basic_salary" step="0.01" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Attachment</label><input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm"></div>
                    <div class="md:col-span-2"><label class="block text-xs font-medium text-slate-500 mb-1">Terms</label><textarea name="terms" rows="2" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-indigo-400"></textarea></div>
                    <div class="md:col-span-2"><button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium transition">Create Contract</button></div>
                </form>
            </div>
            @endcan
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 text-sm font-semibold text-slate-700">Contract History</div>
                @if($teacher->contracts->count())
                <div class="divide-y divide-slate-50">
                    @foreach($teacher->contracts as $c)
                    <div class="px-5 py-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <span class="text-sm font-semibold text-slate-700">{{ $c->position ?? \App\Models\TeacherContract::TYPES[$c->contract_type] }}</span>
                                    <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $c->status_badge }}">{{ ucfirst($c->status) }}</span>
                                    @if($c->is_expiring)<span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full"><i class="bi bi-exclamation-triangle me-1"></i>Expiring soon</span>@endif
                                    @if($c->is_expired)<span class="text-xs bg-rose-100 text-rose-700 px-2 py-0.5 rounded-full"><i class="bi bi-x-circle me-1"></i>Expired</span>@endif
                                </div>
                                <p class="text-xs text-slate-500">{{ $c->start_date->format('d M Y') }} – {{ $c->end_date?->format('d M Y') ?? 'Ongoing' }} · Rs. {{ number_format($c->basic_salary) }}/month</p>
                            </div>
                            @if($c->attachment_path)<a href="{{ \Illuminate\Support\Facades\Storage::url($c->attachment_path) }}" target="_blank" class="text-xs text-indigo-600 hover:underline flex-shrink-0">View</a>@endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @else<p class="text-sm text-slate-400 text-center py-10">No contracts on record.</p>@endif
            </div>
        </div>

        {{-- TAB: DOCUMENTS --}}
        <div id="tab-documents" class="tab-panel hidden">
            @can('create teachers')
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-5">
                <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-upload me-1 text-indigo-500"></i>Upload Document</p>
                <form method="POST" action="{{ route('teacher.documents.store', $teacher->id) }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Document Type</label>
                    <select name="document_type" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        @foreach(\App\Models\TeacherDocument::TYPES as $v=>$l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
                    </select></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Title *</label><input type="text" name="title" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div class="md:col-span-2"><label class="block text-xs font-medium text-slate-500 mb-1">File (PDF/JPG/PNG, max 10MB)</label><input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm"></div>
                    <div class="md:col-span-2"><button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">Upload</button></div>
                </form>
            </div>
            @endcan
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 text-sm font-semibold text-slate-700"><i class="bi bi-folder2-open me-1 text-amber-500"></i>Documents ({{ $teacher->teacherDocuments->count() }})</div>
                @if($teacher->teacherDocuments->count())
                <div class="divide-y divide-slate-50">
                    @foreach($teacher->teacherDocuments as $doc)
                    <div class="px-5 py-3 flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg {{ $doc->isPdf() ? 'bg-rose-50 text-rose-600' : 'bg-blue-50 text-blue-600' }} flex items-center justify-center text-sm flex-shrink-0">
                            <i class="bi {{ $doc->isPdf() ? 'bi-file-earmark-pdf' : 'bi-file-earmark-image' }}"></i>
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-700 truncate">{{ $doc->title }}</p>
                            <p class="text-xs text-slate-400">{{ \App\Models\TeacherDocument::TYPES[$doc->document_type] ?? $doc->document_type }} · {{ $doc->human_size }}</p>
                        </div>
                        @if($doc->is_verified)<span class="text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full flex-shrink-0"><i class="bi bi-patch-check-fill"></i> Verified</span>@endif
                        <div class="flex gap-2 flex-shrink-0">
                            <a href="{{ $doc->url }}" target="_blank" class="text-xs text-indigo-600 hover:underline">View</a>
                            @can('create teachers')
                            <form method="POST" action="{{ route('teacher.documents.verify', $doc->id) }}">@csrf<button class="text-xs text-slate-500 hover:text-slate-700">{{ $doc->is_verified ? 'Unverify' : 'Verify' }}</button></form>
                            <form method="POST" action="{{ route('teacher.documents.destroy', $doc->id) }}">@csrf @method('DELETE')<button class="text-xs text-rose-500 hover:text-rose-700" onclick="return confirm('Delete?')">Delete</button></form>
                            @endcan
                        </div>
                    </div>
                    @endforeach
                </div>
                @else<p class="text-sm text-slate-400 text-center py-10">No documents uploaded.</p>@endif
            </div>
        </div>

        {{-- TAB: LEAVE --}}
        <div id="tab-leave" class="tab-panel hidden">
            {{-- Leave balances --}}
            @php $balances = $teacher->leaveBalances()->with('leaveType')->get(); @endphp
            @if($balances->count())
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
                @foreach($balances as $b)
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
                    <p class="text-xs text-slate-400 mb-1">{{ $b->leaveType->name }}</p>
                    <p class="text-2xl font-bold text-indigo-600">{{ $b->remaining_days }}</p>
                    <p class="text-xs text-slate-400">of {{ $b->total_days }} days left</p>
                </div>
                @endforeach
            </div>
            @endif

            {{-- Apply for leave (teacher) or view all --}}
            @if(auth()->id() === $teacher->id || auth()->user()->can('create teachers'))
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-5">
                <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-calendar-plus me-1 text-indigo-500"></i>Apply for Leave</p>
                <form method="POST" action="{{ route('leave.apply') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Leave Type *</label>
                    <select name="leave_type_id" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        @foreach(\App\Models\LeaveType::where('is_active',true)->get() as $lt)
                        <option value="{{ $lt->id }}">{{ $lt->name }} ({{ $lt->days_allowed }} days/year)</option>
                        @endforeach
                    </select></div>
                    <div></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">From Date *</label><input type="date" name="from_date" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">To Date *</label><input type="date" name="to_date" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div class="md:col-span-2"><label class="block text-xs font-medium text-slate-500 mb-1">Reason *</label><textarea name="reason" rows="2" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-indigo-400"></textarea></div>
                    <div class="md:col-span-2"><button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">Submit Application</button></div>
                </form>
            </div>
            @endif

            {{-- Leave history --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 text-sm font-semibold text-slate-700">Leave History</div>
                @php $leaves = $teacher->leaveApplications()->with('leaveType','reviewer')->get(); @endphp
                @if($leaves->count())
                <div class="divide-y divide-slate-50">
                    @foreach($leaves as $la)
                    <div class="px-5 py-3 flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-0.5">
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $la->status_badge }}">{{ ucfirst($la->status) }}</span>
                                <span class="text-sm font-medium text-slate-700">{{ $la->leaveType->name }}</span>
                                <span class="text-xs text-slate-400">{{ $la->total_days }} day(s)</span>
                            </div>
                            <p class="text-xs text-slate-500">{{ $la->from_date->format('d M Y') }} – {{ $la->to_date->format('d M Y') }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">{{ \Illuminate\Support\Str::limit($la->reason, 80) }}</p>
                            @if($la->reviewer_notes)<p class="text-xs text-slate-400 mt-0.5 italic">Note: {{ $la->reviewer_notes }}</p>@endif
                        </div>
                        <div class="flex-shrink-0 text-right">
                            @if($la->status === 'pending')
                            @can('create teachers')
                            <form method="POST" action="{{ route('leave.review', $la->id) }}" class="inline-flex gap-1">
                                @csrf<input type="hidden" name="status" value="approved">
                                <button class="text-xs px-2 py-1 bg-emerald-100 text-emerald-700 hover:bg-emerald-200 rounded-lg transition">Approve</button>
                            </form>
                            <form method="POST" action="{{ route('leave.review', $la->id) }}" class="inline-flex gap-1">
                                @csrf<input type="hidden" name="status" value="rejected">
                                <button class="text-xs px-2 py-1 bg-rose-100 text-rose-700 hover:bg-rose-200 rounded-lg transition">Reject</button>
                            </form>
                            @endcan
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @else<p class="text-sm text-slate-400 text-center py-8">No leave applications.</p>@endif
            </div>
        </div>

        {{-- TAB: ATTENDANCE --}}
        <div id="tab-attendance" class="tab-panel hidden">
            @php
                $thisMonth = now()->month; $thisYear = now()->year;
                $monthRecords = $teacher->teacherAttendance()->whereMonth('date',$thisMonth)->whereYear('date',$thisYear)->get();
                $attSummary = ['present'=>$monthRecords->where('status','present')->count(),'absent'=>$monthRecords->where('status','absent')->count(),'late'=>$monthRecords->where('status','late')->count(),'on_leave'=>$monthRecords->where('status','on_leave')->count()];
                $total = $monthRecords->count(); $pct = $total > 0 ? round($attSummary['present']/$total*100) : 0;
            @endphp
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
                @foreach(['present'=>['emerald','Present'],'absent'=>['rose','Absent'],'late'=>['amber','Late'],'on_leave'=>['violet','On Leave']] as $s=>[$c,$l])
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
                    <p class="text-2xl font-bold text-{{ $c }}-600">{{ $attSummary[$s] }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $l }} ({{ now()->format('M Y') }})</p>
                </div>
                @endforeach
            </div>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-5">
                <p class="text-sm font-semibold text-slate-700 mb-3">Monthly Attendance Rate</p>
                <div class="h-3 bg-slate-100 rounded-full overflow-hidden mb-1">
                    <div class="h-full rounded-full {{ $pct >= 75 ? 'bg-emerald-500' : 'bg-rose-500' }}" style="width:{{ $pct }}%"></div>
                </div>
                <p class="text-xs text-slate-400">{{ $pct }}% present this month · <a href="{{ route('teacher.attendance.show', $teacher->id) }}" class="text-indigo-600 hover:underline">Full report</a></p>
            </div>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 text-sm font-semibold text-slate-700">Recent Attendance</div>
                @if($monthRecords->count())
                <div class="overflow-x-auto">
                    <table class="w-full text-sm"><thead><tr class="text-left text-xs text-slate-400 bg-slate-50">
                        <th class="px-5 py-2.5 font-medium">Date</th><th class="px-5 py-2.5 font-medium">Status</th>
                        <th class="px-5 py-2.5 font-medium">Check In</th><th class="px-5 py-2.5 font-medium">Check Out</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($monthRecords->sortByDesc('date')->take(10) as $rec)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-2.5 text-slate-600">{{ $rec->date->format('d M Y') }}</td>
                            <td class="px-5 py-2.5"><span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $rec->status_badge }}">{{ \App\Models\TeacherAttendance::STATUSES[$rec->status] ?? $rec->status }}</span></td>
                            <td class="px-5 py-2.5 text-slate-500 font-mono text-xs">{{ $rec->check_in ?? '—' }}</td>
                            <td class="px-5 py-2.5 text-slate-500 font-mono text-xs">{{ $rec->check_out ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody></table>
                </div>
                @else<p class="text-sm text-slate-400 text-center py-8">No attendance records this month.</p>@endif
            </div>
        </div>

        {{-- TAB: PAYROLL --}}
        <div id="tab-payroll" class="tab-panel hidden">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 flex justify-between items-center">
                    <p class="text-sm font-semibold text-slate-700"><i class="bi bi-cash-stack me-1 text-emerald-500"></i>Payroll History</p>
                    <a href="{{ route('teacher.payroll.index') }}" class="text-xs text-indigo-600 hover:underline">Manage Payroll</a>
                </div>
                @php $payrolls = $teacher->payrolls()->take(12)->get(); @endphp
                @if($payrolls->count())
                <div class="overflow-x-auto">
                    <table class="w-full text-sm"><thead><tr class="text-left text-xs text-slate-400 bg-slate-50">
                        <th class="px-5 py-2.5 font-medium">Period</th><th class="px-5 py-2.5 font-medium">Gross</th>
                        <th class="px-5 py-2.5 font-medium">Deductions</th><th class="px-5 py-2.5 font-medium">Net</th>
                        <th class="px-5 py-2.5 font-medium">Status</th><th class="px-5 py-2.5 font-medium"></th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($payrolls as $p)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-medium text-slate-700">{{ $p->month_name }} {{ $p->year }}</td>
                            <td class="px-5 py-3 text-slate-600">Rs. {{ number_format($p->gross_salary) }}</td>
                            <td class="px-5 py-3 text-rose-500">-Rs. {{ number_format($p->tax_deduction + $p->other_deductions) }}</td>
                            <td class="px-5 py-3 font-bold text-emerald-600">Rs. {{ number_format($p->net_salary) }}</td>
                            <td class="px-5 py-3"><span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $p->status_badge }}">{{ ucfirst($p->status) }}</span></td>
                            <td class="px-5 py-3"><a href="{{ route('teacher.payroll.slip', $p->id) }}" target="_blank" class="text-xs text-indigo-600 hover:underline">Slip</a></td>
                        </tr>
                        @endforeach
                    </tbody></table>
                </div>
                @else<p class="text-sm text-slate-400 text-center py-10">No payroll records.</p>@endif
            </div>
        </div>

        {{-- TAB: PERFORMANCE --}}
        <div id="tab-performance" class="tab-panel hidden">
            @can('create teachers')
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-5">
                <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-graph-up me-1 text-indigo-500"></i>New Review</p>
                <form method="POST" action="{{ route('teacher.reviews.store', $teacher->id) }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Review Period *</label><input type="text" name="review_period" required placeholder="e.g. 2025-Q1" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Review Date *</label><input type="date" name="review_date" required value="{{ now()->toDateString() }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    @foreach(['teaching_quality'=>'Teaching Quality','punctuality'=>'Punctuality','student_engagement'=>'Student Engagement','communication'=>'Communication','professionalism'=>'Professionalism'] as $field => $label)
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">{{ $label }} (1–5) *</label>
                        <select name="{{ $field }}" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            @foreach([5=>'5 - Excellent',4=>'4 - Good',3=>'3 - Average',2=>'2 - Below Average',1=>'1 - Poor'] as $v=>$l)
                            <option value="{{ $v }}">{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endforeach
                    @foreach(['strengths'=>'Strengths','areas_for_improvement'=>'Areas for Improvement','goals'=>'Goals','reviewer_comments'=>'Comments'] as $f=>$l)
                    <div class="md:col-span-2"><label class="block text-xs font-medium text-slate-500 mb-1">{{ $l }}</label><textarea name="{{ $f }}" rows="2" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-indigo-400"></textarea></div>
                    @endforeach
                    <div class="md:col-span-2"><button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">Submit Review</button></div>
                </form>
            </div>
            @endcan
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 text-sm font-semibold text-slate-700">Performance Reviews ({{ $teacher->performanceReviews->count() }})</div>
                @if($teacher->performanceReviews->count())
                <div class="divide-y divide-slate-50">
                    @foreach($teacher->performanceReviews as $rev)
                    <div class="px-5 py-4">
                        <div class="flex items-start justify-between gap-3 mb-2">
                            <div>
                                <p class="text-sm font-semibold text-slate-700">{{ $rev->review_period }}</p>
                                <p class="text-xs text-slate-400">{{ $rev->review_date->format('d M Y') }} · By {{ $rev->reviewer?->full_name ?? '—' }}</p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-2xl font-bold {{ $rev->rating_color }}">{{ $rev->overall_rating }}</p>
                                <p class="text-xs text-slate-400">/5 overall</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-5 gap-1 mb-2">
                            @foreach(['teaching_quality','punctuality','student_engagement','communication','professionalism'] as $field)
                            <div class="text-center">
                                <div class="h-10 bg-slate-100 rounded flex items-end overflow-hidden">
                                    <div class="w-full bg-indigo-500 rounded" style="height:{{ ($rev->$field ?? 0) * 20 }}%"></div>
                                </div>
                                <p class="text-[9px] text-slate-400 mt-0.5 truncate">{{ ucwords(str_replace('_',' ',$field)) }}</p>
                            </div>
                            @endforeach
                        </div>
                        @if($rev->strengths)<p class="text-xs text-slate-600"><span class="font-medium text-slate-700">Strengths:</span> {{ \Illuminate\Support\Str::limit($rev->strengths, 100) }}</p>@endif
                        @if($rev->areas_for_improvement)<p class="text-xs text-slate-500 mt-0.5"><span class="font-medium text-slate-600">Improve:</span> {{ \Illuminate\Support\Str::limit($rev->areas_for_improvement, 100) }}</p>@endif
                    </div>
                    @endforeach
                </div>
                @else<p class="text-sm text-slate-400 text-center py-10">No performance reviews yet.</p>@endif
            </div>
        </div>

        {{-- TAB: TRAINING --}}
        <div id="tab-training" class="tab-panel hidden">
            @can('create teachers')
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-5">
                <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-book me-1 text-blue-500"></i>Add Training Record</p>
                <form method="POST" action="{{ route('teacher.training.store', $teacher->id) }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf
                    <div class="md:col-span-2"><label class="block text-xs font-medium text-slate-500 mb-1">Title *</label><input type="text" name="title" required placeholder="e.g. Modern Teaching Methods Workshop" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Type</label>
                    <select name="type" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        @foreach(\App\Models\TeacherTraining::TYPES as $v=>$l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
                    </select></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Organizer</label><input type="text" name="organizer" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">From Date *</label><input type="date" name="from_date" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">To Date</label><input type="date" name="to_date" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Hours</label><input type="number" name="hours" min="0" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Certificate No.</label><input type="text" name="certificate_no" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div class="md:col-span-2"><button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition">Add Record</button></div>
                </form>
            </div>
            @endcan
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 text-sm font-semibold text-slate-700">Training Records ({{ $teacher->trainingRecords->count() }})</div>
                @if($teacher->trainingRecords->count())
                <div class="divide-y divide-slate-50">
                    @foreach($teacher->trainingRecords as $tr)
                    <div class="px-5 py-3 flex items-start gap-3">
                        <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm flex-shrink-0"><i class="bi bi-journal-bookmark"></i></span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-700">{{ $tr->title }}</p>
                            <p class="text-xs text-slate-400">{{ \App\Models\TeacherTraining::TYPES[$tr->type] ?? $tr->type }}@if($tr->organizer) · {{ $tr->organizer }}@endif</p>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $tr->from_date->format('d M Y') }} @if($tr->to_date) – {{ $tr->to_date->format('d M Y') }} @endif @if($tr->hours) · {{ $tr->hours }}h @endif</p>
                        </div>
                        @can('create teachers')
                        <form method="POST" action="{{ route('teacher.training.destroy', $tr->id) }}">@csrf @method('DELETE')
                            <button class="text-xs text-rose-400 hover:text-rose-600" onclick="return confirm('Remove?')"><i class="bi bi-trash"></i></button>
                        </form>
                        @endcan
                    </div>
                    @endforeach
                </div>
                @else<p class="text-sm text-slate-400 text-center py-10">No training records.</p>@endif
            </div>
        </div>

    </div>{{-- /content --}}
</div>{{-- /flex --}}

{{-- ── Modal: Assign Subject & Class Teacher ── --}}
<div id="assignTeacherModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-100 relative animate-in fade-in zoom-in duration-200">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <i class="bi bi-person-badge-fill text-indigo-600"></i> Assign Subject & Class Teacher
            </h3>
            <button type="button" onclick="closeAssignTeacherModal()" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>
        <form action="{{ route('teacher.assign') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">
            <input type="hidden" name="session_id" value="{{ $sessionId ?? 1 }}">
            <input type="hidden" name="semester_id" value="1">
            
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Select Class *</label>
                <select name="class_id" id="assign_class_select" onchange="loadAssignSectionsAndCourses(this.value)" required class="w-full text-xs p-2.5 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- Select Class --</option>
                    @if(isset($allClasses))
                    @foreach($allClasses as $c)
                    <option value="{{ $c->id }}">{{ $c->class_name }}</option>
                    @endforeach
                    @endif
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Select Section *</label>
                <select name="section_id" id="assign_section_select" required class="w-full text-xs p-2.5 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- Select Class First --</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Select Subject (Course) *</label>
                <select name="course_id" id="assign_course_select" required class="w-full text-xs p-2.5 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- Select Class First --</option>
                </select>
            </div>

            <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-xl shadow-xs">
                <label class="flex items-center gap-2.5 cursor-pointer">
                    <input type="checkbox" name="is_class_teacher" value="1" class="w-4 h-4 text-amber-600 rounded border-amber-300 focus:ring-amber-500">
                    <span class="text-xs font-bold text-amber-900 flex items-center gap-1.5">
                        <i class="bi bi-star-fill text-amber-500"></i> Designate as Class Teacher
                    </span>
                </label>
                <p class="text-[11px] text-amber-700 mt-1 leading-tight">Checking this will make {{ $teacher->full_name }} the official primary Class Teacher for this section.</p>
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeAssignTeacherModal()" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold hover:bg-slate-200 transition">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold hover:bg-indigo-700 transition shadow-sm">Assign Teacher</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showTab(id) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(b => { b.classList.remove('bg-indigo-50','text-indigo-700','font-semibold'); b.classList.add('text-slate-500'); });
    var panel = document.getElementById('tab-' + id);
    var btn = document.getElementById('tab-btn-' + id);
    if (panel) panel.classList.remove('hidden');
    if (btn) { btn.classList.add('bg-indigo-50','text-indigo-700','font-semibold'); btn.classList.remove('text-slate-500'); }
    history.replaceState(null,'','#' + id);
}
(function(){ showTab(location.hash.replace('#','') || 'overview'); })();

// ── Assign Teacher Modal Logic ──
function openAssignTeacherModal() {
    document.getElementById('assignTeacherModal').classList.remove('hidden');
}
function closeAssignTeacherModal() {
    document.getElementById('assignTeacherModal').classList.add('hidden');
}

function loadAssignSectionsAndCourses(classId) {
    const sectionSel = document.getElementById('assign_section_select');
    const courseSel  = document.getElementById('assign_course_select');
    if (!sectionSel || !courseSel || !classId) return;

    // Use tt_section_id options as reference if available
    const ttSectionSel = document.getElementById('tt_section_id');
    const ttCourseSel  = document.getElementById('tt_course_id');

    sectionSel.innerHTML = '<option value="">-- Select Section --</option>';
    courseSel.innerHTML = '<option value="">-- Select Subject --</option>';

    if (ttSectionSel) {
        Array.from(ttSectionSel.options).forEach(opt => {
            if (opt.value && opt.dataset.class == classId) {
                const newOpt = opt.cloneNode(true);
                newOpt.hidden = false;
                sectionSel.appendChild(newOpt);
            }
        });
    }

    if (ttCourseSel) {
        Array.from(ttCourseSel.options).forEach(opt => {
            if (opt.value && opt.dataset.class == classId) {
                const newOpt = opt.cloneNode(true);
                newOpt.hidden = false;
                courseSel.appendChild(newOpt);
            }
        });
    }
}

// ── Timetable form: cascade class → sections & courses ───────────────
function loadCoursesForClass(classId) {
    const sectionSel = document.getElementById('tt_section_id');
    const courseSel  = document.getElementById('tt_course_id');
    if (!sectionSel || !courseSel) return;

    // Filter sections
    Array.from(sectionSel.options).forEach(opt => {
        if (!opt.value) return; // keep placeholder
        opt.hidden = opt.dataset.class != classId;
    });
    sectionSel.value = '';

    // Filter courses
    Array.from(courseSel.options).forEach(opt => {
        if (!opt.value) return;
        opt.hidden = opt.dataset.class != classId;
    });
    courseSel.value = '';
}

// Run on page load to filter to any previously-selected class (e.g. after validation error)
(function() {
    const classEl = document.getElementById('tt_class_id');
    if (classEl && classEl.value) loadCoursesForClass(classEl.value);
})();
</script>
@endpush

