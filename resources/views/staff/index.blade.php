@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3"><i class="bi bi-person-lines-fill"></i> Staff</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Staff</li>
                        </ol>
                    </nav>
                    <div class="mb-3">
                        <a href="{{ route('staff.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add New Staff</a>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($staffs as $staff)
                                    <tr>
                                        <td>{{ $staff->first_name }} {{ $staff->last_name }}</td>
                                        <td>{{ $staff->email }}</td>
                                        <td>{{ $staff->role }}</td>
                                        <td class="text-nowrap">
                                            @can('manage roles')
                                            <a href="{{ route('roles.user-roles', $staff->id) }}"
                                               class="btn btn-sm btn-outline-success me-1">
                                                <i class="bi bi-person-check"></i> Assign Roles
                                            </a>
                                            @endcan
                                            <a href="{{ route('staff.edit', $staff->id) }}"
                                               class="btn btn-sm btn-outline-primary me-1">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                            <form method="POST" action="{{ route('staff.destroy', $staff->id) }}"
                                                  class="d-inline" onsubmit="return confirm('Delete this staff?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
