@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">
        @include('layouts.left-menu')
    </div>

    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        {{-- Header --}}
        <div class="mb-7">
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Student Dashboard</h1>
            <p class="text-slate-400 text-sm mt-0.5">{{ now()->format('l, F j, Y') }} &middot; Welcome, <span class="font-medium text-slate-600">{{ $student->first_name }}</span></p>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Attendance</p>
                    <span class="w-8 h-8 rounded-lg {{ $attendancePct >= 75 ? 'bg-emerald-50' : 'bg-rose-50' }} flex items-center justify-center text-sm {{ $attendancePct >= 75 ? 'text-emerald-600' : 'text-rose-600' }}"><i class="bi bi-calendar-check"></i></span>
                </div>
                <p class="text-3xl font-bold {{ $attendancePct >= 75 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $attendancePct }}%</p>
                <p class="mt-1 text-xs text-slate-400">{{ $presentCount }}/{{ $totalAttendance }} classes</p>
                <div class="mt-2 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full {{ $attendancePct >= 75 ? 'bg-emerald-500' : 'bg-rose-500' }}" style="width:{{ $attendancePct }}%"></div>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Class</p>
                    <span class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 text-sm"><i class="bi bi-mortarboard"></i></span>
                </div>
                <p class="text-xl font-bold text-slate-800">{{ $classInfo?->schoolClass?->name ?? '—' }}</p>
                <p class="mt-1 text-xs text-slate-400">Section: {{ $classInfo?->section?->name ?? '—' }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Results</p>
                    <span class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 text-sm"><i class="bi bi-clipboard-data"></i></span>
                </div>
                <p class="text-3xl font-bold text-slate-800">{{ $marks->count() }}</p>
                <p class="mt-1 text-xs text-blue-600">Marks recorded</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Fees Due</p>
                    <span class="w-8 h-8 rounded-lg {{ $invoices->where('status','unpaid')->count() > 0 ? 'bg-rose-50' : 'bg-emerald-50' }} flex items-center justify-center text-sm {{ $invoices->where('status','unpaid')->count() > 0 ? 'text-rose-600' : 'text-emerald-600' }}"><i class="bi bi-receipt"></i></span>
                </div>
                <p class="text-3xl font-bold {{ $invoices->where('status','unpaid')->count() > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                    {{ $invoices->where('status','unpaid')->count() }}
                </p>
                <p class="mt-1 text-xs text-slate-400">Unpaid invoices</p>
            </div>
        </div>

        {{-- Attendance Trend Chart --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-6">
            <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-graph-up me-1 text-indigo-500"></i>Attendance Trend (Last 6 Months)</p>
            <div id="chart-student-attendance" style="min-height:160px"></div>
        </div>

        {{-- Marks + Upcoming Exams --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 flex justify-between items-center">
                    <p class="text-sm font-semibold text-slate-700"><i class="bi bi-clipboard-data me-1 text-blue-500"></i>Recent Marks</p>
                    <a href="{{ route('course.student.list.show', ['student_id' => $student->id]) }}" class="text-xs text-indigo-600 hover:underline">View all</a>
                </div>
                @if($marks->count())
                <div class="divide-y divide-slate-50">
                    @foreach($marks as $mark)
                    <div class="px-5 py-3 flex justify-between items-center">
                        <span class="text-sm text-slate-600">{{ $mark->exam->name ?? 'Exam' }}</span>
                        <span class="text-sm font-bold text-slate-800 bg-slate-100 px-2.5 py-0.5 rounded-lg">{{ $mark->mark }}</span>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-400 text-center py-10">No marks recorded yet.</p>
                @endif
            </div>

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100">
                    <p class="text-sm font-semibold text-slate-700"><i class="bi bi-file-earmark-text me-1 text-rose-500"></i>Upcoming Exams</p>
                </div>
                @if($upcomingExams->count())
                <div class="divide-y divide-slate-50">
                    @foreach($upcomingExams as $exam)
                    <div class="px-5 py-3 flex justify-between items-center">
                        <p class="text-sm text-slate-700">{{ $exam->exam_name }}</p>
                        <span class="text-xs font-medium text-rose-700 bg-rose-50 px-2 py-1 rounded-lg">
                            {{ \Carbon\Carbon::parse($exam->start_date)->format('M d') }}
                        </span>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-400 text-center py-10">No upcoming exams.</p>
                @endif
            </div>
        </div>

        {{-- Invoices + Notices --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100">
                    <p class="text-sm font-semibold text-slate-700"><i class="bi bi-receipt me-1 text-rose-500"></i>Fee Invoices</p>
                </div>
                @if($invoices->count())
                <div class="divide-y divide-slate-50">
                    @foreach($invoices as $inv)
                    <div class="px-5 py-3 flex justify-between items-center">
                        <div>
                            <p class="text-sm text-slate-700">{{ $inv->title }}</p>
                            @if($inv->due_date)
                            <p class="text-xs text-slate-400">Due: {{ \Carbon\Carbon::parse($inv->due_date)->format('M d, Y') }}</p>
                            @endif
                        </div>
                        <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $inv->status === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                            {{ ucfirst($inv->status) }}
                        </span>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-400 text-center py-6">No invoices.</p>
                @endif
            </div>

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100">
                    <p class="text-sm font-semibold text-slate-700"><i class="bi bi-megaphone me-1 text-amber-500"></i>Notices</p>
                </div>
                @if($notices->count())
                <div class="divide-y divide-slate-50">
                    @foreach($notices->take(5) as $notice)
                    <div class="px-5 py-3 text-sm text-slate-600 line-clamp-2">
                        {!! \Stevebauman\Purify\Facades\Purify::clean(strip_tags($notice->notice)) !!}
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-400 text-center py-6">No notices.</p>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var months  = @json($attendanceTrend->pluck('month')->map(fn($m) => \Carbon\Carbon::create()->month((int)$m)->format('M')));
    var present = @json($attendanceTrend->pluck('present')->map(fn($v) => (int)$v));
    var absent  = @json($attendanceTrend->pluck('absent')->map(fn($v) => (int)$v));

    if (document.getElementById('chart-student-attendance')) {
        new ApexCharts(document.getElementById('chart-student-attendance'), {
            chart: { type: 'bar', height: 160, stacked: true, toolbar: { show: false } },
            series: [
                { name: 'Present', data: present },
                { name: 'Absent',  data: absent  },
            ],
            xaxis: { categories: months, labels: { style: { fontSize: '10px' } } },
            yaxis: { labels: { style: { fontSize: '10px' } } },
            colors: ['#6366f1', '#f43f5e'],
            plotOptions: { bar: { borderRadius: 3, columnWidth: '50%' } },
            dataLabels: { enabled: false },
            legend: { position: 'top', fontSize: '12px' },
            grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
        }).render();
    }
})();
</script>
@endpush
