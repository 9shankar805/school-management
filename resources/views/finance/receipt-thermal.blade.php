<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>POS Receipt #{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 3mm;
        }
        body {
            font-family: 'Courier New', Courier, monospace, sans-serif;
            font-size: 11px;
            color: #000;
            margin: 0;
            padding: 4px;
            line-height: 1.3;
        }
        .text-center { text-align: center; }
        .text-right  { text-align: right; }
        .bold        { font-weight: bold; }
        .dashed-line { border-top: 1px dashed #000; margin: 5px 0; }
        .double-line { border-top: 1px double #000; margin: 6px 0; }
        .header h2   { margin: 0; font-size: 14px; font-weight: bold; text-transform: uppercase; }
        .header p    { margin: 2px 0; font-size: 10px; }
        table        { width: 100%; border-collapse: collapse; font-size: 10.5px; }
        td           { padding: 2px 0; }
        .total-box   { font-size: 13px; font-weight: bold; margin: 4px 0; }
    </style>
</head>
<body onload="window.print()">

@php
    $student = $payment->invoice?->student;
    $invoice = $payment->invoice;
    $school = $school ?? \App\Models\School::find($invoice?->school_id ?? $student?->school_id) ?? null;
    $schoolName = $school->name ?? config('app.name', 'Nepal School Hub');
    $schoolAddress = $school->address ?? 'Kathmandu, Nepal';
    $schoolPan = $school->pan_number ?? $school->vat_number ?? 'N/A';
    $schoolPhone = $school->phone ?? '';

    $items = $invoice?->items ?? collect();
@endphp

<div class="header text-center">
    <h2>{{ $schoolName }}</h2>
    <p>{{ $schoolAddress }}</p>
    @if($schoolPhone)<p>Tel: {{ $schoolPhone }}</p>@endif
    <p class="bold">PAN: {{ $schoolPan }}</p>
    <div class="dashed-line"></div>
    <div class="bold" style="font-size:12px;">FEE PAYMENT RECEIPT</div>
</div>

<div class="dashed-line"></div>

<table>
    <tr>
        <td>Receipt #:</td>
        <td class="text-right bold">{{ $payment->receipt_number ?? ('#' . str_pad($payment->id, 6, '0', STR_PAD_LEFT)) }}</td>
    </tr>
    <tr>
        <td>Date:</td>
        <td class="text-right">{{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d H:i') }}</td>
    </tr>
    <tr>
        <td>Student:</td>
        <td class="text-right bold">{{ $student->full_name ?? ($student->first_name . ' ' . $student->last_name) }}</td>
    </tr>
    <tr>
        <td>Roll / Adm:</td>
        <td class="text-right">{{ $student->admission_no ?? $student->roll_no ?? '—' }}</td>
    </tr>
    <tr>
        <td>Pay Method:</td>
        <td class="text-right bold">{{ strtoupper($payment->payment_method ?? 'CASH') }}</td>
    </tr>
    @if($payment->bankAccount)
    <tr>
        <td>Bank:</td>
        <td class="text-right">{{ $payment->bankAccount->bank_name }}</td>
    </tr>
    @endif
    @if($payment->gateway_transaction_id)
    <tr>
        <td>Trace / Ref:</td>
        <td class="text-right font-mono">{{ $payment->gateway_transaction_id }}</td>
    </tr>
    @endif
</table>

<div class="dashed-line"></div>

<table>
    <thead>
        <tr class="bold">
            <td style="width:70%;">Particulars</td>
            <td style="width:30%;" class="text-right">Amount</td>
        </tr>
    </thead>
    <tbody>
        @if($items->count() > 0)
            @foreach($items as $item)
            <tr>
                <td>{{ \Illuminate\Support\Str::limit($item->description ?? $item->name ?? $item->feeType?->name ?? 'Fee', 18) }}</td>
                <td class="text-right">Rs.{{ number_format((float)($item->total ?? $item->amount ?? $item->unit_price ?? 0), 2) }}</td>
            </tr>
            @endforeach
        @else
            <tr>
                <td>{{ \Illuminate\Support\Str::limit($invoice->title ?? 'Tuition Fee', 18) }}</td>
                <td class="text-right">Rs.{{ number_format($payment->amount, 2) }}</td>
            </tr>
        @endif
    </tbody>
</table>

<div class="double-line"></div>

<table>
    <tr class="total-box">
        <td>TOTAL PAID:</td>
        <td class="text-right">NPR {{ number_format($payment->amount, 2) }}</td>
    </tr>
    @if(isset($invoice) && $invoice->amount_due > 0)
    <tr style="font-size:10px; color:#666;">
        <td>Remaining Due:</td>
        <td class="text-right bold">Rs. {{ number_format($invoice->amount_due, 2) }}</td>
    </tr>
    @endif
</table>

<div class="dashed-line"></div>

<div class="text-center" style="font-size:9.5px; margin-top:8px;">
    <div>*** THANK YOU ***</div>
    <div>Keep receipt for your records.</div>
    <div style="margin-top:4px; font-size:8px;">Software by NepalSchoolHub ERP</div>
</div>

</body>
</html>
