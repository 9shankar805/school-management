<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #0f172a; background: #fff; }
    .header { background: #4f46e5; color: #fff; padding: 20px 24px; margin-bottom: 20px; }
    .header h1 { font-size: 20px; font-weight: 900; letter-spacing: -0.5px; }
    .header p  { font-size: 11px; color: #c7d2fe; margin-top: 3px; }
    .school    { font-size: 13px; font-weight: 700; }
    .columns   { display: flex; gap: 16px; padding: 0 24px; }
    .col       { flex: 1; }
    .section   { margin-bottom: 14px; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; }
    .section-head { padding: 8px 12px; font-weight: 900; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; }
    .assets-head     { background: #ecfdf5; color: #065f46; }
    .liab-head       { background: #fff1f2; color: #9f1239; }
    .equity-head     { background: #eef2ff; color: #3730a3; }
    .row       { display: flex; justify-content: space-between; padding: 6px 12px; border-top: 1px solid #f8fafc; font-size: 11px; }
    .row-label { color: #475569; }
    .row-value { font-weight: 600; }
    .total-row { background: #f8fafc; padding: 8px 12px; display: flex; justify-content: space-between; font-weight: 900; font-size: 12px; border-top: 1px solid #e2e8f0; }
    .subhead   { padding: 5px 12px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #94a3b8; border-top: 1px solid #f1f5f9; background: #fafafa; }
    .footer    { text-align: center; font-size: 9px; color: #94a3b8; padding: 16px 24px; margin-top: 12px; border-top: 1px solid #e2e8f0; }
    .balanced  { background: #ecfdf5; border: 1px solid #6ee7b7; color: #065f46; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 700; text-align: center; margin: 0 24px 16px; }
    .unbalanced{ background: #fffbeb; border: 1px solid #fcd34d; color: #92400e; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 700; text-align: center; margin: 0 24px 16px; }
</style>
</head>
<body>

<div class="header">
    <p class="school">{{ config('app.name') }}</p>
    <h1>Balance Sheet</h1>
    <p>As at {{ \Carbon\Carbon::parse($asOf)->format('d F Y') }} &nbsp;·&nbsp; Financial Year {{ $year }}</p>
</div>

@if(abs($balanceCheck) <= 1)
<div class="balanced">✓ Balanced — Assets = Liabilities + Equity</div>
@else
<div class="unbalanced">⚠ Discrepancy of NPR {{ number_format(abs($balanceCheck), 2) }} — please reconcile ledger</div>
@endif

<div class="columns">

    {{-- ASSETS --}}
    <div class="col">
        <div class="section">
            <div class="section-head assets-head">Assets</div>
            <div class="subhead">Current Assets</div>
            <div class="row"><span class="row-label">Cash &amp; Bank (Net)</span><span class="row-value">NPR {{ number_format($cashAndBank, 2) }}</span></div>
            <div class="row"><span class="row-label">Accounts Receivable</span><span class="row-value">NPR {{ number_format($receivables, 2) }}</span></div>
            <div class="subhead">Non-Current Assets</div>
            <div class="row"><span class="row-label">Fixed Assets (at cost)</span><span class="row-value">NPR {{ number_format($fixedAssets, 2) }}</span></div>
            <div class="total-row"><span>Total Assets</span><span>NPR {{ number_format($totalAssets, 2) }}</span></div>
        </div>
    </div>

    {{-- LIABILITIES + EQUITY --}}
    <div class="col">
        <div class="section">
            <div class="section-head liab-head">Liabilities</div>
            <div class="subhead">Current Liabilities</div>
            <div class="row"><span class="row-label">Staff Salaries Payable</span><span class="row-value">NPR {{ number_format($salariesPayable + $teacherSalariesPayable, 2) }}</span></div>
            <div class="row"><span class="row-label">TDS Payable (est. 15%)</span><span class="row-value">NPR {{ number_format($tdsPayable, 2) }}</span></div>
            <div class="total-row"><span>Total Liabilities</span><span>NPR {{ number_format($totalLiabilities, 2) }}</span></div>
        </div>

        <div class="section">
            <div class="section-head equity-head">Equity</div>
            <div class="row"><span class="row-label">Retained Earnings</span><span class="row-value">NPR {{ number_format($retainedEarnings, 2) }}</span></div>
            <div class="total-row"><span>Total Equity</span><span>NPR {{ number_format($totalEquity, 2) }}</span></div>
        </div>

        <div class="section">
            <div class="total-row" style="background:#eef2ff;">
                <span>Liabilities + Equity</span>
                <span>NPR {{ number_format($totalLiabilities + $totalEquity, 2) }}</span>
            </div>
        </div>
    </div>

</div>

<div class="footer">
    Generated on {{ now()->format('d M Y, h:i A') }} &nbsp;·&nbsp; {{ config('app.name') }} School Management System
</div>
</body>
</html>
