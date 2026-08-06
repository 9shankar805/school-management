@extends('layouts.app')
@section('title', 'RFID Scan Log')

@section('content')
<style>
  .header-icon-box-rfid {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: linear-gradient(135deg, #0284c7 0%, #06b6d4 100%) !important;
    color: #ffffff !important;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
  }
  .header-icon-box-rfid i {
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
                <div class="header-icon-box-rfid">
                    <i class="bi bi-broadcast"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">RFID & Biometric Gate Scans</h2>
                    <p class="text-slate-500 text-sm mb-0">Real-time attendance gate scans, turnstile logs, and RFID card taps</p>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('rfid.scans') }}" class="btn btn-cyan bg-cyan-600 text-white font-bold px-3.5 py-2 rounded-xl text-xs shadow-sm">
                <i class="bi bi-activity me-1"></i> Scan Log
            </a>
            <a href="{{ route('rfid.cards') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-credit-card-2-front me-1"></i> Assigned Cards
            </a>
            <a href="{{ route('rfid.devices') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-cpu me-1"></i> Gate Devices
            </a>
        </div>
    </div>

    @include('session-messages')

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-md-3">
            <div class="bg-white rounded-2xl border border-slate-200 p-3.5 shadow-sm d-flex align-items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-cyan-50 text-cyan-600 d-flex align-items-center justify-content-center flex-shrink-0">
                    <i class="bi bi-broadcast fs-5"></i>
                </div>
                <div>
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Scans</div>
                    <div class="h5 font-extrabold text-slate-800 mb-0">{{ $scans->total() }}</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
            <div class="bg-white rounded-2xl border border-slate-200 p-3.5 shadow-sm d-flex align-items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 d-flex align-items-center justify-content-center flex-shrink-0">
                    <i class="bi bi-box-arrow-in-right fs-5"></i>
                </div>
                <div>
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Entry (IN)</div>
                    <div class="h5 font-extrabold text-emerald-600 mb-0">
                        {{ $scans->getCollection()->where('direction', 'in')->count() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
            <div class="bg-white rounded-2xl border border-slate-200 p-3.5 shadow-sm d-flex align-items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 d-flex align-items-center justify-content-center flex-shrink-0">
                    <i class="bi bi-box-arrow-right fs-5"></i>
                </div>
                <div>
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Exit (OUT)</div>
                    <div class="h5 font-extrabold text-amber-600 mb-0">
                        {{ $scans->getCollection()->where('direction', 'out')->count() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
            <div class="bg-white rounded-2xl border border-slate-200 p-3.5 shadow-sm d-flex align-items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 d-flex align-items-center justify-content-center flex-shrink-0">
                    <i class="bi bi-check2-square fs-5"></i>
                </div>
                <div>
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Attendance Marked</div>
                    <div class="h5 font-extrabold text-indigo-600 mb-0">
                        {{ $scans->getCollection()->where('attendance_marked', true)->count() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Control Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-4">
        <form method="GET" action="{{ route('rfid.scans') }}" class="row g-3 align-items-end">
            <div class="col-12 col-sm-6 col-md-3">
                <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Date</label>
                <input type="date" name="date" value="{{ request('date', today()->toDateString()) }}" class="form-control rounded-xl border-slate-200 py-2 text-sm font-medium text-slate-800">
            </div>

            <div class="col-12 col-sm-6 col-md-4">
                <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Gate Device</label>
                <select name="device_id" class="form-select rounded-xl border-slate-200 py-2 text-sm font-medium text-slate-800">
                    <option value="">All Gates / Devices</option>
                    @foreach($devices as $d)
                        <option value="{{ $d->id }}" @selected(request('device_id')==$d->id)>{{ $d->name }} ({{ $d->location ?? 'Main' }})</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-sm-6 col-md-3">
                <button type="submit" class="btn btn-cyan bg-cyan-600 hover:bg-cyan-700 text-white font-bold py-2 px-4 rounded-xl text-sm shadow-sm transition">
                    <i class="bi bi-filter me-1"></i> Apply Filter
                </button>
                @if(request()->hasAny(['date','device_id']))
                <a href="{{ route('rfid.scans') }}" class="btn btn-light border border-slate-200 text-slate-600 font-semibold py-2 px-3 rounded-xl text-sm ms-1">
                    Clear
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Scans Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
        <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
            <div>
                <h5 class="font-bold text-slate-800 mb-0">Scan Activity Logs</h5>
                <p class="text-xs text-slate-400 mb-0">Live feeds from hardware RFID turnstiles and biometric scanners</p>
            </div>
            <span class="badge bg-cyan-50 text-cyan-700 font-semibold text-xs px-3 py-1 rounded-full">
                Showing {{ $scans->count() }} of {{ $scans->total() }}
            </span>
        </div>

        @if($scans->count() > 0)
        <div class="table-responsive">
            <table class="table align-middle text-sm mb-0">
                <thead>
                    <tr class="text-xs font-semibold text-slate-400 uppercase tracking-wider bg-slate-50">
                        <th class="py-3 px-4 rounded-s-xl">Scan Time</th>
                        <th class="py-3 px-4">User</th>
                        <th class="py-3 px-4">Card UID / Biometric ID</th>
                        <th class="py-3 px-4">Device & Location</th>
                        <th class="py-3 px-4 text-center">Direction</th>
                        <th class="py-3 px-4 rounded-e-xl text-center">Attendance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($scans as $scan)
                    <tr>
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-800">{{ $scan->scanned_at->format('h:i:s A') }}</div>
                            <div class="text-xs text-slate-400">{{ $scan->scanned_at->diffForHumans() }}</div>
                        </td>
                        <td class="py-3 px-4">
                            @if($scan->user)
                            <div class="d-flex align-items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 font-bold d-flex align-items-center justify-content-center text-xs flex-shrink-0">
                                    {{ strtoupper(substr($scan->user->first_name ?? 'U', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800">{{ $scan->user->first_name }} {{ $scan->user->last_name }}</div>
                                    <span class="badge bg-slate-100 text-slate-600 text-[10px] px-2 py-0.5 rounded-full font-semibold">
                                        {{ ucfirst($scan->user->role ?? 'User') }}
                                    </span>
                                </div>
                            </div>
                            @else
                            <span class="badge bg-rose-100 text-rose-700 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                <i class="bi bi-question-circle me-1"></i> Unknown Card
                            </span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <code class="px-2 py-1 bg-slate-100 text-slate-700 rounded-lg text-xs font-mono">
                                {{ $scan->card_uid ?? $scan->biometric_template_id ?? 'N/A' }}
                            </code>
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-semibold text-slate-700">{{ $scan->device->name ?? 'Main Gate' }}</div>
                            <div class="text-xs text-slate-400">{{ $scan->device->location ?? 'School Entrance' }}</div>
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($scan->direction === 'in')
                            <span class="badge bg-emerald-100 text-emerald-700 text-xs px-3 py-1 rounded-full font-semibold">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Entry (IN)
                            </span>
                            @elseif($scan->direction === 'out')
                            <span class="badge bg-amber-100 text-amber-700 text-xs px-3 py-1 rounded-full font-semibold">
                                <i class="bi bi-box-arrow-right me-1"></i> Exit (OUT)
                            </span>
                            @else
                            <span class="badge bg-slate-100 text-slate-600 text-xs px-3 py-1 rounded-full font-semibold">
                                Scan
                            </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($scan->attendance_marked)
                            <span class="badge bg-indigo-100 text-indigo-700 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                <i class="bi bi-check-circle-fill me-1"></i> Marked
                            </span>
                            @else
                            <span class="badge bg-slate-100 text-slate-500 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                Logged
                            </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $scans->links() }}
        </div>
        @else
        <div class="text-center py-5 text-slate-400">
            <i class="bi bi-broadcast display-3 text-slate-200 mb-3 block"></i>
            <h6 class="font-bold text-slate-600 mb-1">No Scan Logs Found</h6>
            <p class="mb-0 text-xs">No RFID card taps or biometric turnstile events were logged for this filter.</p>
        </div>
        @endif
    </div>
</div>
@endsection
