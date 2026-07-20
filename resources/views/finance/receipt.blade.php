<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: sans-serif; font-size: 11px; color: #1e293b; background: #fff; }

    .page { padding: 28px 32px; min-height: 295px; }

    /* Header */
    .header { display: flex; justify-content: space-between; align-items: flex-start;
               border-bottom: 3px solid #4f46e5; padding-bottom: 12px; margin-bottom: 18px; }
    .school-name { font-size: 18px; font-weight: 700; color: #4f46e5; }
    .school-sub  { font-size: 9px; color: #64748b; margin-top: 2px; }
    .receipt-badge { text-align: right; }
    .receipt-badge .label { font-size: 20px; font-weight: 800; color: #10b981; letter-spacing: -0.5px; }
    .receipt-badge .number{ font-size: 11px; color: #64748b; font-family: monospace; }

    /* Paid watermark */
    .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%,-50%) rotate(-30deg);
                  font-size: 80px; font-weight: 900; color: rgba(16,185,129,0.08);
                  letter-spacing: 4px; white-space: nowrap; pointer-events: none; z-index: 0; }

    /* Info grid */
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 4px 24px; margin-bottom: 18px; }
    .info-row   { display: flex; justify-content: space-between; padding: 4px 0;
                  border-bottom: 1px dashed #e2e8f0; }
    .info-label { color: #64748b; font-size: 10px; }
    .info-value { font-weight: 600; font-size: 10px; text-align: right; }

    /* Amount box */
    .amount-box { background: #f0fdf4; border: 2px solid #10b981; border-radius: 8px;
                   padding: 14px 20px; text-align: center; margin: 18px 0; }
    .amount-box .label  { font-size: 10px; color: #64748b; margin-bottom: 4px; }
    .amount-box .amount { font-size: 28px; font-weight: 800; color: #10b981; }

    /* Footer */
    .footer { border-top: 1px solid #e2e8f0; padding-top: 10px; margin-top: 18px;
               display: flex; justify-content: space-between; font-size: 9px; color: #94a3b8; }
    .sig-line { border-top: 1px solid #cbd5e1; width: 140px; margin-top: 28px;
                 font-size: 9px; text-align: center; color: #94a3b8; }
</style>
</head>
<body>

<div class="watermark">PAID</div>

<div class="page">

    {{-- Header --}}
    <div class="header">
        <div>
            <div class="school-name">{{ config('app.name', 'School Management') }}</div>
            <div class="school-sub">Official Payment Receipt</div>
        </div>
        <div class="receipt-badge">
            <div class="label">RECEIPT</div>
            <div class="number">{{ $payment->receipt_number }}</div>
        </div>
    </div>

    {{-- Amount --}}
    <div class="amount-box">
        <div class="label">Amount Received</div>
        <div class="amount">${{ number_format($payment->amount_paid, 2) }}</div>
    </div>

    {{-- Info grid --}}
    <div class="info-grid">
        <div>
            <div class="info-row">
                <span class="info-label">Student</span>
                <span class="info-value">{{ optional($payment->invoice?->student)->full_name ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Invoice #</span>
                <span class="info-value" style="font-family:monospace">{{ $payment->invoice?->invoice_number ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Invoice Title</span>
                <span class="info-value">{{ $payment->invoice?->title ?? '—' }}</span>
            </div>
            @if($payment->invoice?->feeStructure)
            <div class="info-row">
                <span class="info-label">Fee Structure</span>
                <span class="info-value">{{ $payment->invoice->feeStructure->name }}</span>
            </div>
            @endif
        </div>
        <div>
            <div class="info-row">
                <span class="info-label">Payment Date</span>
                <span class="info-value">{{ $payment->payment_date->format('d M Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Payment Method</span>
                <span class="info-value">{{ ucfirst(str_replace('_',' ',$payment->payment_method ?? '')) }}</span>
            </div>
            @if($payment->bank_name)
            <div class="info-row">
                <span class="info-label">Bank</span>
                <span class="info-value">{{ $payment->bank_name }}</span>
            </div>
            @endif
            @if($payment->transaction_reference)
            <div class="info-row">
                <span class="info-label">Reference</span>
                <span class="info-value" style="font-family:monospace">{{ $payment->transaction_reference }}</span>
            </div>
            @endif
            <div class="info-row">
                <span class="info-label">Received By</span>
                <span class="info-value">{{ optional($payment->receivedBy)->full_name ?? '—' }}</span>
            </div>
        </div>
    </div>

    @if($payment->notes)
    <p style="font-size:10px;color:#64748b;margin-top:4px"><em>Notes: {{ $payment->notes }}</em></p>
    @endif

    {{-- Signatures --}}
    <div style="display:flex;justify-content:space-between;margin-top:28px">
        <div class="sig-line">Cashier / Accountant</div>
        <div class="sig-line">School Stamp</div>
        <div class="sig-line">Received By (Parent/Guardian)</div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <span>{{ config('app.name') }} &mdash; Official Receipt</span>
        <span>This is a computer-generated receipt.</span>
        <span>Printed: {{ now()->format('d M Y H:i') }}</span>
    </div>

</div>
</body>
</html>
