@extends('layouts.app')
@section('title', 'Fee Chart & Structures')

@section('content')
<div class="px-4 md:px-8 py-6 max-w-[1600px] mx-auto">

    {{-- Top Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-3">
                <span class="p-2.5 bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 rounded-2xl">
                    <i class="bi bi-grid-3x3-gap-fill text-2xl"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                        Fee Chart & Structures <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">कक्षागत शुल्क तालिका</span>
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Set fee amounts per class in an interactive 2D table or print the official school fee chart.
                    </p>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            {{-- Print Button --}}
            <a href="{{ route('finance.structures.print-chart', ['session_id' => $selectedSession?->id]) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-slate-800 dark:text-slate-100 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 shadow-sm transition">
                <i class="bi bi-printer text-indigo-600 dark:text-indigo-400"></i> Print Fee Chart (A4)
            </a>

            {{-- Standard Seed Heads --}}
            <form method="POST" action="{{ route('finance.structures.seed-defaults') }}" class="inline" onsubmit="return confirm('Add missing standard fee heads (Annual Charge, Admission, Monthly, Computer, Hostel, Exam, Day Hostel, Tuition)?')">
                @csrf
                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition" title="Ensure all 9 standard heads exist">
                    <i class="bi bi-magic text-amber-500"></i> Standard Heads
                </button>
            </form>

            <a href="{{ route('finance.overview') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition">
                <i class="bi bi-arrow-left"></i> Finance
            </a>
        </div>
    </div>

    @if(session('status'))
    <div class="mb-6 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 flex items-center justify-between gap-3 shadow-sm">
        <div class="flex items-center gap-2.5">
            <i class="bi bi-check-circle-fill text-emerald-600 text-lg"></i>
            <span class="text-sm font-semibold text-emerald-900 dark:text-emerald-200">{{ session('status') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700"><i class="bi bi-x-lg"></i></button>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 p-4 rounded-2xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 flex items-center gap-2.5 shadow-sm">
        <i class="bi bi-exclamation-triangle-fill text-red-600 text-lg"></i>
        <span class="text-sm font-semibold text-red-900 dark:text-red-200">{{ session('error') }}</span>
    </div>
    @endif

    {{-- Session Selector & View Mode Switcher --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200/90 dark:border-slate-700/80 rounded-2xl p-4 mb-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        
        {{-- Session Form --}}
        <form method="GET" action="{{ route('finance.structures') }}" class="flex items-center gap-3">
            <input type="hidden" name="view" value="{{ request('view', 'matrix') }}">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Academic Session:</span>
                <select name="session_id" onchange="this.form.submit()" class="text-sm font-bold border border-slate-300 dark:border-slate-600 rounded-xl px-3 py-1.5 bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-400">
                    @foreach($sessions as $s)
                        <option value="{{ $s->id }}" @selected(($selectedSession->id ?? 0) == $s->id)>
                            {{ $s->session_name }} {{ $s->is_active ? '(Active)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <span class="text-xs text-slate-400">&bull; {{ count($classes) }} Classes &bull; {{ count($categories) }} Fee Heads</span>
        </form>

        {{-- View Tabs --}}
        <div class="flex items-center p-1 bg-slate-100 dark:bg-slate-700/60 rounded-xl">
            <button type="button" onclick="switchView('matrix')" id="tabBtnMatrix" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 {{ request('view', 'matrix') === 'matrix' ? 'bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900' }}">
                <i class="bi bi-grid-3x3"></i> 2D Fee Chart (Matrix)
            </button>
            <button type="button" onclick="switchView('list')" id="tabBtnList" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 {{ request('view') === 'list' ? 'bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900' }}">
                <i class="bi bi-list-ul"></i> Detailed List View
            </button>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 1: 2D FEE MATRIX GRID (COLUMNS = CLASSES, ROWS = FEE HEADS)          --}}
    {{-- ========================================================================= --}}
    <div id="viewMatrix" class="{{ request('view', 'matrix') === 'matrix' ? '' : 'hidden' }}">
        
        <form method="POST" action="{{ route('finance.structures.matrix') }}" id="matrixForm">
            @csrf
            <input type="hidden" name="session_id" value="{{ $selectedSession->id ?? '' }}">

            <div class="bg-white dark:bg-slate-800 border border-slate-200/90 dark:border-slate-700/80 rounded-2xl shadow-sm overflow-hidden mb-6">
                
                {{-- Matrix Top Toolbar --}}
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-between gap-4 bg-slate-50/70 dark:bg-slate-900/40">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-200">
                            <i class="bi bi-pencil-square text-indigo-600"></i> Principal Fee Chart Editor
                        </span>
                        <span class="text-xs text-slate-400">| टाइप गरेपछि स्वतः हिसाब हुन्छ, अन्त्यमा 'Save Fee Matrix' थिच्नुहोस्</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 rounded-xl text-xs font-black text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-600/20 hover:shadow-indigo-600/30 transition transform active:scale-95">
                            <i class="bi bi-check-circle-fill"></i> Save Fee Matrix (शुल्क चार्ट सुरक्षित गर्नुहोस्)
                        </button>
                    </div>
                </div>

                {{-- The 2D Matrix Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse" id="feeMatrixTable">
                        <thead class="bg-slate-100/90 dark:bg-slate-900/80 border-b-2 border-slate-300 dark:border-slate-700">
                            <tr>
                                {{-- Fixed Fee Head Column --}}
                                <th class="sticky left-0 z-20 bg-slate-100 dark:bg-slate-900 px-4 py-3 text-left text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider border-r border-slate-200 dark:border-slate-700 min-w-[200px]">
                                    CLASS / शुल्क शीर्षक
                                </th>
                                <th class="px-3 py-3 text-center text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider border-r border-slate-200 dark:border-slate-700 min-w-[110px]">
                                    चक्र (Freq)
                                </th>

                                {{-- Class Columns in Natural Order --}}
                                @foreach($classes as $c)
                                <th class="px-2 py-3 text-center text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider border-r border-slate-200 dark:border-slate-700 min-w-[85px]">
                                    <div class="font-extrabold text-indigo-600 dark:text-indigo-400">{{ strtoupper($c->class_name) }}</div>
                                </th>
                                @endforeach

                                <th class="px-3 py-3 text-center text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider min-w-[90px]">
                                    Row Action
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700 text-xs">
                            @forelse($categories as $cat)
                            @php
                                $detectedFreq = str_contains(strtolower($cat->name), 'annual') || str_contains(strtolower($cat->name), 'वार्षिक')
                                    ? 'yearly'
                                    : (str_contains(strtolower($cat->name), 'add') || str_contains(strtolower($cat->name), 'admission') || str_contains(strtolower($cat->name), 'भर्ना') || str_contains(strtolower($cat->name), 'deposit')
                                        ? 'once'
                                        : (str_contains(strtolower($cat->name), 'exam') || str_contains(strtolower($cat->name), 'परीक्षा')
                                            ? 'quarterly'
                                            : 'monthly'));
                            @endphp
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/40 transition group" data-category-id="{{ $cat->id }}">
                                
                                {{-- Fee Head Name --}}
                                <td class="sticky left-0 z-10 bg-white dark:bg-slate-800 group-hover:bg-slate-50 dark:group-hover:bg-slate-700/80 px-4 py-3 font-extrabold text-slate-900 dark:text-white border-r border-slate-200 dark:border-slate-700">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="tracking-wide uppercase">{{ $cat->name }}</span>
                                        @if(\App\Models\FeeCategory::isLegalHeadName($cat->name))
                                            <i class="bi bi-shield-check text-emerald-500 text-sm" title="Nepal Legal Fee Head"></i>
                                        @endif
                                    </div>
                                    @if($cat->description)
                                        <div class="text-[10px] text-slate-400 truncate max-w-[180px] font-normal">{{ $cat->description }}</div>
                                    @endif
                                </td>

                                {{-- Frequency Dropdown --}}
                                <td class="px-2 py-2 border-r border-slate-200 dark:border-slate-700 text-center">
                                    <select name="frequencies[{{ $cat->id }}]" class="freq-select w-full text-[11px] font-semibold border border-slate-200 dark:border-slate-600 rounded-lg px-1.5 py-1 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-indigo-400">
                                        <option value="monthly" @selected($detectedFreq==='monthly')>Monthly (मासिक)</option>
                                        <option value="yearly" @selected($detectedFreq==='yearly')>Yearly (वार्षिक)</option>
                                        <option value="once" @selected($detectedFreq==='once')>One-time (एकपटक)</option>
                                        <option value="quarterly" @selected($detectedFreq==='quarterly')>Quarterly (त्रैमासिक)</option>
                                        <option value="half_yearly" @selected($detectedFreq==='half_yearly')>Half-Yearly</option>
                                    </select>
                                </td>

                                {{-- Editable Amount Inputs per Class --}}
                                @foreach($classes as $c)
                                @php
                                    $currentAmt = $feeMatrix[$cat->id][$c->id] ?? null;
                                @endphp
                                <td class="p-1.5 border-r border-slate-200 dark:border-slate-700 text-center">
                                    <input type="number"
                                           step="any"
                                           min="0"
                                           name="matrix[{{ $cat->id }}][{{ $c->id }}]"
                                           value="{{ $currentAmt !== null && $currentAmt > 0 ? (float)$currentAmt : '' }}"
                                           placeholder="—"
                                           data-class-id="{{ $c->id }}"
                                           data-category-id="{{ $cat->id }}"
                                           class="matrix-input w-20 text-center font-bold text-xs md:text-sm border border-slate-200 dark:border-slate-600 rounded-lg px-1.5 py-1.5 bg-slate-50/70 dark:bg-slate-900/60 text-slate-900 dark:text-white placeholder-slate-400 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                                </td>
                                @endforeach

                                {{-- Quick Row Actions --}}
                                <td class="px-2 py-2 text-center whitespace-nowrap">
                                    <button type="button" onclick="copyRowFirstValue({{ $cat->id }})" class="p-1 rounded text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-slate-700 transition" title="Copy first filled value across all classes">
                                        <i class="bi bi-arrow-right-short text-base"></i>
                                    </button>
                                    <button type="button" onclick="clearRow({{ $cat->id }})" class="p-1 rounded text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-slate-700 transition" title="Clear this row">
                                        <i class="bi bi-x-circle text-xs"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ count($classes) + 3 }}" class="py-12 text-center text-slate-500">
                                    <i class="bi bi-tags text-4xl mb-2 block text-slate-300"></i>
                                    <p class="font-bold text-sm">No fee heads configured yet.</p>
                                    <p class="text-xs text-slate-400 mt-1">Click "Standard Heads" at the top to add Annual Charge, Monthly Fee, Exam Fee, Hostel, etc.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>

                        {{-- Footer Summary Row (Auto-Calculated in Real-Time via JavaScript) --}}
                        <tfoot class="bg-indigo-50/70 dark:bg-indigo-950/40 border-t-2 border-indigo-200 dark:border-indigo-800 font-extrabold text-xs">
                            <tr>
                                <td class="sticky left-0 z-10 bg-indigo-50 dark:bg-indigo-950 px-4 py-3 text-indigo-950 dark:text-indigo-200 uppercase tracking-wider border-r border-indigo-200 dark:border-indigo-800">
                                    मासिक जम्मा (Monthly Total)
                                </td>
                                <td class="px-2 py-3 text-center text-slate-500 border-r border-indigo-200 dark:border-indigo-800 text-[10px]">
                                    ∑ Monthly
                                </td>
                                @foreach($classes as $c)
                                <td class="px-2 py-3 text-center text-indigo-700 dark:text-indigo-300 font-black border-r border-indigo-200 dark:border-indigo-800" id="totalMonthly_{{ $c->id }}">
                                    Rs. 0
                                </td>
                                @endforeach
                                <td></td>
                            </tr>
                            <tr>
                                <td class="sticky left-0 z-10 bg-indigo-50/90 dark:bg-indigo-950/90 px-4 py-3 text-indigo-950 dark:text-indigo-200 uppercase tracking-wider border-r border-indigo-200 dark:border-indigo-800">
                                    वार्षिक अनुमानित (Annual Total)
                                </td>
                                <td class="px-2 py-3 text-center text-slate-500 border-r border-indigo-200 dark:border-indigo-800 text-[10px]">
                                    ∑ Full Yr
                                </td>
                                @foreach($classes as $c)
                                <td class="px-2 py-3 text-center text-emerald-700 dark:text-emerald-400 font-black border-r border-indigo-200 dark:border-indigo-800" id="totalAnnual_{{ $c->id }}">
                                    Rs. 0
                                </td>
                                @endforeach
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Bottom Action Bar --}}
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/60 border-t border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-between gap-4">
                    <div class="text-xs text-slate-500">
                        <i class="bi bi-info-circle text-indigo-500"></i> Tip: Use <kbd class="px-1.5 py-0.5 bg-white dark:bg-slate-800 border rounded text-[10px]">Tab</kbd> to jump between classes, or <kbd class="px-1.5 py-0.5 bg-white dark:bg-slate-800 border rounded text-[10px]">Enter</kbd> to move down.
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-xs font-black text-white bg-indigo-600 hover:bg-indigo-700 shadow-md transition transform active:scale-95">
                            <i class="bi bi-check-circle-fill"></i> Save Fee Matrix (शुल्क चार्ट सेभ गर्नुहोस्)
                        </button>
                    </div>
                </div>

            </div>
        </form>
    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 2: DETAILED LIST VIEW (WITH MANUAL ADD & PAGINATED LIST)             --}}
    {{-- ========================================================================= --}}
    <div id="viewList" class="{{ request('view') === 'list' ? '' : 'hidden' }}">
        
        {{-- Individual Add Form --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl shadow-sm overflow-hidden mb-6">
            <div class="px-6 py-4 flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60">
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                    <i class="bi bi-plus-circle text-indigo-500"></i> Add Single Custom Fee Structure
                </h3>
                <button type="button" id="toggleAddForm" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition">
                    <i class="bi bi-plus-lg"></i> Add Custom
                </button>
            </div>
            <div id="addForm" class="hidden px-6 py-4 border-b border-slate-100 dark:border-slate-700/60">
                <form method="POST" action="{{ route('finance.structures.store') }}">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="e.g. Science Lab Fee" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Category <span class="text-red-500">*</span></label>
                            <select name="category_id" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400" required>
                                <option value="">— Select Category —</option>
                                @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Amount (NPR) <span class="text-red-500">*</span></label>
                            <input type="number" name="amount" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400" step="0.01" min="0" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Frequency</label>
                            <select name="frequency" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                @foreach(['once'=>'One-time','monthly'=>'Monthly','quarterly'=>'Quarterly','half_yearly'=>'Half-Yearly','yearly'=>'Yearly'] as $v=>$l)
                                    <option value="{{ $v }}">{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Session</label>
                            <select name="session_id" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                <option value="">All Sessions</option>
                                @foreach($sessions as $s)<option value="{{ $s->id }}">{{ $s->session_name }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Class</label>
                            <select name="class_id" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                <option value="">All Classes</option>
                                @foreach($classes as $c)<option value="{{ $c->id }}">{{ $c->class_name }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Due Date</label>
                            <input type="date" name="due_date" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-lg text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition">
                                <i class="bi bi-check-circle"></i> Save Structure
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Filter for List --}}
        <form method="GET" action="{{ route('finance.structures') }}" class="mb-4 flex flex-wrap gap-3">
            <input type="hidden" name="view" value="list">
            <div class="flex-1 min-w-[200px]">
                <select name="session_id" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400" onchange="this.form.submit()">
                    <option value="">All Sessions</option>
                    @foreach($sessions as $s)<option value="{{ $s->id }}" @selected(request('session_id')==$s->id)>{{ $s->session_name }}</option>@endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <select name="class_id" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400" onchange="this.form.submit()">
                    <option value="">All Classes</option>
                    @foreach($classes as $c)<option value="{{ $c->id }}" @selected(request('class_id')==$c->id)>{{ $c->class_name }}</option>@endforeach
                </select>
            </div>
            @if(request()->hasAny(['session_id','class_id']))
            <a href="{{ route('finance.structures', ['view' => 'list']) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-indigo-400 transition">
                <i class="bi bi-x-circle"></i> Clear Filter
            </a>
            @endif
        </form>

        {{-- List Table --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Session</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Class</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Frequency</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse($structures as $s)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition">
                            <td class="px-6 py-4 text-sm font-semibold text-slate-900 dark:text-white">{{ $s->name }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">{{ $s->category?->name ?? '—' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400">{{ $s->session?->session_name ?? 'All' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400">{{ $s->schoolClass?->class_name ?? 'All' }}</td>
                            <td class="px-6 py-4 text-sm font-bold text-slate-900 dark:text-white">Rs. {{ number_format($s->amount, 2) }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">{{ $s->frequency_label }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $s->is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300' }}">
                                    {{ $s->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button onclick="document.getElementById('editStr{{ $s->id }}').classList.remove('hidden')" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <form method="POST" action="{{ route('finance.structures.destroy', $s->id) }}" class="inline" onsubmit="return confirm('Delete this fee structure?')">@csrf @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                <i class="bi bi-inbox text-4xl mb-2 block text-slate-300 dark:text-slate-600"></i>
                                <span class="text-sm">No fee structures yet.</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700">
                {{ $structures->links() }}
            </div>
        </div>

    </div>

    {{-- Edit Modals (for detailed list view) --}}
    @foreach($structures as $s)
    <div id="editStr{{ $s->id }}" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="document.getElementById('editStr{{ $s->id }}').classList.add('hidden')"></div>
            <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-xl max-w-2xl w-full p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Edit: {{ $s->name }}</h3>
                    <button onclick="document.getElementById('editStr{{ $s->id }}').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <i class="bi bi-x-lg text-xl"></i>
                    </button>
                </div>
                <form method="POST" action="{{ route('finance.structures.update', $s->id) }}">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Name</label>
                            <input type="text" name="name" value="{{ $s->name }}" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Category</label>
                            <select name="category_id" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400" required>
                                @foreach($categories as $c)<option value="{{ $c->id }}" @selected($s->category_id==$c->id)>{{ $c->name }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Amount (NPR)</label>
                            <input type="number" name="amount" value="{{ $s->amount }}" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400" step="0.01" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Frequency</label>
                            <select name="frequency" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                @foreach(['once'=>'One-time','monthly'=>'Monthly','quarterly'=>'Quarterly','half_yearly'=>'Half-Yearly','yearly'=>'Yearly'] as $v=>$l)
                                    <option value="{{ $v }}" @selected($s->frequency===$v)>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Session</label>
                            <select name="session_id" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                <option value="">All</option>
                                @foreach($sessions as $ss)<option value="{{ $ss->id }}" @selected($s->session_id==$ss->id)>{{ $ss->session_name }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Class</label>
                            <select name="class_id" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                <option value="">All</option>
                                @foreach($classes as $c)<option value="{{ $c->id }}" @selected($s->class_id==$c->id)>{{ $c->class_name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-span-2 flex items-center pt-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" @checked($s->is_active) class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Active</span>
                            </label>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-slate-200 dark:border-slate-700">
                        <button type="button" onclick="document.getElementById('editStr{{ $s->id }}').classList.add('hidden')" class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 transition">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach

</div>

<script>
    // Tab switching
    function switchView(viewName) {
        var matrixEl = document.getElementById('viewMatrix');
        var listEl = document.getElementById('viewList');
        var tabMatrix = document.getElementById('tabBtnMatrix');
        var tabList = document.getElementById('tabBtnList');

        if (viewName === 'matrix') {
            matrixEl.classList.remove('hidden');
            listEl.classList.add('hidden');
            tabMatrix.className = "px-3.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 shadow-sm";
            tabList.className = "px-3.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 text-slate-600 dark:text-slate-300 hover:text-slate-900";
        } else {
            matrixEl.classList.add('hidden');
            listEl.classList.remove('hidden');
            tabList.className = "px-3.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 shadow-sm";
            tabMatrix.className = "px-3.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 text-slate-600 dark:text-slate-300 hover:text-slate-900";
        }
    }

    // Toggle single add form
    var toggleBtn = document.getElementById('toggleAddForm');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            var form = document.getElementById('addForm');
            form.classList.toggle('hidden');
        });
    }

    // Copy first filled value in row across all classes
    function copyRowFirstValue(catId) {
        var row = document.querySelector('tr[data-category-id="' + catId + '"]');
        if (!row) return;
        var inputs = row.querySelectorAll('.matrix-input');
        var firstVal = '';
        for (var i = 0; i < inputs.length; i++) {
            if (inputs[i].value !== '') {
                firstVal = inputs[i].value;
                break;
            }
        }
        if (firstVal === '') {
            alert('Please enter an amount in at least one class cell first.');
            return;
        }
        inputs.forEach(function(inp) {
            inp.value = firstVal;
        });
        recalculateTotals();
    }

    // Clear entire row
    function clearRow(catId) {
        if (!confirm('Clear all class amounts for this row?')) return;
        var row = document.querySelector('tr[data-category-id="' + catId + '"]');
        if (!row) return;
        row.querySelectorAll('.matrix-input').forEach(function(inp) {
            inp.value = '';
        });
        recalculateTotals();
    }

    // Recalculate monthly and annual totals per class
    function recalculateTotals() {
        var classIds = [
            @foreach($classes as $c)
                {{ $c->id }},
            @endforeach
        ];

        classIds.forEach(function(cId) {
            var monthlySum = 0;
            var annualSum = 0;

            var inputs = document.querySelectorAll('.matrix-input[data-class-id="' + cId + '"]');
            inputs.forEach(function(inp) {
                var val = parseFloat(inp.value) || 0;
                if (val <= 0) return;

                var row = inp.closest('tr');
                var freqSelect = row ? row.querySelector('.freq-select') : null;
                var freq = freqSelect ? freqSelect.value : 'monthly';

                if (freq === 'monthly') {
                    monthlySum += val;
                    annualSum += (val * 12);
                } else if (freq === 'yearly') {
                    annualSum += val;
                } else if (freq === 'once') {
                    annualSum += val; // one-time at admission
                } else if (freq === 'quarterly') {
                    annualSum += (val * 4);
                } else if (freq === 'half_yearly') {
                    annualSum += (val * 2);
                }
            });

            var mEl = document.getElementById('totalMonthly_' + cId);
            if (mEl) mEl.textContent = 'Rs. ' + monthlySum.toLocaleString();

            var aEl = document.getElementById('totalAnnual_' + cId);
            if (aEl) aEl.textContent = 'Rs. ' + annualSum.toLocaleString();
        });
    }

    // Recalculate on any input change
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('matrix-input')) {
            recalculateTotals();
        }
    });

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('freq-select')) {
            recalculateTotals();
        }
    });

    // Arrow keys & Enter key navigation across matrix table
    document.addEventListener('keydown', function(e) {
        if (!e.target.classList.contains('matrix-input')) return;

        var input = e.target;
        var td = input.closest('td');
        var tr = td.closest('tr');
        var tbody = tr.closest('tbody');
        var colIndex = Array.prototype.indexOf.call(tr.children, td);
        var rowIndex = Array.prototype.indexOf.call(tbody.children, tr);

        if (e.key === 'Enter' || e.key === 'ArrowDown') {
            e.preventDefault();
            var nextRow = tbody.children[rowIndex + 1];
            if (nextRow && nextRow.children[colIndex]) {
                var nextInput = nextRow.children[colIndex].querySelector('.matrix-input');
                if (nextInput) nextInput.focus();
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            var prevRow = tbody.children[rowIndex - 1];
            if (prevRow && prevRow.children[colIndex]) {
                var prevInput = prevRow.children[colIndex].querySelector('.matrix-input');
                if (prevInput) prevInput.focus();
            }
        }
    });

    // Initialize calculations on page load
    document.addEventListener('DOMContentLoaded', recalculateTotals);
</script>
@endsection
