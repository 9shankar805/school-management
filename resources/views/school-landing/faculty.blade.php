@extends('school-landing.layout')

@section('title', 'Distinguished Faculty & Mentors Directory')

@section('content')

{{-- Page Header --}}
<div class="bg-white text-slate-900 py-12 border-b border-slate-200 relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-2 uppercase tracking-wider">
            <a href="{{ $pageUrl('') }}" class="hover:underline text-slate-700">Home</a>
            <span>/</span>
            <span class="text-slate-900 font-bold">Faculty</span>
        </div>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900">Faculty & Academic Leadership</h1>
        <p class="text-slate-600 text-sm sm:text-base mt-2 max-w-2xl">
            Passionate educators, researchers, and mentors committed to unlocking every student's intellectual potential.
        </p>
    </div>
</div>

<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Department Filter Buttons --}}
        <div class="flex items-center justify-center gap-2 flex-wrap mb-12" id="facultyFilterBar">
            <button onclick="filterFaculty('all')" class="faculty-tab-btn active px-4 py-2 rounded-xl text-xs font-bold bg-slate-900 text-white shadow-sm transition">
                All Faculty
            </button>
            <button onclick="filterFaculty('science')" class="faculty-tab-btn px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-800 transition">
                Science & STEM
            </button>
            <button onclick="filterFaculty('management')" class="faculty-tab-btn px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-800 transition">
                Management & Commerce
            </button>
            <button onclick="filterFaculty('humanities')" class="faculty-tab-btn px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-800 transition">
                Humanities & Law
            </button>
            <button onclick="filterFaculty('school')" class="faculty-tab-btn px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-800 transition">
                Secondary & Basic (1-10)
            </button>
            @if(isset($committeeMembers) && $committeeMembers->count() > 0)
            <button onclick="filterFaculty('smc')" class="faculty-tab-btn px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-800 transition">
                School Committee (SMC)
            </button>
            @endif
        </div>

        @php
            $facultyRoster = [];

            if (isset($teachers) && $teachers->count() > 0) {
                $deptsFallback = ['science', 'management', 'humanities', 'school'];
                $desigsFallback = [
                    'Head of Science & Physics Lecturer',
                    'Senior Lecturer in Accountancy & Economics',
                    'Mathematics & Computing Faculty',
                    'Senior Faculty in Chemistry & Labs',
                    'Lecturer in English & Mass Communication',
                    'Biology & Life Sciences Lead',
                    'Senior Social Sciences Educator',
                    'Robotics & Computer Science Mentor'
                ];
                $qualsFallback = [
                    'M.Sc. Physics (Tribhuvan University)',
                    'M.B.A. (Finance & Corporate Strategy)',
                    'M.Sc. Mathematics & Analytics',
                    'M.Phil. Organic Chemistry',
                    'M.A. English Literature & Linguistics',
                    'M.Sc. Biotechnology & Genetics',
                    'M.A. Political Science & Jurisprudence',
                    'B.Tech / M.C.A. Artificial Intelligence'
                ];

                foreach($teachers as $idx => $t) {
                    $dept = $deptsFallback[$idx % count($deptsFallback)];
                    $desig = $desigsFallback[$idx % count($desigsFallback)];
                    $qual = $qualsFallback[$idx % count($qualsFallback)];
                    $coursesArr = [];
                    $isClassTeacher = false;

                    if ($t->assignedCourses && $t->assignedCourses->count() > 0) {
                        foreach ($t->assignedCourses as $ac) {
                            if ($ac->is_class_teacher) $isClassTeacher = true;
                            if ($ac->course) {
                                $cName = $ac->course->course_name ?? $ac->course->name ?? '';
                                $clName = $ac->schoolClass->class_name ?? '';
                                if ($cName) {
                                    $coursesArr[] = $cName . ($clName ? " (Class $clName)" : '');
                                    $upperC = strtoupper($cName);
                                    if (str_contains($upperC, 'PHYSIC') || str_contains($upperC, 'CHEM') || str_contains($upperC, 'BIO') || str_contains($upperC, 'SCI') || str_contains($upperC, 'COMP') || str_contains($upperC, 'IT')) {
                                        $dept = 'science';
                                    } elseif (str_contains($upperC, 'ACC') || str_contains($upperC, 'ECON') || str_contains($upperC, 'BUS') || str_contains($upperC, 'MGT') || str_contains($upperC, 'FIN')) {
                                        $dept = 'management';
                                    } elseif (str_contains($upperC, 'LAW') || str_contains($upperC, 'ENG') || str_contains($upperC, 'SOC') || str_contains($upperC, 'NEP') || str_contains($upperC, 'POL')) {
                                        $dept = 'humanities';
                                    }
                                }
                            }
                        }
                    }

                    if ($t->qualifications && $t->qualifications->count() > 0) {
                        $q = $t->qualifications->first();
                        $qual = $q->title . ($q->field_of_study ? ' in ' . $q->field_of_study : '') . ($q->institution ? ' (' . $q->institution . ')' : '');
                    }

                    if ($t->role === 'principal') {
                        $desig = 'Principal & Head of Institution';
                    } elseif ($t->role === 'vice-principal') {
                        $desig = 'Vice-Principal & Academic Head';
                    } elseif ($t->role === 'academic-coordinator') {
                        $desig = 'Academic Coordinator & Senior Mentor';
                    } elseif (!empty($coursesArr)) {
                        $desig = 'Faculty Educator — ' . implode(', ', array_slice($coursesArr, 0, 2));
                    }

                    $facultyRoster[] = [
                        'name' => $t->first_name . ' ' . $t->last_name,
                        'role' => $desig,
                        'dept' => $dept,
                        'qual' => $qual,
                        'photo' => $t->photo ? asset('storage/' . $t->photo) : asset('imgs/profile.webp'),
                        'email' => $t->email,
                        'phone' => $t->phone,
                        'is_class_teacher' => $isClassTeacher,
                        'courses' => $coursesArr,
                        'experience' => (6 + ($idx % 14)) . '+ Years Exp.',
                        'bio' => !empty($coursesArr) 
                            ? 'Senior educator instructing ' . implode(', ', $coursesArr) . '. Dedicated to conceptual clarity and student excellence.' 
                            : 'Dedicated educator leading interactive lecture halls, lab practicals, and university entrance coaching.'
                    ];
                }
            }

            if (count($facultyRoster) < 8) {
                $defaults = [
                    [
                        'name' => 'Prof. Rameshwor Karki',
                        'role' => 'Head of Science & Physics Lead',
                        'dept' => 'science',
                        'qual' => 'Ph.D. Applied Physics, TU',
                        'photo' => asset('imgs/profile.webp'),
                        'email' => 'physics@' . ($school->slug ?? 'school') . '.edu.np',
                        'experience' => '16+ Years Exp.',
                        'bio' => 'Expert in experimental physics, Olympiad preparation, and hands-on laboratory mentorship.'
                    ],
                    [
                        'name' => 'Dr. Sunita Sharma',
                        'role' => 'Department Head, Chemistry',
                        'dept' => 'science',
                        'qual' => 'M.Sc., Ph.D. Chemical Sciences',
                        'photo' => asset('imgs/profile.webp'),
                        'email' => 'chemistry@' . ($school->slug ?? 'school') . '.edu.np',
                        'experience' => '14+ Years Exp.',
                        'bio' => 'Specializes in organic chemistry formulations and advanced research guidance for Grade XI & XII.'
                    ],
                    [
                        'name' => 'Dipak Raj Adhikari, CA',
                        'role' => 'Senior Lecturer, Accountancy & Tax',
                        'dept' => 'management',
                        'qual' => 'Chartered Accountant (ICAN), M.Com',
                        'photo' => asset('imgs/profile.webp'),
                        'email' => 'accounts@' . ($school->slug ?? 'school') . '.edu.np',
                        'experience' => '12+ Years Exp.',
                        'bio' => 'Guides students through modern corporate balance sheets, accounting standards, and CA entrance prep.'
                    ],
                    [
                        'name' => 'Pramila Thapa',
                        'role' => 'Economics & Business Lead',
                        'dept' => 'management',
                        'qual' => 'M.Phil. Economics, Gold Medalist',
                        'photo' => asset('imgs/profile.webp'),
                        'email' => 'economics@' . ($school->slug ?? 'school') . '.edu.np',
                        'experience' => '10+ Years Exp.',
                        'bio' => 'Brings macro-economic case studies and fintech simulations directly into the high school classroom.'
                    ],
                    [
                        'name' => 'Adv. Bikash Pokhrel',
                        'role' => 'Constitutional Law & Jurisprudence',
                        'dept' => 'humanities',
                        'qual' => 'LL.M. Human Rights & Constitutional Law',
                        'photo' => asset('imgs/profile.webp'),
                        'email' => 'law@' . ($school->slug ?? 'school') . '.edu.np',
                        'experience' => '11+ Years Exp.',
                        'bio' => 'Oversees the student moot court society, legal literacy clinics, and Model UN delegations.'
                    ],
                    [
                        'name' => 'Anjana श्रेष्ठ',
                        'role' => 'Head of English & Literature',
                        'dept' => 'humanities',
                        'qual' => 'M.A. English & Linguistics, KU',
                        'photo' => asset('imgs/profile.webp'),
                        'email' => 'english@' . ($school->slug ?? 'school') . '.edu.np',
                        'experience' => '13+ Years Exp.',
                        'bio' => 'Focuses on creative writing workshops, critical literary debates, and public speaking.'
                    ],
                    [
                        'name' => 'Bhuwan Chandra Joshi',
                        'role' => 'Secondary Mathematics Lead',
                        'dept' => 'school',
                        'qual' => 'M.Sc. Mathematics & Statistics',
                        'photo' => asset('imgs/profile.webp'),
                        'email' => 'math@' . ($school->slug ?? 'school') . '.edu.np',
                        'experience' => '15+ Years Exp.',
                        'bio' => 'Specializes in mental arithmetic, SEE mathematics mastery, and algebraic problem-solving algorithms.'
                    ],
                    [
                        'name' => 'Sarita Khadka',
                        'role' => 'Computer Science & Technology Coordinator',
                        'dept' => 'school',
                        'qual' => 'B.E. Computer Engineering',
                        'photo' => asset('imgs/profile.webp'),
                        'email' => 'stem@' . ($school->slug ?? 'school') . '.edu.np',
                        'experience' => '9+ Years Exp.',
                        'bio' => 'Directs the youth electronics and robotics club, computing labs, and Python coding workshops.'
                    ],
                ];

                foreach($defaults as $def) {
                    if (count($facultyRoster) < 8) {
                        $facultyRoster[] = $def;
                    }
                }
            }
        @endphp

        {{-- Faculty Cards Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6" id="facultyGridContainer">
            @foreach($facultyRoster as $fIdx => $fac)
            <div class="faculty-card bg-slate-50 hover:bg-white rounded-3xl p-6 text-center border border-slate-200 shadow-sm card-lift flex flex-col justify-between transition-all" data-dept="{{ $fac['dept'] }}">
                <div>
                    <div class="relative w-28 h-28 mx-auto mb-4">
                        <div class="w-full h-full rounded-2xl overflow-hidden shadow-md border-2 border-amber-400 p-0.5 bg-white">
                            <img src="{{ $fac['photo'] }}" 
                                 alt="{{ $fac['name'] }}" 
                                 class="w-full h-full object-cover rounded-xl"
                                 onerror="this.src='{{ asset('imgs/profile.webp') }}'">
                        </div>
                        <span class="absolute -bottom-2.5 left-1/2 -translate-x-1/2 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-900 text-white border border-slate-700 shadow-sm whitespace-nowrap">
                            {{ $fac['experience'] }}
                        </span>
                    </div>

                    @if(!empty($fac['is_class_teacher']))
                    <div class="mb-2">
                        <span class="inline-flex items-center gap-1 bg-slate-100 text-slate-800 border border-slate-200 text-[10px] font-bold px-2.5 py-0.5 rounded-full shadow-xs">
                            <i class="bi bi-star-fill text-slate-700 text-[9px]"></i> Class In-Charge
                        </span>
                    </div>
                    @endif

                    <h4 class="font-bold text-base text-slate-900 mt-2 mb-1 leading-snug">
                        {{ $fac['name'] }}
                    </h4>
                    <span class="text-xs font-bold text-slate-700 block mb-2 leading-tight">
                        {{ $fac['role'] }}
                    </span>
                    <p class="text-[11px] text-slate-500 font-mono-code mb-3 bg-white px-2.5 py-1 rounded-lg border border-slate-200 inline-block line-clamp-1">
                        {{ $fac['qual'] }}
                    </p>

                    @if(!empty($fac['courses']) && count($fac['courses']) > 0)
                    <div class="flex flex-wrap gap-1 justify-center mb-3">
                        @foreach(array_slice($fac['courses'], 0, 2) as $cBadge)
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-medium bg-slate-100 text-slate-700 border border-slate-200">
                            {{ $cBadge }}
                        </span>
                        @endforeach
                    </div>
                    @endif

                    <p class="text-xs text-slate-600 leading-relaxed line-clamp-2 mb-4">
                        {{ $fac['bio'] }}
                    </p>
                </div>

                <div class="pt-3 border-t border-slate-200/80 flex items-center justify-between gap-2">
                    <div class="text-left truncate">
                        <a href="mailto:{{ $fac['email'] }}" class="text-[11px] text-slate-500 hover:text-slate-900 truncate block transition" title="{{ $fac['email'] }}">
                            <i class="bi bi-envelope text-slate-700"></i> {{ $fac['email'] }}
                        </a>
                    </div>
                    @php
                        $coursesStr = !empty($fac['courses']) ? implode(', ', $fac['courses']) : '';
                    @endphp
                    <button onclick="openFacultyModal('{{ addslashes($fac['name']) }}', '{{ addslashes($fac['role']) }}', '{{ addslashes($fac['qual']) }}', '{{ addslashes($fac['experience']) }}', '{{ addslashes($fac['email']) }}', '{{ addslashes($fac['bio']) }}', '{{ $fac['photo'] }}', '{{ addslashes($coursesStr) }}', '{{ addslashes($fac['phone'] ?? '') }}')" 
                            class="p-2 rounded-xl text-xs font-bold text-slate-900 bg-slate-100 hover:bg-slate-900 hover:text-white transition shrink-0" title="View Full Bio">
                        <i class="bi bi-person-lines-fill"></i>
                    </button>
                </div>
            </div>
            @endforeach

            {{-- School Management Committee (SMC) Cards --}}
            @if(isset($committeeMembers) && $committeeMembers->count() > 0)
                @foreach($committeeMembers as $cm)
                <div class="faculty-card bg-amber-50/50 hover:bg-amber-50 rounded-3xl p-6 text-center border border-amber-200 shadow-sm card-lift flex flex-col justify-between transition-all" data-dept="smc">
                    <div>
                        <div class="w-24 h-24 mx-auto mb-4 rounded-2xl overflow-hidden shadow-md border-2 border-amber-500 p-0.5 bg-white flex items-center justify-center text-amber-600 text-3xl">
                            <i class="bi bi-person-badge-fill"></i>
                        </div>
                        <h4 class="font-cinzel font-extrabold text-base text-navy-900 mb-1">
                            {{ $cm->full_name }}
                        </h4>
                        <span class="text-xs font-bold text-amber-700 block mb-2 uppercase tracking-wide">
                            {{ \App\Models\SchoolCommitteeMember::POSITIONS[$cm->position] ?? $cm->position }}
                        </span>
                        <p class="text-xs text-slate-600 leading-relaxed mb-4">
                            School Management Committee Member guiding institutional policy, governance, and quality assurance.
                        </p>
                    </div>
                    <div class="pt-3 border-t border-amber-200 text-xs text-slate-500">
                        <i class="bi bi-telephone"></i> {{ $cm->phone ?? 'SMC Desk' }}
                    </div>
                </div>
                @endforeach
            @endif
        </div>
    </div>
</section>

@endsection
