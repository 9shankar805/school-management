@extends('layouts.app')
@section('content')
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        {{-- Top Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-7">
            <div class="flex items-center gap-3">
                <a href="{{ route('exam.schedule.index') }}" class="text-slate-400 hover:text-slate-600 transition"><i class="bi bi-arrow-left text-lg"></i></a>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Academic Coordinator Seat Planning</h1>
                    <p class="text-slate-400 text-sm mt-0.5">
                        {{ $schedule->exam?->exam_name }} · {{ \Carbon\Carbon::parse($schedule->exam_date)->format('d M Y') }}
                        · {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}
                        @if($schedule->hall) · {{ $schedule->hall->hall_name }} (cap. {{ $schedule->hall->capacity }}) @endif
                    </p>
                </div>
            </div>
            
            <a href="{{ route('exam.hall.seats.print', $schedule->id) }}" target="_blank"
               class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-medium transition flex items-center justify-center gap-2 shadow-sm">
                <i class="bi bi-printer"></i> Print Bench & Door Chart
            </a>
        </div>

        @if(session('status'))
        <div class="mb-5 p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm flex items-center gap-2 shadow-sm">
            <i class="bi bi-check-circle-fill text-lg"></i> {{ session('status') }}
        </div>
        @endif
        @include('session-messages')

        @can('create exams')
        {{-- Room Column & Bench Seating Generator --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 mb-8">
            <div class="border-b border-slate-100 pb-4 mb-5 flex justify-between items-center">
                <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <i class="bi bi-grid-3x3-gap-fill text-indigo-600"></i> Room Bench Seating Generator
                </h2>
                <span class="text-xs bg-indigo-50 text-indigo-700 font-semibold px-2.5 py-1 rounded-full">
                    Configure Benches (1, 2, or 3 Students per Bench)
                </span>
            </div>
            
            <form method="POST" action="{{ route('exam.hall.seats.auto', $schedule->id) }}" class="space-y-5">
                @csrf
                
                {{-- Multi-Class Selection Checklist --}}
                <div>
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500 block mb-2">
                        1. Select Classes to Seat Together (Multi-Class Selection):
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-2 bg-slate-50 p-3.5 rounded-xl border border-slate-100 max-h-40 overflow-y-auto">
                        @foreach($allClasses as $clsItem)
                        <label class="flex items-center gap-2 text-xs font-medium text-slate-700 hover:text-indigo-600 cursor-pointer p-1 rounded hover:bg-white transition">
                            <input type="checkbox" name="class_ids[]" value="{{ $clsItem->id }}"
                                   {{ $clsItem->id === $schedule->exam?->class_id ? 'checked' : '' }}
                                   class="rounded text-indigo-600 focus:ring-indigo-500 border-slate-300">
                            <span>{{ $clsItem->class_name }}</span>
                        </label>
                        @endforeach
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1.5"><i class="bi bi-info-circle me-1"></i>Check multiple classes to mix and seat students together in the examination room.</p>
                </div>

                {{-- Column & Bench Configuration Controls --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Room Columns</label>
                        <input type="number" name="columns" min="1" max="10" value="{{ $schedule->hall?->columns ?? 4 }}"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none bg-slate-50/50">
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Benches per Column</label>
                        <input type="number" name="benches_per_column" min="1" max="20" value="{{ $schedule->hall?->benches_per_column ?? 5 }}"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none bg-slate-50/50">
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Students per Bench</label>
                        <select name="students_per_bench" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm font-bold text-indigo-700 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none bg-indigo-50/40">
                            <option value="1" {{ ($schedule->hall?->students_per_bench ?? 2) == 1 ? 'selected' : '' }}>1 Student per Bench (Single Seat)</option>
                            <option value="2" {{ ($schedule->hall?->students_per_bench ?? 2) == 2 ? 'selected' : '' }}>2 Students per Bench (Left & Right)</option>
                            <option value="3" {{ ($schedule->hall?->students_per_bench ?? 2) == 3 ? 'selected' : '' }}>3 Students per Bench (Left, Center & Right)</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Placement Strategy</label>
                        <select name="strategy" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none bg-slate-50/50">
                            <option value="alphabetical">Alphabetical Order (A-Z)</option>
                            <option value="roll_number">Roll Number Sequence</option>
                            <option value="staggered">Staggered Anti-Cheating (Alternate Desks)</option>
                        </select>
                    </div>
                </div>

                {{-- Action Submit --}}
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" onclick="return confirm('Auto-generate seating plan for room benches? This will overwrite existing assignments.')"
                            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition flex items-center gap-2 shadow-sm">
                        <i class="bi bi-magic"></i> Auto-Generate Bench Seating
                    </button>
            </form>

            <form method="POST" action="{{ route('exam.hall.seats.clear', $schedule->id) }}" class="inline">
                @csrf @method('DELETE')
                <button type="submit" onclick="return confirm('Reset all seat allocations for this room?')"
                        class="px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-xl text-sm font-semibold transition flex items-center gap-1.5">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset Room Seating
                </button>
            </form>
                </div>
        </div>
        @endcan

        {{-- 2D Visual Room & Bench Grid Visualizer --}}
        @php
            $numCols = $schedule->hall?->columns ?? 4;
            $benchesPerCol = $schedule->hall?->benches_per_column ?? 5;
            $studentsPerBench = $schedule->hall?->students_per_bench ?? 2;
            
            // Group allocations by column_no and bench_no
            $allocatedByBench = [];
            foreach ($allocations as $a) {
                $cNo = $a->column_no ?? 1;
                $bNo = $a->bench_no ?? 1;
                $pNo = $a->bench_position ?? 1;
                $allocatedByBench["{$cNo}_{$bNo}_{$pNo}"] = $a;
            }
        @endphp

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 mb-8">
            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3 border-b border-slate-100 pb-4 mb-6">
                <div>
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <i class="bi bi-easel2-fill text-indigo-600"></i> Real-World Examination Room Visualizer
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Showing Columns, Benches & Allocated Student Roll Numbers</p>
                </div>
                <div class="flex items-center gap-2 text-xs font-semibold">
                    <span class="px-3 py-1 bg-indigo-50 text-indigo-700 rounded-full border border-indigo-100">
                        Total Allocated: {{ $allocations->count() }}
                    </span>
                    <span class="px-3 py-1 bg-slate-100 text-slate-700 rounded-full">
                        Capacity: {{ $studentsPerBench }} Students / Bench
                    </span>
                </div>
            </div>

            @if($allocations->isEmpty())
                <div class="p-12 text-center text-sm text-slate-400 bg-slate-50/60 rounded-2xl border border-dashed border-slate-200">
                    <i class="bi bi-grid-3x3 text-3xl text-slate-300 block mb-2"></i>
                    No seats allocated yet. Use the Bench Seating Generator above to select classes and auto-assign student Roll Numbers.
                </div>
            @else
                {{-- Column Grid Layout --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 overflow-x-auto">
                    @for($c = 1; $c <= $numCols; $c++)
                    <div class="bg-slate-50/80 rounded-2xl p-4 border border-slate-200/60">
                        <div class="text-center font-bold text-xs uppercase tracking-wider text-slate-600 bg-slate-200/60 rounded-xl py-1.5 mb-4">
                            Column {{ $c }}
                        </div>

                        <div class="space-y-4">
                            @for($b = 1; $b <= $benchesPerCol; $b++)
                            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-3 hover:border-indigo-300 transition">
                                <div class="flex justify-between items-center border-b border-slate-100 pb-1.5 mb-2">
                                    <span class="text-[11px] font-bold text-slate-500">Bench {{ $b }}</span>
                                    <span class="text-[10px] font-mono text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded">Col {{ $c }}</span>
                                </div>

                                {{-- Bench Positions (1, 2, or 3 seats) --}}
                                <div class="grid grid-cols-{{ $studentsPerBench }} gap-2">
                                    @for($p = 1; $p <= $studentsPerBench; $p++)
                                    @php
                                        $allocKey = "{$c}_{$b}_{$p}";
                                        $alloc = $allocatedByBench[$allocKey] ?? null;
                                        
                                        $posLabel = ($studentsPerBench == 1) ? 'Single' : (($p == 1) ? 'Left' : (($p == 2 && $studentsPerBench == 3) ? 'Center' : 'Right'));
                                    @endphp
                                    <div class="p-2 rounded-lg text-center {{ $alloc ? 'bg-indigo-50/80 border border-indigo-200' : 'bg-slate-50 border border-dashed border-slate-200 text-slate-300' }}">
                                        <div class="text-[9px] uppercase font-bold text-slate-400 mb-0.5">{{ $posLabel }}</div>
                                        @if($alloc)
                                            <div class="text-[11px] font-bold text-indigo-700 font-mono bg-white px-1 py-0.5 rounded border border-indigo-100 mb-1">
                                                Roll: {{ $alloc->student?->promotions?->first()?->id_card_number ?? '—' }}
                                            </div>
                                            <p class="text-[11px] font-bold text-slate-800 truncate" title="{{ $alloc->student?->full_name }}">
                                                {{ $alloc->student?->first_name }}
                                            </p>
                                            <p class="text-[9px] text-slate-500 truncate mt-0.5">
                                                {{ $alloc->student?->promotions?->first()?->schoolClass?->class_name ?? 'Student' }}
                                            </p>
                                        @else
                                            <div class="text-[10px] font-mono text-slate-300 py-1">[ Empty ]</div>
                                        @endif
                                    </div>
                                    @endfor
                                </div>
                            </div>
                            @endfor
                        </div>
                    </div>
                    @endfor
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Current Allocations Directory Table --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-100 flex justify-between items-center">
                    <p class="text-sm font-bold text-slate-700">Allocated Seats List</p>
                    <span class="text-xs bg-slate-100 text-slate-600 px-2.5 py-0.5 rounded-full font-semibold">{{ $allocations->count() }} Students Seated</span>
                </div>
                @if($allocations->isEmpty())
                <div class="p-6 text-center text-sm text-slate-400">No seats allocated yet.</div>
                @else
                <div class="overflow-x-auto max-h-96 overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="sticky top-0 bg-slate-50 text-xs text-slate-400 text-left border-b border-slate-100">
                            <tr>
                                <th class="px-4 py-2.5 font-medium">Bench Code</th>
                                <th class="px-4 py-2.5 font-medium">Roll No</th>
                                <th class="px-4 py-2.5 font-medium">Student Name</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($allocations as $a)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-2.5">
                                    <span class="font-mono font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded text-xs">{{ $a->seat_number }}</span>
                                </td>
                                <td class="px-4 py-2.5 font-mono font-semibold text-slate-700">
                                    {{ $a->student?->promotions?->first()?->id_card_number ?? '—' }}
                                </td>
                                <td class="px-4 py-2.5 font-medium text-slate-800">
                                    {{ $a->student?->full_name ?? '—' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            {{-- Manual Seat Assignment Form --}}
            @can('create exams')
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <p class="text-sm font-bold text-slate-700 mb-4">Manual Bench Override</p>
                <form method="POST" action="{{ route('exam.hall.seats.save', $schedule->id) }}" class="space-y-3">
                    @csrf
                    <div class="max-h-80 overflow-y-auto space-y-2 pr-1">
                        @foreach($students as $i => $promo)
                        @php
                            $existingAlloc = $allocations->firstWhere('student_id', $promo->student_id);
                        @endphp
                        <div class="flex items-center gap-3 bg-slate-50/50 p-2 rounded-xl border border-slate-100">
                            <input type="hidden" name="seats[{{ $i }}][student_id]" value="{{ $promo->student_id }}">
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-slate-800 truncate">{{ $promo->student?->full_name }}</p>
                                <p class="text-[10px] text-slate-400 font-mono">Roll: {{ $promo->id_card_number ?? '—' }} · {{ $promo->schoolClass?->class_name }}</p>
                            </div>
                            <input type="text" name="seats[{{ $i }}][seat_number]" value="{{ $existingAlloc?->seat_number }}" placeholder="e.g. C1-B1-P1"
                                   class="w-28 border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs font-mono font-bold text-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        </div>
                        @endforeach
                    </div>
                    @if($students->isEmpty())
                    <p class="text-sm text-slate-400">No students found for this class.</p>
                    @else
                    <button type="submit" class="mt-4 w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition shadow-sm">Save Bench Changes</button>
                    @endif
                </form>
            </div>
            @endcan
        </div>
    </div>
@endsection
