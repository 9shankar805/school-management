@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-1"><i class="bi bi-journals"></i> Library — Book Catalog</h1>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item active">Library</li>
                        </ol>
                    </nav>

                    @if(session('status'))
                        <div class="alert alert-success alert-dismissible fade show">{{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    @endif

                    {{-- KPI strip --}}
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-3">
                            <div class="card border-0 bg-primary bg-opacity-10 text-center py-3">
                                <div class="fs-2 fw-bold text-primary">{{ $totalBooks }}</div>
                                <div class="small text-muted">Total Titles</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card border-0 bg-success bg-opacity-10 text-center py-3">
                                <div class="fs-2 fw-bold text-success">{{ $available }}</div>
                                <div class="small text-muted">Available</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card border-0 bg-warning bg-opacity-10 text-center py-3">
                                <div class="fs-2 fw-bold text-warning">{{ $issued }}</div>
                                <div class="small text-muted">On Loan</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card border-0 bg-danger bg-opacity-10 text-center py-3">
                                <div class="fs-2 fw-bold text-danger">{{ $overdue }}</div>
                                <div class="small text-muted">Overdue</div>
                            </div>
                        </div>
                    </div>

                    {{-- Actions + Filters --}}
                    <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
                        @can('create books')
                        <a href="{{ route('library.create') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-circle"></i> Add Book
                        </a>
                        @endcan
                        <a href="{{ route('library.categories.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-tags"></i> Categories
                        </a>
                        <a href="{{ route('library.analytics') }}" class="btn btn-outline-info btn-sm">
                            <i class="bi bi-bar-chart-line"></i> Analytics
                        </a>
                        <a href="{{ route('library.reports.form') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-file-earmark-spreadsheet"></i> Reports
                        </a>
                    </div>

                    <form method="GET" action="{{ route('library.index') }}" class="row g-2 mb-3">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control form-control-sm"
                                   placeholder="Search title, author, ISBN, barcode…"
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <select name="category_id" class="form-select form-select-sm">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="availability" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="available"   @selected(request('availability') === 'available')>Available</option>
                                <option value="unavailable" @selected(request('availability') === 'unavailable')>Out of Stock</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-secondary btn-sm w-100" type="submit">
                                <i class="bi bi-search"></i> Filter
                            </button>
                        </div>
                        @if(request()->hasAny(['search','category_id','availability']))
                        <div class="col-md-1">
                            <a href="{{ route('library.index') }}" class="btn btn-outline-secondary btn-sm w-100">Clear</a>
                        </div>
                        @endif
                    </form>

                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="40">#</th>
                                        <th>Title / Author</th>
                                        <th>Category</th>
                                        <th>ISBN / Barcode</th>
                                        <th>Location</th>
                                        <th>Stock</th>
                                        <th>Availability</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($books as $book)
                                    <tr>
                                        <td class="text-muted small">{{ $books->firstItem() + $loop->index }}</td>
                                        <td>
                                            <div class="fw-semibold">
                                                <a href="{{ route('library.show', $book->id) }}" class="text-decoration-none text-dark">
                                                    {{ $book->title }}
                                                </a>
                                            </div>
                                            <div class="small text-muted">{{ $book->author }}</div>
                                        </td>
                                        <td>
                                            @if($book->category)
                                                <span class="badge" style="background-color:{{ $book->category->color }}">{{ $book->category->name }}</span>
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </td>
                                        <td class="small">
                                            @if($book->isbn)<div>ISBN: {{ $book->isbn }}</div>@endif
                                            @if($book->barcode)<div class="text-muted">{{ $book->barcode }}</div>@endif
                                        </td>
                                        <td class="small text-muted">{{ $book->shelf_location ?? '—' }}</td>
                                        <td class="text-center">{{ $book->available_qty }}/{{ $book->qty }}</td>
                                        <td>{!! $book->availability_badge !!}</td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('library.show', $book->id) }}" class="btn btn-outline-info btn-sm" title="View"><i class="bi bi-eye"></i></a>
                                                @can('edit books')
                                                <a href="{{ route('library.edit', $book->id) }}" class="btn btn-outline-primary btn-sm" title="Edit"><i class="bi bi-pencil"></i></a>
                                                @endcan
                                                @can('issue books')
                                                <a href="{{ route('library.issues.create', ['book_id' => $book->id]) }}" class="btn btn-outline-success btn-sm" title="Issue"><i class="bi bi-box-arrow-up-right"></i></a>
                                                @endcan
                                                @can('delete books')
                                                <form action="{{ route('library.destroy', $book->id) }}" method="POST" onsubmit="return confirm('Delete this book?')">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-outline-danger btn-sm" title="Delete"><i class="bi bi-trash"></i></button>
                                                </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">No books found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-3">{{ $books->links() }}</div>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
