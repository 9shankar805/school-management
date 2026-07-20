@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-1"><i class="bi bi-file-earmark-text"></i> Digital Library</h1>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('library.index') }}">Library</a></li>
                            <li class="breadcrumb-item active">E-Books</li>
                        </ol>
                    </nav>

                    @if(session('status'))
                        <div class="alert alert-success alert-dismissible fade show">{{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    @endif

                    <div class="d-flex gap-2 mb-3">
                        @can('create books')
                        <a href="{{ route('library.ebooks.create') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-upload"></i> Upload E-Book
                        </a>
                        @endcan
                    </div>

                    <form method="GET" action="{{ route('library.ebooks.index') }}" class="row g-2 mb-3">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control form-control-sm"
                                   placeholder="Search title, author, ISBN…"
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
                            <select name="file_type" class="form-select form-select-sm">
                                <option value="">All Types</option>
                                <option value="pdf"  @selected(request('file_type') === 'pdf')>PDF</option>
                                <option value="epub" @selected(request('file_type') === 'epub')>EPUB</option>
                                <option value="mobi" @selected(request('file_type') === 'mobi')>MOBI</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-secondary btn-sm w-100" type="submit"><i class="bi bi-search"></i> Filter</button>
                        </div>
                        @if(request()->hasAny(['search','category_id','file_type']))
                        <div class="col-md-1">
                            <a href="{{ route('library.ebooks.index') }}" class="btn btn-outline-secondary btn-sm w-100">Clear</a>
                        </div>
                        @endif
                    </form>

                    <div class="row row-cols-1 row-cols-md-3 row-cols-xl-4 g-3">
                        @forelse($ebooks as $ebook)
                        <div class="col">
                            <div class="card h-100 shadow-sm">
                                @if($ebook->cover_image)
                                    <img src="{{ $ebook->cover_url }}" class="card-img-top" alt="{{ $ebook->title }}" style="height:160px;object-fit:cover">
                                @else
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height:160px">
                                        <i class="{{ $ebook->file_icon }}" style="font-size:3rem"></i>
                                    </div>
                                @endif
                                <div class="card-body">
                                    <h6 class="card-title mb-1">{{ $ebook->title }}</h6>
                                    <p class="card-text text-muted small mb-1">{{ $ebook->author ?? '—' }}</p>
                                    <div class="d-flex gap-1 flex-wrap mb-2">
                                        <span class="badge bg-secondary">{{ strtoupper($ebook->file_type) }}</span>
                                        @if($ebook->category)
                                            <span class="badge" style="background-color:{{ $ebook->category->color }}">{{ $ebook->category->name }}</span>
                                        @endif
                                        <span class="badge bg-{{ $ebook->access_level === 'public' ? 'success' : ($ebook->access_level === 'restricted' ? 'danger' : 'primary') }}">
                                            {{ ucfirst(str_replace('_', ' ', $ebook->access_level)) }}
                                        </span>
                                    </div>
                                    <div class="text-muted" style="font-size:.72rem">
                                        {{ $ebook->file_size_human }} &bull; {{ $ebook->download_count }} downloads
                                    </div>
                                </div>
                                <div class="card-footer d-flex gap-1 flex-wrap">
                                    <a href="{{ route('library.ebooks.download', $ebook->id) }}" class="btn btn-success btn-sm" title="Download">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <a href="{{ route('library.ebooks.show', $ebook->id) }}" class="btn btn-outline-info btn-sm" title="Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @can('edit books')
                                    <a href="{{ route('library.ebooks.edit', $ebook->id) }}" class="btn btn-outline-primary btn-sm" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('library.ebooks.toggle', $ebook->id) }}" method="POST">
                                        @csrf
                                        <button class="btn btn-outline-{{ $ebook->is_active ? 'warning' : 'success' }} btn-sm" title="{{ $ebook->is_active ? 'Unpublish' : 'Publish' }}">
                                            <i class="bi bi-{{ $ebook->is_active ? 'eye-slash' : 'eye' }}"></i>
                                        </button>
                                    </form>
                                    @endcan
                                    @can('delete books')
                                    <form action="{{ route('library.ebooks.destroy', $ebook->id) }}" method="POST" onsubmit="return confirm('Delete this e-book?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-outline-danger btn-sm" title="Delete"><i class="bi bi-trash"></i></button>
                                    </form>
                                    @endcan
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-file-earmark-x" style="font-size:3rem"></i>
                                <p class="mt-2">No e-books found.</p>
                            </div>
                        </div>
                        @endforelse
                    </div>

                    <div class="mt-3">{{ $ebooks->links() }}</div>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
