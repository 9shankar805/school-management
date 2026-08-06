@extends('layouts.app')
@section('title', 'Hostel Visitor Log')

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
                    <i class="bi bi-person-lines-fill"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Hostel Visitor & Parent Log</h2>
                    <p class="text-slate-500 text-sm mb-0">Track parent visits, guest check-in/out times, and visitor entry logs</p>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('hostel.hostels.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-building me-1"></i> Hostels
            </a>
            <a href="{{ route('hostel.attendances.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-clipboard-check me-1"></i> Roll Call
            </a>
            <a href="{{ route('hostel.visitors.index') }}" class="btn btn-purple bg-purple-600 text-white font-bold px-3.5 py-2 rounded-xl text-xs shadow-sm">
                <i class="bi bi-person-lines me-1"></i> Visitors
            </a>
            <a href="{{ route('hostel.maintenance.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-tools me-1"></i> Maintenance
            </a>
        </div>
    </div>

    @include('session-messages')

    <!-- Log Visitor Form Card -->
    @unlessrole('student|parent')
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-b border-slate-100">
            <div>
                <h6 class="font-bold text-slate-800 mb-0">Log New Visitor Entry</h6>
                <p class="text-xs text-slate-400 mb-0">Record visitor details, resident student being visited, and entry time</p>
            </div>
            <span class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 d-flex align-items-center justify-content-center">
                <i class="bi bi-person-plus-fill"></i>
            </span>
        </div>

        <form method="POST" action="{{ route('hostel.visitors.store') }}">
            @csrf
            <div class="row g-3 mb-3">
                <div class="col-12 col-md-3">
                    <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Hostel Building <span class="text-rose-500">*</span></label>
                    <select name="hostel_id" class="form-select rounded-xl border-slate-200 py-2.5 text-sm font-medium text-slate-800" required>
                        <option value="">-- Choose Hostel --</option>
                        @foreach($hostels as $h)
                            <option value="{{ $h->id }}">{{ $h->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Student Visited <span class="text-rose-500">*</span></label>
                    <select name="student_id" class="form-select rounded-xl border-slate-200 py-2.5 text-sm font-medium text-slate-800" required>
                        <option value="">-- Select Resident --</option>
                        @foreach($students as $s)
                            <option value="{{ $s->id }}">{{ $s->first_name }} {{ $s->last_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Visitor Full Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="visitor_name" class="form-control rounded-xl border-slate-200 py-2.5 text-sm font-bold text-slate-800" required placeholder="e.g. Ram Prasad Sharma">
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Relation <span class="text-rose-500">*</span></label>
                    <input type="text" name="relation" class="form-control rounded-xl border-slate-200 py-2.5 text-sm" required placeholder="e.g. Father, Mother, Uncle">
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Visit Date <span class="text-rose-500">*</span></label>
                    <input type="date" name="date" class="form-control rounded-xl border-slate-200 py-2.5 text-sm font-medium text-slate-800" value="{{ date('Y-m-d') }}" required>
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">In Time <span class="text-rose-500">*</span></label>
                    <input type="time" name="in_time" class="form-control rounded-xl border-slate-200 py-2.5 text-sm font-medium text-slate-800" required>
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Out Time</label>
                    <input type="time" name="out_time" class="form-control rounded-xl border-slate-200 py-2.5 text-sm text-slate-800">
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Purpose of Visit</label>
                    <input type="text" name="purpose" class="form-control rounded-xl border-slate-200 py-2.5 text-sm" placeholder="e.g. Deliver supplies, Weekend permission">
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-purple bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold px-6 py-2.5 rounded-xl shadow-md shadow-purple-500/20 transition">
                    <i class="bi bi-check-circle me-1.5"></i> Log Visitor Entry
                </button>
            </div>
        </form>
    </div>
    @endunlessrole

    <!-- Visitors Directory Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
        <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
            <div>
                <h5 class="font-bold text-slate-800 mb-0">Visitor Log Register</h5>
                <p class="text-xs text-slate-400 mb-0">Recorded visitor entries and check-out timestamps</p>
            </div>
            <span class="badge bg-purple-50 text-purple-700 font-semibold text-xs px-3 py-1 rounded-full">
                Total Visits: {{ count($visitors) }}
            </span>
        </div>

        @if(count($visitors) > 0)
        <div class="table-responsive">
            <table class="table align-middle text-sm mb-0">
                <thead>
                    <tr class="text-xs font-semibold text-slate-400 uppercase tracking-wider bg-slate-50">
                        <th class="py-3 px-4 rounded-s-xl">Visit Date</th>
                        <th class="py-3 px-4">Visitor Name</th>
                        <th class="py-3 px-4">Relation</th>
                        <th class="py-3 px-4">Student Visited</th>
                        <th class="py-3 px-4">Hostel</th>
                        <th class="py-3 px-4 text-center">In Time</th>
                        <th class="py-3 px-4 text-center">Out Time</th>
                        <th class="py-3 px-4 rounded-e-xl text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($visitors as $v)
                    <tr>
                        <td class="py-3 px-4 text-xs font-semibold text-slate-700">
                            {{ $v->date }}
                        </td>
                        <td class="py-3 px-4 font-bold text-slate-800">
                            {{ $v->visitor_name }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="badge bg-slate-100 text-slate-700 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                {{ $v->relation }}
                            </span>
                        </td>
                        <td class="py-3 px-4 font-semibold text-slate-800">
                            {{ $v->student->first_name ?? 'Resident' }} {{ $v->student->last_name ?? '' }}
                        </td>
                        <td class="py-3 px-4 text-xs text-slate-600 font-medium">
                            {{ $v->hostel->name ?? 'N/A' }}
                        </td>
                        <td class="py-3 px-4 text-center text-xs font-bold text-emerald-700">
                            {{ $v->in_time }}
                        </td>
                        <td class="py-3 px-4 text-center text-xs">
                            @if($v->out_time)
                                <span class="font-bold text-slate-700">{{ $v->out_time }}</span>
                            @else
                                @unlessrole('student|parent')
                                <form method="POST" action="{{ route('hostel.visitors.update', $v->id) }}" class="d-inline-flex align-items-center gap-1">
                                    @csrf @method('PUT')
                                    <input type="time" name="out_time" class="form-control form-control-sm rounded-lg border-slate-200 py-1 text-xs font-medium" style="width: 100px;" required>
                                    <button type="submit" class="btn btn-sm btn-emerald bg-emerald-600 text-white font-semibold text-xs py-1 px-2.5 rounded-lg">Out</button>
                                </form>
                                @else
                                <span class="badge bg-amber-100 text-amber-700 text-xs px-2.5 py-1 rounded-full font-semibold">Inside</span>
                                @endunlessrole
                            @endif
                        </td>
                        <td class="py-3 px-4 text-end">
                            @unlessrole('student|parent')
                            <form method="POST" action="{{ route('hostel.visitors.destroy', $v->id) }}" onsubmit="return confirm('Delete visitor log entry?')" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger text-xs py-1 px-2.5 rounded-lg ms-1" title="Delete Log">
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
            <i class="bi bi-person-lines-fill display-3 text-slate-200 mb-3 block"></i>
            <h6 class="font-bold text-slate-600 mb-1">No Visitor Logs Recorded</h6>
            <p class="mb-0 text-xs">Record guest visits using the form above.</p>
        </div>
        @endif
    </div>
</div>
@endsection
