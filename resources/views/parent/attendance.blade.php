@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">
        @include('layouts.left-menu')
    </div>

    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        @include('parent.partials.child-selector')
        @include('parent.partials.page-header', ['title' => 'Attendance'])

        @include('session-messages')

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-4 text-center">
                <p class="text-2xl font-bold {{ $pct >= 75 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $pct }}%</p>
                <p class="text-xs text-slate-400 mt-0.5">Session Attendance</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-4 text-center">
                <p class="text-2xl font-bold text-blue-600">{{ $present }}</p>
                <p class="text-xs text-slate-400 mt-0.5">Present Days</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-4 text-center">
                <p class="text-2xl font-bold text-rose-600">{{ $absent }}</p>
                <p class="text-xs text-slate-400 mt-0.5">Absent Days</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-4 text-center">
                <p class="text-2xl font-bold text-slate-700">{{ $total }}</p>
                <p class="text-xs text-slate-400 mt-0.5">Total Classes</p>
            </div>
        </div>

        @if($pct < 75 && $total > 0)
        <div class="bg-rose-50 border border-rose-200 rounded-xl p-4 mb-6 flex items-start gap-3">
            <i class="bi bi-exclamation-triangle-fill text-rose-500 mt-0.5"></i>
            <div>
                <p class="font-semibold text-rose-800 text-sm">Attendance shortage alert</p>
                <p class="text-rose-700 text-xs mt-1">Current attendance is {{ $pct }}%. Need approximately <strong>{{ $deficit }}</strong> more present days to reach the 75% required threshold.</p>
            </div>
        </div>
        @endif

        {{-- Month navigation --}}
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-4 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-slate-700">
                    {{ \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y') }}
                </h2>
                <div class="flex gap-2">
                    @php
                        $prev = \Carbon\Carbon::createFromDate($year, $month, 1)->subMonth();
                        $next = \Carbon\Carbon::createFromDate($year, $month, 1)->addMonth();
                    @endphp
                    <a href="{{ route('parent.attendance', [$child->id, 'month' => $prev->month, 'year' => $prev->year]) }}"
                       class="text-xs px-3 py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg text-slate-600">
                        <i class="bi bi-chevron-left"></i> {{ $prev->format('M') }}
                    </a>
                    @if($next->lte(now()))
                    <a href="{{ route('parent.attendance', [$child->id, 'month' => $next->month, 'year' => $next->year]) }}"
                       class="text-xs px-3 py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg text-slate-600">
                        {{ $next->format('M') }} <i class="bi bi-chevron-right"></i>
                    </a>
                    @endif
                </div>
            </div>

            {{-- Calendar grid --}}
            <div class="grid grid-cols-7 gap-1 text-center mb-2">
                @foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $day)
                <p class="text-[10px] font-semibold text-slate-400 uppercase">{{ $day }}</p>
                @endforeach
            </div>
            @php
                $startDay = \Carbon\Carbon::createFromDate($year, $month, 1)->dayOfWeekIso; // 1=Mon
            @endphp
            <div class="grid grid-cols-7 gap-1 text-center">
                {{-- Empty cells before month start --}}
                @for($i = 1; $i < $startDay; $i++)
                <div></div>
                @endfor

                @for($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $records = $monthly->get($day, collect());
                        $hasPresent = $records->contains('status', 'present');
                        $hasAbsent  = $records->contains('status', 'absent');
                        if ($hasPresent)      $cls = 'bg-emerald-100 text-emerald-700 font-semibold';
                        elseif ($hasAbsent)   $cls = 'bg-rose-100 text-rose-700 font-semibold';
                        else                  $cls = 'text-slate-400';
                    @endphp
                    <div class="rounded-lg py-1.5 text-xs {{ $cls }}">{{ $day }}</div>
                @endfor
            </div>
            <div class="flex gap-4 mt-3 text-xs text-slate-500">
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-emerald-100 inline-block"></span>Present</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-rose-100 inline-block"></span>Absent</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-slate-100 inline-block"></span>No record</span>
            </div>
        </div>

        {{-- Per-course breakdown --}}
        @if($courseBreakdown->isNotEmpty())
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden mb-6">
            <div class="px-5 py-3 border-b border-slate-100">
                <p class="text-sm font-semibold text-slate-700">Course-wise Breakdown</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="text-left px-4 py-2 text-xs font-semibold text-slate-500">Course</th>
                            <th class="text-center px-4 py-2 text-xs font-semibold text-slate-500">Total</th>
                            <th class="text-center px-4 py-2 text-xs font-semibold text-slate-500">Present</th>
                            <th class="text-center px-4 py-2 text-xs font-semibold text-slate-500">Absent</th>
                            <th class="text-center px-4 py-2 text-xs font-semibold text-slate-500">%</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($courseBreakdown as $cb)
                        <tr>
                            <td class="px-4 py-2 font-medium text-slate-700">{{ $cb->course?->name ?? 'Unknown' }}</td>
                            <td class="px-4 py-2 text-center text-slate-600">{{ $cb->total }}</td>
                            <td class="px-4 py-2 text-center text-emerald-600 font-medium">{{ $cb->present }}</td>
                            <td class="px-4 py-2 text-center text-rose-600 font-medium">{{ $cb->absent }}</td>
                            <td class="px-4 py-2 text-center">
                                <span class="text-xs font-semibold {{ $cb->pct >= 75 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $cb->pct }}%</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Attendance trend chart --}}
        @if($trendWeeks->isNotEmpty())
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5">
            <p class="text-sm font-semibold text-slate-700 mb-3">12-Week Attendance Trend</p>
            <div id="attendanceTrendChart"></div>
        </div>
        @endif

    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const weeks   = @json($trendWeeks);
    const present = @json($trendPresent);
    const absent  = @json($trendAbsent);

    if (!document.getElementById('attendanceTrendChart') || weeks.length === 0) return;

    new ApexCharts(document.getElementById('attendanceTrendChart'), {
        chart: { type: 'area', height: 200, toolbar: { show: false }, sparkline: { enabled: false } },
        series: [
            { name: 'Present', data: present.map(Number) },
            { name: 'Absent',  data: absent.map(Number) },
        ],
        xaxis: { categories: weeks, labels: { style: { fontSize: '10px' } } },
        colors: ['#10b981', '#f43f5e'],
        fill: { opacity: 0.15 },
        stroke: { curve: 'smooth', width: 2 },
        legend: { position: 'top', fontSize: '11px' },
        dataLabels: { enabled: false },
        tooltip: { theme: 'light' },
    }).render();
});
</script>
@endpush
