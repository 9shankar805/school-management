@extends('school-landing.layout')

@section('title', 'Official Academic Portal & Home')

@section('content')

<!-- ═════════════════════════════════════════════════════════════════════
     1. FULL SCREEN PURE IMAGE SLIDER (NO TEXT ON SLIDER)
══════════════════════════════════════════════════════════════════════ -->
<section class="banner-container relative w-full h-[56vh] min-h-[360px] sm:h-[72vh] lg:h-[calc(100vh-5rem)] min-h-[400px] max-h-[960px] bg-slate-950 overflow-hidden">
    @php
        $sliderSlides = [];
        if (!empty($school->slider_banners) && is_array($school->slider_banners) && count($school->slider_banners) > 0) {
            foreach ($school->slider_banners as $slide) {
                if (!empty($slide['image'])) {
                    $slideUrl = (!empty($slide['is_asset']) || str_starts_with($slide['image'], 'imgs/')) 
                        ? asset($slide['image']) 
                        : asset('storage/' . $slide['image']);
                    $isVideo = (!empty($slide['type']) && $slide['type'] === 'video') || preg_match('/\.(mp4|webm|ogg|mov)$/i', $slide['image']);
                    $sliderSlides[] = [
                        'url'      => $slideUrl,
                        'is_video' => $isVideo,
                    ];
                }
            }
        }

        // If no custom slider banners uploaded, use exactly the 3 official hero slider images
        if (empty($sliderSlides)) {
            $sliderSlides = [
                ['url' => asset('imgs/hero/hero_1.webp'), 'is_video' => false],
                ['url' => asset('imgs/hero/hero_2.webp'), 'is_video' => false],
                ['url' => asset('imgs/hero/hero_3.webp'), 'is_video' => false],
            ];
        }
    @endphp

    {{-- Fullscreen Swiper Banner Slider (Clean Image & Autoplay MP4 Video Display) --}}
    <div class="swiper banner-swiper w-full h-full">
        <div class="swiper-wrapper w-full h-full">
            @foreach($sliderSlides as $idx => $slideItem)
            <div class="swiper-slide w-full h-full bg-slate-900 relative">
                @if($slideItem['is_video'])
                    <video class="banner-slide-img banner-slide-video w-full h-full object-cover object-center select-none" 
                           autoplay muted playsinline preload="auto">
                        <source src="{{ $slideItem['url'] }}" type="video/mp4">
                    </video>
                @else
                    <img src="{{ $slideItem['url'] }}" 
                         alt="{{ $school->name }} Campus Slide {{ $idx + 1 }}" 
                         class="banner-slide-img w-full h-full object-cover object-center select-none"
                         loading="{{ $idx === 0 ? 'eager' : 'lazy' }}">
                @endif
            </div>
            @endforeach
        </div>

        {{-- Scroll Down Indicator --}}
        <a href="#welcome" class="absolute bottom-5 left-1/2 -translate-x-1/2 z-20 text-white/80 hover:text-white hidden sm:flex flex-col items-center gap-0.5 text-[10px] font-bold tracking-widest uppercase transition drop-shadow">
            <span>Scroll</span>
            <i class="bi bi-chevron-down animate-bounce text-xs"></i>
        </a>
    </div>
</section>

<!-- ═════════════════════════════════════════════════════════════════════
     2. INSTITUTIONAL WELCOME & QUICK ACTION BAR
══════════════════════════════════════════════════════════════════════ -->
<section id="welcome" class="bg-white py-10 sm:py-14 border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            <div class="lg:col-span-8">
                <div class="inline-flex items-center gap-2 mb-3 flex-wrap">
                    <span class="bg-[#f36c21] text-white text-xs font-bold px-3 py-1 rounded-md shadow-sm tracking-wide">
                        {{ $school->name }}
                    </span>
                    <span class="text-slate-500 font-semibold text-xs sm:text-sm">
                        ESTD {{ $school->established_year ?? '2004' }} · {{ $school->city ?? ($school->district ?? 'Nepal') }}
                    </span>
                    <span class="bg-slate-100 text-slate-800 text-[11px] font-bold px-2.5 py-0.5 rounded border border-slate-200">
                        <i class="bi bi-patch-check-fill text-[#f36c21]"></i> Govt. / NEB Approved
                    </span>
                </div>
                <h1 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight mb-3">
                    We help our students <span class="text-slate-900 underline decoration-[#f36c21] decoration-4 underline-offset-8">realise their potential</span>
                </h1>
                <p class="text-slate-600 text-sm sm:text-base leading-relaxed max-w-3xl">
                    {{ $school->slogan ?? 'A premier institution dedicated to academic excellence, disciplined leadership, holistic sports, and modern STEM innovation.' }}
                </p>
            </div>

            <div class="lg:col-span-4 flex flex-col sm:flex-row lg:flex-col gap-3 justify-center">
                <a href="{{ url('admissions/apply?school='.$school->slug) }}" 
                   class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl font-bold text-xs sm:text-sm text-white bg-[#f36c21] hover:bg-[#e05d15] shadow-md transition transform hover:-translate-y-0.5">
                    <i class="bi bi-pencil-square"></i>
                    <span>Apply Online for 2026/27</span>
                </a>
                <div class="grid grid-cols-2 gap-2">
                    <button onclick="openProspectusModal()" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 rounded-xl font-bold text-xs text-slate-800 bg-white hover:bg-slate-50 border border-slate-300 shadow-sm transition">
                        <i class="bi bi-download text-[#f36c21]"></i> Prospectus
                    </button>
                    <button onclick="openExamResultModal()" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 rounded-xl font-bold text-xs text-slate-800 bg-white hover:bg-slate-50 border border-slate-300 shadow-sm transition">
                        <i class="bi bi-mortarboard text-[#f36c21]"></i> Exam Results
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═════════════════════════════════════════════════════════════════════
     3. INSTITUTIONAL STATISTICAL MILESTONES
══════════════════════════════════════════════════════════════════════ -->
<div id="milestones" class="bg-slate-50 text-slate-900 py-8 border-b border-slate-200 relative z-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 sm:gap-6 text-center">
            <div class="p-3 border-r border-slate-200">
                <div class="text-2xl sm:text-4xl font-extrabold text-slate-900 mb-0.5">100%</div>
                <div class="text-xs sm:text-sm font-bold tracking-wider text-slate-800 uppercase">Board Pass Record</div>
                <p class="text-[11px] text-slate-500 mt-0.5 hidden sm:block">National NEB & SEE Distinctions</p>
            </div>
            <div class="p-3 border-r border-slate-200">
                <div class="text-2xl sm:text-4xl font-extrabold text-slate-900 mb-0.5">Max 35</div>
                <div class="text-xs sm:text-sm font-bold tracking-wider text-slate-800 uppercase">Capped Class Size</div>
                <p class="text-[11px] text-slate-500 mt-0.5 hidden sm:block">Guaranteed 1:1 Attention</p>
            </div>
            <div class="p-3 border-r border-slate-200">
                <div class="text-2xl sm:text-4xl font-extrabold text-slate-900 mb-0.5">ISO 9001</div>
                <div class="text-xs sm:text-sm font-bold tracking-wider text-slate-800 uppercase">Quality Standard</div>
                <p class="text-[11px] text-slate-500 mt-0.5 hidden sm:block">Certified Management System</p>
            </div>
            <div class="p-3 border-r border-slate-200">
                <div class="text-2xl sm:text-4xl font-extrabold text-slate-900 mb-0.5">1:15</div>
                <div class="text-xs sm:text-sm font-bold tracking-wider text-slate-800 uppercase">Teacher Ratio</div>
                <p class="text-[11px] text-slate-500 mt-0.5 hidden sm:block">Personalized Mentorship</p>
            </div>
            <div class="p-3 col-span-2 md:col-span-1">
                <div class="text-2xl sm:text-4xl font-extrabold text-slate-900 mb-0.5">45+</div>
                <div class="text-xs sm:text-sm font-bold tracking-wider text-slate-800 uppercase">ECA Clubs & Sports</div>
                <p class="text-[11px] text-slate-500 mt-0.5 hidden sm:block">Holistic STEAM & Athletics</p>
            </div>
        </div>
    </div>
</div>

<!-- ═════════════════════════════════════════════════════════════════════
     4. ACADEMIC PROGRAM LEVELS WITH PHOTO CARDS (MAXIMUM IMAGES)
══════════════════════════════════════════════════════════════════════ -->
<section class="py-16 bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-4">
            <div>
                <span class="text-xs font-bold tracking-widest uppercase text-slate-700 bg-orange-50 text-[#f36c21] px-3.5 py-1 rounded-md border border-orange-200 inline-block mb-2">
                    Academic Streams & Curriculum
                </span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900">
                    Comprehensive Academic Pathways
                </h2>
                <p class="text-slate-600 text-sm mt-1 max-w-xl">
                    From early childhood play-based inquiry to Secondary SEE and +2 Higher Secondary Excellence.
                </p>
            </div>
            <a href="{{ $pageUrl('academics') }}" class="btn-primary-dark !text-xs !py-2.5 !px-4 flex items-center gap-1.5 shrink-0">
                <span>View All Curriculum Streams</span> &rarr;
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- 1. Pre-Primary & Montessori --}}
            <div class="bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-sm card-lift flex flex-col justify-between group hover:border-[#f36c21] transition">
                <div class="relative aspect-[16/10] overflow-hidden bg-slate-100">
                    <img src="{{ asset('imgs/hero/hero_3.webp') }}" alt="Pre-Primary & Montessori" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                    <div class="absolute top-3 left-3 bg-white/95 backdrop-blur-sm px-2.5 py-1 rounded-md text-[11px] font-bold text-slate-900 shadow-sm">
                        Pre-Nursery – UKG
                    </div>
                </div>
                <div class="p-6 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-lg text-slate-900 mb-1.5 group-hover:text-[#f36c21] transition">Preschool & Montessori</h3>
                        <p class="text-xs text-slate-600 leading-relaxed mb-4">
                            Child-centred early years foundation where dedicated educators nurture phonics, sensorial play, emotional confidence, and social curiosity.
                        </p>
                    </div>
                    <a href="{{ $pageUrl('academics') }}#preschool" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#f36c21] group-hover:underline">
                        <span>Learn about Early Years</span> &rarr;
                    </a>
                </div>
            </div>

            {{-- 2. Basic School (1-8) --}}
            <div class="bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-sm card-lift flex flex-col justify-between group hover:border-[#f36c21] transition">
                <div class="relative aspect-[16/10] overflow-hidden bg-slate-100">
                    <img src="{{ asset('imgs/hero/hero_1.webp') }}" alt="Basic School Grades 1-8" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                    <div class="absolute top-3 left-3 bg-white/95 backdrop-blur-sm px-2.5 py-1 rounded-md text-[11px] font-bold text-slate-900 shadow-sm">
                        Grades 1 – 8
                    </div>
                </div>
                <div class="p-6 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-lg text-slate-900 mb-1.5 group-hover:text-[#f36c21] transition">Primary & Basic School</h3>
                        <p class="text-xs text-slate-600 leading-relaxed mb-4">
                            Comprehensive English-medium curriculum combining mathematical inquiry, bilingual fluency, early coding, scientific curiosity, and moral leadership.
                        </p>
                    </div>
                    <a href="{{ $pageUrl('academics') }}#primary" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#f36c21] group-hover:underline">
                        <span>Explore Basic Level</span> &rarr;
                    </a>
                </div>
            </div>

            {{-- 3. Secondary SEE (9-10) --}}
            <div class="bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-sm card-lift flex flex-col justify-between group hover:border-[#f36c21] transition">
                <div class="relative aspect-[16/10] overflow-hidden bg-slate-100">
                    <img src="{{ file_exists(public_path('imgs/hero/school_hero_graduates.jpg')) ? asset('imgs/hero/school_hero_graduates.jpg') : asset('imgs/hero/hero_2.webp') }}" alt="Secondary SEE Grades 9-10" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                    <div class="absolute top-3 left-3 bg-white/95 backdrop-blur-sm px-2.5 py-1 rounded-md text-[11px] font-bold text-slate-900 shadow-sm">
                        Grades 9 – 10 (SEE)
                    </div>
                </div>
                <div class="p-6 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-lg text-slate-900 mb-1.5 group-hover:text-[#f36c21] transition">Secondary SEE Board</h3>
                        <p class="text-xs text-slate-600 leading-relaxed mb-4">
                            Rigorous national examination preparation with intensive laboratory sessions, model tests, and personalized career counseling.
                        </p>
                    </div>
                    <a href="{{ $pageUrl('academics') }}#secondary" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#f36c21] group-hover:underline">
                        <span>Explore SEE Preparation</span> &rarr;
                    </a>
                </div>
            </div>

            {{-- 4. +2 Science & Management --}}
            <div class="bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-sm card-lift flex flex-col justify-between group hover:border-[#f36c21] transition">
                <div class="relative aspect-[16/10] overflow-hidden bg-slate-100">
                    <img src="{{ asset('imgs/hero/hero_2.webp') }}" alt="+2 Higher Secondary" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                    <div class="absolute top-3 left-3 bg-white/95 backdrop-blur-sm px-2.5 py-1 rounded-md text-[11px] font-bold text-slate-900 shadow-sm">
                        Grade XI & XII (NEB)
                    </div>
                </div>
                <div class="p-6 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-lg text-slate-900 mb-1.5 group-hover:text-[#f36c21] transition">+2 Higher Secondary</h3>
                        <p class="text-xs text-slate-600 leading-relaxed mb-4">
                            NEB accredited Science, Management & Humanities streams with pre-medical, engineering, and CA entrance foundation coaching.
                        </p>
                    </div>
                    <a href="{{ $pageUrl('academics') }}#plus2" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#f36c21] group-hover:underline">
                        <span>View +2 Streams</span> &rarr;
                    </a>
                </div>
            </div>

            {{-- 5. STEAM & Robotics --}}
            <div class="bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-sm card-lift flex flex-col justify-between group hover:border-[#f36c21] transition">
                <div class="relative aspect-[16/10] overflow-hidden bg-slate-100">
                    <img src="{{ asset('imgs/kathmandu.webp') }}" alt="STEAM & Digital Labs" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                    <div class="absolute top-3 left-3 bg-white/95 backdrop-blur-sm px-2.5 py-1 rounded-md text-[11px] font-bold text-slate-900 shadow-sm">
                        Innovation & Tech
                    </div>
                </div>
                <div class="p-6 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-lg text-slate-900 mb-1.5 group-hover:text-[#f36c21] transition">Robotics, AI & Computing</h3>
                        <p class="text-xs text-slate-600 leading-relaxed mb-4">
                            Hands-on digital intelligence, Python coding, microcontrollers, and logic development for 21st-century problem solvers.
                        </p>
                    </div>
                    <a href="{{ $pageUrl('facilities') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#f36c21] group-hover:underline">
                        <span>Tour Computing Labs</span> &rarr;
                    </a>
                </div>
            </div>

            {{-- 6. Sports & Creative Arts --}}
            <div class="bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-sm card-lift flex flex-col justify-between group hover:border-[#f36c21] transition">
                <div class="relative aspect-[16/10] overflow-hidden bg-slate-100">
                    <img src="{{ asset('imgs/schools.webp') }}" alt="Sports & Performing Arts" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                    <div class="absolute top-3 left-3 bg-white/95 backdrop-blur-sm px-2.5 py-1 rounded-md text-[11px] font-bold text-slate-900 shadow-sm">
                        Athletics & Culture
                    </div>
                </div>
                <div class="p-6 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-lg text-slate-900 mb-1.5 group-hover:text-[#f36c21] transition">Sports & Performing Arts</h3>
                        <p class="text-xs text-slate-600 leading-relaxed mb-4">
                            Football, cricket, basketball courts, martial arts, vocal music, traditional instruments, and inter-school championship teams.
                        </p>
                    </div>
                    <a href="{{ $pageUrl('gallery') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#f36c21] group-hover:underline">
                        <span>See Sports In Action</span> &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═════════════════════════════════════════════════════════════════════
     5. INTERACTIVE ACADEMIC & EVENT CALENDAR (PROPERLY SET UP)
══════════════════════════════════════════════════════════════════════ -->
<section id="academic-calendar" class="py-16 bg-slate-50 border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-4">
            <div>
                <span class="text-xs font-bold tracking-widest uppercase text-slate-700 bg-white px-3.5 py-1 rounded-md border border-slate-200 inline-block mb-2">
                    <i class="bi bi-calendar-event text-[#f36c21] mr-1"></i> Academic Schedule 2026/27
                </span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900">
                    Official Academic & Event Calendar
                </h2>
                <p class="text-slate-600 text-sm mt-1 max-w-xl">
                    Stay synchronized with terminal examination schedules, school holidays, sports meets, and parent-teacher meetings.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="openProspectusModal()" class="btn-secondary-white !text-xs flex items-center gap-1.5">
                    <i class="bi bi-file-earmark-pdf text-[#f36c21]"></i> Download Calendar (PDF)
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            {{-- Interactive Month Calendar View --}}
            <div class="lg:col-span-7 bg-white rounded-2xl p-4 sm:p-7 border border-slate-200 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between pb-4 border-b border-slate-200 mb-4 gap-2">
                    <div class="min-w-0">
                        <h3 class="font-extrabold text-base sm:text-lg text-slate-900 truncate" id="calendarMonthTitle">October 2026 / Kartik 2083</h3>
                        <p class="text-[11px] sm:text-xs text-slate-500 font-medium truncate">Official Institutional Academic Calendar</p>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <button type="button" onclick="changeCalMonth(-1)" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition">
                            <i class="bi bi-chevron-left text-xs"></i>
                        </button>
                        <button type="button" onclick="changeCalMonth(1)" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition">
                            <i class="bi bi-chevron-right text-xs"></i>
                        </button>
                    </div>
                </div>

                {{-- Weekday Headers --}}
                <div class="grid grid-cols-7 text-center font-bold text-[10px] sm:text-[11px] text-slate-500 uppercase tracking-wider mb-2">
                    <span class="text-red-500">Sun</span>
                    <span>Mon</span>
                    <span>Tue</span>
                    <span>Wed</span>
                    <span>Thu</span>
                    <span>Fri</span>
                    <span class="text-slate-400">Sat</span>
                </div>

                {{-- Dynamic Calendar Grid --}}
                <div class="grid grid-cols-7 gap-1 sm:gap-1.5 text-center text-xs" id="calendarDaysGrid">
                    {{-- Populated dynamically via JS below --}}
                </div>

                <div class="flex items-center justify-between pt-4 mt-4 border-t border-slate-100 text-[10px] sm:text-[11px] text-slate-500 flex-wrap gap-2">
                    <div class="flex items-center gap-3 sm:gap-4 flex-wrap">
                        <span class="flex items-center gap-1.5"><span class="w-2 sm:w-2.5 h-2 sm:h-2.5 rounded-full bg-[#f36c21]"></span> Exam</span>
                        <span class="flex items-center gap-1.5"><span class="w-2 sm:w-2.5 h-2 sm:h-2.5 rounded-full bg-emerald-500"></span> Event</span>
                        <span class="flex items-center gap-1.5"><span class="w-2 sm:w-2.5 h-2 sm:h-2.5 rounded-full bg-rose-500"></span> Holiday</span>
                    </div>
                    <span class="font-bold text-slate-700">Saturday Closed</span>
                </div>
            </div>

            {{-- Upcoming Key Dates & Events List --}}
            <div class="lg:col-span-5 space-y-3.5">
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
                        <h4 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                            <i class="bi bi-clock-history text-[#f36c21]"></i> Upcoming Key Dates
                        </h4>
                        <span class="text-[11px] text-slate-500 font-semibold">Active Session</span>
                    </div>

                    <div class="space-y-3">
                        @php
                            $displayEvents = [];
                            if (isset($events) && $events->count() > 0) {
                                foreach($events->take(5) as $evItem) {
                                    $st = \Carbon\Carbon::parse($evItem->start ?? $evItem->created_at);
                                    $displayEvents[] = [
                                        'date' => $st->format('M d, Y'),
                                        'day' => $st->format('l'),
                                        'title' => $evItem->title,
                                        'type' => 'Official Event',
                                        'color' => 'bg-orange-50 text-[#f36c21] border-orange-200'
                                    ];
                                }
                            }
                            if (count($displayEvents) < 4) {
                                $presetEvents = [
                                    ['date' => 'Oct 15, 2026', 'day' => 'Thursday', 'title' => 'Second Terminal Examination Commences', 'type' => 'Examination', 'color' => 'bg-orange-50 text-[#f36c21] border-orange-200'],
                                    ['date' => 'Oct 28, 2026', 'day' => 'Wednesday', 'title' => 'Annual Science, Robotics & Math Fair', 'type' => 'Academic Expo', 'color' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                                    ['date' => 'Nov 05, 2026', 'day' => 'Thursday', 'title' => 'Parent-Teacher Meeting (PTM) & Result Distribution', 'type' => 'PTM', 'color' => 'bg-blue-50 text-blue-700 border-blue-200'],
                                    ['date' => 'Nov 18, 2026', 'day' => 'Wednesday', 'title' => 'Inter-House Athletics & Football Tournament', 'type' => 'Sports Meet', 'color' => 'bg-purple-50 text-purple-700 border-purple-200'],
                                    ['date' => 'Dec 12, 2026', 'day' => 'Saturday', 'title' => 'SEE Model Board Examination (Grade 10)', 'type' => 'Board Prep', 'color' => 'bg-rose-50 text-rose-700 border-rose-200'],
                                ];
                                foreach($presetEvents as $pe) {
                                    if (count($displayEvents) < 5) {
                                        $displayEvents[] = $pe;
                                    }
                                }
                            }
                        @endphp

                        @foreach($displayEvents as $ev)
                        <div class="p-3 rounded-xl bg-slate-50 hover:bg-white border border-slate-200 hover:border-slate-300 transition flex items-start gap-3">
                            <div class="w-12 h-12 rounded-xl bg-white border border-slate-200 flex flex-col items-center justify-center shrink-0 shadow-xs">
                                <span class="text-[9px] uppercase font-bold text-slate-500">{{ substr($ev['date'], 0, 3) }}</span>
                                <span class="text-sm font-extrabold text-slate-900 leading-none">{{ preg_replace('/[^0-9]/', '', substr($ev['date'], 4, 3)) }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-0.5">
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded border {{ $ev['color'] }}">
                                        {{ $ev['type'] }}
                                    </span>
                                    <span class="text-[11px] text-slate-500">{{ $ev['day'] }}</span>
                                </div>
                                <h5 class="font-bold text-xs text-slate-900 truncate leading-snug">{{ $ev['title'] }}</h5>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Exam Routine & Help Card --}}
                <div class="bg-slate-900 text-white rounded-2xl p-5 border border-slate-800 shadow-md">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] uppercase font-bold tracking-widest text-[#f36c21]">Board Routine Portal</span>
                        <i class="bi bi-file-earmark-arrow-down-fill text-[#f36c21] text-lg"></i>
                    </div>
                    <h4 class="font-extrabold text-sm mb-1 text-white">{{ $school->exam_title ?: 'Terminal Examination Routine' }}</h4>
                    <p class="text-xs text-slate-300 mb-3 line-clamp-2">
                        {{ $school->exam_instructions ?: 'Official subject-wise examination routine and reporting timings for all grades published.' }}
                    </p>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="openExamResultModal()" class="w-full py-2 bg-[#f36c21] hover:bg-[#e05d15] text-white font-bold rounded-xl text-center text-xs transition shadow-sm">
                            <i class="bi bi-mortarboard-fill"></i> Check Results
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═════════════════════════════════════════════════════════════════════
     6. ABOUT THE ACADEMY FEATURED OVERVIEW (WITH CAMPUS PHOTO)
══════════════════════════════════════════════════════════════════════ -->
<section class="py-16 bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-5 relative">
                <div class="relative rounded-2xl overflow-hidden shadow-lg border border-slate-200 aspect-[4/3] bg-white">
                    @php
                        $aboutImg = !empty($school->gallery_images[0]) 
                            ? asset('storage/' . $school->gallery_images[0]) 
                            : asset('imgs/hero/hero_1.webp');
                    @endphp
                    <img src="{{ $aboutImg }}" alt="{{ $school->name }} Campus" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-transparent to-transparent"></div>
                    <div class="absolute bottom-5 left-5 right-5 text-white">
                        <span class="text-xs uppercase tracking-widest font-bold text-orange-300">INSPIRED SCHOLARSHIP</span>
                        <h4 class="text-lg font-bold text-white">{{ $school->name }}</h4>
                    </div>
                </div>

                <div class="absolute -bottom-6 -right-4 sm:-bottom-8 sm:-right-6 bg-white text-slate-900 p-4 rounded-2xl shadow-xl border border-slate-200 max-w-xs hidden sm:block">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-orange-50 text-[#f36c21] flex items-center justify-center text-xl font-bold border border-orange-200">
                            <i class="bi bi-award-fill"></i>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-slate-900 block">SEE & NEB Accredited</span>
                            <span class="text-[11px] text-slate-500">Excellence in Board Results</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-7">
                <span class="text-xs font-bold tracking-widest uppercase text-slate-700 bg-slate-100 px-3.5 py-1 rounded-md border border-slate-200 inline-block mb-3">
                    Who We Are
                </span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mb-5 leading-tight">
                    Nurturing Intellect, Inspiring Character & Shaping Future Leaders
                </h2>
                <div class="text-slate-600 text-sm sm:text-base leading-relaxed space-y-4 mb-6">
                    @if($school->about_us && strlen($school->about_us) > 30)
                        <p>{{ Str::limit($school->about_us, 350) }}</p>
                    @elseif($school->description && strlen($school->description) > 30)
                        <p>{{ Str::limit($school->description, 350) }}</p>
                    @else
                        <p>
                            Welcome to <strong>{{ $school->name }}</strong>, a premier center of academic distinction dedicated to providing holistic, forward-looking education in Nepal. Our pedagogy harmonizes rigorous academic inquiry with moral values, critical reasoning, and leadership development.
                        </p>
                    @endif
                </div>

                <div class="flex items-center gap-4 flex-wrap">
                    <a href="{{ $pageUrl('about') }}" class="btn-primary-dark shadow-sm">
                        <span>Read Full Heritage & Mission</span> <i class="bi bi-arrow-right"></i>
                    </a>
                    <a href="{{ $pageUrl('facilities') }}" class="btn-secondary-white">
                        <span>Campus Infrastructure</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═════════════════════════════════════════════════════════════════════
     7. FROM THE PRINCIPAL'S DESK (WITH PRINCIPAL PHOTO)
══════════════════════════════════════════════════════════════════════ -->
<section class="py-16 bg-slate-50 border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl p-8 sm:p-12 border border-slate-200 shadow-md">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
                <div class="lg:col-span-4 flex flex-col items-center text-center">
                    <div class="relative">
                        <div class="w-52 h-52 sm:w-60 sm:h-60 rounded-2xl overflow-hidden shadow-lg border border-slate-200 p-1 bg-white">
                            <img src="{{ $school->principal_photo_url }}" 
                                 onerror="this.src='{{ asset('imgs/profile.webp') }}'" 
                                 alt="{{ $school->principal_name_resolved }}" 
                                 class="w-full h-full object-cover rounded-xl">
                        </div>
                        <div class="absolute -bottom-4 bg-slate-900 text-white text-xs font-bold px-4 py-1.5 rounded-full shadow-md">
                            Office of the Principal
                        </div>
                    </div>
                    <div class="mt-8">
                        <h3 class="font-bold text-xl text-slate-900">{{ $school->principal_name_resolved }}</h3>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mt-0.5">{{ $school->settings['principal_title'] ?? 'Head of Institution / Principal' }}</p>
                        <p class="text-xs text-[#f36c21] font-bold mt-1">{{ $school->name }}</p>
                    </div>
                </div>

                <div class="lg:col-span-8">
                    <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-slate-600 mb-2">
                        <i class="bi bi-quote text-2xl leading-none text-[#f36c21]"></i> Words of Welcome & Inspiration
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mb-5 leading-tight">
                        Leadership Address
                    </h2>
                    <div class="text-slate-700 leading-relaxed text-sm sm:text-base space-y-4">
                        @if($school->principal_message && strlen($school->principal_message) > 20)
                            <p class="italic text-slate-800 font-medium border-l-4 border-[#f36c21] pl-4 py-2 bg-slate-50 rounded-r-lg">
                                "{{ Str::limit($school->principal_message, 240) }}"
                            </p>
                        @else
                            <p class="italic text-slate-800 font-medium border-l-4 border-[#f36c21] pl-4 py-2 bg-slate-50 rounded-r-lg">
                                "Education is not merely the accumulation of facts; it is the training of the mind to think critically, the awakening of curiosity, and the cultivation of compassionate character."
                            </p>
                        @endif
                        <p>
                            At {{ $school->name }}, we believe in unlocking every student's distinctive potential through holistic mentoring, scientific inquiry, and global ethical awareness.
                        </p>
                    </div>

                    <div class="mt-8 pt-6 border-t border-slate-200 flex flex-wrap items-center gap-4">
                        <a href="{{ $pageUrl('about') }}#leadership" class="btn-primary-dark">
                            <span>Read Full Principal Message</span> <i class="bi bi-arrow-right"></i>
                        </a>
                        <a href="{{ url('admissions/apply?school='.$school->slug) }}" class="btn-secondary-white">
                            <i class="bi bi-mortarboard-fill text-[#f36c21]"></i> Apply for 2026/27
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═════════════════════════════════════════════════════════════════════
     8. CAMPUS INFRASTRUCTURE & FACILITIES PHOTO SHOWCASE
══════════════════════════════════════════════════════════════════════ -->
<section class="py-16 bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-4">
            <div>
                <span class="text-xs font-bold tracking-widest uppercase text-slate-700 bg-orange-50 text-[#f36c21] px-3.5 py-1 rounded-md border border-orange-200 inline-block mb-2">
                    Campus Infrastructure
                </span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900">
                    World-Class Learning Environment
                </h2>
                <p class="text-slate-600 text-sm mt-1 max-w-xl">
                    High-tech laboratories, modern classrooms, well-stocked library, and safe student transportation.
                </p>
            </div>
            <a href="{{ $pageUrl('facilities') }}" class="btn-primary-dark !text-xs !py-2.5 !px-4 flex items-center gap-1.5 shrink-0">
                <span>View All Facilities</span> &rarr;
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-sm card-lift group hover:border-[#f36c21] transition">
                <div class="aspect-[16/10] overflow-hidden bg-slate-100">
                    <img src="{{ asset('imgs/hero/hero_2.webp') }}" alt="Physics, Chemistry & Biology Science Labs" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                </div>
                <div class="p-5">
                    <h3 class="font-bold text-base text-slate-900 mb-1">Advanced STEM Science Labs</h3>
                    <p class="text-xs text-slate-600">Equipped with precision apparatus, optical microscopes, and safety gear for hands-on experimentation.</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-sm card-lift group hover:border-[#f36c21] transition">
                <div class="aspect-[16/10] overflow-hidden bg-slate-100">
                    <img src="{{ asset('imgs/hero/hero_3.webp') }}" alt="Computer & AI Innovation Center" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                </div>
                <div class="p-5">
                    <h3 class="font-bold text-base text-slate-900 mb-1">High-Speed Computer & AI Lab</h3>
                    <p class="text-xs text-slate-600">Gigabit fiber workstations, coding software, interactive displays, and digital literacy suites.</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-sm card-lift group hover:border-[#f36c21] transition">
                <div class="aspect-[16/10] overflow-hidden bg-slate-100">
                    <img src="{{ asset('imgs/hero/hero_1.webp') }}" alt="Central Knowledge Library" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                </div>
                <div class="p-5">
                    <h3 class="font-bold text-base text-slate-900 mb-1">Rich Knowledge Resource Library</h3>
                    <p class="text-xs text-slate-600">Thousands of curriculum volumes, international journals, encyclopedias, and quiet study alcoves.</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-sm card-lift group hover:border-[#f36c21] transition">
                <div class="aspect-[16/10] overflow-hidden bg-slate-100">
                    <img src="{{ asset('imgs/schools.webp') }}" alt="Sports Arena & Football Ground" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                </div>
                <div class="p-5">
                    <h3 class="font-bold text-base text-slate-900 mb-1">Multi-Sport Athletics Arena</h3>
                    <p class="text-xs text-slate-600">Spacious turf grounds for football, basketball court, table tennis, and specialized athletics coaching.</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-sm card-lift group hover:border-[#f36c21] transition">
                <div class="aspect-[16/10] overflow-hidden bg-slate-100">
                    <img src="{{ asset('imgs/kathmandu.webp') }}" alt="GPS Fleet School Transportation" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                </div>
                <div class="p-5">
                    <h3 class="font-bold text-base text-slate-900 mb-1">Safe GPS Fleet Transportation</h3>
                    <p class="text-xs text-slate-600">Dedicated school buses covering major routes with real-time GPS tracking and caring attendant staff.</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-sm card-lift group hover:border-[#f36c21] transition">
                <div class="aspect-[16/10] overflow-hidden bg-slate-100">
                    <img src="{{ file_exists(public_path('imgs/hero/school_hero_graduates.jpg')) ? asset('imgs/hero/school_hero_graduates.jpg') : asset('imgs/hero/hero_2.webp') }}" alt="Smart Interactive Digital Classrooms" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                </div>
                <div class="p-5">
                    <h3 class="font-bold text-base text-slate-900 mb-1">Smart Interactive AV Classrooms</h3>
                    <p class="text-xs text-slate-600">Multimedia projectors, smart boards, ergonomic seating, and natural ventilation for optimal learning.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═════════════════════════════════════════════════════════════════════
     9. CAMPUS LIFE PHOTO GALLERY PREVIEW (MAXIMUM IMAGES)
══════════════════════════════════════════════════════════════════════ -->
<section class="py-16 bg-slate-50 border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-4">
            <div>
                <span class="text-xs font-bold tracking-widest uppercase text-slate-700 bg-white px-3.5 py-1 rounded-md border border-slate-200 inline-block mb-2">
                    Visual Chronicle
                </span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900">
                    Life at {{ $school->name }}
                </h2>
                <p class="text-slate-600 text-sm mt-1 max-w-xl">
                    Snapshots of academic tournaments, cultural celebrations, laboratory experiments, and annual achievements.
                </p>
            </div>
            <a href="{{ $pageUrl('gallery') }}" class="btn-primary-dark !text-xs !py-2.5 !px-4 flex items-center gap-1.5 shrink-0">
                <span>Open Full Visual Gallery</span> &rarr;
            </a>
        </div>

        @php
            $galleryPreview = [
                ['url' => asset('imgs/hero/hero_1.webp'), 'title' => 'Main Academic Quadrangle', 'tag' => 'Campus Life'],
                ['url' => file_exists(public_path('imgs/hero/school_hero_graduates.jpg')) ? asset('imgs/hero/school_hero_graduates.jpg') : asset('imgs/hero/hero_2.webp'), 'title' => 'SEE & +2 Graduation Honours', 'tag' => 'Achievement'],
                ['url' => asset('imgs/hero/hero_2.webp'), 'title' => 'Robotics & Science Exhibition', 'tag' => 'STEM Fair'],
                ['url' => asset('imgs/hero/hero_3.webp'), 'title' => 'Early Years Sensory Discovery', 'tag' => 'Montessori'],
                ['url' => asset('imgs/schools.webp'), 'title' => 'Annual Cultural & Arts Gala', 'tag' => 'Cultural Fest'],
                ['url' => asset('imgs/kathmandu.webp'), 'title' => 'Educational Study Excursion', 'tag' => 'Field Study']
            ];
            if (!empty($school->gallery_images) && is_array($school->gallery_images)) {
                foreach($school->gallery_images as $i => $gImg) {
                    if ($i < 3) {
                        array_unshift($galleryPreview, [
                            'url' => asset('storage/' . $gImg),
                            'title' => 'Official Campus Event Record',
                            'tag' => 'Campus Life'
                        ]);
                    }
                }
            }
            $galleryPreview = array_slice($galleryPreview, 0, 6);
        @endphp

        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6">
            @foreach($galleryPreview as $item)
            <a href="{{ $pageUrl('gallery') }}" class="group relative rounded-2xl overflow-hidden shadow-sm border border-slate-200 bg-white aspect-[4/3] card-lift block">
                <img src="{{ $item['url'] }}" alt="{{ $item['title'] }}" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent opacity-80 group-hover:opacity-100 transition">
                    <div class="absolute bottom-3 left-3 right-3 text-white">
                        <span class="text-[9px] sm:text-[10px] uppercase font-bold tracking-wider bg-slate-900/80 backdrop-blur-sm px-2 py-0.5 rounded mb-1 inline-block border border-white/20">
                            {{ $item['tag'] }}
                        </span>
                        <h4 class="font-bold text-xs sm:text-sm leading-snug truncate">{{ $item['title'] }}</h4>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>

<!-- ═════════════════════════════════════════════════════════════════════
     10. NOTICES & QUICK ADMISSION CALL TO ACTION
══════════════════════════════════════════════════════════════════════ -->
<section class="py-16 bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-4">
            <div>
                <span class="text-xs font-bold uppercase tracking-widest text-slate-700 flex items-center gap-1.5">
                    <i class="bi bi-pin-angle-fill text-[#f36c21]"></i> Official Notice Board
                </span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mt-1">
                    Latest Circulars & Announcements
                </h2>
            </div>
            <a href="{{ $pageUrl('notices') }}" class="btn-secondary-white text-xs">
                <span>View All Notices Archive</span> &rarr;
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($recentNotices->take(4) as $rn)
            <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200 shadow-sm card-lift flex items-start gap-4 hover:border-[#f36c21] transition">
                <div class="w-12 h-12 rounded-xl bg-white text-slate-900 flex flex-col items-center justify-center shrink-0 border border-slate-200 shadow-xs">
                    <i class="bi bi-calendar3 text-xs text-[#f36c21]"></i>
                    <span class="text-[10px] font-bold mt-0.5">{{ $rn->created_at ? $rn->created_at->format('M d') : 'Recent' }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-white text-slate-800 uppercase tracking-wider mb-1 inline-block border border-slate-200">
                        Academic Notice
                    </span>
                    <h4 class="font-bold text-sm text-slate-900 line-clamp-2">
                        {{ $rn->notice }}
                    </h4>
                </div>
            </div>
            @empty
            <div class="bg-slate-50 rounded-2xl p-8 text-center text-slate-500 border border-slate-200 col-span-2 flex flex-col items-center justify-center">
                <div class="w-12 h-12 rounded-2xl bg-white text-[#f36c21] flex items-center justify-center text-xl mb-3 shadow-sm">
                    <i class="bi bi-bell-slash"></i>
                </div>
                <h4 class="font-bold text-slate-900 text-sm mb-1">No Active Circulars</h4>
                <p class="text-xs text-slate-500 max-w-sm mb-3">All academic announcements, examination routines, and official directives are updated promptly.</p>
                <a href="{{ $pageUrl('notices') }}" class="text-xs font-bold text-[#f36c21] hover:underline inline-flex items-center gap-1">
                    <span>Browse Notice Archive</span> <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            @endforelse
        </div>
    </div>
</section>

<!-- ═════════════════════════════════════════════════════════════════════
     11. ADMISSIONS CALL TO ACTION BANNER
══════════════════════════════════════════════════════════════════════ -->
<section class="py-16 bg-slate-900 text-white relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-6 relative z-10">
        <div>
            <span class="text-xs font-bold uppercase tracking-widest text-[#f36c21] bg-white/10 px-3 py-1 rounded-md mb-2 inline-block">
                Admissions Session 2026/27
            </span>
            <h3 class="text-2xl sm:text-3xl font-extrabold text-white mb-2">Begin Your Journey of Academic Distinction</h3>
            <p class="text-slate-300 text-sm max-w-xl leading-relaxed">
                Admissions are actively accepted for Preschool, Grades 1–10 (SEE), and +2 Science/Management. Scholarships available for meritorious achievers.
            </p>
        </div>
        <div class="flex items-center gap-3 shrink-0 flex-wrap">
            <a href="{{ url('admissions/apply?school='.$school->slug) }}" class="px-6 py-3.5 rounded-xl font-bold text-sm text-white bg-[#f36c21] hover:bg-[#e05d15] shadow-lg transition transform hover:-translate-y-0.5">
                <i class="bi bi-pencil-square mr-1"></i> Apply Online
            </a>
            <button onclick="openProspectusModal()" class="px-5 py-3.5 rounded-xl border border-white/20 text-white font-bold text-sm bg-white/10 hover:bg-white/20 transition flex items-center gap-2">
                <i class="bi bi-file-earmark-pdf text-[#f36c21]"></i> Prospectus
            </button>
            <a href="{{ $pageUrl('contact') }}" class="px-5 py-3.5 rounded-xl border border-white/20 text-white font-bold text-sm bg-white/10 hover:bg-white/20 transition">
                Contact Office
            </a>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
// ── Interactive Dynamic Academic Calendar ──
let currentCalDate = new Date();

const nepaliMonths = [
    'Baisakh', 'Jestha', 'Ashadh', 'Shrawan', 'Bhadra', 'Ashwin',
    'Kartik', 'Mangsir', 'Poush', 'Magh', 'Falgun', 'Chaitra'
];

const eventDatesInMonth = [
    { day: 5, type: 'exam', title: 'Unit Evaluation Test' },
    { day: 12, type: 'holiday', title: 'Public Holiday' },
    { day: 15, type: 'exam', title: 'Second Terminal Exam' },
    { day: 22, type: 'event', title: 'Inter-House Sports Meet' },
    { day: 28, type: 'event', title: 'Science & Robotics Fair' },
];

function renderAcademicCalendar() {
    const titleEl = document.getElementById('calendarMonthTitle');
    const gridEl  = document.getElementById('calendarDaysGrid');
    if (!titleEl || !gridEl) return;

    const year  = currentCalDate.getFullYear();
    const month = currentCalDate.getMonth();

    const monthNames = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];

    const bsMonth = nepaliMonths[(month + 8) % 12];
    const bsYear  = year + 57;

    titleEl.textContent = `${monthNames[month]} ${year} (${bsMonth} ${bsYear})`;

    const firstDayIndex = new Date(year, month, 1).getDay();
    const totalDays     = new Date(year, month + 1, 0).getDate();
    const today         = new Date();

    let html = '';

    // Empty cells before month start
    for (let i = 0; i < firstDayIndex; i++) {
        html += `<div class="p-2 text-slate-300"></div>`;
    }

    // Days of month
    for (let day = 1; day <= totalDays; day++) {
        const isToday = (today.getFullYear() === year && today.getMonth() === month && today.getDate() === day);
        const dayOfWeek = (firstDayIndex + day - 1) % 7;
        const isSaturday = (dayOfWeek === 6);

        const ev = eventDatesInMonth.find(e => e.day === day);

        let dotClass = '';
        if (ev) {
            if (ev.type === 'exam') dotClass = 'bg-[#f36c21]';
            else if (ev.type === 'holiday') dotClass = 'bg-rose-500';
            else dotClass = 'bg-emerald-500';
        }

        let bgClass = 'hover:bg-orange-50 border-slate-100 text-slate-800';
        if (isToday) {
            bgClass = 'bg-[#f36c21] text-white font-extrabold shadow-sm border-[#f36c21] hover:!bg-[#e05d15]';
        } else if (isSaturday) {
            bgClass = 'bg-slate-100 text-slate-400 border-slate-200';
        } else if (ev) {
            bgClass = 'bg-orange-50/70 border-orange-200 font-bold text-slate-900';
        }

        html += `
            <div class="relative p-1 sm:p-2 rounded-lg sm:rounded-xl border flex flex-col items-center justify-center min-h-[32px] sm:min-h-[38px] transition cursor-pointer ${bgClass}" 
                 title="${ev ? ev.title : (isSaturday ? 'Saturday Closed' : 'Academic Day')}">
                <span class="text-[11px] sm:text-xs leading-none ${isToday ? 'text-white' : ''}">${day}</span>
                ${dotClass && !isToday ? `<span class="w-1.5 h-1.5 rounded-full ${dotClass} mt-0.5 sm:mt-1"></span>` : ''}
            </div>
        `;
    }

    gridEl.innerHTML = html;
}

function changeCalMonth(offset) {
    currentCalDate.setMonth(currentCalDate.getMonth() + offset);
    renderAcademicCalendar();
}

document.addEventListener('DOMContentLoaded', function() {
    renderAcademicCalendar();
});
</script>
@endpush
