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
    <style>
        .register-card-input {
            background-color: #f8fafc !important;
            color: #0f172a !important;
            font-weight: 600 !important;
            font-size: 0.875rem !important;
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 0.75rem !important;
            padding: 0.6875rem 0.875rem !important;
            width: 100% !important;
            transition: all 0.2s ease-in-out !important;
        }
        .register-card-input:focus {
            background-color: #ffffff !important;
            border-color: #4f46e5 !important;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.15) !important;
            outline: none !important;
        }
        .register-card-input::placeholder {
            color: #94a3b8 !important;
            font-weight: 400 !important;
        }
        .register-card-label {
            display: block !important;
            font-size: 0.8125rem !important;
            font-weight: 700 !important;
            color: #1e293b !important;
            margin-bottom: 0.375rem !important;
        }
    </style>
</head>
<body style="background:var(--bg-surface);font-family:'Inter',sans-serif">

<div class="min-h-screen flex" style="height:100vh;overflow:hidden">

    {{-- ── Left branding panel ──────────────────────────────────────── --}}
    <div class="hidden lg:flex lg:w-2/5 flex-col justify-between p-12 relative overflow-hidden"
         style="background: url('{{ asset('imgs/kathmandu.webp') }}') center center / cover no-repeat;">

        {{-- Dark overlay for readability --}}
        <div class="absolute inset-0" style="background:linear-gradient(135deg,rgba(15,23,42,.88) 0%,rgba(30,27,75,.8) 60%,rgba(37,99,235,.65) 100%)"></div>

        <div class="relative">
            <a href="{{ route('saas.landing') }}" class="flex items-center gap-2.5 no-underline">
                <img src="{{ asset('imgs/logo_icon.webp') }}?v={{ time() }}" alt="NepalSchoolHub logo"
                     class="w-10 h-10 rounded-xl object-contain"
                     style="background:rgba(255,255,255,.2);backdrop-filter:blur(6px);padding:4px">
                <span class="font-bold text-white text-base">NepalSchoolHub</span>
            </a>
        </div>
        <div class="relative z-10 space-y-5">
            <h2 class="font-extrabold text-white leading-snug" style="font-size:1.85rem;text-shadow:0 2px 12px rgba(0,0,0,.5)">
                Start managing your school smarter today
            </h2>
            <p style="color:rgba(255,255,255,.85)" class="text-sm leading-relaxed" style="text-shadow:0 1px 6px rgba(0,0,0,.3)">
                Join schools across Nepal using NepalSchoolHub to streamline admissions, exams, fees, attendance and more.
            </p>
            @foreach(['Free plan — no credit card needed','14-day trial on all paid plans','Setup in under 5 minutes','Dedicated support for Nepali schools'] as $p)
            <div class="flex items-center gap-2.5 text-sm" style="color:rgba(255,255,255,.95)">
                <i class="bi bi-check-circle-fill" style="color:#4ade80;flex-shrink:0"></i>{{ $p }}
            </div>
            @endforeach
        </div>
        <div class="relative z-10">
            <p class="text-xs" style="color:rgba(255,255,255,.5)">&copy; {{ date('Y') }} NepalSchoolHub. All rights reserved.</p>
        </div>
    </div>

    {{-- ── Right form panel ─────────────────────────────────────────── --}}
    <div class="flex-1 overflow-y-auto relative"
         style="background: url('{{ asset('imgs/loginbg.webp') }}') center center / cover no-repeat;">

        {{-- Inner scroll container centred --}}
        <div class="min-h-full flex items-center justify-center p-4 sm:p-8">

        {{-- Solid High-Contrast White Card --}}
        <div class="w-full max-w-lg my-auto rounded-2xl p-7 sm:p-10"
             style="background:#ffffff;box-shadow:0 20px 50px -10px rgba(15,23,42,0.22), 0 0 1px rgba(15,23,42,0.1);border:1px solid #e2e8f0">

            {{-- Mobile brand --}}
            <div class="flex items-center gap-2 mb-6 lg:hidden">
                <img src="{{ asset('imgs/logo_icon.webp') }}" alt="NepalSchoolHub logo"
                     class="w-9 h-9 rounded-xl object-contain bg-indigo-600"
                     style="padding:4px">
                <span class="font-bold text-base" style="color:var(--text-heading)">NepalSchoolHub</span>
            </div>

            <h1 class="font-extrabold text-2xl sm:text-3xl mb-1 text-slate-900 tracking-tight">Register your school</h1>
            <p class="text-sm font-medium mb-6 text-slate-600">Takes about 3 minutes. Free plan available immediately.</p>

            {{-- Validation errors --}}
            @if(isset($errors) && $errors->any())
            <div class="alert-modern alert-error mb-6 bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl">
                <div class="flex items-center gap-2 mb-1 font-bold text-xs">
                    <i class="bi bi-exclamation-circle-fill text-rose-600"></i> Please resolve the following errors:
                </div>
                <ul class="list-disc list-inside text-xs space-y-0.5">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('saas.register.store') }}" class="space-y-5">
                @csrf

                {{-- Plan is default free --}}
                <input type="hidden" name="plan" value="free">

                {{-- ── School Details Header ── --}}
                <div class="flex items-center gap-2 pt-2 border-t border-slate-100">
                    <span class="w-1.5 h-4 rounded-full bg-indigo-600"></span>
                    <span class="text-xs font-extrabold uppercase tracking-wider text-slate-800">School Details</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="form-group-modern">
                        <label class="register-card-label" for="school_name">School name <span class="text-rose-600 font-bold">*</span></label>
                        <input id="school_name" type="text" name="school_name" value="{{ old('school_name') }}"
                               class="register-card-input" placeholder="e.g. Shree Janasewa Secondary School" required>
                    </div>
                    <div class="form-group-modern">
                        <div class="flex items-center justify-between mb-1">
                            <label class="register-card-label mb-0" for="iemis_code">
                                <i class="bi bi-shield-check text-indigo-600 me-0.5"></i> School IEMIS Code
                            </label>
                            <span class="text-[10px] font-bold text-indigo-700 bg-indigo-50 px-1.5 py-0.5 rounded border border-indigo-100">CEHRD Nepal</span>
                        </div>
                        <input id="iemis_code" type="text" name="iemis_code" value="{{ old('iemis_code') }}"
                               maxlength="12" pattern="[0-9]*" inputmode="numeric"
                               class="register-card-input" placeholder="e.g. 270010001">
                        <p class="text-[10px] text-slate-400 mt-1 leading-tight">9-digit official code assigned by MoEST / CEHRD (Optional)</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="form-group-modern">
                        <label class="register-card-label" for="district">District <span class="text-rose-600 font-bold">*</span></label>
                        <input id="district" type="text" name="district" value="{{ old('district') }}"
                               class="register-card-input" placeholder="Kathmandu" required>
                    </div>
                    <div class="form-group-modern">
                        <label class="register-card-label" for="province">Province <span class="text-rose-600 font-bold">*</span></label>
                        <select id="province" name="province" class="register-card-input" required>
                            <option value="" class="text-slate-400">Select Province…</option>
                            @foreach(['Koshi','Madhesh','Bagmati','Gandaki','Lumbini','Karnali','Sudurpashchim'] as $prov)
                            <option value="{{ $prov }}" {{ old('province')===$prov?'selected':'' }}>{{ $prov }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="form-group-modern">
                        <label class="register-card-label" for="school_email">School email <span class="text-rose-600 font-bold">*</span></label>
                        <input id="school_email" type="email" name="school_email" value="{{ old('school_email') }}"
                               class="register-card-input" placeholder="info@school.edu.np" required>
                    </div>
                    <div class="form-group-modern">
                        <label class="register-card-label" for="school_phone">Phone</label>
                        <input id="school_phone" type="text" name="school_phone" value="{{ old('school_phone') }}"
                               class="register-card-input" placeholder="01-XXXXXXX">
                    </div>
                </div>

                {{-- ── Admin Account Header ── --}}
                <div class="flex items-center gap-2 pt-3 border-t border-slate-100">
                    <span class="w-1.5 h-4 rounded-full bg-indigo-600"></span>
                    <span class="text-xs font-extrabold uppercase tracking-wider text-slate-800">Admin Account Credentials</span>
                </div>

                <div class="form-group-modern">
                    <label class="register-card-label" for="contact_person">Your full name <span class="text-rose-600 font-bold">*</span></label>
                    <input id="contact_person" type="text" name="contact_person" value="{{ old('contact_person') }}"
                           class="register-card-input" placeholder="Ram Prasad Sharma" required>
                </div>

                <div class="form-group-modern">
                    <label class="register-card-label" for="admin_email">Admin email <span class="text-rose-600 font-bold">*</span></label>
                    <input id="admin_email" type="email" name="admin_email" value="{{ old('admin_email') }}"
                           class="register-card-input" placeholder="admin@school.edu.np" required>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="form-group-modern">
                        <label class="register-card-label" for="admin_password">Password <span class="text-rose-600 font-bold">*</span></label>
                        <input id="admin_password" type="password" name="admin_password"
                               class="register-card-input" placeholder="Min. 8 characters" required>
                    </div>
                    <div class="form-group-modern">
                        <label class="register-card-label" for="admin_password_confirmation">Confirm password <span class="text-rose-600 font-bold">*</span></label>
                        <input id="admin_password_confirmation" type="password" name="admin_password_confirmation"
                               class="register-card-input" placeholder="Repeat password" required>
                    </div>
                </div>

                <button type="submit" class="w-full justify-center py-3.5 px-6 rounded-xl font-bold text-white text-sm shadow-lg transition duration-200 flex items-center gap-2 mt-4"
                        style="background:linear-gradient(135deg,#4f46e5 0%,#3730a3 100%);box-shadow:0 4px 16px rgba(79,70,229,0.35);">
                    <i class="bi bi-rocket-takeoff-fill"></i> Register School
                </button>

                <p class="text-center text-xs font-medium pt-2 text-slate-600">
                    Already registered?
                    <a href="{{ route('login') }}" class="text-indigo-600 font-bold hover:underline">Sign in to Portal</a>
                </p>
            </form>
        </div>{{-- /high contrast white card --}}
        </div>{{-- /inner scroll container --}}
    </div>{{-- /right panel --}}
</div>
</body>
</html>
