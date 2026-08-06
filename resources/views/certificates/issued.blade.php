@extends('layouts.app')
@section('content')
<div class="flex-1 p-6 lg:p-8 overflow-auto">

    <nav class="text-xs text-slate-400 mb-5 flex items-center gap-1">
        <a href="{{ route('certificates.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Certificates</a>
        <i class="bi bi-chevron-right text-slate-300 text-xs"></i>
        <span class="text-slate-500 dark:text-slate-300">Issued Ledger</span>
    </nav>

    <div class="flex flex-wrap justify-between items-start mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Issued Certificates</h1>
            <p class="text-slate-400 text-sm mt-0.5">Complete ledger of all certificates issued by this school</p>
        </div>
        <a href="{{ route('certificates.bulk') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition">
            <i class="bi bi-people-fill"></i> Bulk Issue
        </a>
    </div>

    @if(session('status'))
    <div class="mb-5 p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-700 flex items-center gap-2">
        <i class="bi bi-check-circle-fill"></i> {{ session('status') }}
    </div>
    @endif

    {{-- Filters --}}
    <form method="GET" action="{{ route('certificates.issued') }}"
          class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-xs p-4 mb-6 flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[180px]">
            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Name or certificate number..."
                class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400">
        </div>
        <div class="min-w-[180px]">
            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Type</label>
            <select name="type"
                class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">All Types</option>
                @foreach($types as $val => $label)
                <option value="{{ $val }}" {{ request('type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-[140px]">
            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Status</label>
            <select name="status"
                class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">All</option>
                <option value="issued"  {{ request('status') === 'issued'  ? 'selected' : '' }}>Issued</option>
                <option value="revoked" {{ request('status') === 'revoked' ? 'selected' : '' }}>Revoked</option>
                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">
                <i class="bi bi-search me-1"></i> Filter
            </button>
            <a href="{{ route('certificates.issued') }}"
               class="px-4 py-2 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200 rounded-lg text-sm font-medium hover:bg-slate-50 transition">
                Clear
            </a>
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-100 dark:border-slate-700">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Certificate No.</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Student</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Type</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Issued</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Issued By</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Status</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-700">
                    @forelse($records as $rec)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition">
                        <td class="px-4 py-3">
                            <span class="font-mono text-xs font-semibold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/30 px-2 py-0.5 rounded">
                                {{ $rec->certificate_number }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-xs font-bold text-slate-600 dark:text-slate-300 flex-shrink-0">
                                    {{ strtoupper(substr($rec->student->first_name ?? '?', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-slate-700 dark:text-slate-200">{{ $rec->student->full_name ?? '—' }}</p>
                                    @if($rec->purpose)
                                    <p class="text-xs text-slate-400">{{ $rec->purpose }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 px-2 py-0.5 rounded-full">
                                {{ $rec->typeLabel() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-500 dark:text-slate-400 text-xs">
                            {{ $rec->issued_date->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3 text-slate-500 dark:text-slate-400 text-xs">
                            {{ $rec->issuer->full_name ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $rec->statusBadgeClass() }}">
                                {{ ucfirst($rec->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1.5">
                                @if($rec->status === 'issued')
                                <a href="{{ route('certificates.download', $rec->id) }}" target="_blank"
                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-indigo-50 dark:bg-indigo-900/20 hover:bg-indigo-100 text-indigo-700 dark:text-indigo-300 rounded-lg text-xs font-medium transition">
                                    <i class="bi bi-printer-fill"></i> View & Print
                                </a>
                                <form method="POST" action="{{ route('certificates.revoke', $rec->id) }}" class="inline">
                                    @csrf
                                    <button onclick="return confirm('Revoke certificate {{ $rec->certificate_number }}?')"
                                            class="p-1.5 bg-rose-50 dark:bg-rose-900/20 hover:bg-rose-100 text-rose-700 dark:text-rose-400 rounded-lg transition" title="Revoke">
                                        <i class="bi bi-x-circle text-xs"></i>
                                    </button>
                                </form>
                                @endif
                                <a href="{{ route('certificates.verify', $rec->verification_token) }}" target="_blank"
                                   class="p-1.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-600 dark:text-slate-300 rounded-lg transition" title="Verify QR">
                                    <i class="bi bi-qr-code-scan text-xs"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-slate-400 text-sm">
                            <i class="bi bi-inbox text-3xl block mb-2"></i>
                            No certificates found matching the current filters.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($records->hasPages())
        <div class="px-4 py-3 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/30">
            {{ $records->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
