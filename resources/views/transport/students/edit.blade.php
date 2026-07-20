@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-1"><i class="bi bi-pencil-square"></i> Edit Allocation</h1>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('transport.students.index') }}">Students</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </nav>
                    @if($errors->any())
                        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                    @endif
                    <div class="card shadow-sm" style="max-width:650px">
                        <div class="card-header fw-semibold">
                            {{ $allocation->student->first_name }} {{ $allocation->student->last_name }}
                        </div>
                        <div class="card-body">
                            <form action="{{ route('transport.students.update', $allocation->id) }}" method="POST">
                                @csrf @method('PUT')
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Route <span class="text-danger">*</span></label>
                                    <select name="route_id" id="routeSelect" class="form-select" required>
                                        @foreach($routes as $r)
                                            <option value="{{ $r->id }}" data-fee="{{ $r->monthly_fee }}" @selected(old('route_id',$allocation->route_id)==$r->id)>
                                                {{ $r->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Stop</label>
                                    <select name="stop_id" id="stopSelect" class="form-select">
                                        <option value="">— No specific stop —</option>
                                        @foreach($routes->firstWhere('id', $allocation->route_id)?->stops ?? [] as $stop)
                                            <option value="{{ $stop->id }}" @selected(old('stop_id',$allocation->stop_id)==$stop->id)>
                                                {{ $stop->stop_order }}. {{ $stop->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Direction</label>
                                        <select name="direction" class="form-select">
                                            <option value="both"         @selected(old('direction',$allocation->direction)==='both')>Both</option>
                                            <option value="pickup_only"  @selected(old('direction',$allocation->direction)==='pickup_only')>Pickup Only</option>
                                            <option value="dropoff_only" @selected(old('direction',$allocation->direction)==='dropoff_only')>Dropoff Only</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Monthly Fee ($)</label>
                                        <input type="number" name="monthly_fee" class="form-control" value="{{ old('monthly_fee', $allocation->monthly_fee) }}" step="0.01" min="0" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Status</label>
                                        <select name="status" class="form-select">
                                            <option value="active"    @selected(old('status',$allocation->status)==='active')>Active</option>
                                            <option value="suspended" @selected(old('status',$allocation->status)==='suspended')>Suspended</option>
                                            <option value="cancelled" @selected(old('status',$allocation->status)==='cancelled')>Cancelled</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6"><label class="form-label fw-semibold">Start Date</label><input type="date" name="start_date" class="form-control" value="{{ old('start_date', $allocation->start_date->format('Y-m-d')) }}" required></div>
                                    <div class="col-md-6"><label class="form-label fw-semibold">End Date</label><input type="date" name="end_date" class="form-control" value="{{ old('end_date', $allocation->end_date?->format('Y-m-d')) }}"></div>
                                </div>
                                <div class="mb-3"><label class="form-label fw-semibold">Notes</label><textarea name="notes" class="form-control" rows="2">{{ old('notes', $allocation->notes) }}</textarea></div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Update</button>
                                    <a href="{{ route('transport.students.index') }}" class="btn btn-outline-secondary">Cancel</a>
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
