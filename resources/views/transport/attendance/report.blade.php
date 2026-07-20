@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-1"><i class="bi bi-calendar-check"></i> Transport Attendance Report</h1>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('transport.attendance.index') }}">Attendance</a></li>
                            <li class="breadcrumb-item active">Report</li>
                        </ol>
                    </nav>

                    <form method="GET" action="{{ route('transport.attendance.report') }}" class="row g-2 mb-4">
                        <div class="col-md-4">
                            <select name="route_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">— Select Route —</option>
                                @foreach($routes as $r)
                                    <option value="{{ $r->id }}" @selected($routeId == $r->id)>{{ $r->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="month" name="month" class="form-control form-control-sm" value="{{ $month }}" onchange="this.form.submit()">
                        </div>
                    </form>

                    @if($route && $report->isNotEmpty())
                    <div class="card shadow-sm">
                        <div class="card-header fw-semibold">
                            {{ $route->name }} — {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Student</th>
                                        <th class="text-center text-success">Present</th>
                                        <th class="text-center text-danger">Absent</th>
                                        <th class="text-center text-warning">Late</th>
                                        <th class="text-center">Attendance %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($report as $row)
                                    @php
                                        $total = $row['present'] + $row['absent'] + $row['late'];
                                        $pct = $total > 0 ? round(($row['present'] / $total) * 100) : 0;
                                    @endphp
                                    <tr>
                                        <td class="fw-semibold small">{{ $row['student']->first_name }} {{ $row['student']->last_name }}</td>
                                        <td class="text-center"><span class="badge bg-success">{{ $row['present'] }}</span></td>
                                        <td class="text-center"><span class="badge bg-danger">{{ $row['absent'] }}</span></td>
                                        <td class="text-center"><span class="badge bg-warning text-dark">{{ $row['late'] }}</span></td>
                                        <td class="text-center">
                                            <div class="progress" style="height:18px">
                                                <div class="progress-bar bg-{{ $pct >= 75 ? 'success' : ($pct >= 50 ? 'warning' : 'danger') }}"
                                                     style="width:{{ $pct }}%">{{ $pct }}%</div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @elseif($routeId)
                    <div class="alert alert-info">No attendance records found for the selected period.</div>
                    @endif

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
