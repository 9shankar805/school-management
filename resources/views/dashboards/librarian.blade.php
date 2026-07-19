@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">
        @include('layouts.left-menu')
    </div>

    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Library Dashboard</h1>
                <p class="text-slate-400 text-sm mt-0.5">{{ now()->format('l, F j, Y') }}</p>
            </div>
            <div class="flex gap-2">
                @can('create books')
                <a href="{{ route('library.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                    <i class="bi bi-plus-lg"></i> Add Book
                </a>
                @endcan
                <a href="{{ route('library.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 text-sm font-medium rounded-lg hover:bg-slate-50 transition text-slate-700">
                    <i class="bi bi-journals"></i> View Catalog
                </a>
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Total Books</p>
                    <span class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 text-sm"><i class="bi bi-journals"></i></span>
                </div>
                <p class="text-3xl font-bold text-slate-800">{{ number_format($totalBooks) }}</p>
                <p class="mt-1 text-xs text-indigo-600">In catalog</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Issued</p>
                    <span class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600 text-sm"><i class="bi bi-book"></i></span>
                </div>
                <p class="text-3xl font-bold text-amber-600">{{ number_format($issuedBooks) }}</p>
                <p class="mt-1 text-xs text-slate-400">Currently borrowed</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-rose-50 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-rose-400 uppercase tracking-wide">Overdue</p>
                    <span class="w-8 h-8 rounded-lg bg-rose-50 flex items-center justify-center text-rose-600 text-sm"><i class="bi bi-exclamation-circle"></i></span>
                </div>
                <p class="text-3xl font-bold text-rose-600">{{ number_format($overdueBooks) }}</p>
                <p class="mt-1 text-xs text-rose-500">Past due date</p>
            </div>
        </div>

        {{-- Book issue module coming soon notice --}}
        @if($issuedBooks === 0 && $overdueBooks === 0)
        <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 mb-6 flex items-start gap-3">
            <i class="bi bi-info-circle-fill text-blue-500 text-lg mt-0.5"></i>
            <div>
                <p class="font-semibold text-blue-800 text-sm">Book Issue/Return Module</p>
                <p class="text-blue-700 text-xs mt-0.5">Issue and return tracking will be available when Module 12 (Library) is fully implemented.</p>
            </div>
        </div>
        @endif

        {{-- Recent Books --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 flex justify-between items-center">
                <p class="text-sm font-semibold text-slate-700"><i class="bi bi-clock-history me-1 text-slate-400"></i>Recently Added Books</p>
                <a href="{{ route('library.index') }}" class="text-xs text-indigo-600 hover:underline">View all</a>
            </div>
            @if($recentBooks->count())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-slate-400 bg-slate-50">
                            <th class="px-5 py-2.5 font-medium">Title</th>
                            <th class="px-5 py-2.5 font-medium">Author</th>
                            <th class="px-5 py-2.5 font-medium">ISBN</th>
                            <th class="px-5 py-2.5 font-medium">Qty</th>
                            <th class="px-5 py-2.5 font-medium">Added</th>
                            @can('create books')
                            <th class="px-5 py-2.5 font-medium"></th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($recentBooks as $book)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-medium text-slate-700">{{ $book->title }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $book->author }}</td>
                            <td class="px-5 py-3 text-slate-400 font-mono text-xs">{{ $book->isbn ?? '—' }}</td>
                            <td class="px-5 py-3 text-slate-700">{{ $book->qty ?? '—' }}</td>
                            <td class="px-5 py-3 text-slate-400">{{ $book->created_at->format('M d, Y') }}</td>
                            @can('create books')
                            <td class="px-5 py-3">
                                <a href="{{ route('library.edit', $book->id) }}" class="text-xs text-indigo-600 hover:underline">Edit</a>
                            </td>
                            @endcan
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-sm text-slate-400 text-center py-10">No books in the catalog yet.</p>
            @endif
        </div>

    </div>
</div>
@endsection
