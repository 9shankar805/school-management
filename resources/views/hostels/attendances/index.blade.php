@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-1"><i class="bi bi-check2-square"></i> Hostel Attendance</h1>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('hostel.hostels.index') }}">Hostels</a></li>
                            <li class="breadcrumb-item active">Attendance</li>
                        </ol>
                    </nav>
                    @if(session('status'))<div class="alert alert-success alert-dismissible fade show">{{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

                    {{-- Filter form --}}
                    <form method="GET" action="{{ route('hostel.attendances.index') }}" class="card shadow-sm mb-4">
                        <div class="card-body">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Hostel</label>
                                    <select name="hostel_id" class="form-select" onchange="this.form.submit()">
                                        <option value="">— Select Hostel —</option>
                                        @foreach($hostels as $h)
                                            <option value="{{ $h->id }}" @selected($hostel_id==$h->id)>{{ $h->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Date</label>
                                    <input type="date" name="date" class="form-control" value="{{ $date }}" onchange="this.form.submit()">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-search"></i> Load</button>
                                </div>
                            </div>
                        </div>
                    </form>

                    @if($hostel_id && count($students) > 0)
                    <form method="POST" action="{{ route('hostel.attendances.store') }}">
                        @csrf
                        <input type="hidden" name="hostel_id" value="{{ $hostel_id }}">
                        <input type="hidden" name="date" value="{{ $date }}">
                        <div class="card shadow-sm">
                            <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                                <span>{{ \App\Models\Hostel::find($hostel_id)?->name }} — {{ \Carbon\Carbon::parse($date)->format('d M Y') }}</span>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-success btn-sm" onclick="markAll('Present')">All Present</button>
                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="markAll('Absent')">All Absent</button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light"><tr><th>#</th><th>Student</th><th>Status</th></tr></thead>
                                    <tbody>
                                        @foreach($students as $i => $alloc)
                                        @php $existing = $attendances->get($alloc->student_id); @endphp
                                        <tr>
                                            <td class="text-muted small">{{ $i+1 }}</td>
                                            <td class="fw-semibold small">{{ $alloc->student->first_name }} {{ $alloc->student->last_name }}</td>
                                            <td>
                                                <div class="d-flex gap-3">
                                                    @foreach(['Present'=>'success','Absent'=>'danger','Leave'=>'warning'] as $val=>$color)
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input att-radio" type="radio"
                                                               name="attendance[{{ $alloc->student_id }}]"
                                                               value="{{ $val }}"
                                                               @checked(($existing?->status ?? 'Present') === $val)>
                                                        <label class="form-check-label text-{{ $color }}">{{ $val }}</label>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Save Attendance</button>
                            </div>
                        </div>
                    </form>
                    @elseif($hostel_id)
                    <div class="alert alert-info">No active students allocated to this hostel.</div>
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
