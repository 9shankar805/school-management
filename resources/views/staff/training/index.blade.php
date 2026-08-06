@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2"><div class="col ps-4">
                <h1 class="display-6 mb-1"><i class="bi bi-mortarboard"></i> Staff Training Records</h1>
                <nav aria-label="breadcrumb" class="mb-3"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li><li class="breadcrumb-item"><a href="{{ route('staff.index') }}">HR & Staff</a></li><li class="breadcrumb-item active">Training</li></ol></nav>
                @if(session('status'))<div class="alert alert-success alert-dismissible fade show">{{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-header fw-semibold">Add Training Record</div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('staff.training.store') }}" enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-3"><label class="form-label fw-semibold">Staff Member</label>
                                        <select name="user_id" class="form-select" required>
                                            <option value="">— Select —</option>
                                            @foreach($staff as $u)<option value="{{ $u->id }}">{{ $u->first_name }} {{ $u->last_name }}</option>@endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3"><label class="form-label fw-semibold">Title <span class="text-danger">*</span></label><input type="text" name="title" class="form-control" required></div>
                                    <div class="mb-3"><label class="form-label fw-semibold">Type</label>
                                        <input type="text" name="type" class="form-control" list="trainingTypes" placeholder="Workshop, Seminar, Online…">
                                        <datalist id="trainingTypes"><option value="Workshop"><option value="Seminar"><option value="Online Course"><option value="Certification"><option value="Conference"></datalist>
                                    </div>
                                    <div class="mb-3"><label class="form-label fw-semibold">Provider</label><input type="text" name="provider" class="form-control"></div>
                                    <div class="row g-2 mb-3">
                                        <div class="col-6"><label class="form-label small">Start Date</label><input type="date" name="start_date" class="form-control form-control-sm" required></div>
                                        <div class="col-6"><label class="form-label small">End Date</label><input type="date" name="end_date" class="form-control form-control-sm"></div>
                                    </div>
                                    <div class="mb-3"><label class="form-label fw-semibold">Duration (hours)</label><input type="number" name="duration_hours" class="form-control" min="1"></div>
                                    <div class="mb-3">
                                        <div class="form-check"><input type="checkbox" name="is_completed" value="1" class="form-check-input"><label class="form-check-label">Completed</label></div>
                                    </div>
                                    <div class="mb-3"><label class="form-label fw-semibold">Certificate</label><input type="file" name="certificate" class="form-control" accept=".pdf,.jpg,.jpeg,.png"></div>
                                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-plus-circle"></i> Add</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <form method="GET" action="{{ route('staff.training.index') }}" class="row g-2 mb-3">
                            <div class="col-md-5"><input type="text" name="search" class="form-control form-control-sm" placeholder="Search name or title…" value="{{ request('search') }}"></div>
                            <div class="col-md-3"><input type="text" name="type" class="form-control form-control-sm" placeholder="Training type…" value="{{ request('type') }}"></div>
                            <div class="col-md-2"><button class="btn btn-secondary btn-sm w-100" type="submit"><i class="bi bi-search"></i></button></div>
                        </form>
                        <div class="card shadow-sm">
                            <div class="card-body p-0">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light"><tr><th>Staff</th><th>Title</th><th>Type</th><th>Provider</th><th>Start</th><th>Hours</th><th>Done</th><th>Actions</th></tr></thead>
                                    <tbody>
                                        @forelse($trainings as $t)
                                        <tr>
                                            <td class="small fw-semibold">{{ $t->user->first_name }} {{ $t->user->last_name }}</td>
                                            <td class="small">{{ $t->title }}</td>
                                            <td class="small"><span class="badge bg-info text-dark">{{ $t->type }}</span></td>
                                            <td class="small text-muted">{{ $t->provider ?? '—' }}</td>
                                            <td class="small">{{ $t->start_date->format('d M Y') }}</td>
                                            <td class="small text-center">{{ $t->duration_hours ?? '—' }}</td>
                                            <td><span class="badge {{ $t->is_completed ? 'bg-success' : 'bg-warning text-dark' }}">{{ $t->is_completed ? 'Yes' : 'No' }}</span></td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    @if($t->certificate_path)<a href="{{ $t->certificate_url }}" target="_blank" class="btn btn-outline-info btn-sm"><i class="bi bi-award"></i></a>@endif
                                                    <form method="POST" action="{{ route('staff.training.destroy', $t->id) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button></form>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="8" class="text-center text-muted py-4">No training records.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="mt-3">{{ $trainings->links() }}</div>
                    </div>
                </div>
            </div></div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
