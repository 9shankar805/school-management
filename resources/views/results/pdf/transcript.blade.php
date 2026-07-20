<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Transcript — {{ $student->full_name }}</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:sans-serif; font-size:10px; color:#1e293b; padding:28px; }
.header { text-align:center; border-bottom:3px solid #4f46e5; padding-bottom:12px; margin-bottom:16px; }
.header h1 { font-size:16px; color:#4f46e5; font-weight:800; }
.header p  { font-size:10px; color:#64748b; margin-top:2px; }
.student-row { display:flex; justify-content:space-between; background:#f8fafc; padding:10px 14px; border-radius:8px; margin-bottom:14px; font-size:10px; }
.student-row .item p:first-child { color:#94a3b8; font-size:9px; }
.student-row .item p:last-child  { font-weight:600; }
h3.sem { background:#e0e7ff; color:#3730a3; padding:5px 10px; border-radius:6px; margin-bottom:6px; font-size:10px; }
table { width:100%; border-collapse:collapse; margin-bottom:12px; }
thead tr { background:#4f46e5; color:#fff; }
thead th { padding:5px 8px; font-size:8.5px; font-weight:600; }
thead th:not(:first-child) { text-align:center; }
tbody td { padding:5px 8px; border-bottom:1px solid #e2e8f0; font-size:9px; }
tbody td:not(:first-child) { text-align:center; }
tbody tr:nth-child(even) { background:#f8fafc; }
.cgpa-box { background:#e0e7ff; color:#3730a3; padding:10px 16px; border-radius:10px; text-align:center; margin-top:14px; }
.cgpa-box .val { font-size:28px; font-weight:800; }
.cgpa-box .lbl { font-size:10px; margin-top:2px; }
.footer { text-align:center; font-size:8px; color:#94a3b8; border-top:1px solid #e2e8f0; padding-top:8px; margin-top:14px; }
.sig { display:flex; justify-content:space-between; margin-top:24px; }
.sig .box .line { border-top:1px solid #94a3b8; width:110px; margin:0 auto 4px; }
.sig .box p { font-size:9px; color:#64748b; text-align:center; }
</style>
</head>
<body>
<div class="header">
    <h1>{{ config('app.name') }}</h1>
    <p>OFFICIAL ACADEMIC TRANSCRIPT &mdash; {{ $session?->session_name }}</p>
</div>

<div class="student-row">
    <div class="item"><p>Student Name</p><p>{{ $student->full_name }}</p></div>
    <div class="item"><p>Email</p><p>{{ $student->email }}</p></div>
    <div class="item"><p>Session</p><p>{{ $session?->session_name }}</p></div>
    <div class="item"><p>Date Issued</p><p>{{ now()->format('d M Y') }}</p></div>
</div>

@foreach($semesterResults as $result)
<h3 class="sem">{{ $result['semester']?->semester_name ?? 'Semester' }} &mdash; GPA: {{ $result['gpa'] }}</h3>
<table>
    <thead><tr>
        <th style="text-align:left">Course</th>
        <th>Final Marks</th><th>Grade</th><th>Points</th><th>Result</th>
    </tr></thead>
    <tbody>
        @foreach($result['courses'] as $c)
        <tr>
            <td>{{ $c['course']?->course_name ?? '—' }}</td>
            <td>{{ $c['final_marks'] }}</td>
            <td style="font-weight:700;color:{{ $c['passed'] ? '#059669' : '#dc2626' }}">{{ $c['grade'] }}</td>
            <td style="color:#4f46e5;font-weight:600">{{ $c['point'] }}</td>
            <td>{{ $c['passed'] ? 'PASS' : 'FAIL' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endforeach

<div class="cgpa-box">
    <div class="val">{{ $cgpaData['cgpa'] }}</div>
    <div class="lbl">Cumulative Grade Point Average (CGPA) &mdash; {{ $cgpaData['total_courses'] }} courses</div>
</div>

<div class="sig">
    <div class="box"><div class="line"></div><p>Registrar</p></div>
    <div class="box"><div class="line"></div><p>Principal</p></div>
    <div class="box"><div class="line"></div><p>School Seal</p></div>
</div>

<div class="footer">{{ config('app.name') }} &bull; Official Transcript &bull; Generated {{ now()->format('d M Y H:i') }} &bull; Confidential</div>
</body>
</html>
