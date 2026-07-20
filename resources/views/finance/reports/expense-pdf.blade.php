<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: sans-serif; font-size: 11px; color: #1e293b; padding: 24px; }
    h1 { font-size: 18px; margin-bottom: 4px; }
    .meta { color: #64748b; font-size: 10px; margin-bottom: 16px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    thead th { background: #f8fafc; font-size: 10px; padding: 6px 8px;
               border-bottom: 2px solid #e2e8f0; text-align: left; }
    tbody td { padding: 5px 8px; border-bottom: 1px solid #f1f5f9; font-size: 10px; }
    tbody tr:nth-child(even) td { background: #fafafa; }
    .text-right { text-align: right; }
    .text-danger { color: #ef4444; font-weight: 600; }
    tfoot td { font-weight: bold; padding: 7px 8px; border-top: 2px solid #e2e8f0; }
    .summary { background: #fff1f2; border-radius: 6px; padding: 12px; margin-bottom: 16px; }
    .summary h3 { font-size: 13px; color: #ef4444; margin-bottom: 8px; }
    .summary-row { display: flex; justify-content: space-between; font-size: 10px; margin-bottom: 4px; }
</style>
</head>
<body>
    <h1>Expense Report</h1>
    <p class="meta">
        Period: {{ \Carbon\Carbon::parse($from)->format('d M Y') }} — {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
        &nbsp;|&nbsp; Generated: {{ now()->format('d M Y H:i') }}
    </p>

    <div class="summary">
        <h3>By Category — Total: ${{ number_format($total, 2) }}</h3>
        @foreach($byCategory as $cat => $amt)
        <div class="summary-row">
            <span>{{ ucfirst(str_replace('_',' ',$cat)) }}</span>
            <strong>${{ number_format($amt, 2) }}</strong>
        </div>
        @endforeach
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th><th>Title</th><th>Category</th>
                <th>Vendor</th><th class="text-right">Amount</th><th>Method</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expenses as $e)
            <tr>
                <td>{{ $e->expense_date->format('d M Y') }}</td>
                <td>{{ $e->title }}</td>
                <td>{{ $e->category_label }}</td>
                <td>{{ $e->vendor ?? '—' }}</td>
                <td class="text-right text-danger">${{ number_format($e->amount, 2) }}</td>
                <td>{{ ucfirst(str_replace('_',' ',$e->payment_method)) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4">Total</td>
                <td class="text-right text-danger">${{ number_format($total, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
