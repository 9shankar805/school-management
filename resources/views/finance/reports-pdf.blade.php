<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }} — {{ $school?->name ?? config('app.name') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9.5px; color: #1e293b; margin: 0; padding: 0; }
        
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; border-bottom: 2px solid #0D7C66; padding-bottom: 8px; }
        .header-logo { width: 15%; vertical-align: middle; text-align: left; }
        .header-details { width: 63%; text-align: center; vertical-align: middle; }
        .header-badge { width: 22%; text-align: right; vertical-align: middle; }

        .school-name { font-size: 16px; font-weight: bold; color: #0D7C66; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; }
        .school-info { font-size: 8.5px; color: #475569; margin-bottom: 2px; }
        .school-tax { font-size: 8px; color: #64748b; font-weight: bold; }

        .report-badge-box { border: 1.5px solid #cbd5e1; border-radius: 4px; padding: 5px 8px; background: #f8fafc; display: inline-block; text-align: right; }
        .report-badge-type { font-size: 7.5px; color: #64748b; font-weight: bold; text-transform: uppercase; }
        .report-badge-title { font-size: 11px; font-weight: bold; color: #0f172a; }

        .meta-bar { background: #f1f5f9; padding: 5px 8px; border-radius: 4px; border-left: 3px solid #0D7C66; font-size: 8.5px; color: #334155; margin-bottom: 10px; }
        .meta-bar strong { color: #0f172a; }

        table.data-table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.data-table th { background: #0D7C66; color: #ffffff; padding: 5px 6px; text-align: left; font-size: 8.5px; font-weight: bold; text-transform: uppercase; border: 1px solid #0b6754; }
        table.data-table td { padding: 4.5px 6px; border: 1px solid #e2e8f0; font-size: 8.5px; }
        table.data-table tr:nth-child(even) td { background: #f8fafc; }
        table.data-table .text-right { text-align: right; }
        table.data-table .text-center { text-align: center; }
        table.data-table .font-bold { font-weight: bold; }

        .summary-total { font-weight: bold; background: #e6f7f3 !important; color: #064e3b; }

        .footer { position: fixed; bottom: 0; left: 0; right: 0; font-size: 8px; color: #64748b; text-align: center; border-top: 1px solid #cbd5e1; padding: 4px; background: #fff; }
    </style>
</head>
<body>

@php
    $schoolName = $school?->name ?? config('app.name', 'Nepal School Hub');
    $schoolAddress = $school?->address ?? 'Kathmandu, Nepal';
    $schoolPhone = $school?->phone ?? $school?->contact_number ?? '';
    $schoolEmail = $school?->email ?? '';
    $schoolPan = $school?->pan_number ?? $school?->vat_number ?? '';
    $schoolIemis = $school?->iemis_code ?? '';
@endphp

{{-- Institutional Header with Logo --}}
<table class="header-table">
    <tr>
        <td class="header-logo">
            @if(!empty($logoSrc))
                <img src="{{ $logoSrc }}" style="max-height: 52px; max-width: 90px; object-fit: contain;" alt="Logo">
            @else
                <div style="font-size: 13px; font-weight: bold; color: #0D7C66; border: 2px solid #0D7C66; border-radius: 4px; padding: 8px 10px; text-align: center; display: inline-block; background: #e6f7f3;">
                    {{ strtoupper(substr($schoolName, 0, 6)) }}
                </div>
            @endif
        </td>
        <td class="header-details">
            <div class="school-name">{{ $schoolName }}</div>
            <div class="school-info">
                {{ $schoolAddress }}
                @if($schoolPhone) &bull; Tel: {{ $schoolPhone }} @endif
                @if($schoolEmail) &bull; Email: {{ $schoolEmail }} @endif
            </div>
            <div class="school-tax">
                @if($schoolPan) PAN/VAT: {{ $schoolPan }} &bull; @endif
                @if($schoolIemis) CEHRD EMIS: {{ $schoolIemis }} &bull; @endif
                Institutional Financial Accounting Ledger
            </div>
        </td>
        <td class="header-badge">
            <div class="report-badge-box">
                <div class="report-badge-type">Verified Finance Report</div>
                <div class="report-badge-title">{{ $title }}</div>
                <div style="font-size: 7.5px; color: #0D7C66; font-weight: bold; margin-top: 2px;">OFFICIAL RECORD</div>
            </div>
        </td>
    </tr>
</table>

{{-- Meta Info Bar --}}
<div class="meta-bar">
    <strong>Generated:</strong> {{ now()->format('d M Y H:i') }}
    @if($dateFrom || $dateTo)
        &nbsp;|&nbsp; <strong>Period:</strong> {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d M Y') : 'Beginning' }} to {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d M Y') : 'Present' }}
    @endif
    &nbsp;|&nbsp; <strong>Total Records:</strong> {{ is_countable($records) ? count($records) : '—' }}
</div>

{{-- Report Tables --}}
@if($type === 'fee_collection')
@php $totalCollection = 0; @endphp
<table class="data-table">
    <thead>
        <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 25%;">Student Name</th>
            <th style="width: 25%;">Invoice Particulars</th>
            <th style="width: 15%;" class="text-right">Amount Paid</th>
            <th style="width: 15%;" class="text-center">Payment Date</th>
            <th style="width: 15%;" class="text-center">Method</th>
        </tr>
    </thead>
    <tbody>
        @forelse($records as $i => $p)
        @php $totalCollection += (float)$p->amount; @endphp
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td class="font-bold">{{ $p->invoice?->student?->first_name ?? '—' }} {{ $p->invoice?->student?->last_name ?? '' }}</td>
            <td>{{ $p->invoice?->title ?? 'Student Fee Payment' }}</td>
            <td class="text-right font-bold">Rs. {{ number_format($p->amount, 2) }}</td>
            <td class="text-center">{{ \Carbon\Carbon::parse($p->payment_date)->format('d M Y') }}</td>
            <td class="text-center">{{ strtoupper($p->payment_method ?? 'CASH') }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="text-center" style="padding: 12px; color: #64748b;">No fee collection records found for the selected period.</td>
        </tr>
        @endforelse
    </tbody>
    @if(count($records) > 0)
    <tfoot>
        <tr class="summary-total">
            <td colspan="3" class="text-right font-bold">CUMULATIVE TOTAL COLLECTED:</td>
            <td class="text-right font-bold">Rs. {{ number_format($totalCollection, 2) }}</td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
    @endif
</table>

@elseif($type === 'expenses')
@php $totalExpense = 0; @endphp
<table class="data-table">
    <thead>
        <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 12%;" class="text-center">Date</th>
            <th style="width: 28%;">Title / Particulars</th>
            <th style="width: 15%;">Category</th>
            <th style="width: 15%;" class="text-right">Amount</th>
            <th style="width: 12%;" class="text-center">Method</th>
            <th style="width: 13%;">Reference / UTR</th>
        </tr>
    </thead>
    <tbody>
        @forelse($records as $i => $e)
        @php $totalExpense += (float)$e->amount; @endphp
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td class="text-center">{{ $e->expense_date->format('d M Y') }}</td>
            <td class="font-bold">{{ $e->title }}</td>
            <td>{{ $e->category }}</td>
            <td class="text-right font-bold">Rs. {{ number_format($e->amount, 2) }}</td>
            <td class="text-center">{{ strtoupper($e->payment_method) }}</td>
            <td>{{ $e->reference ?? '—' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="text-center" style="padding: 12px; color: #64748b;">No expense records found for the selected period.</td>
        </tr>
        @endforelse
    </tbody>
    @if(count($records) > 0)
    <tfoot>
        <tr class="summary-total">
            <td colspan="4" class="text-right font-bold">TOTAL EXPENSES:</td>
            <td class="text-right font-bold">Rs. {{ number_format($totalExpense, 2) }}</td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
    @endif
</table>

@elseif($type === 'income')
@php $totalIncome = 0; @endphp
<table class="data-table">
    <thead>
        <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 15%;" class="text-center">Date</th>
            <th style="width: 35%;">Title / Income Source</th>
            <th style="width: 20%;">Category</th>
            <th style="width: 15%;" class="text-right">Amount</th>
            <th style="width: 10%;" class="text-center">Method</th>
        </tr>
    </thead>
    <tbody>
        @forelse($records as $i => $r)
        @php $totalIncome += (float)$r->amount; @endphp
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td class="text-center">{{ $r->income_date->format('d M Y') }}</td>
            <td class="font-bold">{{ $r->title }}</td>
            <td>{{ $r->category }}</td>
            <td class="text-right font-bold">Rs. {{ number_format($r->amount, 2) }}</td>
            <td class="text-center">{{ strtoupper($r->payment_method) }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="text-center" style="padding: 12px; color: #64748b;">No income records found for the selected period.</td>
        </tr>
        @endforelse
    </tbody>
    @if(count($records) > 0)
    <tfoot>
        <tr class="summary-total">
            <td colspan="4" class="text-right font-bold">TOTAL REVENUE:</td>
            <td class="text-right font-bold">Rs. {{ number_format($totalIncome, 2) }}</td>
            <td></td>
        </tr>
    </tfoot>
    @endif
</table>

@elseif($type === 'outstanding')
@php $totalDue = 0; @endphp
<table class="data-table">
    <thead>
        <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 30%;">Student Name</th>
            <th style="width: 30%;">Invoice Particulars</th>
            <th style="width: 15%;" class="text-right">Outstanding Due</th>
            <th style="width: 10%;" class="text-center">Due Date</th>
            <th style="width: 10%;" class="text-center">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($records as $i => $inv)
        @php $totalDue += (float)$inv->amount; @endphp
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td class="font-bold">{{ $inv->student?->first_name ?? '—' }} {{ $inv->student?->last_name ?? '' }}</td>
            <td>{{ $inv->title }}</td>
            <td class="text-right font-bold" style="color:#b91c1c;">Rs. {{ number_format($inv->amount, 2) }}</td>
            <td class="text-center">{{ $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('d M Y') : '—' }}</td>
            <td class="text-center" style="color:#b91c1c; font-weight: bold;">{{ strtoupper($inv->status) }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="text-center" style="padding: 12px; color: #166534;">All student dues are settled! Zero outstanding dues.</td>
        </tr>
        @endforelse
    </tbody>
    @if(count($records) > 0)
    <tfoot>
        <tr class="summary-total" style="background:#fee2e2 !important; color:#991b1b;">
            <td colspan="3" class="text-right font-bold">TOTAL OUTSTANDING DUES:</td>
            <td class="text-right font-bold">Rs. {{ number_format($totalDue, 2) }}</td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
    @endif
</table>

@elseif($type === 'ledger')
@php
    $fees     = (float)($records['fees'] ?? 0);
    $income   = (float)($records['income'] ?? 0);
    $expenses = (float)($records['expenses'] ?? 0);
    $net      = ($fees + $income) - $expenses;
@endphp
<table class="data-table">
    <thead>
        <tr>
            <th style="width: 70%;">Accounting Ledger Head</th>
            <th style="width: 30%;" class="text-right">Net Amount (NPR)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Fee Collections Received (Fee Ledger)</td>
            <td class="text-right font-bold" style="color: #065f46;">Rs. {{ number_format($fees, 2) }}</td>
        </tr>
        <tr>
            <td>Other Institutional Revenue / Income</td>
            <td class="text-right font-bold" style="color: #065f46;">Rs. {{ number_format($income, 2) }}</td>
        </tr>
        <tr>
            <td>Operational Expenses & Disbursements</td>
            <td class="text-right font-bold" style="color: #b91c1c;">- Rs. {{ number_format($expenses, 2) }}</td>
        </tr>
    </tbody>
    <tfoot>
        <tr class="summary-total" style="font-size: 10px;">
            <td class="font-bold">NET INSTITUTIONAL SURPLUS / BALANCE:</td>
            <td class="text-right font-bold" style="color: {{ $net >= 0 ? '#064e3b' : '#991b1b' }};">
                Rs. {{ number_format($net, 2) }}
            </td>
        </tr>
    </tfoot>
</table>
@endif

<div class="footer">
    {{ $schoolName }} &bull; Institutional Financial Accounting Statement &bull; Generated: {{ now()->format('d M Y H:i') }} &bull; Confidential
</div>

</body>
</html>
