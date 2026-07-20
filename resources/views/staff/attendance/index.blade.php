@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Staff Attendance</h1>
                <p class="text-slate-400 text-sm mt-0.5">Mark and track daily attendance for non-teaching staff</p>
            </div>
        </div>

        @if(session('status'))
        <div class="mb-5 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm"><i class="bi bi-check-circle me-1"></i>{{ session('status') }}</div>
        @endif

        {{-- Date picker --}}
        <form method="GET" action="{{ route('staff.attendance.index') }}" class="flex gap-2 mb-6">
            <input type="date" name="date" value="{{ $date }}"
                   class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition">Load</button>
        </form>

        {{-- Summary --}}
        <div class="grid grid-cols-3 md:grid-cols-5 gap-3 mb-6">
            @foreach(\App\Models\StaffAttendance::STATUSES as $key => $label)
            @php $colours = ['present'=>'emerald','absent'=>'rose','late'=>'amber','half_day'=>'blue','on_leave'=>'violet']; @endphp
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
                <p class="text-2xl font-bold text-{{ $colours[$key] ?? 'slate' }}-600">{{ $summary[$key] ?? 0 }}</p>
                <p class="text-xs text-slate-400 mt-0.5">{{ $label }}</p>
            </div>
            @endforeach
        </div>

        {{-- Bulk mark form --}}
        @can('create staff')
        <form method="POST" action="{{ route('staff.attendance.store') }}">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 flex justify-between items-center">
                    <p class="text-sm font-semibold text-slate-700">{{ \Carbon\Carbon::parse($date)->format('l, d M Y') }}</p>
                    <button type="submit" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-medium transition">Save Attendance</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead><tr class="text-left text-xs text-slate-400 bg-slate-50">
                            <th class="px-5 py-2.5 font-medium">Staff Member</th>
                            <th class="px-4 py-2.5 font-medium">Role</th>
                            @foreach(\App\Models\StaffAttendance::STATUSES as $v => $l)
                            <th class="px-3 py-2.5 font-medium text-center">{{ $l }}</th>
                            @endforeach
                            <th class="px-3 py-2.5 font-medium">Check-in</th>
                        </tr></thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($staff as $member)
                            @php $current = $member->staffAttendance->first()?->status ?? 'present'; @endphp
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2">
                                        <img src="{{ $member->avatar }}" class="w-7 h-7 rounded-full object-cover flex-shrink-0" alt="">
                                        <span class="font-medium text-slate-700">{{ $member->full_name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-400 capitalize">{{ str_replace('-', ' ', $member->primary_role) }}</td>
                                @foreach(\App\Models\StaffAttendance::STATUSES as $v => $l)
                                <td class="px-3 py-3 text-center">
                                    <input type="radio" name="attendance[{{ $member->id }}]" value="{{ $v }}"
                                           {{ $current === $v ? 'checked' : '' }} class="accent-indigo-600">
                                </td>
                                @endforeach
                                <td class="px-3 py-3">
                                    <input type="time" name="check_in[{{ $member->id }}]"
                                           value="{{ $member->staffAttendance->first()?->check_in ? \Carbon\Carbon::parse($member->staffAttendance->first()->check_in)->format('H:i') : '' }}"
                                           class="border border-slate-200 rounded-lg px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-400">
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="px-5 py-8 text-center text-sm text-slate-400">No staff members found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
        @endcan
    </div>
</div>
@endsection
