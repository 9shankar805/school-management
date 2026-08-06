@extends('layouts.app')
@section('content')
<div class="px-4 lg:px-8 py-6 max-w-7xl mx-auto">

    {{-- Page Header --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <span class="w-10 h-10 rounded-2xl bg-indigo-600/10 text-indigo-600 flex items-center justify-center font-bold text-xl shadow-xs">
                    <i class="bi bi-journals"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Library — Book Catalog</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Browse and manage library book collection</p>
                </div>
            </div>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800/80 px-3 py-1.5 rounded-xl">
                <li><a href="{{ route('home') }}" class="hover:text-indigo-600 transition">Home</a></li>
                <li class="text-slate-300 dark:text-slate-600">/</li>
                <li class="font-semibold text-slate-800 dark:text-slate-200">Library</li>
            </ol>
        </nav>
    </div>

    @include('session-messages')

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-4">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center">
                    <i class="bi bi-journals text-lg"></i>
                </span>
                <div>
                    <div class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ $totalBooks }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">Total Titles</div>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-4">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center">
                    <i class="bi bi-check-circle text-lg"></i>
                </span>
                <div>
                    <div class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ $available }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">Available</div>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-4">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center">
                    <i class="bi bi-arrow-left-right text-lg"></i>
                </span>
                <div>
                    <div class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ $issued }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">On Loan</div>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-4">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-600 flex items-center justify-center">
                    <i class="bi bi-exclamation-circle text-lg"></i>
                </span>
                <div>
                    <div class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ $overdue }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">Overdue</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Actions + Filters --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6 mb-6">
        <div class="flex flex-wrap gap-3 mb-4 items-center">
            @can('create books')
            <a href="{{ route('library.create') }}" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md transition flex items-center gap-2">
                <i class="bi bi-plus-circle"></i> Add Book
            </a>
            @endcan
            @canany(['create books', 'issue books', 'manage library members'])
            <a href="{{ route('library.categories.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold transition flex items-center gap-2">
                <i class="bi bi-tags"></i> Categories
            </a>
            <a href="{{ route('library.analytics') }}" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold transition flex items-center gap-2">
                <i class="bi bi-bar-chart-line"></i> Analytics
            </a>
            <a href="{{ route('library.reports.form') }}" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold transition flex items-center gap-2">
                <i class="bi bi-file-earmark-spreadsheet"></i> Reports
            </a>
            @endcanany
        </div>

        <form method="GET" action="{{ route('library.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <input type="text" name="search" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none"
                       placeholder="Search title, author, ISBN, barcode…"
                       value="{{ request('search') }}">
            </div>
            <div>
                <select name="category_id" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="availability" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none">
                    <option value="">All</option>
                    <option value="available"   @selected(request('availability') === 'available')>Available</option>
                    <option value="unavailable" @selected(request('availability') === 'unavailable')>Out of Stock</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button class="px-4 py-2.5 rounded-xl bg-slate-800 dark:bg-slate-700 hover:bg-slate-700 dark:hover:bg-slate-600 text-white text-xs font-semibold transition flex items-center gap-2" type="submit">
                    <i class="bi bi-search"></i> Filter
                </button>
                @if(request()->hasAny(['search','category_id','availability']))
                <a href="{{ route('library.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold transition">Clear</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Books Table --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-semibold bg-slate-50/50 dark:bg-slate-800/20">
                    <tr>
                        <th class="py-3 px-4 w-10">#</th>
                        <th class="py-3 px-4">Title / Author</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4">ISBN / Barcode</th>
                        <th class="py-3 px-4">Location</th>
                        <th class="py-3 px-4">Stock</th>
                        <th class="py-3 px-4">Availability</th>
                        <th class="py-3 px-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($books as $book)
                    <tr class="border-b border-slate-50 dark:border-slate-800/50 hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                        <td class="py-3 px-4 text-slate-400">{{ $books->firstItem() + $loop->index }}</td>
                        <td class="py-3 px-4">
                            <div class="font-semibold text-slate-800 dark:text-slate-100">
                                <a href="{{ route('library.show', $book->id) }}" class="hover:text-indigo-600 transition">
                                    {{ $book->title }}
                                </a>
                            </div>
                            <div class="text-slate-500 dark:text-slate-400">{{ $book->author }}</div>
                        </td>
                        <td class="py-3 px-4">
                            @if($book->category)
                                <span class="px-2 py-1 rounded-lg text-xs font-semibold" style="background-color:{{ $book->category->color }}20; color:{{ $book->category->color }}">{{ $book->category->name }}</span>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            @if($book->isbn)<div>ISBN: {{ $book->isbn }}</div>@endif
                            @if($book->barcode)<div class="text-slate-400">{{ $book->barcode }}</div>@endif
                        </td>
                        <td class="py-3 px-4 text-slate-400">{{ $book->shelf_location ?? '—' }}</td>
                        <td class="py-3 px-4 text-center">{{ $book->available_qty }}/{{ $book->qty }}</td>
                        <td class="py-3 px-4">{!! $book->availability_badge !!}</td>
                        <td class="py-3 px-4">
                            <div class="flex gap-1">
                                <a href="{{ route('library.show', $book->id) }}" class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-indigo-100 dark:hover:bg-indigo-900/30 text-slate-600 dark:text-slate-400 hover:text-indigo-600 transition" title="View"><i class="bi bi-eye"></i></a>
                                @can('edit books')
                                <a href="{{ route('library.edit', $book->id) }}" class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-indigo-100 dark:hover:bg-indigo-900/30 text-slate-600 dark:text-slate-400 hover:text-indigo-600 transition" title="Edit"><i class="bi bi-pencil"></i></a>
                                @endcan
                                @can('issue books')
                                <a href="{{ route('library.issues.create', ['book_id' => $book->id]) }}" class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-emerald-100 dark:hover:bg-emerald-900/30 text-slate-600 dark:text-slate-400 hover:text-emerald-600 transition" title="Issue"><i class="bi bi-box-arrow-up-right"></i></a>
                                @endcan
                                @can('delete books')
                                <form action="{{ route('library.destroy', $book->id) }}" method="POST" onsubmit="return confirm('Delete this book?')" class="inline">
                                    @csrf @method('DELETE')
                                    <button class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-rose-100 dark:hover:bg-rose-900/30 text-slate-600 dark:text-slate-400 hover:text-rose-600 transition" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-slate-400 py-8">No books found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">{{ $books->links() }}</div>
    </div>
</div>
@endsection
