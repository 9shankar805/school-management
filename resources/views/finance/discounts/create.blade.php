@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4" style="max-width:700px">

                    <h1 class="display-6 mb-1"><i class="bi bi-percent"></i> New Discount / Waiver</h1>
                    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-3">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('finance.discounts.index') }}">Discounts</a></li>
                        <li class="breadcrumb-item active">New</li>
                    </ol></nav>

                    @include('session-messages')

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <form method="POST" action="{{ route('finance.discounts.store') }}">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label class="form-label fw-semibold">Discount Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" value="{{ old('name') }}"
                                               class="form-control @error('name') is-invalid @enderror"
                                               placeholder="e.g. Sibling Discount, Merit Scholarship" required>
                                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                                        <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                            <option value="percentage" @selected(old('type','percentage')=='percentage')>Percentage (%)</option>
                                            <option value="fixed"      @selected(old('type')=='fixed')>Fixed Amount ($)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Value <span class="text-danger">*</span></label>
                                        <input type="number" name="value" step="0.01" min="0"
                                               value="{{ old('value') }}"
                                               class="form-control @error('value') is-invalid @enderror" required>
                                        @error('value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Apply to Category</label>
                                        <select name="fee_category_id" class="form-select">
                                            <option value="">— total invoice —</option>
                                            @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}" @selected(old('fee_category_id')==$cat->id)>{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Fee Structure</label>
                                        <select name="fee_structure_id" class="form-select">
                                            <option value="">— any —</option>
                                            @foreach($structures as $s)
                                            <option value="{{ $s->id }}" @selected(old('fee_structure_id')==$s->id)>{{ $s->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Assign to Student</label>
                                        <select name="student_id" class="form-select">
                                            <option value="">— all students (global) —</option>
                                            @foreach($students as $st)
                                            <option value="{{ $st->id }}" @selected(old('student_id')==$st->id)>{{ $st->full_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Valid From</label>
                                        <input type="date" name="valid_from" value="{{ old('valid_from') }}" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Valid Until</label>
                                        <input type="date" name="valid_until" value="{{ old('valid_until') }}" class="form-control">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Reason / Notes</label>
                                        <textarea name="reason" class="form-control" rows="2"
                                                  placeholder="Optional justification">{{ old('reason') }}</textarea>
                                    </div>
                                </div>
                                <hr class="my-4">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Discount</button>
                                    <a href="{{ route('finance.discounts.index') }}" class="btn btn-outline-secondary">Cancel</a>
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
