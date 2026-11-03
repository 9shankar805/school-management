<!DOCTYPE html>
<html lang="ne">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill No. {{ $invoice->invoice_number }} - Techprocod Pvt. Ltd.</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Mukta:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm 12mm 8mm 12mm;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Mukta", sans-serif;
            color: #000000;
            background: #e2e8f0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Screen Toolbar */
        .no-print-toolbar {
            background: #0f172a;
            color: #ffffff;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.25);
        }

        .no-print-toolbar .btn {
            background: #dc2626;
            color: #ffffff;
            padding: 9px 20px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 13px;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            text-decoration: none;
            transition: background 0.15s;
        }

        .no-print-toolbar .btn:hover {
            background: #b91c1c;
        }

        .no-print-toolbar .btn-secondary {
            background: #334155;
        }

        .no-print-toolbar .btn-secondary:hover {
            background: #1e293b;
        }

        .no-print-toolbar .btn-outline {
            background: transparent;
            border: 1px solid #475569;
            color: #cbd5e1;
        }

        .no-print-toolbar .btn-outline:hover {
            background: #1e293b;
            color: #ffffff;
        }

        /* Printable Sheet */
        .bill-sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            background: #ffffff;
            padding: 12mm 16mm 10mm 16mm;
            box-shadow: 0 4px 25px rgba(0,0,0,0.12);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* ── 1. HEADER ──────────────────────────────────────────────── */
        .header-section {
            text-align: center;
            margin-bottom: 8px;
        }

        .comp-title-en {
            font-size: 27px;
            font-weight: 800;
            color: #000000;
            line-height: 1.15;
            letter-spacing: -0.3px;
        }

        .comp-title-np {
            font-family: 'Mukta', 'Noto Sans Devanagari', Arial, sans-serif;
            font-size: 19px;
            font-weight: 800;
            color: #000000;
            margin-top: 1px;
            line-height: 1.25;
            letter-spacing: 0.3px;
        }

        .comp-address {
            font-size: 12px;
            color: #111827;
            font-weight: 500;
            margin-top: 2px;
            line-height: 1.35;
        }

        .comp-contact {
            font-size: 12px;
            color: #111827;
            font-weight: 500;
            margin-top: 1px;
            line-height: 1.35;
        }

        /* ── 2. PAN NO & BILL NO ROW ────────────────────────────────── */
        .pan-bill-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 8px;
            padding-bottom: 4px;
        }

        .pan-container {
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 800;
            font-size: 13px;
            letter-spacing: 0.2px;
        }

        .pan-boxes {
            display: inline-flex;
            border: 1.5px solid #000000;
        }

        .pan-digit-box {
            width: 19px;
            height: 22px;
            border-right: 1.5px solid #000000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 800;
            font-family: Arial, sans-serif;
            background: #ffffff;
        }

        .pan-digit-box:last-child {
            border-right: none;
        }

        .bill-no-container {
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.2px;
        }

        .bill-no-val {
            color: #dc2626;
            font-weight: 800;
            font-size: 14px;
            margin-left: 3px;
        }

        /* Horizontal dotted divider line under PAN row */
        .pan-dotted-divider {
            border-bottom: 1.2px dotted #000000;
            width: 100%;
            margin-bottom: 8px;
        }

        /* ── 3. CLIENT INFO LINES (M/S, Date, Address) ──────────────── */
        .client-info-lines {
            margin-bottom: 12px;
            font-size: 13px;
            line-height: 1.6;
        }

        .client-line {
            display: flex;
            align-items: baseline;
            width: 100%;
            margin-bottom: 5px;
        }

        .client-label {
            font-weight: 800;
            margin-right: 6px;
            white-space: nowrap;
        }

        .dotted-fill {
            flex: 1;
            display: flex;
            align-items: baseline;
            border-bottom: 1.2px dotted #000000;
            min-height: 18px;
            padding-left: 4px;
        }

        .dotted-value {
            font-weight: 700;
            color: #000000;
            white-space: nowrap;
        }

        .date-segment {
            display: flex;
            align-items: baseline;
            margin-left: 20px;
            white-space: nowrap;
        }

        .date-label {
            font-weight: 800;
            margin-right: 6px;
        }

        .date-dotted-fill {
            width: 140px;
            border-bottom: 1.2px dotted #000000;
            text-align: center;
            font-weight: 700;
            min-height: 18px;
        }

        /* ── 4. MAIN TABLE (Exact-aligned grid) ─────────────────────── */
        .bill-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000000;
            table-layout: fixed;
            margin-bottom: 16px;
        }

        .bill-table th,
        .bill-table td {
            border-right: 1.5px solid #000000;
            vertical-align: top;
            padding: 0;
        }

        .bill-table th:last-child,
        .bill-table td:last-child {
            border-right: none;
        }

        /* Table Column Widths */
        .col-sn   { width: 7%; }
        .col-part { width: 55%; }
        .col-qty  { width: 9%; }
        .col-rate { width: 13%; }
        .col-amt  { width: 16%; }

        /* Table Header */
        .bill-table thead th {
            border-bottom: 2px solid #000000;
            padding: 6px 4px;
            font-size: 13px;
            font-weight: 800;
            text-align: center;
            background: #ffffff;
            color: #000000;
        }

        /* Item Rows */
        .item-row td {
            padding: 4px 6px;
            font-size: 13px;
            line-height: 1.4;
        }

        .item-row .col-sn {
            text-align: center;
        }

        .item-row .col-part {
            text-align: left;
            padding-left: 8px;
        }

        .item-row .col-qty {
            text-align: center;
        }

        .item-row .col-rate {
            text-align: right;
            padding-right: 8px;
            font-family: -apple-system, BlinkMacSystemFont, Arial, sans-serif;
        }

        .item-row .col-amt {
            text-align: right;
            padding-right: 8px;
            font-weight: 700;
            font-family: -apple-system, BlinkMacSystemFont, Arial, sans-serif;
        }

        .item-title-text {
            font-weight: 700;
        }

        .item-desc-text {
            font-size: 11px;
            color: #374151;
            margin-top: 1px;
        }

        /* Filler Row (Height calibrated to fill the physical pad) */
        .filler-row td {
            height: 480px;
        }

        /* ── TABLE FOOTER (In words & Totals) ───────────────────────── */
        .table-foot-tbody {
            border-top: 2px solid #000000;
        }

        .footer-words-cell {
            padding: 8px 10px;
            vertical-align: top;
            font-size: 12.5px;
        }

        .words-line-container {
            display: flex;
            align-items: baseline;
            width: 100%;
        }

        .words-lead {
            font-weight: 800;
            margin-right: 6px;
            white-space: nowrap;
        }

        .words-text-val {
            font-weight: 700;
            text-transform: capitalize;
            border-bottom: 1px dotted #000000;
            flex: 1;
            padding-bottom: 1px;
        }

        .words-empty-dotted {
            width: 100%;
            border-bottom: 1.2px dotted #000000;
            height: 22px;
            margin-top: 4px;
        }

        /* Totals Rows */
        .total-label-cell {
            border-top: 1px solid #000000;
            padding: 5px 6px;
            font-size: 12.5px;
            font-weight: 700;
            text-align: center;
            vertical-align: middle;
        }

        .total-val-cell {
            border-top: 1px solid #000000;
            padding: 5px 8px;
            font-size: 12.5px;
            font-weight: 700;
            text-align: right;
            vertical-align: middle;
            font-family: -apple-system, BlinkMacSystemFont, Arial, sans-serif;
        }

        .table-foot-tbody tr:first-child .total-label-cell,
        .table-foot-tbody tr:first-child .total-val-cell {
            border-top: none;
        }

        .grand-total-row .total-label-cell {
            font-size: 13.5px;
            font-weight: 800;
        }

        .grand-total-row .total-val-cell {
            font-size: 13.5px;
            font-weight: 800;
        }

        /* ── 5. SIGNATURE SECTION ───────────────────────────────────── */
        .sig-container {
            display: flex;
            justify-content: flex-end;
            margin-top: 14px;
            margin-bottom: 4px;
        }

        .sig-box {
            width: 250px;
            text-align: center;
        }

        .sig-dotted-line {
            border-bottom: 1.2px dotted #000000;
            width: 100%;
            height: 18px;
            margin-bottom: 6px;
        }

        .sig-text {
            font-weight: 800;
            font-size: 13px;
        }

        /* ── BLANK PAD MODE TOGGLE ──────────────────────────────────── */
        body.blank-mode .data-value {
            display: none !important;
        }
        body.blank-mode .item-row {
            display: none !important;
        }
        body.blank-mode .filler-row td {
            height: 580px !important;
        }

        /* ── PRINT MEDIA RULES ─────────────────────────────────────── */
        @media print {
            body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .no-print-toolbar {
                display: none !important;
            }

            .bill-sheet {
                width: 100% !important;
                max-width: 100% !important;
                min-height: auto !important;
                margin: 0 !important;
                padding: 4mm 6mm !important;
                box-shadow: none !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .filler-row td {
                height: 480px !important;
            }
        }
    </style>
</head>
<body>

    {{-- Screen Toolbar for Super Admin --}}
    <div class="no-print-toolbar">
        <div style="display: flex; align-items: center; gap: 14px;">
            <a href="{{ route('super.billing.show', $invoice->id) }}" class="btn btn-secondary">
                &larr; Back to Invoice
            </a>
            <span style="font-weight: 700; font-size: 14px; letter-spacing: 0.3px;">
                Bill No: {{ $invoice->invoice_number }} &bull; {{ $invoice->school_name }}
            </span>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <button type="button" onclick="toggleBlankMode()" class="btn btn-outline" id="blankModeBtn">
                <svg width="15" height="15" fill="currentColor" viewBox="0 0 16 16"><path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/><path d="M4 4h8v1H4V4zm0 3h8v1H4V7zm0 3h5v1H4v-1z"/></svg>
                Blank Pad Mode (खाली बिल फारम)
            </button>
            <button type="button" onclick="window.print()" class="btn">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/><path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/></svg>
                Print Bill (प्रिन्ट गर्नुहोस्)
            </button>
        </div>
    </div>

    {{-- Main Printable Bill Pad Sheet --}}
    <div class="bill-sheet">
        <div>
            {{-- 1. Header (Techprocod Pvt. Ltd.) --}}
            <div class="header-section">
                <h1 class="comp-title-en">Techprocod Pvt. Ltd.</h1>
                <h2 class="comp-title-np">टेक प्रोकोड प्रा.लि</h2>
                <p class="comp-address">Ward No. 5, Thana Chowk, Mirchaiya, Siraha, Nepal</p>
                <p class="comp-contact">Email: info.techprocod@gmail.com | Mobile: +977 980-5916598</p>
            </div>

            {{-- 2. PAN No. & Bill No. Row --}}
            <div class="pan-bill-row">
                <div class="pan-container">
                    <span>PAN NO.:</span>
                    <div class="pan-boxes">
                        @php
                            $panDigits = \App\Models\SaasInvoice::getPanDigits();
                        @endphp
                        @foreach($panDigits as $digit)
                            <div class="pan-digit-box">{{ $digit }}</div>
                        @endforeach
                    </div>
                </div>

                <div class="bill-no-container">
                    <span>S.N. / Bill No.:</span>
                    <span class="bill-no-val">{{ $invoice->invoice_number }}</span>
                </div>
            </div>

            {{-- Dotted horizontal divider line --}}
            <div class="pan-dotted-divider"></div>

            {{-- 3. M/S, Date & Address Client Details --}}
            <div class="client-info-lines">
                {{-- M/S and Date Line --}}
                <div class="client-line">
                    <span class="client-label">M/S:</span>
                    <div class="dotted-fill">
                        <span class="dotted-value data-value">{{ $invoice->school_name }}</span>
                    </div>
                    <div class="date-segment">
                        <span class="date-label">Date:</span>
                        <div class="date-dotted-fill">
                            <span class="data-value">{{ $invoice->invoice_date_bs ?: $invoice->invoice_date->format('Y-m-d') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Address Line --}}
                <div class="client-line">
                    <span class="client-label">Address:</span>
                    <div class="dotted-fill">
                        <span class="dotted-value data-value">{{ $invoice->school_address ?: ($invoice->school ? ($invoice->school->address . ', ' . $invoice->school->city) : '') }}</span>
                    </div>
                </div>
            </div>

            {{-- 4. Main Table (100% exact aligned grid) --}}
            <table class="bill-table">
                <colgroup>
                    <col class="col-sn">
                    <col class="col-part">
                    <col class="col-qty">
                    <col class="col-rate">
                    <col class="col-amt">
                </colgroup>
                <thead>
                    <tr>
                        <th>S.NO.</th>
                        <th>Particulars</th>
                        <th>Qty.</th>
                        <th>Rate</th>
                        <th>Amount (Rs.)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $idx => $it)
                        <tr class="item-row">
                            <td class="col-sn">{{ $idx + 1 }}</td>
                            <td class="col-part">
                                <span class="item-title-text">{{ $it->title }}</span>
                                @if($it->description)
                                    <div class="item-desc-text">({{ $it->description }})</div>
                                @endif
                            </td>
                            <td class="col-qty">{{ (float)$it->quantity == intval($it->quantity) ? intval($it->quantity) : $it->quantity }}</td>
                            <td class="col-rate">{{ number_format($it->unit_price, 2) }}</td>
                            <td class="col-amt">{{ number_format($it->total_amount, 2) }}</td>
                        </tr>
                    @endforeach

                    {{-- Filler Row to maintain continuous vertical column divider lines --}}
                    <tr class="filler-row">
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
                <tbody class="table-foot-tbody">
                    @php
                        $amountInWords = \App\Models\SaasInvoice::numberToWords($invoice->total_amount);
                    @endphp
                    {{-- Row 1: Sub Total --}}
                    <tr>
                        <td rowspan="4" colspan="2" class="footer-words-cell">
                            <div class="words-line-container">
                                <span class="words-lead">In words rupees:</span>
                                <span class="words-text-val data-value">{{ $amountInWords }}</span>
                            </div>
                            <div class="words-empty-dotted"></div>
                            <div class="words-empty-dotted"></div>
                        </td>
                        <td colspan="2" class="total-label-cell">Sub Total</td>
                        <td class="total-val-cell">
                            <span class="data-value">{{ number_format($invoice->subtotal, 2) }}</span>
                        </td>
                    </tr>
                    {{-- Row 2: Advance --}}
                    <tr>
                        <td colspan="2" class="total-label-cell">Advance</td>
                        <td class="total-val-cell">
                            <span class="data-value">{{ $invoice->paid_amount > 0 ? number_format($invoice->paid_amount, 2) : '' }}</span>
                        </td>
                    </tr>
                    {{-- Row 3: Discount --}}
                    <tr>
                        <td colspan="2" class="total-label-cell">Discount</td>
                        <td class="total-val-cell">
                            <span class="data-value">{{ $invoice->discount_amount > 0 ? number_format($invoice->discount_amount, 2) : '' }}</span>
                        </td>
                    </tr>
                    {{-- Row 4: Grand Total --}}
                    <tr class="grand-total-row">
                        <td colspan="2" class="total-label-cell">Grand Total</td>
                        <td class="total-val-cell">
                            <span class="data-value">{{ number_format($invoice->total_amount, 2) }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- 5. Signature Section --}}
            <div class="sig-container">
                <div class="sig-box">
                    <div class="sig-dotted-line"></div>
                    <div class="sig-text">For: Techprocod Pvt. Ltd.</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleBlankMode() {
            document.body.classList.toggle('blank-mode');
            const btn = document.getElementById('blankModeBtn');
            if (document.body.classList.contains('blank-mode')) {
                btn.innerHTML = `<svg width="15" height="15" fill="currentColor" viewBox="0 0 16 16"><path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/><path d="M10.97 4.97a.75.75 0 0 1 1.071 1.05l-3.992 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.235.235 0 0 1 .02-.022z"/></svg> Filled Invoice Mode (विवरण सहित)`;
                btn.classList.add('bg-slate-700');
            } else {
                btn.innerHTML = `<svg width="15" height="15" fill="currentColor" viewBox="0 0 16 16"><path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/><path d="M4 4h8v1H4V4zm0 3h8v1H4V7zm0 3h5v1H4v-1z"/></svg> Blank Pad Mode (खाली बिल फारम)`;
                btn.classList.remove('bg-slate-700');
            }
        }
    </script>
</body>
</html>
