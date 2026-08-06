@extends('layouts.app')
@section('title', 'Transport Drivers Directory')

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
                    <i class="bi bi-person-badge-fill"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Transport Drivers Directory</h2>
                    <p class="text-slate-500 text-sm mb-0">Manage school bus drivers, contact numbers, driving license compliance, and vehicle assignments</p>
                </div>
            </div>
        </div>

        <!-- Navigation Bar & Action Button -->
        <div class="d-flex flex-wrap align-items-center gap-2">
            @unlessrole('student|parent')
            <a href="{{ route('transport.drivers.create') }}" class="btn btn-amber bg-amber-600 hover:bg-amber-700 text-white font-bold px-3.5 py-2 rounded-xl text-xs shadow-sm transition">
                <i class="bi bi-person-plus-fill me-1"></i> Add Driver
            </a>
            @endunlessrole
            <a href="{{ route('transport.vehicles.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-bus-front me-1"></i> Fleet
            </a>
            <a href="{{ route('transport.routes.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-map me-1"></i> Routes
            </a>
        </div>
    </div>

    @include('session-messages')

    @if($licenseAlerts->isNotEmpty())
    <div class="alert alert-warning rounded-2xl border-0 shadow-sm p-3.5 mb-4 bg-amber-50 text-amber-900 border-l-4 border-amber-500 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2.5">
            <i class="bi bi-exclamation-triangle-fill text-amber-600 fs-5"></i>
            <span class="text-sm">
                <strong>{{ $licenseAlerts->count() }}</strong> driver(s) have driving licenses expiring within 30 days or past due date.
            </span>
        </div>
    </div>
    @endif

    <!-- Filter Control Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-4">
        <form method="GET" action="{{ route('transport.drivers.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-6">
                <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Search Driver</label>
                <input type="text" name="search" class="form-control rounded-xl border-slate-200 py-2 text-sm font-medium text-slate-800" placeholder="Search driver name, phone, license number..." value="{{ request('search') }}">
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Driver Status</label>
                <select name="status" class="form-select rounded-xl border-slate-200 py-2 text-sm font-medium text-slate-800">
                    <option value="">All Statuses</option>
                    <option value="active" @selected(request('status')==='active')>Active</option>
                    <option value="on_leave" @selected(request('status')==='on_leave')>On Leave</option>
                    <option value="terminated" @selected(request('status')==='terminated')>Terminated</option>
                </select>
            </div>

            <div class="col-12 col-md-2">
                <button type="submit" class="btn btn-amber bg-amber-600 hover:bg-amber-700 text-white font-bold py-2 px-4 rounded-xl text-sm w-100 shadow-sm transition">
                    <i class="bi bi-filter me-1"></i> Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Drivers Directory Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
        <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
            <div>
                <h5 class="font-bold text-slate-800 mb-0">Registered Transport Drivers</h5>
                <p class="text-xs text-slate-400 mb-0">Driver contact registry & license validity</p>
            </div>
            <span class="badge bg-amber-50 text-amber-700 font-semibold text-xs px-3 py-1 rounded-full">
                Total Drivers: {{ $drivers->total() }}
            </span>
        </div>

        @if($drivers->count() > 0)
        <div class="table-responsive">
            <table class="table align-middle text-sm mb-0">
                <thead>
                    <tr class="text-xs font-semibold text-slate-400 uppercase tracking-wider bg-slate-50">
                        <th class="py-3 px-4 rounded-s-xl">Driver Info</th>
                        <th class="py-3 px-4">Contact Phone</th>
                        <th class="py-3 px-4">License No.</th>
                        <th class="py-3 px-4">License Expiry</th>
                        <th class="py-3 px-4">Assigned Vehicle</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 rounded-e-xl text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($drivers as $d)
                    <tr>
                        <td class="py-3 px-4">
                            <div class="d-flex align-items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-700 font-bold d-flex align-items-center justify-content-center text-xs flex-shrink-0">
                                    {{ strtoupper(substr($d->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800">
                                        <a href="{{ route('transport.drivers.show', $d->id) }}" class="text-slate-800 hover:text-amber-600 transition">
                                            {{ $d->name }}
                                        </a>
                                    </div>
                                    <div class="text-xs text-slate-400">ID: {{ $d->employee_id ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-xs font-semibold text-slate-700">
                            {{ $d->phone ?? '—' }}
                        </td>
                        <td class="py-3 px-4">
                            <code class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded text-[11px] font-mono font-bold">
                                {{ $d->license_number }}
                            </code>
                        </td>
                        <td class="py-3 px-4 text-xs">
                            @if($d->is_license_expired)
                                <span class="badge bg-rose-100 text-rose-700 text-xs px-2 py-0.5 rounded font-bold">
                                    <i class="bi bi-x-circle me-1"></i> Expired ({{ $d->license_expiry?->format('d M Y') }})
                                </span>
                            @elseif($d->is_license_expiring_soon)
                                <span class="badge bg-amber-100 text-amber-700 text-xs px-2 py-0.5 rounded font-semibold">
                                    <i class="bi bi-exclamation-triangle me-1"></i> Soon ({{ $d->license_expiry?->format('d M Y') }})
                                </span>
                            @else
                                <span class="text-slate-600 font-medium">{{ $d->license_expiry?->format('d M Y') ?? '—' }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-semibold text-slate-800 text-xs">
                            {{ $d->currentVehicle?->name ?? 'Unassigned' }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($d->status === 'active')
                            <span class="badge bg-emerald-100 text-emerald-700 text-xs px-2.5 py-1 rounded-full font-semibold">
                                <i class="bi bi-check-circle-fill me-1"></i> Active
                            </span>
                            @elseif($d->status === 'on_leave')
                            <span class="badge bg-amber-100 text-amber-700 text-xs px-2.5 py-1 rounded-full font-semibold">
                                <i class="bi bi-clock me-1"></i> On Leave
                            </span>
                            @else
                            <span class="badge bg-slate-100 text-slate-600 text-xs px-2.5 py-1 rounded-full font-semibold">
                                Terminated
                            </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-end">
                            <a href="{{ route('transport.drivers.show', $d->id) }}" class="btn btn-sm btn-light border border-slate-200 text-slate-700 text-xs py-1 px-2.5 rounded-lg" title="View Profile">
                                <i class="bi bi-eye"></i>
                            </a>
                            @unlessrole('student|parent')
                            <a href="{{ route('transport.drivers.edit', $d->id) }}" class="btn btn-sm btn-light border border-slate-200 text-slate-700 text-xs py-1 px-2.5 rounded-lg ms-1" title="Edit Driver">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('transport.drivers.destroy', $d->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove driver?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger text-xs py-1 px-2 rounded-lg ms-1" title="Remove Driver">
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
            {{ $drivers->links() }}
        </div>
        @else
        <div class="text-center py-5 text-slate-400">
            <i class="bi bi-person-badge display-3 text-slate-200 mb-3 block"></i>
            <h6 class="font-bold text-slate-600 mb-1">No Drivers Found</h6>
            <p class="mb-0 text-xs">Register transport drivers using the Add Driver button above.</p>
        </div>
        @endif
    </div>
</div>
@endsection
