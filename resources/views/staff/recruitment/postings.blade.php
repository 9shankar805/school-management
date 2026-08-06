@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2"><div class="col ps-4">
                <h1 class="display-6 mb-1"><i class="bi bi-megaphone"></i> Job Postings</h1>
                <nav aria-label="breadcrumb" class="mb-3"><ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('staff.index') }}">HR & Staff</a></li>
                    <li class="breadcrumb-item active">Recruitment</li>
                </ol></nav>
                @if(session('status'))<div class="alert alert-success alert-dismissible fade show">{{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

                <div class="d-flex gap-2 mb-3">
                    <button class="btn btn-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#newPostForm"><i class="bi bi-plus-circle"></i> New Posting</button>
                </div>

                <div class="collapse mb-4" id="newPostForm">
                    <div class="card shadow-sm">
                        <div class="card-header fw-semibold">Create Job Posting</div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('staff.recruitment.postings.store') }}">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label fw-semibold">Job Title <span class="text-danger">*</span></label><input type="text" name="title" class="form-control" required></div>
                                    <div class="col-md-3"><label class="form-label fw-semibold">Department</label><input type="text" name="department" class="form-control"></div>
                                    <div class="col-md-1"><label class="form-label fw-semibold">Vacancies</label><input type="number" name="vacancies" class="form-control" value="1" min="1"></div>
                                    <div class="col-md-2"><label class="form-label fw-semibold">Status</label><select name="status" class="form-select"><option value="draft">Draft</option><option value="published">Published</option></select></div>
                                    <div class="col-md-3"><label class="form-label fw-semibold">Salary Min ($)</label><input type="number" name="salary_min" class="form-control" step="0.01"></div>
                                    <div class="col-md-3"><label class="form-label fw-semibold">Salary Max ($)</label><input type="number" name="salary_max" class="form-control" step="0.01"></div>
                                    <div class="col-md-3"><label class="form-label fw-semibold">Application Deadline</label><input type="date" name="application_deadline" class="form-control"></div>
                                    <div class="col-12"><label class="form-label fw-semibold">Description <span class="text-danger">*</span></label><textarea name="description" class="form-control" rows="3" required></textarea></div>
                                    <div class="col-12"><label class="form-label fw-semibold">Requirements</label><textarea name="requirements" class="form-control" rows="3"></textarea></div>
                                    <div class="col-12"><button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check-circle"></i> Create</button></div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <form method="GET" action="{{ route('staff.recruitment.postings') }}" class="row g-2 mb-3">
                    <div class="col-md-3"><select name="status" class="form-select form-select-sm" onchange="this.form.submit()"><option value="">All Status</option><option value="draft" @selected(request('status')==='draft')>Draft</option><option value="published" @selected(request('status')==='published')>Published</option><option value="closed" @selected(request('status')==='closed')>Closed</option></select></div>
                </form>

                <div class="row g-3">
                    @forelse($postings as $p)
                    <div class="col-md-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="fw-bold mb-0">{{ $p->title }}</h5>
                                    {!! $p->status_badge !!}
                                </div>
                                @if($p->department)<span class="badge bg-secondary mb-2">{{ $p->department }}</span>@endif
                                <p class="small text-muted mb-1"><i class="bi bi-people me-1"></i> {{ $p->vacancies }} vacancies | <i class="bi bi-file-earmark-text me-1"></i> {{ $p->applications_count }} applications</p>
                                @if($p->salary_min || $p->salary_max)<p class="small text-muted mb-1"><i class="bi bi-cash me-1"></i> Rs. {{ number_format($p->salary_min ?? 0,0) }} – Rs. {{ number_format($p->salary_max ?? 0,0) }}</p>@endif
                                @if($p->application_deadline)<p class="small {{ $p->application_deadline->isPast() ? 'text-danger' : 'text-muted' }} mb-0"><i class="bi bi-calendar me-1"></i> Deadline: {{ $p->application_deadline->format('d M Y') }}</p>@endif
                            </div>
                            <div class="card-footer d-flex gap-2">
                                <a href="{{ route('staff.recruitment.applications', $p->id) }}" class="btn btn-outline-info btn-sm"><i class="bi bi-people"></i> Applications</a>
                                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editPosting{{ $p->id }}"><i class="bi bi-pencil"></i></button>
                                <form method="POST" action="{{ route('staff.recruitment.postings.destroy', $p->id) }}" onsubmit="return confirm('Delete posting?')">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button></form>
                            </div>
                        </div>
                    </div>

                    {{-- Edit modal --}}
                    <div class="modal fade" id="editPosting{{ $p->id }}" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
                        <form method="POST" action="{{ route('staff.recruitment.postings.update', $p->id) }}">@csrf @method('PUT')
                            <div class="modal-header"><h5 class="modal-title">Edit: {{ $p->title }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                            <div class="modal-body row g-2">
                                <div class="col-7"><label class="form-label">Title</label><input type="text" name="title" class="form-control" value="{{ $p->title }}" required></div>
                                <div class="col-3"><label class="form-label">Status</label><select name="status" class="form-select"><option value="draft" @selected($p->status==='draft')>Draft</option><option value="published" @selected($p->status==='published')>Published</option><option value="closed" @selected($p->status==='closed')>Closed</option></select></div>
                                <div class="col-2"><label class="form-label">Vacancies</label><input type="number" name="vacancies" class="form-control" value="{{ $p->vacancies }}" min="1"></div>
                                <div class="col-4"><label class="form-label">Department</label><input type="text" name="department" class="form-control" value="{{ $p->department }}"></div>
                                <div class="col-4"><label class="form-label">Salary Range</label><input type="number" name="salary_min" class="form-control" placeholder="Min" value="{{ $p->salary_min }}" step="0.01"></div>
                                <div class="col-4"><label class="form-label">&nbsp;</label><input type="number" name="salary_max" class="form-control" placeholder="Max" value="{{ $p->salary_max }}" step="0.01"></div>
                                <div class="col-4"><label class="form-label">Deadline</label><input type="date" name="application_deadline" class="form-control" value="{{ $p->application_deadline?->format('Y-m-d') }}"></div>
                                <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3" required>{{ $p->description }}</textarea></div>
                                <div class="col-12"><label class="form-label">Requirements</label><textarea name="requirements" class="form-control" rows="3">{{ $p->requirements }}</textarea></div>
                            </div>
                            <div class="modal-footer"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary btn-sm">Save</button></div>
                        </form>
                    </div></div></div>
                    @empty
                    <div class="col-12"><div class="alert alert-info text-center py-4">No job postings yet. Create one above.</div></div>
                    @endforelse
                </div>
                <div class="mt-3">{{ $postings->links() }}</div>
            </div></div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
