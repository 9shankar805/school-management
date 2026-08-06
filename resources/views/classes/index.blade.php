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

    {{-- Filter Search Bar --}}
    <div class="mb-6 flex items-center justify-between gap-4 bg-white dark:bg-slate-900 p-3 rounded-2xl border border-slate-200/80 dark:border-slate-800">
        <div class="relative flex-1 max-w-md">
            <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <input type="text" id="classSearchInput" onkeyup="filterClassCards()" placeholder="Search class name, sections..." class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
        </div>
        <span class="text-xs text-slate-400 font-medium px-2">Showing {{ $totalClassesCount }} Grade Classes</span>
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
                    <div class="p-5 border-b border-slate-100 dark:border-slate-800/80 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50/50 dark:bg-slate-800/30">
                        <div class="flex items-center gap-3">
                            <span class="w-12 h-12 rounded-2xl bg-indigo-600 text-white font-black text-lg flex items-center justify-center shadow-xs">
                                {{ substr($school_class->class_name, 0, 2) }}
                            </span>
                            <div>
                                <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100 leading-tight">
                                    {{ $school_class->class_name }}
                                </h2>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300">
                                        <i class="bi bi-grid-fill me-1 text-[10px]"></i> {{ $classSections->count() }} Sections
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300">
                                        <i class="bi bi-people-fill me-1 text-[10px]"></i> {{ $classStudentCount }} Students
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300">
                                        <i class="bi bi-journal-text me-1 text-[10px]"></i> {{ $classCourses->count() }} Subjects
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            @can('create sections')
                            <button onclick="openAddSectionModal('{{ $school_class->id }}', '{{ $school_class->class_name }}')" class="px-3.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 text-slate-700 dark:text-slate-200 text-xs font-semibold transition flex items-center gap-1.5">
                                <i class="bi bi-plus text-sm"></i> Add Section
                            </button>
                            @endcan

                            @can('edit classes')
                            <a href="{{ route('class.detail.show', $school_class->id) }}" class="px-3.5 py-1.5 rounded-xl border border-indigo-200 dark:border-indigo-800 bg-indigo-50 dark:bg-indigo-900/30 hover:bg-indigo-100 text-indigo-700 dark:text-indigo-300 text-xs font-semibold transition flex items-center gap-1.5">
                                <i class="bi bi-eye"></i> Details
                            </a>
                            <a href="{{ route('class.edit', ['id' => $school_class->id]) }}" class="px-3.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 text-slate-700 dark:text-slate-200 text-xs font-semibold transition flex items-center gap-1.5">
                                <i class="bi bi-pencil text-xs"></i> Edit Class
                            </a>
                            @endcan

                            @can('delete classes')
                            <form action="{{ route('school.class.delete', ['id' => $school_class->id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete class {{ $school_class->class_name }}? All related sections will be removed.');">
                                @csrf
                                <button type="submit" class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition">
                                    <i class="bi bi-trash3 text-xs"></i>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </div>

                    {{-- Class Tabs Navigation --}}
                    <div class="px-6 pt-3 border-b border-slate-100 dark:border-slate-800 flex items-center gap-6">
                        <button onclick="switchClassTab('{{ $school_class->id }}', 'sections')" id="tab-btn-{{ $school_class->id }}-sections" class="class-tab-btn py-2 text-xs font-bold text-indigo-600 border-b-2 border-indigo-600 transition flex items-center gap-1.5">
                            <i class="bi bi-grid-3x3-gap-fill"></i> Sections ({{ $classSections->count() }})
                        </button>
                        <button onclick="switchClassTab('{{ $school_class->id }}', 'courses')" id="tab-btn-{{ $school_class->id }}-courses" class="class-tab-btn py-2 text-xs font-semibold text-slate-400 hover:text-slate-600 border-b-2 border-transparent transition flex items-center gap-1.5">
                            <i class="bi bi-journal-bookmark-fill"></i> Courses / Subjects ({{ $classCourses->count() }})
                        </button>
                        <button onclick="switchClassTab('{{ $school_class->id }}', 'syllabi')" id="tab-btn-{{ $school_class->id }}-syllabi" class="class-tab-btn py-2 text-xs font-semibold text-slate-400 hover:text-slate-600 border-b-2 border-transparent transition flex items-center gap-1.5">
                            <i class="bi bi-file-earmark-text-fill"></i> Syllabi ({{ $classSyllabi->count() }})
                        </button>
                    </div>

                    {{-- Tab Content Bodies --}}
                    <div class="p-6">
                        
                        {{-- Sections Grid --}}
                        <div id="tab-content-{{ $school_class->id }}-sections" class="class-tab-content">
                            @if($classSections->count() > 0)
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach ($classSections as $section)
                                    @php
                                        $secStudentCount = $section->promotions ? $section->promotions->count() : 0;
                                    @endphp
                                    <div class="p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/20 hover:border-indigo-200 dark:hover:border-indigo-900 transition-all flex flex-col justify-between group">
                                        <div>
                                            <div class="flex items-center justify-between mb-3">
                                                <div class="flex items-center gap-2.5">
                                                    <span class="w-9 h-9 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 font-bold text-sm flex items-center justify-center shadow-2xs">
                                                        {{ $section->section_name }}
                                                    </span>
                                                    <div>
                                                        <h4 class="text-xs font-bold text-slate-800 dark:text-slate-100">Section {{ $section->section_name }}</h4>
                                                        <span class="text-[11px] text-slate-400 flex items-center gap-1">
                                                            <i class="bi bi-geo-alt"></i> Room: {{ $section->room_no ?? 'N/A' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                                    {{ $secStudentCount }} Students
                                                </span>
                                            </div>
                                        </div>

                                        <div class="pt-3 border-t border-slate-200/60 dark:border-slate-800 flex items-center justify-between gap-2">
                                            <div class="flex items-center gap-1.5">
                                                <a href="{{ route('student.list.show', ['class_id' => $school_class->id, 'section_id' => $section->id, 'section_name' => $section->section_name]) }}" class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-[11px] font-medium hover:bg-slate-100 transition">
                                                    <i class="bi bi-people me-1"></i> Students
                                                </a>
                                                <a href="{{ route('section.routine.show', ['class_id' => $school_class->id, 'section_id' => $section->id]) }}" class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-[11px] font-medium hover:bg-slate-100 transition">
                                                    <i class="bi bi-calendar-event me-1"></i> Timetable
                                                </a>
                                            </div>

                                            <div class="flex items-center gap-1">
                                                @can('edit sections')
                                                <a href="{{ route('section.edit', ['id' => $section->id]) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-white dark:hover:bg-slate-800 transition" title="Edit Section">
                                                    <i class="bi bi-pencil text-xs"></i>
                                                </a>
                                                @endcan

                                                @can('delete sections')
                                                <form action="{{ route('school.section.delete', ['id' => $section->id]) }}" method="POST" onsubmit="return confirm('Delete Section {{ $section->section_name }}?');">
                                                    @csrf
                                                    <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition" title="Delete Section">
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

{{-- MODAL: Add New Class --}}
<div id="addClassModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in-95">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/40">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm">
                    <i class="bi bi-building-plus"></i>
                </span>
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Add New Class</h3>
            </div>
            <button onclick="closeModal('addClassModal')" class="text-slate-400 hover:text-slate-600 text-lg leading-none">&times;</button>
        </div>

        <form action="{{ route('school.class.create') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="session_id" value="{{ $current_school_session_id }}">

            <div>
                <label for="modal_class_name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    Class / Grade Name <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="modal_class_name" name="class_name" placeholder="e.g. Class 10, Grade 1, Nursery" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
            </div>

            <div>
                <label for="modal_initial_sections" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    Initial Sections <span class="text-slate-400 font-normal">(Comma separated, e.g. A, B, C)</span>
                </label>
                <input type="text" id="modal_initial_sections" name="initial_sections_str" placeholder="A, B, C" value="A, B" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
            </div>

            <div>
                <label for="modal_default_room" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    Default Room Number
                </label>
                <input type="text" id="modal_default_room" name="default_room_no" placeholder="e.g. 101" value="101" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('addClassModal')" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 shadow-md">Save Class</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL: Add New Section --}}
<div id="addSectionModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in-95">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/40">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm">
                    <i class="bi bi-grid-plus"></i>
                </span>
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Add New Section</h3>
            </div>
            <button onclick="closeModal('addSectionModal')" class="text-slate-400 hover:text-slate-600 text-lg leading-none">&times;</button>
        </div>

        <form action="{{ route('school.section.create') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="session_id" value="{{ $current_school_session_id }}">

            <div>
                <label for="sec_modal_class_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    Select Class <span class="text-rose-500">*</span>
                </label>
                <select id="sec_modal_class_id" name="class_id" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                    @isset($school_classes)
                        @foreach ($school_classes as $sc)
                            <option value="{{ $sc->id }}">{{ $sc->class_name }}</option>
                        @endforeach
                    @endisset
                </select>
            </div>

            <div>
                <label for="sec_modal_name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    Section Name <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="sec_modal_name" name="section_name" placeholder="e.g. A, B, Red, Blue" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
            </div>

            <div>
                <label for="sec_modal_room" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    Room Number <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="sec_modal_room" name="room_no" placeholder="e.g. Room 102" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('addSectionModal')" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 shadow-md">Create Section</button>
            </div>
        </form>
    </div>
</div>

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
</script>
@endsection
