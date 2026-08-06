<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
body { font-family: Arial, sans-serif; font-size: 12px; margin: 0; padding: 20px; }
h1 { font-size: 18px; text-align: center; color: #1a56db; margin: 0; }
.meta { text-align: center; color: #666; margin: 4px 0 16px; }
table { width: 100%; border-collapse: collapse; }
th { background: #1a56db; color: #fff; padding: 7px 10px; text-align: left; }
td { padding: 6px 10px; border-bottom: 1px solid #e5e7eb; }
.pass { color: #059669; font-weight: bold; }
.fail { color: #dc2626; font-weight: bold; }
</style>
</head>
<body>
<h1>{{ config('app.name') }}</h1>
<p class="meta">
    {{ $practical->title }} &mdash; {{ $practical->course?->course_name ?? '' }} &mdash; {{ $practical->schoolClass?->class_name ?? '' }}<br>
    Date: {{ $practical->exam_date->format('d M Y') }} &nbsp;|&nbsp; Total Marks: {{ $practical->total_marks }} &nbsp;|&nbsp; Pass: {{ $practical->pass_marks }}
</p>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Student Name</th>
            <th>Marks Obtained</th>
            <th>Result</th>
            <th>Remarks</th>
        </tr>
    </thead>
    <tbody>
        @foreach($practical->marks->sortBy('student.first_name') as $i => $mark)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $mark->student->fullName }}</td>
            <td>{{ $mark->marks_obtained }} / {{ $practical->total_marks }}</td>
            <td class="{{ $mark->marks_obtained >= $practical->pass_marks ? 'pass' : 'fail' }}">
                {{ $mark->marks_obtained >= $practical->pass_marks ? 'PASS' : 'FAIL' }}
            </td>
            <td>{{ $mark->remarks ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
<p style="text-align:right;font-size:10px;color:#999;margin-top:16px">Generated {{ now()->format('d M Y H:i') }}</p>
</body>
</html>
