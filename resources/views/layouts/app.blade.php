<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'School ERP'))</title>

    {{-- Favicons — NepalSchoolHub logo --}}
    <link rel="shortcut icon" href="{{ asset('imgs/logo_icon_32.webp') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('imgs/logo_icon_512.webp') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('imgs/logo_icon_192.webp') }}">
    <link rel="icon" type="image/png" sizes="64x64"   href="{{ asset('imgs/logo_icon_64.webp') }}">
    <link rel="icon" type="image/png" sizes="32x32"   href="{{ asset('imgs/logo_icon_32.webp') }}">
    <link rel="icon" type="image/png" sizes="16x16"   href="{{ asset('imgs/logo_icon_32.webp') }}">
    <link rel="apple-touch-icon" sizes="180x180"      href="{{ asset('imgs/logo_icon_192.webp') }}">

    {{-- Module 23: PWA --}}
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#6366f1">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'SchoolHub') }}">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    {{-- Bootstrap CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

    {{-- ApexCharts --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.0/dist/apexcharts.min.js"></script>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Compiled CSS (Tailwind + custom) --}}
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    {{-- Loading indicator — must be first so ErpLoader is available to inline scripts --}}
    <script src="{{ asset('js/loader.js') }}?v={{ filemtime(public_path('js/loader.js')) }}" defer></script>

    <style>
        /* ── Sidebar Navigation Custom Styles ─────────────────────────────── */
        .sidebar-nav {
            display: flex !important;
            flex-direction: column !important;
            width: 100% !important;
            padding: 0.25rem 0.25rem !important;
        }

        .sidebar-section-label {
            display: block !important;
            font-size: 0.65rem !important;
            font-weight: 700 !important;
            letter-spacing: 0.08em !important;
            text-transform: uppercase !important;
            color: #64748b !important;
            margin-top: 1.25rem !important;
            margin-bottom: 0.35rem !important;
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
        }
        .sidebar-section-label:first-child {
            margin-top: 0.25rem !important;
        }

        .sidebar-link {
            display: flex !important;
            align-items: center !important;
            gap: 0.75rem !important;
            padding: 0.55rem 0.75rem !important;
            margin-bottom: 0.2rem !important;
            border-radius: 0.75rem !important;
            font-size: 0.8125rem !important;
            font-weight: 500 !important;
            color: #94a3b8 !important;
            text-decoration: none !important;
            transition: all 0.15s ease-in-out !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }

        .sidebar-link:hover {
            color: #ffffff !important;
            background-color: rgba(255, 255, 255, 0.06) !important;
        }

        .sidebar-link.active {
            color: #ffffff !important;
            background-color: #2563eb !important;
            font-weight: 600 !important;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35) !important;
        }

        .sidebar-link-icon {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 1.25rem !important;
            height: 1.25rem !important;
            font-size: 0.95rem !important;
            flex-shrink: 0 !important;
            color: inherit !important;
        }

        .sidebar-link-text {
            flex: 1 !important;
            min-width: 0 !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        .sidebar-badge {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0.1rem 0.45rem !important;
            font-size: 0.65rem !important;
            font-weight: 700 !important;
            border-radius: 9999px !important;
            background-color: #2563eb !important;
            color: #ffffff !important;
            margin-left: auto !important;
            flex-shrink: 0 !important;
        }
        .sidebar-link.active .sidebar-badge {
            background-color: rgba(255, 255, 255, 0.25) !important;
            color: #ffffff !important;
        }
    </style>

    <style>
    /* ── Professional card style — solid white cards that stay visible ── */
    main .bg-white {
        background-color: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 1px 8px rgba(0, 0, 0, 0.07) !important;
    }
    /* When a background theme image is active, add subtle glass effect */
    main.has-bg-theme .bg-white {
        background-color: rgba(255, 255, 255, 0.92) !important;
        backdrop-filter: blur(8px) !important;
        -webkit-backdrop-filter: blur(8px) !important;
        border: 1px solid rgba(255, 255, 255, 0.7) !important;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08) !important;
    }

    /* ── Prevent Bootstrap body color from overriding Tailwind text utilities ── */
    /* Bootstrap sets body{color:#212529} which cascades and beats Tailwind's non-!important utilities */
    main .text-slate-900  { color: rgb(15 23 42)     !important; }
    main .text-slate-800  { color: rgb(30 41 59)     !important; }
    main .text-slate-700  { color: rgb(51 65 85)     !important; }
    main .text-slate-600  { color: rgb(71 85 105)    !important; }
    main .text-slate-500  { color: rgb(100 116 139)  !important; }
    main .text-slate-400  { color: rgb(148 163 184)  !important; }
    main .text-slate-300  { color: rgb(203 213 225)  !important; }
    main .text-slate-200  { color: rgb(226 232 240)  !important; }
    main .text-white      { color: #ffffff            !important; }
    main .text-emerald-600{ color: rgb(5 150 105)    !important; }
    main .text-emerald-500{ color: rgb(16 185 129)   !important; }
    main .text-emerald-400{ color: rgb(52 211 153)   !important; }
    main .text-rose-600   { color: rgb(225 29 72)    !important; }
    main .text-rose-500   { color: rgb(239 68 68)    !important; }
    main .text-rose-700   { color: rgb(190 18 60)    !important; }
    main .text-amber-600  { color: rgb(217 119 6)    !important; }
    main .text-amber-700  { color: rgb(180 83 9)     !important; }
    main .text-amber-500  { color: rgb(245 158 11)   !important; }
    main .text-blue-600   { color: rgb(37 99 235)    !important; }
    main .text-blue-500   { color: rgb(59 130 246)   !important; }
    main .text-blue-400   { color: rgb(96 165 250)   !important; }
    main .text-indigo-600 { color: rgb(79 70 229)    !important; }
    main .text-indigo-500 { color: rgb(99 102 241)   !important; }
    main .text-indigo-400 { color: rgb(129 140 248)  !important; }
    main .text-indigo-300 { color: rgb(165 180 252)  !important; }
    main .text-teal-600   { color: rgb(13 148 136)   !important; }
    main .text-teal-500   { color: rgb(20 184 166)   !important; }
    main .text-violet-500 { color: rgb(139 92 246)   !important; }
    main .text-violet-400 { color: rgb(167 139 250)  !important; }
    main .text-purple-500 { color: rgb(168 85 247)   !important; }
    main .text-pink-500   { color: rgb(236 72 153)   !important; }
    main .text-pink-700   { color: rgb(190 24 93)    !important; }
    main .text-green-600  { color: rgb(22 163 74)    !important; }

    /* ═══════════════════════════════════════════════════════════════
       CUSTOM BUTTON VARIANTS
       These must live here (after Bootstrap 5 CDN) because Bootstrap's
       .btn rules would otherwise override them. The classes btn-indigo,
       btn-emerald, btn-blue, btn-sky, btn-purple are not part of Bootstrap.
    ═══════════════════════════════════════════════════════════════ */

    /* Base: make sure gradient bg-image fires on .btn */
    .btn[class*="from-"],
    .btn[class*="bg-gradient"] {
        background-image: linear-gradient(to right, var(--tw-gradient-stops)) !important;
        border-color: transparent !important;
    }
    .btn.bg-gradient-to-r  { background-image: linear-gradient(to right,       var(--tw-gradient-stops)) !important; }
    .btn.bg-gradient-to-br { background-image: linear-gradient(to bottom right, var(--tw-gradient-stops)) !important; }

    /* Custom named variants — every non-Bootstrap .btn-* color used in the app */
    .btn-indigo  { background-color: #4f46e5 !important; color: #fff !important; border-color: transparent !important; }
    .btn-indigo:hover,  .btn-indigo:focus  { background-color: #4338ca !important; color: #fff !important; }
    .btn-emerald { background-color: #059669 !important; color: #fff !important; border-color: transparent !important; }
    .btn-emerald:hover, .btn-emerald:focus { background-color: #047857 !important; color: #fff !important; }
    .btn-blue    { background-color: #2563eb !important; color: #fff !important; border-color: transparent !important; }
    .btn-blue:hover,    .btn-blue:focus    { background-color: #1d4ed8 !important; color: #fff !important; }
    .btn-sky     { background-color: #0284c7 !important; color: #fff !important; border-color: transparent !important; }
    .btn-sky:hover,     .btn-sky:focus     { background-color: #0369a1 !important; color: #fff !important; }
    .btn-purple  { background-color: #9333ea !important; color: #fff !important; border-color: transparent !important; }
    .btn-purple:hover,  .btn-purple:focus  { background-color: #7e22ce !important; color: #fff !important; }
    .btn-teal    { background-color: #0d9488 !important; color: #fff !important; border-color: transparent !important; }
    .btn-teal:hover,    .btn-teal:focus    { background-color: #0f766e !important; color: #fff !important; }
    .btn-amber   { background-color: #d97706 !important; color: #fff !important; border-color: transparent !important; }
    .btn-amber:hover,   .btn-amber:focus   { background-color: #b45309 !important; color: #fff !important; }
    .btn-rose    { background-color: #e11d48 !important; color: #fff !important; border-color: transparent !important; }
    .btn-rose:hover,    .btn-rose:focus    { background-color: #be123c !important; color: #fff !important; }
    .btn-cyan    { background-color: #0891b2 !important; color: #fff !important; border-color: transparent !important; }
    .btn-cyan:hover,    .btn-cyan:focus    { background-color: #0e7490 !important; color: #fff !important; }
    .btn-navy    { background-color: #1e3a5f !important; color: #fff !important; border-color: transparent !important; }
    .btn-navy:hover,    .btn-navy:focus    { background-color: #162d49 !important; color: #fff !important; }
    .btn-gold    { background-color: #b8953e !important; color: #fff !important; border-color: transparent !important; }
    .btn-gold:hover,    .btn-gold:focus    { background-color: #9a7c34 !important; color: #fff !important; }
    .btn-print   { background-color: #475569 !important; color: #fff !important; border-color: transparent !important; }
    .btn-print:hover,   .btn-print:focus   { background-color: #334155 !important; color: #fff !important; }
    .btn-back    { background-color: #e2e8f0 !important; color: #475569 !important; border-color: transparent !important; }
    .btn-back:hover,    .btn-back:focus    { background-color: #cbd5e1 !important; color: #334155 !important; }
    .btn-icon    { background-color: #f1f5f9 !important; color: #475569 !important; border-color: #e2e8f0 !important; }
    .btn-icon:hover,    .btn-icon:focus    { background-color: #e2e8f0 !important; color: #1e293b !important; }

    /* Gradient FROM stops for .btn */
    .btn.from-indigo-600 { --tw-gradient-from: #4f46e5; --tw-gradient-to: rgba(79,70,229,0);   --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
    .btn.from-indigo-700 { --tw-gradient-from: #4338ca; --tw-gradient-to: rgba(67,56,202,0);   --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
    .btn.from-blue-600   { --tw-gradient-from: #2563eb; --tw-gradient-to: rgba(37,99,235,0);   --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
    .btn.from-blue-700   { --tw-gradient-from: #1d4ed8; --tw-gradient-to: rgba(29,78,216,0);   --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
    .btn.from-purple-600 { --tw-gradient-from: #9333ea; --tw-gradient-to: rgba(147,51,234,0);  --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
    .btn.from-purple-700 { --tw-gradient-from: #7e22ce; --tw-gradient-to: rgba(126,34,206,0);  --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
    .btn.from-emerald-500{ --tw-gradient-from: #10b981; --tw-gradient-to: rgba(16,185,129,0);  --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
    .btn.from-emerald-600{ --tw-gradient-from: #059669; --tw-gradient-to: rgba(5,150,105,0);   --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
    .btn.from-sky-600    { --tw-gradient-from: #0284c7; --tw-gradient-to: rgba(2,132,199,0);   --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
    .btn.from-sky-700    { --tw-gradient-from: #0369a1; --tw-gradient-to: rgba(3,105,161,0);   --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
    .btn.from-amber-500  { --tw-gradient-from: #f59e0b; --tw-gradient-to: rgba(245,158,11,0);  --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
    .btn.from-amber-600  { --tw-gradient-from: #d97706; --tw-gradient-to: rgba(217,119,6,0);   --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
    .btn.from-teal-500   { --tw-gradient-from: #14b8a6; --tw-gradient-to: rgba(20,184,166,0);  --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
    .btn.from-teal-600   { --tw-gradient-from: #0d9488; --tw-gradient-to: rgba(13,148,136,0);  --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
    .btn.from-rose-500   { --tw-gradient-from: #f43f5e; --tw-gradient-to: rgba(244,63,94,0);   --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
    .btn.from-rose-600   { --tw-gradient-from: #e11d48; --tw-gradient-to: rgba(225,29,72,0);   --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
    .btn.from-cyan-500   { --tw-gradient-from: #06b6d4; --tw-gradient-to: rgba(6,182,212,0);   --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
    .btn.from-cyan-600   { --tw-gradient-from: #0891b2; --tw-gradient-to: rgba(8,145,178,0);   --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }

    /* Gradient TO stops for .btn */
    .btn.to-indigo-600   { --tw-gradient-to: #4f46e5; }
    .btn.to-indigo-700   { --tw-gradient-to: #4338ca; }
    .btn.to-indigo-800   { --tw-gradient-to: #3730a3; }
    .btn.to-blue-600     { --tw-gradient-to: #2563eb; }
    .btn.to-blue-700     { --tw-gradient-to: #1d4ed8; }
    .btn.to-purple-600   { --tw-gradient-to: #9333ea; }
    .btn.to-purple-700   { --tw-gradient-to: #7e22ce; }
    .btn.to-violet-600   { --tw-gradient-to: #7c3aed; }
    .btn.to-violet-700   { --tw-gradient-to: #6d28d9; }
    .btn.to-teal-600     { --tw-gradient-to: #0d9488; }
    .btn.to-teal-700     { --tw-gradient-to: #0f766e; }
    .btn.to-amber-600    { --tw-gradient-to: #d97706; }
    .btn.to-amber-700    { --tw-gradient-to: #b45309; }
    .btn.to-rose-600     { --tw-gradient-to: #e11d48; }
    .btn.to-rose-700     { --tw-gradient-to: #be123c; }
    .btn.to-cyan-600     { --tw-gradient-to: #0891b2; }
    .btn.to-cyan-700     { --tw-gradient-to: #0e7490; }

    /* Hover gradient changes */
    .btn.hover\:from-indigo-700:hover  { --tw-gradient-from: #4338ca; --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
    .btn.hover\:from-indigo-800:hover  { --tw-gradient-from: #3730a3; --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
    .btn.hover\:from-blue-700:hover    { --tw-gradient-from: #1d4ed8; --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
    .btn.hover\:from-purple-700:hover  { --tw-gradient-from: #7e22ce; --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
    .btn.hover\:from-emerald-600:hover { --tw-gradient-from: #059669; --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
    .btn.hover\:from-sky-700:hover     { --tw-gradient-from: #0369a1; --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
    .btn.hover\:from-amber-700:hover   { --tw-gradient-from: #b45309; --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
    .btn.hover\:from-teal-700:hover    { --tw-gradient-from: #0f766e; --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
    .btn.hover\:from-rose-700:hover    { --tw-gradient-from: #be123c; --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
    .btn.hover\:from-cyan-700:hover    { --tw-gradient-from: #0e7490; --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
    .btn.hover\:to-indigo-700:hover    { --tw-gradient-to: #4338ca; }
    .btn.hover\:to-indigo-800:hover    { --tw-gradient-to: #3730a3; }
    .btn.hover\:to-blue-700:hover      { --tw-gradient-to: #1d4ed8; }
    .btn.hover\:to-purple-700:hover    { --tw-gradient-to: #7e22ce; }
    .btn.hover\:to-teal-700:hover      { --tw-gradient-to: #0f766e; }
    .btn.hover\:to-violet-700:hover    { --tw-gradient-to: #6d28d9; }
    .btn.hover\:to-emerald-700:hover   { --tw-gradient-to: #047857; }
    .btn.hover\:to-amber-700:hover     { --tw-gradient-to: #b45309; }
    .btn.hover\:to-rose-700:hover      { --tw-gradient-to: #be123c; }
    .btn.hover\:to-cyan-700:hover      { --tw-gradient-to: #0e7490; }

    /* Solid bg- classes used directly without gradient (e.g. btn-emerald bg-emerald-600) */
    .btn.bg-emerald-600  { background-color: #059669 !important; }
    .btn.bg-emerald-700  { background-color: #047857 !important; }
    .btn.bg-indigo-600   { background-color: #4f46e5 !important; }
    .btn.bg-blue-600     { background-color: #2563eb !important; }
    .btn.bg-sky-600      { background-color: #0284c7 !important; }
    .btn.bg-purple-600   { background-color: #9333ea !important; }
    .btn.bg-purple-700   { background-color: #7e22ce !important; }
    .btn.bg-teal-600     { background-color: #0d9488 !important; }
    .btn.bg-amber-500    { background-color: #f59e0b !important; }
    .btn.bg-amber-600    { background-color: #d97706 !important; }
    .btn.bg-rose-500     { background-color: #f43f5e !important; }
    .btn.bg-rose-600     { background-color: #e11d48 !important; }
    .btn.bg-cyan-600     { background-color: #0891b2 !important; }

    /* Ensure white text always shows on colored buttons */
    .btn.text-white { color: #ffffff !important; }
    .btn-indigo .text-white, .btn-emerald .text-white,
    .btn-blue .text-white, .btn-sky .text-white, .btn-purple .text-white { color: #ffffff !important; }
    </style>

    @stack('head-scripts')

    {{-- Inline critical theme vars so there's no flash --}}
    <script>
        (function(){
            var t = localStorage.getItem('erp_theme') || 'light';
            document.documentElement.setAttribute('data-theme', t);
            if(t==='dark') document.documentElement.classList.add('dark');
        })();
    </script>
</head>
<body class="h-full font-sans antialiased bg-surface text-body transition-colors duration-200">

@auth
{{-- ═══════════════════════════════════════════════════════════
     FULL APP SHELL (authenticated)
═══════════════════════════════════════════════════════════ --}}
<div class="flex h-full" id="app-shell">

    {{-- ─── SIDEBAR OVERLAY (mobile) ───────────────────────────────── --}}
    <div id="sidebar-overlay" class="fixed inset-0 bg-black/40 z-30 hidden lg:hidden" onclick="closeSidebar()"></div>

    {{-- ─── SIDEBAR ─────────────────────────────────────────────────── --}}
    <aside id="sidebar"
           class="fixed inset-y-0 left-0 z-40 flex flex-col w-60 transition-transform duration-300 ease-in-out
                  -translate-x-full lg:translate-x-0 lg:static lg:inset-auto lg:z-auto"
           style="background:url('/imgs/sidebarbg.webp') center center / cover no-repeat;border-right:1px solid rgba(255,255,255,.06)">

        {{-- Logo / Brand --}}
        <div class="flex items-center justify-between h-16 px-5 flex-shrink-0" style="border-bottom:1px solid rgba(255,255,255,.07)">
            <a href="{{ url('/') }}" class="flex items-center gap-2.5 min-w-0 no-underline">
                <span class="w-8 h-8 rounded-lg overflow-hidden flex-shrink-0 shadow-lg">
                    <img src="{{ asset('imgs/logo_icon.webp') }}" alt="Logo" class="w-full h-full object-cover">
                </span>
                <div class="min-w-0">
                    <span class="font-black text-sm truncate block leading-tight text-white" style="letter-spacing:-.01em">Nepal<span class="text-indigo-400">School</span>Hub</span>
                    @auth
                    @if(!empty($currentSchool))
                    <span class="text-[9px] truncate block leading-tight" style="color:rgba(255,255,255,.4)">{{ $currentSchool->name }}</span>
                    @endif
                    @endauth
                </div>
            </a>
            <button onclick="closeSidebar()"
                    class="lg:hidden"
                    style="background: rgba(255, 255, 255, 0.12) !important; color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.25) !important; border-radius: 0.5rem !important; padding: 0.35rem 0.6rem !important; display: flex !important; align-items: center !important; justify-content: center !important; cursor: pointer !important;"
                    aria-label="Close sidebar">
                <i class="bi bi-x-lg" style="font-size: 1.125rem !important; color: #ffffff !important;"></i>
            </button>
        </div>

        {{-- Session badge --}}
        @if(!auth()->user()->isSuperAdmin())
        @php
            $latestSession = \App\Models\SchoolSession::latest()->first();
            $sessionName   = $latestSession?->session_name ?? null;
            $browsingOther = session()->has('browse_session_name') && session('browse_session_name') !== $sessionName;
        @endphp
        @if($sessionName)
        <div class="mx-3 mt-3 px-3 py-1.5 rounded-lg text-[10px] font-medium flex items-center gap-1.5 flex-shrink-0"
             style="{{ $browsingOther ? 'background:rgba(251,191,36,.12);color:#fbbf24;border:1px solid rgba(251,191,36,.25)' : 'background:rgba(99,102,241,.15);color:#a5b4fc' }}">
            <i class="bi bi-calendar3 flex-shrink-0"></i>
            <span class="truncate">
                @if($browsingOther)Browsing: {{ session('browse_session_name') }}
                @else{{ $sessionName }}@endif
            </span>
        </div>
        @else
        <div class="mx-3 mt-3 px-3 py-1.5 rounded-lg text-[10px] flex items-center gap-1.5" style="background:rgba(239,68,68,.12);color:#f87171">
            <i class="bi bi-exclamation-diamond-fill"></i> Create an Academic Session
        </div>
        @endif
        @endif

        {{-- Nav (scrollable) --}}
        <div class="flex-1 overflow-y-auto overflow-x-hidden py-3 sidebar-scroll px-2">
            @include('layouts.left-menu')
        </div>

        {{-- Sidebar footer user --}}
        <div class="flex-shrink-0 px-4 py-3" style="border-top:1px solid rgba(255,255,255,.07)">
            <div class="flex items-center gap-2.5">
                @if(auth()->user()->isSuperAdmin())
                <div class="w-8 h-8 rounded-full bg-blue-600 text-white text-xs font-black flex items-center justify-center flex-shrink-0 shadow-xs">SA</div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold text-white truncate">Super Admin</p>
                    <p class="text-[10px] truncate" style="color:rgba(255,255,255,.4)">{{ auth()->user()->email ?? 'superadmin@nshub.com' }}</p>
                </div>
                @else
                <img src="{{ auth()->user()->avatar }}" class="w-8 h-8 rounded-full object-cover flex-shrink-0 ring-2 ring-indigo-500/30" alt="">
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-white truncate">{{ auth()->user()->full_name }}</p>
                    <p class="text-[10px] capitalize truncate" style="color:rgba(255,255,255,.4)">{{ str_replace('-', ' ', auth()->user()->primary_role) }}</p>
                </div>
                @endif
                <a href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('main-logout-form').submit();"
                   class="p-1.5 rounded-md transition" style="color:rgba(255,255,255,.35)" title="Logout"
                   onmouseover="this.style.color='#f87171'" onmouseout="this.style.color='rgba(255,255,255,.35)'">
                    <i class="bi bi-box-arrow-right text-sm"></i>
                </a>
            </div>
            <form id="main-logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
        </div>
    </aside>

    {{-- ─── MAIN AREA ───────────────────────────────────────────────── --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        {{-- ─── TOP BAR (Constant across screen sizes) ────────────── --}}
        <header class="flex-shrink-0 h-14 flex items-center px-4 lg:px-5 gap-3 z-30 sticky top-0 w-full transition-colors"
                style="background:#ffffff;border-bottom:1px solid #e5e7eb;box-shadow:0 1px 4px rgba(0,0,0,.06)">

            {{-- Mobile hamburger (hidden on large screens) --}}
            <button onclick="openSidebar()"
                    class="lg:hidden"
                    style="background: #f1f5f9 !important; color: #0f172a !important; border: 1px solid #cbd5e1 !important; border-radius: 0.5rem !important; padding: 0.35rem 0.6rem !important; display: flex !important; align-items: center !important; justify-content: center !important; cursor: pointer !important;"
                    aria-label="Open sidebar">
                <i class="bi bi-list" style="font-size: 1.25rem !important; color: #0f172a !important;"></i>
            </button>

            {{-- Search bar --}}
            <button onclick="openCommandPalette()"
                    class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs cursor-pointer transition hidden md:flex"
                    style="background:#f8fafc;border:1px solid #e2e8f0;color:#94a3b8;min-width:220px">
                <i class="bi bi-search text-xs"></i>
                <span class="flex-1 text-left truncate">Search schools, users, invoices...</span>
                <kbd class="text-[9px] font-mono px-1 py-0.5 rounded" style="background:#fff;border:1px solid #e2e8f0;color:#94a3b8">⌘K</kbd>
            </button>

            {{-- Spacer --}}
            <div class="flex-1"></div>

            {{-- Add School primary button (super-admin only) --}}
            @role('super-admin')
            <a href="{{ route('saas.register') }}"
               class="hidden sm:inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-white transition hover:opacity-90"
               style="background:#2563eb">
                <i class="bi bi-plus-lg text-xs"></i> Add School
                <i class="bi bi-chevron-down text-[9px] opacity-70"></i>
            </a>
            @endrole

            {{-- Icon buttons --}}
            <div class="flex items-center gap-1">

                {{-- Calendar --}}
                <a href="{{ route('events.show') }}"
                   class="w-9 h-9 rounded-xl flex items-center justify-center transition"
                   style="color:#6b7280" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'"
                   title="Calendar">
                    <i class="bi bi-calendar3 text-sm"></i>
                </a>

                {{-- Messages --}}
                <div class="relative">
                    <a href="#"
                       class="w-9 h-9 rounded-xl flex items-center justify-center transition"
                       style="color:#6b7280" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'"
                       title="Messages">
                        <i class="bi bi-chat-left-dots text-sm"></i>
                    </a>
                    <span class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-rose-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center leading-none pointer-events-none">3</span>
                </div>

                {{-- Theme picker (replaces dark mode toggle) --}}
                <div class="relative" id="theme-picker-wrapper">
                    <button onclick="toggleThemePicker()"
                            class="w-9 h-9 rounded-xl flex items-center justify-center transition"
                            style="color:#6b7280" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'"
                            title="Choose background theme" id="theme-picker-btn">
                        <i class="bi bi-image text-sm"></i>
                    </button>

                    {{-- Dropdown --}}
                    <div id="theme-picker-panel"
                         class="hidden absolute right-0 mt-2 rounded-2xl shadow-floating border overflow-hidden z-50 p-3"
                         style="width:260px;background:#fff;border-color:#e5e7eb;top:100%">
                        <p class="text-xs font-semibold mb-2.5 px-0.5" style="color:#6b7280">Background Theme</p>
                        <div class="grid grid-cols-4 gap-2">
                            {{-- No background option --}}
                            <button onclick="setDashboardTheme('none')"
                                    id="theme-opt-none"
                                    class="theme-opt rounded-lg overflow-hidden border-2 flex items-center justify-center transition"
                                    style="height:44px;border-color:#e5e7eb;background:#f8fafc"
                                    title="No background">
                                <i class="bi bi-slash-circle text-slate-400 text-sm"></i>
                            </button>
                            @for($i = 1; $i <= 8; $i++)
                            <button onclick="setDashboardTheme('theme{{ $i }}')"
                                    id="theme-opt-theme{{ $i }}"
                                    class="theme-opt rounded-lg overflow-hidden border-2 transition p-0"
                                    style="height:44px;border-color:#e5e7eb"
                                    title="Theme {{ $i }}">
                                <img src="/imgs/theme{{ $i }}.png" alt="Theme {{ $i }}"
                                     class="w-full h-full object-cover">
                            </button>
                            @endfor
                        </div>
                        <div class="mt-3 pt-2.5 border-t" style="border-color:#f1f5f9">
                            <button onclick="toggleTheme()"
                                    class="w-full flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs transition hover:bg-slate-50"
                                    style="color:#6b7280">
                                <i class="bi bi-moon-fill dark:hidden text-sm"></i>
                                <i class="bi bi-sun-fill hidden dark:inline text-sm"></i>
                                <span class="dark:hidden">Switch to Dark Mode</span>
                                <span class="hidden dark:inline">Switch to Light Mode</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Notifications --}}
                @php try { $unreadCount = auth()->user()->unreadNotifications()->count(); } catch(\Throwable $e){ $unreadCount=0; } @endphp
                <div class="relative" id="notif-wrapper">
                    <button onclick="toggleNotifPanel()"
                            class="w-9 h-9 rounded-xl flex items-center justify-center transition"
                            style="color:#6b7280" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'"
                            aria-label="Notifications">
                        <i class="bi bi-bell text-sm"></i>
                        <span id="notif-badge" class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-rose-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center leading-none pointer-events-none">
                            {{ $unreadCount > 0 ? ($unreadCount > 9 ? '9+' : $unreadCount) : '8' }}
                        </span>
                    </button>
                    {{-- Notification panel --}}
                    <div id="notif-panel"
                         class="absolute right-0 top-full mt-2 w-80 bg-white border border-slate-200 rounded-2xl shadow-floating z-50 hidden overflow-hidden">
                        <div class="px-4 py-3 border-b border-slate-100 flex justify-between items-center">
                            <p class="font-semibold text-sm text-slate-700">Notifications</p>
                            @if($unreadCount > 0)
                            <form action="{{ route('notifications.markAllRead') }}" method="POST" class="inline">@csrf
                                <button type="submit" class="text-xs text-blue-600 hover:underline">Mark all read</button>
                            </form>
                            @endif
                        </div>
                        <div class="max-h-72 overflow-y-auto">
                            @forelse(auth()->user()->notifications()->latest()->take(8)->get() as $notif)
                            <div class="px-4 py-3 flex items-start gap-3 hover:bg-slate-50 transition {{ is_null($notif->read_at) ? 'bg-blue-50/50' : '' }}">
                                <span class="mt-0.5 w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 text-xs {{ is_null($notif->read_at) ? 'bg-blue-100 text-blue-600' : 'bg-slate-100 text-slate-400' }}">
                                    <i class="bi bi-bell"></i>
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs text-slate-700 leading-relaxed line-clamp-2">{{ $notif->data['message'] ?? ($notif->data['title'] ?? 'Notification') }}</p>
                                    <p class="text-[10px] text-slate-400 mt-0.5">{{ $notif->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            @empty
                            <div class="px-4 py-8 text-center">
                                <p class="text-xs text-slate-400 mt-2">No notifications yet</p>
                            </div>
                            @endforelse
                        </div>
                        <div class="px-4 py-2.5 border-t border-slate-100 bg-slate-50">
                            <a href="{{ route('notifications.index') }}" class="text-xs text-blue-600 hover:underline">View all →</a>
                        </div>
                    </div>
                </div>

                {{-- User avatar dropdown --}}
                <div class="relative ml-1" id="user-menu-wrapper">
                    <button onclick="toggleUserMenu()"
                            class="flex items-center gap-2 pl-2 pr-3 py-1.5 rounded-xl hover:bg-slate-100 transition">
                        @if(auth()->user()->isSuperAdmin())
                        <div class="w-7 h-7 rounded-full bg-blue-600 text-white text-[10px] font-black flex items-center justify-center flex-shrink-0">SA</div>
                        @else
                        <img src="{{ auth()->user()->avatar }}" class="w-7 h-7 rounded-full object-cover flex-shrink-0 border border-slate-200" alt="">
                        @endif
                        <span class="hidden sm:block text-xs font-semibold text-slate-700 max-w-[90px] truncate">
                            @if(auth()->user()->isSuperAdmin())Super Admin@else{{ auth()->user()->first_name }}@endif
                        </span>
                        <i class="bi bi-chevron-down text-[9px] text-slate-400"></i>
                    </button>
                    <div id="user-menu-panel"
                         class="absolute right-0 top-full mt-2 w-44 bg-white border border-slate-200 rounded-xl shadow-floating z-50 hidden py-1 overflow-hidden">
                        <div class="px-3 py-2 border-b border-slate-100">
                            <p class="text-xs font-semibold text-slate-700 truncate">{{ auth()->user()->full_name }}</p>
                            <p class="text-[10px] text-slate-400 truncate">{{ auth()->user()->email }}</p>
                        </div>
                        <a href="{{ route('password.change') }}" class="flex items-center gap-2 px-3 py-2 text-xs text-slate-600 hover:bg-slate-50 transition"><i class="bi bi-key w-4"></i> Change Password</a>
                        <a href="{{ route('two-factor.setup') }}" class="flex items-center gap-2 px-3 py-2 text-xs text-slate-600 hover:bg-slate-50 transition"><i class="bi bi-shield-lock w-4"></i> Security (2FA)</a>
                        <div class="border-t border-slate-100 mt-1 pt-1">
                            <a href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('main-logout-form').submit();"
                               class="flex items-center gap-2 px-3 py-2 text-xs text-rose-600 hover:bg-rose-50 transition">
                                <i class="bi bi-box-arrow-right w-4"></i> Sign Out
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- ─── PAGE CONTENT ───────────────────────────────────────── --}}
        <main class="flex-1 overflow-y-auto overflow-x-hidden relative">

            {{-- Softening overlay — tones down the bg so cards pop cleanly --}}
            <div class="absolute inset-0 pointer-events-none" style="background:rgba(248,250,252,0.55);z-index:0"></div>

            {{-- ── Offline status banner (hidden when online) ──────────── --}}
            <div id="offline-banner"
                 class="hidden sticky top-0 z-50 w-full flex items-center justify-between gap-3 px-4 py-2 text-xs font-semibold"
                 style="background:#fef3c7;border-bottom:1px solid #fde68a;color:#92400e;">
                <span class="flex items-center gap-2">
                    <i class="bi bi-wifi-off"></i>
                    You're offline — attendance will be saved locally and synced when you reconnect.
                    <span id="offline-pending-wrapper" class="hidden items-center gap-1 bg-amber-200 text-amber-900 px-2 py-0.5 rounded-full">
                        <i class="bi bi-clock-history"></i>
                        <span id="offline-pending-count">0</span> pending
                    </span>
                </span>
                <button onclick="window.flushAttendanceSync && window.flushAttendanceSync()"
                        class="px-2.5 py-1 rounded-lg bg-amber-200 hover:bg-amber-300 transition text-amber-900 font-bold whitespace-nowrap">
                    <i class="bi bi-arrow-repeat me-1"></i>Sync Now
                </button>
            </div>

            {{-- ── Online restored toast (injected by JS) ──────────────── --}}
            <div id="online-restored-toast"
                 class="hidden fixed bottom-4 right-4 z-50 flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold shadow-lg"
                 style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;">
                <i class="bi bi-check-circle-fill"></i>
                Back online — syncing pending data…
            </div>

            <div class="relative" style="z-index:1">
            {{-- Flash messages (session) --}}
            @if(session('success') || session('status'))
            <div class="toast-auto-show" data-type="success" data-msg="{{ session('success') ?? session('status') }}"></div>
            @endif
            @if(session('error'))
            <div class="toast-auto-show" data-type="error" data-msg="{{ session('error') }}"></div>
            @endif
            @if(session('warning'))
            <div class="toast-auto-show" data-type="warning" data-msg="{{ session('warning') }}"></div>
            @endif

            @yield('content')
        </div>{{-- /z-index wrapper --}}
        </main>

    </div>{{-- /main area --}}
</div>{{-- /app shell --}}

{{-- ═══════════════════════════════════════════════════════════
     COMMAND PALETTE  (Cmd/Ctrl + K)
═══════════════════════════════════════════════════════════ --}}
<div id="cmd-palette-backdrop"
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[100] hidden items-start justify-center pt-[15vh]"
     onclick="if(event.target===this)closeCommandPalette()">
    <div class="w-full max-w-lg mx-4 bg-white dark:bg-slate-900 rounded-2xl shadow-floating border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="flex items-center gap-3 px-4 py-3 border-b border-slate-100 dark:border-slate-800">
            <i class="bi bi-search text-slate-400 flex-shrink-0"></i>
            <input id="cmd-input" type="text" placeholder="Search students, pages, actions…"
                   class="flex-1 bg-transparent text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none"
                   oninput="filterCmdResults(this.value)" onkeydown="cmdKeyNav(event)">
            <kbd class="text-[10px] bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-1.5 py-0.5 rounded text-slate-400">ESC</kbd>
        </div>
        <div id="cmd-results" class="max-h-72 overflow-y-auto py-2">
            {{-- Populated by JS --}}
        </div>
        <div class="px-4 py-2 border-t border-slate-100 dark:border-slate-800 flex items-center gap-4 text-[10px] text-slate-400">
            <span><kbd class="bg-slate-100 dark:bg-slate-800 px-1 rounded">↑↓</kbd> navigate</span>
            <span><kbd class="bg-slate-100 dark:bg-slate-800 px-1 rounded">↵</kbd> open</span>
            <span><kbd class="bg-slate-100 dark:bg-slate-800 px-1 rounded">ESC</kbd> close</span>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     TOAST CONTAINER
═══════════════════════════════════════════════════════════ --}}
<div id="toast-container" class="fixed bottom-5 right-5 z-[200] flex flex-col gap-2 pointer-events-none" aria-live="polite"></div>

{{-- ═══════════════════════════════════════════════════════════
     FLOATING ACTION BUTTON (FAB)
═══════════════════════════════════════════════════════════ --}}
<div class="fixed bottom-6 right-6 z-50 flex flex-col-reverse items-end gap-2" id="fab-group">
    {{-- FAB toggle --}}
    <button id="fab-main" onclick="toggleFab()"
            class="w-12 h-12 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full shadow-floating flex items-center justify-center transition-all duration-200 hover:scale-105"
            title="Quick actions">
        <i class="bi bi-plus-lg text-xl" id="fab-icon"></i>
    </button>
    {{-- FAB actions (hidden by default) --}}
    <div id="fab-actions" class="flex flex-col items-end gap-2 opacity-0 translate-y-2 pointer-events-none transition-all duration-200">
        @can('create students')
        <a href="{{ route('student.create.show') }}"
           class="fab-action flex items-center gap-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-soft text-slate-700 dark:text-slate-200 px-3 py-2 rounded-xl text-xs font-medium hover:bg-indigo-50 dark:hover:bg-slate-700 transition">
            <i class="bi bi-person-plus text-indigo-600"></i> New Student
        </a>
        @endcan
        @can('create notices')
        <a href="{{ route('notice.create') }}"
           class="fab-action flex items-center gap-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-soft text-slate-700 dark:text-slate-200 px-3 py-2 rounded-xl text-xs font-medium hover:bg-amber-50 dark:hover:bg-slate-700 transition">
            <i class="bi bi-megaphone text-amber-600"></i> New Notice
        </a>
        @endcan
        @can('create exams')
        <a href="{{ route('exam.create.show') }}"
           class="fab-action flex items-center gap-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-soft text-slate-700 dark:text-slate-200 px-3 py-2 rounded-xl text-xs font-medium hover:bg-rose-50 dark:hover:bg-slate-700 transition">
            <i class="bi bi-file-plus text-rose-600"></i> New Exam
        </a>
        @endcan
        <button onclick="openCommandPalette()"
                class="fab-action flex items-center gap-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-soft text-slate-700 dark:text-slate-200 px-3 py-2 rounded-xl text-xs font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition">
            <i class="bi bi-search text-slate-500"></i> Search
        </button>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     KEYBOARD SHORTCUT HELPER  (? key)
═══════════════════════════════════════════════════════════ --}}
<div id="shortcut-modal" class="fixed inset-0 bg-black/50 z-[100] hidden items-center justify-center" onclick="if(event.target===this)closeShortcutModal()">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-floating border border-slate-200 dark:border-slate-700 w-80 p-6">
        <div class="flex justify-between items-center mb-4">
            <p class="font-semibold text-slate-700 dark:text-slate-200">Keyboard Shortcuts</p>
            <button onclick="closeShortcutModal()" class="text-slate-400 hover:text-slate-600"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between items-center py-1.5 border-b border-slate-100 dark:border-slate-800">
                <span class="text-slate-600 dark:text-slate-300">Command palette</span>
                <kbd class="bg-slate-100 dark:bg-slate-800 text-slate-500 px-2 py-0.5 rounded text-xs">⌘ K</kbd>
            </div>
            <div class="flex justify-between items-center py-1.5 border-b border-slate-100 dark:border-slate-800">
                <span class="text-slate-600 dark:text-slate-300">Toggle sidebar</span>
                <kbd class="bg-slate-100 dark:bg-slate-800 text-slate-500 px-2 py-0.5 rounded text-xs">⌘ B</kbd>
            </div>
            <div class="flex justify-between items-center py-1.5 border-b border-slate-100 dark:border-slate-800">
                <span class="text-slate-600 dark:text-slate-300">Toggle dark mode</span>
                <kbd class="bg-slate-100 dark:bg-slate-800 text-slate-500 px-2 py-0.5 rounded text-xs">⌘ D</kbd>
            </div>
            <div class="flex justify-between items-center py-1.5 border-b border-slate-100 dark:border-slate-800">
                <span class="text-slate-600 dark:text-slate-300">Go to Dashboard</span>
                <kbd class="bg-slate-100 dark:bg-slate-800 text-slate-500 px-2 py-0.5 rounded text-xs">G H</kbd>
            </div>
            <div class="flex justify-between items-center py-1.5">
                <span class="text-slate-600 dark:text-slate-300">This help</span>
                <kbd class="bg-slate-100 dark:bg-slate-800 text-slate-500 px-2 py-0.5 rounded text-xs">?</kbd>
            </div>
        </div>
    </div>
</div>

@endauth

@guest
{{-- ═══════════════════════════════════════════════════════════
     GUEST SHELL (login / register pages)
═══════════════════════════════════════════════════════════ --}}
<div id="app">
    <main class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-slate-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
        @yield('content')
    </main>
</div>
@endguest

{{-- ═══════════════════════════════════════════════════════════
     COMPILED JS
═══════════════════════════════════════════════════════════ --}}
<script src="{{ asset('js/app.js') }}"></script>

{{-- ── Offline-first PWA layer ─────────────────────────────────────────── --}}
@auth
<script src="{{ asset('js/offline-db.js') }}"></script>
<script src="{{ asset('js/offline-seed.js') }}"></script>
<script src="{{ asset('js/attendance-sync.js') }}"></script>
<script>
(function () {
    var banner      = document.getElementById('offline-banner');
    var toast       = document.getElementById('online-restored-toast');
    var toastTimer  = null;

    function showBanner()  { if (banner) banner.classList.remove('hidden'); }
    function hideBanner()  { if (banner) banner.classList.add('hidden'); }

    function showOnlineToast() {
        if (!toast) return;
        toast.classList.remove('hidden');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () { toast.classList.add('hidden'); }, 4000);
    }

    // Set initial state
    if (!navigator.onLine) { showBanner(); } else { hideBanner(); }

    window.addEventListener('offline', function () {
        showBanner();
    });

    window.addEventListener('online', function () {
        hideBanner();
        showOnlineToast();
        // attendance-sync.js also listens to 'online' and flushes automatically
    });

    // Listen for attendance-synced event to update pending count in the banner
    document.addEventListener('attendance-synced', function (e) {
        var count = e.detail?.count ?? 0;
        if (count > 0 && typeof showToast === 'function') {
            showToast(count + ' attendance record(s) synced successfully.', 'success');
        }
    });
}());
</script>
@endauth

@stack('scripts')

<script>
// ── Dashboard Background Theme Picker ──────────────────────────────────
(function () {
    var THEME_BG_KEY = 'erp_bg_theme';
    var mainEl = document.querySelector('main');

    var themes = {
        'none':   '',
        'theme1': '/imgs/theme1.webp',
        'theme2': '/imgs/theme2.webp',
        'theme3': '/imgs/theme3.webp',
        'theme4': '/imgs/theme4.webp',
        'theme5': '/imgs/theme5.webp',
        'theme6': '/imgs/theme6.webp',
        'theme7': '/imgs/theme7.webp',
        'theme8': '/imgs/theme8.webp',
    };

    function applyBgTheme(key) {
        if (!mainEl) return;
        var img = themes[key] || '';
        mainEl.style.backgroundImage = img ? "url('" + img + "')" : '';
        mainEl.style.backgroundSize = img ? 'cover' : '';
        mainEl.style.backgroundAttachment = img ? 'fixed' : '';
        mainEl.style.backgroundPosition = img ? 'center center' : '';
        mainEl.style.backgroundRepeat = img ? 'no-repeat' : '';
        // toggle glass-card class so cards pick up backdrop-filter only when a bg is set
        if (img) {
            mainEl.classList.add('has-bg-theme');
        } else {
            mainEl.classList.remove('has-bg-theme');
        }
        // update active ring on buttons
        document.querySelectorAll('.theme-opt').forEach(function (btn) {
            btn.style.borderColor = (btn.id === 'theme-opt-' + key) ? '#6366f1' : '#e5e7eb';
            btn.style.boxShadow   = (btn.id === 'theme-opt-' + key) ? '0 0 0 2px rgba(99,102,241,.35)' : '';
        });
    }

    // Apply saved theme on load
    var saved = localStorage.getItem(THEME_BG_KEY) || 'none';
    applyBgTheme(saved);

    window.setDashboardTheme = function (key) {
        localStorage.setItem(THEME_BG_KEY, key);
        applyBgTheme(key);
        // close panel
        var panel = document.getElementById('theme-picker-panel');
        if (panel) panel.classList.add('hidden');
    };

    window.toggleThemePicker = function () {
        var panel = document.getElementById('theme-picker-panel');
        if (!panel) return;
        panel.classList.toggle('hidden');
        // highlight current
        var cur = localStorage.getItem(THEME_BG_KEY) || 'none';
        applyBgTheme(cur);
    };

    // Close picker on outside click
    document.addEventListener('click', function (e) {
        var wrapper = document.getElementById('theme-picker-wrapper');
        var panel   = document.getElementById('theme-picker-panel');
        if (panel && wrapper && !wrapper.contains(e.target)) {
            panel.classList.add('hidden');
        }
    });
}());
</script>

{{-- Module 23: Service Worker registration --}}
@if(config('app.env') !== 'testing')
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js', { scope: '/' })
            .then(reg => {
                console.log('[PWA] Service worker registered:', reg.scope);

                // Check for SW updates every 30 minutes
                setInterval(() => reg.update(), 30 * 60 * 1000);

                reg.addEventListener('updatefound', () => {
                    const newWorker = reg.installing;
                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            // New version available — show a toast if showToast() is available
                            if (typeof showToast === 'function') {
                                showToast('App updated. Refresh for the latest version.', 'info');
                            }
                        }
                    });
                });
            })
            .catch(err => console.warn('[PWA] SW registration failed:', err));
    });
}
</script>
@endif

<script>
// Auto-scroll active sidebar link into view on page load so highlighted item is ALWAYS visible
document.addEventListener("DOMContentLoaded", function() {
    const sidebarScroll = document.querySelector(".sidebar-scroll");
    if (!sidebarScroll) return;

    const activeLink = sidebarScroll.querySelector(".sidebar-link.active, .active");
    if (activeLink) {
        setTimeout(function() {
            activeLink.scrollIntoView({ block: "center", behavior: "instant" });
        }, 50);
    }
});
</script>

@auth
@if(config('services.fcm.api_key'))
{{-- ═══════════════════════════════════════════════════════════════════════
     Firebase SDK — Cloud Messaging + App Check (reCAPTCHA Enterprise)
     Loaded only when FIREBASE_API_KEY is set in .env
═══════════════════════════════════════════════════════════════════════ --}}
<script type="module">
// ── 1. Firebase config (all values are PUBLIC — safe in HTML) ─────────────
const firebaseConfig = {
    apiKey:            "{{ config('services.fcm.api_key') }}",
    authDomain:        "{{ config('services.fcm.auth_domain') }}",
    projectId:         "{{ config('services.fcm.project_id') }}",
    storageBucket:     "{{ config('services.fcm.storage_bucket') }}",
    messagingSenderId: "{{ config('services.fcm.sender_id') }}",
    appId:             "{{ config('services.fcm.app_id') }}",
    measurementId:     "{{ config('services.fcm.measurement_id') }}",
};
const VAPID_KEY          = "{{ config('services.fcm.vapid_key') }}";
const APP_CHECK_SITE_KEY = "{{ config('services.recaptcha.app_check_site_key') }}";
const CSRF               = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

// ── 2. Import Firebase SDK modules ────────────────────────────────────────
import { initializeApp }              from 'https://www.gstatic.com/firebasejs/10.12.0/firebase-app.js';
import { getMessaging, getToken, onMessage }
                                      from 'https://www.gstatic.com/firebasejs/10.12.0/firebase-messaging.js';
import { initializeAppCheck, ReCaptchaEnterpriseProvider, getToken as getAppCheckToken }
                                      from 'https://www.gstatic.com/firebasejs/10.12.0/firebase-app-check.js';

// ── 3. Init Firebase app ──────────────────────────────────────────────────
const app = initializeApp(firebaseConfig);

// ── 4. Firebase App Check with reCAPTCHA Enterprise ──────────────────────
// App Check protects your Firebase resources from abuse.
// It attaches a short-lived attestation token to every Firebase request.
let appCheck = null;
if (APP_CHECK_SITE_KEY) {
    try {
        appCheck = initializeAppCheck(app, {
            provider: new ReCaptchaEnterpriseProvider(APP_CHECK_SITE_KEY),
            isTokenAutoRefreshEnabled: true,   // auto-refresh token silently
        });
        console.log('[AppCheck] Initialised with reCAPTCHA Enterprise.');
    } catch (err) {
        console.warn('[AppCheck] Init failed:', err.message);
    }
}

// ── 5. Init Firebase Messaging ────────────────────────────────────────────
const messaging = getMessaging(app);

// ── 6. Tell the service worker the Firebase config ────────────────────────
async function sendConfigToSW() {
    if (!('serviceWorker' in navigator)) return;
    const reg = await navigator.serviceWorker.ready;
    if (reg.active) {
        reg.active.postMessage({ type: 'FIREBASE_CONFIG', config: firebaseConfig });
    }
}

// ── 7. Request permission + get FCM token ─────────────────────────────────
async function initPush() {
    if (!('Notification' in window)) return;
    if (Notification.permission === 'denied') return;

    let permission = Notification.permission;
    if (permission === 'default') {
        permission = await Notification.requestPermission();
    }
    if (permission !== 'granted') return;

    await sendConfigToSW();

    try {
        const swReg = await navigator.serviceWorker.ready;
        const opts  = { serviceWorkerRegistration: swReg };
        if (VAPID_KEY) opts.vapidKey = VAPID_KEY;

        const token = await getToken(messaging, opts);

        if (token) {
            await fetch('/push/token', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body:    JSON.stringify({ token, platform: 'web', device_name: navigator.userAgent.substring(0, 200) }),
            });
            console.log('[FCM] Push token registered.');
        }
    } catch (err) {
        console.warn('[FCM] Could not get push token:', err.message);
    }
}

// ── 8. Foreground message handler ─────────────────────────────────────────
onMessage(messaging, (payload) => {
    const notif = payload.notification || {};
    const data  = payload.data         || {};
    const title = notif.title || data.title || 'New Notification';
    const body  = notif.body  || data.body  || '';
    const url   = data.click_action || data.url || null;

    if (typeof showToast === 'function') {
        showToast(`<strong>${title}</strong>${body ? '<br>' + body : ''}`, 'info', 6000);
    } else {
        if (Notification.permission === 'granted') {
            const n = new Notification(title, { body, icon: '/favicon_io/android-chrome-192x192.png' });
            if (url) n.addEventListener('click', () => { window.focus(); window.location.href = url; });
        }
    }
});

// ── 9. Expose App Check token getter globally (for AJAX calls) ────────────
// Usage: const { token } = await window.getAppCheckToken?.() ?? {};
window.getFirebaseAppCheckToken = async () => {
    if (!appCheck) return null;
    try {
        const result = await getAppCheckToken(appCheck);
        return result.token;
    } catch (e) {
        return null;
    }
};

// ── 10. Init on load ──────────────────────────────────────────────────────
window.addEventListener('load', () => {
    setTimeout(initPush, 2000);
});
</script>
@endif
@endauth

</body>
</html>
