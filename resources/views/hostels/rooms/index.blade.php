@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-1"><i class="bi bi-door-closed"></i> Hostel Rooms</h1>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('hostel.hostels.index') }}">Hostels</a></li>
                            <li class="breadcrumb-item active">Rooms</li>
                        </ol>
                    </nav>

                    @if(session('status'))
                        <div class="alert alert-success alert-dismissible fade show">{{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    @endif

                    <div class="row g-4">
                        {{-- Add Room Form --}}
                        <div class="col-md-4">
                            <div class="card shadow-sm">
                                <div class="card-header fw-semibold">Add Room</div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('hostel.rooms.store') }}">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Hostel <span class="text-danger">*</span></label>
                                            <select name="hostel_id" class="form-select" required>
                                                <option value="">— Select —</option>
                                                @foreach($hostels as $h)
                                                    <option value="{{ $h->id }}">{{ $h->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Room Number <span class="text-danger">*</span></label>
                                            <input type="text" name="room_number" class="form-control" required placeholder="e.g. 101">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Room Type <span class="text-danger">*</span></label>
                                            <select name="room_type" class="form-select" required>
                                                <option value="Non-AC">Non-AC</option>
                                                <option value="AC">AC</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Capacity <span class="text-danger">*</span></label>
                                            <input type="number" name="capacity" class="form-control" required min="1">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Cost Per Bed ($) <span class="text-danger">*</span></label>
                                            <input type="number" name="cost_per_bed" class="form-control" required min="0" step="0.01">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Description</label>
                                            <textarea name="description" class="form-control" rows="2"></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-plus-circle"></i> Add Room</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Rooms Table --}}
                        <div class="col-md-8">
                            <div class="card shadow-sm">
                                <div class="card-body p-0">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr><th>Room</th><th>Hostel</th><th>Type</th><th class="text-center">Capacity</th><th>Fee/Bed</th><th>Actions</th></tr>
                                        </thead>
                                        <tbody>
                                            @forelse($rooms as $room)
                                            <tr>
                                                <td class="fw-semibold">{{ $room->room_number }}</td>
                                                <td class="small">{{ $room->hostel->name }}</td>
                                                <td><span class="badge {{ $room->room_type==='AC' ? 'bg-info text-dark' : 'bg-secondary' }}">{{ $room->room_type }}</span></td>
                                                <td class="text-center">{{ $room->capacity }}</td>
                                                <td>${{ number_format($room->cost_per_bed,2) }}</td>
                                                <td>
                                                    <div class="d-flex gap-1">
                                                        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editRoom{{ $room->id }}">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <form method="POST" action="{{ route('hostel.rooms.destroy', $room->id) }}" onsubmit="return confirm('Delete room {{ $room->room_number }}?')">
                                                            @csrf @method('DELETE')
                                                            <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>

                                            {{-- Edit modal --}}
                                            <div class="modal fade" id="editRoom{{ $room->id }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form method="POST" action="{{ route('hostel.rooms.update', $room->id) }}">
                                                            @csrf @method('PUT')
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Edit Room {{ $room->room_number }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body row g-3">
                                                                <div class="col-md-6"><label class="form-label">Hostel</label><select name="hostel_id" class="form-select" required>@foreach($hostels as $h)<option value="{{ $h->id }}" @selected($room->hostel_id==$h->id)>{{ $h->name }}</option>@endforeach</select></div>
                                                                <div class="col-md-6"><label class="form-label">Room Number</label><input type="text" name="room_number" class="form-control" value="{{ $room->room_number }}" required></div>
                                                                <div class="col-md-4"><label class="form-label">Type</label><select name="room_type" class="form-select" required><option value="Non-AC" @selected($room->room_type==='Non-AC')>Non-AC</option><option value="AC" @selected($room->room_type==='AC')>AC</option></select></div>
                                                                <div class="col-md-4"><label class="form-label">Capacity</label><input type="number" name="capacity" class="form-control" value="{{ $room->capacity }}" required></div>
                                                                <div class="col-md-4"><label class="form-label">Cost/Bed ($)</label><input type="number" name="cost_per_bed" class="form-control" value="{{ $room->cost_per_bed }}" step="0.01" required></div>
                                                                <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2">{{ $room->description }}</textarea></div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            @empty
                                            <tr><td colspan="6" class="text-center text-muted py-4">No rooms yet. Add one on the left.</td></tr>
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
