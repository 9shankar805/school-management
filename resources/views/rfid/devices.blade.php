@extends('layouts.app')
@section('title', 'Biometric Devices')

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
                    <i class="bi bi-cpu"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Biometric & RFID Gate Devices</h2>
                    <p class="text-slate-500 text-sm mb-0">Hardware turnstile terminals, RFID readers, and face-recognition scanners</p>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('rfid.scans') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-activity me-1"></i> Scan Log
            </a>
            <a href="{{ route('rfid.cards') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-credit-card-2-front me-1"></i> Assigned Cards
            </a>
            <a href="{{ route('rfid.devices') }}" class="btn btn-cyan bg-cyan-600 text-white font-bold px-3.5 py-2 rounded-xl text-xs shadow-sm">
                <i class="bi bi-cpu me-1"></i> Gate Devices
            </a>
        </div>
    </div>

    @include('session-messages')

    <div class="row g-4">
        <!-- Register Device Form & API Integration Info -->
        <div class="col-12 col-lg-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-4">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-b border-slate-100">
                    <div>
                        <h6 class="font-bold text-slate-800 mb-0">Register Gate Terminal</h6>
                        <p class="text-xs text-slate-400 mb-0">Add hardware RFID / Biometric scanner</p>
                    </div>
                    <span class="w-8 h-8 rounded-lg bg-cyan-50 text-cyan-600 d-flex align-items-center justify-content-center">
                        <i class="bi bi-cpu"></i>
                    </span>
                </div>

                <form method="POST" action="{{ route('rfid.devices.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Device Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" class="form-control rounded-xl border-slate-200 py-2.5 text-sm" required placeholder="e.g. Main Gate RFID Turnstile #1">
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Serial Number <span class="text-rose-500">*</span></label>
                        <input type="text" name="serial_number" class="form-control rounded-xl border-slate-200 py-2.5 text-sm font-mono" required placeholder="e.g. SN-89472-A">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Location</label>
                            <input type="text" name="location" class="form-control rounded-xl border-slate-200 py-2 text-sm" placeholder="e.g. Main Entrance">
                        </div>
                        <div class="col-6">
                            <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Scanner Type</label>
                            <select name="type" class="form-select rounded-xl border-slate-200 py-2 text-sm">
                                <option value="rfid">RFID Tap</option>
                                <option value="nfc">NFC Tag</option>
                                <option value="fingerprint">Biometric Fingerprint</option>
                                <option value="face">Face Recognition</option>
                                <option value="barcode">QR / Barcode</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Static IP Address</label>
                        <input type="text" name="ip_address" class="form-control rounded-xl border-slate-200 py-2.5 text-sm font-mono" placeholder="192.168.1.100">
                    </div>

                    <button type="submit" class="btn btn-cyan bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-700 hover:to-blue-700 text-white font-bold w-100 py-2.5 rounded-xl shadow-md shadow-cyan-500/20 transition">
                        <i class="bi bi-plus-circle me-1.5"></i> Register Hardware Device
                    </button>
                </form>
            </div>

            <!-- API Payload Doc Card -->
            <div class="bg-slate-900 text-slate-100 rounded-2xl p-4 shadow-sm">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-code-slash text-cyan-400 font-bold"></i>
                    <h6 class="font-bold text-white mb-0 text-sm">Hardware Webhook Specs</h6>
                </div>
                <div class="text-xs text-slate-400 mb-2">Endpoint URL:</div>
                <code class="block p-2 bg-slate-800 text-cyan-300 rounded-lg text-[11px] font-mono mb-2 break-all">
                    POST {{ url('/api/rfid/scan') }}
                </code>
                <div class="text-xs text-slate-400 mb-1">Headers:</div>
                <code class="block p-2 bg-slate-800 text-amber-300 rounded-lg text-[11px] font-mono mb-2">
                    X-Device-Key: {api_key}
                </code>
                <div class="text-xs text-slate-400 mb-1">Payload:</div>
                <pre class="p-2 bg-slate-800 text-emerald-300 rounded-lg text-[11px] font-mono mb-0">{ "card_uid": "A1B2C3D4", "direction": "in" }</pre>
            </div>
        </div>

        <!-- Devices List Table -->
        <div class="col-12 col-lg-8">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
                <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
                    <div>
                        <h5 class="font-bold text-slate-800 mb-0">Registered Gate Scanners</h5>
                        <p class="text-xs text-slate-400 mb-0">Active hardware terminals & API keys</p>
                    </div>
                    <span class="badge bg-cyan-50 text-cyan-700 font-semibold text-xs px-3 py-1 rounded-full">
                        Total Devices: {{ count($devices) }}
                    </span>
                </div>

                @if(count($devices) > 0)
                <div class="table-responsive">
                    <table class="table align-middle text-sm mb-0">
                        <thead>
                            <tr class="text-xs font-semibold text-slate-400 uppercase tracking-wider bg-slate-50">
                                <th class="py-3 px-4 rounded-s-xl">Device Name</th>
                                <th class="py-3 px-4">Type</th>
                                <th class="py-3 px-4">Location</th>
                                <th class="py-3 px-4">Serial Number</th>
                                <th class="py-3 px-4 text-center">Last Pulse</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 rounded-e-xl text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($devices as $device)
                            <tr>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-800">{{ $device->name }}</div>
                                    <div class="text-xs font-mono text-slate-400" title="{{ $device->api_key }}">
                                        Key: {{ Str::limit($device->api_key, 12) }}...
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="badge bg-slate-100 text-slate-700 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                        {{ strtoupper($device->type) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-xs font-medium text-slate-600">
                                    {{ $device->location ?? 'Main Entrance' }}
                                </td>
                                <td class="py-3 px-4">
                                    <code class="px-2 py-1 bg-slate-100 text-slate-700 rounded-lg text-xs font-mono">
                                        {{ $device->serial_number }}
                                    </code>
                                </td>
                                <td class="py-3 px-4 text-center text-xs text-slate-500">
                                    {{ $device->last_seen_at?->diffForHumans() ?? 'Never' }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($device->is_active)
                                    <span class="badge bg-emerald-100 text-emerald-700 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                        <i class="bi bi-check-circle-fill me-1"></i> Active
                                    </span>
                                    @else
                                    <span class="badge bg-rose-100 text-rose-700 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                        <i class="bi bi-x-circle-fill me-1"></i> Disabled
                                    </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-end">
                                    <form method="POST" action="{{ route('rfid.devices.toggle', $device->id) }}" class="d-inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-warning text-xs py-1 px-2.5 rounded-lg">
                                            Toggle
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('rfid.devices.destroy', $device->id) }}" class="d-inline" onsubmit="return confirm('Delete gate device?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger text-xs py-1 px-2.5 rounded-lg ms-1">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $devices->links() }}
                </div>
                @else
                <div class="text-center py-5 text-slate-400">
                    <i class="bi bi-cpu display-3 text-slate-200 mb-3 block"></i>
                    <h6 class="font-bold text-slate-600 mb-1">No Gate Devices Registered</h6>
                    <p class="mb-0 text-xs">Register your school's RFID turnstiles or biometric gates using the form on the left.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
