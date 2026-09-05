@extends('layouts.app')
@section('title', 'Student Transfers')

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div style="width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,#6366f1,#3b82f6);display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(99,102,241,.3);">
                <i class="bi bi-arrow-left-right text-white fs-5"></i>
            </div>
            <div>
                <h2 class="h4 fw-bold text-slate-800 mb-0">Student Transfers</h2>
                <p class="text-muted small mb-0">Inter-class, inter-section, and inter-school transfers — all 3 scenarios</p>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('student.transfer.incoming.create') }}" class="btn fw-semibold text-white"
               style="background:linear-gradient(135deg,#059669,#0d9488);border:none;border-radius:10px;">
                <i class="bi bi-person-plus-fill me-1"></i> Admit Incoming (Scenario 3)
            </a>
            <a href="{{ route('student.list.show') }}" class="btn btn-light border fw-semibold">
                <i class="bi bi-people me-1"></i> All Students
            </a>
        </div>
    </div>

    @include('session-messages')

    {{-- Stats row --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="bg-white rounded-3 border p-3 text-center">
                <div class="h3 fw-bold mb-0 text-slate-800">{{ $stats['total'] }}</div>
                <div class="small text-muted">Total Records</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="bg-white rounded-3 border p-3 text-center">
                <div class="h3 fw-bold mb-0" style="color:#d97706;">{{ $stats['pending'] }}</div>
                <div class="small text-muted">Pending</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="bg-white rounded-3 border p-3 text-center">
                <div class="h3 fw-bold mb-0" style="color:#059669;">{{ $stats['approved'] }}</div>
                <div class="small text-muted">Approved</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="bg-white rounded-3 border p-3 text-center">
                <div class="h3 fw-bold mb-0" style="color:#0284c7;">{{ $stats['incoming'] }}</div>
                <div class="small text-muted">Incoming</div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-white border rounded-3 p-3 mb-3 d-flex flex-wrap gap-3 align-items-end">
        <div>
            <label class="form-label small fw-semibold mb-1">Type</label>
            <select name="type" class="form-select form-select-sm rounded-3" style="min-width:170px;">
                <option value="">All Types</option>
                <option value="inter_class"    {{ request('type')=='inter_class'    ? 'selected' : '' }}>Class Transfer</option>
                <option value="inter_section"  {{ request('type')=='inter_section'  ? 'selected' : '' }}>Section Transfer</option>
                <option value="inter_school"   {{ request('type')=='inter_school'   ? 'selected' : '' }}>School Transfer (Outgoing)</option>
                <option value="incoming"       {{ request('type')=='incoming'       ? 'selected' : '' }}>Incoming Transfer</option>
            </select>
        </div>
        <div>
            <label class="form-label small fw-semibold mb-1">Status</label>
            <select name="status" class="form-select form-select-sm rounded-3" style="min-width:140px;">
                <option value="">All Status</option>
                <option value="pending"  {{ request('status')=='pending'  ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status')=='approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status')=='rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
        </div>
        <div>
            <button class="btn btn-indigo btn-sm fw-semibold px-4 rounded-3" style="background:#6366f1;color:#fff;border:none;">Filter</button>
            <a href="{{ route('student.transfers.index') }}" class="btn btn-light btn-sm border fw-semibold ms-1 rounded-3">Clear</a>
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-3 border shadow-sm">
        @if($transfers->count() > 0)
        <div class="table-responsive">
            <table class="table align-middle small mb-0">
                <thead>
                    <tr class="text-xs text-uppercase fw-semibold text-muted bg-light">
                        <th class="py-3 px-4">Student</th>
                        <th class="py-3 px-3">Scenario / Type</th>
                        <th class="py-3 px-3">From</th>
                        <th class="py-3 px-3">To</th>
                        <th class="py-3 px-3 text-center">Date</th>
                        <th class="py-3 px-3 text-center">Status</th>
                        <th class="py-3 px-3 text-center">TC</th>
                        <th class="py-3 px-4 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($transfers as $tr)
                    <tr>
                        {{-- Student --}}
                        <td class="py-3 px-4">
                            <div class="fw-bold text-dark">{{ $tr->student?->first_name }} {{ $tr->student?->last_name }}</div>
                            <div class="text-muted" style="font-size:11px;">ID #{{ $tr->student_id }}</div>
                        </td>

                        {{-- Scenario badge --}}
                        <td class="py-3 px-3">
                            @if($tr->transfer_type === 'incoming')
                                <span class="badge rounded-pill" style="background:#d1fae5;color:#065f46;">Incoming</span>
                            @elseif($tr->transfer_type === 'inter_school' && $tr->to_school_id)
                                <span class="badge rounded-pill" style="background:#dbeafe;color:#1e40af;">S1: Same Platform</span>
                            @elseif($tr->transfer_type === 'inter_school')
                                <span class="badge rounded-pill" style="background:#ede9fe;color:#5b21b6;">S2: External TC</span>
                            @elseif($tr->transfer_type === 'inter_class')
                                <span class="badge rounded-pill" style="background:#f1f5f9;color:#475569;">Class Transfer</span>
                            @else
                                <span class="badge rounded-pill" style="background:#f1f5f9;color:#475569;">Section Transfer</span>
                            @endif
                        </td>

                        {{-- From --}}
                        <td class="py-3 px-3">
                            <div class="fw-semibold text-dark" style="font-size:12px;">
                                {{ $tr->transfer_type === 'incoming'
                                    ? ($tr->previous_school_name ?? $tr->from_school ?? '—')
                                    : ($tr->fromSchool?->name ?? $tr->from_school ?? '—') }}
                            </div>
                            <div class="text-muted" style="font-size:11px;">
                                {{ $tr->fromClass?->class_name ?? '' }}
                                {{ $tr->fromSection?->section_name ? '('.$tr->fromSection->section_name.')' : '' }}
                            </div>
                        </td>

                        {{-- To --}}
                        <td class="py-3 px-3">
                            <div class="fw-semibold text-dark" style="font-size:12px;">
                                {{ $tr->toSchool?->name ?? $tr->to_school ?? ($tr->toClass?->class_name ? 'Same School' : '—') }}
                            </div>
                            <div class="text-muted" style="font-size:11px;">
                                {{ $tr->toClass?->class_name ?? '' }}
                                {{ $tr->toSection?->section_name ? '('.$tr->toSection->section_name.')' : '' }}
                            </div>
                        </td>

                        {{-- Date --}}
                        <td class="py-3 px-3 text-center text-muted" style="font-size:12px;">
                            {{ $tr->transfer_date?->format('d M Y') }}
                        </td>

                        {{-- Status --}}
                        <td class="py-3 px-3 text-center">
                            @if($tr->status === 'approved')
                                <span class="badge" style="background:#d1fae5;color:#065f46;"><i class="bi bi-check-circle-fill me-1"></i>Approved</span>
                            @elseif($tr->status === 'pending')
                                <span class="badge" style="background:#fef3c7;color:#92400e;"><i class="bi bi-clock-history me-1"></i>Pending</span>
                            @else
                                <span class="badge" style="background:#fee2e2;color:#991b1b;"><i class="bi bi-x-circle-fill me-1"></i>Rejected</span>
                            @endif
                        </td>

                        {{-- TC --}}
                        <td class="py-3 px-3 text-center">
                            @if($tr->isTcIssued())
                                <a href="{{ route('student.transfer.tc', $tr->id) }}" class="btn btn-sm btn-outline-danger rounded-2" title="Download TC: {{ $tr->tc_number }}">
                                    <i class="bi bi-file-pdf"></i>
                                </a>
                            @elseif($tr->transfer_type === 'inter_school' && $tr->status === 'approved')
                                <a href="{{ route('student.transfer.tc', $tr->id) }}" class="btn btn-sm btn-outline-secondary rounded-2" title="Issue & Download TC">
                                    <i class="bi bi-file-earmark-plus"></i>
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="py-3 px-4 text-end">
                            <a href="{{ route('student.transfer.show', $tr->id) }}" class="btn btn-sm btn-light border rounded-2 me-1" title="View">
                                <i class="bi bi-eye"></i>
                            </a>

                            @if($tr->status === 'pending')
                            {{-- Quick approve / reject --}}
                            <button type="button" class="btn btn-sm btn-success rounded-2 me-1"
                                    onclick="quickApprove({{ $tr->id }})" title="Approve">
                                <i class="bi bi-check2"></i>
                            </button>
                            <form method="POST" action="{{ route('student.transfer.approve', $tr->id) }}" class="d-inline" id="reject-form-{{ $tr->id }}">
                                @csrf
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-2" title="Reject"
                                        onclick="return confirm('Reject this transfer request?')">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </form>
                            @endif

                            {{-- Accept button for Scenario 1 incoming at destination school --}}
                            @if($tr->status === 'approved' && $tr->to_school_id === auth()->user()->school_id && !$tr->isAccepted())
                            <a href="{{ route('student.transfer.show', $tr->id) }}#accept" class="btn btn-sm btn-primary rounded-2" title="Accept & Enroll">
                                <i class="bi bi-person-check me-1"></i>Accept
                            </a>
                            @endif

                            <form method="POST" action="{{ route('student.transfer.destroy', $tr->id) }}" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-secondary rounded-2 ms-1" title="Delete"
                                        onclick="return confirm('Delete this transfer record?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-top">
            {{ $transfers->links() }}
        </div>
        @else
        <div class="text-center py-5 text-muted">
            <i class="bi bi-arrow-left-right display-3 mb-3 d-block" style="color:#cbd5e1;"></i>
            <h6 class="fw-bold text-slate-600 mb-1">No Transfer Records</h6>
            <p class="small mb-3">Student transfer requests will appear here. Start by going to a student's profile and clicking "Transfer".</p>
            <a href="{{ route('student.transfer.incoming.create') }}" class="btn fw-semibold text-white"
               style="background:linear-gradient(135deg,#059669,#0d9488);border:none;border-radius:10px;">
                <i class="bi bi-person-plus-fill me-1"></i> Admit Incoming Student
            </a>
        </div>
        @endif
    </div>

</div>

{{-- Quick approve modal --}}
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-3">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Approve Transfer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="approveModalForm">
                @csrf
                <input type="hidden" name="status" value="approved">
                <div class="modal-body">
                    <div class="mb-3" id="classAssignBlock" style="display:none;">
                        <div class="alert alert-info small border-0">
                            <i class="bi bi-info-circle me-1"></i>
                            This is a same-platform transfer. Optionally pre-assign the class at the destination school:
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Notes (optional)</label>
                        <textarea name="notes" rows="3" class="form-control rounded-3" placeholder="Any approval notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn fw-bold text-white px-4" style="background:#059669;border:none;border-radius:8px;">
                        <i class="bi bi-check-circle-fill me-1"></i> Confirm Approval
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function quickApprove(id) {
    const form = document.getElementById('approveModalForm');
    form.action = '/transfers/' + id + '/approve';
    new bootstrap.Modal(document.getElementById('approveModal')).show();
}
</script>
@endsection
