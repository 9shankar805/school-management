<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Academic Report Card — {{ $result['student']?->full_name }}</title>
<style>
/*
 * DomPDF 2.x – A4 portrait – single page target
 *
 * Units: pt throughout (1pt = 1/72 inch; DomPDF is accurate with pt at any dpi).
 * @page margin 8mm = 22.68pt each side.
 * Available content height: 841.89 - 2*22.68 = 796.53pt.
 * .page-wrap adds 2pt border*2 + 5pt pad top + 5pt pad bot = 14pt overhead.
 * Content budget: ~782pt.
 *
 * DomPDF quirks addressed:
 * - No margin collapsing: every margin-bottom is real vertical space.
 * - div inside td adds extra spacing: minimise divs, use direct td content.
 * - line-height:1 on body prevents automatic extra leading.
 * - Background colours on td must be set inline or with explicit background-color.
 */

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
    /* DomPDF: @page margin is unreliable for shifting content.
       Set margins directly on body: 8mm each side.
       Width = 210mm - 8mm left - 8mm right = 194mm */
    margin: 8mm;
    width: 194mm;
}

/* ─── Outer border wrapper ──────────────────────────────── */
/* DomPDF: border on a block-level div is rendered correctly when width:100%.
   Do NOT use overflow:hidden — DomPDF physically clips content, unlike browsers. */
.wrap {
    width: 100%;
    border: 2pt solid #1a365d;
    padding: 5pt 7pt;
}

/* ─── School header ─────────────────────────────────────── */
/* No table-layout:fixed here — the logo/QR columns need to shrink/grow naturally.
   We control widths via percentage on the td's. */
.hdr { width:100%; border-collapse:collapse; margin-bottom:3pt; }
.hdr td { vertical-align:middle; }
.h-logo { width:12%; text-align:left; }
.h-info { width:76%; text-align:center; padding:0 4pt; }
.h-qr   { width:12%; text-align:right; }

.s-name {
    font-size: 17pt;
    font-weight: bold;
    color: #1a365d;
    text-transform: uppercase;
    letter-spacing: 0.4pt;
    line-height: 1.1;
    margin-bottom: 2pt;
}
.s-tag  { font-size:7.5pt; font-weight:bold; color:#b45309; text-transform:uppercase; margin-bottom:1pt; }
.s-addr { font-size:7.5pt; color:#334155; line-height:1.2; }
.s-tel  { font-size:7pt; color:#475569; margin-top:1pt; }

/* ─── Decorative ribbons ────────────────────────────────── */
.r1 { width:100%; height:2pt; background:#1a365d; margin-bottom:1pt; }
.r2 { width:100%; height:1pt; background:#d97706; margin-bottom:3pt; }

/* ─── Document title ────────────────────────────────────── */
.title-wrap { text-align:center; margin-bottom:4pt; }
.title-badge {
    display:inline-block;
    background:#1a365d;
    color:#fff;
    font-size:10.5pt;
    font-weight:bold;
    text-transform:uppercase;
    letter-spacing:0.6pt;
    padding:2.5pt 14pt;
}
.title-sub {
    font-size:8pt;
    font-weight:bold;
    color:#334155;
    margin-top:1.5pt;
    text-transform:uppercase;
    letter-spacing:0.2pt;
}

/* ─── Student info panel ────────────────────────────────── */
.info {
    width:100%;
    border-collapse:collapse;
    border:1pt solid #94a3b8;
    margin-bottom:4pt;
}
.info td {
    padding:2.5pt 4pt;
    font-size:8.5pt;
    vertical-align:middle;
    border-bottom:0.5pt solid #e2e8f0;
    border-right:0.5pt solid #e2e8f0;
    line-height:1.2;
}
.info tr:last-child td { border-bottom:none; }
.info td:last-child    { border-right:none; }
.il { width:15%; font-size:7pt; font-weight:bold; color:#475569; text-transform:uppercase; background-color:#f1f5f9; }
.iv { width:35%; font-size:8.5pt; font-weight:bold; color:#0f172a; }

/* ─── Marks table ───────────────────────────────────────── */
.mt {
    width:100%;
    border-collapse:collapse;
    table-layout:fixed;
    margin-bottom:4pt;
    border:1pt solid #1a365d;
}
.mt th {
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
.mt th.tl { text-align:left; padding-left:4pt; }
.mt td {
    border:0.5pt solid #cbd5e1;
    padding:3pt 2pt;
    font-size:8.5pt;
    text-align:center;
    vertical-align:middle;
    color:#1e293b;
    line-height:1;
}
.mt td.tl { text-align:left; padding-left:4pt; font-weight:bold; color:#0f172a; }
.mt tbody tr.odd  td { background-color:#ffffff; }
.mt tbody tr.even td { background-color:#f8fafc; }
.mt .tr td {
    background-color:#dbeafe;
    font-weight:bold;
    color:#1a365d;
    font-size:8pt;
    border-top:1.5pt solid #1a365d;
    padding:3.5pt 2pt;
}

/* ─── KPI summary ───────────────────────────────────────── */
.kpi {
    width:100%;
    border-collapse:collapse;
    table-layout:fixed;
    margin-bottom:4pt;
    border:1pt solid #93c5fd;
}
.kpi td {
    width:20%;
    padding:4pt 3pt;
    text-align:center;
    vertical-align:middle;
    background-color:#eff6ff;
    border-right:0.5pt solid #93c5fd;
    line-height:1.1;
}
.kpi td:last-child { border-right:none; }
.kpi td.a { background-color:#fffbeb; border-right-color:#fcd34d; }
.kv { font-size:14pt; font-weight:bold; color:#1a365d; }
.kv.g { color:#166534; }
.kv.o { color:#b45309; }
.kv.r { color:#dc2626; }
.kl { font-size:6.5pt; font-weight:bold; color:#475569; text-transform:uppercase; letter-spacing:0.1pt; margin-top:1pt; }

/* ─── Evaluation + conduct (side by side) ───────────────── */
.ev { width:100%; border-collapse:collapse; margin-bottom:4pt; }
.ev td { vertical-align:top; padding:0; }
.eb { border:1pt solid #cbd5e1; padding:3.5pt 5pt; background-color:#fafafa; }
.eh {
    font-size:7.5pt; font-weight:bold; color:#1a365d;
    text-transform:uppercase; letter-spacing:0.1pt;
    border-bottom:0.5pt solid #e2e8f0;
    padding-bottom:2pt; margin-bottom:2.5pt; line-height:1;
}
.er { font-size:7.5pt; color:#1e293b; line-height:1.35; font-style:italic; margin-bottom:4pt; }
.en { font-size:7pt; font-weight:bold; color:#64748b; border-top:0.5pt dashed #cbd5e1; padding-top:2pt; margin-top:2pt; line-height:1; }
.el { border-bottom:0.5pt dotted #94a3b8; height:11pt; margin-top:1.5pt; }

.ct { width:100%; border-collapse:collapse; }
.ct td { padding:1.5pt 2pt; font-size:7.5pt; border-bottom:0.5pt solid #f1f5f9; line-height:1.1; }
.ct tr:last-child td { border-bottom:none; }
.ctl { font-weight:bold; color:#475569; width:58%; }
.ctv { font-weight:bold; color:#1a365d; width:42%; text-align:right; }

/* ─── CDC grading scale ─────────────────────────────────── */
.sc-wrap { margin-bottom:4pt; }
.sc-ttl { font-size:7pt; font-weight:bold; color:#475569; text-transform:uppercase; letter-spacing:0.1pt; margin-bottom:1.5pt; line-height:1; }
.sc {
    width:100%;
    border-collapse:collapse;
    table-layout:fixed;
    border:1pt solid #cbd5e1;
}
.sc th { background-color:#f1f5f9; color:#334155; font-size:6.5pt; font-weight:bold; border:0.5pt solid #cbd5e1; padding:2pt; text-align:center; line-height:1.2; }
.sc td { border:0.5pt solid #e2e8f0; font-size:6.5pt; padding:2pt; text-align:center; line-height:1.2; }
.sc .sg td { background-color:#f0fdf4; font-weight:bold; color:#166534; }
.sc .sp td { background-color:#eff6ff; font-weight:bold; color:#1a365d; }

/* ─── Signatures ────────────────────────────────────────── */
.sg-tbl { width:100%; border-collapse:collapse; table-layout:fixed; margin-top:4pt; margin-bottom:3pt; }
.sg-tbl td { width:25%; text-align:center; vertical-align:bottom; padding:0 4pt; }
.ss { height:24pt; }
.sl { border-top:0.8pt solid #475569; width:80%; margin:0 auto 1.5pt auto; }
.sn { font-size:7.5pt; font-weight:bold; color:#0f172a; line-height:1.1; }
.sr { font-size:6.5pt; color:#64748b; margin-top:0.5pt; line-height:1.1; }

/* ─── Footer ────────────────────────────────────────────── */
.ft {
    border-top:0.5pt solid #e2e8f0;
    padding-top:2pt; margin-top:2pt;
    font-size:6.5pt; color:#64748b; text-align:center; line-height:1.3;
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
            $school = $student && isset($student->school_id)
                ? \App\Models\School::find($student->school_id) : null;
            $school = $school ?? config('tenant.school') ?? \App\Models\School::first();
        } catch (\Throwable $e) { $school = null; }
    }

    $sessionId = $promotion?->session_id ?? session('browse_session_id') ?? 1;

    // Attendance
    if (!isset($attendance) || empty($attendance)) {
        if ($student && !empty($student->id) && $sessionId && class_exists(\App\Models\Attendance::class)) {
            try { $attendance = \App\Models\Attendance::statsFor($student->id, $sessionId); }
            catch (\Throwable $e) { $attendance = ['total'=>0,'present'=>0,'absent'=>0,'percentage'=>100]; }
        } else {
            $attendance = ['total'=>0,'present'=>0,'absent'=>0,'percentage'=>100];
        }
    }

    // Class Teacher
    if (!isset($classTeacher) || !$classTeacher) {
        if ($class && !empty($class->id) && $sessionId && class_exists(\App\Models\AssignedTeacher::class)) {
            try {
                $at = \App\Models\AssignedTeacher::with('teacher')
                    ->where('session_id', $sessionId)->where('class_id', $class->id)
                    ->where('is_class_teacher', true)
                    ->when($section && !empty($section->id) && $section->id > 0, fn($q) =>
                        $q->where(fn($sq) =>
                            $sq->where('section_id', $section->id)
                               ->orWhere('section_id', 0)->orWhereNull('section_id')
                        )
                    )->first();
                $classTeacher = $at?->teacher;
            } catch (\Throwable $e) { $classTeacher = null; }
        }
    }

    // Aggregate totals
    $totalFullMarks = 0; $totalPassMarks = 0;
    $totalObtained  = (float)($result['totalMarks'] ?? 0);
    foreach ($courses as $c) {
        $totalFullMarks += (float)($c['full_marks'] ?? 100);
        $totalPassMarks += (float)($c['pass_marks'] ?? round((float)($c['full_marks'] ?? 100) * 0.4, 0));
    }
    $aggregatePct = $totalFullMarks > 0 ? min(round(($totalObtained / $totalFullMarks) * 100, 1), 100.0) : 0;
    $gpa         = (float)($result['gpa'] ?? 0);
    $failedCount = (int)($result['failed'] ?? 0);
    $missingCount = (int)($result['missingCount'] ?? 0);
    $missingNames = $result['missingNames'] ?? [];
    $resultStatusComputed = $result['resultStatus'] ?? ($failedCount > 0 ? 'FAIL' : 'PASS');
    // When marks are missing, GPA is not meaningful
    $displayGpa = $missingCount > 0 ? '' : number_format($gpa, 2);

    // Show CDC scale only when subject count is 12 or fewer
    $showCdcScale = count($courses) <= 12;

    // Per school policy: show PASS only when all marks entered and all passed.
    // Missing marks, failed subjects, or absent → overall result is blank.
    if ($missingCount > 0)      { $overallGrade='';   $division='';  $resultStatus=''; }
    elseif ($failedCount > 0)   { $overallGrade='';   $division='';  $resultStatus=''; }
    elseif ($gpa >= 3.6)       { $overallGrade='A+'; $division='Distinction';     $resultStatus='PASS'; }
    elseif ($gpa >= 3.2)       { $overallGrade='A';  $division='First Division';  $resultStatus='PASS'; }
    elseif ($gpa >= 2.8)       { $overallGrade='B+'; $division='First Division';  $resultStatus='PASS'; }
    elseif ($gpa >= 2.4)       { $overallGrade='B';  $division='Second Division'; $resultStatus='PASS'; }
    elseif ($gpa >= 2.0)       { $overallGrade='C+'; $division='Third Division';  $resultStatus='PASS'; }
    else                       { $overallGrade='C';  $division='Pass';            $resultStatus='PASS'; }

    if ($failedCount > 0) {
        $remark = "Demonstrates potential but needs remedial attention in subject(s) below pass marks. Structured revision and subject teacher guidance strongly recommended.";
        $trD='Good'; $trP='Developing'; $trH='Needs Regularity'; $trT='Cooperative';
    } elseif ($gpa >= 3.75) {
        $remark = "Outstanding achievement. Exceptional conceptual mastery, analytical precision, and commendable classroom leadership throughout the term.";
        $trD='Exemplary'; $trP='Active & Inquisitive'; $trH='Prompt & Thorough'; $trT='Commendable Leader';
    } elseif ($gpa >= 3.3) {
        $remark = "Very good performance. Consistently attentive, diligent, and demonstrates commendable analytical thinking and positive classroom conduct.";
        $trD='Very Good'; $trP='Constructive'; $trH='Regular'; $trT='Helpful & Polite';
    } elseif ($gpa >= 2.8) {
        $remark = "Good performance overall. Constructive class participation; regular practice and focus on challenging areas will help achieve higher distinction.";
        $trD='Very Good'; $trP='Constructive'; $trH='Regular'; $trT='Helpful & Polite';
    } elseif ($gpa >= 2.2) {
        $remark = "Satisfactory progress. Solid foundational ability; higher consistency in homework and attentive class focus will yield better results next term.";
        $trD='Good'; $trP='Satisfactory'; $trH='Fair'; $trT='Friendly';
    } else {
        $remark = "Basic pass. Regular attendance, a structured homework schedule, and targeted revision in core subjects are encouraged for the upcoming term.";
        $trD='Good'; $trP='Satisfactory'; $trH='Fair'; $trT='Friendly';
    }
@endphp

<div class="wrap">

{{-- ── Header ── --}}
<table class="hdr">
<tr>
  <td class="h-logo">
    @if(!empty($logoSrc))
      <img src="{{ $logoSrc }}" style="max-height:60pt;max-width:76pt;" alt="Logo">
    @else
      <div style="width:52pt;height:52pt;border:2pt solid #1a365d;font-size:12pt;font-weight:bold;color:#1a365d;text-align:center;padding-top:14pt;">{{ strtoupper(substr($school?->name??'S',0,3)) }}</div>
    @endif
  </td>
  <td class="h-info">
    <div class="s-name">{{ $school?->name ?? config('app.name') }}</div>
    @if($school?->iemis_code)
    <div class="s-tag">Gov. of Nepal Approved &bull; EMIS: {{ $school->iemis_code }}</div>
    @else
    <div class="s-tag">Recognised Academic Institution &bull; Nepal</div>
    @endif
    <div class="s-addr">{{ $school?->address ?? 'Kathmandu, Bagmati Province, Nepal' }}</div>
    <div class="s-tel">
      @if($school?->phone)Tel: {{ $school->phone }} @endif
      @if($school?->email) | Email: {{ $school->email }} @endif
      | Web: {{ $school?->website ?? 'www.nepalschoolhub.com' }}
    </div>
  </td>
  <td class="h-qr">
    @if(!empty($qrSrc))
      <div style="text-align:center;"><img src="{{ $qrSrc }}" style="width:52pt;height:52pt;border:1pt solid #cbd5e1;padding:1.5pt;background:#fff;" alt="QR"><div style="font-size:5.5pt;color:#1a365d;font-weight:bold;margin-top:1pt;letter-spacing:0.2pt;">SCAN TO VERIFY</div></div>
    @else
      <div style="width:52pt;height:52pt;border:1pt dashed #cbd5e1;text-align:center;padding-top:15pt;font-size:6.5pt;color:#64748b;line-height:1.3;">OFFICIAL<br>VERIFIED</div>
    @endif
  </td>
</tr>
</table>

<div class="r1"></div>
<div class="r2"></div>

<div class="title-wrap">
  <div class="title-badge">Official Academic Progress Report Card</div>
  <div class="title-sub">{{ $semester?->semester_name ?? 'Terminal Examination' }} &bull; Academic Session 2083</div>
</div>

{{-- ── Student info ── --}}
<table class="info">
  <tr>
    <td class="il">Student Name</td>
    <td class="iv" style="font-size:9.5pt;color:#1a365d;" colspan="3">{{ $student?->full_name ?? '—' }}</td>
  </tr>
  <tr>
    <td class="il">Roll No.</td>
    <td class="iv" style="color:#1a365d;">{{ $promotion?->roll_no ?? $student?->roll_no ?? '—' }}</td>
    <td class="il">Class &amp; Section</td>
    <td class="iv">{{ $class?->class_name ?? '—' }} &bull; Sec {{ $section?->section_name ?? 'All' }}</td>
  </tr>
  <tr>
    <td class="il">Student ID</td>
    <td class="iv" colspan="3">{{ $student?->admission_no ?? sprintf('STD-%05d',$student?->id??0) }}</td>
  </tr>
  <tr>
    <td class="il">Date of Issue</td>
    <td class="iv" colspan="3">{{ now()->format('d M Y') }} <span style="font-size:7.5pt;font-weight:normal;color:#475569;">(@bsDateEn(now()->toDateString()))</span></td>
  </tr>
</table>

{{-- ── Marks table ── --}}
<table class="mt">
  <thead>
    <tr>
      <th style="width:4%;">SN</th>
      <th class="tl" style="width:40%;">Subject / Course Name</th>
      <th style="width:10%;">Full<br>Marks</th>
      <th style="width:10%;">Pass<br>Marks</th>
      <th style="width:10%;">Marks<br>Obtained</th>
      <th style="width:8%;">%</th>
      <th style="width:10%;">Grade<br>Point</th>
      <th style="width:8%;">Grade</th>
    </tr>
  </thead>
  <tbody>
    @php $sn=1; @endphp
    @foreach($courses as $idx=>$c)
    @php
      $fm = (float)($c['full_marks']??100);
      $pm = (float)($c['pass_marks']??round($fm*0.4,0));
      $ob = (float)$c['final_marks'];
      // Cap display percentage at 100% — over-marks happen when teachers enter marks
      // exceeding the configured full marks (e.g. 70 obtained on 40 full marks).
      $rawPct = $fm>0 ? round(($ob/$fm)*100,1) : $ob;
      $dispPct = min($rawPct, 100.0);
      $passed = (bool)($c['passed']??($ob>=$pm));
      $rowCls = ($idx%2===0) ? 'odd' : 'even';
      $gc = match(true){
        in_array($c['grade'],['A+','A']) => '#166534',
        in_array($c['grade'],['B+','B']) => '#1e40af',
        in_array($c['grade'],['C+','C']) => '#b45309',
        in_array($c['grade'],['D'])      => '#7c3aed',
        default                          => '#dc2626',
      };
    @endphp
    <tr class="{{ $rowCls }}">
      <td>{{ $sn++ }}</td>
      <td class="tl">{{ $c['course']?->course_name ?? '—' }}</td>
      <td style="font-weight:bold;">{{ number_format($fm,0) }}</td>
      <td style="color:#475569;">{{ number_format($pm,0) }}</td>
      <td style="font-weight:bold;font-size:10pt;color:{{ $passed?'#0f172a':'#dc2626' }};">{{ round($ob,1) }}</td>
      <td style="color:#64748b;font-size:7.5pt;">{{ $dispPct }}%</td>
      <td style="font-weight:bold;color:#1a365d;">{{ number_format((float)$c['point'],2) }}</td>
      <td style="font-weight:bold;font-size:10.5pt;color:{{ $passed?$gc:'#dc2626' }};">{{ $c['grade'] }}</td>
    </tr>
    @endforeach

    <tr class="tr">
      <td colspan="2" style="text-align:right;padding-right:7pt;text-transform:uppercase;letter-spacing:0.2pt;font-size:7.5pt;">Grand Total &amp; Aggregate:</td>
      <td>{{ number_format($totalFullMarks,0) }}</td>
      <td>{{ number_format($totalPassMarks,0) }}</td>
      <td style="font-size:9.5pt;font-weight:bold;">{{ round($totalObtained,1) }}</td>
      <td>{{ $aggregatePct }}%</td>
      <td style="font-size:9.5pt;font-weight:bold;">{{ $displayGpa }}</td>
      <td style="font-size:10.5pt;font-weight:bold;color:{{ $overallGrade ? '#166534' : '#cbd5e1' }};">{{ $overallGrade ?: '' }}</td>
    </tr>
  </tbody>
</table>

{{-- ── KPI band ── --}}
<table class="kpi">
  <tr>
    <td>
      <div class="kv">{{ round($totalObtained,0) }}<span style="font-size:8pt;font-weight:normal;color:#64748b;"> /{{ number_format($totalFullMarks,0) }}</span></div>
      <div class="kl">Total Marks</div>
    </td>
    <td>
      <div class="kv">{{ $aggregatePct }}%</div>
      <div class="kl">Percentage</div>
    </td>
    <td>
      <div class="kv">{{ $displayGpa }}</div>
      <div class="kl">GPA</div>
    </td>
    <td class="a">
      <div class="kv o">{{ $overallGrade }} <span style="font-size:8pt;font-weight:bold;">({{ $division }})</span></div>
      <div class="kl">Division</div>
    </td>
    <td class="a">
      @if(!empty($result['rank']))
        <div class="kv o">{{ ordinal((int)$result['rank']) }}</div>
        <div class="kl">Class Rank</div>
      @else
        <div class="kv {{ $failedCount===0?'g':'r' }}" style="font-size:10pt;">{{ $resultStatus }}</div>
        <div class="kl">Standing</div>
      @endif
    </td>
  </tr>
</table>

{{-- ── CDC grading scale (shown only when content fits on one page) ── --}}
@if($showCdcScale)
<div class="sc-wrap">
  <div class="sc-ttl">CDC National Grading Scale Reference (Nepal Curriculum Development Centre)</div>
  <table class="sc">
    <tr>
      <th>Interval</th>
      <td>90–100%</td><td>80–89%</td><td>70–79%</td><td>60–69%</td>
      <td>50–59%</td><td>40–49%</td><td>35–39%</td><td>&lt;35%</td>
    </tr>
    <tr class="sg">
      <th>Grade</th>
      <td>A+</td><td>A</td><td>B+</td><td>B</td><td>C+</td><td>C</td><td>D</td><td>NG</td>
    </tr>
    <tr class="sp">
      <th>Points</th>
      <td>4.0</td><td>3.6</td><td>3.2</td><td>2.8</td><td>2.4</td><td>2.0</td><td>1.6</td><td>0.0</td>
    </tr>
    <tr>
      <th>Level</th>
      <td>Outstanding</td><td>Excellent</td><td>Very Good</td><td>Good</td>
      <td>Satisfactory</td><td>Acceptable</td><td>Basic</td><td>Non-Graded</td>
    </tr>
  </table>
</div>
@endif

{{-- ── Signatures ── --}}
<table class="sg-tbl">
  <tr>
    <td>
      <div class="ss"></div>
      <div class="sl"></div>
      <div class="sn">{{ $classTeacher?->full_name ?? 'Class Teacher' }}</div>
      <div class="sr">Class In-Charge</div>
    </td>
    <td>
      <div class="ss"></div>
      <div class="sl"></div>
      <div class="sn">Exam Controller</div>
      <div class="sr">Controller of Examinations</div>
    </td>
    <td>
      <div class="ss" style="text-align:center;"><div style="display:block;width:24pt;height:24pt;margin:0 auto;border:1pt dashed #94a3b8;border-radius:50%;font-size:5.5pt;color:#94a3b8;text-align:center;padding-top:6pt;">SEAL</div></div>
      <div class="sl"></div>
      <div class="sn">Principal / Headmaster</div>
      <div class="sr">Official Seal &amp; Signature</div>
    </td>
    <td>
      <div class="ss"></div>
      <div class="sl"></div>
      <div class="sn">Parent / Guardian</div>
      <div class="sr">Acknowledgment Signature</div>
    </td>
  </tr>
</table>

{{-- ── Footer ── --}}
<div class="ft">
  This certified Academic Report Card is an official legal record issued by <strong>{{ $school?->name ?? config('app.name') }}</strong>. Any alteration invalidates this document.
  @if(isset($verifyToken)) <br><span style="font-family:monospace;font-size:6pt;color:#475569;">Token: {{ $verifyToken }} &bull; Verify: {{ route('results.verify',['token'=>$verifyToken]) }}</span>@endif
</div>

</div>{{-- .wrap --}}
</body>
</html>
