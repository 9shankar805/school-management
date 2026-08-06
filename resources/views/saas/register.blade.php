<!doctype html>
<html lang="en" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register Your School — NepalSchoolHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body style="background:var(--bg-surface);font-family:'Inter',sans-serif">

<div class="min-h-screen flex" style="height:100vh;overflow:hidden">

    {{-- ── Left branding panel ──────────────────────────────────────── --}}
    <div class="hidden lg:flex lg:w-2/5 flex-col justify-between p-12 relative overflow-hidden"
         style="background: url('{{ asset('imgs/kathmandu.png') }}') center center / cover no-repeat;">

        {{-- Dark overlay for readability --}}
        <div class="absolute inset-0" style="background:linear-gradient(135deg,rgba(30,27,75,.75) 0%,rgba(79,70,229,.55) 60%,rgba(37,99,235,.45) 100%)"></div>

        <div class="relative">
            <a href="{{ route('saas.landing') }}" class="flex items-center gap-2.5 no-underline">
                <img src="{{ asset('imgs/logo_icon.png') }}" alt="NepalSchoolHub logo"
                     class="w-10 h-10 rounded-xl object-contain"
                     style="background:rgba(255,255,255,.2);backdrop-filter:blur(6px);padding:4px">
                <span class="font-bold text-white text-base">NepalSchoolHub</span>
            </a>
        </div>
        <div class="relative space-y-5">
            <h2 class="font-bold text-white leading-snug" style="font-size:1.75rem;text-shadow:0 2px 12px rgba(0,0,0,.4)">
                Start managing your school smarter today
            </h2>
            <p style="color:rgba(255,255,255,.75)" class="text-sm leading-relaxed" style="text-shadow:0 1px 6px rgba(0,0,0,.3)">
                Join schools across Nepal using NepalSchoolHub to streamline admissions, exams, fees, attendance and more.
            </p>
            @foreach(['Free plan — no credit card needed','14-day trial on all paid plans','Setup in under 5 minutes','Dedicated support for Nepali schools'] as $p)
            <div class="flex items-center gap-2.5 text-sm" style="color:rgba(255,255,255,.9)">
                <i class="bi bi-check-circle-fill" style="color:#86efac;flex-shrink:0"></i>{{ $p }}
            </div>
            @endforeach
        </div>
        <div class="relative">
            <p class="text-xs" style="color:rgba(255,255,255,.4)">&copy; {{ date('Y') }} NepalSchoolHub. All rights reserved.</p>
        </div>
    </div>

    {{-- ── Right form panel ─────────────────────────────────────────── --}}
    <div class="flex-1 overflow-y-auto relative"
         style="background: url('{{ asset('imgs/loginbg.png') }}') center center / cover no-repeat;">

        {{-- Inner scroll container centred --}}
        <div class="min-h-full flex items-start justify-center p-6 sm:p-10">

        {{-- Frosted glass card --}}
        <div class="w-full max-w-lg my-auto rounded-2xl px-8 py-8 sm:px-10"
             style="background:rgba(255,255,255,.88);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);box-shadow:0 8px 40px rgba(0,0,0,.18);border:1px solid rgba(255,255,255,.6)">

            {{-- Mobile brand --}}
            <div class="flex items-center gap-2 mb-8 lg:hidden">
                <img src="{{ asset('imgs/logo_icon.png') }}" alt="NepalSchoolHub logo"
                     class="w-9 h-9 rounded-xl object-contain bg-indigo-600"
                     style="padding:4px">
                <span class="font-bold text-base" style="color:var(--text-heading)">NepalSchoolHub</span>
            </div>

            <h1 class="font-bold text-2xl mb-1" style="color:var(--text-heading)">Register your school</h1>
            <p class="text-sm mb-8" style="color:var(--text-muted)">Takes about 3 minutes. Free plan available immediately.</p>

            {{-- Validation errors --}}
            @if(isset($errors) && $errors->any())
            <div class="alert-modern alert-error mb-6">
                <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
                <ul class="list-disc list-inside text-xs space-y-0.5">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('saas.register.store') }}" class="space-y-5">
                @csrf

                {{-- ── Plan selector ── --}}
                <div class="form-group-modern">
                    <label class="label-modern">Choose a plan</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($plans as $key => $plan)
                        <label class="cursor-pointer">
                            <input type="radio" name="plan" value="{{ $key }}" class="sr-only peer"
                                   {{ (old('plan', 'free') === $key) ? 'checked' : '' }}>
                            <div class="border-2 rounded-xl p-3 transition peer-checked:border-indigo-500 peer-checked:bg-indigo-50"
                                 style="border-color:var(--border-card)">
                                <p class="font-semibold text-sm" style="color:var(--text-heading)">
                                    {{ ucfirst($key) }}
                                    @if($key==='growth')<span class="ml-1 text-[10px] bg-indigo-600 text-white px-1.5 py-0.5 rounded-full">Popular</span>@endif
                                </p>
                                <p class="text-xs mt-0.5" style="color:var(--text-muted)">
                                    @if($plan['price_npr']===0) Free @else NPR {{ number_format($plan['price_npr']) }}/mo @endif
                                </p>
                                <p class="text-[10px] mt-1" style="color:var(--text-muted)">
                                    {{ number_format($plan['students']) }} students
                                </p>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="divider"></div>

                {{-- ── School info ── --}}
                <p class="text-xs font-bold uppercase tracking-widest" style="color:var(--text-muted)">School Details</p>

                <div class="form-group-modern">
                    <label class="label-modern" for="school_name">School name <span class="text-rose-500">*</span></label>
                    <input id="school_name" type="text" name="school_name" value="{{ old('school_name') }}"
                           class="input-modern" placeholder="e.g. Shree Janasewa Secondary School" required>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="form-group-modern">
                        <label class="label-modern" for="district">District <span class="text-rose-500">*</span></label>
                        <input id="district" type="text" name="district" value="{{ old('district') }}"
                               class="input-modern" placeholder="Kathmandu" required>
                    </div>
                    <div class="form-group-modern">
                        <label class="label-modern" for="province">Province <span class="text-rose-500">*</span></label>
                        <select id="province" name="province" class="select-modern" required>
                            <option value="">Select…</option>
                            @foreach(['Koshi','Madhesh','Bagmati','Gandaki','Lumbini','Karnali','Sudurpashchim'] as $prov)
                            <option value="{{ $prov }}" {{ old('province')===$prov?'selected':'' }}>{{ $prov }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="form-group-modern">
                        <label class="label-modern" for="school_email">School email <span class="text-rose-500">*</span></label>
                        <input id="school_email" type="email" name="school_email" value="{{ old('school_email') }}"
                               class="input-modern" placeholder="info@school.edu.np" required>
                    </div>
                    <div class="form-group-modern">
                        <label class="label-modern" for="school_phone">Phone</label>
                        <input id="school_phone" type="text" name="school_phone" value="{{ old('school_phone') }}"
                               class="input-modern" placeholder="01-XXXXXXX">
                    </div>
                </div>

                <div class="divider"></div>

                {{-- ── Admin account ── --}}
                <p class="text-xs font-bold uppercase tracking-widest" style="color:var(--text-muted)">Admin Account</p>

                <div class="form-group-modern">
                    <label class="label-modern" for="contact_person">Your full name <span class="text-rose-500">*</span></label>
                    <input id="contact_person" type="text" name="contact_person" value="{{ old('contact_person') }}"
                           class="input-modern" placeholder="Ram Prasad Sharma" required>
                </div>

                <div class="form-group-modern">
                    <label class="label-modern" for="admin_email">Admin email <span class="text-rose-500">*</span></label>
                    <input id="admin_email" type="email" name="admin_email" value="{{ old('admin_email') }}"
                           class="input-modern" placeholder="admin@school.edu.np" required>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="form-group-modern">
                        <label class="label-modern" for="admin_password">Password <span class="text-rose-500">*</span></label>
                        <input id="admin_password" type="password" name="admin_password"
                               class="input-modern" placeholder="Min. 8 characters" required>
                    </div>
                    <div class="form-group-modern">
                        <label class="label-modern" for="admin_password_confirmation">Confirm password <span class="text-rose-500">*</span></label>
                        <input id="admin_password_confirmation" type="password" name="admin_password_confirmation"
                               class="input-modern" placeholder="Repeat password" required>
                    </div>
                </div>

                <button type="submit" class="btn-primary w-full justify-center py-3 text-sm font-semibold mt-2">
                    <i class="bi bi-rocket-takeoff-fill"></i> Register School
                </button>

                <p class="text-center text-xs" style="color:var(--text-muted)">
                    Already registered?
                    <a href="{{ route('login') }}" class="text-indigo-600 hover:underline">Sign in</a>
                </p>
            </form>
        </div>{{-- /frosted card --}}
        </div>{{-- /inner scroll container --}}
    </div>{{-- /right panel --}}
</div>
</body>
</html>
