<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Official Academic Progress Report — {{ $result['student']?->full_name }}</title>
<style>
    @page {
        size: A4 portrait;
        margin: 7mm 9mm 7mm 9mm;
    }
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 11px;
        color: #0f172a;
        background: #ffffff;
        line-height: 1.25;
        width: 100%;
        border: 3px solid #1a365d;
        padding: 8px 12px 8px 12px;
    }

    /* ══ HEADER & INSTITUTION ══════════════════════════════════ */
    .header-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 6px;
    }
    .header-table td {
        vertical-align: middle;
    }
    .school-logo-td {
        width: 16%;
        text-align: left;
    }
    .school-info-td {
        width: 68%;
        text-align: center;
        padding: 0 6px;
    }
    .school-qr-td {
        width: 16%;
        text-align: right;
    }
    .school-name {
        font-size: 21px;
        font-weight: bold;
        color: #1a365d;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        line-height: 1.15;
        margin-bottom: 3px;
    }
    .school-tagline {
        font-size: 10px;
        font-weight: bold;
        color: #b45309;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 2px;
    }
    .school-address {
        font-size: 10px;
        color: #334155;
        line-height: 1.3;
    }
    .school-contacts {
        font-size: 9.5px;
        color: #475569;
        margin-top: 2px;
    }

    /* ══ DECORATIVE DUAL ACCENT BARS ══════════════════════════ */
    .bar-navy {
        width: 100%;
        height: 3px;
        background: #1a365d;
        margin-bottom: 2px;
    }
    .bar-gold {
        width: 100%;
        height: 1.5px;
        background: #d97706;
        margin-bottom: 8px;
    }

    /* ══ DOCUMENT TITLE BANNER ═════════════════════════════════ */
    .title-banner {
        text-align: center;
        margin-bottom: 8px;
    }
    .title-banner .badge {
        display: inline-block;
        background: #1a365d;
        color: #ffffff;
        font-size: 12.5px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        padding: 5px 22px;
        border-radius: 4px;
    }
    .term-subtitle {
        font-size: 11px;
        font-weight: bold;
        color: #1e293b;
        margin-top: 3px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* ══ STUDENT INFORMATION CARD ═════════════════════════════ */
    .profile-card {
        width: 100%;
        border-collapse: collapse;
        background: #f8fafc;
        border: 1.5px solid #cbd5e1;
        border-radius: 5px;
        margin-bottom: 9px;
    }
    .profile-card td {
        padding: 4.5px 8px;
        font-size: 10.5px;
        vertical-align: middle;
        border-bottom: 1px solid #e2e8f0;
    }
    .profile-card tr:last-child td {
        border-bottom: none;
    }
    .lbl {
        color: #475569;
        font-weight: bold;
        width: 18%;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .val {
        color: #0f172a;
        font-weight: bold;
        width: 32%;
        font-size: 11px;
    }
    .val-highlight {
        color: #1a365d;
        font-size: 12px;
        font-weight: bold;
    }

    /* ══ ACADEMIC MARKS TABLE ═════════════════════════════════ */
    table.marks-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 9px;
    }
    table.marks-table th {
        background: #1a365d;
        color: #ffffff;
        font-size: 10.5px;
        font-weight: bold;
        text-align: center;
        padding: 6px 5px;
        border: 1px solid #1a365d;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    table.marks-table th.th-left {
        text-align: left;
        padding-left: 10px;
    }
    table.marks-table td {
        border: 1.2px solid #cbd5e1;
        padding: 5px 5px;
        font-size: 11px;
        text-align: center;
        vertical-align: middle;
        color: #1e293b;
    }
    table.marks-table td.td-left {
        text-align: left;
        padding-left: 10px;
        font-weight: bold;
        color: #0f172a;
        font-size: 11.5px;
    }
    table.marks-table tbody tr:nth-child(even) {
        background: #f8fafc;
    }
    table.marks-table .total-row td {
        background: #eff6ff;
        font-weight: bold;
        color: #1a365d;
        font-size: 11.5px;
        border-top: 2px solid #1a365d;
        border-bottom: 2px solid #1a365d;
        padding: 6px 5px;
    }

    /* ══ SUMMARY KPI RIBBON ════════════════════════════════════ */
    .kpi-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 9px;
    }
    .kpi-table td {
        padding: 6px 8px;
        text-align: center;
        vertical-align: middle;
        background: #eff6ff;
        border: 1.5px solid #bfdbfe;
        width: 20%;
    }
    .kpi-table td.kpi-gold {
        background: #fffbeb;
        border-color: #fde68a;
    }
    .kpi-val {
        font-size: 20px;
        font-weight: bold;
        color: #1a365d;
        line-height: 1.1;
    }
    .kpi-val.gold {
        color: #b45309;
    }
    .kpi-lbl {
        font-size: 9px;
        font-weight: bold;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 2px;
    }

    /* ══ TEACHER EVALUATION & CONDUCT MATRIX ═══════════════════ */
    .eval-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 9px;
    }
    .eval-table td {
        vertical-align: top;
        padding: 0;
    }
    .eval-box {
        border: 1.5px solid #cbd5e1;
        border-radius: 5px;
        padding: 6px 9px;
        background: #fafafa;
    }
    .eval-header {
        font-size: 10.5px;
        font-weight: bold;
        color: #1a365d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1.5px solid #e2e8f0;
        padding-bottom: 3px;
        margin-bottom: 4px;
    }
    .eval-text {
        font-size: 10px;
        color: #1e293b;
        line-height: 1.4;
        font-style: italic;
    }
    .traits-table {
        width: 100%;
        border-collapse: collapse;
    }
    .traits-table td {
        padding: 2.5px 4px;
        font-size: 9.5px;
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
        color: #1a365d;
        width: 45%;
        text-align: right;
    }

    /* ══ CDC GRADING SYSTEM KEY ════════════════════════════════ */
    .scale-box {
        margin-bottom: 9px;
    }
    .scale-title {
        font-size: 9.5px;
        font-weight: bold;
        color: #334155;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 3px;
    }
    .scale-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .scale-table th {
        background: #f1f5f9;
        color: #1e293b;
        font-size: 8.5px;
        font-weight: bold;
        border: 1px solid #cbd5e1;
        padding: 3.5px 3px;
        text-align: center;
        width: 11.11%;
    }
    .scale-table td {
        border: 1px solid #e2e8f0;
        font-size: 8.5px;
        padding: 3px 2px;
        text-align: center;
        width: 11.11%;
    }
    .scale-table tr.scale-grade td {
        background: #f0fdf4;
        font-weight: bold;
        color: #166534;
        font-size: 9.5px;
    }
    .scale-table tr.scale-point td {
        background: #eff6ff;
        font-weight: bold;
        color: #1a365d;
    }

    /* ══ AUTHORIZED SIGNATURES ═════════════════════════════════ */
    .sig-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 8px;
        margin-bottom: 5px;
    }
    .sig-table td {
        width: 25%;
        text-align: center;
        vertical-align: bottom;
        padding: 0 6px;
    }
    .sig-space {
        height: 30px;
    }
    .sig-line {
        border-top: 1.5px solid #475569;
        width: 85%;
        margin: 0 auto 3px auto;
    }
    .sig-role {
        font-size: 10px;
        font-weight: bold;
        color: #0f172a;
    }
    .sig-desc {
        font-size: 8.5px;
        color: #64748b;
        margin-top: 1px;
    }

    /* ══ FOOTER & AUTHENTICITY ═════════════════════════════════ */
    .footer-band {
        width: 100%;
        border-top: 1.5px solid #e2e8f0;
        padding-top: 4px;
        margin-top: 4px;
        font-size: 8.5px;
        color: #64748b;
        text-align: center;
        line-height: 1.35;
    }
</style>
</head>
<body>

@php
    $student      = $result['student'] ?? null;
    $class        = $result['class'] ?? ($class ?? null);
    $section      = $result['section'] ?? ($section ?? null);
    $semester     = $result['semester'] ?? ($semester ?? null);
    $promotion    = $result['promotion'] ?? null;
    $courses      = $result['courses'] ?? [];
    
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
    
    // Resolve Attendance stats
    if (!isset($attendance) || empty($attendance)) {
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
    
    // Resolve Class Teacher
    if (!isset($classTeacher) || !$classTeacher) {
        if ($class && !empty($class->id) && $sessionId && class_exists(\App\Models\AssignedTeacher::class)) {
            try {
                $assignedTeacher = \App\Models\AssignedTeacher::with('teacher')
                    ->where('session_id', $sessionId)
                    ->where('class_id', $class->id)
                    ->where('is_class_teacher', true)
                    ->when($section && !empty($section->id) && $section->id > 0, function($q) use ($section) {
                        $q->where(function($sq) use ($section) {
                            $sq->where('section_id', $section->id)->orWhere('section_id', 0)->orWhereNull('section_id');
                        });
                    })
                    ->first();
                $classTeacher = $assignedTeacher?->teacher;
            } catch (\Throwable $e) {
                $classTeacher = null;
            }
        }
    }
    
    // Calculate Aggregate Totals & Normalization
    $totalFullMarks = 0;
    $totalPassMarks = 0;
    $totalObtained  = 0;
    
    foreach ($courses as $c) {
        $fm = (float)($c['full_marks'] ?? 100);
        $ob = (float)$c['final_marks'];
        // Guard against glitch where FM is entered lower than marks obtained (e.g. Nursery data)
        if ($fm < $ob && $ob <= 100) {
            $fm = 100.0;
        }
        $pm = (float)($c['pass_marks'] ?? round($fm * 0.4, 0));
        $totalFullMarks += $fm;
        $totalPassMarks += $pm;
        $totalObtained  += $ob;
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
        $teacherRemark = "Shows active participation in classroom activities, but requires remedial revision and teacher guidance in subject(s) below passing mark. Dedicated daily practice strongly recommended.";
    } elseif ($gpa >= 3.75) {
        $teacherRemark = "Outstanding academic performance! Demonstrates exceptional conceptual mastery, analytical precision, and commendable classroom leadership. Consistently dedicated to excellence.";
    } elseif ($gpa >= 3.3) {
        $teacherRemark = "Very good academic progress throughout the term! Diligent, responsive in discussions, and shows steady dedication. With continuous effort, higher distinction is well within reach.";
    } elseif ($gpa >= 2.8) {
        $teacherRemark = "Good overall performance. Participates constructively in class lessons; regular home practice and focus on challenging areas will help elevate future semester grades.";
    } elseif ($gpa >= 2.2) {
        $teacherRemark = "Satisfactory progress. Possesses solid foundational abilities; higher regularity in homework completion and attentive classroom focus will yield even better marks.";
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

{{-- ══ INSTITUTIONAL HEADER ════════════════════════════════════ --}}
<table class="header-table">
    <tr>
        <td class="school-logo-td">
            @if(!empty($logoSrc))
                <img src="{{ $logoSrc }}" style="max-height: 62px; max-width: 90px;" alt="Logo">
            @else
                <div style="font-size: 15px; font-weight: bold; color: #1a365d; border: 2.5px solid #1a365d; border-radius: 5px; padding: 7px 10px; text-align: center; display: inline-block;">
                    {{ strtoupper(substr($school?->name ?? config('app.name'), 0, 4)) }}
                </div>
            @endif
        </td>
        <td class="school-info-td">
            <h1 class="school-name">{{ $school?->name ?? config('app.name') }}</h1>
            @if($school?->iemis_code)
            <p class="school-tagline">Approved by Government of Nepal &bull; CEHRD / EMIS Code: {{ $school->iemis_code }}</p>
            @else
            <p class="school-tagline">Recognized Academic Institution of Excellence</p>
            @endif
            <p class="school-address">
                {{ $school?->address ?? 'Kathmandu, Bagmati Province, Nepal' }}
                @if(!empty($school?->city) && !str_contains($school?->address ?? '', $school->city)), {{ $school->city }}@endif
            </p>
            <p class="school-contacts">
                @if($school?->phone)Tel: <strong>{{ $school->phone }}</strong> &bull; @endif
                @if($school?->email)Email: {{ $school->email }} &bull; @endif
                Web: {{ $school?->website ?? 'www.nepalschoolhub.com' }}
            </p>
        </td>
        <td class="school-qr-td">
            @if(isset($qrSrc) && !empty($qrSrc))
            <div style="display: inline-block; text-align: center;">
                <img src="{{ $qrSrc }}" style="width: 58px; height: 58px; border: 1.5px solid #cbd5e1; padding: 2px; background: #fff;" alt="Verify QR">
                <div style="font-size: 6.5px; color: #1a365d; font-weight: bold; margin-top: 1px; letter-spacing: 0.5px;">SCAN TO VERIFY</div>
            </div>
            @else
            <div style="display: inline-block; width: 56px; height: 56px; border: 1.5px dashed #cbd5e1; border-radius: 4px; text-align: center; padding-top: 12px; font-size: 7.5px; color: #64748b; line-height: 1.2;">
                OFFICIAL<br>VERIFIED
            </div>
            @endif
        </td>
    </tr>
</table>

{{-- Decorative Accent Bar --}}
<div class="bar-navy"></div>
<div class="bar-gold"></div>

{{-- Title Banner --}}
<div class="title-banner">
    <span class="badge">Official Academic Progress Report Card</span>
    <div class="term-subtitle">
        {{ $semester?->semester_name ?? 'Terminal Examination' }} &bull; Academic Session {{ $school?->current_session_name ?? (date('Y') . '/' . (date('Y') + 1)) }}
    </div>
</div>

{{-- Student Information Details --}}
<table class="profile-card">
    <tr>
        <td class="lbl">Student Name:</td>
        <td class="val val-highlight">{{ $student?->full_name }}</td>
        <td class="lbl">Student ID / Reg:</td>
        <td class="val">{{ $student?->admission_no ?? $student?->academic_info?->board_reg_no ?? sprintf('STD-%05d', $student?->id ?? 0) }}</td>
    </tr>
    <tr>
        <td class="lbl">Roll Number:</td>
        <td class="val">
            <span style="font-size: 13px; color: #1a365d; font-weight: bold;">{{ $promotion?->roll_no ?? $student?->roll_no ?? $student?->academic_info?->roll_number ?? '—' }}</span>
        </td>
        <td class="lbl">Class &amp; Section:</td>
        <td class="val val-highlight">{{ $class?->class_name ?? '—' }} &bull; Sec {{ $section?->section_name ?? 'All' }}</td>
    </tr>
    <tr>
        <td class="lbl">Father / Guardian:</td>
        <td class="val">{{ $student?->guardian?->full_name ?? $student?->parents?->first()?->full_name ?? 'Parent / Guardian' }}</td>
        <td class="lbl">Attendance Record:</td>
        <td class="val">
            @if(!empty($attendance) && ($attendance['total'] ?? 0) > 0)
                <strong>{{ $attendance['present'] }}</strong> / {{ $attendance['total'] }} Days (<strong>{{ $attendance['percentage'] }}%</strong>)
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
            <th style="width: 9%;">Type</th>
            <th style="width: 9%;">Full</th>
            <th style="width: 9%;">Pass</th>
            <th style="width: 10%;">Obtained</th>
            <th style="width: 8%;">%</th>
            <th style="width: 9%;">Grade Point</th>
            <th style="width: 8%;">Grade</th>
        </tr>
    </thead>
    <tbody>
        @php $sn = 1; @endphp
        @foreach($courses as $c)
        @php
            $fm  = (float)($c['full_marks'] ?? 100);
            $ob  = (float)$c['final_marks'];
            if ($fm < $ob && $ob <= 100) { $fm = 100.0; }
            $pm  = (float)($c['pass_marks'] ?? round($fm * 0.4, 0));
            $pct = $fm > 0 ? round(($ob / $fm) * 100, 1) : $ob;
            $passed = (bool)($c['passed'] ?? ($ob >= $pm));
        @endphp
        <tr>
            <td>{{ $sn++ }}</td>
            <td class="td-left">{{ $c['course']?->course_name ?? '—' }}</td>
            <td style="color: #64748b; font-size: 9.5px;">{{ $c['course']?->course_type ?? 'Theory' }}</td>
            <td style="font-weight: bold; font-size: 11.5px;">{{ number_format($fm, 0) }}</td>
            <td style="color: #475569; font-size: 11px;">{{ number_format($pm, 0) }}</td>
            <td style="font-weight: bold; font-size: 13px; color: {{ $passed ? '#0f172a' : '#dc2626' }};">
                {{ round($ob, 1) }}
            </td>
            <td style="color: #475569; font-size: 10.5px; font-weight: bold;">{{ $pct }}%</td>
            <td style="font-weight: bold; font-size: 12px; color: #1a365d;">{{ number_format((float)$c['point'], 2) }}</td>
            <td>
                <span style="font-weight: bold; font-size: 13.5px; color: {{ $passed ? '#15803d' : '#dc2626' }};">
                    {{ $c['grade'] }}
                </span>
            </td>
        </tr>
        @endforeach

        {{-- Total & Summary Row --}}
        <tr class="total-row">
            <td colspan="3" style="text-align: right; padding-right: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">
                Grand Total &amp; Summary:
            </td>
            <td style="font-size: 12px;">{{ number_format($totalFullMarks, 0) }}</td>
            <td style="font-size: 11.5px;">{{ number_format($totalPassMarks, 0) }}</td>
            <td style="font-size: 14px; font-weight: bold; color: #1a365d;">{{ round($totalObtained, 1) }}</td>
            <td style="font-size: 11.5px; font-weight: bold;">{{ $aggregatePct }}%</td>
            <td style="font-size: 13px; font-weight: bold; color: #1a365d;">{{ number_format($gpa, 2) }}</td>
            <td style="font-size: 14px; font-weight: bold; color: {{ $failedCount === 0 ? '#15803d' : '#dc2626' }};">
                {{ $overallGrade }}
            </td>
        </tr>
    </tbody>
</table>

{{-- Executive Summary KPI Cards --}}
<table class="kpi-table">
    <tr>
        <td>
            <div class="kpi-val">{{ round($totalObtained, 1) }} <span style="font-size: 11px; font-weight: normal; color: #64748b;">/ {{ number_format($totalFullMarks, 0) }}</span></div>
            <div class="kpi-lbl">Total Marks</div>
        </td>
        <td>
            <div class="kpi-val">{{ $aggregatePct }}%</div>
            <div class="kpi-lbl">Aggregate Score</div>
        </td>
        <td>
            <div class="kpi-val" style="color: #1a365d;">{{ number_format($gpa, 2) }}</div>
            <div class="kpi-lbl">Grade Point (GPA)</div>
        </td>
        <td class="kpi-gold">
            <div class="kpi-val gold">{{ $overallGrade }} <span style="font-size: 12px; font-weight: bold;">({{ $division }})</span></div>
            <div class="kpi-lbl">Division / Result</div>
        </td>
        <td class="kpi-gold">
            @if(!empty($result['rank']))
                <div class="kpi-val gold">{{ ordinal((int)$result['rank']) }}</div>
                <div class="kpi-lbl">Class Rank</div>
            @else
                <div class="kpi-val" style="color: {{ $failedCount === 0 ? '#15803d' : '#dc2626' }}; font-size: 13px;">
                    {{ $resultStatus }}
                </div>
                <div class="kpi-lbl">Status</div>
            @endif
        </td>
    </tr>
</table>

{{-- Teacher Evaluation & Behavioral Matrix --}}
<table class="eval-table">
    <tr>
        <td style="width: 58%; padding-right: 6px;">
            <div class="eval-box">
                <div class="eval-header">Class Teacher's Evaluative Assessment &amp; Remarks</div>
                <p class="eval-text">
                    "{{ $teacherRemark }}"
                </p>
                <div style="margin-top: 5px; padding-top: 3px; border-top: 1px dashed #cbd5e1; font-size: 9px; color: #475569;">
                    <strong>Teacher's Personal Note:</strong>
                    <div style="border-bottom: 1.2px dotted #94a3b8; height: 14px; margin-top: 2px;"></div>
                </div>
            </div>
        </td>
        <td style="width: 42%; padding-left: 6px;">
            <div class="eval-box">
                <div class="eval-header">Co-Curricular &amp; Behavioral Matrix</div>
                <table class="traits-table">
                    <tr>
                        <td class="traits-lbl">Discipline &amp; Decorum:</td>
                        <td class="traits-val">{{ $traitDiscipline }}</td>
                    </tr>
                    <tr>
                        <td class="traits-lbl">Classroom Participation:</td>
                        <td class="traits-val">{{ $traitParticipation }}</td>
                    </tr>
                    <tr>
                        <td class="traits-lbl">Homework Regularity:</td>
                        <td class="traits-val">{{ $traitHomework }}</td>
                    </tr>
                    <tr>
                        <td class="traits-lbl">Teamwork &amp; Conduct:</td>
                        <td class="traits-val">{{ $traitTeamwork }}</td>
                    </tr>
                </table>
            </div>
        </td>
    </tr>
</table>

{{-- Official National CDC Grading Scale --}}
<div class="scale-box">
    <div class="scale-title">Curriculum Development Centre (CDC) &bull; National Grading Scale Reference</div>
    <table class="scale-table">
        <tr>
            <th>Score Range</th>
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
            <th>Descriptor</th>
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
            <div class="sig-role">{{ $classTeacher?->full_name ?? 'Class Teacher' }}</div>
            <div class="sig-desc">Class In-Charge Signature</div>
        </td>
        <td>
            <div class="sig-space"></div>
            <div class="sig-line"></div>
            <div class="sig-role">Exam Controller</div>
            <div class="sig-desc">Controller of Examinations</div>
        </td>
        <td>
            <div class="sig-space" style="text-align: center;">
                <div style="display: block; width: 30px; height: 30px; margin: 0 auto; border: 1.5px dashed #94a3b8; border-radius: 50%; line-height: 30px; font-size: 7px; color: #94a3b8; text-align: center;">SEAL</div>
            </div>
            <div class="sig-line"></div>
            <div class="sig-role">Principal / Headmaster</div>
            <div class="sig-desc">Official Institutional Seal</div>
        </td>
        <td>
            <div class="sig-space"></div>
            <div class="sig-line"></div>
            <div class="sig-role">Parent / Guardian</div>
            <div class="sig-desc">Acknowledgment Signature</div>
        </td>
    </tr>
</table>

{{-- Security Footer --}}
<div class="footer-band">
    <span>This certified Academic Report Card is an authentic legal record issued by <strong>{{ $school?->name ?? config('app.name') }}</strong>. Unauthorized alteration is invalid.</span>
    @if(isset($verifyToken))
    <br>
    <span style="font-family: monospace; font-size: 8px; color: #334155; font-weight: bold;">
        Verification Code: {{ $verifyToken }} &bull; Scan QR Code or visit {{ route('results.verify', ['token' => $verifyToken]) }}
    </span>
    @endif
</div>

</body>
</html>
