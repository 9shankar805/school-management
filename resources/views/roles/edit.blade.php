@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-10 overflow-auto">

        <div class="mb-8">
            <a href="{{ route('roles.index') }}" class="text-xs text-slate-400 hover:text-slate-600 flex items-center gap-1 mb-3"><i class="bi bi-arrow-left"></i> Back to Roles</a>
            <h1 class="text-2xl font-bold text-slate-800 capitalize">Edit: {{ str_replace('-', ' ', $role->name) }}</h1>
            <p class="text-slate-500 text-sm mt-1">Select which permissions this role should have</p>
        </div>

        @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-emerald-50 text-emerald-700 text-sm rounded-lg border border-emerald-200">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('roles.update', $role) }}">
            @csrf @method('PUT')

            <div class="space-y-4 mb-8">
                @foreach($permissions as $group => $groupPerms)
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-3 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
                        <p class="text-sm font-semibold text-slate-600">{{ $group }}</p>
                        <label class="flex items-center gap-2 text-xs text-slate-400 cursor-pointer select-all-group" data-group="{{ $loop->index }}">
                            <input type="checkbox" class="group-toggle rounded" data-group="{{ $loop->index }}"> Select all
                        </label>
                    </div>
                    <div class="p-5 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                        @foreach($groupPerms as $perm)
                        <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer hover:text-slate-800">
                            <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                class="rounded border-slate-300 text-indigo-600 perm-check-{{ $loop->parent->index }}"
                                {{ in_array($perm->id, $rolePermissions) ? 'checked' : '' }}>
                            {{ $perm->name }}
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            <div class="flex gap-3">
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                    <i class="bi bi-check2 me-1"></i> Save Permissions
                </button>
                <a href="{{ route('roles.index') }}" class="px-6 py-2.5 bg-white border border-slate-200 text-sm font-medium rounded-lg hover:bg-slate-50 text-slate-700 transition">Cancel</a>
            </div>
        </form>

    </div>
</div>
<script>
document.querySelectorAll('.group-toggle').forEach(toggle => {
    toggle.addEventListener('change', function() {
        const group = this.dataset.group;
        document.querySelectorAll('.perm-check-' + group).forEach(cb => cb.checked = this.checked);
    });
});
</script>
@endsection
