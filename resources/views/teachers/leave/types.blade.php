@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto max-w-4xl">
        <nav class="text-xs text-slate-400 mb-5"><a href="{{ route('leave.index') }}" class="hover:text-indigo-600">Leave</a><span class="mx-1">/</span>Types</nav>
        <h1 class="text-2xl font-bold text-slate-800 mb-7 tracking-tight">Leave Types</h1>
        @if(session('status'))<div class="mb-5 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm">{{ session('status') }}</div>@endif

        {{-- Create form --}}
        @can('create teachers')
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-6">
            <p class="text-sm font-semibold text-slate-700 mb-4">Add Leave Type</p>
            <form method="POST" action="{{ route('leave.types.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @csrf
                <div><label class="block text-xs font-medium text-slate-500 mb-1">Name *</label><input type="text" name="name" required placeholder="e.g. Annual Leave" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                <div><label class="block text-xs font-medium text-slate-500 mb-1">Code</label><input type="text" name="code" placeholder="e.g. AL" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                <div><label class="block text-xs font-medium text-slate-500 mb-1">Days Allowed / Year *</label><input type="number" name="days_allowed" required min="0" value="0" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                <div class="flex gap-4 items-center col-span-2">
                    <label class="flex items-center gap-2 text-sm cursor-pointer"><input type="checkbox" name="is_paid" value="1" checked class="rounded"> Paid Leave</label>
                    <label class="flex items-center gap-2 text-sm cursor-pointer"><input type="checkbox" name="carry_forward" value="1" class="rounded"> Carry Forward</label>
                </div>
                <div class="flex items-end"><button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">Create</button></div>
            </form>
        </div>
        @endcan

        {{-- Types list --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            @if($types->count())
            <div class="overflow-x-auto">
                <table class="w-full text-sm"><thead><tr class="text-left text-xs text-slate-400 bg-slate-50">
                    <th class="px-5 py-3 font-medium">Name</th><th class="px-5 py-3 font-medium">Code</th>
                    <th class="px-5 py-3 font-medium">Days</th><th class="px-5 py-3 font-medium">Paid</th>
                    <th class="px-5 py-3 font-medium">Carry Forward</th><th class="px-5 py-3 font-medium">Applications</th>
                    @can('create teachers')<th class="px-5 py-3 font-medium"></th>@endcan
                </tr></thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($types as $t)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 font-medium text-slate-700">{{ $t->name }}</td>
                        <td class="px-5 py-3 font-mono text-slate-500">{{ $t->code ?? '—' }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $t->days_allowed }}</td>
                        <td class="px-5 py-3"><span class="text-xs {{ $t->is_paid ? 'text-emerald-600' : 'text-slate-400' }}">{{ $t->is_paid ? 'Yes' : 'No' }}</span></td>
                        <td class="px-5 py-3"><span class="text-xs {{ $t->carry_forward ? 'text-blue-600' : 'text-slate-400' }}">{{ $t->carry_forward ? 'Yes' : 'No' }}</span></td>
                        <td class="px-5 py-3 text-slate-500">{{ $t->applications_count }}</td>
                        @can('create teachers')
                        <td class="px-5 py-3">
                            <form method="POST" action="{{ route('leave.types.destroy', $t->id) }}">@csrf @method('DELETE')<button class="text-xs text-rose-500 hover:text-rose-700" onclick="return confirm('Delete?')">Delete</button></form>
                        </td>
                        @endcan
                    </tr>
                    @endforeach
                </tbody></table>
            </div>
            @else<p class="text-sm text-slate-400 text-center py-10">No leave types defined.</p>@endif
        </div>
    </div>
</div>
@endsection
