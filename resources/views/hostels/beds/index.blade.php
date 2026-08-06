@extends('layouts.app')
@section('title', 'Hostel Beds')

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
                    <i class="bi bi-lamp"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Hostel Beds & Occupancy Inventory</h2>
                    <p class="text-slate-500 text-sm mb-0">Track individual bed allocations, room capacities, and bed statuses</p>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('hostel.hostels.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-building me-1"></i> Hostels
            </a>
            <a href="{{ route('hostel.rooms.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-door-closed me-1"></i> Rooms
            </a>
            <a href="{{ route('hostel.beds.index') }}" class="btn btn-purple bg-purple-600 text-white font-bold px-3.5 py-2 rounded-xl text-xs shadow-sm">
                <i class="bi bi-lamp me-1"></i> Beds
            </a>
            <a href="{{ route('hostel.allocations.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-person-badge me-1"></i> Allocations
            </a>
        </div>
    </div>

    @include('session-messages')

    <div class="row g-4">
        <!-- Add Bed Form Card -->
        @unlessrole('student|parent')
        <div class="col-12 col-lg-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-b border-slate-100">
                    <div>
                        <h6 class="font-bold text-slate-800 mb-0">Add Bed Slot</h6>
                        <p class="text-xs text-slate-400 mb-0">Register a bed inside a hostel room</p>
                    </div>
                    <span class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 d-flex align-items-center justify-content-center">
                        <i class="bi bi-plus-lg"></i>
                    </span>
                </div>

                <form method="POST" action="{{ route('hostel.beds.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Select Hostel Room <span class="text-rose-500">*</span></label>
                        <select name="hostel_room_id" class="form-select rounded-xl border-slate-200 py-2.5 text-sm font-medium text-slate-800" required>
                            <option value="">-- Choose Room --</option>
                            @php $allRooms = \App\Models\HostelRoom::with('hostel')->get(); @endphp
                            @foreach($allRooms as $r)
                                <option value="{{ $r->id }}">{{ $r->hostel->name ?? 'Hostel' }} — Room {{ $r->room_number }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Bed Designation / Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" class="form-control rounded-xl border-slate-200 py-2.5 text-sm font-bold text-slate-800" required placeholder="e.g. Bed A, Lower Bunk #1">
                    </div>

                    <div class="mb-4">
                        <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Bed Status <span class="text-rose-500">*</span></label>
                        <select name="status" class="form-select rounded-xl border-slate-200 py-2.5 text-sm font-medium text-slate-800" required>
                            <option value="Available">Available (Vacant)</option>
                            <option value="Occupied">Occupied (Allocated)</option>
                            <option value="Maintenance">Under Maintenance</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-purple bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold w-100 py-2.5 rounded-xl shadow-md shadow-purple-500/20 transition">
                        <i class="bi bi-plus-circle me-1.5"></i> Register Bed Slot
                    </button>
                </form>
            </div>
        </div>
        @endunlessrole

        <!-- Beds Inventory Table Card -->
        <div class="col-12 {{ auth()->user()->hasRole('student') ? 'col-lg-12' : 'col-lg-8' }}">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
                <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
                    <div>
                        <h5 class="font-bold text-slate-800 mb-0">Beds Registry</h5>
                        <p class="text-xs text-slate-400 mb-0">All room bed slots & live availability</p>
                    </div>
                    <span class="badge bg-purple-50 text-purple-700 font-semibold text-xs px-3 py-1 rounded-full">
                        Total Beds: {{ count($beds) }}
                    </span>
                </div>

                @if(count($beds) > 0)
                <div class="table-responsive">
                    <table class="table align-middle text-sm mb-0">
                        <thead>
                            <tr class="text-xs font-semibold text-slate-400 uppercase tracking-wider bg-slate-50">
                                <th class="py-3 px-4 rounded-s-xl">Bed Name</th>
                                <th class="py-3 px-4">Room No.</th>
                                <th class="py-3 px-4">Hostel Building</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 rounded-e-xl text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($beds as $bed)
                            <tr>
                                <td class="py-3 px-4 font-bold text-slate-800">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="w-8 h-8 rounded-lg bg-purple-100 text-purple-700 d-flex align-items-center justify-content-center flex-shrink-0 text-xs">
                                            <i class="bi bi-lamp"></i>
                                        </span>
                                        <span>{{ $bed->name }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-xs font-bold text-slate-800">
                                    Room {{ $bed->room->room_number ?? '—' }}
                                </td>
                                <td class="py-3 px-4 text-xs text-slate-600 font-medium">
                                    {{ $bed->room->hostel->name ?? '—' }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($bed->status === 'Available')
                                    <span class="badge bg-emerald-100 text-emerald-700 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                        <i class="bi bi-check-circle-fill me-1"></i> Available
                                    </span>
                                    @elseif($bed->status === 'Occupied')
                                    <span class="badge bg-rose-100 text-rose-700 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                        <i class="bi bi-person-fill me-1"></i> Occupied
                                    </span>
                                    @else
                                    <span class="badge bg-amber-100 text-amber-700 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                        <i class="bi bi-tools me-1"></i> Maintenance
                                    </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-end">
                                    @unlessrole('student|parent')
                                    <button class="btn btn-sm btn-light border border-slate-200 text-slate-700 text-xs py-1 px-2.5 rounded-lg" data-bs-toggle="modal" data-bs-target="#editBed{{ $bed->id }}" title="Edit Bed">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" action="{{ route('hostel.beds.destroy', $bed->id) }}" onsubmit="return confirm('Delete bed {{ $bed->name }}?')" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger text-xs py-1 px-2.5 rounded-lg ms-1" title="Delete Bed">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endunlessrole
                                </td>
                            </tr>

                            <!-- Edit Bed Modal -->
                            <div class="modal fade" id="editBed{{ $bed->id }}" tabindex="-1">
                                <div class="modal-dialog modal-sm">
                                    <div class="modal-content rounded-2xl">
                                        <form method="POST" action="{{ route('hostel.beds.update', $bed->id) }}">
                                            @csrf @method('PUT')
                                            <div class="modal-header border-b border-slate-100">
                                                <h5 class="modal-title font-bold text-slate-800 text-sm">Edit Bed</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label font-semibold text-xs text-slate-700">Bed Name</label>
                                                    <input type="text" name="name" class="form-control rounded-xl text-sm" value="{{ $bed->name }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label font-semibold text-xs text-slate-700">Status</label>
                                                    <select name="status" class="form-select rounded-xl text-sm">
                                                        <option value="Available" @selected($bed->status==='Available')>Available</option>
                                                        <option value="Occupied" @selected($bed->status==='Occupied')>Occupied</option>
                                                        <option value="Maintenance" @selected($bed->status==='Maintenance')>Maintenance</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-t border-slate-100">
                                                <button type="button" class="btn btn-light rounded-xl text-xs font-semibold" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-purple bg-purple-600 text-white rounded-xl text-xs font-bold px-3">Save</button>
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
                    <i class="bi bi-lamp display-3 text-slate-200 mb-3 block"></i>
                    <h6 class="font-bold text-slate-600 mb-1">No Beds Registered</h6>
                    <p class="mb-0 text-xs">Register bed slots using the form on the left.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
