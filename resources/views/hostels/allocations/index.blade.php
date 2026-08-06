@extends('layouts.app')
@section('title', 'Student Hostel Allocations')

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
                    <i class="bi bi-person-badge"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Student Hostel Allocations</h2>
                    <p class="text-slate-500 text-sm mb-0">Assign student residents to hostel buildings, rooms, and specific bed slots</p>
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
            <a href="{{ route('hostel.beds.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-lamp me-1"></i> Beds
            </a>
            <a href="{{ route('hostel.allocations.index') }}" class="btn btn-purple bg-purple-600 text-white font-bold px-3.5 py-2 rounded-xl text-xs shadow-sm">
                <i class="bi bi-person-badge me-1"></i> Allocations
            </a>
        </div>
    </div>

    @include('session-messages')

    <!-- Allocate Student Form Card -->
    @unlessrole('student|parent')
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-b border-slate-100">
            <div>
                <h6 class="font-bold text-slate-800 mb-0">Allocate Student Resident</h6>
                <p class="text-xs text-slate-400 mb-0">Select student, hostel building, room, and available bed</p>
            </div>
            <span class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 d-flex align-items-center justify-content-center">
                <i class="bi bi-person-plus-fill"></i>
            </span>
        </div>

        <form method="POST" action="{{ route('hostel.allocations.store') }}">
            @csrf
            <div class="row g-3 mb-3">
                <div class="col-12 col-md-4">
                    <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Select Student <span class="text-rose-500">*</span></label>
                    <select name="student_id" class="form-select rounded-xl border-slate-200 py-2.5 text-sm font-medium text-slate-800" required>
                        <option value="">-- Select Student --</option>
                        @foreach($students as $s)
                            <option value="{{ $s->id }}">{{ $s->first_name }} {{ $s->last_name }} (ID: #{{ $s->id }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Hostel Building <span class="text-rose-500">*</span></label>
                    <select name="hostel_id" id="hostelSel" class="form-select rounded-xl border-slate-200 py-2.5 text-sm font-medium text-slate-800" required onchange="loadRooms(this.value)">
                        <option value="">-- Choose Hostel --</option>
                        @foreach($hostels as $h)
                            <option value="{{ $h->id }}">{{ $h->name }} ({{ $h->type }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-2">
                    <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Room <span class="text-rose-500">*</span></label>
                    <select name="hostel_room_id" id="roomSel" class="form-select rounded-xl border-slate-200 py-2.5 text-sm font-medium text-slate-800" required onchange="loadBeds(this.value)">
                        <option value="">Select hostel first...</option>
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Bed Slot <span class="text-rose-500">*</span></label>
                    <select name="hostel_bed_id" id="bedSel" class="form-select rounded-xl border-slate-200 py-2.5 text-sm font-medium text-slate-800" required>
                        <option value="">Select room first...</option>
                    </select>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Allocation Start Date <span class="text-rose-500">*</span></label>
                    <input type="date" name="start_date" class="form-control rounded-xl border-slate-200 py-2.5 text-sm font-medium text-slate-800" value="{{ date('Y-m-d') }}" required>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Expected End Date</label>
                    <input type="date" name="end_date" class="form-control rounded-xl border-slate-200 py-2.5 text-sm text-slate-800">
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Status</label>
                    <select name="status" class="form-select rounded-xl border-slate-200 py-2.5 text-sm font-medium text-slate-800">
                        <option value="Active">Active Resident</option>
                        <option value="Inactive">Inactive / Vacated</option>
                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-purple bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold px-6 py-2.5 rounded-xl shadow-md shadow-purple-500/20 transition">
                    <i class="bi bi-check-circle me-1.5"></i> Submit Allocation
                </button>
            </div>
        </form>
    </div>
    @endunlessrole

    <!-- Allocations Directory Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
        <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
            <div>
                <h5 class="font-bold text-slate-800 mb-0">Active Resident Allocations</h5>
                <p class="text-xs text-slate-400 mb-0">Registered student room & bed allotments</p>
            </div>
            <span class="badge bg-purple-50 text-purple-700 font-semibold text-xs px-3 py-1 rounded-full">
                Total Allocations: {{ count($allocations) }}
            </span>
        </div>

        @if(count($allocations) > 0)
        <div class="table-responsive">
            <table class="table align-middle text-sm mb-0">
                <thead>
                    <tr class="text-xs font-semibold text-slate-400 uppercase tracking-wider bg-slate-50">
                        <th class="py-3 px-4 rounded-s-xl">Student Resident</th>
                        <th class="py-3 px-4">Hostel Building</th>
                        <th class="py-3 px-4">Room & Bed</th>
                        <th class="py-3 px-4">Start Date</th>
                        <th class="py-3 px-4">End Date</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 rounded-e-xl text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($allocations as $a)
                    <tr>
                        <td class="py-3 px-4">
                            <div class="d-flex align-items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-700 font-bold d-flex align-items-center justify-content-center text-xs flex-shrink-0">
                                    {{ strtoupper(substr($a->student->first_name ?? 'S', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800">{{ $a->student->first_name ?? 'Student' }} {{ $a->student->last_name ?? '' }}</div>
                                    <div class="text-xs text-slate-400">ID: #{{ $a->student_id }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-xs font-semibold text-slate-700">
                            {{ $a->hostel->name ?? 'N/A' }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="badge bg-purple-50 text-purple-700 font-bold text-xs px-2.5 py-1 rounded-lg">
                                Room {{ $a->room->room_number ?? '—' }} ({{ $a->bed->name ?? 'Bed' }})
                            </span>
                        </td>
                        <td class="py-3 px-4 text-xs font-semibold text-slate-700">
                            {{ $a->start_date }}
                        </td>
                        <td class="py-3 px-4 text-xs text-slate-500 font-medium">
                            {{ $a->end_date ?? 'Ongoing' }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($a->status === 'Active')
                            <span class="badge bg-emerald-100 text-emerald-700 text-xs px-2.5 py-1 rounded-full font-semibold">
                                <i class="bi bi-check-circle-fill me-1"></i> Active
                            </span>
                            @else
                            <span class="badge bg-slate-100 text-slate-600 text-xs px-2.5 py-1 rounded-full font-semibold">
                                Vacated
                            </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-end">
                            @unlessrole('student|parent')
                            <button class="btn btn-sm btn-light border border-slate-200 text-slate-700 text-xs py-1 px-2.5 rounded-lg" data-bs-toggle="modal" data-bs-target="#editAlloc{{ $a->id }}" title="Edit Allocation">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="{{ route('hostel.allocations.destroy', $a->id) }}" onsubmit="return confirm('Remove student allocation?')" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger text-xs py-1 px-2.5 rounded-lg ms-1" title="Remove Allocation">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endunlessrole
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editAlloc{{ $a->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content rounded-2xl">
                                <form method="POST" action="{{ route('hostel.allocations.update', $a->id) }}">
                                    @csrf @method('PUT')
                                    <div class="modal-header border-b border-slate-100">
                                        <h5 class="modal-title font-bold text-slate-800 text-sm">Edit Allocation</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body row g-3">
                                        <div class="col-6">
                                            <label class="form-label font-semibold text-xs text-slate-700">Start Date</label>
                                            <input type="date" name="start_date" class="form-control rounded-xl text-sm" value="{{ $a->start_date }}" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label font-semibold text-xs text-slate-700">End Date</label>
                                            <input type="date" name="end_date" class="form-control rounded-xl text-sm" value="{{ $a->end_date }}">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label font-semibold text-xs text-slate-700">Status</label>
                                            <select name="status" class="form-select rounded-xl text-sm">
                                                <option value="Active" @selected($a->status==='Active')>Active Resident</option>
                                                <option value="Inactive" @selected($a->status==='Inactive')>Inactive / Vacated</option>
                                            </select>
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
            <i class="bi bi-person-badge display-3 text-slate-200 mb-3 block"></i>
            <h6 class="font-bold text-slate-600 mb-1">No Student Allocations</h6>
            <p class="mb-0 text-xs">Allocate students to hostel beds using the form above.</p>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function loadRooms(hostelId) {
    const rSel = document.getElementById('roomSel');
    const bSel = document.getElementById('bedSel');
    rSel.innerHTML = '<option value="">Loading rooms...</option>';
    bSel.innerHTML = '<option value="">Select room first...</option>';
    if (!hostelId) { rSel.innerHTML = '<option value="">Select hostel first...</option>'; return; }
    const rooms = @json($hostels->keyBy('id'));
    const hostel = rooms[hostelId];
    if (!hostel || !hostel.rooms) return;
    rSel.innerHTML = '<option value="">-- Select Room --</option>';
    hostel.rooms.forEach(r => {
        const o = document.createElement('option');
        o.value = r.id; o.textContent = 'Room ' + r.room_number + ' (' + r.room_type + ')';
        rSel.appendChild(o);
    });
}
function loadBeds(roomId) {
    const bSel = document.getElementById('bedSel');
    bSel.innerHTML = '<option value="">Loading beds...</option>';
    if (!roomId) { bSel.innerHTML = '<option value="">Select room first...</option>'; return; }
    const allBeds = @json(\App\Models\HostelBed::all());
    bSel.innerHTML = '<option value="">-- Select Available Bed --</option>';
    allBeds.filter(b => b.hostel_room_id == roomId && b.status === 'Available').forEach(b => {
        const o = document.createElement('option'); o.value = b.id; o.textContent = b.name;
        bSel.appendChild(o);
    });
}
</script>
@endpush
