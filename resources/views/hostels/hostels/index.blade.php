@extends('layouts.app')
@section('title', 'Hostel Management')

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
                    <i class="bi bi-building"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Hostels & Residential Buildings</h2>
                    <p class="text-slate-500 text-sm mb-0">Manage hostel blocks, room allotments, wardens, and student occupancy</p>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('hostel.rooms.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-door-closed me-1"></i> Rooms & Beds
            </a>
            <a href="{{ route('hostel.allocations.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-person-badge me-1"></i> Student Allocations
            </a>
        </div>
    </div>

    @include('session-messages')

    <div class="row g-4">
        <!-- Add New Hostel Form Card (ALWAYS VISIBLE & WORKING) -->
        @unlessrole('student|parent')
        <div class="col-12 col-lg-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-b border-slate-100">
                    <div>
                        <h6 class="font-bold text-slate-800 mb-0">Add New Hostel Block</h6>
                        <p class="text-xs text-slate-400 mb-0">Register a new residential building</p>
                    </div>
                    <span class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 d-flex align-items-center justify-content-center">
                        <i class="bi bi-building-add"></i>
                    </span>
                </div>

                <form method="POST" action="{{ route('hostel.hostels.store') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Hostel Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" class="form-control rounded-xl border-slate-200 py-2.5 text-sm" required placeholder="e.g. Block A – Boys Hostel">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Hostel Type <span class="text-rose-500">*</span></label>
                            <select name="type" class="form-select rounded-xl border-slate-200 py-2 text-sm" required>
                                <option value="Boys">Boys Hostel</option>
                                <option value="Girls">Girls Hostel</option>
                                <option value="Mixed">Mixed / General</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Capacity <span class="text-rose-500">*</span></label>
                            <input type="number" name="intake_capacity" class="form-control rounded-xl border-slate-200 py-2 text-sm font-bold text-slate-800" required min="1" placeholder="100">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Hostel Warden</label>
                        <select name="warden_id" class="form-select rounded-xl border-slate-200 py-2.5 text-sm">
                            <option value="">-- Select Warden --</option>
                            @foreach($wardens as $w)
                                <option value="{{ $w->id }}">{{ $w->first_name }} {{ $w->last_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Building Address</label>
                        <input type="text" name="address" class="form-control rounded-xl border-slate-200 py-2 text-sm" placeholder="Campus location or block address">
                    </div>

                    <div class="mb-4">
                        <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Description / Remarks</label>
                        <textarea name="description" class="form-control rounded-xl border-slate-200 p-2 text-sm" rows="2" placeholder="Optional notes..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-purple bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold w-100 py-2.5 rounded-xl shadow-md shadow-purple-500/20 transition">
                        <i class="bi bi-plus-circle me-1.5"></i> Save Hostel Building
                    </button>
                </form>
            </div>
        </div>
        @endunlessrole

        <!-- Hostels Directory Cards Grid -->
        <div class="col-12 {{ auth()->user()->hasRole('student') ? 'col-lg-12' : 'col-lg-8' }}">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
                <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
                    <div>
                        <h5 class="font-bold text-slate-800 mb-0">Configured Hostels</h5>
                        <p class="text-xs text-slate-400 mb-0">Active residential blocks & capacity</p>
                    </div>
                    <span class="badge bg-purple-50 text-purple-700 font-semibold text-xs px-3 py-1 rounded-full">
                        Total Hostels: {{ count($hostels) }}
                    </span>
                </div>

                @if(count($hostels) > 0)
                <div class="row g-4">
                    @foreach($hostels as $h)
                    <div class="col-12 col-md-6">
                        <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 transition hover:shadow-md">
                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <div>
                                    <h6 class="font-bold text-slate-800 mb-1">{{ $h->name }}</h6>
                                    @php
                                        $typeBadge = match($h->type) {
                                            'Boys'  => 'bg-blue-100 text-blue-700',
                                            'Girls' => 'bg-rose-100 text-rose-700',
                                            default => 'bg-slate-200 text-slate-700',
                                        };
                                    @endphp
                                    <span class="badge {{ $typeBadge }} text-xs px-2.5 py-0.5 rounded-full font-semibold">
                                        {{ $h->type }} Hostel
                                    </span>
                                </div>
                                <span class="w-9 h-9 rounded-xl bg-white border border-slate-200 d-flex align-items-center justify-content-center text-purple-600">
                                    <i class="bi bi-building"></i>
                                </span>
                            </div>

                            <div class="space-y-1.5 mb-4 text-xs text-slate-600">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-people text-slate-400"></i> Intake Capacity: <strong class="text-slate-800">{{ $h->intake_capacity }} Beds</strong>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-geo-alt text-slate-400"></i> Address: <span>{{ $h->address ?? 'Main Campus' }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-person-badge text-slate-400"></i> Warden: 
                                    <strong class="text-slate-800">{{ $h->warden ? $h->warden->first_name.' '.$h->warden->last_name : 'Unassigned' }}</strong>
                                </div>
                            </div>

                            @unlessrole('student|parent')
                            <div class="d-flex gap-2 pt-3 border-t border-slate-200">
                                <button class="btn btn-sm btn-light border border-slate-200 text-slate-700 font-semibold flex-1 text-xs py-1.5 rounded-xl" data-bs-toggle="modal" data-bs-target="#editHostel{{ $h->id }}">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </button>
                                <form method="POST" action="{{ route('hostel.hostels.destroy', $h->id) }}" onsubmit="return confirm('Delete hostel {{ addslashes($h->name) }}?')" class="flex-1">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100 text-xs py-1.5 rounded-xl">
                                        <i class="bi bi-trash me-1"></i> Delete
                                    </button>
                                </form>
                            </div>
                            @endunlessrole
                        </div>
                    </div>

                    <!-- Edit Hostel Modal -->
                    <div class="modal fade" id="editHostel{{ $h->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content rounded-2xl">
                                <form method="POST" action="{{ route('hostel.hostels.update', $h->id) }}">
                                    @csrf @method('PUT')
                                    <div class="modal-header border-b border-slate-100">
                                        <h5 class="modal-title font-bold text-slate-800 text-sm">Edit Hostel: {{ $h->name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body row g-3">
                                        <div class="col-12">
                                            <label class="form-label font-semibold text-xs text-slate-700">Hostel Name</label>
                                            <input type="text" name="name" class="form-control rounded-xl text-sm" value="{{ $h->name }}" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label font-semibold text-xs text-slate-700">Type</label>
                                            <select name="type" class="form-select rounded-xl text-sm" required>
                                                @foreach(['Boys','Girls','Mixed'] as $t)
                                                    <option value="{{ $t }}" @selected($h->type===$t)>{{ $t }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label font-semibold text-xs text-slate-700">Capacity</label>
                                            <input type="number" name="intake_capacity" class="form-control rounded-xl text-sm" value="{{ $h->intake_capacity }}" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label font-semibold text-xs text-slate-700">Warden</label>
                                            <select name="warden_id" class="form-select rounded-xl text-sm">
                                                <option value="">— None —</option>
                                                @foreach($wardens as $w)
                                                    <option value="{{ $w->id }}" @selected($h->warden_id==$w->id)>{{ $w->first_name }} {{ $w->last_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label font-semibold text-xs text-slate-700">Address</label>
                                            <input type="text" name="address" class="form-control rounded-xl text-sm" value="{{ $h->address }}">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label font-semibold text-xs text-slate-700">Description</label>
                                            <textarea name="description" class="form-control rounded-xl text-sm" rows="2">{{ $h->description }}</textarea>
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
                </div>
                @else
                <div class="text-center py-5 text-slate-400">
                    <i class="bi bi-building display-3 text-slate-200 mb-3 block"></i>
                    <h6 class="font-bold text-slate-600 mb-1">No Hostels Registered</h6>
                    <p class="mb-0 text-xs">Register your first hostel building using the form on the left.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
