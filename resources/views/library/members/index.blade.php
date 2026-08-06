@extends('layouts.app')
@section('title', 'Library Members')

@section('content')
<style>
  .header-icon-box-library {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%) !important;
    color: #ffffff !important;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
  }
  .header-icon-box-library i {
    color: #ffffff !important;
    font-size: 22px !important;
    line-height: 1 !important;
    display: inline-block !important;
    opacity: 1 !important;
    visibility: visible !important;
  }
</style>

<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-3 mb-1">
                <div class="header-icon-box-library">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Library Members Directory</h2>
                    <p class="text-slate-500 text-sm mb-0">Manage student, teacher, and staff library card enrollments</p>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            @unlessrole('student|parent')
            <a href="{{ route('library.members.create') }}" class="btn btn-indigo bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-3.5 py-2.5 rounded-xl text-xs shadow-sm transition">
                <i class="bi bi-person-plus-fill me-1"></i> Enroll Member
            </a>
            @endunlessrole
            <a href="{{ route('library.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2.5 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-journals me-1"></i> Catalog
            </a>
            <a href="{{ route('library.issues.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2.5 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-arrow-left-right me-1"></i> Circulation Log
            </a>
        </div>
    </div>

    @include('session-messages')

    <!-- Filter Control Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-4">
        <form method="GET" action="{{ route('library.members.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Search Member</label>
                <input type="text" name="search" class="form-control rounded-xl border-slate-200 py-2 text-sm font-medium text-slate-800" placeholder="Search name, email, or card number..." value="{{ request('search') }}">
            </div>

            <div class="col-12 col-md-3">
                <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Member Type</label>
                <select name="member_type" class="form-select rounded-xl border-slate-200 py-2 text-sm font-medium text-slate-800">
                    <option value="">All Member Types</option>
                    <option value="student" @selected(request('member_type') === 'student')>Student</option>
                    <option value="teacher" @selected(request('member_type') === 'teacher')>Teacher</option>
                    <option value="staff" @selected(request('member_type') === 'staff')>Staff</option>
                </select>
            </div>

            <div class="col-12 col-md-3">
                <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Membership Status</label>
                <select name="status" class="form-select rounded-xl border-slate-200 py-2 text-sm font-medium text-slate-800">
                    <option value="">All Statuses</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="suspended" @selected(request('status') === 'suspended')>Suspended</option>
                    <option value="expired" @selected(request('status') === 'expired')>Expired</option>
                </select>
            </div>

            <div class="col-12 col-md-2">
                <button type="submit" class="btn btn-indigo bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-xl text-sm w-100 shadow-sm transition">
                    <i class="bi bi-filter me-1"></i> Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Members Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
        <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
            <div>
                <h5 class="font-bold text-slate-800 mb-0">Enrolled Library Members</h5>
                <p class="text-xs text-slate-400 mb-0">Registered cardholders and active borrower status</p>
            </div>
            <span class="badge bg-indigo-50 text-indigo-700 font-semibold text-xs px-3 py-1 rounded-full">
                Total Members: {{ $members->total() }}
            </span>
        </div>

        @if($members->count() > 0)
        <div class="table-responsive">
            <table class="table align-middle text-sm mb-0">
                <thead>
                    <tr class="text-xs font-semibold text-slate-400 uppercase tracking-wider bg-slate-50">
                        <th class="py-3 px-4 rounded-s-xl">Member Info</th>
                        <th class="py-3 px-4">Card Number</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4">Membership Period</th>
                        <th class="py-3 px-4 text-center">Active Loans</th>
                        <th class="py-3 px-4 text-center">Overdue</th>
                        <th class="py-3 px-4">Outstanding Fine</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 rounded-e-xl text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($members as $member)
                    <tr>
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-800">{{ $member->user->first_name ?? '' }} {{ $member->user->last_name ?? 'Member' }}</div>
                            <div class="text-xs text-slate-400">{{ $member->user->email ?? '' }}</div>
                        </td>
                        <td class="py-3 px-4">
                            <code class="px-2 py-1 bg-slate-100 text-slate-700 rounded-lg text-xs font-mono font-bold">
                                {{ $member->card_number }}
                            </code>
                        </td>
                        <td class="py-3 px-4">
                            <span class="badge bg-slate-100 text-slate-700 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                {{ ucfirst($member->member_type) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-xs font-medium text-slate-600">
                            {{ $member->membership_start->format('d M Y') }}
                            @if($member->membership_end)
                                <div class="text-[10px] text-slate-400">until {{ $member->membership_end->format('d M Y') }}</div>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="badge {{ $member->active_issues_count > 0 ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-500' }} text-xs px-2.5 py-1 rounded-full font-semibold">
                                {{ $member->active_issues_count }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="badge {{ $member->overdue_issues_count > 0 ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-500' }} text-xs px-2.5 py-1 rounded-full font-semibold">
                                {{ $member->overdue_issues_count }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            @if($member->outstanding_fine > 0)
                                <span class="text-rose-600 font-bold text-xs">Rs. {{ number_format($member->outstanding_fine, 2) }}</span>
                            @else
                                <span class="text-xs text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($member->status === 'active')
                            <span class="badge bg-emerald-100 text-emerald-700 text-xs px-2.5 py-1 rounded-full font-semibold">
                                <i class="bi bi-check-circle-fill me-1"></i> Active
                            </span>
                            @elseif($member->status === 'suspended')
                            <span class="badge bg-amber-100 text-amber-700 text-xs px-2.5 py-1 rounded-full font-semibold">
                                <i class="bi bi-exclamation-circle-fill me-1"></i> Suspended
                            </span>
                            @else
                            <span class="badge bg-slate-100 text-slate-600 text-xs px-2.5 py-1 rounded-full font-semibold">
                                Expired
                            </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-end">
                            <a href="{{ route('library.members.show', $member->id) }}" class="btn btn-sm btn-light border border-slate-200 text-slate-700 text-xs py-1 px-2.5 rounded-lg" title="View Member Profile">
                                <i class="bi bi-eye"></i>
                            </a>
                            @unlessrole('student|parent')
                            <a href="{{ route('library.members.edit', $member->id) }}" class="btn btn-sm btn-light border border-slate-200 text-slate-700 text-xs py-1 px-2.5 rounded-lg ms-1" title="Edit Member">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('library.members.destroy', $member->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove library member?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger text-xs py-1 px-2 rounded-lg ms-1" title="Remove Member">
                                    <i class="bi bi-person-dash"></i>
                                </button>
                            </form>
                            @endunlessrole
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $members->links() }}
        </div>
        @else
        <div class="text-center py-5 text-slate-400">
            <i class="bi bi-people display-3 text-slate-200 mb-3 block"></i>
            <h6 class="font-bold text-slate-600 mb-1">No Library Members Found</h6>
            <p class="mb-0 text-xs">Enroll students or teachers to issue library cards.</p>
        </div>
        @endif
    </div>
</div>
@endsection
