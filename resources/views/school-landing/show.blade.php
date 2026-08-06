<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $school->name }} - Official School Website</title>
<meta name="description" content="{{ $school->description && strlen($school->description) > 20 ? Str::limit($school->description, 160) : $school->name . ' — Quality education for a brighter future.' }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" crossorigin="anonymous">
<link rel="stylesheet" href="{{ asset('vendor/swiper/swiper-bundle.min.css') }}">
<script src="{{ asset('vendor/tailwind/tailwindcss.js') }}"></script>
<script src="{{ asset('vendor/swiper/swiper-bundle.min.js') }}"></script>
<style>
/* ── CSS Variables — driven by school's saved theme ── */
@php
    $pc  = $school->primary_color_resolved;   // e.g. #1e4080
    $sc  = $school->secondary_color_resolved; // e.g. #e6b800
    // Derive a darker variant of primary for hero dark bg (approx -40 brightness)
    $hex2rgb  = fn(string $h) => [hexdec(substr($h,1,2)), hexdec(substr($h,3,2)), hexdec(substr($h,5,2))];
    $shade    = function(string $h, int $amt) use ($hex2rgb): string {
        [$r,$g,$b] = $hex2rgb($h);
        return '#'.implode('', array_map(fn($v)=>str_pad(dechex(max(0,min(255,$v+$amt))),2,'0',STR_PAD_LEFT), [$r,$g,$b]));
    };
    $pcDark   = $shade($pc, -55);  // deep dark for hero/footer backgrounds
    $pcDarker = $shade($pc, -75);  // even deeper for footer-bg
    $pcLight  = $shade($pc,  40);  // lighter tint
    $scLight  = $shade($sc,  20);  // slightly lighter gold
@endphp
:root {
    --navy-900: {{ $pcDarker }};
    --navy-800: {{ $pcDark }};
    --navy-700: {{ $shade($pc, -30) }};
    --navy-600: {{ $pc }};
    --navy-500: {{ $pcLight }};
    --gold-400:  {{ $scLight }};
    --gold-500:  {{ $sc }};
}

html { scroll-behavior: smooth; }
body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fafc; }

/* ── Custom color helpers ── */
.bg-navy        { background-color: var(--navy-800); }
.bg-navy-light  { background-color: var(--navy-700); }
.bg-navy-dark   { background-color: var(--navy-900); }
.bg-navy-mid    { background-color: var(--navy-600); }
.text-navy      { color: var(--navy-800); }
.text-navy-light{ color: var(--navy-600); }
.text-gold      { color: var(--gold-400); }
.bg-gold        { background-color: var(--gold-500); }
.border-navy    { border-color: var(--navy-700); }
.hover-navy:hover { background-color: var(--navy-700); color: #fff; }

/* ── Banner Swiper ── */
.banner-swiper {
    width: 100%;
    height: auto;
    border-radius: 1.5rem;
}
.banner-swiper .swiper-slide img {
    width: 100%;
    height: auto;
    object-fit: cover;
    display: block;
}
.swiper-button-next.custom-nav, .swiper-button-prev.custom-nav {
    background-color: #15803d;
    color: white;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
@media (min-width: 768px) {
    .swiper-button-next.custom-nav, .swiper-button-prev.custom-nav {
        width: 44px;
        height: 44px;
    }
}
.swiper-button-next.custom-nav::after, .swiper-button-prev.custom-nav::after {
    font-size: 1rem;
    font-weight: 900;
}
@media (min-width: 768px) {
    .swiper-button-next.custom-nav::after, .swiper-button-prev.custom-nav::after {
        font-size: 1.2rem;
    }
}

/* ── Glass morphism ── */
.glass {
    background: rgba(255,255,255,.10);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255,255,255,.18);
}
.glass-dark {
    background: rgba(10,22,40,.55);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255,255,255,.10);
}

/* ── Navbar ── */
.navbar-glass {
    background: rgba(255,255,255,.95);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(30,64,128,.08);
}
.nav-link { position: relative; }
.nav-link::after {
    content: '';
    position: absolute;
    bottom: -3px; left: 0;
    width: 0; height: 2px;
    background: linear-gradient(90deg, var(--navy-600), var(--gold-400));
    border-radius: 99px;
    transition: width .3s ease;
}
.nav-link:hover::after, .nav-link.active::after { width: 100%; }

/* ── Cards ── */
.card-lift { transition: transform .35s cubic-bezier(.22,.68,0,1.2), box-shadow .35s ease; }
.card-lift:hover { transform: translateY(-8px); box-shadow: 0 24px 60px -8px rgba(10,22,40,.16); }

/* ── Gradient text ── */
.grad-text {
    background: linear-gradient(135deg, #60a5fa 0%, #a5f3fc 50%, #6ee7b7 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.grad-text-gold {
    background: linear-gradient(90deg, #f5c842, #fde68a);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* ── Section label ── */
.section-label {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: .75rem;
    font-weight: 700;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: var(--navy-600);
}
.section-label::before {
    content: '';
    display: inline-block;
    width: 24px; height: 3px;
    background: linear-gradient(90deg, var(--navy-600), var(--gold-400));
    border-radius: 99px;
}

/* ── Stat bar ── */
.stat-bar { background: linear-gradient(135deg, var(--navy-900) 0%, var(--navy-600) 50%, var(--navy-800) 100%); }

/* ── Notice item ── */
.notice-item { border-left: 3px solid var(--navy-600); }

/* ── Pulse animation ── */
@keyframes pulse-dot { 0%,100%{ opacity:1; transform:scale(1) } 50%{ opacity:.6; transform:scale(1.3) } }
.pulse-dot { animation: pulse-dot 2s ease-in-out infinite; }

/* ── Mobile menu ── */
#mobile-menu { transition: max-height .35s ease, opacity .35s ease; max-height: 0; overflow: hidden; opacity: 0; }
#mobile-menu.open { max-height: 600px; opacity: 1; }

/* ── Admission section ── */
.admission-section { background: linear-gradient(135deg, var(--navy-900) 0%, var(--navy-600) 100%); }
.admission-checklist-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.10);
    border-radius: .75rem;
    padding: .75rem 1rem;
}
.admission-step-num {
    width: 2rem; height: 2rem;
    border-radius: 50%;
    background: rgba(245,200,66,.15);
    border: 1px solid rgba(245,200,66,.35);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: .7rem;
    font-weight: 700;
    color: var(--gold-400);
}

/* ── Footer ── */
.footer-bg { background-color: var(--navy-900); }
.footer-divider { border-color: rgba(255,255,255,.08); }
.footer-link { color: #94a3b8; font-size: .875rem; font-weight: 500; transition: color .2s; display: flex; align-items: center; gap: .5rem; }
.footer-link:hover { color: #fff; }
.social-btn {
    width: 2.5rem; height: 2.5rem;
    background: rgba(255,255,255,.06);
    border-radius: .75rem;
    display: flex; align-items: center; justify-content: center;
    transition: background .2s, transform .2s;
    font-size: 1.1rem;
    color: #94a3b8;
}
.social-btn:hover { transform: scale(1.15); color: #fff; }

/* ── Primary CTA buttons ── */
.btn-gold {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: 1rem 2rem;
    background: linear-gradient(135deg, var(--gold-500), var(--gold-400));
    color: var(--navy-900);
    border-radius: .75rem;
    font-weight: 800;
    font-size: .875rem;
    box-shadow: 0 8px 24px -4px rgba(230,184,0,.35);
    transition: transform .25s ease, box-shadow .25s ease;
    text-decoration: none;
}
.btn-gold:hover { transform: translateY(-3px); box-shadow: 0 14px 32px -4px rgba(230,184,0,.4); }

.btn-outline-white {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: 1rem 2rem;
    background: rgba(255,255,255,.08);
    backdrop-filter: blur(12px);
    color: #fff;
    border: 1.5px solid rgba(255,255,255,.3);
    border-radius: .75rem;
    font-weight: 700;
    font-size: .875rem;
    transition: background .25s, transform .25s, border-color .25s;
    text-decoration: none;
}
.btn-outline-white:hover { background: rgba(255,255,255,.18); border-color: rgba(255,255,255,.6); transform: translateY(-3px); }

.btn-navy {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: .625rem 1.25rem;
    background-color: var(--navy-800);
    color: #fff;
    border-radius: .75rem;
    font-weight: 700;
    font-size: .875rem;
    transition: background .2s, transform .2s;
    text-decoration: none;
}
.btn-navy:hover { background-color: var(--navy-700); transform: translateY(-2px); }

.btn-navy-outline {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: .625rem 1.25rem;
    background: transparent;
    color: var(--navy-700);
    border: 2px solid var(--navy-700);
    border-radius: .75rem;
    font-weight: 700;
    font-size: .875rem;
    transition: all .2s;
    text-decoration: none;
}
.btn-navy-outline:hover { background-color: var(--navy-700); color: #fff; }

/* ── Icon ring ── */
.icon-ring {
    width: 56px; height: 56px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem;
    flex-shrink: 0;
}
</style>
</head>
<body class="antialiased">

<!-- ═══════════════════════════ NAVBAR ═══════════════════════════ -->
<nav class="navbar-glass sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20">

            <!-- Brand -->
            <a href="#hero" class="flex items-center gap-3 group" style="text-decoration:none;">
                @if($school->logo)
                    <img src="{{ $school->logo_url }}" alt="{{ $school->name }}"
                         class="h-12 w-12 rounded-xl object-cover shadow-md">
                @else
                    <div class="h-12 w-12 rounded-xl flex items-center justify-center shadow-lg"
                         style="background:linear-gradient(135deg,var(--navy-700),var(--navy-500));">
                        <span class="text-white font-bold text-xl">{{ strtoupper(substr($school->name,0,2)) }}</span>
                    </div>
                @endif
                <div class="leading-tight">
                    <div class="font-extrabold text-lg tracking-tight text-navy">{{ $school->name }}</div>
                    <div class="text-xs text-slate-500 font-medium">{{ $school->city ?? ($school->district ?? 'Excellence in Education') }}</div>
                </div>
            </a>

            <!-- Desktop nav -->
            <div class="hidden md:flex items-center gap-6">
                <a href="#about"     class="nav-link text-sm font-semibold text-slate-600 hover:text-navy transition-colors" style="text-decoration:none;">About</a>
                <a href="#features"  class="nav-link text-sm font-semibold text-slate-600 hover:text-navy transition-colors" style="text-decoration:none;">Features</a>
                <a href="#admission" class="nav-link text-sm font-semibold text-slate-600 hover:text-navy transition-colors" style="text-decoration:none;">Admission</a>
                <a href="#results"   class="nav-link text-sm font-semibold text-slate-600 hover:text-navy transition-colors" style="text-decoration:none;">Results</a>
                <a href="#notices"   class="nav-link text-sm font-semibold text-slate-600 hover:text-navy transition-colors" style="text-decoration:none;">Notices</a>
                <a href="#contact"   class="nav-link text-sm font-semibold text-slate-600 hover:text-navy transition-colors" style="text-decoration:none;">Contact</a>
                <a href="{{ url('login') }}" class="btn-navy-outline ml-2">Login</a>
                <a href="{{ url('admissions/apply?school='.$school->slug) }}" class="btn-navy">Apply Now</a>
            </div>

            <!-- Mobile toggle -->
            <button id="mobile-toggle" aria-label="Toggle menu"
                    class="md:hidden p-2 rounded-xl hover:bg-slate-100 transition text-slate-600">
                <i class="bi bi-list text-2xl" id="hamburger-icon"></i>
            </button>
        </div>

        <!-- Mobile menu -->
        <div id="mobile-menu">
            <div class="pb-6 pt-1 flex flex-col gap-1">
                <a href="#about"     class="px-3 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 rounded-xl border-b border-slate-100 block" style="text-decoration:none;">About</a>
                <a href="#features"  class="px-3 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 rounded-xl border-b border-slate-100 block" style="text-decoration:none;">Features</a>
                <a href="#admission" class="px-3 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 rounded-xl border-b border-slate-100 block" style="text-decoration:none;">Admission</a>
                <a href="#results"   class="px-3 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 rounded-xl border-b border-slate-100 block" style="text-decoration:none;">Results</a>
                <a href="#notices"   class="px-3 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 rounded-xl border-b border-slate-100 block" style="text-decoration:none;">Notices</a>
                <a href="#contact"   class="px-3 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 rounded-xl border-b border-slate-100 block" style="text-decoration:none;">Contact</a>
                <div class="flex gap-3 mt-3 pt-2">
                    <a href="{{ url('login') }}" class="btn-navy-outline flex-1 justify-center">Login</a>
                    <a href="{{ url('admissions/apply?school='.$school->slug) }}" class="btn-navy flex-1 justify-center">Apply Now</a>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- ═══════════════════════════ HERO ═══════════════════════════ -->
<section id="hero" class="pt-6 pb-12 bg-slate-50">
    <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8">
        <div class="relative w-full shadow-xl rounded-2xl md:rounded-[2rem] overflow-hidden">
            <div class="swiper banner-swiper">
                <div class="swiper-wrapper">
                    @if($school->gallery_images && is_array($school->gallery_images) && count($school->gallery_images) > 0)
                        @foreach($school->gallery_images as $imagePath)
                            <div class="swiper-slide">
                                <img src="{{ asset('storage/' . $imagePath) }}" alt="Hero Banner">
                            </div>
                        @endforeach
                    @else
                        <div class="swiper-slide">
                            <img src="{{ asset('imgs/hero/hero_1.webp') }}" alt="Hero 1">
                        </div>
                        <div class="swiper-slide">
                            <img src="{{ asset('imgs/hero/hero_2.webp') }}" alt="Hero 2">
                        </div>
                        <div class="swiper-slide">
                            <img src="{{ asset('imgs/hero/hero_3.webp') }}" alt="Hero 3">
                        </div>
                    @endif
                </div>
                
                <!-- Navigation -->
                <div class="swiper-button-next custom-nav"></div>
                <div class="swiper-button-prev custom-nav"></div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Hero slider
    if (typeof Swiper !== 'undefined') {
        new Swiper('.banner-swiper', {
            loop: true, speed: 1000,
            autoplay: { delay: 5500, disableOnInteraction: false },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
        });
    }
    // Mobile menu
    const toggle = document.getElementById('mobile-toggle');
    const menu   = document.getElementById('mobile-menu');
    const icon   = document.getElementById('hamburger-icon');
    if (toggle) {
        toggle.addEventListener('click', () => {
            const open = menu.classList.toggle('open');
            icon.className = open ? 'bi bi-x-lg text-2xl' : 'bi bi-list text-2xl';
        });
        document.querySelectorAll('#mobile-menu a').forEach(a => {
            a.addEventListener('click', () => { menu.classList.remove('open'); icon.className = 'bi bi-list text-2xl'; });
        });
    }
    // Active nav link on scroll
    const sections = document.querySelectorAll('section[id]');
    window.addEventListener('scroll', () => {
        let cur = '';
        sections.forEach(s => { if (window.scrollY >= s.offsetTop - 130) cur = s.id; });
        document.querySelectorAll('.nav-link').forEach(l => {
            l.classList.toggle('active', l.getAttribute('href') === '#' + cur);
        });
    }, { passive: true });
});
</script>

<!-- ═══════════════════════ STATS BAR ═══════════════════════ -->
<div class="stat-bar py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach([['500+','Students Enrolled','bi-people-fill'],['50+','Expert Teachers','bi-mortarboard-fill'],['25+','Years of Excellence','bi-calendar-check-fill'],['100%','Pass Rate','bi-trophy-fill']] as $s)
            <div class="flex items-center gap-3 justify-center py-2">
                <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background:rgba(255,255,255,.10);">
                    <i class="bi {{ $s[2] }} text-lg" style="color:var(--gold-400);"></i>
                </div>
                <div>
                    <div class="text-2xl font-extrabold text-white leading-none">{{ $s[0] }}</div>
                    <div class="text-xs font-semibold tracking-wide mt-0.5" style="color:#bfdbfe;">{{ $s[1] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- ═══════════════════════ ABOUT ═══════════════════════ -->
<section id="about" class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="section-label">About Us</span>
            <h2 class="text-4xl font-extrabold text-slate-900 mt-4 mb-4">Why Choose {{ $school->name }}?</h2>
            <p class="text-slate-500 max-w-2xl mx-auto text-lg leading-relaxed">
                @if($school->about_us && strlen($school->about_us) > 20)
                    {{ Str::limit($school->about_us, 240) }}
                @else
                    We are committed to providing a world-class education that prepares students for the challenges and opportunities of the 21st century — nurturing intellect, character, and creativity in equal measure.
                @endif
            </p>
        </div>
        <div class="grid md:grid-cols-2 gap-8 mb-8">
            <div class="rounded-2xl p-8 border card-lift" style="background:linear-gradient(135deg,#eff6ff,#fff);border-color:#dbeafe;">
                <div class="icon-ring mb-5" style="background:#2563eb;">
                    <i class="bi bi-bullseye text-white"></i>
                </div>
                <h3 class="text-xl font-extrabold text-slate-900 mb-3">Our Mission</h3>
                <p class="text-slate-600 leading-relaxed text-sm">
                    @if($school->mission && strlen($school->mission) > 10) {{ $school->mission }}
                    @else To deliver exceptional education that inspires curiosity, builds critical thinking, and shapes responsible global citizens who contribute meaningfully to society. @endif
                </p>
            </div>
            <div class="rounded-2xl p-8 border card-lift" style="background:linear-gradient(135deg,#ecfdf5,#fff);border-color:#d1fae5;">
                <div class="icon-ring mb-5" style="background:#059669;">
                    <i class="bi bi-eye text-white"></i>
                </div>
                <h3 class="text-xl font-extrabold text-slate-900 mb-3">Our Vision</h3>
                <p class="text-slate-600 leading-relaxed text-sm">
                    @if($school->vision && strlen($school->vision) > 10) {{ $school->vision }}
                    @else To be the leading institution of academic excellence in the region, recognised for producing graduates who are innovative, ethical, and empowered to shape the future. @endif
                </p>
            </div>
        </div>
        @if($school->history && strlen($school->history) > 10)
        <div class="rounded-2xl p-8 border card-lift mb-8" style="background:linear-gradient(135deg,#fffbeb,#fff);border-color:#fde68a;">
            <div class="flex items-center gap-4 mb-4">
                <div class="icon-ring" style="background:#d97706;"><i class="bi bi-clock-history text-white"></i></div>
                <h3 class="text-xl font-extrabold text-slate-900">Our History</h3>
            </div>
            <p class="text-slate-600 leading-relaxed text-sm">{{ $school->history }}</p>
        </div>
        @endif
        @if($school->principal_message && strlen($school->principal_message) > 10)
        <div class="rounded-2xl p-8 border card-lift" style="background:linear-gradient(135deg,#faf5ff,#fff);border-color:#e9d5ff;">
            <div class="flex items-center gap-4 mb-4">
                <div class="icon-ring" style="background:#7c3aed;"><i class="bi bi-person-badge text-white"></i></div>
                <h3 class="text-xl font-extrabold text-slate-900">Principal's Message</h3>
            </div>
            <p class="text-slate-600 leading-relaxed text-sm italic">"{{ $school->principal_message }}"</p>
        </div>
        @endif
    </div>
</section>

<!-- ═══════════════════════ FEATURES ═══════════════════════ -->
<section id="features" class="py-24" style="background:linear-gradient(135deg,#f8fafc,#f1f5f9);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="section-label">What We Offer</span>
            <h2 class="text-4xl font-extrabold text-slate-900 mt-4 mb-4">Our Key Features</h2>
            <p class="text-slate-500 max-w-2xl mx-auto text-lg">Comprehensive facilities and programmes designed to give every student the best possible foundation for life.</p>
        </div>
        @php
        $feats = [
            ['bi-book-half','#2563eb','#eff6ff','Modern Curriculum','Updated syllabi aligned with national and international standards, blending academic rigour with real-world application.'],
            ['bi-display','#0891b2','#ecfeff','Smart Classrooms','Interactive boards, digital tools, and high-speed internet create an engaging, technology-powered learning environment.'],
            ['bi-trophy','#d97706','#fffbeb','Sports Facilities','Indoor and outdoor sports arenas, trained coaches, and competitive teams foster physical health and team spirit.'],
            ['bi-shield-lock','#059669','#ecfdf5','Safe Campus','24/7 CCTV surveillance, trained security personnel, and a child-safe policy ensure every student feels secure.'],
            ['bi-mortarboard','#7c3aed','#faf5ff','Expert Teachers','Highly qualified, experienced educators who are dedicated mentors inspiring and motivating every learner to excel.'],
            ['bi-journal-bookmark','#db2777','#fdf2f8','Digital Library','Thousands of e-books, research journals, and multimedia resources accessible anytime to support study and curiosity.'],
        ];
        @endphp
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($feats as $f)
            <div class="bg-white rounded-2xl p-7 shadow-md card-lift border border-slate-100">
                <div class="w-14 h-14 rounded-xl flex items-center justify-center mb-5 transition-transform duration-300"
                     style="background:{{ $f[2] }};">
                    <i class="bi {{ $f[0] }} text-2xl" style="color:{{ $f[1] }};"></i>
                </div>
                <h3 class="text-lg font-extrabold text-slate-900 mb-2">{{ $f[3] }}</h3>
                <p class="text-slate-500 text-sm leading-relaxed">{{ $f[4] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ═══════════════════ ADMISSION ═══════════════════ -->
<section id="admission" class="admission-section py-24 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-96 h-96 rounded-full blur-3xl pointer-events-none" style="background:rgba(255,255,255,.04);transform:translate(50%,-50%);"></div>
    <div class="absolute bottom-0 left-0 w-80 h-80 rounded-full blur-3xl pointer-events-none" style="background:rgba(245,200,66,.08);transform:translate(-50%,50%);"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative" style="z-index:2;">
        <div class="grid lg:grid-cols-2 gap-14 items-center">
            <div class="text-white">
                <div class="flex items-center gap-2 mb-5 text-xs font-bold tracking-widest uppercase" style="color:var(--gold-400);">
                    <i class="bi bi-star-fill"></i> Enroll Today
                </div>
                <h2 class="text-4xl font-extrabold mb-5 leading-tight">
                    Start Your Journey<br><span class="grad-text-gold">at {{ $school->name }}</span>
                </h2>
                <p class="mb-8 leading-relaxed" style="color:#bfdbfe;font-size:1.1rem;">
                    @if($school->admission_info && strlen($school->admission_info) > 20)
                        {{ Str::limit($school->admission_info, 300) }}
                    @else
                        Admissions are open for the upcoming academic year. We welcome students who are eager to learn, grow, and make a difference. Join our vibrant community and discover your potential.
                    @endif
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="{{ url('admissions/apply?school='.$school->slug) }}" class="btn-gold">
                        <i class="bi bi-pencil-square"></i> Apply for Admission
                    </a>
                    <a href="#contact" class="btn-outline-white">
                        <i class="bi bi-info-circle"></i> Enquire Now
                    </a>
                </div>
            </div>
            <div class="glass rounded-3xl p-8 shadow-2xl">
                <h3 class="text-white font-bold text-base mb-6 tracking-wide">Admission Process</h3>
                <div class="flex flex-col gap-3">
                    @foreach(['Submit online application form','Collect & verify required documents','Appear for entrance assessment','Interview with principal','Fee payment & enrollment confirmation','Receive welcome kit & timetable'] as $i => $step)
                    <div class="admission-checklist-item">
                        <div class="admission-step-num">{{ $i+1 }}</div>
                        <span class="text-sm font-medium leading-relaxed" style="color:#bfdbfe;">{{ $step }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════ RESULTS ═══════════════════ -->
<section id="results" class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="section-label" style="color:#059669;">Achievements</span>
            <h2 class="text-4xl font-extrabold text-slate-900 mt-4 mb-4">Academic Excellence</h2>
            <p class="text-slate-500 max-w-xl mx-auto text-lg">
                @if($school->result_info && strlen($school->result_info) > 10)
                    {{ Str::limit($school->result_info, 200) }}
                @else
                    Year after year, our students achieve remarkable results that reflect the quality of teaching and dedication.
                @endif
            </p>
        </div>
        @if($school->result_info && strlen($school->result_info) > 10)
        <div class="max-w-3xl mx-auto">
            <div class="rounded-2xl p-10 border card-lift shadow-lg" style="background:linear-gradient(135deg,#ecfdf5,#fff);border-color:#d1fae5;">
                <div class="flex items-center gap-4 mb-6">
                    <div class="icon-ring" style="background:#059669;"><i class="bi bi-trophy text-white"></i></div>
                    <h3 class="text-2xl font-extrabold text-slate-900">Our Excellence</h3>
                </div>
                <div class="text-slate-600 leading-relaxed whitespace-pre-line">{{ $school->result_info }}</div>
            </div>
        </div>
        @else
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach([['bi-award','#059669','#ecfdf5','#d1fae5','Top 10','District Rank'],['bi-patch-check','#2563eb','#eff6ff','#dbeafe','100%','Board Pass Rate'],['bi-star','#d97706','#fffbeb','#fde68a','A+','Average Grade'],['bi-cup-hot','#7c3aed','#faf5ff','#e9d5ff','25+','Years of Excellence']] as $r)
            <div class="rounded-2xl p-8 text-center border card-lift shadow-md" style="background:linear-gradient(135deg,{{ $r[2] }},#fff);border-color:{{ $r[3] }};">
                <i class="bi {{ $r[0] }} text-4xl block mb-4" style="color:{{ $r[1] }};"></i>
                <div class="text-3xl font-extrabold text-slate-900 mb-1">{{ $r[4] }}</div>
                <div class="text-sm font-semibold text-slate-500 uppercase tracking-wide">{{ $r[5] }}</div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</section>

<!-- ═══════════════════ GALLERY ═══════════════════ -->
@if($school->gallery_images && is_array($school->gallery_images) && count($school->gallery_images) > 0)
<section id="gallery" class="py-24" style="background:linear-gradient(135deg,#f8fafc,#f1f5f9);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="section-label" style="color:#0ea5e9;">Campus Life</span>
            <h2 class="text-4xl font-extrabold text-slate-900 mt-4 mb-4">Our Gallery</h2>
            <p class="text-slate-500 max-w-xl mx-auto text-lg">Glimpses of events, activities, and campus life at {{ $school->name }}.</p>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($school->gallery_images as $imagePath)
                <div class="relative rounded-2xl overflow-hidden shadow-md group aspect-[4/3]">
                    <img src="{{ asset('storage/' . $imagePath) }}" alt="Gallery Image" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                    <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- ═══════════════════ TEACHERS ═══════════════════ -->
@if(isset($teachers) && $teachers->count() > 0)
<section id="teachers" class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="section-label" style="color:#8b5cf6;">Our Faculty</span>
            <h2 class="text-4xl font-extrabold text-slate-900 mt-4 mb-4">Meet Our Teachers</h2>
            <p class="text-slate-500 max-w-xl mx-auto text-lg">Dedicated professionals committed to nurturing and guiding our students.</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
            @foreach($teachers as $teacher)
                <div class="bg-white rounded-3xl border border-slate-100 overflow-hidden card-lift shadow-lg group">
                    <div class="relative h-64 bg-slate-100 overflow-hidden">
                        @if($teacher->photo)
                            <img src="{{ asset('storage/' . $teacher->photo) }}" alt="{{ $teacher->first_name }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-slate-200">
                                <i class="bi bi-person text-6xl text-slate-400"></i>
                            </div>
                        @endif
                    </div>
                    <div class="p-6 text-center">
                        <h3 class="text-xl font-bold text-slate-900 mb-1">{{ $teacher->first_name }} {{ $teacher->last_name }}</h3>
                        <p class="text-sm font-semibold uppercase tracking-wider" style="color:#8b5cf6;">Faculty Member</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- ═══════════════════ NOTICES ═══════════════════ -->
@if($school->notice_board && strlen($school->notice_board) > 10)
<section id="notices" class="py-24" style="background:linear-gradient(135deg,#fffbeb,#fff);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="section-label" style="color:#d97706;">Stay Informed</span>
            <h2 class="text-4xl font-extrabold text-slate-900 mt-4 mb-4">Notice Board</h2>
            <p class="text-slate-500 max-w-xl mx-auto text-lg">Latest announcements and important updates from the school office.</p>
        </div>
        <div class="max-w-3xl mx-auto bg-white rounded-2xl p-10 border shadow-lg card-lift" style="border-color:#fde68a;">
            <div class="flex items-center gap-4 mb-6">
                <div class="icon-ring" style="background:#d97706;"><i class="bi bi-megaphone text-white"></i></div>
                <h3 class="text-2xl font-extrabold text-slate-900">Latest Announcements</h3>
            </div>
            <div class="flex flex-col gap-4">
                @foreach(array_filter(explode("\n", trim($school->notice_board))) as $line)
                @if(trim($line))
                <div class="notice-item pl-4 py-2">
                    <p class="text-slate-700 text-sm leading-relaxed">{{ trim($line) }}</p>
                </div>
                @endif
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif

<!-- ═══════════════════ CONTACT ═══════════════════ -->
<section id="contact" class="py-24" style="background:linear-gradient(135deg,#f8fafc,#fff);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="section-label">Get In Touch</span>
            <h2 class="text-4xl font-extrabold text-slate-900 mt-4 mb-4">Contact Us</h2>
            <p class="text-slate-500 max-w-xl mx-auto text-lg">We'd love to hear from you. Reach out for any enquiries about admissions, programmes, or partnerships.</p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <!-- Address -->
            <div class="bg-white rounded-2xl p-7 text-center shadow-md card-lift border border-slate-100">
                <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-5 shadow-lg" style="background:#2563eb;">
                    <i class="bi bi-geo-alt-fill text-white text-xl"></i>
                </div>
                <h3 class="font-extrabold text-slate-900 mb-2">Address</h3>
                <p class="text-slate-500 text-sm leading-relaxed">
                    {{ $school->address }}
                    @if($school->city), {{ $school->city }}@endif
                    @if($school->district)<br>{{ $school->district }}@endif
                    @if($school->province), {{ $school->province }}@endif
                    @if($school->country)<br>{{ $school->country }}@endif
                </p>
            </div>
            <!-- Phone -->
            <div class="bg-white rounded-2xl p-7 text-center shadow-md card-lift border border-slate-100">
                <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-5 shadow-lg" style="background:#059669;">
                    <i class="bi bi-telephone-fill text-white text-xl"></i>
                </div>
                <h3 class="font-extrabold text-slate-900 mb-2">Phone</h3>
                @if($school->phone)
                    <a href="tel:{{ $school->phone }}" class="font-semibold text-sm" style="color:#059669;text-decoration:none;">{{ $school->phone }}</a>
                @else
                    <p class="text-slate-400 text-sm">Not available</p>
                @endif
                @if($school->contact_person)
                    <p class="text-slate-400 text-xs mt-2">{{ $school->contact_person }}</p>
                @endif
            </div>
            <!-- Email -->
            <div class="bg-white rounded-2xl p-7 text-center shadow-md card-lift border border-slate-100">
                <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-5 shadow-lg" style="background:#d97706;">
                    <i class="bi bi-envelope-fill text-white text-xl"></i>
                </div>
                <h3 class="font-extrabold text-slate-900 mb-2">Email</h3>
                @if($school->email)
                    <a href="mailto:{{ $school->email }}" class="font-semibold text-sm break-all" style="color:#d97706;text-decoration:none;">{{ $school->email }}</a>
                @else
                    <p class="text-slate-400 text-sm">Not available</p>
                @endif
            </div>
            <!-- Website -->
            <div class="bg-white rounded-2xl p-7 text-center shadow-md card-lift border border-slate-100">
                <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-5 shadow-lg" style="background:#7c3aed;">
                    <i class="bi bi-globe text-white text-xl"></i>
                </div>
                <h3 class="font-extrabold text-slate-900 mb-2">Website</h3>
                @if($school->website)
                    <a href="{{ $school->website }}" target="_blank" rel="noopener" class="font-semibold text-sm break-all" style="color:#7c3aed;text-decoration:none;">{{ $school->website }}</a>
                @else
                    <p class="text-slate-400 text-sm">Not available</p>
                @endif
            </div>
        </div>

        <!-- CTA bar -->
        <div class="rounded-3xl p-10 flex flex-col md:flex-row items-center justify-between gap-6 shadow-2xl"
             style="background:linear-gradient(135deg,var(--navy-900),var(--navy-600));">
            <div class="text-white text-center md:text-left">
                <div class="text-2xl font-extrabold mb-2">Ready to join {{ $school->name }}?</div>
                <div class="text-sm" style="color:#bfdbfe;">Take the first step towards excellence. Apply online today.</div>
            </div>
            <div class="flex gap-4 flex-wrap justify-center">
                <a href="{{ url('admissions/apply?school='.$school->slug) }}" class="btn-gold">
                    <i class="bi bi-pencil-square"></i> Apply for Admission
                </a>
                <a href="{{ url('login') }}" class="btn-outline-white" style="padding:.875rem 1.5rem;">
                    <i class="bi bi-box-arrow-in-right"></i> Login
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════ FOOTER ═══════════════════ -->
<footer class="footer-bg text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-10">

            <!-- Brand -->
            <div class="lg:col-span-2">
                <div class="flex items-center gap-3 mb-5">
                    @if($school->logo)
                        <img src="{{ $school->logo_url }}" alt="{{ $school->name }}"
                             class="h-14 w-14 rounded-xl object-cover shadow-lg" style="ring:2px solid rgba(255,255,255,.10);">
                    @else
                        <div class="h-14 w-14 rounded-xl flex items-center justify-center shadow-lg"
                             style="background:linear-gradient(135deg,var(--navy-600),var(--navy-500));border:1px solid rgba(255,255,255,.10);">
                            <span class="text-white font-bold text-xl">{{ strtoupper(substr($school->name,0,2)) }}</span>
                        </div>
                    @endif
                    <div>
                        <div class="text-xl font-extrabold">{{ $school->name }}</div>
                        <div class="text-xs font-medium" style="color:#93c5fd;">
                            {{ $school->city ?? ($school->district ?? '') }}{{ ($school->city||$school->district) && $school->country ? ', ':'' }}{{ $school->country ?? '' }}
                        </div>
                    </div>
                </div>
                <p class="leading-relaxed text-sm max-w-sm mb-6" style="color:#94a3b8;">
                    @if($school->description && strlen($school->description) > 20)
                        {{ Str::limit($school->description, 180) }}
                    @else
                        Empowering the next generation through quality education, strong values, and holistic development since our establishment.
                    @endif
                </p>
                <div class="flex gap-3">
                    @if($school->facebook_url)
                    <a href="{{ $school->facebook_url }}" target="_blank" rel="noopener" aria-label="Facebook" class="social-btn" style="background:rgba(255,255,255,.06);" onmouseover="this.style.background='#2563eb'" onmouseout="this.style.background='rgba(255,255,255,.06)'">
                        <i class="bi bi-facebook"></i>
                    </a>
                    @endif
                    @if($school->twitter_url)
                    <a href="{{ $school->twitter_url }}" target="_blank" rel="noopener" aria-label="Twitter / X" class="social-btn" style="background:rgba(255,255,255,.06);" onmouseover="this.style.background='#0f172a'" onmouseout="this.style.background='rgba(255,255,255,.06)'">
                        <i class="bi bi-twitter-x"></i>
                    </a>
                    @endif
                    @if($school->instagram_url)
                    <a href="{{ $school->instagram_url }}" target="_blank" rel="noopener" aria-label="Instagram" class="social-btn" style="background:rgba(255,255,255,.06);" onmouseover="this.style.background='#db2777'" onmouseout="this.style.background='rgba(255,255,255,.06)'">
                        <i class="bi bi-instagram"></i>
                    </a>
                    @endif
                    @if($school->youtube_url)
                    <a href="{{ $school->youtube_url }}" target="_blank" rel="noopener" aria-label="YouTube" class="social-btn" style="background:rgba(255,255,255,.06);" onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='rgba(255,255,255,.06)'">
                        <i class="bi bi-youtube"></i>
                    </a>
                    @endif
                    {{-- Fallback placeholders when no social links set yet --}}
                    @if(!$school->facebook_url && !$school->twitter_url && !$school->instagram_url && !$school->youtube_url)
                    @foreach([['bi-facebook','#2563eb'],['bi-twitter-x','#0f172a'],['bi-instagram','#db2777'],['bi-youtube','#dc2626']] as [$ico,$hov])
                    <a href="{{ route('school.profile.edit', ['step'=>2]) }}" title="Add social link in School Profile" class="social-btn opacity-40" style="background:rgba(255,255,255,.06);" onmouseover="this.style.background='{{ $hov }}';this.style.opacity=1" onmouseout="this.style.background='rgba(255,255,255,.06)';this.style.opacity='.4'">
                        <i class="bi {{ $ico }}"></i>
                    </a>
                    @endforeach
                    @endif
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 class="text-sm font-extrabold uppercase tracking-widest mb-6" style="color:var(--gold-400);">Quick Links</h4>
                <div class="flex flex-col gap-3">
                    @foreach([['#about','About Us'],['#features','Features'],['#admission','Admissions'],['#results','Results'],['#notices','Notices'],['#contact','Contact Us']] as $lnk)
                    <a href="{{ $lnk[0] }}" class="footer-link group" style="text-decoration:none;">
                        <i class="bi bi-chevron-right text-xs transition-transform group-hover:translate-x-1" style="color:var(--gold-500);"></i>
                        {{ $lnk[1] }}
                    </a>
                    @endforeach
                </div>
            </div>

            <!-- Contact info -->
            <div>
                <h4 class="text-sm font-extrabold uppercase tracking-widest mb-6" style="color:var(--gold-400);">Contact Info</h4>
                <div class="flex flex-col gap-4">
                    @if($school->address)
                    <div class="flex items-start gap-3 text-sm" style="color:#94a3b8;">
                        <i class="bi bi-geo-alt-fill mt-0.5 shrink-0" style="color:var(--gold-400);"></i>
                        <span>{{ $school->address }}{{ $school->city ? ', '.$school->city : '' }}{{ $school->district ? ', '.$school->district : '' }}</span>
                    </div>
                    @endif
                    @if($school->phone)
                    <a href="tel:{{ $school->phone }}" class="flex items-center gap-3 text-sm transition hover:text-white" style="color:#94a3b8;text-decoration:none;">
                        <i class="bi bi-telephone-fill shrink-0" style="color:var(--gold-400);"></i>
                        {{ $school->phone }}
                    </a>
                    @endif
                    @if($school->email)
                    <a href="mailto:{{ $school->email }}" class="flex items-center gap-3 text-sm transition hover:text-white break-all" style="color:#94a3b8;text-decoration:none;">
                        <i class="bi bi-envelope-fill shrink-0" style="color:var(--gold-400);"></i>
                        {{ $school->email }}
                    </a>
                    @endif
                    @if($school->website)
                    <a href="{{ $school->website }}" target="_blank" rel="noopener" class="flex items-center gap-3 text-sm transition hover:text-white break-all" style="color:#94a3b8;text-decoration:none;">
                        <i class="bi bi-globe shrink-0" style="color:var(--gold-400);"></i>
                        {{ $school->website }}
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom bar -->
    <div class="footer-divider border-t">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-sm text-center sm:text-left" style="color:#64748b;">
                &copy; {{ date('Y') }} {{ $school->name }}. All rights reserved.
            </p>
            <div class="flex items-center gap-4 text-sm" style="color:#64748b;">
                <a href="{{ url('login') }}" class="hover:text-white transition" style="text-decoration:none;">Portal Login</a>
                <span style="color:rgba(255,255,255,.15);">|</span>
                <a href="{{ url('admissions/apply?school='.$school->slug) }}" class="transition hover:text-white" style="color:var(--gold-400);text-decoration:none;">Apply for Admission</a>
            </div>
        </div>
    </div>
</footer>

<!-- Back to top -->
<button id="back-to-top" aria-label="Back to top"
        class="fixed bottom-8 right-6 w-12 h-12 text-white rounded-xl shadow-xl flex items-center justify-center transition-all duration-300"
        style="background-color:var(--navy-800);opacity:0;transform:translateY(1rem);pointer-events:none;z-index:50;">
    <i class="bi bi-arrow-up-short text-xl"></i>
</button>

<script>
    const btt = document.getElementById('back-to-top');
    window.addEventListener('scroll', () => {
        const show = window.scrollY > 400;
        btt.style.opacity = show ? '1' : '0';
        btt.style.transform = show ? 'translateY(0)' : 'translateY(1rem)';
        btt.style.pointerEvents = show ? 'auto' : 'none';
    }, { passive: true });
    btt.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
</script>

</body>
</html>
