@extends('layouts.app')
@section('title', 'Hostel Rooms')

@section('content')
<style>
  .header-icon-box-hostel {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: linear-gradient(135deg, #7c3aed 0%, #9333ea 100%) !important;
    color: #ffffff !important;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(147, 51, 234, 0.3);
  }
  .header-icon-box-hostel i {
    color: #ffffff !important;
    font-size: 22px !important;
    line-height: 1 !important;
    display: inline-block !important;
    opacity: 1 !important;
    visibility: visible !important;
  }
</style>

<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-3 mb-1">
                <div class="header-icon-box-hostel">
                    <i class="bi bi-door-closed"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Hostel Rooms & Accommodation</h2>
                    <p class="text-slate-500 text-sm mb-0">Configure room numbers, AC/Non-AC room types, bed capacities, and monthly rates</p>
                </div>
            </div>
        </div>

        <!-- Navigation Bar -->
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('hostel.hostels.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-building me-1"></i> Hostels
            </a>
            <a href="{{ route('hostel.rooms.index') }}" class="btn btn-purple bg-purple-600 text-white font-bold px-3.5 py-2 rounded-xl text-xs shadow-sm">
                <i class="bi bi-door-closed me-1"></i> Rooms
            </a>
            <a href="{{ route('hostel.beds.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-lamp me-1"></i> Beds
            </a>
            <a href="{{ route('hostel.allocations.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-person-badge me-1"></i> Allocations
            </a>
        </div>
    </div>

    @include('session-messages')

    <div class="row g-4">
        <!-- Add Room Form Card -->
        @unlessrole('student|parent')
        <div class="col-12 col-lg-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-b border-slate-100">
                    <div>
                        <h6 class="font-bold text-slate-800 mb-0">Add Hostel Room</h6>
                        <p class="text-xs text-slate-400 mb-0">Register a new room inside a building</p>
                    </div>
                    <span class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 d-flex align-items-center justify-content-center">
                        <i class="bi bi-plus-lg"></i>
                    </span>
                </div>

                <form method="POST" action="{{ route('hostel.rooms.store') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Select Hostel <span class="text-rose-500">*</span></label>
                        <select name="hostel_id" class="form-select rounded-xl border-slate-200 py-2.5 text-sm font-medium text-slate-800" required>
                            <option value="">-- Select Hostel Building --</option>
                            @foreach($hostels as $h)
                                <option value="{{ $h->id }}">{{ $h->name }} ({{ $h->type }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Room Number <span class="text-rose-500">*</span></label>
                        <input type="text" name="room_number" class="form-control rounded-xl border-slate-200 py-2.5 text-sm font-bold text-slate-800" required placeholder="e.g. Room 101, B-204">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Room Type <span class="text-rose-500">*</span></label>
                            <select name="room_type" class="form-select rounded-xl border-slate-200 py-2 text-sm" required>
                                <option value="Non-AC">Non-AC</option>
                                <option value="AC">AC (Air Conditioned)</option>
                            </select>
                        </div>

                        <div class="col-6">
                            <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Bed Capacity <span class="text-rose-500">*</span></label>
                            <input type="number" name="capacity" class="form-control rounded-xl border-slate-200 py-2 text-sm font-bold text-slate-800" required min="1" placeholder="2">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Cost Per Bed (Monthly) <span class="text-rose-500">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text rounded-s-xl bg-slate-100 border-slate-200 text-xs font-semibold">Rs.</span>
                            <input type="number" name="cost_per_bed" class="form-control rounded-e-xl border-slate-200 py-2 text-sm font-bold text-slate-800" required min="0" step="0.01" placeholder="5000">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Room Facilities / Notes</label>
                        <textarea name="description" class="form-control rounded-xl border-slate-200 p-2 text-sm" rows="2" placeholder="e.g. Attached bathroom, balcony view..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-purple bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold w-100 py-2.5 rounded-xl shadow-md shadow-purple-500/20 transition">
                        <i class="bi bi-plus-circle me-1.5"></i> Add Room
                    </button>
                </form>
            </div>
        </div>
        @endunlessrole

        <!-- Rooms Directory List Table Card -->
        <div class="col-12 {{ auth()->user()->hasRole('student') ? 'col-lg-12' : 'col-lg-8' }}">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
                <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
                    <div>
                        <h5 class="font-bold text-slate-800 mb-0">Configured Hostel Rooms</h5>
                        <p class="text-xs text-slate-400 mb-0">Active room list, rates, and bed capacity</p>
                    </div>
                    <span class="badge bg-purple-50 text-purple-700 font-semibold text-xs px-3 py-1 rounded-full">
                        Total Rooms: {{ count($rooms) }}
                    </span>
                </div>

                @if(count($rooms) > 0)
                <div class="table-responsive">
                    <table class="table align-middle text-sm mb-0">
                        <thead>
                            <tr class="text-xs font-semibold text-slate-400 uppercase tracking-wider bg-slate-50">
                                <th class="py-3 px-4 rounded-s-xl">Room No.</th>
                                <th class="py-3 px-4">Hostel Building</th>
                                <th class="py-3 px-4">Room Type</th>
                                <th class="py-3 px-4 text-center">Bed Capacity</th>
                                <th class="py-3 px-4">Fee / Bed</th>
                                <th class="py-3 px-4 rounded-e-xl text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($rooms as $room)
                            <tr>
                                <td class="py-3 px-4 font-bold text-slate-800">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="w-8 h-8 rounded-lg bg-purple-100 text-purple-700 d-flex align-items-center justify-content-center flex-shrink-0 text-xs">
                                            <i class="bi bi-door-closed-fill"></i>
                                        </span>
                                        <span>{{ $room->room_number }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-xs font-semibold text-slate-700">
                                    {{ $room->hostel->name ?? 'N/A' }}
                                </td>
                                <td class="py-3 px-4">
                                    @if($room->room_type === 'AC')
                                    <span class="badge bg-sky-100 text-sky-700 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                        <i class="bi bi-snow me-1"></i> AC Room
                                    </span>
                                    @else
                                    <span class="badge bg-slate-100 text-slate-700 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                        Non-AC
                                    </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="badge bg-purple-50 text-purple-700 font-bold text-xs px-3 py-1 rounded-full">
                                        {{ $room->capacity }} Beds
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-bold text-slate-800 text-xs">
                                    Rs. {{ number_format($room->cost_per_bed, 2) }}
                                </td>
                                <td class="py-3 px-4 text-end">
                                    @unlessrole('student|parent')
                                    <button class="btn btn-sm btn-light border border-slate-200 text-slate-700 text-xs py-1 px-2.5 rounded-lg" data-bs-toggle="modal" data-bs-target="#editRoom{{ $room->id }}" title="Edit Room">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" action="{{ route('hostel.rooms.destroy', $room->id) }}" onsubmit="return confirm('Delete room {{ $room->room_number }}?')" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger text-xs py-1 px-2.5 rounded-lg ms-1" title="Delete Room">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endunlessrole
                                </td>
                            </tr>

                            <!-- Edit Room Modal -->
                            <div class="modal fade" id="editRoom{{ $room->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content rounded-2xl">
                                        <form method="POST" action="{{ route('hostel.rooms.update', $room->id) }}">
                                            @csrf @method('PUT')
                                            <div class="modal-header border-b border-slate-100">
                                                <h5 class="modal-title font-bold text-slate-800 text-sm">Edit Room: {{ $room->room_number }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body row g-3">
                                                <div class="col-6">
                                                    <label class="form-label font-semibold text-xs text-slate-700">Hostel</label>
                                                    <select name="hostel_id" class="form-select rounded-xl text-sm" required>
                                                        @foreach($hostels as $h)
                                                            <option value="{{ $h->id }}" @selected($room->hostel_id==$h->id)>{{ $h->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label font-semibold text-xs text-slate-700">Room Number</label>
                                                    <input type="text" name="room_number" class="form-control rounded-xl text-sm" value="{{ $room->room_number }}" required>
                                                </div>
                                                <div class="col-4">
                                                    <label class="form-label font-semibold text-xs text-slate-700">Type</label>
                                                    <select name="room_type" class="form-select rounded-xl text-sm" required>
                                                        <option value="Non-AC" @selected($room->room_type==='Non-AC')>Non-AC</option>
                                                        <option value="AC" @selected($room->room_type==='AC')>AC</option>
                                                    </select>
                                                </div>
                                                <div class="col-4">
                                                    <label class="form-label font-semibold text-xs text-slate-700">Capacity</label>
                                                    <input type="number" name="capacity" class="form-control rounded-xl text-sm" value="{{ $room->capacity }}" required>
                                                </div>
                                                <div class="col-4">
                                                    <label class="form-label font-semibold text-xs text-slate-700">Cost/Bed ($)</label>
                                                    <input type="number" name="cost_per_bed" class="form-control rounded-xl text-sm" value="{{ $room->cost_per_bed }}" step="0.01" required>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label font-semibold text-xs text-slate-700">Description</label>
                                                    <textarea name="description" class="form-control rounded-xl text-sm" rows="2">{{ $room->description }}</textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-t border-slate-100">
                                                <button type="button" class="btn btn-light rounded-xl text-xs font-semibold" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-purple bg-purple-600 text-white rounded-xl text-xs font-bold px-4">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5 text-slate-400">
                    <i class="bi bi-door-closed display-3 text-slate-200 mb-3 block"></i>
                    <h6 class="font-bold text-slate-600 mb-1">No Rooms Configured</h6>
                    <p class="mb-0 text-xs">Add your first hostel room using the form on the left.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
