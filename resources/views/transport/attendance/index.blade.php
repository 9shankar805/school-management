@extends('layouts.app')
@section('title', 'Bus Attendance Register')

@section('content')
<style>
  .header-icon-box-transport {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%) !important;
    color: #ffffff !important;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
  }
  .header-icon-box-transport i {
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
                <div class="header-icon-box-transport">
                    <i class="bi bi-clipboard-check-fill"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Bus Attendance Register</h2>
                    <p class="text-slate-500 text-sm mb-0">Record morning pickup and afternoon drop-off bus attendance for subscribed students</p>
                </div>
            </div>
        </div>

        <!-- Navigation Bar -->
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('transport.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-speedometer2 me-1"></i> Overview
            </a>
            <a href="{{ route('transport.routes.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-map me-1"></i> Routes
            </a>
            <a href="{{ route('transport.attendance.index') }}" class="btn btn-amber bg-amber-600 text-white font-bold px-3.5 py-2 rounded-xl text-xs shadow-sm">
                <i class="bi bi-clipboard-check me-1"></i> Attendance
            </a>
        </div>
    </div>

    @include('session-messages')

    <!-- Filter Control Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-4">
        <form method="GET" action="{{ route('transport.attendance.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Select Transport Route <span class="text-rose-500">*</span></label>
                <select name="route_id" class="form-select rounded-xl border-slate-200 py-2 text-sm font-medium text-slate-800" required onchange="this.form.submit()">
                    <option value="">-- Choose Route --</option>
                    @foreach($routes as $r)
                        <option value="{{ $r->id }}" @selected($routeId == $r->id)>{{ $r->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-3">
                <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Attendance Date <span class="text-rose-500">*</span></label>
                <input type="date" name="date" class="form-control rounded-xl border-slate-200 py-2 text-sm font-medium text-slate-800" value="{{ $date }}" onchange="this.form.submit()">
            </div>

            <div class="col-12 col-md-3">
                <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Trip Type <span class="text-rose-500">*</span></label>
                <select name="trip" class="form-select rounded-xl border-slate-200 py-2 text-sm font-medium text-slate-800" onchange="this.form.submit()">
                    <option value="morning" @selected($trip==='morning')>Morning (Pickup Roster)</option>
                    <option value="afternoon" @selected($trip==='afternoon')>Afternoon (Drop-off Roster)</option>
                </select>
            </div>

            <div class="col-12 col-md-2">
                <button type="submit" class="btn btn-amber bg-amber-600 hover:bg-amber-700 text-white font-bold py-2 px-4 rounded-xl text-sm w-100 shadow-sm transition">
                    <i class="bi bi-funnel me-1"></i> Load Roster
                </button>
            </div>
        </form>
    </div>

    <!-- Bus Attendance Form Roster -->
    @if($route)
    <form action="{{ route('transport.attendance.store') }}" method="POST">
        @csrf
        <input type="hidden" name="route_id" value="{{ $routeId }}">
        <input type="hidden" name="date" value="{{ $date }}">
        <input type="hidden" name="trip" value="{{ $trip }}">

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4 pb-3 border-b border-slate-100">
                <div>
                    <h5 class="font-bold text-slate-800 mb-0">
                        {{ $route->name }} ({{ ucfirst($trip) }})
                    </h5>
                    <p class="text-xs text-slate-400 mb-0">Date: {{ \Carbon\Carbon::parse($date)->format('d M Y') }} | Passengers: {{ $route->activeStudents->count() }}</p>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-emerald bg-emerald-600 text-white font-semibold text-xs py-1.5 px-3 rounded-xl shadow-sm" onclick="markAll('present')">
                        <i class="bi bi-check-all me-1"></i> All Present
                    </button>
                    <button type="button" class="btn btn-sm btn-rose bg-rose-600 text-white font-semibold text-xs py-1.5 px-3 rounded-xl shadow-sm" onclick="markAll('absent')">
                        <i class="bi bi-x-circle me-1"></i> All Absent
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle text-sm mb-0">
                    <thead>
                        <tr class="text-xs font-semibold text-slate-400 uppercase tracking-wider bg-slate-50">
                            <th class="py-3 px-4 rounded-s-xl" style="width: 50px;">#</th>
                            <th class="py-3 px-4">Student Passenger</th>
                            <th class="py-3 px-4">Designated Bus Stop</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 rounded-e-xl">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($route->activeStudents as $i => $alloc)
                        @php
                            $existing = $records->get($alloc->student_id);
                            $status = $existing?->status ?? 'present';
                        @endphp
                        <tr>
                            <td class="py-3 px-4 text-xs font-semibold text-slate-400">{{ $i + 1 }}</td>
                            <td class="py-3 px-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-700 font-bold d-flex align-items-center justify-content-center text-xs flex-shrink-0">
                                        {{ strtoupper(substr($alloc->student->first_name ?? 'S', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800">{{ $alloc->student->first_name ?? '' }} {{ $alloc->student->last_name ?? '' }}</div>
                                        <div class="text-xs text-slate-400">ID: #{{ $alloc->student_id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-xs font-semibold text-slate-700">
                                <i class="bi bi-geo-alt text-amber-600 me-1"></i> {{ $alloc->stop?->name ?? 'Default Stop' }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <input type="hidden" name="attendance[{{ $i }}][student_id]" value="{{ $alloc->student_id }}">
                                <div class="d-inline-flex align-items-center gap-3 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200">
                                    @foreach(['present'=>['text-emerald-700','Present'],'absent'=>['text-rose-700','Absent'],'late'=>['text-amber-700','Late']] as $val=>[$color,$label])
                                    <label class="d-inline-flex align-items-center gap-1.5 text-xs font-bold cursor-pointer {{ $color }} mb-0">
                                        <input class="form-check-input att-radio" type="radio"
                                               name="attendance[{{ $i }}][status]"
                                               id="att_{{ $i }}_{{ $val }}"
                                               value="{{ $val }}"
                                               @checked($status === $val)>
                                        <span>{{ $label }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <input type="text" name="attendance[{{ $i }}][remarks]" class="form-control rounded-xl border-slate-200 py-1 px-2.5 text-xs text-slate-800" value="{{ $existing?->remarks }}" placeholder="Optional notes...">
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-slate-400 py-5">No active student passengers assigned to this route.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($route->activeStudents->isNotEmpty())
            <hr class="my-4 border-slate-100">

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-amber bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white font-bold px-6 py-2.5 rounded-xl shadow-md shadow-amber-500/20 transition">
                    <i class="bi bi-check2-circle me-1.5"></i> Save Bus Attendance
                </button>
            </div>
            @endif
        </div>
    </form>
    @else
    <div class="bg-white rounded-2xl border border-slate-200 p-5 text-center text-slate-400">
        <i class="bi bi-bus-front display-4 text-slate-300 mb-2 block"></i>
        <h6 class="font-bold text-slate-600 mb-1">Select Transport Route Above</h6>
        <p class="mb-0 text-xs">Choose a route from the dropdown menu above to load the student passenger roster.</p>
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
