@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">
        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div><h1 class="text-2xl font-bold text-slate-800 tracking-tight">Departments</h1></div>
        </div>
        @if(session('status'))<div class="mb-5 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm"><i class="bi bi-check-circle me-1"></i>{{ session('status') }}</div>@endif

        @can('create teachers')
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-6">
            <p class="text-sm font-semibold text-slate-700 mb-4">Create Department</p>
            <form method="POST" action="{{ route('departments.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @csrf
                <div><label class="block text-xs font-medium text-slate-500 mb-1">Name *</label><input type="text" name="name" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                <div><label class="block text-xs font-medium text-slate-500 mb-1">Code</label><input type="text" name="code" placeholder="e.g. MATH" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                <div><label class="block text-xs font-medium text-slate-500 mb-1">Department Head</label>
                <select name="head_id" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    <option value="">— None —</option>
                    @foreach($teachers as $t)<option value="{{ $t->id }}">{{ $t->full_name }}</option>@endforeach
                </select></div>
                <div class="md:col-span-2"><label class="block text-xs font-medium text-slate-500 mb-1">Description</label><input type="text" name="description" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                <div class="flex items-end"><button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">Create</button></div>
            </form>
        </div>
        @endcan

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($departments as $dept)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <p class="font-semibold text-slate-800">{{ $dept->name }}</p>
                        @if($dept->code)<span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full font-mono">{{ $dept->code }}</span>@endif
                    </div>
                    <span class="text-xs {{ $dept->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }} px-2 py-0.5 rounded-full">{{ $dept->is_active ? 'Active' : 'Inactive' }}</span>
                </div>
                @if($dept->head)<p class="text-xs text-slate-500 mb-2"><i class="bi bi-person-badge me-1"></i>Head: {{ $dept->head->full_name }}</p>@endif
                @if($dept->description)<p class="text-xs text-slate-400 mb-3">{{ $dept->description }}</p>@endif

                <div class="mb-3">
                    <p class="text-xs font-medium text-slate-500 mb-2">Teachers ({{ $dept->teachers->count() }})</p>
                    <div class="flex flex-wrap gap-1">
                        @foreach($dept->teachers as $t)
                        <div class="flex items-center gap-1 bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded-full text-xs">
                            {{ $t->full_name }}
                            @can('create teachers')
                            <form method="POST" action="{{ route('departments.teacher.remove', [$dept->id, $t->id]) }}" class="inline"><@csrf<button class="ml-1 text-indigo-400 hover:text-rose-600">×</button></form>
                            @endcan
                        </div>
                        @endforeach
                    </div>
                </div>

                @can('create teachers')
                <form method="POST" action="{{ route('departments.teacher.assign', $dept->id) }}" class="flex gap-2">
                    @csrf
                    <select name="teacher_id" class="flex-1 border border-slate-200 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="">Add teacher…</option>
                        @foreach($teachers->whereNotIn('id', $dept->teachers->pluck('id')) as $t)
                        <option value="{{ $t->id }}">{{ $t->full_name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-2 py-1.5 bg-indigo-600 text-white rounded-lg text-xs hover:bg-indigo-700 transition">Add</button>
                </form>
                @endcan
            </div>
            @empty
            <div class="col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-12 text-center">
                <i class="bi bi-diagram-3 text-4xl text-slate-200"></i>
                <p class="mt-3 text-slate-400">No departments yet.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
