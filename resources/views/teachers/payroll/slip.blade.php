<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #1f2937; }
.page { width:210mm; padding:14mm 16mm; }
.header { border-bottom: 2pt solid #4f46e5; padding-bottom:6mm; margin-bottom:6mm; display:flex; justify-content:space-between; align-items:flex-end; }
.school-name { font-size:14pt; font-weight:bold; color:#4f46e5; }
.slip-title  { font-size:11pt; font-weight:bold; color:#1f2937; text-transform:uppercase; letter-spacing:1pt; }
.slip-sub    { font-size:8pt; color:#6b7280; }
.employee-box { background:#f8fafc; border:0.5pt solid #e2e8f0; border-radius:4pt; padding:5mm; margin-bottom:6mm; display:flex; gap:10mm; }
.emp-col { flex:1; }
.emp-label { font-size:7.5pt; color:#6b7280; }
.emp-value { font-size:9.5pt; font-weight:bold; color:#1f2937; margin-top:1mm; }
.earnings-table { width:100%; border-collapse:collapse; margin-bottom:6mm; }
.earnings-table th { background:#f1f5f9; text-align:left; padding:4pt 8pt; font-size:8.5pt; font-weight:600; color:#374151; border:0.5pt solid #e2e8f0; }
.earnings-table td { padding:4pt 8pt; border:0.5pt solid #e2e8f0; font-size:9pt; }
.earnings-table tr:nth-child(even) td { background:#f8fafc; }
.total-row td { font-weight:bold; background:#eff6ff !important; color:#1e40af; }
.net-box { background:#4f46e5; color:#fff; border-radius:4pt; padding:5mm; text-align:center; margin-bottom:6mm; }
.net-label { font-size:9pt; opacity:0.8; }
.net-amount { font-size:18pt; font-weight:bold; margin-top:2mm; }
.attendance-row { display:flex; gap:4mm; margin-bottom:6mm; }
.att-cell { flex:1; background:#f8fafc; border:0.5pt solid #e2e8f0; border-radius:4pt; padding:3mm; text-align:center; }
.att-val { font-size:12pt; font-weight:bold; }
.att-lbl { font-size:7.5pt; color:#6b7280; }
.footer { border-top:0.5pt solid #e2e8f0; padding-top:5mm; display:flex; justify-content:space-between; font-size:8.5pt; color:#6b7280; }
.sig-block { text-align:center; }
.sig-line { border-top:0.5pt solid #374151; width:50mm; margin:0 auto 2mm; }
</style>
</head>
<body>
<div class="page">
    <div class="header">
        <div>
            <div class="school-name">{{ config('app.name') }}</div>
            <div class="slip-sub">Payroll Department</div>
        </div>
        <div style="text-align:right">
            <div class="slip-title">Salary Slip</div>
            <div class="slip-sub">{{ $payroll->month_name }} {{ $payroll->year }}</div>
        </div>
    </div>

    <div class="employee-box">
        @foreach([['Employee Name',$payroll->teacher->full_name],['Employee ID','EMP-'.$payroll->teacher_id],['Email',$payroll->teacher->email],['Payment Date',$payroll->payment_date?->format('d M Y') ?? 'Pending'],['Payment Status',ucfirst($payroll->status)],['Pay Period',$payroll->month_name.' '.$payroll->year]] as [$l,$v])
        <div class="emp-col"><div class="emp-label">{{ $l }}</div><div class="emp-value">{{ $v }}</div></div>
        @endforeach
    </div>

    <div class="attendance-row">
        @foreach([['Working Days',$payroll->working_days,'#1f2937'],['Present',$payroll->present_days,'#065f46'],['Absent',$payroll->absent_days,'#991b1b'],['On Leave',$payroll->leave_days,'#4338ca']] as [$l,$v,$c])
        <div class="att-cell"><div class="att-val" style="color:{{ $c }}">{{ $v }}</div><div class="att-lbl">{{ $l }}</div></div>
        @endforeach
    </div>

    <table class="earnings-table">
        <tr><th colspan="2">Earnings</th><th colspan="2">Deductions</th></tr>
        <tr>
            <td>Basic Salary</td><td>${{ number_format($payroll->basic_salary,2) }}</td>
            <td>Income Tax</td><td>${{ number_format($payroll->tax_deduction,2) }}</td>
        </tr>
        <tr>
            <td>Allowances</td><td>${{ number_format($payroll->allowances,2) }}</td>
            <td>Other Deductions</td><td>${{ number_format($payroll->other_deductions,2) }}</td>
        </tr>
        <tr>
            <td>Overtime</td><td>${{ number_format($payroll->overtime,2) }}</td>
            <td>Total Deductions</td><td>${{ number_format($payroll->tax_deduction+$payroll->other_deductions,2) }}</td>
        </tr>
        <tr class="total-row">
            <td>Gross Salary</td><td>${{ number_format($payroll->gross_salary,2) }}</td>
            <td colspan="2"></td>
        </tr>
    </table>

    <div class="net-box">
        <div class="net-label">NET SALARY</div>
        <div class="net-amount">${{ number_format($payroll->net_salary,2) }}</div>
    </div>

    @if($payroll->notes)
    <p style="font-size:8.5pt; color:#6b7280; margin-bottom:5mm;"><strong>Notes:</strong> {{ $payroll->notes }}</p>
    @endif

    <div class="footer">
        <div style="font-size:7.5pt;">Generated: {{ now()->format('d M Y H:i') }} · {{ config('app.name') }}</div>
        @if($payroll->processor)
        <div class="sig-block">
            <div class="sig-line"></div>
            <div>{{ $payroll->processor->full_name }}</div>
            <div style="font-size:7.5pt; color:#6b7280;">HR / Finance</div>
        </div>
        @endif
    </div>
</div>
</body>
</html>
