<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Class Marksheets — {{ $class->class_name }} ({{ $semester->semester_name }})</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 11.5px; color: #1e293b; background: #fff; }

    @page {
        size: A4 portrait;
        margin: 14mm 14mm 14mm 14mm;
    }

    .sheet-page {
        width: 100%;
        page-break-after: always;
    }
    .sheet-page:last-child {
        page-break-after: avoid;
    }

    /* Institutional Header */
    .header { text-align: center; border-bottom: 2px solid #1e3a8a; padding-bottom: 12px; margin-bottom: 14px; position: relative; }
    .header h1 { font-size: 19px; color: #1e3a8a; font-weight: bold; text-transform: uppercase; margin-bottom: 2px; }
    .header .sub { font-size: 9.5px; color: #475569; margin-bottom: 3px; }
    .header .iemis { font-size: 8.5px; color: #64748b; font-style: italic; }

    /* Title Badge */
    .title-badge { text-align: center; margin-bottom: 14px; }
    .title-badge span { background: #1e3a8a; color: #fff; font-size: 11px; font-weight: bold; letter-spacing: 1.5px; padding: 4px 18px; border-radius: 4px; text-transform: uppercase; }

    /* Student Information Grid */
    .student-info { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 14px; margin-bottom: 14px; width: 100%; border-collapse: collapse; }
    .student-info td { padding: 4px 6px; font-size: 9px; vertical-align: top; }
    .lbl { color: #64748b; font-weight: 600; width: 18%; }
    .val { color: #0f172a; font-weight: bold; width: 32%; }

    /* Academic Marks Table */
    table.marks-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
    table.marks-table th { background: #1e3a8a; color: #fff; padding: 6px 8px; font-size: 8.5px; font-weight: bold; text-align: center; border: 1px solid #1e3a8a; }
    table.marks-table th:first-child { text-align: left; }
    table.marks-table td { padding: 5px 8px; border: 1px solid #cbd5e1; font-size: 9px; text-align: center; }
    table.marks-table td:first-child { text-align: left; }
    table.marks-table tr:nth-child(even) { background: #f8fafc; }

    /* Summary Bar */
    .summary-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 10px 14px; margin-bottom: 14px; display: table; width: 100%; }
    .summary-cell { display: table-cell; text-align: center; width: 25%; vertical-align: middle; }
    .summary-val { font-size: 16px; font-weight: bold; color: #1e3a8a; }
    .summary-lbl { font-size: 8.5px; color: #64748b; text-transform: uppercase; margin-top: 2px; }

    /* Nepal CDC Grading Scale Key */
    .grading-key { width: 100%; border-collapse: collapse; margin-bottom: 16px; background: #fff; }
    .grading-key th { background: #f1f5f9; color: #334155; font-size: 7.5px; font-weight: bold; border: 1px solid #e2e8f0; padding: 3px 4px; text-align: center; }
    .grading-key td { border: 1px solid #e2e8f0; font-size: 7.5px; padding: 3px 4px; text-align: center; }

    /* Signatures */
    .sig-table { width: 100%; border-collapse: collapse; margin-top: 24px; }
    .sig-table td { width: 33.33%; text-align: center; vertical-align: bottom; font-size: 8.5px; color: #475569; }
    .sig-line { border-top: 1px solid #64748b; width: 75%; margin: 0 auto 4px; }
    
    .footer { text-align: center; font-size: 8px; color: #94a3b8; margin-top: 12px; padding-top: 6px; border-top: 1px solid #e2e8f0; }
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
@endphp
<div class="sheet-page">

    {{-- Institutional Header with Verification QR --}}
    <table style="width: 100%; border-bottom: 2px solid #1e3a8a; padding-bottom: 10px; margin-bottom: 12px; border-collapse: collapse;">
        <tr>
            <td style="width: 18%; vertical-align: middle; text-align: left;">
                @if(!empty($logoSrc))
                    <img src="{{ $logoSrc }}" style="max-height: 55px; max-width: 85px;" alt="Logo">
                @else
                    <div style="font-size: 11px; font-weight: bold; color: #1e3a8a; border: 1.5px solid #1e3a8a; border-radius: 4px; padding: 6px 8px; text-align: center; display: inline-block;">
                        {{ strtoupper(substr($school?->name ?? config('app.name'), 0, 8)) }}
                    </div>
                @endif
            </td>
            <td style="width: 62%; text-align: center; vertical-align: middle;">
                <h1 style="font-size: 16px; color: #1e3a8a; font-weight: bold; text-transform: uppercase; margin: 0 0 2px 0;">{{ $school?->name ?? config('app.name') }}</h1>
                <p style="font-size: 8.5px; color: #475569; margin: 0 0 2px 0;">{{ $school?->address ?? 'Kathmandu, Bagmati Province, Nepal' }} &bull; Tel: {{ $school?->phone ?? '+977-1-4412345' }}</p>
                @if($school?->iemis_code)
                <p style="font-size: 8px; color: #64748b; font-style: italic; margin: 0;">Ministry of Education, CEHRD EMIS Code: {{ $school->iemis_code }}</p>
                @endif
            </td>
            <td style="width: 20%; text-align: right; vertical-align: middle;">
                @if(isset($qrSrc))
                <div style="display: inline-block; text-align: center;">
                    <img src="{{ $qrSrc }}" style="width: 54px; height: 54px; border: 1px solid #cbd5e1; padding: 2px; background: #fff;" alt="Verify QR">
                    <div style="font-size: 6.5px; color: #1e3a8a; font-weight: bold; margin-top: 2px; letter-spacing: 0.5px;">SCAN TO VERIFY</div>
                </div>
                @endif
            </td>
        </tr>
    </table>

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
            <td class="val">{{ $cardSem?->semester_name ?? 'Academic Term' }}</td>
        </tr>
        <tr>
            <td class="lbl">Roll Number:</td>
            <td class="val">{{ $result['promotion']?->roll_no ?? $student?->academic_info?->roll_number ?? '—' }}</td>
            <td class="lbl">Class & Section:</td>
            <td class="val">{{ $result['class']?->class_name ?? $class?->class_name ?? '—' }} &bull; Sec {{ $result['section']?->section_name ?? $section?->section_name ?? 'All' }}</td>
        </tr>
        <tr>
            <td class="lbl">Class Rank:</td>
            <td class="val">
                @if($result['rank'])
                    @php $rank = (int)$result['rank']; @endphp
                    <span style="font-size:13px; font-weight:bold; color:#1e3a8a;">{{ ordinal($rank) }}</span>
                    <span style="font-size:9px; color:#64748b; font-weight:normal;"> Position</span>
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
                <th style="width: 34%;">Subject / Course Name</th>
                <th style="width: 14%;">Credit / Full</th>
                <th style="width: 14%;">Obtained</th>
                <th style="width: 12%;">Grade</th>
                <th style="width: 13%;">Grade Point</th>
                <th style="width: 13%;">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse($result['courses'] ?? [] as $c)
            <tr>
                <td><b>{{ $c['course']?->course_name ?? 'Course' }}</b></td>
                <td>{{ $c['full_marks'] ?? 100 }}</td>
                <td>{{ round($c['final_marks'], 1) }}</td>
                <td style="font-weight: bold; color: {{ $c['passed'] ? '#059669' : '#dc2626' }};">
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
            @empty
            <tr>
                <td colspan="6" style="padding: 12px; text-align: center; color: #94a3b8;">No course marks recorded for this term.</td>
            </tr>
            @endforelse
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
            <div class="summary-val" style="color: {{ ($result['failed'] ?? 0) > 0 ? '#dc2626' : '#64748b' }};">
                {{ $result['failed'] }}
            </div>
            <div class="summary-lbl">Courses Below Pass</div>
        </div>
    </div>

    {{-- Nepal CDC 8-Point Grading Key --}}
    <p style="font-size: 8px; font-weight: bold; color: #475569; margin-bottom: 4px; text-transform: uppercase;">
        Government of Nepal — CDC Grading System Key:
    </p>
    <table class="grading-key">
        <tr>
            <th>Interval</th>
            <td>90% - 100%</td>
            <td>80% - 89%</td>
            <td>70% - 79%</td>
            <td>60% - 69%</td>
            <td>50% - 59%</td>
            <td>40% - 49%</td>
            <td>35% - 39%</td>
            <td>Below 35%</td>
        </tr>
        <tr>
            <th>Grade</th>
            <td><b>A+</b></td>
            <td><b>A</b></td>
            <td><b>B+</b></td>
            <td><b>B</b></td>
            <td><b>C+</b></td>
            <td><b>C</b></td>
            <td><b>D</b></td>
            <td><b>NG</b></td>
        </tr>
        <tr>
            <th>Grade Point</th>
            <td>4.0</td>
            <td>3.6</td>
            <td>3.2</td>
            <td>2.8</td>
            <td>2.4</td>
            <td>2.0</td>
            <td>1.6</td>
            <td>0.0</td>
        </tr>
        <tr>
            <th>Description</th>
            <td>Outstanding</td>
            <td>Excellent</td>
            <td>Very Good</td>
            <td>Good</td>
            <td>Satisfactory</td>
            <td>Acceptable</td>
            <td>Basic</td>
            <td>Non-Graded</td>
        </tr>
    </table>

    {{-- Institutional Signatures --}}
    <table class="sig-table">
        <tr>
            <td>
                <div class="sig-line"></div>
                Class Teacher
            </td>
            <td>
                <div class="sig-line"></div>
                Controller of Examinations
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

</div>
@endforeach

</body>
</html>
