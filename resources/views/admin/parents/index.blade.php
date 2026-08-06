@extends('layouts.app')

@section('content')
<div class="flex-1 p-6 lg:p-8 overflow-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-800">Manage Parents</h1>
            <p class="text-sm text-slate-500 mt-1">View and manage parent accounts and their credentials.</p>
        </div>
        <a href="{{ route('parents.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors flex items-center gap-2 shadow-sm">
            <i class="bi bi-person-plus-fill"></i> Add Parent
        </a>
    </div>

    @include('session-messages')

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        
        {{-- Search Bar --}}
        <div class="p-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
            <form action="{{ route('parents.index') }}" method="GET" class="w-full max-w-md relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email..." class="w-full pl-10 pr-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                <i class="bi bi-search absolute left-3 top-2.5 text-slate-400"></i>
            </form>
            @if(request('search'))
                <a href="{{ route('parents.index') }}" class="text-sm text-slate-500 hover:text-rose-500 font-semibold transition-colors">Clear Search</a>
            @endif
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider">
                        <th class="p-4 font-bold border-b border-slate-200">Parent Details</th>
                        <th class="p-4 font-bold border-b border-slate-200">Contact Info</th>
                        <th class="p-4 font-bold border-b border-slate-200">Linked Children</th>
                        <th class="p-4 font-bold border-b border-slate-200 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-slate-700 divide-y divide-slate-100">
                    @forelse($parents as $parent)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-4 align-top">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-lg shrink-0">
                                        {{ substr($parent->first_name, 0, 1) }}{{ substr($parent->last_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800">{{ $parent->full_name }}</p>
                                        <p class="text-xs text-slate-500">ID: {{ $parent->id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 align-top">
                                <p class="flex items-center gap-2 mb-1"><i class="bi bi-envelope text-slate-400"></i> {{ $parent->email }}</p>
                                <p class="flex items-center gap-2 text-slate-500"><i class="bi bi-telephone text-slate-400"></i> {{ $parent->phone ?? 'N/A' }}</p>
                            </td>
                            <td class="p-4 align-top">
                                <div class="flex flex-col gap-1">
                                    @forelse($parent->children as $child)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                            <i class="bi bi-person-check-fill"></i> {{ $child->full_name }}
                                        </span>
                                    @empty
                                        <span class="text-slate-400 italic text-xs">No children linked</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="p-4 align-top text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('parents.edit', $parent->id) }}" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Edit & Credentials">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('parents.destroy', $parent->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this parent account? This will not delete the linked students.');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Delete">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center text-slate-500">
                                No parents found. Add one to get started.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        @if($parents->hasPages())
        <div class="p-4 border-t border-slate-100 bg-slate-50">
            {{ $parents->links() }}
        </div>
        @endif

    </div>
</div>
@endsection
