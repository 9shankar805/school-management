<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: Arial, sans-serif; font-size: 13px; color: #333; margin: 0; padding: 20px; }
    h1 { font-size: 18px; color: #1a56db; text-align: center; }
    .sub { text-align: center; color: #666; margin-bottom: 20px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    th { background: #1a56db; color: #fff; padding: 8px 12px; text-align: left; }
    td { padding: 7px 12px; border-bottom: 1px solid #e5e7eb; }
    .section-header td { background: #f3f4f6; font-weight: bold; }
    .total-row td { background: #f9fafb; font-weight: bold; }
    .net-row td { background: #1a56db; color: #fff; font-size: 15px; font-weight: bold; }
    .text-end { text-align: right; }
    .text-success { color: #059669; }
    .text-danger  { color: #dc2626; }
</style>
</head>
<body>
<h1>{{ config('app.name') }}</h1>
<p class="sub">Profit & Loss Statement — Financial Year {{ $year }}</p>

<table>
    <thead><tr><th colspan="2">Income</th></tr></thead>
    <tbody>
        <tr><td>Fee Collections</td><td class="text-end">Rs. {{ number_format($income, 2) }}</td></tr>
        <tr><td>Other Income</td><td class="text-end">Rs. {{ number_format($otherInc, 2) }}</td></tr>
        <tr class="total-row"><td><strong>Total Income</strong></td><td class="text-end text-success"><strong>Rs. {{ number_format($totalIncome, 2) }}</strong></td></tr>
    </tbody>
</table>

<table>
    <thead><tr><th colspan="2">Expenses</th></tr></thead>
    <tbody>
        <tr><td>Operational Expenses</td><td class="text-end">Rs. {{ number_format($expenses, 2) }}</td></tr>
        <tr><td>Teacher Payroll</td><td class="text-end">Rs. {{ number_format($payroll, 2) }}</td></tr>
        <tr><td>Staff Payroll</td><td class="text-end">Rs. {{ number_format($staffPay, 2) }}</td></tr>
        <tr class="total-row"><td><strong>Total Expenses</strong></td><td class="text-end text-danger"><strong>Rs. {{ number_format($totalExpenses, 2) }}</strong></td></tr>
    </tbody>
</table>

<table>
    <tbody>
        <tr class="net-row">
            <td>NET {{ $netProfit >= 0 ? 'PROFIT' : 'LOSS' }}</td>
            <td class="text-end">Rs. {{ number_format(abs($netProfit), 2) }}</td>
        </tr>
    </tbody>
</table>

<p style="font-size:10px;color:#999;text-align:center;">Generated on {{ now()->format('d M Y H:i') }}</p>
</body>
</html>
