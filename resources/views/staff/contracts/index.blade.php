@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2"><div class="col ps-4">
                <h1 class="display-6 mb-1"><i class="bi bi-file-earmark-text"></i> Staff Contracts</h1>
                <nav aria-label="breadcrumb" class="mb-3"><ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('staff.index') }}">HR & Staff</a></li>
                    <li class="breadcrumb-item active">Contracts</li>
                </ol></nav>
                @if(session('status'))<div class="alert alert-success alert-dismissible fade show">{{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

                @if(isset($expiryAlerts) && count($expiryAlerts) > 0)
                <div class="alert alert-warning d-flex gap-2 py-2 mb-3">
                    <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                    <div>
                        <strong>{{ count($expiryAlerts) }}</strong> contract(s) expiring within 30 days:
                        @foreach($expiryAlerts as $a)
                            <strong>{{ $a->user->first_name ?? '' }} {{ $a->user->last_name ?? '' }}</strong> ({{ $a->end_date ? $a->end_date->format('d M Y') : 'N/A' }})@if(!$loop->last), @endif
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="d-flex gap-2 mb-3">
                    <button class="btn btn-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#addContract"><i class="bi bi-plus-circle"></i> Add Contract</button>
                </div>

                <div class="collapse mb-3" id="addContract">
                    <div class="card shadow-sm">
                        <div class="card-header fw-semibold">New Contract</div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('staff.contracts.store') }}" enctype="multipart/form-data">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-4"><label class="form-label fw-semibold">Staff Member</label>
                                        <select name="user_id" class="form-select" required>
                                            <option value="">— Select —</option>
                                            @foreach(\App\Models\User::whereHas('roles', fn($q)=>$q->whereIn('name',['teacher','staff','class-teacher']))->orderBy('first_name')->get() as $u)
                                                <option value="{{ $u->id }}">{{ $u->first_name }} {{ $u->last_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3"><label class="form-label fw-semibold">Contract Type</label><input type="text" name="contract_type" class="form-control" placeholder="Permanent, Probation…" required></div>
                                    <div class="col-md-2"><label class="form-label fw-semibold">Position</label><input type="text" name="position" class="form-control"></div>
                                    <div class="col-md-3"><label class="form-label fw-semibold">Department</label><input type="text" name="department" class="form-control"></div>
                                    <div class="col-md-2"><label class="form-label fw-semibold">Start Date</label><input type="date" name="start_date" class="form-control" required></div>
                                    <div class="col-md-2"><label class="form-label fw-semibold">End Date</label><input type="date" name="end_date" class="form-control"></div>
                                    <div class="col-md-2"><label class="form-label fw-semibold">Salary ($)</label><input type="number" name="salary" class="form-control" step="0.01" min="0" required></div>
                                    <div class="col-md-2"><label class="form-label fw-semibold">Allowances ($)</label><input type="number" name="allowances" class="form-control" step="0.01" value="0"></div>
                                    <div class="col-md-4"><label class="form-label fw-semibold">Contract Document (PDF)</label><input type="file" name="document" class="form-control" accept=".pdf"></div>
                                    <div class="col-12"><button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check-circle"></i> Create</button></div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <form method="GET" action="{{ route('staff.contracts.index') }}" class="row g-2 mb-3">
                    <div class="col-md-4"><input type="text" name="search" class="form-control form-control-sm" placeholder="Search staff name…" value="{{ request('search') }}"></div>
                    <div class="col-md-2"><select name="status" class="form-select form-select-sm"><option value="">All Status</option><option value="active" @selected(request('status')==='active')>Active</option><option value="expired" @selected(request('status')==='expired')>Expired</option><option value="terminated" @selected(request('status')==='terminated')>Terminated</option></select></div>
                    <div class="col-md-2"><button class="btn btn-secondary btn-sm w-100" type="submit"><i class="bi bi-search"></i> Filter</button></div>
                    @if(request()->hasAny(['search','status']))<div class="col-md-1"><a href="{{ route('staff.contracts.index') }}" class="btn btn-outline-secondary btn-sm w-100">Clear</a></div>@endif
                </form>

                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light"><tr><th>Staff</th><th>Type</th><th>Position</th><th>Salary</th><th>Start</th><th>End</th><th>Status</th><th>Actions</th></tr></thead>
                            <tbody>
                                @forelse($contracts as $c)
                                <tr class="{{ $c->is_expiring_soon ? 'table-warning' : '' }}">
                                    <td class="fw-semibold small">{{ $c->user->first_name }} {{ $c->user->last_name }}</td>
                                    <td class="small"><span class="badge bg-secondary">{{ $c->contract_type }}</span></td>
                                    <td class="small text-muted">{{ $c->position ?? '—' }}</td>
                                    <td class="small">Rs. {{ number_format($c->gross_salary,2) }}</td>
                                    <td class="small">{{ $c->start_date->format('d M Y') }}</td>
                                    <td class="small {{ $c->is_expired ? 'text-danger' : '' }}">{{ $c->end_date?->format('d M Y') ?? 'Open-ended' }}</td>
                                    <td>{!! $c->status_badge !!}</td>
                                    <td>
                                        @if($c->document_path)<a href="{{ Storage::url($c->document_path) }}" target="_blank" class="btn btn-outline-info btn-sm"><i class="bi bi-file-earmark-pdf"></i></a>@endif
                                        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editContract{{ $c->id }}"><i class="bi bi-pencil"></i></button>
                                        <form method="POST" action="{{ route('staff.contracts.destroy', $c->id) }}" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button></form>
                                    </td>
                                </tr>
                                <div class="modal fade" id="editContract{{ $c->id }}" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
                                    <form method="POST" action="{{ route('staff.contracts.update', $c->id) }}">@csrf @method('PUT')
                                        <div class="modal-header"><h5 class="modal-title">Edit Contract</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                        <div class="modal-body row g-2">
                                            <div class="col-6"><label class="form-label">Type</label><input type="text" name="contract_type" class="form-control" value="{{ $c->contract_type }}" required></div>
                                            <div class="col-6"><label class="form-label">Status</label><select name="status" class="form-select"><option value="active" @selected($c->status==='active')>Active</option><option value="expired" @selected($c->status==='expired')>Expired</option><option value="terminated" @selected($c->status==='terminated')>Terminated</option></select></div>
                                            <div class="col-3"><label class="form-label">Start</label><input type="date" name="start_date" class="form-control" value="{{ $c->start_date->format('Y-m-d') }}" required></div>
                                            <div class="col-3"><label class="form-label">End</label><input type="date" name="end_date" class="form-control" value="{{ $c->end_date?->format('Y-m-d') }}"></div>
                                            <div class="col-3"><label class="form-label">Salary ($)</label><input type="number" name="salary" class="form-control" value="{{ $c->salary }}" step="0.01" required></div>
                                            <div class="col-3"><label class="form-label">Allowances ($)</label><input type="number" name="allowances" class="form-control" value="{{ $c->allowances }}" step="0.01"></div>
                                            <div class="col-6"><label class="form-label">Position</label><input type="text" name="position" class="form-control" value="{{ $c->position }}"></div>
                                            <div class="col-6"><label class="form-label">Department</label><input type="text" name="department" class="form-control" value="{{ $c->department }}"></div>
                                        </div>
                                        <div class="modal-footer"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary btn-sm">Save</button></div>
                                    </form>
                                </div></div></div>
                                @empty
                                <tr><td colspan="8" class="text-center text-muted py-4">No contracts found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="mt-3">{{ $contracts->links() }}</div>
            </div></div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
