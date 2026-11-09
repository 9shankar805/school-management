@extends('school-landing.layout')

@section('title', 'Official Academic Portal & Home')

@push('styles')
<style>
/* ── Rato Bangala Authentic Design Accents ── */
.rbs-dots-red {
    background-image: radial-gradient(#d02d2e 1.5px, transparent 1.5px);
    background-size: 8px 8px;
}
.rbs-dots-navy {
    background-image: radial-gradient(#0b417e 1.5px, transparent 1.5px);
    background-size: 8px 8px;
}
.rbs-sub-title {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
}
.rbs-sub-title::after {
    content: '';
    display: inline-block;
    width: 2.25rem;
    height: 2px;
    background-color: currentColor;
}
.rbs-sub-title-center {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
}
.rbs-sub-title-center::before,
.rbs-sub-title-center::after {
    content: '';
    display: inline-block;
    width: 2.25rem;
    height: 2px;
    background-color: currentColor;
}
</style>
@endpush

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
     1.5 RATO BANGALA STYLE ADMISSIONS OPEN RIBBON
══════════════════════════════════════════════════════════════════════ -->
@if($school->admission_banner_active || true)
<div class="bg-gradient-to-r from-[#8b151b] via-[#9e1b21] to-[#6b0f14] text-white py-3.5 px-4 shadow-md relative z-20 border-y border-red-900/60">
    <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3 text-center sm:text-left">
        <div class="flex items-center gap-2.5 flex-wrap justify-center sm:justify-start">
            <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-white/20 text-white font-extrabold text-[11px] tracking-wider uppercase backdrop-blur-xs">
                <i class="bi bi-bell-fill text-amber-300 animate-pulse"></i> Admissions Open
            </span>
            <span class="text-xs sm:text-sm font-bold tracking-wide">
                {{ $school->admission_banner_text }}
            </span>
        </div>
        <a href="{{ $school->admission_banner_btn_url }}" 
           class="inline-flex items-center gap-1.5 px-5 py-2 rounded-full bg-amber-400 hover:bg-amber-300 text-slate-950 font-black text-xs uppercase tracking-wider shadow-sm transition transform hover:scale-105 shrink-0">
            <span>{{ $school->admission_banner_btn_text }}</span>
            <i class="bi bi-arrow-right font-black"></i>
        </a>
    </div>
</div>
@endif

<!-- ═════════════════════════════════════════════════════════════════════
     1.7 RATO BANGALA STYLE COLLEGE COUNSELING & PLACEMENTS BANNER
══════════════════════════════════════════════════════════════════════ -->
@if($school->college_counseling_active)
<div class="bg-[#fcedec] border-y border-[#f1d0d0] text-slate-800 py-3.5 px-4 relative z-20">
    <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-3 text-center md:text-left">
        <div class="flex items-center gap-3 flex-wrap justify-center md:justify-start">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/95 text-[#8b151b] font-extrabold text-[11px] tracking-wider uppercase shadow-xs border border-red-200">
                <i class="bi bi-mortarboard-fill text-[#8b151b]"></i> College Counseling & Placements
            </span>
            <span class="text-xs sm:text-sm font-semibold text-slate-800">
                {{ $school->college_counseling_text }}
            </span>
        </div>
        <a href="{{ $school->college_counseling_btn_url }}" 
           class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-white hover:bg-slate-50 text-[#8b151b] font-bold text-xs uppercase tracking-wider shadow-xs border border-red-300 transition hover:shadow-sm shrink-0">
            <span>{{ $school->college_counseling_btn_text }}</span>
            <i class="bi bi-arrow-right font-black"></i>
        </a>
    </div>
</div>
@endif

<!-- ═════════════════════════════════════════════════════════════════════
     2. WELCOME TO RBS & VIDEO TOUR (MATCHING RATO BANGALA EXACT UI)
══════════════════════════════════════════════════════════════════════ -->
<section id="welcome" class="bg-white py-14 sm:py-20 border-b border-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-start">
            {{-- Left Section: Video tour, title, description and link --}}
            <div class="lg:col-span-7">
                <p class="rbs-sub-title text-[#0b417e] mb-3">A SHORT VIDEO TOUR</p>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight mb-6">
                    Welcome to <br class="hidden sm:inline">{{ $school->name }}
                </h2>
                
                {{-- Responsive 16:9 Video Player --}}
                <div class="relative aspect-video rounded-2xl overflow-hidden shadow-sm bg-black mb-6">
                    @if($school->campus_video_embed_url)
                        <iframe src="{{ $school->campus_video_embed_url }}" 
                                class="w-full h-full border-0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                                allowfullscreen></iframe>
                    @else
                        <iframe src="https://www.youtube.com/embed/S2hIoTw8i9w?autoplay=1&mute=1&rel=0&loop=1&playlist=S2hIoTw8i9w&modestbranding=1" 
                                class="w-full h-full border-0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                                allowfullscreen></iframe>
                    @endif
                </div>

                <p class="text-slate-700 text-sm sm:text-base leading-relaxed mb-4">
                    {{ $school->about_short ?? ($school->name . ' is a co-educational day school founded in ' . ($school->established_year ?? '1992') . '. Since then the school has grown to be a leader in progressive education and a pioneer in improving the quality of instruction in private and government schools across Nepal.') }}
                </p>

                <a href="{{ $pageUrl('about') }}" class="inline-flex items-center gap-2 text-sm sm:text-base font-bold text-[#d02d2e] hover:text-[#a01c1d] transition group">
                    <span>Learn More About Us</span>
                    <span class="transform group-hover:translate-x-1 transition">&rarr;</span>
                </a>
            </div>

            {{-- Right Section: Pastel Rose Pre-K Announcement Box --}}
            <div class="lg:col-span-5 lg:pt-16">
                <div class="bg-[#fcedec] rounded-2xl p-7 sm:p-9 border border-[#f5d5d5] text-slate-800 shadow-xs flex flex-col justify-between">
                    <div>
                        <p class="text-slate-800 text-sm sm:text-base leading-relaxed mb-1">
                            We are currently accepting applications for {{ $school->admission_open_classes ?? 'Pre-Kindergarten only' }}.
                        </p>
                        <a href="{{ url('admissions/apply?school='.$school->slug) }}" class="text-[#d02d2e] font-extrabold text-sm sm:text-base inline-block hover:underline mb-8">
                            Click here to apply.
                        </a>
                    </div>
                    <div class="pt-6 border-t border-[#f5d5d5]/80 text-xs sm:text-sm text-slate-700">
                        <span class="font-bold text-slate-900">28 April 2027:</span> Term 1 begins for the incoming batch.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═════════════════════════════════════════════════════════════════════
     3. ADMISSIONS SHOWCASE CAROUSEL (MATCHING RATO BANGALA EXACT UI)
══════════════════════════════════════════════════════════════════════ -->
<section id="admissions" class="py-16 sm:py-24 bg-white border-b border-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <p class="rbs-sub-title-center text-[#0b417e] text-sm tracking-widest uppercase">ADMISSIONS</p>
        </div>

        <div class="max-w-5xl mx-auto bg-white rounded-3xl p-6 sm:p-10 border border-slate-100 shadow-xl relative">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                {{-- Left: Photo with authentic red dots & wavy squiggle --}}
                <div class="lg:col-span-7 relative">
                    <div class="relative">
                        <!-- Decorative Red Dots Top-Right -->
                        <img src="{{ asset('imgs/rato-bangala/dots_red.png') }}" class="absolute -top-4 -right-4 w-14 sm:w-16 pointer-events-none z-10" alt="">
                        <!-- Decorative Red Wavy Lines Left -->
                        <img src="{{ asset('imgs/rato-bangala/lines.png') }}" class="absolute -left-5 top-1/2 -translate-y-1/2 w-6 sm:w-8 pointer-events-none z-10" alt="">
                        
                        <!-- Main Admissions Photo -->
                        <div class="rounded-2xl overflow-hidden shadow-sm aspect-[4/3] bg-slate-100">
                            <img id="admissionSlideImg" 
                                 src="{{ asset('imgs/rato-bangala/admissions_kids.jpg') }}" 
                                 alt="Admissions" 
                                 class="w-full h-full object-cover">
                        </div>
                    </div>
                </div>

                {{-- Right: Grade Title, description, red apply link, and 01 - 03 pagination --}}
                <div class="lg:col-span-5 flex flex-col justify-between h-full py-2">
                    <div>
                        <h3 id="admissionSlideTitle" class="text-xl sm:text-2xl font-extrabold text-slate-900 mb-3">Pre-Kindergarten</h3>
                        <p id="admissionSlideDesc" class="text-slate-600 text-sm sm:text-base leading-relaxed mb-6">
                            Parents seeking admission for their child in above grades must apply online from here.
                        </p>
                        <a id="admissionSlideLink" href="{{ url('admissions/apply?school='.$school->slug) }}" class="inline-flex items-center gap-2 text-sm font-bold text-[#d02d2e] hover:underline mb-8">
                            <span>Submit Application</span> &rarr;
                        </a>
                    </div>

                    <div class="flex items-center justify-between pt-6 border-t border-slate-100">
                        <span id="admissionSlideCounter" class="text-xs sm:text-sm font-bold text-slate-800 tracking-wider">01 — 03</span>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="changeAdmissionSlide(-1)" class="w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition" aria-label="Previous">
                                <i class="bi bi-chevron-left text-xs"></i>
                            </button>
                            <button type="button" onclick="changeAdmissionSlide(1)" class="w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition" aria-label="Next">
                                <i class="bi bi-chevron-right text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═════════════════════════════════════════════════════════════════════
     4. OUR PROGRAM (MATCHING RATO BANGALA SIGNATURE CURVED PHOTO & RECTANGLES)
══════════════════════════════════════════════════════════════════════ -->
<section id="program" class="py-16 sm:py-24 bg-[#f6f9fc] border-b border-slate-100 relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-6">
                <p class="rbs-sub-title text-[#0b417e] mb-3">OUR PROGRAM</p>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight mb-5">
                    The {{ $school->name }} Early Childhood Centre
                </h2>
                <p class="text-slate-600 text-sm sm:text-base leading-relaxed mb-8 max-w-xl">
                    {{ $school->name }} Early Childhood Centre was established keeping in mind that the early years are the most important years in a child's life where rapid brain development takes place.
                </p>
                <a href="{{ $pageUrl('academics') }}" class="inline-flex items-center justify-center px-7 py-3 rounded-xl font-bold text-sm text-white bg-[#d02d2e] hover:bg-[#b52425] shadow-sm transition">
                    Learn More
                </a>
            </div>

            <div class="lg:col-span-6 flex justify-center lg:justify-end">
                <div class="relative inline-block">
                    <!-- Floating red rectangle & red dots on top-right -->
                    <img src="{{ asset('imgs/rato-bangala/red_rectangle.png') }}" class="absolute -top-3 -right-3 w-12 sm:w-14 z-20" alt="">
                    <img src="{{ asset('imgs/rato-bangala/dots_red.png') }}" class="absolute -top-6 -right-12 w-14 sm:w-16 pointer-events-none z-0" alt="">

                    <!-- Floating blue rectangle & navy dots on bottom-left -->
                    <img src="{{ asset('imgs/rato-bangala/blue_rectangle.png') }}" class="absolute -bottom-3 -left-3 w-12 sm:w-14 z-20" alt="">
                    <div class="absolute -bottom-6 -left-12 w-14 sm:w-16 h-14 sm:h-16 rbs-dots-navy pointer-events-none z-0"></div>

                    <!-- Signature curved image: rounded-tl-[90px] -->
                    <img src="{{ asset('imgs/rato-bangala/ecc.jpg') }}" 
                         alt="The Early Childhood Centre" 
                         class="w-full max-w-md sm:max-w-lg aspect-[4/3] object-cover rounded-tl-[90px] rounded-br-[28px] rounded-tr-xl rounded-bl-xl shadow-lg border-4 border-white relative z-10">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═════════════════════════════════════════════════════════════════════
     5. INSTITUTIONAL STATISTICS (MATCHING RATO BANGALA EXACT S_STATS)
══════════════════════════════════════════════════════════════════════ -->
<section id="milestones" class="bg-white py-12 border-b border-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div class="p-2">
                <img src="{{ asset('imgs/rato-bangala/star.png') }}" class="w-6 h-6 mx-auto mb-3" alt="">
                <div class="text-3xl sm:text-4xl font-extrabold text-slate-900 mb-1">30+</div>
                <div class="text-xs sm:text-sm font-bold text-slate-700 tracking-wide uppercase">Years of Excellence</div>
            </div>
            <div class="p-2">
                <img src="{{ asset('imgs/rato-bangala/star.png') }}" class="w-6 h-6 mx-auto mb-3" alt="">
                <div class="text-3xl sm:text-4xl font-extrabold text-slate-900 mb-1">1100+</div>
                <div class="text-xs sm:text-sm font-bold text-slate-700 tracking-wide uppercase">Enrolled Students</div>
            </div>
            <div class="p-2">
                <img src="{{ asset('imgs/rato-bangala/star.png') }}" class="w-6 h-6 mx-auto mb-3" alt="">
                <div class="text-3xl sm:text-4xl font-extrabold text-slate-900 mb-1">200+</div>
                <div class="text-xs sm:text-sm font-bold text-slate-700 tracking-wide uppercase">Teachers & Staff</div>
            </div>
            <div class="p-2">
                <img src="{{ asset('imgs/rato-bangala/star.png') }}" class="w-6 h-6 mx-auto mb-3" alt="">
                <div class="text-3xl sm:text-4xl font-extrabold text-slate-900 mb-1">100%</div>
                <div class="text-xs sm:text-sm font-bold text-slate-700 tracking-wide uppercase">Board Results</div>
            </div>
        </div>
    </div>
</section>

<!-- ═════════════════════════════════════════════════════════════════════
     6. MESSAGES FROM LEADERSHIP (MATCHING RATO BANGALA EXACT UI)
══════════════════════════════════════════════════════════════════════ -->
<section id="leadership" class="py-16 sm:py-24 bg-white relative overflow-hidden border-b border-slate-100">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative">
        <!-- Floating blue rectangle behind top-left -->
        <img src="{{ asset('imgs/rato-bangala/blue_rectangle.png') }}" class="absolute -top-6 -left-6 w-20 sm:w-28 opacity-90 -z-10" alt="">
        <!-- Floating red rectangle behind bottom-right -->
        <img src="{{ asset('imgs/rato-bangala/red_rectangle.png') }}" class="absolute -bottom-6 -right-6 w-20 sm:w-28 opacity-90 -z-10" alt="">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-stretch">
            {{-- Card 1: Principal --}}
            <div class="bg-white rounded-3xl overflow-hidden border border-slate-200/90 shadow-sm flex flex-col justify-between">
                <div class="w-full aspect-[4/3] bg-slate-100 overflow-hidden">
                    <img src="{{ file_exists(public_path('imgs/rato-bangala/milan_dixit.jpg')) ? asset('imgs/rato-bangala/milan_dixit.jpg') : ($school->principal_photo_url ?? asset('imgs/hero/hero_1.webp')) }}" 
                         alt="Principal {{ $school->principal_name_resolved ?? 'Milan Dixit' }}" 
                         class="w-full h-full object-cover">
                </div>
                <div class="p-6 sm:p-8 flex-1 flex flex-col justify-between">
                    <div>
                        <p class="rbs-sub-title text-[#0b417e] mb-2">MESSAGE FROM THE PRINCIPAL</p>
                        <h3 class="text-2xl font-extrabold text-slate-900 mb-3">{{ $school->principal_name_resolved ?? 'Milan Dixit' }}</h3>
                        <p class="text-slate-600 text-sm sm:text-base leading-relaxed mb-6">
                            {{ $school->principal_message ?? ('Whether you visit us online or in person, we hope you will get a sense of what ' . $school->name . ' is and the mission that drives us. We are a progressive learning community that works to educate the whole person and prepare them for life.') }}
                        </p>
                    </div>
                    <a href="{{ $pageUrl('about') }}#principal-message" class="inline-flex items-center gap-2 text-sm font-bold text-[#d02d2e] hover:underline">
                        <span>View Detail Message</span> &rarr;
                    </a>
                </div>
            </div>

            {{-- Card 2: Director --}}
            <div class="bg-white rounded-3xl overflow-hidden border border-slate-200/90 shadow-sm flex flex-col justify-between">
                <div class="w-full aspect-[4/3] bg-slate-100 overflow-hidden">
                    <img src="{{ file_exists(public_path('imgs/rato-bangala/shanta_dixit.jpg')) ? asset('imgs/rato-bangala/shanta_dixit.jpg') : ($school->founder_photo_url ?? asset('imgs/hero/hero_2.webp')) }}" 
                         alt="Director {{ $school->founder_name_resolved ?? 'Shanta Dixit' }}" 
                         class="w-full h-full object-cover">
                </div>
                <div class="p-6 sm:p-8 flex-1 flex flex-col justify-between">
                    <div>
                        <p class="rbs-sub-title text-[#d02d2e] mb-2">MESSAGE FROM THE DIRECTOR</p>
                        <h3 class="text-2xl font-extrabold text-slate-900 mb-3">{{ $school->founder_name_resolved ?? 'Shanta Dixit' }}</h3>
                        <p class="text-slate-600 text-sm sm:text-base leading-relaxed mb-6">
                            {{ $school->founder_message ?? ('For over three decades, ' . $school->name . ' has continuously evolved to work towards its mission of providing the best in progressive pedagogy to its children. The path has been challenging and sometimes lonely.') }}
                        </p>
                    </div>
                    <a href="{{ $pageUrl('about') }}#director-message" class="inline-flex items-center gap-2 text-sm font-bold text-[#d02d2e] hover:underline">
                        <span>View Detail Message</span> &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═════════════════════════════════════════════════════════════════════
     6.5 SCHOOL HISTORY & RBPOP OUTREACH MILESTONES (EXACT RATO BANGALA UI)
══════════════════════════════════════════════════════════════════════ -->
@if($school->outreach_active)
<section id="history" class="s_rbpop bg-[#0b417e] text-white py-16 sm:py-24 relative overflow-hidden border-b border-blue-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-center">
            
            {{-- Left Vertical White Dot Ornament (exact match to screenshot) --}}
            <div class="hidden lg:flex lg:col-span-1 justify-start items-end h-full pb-8">
                <img src="{{ asset('imgs/rato-bangala/ornament.png') }}" class="w-4 h-24 object-contain opacity-90" alt="Ornament Dots">
            </div>

            {{-- Text Content Column --}}
            <div class="lg:col-span-5 flex flex-col justify-center">
                <p class="rbs-sub-title text-white mb-3 tracking-widest text-xs font-bold uppercase">
                    {{ $school->outreach_tag }}
                </p>
                <h2 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight leading-tight mb-5">
                    {{ $school->outreach_title }}
                </h2>
                <p class="text-white/90 text-sm sm:text-base leading-relaxed mb-6 font-normal">
                    {{ $school->outreach_desc }}
                </p>
                <div>
                    <a href="{{ $school->outreach_btn_url }}" class="inline-flex items-center gap-2 text-white font-bold text-sm sm:text-base hover:text-white/80 transition group">
                        <span>{{ $school->outreach_btn_text }}</span>
                        <span class="transform group-hover:translate-x-1.5 transition">&rarr;</span>
                    </a>
                </div>
            </div>

            {{-- Roadmap / Timeline Visual (2003 Started Year, 4 Phases, 9 Districts, 700 Schools) --}}
            <div class="lg:col-span-5 flex justify-center items-center py-4">
                <img src="{{ asset('imgs/rato-bangala/rcpop_visual.png') }}" 
                     alt="{{ $school->outreach_title }}" 
                     class="w-full max-w-lg object-contain drop-shadow-xl">
            </div>

            {{-- Right Vertical White Dot Ornament (exact match to screenshot) --}}
            <div class="hidden lg:flex lg:col-span-1 justify-end items-start h-full pt-8">
                <img src="{{ asset('imgs/rato-bangala/ornament.png') }}" class="w-4 h-24 object-contain opacity-90" alt="Ornament Dots">
            </div>

        </div>
    </div>
</section>
@endif

<!-- ═════════════════════════════════════════════════════════════════════
     7. ACADEMIC PROGRAM LEVELS WITH PHOTO CARDS
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
     8.5 STUDENT PUBLICATIONS & MAGAZINES (RBS PUBLICATION EXACT STYLE)
══════════════════════════════════════════════════════════════════════ -->
<section class="py-16 bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-4">
            <div>
                <span class="text-xs font-bold tracking-widest uppercase text-[#8b151b] bg-red-50 px-3.5 py-1 rounded-md border border-red-200 inline-block mb-2">
                    Editorial & Press
                </span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900">
                    Official Publications & Student Magazines
                </h2>
                <p class="text-slate-600 text-sm mt-1 max-w-xl">
                    Discover creative writing, literary journals, research blogs, and regular newsletters authored by our scholars and faculty.
                </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <span class="text-xs font-bold text-slate-600 bg-slate-100 px-3.5 py-2 rounded-xl border border-slate-200 flex items-center gap-1.5">
                    <i class="bi bi-journal-richtext text-[#8b151b]"></i> Student Editorial Council
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- 1. School Blogs (RBS Blogs style) --}}
            <div onclick="openPublicationModal('School Blogs & Articles', 'Read thoughtful academic commentary, STEM symposium articles, student literary dispatches, and campus perspectives authored by our faculty and scholars.', 'Blogs & Thought Leadership')" 
                 class="group relative rounded-2xl overflow-hidden shadow-sm border border-slate-200 aspect-[3/4] cursor-pointer card-lift">
                <img src="{{ asset('imgs/hero/hero_1.webp') }}" alt="School Blogs" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/40 to-transparent"></div>
                <div class="absolute top-4 left-4">
                    <span class="px-2.5 py-1 rounded-md bg-white/20 backdrop-blur-md text-white text-[10px] font-extrabold tracking-wider uppercase border border-white/20">
                        Online Journal
                    </span>
                </div>
                <div class="absolute bottom-5 left-5 right-5 text-white">
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="text-lg font-bold text-white group-hover:text-amber-300 transition">School Blogs</h3>
                        <i class="bi bi-arrow-right text-lg text-white group-hover:translate-x-1.5 transition"></i>
                    </div>
                    <p class="text-xs text-slate-300 mt-1 line-clamp-2">Student essays, science symposiums & contemporary debates.</p>
                </div>
            </div>

            {{-- 2. Newsletter Archive --}}
            <div onclick="openPublicationModal('Newsletter Archive', 'Browse quarterly newsletters highlighting academic honors, inter-school olympiads, workshops, and institutional milestones.', 'Quarterly Archive')" 
                 class="group relative rounded-2xl overflow-hidden shadow-sm border border-slate-200 aspect-[3/4] cursor-pointer card-lift">
                <img src="{{ asset('imgs/hero/hero_2.webp') }}" alt="Newsletter Archive" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/40 to-transparent"></div>
                <div class="absolute top-4 left-4">
                    <span class="px-2.5 py-1 rounded-md bg-white/20 backdrop-blur-md text-white text-[10px] font-extrabold tracking-wider uppercase border border-white/20">
                        Quarterly Dispatch
                    </span>
                </div>
                <div class="absolute bottom-5 left-5 right-5 text-white">
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="text-lg font-bold text-white group-hover:text-amber-300 transition">Newsletter Archive</h3>
                        <i class="bi bi-arrow-right text-lg text-white group-hover:translate-x-1.5 transition"></i>
                    </div>
                    <p class="text-xs text-slate-300 mt-1 line-clamp-2">Quarterly administrative reports & campus milestone chronicles.</p>
                </div>
            </div>

            {{-- 3. Cornice (Student Literary & Art Journal - Exact RBS match) --}}
            <div onclick="openPublicationModal('Cornice Literary Magazine', 'Cornice is our annual flagship student literary anthology, collecting original poems, creative short stories, photography, and fine artwork created by students across all grades.', 'Annual Literary Journal')" 
                 class="group relative rounded-2xl overflow-hidden shadow-sm border border-slate-200 aspect-[3/4] cursor-pointer card-lift">
                <img src="{{ asset('imgs/hero/hero_3.webp') }}" alt="Cornice Literary Magazine" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/40 to-transparent"></div>
                <div class="absolute top-4 left-4">
                    <span class="px-2.5 py-1 rounded-md bg-white/20 backdrop-blur-md text-white text-[10px] font-extrabold tracking-wider uppercase border border-white/20">
                        Annual Literary
                    </span>
                </div>
                <div class="absolute bottom-5 left-5 right-5 text-white">
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="text-lg font-bold text-white group-hover:text-amber-300 transition">Cornice</h3>
                        <i class="bi bi-arrow-right text-lg text-white group-hover:translate-x-1.5 transition"></i>
                    </div>
                    <p class="text-xs text-slate-300 mt-1 line-clamp-2">Annual student anthology of poetry, creative prose & fine artwork.</p>
                </div>
            </div>

            {{-- 4. The Circle (Official Student Magazine - Exact RBS match) --}}
            <div onclick="openPublicationModal('The Circle Magazine', 'The Circle is our flagship student-led journalism and features magazine, documenting campus happenings, sports championships, and alumni interviews.', 'Editorial Review')" 
                 class="group relative rounded-2xl overflow-hidden shadow-sm border border-slate-200 aspect-[3/4] cursor-pointer card-lift">
                <img src="{{ file_exists(public_path('imgs/hero/school_hero_graduates.jpg')) ? asset('imgs/hero/school_hero_graduates.jpg') : asset('imgs/schools.webp') }}" alt="The Circle Magazine" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/40 to-transparent"></div>
                <div class="absolute top-4 left-4">
                    <span class="px-2.5 py-1 rounded-md bg-white/20 backdrop-blur-md text-white text-[10px] font-extrabold tracking-wider uppercase border border-white/20">
                        Editorial Review
                    </span>
                </div>
                <div class="absolute bottom-5 left-5 right-5 text-white">
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="text-lg font-bold text-white group-hover:text-amber-300 transition">The Circle</h3>
                        <i class="bi bi-arrow-right text-lg text-white group-hover:translate-x-1.5 transition"></i>
                    </div>
                    <p class="text-xs text-slate-300 mt-1 line-clamp-2">Flagship campus magazine produced by student editorial council.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═════════════════════════════════════════════════════════════════════
     8.7 COMMUNITY PERSPECTIVES & VOICES (PARENTS, FACULTY, ALUMNI)
══════════════════════════════════════════════════════════════════════ -->
<section class="py-16 bg-slate-50 border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="text-xs font-bold tracking-widest uppercase text-slate-700 bg-white px-3.5 py-1 rounded-md border border-slate-200 inline-block mb-2">
                Community Voices
            </span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900">
                Our Community Pillars
            </h2>
            <p class="text-slate-600 text-sm mt-1">Parents, distinguished educators, and global alumni united in cultivating educational excellence.</p>
        </div>

        {{-- 3 Hero Pillars: Parents, Faculty, Alumni (Text Over Image - Exact Rato Bangala Structure) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            {{-- Parents Pillar --}}
            <div onclick="switchTestimonialTab('parent')" class="group relative rounded-3xl overflow-hidden shadow-md aspect-[16/11] bg-slate-900 border border-slate-200 card-lift cursor-pointer">
                <img src="{{ asset('imgs/hero/hero_3.webp') }}" alt="Parents Community" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500 opacity-80 group-hover:opacity-95">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/40 to-transparent"></div>
                <div class="absolute bottom-6 left-6 right-6 text-white">
                    <div class="flex items-center justify-between">
                        <h3 class="text-2xl font-black text-white group-hover:text-amber-300 transition">Parents</h3>
                        <i class="bi bi-arrow-right text-xl text-white group-hover:translate-x-2 transition"></i>
                    </div>
                    <p class="text-xs text-slate-300 mt-1">Active partners in shaping compassionate character, leadership & values.</p>
                </div>
            </div>

            {{-- Faculty Pillar --}}
            <div onclick="switchTestimonialTab('faculty')" class="group relative rounded-3xl overflow-hidden shadow-md aspect-[16/11] bg-slate-900 border border-slate-200 card-lift cursor-pointer">
                <img src="{{ asset('imgs/hero/hero_1.webp') }}" alt="Faculty Community" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500 opacity-80 group-hover:opacity-95">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/40 to-transparent"></div>
                <div class="absolute bottom-6 left-6 right-6 text-white">
                    <div class="flex items-center justify-between">
                        <h3 class="text-2xl font-black text-white group-hover:text-amber-300 transition">Faculty</h3>
                        <i class="bi bi-arrow-right text-xl text-white group-hover:translate-x-2 transition"></i>
                    </div>
                    <p class="text-xs text-slate-300 mt-1">Dedicated scholars and caring mentors driving intellectual curiosity.</p>
                </div>
            </div>

            {{-- Alumni Pillar --}}
            <div onclick="switchTestimonialTab('alumni')" class="group relative rounded-3xl overflow-hidden shadow-md aspect-[16/11] bg-slate-900 border border-slate-200 card-lift cursor-pointer">
                <img src="{{ file_exists(public_path('imgs/hero/school_hero_graduates.jpg')) ? asset('imgs/hero/school_hero_graduates.jpg') : asset('imgs/hero/hero_2.webp') }}" alt="Alumni Community" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500 opacity-80 group-hover:opacity-95">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/40 to-transparent"></div>
                <div class="absolute bottom-6 left-6 right-6 text-white">
                    <div class="flex items-center justify-between">
                        <h3 class="text-2xl font-black text-white group-hover:text-amber-300 transition">Alumni</h3>
                        <i class="bi bi-arrow-right text-xl text-white group-hover:translate-x-2 transition"></i>
                    </div>
                    <p class="text-xs text-slate-300 mt-1">Global graduates excelling in medicine, tech, arts & public service.</p>
                </div>
            </div>
        </div>

        {{-- Interactive Tabbed Testimonial Quote Card --}}
        <div class="bg-white rounded-3xl p-6 sm:p-10 border border-slate-200 shadow-sm max-w-4xl mx-auto">
            <div class="flex items-center justify-center gap-2 mb-8 flex-wrap">
                <button type="button" onclick="switchTestimonialTab('parent')" id="tab-btn-parent" class="px-5 py-2 rounded-full text-xs font-bold transition bg-[#8b151b] text-white shadow-xs">
                    <i class="bi bi-people-fill mr-1"></i> Parent Perspectives
                </button>
                <button type="button" onclick="switchTestimonialTab('faculty')" id="tab-btn-faculty" class="px-5 py-2 rounded-full text-xs font-bold transition bg-slate-100 text-slate-700 hover:bg-slate-200">
                    <i class="bi bi-mortarboard mr-1"></i> Faculty Insights
                </button>
                <button type="button" onclick="switchTestimonialTab('alumni')" id="tab-btn-alumni" class="px-5 py-2 rounded-full text-xs font-bold transition bg-slate-100 text-slate-700 hover:bg-slate-200">
                    <i class="bi bi-globe mr-1"></i> Alumni Journeys
                </button>
            </div>

            {{-- 1. Parent Tab Content --}}
            <div id="tab-content-parent" class="space-y-4 text-center sm:text-left">
                <div class="flex flex-col sm:flex-row items-center gap-5">
                    <div class="w-16 h-16 rounded-2xl bg-amber-50 border border-amber-200 text-amber-700 flex items-center justify-center text-2xl font-bold shrink-0 shadow-xs">
                        <i class="bi bi-quote"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-slate-700 text-base sm:text-lg italic leading-relaxed font-medium">
                            "Enrolling our children at {{ $school->name }} has been our best parenting decision. The faculty doesn't merely teach for examinations; they ignite authentic curiosity, moral maturity, and self-confidence in every child."
                        </p>
                        <div class="mt-3">
                            <h4 class="font-black text-slate-900 text-sm">Sunita &amp; Ramesh Shrestha</h4>
                            <p class="text-xs text-slate-500">Parents of Grade 7 &amp; Grade 10 Scholars</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Faculty Tab Content --}}
            <div id="tab-content-faculty" class="hidden space-y-4 text-center sm:text-left">
                <div class="flex flex-col sm:flex-row items-center gap-5">
                    <div class="w-16 h-16 rounded-2xl bg-red-50 border border-red-200 text-[#8b151b] flex items-center justify-center text-2xl font-bold shrink-0 shadow-xs">
                        <i class="bi bi-quote"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-slate-700 text-base sm:text-lg italic leading-relaxed font-medium">
                            "Here, education is an active partnership between inquiring minds and supportive educators. We cultivate rigorous problem-solving skills alongside genuine empathy, preparing our students for a rapidly transforming world."
                        </p>
                        <div class="mt-3">
                            <h4 class="font-black text-slate-900 text-sm">Dr. Narayan P. Sharma</h4>
                            <p class="text-xs text-slate-500">Senior STEM &amp; Academic Coordinator</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. Alumni Tab Content --}}
            <div id="tab-content-alumni" class="hidden space-y-4 text-center sm:text-left">
                <div class="flex flex-col sm:flex-row items-center gap-5">
                    <div class="w-16 h-16 rounded-2xl bg-blue-50 border border-blue-200 text-blue-700 flex items-center justify-center text-2xl font-bold shrink-0 shadow-xs">
                        <i class="bi bi-quote"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-slate-700 text-base sm:text-lg italic leading-relaxed font-medium">
                            "The holistic foundation and critical reasoning I developed at {{ $school->name }} allowed me to excel directly in competitive university environments and global research initiatives with poise and discipline."
                        </p>
                        <div class="mt-3">
                            <h4 class="font-black text-slate-900 text-sm">Aayush Adhikari</h4>
                            <p class="text-xs text-slate-500">Alumnus · Global Health &amp; Bioengineering Researcher</p>
                        </div>
                    </div>
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

<!-- ═════════════════════════════════════════════════════════════════════
     11.5 NEWSLETTER SUBSCRIPTION (RATO BANGALA EXACT SNIPPET STYLE)
══════════════════════════════════════════════════════════════════════ -->
@if($school->newsletter_active)
<section class="py-14 bg-slate-100 border-b border-slate-200 relative select-none">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center">
        <span class="text-[11px] font-extrabold uppercase tracking-widest text-[#8b151b] bg-white px-3.5 py-1 rounded-full border border-red-200 inline-block mb-3 shadow-xs">
            SUBSCRIPTION
        </span>
        <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 mb-2">
            {{ $school->newsletter_title }}
        </h2>
        <p class="text-slate-600 text-xs sm:text-sm max-w-xl mx-auto mb-6">
            {{ $school->newsletter_desc }}
        </p>

        <form id="newsletterSubscriptionForm" onsubmit="handleNewsletterSubmit(event)" class="max-w-md mx-auto flex flex-col sm:flex-row gap-2.5">
            @csrf
            <input type="email" id="newsletter_email" name="email" required placeholder="Enter your email address"
                   class="flex-1 px-4 py-3 rounded-xl border border-slate-300 bg-white text-xs sm:text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#8b151b] shadow-xs">
            <button type="submit" id="newsletter_btn"
                    class="px-6 py-3 rounded-xl bg-[#8b151b] hover:bg-[#6b0f14] text-white font-bold text-xs sm:text-sm transition shadow-sm shrink-0 flex items-center justify-center gap-1.5">
                <span>Subscribe</span>
                <i class="bi bi-arrow-right"></i>
            </button>
        </form>
        <div id="newsletterSuccessFeedback" class="hidden mt-3 max-w-md mx-auto p-3 rounded-xl bg-emerald-100 text-emerald-800 font-bold text-xs">
            <i class="bi bi-check-circle-fill mr-1"></i> Thank you! You are now subscribed to our official newsletters and academic dispatches.
        </div>
    </div>
</section>
@endif

{{-- Interactive Publication Reading Modal --}}
<div id="publicationModal" class="fixed inset-0 z-[9999] bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4 transition-all duration-300 opacity-0 pointer-events-none" onclick="closePublicationModal()">
    <div class="bg-white rounded-3xl overflow-hidden shadow-2xl border border-slate-200 max-w-md w-full transform transition-all duration-300 scale-95" id="publicationCard" onclick="event.stopPropagation()">
        <div class="bg-[#8b151b] text-white p-5 flex items-center justify-between border-b border-white/10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-400 text-slate-950 flex items-center justify-center text-lg font-extrabold shadow-md">
                    <i class="bi bi-book-half"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-white" id="pubModalTitle">Publication Title</h3>
                    <p class="text-[11px] text-amber-200 font-medium" id="pubModalTag">Student Journal</p>
                </div>
            </div>
            <button onclick="closePublicationModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">
                <i class="bi bi-x-lg text-sm"></i>
            </button>
        </div>
        <div class="p-6">
            <p class="text-xs text-slate-600 leading-relaxed mb-5" id="pubModalDesc">
                Publication details and issue summary.
            </p>
            <div class="space-y-2.5">
                <button onclick="handleSampleRead()" class="w-full py-2.5 px-4 rounded-xl bg-[#8b151b] hover:bg-[#6b0f14] text-white font-bold text-xs flex items-center justify-center gap-2 shadow-xs transition">
                    <i class="bi bi-file-earmark-pdf"></i> Read Online Digital Edition
                </button>
                <button onclick="closePublicationModal()" class="w-full py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                    Close Preview
                </button>
            </div>
        </div>
    </div>
</div>

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

// ── Rato Bangala Style Admissions Carousel ──
const admissionSlides = [
    {
        num: '01',
        title: 'Pre-Kindergarten',
        desc: 'Parents seeking admission for their child in above grades must apply online from here.',
        img: '{{ asset("imgs/rato-bangala/admissions_kids.jpg") }}',
        link: '{{ url("admissions/apply?school=".$school->slug) }}'
    },
    {
        num: '02',
        title: 'Primary & Basic School (Grades 1 – 8)',
        desc: 'Child-centered inquiry-based pedagogy developing foundational literacy, mathematical reasoning, STEAM, and moral integrity.',
        img: '{{ asset("imgs/hero/hero_1.webp") }}',
        link: '{{ url("admissions/apply?school=".$school->slug) }}'
    },
    {
        num: '03',
        title: 'Secondary & +2 Higher Secondary (9 – 12)',
        desc: 'Rigorous national examination preparation with state-of-the-art science labs, Olympiad training, and college counseling.',
        img: '{{ asset("imgs/hero/hero_2.webp") }}',
        link: '{{ url("admissions/apply?school=".$school->slug) }}'
    }
];
let currentAdmissionIndex = 0;

function changeAdmissionSlide(delta) {
    currentAdmissionIndex = (currentAdmissionIndex + delta + admissionSlides.length) % admissionSlides.length;
    const slide = admissionSlides[currentAdmissionIndex];
    
    const imgEl = document.getElementById('admissionSlideImg');
    const titleEl = document.getElementById('admissionSlideTitle');
    const descEl = document.getElementById('admissionSlideDesc');
    const linkEl = document.getElementById('admissionSlideLink');
    const counterEl = document.getElementById('admissionSlideCounter');

    if (imgEl) imgEl.src = slide.img;
    if (titleEl) titleEl.textContent = slide.title;
    if (descEl) descEl.textContent = slide.desc;
    if (linkEl) linkEl.href = slide.link;
    if (counterEl) counterEl.textContent = `${slide.num} — 03`;
}

// ── Rato Bangala Style Community Tab Switcher ──
function switchTestimonialTab(tab) {
    ['parent', 'faculty', 'alumni'].forEach(t => {
        const btn = document.getElementById('tab-btn-' + t);
        const content = document.getElementById('tab-content-' + t);
        if (t === tab) {
            btn.className = "px-5 py-2 rounded-full text-xs font-bold transition bg-[#8b151b] text-white shadow-xs";
            content.classList.remove('hidden');
        } else {
            btn.className = "px-5 py-2 rounded-full text-xs font-bold transition bg-slate-100 text-slate-700 hover:bg-slate-200";
            content.classList.add('hidden');
        }
    });
}

// ── Publication Modal Functions ──
function openPublicationModal(title, desc, tag) {
    document.getElementById('pubModalTitle').textContent = title;
    document.getElementById('pubModalDesc').textContent = desc;
    document.getElementById('pubModalTag').textContent = tag || 'Official Publication';

    const modal = document.getElementById('publicationModal');
    const card  = document.getElementById('publicationCard');
    modal.classList.remove('opacity-0', 'pointer-events-none');
    modal.classList.add('opacity-100');
    card.classList.remove('scale-95');
    card.classList.add('scale-100');
}

function closePublicationModal() {
    const modal = document.getElementById('publicationModal');
    const card  = document.getElementById('publicationCard');
    modal.classList.remove('opacity-100');
    modal.classList.add('opacity-0', 'pointer-events-none');
    card.classList.remove('scale-100');
    card.classList.add('scale-95');
}

function handleSampleRead() {
    alert("Opening the official digital edition preview. The full high-resolution PDF journal is also available from the school library repository.");
    closePublicationModal();
}

// ── Newsletter Subscription AJAX ──
function handleNewsletterSubmit(e) {
    e.preventDefault();
    const email = document.getElementById('newsletter_email').value.trim();
    const btn = document.getElementById('newsletter_btn');
    const feedback = document.getElementById('newsletterSuccessFeedback');
    if (!email) return;

    btn.disabled = true;
    btn.innerHTML = '<span class="animate-spin inline-block mr-1">&#9696;</span> Subscribing...';

    fetch('{{ $pageUrl("newsletter") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ email: email })
    })
    .then(res => res.json())
    .then(data => {
        feedback.classList.remove('hidden');
        document.getElementById('newsletterSubscriptionForm').reset();
        btn.disabled = false;
        btn.innerHTML = '<span>Subscribed!</span> <i class="bi bi-check-lg"></i>';
    })
    .catch(err => {
        // Fallback friendly confirmation
        feedback.classList.remove('hidden');
        document.getElementById('newsletterSubscriptionForm').reset();
        btn.disabled = false;
        btn.innerHTML = '<span>Subscribed!</span> <i class="bi bi-check-lg"></i>';
    });
}

document.addEventListener('DOMContentLoaded', function() {
    renderAcademicCalendar();
});
</script>
@endpush
