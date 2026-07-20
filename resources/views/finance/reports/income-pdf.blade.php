<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: sans-serif; font-size: 11px; color: #1e293b; padding: 24px; }
    h1 { font-size: 18px; margin-bottom: 4px; }
    .meta { color: #64748b; font-size: 10px; margin-bottom: 16px; }
    h3 { font-size: 13px; margin: 14px 0 6px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
    thead th { background: #f8fafc; font-size: 10px; padding: 6px 8px;
               border-bottom: 2px solid #e2e8f0; text-align: left; }
    tbody td { padding: 5px 8px; border-bottom: 1px solid #f1f5f9; font-size: 10px; }
    tbody tr:nth-child(even) td { background: #fafafa; }
    .text-right { text-align: right; }
    .text-success { color: #10b981; font-weight: 600; }
    tfoot td { font-weight: bold; padding: 7px 8px; border-top: 2px solid #e2e8f0; }
    .grand { background: #f0fdf4; padding: 10px 12px; border-radius: 6px; font-size: 13px; }
</style>
</head>
<body>
    <h1>Income Report</h1>
    <p class="meta">
        Period: {{ \Carbon\Carbon::parse($from)->format('d M Y') }} — {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
        &nbsp;|&nbsp; Generated: {{ now()->format('d M Y H:i') }}
    </p>

    <div class="grand">
        Total Income: <strong class="text-success">${{ number_format($totalIncome, 2) }}</strong>
        &nbsp;(Fees: ${{ number_format($totalFees, 2) }} + Other: ${{ number_format($totalOther, 2) }})
    </div>

    <h3>Fee Payments</h3>
    <table>
        <thead>
            <tr>
                <th>Receipt #</th><th>Date</th><th>Student</th>
                <th>Invoice #</th><th class="text-right">Amount</th><th>Method</th>
            </tr>
        </thead>
        <tbody>
            @foreach($feePayments as $p)
            <tr>
                <td>{{ $p->receipt_number }}</td>
                <td>{{ $p->payment_date->format('d M Y') }}</td>
                <td>{{ optional($p->invoice?->student)->full_name ?? '—' }}</td>
                <td>{{ $p->invoice?->invoice_number ?? '—' }}</td>
                <td class="text-right text-success">${{ number_format($p->amount_paid, 2) }}</td>
                <td>{{ ucfirst(str_replace('_',' ',$p->payment_method ?? '')) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr><td colspan="4">Subtotal</td>
                <td class="text-right text-success">${{ number_format($totalFees, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <h3>Other Income</h3>
    <table>
        <thead>
            <tr>
                <th>Date</th><th>Title</th><th>Category</th>
                <th>Source</th><th class="text-right">Amount</th><th>Method</th>
            </tr>
        </thead>
        <tbody>
            @forelse($otherIncome as $e)
            <tr>
                <td>{{ $e->income_date->format('d M Y') }}</td>
                <td>{{ $e->title }}</td>
                <td>{{ $e->category_label }}</td>
                <td>{{ $e->source ?? '—' }}</td>
                <td class="text-right text-success">${{ number_format($e->amount, 2) }}</td>
                <td>{{ ucfirst(str_replace('_',' ',$e->payment_method ?? '')) }}</td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center;color:#94a3b8;padding:8px">None</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr><td colspan="4">Subtotal</td>
                <td class="text-right text-success">${{ number_format($totalOther, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
