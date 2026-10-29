<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Official Academic Marksheet — {{ $result['student']?->full_name }}</title>
<style>
@page {
    size: A4 portrait;
    margin: 0;
}
* { margin: 0; padding: 0; box-sizing: border-box; }
html, body {
    width: 210mm;
    height: 297mm;
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size: 13px;
    color: #1e293b;
    background: #ffffff;
}

/* ── OUTER SHELL ─────────────────────────────────────────────── */
.page {
    width: 210mm;
    min-height: 297mm;
    padding: 0;
    display: block;
    position: relative;
    background: #ffffff;
}

/* ── TOP COLOR BAR ───────────────────────────────────────────── */
.top-bar {
    width: 100%;
    height: 8px;
    background: #1e3a8a;
}

/* ── HEADER ──────────────────────────────────────────────────── */
.header-wrap {
    padding: 18px 22px 14px 22px;
    border-bottom: 2.5px solid #1e3a8a;
    background: #ffffff;
}
.header-table {
    width: 100%;
    border-collapse: collapse;
}
.school-name {
    font-size: 22px;
    font-weight: bold;
    color: #1e3a8a;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-bottom: 3px;
    line-height: 1.2;
}
.school-sub {
    font-size: 11px;
    color: #475569;
    line-height: 1.6;
}

/* ── TITLE BADGE ─────────────────────────────────────────────── */
.title-wrap {
    background: #1e3a8a;
    padding: 9px 22px;
    text-align: center;
}
.title-wrap span {
    color: #ffffff;
    font-size: 13px;
    font-weight: bold;
    letter-spacing: 3px;
    text-transform: uppercase;
}

/* ── STUDENT INFO SECTION ────────────────────────────────────── */
.info-wrap {
    padding: 14px 22px 10px 22px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}
.info-table {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
}
.info-table td {
    padding: 7px 12px;
    font-size: 12px;
    vertical-align: middle;
    border-bottom: 1px solid #e2e8f0;
}
.info-table tr:last-child td { border-bottom: none; }
.info-lbl {
    color: #64748b;
    font-weight: 600;
    width: 18%;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    background: #f1f5f9;
    border-right: 1px solid #e2e8f0;
}
.info-val {
    color: #0f172a;
    font-weight: bold;
    width: 32%;
    font-size: 13px;
}

/* ── SECTION LABEL ───────────────────────────────────────────── */
.section-label {
    padding: 8px 22px 5px 22px;
    font-size: 10px;
    font-weight: bold;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 1px;
    background: #ffffff;
}

/* ── MARKS TABLE ─────────────────────────────────────────────── */
.marks-wrap { padding: 0 22px; }
table.marks-table {
    width: 100%;
    border-collapse: collapse;
    border: 1.5px solid #1e3a8a;
    margin-bottom: 0;
}
table.marks-table thead tr {
    background: #1e3a8a;
}
table.marks-table th {
    color: #ffffff;
    padding: 9px 8px;
    font-size: 11px;
    font-weight: bold;
    text-align: center;
    border-right: 1px solid #2d4fa0;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
table.marks-table th:first-child { text-align: left; padding-left: 12px; }
table.marks-table th:last-child  { border-right: none; }
table.marks-table td {
    padding: 8px 8px;
    border-bottom: 1px solid #e2e8f0;
    border-right: 1px solid #e2e8f0;
    font-size: 12px;
    text-align: center;
    vertical-align: middle;
}
table.marks-table td:first-child  { text-align: left; padding-left: 12px; font-weight: 600; }
table.marks-table td:last-child   { border-right: none; }
table.marks-table tbody tr:nth-child(even) { background: #f8fafc; }
table.marks-table tbody tr:last-child td   { border-bottom: none; }

/* ── SUMMARY BOX ─────────────────────────────────────────────── */
.summary-wrap {
    padding: 14px 22px 0 22px;
}
.summary-box {
    width: 100%;
    border-collapse: collapse;
    background: #eff6ff;
    border: 1.5px solid #3b82f6;
    border-radius: 6px;
}
.summary-box td {
    width: 25%;
    text-align: center;
    padding: 14px 8px;
    border-right: 1px solid #bfdbfe;
    vertical-align: middle;
}
.summary-box td:last-child { border-right: none; }
.sum-val { font-size: 26px; font-weight: bold; color: #1e3a8a; line-height: 1; }
.sum-lbl { font-size: 9px; color: #64748b; text-transform: uppercase; letter-spacing: 0.8px; margin-top: 4px; }

/* ── GRADING KEY ─────────────────────────────────────────────── */
.grading-wrap {
    padding: 14px 22px 0 22px;
}
.grading-title {
    font-size: 9px;
    font-weight: bold;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-bottom: 5px;
    padding: 5px 8px;
    background: #f1f5f9;
    border-left: 3px solid #1e3a8a;
}
.grading-key {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #cbd5e1;
}
.grading-key th {
    background: #1e3a8a;
    color: #ffffff;
    font-size: 9.5px;
    font-weight: bold;
    border: 1px solid #2d4fa0;
    padding: 6px 4px;
    text-align: center;
    width: 11.11%;
}
.grading-key .row-label {
    background: #f1f5f9;
    color: #334155;
    font-size: 9.5px;
    font-weight: bold;
    border: 1px solid #cbd5e1;
    padding: 6px 4px;
    text-align: center;
}
.grading-key td {
    border: 1px solid #e2e8f0;
    font-size: 10px;
    padding: 6px 4px;
    text-align: center;
}
.grading-key tr:nth-child(2) td { background: #f0fdf4; font-weight: bold; color: #166534; }
.grading-key tr:nth-child(3) td { background: #eff6ff; font-weight: bold; color: #1e40af; }
.grading-key tr:nth-child(4) td { background: #fafafa; color: #475569; font-size: 9px; }

/* ── SIGNATURES ──────────────────────────────────────────────── */
.sig-wrap {
    padding: 22px 22px 14px 22px;
}
.sig-table {
    width: 100%;
    border-collapse: collapse;
}
.sig-table td {
    width: 33.33%;
    text-align: center;
    vertical-align: bottom;
    padding: 0 12px;
}
.sig-space  { height: 55px; }
.sig-line   { border-top: 1.5px solid #334155; width: 80%; margin: 0 auto 5px; }
.sig-role   { font-size: 11px; font-weight: bold; color: #1e293b; }
.sig-name   { font-size: 9px; color: #64748b; margin-top: 2px; }

/* ── WATERMARK BAND ──────────────────────────────────────────── */
.watermark-band {
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    border-bottom: 1px solid #e2e8f0;
    margin: 14px 22px;
    padding: 8px 14px;
    text-align: center;
    font-size: 9px;
    color: #94a3b8;
    line-height: 1.8;
}

/* ── BOTTOM BAR ──────────────────────────────────────────────── */
.bottom-bar {
    width: 100%;
    background: #1e3a8a;
    padding: 7px 22px;
    display: table;
}
.bottom-bar-left {
    display: table-cell;
    vertical-align: middle;
    font-size: 9px;
    color: rgba(255,255,255,0.7);
}
.bottom-bar-right {
    display: table-cell;
    vertical-align: middle;
    text-align: right;
    font-size: 9px;
    color: rgba(255,255,255,0.7);
    font-family: monospace;
}

/* ── PASSED / FAILED BADGES ──────────────────────────────────── */
.badge-pass   { color: #059669; font-weight: bold; font-size: 11px; }
.badge-fail   { color: #dc2626; font-weight: bold; font-size: 11px; }
.grade-color  { font-weight: bold; font-size: 13px; }
</style>
</head>
<body>
@php
    $student  = $result['student'] ?? null;
    if (!isset($school) || !$school) {
        $school = $student ? \App\Models\School::find($student->school_id) : null;
        $school = $school ?? \App\Models\School::first();
    }
    $semester = $result['semester'] ?? null;
    $courses  = $result['courses'] ?? [];
    $totalFM  = collect($courses)->sum(fn($c) => (float)($c['full_marks'] ?? 100));
@endphp

<div class="page">

    {{-- ══ TOP ACCENT BAR ══ --}}
    <div class="top-bar"></div>

    {{-- ══ SCHOOL HEADER ══ --}}
    <div class="header-wrap">
        <table class="header-table">
            <tr>
                <td style="width:13%; vertical-align:middle; text-align:left;">
                    @if(!empty($logoSrc))
                        <img src="{{ $logoSrc }}" style="max-height:72px; max-width:95px;" alt="Logo">
                    @else
                        <div style="width:72px; height:72px; border-radius:50%; background:#1e3a8a; display:flex; align-items:center; justify-content:center;">
                            <span style="color:#fff; font-size:22px; font-weight:bold;">{{ strtoupper(substr($school?->name ?? 'S', 0, 1)) }}</span>
                        </div>
                    @endif
                </td>
                <td style="width:67%; text-align:center; vertical-align:middle; padding:0 10px;">
                    <div class="school-name">{{ $school?->name ?? config('app.name') }}</div>
                    <div class="school-sub">
                        {{ $school?->address ?? 'Kathmandu, Bagmati Province, Nepal' }}
                        &nbsp;&bull;&nbsp; Tel: {{ $school?->phone ?? '+977-1-4412345' }}
                    </div>
                    @if($school?->iemis_code ?? null)
                    <div class="school-sub" style="font-style:italic; font-size:10px; color:#94a3b8;">
                        Ministry of Education — EMIS Code: {{ $school->iemis_code }}
                    </div>
                    @endif
                </td>
                <td style="width:20%; text-align:right; vertical-align:middle;">
                    @if(isset($qrSrc))
                    <div style="display:inline-block; text-align:center;">
                        <img src="{{ $qrSrc }}" style="width:70px; height:70px; border:2px solid #1e3a8a; padding:3px; background:#fff;" alt="QR">
                        <div style="font-size:7px; color:#1e3a8a; font-weight:bold; margin-top:3px; letter-spacing:1px;">SCAN TO VERIFY</div>
                    </div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    {{-- ══ TITLE BANNER ══ --}}
    <div class="title-wrap">
        <span>&#9670;&nbsp; Academic Achievement Marksheet / Grade Sheet &nbsp;&#9670;</span>
    </div>

    {{-- ══ STUDENT INFO ══ --}}
    <div class="info-wrap">
        <table class="info-table">
            <tr>
                <td class="info-lbl">Student Name</td>
                <td class="info-val">{{ $student?->full_name ?? '—' }}</td>
                <td class="info-lbl">Academic Term</td>
                <td class="info-val">{{ $semester?->semester_name ?? '—' }}</td>
            </tr>
            <tr>
                <td class="info-lbl">Roll Number</td>
                <td class="info-val">{{ $result['promotion']?->roll_no ?? $student?->academic_info?->roll_number ?? '—' }}</td>
                <td class="info-lbl">Class &amp; Section</td>
                <td class="info-val">{{ $result['class']?->class_name ?? '—' }} &nbsp;&bull;&nbsp; Sec. {{ $result['section']?->section_name ?? '—' }}</td>
            </tr>
            <tr>
                <td class="info-lbl">Class Rank</td>
                <td class="info-val">
                    @if($result['rank'])
                        @php $rank = (int)$result['rank']; @endphp
                        <span style="font-size:18px; color:#1e3a8a;">{{ ordinal($rank) }}</span>
                        <span style="font-size:10px; color:#64748b; font-weight:normal;"> Position</span>
                    @else &mdash; @endif
                </td>
                <td class="info-lbl">Issue Date</td>
                <td class="info-val">{{ now()->format('d M Y') }} <span style="font-size:10px; color:#64748b;">(@bsDateEn(now()->toDateString()))</span></td>
            </tr>
        </table>
    </div>

    {{-- ══ MARKS TABLE ══ --}}
    <div class="section-label">&#9658; Subject-wise Performance</div>
    <div class="marks-wrap">
        <table class="marks-table">
            <thead>
                <tr>
                    <th style="width:32%;">Subject / Course Name</th>
                    <th style="width:12%;">Full Marks</th>
                    <th style="width:12%;">Obtained</th>
                    <th style="width:9%;">%</th>
                    <th style="width:10%;">Grade</th>
                    <th style="width:13%;">Grade Point</th>
                    <th style="width:12%;">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse($courses as $c)
                @php
                    $fm  = (float)($c['full_marks'] ?? 100);
                    $ob  = (float)$c['final_marks'];
                    $pct = $fm > 0 ? round(($ob / $fm) * 100, 1) : 0;
                @endphp
                <tr>
                    <td>{{ $c['course']?->course_name ?? '—' }}</td>
                    <td>{{ number_format($fm, 0) }}</td>
                    <td style="font-weight:bold;">{{ round($ob, 1) }}</td>
                    <td style="color:#475569;">{{ $pct }}%</td>
                    <td class="grade-color" style="color:{{ $c['passed'] ? '#059669' : '#dc2626' }};">{{ $c['grade'] }}</td>
                    <td style="font-weight:bold; color:#1e3a8a;">{{ number_format((float)$c['point'], 2) }}</td>
                    <td>
                        @if($c['passed'])
                            <span class="badge-pass">&#10003; PASSED</span>
                        @else
                            <span class="badge-fail">&#10007; RETEST</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center; padding:16px; color:#94a3b8;">No marks recorded for this term.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ══ SUMMARY BOX ══ --}}
    <div class="summary-wrap">
        <table class="summary-box">
            <tr>
                <td>
                    <div class="sum-val">{{ $result['gpa'] }}</div>
                    <div class="sum-lbl">Grade Point Average</div>
                </td>
                <td>
                    <div class="sum-val">{{ round($result['totalMarks'], 0) }}</div>
                    <div class="sum-lbl">Total Marks Obtained</div>
                </td>
                <td>
                    <div class="sum-val">{{ $totalFM > 0 ? round(($result['totalMarks'] / $totalFM) * 100, 1) : '—' }}%</div>
                    <div class="sum-lbl">Overall Percentage</div>
                </td>
                <td>
                    <div class="sum-val" style="color:{{ ($result['failed'] ?? 0) > 0 ? '#dc2626' : '#059669' }};">
                        {{ ($result['failed'] ?? 0) > 0 ? 'FAIL' : 'PASS' }}
                    </div>
                    <div class="sum-lbl">Final Result &nbsp;|&nbsp; {{ $result['passed'] }} Passed / {{ $result['failed'] ?? 0 }} Failed</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ══ GRADING KEY ══ --}}
    <div class="grading-wrap">
        <div class="grading-title">&#9632; Government of Nepal — CDC 8-Point Grading System</div>
        <table class="grading-key">
            <thead>
                <tr>
                    <th style="background:#334155;">Interval</th>
                    <th>90–100%</th>
                    <th>80–89%</th>
                    <th>70–79%</th>
                    <th>60–69%</th>
                    <th>50–59%</th>
                    <th>40–49%</th>
                    <th>35–39%</th>
                    <th>Below 35%</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="row-label">Grade</td>
                    <td>A+</td><td>A</td><td>B+</td><td>B</td>
                    <td>C+</td><td>C</td><td>D</td><td>NG</td>
                </tr>
                <tr>
                    <td class="row-label">Grade Point</td>
                    <td>4.0</td><td>3.6</td><td>3.2</td><td>2.8</td>
                    <td>2.4</td><td>2.0</td><td>1.6</td><td>0.0</td>
                </tr>
                <tr>
                    <td class="row-label">Description</td>
                    <td>Outstanding</td><td>Excellent</td><td>Very Good</td><td>Good</td>
                    <td>Satisfactory</td><td>Acceptable</td><td>Basic</td><td>Non-Graded</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- ══ SIGNATURES ══ --}}
    <div class="sig-wrap">
        <table class="sig-table">
            <tr>
                <td>
                    <div class="sig-space"></div>
                    <div class="sig-line"></div>
                    <div class="sig-role">Class Teacher</div>
                    <div class="sig-name">Signature &amp; Stamp</div>
                </td>
                <td>
                    <div class="sig-space"></div>
                    <div class="sig-line"></div>
                    <div class="sig-role">Controller of Examinations</div>
                    <div class="sig-name">Signature &amp; Stamp</div>
                </td>
                <td>
                    <div class="sig-space"></div>
                    <div class="sig-line"></div>
                    <div class="sig-role">Principal / Head of Institution</div>
                    <div class="sig-name">Signature &amp; Stamp</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ══ VERIFICATION BAND ══ --}}
    <div class="watermark-band">
        This document is an authentic certified statement of academic achievement issued by
        <strong>{{ $school?->name ?? config('app.name') }}</strong>.
        @if(isset($verifyToken))
        <br>
        Verification Code: <span style="font-family:monospace; color:#64748b;">{{ $verifyToken }}</span>
        &nbsp;&bull;&nbsp; Scan the QR code or visit:
        <span style="font-family:monospace; color:#1e3a8a;">{{ route('results.verify', ['token' => $verifyToken]) }}</span>
        @endif
    </div>

    {{-- ══ BOTTOM BAR ══ --}}
    <div class="bottom-bar">
        <div class="bottom-bar-left">
            {{ $school?->name ?? config('app.name') }}
            &nbsp;&bull;&nbsp; Academic Year {{ $result['session']->session_name ?? now()->year }}
            &nbsp;&bull;&nbsp; {{ $semester?->semester_name ?? '' }}
        </div>
        <div class="bottom-bar-right">
            Printed: {{ now()->format('d M Y, h:i A') }}
        </div>
    </div>

</div>
</body>
</html>
