@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-1"><i class="bi bi-person-up"></i> Visitor Log</h1>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('hostel.hostels.index') }}">Hostels</a></li>
                            <li class="breadcrumb-item active">Visitors</li>
                        </ol>
                    </nav>
                    @if(session('status'))<div class="alert alert-success alert-dismissible fade show">{{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

                    {{-- Log visitor form --}}
                    <div class="card shadow-sm mb-4">
                        <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                            Log Visitor
                            <button class="btn btn-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#visitorForm"><i class="bi bi-plus-circle"></i> Add</button>
                        </div>
                        <div class="collapse" id="visitorForm">
                            <div class="card-body">
                                <form method="POST" action="{{ route('hostel.visitors.store') }}">
                                    @csrf
                                    <div class="row g-3">
                                        <div class="col-md-3"><label class="form-label fw-semibold">Hostel</label><select name="hostel_id" class="form-select" required><option value="">— Select —</option>@foreach($hostels as $h)<option value="{{ $h->id }}">{{ $h->name }}</option>@endforeach</select></div>
                                        <div class="col-md-3"><label class="form-label fw-semibold">Student Being Visited</label><select name="student_id" class="form-select" required><option value="">— Select —</option>@foreach($students as $s)<option value="{{ $s->id }}">{{ $s->first_name }} {{ $s->last_name }}</option>@endforeach</select></div>
                                        <div class="col-md-3"><label class="form-label fw-semibold">Visitor Name</label><input type="text" name="visitor_name" class="form-control" required></div>
                                        <div class="col-md-3"><label class="form-label fw-semibold">Relation</label><input type="text" name="relation" class="form-control" required placeholder="Parent, Sibling…"></div>
                                        <div class="col-md-2"><label class="form-label fw-semibold">Date</label><input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required></div>
                                        <div class="col-md-2"><label class="form-label fw-semibold">In Time</label><input type="time" name="in_time" class="form-control" required></div>
                                        <div class="col-md-2"><label class="form-label fw-semibold">Out Time</label><input type="time" name="out_time" class="form-control"></div>
                                        <div class="col-md-4"><label class="form-label fw-semibold">Purpose</label><input type="text" name="purpose" class="form-control" placeholder="Reason for visit"></div>
                                        <div class="col-md-2 d-flex align-items-end"><button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-check-circle"></i> Log</button></div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr><th>Date</th><th>Visitor</th><th>Relation</th><th>Student</th><th>Hostel</th><th>In</th><th>Out</th><th>Purpose</th><th>Actions</th></tr>
                                </thead>
                                <tbody>
                                    @forelse($visitors as $v)
                                    <tr>
                                        <td class="small">{{ $v->date }}</td>
                                        <td class="fw-semibold small">{{ $v->visitor_name }}</td>
                                        <td class="small text-muted">{{ $v->relation }}</td>
                                        <td class="small">{{ $v->student->first_name ?? '—' }} {{ $v->student->last_name ?? '' }}</td>
                                        <td class="small">{{ $v->hostel->name ?? '—' }}</td>
                                        <td class="small">{{ $v->in_time }}</td>
                                        <td class="small">
                                            @if($v->out_time)
                                                {{ $v->out_time }}
                                            @else
                                                <form method="POST" action="{{ route('hostel.visitors.update', $v->id) }}" class="d-flex gap-1">
                                                    @csrf @method('PUT')
                                                    <input type="time" name="out_time" class="form-control form-control-sm" style="width:110px" required>
                                                    <button class="btn btn-success btn-sm">Out</button>
                                                </form>
                                            @endif
                                        </td>
                                        <td class="small text-muted">{{ $v->purpose ?? '—' }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('hostel.visitors.destroy', $v->id) }}" onsubmit="return confirm('Delete visitor log?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="9" class="text-center text-muted py-4">No visitor logs today.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
