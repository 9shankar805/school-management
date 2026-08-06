@extends('layouts.app')
@section('title', 'Transport Routes Directory')

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
                    <i class="bi bi-map-fill"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Transport Routes & Bus Stops Directory</h2>
                    <p class="text-slate-500 text-sm mb-0">Manage bus pickup/drop routes, morning & afternoon schedules, monthly fees, and stop lists</p>
                </div>
            </div>
        </div>

        <!-- Navigation Bar & Action Button -->
        <div class="d-flex flex-wrap align-items-center gap-2">
            @unlessrole('student|parent')
            <a href="{{ route('transport.routes.create') }}" class="btn btn-amber bg-amber-600 hover:bg-amber-700 text-white font-bold px-3.5 py-2 rounded-xl text-xs shadow-sm transition">
                <i class="bi bi-plus-circle me-1"></i> Add Route
            </a>
            @endunlessrole
            <a href="{{ route('transport.vehicles.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-bus-front me-1"></i> Fleet
            </a>
            <a href="{{ route('transport.students.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-person-workspace me-1"></i> Student Transport
            </a>
        </div>
    </div>

    @include('session-messages')

    <!-- Filter Control Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-4">
        <form method="GET" action="{{ route('transport.routes.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-6">
                <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Search Route</label>
                <input type="text" name="search" class="form-control rounded-xl border-slate-200 py-2 text-sm font-medium text-slate-800" placeholder="Search route name or route code..." value="{{ request('search') }}">
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Route Status</label>
                <select name="status" class="form-select rounded-xl border-slate-200 py-2 text-sm font-medium text-slate-800">
                    <option value="">All Statuses</option>
                    <option value="active" @selected(request('status')==='active')>Active</option>
                    <option value="suspended" @selected(request('status')==='suspended')>Suspended</option>
                    <option value="discontinued" @selected(request('status')==='discontinued')>Discontinued</option>
                </select>
            </div>

            <div class="col-12 col-md-2">
                <button type="submit" class="btn btn-amber bg-amber-600 hover:bg-amber-700 text-white font-bold py-2 px-4 rounded-xl text-sm w-100 shadow-sm transition">
                    <i class="bi bi-filter me-1"></i> Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Routes Directory Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
        <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
            <div>
                <h5 class="font-bold text-slate-800 mb-0">Configured Transport Routes</h5>
                <p class="text-xs text-slate-400 mb-0">Route schedules, monthly fares, and assigned vehicles</p>
            </div>
            <span class="badge bg-amber-50 text-amber-700 font-semibold text-xs px-3 py-1 rounded-full">
                Total Routes: {{ $routes->total() }}
            </span>
        </div>

        @if($routes->count() > 0)
        <div class="table-responsive">
            <table class="table align-middle text-sm mb-0">
                <thead>
                    <tr class="text-xs font-semibold text-slate-400 uppercase tracking-wider bg-slate-50">
                        <th class="py-3 px-4 rounded-s-xl">Route Name & Code</th>
                        <th class="py-3 px-4">Vehicle</th>
                        <th class="py-3 px-4">Driver</th>
                        <th class="py-3 px-4 text-center">Morning Pickup</th>
                        <th class="py-3 px-4 text-center">Afternoon Drop</th>
                        <th class="py-3 px-4 text-center">Active Passengers</th>
                        <th class="py-3 px-4">Monthly Fee</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 rounded-e-xl text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($routes as $r)
                    <tr>
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-800">
                                <a href="{{ route('transport.routes.show', $r->id) }}" class="text-slate-800 hover:text-amber-600 transition">
                                    {{ $r->name }}
                                </a>
                            </div>
                            @if($r->code)
                            <code class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded text-[11px] font-mono">
                                {{ $r->code }}
                            </code>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-xs font-semibold text-slate-700">
                            {{ $r->vehicle?->name ?? 'Unassigned' }}
                        </td>
                        <td class="py-3 px-4 text-xs font-semibold text-slate-700">
                            {{ $r->driver?->name ?? 'Unassigned' }}
                        </td>
                        <td class="py-3 px-4 text-center text-xs font-bold text-emerald-700">
                            {{ $r->morning_departure ?? '—' }}
                        </td>
                        <td class="py-3 px-4 text-center text-xs font-bold text-amber-700">
                            {{ $r->afternoon_departure ?? '—' }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="badge bg-amber-100 text-amber-800 font-bold text-xs px-2.5 py-1 rounded-full">
                                {{ $r->active_students_count }} Students
                            </span>
                        </td>
                        <td class="py-3 px-4 text-xs font-bold text-slate-800">
                            Rs. {{ number_format($r->monthly_fee, 2) }} / mo
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($r->status === 'active')
                            <span class="badge bg-emerald-100 text-emerald-700 text-xs px-2.5 py-1 rounded-full font-semibold">
                                <i class="bi bi-check-circle-fill me-1"></i> Active
                            </span>
                            @elseif($r->status === 'suspended')
                            <span class="badge bg-amber-100 text-amber-700 text-xs px-2.5 py-1 rounded-full font-semibold">
                                <i class="bi bi-exclamation-circle-fill me-1"></i> Suspended
                            </span>
                            @else
                            <span class="badge bg-slate-100 text-slate-600 text-xs px-2.5 py-1 rounded-full font-semibold">
                                Discontinued
                            </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-end">
                            <a href="{{ route('transport.routes.show', $r->id) }}" class="btn btn-sm btn-light border border-slate-200 text-slate-700 text-xs py-1 px-2.5 rounded-lg" title="View Route Details & Bus Stops">
                                <i class="bi bi-eye"></i>
                            </a>
                            @unlessrole('student|parent')
                            <a href="{{ route('transport.routes.edit', $r->id) }}" class="btn btn-sm btn-light border border-slate-200 text-slate-700 text-xs py-1 px-2.5 rounded-lg ms-1" title="Edit Route">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('transport.routes.destroy', $r->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this transport route?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger text-xs py-1 px-2 rounded-lg ms-1" title="Delete Route">
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
            {{ $routes->links() }}
        </div>
        @else
        <div class="text-center py-5 text-slate-400">
            <i class="bi bi-map display-3 text-slate-200 mb-3 block"></i>
            <h6 class="font-bold text-slate-600 mb-1">No Transport Routes Found</h6>
            <p class="mb-0 text-xs">Create your first bus route using the Add Route button above.</p>
        </div>
        @endif
    </div>
</div>
@endsection
