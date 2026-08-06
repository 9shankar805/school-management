@extends('layouts.app')
@section('title','Dashboard')
@section('content')

{{-- ═══════════════════════════════════════════════════════════
     DASHBOARD STYLES
═══════════════════════════════════════════════════════════ --}}
@push('head-scripts')
<style>
.stat-card {
    position: relative;
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.04);
    transition: box-shadow .2s, transform .2s;
    display: flex;
    flex-direction: column;
}
.stat-card:hover {
    box-shadow: 0 4px 20px rgba(0,0,0,.1);
    transform: translateY(-2px);
}
.stat-card .card-accent {
    height: 4px;
    width: 100%;
    flex-shrink: 0;
}
.stat-card .card-body {
    padding: 14px 16px 0 16px;
    flex: 1;
    display: flex;
    flex-direction: column;
}
.stat-card .card-icon {
    width: 36px; height: 36px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 15px;
    color: #fff;
    flex-shrink: 0;
}
.stat-card .card-label {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .07em;
    text-transform: uppercase;
    color: #94a3b8 !important;
    line-height: 1;
}
.stat-card .card-value {
    font-size: 26px;
    font-weight: 900;
    color: #0f172a !important;
    line-height: 1.1;
    margin: 4px 0 2px;
}
.stat-card .card-sub {
    font-size: 10px;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 6px;
}
.stat-card .card-sparkline {
    margin: 0 -1px -1px -1px;
    height: 44px;
    flex-shrink: 0;
}
.section-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,.05);
    overflow: hidden;
}
.section-card .sc-header {
    padding: 14px 18px 12px;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.section-card .sc-title {
    font-size: 13px;
    font-weight: 700;
    color: #1e293b !important;
    display: flex;
    align-items: center;
    gap: 7px;
}
.section-card .sc-link {
    font-size: 11px;
    font-weight: 600;
    color: #3b82f6 !important;
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
</style>
@endpush

<div style="padding: 28px 24px 40px; display:flex; flex-direction:column; gap:24px;">

{{-- ── HEADER ── --}}
<div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px;">
    <div>
        <h1 style="font-size:22px; font-weight:900; color:#0f172a !important; letter-spacing:-.02em; margin:0;">
            Good {{ now()->hour < 12 ? 'Morning' : (now()->hour < 18 ? 'Afternoon' : 'Evening') }}, {{ auth()->user()->first_name }}! 👋
        </h1>
        <p style="font-size:11px; color:#94a3b8 !important; margin:3px 0 0; font-weight:500;">
            {{ now()->format('l, F j, Y') }} &bull; {{ now()->format('g:i A') }}
        </p>
    </div>
    <div style="display:flex; flex-wrap:wrap; gap:8px; align-items:center;">
        @can('create students')
        <a href="{{ route('student.create.show') }}" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:10px;font-size:12px;font-weight:700;color:#fff !important;background:linear-gradient(135deg,#2563eb,#4f46e5);border:none;text-decoration:none;box-shadow:0 2px 8px rgba(37,99,235,.35);">
            <i class="bi bi-person-plus"></i> New Student
        </a>
        @endcan
        @can('create notices')
        <a href="{{ route('notice.create') }}" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:10px;font-size:12px;font-weight:600;color:#475569 !important;background:#fff;border:1px solid #e2e8f0;text-decoration:none;">
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

{{-- ── 6 STAT CARDS ── --}}
<div style="display:grid; grid-template-columns:repeat(2,1fr); gap:14px;"
     class="sm:grid-cols-3 lg:grid-cols-6"
     id="stat-cards-grid">
<style>
@media(min-width:640px){ #stat-cards-grid{ grid-template-columns:repeat(3,1fr)!important; } }
@media(min-width:1024px){ #stat-cards-grid{ grid-template-columns:repeat(6,1fr)!important; } }
</style>

{{-- Students --}}
<a href="{{ route('student.list.show') }}" class="stat-card" style="text-decoration:none;">
    <div class="card-accent" style="background:linear-gradient(90deg,#2563eb,#3b82f6);"></div>
    <div class="card-body">
        <div style="display:flex;align-items:center;gap:9px;margin-bottom:10px;">
            <div class="card-icon" style="background:linear-gradient(135deg,#2563eb,#3b82f6);">
                <i class="bi bi-people-fill"></i>
            </div>
            <span class="card-label">Students</span>
        </div>
        <div class="card-value">{{ number_format($studentCount) }}</div>
        <div class="card-sub" style="color:#10b981 !important;">This session</div>
    </div>
    <div class="card-sparkline" id="sparkline-students"></div>
</a>

{{-- Teachers --}}
<a href="{{ route('teacher.list.show') }}" class="stat-card" style="text-decoration:none;">
    <div class="card-accent" style="background:linear-gradient(90deg,#10b981,#34d399);"></div>
    <div class="card-body">
        <div style="display:flex;align-items:center;gap:9px;margin-bottom:10px;">
            <div class="card-icon" style="background:linear-gradient(135deg,#10b981,#34d399);">
                <i class="bi bi-person-badge-fill"></i>
            </div>
            <span class="card-label">Teachers</span>
        </div>
        <div class="card-value">{{ number_format($teacherCount) }}</div>
        <div class="card-sub" style="color:#10b981 !important;">Active faculty</div>
    </div>
    <div class="card-sparkline" id="sparkline-teachers"></div>
</a>

{{-- Staff --}}
<div class="stat-card">
    <div class="card-accent" style="background:linear-gradient(90deg,#8b5cf6,#a78bfa);"></div>
    <div class="card-body">
        <div style="display:flex;align-items:center;gap:9px;margin-bottom:10px;">
            <div class="card-icon" style="background:linear-gradient(135deg,#8b5cf6,#a78bfa);">
                <i class="bi bi-briefcase-fill"></i>
            </div>
            <span class="card-label">Staff</span>
        </div>
        <div class="card-value">{{ number_format($staffCount) }}</div>
        <div class="card-sub" style="color:{{ $teachersPresentToday > 0 ? '#10b981' : '#94a3b8' }} !important;">
            {{ $teachersPresentToday }} present today
        </div>
    </div>
    <div style="height:44px;background:linear-gradient(to top,rgba(139,92,246,.06),transparent);"></div>
</div>

{{-- Classes --}}
<a href="{{ url('classes') }}" class="stat-card" style="text-decoration:none;">
    <div class="card-accent" style="background:linear-gradient(90deg,#7c3aed,#a855f7);"></div>
    <div class="card-body">
        <div style="display:flex;align-items:center;gap:9px;margin-bottom:10px;">
            <div class="card-icon" style="background:linear-gradient(135deg,#7c3aed,#a855f7);">
                <i class="bi bi-mortarboard-fill"></i>
            </div>
            <span class="card-label">Classes</span>
        </div>
        <div class="card-value">{{ number_format($classCount) }}</div>
        <div class="card-sub" style="color:#f59e0b !important;">This session</div>
    </div>
    <div class="card-sparkline" id="sparkline-classes"></div>
</a>

{{-- Revenue --}}
<a href="{{ route('finance.overview') }}" class="stat-card" style="text-decoration:none;">
    <div class="card-accent" style="background:linear-gradient(90deg,#0d9488,#14b8a6);"></div>
    <div class="card-body">
        <div style="display:flex;align-items:center;gap:9px;margin-bottom:10px;">
            <div class="card-icon" style="background:linear-gradient(135deg,#0d9488,#14b8a6);">
                <i class="bi bi-currency-rupee"></i>
            </div>
            <span class="card-label">Revenue</span>
        </div>
        <div class="card-value" style="font-size:20px !important;">Rs.{{ number_format($monthRevenue) }}</div>
        <div class="card-sub" style="color:#0d9488 !important;">This month</div>
    </div>
    <div class="card-sparkline" id="sparkline-revenue"></div>
</a>

{{-- Unpaid --}}
<a href="{{ route('payments.index') }}" class="stat-card" style="text-decoration:none;{{ $pendingInvoices > 0 ? 'border-color:#fca5a5;' : '' }}">
    <div class="card-accent" style="background:{{ $pendingInvoices > 0 ? 'linear-gradient(90deg,#ef4444,#f87171)' : 'linear-gradient(90deg,#10b981,#34d399)' }};"></div>
    <div class="card-body">
        <div style="display:flex;align-items:center;gap:9px;margin-bottom:10px;">
            <div class="card-icon" style="background:{{ $pendingInvoices > 0 ? 'linear-gradient(135deg,#ef4444,#f87171)' : 'linear-gradient(135deg,#10b981,#34d399)' }};">
                <i class="bi bi-receipt"></i>
            </div>
            <span class="card-label">Unpaid</span>
        </div>
        <div class="card-value" style="color:{{ $pendingInvoices > 0 ? '#ef4444' : '#10b981' }} !important;">
            {{ number_format($pendingInvoices) }}
        </div>
        <div class="card-sub" style="color:{{ $pendingInvoices > 0 ? '#ef4444' : '#10b981' }} !important;">
            {{ $pendingInvoices > 0 ? 'Invoices pending' : 'All cleared' }}
        </div>
    </div>
    <div style="height:44px;background:{{ $pendingInvoices > 0 ? 'linear-gradient(to top,rgba(239,68,68,.06),transparent)' : 'linear-gradient(to top,rgba(16,185,129,.06),transparent)' }};"></div>
</a>

</div>

{{-- ── ROW 1: Attendance Donut (2/5) + Fee Collection (3/5) ── --}}
<div style="display:grid; grid-template-columns:1fr; gap:18px;" id="row1-grid">
<style>@media(min-width:1024px){#row1-grid{grid-template-columns:2fr 3fr!important;}}</style>

    {{-- Attendance Donut --}}
    <div class="section-card" style="display:flex;flex-direction:column;">
        <div class="sc-header">
            <span class="sc-title">
                <span style="width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg,#2563eb,#3b82f6);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:13px;flex-shrink:0;">
                    <i class="bi bi-calendar-check"></i>
                </span>
                Today's Attendance
            </span>
            <span style="font-size:12px;font-weight:700;padding:3px 10px;border-radius:20px;
                {{ $attendancePct >= 75 ? 'background:#dcfce7;color:#15803d !important;' : 'background:#fee2e2;color:#dc2626 !important;' }}">
                {{ $attendancePct }}%
            </span>
        </div>
        <div style="padding:16px;flex:1;display:flex;flex-direction:column;">
            {{-- Donut --}}
            <div style="position:relative;display:flex;align-items:center;justify-content:center;">
                <div id="chart-attendance-donut" style="width:100%;height:180px;"></div>
                <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;pointer-events:none;">
                    <span style="font-size:32px;font-weight:900;color:#0f172a !important;line-height:1;">{{ $attendancePct }}%</span>
                    <span style="font-size:10px;color:#94a3b8 !important;font-weight:600;margin-top:2px;">Attendance Rate</span>
                </div>
            </div>
            {{-- Present / Absent stats --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:14px;padding-top:14px;border-top:1px solid #f1f5f9;">
                <div style="text-align:center;background:#eff6ff;border-radius:12px;padding:10px 8px;">
                    <div style="font-size:22px;font-weight:900;color:#2563eb !important;">{{ number_format($todayPresent) }}</div>
                    <div style="font-size:10px;color:#64748b !important;font-weight:600;margin-top:2px;">Present</div>
                </div>
                <div style="text-align:center;background:#fff1f2;border-radius:12px;padding:10px 8px;">
                    <div style="font-size:22px;font-weight:900;color:#e11d48 !important;">{{ number_format($todayAbsent) }}</div>
                    <div style="font-size:10px;color:#64748b !important;font-weight:600;margin-top:2px;">Absent</div>
                </div>
            </div>
            {{-- Staff row --}}
            <div style="display:flex;align-items:center;justify-content:space-between;margin-top:12px;padding:8px 12px;background:#faf5ff;border-radius:10px;">
                <span style="font-size:11px;color:#6b21a8 !important;font-weight:600;display:flex;align-items:center;gap:6px;">
                    <i class="bi bi-briefcase-fill" style="color:#8b5cf6;"></i> Staff present
                </span>
                <span style="font-size:13px;font-weight:800;color:#7c3aed !important;">{{ $teachersPresentToday }}</span>
            </div>
            <a href="{{ route('attendance.list.show') }}" style="display:inline-flex;align-items:center;gap:5px;margin-top:12px;font-size:11px;font-weight:700;color:#2563eb !important;text-decoration:none;">
                View Attendance Report <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>

    {{-- Fee Collection --}}
    <div class="section-card" style="display:flex;flex-direction:column;">
        <div class="sc-header">
            <span class="sc-title">
                <span style="width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg,#0d9488,#14b8a6);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:13px;flex-shrink:0;">
                    <i class="bi bi-cash-stack"></i>
                </span>
                Fee Collection vs Outstanding
            </span>
        </div>
        <div style="padding:16px 18px;flex:1;display:flex;flex-direction:column;">
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:14px;">
                <div style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:1px solid #bbf7d0;border-radius:12px;padding:12px;text-align:center;">
                    <div style="font-size:10px;color:#15803d !important;font-weight:700;letter-spacing:.04em;text-transform:uppercase;">Collected</div>
                    <div style="font-size:14px;font-weight:900;color:#166534 !important;margin-top:4px;">Rs.{{ number_format($totalFeeCollected) }}</div>
                </div>
                <div style="background:linear-gradient(135deg,#fff1f2,#ffe4e6);border:1px solid #fecdd3;border-radius:12px;padding:12px;text-align:center;">
                    <div style="font-size:10px;color:#be123c !important;font-weight:700;letter-spacing:.04em;text-transform:uppercase;">Outstanding</div>
                    <div style="font-size:14px;font-weight:900;color:#9f1239 !important;margin-top:4px;">Rs.{{ number_format($totalOutstandingFees) }}</div>
                </div>
                <div style="background:linear-gradient(135deg,#eff6ff,#dbeafe);border:1px solid #bfdbfe;border-radius:12px;padding:12px;text-align:center;">
                    <div style="font-size:10px;color:#1d4ed8 !important;font-weight:700;letter-spacing:.04em;text-transform:uppercase;">Rate</div>
                    <div style="font-size:14px;font-weight:900;color:#1e3a8a !important;margin-top:4px;">{{ $collectionRate }}%</div>
                </div>
            </div>
            <div id="chart-fee-collection" style="flex:1;min-height:180px;"></div>
            <a href="{{ route('payments.index') }}" style="display:inline-flex;align-items:center;gap:5px;margin-top:12px;padding-top:12px;border-top:1px solid #f1f5f9;font-size:11px;font-weight:700;color:#2563eb !important;text-decoration:none;">
                View All Payments <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>

</div>

{{-- ── ROW 2: 7-Day Trend + Class Enrollment + Gender ── --}}
<div style="display:grid;grid-template-columns:1fr;gap:18px;" id="row2-grid">
<style>@media(min-width:1024px){#row2-grid{grid-template-columns:repeat(3,1fr)!important;}}</style>

    {{-- 7-Day Trend --}}
    <div class="section-card" style="display:flex;flex-direction:column;">
        <div class="sc-header">
            <span class="sc-title">
                <span style="width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg,#10b981,#34d399);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:13px;flex-shrink:0;">
                    <i class="bi bi-graph-up"></i>
                </span>
                7-Day Attendance Trend
            </span>
            <a href="{{ route('attendance.analytics') }}" class="sc-link">Analytics →</a>
        </div>
        <div style="padding:12px 16px 16px;flex:1;">
            <div id="chart-attendance-trend" style="min-height:200px;"></div>
        </div>
    </div>

    {{-- Class Enrollment --}}
    <div class="section-card" style="display:flex;flex-direction:column;">
        <div class="sc-header">
            <span class="sc-title">
                <span style="width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg,#6366f1,#818cf8);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:13px;flex-shrink:0;">
                    <i class="bi bi-bar-chart-steps"></i>
                </span>
                Class Enrollment
            </span>
            <a href="{{ url('classes') }}" class="sc-link">All Classes →</a>
        </div>
        <div style="padding:12px 16px 16px;flex:1;">
            @if($classEnrollment->count())
            <div id="chart-class-enrollment" style="min-height:200px;"></div>
            @else
            <div class="empty-state"><i class="bi bi-bar-chart"></i>No enrollment data yet.</div>
            @endif
        </div>
    </div>

    {{-- Student Distribution --}}
    <div class="section-card" style="display:flex;flex-direction:column;">
        <div class="sc-header">
            <span class="sc-title">
                <span style="width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg,#a855f7,#c084fc);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:13px;flex-shrink:0;">
                    <i class="bi bi-gender-ambiguous"></i>
                </span>
                Student Distribution
            </span>
        </div>
        <div style="padding:12px 16px 16px;flex:1;display:flex;flex-direction:column;">
            <div style="position:relative;display:flex;align-items:center;justify-content:center;">
                <div id="chart-gender" style="width:100%;height:160px;"></div>
                <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;pointer-events:none;">
                    <span style="font-size:24px;font-weight:900;color:#0f172a !important;line-height:1;">{{ number_format($studentCount) }}</span>
                    <span style="font-size:9px;color:#94a3b8 !important;font-weight:600;">Total</span>
                </div>
            </div>
            <div style="margin-top:12px;padding-top:12px;border-top:1px solid #f1f5f9;display:flex;flex-direction:column;gap:8px;">
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="width:10px;height:10px;border-radius:50%;background:#3b82f6;display:inline-block;"></span>
                        <span style="font-size:12px;color:#64748b !important;font-weight:500;">Male</span>
                    </div>
                    <span style="font-size:12px;font-weight:700;color:#1e293b !important;">
                        {{ number_format($maleStudents) }} <span style="color:#94a3b8 !important;font-weight:400;">({{ $malePct }}%)</span>
                    </span>
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="width:10px;height:10px;border-radius:50%;background:#ec4899;display:inline-block;"></span>
                        <span style="font-size:12px;color:#64748b !important;font-weight:500;">Female</span>
                    </div>
                    <span style="font-size:12px;font-weight:700;color:#1e293b !important;">
                        {{ number_format($female) }} <span style="color:#94a3b8 !important;font-weight:400;">({{ 100 - $malePct }}%)</span>
                    </span>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ── ROW 3: Avg Marks + Attendance Heatmap ── --}}
<div style="display:grid;grid-template-columns:1fr;gap:18px;" id="row3-grid">
<style>@media(min-width:1024px){#row3-grid{grid-template-columns:repeat(2,1fr)!important;}}</style>

    <div class="section-card" style="display:flex;flex-direction:column;">
        <div class="sc-header">
            <span class="sc-title">
                <span style="width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg,#7c3aed,#a78bfa);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:13px;flex-shrink:0;">
                    <i class="bi bi-award"></i>
                </span>
                Avg Marks by Course
            </span>
            <a href="{{ route('results.index') }}" class="sc-link">View Results →</a>
        </div>
        <div style="padding:12px 16px 16px;flex:1;">
            @if($coursePerformance->count())
            <div id="chart-course-performance" style="min-height:220px;"></div>
            @else
            <div class="empty-state"><i class="bi bi-award"></i>No marks recorded this session.</div>
            @endif
        </div>
    </div>

    <div class="section-card" style="display:flex;flex-direction:column;">
        <div class="sc-header">
            <span class="sc-title">
                <span style="width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg,#2563eb,#60a5fa);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:13px;flex-shrink:0;">
                    <i class="bi bi-calendar3"></i>
                </span>
                Attendance Heatmap
                <span style="font-size:10px;color:#94a3b8 !important;font-weight:400;">(10 weeks)</span>
            </span>
            <a href="{{ route('attendance.analytics') }}" class="sc-link">Analytics →</a>
        </div>
        <div style="padding:12px 16px 16px;flex:1;">
            <div id="chart-heatmap" style="min-height:220px;"></div>
        </div>
    </div>

</div>

{{-- ── ROW 4: Upcoming Exams + Events + Quick Actions ── --}}
<div style="display:grid;grid-template-columns:1fr;gap:18px;" id="row4-grid">
<style>@media(min-width:1024px){#row4-grid{grid-template-columns:repeat(3,1fr)!important;}}</style>

    {{-- Upcoming Exams --}}
    <div class="section-card" style="display:flex;flex-direction:column;">
        <div class="sc-header">
            <span class="sc-title">
                <span style="width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg,#ef4444,#f87171);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:13px;flex-shrink:0;">
                    <i class="bi bi-file-earmark-text"></i>
                </span>
                Upcoming Exams
            </span>
            @can('view exams')
            <a href="{{ route('exam.list.show') }}" class="sc-link">View all →</a>
            @endcan
        </div>
        <div style="padding:8px 10px 14px;flex:1;">
            @if($upcomingExams->count())
            @foreach($upcomingExams as $exam)
            <div class="row-item">
                <div style="width:32px;height:32px;border-radius:8px;background:#fff1f2;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-file-earmark-text" style="color:#ef4444;font-size:13px;"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:12px;font-weight:600;color:#1e293b !important;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $exam->exam_name }}</div>
                    <div style="font-size:10px;color:#94a3b8 !important;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $exam->course?->course_name ?? '—' }}</div>
                </div>
                <span style="font-size:10px;font-weight:700;background:#eef2ff;color:#4f46e5 !important;padding:3px 8px;border-radius:6px;flex-shrink:0;">
                    {{ \Carbon\Carbon::parse($exam->start_date)->format('M d') }}
                </span>
            </div>
            @endforeach
            @else
            <div class="empty-state"><i class="bi bi-file-earmark-text"></i>No upcoming exams.</div>
            @endif
        </div>
    </div>

    {{-- Upcoming Events --}}
    <div class="section-card" style="display:flex;flex-direction:column;">
        <div class="sc-header">
            <span class="sc-title">
                <span style="width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg,#6366f1,#818cf8);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:13px;flex-shrink:0;">
                    <i class="bi bi-calendar3"></i>
                </span>
                Upcoming Events
            </span>
            @can('view events')
            <a href="{{ route('events.show') }}" class="sc-link">Calendar →</a>
            @endcan
        </div>
        <div style="padding:8px 10px 14px;flex:1;">
            @if($upcomingEvents->count())
            @foreach($upcomingEvents as $evt)
            <div class="row-item">
                <div style="width:32px;height:32px;border-radius:8px;background:#eef2ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-calendar-event" style="color:#6366f1;font-size:13px;"></i>
                </div>
                <div style="flex:1;min-width:0;font-size:12px;font-weight:600;color:#1e293b !important;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $evt->title }}</div>
                <span style="font-size:10px;font-weight:700;background:#fefce8;color:#b45309 !important;padding:3px 8px;border-radius:6px;flex-shrink:0;">
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
    <div class="section-card" style="display:flex;flex-direction:column;">
        <div class="sc-header">
            <span class="sc-title">
                <span style="width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg,#f59e0b,#fbbf24);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:13px;flex-shrink:0;">
                    <i class="bi bi-lightning-fill"></i>
                </span>
                Quick Actions
            </span>
        </div>
        <div style="padding:8px 10px 14px;flex:1;">
            <a href="{{ route('school.profile.edit') }}" class="row-item" style="text-decoration:none;display:flex;">
                <div style="width:32px;height:32px;border-radius:8px;background:#eff6ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="bi bi-building" style="color:#2563eb;font-size:14px;"></i></div>
                <span style="font-size:12px;font-weight:600;color:#334155 !important;flex:1;">School Profile</span>
                <i class="bi bi-chevron-right" style="color:#cbd5e1;font-size:11px;"></i>
            </a>
            @can('manage roles')
            <a href="{{ route('roles.index') }}" class="row-item" style="text-decoration:none;display:flex;">
                <div style="width:32px;height:32px;border-radius:8px;background:#eef2ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="bi bi-shield-check" style="color:#6366f1;font-size:14px;"></i></div>
                <span style="font-size:12px;font-weight:600;color:#334155 !important;flex:1;">Manage Roles</span>
                <i class="bi bi-chevron-right" style="color:#cbd5e1;font-size:11px;"></i>
            </a>
            @endcan
            @can('view academic settings')
            <a href="{{ url('academics/settings') }}" class="row-item" style="text-decoration:none;display:flex;">
                <div style="width:32px;height:32px;border-radius:8px;background:#fffbeb;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="bi bi-tools" style="color:#f59e0b;font-size:14px;"></i></div>
                <span style="font-size:12px;font-weight:600;color:#334155 !important;flex:1;">Academic Settings</span>
                <i class="bi bi-chevron-right" style="color:#cbd5e1;font-size:11px;"></i>
            </a>
            @endcan
            @can('create teachers')
            <a href="{{ route('teacher.create.show') }}" class="row-item" style="text-decoration:none;display:flex;">
                <div style="width:32px;height:32px;border-radius:8px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="bi bi-person-plus" style="color:#16a34a;font-size:14px;"></i></div>
                <span style="font-size:12px;font-weight:600;color:#334155 !important;flex:1;">Add Teacher</span>
                <i class="bi bi-chevron-right" style="color:#cbd5e1;font-size:11px;"></i>
            </a>
            @endcan
            @can('create invoices')
            <a href="{{ route('payments.create') }}" class="row-item" style="text-decoration:none;display:flex;">
                <div style="width:32px;height:32px;border-radius:8px;background:#f0fdfa;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="bi bi-receipt" style="color:#0d9488;font-size:14px;"></i></div>
                <span style="font-size:12px;font-weight:600;color:#334155 !important;flex:1;">New Invoice</span>
                <i class="bi bi-chevron-right" style="color:#cbd5e1;font-size:11px;"></i>
            </a>
            @endcan
            @can('promote students')
            <a href="{{ url('promotions/index') }}" class="row-item" style="text-decoration:none;display:flex;">
                <div style="width:32px;height:32px;border-radius:8px;background:#faf5ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="bi bi-arrow-up-circle" style="color:#7c3aed;font-size:14px;"></i></div>
                <span style="font-size:12px;font-weight:600;color:#334155 !important;flex:1;">Promotions</span>
                <i class="bi bi-chevron-right" style="color:#cbd5e1;font-size:11px;"></i>
            </a>
            @endcan
        </div>
    </div>

</div>

{{-- ── ROW 5: Top Performers + Recent Admissions + Recent Payments ── --}}
<div style="display:grid;grid-template-columns:1fr;gap:18px;" id="row5-grid">
<style>@media(min-width:1024px){#row5-grid{grid-template-columns:repeat(3,1fr)!important;}}</style>

    {{-- Top Performers --}}
    <div class="section-card" style="display:flex;flex-direction:column;">
        <div class="sc-header">
            <span class="sc-title">
                <span style="width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg,#f59e0b,#fbbf24);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:13px;flex-shrink:0;">
                    <i class="bi bi-trophy-fill"></i>
                </span>
                Top Performers
            </span>
        </div>
        <div style="padding:8px 10px 14px;flex:1;">
            @if($topPerformers->count())
            @foreach($topPerformers->take(6) as $i => $tp)
            @if($tp->student)
            <div class="row-item">
                <span style="width:20px;height:20px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;flex-shrink:0;
                    {{ $i===0?'background:#fef3c7;color:#92400e !important;':($i===1?'background:#f1f5f9;color:#475569 !important;':($i===2?'background:#fff7ed;color:#9a3412 !important;':'background:#f8fafc;color:#94a3b8 !important;')) }}">
                    {{ $i+1 }}
                </span>
                <img src="{{ $tp->student->avatar }}" style="width:28px;height:28px;border-radius:50%;object-fit:cover;flex-shrink:0;" alt="">
                <div style="flex:1;min-width:0;">
                    <div style="font-size:12px;font-weight:600;color:#1e293b !important;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $tp->student->full_name }}</div>
                    <div style="font-size:10px;color:#94a3b8 !important;">{{ $tp->exams_count }} exams</div>
                </div>
                <span style="font-size:12px;font-weight:800;flex-shrink:0;color:{{ $tp->avg_marks>=80?'#16a34a':($tp->avg_marks>=60?'#d97706':'#dc2626') }} !important;">
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
    <div class="section-card" style="display:flex;flex-direction:column;">
        <div class="sc-header">
            <span class="sc-title">
                <span style="width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg,#10b981,#34d399);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:13px;flex-shrink:0;">
                    <i class="bi bi-person-check-fill"></i>
                </span>
                Recent Admissions
            </span>
            @can('view students')
            <a href="{{ route('student.list.show') }}" class="sc-link">View all →</a>
            @endcan
        </div>
        <div style="padding:8px 10px 14px;flex:1;">
            @if($recentAdmissions->count())
            @foreach($recentAdmissions->take(5) as $s)
            <div class="row-item">
                <img src="{{ $s->avatar }}" style="width:32px;height:32px;border-radius:50%;object-fit:cover;flex-shrink:0;" alt="">
                <div style="flex:1;min-width:0;">
                    <div style="font-size:12px;font-weight:600;color:#1e293b !important;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $s->full_name }}</div>
                    <div style="font-size:10px;color:#94a3b8 !important;">{{ $s->created_at->diffForHumans() }}</div>
                </div>
                @can('view students')
                <a href="{{ route('student.profile.show', $s->id) }}" style="font-size:10px;font-weight:700;color:#2563eb !important;text-decoration:none;flex-shrink:0;background:#eff6ff;padding:3px 8px;border-radius:6px;">Profile</a>
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
    <div class="section-card" style="display:flex;flex-direction:column;">
        <div class="sc-header">
            <span class="sc-title">
                <span style="width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg,#6366f1,#818cf8);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:13px;flex-shrink:0;">
                    <i class="bi bi-credit-card-fill"></i>
                </span>
                Recent Payments
            </span>
            <a href="{{ route('payments.index') }}" class="sc-link">View all →</a>
        </div>
        <div style="padding:8px 10px 14px;flex:1;">
            @if($recentPayments->count())
            @foreach($recentPayments->take(5) as $pmt)
            @php $pSt = $pmt->invoice?->student; @endphp
            <div class="row-item" style="justify-content:space-between;">
                <div style="display:flex;align-items:center;gap:8px;min-width:0;">
                    @if($pSt)
                    <img src="{{ $pSt->avatar }}" style="width:28px;height:28px;border-radius:50%;object-fit:cover;flex-shrink:0;" alt="">
                    @else
                    <div style="width:28px;height:28px;border-radius:50%;background:#f1f5f9;flex-shrink:0;"></div>
                    @endif
                    <div style="min-width:0;">
                        <div style="font-size:12px;font-weight:600;color:#1e293b !important;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $pSt?->full_name ?? '—' }}</div>
                        <div style="font-size:10px;color:#94a3b8 !important;">{{ $pmt->created_at->format('M d, Y') }}</div>
                    </div>
                </div>
                <span style="font-size:12px;font-weight:800;color:#16a34a !important;flex-shrink:0;background:#f0fdf4;padding:3px 8px;border-radius:6px;">
                    Rs.{{ number_format($pmt->amount_paid) }}
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
<div style="display:grid;grid-template-columns:1fr;gap:18px;" id="row6-grid">
<style>@media(min-width:1024px){#row6-grid{grid-template-columns:repeat(3,1fr)!important;}}</style>

    {{-- Today's Schedule --}}
    <div class="section-card" style="display:flex;flex-direction:column;">
        <div class="sc-header">
            <span class="sc-title">
                <span style="width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg,#6366f1,#818cf8);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:13px;flex-shrink:0;">
                    <i class="bi bi-calendar4-range"></i>
                </span>
                Today's Schedule
                <span style="font-size:10px;color:#94a3b8 !important;font-weight:400;">{{ now()->format('l') }}</span>
            </span>
            @can('view routines')
            <a href="{{ route('section.routine.show') }}" class="sc-link">Timetable →</a>
            @endcan
        </div>
        @if($todaySchedule->count())
        <div style="flex:1;">
            @foreach($todaySchedule->take(6) as $slot)
            @php
                $slotStart = \Carbon\Carbon::today()->setTimeFromTimeString($slot->start);
                $slotEnd   = \Carbon\Carbon::today()->setTimeFromTimeString($slot->end);
                $isNow     = now()->between($slotStart, $slotEnd);
            @endphp
            <div style="display:flex;align-items:center;gap:10px;padding:9px 16px;
                {{ $isNow ? 'background:#eef2ff;' : '' }}
                border-bottom:1px solid #f8fafc;transition:background .15s;">
                <div style="width:6px;height:6px;border-radius:50%;flex-shrink:0;
                    {{ $isNow ? 'background:#6366f1;' : 'background:#cbd5e1;' }}
                    {{ $isNow ? 'box-shadow:0 0 0 3px rgba(99,102,241,.25);' : '' }}">
                </div>
                <span style="font-size:10px;font-family:monospace;color:#64748b !important;width:80px;flex-shrink:0;">{{ $slot->start }}–{{ $slot->end }}</span>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:12px;font-weight:600;color:#1e293b !important;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $slot->course?->course_name ?? '—' }}</div>
                    <div style="font-size:10px;color:#94a3b8 !important;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        {{ $slot->schoolClass?->class_name ?? '' }}{{ $slot->section?->section_name ? ' · '.$slot->section->section_name : '' }}
                    </div>
                </div>
                @if($isNow)
                <span style="font-size:9px;background:#6366f1;color:#fff !important;padding:2px 7px;border-radius:20px;flex-shrink:0;font-weight:700;">Live</span>
                @endif
            </div>
            @endforeach
        </div>
        @else
        <div class="empty-state"><i class="bi bi-calendar4-range"></i>No classes scheduled today.</div>
        @endif
    </div>

    {{-- Upcoming Birthdays --}}
    <div class="section-card" style="display:flex;flex-direction:column;">
        <div class="sc-header">
            <span class="sc-title">
                <span style="width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg,#ec4899,#f472b6);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:13px;flex-shrink:0;">
                    <i class="bi bi-cake2-fill"></i>
                </span>
                Upcoming Birthdays
            </span>
        </div>
        @if($birthdayUsers->count())
        <div style="flex:1;">
            @foreach($birthdayUsers->take(6) as $u)
            <div style="display:flex;align-items:center;gap:10px;padding:9px 16px;border-bottom:1px solid #f8fafc;transition:background .15s;" onmouseover="this.style.background='#fdf2f8'" onmouseout="this.style.background=''">
                <img src="{{ $u->avatar }}" style="width:32px;height:32px;border-radius:50%;object-fit:cover;flex-shrink:0;border:2px solid #fbcfe8;" alt="">
                <div style="flex:1;min-width:0;">
                    <div style="font-size:12px;font-weight:600;color:#1e293b !important;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $u->full_name }}</div>
                    <div style="font-size:10px;color:#94a3b8 !important;text-transform:capitalize;">{{ $u->primary_role }}</div>
                </div>
                <span style="font-size:10px;font-weight:700;background:#fdf2f8;color:#be185d !important;border:1px solid #fbcfe8;padding:3px 8px;border-radius:6px;flex-shrink:0;">
                    {{ \Carbon\Carbon::parse($u->birthday)->format('M d') }}
                </span>
            </div>
            @endforeach
        </div>
        @else
        <div class="empty-state"><i class="bi bi-cake2"></i>No birthdays in next 7 days.</div>
        @endif
    </div>

    {{-- Notice Board --}}
    <div class="section-card" style="display:flex;flex-direction:column;">
        <div class="sc-header">
            <span class="sc-title">
                <span style="width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg,#f59e0b,#fbbf24);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:13px;flex-shrink:0;">
                    <i class="bi bi-megaphone-fill"></i>
                </span>
                Notice Board
            </span>
            @can('create notices')
            <a href="{{ route('notice.create') }}" class="sc-link" style="background:#fffbeb;padding:3px 10px;border-radius:6px;border:1px solid #fcd34d;">+ New</a>
            @endcan
        </div>
        @if($notices->count())
        <div style="flex:1;">
            @foreach($notices->take(5) as $notice)
            <div style="padding:10px 16px;border-bottom:1px solid #f8fafc;transition:background .15s;" onmouseover="this.style.background='#fffbeb'" onmouseout="this.style.background=''">
                <div style="display:flex;gap:8px;align-items:flex-start;">
                    <span style="width:6px;height:6px;border-radius:50%;background:#f59e0b;flex-shrink:0;margin-top:5px;"></span>
                    <p style="font-size:12px;color:#475569 !important;line-height:1.5;margin:0;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
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
<div class="section-card">
    <div class="sc-header">
        <span class="sc-title">
            <span style="width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg,#64748b,#94a3b8);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:13px;flex-shrink:0;">
                <i class="bi bi-clock-history"></i>
            </span>
            Recent Activity
        </span>
    </div>
    <div>
        @foreach($activityLog->take(6) as $log)
        <div style="display:flex;align-items:center;gap:12px;padding:10px 16px;border-bottom:1px solid #f8fafc;transition:background .15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
            <div style="width:30px;height:30px;border-radius:50%;overflow:hidden;flex-shrink:0;background:#f1f5f9;display:flex;align-items:center;justify-content:center;">
                @if($log->user)
                <img src="{{ $log->user->avatar }}" style="width:30px;height:30px;object-fit:cover;" alt="">
                @else
                <i class="bi bi-person" style="color:#94a3b8;font-size:13px;"></i>
                @endif
            </div>
            <div style="flex:1;min-width:0;">
                <p style="font-size:12px;color:#475569 !important;margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                    <span style="font-weight:700;color:#1e293b !important;">{{ $log->user?->full_name ?? 'System' }}</span>
                    — {{ $log->description ?? $log->action ?? 'performed an action' }}
                </p>
            </div>
            <span style="font-size:10px;color:#94a3b8 !important;flex-shrink:0;white-space:nowrap;">{{ $log->created_at->diffForHumans() }}</span>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ── FOOTER ── --}}
<div style="padding-top:16px;border-top:1px solid #e2e8f0;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:8px;">
    <p style="font-size:11px;color:#94a3b8 !important;margin:0;">&copy; {{ date('Y') }} NepalSchoolHub. All rights reserved.</p>
    <a href="{{ url('academics/settings') }}" style="font-size:11px;color:#94a3b8 !important;text-decoration:none;" onmouseover="this.style.color='#475569'" onmouseout="this.style.color='#94a3b8'">Settings</a>
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

    // ── Attendance Donut ──────────────────────────────────────────────────
    var donutEl = document.getElementById('chart-attendance-donut');
    if (donutEl) {
        donutEl.style.height = '180px';
        new ApexCharts(donutEl, {
            chart: { type: 'donut', height: 180, parentHeightOffset: 0 },
            series: [{{ $attendanceDonutData['present'] > 0 || $attendanceDonutData['absent'] > 0 ? $attendanceDonutData['present'] : 1 }}, {{ $attendanceDonutData['absent'] > 0 || $attendanceDonutData['present'] > 0 ? $attendanceDonutData['absent'] : 0 }}],
            labels: ['Present', 'Absent'],
            colors: ['#2563eb', '#f43f5e'],
            dataLabels: { enabled: false },
            legend: { show: false },
            plotOptions: { pie: { donut: { size: '75%', labels: { show: false } } } },
            stroke: { width: 3, colors: ['#fff'] },
            tooltip: { theme: isDark ? 'dark' : 'light' },
        }).render();
    }

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
            colors: ['#10b981', '#f43f5e'],
            fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.0 } },
            stroke: { curve: 'smooth', width: 2.5 },
            dataLabels: { enabled: false },
            legend: { position: 'top', fontSize: '11px', labels: { colors: textMuted } },
            grid: { borderColor: gridColor, strokeDashArray: 4 },
            tooltip: { theme: isDark ? 'dark' : 'light' },
        }).render();
    }

    // ── Class Enrollment (horizontal bar) ────────────────────────────────
    var classEl = document.getElementById('chart-class-enrollment');
    if (classEl) {
        var classData   = @json($classEnrollment);
        var classNames  = classData.map(function(c){ return c.name; });
        var classCounts = classData.map(function(c){ return c.count; });
        new ApexCharts(classEl, {
            chart: { type: 'bar', height: 200, toolbar: { show: false }, fontFamily: 'Inter,sans-serif' },
            plotOptions: { bar: { horizontal: true, borderRadius: 5, barHeight: '55%',
                distributed: true } },
            series: [{ name: 'Students', data: classCounts }],
            xaxis: { categories: classNames, labels: { style: { colors: textMuted, fontSize: '10px' } } },
            yaxis: { labels: { style: { colors: textMuted, fontSize: '10px' } } },
            colors: ['#6366f1','#8b5cf6','#a855f7','#7c3aed','#4f46e5','#3b82f6','#2563eb','#0d9488','#10b981','#f59e0b'],
            dataLabels: { enabled: true, style: { fontSize: '10px', colors: ['#fff'] } },
            legend: { show: false },
            grid: { borderColor: gridColor, strokeDashArray: 4 },
            tooltip: { theme: isDark ? 'dark' : 'light', y: { formatter: function(v){ return v+' students'; } } },
        }).render();
    }

    // ── Gender Donut ──────────────────────────────────────────────────────
    var genderEl = document.getElementById('chart-gender');
    if (genderEl) {
        new ApexCharts(genderEl, {
            chart: { type: 'donut', height: 160 },
            series: [{{ $maleStudents }}, {{ $female }}],
            labels: ['Male', 'Female'],
            colors: ['#3b82f6', '#ec4899'],
            dataLabels: { enabled: false },
            legend: { show: false },
            plotOptions: { pie: { donut: { size: '72%', labels: { show: false } } } },
            stroke: { width: 3, colors: ['#fff'] },
            tooltip: { theme: isDark ? 'dark' : 'light' },
        }).render();
    }

    // ── Avg Marks by Course ───────────────────────────────────────────────
    var courseEl = document.getElementById('chart-course-performance');
    if (courseEl) {
        @php
            $courseNamesFormatted = $coursePerformance->map(fn($c) => $c->course?->course_name ?? 'Unknown');
            $courseAvgsFormatted  = $coursePerformance->pluck('avg_marks')->map(fn($v) => (float)$v);
        @endphp
        var courseNames = @json($courseNamesFormatted);
        var courseAvgs  = @json($courseAvgsFormatted);
        new ApexCharts(courseEl, {
            chart: { type: 'bar', height: 220, toolbar: { show: false }, fontFamily: 'Inter,sans-serif' },
            plotOptions: { bar: { horizontal: true, borderRadius: 5, barHeight: '55%' } },
            series: [{ name: 'Avg Marks (%)', data: courseAvgs }],
            xaxis: { categories: courseNames, labels: { style: { colors: textMuted, fontSize: '10px' } }, max: 100 },
            yaxis: { labels: { style: { colors: textMuted, fontSize: '10px' } } },
            colors: ['#7c3aed'],
            dataLabels: { enabled: true, formatter: function(v){ return v+'%'; }, style: { fontSize: '10px' } },
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
                        { from: 0,  to: 0,   color: '#f1f5f9', name: 'No data' },
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
