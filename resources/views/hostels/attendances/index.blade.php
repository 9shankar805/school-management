@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight mb-7">Hostel Attendance</h1>
        
        @if(session('status'))<div class="mb-5 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm"><i class="bi bi-check-circle me-1"></i>{{ session('status') }}</div>@endif

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-6">
            <form method="GET" class="flex flex-wrap gap-4 items-end">
                <div><label class="block text-xs font-medium text-slate-500 mb-1">Date</label><input type="date" name="date" value="{{ $date }}" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400"></div>
                <div><label class="block text-xs font-medium text-slate-500 mb-1">Hostel</label>
                    <select name="hostel_id" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400">
                        <option value="">-- Select Hostel --</option>
                        @foreach($hostels as $h)<option value="{{ $h->id }}" {{ $hostel_id == $h->id ? 'selected' : '' }}>{{ $h->name }}</option>@endforeach
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-lg text-sm font-medium">Filter</button>
            </form>
        </div>

        @if($hostel_id && count($students) > 0)
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm p-5">
            <form method="POST" action="{{ route('hostel.attendances.store') }}">
                @csrf
                <input type="hidden" name="date" value="{{ $date }}">
                <input type="hidden" name="hostel_id" value="{{ $hostel_id }}">
                <table class="min-w-full divide-y divide-slate-200 text-sm mb-6">
                    <thead><tr>
                        <th class="px-4 py-2 text-left font-semibold text-slate-700">Student</th>
                        <th class="px-4 py-2 text-center font-semibold text-slate-700">Present</th>
                        <th class="px-4 py-2 text-center font-semibold text-slate-700">Absent</th>
                        <th class="px-4 py-2 text-center font-semibold text-slate-700">Late</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($students as $alloc)
                        @php $currentStatus = $attendances->get($alloc->student_id)?->status ?? 'Present'; @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">{{ $alloc->student->full_name }}</td>
                            <td class="px-4 py-3 text-center"><input type="radio" name="attendance[{{ $alloc->student_id }}]" value="Present" {{ $currentStatus=='Present'?'checked':'' }} class="text-emerald-600 focus:ring-emerald-500"></td>
                            <td class="px-4 py-3 text-center"><input type="radio" name="attendance[{{ $alloc->student_id }}]" value="Absent" {{ $currentStatus=='Absent'?'checked':'' }} class="text-rose-600 focus:ring-rose-500"></td>
                            <td class="px-4 py-3 text-center"><input type="radio" name="attendance[{{ $alloc->student_id }}]" value="Late" {{ $currentStatus=='Late'?'checked':'' }} class="text-amber-500 focus:ring-amber-500"></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg font-medium shadow-sm hover:bg-indigo-700">Save Attendance</button>
            </form>
        </div>
        @elseif($hostel_id)
        <div class="bg-white p-8 rounded-xl border border-slate-200 text-center text-slate-500">No active allocations found in this hostel.</div>
        @else
        <div class="bg-white p-8 rounded-xl border border-slate-200 text-center text-slate-400">Please select a hostel to mark attendance.</div>
        @endif
    </div>
</div>
@endsection