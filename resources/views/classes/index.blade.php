@extends('layouts.app')

@section('content')
<div class="px-4 lg:px-8 py-6 max-w-7xl mx-auto">

    {{-- Page Header & Actions --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <span class="w-10 h-10 rounded-2xl bg-indigo-600/10 text-indigo-600 flex items-center justify-center font-bold text-xl shadow-xs">
                    <i class="bi bi-diagram-3-fill"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Classes & Sections</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Manage academic grades, section allocations, courses, and timetables</p>
                </div>
            </div>
        </div>
        
        <div class="flex items-center gap-2.5">
            @can('create sections')
            <button onclick="openModal('addSectionModal')" class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 text-xs font-semibold shadow-xs transition flex items-center gap-2">
                <i class="bi bi-plus-lg text-indigo-600"></i> Add Section
            </button>
            @endcan

            @can('create classes')
            <button onclick="openModal('addClassModal')" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-lg shadow-indigo-600/25 hover:shadow-indigo-600/35 transition-all flex items-center gap-2">
                <i class="bi bi-plus-circle text-sm"></i> Add New Class
            </button>
            @endcan
        </div>
    </div>

    @include('session-messages')

    @php
        $totalClassesCount = count($school_classes ?? []);
        $totalSectionsCount = count($school_sections ?? []);
        $totalEnrolledStudents = 0;
        $totalCoursesCount = 0;

        if (isset($school_classes)) {
            foreach ($school_classes as $cls) {
                $totalEnrolledStudents += $cls->promotions ? $cls->promotions->count() : 0;
                $totalCoursesCount += $cls->courses ? $cls->courses->count() : 0;
            }
        }
    @endphp

    {{-- Executive Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg flex-shrink-0">
                <i class="bi bi-building"></i>
            </div>
            <div>
                <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Total Classes</p>
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 leading-none mt-0.5">{{ $totalClassesCount }}</h3>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg flex-shrink-0">
                <i class="bi bi-grid-3x3-gap"></i>
            </div>
            <div>
                <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Total Sections</p>
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 leading-none mt-0.5">{{ $totalSectionsCount }}</h3>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center text-lg flex-shrink-0">
                <i class="bi bi-people"></i>
            </div>
            <div>
                <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Enrolled Students</p>
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 leading-none mt-0.5">{{ $totalEnrolledStudents }}</h3>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg flex-shrink-0">
                <i class="bi bi-journal-bookmark"></i>
            </div>
            <div>
                <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Active Subjects</p>
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 leading-none mt-0.5">{{ $totalCoursesCount }}</h3>
            </div>
        </div>
    </div>

    {{-- Filter Search & Bulk Actions Bar --}}
    <div class="mb-6 space-y-3">
        <div class="flex items-center justify-between gap-4 bg-white dark:bg-slate-900 p-3 rounded-2xl border border-slate-200/80 dark:border-slate-800">
            <div class="relative flex-1 max-w-md">
                <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" id="classSearchInput" onkeyup="filterClassCards()" placeholder="Search class name, sections..." class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
            </div>
            <div class="flex items-center gap-3">
                <label class="flex items-center gap-2 text-xs font-semibold text-slate-600 dark:text-slate-300 cursor-pointer select-none">
                    <input type="checkbox" id="selectAllClassesCb" onchange="toggleSelectAllClasses(this)" class="rounded text-rose-600 focus:ring-rose-500 w-4 h-4 cursor-pointer">
                    <span>सबै कक्षा छान्नुहोस् (Select All)</span>
                </label>
                <span class="text-xs text-slate-400 font-medium px-2 border-l border-slate-200 dark:border-slate-700">Total: {{ $totalClassesCount }} Grades</span>
            </div>
        </div>

        {{-- Class Bulk Delete Action Banner (hidden by default, appears on selection) --}}
        <form id="bulkDeleteClassesForm" action="{{ route('school.class.bulk.delete') }}" method="POST" onsubmit="return confirm('के तपाईं साँच्चै छानिएका सबै कक्षाहरू र तिनीहरूका सेक्सनहरू मेटाउन चाहनुहुन्छ? (Are you sure you want to delete all selected classes and their sections?');" class="hidden">
            @csrf
            <div class="p-3.5 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 flex items-center justify-between gap-4 shadow-sm animate-in fade-in">
                <div class="flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-xl bg-rose-600 text-white flex items-center justify-center font-bold text-xs">
                        <i class="bi bi-check2-all"></i>
                    </span>
                    <span class="text-xs font-bold text-rose-800 dark:text-rose-200">
                        <span id="selectedClassesCount">0</span> कक्षा(हरू) छानियो (Classes Selected)
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="clearAllClassSelections()" class="px-3 py-1.5 rounded-xl border border-rose-200 dark:border-rose-800 bg-white dark:bg-slate-800 text-rose-700 dark:text-rose-300 text-xs font-semibold hover:bg-rose-50">
                        रद्द (Clear)
                    </button>
                    <button type="submit" class="px-4 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-sm transition flex items-center gap-1.5 cursor-pointer">
                        <i class="bi bi-trash3-fill"></i> छानिएका कक्षा Delete गर्नुहोस् (Delete Selected)
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Class Cards List --}}
    <div class="space-y-6" id="classCardsContainer">
        @isset($school_classes)
            @forelse ($school_classes as $school_class)
                @php
                    $classSections = $school_class->sections ?? collect();
                    $classCourses = $school_class->courses ?? collect();
                    $classSyllabi = $school_class->syllabi ?? collect();
                    $classStudentCount = $school_class->promotions ? $school_class->promotions->count() : 0;
                @endphp

                <div class="class-card bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden transition-all hover:shadow-md" data-name="{{ strtolower($school_class->class_name) }}">
                    
                    {{-- Card Header --}}
                    <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50/60 dark:bg-slate-800/40">
                        <div class="flex items-center gap-3">
                            {{-- Class Selection Checkbox --}}
                            <label class="cursor-pointer select-none p-1" title="Select this class">
                                <input type="checkbox" form="bulkDeleteClassesForm" name="class_ids[]" value="{{ $school_class->id }}" class="class-bulk-checkbox rounded text-rose-600 focus:ring-rose-500 w-4 h-4 cursor-pointer" onchange="updateClassBulkUI()">
                            </label>

                            <span class="w-11 h-11 rounded-2xl bg-indigo-600 text-white font-black text-lg flex items-center justify-center shadow-xs">
                                {{ substr($school_class->class_name, 0, 2) }}
                            </span>
                            <div>
                                <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100 leading-tight">
                                    {{ $school_class->class_name }}
                                </h2>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-900/60">
                                        <i class="bi bi-grid-fill me-1 text-[10px]"></i> {{ $classSections->count() }} Sections
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-100 dark:border-blue-900/60">
                                        <i class="bi bi-people-fill me-1 text-[10px]"></i> {{ $classStudentCount }} Students
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-100 dark:border-amber-900/60">
                                        <i class="bi bi-journal-text me-1 text-[10px]"></i> {{ $classCourses->count() }} Subjects
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            @can('create sections')
                            <button type="button" onclick="openAddSectionModal('{{ $school_class->id }}', '{{ $school_class->class_name }}')" class="px-3.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                                <i class="bi bi-plus text-sm text-indigo-600 dark:text-indigo-400"></i> Add Section
                            </button>
                            @endcan

                            @can('edit classes')
                            <a href="{{ route('class.detail.show', $school_class->id) }}" class="px-3.5 py-1.5 rounded-xl border border-indigo-200 dark:border-indigo-800 bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 text-xs font-semibold transition flex items-center gap-1.5 shadow-xs">
                                <i class="bi bi-eye"></i> Details
                            </a>
                            <a href="{{ route('class.edit', ['id' => $school_class->id]) }}" class="px-3.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold transition flex items-center gap-1.5 shadow-xs">
                                <i class="bi bi-pencil text-xs"></i> Edit Class
                            </a>
                            @endcan

                            @can('delete classes')
                            <form action="{{ route('school.class.delete', ['id' => $school_class->id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete class {{ $school_class->class_name }}? All related sections will be removed.');" class="inline m-0">
                                @csrf
                                <button type="submit" class="w-8 h-8 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 hover:border-rose-300 dark:hover:border-rose-800 transition flex items-center justify-center cursor-pointer shadow-xs" style="min-height: unset; padding: 0;" title="Delete Class">
                                <i class="bi bi-trash3 text-xs"></i>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </div>

                    {{-- Class Tabs Navigation --}}
                    <div class="px-6 pt-3 border-b border-slate-100 dark:border-slate-800 flex items-center gap-6">
                        <button onclick="switchClassTab('{{ $school_class->id }}', 'sections')" id="tab-btn-{{ $school_class->id }}-sections" class="class-tab-btn py-2 text-xs font-bold text-indigo-600 dark:text-indigo-400 border-b-2 border-indigo-600 dark:border-indigo-400 transition flex items-center gap-1.5">
                            <i class="bi bi-grid-3x3-gap-fill"></i> Sections ({{ $classSections->count() }})
                        </button>
                        <button onclick="switchClassTab('{{ $school_class->id }}', 'courses')" id="tab-btn-{{ $school_class->id }}-courses" class="class-tab-btn py-2 text-xs font-semibold text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 border-b-2 border-transparent transition flex items-center gap-1.5">
                            <i class="bi bi-journal-bookmark-fill"></i> Courses / Subjects ({{ $classCourses->count() }})
                        </button>
                        <button onclick="switchClassTab('{{ $school_class->id }}', 'syllabi')" id="tab-btn-{{ $school_class->id }}-syllabi" class="class-tab-btn py-2 text-xs font-semibold text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 border-b-2 border-transparent transition flex items-center gap-1.5">
                            <i class="bi bi-file-earmark-text-fill"></i> Syllabi ({{ $classSyllabi->count() }})
                        </button>
                    </div>

                    {{-- Tab Content Bodies --}}
                    <div class="p-6">
                        
                        {{-- Sections Grid --}}
                        <div id="tab-content-{{ $school_class->id }}-sections" class="class-tab-content">
                            @if($classSections->count() > 0)
                            
                            {{-- Section Bulk Controls Inside Class --}}
                            <form id="bulkDeleteSecForm-{{ $school_class->id }}" action="{{ route('school.section.bulk.delete') }}" method="POST" onsubmit="return confirm('Delete selected sections in {{ $school_class->class_name }}?');" class="mb-3 flex items-center justify-between gap-2 p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-700/60">
                                @csrf
                                <label class="flex items-center gap-1.5 text-[11px] font-semibold text-slate-600 dark:text-slate-300 cursor-pointer select-none">
                                    <input type="checkbox" onchange="toggleSelectAllClassSecs('{{ $school_class->id }}', this)" class="rounded text-rose-600 focus:ring-rose-500 w-3.5 h-3.5 cursor-pointer">
                                    <span>सबै सेक्सन छान्नुहोस् (Select All)</span>
                                </label>
                                
                                <button type="submit" id="bulkSecBtn-{{ $school_class->id }}" class="hidden px-3 py-1 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-[11px] font-bold shadow-xs transition items-center gap-1 cursor-pointer">
                                    <i class="bi bi-trash3-fill"></i> छानिएका सेक्सन Delete गर्नुहोस् (<span id="bulkSecCount-{{ $school_class->id }}">0</span>)
                                </button>
                            </form>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach ($classSections as $section)
                                    @php
                                        $secStudentCount = $section->promotions ? $section->promotions->count() : 0;
                                        $cleanSecName = preg_replace('/^Section\s+/i', '', trim($section->section_name));
                                        $cleanRoomNo  = preg_replace('/^Room:\s*|^Room\s+/i', '', trim($section->room_no ?? ''));
                                    @endphp
                                    <div class="p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-800/40 hover:border-indigo-300 dark:hover:border-indigo-800 transition-all flex flex-col justify-between group shadow-xs relative">
                                        <div>
                                            <div class="flex items-center justify-between mb-3">
                                                <div class="flex items-center gap-2.5">
                                                    {{-- Section Checkbox --}}
                                                    <input type="checkbox" form="bulkDeleteSecForm-{{ $school_class->id }}" name="section_ids[]" value="{{ $section->id }}" class="sec-bulk-cb-{{ $school_class->id }} rounded text-rose-600 focus:ring-rose-500 w-3.5 h-3.5 cursor-pointer" onchange="updateSecBulkUI('{{ $school_class->id }}')">

                                                    <span class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-100 dark:border-indigo-900/50 text-indigo-700 dark:text-indigo-300 font-extrabold text-sm flex items-center justify-center shadow-2xs">
                                                        {{ $cleanSecName }}
                                                    </span>
                                                    <div>
                                                        <h4 class="text-xs font-extrabold text-slate-800 dark:text-slate-100">Section {{ $cleanSecName }}</h4>
                                                        <span class="text-[11px] text-slate-400 font-medium flex items-center gap-1">
                                                            <i class="bi bi-geo-alt text-slate-400"></i> Room: {{ $cleanRoomNo ?: 'N/A' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/60">
                                                    {{ $secStudentCount }} Students
                                                </span>
                                            </div>
                                        </div>

                                        <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2">
                                            <div class="flex items-center gap-1.5">
                                                <a href="{{ route('student.list.show', ['class_id' => $school_class->id, 'section_id' => $section->id, 'section_name' => $section->section_name]) }}" class="px-2.5 py-1 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-[11px] font-medium hover:bg-slate-100 dark:hover:bg-slate-700 transition shadow-2xs">
                                                    <i class="bi bi-people me-1"></i> Students
                                                </a>
                                                <a href="{{ route('section.routine.show', ['class_id' => $school_class->id, 'section_id' => $section->id]) }}" class="px-2.5 py-1 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-[11px] font-medium hover:bg-slate-100 dark:hover:bg-slate-700 transition shadow-2xs">
                                                    <i class="bi bi-calendar-event me-1"></i> Timetable
                                                </a>
                                            </div>

                                            <div class="flex items-center gap-1">
                                                @can('edit sections')
                                                <a href="{{ route('section.edit', ['id' => $section->id]) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-slate-100 dark:hover:bg-slate-700/60 transition flex items-center justify-center" title="Edit Section">
                                                    <i class="bi bi-pencil text-xs"></i>
                                                </a>
                                                @endcan

                                                @can('delete sections')
                                                <form action="{{ route('school.section.delete', ['id' => $section->id]) }}" method="POST" onsubmit="return confirm('Delete Section {{ $cleanSecName }}?');" class="inline m-0">
                                                    @csrf
                                                    <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition border-0 bg-transparent flex items-center justify-center cursor-pointer" style="min-height: unset; padding: 0.375rem;" title="Delete Section">
                                                        <i class="bi bi-trash3 text-xs"></i>
                                                    </button>
                                                </form>
                                                @endcan
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @else
                            <div class="py-8 text-center bg-slate-50/50 dark:bg-slate-800/20 rounded-2xl border border-dashed border-slate-200 dark:border-slate-700">
                                <i class="bi bi-grid-3x3-gap text-3xl text-slate-300 dark:text-slate-600 mb-1 block"></i>
                                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">No sections added to {{ $school_class->class_name }} yet.</p>
                                @can('create sections')
                                <button onclick="openAddSectionModal('{{ $school_class->id }}', '{{ $school_class->class_name }}')" class="mt-2 text-xs font-bold text-indigo-600 hover:underline">
                                    + Add First Section
                                </button>
                                @endcan
                            </div>
                            @endif
                        </div>

                        {{-- Courses / Subjects List --}}
                        <div id="tab-content-{{ $school_class->id }}-courses" class="class-tab-content hidden">
                            @if($classCourses->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs">
                                    <thead>
                                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-semibold">
                                            <th class="py-2.5 px-3">Subject / Course Name</th>
                                            <th class="py-2.5 px-3">Course Code / Type</th>
                                            <th class="py-2.5 px-3 text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                        @foreach ($classCourses as $course)
                                        <tr>
                                            <td class="py-3 px-3 font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                                <i class="bi bi-book text-indigo-500"></i> {{ $course->course_name }}
                                            </td>
                                            <td class="py-3 px-3 text-slate-500">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-mono">
                                                    {{ $course->course_type ?? 'Theory' }}
                                                </span>
                                            </td>
                                            <td class="py-3 px-3 text-right">
                                                @can('edit courses')
                                                <a href="{{ route('course.edit', ['id' => $course->id]) }}" class="px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-100 text-[11px] font-medium transition">
                                                    <i class="bi bi-pencil me-1"></i> Edit
                                                </a>
                                                @endcan
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <p class="text-xs text-slate-400 py-6 text-center">No subjects mapped to {{ $school_class->class_name }}.</p>
                            @endif
                        </div>

                        {{-- Syllabi List --}}
                        <div id="tab-content-{{ $school_class->id }}-syllabi" class="class-tab-content hidden">
                            @if($classSyllabi->count() > 0)
                            <div class="space-y-2">
                                @foreach ($classSyllabi as $syllabus)
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/70 dark:border-slate-700 flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <i class="bi bi-file-earmark-pdf text-rose-500 text-base"></i>
                                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ $syllabus->syllabus_name }}</span>
                                    </div>
                                    <a href="{{ asset('storage/'.$syllabus->syllabus_file_path) }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 text-xs font-semibold transition flex items-center gap-1">
                                        <i class="bi bi-download"></i> Download
                                    </a>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <p class="text-xs text-slate-400 py-6 text-center">No syllabus documents uploaded.</p>
                            @endif
                        </div>

                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-12 text-center border border-slate-200 dark:border-slate-800">
                    <i class="bi bi-building text-4xl text-slate-300 dark:text-slate-600 mb-2 block"></i>
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">No Grade Classes Found</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 mb-4">Start by adding academic classes to organize students into grades and sections.</p>
                    @can('create classes')
                    <button onclick="openModal('addClassModal')" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-lg transition">
                        + Add First Class
                    </button>
                    @endcan
                </div>
            @endforelse
        @endisset
    </div>

    @include('layouts.footer')
</div>

{{-- MODAL: Add New Class (Supports Single & Multiple Classes) --}}
<div id="addClassModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in-95">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/40">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm">
                    <i class="bi bi-building-plus"></i>
                </span>
                <div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Add Academic Classes</h3>
                    <p class="text-[10px] text-slate-400">एकल वा एकैपटक धेरै कक्षाहरू सिर्जना गर्नुहोस्</p>
                </div>
            </div>
            <button onclick="closeModal('addClassModal')" class="text-slate-400 hover:text-slate-600 text-lg leading-none">&times;</button>
        </div>

        <form action="{{ route('school.class.create') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="session_id" value="{{ $current_school_session_id }}">

            {{-- Mode Switcher Inside Modal --}}
            <div class="flex p-1 bg-slate-100 dark:bg-slate-800 rounded-xl text-xs font-semibold">
                <button type="button" onclick="setModalClassMode('multiple')" id="modal-tab-multi" class="flex-1 py-1.5 rounded-lg bg-white dark:bg-slate-700 text-indigo-600 shadow-xs">
                    <i class="bi bi-collection"></i> धेरै कक्षाहरू (Multiple)
                </button>
                <button type="button" onclick="setModalClassMode('single')" id="modal-tab-single" class="flex-1 py-1.5 rounded-lg text-slate-600 dark:text-slate-400">
                    <i class="bi bi-plus-square"></i> एकल कक्षा (Single)
                </button>
            </div>

            {{-- MULTIPLE CLASSES VIEW --}}
            <div id="modal-panel-multi" class="space-y-3">
                {{-- Quick Presets Dropdown/Pills --}}
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                        १-क्लिक संरचना छान्नुहोस् (Quick Presets):
                    </label>
                    <div class="flex flex-wrap gap-1.5 text-[10px]">
                        <button type="button" onclick="fillModalClasses('Playgroup, Nursery, LKG, UKG, Class 1, Class 2, Class 3, Class 4, Class 5, Class 6, Class 7, Class 8, Class 9, Class 10')" class="px-2 py-1 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded-lg border border-indigo-200 font-semibold">
                            PG to 10 (मावि)
                        </button>
                        <button type="button" onclick="fillModalClasses('Playgroup, Nursery, LKG, UKG, Class 1, Class 2, Class 3, Class 4, Class 5, Class 6, Class 7, Class 8, Class 9, Class 10, Class 11, Class 12')" class="px-2 py-1 bg-purple-50 text-purple-700 hover:bg-purple-100 rounded-lg border border-purple-200 font-semibold">
                            PG to 12 (उच्च मावि)
                        </button>
                        <button type="button" onclick="fillModalClasses('Class 1, Class 2, Class 3, Class 4, Class 5, Class 6, Class 7, Class 8')" class="px-2 py-1 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg border border-emerald-200 font-semibold">
                            Class 1 to 8 (आधारभूत)
                        </button>
                        <button type="button" onclick="fillModalClasses('ECD, Nursery, Class 1, Class 2, Class 3, Class 4, Class 5')" class="px-2 py-1 bg-amber-50 text-amber-700 hover:bg-amber-100 rounded-lg border border-amber-200 font-semibold">
                            ECD to 5 (प्रावि)
                        </button>
                    </div>
                </div>

                <div>
                    <label for="modal_bulk_classes" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        कक्षाहरूको नामावली (कमा वा लाइन अनुसार)
                    </label>
                    <textarea id="modal_bulk_classes" name="bulk_class_names" rows="3" placeholder="Playgroup, Nursery, LKG, UKG, Class 1, Class 2, Class 3..."
                              class="w-full text-xs font-medium p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">Playgroup, Nursery, LKG, UKG, Class 1, Class 2, Class 3, Class 4, Class 5, Class 6, Class 7, Class 8, Class 9, Class 10</textarea>
                </div>
            </div>

            {{-- SINGLE CLASS VIEW --}}
            <div id="modal-panel-single" class="space-y-3 hidden">
                <div>
                    <label for="modal_class_name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Class / Grade Name <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="modal_class_name" name="class_name" placeholder="e.g. Class 10, Grade 1, Nursery" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                </div>
            </div>

            {{-- Common Settings --}}
            <div class="grid grid-cols-2 gap-3 pt-1">
                <div>
                    <label for="modal_initial_sections" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Initial Sections <span class="text-slate-400 font-normal">(e.g. A, B)</span>
                    </label>
                    <input type="text" id="modal_initial_sections" name="initial_sections_str" placeholder="A, B, C" value="A, B" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                </div>
                <div>
                    <label for="modal_default_room" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Starting Room No <span class="text-[10px] text-emerald-600 font-normal">(103 → 104, 105...)</span>
                    </label>
                    <input type="text" id="modal_default_room" name="default_room_no" placeholder="e.g. 101 or 103" value="101" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none font-semibold">
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('addClassModal')" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-700">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 text-white text-xs font-bold hover:bg-indigo-700 shadow-md">Create Classes</button>
            </div>
        </form>
    </div>
</div>

<script>
    function setModalClassMode(mode) {
        const multiPanel = document.getElementById('modal-panel-multi');
        const singlePanel = document.getElementById('modal-panel-single');
        const tabMulti = document.getElementById('modal-tab-multi');
        const tabSingle = document.getElementById('modal-tab-single');
        const singleInput = document.getElementById('modal_class_name');

        if (mode === 'multiple') {
            multiPanel.classList.remove('hidden');
            singlePanel.classList.add('hidden');
            tabMulti.className = 'flex-1 py-1.5 rounded-lg bg-white dark:bg-slate-700 text-indigo-600 shadow-xs font-bold';
            tabSingle.className = 'flex-1 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 font-semibold';
            singleInput.removeAttribute('required');
        } else {
            multiPanel.classList.add('hidden');
            singlePanel.classList.remove('hidden');
            tabSingle.className = 'flex-1 py-1.5 rounded-lg bg-white dark:bg-slate-700 text-indigo-600 shadow-xs font-bold';
            tabMulti.className = 'flex-1 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 font-semibold';
            singleInput.setAttribute('required', 'required');
            singleInput.focus();
        }
    }

    function fillModalClasses(names) {
        document.getElementById('modal_bulk_classes').value = names;
    }
</script>

{{-- MODAL: Add New Section (Supports Single Class, Specific Multiple Classes e.g. 2, 7, or All Classes with A, B...) --}}
<div id="addSectionModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full max-w-xl overflow-hidden animate-in fade-in zoom-in-95">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/40">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm">
                    <i class="bi bi-grid-plus"></i>
                </span>
                <div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Add Academic Section(s)</h3>
                    <p class="text-[10px] text-slate-400">तोकिएका कक्षाहरू (उदा: Class 2, 7) वा सबै कक्षामा एकैपटक सेक्सनहरू थप्नुहोस्</p>
                </div>
            </div>
            <button onclick="closeModal('addSectionModal')" class="text-slate-400 hover:text-slate-600 text-lg leading-none">&times;</button>
        </div>

        <form action="{{ route('school.section.create') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="session_id" value="{{ $current_school_session_id }}">

            {{-- Mode Switcher Inside Modal --}}
            <div class="flex p-1 bg-slate-100 dark:bg-slate-800 rounded-xl text-xs font-semibold">
                <button type="button" onclick="setSecClassSelectionMode('multi')" id="sec-mode-multi-btn" class="flex-1 py-1.5 rounded-lg bg-white dark:bg-slate-700 text-emerald-600 shadow-xs font-bold">
                    <i class="bi bi-check2-square"></i> छानिएका कक्षाहरू (Select Classes: e.g. 2, 7)
                </button>
                <button type="button" onclick="setSecClassSelectionMode('single')" id="sec-mode-single-btn" class="flex-1 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 font-semibold">
                    <i class="bi bi-card-list"></i> एकल कक्षा (Single Class)
                </button>
            </div>

            {{-- 1. SPECIFIC MULTI-CLASS SELECTION GRID --}}
            <div id="sec-multi-classes-box" class="space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                        कक्षाहरू छान्नुहोस् (Select Target Classes) <span class="text-rose-500">*</span>:
                    </label>
                    <div class="flex items-center gap-2 text-[10px]">
                        <button type="button" onclick="selectAllSecClasses(true)" class="text-indigo-600 font-bold hover:underline">
                            सबै छान्नुहोस् (All)
                        </button>
                        <span class="text-slate-300">|</span>
                        <button type="button" onclick="selectAllSecClasses(false)" class="text-slate-400 font-semibold hover:underline">
                            हटाउनुहोस् (Clear)
                        </button>
                    </div>
                </div>

                {{-- Interactive Checkbox Chips Grid --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 max-h-44 overflow-y-auto p-2 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-700">
                    @isset($school_classes)
                        @foreach ($school_classes as $sc)
                            <label class="sec-class-chip p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-emerald-400 flex items-center gap-2 cursor-pointer transition select-none">
                                <input type="checkbox" name="class_ids[]" value="{{ $sc->id }}" class="sec-class-checkbox rounded text-emerald-600 focus:ring-emerald-500 w-3.5 h-3.5" onchange="updateSecChipStyle(this)">
                                <span class="text-xs font-semibold text-slate-700 dark:text-slate-200 truncate">{{ $sc->class_name }}</span>
                            </label>
                        @endforeach
                    @endisset
                </div>
            </div>

            {{-- 2. SINGLE CLASS SELECTION DROPDOWN --}}
            <div id="sec-single-class-box" class="space-y-1 hidden">
                <label for="sec_modal_class_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                    कक्षा छान्नुहोस् (Select Single Class) <span class="text-rose-500">*</span>
                </label>
                <select id="sec_modal_class_id" name="class_id" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                    <option value="" disabled selected>— कक्षा छान्नुहोस् —</option>
                    @isset($school_classes)
                        @foreach ($school_classes as $sc)
                            <option value="{{ $sc->id }}">{{ $sc->class_name }}</option>
                        @endforeach
                    @endisset
                </select>
            </div>

            {{-- Section Name with Quick Chips --}}
            <div>
                <label for="sec_modal_name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    बनाउने सेक्सनहरू (Sections to Create) <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="sec_modal_name" name="section_name" placeholder="e.g. A, B or Red, Green" value="A, B" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none font-bold text-emerald-700">
                
                {{-- Quick Preset Chips --}}
                <div class="flex flex-wrap gap-1.5 mt-2 text-[10px]">
                    <span class="text-slate-400 font-semibold self-center mr-1">द्रुत छनौट:</span>
                    <button type="button" onclick="document.getElementById('sec_modal_name').value='A, B'" class="px-2 py-0.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg border border-emerald-200 font-bold">
                        + A, B
                    </button>
                    <button type="button" onclick="document.getElementById('sec_modal_name').value='A, B, C'" class="px-2 py-0.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg border border-emerald-200 font-bold">
                        + A, B, C
                    </button>
                    <button type="button" onclick="document.getElementById('sec_modal_name').value='A, B, C, D'" class="px-2 py-0.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg border border-emerald-200 font-bold">
                        + A, B, C, D
                    </button>
                    <button type="button" onclick="document.getElementById('sec_modal_name').value='Red, Green, Blue'" class="px-2 py-0.5 bg-purple-50 text-purple-700 hover:bg-purple-100 rounded-lg border border-purple-200 font-bold">
                        + Red, Green, Blue
                    </button>
                    <button type="button" onclick="document.getElementById('sec_modal_name').value='Morning, Day'" class="px-2 py-0.5 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded-lg border border-indigo-200 font-bold">
                        + Morning, Day
                    </button>
                </div>
            </div>

            <div>
                <label for="sec_modal_room" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    सुरुवाती कोठा नम्बर (Starting Room No) <span class="text-[10px] text-emerald-600 font-normal">✨ (उदा: 103 राखेमा 103, 104, 105... स्वतः बढ्नेछ)</span>
                </label>
                <input type="text" id="sec_modal_room" name="room_no" placeholder="e.g. 101 or 103" value="101" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none font-semibold">
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('addSectionModal')" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-700">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 shadow-md">Create Section(s)</button>
            </div>
        </form>
    </div>
</div>

<script>
    function setSecClassSelectionMode(mode) {
        const multiBox = document.getElementById('sec-multi-classes-box');
        const singleBox = document.getElementById('sec-single-class-box');
        const multiBtn = document.getElementById('sec-mode-multi-btn');
        const singleBtn = document.getElementById('sec-mode-single-btn');
        const singleSelect = document.getElementById('sec_modal_class_id');

        if (mode === 'multi') {
            multiBox.classList.remove('hidden');
            singleBox.classList.add('hidden');
            multiBtn.className = 'flex-1 py-1.5 rounded-lg bg-white dark:bg-slate-700 text-emerald-600 shadow-xs font-bold';
            singleBtn.className = 'flex-1 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 font-semibold';
            singleSelect.removeAttribute('required');
        } else {
            multiBox.classList.add('hidden');
            singleBox.classList.remove('hidden');
            singleBtn.className = 'flex-1 py-1.5 rounded-lg bg-white dark:bg-slate-700 text-emerald-600 shadow-xs font-bold';
            multiBtn.className = 'flex-1 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 font-semibold';
            singleSelect.setAttribute('required', 'required');
        }
    }

    function selectAllSecClasses(checked) {
        document.querySelectorAll('.sec-class-checkbox').forEach(cb => {
            cb.checked = checked;
            updateSecChipStyle(cb);
        });
    }

    function updateSecChipStyle(cb) {
        const parent = cb.closest('.sec-class-chip');
        if (cb.checked) {
            parent.classList.add('border-emerald-500', 'bg-emerald-50/60', 'dark:bg-emerald-950/30', 'font-bold');
        } else {
            parent.classList.remove('border-emerald-500', 'bg-emerald-50/60', 'dark:bg-emerald-950/30', 'font-bold');
        }
    }
</script>

<script>
function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}

function openAddSectionModal(classId, className) {
    const select = document.getElementById('sec_modal_class_id');
    if (select) {
        select.value = classId;
    }
    openModal('addSectionModal');
}

function switchClassTab(classId, tabName) {
    // Hide all tab contents for this class
    document.querySelectorAll('#classCardsContainer #tab-content-' + classId + '-sections, #classCardsContainer #tab-content-' + classId + '-courses, #classCardsContainer #tab-content-' + classId + '-syllabi').forEach(el => el.classList.add('hidden'));

    // Reset button styles
    const tabBtns = document.querySelectorAll('#classCardsContainer [id^="tab-btn-' + classId + '-"]');
    tabBtns.forEach(btn => {
        btn.classList.remove('text-indigo-600', 'border-indigo-600', 'font-bold');
        btn.classList.add('text-slate-400', 'border-transparent', 'font-semibold');
    });

    // Show target content
    const targetContent = document.getElementById('tab-content-' + classId + '-' + tabName);
    if (targetContent) targetContent.classList.remove('hidden');

    // Highlight target button
    const targetBtn = document.getElementById('tab-btn-' + classId + '-' + tabName);
    if (targetBtn) {
        targetBtn.classList.remove('text-slate-400', 'border-transparent', 'font-semibold');
        targetBtn.classList.add('text-indigo-600', 'border-indigo-600', 'font-bold');
    }
}

function filterClassCards() {
    const input = document.getElementById('classSearchInput').value.toLowerCase();
    const cards = document.querySelectorAll('.class-card');
    cards.forEach(card => {
        const name = card.getAttribute('data-name') || '';
        const text = card.innerText.toLowerCase();
        if (name.includes(input) || text.includes(input)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

// ── Bulk Class Selection Handlers ─────────────────────────────────
function updateClassBulkUI() {
    const checkboxes = document.querySelectorAll('.class-bulk-checkbox:checked');
    const count = checkboxes.length;
    const banner = document.getElementById('bulkDeleteClassesForm');
    const countSpan = document.getElementById('selectedClassesCount');
    const selectAllCb = document.getElementById('selectAllClassesCb');

    if (countSpan) countSpan.innerText = count;

    if (count > 0) {
        if (banner) banner.classList.remove('hidden');
    } else {
        if (banner) banner.classList.add('hidden');
    }

    const allCheckboxes = document.querySelectorAll('.class-bulk-checkbox');
    if (selectAllCb && allCheckboxes.length > 0) {
        selectAllCb.checked = (checkboxes.length === allCheckboxes.length);
    }
}

function toggleSelectAllClasses(masterCb) {
    const isChecked = masterCb.checked;
    document.querySelectorAll('.class-bulk-checkbox').forEach(cb => {
        cb.checked = isChecked;
    });
    updateClassBulkUI();
}

function clearAllClassSelections() {
    document.querySelectorAll('.class-bulk-checkbox').forEach(cb => {
        cb.checked = false;
    });
    const masterCb = document.getElementById('selectAllClassesCb');
    if (masterCb) masterCb.checked = false;
    updateClassBulkUI();
}

// ── Bulk Section Selection Handlers Inside a Class ─────────────────
function updateSecBulkUI(classId) {
    const checkboxes = document.querySelectorAll('.sec-bulk-cb-' + classId + ':checked');
    const count = checkboxes.length;
    const btn = document.getElementById('bulkSecBtn-' + classId);
    const countSpan = document.getElementById('bulkSecCount-' + classId);

    if (countSpan) countSpan.innerText = count;

    if (count > 0) {
        if (btn) {
            btn.classList.remove('hidden');
            btn.classList.add('flex');
        }
    } else {
        if (btn) {
            btn.classList.remove('flex');
            btn.classList.add('hidden');
        }
    }
}

function toggleSelectAllClassSecs(classId, masterCb) {
    const isChecked = masterCb.checked;
    document.querySelectorAll('.sec-bulk-cb-' + classId).forEach(cb => {
        cb.checked = isChecked;
    });
    updateSecBulkUI(classId);
}
</script>
@endsection
