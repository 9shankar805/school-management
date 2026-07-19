@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-10 overflow-auto">

        <div class="flex justify-between items-center mb-8">
            <div>
                <a href="{{ route('roles.index') }}" class="text-xs text-slate-400 hover:text-slate-600 flex items-center gap-1 mb-2"><i class="bi bi-arrow-left"></i> Back to Roles</a>
                <h1 class="text-2xl font-bold text-slate-800">Permission Matrix</h1>
                <p class="text-slate-500 text-sm mt-1">Full roles × permissions grid. Check to grant, uncheck to revoke.</p>
            </div>
        </div>

        @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-emerald-50 text-emerald-700 text-sm rounded-lg border border-emerald-200">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('roles.matrix.update') }}">
            @csrf @method('POST')

            @foreach($permissions as $group => $groupPerms)
            <div class="mb-6 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 bg-slate-50 border-b border-slate-100">
                    <p class="text-sm font-semibold text-slate-600">{{ $group }}</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="px-4 py-2.5 text-left text-slate-500 font-medium w-48 min-w-48">Permission</th>
                                @foreach($roles as $role)
                                <th class="px-2 py-2.5 text-center font-medium text-slate-500 min-w-20">
                                    <span class="capitalize text-xs">{{ str_replace('-', ' ', $role->name) }}</span>
                                </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($groupPerms as $perm)
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-4 py-2 text-slate-600 font-medium">{{ $perm->name }}</td>
                                @foreach($roles as $role)
                                <td class="px-2 py-2 text-center">
                                    @if($role->name === 'super-admin')
                                    <span title="Super admin bypasses all checks" class="text-emerald-500 text-base"><i class="bi bi-check-circle-fill"></i></span>
                                    @else
                                    <input type="checkbox"
                                        name="matrix[{{ $role->name }}][]"
                                        value="{{ $perm->name }}"
                                        class="rounded border-slate-300 text-indigo-600 w-4 h-4"
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

            <div class="sticky bottom-4 flex justify-center">
                <button type="submit" class="px-8 py-3 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 shadow-lg transition">
                    <i class="bi bi-check2-all me-2"></i> Save Permission Matrix
                </button>
            </div>
        </form>

    </div>
</div>
@endsection
