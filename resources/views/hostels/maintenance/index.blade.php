@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-1"><i class="bi bi-tools"></i> Hostel Maintenance</h1>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('hostel.hostels.index') }}">Hostels</a></li>
                            <li class="breadcrumb-item active">Maintenance</li>
                        </ol>
                    </nav>
                    @if(session('status'))<div class="alert alert-success alert-dismissible fade show">{{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="card shadow-sm">
                                <div class="card-header fw-semibold">Raise Request</div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('hostel.maintenance.store') }}">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Hostel <span class="text-danger">*</span></label>
                                            <select name="hostel_id" id="mHostelSel" class="form-select" required onchange="loadMRooms(this.value)">
                                                <option value="">— Select —</option>
                                                @foreach($hostels as $h)
                                                    <option value="{{ $h->id }}">{{ $h->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Room <span class="text-danger">*</span></label>
                                            <select name="hostel_room_id" id="mRoomSel" class="form-select" required>
                                                <option value="">— Select hostel first —</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Issue Type <span class="text-danger">*</span></label>
                                            <input type="text" name="issue_type" class="form-control" required placeholder="e.g. Plumbing, Electrical">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Priority <span class="text-danger">*</span></label>
                                            <select name="priority" class="form-select" required>
                                                <option value="Low">Low</option>
                                                <option value="Medium">Medium</option>
                                                <option value="High">High</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                                            <textarea name="description" class="form-control" rows="3" required></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-warning btn-sm w-100"><i class="bi bi-exclamation-triangle"></i> Raise Request</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="card shadow-sm">
                                <div class="card-body p-0">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr><th>Hostel</th><th>Room</th><th>Issue</th><th>Priority</th><th>Status</th><th>Reported</th><th>Actions</th></tr>
                                        </thead>
                                        <tbody>
                                            @forelse($requests as $req)
                                            <tr>
                                                <td class="small">{{ $req->hostel->name ?? '—' }}</td>
                                                <td class="small">Room {{ $req->room->room_number ?? '—' }}</td>
                                                <td>
                                                    <div class="fw-semibold small">{{ $req->issue_type }}</div>
                                                    <div class="text-muted" style="font-size:.72rem">{{ Str::limit($req->description,60) }}</div>
                                                </td>
                                                <td>
                                                    @php $pc=match($req->priority){'High'=>'bg-danger','Medium'=>'bg-warning text-dark',default=>'bg-secondary'}; @endphp
                                                    <span class="badge {{ $pc }}">{{ $req->priority }}</span>
                                                </td>
                                                <td>
                                                    <form method="POST" action="{{ route('hostel.maintenance.update', $req->id) }}" class="d-flex gap-1">
                                                        @csrf @method('PUT')
                                                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="width:130px">
                                                            <option value="Pending"     @selected($req->status==='Pending')>Pending</option>
                                                            <option value="In Progress" @selected($req->status==='In Progress')>In Progress</option>
                                                            <option value="Resolved"    @selected($req->status==='Resolved')>Resolved</option>
                                                        </select>
                                                    </form>
                                                </td>
                                                <td class="small text-muted">{{ $req->reporter?->first_name ?? 'N/A' }}</td>
                                                <td>
                                                    <form method="POST" action="{{ route('hostel.maintenance.destroy', $req->id) }}" onsubmit="return confirm('Delete request?')">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="7" class="text-center text-muted py-4">No maintenance requests.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
function loadMRooms(hostelId) {
    const sel = document.getElementById('mRoomSel');
    sel.innerHTML = '<option value="">Loading…</option>';
    if (!hostelId) { sel.innerHTML = '<option value="">— —</option>'; return; }
    const hostels = @json($hostels->load('rooms'));
    const hostel = hostels.find(h => h.id == hostelId);
    sel.innerHTML = '<option value="">— Select Room —</option>';
    (hostel?.rooms || []).forEach(r => {
        const o = document.createElement('option');
        o.value = r.id; o.textContent = 'Room ' + r.room_number;
        sel.appendChild(o);
    });
}
</script>
@endpush
