@extends('layouts.app')

@section('title', 'Assign Roles to Users - NepalSchoolHub')

@section('content')
<div class="flex-1 p-4 sm:p-6 lg:p-10 overflow-y-auto space-y-6 bg-slate-50/60 dark:bg-slate-950 min-h-screen">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('roles.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline mb-2">
                <i class="bi bi-arrow-left"></i> Back to Roles & Permissions
            </a>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-600 text-white flex items-center justify-center shadow-lg shadow-emerald-500/25 flex-shrink-0">
                    <i class="bi bi-person-check-fill text-lg"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Assign Roles to Users</h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Pick any user account to grant, revoke, or synchronize their institutional authorization roles</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Flash Alert --}}
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

    {{-- Search & Filter Controls --}}
    <form method="GET" action="{{ route('roles.assignable-users') }}" class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search by name, email, or phone…"
                   class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
        </div>
        <select name="role" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs sm:text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
            <option value="">All Roles</option>
            @foreach($assignableRoles as $slug => $label)
                <option value="{{ $slug }}" {{ request('role') == $slug ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <div class="flex items-center gap-2">
            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs sm:text-sm font-bold shadow-sm shadow-emerald-600/20 transition active:scale-95">
                <i class="bi bi-funnel me-1"></i> Filter
            </button>
            @if(request('search') || request('role'))
                <a href="{{ route('roles.assignable-users') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs sm:text-sm font-semibold transition">
                    Clear
                </a>
            @endif
        </div>
    </form>

    {{-- Users List --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold text-slate-400 dark:text-slate-500 bg-slate-50/80 dark:bg-slate-800/60 border-b border-slate-200/80 dark:border-slate-800">
                        <th class="px-6 py-3.5">User</th>
                        <th class="px-6 py-3.5">Email</th>
                        <th class="px-6 py-3.5">Phone</th>
                        <th class="px-6 py-3.5">Current Roles</th>
                        <th class="px-6 py-3.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    @forelse($users as $u)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $u->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($u->full_name).'&background=6366f1&color=fff&size=48' }}"
                                     class="w-10 h-10 rounded-2xl object-cover border border-slate-200 dark:border-slate-700 flex-shrink-0" alt="">
                                <div>
                                    <span class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm block">{{ $u->full_name }}</span>
                                    <span class="text-[11px] text-slate-400 dark:text-slate-500">ID: {{ $u->id }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-500 dark:text-slate-400">{{ $u->email }}</td>
                        <td class="px-6 py-4 text-xs text-slate-500 dark:text-slate-400">{{ $u->phone ?? '—' }}</td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @forelse($u->roles as $r)
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2.5 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-900/60 capitalize">
                                        <i class="bi bi-shield-check text-[10px]"></i>
                                        {{ str_replace('-', ' ', $r->name) }}
                                    </span>
                                @empty
                                    <span class="text-xs text-slate-400 italic">No roles assigned</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('roles.user-roles', $u) }}"
                               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-bold text-xs rounded-xl shadow-xs transition active:scale-95">
                                <i class="bi bi-gear-fill text-[11px]"></i>
                                <span>Manage Roles</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center">
                            <i class="bi bi-people text-4xl text-slate-300 dark:text-slate-600 block mb-2"></i>
                            <p class="text-sm font-semibold text-slate-600 dark:text-slate-400">No users found matching your search</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $users->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
