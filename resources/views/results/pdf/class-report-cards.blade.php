<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Class Marksheets — {{ $class->class_name }}</title>
<style>
@page { size: A4 portrait; margin: 0; }
* { margin: 0; padding: 0; box-sizing: border-box; }
html, body {
    width: 210mm;
    font-family: "DejaVu Sans", Arial, sans-serif;
    font-size: 10pt;
    color: #1a1a2e;
    background: #ffffff;
}
.sheet-page { width: 210mm; min-height: 297mm; page-break-after: always; background: #ffffff; position: relative; display: block; }
.sheet-page:last-child { page-break-after: avoid; }

/* CORNER ACCENTS */
.corner-tl { position: absolute; top: 0; left: 0; width: 0; height: 0; border-style: solid; border-width: 52px 52px 0 0; border-color: #b8860b transparent transparent transparent; z-index: 10; }
.corner-tr { position: absolute; top: 0; right: 0; width: 0; height: 0; border-style: solid; border-width: 0 52px 52px 0; border-color: transparent #b8860b transparent transparent; z-index: 10; }
.corner-bl { position: absolute; bottom: 0; left: 0; width: 0; height: 0; border-style: solid; border-width: 52px 0 0 52px; border-color: transparent transparent transparent #b8860b; z-index: 10; }
.corner-br { position: absolute; bottom: 0; right: 0; width: 0; height: 0; border-style: solid; border-width: 0 0 52px 52px; border-color: transparent transparent #b8860b transparent; z-index: 10; }

/* BORDERS */
.outer-border { position: absolute; top: 8px; left: 8px; right: 8px; bottom: 8px; border: 2px solid #1a1a2e; z-index: 2; }
.inner-border { position: absolute; top: 12px; left: 12px; right: 12px; bottom: 12px; border: 1px solid #b8860b; z-index: 2; }

/* HEADER */
.header-band { background: #1a1a2e; margin: 18px 18px 0 18px; padding: 16px 20px; position: relative; overflow: hidden; }
.header-band::after { content: ''; position: absolute; top: 0; right: 0; bottom: 0; width: 35%; background: repeating-linear-gradient(-45deg, transparent, transparent 4px, rgba(184,134,11,0.15) 4px, rgba(184,134,11,0.15) 8px); }
.header-table { width: 100%; border-collapse: collapse; }
.school-name { font-size: 19pt; font-weight: bold; color: #f5d87a; text-transform: uppercase; letter-spacing: 1.2px; line-height: 1.15; margin-bottom: 4px; }
.school-sub  { font-size: 9.5pt; color: rgba(255,255,255,0.75); line-height: 1.5; }

/* TITLE */
.title-ribbon { background: #b8860b; margin: 0 18px; padding: 7px 20px; text-align: center; position: relative; }
.title-ribbon::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 6px; background: #1a1a2e; }
.title-ribbon::after  { content: ''; position: absolute; right: 0; top: 0; bottom: 0; width: 6px; background: #1a1a2e; }
.title-ribbon span { font-size: 11.5pt; font-weight: bold; color: #ffffff; letter-spacing: 3.5px; text-transform: uppercase; }

/* INFO CARD */
.info-card { margin: 14px 18px 0 18px; border: 1.5px solid #1a1a2e; border-top: 4px solid #b8860b; }
.info-card-header { background: #f5f0e8; padding: 5px 14px; border-bottom: 1px solid #ddd; font-size: 8pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1.5px; color: #b8860b; }
.info-table { width: 100%; border-collapse: collapse; }
.info-table td { padding: 7px 14px; font-size: 10.5pt; border-bottom: 1px solid #eeeeee; vertical-align: middle; }
.info-table tr:last-child td { border-bottom: none; }
.info-lbl { width: 22%; font-size: 8.5pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; color: #666666; }
.info-val { width: 28%; font-size: 11pt; font-weight: bold; color: #1a1a2e; }
.rank-big { font-size: 22pt; font-weight: bold; color: #b8860b; line-height: 1; }

/* SECTION LABEL */
.sec-label { margin: 14px 18px 6px 18px; display: table; width: calc(100% - 36px); }
.sec-label-text { display: table-cell; font-size: 9pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1.5px; color: #1a1a2e; vertical-align: middle; padding-left: 10px; border-left: 4px solid #b8860b; }
.sec-label-line { display: table-cell; border-bottom: 1px solid #cccccc; vertical-align: bottom; padding-bottom: 2px; }

/* MARKS TABLE */
.marks-wrap { margin: 0 18px; }
.marks-table { width: 100%; border-collapse: collapse; border: 1.5px solid #1a1a2e; font-size: 10.5pt; }
.marks-table thead tr { background: #1a1a2e; }
.marks-table th { color: #f5d87a; padding: 9px 8px; font-size: 9pt; font-weight: bold; text-align: center; border-right: 1px solid rgba(255,255,255,0.15); text-transform: uppercase; letter-spacing: 0.5px; }
.marks-table th:first-child { text-align: left; padding-left: 14px; }
.marks-table th:last-child  { border-right: none; }
.marks-table td { padding: 7.5px 8px; border-bottom: 1px solid #e8e8e8; border-right: 1px solid #e8e8e8; text-align: center; vertical-align: middle; font-size: 10.5pt; }
.marks-table td:first-child { text-align: left; padding-left: 14px; font-weight: 600; }
.marks-table td:last-child  { border-right: none; }
.marks-table tbody tr:nth-child(even) { background: #fafafa; }
.marks-table tbody tr:last-child td { border-bottom: none; }

/* SUMMARY */
.summary-strip { margin: 14px 18px 0 18px; background: #1a1a2e; display: table; width: calc(100% - 36px); }
.sum-cell { display: table-cell; width: 25%; text-align: center; padding: 14px 6px; border-right: 1px solid rgba(184,134,11,0.4); vertical-align: middle; }
.sum-cell:last-child { border-right: none; }
.sum-val { font-size: 24pt; font-weight: bold; color: #f5d87a; line-height: 1; }
.sum-lbl { font-size: 7.5pt; color: rgba(255,255,255,0.65); text-transform: uppercase; letter-spacing: 0.8px; margin-top: 4px; }

/* GRADING KEY */
.grading-wrap { margin: 0 18px; }
.grading-key { width: 100%; border-collapse: collapse; border: 1.5px solid #1a1a2e; font-size: 9.5pt; }
.grading-key .g-interval { background: #1a1a2e; color: #f5d87a; font-size: 8.5pt; font-weight: bold; padding: 6px 5px; text-align: center; border: 1px solid rgba(255,255,255,0.1); text-transform: uppercase; }
.grading-key .hdr { background: #f5f0e8; font-weight: bold; font-size: 8.5pt; color: #1a1a2e; border: 1px solid #cccccc; padding: 6px 4px; text-align: center; }
.grading-key td { border: 1px solid #dddddd; padding: 6px 4px; text-align: center; font-size: 9.5pt; }
.grading-key tr:nth-child(2) td { background: #f7f7f7; font-weight: bold; }
.grading-key tr:nth-child(3) td { background: #f0f0f0; font-weight: bold; }
.grading-key tr:nth-child(4) td { background: #fafafa; font-size: 8.5pt; color: #555; }

/* SIGNATURES */
.sig-wrap { margin: 20px 18px 14px 18px; }
.sig-table { width: 100%; border-collapse: collapse; }
.sig-table td { width: 33.33%; text-align: center; vertical-align: bottom; padding: 0 16px; }
.sig-space { height: 50px; }
.sig-line  { border-top: 1.5px solid #1a1a2e; width: 82%; margin: 0 auto 5px; }
.sig-role  { font-size: 10.5pt; font-weight: bold; color: #1a1a2e; }
.sig-note  { font-size: 8pt; color: #888888; margin-top: 2px; }

/* FOOTER */
.footer-band { background: #f5f0e8; border-top: 2px solid #b8860b; margin: 12px 18px 18px 18px; padding: 8px 14px; text-align: center; font-size: 8.5pt; color: #555555; line-height: 1.7; }
.verify-code { font-family: "DejaVu Sans Mono", monospace; font-size: 7.5pt; color: #888888; }
</style>
</head>
<body>

@foreach($cards as $card)
@php
    $result      = $card['result'];
    $verifyToken = $card['verifyToken'];
    $verifyUrl   = $card['verifyUrl'];
    $qrSrc       = $card['qrSrc'];
    $student     = $result['student'] ?? null;
    $cardSem     = $result['semester'] ?? $semester;
    $courses     = $result['courses'] ?? [];
    $totalFM     = collect($courses)->sum(fn($c) => (float)($c['full_marks'] ?? 100));
    $overallPct  = $totalFM > 0 ? round(($result['totalMarks'] / $totalFM) * 100, 1) : 0;
    $finalResult = ($result['failed'] ?? 0) > 0 ? 'FAIL' : 'PASS';
@endphp
<div class="sheet-page">
    <div class="corner-tl"></div><div class="corner-tr"></div>
    <div class="corner-bl"></div><div class="corner-br"></div>
    <div class="outer-border"></div>
    <div class="inner-border"></div>

    {{-- HEADER --}}
    <div class="header-band">
        <table class="header-table">
            <tr>
                <td style="width:13%; vertical-align:middle;">
                    @if(!empty($logoSrc))
                        <img src="{{ $logoSrc }}" style="max-height:68px; max-width:85px; border:2px solid #b8860b; padding:2px; background:#fff;" alt="Logo">
                    @else
                        <div style="width:68px; height:68px; border-radius:50%; border:2px solid #b8860b; background:rgba(255,255,255,0.1); text-align:center; line-height:68px; font-size:22pt; font-weight:bold; color:#f5d87a;">
                            {{ strtoupper(substr($school?->name ?? 'S', 0, 1)) }}
                        </div>
                    @endif
                </td>
                <td style="width:65%; text-align:center; vertical-align:middle; padding:0 14px;">
                    <div class="school-name">{{ $school?->name ?? config('app.name') }}</div>
                    <div class="school-sub">{{ $school?->address ?? 'Kathmandu, Nepal' }} &mdash; Tel: {{ $school?->phone ?? '' }}</div>
                </td>
                <td style="width:22%; text-align:right; vertical-align:middle;">
                    @if(isset($qrSrc))
                    <div style="display:inline-block; text-align:center;">
                        <img src="{{ $qrSrc }}" style="width:70px; height:70px; border:2px solid #b8860b; padding:2px; background:#fff;" alt="QR">
                        <div style="font-size:6.5pt; color:#f5d87a; font-weight:bold; margin-top:3px; letter-spacing:1.5px;">SCAN TO VERIFY</div>
                    </div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    {{-- TITLE --}}
    <div class="title-ribbon">
        <span>&#10022; &nbsp; Academic Achievement Marksheet / Grade Sheet &nbsp; &#10022;</span>
    </div>

    {{-- STUDENT INFO --}}
    <div class="info-card">
        <div class="info-card-header">&#9670; Student Information</div>
        <table class="info-table">
            <tr>
                <td class="info-lbl">Student Name</td>
                <td class="info-val" style="font-size:13pt;" colspan="3">{{ $student?->full_name ?? '—' }}</td>
            </tr>
            <tr>
                <td class="info-lbl">Academic Term</td>
                <td class="info-val">{{ $cardSem?->semester_name ?? '—' }}</td>
                <td class="info-lbl">Class &amp; Section</td>
                <td class="info-val">{{ $result['class']?->class_name ?? $class?->class_name ?? '—' }} &bull; Sec. {{ $result['section']?->section_name ?? $section?->section_name ?? 'All' }}</td>
            </tr>
            <tr>
                <td class="info-lbl">Roll Number</td>
                <td class="info-val">{{ $result['promotion']?->roll_no ?? $student?->academic_info?->roll_number ?? '—' }}</td>
                <td class="info-lbl">Issue Date</td>
                <td class="info-val">{{ now()->format('d M Y') }} <span style="font-size:8.5pt; font-weight:normal; color:#888;">(@bsDateEn(now()->toDateString()))</span></td>
            </tr>
            <tr>
                <td class="info-lbl">Class Rank</td>
                <td class="info-val" colspan="3">
                    @if($result['rank'])
                        @php $rank = (int)$result['rank']; @endphp
                        <span class="rank-big">{{ ordinal($rank) }}</span>
                        <span style="font-size:9pt; color:#888; font-weight:normal;"> Position in Class</span>
                    @else
                        <span style="font-size:9.5pt; color:#aaa; font-weight:normal;">Not Ranked</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    {{-- MARKS --}}
    <div class="sec-label">
        <div class="sec-label-text">&#9658; Subject-wise Performance</div>
        <div class="sec-label-line"></div>
    </div>
    <div class="marks-wrap">
        <table class="marks-table">
            <thead>
                <tr>
                    <th style="width:32%;">Subject / Course Name</th>
                    <th style="width:12%;">Full Marks</th>
                    <th style="width:11%;">Obtained</th>
                    <th style="width:9%;">Pct.</th>
                    <th style="width:10%;">Grade</th>
                    <th style="width:13%;">Gr. Point</th>
                    <th style="width:13%;">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse($courses as $c)
                @php $fm=(float)($c['full_marks']??100); $ob=(float)$c['final_marks']; $pct=$fm>0?round(($ob/$fm)*100,1):0; @endphp
                <tr>
                    <td>{{ $c['course']?->course_name ?? '—' }}</td>
                    <td>{{ number_format($fm,0) }}</td>
                    <td style="font-weight:bold;">{{ round($ob,1) }}</td>
                    <td>{{ $pct }}%</td>
                    <td style="font-weight:bold; font-size:12pt;">{{ $c['grade'] }}</td>
                    <td style="font-weight:bold;">{{ number_format((float)$c['point'],2) }}</td>
                    <td><span style="font-weight:bold;">{{ $c['passed'] ? '&#10003; PASSED' : '&#10007; RETEST' }}</span></td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center; padding:16px; color:#aaa; font-style:italic;">No marks recorded.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- SUMMARY --}}
    <div class="summary-strip">
        <div class="sum-cell"><div class="sum-val">{{ $result['gpa'] }}</div><div class="sum-lbl">GPA</div></div>
        <div class="sum-cell"><div class="sum-val">{{ round($result['totalMarks'],0) }}</div><div class="sum-lbl">Total Marks</div></div>
        <div class="sum-cell"><div class="sum-val">{{ $overallPct }}%</div><div class="sum-lbl">Percentage</div></div>
        <div class="sum-cell">
            <div class="sum-val" style="{{ ($result['failed']??0)>0?'color:#aaa;':'color:#f5d87a;' }} font-size:20pt; letter-spacing:2px;">{{ $finalResult }}</div>
            <div class="sum-lbl">{{ $result['passed'] }} Pass / {{ $result['failed']??0 }} Fail</div>
        </div>
    </div>

    {{-- GRADING KEY --}}
    <div class="sec-label" style="margin-top:12px;">
        <div class="sec-label-text">&#9658; NEB CDC Grading Scale</div>
        <div class="sec-label-line"></div>
    </div>
    <div class="grading-wrap">
        <table class="grading-key">
            <tr>
                <td class="g-interval" style="width:11%;">Interval</td>
                <td class="hdr">90–100%</td><td class="hdr">80–89%</td><td class="hdr">70–79%</td><td class="hdr">60–69%</td>
                <td class="hdr">50–59%</td><td class="hdr">40–49%</td><td class="hdr">35–39%</td><td class="hdr">Below 35%</td>
            </tr>
            <tr><td class="g-interval">Grade</td><td>A+</td><td>A</td><td>B+</td><td>B</td><td>C+</td><td>C</td><td>D</td><td>NG</td></tr>
            <tr><td class="g-interval">Points</td><td>4.0</td><td>3.6</td><td>3.2</td><td>2.8</td><td>2.4</td><td>2.0</td><td>1.6</td><td>0.0</td></tr>
            <tr><td class="g-interval">Desc.</td><td>Outstanding</td><td>Excellent</td><td>Very Good</td><td>Good</td><td>Satisfactory</td><td>Acceptable</td><td>Basic</td><td>Non-Graded</td></tr>
        </table>
    </div>

    {{-- SIGNATURES --}}
    <div class="sig-wrap">
        <table class="sig-table">
            <tr>
                <td><div class="sig-space"></div><div class="sig-line"></div><div class="sig-role">Class Teacher</div><div class="sig-note">Signature &amp; Stamp</div></td>
                <td><div class="sig-space"></div><div class="sig-line"></div><div class="sig-role">Controller of Examinations</div><div class="sig-note">Signature &amp; Stamp</div></td>
                <td><div class="sig-space"></div><div class="sig-line"></div><div class="sig-role">Principal / Head of Institution</div><div class="sig-note">Signature &amp; Stamp</div></td>
            </tr>
        </table>
    </div>

    {{-- FOOTER --}}
    <div class="footer-band">
        Authentic certified academic record issued by <strong>{{ $school?->name ?? config('app.name') }}</strong>. Unauthorized alteration is a punishable offence.
        @if(isset($verifyToken))<br><span class="verify-code">Code: {{ $verifyToken }} &bull; {{ $verifyUrl }}</span>@endif
        <br><span style="font-size:7.5pt; color:#aaa;">Printed: {{ now()->format('d F Y') }}</span>
    </div>
</div>
@endforeach
</body>
</html>
