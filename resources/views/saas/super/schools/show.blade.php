@extends('layouts.app')
@section('title', $school->name)

@php
$activeTab = request()->routeIs('super.schools.committee') ? 'governance' : 'overview';
@endphp

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

    {{-- Tabs --}}
    <div class="card-modern mb-6 !p-0">
        <div class="flex border-b text-sm font-medium" style="border-color:var(--border-card)">
            <a href="{{ route('super.schools.show', $school) }}"
               class="flex items-center gap-2 px-5 py-3 border-b-2 transition {{ $activeTab==='overview' ? 'border-indigo-500 text-indigo-600' : 'border-transparent hover:text-indigo-500' }}"
               style="color:{{ $activeTab==='overview' ? '' : 'var(--text-muted)' }}">
                <i class="bi bi-building"></i> Overview
            </a>
            <a href="{{ route('super.schools.committee', $school) }}"
               class="flex items-center gap-2 px-5 py-3 border-b-2 transition {{ $activeTab==='governance' ? 'border-indigo-500 text-indigo-600' : 'border-transparent hover:text-indigo-500' }}"
               style="color:{{ $activeTab==='governance' ? '' : 'var(--text-muted)' }}">
                <i class="bi bi-diagram-3"></i> Governance (SMC)
                @if($activeTab==='governance' && isset($compliance) && $compliance['total']>0)
                    <span class="text-[10px] px-1.5 py-0.5 rounded {{ $compliance['compliant']===$compliance['total'] ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                        {{ $compliance['compliant'] }}/{{ $compliance['total'] }}
                    </span>
                @endif
            </a>
        </div>
    </div>

    @if($activeTab === 'overview')
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
    @endif

    @if($activeTab === 'governance')
    <div class="space-y-6">

        {{-- Compliance Banner + Stats --}}
        <div class="card-modern">
            <div class="card-header-modern flex items-center justify-between flex-wrap gap-3">
                <div>
                    <span class="card-title-modern"><i class="bi bi-shield-check text-indigo-500"></i> School Management Committee (SMC) — Governance</span>
                    <p class="text-xs mt-1" style="color:var(--text-muted)">Nepal Education Regulations 2026 (July): SMC members MUST sign a declaration of no political party membership.</p>
                </div>
                <button onclick="document.getElementById('smc-add-modal').classList.remove('hidden')"
                        class="btn-primary text-sm">
                    <i class="bi bi-person-plus"></i> Add Member
                </button>
            </div>
            <div class="card-body-modern">
                @php
                $members = $members ?? collect([]);
                $total = $members->count();
                $compliant = $members->where('no_political_party_member', true)->count();
                $withDocs  = $members->filter(fn($m) => $m->documents->count() > 0)->count();
                @endphp

                @if($total > 0 && $compliant < $total)
                <div class="mb-4 p-3 rounded-lg flex items-start gap-3" style="background:rgba(251,191,36,.1); border:1px solid rgba(251,191,36,.3)">
                    <i class="bi bi-exclamation-triangle text-amber-500 mt-0.5"></i>
                    <div class="text-sm" style="color:var(--text-body)">
                        <strong class="text-amber-700">Compliance Alert:</strong>
                        {{ $total - $compliant }} of {{ $total }} SMC member(s) have <em>not</em> signed the no-political-party declaration. Per Education Regs 2026 § SMC membership, this must be resolved before the next MoEST audit.
                    </div>
                </div>
                @endif

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                    @foreach([
                        ['label'=>'Total Members',   'value'=>$total,                  'icon'=>'bi-people',          'color'=>'indigo'],
                        ['label'=>'Compliant (No Party)', 'value'=>$compliant,       'icon'=>'bi-shield-check',    'color'=>$compliant===$total && $total>0 ? 'emerald' : 'amber'],
                        ['label'=>'With Documents',  'value'=>$withDocs,              'icon'=>'bi-file-earmark-pdf','color'=>'blue'],
                        ['label'=>'Compliance %',    'value'=>$total>0 ? round($compliant/$total*100).'%' : '—', 'icon'=>'bi-graph-up',      'color'=>$total>0 && $compliant===$total ? 'emerald' : 'violet'],
                    ] as [$lbl, $val, $ico, $col])
                    <div class="p-3 rounded-lg border" style="border-color:var(--border-card)">
                        <div class="flex items-center gap-2 mb-1">
                            <i class="bi {{ $ico }} text-{{ $col }}-500"></i>
                            <span class="text-xs" style="color:var(--text-muted)">{{ $lbl }}</span>
                        </div>
                        <p class="text-xl font-bold" style="color:var(--text-heading)">{{ $val }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Members Table --}}
        <div class="card-modern">
            <div class="card-header-modern">
                <span class="card-title-modern"><i class="bi bi-list-ul text-violet-500"></i> SMC Members</span>
            </div>
            <div class="card-body-modern !p-0">
                @if($members->count() === 0)
                <div class="text-center py-10">
                    <i class="bi bi-diagram-3 text-5xl" style="color:var(--text-muted); opacity:.4"></i>
                    <p class="mt-3 font-medium" style="color:var(--text-muted)">No SMC members yet.</p>
                    <p class="text-xs mt-1" style="color:var(--text-muted)">Click <strong>Add Member</strong> above to record the School Management Committee.</p>
                </div>
                @else
                <div class="overflow-x-auto">
                    <table class="table-minimal w-full text-sm">
                        <thead>
                            <tr style="border-bottom:1px solid var(--border-card)">
                                <th class="text-left py-2 px-4">Member</th>
                                <th class="text-left py-2 px-4">Position</th>
                                <th class="text-left py-2 px-4">Political Declaration</th>
                                <th class="text-left py-2 px-4">Citizenship / Voter ID</th>
                                <th class="text-left py-2 px-4">Documents</th>
                                <th class="text-left py-2 px-4">Tenure</th>
                                <th class="text-right py-2 px-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($members as $m)
                            <tr class="border-b hover:bg-slate-50 dark:hover:bg-slate-800/30 transition" style="border-color:var(--border-card)">
                                <td class="py-3 px-4">
                                    <div class="font-medium" style="color:var(--text-heading)">{{ $m->full_name }}</div>
                                    @if($m->phone || $m->email)
                                    <div class="text-xs mt-0.5 space-y-0.5" style="color:var(--text-muted)">
                                        @if($m->phone) <div><i class="bi bi-telephone mr-1"></i>{{ $m->phone }}</div> @endif
                                        @if($m->email) <div><i class="bi bi-envelope mr-1"></i>{{ $m->email }}</div> @endif
                                    </div>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-indigo-50 text-indigo-700">
                                        {{ $m->position_label }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium {{ $m->compliance_badge_class }}">
                                        {{ $m->compliance_label }}
                                    </span>
                                    @if($m->declaration_signed_at)
                                    <div class="text-[10px] mt-1" style="color:var(--text-muted)">
                                        Signed {{ $m->declaration_signed_at->format('M d, Y') }}
                                    </div>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-xs" style="color:var(--text-body)">
                                    @if($m->citizenship_no)
                                    <div><span style="color:var(--text-muted)">Citizenship:</span> {{ $m->citizenship_no }}</div>
                                    @endif
                                    @if($m->voter_id_no)
                                    <div class="mt-0.5"><span style="color:var(--text-muted)">Voter ID:</span> {{ $m->voter_id_no }}</div>
                                    @endif
                                    @if(!$m->citizenship_no && !$m->voter_id_no)
                                    <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    @if($m->documents->count() > 0)
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($m->documents as $doc)
                                        <a href="{{ $doc->url }}" target="_blank"
                                           class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                                            <i class="bi bi-file-earmark"></i>
                                            {{ \Illuminate\Support\Str::limit($doc->original_name, 18) }}
                                        </a>
                                        <form method="POST" class="inline"
                                              action="{{ route('super.schools.committee.document.destroy', [$school, $doc]) }}"
                                              data-confirm="Delete this document?">
                                            @csrf @method('DELETE')
                                            <button class="text-[10px] text-rose-500 hover:text-rose-700 px-1" title="Delete document">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                        @endforeach
                                    </div>
                                    @else
                                    <span class="text-xs text-slate-400">No docs</span>
                                    @endif
                                    <div class="mt-2">
                                        <button type="button"
                                                onclick="openDocModal({{ $m->id }}, `{{ addslashes($m->full_name) }}`)"
                                                class="text-[10px] text-indigo-500 hover:text-indigo-700 font-medium">
                                            <i class="bi bi-cloud-upload"></i> Upload docs
                                        </button>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-xs" style="color:var(--text-body)">
                                    @if($m->appointed_at)
                                    <div><span style="color:var(--text-muted)">Appointed:</span> {{ $m->appointed_at->format('M d, Y') }}</div>
                                    @endif
                                    @if($m->term_ends_at)
                                    <div class="mt-0.5"><span style="color:var(--text-muted)">Ends:</span> {{ $m->term_ends_at->format('M d, Y') }}</div>
                                    @endif
                                    @if(!$m->appointed_at && !$m->term_ends_at)
                                    <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button"
                                                onclick='openEditModal(@json($m))'
                                                class="btn-secondary text-xs py-1 px-2 !bg-transparent !border !border-indigo-200 text-indigo-600 hover:!bg-indigo-50">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <form method="POST"
                                              action="{{ route('super.schools.committee.members.destroy', [$school, $m]) }}"
                                              data-confirm="Remove SMC member {{ $m->full_name }}?">
                                            @csrf @method('DELETE')
                                            <button class="btn-danger text-xs py-1 px-2 !bg-transparent !border !border-rose-200 text-rose-600 hover:!bg-rose-50">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>

    </div>
    @endif
</div>

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- ADD MEMBER MODAL                                                      --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
<div id="smc-add-modal" class="fixed inset-0 z-50 hidden items-start justify-center pt-16 bg-slate-900/60 backdrop-blur-sm"
     onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="card-modern w-full max-w-2xl mx-4 shadow-2xl" style="max-height:86vh; overflow-y:auto">
        <div class="card-header-modern flex items-center justify-between">
            <span class="card-title-modern"><i class="bi bi-person-plus text-indigo-500"></i> Add SMC Member</span>
            <button type="button" onclick="document.getElementById('smc-add-modal').classList.add('hidden')"
                    class="text-slate-400 hover:text-slate-600 text-xl">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('super.schools.committee.members.store', $school) }}" class="card-body-modern space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold" style="color:var(--text-muted)">Full Name *</label>
                    <input type="text" name="full_name" required maxlength="200" class="input-modern mt-1 text-sm"
                           placeholder="e.g. Ram Prasad Sharma">
                </div>
                <div>
                    <label class="text-xs font-semibold" style="color:var(--text-muted)">Position *</label>
                    <select name="position" required class="select-modern mt-1 text-sm">
                        @foreach(App\Models\SchoolCommitteeMember::POSITIONS as $val => $lbl)
                        <option value="{{ $val }}">{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold" style="color:var(--text-muted)">Phone</label>
                    <input type="text" name="phone" maxlength="20" class="input-modern mt-1 text-sm"
                           placeholder="98XXXXXXXX">
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold" style="color:var(--text-muted)">Email</label>
                    <input type="email" name="email" maxlength="200" class="input-modern mt-1 text-sm"
                           placeholder="member@example.com">
                </div>

                <div class="md:col-span-2 p-3 rounded-lg border"
                     style="border:1px solid {{ old('no_political_party_member') ? 'rgba(16,185,129,.4)' : 'var(--border-card)' }}; background:{{ old('no_political_party_member') ? 'rgba(16,185,129,.04)' : 'transparent' }}">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="no_political_party_member" value="1"
                               class="mt-1 w-5 h-5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <span class="text-sm font-semibold" style="color:var(--text-heading)">
                                No Political Party Membership (declaration)
                            </span>
                            <p class="text-xs mt-1" style="color:var(--text-muted)">
                                Tick to confirm that this SMC member has signed a written self-declaration
                                that they are <strong>not</strong> an active member of any political party
                                (per Nepal Education Regulations 2026, July amendment). Upload the signed
                                declaration form and photo ID after adding the member.
                            </p>
                        </div>
                    </label>
                </div>

                <div>
                    <label class="text-xs font-semibold" style="color:var(--text-muted)">Citizenship No.</label>
                    <input type="text" name="citizenship_no" maxlength="50" class="input-modern mt-1 text-sm"
                           placeholder="20XX-XX-XXXXXX">
                </div>
                <div>
                    <label class="text-xs font-semibold" style="color:var(--text-muted)">Voter ID No.</label>
                    <input type="text" name="voter_id_no" maxlength="50" class="input-modern mt-1 text-sm"
                           placeholder="Voter ID number">
                </div>
                <div>
                    <label class="text-xs font-semibold" style="color:var(--text-muted)">Appointed Date</label>
                    <input type="date" name="appointed_at" class="input-modern mt-1 text-sm">
                </div>
                <div>
                    <label class="text-xs font-semibold" style="color:var(--text-muted)">Term Ends</label>
                    <input type="date" name="term_ends_at" class="input-modern mt-1 text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold" style="color:var(--text-muted)">Notes</label>
                    <textarea name="notes" rows="2" maxlength="1000" class="input-modern mt-1 text-sm"
                              placeholder="Optional notes about this member..."></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('smc-add-modal').classList.add('hidden')"
                        class="btn-secondary text-sm">Cancel</button>
                <button type="submit" class="btn-primary text-sm">
                    <i class="bi bi-check-circle"></i> Add Member
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- EDIT MEMBER MODAL                                                     --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
<div id="smc-edit-modal" class="fixed inset-0 z-50 hidden items-start justify-center pt-16 bg-slate-900/60 backdrop-blur-sm"
     onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="card-modern w-full max-w-2xl mx-4 shadow-2xl" style="max-height:86vh; overflow-y:auto">
        <div class="card-header-modern flex items-center justify-between">
            <span class="card-title-modern"><i class="bi bi-pencil-square text-indigo-500"></i> Edit SMC Member</span>
            <button type="button" onclick="document.getElementById('smc-edit-modal').classList.add('hidden')"
                    class="text-slate-400 hover:text-slate-600 text-xl">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <form id="smc-edit-form" method="POST" action="" class="card-body-modern space-y-4">
            @csrf @method('PATCH')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold" style="color:var(--text-muted)">Full Name *</label>
                    <input type="text" name="full_name" id="edit_full_name" required maxlength="200" class="input-modern mt-1 text-sm">
                </div>
                <div>
                    <label class="text-xs font-semibold" style="color:var(--text-muted)">Position *</label>
                    <select name="position" id="edit_position" required class="select-modern mt-1 text-sm">
                        @foreach(App\Models\SchoolCommitteeMember::POSITIONS as $val => $lbl)
                        <option value="{{ $val }}">{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold" style="color:var(--text-muted)">Phone</label>
                    <input type="text" name="phone" id="edit_phone" maxlength="20" class="input-modern mt-1 text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold" style="color:var(--text-muted)">Email</label>
                    <input type="email" name="email" id="edit_email" maxlength="200" class="input-modern mt-1 text-sm">
                </div>

                <div class="md:col-span-2 p-3 rounded-lg border" style="border-color:var(--border-card)" id="edit_declaration_box">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="no_political_party_member" id="edit_no_political_party_member" value="1"
                               class="mt-1 w-5 h-5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <span class="text-sm font-semibold" style="color:var(--text-heading)">
                                No Political Party Membership (declaration)
                            </span>
                            <p class="text-xs mt-1" style="color:var(--text-muted)">
                                Nepal Education Regulations 2026 § SMC membership — untick to revoke declaration.
                            </p>
                        </div>
                    </label>
                </div>

                <div>
                    <label class="text-xs font-semibold" style="color:var(--text-muted)">Citizenship No.</label>
                    <input type="text" name="citizenship_no" id="edit_citizenship_no" maxlength="50" class="input-modern mt-1 text-sm">
                </div>
                <div>
                    <label class="text-xs font-semibold" style="color:var(--text-muted)">Voter ID No.</label>
                    <input type="text" name="voter_id_no" id="edit_voter_id_no" maxlength="50" class="input-modern mt-1 text-sm">
                </div>
                <div>
                    <label class="text-xs font-semibold" style="color:var(--text-muted)">Appointed Date</label>
                    <input type="date" name="appointed_at" id="edit_appointed_at" class="input-modern mt-1 text-sm">
                </div>
                <div>
                    <label class="text-xs font-semibold" style="color:var(--text-muted)">Term Ends</label>
                    <input type="date" name="term_ends_at" id="edit_term_ends_at" class="input-modern mt-1 text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold" style="color:var(--text-muted)">Notes</label>
                    <textarea name="notes" id="edit_notes" rows="2" maxlength="1000" class="input-modern mt-1 text-sm"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('smc-edit-modal').classList.add('hidden')"
                        class="btn-secondary text-sm">Cancel</button>
                <button type="submit" class="btn-primary text-sm">
                    <i class="bi bi-save"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- UPLOAD DOCUMENT MODAL                                                 --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
<div id="smc-doc-modal" class="fixed inset-0 z-50 hidden items-start justify-center pt-20 bg-slate-900/60 backdrop-blur-sm"
     onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="card-modern w-full max-w-lg mx-4 shadow-2xl">
        <div class="card-header-modern flex items-center justify-between">
            <span class="card-title-modern"><i class="bi bi-cloud-upload text-blue-500"></i> Upload Document — <span id="doc-member-name"></span></span>
            <button type="button" onclick="document.getElementById('smc-doc-modal').classList.add('hidden')"
                    class="text-slate-400 hover:text-slate-600 text-xl">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <form id="smc-doc-form" method="POST" action="" enctype="multipart/form-data" class="card-body-modern space-y-4">
            @csrf
            <div>
                <label class="text-xs font-semibold" style="color:var(--text-muted)">Document Type</label>
                <select name="collection" required class="select-modern mt-1 text-sm">
                    <option value="smc_id_proof">Photo ID (Citizenship / Voter ID / Passport)</option>
                    <option value="smc_declaration">Signed No-Political-Party Declaration Form</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold" style="color:var(--text-muted)">Files (max 5 at a time, 20MB each)</label>
                <input type="file" name="documents[]" multiple required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                       class="input-modern mt-1 text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-100 file:text-indigo-700 hover:file:bg-indigo-200">
                <p class="text-[10px] mt-1" style="color:var(--text-muted)">Accepted: PDF, JPG, PNG, DOC/DOCX. Stored encrypted; linked to this member record.</p>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('smc-doc-modal').classList.add('hidden')"
                        class="btn-secondary text-sm">Cancel</button>
                <button type="submit" class="btn-primary text-sm">
                    <i class="bi bi-cloud-upload"></i> Upload
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(member) {
    const form = document.getElementById('smc-edit-form');
    const updateUrl = "{{ route('super.schools.committee.members.update', [$school->id, '_MID_']) }}".replace('_MID_', member.id);
    form.action = updateUrl;

    document.getElementById('edit_full_name').value     = member.full_name || '';
    document.getElementById('edit_position').value      = member.position || 'member';
    document.getElementById('edit_phone').value         = member.phone || '';
    document.getElementById('edit_email').value         = member.email || '';
    document.getElementById('edit_citizenship_no').value= member.citizenship_no || '';
    document.getElementById('edit_voter_id_no').value   = member.voter_id_no || '';
    document.getElementById('edit_appointed_at').value  = member.appointed_at ? member.appointed_at.split('T')[0] : '';
    document.getElementById('edit_term_ends_at').value  = member.term_ends_at ? member.term_ends_at.split('T')[0] : '';
    document.getElementById('edit_notes').value         = member.notes || '';

    const declBox = document.getElementById('edit_declaration_box');
    const cb = document.getElementById('edit_no_political_party_member');
    cb.checked = !!member.no_political_party_member;
    declBox.style.borderColor = cb.checked ? 'rgba(16,185,129,.4)' : 'var(--border-card)';
    declBox.style.background  = cb.checked ? 'rgba(16,185,129,.04)' : 'transparent';
    cb.onchange = () => {
        declBox.style.borderColor = cb.checked ? 'rgba(16,185,129,.4)' : 'var(--border-card)';
        declBox.style.background  = cb.checked ? 'rgba(16,185,129,.04)' : 'transparent';
    };

    document.getElementById('smc-edit-modal').classList.remove('hidden');
}

function openDocModal(memberId, memberName) {
    const form = document.getElementById('smc-doc-form');
    const uploadUrl = "{{ route('super.schools.committee.document.upload', [$school->id, '_MID_']) }}".replace('_MID_', memberId);
    form.action = uploadUrl;
    document.getElementById('doc-member-name').textContent = memberName;
    document.getElementById('smc-doc-modal').classList.remove('hidden');
}
</script>
@endsection
