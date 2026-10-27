@extends('layouts.app')

@section('title', 'Change Password')

@section('content')
<div class="px-4 lg:px-8 py-6 max-w-4xl mx-auto">

    {{-- Page Header & Breadcrumbs --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <span class="w-10 h-10 rounded-2xl bg-indigo-600/10 text-indigo-600 flex items-center justify-center font-bold text-xl shadow-xs">
                    <i class="bi bi-key-fill"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Change Password</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Ensure your account uses a strong, secure password</p>
                </div>
            </div>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800/80 px-3 py-1.5 rounded-xl">
                <li><a href="{{ route('home') }}" class="hover:text-indigo-600 transition">Home</a></li>
                <li class="text-slate-300 dark:text-slate-600">/</li>
                <li class="font-semibold text-slate-800 dark:text-slate-200">Change Password</li>
            </ol>
        </nav>
    </div>

    @include('session-messages')

    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 md:p-8">
        <form action="{{ route('password.change.update') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Current Password --}}
            <div>
                <label for="current_password" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">
                    Current Password <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input type="password"
                           id="current_password"
                           name="current_password"
                           class="w-full px-4 py-2.5 pe-11 text-sm rounded-xl border @error('current_password') border-rose-400 bg-rose-50/20 @else border-slate-200 dark:border-slate-700 @enderror bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition"
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
                    <p class="text-xs text-rose-500 mt-1.5 flex items-center gap-1">
                        <i class="bi bi-exclamation-circle"></i> {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- New Password --}}
            <div>
                <label for="password" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">
                    New Password <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input type="password"
                           id="password"
                           name="password"
                           class="w-full px-4 py-2.5 pe-11 text-sm rounded-xl border @error('password') border-rose-400 bg-rose-50/20 @else border-slate-200 dark:border-slate-700 @enderror bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition"
                           placeholder="Enter your new password"
                           autocomplete="new-password"
                           required>
                    <button type="button"
                            onclick="togglePasswordVisibility('password', this)"
                            class="absolute inset-y-0 end-0 flex items-center pe-3.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition"
                            aria-label="Toggle password visibility">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                @error('password')
                    <p class="text-xs text-rose-500 mt-1.5 flex items-center gap-1">
                        <i class="bi bi-exclamation-circle"></i> {{ $message }}
                    </p>
                @enderror

                {{-- Password Requirements Hint --}}
                <div class="mt-3 p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/60 text-xs text-slate-600 dark:text-slate-400 space-y-1">
                    <p class="font-medium text-slate-700 dark:text-slate-300">Password requirements:</p>
                    <ul class="list-disc list-inside space-y-0.5 text-[11px] text-slate-500 dark:text-slate-400">
                        <li>At least 8 characters long</li>
                        <li>Must contain uppercase and lowercase letters</li>
                        <li>Must include at least one number</li>
                        <li>Must include at least one special symbol (e.g. !@#$%^&*)</li>
                    </ul>
                </div>
            </div>

            {{-- Confirm New Password --}}
            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">
                    Confirm New Password <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input type="password"
                           id="password_confirmation"
                           name="password_confirmation"
                           class="w-full px-4 py-2.5 pe-11 text-sm rounded-xl border @error('password_confirmation') border-rose-400 bg-rose-50/20 @else border-slate-200 dark:border-slate-700 @enderror bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition"
                           placeholder="Confirm your new password"
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
                    <p class="text-xs text-rose-500 mt-1.5 flex items-center gap-1">
                        <i class="bi bi-exclamation-circle"></i> {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="pt-2 flex items-center gap-3">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-xs hover:shadow-sm transition focus:ring-2 focus:ring-indigo-500/30">
                    <i class="bi bi-shield-check"></i>
                    Update Password
                </button>
                <a href="{{ route('home') }}"
                   class="px-5 py-2.5 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-xl transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <div class="mt-8">
        @include('layouts.footer')
    </div>
</div>

<script>
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
</script>
@endsection