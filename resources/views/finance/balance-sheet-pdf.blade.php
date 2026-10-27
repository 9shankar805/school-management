<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Balance Sheet - {{ $periodLabel }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: DejaVu Sans, sans-serif; }
    body { font-size: 10px; color: #1e293b; padding: 24px; }
    .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #0f172a; padding-bottom: 12px; }
    .school-name { font-size: 18px; font-weight: bold; text-transform: uppercase; color: #0f172a; }
    .school-sub { font-size: 10px; color: #64748b; margin-top: 3px; }
    .doc-title { font-size: 14px; font-weight: bold; margin-top: 10px; color: #4338ca; text-transform: uppercase; }
    .period-badge { font-size: 11px; margin-top: 4px; color: #334155; font-weight: bold; }
    .date-span { font-size: 9px; color: #64748b; margin-top: 2px; }

    .balanced-banner { background: #ecfdf5; border: 1px solid #6ee7b7; color: #065f46; padding: 6px 12px; border-radius: 4px; font-size: 10px; font-weight: bold; text-align: center; margin-bottom: 16px; }
    .unbalanced-banner { background: #fffbeb; border: 1px solid #fcd34d; color: #92400e; padding: 6px 12px; border-radius: 4px; font-size: 10px; font-weight: bold; text-align: center; margin-bottom: 16px; }

    .columns { width: 100%; border-collapse: collapse; }
    .col-cell { width: 50%; vertical-align: top; }
    .col-left { padding-right: 8px; }
    .col-right { padding-left: 8px; }

    .section { border: 1px solid #cbd5e1; margin-bottom: 12px; border-radius: 4px; overflow: hidden; }
    .section-head { padding: 6px 10px; font-weight: bold; font-size: 10px; text-transform: uppercase; }
    .assets-head { background: #ecfdf5; color: #065f46; }
    .liab-head   { background: #ffe4e6; color: #9f1239; }
    .equity-head { background: #eef2ff; color: #3730a3; }

    .subhead { padding: 4px 10px; font-size: 8.5px; font-weight: bold; text-transform: uppercase; color: #64748b; background: #f8fafc; border-top: 1px solid #e2e8f0; }
    .row-item { width: 100%; border-collapse: collapse; }
    .row-item td { padding: 5px 10px; font-size: 9.5px; border-top: 1px solid #f1f5f9; }
    .row-item .val { text-align: right; font-weight: bold; }

    .total-row { background: #f8fafc; padding: 6px 10px; font-weight: bold; font-size: 10.5px; border-top: 1px solid #cbd5e1; display: table; width: 100%; }
    .total-left { display: table-cell; text-align: left; }
    .total-right { display: table-cell; text-align: right; }

    .grand-total { background: #0f172a; color: #fff; padding: 7px 10px; font-weight: bold; font-size: 11px; display: table; width: 100%; margin-top: 6px; border-radius: 4px; }

    .signatures { margin-top: 45px; width: 100%; display: table; }
    .sign-cell { display: table-cell; width: 33.33%; text-align: center; }
    .sign-line { border-top: 1px solid #475569; width: 75%; margin: 0 auto; padding-top: 4px; font-weight: bold; font-size: 9px; }
    .footer { text-align: center; font-size: 8px; color: #94a3b8; margin-top: 25px; }
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
                <div style="font-size: 13px; font-weight: bold; color: #4338ca; border: 2px solid #4338ca; border-radius: 4px; padding: 8px 10px; text-align: center; display: inline-block; background: #eef2ff;">
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
                Financial Position Ledger
            </div>
            <div style="font-size: 12.5px; font-weight: bold; color: #4338ca; text-transform: uppercase; margin-top: 3px;">
                BALANCE SHEET / वासलात (STATEMENT OF FINANCIAL POSITION)
            </div>
            <div style="font-size: 10px; margin-top: 2px; color: #1e293b; font-weight: bold;">
                {{ $periodLabel }} &bull; मूल्याङ्कन मिति (As of): {{ $asOfLabel }}
            </div>
            <div style="font-size: 8.5px; color: #64748b; margin-top: 2px;">
                Generated on: {{ now()->format('d M Y, h:i A') }}
            </div>
        </td>
        <td style="width: 16%; text-align: right; vertical-align: middle;">
            <div style="border: 1.5px solid #cbd5e1; border-radius: 4px; padding: 5px 8px; background: #f8fafc; display: inline-block; text-align: center;">
                <div style="font-size: 7.5px; color: #64748b; font-weight: bold; text-transform: uppercase;">Financial Period</div>
                <div style="font-size: 10.5px; font-weight: bold; color: #4338ca;">{{ $periodType === 'fy' ? $selectedFy : $year }}</div>
                <div style="font-size: 7px; color: #059669; font-weight: bold; margin-top: 2px;">AUDITED BS</div>
            </div>
        </td>
    </tr>
</table>

@if(abs($balanceCheck) <= 1)
    <div class="balanced-banner">✓ सन्तुलित हिसाब: Total Assets (NPR {{ number_format($totalAssets, 2) }}) = Liabilities &amp; Equity (NPR {{ number_format($totalLiabilities + $totalEquity, 2) }})</div>
@else
    <div class="unbalanced-banner">⚠ Discrepancy of NPR {{ number_format(abs($balanceCheck), 2) }} — Difference between Assets and Liabilities + Equity</div>
@endif

<table class="columns">
    <tr>
        {{-- ASSETS --}}
        <td class="col-cell col-left">
            <div class="section">
                <div class="section-head assets-head">1. Assets (सम्पत्ति)</div>

                <div class="subhead">Current Assets (चालु सम्पत्ति)</div>
                <table class="row-item">
                    <tr>
                        <td>Cash &amp; Bank Balance (नगद तथा बैंक मौज्दात)</td>
                        <td class="val">NPR {{ number_format($cashAndBank, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Accounts Receivable (उठ्न बाँकी शुल्क)</td>
                        <td class="val">NPR {{ number_format($receivables, 2) }}</td>
                    </tr>
                </table>

                <div class="subhead">Fixed / Non-Current Assets (स्थिर सम्पत्ति)</div>
                <table class="row-item">
                    <tr>
                        <td>Fixed Assets at Book Cost (भवन, उपकरण)</td>
                        <td class="val">NPR {{ number_format($fixedAssets, 2) }}</td>
                    </tr>
                </table>

                <div class="total-row" style="background:#ecfdf5; color:#065f46;">
                    <div class="total-left">Total Assets (कुल सम्पत्ति)</div>
                    <div class="total-right">NPR {{ number_format($totalAssets, 2) }}</div>
                </div>
            </div>
        </td>

        {{-- LIABILITIES & EQUITY --}}
        <td class="col-cell col-right">
            {{-- Liabilities --}}
            <div class="section">
                <div class="section-head liab-head">2. Liabilities (दायित्व)</div>
                <div class="subhead">Current Liabilities (चालु दायित्व)</div>
                <table class="row-item">
                    <tr>
                        <td>Salaries Payable (दिन बाँकी तलब)</td>
                        <td class="val">NPR {{ number_format($salariesPayable + $teacherSalariesPayable, 2) }}</td>
                    </tr>
                    <tr>
                        <td>TDS Payable (आयकर दायित्व - 15%)</td>
                        <td class="val">NPR {{ number_format($tdsPayable, 2) }}</td>
                    </tr>
                </table>
                <div class="total-row">
                    <div class="total-left">Total Liabilities (कुल दायित्व)</div>
                    <div class="total-right">NPR {{ number_format($totalLiabilities, 2) }}</div>
                </div>
            </div>

            {{-- Equity --}}
            <div class="section">
                <div class="section-head equity-head">3. Equity &amp; Reserves (पुँजी तथा कोष)</div>
                <table class="row-item">
                    <tr>
                        <td>Retained Earnings (संचित नाफा / कोष)</td>
                        <td class="val">NPR {{ number_format($retainedEarnings, 2) }}</td>
                    </tr>
                </table>
                <div class="total-row" style="background:#eef2ff; color:#3730a3;">
                    <div class="total-left">Total Equity (कुल इक्विटी)</div>
                    <div class="total-right">NPR {{ number_format($totalEquity, 2) }}</div>
                </div>
            </div>

            <div class="grand-total">
                <div class="total-left">Liabilities + Equity (जम्मा दायित्व र पुँजी)</div>
                <div class="total-right" style="color: #34d399;">NPR {{ number_format($totalLiabilities + $totalEquity, 2) }}</div>
            </div>
        </td>
    </tr>
</table>

{{-- Signatures --}}
<div class="signatures">
    <div class="sign-cell">
        <div class="sign-line">Prepared By (Accountant)</div>
    </div>
    <div class="sign-cell">
        <div class="sign-line">Audited By (Internal Auditor)</div>
    </div>
    <div class="sign-cell">
        <div class="sign-line">Approved By (Principal / Director)</div>
    </div>
</div>

<div class="footer">
    {{ $schoolName }} &bull; Balance Sheet (वासलात) &bull; Generated: {{ now()->format('d M Y H:i') }} &bull; Certified Official Financial Statement
</div>

</body>
</html>
