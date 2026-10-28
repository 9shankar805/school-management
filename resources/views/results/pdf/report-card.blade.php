<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Official Academic Marksheet — {{ $result['student']?->full_name }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 12px;
        color: #1e293b;
        padding: 28px 32px;
        background: #fff;
        width: 100%;
    }

    /* ── Institutional Header ─────────────────────── */
    .header-table { width: 100%; border-collapse: collapse; border-bottom: 3px solid #1e3a8a; padding-bottom: 14px; margin-bottom: 16px; }
    .school-name  { font-size: 20px; color: #1e3a8a; font-weight: bold; text-transform: uppercase; margin: 0 0 3px 0; letter-spacing: 0.5px; }
    .school-sub   { font-size: 10.5px; color: #475569; margin: 0 0 2px 0; }
    .school-emis  { font-size: 9px; color: #64748b; font-style: italic; margin: 0; }

    /* ── Title Badge ──────────────────────────────── */
    .title-badge { text-align: center; margin-bottom: 16px; }
    .title-badge span {
        background: #1e3a8a;
        color: #fff;
        font-size: 13px;
        font-weight: bold;
        letter-spacing: 2px;
        padding: 6px 24px;
        border-radius: 4px;
        text-transform: uppercase;
    }

    /* ── Student Info Grid ────────────────────────── */
    .student-info {
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 12px 16px;
        margin-bottom: 16px;
        width: 100%;
        border-collapse: collapse;
    }
    .student-info td { padding: 5px 8px; font-size: 10.5px; vertical-align: middle; }
    .lbl { color: #64748b; font-weight: 600; width: 17%; }
    .val { color: #0f172a; font-weight: bold; width: 33%; }

    /* ── Marks Table ──────────────────────────────── */
    table.marks-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    table.marks-table th {
        background: #1e3a8a;
        color: #fff;
        padding: 9px 10px;
        font-size: 10px;
        font-weight: bold;
        text-align: center;
        border: 1px solid #1e3a8a;
    }
    table.marks-table th:first-child { text-align: left; padding-left: 12px; }
    table.marks-table td {
        padding: 8px 10px;
        border: 1px solid #cbd5e1;
        font-size: 10.5px;
        text-align: center;
    }
    table.marks-table td:first-child { text-align: left; padding-left: 12px; }
    table.marks-table tbody tr:nth-child(even) { background: #f8fafc; }

    /* ── Summary Bar ──────────────────────────────── */
    .summary-box {
        background: #eff6ff;
        border: 1.5px solid #bfdbfe;
        border-radius: 8px;
        padding: 16px 20px;
        margin-bottom: 18px;
        display: table;
        width: 100%;
    }
    .summary-cell { display: table-cell; text-align: center; width: 25%; vertical-align: middle; }
    .summary-val  { font-size: 26px; font-weight: bold; color: #1e3a8a; line-height: 1.1; }
    .summary-lbl  { font-size: 9px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 4px; }

    /* ── Grading Key ──────────────────────────────── */
    .grading-section-label {
        font-size: 9.5px; font-weight: bold; color: #475569;
        margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px;
    }
    .grading-key { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    .grading-key th {
        background: #f1f5f9;
        color: #334155;
        font-size: 9px;
        font-weight: bold;
        border: 1px solid #cbd5e1;
        padding: 5px 6px;
        text-align: center;
    }
    .grading-key td {
        border: 1px solid #e2e8f0;
        font-size: 9px;
        padding: 5px 6px;
        text-align: center;
    }
    .grading-key tr:nth-child(2) td { background: #f0fdf4; font-weight: bold; color: #166534; }
    .grading-key tr:nth-child(3) td { background: #eff6ff; font-weight: bold; color: #1e3a8a; }

    /* ── Signature Section ────────────────────────── */
    .sig-table { width: 100%; border-collapse: collapse; margin-top: 30px; }
    .sig-table td {
        width: 33.33%;
        text-align: center;
        vertical-align: bottom;
        font-size: 10px;
        color: #334155;
        padding: 0 10px;
    }
    .sig-space { height: 52px; }
    .sig-line  { border-top: 1.5px solid #475569; width: 80%; margin: 0 auto 5px; }
    .sig-name  { font-size: 9px; color: #64748b; margin-top: 3px; }

    /* ── Footer ───────────────────────────────────── */
    .footer {
        text-align: center;
        font-size: 8.5px;
        color: #94a3b8;
        margin-top: 20px;
        padding-top: 8px;
        border-top: 1px solid #e2e8f0;
        line-height: 1.6;
    }

    /* ── Accent strip ─────────────────────────────── */
    .accent-strip {
        height: 4px;
        background: #1e3a8a;
        border-radius: 2px;
        margin-bottom: 16px;
    }
</style>
</head>
<body>

@php
    $student  = $result['student'] ?? null;
    // $school is already passed from the controller — do not override it
    if (!isset($school) || !$school) {
        $school = $student ? \App\Models\School::find($student->school_id) : null;
        $school = $school ?? \App\Models\School::first();
    }
    $semester = $result['semester'] ?? null;
@endphp

{{-- Institutional Header --}}
<table class="header-table">
    <tr>
        <td style="width: 16%; vertical-align: middle; text-align: left;">
            @if(!empty($logoSrc))
                <img src="{{ $logoSrc }}" style="max-height: 70px; max-width: 100px;" alt="Logo">
            @else
                <div style="font-size: 13px; font-weight: bold; color: #1e3a8a; border: 2px solid #1e3a8a; border-radius: 6px; padding: 8px 10px; text-align: center; display: inline-block;">
                    {{ strtoupper(substr($school?->name ?? config('app.name'), 0, 6)) }}
                </div>
            @endif
        </td>
        <td style="width: 64%; text-align: center; vertical-align: middle;">
            <h1 class="school-name">{{ $school?->name ?? config('app.name') }}</h1>
            <p class="school-sub">{{ $school?->address ?? 'Kathmandu, Bagmati Province, Nepal' }} &bull; Tel: {{ $school?->phone ?? '+977-1-4412345' }}</p>
            @if($school?->iemis_code)
            <p class="school-emis">Ministry of Education — CEHRD EMIS Code: {{ $school->iemis_code }}</p>
            @endif
        </td>
        <td style="width: 20%; text-align: right; vertical-align: middle;">
            @if(isset($qrSrc))
            <div style="display: inline-block; text-align: center;">
                <img src="{{ $qrSrc }}" style="width: 68px; height: 68px; border: 1.5px solid #cbd5e1; padding: 2px; background: #fff;" alt="Verify QR">
                <div style="font-size: 7px; color: #1e3a8a; font-weight: bold; margin-top: 3px; letter-spacing: 0.8px;">SCAN TO VERIFY</div>
            </div>
            @endif
        </td>
    </tr>
</table>

{{-- Accent Strip --}}
<div class="accent-strip"></div>

{{-- Title Badge --}}
<div class="title-badge">
    <span>Academic Achievement Marksheet / Grade Sheet</span>
</div>

{{-- Student Details --}}
<table class="student-info">
    <tr>
        <td class="lbl">Student Name:</td>
        <td class="val">{{ $student?->full_name }}</td>
        <td class="lbl">Academic Term:</td>
        <td class="val">{{ $semester?->semester_name ?? 'First Term 2026' }}</td>
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
                <span style="font-size:16px; font-weight:bold; color:#1e3a8a;">{{ ordinal($rank) }}</span>
                <span style="font-size:10px; color:#64748b; font-weight:normal;"> Position</span>
            @else
                &mdash;
            @endif
        </td>
        <td class="lbl">Issue Date:</td>
        <td class="val">{{ now()->format('d M Y') }} (@bsDateEn(now()->toDateString()))</td>
    </tr>
</table>

{{-- Academic Marks Table --}}
<table class="marks-table">
    <thead>
        <tr>
            <th style="width: 30%;">Subject / Course Name</th>
            <th style="width: 11%;">Full Marks</th>
            <th style="width: 11%;">Obtained</th>
            <th style="width: 9%;">%</th>
            <th style="width: 10%;">Grade</th>
            <th style="width: 13%;">Grade Point</th>
            <th style="width: 16%;">Remarks</th>
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
            <td><b>{{ $c['course']?->course_name ?? 'Course' }}</b></td>
            <td>{{ $fm }}</td>
            <td>{{ round($ob, 1) }}</td>
            <td style="color: #475569;">{{ $pct }}%</td>
            <td style="font-weight: bold; font-size: 12px; color: {{ $c['passed'] ? '#059669' : '#dc2626' }};">
                {{ $c['grade'] }}
            </td>
            <td style="font-weight: bold; color: #1e3a8a;">{{ number_format((float)$c['point'], 2) }}</td>
            <td>
                @if($c['passed'])
                <span style="color:#059669; font-weight: bold;">PASSED</span>
                @else
                <span style="color:#dc2626; font-weight: bold;">NEEDS RETEST</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Result Summary Box --}}
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
        <div class="summary-val" style="color: #059669;">{{ $result['passed'] }}</div>
        <div class="summary-lbl">Courses Passed</div>
    </div>
    <div class="summary-cell">
        <div class="summary-val" style="color: {{ $result['failed'] > 0 ? '#dc2626' : '#64748b' }};">
            {{ $result['failed'] }}
        </div>
        <div class="summary-lbl">Courses Below Pass</div>
    </div>
</div>

{{-- Nepal CDC 8-Point Grading Key --}}
<p class="grading-section-label">Government of Nepal — CDC Grading System Key:</p>
<table class="grading-key">
    <tr>
        <th>Interval</th>
        <td>90% - 100%</td><td>80% - 89%</td><td>70% - 79%</td><td>60% - 69%</td>
        <td>50% - 59%</td><td>40% - 49%</td><td>35% - 39%</td><td>Below 35%</td>
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

{{-- Institutional Signatures --}}
<table class="sig-table">
    <tr>
        <td>
            <div class="sig-space"></div>
            <div class="sig-line"></div>
            <div style="font-weight: bold;">Class Teacher</div>
            <div class="sig-name">Name &amp; Stamp</div>
        </td>
        <td>
            <div class="sig-space"></div>
            <div class="sig-line"></div>
            <div style="font-weight: bold;">Controller of Examinations</div>
            <div class="sig-name">Name &amp; Stamp</div>
        </td>
        <td>
            <div class="sig-space"></div>
            <div class="sig-line"></div>
            <div style="font-weight: bold;">Principal / Head of Institution</div>
  
of Examinations
        </td>
        <td>
            <div class="sig-line"></div>
            <b>Principal / Head of Institution</b>
        </td>
    </tr>
</table>

<div class="footer">
    This document is an authentic certified statement of academic achievement generated by {{ $school?->name ?? config('app.name') }}.
    @if(isset($verifyToken))
    <br><span style="font-family: monospace; font-size: 7.5px; color: #64748b;">Verification Code: {{ $verifyToken }} &bull; Scan QR Code or visit {{ route('results.verify', ['token' => $verifyToken]) }}</span>
    @endif
</div>

</body>
</html>
