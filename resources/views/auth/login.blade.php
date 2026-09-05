@extends('layouts.app')
@section('title', 'Sign In')

@section('content')
<div class="min-h-screen flex" style="background:var(--bg-surface)">

    {{-- ── Left panel — Kathmandu background ────────────────────────── --}}
    <div class="hidden lg:flex lg:w-1/2 xl:w-3/5 flex-col justify-between p-12 relative overflow-hidden"
         style="background: url('{{ asset('imgs/kathmandu.webp') }}') center center / cover no-repeat;">

        {{-- Dark overlay for readability --}}
        <div class="absolute inset-0" style="background:linear-gradient(135deg,rgba(30,27,75,.75) 0%,rgba(79,70,229,.55) 60%,rgba(37,99,235,.45) 100%)"></div>

        {{-- Top brand mark --}}
        <div class="relative">
            <div class="flex items-center gap-3">
                <img src="{{ asset('imgs/logo_icon.webp') }}?v={{ time() }}" alt="{{ config('app.name') }} logo"
                     class="w-10 h-10 rounded-xl object-contain"
                     style="background:rgba(255,255,255,.2);backdrop-filter:blur(6px);padding:4px">
                <span class="text-white font-bold text-lg">{{ config('app.name', 'School ERP') }}</span>
            </div>
        </div>

        {{-- Centre copy --}}
        <div class="relative space-y-6">
            <h1 class="text-white font-bold leading-tight" style="font-size:2.5rem;text-shadow:0 2px 12px rgba(0,0,0,.4)">
                Everything your school needs,<br>
                <span style="color:rgba(255,255,255,.8)">in one place.</span>
            </h1>
            <p class="text-base leading-relaxed" style="color:rgba(255,255,255,.75);max-width:26rem;text-shadow:0 1px 6px rgba(0,0,0,.3)">
                Manage students, teachers, exams, finance, hostel, transport, and more —
                all from a single modern dashboard.
            </p>

            {{-- Feature pills --}}
            <div class="flex flex-wrap gap-2 pt-2">
                @foreach(['Students','Attendance','Exams','Finance','Library','Transport','Hostel','AI Tools'] as $f)
                <span class="text-xs font-medium px-3 py-1.5 rounded-full"
                      style="background:rgba(255,255,255,.18);color:rgba(255,255,255,.95);backdrop-filter:blur(4px);border:1px solid rgba(255,255,255,.15)">
                    {{ $f }}
                </span>
                @endforeach
            </div>
        </div>

        {{-- Bottom stats --}}
        <div class="relative flex gap-8">
            <div>
                <p class="text-white font-bold text-2xl" style="text-shadow:0 1px 6px rgba(0,0,0,.3)">24+</p>
                <p class="text-xs" style="color:rgba(255,255,255,.65)">Modules</p>
            </div>
            <div>
                <p class="text-white font-bold text-2xl" style="text-shadow:0 1px 6px rgba(0,0,0,.3)">20+</p>
                <p class="text-xs" style="color:rgba(255,255,255,.65)">Role types</p>
            </div>
            <div>
                <p class="text-white font-bold text-2xl" style="text-shadow:0 1px 6px rgba(0,0,0,.3)">100%</p>
                <p class="text-xs" style="color:rgba(255,255,255,.65)">Web-based</p>
            </div>
        </div>
    </div>

    {{-- ── Right panel — login form ──────────────────────────────────── --}}
    <div class="flex-1 flex items-center justify-center p-6 sm:p-12 relative overflow-hidden"
         style="background: url('{{ asset('imgs/loginbg.webp') }}') center center / cover no-repeat;">

        {{-- No overlay — background shows fully --}}

        {{-- Frosted glass card keeps the form readable --}}
        <div class="w-full max-w-md relative rounded-2xl p-8 sm:p-10"
             style="background:rgba(255,255,255,.88);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);box-shadow:0 8px 40px rgba(0,0,0,.18);border:1px solid rgba(255,255,255,.6)">

            {{-- Mobile brand (shown only on small screens) --}}
            <div class="flex items-center gap-2.5 mb-8 lg:hidden">
                <img src="{{ asset('imgs/logo_icon.webp') }}" alt="{{ config('app.name') }} logo"
                     class="w-9 h-9 rounded-lg object-contain bg-indigo-600"
                     style="padding:4px">
                <span class="font-bold text-base" style="color:var(--text-heading)">
                    {{ config('app.name', 'School ERP') }}
                </span>
            </div>

            {{-- Heading --}}
            <div class="mb-8">
                <h2 class="font-bold text-2xl mb-1" style="color:var(--text-heading)">Welcome back</h2>
                <p class="text-sm" style="color:var(--text-muted)">Sign in to your account to continue</p>
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
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                {{-- Email --}}
                <div class="form-group-modern">
                    <label for="email" class="label-modern">Email address</label>
                    <div class="relative flex items-center">
                        <i class="bi bi-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400 pointer-events-none z-10"></i>
                        <input id="email"
                               type="email"
                               name="email"
                               value="{{ old('email') }}"
                               required
                               autofocus
                               autocomplete="email"
                               placeholder="you@school.edu"
                               style="padding-left: 2.75rem !important;"
                               class="input-modern {{ $errors->has('email') ? 'border-red-400' : '' }}">
                    </div>
                </div>

                {{-- Password --}}
                <div class="form-group-modern">
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="label-modern mb-0">Password</label>
                        @if(Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                           class="text-xs hover:underline" style="color:var(--color-primary)">
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
                                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition p-1 z-10"
                                onclick="togglePassword()"
                                aria-label="Show/hide password">
                            <i class="bi bi-eye text-sm" id="pw-eye"></i>
                        </button>
                    </div>
                </div>

                {{-- Remember me --}}
                <div class="flex items-center gap-2.5">
                    <input type="checkbox" id="remember" name="remember"
                           {{ old('remember') ? 'checked' : '' }}
                           class="w-4 h-4 rounded accent-indigo-600 cursor-pointer">
                    <label for="remember" class="text-sm cursor-pointer select-none"
                           style="color:var(--text-body)">
                        Keep me signed in
                    </label>
                </div>

                {{-- Submit --}}
                @if(config('services.recaptcha.site_key'))
                <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response-login">
                @endif
                <button type="submit"
                        id="login-submit-btn"
                        class="w-full btn-primary justify-center py-2.5 text-sm font-semibold mt-2">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Sign In
                </button>

                {{-- QR Code Scan Login --}}
                <div class="relative flex items-center justify-center my-3">
                    <div class="border-t border-slate-200 dark:border-slate-700 w-full"></div>
                    <span class="bg-white dark:bg-slate-900 px-3 text-[10px] uppercase font-bold text-slate-400 absolute">OR</span>
                </div>

                <button type="button"
                        onclick="openQrModal()"
                        class="w-full border border-indigo-200 dark:border-indigo-800/60 bg-indigo-50/50 dark:bg-indigo-950/30 hover:bg-indigo-100/60 text-indigo-700 dark:text-indigo-300 font-semibold py-2.5 rounded-xl text-xs flex items-center justify-center gap-2 transition-all">
                    <i class="bi bi-qr-code-scan text-base text-indigo-600 dark:text-indigo-400"></i>
                    Scan ID Card QR Code to Sign In
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
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Hold the backside of your Student ID Card up to your camera to sign in.</p>
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
                            document.getElementById('qrScanStatus').innerHTML = `<span class="text-emerald-500 font-bold">✓ Welcome ${data.student.name}! Redirecting...</span>`;
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
                &copy; {{ date('Y') }} {{ config('app.name') }} &middot; Tech Procod Pvt Ltd
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
