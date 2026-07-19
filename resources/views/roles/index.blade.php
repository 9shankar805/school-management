@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-10 overflow-auto">

        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Roles & Permissions</h1>
                <p class="text-slate-500 text-sm mt-1">Manage access control roles for all user types</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('roles.matrix') }}" class="px-4 py-2 bg-white border border-slate-200 text-sm font-medium rounded-lg hover:bg-slate-50 text-slate-700 transition">
                    <i class="bi bi-grid-3x3 me-1"></i> Permission Matrix
                </a>
                <a href="{{ route('roles.create') }}" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                    <i class="bi bi-plus me-1"></i> New Role
                </a>
            </div>
        </div>

        @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-emerald-50 text-emerald-700 text-sm rounded-lg border border-emerald-200">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="mb-4 px-4 py-3 bg-rose-50 text-rose-700 text-sm rounded-lg border border-rose-200">{{ session('error') }}</div>
        @endif

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-slate-400 bg-slate-50 border-b border-slate-100">
                        <th class="px-5 py-3 font-semibold">Role Name</th>
                        <th class="px-5 py-3 font-semibold">Permissions</th>
                        <th class="px-5 py-3 font-semibold">Users</th>
                        <th class="px-5 py-3 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($roles as $role)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full {{ in_array($role->name, ['super-admin','admin']) ? 'bg-rose-400' : (in_array($role->name, ['teacher','class-teacher']) ? 'bg-blue-400' : 'bg-emerald-400') }}"></span>
                                <span class="font-medium text-slate-700 capitalize">{{ str_replace('-', ' ', $role->name) }}</span>
                                @if(in_array($role->name, ['super-admin','admin','teacher','student','parent']))
                                <span class="text-[10px] px-1.5 py-0.5 bg-slate-100 text-slate-500 rounded font-medium">system</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-full">
                                <i class="bi bi-shield-check"></i> {{ $role->permissions_count }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-slate-600">{{ $role->users_count }}</td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('roles.edit', $role) }}" class="text-xs px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition">Edit Permissions</a>
                                @if(!in_array($role->name, ['super-admin','admin','teacher','student','parent']))
                                <form method="POST" action="{{ route('roles.destroy', $role) }}" onsubmit="return confirm('Delete role {{ $role->name }}?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg transition">Delete</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection
