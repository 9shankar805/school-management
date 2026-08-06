@extends('layouts.app')
@section('title', 'Hostel Attendance')

@section('content')
<style>
  .header-icon-box-hostel {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: linear-gradient(135deg, #7c3aed 0%, #9333ea 100%) !important;
    color: #ffffff !important;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(147, 51, 234, 0.3);
  }
  .header-icon-box-hostel i {
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
                <div class="header-icon-box-hostel">
                    <i class="bi bi-clipboard-check"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Hostel Night Roll Call & Attendance</h2>
                    <p class="text-slate-500 text-sm mb-0">Record daily curfew attendance, night roll call, and absent resident alerts</p>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('hostel.hostels.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-building me-1"></i> Hostels
            </a>
            <a href="{{ route('hostel.attendances.index') }}" class="btn btn-purple bg-purple-600 text-white font-bold px-3.5 py-2 rounded-xl text-xs shadow-sm">
                <i class="bi bi-clipboard-check me-1"></i> Roll Call
            </a>
            <a href="{{ route('hostel.visitors.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-person-lines me-1"></i> Visitors
            </a>
            <a href="{{ route('hostel.maintenance.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-tools me-1"></i> Maintenance
            </a>
        </div>
    </div>

    @include('session-messages')

    <!-- Filter Control Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-4">
        <form method="GET" action="{{ route('hostel.attendances.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-5">
                <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Select Hostel Building <span class="text-rose-500">*</span></label>
                <select name="hostel_id" class="form-select rounded-xl border-slate-200 py-2 text-sm font-medium text-slate-800" onchange="this.form.submit()">
                    <option value="">-- Choose Hostel --</option>
                    @foreach($hostels as $h)
                        <option value="{{ $h->id }}" @selected($hostel_id==$h->id)>{{ $h->name }} ({{ $h->type }})</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Attendance Date <span class="text-rose-500">*</span></label>
                <input type="date" name="date" class="form-control rounded-xl border-slate-200 py-2 text-sm font-medium text-slate-800" value="{{ $date }}" onchange="this.form.submit()">
            </div>

            <div class="col-12 col-md-3">
                <button type="submit" class="btn btn-purple bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-xl text-sm w-100 shadow-sm transition">
                    <i class="bi bi-funnel me-1"></i> Load Roll Call Roster
                </button>
            </div>
        </form>
    </div>

    <!-- Attendance Roll Call Form Card -->
    @if($hostel_id && count($students) > 0)
    <form method="POST" action="{{ route('hostel.attendances.store') }}">
        @csrf
        <input type="hidden" name="hostel_id" value="{{ $hostel_id }}">
        <input type="hidden" name="date" value="{{ $date }}">

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4 pb-3 border-b border-slate-100">
                <div>
                    <h5 class="font-bold text-slate-800 mb-0">
                        {{ \App\Models\Hostel::find($hostel_id)?->name }} Roll Call
                    </h5>
                    <p class="text-xs text-slate-400 mb-0">Date: {{ \Carbon\Carbon::parse($date)->format('d M Y') }} | Residents: {{ count($students) }}</p>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-emerald bg-emerald-600 text-white font-semibold text-xs py-1.5 px-3 rounded-xl shadow-sm" onclick="markAll('Present')">
                        <i class="bi bi-check-all me-1"></i> All Present
                    </button>
                    <button type="button" class="btn btn-sm btn-rose bg-rose-600 text-white font-semibold text-xs py-1.5 px-3 rounded-xl shadow-sm" onclick="markAll('Absent')">
                        <i class="bi bi-x-circle me-1"></i> All Absent
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle text-sm mb-0">
                    <thead>
                        <tr class="text-xs font-semibold text-slate-400 uppercase tracking-wider bg-slate-50">
                            <th class="py-3 px-4 rounded-s-xl" style="width: 50px;">#</th>
                            <th class="py-3 px-4">Student Resident Name</th>
                            <th class="py-3 px-4 rounded-e-xl text-end">Attendance Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($students as $i => $alloc)
                        @php $existing = $attendances->get($alloc->student_id); @endphp
                        <tr>
                            <td class="py-3 px-4 text-xs font-semibold text-slate-400">{{ $i+1 }}</td>
                            <td class="py-3 px-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-700 font-bold d-flex align-items-center justify-content-center text-xs flex-shrink-0">
                                        {{ strtoupper(substr($alloc->student->first_name ?? 'S', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800">{{ $alloc->student->first_name ?? '' }} {{ $alloc->student->last_name ?? '' }}</div>
                                        <div class="text-xs text-slate-400">ID: #{{ $alloc->student_id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-end">
                                <div class="d-inline-flex align-items-center gap-3 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200">
                                    @foreach(['Present'=>'text-emerald-700','Absent'=>'text-rose-700','Leave'=>'text-amber-700'] as $val=>$color)
                                    <label class="d-inline-flex align-items-center gap-1.5 text-xs font-bold cursor-pointer {{ $color }} mb-0">
                                        <input class="form-check-input att-radio" type="radio"
                                               name="attendance[{{ $alloc->student_id }}]"
                                               value="{{ $val }}"
                                               @checked(($existing?->status ?? 'Present') === $val)>
                                        <span>{{ $val }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <hr class="my-4 border-slate-100">

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-purple bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold px-6 py-2.5 rounded-xl shadow-md shadow-purple-500/20 transition">
                    <i class="bi bi-check2-circle me-1.5"></i> Save Night Roll Call
                </button>
            </div>
        </div>
    </form>
    @elseif($hostel_id)
    <div class="bg-white rounded-2xl border border-slate-200 p-5 text-center text-slate-400">
        <i class="bi bi-info-circle display-4 text-slate-300 mb-2 block"></i>
        <h6 class="font-bold text-slate-600 mb-1">No Active Residents Allocated</h6>
        <p class="mb-0 text-xs">There are no active students assigned to this hostel building.</p>
    </div>
    @else
    <div class="bg-white rounded-2xl border border-slate-200 p-5 text-center text-slate-400">
        <i class="bi bi-funnel display-4 text-slate-300 mb-2 block"></i>
        <h6 class="font-bold text-slate-600 mb-1">Select Hostel & Date Above</h6>
        <p class="mb-0 text-xs">Choose a hostel building from the dropdown above to load the student roll call sheet.</p>
    </div>
    @endif
</div>

@push('scripts')
<script>
function markAll(status) {
    document.querySelectorAll('.att-radio[value="' + status + '"]').forEach(r => r.checked = true);
}
</script>
@endpush
@endsection
