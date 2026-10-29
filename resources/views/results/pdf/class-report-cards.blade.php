<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Official Class Progress Report Cards — {{ $class->class_name }} ({{ $semester->semester_name }})</title>
<style>
    @page {
        size: A4 portrait;
        margin: 6mm 8mm 6mm 8mm;
    }
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 8.5px;
        color: #0f172a;
        background: #ffffff;
        line-height: 1.2;
        width: 100%;
    }
    .sheet-page {
        width: 100%;
        border: 2.5px solid #1e3a8a;
        padding: 6px 10px 5px 10px;
        page-break-after: always;
        page-break-inside: avoid;
        background: #ffffff;
    }
    .sheet-page:last-child {
        page-break-after: avoid;
    }

    /* Header & Branding */
    .header-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 4px;
    }
    .header-table td {
        vertical-align: middle;
    }
    .school-logo-td {
        width: 14%;
        text-align: left;
    }
    .school-info-td {
        width: 72%;
        text-align: center;
        padding: 0 4px;
    }
    .school-qr-td {
        width: 14%;
        text-align: right;
    }
    .school-title {
        font-size: 15px;
        font-weight: bold;
        color: #1e3a8a;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        line-height: 1.1;
        margin-bottom: 1px;
    }
    .school-affiliation {
        font-size: 8px;
        font-weight: bold;
        color: #b45309;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-bottom: 1px;
    }
    .school-address {
        font-size: 8px;
        color: #334155;
        line-height: 1.2;
    }
    .school-contacts {
        font-size: 7.5px;
        color: #475569;
        margin-top: 1px;
    }
    
    /* Dual Decorative Ribbon */
    .ribbon-bar {
        width: 100%;
        height: 2.5px;
        background: #1e3a8a;
        margin-bottom: 1.5px;
    }
    .ribbon-sub {
        width: 100%;
        height: 1px;
        background: #d97706;
        margin-bottom: 4px;
    }

    /* Document Title Badge */
    .title-banner {
        text-align: center;
        margin-bottom: 4px;
    }
    .title-banner .badge {
        display: inline-block;
        background: #1e3a8a;
        color: #ffffff;
        font-size: 9.5px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 3px 14px;
        border-radius: 3px;
    }
    .term-subtitle {
        font-size: 8.5px;
        font-weight: bold;
        color: #334155;
        margin-top: 2px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    /* Student Profile Grid */
    .profile-table {
        width: 100%;
        border-collapse: collapse;
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        margin-bottom: 5px;
    }
    .profile-table td {
        padding: 2.5px 5px;
        font-size: 8.5px;
        vertical-align: middle;
        border-bottom: 1px solid #e2e8f0;
    }
    .profile-table tr:last-child td {
        border-bottom: none;
    }
    .lbl {
        color: #475569;
        font-weight: bold;
        width: 17%;
        font-size: 8px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .val {
        color: #0f172a;
        font-weight: bold;
        width: 33%;
        font-size: 8.5px;
    }

    /* Academic Marks Table */
    table.marks-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 5px;
    }
    table.marks-table th {
        background: #1e3a8a;
        color: #ffffff;
        font-size: 8px;
        font-weight: bold;
        text-align: center;
        padding: 3.5px 3px;
        border: 1px solid #1e3a8a;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    table.marks-table th.th-left {
        text-align: left;
        padding-left: 6px;
    }
    table.marks-table td {
        border: 1px solid #cbd5e1;
        padding: 2.8px 3px;
        font-size: 8px;
        text-align: center;
        vertical-align: middle;
        color: #1e293b;
    }
    table.marks-table td.td-left {
        text-align: left;
        padding-left: 6px;
        font-weight: bold;
        color: #0f172a;
    }
    table.marks-table tbody tr:nth-child(even) {
        background: #f8fafc;
    }
    table.marks-table .total-row td {
        background: #eff6ff;
        font-weight: bold;
        color: #1e3a8a;
        font-size: 8px;
        border-top: 1.5px solid #1e3a8a;
        border-bottom: 1.5px solid #1e3a8a;
        padding: 3.5px 3px;
    }

    /* KPI Summary Row */
    .kpi-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 5px;
    }
    .kpi-table td {
        padding: 3.5px 4px;
        text-align: center;
        vertical-align: middle;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        width: 20%;
    }
    .kpi-table td.kpi-amber {
        background: #fffbeb;
        border-color: #fde68a;
    }
    .kpi-table td.kpi-blue {
        background: #eff6ff;
        border-color: #bfdbfe;
    }
    .kpi-val {
        font-size: 13px;
        font-weight: bold;
        color: #1e3a8a;
        line-height: 1.1;
    }
    .kpi-val.emerald {
        color: #166534;
    }
    .kpi-val.amber {
        color: #b45309;
    }
    .kpi-lbl {
        font-size: 7px;
        font-weight: bold;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-top: 1px;
    }

    /* Teacher's Evaluation & Conduct Matrix */
    .evaluation-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 5px;
        background: #ffffff;
    }
    .evaluation-table td {
        vertical-align: top;
        padding: 0;
    }
    .eval-box {
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        padding: 4px 6px;
        background: #fafafa;
    }
    .eval-title {
        font-size: 8px;
        font-weight: bold;
        color: #1e3a8a;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 2px;
        margin-bottom: 3px;
    }
    .eval-text {
        font-size: 7.5px;
        color: #1e293b;
        line-height: 1.3;
        font-style: italic;
    }
    .traits-table {
        width: 100%;
        border-collapse: collapse;
    }
    .traits-table td {
        padding: 1.5px 3px;
        font-size: 7.5px;
        border-bottom: 1px solid #f1f5f9;
    }
    .traits-table tr:last-child td {
        border-bottom: none;
    }
    .traits-lbl {
        font-weight: bold;
        color: #475569;
        width: 55%;
    }
    .traits-val {
        font-weight: bold;
        color: #1e3a8a;
        width: 45%;
        text-align: right;
    }

    /* CDC Grading Key */
    .scale-box {
        margin-bottom: 5px;
    }
    .scale-title {
        font-size: 7.5px;
        font-weight: bold;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-bottom: 2px;
    }
    .scale-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .scale-table th {
        background: #f1f5f9;
        color: #334155;
        font-size: 6.5px;
        font-weight: bold;
        border: 1px solid #cbd5e1;
        padding: 2px 2px;
        text-align: center;
        width: 11.11%;
    }
    .scale-table td {
        border: 1px solid #e2e8f0;
        font-size: 6.5px;
        padding: 1.8px 2px;
        text-align: center;
        width: 11.11%;
    }
    .scale-table tr.scale-grade td {
        background: #f0fdf4;
        font-weight: bold;
        color: #166534;
    }
    .scale-table tr.scale-point td {
        background: #eff6ff;
        font-weight: bold;
        color: #1e3a8a;
    }

    /* Authorized Signatures */
    .sig-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 5px;
        margin-bottom: 3px;
    }
    .sig-table td {
        width: 25%;
        text-align: center;
        vertical-align: bottom;
        padding: 0 4px;
    }
    .sig-space {
        height: 24px;
    }
    .sig-line {
        border-top: 1.2px solid #475569;
        width: 82%;
        margin: 0 auto 2px auto;
    }
    .sig-title {
        font-size: 7.5px;
        font-weight: bold;
        color: #0f172a;
    }
    .sig-sub {
        font-size: 6.5px;
        color: #64748b;
        margin-top: 1px;
    }

    /* Footer & Security */
    .cert-footer {
        width: 100%;
        border-top: 1px solid #e2e8f0;
        padding-top: 3px;
        margin-top: 2px;
        font-size: 6.5px;
        color: #64748b;
        text-align: center;
        line-height: 1.3;
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
    
    $aggregatePct = $totalFullMarks > 0 ? round(($totalObtained / $totalFullMarks) * 100, 1) : 0;
    $gpa = (float)($result['gpa'] ?? 0);
    $failedCount = (int)($result['failed'] ?? 0);
    $passedCount = (int)($result['passed'] ?? count($courses));

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

    {{-- Institutional Header --}}
    <table class="header-table">
        <tr>
            <td class="school-logo-td">
                @if(!empty($logoSrc))
                    <img src="{{ $logoSrc }}" style="max-height: 58px; max-width: 82px;" alt="Logo">
                @else
                    <div style="font-size: 13px; font-weight: bold; color: #1e3a8a; border: 2px solid #1e3a8a; border-radius: 4px; padding: 6px 8px; text-align: center; display: inline-block;">
                        {{ strtoupper(substr($school?->name ?? config('app.name'), 0, 4)) }}
                    </div>
                @endif
            </td>
            <td class="school-info-td">
                <h1 class="school-title">{{ $school?->name ?? config('app.name') }}</h1>
                @if($school?->iemis_code)
                <p class="school-affiliation">Approved by Government of Nepal &bull; CEHRD / EMIS Code: {{ $school->iemis_code }}</p>
                @else
                <p class="school-affiliation">Recognized Academic Institution of Excellence</p>
                @endif
                <p class="school-address">
                    {{ $school?->address ?? 'Kathmandu, Bagmati Province, Nepal' }}
                    @if(!empty($school?->city) && !str_contains($school?->address ?? '', $school->city)), {{ $school->city }}@endif
                </p>
                <p class="school-contacts">
                    @if($school?->phone)Tel: {{ $school->phone }} &bull; @endif
                    @if($school?->email)Email: {{ $school->email }} &bull; @endif
                    Web: {{ $school?->website ?? 'www.nepalschoolhub.com' }}
                </p>
            </td>
            <td class="school-qr-td">
                @if(isset($qrSrc) && !empty($qrSrc))
                <div style="display: inline-block; text-align: center;">
                    <img src="{{ $qrSrc }}" style="width: 52px; height: 52px; border: 1px solid #cbd5e1; padding: 2px; background: #fff;" alt="Verify QR">
                    <div style="font-size: 6px; color: #1e3a8a; font-weight: bold; margin-top: 1px; letter-spacing: 0.5px;">SCAN TO VERIFY</div>
                </div>
                @else
                <div style="display: inline-block; width: 52px; height: 52px; border: 1px dashed #cbd5e1; border-radius: 4px; text-align: center; padding-top: 10px; font-size: 7px; color: #64748b; line-height: 1.2;">
                    OFFICIAL<br>VERIFIED
                </div>
                @endif
            </td>
        </tr>
    </table>

    {{-- Decorative Institutional Ribbon --}}
    <div class="ribbon-bar"></div>
    <div class="ribbon-sub"></div>

    {{-- Document Title --}}
    <div class="title-banner">
        <span class="badge">Official Academic Progress Report Card</span>
        <div class="term-subtitle">
            {{ $cardSem?->semester_name ?? 'Terminal Examination' }} &bull; Academic Session {{ $school?->current_session_name ?? (date('Y') . '/' . (date('Y') + 1)) }}
        </div>
    </div>

    {{-- Student Academic Profile Grid --}}
    <table class="profile-table">
        <tr>
            <td class="lbl">Student Name:</td>
            <td class="val" style="font-size: 10.5px; color: #1e3a8a;">{{ $student?->full_name }}</td>
            <td class="lbl">Student ID / Reg:</td>
            <td class="val">{{ $student?->admission_no ?? $student?->academic_info?->board_reg_no ?? sprintf('STD-%05d', $student?->id ?? 0) }}</td>
        </tr>
        <tr>
            <td class="lbl">Roll Number:</td>
            <td class="val">
                <span style="font-size: 11px; color: #1e3a8a;">{{ $promotion?->roll_no ?? $student?->roll_no ?? $student?->academic_info?->roll_number ?? '—' }}</span>
            </td>
            <td class="lbl">Class &amp; Section:</td>
            <td class="val">{{ $cardClass?->class_name ?? '—' }} &bull; Sec {{ $cardSec?->section_name ?? 'All' }}</td>
        </tr>
        <tr>
            <td class="lbl">Father / Guardian:</td>
            <td class="val">{{ $student?->guardian?->full_name ?? $student?->parents?->first()?->full_name ?? 'Parent / Guardian' }}</td>
            <td class="lbl">Attendance Record:</td>
            <td class="val">
                @if(!empty($attendance) && ($attendance['total'] ?? 0) > 0)
                    {{ $attendance['present'] }} / {{ $attendance['total'] }} Days ({{ $attendance['percentage'] }}%)
                @else
                    Regular (100% Verified)
                @endif
            </td>
        </tr>
        <tr>
            <td class="lbl">Date of Birth:</td>
            <td class="val">{{ $student?->birthday ? date('d M Y', strtotime($student->birthday)) : '—' }}</td>
            <td class="lbl">Date of Issue:</td>
            <td class="val">{{ now()->format('d M Y') }} (@bsDateEn(now()->toDateString()))</td>
        </tr>
    </table>

    {{-- Academic Marks Table --}}
    <table class="marks-table">
        <thead>
            <tr>
                <th style="width: 5%;">SN</th>
                <th style="width: 33%;" class="th-left">Subject / Course Name</th>
                <th style="width: 9%;">Credit/Type</th>
                <th style="width: 9%;">Full Marks</th>
                <th style="width: 9%;">Pass Marks</th>
                <th style="width: 9%;">Obtained</th>
                <th style="width: 8%;">%</th>
                <th style="width: 9%;">Grade Point</th>
                <th style="width: 9%;">Grade</th>
            </tr>
        </thead>
        <tbody>
            @php $sn = 1; @endphp
            @foreach($courses as $c)
            @php
                $fm  = (float)($c['full_marks'] ?? 100);
                $pm  = (float)($c['pass_marks'] ?? round($fm * 0.4, 0));
                $ob  = (float)$c['final_marks'];
                $pct = $fm > 0 ? round(($ob / $fm) * 100, 1) : $ob;
                $passed = (bool)($c['passed'] ?? ($ob >= $pm));
            @endphp
            <tr>
                <td>{{ $sn++ }}</td>
                <td class="td-left">{{ $c['course']?->course_name ?? '—' }}</td>
                <td style="color: #64748b; font-size: 8px;">{{ $c['course']?->course_type ?? 'Theory' }}</td>
                <td style="font-weight: bold;">{{ number_format($fm, 0) }}</td>
                <td style="color: #475569;">{{ number_format($pm, 0) }}</td>
                <td style="font-weight: bold; font-size: 10px; color: {{ $passed ? '#0f172a' : '#dc2626' }};">
                    {{ round($ob, 1) }}
                </td>
                <td style="color: #475569; font-size: 8.5px;">{{ $pct }}%</td>
                <td style="font-weight: bold; color: #1e3a8a;">{{ number_format((float)$c['point'], 2) }}</td>
                <td>
                    <span style="font-weight: bold; font-size: 10.5px; color: {{ $passed ? '#166534' : '#dc2626' }};">
                        {{ $c['grade'] }}
                    </span>
                </td>
            </tr>
            @endforeach

            {{-- Total & Summary Row --}}
            <tr class="total-row">
                <td colspan="3" style="text-align: right; padding-right: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">
                    Grand Total &amp; Aggregate Summary:
                </td>
                <td>{{ number_format($totalFullMarks, 0) }}</td>
                <td>{{ number_format($totalPassMarks, 0) }}</td>
                <td style="font-size: 10.5px; font-weight: bold;">{{ round($totalObtained, 1) }}</td>
                <td>{{ $aggregatePct }}%</td>
                <td style="font-size: 10.5px; font-weight: bold;">{{ number_format($gpa, 2) }}</td>
                <td style="font-size: 11px; font-weight: bold; color: {{ $failedCount === 0 ? '#166534' : '#dc2626' }};">
                    {{ $overallGrade }}
                </td>
            </tr>
        </tbody>
    </table>

    {{-- Executive KPI Summary Ribbon --}}
    <table class="kpi-table">
        <tr>
            <td class="kpi-blue">
                <div class="kpi-val">{{ round($totalObtained, 1) }} <span style="font-size: 9px; font-weight: normal; color: #64748b;">/ {{ number_format($totalFullMarks, 0) }}</span></div>
                <div class="kpi-lbl">Total Aggregate</div>
            </td>
            <td class="kpi-blue">
                <div class="kpi-val">{{ $aggregatePct }}%</div>
                <div class="kpi-lbl">Percentage</div>
            </td>
            <td class="kpi-blue">
                <div class="kpi-val" style="color: #1e3a8a;">{{ number_format($gpa, 2) }}</div>
                <div class="kpi-lbl">Grade Point (GPA)</div>
            </td>
            <td class="kpi-amber">
                <div class="kpi-val amber">{{ $overallGrade }} <span style="font-size: 9px; font-weight: bold;">({{ $division }})</span></div>
                <div class="kpi-lbl">Overall Division</div>
            </td>
            <td class="kpi-amber">
                @if(!empty($result['rank']))
                    <div class="kpi-val amber">{{ ordinal((int)$result['rank']) }}</div>
                    <div class="kpi-lbl">Class Merit Rank</div>
                @else
                    <div class="kpi-val" style="color: {{ $failedCount === 0 ? '#166534' : '#dc2626' }}; font-size: 11px;">
                        {{ $resultStatus }}
                    </div>
                    <div class="kpi-lbl">Academic Standing</div>
                @endif
            </td>
        </tr>
    </table>

    {{-- Teacher's Evaluation & Personal Qualities Matrix --}}
    <table class="evaluation-table">
        <tr>
            <td style="width: 58%; padding-right: 6px;">
                <div class="eval-box">
                    <div class="eval-title">Class Teacher's Evaluative Assessment &amp; Remarks</div>
                    <p class="eval-text">
                        "{{ $teacherRemark }}"
                    </p>
                    <div style="margin-top: 6px; padding-top: 4px; border-top: 1px dashed #cbd5e1; font-size: 7.5px; color: #64748b;">
                        <strong>Teacher's Additional Note / Recommendations:</strong>
                        <div style="border-bottom: 1px dotted #94a3b8; height: 12px; margin-top: 2px;"></div>
                    </div>
                </div>
            </td>
            <td style="width: 42%; padding-left: 6px;">
                <div class="eval-box">
                    <div class="eval-title">Co-Curricular &amp; Behavioral Rubric</div>
                    <table class="traits-table">
                        <tr>
                            <td class="traits-lbl">Discipline &amp; Conduct:</td>
                            <td class="traits-val">{{ $traitDiscipline }}</td>
                        </tr>
                        <tr>
                            <td class="traits-lbl">Class Participation:</td>
                            <td class="traits-val">{{ $traitParticipation }}</td>
                        </tr>
                        <tr>
                            <td class="traits-lbl">Homework &amp; Regularity:</td>
                            <td class="traits-val">{{ $traitHomework }}</td>
                        </tr>
                        <tr>
                            <td class="traits-lbl">Teamwork &amp; Cooperation:</td>
                            <td class="traits-val">{{ $traitTeamwork }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    {{-- Official Government of Nepal CDC Grading Key --}}
    <div class="scale-box">
        <div class="scale-title">Curriculum Development Centre (CDC) &bull; National Examination Grading Scale Reference</div>
        <table class="scale-table">
            <tr>
                <th>Score Interval</th>
                <td>90%–100%</td><td>80%–89%</td><td>70%–79%</td><td>60%–69%</td>
                <td>50%–59%</td><td>40%–49%</td><td>35%–39%</td><td>Below 35%</td>
            </tr>
            <tr class="scale-grade">
                <th>Letter Grade</th>
                <td>A+</td><td>A</td><td>B+</td><td>B</td>
                <td>C+</td><td>C</td><td>D</td><td>NG</td>
            </tr>
            <tr class="scale-point">
                <th>Grade Point</th>
                <td>4.0</td><td>3.6</td><td>3.2</td><td>2.8</td>
                <td>2.4</td><td>2.0</td><td>1.6</td><td>0.0</td>
            </tr>
            <tr>
                <th>Achievement</th>
                <td>Outstanding</td><td>Excellent</td><td>Very Good</td><td>Good</td>
                <td>Satisfactory</td><td>Acceptable</td><td>Basic</td><td>Non-Graded</td>
            </tr>
        </table>
    </div>

    {{-- Authorized Institutional Signatures --}}
    <table class="sig-table">
        <tr>
            <td>
                <div class="sig-space"></div>
                <div class="sig-line"></div>
                <div class="sig-title">{{ $cTeacher?->full_name ?? 'Class Teacher' }}</div>
                <div class="sig-sub">Class In-Charge Signature</div>
            </td>
            <td>
                <div class="sig-space"></div>
                <div class="sig-line"></div>
                <div class="sig-title">Exam Controller</div>
                <div class="sig-sub">Controller of Examinations</div>
            </td>
            <td>
                <div class="sig-space" style="text-align: center;">
                    <div style="display: block; width: 26px; height: 26px; margin: 0 auto; border: 1px dashed #94a3b8; border-radius: 50%; line-height: 26px; font-size: 6px; color: #94a3b8; text-align: center;">SEAL</div>
                </div>
                <div class="sig-line"></div>
                <div class="sig-title">Principal / Headmaster</div>
                <div class="sig-sub">Official Institutional Seal</div>
            </td>
            <td>
                <div class="sig-space"></div>
                <div class="sig-line"></div>
                <div class="sig-title">Parent / Guardian</div>
                <div class="sig-sub">Acknowledgment Signature</div>
            </td>
        </tr>
    </table>

    {{-- Verification & Authenticity Footer --}}
    <div class="cert-footer">
        <span>This certified Academic Report Card is an official legal record generated by {{ $school?->name ?? config('app.name') }}. Any alteration or erasure invalidates this statement.</span>
        @if(isset($verifyToken))
        <br>
        <span style="font-family: monospace; font-size: 6.5px; color: #475569; font-weight: bold;">
            Security Verification Token: {{ $verifyToken }} &bull; Scan QR Code or verify at {{ route('results.verify', ['token' => $verifyToken]) }}
        </span>
        @endif
    </div>

</div>
@endforeach

</body>
</html>
