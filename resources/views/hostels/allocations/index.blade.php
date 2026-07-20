@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-1"><i class="bi bi-person-check"></i> Hostel Allocations</h1>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('hostel.hostels.index') }}">Hostels</a></li>
                            <li class="breadcrumb-item active">Allocations</li>
                        </ol>
                    </nav>
                    @if(session('status'))<div class="alert alert-success alert-dismissible fade show">{{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
                    @if(session('error'))<div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

                    {{-- Allocate form --}}
                    <div class="card shadow-sm mb-4">
                        <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                            Allocate Student to Bed
                            <button class="btn btn-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#allocForm"><i class="bi bi-plus-circle"></i> Allocate</button>
                        </div>
                        <div class="collapse" id="allocForm">
                            <div class="card-body">
                                <form method="POST" action="{{ route('hostel.allocations.store') }}">
                                    @csrf
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Student <span class="text-danger">*</span></label>
                                            <select name="student_id" class="form-select" required>
                                                <option value="">— Select —</option>
                                                @foreach($students as $s)
                                                    <option value="{{ $s->id }}">{{ $s->first_name }} {{ $s->last_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">Hostel <span class="text-danger">*</span></label>
                                            <select name="hostel_id" id="hostelSel" class="form-select" required onchange="loadRooms(this.value)">
                                                <option value="">— Select —</option>
                                                @foreach($hostels as $h)
                                                    <option value="{{ $h->id }}">{{ $h->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label fw-semibold">Room <span class="text-danger">*</span></label>
                                            <select name="hostel_room_id" id="roomSel" class="form-select" required onchange="loadBeds(this.value)">
                                                <option value="">— —</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label fw-semibold">Bed <span class="text-danger">*</span></label>
                                            <select name="hostel_bed_id" id="bedSel" class="form-select" required>
                                                <option value="">— —</option>
                                            </select>
                                        </div>
                                        <div class="col-md-1">
                                            <label class="form-label fw-semibold">Status</label>
                                            <select name="status" class="form-select">
                                                <option value="Active">Active</option>
                                                <option value="Inactive">Inactive</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                                            <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">End Date</label>
                                            <input type="date" name="end_date" class="form-control">
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-check-circle"></i> Allocate</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr><th>Student</th><th>Hostel</th><th>Room</th><th>Bed</th><th>Start</th><th>End</th><th>Status</th><th>Actions</th></tr>
                                </thead>
                                <tbody>
                                    @forelse($allocations as $a)
                                    <tr>
                                        <td class="fw-semibold small">{{ $a->student->first_name ?? '—' }} {{ $a->student->last_name ?? '' }}</td>
                                        <td class="small">{{ $a->hostel->name ?? '—' }}</td>
                                        <td class="small">Room {{ $a->room->room_number ?? '—' }}</td>
                                        <td class="small">{{ $a->bed->name ?? '—' }}</td>
                                        <td class="small">{{ $a->start_date }}</td>
                                        <td class="small">{{ $a->end_date ?? '—' }}</td>
                                        <td><span class="badge {{ $a->status==='Active' ? 'bg-success' : 'bg-secondary' }}">{{ $a->status }}</span></td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <button class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editAlloc{{ $a->id }}"><i class="bi bi-pencil"></i></button>
                                                <form method="POST" action="{{ route('hostel.allocations.destroy', $a->id) }}" onsubmit="return confirm('Remove allocation?')">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button></form>
                                            </div>
                                        </td>
                                    </tr>
                                    <div class="modal fade" id="editAlloc{{ $a->id }}" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
                                        <form method="POST" action="{{ route('hostel.allocations.update', $a->id) }}">@csrf @method('PUT')
                                            <div class="modal-header"><h5 class="modal-title">Edit Allocation</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                            <div class="modal-body row g-2">
                                                <div class="col-6"><label class="form-label">Start Date</label><input type="date" name="start_date" class="form-control" value="{{ $a->start_date }}" required></div>
                                                <div class="col-6"><label class="form-label">End Date</label><input type="date" name="end_date" class="form-control" value="{{ $a->end_date }}"></div>
                                                <div class="col-12"><label class="form-label">Status</label><select name="status" class="form-select"><option value="Active" @selected($a->status==='Active')>Active</option><option value="Inactive" @selected($a->status==='Inactive')>Inactive</option></select></div>
                                            </div>
                                            <div class="modal-footer"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary btn-sm">Save</button></div>
                                        </form>
                                    </div></div></div>
                                    @empty
                                    <tr><td colspan="8" class="text-center text-muted py-4">No allocations yet.</td></tr>
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
@push('scripts')
<script>
function loadRooms(hostelId) {
    const rSel = document.getElementById('roomSel');
    const bSel = document.getElementById('bedSel');
    rSel.innerHTML = '<option value="">Loading…</option>';
    bSel.innerHTML = '<option value="">— —</option>';
    if (!hostelId) { rSel.innerHTML = '<option value="">— —</option>'; return; }
    const rooms = @json($hostels->keyBy('id'));
    const hostel = rooms[hostelId];
    if (!hostel || !hostel.rooms) return;
    rSel.innerHTML = '<option value="">— Select Room —</option>';
    hostel.rooms.forEach(r => {
        const o = document.createElement('option');
        o.value = r.id; o.textContent = 'Room ' + r.room_number + ' (' + r.room_type + ')';
        rSel.appendChild(o);
    });
}
function loadBeds(roomId) {
    const bSel = document.getElementById('bedSel');
    bSel.innerHTML = '<option value="">Loading…</option>';
    if (!roomId) { bSel.innerHTML = '<option value="">— —</option>'; return; }
    const allBeds = @json(\App\Models\HostelBed::all());
    bSel.innerHTML = '<option value="">— Select Bed —</option>';
    allBeds.filter(b => b.hostel_room_id == roomId && b.status === 'Available').forEach(b => {
        const o = document.createElement('option'); o.value = b.id; o.textContent = b.name;
        bSel.appendChild(o);
    });
}
</script>
@endpush
