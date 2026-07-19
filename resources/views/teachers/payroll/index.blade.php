@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div><h1 class="text-2xl font-bold text-slate-800 tracking-tight">Teacher Payroll</h1></div>
        </div>

        @if(session('status'))
        <div class="mb-5 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm"><i class="bi bi-check-circle me-1"></i>{{ session('status') }}</div>
        @endif

        {{-- Month/Year filter --}}
        <form method="GET" action="{{ route('teacher.payroll.index') }}" class="flex gap-2 mb-6">
            <select name="month" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                @for($m=1;$m<=12;$m++)<option value="{{ $m }}" {{ $month==$m ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>@endfor
            </select>
            <input type="number" name="year" value="{{ $year }}" min="2020" max="2100" class="w-24 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition">Load</button>
        </form>

        {{-- KPI --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center"><p class="text-2xl font-bold text-slate-800">{{ $payrolls->count() }}</p><p class="text-xs text-slate-400">Slips Generated</p></div>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center"><p class="text-2xl font-bold text-emerald-600">${{ number_format($totalPaid) }}</p><p class="text-xs text-slate-400">Total Paid</p></div>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center"><p class="text-2xl font-bold text-amber-600">{{ $payrolls->where('status','draft')->count() }}</p><p class="text-xs text-slate-400">Pending</p></div>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center"><p class="text-2xl font-bold text-indigo-600">{{ $teachers->count() }}</p><p class="text-xs text-slate-400">Total Teachers</p></div>
        </div>

        {{-- Generate payroll form --}}
        @can('create teachers')
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-6">
            <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-plus-lg me-1 text-indigo-500"></i>Generate / Update Payroll</p>
            <form method="POST" action="{{ route('teacher.payroll.store') }}" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @csrf
                <div><label class="block text-xs font-medium text-slate-500 mb-1">Teacher *</label>
                <select name="teacher_id" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    @foreach($teachers as $t)<option value="{{ $t->id }}">{{ $t->full_name }}</option>@endforeach
                </select></div>
                <div><label class="block text-xs font-medium text-slate-500 mb-1">Month *</label>
                <select name="month" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    @for($m=1;$m<=12;$m++)<option value="{{ $m }}" {{ $month==$m ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>@endfor
                </select></div>
                <div><label class="block text-xs font-medium text-slate-500 mb-1">Year *</label><input type="number" name="year" value="{{ $year }}" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                <div><label class="block text-xs font-medium text-slate-500 mb-1">Basic Salary *</label><input type="number" name="basic_salary" step="0.01" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                <div><label class="block text-xs font-medium text-slate-500 mb-1">Allowances</label><input type="number" name="allowances" step="0.01" value="0" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                <div><label class="block text-xs font-medium text-slate-500 mb-1">Overtime</label><input type="number" name="overtime" step="0.01" value="0" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                <div><label class="block text-xs font-medium text-slate-500 mb-1">Tax Deduction</label><input type="number" name="tax_deduction" step="0.01" value="0" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                <div><label class="block text-xs font-medium text-slate-500 mb-1">Other Deductions</label><input type="number" name="other_deductions" step="0.01" value="0" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                <div class="md:col-span-2 flex items-end"><button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">Save Payroll</button></div>
            </form>
        </div>
        @endcan

        {{-- Payroll table --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 text-sm font-semibold text-slate-700">
                {{ \Carbon\Carbon::create()->month($month)->format('F') }} {{ $year }} — Payrolls
            </div>
            @if($payrolls->count())
            <div class="overflow-x-auto">
                <table class="w-full text-sm"><thead><tr class="text-left text-xs text-slate-400 bg-slate-50">
                    <th class="px-5 py-3 font-medium">Teacher</th><th class="px-5 py-3 font-medium">Basic</th>
                    <th class="px-5 py-3 font-medium">Gross</th><th class="px-5 py-3 font-medium">Deductions</th>
                    <th class="px-5 py-3 font-medium">Net</th><th class="px-5 py-3 font-medium">Attendance</th>
                    <th class="px-5 py-3 font-medium">Status</th><th class="px-5 py-3 font-medium">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($payrolls as $p)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <img src="{{ $p->teacher->avatar }}" class="w-7 h-7 rounded-full object-cover flex-shrink-0" alt="">
                                <a href="{{ route('teacher.profile.show', $p->teacher_id) }}" class="font-medium text-slate-700 hover:text-indigo-600">{{ $p->teacher->full_name }}</a>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-slate-600">${{ number_format($p->basic_salary) }}</td>
                        <td class="px-5 py-3 text-slate-600">${{ number_format($p->gross_salary) }}</td>
                        <td class="px-5 py-3 text-rose-500">-${{ number_format($p->tax_deduction + $p->other_deductions) }}</td>
                        <td class="px-5 py-3 font-bold text-emerald-600">${{ number_format($p->net_salary) }}</td>
                        <td class="px-5 py-3 text-xs text-slate-500">{{ $p->present_days }}/{{ $p->working_days }} days</td>
                        <td class="px-5 py-3"><span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $p->status_badge }}">{{ ucfirst($p->status) }}</span></td>
                        <td class="px-5 py-3">
                            <div class="flex gap-2">
                                <a href="{{ route('teacher.payroll.slip', $p->id) }}" target="_blank" class="text-xs text-indigo-600 hover:underline">Slip</a>
                                @if($p->status === 'draft')
                                @can('create teachers')
                                <form method="POST" action="{{ route('teacher.payroll.paid', $p->id) }}">@csrf<button class="text-xs text-emerald-600 hover:underline">Mark Paid</button></form>
                                @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody></table>
            </div>
            @else<p class="text-sm text-slate-400 text-center py-12">No payrolls for this period.</p>@endif
        </div>
    </div>
</div>
@endsection
