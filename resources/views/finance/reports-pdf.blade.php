<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"><title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        h2  { font-size: 14px; margin-bottom: 4px; }
        .sub { font-size: 9px; color: #666; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th { background: #1a3c6e; color: #fff; padding: 5px 6px; text-align: left; font-size: 9px; }
        td { padding: 4px 6px; border-bottom: 1px solid #e0e0e0; font-size: 9px; }
        tr:nth-child(even) td { background: #f5f7fa; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; font-size: 8px; color: #999; text-align: center; border-top: 1px solid #ddd; padding: 4px; }
    </style>
</head>
<body>
    <h2>{{ config('app.name') }} — {{ $title }}</h2>
    <div class="sub">Generated: {{ now()->format('d M Y H:i') }} @if($dateFrom || $dateTo) | Period: {{ $dateFrom ?? '—' }} to {{ $dateTo ?? 'present' }} @endif | Records: {{ is_countable($records) ? count($records) : '—' }}</div>

    @if($type === 'fee_collection')
    <table>
        <thead><tr><th>#</th><th>Student</th><th>Invoice</th><th>Amount Paid</th><th>Payment Date</th><th>Method</th></tr></thead>
        <tbody>
            @foreach($records as $i => $p)
            <tr><td>{{ $i+1 }}</td><td>{{ $p->invoice->student->first_name ?? '—' }} {{ $p->invoice->student->last_name ?? '' }}</td><td>{{ $p->invoice->title ?? '—' }}</td><td>Rs. {{ number_format($p->amount,2) }}</td><td>{{ \Carbon\Carbon::parse($p->payment_date)->format('d M Y') }}</td><td>{{ ucfirst($p->payment_method ?? 'cash') }}</td></tr>
            @endforeach
        </tbody>
    </table>

    @elseif($type === 'expenses')
    <table>
        <thead><tr><th>#</th><th>Date</th><th>Title</th><th>Category</th><th>Amount</th><th>Method</th><th>Ref</th></tr></thead>
        <tbody>
            @foreach($records as $i => $e)
            <tr><td>{{ $i+1 }}</td><td>{{ $e->expense_date->format('d M Y') }}</td><td>{{ $e->title }}</td><td>{{ $e->category }}</td><td>Rs. {{ number_format($e->amount,2) }}</td><td>{{ ucfirst($e->payment_method) }}</td><td>{{ $e->reference ?? '—' }}</td></tr>
            @endforeach
        </tbody>
    </table>

    @elseif($type === 'income')
    <table>
        <thead><tr><th>#</th><th>Date</th><th>Title</th><th>Category</th><th>Amount</th><th>Method</th></tr></thead>
        <tbody>
            @foreach($records as $i => $r)
            <tr><td>{{ $i+1 }}</td><td>{{ $r->income_date->format('d M Y') }}</td><td>{{ $r->title }}</td><td>{{ $r->category }}</td><td>Rs. {{ number_format($r->amount,2) }}</td><td>{{ ucfirst($r->payment_method) }}</td></tr>
            @endforeach
        </tbody>
    </table>

    @elseif($type === 'outstanding')
    <table>
        <thead><tr><th>#</th><th>Student</th><th>Invoice</th><th>Amount</th><th>Due Date</th><th>Status</th></tr></thead>
        <tbody>
            @foreach($records as $i => $inv)
            <tr><td>{{ $i+1 }}</td><td>{{ $inv->student->first_name ?? '—' }} {{ $inv->student->last_name ?? '' }}</td><td>{{ $inv->title }}</td><td>Rs. {{ number_format($inv->amount,2) }}</td><td>{{ $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('d M Y') : '—' }}</td><td>{{ ucfirst($inv->status) }}</td></tr>
            @endforeach
        </tbody>
    </table>

    @elseif($type === 'ledger')
    <table>
        <thead><tr><th>Category</th><th>Total ($)</th></tr></thead>
        <tbody>
            <tr><td>Fee Collections</td><td>Rs. {{ number_format($records['fees'] ?? 0, 2) }}</td></tr>
            <tr><td>Other Income</td><td>Rs. {{ number_format($records['income'] ?? 0, 2) }}</td></tr>
            <tr><td>Expenses</td><td>-Rs. {{ number_format($records['expenses'] ?? 0, 2) }}</td></tr>
            <tr><td><strong>Net Balance</strong></td><td><strong>Rs. {{ number_format(($records['fees'] ?? 0) + ($records['income'] ?? 0) - ($records['expenses'] ?? 0), 2) }}</strong></td></tr>
        </tbody>
    </table>
    @endif

    <div class="footer">{{ config('app.name') }} — Finance | {{ now()->format('d M Y H:i') }}</div>
</body>
</html>
