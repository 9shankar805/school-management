@extends('layouts.app')
@section('title', 'Sign In')

@section('content')
<div class="min-h-screen flex" style="background:var(--bg-surface)">

    {{-- ── Left panel — Kathmandu background ────────────────────────── --}}
    <div class="hidden lg:flex lg:w-1/2 xl:w-3/5 flex-col justify-between p-12 relative overflow-hidden"
         style="background: url('{{ asset('imgs/kathmandu.png') }}') center center / cover no-repeat;">

        {{-- Dark overlay for readability --}}
        <div class="absolute inset-0" style="background:linear-gradient(135deg,rgba(30,27,75,.75) 0%,rgba(79,70,229,.55) 60%,rgba(37,99,235,.45) 100%)"></div>

        {{-- Top brand mark --}}
        <div class="relative">
            <div class="flex items-center gap-3">
                <img src="{{ asset('imgs/logo_icon.png') }}" alt="{{ config('app.name') }} logo"
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
         style="background: url('{{ asset('imgs/loginbg.png') }}') center center / cover no-repeat;">

        {{-- No overlay — background shows fully --}}

        {{-- Frosted glass card keeps the form readable --}}
        <div class="w-full max-w-md relative rounded-2xl p-8 sm:p-10"
             style="background:rgba(255,255,255,.88);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);box-shadow:0 8px 40px rgba(0,0,0,.18);border:1px solid rgba(255,255,255,.6)">

            {{-- Mobile brand (shown only on small screens) --}}
            <div class="flex items-center gap-2.5 mb-8 lg:hidden">
                <img src="{{ asset('imgs/logo_icon.png') }}" alt="{{ config('app.name') }} logo"
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
                <button type="submit"
                        class="w-full btn-primary justify-center py-2.5 text-sm font-semibold mt-2">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Sign In
                </button>
            </form>

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
@endpush
