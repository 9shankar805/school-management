@extends('school-landing.layout')

@section('title', 'Academic Programs, Streams & Curriculum')

@section('content')

{{-- Page Header --}}
<div class="bg-white text-slate-900 py-12 border-b border-slate-200 relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-2 uppercase tracking-wider">
            <a href="{{ $pageUrl('') }}" class="hover:underline text-slate-700">Home</a>
            <span>/</span>
            <span class="text-slate-900 font-bold">Academics</span>
        </div>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900">Academic Programs & Streams</h1>
        <p class="text-slate-600 text-sm sm:text-base mt-2 max-w-2xl">
            National Examinations Board (NEB) affiliated curriculums designed to pave seamless pathways to top universities worldwide.
        </p>
    </div>
</div>

{{-- Academic Streams Grid --}}
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Program 1: +2 Science Stream --}}
        <div class="bg-slate-50 rounded-3xl p-8 sm:p-12 border border-slate-200 mb-12 shadow-sm">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <div class="lg:col-span-8">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-800 flex items-center justify-center text-2xl font-bold">
                            <i class="bi bi-atom"></i>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-blue-600 uppercase tracking-wider">NEB Affiliated · 2 Years</span>
                            <h2 class="font-cinzel font-extrabold text-2xl sm:text-3xl text-navy-900">+2 Science Stream</h2>
                        </div>
                    </div>
                    <p class="text-slate-600 text-sm sm:text-base leading-relaxed mb-6">
                        The Science stream offers an intensive, research-based curriculum designed for aspiring engineers, physicians, biochemists, and IT innovators. Scholars engage in hands-on physics, chemistry, and biology laboratory experiments alongside advanced mathematics and computing.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                        <div class="bg-white p-4 rounded-xl border border-slate-200">
                            <h4 class="font-bold text-xs uppercase tracking-wider text-slate-900 mb-2">Compulsory & Elective Subjects</h4>
                            <ul class="text-xs text-slate-600 space-y-1">
                                <li>• Compulsory English & Nepali</li>
                                <li>• Physics (Theory & Practical Lab)</li>
                                <li>• Chemistry (Organic & Inorganic)</li>
                                <li>• Mathematics / Biology (Pre-Med Track)</li>
                                <li>• Computer Science & Python Programming</li>
                            </ul>
                        </div>
                        <div class="rounded-xl overflow-hidden border border-slate-200 aspect-[16/9] shadow-xs">
                            <img src="{{ asset('imgs/hero/hero_2.webp') }}" alt="+2 Science Laboratory" class="w-full h-full object-cover">
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest block mb-1">Eligibility Criteria</span>
                        <h4 class="font-bold text-navy-900 text-sm mb-3">SEE Minimum GPA 2.8+</h4>
                        <p class="text-xs text-slate-500 mb-6 leading-relaxed">
                            Minimum Grade C+ in Science & Compulsory Mathematics in Secondary Education Examination (SEE).
                        </p>
                    </div>
                    <a href="{{ url('admissions/apply?school='.$school->slug) }}" class="btn-primary-dark w-full text-center">
                        <i class="bi bi-mortarboard-fill"></i> Apply for Science &rarr;
                    </a>
                </div>
            </div>
        </div>

        {{-- Program 2: +2 Management Stream --}}
        <div class="bg-slate-50 rounded-3xl p-8 sm:p-12 border border-slate-200 mb-12 shadow-sm">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <div class="lg:col-span-8">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center text-2xl font-bold">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-amber-600 uppercase tracking-wider">NEB Affiliated · 2 Years</span>
                            <h2 class="font-cinzel font-extrabold text-2xl sm:text-3xl text-navy-900">+2 Management Stream</h2>
                        </div>
                    </div>
                    <p class="text-slate-600 text-sm sm:text-base leading-relaxed mb-6">
                        Designed to mold visionary entrepreneurs, corporate financial analysts, chartered accountants, and marketing leaders. Students engage in corporate case studies, stock simulation labs, and practical business analytics.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                        <div class="bg-white p-4 rounded-xl border border-slate-200">
                            <h4 class="font-bold text-xs uppercase tracking-wider text-slate-900 mb-2">Core Subject Concentrations</h4>
                            <ul class="text-xs text-slate-600 space-y-1">
                                <li>• Principles of Accounting & Financial Reporting</li>
                                <li>• Micro & Macro Economics</li>
                                <li>• Business Studies & Corporate Governance</li>
                                <li>• Business Mathematics / Marketing</li>
                                <li>• Hotel Management / Computer Science</li>
                            </ul>
                        </div>
                        <div class="rounded-xl overflow-hidden border border-slate-200 aspect-[16/9] shadow-xs">
                            <img src="{{ asset('imgs/hero/hero_1.webp') }}" alt="+2 Management Studies" class="w-full h-full object-cover">
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest block mb-1">Eligibility Criteria</span>
                        <h4 class="font-bold text-navy-900 text-sm mb-3">SEE Minimum GPA 2.0+</h4>
                        <p class="text-xs text-slate-500 mb-6 leading-relaxed">
                            Minimum Grade D+ in English, Nepali, and Mathematics in SEE examination.
                        </p>
                    </div>
                    <a href="{{ url('admissions/apply?school='.$school->slug) }}" class="btn-primary-dark w-full text-center">
                        <i class="bi bi-mortarboard-fill"></i> Apply for Management &rarr;
                    </a>
                </div>
            </div>
        </div>

        {{-- Program 3: Humanities & Law --}}
        <div class="bg-slate-50 rounded-3xl p-8 sm:p-12 border border-slate-200 mb-12 shadow-sm">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <div class="lg:col-span-8">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 rounded-xl bg-purple-100 text-purple-800 flex items-center justify-center text-2xl font-bold">
                            <i class="bi bi-bank"></i>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-purple-600 uppercase tracking-wider">NEB Affiliated · 2 Years</span>
                            <h2 class="font-cinzel font-extrabold text-2xl sm:text-3xl text-navy-900">+2 Humanities & Law</h2>
                        </div>
                    </div>
                    <p class="text-slate-600 text-sm sm:text-base leading-relaxed mb-6">
                        Rigorous intellectual grounding in jurisprudence, constitutional rights, sociology, mass media, and literature. Features moot court trials, journalism studios, and social policy research.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-white p-4 rounded-xl border border-slate-200">
                            <h4 class="font-bold text-xs uppercase tracking-wider text-navy-900 mb-2">Curriculum Highlights</h4>
                            <ul class="text-xs text-slate-600 space-y-1">
                                <li>• Jurisprudence & Nepalese Legal System</li>
                                <li>• Sociology & Social Research Methodology</li>
                                <li>• Mass Communication, Journalism & Media</li>
                                <li>• English Literature & Public Rhetoric</li>
                            </ul>
                        </div>
                        <div class="bg-white p-4 rounded-xl border border-slate-200">
                            <h4 class="font-bold text-xs uppercase tracking-wider text-navy-900 mb-2">Practical Experience</h4>
                            <ul class="text-xs text-slate-600 space-y-1">
                                <li>• Model United Nations (MUN) Delegations</li>
                                <li>• Courtroom Observation Visits</li>
                                <li>• Legal Aid Clinic Internships</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest block mb-1">Eligibility Criteria</span>
                        <h4 class="font-bold text-navy-900 text-sm mb-3">SEE Minimum GPA 2.0+</h4>
                        <p class="text-xs text-slate-500 mb-6 leading-relaxed">
                            Pass in SEE examination with good aptitude in language and social sciences.
                        </p>
                    </div>
                    <a href="{{ url('admissions/apply?school='.$school->slug) }}" class="btn-primary-dark w-full text-center">
                        <i class="bi bi-mortarboard-fill"></i> Apply for Law & Humanities &rarr;
                    </a>
                </div>
            </div>
        </div>

        {{-- Program 4: Secondary School (1-10) --}}
        <div class="bg-slate-50 rounded-3xl p-8 sm:p-12 border border-slate-200 mb-12 shadow-sm">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <div class="lg:col-span-8">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-2xl font-bold">
                            <i class="bi bi-book-half"></i>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Comprehensive Foundation · Grades 1-10</span>
                            <h2 class="font-cinzel font-extrabold text-2xl sm:text-3xl text-navy-900">Basic & Secondary Education (Grades 1–10)</h2>
                        </div>
                    </div>
                    <p class="text-slate-600 text-sm sm:text-base leading-relaxed mb-6">
                        An activity-based holistic curriculum combining STEM, mental arithmetic, English fluency, environmental studies, and physical education to prepare students for outstanding SEE distinction.
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-white p-4 rounded-xl border border-slate-200">
                            <h4 class="font-bold text-xs uppercase tracking-wider text-navy-900 mb-2">Foundation Pillars</h4>
                            <ul class="text-xs text-slate-600 space-y-1">
                                <li>• Smart Digital Classrooms & Audiovisuals</li>
                                <li>• Coding, Scratch & Robotics Projects</li>
                                <li>• Continuous Assessment System (CAS)</li>
                            </ul>
                        </div>
                        <div class="bg-white p-4 rounded-xl border border-slate-200">
                            <h4 class="font-bold text-xs uppercase tracking-wider text-navy-900 mb-2">Holistic Growth</h4>
                            <ul class="text-xs text-slate-600 space-y-1">
                                <li>• Sports, Martial Arts & Yoga</li>
                                <li>• Music, Drama & Art Studios</li>
                                <li>• Spoken English & Moral Etiquette</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest block mb-1">Admissions Level</span>
                        <h4 class="font-bold text-navy-900 text-sm mb-3">Pre-Primary to Grade IX</h4>
                        <p class="text-xs text-slate-500 mb-6 leading-relaxed">
                            Age-appropriate assessment and character interview for student admission.
                        </p>
                    </div>
                    <a href="{{ url('admissions/apply?school='.$school->slug) }}" class="btn-primary-dark w-full text-center">
                        <i class="bi bi-mortarboard-fill"></i> Apply for School (1-10) &rarr;
                    </a>
                </div>
            </div>
        </div>

        {{-- Program 5: Preschool & Montessori Early Childhood --}}
        <div class="bg-slate-50 rounded-3xl p-8 sm:p-12 border border-slate-200 mb-12 shadow-sm">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <div class="lg:col-span-8">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 rounded-xl bg-orange-100 text-orange-800 flex items-center justify-center text-2xl font-bold">
                            <i class="bi bi-balloon-fill"></i>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-orange-600 uppercase tracking-wider">Early Childhood Development · Pre-Nursery to UKG</span>
                            <h2 class="font-cinzel font-extrabold text-2xl sm:text-3xl text-navy-900">Preschool & Montessori Early Years</h2>
                        </div>
                    </div>
                    <p class="text-slate-600 text-sm sm:text-base leading-relaxed mb-6">
                        A loving, joyful environment where young minds develop phonics, motor coordination, sensorial cognition, and emotional security through play-based discovery.
                    </p>
                </div>

                <div class="lg:col-span-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest block mb-1">Ages 2.5 to 5.5 Years</span>
                        <h4 class="font-bold text-navy-900 text-sm mb-3">Pre-Nursery, Nursery, LKG, UKG</h4>
                        <p class="text-xs text-slate-500 mb-6 leading-relaxed">
                            Caring teachers follow children from early years into Grade 1 with warmth.
                        </p>
                    </div>
                    <a href="{{ url('admissions/apply?school='.$school->slug) }}" class="btn-primary-dark w-full text-center">
                        <i class="bi bi-mortarboard-fill"></i> Enroll in Preschool &rarr;
                    </a>
                </div>
            </div>
        </div>

        {{-- Program 6: Spectrum Inclusive Learning Center --}}
        <div class="bg-slate-50 rounded-3xl p-8 sm:p-12 border border-slate-200 mb-16 shadow-sm">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <div class="lg:col-span-8">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 rounded-xl bg-teal-100 text-teal-800 flex items-center justify-center text-2xl font-bold">
                            <i class="bi bi-heart-pulse-fill"></i>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-teal-600 uppercase tracking-wider">Special Needs & Neurodiverse Care</span>
                            <h2 class="font-cinzel font-extrabold text-2xl sm:text-3xl text-navy-900">Spectrum Learning Center (Special Education)</h2>
                        </div>
                    </div>
                    <p class="text-slate-600 text-sm sm:text-base leading-relaxed mb-6">
                        Tailored educational therapy and behavioral mentoring for children with autism, Down syndrome, ADHD, and developmental delays, designed with compassion to guide them toward mainstream integration.
                    </p>
                </div>

                <div class="lg:col-span-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest block mb-1">Personalized Care</span>
                        <h4 class="font-bold text-navy-900 text-sm mb-3">1:1 Educator Ratio</h4>
                        <p class="text-xs text-slate-500 mb-6 leading-relaxed">
                            Specialized therapies and individual learning plans (IEP).
                        </p>
                    </div>
                    <a href="{{ $pageUrl('contact') }}" class="btn-primary-dark w-full text-center">
                        <i class="bi bi-chat-heart"></i> Inquire for Spectrum &rarr;
                    </a>
                </div>
            </div>
        </div>

        {{-- DYNAMIC REGISTERED CLASSES & SUBJECT OFFERINGS FROM DATABASE --}}
        @if(isset($classes) && $classes->count() > 0)
        <div class="mb-16">
            <div class="text-center max-w-3xl mx-auto mb-10">
                <span class="text-xs font-bold tracking-widest uppercase text-[#f36c21] bg-orange-50 px-3.5 py-1 rounded-md border border-orange-200 inline-block mb-2">
                    Live Institutional Registry
                </span>
                <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900">
                    Active Registered Grades & Curriculum Structure
                </h3>
                <p class="text-slate-600 text-xs sm:text-sm mt-1">
                    Direct academic catalog synchronized with the {{ $school->name }} management system.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($classes as $cls)
                <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200 shadow-sm card-lift flex flex-col justify-between hover:border-[#f36c21] transition">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-bold text-[#f36c21] uppercase tracking-wider">
                                Class / Level
                            </span>
                            @if($cls->sections && $cls->sections->count() > 0)
                            <span class="text-[10px] font-bold bg-white text-slate-700 px-2 py-0.5 rounded border border-slate-200">
                                {{ $cls->sections->count() }} Section{{ $cls->sections->count() > 1 ? 's' : '' }}
                            </span>
                            @endif
                        </div>
                        <h4 class="font-bold text-xl text-slate-900 mb-2">
                            Class {{ $cls->class_name }}
                        </h4>

                        @if($cls->sections && $cls->sections->count() > 0)
                        <div class="flex flex-wrap gap-1 mb-3">
                            @foreach($cls->sections as $sec)
                            <span class="text-[11px] font-medium bg-white px-2 py-0.5 rounded border border-slate-200 text-slate-700">
                                Section {{ $sec->section_name ?? $sec->name }}
                            </span>
                            @endforeach
                        </div>
                        @endif

                        @if($cls->courses && $cls->courses->count() > 0)
                        <div class="border-t border-slate-200 pt-3 mt-3">
                            <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider block mb-1.5">Key Subjects:</span>
                            <div class="flex flex-wrap gap-1">
                                @foreach($cls->courses->take(6) as $crs)
                                <span class="text-[10px] font-medium bg-white text-slate-700 px-2 py-0.5 rounded border border-slate-200">
                                    {{ $crs->course_name ?? $crs->name }}
                                </span>
                                @endforeach
                                @if($cls->courses->count() > 6)
                                <span class="text-[10px] font-bold text-slate-500 bg-slate-200 px-1.5 py-0.5 rounded">
                                    +{{ $cls->courses->count() - 6 }} more
                                </span>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="pt-4 mt-4 border-t border-slate-200 flex items-center justify-between">
                        <a href="{{ url('admissions/apply?school='.$school->slug) }}" class="text-xs font-bold text-[#f36c21] hover:underline flex items-center gap-1">
                            <span>Enroll for Class {{ $cls->class_name }}</span> &rarr;
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- SPECIALIZED NEXT-GEN LABORATORIES (From Akshar SMART School Model) --}}
        <div class="mb-16">
            <div class="text-center max-w-3xl mx-auto mb-10">
                <span class="text-xs font-bold tracking-widest uppercase text-amber-600 bg-amber-50 px-3.5 py-1 rounded-full border border-amber-200 inline-block mb-2">
                    Experiential Infrastructure
                </span>
                <h3 class="font-cinzel text-2xl sm:text-3xl font-extrabold text-navy-900">
                    Next-Generation Specialized Laboratories
                </h3>
                <p class="text-slate-600 text-xs sm:text-sm mt-1">
                    Bridging abstract theoretical concepts with tangible, hands-on experimentation.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200 shadow-sm card-lift">
                    <div class="w-12 h-12 rounded-xl bg-blue-600 text-white flex items-center justify-center text-2xl font-bold mb-4">
                        <i class="bi bi-translate"></i>
                    </div>
                    <h4 class="font-cinzel font-bold text-base text-navy-900 mb-2">Interactive Language Lab</h4>
                    <p class="text-xs text-slate-600 leading-relaxed mb-4">
                        Equipped with individual multimedia audio booths and speech-analysis software for native English phonetics, listening comprehension, and public rhetoric.
                    </p>
                    <span class="text-[11px] font-bold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-200">Bilingual Fluency & Accent Neutralization</span>
                </div>

                <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200 shadow-sm card-lift">
                    <div class="w-12 h-12 rounded-xl bg-amber-600 text-white flex items-center justify-center text-2xl font-bold mb-4">
                        <i class="bi bi-calculator-fill"></i>
                    </div>
                    <h4 class="font-cinzel font-bold text-base text-navy-900 mb-2">Mathematics Discovery Lab</h4>
                    <p class="text-xs text-slate-600 leading-relaxed mb-4">
                        Manipulative models, geometric 3D solids, algebraic kits, and Vedic math visualization tools that turn complex theorems into intuitive playful proofs.
                    </p>
                    <span class="text-[11px] font-bold text-amber-700 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200">Experiential Arithmetic & Geometry</span>
                </div>

                <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200 shadow-sm card-lift">
                    <div class="w-12 h-12 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-2xl font-bold mb-4">
                        <i class="bi bi-display"></i>
                    </div>
                    <h4 class="font-cinzel font-bold text-base text-navy-900 mb-2">SMART Classrooms (Max 35)</h4>
                    <p class="text-xs text-slate-600 leading-relaxed mb-4">
                        Strictly capped class sizes (maximum 30–35 scholars per section) with interactive touchboards to ensure personal 1:1 teacher mentorship and active participation.
                    </p>
                    <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200">1:1 Attention & ISO 9001 Standards</span>
                </div>
            </div>
        </div>

        {{-- ACADEMIC DOWNLOADS HUB & PTM ENGAGEMENT POLICY --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center bg-slate-50 rounded-3xl p-8 sm:p-12 border border-slate-200">
            <div class="lg:col-span-7">
                <span class="text-xs font-bold tracking-widest uppercase text-navy-700 bg-blue-100 text-blue-900 px-3.5 py-1 rounded-full inline-block mb-3">
                    Academic Resources
                </span>
                <h3 class="font-cinzel text-2xl sm:text-3xl font-extrabold text-navy-900 mb-4">
                    Download Curricular Guidelines & Syllabi
                </h3>
                <p class="text-slate-600 text-xs sm:text-sm leading-relaxed mb-6">
                    Access complete grade-wise subject syllabi, annual academic calendar planners, recommended reading lists, and our structured <strong>Parent-Teacher Meeting (PTM)</strong> schedule.
                </p>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <button onclick="openProspectusModal()" class="p-3 bg-white hover:bg-slate-100 rounded-xl border border-slate-200 text-left transition shadow-sm">
                        <i class="bi bi-file-earmark-text-fill text-amber-500 text-lg block mb-1"></i>
                        <span class="font-bold text-xs text-navy-900 block">Course Syllabus</span>
                        <span class="text-[10px] text-slate-400">PDF Download</span>
                    </button>
                    <button onclick="openProspectusModal()" class="p-3 bg-white hover:bg-slate-100 rounded-xl border border-slate-200 text-left transition shadow-sm">
                        <i class="bi bi-calendar3 text-blue-600 text-lg block mb-1"></i>
                        <span class="font-bold text-xs text-navy-900 block">Academic Planner</span>
                        <span class="text-[10px] text-slate-400">Session 2026-27</span>
                    </button>
                    <button onclick="openProspectusModal()" class="p-3 bg-white hover:bg-slate-100 rounded-xl border border-slate-200 text-left transition shadow-sm col-span-2 sm:col-span-1">
                        <i class="bi bi-book-half text-emerald-600 text-lg block mb-1"></i>
                        <span class="font-bold text-xs text-navy-900 block">Book & Kit Lists</span>
                        <span class="text-[10px] text-slate-400">All Grades</span>
                    </button>
                </div>
            </div>

            <div class="lg:col-span-5 bg-white text-slate-900 p-7 rounded-2xl shadow-md border border-slate-200">
                <div class="flex items-center gap-2 text-slate-900 font-bold text-xs uppercase tracking-wider mb-2">
                    <i class="bi bi-people-fill text-base"></i> Parent Partnership Policy
                </div>
                <h4 class="text-lg font-bold mb-3 text-slate-900">Structured Monthly PTM</h4>
                <p class="text-xs text-slate-600 leading-relaxed mb-4">
                    In alignment with the best institutional practices, formal Parent-Teacher Consultations are conducted on the <strong>2nd Friday of every month</strong> to review individual child performance, attendance, and social progress.
                </p>
                <div class="p-3 rounded-xl bg-slate-100 border border-slate-200 text-[11px] text-slate-800 font-mono">
                    <i class="bi bi-shield-check"></i> 100% Transparency via Academic Portal
                </div>
            </div>
        </div>

    </div>
</section>

@endsection
