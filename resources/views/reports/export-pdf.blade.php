<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
@page { margin: 10mm; }
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size: 9pt; color: #1e293b; }

.report-header { margin-bottom: 6mm; border-bottom: 2pt solid #4f46e5; padding-bottom: 3mm; }
.report-title  { font-size: 14pt; font-weight: bold; color: #1e1b4b; }
.report-meta   { font-size: 7pt; color: #64748b; margin-top: 1mm; }

table { width: 100%; border-collapse: collapse; margin-top: 3mm; }
thead th {
    background: #1e1b4b;
    color: #ffffff;
    font-size: 7.5pt;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.5pt;
    padding: 3mm 2mm;
    text-align: left;
    border: 0.5pt solid #3730a3;
}
tbody tr:nth-child(even) td { background: #f1f5f9; }
tbody tr:nth-child(odd)  td { background: #ffffff; }
tbody td {
    font-size: 8pt;
    padding: 2.5mm 2mm;
    border: 0.5pt solid #e2e8f0;
    word-break: break-word;
    vertical-align: top;
}
.report-footer {
    margin-top: 5mm;
    font-size: 6.5pt;
    color: #94a3b8;
    text-align: right;
    border-top: 0.5pt solid #e2e8f0;
    padding-top: 2mm;
}
.empty-msg { text-align: center; padding: 8mm; color: #94a3b8; font-size: 9pt; font-style: italic; }
</style>
</head>
<body>

<div class="report-header">
    <div class="report-title">{{ $title }}</div>
    <div class="report-meta">
        Generated on {{ now()->format('d M Y, h:i A') }} &nbsp;|&nbsp;
        {{ config('app.name') }} &nbsp;|&nbsp;
        Total records: {{ count($rows) }}
    </div>
</div>

@if(count($rows) > 0)
<table>
    <thead>
        <tr>
            <th style="width:6mm;">#</th>
            @foreach($cols as $col)
            <th>{{ $col['label'] }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $i => $row)
        <tr>
            <td style="text-align:center;color:#94a3b8;">{{ $i + 1 }}</td>
            @foreach(array_keys($cols) as $key)
            <td>{{ $row[$key] ?? '—' }}</td>
            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="empty-msg">No records found for the selected criteria.</div>
@endif

<div class="report-footer">
    {{ config('app.name') }} &nbsp;·&nbsp; {{ $title }} &nbsp;·&nbsp; {{ now()->format('d M Y') }}
</div>

</body>
</html>
