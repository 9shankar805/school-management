@extends('layouts.app')
@section('title', 'Hostel Maintenance')

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
                    <i class="bi bi-tools"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Hostel Maintenance Requests</h2>
                    <p class="text-slate-500 text-sm mb-0">Log maintenance tickets, track plumbing/electrical repairs, and manage resolutions</p>
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
            <a href="{{ route('hostel.visitors.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-person-lines me-1"></i> Visitors
            </a>
            <a href="{{ route('hostel.maintenance.index') }}" class="btn btn-purple bg-purple-600 text-white font-bold px-3.5 py-2 rounded-xl text-xs shadow-sm">
                <i class="bi bi-tools me-1"></i> Maintenance
            </a>
        </div>
    </div>

    @include('session-messages')

    <div class="row g-4">
        <!-- Raise Request Form Card -->
        @unlessrole('student|parent')
        <div class="col-12 col-lg-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-b border-slate-100">
                    <div>
                        <h6 class="font-bold text-slate-800 mb-0">Raise Maintenance Ticket</h6>
                        <p class="text-xs text-slate-400 mb-0">Report damaged fixtures or room issues</p>
                    </div>
                    <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 d-flex align-items-center justify-content-center">
                        <i class="bi bi-wrench-adjustable-circle"></i>
                    </span>
                </div>

                <form method="POST" action="{{ route('hostel.maintenance.store') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Hostel Building <span class="text-rose-500">*</span></label>
                        <select name="hostel_id" id="mHostelSel" class="form-select rounded-xl border-slate-200 py-2.5 text-sm font-medium text-slate-800" required onchange="loadMRooms(this.value)">
                            <option value="">-- Select Hostel --</option>
                            @foreach($hostels as $h)
                                <option value="{{ $h->id }}">{{ $h->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Hostel Room <span class="text-rose-500">*</span></label>
                        <select name="hostel_room_id" id="mRoomSel" class="form-select rounded-xl border-slate-200 py-2.5 text-sm font-medium text-slate-800" required>
                            <option value="">Select hostel first...</option>
                        </select>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Issue Category <span class="text-rose-500">*</span></label>
                            <input type="text" name="issue_type" class="form-control rounded-xl border-slate-200 py-2 text-sm font-semibold text-slate-800" required placeholder="e.g. Plumbing, Electrical">
                        </div>
                        <div class="col-6">
                            <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Priority Level <span class="text-rose-500">*</span></label>
                            <select name="priority" class="form-select rounded-xl border-slate-200 py-2 text-sm font-medium text-slate-800" required>
                                <option value="Low">Low</option>
                                <option value="Medium">Medium</option>
                                <option value="High">High (Urgent)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Description of Issue <span class="text-rose-500">*</span></label>
                        <textarea name="description" class="form-control rounded-xl border-slate-200 p-2.5 text-sm" rows="3" required placeholder="Describe what is broken or needs repair..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-amber bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white font-bold w-100 py-2.5 rounded-xl shadow-md shadow-amber-500/20 transition">
                        <i class="bi bi-exclamation-triangle me-1.5"></i> Raise Maintenance Request
                    </button>
                </form>
            </div>
        </div>
        @endunlessrole

        <!-- Maintenance Directory List Table Card -->
        <div class="col-12 {{ auth()->user()->hasRole('student') ? 'col-lg-12' : 'col-lg-8' }}">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
                <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
                    <div>
                        <h5 class="font-bold text-slate-800 mb-0">Maintenance Tickets Register</h5>
                        <p class="text-xs text-slate-400 mb-0">Reported hostel maintenance tasks & resolution statuses</p>
                    </div>
                    <span class="badge bg-purple-50 text-purple-700 font-semibold text-xs px-3 py-1 rounded-full">
                        Total Tickets: {{ count($requests) }}
                    </span>
                </div>

                @if(count($requests) > 0)
                <div class="table-responsive">
                    <table class="table align-middle text-sm mb-0">
                        <thead>
                            <tr class="text-xs font-semibold text-slate-400 uppercase tracking-wider bg-slate-50">
                                <th class="py-3 px-4 rounded-s-xl">Hostel & Room</th>
                                <th class="py-3 px-4">Issue Details</th>
                                <th class="py-3 px-4 text-center">Priority</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4">Reporter</th>
                                <th class="py-3 px-4 rounded-e-xl text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($requests as $req)
                            <tr>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-800">{{ $req->hostel->name ?? 'N/A' }}</div>
                                    <div class="text-xs text-slate-400">Room {{ $req->room->room_number ?? '—' }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-800 text-xs">{{ $req->issue_type }}</div>
                                    <div class="text-xs text-slate-500">{{ Str::limit($req->description, 50) }}</div>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($req->priority === 'High')
                                    <span class="badge bg-rose-100 text-rose-700 text-xs px-2.5 py-1 rounded-full font-semibold">
                                        High
                                    </span>
                                    @elseif($req->priority === 'Medium')
                                    <span class="badge bg-amber-100 text-amber-700 text-xs px-2.5 py-1 rounded-full font-semibold">
                                        Medium
                                    </span>
                                    @else
                                    <span class="badge bg-slate-100 text-slate-600 text-xs px-2.5 py-1 rounded-full font-semibold">
                                        Low
                                    </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @unlessrole('student|parent')
                                    <form method="POST" action="{{ route('hostel.maintenance.update', $req->id) }}" class="d-inline">
                                        @csrf @method('PUT')
                                        <select name="status" class="form-select form-select-sm rounded-lg border-slate-200 text-xs font-semibold py-1 px-2" onchange="this.form.submit()" style="width: 130px;">
                                            <option value="Pending" @selected($req->status==='Pending')>Pending</option>
                                            <option value="In Progress" @selected($req->status==='In Progress')>In Progress</option>
                                            <option value="Resolved" @selected($req->status==='Resolved')>Resolved</option>
                                        </select>
                                    </form>
                                    @else
                                    <span class="badge {{ $req->status === 'Resolved' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }} text-xs px-2.5 py-1 rounded-full font-semibold">
                                        {{ $req->status }}
                                    </span>
                                    @endunlessrole
                                </td>
                                <td class="py-3 px-4 text-xs font-semibold text-slate-600">
                                    {{ $req->reporter?->first_name ?? 'Staff/Resident' }}
                                </td>
                                <td class="py-3 px-4 text-end">
                                    @unlessrole('student|parent')
                                    <form method="POST" action="{{ route('hostel.maintenance.destroy', $req->id) }}" onsubmit="return confirm('Delete maintenance request?')" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger text-xs py-1 px-2.5 rounded-lg" title="Delete Ticket">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endunlessrole
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5 text-slate-400">
                    <i class="bi bi-tools display-3 text-slate-200 mb-3 block"></i>
                    <h6 class="font-bold text-slate-600 mb-1">No Maintenance Requests</h6>
                    <p class="mb-0 text-xs">Raise maintenance tickets using the form on the left.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function loadMRooms(hostelId) {
    const sel = document.getElementById('mRoomSel');
    sel.innerHTML = '<option value="">Loading rooms...</option>';
    if (!hostelId) { sel.innerHTML = '<option value="">Select hostel first...</option>'; return; }
    const hostels = @json($hostels->load('rooms'));
    const hostel = hostels.find(h => h.id == hostelId);
    sel.innerHTML = '<option value="">-- Select Room --</option>';
    (hostel?.rooms || []).forEach(r => {
        const o = document.createElement('option');
        o.value = r.id; o.textContent = 'Room ' + r.room_number + ' (' + r.room_type + ')';
        sel.appendChild(o);
    });
}
</script>
@endpush
@endsection
