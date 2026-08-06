@extends('layouts.app')
@section('title', 'School Management')

@section('breadcrumb')
<x-breadcrumb :items="[ ['label' => 'Super Admin'], ['label' => 'Schools'] ]"/>
@endsection

@section('content')
<div class="page-wrapper">

    <div class="page-header">
        <div>
            <h1 class="page-title">School Management</h1>
            <p class="page-subtitle">All registered schools on NepalSchoolHub</p>
        </div>
    </div>

    {{-- KPI cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <x-stat-card label="Total Schools"  :value="$stats['total']"     icon="bi-building"        icon-color="indigo"/>
        <x-stat-card label="Active"         :value="$stats['active']"    icon="bi-check-circle"    icon-color="emerald"/>
        <x-stat-card label="Pending Review" :value="$stats['pending']"   icon="bi-hourglass-split" icon-color="amber"/>
        <x-stat-card label="Suspended"      :value="$stats['suspended']" icon="bi-slash-circle"    icon-color="rose"/>
    </div>

    {{-- Filters --}}
    <form method="GET" class="card-modern p-4 mb-6 flex flex-wrap gap-3 items-end">
        <div class="form-group-modern flex-1 min-w-40">
            <label class="label-modern">Search</label>
            <input type="text" name="search" value="{{ request('search') }}"
                   class="input-modern" placeholder="Name, email, slug…">
        </div>
        <div class="form-group-modern min-w-32">
            <label class="label-modern">Status</label>
            <select name="status" class="select-modern">
                <option value="">All</option>
                @foreach(['pending','active','suspended','cancelled'] as $s)
                <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group-modern min-w-32">
            <label class="label-modern">Plan</label>
            <select name="plan" class="select-modern">
                <option value="">All</option>
                @foreach(['free','starter','growth','enterprise'] as $p)
                <option value="{{ $p }}" {{ request('plan')===$p?'selected':'' }}>{{ ucfirst($p) }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2 pb-0.5">
            <button type="submit" class="btn-primary">Filter</button>
            @if(request()->hasAny(['search','status','plan']))
            <a href="{{ route('super.schools.index') }}" class="btn-secondary">Clear</a>
            @endif
        </div>
    </form>

    {{-- Table --}}
    <div class="table-wrapper">
        <div class="table-scroll">
            <table class="table-modern" id="schools-table">
                <thead>
                    <tr>
                        <th style="width:3rem"></th>
                        <th class="sortable">School <span class="sort-icon"></span></th>
                        <th class="sortable">Plan <span class="sort-icon"></span></th>
                        <th class="sortable">Status <span class="sort-icon"></span></th>
                        <th class="sortable">District <span class="sort-icon"></span></th>
                        <th class="sortable">Users <span class="sort-icon"></span></th>
                        <th class="sortable">Registered <span class="sort-icon"></span></th>
                        <th align="right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schools as $school)
                    <tr>
                        <td>
                            <img src="{{ $school->logo_url }}" class="w-9 h-9 rounded-lg object-cover" alt="">
                        </td>
                        <td>
                            <p class="font-semibold text-sm" style="color:var(--text-heading)">{{ $school->name }}</p>
                            <p class="text-xs" style="color:var(--text-muted)">{{ $school->slug }} · {{ $school->email }}</p>
                        </td>
                        <td>
                            @php $pc = $school->plan_color; @endphp
                            <span class="badge-{{ $pc === 'slate' ? 'neutral' : 'primary' }} text-[10px]">
                                {{ $school->plan_label }}
                            </span>
                        </td>
                        <td>
                            @php $sc = $school->status_color; @endphp
                            <span class="badge-{{ match($sc){ 'emerald'=>'success','rose'=>'danger','amber'=>'warning',default=>'neutral' } }}">
                                {{ ucfirst($school->status) }}
                            </span>
                        </td>
                        <td class="text-sm" style="color:var(--text-muted)">{{ $school->district }}</td>
                        <td class="text-sm font-medium" style="color:var(--text-body)">{{ number_format($school->users_count) }}</td>
                        <td class="text-xs" style="color:var(--text-muted)">{{ $school->created_at->format('M d, Y') }}</td>
                        <td align="right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('super.schools.show', $school) }}" class="btn-icon" data-tooltip="View">
                                    <i class="bi bi-eye text-xs"></i>
                                </a>
                                @if($school->status === 'pending')
                                <form method="POST" action="{{ route('super.schools.approve', $school) }}">
                                    @csrf
                                    <button type="submit" class="btn-icon" data-tooltip="Approve"
                                            style="color:#16a34a;border-color:#bbf7d0">
                                        <i class="bi bi-check-lg text-xs"></i>
                                    </button>
                                </form>
                                @elseif($school->status === 'active')
                                <form method="POST" action="{{ route('super.schools.suspend', $school) }}"
                                      data-confirm="Suspend {{ $school->name }}?">
                                    @csrf
                                    <button type="submit" class="btn-icon" data-tooltip="Suspend"
                                            style="color:#dc2626;border-color:#fecaca">
                                        <i class="bi bi-slash-circle text-xs"></i>
                                    </button>
                                </form>
                                @elseif($school->status === 'suspended')
                                <form method="POST" action="{{ route('super.schools.reactivate', $school) }}">
                                    @csrf
                                    <button type="submit" class="btn-icon" data-tooltip="Reactivate"
                                            style="color:#16a34a;border-color:#bbf7d0">
                                        <i class="bi bi-play-circle text-xs"></i>
                                    </button>
                                </form>
                                @endif
                                <form method="POST" action="{{ route('super.schools.impersonate', $school) }}">
                                    @csrf
                                    <button type="submit" class="btn-icon" data-tooltip="Login as admin"
                                            style="color:#6366f1;border-color:#c7d2fe">
                                        <i class="bi bi-person-fill-gear text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-16 text-center" style="color:var(--text-muted)">
                            <i class="bi bi-building text-4xl mb-3 block opacity-20"></i>
                            No schools found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($schools->hasPages())
        <div class="px-4 py-3 border-t" style="border-color:var(--border-card);background:var(--bg-surface)">
            {{ $schools->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
