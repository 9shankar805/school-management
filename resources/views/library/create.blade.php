@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3"><i class="bi bi-journals"></i> Add Book</h1>
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('library.store') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Title</label>
                                    <input type="text" name="title" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Author</label>
                                    <input type="text" name="author" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Publisher</label>
                                    <input type="text" name="publisher" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">ISBN</label>
                                    <input type="text" name="isbn" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Quantity</label>
                                    <input type="number" name="qty" class="form-control" value="1" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Save Book</button>
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
