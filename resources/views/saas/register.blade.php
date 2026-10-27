<!doctype html>
<html lang="en" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register Your School — NepalSchoolHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        .form-group-wrap {
            display: flex;
            flex-direction: column;
            width: 100%;
        }
        .register-card-label {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            min-height: 22px !important;
            font-size: 0.8125rem !important;
            font-weight: 700 !important;
            color: #1e293b !important;
            margin-bottom: 0.375rem !important;
            line-height: 1.25 !important;
        }
        .register-card-input {
            background-color: #f8fafc !important;
            color: #0f172a !important;
            font-weight: 600 !important;
            font-size: 0.875rem !important;
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 0.75rem !important;
            height: 44px !important;
            padding: 0 0.875rem !important;
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
        select.register-card-input {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.75rem center;
            background-repeat: no-repeat;
            background-size: 1.25em 1.25em;
            padding-right: 2.25rem !important;
        }
    </style>
</head>
<body style="background:var(--bg-surface);font-family:'Inter',sans-serif">

<div class="min-h-screen flex" style="min-height:100vh">

    {{-- ── Left branding panel ──────────────────────────────────────── --}}
    <div class="hidden lg:flex lg:w-2/5 flex-col justify-between p-12 relative overflow-hidden"
         style="background: linear-gradient(135deg,rgba(15,23,42,.92) 0%,rgba(30,27,75,.85) 60%,rgba(37,99,235,.75) 100%), url('{{ asset('imgs/kathmandu.webp') }}') center center / cover no-repeat;">

        <div class="relative z-10">
            <a href="{{ route('saas.landing') }}" class="inline-flex items-center gap-3 no-underline">
                <div class="w-10 h-10 rounded-xl p-1 bg-white/20 backdrop-blur-md border border-white/20 flex items-center justify-center shadow-md">
                    <img src="{{ asset('imgs/logo_icon.webp') }}?v={{ time() }}" alt="NepalSchoolHub logo"
                         class="w-full h-full object-contain rounded-lg">
                </div>
                <div>
                    <span class="font-extrabold text-white text-base tracking-tight block">Nepal School Hub</span>
                    <span class="text-white/70 text-[11px] font-medium block">Smart Campus ERP</span>
                </div>
            </a>
        </div>

        <div class="relative z-10 space-y-6 my-auto py-10">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-white/15 text-white border border-white/20 backdrop-blur-md">
                <i class="bi bi-patch-check-fill text-amber-300"></i>
                <span>Fast & Simple Setup</span>
            </div>

            <h2 class="font-black text-white leading-tight tracking-tight" style="font-size:2.2rem;text-shadow:0 3px 16px rgba(0,0,0,.45)">
                Empower your school with modern digital management
            </h2>

            <p style="color:rgba(255,255,255,.9)" class="text-sm leading-relaxed max-w-md">
                Join 200+ institutions across Nepal managing admissions, attendance, marks, fees, and communication from a unified platform.
            </p>

            <div class="space-y-3 pt-2">
                @foreach([
                    'Free plan included — zero setup cost',
                    'Dedicated school subdomain & custom portal',
                    'Official CEHRD IEMIS integration ready',
                    'Live WhatsApp & SMS notifications'
                ] as $p)
                <div class="flex items-center gap-3 text-sm font-medium" style="color:rgba(255,255,255,.95)">
                    <div class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-300 flex items-center justify-center flex-shrink-0 text-xs font-bold border border-emerald-400/30">
                        <i class="bi bi-check"></i>
                    </div>
                    <span>{{ $p }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="relative z-10 pt-4 border-t border-white/15 flex items-center justify-between text-xs" style="color:rgba(255,255,255,.7)">
            <span>&copy; {{ date('Y') }} NepalSchoolHub ERP</span>
            <span>Tech Procod Pvt. Ltd.</span>
        </div>
    </div>

    {{-- ── Right form panel ─────────────────────────────────────────── --}}
    <div class="flex-1 overflow-y-auto relative py-8 px-4 sm:px-8 flex items-center justify-center"
         style="background: url('{{ asset('imgs/loginbg.webp') }}') center center / cover no-repeat;">

        <div class="w-full max-w-xl my-auto rounded-3xl p-6 sm:p-10 shadow-2xl border border-white/80"
             style="background:#ffffff;box-shadow:0 25px 60px -15px rgba(15,23,42,0.25);">

            {{-- Mobile brand --}}
            <div class="flex items-center gap-2.5 mb-6 lg:hidden">
                <img src="{{ asset('imgs/logo_icon.webp') }}" alt="NepalSchoolHub logo"
                     class="w-9 h-9 rounded-xl object-contain bg-indigo-600 p-1">
                <span class="font-extrabold text-base text-slate-900">Nepal School Hub</span>
            </div>

            <div class="mb-6">
                <h1 class="font-black text-2xl sm:text-3xl text-slate-900 tracking-tight mb-1.5">Register your school</h1>
                <p class="text-sm text-slate-500 font-medium">Takes about 3 minutes. Free plan available immediately.</p>
            </div>

            {{-- Validation errors --}}
            @if(isset($errors) && $errors->any())
            <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-2xl text-xs">
                <div class="flex items-center gap-2 mb-1.5 font-bold">
                    <i class="bi bi-exclamation-circle-fill text-rose-600 text-sm"></i> Please correct the following:
                </div>
                <ul class="list-disc list-inside space-y-1 text-slate-700">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('saas.register.store') }}" class="space-y-4">
                @csrf

                <input type="hidden" name="plan" value="free">

                {{-- ── Section 1: School Details ── --}}
                <div class="flex items-center gap-2 pb-1 border-b border-slate-100">
                    <span class="w-1.5 h-3.5 rounded-full bg-indigo-600"></span>
                    <span class="text-xs font-black uppercase tracking-wider text-slate-800">School Details</span>
                </div>

                {{-- Row 1: School Name & IEMIS Code (Perfect Equal Height) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div class="form-group-wrap">
                        <label class="register-card-label" for="school_name">
                            <span>School Name <span class="text-rose-600">*</span></span>
                        </label>
                        <input id="school_name" type="text" name="school_name" value="{{ old('school_name') }}"
                               class="register-card-input" placeholder="e.g. Shree Janasewa Secondary School" required>
                    </div>

                    <div class="form-group-wrap">
                        <label class="register-card-label" for="iemis_code">
                            <span>IEMIS Code</span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-tight">MoEST / Optional</span>
                        </label>
                        <input id="iemis_code" type="text" name="iemis_code" value="{{ old('iemis_code') }}"
                               maxlength="12" pattern="[0-9]*" inputmode="numeric"
                               class="register-card-input" placeholder="e.g. 270010001">
                    </div>
                </div>

                {{-- Row 2: District & Province --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div class="form-group-wrap">
                        <label class="register-card-label" for="district">
                            <span>District <span class="text-rose-600">*</span></span>
                        </label>
                        <input id="district" type="text" name="district" value="{{ old('district') }}"
                               class="register-card-input" placeholder="e.g. Kathmandu" required>
                    </div>

                    <div class="form-group-wrap">
                        <label class="register-card-label" for="province">
                            <span>Province <span class="text-rose-600">*</span></span>
                        </label>
                        <select id="province" name="province" class="register-card-input" required>
                            <option value="" class="text-slate-400">Select Province…</option>
                            @foreach(['Koshi','Madhesh','Bagmati','Gandaki','Lumbini','Karnali','Sudurpashchim'] as $prov)
                            <option value="{{ $prov }}" {{ old('province')===$prov?'selected':'' }}>{{ $prov }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Row 3: School Email & Phone --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div class="form-group-wrap">
                        <label class="register-card-label" for="school_email">
                            <span>School Email <span class="text-rose-600">*</span></span>
                        </label>
                        <input id="school_email" type="email" name="school_email" value="{{ old('school_email') }}"
                               class="register-card-input" placeholder="info@school.edu.np" required>
                    </div>

                    <div class="form-group-wrap">
                        <label class="register-card-label" for="school_phone">
                            <span>Phone Number</span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase">Optional</span>
                        </label>
                        <input id="school_phone" type="text" name="school_phone" value="{{ old('school_phone') }}"
                               class="register-card-input" placeholder="01-XXXXXXX / 98XXXXXXXX">
                    </div>
                </div>

                {{-- ── Section 2: Administrator Credentials ── --}}
                <div class="flex items-center gap-2 pt-3 pb-1 border-b border-slate-100">
                    <span class="w-1.5 h-3.5 rounded-full bg-indigo-600"></span>
                    <span class="text-xs font-black uppercase tracking-wider text-slate-800">Admin Account Credentials</span>
                </div>

                {{-- Row 4: Full Name & Admin Email --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div class="form-group-wrap">
                        <label class="register-card-label" for="contact_person">
                            <span>Your Full Name <span class="text-rose-600">*</span></span>
                        </label>
                        <input id="contact_person" type="text" name="contact_person" value="{{ old('contact_person') }}"
                               class="register-card-input" placeholder="Ram Prasad Sharma" required>
                    </div>

                    <div class="form-group-wrap">
                        <label class="register-card-label" for="admin_email">
                            <span>Admin Login Email <span class="text-rose-600">*</span></span>
                        </label>
                        <input id="admin_email" type="email" name="admin_email" value="{{ old('admin_email') }}"
                               class="register-card-input" placeholder="admin@school.edu.np" required>
                    </div>
                </div>

                {{-- Row 5: Password & Confirm Password (Equal Height & Symmetric) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div class="form-group-wrap">
                        <label class="register-card-label" for="admin_password">
                            <span>Password <span class="text-rose-600">*</span></span>
                        </label>
                        <input id="admin_password" type="password" name="admin_password"
                               class="register-card-input" placeholder="Min. 8 characters" required>
                    </div>

                    <div class="form-group-wrap">
                        <label class="register-card-label" for="admin_password_confirmation">
                            <span>Confirm Password <span class="text-rose-600">*</span></span>
                        </label>
                        <input id="admin_password_confirmation" type="password" name="admin_password_confirmation"
                               class="register-card-input" placeholder="Repeat password" required>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="pt-2">
                    <button type="submit"
                            class="w-full justify-center py-3.5 px-6 rounded-xl font-bold text-white text-sm shadow-lg transition duration-200 flex items-center gap-2 cursor-pointer hover:brightness-110"
                            style="background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); box-shadow: 0 6px 20px rgba(79,70,229,0.35);">
                        <i class="bi bi-rocket-takeoff-fill"></i>
                        <span>Register School</span>
                    </button>
                </div>

                <p class="text-center text-xs font-medium pt-1 text-slate-500">
                    Already registered?
                    <a href="{{ route('login') }}" class="text-indigo-600 font-bold hover:underline">Sign in to Portal</a>
                </p>
            </form>
        </div>
    </div>
</div>
</body>
</html>
