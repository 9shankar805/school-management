<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', ($currentSchool ? $currentSchool->name . ' · ' : '') . config('app.name', 'School ERP'))</title>

    @php
        $pwaSchool = $currentSchool ?? (auth()->check() ? auth()->user()?->school : null);
        $pwaLogoUrl = ($pwaSchool && $pwaSchool->logo) 
            ? asset('storage/' . ltrim(str_replace('storage/', '', $pwaSchool->logo), '/')) 
            : ($pwaSchool ? $pwaSchool->logo_url : asset('imgs/logo_icon_192.webp'));
        $pwaAppName = $pwaSchool?->name ?? config('app.name', 'NepalSchoolHub ERP');
        $pwaThemeColor = $pwaSchool?->primary_color ?: '#6366f1';
        $pwaManifestUrl = route('pwa.manifest.dynamic') . ($pwaSchool ? '?school_id=' . $pwaSchool->id : '');
    @endphp

    {{-- Favicons & Dynamic School App Branding --}}
    <link rel="shortcut icon" href="{{ $pwaLogoUrl }}">
    <link rel="icon" sizes="512x512" href="{{ $pwaLogoUrl }}">
    <link rel="icon" sizes="192x192" href="{{ $pwaLogoUrl }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ $pwaLogoUrl }}">

    {{-- Module 23: Dynamic School PWA Manifest --}}
    <link rel="manifest" href="{{ $pwaManifestUrl }}">
    <meta name="theme-color" content="{{ $pwaThemeColor }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ $pwaAppName }}">

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
    <link href="{{ asset('css/dark-theme.css') }}?v={{ filemtime(public_path('css/dark-theme.css')) }}" rel="stylesheet">

    {{-- Loading indicator — must be first so ErpLoader is available to inline scripts --}}
    <script src="{{ asset('js/loader.js') }}?v={{ filemtime(public_path('js/loader.js')) }}" defer></script>

    <style>
        /* ── Topbar System ─────────────────────────────────── */
        .app-topbar {
            background-color: rgba(255, 255, 255, 0.92) !important;
            backdrop-filter: blur(14px) !important;
            -webkit-backdrop-filter: blur(14px) !important;
            border-bottom: 1px solid rgba(226, 232, 240, 0.8) !important;
        }
        [data-theme="dark"] .app-topbar,
        html.dark .app-topbar {
            background-color: rgba(15, 23, 42, 0.92) !important;
            border-bottom: 1px solid rgba(51, 65, 85, 0.6) !important;
        }
        .app-search-box {
            background-color: rgba(248, 250, 252, 0.9) !important;
            border: 1px solid rgba(226, 232, 240, 0.9) !important;
            color: #64748b !important;
            transition: all 0.2s ease-in-out !important;
        }
        .app-search-box:hover {
            border-color: #cbd5e1 !important;
            background-color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04) !important;
        }
        [data-theme="dark"] .app-search-box,
        html.dark .app-search-box {
            background-color: rgba(30, 41, 59, 0.8) !important;
            border-color: rgba(51, 65, 85, 0.8) !important;
            color: #94a3b8 !important;
        }
        [data-theme="dark"] .app-search-box:hover,
        html.dark .app-search-box:hover {
            background-color: rgba(30, 41, 59, 1) !important;
            border-color: rgba(71, 85, 105, 1) !important;
        }
        .app-search-box kbd {
            background-color: rgba(255, 255, 255, 0.9) !important;
            border: 1px solid rgba(226, 232, 240, 0.9) !important;
            color: #64748b !important;
        }
        [data-theme="dark"] .app-search-box kbd,
        html.dark .app-search-box kbd {
            background-color: rgba(15, 23, 42, 0.6) !important;
            border-color: rgba(51, 65, 85, 0.8) !important;
            color: #94a3b8 !important;
        }

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
            color: #ffffff !important;
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

    /* ═══════════════════════════════════════════════════════════════
       MOBILE BOTTOM NAVIGATION BAR
    ═══════════════════════════════════════════════════════════════ */
    #mobile-bottom-nav {
        display: flex !important;
        position: fixed !important;
        bottom: 0 !important;
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
        max-width: 100vw !important;
        box-sizing: border-box !important;
        z-index: 40 !important;
        background-color: rgba(255, 255, 255, 0.96) !important;
        backdrop-filter: blur(12px) !important;
        -webkit-backdrop-filter: blur(12px) !important;
        border-top: 1px solid rgba(226, 232, 240, 0.85) !important;
        box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.06) !important;
        padding: 0.35rem 0.25rem !important;
        padding-bottom: max(0.35rem, env(safe-area-inset-bottom)) !important;
        justify-content: space-around !important;
        align-items: center !important;
        margin: 0 !important;
        /* Hide/show on scroll */
        transform: translateY(0) !important;
        transition: transform 0.28s cubic-bezier(0.4, 0, 0.2, 1) !important;
        will-change: transform !important;
    }

    #mobile-bottom-nav.nav-hidden {
        transform: translateY(110%) !important;
    }

    [data-theme="dark"] #mobile-bottom-nav,
    html.dark #mobile-bottom-nav {
        background-color: rgba(15, 23, 42, 0.96) !important;
        border-top-color: rgba(51, 65, 85, 0.85) !important;
        box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.35) !important;
    }

    /* Completely hide bottom navbar on desktop screens (>= 1024px) where sidebar is visible */
    @media (min-width: 1024px) {
        #mobile-bottom-nav {
            display: none !important;
        }
        #fab-group {
            bottom: 1.5rem !important;
        }
        main {
            padding-bottom: 1.5rem !important;
        }
    }

    @media (max-width: 1023px) {
        #fab-group {
            bottom: 4.75rem !important;
        }
        main {
            padding-bottom: 5rem !important;
        }
    }

    .mobile-nav-item {
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        flex: 1 1 0% !important;
        min-width: 0 !important;
        padding: 0.35rem 0.25rem !important;
        border-radius: 0.75rem !important;
        text-align: center !important;
        text-decoration: none !important;
        background: transparent !important;
        border: none !important;
        cursor: pointer !important;
        color: #64748b !important;
        transition: all 0.15s ease-in-out !important;
        -webkit-tap-highlight-color: transparent !important;
    }

    .mobile-nav-item:hover,
    .mobile-nav-item:focus {
        color: #1e293b !important;
        text-decoration: none !important;
    }

    [data-theme="dark"] .mobile-nav-item,
    html.dark .mobile-nav-item {
        color: #94a3b8 !important;
    }

    [data-theme="dark"] .mobile-nav-item:hover,
    [data-theme="dark"] .mobile-nav-item:focus,
    html.dark .mobile-nav-item:hover,
    html.dark .mobile-nav-item:focus {
        color: #f1f5f9 !important;
    }

    .mobile-nav-item.active {
        color: #4f46e5 !important;
        font-weight: 600 !important;
    }

    [data-theme="dark"] .mobile-nav-item.active,
    html.dark .mobile-nav-item.active {
        color: #818cf8 !important;
    }

    .mobile-nav-item i {
        font-size: 1.2rem !important;
        line-height: 1 !important;
        margin-bottom: 0.2rem !important;
    }

    .mobile-nav-item span {
        font-size: 0.65rem !important;
        line-height: 1 !important;
        letter-spacing: -0.01em !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        max-width: 100% !important;
        display: block !important;
    }

    /* ── Bottom Nav Customizer Modal (Light & Dark Theme Polish) ── */
    #bottom-nav-customizer-modal {
        position: fixed !important;
        inset: 0 !important;
        z-index: 999999 !important;
        background: rgba(15, 23, 42, 0.65) !important;
        backdrop-filter: blur(8px) !important;
        -webkit-backdrop-filter: blur(8px) !important;
        padding: 0 !important;
        box-sizing: border-box !important;
    }

    @media (min-width: 640px) {
        #bottom-nav-customizer-modal {
            padding: 1.5rem !important;
        }
    }

    .customizer-card {
        background: #ffffff !important;
        color: #0f172a !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
        height: 80vh !important;
        height: 80dvh !important;
        max-height: 85vh !important;
        max-height: 85dvh !important;
        display: flex !important;
        flex-direction: column !important;
        width: 100% !important;
        margin: 0 !important;
    }

    @media (min-width: 640px) {
        .customizer-card {
            height: auto !important;
            max-height: 85vh !important;
            max-height: 85dvh !important;
            max-width: 36rem !important;
            margin: 0 auto !important;
        }
    }

    [data-theme="dark"] .customizer-card,
    html.dark .customizer-card {
        background: #0f172a !important;
        color: #f8fafc !important;
        border: 1px solid #1e293b !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7) !important;
    }

    .customizer-header {
        background: #f8fafc !important;
        border-bottom: 1px solid #e2e8f0 !important;
        flex-shrink: 0 !important;
    }

    [data-theme="dark"] .customizer-header,
    html.dark .customizer-header {
        background: #1e293b !important;
        border-bottom: 1px solid #334155 !important;
    }

    .customizer-preview-container {
        flex-shrink: 0 !important;
        background: #f8fafc !important;
        border-bottom: 1px solid #e2e8f0 !important;
    }

    [data-theme="dark"] .customizer-preview-container,
    html.dark .customizer-preview-container {
        background: #0f172a !important;
        border-bottom: 1px solid #1e293b !important;
    }

    .customizer-footer {
        background: #f8fafc !important;
        border-top: 1px solid #e2e8f0 !important;
        flex-shrink: 0 !important;
    }

    [data-theme="dark"] .customizer-footer,
    html.dark .customizer-footer {
        background: #0b1120 !important;
        border-top: 1px solid #1e293b !important;
    }

    /* Live Preview Bar & Scrollable Body */
    .customizer-preview-bar {
        border-radius: 1rem !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
    }

    /* ── Global High-Quality Print Stylesheet ── */
    @media print {
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }
        @page {
            size: landscape;
            margin: 8mm;
        }
        body, html, main, .app-layout, .flex-1, .content-wrapper, .min-h-screen {
            background: #FFFFFF !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            min-height: auto !important;
            overflow: visible !important;
        }
        .app-topbar,
        header,
        #left-sidebar,
        aside,
        .left-sidebar,
        #sidebar-overlay,
        #mobile-bottom-nav,
        .mobile-bottom-nav,
        #mobile-bottom-sheet,
        #fab-group,
        #fab-main,
        #fab-actions,
        #bottom-nav-customizer-modal,
        #erp-loader,
        #quick-search-modal,
        #command-palette-modal,
        .print-hide,
        .print\:hidden {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            opacity: 0 !important;
        }
    }
        background: #ffffff;
        border: 1.5px dashed #cbd5e1;
    }
    [data-theme="dark"] .customizer-preview-bar,
    html.dark .customizer-preview-bar {
        background: #1e293b;
        border: 1.5px dashed #334155;
    }

    #customizer-modal-body {
        flex: 1 1 0% !important;
        min-height: 0 !important;
        overflow-y: auto !important;
        -webkit-overflow-scrolling: touch !important;
        overscroll-behavior: contain !important;
        scrollbar-width: thin;
        scrollbar-color: rgba(148, 163, 184, 0.4) transparent;
    }
    #customizer-modal-body::-webkit-scrollbar {
        width: 4px;
    }
    #customizer-modal-body::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, 0.4);
        border-radius: 9999px;
    }

    /* Preset Chips */
    .role-preset-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.4rem 0.75rem;
        border-radius: 0.625rem;
        font-size: 0.75rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s ease-in-out;
        border: 1px solid transparent;
        user-select: none;
    }
    .role-preset-chip:hover {
        transform: translateY(-1px);
    }

    .chip-coordinator {
        background: #eef2ff;
        color: #4338ca;
        border-color: #c7d2fe;
    }
    [data-theme="dark"] .chip-coordinator,
    html.dark .chip-coordinator {
        background: rgba(49, 46, 129, 0.45);
        color: #a5b4fc;
        border-color: #3730a3;
    }

    .chip-teacher {
        background: #ecfdf5;
        color: #047857;
        border-color: #a7f3d0;
    }
    [data-theme="dark"] .chip-teacher,
    html.dark .chip-teacher {
        background: rgba(6, 78, 59, 0.45);
        color: #6ee7b7;
        border-color: #065f46;
    }

    .chip-admin {
        background: #f5f3ff;
        color: #6d28d9;
        border-color: #ddd6fe;
    }
    [data-theme="dark"] .chip-admin,
    html.dark .chip-admin {
        background: rgba(91, 33, 182, 0.45);
        color: #c4b5fd;
        border-color: #5b21b6;
    }

    .chip-accountant {
        background: #fffbeb;
        color: #b45309;
        border-color: #fde68a;
    }
    [data-theme="dark"] .chip-accountant,
    html.dark .chip-accountant {
        background: rgba(120, 53, 15, 0.45);
        color: #fcd34d;
        border-color: #92400e;
    }

    /* Shortcut Grid Tiles */
    .customizer-tile {
        position: relative;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.55rem 0.65rem;
        border-radius: 0.75rem;
        cursor: pointer;
        transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1.5px solid #e2e8f0;
        background: #ffffff;
        color: #1e293b;
        user-select: none;
        min-width: 0;
    }
    .customizer-tile:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
        transform: translateY(-1px);
    }
    [data-theme="dark"] .customizer-tile,
    html.dark .customizer-tile {
        border-color: #334155;
        background: #1e293b;
        color: #f1f5f9;
    }
    [data-theme="dark"] .customizer-tile:hover,
    html.dark .customizer-tile:hover {
        border-color: #475569;
        background: #243247;
    }

    /* Selected Tile */
    .customizer-tile.is-selected {
        background: linear-gradient(135deg, #eef2ff 0%, #f5f3ff 100%) !important;
        border-color: #6366f1 !important;
        color: #1e1b4b !important;
        box-shadow: 0 4px 12px -2px rgba(99, 102, 241, 0.2) !important;
    }
    [data-theme="dark"] .customizer-tile.is-selected,
    html.dark .customizer-tile.is-selected {
        background: linear-gradient(135deg, rgba(49, 46, 129, 0.55) 0%, rgba(76, 29, 149, 0.4) 100%) !important;
        border-color: #818cf8 !important;
        color: #ffffff !important;
        box-shadow: 0 4px 14px -2px rgba(99, 102, 241, 0.35) !important;
    }

    .customizer-tile .tile-icon {
        width: 1.85rem;
        height: 1.85rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 0.9rem;
        background: #f1f5f9;
        color: #64748b;
        transition: all 0.15s ease;
    }
    [data-theme="dark"] .customizer-tile .tile-icon,
    html.dark .customizer-tile .tile-icon {
        background: #334155;
        color: #cbd5e1;
    }

    .customizer-tile.is-selected .tile-icon {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) !important;
        color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(79, 70, 229, 0.4) !important;
    }

    .customizer-tile .tile-label {
        font-size: 0.8125rem;
        font-weight: 600;
        color: #334155;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        line-height: 1.2;
    }
    [data-theme="dark"] .customizer-tile .tile-label,
    html.dark .customizer-tile .tile-label {
        color: #e2e8f0;
    }
    .customizer-tile.is-selected .tile-label {
        color: #1e1b4b !important;
        font-weight: 700;
    }
    [data-theme="dark"] .customizer-tile.is-selected .tile-label,
    html.dark .customizer-tile.is-selected .tile-label {
        color: #ffffff !important;
        font-weight: 700;
    }

    .customizer-tile .tile-checkbox {
        width: 1.15rem;
        height: 1.15rem;
        border-radius: 9999px;
        border: 1.5px solid #cbd5e1;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: all 0.15s ease;
        margin-left: auto;
    }
    [data-theme="dark"] .customizer-tile .tile-checkbox,
    html.dark .customizer-tile .tile-checkbox {
        border-color: #475569;
    }
    /* ── Command Palette & Quick Search Modal ── */
    #cmd-palette-backdrop {
        position: fixed !important;
        inset: 0 !important;
        z-index: 999999 !important;
        background: rgba(15, 23, 42, 0.65) !important;
        backdrop-filter: blur(8px) !important;
        -webkit-backdrop-filter: blur(8px) !important;
        display: none;
        align-items: flex-start !important;
        justify-content: center !important;
        padding-top: 10vh !important;
        padding-left: 1rem !important;
        padding-right: 1rem !important;
    }
    #cmd-palette-backdrop.is-open {
        display: flex !important;
    }
    .cmd-modal-box {
        width: 100% !important;
        max-width: 36rem !important;
        background: #ffffff !important;
        color: #0f172a !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 1rem !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35) !important;
        overflow: hidden !important;
        display: flex !important;
        flex-direction: column !important;
        animation: cmdPopIn 0.15s cubic-bezier(0.16, 1, 0.3, 1) !important;
    }
    [data-theme="dark"] .cmd-modal-box,
    html.dark .cmd-modal-box {
        background: #0f172a !important;
        color: #f8fafc !important;
        border-color: #334155 !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.8) !important;
    }
    @keyframes cmdPopIn {
        from { opacity: 0; transform: scale(0.96) translateY(-8px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }
    .cmd-header {
        display: flex !important;
        align-items: center !important;
        gap: 0.75rem !important;
        padding: 0.85rem 1.1rem !important;
        border-bottom: 1px solid #e2e8f0 !important;
        background: #ffffff !important;
    }
    [data-theme="dark"] .cmd-header,
    html.dark .cmd-header {
        background: #0f172a !important;
        border-bottom-color: #1e293b !important;
    }
    #cmd-input {
        flex: 1 1 0% !important;
        background: transparent !important;
        border: none !important;
        outline: none !important;
        font-size: 0.95rem !important;
        color: #0f172a !important;
        font-weight: 500 !important;
    }
    [data-theme="dark"] #cmd-input,
    html.dark #cmd-input {
        color: #f8fafc !important;
    }
    #cmd-input::placeholder {
        color: #94a3b8 !important;
    }
    #cmd-results {
        max-height: 22rem !important;
        overflow-y: auto !important;
        padding: 0.5rem !important;
        scrollbar-width: thin;
    }
    .cmd-group-label {
        font-size: 0.7rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        padding: 0.4rem 0.65rem !important;
        margin-top: 0.35rem !important;
        color: #64748b !important;
        border-radius: 0.375rem !important;
    }
    [data-theme="dark"] .cmd-group-label,
    html.dark .cmd-group-label {
        color: #94a3b8 !important;
    }
    .cmd-result-item {
        display: flex !important;
        align-items: center !important;
        gap: 0.75rem !important;
        padding: 0.55rem 0.75rem !important;
        border-radius: 0.625rem !important;
        font-size: 0.875rem !important;
        color: #1e293b !important;
        text-decoration: none !important;
        cursor: pointer !important;
        transition: all 0.1s ease-in-out !important;
        border: 1px solid transparent !important;
        background: transparent !important;
        width: 100% !important;
        text-align: left !important;
    }
    [data-theme="dark"] .cmd-result-item,
    html.dark .cmd-result-item {
        color: #f1f5f9 !important;
    }
    .cmd-result-item:hover,
    .cmd-result-item.is-selected {
        background: #f1f5f9 !important;
        color: #0f172a !important;
        text-decoration: none !important;
    }
    [data-theme="dark"] .cmd-result-item:hover,
    [data-theme="dark"] .cmd-result-item.is-selected,
    html.dark .cmd-result-item:hover,
    html.dark .cmd-result-item.is-selected {
        background: #1e293b !important;
        color: #ffffff !important;
    }
    .cmd-result-item.is-selected {
        border-color: #6366f1 !important;
    }
    .cmd-icon-box {
        width: 2rem !important;
        height: 2rem !important;
        border-radius: 0.5rem !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
        font-size: 0.95rem !important;
        background: #eef2ff !important;
        color: #4f46e5 !important;
    }
    [data-theme="dark"] .cmd-icon-box,
    html.dark .cmd-icon-box {
        background: rgba(99, 102, 241, 0.2) !important;
        color: #818cf8 !important;
    }
    .cmd-result-text {
        flex: 1 1 0% !important;
        min-width: 0 !important;
    }
    .cmd-result-title {
        font-weight: 600 !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        line-height: 1.25 !important;
    }
    .cmd-result-subtitle {
        font-size: 0.725rem !important;
        color: #64748b !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        line-height: 1.2 !important;
    }
    [data-theme="dark"] .cmd-result-subtitle,
    html.dark .cmd-result-subtitle {
        color: #94a3b8 !important;
    }
    .cmd-badge {
        font-size: 0.6875rem !important;
        font-weight: 600 !important;
        padding: 0.15rem 0.45rem !important;
        border-radius: 0.375rem !important;
        background: #f1f5f9 !important;
        color: #64748b !important;
        border: 1px solid #e2e8f0 !important;
        margin-left: auto !important;
        flex-shrink: 0 !important;
    }
    [data-theme="dark"] .cmd-badge,
    html.dark .cmd-badge {
        background: #1e293b !important;
        color: #94a3b8 !important;
        border-color: #334155 !important;
    }
    .cmd-footer {
        padding: 0.5rem 1rem !important;
        border-top: 1px solid #e2e8f0 !important;
        display: flex !important;
        align-items: center !important;
        gap: 1rem !important;
        font-size: 0.7rem !important;
        color: #64748b !important;
        background: #f8fafc !important;
    }
    [data-theme="dark"] .cmd-footer,
    html.dark .cmd-footer {
        background: #0b1120 !important;
        border-top-color: #1e293b !important;
        color: #94a3b8 !important;
    }
    .cmd-item-actions {
        display: flex !important;
        align-items: center !important;
        gap: 0.35rem !important;
        margin-left: auto !important;
        flex-shrink: 0 !important;
    }
    .cmd-pin-btn, .cmd-delete-btn {
        background: transparent !important;
        border: none !important;
        padding: 0.2rem 0.4rem !important;
        border-radius: 0.375rem !important;
        cursor: pointer !important;
        color: #94a3b8 !important;
        font-size: 0.85rem !important;
        transition: all 0.15s ease !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    .cmd-pin-btn:hover {
        color: #f59e0b !important;
        background: rgba(245, 158, 11, 0.15) !important;
    }
    .cmd-pin-btn.is-pinned {
        color: #f59e0b !important;
    }
    .cmd-delete-btn:hover {
        color: #ef4444 !important;
        background: rgba(239, 68, 68, 0.15) !important;
    }
    .shortcut-icon-opt {
        transition: all 0.15s ease !important;
        cursor: pointer !important;
    }
    .shortcut-icon-opt.active {
        border-color: #6366f1 !important;
        background: rgba(99, 102, 241, 0.15) !important;
        color: #6366f1 !important;
    }
    #create-shortcut-modal,
    #shortcut-modal {
        z-index: 1000005 !important;
        position: fixed !important;
        inset: 0 !important;
    }
    </style>

    @stack('head-scripts')

    {{-- Inline critical theme vars so there's no flash and toggleTheme is globally available immediately --}}
    <script>
        (function(){
            window.applyTheme = function(t) {
                var theme = (t === 'dark') ? 'dark' : 'light';
                document.documentElement.setAttribute('data-theme', theme);
                if (theme === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
                try {
                    localStorage.setItem('erp_theme', theme);
                } catch(e) {}
                try {
                    window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme: theme } }));
                } catch(e) {}
            };

            window.toggleTheme = function() {
                var isDark = document.documentElement.classList.contains('dark') ||
                             document.documentElement.getAttribute('data-theme') === 'dark';
                var next = isDark ? 'light' : 'dark';
                window.applyTheme(next);
            };

            var savedTheme = null;
            try {
                savedTheme = localStorage.getItem('erp_theme');
            } catch(e) {}
            if (!savedTheme) {
                savedTheme = (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) ? 'dark' : 'light';
            }
            window.applyTheme(savedTheme);
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
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 flex-1 min-w-0 group" title="View & Edit Profile">
                    @if(auth()->user()->isSuperAdmin())
                    <div class="w-8 h-8 rounded-full bg-blue-600 text-white text-xs font-black flex items-center justify-center flex-shrink-0 shadow-xs group-hover:ring-2 group-hover:ring-indigo-400 transition">SA</div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-white truncate group-hover:text-indigo-300 transition">Super Admin</p>
                        <p class="text-[10px] truncate" style="color:rgba(255,255,255,.4)">{{ auth()->user()->email ?? 'superadmin@nshub.com' }}</p>
                    </div>
                    @else
                    <img src="{{ auth()->user()->avatar }}" class="w-8 h-8 rounded-full object-cover flex-shrink-0 ring-2 ring-indigo-500/30 group-hover:ring-indigo-400 transition" alt="">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-white truncate group-hover:text-indigo-300 transition">{{ auth()->user()->full_name }}</p>
                        <p class="text-[10px] capitalize truncate" style="color:rgba(255,255,255,.4)">{{ str_replace('-', ' ', auth()->user()->primary_role) }}</p>
                    </div>
                    @endif
                </a>
                <a href="{{ route('profile.edit') }}" class="p-1.5 rounded-md transition" style="color:rgba(255,255,255,.35)" title="My Profile"
                   onmouseover="this.style.color='#818cf8'" onmouseout="this.style.color='rgba(255,255,255,.35)'">
                    <i class="bi bi-person-gear text-sm"></i>
                </a>
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

        {{-- Global Offline Glassmorphic Floating Banner --}}
        <div id="global-offline-banner" class="hidden fixed top-3 left-1/2 -translate-x-1/2 z-50 px-4 py-2 rounded-2xl shadow-xl flex items-center gap-3 backdrop-blur-md transition-all duration-300"
             style="background:rgba(15,23,42,0.95);border:1px solid rgba(255,255,255,0.15);color:#fff;font-size:0.8rem;">
            <div class="flex items-center gap-2">
                <span id="global-offline-dot" class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-ping"></span>
                <span id="global-offline-msg" class="font-medium">तपाईं अहिले अफलाइन हुनुहुन्छ (Offline Mode). सबै कामहरू स्थानीय रूपमा सुरक्षित छन्।</span>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="if(typeof openSyncPanel==='function') openSyncPanel();" class="px-2.5 py-1 bg-white/20 hover:bg-white/30 rounded-lg text-xs font-semibold transition">
                    <i class="bi bi-arrow-repeat"></i> <span id="global-offline-queue-text">Queue</span>
                </button>
                <button onclick="document.getElementById('global-offline-banner').classList.add('hidden')" class="text-white/60 hover:text-white text-xs">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>

        {{-- ─── TOP BAR (Constant across screen sizes) ────────────── --}}
        <header class="app-topbar flex-shrink-0 h-14 flex items-center px-4 lg:px-5 gap-3 z-30 sticky top-0 w-full transition-colors"
                style="box-shadow:0 1px 4px rgba(0,0,0,.06)">

            {{-- Mobile hamburger (hidden on large screens) --}}
            <button onclick="openSidebar()"
                    class="lg:hidden"
                    style="background: #f1f5f9 !important; color: #0f172a !important; border: 1px solid #cbd5e1 !important; border-radius: 0.5rem !important; padding: 0.35rem 0.6rem !important; display: flex !important; align-items: center !important; justify-content: center !important; cursor: pointer !important;"
                    aria-label="Open sidebar">
                <i class="bi bi-list" style="font-size: 1.25rem !important; color: #0f172a !important;"></i>
            </button>

            {{-- Mobile Quick Search button --}}
            <button onclick="openCommandPalette()"
                    class="md:hidden w-8 h-8 rounded-lg flex items-center justify-center transition text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800"
                    aria-label="Quick Search">
                <i class="bi bi-search text-sm"></i>
            </button>

            {{-- Search bar --}}
            <button onclick="openCommandPalette()"
                    class="app-search-box flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs cursor-pointer transition hidden md:flex"
                    style="min-width:220px">
                <i class="bi bi-search text-xs"></i>
                <span class="flex-1 text-left truncate">Search schools, users, invoices...</span>
                <kbd class="text-[9px] font-mono px-1 py-0.5 rounded">⌘K</kbd>
            </button>

            {{-- Offline Network Status & Sync Pill --}}
            <div id="offline-header-pill" class="hidden items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold cursor-pointer transition"
                 onclick="if(typeof openSyncPanel==='function') openSyncPanel();"
                 style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c" title="Click to view offline queue">
                <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                <span id="offline-header-text">अफलाइन (Offline)</span>
                <span id="offline-pending-wrapper" class="hidden ml-1 px-1.5 py-0.2 bg-red-100 text-red-800 rounded-full text-[10px]">
                    <span id="offline-pending-count">0</span>
                </span>
            </div>

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


                {{-- 1-Click Dark / Light Mode Toggle --}}
                <button onclick="toggleTheme()" id="dark-mode-toggle-btn"
                        class="w-9 h-9 rounded-xl flex items-center justify-center transition cursor-pointer"
                        style="color:#6b7280" onmouseover="this.style.background='rgba(99,102,241,0.1)'" onmouseout="this.style.background='transparent'"
                        title="Toggle Dark / Light Mode (⌘D)" aria-label="Toggle Dark / Light Mode">
                    <i class="bi bi-moon-stars-fill dark:hidden text-sm"></i>
                    <i class="bi bi-sun-fill hidden dark:inline text-base text-amber-400"></i>
                </button>

                {{-- Calendar (hidden on mobile) --}}
                <a href="{{ route('events.show') }}"
                   class="w-9 h-9 rounded-xl hidden sm:flex items-center justify-center transition"
                   style="color:#6b7280" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'"
                   title="Calendar">
                    <i class="bi bi-calendar3 text-sm"></i>
                </a>

                {{-- Messages (hidden on mobile) --}}
                @php
                    $unreadChatCount = 0;
                    $chatLink = route('communication.index');
                    try {
                        if (auth()->user()->isSuperAdmin()) {
                            $unreadChatCount = \App\Models\ContactMessage::where('status', 'unread')->count();
                            $chatLink = route('super.contact-messages.index');
                        } elseif (auth()->user()->isTeacher()) {
                            $unreadChatCount = \App\Models\Conversation::where('teacher_id', auth()->id())
                                ->withCount(['messages as um' => fn($q) => $q->where('sender_id', '!=', auth()->id())->whereNull('read_at')])
                                ->get()->sum('um');
                            $chatLink = route('teacher.messages');
                        } elseif (auth()->user()->isParent()) {
                            $unreadChatCount = \App\Models\Conversation::where('parent_id', auth()->id())
                                ->withCount(['messages as um' => fn($q) => $q->where('sender_id', '!=', auth()->id())->whereNull('read_at')])
                                ->get()->sum('um');
                            $chatLink = route('parent.conversations');
                        }
                    } catch(\Throwable $e) {
                        $unreadChatCount = 0;
                    }
                @endphp
                <div class="relative hidden sm:block">
                    <a href="{{ $chatLink }}"
                       class="w-9 h-9 rounded-xl flex items-center justify-center transition"
                       style="color:#6b7280" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'"
                       title="Messages">
                        <i class="bi bi-chat-left-dots text-sm"></i>
                    </a>
                    @if($unreadChatCount > 0)
                    <span class="absolute -top-0.5 -right-0.5 min-w-[16px] h-4 px-1 bg-rose-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center leading-none pointer-events-none">
                        {{ $unreadChatCount > 9 ? '9+' : $unreadChatCount }}
                    </span>
                    @endif
                </div>

                {{-- Theme picker (hidden on mobile) --}}
                <div class="relative hidden sm:block" id="theme-picker-wrapper">
                    <button onclick="toggleThemePicker()"
                            class="w-9 h-9 rounded-xl flex items-center justify-center transition"
                            style="color:#6b7280" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'"
                            title="Choose background theme" id="theme-picker-btn">
                        <i class="bi bi-image text-sm"></i>
                    </button>

                    {{-- Dropdown --}}
                    <div id="theme-picker-panel"
                         class="hidden absolute right-0 mt-2 rounded-2xl shadow-floating border overflow-hidden z-50 p-3 bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800"
                         style="width:260px;top:100%">
                        <p class="text-xs font-semibold mb-2.5 px-0.5 text-slate-500 dark:text-slate-400">Background Theme</p>
                        <div class="grid grid-cols-4 gap-2">
                            {{-- No background option --}}
                            <button onclick="setDashboardTheme('none')"
                                    id="theme-opt-none"
                                    class="theme-opt rounded-lg overflow-hidden border-2 flex items-center justify-center transition border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800"
                                    style="height:44px"
                                    title="No background">
                                <i class="bi bi-slash-circle text-slate-400 text-sm"></i>
                            </button>
                            @for($i = 1; $i <= 8; $i++)
                            <button onclick="setDashboardTheme('theme{{ $i }}')"
                                    id="theme-opt-theme{{ $i }}"
                                    class="theme-opt rounded-lg overflow-hidden border-2 transition p-0 border-slate-200 dark:border-slate-700"
                                    style="height:44px"
                                    title="Theme {{ $i }}">
                                <img src="/imgs/theme{{ $i }}.png" alt="Theme {{ $i }}"
                                     class="w-full h-full object-cover">
                            </button>
                            @endfor
                        </div>
                        <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800">
                            <button onclick="toggleTheme()"
                                    class="w-full flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs transition hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300">
                                <i class="bi bi-moon-fill dark:hidden text-sm"></i>
                                <i class="bi bi-sun-fill hidden dark:inline text-sm text-amber-400"></i>
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
                        @if($unreadCount > 0)
                        <span id="notif-badge" class="absolute -top-0.5 -right-0.5 min-w-[16px] h-4 px-1 bg-rose-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center leading-none pointer-events-none">
                            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                        </span>
                        @endif
                    </button>
                    {{-- Notification panel --}}
                    <div id="notif-panel"
                         class="absolute right-0 top-full mt-2 w-80 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-floating z-50 hidden overflow-hidden">
                        <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                            <p class="font-semibold text-sm text-slate-700 dark:text-slate-200">Notifications</p>
                            @if($unreadCount > 0)
                            <form action="{{ route('notifications.read-all') }}" method="POST" class="inline">@csrf
                                <button type="submit" class="text-xs text-blue-600 hover:underline">Mark all read</button>
                            </form>
                            @endif
                        </div>
                        <div class="max-h-72 overflow-y-auto">
                            @forelse(auth()->user()->notifications()->latest()->take(8)->get() as $notif)
                            @php
                                $actionUrl = $notif->data['action_url'] ?? ($notif->data['link'] ?? ($notif->data['url'] ?? null));
                            @endphp
                            <div class="px-4 py-3 flex items-start gap-3 hover:bg-slate-50 dark:hover:bg-slate-800 transition {{ is_null($notif->read_at) ? 'bg-blue-50/50 dark:bg-indigo-950/30' : '' }}">
                                <span class="mt-0.5 w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 text-xs {{ is_null($notif->read_at) ? 'bg-blue-100 dark:bg-indigo-900 text-blue-600 dark:text-indigo-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-400' }}">
                                    <i class="bi bi-bell"></i>
                                </span>
                                <div class="flex-1 min-w-0">
                                    @if($actionUrl)
                                    <a href="{{ $actionUrl }}" class="text-xs text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-indigo-400 leading-relaxed line-clamp-2 font-medium">
                                        {{ $notif->data['message'] ?? ($notif->data['title'] ?? 'Notification') }}
                                    </a>
                                    @else
                                    <p class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed line-clamp-2">{{ $notif->data['message'] ?? ($notif->data['title'] ?? 'Notification') }}</p>
                                    @endif
                                    <p class="text-[10px] text-slate-400 mt-0.5">{{ $notif->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            @empty
                            <div class="px-4 py-8 text-center">
                                <i class="bi bi-bell-slash text-2xl text-slate-300 dark:text-slate-600 block mb-1"></i>
                                <p class="text-xs text-slate-400">No notifications yet</p>
                            </div>
                            @endforelse
                        </div>
                        <div class="px-4 py-2.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50">
                            <a href="{{ route('notifications.index') }}" class="text-xs text-blue-600 dark:text-indigo-400 hover:underline">View all →</a>
                        </div>
                    </div>
                </div>

                {{-- User avatar dropdown --}}
                <div class="relative ml-1" id="user-menu-wrapper">
                    <button onclick="toggleUserMenu()"
                            class="flex items-center gap-2 pl-2 pr-3 py-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        @if(auth()->user()->isSuperAdmin())
                        <div class="w-7 h-7 rounded-full bg-blue-600 text-white text-[10px] font-black flex items-center justify-center flex-shrink-0">SA</div>
                        @else
                        <img src="{{ auth()->user()->avatar }}" class="w-7 h-7 rounded-full object-cover flex-shrink-0 border border-slate-200 dark:border-slate-700" alt="">
                        @endif
                        <span class="hidden sm:block text-xs font-semibold text-slate-700 dark:text-slate-200 max-w-[90px] truncate">
                            @if(auth()->user()->isSuperAdmin())Super Admin@else{{ auth()->user()->first_name }}@endif
                        </span>
                        <i class="bi bi-chevron-down text-[9px] text-slate-400"></i>
                    </button>
                    <div id="user-menu-panel"
                         class="absolute right-0 top-full mt-2 w-44 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-floating z-50 hidden py-1 overflow-hidden">
                        <div class="px-3 py-2 border-b border-slate-100 dark:border-slate-800">
                            <p class="text-xs font-semibold text-slate-700 dark:text-slate-200 truncate">{{ auth()->user()->full_name }}</p>
                            <p class="text-[10px] text-slate-400 truncate">{{ auth()->user()->email }}</p>
                        </div>
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-3 py-2 text-xs text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition"><i class="bi bi-person-gear w-4 text-indigo-500"></i> My Profile</a>
                        <a href="{{ route('password.change') }}" class="flex items-center gap-2 px-3 py-2 text-xs text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition"><i class="bi bi-key w-4"></i> Change Password</a>
                        <a href="{{ route('two-factor.setup') }}" class="flex items-center gap-2 px-3 py-2 text-xs text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition"><i class="bi bi-shield-lock w-4"></i> Security (2FA)</a>
                        <button type="button" onclick="promptPwaInstall()" class="w-full text-left flex items-center gap-2 px-3 py-2 text-xs text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 transition font-medium">
                            <i class="bi bi-phone w-4"></i> Install App
                        </button>
                        <div class="border-t border-slate-100 dark:border-slate-800 mt-1 pt-1">
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
        <main class="flex-1 overflow-y-auto overflow-x-hidden relative pb-20 sm:pb-6">

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

            <div>
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

@auth
{{-- ═══════════════════════════════════════════════════════════
     MOBILE BOTTOM NAVIGATION BAR (Screens < 1024px)
     Customizable by any officer / user with live persistence
═══════════════════════════════════════════════════════════ --}}
<nav id="mobile-bottom-nav" class="mobile-bottom-nav" aria-label="Mobile Navigation">
    {{-- Initial default server-side items (dynamically replaced by JS if custom saved) --}}
    <a href="{{ url('home') }}"
       class="mobile-nav-item {{ request()->is('home*') ? 'active' : '' }}">
        <i class="bi {{ request()->is('home*') ? 'bi-grid-fill' : 'bi-grid' }}"></i>
        <span>Home</span>
    </a>

    @if(auth()->user()?->isAcademicCoord())
    <a href="{{ url('classes') }}"
       class="mobile-nav-item {{ request()->is('classes*') ? 'active' : '' }}">
        <i class="bi {{ request()->is('classes*') ? 'bi-diagram-3-fill' : 'bi-diagram-3' }}"></i>
        <span>Classes</span>
    </a>
    <a href="{{ route('section.routine.show') }}"
       class="mobile-nav-item {{ request()->is('*routine*') ? 'active' : '' }}">
        <i class="bi {{ request()->is('*routine*') ? 'bi-calendar4-range' : 'bi-calendar4-range' }}"></i>
        <span>Routine</span>
    </a>
    <a href="{{ route('exam.list.show') }}"
       class="mobile-nav-item {{ request()->is('*exam*') ? 'active' : '' }}">
        <i class="bi {{ request()->is('*exam*') ? 'bi-file-earmark-text-fill' : 'bi-file-earmark-text' }}"></i>
        <span>Exams</span>
    </a>
    @elseif(auth()->user()?->isStudent())
    <a href="{{ route('student.attendance.show', auth()->id()) }}"
       class="mobile-nav-item {{ request()->is('students/view/attendance*') ? 'active' : '' }}">
        <i class="bi {{ request()->is('students/view/attendance*') ? 'bi-calendar-check-fill' : 'bi-calendar-check' }}"></i>
        <span>Attendance</span>
    </a>
    <a href="{{ url('homework') }}"
       class="mobile-nav-item {{ request()->is('*homework*') ? 'active' : '' }}">
        <i class="bi {{ request()->is('*homework*') ? 'bi-journal-bookmark-fill' : 'bi-journal-bookmark' }}"></i>
        <span>Homework</span>
    </a>
    <a href="{{ route('notice.index') }}"
       class="mobile-nav-item {{ request()->is('notices*') || request()->is('notice*') ? 'active' : '' }}">
        <i class="bi {{ request()->is('notices*') || request()->is('notice*') ? 'bi-megaphone-fill' : 'bi-megaphone' }}"></i>
        <span>Notices</span>
    </a>
    @elseif(auth()->user()?->isParent())
    <a href="{{ url('parent/children') }}"
       class="mobile-nav-item {{ request()->is('parent/children*') ? 'active' : '' }}">
        <i class="bi {{ request()->is('parent/children*') ? 'bi-people-fill' : 'bi-people' }}"></i>
        <span>Children</span>
    </a>
    <a href="{{ url('parent/invoices') }}"
       class="mobile-nav-item {{ request()->is('*invoice*') ? 'active' : '' }}">
        <i class="bi {{ request()->is('*invoice*') ? 'bi-receipt-cutoff' : 'bi-receipt' }}"></i>
        <span>Fees</span>
    </a>
    <a href="{{ route('notice.index') }}"
       class="mobile-nav-item {{ request()->is('notices*') || request()->is('notice*') ? 'active' : '' }}">
        <i class="bi {{ request()->is('notices*') || request()->is('notice*') ? 'bi-megaphone-fill' : 'bi-megaphone' }}"></i>
        <span>Notices</span>
    </a>
    @else
    <a href="{{ url('attendances/take') }}"
       class="mobile-nav-item {{ request()->is('attendances*') ? 'active' : '' }}">
        <i class="bi {{ request()->is('attendances*') ? 'bi-calendar-check-fill' : 'bi-calendar-check' }}"></i>
        <span>Attendance</span>
    </a>
    <a href="{{ route('course.mark.create') }}"
       class="mobile-nav-item {{ request()->is('marks*') ? 'active' : '' }}">
        <i class="bi {{ request()->is('marks*') ? 'bi-pencil-square' : 'bi-pencil-square' }}"></i>
        <span>Marks</span>
    </a>
    <a href="{{ url('students/view/list') }}"
       class="mobile-nav-item {{ request()->is('students*') ? 'active' : '' }}">
        <i class="bi {{ request()->is('students*') ? 'bi-person-badge-fill' : 'bi-person-badge' }}"></i>
        <span>Students</span>
    </a>
    <a href="{{ route('notice.index') }}"
       class="mobile-nav-item {{ request()->is('notices*') || request()->is('notice*') ? 'active' : '' }}">
        <i class="bi {{ request()->is('notices*') || request()->is('notice*') ? 'bi-megaphone-fill' : 'bi-megaphone' }}"></i>
        <span>Notices</span>
    </a>
    @endif

    {{-- Customize & Menu button --}}
    <button type="button" onclick="openBottomNavCustomizer()"
            class="mobile-nav-item"
            title="Customize Bottom Nav"
            aria-label="Customize Bottom Navigation">
        <i class="bi bi-sliders text-indigo-500"></i>
        <span>Customize</span>
    </button>
</nav>

{{-- ═══════════════════════════════════════════════════════════
     CUSTOMIZE BOTTOM NAV MODAL
═══════════════════════════════════════════════════════════ --}}
<div id="bottom-nav-customizer-modal"
     class="fixed inset-0 hidden items-end sm:items-center justify-center p-0 sm:p-4 transition-all duration-300"
     onclick="if(event.target===this)closeBottomNavCustomizer()">
    <div class="customizer-card w-full sm:max-w-xl rounded-t-[28px] sm:rounded-3xl overflow-hidden animate-in slide-in-from-bottom duration-200"
         onclick="event.stopPropagation()">
        
        {{-- Mobile Drag Handle --}}
        <div class="w-12 h-1 bg-slate-300 dark:bg-slate-700 rounded-full mx-auto my-2.5 sm:hidden flex-shrink-0"></div>

        {{-- Header (Pinned) --}}
        <div class="customizer-header px-5 py-3 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-600 text-white flex items-center justify-center shadow-md shadow-indigo-500/25">
                    <i class="bi bi-sliders text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white leading-tight">Customize Bottom Navigation</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Choose 3 to 5 shortcuts for 1-tap mobile access</p>
                </div>
            </div>
            <button type="button" onclick="closeBottomNavCustomizer()" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-700 dark:hover:text-white bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition" aria-label="Close">
                <i class="bi bi-x-lg text-xs"></i>
            </button>
        </div>

        {{-- Live Dock Preview (Sticky / Pinned at Top - Always Visible while sliding up and down) --}}
        <div class="customizer-preview-container px-4 py-2.5 sm:px-5">
            <div class="customizer-preview-bar rounded-2xl p-2.5">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                        <i class="bi bi-phone text-indigo-500"></i> Live Dock Preview
                    </span>
                    <span id="nav-selected-count-badge" class="px-2.5 py-0.5 rounded-full font-bold text-[11px] bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">
                        5 / 5 Selected
                    </span>
                </div>
                <div id="customizer-live-preview-dock" class="flex items-center justify-around gap-1 bg-white dark:bg-slate-900/90 rounded-xl p-1.5 sm:p-2 border border-slate-200/80 dark:border-slate-800 shadow-xs min-h-[46px]">
                    {{-- Preview pills inserted by JS --}}
                </div>
            </div>
        </div>

        {{-- Scrollable Body (Presets & Shortcuts Grid) --}}
        <div id="customizer-modal-body" class="p-4 sm:p-5 overflow-y-auto overscroll-contain space-y-4 flex-1">
            
            {{-- Presets --}}
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block mb-2 flex items-center gap-1">
                    <i class="bi bi-magic text-indigo-500"></i> Quick Role Presets
                </span>
                <div class="flex flex-wrap gap-1.5 sm:gap-2">
                    @if(auth()->check() && auth()->user()->isStudent())
                        <button type="button" onclick="applyNavPreset('student')" class="role-preset-chip chip-coordinator">
                            <i class="bi bi-backpack"></i> Student Daily
                        </button>
                        <button type="button" onclick="applyNavPreset('study')" class="role-preset-chip chip-teacher">
                            <i class="bi bi-journal-text"></i> Exams & Study
                        </button>
                    @elseif(auth()->check() && auth()->user()->isParent())
                        <button type="button" onclick="applyNavPreset('parent')" class="role-preset-chip chip-coordinator">
                            <i class="bi bi-people"></i> Parent Daily
                        </button>
                    @elseif(auth()->check() && (auth()->user()->isTeacher() || auth()->user()->isClassTeacher()))
                        <button type="button" onclick="applyNavPreset('teacher')" class="role-preset-chip chip-teacher">
                            <i class="bi bi-person-workspace"></i> Teacher Daily
                        </button>
                        <button type="button" onclick="applyNavPreset('classes')" class="role-preset-chip chip-coordinator">
                            <i class="bi bi-diagram-3"></i> Classes & Routine
                        </button>
                    @elseif(auth()->check() && auth()->user()->isAcademicCoord())
                        <button type="button" onclick="applyNavPreset('coordinator')" class="role-preset-chip chip-coordinator">
                            <i class="bi bi-diagram-3"></i> Academic Coordinator
                        </button>
                        <button type="button" onclick="applyNavPreset('teacher')" class="role-preset-chip chip-teacher">
                            <i class="bi bi-person-workspace"></i> Teacher
                        </button>
                    @elseif(auth()->check() && auth()->user()->isAccountant())
                        <button type="button" onclick="applyNavPreset('accountant')" class="role-preset-chip chip-accountant">
                            <i class="bi bi-cash-stack"></i> Accountant
                        </button>
                    @else
                        <button type="button" onclick="applyNavPreset('admin')" class="role-preset-chip chip-admin">
                            <i class="bi bi-shield-check"></i> Admin / Principal
                        </button>
                        <button type="button" onclick="applyNavPreset('coordinator')" class="role-preset-chip chip-coordinator">
                            <i class="bi bi-diagram-3"></i> Academic Coordinator
                        </button>
                        <button type="button" onclick="applyNavPreset('teacher')" class="role-preset-chip chip-teacher">
                            <i class="bi bi-person-workspace"></i> Teacher
                        </button>
                        <button type="button" onclick="applyNavPreset('accountant')" class="role-preset-chip chip-accountant">
                            <i class="bi bi-cash-stack"></i> Accountant
                        </button>
                    @endif
                </div>
            </div>

            {{-- Grid Title --}}
            <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
                <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Available Shortcuts</span>
                <span class="text-[11px] text-slate-400 dark:text-slate-500">Tap card to select / unselect</span>
            </div>

            {{-- Items Grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-2.5 pb-2" id="nav-items-grid">
                {{-- Injected by JS --}}
            </div>

        </div>

        {{-- Footer (Pinned) --}}
        <div class="customizer-footer px-4 py-3 sm:px-5 sm:py-3.5 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-between gap-2.5 flex-shrink-0">
            <div class="flex items-center justify-between sm:justify-start gap-2">
                <button type="button" onclick="resetBottomNavToDefault()" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white hover:bg-slate-200/70 dark:hover:bg-slate-800 transition flex items-center gap-1.5 active:scale-95">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset Defaults
                </button>
                <button type="button" onclick="closeBottomNavCustomizer()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white hover:bg-slate-200/70 dark:hover:bg-slate-800 transition sm:hidden active:scale-95">
                    Cancel
                </button>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="closeBottomNavCustomizer()" class="hidden sm:inline-flex px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-200/70 dark:hover:bg-slate-800 transition active:scale-95">
                    Cancel
                </button>
                <button type="button" onclick="saveCustomBottomNav()" class="w-full sm:w-auto justify-center px-6 py-2.5 rounded-xl text-xs font-bold bg-gradient-to-r from-indigo-600 via-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white shadow-md shadow-indigo-600/25 transition flex items-center gap-2 whitespace-nowrap active:scale-95">
                    <i class="bi bi-check2-circle text-sm leading-none"></i>
                    <span>Save & Apply</span>
                </button>
            </div>
        </div>

    </div>
</div>
@endauth

{{-- ═══════════════════════════════════════════════════════════
     COMMAND PALETTE  (Cmd/Ctrl + K)
═══════════════════════════════════════════════════════════ --}}
<div id="cmd-palette-backdrop"
     onclick="if(event.target===this)closeCommandPalette()">
    <div class="cmd-modal-box">
        <div class="cmd-header">
            <i class="bi bi-search text-slate-400 text-base flex-shrink-0"></i>
            <input id="cmd-input" type="text" placeholder="Search pages, classes, courses, exams, fees, students…"
                   autocomplete="off"
                   oninput="filterCmdResults(this.value)" onkeydown="cmdKeyNav(event)">
            <button type="button" onclick="closeCommandPalette()"
                    class="inline-flex items-center justify-center p-0.5 rounded text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
                    title="Close (ESC)">
                <kbd class="text-[10px] font-mono font-medium bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-1.5 py-0.5 rounded text-slate-500 dark:text-slate-400 cursor-pointer select-none">ESC</kbd>
            </button>
        </div>
        <div id="cmd-results">
            {{-- Populated by JS --}}
        </div>
        <div class="cmd-footer flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span><kbd class="bg-slate-200 dark:bg-slate-800 px-1 py-0.5 rounded text-[10px]">↑↓</kbd> navigate</span>
                <span><kbd class="bg-slate-200 dark:bg-slate-800 px-1 py-0.5 rounded text-[10px]">↵</kbd> select</span>
                <button type="button" onclick="closeCommandPalette()" class="inline-flex items-center gap-1 text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition cursor-pointer select-none">
                    <kbd class="bg-slate-200 dark:bg-slate-800 px-1 py-0.5 rounded text-[10px]">ESC</kbd> close
                </button>
            </div>
            <button type="button" onclick="openCreateShortcutModal()"
                    class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition cursor-pointer">
                <i class="bi bi-plus-circle-fill"></i> Add Custom Shortcut
            </button>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     CREATE CUSTOM SHORTCUT MODAL
═══════════════════════════════════════════════════════════ --}}
<div id="create-shortcut-modal" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-[1000005] hidden items-center justify-center p-4 transition-all"
     onclick="if(event.target===this)closeCreateShortcutModal()">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 w-full max-w-md p-5 text-slate-800 dark:text-slate-100 animate-in fade-in zoom-in duration-150"
         onclick="event.stopPropagation()">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold shadow-xs">
                    <i class="bi bi-lightning-charge-fill text-sm"></i>
                </span>
                <div>
                    <h4 class="font-bold text-sm leading-tight text-slate-900 dark:text-white">Create Custom Shortcut</h4>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Add any quick link or action to your shortcuts</p>
                </div>
            </div>
            <button type="button" onclick="closeCreateShortcutModal()" class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition"><i class="bi bi-x-lg text-xs"></i></button>
        </div>
        <form id="custom-shortcut-form" onsubmit="handleSaveCustomShortcut(event)" class="mt-4 space-y-3.5">
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Shortcut Title / Label <span class="text-rose-500">*</span></label>
                <input type="text" id="shortcut-title" required placeholder="e.g. Fee Collections, Class Routine, TC Form"
                       class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Target Page URL or Path <span class="text-rose-500">*</span></label>
                <input type="text" id="shortcut-url" required placeholder="e.g. /payments, /routine/view, or https://..."
                       class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Category / Group</label>
                    <input type="text" id="shortcut-category" placeholder="e.g. My Shortcuts" value="My Shortcuts"
                           class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Quick Key (Optional)</label>
                    <input type="text" id="shortcut-key" maxlength="2" placeholder="e.g. F, 1, R"
                           class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 uppercase">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Choose Icon</label>
                <div class="flex items-center gap-2 flex-wrap" id="shortcut-icon-picker">
                    <button type="button" onclick="selectShortcutIcon(this, 'bi-star-fill')" class="shortcut-icon-opt active p-2 rounded-lg border text-indigo-600 bg-indigo-50 dark:bg-indigo-950 border-indigo-500"><i class="bi bi-star-fill"></i></button>
                    <button type="button" onclick="selectShortcutIcon(this, 'bi-lightning-charge-fill')" class="shortcut-icon-opt p-2 rounded-lg border text-slate-500 border-slate-200 dark:border-slate-700"><i class="bi bi-lightning-charge-fill"></i></button>
                    <button type="button" onclick="selectShortcutIcon(this, 'bi-bookmark-fill')" class="shortcut-icon-opt p-2 rounded-lg border text-slate-500 border-slate-200 dark:border-slate-700"><i class="bi bi-bookmark-fill"></i></button>
                    <button type="button" onclick="selectShortcutIcon(this, 'bi-link-45deg')" class="shortcut-icon-opt p-2 rounded-lg border text-slate-500 border-slate-200 dark:border-slate-700"><i class="bi bi-link-45deg"></i></button>
                    <button type="button" onclick="selectShortcutIcon(this, 'bi-journal-bookmark-fill')" class="shortcut-icon-opt p-2 rounded-lg border text-slate-500 border-slate-200 dark:border-slate-700"><i class="bi bi-journal-bookmark-fill"></i></button>
                    <button type="button" onclick="selectShortcutIcon(this, 'bi-receipt')" class="shortcut-icon-opt p-2 rounded-lg border text-slate-500 border-slate-200 dark:border-slate-700"><i class="bi bi-receipt"></i></button>
                    <button type="button" onclick="selectShortcutIcon(this, 'bi-calendar-check')" class="shortcut-icon-opt p-2 rounded-lg border text-slate-500 border-slate-200 dark:border-slate-700"><i class="bi bi-calendar-check"></i></button>
                    <button type="button" onclick="selectShortcutIcon(this, 'bi-grid-fill')" class="shortcut-icon-opt p-2 rounded-lg border text-slate-500 border-slate-200 dark:border-slate-700"><i class="bi bi-grid-fill"></i></button>
                </div>
                <input type="hidden" id="shortcut-selected-icon" value="bi-star-fill">
            </div>
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeCreateShortcutModal()" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-300 transition">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-md shadow-indigo-600/25 transition flex items-center gap-1.5">
                    <i class="bi bi-plus-lg"></i> Save Shortcut
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     TOAST CONTAINER
═══════════════════════════════════════════════════════════ --}}
<div id="toast-container" class="fixed bottom-5 right-5 z-[200] flex flex-col gap-2 pointer-events-none" aria-live="polite"></div>

{{-- ═══════════════════════════════════════════════════════════
     FLOATING ACTION BUTTON (FAB)
═══════════════════════════════════════════════════════════ --}}
<div class="fixed z-[9999] flex flex-col-reverse items-end gap-2" id="fab-group" style="right:1rem; bottom:4.75rem; pointer-events:none;">
    {{-- FAB toggle --}}
    <button id="fab-main" onclick="toggleFab()"
            class="shadow-floating flex items-center justify-center transition-all duration-200 hover:scale-105 cursor-pointer"
            style="pointer-events:auto; position:relative; z-index:1;"
            title="Quick actions & Search">
        <i class="bi bi-plus-lg" id="fab-icon"></i>
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
        <button onclick="openCommandPalette(); closeFab();"
                class="fab-action flex items-center gap-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-soft text-slate-700 dark:text-slate-200 px-3 py-2 rounded-xl text-xs font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition">
            <i class="bi bi-search text-indigo-600 dark:text-indigo-400"></i> Quick Search
        </button>
    </div>
</div>

<script>
var isFabOpen = false;
function toggleFab() {
    isFabOpen = !isFabOpen;
    var actions = document.getElementById('fab-actions');
    var group   = document.getElementById('fab-group');
    var icon    = document.getElementById('fab-icon');
    if (!actions) return;
    if (isFabOpen) {
        actions.classList.remove('opacity-0', 'translate-y-2', 'pointer-events-none');
        if (group) group.style.pointerEvents = 'auto';
        if (icon) icon.className = 'bi bi-x-lg text-xl';
    } else {
        closeFab();
    }
}
function closeFab() {
    isFabOpen = false;
    var actions = document.getElementById('fab-actions');
    var group   = document.getElementById('fab-group');
    var icon    = document.getElementById('fab-icon');
    if (actions) actions.classList.add('opacity-0', 'translate-y-2', 'pointer-events-none');
    // Keep pointer-events on the button itself but disable the container
    // so it doesn't block scrolling in the bottom-right corner
    if (group) group.style.pointerEvents = 'none';
    if (icon) icon.className = 'bi bi-plus-lg text-xl';
}
document.addEventListener('click', function(e) {
    var fab = document.getElementById('fab-group');
    if (fab && !fab.contains(e.target) && isFabOpen) {
        closeFab();
    }
});
// On page load make sure container allows click through
document.addEventListener('DOMContentLoaded', function() {
    var group = document.getElementById('fab-group');
    if (group) group.style.pointerEvents = 'none';
});
</script>

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
            <div class="flex justify-between items-center py-1.5 border-b border-slate-100 dark:border-slate-800">
                <span class="text-slate-600 dark:text-slate-300">This help</span>
                <kbd class="bg-slate-100 dark:bg-slate-800 text-slate-500 px-2 py-0.5 rounded text-xs">?</kbd>
            </div>
        </div>
        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <span class="text-[11px] text-slate-400">Custom Quick Links</span>
            <button onclick="closeShortcutModal(); openCreateShortcutModal();"
                    class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 flex items-center gap-1 cursor-pointer">
                <i class="bi bi-plus-circle-fill"></i> + Create Shortcut
            </button>
        </div>
    </div>
</div>

@endauth

@guest
{{-- ═══════════════════════════════════════════════════════════
     GUEST SHELL (login / register pages)
═══════════════════════════════════════════════════════════ --}}
<div id="app" class="relative">
    {{-- Floating Theme Switcher for Guest pages --}}
    <div class="fixed top-4 right-4 z-50">
        <button onclick="toggleTheme()"
                id="guest-theme-toggle-btn"
                class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white/80 dark:bg-slate-800/80 backdrop-blur-md text-slate-600 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-700 shadow-md transition-all cursor-pointer flex items-center justify-center"
                title="Toggle light / dark mode"
                aria-label="Toggle theme">
            <i class="bi bi-moon-stars text-sm dark:hidden"></i>
            <i class="bi bi-sun text-sm hidden dark:inline text-amber-400"></i>
        </button>
    </div>
    <main class="min-h-screen bg-slate-50 dark:bg-slate-900 transition-colors">
        @yield('content')
    </main>
</div>
@endguest

{{-- ═══════════════════════════════════════════════════════════
     COMPILED JS
═══════════════════════════════════════════════════════════ --}}
<script src="{{ asset('js/app.js') }}"></script>

{{-- ── Comprehensive Dynamic Command Palette & Global Search Engine ───────── --}}
<script>
(function() {
    // ── Local Storage Helpers for User Pinned & Custom Shortcuts ─────────
    function getPinnedUrls() {
        try { return JSON.parse(localStorage.getItem('nshub_pinned_urls') || '[]'); } catch(e) { return []; }
    }
    function savePinnedUrls(urls) {
        try { localStorage.setItem('nshub_pinned_urls', JSON.stringify(urls)); } catch(e) {}
    }
    function getCustomShortcuts() {
        try { return JSON.parse(localStorage.getItem('nshub_custom_shortcuts') || '[]'); } catch(e) { return []; }
    }
    function saveCustomShortcuts(shortcuts) {
        try { localStorage.setItem('nshub_custom_shortcuts', JSON.stringify(shortcuts)); } catch(e) {}
    }

    function togglePinShortcut(cleanPath, label, e) {
        if (e) { e.preventDefault(); e.stopPropagation(); }
        let pinned = getPinnedUrls();
        if (pinned.includes(cleanPath)) {
            pinned = pinned.filter(p => p !== cleanPath);
            if (typeof showToast === 'function') showToast(`Unpinned "${label}" from shortcuts`, 'info');
        } else {
            pinned.push(cleanPath);
            if (typeof showToast === 'function') showToast(`Pinned "${label}" to shortcuts!`, 'success');
        }
        savePinnedUrls(pinned);
        const input = document.getElementById('cmd-input');
        filterResults(input ? input.value : '');
    }

    function removeCustomShortcut(id, e) {
        if (e) { e.preventDefault(); e.stopPropagation(); }
        let customs = getCustomShortcuts();
        const target = customs.find(c => c.id === id);
        customs = customs.filter(c => c.id !== id);
        saveCustomShortcuts(customs);
        if (typeof showToast === 'function') showToast(`Removed custom shortcut "${target?.label || ''}"`, 'info');
        const input = document.getElementById('cmd-input');
        filterResults(input ? input.value : '');
    }

    function openCreateShortcutModal() {
        const modal = document.getElementById('create-shortcut-modal');
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
        const form = document.getElementById('custom-shortcut-form');
        if (form) form.reset();
        const input = document.getElementById('shortcut-title');
        if (input) setTimeout(() => input.focus(), 50);
    }

    function closeCreateShortcutModal() {
        const modal = document.getElementById('create-shortcut-modal');
        if (modal) {
            modal.classList.add('hidden');
            modal.style.display = 'none';
        }
    }

    function selectShortcutIcon(btn, icon) {
        document.querySelectorAll('#shortcut-icon-picker .shortcut-icon-opt').forEach(b => {
            b.classList.remove('active', 'border-indigo-500', 'bg-indigo-50', 'dark:bg-indigo-950', 'text-indigo-600');
            b.classList.add('border-slate-200', 'dark:border-slate-700', 'text-slate-500');
        });
        if (btn) {
            btn.classList.add('active', 'border-indigo-500', 'bg-indigo-50', 'dark:bg-indigo-950', 'text-indigo-600');
            btn.classList.remove('border-slate-200', 'dark:border-slate-700', 'text-slate-500');
        }
        const hidden = document.getElementById('shortcut-selected-icon');
        if (hidden) hidden.value = icon;
    }

    function handleSaveCustomShortcut(e) {
        e.preventDefault();
        const title = (document.getElementById('shortcut-title')?.value || '').trim();
        let url = (document.getElementById('shortcut-url')?.value || '').trim();
        const category = (document.getElementById('shortcut-category')?.value || '').trim() || 'My Shortcuts';
        const key = (document.getElementById('shortcut-key')?.value || '').trim().toUpperCase();
        const icon = document.getElementById('shortcut-selected-icon')?.value || 'bi-star-fill';

        if (!title || !url) return;

        // Ensure relative paths start with slash if not a full protocol
        if (!url.startsWith('http://') && !url.startsWith('https://') && !url.startsWith('/') && !url.startsWith('#')) {
            url = '/' + url;
        }

        const newShortcut = {
            id: 'sc_' + Date.now(),
            label: title,
            href: url,
            category: category,
            icon: icon.includes('bi-') ? icon : 'bi-' + icon,
            shortcut: key || undefined,
            keywords: [title.toLowerCase(), category.toLowerCase(), 'shortcut', 'custom', key.toLowerCase()].filter(Boolean),
            isCustom: true
        };

        const customs = getCustomShortcuts();
        customs.unshift(newShortcut);
        saveCustomShortcuts(customs);

        closeCreateShortcutModal();
        if (typeof showToast === 'function') showToast(`Custom shortcut "${title}" created!`, 'success');
        
        // Refresh Palette
        const cmdInput = document.getElementById('cmd-input');
        filterResults(cmdInput ? cmdInput.value : '');
    }

    function getActiveSidebarNavigation() {
        const items = [];
        const seenPaths = new Set();
        const pinnedUrls = getPinnedUrls();
        const sidebarNav = document.querySelector('.sidebar-nav') || document.querySelector('#sidebar .sidebar-scroll') || document.getElementById('sidebar');

        // 1. Incorporate User Created Custom Shortcuts first
        const customShortcuts = getCustomShortcuts();
        customShortcuts.forEach(sc => {
            if (!seenPaths.has(sc.href)) {
                seenPaths.add(sc.href);
                items.push({
                    id: sc.id,
                    label: sc.label,
                    icon: sc.icon.includes('bi-') ? 'bi ' + sc.icon : 'bi bi-' + sc.icon,
                    category: sc.category || '★ My Shortcuts',
                    href: sc.href,
                    shortcut: sc.shortcut,
                    keywords: sc.keywords || [sc.label.toLowerCase()],
                    isCustom: true,
                    isPinned: pinnedUrls.includes(sc.href)
                });
            }
        });

        // 2. Scan active DOM navigation
        if (sidebarNav) {
            let currentCategory = 'Navigation';
            const elements = sidebarNav.querySelectorAll('.sidebar-section-label, a.sidebar-link, a[href]');
            elements.forEach(el => {
                if (el.classList.contains('sidebar-section-label') || el.tagName === 'P') {
                    const clone = el.cloneNode(true);
                    clone.querySelectorAll('.sidebar-badge, .badge, span').forEach(b => b.remove());
                    const catText = clone.textContent.replace(/\s+/g, ' ').trim();
                    if (catText && !catText.includes('Impersonating')) {
                        currentCategory = catText;
                    }
                } else if (el.tagName === 'A') {
                    const href = el.getAttribute('href');
                    if (!href || href === '#' || href.startsWith('javascript:')) return;

                    let cleanPath = href;
                    try {
                        const parsed = new URL(href, window.location.origin);
                        cleanPath = parsed.pathname + (parsed.search || '');
                    } catch(e) {}

                    if (cleanPath.includes('logout') || seenPaths.has(cleanPath)) return;

                    const textEl = el.querySelector('.sidebar-link-text') || el;
                    const clone = textEl.cloneNode(true);
                    clone.querySelectorAll('.sidebar-badge, .badge, span[class*="badge"]').forEach(b => b.remove());
                    const label = clone.textContent.replace(/\s+/g, ' ').trim();

                    if (!label || label.toLowerCase().includes('nepalschoolhub')) return;
                    seenPaths.add(cleanPath);

                    const iconEl = el.querySelector('.sidebar-link-icon i, i');
                    const icon = iconEl ? iconEl.className : 'bi bi-grid-fill';

                    const words = label.toLowerCase().split(/[\s—\-\&/,()]+/).filter(w => w.length > 1);
                    const catWords = currentCategory.toLowerCase().split(/[\s—\-\&/,()]+/).filter(w => w.length > 1);
                    const keywords = Array.from(new Set([...words, ...catWords, label.toLowerCase(), currentCategory.toLowerCase()]));

                    const lLower = label.toLowerCase();
                    const cLower = currentCategory.toLowerCase();

                    if (lLower.includes('class') || lLower.includes('section') || cLower.includes('academic')) {
                        keywords.push('class', 'classes', 'section', 'sections', 'grade', 'grades', 'classroom', 'routine', 'timetable');
                    }
                    if (lLower.includes('course') || lLower.includes('subject') || lLower.includes('curriculum') || lLower.includes('syllabus')) {
                        keywords.push('course', 'courses', 'subject', 'subjects', 'curriculum', 'syllabus', 'book', 'theory', 'practical', 'study');
                    }
                    if (lLower.includes('homework') || lLower.includes('assignment') || lLower.includes('project')) {
                        keywords.push('homework', 'hw', 'assignment', 'assignments', 'project', 'projects', 'tasks', 'submissions');
                    }
                    if (lLower.includes('exam') || lLower.includes('mark') || lLower.includes('result') || lLower.includes('grade') || lLower.includes('assessment')) {
                        keywords.push('exam', 'exams', 'marks', 'mark', 'result', 'results', 'marksheet', 'report card', 'grade', 'gradebook', 'test', 'quiz');
                    }
                    if (lLower.includes('fee') || lLower.includes('payment') || lLower.includes('invoice') || lLower.includes('finance') || lLower.includes('salary') || lLower.includes('expense') || lLower.includes('income') || lLower.includes('payroll')) {
                        keywords.push('fee', 'fees', 'invoice', 'invoices', 'payment', 'payments', 'dues', 'receipt', 'finance', 'salary', 'payroll', 'expense', 'income', 'qr', 'bill');
                    }
                    if (lLower.includes('attend')) {
                        keywords.push('attendance', 'present', 'absent', 'roll call', 'rfid', 'daily attendance');
                    }
                    if (lLower.includes('student') || lLower.includes('admiss') || lLower.includes('alumni') || lLower.includes('child')) {
                        keywords.push('student', 'students', 'admission', 'admissions', 'enquiry', 'inquiry', 'children', 'child', 'alumni', 'transfer', 'tc', 'roll');
                    }
                    if (lLower.includes('teach') || lLower.includes('staff') || lLower.includes('faculty') || lLower.includes('hr')) {
                        keywords.push('teacher', 'teachers', 'staff', 'faculty', 'employee', 'hr', 'instructor', 'leave');
                    }
                    if (lLower.includes('routine') || lLower.includes('timetable') || lLower.includes('schedule')) {
                        keywords.push('routine', 'timetable', 'schedule', 'periods', 'time table', 'calendar');
                    }
                    if (lLower.includes('notice') || lLower.includes('circular') || lLower.includes('broadcast') || lLower.includes('message')) {
                        keywords.push('notice', 'notices', 'circular', 'announcements', 'broadcast', 'sms', 'message', 'news');
                    }
                    if (lLower.includes('librar') || lLower.includes('book')) {
                        keywords.push('library', 'books', 'book catalog', 'issue book', 'return book');
                    }
                    if (lLower.includes('transport') || lLower.includes('bus') || lLower.includes('vehicle')) {
                        keywords.push('transport', 'bus', 'van', 'fleet', 'vehicles', 'routes', 'tracking');
                    }
                    if (lLower.includes('hostel') || lLower.includes('dormitory')) {
                        keywords.push('hostel', 'dormitory', 'rooms', 'beds', 'room allocation');
                    }
                    if (lLower.includes('note') || lLower.includes('material') || lLower.includes('online')) {
                        keywords.push('study notes', 'materials', 'online classes', 'online exams', 'video class', 'resources');
                    }
                    if (lLower.includes('quiz')) {
                        keywords.push('quiz', 'live quiz', 'trivia', 'game', 'arena');
                    }

                    const isPinned = pinnedUrls.includes(cleanPath);

                    items.push({
                        label: label,
                        icon: icon,
                        category: currentCategory,
                        href: href,
                        cleanPath: cleanPath,
                        keywords: keywords,
                        isPinned: isPinned
                    });
                }
            });
        }

        // Add System Actions
        items.push(
            { label: 'Create New Shortcut', icon: 'bi bi-plus-circle-fill text-indigo-500', category: 'Actions', action: 'createShortcut', keywords: ['add shortcut', 'new shortcut', 'create shortcut', 'custom shortcut', 'bookmark'], shortcut: 'N' },
            { label: 'Toggle Dark / Light Mode', icon: 'bi bi-moon-stars-fill', category: 'Actions', action: 'toggleTheme', keywords: ['dark mode', 'light mode', 'theme', 'night mode'], shortcut: 'T' },
            { label: 'Change Password', icon: 'bi bi-key-fill', category: 'Actions', href: '/password/change', keywords: ['change password', 'reset password', 'security credentials', 'password'] },
            { label: 'Keyboard Shortcuts Help', icon: 'bi bi-keyboard-fill', category: 'Actions', action: 'showShortcuts', keywords: ['shortcuts', 'hotkeys', 'keyboard help', 'help'], shortcut: '?' }
        );

        return items;
    }

    let cmdSelected = -1;
    let searchDebounceTimer = null;
    let searchAbortController = null;

    function openPalette() {
        const backdrop = document.getElementById('cmd-palette-backdrop');
        const input    = document.getElementById('cmd-input');
        if (!backdrop) return;
        backdrop.classList.add('is-open');
        backdrop.style.display = 'flex';
        if (input) {
            input.value = '';
            setTimeout(() => input.focus(), 50);
        }
        filterResults('');
        cmdSelected = -1;
    }

    function closePalette() {
        const backdrop = document.getElementById('cmd-palette-backdrop');
        const input    = document.getElementById('cmd-input');
        if (backdrop) {
            backdrop.classList.remove('is-open');
            backdrop.style.display = 'none';
        }
        if (input) {
            input.blur();
        }
        cmdSelected = -1;
        if (searchAbortController) {
            try { searchAbortController.abort(); } catch(e) {}
        }
    }

    function hl(text, query) {
        if (!query) return text;
        const re = new RegExp('(' + query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
        return String(text).replace(re, '<mark class="bg-amber-200 text-slate-900 rounded-sm px-0.5">$1</mark>');
    }

    function renderResultsList(matched, dbItems, q, container) {
        let html = '';

        // 1. Sidebar Navigation Items & Shortcuts
        if (matched.length > 0) {
            html += `<div class="cmd-group-label">Pages & Shortcuts (${matched.length})</div>`;
            matched.forEach((c, i) => {
                const label = hl(c.label, q);
                const shortcutBadge = c.shortcut ? `<span class="cmd-badge font-mono">${c.shortcut}</span>` : '';
                const tag = c.href ? 'a' : 'button';
                const hrefAttr = c.href ? `href="${c.href}"` : '';
                const actionAttr = c.action ? `onclick="cmdRunAction('${c.action}')"` : '';
                const iconClass = c.icon ? (c.icon.includes('bi-') ? c.icon : 'bi ' + c.icon) : 'bi bi-grid-fill';
                const pinClass = c.isPinned ? 'is-pinned text-amber-500' : 'text-slate-400 hover:text-amber-500';
                const pinIcon = c.isPinned ? 'bi-star-fill' : 'bi-star';
                const pinPath = c.cleanPath || c.href || '';
                
                let pinBtn = '';
                if (c.href) {
                    pinBtn = `<button type="button" class="cmd-pin-btn ${pinClass}" data-pin-path="${pinPath}" onclick="togglePinShortcut(this.getAttribute('data-pin-path'), this.closest('.cmd-result-item')?.querySelector('.cmd-result-title span')?.textContent || '', event)" title="${c.isPinned ? 'Unpin from shortcuts' : 'Pin to shortcuts'}">
                        <i class="bi ${pinIcon}"></i>
                    </button>`;
                }

                let deleteBtn = '';
                if (c.isCustom && c.id) {
                    deleteBtn = `<button type="button" class="cmd-delete-btn" data-del-id="${c.id}" onclick="removeCustomShortcut(this.getAttribute('data-del-id'), event)" title="Delete custom shortcut">
                        <i class="bi bi-trash"></i>
                    </button>`;
                }

                html += `<${tag} class="cmd-result-item" ${hrefAttr} ${actionAttr} data-cmd-idx="${i}">
                    <span class="cmd-icon-box"><i class="${iconClass}"></i></span>
                    <div class="cmd-result-text">
                        <div class="cmd-result-title flex items-center gap-1.5">
                            <span>${label}</span>
                            ${c.isCustom ? '<span class="text-[9px] px-1 py-0.2 bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 rounded font-bold uppercase">Custom</span>' : ''}
                        </div>
                        <div class="cmd-result-subtitle">${c.category}</div>
                    </div>
                    <div class="cmd-item-actions">
                        ${shortcutBadge}
                        ${pinBtn}
                        ${deleteBtn}
                    </div>
                </${tag}>`;
            });
        }

        // 2. Dynamic Database Records
        if (dbItems && dbItems.length > 0) {
            html += `<div class="cmd-group-label" style="color:#2563eb;">Database Records (${dbItems.length})</div>`;
            dbItems.forEach((item, i) => {
                const label = hl(item.label, q);
                const sublabel = hl(item.sublabel, q);
                const iconClass = item.icon ? (item.icon.includes('bi-') ? item.icon : 'bi ' + item.icon) : 'bi bi-bookmark-fill';
                const globalIdx = matched.length + i;
                html += `<a class="cmd-result-item" href="${item.href}" data-cmd-idx="${globalIdx}">
                    <span class="cmd-icon-box" style="background:#eff6ff;color:#2563eb;"><i class="${iconClass}"></i></span>
                    <div class="cmd-result-text">
                        <div class="cmd-result-title">${label}</div>
                        <div class="cmd-result-subtitle">${sublabel}</div>
                    </div>
                </a>`;
            });
        }

        if (!html) {
            html = `<div style="padding:2.5rem 1rem; text-align:center; color:#94a3b8; font-size:0.875rem;">
                <i class="bi bi-search" style="display:block; font-size:1.75rem; margin-bottom:0.5rem; opacity:0.6;"></i>
                <p>No results found for "${q}"</p>
                <button type="button" onclick="openCreateShortcutModal()" class="mt-2 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                    + Create a custom shortcut for "${q}"
                </button>
            </div>`;
        }

        container.innerHTML = html;
        cmdSelected = -1;
    }

    function renderInitialGrouped(matched, container) {
        if (!matched.length) {
            container.innerHTML = `<div style="padding:2rem 1rem; text-align:center; color:#94a3b8;">
                <p>No navigation items found.</p>
                <button type="button" onclick="openCreateShortcutModal()" class="mt-2 px-3 py-1.5 bg-indigo-600 text-white text-xs font-bold rounded-xl shadow-xs">
                    + Create Custom Shortcut
                </button>
            </div>`;
            return;
        }

        // Separate pinned items & custom items for top display
        const pinnedList = matched.filter(c => c.isPinned && !c.isCustom);
        const customList = matched.filter(c => c.isCustom);
        const regularList = matched.filter(c => !c.isPinned && !c.isCustom);

        const groups = {};

        if (pinnedList.length > 0) {
            groups['★ Pinned Shortcuts'] = pinnedList;
        }
        if (customList.length > 0) {
            groups['★ My Custom Shortcuts'] = customList;
        }

        regularList.forEach(c => {
            if (!groups[c.category]) groups[c.category] = [];
            groups[c.category].push(c);
        });

        let html = '';
        let totalIdx = 0;
        Object.keys(groups).forEach(cat => {
            const isSpecialGroup = cat.startsWith('★');
            const groupColor = isSpecialGroup ? 'color:#f59e0b;font-weight:700;' : '';
            html += `<div class="cmd-group-label" style="${groupColor}">${cat} (${groups[cat].length})</div>`;
            groups[cat].forEach((c) => {
                const shortcutBadge = c.shortcut ? `<span class="cmd-badge font-mono">${c.shortcut}</span>` : '';
                const tag = c.href ? 'a' : 'button';
                const hrefAttr = c.href ? `href="${c.href}"` : '';
                const actionAttr = c.action ? `onclick="cmdRunAction('${c.action}')"` : '';
                const iconClass = c.icon ? (c.icon.includes('bi-') ? c.icon : 'bi ' + c.icon) : 'bi bi-grid-fill';
                const pinClass = c.isPinned ? 'is-pinned text-amber-500' : 'text-slate-400 hover:text-amber-500';
                const pinIcon = c.isPinned ? 'bi-star-fill' : 'bi-star';
                const pinPath = c.cleanPath || c.href || '';
                
                let pinBtn = '';
                if (c.href) {
                    pinBtn = `<button type="button" class="cmd-pin-btn ${pinClass}" data-pin-path="${pinPath}" onclick="togglePinShortcut(this.getAttribute('data-pin-path'), this.closest('.cmd-result-item')?.querySelector('.cmd-result-title span')?.textContent || '', event)" title="${c.isPinned ? 'Unpin from shortcuts' : 'Pin to shortcuts'}">
                        <i class="bi ${pinIcon}"></i>
                    </button>`;
                }

                let deleteBtn = '';
                if (c.isCustom && c.id) {
                    deleteBtn = `<button type="button" class="cmd-delete-btn" data-del-id="${c.id}" onclick="removeCustomShortcut(this.getAttribute('data-del-id'), event)" title="Delete custom shortcut">
                        <i class="bi bi-trash"></i>
                    </button>`;
                }

                html += `<${tag} class="cmd-result-item" ${hrefAttr} ${actionAttr} data-cmd-idx="${totalIdx++}">
                    <span class="cmd-icon-box"><i class="${iconClass}"></i></span>
                    <div class="cmd-result-text">
                        <div class="cmd-result-title flex items-center gap-1.5">
                            <span>${c.label}</span>
                            ${c.isCustom ? '<span class="text-[9px] px-1 py-0.2 bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 rounded font-bold uppercase">Custom</span>' : ''}
                        </div>
                        <div class="cmd-result-subtitle">${c.category}</div>
                    </div>
                    <div class="cmd-item-actions">
                        ${shortcutBadge}
                        ${pinBtn}
                        ${deleteBtn}
                    </div>
                </${tag}>`;
            });
        });

        container.innerHTML = html;
        cmdSelected = -1;
    }

    function filterResults(query) {
        const container = document.getElementById('cmd-results');
        if (!container) return;

        const q = (query || '').trim().toLowerCase();
        const liveItems = getActiveSidebarNavigation();

        if (!q) {
            renderInitialGrouped(liveItems, container);
            return;
        }

        const qWords = q.split(/\s+/).filter(Boolean);
        const matched = liveItems.filter(c => {
            const labelLower = (c.label || '').toLowerCase();
            const catLower = (c.category || '').toLowerCase();
            const kwList = Array.isArray(c.keywords) ? c.keywords : [];

            // Full query matches label or category directly
            if (labelLower.includes(q) || catLower.includes(q)) return true;

            // Shortcut key match (e.g. searching "1" or "N")
            if (c.shortcut && c.shortcut.toLowerCase() === q) return true;

            // Every word in search query matches either label, category, or a keyword
            return qWords.every(word => {
                if (labelLower.includes(word) || catLower.includes(word)) return true;
                return kwList.some(k => k.toLowerCase().includes(word));
            });
        });

        // 1. Instantly render matching local sidebar items with ZERO latency
        renderResultsList(matched, [], q, container);

        // 2. Debounced background fetch for dynamic database items
        if (searchDebounceTimer) clearTimeout(searchDebounceTimer);
        if (searchAbortController) {
            try { searchAbortController.abort(); } catch(e) {}
        }
        searchAbortController = new AbortController();

        searchDebounceTimer = setTimeout(() => {
            fetch(`/search?q=${encodeURIComponent(q)}`, {
                signal: searchAbortController.signal,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(r => r.ok ? r.json() : { data: [] })
            .then(response => {
                const dbItems = response.data || [];
                renderResultsList(matched, dbItems, q, container);
            })
            .catch(err => {
                if (err.name !== 'AbortError') {
                    renderResultsList(matched, [], q, container);
                }
            });
        }, 150);
    }

    function keyNav(e) {
        if (e.key === 'Escape' || e.key === 'Esc' || e.keyCode === 27) {
            e.preventDefault();
            e.stopPropagation();
            closePalette();
            return;
        }

        const items = document.querySelectorAll('#cmd-results .cmd-result-item');
        if (!items.length) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            cmdSelected = Math.min(cmdSelected + 1, items.length - 1);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            cmdSelected = Math.max(cmdSelected - 1, 0);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            const target = (cmdSelected >= 0 && items[cmdSelected]) ? items[cmdSelected] : items[0];
            if (target) {
                if (target.getAttribute('href')) {
                    window.location.href = target.getAttribute('href');
                } else {
                    target.click();
                }
            }
            closePalette();
            return;
        }

        items.forEach((el, idx) => {
            el.classList.toggle('is-selected', idx === cmdSelected);
        });

        if (items[cmdSelected]) {
            items[cmdSelected].scrollIntoView({ block: 'nearest' });
        }
    }

    // Expose functions to window
    window.openCommandPalette       = openPalette;
    window.closeCommandPalette      = closePalette;
    window.filterCmdResults         = filterResults;
    window.cmdKeyNav                = keyNav;
    window.togglePinShortcut        = togglePinShortcut;
    window.removeCustomShortcut     = removeCustomShortcut;
    window.openCreateShortcutModal  = openCreateShortcutModal;
    window.closeCreateShortcutModal = closeCreateShortcutModal;
    window.selectShortcutIcon       = selectShortcutIcon;
    window.handleSaveCustomShortcut = handleSaveCustomShortcut;
    window.cmdRunAction             = function(action) {
        closePalette();
        if (action === 'createShortcut') openCreateShortcutModal();
        if (action === 'toggleTheme' && typeof window.toggleTheme === 'function') window.toggleTheme();
        if (action === 'showShortcuts' && typeof window.openShortcutModal === 'function') window.openShortcutModal();
    };

    // Global Key Listener for Cmd/Ctrl+K and Escape
    document.addEventListener('keydown', function(e) {
        if ((e.metaKey || e.ctrlKey) && (e.key === 'k' || e.key === 'K')) {
            e.preventDefault();
            openPalette();
        } else if (e.key === 'Escape' || e.key === 'Esc' || e.keyCode === 27) {
            const backdrop = document.getElementById('cmd-palette-backdrop');
            if (backdrop && (backdrop.classList.contains('is-open') || backdrop.style.display === 'flex' || window.getComputedStyle(backdrop).display !== 'none')) {
                e.preventDefault();
                closePalette();
            }
            const scModal = document.getElementById('create-shortcut-modal');
            if (scModal && !scModal.classList.contains('hidden') && window.getComputedStyle(scModal).display !== 'none') {
                closeCreateShortcutModal();
            }
        }
    });

    // Also attach to search triggers in header
    document.querySelectorAll('[data-open-cmd], .search-btn, .top-search-bar, .app-search-box').forEach(function(el) {
        el.addEventListener('click', function(e) {
            e.preventDefault();
            openPalette();
        });
    });
})();
</script>

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

@stack('modals')
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

{{-- ═══════════════════════════════════════════════════════════
     CUSTOM BOTTOM NAVIGATION SCRIPT ENGINE
═══════════════════════════════════════════════════════════ --}}
<script>
(function () {
    const STORAGE_KEY = 'nsh_custom_bottom_nav_v2';
    
    @php
        $navUser = auth()->user();
        $allowedNavItems = [];

        if ($navUser) {
            $allowedNavItems[] = [
                'id'    => 'home',
                'label' => 'Home',
                'icon'  => 'bi-grid-fill',
                'url'   => url('home'),
                'match' => ['/home'],
            ];

            if ($navUser->isStudent()) {
                $allowedNavItems[] = ['id' => 'routine',    'label' => 'Timetable',   'icon' => 'bi-calendar4-range',       'url' => route('section.routine.show'),           'match' => ['routine']];
                $allowedNavItems[] = ['id' => 'attendance', 'label' => 'Attendance',  'icon' => 'bi-calendar-check-fill',   'url' => route('student.attendance.show', $navUser->id), 'match' => ['attendance']];
                $allowedNavItems[] = ['id' => 'homework',   'label' => 'Homework',    'icon' => 'bi-journal-bookmark-fill', 'url' => route('homework.index'),                  'match' => ['homework']];
                $allowedNavItems[] = ['id' => 'results',    'label' => 'Results',     'icon' => 'bi-award-fill',            'url' => route('results.index'),                   'match' => ['results']];
                $allowedNavItems[] = ['id' => 'onlineexams','label' => 'Exams',       'icon' => 'bi-laptop-fill',           'url' => route('online-exams.student.index'),     'match' => ['online-exams']];
                $allowedNavItems[] = ['id' => 'livequiz',   'label' => 'Live Quiz',   'icon' => 'bi-broadcast text-warning','url' => route('live-quiz.player.join'),          'match' => ['live-quiz']];
                $allowedNavItems[] = ['id' => 'studynotes', 'label' => 'Notes',       'icon' => 'bi-file-earmark-text-fill','url' => route('study-notes.index'),            'match' => ['study-notes']];
                $allowedNavItems[] = ['id' => 'fees',       'label' => 'Fees',        'icon' => 'bi-receipt-cutoff',        'url' => route('payments.index'),                 'match' => ['payment', 'invoice', 'fee']];
                $allowedNavItems[] = ['id' => 'notices',    'label' => 'Notices',     'icon' => 'bi-megaphone-fill',        'url' => route('notice.index'),                   'match' => ['notice']];
                $allowedNavItems[] = ['id' => 'events',     'label' => 'Calendar',    'icon' => 'bi-calendar-event-fill',   'url' => route('events.show'),                    'match' => ['event', 'calendar']];
                $allowedNavItems[] = ['id' => 'library',    'label' => 'Library',     'icon' => 'bi-book-fill',             'url' => route('library.index'),                  'match' => ['library']];
                $allowedNavItems[] = ['id' => 'leave',      'label' => 'Leaves',      'icon' => 'bi-calendar-x-fill',       'url' => route('leave.index'),                    'match' => ['leave']];
            } elseif ($navUser->isParent()) {
                $allowedNavItems[] = ['id' => 'attendance', 'label' => 'Attendance',     'icon' => 'bi-calendar-check-fill',   'url' => route('home'),                           'match' => ['attendance']];
                $allowedNavItems[] = ['id' => 'results',    'label' => 'Results',        'icon' => 'bi-award-fill',            'url' => route('results.index'),                   'match' => ['results']];
                $allowedNavItems[] = ['id' => 'fees',       'label' => 'Fees & Invoices','icon' => 'bi-receipt-cutoff',        'url' => route('payments.index'),                 'match' => ['payment', 'invoice', 'fee']];
                $allowedNavItems[] = ['id' => 'notices',    'label' => 'Notices',        'icon' => 'bi-megaphone-fill',        'url' => route('notice.index'),                   'match' => ['notice']];
                $allowedNavItems[] = ['id' => 'events',     'label' => 'Calendar',       'icon' => 'bi-calendar-event-fill',   'url' => route('events.show'),                    'match' => ['event', 'calendar']];
                $allowedNavItems[] = ['id' => 'leave',      'label' => 'Leaves',         'icon' => 'bi-calendar-x-fill',       'url' => route('leave.index'),                    'match' => ['leave']];
            } elseif ($navUser->isTeacher() || $navUser->isClassTeacher()) {
                $allowedNavItems[] = ['id' => 'classes',    'label' => 'Classes',        'icon' => 'bi-diagram-3-fill',        'url' => url('classes'),                           'match' => ['/classes']];
                $allowedNavItems[] = ['id' => 'routine',    'label' => 'Routine',        'icon' => 'bi-calendar4-range',       'url' => route('section.routine.show'),           'match' => ['routine']];
                $allowedNavItems[] = ['id' => 'attendance', 'label' => 'Attendance',     'icon' => 'bi-calendar-check-fill',   'url' => route('attendance.create.show'),         'match' => ['attendance']];
                $allowedNavItems[] = ['id' => 'students',   'label' => 'Students',       'icon' => 'bi-people-fill',           'url' => route('student.list.show'),              'match' => ['students']];
                $allowedNavItems[] = ['id' => 'homework',   'label' => 'Homework',       'icon' => 'bi-journal-bookmark-fill', 'url' => route('homework.index'),                  'match' => ['homework']];
                $allowedNavItems[] = ['id' => 'exams',      'label' => 'Exams',          'icon' => 'bi-file-earmark-text-fill','url' => route('exam.list.show'),                  'match' => ['exam']];
                $allowedNavItems[] = ['id' => 'aipaper',    'label' => 'AI Paper',       'icon' => 'bi-stars',                 'url' => route('question-papers.create'),         'match' => ['paper', 'question-paper']];
                $allowedNavItems[] = ['id' => 'grades',     'label' => 'Marks Entry',    'icon' => 'bi-award-fill',            'url' => route('course.mark.list.show'),          'match' => ['mark', 'grade']];
                $allowedNavItems[] = ['id' => 'livequiz',   'label' => 'Live Quiz',      'icon' => 'bi-trophy-fill',           'url' => route('live-quiz.index'),                'match' => ['live-quiz', 'quiz']];
                $allowedNavItems[] = ['id' => 'notices',    'label' => 'Notices',        'icon' => 'bi-megaphone-fill',        'url' => route('notice.index'),                   'match' => ['notice']];
                $allowedNavItems[] = ['id' => 'events',     'label' => 'Calendar',       'icon' => 'bi-calendar-event-fill',   'url' => route('events.show'),                    'match' => ['event', 'calendar']];
                $allowedNavItems[] = ['id' => 'library',    'label' => 'Library',        'icon' => 'bi-book-fill',             'url' => route('library.index'),                  'match' => ['library']];
                $allowedNavItems[] = ['id' => 'leave',      'label' => 'Leaves',         'icon' => 'bi-calendar-x-fill',       'url' => route('leave.index'),                    'match' => ['leave']];
            } else {
                // Admin, Principal, Coordinator, Staff
                $allowedNavItems[] = ['id' => 'classes',    'label' => 'Classes',        'icon' => 'bi-diagram-3-fill',        'url' => url('classes'),                           'match' => ['/classes']];
                $allowedNavItems[] = ['id' => 'routine',    'label' => 'Routine',        'icon' => 'bi-calendar4-range',       'url' => route('section.routine.show'),           'match' => ['routine']];
                $allowedNavItems[] = ['id' => 'attendance', 'label' => 'Attendance',     'icon' => 'bi-calendar-check-fill',   'url' => route('attendance.create.show'),         'match' => ['attendance']];
                $allowedNavItems[] = ['id' => 'students',   'label' => 'Students',       'icon' => 'bi-people-fill',           'url' => route('student.list.show'),              'match' => ['students']];
                if ($navUser->hasAnyRole(['super-admin', 'admin', 'principal', 'vice-principal', 'hr-manager', 'hr'])) {
                    $allowedNavItems[] = ['id' => 'teachers',   'label' => 'Teachers',       'icon' => 'bi-person-badge-fill',     'url' => route('teachers.index'),                  'match' => ['teachers', 'teacher']];
                }
                $allowedNavItems[] = ['id' => 'exams',      'label' => 'Exams',          'icon' => 'bi-file-earmark-text-fill','url' => route('exam.list.show'),                  'match' => ['exam']];
                $allowedNavItems[] = ['id' => 'aipaper',    'label' => 'AI Paper',       'icon' => 'bi-stars',                 'url' => route('question-papers.create'),         'match' => ['paper', 'question-paper']];
                $allowedNavItems[] = ['id' => 'idcards',    'label' => 'ID Cards',       'icon' => 'bi-qr-code-scan',          'url' => route('student.id-cards.bulk'),          'match' => ['id-card', 'id-cards']];
                $allowedNavItems[] = ['id' => 'grades',     'label' => 'Grades',         'icon' => 'bi-award-fill',            'url' => route('exam.grade.system.index'),        'match' => ['grade']];
                $allowedNavItems[] = ['id' => 'fees',       'label' => 'Fees',           'icon' => 'bi-receipt-cutoff',        'url' => route('payments.index'),                 'match' => ['invoice', 'payment', 'fee']];
                $allowedNavItems[] = ['id' => 'notices',    'label' => 'Notices',        'icon' => 'bi-megaphone-fill',        'url' => route('notice.index'),                   'match' => ['notice']];
                $allowedNavItems[] = ['id' => 'events',     'label' => 'Calendar',       'icon' => 'bi-calendar-event-fill',   'url' => route('events.show'),                    'match' => ['event', 'calendar']];
                $allowedNavItems[] = ['id' => 'homework',   'label' => 'Homework',       'icon' => 'bi-journal-bookmark-fill', 'url' => route('homework.index'),                  'match' => ['homework']];
                $allowedNavItems[] = ['id' => 'livequiz',   'label' => 'Live Quiz',      'icon' => 'bi-trophy-fill',           'url' => route('live-quiz.index'),                'match' => ['live-quiz', 'quiz']];
                $allowedNavItems[] = ['id' => 'library',    'label' => 'Library',        'icon' => 'bi-book-fill',             'url' => route('library.index'),                  'match' => ['library']];
                $allowedNavItems[] = ['id' => 'transport',  'label' => 'Transport',      'icon' => 'bi-bus-front-fill',        'url' => route('transport.index'),                'match' => ['transport']];
            }

            $allowedNavItems[] = ['id' => 'menu', 'label' => 'Menu', 'icon' => 'bi-grid-3x3-gap-fill', 'action' => 'openSidebar', 'match' => []];
        }
    @endphp

    // Master list of available shortcuts scoped to current user's role & permissions
    const ALL_NAV_ITEMS = {!! json_encode($allowedNavItems) !!};

    const PRESETS = {
        @if(auth()->check() && auth()->user()?->isStudent())
        student: ['home', 'routine', 'homework', 'attendance', 'menu'],
        study:   ['home', 'homework', 'results', 'notices', 'menu'],
        @elseif(auth()->check() && auth()->user()?->isParent())
        parent:  ['home', 'attendance', 'fees', 'notices', 'menu'],
        @elseif(auth()->check() && (auth()->user()?->isTeacher() || auth()->user()?->isClassTeacher()))
        teacher: ['home', 'attendance', 'homework', 'notices', 'menu'],
        classes: ['home', 'classes', 'routine', 'exams', 'menu'],
        @elseif(auth()->check() && auth()->user()?->isAcademicCoord())
        coordinator: ['home', 'classes', 'routine', 'exams', 'menu'],
        teacher:     ['home', 'attendance', 'homework', 'notices', 'menu'],
        @elseif(auth()->check() && auth()->user()?->isAccountant())
        accountant:  ['home', 'fees', 'students', 'notices', 'menu'],
        @else
        coordinator: ['home', 'classes', 'routine', 'exams', 'menu'],
        teacher:     ['home', 'attendance', 'homework', 'notices', 'menu'],
        admin:       ['home', 'students', 'teachers', 'fees', 'menu'],
        accountant:  ['home', 'fees', 'students', 'notices', 'menu'],
        @endif
    };

    let selectedItemIds = [];

    function getSavedItems() {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            if (raw) {
                const parsed = JSON.parse(raw);
                if (Array.isArray(parsed)) {
                    // Filter strictly to items allowed for the current role
                    const validIds = parsed.filter(id => ALL_NAV_ITEMS.some(item => item.id === id));
                    if (validIds.length >= 3 && validIds.length <= 5) {
                        return validIds;
                    }
                }
            }
        } catch(e) {}
        return null;
    }

    function renderBottomNavbar(itemIds) {
        const nav = document.getElementById('mobile-bottom-nav');
        if (!nav) return;

        const currentPath = window.location.pathname.toLowerCase();
        let html = '';

        itemIds.forEach(id => {
            const item = ALL_NAV_ITEMS.find(i => i.id === id);
            if (!item) return;

            let isActive = false;
            if (item.match && item.match.length > 0) {
                isActive = item.match.some(m => currentPath.includes(m.toLowerCase()));
            }

            if (item.action === 'openSidebar') {
                html += `
                    <button type="button" onclick="openSidebar()" class="mobile-nav-item ${isActive ? 'active' : ''}" aria-label="Open navigation menu">
                        <i class="bi ${item.icon}"></i>
                        <span>${item.label}</span>
                    </button>
                `;
            } else {
                html += `
                    <a href="${item.url}" class="mobile-nav-item ${isActive ? 'active' : ''}">
                        <i class="bi ${item.icon}"></i>
                        <span>${item.label}</span>
                    </a>
                `;
            }
        });

        // Add customize button
        html += `
            <button type="button" onclick="openBottomNavCustomizer()" class="mobile-nav-item" title="Customize Bottom Bar" style="opacity: 0.75">
                <i class="bi bi-sliders text-indigo-500"></i>
                <span>Edit</span>
            </button>
        `;

        nav.innerHTML = html;
    }

    function renderCustomizerGrid() {
        const grid = document.getElementById('nav-items-grid');
        if (!grid) return;

        let html = '';
        ALL_NAV_ITEMS.forEach(item => {
            const isChecked = selectedItemIds.includes(item.id);
            html += `
                <div onclick="toggleNavItem('${item.id}')" 
                     class="customizer-tile ${isChecked ? 'is-selected' : ''}"
                     role="checkbox"
                     aria-checked="${isChecked}"
                     tabindex="0">
                    <div class="tile-icon">
                        <i class="bi ${item.icon}"></i>
                    </div>
                    <div class="flex-1 min-w-0 pr-0">
                        <p class="tile-label" title="${item.label}">${item.label}</p>
                    </div>
                    <div class="tile-checkbox">
                        ${isChecked ? '<i class="bi bi-check text-xs font-bold"></i>' : ''}
                    </div>
                </div>
            `;
        });
        grid.innerHTML = html;

        renderLivePreviewDock();

        const countBadge = document.getElementById('nav-selected-count-badge');
        if (countBadge) {
            countBadge.textContent = `${selectedItemIds.length} / 5 Selected`;
            if (selectedItemIds.length < 3 || selectedItemIds.length > 5) {
                countBadge.className = 'px-2.5 py-0.5 rounded-full font-bold text-[11px] bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300 border border-rose-200 dark:border-rose-800';
            } else {
                countBadge.className = 'px-2.5 py-0.5 rounded-full font-bold text-[11px] bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800';
            }
        }
    }

    function renderLivePreviewDock() {
        const previewContainer = document.getElementById('customizer-live-preview-dock');
        if (!previewContainer) return;

        let html = '';
        selectedItemIds.forEach(id => {
            const item = ALL_NAV_ITEMS.find(i => i.id === id);
            if (!item) return;
            html += `
                <div class="flex flex-col items-center justify-center flex-1 px-1 py-1 text-center transition-all">
                    <i class="bi ${item.icon} text-sm text-indigo-600 dark:text-indigo-400"></i>
                    <span class="text-[10px] font-semibold text-slate-700 dark:text-slate-300 truncate max-w-[65px] mt-0.5">${item.label}</span>
                </div>
            `;
        });
        previewContainer.innerHTML = html;
    }

    window.toggleNavItem = function(id) {
        const idx = selectedItemIds.indexOf(id);
        if (idx > -1) {
            if (selectedItemIds.length <= 3) {
                if (typeof showToast === 'function') {
                    showToast('Please keep at least 3 items in the bottom navigation.', 'warning');
                } else {
                    alert('Please keep at least 3 items in the bottom navigation.');
                }
                return;
            }
            selectedItemIds.splice(idx, 1);
        } else {
            if (selectedItemIds.length >= 5) {
                if (typeof showToast === 'function') {
                    showToast('You can select up to 5 items max for the bottom bar.', 'warning');
                } else {
                    alert('You can select up to 5 items max for the bottom bar.');
                }
                return;
            }
            selectedItemIds.push(id);
        }
        renderCustomizerGrid();
    };

    window.applyNavPreset = function(presetKey) {
        if (PRESETS[presetKey]) {
            selectedItemIds = [...PRESETS[presetKey]];
            renderCustomizerGrid();
        }
    };

    window.openBottomNavCustomizer = function() {
        const saved = getSavedItems();
        if (saved) {
            selectedItemIds = [...saved];
        } else {
            @if(auth()->check() && auth()->user()?->isStudent())
            selectedItemIds = ['home', 'routine', 'homework', 'attendance', 'menu'];
            @elseif(auth()->check() && auth()->user()?->isParent())
            selectedItemIds = ['home', 'attendance', 'fees', 'notices', 'menu'];
            @elseif(auth()->check() && auth()->user()?->isAcademicCoord())
            selectedItemIds = ['home', 'classes', 'routine', 'exams', 'menu'];
            @elseif(auth()->check() && (auth()->user()?->isTeacher() || auth()->user()?->isClassTeacher()))
            selectedItemIds = ['home', 'attendance', 'homework', 'notices', 'menu'];
            @elseif(auth()->check() && auth()->user()?->isAccountant())
            selectedItemIds = ['home', 'fees', 'students', 'notices', 'menu'];
            @else
            selectedItemIds = ['home', 'attendance', 'students', 'notices', 'menu'];
            @endif
        }
        renderCustomizerGrid();
        const modal = document.getElementById('bottom-nav-customizer-modal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            const body = document.getElementById('customizer-modal-body');
            if (body) body.scrollTop = 0;
        }
    };

    window.closeBottomNavCustomizer = function() {
        const modal = document.getElementById('bottom-nav-customizer-modal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    };

    window.saveCustomBottomNav = function() {
        if (selectedItemIds.length < 3 || selectedItemIds.length > 5) {
            alert('Please select between 3 and 5 items.');
            return;
        }
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(selectedItemIds));
        } catch(e) {}
        
        renderBottomNavbar(selectedItemIds);
        closeBottomNavCustomizer();

        if (typeof showToast === 'function') {
            showToast('Bottom navigation bar updated successfully! ✨', 'success');
        }
    };

    window.resetBottomNavToDefault = function() {
        try {
            localStorage.removeItem(STORAGE_KEY);
        } catch(e) {}
        window.location.reload();
    };

    // Auto-init on page load
    document.addEventListener('DOMContentLoaded', function() {
        const saved = getSavedItems();
        if (saved) {
            renderBottomNavbar(saved);
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeBottomNavCustomizer();
        }
    });
})();
</script>

<script>
/* ── Bottom Nav: hide on scroll down, show on scroll up ── */
(function () {
    var nav        = document.getElementById('mobile-bottom-nav');
    var lastY      = window.scrollY;
    var ticking    = false;
    var THRESHOLD  = 8;   // px — ignore tiny jitter
    var SHOW_AT    = 60;  // px from bottom — always show near page end

    // Check if page actually has enough content to scroll
    function isPageScrollable() {
        return document.documentElement.scrollHeight > window.innerHeight + 10;
    }

    function onScroll() {
        if (!nav) return;

        // If page is not scrollable, always keep nav visible
        if (!isPageScrollable()) {
            nav.classList.remove('nav-hidden');
            lastY = window.scrollY;
            ticking = false;
            return;
        }

        var y          = window.scrollY;
        var delta      = y - lastY;
        var docH       = document.documentElement.scrollHeight;
        var winH       = window.innerHeight;
        var nearBottom = (y + winH) >= (docH - SHOW_AT);
        var atTop      = y <= 10;

        if (atTop || nearBottom) {
            // Always show at very top or near bottom
            nav.classList.remove('nav-hidden');
        } else if (delta > THRESHOLD) {
            // Scrolling DOWN — hide
            nav.classList.add('nav-hidden');
        } else if (delta < -THRESHOLD) {
            // Scrolling UP — show
            nav.classList.remove('nav-hidden');
        }

        lastY   = y;
        ticking = false;
    }

    window.addEventListener('scroll', function () {
        if (!ticking) {
            window.requestAnimationFrame(onScroll);
            ticking = true;
        }
    }, { passive: true });

    // Re-check scrollability on resize / content change
    window.addEventListener('resize', function () {
        if (nav && !isPageScrollable()) {
            nav.classList.remove('nav-hidden');
        }
    }, { passive: true });

    // Touch swipe support (for momentum scrolling on iOS)
    var touchStartY = 0;
    document.addEventListener('touchstart', function (e) {
        touchStartY = e.touches[0].clientY;
    }, { passive: true });
    document.addEventListener('touchend', function (e) {
        if (!nav) return;
        // Only act on swipe if page is scrollable
        if (!isPageScrollable()) {
            nav.classList.remove('nav-hidden');
            return;
        }
        var diff = touchStartY - e.changedTouches[0].clientY;
        if (diff > THRESHOLD) {
            nav.classList.add('nav-hidden');
        } else if (diff < -THRESHOLD) {
            nav.classList.remove('nav-hidden');
        }
    }, { passive: true });
})();
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

// ── PWA School-Branded Install Prompt Handler ─────────────────
let deferredPwaInstallPrompt = null;
window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPwaInstallPrompt = e;
    window.canInstallPwa = true;
    window.dispatchEvent(new CustomEvent('pwa-install-ready', { detail: { prompt: e } }));
    console.log('[PWA] Install prompt ready for school app:', '{{ addslashes($pwaAppName) }}');
});

window.promptPwaInstall = async function() {
    if (deferredPwaInstallPrompt) {
        deferredPwaInstallPrompt.prompt();
        const choice = await deferredPwaInstallPrompt.userChoice;
        console.log('[PWA] User response to install:', choice.outcome);
        deferredPwaInstallPrompt = null;
    } else {
        const schoolName = '{{ addslashes($pwaAppName) }}';
        if (typeof showToast === 'function') {
            showToast('To install ' + schoolName + ', tap your browser menu (⋮ or Share) and select "Add to Home Screen".', 'info', 'Install App', 6000);
        } else {
            alert('To install ' + schoolName + ', tap your browser menu (⋮ or Share) and select "Add to Home Screen".');
        }
    }
};

window.addEventListener('appinstalled', () => {
    deferredPwaInstallPrompt = null;
    if (typeof showToast === 'function') {
        showToast('{{ addslashes($pwaAppName) }} installed successfully! 🎉', 'success');
    }
});
</script>
@endif

{{-- Module 23: Offline-first PWA Engine --}}
<script src="{{ asset('js/offline-db.js') }}"></script>
<script src="{{ asset('js/offline-seed.js') }}"></script>
<script src="{{ asset('js/attendance-sync.js') }}"></script>
<script src="{{ asset('js/offline-forms.js') }}"></script>
<script src="{{ asset('js/offline-sync.js') }}"></script>

<script>
(function() {
    function updateOnlineStatusUI() {
        const isOnline = navigator.onLine;
        const banner = document.getElementById('global-offline-banner');
        const headerPill = document.getElementById('offline-header-pill');
        const headerText = document.getElementById('offline-header-text');
        const dot = document.getElementById('global-offline-dot');
        const msg = document.getElementById('global-offline-msg');

        if (!isOnline) {
            if (banner) {
                banner.classList.remove('hidden');
                banner.style.background = 'rgba(15,23,42,0.95)';
                if (dot) { dot.className = 'w-2.5 h-2.5 rounded-full bg-rose-500 animate-pulse'; }
                if (msg) { msg.textContent = 'तपाईं अहिले अफलाइन हुनुहुन्छ (Offline Mode) — All actions saved locally.'; }
            }
            if (headerPill) {
                headerPill.classList.remove('hidden');
                headerPill.classList.add('flex');
                headerPill.style.background = '#fef2f2';
                headerPill.style.borderColor = '#fecaca';
                headerPill.style.color = '#b91c1c';
                if (headerText) headerText.textContent = 'अफलाइन (Offline)';
            }
        } else {
            if (banner) {
                banner.style.background = 'rgba(6,78,59,0.95)';
                if (dot) { dot.className = 'w-2.5 h-2.5 rounded-full bg-emerald-400'; }
                if (msg) { msg.textContent = 'अनलाइन भयो (Back Online) — Synchronizing data...'; }
                setTimeout(() => { banner.classList.add('hidden'); }, 3500);
            }
            if (headerPill) {
                headerPill.classList.add('hidden');
                headerPill.classList.remove('flex');
            }
            if (typeof offlineSyncFlush === 'function') {
                offlineSyncFlush();
            }
        }
        if (typeof offlineSyncUpdateBadge === 'function') {
            offlineSyncUpdateBadge();
        }
    }
    window.addEventListener('online', updateOnlineStatusUI);
    window.addEventListener('offline', updateOnlineStatusUI);
    document.addEventListener('DOMContentLoaded', updateOnlineStatusUI);
})();
</script>

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

{{-- Universal Mobile Responsive Tables Engine & Sidebar Cleanup --}}
<script>
window.openSidebar = function() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    if (sidebar) sidebar.classList.remove('-translate-x-full');
    if (overlay) overlay.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
};

window.closeSidebar = function() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    if (sidebar) sidebar.classList.add('-translate-x-full');
    if (overlay) overlay.classList.add('hidden');
    document.body.style.overflow = '';
};

// Clear any saved dual-sidebar mode preference
try { localStorage.removeItem('nsh_sidebar_style'); } catch(e) {}

(function() {
    function makeTablesResponsive() {
        document.querySelectorAll('table').forEach(function(table) {
            if (!table.closest('.overflow-x-auto, .table-responsive, [class*="overflow-x"]')) {
                var wrapper = document.createElement('div');
                wrapper.className = 'table-responsive-wrapper overflow-x-auto w-full max-w-full';
                wrapper.style.webkitOverflowScrolling = 'touch';
                table.parentNode.insertBefore(wrapper, table);
                wrapper.appendChild(table);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', makeTablesResponsive);
    } else {
        makeTablesResponsive();
    }

    window.makeTablesResponsive = makeTablesResponsive;
})();
</script>

</body>
</html>
