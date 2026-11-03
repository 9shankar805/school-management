@extends('layouts.app')
@section('title', 'SaaS Staff & Team Management - NepalSchoolHub')

@section('content')
<div class="px-6 py-6 max-w-7xl mx-auto space-y-6">

    {{-- Top Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300">
                    SaaS Operations
                </span>
                <span class="text-xs text-slate-400">&bull; कर्मचारी तथा पहुँच नियन्त्रण</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1 flex items-center gap-2.5">
                <i class="bi bi-people-fill text-indigo-600"></i>
                SaaS Staff &amp; Team Management (साँस कर्मचारी व्यवस्थापन)
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                Add platform staff, assign specialized roles, and control granular management permissions for handling schools, billing, and agreements.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('super.staff.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm shadow-sm shadow-indigo-200 dark:shadow-none transition-all">
                <i class="bi bi-person-plus-fill"></i>
                Add SaaS Staff (नयाँ कर्मचारी थप्नुहोस्)
            </a>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-sm flex items-center gap-2">
            <i class="bi bi-check-circle-fill text-emerald-600 text-base"></i>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-sm flex items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill text-rose-600 text-base"></i>
            {{ session('error') }}
        </div>
    @endif

    {{-- Metric Stat Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-medium">Total SaaS Staff</p>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white">{{ $stats['total'] }}</h3>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center text-lg">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-medium">Platform Admins</p>
                    <h3 class="text-xl font-black text-blue-600 dark:text-blue-400">{{ $stats['admins'] }}</h3>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg">
                    <i class="bi bi-receipt"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-medium">Billing Officers</p>
                    <h3 class="text-xl font-black text-emerald-600 dark:text-emerald-400">{{ $stats['billing'] }}</h3>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg">
                    <i class="bi bi-headset"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-medium">Support &amp; Onboarding</p>
                    <h3 class="text-xl font-black text-amber-600 dark:text-amber-400">{{ $stats['support'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 shadow-xs">
        <form action="{{ route('super.staff.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[200px] relative">
                <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search staff by name, email, phone..."
                    class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:text-white">
            </div>

            <div class="w-48">
                <select name="role" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:text-white">
                    <option value="">All Roles (सबै पदहरू)</option>
                    <option value="super-admin" {{ request('role') == 'super-admin' ? 'selected' : '' }}>Super Admin</option>
                    <option value="saas-admin" {{ request('role') == 'saas-admin' ? 'selected' : '' }}>SaaS Admin</option>
                    <option value="saas-billing" {{ request('role') == 'saas-billing' ? 'selected' : '' }}>Billing Officer</option>
                    <option value="saas-support" {{ request('role') == 'saas-support' ? 'selected' : '' }}>Support Officer</option>
                    <option value="saas-staff" {{ request('role') == 'saas-staff' ? 'selected' : '' }}>General Staff</option>
                </select>
            </div>

            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white dark:bg-slate-700 dark:hover:bg-slate-600 rounded-xl text-xs font-semibold transition">
                Filter
            </button>
            @if(request()->hasAny(['search', 'role']))
                <a href="{{ route('super.staff.index') }}" class="px-3 py-2 text-xs text-slate-500 hover:text-slate-800 dark:hover:text-slate-200">
                    Clear
                </a>
            @endif
        </form>
    </div>

    {{-- Staff Table --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 dark:bg-slate-900/40 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200/80 dark:border-slate-700/80">
                    <tr>
                        <th class="px-5 py-3.5">Staff Member</th>
                        <th class="px-4 py-3.5">Role</th>
                        <th class="px-4 py-3.5">Contact Details</th>
                        <th class="px-4 py-3.5">Assigned Permissions</th>
                        <th class="px-4 py-3.5">Created</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 font-medium">
                    @forelse($staffMembers as $staff)
                        @php
                            $roleName = $staff->getRoleNames()->first() ?? $staff->role ?? 'saas-staff';
                            $directPermissions = $staff->getAllPermissions();
                        @endphp
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-750 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-indigo-600 to-violet-500 text-white text-xs font-bold flex items-center justify-center flex-shrink-0 shadow-xs">
                                        {{ strtoupper(substr($staff->first_name ?? 'S', 0, 1) . substr($staff->last_name ?? 'T', 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900 dark:text-white text-sm">
                                            {{ $staff->full_name }}
                                            @if($staff->id === auth()->id())
                                                <span class="ml-1.5 px-1.5 py-0.5 rounded text-[10px] bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300 font-semibold">You</span>
                                            @endif
                                        </p>
                                        <p class="text-[11px] text-slate-400">{{ $staff->email }}</p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if($roleName === 'super-admin')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                                        <i class="bi bi-star-fill text-purple-500 text-[9px]"></i> Super Admin
                                    </span>
                                @elseif($roleName === 'saas-admin')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                        <i class="bi bi-shield-lock-fill text-blue-500 text-[9px]"></i> SaaS Admin
                                    </span>
                                @elseif($roleName === 'saas-billing')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                        <i class="bi bi-receipt text-emerald-500 text-[9px]"></i> Billing Officer
                                    </span>
                                @elseif($roleName === 'saas-support')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                        <i class="bi bi-headset text-amber-500 text-[9px]"></i> Support Officer
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300">
                                        {{ ucfirst($roleName) }}
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap text-slate-600 dark:text-slate-300">
                                <div class="flex flex-col gap-0.5">
                                    <span><i class="bi bi-telephone text-slate-400 me-1"></i>{{ $staff->phone ?: '—' }}</span>
                                </div>
                            </td>

                            <td class="px-4 py-3.5">
                                @if($roleName === 'super-admin')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                                        <i class="bi bi-check2-all text-xs"></i> Full Unrestricted Platform Access
                                    </span>
                                @elseif($directPermissions->isEmpty())
                                    <span class="text-slate-400 italic text-[11px]">No specific permissions assigned</span>
                                @else
                                    <div class="flex flex-wrap gap-1 max-w-sm">
                                        @foreach($directPermissions->take(3) as $perm)
                                            <span class="px-2 py-0.5 rounded text-[10px] bg-slate-100 text-slate-700 dark:bg-slate-700/60 dark:text-slate-300 font-mono">
                                                {{ str_replace('manage saas ', '', $perm->name) }}
                                            </span>
                                        @endforeach
                                        @if($directPermissions->count() > 3)
                                            <span class="px-1.5 py-0.5 rounded text-[10px] bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-300 font-bold">
                                                +{{ $directPermissions->count() - 3 }} more
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap text-slate-400 text-[11px]">
                                {{ $staff->created_at ? $staff->created_at->format('M d, Y') : '—' }}
                            </td>

                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('super.staff.edit', $staff) }}"
                                        class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-medium text-xs transition"
                                        title="Edit Staff & Permissions">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>

                                    @if($staff->id !== auth()->id() && !$staff->isSuperAdmin())
                                        <form action="{{ route('super.staff.destroy', $staff) }}" method="POST"
                                              onsubmit="return confirm('Are you sure you want to remove {{ addslashes($staff->full_name) }} from SaaS staff?');"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/30 dark:hover:bg-rose-900/50 text-rose-600 dark:text-rose-400 font-medium text-xs transition"
                                                title="Delete Staff Member">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <i class="bi bi-people text-3xl mb-2 block opacity-50"></i>
                                No SaaS staff members found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($staffMembers->hasPages())
            <div class="px-5 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/30">
                {{ $staffMembers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
