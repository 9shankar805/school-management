@php
    $school = $school ?? $currentSchool ?? config('tenant.school');
    $schoolName = $school ? $school->name : config('app.name', 'NepalSchoolHub ERP');
    $schoolLogo = $school ? $school->logo_url : asset('imgs/logo_icon.webp');
    $primaryColor = $school ? ($school->primary_color_resolved ?: '#4f46e5') : '#4f46e5';
    $secondaryColor = $school ? ($school->secondary_color_resolved ?: '#f59e0b') : '#f59e0b';

    // Dynamic left hero backdrop
    $heroBg = asset('imgs/kathmandu.webp');
    if ($school) {
        $banners = $school->slider_banners_list;
        if (!empty($banners[0]['image'])) {
            $img = $banners[0]['image'];
            $heroBg = (!empty($banners[0]['is_asset']) || str_starts_with($img, 'imgs/')) ? asset($img) : asset('storage/' . ltrim(str_replace('storage/', '', $img), '/'));
        } elseif (!empty($school->gallery_images) && is_array($school->gallery_images) && count($school->gallery_images) > 0) {
            $heroBg = asset('storage/' . ltrim(str_replace('storage/', '', $school->gallery_images[0]), '/'));
        }
    }
@endphp

@extends('layouts.app')
@section('title', $school ? 'Sign In · ' . $school->name : 'Sign In · ' . config('app.name', 'School ERP'))

@section('content')
<div class="min-h-screen flex" style="background:var(--bg-surface)">

    {{-- ── Left panel — School / SaaS dynamic branding banner ───────── --}}
    <div class="hidden lg:flex lg:w-1/2 xl:w-3/5 flex-col justify-between p-12 relative overflow-hidden"
         style="background: linear-gradient(135deg, rgba(15,23,42,0.92) 0%, rgba(30,27,75,0.85) 50%, rgba(15,23,42,0.94) 100%), url('{{ $heroBg }}') center center / cover no-repeat;">

        {{-- Subtle ambient glow --}}
        <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full blur-3xl pointer-events-none opacity-40" style="background: {{ $secondaryColor }};"></div>

        {{-- Top brand mark & Public Website Link --}}
        <div class="relative flex items-center justify-between gap-4 z-10">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl p-1.5 flex items-center justify-center shadow-lg backdrop-blur-md border border-white/30"
                     style="background: rgba(255,255,255,0.95);">
                    <img src="{{ $schoolLogo }}" alt="{{ $schoolName }} logo"
                         class="w-full h-full object-contain rounded-xl">
                </div>
                <div>
                    <span class="block leading-snug tracking-tight font-extrabold"
                          style="color: #ffffff !important; font-size: 1.15rem; text-shadow: 0 2px 8px rgba(0,0,0,0.6);">
                        {{ $schoolName }}
                    </span>
                    @if($school)
                        <span class="text-xs font-medium flex items-center gap-1.5 mt-0.5"
                              style="color: rgba(255,255,255,0.92) !important; text-shadow: 0 1px 4px rgba(0,0,0,0.6);">
                            <i class="bi bi-geo-alt-fill text-[11px]" style="color: #fbbf24 !important;"></i>
                            {{ $school->city ? $school->city . ($school->province ? ', ' . $school->province : '') : ($school->address ?: 'Official Campus Portal') }}
                        </span>
                    @else
                        <span class="text-xs font-medium" style="color: rgba(255,255,255,0.92) !important; text-shadow: 0 1px 4px rgba(0,0,0,0.6);">
                            Cloud ERP & Smart Learning Suite
                        </span>
                    @endif
                </div>
            </div>

            @if($school && $school->subdomain_url)
                <a href="{{ $school->subdomain_url }}"
                   style="background: rgba(255,255,255,0.18) !important; color: #ffffff !important; border: 1px solid rgba(255,255,255,0.3) !important; backdrop-filter: blur(8px); text-shadow: 0 1px 3px rgba(0,0,0,0.4);"
                   class="inline-flex items-center gap-2 text-xs font-semibold px-3.5 py-2 rounded-xl transition hover:brightness-125 shadow-sm">
                    <i class="bi bi-arrow-left"></i>
                    <span>Visit School Website</span>
                </a>
            @else
                <a href="{{ url('/') }}"
                   style="background: rgba(255,255,255,0.18) !important; color: #ffffff !important; border: 1px solid rgba(255,255,255,0.3) !important; backdrop-filter: blur(8px); text-shadow: 0 1px 3px rgba(0,0,0,0.4);"
                   class="inline-flex items-center gap-2 text-xs font-semibold px-3.5 py-2 rounded-xl transition hover:brightness-125 shadow-sm">
                    <i class="bi bi-arrow-left"></i>
                    <span>Back to Home</span>
                </a>
            @endif
        </div>

        {{-- Centre copy --}}
        <div class="relative space-y-6 z-10 my-auto py-8">
            @if($school)
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold backdrop-blur-md shadow-sm"
                     style="background: rgba(255,255,255,0.18) !important; color: #ffffff !important; border: 1px solid rgba(255,255,255,0.3) !important;">
                    <i class="bi bi-patch-check-fill" style="color: #fbbf24 !important;"></i>
                    <span style="color: #ffffff !important; font-weight: 600;">Official Institutional Portal</span>
                    @if($school->iemis_code)
                        <span style="color: rgba(255,255,255,0.6) !important;">&bull;</span>
                        <span class="font-mono text-[11px]" style="color: #ffffff !important; font-weight: 700;">IEMIS #{{ $school->iemis_code }}</span>
                    @endif
                </div>

                <h1 class="leading-tight tracking-tight font-black"
                    style="color: #ffffff !important; font-size: 2.6rem; text-shadow: 0 3px 18px rgba(0,0,0,0.65);">
                    {{ $school->name }}<br>
                    <span style="color: rgba(255,255,255,0.95) !important; font-size: 1.65rem; font-weight: 600; text-shadow: 0 2px 12px rgba(0,0,0,0.6);">
                        Unified Learning & Academic Portal
                    </span>
                </h1>

                <p class="text-base leading-relaxed max-w-xl font-normal"
                   style="color: rgba(255, 255, 255, 0.95) !important; text-shadow: 0 2px 8px rgba(0,0,0,0.6) !important;">
                    {{ $school->description ?: 'Access student grades, attendance records, exam schedules, online fee payments, course syllabi, and administrative services from a single secure dashboard.' }}
                </p>

                {{-- Feature pills --}}
                <div class="flex flex-wrap gap-2 pt-2">
                    @foreach(['Student Portal', 'Teacher LMS', 'Parent Access', 'Live Attendance', 'Exam Results', 'Fee Ledger', 'Digital Library', 'School Notices'] as $f)
                    <span class="text-xs font-semibold px-3.5 py-1.5 rounded-full shadow-sm"
                          style="background: rgba(255,255,255,0.18) !important; color: #ffffff !important; backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.35) !important; text-shadow: 0 1px 3px rgba(0,0,0,0.5);">
                        {{ $f }}
                    </span>
                    @endforeach
                </div>
            @else
                <h1 class="font-bold leading-tight" style="color: #ffffff !important; font-size: 2.5rem; text-shadow: 0 2px 12px rgba(0,0,0,0.6);">
                    Everything your school needs,<br>
                    <span style="color: rgba(255,255,255,0.9) !important;">in one place.</span>
                </h1>
                <p class="text-base leading-relaxed max-w-md font-normal"
                   style="color: rgba(255, 255, 255, 0.95) !important; text-shadow: 0 1px 6px rgba(0,0,0,0.6) !important;">
                    Manage students, teachers, exams, finance, hostel, transport, and more —
                    all from a single modern dashboard.
                </p>

                {{-- Feature pills --}}
                <div class="flex flex-wrap gap-2 pt-2">
                    @foreach(['Students','Attendance','Exams','Finance','Library','Transport','Hostel','AI Tools'] as $f)
                    <span class="text-xs font-semibold px-3.5 py-1.5 rounded-full shadow-sm"
                          style="background: rgba(255,255,255,0.18) !important; color: #ffffff !important; backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.35) !important; text-shadow: 0 1px 3px rgba(0,0,0,0.5);">
                        {{ $f }}
                    </span>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Bottom stats & Trust badges --}}
        <div class="relative flex gap-8 z-10 pt-5 border-t" style="border-color: rgba(255,255,255,0.25) !important;">
            @if($school)
                <div>
                    <p class="font-black text-2xl" style="color: #ffffff !important; text-shadow: 0 2px 8px rgba(0,0,0,0.6) !important;">
                        {{ $school->iemis_code ?: 'Registered' }}
                    </p>
                    <p class="text-xs font-semibold mt-0.5" style="color: rgba(255,255,255,0.9) !important; text-shadow: 0 1px 4px rgba(0,0,0,0.6) !important;">
                        IEMIS Accreditation
                    </p>
                </div>
                <div>
                    <p class="font-black text-2xl" style="color: #ffffff !important; text-shadow: 0 2px 8px rgba(0,0,0,0.6) !important;">
                        {{ $school->district ?: 'Nepal' }}
                    </p>
                    <p class="text-xs font-semibold mt-0.5" style="color: rgba(255,255,255,0.9) !important; text-shadow: 0 1px 4px rgba(0,0,0,0.6) !important;">
                        Campus District
                    </p>
                </div>
                <div>
                    <p class="font-black text-2xl" style="color: #ffffff !important; text-shadow: 0 2px 8px rgba(0,0,0,0.6) !important;">
                        256-bit
                    </p>
                    <p class="text-xs font-semibold mt-0.5" style="color: rgba(255,255,255,0.9) !important; text-shadow: 0 1px 4px rgba(0,0,0,0.6) !important;">
                        SSL Encryption
                    </p>
                </div>
            @else
                <div>
                    <p class="font-black text-2xl" style="color: #ffffff !important; text-shadow: 0 2px 8px rgba(0,0,0,0.6) !important;">24+</p>
                    <p class="text-xs font-semibold mt-0.5" style="color: rgba(255,255,255,0.9) !important; text-shadow: 0 1px 4px rgba(0,0,0,0.6) !important;">Modules</p>
                </div>
                <div>
                    <p class="font-black text-2xl" style="color: #ffffff !important; text-shadow: 0 2px 8px rgba(0,0,0,0.6) !important;">20+</p>
                    <p class="text-xs font-semibold mt-0.5" style="color: rgba(255,255,255,0.9) !important; text-shadow: 0 1px 4px rgba(0,0,0,0.6) !important;">Role types</p>
                </div>
                <div>
                    <p class="font-black text-2xl" style="color: #ffffff !important; text-shadow: 0 2px 8px rgba(0,0,0,0.6) !important;">100%</p>
                    <p class="text-xs font-semibold mt-0.5" style="color: rgba(255,255,255,0.9) !important; text-shadow: 0 1px 4px rgba(0,0,0,0.6) !important;">Web-based</p>
                </div>
            @endif
        </div>
    </div>

    {{-- ── Right panel — login form ──────────────────────────────────── --}}
    <div class="flex-1 flex items-center justify-center p-6 sm:p-12 relative overflow-hidden"
         style="background: url('{{ asset('imgs/loginbg.webp') }}') center center / cover no-repeat;">

        <div class="absolute inset-0 bg-slate-900/5 dark:bg-slate-950/60 pointer-events-none transition-colors"></div>

            {{-- Frosted glass card keeps the form readable --}}
            <div class="w-full max-w-md relative rounded-3xl p-8 sm:p-10 auth-glass-card shadow-2xl border border-white/60 dark:border-slate-800/80">

                {{-- School Identity Card Header (Desktop & Mobile) --}}
                @if($school)
                <div class="flex items-center gap-3.5 mb-6 p-3 rounded-2xl bg-indigo-50/80 dark:bg-slate-800/80 border border-indigo-100 dark:border-slate-700/80 shadow-sm backdrop-blur-xs">
                    <div class="w-11 h-11 rounded-xl bg-white dark:bg-slate-900 p-1 shadow-xs border border-slate-200 dark:border-slate-700 flex items-center justify-center flex-shrink-0">
                        <img src="{{ $schoolLogo }}" alt="{{ $schoolName }} logo" class="w-full h-full object-contain rounded-lg">
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5">
                            <h3 class="font-bold text-sm text-slate-900 dark:text-white truncate">{{ $schoolName }}</h3>
                            <i class="bi bi-patch-check-fill text-indigo-600 dark:text-indigo-400 text-xs flex-shrink-0" title="Verified School"></i>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-300 font-medium truncate mt-0.5">
                            {{ $school->city ? $school->city . ' · ' : '' }}Official Login Portal
                        </p>
                    </div>
                </div>
                @else
                <div class="flex items-center gap-3.5 mb-6 p-3 rounded-2xl bg-indigo-50/80 dark:bg-slate-800/80 border border-indigo-100 dark:border-slate-700/80 shadow-sm backdrop-blur-xs">
                    <div class="w-11 h-11 rounded-xl bg-white dark:bg-slate-900 p-1 shadow-xs border border-slate-200 dark:border-slate-700 flex items-center justify-center flex-shrink-0">
                        <img src="{{ $schoolLogo }}" alt="{{ $schoolName }} logo" class="w-full h-full object-contain rounded-lg">
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="font-bold text-sm text-slate-900 dark:text-white truncate">{{ $schoolName }}</h3>
                        <p class="text-xs text-slate-600 dark:text-slate-300 font-medium truncate mt-0.5">Cloud ERP & Smart LMS</p>
                    </div>
                </div>
                @endif

                {{-- Heading --}}
                <div class="mb-6">
                    <h2 class="font-bold text-2xl mb-1 text-slate-900 dark:text-white">
                        {{ $school ? 'Sign In to Portal' : 'Welcome back' }}
                    </h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ $school ? 'Enter your credentials to access ' . $schoolName : 'Sign in to your account to continue' }}
                    </p>
                </div>

                {{-- Error messages --}}
                @if($errors->any())
                <div class="alert-modern alert-error mb-6">
                    <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
                    <div>
                        @foreach($errors->all() as $error)
                        <p class="text-xs">{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Login form --}}
                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    {{-- Email --}}
                    <div class="form-group-modern">
                        <label for="email" class="label-modern text-slate-700 dark:text-slate-300 font-medium text-xs mb-1.5 block">Email or Username</label>
                        <div class="relative flex items-center">
                            <i class="bi bi-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400 pointer-events-none z-10"></i>
                            <input id="email"
                                   type="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   required
                                   autofocus
                                   autocomplete="email"
                                   placeholder="{{ 'you@' . ($school ? ($school->subdomain . '.edu') : 'school.edu') }}"
                                   style="padding-left: 2.75rem !important;"
                                   class="input-modern {{ $errors->has('email') ? 'border-red-400' : '' }}">
                        </div>
                    </div>

                    {{-- Password --}}
                    <div class="form-group-modern">
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="label-modern mb-0 text-slate-700 dark:text-slate-300 font-medium text-xs">Password</label>
                            @if(Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                               class="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                                Forgot password?
                            </a>
                            @endif
                        </div>
                        <div class="relative flex items-center">
                            <i class="bi bi-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400 pointer-events-none z-10"></i>
                            <input id="password"
                                   type="password"
                                   name="password"
                                   required
                                   autocomplete="current-password"
                                   placeholder="••••••••"
                                   style="padding-left: 2.75rem !important; padding-right: 2.75rem !important;"
                                   class="input-modern {{ $errors->has('password') ? 'border-red-400' : '' }}">
                            <button type="button" id="pw-toggle"
                                    class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition p-1.5 z-10 bg-transparent border-0 outline-none shadow-none cursor-pointer flex items-center justify-center"
                                    style="background:transparent !important; border:none !important; min-height:auto !important; outline:none !important; box-shadow:none !important;"
                                    onclick="togglePassword()"
                                    aria-label="Show/hide password">
                                <i class="bi bi-eye text-sm" id="pw-eye"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Remember me --}}
                    <div class="flex items-center gap-2.5 pt-1">
                        <input type="checkbox" id="remember" name="remember"
                               {{ old('remember') ? 'checked' : '' }}
                               class="w-4 h-4 rounded accent-indigo-600 cursor-pointer">
                        <label for="remember" class="text-sm cursor-pointer select-none text-slate-700 dark:text-slate-300 font-medium">
                            Keep me signed in
                        </label>
                    </div>

                    {{-- Submit --}}
                    @if(config('services.recaptcha.site_key'))
                    <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response-login">
                    @endif
                    <button type="submit"
                            id="login-submit-btn"
                            style="background: {{ $primaryColor }}; border-color: {{ $primaryColor }};"
                            class="w-full btn-primary justify-center py-2.5 text-sm font-semibold mt-2 shadow-md hover:brightness-110 transition-all">
                        <i class="bi bi-box-arrow-in-right"></i>
                        Sign In
                    </button>

                    {{-- QR Code Scan Login --}}
                    <div class="relative flex items-center justify-center my-3.5">
                        <div class="border-t border-slate-200 dark:border-slate-700/80 w-full"></div>
                        <span class="px-2.5 py-0.5 text-[10px] uppercase font-bold text-slate-500 dark:text-slate-400 absolute bg-white dark:bg-slate-900 rounded-md border border-slate-100 dark:border-slate-800">OR</span>
                    </div>

                    <button type="button"
                            onclick="openQrModal()"
                            class="w-full border border-indigo-200 dark:border-slate-700 bg-indigo-50/80 dark:bg-slate-800/80 hover:bg-indigo-100 dark:hover:bg-slate-700 text-indigo-700 dark:text-indigo-300 font-semibold py-2.5 rounded-xl text-xs flex items-center justify-center gap-2 transition-all cursor-pointer shadow-xs">
                        <i class="bi bi-qr-code-scan text-base text-indigo-600 dark:text-indigo-400"></i>
                        <span>Scan {{ $school ? ($school->name . ' ') : '' }}ID Card QR Code</span>
                    </button>
                </form>

            {{-- QR Scanner Modal --}}
            <div id="qrScanModal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4 hidden">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 max-w-md w-full shadow-2xl relative">
                    <button type="button" onclick="closeQrModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 text-lg">
                        <i class="bi bi-x-lg"></i>
                    </button>
                    
                    <div class="text-center mb-4">
                        <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 rounded-2xl flex items-center justify-center mx-auto mb-2 text-xl font-bold">
                            <i class="bi bi-qr-code-scan"></i>
                        </div>
                        <h3 class="font-bold text-base text-slate-800 dark:text-slate-100">Scan ID Card QR Code</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            Hold the backside of your {{ $schoolName }} ID Card up to your camera to sign in.
                        </p>
                    </div>

                    {{-- Camera Container --}}
                    <div id="qr-reader" class="rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800 bg-black min-h-[220px]"></div>

                    <div id="qrScanStatus" class="mt-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-300 min-h-[20px]">
                        Initializing camera...
                    </div>
                </div>
            </div>

            <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
            <script>
                let html5QrScanner = null;

                function openQrModal() {
                    document.getElementById('qrScanModal').classList.remove('hidden');
                    document.getElementById('qrScanStatus').innerHTML = '<span class="text-slate-500">Initializing camera...</span>';

                    if (!html5QrScanner) {
                        html5QrScanner = new Html5Qrcode("qr-reader");
                    }

                    html5QrScanner.start(
                        { facingMode: "environment" },
                        { fps: 10, qrbox: { width: 200, height: 200 } },
                        onQrCodeSuccess,
                        onQrCodeError
                    ).catch(err => {
                        document.getElementById('qrScanStatus').innerHTML = '<span class="text-red-500 font-bold">Camera permission denied or unavailable.</span>';
                    });
                }

                function closeQrModal() {
                    document.getElementById('qrScanModal').classList.add('hidden');
                    if (html5QrScanner && html5QrScanner.isScanning) {
                        html5QrScanner.stop().catch(() => {});
                    }
                }

                function onQrCodeSuccess(decodedText, decodedResult) {
                    if (html5QrScanner && html5QrScanner.isScanning) {
                        html5QrScanner.stop().catch(() => {});
                    }

                    document.getElementById('qrScanStatus').innerHTML = '<span class="text-emerald-500 font-bold">✓ QR Scanned! Authenticating...</span>';

                    fetch("{{ route('login.qr-scan') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({ qr_code: decodedText })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('qrScanStatus').innerHTML = `<span class="text-emerald-500 font-bold">✓ Welcome ${data.student?.name || 'User'}! Redirecting...</span>`;
                            window.location.href = data.redirect;
                        } else {
                            document.getElementById('qrScanStatus').innerHTML = `<span class="text-red-500 font-bold">${data.message || 'QR Authentication failed.'}</span>`;
                            setTimeout(openQrModal, 2000);
                        }
                    })
                    .catch(err => {
                        document.getElementById('qrScanStatus').innerHTML = '<span class="text-red-500 font-bold">Server connection error.</span>';
                    });
                }

                function onQrCodeError(errorMessage) {
                    // Ignored continuous scanning errors
                }
            </script>

            {{-- Footer note --}}
            <p class="text-center text-xs mt-8" style="color:var(--text-muted)">
                @if($school)
                    &copy; {{ date('Y') }} {{ $school->name }} &middot; Powered by {{ config('app.name', 'NepalSchoolHub') }}
                @else
                    &copy; {{ date('Y') }} {{ config('app.name', 'NepalSchoolHub') }} &middot; Tech Procod Pvt Ltd
                @endif
            </p>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function togglePassword() {
    var input = document.getElementById('password');
    var icon  = document.getElementById('pw-eye');
    var show  = input.type === 'password';
    input.type = show ? 'text' : 'password';
    icon.className = show ? 'bi bi-eye-slash text-sm' : 'bi bi-eye text-sm';
}
</script>

@if(config('services.recaptcha.site_key'))
{{-- reCAPTCHA v3 — invisible, auto-runs on form submit --}}
<script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
<script>
(function() {
    const form     = document.querySelector('form[action="{{ route('login') }}"]');
    const siteKey  = '{{ config('services.recaptcha.site_key') }}';
    const tokenField = document.getElementById('g-recaptcha-response-login');
    const btn      = document.getElementById('login-submit-btn');

    if (!form || !tokenField) return;

    form.addEventListener('submit', function(e) {
        // If token already filled (retry after grecaptcha loaded), proceed
        if (tokenField.value) return;

        // If grecaptcha not loaded yet (e.g. slow network), just submit without token
        if (typeof grecaptcha === 'undefined') {
            console.warn('[reCAPTCHA] Not loaded — submitting without token.');
            return;
        }

        e.preventDefault();

        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Verifying...'; }

        grecaptcha.ready(function() {
            grecaptcha.execute(siteKey, { action: 'login' })
                .then(function(token) {
                    tokenField.value = token;
                    if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-box-arrow-in-right"></i> Sign In'; }
                    form.submit();
                })
                .catch(function(err) {
                    console.warn('[reCAPTCHA] Execute error:', err);
                    if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-box-arrow-in-right"></i> Sign In'; }
                    form.submit(); // fail open — don't block login
                });
        });
    });
})();
</script>
@endif
@endpush
