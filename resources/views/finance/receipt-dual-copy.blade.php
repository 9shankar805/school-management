<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt #{{ $payment->receipt_number ?? str_pad($payment->id, 6, '0', STR_PAD_LEFT) }} — Dual Copy</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm 10mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'DejaVu Sans', sans-serif;
        }
        body {
            font-size: 9.5px;
            color: #1e293b;
            background: #ffffff;
        }
        .receipt-container {
            border: 1.5px solid #0f172a;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 8px;
            position: relative;
            background: #ffffff;
        }
        .cut-line {
            text-align: center;
            border-top: 1.5px dashed #64748b;
            margin: 10px 0;
            position: relative;
            height: 1px;
        }
        .cut-badge {
            position: absolute;
            top: -9px;
            left: 50%;
            transform: translateX(-50%);
            background: #ffffff;
            padding: 0 12px;
            font-size: 8px;
            color: #475569;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .school-title {
            font-size: 13.5px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.2;
            margin: 0;
        }
        .school-subtitle {
            font-size: 8px;
            color: #475569;
            margin: 1.5px 0 0;
        }
        .copy-tag {
            display: inline-block;
            padding: 2.5px 8px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .tag-student {
            background: #e0e7ff;
            color: #3730a3;
            border: 1px solid #c7d2fe;
        }
        .tag-office {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .info-grid {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            margin-bottom: 6px;
            background: #f8fafc;
        }
        .info-grid td {
            padding: 3px 6px;
            font-size: 8.5px;
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
            margin-top: 4px;
            margin-bottom: 6px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
        }
        .items-table th {
            background: #0f172a;
            border: 1px solid #0f172a;
            padding: 3.5px 6px;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            color: #ffffff;
            text-align: left;
        }
        .items-table td {
            border: 1px solid #e2e8f0;
            padding: 3px 6px;
            font-size: 8.5px;
        }
        .amount-words {
            font-size: 8px;
            font-style: italic;
            color: #334155;
            background: #f1f5f9;
            padding: 2.5px 6px;
            border-radius: 3px;
            border: 1px dashed #cbd5e1;
            margin-top: 4px;
        }
        .sig-row {
            margin-top: 14px;
            width: 100%;
        }
        .sig-box {
            text-align: center;
            font-size: 8px;
            color: #475569;
            border-top: 1px dotted #64748b;
            padding-top: 2px;
            width: 130px;
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
    $schoolEmail = $school->email ?? '';
    $schoolIemis = $school->iemis_code ?? '';

    // Eager-load or resolve logoSrc if missing
    $logoSrc = $logoSrc ?? (function() use ($school) {
        if (!$school || !$school->logo) return null;
        $clean = ltrim(str_replace('storage/', '', $school->logo), '/');
        $candidates = [
            storage_path('app/public/' . $clean),
            public_path('storage/' . $clean),
            public_path($clean),
        ];
        foreach ($candidates as $filePath) {
            if (file_exists($filePath)) {
                $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                $data = @file_get_contents($filePath);
                if ($data) {
                    $mime = ($ext === 'png') ? 'image/png' : (($ext === 'svg') ? 'image/svg+xml' : 'image/jpeg');
                    return 'data:' . $mime . ';base64,' . base64_encode($data);
                }
            }
        }
        return $school->logo_url ?? null;
    })();

    $paymentDate = $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->toDateString() : now()->toDateString();
    $bsDateNp = class_exists(\App\Services\NepaliDateService::class) ? \App\Services\NepaliDateService::formatBS($paymentDate, true) : '';
    $adDateFormatted = \Carbon\Carbon::parse($paymentDate)->format('d M Y');

    $studentName = $student?->full_name ?? ($student ? trim($student->first_name . ' ' . $student->last_name) : 'Walk-in Student');
    $className = $invoice?->schoolClass?->name ?? $student?->academic_class?->name ?? '—';
    $sectionName = $invoice?->section?->name ?? $student?->section?->name ?? '';
    $classSection = $sectionName ? ($className . ' (' . $sectionName . ')') : $className;
    $rollNo = $student?->roll_no ?? $student?->admission_no ?? '—';

    $items = $invoice?->items ?? collect();

    $numberToWords = function($num) use (&$numberToWords) {
        $num = (float) $num;
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
                 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        if ($num <= 0) return 'Zero';
        $n = (int) $num;
        $words = [];
        if ($n >= 10000000) { $words[] = $numberToWords((int)($n / 10000000)) . ' Crore'; $n %= 10000000; }
        if ($n >= 100000) { $words[] = $numberToWords((int)($n / 100000)) . ' Lakh'; $n %= 100000; }
        if ($n >= 1000) { $words[] = $numberToWords((int)($n / 1000)) . ' Thousand'; $n %= 1000; }
        if ($n >= 100) { $words[] = $numberToWords((int)($n / 100)) . ' Hundred'; $n %= 100; }
        if ($n > 0) {
            if ($n < 20) { $words[] = $ones[$n]; }
            else { $t = $tens[(int)($n / 10)]; $rem = $n % 10; $words[] = $rem ? "$t " . $ones[$rem] : $t; }
        }
        $paisa = round(($num - (int)$num) * 100);
        $res = implode(' ', $words);
        if ($paisa > 0) { $res .= " and $paisa/100 Paisa"; }
        return $res . ' Only';
    };
@endphp

{{-- ═══════════════════════════════════════════════════════════════
     COPY 1: STUDENT / PARENT COPY (विद्यार्थी प्रति)
     ═══════════════════════════════════════════════════════════════ --}}
<div class="receipt-container">
    <table class="header-table">
        <tr>
            <td style="width:12%; text-align:left;">
                @if(!empty($logoSrc))
                    <img src="{{ $logoSrc }}" style="max-height: 44px; max-width: 65px; object-fit: contain;" alt="Logo">
                @else
                    <div style="font-size: 11px; font-weight: bold; color: #4338ca; border: 1.5px solid #4338ca; border-radius: 4px; padding: 4px 6px; text-align: center; display: inline-block; background: #eef2ff;">
                        {{ strtoupper(substr($schoolName, 0, 4)) }}
                    </div>
                @endif
            </td>
            <td style="width:58%;">
                <div class="school-title">{{ $schoolName }}</div>
                <div class="school-subtitle">
                    {{ $schoolAddress }}
                    @if($schoolPhone) | Tel: {{ $schoolPhone }} @endif
                    @if($schoolEmail) | Email: {{ $schoolEmail }} @endif
                </div>
                <div class="school-subtitle" style="font-weight:600;">
                    PAN/VAT: {{ $schoolPan }} @if($schoolIemis) &bull; CEHRD EMIS: {{ $schoolIemis }} @endif
                </div>
            </td>
            <td style="text-align:right; width:30%;">
                <div class="copy-tag tag-student">Student Copy (विद्यार्थी प्रति)</div>
                <div style="font-size:9.5px; font-weight:bold; margin-top:4px; color:#475569;">
                    Receipt #: <span style="color:#4338ca;">#{{ $payment->receipt_number ?? str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div style="font-size:7.5px; color:#64748b; margin-top:2px;">{{ $bsDateNp }}</div>
            </td>
        </tr>
    </table>

    <table class="info-grid">
        <tr>
            <td class="info-label">Student:</td>
            <td class="info-val">{{ $studentName }}</td>
            <td class="info-label">Date (AD/BS):</td>
            <td class="info-val">{{ $adDateFormatted }} ({{ $bsDateNp }})</td>
        </tr>
        <tr>
            <td class="info-label">Class &amp; Sec:</td>
            <td class="info-val">{{ $classSection }}</td>
            <td class="info-label">Payment Mode:</td>
            <td class="info-val">
                {{ strtoupper($payment->payment_method ?? 'CASH') }}
                @if($payment->bankAccount) ({{ $payment->bankAccount->bank_name }}) @endif
                @if($payment->gateway_transaction_id) | Ref: {{ $payment->gateway_transaction_id }} @endif
            </td>
        </tr>
        <tr>
            <td class="info-label">Roll / Adm:</td>
            <td class="info-val">{{ $rollNo }}</td>
            <td class="info-label">Invoice Ref:</td>
            <td class="info-val">{{ $invoice->title ?? 'Fee Invoice' }} (#{{ $invoice->invoice_number ?? '—' }})</td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width:6%; text-align:center;">SN</th>
                <th style="width:64%;">Particulars / Fee Head (शुल्क विवरण)</th>
                <th style="width:30%; text-align:right;">Amount (NPR)</th>
            </tr>
        </thead>
        <tbody>
            @if($items->count() > 0)
                @foreach($items as $idx => $item)
                <tr>
                    <td style="text-align:center;">{{ $idx + 1 }}</td>
                    <td>{{ $item->feeType?->name ?? $item->description ?? 'Tuition & Academic Fees' }}</td>
                    <td style="text-align:right;">Rs. {{ number_format((float)($item->total ?? $item->amount ?? $item->unit_price ?? 0), 2) }}</td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td style="text-align:center;">1</td>
                    <td>{{ $invoice->title ?? 'Tuition & Academic Fees' }}</td>
                    <td style="text-align:right;">Rs. {{ number_format($payment->amount, 2) }}</td>
                </tr>
            @endif

            @if($invoice && (float)$invoice->discount_amount > 0)
            <tr>
                <td colspan="2" style="text-align:right; color:#059669; font-weight:600;">Scholarship / Discount Waiver:</td>
                <td style="text-align:right; color:#059669; font-weight:600;">- Rs. {{ number_format($invoice->discount_amount, 2) }}</td>
            </tr>
            @endif

            <tr style="background:#ecfdf5; font-weight:bold;">
                <td colspan="2" style="text-align:right; color:#065f46;">Total Amount Paid (जम्मा भुक्तानी रकम):</td>
                <td style="text-align:right; color:#065f46; font-size:10px;">Rs. {{ number_format($payment->amount, 2) }}</td>
            </tr>
            @if(isset($invoice) && $invoice->amount_due > 0)
            <tr style="color:#b91c1c; background:#fef2f2;">
                <td colspan="2" style="text-align:right; font-weight:bold;">Remaining Dues (बाँकी बक्यौता):</td>
                <td style="text-align:right; font-weight:bold;">Rs. {{ number_format($invoice->amount_due, 2) }}</td>
            </tr>
            @else
            <tr style="color:#15803d; background:#f0fdf4;">
                <td colspan="2" style="text-align:right; font-weight:bold;">Status:</td>
                <td style="text-align:right; font-weight:bold;">Fully Settled (NIL)</td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="amount-words">
        <strong>In Words:</strong> Nepalese Rupees {{ $numberToWords($payment->amount) }}
    </div>

    <table style="width:100%; margin-top:8px;">
        <tr>
            <td style="font-size:7.5px; color:#64748b; vertical-align:bottom;">
                <em>Fees once deposited are non-refundable. Verified by {{ $schoolName }}.</em>
            </td>
            <td style="text-align:right; vertical-align:bottom;">
                <div class="sig-box" style="float:right;">Authorized Cashier Signature</div>
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
            <td style="width:12%; text-align:left;">
                @if(!empty($logoSrc))
                    <img src="{{ $logoSrc }}" style="max-height: 44px; max-width: 65px; object-fit: contain;" alt="Logo">
                @else
                    <div style="font-size: 11px; font-weight: bold; color: #92400e; border: 1.5px solid #d97706; border-radius: 4px; padding: 4px 6px; text-align: center; display: inline-block; background: #fffbeb;">
                        {{ strtoupper(substr($schoolName, 0, 4)) }}
                    </div>
                @endif
            </td>
            <td style="width:58%;">
                <div class="school-title">{{ $schoolName }}</div>
                <div class="school-subtitle">
                    {{ $schoolAddress }}
                    @if($schoolPhone) | Tel: {{ $schoolPhone }} @endif
                    @if($schoolEmail) | Email: {{ $schoolEmail }} @endif
                </div>
                <div class="school-subtitle" style="font-weight:600;">
                    PAN/VAT: {{ $schoolPan }} @if($schoolIemis) &bull; CEHRD EMIS: {{ $schoolIemis }} @endif
                </div>
            </td>
            <td style="text-align:right; width:30%;">
                <div class="copy-tag tag-office">Office Copy (कार्यालय प्रति)</div>
                <div style="font-size:9.5px; font-weight:bold; margin-top:4px; color:#475569;">
                    Receipt #: <span style="color:#d97706;">#{{ $payment->receipt_number ?? str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div style="font-size:7.5px; color:#64748b; margin-top:2px;">{{ $bsDateNp }}</div>
            </td>
        </tr>
    </table>

    <table class="info-grid">
        <tr>
            <td class="info-label">Student:</td>
            <td class="info-val">{{ $studentName }}</td>
            <td class="info-label">Date (AD/BS):</td>
            <td class="info-val">{{ $adDateFormatted }} ({{ $bsDateNp }})</td>
        </tr>
        <tr>
            <td class="info-label">Class &amp; Sec:</td>
            <td class="info-val">{{ $classSection }}</td>
            <td class="info-label">Payment Mode:</td>
            <td class="info-val">
                {{ strtoupper($payment->payment_method ?? 'CASH') }}
                @if($payment->bankAccount) ({{ $payment->bankAccount->bank_name }}) @endif
                @if($payment->gateway_transaction_id) | Ref: {{ $payment->gateway_transaction_id }} @endif
            </td>
        </tr>
        <tr>
            <td class="info-label">Roll / Adm:</td>
            <td class="info-val">{{ $rollNo }}</td>
            <td class="info-label">Invoice Ref:</td>
            <td class="info-val">{{ $invoice->title ?? 'Fee Invoice' }} (#{{ $invoice->invoice_number ?? '—' }})</td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width:6%; text-align:center;">SN</th>
                <th style="width:64%;">Particulars / Fee Head (शुल्क विवरण)</th>
                <th style="width:30%; text-align:right;">Amount (NPR)</th>
            </tr>
        </thead>
        <tbody>
            @if($items->count() > 0)
                @foreach($items as $idx => $item)
                <tr>
                    <td style="text-align:center;">{{ $idx + 1 }}</td>
                    <td>{{ $item->feeType?->name ?? $item->description ?? 'Tuition & Academic Fees' }}</td>
                    <td style="text-align:right;">Rs. {{ number_format((float)($item->total ?? $item->amount ?? $item->unit_price ?? 0), 2) }}</td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td style="text-align:center;">1</td>
                    <td>{{ $invoice->title ?? 'Tuition & Academic Fees' }}</td>
                    <td style="text-align:right;">Rs. {{ number_format($payment->amount, 2) }}</td>
                </tr>
            @endif

            @if($invoice && (float)$invoice->discount_amount > 0)
            <tr>
                <td colspan="2" style="text-align:right; color:#059669; font-weight:600;">Scholarship / Discount Waiver:</td>
                <td style="text-align:right; color:#059669; font-weight:600;">- Rs. {{ number_format($invoice->discount_amount, 2) }}</td>
            </tr>
            @endif

            <tr style="background:#fef3c7; font-weight:bold;">
                <td colspan="2" style="text-align:right; color:#92400e;">Total Amount Paid (जम्मा भुक्तानी रकम):</td>
                <td style="text-align:right; color:#92400e; font-size:10px;">Rs. {{ number_format($payment->amount, 2) }}</td>
            </tr>
            @if(isset($invoice) && $invoice->amount_due > 0)
            <tr style="color:#b91c1c; background:#fef2f2;">
                <td colspan="2" style="text-align:right; font-weight:bold;">Remaining Dues (बाँकी बक्यौता):</td>
                <td style="text-align:right; font-weight:bold;">Rs. {{ number_format($invoice->amount_due, 2) }}</td>
            </tr>
            @else
            <tr style="color:#15803d; background:#f0fdf4;">
                <td colspan="2" style="text-align:right; font-weight:bold;">Status:</td>
                <td style="text-align:right; font-weight:bold;">Fully Settled (NIL)</td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="amount-words">
        <strong>In Words:</strong> Nepalese Rupees {{ $numberToWords($payment->amount) }}
    </div>

    <table style="width:100%; margin-top:8px;">
        <tr>
            <td style="font-size:7.5px; color:#64748b; vertical-align:bottom;">
                <em>Receiver Record Verified &bull; {{ $payment->recordedBy?->full_name ?? 'Accounts Dept' }}</em>
            </td>
            <td style="text-align:right; vertical-align:bottom;">
                <div class="sig-box" style="float:right;">Accountant Signature</div>
            </td>
        </tr>
    </table>
</div>

</body>
</html>
