@extends('layouts.app')
@section('title', 'Transfer Details')

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div style="width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,#6366f1,#3b82f6);display:flex;align-items:center;justify-content:center;">
                <i class="bi bi-clipboard2-data-fill text-white fs-5"></i>
            </div>
            <div>
                <h2 class="h4 fw-bold mb-0">Transfer Details</h2>
                <p class="text-muted small mb-0">
                    {{ $transfer->scenario_label }}
                    &nbsp;·&nbsp;
                    {{ $transfer->student?->first_name }} {{ $transfer->student?->last_name }}
                </p>
            </div>
        </div>
        <a href="{{ route('student.transfers.index') }}" class="btn btn-light border fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    @include('session-messages')

    <div class="row g-4">

        {{-- Left: Transfer info card --}}
        <div class="col-lg-8">
            <div class="bg-white rounded-3 border shadow-sm p-4 mb-4">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                    <h6 class="fw-bold mb-0">Transfer Information</h6>
                    <div class="d-flex gap-2">
                        @if($transfer->status === 'approved')
                            <span class="badge rounded-pill" style="background:#d1fae5;color:#065f46;"><i class="bi bi-check-circle-fill me-1"></i>Approved</span>
                        @elseif($transfer->status === 'pending')
                            <span class="badge rounded-pill" style="background:#fef3c7;color:#92400e;"><i class="bi bi-clock-history me-1"></i>Pending</span>
                        @else
                            <span class="badge rounded-pill" style="background:#fee2e2;color:#991b1b;"><i class="bi bi-x-circle-fill me-1"></i>Rejected</span>
                        @endif

                        @if($transfer->scenario === 1)
                            <span class="badge rounded-pill" style="background:#dbeafe;color:#1e40af;">Scenario 1 — Same Platform</span>
                        @elseif($transfer->scenario === 2)
                            <span class="badge rounded-pill" style="background:#ede9fe;color:#5b21b6;">Scenario 2 — External TC</span>
                        @elseif($transfer->scenario === 3)
                            <span class="badge rounded-pill" style="background:#d1fae5;color:#065f46;">Scenario 3 — Incoming</span>
                        @endif
                    </div>
                </div>

                <table class="table table-sm small">
                    <tbody>
                        <tr>
                            <td class="text-muted fw-semibold" style="width:35%;">Student</td>
                            <td class="fw-bold text-dark">
                                {{ $transfer->student?->first_name }} {{ $transfer->student?->last_name }}
                                @if($transfer->student_id)
                                <a href="{{ route('student.profile.show', $transfer->student_id) }}" class="text-indigo-600 ms-2 small">(View Profile)</a>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Transfer Type</td>
                            <td>{{ \App\Models\StudentTransfer::TYPE_LABELS[$transfer->transfer_type] ?? $transfer->transfer_type }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Transfer Date</td>
                            <td>{{ $transfer->transfer_date?->format('d F Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">From School</td>
                            <td>{{ $transfer->source_school_name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">From Class / Section</td>
                            <td>
                                {{ $transfer->fromClass?->class_name ?? '—' }}
                                {{ $transfer->fromSection?->section_name ? ' / ' . $transfer->fromSection->section_name : '' }}
                            </td>
                        </tr>
                        @if($transfer->transfer_type !== 'incoming')
                        <tr>
                            <td class="text-muted fw-semibold">To School</td>
                            <td>{{ $transfer->destination_school_name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">To Class / Section</td>
                            <td>
                                {{ $transfer->toClass?->class_name ?? '—' }}
                                {{ $transfer->toSection?->section_name ? ' / ' . $transfer->toSection->section_name : '' }}
                            </td>
                        </tr>
                        @endif
                        @if($transfer->transfer_type === 'incoming')
                        <tr>
                            <td class="text-muted fw-semibold">Previous School</td>
                            <td>{{ $transfer->previous_school_name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Previous TC Number</td>
                            <td>{{ $transfer->previous_tc_number ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Joining Date</td>
                            <td>{{ $transfer->joining_date?->format('d F Y') ?? '—' }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="text-muted fw-semibold">Reason</td>
                            <td>{{ $transfer->reason ?? '—' }}</td>
                        </tr>
                        @if($transfer->approved_at)
                        <tr>
                            <td class="text-muted fw-semibold">Approved By</td>
                            <td>{{ $transfer->approver?->first_name }} {{ $transfer->approver?->last_name }} on {{ $transfer->approved_at->format('d M Y H:i') }}</td>
                        </tr>
                        @endif
                        @if($transfer->notes)
                        <tr>
                            <td class="text-muted fw-semibold">Notes</td>
                            <td>{{ $transfer->notes }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>

                {{-- Documents carried --}}
                @if($transfer->carry_marksheet || $transfer->carry_character || $transfer->carry_birth_cert || $transfer->carry_medical)
                <div class="mt-3 p-3 rounded-3 border bg-light">
                    <div class="small fw-semibold text-muted mb-2">Documents Handed Over:</div>
                    <div class="d-flex flex-wrap gap-2">
                        @if($transfer->carry_marksheet)  <span class="badge border" style="background:#eef2ff;color:#3730a3;font-weight:600;">✓ Mark Sheet</span>         @endif
                        @if($transfer->carry_character)  <span class="badge border" style="background:#eef2ff;color:#3730a3;font-weight:600;">✓ Character Certificate</span> @endif
                        @if($transfer->carry_birth_cert) <span class="badge border" style="background:#eef2ff;color:#3730a3;font-weight:600;">✓ Birth Certificate</span>    @endif
                        @if($transfer->carry_medical)    <span class="badge border" style="background:#eef2ff;color:#3730a3;font-weight:600;">✓ Medical Records</span>      @endif
                    </div>
                </div>
                @endif
            </div>

            {{-- TC info --}}
            @if($transfer->isTcIssued())
            <div class="bg-white rounded-3 border shadow-sm p-4 mb-4">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <h6 class="fw-bold mb-0"><i class="bi bi-file-earmark-check text-danger me-2"></i>Transfer Certificate</h6>
                    <span class="badge text-bg-danger">{{ $transfer->tc_number }}</span>
                </div>
                <table class="table table-sm small">
                    <tr><td class="text-muted fw-semibold" style="width:35%;">TC Number</td><td>{{ $transfer->tc_number }}</td></tr>
                    <tr><td class="text-muted fw-semibold">Issued On</td><td>{{ $transfer->tc_issued_at?->format('d F Y H:i') }}</td></tr>
                    <tr><td class="text-muted fw-semibold">Verification Token</td><td><code class="small">{{ $transfer->tc_token }}</code></td></tr>
                    <tr>
                        <td class="text-muted fw-semibold">Verify URL</td>
                        <td>
                            <a href="{{ route('student.transfer.verify', $transfer->tc_token) }}" target="_blank" class="small text-indigo-600">
                                {{ route('student.transfer.verify', $transfer->tc_token) }}
                            </a>
                        </td>
                    </tr>
                    @if($transfer->accepted_at)
                    <tr>
                        <td class="text-muted fw-semibold">Accepted By</td>
                        <td>{{ $transfer->acceptedBy?->first_name }} {{ $transfer->acceptedBy?->last_name }} on {{ $transfer->accepted_at?->format('d M Y') }}</td>
                    </tr>
                    @endif
                </table>
                <div class="mt-3">
                    <a href="{{ route('student.transfer.tc', $transfer->id) }}" class="btn fw-bold text-white" style="background:#dc2626;border:none;border-radius:8px;">
                        <i class="bi bi-file-pdf me-1"></i> Download TC PDF
                    </a>
                </div>
            </div>
            @endif

        </div>{{-- col-lg-8 --}}

        {{-- Right: Actions panel --}}
        <div class="col-lg-4">

            {{-- Approve / Reject (pending) --}}
            @if($transfer->status === 'pending')
            <div class="bg-white rounded-3 border shadow-sm p-4 mb-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-shield-check me-2 text-indigo-500"></i>Approval Decision</h6>
                <form method="POST" action="{{ route('student.transfer.approve', $transfer->id) }}">
                    @csrf
                    <input type="hidden" name="status" value="approved">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Notes</label>
                        <textarea name="notes" rows="3" class="form-control rounded-3 small" placeholder="Approval notes..."></textarea>
                    </div>
                    <button class="btn w-100 fw-bold text-white mb-2" style="background:#059669;border:none;border-radius:8px;">
                        <i class="bi bi-check-circle-fill me-1"></i> Approve Transfer
                    </button>
                </form>
                <form method="POST" action="{{ route('student.transfer.approve', $transfer->id) }}">
                    @csrf
                    <input type="hidden" name="status" value="rejected">
                    <div class="mb-2">
                        <textarea name="notes" rows="2" class="form-control rounded-3 small" placeholder="Rejection reason..."></textarea>
                    </div>
                    <button class="btn btn-outline-danger w-100 fw-bold rounded-3" onclick="return confirm('Reject this transfer?')">
                        <i class="bi bi-x-circle me-1"></i> Reject
                    </button>
                </form>
            </div>
            @endif

            {{-- Scenario 1: Accept at destination school --}}
            @if($transfer->status === 'approved' && $transfer->to_school_id === auth()->user()->school_id && !$transfer->isAccepted())
            <div class="bg-white rounded-3 border border-primary shadow-sm p-4 mb-4" id="accept">
                <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-person-check-fill me-2"></i>Accept Incoming Student</h6>
                <p class="small text-muted mb-3">
                    <strong>{{ $transfer->student?->first_name }} {{ $transfer->student?->last_name }}</strong> has been transferred to your school.
                    Assign them to a class to complete enrollment.
                </p>
                @php
                    $sessionId = auth()->user()->school?->sessions()->latest()->first()?->id;
                    $classes   = \App\Models\SchoolClass::where('session_id', $sessionId)->get();
                    $sections  = \App\Models\Section::where('session_id', $sessionId)->get();
                @endphp
                <form method="POST" action="{{ route('student.transfer.accept', $transfer->id) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Assign to Class <span class="text-danger">*</span></label>
                        <select name="to_class_id" class="form-select rounded-3 small" required>
                            <option value="">-- Select Class --</option>
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}">{{ $c->class_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Section</label>
                        <select name="to_section_id" class="form-select rounded-3 small">
                            <option value="">-- Select Section --</option>
                            @foreach($sections as $s)
                                <option value="{{ $s->id }}">{{ $s->section_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn w-100 fw-bold text-white" style="background:#2563eb;border:none;border-radius:8px;">
                        <i class="bi bi-person-check me-1"></i> Accept & Enroll
                    </button>
                </form>
            </div>
            @endif

            {{-- TC download (approved + inter_school) --}}
            @if(in_array($transfer->transfer_type, ['inter_school']) && $transfer->status === 'approved')
            <div class="bg-white rounded-3 border shadow-sm p-4 mb-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-file-pdf-fill text-danger me-2"></i>Transfer Certificate</h6>
                @if($transfer->isTcIssued())
                    <p class="small text-muted mb-3">TC <strong>{{ $transfer->tc_number }}</strong> issued on {{ $transfer->tc_issued_at?->format('d M Y') }}.</p>
                @else
                    <p class="small text-muted mb-3">TC not yet issued. Click below to generate and download it.</p>
                @endif
                <a href="{{ route('student.transfer.tc', $transfer->id) }}" class="btn w-100 fw-bold text-white" style="background:#dc2626;border:none;border-radius:8px;">
                    <i class="bi bi-download me-1"></i> {{ $transfer->isTcIssued() ? 'Download TC PDF' : 'Issue & Download TC' }}
                </a>
            </div>
            @endif

            {{-- Delete --}}
            <div class="bg-white rounded-3 border shadow-sm p-4">
                <h6 class="fw-bold mb-3 text-danger"><i class="bi bi-trash me-2"></i>Delete Record</h6>
                <form method="POST" action="{{ route('student.transfer.destroy', $transfer->id) }}" onsubmit="return confirm('Permanently delete this transfer record?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger w-100 fw-semibold rounded-3">
                        <i class="bi bi-trash me-1"></i> Delete Transfer
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
