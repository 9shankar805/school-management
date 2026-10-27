@extends('layouts.app')

@section('title', 'Staff Directory - School Management')

@section('content')
<div class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto space-y-6 bg-slate-50/60 dark:bg-slate-950 min-h-screen">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400">
        <a href="{{ route('home') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition flex items-center gap-1">
            <i class="bi bi-house-door"></i> Home
        </a>
        <i class="bi bi-chevron-right text-[10px]"></i>
        <span class="text-slate-800 dark:text-slate-200">Staff Directory</span>
    </nav>

    {{-- Top Status Notifications --}}
    @if(session('status'))
    <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-200 rounded-2xl text-xs sm:text-sm font-semibold flex items-center justify-between shadow-xs">
        <div class="flex items-center gap-2.5">
            <i class="bi bi-check-circle-fill text-emerald-600 dark:text-emerald-400 text-base"></i>
            <span>{{ session('status') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 dark:hover:text-emerald-300">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 to-purple-600 text-white flex items-center justify-center shadow-lg shadow-indigo-500/25 text-xl font-bold">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Staff Directory</h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                        Manage non-teaching faculty, accountants, officers, librarians, and administrative members
                    </p>
                </div>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @can('create staff')
            <a href="{{ route('staff.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm shadow-indigo-600/25 transition active:scale-95 cursor-pointer">
                <i class="bi bi-person-plus-fill"></i> Add Staff Member
            </a>
            @endcan
            <a href="{{ route('staff.attendance.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 transition shadow-xs">
                <i class="bi bi-calendar-check"></i> Staff Attendance
            </a>
            <a href="{{ route('staff.contracts.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 transition shadow-xs">
                <i class="bi bi-file-earmark-text"></i> Contracts
            </a>
        </div>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-4 sm:p-5 shadow-xs">
        <form method="GET" action="{{ route('staff.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            {{-- Search Query --}}
            <div class="sm:col-span-6 lg:col-span-5 relative">
                <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ $search ?? request('search') }}" placeholder="Search staff by name, email, phone…"
                    class="w-full pl-9 pr-3.5 py-2 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-hidden transition">
            </div>

            {{-- Role Filter --}}
            <div class="sm:col-span-4 lg:col-span-4">
                <select name="role" class="w-full px-3.5 py-2 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-hidden transition">
                    <option value="">All Staff Roles</option>
                    @foreach($roles as $slug => $info)
                    <option value="{{ $slug }}" @selected(($role ?? request('role')) === $slug)>
                        {{ $info['label'] ?? ucwords(str_replace('-', ' ', $slug)) }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Buttons --}}
            <div class="sm:col-span-2 lg:col-span-3 flex items-center gap-2">
                <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs sm:text-sm font-semibold transition cursor-pointer">
                    <i class="bi bi-funnel-fill"></i> Filter
                </button>
                @if(request('search') || request('role'))
                <a href="{{ route('staff.index') }}" class="px-3 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-semibold hover:bg-slate-200 dark:hover:bg-slate-700 transition" title="Clear Filters">
                    <i class="bi bi-x-lg"></i>
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Staff Table --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800/80 bg-slate-50/60 dark:bg-slate-800/40 text-[11px] uppercase tracking-wider text-slate-400 font-bold">
                        <th class="py-3.5 px-5">Staff Member</th>
                        <th class="py-3.5 px-4">Contact Info</th>
                        <th class="py-3.5 px-4">Assigned Role</th>
                        <th class="py-3.5 px-4">Location</th>
                        <th class="py-3.5 px-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-xs sm:text-sm">
                    @forelse($staffs as $staff)
                    @php
                        $roleSlug = $staff->roles->first()?->name ?? $staff->role ?? 'staff';
                        $roleInfo = $roles[$roleSlug] ?? ['label' => ucwords(str_replace('-', ' ', $roleSlug)), 'icon' => 'bi-person-badge'];
                    @endphp
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                        {{-- Avatar & Name --}}
                        <td class="py-3.5 px-5">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-indigo-500/10 to-purple-500/10 border border-indigo-200/60 dark:border-indigo-800/60 text-indigo-600 dark:text-indigo-400 font-black flex items-center justify-center flex-shrink-0 text-sm overflow-hidden shadow-xs">
                                    @if($staff->photo)
                                        <img src="{{ asset('storage/' . ltrim($staff->photo, '/')) }}" class="w-full h-full object-cover">
                                    @else
                                        {{ strtoupper(substr($staff->first_name, 0, 1) . substr($staff->last_name, 0, 1)) }}
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-slate-800 dark:text-slate-100 truncate">
                                        {{ $staff->first_name }} {{ $staff->last_name }}
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        ID #{{ $staff->id }} • Joined {{ $staff->created_at ? $staff->created_at->format('M Y') : '—' }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- Contact Info --}}
                        <td class="py-3.5 px-4">
                            <div class="text-slate-700 dark:text-slate-300 font-medium">
                                <a href="mailto:{{ $staff->email }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 flex items-center gap-1.5">
                                    <i class="bi bi-envelope text-slate-400"></i> {{ $staff->email }}
                                </a>
                            </div>
                            @if($staff->phone)
                            <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1.5">
                                <i class="bi bi-telephone text-slate-400"></i> {{ $staff->phone }}
                            </div>
                            @endif
                        </td>

                        {{-- Role --}}
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-semibold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800/60">
                                <i class="bi {{ $roleInfo['icon'] ?? 'bi-person-badge' }} text-[11px]"></i>
                                {{ $roleInfo['label'] }}
                            </span>
                        </td>

                        {{-- Location --}}
                        <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400">
                            {{ $staff->city ?: ($staff->address ?: '—') }}
                        </td>

                        {{-- Actions --}}
                        <td class="py-3.5 px-5 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                @can('manage roles')
                                <a href="{{ route('roles.user-roles', $staff->id) }}"
                                   class="p-2 rounded-xl text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 dark:hover:bg-emerald-950/50 transition" title="Assign Granular Roles">
                                    <i class="bi bi-shield-check text-sm"></i>
                                </a>
                                @endcan

                                <a href="{{ route('staff.edit', $staff->id) }}"
                                   class="p-2 rounded-xl text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 transition" title="Edit Profile">
                                    <i class="bi bi-pencil-square text-sm"></i>
                                </a>

                                <form method="POST" action="{{ route('staff.destroy', $staff->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to remove {{ $staff->first_name }} {{ $staff->last_name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 rounded-xl text-rose-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/50 transition cursor-pointer" title="Delete Staff">
                                        <i class="bi bi-trash3 text-sm"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 px-4 text-center">
                            <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center mx-auto text-2xl mb-3">
                                <i class="bi bi-people"></i>
                            </div>
                            <h3 class="font-bold text-slate-800 dark:text-slate-100 text-base">No staff members found</h3>
                            <p class="text-xs text-slate-400 max-w-sm mx-auto mt-1">
                                @if(request('search') || request('role'))
                                    Try adjusting your search criteria or clearing filters.
                                @else
                                    Add your first administrative staff member, accountant, or librarian to get started.
                                @endif
                            </p>
                            @can('create staff')
                            <a href="{{ route('staff.create') }}" class="inline-flex items-center gap-1.5 mt-4 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold transition shadow-sm">
                                <i class="bi bi-person-plus-fill"></i> Add Staff Member
                            </a>
                            @endcan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($staffs, 'hasPages') && $staffs->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $staffs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
