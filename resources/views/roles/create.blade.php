@extends('layouts.app')

@section('title', 'Create New Role - NepalSchoolHub')

@section('content')
<div class="flex-1 p-4 sm:p-6 lg:p-10 overflow-y-auto space-y-6 bg-slate-50/60 dark:bg-slate-950 min-h-screen">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('roles.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline mb-2">
                <i class="bi bi-arrow-left"></i> Back to Roles & Permissions
            </a>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-600 text-white flex items-center justify-center shadow-lg shadow-indigo-500/25 flex-shrink-0">
                    <i class="bi bi-shield-plus text-lg"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Create New Custom Role</h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Define a new authorization role with tailored capabilities</p>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('roles.store') }}">
        @csrf

        {{-- Role Name Input Card --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 mb-6">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">Role Name / Identifier</label>
            <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. department-head, lab-assistant, bus-coordinator"
                class="w-full max-w-md px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
            @error('name')<p class="mt-1.5 text-xs text-rose-500 font-semibold">{{ $message }}</p>@enderror
            <p class="mt-2 text-xs text-slate-400">Recommended format: lowercase alphanumeric with hyphens (e.g. <code class="font-mono bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded text-indigo-600 dark:text-indigo-400">lab-incharge</code>)</p>
        </div>

        {{-- Module Permissions --}}
        <div class="space-y-5 mb-8">
            @foreach($permissions as $group => $groupPerms)
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-6 py-3.5 bg-slate-50/80 dark:bg-slate-800/60 border-b border-slate-200/80 dark:border-slate-800 flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                        <p class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100 uppercase tracking-wider">{{ $group }} Module</p>
                    </div>
                </div>
                <div class="p-5 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                    @foreach($groupPerms as $perm)
                    <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50 cursor-pointer transition select-none">
                        <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                            class="rounded-lg border-slate-300 dark:border-slate-700 text-indigo-600 focus:ring-indigo-500 w-4 h-4 cursor-pointer"
                            {{ in_array($perm->id, old('permissions', [])) ? 'checked' : '' }}>
                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $perm->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

        <div class="sticky bottom-6 flex justify-center gap-3 z-20">
            <button type="submit" class="px-8 py-3.5 bg-gradient-to-r from-indigo-600 via-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white text-xs sm:text-sm font-bold rounded-2xl shadow-xl shadow-indigo-600/30 transition transform hover:-translate-y-0.5 active:scale-95 flex items-center gap-2">
                <i class="bi bi-plus-circle text-base"></i>
                <span>Create Role</span>
            </button>
            <a href="{{ route('roles.index') }}" class="px-6 py-3.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 text-xs sm:text-sm font-semibold rounded-2xl shadow-sm hover:bg-slate-100 dark:hover:bg-slate-800 transition active:scale-95 flex items-center">
                Cancel
            </a>
        </div>
    </form>

</div>
@endsection
