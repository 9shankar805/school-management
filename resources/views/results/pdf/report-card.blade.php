<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Official Academic Marksheet — {{ $result['student']?->full_name }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 11px;
        color: #1e293b;
        padding: 22px 26px;
        background: #fff;
        width: 100%;
    }
    .header-table { width: 100%; border-collapse: collapse; border-bottom: 3px solid #1e3a8a; padding-bottom: 12px; margin-bottom: 12px; }
    .school-name  { font-size: 18px; color: #1e3a8a; font-weight: bold; text-transform: uppercase; margin: 0 0 2px 0; }
    .school-sub   { font-size: 9.5px; color: #475569; margin: 0; }
    .title-badge  { text-align: center; margin-bottom: 12px; }
    .title-badge span { background: #1e3a8a; color: #fff; font-size: 11px; font-weight: bold; letter-spacing: 2px; padding: 5px 20px; border-radius: 4px; text-transform: uppercase; }
    .student-info { background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 5px; padding: 10px 14px; margin-bottom: 13px; width: 100%; border-collapse: collapse; }
    .student-info td { padding: 4px 7px; font-size: 10px; vertical-align: middle; }
    .lbl { color: #64748b; font-weight: 600; width: 17%; }
    .val { color: #0f172a; font-weight: bold; width: 33%; }
    table.marks-table { width: 100%; border-collapse: collapse; margin-bottom: 13px; }
    table.marks-table th { background: #1e3a8a; color: #fff; padding: 7px 6px; font-size: 9px; font-weight: bold; text-align: center; border: 1px solid #1e3a8a; }
    table.marks-table th:first-child { text-align: left; padding-left: 10px; }
    table.marks-table td { padding: 6px 6px; border: 1px solid #cbd5e1; font-size: 9.5px; text-align: center; }
    table.marks-table td:first-child { text-align: left; padding-left: 10px; }
    table.marks-table tbody tr:nth-child(even) { background: #f8fafc; }
    .summary-box { background: #eff6ff; border: 1.5px solid #bfdbfe; border-radius: 7px; padding: 13px 16px; margin-bottom: 15px; display: table; width: 100%; }
    .summary-cell { display: table-cell; text-align: center; width: 25%; vertical-align: middle; }
    .summary-val  { font-size: 22px; font-weight: bold; color: #1e3a8a; line-height: 1.1; }
    .summary-lbl  { font-size: 8px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 3px; }
    .grading-section-label { font-size: 9px; font-weight: bold; color: #475569; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px; }
    .grading-key { width: 100%; border-collapse: collapse; margin-bottom: 18px; table-layout: fixed; }
    .grading-key th { background: #f1f5f9; color: #334155; font-size: 8px; font-weight: bold; border: 1px solid #cbd5e1; padding: 4px 3px; text-align: center; width: 11.11%; }
    .grading-key td { border: 1px solid #e2e8f0; font-size: 8px; padding: 4px 3px; text-align: center; width: 11.11%; overflow: hidden; }
    .grading-key tr:nth-child(2) td { background: #f0fdf4; font-weight: bold; color: #166534; }
    .grading-key tr:nth-child(3) td { background: #eff6ff; font-weight: bold; color: #1e3a8a; }
    .sig-table { width: 100%; border-collapse: collapse; margin-top: 26px; }
    .sig-table td { width: 33.33%; text-align: center; vertical-align: bottom; font-size: 9.5px; color: #334155; padding: 0 8px; }
    .sig-space { height: 46px; }
    .sig-line  { border-top: 1.5px solid #475569; width: 80%; margin: 0 auto 4px; }
    .sig-name  { font-size: 8.5px; color: #64748b; margin-top: 2px; }
    .footer { text-align: center; font-size: 8px; color: #94a3b8; margin-top: 16px; padding-top: 7px; border-top: 1px solid #e2e8f0; line-height: 1.6; }
    .accent-strip { height: 3px; background: #1e3a8a; border-radius: 2px; margin-bottom: 12px; }
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
@endphp

{{-- Header --}}
<table class="header-table">
    <tr>
        <td style="width:15%; vertical-align:middle; text-align:left;">
            @if(!empty($logoSrc))
                <img src="{{ $logoSrc }}" style="max-height:65px; max-width:90px;" alt="Logo">
            @else
                <div style="font-size:12px; font-weight:bold; color:#1e3a8a; border:2px solid #1e3a8a; border-radius:5px; padding:7px 9px; text-align:center; display:inline-block;">
                    {{ strtoupper(substr($school?->name ?? config('app.name'), 0, 4)) }}
                </div>
            @endif
        </td>
        <td style="width:65%; text-align:center; vertical-align:middle;">
            <h1 class="school-name">{{ $school?->name ?? config('app.name') }}</h1>
            <p class="school-sub">{{ $school?->address ?? 'Kathmandu, Bagmati Province, Nepal' }} &bull; Tel: {{ $school?->phone ?? '' }}</p>
        </td>
        <td style="width:20%; text-align:right; vertical-align:middle;">
            @if(isset($qrSrc))
            <div style="display:inline-block; text-align:center;">
                <img src="{{ $qrSrc }}" style="width:62px; height:62px; border:1.5px solid #cbd5e1; padding:2px; background:#fff;" alt="Verify QR">
                <div style="font-size:6.5px; color:#1e3a8a; font-weight:bold; margin-top:2px; letter-spacing:0.8px;">SCAN TO VERIFY</div>
            </div>
            @endif
        </td>
    </tr>
</table>

<div class="accent-strip"></div>

<div class="title-badge"><span>Academic Achievement Marksheet / Grade Sheet</span></div>

{{-- Student Info --}}
<table class="student-info">
    <tr>
        <td class="lbl">Student Name:</td>
        <td class="val">{{ $student?->full_name }}</td>
        <td class="lbl">Academic Term:</td>
        <td class="val">{{ $semester?->semester_name ?? '—' }}</td>
    </tr>
    <tr>
        <td class="lbl">Roll Number:</td>
        <td class="val">{{ $result['promotion']?->roll_no ?? $student?->academic_info?->roll_number ?? '—' }}</td>
        <td class="lbl">Class &amp; Section:</td>
        <td class="val">{{ $result['class']?->class_name ?? '—' }} &bull; Sec {{ $result['section']?->section_name ?? '—' }}</td>
    </tr>
    <tr>
        <td class="lbl">Class Rank:</td>
        <td class="val">
            @if($result['rank'])
                @php $rank = (int)$result['rank']; @endphp
                <span style="font-size:15px; font-weight:bold; color:#1e3a8a;">{{ ordinal($rank) }}</span>
                <span style="font-size:9px; color:#64748b; font-weight:normal;"> Position</span>
            @else &mdash;
            @endif
        </td>
        <td class="lbl">Issue Date:</td>
        <td class="val">{{ now()->format('d M Y') }} (@bsDateEn(now()->toDateString()))</td>
    </tr>
</table>

{{-- Marks Table --}}
<table class="marks-table">
    <thead>
        <tr>
            <th style="width:32%;">Subject / Course Name</th>
            <th style="width:12%;">Full Marks</th>
            <th style="width:11%;">Obtained</th>
            <th style="width:9%;">%</th>
            <th style="width:10%;">Grade</th>
            <th style="width:13%;">Grade Point</th>
            <th style="width:13%;">Remarks</th>
        </tr>
    </thead>
    <tbody>
        @foreach($result['courses'] as $c)
        @php
            $fm  = (float)($c['full_marks'] ?? 100);
            $ob  = (float)$c['final_marks'];
            $pct = $fm > 0 ? round(($ob / $fm) * 100, 1) : $ob;
        @endphp
        <tr>
            <td><b>{{ $c['course']?->course_name ?? '—' }}</b></td>
            <td>{{ number_format($fm, 0) }}</td>
            <td>{{ round($ob, 1) }}</td>
            <td style="color:#475569;">{{ $pct }}%</td>
            <td style="font-weight:bold; font-size:11px; color:{{ $c['passed'] ? '#059669' : '#dc2626' }};">{{ $c['grade'] }}</td>
            <td style="font-weight:bold; color:#1e3a8a;">{{ number_format((float)$c['point'], 2) }}</td>
            <td>
                @if($c['passed'])
                    <span style="color:#059669; font-weight:bold;">PASSED</span>
                @else
                    <span style="color:#dc2626; font-weight:bold;">NEEDS RETEST</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Summary Box --}}
<div class="summary-box">
    <div class="summary-cell">
        <div class="summary-val">{{ $result['gpa'] }}</div>
        <div class="summary-lbl">Grade Point Average (GPA)</div>
    </div>
    <div class="summary-cell">
        <div class="summary-val">{{ round($result['totalMarks'], 1) }}</div>
        <div class="summary-lbl">Total Aggregate Marks</div>
    </div>
    <div class="summary-cell">
        <div class="summary-val" style="color:#059669;">{{ $result['passed'] }}</div>
        <div class="summary-lbl">Courses Passed</div>
    </div>
    <div class="summary-cell">
        <div class="summary-val" style="color:{{ $result['failed'] > 0 ? '#dc2626' : '#64748b' }};">{{ $result['failed'] }}</div>
        <div class="summary-lbl">Courses Below Pass</div>
    </div>
</div>

{{-- Grading Key --}}
<p class="grading-section-label">Government of Nepal — CDC Grading System Key:</p>
<table class="grading-key">
    <tr>
        <th>Interval</th>
        <td>90-100%</td><td>80-89%</td><td>70-79%</td><td>60-69%</td>
        <td>50-59%</td><td>40-49%</td><td>35-39%</td><td>Below 35%</td>
    </tr>
    <tr>
        <th>Grade</th>
        <td><b>A+</b></td><td><b>A</b></td><td><b>B+</b></td><td><b>B</b></td>
        <td><b>C+</b></td><td><b>C</b></td><td><b>D</b></td><td><b>NG</b></td>
    </tr>
    <tr>
        <th>Grade Point</th>
        <td>4.0</td><td>3.6</td><td>3.2</td><td>2.8</td>
        <td>2.4</td><td>2.0</td><td>1.6</td><td>0.0</td>
    </tr>
    <tr>
        <th>Description</th>
        <td>Outstanding</td><td>Excellent</td><td>Very Good</td><td>Good</td>
        <td>Satisfactory</td><td>Acceptable</td><td>Basic</td><td>Non-Graded</td>
    </tr>
</table>

{{-- Signatures --}}
<table class="sig-table">
    <tr>
        <td>
            <div class="sig-space"></div>
            <div class="sig-line"></div>
            <div style="font-weight:bold;">Class Teacher</div>
            <div class="sig-name">Name &amp; Stamp</div>
        </td>
        <td>
            <div class="sig-space"></div>
            <div class="sig-line"></div>
            <div style="font-weight:bold;">Controller of Examinations</div>
            <div class="sig-name">Name &amp; Stamp</div>
        </td>
        <td>
            <div class="sig-space"></div>
            <div class="sig-line"></div>
            <div style="font-weight:bold;">Principal / Head of Institution</div>
            <div class="sig-name">Name &amp; Stamp</div>
        </td>
    </tr>
</table>

<div class="footer">
    This document is an authentic certified statement of academic achievement generated by {{ $school?->name ?? config('app.name') }}.
    @if(isset($verifyToken))
    <br><span style="font-family:monospace; font-size:7px; color:#64748b;">Verification Code: {{ $verifyToken }} &bull; Scan QR Code or visit {{ route('results.verify', ['token' => $verifyToken]) }}</span>
    @endif
</div>

</body>
</html>
