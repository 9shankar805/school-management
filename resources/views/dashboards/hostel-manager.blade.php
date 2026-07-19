@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">
        @include('layouts.left-menu')
    </div>

    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="mb-7">
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Hostel Manager Dashboard</h1>
            <p class="text-slate-400 text-sm mt-0.5">{{ now()->format('l, F j, Y') }}</p>
        </div>

        {{-- Coming Soon Notice --}}
        <div class="bg-violet-50 border border-violet-200 rounded-2xl p-6 mb-6 flex items-start gap-4">
            <span class="w-10 h-10 rounded-xl bg-violet-100 flex items-center justify-center text-violet-600 text-xl flex-shrink-0"><i class="bi bi-house-door"></i></span>
            <div>
                <p class="font-bold text-violet-800">Hostel Module Coming Soon</p>
                <p class="text-violet-700 text-sm mt-1">Building, room, and bed management along with student allocations and hostel attendance will be available in Module 14.</p>
            </div>
        </div>

        {{-- Current data --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Total Students</p>
                    <span class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 text-sm"><i class="bi bi-people"></i></span>
                </div>
                <p class="text-3xl font-bold text-slate-800">{{ number_format($studentCount) }}</p>
                <p class="mt-1 text-xs text-slate-400">Potential hostel residents</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Rooms</p>
                    <span class="w-8 h-8 rounded-lg bg-violet-50 flex items-center justify-center text-violet-600 text-sm"><i class="bi bi-door-open"></i></span>
                </div>
                <p class="text-3xl font-bold text-slate-400">—</p>
                <p class="mt-1 text-xs text-slate-400">Module 14 required</p>
            </div>
        </div>

        {{-- Planned features --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mt-6">
            <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-list-check me-1 text-slate-400"></i>Planned Features (Module 14)</p>
            <div class="grid grid-cols-2 gap-2">
                @foreach(['Building / block management','Room management','Bed management & allocation','Student hostel allocation','Hostel attendance','Visitor log','Hostel fee integration','Maintenance requests','Hostel warden management'] as $feat)
                <div class="flex items-center gap-2 text-sm text-slate-500">
                    <i class="bi bi-circle text-slate-300 text-xs"></i> {{ $feat }}
                </div>
                @endforeach
            </div>
        </div>

    </div>
</div>
@endsection
