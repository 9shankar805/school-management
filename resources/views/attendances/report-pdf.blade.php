<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance Report — {{ $class->class_name }} — {{ $monthLabel }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: sans-serif; font-size: 11px; color: #1e293b; padding: 24px; }
        .header { text-align: center; margin-bottom: 18px; border-bottom: 2px solid #4f46e5; padding-bottom: 12px; }
        .header h1 { font-size: 16px; color: #4f46e5; font-weight: 700; }
        .header p  { font-size: 11px; color: #64748b; margin-top: 3px; }
        .meta { display: flex; gap: 24px; margin-bottom: 16px; font-size: 11px; color: #475569; }
        .meta span b { color: #1e293b; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        thead tr { background: #4f46e5; color: #fff; }
        thead th { padding: 7px 10px; text-align: left; font-size: 10px; font-weight: 600; letter-spacing: .03em; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody td { padding: 6px 10px; border-bottom: 1px solid #e2e8f0; }
        .badge-ok  { background: #d1fae5; color: #065f46; padding: 2px 8px; border-radius: 9999px; font-size: 9px; font-weight: 700; }
        .badge-low { background: #fee2e2; color: #991b1b; padding: 2px 8px; border-radius: 9999px; font-size: 9px; font-weight: 700; }
        .footer { text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 8px; margin-top: 10px; }
    </style>
</head>
<body>

<div class="header">
    <h1>{{ config('app.name', 'School') }}</h1>
    <p>Monthly Attendance Report &mdash; {{ $class->class_name }} &mdash; {{ $monthLabel }}</p>
</div>

<div class="meta">
    <span>Class: <b>{{ $class->class_name }}</b></span>
    <span>Month: <b>{{ $monthLabel }}</b></span>
    <span>Generated: <b>{{ now()->format('d M Y H:i') }}</b></span>
    <span>Total Students: <b>{{ count($rows) }}</b></span>
</div>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Student Name</th>
            <th>Total Days</th>
            <th>Present</th>
            <th>Absent</th>
            <th>Late</th>
            <th>Attendance %</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $i => $row)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $row['student']?->full_name ?? '—' }}</td>
            <td>{{ $row['total'] }}</td>
            <td style="color:#059669;font-weight:600;">{{ $row['present'] }}</td>
            <td style="color:#e11d48;font-weight:600;">{{ $row['absent'] }}</td>
            <td style="color:#d97706;">{{ $row['late'] }}</td>
            <td style="font-weight:700;">{{ $row['percentage'] }}%</td>
            <td>
                @if($row['percentage'] >= 75)
                    <span class="badge-ok">OK</span>
                @else
                    <span class="badge-low">LOW</span>
                @endif
            </td>
        </tr>
        @empty
        <tr><td colspan="8" style="text-align:center;padding:20px;color:#94a3b8;">No attendance records found for this period.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    {{ config('app.name') }} &bull; Generated on {{ now()->format('d M Y H:i') }} &bull; Confidential
</div>

</body>
</html>
