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

@php
    $schoolName = $school->name ?? config('app.name', 'SCHOOL MANAGEMENT SYSTEM');
    $schoolAddress = $school->address ?? 'Kathmandu, Nepal';
    $schoolPhone = $school->phone ?? '';
    $schoolEmail = $school->email ?? '';
    $schoolPan = $school->pan_number ?? $school->vat_number ?? '';
    $schoolIemis = $school->iemis_code ?? '';
@endphp

<table style="width: 100%; border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 14px; border-collapse: collapse;">
    <tr>
        <td style="width: 16%; vertical-align: middle; text-align: left;">
            @if(!empty($logoSrc))
                <img src="{{ $logoSrc }}" style="max-height: 58px; max-width: 95px; object-fit: contain;" alt="Logo">
            @else
                <div style="font-size: 13px; font-weight: bold; color: #059669; border: 2px solid #059669; border-radius: 4px; padding: 8px 10px; text-align: center; display: inline-block; background: #ecfdf5;">
                    {{ strtoupper(substr($schoolName, 0, 6)) }}
                </div>
            @endif
        </td>
        <td style="width: 68%; text-align: center; vertical-align: middle;">
            <div style="font-size: 17px; font-weight: bold; text-transform: uppercase; color: #0f172a; letter-spacing: 0.5px; margin-bottom: 2px;">
                {{ $schoolName }}
            </div>
            <div style="font-size: 9px; color: #475569; margin-bottom: 2px;">
                {{ $schoolAddress }}
                @if($schoolPhone) &bull; Tel: {{ $schoolPhone }} @endif
                @if($schoolEmail) &bull; Email: {{ $schoolEmail }} @endif
            </div>
            <div style="font-size: 8.5px; color: #64748b; font-weight: bold; margin-bottom: 3px;">
                @if($schoolPan) PAN/VAT: {{ $schoolPan }} &bull; @endif
                @if($schoolIemis) CEHRD EMIS: {{ $schoolIemis }} &bull; @endif
                Income & Expenditure Statement
            </div>
            <div style="font-size: 12.5px; font-weight: bold; color: #059669; text-transform: uppercase; margin-top: 3px;">
                PROFIT & LOSS STATEMENT (नाफा नोक्सान हिसाब)
            </div>
            <div style="font-size: 10px; margin-top: 2px; color: #1e293b; font-weight: bold;">
                {{ $periodLabel }} &bull; {{ $periodSub }}
            </div>
            <div style="font-size: 8.5px; color: #64748b; margin-top: 2px;">
                Generated on: {{ now()->format('d M Y, h:i A') }}
            </div>
        </td>
        <td style="width: 16%; text-align: right; vertical-align: middle;">
            <div style="border: 1.5px solid #cbd5e1; border-radius: 4px; padding: 5px 8px; background: #f8fafc; display: inline-block; text-align: center;">
                <div style="font-size: 7.5px; color: #64748b; font-weight: bold; text-transform: uppercase;">Financial Period</div>
                <div style="font-size: 10.5px; font-weight: bold; color: #059669;">{{ $periodType === 'fy' ? $selectedFy : $year }}</div>
                <div style="font-size: 7px; color: #059669; font-weight: bold; margin-top: 2px;">AUDITED P&amp;L</div>
            </div>
        </td>
    </tr>
</table>

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
    Report generated on {{ now()->format('d M Y, h:i A') }} &bull; {{ $schoolName }} Financial Accounting Engine &bull; Confidential
</div>

</body>
</html>
