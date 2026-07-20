@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4" style="max-width:900px">

                    <h1 class="display-6 mb-1"><i class="bi bi-tags"></i> Fee Categories</h1>
                    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-3">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Fee Categories</li>
                    </ol></nav>

                    @include('session-messages')

                    <div class="row g-4">
                        {{-- Add form --}}
                        @can('create invoices')
                        <div class="col-md-4">
                            <div class="card shadow-sm">
                                <div class="card-header bg-white fw-semibold">Add Category</div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('finance.categories.store') }}">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label">Name <span class="text-danger">*</span></label>
                                            <input type="text" name="name" value="{{ old('name') }}"
                                                   class="form-control @error('name') is-invalid @enderror"
                                                   placeholder="e.g. Tuition" required>
                                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Description</label>
                                            <textarea name="description" class="form-control" rows="2"
                                                      placeholder="Optional">{{ old('description') }}</textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Sort Order</label>
                                            <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}"
                                                   class="form-control" min="0">
                                        </div>
                                        <button class="btn btn-primary w-100">
                                            <i class="bi bi-plus-circle"></i> Add Category
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endcan

                        {{-- List --}}
                        <div class="col-md-8">
                            <div class="card shadow-sm">
                                <div class="card-body p-0">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th><th>Name</th><th>Slug</th>
                                                <th>Sort</th><th>Status</th><th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($categories as $cat)
                                            <tr>
                                                <td>{{ $cat->id }}</td>
                                                <td class="fw-semibold">{{ $cat->name }}</td>
                                                <td><small class="text-muted font-monospace">{{ $cat->slug }}</small></td>
                                                <td>{{ $cat->sort_order }}</td>
                                                <td>
                                                    <span class="badge {{ $cat->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                        {{ $cat->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @can('create invoices')
                                                    <button class="btn btn-sm btn-outline-secondary"
                                                            onclick="openEdit({{ $cat->id }}, '{{ addslashes($cat->name) }}', '{{ addslashes($cat->description) }}', {{ $cat->sort_order }}, {{ $cat->is_active ? 1 : 0 }})">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <form method="POST" action="{{ route('finance.categories.destroy', $cat->id) }}"
                                                          class="d-inline" onsubmit="return confirm('Delete this category?')">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-sm btn-outline-danger">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                    @endcan
                                                </td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="6" class="text-center text-muted py-4">No categories yet.</td></tr>
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

{{-- Edit modal --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="editForm">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="edit_desc" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" id="edit_sort" class="form-control" min="0">
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_active" id="edit_active" value="1" class="form-check-input">
                        <label class="form-check-label" for="edit_active">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openEdit(id, name, desc, sort, active) {
    document.getElementById('editForm').action = '/finance/categories/' + id;
    document.getElementById('edit_name').value  = name;
    document.getElementById('edit_desc').value  = desc;
    document.getElementById('edit_sort').value  = sort;
    document.getElementById('edit_active').checked = active === 1;
    new bootstrap.Modal(document.getElementById('editModal')).show();
}
</script>
@endpush
