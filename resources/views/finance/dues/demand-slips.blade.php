<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Demand Slips (शुल्क माग भौचर) — {{ $class?->class_name ?? 'All Classes' }} ({{ $periodLabelNp }})</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, sans-serif;
            background: #f1f5f9;
            color: #0f172a;
            font-size: 11px;
            line-height: 1.25;
        }

        /* ── Screen Action Bar ── */
        .action-bar {
            background: #ffffff;
            border-bottom: 1px solid #cbd5e1;
            padding: 10px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .action-left { display: flex; align-items: center; gap: 12px; }
        .action-right { display: flex; align-items: center; gap: 10px; }

        .btn-action {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 12px; font-weight: 700; padding: 7px 14px;
            border-radius: 8px; border: 1px solid #cbd5e1;
            background: #f8fafc; color: #334155; text-decoration: none;
            cursor: pointer; transition: 0.15s;
        }
        .btn-action:hover { background: #e2e8f0; color: #0f172a; }

        .layout-btn-group {
            display: inline-flex;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            overflow: hidden;
        }
        .layout-btn {
            font-size: 11px; font-weight: 700; padding: 6px 12px;
            background: #f8fafc; color: #475569; border: none;
            cursor: pointer; border-right: 1px solid #cbd5e1; text-decoration: none;
        }
        .layout-btn:last-child { border-right: none; }
        .layout-btn.active {
            background: #0f766e; color: #ffffff;
        }

        .btn-print {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 12px; font-weight: 800; color: #ffffff;
            padding: 8px 20px; border-radius: 9px; border: none;
            background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%);
            cursor: pointer; box-shadow: 0 2px 8px rgba(13, 148, 136, 0.35);
            transition: 0.15s;
        }
        .btn-print:hover { background: linear-gradient(135deg, #115e59 0%, #0f766e 100%); }

        .meta-pill {
            font-size: 11px; font-weight: 600; background: #e6fffa;
            color: #0f766e; border: 1px solid #99f6e4;
            padding: 4px 10px; border-radius: 20px;
        }

        /* ── Sheet Container ── */
        .sheets-wrapper {
            max-width: 210mm;
            margin: 20px auto;
        }

        .sheet-page {
            width: 210mm;
            height: 297mm;
            background: #ffffff;
            margin: 0 auto 20px auto;
            padding: 5mm 6mm;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            page-break-after: always;
            break-after: page;
            display: grid;
            gap: 4mm;
        }

        /* ── 4 Slips Per Page (2 x 2) ── */
        .sheet-page.layout-4 {
            grid-template-columns: repeat(2, 1fr);
            grid-template-rows: repeat(2, 1fr);
            height: 297mm;
        }

        /* ── 6 Slips Per Page (2 x 3) ── */
        .sheet-page.layout-6 {
            grid-template-columns: repeat(2, 1fr);
            grid-template-rows: repeat(3, 1fr);
            height: 297mm;
        }

        /* ── Demand Slip Card ── */
        .demand-slip {
            border: 1px dashed #64748b;
            border-radius: 5px;
            padding: 3.5mm 4mm;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .scissors-tag {
            position: absolute;
            top: 2px;
            right: 4px;
            font-size: 9px;
            color: #94a3b8;
        }

        /* ── Slip Header ── */
        .slip-header {
            text-align: center;
            border-bottom: 1.5px solid #0f766e;
            padding-bottom: 2mm;
            margin-bottom: 2mm;
        }
        .slip-school-name {
            font-size: 11pt;
            font-weight: 900;
            color: #0f766e;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            line-height: 1.1;
        }
        .slip-school-meta {
            font-size: 7pt;
            color: #64748b;
            margin-top: 1px;
        }
        .slip-title-badge {
            display: inline-block;
            background: #f0fdfa;
            color: #0f766e;
            border: 0.5px solid #99f6e4;
            font-size: 7.5pt;
            font-weight: 800;
            padding: 1px 7px;
            border-radius: 3px;
            margin-top: 2px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* ── Student Meta Grid ── */
        .student-grid {
            display: table;
            width: 100%;
            font-size: 7.5pt;
            background: #f8fafc;
            border: 0.5px solid #e2e8f0;
            border-radius: 3px;
            padding: 2mm 2.5mm;
            margin-bottom: 2mm;
        }
        .student-row { display: table-row; }
        .student-cell {
            display: table-cell;
            padding: 1px 3px;
            vertical-align: middle;
        }
        .lbl { color: #64748b; font-size: 6.5pt; text-transform: uppercase; }
        .val { font-weight: 700; color: #0f172a; }

        /* ── Particulars Table ── */
        .fee-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
            margin-bottom: 2mm;
        }
        .fee-table th {
            background: #f1f5f9;
            color: #334155;
            padding: 2px 4px;
            font-weight: 800;
            border-top: 0.5px solid #cbd5e1;
            border-bottom: 0.5px solid #cbd5e1;
            text-align: left;
            font-size: 7pt;
            text-transform: uppercase;
        }
        .fee-table td {
            padding: 2px 4px;
            border-bottom: 0.5px solid #f1f5f9;
            vertical-align: middle;
        }
        .fee-table td.amt { text-align: right; font-weight: 700; }

        .row-dues {
            background: #fffbeb !important;
            color: #b45309;
            font-weight: 800;
        }
        .row-total {
            background: #f0fdfa !important;
            color: #0f766e;
            font-weight: 900;
            font-size: 8.5pt;
            border-top: 1px solid #0d9488 !important;
            border-bottom: 1px solid #0d9488 !important;
        }

        /* ── Due Date Banner ── */
        .due-banner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fef2f2;
            border: 0.5px solid #fecaca;
            border-radius: 3px;
            padding: 2px 6px;
            font-size: 7pt;
            margin-bottom: 2mm;
        }
        .due-title { color: #991b1b; font-weight: 800; text-transform: uppercase; }
        .due-date { color: #dc2626; font-weight: 900; }

        /* ── Slip Footer ── */
        .slip-footer {
            border-top: 0.5px solid #cbd5e1;
            padding-top: 2mm;
            display: table;
            width: 100%;
            font-size: 6.5pt;
            color: #475569;
        }
        .footer-left { display: table-cell; vertical-align: bottom; width: 68%; }
        .footer-right { display: table-cell; vertical-align: bottom; width: 32%; text-align: right; }
        .sig-box {
            border-top: 0.5px solid #334155;
            padding-top: 2px;
            font-weight: 800;
            color: #1e293b;
            text-align: center;
            width: 32mm;
            margin-left: auto;
        }

        /* ── Compact adjustments for 6 Slips Layout ── */
        .layout-6 .demand-slip { padding: 2mm 3mm; }
        .layout-6 .slip-school-name { font-size: 9.5pt; }
        .layout-6 .student-grid { padding: 1mm 2mm; margin-bottom: 1.5mm; font-size: 7pt; }
        .layout-6 .fee-table { font-size: 7pt; margin-bottom: 1.5mm; }
        .layout-6 .fee-table th, .layout-6 .fee-table td { padding: 1.5px 3px; }
        .layout-6 .row-total { font-size: 7.5pt; }
        .layout-6 .due-banner { padding: 1.5px 4px; margin-bottom: 1.5mm; }
        .layout-6 .slip-footer { padding-top: 1mm; font-size: 6pt; }

        /* ── Print Styles ── */
        @media print {
            body { background: #ffffff !important; }
            .action-bar { display: none !important; }
            .sheets-wrapper { margin: 0 !important; max-width: 100% !important; }
            .sheet-page {
                box-shadow: none !important;
                margin: 0 !important;
                width: 100% !important;
                height: 100vh !important;
                padding: 4mm 5mm !important;
                page-break-after: always !important;
                break-after: page !important;
            }
            .demand-slip { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    {{-- ── Screen Action Bar ── --}}
    <div class="action-bar">
        <div class="action-left">
            <a href="javascript:history.back()" class="btn-action">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <span class="meta-pill">
                <i class="bi bi-calendar3 me-1"></i> {{ $periodLabelNp }}
            </span>
            <span class="meta-pill">
                <i class="bi bi-mortarboard me-1"></i> {{ $class?->class_name ?? 'All Classes' }} {{ $section ? '('.$section->section_name.')' : '' }}
            </span>
            <span class="meta-pill">
                <i class="bi bi-people me-1"></i> Total Slips: {{ count($slips) }}
            </span>
        </div>

        <div class="action-right">
            {{-- Layout Toggle (4 vs 6 per page) --}}
            <div class="layout-btn-group">
                <a href="{{ request()->fullUrlWithQuery(['layout' => '4_per_page']) }}"
                   class="layout-btn {{ $layout === '4_per_page' ? 'active' : '' }}" title="4 Slips on single A4 sheet (2x2)">
                    <i class="bi bi-grid-fill me-1"></i> 4 per Page (2×2)
                </a>
                <a href="{{ request()->fullUrlWithQuery(['layout' => '6_per_page']) }}"
                   class="layout-btn {{ $layout === '6_per_page' ? 'active' : '' }}" title="6 Slips on single A4 sheet (2x3)">
                    <i class="bi bi-grid-3x2-gap-fill me-1"></i> 6 per Page (2×3)
                </a>
            </div>

            <button onclick="window.print()" class="btn-print">
                <i class="bi bi-printer-fill"></i> Print Demand Slips
            </button>
        </div>
    </div>

    {{-- ── Sheet Pages Wrapper ── --}}
    <div class="sheets-wrapper">
        @php
            $slipsPerPage = ($layout === '6_per_page') ? 6 : 4;
            $chunks = array_chunk($slips, $slipsPerPage);
        @endphp

        @foreach($chunks as $pageIndex => $pageSlips)
        <div class="sheet-page {{ $layout === '6_per_page' ? 'layout-6' : 'layout-4' }}">

            @foreach($pageSlips as $slip)
            <div class="demand-slip">
                <div class="scissors-tag">✂ - - - - - -</div>

                {{-- Header --}}
                <div class="slip-header">
                    <div class="slip-school-name">{{ $school?->name ?? 'Nepal School Hub' }}</div>
                    <div class="slip-school-meta">
                        {{ $school?->address ?? 'Kathmandu, Nepal' }} · Ph: {{ $school?->phone ?? '01-4XXXXXX' }}
                        @if($school?->pan_number) · PAN: {{ $school->pan_number }} @endif
                    </div>
                    <div class="slip-title-badge">
                        {{ $slip['copy_label'] ?? 'शुल्क माग भौचर (Fee Demand Slip)' }}
                    </div>
                </div>

                {{-- Student Information --}}
                <div class="student-grid">
                    <div class="student-row">
                        <div class="student-cell" style="width: 55%;">
                            <span class="lbl">Student:</span> <span class="val">{{ $slip['student_name'] }}</span>
                        </div>
                        <div class="student-cell" style="width: 45%;">
                            <span class="lbl">Class / Sec:</span> <span class="val">{{ $slip['class_name'] }} {{ $slip['section_name'] ? '('.$slip['section_name'].')' : '' }}</span>
                        </div>
                    </div>
                    <div class="student-row">
                        <div class="student-cell">
                            <span class="lbl">Roll No:</span> <span class="val">{{ $slip['roll_no'] }}</span>
                            <span class="lbl" style="margin-left: 6px;">Adm:</span> <span class="val">{{ $slip['admission_no'] }}</span>
                        </div>
                        <div class="student-cell">
                            <span class="lbl">Parent Ph:</span> <span class="val">{{ $slip['guardian_phone'] ?: '—' }}</span>
                        </div>
                    </div>
                    <div class="student-row">
                        <div class="student-cell" colspan="2">
                            <span class="lbl">Billing Period:</span>
                            <span class="val" style="color: #0f766e;">{{ $slip['period_label_np'] }}</span>
                        </div>
                    </div>
                </div>

                {{-- Itemized Particulars --}}
                <table class="fee-table">
                    <thead>
                        <tr>
                            <th>Particulars (विवरण)</th>
                            <th style="text-align: right; width: 35%;">Amount (Rs.)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $rowCount = 0; $maxDisplay = ($layout === '6_per_page') ? 2 : 4; @endphp
                        @foreach($slip['items'] as $it)
                            @if($rowCount < $maxDisplay)
                            <tr>
                                <td>{{ \Illuminate\Support\Str::limit($it['name'], 28) }}</td>
                                <td class="amt">{{ number_format($it['amount'], 2) }}</td>
                            </tr>
                            @php $rowCount++; @endphp
                            @endif
                        @endforeach

                        @if(count($slip['items']) > $maxDisplay)
                            <tr>
                                <td style="font-style: italic; color: #64748b;">+ {{ count($slip['items']) - $maxDisplay }} other regular charges</td>
                                <td class="amt">—</td>
                            </tr>
                        @endif

                        {{-- Current Period Charges Subtotal --}}
                        <tr>
                            <td style="font-weight: 700; color: #334155;">Current Period Fee (चालु शुल्क)</td>
                            <td class="amt" style="color: #334155;">{{ number_format($slip['current_fee'], 2) }}</td>
                        </tr>

                        {{-- Previous Dues (बक्यौता रकम) --}}
                        @if($slip['previous_due'] > 0)
                        <tr class="row-dues">
                            <td>
                                ⚠️ Previous Dues (अघिल्लो बक्यौता)
                            </td>
                            <td class="amt">+ {{ number_format($slip['previous_due'], 2) }}</td>
                        </tr>
                        @endif

                        {{-- Fine Amount if any --}}
                        @if(!empty($slip['fine_amount']) && $slip['fine_amount'] > 0)
                        <tr style="color: #dc2626;">
                            <td>Late Fine (विलम्ब शुल्क)</td>
                            <td class="amt">+ {{ number_format($slip['fine_amount'], 2) }}</td>
                        </tr>
                        @endif

                        {{-- Grand Total Payable --}}
                        <tr class="row-total">
                            <td>TOTAL DUE (जम्मा बुझाउनुपर्ने)</td>
                            <td class="amt">रू {{ number_format($slip['total_payable'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>

                {{-- Due Date Banner --}}
                <div class="due-banner">
                    <span class="due-title">Last Date to Pay (अन्तिम मिति):</span>
                    <span class="due-date">{{ $slip['due_date_bs'] }} ({{ $slip['due_date_ad'] }})</span>
                </div>

                {{-- Slip Footer (Bank & Sign) --}}
                <div class="slip-footer">
                    <div class="footer-left">
                        @if($bankAccount)
                            <div><b>Bank:</b> {{ $bankAccount->bank_name }}</div>
                            <div><b>A/C No:</b> {{ $bankAccount->account_number }} · <b>Branch:</b> {{ $bankAccount->branch_name }}</div>
                            <div style="color: #0f766e; font-weight: 700;">Fonepay / eSewa Online Payment Accepted</div>
                        @else
                            <div>Please pay at the school cash counter or designated bank.</div>
                        @endif
                    </div>
                    <div class="footer-right">
                        <div class="sig-box">Accountant / Principal</div>
                    </div>
                </div>

            </div>
            @endforeach

            {{-- Fill empty slots if last page has fewer slips to maintain grid alignment --}}
            @for($i = count($pageSlips); $i < $slipsPerPage; $i++)
            <div class="demand-slip" style="border-style: dotted; border-color: #cbd5e1; opacity: 0.35;">
                <div style="text-align: center; margin-top: 30%; color: #94a3b8; font-weight: bold; font-size: 8pt;">
                    [ Blank Slot / खाली प्रति ]
                </div>
            </div>
            @endfor

        </div>
        @endforeach
    </div>

</body>
</html>

