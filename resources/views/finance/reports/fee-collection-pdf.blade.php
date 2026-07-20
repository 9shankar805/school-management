<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: sans-serif; font-size: 11px; color: #1e293b; padding: 24px; }
    h1 { font-size: 18px; margin-bottom: 2px; }
    .meta { color: #64748b; font-size: 10px; margin-bottom: 16px; }
    .summary { display: flex; gap: 16px; margin-bottom: 16px; }
    .kpi { background: #f1f5f9; border-radius: 6px; padding: 8px 14px; }
    .kpi p { font-size: 9px; color: #64748b; margin-bottom: 2px; }
    .kpi strong { font-size: 14px; color: #10b981; }
    table { width: 100%; border-collapse: collapse; }
    thead th { background: #f8fafc; font-size: 10px; padding: 6px 8px;
               border-bottom: 2px solid #e2e8f0; text-align: left; }
    tbody td { padding: 5px 8px; border-bottom: 1px solid #f1f5f9; font-size: 10px; }
    tbody tr:nth-child(even) td { background: #fafafa; }
    .text-right { text-align: right; }
    .text-success { color: #10b981; font-weight: 600; }
    tfoot td { font-weight: bold; padding: 7px 8px; border-top: 2px solid #e2e8f0; }
</style>
</head>
<body>
    <h1>Fee Collection Report</h1>
    <p class="meta">
        Period: {{ \Carbon\Carbon::parse($from)->format('d M Y') }} — {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
        &nbsp;|&nbsp; Generated: {{ now()->format('d M Y H:i') }}
    </p>

    <div class="summary">
        <div class="kpi">
            <p>Total Collected</p>
            <strong>${{ number_format($total, 2) }}</strong>
        </div>
        @foreach($byMethod as $method => $amt)
        <div class="kpi">
            <p>{{ ucfirst(str_replace('_',' ',$method)) }}</p>
            <strong style="color:#6366f1">${{ number_format($amt, 2) }}</strong>
        </div>
        @endforeach
    </div>

    <table>
        <thead>
            <tr>
                <th>Receipt #</th><th>Date</th><th>Student</th>
                <th>Invoice #</th><th class="text-right">Amount</th>
                <th>Method</th><th>Reference</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $p)
            <tr>
                <td>{{ $p->receipt_number }}</td>
                <td>{{ $p->payment_date->format('d M Y') }}</td>
                <td>{{ optional($p->invoice?->student)->full_name ?? '—' }}</td>
                <td>{{ $p->invoice?->invoice_number ?? '—' }}</td>
                <td class="text-right text-success">${{ number_format($p->amount_paid, 2) }}</td>
                <td>{{ ucfirst(str_replace('_',' ',$p->payment_method ?? '')) }}</td>
                <td>{{ $p->transaction_reference ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4">Total</td>
                <td class="text-right" style="color:#10b981">${{ number_format($total, 2) }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
