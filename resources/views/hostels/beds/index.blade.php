@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-1"><i class="bi bi-layout-three-columns"></i> Beds</h1>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('hostel.rooms.index') }}">Rooms</a></li>
                            <li class="breadcrumb-item active">Beds</li>
                        </ol>
                    </nav>

                    @if(session('status'))
                        <div class="alert alert-success alert-dismissible fade show">{{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    @endif

                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="card shadow-sm">
                                <div class="card-header fw-semibold">Add Bed</div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('hostel.beds.store') }}">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Room <span class="text-danger">*</span></label>
                                            <select name="hostel_room_id" class="form-select" required>
                                                <option value="">— Select Room —</option>
                                                @foreach($beds->pluck('room')->unique('id')->filter() as $room)
                                                    {{-- rooms populated via beds --}}
                                                @endforeach
                                                {{-- use all rooms from beds collection --}}
                                                @php $allRooms = \App\Models\HostelRoom::with('hostel')->get(); @endphp
                                                @foreach($allRooms as $r)
                                                    <option value="{{ $r->id }}">{{ $r->hostel->name }} — Room {{ $r->room_number }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Bed Name / Number <span class="text-danger">*</span></label>
                                            <input type="text" name="name" class="form-control" required placeholder="e.g. Bed A">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                            <select name="status" class="form-select" required>
                                                <option value="Available">Available</option>
                                                <option value="Occupied">Occupied</option>
                                                <option value="Maintenance">Maintenance</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-plus-circle"></i> Add Bed</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="card shadow-sm">
                                <div class="card-body p-0">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr><th>Bed</th><th>Room</th><th>Hostel</th><th>Status</th><th>Actions</th></tr>
                                        </thead>
                                        <tbody>
                                            @forelse($beds as $bed)
                                            <tr>
                                                <td class="fw-semibold">{{ $bed->name }}</td>
                                                <td class="small">Room {{ $bed->room->room_number ?? '—' }}</td>
                                                <td class="small text-muted">{{ $bed->room->hostel->name ?? '—' }}</td>
                                                <td>
                                                    @php
                                                        $sc = match($bed->status) {
                                                            'Available'   => 'bg-success',
                                                            'Occupied'    => 'bg-danger',
                                                            'Maintenance' => 'bg-warning text-dark',
                                                            default       => 'bg-secondary',
                                                        };
                                                    @endphp
                                                    <span class="badge {{ $sc }}">{{ $bed->status }}</span>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-1">
                                                        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editBed{{ $bed->id }}"><i class="bi bi-pencil"></i></button>
                                                        <form method="POST" action="{{ route('hostel.beds.destroy', $bed->id) }}" onsubmit="return confirm('Delete bed?')">
                                                            @csrf @method('DELETE')
                                                            <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                            {{-- Edit modal --}}
                                            <div class="modal fade" id="editBed{{ $bed->id }}" tabindex="-1">
                                                <div class="modal-dialog modal-sm">
                                                    <div class="modal-content">
                                                        <form method="POST" action="{{ route('hostel.beds.update', $bed->id) }}">
                                                            @csrf @method('PUT')
                                                            <div class="modal-header"><h5 class="modal-title">Edit Bed</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                                            <div class="modal-body">
                                                                <div class="mb-3"><label class="form-label">Bed Name</label><input type="text" name="name" class="form-control" value="{{ $bed->name }}" required></div>
                                                                <div class="mb-3"><label class="form-label">Status</label>
                                                                    <select name="status" class="form-select">
                                                                        <option value="Available"   @selected($bed->status==='Available')>Available</option>
                                                                        <option value="Occupied"    @selected($bed->status==='Occupied')>Occupied</option>
                                                                        <option value="Maintenance" @selected($bed->status==='Maintenance')>Maintenance</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary btn-sm">Save</button></div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            @empty
                                            <tr><td colspan="5" class="text-center text-muted py-4">No beds yet.</td></tr>
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
