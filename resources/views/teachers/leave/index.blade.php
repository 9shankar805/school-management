@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div><h1 class="text-2xl font-bold text-slate-800 tracking-tight">Leave Management</h1></div>
            <a href="{{ route('leave.types') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 text-sm font-medium rounded-lg hover:bg-slate-50 transition text-slate-700">
                <i class="bi bi-gear"></i> Manage Types
            </a>
        </div>

        @if(session('status'))
        <div class="mb-5 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm"><i class="bi bi-check-circle me-1"></i>{{ session('status') }}</div>
        @endif

        {{-- Status tabs --}}
        <div class="flex gap-1 bg-white rounded-xl border border-slate-100 shadow-sm p-1 mb-6 overflow-x-auto">
            @foreach(['pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected','all'=>'All'] as $s=>$l)
            <a href="{{ route('leave.index',['status'=>$s,'search'=>request('search')]) }}"
               class="flex-shrink-0 px-4 py-2 rounded-lg text-xs font-medium transition whitespace-nowrap {{ $status===$s ? 'bg-indigo-50 text-indigo-700' : 'text-slate-500 hover:bg-slate-50' }}">
                {{ $l }} @if(isset($counts[$s]))<span class="ml-1 text-[10px] bg-slate-200 px-1.5 py-0.5 rounded-full">{{ $counts[$s] }}</span>@endif
            </a>
            @endforeach
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            @if($applications->count())
            <div class="overflow-x-auto">
                <table class="w-full text-sm"><thead><tr class="text-left text-xs text-slate-400 bg-slate-50">
                    <th class="px-5 py-3 font-medium">Applicant</th><th class="px-5 py-3 font-medium">Type</th>
                    <th class="px-5 py-3 font-medium">Period</th><th class="px-5 py-3 font-medium">Days</th>
                    <th class="px-5 py-3 font-medium">Status</th><th class="px-5 py-3 font-medium">Action</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($applications as $app)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <img src="{{ $app->user->avatar }}" class="w-7 h-7 rounded-full object-cover flex-shrink-0" alt="">
                                <span class="font-medium text-slate-700">{{ $app->user->full_name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-slate-600">{{ $app->leaveType->name }}</td>
                        <td class="px-5 py-3 text-slate-500">{{ $app->from_date->format('d M') }} – {{ $app->to_date->format('d M Y') }}</td>
                        <td class="px-5 py-3 font-medium text-slate-700">{{ $app->total_days }}</td>
                        <td class="px-5 py-3"><span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $app->status_badge }}">{{ ucfirst($app->status) }}</span></td>
                        <td class="px-5 py-3">
                            @if($app->status === 'pending')
                            @can('create teachers')
                            <div class="flex gap-1">
                                <form method="POST" action="{{ route('leave.review',$app->id) }}">@csrf<input type="hidden" name="status" value="approved"><button class="text-xs px-2 py-1 bg-emerald-100 text-emerald-700 rounded-lg hover:bg-emerald-200 transition">Approve</button></form>
                                <form method="POST" action="{{ route('leave.review',$app->id) }}">@csrf<input type="hidden" name="status" value="rejected"><button class="text-xs px-2 py-1 bg-rose-100 text-rose-700 rounded-lg hover:bg-rose-200 transition">Reject</button></form>
                            </div>
                            @endcan
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody></table>
            </div>
            <div class="px-5 py-3 border-t border-slate-50">{{ $applications->links() }}</div>
            @else
            <p class="text-sm text-slate-400 text-center py-12">No applications found.</p>
            @endif
        </div>
    </div>
</div>
@endsection
