@extends('layouts.app')

@section('title', 'Manage User Roles - NepalSchoolHub')

@section('content')
<div class="flex-1 p-4 sm:p-6 lg:p-10 overflow-y-auto space-y-6 bg-slate-50/60 dark:bg-slate-950 min-h-screen">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('roles.assignable-users') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline mb-2">
                <i class="bi bi-arrow-left"></i> Back to Assignable Users
            </a>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-600 text-white flex items-center justify-center shadow-lg shadow-indigo-500/25 flex-shrink-0">
                    <i class="bi bi-person-gear text-lg"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Assign Roles to User</h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Configure authorization membership for <span class="font-bold text-slate-800 dark:text-slate-200">{{ $user->full_name }}</span></p>
                </div>
            </div>
        </div>

        {{-- User Profile Card Header --}}
        <div class="flex items-center gap-3 bg-white dark:bg-slate-900 px-4 py-2.5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs">
            <img src="{{ $user->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($user->full_name).'&background=6366f1&color=fff&size=64' }}"
                 class="w-11 h-11 rounded-xl object-cover border border-slate-200 dark:border-slate-700 flex-shrink-0" alt="">
            <div>
                <span class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm block">{{ $user->full_name }}</span>
                <span class="text-[11px] text-slate-400 dark:text-slate-500">{{ $user->email }}</span>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="px-4 py-3.5 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-800 dark:text-emerald-300 text-sm rounded-2xl border border-emerald-200 dark:border-emerald-900/50 flex items-center justify-between shadow-xs">
        <div class="flex items-center gap-2">
            <i class="bi bi-check-circle-fill text-emerald-500"></i>
            <span class="font-medium text-xs sm:text-sm">{{ session('success') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 text-xs">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    @endif

    {{-- Current roles summary --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-5">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-3 flex items-center gap-2">
            <i class="bi bi-person-badge text-indigo-500"></i> Currently Assigned Roles
        </h3>
        @if(count($userRoles))
            <div class="flex flex-wrap gap-2">
                @foreach($userRoles as $r)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-900/60 capitalize">
                    <i class="bi bi-shield-check text-[11px]"></i>
                    {{ ucwords(str_replace('-', ' ', $r)) }}
                </span>
                @endforeach
            </div>
        @else
            <p class="text-xs text-slate-400 italic">No roles assigned yet. Check the boxes below to grant access.</p>
        @endif
    </div>

    <form method="POST" action="{{ route('roles.user-roles.update', $user) }}">
        @csrf @method('PUT')

        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/60 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    <h3 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100 uppercase tracking-wider">
                        Available Roles
                    </h3>
                </div>
                <div class="flex gap-2 text-xs">
                    <button type="button" onclick="toggleAllRoles(true)" class="px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-50 font-semibold transition active:scale-95">Select All</button>
                    <button type="button" onclick="toggleAllRoles(false)" class="px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-50 font-semibold transition active:scale-95">Clear All</button>
                </div>
            </div>
            <div class="p-5 sm:p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3.5">
                    @foreach($roles as $role)
                        @php
                            $disabled = $role->name === 'super-admin' && !auth()->user()->hasRole('super-admin');
                            $checked = in_array($role->name, $userRoles, true);
                            $isSystem = in_array($role->name, ['super-admin','admin','teacher','student','parent']);
                        @endphp
                        <label class="group relative flex items-start gap-3 p-3.5 rounded-2xl border cursor-pointer transition select-none
                                      {{ $checked ? 'border-indigo-300 dark:border-indigo-700 bg-indigo-50/40 dark:bg-indigo-950/40' : 'border-slate-200 dark:border-slate-800 hover:border-indigo-300 dark:hover:border-indigo-700 hover:bg-slate-50 dark:hover:bg-slate-800/40' }}
                                      {{ $disabled ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' }}">
                            <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                                   class="mt-0.5 w-4 h-4 rounded text-indigo-600 border-slate-300 dark:border-slate-700 focus:ring-indigo-500 role-checkbox cursor-pointer"
                                   {{ $checked ? 'checked' : '' }}
                                   {{ $disabled ? 'disabled' : '' }}>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-200 capitalize truncate">{{ str_replace('-', ' ', $role->name) }}</span>
                                    @if($isSystem)
                                        <span class="text-[9px] uppercase font-bold tracking-wider px-1.5 py-0.5 bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 rounded border border-purple-200 dark:border-purple-800">Sys</span>
                                    @endif
                                </div>
                                <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">
                                    {{ $role->permissions()->count() }} perms &bull; {{ $role->users()->count() }} users
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="sticky bottom-6 flex justify-center gap-3 z-20">
            <button type="submit" class="px-8 py-3.5 bg-gradient-to-r from-indigo-600 via-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white text-xs sm:text-sm font-bold rounded-2xl shadow-xl shadow-indigo-600/30 transition transform hover:-translate-y-0.5 active:scale-95 flex items-center gap-2">
                <i class="bi bi-check2-circle text-base"></i>
                <span>Save Role Assignment</span>
            </button>
            <a href="{{ route('roles.assignable-users') }}" class="px-6 py-3.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 text-xs sm:text-sm font-semibold rounded-2xl shadow-sm hover:bg-slate-100 dark:hover:bg-slate-800 transition active:scale-95 flex items-center">
                Cancel
            </a>
        </div>
    </form>

</div>

<script>
function toggleAllRoles(state) {
    document.querySelectorAll('.role-checkbox:not(:disabled)').forEach(cb => cb.checked = state);
}
</script>
@endsection
