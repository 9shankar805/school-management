<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Official Class Progress Report Cards — {{ $class->class_name }} ({{ $semester->semester_name }})</title>
<style>
    @page {
        size: 210mm 297mm portrait;
        margin: 0;
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: DejaVu Sans, Arial, sans-serif;
        font-size: 9pt;
        color: #0f172a;
        background: #ffffff;
        line-height: 1;
        margin: 8mm;
        width: 194mm;
    }
    .sheet-page {
        width: 100%;
        border: 2pt solid #1a365d;
        padding: 5pt 7pt;
        page-break-after: always;
        page-break-inside: avoid;
        background: #ffffff;
    }
    .sheet-page:last-child {
        page-break-after: avoid;
    }

    /* Header */
    .header-table { width:100%; border-collapse:collapse; margin-bottom:3pt; }
    .header-table td { vertical-align:middle; }
    .school-logo-td { width:12%; text-align:left; }
    .school-info-td { width:76%; text-align:center; padding:0 4pt; }
    .school-qr-td   { width:12%; text-align:right; }

    .school-title {
        font-size: 17pt;
        font-weight: bold;
        color: #1a365d;
        text-transform: uppercase;
        letter-spacing: 0.4pt;
        line-height: 1.1;
        margin-bottom: 2pt;
    }
    .school-affiliation { font-size:7.5pt; font-weight:bold; color:#b45309; text-transform:uppercase; margin-bottom:1pt; }
    .school-address  { font-size:7.5pt; color:#334155; line-height:1.2; }
    .school-contacts { font-size:7pt; color:#475569; margin-top:1pt; }

    /* Ribbons */
    .ribbon-bar { width:100%; height:2pt; background:#1a365d; margin-bottom:1pt; }
    .ribbon-sub { width:100%; height:1pt; background:#d97706; margin-bottom:3pt; }

    /* Title */
    .title-banner { text-align:center; margin-bottom:4pt; }
    .title-banner .badge {
        display:inline-block;
        background:#1a365d;
        color:#fff;
        font-size:10.5pt;
        font-weight:bold;
        text-transform:uppercase;
        letter-spacing:0.6pt;
        padding:2.5pt 14pt;
    }
    .term-subtitle {
        font-size:8pt;
        font-weight:bold;
        color:#334155;
        margin-top:1.5pt;
        text-transform:uppercase;
        letter-spacing:0.2pt;
    }

    /* Student info */
    .profile-table {
        width:100%;
        border-collapse:collapse;
        border:1pt solid #94a3b8;
        margin-bottom:4pt;
    }
    .profile-table td {
        padding:2.5pt 4pt;
        font-size:8.5pt;
        vertical-align:middle;
        border-bottom:0.5pt solid #e2e8f0;
        border-right:0.5pt solid #e2e8f0;
        line-height:1.2;
    }
    .profile-table tr:last-child td { border-bottom:none; }
    .profile-table td:last-child    { border-right:none; }
    .lbl { width:15%; font-size:7pt; font-weight:bold; color:#475569; text-transform:uppercase; background-color:#f1f5f9; }
    .val { width:35%; font-size:8.5pt; font-weight:bold; color:#0f172a; }

    /* Marks table */
    table.marks-table { width:100%; border-collapse:collapse; table-layout:fixed; margin-bottom:4pt; border:1pt solid #1a365d; }
    table.marks-table th {
        background-color:#1a365d;
        color:#fff;
        font-size:7.5pt;
        font-weight:bold;
        text-align:center;
        padding:3pt 2pt;
        border:0.5pt solid #2d4a8a;
        text-transform:uppercase;
        letter-spacing:0.1pt;
        line-height:1.2;
    }
    table.marks-table th.th-left { text-align:left; padding-left:4pt; }
    table.marks-table td {
        border:0.5pt solid #cbd5e1;
        padding:3pt 2pt;
        font-size:8.5pt;
        text-align:center;
        vertical-align:middle;
        color:#1e293b;
        line-height:1;
    }
    table.marks-table td.td-left { text-align:left; padding-left:4pt; font-weight:bold; color:#0f172a; }
    table.marks-table tbody tr td     { background-color:#ffffff; }
    table.marks-table tbody tr.alt td { background-color:#f8fafc; }
    table.marks-table .total-row td {
        background-color:#dbeafe;
        font-weight:bold;
        color:#1a365d;
        font-size:8pt;
        border-top:1.5pt solid #1a365d;
        padding:3.5pt 2pt;
    }

    /* KPI */
    .kpi-table { width:100%; border-collapse:collapse; table-layout:fixed; margin-bottom:4pt; border:1pt solid #93c5fd; }
    .kpi-table td {
        width:20%;
        padding:4pt 3pt;
        text-align:center;
        vertical-align:middle;
        background-color:#eff6ff;
        border-right:0.5pt solid #93c5fd;
        line-height:1.1;
    }
    .kpi-table td:last-child { border-right:none; }
    .kpi-table td.kpi-amber { background-color:#fffbeb; border-right-color:#fcd34d; }
    .kpi-val  { font-size:14pt; font-weight:bold; color:#1a365d; }
    .kpi-val.amber { color:#b45309; }
    .kpi-val.green { color:#166534; }
    .kpi-val.red   { color:#dc2626; }
    .kpi-lbl  { font-size:6.5pt; font-weight:bold; color:#475569; text-transform:uppercase; letter-spacing:0.1pt; margin-top:1pt; }

    /* Evaluation */
    .evaluation-table { width:100%; border-collapse:collapse; margin-bottom:4pt; }
    .evaluation-table td { vertical-align:top; padding:0; }
    .eval-box { border:1pt solid #cbd5e1; padding:3.5pt 5pt; background-color:#fafafa; }
    .eval-title {
        font-size:7.5pt; font-weight:bold; color:#1a365d;
        text-transform:uppercase; letter-spacing:0.1pt;
        border-bottom:0.5pt solid #e2e8f0;
        padding-bottom:2pt; margin-bottom:2.5pt; line-height:1;
    }
    .eval-text { font-size:7.5pt; color:#1e293b; line-height:1.35; font-style:italic; margin-bottom:4pt; }
    .note-label { font-size:7pt; font-weight:bold; color:#64748b; border-top:0.5pt dashed #cbd5e1; padding-top:2pt; margin-top:2pt; line-height:1; }
    .note-line  { border-bottom:0.5pt dotted #94a3b8; height:11pt; margin-top:1.5pt; }
    .traits-table { width:100%; border-collapse:collapse; }
    .traits-table td { padding:1.5pt 2pt; font-size:7.5pt; border-bottom:0.5pt solid #f1f5f9; line-height:1.1; }
    .traits-table tr:last-child td { border-bottom:none; }
    .traits-lbl { font-weight:bold; color:#475569; width:60%; }
    .traits-val { font-weight:bold; color:#1a365d; width:40%; text-align:right; }

    /* Scale */
    .scale-box   { margin-bottom:4pt; }
    .scale-title { font-size:7pt; font-weight:bold; color:#475569; text-transform:uppercase; letter-spacing:0.1pt; margin-bottom:1.5pt; line-height:1; }
    .scale-table { width:100%; border-collapse:collapse; table-layout:fixed; border:1pt solid #cbd5e1; }
    .scale-table th { background-color:#f1f5f9; color:#334155; font-size:6.5pt; font-weight:bold; border:0.5pt solid #cbd5e1; padding:2pt; text-align:center; line-height:1.2; }
    .scale-table td { border:0.5pt solid #e2e8f0; font-size:6.5pt; padding:2pt; text-align:center; line-height:1.2; }
    .scale-table tr.scale-grade td { background-color:#f0fdf4; font-weight:bold; color:#166534; }
    .scale-table tr.scale-point td { background-color:#eff6ff; font-weight:bold; color:#1a365d; }

    /* Signatures */
    .sig-table { width:100%; border-collapse:collapse; table-layout:fixed; margin-top:4pt; margin-bottom:3pt; }
    .sig-table td { width:25%; text-align:center; vertical-align:bottom; padding:0 4pt; }
    .sig-space { height:24pt; }
    .sig-line  { border-top:0.8pt solid #475569; width:80%; margin:0 auto 1.5pt auto; }
    .sig-title { font-size:7.5pt; font-weight:bold; color:#0f172a; line-height:1.1; }
    .sig-sub   { font-size:6.5pt; color:#64748b; margin-top:0.5pt; line-height:1.1; }

    /* Footer */
    .cert-footer {
        border-top:0.5pt solid #e2e8f0;
        padding-top:2pt; margin-top:2pt;
        font-size:6.5pt; color:#64748b; text-align:center; line-height:1.3;
    }
</style>
</head>
<body>

@foreach($cards as $card)
@php
    $result       = $card['result'];
    $verifyToken  = $card['verifyToken'];
    $verifyUrl    = $card['verifyUrl'];
    $qrSrc        = $card['qrSrc'];
    $student      = $result['student'] ?? null;
    $cardClass    = $result['class'] ?? $class;
    $cardSec      = $result['section'] ?? $section;
    $cardSem      = $result['semester'] ?? $semester;
    $promotion    = $result['promotion'] ?? null;
    $courses      = $result['courses'] ?? [];
    $attendance   = $card['attendance'] ?? null;
    $cTeacher     = $card['classTeacher'] ?? ($classTeacher ?? null);

    // Resolve School
    if (!isset($school) || !$school) {
        try {
            $school = $student && isset($student->school_id) ? \App\Models\School::find($student->school_id) : null;
            $school = $school ?? config('tenant.school') ?? \App\Models\School::first();
        } catch (\Throwable $e) {
            $school = null;
        }
    }
    
    // Resolve Session ID
    $sessionId = $promotion?->session_id ?? session('browse_session_id') ?? 1;
    
    // Fallback Attendance stats
    if (empty($attendance)) {
        if ($student && !empty($student->id) && $sessionId && class_exists(\App\Models\Attendance::class)) {
            try {
                $attendance = \App\Models\Attendance::statsFor($student->id, $sessionId);
            } catch (\Throwable $e) {
                $attendance = ['total' => 0, 'present' => 0, 'absent' => 0, 'percentage' => 100];
            }
        } else {
            $attendance = ['total' => 0, 'present' => 0, 'absent' => 0, 'percentage' => 100];
        }
    }

    // Fallback Class Teacher
    if (!$cTeacher) {
        if ($cardClass && !empty($cardClass->id) && $sessionId && class_exists(\App\Models\AssignedTeacher::class)) {
            try {
                $assignedTeacher = \App\Models\AssignedTeacher::with('teacher')
                    ->where('session_id', $sessionId)
                    ->where('class_id', $cardClass->id)
                    ->where('is_class_teacher', true)
                    ->when($cardSec && !empty($cardSec->id) && $cardSec->id > 0, function($q) use ($cardSec) {
                        $q->where(function($sq) use ($cardSec) {
                            $sq->where('section_id', $cardSec->id)->orWhere('section_id', 0)->orWhereNull('section_id');
                        });
                    })
                    ->first();
                $cTeacher = $assignedTeacher?->teacher;
            } catch (\Throwable $e) {
                $cTeacher = null;
            }
        }
    }

    // Calculate Aggregate Totals
    $totalFullMarks = 0;
    $totalPassMarks = 0;
    $totalObtained  = (float)($result['totalMarks'] ?? 0);
    
    foreach ($courses as $c) {
        $fm = (float)($c['full_marks'] ?? 100);
        $pm = (float)($c['pass_marks'] ?? round($fm * 0.4, 0));
        $totalFullMarks += $fm;
        $totalPassMarks += $pm;
    }
    
    $aggregatePct = $totalFullMarks > 0 ? min(round(($totalObtained / $totalFullMarks) * 100, 1), 100.0) : 0;
    $gpa = (float)($result['gpa'] ?? 0);
    $failedCount = (int)($result['failed'] ?? 0);
    $passedCount = (int)($result['passed'] ?? count($courses));

    // Show CDC scale only when subject count is 12 or fewer
    $showCdcScale = count($courses) <= 12;

    // Resolve Overall Grade Letter & Division
    if ($failedCount > 0) {
        $overallGrade = 'NG';
        $division = 'Needs Retest';
        $resultStatus = 'RE-EXAM REQUIRED';
    } elseif ($gpa >= 3.6) {
        $overallGrade = 'A+';
        $division = 'Distinction';
        $resultStatus = 'PASSED WITH DISTINCTION';
    } elseif ($gpa >= 3.2) {
        $overallGrade = 'A';
        $division = 'First Division';
        $resultStatus = 'PASSED';
    } elseif ($gpa >= 2.8) {
        $overallGrade = 'B+';
        $division = 'First Division';
        $resultStatus = 'PASSED';
    } elseif ($gpa >= 2.4) {
        $overallGrade = 'B';
        $division = 'Second Division';
        $resultStatus = 'PASSED';
    } elseif ($gpa >= 2.0) {
        $overallGrade = 'C+';
        $division = 'Third Division';
        $resultStatus = 'PASSED';
    } elseif ($gpa >= 1.6) {
        $overallGrade = 'C';
        $division = 'Pass';
        $resultStatus = 'PASSED';
    } else {
        $overallGrade = 'D';
        $division = 'Basic Pass';
        $resultStatus = 'PASSED';
    }

    // Dynamic Class Teacher's Pedagogical Remark
    if ($failedCount > 0) {
        $teacherRemark = "Demonstrates good potential in interactive discussions, but requires remedial attention and structured revision in subject(s) below pass marks. Daily practice and subject teacher consultation strongly recommended.";
    } elseif ($gpa >= 3.75) {
        $teacherRemark = "Outstanding academic achievement! Demonstrates exceptional conceptual mastery, analytical precision, and commendable classroom leadership. Consistently dedicated to highest academic standards.";
    } elseif ($gpa >= 3.3) {
        $teacherRemark = "Very good academic performance! Consistently attentive, diligent, and shows commendable analytical thinking and positive conduct in class discussions.";
    } elseif ($gpa >= 2.8) {
        $teacherRemark = "Good performance overall. Participates constructively in class; regular home practice and focus on challenging areas will help achieve higher distinction.";
    } elseif ($gpa >= 2.2) {
        $teacherRemark = "Satisfactory progress. Possesses solid foundational ability; higher consistency in homework completion and attentive class focus will yield even better marks.";
    } else {
        $teacherRemark = "Basic pass. Regular attendance, structured homework schedule, and targeted revision in core subjects are encouraged for the upcoming term.";
    }

    // Dynamic Co-Curricular & Conduct Attributes
    if ($failedCount > 0) {
        $traitDiscipline    = 'Good';
        $traitParticipation = 'Developing';
        $traitHomework      = 'Needs Regularity';
        $traitTeamwork      = 'Cooperative';
    } elseif ($gpa >= 3.6) {
        $traitDiscipline    = 'Exemplary';
        $traitParticipation = 'Active & Inquisitive';
        $traitHomework      = 'Prompt & Thorough';
        $traitTeamwork      = 'Commendable Leader';
    } elseif ($gpa >= 3.0) {
        $traitDiscipline    = 'Very Good';
        $traitParticipation = 'Constructive';
        $traitHomework      = 'Regular';
        $traitTeamwork      = 'Helpful & Polite';
    } else {
        $traitDiscipline    = 'Good';
        $traitParticipation = 'Satisfactory';
        $traitHomework      = 'Fair';
        $traitTeamwork      = 'Friendly';
    }
@endphp

<div class="sheet-page">

    {{-- Header --}}
    <table class="header-table">
        <tr>
            <td class="school-logo-td">
                @if(!empty($logoSrc))
                    <img src="{{ $logoSrc }}" style="max-height:64pt; max-width:80pt;" alt="Logo">
                @else
                    <div style="width:56pt; height:56pt; border:2pt solid #1e3a8a; border-radius:3pt; font-size:14pt; font-weight:bold; color:#1e3a8a; text-align:center; padding-top:14pt;">
                        {{ strtoupper(substr($school?->name ?? 'S', 0, 3)) }}
                    </div>
                @endif
            </td>
            <td class="school-info-td">
                <h1 class="school-title">{{ $school?->name ?? config('app.name') }}</h1>
                @if($school?->iemis_code)
                    <p class="school-affiliation">Gov. of Nepal Approved &bull; EMIS: {{ $school->iemis_code }}</p>
                @else
                    <p class="school-affiliation">Recognised Academic Institution</p>
                @endif
                <p class="school-address">{{ $school?->address ?? 'Kathmandu, Bagmati Province, Nepal' }}</p>
                <p class="school-contacts">
                    @if($school?->phone)Tel: {{ $school->phone }} @endif
                    @if($school?->email)| Email: {{ $school->email }} @endif
                    | Web: {{ $school?->website ?? 'www.nepalschoolhub.com' }}
                </p>
            </td>
            <td class="school-qr-td">
                @if(isset($qrSrc) && !empty($qrSrc))
                    <div style="text-align:center; display:inline-block;">
                        <img src="{{ $qrSrc }}" style="width:56pt; height:56pt; border:1pt solid #cbd5e1; padding:2pt; background:#fff;" alt="QR">
                        <div style="font-size:6pt; color:#1e3a8a; font-weight:bold; margin-top:1pt;">SCAN TO VERIFY</div>
                    </div>
                @else
                    <div style="width:56pt; height:56pt; border:1pt dashed #cbd5e1; border-radius:3pt; text-align:center; padding-top:16pt; font-size:7pt; color:#64748b; line-height:1.3;">OFFICIAL<br>VERIFIED</div>
                @endif
            </td>
        </tr>
    </table>

    <div class="ribbon-bar"></div>
    <div class="ribbon-sub"></div>

    <div class="title-banner">
        <span class="badge">Official Academic Progress Report Card</span>
        <div class="term-subtitle">
            {{ $cardSem?->semester_name ?? 'Terminal Examination' }} &bull; Academic Session 2083
        </div>
    </div>

    {{-- Student info --}}
    <table class="profile-table">
        <tr>
            <td class="lbl">Student Name</td>
            <td class="val" style="font-size:10pt; color:#1a365d;" colspan="3">{{ $student?->full_name ?? '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Roll No.</td>
            <td class="val" style="font-size:10pt; color:#1e3a8a;">{{ $promotion?->roll_no ?? $student?->roll_no ?? '—' }}</td>
            <td class="lbl">Class &amp; Section</td>
            <td class="val">{{ $cardClass?->class_name ?? '—' }} &bull; Sec {{ $cardSec?->section_name ?? 'All' }}</td>
        </tr>
        <tr>
            <td class="lbl">Student ID</td>
            <td class="val" colspan="3">{{ $student?->admission_no ?? sprintf('STD-%05d', $student?->id ?? 0) }}</td>
        </tr>
        <tr>
            <td class="lbl">Date of Issue</td>
            <td class="val" colspan="3">{{ now()->format('d M Y') }} <span style="font-size:8pt; font-weight:normal; color:#475569;">(@bsDateEn(now()->toDateString()))</span></td>
        </tr>
    </table>

    {{-- Marks table --}}
    <table class="marks-table">
        <thead>
            <tr>
                <th style="width:4%;">SN</th>
                <th class="th-left" style="width:40%;">Subject / Course Name</th>
                <th style="width:10%;">Full<br>Marks</th>
                <th style="width:10%;">Pass<br>Marks</th>
                <th style="width:10%;">Marks<br>Obtained</th>
                <th style="width:8%;">%</th>
                <th style="width:10%;">Grade<br>Point</th>
                <th style="width:8%;">Letter<br>Grade</th>
            </tr>
        </thead>
        <tbody>
            @php $sn = 1; @endphp
            @foreach($courses as $idx => $c)
            @php
                $fm  = (float)($c['full_marks'] ?? 100);
                $pm  = (float)($c['pass_marks'] ?? round($fm * 0.4, 0));
                $ob  = (float)$c['final_marks'];
                $pct = min($fm > 0 ? round(($ob / $fm) * 100, 1) : $ob, 100.0);
                $passed = (bool)($c['passed'] ?? ($ob >= $pm));
                $rowClass = ($idx % 2 === 1) ? 'alt' : '';
                $gradeColor = match(true) {
                    in_array($c['grade'], ['A+','A'])   => '#166534',
                    in_array($c['grade'], ['B+','B'])   => '#1e40af',
                    in_array($c['grade'], ['C+','C'])   => '#b45309',
                    in_array($c['grade'], ['D'])        => '#7c3aed',
                    default                             => '#dc2626',
                };
            @endphp
            <tr class="{{ $rowClass }}">
                <td>{{ $sn++ }}</td>
                <td class="td-left">{{ $c['course']?->course_name ?? '—' }}</td>
                <td style="font-weight:bold;">{{ number_format($fm, 0) }}</td>
                <td style="color:#475569;">{{ number_format($pm, 0) }}</td>
                <td style="font-weight:bold; font-size:10.5pt; color:{{ $passed ? '#0f172a' : '#dc2626' }};">{{ round($ob, 1) }}</td>
                <td style="color:#64748b;">{{ $pct }}%</td>
                <td style="font-weight:bold; color:#1e3a8a;">{{ number_format((float)$c['point'], 2) }}</td>
                <td style="font-weight:bold; font-size:11pt; color:{{ $passed ? $gradeColor : '#dc2626' }};">{{ $c['grade'] }}</td>
            </tr>
            @endforeach

            <tr class="total-row">
                <td colspan="2" style="text-align:right; padding-right:8pt; text-transform:uppercase; letter-spacing:0.2pt;">Grand Total &amp; Aggregate:</td>
                <td>{{ number_format($totalFullMarks, 0) }}</td>
                <td>{{ number_format($totalPassMarks, 0) }}</td>
                <td style="font-size:10pt; font-weight:bold;">{{ round($totalObtained, 1) }}</td>
                <td>{{ $aggregatePct }}%</td>
                <td style="font-size:10pt; font-weight:bold;">{{ number_format($gpa, 2) }}</td>
                <td style="font-size:11pt; font-weight:bold; color:{{ $failedCount === 0 ? '#166534' : '#dc2626' }};">{{ $overallGrade }}</td>
            </tr>
        </tbody>
    </table>

    {{-- KPI band --}}
    <table class="kpi-table">
        <tr>
            <td class="kpi-blue">
                <div class="kpi-val">{{ round($totalObtained, 0) }}<span style="font-size:9pt; font-weight:normal; color:#64748b;"> / {{ number_format($totalFullMarks, 0) }}</span></div>
                <div class="kpi-lbl">Total Marks</div>
            </td>
            <td class="kpi-blue">
                <div class="kpi-val">{{ $aggregatePct }}%</div>
                <div class="kpi-lbl">Percentage</div>
            </td>
            <td class="kpi-blue">
                <div class="kpi-val">{{ number_format($gpa, 2) }}</div>
                <div class="kpi-lbl">GPA</div>
            </td>
            <td class="kpi-amber">
                <div class="kpi-val amber">{{ $overallGrade }} <span style="font-size:9pt; font-weight:bold;">({{ $division }})</span></div>
                <div class="kpi-lbl">Division</div>
            </td>
            <td class="kpi-amber">
                @if(!empty($result['rank']))
                    <div class="kpi-val amber">{{ ordinal((int)$result['rank']) }}</div>
                    <div class="kpi-lbl">Class Rank</div>
                @else
                    <div class="kpi-val {{ $failedCount === 0 ? 'green' : 'red' }}" style="font-size:11pt;">{{ $resultStatus }}</div>
                    <div class="kpi-lbl">Standing</div>
                @endif
            </td>
        </tr>
    </table>

    {{-- Evaluation --}}
    {{-- CDC scale (shown only when content fits on one page) --}}
    @if($showCdcScale)
    <div class="scale-box">
        <div class="scale-title">CDC National Grading Scale Reference (Nepal Curriculum Development Centre)</div>
        <table class="scale-table">
            <tr>
                <th>Interval</th>
                <td>90–100%</td><td>80–89%</td><td>70–79%</td><td>60–69%</td>
                <td>50–59%</td><td>40–49%</td><td>35–39%</td><td>&lt;35%</td>
            </tr>
            <tr class="scale-grade">
                <th>Grade</th>
                <td>A+</td><td>A</td><td>B+</td><td>B</td>
                <td>C+</td><td>C</td><td>D</td><td>NG</td>
            </tr>
            <tr class="scale-point">
                <th>Points</th>
                <td>4.0</td><td>3.6</td><td>3.2</td><td>2.8</td>
                <td>2.4</td><td>2.0</td><td>1.6</td><td>0.0</td>
            </tr>
            <tr>
                <th>Level</th>
                <td>Outstanding</td><td>Excellent</td><td>Very Good</td><td>Good</td>
                <td>Satisfactory</td><td>Acceptable</td><td>Basic</td><td>Non-Graded</td>
            </tr>
        </table>
    </div>
    @endif

    {{-- Signatures --}}
    <table class="sig-table">
        <tr>
            <td>
                <div class="sig-space"></div>
                <div class="sig-line"></div>
                <div class="sig-title">{{ $cTeacher?->full_name ?? 'Class Teacher' }}</div>
                <div class="sig-sub">Class In-Charge</div>
            </td>
            <td>
                <div class="sig-space"></div>
                <div class="sig-line"></div>
                <div class="sig-title">Exam Controller</div>
                <div class="sig-sub">Controller of Examinations</div>
            </td>
            <td>
                <div class="sig-space" style="text-align:center;">
                    <div style="display:block; width:28pt; height:28pt; margin:0 auto; border:1pt dashed #94a3b8; border-radius:50%; font-size:6pt; color:#94a3b8; text-align:center; padding-top:8pt;">SEAL</div>
                </div>
                <div class="sig-line"></div>
                <div class="sig-title">Principal / Headmaster</div>
                <div class="sig-sub">Official Seal &amp; Signature</div>
            </td>
            <td>
                <div class="sig-space"></div>
                <div class="sig-line"></div>
                <div class="sig-title">Parent / Guardian</div>
                <div class="sig-sub">Acknowledgment Signature</div>
            </td>
        </tr>
    </table>

    {{-- Footer --}}
    <div class="cert-footer">
        This certified Academic Report Card is an official legal record issued by <strong>{{ $school?->name ?? config('app.name') }}</strong>. Any alteration invalidates this document.
        @if(isset($verifyToken))
        <br><span style="font-family:monospace; font-size:6.5pt; color:#475569;">Token: {{ $verifyToken }} &bull; Verify at: {{ route('results.verify', ['token' => $verifyToken]) }}</span>
        @endif
    </div>

</div>
@endforeach

</body>
</html>
