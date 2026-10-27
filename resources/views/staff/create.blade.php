@extends('layouts.app')

@section('title', 'Add Staff Member - School Management')

@section('content')
<div class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto space-y-6 bg-slate-50/60 dark:bg-slate-950 min-h-screen">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400">
        <a href="{{ route('home') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition flex items-center gap-1">
            <i class="bi bi-house-door"></i> Home
        </a>
        <i class="bi bi-chevron-right text-[10px]"></i>
        <a href="{{ route('staff.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">
            HR &amp; Staff
        </a>
        <i class="bi bi-chevron-right text-[10px]"></i>
        <span class="text-slate-800 dark:text-slate-200">Add Staff Member</span>
    </nav>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200/80 dark:border-slate-800/80">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 via-indigo-700 to-purple-600 text-white flex items-center justify-center shadow-lg shadow-indigo-500/25 text-xl font-bold">
                <i class="bi bi-person-plus-fill"></i>
            </div>
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Add Staff Member</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                    Enroll non-teaching staff, administrative personnel, officers, and support team members
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('staff.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 transition shadow-xs">
                <i class="bi bi-arrow-left"></i> Staff Directory
            </a>
            @can('create teachers')
            <a href="{{ route('teacher.create.show') }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-200 dark:border-indigo-800/60 hover:bg-indigo-100 transition shadow-xs">
                <i class="bi bi-person-video3"></i> Add Teacher Instead
            </a>
            @endcan
        </div>
    </div>

    {{-- Validation Errors Summary --}}
    @if ($errors->any())
    <div class="p-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 rounded-2xl text-rose-800 dark:text-rose-200 text-sm shadow-xs">
        <div class="flex items-center gap-2 font-bold mb-2">
            <i class="bi bi-exclamation-triangle-fill text-rose-600 dark:text-rose-400"></i>
            <span>Please correct the errors below before continuing:</span>
        </div>
        <ul class="list-disc list-inside space-y-1 text-xs text-rose-700 dark:text-rose-300 ml-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Form --}}
    <form action="{{ route('staff.store') }}" method="POST" enctype="multipart/form-data" id="staffCreateForm" class="space-y-6">
        @csrf

        {{-- Card 1: Personal Information --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-7 shadow-xs">
            <div class="flex items-center gap-2.5 pb-4 mb-5 border-b border-slate-100 dark:border-slate-800">
                <span class="w-2 h-5 bg-indigo-600 rounded-full"></span>
                <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">1. Personal Information</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-start">
                {{-- Left: Photo Preview & Upload --}}
                <div class="md:col-span-4 lg:col-span-3 flex flex-col items-center p-5 bg-slate-50/70 dark:bg-slate-800/50 rounded-2xl border border-slate-200/70 dark:border-slate-700/60">
                    <div class="relative group cursor-pointer mb-3" onclick="document.getElementById('photoInput').click()">
                        <div id="photoPreview" class="w-28 h-28 rounded-2xl bg-slate-200 dark:bg-slate-700 overflow-hidden flex items-center justify-center text-slate-400 text-4xl shadow-inner border-2 border-dashed border-slate-300 dark:border-slate-600 group-hover:border-indigo-500 transition">
                            <i class="bi bi-person"></i>
                        </div>
                        <div class="absolute inset-0 bg-slate-900/40 rounded-2xl opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-semibold gap-1 backdrop-blur-xs">
                            <i class="bi bi-camera"></i> Change
                        </div>
                    </div>
                    <input type="file" id="photoInput" name="photo" accept="image/*" class="hidden" onchange="previewStaffPhoto(this)">
                    <button type="button" onclick="document.getElementById('photoInput').click()" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                        Upload Staff Photo
                    </button>
                    <p class="text-[11px] text-slate-400 text-center mt-1">JPG, PNG or WEBP (Max 2MB)</p>
                </div>

                {{-- Right: Inputs --}}
                <div class="md:col-span-8 lg:col-span-9 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- First Name --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            First Name <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <i class="bi bi-person absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                            <input type="text" name="first_name" value="{{ old('first_name') }}" required placeholder="e.g. Ramesh"
                                class="w-full pl-10 pr-3.5 py-2.5 text-xs sm:text-sm rounded-xl border {{ $errors->has('first_name') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800' }} text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-hidden transition">
                        </div>
                    </div>

                    {{-- Last Name --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            Last Name <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <i class="bi bi-person absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                            <input type="text" name="last_name" value="{{ old('last_name') }}" required placeholder="e.g. Shrestha"
                                class="w-full pl-10 pr-3.5 py-2.5 text-xs sm:text-sm rounded-xl border {{ $errors->has('last_name') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800' }} text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-hidden transition">
                        </div>
                    </div>

                    {{-- Gender --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            Gender
                        </label>
                        <div class="relative">
                            <i class="bi bi-gender-ambiguous absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                            <select name="gender" class="w-full pl-10 pr-3.5 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-hidden transition">
                                <option value="">— Select Gender —</option>
                                <option value="Male" @selected(old('gender') == 'Male')>Male</option>
                                <option value="Female" @selected(old('gender') == 'Female')>Female</option>
                                <option value="Other" @selected(old('gender') == 'Other')>Other</option>
                            </select>
                        </div>
                    </div>

                    {{-- Phone --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            Contact Phone
                        </label>
                        <div class="relative">
                            <i class="bi bi-telephone absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                            <input type="text" name="phone" value="{{ old('phone') }}" placeholder="e.g. +977-9801234567"
                                class="w-full pl-10 pr-3.5 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-hidden transition">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: Role & Designation --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-7 shadow-xs">
            <div class="flex items-center gap-2.5 pb-4 mb-5 border-b border-slate-100 dark:border-slate-800">
                <span class="w-2 h-5 bg-purple-600 rounded-full"></span>
                <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">2. Role &amp; Designation</h2>
            </div>

            {{-- Notice callout --}}
            <div class="p-4 bg-indigo-50/70 dark:bg-indigo-950/30 border border-indigo-200/80 dark:border-indigo-800/60 rounded-2xl text-xs sm:text-sm text-indigo-900 dark:text-indigo-200 mb-5 flex items-start gap-3">
                <i class="bi bi-info-circle-fill text-indigo-600 dark:text-indigo-400 text-base flex-shrink-0 mt-0.5"></i>
                <div class="leading-relaxed">
                    <strong>Role assignment guidelines:</strong> This form is designed for non-teaching administrative and support staff.
                    <span class="text-indigo-700 dark:text-indigo-300 block mt-0.5">
                        • Teachers are enrolled via <a href="{{ route('teacher.create.show') }}" class="underline font-bold hover:text-indigo-900">Add Teacher</a> with qualification tracking.<br>
                        • Students are enrolled via <a href="{{ route('student.create.show') }}" class="underline font-bold hover:text-indigo-900">Student Admission</a>.<br>
                        • SaaS Super Admins are managed via the central platform.
                    </span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">
                    Select Staff Role <span class="text-rose-500">*</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @php
                        $selectedRole = old('role', 'accountant');
                    @endphp
                    @foreach($assignableRoles as $slug => $info)
                    <label class="staff-role-card relative flex items-start gap-3 p-3.5 rounded-2xl border-2 transition cursor-pointer bg-white dark:bg-slate-900"
                           style="{{ $selectedRole === $slug ? 'border-color:#4f46e5;background-color:#eef2ff;' : 'border-color:#e2e8f0;' }}"
                           onclick="selectRoleCard(this)">
                        <input type="radio" name="role" value="{{ $slug }}" @checked($selectedRole === $slug) required class="sr-only">
                        {{-- Selected checkmark --}}
                        <span class="role-check absolute top-2 right-2 w-5 h-5 rounded-full bg-indigo-600 text-white flex items-center justify-center text-[10px] {{ $selectedRole === $slug ? '' : 'hidden' }}">
                            <i class="bi bi-check-lg"></i>
                        </span>
                        <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0 text-lg">
                            <i class="bi {{ $info['icon'] ?? 'bi-person-badge' }}"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="block text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100 leading-tight">
                                {{ $info['label'] ?? ucwords(str_replace('-', ' ', $slug)) }}
                            </span>
                            <span class="block text-[11px] text-slate-400 dark:text-slate-500 leading-tight mt-1 line-clamp-2">
                                {{ $info['desc'] ?? 'Staff role privileges' }}
                            </span>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Card 3: Account & Access Credentials --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-7 shadow-xs">
            <div class="flex items-center gap-2.5 pb-4 mb-5 border-b border-slate-100 dark:border-slate-800">
                <span class="w-2 h-5 bg-emerald-600 rounded-full"></span>
                <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">3. Account &amp; Access Credentials</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Email Address --}}
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        Official Email Address <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <i class="bi bi-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="email" name="email" value="{{ old('email') }}" required placeholder="staff.name@school.edu.np"
                            class="w-full pl-10 pr-3.5 py-2.5 text-xs sm:text-sm rounded-xl border {{ $errors->has('email') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800' }} text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-hidden transition">
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">This email will be used for system login and administrative notifications.</p>
                </div>

                {{-- Password --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        Initial Password <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <i class="bi bi-shield-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="password" id="staffPassword" name="password" required minlength="8" placeholder="Minimum 8 characters"
                            class="w-full pl-10 pr-10 py-2.5 text-xs sm:text-sm rounded-xl border {{ $errors->has('password') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800' }} text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-hidden transition">
                        <button type="button" onclick="togglePasswordVisibility('staffPassword', 'eyeStaffPass')" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                            <i id="eyeStaffPass" class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                {{-- Confirm Password --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        Confirm Password <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <i class="bi bi-shield-check absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="password" id="staffPasswordConfirm" name="password_confirmation" required minlength="8" placeholder="Repeat password"
                            class="w-full pl-10 pr-10 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-hidden transition">
                        <button type="button" onclick="togglePasswordVisibility('staffPasswordConfirm', 'eyeStaffPassConfirm')" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                            <i id="eyeStaffPassConfirm" class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 4: Address & Contact Details --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-7 shadow-xs">
            <div class="flex items-center gap-2.5 pb-4 mb-5 border-b border-slate-100 dark:border-slate-800">
                <span class="w-2 h-5 bg-amber-500 rounded-full"></span>
                <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">4. Address &amp; Location Details</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        Primary Street Address
                    </label>
                    <div class="relative">
                        <i class="bi bi-geo-alt absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="text" name="address" value="{{ old('address') }}" placeholder="e.g. Ward No. 4, Putalisadak"
                            class="w-full pl-10 pr-3.5 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-hidden transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        Address Line 2 (Optional)
                    </label>
                    <input type="text" name="address2" value="{{ old('address2') }}" placeholder="Apartment, suite, unit"
                        class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-hidden transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        City / District
                    </label>
                    <input type="text" name="city" value="{{ old('city') }}" placeholder="e.g. Kathmandu"
                        class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-hidden transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        Postal / ZIP Code
                    </label>
                    <input type="text" name="zip" value="{{ old('zip') }}" placeholder="e.g. 44600"
                        class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-hidden transition">
                </div>
            </div>
        </div>

        {{-- Form Actions Bar --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-5 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl shadow-xs">
            <div class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-2">
                <i class="bi bi-shield-check text-emerald-600 text-base"></i>
                <span>Credentials will be encrypted with Bcrypt and school tenancy enforced</span>
            </div>
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <a href="{{ route('staff.index') }}" class="w-full sm:w-auto text-center px-5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                    Cancel
                </a>
                <button type="submit" id="saveStaffBtn" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 shadow-md shadow-indigo-500/25 transition active:scale-95 cursor-pointer">
                    <i class="bi bi-check2-circle text-base"></i> Save Staff Member
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function togglePasswordVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (!input || !icon) return;
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

function previewStaffPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('photoPreview');
            preview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function highlightRoleCard(radio) {
    document.querySelectorAll('.staff-role-card').forEach(card => {
        card.style.borderColor = '#e2e8f0';
        card.style.backgroundColor = '';
        const check = card.querySelector('.role-check');
        if (check) check.classList.add('hidden');
        const radio = card.querySelector('input[type="radio"]');
        if (radio) radio.checked = false;
    });
    // This is now called via selectRoleCard, kept for back-compat
}
function selectRoleCard(label) {
    // Deselect all
    document.querySelectorAll('.staff-role-card').forEach(card => {
        card.style.borderColor = '#e2e8f0';
        card.style.backgroundColor = '';
        const check = card.querySelector('.role-check');
        if (check) check.classList.add('hidden');
    });
    // Select clicked
    label.style.borderColor = '#4f46e5';
    label.style.backgroundColor = '#eef2ff';
    const check = label.querySelector('.role-check');
    if (check) check.classList.remove('hidden');
    const radio = label.querySelector('input[type="radio"]');
    if (radio) radio.checked = true;
}
</script>
@endsection
