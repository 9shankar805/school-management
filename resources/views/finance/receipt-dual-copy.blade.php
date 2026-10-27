<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt #{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }} — Dual Copy</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }
        .receipt-container {
            border: 1.5px solid #334155;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 12px;
            position: relative;
            background: #ffffff;
        }
        .cut-line {
            text-align: center;
            border-top: 1.5px dashed #94a3b8;
            margin: 14px 0;
            position: relative;
            height: 1px;
        }
        .cut-badge {
            position: absolute;
            top: -9px;
            left: 50%;
            transform: translateX(-50%);
            background: #ffffff;
            padding: 0 10px;
            font-size: 8.5px;
            color: #64748b;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .school-title {
            font-size: 14px;
            font-weight: 800;
            color: #1e1b4b;
            text-transform: uppercase;
            margin: 0;
        }
        .school-subtitle {
            font-size: 9px;
            color: #475569;
            margin: 2px 0 0;
        }
        .copy-tag {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            background: #e0e7ff;
            color: #3730a3;
            border: 1px solid #c7d2fe;
        }
        .info-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 9.5px;
        }
        .info-grid td {
            padding: 2.5px 4px;
        }
        .info-label {
            color: #64748b;
            font-weight: 600;
            width: 15%;
        }
        .info-val {
            font-weight: 700;
            color: #0f172a;
            width: 35%;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 8px;
        }
        .items-table th {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 4px 6px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            color: #334155;
            text-align: left;
        }
        .items-table td {
            border: 1px solid #e2e8f0;
            padding: 4px 6px;
            font-size: 9px;
        }
        .summary-box {
            width: 100%;
            margin-top: 6px;
        }
        .amount-words {
            font-size: 8.5px;
            font-style: italic;
            color: #475569;
            margin-top: 4px;
        }
        .sig-row {
            margin-top: 20px;
            width: 100%;
        }
        .sig-box {
            text-align: center;
            font-size: 8.5px;
            color: #475569;
            border-top: 1px dotted #64748b;
            padding-top: 3px;
            width: 120px;
        }
    </style>
</head>
<body>

@php
    $student = $payment->invoice?->student;
    $invoice = $payment->invoice;
    $school = $school ?? \App\Models\School::find($invoice?->school_id ?? $student?->school_id) ?? null;
    $schoolName = $school->name ?? config('app.name', 'Nepal School Hub');
    $schoolAddress = $school->address ?? 'Kathmandu, Nepal';
    $schoolPan = $school->pan_number ?? $school->vat_number ?? 'N/A';
    $schoolPhone = $school->phone ?? $school->contact_number ?? '';

    $items = $invoice?->items ?? collect();
@endphp

{{-- ═══════════════════════════════════════════════════════════════
     COPY 1: STUDENT / PARENT COPY (विद्यार्थी प्रति)
     ═══════════════════════════════════════════════════════════════ --}}
<div class="receipt-container">
    <table class="header-table">
        <tr>
            <td style="width:70%;">
                <div class="school-title">{{ $schoolName }}</div>
                <div class="school-subtitle">{{ $schoolAddress }} @if($schoolPhone) | Tel: {{ $schoolPhone }} @endif</div>
                <div class="school-subtitle" style="font-weight:600;">PAN / VAT: {{ $schoolPan }}</div>
            </td>
            <td style="text-align:right; width:30%;">
                <div class="copy-tag">Student Copy (विद्यार्थी प्रति)</div>
                <div style="font-size:9px; font-weight:bold; margin-top:4px; color:#475569;">
                    Receipt #: <span style="color:#4f46e5;">#{{ $payment->receipt_number ?? str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</span>
                </div>
            </td>
        </tr>
    </table>

    <table class="info-grid">
        <tr>
            <td class="info-label">Student:</td>
            <td class="info-val">{{ $student?->full_name ?? ($student ? $student->first_name . ' ' . $student->last_name : 'N/A') }}</td>
            <td class="info-label">Date:</td>
            <td class="info-val">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
        </tr>
        <tr>
            <td class="info-label">Roll / ID:</td>
            <td class="info-val">{{ $student?->roll_no ?? $student?->id_card_number ?? '—' }}</td>
            <td class="info-label">Method:</td>
            <td class="info-val">{{ strtoupper($payment->payment_method ?? 'CASH') }}</td>
        </tr>
        <tr>
            <td class="info-label">Invoice:</td>
            <td class="info-val" colspan="3">{{ $invoice->title ?? 'Monthly Fee Statement' }} (#{{ $invoice->invoice_number ?? '—' }})</td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width:8%;">SN</th>
                <th style="width:62%;">Particulars / Fee Head</th>
                <th style="width:30%; text-align:right;">Amount (NPR)</th>
            </tr>
        </thead>
        <tbody>
            @if($items->count() > 0)
                @foreach($items as $idx => $item)
                <tr>
                    <td style="text-align:center;">{{ $idx + 1 }}</td>
                    <td>{{ $item->description ?? $item->name ?? $item->feeType?->name ?? 'Fee Head' }}</td>
                    <td style="text-align:right;">Rs. {{ number_format((float)($item->total ?? $item->amount ?? $item->unit_price ?? 0), 2) }}</td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td style="text-align:center;">1</td>
                    <td>{{ $invoice->title ?? 'Fee Payment' }}</td>
                    <td style="text-align:right;">Rs. {{ number_format($payment->amount, 2) }}</td>
                </tr>
            @endif
            <tr style="background:#f8fafc; font-weight:bold;">
                <td colspan="2" style="text-align:right;">Total Amount Paid:</td>
                <td style="text-align:right; color:#4338ca;">Rs. {{ number_format($payment->amount, 2) }}</td>
            </tr>
            @if(isset($invoice) && $invoice->amount_due > 0)
            <tr style="color:#b91c1c;">
                <td colspan="2" style="text-align:right; font-weight:bold;">Remaining Dues (बक्यौता):</td>
                <td style="text-align:right; font-weight:bold;">Rs. {{ number_format($invoice->amount_due, 2) }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <table style="width:100%; margin-top:10px;">
        <tr>
            <td style="font-size:8.5px; color:#64748b; vertical-align:bottom;">
                <em>Note: Fees once deposited are non-refundable. Thank you.</em>
            </td>
            <td style="text-align:right; vertical-align:bottom;">
                <div class="sig-box" style="float:right;">Authorized Sign / Cashier</div>
            </td>
        </tr>
    </table>
</div>

{{-- Perforation Scissor Line --}}
<div class="cut-line">
    <span class="cut-badge">✂ Cut Along Line</span>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     COPY 2: SCHOOL / OFFICE COPY (कार्यालय प्रति)
     ═══════════════════════════════════════════════════════════════ --}}
<div class="receipt-container">
    <table class="header-table">
        <tr>
            <td style="width:70%;">
                <div class="school-title">{{ $schoolName }}</div>
                <div class="school-subtitle">{{ $schoolAddress }} @if($schoolPhone) | Tel: {{ $schoolPhone }} @endif</div>
                <div class="school-subtitle" style="font-weight:600;">PAN / VAT: {{ $schoolPan }}</div>
            </td>
            <td style="text-align:right; width:30%;">
                <div class="copy-tag" style="background:#fef3c7; color:#92400e; border-color:#fde68a;">Office Copy (कार्यालय प्रति)</div>
                <div style="font-size:9px; font-weight:bold; margin-top:4px; color:#475569;">
                    Receipt #: <span style="color:#d97706;">#{{ $payment->receipt_number ?? str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</span>
                </div>
            </td>
        </tr>
    </table>

    <table class="info-grid">
        <tr>
            <td class="info-label">Student:</td>
            <td class="info-val">{{ $student?->full_name ?? ($student ? $student->first_name . ' ' . $student->last_name : 'N/A') }}</td>
            <td class="info-label">Date:</td>
            <td class="info-val">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
        </tr>
        <tr>
            <td class="info-label">Roll / ID:</td>
            <td class="info-val">{{ $student?->roll_no ?? $student?->id_card_number ?? '—' }}</td>
            <td class="info-label">Method:</td>
            <td class="info-val">{{ strtoupper($payment->payment_method ?? 'CASH') }}</td>
        </tr>
        <tr>
            <td class="info-label">Invoice:</td>
            <td class="info-val" colspan="3">{{ $invoice->title ?? 'Monthly Fee Statement' }} (#{{ $invoice->invoice_number ?? '—' }})</td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width:8%;">SN</th>
                <th style="width:62%;">Particulars / Fee Head</th>
                <th style="width:30%; text-align:right;">Amount (NPR)</th>
            </tr>
        </thead>
        <tbody>
            @if($items->count() > 0)
                @foreach($items as $idx => $item)
                <tr>
                    <td style="text-align:center;">{{ $idx + 1 }}</td>
                    <td>{{ $item->description ?? $item->name ?? $item->feeType?->name ?? 'Fee Head' }}</td>
                    <td style="text-align:right;">Rs. {{ number_format((float)($item->total ?? $item->amount ?? $item->unit_price ?? 0), 2) }}</td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td style="text-align:center;">1</td>
                    <td>{{ $invoice->title ?? 'Fee Payment' }}</td>
                    <td style="text-align:right;">Rs. {{ number_format($payment->amount, 2) }}</td>
                </tr>
            @endif
            <tr style="background:#f8fafc; font-weight:bold;">
                <td colspan="2" style="text-align:right;">Total Amount Paid:</td>
                <td style="text-align:right; color:#92400e;">Rs. {{ number_format($payment->amount, 2) }}</td>
            </tr>
            @if(isset($invoice) && $invoice->amount_due > 0)
            <tr style="color:#b91c1c;">
                <td colspan="2" style="text-align:right; font-weight:bold;">Remaining Dues (बक्यौता):</td>
                <td style="text-align:right; font-weight:bold;">Rs. {{ number_format($invoice->amount_due, 2) }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <table style="width:100%; margin-top:10px;">
        <tr>
            <td style="font-size:8.5px; color:#64748b; vertical-align:bottom;">
                <em>Depositor / Receiver Record Verified</em>
            </td>
            <td style="text-align:right; vertical-align:bottom;">
                <div class="sig-box" style="float:right;">Accountant Signature</div>
            </td>
        </tr>
    </table>
</div>

</body>
</html>
