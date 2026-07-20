@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">
        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div><h1 class="text-2xl font-bold text-slate-800 tracking-tight">Visitor Log</h1></div>
        </div>
        @if(session('status'))<div class="mb-5 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm"><i class="bi bi-check-circle me-1"></i>{{ session('status') }}</div>@endif

        @can('manage hostel visitors')
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-6">
            <p class="text-sm font-semibold text-slate-700 mb-4">Log New Visitor</p>
            <form method="POST" action="{{ route('hostel.visitors.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                @csrf
                <div><label class="block text-xs font-medium text-slate-500 mb-1">Hostel *</label>
                    <select name="hostel_id" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400">
                        @foreach($hostels as $h)<option value="{{ $h->id }}">{{ $h->name }}</option>@endforeach
                    </select>
                </div>
                <div><label class="block text-xs font-medium text-slate-500 mb-1">Student Visited *</label>
                    <select name="student_id" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400">
                        <option value="">Select...</option>
                        @foreach($students as $s)<option value="{{ $s->id }}">{{ $s->full_name }}</option>@endforeach
                    </select>
                </div>
                <div><label class="block text-xs font-medium text-slate-500 mb-1">Visitor Name *</label><input type="text" name="visitor_name" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm"></div>
                <div><label class="block text-xs font-medium text-slate-500 mb-1">Relation *</label><input type="text" name="relation" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm"></div>
                
                <div><label class="block text-xs font-medium text-slate-500 mb-1">Date *</label><input type="date" name="date" required value="{{ date('Y-m-d') }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm"></div>
                <div><label class="block text-xs font-medium text-slate-500 mb-1">In Time *</label><input type="time" name="in_time" required value="{{ date('H:i') }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm"></div>
                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium">Log Visitor</button>
                </div>
            </form>
        </div>
        @endcan

        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Visitor</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Student</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Date & Time</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Status</th>
                        <th class="px-4 py-3 text-right font-semibold text-slate-700">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($visitors as $v)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <p class="font-medium text-slate-800">{{ $v->visitor_name }}</p>
                            <p class="text-xs text-slate-500">{{ $v->relation }}</p>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $v->student->full_name }}</td>
                        <td class="px-4 py-3">
                            <p class="text-slate-700">{{ $v->date }}</p>
                            <p class="text-xs text-slate-500">In: {{ $v->in_time }} {!! $v->out_time ? '| Out: '.$v->out_time : '' !!}</p>
                        </td>
                        <td class="px-4 py-3">
                            @if($v->out_time) <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded text-xs">Departed</span>
                            @else <span class="px-2 py-1 bg-emerald-100 text-emerald-700 rounded text-xs"><i class="bi bi-circle-fill text-[8px] me-1"></i>Inside</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if(!$v->out_time)
                            <form method="POST" action="{{ route('hostel.visitors.update', $v->id) }}" class="inline">
                                @csrf @method('PUT')
                                <input type="hidden" name="out_time" value="{{ date('H:i') }}">
                                <button class="text-xs bg-slate-800 text-white px-2 py-1 rounded">Mark Out</button>
                            </form>
                            @endif
                            <form method="POST" action="{{ route('hostel.visitors.destroy', $v->id) }}" class="inline ml-2">
                                @csrf @method('DELETE')
                                <button class="text-rose-500 hover:text-rose-700" onclick="return confirm('Delete record?')"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">No visitors logged.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection