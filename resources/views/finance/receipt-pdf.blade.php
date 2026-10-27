<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt #{{ $payment->receipt_number ?? str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        @page {
            size: A5 landscape;
            margin: 8mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'DejaVu Sans', sans-serif;
        }
        body {
            font-size: 9px;
            color: #1e293b;
            background: #ffffff;
            padding: 6px;
        }
        .outer-border {
            border: 2px solid #0f172a;
            border-radius: 8px;
            padding: 10px 14px;
            position: relative;
            background: #ffffff;
        }
        .inner-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 55px;
            font-weight: 900;
            color: rgba(15, 23, 42, 0.03);
            text-transform: uppercase;
            letter-spacing: 6px;
            pointer-events: none;
            z-index: 0;
        }
        .content-layer {
            position: relative;
            z-index: 1;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: middle;
        }
        .school-name {
            font-size: 15px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }
        .school-info {
            font-size: 8px;
            color: #475569;
            margin-top: 2px;
        }
        .school-meta {
            font-size: 7.5px;
            color: #64748b;
            font-weight: bold;
            margin-top: 2px;
        }
        .receipt-badge-box {
            border: 1.5px solid #0f172a;
            border-radius: 6px;
            padding: 4px 8px;
            text-align: center;
            background: #f8fafc;
        }
        .doc-title-bar {
            text-align: center;
            margin: 6px 0 8px 0;
            border-top: 1px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
            padding: 3px 0;
            background: #f1f5f9;
        }
        .doc-title-bar span {
            font-size: 10.5px;
            font-weight: 800;
            color: #1e1b4b;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .meta-card {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 5px;
            margin-bottom: 8px;
            background: #fcfdfe;
        }
        .meta-card td {
            padding: 3px 6px;
            font-size: 8.5px;
        }
        .meta-label {
            color: #64748b;
            font-weight: 600;
            width: 16%;
        }
        .meta-value {
            font-weight: 700;
            color: #0f172a;
            width: 34%;
        }
        .items-table {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            margin-bottom: 6px;
        }
        .items-table th {
            background: #0f172a;
            color: #ffffff;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 4px 6px;
            text-align: left;
        }
        .items-table td {
            padding: 3.5px 6px;
            font-size: 8.5px;
            border-top: 1px solid #e2e8f0;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .highlight-row {
            background: #ecfdf5;
            font-weight: 800;
            color: #065f46;
        }
        .due-row {
            background: #fef2f2;
            font-weight: 700;
            color: #991b1b;
        }
        .words-box {
            font-size: 8px;
            font-style: italic;
            color: #334155;
            border: 1px dashed #cbd5e1;
            border-radius: 4px;
            padding: 3px 6px;
            background: #f8fafc;
            margin-bottom: 8px;
        }
        .signatures-table {
            width: 100%;
            margin-top: 12px;
        }
        .sig-line {
            border-top: 1px solid #475569;
            width: 80%;
            margin: 0 auto;
            padding-top: 3px;
            font-size: 8px;
            font-weight: bold;
            text-align: center;
            color: #334155;
        }
        .seal-box {
            border: 1px dashed #94a3b8;
            border-radius: 4px;
            height: 38px;
            width: 90px;
            margin: 0 auto;
            text-align: center;
            line-height: 38px;
            font-size: 7.5px;
            color: #94a3b8;
            font-weight: bold;
            text-transform: uppercase;
        }
        .footer-note {
            margin-top: 6px;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
            font-size: 7px;
            color: #64748b;
            text-align: center;
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
    $schoolPhone = $school->phone ?? '';
    $schoolEmail = $school->email ?? '';
    $schoolPan = $school->pan_number ?? $school->vat_number ?? '';
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

    // Nepali Date
    $paymentDate = $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->toDateString() : now()->toDateString();
    $bsDateNp = class_exists(\App\Services\NepaliDateService::class) ? \App\Services\NepaliDateService::formatBS($paymentDate, true) : '';
    $adDateFormatted = \Carbon\Carbon::parse($paymentDate)->format('d M Y');

    // Student & Academic particulars
    $studentName = $student?->full_name ?? ($student ? trim($student->first_name . ' ' . $student->last_name) : 'Walk-in Student');
    $className = $invoice?->schoolClass?->name ?? $student?->academic_class?->name ?? '—';
    $sectionName = $invoice?->section?->name ?? $student?->section?->name ?? '';
    $classSection = $sectionName ? ($className . ' (' . $sectionName . ')') : $className;
    $rollNo = $student?->roll_no ?? $student?->admission_no ?? '—';
    $guardianName = $student?->parent_info?->father_name ?? $student?->parent_info?->mother_name ?? $student?->parent_info?->guardian_name ?? '—';

    // Fee items
    $items = $invoice?->items ?? collect();

    // Number to words helper closure
    $numberToWords = function($num) use (&$numberToWords) {
        $num = (float) $num;
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
                 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        if ($num <= 0) return 'Zero';
        $n = (int) $num;
        $words = [];
        if ($n >= 10000000) {
            $words[] = $numberToWords((int)($n / 10000000)) . ' Crore';
            $n %= 10000000;
        }
        if ($n >= 100000) {
            $words[] = $numberToWords((int)($n / 100000)) . ' Lakh';
            $n %= 100000;
        }
        if ($n >= 1000) {
            $words[] = $numberToWords((int)($n / 1000)) . ' Thousand';
            $n %= 1000;
        }
        if ($n >= 100) {
            $words[] = $numberToWords((int)($n / 100)) . ' Hundred';
            $n %= 100;
        }
        if ($n > 0) {
            if ($n < 20) {
                $words[] = $ones[$n];
            } else {
                $t = $tens[(int)($n / 10)];
                $rem = $n % 10;
                $words[] = $rem ? "$t " . $ones[$rem] : $t;
            }
        }
        $paisa = round(($num - (int)$num) * 100);
        $res = implode(' ', $words);
        if ($paisa > 0) {
            $res .= " and $paisa/100 Paisa";
        }
        return $res . ' Only';
    };
@endphp

<div class="outer-border">
    <div class="inner-watermark">PAID</div>

    <div class="content-layer">
        {{-- Institutional Letterhead --}}
        <table class="header-table">
            <tr>
                <td style="width: 14%; text-align: left;">
                    @if(!empty($logoSrc))
                        <img src="{{ $logoSrc }}" style="max-height: 48px; max-width: 80px; object-fit: contain;" alt="Logo">
                    @else
                        <div style="font-size: 13px; font-weight: bold; color: #4338ca; border: 2px solid #4338ca; border-radius: 6px; padding: 6px 8px; text-align: center; display: inline-block; background: #eef2ff;">
                            {{ strtoupper(substr($schoolName, 0, 4)) }}
                        </div>
                    @endif
                </td>
                <td style="width: 66%; text-align: center;">
                    <div class="school-name">{{ $schoolName }}</div>
                    <div class="school-info">
                        {{ $schoolAddress }}
                        @if($schoolPhone) &bull; Tel: {{ $schoolPhone }} @endif
                        @if($schoolEmail) &bull; Email: {{ $schoolEmail }} @endif
                    </div>
                    <div class="school-meta">
                        @if($schoolPan) PAN/VAT: {{ $schoolPan }} &bull; @endif
                        @if($schoolIemis) CEHRD EMIS: {{ $schoolIemis }} &bull; @endif
                        Affiliated to National Examination Board (NEB)
                    </div>
                </td>
                <td style="width: 20%; text-align: right;">
                    <div class="receipt-badge-box">
                        <div style="font-size: 7px; color: #64748b; font-weight: bold; text-transform: uppercase;">Receipt No.</div>
                        <div style="font-size: 10px; font-weight: bold; color: #4338ca;">
                            #{{ $payment->receipt_number ?? str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}
                        </div>
                        <div style="font-size: 7px; color: #059669; font-weight: bold; margin-top: 1px;">ORIGINAL / PAID</div>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Document Title Bar --}}
        <div class="doc-title-bar">
            <span>FEE PAYMENT RECEIPT / शुल्क भुक्तानी रसिद</span>
        </div>

        {{-- Metadata Grid --}}
        <table class="meta-card">
            <tr>
                <td class="meta-label">Student Name:</td>
                <td class="meta-value" style="font-size: 9.5px; color:#1e1b4b;">{{ $studentName }}</td>
                <td class="meta-label">Nepali Date (BS):</td>
                <td class="meta-value">{{ $bsDateNp ?: '—' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Class &amp; Sec:</td>
                <td class="meta-value">{{ $classSection }}</td>
                <td class="meta-label">English Date (AD):</td>
                <td class="meta-value">{{ $adDateFormatted }}</td>
            </tr>
            <tr>
                <td class="meta-label">Roll / Adm No:</td>
                <td class="meta-value">{{ $rollNo }}</td>
                <td class="meta-label">Payment Mode:</td>
                <td class="meta-value" style="text-transform: uppercase; color:#059669;">
                    {{ $payment->payment_method ?? 'CASH' }}
                    @if($payment->bankAccount) ({{ $payment->bankAccount->bank_name }}) @endif
                </td>
            </tr>
            @if($guardianName !== '—' || $payment->gateway_transaction_id)
            <tr>
                <td class="meta-label">Parent / Guardian:</td>
                <td class="meta-value">{{ $guardianName }}</td>
                <td class="meta-label">Txn / Trace Ref:</td>
                <td class="meta-value" style="font-family: monospace;">{{ $payment->gateway_transaction_id ?: 'COUNTER-CASH' }}</td>
            </tr>
            @endif
        </table>

        {{-- Itemized Particulars Table --}}
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 6%; text-align: center;">S.N.</th>
                    <th style="width: 52%;">Particulars (शुल्क शीर्षक)</th>
                    <th style="width: 22%;">Description / Month</th>
                    <th style="width: 20%; text-align: right;">Amount (Rs.)</th>
                </tr>
            </thead>
            <tbody>
                @if($items->count() > 0)
                    @foreach($items as $idx => $item)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td style="font-weight: 600;">{{ $item->feeType?->name ?? $item->description ?? 'Tuition Fee' }}</td>
                        <td style="color: #64748b;">{{ $item->notes ?? $invoice?->billing_month ?? 'Regular' }}</td>
                        <td class="text-right">Rs. {{ number_format((float)($item->total ?? $item->amount ?? $item->unit_price ?? 0), 2) }}</td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td class="text-center">1</td>
                        <td style="font-weight: 600;">{{ $invoice?->title ?? 'Tuition & Academic Fees' }}</td>
                        <td style="color: #64748b;">{{ $invoice?->billing_month ?? $invoice?->session?->name ?? 'Educational Service' }}</td>
                        <td class="text-right">Rs. {{ number_format($payment->amount, 2) }}</td>
                    </tr>
                @endif

                @if($invoice && (float)$invoice->discount_amount > 0)
                <tr>
                    <td colspan="3" class="text-right" style="color: #059669; font-weight: 600;">Institutional Scholarship / Discount Waiver:</td>
                    <td class="text-right" style="color: #059669; font-weight: 600;">- Rs. {{ number_format($invoice->discount_amount, 2) }}</td>
                </tr>
                @endif

                @if($invoice && (float)$invoice->previous_due > 0)
                <tr>
                    <td colspan="3" class="text-right" style="color: #991b1b; font-weight: 600;">Previous Arrears / Carried Forward Due:</td>
                    <td class="text-right" style="color: #991b1b; font-weight: 600;">+ Rs. {{ number_format($invoice->previous_due, 2) }}</td>
                </tr>
                @endif

                {{-- Amount Paid Highlight Row --}}
                <tr class="highlight-row">
                    <td colspan="3" class="text-right" style="font-size: 9.5px;">TOTAL AMOUNT PAID THIS TRANSACTION (बुझाएको जम्मा रकम):</td>
                    <td class="text-right" style="font-size: 10.5px; color: #065f46;">Rs. {{ number_format($payment->amount, 2) }}</td>
                </tr>

                {{-- Remaining Balance Row --}}
                @php
                    $due = $invoice?->amount_due ?? 0;
                @endphp
                @if($due > 0)
                <tr class="due-row">
                    <td colspan="3" class="text-right" style="font-size: 8.5px;">REMAINING OUTSTANDING BALANCE (बाँकी बक्यौता):</td>
                    <td class="text-right" style="font-size: 9px;">Rs. {{ number_format($due, 2) }}</td>
                </tr>
                @else
                <tr style="background: #f0fdf4; color: #166534; font-weight: bold;">
                    <td colspan="3" class="text-right" style="font-size: 8px;">INVOICE STATUS:</td>
                    <td class="text-right" style="font-size: 8.5px;">FULLY SETTLED (बक्यौता बाँकी छैन)</td>
                </tr>
                @endif
            </tbody>
        </table>

        {{-- Amount in Words --}}
        <div class="words-box">
            <strong>In Words:</strong> Nepalese Rupees {{ $numberToWords($payment->amount) }}
        </div>

        {{-- Signatures & Stamp --}}
        <table class="signatures-table">
            <tr>
                <td style="width: 32%; vertical-align: bottom; text-align: center;">
                    <div class="sig-line">
                        Student / Depositor Signature
                    </div>
                </td>
                <td style="width: 36%; vertical-align: bottom; text-align: center;">
                    <div class="seal-box">
                        OFFICIAL SEAL
                    </div>
                </td>
                <td style="width: 32%; vertical-align: bottom; text-align: center;">
                    <div class="sig-line">
                        {{ $payment->recordedBy?->full_name ?? 'Accounts Officer' }}<br>
                        <span style="font-size: 7px; color: #64748b; font-weight: normal;">Authorized Cashier Signature</span>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Footer Note --}}
        <div class="footer-note">
            <em>Fees once paid are non-refundable. Please preserve this receipt for academic records, admit card issuance, and audit verification.</em>
            <br>
            Generated on {{ now()->format('d M Y, h:i A') }} &bull; {{ $schoolName }} Institutional ERP
        </div>
    </div>
</div>

</body>
</html>
