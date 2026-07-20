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
        .footer { position: fixed; bottom: 0; left: 0; right: 0; font-size: 8px; color: #999; text-align: center; border-top: 1px solid #ddd; padding: 4px; }
    </style>
</head>
<body>
    <h2>{{ config('app.name') }} — {{ $title }}</h2>
    <div class="sub">
        Generated: {{ now()->format('d M Y H:i') }}
        @if($dateFrom || $dateTo) &nbsp;|&nbsp; Period: {{ $dateFrom ?? '—' }} to {{ $dateTo ?? 'present' }} @endif
        &nbsp;|&nbsp; Records: {{ $records->count() }}
    </div>

    @if($reportType === 'fleet')
    <table>
        <thead><tr><th>#</th><th>Name</th><th>Reg. No.</th><th>Type</th><th>Capacity</th><th>Fuel</th><th>Status</th><th>Driver</th><th>Insurance Exp.</th><th>Fitness Exp.</th></tr></thead>
        <tbody>
            @foreach($records as $i => $v)
            <tr>
                <td>{{ $i+1 }}</td><td>{{ $v->name }}</td><td>{{ $v->registration_number }}</td>
                <td>{{ ucfirst($v->type) }}</td><td>{{ $v->capacity }}</td>
                <td>{{ ucfirst($v->fuel_type) }}</td><td>{{ ucfirst($v->status) }}</td>
                <td>{{ $v->driver?->name ?? '—' }}</td>
                <td>{{ $v->insurance_expiry?->format('d M Y') ?? '—' }}</td>
                <td>{{ $v->fitness_expiry?->format('d M Y') ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @elseif($reportType === 'drivers')
    <table>
        <thead><tr><th>#</th><th>Name</th><th>Phone</th><th>License No.</th><th>License Expiry</th><th>Vehicle</th><th>Status</th></tr></thead>
        <tbody>
            @foreach($records as $i => $d)
            <tr>
                <td>{{ $i+1 }}</td><td>{{ $d->name }}</td><td>{{ $d->phone ?? '—' }}</td>
                <td>{{ $d->license_number }}</td>
                <td>{{ $d->license_expiry?->format('d M Y') ?? '—' }}</td>
                <td>{{ $d->currentVehicle?->name ?? '—' }}</td>
                <td>{{ ucfirst($d->status) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @elseif($reportType === 'routes')
    <table>
        <thead><tr><th>#</th><th>Route</th><th>Code</th><th>Vehicle</th><th>Driver</th><th>Morning Dep.</th><th>Fee/mo</th><th>Students</th><th>Status</th></tr></thead>
        <tbody>
            @foreach($records as $i => $r)
            <tr>
                <td>{{ $i+1 }}</td><td>{{ $r->name }}</td><td>{{ $r->code ?? '—' }}</td>
                <td>{{ $r->vehicle?->name ?? '—' }}</td><td>{{ $r->driver?->name ?? '—' }}</td>
                <td>{{ $r->morning_departure ?? '—' }}</td>
                <td>${{ number_format($r->monthly_fee,2) }}</td>
                <td>{{ $r->active_students_count }}</td>
                <td>{{ ucfirst($r->status) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @elseif($reportType === 'students')
    <table>
        <thead><tr><th>#</th><th>Student</th><th>Route</th><th>Stop</th><th>Direction</th><th>Fee</th><th>Status</th><th>Start</th></tr></thead>
        <tbody>
            @foreach($records as $i => $s)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $s->student->first_name }} {{ $s->student->last_name }}</td>
                <td>{{ $s->route->name }}</td><td>{{ $s->stop?->name ?? '—' }}</td>
                <td>{{ ucfirst(str_replace('_',' ',$s->direction)) }}</td>
                <td>${{ number_format($s->monthly_fee,2) }}</td>
                <td>{{ ucfirst($s->status) }}</td>
                <td>{{ $s->start_date->format('d M Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @elseif($reportType === 'fuel')
    <table>
        <thead><tr><th>#</th><th>Vehicle</th><th>Date</th><th>Litres</th><th>Cost/L</th><th>Total</th><th>Odometer</th><th>Station</th></tr></thead>
        <tbody>
            @foreach($records as $i => $f)
            <tr>
                <td>{{ $i+1 }}</td><td>{{ $f->vehicle->name }}</td>
                <td>{{ $f->date->format('d M Y') }}</td>
                <td>{{ $f->litres }}</td><td>${{ $f->cost_per_litre }}</td>
                <td>${{ number_format($f->total_cost,2) }}</td>
                <td>{{ $f->odometer_reading ?? '—' }}</td>
                <td>{{ $f->fuel_station ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @elseif($reportType === 'maintenance')
    <table>
        <thead><tr><th>#</th><th>Vehicle</th><th>Type</th><th>Title</th><th>Date</th><th>Cost</th><th>Provider</th><th>Status</th></tr></thead>
        <tbody>
            @foreach($records as $i => $m)
            <tr>
                <td>{{ $i+1 }}</td><td>{{ $m->vehicle->name }}</td>
                <td>{{ $m->type_label }}</td><td>{{ $m->title }}</td>
                <td>{{ $m->service_date->format('d M Y') }}</td>
                <td>${{ number_format($m->cost,2) }}</td>
                <td>{{ $m->service_provider ?? '—' }}</td>
                <td>{{ ucfirst($m->status) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="footer">{{ config('app.name') }} — Transport Management &nbsp;|&nbsp; {{ now()->format('d M Y H:i') }}</div>
</body>
</html>
