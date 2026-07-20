@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-1"><i class="bi bi-people"></i> Library Members</h1>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('library.index') }}">Library</a></li>
                            <li class="breadcrumb-item active">Members</li>
                        </ol>
                    </nav>

                    @if(session('status'))
                        <div class="alert alert-success alert-dismissible fade show">{{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    @endif

                    <div class="d-flex gap-2 mb-3">
                        <a href="{{ route('library.members.create') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-person-plus"></i> Enroll Member
                        </a>
                    </div>

                    <form method="GET" action="{{ route('library.members.index') }}" class="row g-2 mb-3">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control form-control-sm"
                                   placeholder="Search name, email, card number…"
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <select name="member_type" class="form-select form-select-sm">
                                <option value="">All Types</option>
                                <option value="student" @selected(request('member_type') === 'student')>Student</option>
                                <option value="teacher" @selected(request('member_type') === 'teacher')>Teacher</option>
                                <option value="staff"   @selected(request('member_type') === 'staff')>Staff</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="active"    @selected(request('status') === 'active')>Active</option>
                                <option value="suspended" @selected(request('status') === 'suspended')>Suspended</option>
                                <option value="expired"   @selected(request('status') === 'expired')>Expired</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-secondary btn-sm w-100" type="submit"><i class="bi bi-search"></i> Filter</button>
                        </div>
                        @if(request()->hasAny(['search','member_type','status']))
                        <div class="col-md-1">
                            <a href="{{ route('library.members.index') }}" class="btn btn-outline-secondary btn-sm w-100">Clear</a>
                        </div>
                        @endif
                    </form>

                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Member</th>
                                        <th>Card No.</th>
                                        <th>Type</th>
                                        <th>Membership</th>
                                        <th class="text-center">Active Loans</th>
                                        <th class="text-center">Overdue</th>
                                        <th>Outstanding Fine</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($members as $member)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $member->user->first_name }} {{ $member->user->last_name }}</div>
                                            <div class="small text-muted">{{ $member->user->email }}</div>
                                        </td>
                                        <td><code>{{ $member->card_number }}</code></td>
                                        <td><span class="badge bg-secondary">{{ ucfirst($member->member_type) }}</span></td>
                                        <td class="small">
                                            {{ $member->membership_start->format('d M Y') }}
                                            @if($member->membership_end)
                                                <br><span class="text-muted">→ {{ $member->membership_end->format('d M Y') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $member->active_issues_count > 0 ? 'bg-primary' : 'bg-light text-dark' }}">
                                                {{ $member->active_issues_count }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $member->overdue_issues_count > 0 ? 'bg-danger' : 'bg-light text-dark' }}">
                                                {{ $member->overdue_issues_count }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($member->outstanding_fine > 0)
                                                <span class="text-danger fw-semibold">${{ number_format($member->outstanding_fine, 2) }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $sc = match($member->status) {
                                                    'active'    => 'bg-success',
                                                    'suspended' => 'bg-warning text-dark',
                                                    'expired'   => 'bg-secondary',
                                                    default     => 'bg-light text-dark',
                                                };
                                            @endphp
                                            <span class="badge {{ $sc }}">{{ ucfirst($member->status) }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('library.members.show', $member->id) }}" class="btn btn-outline-info btn-sm" title="View"><i class="bi bi-eye"></i></a>
                                                <a href="{{ route('library.members.edit', $member->id) }}" class="btn btn-outline-primary btn-sm" title="Edit"><i class="bi bi-pencil"></i></a>
                                                <form action="{{ route('library.members.destroy', $member->id) }}" method="POST"
                                                      onsubmit="return confirm('Remove this member?')">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-outline-danger btn-sm" title="Remove"><i class="bi bi-person-dash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="9" class="text-center text-muted py-4">No members found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-3">{{ $members->links() }}</div>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
