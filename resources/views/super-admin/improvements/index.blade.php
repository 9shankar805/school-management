@extends('layouts.app')
@section('title', 'System Improvements & Feature Roadmap - NepalSchoolHub')

@section('content')
<div class="px-6 py-6 max-w-7xl mx-auto space-y-6">

    {{-- Top Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300">
                    SaaS Platform Quality
                </span>
                <span class="text-xs text-slate-400">&bull; प्रणाली सुधार तथा कार्य योजना</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1 flex items-center gap-2.5">
                <i class="bi bi-tools text-indigo-600"></i>
                System Improvements &amp; Tasks (प्रणाली सुधार तथा कार्य सूची)
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                Track platform upgrades, required feature enhancements, user-requested fixes, and monitor what is Remaining vs. Done.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button type="button" onclick="openCreateModal()"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm shadow-sm shadow-indigo-200 dark:shadow-none transition-all">
                <i class="bi bi-plus-circle-fill"></i>
                Add What Needs to Improve (नयाँ सुधार थप्नुहोस्)
            </button>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-sm flex items-center gap-2">
            <i class="bi bi-check-circle-fill text-emerald-600 text-base"></i>
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-sm">
            <ul class="list-disc list-inside text-xs">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Metric Stat Cards with Progress --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        {{-- Total Items --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg">
                    <i class="bi bi-list-check"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-medium">Total Improvements (कुल कार्य)</p>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white">{{ $stats['total'] }}</h3>
                </div>
            </div>
        </div>

        {{-- Remaining Items --}}
        <div class="bg-white dark:bg-slate-800 border border-amber-200/80 dark:border-amber-800/80 rounded-2xl p-4 shadow-xs bg-amber-50/20">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div>
                    <p class="text-xs text-amber-700 dark:text-amber-300 font-bold uppercase tracking-wider">Remaining (गर्न बाँकी)</p>
                    <h3 class="text-xl font-black text-amber-600 dark:text-amber-400">{{ $stats['remaining'] }}</h3>
                </div>
            </div>
        </div>

        {{-- Done Items --}}
        <div class="bg-white dark:bg-slate-800 border border-emerald-200/80 dark:border-emerald-800/80 rounded-2xl p-4 shadow-xs bg-emerald-50/20">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div>
                    <p class="text-xs text-emerald-700 dark:text-emerald-300 font-bold uppercase tracking-wider">Done (सम्पन्न भयो)</p>
                    <h3 class="text-xl font-black text-emerald-600 dark:text-emerald-400">{{ $stats['done'] }}</h3>
                </div>
            </div>
        </div>

        {{-- Progress Bar --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between text-xs font-semibold">
                    <span class="text-slate-400">Completion Rate</span>
                    <span class="text-slate-900 dark:text-white font-bold">{{ $stats['progress'] }}%</span>
                </div>
                <div class="w-full bg-slate-100 dark:bg-slate-700 h-2.5 rounded-full mt-2 overflow-hidden">
                    <div class="bg-gradient-to-r from-emerald-500 to-indigo-600 h-full rounded-full transition-all duration-500"
                        style="width: {{ $stats['progress'] }}%"></div>
                </div>
            </div>
            <p class="text-[10px] text-slate-400 mt-1">
                {{ $stats['done'] }} completed out of {{ $stats['total'] }} planned tasks
            </p>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 shadow-xs">
        <form action="{{ route('super.improvements.index') }}" method="GET" class="flex flex-wrap items-center justify-between gap-3">
            {{-- Quick Status Pills --}}
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('super.improvements.index') }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition {{ !request()->has('status') || request('status') === 'all' ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    All Items (सबै)
                </a>
                <a href="{{ route('super.improvements.index', ['status' => 'remaining']) }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ request('status') === 'remaining' ? 'bg-amber-500 text-white' : 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 hover:bg-amber-100' }}">
                    <i class="bi bi-clock-history"></i> Remaining Only (बाँकी - {{ $stats['remaining'] }})
                </a>
                <a href="{{ route('super.improvements.index', ['status' => 'done']) }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ request('status') === 'done' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 hover:bg-emerald-100' }}">
                    <i class="bi bi-check2-circle"></i> Done Only (सम्पन्न - {{ $stats['done'] }})
                </a>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <div class="w-48">
                    <select name="category" onchange="this.form.submit()" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:text-white">
                        <option value="">All Categories (सबै विधा)</option>
                        @foreach($categories as $catKey => $catLabel)
                            <option value="{{ $catKey }}" {{ request('category') === $catKey ? 'selected' : '' }}>
                                {{ $catLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="relative w-56">
                    <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search improvement..."
                        class="w-full pl-8 pr-3 py-1.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:text-white">
                </div>
                <button type="submit" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-semibold">
                    Filter
                </button>
            </div>
        </form>
    </div>

    {{-- Improvement Items Table --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 dark:bg-slate-900/40 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200/80 dark:border-slate-700/80">
                    <tr>
                        <th class="px-4 py-3.5 text-center w-14">Status</th>
                        <th class="px-5 py-3.5">Improvement / Feature Task</th>
                        <th class="px-4 py-3.5">Module Category</th>
                        <th class="px-4 py-3.5">Priority</th>
                        <th class="px-4 py-3.5">Target / Finished Date</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 font-medium">
                    @forelse($improvements as $item)
                        @php
                            $isDone = $item->status === 'done';
                        @endphp
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-750 transition-colors {{ $isDone ? 'bg-slate-50/30 opacity-80' : '' }}">
                            {{-- One-Click Status Checkbox --}}
                            <td class="px-4 py-3.5 text-center">
                                <form action="{{ route('super.improvements.toggle', $item) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" title="{{ $isDone ? 'Mark as Remaining (फेरि खोल्नुहोस्)' : 'Mark as Done (सम्पन्न चिन्ह लगाउनुहोस्)' }}"
                                        class="w-7 h-7 rounded-lg flex items-center justify-center transition {{ $isDone ? 'bg-emerald-500 text-white hover:bg-emerald-600 shadow-xs' : 'border-2 border-slate-300 text-transparent hover:border-emerald-500 hover:text-emerald-500' }}">
                                        <i class="bi bi-check-lg text-sm font-black"></i>
                                    </button>
                                </form>
                            </td>

                            {{-- Title & Description --}}
                            <td class="px-5 py-3.5">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-bold text-sm {{ $isDone ? 'line-through text-slate-500 dark:text-slate-400' : 'text-slate-900 dark:text-white' }}">
                                            {{ $item->title }}
                                        </h4>
                                        @if($isDone)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                                <i class="bi bi-check-circle-fill text-[9px]"></i> DONE
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                                <i class="bi bi-clock-history text-[9px]"></i> REMAINING
                                            </span>
                                        @endif
                                    </div>

                                    @if($item->description)
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 line-clamp-2 max-w-xl">
                                            {{ $item->description }}
                                        </p>
                                    @endif

                                    @if($item->notes)
                                        <p class="text-[10px] text-indigo-600 dark:text-indigo-400 mt-0.5 italic">
                                            <i class="bi bi-chat-left-dots"></i> Note: {{ $item->notes }}
                                        </p>
                                    @endif
                                </div>
                            </td>

                            {{-- Category Badge --}}
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300">
                                    {{ $categories[$item->category] ?? ucfirst($item->category) }}
                                </span>
                            </td>

                            {{-- Priority Badge --}}
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if($item->priority === 'urgent')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300 border border-rose-200">
                                        🚨 Urgent
                                    </span>
                                @elseif($item->priority === 'high')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 border border-amber-200">
                                        ⚡ High
                                    </span>
                                @elseif($item->priority === 'medium')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                                        Medium
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                        Low
                                    </span>
                                @endif
                            </td>

                            {{-- Date / Completion --}}
                            <td class="px-4 py-3.5 whitespace-nowrap text-slate-500 text-[11px]">
                                @if($isDone && $item->completed_at)
                                    <div>
                                        <span class="text-emerald-600 font-semibold flex items-center gap-1">
                                            <i class="bi bi-calendar2-check"></i> Done: {{ $item->completed_at->format('M d, Y') }}
                                        </span>
                                        @if($item->completer)
                                            <span class="text-[10px] text-slate-400 block">by {{ $item->completer->first_name }}</span>
                                        @endif
                                    </div>
                                @elseif($item->target_date)
                                    <span class="text-slate-600 dark:text-slate-300 flex items-center gap-1">
                                        <i class="bi bi-calendar-event"></i> Target: {{ $item->target_date->format('M d, Y') }}
                                    </span>
                                @else
                                    <span class="text-slate-400 italic">No deadline set</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    {{-- Quick Toggle Button --}}
                                    <form action="{{ route('super.improvements.toggle', $item) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="px-2.5 py-1 rounded-lg text-xs font-semibold transition {{ $isDone ? 'bg-slate-100 text-slate-600 hover:bg-slate-200' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-300' }}">
                                            {{ $isDone ? 'Re-open' : 'Mark Done' }}
                                        </button>
                                    </form>

                                    {{-- Edit Button --}}
                                    <button type="button"
                                        onclick="openEditModal({{ $item->id }}, '{{ addslashes($item->title) }}', '{{ $item->category }}', '{{ $item->priority }}', '{{ $item->status }}', '{{ $item->target_date ? $item->target_date->format('Y-m-d') : '' }}', '{{ addslashes($item->description ?? '') }}', '{{ addslashes($item->notes ?? '') }}')"
                                        class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs">
                                        <i class="bi bi-pencil"></i>
                                    </button>

                                    {{-- Delete Button --}}
                                    <form action="{{ route('super.improvements.destroy', $item) }}" method="POST"
                                        onsubmit="return confirm('Delete this improvement item?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-900/30 text-xs">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <i class="bi bi-tools text-3xl mb-2 block opacity-50"></i>
                                No improvement items found. Click "Add What Needs to Improve" to record new system tasks.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($improvements->hasPages())
            <div class="px-5 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/30">
                {{ $improvements->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Create Improvement Modal --}}
<div id="createModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl w-full max-w-lg shadow-2xl p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-3">
            <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="bi bi-plus-circle text-indigo-600"></i>
                Add What Needs to Improve (नयाँ सुधार कार्य थप्नुहोस्)
            </h3>
            <button onclick="closeCreateModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-lg">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form action="{{ route('super.improvements.store') }}" method="POST" class="space-y-4 text-xs">
            @csrf

            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    What needs to be improved? (सुधार गर्नुपर्ने कुरा / कार्य) <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="title" required
                    placeholder="e.g. Add bulk student fee invoice PDF printing"
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:text-white font-semibold">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Category (विधा) <span class="text-rose-500">*</span></label>
                    <select name="category" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:text-white">
                        @foreach($categories as $catKey => $catLabel)
                            <option value="{{ $catKey }}">{{ $catLabel }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Priority (प्राथमिकता)</label>
                    <select name="priority" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:text-white">
                        <option value="medium" selected>Medium (सामान्य)</option>
                        <option value="high">High (उच्च)</option>
                        <option value="urgent">Urgent (अत्यन्त जरुरी)</option>
                        <option value="low">Low (कम प्राथमिकता)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Status (अवस्था) <span class="text-rose-500">*</span></label>
                    <select name="status" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:text-white font-bold">
                        <option value="remaining" selected class="text-amber-600 font-bold">Remaining (गर्न बाँकी)</option>
                        <option value="done" class="text-emerald-600 font-bold">Done (सम्पन्न भयो)</option>
                        <option value="in_progress">In Progress (कार्य हुँदैछ)</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Target Date (लक्ष्य मिति)</label>
                    <input type="date" name="target_date"
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:text-white">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Detailed Description (विस्तृत विवरण)</label>
                <textarea name="description" rows="3"
                    placeholder="Explain what needs to be improved, context, or feedback source..."
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:text-white"></textarea>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Implementation Notes (सुझाव / नोट)</label>
                <input type="text" name="notes" placeholder="Optional notes for developer or team"
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:text-white">
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                <button type="button" onclick="closeCreateModal()"
                    class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-semibold text-xs">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md transition">
                    Save Improvement Item
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Improvement Modal --}}
<div id="editModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl w-full max-w-lg shadow-2xl p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-3">
            <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="bi bi-pencil-square text-indigo-600"></i>
                Edit Improvement Item (सुधार कार्य सम्पादन)
            </h3>
            <button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-lg">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="editForm" method="POST" action="" class="space-y-4 text-xs">
            @csrf
            @method('PUT')

            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    What needs to be improved? <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="title" id="editTitle" required
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:text-white font-semibold">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Category <span class="text-rose-500">*</span></label>
                    <select name="category" id="editCategory" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:text-white">
                        @foreach($categories as $catKey => $catLabel)
                            <option value="{{ $catKey }}">{{ $catLabel }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Priority</label>
                    <select name="priority" id="editPriority" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:text-white">
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                        <option value="low">Low</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Status (अवस्था) <span class="text-rose-500">*</span></label>
                    <select name="status" id="editStatus" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:text-white font-bold">
                        <option value="remaining">Remaining (गर्न बाँकी)</option>
                        <option value="done">Done (सम्पन्न भयो)</option>
                        <option value="in_progress">In Progress (कार्य हुँदैछ)</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Target Date</label>
                    <input type="date" name="target_date" id="editTargetDate"
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:text-white">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Detailed Description</label>
                <textarea name="description" id="editDescription" rows="3"
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:text-white"></textarea>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Implementation Notes</label>
                <input type="text" name="notes" id="editNotes"
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:text-white">
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                <button type="button" onclick="closeEditModal()"
                    class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-semibold text-xs">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md transition">
                    Update Item
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('createModal').classList.remove('hidden');
}

function closeCreateModal() {
    document.getElementById('createModal').classList.add('hidden');
}

function openEditModal(id, title, category, priority, status, targetDate, description, notes) {
    document.getElementById('editForm').action = '/super/improvements/' + id;
    document.getElementById('editTitle').value = title;
    document.getElementById('editCategory').value = category;
    document.getElementById('editPriority').value = priority;
    document.getElementById('editStatus').value = status;
    document.getElementById('editTargetDate').value = targetDate;
    document.getElementById('editDescription').value = description;
    document.getElementById('editNotes').value = notes;

    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}
</script>
@endsection

