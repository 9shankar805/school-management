@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">
        @include('layouts.left-menu')
    </div>

    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        {{-- Header --}}
        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Super Admin Dashboard</h1>
                <p class="text-slate-400 text-sm mt-0.5">{{ now()->format('l, F j, Y') }} &middot; Full system access</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('roles.matrix') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                    <i class="bi bi-grid-3x3"></i> Permission Matrix
                </a>
                <a href="{{ url('academics/settings') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 text-sm font-medium rounded-lg hover:bg-slate-50 transition text-slate-700">
                    <i class="bi bi-tools"></i> System Settings
                </a>
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Students</p>
                    <span class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 text-sm"><i class="bi bi-people-fill"></i></span>
                </div>
                <p class="text-3xl font-bold text-slate-800">{{ number_format($studentCount) }}</p>
                <p class="mt-1 text-xs text-indigo-600">This session</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Teachers</p>
                    <span class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 text-sm"><i class="bi bi-person-badge-fill"></i></span>
                </div>
                <p class="text-3xl font-bold text-slate-800">{{ number_format($teacherCount) }}</p>
                <p class="mt-1 text-xs text-blue-600">Active faculty</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Staff</p>
                    <span class="w-8 h-8 rounded-lg bg-violet-50 flex items-center justify-center text-violet-600 text-sm"><i class="bi bi-building"></i></span>
                </div>
                <p class="text-3xl font-bold text-slate-800">{{ number_format($staffCount) }}</p>
                <p class="mt-1 text-xs text-violet-600">All departments</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Revenue</p>
                    <span class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600 text-sm"><i class="bi bi-currency-dollar"></i></span>
                </div>
                <p class="text-3xl font-bold text-emerald-600">${{ number_format($monthRevenue) }}</p>
                <p class="mt-1 text-xs text-emerald-500">This month</p>
            </div>
        </div>

        {{-- Charts --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-graph-up-arrow me-1 text-indigo-500"></i>7-Day Attendance Trend</p>
                <div id="chart-sa-attendance" style="min-height:180px"></div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-bar-chart-fill me-1 text-emerald-500"></i>Monthly Revenue</p>
                <div id="chart-sa-revenue" style="min-height:180px"></div>
            </div>
        </div>

        {{-- Attendance Today + Gender + Pending --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">Today's Attendance</p>
                <div class="flex items-end gap-3 mb-3">
                    <span class="text-4xl font-bold {{ $attendancePct >= 75 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $attendancePct }}%</span>
                    <span class="text-xs text-slate-400 pb-1">{{ $todayPresent }} present · {{ $todayAbsent }} absent</span>
                </div>
                <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full {{ $attendancePct >= 75 ? 'bg-emerald-500' : 'bg-rose-500' }}" style="width:{{ $attendancePct }}%"></div>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">Gender Distribution</p>
                <div class="flex gap-5 mb-3">
                    <div>
                        <p class="text-2xl font-bold text-blue-600">{{ $malePct }}%</p>
                        <p class="text-xs text-slate-400"><i class="bi bi-gender-male"></i> Male</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-pink-500">{{ 100 - $malePct }}%</p>
                        <p class="text-xs text-slate-400"><i class="bi bi-gender-female"></i> Female</p>
                    </div>
                </div>
                <div class="h-2 bg-slate-100 rounded-full overflow-hidden flex">
                    <div class="h-full bg-blue-500" style="width:{{ $malePct }}%"></div>
                    <div class="h-full bg-pink-500" style="width:{{ 100 - $malePct }}%"></div>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-rose-50 shadow-sm">
                <p class="text-xs font-semibold text-rose-400 uppercase tracking-wide mb-3">Pending Invoices</p>
                <p class="text-4xl font-bold text-rose-600">{{ number_format($pendingInvoices) }}</p>
                <p class="mt-2 text-xs text-rose-500"><i class="bi bi-exclamation-circle"></i> Awaiting payment</p>
                <a href="{{ route('payments.index') }}" class="mt-2 inline-block text-xs text-indigo-600 hover:underline">View all →</a>
            </div>
        </div>

        {{-- Recent Activity + Birthdays --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100">
                    <p class="text-sm font-semibold text-slate-700"><i class="bi bi-clock-history me-1 text-slate-400"></i>Recent Activity</p>
                </div>
                @if($activityLog->count())
                <div class="divide-y divide-slate-50">
                    @foreach($activityLog as $log)
                    <div class="px-5 py-3 flex items-start gap-3">
                        <span class="mt-0.5 w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center flex-shrink-0 text-xs text-slate-500"><i class="bi bi-activity"></i></span>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-slate-600">
                                <span class="font-medium text-slate-700">{{ $log->user?->full_name ?? 'System' }}</span>
                                {{ $log->event }}
                                @if($log->auditable_type)<span class="text-slate-400"> {{ class_basename($log->auditable_type) }}</span>@endif
                            </p>
                            <p class="text-[11px] text-slate-400 mt-0.5">{{ $log->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-400 text-center py-8">No activity recorded.</p>
                @endif
            </div>

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100">
                    <p class="text-sm font-semibold text-slate-700"><i class="bi bi-cake me-1 text-pink-500"></i>Upcoming Birthdays</p>
                </div>
                @if($birthdayUsers->count())
                <div class="divide-y divide-slate-50">
                    @foreach($birthdayUsers as $u)
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
                <p class="text-sm text-slate-400 text-center py-8">No birthdays in the next 7 days.</p>
                @endif
            </div>
        </div>

        {{-- System Quick Actions --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-lightning-fill me-1 text-amber-500"></i>System Actions</p>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('roles.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-indigo-50 hover:text-indigo-700 text-slate-700 rounded-lg text-sm font-medium transition"><i class="bi bi-shield-check"></i> Roles</a>
                <a href="{{ route('roles.matrix') }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-indigo-50 hover:text-indigo-700 text-slate-700 rounded-lg text-sm font-medium transition"><i class="bi bi-grid-3x3"></i> Permissions</a>
                <a href="{{ url('academics/settings') }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-amber-50 hover:text-amber-700 text-slate-700 rounded-lg text-sm font-medium transition"><i class="bi bi-tools"></i> Settings</a>
                <a href="{{ url('promotions/index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 text-slate-700 rounded-lg text-sm font-medium transition"><i class="bi bi-arrow-up-circle"></i> Promotions</a>
                <a href="{{ route('file.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-blue-50 hover:text-blue-700 text-slate-700 rounded-lg text-sm font-medium transition"><i class="bi bi-folder2-open"></i> File Manager</a>
                <a href="{{ route('two-factor.setup') }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-rose-50 hover:text-rose-700 text-slate-700 rounded-lg text-sm font-medium transition"><i class="bi bi-shield-lock"></i> 2FA Security</a>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var days    = @json($attendanceTrend->pluck('day')->map(fn($d) => \Carbon\Carbon::parse($d)->format('D M d')));
    var present = @json($attendanceTrend->pluck('present')->map(fn($v) => (int)$v));
    var absent  = @json($attendanceTrend->pluck('absent')->map(fn($v) => (int)$v));
    var months  = @json($monthlyRevenue->pluck('month')->map(fn($m) => \Carbon\Carbon::create()->month((int)$m)->format('M')));
    var totals  = @json($monthlyRevenue->pluck('total')->map(fn($v) => (float)$v));

    if (document.getElementById('chart-sa-attendance')) {
        new ApexCharts(document.getElementById('chart-sa-attendance'), {
            chart: { type: 'area', height: 180, toolbar: { show: false } },
            series: [{ name: 'Present', data: present }, { name: 'Absent', data: absent }],
            xaxis: { categories: days, labels: { style: { fontSize: '10px' } } },
            yaxis: { labels: { style: { fontSize: '10px' } } },
            colors: ['#22c55e', '#f43f5e'],
            fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.05 } },
            stroke: { curve: 'smooth', width: 2 },
            dataLabels: { enabled: false },
            legend: { position: 'top', fontSize: '12px' },
            grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
        }).render();
    }

    if (document.getElementById('chart-sa-revenue')) {
        new ApexCharts(document.getElementById('chart-sa-revenue'), {
            chart: { type: 'bar', height: 180, toolbar: { show: false } },
            series: [{ name: 'Revenue ($)', data: totals }],
            xaxis: { categories: months, labels: { style: { fontSize: '10px' } } },
            yaxis: { labels: { style: { fontSize: '10px' }, formatter: v => '$' + (v >= 1000 ? (v/1000).toFixed(1)+'k' : v) } },
            colors: ['#6366f1'],
            plotOptions: { bar: { borderRadius: 4, columnWidth: '55%' } },
            dataLabels: { enabled: false },
            grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
            tooltip: { y: { formatter: v => '$' + Number(v).toLocaleString() } },
        }).render();
    }
})();
</script>
@endpush
