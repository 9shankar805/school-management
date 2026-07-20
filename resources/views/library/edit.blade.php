@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-1"><i class="bi bi-pencil-square"></i> Edit Book</h1>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('library.index') }}">Library</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </nav>

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                        </div>
                    @endif

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <form action="{{ route('library.update', $book->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf @method('PUT')
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                                        <input type="text" name="title" class="form-control" value="{{ old('title', $book->title) }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Category</label>
                                        <select name="category_id" class="form-select">
                                            <option value="">— Select —</option>
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->id }}" @selected(old('category_id', $book->category_id) == $cat->id)>{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Author</label>
                                        <input type="text" name="author" class="form-control" value="{{ old('author', $book->author) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Publisher</label>
                                        <input type="text" name="publisher" class="form-control" value="{{ old('publisher', $book->publisher) }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">Edition</label>
                                        <input type="text" name="edition" class="form-control" value="{{ old('edition', $book->edition) }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">Year</label>
                                        <input type="number" name="publication_year" class="form-control" value="{{ old('publication_year', $book->publication_year) }}" min="1900" max="{{ date('Y') }}">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">ISBN</label>
                                        <input type="text" name="isbn" class="form-control" value="{{ old('isbn', $book->isbn) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Barcode</label>
                                        <input type="text" name="barcode" class="form-control" value="{{ old('barcode', $book->barcode) }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">Language</label>
                                        <input type="text" name="language" class="form-control" value="{{ old('language', $book->language) }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">Total Copies <span class="text-danger">*</span></label>
                                        <input type="number" name="qty" class="form-control" value="{{ old('qty', $book->qty) }}" min="0" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">Available Copies <span class="text-danger">*</span></label>
                                        <input type="number" name="available_qty" class="form-control" value="{{ old('available_qty', $book->available_qty) }}" min="0" required>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Price ($)</label>
                                        <input type="number" name="price" class="form-control" value="{{ old('price', $book->price) }}" min="0" step="0.01">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Shelf Location</label>
                                        <input type="text" name="shelf_location" class="form-control" value="{{ old('shelf_location', $book->shelf_location) }}">
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label fw-semibold">Cover Image</label>
                                        @if($book->cover_image)
                                            <div class="mb-1">
                                                <img src="{{ Storage::url($book->cover_image) }}" alt="cover" class="rounded" style="height:60px">
                                            </div>
                                        @endif
                                        <input type="file" name="cover" class="form-control" accept="image/*">
                                        <div class="form-text">Leave blank to keep current.</div>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Description</label>
                                        <textarea name="description" class="form-control" rows="3">{{ old('description', $book->description) }}</textarea>
                                    </div>

                                    <div class="col-12 d-flex gap-2">
                                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Update Book</button>
                                        <a href="{{ route('library.show', $book->id) }}" class="btn btn-outline-secondary">Cancel</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
