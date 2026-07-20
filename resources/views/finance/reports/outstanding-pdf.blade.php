<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: sans-serif; font-size: 11px; color: #1e293b; padding: 24px; }
    h1 { font-size: 18px; margin-bottom: 4px; }
    .meta { color: #64748b; font-size: 10px; margin-bottom: 16px; }
    .alert { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 8px 12px;
             border-radius: 4px; margin-bottom: 16px; font-size: 11px; }
    table { width: 100%; border-collapse: collapse; }
    thead th { background: #f8fafc; font-size: 10px; padding: 6px 8px;
               border-bottom: 2px solid #e2e8f0; text-align: left; }
    tbody td { padding: 5px 8px; border-bottom: 1px solid #f1f5f9; font-size: 10px; }
    tbody tr:nth-child(even) td { background: #fafafa; }
    .overdue td { background: #fff7ed !important; }
    .text-right { text-align: right; }
    .text-danger { color: #ef4444; font-weight: 600; }
    tfoot td { font-weight: bold; padding: 7px 8px; border-top: 2px solid #e2e8f0; }
</style>
</head>
<body>
    <h1>Outstanding Fees Report</h1>
    <p class="meta">Generated: {{ now()->format('d M Y H:i') }}</p>
    <div class="alert">
        Total Outstanding: <strong>${{ number_format($totalOutstanding, 2) }}</strong>
        across {{ $invoices->count() }} invoice(s)
    </div>
    <table>
        <thead>
            <tr>
                <th>Invoice #</th><th>Student</th><th>Title</th>
                <th>Due Date</th><th class="text-right">Amount</th>
                <th class="text-right">Paid</th><th class="text-right">Balance</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoices as $inv)
            <tr class="{{ $inv->is_overdue ? 'overdue' : '' }}">
                <td>{{ $inv->invoice_number }}</td>
                <td>{{ optional($inv->student)->full_name }}</td>
                <td>{{ $inv->title }}</td>
                <td>{{ $inv->due_date?->format('d M Y') ?? '—' }}</td>
                <td class="text-right">${{ number_format($inv->net_amount ?: $inv->amount, 2) }}</td>
                <td class="text-right">${{ number_format($inv->total_paid, 2) }}</td>
                <td class="text-right text-danger">${{ number_format($inv->balance_due, 2) }}</td>
                <td>{{ ucfirst($inv->status) }}{{ $inv->is_overdue ? ' (Overdue)' : '' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6">Total Outstanding</td>
                <td class="text-right text-danger">${{ number_format($totalOutstanding, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
