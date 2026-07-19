@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">
        @include('layouts.left-menu')
    </div>

    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">HR Dashboard</h1>
                <p class="text-slate-400 text-sm mt-0.5">{{ now()->format('l, F j, Y') }}</p>
            </div>
            <div class="flex gap-2">
                @can('create staff')
                <a href="{{ route('staff.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                    <i class="bi bi-person-plus"></i> Add Staff
                </a>
                @endcan
                <a href="{{ route('staff.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 text-sm font-medium rounded-lg hover:bg-slate-50 transition text-slate-700">
                    <i class="bi bi-people"></i> All Staff
                </a>
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Total Staff</p>
                    <span class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 text-sm"><i class="bi bi-people-fill"></i></span>
                </div>
                <p class="text-3xl font-bold text-slate-800">{{ number_format($staffCount) }}</p>
                <p class="mt-1 text-xs text-indigo-600">All departments</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Teachers</p>
                    <span class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 text-sm"><i class="bi bi-person-badge-fill"></i></span>
                </div>
                <p class="text-3xl font-bold text-blue-600">{{ number_format($teacherCount) }}</p>
                <p class="mt-1 text-xs text-blue-500">Active faculty</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Birthdays</p>
                    <span class="w-8 h-8 rounded-lg bg-pink-50 flex items-center justify-center text-pink-600 text-sm"><i class="bi bi-cake"></i></span>
                </div>
                <p class="text-3xl font-bold text-pink-600">{{ $birthdayStaff->count() }}</p>
                <p class="mt-1 text-xs text-pink-500">This month</p>
            </div>
        </div>

        {{-- Staff Breakdown Chart + Birthdays --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-pie-chart me-1 text-indigo-500"></i>Staff by Role</p>
                <div id="chart-staff-roles" style="min-height:200px"></div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100">
                    <p class="text-sm font-semibold text-slate-700"><i class="bi bi-cake me-1 text-pink-500"></i>Birthdays This Month</p>
                </div>
                @if($birthdayStaff->count())
                <div class="divide-y divide-slate-50">
                    @foreach($birthdayStaff as $u)
                    <div class="px-5 py-3 flex items-center gap-3">
                        <img src="{{ $u->avatar }}" class="w-8 h-8 rounded-full object-cover flex-shrink-0" alt="">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-700 truncate">{{ $u->full_name }}</p>
                            <p class="text-xs text-slate-400 capitalize">{{ str_replace('-', ' ', $u->primary_role) }}</p>
                        </div>
                        <span class="text-xs text-pink-700 bg-pink-50 px-2 py-1 rounded-lg flex-shrink-0">
                            {{ \Carbon\Carbon::parse($u->birthday)->format('M d') }}
                        </span>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-400 text-center py-10">No birthdays this month.</p>
                @endif
            </div>
        </div>

        {{-- Recent Staff --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 flex justify-between items-center">
                <p class="text-sm font-semibold text-slate-700"><i class="bi bi-person-lines-fill me-1 text-slate-400"></i>Recently Added Staff</p>
                <a href="{{ route('staff.index') }}" class="text-xs text-indigo-600 hover:underline">View all</a>
            </div>
            @if($recentStaff->count())
            <div class="divide-y divide-slate-50">
                @foreach($recentStaff as $u)
                <div class="px-5 py-3 flex items-center gap-3">
                    <img src="{{ $u->avatar }}" class="w-8 h-8 rounded-full object-cover flex-shrink-0" alt="">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-700 truncate">{{ $u->full_name }}</p>
                        <p class="text-xs text-slate-400">{{ $u->email }}</p>
                    </div>
                    <span class="text-xs text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full capitalize flex-shrink-0">
                        {{ str_replace('-', ' ', $u->primary_role) }}
                    </span>
                    <span class="text-xs text-slate-400 flex-shrink-0">{{ $u->created_at->diffForHumans() }}</span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-slate-400 text-center py-8">No staff added yet.</p>
            @endif
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var roles  = @json($roleBreakdown->pluck('role')->map(fn($r) => ucwords(str_replace('-', ' ', $r))));
    var counts = @json($roleBreakdown->pluck('count'));

    if (document.getElementById('chart-staff-roles')) {
        new ApexCharts(document.getElementById('chart-staff-roles'), {
            chart: { type: 'donut', height: 200 },
            series: counts.map(Number),
            labels: roles,
            legend: { position: 'bottom', fontSize: '11px' },
            dataLabels: { enabled: false },
            plotOptions: { pie: { donut: { size: '60%' } } },
            tooltip: { y: { formatter: v => v + ' staff' } },
        }).render();
    }
})();
</script>
@endpush
