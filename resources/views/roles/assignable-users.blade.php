@extends('layouts.app')
@section('content')
<div class="flex-1 p-6 lg:p-10 overflow-auto">

    <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
        <div>
            <a href="{{ route('roles.index') }}" class="text-xs text-slate-400 hover:text-slate-600 flex items-center gap-1 mb-2">
                <i class="bi bi-arrow-left"></i> Back to Roles
            </a>
            <h1 class="text-2xl font-bold text-slate-800">Assign Roles to Users</h1>
            <p class="text-slate-500 text-sm mt-1">Pick a user below to manage their assigned roles & permissions</p>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-5 px-4 py-3 bg-emerald-50 text-emerald-700 text-sm rounded-lg border border-emerald-200">
        <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
    </div>
    @endif

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-2 mb-6" action="{{ route('roles.assignable-users') }}">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search by name, email, phone…"
               class="flex-1 min-w-56 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
        <select name="role" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            <option value="">All Roles</option>
            @foreach($assignableRoles as $slug => $label)
                <option value="{{ $slug }}" {{ request('role') == $slug ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition">
            <i class="bi bi-funnel me-1"></i>Filter
        </button>
        @if(request('search') || request('role'))
            <a href="{{ route('roles.assignable-users') }}" class="px-4 py-2 bg-white border border-slate-200 text-slate-500 rounded-lg text-sm hover:bg-slate-50 transition">
                Clear
            </a>
        @endif
    </form>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-slate-400 bg-slate-50 border-b border-slate-100">
                        <th class="px-5 py-3 font-semibold">User</th>
                        <th class="px-5 py-3 font-semibold">Email</th>
                        <th class="px-5 py-3 font-semibold">Phone</th>
                        <th class="px-5 py-3 font-semibold">Current Roles</th>
                        <th class="px-5 py-3 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($users as $u)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <img src="{{ $u->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($u->full_name).'&background=6366f1&color=fff&size=48' }}"
                                     class="w-9 h-9 rounded-lg object-cover border border-slate-100" alt="">
                                <div class="min-w-0">
                                    <div class="font-medium text-slate-700">{{ $u->full_name }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $u->gender ?? '—' }} · {{ $u->city ?? '—' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-slate-600">{{ $u->email }}</td>
                        <td class="px-5 py-3.5 text-slate-500 text-xs">{{ $u->phone ?? '—' }}</td>
                        <td class="px-5 py-3.5">
                            @php $rnames = $u->getRoleNames(); @endphp
                            @if($rnames->count())
                                <div class="flex flex-wrap gap-1">
                                    @foreach($rnames as $rn)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium
                                                     {{ in_array($rn,['super-admin','admin']) ? 'bg-rose-50 text-rose-700'
                                                        : (in_array($rn,['teacher','class-teacher']) ? 'bg-blue-50 text-blue-700'
                                                        : (in_array($rn,['student','parent']) ? 'bg-emerald-50 text-emerald-700'
                                                        : 'bg-indigo-50 text-indigo-700')) }}">
                                            {{ ucwords(str_replace('-',' ',$rn)) }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-[11px] text-slate-400 italic">— no roles —</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <a href="{{ route('roles.user-roles', $u) }}"
                               class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg
                                          bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition">
                                <i class="bi bi-person-check"></i> Assign Roles
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-16 text-center">
                            <i class="bi bi-people text-4xl text-slate-200"></i>
                            <p class="mt-3 text-slate-400">No users match your filter.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-5 py-3 border-t border-slate-100 bg-slate-50">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
