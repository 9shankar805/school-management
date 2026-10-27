<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Day Book - {{ $date }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: DejaVu Sans, sans-serif; }
        body { font-size: 10px; color: #1e293b; padding: 24px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #334155; padding-bottom: 12px; }
        .school-name { font-size: 18px; font-weight: bold; text-transform: uppercase; color: #0f172a; }
        .school-sub { font-size: 10px; color: #64748b; margin-top: 3px; }
        .doc-title { font-size: 14px; font-weight: bold; margin-top: 10px; color: #4338ca; text-transform: uppercase; }
        .date-badge { font-size: 11px; margin-top: 4px; color: #334155; font-weight: bold; }
        
        .summary-box { width: 100%; margin-bottom: 16px; border: 1px solid #cbd5e1; border-collapse: collapse; }
        .summary-box td { padding: 6px 10px; border: 1px solid #cbd5e1; font-size: 10px; }
        .summary-label { font-weight: bold; background: #f8fafc; color: #475569; width: 25%; }
        .summary-val { font-weight: bold; text-align: right; width: 25%; }

        .columns { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .col-cell { width: 50%; vertical-align: top; }
        .col-left { padding-right: 8px; }
        .col-right { padding-left: 8px; }

        .table-title { font-size: 11px; font-weight: bold; padding: 6px 8px; text-transform: uppercase; border-radius: 4px 4px 0 0; }
        .receipt-header { background: #dcfce7; color: #166534; }
        .payment-header { background: #ffe4e6; color: #9f1239; }

        .data-table { width: 100%; border-collapse: collapse; border: 1px solid #cbd5e1; }
        .data-table th { font-size: 9px; padding: 5px; background: #f1f5f9; text-align: left; border-bottom: 1px solid #cbd5e1; }
        .data-table td { font-size: 9px; padding: 4px 6px; border-bottom: 1px solid #f1f5f9; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        .signatures { margin-top: 40px; width: 100%; display: table; }
        .sign-cell { display: table-cell; width: 33.33%; text-align: center; }
        .sign-line { border-top: 1px solid #475569; width: 70%; margin: 0 auto; padding-top: 4px; font-weight: bold; font-size: 9px; }
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
                Daily Transaction Register
            </div>
            <div style="font-size: 12.5px; font-weight: bold; color: #4338ca; text-transform: uppercase; margin-top: 3px;">
                DAILY DAY BOOK / रोजनाम्चा (CASH &amp; BANK REGISTER)
            </div>
            <div style="font-size: 10px; margin-top: 2px; color: #1e293b; font-weight: bold;">
                नेपाली मिति: {{ $bsDateNp }} ({{ $bsDateEn }} BS) &bull; {{ $dayOfWeekNp }} &bull; {{ $currentFyNp }}
            </div>
            <div style="font-size: 8.5px; color: #64748b; margin-top: 2px;">
                English Date: {{ \Carbon\Carbon::parse($date)->format('F j, Y (l)') }}
            </div>
        </td>
        <td style="width: 16%; text-align: right; vertical-align: middle;">
            <div style="border: 1.5px solid #cbd5e1; border-radius: 4px; padding: 5px 8px; background: #f8fafc; display: inline-block; text-align: center;">
                <div style="font-size: 7.5px; color: #64748b; font-weight: bold; text-transform: uppercase;">Register Date</div>
                <div style="font-size: 10px; font-weight: bold; color: #4338ca;">{{ $bsDateNp }}</div>
                <div style="font-size: 7px; color: #059669; font-weight: bold; margin-top: 2px;">DAY BOOK</div>
            </div>
        </td>
    </tr>
</table>

    {{-- Summary Balance Table --}}
    <table class="summary-box">
        <tr>
            <td class="summary-label">Opening Balance:</td>
            <td class="summary-val">Rs. {{ number_format($openingBalance, 2) }}</td>
            <td class="summary-label">Total Outflows (खर्च):</td>
            <td class="summary-val" style="color: #be123c;">Rs. {{ number_format($totalOutflow, 2) }}</td>
        </tr>
        <tr>
            <td class="summary-label">Total Inflows (आम्दानी):</td>
            <td class="summary-val" style="color: #15803d;">Rs. {{ number_format($totalInflow, 2) }}</td>
            <td class="summary-label">Closing Balance (मौज्दात):</td>
            <td class="summary-val" style="color: #4338ca; font-size: 11px;">Rs. {{ number_format($closingBalance, 2) }}</td>
        </tr>
    </table>

    {{-- 2-Column Ledger: Receipts vs Payments --}}
    <table class="columns">
        <tr>
            {{-- Receipts --}}
            <td class="col-cell col-left">
                <div class="table-title receipt-header">RECEIPTS / INFLOWS (आम्दानी)</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Ref / Student</th>
                            <th>Mode</th>
                            <th class="text-right">Amount (NPR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $p)
                        <tr>
                            <td>
                                <b>{{ $p->invoice->student->full_name ?? 'Student' }}</b><br>
                                <span style="color:#64748b; font-size:8px;">Rec #{{ $p->receipt_number ?? $p->id }} ({{ $p->invoice->schoolClass->name ?? '' }})</span>
                            </td>
                            <td>{{ strtoupper($p->payment_method ?? 'CASH') }}</td>
                            <td class="text-right font-bold">{{ number_format($p->amount, 2) }}</td>
                        </tr>
                        @empty
                        @endforelse

                        @foreach($incomes as $inc)
                        <tr>
                            <td>
                                <b>{{ $inc->title }}</b><br>
                                <span style="color:#64748b; font-size:8px;">Other: {{ $inc->category }}</span>
                            </td>
                            <td>{{ strtoupper($inc->payment_method ?? 'CASH') }}</td>
                            <td class="text-right font-bold">{{ number_format($inc->amount, 2) }}</td>
                        </tr>
                        @endforeach

                        @if($payments->isEmpty() && $incomes->isEmpty())
                        <tr><td colspan="3" style="text-align:center; padding:12px; color:#94a3b8;">No receipts</td></tr>
                        @endif
                    </tbody>
                    <tfoot>
                        <tr style="background:#f8fafc; font-weight:bold;">
                            <td colspan="2">TOTAL RECEIPTS</td>
                            <td class="text-right" style="color:#15803d;">Rs. {{ number_format($totalInflow, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </td>

            {{-- Payments --}}
            <td class="col-cell col-right">
                <div class="table-title payment-header">PAYMENTS / OUTFLOWS (खर्च)</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Particulars</th>
                            <th>Category</th>
                            <th class="text-right">Amount (NPR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $e)
                        <tr>
                            <td>
                                <b>{{ $e->title ?? $e->description }}</b><br>
                                <span style="color:#64748b; font-size:8px;">Paid to: {{ $e->paid_to ?? 'Vendor' }}</span>
                            </td>
                            <td>{{ $e->category ?? 'Expense' }}</td>
                            <td class="text-right font-bold">{{ number_format($e->amount, 2) }}</td>
                        </tr>
                        @empty
                        @endforelse

                        @foreach($teacherPayrolls as $tp)
                        <tr>
                            <td>
                                <b>{{ $tp->teacher->full_name ?? 'Teacher' }}</b><br>
                                <span style="color:#64748b; font-size:8px;">Teacher Payroll</span>
                            </td>
                            <td>Salary</td>
                            <td class="text-right font-bold">{{ number_format($tp->net_salary, 2) }}</td>
                        </tr>
                        @endforeach

                        @foreach($staffPayrolls as $sp)
                        <tr>
                            <td>
                                <b>{{ $sp->user->full_name ?? 'Staff' }}</b><br>
                                <span style="color:#64748b; font-size:8px;">Slip #{{ $sp->id }}</span>
                            </td>
                            <td>Salary</td>
                            <td class="text-right font-bold">{{ number_format($sp->net_salary, 2) }}</td>
                        </tr>
                        @endforeach

                        @if($expenses->isEmpty() && $teacherPayrolls->isEmpty() && $staffPayrolls->isEmpty())
                        <tr><td colspan="3" style="text-align:center; padding:12px; color:#94a3b8;">No expenses</td></tr>
                        @endif
                    </tbody>
                    <tfoot>
                        <tr style="background:#f8fafc; font-weight:bold;">
                            <td colspan="2">TOTAL OUTFLOWS</td>
                            <td class="text-right" style="color:#be123c;">Rs. {{ number_format($totalOutflow, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </td>
        </tr>
    </table>

    @if(isset($closing) && $closing)
    {{-- Physical Cash Denomination Summary --}}
    <div style="margin-top: 14px; border: 1px solid #cbd5e1; border-radius: 4px; overflow: hidden;">
        <div style="background: #f1f5f9; padding: 5px 8px; font-weight: bold; font-size: 9.5px; text-transform: uppercase;">
            भौतिक नगद दरबन्दी तथा हिसाब मिलान (PHYSICAL CASH COUNT &amp; CLOSING RECONCILIATION)
            <span style="float: right; color: #059669; font-weight: bold;">[🔒 DAY BOOK LOCKED]</span>
        </div>
        <table style="width: 100%; border-collapse: collapse; font-size: 8.5px;">
            <tr>
                <td style="width: 50%; vertical-align: top; padding: 6px; border-right: 1px solid #cbd5e1;">
                    <div style="font-weight: bold; margin-bottom: 4px; color: #475569;">Banknote Breakdown:</div>
                    @php
                        $denoms = $closing->denominations ?? [];
                        $denomParts = [];
                        foreach ([1000, 500, 100, 50, 20, 10, 5, 2, 1] as $d) {
                            $q = (int)($denoms[(string)$d] ?? 0);
                            if ($q > 0) {
                                $denomParts[] = "रू {$d} × {$q} = Rs. " . number_format($q * $d);
                            }
                        }
                        if (!empty($denoms['coins']) && (float)$denoms['coins'] > 0) {
                            $denomParts[] = "सिक्का/Coins = Rs. " . number_format((float)$denoms['coins'], 2);
                        }
                    @endphp
                    @if(count($denomParts) > 0)
                        <div>{{ implode(' &nbsp;·&nbsp; ', $denomParts) }}</div>
                    @else
                        <div style="color: #94a3b8;">कुनै विवरण छैन (No denomination record)</div>
                    @endif
                </td>
                <td style="width: 50%; vertical-align: top; padding: 6px;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 8.5px;">
                        <tr>
                            <td style="border: none; padding: 2px 0; color: #475569;">अपेक्षित नगद (Expected Cash):</td>
                            <td style="border: none; padding: 2px 0; text-align: right; font-weight: bold;">Rs. {{ number_format($closing->expected_cash, 2) }}</td>
                        </tr>
                        <tr>
                            <td style="border: none; padding: 2px 0; color: #475569;">दाखिला गरिएको भौतिक नगद (Counted Cash):</td>
                            <td style="border: none; padding: 2px 0; text-align: right; font-weight: bold; color: #059669;">Rs. {{ number_format($closing->actual_cash, 2) }}</td>
                        </tr>
                        <tr>
                            <td style="border: none; padding: 2px 0; color: #475569;">फरक रकम (Difference):</td>
                            <td style="border: none; padding: 2px 0; text-align: right; font-weight: bold; color: {{ $closing->difference == 0 ? '#059669' : ($closing->difference > 0 ? '#2563eb' : '#dc2626') }};">
                                {{ $closing->difference >= 0 ? '+' : '' }}Rs. {{ number_format($closing->difference, 2) }}
                                {{ $closing->difference == 0 ? '(Exact Match)' : ($closing->difference > 0 ? '(Surplus)' : '(Short)') }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    @endif

    {{-- Official Signatures --}}
    <div class="signatures">
        <div class="sign-cell">
            <div class="sign-line">Prepared By (Cashier / Accountant)</div>
        </div>
        <div class="sign-cell">
            <div class="sign-line">Verified By (Accounts Officer)</div>
        </div>
        <div class="sign-cell">
            <div class="sign-line">Approved By (Principal / Director)</div>
        </div>
    </div>

</body>
</html>
