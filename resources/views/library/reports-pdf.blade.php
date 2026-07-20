<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        h2   { font-size: 14px; margin-bottom: 4px; }
        .sub { font-size: 9px; color: #666; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th { background: #1a3c6e; color: #fff; padding: 5px 6px; text-align: left; font-size: 9px; }
        td { padding: 4px 6px; border-bottom: 1px solid #e0e0e0; font-size: 9px; }
        tr:nth-child(even) td { background: #f5f7fa; }
        .badge-danger  { background: #dc3545; color: #fff; padding: 2px 5px; border-radius: 3px; }
        .badge-success { background: #198754; color: #fff; padding: 2px 5px; border-radius: 3px; }
        .badge-warning { background: #ffc107; color: #000; padding: 2px 5px; border-radius: 3px; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; font-size: 8px; color: #999; text-align: center; border-top: 1px solid #ddd; padding: 4px; }
    </style>
</head>
<body>
    <h2>{{ config('app.name') }} — {{ $title }}</h2>
    <div class="sub">
        Generated: {{ now()->format('d M Y H:i') }}
        @if($dateFrom || $dateTo)
            &nbsp;|&nbsp; Period: {{ $dateFrom ?? '—' }} to {{ $dateTo ?? 'present' }}
        @endif
        &nbsp;|&nbsp; Total records: {{ $records->count() }}
    </div>

    @if($reportType === 'catalog')
    <table>
        <thead>
            <tr><th>#</th><th>Title</th><th>Author</th><th>Category</th><th>ISBN</th><th>Qty</th><th>Available</th><th>Shelf</th></tr>
        </thead>
        <tbody>
            @foreach($records as $i => $book)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $book->title }}</td>
                <td>{{ $book->author }}</td>
                <td>{{ $book->category?->name ?? '—' }}</td>
                <td>{{ $book->isbn ?? '—' }}</td>
                <td>{{ $book->qty }}</td>
                <td>{{ $book->available_qty }}</td>
                <td>{{ $book->shelf_location ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @elseif(in_array($reportType, ['issued', 'overdue']))
    <table>
        <thead>
            <tr><th>#</th><th>Book</th><th>Member</th><th>Card No.</th><th>Issued</th><th>Due</th><th>Days Overdue</th><th>Fine</th></tr>
        </thead>
        <tbody>
            @foreach($records as $i => $issue)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $issue->book->title }}</td>
                <td>{{ $issue->member->user->first_name }} {{ $issue->member->user->last_name }}</td>
                <td>{{ $issue->member->card_number }}</td>
                <td>{{ $issue->issue_date->format('d M Y') }}</td>
                <td>{{ $issue->due_date->format('d M Y') }}</td>
                <td>{{ $issue->overdue_days > 0 ? $issue->overdue_days : '—' }}</td>
                <td>{{ $issue->fine_amount > 0 ? '$' . number_format($issue->fine_amount, 2) : '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @elseif($reportType === 'fines')
    <table>
        <thead>
            <tr><th>#</th><th>Book</th><th>Member</th><th>Due Date</th><th>Return Date</th><th>Days Overdue</th><th>Fine Amount</th></tr>
        </thead>
        <tbody>
            @foreach($records as $i => $issue)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $issue->book->title }}</td>
                <td>{{ $issue->member->user->first_name }} {{ $issue->member->user->last_name }}</td>
                <td>{{ $issue->due_date->format('d M Y') }}</td>
                <td>{{ $issue->return_date?->format('d M Y') ?? '—' }}</td>
                <td>{{ $issue->overdue_days }}</td>
                <td class="badge-danger">${{ number_format($issue->fine_amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @elseif($reportType === 'members')
    <table>
        <thead>
            <tr><th>#</th><th>Name</th><th>Card No.</th><th>Type</th><th>Status</th><th>Active Loans</th><th>Outstanding Fine</th><th>Joined</th></tr>
        </thead>
        <tbody>
            @foreach($records as $i => $member)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $member->user->first_name }} {{ $member->user->last_name }}</td>
                <td>{{ $member->card_number }}</td>
                <td>{{ ucfirst($member->member_type) }}</td>
                <td>{{ ucfirst($member->status) }}</td>
                <td>{{ $member->active_issues_count }}</td>
                <td>{{ $member->outstanding_fine > 0 ? '$' . number_format($member->outstanding_fine, 2) : '—' }}</td>
                <td>{{ $member->membership_start->format('d M Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="footer">{{ config('app.name') }} — Library Management System &nbsp;|&nbsp; {{ now()->format('d M Y H:i') }}</div>
</body>
</html>
