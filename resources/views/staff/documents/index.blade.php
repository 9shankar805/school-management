@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2"><div class="col ps-4">
                <h1 class="display-6 mb-1"><i class="bi bi-folder2-open"></i> Staff Document Vault</h1>
                <nav aria-label="breadcrumb" class="mb-3"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li><li class="breadcrumb-item"><a href="{{ route('staff.index') }}">HR & Staff</a></li><li class="breadcrumb-item active">Documents</li></ol></nav>
                @if(session('status'))<div class="alert alert-success alert-dismissible fade show">{{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

                @if($expiryAlerts->isNotEmpty())
                <div class="alert alert-warning py-2 mb-3"><i class="bi bi-exclamation-triangle me-1"></i> <strong>{{ $expiryAlerts->count() }}</strong> document(s) expiring within 30 days.</div>
                @endif

                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-header fw-semibold">Upload Document</div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('staff.documents.store') }}" enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-3"><label class="form-label fw-semibold">Staff Member</label>
                                        <select name="user_id" class="form-select" required>
                                            <option value="">— Select —</option>
                                            @foreach(\App\Models\User::where(function($q) { $q->whereDoesntHave('roles', fn($r) => $r->whereIn('name', ['student', 'parent']))->where(function($sub) { $sub->whereNull('role')->orWhereNotIn('role', ['student', 'parent']); }); })->orderBy('first_name')->get() as $u)
                                                <option value="{{ $u->id }}">{{ $u->first_name }} {{ $u->last_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3"><label class="form-label fw-semibold">Title</label><input type="text" name="title" class="form-control" required></div>
                                    <div class="mb-3"><label class="form-label fw-semibold">Document Type</label>
                                        <select name="document_type" class="form-select" required>
                                            @foreach(\App\Models\StaffDocument::TYPES as $val=>$lbl)
                                                <option value="{{ $val }}">{{ $lbl }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3"><label class="form-label fw-semibold">File <span class="text-danger">*</span></label><input type="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required></div>
                                    <div class="mb-3"><label class="form-label fw-semibold">Expiry Date</label><input type="date" name="expiry_date" class="form-control"></div>
                                    <div class="mb-3"><label class="form-label fw-semibold">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-upload"></i> Upload</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <form method="GET" action="{{ route('staff.documents.index') }}" class="row g-2 mb-3">
                            <div class="col-md-5"><input type="text" name="search" class="form-control form-control-sm" placeholder="Search staff name…" value="{{ request('search') }}"></div>
                            <div class="col-md-4"><select name="document_type" class="form-select form-select-sm"><option value="">All Types</option>@foreach(\App\Models\StaffDocument::TYPES as $v=>$l)<option value="{{ $v }}" @selected(request('document_type')===$v)>{{ $l }}</option>@endforeach</select></div>
                            <div class="col-md-2"><button class="btn btn-secondary btn-sm w-100" type="submit"><i class="bi bi-search"></i></button></div>
                            @if(request()->hasAny(['search','document_type']))<div class="col-md-1"><a href="{{ route('staff.documents.index') }}" class="btn btn-outline-secondary btn-sm w-100">X</a></div>@endif
                        </form>

                        <div class="card shadow-sm">
                            <div class="card-body p-0">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light"><tr><th>Staff</th><th>Title</th><th>Type</th><th>Expiry</th><th>Verified</th><th>Actions</th></tr></thead>
                                    <tbody>
                                        @forelse($documents as $doc)
                                        <tr class="{{ $doc->is_expired ? 'table-danger' : '' }}">
                                            <td class="small fw-semibold">{{ $doc->user->first_name }} {{ $doc->user->last_name }}</td>
                                            <td class="small">{{ $doc->title }}</td>
                                            <td class="small"><span class="badge bg-secondary">{{ $doc->type_label }}</span></td>
                                            <td class="small {{ $doc->is_expired ? 'text-danger fw-bold' : '' }}">{{ $doc->expiry_date?->format('d M Y') ?? '—' }}</td>
                                            <td>
                                                @if($doc->is_verified)
                                                    <span class="badge bg-success"><i class="bi bi-check"></i> Verified</span>
                                                @else
                                                    <form method="POST" action="{{ route('staff.documents.verify', $doc->id) }}">@csrf<button class="btn btn-outline-success btn-sm py-0 px-2 small">Verify</button></form>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="{{ $doc->file_url }}" target="_blank" class="btn btn-outline-info btn-sm"><i class="bi bi-eye"></i></a>
                                                    <form method="POST" action="{{ route('staff.documents.destroy', $doc->id) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button></form>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="6" class="text-center text-muted py-4">No documents found.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="mt-3">{{ $documents->links() }}</div>
                    </div>
                </div>
            </div></div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
