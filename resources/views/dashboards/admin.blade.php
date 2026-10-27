@extends('layouts.app')
@section('title','Dashboard')
@section('content')

{{-- ═══════════════════════════════════════════════════════════
     DASHBOARD STYLES
═══════════════════════════════════════════════════════════ --}}
@push('head-scripts')
<style>
.iemis-stat-card {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 14px !important;
    padding: 12px 14px !important;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04) !important;
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    gap: 12px !important;
    text-decoration: none !important;
    transition: all 0.2s ease-in-out !important;
    position: relative !important;
    min-width: 0 !important;
    overflow: hidden !important;
}
.iemis-stat-card:hover {
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.08) !important;
    transform: translateY(-1px) !important;
    border-color: #cbd5e1 !important;
}
.iemis-icon-wrapper {
    width: 38px !important;
    height: 38px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 1.85rem !important;
    line-height: 1 !important;
    flex-shrink: 0 !important;
}
.iemis-card-details {
    display: flex !important;
    flex-direction: column !important;
    justify-content: center !important;
    align-items: flex-start !important;
    flex: 1 !important;
    min-width: 0 !important;
    overflow: hidden !important;
}
.iemis-title {
    font-size: 12px !important;
    font-weight: 700 !important;
    color: #64748b !important;
    line-height: 1.2 !important;
    margin-bottom: 2px !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}
.iemis-num-group {
    display: flex !important;
    flex-wrap: wrap !important;
    align-items: baseline !important;
    gap: 2px 6px !important;
    width: 100% !important;
}
.iemis-value {
    font-size: 18px !important;
    font-weight: 800 !important;
    color: #0f172a !important;
    line-height: 1.15 !important;
    letter-spacing: -0.01em !important;
    white-space: nowrap !important;
}
.iemis-badge-green {
    font-size: 10.5px !important;
    font-weight: 700 !important;
    color: #059669 !important;
    white-space: nowrap !important;
}
.iemis-badge-amber {
    font-size: 10.5px !important;
    font-weight: 700 !important;
    color: #d97706 !important;
    white-space: nowrap !important;
}
.iemis-badge-teal {
    font-size: 10.5px !important;
    font-weight: 700 !important;
    color: #0d9488 !important;
    white-space: nowrap !important;
}
.iemis-badge-rose {
    font-size: 10.5px !important;
    font-weight: 700 !important;
    color: #dc2626 !important;
    white-space: nowrap !important;
}
.stat-card .card-value {
    font-size: 22px;
    font-weight: 800;
    color: #0f172a !important;
    line-height: 1.1;
    margin: 3px 0 2px;
}
.stat-card .card-sub {
    font-size: 11px;
    font-weight: 700;
    line-height: 1.1;
}
.stat-card .card-sparkline {
    margin: 0 -1px -1px -1px;
    height: 44px;
    flex-shrink: 0;
}
:root {
    --dash-heading: #0f172a;
    --dash-text: #334155;
    --dash-muted: #64748b;
}
html[data-theme="dark"],
html.dark {
    --dash-heading: #f8fafc;
    --dash-text: #e2e8f0;
    --dash-muted: #94a3b8;
}

.dashboard-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 18px;
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
    display: flex;
    flex-direction: column;
}
html[data-theme="dark"] .dashboard-card,
html.dark .dashboard-card {
    background: #111927 !important;
    border-color: #223049 !important;
    box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.4) !important;
    color: #cbd5e1 !important;
}

.section-card {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 14px !important;
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04) !important;
    overflow: hidden;
}
html[data-theme="dark"] .section-card,
html.dark .section-card {
    background: #111927 !important;
    border-color: #223049 !important;
    box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.4) !important;
    color: #cbd5e1 !important;
}
.section-card .sc-header {
    padding: 14px 18px 12px;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
html[data-theme="dark"] .section-card .sc-header,
html.dark .section-card .sc-header {
    border-color: #1e293b !important;
}
.section-card .sc-title {
    font-size: 14px !important;
    font-weight: 800 !important;
    color: #1e3c72 !important;
    display: flex;
    align-items: center;
    gap: 8px;
}
html[data-theme="dark"] .section-card .sc-title,
html.dark .section-card .sc-title {
    color: #f1f5f9 !important;
}
.section-card .sc-link {
    font-size: 11px;
    font-weight: 700;
    color: #2563eb !important;
    text-decoration: none;
}
.section-card .sc-link:hover { text-decoration: underline; }
.sc-body { padding: 16px 18px; }
.empty-state {
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    padding: 32px 16px;
    color: #94a3b8 !important;
    font-size: 12px;
    gap: 8px;
}
.empty-state i { font-size: 24px; opacity: .4; }
.row-item {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 10px;
    border-radius: 10px;
    transition: background .15s;
}
.row-item:hover { background: #f8fafc; }
html[data-theme="dark"] .row-item:hover,
html.dark .row-item:hover {
    background: #1e293b !important;
}
.header-notice-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 600;
    color: #475569 !important;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    text-decoration: none;
    transition: all .15s ease;
}
.header-notice-btn:hover {
    background: #f8fafc;
}
html[data-theme="dark"] .header-notice-btn,
html.dark .header-notice-btn {
    background: #1e293b !important;
    border-color: #334155 !important;
    color: #cbd5e1 !important;
}
html[data-theme="dark"] .header-notice-btn:hover,
html.dark .header-notice-btn:hover {
    background: #27354f !important;
}
</style>
@endpush

<div style="padding: 28px 24px 40px; display:flex; flex-direction:column; gap:24px;">

{{-- ── HEADER ── --}}
<div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px;">
    <div>
        <h1 style="font-size:22px; font-weight:900; color:var(--dash-heading) !important; letter-spacing:-.02em; margin:0;">
            Good {{ now()->hour < 12 ? 'Morning' : (now()->hour < 18 ? 'Afternoon' : 'Evening') }}, {{ auth()->user()->first_name }}! 👋
        </h1>
        <p style="font-size:11px; color:#94a3b8 !important; margin:3px 0 0; font-weight:500;">
            {{ now()->format('l, F j, Y') }} &bull; {{ now()->format('g:i A') }}
            &bull; <span style="color:#6366f1;font-weight:700;">आ.व. {{ \App\Services\NepaliDateService::currentFiscalYear() }}</span>
        </p>
    </div>
    <div style="display:flex; flex-wrap:wrap; gap:8px; align-items:center;">
        @can('create students')
        <a href="{{ route('student.create.show') }}" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:10px;font-size:12px;font-weight:700;color:#fff !important;background:linear-gradient(135deg,#2563eb,#4f46e5);border:none;text-decoration:none;box-shadow:0 2px 8px rgba(37,99,235,.35);">
            <i class="bi bi-person-plus"></i> New Student
        </a>
        @endcan
        @can('create notices')
        <a href="{{ route('notice.create') }}" class="header-notice-btn">
            <i class="bi bi-megaphone" style="color:#f59e0b;"></i> Notice
        </a>
        @endcan
        @if($pendingAdmissions > 0)
        <a href="{{ route('admissions.index') }}" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:10px;font-size:12px;font-weight:700;color:#92400e !important;background:#fef3c7;border:1px solid #fcd34d;text-decoration:none;">
            <i class="bi bi-person-check"></i> {{ $pendingAdmissions }} Pending
        </a>
        @endif
    </div>
</div>

{{-- ── 6 STAT CARDS (CEHRD IEMIS CARD STYLE) ── --}}
<div style="display:grid; grid-template-columns:repeat(2,1fr); gap:12px;"
     id="stat-cards-grid">
<style>
@media(min-width:640px){ #stat-cards-grid{ grid-template-columns:repeat(3,1fr)!important; } }
@media(min-width:1200px){ #stat-cards-grid{ grid-template-columns:repeat(6,1fr)!important; } }
</style>

{{-- Students --}}
<a href="{{ route('student.list.show') }}" class="iemis-stat-card">
    <div class="iemis-icon-wrapper">🎓</div>
    <div class="iemis-card-details">
        <span class="iemis-title">Students</span>
        <div class="iemis-num-group">
            <span class="iemis-value">{{ number_format($studentCount) }}</span>
            @if($enrollmentGrowthPct !== null)
                @if($enrollmentGrowthPct >= 0)
                    <span class="iemis-badge-green">+{{ $enrollmentGrowthPct }}%</span>
                @else
                    <span class="iemis-badge-rose">{{ $enrollmentGrowthPct }}%</span>
                @endif
            @else
                <span class="iemis-badge-amber">New</span>
            @endif
        </div>
    </div>
</a>

{{-- Teachers --}}
<a href="{{ route('teacher.list.show') }}" class="iemis-stat-card">
    <div class="iemis-icon-wrapper">🧑‍🏫</div>
    <div class="iemis-card-details">
        <span class="iemis-title">Teachers</span>
        <div class="iemis-num-group">
            <span class="iemis-value">{{ number_format($teacherCount) }}</span>
            <span class="iemis-badge-green">Active</span>
        </div>
    </div>
</a>

{{-- Staff --}}
<div class="iemis-stat-card">
    <div class="iemis-icon-wrapper">💼</div>
    <div class="iemis-card-details">
        <span class="iemis-title">Staff</span>
        <div class="iemis-num-group">
            <span class="iemis-value">{{ number_format($staffCount) }}</span>
            <span class="iemis-badge-green">{{ $teachersPresentToday }} present</span>
        </div>
    </div>
</div>

{{-- Classes --}}
<a href="{{ url('classes') }}" class="iemis-stat-card">
    <div class="iemis-icon-wrapper">🏫</div>
    <div class="iemis-card-details">
        <span class="iemis-title">Classes</span>
        <div class="iemis-num-group">
            <span class="iemis-value">{{ number_format($classCount) }}</span>
            <span class="iemis-badge-amber">This session</span>
        </div>
    </div>
</a>

{{-- Revenue --}}
<a href="{{ route('finance.overview') }}" class="iemis-stat-card">
    <div class="iemis-icon-wrapper">💰</div>
    <div class="iemis-card-details">
        <span class="iemis-title">Revenue</span>
        <div class="iemis-num-group">
            <span class="iemis-value">Rs.{{ number_format($monthRevenue) }}</span>
            <span class="iemis-badge-teal">This month</span>
        </div>
    </div>
</a>

{{-- Unpaid --}}
<a href="{{ route('payments.index') }}" class="iemis-stat-card" style="{{ $pendingInvoices > 0 ? 'border-color:#fca5a5 !important;' : '' }}">
    <div class="iemis-icon-wrapper">🧾</div>
    <div class="iemis-card-details">
        <span class="iemis-title">Unpaid</span>
        <div class="iemis-num-group">
            <span class="iemis-value" style="{{ $pendingInvoices > 0 ? 'color:#ef4444 !important;' : '' }}">{{ number_format($pendingInvoices) }}</span>
            <span class="{{ $pendingInvoices > 0 ? 'iemis-badge-rose' : 'iemis-badge-green' }}">
                {{ $pendingInvoices > 0 ? 'Pending' : 'Cleared' }}
            </span>
        </div>
    </div>
</a>

</div>

{{-- ── ROW 1: Today's Attendance (2/5) + Fee Collection (3/5) ── --}}
<div style="display:grid; grid-template-columns:1fr; gap:18px;" id="row1-grid">
<style>@media(min-width:1024px){#row1-grid{grid-template-columns:2fr 3fr!important;}}</style>

    {{-- 1. Today's Attendance --}}
    <div class="dashboard-card" style="justify-content:space-between;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <span style="font-size:14px;font-weight:800;color:var(--dash-heading) !important;">Today's Attendance</span>
            <span style="font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;
                {{ $attendancePct >= 75 ? 'background:#dcfce7;color:#15803d !important;' : 'background:#fee2e2;color:#dc2626 !important;' }}">
                {{ $attendancePct }}% Rate
            </span>
        </div>
        <div style="display:flex;align-items:center;justify-content:center;margin:4px 0;">
            <div id="chart-attendance-donut" style="width:100%;min-height:185px;"></div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:10px;padding-top:12px;border-top:1px solid #f1f5f9;">
            <div style="text-align:center;background:#f0fdf4;border:1px solid #dcfce7;border-radius:10px;padding:8px;">
                <div style="font-size:18px;font-weight:900;color:#166534 !important;">{{ number_format($todayPresent) }}</div>
                <div style="font-size:10px;color:#15803d !important;font-weight:700;">Present</div>
            </div>
            <div style="text-align:center;background:#fff1f2;border:1px solid #ffe4e6;border-radius:10px;padding:8px;">
                <div style="font-size:18px;font-weight:900;color:#9f1239 !important;">{{ number_format($todayAbsent) }}</div>
                <div style="font-size:10px;color:#be123c !important;font-weight:700;">Absent</div>
            </div>
        </div>
        <a href="{{ route('attendance.list.show') }}" style="display:inline-flex;align-items:center;gap:5px;margin-top:12px;font-size:11px;font-weight:700;color:#2563eb !important;text-decoration:none;">
            View Attendance Report →
        </a>
    </div>

    {{-- 2. Fee Collection vs Outstanding --}}
    <div class="dashboard-card" style="justify-content:space-between;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <span style="font-size:14px;font-weight:800;color:var(--dash-heading) !important;">Fee Collection vs Outstanding</span>
            <a href="{{ route('payments.index') }}" style="font-size:11px;font-weight:700;color:#2563eb !important;text-decoration:none;">View Payments →</a>
        </div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:12px;">
            <div style="background:#f0fdf4;border:1px solid #dcfce7;border-radius:10px;padding:8px;text-align:center;">
                <div style="font-size:9px;color:#15803d !important;font-weight:800;text-transform:uppercase;">Collected</div>
                <div style="font-size:13px;font-weight:900;color:#166534 !important;margin-top:2px;">Rs.{{ number_format($totalFeeCollected) }}</div>
            </div>
            <div style="background:#fff1f2;border:1px solid #ffe4e6;border-radius:10px;padding:8px;text-align:center;">
                <div style="font-size:9px;color:#be123c !important;font-weight:800;text-transform:uppercase;">Outstanding</div>
                <div style="font-size:13px;font-weight:900;color:#9f1239 !important;margin-top:2px;">Rs.{{ number_format($totalOutstandingFees) }}</div>
            </div>
            <div style="background:#eff6ff;border:1px solid #dbeafe;border-radius:10px;padding:8px;text-align:center;">
                <div style="font-size:9px;color:#1d4ed8 !important;font-weight:800;text-transform:uppercase;">Rate</div>
                <div style="font-size:13px;font-weight:900;color:#1e3a8a !important;margin-top:2px;">{{ $collectionRate }}%</div>
            </div>
        </div>
        <div id="chart-fee-collection" style="flex:1;min-height:180px;"></div>
    </div>

</div>

{{-- ── ROW 2: Class Enrollment (2/3 width) + Gender Distribution (1/3 width) ── --}}
<div style="display:grid;grid-template-columns:1fr;gap:18px;margin-top:18px;" id="row2-grid">
<style>@media(min-width:1024px){#row2-grid{grid-template-columns:2fr 1fr!important;}}</style>

    {{-- 4. Class Enrollment (Wider 2-column span) --}}
    <div class="dashboard-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <span style="font-size:14px;font-weight:800;color:var(--dash-heading) !important;">Class Enrollment (ECD–12)</span>
            <a href="{{ url('classes') }}" style="font-size:11px;font-weight:700;color:#2563eb !important;text-decoration:none;">All Classes →</a>
        </div>
        <div style="flex:1;">
            @if($classEnrollment->count())
            <div id="chart-class-enrollment" style="min-height:240px;"></div>
            @else
            <div class="empty-state"><i class="bi bi-bar-chart"></i>No enrollment data yet.</div>
            @endif
        </div>
    </div>

    {{-- 5. Student Gender Distribution --}}
    <div class="dashboard-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <span style="font-size:14px;font-weight:800;color:var(--dash-heading) !important;">Student Gender Distribution</span>
        </div>
        <div style="flex:1;display:flex;flex-direction:column;justify-content:space-between;">
            <div style="display:flex;align-items:center;justify-content:center;margin:4px 0;">
                <div id="chart-gender" style="width:100%;min-height:185px;"></div>
            </div>
            <div style="padding:8px 12px;background:#f8fafc;border:1px solid #f1f5f9;border-radius:10px;display:flex;align-items:center;justify-content:space-between;font-size:11px;font-weight:700;">
                <span style="color:#208cf0;"><span style="width:8px;height:8px;border-radius:50%;background:#208cf0;display:inline-block;margin-right:4px;"></span>Boys: {{ number_format($maleStudents) }} ({{ $malePct }}%)</span>
                <span style="color:#e9488a;"><span style="width:8px;height:8px;border-radius:50%;background:#e9488a;display:inline-block;margin-right:4px;"></span>Girls: {{ number_format($female) }} ({{ 100 - $malePct }}%)</span>
            </div>
        </div>
    </div>

</div>

{{-- ── ROW 3: Attendance Trend + Avg Marks + Heatmap (3 columns) ── --}}
<div style="display:grid;grid-template-columns:1fr;gap:18px;margin-top:18px;" id="row3-grid">
<style>@media(min-width:1024px){#row3-grid{grid-template-columns:repeat(3,1fr)!important;}}</style>

    {{-- 3. 7-Day Attendance Trend --}}
    <div class="dashboard-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <span style="font-size:14px;font-weight:800;color:var(--dash-heading) !important;">7-Day Attendance Trend</span>
            <a href="{{ route('attendance.analytics') }}" style="font-size:11px;font-weight:700;color:#2563eb !important;text-decoration:none;">Analytics →</a>
        </div>
        <div id="chart-attendance-trend" style="flex:1;min-height:200px;"></div>
    </div>

    {{-- Average Marks by Course --}}
    <div class="dashboard-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <span style="font-size:14px;font-weight:800;color:var(--dash-heading) !important;">Average Marks by Course</span>
            <a href="{{ route('results.index') }}" style="font-size:11px;font-weight:700;color:#2563eb !important;text-decoration:none;">View Results →</a>
        </div>
        <div style="flex:1;">
            @if($coursePerformance->count())
            <div id="chart-course-performance" style="min-height:210px;"></div>
            @else
            <div class="empty-state"><i class="bi bi-award"></i>No marks recorded this session.</div>
            @endif
        </div>
    </div>

    {{-- Attendance Heatmap --}}
    <div class="dashboard-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <span style="font-size:14px;font-weight:800;color:var(--dash-heading) !important;">Attendance Heatmap <span style="font-size:10px;color:#94a3b8;font-weight:400;">(10 weeks)</span></span>
            <a href="{{ route('attendance.analytics') }}" style="font-size:11px;font-weight:700;color:#2563eb !important;text-decoration:none;">Analytics →</a>
        </div>
        <div style="flex:1;">
            <div id="chart-heatmap" style="min-height:220px;"></div>
        </div>
    </div>

</div>
{{-- ── ROW 4: Upcoming Exams + Events + Quick Actions ── --}}
<div style="display:grid;grid-template-columns:1fr;gap:18px;margin-top:18px;" id="row4-grid">
<style>@media(min-width:1024px){#row4-grid{grid-template-columns:repeat(3,1fr)!important;}}</style>

    {{-- Upcoming Exams --}}
    <div class="dashboard-card" style="justify-content:space-between;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <span style="font-size:14px;font-weight:800;color:var(--dash-heading) !important;">Upcoming Exams</span>
            @can('view exams')
            <a href="{{ route('exam.list.show') }}" style="font-size:11px;font-weight:700;color:#2563eb !important;text-decoration:none;">View all →</a>
            @endcan
        </div>
        <div style="flex:1;display:flex;flex-direction:column;gap:8px;">
            @if($upcomingExams->count())
            @foreach($upcomingExams->take(5) as $exam)
            <div style="display:flex;align-items:center;gap:10px;padding:9px 12px;background:#f8fafc;border:1px solid #f1f5f9;border-radius:10px;">
                <div style="width:32px;height:32px;border-radius:8px;background:#fff1f2;border:1px solid #ffe4e6;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-file-earmark-text" style="color:#ef4444;font-size:13px;"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:12px;font-weight:700;color:var(--dash-heading) !important;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $exam->exam_name }}</div>
                    <div style="font-size:10px;color:#64748b !important;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $exam->course?->course_name ?? '—' }}</div>
                </div>
                <span style="font-size:10px;font-weight:800;background:#eef2ff;color:#4f46e5 !important;border:1px solid #c7d2fe;padding:3px 8px;border-radius:6px;flex-shrink:0;">
                    {{ \Carbon\Carbon::parse($exam->start_date)->format('M d') }}
                    <span style="font-size:9px;color:#818cf8;display:block;text-align:center;">{{ bsDate(\Carbon\Carbon::parse($exam->start_date)->format('Y-m-d'), true, 'short') }}</span>
                </span>
            </div>
            @endforeach
            @else
            <div class="empty-state"><i class="bi bi-file-earmark-text"></i>No upcoming exams.</div>
            @endif
        </div>
    </div>

    {{-- Upcoming Events --}}
    <div class="dashboard-card" style="justify-content:space-between;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <span style="font-size:14px;font-weight:800;color:var(--dash-heading) !important;">Upcoming Events</span>
            @can('view events')
            <a href="{{ route('events.show') }}" style="font-size:11px;font-weight:700;color:#2563eb !important;text-decoration:none;">Calendar →</a>
            @endcan
        </div>
        <div style="flex:1;display:flex;flex-direction:column;gap:8px;">
            @if($upcomingEvents->count())
            @foreach($upcomingEvents->take(5) as $evt)
            <div style="display:flex;align-items:center;gap:10px;padding:9px 12px;background:#f8fafc;border:1px solid #f1f5f9;border-radius:10px;">
                <div style="width:32px;height:32px;border-radius:8px;background:#eef2ff;border:1px solid #c7d2fe;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-calendar-event" style="color:#6366f1;font-size:13px;"></i>
                </div>
                <div style="flex:1;min-width:0;font-size:12px;font-weight:700;color:var(--dash-heading) !important;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $evt->title }}</div>
                <span style="font-size:10px;font-weight:800;background:#fefce8;color:#b45309 !important;border:1px solid #fef08a;padding:3px 8px;border-radius:6px;flex-shrink:0;">
                    {{ \Carbon\Carbon::parse($evt->start)->format('M d') }}
                </span>
            </div>
            @endforeach
            @else
            <div class="empty-state"><i class="bi bi-calendar3"></i>No upcoming events.</div>
            @endif
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="dashboard-card" style="justify-content:space-between;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <span style="font-size:14px;font-weight:800;color:var(--dash-heading) !important;">Quick Actions</span>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;flex:1;">
            <a href="{{ route('school.profile.edit') }}" style="text-decoration:none;padding:10px;background:#f8fafc;border:1px solid #f1f5f9;border-radius:10px;display:flex;align-items:center;gap:8px;transition:all .15s;" onmouseover="this.style.background='#eff6ff';this.style.borderColor='#bfdbfe';" onmouseout="this.style.background='#f8fafc';this.style.borderColor='#f1f5f9';">
                <div style="width:28px;height:28px;border-radius:6px;background:#eff6ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="bi bi-building" style="color:#2563eb;font-size:13px;"></i></div>
                <span style="font-size:11px;font-weight:700;color:var(--dash-heading) !important;line-height:1.2;">School Profile</span>
            </a>
            @can('manage roles')
            <a href="{{ route('roles.index') }}" style="text-decoration:none;padding:10px;background:#f8fafc;border:1px solid #f1f5f9;border-radius:10px;display:flex;align-items:center;gap:8px;transition:all .15s;" onmouseover="this.style.background='#eef2ff';this.style.borderColor='#c7d2fe';" onmouseout="this.style.background='#f8fafc';this.style.borderColor='#f1f5f9';">
                <div style="width:28px;height:28px;border-radius:6px;background:#eef2ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="bi bi-shield-check" style="color:#6366f1;font-size:13px;"></i></div>
                <span style="font-size:11px;font-weight:700;color:var(--dash-heading) !important;line-height:1.2;">Manage Roles</span>
            </a>
            @endcan
            @can('view academic settings')
            <a href="{{ route('school.setup') }}" style="text-decoration:none;padding:10px;background:#f8fafc;border:1px solid #f1f5f9;border-radius:10px;display:flex;align-items:center;gap:8px;transition:all .15s;" onmouseover="this.style.background='#fffbeb';this.style.borderColor='#fde68a';" onmouseout="this.style.background='#f8fafc';this.style.borderColor='#f1f5f9';">
                <div style="width:28px;height:28px;border-radius:6px;background:#fffbeb;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="bi bi-gear-wide-connected" style="color:#d97706;font-size:13px;"></i></div>
                <span style="font-size:11px;font-weight:700;color:var(--dash-heading) !important;line-height:1.2;">School Setup</span>
            </a>
            @endcan
            @can('create teachers')
            <a href="{{ route('teacher.create.show') }}" style="text-decoration:none;padding:10px;background:#f8fafc;border:1px solid #f1f5f9;border-radius:10px;display:flex;align-items:center;gap:8px;transition:all .15s;" onmouseover="this.style.background='#f0fdf4';this.style.borderColor='#bbf7d0';" onmouseout="this.style.background='#f8fafc';this.style.borderColor='#f1f5f9';">
                <div style="width:28px;height:28px;border-radius:6px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="bi bi-person-plus" style="color:#16a34a;font-size:13px;"></i></div>
                <span style="font-size:11px;font-weight:700;color:var(--dash-heading) !important;line-height:1.2;">Add Teacher</span>
            </a>
            @endcan
            @can('create invoices')
            <a href="{{ route('payments.create') }}" style="text-decoration:none;padding:10px;background:#f8fafc;border:1px solid #f1f5f9;border-radius:10px;display:flex;align-items:center;gap:8px;transition:all .15s;" onmouseover="this.style.background='#f0fdfa';this.style.borderColor='#99f6e4';" onmouseout="this.style.background='#f8fafc';this.style.borderColor='#f1f5f9';">
                <div style="width:28px;height:28px;border-radius:6px;background:#f0fdfa;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="bi bi-receipt" style="color:#0d9488;font-size:13px;"></i></div>
                <span style="font-size:11px;font-weight:700;color:var(--dash-heading) !important;line-height:1.2;">New Invoice</span>
            </a>
            @endcan
            @can('promote students')
            <a href="{{ url('promotions/index') }}" style="text-decoration:none;padding:10px;background:#f8fafc;border:1px solid #f1f5f9;border-radius:10px;display:flex;align-items:center;gap:8px;transition:all .15s;" onmouseover="this.style.background='#faf5ff';this.style.borderColor='#e9d5ff';" onmouseout="this.style.background='#f8fafc';this.style.borderColor='#f1f5f9';">
                <div style="width:28px;height:28px;border-radius:6px;background:#faf5ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="bi bi-arrow-up-circle" style="color:#7c3aed;font-size:13px;"></i></div>
                <span style="font-size:11px;font-weight:700;color:var(--dash-heading) !important;line-height:1.2;">Promotions</span>
            </a>
            @endcan
        </div>
    </div>

</div>

{{-- ── ROW 5: Top Performers + Recent Admissions + Recent Payments ── --}}
<div style="display:grid;grid-template-columns:1fr;gap:18px;margin-top:18px;" id="row5-grid">
<style>@media(min-width:1024px){#row5-grid{grid-template-columns:repeat(3,1fr)!important;}}</style>

    {{-- Top Performers --}}
    <div class="dashboard-card" style="justify-content:space-between;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <span style="font-size:14px;font-weight:800;color:var(--dash-heading) !important;">Top Performers</span>
        </div>
        <div style="flex:1;display:flex;flex-direction:column;gap:8px;">
            @if($topPerformers->count())
            @foreach($topPerformers->take(6) as $i => $tp)
            @if($tp->student)
            <div style="display:flex;align-items:center;gap:10px;padding:8px 12px;background:#f8fafc;border:1px solid #f1f5f9;border-radius:10px;">
                <span style="width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:900;flex-shrink:0;
                    {{ $i===0?'background:#fef3c7;color:#92400e !important;border:1px solid #fde68a;':($i===1?'background:#f1f5f9;color:#475569 !important;border:1px solid #e2e8f0;':($i===2?'background:#fff7ed;color:#9a3412 !important;border:1px solid #ffedd5;':'background:#ffffff;color:#94a3b8 !important;border:1px solid #f1f5f9;')) }}">
                    {{ $i+1 }}
                </span>
                <img src="{{ $tp->student->avatar }}" style="width:28px;height:28px;border-radius:50%;object-fit:cover;flex-shrink:0;" alt="">
                <div style="flex:1;min-width:0;">
                    <div style="font-size:12px;font-weight:700;color:var(--dash-heading) !important;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $tp->student->full_name }}</div>
                    <div style="font-size:10px;color:#64748b !important;">{{ $tp->exams_count }} exams</div>
                </div>
                <span style="font-size:12px;font-weight:900;flex-shrink:0;color:{{ $tp->avg_marks>=80?'#16a34a':($tp->avg_marks>=60?'#d97706':'#dc2626') }} !important;">
                    {{ $tp->avg_marks }}%
                </span>
            </div>
            @endif
            @endforeach
            @else
            <div class="empty-state"><i class="bi bi-trophy"></i>No marks recorded yet.</div>
            @endif
        </div>
    </div>

    {{-- Recent Admissions --}}
    <div class="dashboard-card" style="justify-content:space-between;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <span style="font-size:14px;font-weight:800;color:var(--dash-heading) !important;">Recent Admissions</span>
            @can('view students')
            <a href="{{ route('student.list.show') }}" style="font-size:11px;font-weight:700;color:#2563eb !important;text-decoration:none;">View all →</a>
            @endcan
        </div>
        <div style="flex:1;display:flex;flex-direction:column;gap:8px;">
            @if($recentAdmissions->count())
            @foreach($recentAdmissions->take(5) as $s)
            <div style="display:flex;align-items:center;gap:10px;padding:8px 12px;background:#f8fafc;border:1px solid #f1f5f9;border-radius:10px;">
                <img src="{{ $s->avatar }}" style="width:30px;height:30px;border-radius:50%;object-fit:cover;flex-shrink:0;" alt="">
                <div style="flex:1;min-width:0;">
                    <div style="font-size:12px;font-weight:700;color:var(--dash-heading) !important;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $s->full_name }}</div>
                    <div style="font-size:10px;color:#64748b !important;">{{ $s->created_at->diffForHumans() }}</div>
                </div>
                @can('view students')
                <a href="{{ route('student.profile.show', $s->id) }}" style="font-size:10px;font-weight:700;color:#2563eb !important;text-decoration:none;flex-shrink:0;background:#eff6ff;border:1px solid #bfdbfe;padding:3px 8px;border-radius:6px;">Profile</a>
                @endcan
            </div>
            @endforeach
            @else
            <div class="empty-state"><i class="bi bi-person-plus"></i>No recent admissions.</div>
            @endif
        </div>
    </div>

    {{-- Recent Payments --}}
    @can('view payments')
    <div class="dashboard-card" style="justify-content:space-between;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <span style="font-size:14px;font-weight:800;color:var(--dash-heading) !important;">Recent Payments</span>
            <a href="{{ route('payments.index') }}" style="font-size:11px;font-weight:700;color:#2563eb !important;text-decoration:none;">View all →</a>
        </div>
        <div style="flex:1;display:flex;flex-direction:column;gap:8px;">
            @if($recentPayments->count())
            @foreach($recentPayments->take(5) as $pmt)
            @php $pSt = $pmt->invoice?->student; @endphp
            <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 12px;background:#f8fafc;border:1px solid #f1f5f9;border-radius:10px;">
                <div style="display:flex;align-items:center;gap:8px;min-width:0;">
                    @if($pSt)
                    <img src="{{ $pSt->avatar }}" style="width:28px;height:28px;border-radius:50%;object-fit:cover;flex-shrink:0;" alt="">
                    @else
                    <div style="width:28px;height:28px;border-radius:50%;background:#e2e8f0;flex-shrink:0;"></div>
                    @endif
                    <div style="min-width:0;">
                        <div style="font-size:12px;font-weight:700;color:var(--dash-heading) !important;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $pSt?->full_name ?? 'Student Payment' }}</div>
                        <div style="font-size:10px;color:#64748b !important;">{{ $pmt->created_at->format('M d, Y') }}</div>
                    </div>
                </div>
                <span style="font-size:12px;font-weight:900;color:#166534 !important;flex-shrink:0;background:#f0fdf4;border:1px solid #dcfce7;padding:3px 8px;border-radius:6px;">
                    Rs.{{ number_format($pmt->amount) }}
                </span>
            </div>
            @endforeach
            @else
            <div class="empty-state"><i class="bi bi-credit-card"></i>No payments recorded.</div>
            @endif
        </div>
    </div>
    @endcan

</div>

{{-- ── ROW 6: Today's Schedule + Birthdays + Notice Board ── --}}
<div style="display:grid;grid-template-columns:1fr;gap:18px;margin-top:18px;" id="row6-grid">
<style>@media(min-width:1024px){#row6-grid{grid-template-columns:repeat(3,1fr)!important;}}</style>

    {{-- Today's Schedule --}}
    <div class="dashboard-card" style="justify-content:space-between;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <span style="font-size:14px;font-weight:800;color:var(--dash-heading) !important;">
                Today's Schedule <span style="font-size:10px;color:#64748b;font-weight:600;">({{ now()->format('l') }})</span>
            </span>
            @can('view routines')
            <a href="{{ route('section.routine.show') }}" style="font-size:11px;font-weight:700;color:#2563eb !important;text-decoration:none;">Timetable →</a>
            @endcan
        </div>
        @if($todaySchedule->count())
        <div style="flex:1;display:flex;flex-direction:column;gap:6px;">
            @foreach($todaySchedule->take(6) as $slot)
            @php
                $slotStart = \Carbon\Carbon::today()->setTimeFromTimeString($slot->start);
                $slotEnd   = \Carbon\Carbon::today()->setTimeFromTimeString($slot->end);
                $isNow     = now()->between($slotStart, $slotEnd);
            @endphp
            <div style="display:flex;align-items:center;gap:10px;padding:8px 12px;background:{{ $isNow ? '#eff6ff' : '#f8fafc' }};border:1px solid {{ $isNow ? '#bfdbfe' : '#f1f5f9' }};border-radius:10px;">
                <div style="width:6px;height:6px;border-radius:50%;flex-shrink:0;background:{{ $isNow ? '#2563eb' : '#cbd5e1' }};"></div>
                <span style="font-size:10px;font-weight:700;font-family:monospace;color:var(--dash-muted) !important;width:76px;flex-shrink:0;">{{ $slot->start }}–{{ $slot->end }}</span>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:12px;font-weight:700;color:var(--dash-heading) !important;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $slot->course?->course_name ?? '—' }}</div>
                    <div style="font-size:10px;color:#64748b !important;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        {{ $slot->schoolClass?->class_name ?? '' }}{{ $slot->section?->section_name ? ' · '.$slot->section->section_name : '' }}
                    </div>
                </div>
                @if($isNow)
                <span style="font-size:9px;background:#2563eb;color:#ffffff !important;padding:2px 7px;border-radius:20px;flex-shrink:0;font-weight:800;">Live</span>
                @endif
            </div>
            @endforeach
        </div>
        @else
        <div class="empty-state"><i class="bi bi-calendar4-range"></i>No classes scheduled today.</div>
        @endif
    </div>

    {{-- Upcoming Birthdays --}}
    <div class="dashboard-card" style="justify-content:space-between;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <span style="font-size:14px;font-weight:800;color:var(--dash-heading) !important;">Upcoming Birthdays</span>
        </div>
        @if($birthdayUsers->count())
        <div style="flex:1;display:flex;flex-direction:column;gap:6px;">
            @foreach($birthdayUsers->take(6) as $u)
            @php
                $bday = \Carbon\Carbon::parse($u->birthday);
                $isToday = $bday->isBirthday();
            @endphp
            <div style="display:flex;align-items:center;gap:10px;padding:8px 12px;background:{{ $isToday ? '#fef3c7' : '#f8fafc' }};border:1px solid {{ $isToday ? '#fde68a' : '#f1f5f9' }};border-radius:10px;">
                <img src="{{ $u->avatar }}" style="width:30px;height:30px;border-radius:50%;object-fit:cover;flex-shrink:0;border:2px solid {{ $isToday ? '#f59e0b' : '#fbcfe8' }};" alt="">
                <div style="flex:1;min-width:0;">
                    <div style="font-size:12px;font-weight:700;color:var(--dash-heading) !important;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        {{ $u->full_name }}
                        @if($isToday)
                        <span style="background:#f59e0b;color:#fff;font-size:9px;font-weight:800;padding:1px 5px;border-radius:4px;margin-left:4px;">TODAY! 🎉</span>
                        @endif
                    </div>
                    <div style="font-size:10px;color:#64748b !important;text-transform:capitalize;">{{ $u->primary_role }}</div>
                </div>
                @if($u->phone)
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $u->phone) }}?text={{ urlencode('Dear ' . $u->first_name . ', Warmest Happy Birthday wishes to you from ' . (auth()->user()->school->name ?? config('app.name')) . '! Have a blessed year ahead! 🎂🎉') }}"
                   target="_blank" style="font-size:10px;font-weight:700;background:#dcfce7;color:#166534;border:1px solid #bbf7d0;padding:3px 8px;border-radius:6px;text-decoration:none;display:flex;align-items:center;gap:3px;" title="Send WhatsApp Wish">
                    <i class="bi bi-whatsapp"></i> Wish
                </a>
                @endif
                <span style="font-size:10px;font-weight:800;background:#fdf2f8;color:#be185d !important;border:1px solid #fbcfe8;padding:3px 8px;border-radius:6px;flex-shrink:0;">
                    🎂 {{ $bday->format('M d') }}
                </span>
            </div>
            @endforeach
        </div>
        @else
        <div class="empty-state"><i class="bi bi-cake2"></i>No birthdays in next 7 days.</div>
        @endif
    </div>

    {{-- Notice Board --}}
    <div class="dashboard-card" style="justify-content:space-between;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <span style="font-size:14px;font-weight:800;color:var(--dash-heading) !important;">Notice Board</span>
            @can('create notices')
            <a href="{{ route('notice.create') }}" style="font-size:11px;font-weight:800;background:#eff6ff;color:#2563eb !important;border:1px solid #bfdbfe;padding:3px 10px;border-radius:6px;text-decoration:none;">+ New Notice</a>
            @endcan
        </div>
        @if($notices->count())
        <div style="flex:1;display:flex;flex-direction:column;gap:6px;">
            @foreach($notices->take(5) as $notice)
            <div style="padding:9px 12px;background:#f8fafc;border:1px solid #f1f5f9;border-radius:10px;">
                <div style="display:flex;gap:8px;align-items:flex-start;">
                    <span style="font-size:12px;flex-shrink:0;margin-top:1px;">📌</span>
                    <p style="font-size:11px;font-weight:600;color:var(--dash-text) !important;line-height:1.4;margin:0;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                        {!! \Stevebauman\Purify\Facades\Purify::clean(strip_tags($notice->notice)) !!}
                    </p>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="empty-state"><i class="bi bi-megaphone"></i>No notices posted yet.</div>
        @endif
    </div>

</div>

{{-- ── ROW 7: Recent Activity Log ── --}}
@if($activityLog->count())
<div class="dashboard-card" style="margin-top:18px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
        <span style="font-size:14px;font-weight:800;color:var(--dash-heading) !important;">Recent Activity Log</span>
    </div>
    <div style="display:flex;flex-direction:column;gap:6px;">
        @foreach($activityLog->take(6) as $log)
        <div style="display:flex;align-items:center;gap:12px;padding:9px 12px;background:#f8fafc;border:1px solid #f1f5f9;border-radius:10px;">
            <div style="width:28px;height:28px;border-radius:50%;overflow:hidden;flex-shrink:0;background:#e2e8f0;display:flex;align-items:center;justify-content:center;">
                @if($log->user)
                <img src="{{ $log->user->avatar }}" style="width:28px;height:28px;object-fit:cover;" alt="">
                @else
                <i class="bi bi-person" style="color:#64748b;font-size:12px;"></i>
                @endif
            </div>
            <div style="flex:1;min-width:0;">
                <p style="font-size:11px;font-weight:600;color:var(--dash-text) !important;margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                    <span style="font-weight:800;color:var(--dash-heading) !important;">{{ $log->user?->full_name ?? 'System' }}</span>
                    — {{ $log->description ?? $log->action ?? 'performed an action' }}
                </p>
            </div>
            <span style="font-size:10px;font-weight:700;color:#64748b !important;flex-shrink:0;white-space:nowrap;background:#ffffff;border:1px solid #e2e8f0;padding:2px 8px;border-radius:6px;">
                {{ $log->created_at->diffForHumans() }}
            </span>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ── FOOTER ── --}}
<div style="padding-top:16px;border-top:1px solid #e2e8f0;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:8px;">
    <p style="font-size:11px;color:#94a3b8 !important;margin:0;">&copy; {{ date('Y') }} NepalSchoolHub. All rights reserved.</p>
    <a href="{{ route('school.setup') }}" style="font-size:11px;color:#94a3b8 !important;text-decoration:none;" onmouseover="this.style.color='#475569'" onmouseout="this.style.color='#94a3b8'">School Setup</a>
</div>

</div>{{-- /main padding wrapper --}}
@endsection

@push('scripts')
<script>
window.addEventListener('load', function () {
    var isDark    = document.documentElement.getAttribute('data-theme') === 'dark';
    var textMuted = isDark ? '#94a3b8' : '#64748b';
    var gridColor = isDark ? '#334155' : '#f1f5f9';

    // ── Sparkline helper ──────────────────────────────────────────────────
    function sparkline(id, color, data) {
        var el = document.getElementById(id);
        if (!el || typeof ApexCharts === 'undefined') return;
        new ApexCharts(el, {
            chart: { type: 'area', sparkline: { enabled: true }, height: 44, animations: { enabled: false } },
            series: [{ data: data }],
            stroke: { curve: 'smooth', width: 2.5 },
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.0 } },
            colors: [color],
            tooltip: { enabled: false },
        }).render();
    }

    sparkline('sparkline-students', '#2563eb', @json($sparklineStudents));
    sparkline('sparkline-teachers', '#10b981', @json($sparklineTeachers));
    sparkline('sparkline-classes',  '#7c3aed', @json($sparklineClasses));
    sparkline('sparkline-revenue',  '#0d9488', @json($sparklineRevenue));

    // ── IEMIS Speedometer Gauge Helper ────────────────────────────────────
    function renderIemisSpeedometer(containerId, percentage, leftLabel, rightLabel, leftColor, rightColor) {
        var el = document.getElementById(containerId);
        if (!el) return;
        
        var pct = Math.min(100, Math.max(0, parseFloat(percentage) || 0));
        var rightPct = (100 - pct).toFixed(1);
        var leftPctStr = pct.toFixed(1) + '%';
        var rightPctStr = rightPct + '%';
        
        leftColor = leftColor || '#2563eb';
        rightColor = rightColor || '#ec4899';
        
        var w = 320, h = 185;
        var cx = 160, cy = 138, r = 96, strokeW = 38;
        
        var pRad = (pct / 100) * Math.PI;
        var splitAngle = Math.PI - pRad;
        
        var xSplit = cx + r * Math.cos(splitAngle);
        var ySplit = cy - r * Math.sin(splitAngle);
        
        // LargeArc is ALWAYS 0 for sub-arcs of a 180° semi-circle
        var leftPath = "M " + (cx - r) + " " + cy + " A " + r + " " + r + " 0 0 1 " + xSplit + " " + ySplit;
        var rightPath = "M " + xSplit + " " + ySplit + " A " + r + " " + r + " 0 0 1 " + (cx + r) + " " + cy;
        
        var leftMid = Math.PI - (pRad / 2);
        var xLeftText = cx + r * Math.cos(leftMid);
        var yLeftText = cy - r * Math.sin(leftMid) + 5;
        
        var rightMid = splitAngle / 2;
        var xRightText = cx + r * Math.cos(rightMid);
        var yRightText = cy - r * Math.sin(rightMid) + 5;
        
        var needleL = 84;
        var tipX = cx + needleL * Math.cos(splitAngle);
        var tipY = cy - needleL * Math.sin(splitAngle);
        
        var perpX = Math.sin(splitAngle) * 5;
        var perpY = Math.cos(splitAngle) * 5;
        
        var b1X = cx - perpX;
        var b1Y = cy - perpY;
        var b2X = cx + perpX;
        var b2Y = cy + perpY;
        
        var isDarkGauge  = document.documentElement.getAttribute('data-theme') === 'dark' || document.documentElement.classList.contains('dark');
        var trackStroke  = isDarkGauge ? '#1e293b' : '#e2e8f0';
        var needleFill   = isDarkGauge ? '#f8fafc' : '#0f172a';
        var centerCircle = isDarkGauge ? '#111927' : '#ffffff';
        var labelMuted   = isDarkGauge ? '#94a3b8' : '#64748b';
        
        var svg = `
        <svg viewBox="0 0 ${w} ${h}" style="width:100%;max-width:320px;height:auto;display:block;margin:0 auto;" xmlns="http://www.w3.org/2000/svg">
            <path d="M ${cx - r} ${cy} A ${r} ${r} 0 0 1 ${cx + r} ${cy}" fill="none" stroke="${trackStroke}" stroke-width="${strokeW}" stroke-linecap="butt" />
            ${pct > 0 ? `<path d="${leftPath}" fill="none" stroke="${leftColor}" stroke-width="${strokeW}" stroke-linecap="butt" />` : ''}
            ${pct < 100 ? `<path d="${rightPath}" fill="none" stroke="${rightColor}" stroke-width="${strokeW}" stroke-linecap="butt" />` : ''}
            ${pct >= 8 ? `<text x="${xLeftText}" y="${yLeftText}" fill="#ffffff" font-size="12" font-weight="900" text-anchor="middle" dominant-baseline="middle">${leftPctStr}</text>` : ''}
            ${(100 - pct) >= 8 ? `<text x="${xRightText}" y="${yRightText}" fill="#ffffff" font-size="12" font-weight="900" text-anchor="middle" dominant-baseline="middle">${rightPctStr}</text>` : ''}
            <polygon points="${tipX},${tipY} ${b1X},${b1Y} ${b2X},${b2Y}" fill="${needleFill}" />
            <circle cx="${cx}" cy="${cy}" r="10" fill="${needleFill}" />
            <circle cx="${cx}" cy="${cy}" r="3.5" fill="${centerCircle}" />
            <text x="${cx - r - 2}" y="${cy + 20}" font-size="12" font-weight="800" fill="${labelMuted}" text-anchor="start">0%</text>
            <text x="${cx - r - 2}" y="${cy + 38}" font-size="14" font-weight="900" fill="${leftColor}" text-anchor="start">${leftLabel}</text>
            <text x="${cx + r + 2}" y="${cy + 20}" font-size="12" font-weight="800" fill="${labelMuted}" text-anchor="end">100%</text>
            <text x="${cx + r + 2}" y="${cy + 38}" font-size="14" font-weight="900" fill="${rightColor}" text-anchor="end">${rightLabel}</text>
        </svg>
        `;
        
        el.innerHTML = svg;
    }

    // ── Today Attendance Speedometer Gauge (Pure Green for Present, Pure Red for Absent) ──
    renderIemisSpeedometer('chart-attendance-donut', {{ $attendancePct }}, 'Present', 'Absent', '#10b981', '#ef4444');

    window.addEventListener('theme-changed', function () {
        renderIemisSpeedometer('chart-attendance-donut', {{ $attendancePct }}, 'Present', 'Absent', '#10b981', '#ef4444');
        renderIemisSpeedometer('chart-gender', {{ $malePct }}, 'Boys', 'Girls', '#2563eb', '#ec4899');
    });

    // ── Fee Collection (grouped bar) ─────────────────────────────────────
    var feeEl = document.getElementById('chart-fee-collection');
    if (feeEl) {
        var feeData     = @json($feeChartData);
        var feeMonths   = feeData.map(function(d){ return d.month; });
        var collected   = feeData.map(function(d){ return parseFloat(d.collected) || 0; });
        var outstanding = feeData.map(function(d){ return parseFloat(d.outstanding) || 0; });
        var hasData     = collected.some(function(v){ return v > 0; }) || outstanding.some(function(v){ return v > 0; });
        if (!hasData) {
            feeEl.innerHTML = '<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:180px;color:#94a3b8;gap:8px;"><i class="bi bi-bar-chart" style="font-size:28px;opacity:.3;"></i><span style="font-size:12px;font-weight:500;">No fee data recorded yet</span></div>';
        } else {
        new ApexCharts(feeEl, {
            chart: { type: 'bar', height: 180, toolbar: { show: false }, fontFamily: 'Inter,sans-serif' },
            series: [{ name: 'Collected', data: collected }, { name: 'Outstanding', data: outstanding }],
            xaxis: { categories: feeMonths, labels: { style: { colors: textMuted, fontSize: '10px' } }, axisBorder: { show: false }, axisTicks: { show: false } },
            yaxis: { labels: { style: { colors: textMuted, fontSize: '10px' }, formatter: function(v){ return 'Rs.'+(v>=1000?(v/1000).toFixed(0)+'k':v); } } },
            colors: ['#10b981', '#f59e0b'],
            plotOptions: { bar: { borderRadius: 4, columnWidth: '52%', grouped: true } },
            dataLabels: { enabled: false },
            legend: { position: 'top', fontSize: '11px', labels: { colors: textMuted } },
            grid: { borderColor: gridColor, strokeDashArray: 4, padding: { left: 0, right: 0 } },
            tooltip: { theme: isDark ? 'dark' : 'light', y: { formatter: function(v){ return 'Rs.'+Number(v).toLocaleString(); } } },
        }).render();
        }
    }

    // ── 7-Day Attendance Trend ────────────────────────────────────────────
    var trendEl = document.getElementById('chart-attendance-trend');
    if (trendEl) {
        @php
            $trendDaysFormatted = $attendanceTrend->pluck('day')->map(fn($d) => \Carbon\Carbon::parse($d)->format('D M d'));
            $trendPresentValues = $attendanceTrend->pluck('present')->map(fn($v) => (int)$v);
            $trendAbsentValues  = $attendanceTrend->pluck('absent')->map(fn($v) => (int)$v);
        @endphp
        var trendDays    = @json($trendDaysFormatted);
        var trendPresent = @json($trendPresentValues);
        var trendAbsent  = @json($trendAbsentValues);
        new ApexCharts(trendEl, {
            chart: { type: 'area', height: 200, toolbar: { show: false }, fontFamily: 'Inter,sans-serif' },
            series: [{ name: 'Present', data: trendPresent }, { name: 'Absent', data: trendAbsent }],
            xaxis: { categories: trendDays, labels: { style: { colors: textMuted, fontSize: '10px' } }, axisBorder: { show: false }, axisTicks: { show: false } },
            yaxis: { labels: { style: { colors: textMuted, fontSize: '10px' } } },
            colors: ['#10b981', '#ef4444'],
            fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.0 } },
            stroke: { curve: 'smooth', width: 2.5 },
            dataLabels: { enabled: false },
            legend: { position: 'top', fontSize: '11px', labels: { colors: textMuted } },
            grid: { borderColor: gridColor, strokeDashArray: 4 },
            tooltip: { theme: isDark ? 'dark' : 'light' },
        }).render();
    }

    // ── Class Enrollment (Vertical Stacked Column Chart like IEMIS) ──────
    var classEl = document.getElementById('chart-class-enrollment');
    if (classEl) {
        var classData  = @json($classEnrollment);
        var classNames = classData.map(function(c){ return c.name; });
        var boysData   = classData.map(function(c){ return parseInt(c.boys) || 0; });
        var girlsData  = classData.map(function(c){ return parseInt(c.girls) || 0; });
        
        new ApexCharts(classEl, {
            chart: {
                type: 'bar',
                height: 250,
                stacked: true,
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif'
            },
            series: [
                { name: 'Boys', data: boysData },
                { name: 'Girls', data: girlsData }
            ],
            colors: ['#208cf0', '#e9488a'],
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '45%',
                    borderRadius: 2
                }
            },
            dataLabels: {
                enabled: true,
                style: {
                    fontSize: '10px',
                    fontWeight: '800',
                    colors: ['#ffffff']
                },
                formatter: function(val) {
                    return val > 0 ? val : '';
                }
            },
            xaxis: {
                categories: classNames,
                title: {
                    text: 'Grade',
                    style: { color: textMuted, fontSize: '12px', fontWeight: '700' }
                },
                labels: {
                    rotate: 0,
                    rotateAlways: false,
                    hideOverlappingLabels: false,
                    style: { colors: textMuted, fontSize: '11px', fontWeight: '700' }
                },
                axisBorder: { show: true, color: '#e2e8f0' },
                axisTicks: { show: false }
            },
            yaxis: {
                title: {
                    text: 'Number of Students',
                    style: { color: textMuted, fontSize: '11px', fontWeight: '700' }
                },
                labels: { style: { colors: textMuted, fontSize: '10px' } }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
                fontSize: '11px',
                fontWeight: 700,
                markers: { radius: 3 },
                labels: { colors: textMuted }
            },
            grid: { borderColor: gridColor, strokeDashArray: 0 },
            tooltip: { theme: isDark ? 'dark' : 'light' }
        }).render();
    }

    // ── Student Gender Speedometer Gauge (Blue for Boys, Pink for Girls) ──
    renderIemisSpeedometer('chart-gender', {{ $malePct }}, 'Boys', 'Girls', '#2563eb', '#ec4899');

    // ── Avg Marks by Course (Shining Multi-Color Gradient Chart) ─────────
    var courseEl = document.getElementById('chart-course-performance');
    if (courseEl) {
        @php
            $courseNamesFormatted = $coursePerformance->map(fn($c) => $c->course?->course_name ?? 'Subject');
            $courseAvgsFormatted  = $coursePerformance->pluck('avg_marks')->map(fn($v) => (float)$v);
        @endphp
        var courseNames = @json($courseNamesFormatted);
        var courseAvgs  = @json($courseAvgsFormatted);
        new ApexCharts(courseEl, {
            chart: { type: 'bar', height: 235, toolbar: { show: false }, fontFamily: 'Inter,sans-serif' },
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 6,
                    barHeight: '58%',
                    distributed: true
                }
            },
            series: [{ name: 'Avg Marks (%)', data: courseAvgs }],
            xaxis: { categories: courseNames, labels: { style: { colors: textMuted, fontSize: '10px' } }, max: 100 },
            yaxis: { labels: { style: { colors: textMuted, fontSize: '10px', fontWeight: '600' } } },
            colors: ['#6366f1', '#06b6d4', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6', '#3b82f6'],
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'light',
                    type: 'horizontal',
                    shadeIntensity: 0.45,
                    gradientToColors: ['#818cf8', '#22d3ee', '#34d399', '#fbbf24', '#f472b6', '#a78bfa', '#60a5fa'],
                    inverseColors: false,
                    opacityFrom: 1,
                    opacityTo: 0.9,
                    stops: [0, 100]
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function(v){ return v+'%'; },
                style: { fontSize: '10px', fontWeight: '800', colors: ['#ffffff'] }
            },
            legend: { show: false },
            grid: { borderColor: gridColor, strokeDashArray: 4 },
            tooltip: { theme: isDark ? 'dark' : 'light', y: { formatter: function(v){ return v+'%'; } } },
        }).render();
    }

    // ── Attendance Heatmap ───────────────────────────────────────────────
    var heatEl = document.getElementById('chart-heatmap');
    if (heatEl) {
        var heatmapRaw  = @json($heatmapData);
        var serverToday = @json(now()->toDateString());
        var dayNames    = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
        var todayMs     = new Date(serverToday).getTime();
        var heatSeries  = dayNames.map(function(day, idx) {
            var values = [];
            for (var w = 9; w >= 0; w--) {
                var pct = 0;
                var allDates = Object.keys(heatmapRaw);
                for (var di = 0; di < allDates.length; di++) {
                    var d = allDates[di];
                    var dt = new Date(d);
                    var isoDay = dt.getUTCDay() === 0 ? 7 : dt.getUTCDay();
                    if (isoDay !== idx + 1) continue;
                    var diffDays  = Math.round((todayMs - dt.getTime()) / 86400000);
                    var diffWeeks = Math.floor(diffDays / 7);
                    if (diffWeeks === w) { pct = parseInt(heatmapRaw[d].pct) || 0; break; }
                }
                values.push({ x: 'W' + (10 - w), y: pct });
            }
            return { name: day, data: values };
        });
        new ApexCharts(heatEl, {
            chart: { type: 'heatmap', height: 220, toolbar: { show: false }, fontFamily: 'Inter,sans-serif' },
            series: heatSeries,
            dataLabels: { enabled: false },
            xaxis: { labels: { style: { colors: textMuted, fontSize: '9px' } } },
            yaxis: { labels: { style: { colors: textMuted, fontSize: '10px' } } },
            tooltip: { theme: isDark ? 'dark' : 'light', y: { formatter: function(v){ return v > 0 ? v+'% attendance' : 'No data'; } } },
            plotOptions: {
                heatmap: {
                    shadeIntensity: 0.9, radius: 4,
                    colorScale: { ranges: [
                        { from: 0,  to: 0,   color: isDark ? '#1e293b' : '#f1f5f9', name: 'No data' },
                        { from: 1,  to: 50,  color: '#fca5a5', name: '<50%' },
                        { from: 51, to: 74,  color: '#fbbf24', name: '51–74%' },
                        { from: 75, to: 89,  color: '#86efac', name: '75–89%' },
                        { from: 90, to: 100, color: '#16a34a', name: '90%+' },
                    ]},
                },
            },
        }).render();
    }
});
</script>
@endpush
