@extends('layouts.app')

@section('content')

    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        {{-- Header --}}
        <div class="mb-7">
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Student Dashboard</h1>
            <p class="text-slate-400 text-sm mt-0.5">{{ now()->format('l, F j, Y') }} &middot; Welcome, <span class="font-medium text-slate-600">{{ $student->first_name }}</span></p>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
            <a href="{{ route('student.attendance.show', $student->id) }}"
               class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition block">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Attendance</p>
                    <span class="w-8 h-8 rounded-lg {{ $attendancePct >= 75 ? 'bg-emerald-50' : 'bg-rose-50' }} flex items-center justify-center text-sm {{ $attendancePct >= 75 ? 'text-emerald-600' : 'text-rose-600' }}"><i class="bi bi-calendar-check"></i></span>
                </div>
                <p class="text-3xl font-bold {{ $attendancePct >= 75 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $attendancePct }}%</p>
                <p class="mt-1 text-xs text-slate-400">{{ $presentCount }}/{{ $totalAttendance }}</p>
                <div class="mt-2 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full {{ $attendancePct >= 75 ? 'bg-emerald-500' : 'bg-rose-500' }}" style="width:{{ $attendancePct }}%"></div>
                </div>
            </a>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Class</p>
                    <span class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 text-sm"><i class="bi bi-mortarboard"></i></span>
                </div>
                <p class="text-xl font-bold text-slate-800">{{ $classInfo?->schoolClass?->class_name ?? '—' }}</p>
                <p class="mt-1 text-xs text-slate-400">{{ $classInfo?->section?->section_name ?? '—' }}</p>
            </div>
            <a href="{{ route('results.index') }}"
               class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition block">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Results</p>
                    <span class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 text-sm"><i class="bi bi-clipboard-data"></i></span>
                </div>
                <p class="text-3xl font-bold text-slate-800">{{ $marks->count() }}</p>
                <p class="mt-1 text-xs text-blue-600">Marks recorded</p>
            </a>
            <a href="{{ route('homework.index') }}"
               class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition block {{ $homeworkDueCount > 0 ? 'ring-2 ring-amber-200' : '' }}">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">HW Due</p>
                    <span class="w-8 h-8 rounded-lg {{ $homeworkDueCount > 0 ? 'bg-amber-50' : 'bg-slate-50' }} flex items-center justify-center {{ $homeworkDueCount > 0 ? 'text-amber-600' : 'text-slate-400' }} text-sm"><i class="bi bi-pencil-fill"></i></span>
                </div>
                <p class="text-3xl font-bold {{ $homeworkDueCount > 0 ? 'text-amber-600' : 'text-slate-400' }}">{{ $homeworkDueCount }}</p>
                <p class="mt-1 text-xs text-slate-400">Pending</p>
            </a>
            <a href="{{ route('payments.index') }}"
               class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition block">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Fees Due</p>
                    <span class="w-8 h-8 rounded-lg {{ $invoices->where('status','unpaid')->count() > 0 ? 'bg-rose-50' : 'bg-emerald-50' }} flex items-center justify-center text-sm {{ $invoices->where('status','unpaid')->count() > 0 ? 'text-rose-600' : 'text-emerald-600' }}"><i class="bi bi-receipt"></i></span>
                </div>
                <p class="text-3xl font-bold {{ $invoices->where('status','unpaid')->count() > 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ $invoices->where('status','unpaid')->count() }}</p>
                <p class="mt-1 text-xs text-slate-400">Unpaid invoices</p>
            </a>
            <a href="{{ route('leave.index') }}"
               class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition block {{ $pendingLeaveCount > 0 ? 'ring-2 ring-violet-200' : '' }}">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Leave</p>
                    <span class="w-8 h-8 rounded-lg bg-violet-50 flex items-center justify-center text-violet-600 text-sm"><i class="bi bi-calendar-x-fill"></i></span>
                </div>
                <p class="text-3xl font-bold text-violet-600">{{ $pendingLeaveCount }}</p>
                <p class="mt-1 text-xs text-slate-400">Pending</p>
            </a>
        </div>

        {{-- Attendance Trend Chart --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-6">
            <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-graph-up me-1 text-indigo-500"></i>Attendance Trend (Last 6 Months)</p>
            <div id="chart-student-attendance" style="min-height:160px"></div>
        </div>

        {{-- Today's Schedule + Pending Homework --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

            {{-- Today's timetable --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 flex justify-between items-center">
                    <p class="text-sm font-semibold text-slate-700"><i class="bi bi-calendar4-week me-1 text-violet-500"></i>Today's Classes</p>
                    <a href="{{ route('section.routine.show') }}" class="text-xs text-indigo-600 hover:underline">Full timetable</a>
                </div>
                @if($todaySchedule->count())
                <div class="divide-y divide-slate-50">
                    @foreach($todaySchedule as $slot)
                    <div class="px-5 py-3 flex items-center gap-3">
                        <div class="w-1 self-stretch rounded-full flex-shrink-0" style="background:{{ $slot->color ?? '#6366f1' }}"></div>
                        <div class="w-14 flex-shrink-0">
                            <p class="text-xs font-bold text-slate-700">{{ \Carbon\Carbon::parse($slot->start)->format('H:i') }}</p>
                            <p class="text-[10px] text-slate-400">{{ \Carbon\Carbon::parse($slot->end)->format('H:i') }}</p>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-700 truncate">{{ $slot->course?->course_name ?? '—' }}</p>
                            @if($slot->teacher)
                            <p class="text-xs text-slate-400 truncate"><i class="bi bi-person me-0.5"></i>{{ $slot->teacher->full_name }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="px-5 py-10 text-center">
                    <i class="bi bi-calendar-check text-3xl text-slate-200 block mb-2"></i>
                    <p class="text-sm text-slate-400">No classes today.</p>
                </div>
                @endif
            </div>

            {{-- Pending homework --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 flex justify-between items-center">
                    <p class="text-sm font-semibold text-slate-700">
                        <i class="bi bi-pencil-fill me-1 text-amber-500"></i>Pending Homework
                        @if($homeworkDueCount > 0)
                        <span class="ml-1 text-[10px] bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded-full">{{ $homeworkDueCount }}</span>
                        @endif
                    </p>
                    <a href="{{ route('homework.index') }}" class="text-xs text-indigo-600 hover:underline">All homework</a>
                </div>
                @if($pendingHomework->count())
                <div class="divide-y divide-slate-50">
                    @foreach($pendingHomework as $hw)
                    <a href="{{ route('homework.show', $hw->id) }}" class="px-5 py-3 flex items-center justify-between gap-3 hover:bg-slate-50 transition block">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-700 truncate">{{ $hw->title }}</p>
                            <p class="text-xs text-slate-400">{{ $hw->course?->course_name ?? '—' }}</p>
                        </div>
                        <span class="flex-shrink-0 text-xs font-medium {{ $hw->due_date->isToday() ? 'text-rose-600 bg-rose-50' : 'text-amber-600 bg-amber-50' }} px-2 py-0.5 rounded-lg">
                            {{ $hw->due_date->isToday() ? 'Today' : $hw->due_date->format('M d') }}
                        </span>
                    </a>
                    @endforeach
                </div>
                @else
                <div class="px-5 py-10 text-center">
                    <i class="bi bi-check2-all text-3xl text-slate-200 block mb-2"></i>
                    <p class="text-sm text-slate-400">All caught up! No pending homework.</p>
                </div>
                @endif
            </div>
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

        {{-- Practice Quizzes + Formal Exams --}}
        @if($practiceQuizzes->count() || $formalExams->count())
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

            {{-- Practice Quizzes --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 flex justify-between items-center">
                    <p class="text-sm font-semibold text-slate-700">
                        <i class="bi bi-lightning-fill me-1 text-emerald-500"></i>Practice Quizzes
                        @if($practiceQuizzes->count())
                        <span class="ml-1 text-[10px] bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded-full">{{ $practiceQuizzes->count() }}</span>
                        @endif
                    </p>
                    <a href="{{ route('online-exams.student.index') }}" class="text-xs text-indigo-600 hover:underline">All</a>
                </div>
                @if($practiceQuizzes->count())
                <div class="divide-y divide-slate-50">
                    @foreach($practiceQuizzes->take(4) as $quiz)
                    <div class="px-5 py-3 flex items-center justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-700 truncate">{{ $quiz->title }}</p>
                            <p class="text-xs text-slate-400">
                                {{ $quiz->course?->course_name ?? 'General' }}
                                · {{ $quiz->questions()->count() }} questions
                                · {{ $quiz->duration_minutes }} min
                                @if($quiz->last_attempt && $quiz->last_attempt->score !== null)
                                · <span class="text-indigo-600">Last: {{ $quiz->last_attempt->score }}/{{ $quiz->total_marks }}</span>
                                @endif
                            </p>
                        </div>
                        <a href="{{ route('online-exams.student.start', $quiz->id) }}"
                           class="flex-shrink-0 inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg text-xs font-medium transition">
                            <i class="bi bi-play-fill"></i>
                            {{ $quiz->attempts_used > 0 ? 'Retry' : 'Start' }}
                        </a>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="px-5 py-8 text-center">
                    <i class="bi bi-lightning text-3xl text-slate-200 block mb-2"></i>
                    <p class="text-sm text-slate-400">No practice quizzes available.</p>
                </div>
                @endif
            </div>

            {{-- Formal Online Exams --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 flex justify-between items-center">
                    <p class="text-sm font-semibold text-slate-700">
                        <i class="bi bi-laptop-fill me-1 text-indigo-500"></i>Online Exams
                        @if($formalExams->count())
                        <span class="ml-1 text-[10px] bg-indigo-100 text-indigo-700 px-1.5 py-0.5 rounded-full">{{ $formalExams->count() }}</span>
                        @endif
                    </p>
                    <a href="{{ route('online-exams.student.index') }}" class="text-xs text-indigo-600 hover:underline">All</a>
                </div>
                @if($formalExams->count())
                <div class="divide-y divide-slate-50">
                    @foreach($formalExams->take(4) as $exam)
                    <div class="px-5 py-3 flex items-center justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-700 truncate">{{ $exam->title }}</p>
                            <p class="text-xs text-slate-400">
                                {{ $exam->course?->course_name ?? 'General' }}
                                · {{ $exam->duration_minutes }} min
                                · Pass: {{ $exam->pass_marks }}/{{ $exam->total_marks }}
                                @if($exam->last_attempt && $exam->last_attempt->score !== null)
                                · <span class="{{ $exam->last_attempt->passed ? 'text-emerald-600' : 'text-rose-600' }}">{{ $exam->last_attempt->passed ? '✓ Passed' : '✗ Failed' }}</span>
                                @endif
                            </p>
                        </div>
                        @if($exam->can_attempt)
                        <a href="{{ route('online-exams.student.start', $exam->id) }}"
                           class="flex-shrink-0 inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-medium transition">
                            <i class="bi bi-play-fill"></i>
                            {{ $exam->attempts_used > 0 ? 'Retry' : 'Start' }}
                        </a>
                        @else
                        <span class="flex-shrink-0 text-xs text-slate-400 bg-slate-100 px-3 py-1.5 rounded-lg">Done</span>
                        @endif
                    </div>
                    @endforeach
                </div>
                @else
                <div class="px-5 py-8 text-center">
                    <i class="bi bi-laptop text-3xl text-slate-200 block mb-2"></i>
                    <p class="text-sm text-slate-400">No online exams scheduled.</p>
                </div>
                @endif
            </div>
        </div>
        @endif

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
