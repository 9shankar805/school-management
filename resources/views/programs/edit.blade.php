@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <nav class="text-xs text-slate-400 mb-4">
            <a href="{{ route('programs.index') }}" class="hover:text-indigo-600">Programs</a>
            <span class="mx-1">/</span>
            <span class="text-slate-600">Edit: {{ $program->name }}</span>
        </nav>

        <h1 class="text-2xl font-bold text-slate-800 mb-6">Edit Program</h1>

        @include('session-messages')

        <div class="max-w-2xl bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <form action="{{ route('programs.update', $program->id) }}" method="POST" class="space-y-4">
                @csrf @method('PUT')

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Name <span class="text-rose-400">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $program->name) }}" required
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Code</label>
                        <input type="text" name="code" value="{{ old('code', $program->code) }}"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Level <span class="text-rose-400">*</span></label>
                        <select name="level" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            @foreach(['primary','secondary','higher_secondary','undergraduate'] as $lvl)
                            <option value="{{ $lvl }}" {{ old('level', $program->level) === $lvl ? 'selected' : '' }}>
                                {{ ucwords(str_replace('_',' ',$lvl)) }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Duration (years)</label>
                        <input type="number" name="duration_years" value="{{ old('duration_years', $program->duration_years) }}" min="1" max="10" required
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                </div>

                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">Department</label>
                    <select name="department_id" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="">— None —</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ old('department_id', $program->department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">Linked Classes</label>
                    <div class="border border-slate-200 rounded-lg p-3 grid grid-cols-2 gap-1.5">
                        @foreach($classes as $cls)
                        <label class="flex items-center gap-2 text-xs text-slate-600 cursor-pointer">
                            <input type="checkbox" name="class_ids[]" value="{{ $cls->id }}"
                                   class="rounded text-indigo-600"
                                   {{ $program->classes->contains($cls->id) ? 'checked' : '' }}>
                            {{ $cls->class_name }}
                        </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">Description</label>
                    <textarea name="description" rows="3"
                              class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ old('description', $program->description) }}</textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="is_active" name="is_active" value="1" class="rounded text-indigo-600"
                           {{ old('is_active', $program->is_active) ? 'checked' : '' }}>
                    <label for="is_active" class="text-sm text-slate-600">Active</label>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">Save Changes</button>
                    <a href="{{ route('programs.index') }}" class="px-6 py-2 bg-white border border-slate-200 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
