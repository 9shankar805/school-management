@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-1"><i class="bi bi-tags"></i> Book Categories</h1>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('library.index') }}">Library</a></li>
                            <li class="breadcrumb-item active">Categories</li>
                        </ol>
                    </nav>

                    @if(session('status'))
                        <div class="alert alert-success alert-dismissible fade show">{{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    @endif

                    <div class="row g-4">
                        {{-- Add Category form --}}
                        @can('create books')
                        <div class="col-md-4">
                            <div class="card shadow-sm">
                                <div class="card-header fw-semibold">Add Category</div>
                                <div class="card-body">
                                    <form action="{{ route('library.categories.store') }}" method="POST">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label">Name <span class="text-danger">*</span></label>
                                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Badge Colour</label>
                                            <div class="d-flex gap-2 align-items-center">
                                                <input type="color" name="color" class="form-control form-control-color" value="{{ old('color', '#6c757d') }}" title="Pick a colour">
                                                <span class="small text-muted">Used for badge display</span>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Description</label>
                                            <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-plus-circle"></i> Add Category</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endcan

                        {{-- Categories list --}}
                        <div class="col-md-{{ auth()->user()->can('create books') ? '8' : '12' }}">
                            <div class="card shadow-sm">
                                <div class="card-body p-0">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Category</th>
                                                <th>Description</th>
                                                <th class="text-center">Books</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($categories as $cat)
                                            <tr>
                                                <td>
                                                    <span class="badge fs-6" style="background-color:{{ $cat->color }}">{{ $cat->name }}</span>
                                                </td>
                                                <td class="text-muted small">{{ $cat->description ?? '—' }}</td>
                                                <td class="text-center">
                                                    <a href="{{ route('library.index', ['category_id' => $cat->id]) }}" class="badge bg-secondary text-decoration-none">
                                                        {{ $cat->books_count }}
                                                    </a>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-1">
                                                        @can('edit books')
                                                        <button class="btn btn-outline-primary btn-sm"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#editCatModal"
                                                                data-id="{{ $cat->id }}"
                                                                data-name="{{ $cat->name }}"
                                                                data-color="{{ $cat->color }}"
                                                                data-desc="{{ $cat->description }}">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        @endcan
                                                        @can('delete books')
                                                        <form action="{{ route('library.categories.destroy', $cat->id) }}" method="POST"
                                                              onsubmit="return confirm('Delete category \'{{ $cat->name }}\'?')">
                                                            @csrf @method('DELETE')
                                                            <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                                        </form>
                                                        @endcan
                                                    </div>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="4" class="text-center text-muted py-4">No categories yet.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>

{{-- Edit Category Modal --}}
@can('edit books')
<div class="modal fade" id="editCatModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editCatForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editCatName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Badge Colour</label>
                        <input type="color" name="color" id="editCatColor" class="form-control form-control-color">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="editCatDesc" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('editCatModal').addEventListener('show.bs.modal', function(e) {
    const btn  = e.relatedTarget;
    const id   = btn.dataset.id;
    const base = '{{ url("library/categories") }}';
    document.getElementById('editCatForm').action = base + '/' + id;
    document.getElementById('editCatName').value  = btn.dataset.name;
    document.getElementById('editCatColor').value = btn.dataset.color;
    document.getElementById('editCatDesc').value  = btn.dataset.desc || '';
});
</script>
@endpush
@endcan
@endsection
