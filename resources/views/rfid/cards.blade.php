@extends('layouts.app')
@section('title', 'RFID Cards')

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
                    <i class="bi bi-credit-card-2-front"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Assigned RFID & NFC Cards</h2>
                    <p class="text-slate-500 text-sm mb-0">Manage RFID keycards, NFC tags, and student/staff biometric credentials</p>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('rfid.scans') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-activity me-1"></i> Scan Log
            </a>
            <a href="{{ route('rfid.cards') }}" class="btn btn-cyan bg-cyan-600 text-white font-bold px-3.5 py-2 rounded-xl text-xs shadow-sm">
                <i class="bi bi-credit-card-2-front me-1"></i> Assigned Cards
            </a>
            <a href="{{ route('rfid.devices') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-cpu me-1"></i> Gate Devices
            </a>
        </div>
    </div>

    @include('session-messages')

    <div class="row g-4">
        <!-- Assign Card Form -->
        <div class="col-12 col-lg-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-b border-slate-100">
                    <div>
                        <h6 class="font-bold text-slate-800 mb-0">Assign Card to User</h6>
                        <p class="text-xs text-slate-400 mb-0">Pair physical card UID to student or staff</p>
                    </div>
                    <span class="w-8 h-8 rounded-lg bg-cyan-50 text-cyan-600 d-flex align-items-center justify-content-center">
                        <i class="bi bi-plus-lg"></i>
                    </span>
                </div>

                <form method="POST" action="{{ route('rfid.cards.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Select User <span class="text-rose-500">*</span></label>
                        <select name="user_id" class="form-select rounded-xl border-slate-200 py-2.5 text-sm font-medium text-slate-800" required>
                            <option value="">-- Choose Student/Staff --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->fullName }} ({{ ucfirst($u->primaryRole) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Card UID / Hex Code <span class="text-rose-500">*</span></label>
                        <input type="text" name="card_uid" class="form-control rounded-xl border-slate-200 py-2.5 text-sm font-mono" required placeholder="e.g. A1B2C3D4">
                        <div class="form-text text-xs text-slate-400 mt-1">Tap card on reader to auto-fill UID or type manually.</div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Card Type</label>
                            <select name="card_type" class="form-select rounded-xl border-slate-200 py-2 text-sm">
                                <option value="rfid">RFID (13.56MHz)</option>
                                <option value="nfc">NFC Smartcard</option>
                                <option value="barcode">Barcode / QR</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Expires On</label>
                            <input type="date" name="expires_at" class="form-control rounded-xl border-slate-200 py-2 text-sm">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-cyan bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-700 hover:to-blue-700 text-white font-bold w-100 py-2.5 rounded-xl shadow-md shadow-cyan-500/20 transition">
                        <i class="bi bi-check-circle me-1.5"></i> Assign Keycard
                    </button>
                </form>
            </div>
        </div>

        <!-- Cards Directory List -->
        <div class="col-12 col-lg-8">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
                <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
                    <div>
                        <h5 class="font-bold text-slate-800 mb-0">Assigned Keycards Directory</h5>
                        <p class="text-xs text-slate-400 mb-0">Active student & staff RFID credential registry</p>
                    </div>
                    <span class="badge bg-cyan-50 text-cyan-700 font-semibold text-xs px-3 py-1 rounded-full">
                        Total Cards: {{ $cards->total() }}
                    </span>
                </div>

                @if($cards->count() > 0)
                <div class="table-responsive">
                    <table class="table align-middle text-sm mb-0">
                        <thead>
                            <tr class="text-xs font-semibold text-slate-400 uppercase tracking-wider bg-slate-50">
                                <th class="py-3 px-4 rounded-s-xl">User Name</th>
                                <th class="py-3 px-4">Card UID</th>
                                <th class="py-3 px-4">Type</th>
                                <th class="py-3 px-4 text-center">Expires</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 rounded-e-xl text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($cards as $card)
                            <tr>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-800">{{ $card->user->fullName ?? 'User' }}</div>
                                    <span class="badge bg-slate-100 text-slate-600 text-[10px] px-2 py-0.5 rounded-full font-semibold">
                                        {{ ucfirst($card->user->primaryRole ?? 'User') }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <code class="px-2 py-1 bg-slate-100 text-slate-700 rounded-lg text-xs font-mono font-bold">
                                        {{ $card->card_uid }}
                                    </code>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="badge bg-slate-100 text-slate-700 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                        {{ strtoupper($card->card_type) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center text-xs text-slate-500 font-medium">
                                    {{ $card->expires_at?->format('d M Y') ?? 'Never' }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($card->is_valid)
                                    <span class="badge bg-emerald-100 text-emerald-700 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                        <i class="bi bi-check-circle-fill me-1"></i> Active
                                    </span>
                                    @elseif($card->is_expired)
                                    <span class="badge bg-amber-100 text-amber-700 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Expired
                                    </span>
                                    @else
                                    <span class="badge bg-rose-100 text-rose-700 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                        <i class="bi bi-slash-circle me-1"></i> Revoked
                                    </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-end">
                                    @if($card->is_active)
                                    <form method="POST" action="{{ route('rfid.cards.revoke', $card->id) }}" class="d-inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-warning text-xs py-1 px-2.5 rounded-lg">
                                            Revoke
                                        </button>
                                    </form>
                                    @endif
                                    <form method="POST" action="{{ route('rfid.cards.destroy', $card->id) }}" class="d-inline" onsubmit="return confirm('Remove card assignment?')">
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
                    {{ $cards->links() }}
                </div>
                @else
                <div class="text-center py-5 text-slate-400">
                    <i class="bi bi-credit-card-2-front display-3 text-slate-200 mb-3 block"></i>
                    <h6 class="font-bold text-slate-600 mb-1">No Assigned Keycards</h6>
                    <p class="mb-0 text-xs">Assign physical RFID cards to students or staff using the form on the left.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
