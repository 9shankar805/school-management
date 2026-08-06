@extends('layouts.app')
@section('title', 'Student Transport Allocations')

@section('content')
<style>
  .header-icon-box-transport {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%) !important;
    color: #ffffff !important;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
  }
  .header-icon-box-transport i {
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
                <div class="header-icon-box-transport">
                    <i class="bi bi-person-workspace"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Student Transport Allocations</h2>
                    <p class="text-slate-500 text-sm mb-0">Manage student bus subscriptions, assigned routes, pickup/drop-off stops, and monthly bus fares</p>
                </div>
            </div>
        </div>

        <!-- Navigation Bar & Action Button -->
        <div class="d-flex flex-wrap align-items-center gap-2">
            @unlessrole('student|parent')
            <a href="{{ route('transport.students.create') }}" class="btn btn-amber bg-amber-600 hover:bg-amber-700 text-white font-bold px-3.5 py-2 rounded-xl text-xs shadow-sm transition">
                <i class="bi bi-person-plus-fill me-1"></i> Allocate Student
            </a>
            @endunlessrole
            <a href="{{ route('transport.routes.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-map me-1"></i> Routes
            </a>
            <a href="{{ route('transport.attendance.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-clipboard-check me-1"></i> Attendance
            </a>
        </div>
    </div>

    @include('session-messages')

    <!-- Filter Control Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-4">
        <form method="GET" action="{{ route('transport.students.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Search Student</label>
                <input type="text" name="search" class="form-control rounded-xl border-slate-200 py-2 text-sm font-medium text-slate-800" placeholder="Search student name or email..." value="{{ request('search') }}">
            </div>

            <div class="col-12 col-md-3">
                <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Filter Route</label>
                <select name="route_id" class="form-select rounded-xl border-slate-200 py-2 text-sm font-medium text-slate-800">
                    <option value="">All Transport Routes</option>
                    @foreach($routes as $r)
                        <option value="{{ $r->id }}" @selected(request('route_id') == $r->id)>{{ $r->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-3">
                <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Allocation Status</label>
                <select name="status" class="form-select rounded-xl border-slate-200 py-2 text-sm font-medium text-slate-800">
                    <option value="">All Statuses</option>
                    <option value="active" @selected(request('status')==='active')>Active</option>
                    <option value="suspended" @selected(request('status')==='suspended')>Suspended</option>
                    <option value="cancelled" @selected(request('status')==='cancelled')>Cancelled</option>
                </select>
            </div>

            <div class="col-12 col-md-2">
                <button type="submit" class="btn btn-amber bg-amber-600 hover:bg-amber-700 text-white font-bold py-2 px-4 rounded-xl text-sm w-100 shadow-sm transition">
                    <i class="bi bi-filter me-1"></i> Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Allocations Directory Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
        <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
            <div>
                <h5 class="font-bold text-slate-800 mb-0">Active Student Subscriptions</h5>
                <p class="text-xs text-slate-400 mb-0">Bus passenger assignments & monthly fees</p>
            </div>
            <span class="badge bg-amber-50 text-amber-700 font-semibold text-xs px-3 py-1 rounded-full">
                Total Subscriptions: {{ $allocations->total() }}
            </span>
        </div>

        @if($allocations->count() > 0)
        <div class="table-responsive">
            <table class="table align-middle text-sm mb-0">
                <thead>
                    <tr class="text-xs font-semibold text-slate-400 uppercase tracking-wider bg-slate-50">
                        <th class="py-3 px-4 rounded-s-xl">Student Passenger</th>
                        <th class="py-3 px-4">Assigned Route</th>
                        <th class="py-3 px-4">Bus Stop</th>
                        <th class="py-3 px-4 text-center">Direction</th>
                        <th class="py-3 px-4">Monthly Fee</th>
                        <th class="py-3 px-4">Start Date</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 rounded-e-xl text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($allocations as $a)
                    <tr>
                        <td class="py-3 px-4">
                            <div class="d-flex align-items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-700 font-bold d-flex align-items-center justify-content-center text-xs flex-shrink-0">
                                    {{ strtoupper(substr($a->student->first_name ?? 'S', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800">{{ $a->student->first_name ?? 'Student' }} {{ $a->student->last_name ?? '' }}</div>
                                    <div class="text-xs text-slate-400">{{ $a->student->email ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-4 font-semibold text-slate-800 text-xs">
                            {{ $a->route->name ?? 'N/A' }}
                        </td>
                        <td class="py-3 px-4 text-xs text-slate-600 font-medium">
                            <i class="bi bi-geo-alt text-amber-600 me-1"></i> {{ $a->stop?->name ?? 'Default Stop' }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="badge bg-slate-100 text-slate-700 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                {{ ucfirst(str_replace('_',' ',$a->direction)) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 font-bold text-slate-800 text-xs">
                            Rs. {{ number_format($a->monthly_fee, 2) }}
                        </td>
                        <td class="py-3 px-4 text-xs font-semibold text-slate-700">
                            {{ $a->start_date->format('d M Y') }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($a->status === 'active')
                            <span class="badge bg-emerald-100 text-emerald-700 text-xs px-2.5 py-1 rounded-full font-semibold">
                                <i class="bi bi-check-circle-fill me-1"></i> Active
                            </span>
                            @elseif($a->status === 'suspended')
                            <span class="badge bg-amber-100 text-amber-700 text-xs px-2.5 py-1 rounded-full font-semibold">
                                <i class="bi bi-exclamation-circle-fill me-1"></i> Suspended
                            </span>
                            @else
                            <span class="badge bg-slate-100 text-slate-600 text-xs px-2.5 py-1 rounded-full font-semibold">
                                Cancelled
                            </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-end">
                            @unlessrole('student|parent')
                            <a href="{{ route('transport.students.edit', $a->id) }}" class="btn btn-sm btn-light border border-slate-200 text-slate-700 text-xs py-1 px-2.5 rounded-lg" title="Edit Allocation">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('transport.students.destroy', $a->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove student transport allocation?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger text-xs py-1 px-2 rounded-lg ms-1" title="Remove Allocation">
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

        <div class="mt-4">
            {{ $allocations->links() }}
        </div>
        @else
        <div class="text-center py-5 text-slate-400">
            <i class="bi bi-person-workspace display-3 text-slate-200 mb-3 block"></i>
            <h6 class="font-bold text-slate-600 mb-1">No Student Transport Allocations</h6>
            <p class="mb-0 text-xs">Assign students to bus routes using the Allocate Student button above.</p>
        </div>
        @endif
    </div>
</div>
@endsection
