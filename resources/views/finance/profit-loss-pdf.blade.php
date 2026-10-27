<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Profit & Loss Statement - {{ $periodLabel }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: DejaVu Sans, sans-serif; }
    body { font-size: 11px; color: #1e293b; padding: 24px; }
    .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #0f172a; padding-bottom: 12px; }
    .school-name { font-size: 18px; font-weight: bold; text-transform: uppercase; color: #0f172a; }
    .school-sub { font-size: 10px; color: #64748b; margin-top: 3px; }
    .doc-title { font-size: 14px; font-weight: bold; margin-top: 10px; color: #059669; text-transform: uppercase; }
    .period-badge { font-size: 11px; margin-top: 4px; color: #334155; font-weight: bold; }
    .date-span { font-size: 9px; color: #64748b; margin-top: 2px; }

    table { width: 100%; border-collapse: collapse; margin-bottom: 16px; border: 1px solid #cbd5e1; }
    th { background: #f8fafc; color: #334155; padding: 7px 10px; font-size: 10px; text-align: left; border-bottom: 1px solid #cbd5e1; text-transform: uppercase; }
    td { padding: 6px 10px; border-bottom: 1px solid #e2e8f0; font-size: 10px; }
    .section-head { background: #ecfdf5; color: #065f46; font-weight: bold; }
    .section-head-exp { background: #ffe4e6; color: #9f1239; font-weight: bold; }
    .total-row td { background: #f8fafc; font-weight: bold; border-top: 1px solid #cbd5e1; }
    .net-row td { background: #0f172a; color: #fff; font-size: 12px; font-weight: bold; padding: 10px; }
    .text-right { text-align: right; }
    .text-success { color: #059669; font-weight: bold; }
    .text-danger  { color: #dc2626; font-weight: bold; }

    .signatures { margin-top: 50px; width: 100%; display: table; }
    .sign-cell { display: table-cell; width: 33.33%; text-align: center; }
    .sign-line { border-top: 1px solid #475569; width: 75%; margin: 0 auto; padding-top: 4px; font-weight: bold; font-size: 9px; }
    .footer { text-align: center; font-size: 8px; color: #94a3b8; margin-top: 30px; }
</style>
</head>
<body>

<div class="header">
    <div class="school-name">{{ $school->name ?? config('app.name', 'SCHOOL MANAGEMENT SYSTEM') }}</div>
    <div class="school-sub">{{ $school->address ?? '' }} | Phone: {{ $school->phone ?? '' }}</div>
    <div class="doc-title">PROFIT & LOSS STATEMENT (नाफा नोक्सान हिसाब)</div>
    <div class="period-badge">{{ $periodLabel }}</div>
    <div class="date-span">{{ $periodSub }}</div>
</div>

{{-- Revenue Table --}}
<table>
    <thead>
        <tr class="section-head">
            <th colspan="2">1. REVENUE & INFLOWS (आम्दानी शीर्षक)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Student Tuition & Educational Fee Collections</td>
            <td class="text-right">Rs. {{ number_format($income, 2) }}</td>
        </tr>
        <tr>
            <td>Miscellaneous Revenues & Other Incomes</td>
            <td class="text-right">Rs. {{ number_format($otherInc, 2) }}</td>
        </tr>
        <tr class="total-row">
            <td>TOTAL REVENUE (जम्मा आम्दानी)</td>
            <td class="text-right text-success">Rs. {{ number_format($totalIncome, 2) }}</td>
        </tr>
    </tbody>
</table>

{{-- Expenditure Table --}}
<table>
    <thead>
        <tr class="section-head-exp">
            <th colspan="2">2. EXPENDITURES & OUTFLOWS (खर्च शीर्षक)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>General & Administrative Operational Expenses</td>
            <td class="text-right">Rs. {{ number_format($expenses, 2) }}</td>
        </tr>
        <tr>
            <td>Teaching Faculty Payroll Disbursed</td>
            <td class="text-right">Rs. {{ number_format($payroll, 2) }}</td>
        </tr>
        <tr>
            <td>Administrative Staff Salaries Disbursed</td>
            <td class="text-right">Rs. {{ number_format($staffPay, 2) }}</td>
        </tr>
        <tr class="total-row">
            <td>TOTAL EXPENDITURE (जम्मा खर्च)</td>
            <td class="text-right text-danger">Rs. {{ number_format($totalExpenses, 2) }}</td>
        </tr>
    </tbody>
</table>

{{-- Net Summary --}}
<table>
    <tbody>
        <tr class="net-row">
            <td>
                NET {{ $netProfit >= 0 ? 'SURPLUS / PROFIT (खुद बचत / नाफा)' : 'DEFICIT / LOSS (खुद घाटा / नोक्सान)' }}
            </td>
            <td class="text-right" style="color:#{{ $netProfit >= 0 ? '34d399' : 'f87171' }};">
                Rs. {{ number_format(abs($netProfit), 2) }}
            </td>
        </tr>
    </tbody>
</table>

{{-- Signatures --}}
<div class="signatures">
    <div class="sign-cell">
        <div class="sign-line">Prepared By (Accountant)</div>
    </div>
    <div class="sign-cell">
        <div class="sign-line">Verified By (Auditor)</div>
    </div>
    <div class="sign-cell">
        <div class="sign-line">Approved By (Principal / Director)</div>
    </div>
</div>

<div class="footer">
    Report generated on {{ now()->format('Y-m-d H:i') }} | School Management ERP Financial Reporting Engine
</div>

</body>
</html>
