@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">
        @include('layouts.left-menu')
    </div>

    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="mb-7">
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Transport Manager Dashboard</h1>
            <p class="text-slate-400 text-sm mt-0.5">{{ now()->format('l, F j, Y') }}</p>
        </div>

        {{-- Coming Soon Notice --}}
        <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6 mb-6 flex items-start gap-4">
            <span class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600 text-xl flex-shrink-0"><i class="bi bi-bus-front"></i></span>
            <div>
                <p class="font-bold text-blue-800">Transport Module Coming Soon</p>
                <p class="text-blue-700 text-sm mt-1">Vehicle management, routes, stops, GPS tracking, and student transport allocation will be available in Module 13.</p>
            </div>
        </div>

        {{-- Current data available --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Total Students</p>
                    <span class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 text-sm"><i class="bi bi-people"></i></span>
                </div>
                <p class="text-3xl font-bold text-slate-800">{{ number_format($studentCount) }}</p>
                <p class="mt-1 text-xs text-slate-400">Potential transport users</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Vehicles</p>
                    <span class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600 text-sm"><i class="bi bi-bus-front"></i></span>
                </div>
                <p class="text-3xl font-bold text-slate-400">—</p>
                <p class="mt-1 text-xs text-slate-400">Module 13 required</p>
            </div>
        </div>

        {{-- Planned features --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mt-6">
            <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-list-check me-1 text-slate-400"></i>Planned Features (Module 13)</p>
            <div class="grid grid-cols-2 gap-2">
                @foreach(['Vehicle management','Driver profiles','Route management','Route stops','Student allocation','GPS live tracking','Fuel & maintenance logs','Transport fee integration','Transport attendance'] as $feat)
                <div class="flex items-center gap-2 text-sm text-slate-500">
                    <i class="bi bi-circle text-slate-300 text-xs"></i> {{ $feat }}
                </div>
                @endforeach
            </div>
        </div>

    </div>
</div>
@endsection
