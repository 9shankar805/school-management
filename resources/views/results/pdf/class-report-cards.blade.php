<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Class Marksheets — {{ $class->class_name }} ({{ $semester->semester_name }})</title>
<style>
@page {
    size: A4 portrait;
    margin: 10mm;
}
* { margin: 0; padding: 0; box-sizing: border-box; }
html, body {
    width: 100%;
    font-family: "DejaVu Serif", Georgia, "Times New Roman", serif;
    font-size: 13pt;
    color: #000000;
    background: #ffffff;
    line-height: 1.4;
}

.sheet-page {
    width: 100%;
    min-height: 257mm;
    page-break-after: always;
    background: #ffffff;
    display: block;
}
.sheet-page:last-child { page-break-after: avoid; }

/* OUTER FRAME */
.frame { border: 4px double #000000; padding: 0; width: 100%; min-height: 257mm; display: block; }
.frame-inner { border: 1.5px solid #000000; margin: 5px; padding: 0; min-height: calc(257mm - 16px); display: block; }

/* HEADER */
.header { padding: 18px 20px 14px 20px; border-bottom: 3px solid #000000; width: 100%; border-collapse: collapse; }
.school-name { font-size: 22pt; font-weight: bold; color: #000000; text-transform: uppercase; letter-spacing: 1.5px; line-height: 1.1; margin-bottom: 5px; }
.school-addr { font-size: 11pt; color: #333333; line-height: 1.5; }

/* TITLE */
.doc-title { text-align: center; padding: 8px 20px; border-bottom: 2px solid #000000; background: #000000; }
.doc-title span { font-size: 14pt; font-weight: bold; color: #ffffff; letter-spacing: 4px; text-transform: uppercase; font-family: "DejaVu Sans", Arial, sans-serif; }

/* INFO */
.info-section { padding: 14px 20px 12px 20px; border-bottom: 2px solid #000000; }
.info-grid { width: 100%; border-collapse: collapse; border: 1.5px solid #000000; }
.info-grid td { padding: 7px 12px; font-size: 12pt; border: 1px solid #999999; vertical-align: middle; }
.info-grid .lbl { font-weight: bold; font-size: 10pt; text-transform: uppercase; letter-spacing: 0.5px; color: #000000; background: #f0f0f0; width: 18%; border-right: 2px solid #000000; }
.info-grid .val { font-size: 13pt; font-weight: bold; color: #000000; width: 32%; }

/* SECTION HEADING */
.sec-heading { font-size: 11pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #000000; padding: 7px 20px 5px 20px; border-bottom: 1px solid #cccccc; font-family: "DejaVu Sans", Arial, sans-serif; }

/* MARKS TABLE */
.marks-wrap { padding: 0 20px; }
.marks-table { width: 100%; border-collapse: collapse; border: 2px solid #000000; font-size: 12pt; }
.marks-table thead tr { background: #000000; }
.marks-table th { color: #ffffff; padding: 9px 8px; font-size: 11pt; font-weight: bold; text-align: center; border-right: 1px solid #444444; text-transform: uppercase; letter-spacing: 0.5px; font-family: "DejaVu Sans", Arial, sans-serif; }
.marks-table th:first-child { text-align: left; padding-left: 12px; }
.marks-table th:last-child  { border-right: none; }
.marks-table td { padding: 8px 8px; border-bottom: 1px solid #cccccc; border-right: 1px solid #cccccc; text-align: center; vertical-align: middle; font-size: 12pt; }
.marks-table td:first-child { text-align: left; padding-left: 12px; font-weight: bold; }
.marks-table td:last-child  { border-right: none; }
.marks-table tbody tr:nth-child(even) { background: #f7f7f7; }
.marks-table tbody tr:last-child td   { border-bottom: none; }

/* SUMMARY */
.summary-wrap { padding: 14px 20px 0 20px; }
.summary-table { width: 100%; border-collapse: collapse; border: 2px solid #000000; }
.summary-table td { width: 25%; text-align: center; padding: 14px 6px; border-right: 2px solid #000000; vertical-align: middle; }
.summary-table td:last-child { border-right: none; }
.sum-val { font-size: 26pt; font-weight: bold; color: #000000; line-height: 1; font-family: "DejaVu Sans", Arial, sans-serif; }
.sum-lbl { font-size: 8.5pt; color: #333333; text-transform: uppercase; letter-spacing: 0.8px; margin-top: 4px; font-family: "DejaVu Sans", Arial, sans-serif; }

/* GRADING KEY */
.grading-wrap { padding: 14px 20px 0 20px; }
.grading-title { font-size: 10pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.8px; color: #000000; padding: 5px 8px; background: #eeeeee; border-left: 4px solid #000000; margin-bottom: 5px; font-family: "DejaVu Sans", Arial, sans-serif; }
.grading-table { width: 100%; border-collapse: collapse; border: 1.5px solid #000000; }
.grading-table th { background: #000000; color: #ffffff; font-size: 10pt; font-weight: bold; border: 1px solid #444444; padding: 6px 4px; text-align: center; font-family: "DejaVu Sans", Arial, sans-serif; }
.grading-table .row-hdr { background: #333333; color: #ffffff; font-size: 10pt; font-weight: bold; padding: 6px 4px; text-align: center; border: 1px solid #555555; font-family: "DejaVu Sans", Arial, sans-serif; }
.grading-table td { border: 1px solid #cccccc; font-size: 10.5pt; padding: 6px 4px; text-align: center; }
.grading-table tr:nth-child(2) td { background: #f0f0f0; font-weight: bold; }
.grading-table tr:nth-child(3) td { background: #e8e8e8; font-weight: bold; }
.grading-table tr:nth-child(4) td { background: #fafafa; font-size: 9.5pt; }

/* SIGNATURES */
.sig-wrap { padding: 28px 20px 16px 20px; }
.sig-table { width: 100%; border-collapse: collapse; }
.sig-table td { width: 33.33%; text-align: center; vertical-align: bottom; padding: 0 14px; }
.sig-space { height: 55px; }
.sig-line  { border-top: 1.5px solid #000000; width: 80%; margin: 0 auto 5px; }
.sig-role  { font-size: 12pt; font-weight: bold; color: #000000; font-family: "DejaVu Sans", Arial, sans-serif; }
.sig-note  { font-size: 9pt; color: #555555; margin-top: 2px; font-family: "DejaVu Sans", Arial, sans-serif; }

/* FOOTER */
.footer-divider { border-top: 2px solid #000000; margin: 10px 20px 0 20px; }
.footer-text { padding: 8px 20px 14px 20px; text-align: center; font-size: 9pt; color: #333333; line-height: 1.7; font-family: "DejaVu Sans", Arial, sans-serif; }
.verify-code { font-family: "DejaVu Sans Mono", "Courier New", monospace; font-size: 8pt; color: #555555; }
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
<div class="frame">
<div class="frame-inner">

    {{-- HEADER --}}
    <table class="header" style="width:100%; border-collapse:collapse;">
        <tr>
            <td style="width:14%; vertical-align:middle; text-align:left;">
                @if(!empty($logoSrc))
                    <img src="{{ $logoSrc }}" style="max-height:75px; max-width:90px;" alt="Logo">
                @else
                    <div style="width:75px; height:75px; border:2px solid #000; border-radius:50%; text-align:center; line-height:75px; font-size:24pt; font-weight:bold;">
                        {{ strtoupper(substr($school?->name ?? 'S', 0, 1)) }}
                    </div>
                @endif
            </td>
            <td style="width:68%; vertical-align:middle; text-align:center; padding:0 12px;">
                <div class="school-name">{{ $school?->name ?? config('app.name') }}</div>
                <div class="school-addr">
                    {{ $school?->address ?? 'Kathmandu, Bagmati Province, Nepal' }}
                    &nbsp;&mdash;&nbsp; Tel: {{ $school?->phone ?? '+977-1-4412345' }}
                </div>
                @if($school?->iemis_code ?? null)
                <div class="school-addr" style="font-size:10pt; font-style:italic; color:#555;">
                    Ministry of Education, CEHRD &mdash; EMIS Code: {{ $school->iemis_code }}
                </div>
                @endif
            </td>
            <td style="width:18%; vertical-align:middle; text-align:right;">
                @if(isset($qrSrc))
                <div style="display:inline-block; text-align:center;">
                    <img src="{{ $qrSrc }}" style="width:72px; height:72px; border:2px solid #000; padding:2px;" alt="QR">
                    <div style="font-size:7pt; font-weight:bold; margin-top:3px; letter-spacing:1px; font-family:'DejaVu Sans',Arial,sans-serif;">SCAN TO VERIFY</div>
                </div>
                @endif
            </td>
        </tr>
    </table>

    {{-- TITLE --}}
    <div class="doc-title">
        <span>&#10022;&nbsp; Academic Achievement Marksheet / Grade Sheet &nbsp;&#10022;</span>
    </div>

    {{-- STUDENT INFO --}}
    <div class="info-section">
        <table class="info-grid">
            <tr>
                <td class="lbl">Student Name</td>
                <td class="val" colspan="3">{{ $student?->full_name ?? '—' }}</td>
            </tr>
            <tr>
                <td class="lbl">Academic Term</td>
                <td class="val">{{ $cardSem?->semester_name ?? '—' }}</td>
                <td class="lbl">Class &amp; Section</td>
                <td class="val">{{ $result['class']?->class_name ?? $class?->class_name ?? '—' }} &nbsp;&bull;&nbsp; Sec. {{ $result['section']?->section_name ?? $section?->section_name ?? 'All' }}</td>
            </tr>
            <tr>
                <td class="lbl">Roll Number</td>
                <td class="val">{{ $result['promotion']?->roll_no ?? $student?->academic_info?->roll_number ?? '—' }}</td>
                <td class="lbl">Issue Date</td>
                <td class="val">{{ now()->format('d M Y') }} <span style="font-size:10pt; font-weight:normal; color:#555;">(@bsDateEn(now()->toDateString()))</span></td>
            </tr>
            <tr>
                <td class="lbl">Class Rank</td>
                <td class="val" colspan="3">
                    @if($result['rank'])
                        @php $rank = (int)$result['rank']; @endphp
                        <span style="font-size:20pt;">{{ ordinal($rank) }}</span>
                        <span style="font-size:10pt; font-weight:normal; color:#555;"> Position in Class</span>
                    @else
                        <span style="font-size:11pt; font-weight:normal; color:#777;">Not Ranked</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    {{-- MARKS TABLE --}}
    <div class="sec-heading">Subject-wise Performance</div>
    <div class="marks-wrap">
        <table class="marks-table">
            <thead>
                <tr>
                    <th style="width:33%;">Subject / Course Name</th>
                    <th style="width:12%;">Full Marks</th>
                    <th style="width:12%;">Obtained</th>
                    <th style="width:9%;">Pct.</th>
                    <th style="width:10%;">Grade</th>
                    <th style="width:12%;">Gr. Point</th>
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
                    <td>{{ $pct }}%</td>
                    <td style="font-weight:bold; font-size:13pt;">{{ $c['grade'] }}</td>
                    <td style="font-weight:bold;">{{ number_format((float)$c['point'], 2) }}</td>
                    <td>
                        @if($c['passed'])
                            <span style="font-weight:bold;">&#10003; PASSED</span>
                        @else
                            <span style="font-weight:bold;">&#10007; RETEST</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center; padding:18px; color:#777; font-style:italic;">
                        No marks recorded for this term.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- SUMMARY --}}
    <div class="summary-wrap">
        <table class="summary-table">
            <tr>
                <td>
                    <div class="sum-val">{{ $result['gpa'] }}</div>
                    <div class="sum-lbl">Grade Point Average (GPA)</div>
                </td>
                <td>
                    <div class="sum-val">{{ round($result['totalMarks'], 0) }}</div>
                    <div class="sum-lbl">Total Marks Obtained</div>
                </td>
                <td>
                    <div class="sum-val">{{ $overallPct }}%</div>
                    <div class="sum-lbl">Overall Percentage</div>
                </td>
                <td>
                    <div class="sum-val" style="font-size:22pt; letter-spacing:2px;">{{ $finalResult }}</div>
                    <div class="sum-lbl">Final Result &nbsp;&mdash;&nbsp; {{ $result['passed'] }} Passed / {{ $result['failed'] ?? 0 }} Failed</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- GRADING KEY --}}
    <div class="grading-wrap">
        <div class="grading-title">&#9632; Government of Nepal &mdash; National Examination Board (NEB) CDC Grading System</div>
        <table class="grading-table">
            <thead>
                <tr>
                    <th class="row-hdr" style="width:12%;">Interval</th>
                    <th>90&ndash;100%</th><th>80&ndash;89%</th><th>70&ndash;79%</th><th>60&ndash;69%</th>
                    <th>50&ndash;59%</th><th>40&ndash;49%</th><th>35&ndash;39%</th><th>Below 35%</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="row-hdr">Grade</td>
                    <td>A+</td><td>A</td><td>B+</td><td>B</td>
                    <td>C+</td><td>C</td><td>D</td><td>NG</td>
                </tr>
                <tr>
                    <td class="row-hdr">Gr. Point</td>
                    <td>4.0</td><td>3.6</td><td>3.2</td><td>2.8</td>
                    <td>2.4</td><td>2.0</td><td>1.6</td><td>0.0</td>
                </tr>
                <tr>
                    <td class="row-hdr">Description</td>
                    <td>Outstanding</td><td>Excellent</td><td>Very Good</td><td>Good</td>
                    <td>Satisfactory</td><td>Acceptable</td><td>Basic</td><td>Non-Graded</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- SIGNATURES --}}
    <div class="sig-wrap">
        <table class="sig-table">
            <tr>
                <td>
                    <div class="sig-space"></div>
                    <div class="sig-line"></div>
                    <div class="sig-role">Class Teacher</div>
                    <div class="sig-note">Signature &amp; Official Stamp</div>
                </td>
                <td>
                    <div class="sig-space"></div>
                    <div class="sig-line"></div>
                    <div class="sig-role">Controller of Examinations</div>
                    <div class="sig-note">Signature &amp; Official Stamp</div>
                </td>
                <td>
                    <div class="sig-space"></div>
                    <div class="sig-line"></div>
                    <div class="sig-role">Principal / Head of Institution</div>
                    <div class="sig-note">Signature &amp; Official Stamp</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- FOOTER --}}
    <div class="footer-divider"></div>
    <div class="footer-text">
        This is an authentic certified statement of academic achievement issued by
        <strong>{{ $school?->name ?? config('app.name') }}</strong>.
        Unauthorized alteration of this document is a punishable offence.
        @if(isset($verifyToken))
        <br>
        <span class="verify-code">
            Verification Code: {{ $verifyToken }}
            &nbsp;&bull;&nbsp; Verify at: {{ $verifyUrl }}
        </span>
        @endif
        <br>
        <span style="font-size:8pt; color:#888;">Printed: {{ now()->format('d F Y') }} &nbsp;&bull;&nbsp; {{ $cardSem?->semester_name ?? '' }}</span>
    </div>

</div>{{-- /frame-inner --}}
</div>{{-- /frame --}}
</div>{{-- /sheet-page --}}
@endforeach

</body>
</html>
