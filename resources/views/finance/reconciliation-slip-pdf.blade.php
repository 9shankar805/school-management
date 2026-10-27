<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Cash Handover Slip - {{ $date }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: DejaVu Sans, sans-serif; }
    body { font-size: 9.5px; color: #0f172a; padding: 18px; }
    .header { text-align: center; margin-bottom: 12px; border-bottom: 1.5px solid #0f172a; padding-bottom: 8px; }
    .school-name { font-size: 14px; font-weight: bold; text-transform: uppercase; color: #0f172a; }
    .school-sub { font-size: 8px; color: #64748b; margin-top: 2px; }
    .doc-title { font-size: 11px; font-weight: bold; margin-top: 6px; color: #059669; text-transform: uppercase; }
    .date-badge { font-size: 9px; margin-top: 3px; color: #334155; font-weight: bold; }

    table { width: 100%; border-collapse: collapse; margin-bottom: 10px; border: 1px solid #cbd5e1; }
    th { background: #f8fafc; color: #334155; padding: 4.5px 8px; font-size: 8.5px; text-align: left; border: 1px solid #cbd5e1; text-transform: uppercase; }
    td { padding: 4px 8px; border: 1px solid #e2e8f0; font-size: 8.5px; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .font-bold { font-weight: bold; }
    .bg-light { background: #f8fafc; }

    .reconcile-box { width: 100%; border-collapse: collapse; margin-bottom: 12px; border: 1.5px solid #0f172a; }
    .reconcile-box td { padding: 5px 8px; font-size: 9px; border: 1px solid #cbd5e1; }
    .reconcile-box .label { font-weight: bold; background: #f1f5f9; width: 45%; }
    .reconcile-box .val { font-weight: bold; text-align: right; width: 55%; }

    .signatures { margin-top: 32px; width: 100%; display: table; }
    .sign-cell { display: table-cell; width: 33.33%; text-align: center; }
    .sign-line { border-top: 1px solid #475569; width: 80%; margin: 0 auto; padding-top: 3px; font-weight: bold; font-size: 8px; }
    .footer { text-align: center; font-size: 7.5px; color: #94a3b8; margin-top: 16px; }
</style>
</head>
<body>

<div class="header">
    <div class="school-name">{{ $school->name ?? config('app.name', 'SCHOOL MANAGEMENT SYSTEM') }}</div>
    <div class="school-sub">{{ $school->address ?? '' }} | Phone: {{ $school->phone ?? '' }}</div>
    <div class="doc-title">DAILY CASH HANDOVER &amp; DENOMINATION SLIP (दैनिक नगद बुझबुझारथ तथा दरबन्दी भौचर)</div>
    <div class="date-badge">
        नेपाली मिति: {{ $bsDateNp }} ({{ $bsDateEn }} BS) &nbsp;·&nbsp; {{ $dayOfWeekNp }} &nbsp;·&nbsp; {{ $currentFyNp }}
    </div>
</div>

{{-- Banknotes Denomination Table --}}
<table>
    <thead>
        <tr>
            <th style="width: 35%;">दरबन्दी (Denomination)</th>
            <th class="text-center" style="width: 25%;">थान (Pieces)</th>
            <th class="text-right" style="width: 40%;">जम्मा रकम (Total NPR)</th>
        </tr>
    </thead>
    <tbody>
        @php
            $denoms = $closing->denominations ?? [];
            $denomList = [
                '1000' => 'रू १००० (Rs. 1,000)',
                '500'  => 'रू ५०० (Rs. 500)',
                '100'  => 'रू १०० (Rs. 100)',
                '50'   => 'रू ५० (Rs. 50)',
                '20'   => 'रू २० (Rs. 20)',
                '10'   => 'रू १० (Rs. 10)',
                '5'    => 'रू ५ (Rs. 5)',
                '2'    => 'रू २ (Rs. 2)',
                '1'    => 'रू १ (Rs. 1)',
            ];
            $totalCount = 0;
            $totalAmount = 0;
        @endphp

        @foreach($denomList as $d => $label)
            @php
                $qty = (int)($denoms[$d] ?? 0);
                $sub = $qty * (int)$d;
                $totalCount += $qty;
                $totalAmount += $sub;
            @endphp
            <tr>
                <td class="font-bold">{{ $label }}</td>
                <td class="text-center">{{ $qty > 0 ? $qty : '—' }}</td>
                <td class="text-right font-bold">{{ $sub > 0 ? number_format($sub, 2) : '0.00' }}</td>
            </tr>
        @endforeach

        @php
            $coins = (float)($denoms['coins'] ?? 0);
            $totalAmount += $coins;
        @endphp
        <tr>
            <td class="font-bold">सिक्का तथा खुद्रा (Coins &amp; Loose)</td>
            <td class="text-center">—</td>
            <td class="text-right font-bold">{{ $coins > 0 ? number_format($coins, 2) : '0.00' }}</td>
        </tr>

        <tr class="bg-light" style="font-weight: bold; font-size: 9.5px;">
            <td>कुल भौतिक नगद (Total Cash Counted)</td>
            <td class="text-center">{{ $totalCount }} थान</td>
            <td class="text-right" style="color: #059669;">NPR {{ number_format($totalAmount, 2) }}</td>
        </tr>
    </tbody>
</table>

{{-- Cash Reconciliation Box --}}
<table class="reconcile-box">
    <tr>
        <td class="label">दिनको अपेक्षित नगद (Expected Cash in Hand):</td>
        <td class="val">NPR {{ number_format($closing->expected_cash, 2) }}</td>
    </tr>
    <tr>
        <td class="label">दाखिला गरिएको भौतिक नगद (Actual Counted Cash):</td>
        <td class="val">NPR {{ number_format($closing->actual_cash, 2) }}</td>
    </tr>
    <tr>
        <td class="label">फरक रकम (Variance / Difference):</td>
        <td class="val" style="color: {{ $closing->difference == 0 ? '#059669' : ($closing->difference > 0 ? '#2563eb' : '#dc2626') }};">
            {{ $closing->difference >= 0 ? '+' : '' }}NPR {{ number_format($closing->difference, 2) }}
            @if($closing->difference == 0)
                (सन्तुलित / Exact Match)
            @elseif($closing->difference > 0)
                (अधिशेष / Surplus Cash)
            @else
                (न्यून / Cash Shortage)
            @endif
        </td>
    </tr>
    @if($closing->notes)
    <tr>
        <td class="label">कैफियत (Remarks / Notes):</td>
        <td class="val" style="text-align: left; font-weight: normal;">{{ $closing->notes }}</td>
    </tr>
    @endif
    <tr>
        <td class="label">हिसाब बन्द गर्ने अधिकृत र समय:</td>
        <td class="val" style="text-align: left; font-weight: normal;">
            {{ $closing->closedByUser->full_name ?? 'Cashier / Accountant' }} &nbsp;·&nbsp; {{ $closing->closed_at ? $closing->closed_at->format('Y-m-d h:i A') : '' }}
        </td>
    </tr>
</table>

{{-- Signatures --}}
<div class="signatures">
    <div class="sign-cell">
        <div class="sign-line">नगद बुझाउने (Cashier / Accountant)</div>
    </div>
    <div class="sign-cell">
        <div class="sign-line">नगद बुझ्ने (Head Accountant / Bursar)</div>
    </div>
    <div class="sign-cell">
        <div class="sign-line">प्रमाणित गर्ने (Principal / Director)</div>
    </div>
</div>

<div class="footer">
    Print Date: {{ now()->format('Y-m-d H:i') }} | School Management ERP Financial Reconciliation Engine
</div>

</body>
</html>
