@extends('layouts.app')
@section('title', $school->name)

@section('breadcrumb')
<x-breadcrumb :items="[['label'=>'Schools','url'=>route('super.schools.index')],['label'=>$school->name]]"/>
@endsection

@section('content')
<div class="page-wrapper">

    <div class="page-header">
        <div class="flex items-center gap-4">
            <img src="{{ $school->logo_url }}" class="w-14 h-14 rounded-2xl object-cover border" style="border-color:var(--border-card)" alt="">
            <div>
                <h1 class="page-title">{{ $school->name }}</h1>
                <div class="flex items-center gap-2 mt-1 flex-wrap">
                    <span class="badge-{{ match($school->status_color){ 'emerald'=>'success','rose'=>'danger','amber'=>'warning',default=>'neutral' } }}">
                        {{ ucfirst($school->status) }}
                    </span>
                    <span class="badge-primary text-[10px]">{{ $school->plan_label }} Plan</span>
                    <span class="text-xs" style="color:var(--text-muted)">{{ $school->slug }}.nepalschoolhub.com</span>
                </div>
            </div>
        </div>
        <div class="page-actions flex-wrap">
            @if($school->status === 'pending')
            <form method="POST" action="{{ route('super.schools.approve', $school) }}" class="inline">
                @csrf
                <button class="btn-primary"><i class="bi bi-check-circle"></i> Approve</button>
            </form>
            @elseif($school->status === 'active')
            <form method="POST" action="{{ route('super.schools.suspend', $school) }}" class="inline"
                  data-confirm="Suspend {{ $school->name }}?">
                @csrf
                <button class="btn-danger"><i class="bi bi-slash-circle"></i> Suspend</button>
            </form>
            @elseif($school->status === 'suspended')
            <form method="POST" action="{{ route('super.schools.reactivate', $school) }}" class="inline">
                @csrf
                <button class="btn-primary"><i class="bi bi-play-circle"></i> Reactivate</button>
            </form>
            @endif
            <form method="POST" action="{{ route('super.schools.impersonate', $school) }}" class="inline">
                @csrf
                <button class="btn-secondary"><i class="bi bi-person-fill-gear"></i> Login as Admin</button>
            </form>
        </div>
    </div>

    @include('session-messages')

    {{-- Stats row --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <x-stat-card label="Total Users"  :value="$userStats['total']"    icon="bi-people"           icon-color="indigo"/>
        <x-stat-card label="Students"     :value="$userStats['students']" icon="bi-mortarboard"      icon-color="blue"/>
        <x-stat-card label="Teachers"     :value="$userStats['teachers']" icon="bi-person-badge"     icon-color="violet"/>
        <x-stat-card label="Plan Limit"   :value="number_format($limits['students'] ?? 0) . ' students'" :format="false" icon="bi-bar-chart" icon-color="amber"/>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- School details --}}
        <div class="card-modern">
            <div class="card-header-modern">
                <span class="card-title-modern"><i class="bi bi-building text-indigo-500"></i> School Details</span>
            </div>
            <div class="card-body-modern space-y-3 text-sm">
                @foreach([
                    ['Email',    $school->email],
                    ['Phone',    $school->phone ?? '—'],
                    ['District', $school->district ?? '—'],
                    ['Province', $school->province ?? '—'],
                    ['Website',  $school->website ?? '—'],
                    ['Slug',     $school->slug],
                    ['Registered', $school->created_at->format('M d, Y')],
                    ['Approved',   $school->approved_at?->format('M d, Y') ?? 'Pending'],
                ] as [$label, $val])
                <div class="flex justify-between gap-4">
                    <span style="color:var(--text-muted)">{{ $label }}</span>
                    <span class="font-medium text-right" style="color:var(--text-body)">{{ $val }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Plan & change --}}
        <div class="card-modern">
            <div class="card-header-modern">
                <span class="card-title-modern"><i class="bi bi-credit-card text-emerald-500"></i> Plan & Limits</span>
            </div>
            <div class="card-body-modern space-y-4">
                <div class="grid grid-cols-2 gap-3 text-sm">
                    @foreach([
                        ['Plan',     $school->plan_label],
                        ['Students', number_format($limits['students'] ?? 0)],
                        ['Teachers', number_format($limits['teachers'] ?? 0)],
                        ['Staff',    number_format($limits['staff'] ?? 0)],
                        ['AI Tools', ($limits['ai'] ?? false) ? 'Enabled' : 'Disabled'],
                        ['Expires',  $school->plan_expires_at?->format('M d, Y') ?? 'Never'],
                    ] as [$lbl, $v])
                    <div>
                        <p class="text-xs" style="color:var(--text-muted)">{{ $lbl }}</p>
                        <p class="font-semibold" style="color:var(--text-heading)">{{ $v }}</p>
                    </div>
                    @endforeach
                </div>

                <div class="border-t pt-4" style="border-color:var(--border-card)">
                    <p class="text-xs font-semibold mb-2" style="color:var(--text-muted)">Change Plan</p>
                    <form method="POST" action="{{ route('super.schools.plan', $school) }}" class="flex gap-2">
                        @csrf @method('PATCH')
                        <select name="plan" class="select-modern flex-1 text-xs">
                            @foreach(['free','starter','growth','enterprise'] as $p)
                            <option value="{{ $p }}" {{ $school->plan===$p?'selected':'' }}>{{ ucfirst($p) }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn-primary text-xs py-1.5">Update</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Owner --}}
        @if($school->owner)
        <div class="card-modern">
            <div class="card-header-modern">
                <span class="card-title-modern"><i class="bi bi-person-circle text-blue-500"></i> School Owner / Admin</span>
            </div>
            <div class="card-body-modern flex items-center gap-4">
                <img src="{{ $school->owner->avatar }}" class="w-12 h-12 rounded-xl object-cover" alt="">
                <div>
                    <p class="font-semibold" style="color:var(--text-heading)">{{ $school->owner->full_name }}</p>
                    <p class="text-xs" style="color:var(--text-muted)">{{ $school->owner->email }}</p>
                    <p class="text-xs mt-0.5" style="color:var(--text-muted)">Joined {{ $school->owner->created_at->format('M d, Y') }}</p>
                </div>
            </div>
        </div>
        @endif

        {{-- Danger zone --}}
        <div class="card-modern" style="border-color:rgba(239,68,68,.3)">
            <div class="card-header-modern">
                <span class="card-title-modern text-rose-500"><i class="bi bi-exclamation-triangle-fill"></i> Danger Zone</span>
            </div>
            <div class="card-body-modern space-y-3">
                <p class="text-xs" style="color:var(--text-muted)">These actions are destructive or irreversible.</p>
                <form method="POST" action="{{ route('super.schools.destroy', $school) }}"
                      data-confirm="Permanently cancel and archive {{ $school->name }}? This cannot be undone.">
                    @csrf @method('DELETE')
                    <button class="btn-danger text-xs">
                        <i class="bi bi-trash"></i> Cancel & Archive School
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
