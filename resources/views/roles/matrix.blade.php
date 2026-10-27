@extends('layouts.app')

@section('title', 'Permission Matrix - NepalSchoolHub')

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
                    <i class="bi bi-grid-3x3-gap-fill text-lg"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Institutional Permission Matrix</h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Complete cross-sectional grid of all roles &times; privileges. Check boxes to grant, uncheck to revoke.</p>
                </div>
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

    <form method="POST" action="{{ route('roles.matrix.update') }}">
        @csrf

        @foreach($permissions as $group => $groupPerms)
        <div class="mb-6 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-6 py-3.5 bg-slate-50/80 dark:bg-slate-800/60 border-b border-slate-200/80 dark:border-slate-800 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                <p class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100 uppercase tracking-wider">{{ $group }} Module Privileges</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b border-slate-200/80 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/20">
                            <th class="px-6 py-3 text-left text-slate-400 dark:text-slate-500 font-semibold w-56 min-w-56">Permission</th>
                            @foreach($roles as $role)
                            <th class="px-3 py-3 text-center font-bold text-slate-700 dark:text-slate-200 min-w-24">
                                <span class="capitalize text-xs block">{{ str_replace('-', ' ', $role->name) }}</span>
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        @foreach($groupPerms as $perm)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-6 py-3 font-semibold text-slate-700 dark:text-slate-300 font-mono text-[11px]">{{ $perm->name }}</td>
                            @foreach($roles as $role)
                            <td class="px-3 py-3 text-center">
                                @if($role->name === 'super-admin')
                                <span title="Super admin bypasses all checks" class="text-emerald-500 text-sm"><i class="bi bi-shield-fill-check"></i></span>
                                @else
                                <input type="checkbox"
                                    name="matrix[{{ $role->name }}][]"
                                    value="{{ $perm->name }}"
                                    class="rounded-lg border-slate-300 dark:border-slate-700 text-indigo-600 focus:ring-indigo-500 w-4 h-4 cursor-pointer"
                                    {{ in_array($perm->name, $matrix[$role->name] ?? []) ? 'checked' : '' }}>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach

        <div class="sticky bottom-6 flex justify-center z-20">
            <button type="submit" class="px-8 py-3.5 bg-gradient-to-r from-indigo-600 via-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white text-xs sm:text-sm font-bold rounded-2xl shadow-xl shadow-indigo-600/30 transition transform hover:-translate-y-0.5 active:scale-95 flex items-center gap-2">
                <i class="bi bi-check2-all text-base"></i>
                <span>Save & Apply Permission Matrix</span>
            </button>
        </div>
    </form>

</div>
@endsection
