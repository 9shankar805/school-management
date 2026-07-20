@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Programs</h1>
                <p class="text-slate-400 text-sm mt-0.5">Curriculum tracks &amp; academic programs</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('curriculums.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 text-sm font-medium rounded-lg hover:bg-slate-50 transition text-slate-700">
                    <i class="bi bi-journal-richtext"></i> Curriculums
                </a>
            </div>
        </div>

        @include('session-messages')

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

            {{-- Program list --}}
            <div class="xl:col-span-2 space-y-3">
                @forelse($programs as $program)
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                    <div class="flex flex-wrap justify-between items-start gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="font-semibold text-slate-800">{{ $program->name }}</h3>
                                @if($program->code)
                                <span class="text-[11px] bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full font-medium">{{ $program->code }}</span>
                                @endif
                                <span class="text-[11px] px-2 py-0.5 rounded-full font-medium
                                    {{ $program->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $program->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <div class="flex flex-wrap gap-4 mt-1 text-xs text-slate-500">
                                <span class="capitalize"><i class="bi bi-layers me-1"></i>{{ str_replace('_', ' ', $program->level) }}</span>
                                <span><i class="bi bi-clock-history me-1"></i>{{ $program->duration_years }} yr(s)</span>
                                @if($program->department)
                                <span><i class="bi bi-diagram-3 me-1"></i>{{ $program->department->name }}</span>
                                @endif
                                <span><i class="bi bi-journal-richtext me-1"></i>{{ $program->curriculums_count }} curriculum(s)</span>
                            </div>
                            @if($program->description)
                            <p class="mt-1.5 text-xs text-slate-400 line-clamp-2">{{ $program->description }}</p>
                            @endif
                            @if($program->classes->count())
                            <div class="flex flex-wrap gap-1 mt-2">
                                @foreach($program->classes as $cls)
                                <span class="text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">{{ $cls->class_name }}</span>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        <div class="flex gap-2 flex-shrink-0">
                            @can('view academic settings')
                            <a href="{{ route('programs.edit', $program->id) }}"
                               class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Edit">
                                <i class="bi bi-pencil text-sm"></i>
                            </a>
                            <form action="{{ route('programs.destroy', $program->id) }}" method="POST"
                                  onsubmit="return confirm('Delete this program?')">
                                @csrf @method('DELETE')
                                <button class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Delete">
                                    <i class="bi bi-trash text-sm"></i>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </div>
                </div>
                @empty
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-12 text-center">
                    <i class="bi bi-mortarboard text-5xl text-slate-200"></i>
                    <p class="mt-3 text-slate-400">No programs yet. Create one to get started.</p>
                </div>
                @endforelse
            </div>

            {{-- Create form --}}
            @can('view academic settings')
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <h2 class="text-base font-semibold text-slate-700 mb-4"><i class="bi bi-plus-circle me-1 text-indigo-500"></i>New Program</h2>
                <form action="{{ route('programs.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Name <span class="text-rose-400">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="e.g. Science Stream">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Code</label>
                        <input type="text" name="code" value="{{ old('code') }}"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="SCI">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Level <span class="text-rose-400">*</span></label>
                        <select name="level" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="primary">Primary</option>
                            <option value="secondary" selected>Secondary</option>
                            <option value="higher_secondary">Higher Secondary</option>
                            <option value="undergraduate">Undergraduate</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Duration (years) <span class="text-rose-400">*</span></label>
                        <input type="number" name="duration_years" value="{{ old('duration_years', 1) }}" min="1" max="10" required
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Department</label>
                        <select name="department_id" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="">— None —</option>
                            @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Link Classes</label>
                        <div class="border border-slate-200 rounded-lg p-2 max-h-32 overflow-y-auto space-y-1">
                            @foreach(\App\Models\SchoolClass::orderBy('class_name')->get() as $cls)
                            <label class="flex items-center gap-2 text-xs text-slate-600 cursor-pointer">
                                <input type="checkbox" name="class_ids[]" value="{{ $cls->id }}" class="rounded text-indigo-600">
                                {{ $cls->class_name }}
                            </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Description</label>
                        <textarea name="description" rows="2"
                                  class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="Optional...">{{ old('description') }}</textarea>
                    </div>
                    <button type="submit" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">
                        Create Program
                    </button>
                </form>
            </div>
            @endcan

        </div>
    </div>
</div>
@endsection
