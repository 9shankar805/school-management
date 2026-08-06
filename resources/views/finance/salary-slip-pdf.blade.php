<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: Arial, sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
    .header { text-align: center; border-bottom: 2px solid #1a56db; padding-bottom: 12px; margin-bottom: 20px; }
    .header h1 { font-size: 20px; margin: 0; color: #1a56db; }
    .header p  { margin: 2px 0; color: #666; }
    .slip-title { background: #1a56db; color: #fff; text-align: center; padding: 8px; font-size: 14px; font-weight: bold; margin-bottom: 16px; }
    .info-grid { display: table; width: 100%; margin-bottom: 16px; }
    .info-row  { display: table-row; }
    .info-cell { display: table-cell; width: 50%; padding: 4px 8px; }
    .info-label { font-weight: bold; color: #555; }
    .earnings-table, .deductions-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
    .earnings-table th, .deductions-table th { background: #f3f4f6; padding: 6px 8px; text-align: left; border: 1px solid #ddd; }
    .earnings-table td, .deductions-table td { padding: 6px 8px; border: 1px solid #ddd; }
    .total-row td { font-weight: bold; background: #f9fafb; }
    .net-pay { background: #1a56db; color: #fff; padding: 12px; text-align: center; font-size: 16px; font-weight: bold; margin-top: 16px; }
    .two-col { display: table; width: 100%; }
    .col-half { display: table-cell; width: 50%; vertical-align: top; padding-right: 8px; }
</style>
</head>
<body>
<div class="header">
    <h1>{{ config('app.name', 'School Management') }}</h1>
    <p>SALARY SLIP — {{ $slip->monthName }} {{ $slip->year }}</p>
</div>
<div class="slip-title">EMPLOYEE SALARY SLIP</div>

<div class="info-grid">
    <div class="info-row">
        <div class="info-cell"><span class="info-label">Employee Name:</span> {{ $slip->staff->fullName }}</div>
        <div class="info-cell"><span class="info-label">Slip Month:</span> {{ $slip->monthName }} {{ $slip->year }}</div>
    </div>
    <div class="info-row">
        <div class="info-cell"><span class="info-label">Role:</span> {{ ucfirst($slip->staff->primaryRole) }}</div>
        <div class="info-cell"><span class="info-label">Payment Method:</span> {{ ucfirst(str_replace('_',' ',$slip->payment_method ?? 'N/A')) }}</div>
    </div>
    <div class="info-row">
        <div class="info-cell"><span class="info-label">Working Days:</span> {{ $slip->working_days }}</div>
        <div class="info-cell"><span class="info-label">Present Days:</span> {{ $slip->present_days }}</div>
    </div>
    <div class="info-row">
        <div class="info-cell"><span class="info-label">Absent Days:</span> {{ $slip->absent_days }}</div>
        <div class="info-cell"><span class="info-label">Leave Days:</span> {{ $slip->leave_days }}</div>
    </div>
</div>

<div class="two-col">
    <div class="col-half">
        <table class="earnings-table">
            <thead><tr><th colspan="2">Earnings</th></tr></thead>
            <tbody>
                <tr><td>Basic Salary</td><td>Rs. {{ number_format($slip->basic_salary, 2) }}</td></tr>
                <tr><td>Allowances</td><td>Rs. {{ number_format($slip->allowances, 2) }}</td></tr>
                <tr><td>Overtime</td><td>Rs. {{ number_format($slip->overtime, 2) }}</td></tr>
                <tr><td>Bonus</td><td>Rs. {{ number_format($slip->bonus, 2) }}</td></tr>
                <tr class="total-row"><td><strong>Gross Salary</strong></td><td><strong>Rs. {{ number_format($slip->gross_salary, 2) }}</strong></td></tr>
            </tbody>
        </table>
    </div>
    <div class="col-half">
        <table class="deductions-table">
            <thead><tr><th colspan="2">Deductions</th></tr></thead>
            <tbody>
                <tr><td>Tax Deduction</td><td>Rs. {{ number_format($slip->tax_deduction, 2) }}</td></tr>
                <tr><td>Provident Fund</td><td>Rs. {{ number_format($slip->provident_fund, 2) }}</td></tr>
                <tr><td>Other Deductions</td><td>Rs. {{ number_format($slip->other_deductions, 2) }}</td></tr>
                <tr class="total-row"><td><strong>Total Deductions</strong></td><td><strong>Rs. {{ number_format($slip->tax_deduction + $slip->provident_fund + $slip->other_deductions, 2) }}</strong></td></tr>
            </tbody>
        </table>
    </div>
</div>

<div class="net-pay">
    NET SALARY PAYABLE: Rs. {{ number_format($slip->net_salary, 2) }}
</div>

@if($slip->notes)
<p style="margin-top:12px;"><strong>Notes:</strong> {{ $slip->notes }}</p>
@endif

<p style="margin-top:20px; font-size:10px; color:#999; text-align:center;">
    Generated on {{ now()->format('d M Y H:i') }}
    @if($slip->processor) &nbsp;|&nbsp; Processed by: {{ $slip->processor->fullName }} @endif
</p>
</body>
</html>
