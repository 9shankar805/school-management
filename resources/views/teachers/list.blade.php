@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Teachers</h1>
                <p class="text-slate-400 text-sm mt-0.5">{{ $teachers->count() }} faculty members</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('departments.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 text-sm font-medium rounded-lg hover:bg-slate-50 transition text-slate-700"><i class="bi bi-diagram-3"></i> Departments</a>
                <a href="{{ route('teacher.attendance.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 text-sm font-medium rounded-lg hover:bg-slate-50 transition text-slate-700"><i class="bi bi-calendar-check"></i> Attendance</a>
                <a href="{{ route('teacher.payroll.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 text-sm font-medium rounded-lg hover:bg-slate-50 transition text-slate-700"><i class="bi bi-cash-stack"></i> Payroll</a>
                @can('create teachers')
                <a href="{{ route('teacher.create.show') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition"><i class="bi bi-person-plus"></i> Add Teacher</a>
                @endcan
            </div>
        </div>

        @if(session('status'))
        <div class="mb-5 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm"><i class="bi bi-check-circle me-1"></i>{{ session('status') }}</div>
        @endif

        {{-- Search & department filter --}}
        <form method="GET" action="{{ route('teacher.list.show') }}" class="flex flex-wrap gap-2 mb-6">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email…" class="flex-1 min-w-48 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            <select name="department_id" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">All Departments</option>
                @foreach(\App\Models\Department::where('is_active',true)->get() as $dept)
                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition">Filter</button>
            @if(request('search') || request('department_id'))
            <a href="{{ route('teacher.list.show') }}" class="px-4 py-2 bg-white border border-slate-200 text-slate-500 rounded-lg text-sm hover:bg-slate-50 transition">Clear</a>
            @endif
        </form>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($teachers as $t)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 hover:shadow-md transition">
                <div class="flex items-start gap-3 mb-4">
                    <img src="{{ $t->avatar }}" class="w-12 h-12 rounded-xl object-cover flex-shrink-0 border border-slate-100" alt="">
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-slate-800 truncate">{{ $t->full_name }}</p>
                        <p class="text-xs text-slate-400 capitalize">{{ str_replace('-',' ',$t->primary_role) }}</p>
                        @if($t->departments->count())
                        <div class="flex flex-wrap gap-1 mt-1">
                            @foreach($t->departments->take(2) as $d)
                            <span class="text-[10px] bg-indigo-100 text-indigo-700 px-1.5 py-0.5 rounded-full">{{ $d->name }}</span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
                <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500 mb-4">
                    <span><i class="bi bi-envelope me-1"></i>{{ $t->email }}</span>
                    @if($t->phone)<span><i class="bi bi-telephone me-1"></i>{{ $t->phone }}</span>@endif
                    <span><i class="bi bi-journal-medical me-1"></i>{{ $t->assignedCourses->count() }} course(s)</span>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('teacher.profile.show', $t->id) }}" class="flex-1 text-center py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-medium transition">Profile</a>
                    @can('create teachers')
                    <a href="{{ route('teacher.edit.show', $t->id) }}" class="flex-1 text-center py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-xs font-medium transition">Edit</a>
                    @endcan
                </div>
            </div>
            @empty
            <div class="col-span-3 bg-white rounded-2xl border border-slate-100 shadow-sm p-12 text-center">
                <i class="bi bi-person-badge text-4xl text-slate-200"></i>
                <p class="mt-3 text-slate-400">No teachers found.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
