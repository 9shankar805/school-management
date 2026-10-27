@extends('layouts.app')

@section('title', 'My Profile & Account Settings')

@section('content')
<div class="px-4 lg:px-8 py-6 max-w-7xl mx-auto space-y-6">

    {{-- Breadcrumbs & Header Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="w-10 h-10 rounded-2xl bg-indigo-600/10 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 flex items-center justify-center font-bold text-xl shadow-xs">
                <i class="bi bi-person-gear"></i>
            </span>
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">My Profile & Account Settings</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">Manage your personal credentials, contact details, profile picture, and security</p>
            </div>
        </div>

        {{-- Clean Breadcrumb (list-none to prevent numbers) --}}
        <nav aria-label="breadcrumb">
            <div class="flex items-center gap-2 text-xs font-medium text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800/80 px-3.5 py-2 rounded-xl">
                <a href="{{ route('home') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition flex items-center gap-1.5">
                    <i class="bi bi-house-door-fill text-slate-400"></i>
                    <span>Home</span>
                </a>
                <span class="text-slate-300 dark:text-slate-600">/</span>
                <span class="font-semibold text-slate-800 dark:text-slate-200">Account Profile</span>
            </div>
        </nav>
    </div>

    @include('session-messages')

    {{-- ══════════════════════════════════════════════════════════════════════
         EXECUTIVE HERO PROFILE CARD (WHITE THEME)
    ══════════════════════════════════════════════════════════════════════ --}}
    <div class="relative overflow-hidden rounded-3xl bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 shadow-sm border border-slate-200/80 dark:border-slate-800 p-6 md:p-8">

        <div class="relative z-10 flex flex-col md:flex-row items-center md:items-start gap-6">

            {{-- Avatar Container --}}
            <div class="relative flex-shrink-0">
                <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl overflow-hidden ring-4 ring-indigo-50 dark:ring-slate-800 shadow-sm bg-indigo-50 dark:bg-slate-800 flex items-center justify-center border border-slate-200/60 dark:border-slate-700">
                    <img id="hero-avatar-preview"
                         src="{{ $user->avatar }}"
                         alt="{{ $user->full_name }}"
                         class="w-full h-full object-cover">
                </div>

                {{-- Camera Upload Button (Bottom Right) --}}
                <label for="profile-photo-input"
                       class="cursor-pointer absolute -bottom-1.5 -right-1.5 w-8 h-8 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white flex items-center justify-center shadow-md ring-2 ring-white dark:ring-slate-900 transition-all duration-150 hover:scale-110 active:scale-95"
                       title="Upload New Profile Photo">
                    <i class="bi bi-camera-fill text-xs"></i>
                </label>

                {{-- Online Status Dot (Top Right) --}}
                <span class="absolute -top-1 -right-1 flex h-3.5 w-3.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-emerald-500 ring-2 ring-white dark:ring-slate-900"></span>
                </span>
            </div>

            {{-- Identity & Meta Information --}}
            <div class="flex-1 text-center md:text-left min-w-0 space-y-3">

                {{-- Name & Badges --}}
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-2.5">
                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                        {{ $user->full_name }}
                    </h2>

                    {{-- Role Badge --}}
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800/60 uppercase tracking-wider shadow-2xs">
                        <i class="bi bi-shield-check text-indigo-600 dark:text-indigo-400"></i>
                        {{ str_replace('-', ' ', $user->primary_role) }}
                    </span>

                    {{-- School Badge --}}
                    @if($user->school)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800 shadow-2xs">
                        <i class="bi bi-building text-emerald-600 dark:text-emerald-400"></i>
                        {{ $user->school->name }}
                    </span>
                    @endif
                </div>

                {{-- Contact Info Pills Row --}}
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-2 pt-0.5">

                    {{-- Email Chip --}}
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700/70 border border-slate-200/80 dark:border-slate-700 text-xs text-slate-700 dark:text-slate-200 transition shadow-2xs">
                        <i class="bi bi-envelope-fill text-indigo-600 dark:text-indigo-400"></i>
                        <span class="font-medium">{{ $user->email }}</span>
                        <button type="button" onclick="copyToClipboard('{{ $user->email }}', this)" class="text-slate-400 hover:text-slate-700 dark:hover:text-white transition px-1 py-0.5 rounded hover:bg-slate-200/60 dark:hover:bg-slate-700" title="Copy Email">
                            <i class="bi bi-clipboard text-[11px]"></i>
                        </button>
                    </div>

                    {{-- Phone Chip --}}
                    @if($user->phone)
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700/70 border border-slate-200/80 dark:border-slate-700 text-xs text-slate-700 dark:text-slate-200 transition shadow-2xs">
                        <i class="bi bi-telephone-fill text-indigo-600 dark:text-indigo-400"></i>
                        <span class="font-medium">{{ $user->phone }}</span>
                        <button type="button" onclick="copyToClipboard('{{ $user->phone }}', this)" class="text-slate-400 hover:text-slate-700 dark:hover:text-white transition px-1 py-0.5 rounded hover:bg-slate-200/60 dark:hover:bg-slate-700" title="Copy Phone">
                            <i class="bi bi-clipboard text-[11px]"></i>
                        </button>
                    </div>
                    @endif

                    {{-- Location Chip --}}
                    @if($user->city || $user->address)
                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 text-xs text-slate-700 dark:text-slate-200 shadow-2xs">
                        <i class="bi bi-geo-alt-fill text-indigo-600 dark:text-indigo-400"></i>
                        <span class="font-medium">{{ $user->city ?? $user->address }}</span>
                    </div>
                    @endif

                    {{-- Joined Date Chip --}}
                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 text-xs text-slate-600 dark:text-slate-300 shadow-2xs">
                        <i class="bi bi-calendar2-check-fill text-indigo-600 dark:text-indigo-400"></i>
                        <span>Joined {{ $user->created_at ? $user->created_at->format('M Y') : 'N/A' }}</span>
                    </div>
                </div>

                {{-- Actions & Completion Row --}}
                <div class="pt-2 flex flex-wrap items-center justify-center md:justify-start gap-3">
                    {{-- 2FA Status --}}
                    @if($user->twoFactorAuth && $user->twoFactorAuth->is_enabled)
                    <a href="{{ route('two-factor.setup') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800 transition shadow-2xs">
                        <i class="bi bi-shield-lock-fill text-emerald-600"></i> 2FA Enabled
                    </a>
                    @else
                    <a href="{{ route('two-factor.setup') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-amber-50 dark:bg-amber-950/40 hover:bg-amber-100 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800 transition shadow-2xs">
                        <i class="bi bi-shield-exclamation text-amber-600"></i> Setup 2FA Security
                    </a>
                    @endif

                    {{-- Change Password Button --}}
                    <button type="button" onclick="switchProfileTab('security-tab')" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 transition shadow-2xs">
                        <i class="bi bi-key-fill text-indigo-600 dark:text-indigo-400"></i> Change Password
                    </button>

                    {{-- Remove Custom Photo (if uploaded) --}}
                    @if($user->photo)
                    <form action="{{ route('profile.photo.remove') }}" method="POST" onsubmit="return confirm('Are you sure you want to remove your profile photo?');" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800 transition shadow-2xs">
                            <i class="bi bi-trash text-rose-600"></i> Remove Photo
                        </button>
                    </form>
                    @endif

                    {{-- Profile Completion Pill --}}
                    <div class="hidden sm:flex items-center gap-2.5 ms-auto text-xs text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 px-3.5 py-1.5 rounded-xl shadow-2xs">
                        <span class="text-[11px] font-medium">Profile {{ $completionPercentage ?? 75 }}% Complete</span>
                        <div class="w-16 h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500 rounded-full transition-all duration-500" style="width: {{ $completionPercentage ?? 75 }}%"></div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
         MODERN PILL TABS
    ══════════════════════════════════════════════════════════════════════ --}}
    <div class="flex items-center gap-2 p-1.5 bg-slate-100 dark:bg-slate-800/80 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 overflow-x-auto">
        <button type="button"
                id="tab-btn-personal"
                onclick="switchProfileTab('personal-tab')"
                class="profile-nav-tab inline-flex items-center gap-2 px-4 py-2 text-xs font-bold rounded-xl transition-all shadow-xs bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 border border-slate-200/80 dark:border-slate-700">
            <i class="bi bi-person-lines-fill text-sm"></i>
            <span>Personal Details & Contact</span>
        </button>

        <button type="button"
                id="tab-btn-security"
                onclick="switchProfileTab('security-tab')"
                class="profile-nav-tab inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 transition-all">
            <i class="bi bi-shield-lock-fill text-sm"></i>
            <span>Security & Password</span>
        </button>

        <button type="button"
                id="tab-btn-roles"
                onclick="switchProfileTab('roles-tab')"
                class="profile-nav-tab inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 transition-all">
            <i class="bi bi-person-badge-fill text-sm"></i>
            <span>Role & Account Privileges</span>
        </button>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
         TAB 1: PERSONAL DETAILS & CONTACT
    ══════════════════════════════════════════════════════════════════════ --}}
    <div id="personal-tab" class="profile-tab-pane space-y-6">

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Hidden File Input for Avatar --}}
            <input type="file" id="profile-photo-input" name="photo" accept="image/jpeg,image/png,image/jpg,image/webp,image/gif" class="hidden" onchange="previewAvatar(this)">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Left 2 Cols: Form Sections --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Personal Info Card --}}
                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8">
                        <div class="flex items-center justify-between pb-5 border-b border-slate-100 dark:border-slate-800 mb-6">
                            <div>
                                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                    <i class="bi bi-person-circle text-indigo-600 dark:text-indigo-400"></i>
                                    Basic Identity & Contact Information
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Edit your name, primary email address, and mobile number</p>
                            </div>
                        </div>

                        <div class="space-y-5">
                            {{-- Name Grid --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="first_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                                        First Name <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text"
                                           id="first_name"
                                           name="first_name"
                                           value="{{ old('first_name', $user->first_name) }}"
                                           class="w-full px-4 py-2.5 text-sm rounded-xl border @error('first_name') border-rose-400 bg-rose-50/20 @else border-slate-200 dark:border-slate-700 @enderror bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition font-medium"
                                           placeholder="First name"
                                           required>
                                    @error('first_name')
                                        <p class="text-xs text-rose-500 mt-1 flex items-center gap-1"><i class="bi bi-exclamation-circle"></i> {{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="last_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                                        Last Name <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text"
                                           id="last_name"
                                           name="last_name"
                                           value="{{ old('last_name', $user->last_name) }}"
                                           class="w-full px-4 py-2.5 text-sm rounded-xl border @error('last_name') border-rose-400 bg-rose-50/20 @else border-slate-200 dark:border-slate-700 @enderror bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition font-medium"
                                           placeholder="Last name"
                                           required>
                                    @error('last_name')
                                        <p class="text-xs text-rose-500 mt-1 flex items-center gap-1"><i class="bi bi-exclamation-circle"></i> {{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Email & Phone Grid --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                                        Email / Gmail Address <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="email"
                                           id="email"
                                           name="email"
                                           value="{{ old('email', $user->email) }}"
                                           class="w-full px-4 py-2.5 text-sm rounded-xl border @error('email') border-rose-400 bg-rose-50/20 @else border-slate-200 dark:border-slate-700 @enderror bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition font-medium"
                                           placeholder="your.email@gmail.com"
                                           required>
                                    @error('email')
                                        <p class="text-xs text-rose-500 mt-1 flex items-center gap-1"><i class="bi bi-exclamation-circle"></i> {{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="phone" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                                        Phone / Mobile Number
                                    </label>
                                    <input type="tel"
                                           id="phone"
                                           name="phone"
                                           value="{{ old('phone', $user->phone) }}"
                                           class="w-full px-4 py-2.5 text-sm rounded-xl border @error('phone') border-rose-400 bg-rose-50/20 @else border-slate-200 dark:border-slate-700 @enderror bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition font-medium"
                                           placeholder="+977 98XXXXXXXX">
                                    @error('phone')
                                        <p class="text-xs text-rose-500 mt-1 flex items-center gap-1"><i class="bi bi-exclamation-circle"></i> {{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Gender, Birthday & Nationality --}}
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label for="gender" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                                        Gender
                                    </label>
                                    <select id="gender"
                                            name="gender"
                                            class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition font-medium">
                                        <option value="">Select Gender</option>
                                        <option value="male" {{ strtolower(old('gender', $user->gender)) === 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ strtolower(old('gender', $user->gender)) === 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other" {{ strtolower(old('gender', $user->gender)) === 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="birthday" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                                        Date of Birth
                                    </label>
                                    <input type="date"
                                           id="birthday"
                                           name="birthday"
                                           value="{{ old('birthday', $user->birthday ? $user->birthday->format('Y-m-d') : '') }}"
                                           class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition font-medium">
                                    @error('birthday')
                                        <p class="text-xs text-rose-500 mt-1 flex items-center gap-1"><i class="bi bi-exclamation-circle"></i> {{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="nationality" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                                        Nationality
                                    </label>
                                    <input type="text"
                                           id="nationality"
                                           name="nationality"
                                           value="{{ old('nationality', $user->nationality ?? 'Nepali') }}"
                                           class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition font-medium"
                                           placeholder="e.g. Nepali">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Location Details Card --}}
                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8">
                        <div class="flex items-center justify-between pb-5 border-b border-slate-100 dark:border-slate-800 mb-6">
                            <div>
                                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                    <i class="bi bi-geo-alt-fill text-indigo-600 dark:text-indigo-400"></i>
                                    Address & Demographics
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Location details, postal address, and blood group</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <label for="address" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                                    Street Address / Tole
                                </label>
                                <input type="text"
                                       id="address"
                                       name="address"
                                       value="{{ old('address', $user->address) }}"
                                       class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition font-medium"
                                       placeholder="e.g. Main Ward No. 4, Lahan Road">
                            </div>

                            <div>
                                <label for="city" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                                    City / District
                                </label>
                                <input type="text"
                                       id="city"
                                       name="city"
                                       value="{{ old('city', $user->city) }}"
                                       class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition font-medium"
                                       placeholder="e.g. Siraha">
                            </div>

                            <div>
                                <label for="zip" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                                    Postal / Zip Code
                                </label>
                                <input type="text"
                                       id="zip"
                                       name="zip"
                                       value="{{ old('zip', $user->zip) }}"
                                       class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition font-medium"
                                       placeholder="e.g. 56500">
                            </div>

                            <div>
                                <label for="blood_type" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                                    Blood Group
                                </label>
                                <select id="blood_type"
                                        name="blood_type"
                                        class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition font-medium">
                                    <option value="">Select Blood Group</option>
                                    @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg)
                                    <option value="{{ $bg }}" {{ old('blood_type', $user->blood_type) === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="religion" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                                    Religion
                                </label>
                                <input type="text"
                                       id="religion"
                                       name="religion"
                                       value="{{ old('religion', $user->religion) }}"
                                       class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition font-medium"
                                       placeholder="e.g. Hinduism, Buddhism, etc.">
                            </div>
                        </div>
                    </div>

                    {{-- Form Submit Bar --}}
                    <div class="flex flex-wrap items-center justify-between gap-4 pt-2">
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-7 py-3 bg-indigo-600 hover:bg-indigo-700 active:scale-98 text-white text-sm font-bold rounded-2xl shadow-md hover:shadow-lg transition-all focus:ring-4 focus:ring-indigo-500/20">
                            <i class="bi bi-check2-circle text-base"></i>
                            Save Profile Details
                        </button>
                        <span id="photo-selected-indicator" class="hidden text-xs font-semibold text-emerald-600 dark:text-emerald-400 items-center gap-1.5 animate-pulse">
                            <i class="bi bi-image-fill"></i> New photo selected — click Save to upload
                        </span>
                    </div>

                </div>

                {{-- Right 1 Col: Photo Upload Card & Safety Summary --}}
                <div class="space-y-6">

                    {{-- Photo Uploader Card --}}
                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 text-center space-y-4">
                        <h4 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center justify-center gap-2">
                            <i class="bi bi-camera text-indigo-500"></i> Profile Photo
                        </h4>

                        <div class="w-24 h-24 mx-auto rounded-2xl overflow-hidden ring-4 ring-indigo-500/20 shadow-md bg-indigo-50 dark:bg-slate-800">
                            <img id="card-avatar-preview" src="{{ $user->avatar }}" alt="" class="w-full h-full object-cover">
                        </div>

                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Supported: JPG, PNG, WEBP up to 3MB.<br>Photos are optimized to WebP automatically.
                        </p>

                        <div class="pt-1 flex flex-col gap-2">
                            <label for="profile-photo-input"
                                   class="cursor-pointer inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-50 dark:bg-indigo-950/50 hover:bg-indigo-100 dark:hover:bg-indigo-900 text-indigo-600 dark:text-indigo-400 font-semibold text-xs rounded-xl transition border border-indigo-200/50 dark:border-indigo-800/50">
                                <i class="bi bi-upload"></i> Choose New Photo
                            </label>
                        </div>
                    </div>

                    {{-- Security Quick Summary Card --}}
                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 space-y-4">
                        <h4 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <i class="bi bi-shield-check text-emerald-500"></i> Account Safety
                        </h4>
                        <div class="space-y-3 text-xs">
                            <div class="flex items-center justify-between text-slate-600 dark:text-slate-300">
                                <span>2FA Security</span>
                                @if($user->twoFactorAuth && $user->twoFactorAuth->is_enabled)
                                <span class="text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1">
                                    <i class="bi bi-check-circle-fill"></i> Enabled
                                </span>
                                @else
                                <span class="text-amber-500 font-bold flex items-center gap-1">
                                    <i class="bi bi-exclamation-circle-fill"></i> Not Active
                                </span>
                                @endif
                            </div>
                            <div class="flex items-center justify-between text-slate-600 dark:text-slate-300">
                                <span>Password Policy</span>
                                <span class="text-slate-800 dark:text-slate-200 font-semibold">Strict (8+ chars)</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-600 dark:text-slate-300">
                                <span>Last Updated</span>
                                <span class="text-slate-500">{{ $user->updated_at ? $user->updated_at->diffForHumans() : 'Never' }}</span>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </form>

    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
         TAB 2: SECURITY & PASSWORD
    ══════════════════════════════════════════════════════════════════════ --}}
    <div id="security-tab" class="profile-tab-pane hidden space-y-6">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Change Password Box --}}
            <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8">
                <div class="flex items-center justify-between pb-5 border-b border-slate-100 dark:border-slate-800 mb-6">
                    <div>
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <i class="bi bi-key-fill text-indigo-600 dark:text-indigo-400"></i>
                            Change Account Password
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Ensure your account uses a strong passphrase with letters, numbers, and symbols</p>
                    </div>
                </div>

                <form action="{{ route('profile.password.update') }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PUT')

                    {{-- Current Password --}}
                    <div>
                        <label for="current_password" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                            Current Password <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password"
                                   id="current_password"
                                   name="current_password"
                                   class="w-full px-4 pe-11 py-2.5 text-sm rounded-xl border @error('current_password') border-rose-400 bg-rose-50/20 @else border-slate-200 dark:border-slate-700 @enderror bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition font-medium"
                                   placeholder="Enter your current password"
                                   autocomplete="current-password"
                                   required>
                            <button type="button"
                                    onclick="togglePasswordVisibility('current_password', this)"
                                    class="absolute inset-y-0 end-0 flex items-center pe-3.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition"
                                    aria-label="Toggle password visibility">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        @error('current_password')
                            <p class="text-xs text-rose-500 mt-1 flex items-center gap-1"><i class="bi bi-exclamation-circle"></i> {{ $message }}</p>
                        @enderror
                    </div>

                    {{-- New Password --}}
                    <div>
                        <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                            New Password <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password"
                                   id="password"
                                   name="password"
                                   class="w-full px-4 pe-11 py-2.5 text-sm rounded-xl border @error('password') border-rose-400 bg-rose-50/20 @else border-slate-200 dark:border-slate-700 @enderror bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition font-medium"
                                   placeholder="Create a new strong password"
                                   autocomplete="new-password"
                                   oninput="checkPasswordStrength(this.value)"
                                   required>
                            <button type="button"
                                    onclick="togglePasswordVisibility('password', this)"
                                    class="absolute inset-y-0 end-0 flex items-center pe-3.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition"
                                    aria-label="Toggle password visibility">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-xs text-rose-500 mt-1 flex items-center gap-1"><i class="bi bi-exclamation-circle"></i> {{ $message }}</p>
                        @enderror

                        {{-- Animated Strength Bar --}}
                        <div class="mt-2.5 space-y-1.5">
                            <div class="h-2 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden flex">
                                <div id="pwd-strength-bar" class="h-full w-0 transition-all duration-300 rounded-full"></div>
                            </div>
                            <div class="flex items-center justify-between text-[11px]">
                                <span id="pwd-strength-text" class="text-slate-400 font-medium">Password strength</span>
                                <span id="pwd-strength-score" class="text-slate-400">0/4</span>
                            </div>
                        </div>
                    </div>

                    {{-- Confirm Password --}}
                    <div>
                        <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                            Confirm New Password <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password"
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   class="w-full px-4 pe-11 py-2.5 text-sm rounded-xl border @error('password_confirmation') border-rose-400 bg-rose-50/20 @else border-slate-200 dark:border-slate-700 @enderror bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition font-medium"
                                   placeholder="Re-enter your new password"
                                   autocomplete="new-password"
                                   required>
                            <button type="button"
                                    onclick="togglePasswordVisibility('password_confirmation', this)"
                                    class="absolute inset-y-0 end-0 flex items-center pe-3.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition"
                                    aria-label="Toggle password visibility">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        @error('password_confirmation')
                            <p class="text-xs text-rose-500 mt-1 flex items-center gap-1"><i class="bi bi-exclamation-circle"></i> {{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-3">
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-7 py-3 bg-slate-900 dark:bg-indigo-600 hover:bg-slate-800 dark:hover:bg-indigo-700 text-white text-sm font-bold rounded-2xl shadow-md hover:shadow-lg transition-all focus:ring-4 focus:ring-indigo-500/20">
                            <i class="bi bi-shield-check text-base"></i>
                            Update Password
                        </button>
                    </div>
                </form>
            </div>

            {{-- Security Policy & 2FA Setup Card --}}
            <div class="space-y-6">

                {{-- Password Requirements Card --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 space-y-3.5">
                    <h4 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <i class="bi bi-shield-lock-fill text-indigo-500"></i> Password Rules
                    </h4>
                    <ul class="space-y-2 text-xs text-slate-600 dark:text-slate-300">
                        <li id="rule-len" class="flex items-center gap-2 text-slate-400">
                            <i class="bi bi-dash-circle"></i> At least 8 characters in length
                        </li>
                        <li id="rule-case" class="flex items-center gap-2 text-slate-400">
                            <i class="bi bi-dash-circle"></i> Upper and lowercase letters
                        </li>
                        <li id="rule-num" class="flex items-center gap-2 text-slate-400">
                            <i class="bi bi-dash-circle"></i> At least one numeric digit (0-9)
                        </li>
                        <li id="rule-sym" class="flex items-center gap-2 text-slate-400">
                            <i class="bi bi-dash-circle"></i> At least one special symbol (!@#$%^&*)
                        </li>
                    </ul>
                </div>

                {{-- Two-Factor Auth Box --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 space-y-3.5">
                    <h4 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <i class="bi bi-phone text-indigo-500"></i> Two-Factor Authentication
                    </h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Add an extra layer of protection to prevent unauthorized access using Google Authenticator or Microsoft Authenticator.
                    </p>
                    <a href="{{ route('two-factor.setup') }}"
                       class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold bg-indigo-50 dark:bg-indigo-950/50 hover:bg-indigo-100 dark:hover:bg-indigo-900 text-indigo-600 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-800/60 transition">
                        <i class="bi bi-shield-lock"></i> Configure 2FA Settings
                    </a>
                </div>

            </div>

        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
         TAB 3: ROLE & ACCOUNT PRIVILEGES
    ══════════════════════════════════════════════════════════════════════ --}}
    <div id="roles-tab" class="profile-tab-pane hidden space-y-6">

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Role Information --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8 space-y-4">
                <div class="pb-4 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <i class="bi bi-shield-shaded text-indigo-600 dark:text-indigo-400"></i>
                        Assigned System Roles & Privileges
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Your access level and permissions within the school portal</p>
                </div>

                <div class="space-y-4 text-xs">
                    <div>
                        <span class="text-slate-400 uppercase tracking-wider font-semibold text-[10px]">Primary Role</span>
                        <div class="mt-1 flex items-center gap-2">
                            <span class="px-3 py-1 rounded-xl text-xs font-bold bg-indigo-600 text-white uppercase tracking-wider">
                                {{ str_replace('-', ' ', $user->primary_role) }}
                            </span>
                            @foreach($user->roles as $role)
                                @if($role->name !== $user->primary_role)
                                <span class="px-2.5 py-1 rounded-xl text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                    {{ $role->name }}
                                </span>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <span class="text-slate-400 uppercase tracking-wider font-semibold text-[10px]">Account Scope</span>
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 mt-0.5">
                            @if($user->isSuperAdmin())
                                System-wide Super Administrator (All Schools)
                            @else
                                Institution / Tenant Level Administrator
                            @endif
                        </p>
                    </div>

                    <div>
                        <span class="text-slate-400 uppercase tracking-wider font-semibold text-[10px]">Active Permissions</span>
                        <div class="mt-2 flex flex-wrap gap-1.5 max-h-48 overflow-y-auto p-2 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                            @forelse($user->getAllPermissions() as $permission)
                            <span class="px-2 py-0.5 rounded-lg text-[11px] font-medium bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600">
                                {{ $permission->name }}
                            </span>
                            @empty
                            <span class="text-slate-400 italic text-[11px]">Inherits all global administrator privileges.</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- School / Institution Info --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8 space-y-4">
                <div class="pb-4 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <i class="bi bi-building text-indigo-600 dark:text-indigo-400"></i>
                        Institution / Organization
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">School tenant association and metadata</p>
                </div>

                @if($user->school)
                <div class="space-y-3.5 text-xs">
                    <div class="flex justify-between items-center py-2 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-400">School Name</span>
                        <span class="font-bold text-slate-800 dark:text-slate-100 text-sm">{{ $user->school->name }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-400">School Code</span>
                        <span class="font-semibold text-slate-700 dark:text-slate-300 font-mono">{{ $user->school->code ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-400">School Email</span>
                        <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $user->school->email ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-slate-400">School Address</span>
                        <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $user->school->address ?? 'N/A' }}</span>
                    </div>
                </div>
                @else
                <div class="py-8 text-center text-slate-400 text-xs">
                    <i class="bi bi-globe text-3xl mb-2 block text-slate-300 dark:text-slate-600"></i>
                    Global Super Administrator account not bound to a single school tenant.
                </div>
                @endif
            </div>

        </div>

    </div>

    <div class="mt-8">
        @include('layouts.footer')
    </div>
</div>

<script>
// Switch profile navigation tabs
function switchProfileTab(tabId) {
    document.querySelectorAll('.profile-tab-pane').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.profile-nav-tab').forEach(el => {
        el.className = 'profile-nav-tab inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 transition-all';
    });

    const targetPane = document.getElementById(tabId);
    if (targetPane) targetPane.classList.remove('hidden');

    const btnMap = {
        'personal-tab': 'tab-btn-personal',
        'security-tab': 'tab-btn-security',
        'roles-tab': 'tab-btn-roles'
    };

    const targetBtn = document.getElementById(btnMap[tabId]);
    if (targetBtn) {
        targetBtn.className = 'profile-nav-tab inline-flex items-center gap-2 px-4 py-2 text-xs font-bold rounded-xl transition-all shadow-xs bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 border border-slate-200/80 dark:border-slate-700';
    }

    if (history.replaceState) {
        history.replaceState(null, null, '#' + tabId.replace('-tab', ''));
    }
}

// Check URL hash on load
document.addEventListener('DOMContentLoaded', () => {
    const hash = window.location.hash.replace('#', '');
    if (hash === 'security') switchProfileTab('security-tab');
    else if (hash === 'roles') switchProfileTab('roles-tab');
});

// Toggle password show/hide
function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        if (icon) {
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        }
    } else {
        input.type = 'password';
        if (icon) {
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }
}

// Instant client-side avatar preview
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewHero = document.getElementById('hero-avatar-preview');
            const previewCard = document.getElementById('card-avatar-preview');
            if (previewHero) previewHero.src = e.target.result;
            if (previewCard) previewCard.src = e.target.result;

            const indicator = document.getElementById('photo-selected-indicator');
            if (indicator) {
                indicator.classList.remove('hidden');
                indicator.classList.add('inline-flex');
            }
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Copy to clipboard with tooltip feedback
function copyToClipboard(text, btn) {
    if (!navigator.clipboard) return;
    navigator.clipboard.writeText(text).then(() => {
        const icon = btn.querySelector('i');
        if (icon) {
            const originalClass = icon.className;
            icon.className = 'bi bi-check-lg text-emerald-400 font-bold';
            setTimeout(() => {
                icon.className = originalClass;
            }, 1800);
        }
    });
}

// Dynamic Password strength & requirements checker
function checkPasswordStrength(password) {
    const bar = document.getElementById('pwd-strength-bar');
    const text = document.getElementById('pwd-strength-text');
    const scoreText = document.getElementById('pwd-strength-score');
    if (!bar || !text) return;

    const hasLength = password.length >= 8;
    const hasCase = /[a-z]/.test(password) && /[A-Z]/.test(password);
    const hasNum = /[0-9]/.test(password);
    const hasSym = /[^A-Za-z0-9]/.test(password);

    updateRuleItem('rule-len', hasLength);
    updateRuleItem('rule-case', hasCase);
    updateRuleItem('rule-num', hasNum);
    updateRuleItem('rule-sym', hasSym);

    let score = 0;
    if (hasLength) score += 1;
    if (hasCase) score += 1;
    if (hasNum) score += 1;
    if (hasSym) score += 1;

    if (scoreText) scoreText.innerText = score + '/4';

    if (password.length === 0) {
        bar.style.width = '0%';
        bar.className = 'h-full transition-all duration-300 rounded-full';
        text.innerText = 'Password strength';
        text.className = 'text-slate-400 font-medium';
    } else if (score === 1) {
        bar.style.width = '25%';
        bar.className = 'h-full transition-all duration-300 rounded-full bg-rose-500';
        text.innerText = 'Very Weak';
        text.className = 'text-rose-500 font-bold';
    } else if (score === 2) {
        bar.style.width = '50%';
        bar.className = 'h-full transition-all duration-300 rounded-full bg-amber-500';
        text.innerText = 'Fair';
        text.className = 'text-amber-500 font-bold';
    } else if (score === 3) {
        bar.style.width = '75%';
        bar.className = 'h-full transition-all duration-300 rounded-full bg-blue-500';
        text.innerText = 'Good';
        text.className = 'text-blue-500 font-bold';
    } else {
        bar.style.width = '100%';
        bar.className = 'h-full transition-all duration-300 rounded-full bg-emerald-500';
        text.innerText = 'Excellent & Secure!';
        text.className = 'text-emerald-500 font-bold';
    }
}

function updateRuleItem(elementId, passed) {
    const el = document.getElementById(elementId);
    if (!el) return;
    const icon = el.querySelector('i');
    if (passed) {
        el.className = 'flex items-center gap-2 text-emerald-600 dark:text-emerald-400 font-medium';
        if (icon) icon.className = 'bi bi-check-circle-fill text-emerald-500';
    } else {
        el.className = 'flex items-center gap-2 text-slate-400';
        if (icon) icon.className = 'bi bi-dash-circle';
    }
}
</script>
@endsection
