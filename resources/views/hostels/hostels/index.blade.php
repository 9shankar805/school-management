@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-1"><i class="bi bi-building"></i> Hostels</h1>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item active">Hostels</li>
                        </ol>
                    </nav>

                    @if(session('status'))
                        <div class="alert alert-success alert-dismissible fade show">{{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    @endif

                    @can('create hostel')
                    <div class="card shadow-sm mb-4">
                        <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                            Add New Hostel / Block
                            <button class="btn btn-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#addHostelForm">
                                <i class="bi bi-plus-circle"></i> Add
                            </button>
                        </div>
                        <div class="collapse" id="addHostelForm">
                            <div class="card-body">
                                <form method="POST" action="{{ route('hostel.hostels.store') }}">
                                    @csrf
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                                            <input type="text" name="name" class="form-control" required placeholder="e.g. Block A – Boys">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                                            <select name="type" class="form-select" required>
                                                <option value="Boys">Boys</option>
                                                <option value="Girls">Girls</option>
                                                <option value="Mixed">Mixed</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label fw-semibold">Capacity <span class="text-danger">*</span></label>
                                            <input type="number" name="intake_capacity" class="form-control" required min="1">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Warden</label>
                                            <select name="warden_id" class="form-select">
                                                <option value="">— None —</option>
                                                @foreach($wardens as $w)
                                                    <option value="{{ $w->id }}">{{ $w->first_name }} {{ $w->last_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Address</label>
                                            <input type="text" name="address" class="form-control" placeholder="Building address or location">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Description</label>
                                            <input type="text" name="description" class="form-control">
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check-circle"></i> Save Hostel</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endcan

                    <div class="row g-3">
                        @forelse($hostels as $h)
                        <div class="col-md-4">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="fw-bold mb-0">{{ $h->name }}</h5>
                                        @php
                                            $typeColor = match($h->type) {
                                                'Boys'  => 'primary',
                                                'Girls' => 'danger',
                                                default => 'secondary',
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $typeColor }}">{{ $h->type }}</span>
                                    </div>
                                    <p class="text-muted small mb-1"><i class="bi bi-people me-1"></i> Capacity: <strong>{{ $h->intake_capacity }}</strong></p>
                                    <p class="text-muted small mb-1"><i class="bi bi-geo-alt me-1"></i> {{ $h->address ?? '—' }}</p>
                                    <p class="text-muted small mb-0"><i class="bi bi-person-badge me-1"></i> Warden: <strong>{{ $h->warden ? $h->warden->first_name.' '.$h->warden->last_name : 'Unassigned' }}</strong></p>
                                </div>
                                <div class="card-footer d-flex gap-2">
                                    @can('edit hostel')
                                    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editHostel{{ $h->id }}">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    @endcan
                                    @can('delete hostel')
                                    <form method="POST" action="{{ route('hostel.hostels.destroy', $h->id) }}" onsubmit="return confirm('Delete hostel {{ addslashes($h->name) }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                    </form>
                                    @endcan
                                </div>
                            </div>
                        </div>

                        {{-- Edit modal --}}
                        @can('edit hostel')
                        <div class="modal fade" id="editHostel{{ $h->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('hostel.hostels.update', $h->id) }}">
                                        @csrf @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Hostel</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body row g-3">
                                            <div class="col-md-8"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="{{ $h->name }}" required></div>
                                            <div class="col-md-4"><label class="form-label">Type</label>
                                                <select name="type" class="form-select" required>
                                                    @foreach(['Boys','Girls','Mixed'] as $t)
                                                        <option value="{{ $t }}" @selected($h->type===$t)>{{ $t }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4"><label class="form-label">Capacity</label><input type="number" name="intake_capacity" class="form-control" value="{{ $h->intake_capacity }}" required></div>
                                            <div class="col-md-8"><label class="form-label">Warden</label>
                                                <select name="warden_id" class="form-select">
                                                    <option value="">— None —</option>
                                                    @foreach($wardens as $w)
                                                        <option value="{{ $w->id }}" @selected($h->warden_id==$w->id)>{{ $w->first_name }} {{ $w->last_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12"><label class="form-label">Address</label><input type="text" name="address" class="form-control" value="{{ $h->address }}"></div>
                                            <div class="col-12"><label class="form-label">Description</label><input type="text" name="description" class="form-control" value="{{ $h->description }}"></div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endcan

                        @empty
                        <div class="col-12">
                            <div class="alert alert-info text-center py-5">
                                <i class="bi bi-building fs-1 d-block mb-2 text-muted"></i>
                                No hostels configured yet.
                            </div>
                        </div>
                        @endforelse
                    </div>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
