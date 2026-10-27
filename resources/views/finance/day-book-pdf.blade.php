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

    <div class="header">
        <div class="school-name">{{ $school->name ?? 'SCHOOL MANAGEMENT' }}</div>
        <div class="school-sub">{{ $school->address ?? '' }} | Phone: {{ $school->phone ?? '' }}</div>
        <div class="doc-title">DAILY DAY BOOK / रोजनाम्चा (CASH & BANK REGISTER)</div>
        <div class="date-badge">
            Date: {{ \Carbon\Carbon::parse($date)->format('F j, Y') }} 
            @if(function_exists('bsDate'))
                ({{ bsDate($date) }})
            @endif
        </div>
    </div>

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
