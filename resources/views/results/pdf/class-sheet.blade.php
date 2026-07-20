<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Result Sheet — {{ $class?->class_name }} — {{ $semester?->semester_name }}</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:sans-serif; font-size:10px; color:#1e293b; padding:20px; }
.header { text-align:center; border-bottom:2px solid #4f46e5; padding-bottom:10px; margin-bottom:14px; }
.header h1 { font-size:15px; color:#4f46e5; font-weight:700; }
.header p  { font-size:10px; color:#64748b; margin-top:2px; }
.meta { display:flex; gap:20px; font-size:10px; color:#475569; margin-bottom:12px; }
.meta span b { color:#1e293b; }
table { width:100%; border-collapse:collapse; }
thead tr { background:#4f46e5; color:#fff; }
thead th { padding:6px 8px; text-align:center; font-size:9px; font-weight:600; }
thead th:first-child, thead th:nth-child(2) { text-align:left; }
tbody tr:nth-child(even) { background:#f8fafc; }
tbody td { padding:5px 8px; border-bottom:1px solid #e2e8f0; text-align:center; font-size:9px; }
tbody td:first-child, tbody td:nth-child(2) { text-align:left; }
.pass  { background:#d1fae5; color:#065f46; padding:1px 6px; border-radius:999px; font-weight:700; font-size:8px; }
.fail  { background:#fee2e2; color:#991b1b; padding:1px 6px; border-radius:999px; font-weight:700; font-size:8px; }
.rank1 { color:#d97706; font-weight:700; }
.footer { text-align:center; font-size:8px; color:#94a3b8; border-top:1px solid #e2e8f0; padding-top:6px; margin-top:12px; }
</style>
</head>
<body>
<div class="header">
    <h1>{{ config('app.name') }}</h1>
    <p>Class Result Sheet &mdash; {{ $class?->class_name }} &mdash; {{ $section?->section_name ?? 'All' }} &mdash; {{ $semester?->semester_name }}</p>
</div>
<div class="meta">
    <span>Class: <b>{{ $class?->class_name }}</b></span>
    <span>Semester: <b>{{ $semester?->semester_name }}</b></span>
    <span>Generated: <b>{{ now()->format('d M Y H:i') }}</b></span>
    <span>Students: <b>{{ count($results) }}</b></span>
</div>
<table>
    <thead>
        <tr>
            <th>Rank</th>
            <th>Student</th>
            @foreach($courses as $course)
            <th>{{ $course?->course_name }}<br>FM | Grade</th>
            @endforeach
            <th>GPA</th>
            <th>Total</th>
            <th>Result</th>
        </tr>
    </thead>
    <tbody>
        @foreach($results as $row)
        <tr>
            <td class="{{ $row['rank'] <= 3 ? 'rank1' : '' }}">#{{ $row['rank'] }}</td>
            <td>{{ $row['student']?->full_name ?? '—' }}</td>
            @foreach($row['courses'] as $c)
            <td>{{ $c['final_marks'] }} | <b style="color:{{ $c['passed'] ? '#059669' : '#dc2626' }}">{{ $c['grade'] }}</b></td>
            @endforeach
            <td><b style="color:#4f46e5">{{ $row['gpa'] }}</b></td>
            <td>{{ round($row['totalMarks'], 1) }}</td>
            <td><span class="{{ $row['failed'] === 0 ? 'pass' : 'fail' }}">{{ $row['failed'] === 0 ? 'PASS' : 'FAIL' }}</span></td>
        </tr>
        @endforeach
    </tbody>
</table>
<div class="footer">{{ config('app.name') }} &bull; Generated {{ now()->format('d M Y H:i') }} &bull; Confidential</div>
</body>
</html>
