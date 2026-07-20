@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('library.ebooks.index') }}">E-Books</a></li>
                            <li class="breadcrumb-item active">{{ Str::limit($ebook->title, 40) }}</li>
                        </ol>
                    </nav>

                    <div class="row g-4">
                        <div class="col-md-3 text-center">
                            @if($ebook->cover_image)
                                <img src="{{ $ebook->cover_url }}" alt="{{ $ebook->title }}" class="img-fluid rounded shadow-sm mb-3" style="max-height:250px;object-fit:cover">
                            @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center mb-3" style="height:200px">
                                    <i class="{{ $ebook->file_icon }}" style="font-size:4rem"></i>
                                </div>
                            @endif
                            <a href="{{ route('library.ebooks.download', $ebook->id) }}" class="btn btn-success w-100">
                                <i class="bi bi-download"></i> Download
                            </a>
                            @can('edit books')
                            <a href="{{ route('library.ebooks.edit', $ebook->id) }}" class="btn btn-outline-primary w-100 mt-2">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            @endcan
                        </div>
                        <div class="col-md-9">
                            <h3 class="fw-bold">{{ $ebook->title }}</h3>
                            <p class="text-muted">by {{ $ebook->author ?? 'Unknown Author' }}</p>

                            <div class="d-flex gap-2 flex-wrap mb-3">
                                <span class="badge bg-secondary">{{ strtoupper($ebook->file_type) }}</span>
                                @if($ebook->category)
                                    <span class="badge" style="background-color:{{ $ebook->category->color }}">{{ $ebook->category->name }}</span>
                                @endif
                                <span class="badge bg-{{ $ebook->access_level === 'public' ? 'success' : ($ebook->access_level === 'restricted' ? 'danger' : 'primary') }}">
                                    {{ ucfirst(str_replace('_', ' ', $ebook->access_level)) }}
                                </span>
                                @if(!$ebook->is_active)
                                    <span class="badge bg-warning text-dark">Unpublished</span>
                                @endif
                            </div>

                            <dl class="row">
                                <dt class="col-sm-3">Publisher</dt>
                                <dd class="col-sm-9">{{ $ebook->publisher ?? '—' }}</dd>
                                <dt class="col-sm-3">Year</dt>
                                <dd class="col-sm-9">{{ $ebook->publication_year ?? '—' }}</dd>
                                <dt class="col-sm-3">ISBN</dt>
                                <dd class="col-sm-9">{{ $ebook->isbn ?? '—' }}</dd>
                                <dt class="col-sm-3">Pages</dt>
                                <dd class="col-sm-9">{{ $ebook->pages ?? '—' }}</dd>
                                <dt class="col-sm-3">File Size</dt>
                                <dd class="col-sm-9">{{ $ebook->file_size_human }}</dd>
                                <dt class="col-sm-3">Downloads</dt>
                                <dd class="col-sm-9">{{ number_format($ebook->download_count) }}</dd>
                            </dl>

                            @if($ebook->description)
                                <hr>
                                <p class="text-muted">{{ $ebook->description }}</p>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
