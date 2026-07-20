@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-1"><i class="bi bi-check2-square"></i> Transport Attendance</h1>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('transport.index') }}">Transport</a></li>
                            <li class="breadcrumb-item active">Attendance</li>
                        </ol>
                    </nav>

                    @if(session('status'))
                        <div class="alert alert-success alert-dismissible fade show">{{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    @endif

                    {{-- Selector form --}}
                    <form method="GET" action="{{ route('transport.attendance.index') }}" class="card shadow-sm mb-4">
                        <div class="card-body">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Route <span class="text-danger">*</span></label>
                                    <select name="route_id" class="form-select" required onchange="this.form.submit()">
                                        <option value="">— Select Route —</option>
                                        @foreach($routes as $r)
                                            <option value="{{ $r->id }}" @selected($routeId == $r->id)>{{ $r->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Date</label>
                                    <input type="date" name="date" class="form-control" value="{{ $date }}" onchange="this.form.submit()">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Trip</label>
                                    <select name="trip" class="form-select" onchange="this.form.submit()">
                                        <option value="morning"   @selected($trip==='morning')>Morning (Pickup)</option>
                                        <option value="afternoon" @selected($trip==='afternoon')>Afternoon (Dropoff)</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-secondary w-100" type="submit"><i class="bi bi-search"></i> Load</button>
                                </div>
                            </div>
                        </div>
                    </form>

                    @if($route)
                    <form action="{{ route('transport.attendance.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="route_id" value="{{ $routeId }}">
                        <input type="hidden" name="date" value="{{ $date }}">
                        <input type="hidden" name="trip" value="{{ $trip }}">

                        <div class="card shadow-sm">
                            <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                                <span>
                                    {{ $route->name }} — {{ ucfirst($trip) }} — {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
                                </span>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-success btn-sm" onclick="markAll('present')">All Present</button>
                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="markAll('absent')">All Absent</button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr><th>#</th><th>Student</th><th>Stop</th><th>Status</th><th>Remarks</th></tr>
                                    </thead>
                                    <tbody>
                                        @forelse($route->activeStudents as $i => $alloc)
                                        @php
                                            $existing = $records->get($alloc->student_id);
                                            $status = $existing?->status ?? 'present';
                                        @endphp
                                        <tr>
                                            <td class="text-muted">{{ $i + 1 }}</td>
                                            <td class="fw-semibold small">
                                                {{ $alloc->student->first_name }} {{ $alloc->student->last_name }}
                                            </td>
                                            <td class="small text-muted">{{ $alloc->stop?->name ?? '—' }}</td>
                                            <td>
                                                <input type="hidden" name="attendance[{{ $i }}][student_id]" value="{{ $alloc->student_id }}">
                                                <div class="d-flex gap-2">
                                                    @foreach(['present'=>['success','check-circle'],'absent'=>['danger','x-circle'],'late'=>['warning','clock']] as $val=>[$color,$icon])
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input att-radio" type="radio"
                                                               name="attendance[{{ $i }}][status]"
                                                               id="att_{{ $i }}_{{ $val }}"
                                                               value="{{ $val }}"
                                                               @checked($status === $val)>
                                                        <label class="form-check-label text-{{ $color }}" for="att_{{ $i }}_{{ $val }}">
                                                            <i class="bi bi-{{ $icon }}"></i> {{ ucfirst($val) }}
                                                        </label>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td>
                                                <input type="text" name="attendance[{{ $i }}][remarks]" class="form-control form-control-sm"
                                                       value="{{ $existing?->remarks }}" placeholder="Optional remark">
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="5" class="text-center text-muted py-4">No students on this route.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @if($route->activeStudents->isNotEmpty())
                            <div class="card-footer">
                                <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Save Attendance</button>
                            </div>
                            @endif
                        </div>
                    </form>
                    @endif

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function markAll(status) {
    document.querySelectorAll('.att-radio[value="' + status + '"]').forEach(r => r.checked = true);
}
</script>
@endpush
