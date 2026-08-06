<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; margin: 0; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #1a3c6e; padding-bottom: 12px; margin-bottom: 16px; }
        .header h1 { font-size: 18px; color: #1a3c6e; margin: 0 0 4px; }
        .header p  { margin: 0; color: #666; font-size: 10px; }
        .title { text-align: center; font-size: 14px; font-weight: bold; color: #1a3c6e; letter-spacing: 2px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 6px 8px; }
        .label { color: #666; width: 40%; }
        .value { font-weight: bold; }
        .amount-box { background: #1a3c6e; color: #fff; text-align: center; padding: 14px; border-radius: 6px; margin: 16px 0; }
        .amount-box .num { font-size: 22px; font-weight: bold; }
        .footer { text-align: center; margin-top: 24px; font-size: 9px; color: #999; border-top: 1px solid #eee; padding-top: 8px; }
        .badge-paid { background: #198754; color: #fff; padding: 3px 10px; border-radius: 12px; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('app.name') }}</h1>
        <p>School Management System</p>
    </div>
    <div class="title">PAYMENT RECEIPT</div>

    <table>
        <tr><td class="label">Receipt No.</td><td class="value">#{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</td></tr>
        <tr><td class="label">Student</td><td class="value">{{ $payment->invoice->student->first_name ?? '—' }} {{ $payment->invoice->student->last_name ?? '' }}</td></tr>
        <tr><td class="label">Invoice Title</td><td class="value">{{ $payment->invoice->title ?? '—' }}</td></tr>
        <tr><td class="label">Payment Date</td><td class="value">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td></tr>
        <tr><td class="label">Payment Method</td><td class="value">{{ ucfirst($payment->payment_method ?? 'Cash') }}</td></tr>
        <tr><td class="label">Status</td><td><span class="badge-paid">PAID</span></td></tr>
    </table>

    <div class="amount-box">
        <div style="font-size:11px;letter-spacing:1px">AMOUNT PAID</div>
        <div class="num">Rs. {{ number_format($payment->amount, 2) }}</div>
    </div>

    <div class="footer">
        This is a computer-generated receipt. &nbsp;|&nbsp; Generated: {{ now()->format('d M Y H:i') }}<br>
        {{ config('app.name') }} — School Management System
    </div>
</body>
</html>
