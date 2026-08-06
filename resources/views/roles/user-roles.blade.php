@extends('layouts.app')
@section('content')
<div class="flex-1 p-6 lg:p-10 overflow-auto">

    <div class="mb-6">
        <a href="{{ url()->previous() == url()->current() ? route('roles.assignable-users') : url()->previous() }}"
           class="text-xs text-slate-400 hover:text-slate-600 flex items-center gap-1 mb-2">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <div class="flex flex-wrap justify-between items-start gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Assign Roles to User</h1>
                <p class="text-slate-500 text-sm mt-1">Grant or revoke roles for <span class="font-semibold text-slate-700">{{ $user->full_name }}</span></p>
            </div>
            <div class="flex items-center gap-3">
                <img src="{{ $user->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($user->full_name).'&background=6366f1&color=fff&size=64' }}"
                     class="w-14 h-14 rounded-xl border border-slate-200 object-cover" alt="">
                <div class="text-right">
                    <div class="font-semibold text-slate-800">{{ $user->full_name }}</div>
                    <div class="text-xs text-slate-500">{{ $user->email }}</div>
                    @if($user->phone)<div class="text-xs text-slate-400">{{ $user->phone }}</div>@endif
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-emerald-50 text-emerald-700 text-sm rounded-lg border border-emerald-200">
        <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
    </div>
    @endif

    {{-- Current roles summary --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-6">
        <h3 class="text-sm font-semibold text-slate-700 mb-3 flex items-center gap-2">
            <i class="bi bi-person-badge text-indigo-500"></i> Currently Assigned Roles
        </h3>
        @if(count($userRoles))
            <div class="flex flex-wrap gap-2">
                @foreach($userRoles as $r)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">
                    <i class="bi bi-shield-check"></i>{{ ucwords(str_replace('-', ' ', $r)) }}
                </span>
                @endforeach
            </div>
        @else
            <p class="text-sm text-slate-400 italic">No roles assigned yet — select roles below and save.</p>
        @endif
    </div>

    <form method="POST" action="{{ route('roles.user-roles.update', $user) }}">
        @csrf @method('PUT')

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                    <i class="bi bi-list-check text-indigo-500"></i> Select Roles
                </h3>
                <div class="flex gap-2 text-xs">
                    <button type="button" onclick="toggleAllRoles(true)" class="px-2.5 py-1 bg-white border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-50 transition">Select All</button>
                    <button type="button" onclick="toggleAllRoles(false)" class="px-2.5 py-1 bg-white border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-50 transition">Clear All</button>
                </div>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                    @foreach($roles as $role)
                        @php
                            $disabled = $role->name === 'super-admin' && !auth()->user()->hasRole('super-admin');
                            $checked = in_array($role->name, $userRoles, true);
                        @endphp
                        <label class="group relative flex items-start gap-3 p-3.5 rounded-xl border cursor-pointer transition
                                      {{ $checked ? 'border-indigo-300 bg-indigo-50/40' : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50' }}
                                      {{ $disabled ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' }}">
                            <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                                   class="mt-0.5 w-4 h-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500 role-checkbox"
                                   {{ $checked ? 'checked' : '' }}
                                   {{ $disabled ? 'disabled' : '' }}>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-sm font-medium text-slate-800 capitalize">{{ str_replace('-', ' ', $role->name) }}</span>
                                    @if(in_array($role->name, ['super-admin','admin','teacher','student','parent']))
                                        <span class="text-[10px] px-1.5 py-0.5 bg-slate-100 text-slate-500 rounded font-medium">system</span>
                                    @endif
                                </div>
                                <div class="text-[11px] text-slate-400 mt-0.5">
                                    {{ $role->permissions()->count() }} permissions · {{ $role->users()->count() }} user(s)
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="px-5 py-4 border-t border-slate-100 bg-slate-50 flex justify-end gap-3">
                <a href="{{ url()->previous() == url()->current() ? route('roles.assignable-users') : url()->previous() }}"
                   class="px-4 py-2 bg-white border border-slate-200 text-sm font-medium rounded-lg hover:bg-slate-50 text-slate-700 transition">
                    Cancel
                </a>
                <button type="submit" class="px-5 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition shadow-sm">
                    <i class="bi bi-save me-1"></i> Save Role Assignment
                </button>
            </div>
        </div>
    </form>

    <script>
        function toggleAllRoles(checked) {
            document.querySelectorAll('.role-checkbox:not([disabled])').forEach(cb => cb.checked = checked);
        }
    </script>
</div>
@endsection
