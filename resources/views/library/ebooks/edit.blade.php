@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-1"><i class="bi bi-pencil-square"></i> Edit E-Book</h1>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('library.ebooks.index') }}">E-Books</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </nav>

                    @if($errors->any())
                        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                    @endif

                    <div class="card shadow-sm" style="max-width:700px">
                        <div class="card-body">
                            <form action="{{ route('library.ebooks.update', $ebook->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf @method('PUT')
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                                        <input type="text" name="title" class="form-control" value="{{ old('title', $ebook->title) }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Author</label>
                                        <input type="text" name="author" class="form-control" value="{{ old('author', $ebook->author) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Publisher</label>
                                        <input type="text" name="publisher" class="form-control" value="{{ old('publisher', $ebook->publisher) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Category</label>
                                        <select name="category_id" class="form-select">
                                            <option value="">— None —</option>
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->id }}" @selected(old('category_id', $ebook->category_id) == $cat->id)>{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">ISBN</label>
                                        <input type="text" name="isbn" class="form-control" value="{{ old('isbn', $ebook->isbn) }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">Pages</label>
                                        <input type="number" name="pages" class="form-control" value="{{ old('pages', $ebook->pages) }}" min="1">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">Year</label>
                                        <input type="number" name="publication_year" class="form-control" value="{{ old('publication_year', $ebook->publication_year) }}" min="1900" max="{{ date('Y') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Cover Image</label>
                                        @if($ebook->cover_image)
                                            <div class="mb-1">
                                                <img src="{{ $ebook->cover_url }}" alt="cover" class="rounded" style="height:60px">
                                            </div>
                                        @endif
                                        <input type="file" name="cover" class="form-control" accept="image/*">
                                        <div class="form-text">Leave blank to keep current.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Access Level <span class="text-danger">*</span></label>
                                        <select name="access_level" class="form-select" required>
                                            <option value="members_only" @selected(old('access_level', $ebook->access_level) === 'members_only')>Members Only</option>
                                            <option value="public"       @selected(old('access_level', $ebook->access_level) === 'public')>Public</option>
                                            <option value="restricted"   @selected(old('access_level', $ebook->access_level) === 'restricted')>Restricted</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Description</label>
                                        <textarea name="description" class="form-control" rows="3">{{ old('description', $ebook->description) }}</textarea>
                                    </div>
                                    <div class="col-12 d-flex gap-2">
                                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Update</button>
                                        <a href="{{ route('library.ebooks.index') }}" class="btn btn-outline-secondary">Cancel</a>
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
