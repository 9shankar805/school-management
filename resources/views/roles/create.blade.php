@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-10 overflow-auto">

        <div class="mb-8">
            <a href="{{ route('roles.index') }}" class="text-xs text-slate-400 hover:text-slate-600 flex items-center gap-1 mb-3"><i class="bi bi-arrow-left"></i> Back</a>
            <h1 class="text-2xl font-bold text-slate-800">Create New Role</h1>
        </div>

        <form method="POST" action="{{ route('roles.store') }}">
            @csrf
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mb-6">
                <label class="block text-sm font-medium text-slate-700 mb-2">Role Name</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. department-head"
                    class="w-full max-w-sm px-4 py-2.5 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                @error('name')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                <p class="mt-2 text-xs text-slate-400">Use lowercase with hyphens. e.g. <code>class-teacher</code></p>
            </div>

            <div class="space-y-4 mb-8">
                @foreach($permissions as $group => $groupPerms)
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-3 bg-slate-50 border-b border-slate-100">
                        <p class="text-sm font-semibold text-slate-600">{{ $group }}</p>
                    </div>
                    <div class="p-5 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                        @foreach($groupPerms as $perm)
                        <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer hover:text-slate-800">
                            <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                class="rounded border-slate-300 text-indigo-600"
                                {{ in_array($perm->id, old('permissions', [])) ? 'checked' : '' }}>
                            {{ $perm->name }}
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">Create Role</button>
        </form>
    </div>
</div>
@endsection
