<!DOCTYPE html>
<html lang="en" prefix="og: https://ogp.me/ns#">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

{{-- ── Subdomain Auto-SEO Engine ── --}}
@php
    $pageTitle = trim($__env->yieldContent('title'));
    $fullTitle = $pageTitle 
        ? $pageTitle . ' — ' . $school->name . ' | Nepal School Hub' 
        : $school->name . ' — Official Academic Portal & School Profile | Nepal School Hub';

    $seoDesc = $school->description && strlen($school->description) > 20 
        ? Str::limit(strip_tags($school->description), 160) 
        : $school->name . ' located in ' . ($school->city ?? 'Nepal') . ', ' . ($school->district ?? '') . '. Quality education, admissions, notices, exam results & online LMS portal powered by Nepal School Hub (NSH LMS).';

    $seoKeywords = implode(', ', array_filter([
        $school->name,
        $school->name . ' Nepal',
        $school->name . ' ' . ($school->city ?? ''),
        $school->name . ' portal',
        $school->name . ' admissions 2026',
        $school->name . ' LMS',
        'nepal school hub',
        'NSH LMS',
        'nshlms.com',
        ($school->city ?? '') . ' school management',
        ($school->district ?? '') . ' school software',
        'school portal Nepal'
    ]));
    $schoolLogoUrl = $school->logo ? asset('storage/' . $school->logo) : asset('imgs/logo_icon.webp');
    $canonicalUrl  = url()->current();

    // Smart Multi-Page Link Generator fallback
    $isSubdomain = request()->routeIs('school.subdomain*') || !str_contains(request()->path(), 'school/');
    if (!isset($pageUrl) || !is_callable($pageUrl)) {
        $pageUrl = function(string $page = '') use ($school, $isSubdomain): string {
            $page = ltrim($page, '/');
            if ($isSubdomain) {
                return $page ? url('/' . $page) : url('/');
            }
            return $page ? url('/school/' . $school->slug . '/' . $page) : url('/school/' . $school->slug);
        };
    }

    if (!isset($activeNav) || !is_callable($activeNav)) {
        $activeNav = function(string $page = '') use ($isSubdomain): bool {
            $path = trim(request()->path(), '/');
            if (empty($page)) {
                return request()->routeIs('school.landing') || request()->routeIs('school.subdomain') || $path === '' || preg_match('#^school/[^/]+$#', $path);
            }
            $cleanPage = trim($page, '/');
            return str_contains('/' . $path, '/' . $cleanPage) || request()->routeIs('*.' . $cleanPage) || request()->routeIs('school.landing.' . $cleanPage) || request()->routeIs('school.subdomain.' . $cleanPage);
        };
    }
@endphp

<title>{{ $fullTitle }}</title>
<meta name="description" content="{{ $seoDesc }}">
<meta name="keywords" content="{{ $seoKeywords }}">
<meta name="author" content="{{ $school->name }} / Nepal School Hub (NSH LMS)">
<meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large">
<meta name="googlebot" content="index, follow">
<link rel="canonical" href="{{ $canonicalUrl }}">
<link rel="icon" type="image/png" href="{{ $schoolLogoUrl }}">
<link rel="apple-touch-icon" href="{{ $schoolLogoUrl }}">

{{-- ── Open Graph Social Sharing ── --}}
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $school->name }} — Nepal School Hub">
<meta property="og:url" content="{{ $canonicalUrl }}">
<meta property="og:title" content="{{ $fullTitle }}">
<meta property="og:description" content="{{ $seoDesc }}">
<meta property="og:image" content="{{ $schoolLogoUrl }}">
<meta property="og:locale" content="en_US">

{{-- ── Twitter Card ── --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:site" content="@NepalSchoolHub">
<meta name="twitter:url" content="{{ $canonicalUrl }}">
<meta name="twitter:title" content="{{ $fullTitle }}">
<meta name="twitter:description" content="{{ $seoDesc }}">
<meta name="twitter:image" content="{{ $schoolLogoUrl }}">

{{-- ── Schema.org JSON-LD Structured Data ── --}}
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "EducationalOrganization",
  "name": "{{ $school->name }}",
  "url": "{{ $canonicalUrl }}",
  "logo": "{{ $schoolLogoUrl }}",
  "description": "{{ $seoDesc }}",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "{{ $school->address ?? '' }}",
    "addressLocality": "{{ $school->city ?? '' }}",
    "addressRegion": "{{ $school->province ?? '' }}",
    "addressCountry": "NP"
  },
  "telephone": "{{ $school->phone ?? '' }}",
  "email": "{{ $school->email ?? '' }}",
  "parentOrganization": {
    "@type": "EducationalOrganization",
    "name": "Nepal School Hub (NSH LMS)",
    "url": "https://nshlms.com"
  }
}
</script>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700;800;900&family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" crossorigin="anonymous">
<link rel="stylesheet" href="{{ asset('vendor/swiper/swiper-bundle.min.css') }}">
<script src="{{ asset('vendor/tailwind/tailwindcss.js') }}"></script>
<script>
    if (typeof tailwind !== 'undefined') {
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: {
                            100: 'var(--navy-100)',
                            500: 'var(--navy-500)',
                            600: 'var(--navy-600)',
                            700: 'var(--navy-700)',
                            800: 'var(--navy-800)',
                            900: 'var(--navy-900)',
                        },
                        gold: {
                            300: 'var(--gold-300)',
                            400: 'var(--gold-400)',
                            500: 'var(--gold-500)',
                            600: 'var(--gold-600)',
                        }
                    }
                }
            }
        };
    }
</script>
<script src="{{ asset('vendor/swiper/swiper-bundle.min.js') }}"></script>

<style>
:root {
    --brand-primary: {{ $school->primary_color_resolved ?? '#f36c21' }};
    --brand-secondary: {{ $school->secondary_color_resolved ?? '#e6b800' }};
    --brand-orange: {{ $school->primary_color_resolved ?? '#f36c21' }};
    --brand-orange-hover: {{ $school->primary_color_resolved ?? '#e05d15' }};
    --brand-dark: #0f172a;
    --brand-slate: #334155;
    --brand-border: #e2e8f0;
    --brand-light: #f8fafc;
    --brand-white: #ffffff;
}

/* Dynamic Brand Palette Override */
.bg-\[\#f36c21\] { background-color: var(--brand-primary) !important; }
.hover\:bg-\[\#e05d15\]:hover { filter: brightness(0.92); }
.text-\[\#f36c21\] { color: var(--brand-primary) !important; }
.border-\[\#f36c21\] { border-color: var(--brand-primary) !important; }
.decoration-\[\#f36c21\] { text-decoration-color: var(--brand-primary) !important; }
.focus\:ring-\[\#f36c21\]:focus { --tw-ring-color: var(--brand-primary) !important; }

html {
    scroll-behavior: smooth;
    overflow-x: hidden !important;
    width: 100% !important;
    max-width: 100vw !important;
}
body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    background: #ffffff;
    color: #0f172a;
    overflow-x: hidden !important;
    width: 100% !important;
    max-width: 100vw !important;
    position: relative;
}
*, *::before, *::after {
    box-sizing: border-box;
}
.font-cinzel { font-family: 'Cinzel', serif; }
.font-mono-code { font-family: 'JetBrains Mono', monospace; }

/* ── Minimalist White Navbar ── */
.navbar-glass {
    position: sticky;
    top: 0;
    z-index: 50;
    background: #ffffff;
    border-bottom: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04);
    transition: all 0.2s ease;
}

.nav-link-white {
    position: relative;
    font-weight: 600;
    font-size: 0.875rem;
    color: #334155;
    padding: 0.5rem 0.65rem;
    border-radius: 0.5rem;
    transition: all 0.2s ease;
    text-decoration: none;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    background: transparent;
    border: none;
    cursor: pointer;
    outline: none;
}
.nav-link-white:hover {
    color: #f36c21;
    background-color: #fff7ed;
}
.nav-link-white.active {
    color: #f36c21;
    background-color: #fff7ed;
    font-weight: 700;
}
.nav-link-white:focus-visible {
    outline: 2px solid #f36c21;
    outline-offset: 2px;
}

/* ── Navbar Hover Dropdown Menus ── */
.nav-dropdown {
    position: relative;
    display: inline-flex;
    align-items: center;
    height: 100%;
}
.nav-dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    min-width: 250px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    padding: 0.5rem;
    box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
    opacity: 0;
    visibility: hidden;
    transform: translateY(6px);
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    z-index: 70;
    pointer-events: none;
}
.nav-dropdown-menu::before {
    content: '';
    position: absolute;
    top: -10px;
    left: 0;
    width: 100%;
    height: 10px;
    background: transparent;
}
.nav-dropdown:hover .nav-dropdown-menu {
    opacity: 1;
    visibility: visible;
    transform: translateY(2px);
    pointer-events: auto;
}
.nav-dropdown:hover > .nav-link-white {
    color: #f36c21;
    background-color: #fff7ed;
}
.nav-dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.55rem 0.75rem;
    border-radius: 0.5rem;
    color: #334155;
    font-size: 0.8125rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.15s ease;
}
.nav-dropdown-item:hover {
    background: #fff7ed;
    color: #f36c21;
    transform: translateX(2px);
}
.nav-dropdown-item i {
    font-size: 0.95rem;
    color: #f36c21;
}

/* ── Swiper Fullscreen Custom Theme & Auto-Fit Stretch Engine ── */
.banner-container {
    width: 100% !important;
    position: relative;
    overflow: hidden;
}
.banner-swiper {
    width: 100% !important;
    height: 100% !important;
}
.banner-swiper .swiper-wrapper {
    width: 100% !important;
    height: 100% !important;
}
.banner-swiper .swiper-slide {
    width: 100% !important;
    height: 100% !important;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #020617;
}
.banner-swiper .swiper-slide img,
.banner-swiper .swiper-slide video,
.banner-slide-img {
    width: 100% !important;
    height: 100% !important;
    min-width: 100% !important;
    min-height: 100% !important;
    max-width: 100% !important;
    object-fit: cover !important;
    object-position: center center !important;
    display: block !important;
    image-rendering: -webkit-optimize-contrast;
}
.banner-swiper .swiper-pagination-bullet {
    width: 10px;
    height: 10px;
    background: rgba(255, 255, 255, 0.7);
    opacity: 0.8;
    transition: all 0.3s ease;
}
.banner-swiper .swiper-pagination-bullet-active {
    width: 30px;
    border-radius: 6px;
    background: #f36c21 !important;
    opacity: 1;
    box-shadow: 0 0 10px rgba(243, 108, 33, 0.8);
}
.banner-swiper .swiper-button-prev,
.banner-swiper .swiper-button-next {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.85);
    color: #0f172a;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.18);
    backdrop-filter: blur(8px);
    transition: all 0.2s ease;
}
.banner-swiper .swiper-button-prev:hover,
.banner-swiper .swiper-button-next:hover {
    background: #ffffff;
    color: #f36c21;
    transform: scale(1.08);
}
.banner-swiper .swiper-button-prev::after,
.banner-swiper .swiper-button-next::after {
    font-size: 1.15rem;
    font-weight: 800;
}

/* ── Floating Left Social Media Bar (Clean Bottom-Left Dock) ── */
.floating-social-bar {
    position: fixed;
    left: 1.25rem;
    bottom: 2rem;
    z-index: 40;
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
}
.social-circle-btn {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background-color: #ffffff;
    color: #334155;
    border: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    transition: all 0.2s ease;
    text-decoration: none;
}
.social-circle-btn:hover {
    background-color: #f36c21;
    color: #ffffff;
    border-color: #f36c21;
    transform: scale(1.08) translateX(2px);
}

/* ── Minimalist Buttons ── */
.btn-primary-dark {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background-color: #0f172a;
    color: #ffffff;
    border-radius: 0.75rem;
    font-weight: 700;
    font-size: 0.875rem;
    transition: all 0.2s ease;
    text-decoration: none;
    border: 1px solid #0f172a;
    cursor: pointer;
}
.btn-primary-dark:hover {
    background-color: #1e293b;
    color: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
}

.btn-brand-orange {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background-color: #f36c21;
    color: #ffffff;
    border-radius: 0.75rem;
    font-weight: 700;
    font-size: 0.875rem;
    transition: all 0.2s ease;
    text-decoration: none;
    border: 1px solid #f36c21;
    cursor: pointer;
}
.btn-brand-orange:hover {
    background-color: #e05d15;
    color: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(243, 108, 33, 0.25);
}

.btn-navy {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background-color: #0f172a;
    color: #ffffff;
    border-radius: 0.75rem;
    font-weight: 700;
    font-size: 0.875rem;
    transition: all 0.2s ease;
    text-decoration: none;
    border: 1px solid #0f172a;
    cursor: pointer;
}
.btn-navy:hover {
    background-color: #1e293b;
    color: #ffffff;
}

.btn-gold {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background-color: #f36c21;
    color: #ffffff;
    border-radius: 0.75rem;
    font-weight: 700;
    font-size: 0.875rem;
    transition: all 0.2s ease;
    text-decoration: none;
    border: 1px solid #f36c21;
    cursor: pointer;
}
.btn-gold:hover {
    background-color: #e05d15;
    color: #ffffff;
}

.btn-secondary-white {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background-color: #ffffff;
    color: #0f172a;
    border: 1px solid #cbd5e1;
    border-radius: 0.75rem;
    font-weight: 700;
    font-size: 0.875rem;
    transition: all 0.2s ease;
    text-decoration: none;
}
.btn-secondary-white:hover {
    background-color: #f8fafc;
    border-color: #94a3b8;
    color: #0f172a;
    transform: translateY(-1px);
}

/* ── Interactive Cards & Glassmorphism ── */
.card-lift {
    transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
}
.card-lift:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px -6px rgba(15, 23, 42, 0.08);
    border-color: #cbd5e1;
}

/* ── Marquee Animation ── */
@keyframes marquee {
    0%   { transform: translateX(100%); }
    100% { transform: translateX(-100%); }
}
.animate-marquee {
    display: inline-block;
    white-space: nowrap;
    animation: marquee 30s linear infinite;
}
.animate-marquee:hover { animation-play-state: paused; }

/* ── Tab Active Indicators ── */
.faculty-tab-btn.active {
    background-color: #0f172a !important;
    color: #ffffff !important;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.15);
}

/* ── Mobile Menu ── */
#mobile-menu {
    transition: max-height 0.35s ease, opacity 0.35s ease;
    max-height: 0;
    overflow: hidden;
    opacity: 0;
}
#mobile-menu.open { max-height: 700px; opacity: 1; }

/* ── Slide-out Mega Drawer ── */
#sideMegaDrawer {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    width: 88vw;
    max-width: 28rem;
    background: #ffffff;
    z-index: 9995;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    overflow-y: auto;
    transform: translateX(100%);
    visibility: hidden;
    pointer-events: none;
    transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.35s;
}
#sideMegaDrawer.drawer-open {
    transform: translateX(0) !important;
    visibility: visible !important;
    pointer-events: auto !important;
}

/* ── Curved Orange Footer ── */
.footer-curved-orange {
    background-color: #f36c21;
    color: #ffffff;
    border-top-left-radius: 40px;
    border-top-right-radius: 40px;
}
@media (min-width: 768px) {
    .footer-curved-orange {
        border-top-left-radius: 80px;
        border-top-right-radius: 80px;
    }
}
</style>
@stack('styles')
</head>
<body class="antialiased selection:bg-[#f36c21] selection:text-white bg-white pb-16 md:pb-0">


<!-- ═════════════════════════════════════════════════════════════════════
     2. FLOATING LEFT SOCIAL MEDIA DOCK (CLEAN WHITE)
══════════════════════════════════════════════════════════════════════ -->
<div class="floating-social-bar hidden md:flex" aria-label="Social Channels">
    <a href="{{ $school->facebook_url ?? 'https://facebook.com' }}" target="_blank" rel="noopener" class="social-circle-btn" title="Facebook">
        <i class="bi bi-facebook"></i>
    </a>
    <a href="{{ $school->youtube_url ?? 'https://youtube.com' }}" target="_blank" rel="noopener" class="social-circle-btn" title="YouTube">
        <i class="bi bi-youtube"></i>
    </a>
    <a href="{{ $school->instagram_url ?? 'https://instagram.com' }}" target="_blank" rel="noopener" class="social-circle-btn" title="Instagram">
        <i class="bi bi-instagram"></i>
    </a>
    @if($school->phone)
    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $school->phone) }}" target="_blank" rel="noopener" class="social-circle-btn" title="WhatsApp">
        <i class="bi bi-whatsapp"></i>
    </a>
    @endif
</div>

<!-- ═════════════════════════════════════════════════════════════════════
     3. STICKY INSTITUTIONAL NAVBAR (SAMRIDDHI SCHOOL EXACT SPEC)
══════════════════════════════════════════════════════════════════════ -->
<nav class="navbar-glass">
    <div class="max-w-7xl mx-auto px-3.5 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-20">
            {{-- School Brand Identity --}}
            <a href="{{ $pageUrl('') }}" class="flex items-center gap-2.5 sm:gap-3.5 group text-decoration-none min-w-0 flex-1 mr-2">
                @if($school->logo)
                    <img src="{{ asset('storage/' . $school->logo) }}" alt="{{ $school->name }} Logo"
                         class="h-10 w-10 sm:h-12 sm:w-12 rounded-xl object-cover border border-slate-200 shadow-sm p-0.5 bg-white group-hover:scale-105 transition duration-200 shrink-0">
                @else
                    <div class="h-10 w-10 sm:h-12 sm:w-12 rounded-xl flex items-center justify-center shadow-sm bg-[#f36c21] text-white shrink-0 font-cinzel font-bold text-base sm:text-lg">
                        {{ strtoupper(substr($school->name, 0, 2)) }}
                    </div>
                @endif
                <div class="min-w-0 flex-1">
                    <h1 class="text-sm sm:text-lg lg:text-xl font-extrabold tracking-tight text-slate-900 group-hover:text-[#f36c21] transition leading-tight truncate">
                        {{ $school->name }}
                    </h1>
                    <p class="text-[10px] sm:text-[11px] text-slate-500 font-medium flex items-center gap-1 mt-0.5 truncate">
                        <span>{{ ucwords(strtolower($school->city ?? ($school->district ?? 'Nepal'))) }}</span>
                        <span>·</span>
                        <span class="truncate">{{ $school->slogan ?? 'Excellence in Education & Character' }}</span>
                    </p>
                </div>
            </a>

            {{-- Institutional Navigation Links --}}
            <div class="hidden lg:flex items-center gap-1 xl:gap-1.5">
                <a href="{{ $pageUrl('') }}" class="nav-link-white {{ $activeNav('') ? 'active' : '' }}">
                    <span>Home</span>
                </a>

                {{-- About Us Dropdown --}}
                <div class="nav-dropdown">
                    <a href="{{ $pageUrl('about') }}" class="nav-link-white {{ $activeNav('about') ? 'active' : '' }}">
                        <span>About Us</span>
                        <i class="bi bi-chevron-down text-[10px] opacity-70"></i>
                    </a>
                    <div class="nav-dropdown-menu">
                        <a href="{{ $pageUrl('about') }}" class="nav-dropdown-item">
                            <i class="bi bi-shield-check"></i>
                            <div>
                                <div class="font-bold text-slate-900 leading-tight">School Profile</div>
                                <div class="text-[11px] text-slate-500 font-normal">History, vision & milestones</div>
                            </div>
                        </a>
                        <a href="{{ $pageUrl('about') }}#leadership" class="nav-dropdown-item">
                            <i class="bi bi-person-lines-fill"></i>
                            <div>
                                <div class="font-bold text-slate-900 leading-tight">Principal's Desk</div>
                                <div class="text-[11px] text-slate-500 font-normal">Leadership message & philosophy</div>
                            </div>
                        </a>
                        <a href="{{ $pageUrl('faculty') }}" class="nav-dropdown-item">
                            <i class="bi bi-people"></i>
                            <div>
                                <div class="font-bold text-slate-900 leading-tight">Faculty Directory</div>
                                <div class="text-[11px] text-slate-500 font-normal">Meet our master educators</div>
                            </div>
                        </a>
                        <a href="{{ $pageUrl('facilities') }}" class="nav-dropdown-item">
                            <i class="bi bi-buildings"></i>
                            <div>
                                <div class="font-bold text-slate-900 leading-tight">Campus Facilities</div>
                                <div class="text-[11px] text-slate-500 font-normal">Labs, library, sports & transport</div>
                            </div>
                        </a>
                    </div>
                </div>

                {{-- Programs Dropdown --}}
                <div class="nav-dropdown">
                    <a href="{{ $pageUrl('academics') }}" class="nav-link-white {{ $activeNav('academics') ? 'active' : '' }}">
                        <span>Programs</span>
                        <i class="bi bi-chevron-down text-[10px] opacity-70"></i>
                    </a>
                    <div class="nav-dropdown-menu !min-w-[270px]">
                        <a href="{{ $pageUrl('academics') }}#preschool" class="nav-dropdown-item">
                            <i class="bi bi-balloon"></i>
                            <div>
                                <div class="font-bold text-slate-900 leading-tight">Pre-Primary & Montessori</div>
                                <div class="text-[11px] text-slate-500 font-normal">Playgroup, Nursery, LKG, UKG</div>
                            </div>
                        </a>
                        <a href="{{ $pageUrl('academics') }}#primary" class="nav-dropdown-item">
                            <i class="bi bi-book"></i>
                            <div>
                                <div class="font-bold text-slate-900 leading-tight">Basic School (Grades 1–8)</div>
                                <div class="text-[11px] text-slate-500 font-normal">Foundational curriculum & STEAM</div>
                            </div>
                        </a>
                        <a href="{{ $pageUrl('academics') }}#secondary" class="nav-dropdown-item">
                            <i class="bi bi-journal-check"></i>
                            <div>
                                <div class="font-bold text-slate-900 leading-tight">Secondary SEE (Grades 9–10)</div>
                                <div class="text-[11px] text-slate-500 font-normal">National examination preparation</div>
                            </div>
                        </a>
                        <a href="{{ $pageUrl('academics') }}#plus2" class="nav-dropdown-item">
                            <i class="bi bi-mortarboard"></i>
                            <div>
                                <div class="font-bold text-slate-900 leading-tight">+2 Higher Secondary</div>
                                <div class="text-[11px] text-slate-500 font-normal">Science, Management & Humanities</div>
                            </div>
                        </a>
                        <div class="my-1 border-t border-slate-100"></div>
                        <a href="{{ $pageUrl('academics') }}" class="nav-dropdown-item !bg-orange-50 hover:!bg-orange-100">
                            <i class="bi bi-grid text-[#f36c21]"></i>
                            <div class="font-bold text-[#f36c21] leading-tight">View All Programs</div>
                        </a>
                    </div>
                </div>

                {{-- Admission Dropdown --}}
                <div class="nav-dropdown">
                    <a href="{{ $pageUrl('admissions') }}" class="nav-link-white {{ $activeNav('admissions') ? 'active' : '' }}">
                        <span>Admission</span>
                        <i class="bi bi-chevron-down text-[10px] opacity-70"></i>
                    </a>
                    <div class="nav-dropdown-menu !min-w-[260px]">
                        <a href="{{ $pageUrl('admissions') }}" class="nav-dropdown-item">
                            <i class="bi bi-calendar-check"></i>
                            <div>
                                <div class="font-bold text-slate-900 leading-tight">Admission Procedure</div>
                                <div class="text-[11px] text-slate-500 font-normal">Criteria, timeline & fees</div>
                            </div>
                        </a>
                        <a href="{{ $pageUrl('admissions') }}#scholarships" class="nav-dropdown-item">
                            <i class="bi bi-trophy"></i>
                            <div>
                                <div class="font-bold text-slate-900 leading-tight">Scholarships & Grants</div>
                                <div class="text-[11px] text-slate-500 font-normal">Merit & need-based waivers</div>
                            </div>
                        </a>
                        <a href="{{ url('admissions/apply?school='.$school->slug) }}" class="nav-dropdown-item !bg-[#f36c21] !text-white hover:!bg-[#e05d15]">
                            <i class="bi bi-pencil-square !text-white"></i>
                            <div>
                                <div class="font-bold text-white leading-tight">Apply Online Form</div>
                                <div class="text-[11px] text-orange-100 font-normal">Submit digital application</div>
                            </div>
                        </a>
                        <button type="button" onclick="openProspectusModal()" class="w-full text-left nav-dropdown-item">
                            <i class="bi bi-file-earmark-pdf"></i>
                            <div>
                                <div class="font-bold text-slate-900 leading-tight">Download Prospectus</div>
                                <div class="text-[11px] text-slate-500 font-normal">Digital brochure (PDF)</div>
                            </div>
                        </button>
                    </div>
                </div>

                {{-- News/Events Dropdown --}}
                <div class="nav-dropdown">
                    <a href="{{ $pageUrl('notices') }}" class="nav-link-white {{ $activeNav('notices') ? 'active' : '' }}">
                        <span>News/Events</span>
                        <i class="bi bi-chevron-down text-[10px] opacity-70"></i>
                    </a>
                    <div class="nav-dropdown-menu">
                        <a href="{{ $pageUrl('notices') }}" class="nav-dropdown-item">
                            <i class="bi bi-bell"></i>
                            <div>
                                <div class="font-bold text-slate-900 leading-tight">Notice Board</div>
                                <div class="text-[11px] text-slate-500 font-normal">Circulars & announcements</div>
                            </div>
                        </a>
                        <a href="{{ $pageUrl('gallery') }}" class="nav-dropdown-item">
                            <i class="bi bi-images"></i>
                            <div>
                                <div class="font-bold text-slate-900 leading-tight">Photo & Event Gallery</div>
                                <div class="text-[11px] text-slate-500 font-normal">Campus life and activities</div>
                            </div>
                        </a>
                        <button type="button" onclick="openExamResultModal()" class="w-full text-left nav-dropdown-item">
                            <i class="bi bi-mortarboard"></i>
                            <div>
                                <div class="font-bold text-slate-900 leading-tight">Exam Results</div>
                                <div class="text-[11px] text-slate-500 font-normal">Check student marksheets</div>
                            </div>
                        </button>
                    </div>
                </div>

                {{-- Career Trigger --}}
                <button type="button" onclick="openCareerModal()" class="nav-link-white">
                    <span>Career</span>
                </button>
            </div>

            {{-- Right Actions (Online Application CTA, Hamburger Mega Drawer, Login Dropdown) --}}
            <div class="hidden lg:flex items-center gap-2 xl:gap-3 shrink-0">
                {{-- Online Application CTA Button --}}
                <a href="{{ url('admissions/apply?school='.$school->slug) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-[#f36c21] hover:bg-[#e05d15] shadow-sm hover:shadow transition transform hover:-translate-y-0.5">
                    <i class="bi bi-pencil-square text-xs"></i>
                    <span>Online Application</span>
                </a>

                {{-- Hamburger Mega Menu Drawer Trigger (☰) --}}
                <button type="button" onclick="toggleSideDrawer()" aria-label="Open School Information Drawer" class="w-9 h-9 flex items-center justify-center text-slate-700 hover:text-[#f36c21] hover:bg-orange-50 border border-slate-200 rounded-xl transition">
                    <i class="bi bi-list text-xl font-bold"></i>
                </button>

                {{-- Login Dropdown with Padlock Icon (🔒 Login ∨) --}}
                <div class="nav-dropdown">
                    <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-slate-800 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition">
                        <i class="bi bi-lock-fill text-slate-700 text-xs"></i>
                        <span>Login</span>
                        <i class="bi bi-chevron-down text-[9px] text-slate-500"></i>
                    </button>
                    <div class="nav-dropdown-menu !right-0 !left-auto !min-w-[220px]">
                        <a href="{{ url('login') }}" class="nav-dropdown-item">
                            <i class="bi bi-person-circle"></i>
                            <div>
                                <div class="font-bold text-slate-900">Student Portal</div>
                                <div class="text-[10px] text-slate-500">Results, attendance & courses</div>
                            </div>
                        </a>
                        <a href="{{ url('login') }}" class="nav-dropdown-item">
                            <i class="bi bi-people-fill"></i>
                            <div>
                                <div class="font-bold text-slate-900">Parent Portal</div>
                                <div class="text-[10px] text-slate-500">Fees, progress & communication</div>
                            </div>
                        </a>
                        <a href="{{ url('login') }}" class="nav-dropdown-item">
                            <i class="bi bi-person-badge-fill"></i>
                            <div>
                                <div class="font-bold text-slate-900">Teacher & Staff</div>
                                <div class="text-[10px] text-slate-500">Class management & marks</div>
                            </div>
                        </a>
                        <div class="my-1 border-t border-slate-100"></div>
                        <a href="{{ url('login') }}" class="nav-dropdown-item !bg-slate-900 !text-white hover:!bg-slate-800">
                            <i class="bi bi-shield-lock-fill !text-amber-400"></i>
                            <div>
                                <div class="font-bold text-white">Admin Management</div>
                                <div class="text-[10px] text-slate-300">NSH LMS School ERP</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Mobile Actions --}}
            <div class="flex lg:hidden items-center gap-2 shrink-0">
                <a href="{{ url('admissions/apply?school='.$school->slug) }}" class="hidden sm:inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-[#f36c21] hover:bg-[#e05d15] shadow-xs transition">
                    Apply
                </a>
                <button id="mobile-toggle" aria-label="Toggle menu" class="w-10 h-10 rounded-xl border border-slate-200 text-slate-700 hover:text-[#f36c21] hover:bg-orange-50 flex items-center justify-center transition focus:outline-none">
                    <i class="bi bi-list text-2xl font-bold" id="hamburger-icon"></i>
                </button>
            </div>
        </div>

        {{-- Mobile dropdown menu --}}
        <div id="mobile-menu" class="lg:hidden">
            <div class="py-3 flex flex-col gap-1 border-t border-slate-100">
                <div class="flex items-center gap-3 px-3 py-2.5 bg-slate-50 rounded-2xl mb-2 border border-slate-200">
                    @if($school->logo)
                        <img src="{{ asset('storage/' . $school->logo) }}" alt="{{ $school->name }}" class="h-10 w-10 rounded-xl object-cover border border-slate-200 p-0.5 bg-white shrink-0 shadow-sm">
                    @else
                        <div class="h-10 w-10 rounded-xl bg-[#f36c21] text-white font-cinzel font-bold text-sm flex items-center justify-center shrink-0 shadow-sm">
                            {{ strtoupper(substr($school->name, 0, 2)) }}
                        </div>
                    @endif
                    <div class="min-w-0">
                        <div class="font-bold text-xs text-slate-900 truncate">{{ $school->name }}</div>
                        <div class="text-[10px] text-slate-500 font-semibold tracking-wider uppercase">{{ $school->slogan ?? 'Academic Portal' }}</div>
                    </div>
                </div>
                <a href="{{ $pageUrl('') }}" class="px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-orange-50 rounded-lg {{ $activeNav('') ? 'bg-orange-50 font-bold text-[#f36c21]' : '' }} flex items-center gap-2.5">
                    <i class="bi bi-house text-[#f36c21]"></i> Home
                </a>
                <a href="{{ $pageUrl('about') }}" class="px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-orange-50 rounded-lg {{ $activeNav('about') ? 'bg-orange-50 font-bold text-[#f36c21]' : '' }} flex items-center gap-2.5">
                    <i class="bi bi-shield-check text-[#f36c21]"></i> About Us
                </a>
                <a href="{{ $pageUrl('academics') }}" class="px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-orange-50 rounded-lg {{ $activeNav('academics') ? 'bg-orange-50 font-bold text-[#f36c21]' : '' }} flex items-center gap-2.5">
                    <i class="bi bi-mortarboard text-[#f36c21]"></i> Programs
                </a>
                <a href="{{ $pageUrl('admissions') }}" class="px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-orange-50 rounded-lg {{ $activeNav('admissions') ? 'bg-orange-50 font-bold text-[#f36c21]' : '' }} flex items-center gap-2.5">
                    <i class="bi bi-journal-check text-[#f36c21]"></i> Admission
                </a>
                <button type="button" onclick="openCareerModal()" class="px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-orange-50 rounded-lg flex items-center gap-2.5 text-left w-full">
                    <i class="bi bi-briefcase text-[#f36c21]"></i> Career
                </button>
                <a href="{{ $pageUrl('notices') }}" class="px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-orange-50 rounded-lg {{ $activeNav('notices') ? 'bg-orange-50 font-bold text-[#f36c21]' : '' }} flex items-center gap-2.5">
                    <i class="bi bi-bell text-[#f36c21]"></i> News/Events
                </a>
                <a href="{{ url('admissions/apply?school='.$school->slug) }}" class="px-3 py-2 text-sm font-bold text-[#f36c21] hover:bg-orange-50 rounded-lg flex items-center gap-2.5">
                    <i class="bi bi-pencil-square text-[#f36c21]"></i> Online Application
                </a>
                <div class="grid grid-cols-2 gap-2 pt-2 mt-1 border-t border-slate-100">
                    <button type="button" onclick="openProspectusModal()" class="py-2 text-xs font-semibold text-center border border-slate-200 bg-white text-slate-800 rounded-lg flex items-center justify-center gap-1.5">
                        <i class="bi bi-file-earmark-pdf"></i> Prospectus
                    </button>
                    <button type="button" onclick="openExamResultModal()" class="py-2 text-xs font-semibold text-center border border-slate-200 bg-white text-slate-800 rounded-lg flex items-center justify-center gap-1.5">
                        <i class="bi bi-mortarboard"></i> Check Result
                    </button>
                </div>
                <div class="grid grid-cols-2 gap-2 pt-2">
                    <a href="{{ url('login') }}" class="py-2.5 text-xs font-bold text-center border border-slate-300 bg-white text-slate-700 rounded-lg flex items-center justify-center gap-1.5">
                        <i class="bi bi-lock-fill"></i> Portal Login
                    </a>
                    <a href="{{ url('admissions/apply?school='.$school->slug) }}" class="py-2.5 text-xs font-bold text-center bg-[#f36c21] text-white rounded-lg flex items-center justify-center gap-1.5 shadow-sm">
                        <i class="bi bi-pencil-square"></i> Apply Online
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- ═════════════════════════════════════════════════════════════════════
     SLIDE-OUT MEGA DRAWER (TRIGGERED BY ☰ HAMBURGER MENU)
══════════════════════════════════════════════════════════════════════ -->
<div id="sideDrawerBackdrop" onclick="toggleSideDrawer()" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-[9990] transition-opacity duration-300 opacity-0 pointer-events-none"></div>
<div id="sideMegaDrawer" class="fixed top-0 right-0 bottom-0 w-[88vw] sm:max-w-md bg-white z-[9995] shadow-2xl overflow-y-auto">
    <div class="p-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-200">
            <div class="flex items-center gap-3">
                @if($school->logo)
                    <img src="{{ asset('storage/' . $school->logo) }}" alt="{{ $school->name }}" class="h-11 w-11 rounded-xl object-cover border border-slate-200 p-0.5 bg-white shadow-sm">
                @else
                    <div class="h-11 w-11 rounded-xl bg-[#f36c21] text-white font-cinzel font-bold text-base flex items-center justify-center shadow-sm">
                        {{ strtoupper(substr($school->name, 0, 2)) }}
                    </div>
                @endif
                <div>
                    <h3 class="font-extrabold text-sm text-slate-900 leading-tight">{{ $school->name }}</h3>
                    <p class="text-[11px] text-slate-500 font-medium">{{ $school->city ?? 'Nepal' }} · ESTD {{ $school->established_year ?? '2004' }}</p>
                </div>
            </div>
            <button type="button" onclick="toggleSideDrawer()" class="w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition">
                <i class="bi bi-x-lg text-base"></i>
            </button>
        </div>

        <div class="py-5 space-y-4">
            <div>
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2.5">Academic Pathways</h4>
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ $pageUrl('academics') }}#preschool" class="p-3 bg-slate-50 hover:bg-orange-50 border border-slate-200 hover:border-orange-200 rounded-xl transition group">
                        <i class="bi bi-balloon text-[#f36c21] text-lg block mb-1"></i>
                        <span class="text-xs font-bold text-slate-800 group-hover:text-[#f36c21] block">Pre-Primary</span>
                        <span class="text-[10px] text-slate-500">Montessori</span>
                    </a>
                    <a href="{{ $pageUrl('academics') }}#primary" class="p-3 bg-slate-50 hover:bg-orange-50 border border-slate-200 hover:border-orange-200 rounded-xl transition group">
                        <i class="bi bi-book text-[#f36c21] text-lg block mb-1"></i>
                        <span class="text-xs font-bold text-slate-800 group-hover:text-[#f36c21] block">Basic School</span>
                        <span class="text-[10px] text-slate-500">Grades 1–8</span>
                    </a>
                    <a href="{{ $pageUrl('academics') }}#secondary" class="p-3 bg-slate-50 hover:bg-orange-50 border border-slate-200 hover:border-orange-200 rounded-xl transition group">
                        <i class="bi bi-journal-check text-[#f36c21] text-lg block mb-1"></i>
                        <span class="text-xs font-bold text-slate-800 group-hover:text-[#f36c21] block">Secondary SEE</span>
                        <span class="text-[10px] text-slate-500">Grades 9–10</span>
                    </a>
                    <a href="{{ $pageUrl('academics') }}#plus2" class="p-3 bg-slate-50 hover:bg-orange-50 border border-slate-200 hover:border-orange-200 rounded-xl transition group">
                        <i class="bi bi-mortarboard text-[#f36c21] text-lg block mb-1"></i>
                        <span class="text-xs font-bold text-slate-800 group-hover:text-[#f36c21] block">+2 College</span>
                        <span class="text-[10px] text-slate-500">NEB Streams</span>
                    </a>
                </div>
            </div>

            <div>
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2.5">Campus Helpline & Office</h4>
                <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200 space-y-2.5 text-xs text-slate-700">
                    <p class="flex items-start gap-2.5">
                        <i class="bi bi-geo-alt-fill text-[#f36c21] mt-0.5 text-sm shrink-0"></i>
                        <span>{{ $school->address ?? ($school->city . ', ' . $school->district) }}</span>
                    </p>
                    <p class="flex items-center gap-2.5">
                        <i class="bi bi-telephone-fill text-[#f36c21] text-sm shrink-0"></i>
                        <span>{{ $school->phone ?? 'Contact Office' }}</span>
                    </p>
                    <p class="flex items-center gap-2.5">
                        <i class="bi bi-envelope-fill text-[#f36c21] text-sm shrink-0"></i>
                        <span>{{ $school->email ?? 'info@school.edu.np' }}</span>
                    </p>
                    <p class="flex items-center gap-2.5">
                        <i class="bi bi-clock-fill text-[#f36c21] text-sm shrink-0"></i>
                        <span>Sun – Fri: 9:00 AM – 4:30 PM (Saturday Closed)</span>
                    </p>
                </div>
            </div>

            <div class="space-y-2 pt-2">
                <a href="{{ url('admissions/apply?school='.$school->slug) }}" class="w-full py-3 bg-[#f36c21] hover:bg-[#e05d15] text-white font-bold rounded-xl text-center flex items-center justify-center gap-2 shadow-sm text-xs transition">
                    <i class="bi bi-pencil-square"></i> Fill Online Admission Form
                </a>
                <button type="button" onclick="openProspectusModal()" class="w-full py-2.5 bg-white border border-slate-300 text-slate-800 hover:bg-slate-50 font-bold rounded-xl text-center flex items-center justify-center gap-2 text-xs transition">
                    <i class="bi bi-file-earmark-pdf text-[#f36c21]"></i> Download Digital Prospectus
                </button>
                <button type="button" onclick="openCareerModal()" class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-xl text-center flex items-center justify-center gap-2 text-xs transition">
                    <i class="bi bi-briefcase text-slate-700"></i> Career & Teaching Vacancies
                </button>
            </div>
        </div>
    </div>
</div>


<!-- ═════════════════════════════════════════════════════════════════════
     MAIN PAGE CONTENT INJECTION
══════════════════════════════════════════════════════════════════════ -->
<main class="pb-12 bg-white">
    @yield('content')
</main>

<!-- ═════════════════════════════════════════════════════════════════════
     INSTITUTIONAL FOOTER (SIGNATURE CURVED ORANGE - SAMRIDDHI SPEC)
══════════════════════════════════════════════════════════════════════ -->
<footer class="footer-curved-orange pt-14 pb-8 mt-12 relative select-none">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-10 lg:gap-12 mb-12">
            
            {{-- Column 1: Logo + Slogan Quote + Follow Us --}}
            <div class="md:col-span-5 lg:col-span-5 space-y-4">
                <div class="bg-white p-3 rounded-2xl w-24 h-24 sm:w-28 sm:h-28 flex items-center justify-center shadow-lg">
                    @if($school->logo)
                    <img src="{{ asset('storage/' . $school->logo) }}" alt="{{ $school->name }} Crest" class="w-full h-full object-contain">
                    @else
                    <div class="w-full h-full bg-[#f36c21] text-white flex items-center justify-center rounded-xl font-cinzel font-bold text-2xl">
                        {{ strtoupper(substr($school->name, 0, 2)) }}
                    </div>
                    @endif
                </div>

                <p class="text-white text-base sm:text-lg font-medium leading-snug italic max-w-sm">
                    “{{ $school->slogan ?? 'Helping Students Grow Into Leaders of Today and Tomorrow' }}”
                </p>

                <div class="pt-2">
                    <h5 class="text-white font-bold text-lg mb-3">Follow Us</h5>
                    <div class="flex items-center gap-3">
                        <a href="{{ $school->facebook_url ?? 'https://facebook.com' }}" target="_blank" rel="noopener" class="w-9 h-9 rounded-lg bg-white/10 hover:bg-white text-white hover:text-[#f36c21] flex items-center justify-center transition border border-white/20 text-base">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="{{ $school->youtube_url ?? 'https://youtube.com' }}" target="_blank" rel="noopener" class="w-9 h-9 rounded-lg bg-white/10 hover:bg-white text-white hover:text-[#f36c21] flex items-center justify-center transition border border-white/20 text-base">
                            <i class="bi bi-youtube"></i>
                        </a>
                        <a href="{{ $school->instagram_url ?? 'https://instagram.com' }}" target="_blank" rel="noopener" class="w-9 h-9 rounded-lg bg-white/10 hover:bg-white text-white hover:text-[#f36c21] flex items-center justify-center transition border border-white/20 text-base">
                            <i class="bi bi-instagram"></i>
                        </a>
                        @if($school->linkedin_url)
                        <a href="{{ $school->linkedin_url }}" target="_blank" rel="noopener" class="w-9 h-9 rounded-lg bg-white/10 hover:bg-white text-white hover:text-[#f36c21] flex items-center justify-center transition border border-white/20 text-base">
                            <i class="bi bi-linkedin"></i>
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Column 2: UsefulLink --}}
            <div class="md:col-span-3 lg:col-span-3">
                <h5 class="font-bold text-lg text-white mb-5">UsefulLink</h5>
                <ul class="space-y-2.5 text-sm text-white/90">
                    <li class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                        <a href="{{ $pageUrl('about') }}" class="hover:underline hover:text-white transition">About Us</a>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                        <a href="{{ $pageUrl('academics') }}" class="hover:underline hover:text-white transition">Programs</a>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                        <a href="{{ $pageUrl('admissions') }}" class="hover:underline hover:text-white transition">Admission</a>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                        <button type="button" onclick="openCareerModal()" class="hover:underline hover:text-white transition text-left">Career</button>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                        <a href="{{ $pageUrl('notices') }}" class="hover:underline hover:text-white transition">News/Events</a>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                        <a href="{{ url('admissions/apply?school='.$school->slug) }}" class="hover:underline hover:text-white transition font-bold">Online Application</a>
                    </li>
                </ul>
            </div>

            {{-- Column 3: Contact Us --}}
            <div class="md:col-span-4 lg:col-span-4 space-y-4">
                <h5 class="font-bold text-lg text-white mb-5">Contact Us</h5>
                <div class="space-y-3.5 text-sm text-white/95">
                    <p class="flex items-start gap-3">
                        <i class="bi bi-geo-alt text-lg shrink-0 mt-0.5"></i>
                        <span>{{ $school->address ?? ($school->city . ', ' . ($school->district ?? 'Nepal')) }}</span>
                    </p>
                    <p class="flex items-center gap-3">
                        <i class="bi bi-envelope text-lg shrink-0"></i>
                        <a href="mailto:{{ $school->email }}" class="hover:underline">{{ $school->email ?? 'info@school.edu.np' }}</a>
                    </p>
                    <p class="flex items-center gap-3">
                        <i class="bi bi-telephone text-lg shrink-0"></i>
                        <a href="tel:{{ $school->phone }}" class="hover:underline">{{ $school->phone ?? '01-4970590, 4970591' }}</a>
                    </p>
                </div>
            </div>

        </div>

        {{-- Bottom Copyright & Nepal School Hub Badge --}}
        <div class="pt-8 border-t border-white/20 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-white/90">
            <p>© Copyright {{ date('Y') }} {{ $school->name }} All Rights Reserved</p>
            
            <div class="flex items-center gap-3">
                <a href="https://nshlms.com" target="_blank" rel="noopener" class="flex items-center gap-2 bg-white text-slate-900 px-3.5 py-1.5 rounded-2xl shadow-md font-bold text-xs hover:shadow-lg transition-all group no-underline hover:opacity-95">
                    <div class="w-6 h-6 rounded-full bg-[#f36c21] text-white flex items-center justify-center text-[10px] font-black tracking-tighter shrink-0 group-hover:scale-105 transition">
                        nsh
                    </div>
                    <span class="text-slate-900 font-bold">Powered By: <span class="text-[#f36c21] font-extrabold">Nepal School Hub</span></span>
                </a>
            </div>
        </div>
    </div>
</footer>

<!-- ═════════════════════════════════════════════════════════════════════
     FLOATING BACK TO TOP BUTTON (ORANGE CIRCLE - EXACT MATCH)
══════════════════════════════════════════════════════════════════════ -->
<button type="button" id="backToTopBtn" onclick="window.scrollTo({top: 0, behavior: 'smooth'})" aria-label="Scroll to Top" class="fixed bottom-20 md:bottom-6 right-3 sm:right-6 z-40 w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-[#f36c21] hover:bg-[#e05d15] text-white hidden sm:flex items-center justify-center shadow-xl border border-white/30 transition-all transform hover:scale-110">
    <i class="bi bi-arrow-up text-lg font-bold"></i>
</button>

<!-- ═════════════════════════════════════════════════════════════════════
     POPUP MODALS & LIGHTBOXES
══════════════════════════════════════════════════════════════════════ -->

{{-- 1. Urgent Notice Popup Modal --}}
@if($school->hasActivePopup())
<div id="schoolNoticePopupModal" 
     onclick="if(event.target === this) closeSchoolPopup(false)"
     class="fixed inset-0 z-[9999] bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 transition-all duration-300 opacity-0 pointer-events-none"
     role="dialog" aria-modal="true">
    
    <div class="bg-white rounded-3xl overflow-hidden shadow-2xl border border-slate-200 max-w-lg w-full transform transition-all duration-300 scale-95" id="popupCard">
        <div class="bg-gradient-to-r from-navy-900 to-navy-800 text-white px-5 py-3.5 flex items-center justify-between border-b border-white/10">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-ping"></span>
                <span class="font-cinzel text-xs font-bold text-amber-300 tracking-wider uppercase flex items-center gap-1.5">
                    <i class="bi bi-bell-fill"></i> Official Notice & Announcement
                </span>
            </div>
            <button onclick="closeSchoolPopup(false)" aria-label="Close Notice" 
                    class="w-7 h-7 rounded-full bg-white/10 hover:bg-white/20 text-slate-300 hover:text-white flex items-center justify-center transition text-sm">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        @if($school->popupImageUrl)
        <div class="relative max-h-72 sm:max-h-80 overflow-hidden bg-slate-900 flex items-center justify-center border-b border-slate-100">
            <img src="{{ $school->popupImageUrl }}" alt="{{ $school->popup_title ?? 'Notice Flyer' }}" class="w-full h-full object-cover">
        </div>
        @endif

        <div class="p-6">
            <h3 class="font-cinzel text-lg sm:text-xl font-extrabold text-navy-900 mb-2 leading-snug">
                {{ $school->popup_title ?: 'Notice from the Administration' }}
            </h3>

            @if($school->popup_subtitle)
            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed mb-5">
                {{ $school->popup_subtitle }}
            </p>
            @endif

            <div class="flex items-center gap-3 pt-2">
                @if($school->popup_btn_text && $school->popup_btn_url)
                <a href="{{ $school->popup_btn_url }}" class="flex-1 text-center py-2.5 px-4 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs shadow-md transition">
                    {{ $school->popup_btn_text }} &rarr;
                </a>
                @else
                <a href="{{ url('admissions/apply?school='.$school->slug) }}" class="flex-1 text-center py-2.5 px-4 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs shadow-md transition">
                    Apply Online Now &rarr;
                </a>
                @endif
                <button onclick="closeSchoolPopup(false)" class="py-2.5 px-4 rounded-xl text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">
                    Dismiss
                </button>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                <label class="flex items-center gap-1.5 cursor-pointer select-none hover:text-slate-600 transition">
                    <input type="checkbox" id="dontShowAgainCheckbox" class="rounded accent-slate-900 text-xs">
                    <span>Don't show this announcement again today</span>
                </label>
                <button onclick="closeSchoolPopup(true)" class="hover:underline text-slate-500 font-semibold">
                    Don't show again
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- 2. Lightbox Modal for Gallery --}}
<div id="imageLightboxModal" class="fixed inset-0 z-[9999] bg-slate-950/90 backdrop-blur-md flex items-center justify-center p-4 transition-opacity duration-300 opacity-0 pointer-events-none" onclick="closeLightbox()">
    <div class="relative max-w-4xl max-h-[90vh] overflow-hidden" onclick="event.stopPropagation()">
        <img id="lightboxImg" src="" alt="Enlarged Campus View" class="w-full h-full object-contain rounded-2xl shadow-2xl">
        <button onclick="closeLightbox()" class="absolute top-4 right-4 w-10 h-10 rounded-full bg-slate-900/80 hover:bg-slate-900 text-white flex items-center justify-center transition">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
</div>

{{-- 3. Faculty Bio Modal --}}
<div id="facultyBioModal" class="fixed inset-0 z-[9999] bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4 transition-all duration-300 opacity-0 pointer-events-none" onclick="closeFacultyModal()">
    <div class="bg-white rounded-3xl overflow-hidden shadow-2xl border border-slate-200 max-w-lg w-full transform transition-all duration-300 scale-95" id="facultyModalCard" onclick="event.stopPropagation()">
        <div class="bg-slate-900 text-white p-6 flex items-center justify-between border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-14 h-14 rounded-2xl overflow-hidden border-2 border-slate-600 shrink-0 bg-white p-0.5">
                    <img id="facModalImg" src="" alt="Faculty Portrait" class="w-full h-full object-cover rounded-xl">
                </div>
                <div>
                    <h3 id="facModalName" class="font-extrabold text-lg text-white"></h3>
                    <p id="facModalRole" class="text-xs text-slate-300 font-semibold"></p>
                </div>
            </div>
            <button onclick="closeFacultyModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">
                <i class="bi bi-x-lg text-sm"></i>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-3 text-xs">
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
                    <span class="text-slate-400 block font-semibold mb-0.5">Qualification:</span>
                    <strong id="facModalQual" class="text-slate-800 leading-tight block"></strong>
                </div>
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
                    <span class="text-slate-400 block font-semibold mb-0.5">Experience:</span>
                    <strong id="facModalExp" class="text-slate-800 leading-tight block"></strong>
                </div>
            </div>
            <div id="facModalCoursesWrapper" class="bg-slate-100 border border-slate-200 p-3 rounded-xl text-xs hidden">
                <span class="text-slate-900 font-bold block mb-1">Courses & Subjects Instructed:</span>
                <span id="facModalCourses" class="text-slate-700 font-medium"></span>
            </div>
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 block mb-1">Academic Mentorship & Profile</span>
                <p id="facModalBio" class="text-xs sm:text-sm text-slate-600 leading-relaxed"></p>
            </div>
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs gap-3 flex-wrap">
                <div class="flex items-center gap-3 text-slate-600">
                    <span><i class="bi bi-envelope text-slate-700"></i> <span id="facModalEmail"></span></span>
                    <span id="facModalPhoneWrapper" class="hidden"><i class="bi bi-telephone text-slate-700"></i> <span id="facModalPhone"></span></span>
                </div>
                <button onclick="closeFacultyModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 rounded-xl font-bold text-slate-700 transition ml-auto">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

{{-- 4. Student Exam & Results Gateway Modal --}}
<div id="examResultModal" class="fixed inset-0 z-[9999] bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4 transition-all duration-300 opacity-0 pointer-events-none" onclick="closeExamResultModal()">
    <div class="bg-white rounded-3xl overflow-hidden shadow-2xl border border-slate-200 max-w-lg w-full transform transition-all duration-300 scale-95" id="examResultCard" onclick="event.stopPropagation()">
        <div class="bg-slate-900 text-white p-5 flex items-center justify-between border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-slate-800 text-white flex items-center justify-center text-lg font-extrabold shadow-md border border-slate-700">
                    <i class="bi bi-award-fill"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-base sm:text-lg text-white">Student Examination & Results</h3>
                    <p class="text-[11px] text-slate-300 font-medium">Terminal Gradebooks & Performance Portal</p>
                </div>
            </div>
            <button onclick="closeExamResultModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">
                <i class="bi bi-x-lg text-sm"></i>
            </button>
        </div>

        <div class="p-6 space-y-4">
            {{-- Quick Student LMS Login for Results --}}
            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-slate-900 text-white flex items-center justify-center text-base shrink-0 font-bold mt-0.5">
                        <i class="bi bi-person-bounding-box"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-bold text-slate-900 mb-0.5">Student & Parent Portal Login</h4>
                        <p class="text-xs text-slate-600 leading-relaxed mb-3">
                            Sign in with your Student ID / Roll number to view detailed terminal marks, subject-wise GPA, rank, and download digital report cards.
                        </p>
                        <a href="{{ url('login') }}" class="btn-primary-dark !py-2 !px-4 !text-xs !w-full text-center flex items-center justify-center gap-2">
                            <i class="bi bi-box-arrow-in-right"></i>
                            <span>Login to View Exam Results</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Exam Routines & Notices Link --}}
            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-slate-900 text-white flex items-center justify-center text-base shrink-0 font-bold mt-0.5">
                        <i class="bi bi-calendar3-event"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-bold text-slate-900 mb-0.5">Exam Schedules & Routines</h4>
                        <p class="text-xs text-slate-600 leading-relaxed mb-3">
                            Check upcoming Terminal Exams, Unit Tests, and SEE / +2 Board Examination timetables and notices.
                        </p>
                        <a href="{{ $pageUrl('notices') }}" class="btn-secondary-white !py-2 !px-4 !text-xs !w-full text-center flex items-center justify-center gap-2">
                            <i class="bi bi-bell-fill text-slate-700"></i>
                            <span>View Examination Notices & Routines</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Verify Marksheet Token --}}
            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4">
                <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                    <i class="bi bi-shield-check text-slate-900"></i> Verify Digital Marksheet / Certificate
                </h4>
                <p class="text-[11px] text-slate-500 mb-2.5">
                    Enter the verification token printed on your digital marksheet or certificate to authenticate official records.
                </p>
                <form onsubmit="event.preventDefault(); var t = document.getElementById('verifyResultToken').value.trim(); if(t) window.location.href='{{ url('results/verify') }}/' + encodeURIComponent(t); else alert('Please enter a valid verification code');" class="flex gap-2">
                    <input type="text" id="verifyResultToken" placeholder="e.g. MS-2026-XXXXX" class="flex-1 px-3 py-2 text-xs border border-slate-300 rounded-xl focus:outline-none focus:border-slate-900 bg-white font-mono">
                    <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition">
                        Verify
                    </button>
                </form>
            </div>
        </div>

        <div class="bg-slate-100 px-6 py-3 border-t border-slate-200 flex justify-end">
            <button onclick="closeExamResultModal()" class="px-4 py-1.5 bg-slate-200 hover:bg-slate-300 rounded-xl font-bold text-slate-700 transition text-xs">
                Close
            </button>
        </div>
    </div>
</div>



{{-- 6. Quick Prospectus Download Modal --}}
<div id="prospectusDownloadModal" class="fixed inset-0 z-[9999] bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4 transition-all duration-300 opacity-0 pointer-events-none" onclick="closeProspectusModal()">
    <div class="bg-white rounded-3xl overflow-hidden shadow-2xl border border-slate-200 max-w-md w-full transform transition-all duration-300 scale-95" id="prospectusCard" onclick="event.stopPropagation()">
        <div class="bg-gradient-to-r from-navy-900 to-navy-800 text-white p-5 flex items-center justify-between border-b border-white/10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500 text-slate-950 flex items-center justify-center text-lg font-extrabold shadow-md">
                    <i class="bi bi-file-earmark-pdf-fill"></i>
                </div>
                <div>
                    <h3 class="font-cinzel font-extrabold text-base text-white">Download Prospectus</h3>
                    <p class="text-[11px] text-amber-300 font-medium">Academic Guide & Admission Kit (PDF)</p>
                </div>
            </div>
            <button onclick="closeProspectusModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">
                <i class="bi bi-x-lg text-sm"></i>
            </button>
        </div>
        <div class="p-6">
            <p class="text-xs text-slate-600 leading-relaxed mb-4">
                Get full details on curriculum streams (+2 Science, Management, Humanities, School 1-10), fee structure guidelines, laboratory infrastructure, and faculty profiles in a single downloadable PDF.
            </p>
            <form onsubmit="handleProspectusDownload(event)" class="space-y-3">
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Parent / Student Name *</label>
                    <input type="text" required placeholder="Full name" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs focus:ring-2 focus:ring-navy-700 focus:outline-none">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">WhatsApp / Phone Number *</label>
                    <input type="tel" required placeholder="98XXXXXXXX" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs focus:ring-2 focus:ring-navy-700 focus:outline-none">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Interested Stream</label>
                    <select class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs focus:ring-2 focus:ring-navy-700 focus:outline-none">
                        <option>+2 Science Stream</option>
                        <option>+2 Management Stream</option>
                        <option>+2 Humanities & Law</option>
                        <option>Secondary School (SEE Grades 9-10)</option>
                        <option>Primary & Middle (Grades 1-8)</option>
                        <option>Preschool & Early Childhood</option>
                        <option>Higher Education / Degree Programs</option>
                    </select>
                </div>
                <button type="submit" class="w-full bg-[#f36c21] hover:bg-[#e05d15] text-white font-bold rounded-xl text-xs py-3 mt-2 flex items-center justify-center gap-2 shadow-sm transition">
                    <i class="bi bi-cloud-arrow-down-fill text-base"></i> Download Official Prospectus (PDF)
                </button>
                <div id="prospectusSuccessMsg" class="hidden p-3 rounded-xl bg-emerald-100 text-emerald-800 text-xs font-bold text-center mt-2">
                    <i class="bi bi-check-circle-fill"></i> Downloading Prospectus... Check your downloads folder!
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 5. Career & Faculty Vacancies Modal --}}
<div id="careerModal" class="fixed inset-0 z-[9999] bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4 transition-all duration-300 opacity-0 pointer-events-none" onclick="closeCareerModal()">
    <div class="bg-white rounded-3xl overflow-hidden shadow-2xl border border-slate-200 max-w-lg w-full transform transition-all duration-300 scale-95" id="careerCard" onclick="event.stopPropagation()">
        <div class="bg-slate-900 text-white p-5 flex items-center justify-between border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#f36c21] text-white flex items-center justify-center text-lg font-bold">
                    <i class="bi bi-briefcase-fill"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-white">Career Opportunities</h3>
                    <p class="text-[11px] text-slate-300 font-medium">Join our distinguished academic team</p>
                </div>
            </div>
            <button onclick="closeCareerModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">
                <i class="bi bi-x-lg text-sm"></i>
            </button>
        </div>
        <div class="p-6">
            <div class="bg-orange-50 border border-orange-200 rounded-xl p-3 text-xs text-orange-900 mb-4">
                <strong>Current Openings (Session 2026/27):</strong>
                <ul class="list-disc list-inside mt-1 space-y-0.5 text-orange-800">
                    <li>Secondary Level Science & Mathematics Teacher (Full-time)</li>
                    <li>Pre-Primary & Montessori Trained Educator</li>
                    <li>+2 Lecturer in Computer Science & Accountancy</li>
                    <li>Lab Assistant & IT Support Officer</li>
                </ul>
            </div>

            <form onsubmit="handleCareerSubmit(event)" class="space-y-3">
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Full Name *</label>
                    <input type="text" required placeholder="Your full name" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs focus:ring-2 focus:ring-slate-900 focus:outline-none">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Phone / Mobile *</label>
                        <input type="tel" required placeholder="98XXXXXXXX" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs focus:ring-2 focus:ring-slate-900 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Email *</label>
                        <input type="email" required placeholder="you@email.com" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs focus:ring-2 focus:ring-slate-900 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Position Applied For *</label>
                    <select class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs focus:ring-2 focus:ring-slate-900 focus:outline-none">
                        <option>Secondary Science Teacher</option>
                        <option>Secondary Mathematics Teacher</option>
                        <option>Montessori / Pre-Primary Educator</option>
                        <option>+2 Higher Secondary Lecturer</option>
                        <option>Administrative / Accounts Staff</option>
                        <option>Other Teaching / Non-Teaching Role</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Brief Bio & Qualifications</label>
                    <textarea rows="2" placeholder="Tell us about your academic degrees, teaching experience, and specializations..." class="w-full px-3.5 py-2 rounded-xl border border-slate-200 bg-slate-50 text-xs focus:ring-2 focus:ring-slate-900 focus:outline-none"></textarea>
                </div>
                <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs py-3 mt-2 flex items-center justify-center gap-2 shadow-sm transition">
                    <i class="bi bi-send-fill text-xs text-[#f36c21]"></i> Submit Career Application / CV
                </button>
                <div id="careerSuccessMsg" class="hidden p-3 rounded-xl bg-emerald-100 text-emerald-800 text-xs font-bold text-center mt-2">
                    <i class="bi bi-check-circle-fill"></i> Thank you! Your application has been submitted to the Administration desk.
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ═════════════════════════════════════════════════════════════════════
     MOBILE FLOATING BOTTOM BAR
══════════════════════════════════════════════════════════════════════ -->
<div class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-xl border-t border-slate-200 py-1.5 px-3 pb-[max(0.375rem,env(safe-area-inset-bottom))] flex items-center justify-around md:hidden shadow-[0_-4px_20px_rgba(0,0,0,0.08)]">
    <a href="{{ $pageUrl('') }}" class="flex flex-col items-center py-1 px-2.5 {{ $activeNav('') ? 'text-[#f36c21] font-bold' : 'text-slate-500 hover:text-slate-900' }} transition">
        <i class="bi {{ $activeNav('') ? 'bi-house-door-fill text-[#f36c21]' : 'bi-house-door text-slate-400' }} text-lg leading-none"></i>
        <span class="text-[10px] font-semibold mt-1">Home</span>
    </a>
    <a href="{{ $pageUrl('notices') }}" class="flex flex-col items-center py-1 px-2.5 {{ $activeNav('notices') ? 'text-[#f36c21] font-bold' : 'text-slate-500 hover:text-slate-900' }} transition">
        <i class="bi {{ $activeNav('notices') ? 'bi-bell-fill text-[#f36c21]' : 'bi-bell text-slate-400' }} text-lg leading-none"></i>
        <span class="text-[10px] font-semibold mt-1">Notices</span>
    </a>
    
    {{-- Central Elevated Apply Action --}}
    <a href="{{ url('admissions/apply?school='.$school->slug) }}" class="flex flex-col items-center -mt-5 group focus:outline-none">
        <div class="w-12 h-12 rounded-full bg-[#f36c21] hover:bg-[#e05d15] text-white flex items-center justify-center text-xl shadow-lg border-4 border-white group-hover:scale-105 transition transform">
            <i class="bi bi-pencil-square"></i>
        </div>
        <span class="text-[10px] font-extrabold text-[#f36c21] mt-0.5">Apply</span>
    </a>

    <button type="button" onclick="openExamResultModal()" class="flex flex-col items-center py-1 px-2.5 text-slate-500 hover:text-slate-900 transition">
        <i class="bi bi-mortarboard text-slate-400 text-lg leading-none"></i>
        <span class="text-[10px] font-semibold mt-1">Result</span>
    </button>
    <a href="{{ url('login') }}" class="flex flex-col items-center py-1 px-2.5 text-slate-500 hover:text-slate-900 transition">
        <i class="bi bi-lock-fill text-slate-700 text-lg leading-none"></i>
        <span class="text-[10px] font-semibold mt-1">Login</span>
    </a>
</div>

<!-- ═════════════════════════════════════════════════════════════════════
     JAVASCRIPT CONTROLLERS
══════════════════════════════════════════════════════════════════════ -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ── Hero banner swiper (Pure Fullscreen Photo Slideshow) ───────────
    if (typeof Swiper !== 'undefined' && document.querySelector('.banner-swiper')) {
        let slideTimer = null;

        function advanceSlide(swiper) {
            clearTimeout(slideTimer);
            if (swiper && !swiper.destroyed) {
                swiper.slideNext();
            }
        }

        function handleCurrentSlide(swiper) {
            clearTimeout(slideTimer);

            // Pause all other videos and reset
            swiper.el.querySelectorAll('video').forEach(v => {
                try {
                    v.pause();
                } catch (e) {}
            });

            // Find active slide element
            const activeSlide = swiper.slides[swiper.activeIndex] || swiper.el.querySelector('.swiper-slide-active');
            if (!activeSlide) return;

            const video = activeSlide.querySelector('video');

            if (video) {
                // Stop swiper internal autoplay
                if (swiper.autoplay && swiper.autoplay.running) {
                    swiper.autoplay.stop();
                }

                video.muted = true;
                video.currentTime = 0;

                // Event listener to advance when video finishes
                const onEnded = () => {
                    video.removeEventListener('ended', onEnded);
                    video.removeEventListener('error', onError);
                    advanceSlide(swiper);
                };

                const onError = () => {
                    video.removeEventListener('ended', onEnded);
                    video.removeEventListener('error', onError);
                    slideTimer = setTimeout(() => advanceSlide(swiper), 3000);
                };

                video.addEventListener('ended', onEnded, { once: true });
                video.addEventListener('error', onError, { once: true });

                const playPromise = video.play();
                if (playPromise !== undefined) {
                    playPromise.catch(() => {
                        // If autoplay is blocked by browser, fallback to 3 seconds
                        slideTimer = setTimeout(() => advanceSlide(swiper), 3000);
                    });
                }
            } else {
                // Image Slide: Display for exactly 3 seconds (3000 ms)
                if (swiper.autoplay && swiper.autoplay.running) {
                    swiper.autoplay.stop();
                }
                slideTimer = setTimeout(() => {
                    advanceSlide(swiper);
                }, 3000);
            }
        }

        const heroSwiper = new Swiper('.banner-swiper', {
            loop: true,
            speed: 1000,
            effect: 'fade',
            fadeEffect: {
                crossFade: true,
            },
            grabCursor: true,
            touchRatio: 1,
            simulateTouch: true,
            autoplay: false,
            on: {
                init: function () {
                    handleCurrentSlide(this);
                },
                slideChangeTransitionEnd: function () {
                    handleCurrentSlide(this);
                }
            }
        });
    }

    // ── Mobile menu drawer ────────────────────────────────────────────
    const toggle = document.getElementById('mobile-toggle');
    const menu   = document.getElementById('mobile-menu');
    const icon   = document.getElementById('hamburger-icon');
    if (toggle && menu) {
        toggle.addEventListener('click', () => {
            const open = menu.classList.toggle('open');
            if (icon) icon.className = open ? 'bi bi-x-lg text-2xl' : 'bi bi-list text-2xl';
        });
        menu.querySelectorAll('a').forEach(a => {
            a.addEventListener('click', () => {
                menu.classList.remove('open');
                if (icon) icon.className = 'bi bi-list text-2xl';
            });
        });
    }

    // ── Notice search filter ──────────────────────────────────────────
    const noticeInput = document.getElementById('noticeSearchInput');
    const noticeItems = document.querySelectorAll('.notice-item');
    if (noticeInput && noticeItems.length > 0) {
        noticeInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            noticeItems.forEach(item => {
                const title = item.querySelector('.notice-title')?.textContent.toLowerCase() || '';
                if (title.includes(query)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    // ── Notice Popup Modal Logic (sessionStorage & localStorage "don't show again") ─────
    @if($school->hasActivePopup())
    const schoolId   = '{{ $school->id }}';
    const storageKey = 'nsh_school_notice_popup_dismissed_' + schoolId;
    const sessionKey = 'nsh_school_notice_popup_session_dismissed_' + schoolId;
    let shouldOpen   = true;

    // 1. If dismissed in the current tab/browser session, don't auto-open
    if (sessionStorage.getItem(sessionKey) === '1') {
        shouldOpen = false;
    }

    // 2. If dismissed with 24h preference or show_once is active
    const dismissed = localStorage.getItem(storageKey);
    if (dismissed) {
        const diffHours = (Date.now() - parseInt(dismissed, 10)) / (1000 * 60 * 60);
        if (diffHours < 24) shouldOpen = false;
    }

    if (shouldOpen) {
        setTimeout(() => { openSchoolPopup(); }, 700);
    }
    @endif
});

// Popup modal handlers
function openSchoolPopup() {
    const modal = document.getElementById('schoolNoticePopupModal');
    const card  = document.getElementById('popupCard');
    if (!modal) return;
    modal.classList.remove('opacity-0', 'pointer-events-none');
    modal.classList.add('opacity-100');
    if (card) { card.classList.remove('scale-95'); card.classList.add('scale-100'); }
    document.body.classList.add('overflow-hidden');
}

function closeSchoolPopup(saveDismiss = false) {
    const modal = document.getElementById('schoolNoticePopupModal');
    const card  = document.getElementById('popupCard');
    if (!modal) return;
    modal.classList.add('opacity-0', 'pointer-events-none');
    modal.classList.remove('opacity-100');
    if (card) { card.classList.add('scale-95'); card.classList.remove('scale-100'); }
    document.body.classList.remove('overflow-hidden');

    const schoolId = '{{ $school->id }}';
    // Always mark dismissed for current session to avoid annoying user on page switches
    sessionStorage.setItem('nsh_school_notice_popup_session_dismissed_' + schoolId, '1');

    const chk = document.getElementById('dontShowAgainCheckbox');
    if (saveDismiss || (chk && chk.checked)) {
        localStorage.setItem('nsh_school_notice_popup_dismissed_' + schoolId, Date.now().toString());
    }
}

// Lightbox modal handlers
function openLightbox(url) {
    const modal = document.getElementById('imageLightboxModal');
    const img   = document.getElementById('lightboxImg');
    if (!modal || !img) return;
    img.src = url;
    modal.classList.remove('opacity-0', 'pointer-events-none');
    modal.classList.add('opacity-100');
    document.body.classList.add('overflow-hidden');
}

function closeLightbox() {
    const modal = document.getElementById('imageLightboxModal');
    if (!modal) return;
    modal.classList.add('opacity-0', 'pointer-events-none');
    modal.classList.remove('opacity-100');
    document.body.classList.remove('overflow-hidden');
}

// FAQ accordion toggler
function toggleFaq(btn) {
    const content = btn.nextElementSibling;
    const icon = btn.querySelector('i');
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        if (icon) icon.classList.add('rotate-180');
    } else {
        content.classList.add('hidden');
        if (icon) icon.classList.remove('rotate-180');
    }
}

// Faculty department filter
function filterFaculty(dept) {
    const btns = document.querySelectorAll('.faculty-tab-btn');
    btns.forEach(b => {
        b.classList.remove('active', 'bg-slate-900', 'text-white');
        b.classList.add('bg-slate-100', 'text-slate-800');
    });

    if (event && event.currentTarget) {
        event.currentTarget.classList.add('active', 'bg-slate-900', 'text-white');
        event.currentTarget.classList.remove('bg-slate-100', 'text-slate-800');
    }

    const cards = document.querySelectorAll('.faculty-card');
    cards.forEach(card => {
        const cardDept = card.getAttribute('data-dept');
        if (dept === 'all' || cardDept === dept) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}

// Faculty Bio Modal
function openFacultyModal(name, role, qual, exp, email, bio, photo, courses = '', phone = '') {
    const modal = document.getElementById('facultyBioModal');
    const card  = document.getElementById('facultyModalCard');
    if (!modal) return;

    document.getElementById('facModalName').textContent = name;
    document.getElementById('facModalRole').textContent = role;
    document.getElementById('facModalQual').textContent = qual;
    document.getElementById('facModalExp').textContent = exp;
    document.getElementById('facModalEmail').textContent = email;
    document.getElementById('facModalBio').textContent = bio;
    const imgEl = document.getElementById('facModalImg');
    if (imgEl) imgEl.src = photo;

    const coursesWrapper = document.getElementById('facModalCoursesWrapper');
    const coursesEl = document.getElementById('facModalCourses');
    if (coursesWrapper && coursesEl) {
        if (courses && courses.trim().length > 0) {
            coursesEl.textContent = courses;
            coursesWrapper.classList.remove('hidden');
        } else {
            coursesWrapper.classList.add('hidden');
        }
    }

    const phoneWrapper = document.getElementById('facModalPhoneWrapper');
    const phoneEl = document.getElementById('facModalPhone');
    if (phoneWrapper && phoneEl) {
        if (phone && phone.trim().length > 0) {
            phoneEl.textContent = phone;
            phoneWrapper.classList.remove('hidden');
        } else {
            phoneWrapper.classList.add('hidden');
        }
    }

    modal.classList.remove('opacity-0', 'pointer-events-none');
    modal.classList.add('opacity-100');
    if (card) { card.classList.remove('scale-95'); card.classList.add('scale-100'); }
    document.body.classList.add('overflow-hidden');
}

function closeFacultyModal() {
    const modal = document.getElementById('facultyBioModal');
    const card  = document.getElementById('facultyModalCard');
    if (!modal) return;

    modal.classList.add('opacity-0', 'pointer-events-none');
    modal.classList.remove('opacity-100');
    if (card) { card.classList.add('scale-95'); card.classList.remove('scale-100'); }
    document.body.classList.remove('overflow-hidden');
}

// Student Exam & Result Gateway Modal
function openExamResultModal() {
    const modal = document.getElementById('examResultModal');
    const card  = document.getElementById('examResultCard');
    if (!modal) return;

    modal.classList.remove('opacity-0', 'pointer-events-none');
    modal.classList.add('opacity-100');
    if (card) { card.classList.remove('scale-95'); card.classList.add('scale-100'); }
    document.body.classList.add('overflow-hidden');
}

function closeExamResultModal() {
    const modal = document.getElementById('examResultModal');
    const card  = document.getElementById('examResultCard');
    if (!modal) return;

    modal.classList.add('opacity-0', 'pointer-events-none');
    modal.classList.remove('opacity-100');
    if (card) { card.classList.add('scale-95'); card.classList.remove('scale-100'); }
    document.body.classList.remove('overflow-hidden');
}

// Prospectus Modal Handlers
function openProspectusModal() {
    const modal = document.getElementById('prospectusDownloadModal');
    const card  = document.getElementById('prospectusCard');
    if (!modal) return;
    modal.classList.remove('opacity-0', 'pointer-events-none');
    modal.classList.add('opacity-100');
    if (card) { card.classList.remove('scale-95'); card.classList.add('scale-100'); }
    document.body.classList.add('overflow-hidden');
}

function closeProspectusModal() {
    const modal = document.getElementById('prospectusDownloadModal');
    const card  = document.getElementById('prospectusCard');
    if (!modal) return;
    modal.classList.add('opacity-0', 'pointer-events-none');
    modal.classList.remove('opacity-100');
    if (card) { card.classList.add('scale-95'); card.classList.remove('scale-100'); }
    document.body.classList.remove('overflow-hidden');
}

function handleProspectusDownload(e) {
    e.preventDefault();
    const msg = document.getElementById('prospectusSuccessMsg');
    if (msg) msg.classList.remove('hidden');
    setTimeout(() => {
        closeProspectusModal();
        if (msg) msg.classList.add('hidden');
    }, 2500);
}

// Mega Drawer Toggle (☰)
function toggleSideDrawer() {
    const drawer = document.getElementById('sideMegaDrawer');
    const backdrop = document.getElementById('sideDrawerBackdrop');
    if (!drawer || !backdrop) return;

    if (drawer.classList.contains('drawer-open')) {
        drawer.classList.remove('drawer-open');
        backdrop.classList.add('opacity-0', 'pointer-events-none');
        backdrop.classList.remove('opacity-100');
        document.body.classList.remove('overflow-hidden');
    } else {
        drawer.classList.add('drawer-open');
        backdrop.classList.remove('opacity-0', 'pointer-events-none');
        backdrop.classList.add('opacity-100');
        document.body.classList.add('overflow-hidden');
    }
}

// Career Modal Handlers
function openCareerModal() {
    const modal = document.getElementById('careerModal');
    const card  = document.getElementById('careerCard');
    if (!modal) return;
    modal.classList.remove('opacity-0', 'pointer-events-none');
    modal.classList.add('opacity-100');
    if (card) { card.classList.remove('scale-95'); card.classList.add('scale-100'); }
    document.body.classList.add('overflow-hidden');
}

function closeCareerModal() {
    const modal = document.getElementById('careerModal');
    const card  = document.getElementById('careerCard');
    if (!modal) return;
    modal.classList.add('opacity-0', 'pointer-events-none');
    modal.classList.remove('opacity-100');
    if (card) { card.classList.add('scale-95'); card.classList.remove('scale-100'); }
    document.body.classList.remove('overflow-hidden');
}

function handleCareerSubmit(e) {
    e.preventDefault();
    const msg = document.getElementById('careerSuccessMsg');
    if (msg) msg.classList.remove('hidden');
    setTimeout(() => {
        closeCareerModal();
        if (msg) msg.classList.add('hidden');
    }, 2500);
}

// ══════════════════════════════════════════════════════════════════════════
// LIVE ADMISSIONS & SUPPORT DESK CHAT LOGIC (100% DIRECT & DETERMINISTIC)
// ══════════════════════════════════════════════════════════════════════════
const SCHOOL_CONTEXT = {
    name: "{{ addslashes($school->name) }}",
    city: "{{ addslashes($school->city ?? 'Nepal') }}",
    district: "{{ addslashes($school->district ?? '') }}",
    address: "{{ addslashes($school->address ?? '') }}",
    phone: "{{ addslashes($school->phone ?? '') }}",
    email: "{{ addslashes($school->email ?? '') }}",
    admissionsUrl: "{{ $pageUrl('admissions') }}",
    noticesUrl: "{{ $pageUrl('notices') }}",
    facultyUrl: "{{ $pageUrl('faculty') }}",
    facilitiesUrl: "{{ $pageUrl('facilities') }}",
    contactUrl: "{{ $pageUrl('contact') }}"
};

function toggleAiChatbot() {
    const box = document.getElementById('aiChatbotBox');
    const input = document.getElementById('aiChatInput');
    if (!box) return;

    if (box.classList.contains('hidden')) {
        box.classList.remove('hidden');
        box.classList.add('flex');
        setTimeout(() => {
            if (input) input.focus();
            scrollAiChatToBottom();
        }, 100);
    } else {
        box.classList.add('hidden');
        box.classList.remove('flex');
    }
}

function resetAiChat() {
    const container = document.getElementById('aiChatMessages');
    if (!container) return;
    container.innerHTML = `
        <div class="flex items-start gap-2.5">
            <div class="w-7 h-7 rounded-xl bg-slate-900 text-white flex-shrink-0 flex items-center justify-center text-xs shadow-sm mt-0.5">
                <i class="bi bi-headset"></i>
            </div>
            <div class="bg-white p-3.5 rounded-2xl rounded-tl-sm border border-slate-200 text-slate-800 shadow-sm max-w-[88%] space-y-2 leading-relaxed">
                <p>
                    <strong>Namaste! 🙏 Welcome to ${SCHOOL_CONTEXT.name} Help Desk.</strong>
                </p>
                <p class="text-slate-600">
                    How can we assist you today? Please choose a topic below or type your question:
                </p>
                <div class="pt-2 border-t border-slate-100 flex flex-col gap-1.5">
                    <button type="button" onclick="sendQuickQuery('How do I apply for admissions?')" class="text-[11px] bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold px-3 py-2 rounded-lg border border-slate-200 transition text-left flex items-center justify-between">
                        <span>🎓 Admission Guidelines &amp; Form</span>
                        <i class="bi bi-chevron-right text-[10px] text-slate-400"></i>
                    </button>
                    <button type="button" onclick="sendQuickQuery('What are the fee payment methods and scholarship options?')" class="text-[11px] bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold px-3 py-2 rounded-lg border border-slate-200 transition text-left flex items-center justify-between">
                        <span>💰 Fee Structure &amp; Scholarships</span>
                        <i class="bi bi-chevron-right text-[10px] text-slate-400"></i>
                    </button>
                    <button type="button" onclick="sendQuickQuery('Tell me about hostel, transport, and lab facilities.')" class="text-[11px] bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold px-3 py-2 rounded-lg border border-slate-200 transition text-left flex items-center justify-between">
                        <span>🚌 Transport &amp; Hostel Facilities</span>
                        <i class="bi bi-chevron-right text-[10px] text-slate-400"></i>
                    </button>
                    <button type="button" onclick="sendQuickQuery('What is the contact information and location of the school?')" class="text-[11px] bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold px-3 py-2 rounded-lg border border-slate-200 transition text-left flex items-center justify-between">
                        <span>📞 Direct Phone &amp; Office Hours</span>
                        <i class="bi bi-chevron-right text-[10px] text-slate-400"></i>
                    </button>
                    <button type="button" onclick="sendQuickQuery('I would like to request a callback / leave an inquiry')" class="text-[11px] bg-slate-100 hover:bg-slate-200 text-slate-900 font-bold px-3 py-2 rounded-lg border border-slate-300 transition text-left flex items-center justify-between">
                        <span>✉️ Request a Callback from Principal</span>
                        <i class="bi bi-telephone-outbound text-[11px] text-slate-700"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    scrollAiChatToBottom();
}

function sendQuickQuery(queryText) {
    const input = document.getElementById('aiChatInput');
    if (input) {
        input.value = queryText;
        handleAiChatSubmit(new Event('submit'));
    }
}

function scrollAiChatToBottom() {
    const container = document.getElementById('aiChatMessages');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
}

function appendUserMessage(text) {
    const container = document.getElementById('aiChatMessages');
    if (!container) return;

    const div = document.createElement('div');
    div.className = 'flex items-start justify-end gap-2';
    div.innerHTML = `
        <div class="bg-slate-900 text-white p-3 rounded-2xl rounded-tr-sm shadow-sm max-w-[85%] text-xs leading-relaxed">
            ${escapeHtml(text)}
        </div>
    `;
    container.appendChild(div);
    scrollAiChatToBottom();
}

function appendBotMessage(htmlContent) {
    const container = document.getElementById('aiChatMessages');
    if (!container) return;

    const div = document.createElement('div');
    div.className = 'flex items-start gap-2.5';
    div.innerHTML = `
        <div class="w-7 h-7 rounded-xl bg-slate-900 text-white flex-shrink-0 flex items-center justify-center text-xs shadow-sm mt-0.5">
            <i class="bi bi-headset"></i>
        </div>
        <div class="bg-white p-3.5 rounded-2xl rounded-tl-sm border border-slate-200 text-slate-800 shadow-sm max-w-[88%] space-y-1.5 leading-relaxed">
            ${htmlContent}
        </div>
    `;
    container.appendChild(div);
    scrollAiChatToBottom();
}

function escapeHtml(str) {
    return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

function generateOfficialSupportResponse(query) {
    const q = query.toLowerCase();

    if (q.includes('callback') || q.includes('inquiry') || q.includes('leave a message') || q.includes('call me') || q.includes('talk')) {
        return `
            <p><strong>✉️ Request an Official Callback:</strong></p>
            <p class="text-slate-600">Please provide your details below and our Admissions Desk will call you within 30 minutes:</p>
            <form onsubmit="submitInquiryLead(event)" class="space-y-2 mt-2 bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                <input type="text" id="leadName" required placeholder="Parent / Student Full Name" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-1 focus:ring-slate-900">
                <input type="tel" id="leadPhone" required placeholder="Phone / Mobile Number (98XXXXXXXX)" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-1 focus:ring-slate-900">
                <textarea id="leadMsg" rows="2" placeholder="Your query or grade of interest..." class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-1 focus:ring-slate-900"></textarea>
                <button type="submit" class="w-full py-2 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow transition">
                    <i class="bi bi-check-circle-fill"></i> Submit Callback Request
                </button>
            </form>
        `;
    }

    if (q.includes('admission') || q.includes('apply') || q.includes('enroll') || q.includes('form') || q.includes('criteria')) {
        return `
            <p><strong>🎓 Admissions at ${SCHOOL_CONTEXT.name}:</strong></p>
            <p>Admissions for the <strong>2026–2027 Academic Session</strong> are currently open!</p>
            <div class="mt-2 pt-2 border-t border-slate-100 flex flex-col gap-1.5">
                <a href="${SCHOOL_CONTEXT.admissionsUrl}" class="inline-flex items-center gap-1.5 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 p-2 rounded-xl transition">
                    <i class="bi bi-file-earmark-text-fill"></i> Fill Online Admission Form →
                </a>
                <button type="button" onclick="openProspectusModal()" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-800 bg-white border border-slate-300 hover:bg-slate-50 p-2 rounded-xl transition">
                    <i class="bi bi-download"></i> Download Official Prospectus (PDF)
                </button>
            </div>
        `;
    }

    if (q.includes('fee') || q.includes('cost') || q.includes('tuition') || q.includes('scholarship') || q.includes('discount')) {
        return `
            <p><strong>💰 Fees & Scholarships:</strong></p>
            <p>${SCHOOL_CONTEXT.name} provides transparent fee structures with monthly and term installment options. We offer merit scholarships and mandatory 10% government quota scholarships (Janajati, Dalit, Female & Differently-abled).</p>
            <p class="text-slate-600">Payments can be completed via Digital Wallets (eSewa, Khalti, ConnectIPS) or at the school cash counter.</p>
            <a href="${SCHOOL_CONTEXT.contactUrl}" class="inline-block mt-2 text-xs font-bold text-slate-900 hover:underline">
                Contact Accounts Desk for Exact Fee Breakdown →
            </a>
        `;
    }

    if (q.includes('facility') || q.includes('hostel') || q.includes('bus') || q.includes('transport') || q.includes('lab') || q.includes('library') || q.includes('sport')) {
        return `
            <p><strong>🚌 Campus Facilities & Infrastructure:</strong></p>
            <ul class="space-y-1 text-slate-700">
                <li>• <strong>Transport:</strong> Fleet of buses covering major local routes with RFID & tracking.</li>
                <li>• <strong>Hostel:</strong> Separate, secure boys' and girls' residential facilities.</li>
                <li>• <strong>Laboratories:</strong> Modern Physics, Chemistry, Biology & High-speed Computer Labs.</li>
                <li>• <strong>Library:</strong> Physical reading catalog + digital eBooks portal.</li>
            </ul>
            <a href="${SCHOOL_CONTEXT.facilitiesUrl}" class="inline-block mt-2 text-xs font-bold text-slate-900 hover:underline">
                Explore All Facilities & Photos →
            </a>
        `;
    }

    if (q.includes('contact') || q.includes('phone') || q.includes('number') || q.includes('email') || q.includes('address') || q.includes('location') || q.includes('where')) {
        return `
            <p><strong>📞 Contact ${SCHOOL_CONTEXT.name}:</strong></p>
            <div class="space-y-1 text-slate-700 bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                <div>📍 <strong>Address:</strong> ${SCHOOL_CONTEXT.address || SCHOOL_CONTEXT.city}</div>
                <div>☎️ <strong>Phone:</strong> ${SCHOOL_CONTEXT.phone || 'Available via Office Desk'}</div>
                <div>✉️ <strong>Email:</strong> ${SCHOOL_CONTEXT.email || 'info@school.edu.np'}</div>
            </div>
            <a href="${SCHOOL_CONTEXT.contactUrl}" class="inline-block mt-2 text-xs font-bold text-slate-900 hover:underline">
                Send Direct Message to Principal / Admin →
            </a>
        `;
    }

    if (q.includes('result') || q.includes('exam') || q.includes('mark') || q.includes('transcript') || q.includes('grade')) {
        return `
            <p><strong>🏆 Exams & Result Gateway:</strong></p>
            <p>Students and parents can view published term exam results and download digital transcripts online.</p>
            <button type="button" onclick="openExamResultModal()" class="mt-2 inline-flex items-center gap-1.5 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 p-2 rounded-xl transition">
                <i class="bi bi-trophy-fill"></i> Check Student Exam Results →
            </button>
        `;
    }

    if (q.includes('notice') || q.includes('holiday') || q.includes('routine') || q.includes('event')) {
        return `
            <p><strong>📢 School Notice Board:</strong></p>
            <p>Stay updated with the latest circulars, holiday announcements, academic calendars, and event updates.</p>
            <a href="${SCHOOL_CONTEXT.noticesUrl}" class="inline-block mt-2 text-xs font-bold text-navy-900 hover:underline">
                View Official Notice Board →
            </a>
        `;
    }

    if (q.includes('faculty') || q.includes('teacher') || q.includes('principal') || q.includes('staff')) {
        return `
            <p><strong>👨‍🏫 Our Faculty:</strong></p>
            <p>Our educators are certified subject specialists holding advanced degrees and specialized pedagogical training.</p>
            <a href="${SCHOOL_CONTEXT.facultyUrl}" class="inline-block mt-2 text-xs font-bold text-navy-900 hover:underline">
                Browse Faculty Directory →
            </a>
        `;
    }

    // Default response with action options
    return `
        <p>Thank you for reaching out to <strong>${SCHOOL_CONTEXT.name}</strong>!</p>
        <p class="text-slate-600">For immediate assistance, call us at <strong>${SCHOOL_CONTEXT.phone}</strong> or use the options below:</p>
        <div class="mt-2 pt-2 border-t border-slate-100 flex flex-col gap-1.5">
            <a href="${SCHOOL_CONTEXT.admissionsUrl}" class="inline-block text-xs font-bold text-navy-900 hover:underline">
                • Online Admission Application Portal →
            </a>
            <a href="${SCHOOL_CONTEXT.contactUrl}" class="inline-block text-xs font-bold text-navy-900 hover:underline">
                • Send Message to School Office →
            </a>
            <button type="button" onclick="sendQuickQuery('I would like to request a callback / leave an inquiry')" class="text-left text-xs font-bold text-amber-700 hover:underline">
                • Request a Callback from our Team →
            </button>
        </div>
    `;
}

function submitInquiryLead(e) {
    e.preventDefault();
    const name = document.getElementById('leadName').value;
    const phone = document.getElementById('leadPhone').value;
    const msg = document.getElementById('leadMsg').value;

    appendBotMessage(`
        <div class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900">
            <p class="font-bold mb-1 flex items-center gap-1.5">
                <i class="bi bi-check-circle-fill text-emerald-600"></i> Callback Request Received!
            </p>
            <p class="text-[11px] text-emerald-800">
                Thank you <strong>${escapeHtml(name)}</strong>! Our admissions counselor has been notified and will call you on <strong>${escapeHtml(phone)}</strong> shortly.
            </p>
        </div>
    `);
}

function handleAiChatSubmit(e) {
    if (e) e.preventDefault();

    const input = document.getElementById('aiChatInput');
    const sendBtn = document.getElementById('aiChatSendBtn');
    const indicator = document.getElementById('aiTypingIndicator');
    if (!input) return;

    const query = input.value.trim();
    if (!query) return;

    appendUserMessage(query);
    input.value = '';
    
    if (sendBtn) sendBtn.disabled = true;
    if (indicator) {
        indicator.classList.remove('hidden');
        indicator.classList.add('flex');
    }
    scrollAiChatToBottom();

    // Instant realistic response after 300ms
    setTimeout(() => {
        if (indicator) {
            indicator.classList.add('hidden');
            indicator.classList.remove('flex');
        }
        if (sendBtn) sendBtn.disabled = false;
        appendBotMessage(generateOfficialSupportResponse(query));
        scrollAiChatToBottom();
    }, 350);
}

// Close modals on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeSchoolPopup(false);
        closeLightbox();
        closeFacultyModal();
        closeExamResultModal();
        closeProspectusModal();
        const chatBox = document.getElementById('aiChatbotBox');
        if (chatBox && !chatBox.classList.contains('hidden')) {
            toggleAiChatbot();
        }
    }
});
</script>
@stack('scripts')
</body>
</html>
