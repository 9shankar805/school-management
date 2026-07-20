<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Report Card — {{ $result['student']?->full_name }}</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:sans-serif; font-size:11px; color:#1e293b; padding:28px; max-width:600px; margin:0 auto; }
.header { text-align:center; margin-bottom:20px; padding-bottom:12px; border-bottom:3px solid #4f46e5; }
.header h1 { font-size:18px; color:#4f46e5; font-weight:800; letter-spacing:.03em; }
.header p  { font-size:10px; color:#64748b; margin-top:3px; }
.student-info { background:#f8fafc; border-radius:10px; padding:12px 16px; margin-bottom:16px; display:grid; grid-template-columns:1fr 1fr; gap:6px; }
.student-info .field p:first-child { font-size:9px; color:#94a3b8; }
.student-info .field p:last-child  { font-weight:600; color:#1e293b; }
table { width:100%; border-collapse:collapse; margin-bottom:16px; }
thead tr { background:#4f46e5; color:#fff; }
thead th { padding:7px 10px; font-size:9px; font-weight:600; text-align:left; }
thead th:not(:first-child) { text-align:center; }
tbody tr:nth-child(even) { background:#f8fafc; }
tbody td { padding:6px 10px; border-bottom:1px solid #e2e8f0; font-size:10px; }
tbody td:not(:first-child) { text-align:center; }
.summary { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; background:#f0f4ff; border-radius:10px; padding:12px 16px; margin-bottom:20px; text-align:center; }
.summary .val { font-size:20px; font-weight:800; color:#4f46e5; }
.summary .lbl { font-size:9px; color:#94a3b8; margin-top:2px; }
.sig { margin-top:30px; display:flex; justify-content:space-between; }
.sig .box { text-align:center; }
.sig .box .line { border-top:1px solid #94a3b8; width:120px; margin:0 auto 4px; }
.sig .box p { font-size:9px; color:#64748b; }
.footer { text-align:center; font-size:8px; color:#94a3b8; margin-top:20px; padding-top:8px; border-top:1px solid #e2e8f0; }
</style>
</head>
<body>
<div class="header">
    <h1>{{ config('app.name') }}</h1>
    <p>Academic Report Card &mdash; {{ $result['semester']?->semester_name }}</p>
</div>

<div class="student-info">
    <div class="field"><p>Student Name</p><p>{{ $result['student']?->full_name }}</p></div>
    <div class="field"><p>Semester</p><p>{{ $result['semester']?->semester_name }}</p></div>
    <div class="field"><p>Class Rank</p><p>#{{ $result['rank'] ?? '—' }}</p></div>
    <div class="field"><p>Generated</p><p>{{ now()->format('d M Y') }}</p></div>
</div>

<table>
    <thead>
        <tr>
            <th>Course</th>
            <th>Marks Obtained</th>
            <th>Grade</th>
            <th>Grade Points</th>
            <th>Result</th>
            <th>Notes</th>
        </tr>
    </thead>
    <tbody>
        @foreach($result['courses'] as $c)
        <tr>
            <td><b>{{ $c['course']?->course_name ?? '—' }}</b></td>
            <td>{{ $c['final_marks'] }}</td>
            <td style="font-weight:700; color:{{ $c['passed'] ? '#059669' : '#dc2626' }}">{{ $c['grade'] }}</td>
            <td style="color:#4f46e5; font-weight:600">{{ $c['point'] }}</td>
            <td>
                @if($c['passed'])
                <span style="background:#d1fae5;color:#065f46;padding:1px 7px;border-radius:999px;font-weight:700;font-size:8px;">PASS</span>
                @else
                <span style="background:#fee2e2;color:#991b1b;padding:1px 7px;border-radius:999px;font-weight:700;font-size:8px;">FAIL</span>
                @endif
            </td>
            <td style="color:#94a3b8;font-size:9px">{{ $c['note'] ?? '' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="summary">
    <div><p class="val">{{ $result['gpa'] }}</p><p class="lbl">GPA</p></div>
    <div><p class="val">{{ round($result['totalMarks'], 1) }}</p><p class="lbl">Total Marks</p></div>
    <div><p class="val" style="color:#059669">{{ $result['passed'] }}</p><p class="lbl">Passed</p></div>
    <div><p class="val" style="color:#dc2626">{{ $result['failed'] }}</p><p class="lbl">Failed</p></div>
</div>

<div class="sig">
    <div class="box"><div class="line"></div><p>Class Teacher</p></div>
    <div class="box"><div class="line"></div><p>Principal</p></div>
    <div class="box"><div class="line"></div><p>Parent / Guardian</p></div>
</div>

<div class="footer">{{ config('app.name') }} &bull; Report Card generated {{ now()->format('d M Y') }} &bull; Confidential</div>
</body>
</html>
