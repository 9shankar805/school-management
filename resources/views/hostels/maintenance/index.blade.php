@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight mb-7">Maintenance Requests</h1>
        @if(session('status'))<div class="mb-5 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm"><i class="bi bi-check-circle me-1"></i>{{ session('status') }}</div>@endif

        @can('manage hostel maintenance')
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-6">
            <p class="text-sm font-semibold text-slate-700 mb-4">New Request</p>
            <form method="POST" action="{{ route('hostel.maintenance.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                @csrf
                <div><label class="block text-xs font-medium text-slate-500 mb-1">Room *</label>
                    <select name="hostel_room_id" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
                        @foreach(\App\Models\HostelRoom::with('hostel')->get() as $r)<option value="{{ $r->id }}">{{ $r->hostel->name }} - {{ $r->room_number }}</option>@endforeach
                    </select>
                    <!-- controller workaround again -->
                    <input type="hidden" name="hostel_id" value="1">
                </div>
                <div><label class="block text-xs font-medium text-slate-500 mb-1">Issue Type *</label>
                    <select name="issue_type" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
                        <option value="Plumbing">Plumbing</option><option value="Electrical">Electrical</option><option value="Carpentry">Carpentry</option><option value="Other">Other</option>
                    </select>
                </div>
                <div><label class="block text-xs font-medium text-slate-500 mb-1">Priority *</label>
                    <select name="priority" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
                        <option value="Low">Low</option><option value="Medium">Medium</option><option value="High">High</option>
                    </select>
                </div>
                <div class="md:col-span-3"><label class="block text-xs font-medium text-slate-500 mb-1">Description *</label><input type="text" name="description" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm"></div>
                <div><button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium">Submit Request</button></div>
            </form>
        </div>
        @endcan

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @forelse($requests as $r)
            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
                <div class="flex justify-between items-start mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider px-2 py-1 rounded-full {{ $r->priority=='High' ? 'bg-rose-100 text-rose-700' : ($r->priority=='Medium' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">{{ $r->priority }}</span>
                    <span class="text-xs text-slate-500">{{ $r->created_at->diffForHumans() }}</span>
                </div>
                <h4 class="font-bold text-slate-800">{{ $r->issue_type }} in {{ $r->room->hostel->name }} - {{ $r->room->room_number }}</h4>
                <p class="text-sm text-slate-600 mt-2 mb-4">{{ $r->description }}</p>
                
                <div class="flex items-center justify-between border-t border-slate-100 pt-3">
                    <span class="text-xs font-medium {{ $r->status=='Pending'?'text-rose-500':($r->status=='In Progress'?'text-amber-500':'text-emerald-500') }}"><i class="bi bi-circle-fill me-1 text-[8px]"></i>{{ $r->status }}</span>
                    
                    @can('manage hostel maintenance')
                    <form method="POST" action="{{ route('hostel.maintenance.update', $r->id) }}" class="flex gap-2">
                        @csrf @method('PUT')
                        <select name="status" class="text-xs border border-slate-200 rounded px-2 py-1">
                            <option value="Pending" {{ $r->status=='Pending'?'selected':'' }}>Pending</option>
                            <option value="In Progress" {{ $r->status=='In Progress'?'selected':'' }}>In Progress</option>
                            <option value="Resolved" {{ $r->status=='Resolved'?'selected':'' }}>Resolved</option>
                        </select>
                        <button class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-2 py-1 rounded text-xs transition">Update</button>
                    </form>
                    @endcan
                </div>
            </div>
            @empty
            <div class="col-span-full py-12 text-center text-slate-400 bg-white border border-slate-100 rounded-xl">No maintenance requests.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection