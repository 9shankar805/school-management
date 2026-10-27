@php
    $school = $school ?? $currentSchool ?? config('tenant.school');
    $schoolName = $school ? $school->name : config('app.name', 'NepalSchoolHub ERP');
    $schoolLogo = $school ? $school->logo_url : asset('imgs/logo_icon.webp');
    $primaryColor = $school ? ($school->primary_color_resolved ?: '#4f46e5') : '#4f46e5';
@endphp

@extends('layouts.app')
@section('title', $school ? 'Reset Password · ' . $school->name : 'Reset Password')

@section('content')
<div class="min-h-screen flex items-center justify-center p-6 sm:p-12 relative overflow-hidden"
     style="background: url('{{ asset('imgs/loginbg.webp') }}') center center / cover no-repeat;">

    <div class="absolute inset-0 bg-slate-900/40 dark:bg-slate-950/60 pointer-events-none transition-colors"></div>

    <div class="w-full max-w-md relative rounded-3xl p-8 sm:p-10 auth-glass-card shadow-2xl border border-white/60 dark:border-slate-800/80">

        {{-- Brand header --}}
        <div class="flex items-center gap-3.5 mb-6 p-3 rounded-2xl bg-indigo-50/80 dark:bg-slate-800/80 border border-indigo-100 dark:border-slate-700/80 shadow-sm backdrop-blur-xs">
            <div class="w-11 h-11 rounded-xl bg-white dark:bg-slate-900 p-1 shadow-xs border border-slate-200 dark:border-slate-700 flex items-center justify-center flex-shrink-0">
                <img src="{{ $schoolLogo }}" alt="{{ $schoolName }} logo" class="w-full h-full object-contain rounded-lg">
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="font-bold text-sm text-slate-900 dark:text-white truncate">{{ $schoolName }}</h3>
                <p class="text-xs text-slate-600 dark:text-slate-300 font-medium truncate mt-0.5">Set New Password</p>
            </div>
        </div>

        <div class="mb-6">
            <h2 class="font-bold text-2xl text-slate-900 dark:text-white mb-1">Create New Password</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Enter your email address and new password below.
            </p>
        </div>

        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <div class="form-group-modern">
                <label for="email" class="label-modern text-slate-700 dark:text-slate-300">Email address</label>
                <div class="relative flex items-center">
                    <i class="bi bi-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400 pointer-events-none z-10"></i>
                    <input id="email"
                           type="email"
                           name="email"
                           value="{{ $email ?? old('email') }}"
                           required
                           autofocus
                           autocomplete="email"
                           style="padding-left: 2.75rem !important;"
                           class="input-modern {{ $errors->has('email') ? 'border-red-400' : '' }}">
                </div>
                @error('email')
                    <p class="text-xs text-red-500 mt-1.5 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group-modern">
                <label for="password" class="label-modern text-slate-700 dark:text-slate-300">New Password</label>
                <div class="relative flex items-center">
                    <i class="bi bi-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400 pointer-events-none z-10"></i>
                    <input id="password"
                           type="password"
                           name="password"
                           required
                           autocomplete="new-password"
                           placeholder="••••••••"
                           style="padding-left: 2.75rem !important;"
                           class="input-modern {{ $errors->has('password') ? 'border-red-400' : '' }}">
                </div>
                @error('password')
                    <p class="text-xs text-red-500 mt-1.5 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group-modern">
                <label for="password-confirm" class="label-modern text-slate-700 dark:text-slate-300">Confirm New Password</label>
                <div class="relative flex items-center">
                    <i class="bi bi-shield-check absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400 pointer-events-none z-10"></i>
                    <input id="password-confirm"
                           type="password"
                           name="password_confirmation"
                           required
                           autocomplete="new-password"
                           placeholder="••••••••"
                           style="padding-left: 2.75rem !important;"
                           class="input-modern">
                </div>
            </div>

            <button type="submit"
                    style="background: {{ $primaryColor }}; border-color: {{ $primaryColor }};"
                    class="w-full btn-primary justify-center py-2.5 text-sm font-semibold mt-2 shadow-md hover:brightness-110 transition-all">
                <i class="bi bi-key-fill"></i>
                Reset Password
            </button>

            <div class="text-center pt-3">
                <a href="{{ route('login') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                    &larr; Back to Sign In
                </a>
            </div>
        </form>

        <p class="text-center text-xs mt-8" style="color:var(--text-muted)">
            &copy; {{ date('Y') }} {{ $schoolName }}
        </p>
    </div>
</div>
@endsection
