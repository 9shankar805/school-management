<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; margin: 0; padding: 20px; }
        .header { border-bottom: 2px solid #0052cc; padding-bottom: 10px; margin-bottom: 15px; }
        .title { font-size: 18px; font-weight: bold; color: #0747a6; margin: 0; }
        .subtitle { font-size: 10px; color: #64748b; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #f1f5f9; color: #334155; text-align: left; padding: 6px 8px; border-bottom: 1px solid #cbd5e1; font-size: 10px; text-transform: uppercase; }
        td { padding: 6px 8px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        .footer { margin-top: 20px; text-align: right; font-size: 9px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">{{ $title }}</h1>
        <div class="subtitle">Generated on: {{ now()->format('Y-m-d H:i:s') }} {{ $dateFrom ? "| From: {$dateFrom}" : '' }} {{ $dateTo ? "| To: {$dateTo}" : '' }}</div>
    </div>

    <table>
        <thead>
            @if($type === 'assets')
                <tr>
                    <th>Asset Tag</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Serial No</th>
                    <th>Status</th>
                    <th>Condition</th>
                    <th>Cost</th>
                </tr>
            @elseif($type === 'stock' || $type === 'low_stock')
                <tr>
                    <th>Item Name</th>
                    <th>SKU</th>
                    <th>Category</th>
                    <th>In Stock</th>
                    <th>Min Level</th>
                    <th>Unit Cost</th>
                </tr>
            @elseif($type === 'suppliers')
                <tr>
                    <th>Supplier Name</th>
                    <th>Contact Person</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Total Assets</th>
                </tr>
            @else
                <tr>
                    <th>ID / Ref</th>
                    <th>Details</th>
                    <th>Date</th>
                    <th>Status / Info</th>
                </tr>
            @endif
        </thead>
        <tbody>
            @foreach($records as $rec)
                <tr>
                    @if($type === 'assets')
                        <td>{{ $rec->asset_tag }}</td>
                        <td>{{ $rec->name }}</td>
                        <td>{{ $rec->category->name ?? 'N/A' }}</td>
                        <td>{{ $rec->serial_number ?? 'N/A' }}</td>
                        <td>{{ ucfirst($rec->status) }}</td>
                        <td>{{ ucfirst($rec->condition) }}</td>
                        <td>Rs. {{ number_format($rec->purchase_cost, 2) }}</td>
                    @elseif($type === 'stock' || $type === 'low_stock')
                        <td>{{ $rec->name }}</td>
                        <td>{{ $rec->sku }}</td>
                        <td>{{ $rec->category->name ?? 'N/A' }}</td>
                        <td>{{ $rec->quantity_in_stock }} {{ $rec->unit }}</td>
                        <td>{{ $rec->minimum_stock_level }}</td>
                        <td>Rs. {{ number_format($rec->unit_cost, 2) }}</td>
                    @elseif($type === 'suppliers')
                        <td>{{ $rec->name }}</td>
                        <td>{{ $rec->contact_person }}</td>
                        <td>{{ $rec->phone }}</td>
                        <td>{{ $rec->email }}</td>
                        <td>{{ $rec->assets_count ?? 0 }}</td>
                    @else
                        <td>#{{ $rec->id }}</td>
                        <td>{{ $rec->inventoryItem->name ?? ($rec->supplier->name ?? 'N/A') }}</td>
                        <td>{{ $rec->transacted_at ?? ($rec->order_date ?? 'N/A') }}</td>
                        <td>{{ $rec->type ?? ($rec->status ?? 'N/A') }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        NepalSchoolHub ERP — Inventory & Asset Management System
    </div>
</body>
</html>
