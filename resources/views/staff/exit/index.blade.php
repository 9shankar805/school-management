@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2"><div class="col ps-4">
                <h1 class="display-6 mb-1"><i class="bi bi-box-arrow-right"></i> Staff Exit / Offboarding</h1>
                <nav aria-label="breadcrumb" class="mb-3"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li><li class="breadcrumb-item"><a href="{{ route('staff.index') }}">HR & Staff</a></li><li class="breadcrumb-item active">Exit Records</li></ol></nav>
                @if(session('status'))<div class="alert alert-success alert-dismissible fade show">{{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-header fw-semibold">Record Exit</div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('staff.exit.store') }}">
                                    @csrf
                                    <div class="mb-3"><label class="form-label fw-semibold">Staff Member <span class="text-danger">*</span></label>
                                        <select name="user_id" class="form-select" required>
                                            <option value="">— Select —</option>
                                            @foreach($staff as $u)<option value="{{ $u->id }}">{{ $u->first_name }} {{ $u->last_name }}</option>@endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3"><label class="form-label fw-semibold">Exit Type <span class="text-danger">*</span></label>
                                        <select name="exit_type" class="form-select" required>
                                            <option value="resignation">Resignation</option>
                                            <option value="termination">Termination</option>
                                            <option value="retirement">Retirement</option>
                                            <option value="contract_end">Contract End</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    <div class="mb-3"><label class="form-label fw-semibold">Notice Date <span class="text-danger">*</span></label><input type="date" name="notice_date" class="form-control" required></div>
                                    <div class="mb-3"><label class="form-label fw-semibold">Last Working Date <span class="text-danger">*</span></label><input type="date" name="last_working_date" class="form-control" required></div>
                                    <div class="mb-3"><label class="form-label fw-semibold">Reason</label><input type="text" name="reason" class="form-control"></div>
                                    <div class="mb-3"><label class="form-label fw-semibold">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                                    <div class="mb-3">
                                        <div class="form-check"><input type="checkbox" name="clearance_done" value="1" class="form-check-input"><label class="form-check-label">Clearance Completed</label></div>
                                        <div class="form-check mt-1"><input type="checkbox" name="dues_settled" value="1" class="form-check-input"><label class="form-check-label">Dues Settled</label></div>
                                    </div>
                                    <button type="submit" class="btn btn-danger btn-sm w-100"><i class="bi bi-check-circle"></i> Record Exit</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <form method="GET" action="{{ route('staff.exit.index') }}" class="row g-2 mb-3">
                            <div class="col-md-5"><input type="text" name="search" class="form-control form-control-sm" placeholder="Search staff name…" value="{{ request('search') }}"></div>
                            <div class="col-md-3"><select name="exit_type" class="form-select form-select-sm"><option value="">All Types</option>@foreach(['resignation'=>'Resignation','termination'=>'Termination','retirement'=>'Retirement','contract_end'=>'Contract End','other'=>'Other'] as $v=>$l)<option value="{{ $v }}" @selected(request('exit_type')===$v)>{{ $l }}</option>@endforeach</select></div>
                            <div class="col-md-2"><button class="btn btn-secondary btn-sm w-100" type="submit"><i class="bi bi-search"></i></button></div>
                        </form>
                        <div class="card shadow-sm">
                            <div class="card-body p-0">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light"><tr><th>Staff</th><th>Exit Type</th><th>Notice</th><th>Last Day</th><th>Clearance</th><th>Dues</th><th>Actions</th></tr></thead>
                                    <tbody>
                                        @forelse($exits as $ex)
                                        <tr>
                                            <td class="fw-semibold small">{{ $ex->user->first_name }} {{ $ex->user->last_name }}</td>
                                            <td class="small"><span class="badge bg-secondary">{{ $ex->exit_type_label }}</span></td>
                                            <td class="small">{{ $ex->notice_date->format('d M Y') }}</td>
                                            <td class="small">{{ $ex->last_working_date->format('d M Y') }}</td>
                                            <td><span class="badge {{ $ex->clearance_done ? 'bg-success' : 'bg-warning text-dark' }}">{{ $ex->clearance_done ? 'Done' : 'Pending' }}</span></td>
                                            <td><span class="badge {{ $ex->dues_settled ? 'bg-success' : 'bg-danger' }}">{{ $ex->dues_settled ? 'Settled' : 'Outstanding' }}</span></td>
                                            <td>
                                                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editExit{{ $ex->id }}"><i class="bi bi-pencil"></i></button>
                                                <form method="POST" action="{{ route('staff.exit.destroy', $ex->id) }}" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button></form>
                                            </td>
                                        </tr>
                                        <div class="modal fade" id="editExit{{ $ex->id }}" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
                                            <form method="POST" action="{{ route('staff.exit.update', $ex->id) }}">@csrf @method('PUT')
                                                <div class="modal-header"><h5 class="modal-title">Update Exit: {{ $ex->user->first_name }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                                <div class="modal-body row g-2">
                                                    <div class="col-12"><div class="form-check"><input type="checkbox" name="clearance_done" value="1" class="form-check-input" @checked($ex->clearance_done)><label class="form-check-label">Clearance Completed</label></div></div>
                                                    <div class="col-12"><div class="form-check"><input type="checkbox" name="dues_settled" value="1" class="form-check-input" @checked($ex->dues_settled)><label class="form-check-label">Dues Settled</label></div></div>
                                                    <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2">{{ $ex->notes }}</textarea></div>
                                                </div>
                                                <div class="modal-footer"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary btn-sm">Save</button></div>
                                            </form>
                                        </div></div></div>
                                        @empty
                                        <tr><td colspan="7" class="text-center text-muted py-4">No exit records.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="mt-3">{{ $exits->links() }}</div>
                    </div>
                </div>
            </div></div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
