{{-- ── ACADEMIC HALL OF FAME: TOP 1ST, 2ND, 3RD RANK ACHIEVERS ── --}}
@if(!empty($topRankers) && ($topRankers['is_active'] ?? true) && (!empty($topRankers['overall']) || !empty($topRankers['by_class'])))
<section id="hall-of-fame" class="py-16 sm:py-24 bg-gradient-to-b from-slate-50 via-white to-slate-50 border-b border-slate-200 relative overflow-hidden">
    {{-- Ambient Decorative Elements --}}
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-full pointer-events-none overflow-hidden opacity-40">
        <div class="absolute -top-24 -left-20 w-96 h-96 bg-amber-200/40 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 -right-20 w-96 h-96 bg-blue-200/30 rounded-full blur-3xl"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">

        {{-- Section Header --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-4">
            <div>
                <span class="text-xs font-bold tracking-widest uppercase text-amber-800 bg-amber-50 px-3.5 py-1 rounded-md border border-amber-200 inline-flex items-center gap-1.5 shadow-xs mb-2">
                    <i class="bi bi-trophy-fill text-amber-500"></i> Academic Excellence &amp; Merit
                </span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 font-cinzel">
                    {{ $topRankers['title'] ?? 'Academic Hall of Fame · Top Rankers' }}
                </h2>
                <p class="text-slate-600 text-sm mt-1 max-w-2xl leading-relaxed">
                    {{ $topRankers['subtitle'] ?? 'Celebrating academic brilliance, diligence, and scholarly distinction across our classes and sections.' }}
                </p>
            </div>

            {{-- Auto-Updated Live Status Badge --}}
            <div class="flex items-center gap-2 shrink-0">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <i class="bi bi-patch-check-fill text-emerald-600"></i>
                    <span>Auto-Updated: {{ $topRankers['exam_term'] }}</span>
                </div>
            </div>
        </div>

        {{-- Class Filter Navigation Pills (If multiple classes exist) --}}
        @php
            $hasClasses = !empty($topRankers['classes']) && count($topRankers['classes']) > 1;
        @endphp

        @if($hasClasses)
        <div class="flex items-center gap-2 overflow-x-auto pb-4 mb-8 no-scrollbar">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider shrink-0 mr-1 flex items-center gap-1">
                <i class="bi bi-funnel-fill text-slate-400"></i> Filter Class:
            </span>
            @foreach($topRankers['classes'] as $idx => $classTab)
                <button type="button"
                        onclick="switchRankerClassTab('{{ $classTab['id'] }}')"
                        id="ranker-tab-btn-{{ $classTab['id'] }}"
                        class="ranker-tab-pill px-4 py-2 rounded-full text-xs font-bold transition-all shrink-0 cursor-pointer {{ $idx === 0 ? 'bg-[#8b151b] text-white shadow-md ring-2 ring-[#8b151b]/30' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200 shadow-xs' }}">
                    @if($classTab['id'] === 'overall')
                        <i class="bi bi-stars text-amber-300 mr-1"></i>
                    @else
                        <i class="bi bi-mortarboard mr-1"></i>
                    @endif
                    {{ $classTab['name'] }}
                </button>
            @endforeach
        </div>
        @endif

        {{-- ── RANKER PANELS ── --}}

        {{-- 1. Overall Campus Panel --}}
        @if(!empty($topRankers['overall']))
        <div id="ranker-panel-overall" class="ranker-class-panel">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8 items-end">
                @php
                    $overallList = $topRankers['overall'];
                    $firstRank  = collect($overallList)->firstWhere('rank', 1) ?? ($overallList[0] ?? null);
                    $secondRank = collect($overallList)->firstWhere('rank', 2) ?? ($overallList[1] ?? null);
                    $thirdRank  = collect($overallList)->firstWhere('rank', 3) ?? ($overallList[2] ?? null);
                @endphp

                {{-- Left: 2nd Rank (Silver) --}}
                @if($secondRank)
                <div class="order-2 lg:order-1 bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/90 shadow-md hover:shadow-xl transition-all duration-300 relative group flex flex-col justify-between">
                    <div>
                        {{-- Rank Header Ribbon --}}
                        <div class="flex items-center justify-between mb-5">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-gradient-to-r from-slate-200 to-slate-100 text-slate-800 border border-slate-300 shadow-xs">
                                <span class="text-sm">🥈</span> 2nd Rank · Silver Medalist
                            </span>
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Campus Merit</span>
                        </div>

                        {{-- Avatar --}}
                        <div class="relative w-24 h-24 mx-auto mb-4">
                            <img src="{{ $secondRank['student_photo'] }}"
                                 alt="{{ $secondRank['student_name'] }}"
                                 class="w-full h-full rounded-full object-cover border-4 border-slate-300 ring-4 ring-slate-100 shadow-md">
                            <span class="absolute bottom-0 right-0 w-7 h-7 bg-slate-700 text-white rounded-full flex items-center justify-center text-xs font-black shadow-sm">
                                2
                            </span>
                        </div>

                        {{-- Student Name --}}
                        <div class="text-center mb-4">
                            <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 font-cinzel group-hover:text-slate-700 transition">
                                {{ $secondRank['student_name'] }}
                            </h3>
                            <p class="text-xs text-slate-500 font-medium mt-0.5">{{ $secondRank['honors'] }}</p>
                        </div>

                        {{-- Class & Section Badges (Mandatory Requirement) --}}
                        <div class="flex items-center justify-center flex-wrap gap-2 mb-5">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-blue-50 text-blue-800 border border-blue-200 shadow-xs">
                                <i class="bi bi-mortarboard-fill text-blue-600"></i>
                                <span>Class: <strong>{{ $secondRank['class_name'] }}</strong></span>
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-indigo-50 text-indigo-800 border border-indigo-200 shadow-xs">
                                <i class="bi bi-people-fill text-indigo-600"></i>
                                <span>Section: <strong>{{ $secondRank['section_name'] }}</strong></span>
                            </span>
                        </div>

                        {{-- Score Grid --}}
                        <div class="grid grid-cols-2 gap-3 bg-slate-50 rounded-2xl p-3.5 border border-slate-200/80 mb-4">
                            <div class="text-center">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Score / GPA</span>
                                <span class="text-base font-black text-slate-800">{{ $secondRank['gpa'] }}</span>
                            </div>
                            <div class="text-center border-l border-slate-200">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Aggregate</span>
                                <span class="text-base font-black text-slate-800">{{ $secondRank['percentage'] }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                        <span><i class="bi bi-journal-check text-slate-500 mr-1"></i> {{ $secondRank['term_name'] }}</span>
                        <span class="font-bold text-slate-600">Distinction</span>
                    </div>
                </div>
                @endif

                {{-- Center: 1st Rank (Gold Champion - Highest Podium) --}}
                @if($firstRank)
                <div class="order-1 lg:order-2 bg-gradient-to-b from-amber-50/80 via-white to-amber-50/40 rounded-3xl p-7 sm:p-9 border-2 border-amber-400 shadow-2xl shadow-amber-500/15 hover:shadow-2xl hover:shadow-amber-500/25 transition-all duration-300 relative group flex flex-col justify-between lg:-translate-y-4">
                    {{-- Golden Laurel Wreath Crown Tag --}}
                    <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-gradient-to-r from-amber-500 via-amber-600 to-yellow-500 text-white font-extrabold text-[11px] uppercase tracking-wider px-4 py-1 rounded-full shadow-md flex items-center gap-1.5 whitespace-nowrap">
                        <i class="bi bi-trophy-fill text-yellow-200 text-xs"></i> CAMPUS TOPPER · 1ST RANK
                    </div>

                    <div>
                        {{-- Rank Header Ribbon --}}
                        <div class="flex items-center justify-between mb-5 mt-2">
                            <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-black bg-gradient-to-r from-amber-400 to-yellow-400 text-amber-950 border border-amber-500/50 shadow-xs">
                                <span class="text-sm">🥇</span> 1st Rank · Gold Champion
                            </span>
                            <span class="inline-flex items-center gap-1 text-[11px] font-black text-amber-700 uppercase tracking-wider bg-amber-100/80 px-2 py-0.5 rounded">
                                <i class="bi bi-star-fill text-amber-500"></i> Valedictorian
                            </span>
                        </div>

                        {{-- Avatar with Gold Ring --}}
                        <div class="relative w-28 h-28 mx-auto mb-4">
                            <img src="{{ $firstRank['student_photo'] }}"
                                 alt="{{ $firstRank['student_name'] }}"
                                 class="w-full h-full rounded-full object-cover border-4 border-amber-400 ring-4 ring-amber-300/50 shadow-lg">
                            <span class="absolute bottom-0 right-0 w-8 h-8 bg-gradient-to-br from-amber-500 to-yellow-500 text-amber-950 rounded-full flex items-center justify-center text-sm font-black shadow-md border-2 border-white">
                                1
                            </span>
                        </div>

                        {{-- Student Name --}}
                        <div class="text-center mb-4">
                            <h3 class="text-xl sm:text-2xl font-black text-slate-900 font-cinzel group-hover:text-amber-800 transition">
                                {{ $firstRank['student_name'] }}
                            </h3>
                            <p class="text-xs text-amber-800 font-semibold mt-0.5">{{ $firstRank['honors'] }}</p>
                        </div>

                        {{-- Class & Section Badges (Mandatory Requirement) --}}
                        <div class="flex items-center justify-center flex-wrap gap-2 mb-6">
                            <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-black bg-amber-100 text-amber-950 border border-amber-300 shadow-xs">
                                <i class="bi bi-mortarboard-fill text-amber-700"></i>
                                <span>Class: <strong>{{ $firstRank['class_name'] }}</strong></span>
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-black bg-blue-100 text-blue-950 border border-blue-300 shadow-xs">
                                <i class="bi bi-people-fill text-blue-700"></i>
                                <span>Section: <strong>{{ $firstRank['section_name'] }}</strong></span>
                            </span>
                        </div>

                        {{-- Score Grid --}}
                        <div class="grid grid-cols-2 gap-3 bg-gradient-to-r from-amber-50 to-yellow-50/60 rounded-2xl p-4 border border-amber-200 shadow-inner mb-4">
                            <div class="text-center">
                                <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-700 block">Cumulative GPA</span>
                                <span class="text-xl font-black text-amber-950">{{ $firstRank['gpa'] }}</span>
                            </div>
                            <div class="text-center border-l border-amber-200">
                                <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-700 block">Examination Aggregate</span>
                                <span class="text-xl font-black text-amber-950">{{ $firstRank['percentage'] }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="pt-3 border-t border-amber-100 flex items-center justify-between text-[11px] text-amber-800 font-medium">
                        <span><i class="bi bi-award-fill text-amber-600 mr-1"></i> {{ $firstRank['term_name'] }}</span>
                        <span class="font-extrabold text-amber-900 bg-amber-100 px-2 py-0.5 rounded">Highest Aggregate</span>
                    </div>
                </div>
                @endif

                {{-- Right: 3rd Rank (Bronze) --}}
                @if($thirdRank)
                <div class="order-3 lg:order-3 bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/90 shadow-md hover:shadow-xl transition-all duration-300 relative group flex flex-col justify-between">
                    <div>
                        {{-- Rank Header Ribbon --}}
                        <div class="flex items-center justify-between mb-5">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-gradient-to-r from-amber-100 to-amber-50 text-amber-900 border border-amber-300 shadow-xs">
                                <span class="text-sm">🥉</span> 3rd Rank · Bronze Medalist
                            </span>
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Campus Merit</span>
                        </div>

                        {{-- Avatar --}}
                        <div class="relative w-24 h-24 mx-auto mb-4">
                            <img src="{{ $thirdRank['student_photo'] }}"
                                 alt="{{ $thirdRank['student_name'] }}"
                                 class="w-full h-full rounded-full object-cover border-4 border-amber-600/70 ring-4 ring-amber-100 shadow-md">
                            <span class="absolute bottom-0 right-0 w-7 h-7 bg-amber-800 text-white rounded-full flex items-center justify-center text-xs font-black shadow-sm">
                                3
                            </span>
                        </div>

                        {{-- Student Name --}}
                        <div class="text-center mb-4">
                            <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 font-cinzel group-hover:text-amber-800 transition">
                                {{ $thirdRank['student_name'] }}
                            </h3>
                            <p class="text-xs text-slate-500 font-medium mt-0.5">{{ $thirdRank['honors'] }}</p>
                        </div>

                        {{-- Class & Section Badges (Mandatory Requirement) --}}
                        <div class="flex items-center justify-center flex-wrap gap-2 mb-5">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-blue-50 text-blue-800 border border-blue-200 shadow-xs">
                                <i class="bi bi-mortarboard-fill text-blue-600"></i>
                                <span>Class: <strong>{{ $thirdRank['class_name'] }}</strong></span>
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-indigo-50 text-indigo-800 border border-indigo-200 shadow-xs">
                                <i class="bi bi-people-fill text-indigo-600"></i>
                                <span>Section: <strong>{{ $thirdRank['section_name'] }}</strong></span>
                            </span>
                        </div>

                        {{-- Score Grid --}}
                        <div class="grid grid-cols-2 gap-3 bg-slate-50 rounded-2xl p-3.5 border border-slate-200/80 mb-4">
                            <div class="text-center">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Score / GPA</span>
                                <span class="text-base font-black text-slate-800">{{ $thirdRank['gpa'] }}</span>
                            </div>
                            <div class="text-center border-l border-slate-200">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Aggregate</span>
                                <span class="text-base font-black text-slate-800">{{ $thirdRank['percentage'] }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                        <span><i class="bi bi-journal-check text-slate-500 mr-1"></i> {{ $thirdRank['term_name'] }}</span>
                        <span class="font-bold text-slate-600">Honors</span>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- 2. Class-Specific Panels (Dynamically Switched on Tab Click) --}}
        @if(!empty($topRankers['by_class']))
            @foreach($topRankers['by_class'] as $cId => $classData)
                @php
                    $cRankers = $classData['rankers'];
                    $c1 = collect($cRankers)->firstWhere('rank', 1) ?? ($cRankers[0] ?? null);
                    $c2 = collect($cRankers)->firstWhere('rank', 2) ?? ($cRankers[1] ?? null);
                    $c3 = collect($cRankers)->firstWhere('rank', 3) ?? ($cRankers[2] ?? null);
                @endphp

                <div id="ranker-panel-{{ $cId }}" class="ranker-class-panel hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8 items-end">
                        
                        {{-- 2nd Rank --}}
                        @if($c2)
                        <div class="order-2 lg:order-1 bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/90 shadow-md hover:shadow-xl transition-all duration-300 relative group flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-5">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-gradient-to-r from-slate-200 to-slate-100 text-slate-800 border border-slate-300 shadow-xs">
                                        <span class="text-sm">🥈</span> 2nd Rank · Silver
                                    </span>
                                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ $classData['class_name'] }}</span>
                                </div>

                                <div class="relative w-24 h-24 mx-auto mb-4">
                                    <img src="{{ $c2['student_photo'] }}" alt="{{ $c2['student_name'] }}" class="w-full h-full rounded-full object-cover border-4 border-slate-300 ring-4 ring-slate-100 shadow-md">
                                    <span class="absolute bottom-0 right-0 w-7 h-7 bg-slate-700 text-white rounded-full flex items-center justify-center text-xs font-black shadow-sm">2</span>
                                </div>

                                <div class="text-center mb-4">
                                    <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 font-cinzel">{{ $c2['student_name'] }}</h3>
                                    <p class="text-xs text-slate-500 font-medium mt-0.5">{{ $c2['honors'] }}</p>
                                </div>

                                <div class="flex items-center justify-center flex-wrap gap-2 mb-5">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-blue-50 text-blue-800 border border-blue-200 shadow-xs">
                                        <i class="bi bi-mortarboard-fill text-blue-600"></i>
                                        <span>Class: <strong>{{ $c2['class_name'] }}</strong></span>
                                    </span>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-indigo-50 text-indigo-800 border border-indigo-200 shadow-xs">
                                        <i class="bi bi-people-fill text-indigo-600"></i>
                                        <span>Section: <strong>{{ $c2['section_name'] }}</strong></span>
                                    </span>
                                </div>

                                <div class="grid grid-cols-2 gap-3 bg-slate-50 rounded-2xl p-3.5 border border-slate-200/80 mb-4">
                                    <div class="text-center">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">GPA</span>
                                        <span class="text-base font-black text-slate-800">{{ $c2['gpa'] }}</span>
                                    </div>
                                    <div class="text-center border-l border-slate-200">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Percentage</span>
                                        <span class="text-base font-black text-slate-800">{{ $c2['percentage'] }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                                <span><i class="bi bi-journal-check mr-1"></i> {{ $c2['term_name'] }}</span>
                                <span class="font-bold text-slate-600">Distinction</span>
                            </div>
                        </div>
                        @endif

                        {{-- 1st Rank --}}
                        @if($c1)
                        <div class="order-1 lg:order-2 bg-gradient-to-b from-amber-50/80 via-white to-amber-50/40 rounded-3xl p-7 sm:p-9 border-2 border-amber-400 shadow-2xl shadow-amber-500/15 hover:shadow-2xl hover:shadow-amber-500/25 transition-all duration-300 relative group flex flex-col justify-between lg:-translate-y-4">
                            <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-gradient-to-r from-amber-500 via-amber-600 to-yellow-500 text-white font-extrabold text-[11px] uppercase tracking-wider px-4 py-1 rounded-full shadow-md flex items-center gap-1.5 whitespace-nowrap">
                                <i class="bi bi-trophy-fill text-yellow-200 text-xs"></i> {{ strtoupper($classData['class_name']) }} TOPPER
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-5 mt-2">
                                    <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-black bg-gradient-to-r from-amber-400 to-yellow-400 text-amber-950 border border-amber-500/50 shadow-xs">
                                        <span class="text-sm">🥇</span> 1st Rank · Topper
                                    </span>
                                    <span class="text-[11px] font-black text-amber-700 uppercase tracking-wider bg-amber-100/80 px-2 py-0.5 rounded">
                                        Rank #1
                                    </span>
                                </div>

                                <div class="relative w-28 h-28 mx-auto mb-4">
                                    <img src="{{ $c1['student_photo'] }}" alt="{{ $c1['student_name'] }}" class="w-full h-full rounded-full object-cover border-4 border-amber-400 ring-4 ring-amber-300/50 shadow-lg">
                                    <span class="absolute bottom-0 right-0 w-8 h-8 bg-gradient-to-br from-amber-500 to-yellow-500 text-amber-950 rounded-full flex items-center justify-center text-sm font-black shadow-md border-2 border-white">
                                        1
                                    </span>
                                </div>

                                <div class="text-center mb-4">
                                    <h3 class="text-xl sm:text-2xl font-black text-slate-900 font-cinzel">{{ $c1['student_name'] }}</h3>
                                    <p class="text-xs text-amber-800 font-semibold mt-0.5">{{ $c1['honors'] }}</p>
                                </div>

                                <div class="flex items-center justify-center flex-wrap gap-2 mb-6">
                                    <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-black bg-amber-100 text-amber-950 border border-amber-300 shadow-xs">
                                        <i class="bi bi-mortarboard-fill text-amber-700"></i>
                                        <span>Class: <strong>{{ $c1['class_name'] }}</strong></span>
                                    </span>
                                    <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-black bg-blue-100 text-blue-950 border border-blue-300 shadow-xs">
                                        <i class="bi bi-people-fill text-blue-700"></i>
                                        <span>Section: <strong>{{ $c1['section_name'] }}</strong></span>
                                    </span>
                                </div>

                                <div class="grid grid-cols-2 gap-3 bg-gradient-to-r from-amber-50 to-yellow-50/60 rounded-2xl p-4 border border-amber-200 shadow-inner mb-4">
                                    <div class="text-center">
                                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-700 block">GPA</span>
                                        <span class="text-xl font-black text-amber-950">{{ $c1['gpa'] }}</span>
                                    </div>
                                    <div class="text-center border-l border-amber-200">
                                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-700 block">Aggregate</span>
                                        <span class="text-xl font-black text-amber-950">{{ $c1['percentage'] }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-3 border-t border-amber-100 flex items-center justify-between text-[11px] text-amber-800 font-medium">
                                <span><i class="bi bi-award-fill text-amber-600 mr-1"></i> {{ $c1['term_name'] }}</span>
                                <span class="font-extrabold text-amber-900 bg-amber-100 px-2 py-0.5 rounded">Top of Class</span>
                            </div>
                        </div>
                        @endif

                        {{-- 3rd Rank --}}
                        @if($c3)
                        <div class="order-3 lg:order-3 bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/90 shadow-md hover:shadow-xl transition-all duration-300 relative group flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-5">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-gradient-to-r from-amber-100 to-amber-50 text-amber-900 border border-amber-300 shadow-xs">
                                        <span class="text-sm">🥉</span> 3rd Rank · Bronze
                                    </span>
                                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ $classData['class_name'] }}</span>
                                </div>

                                <div class="relative w-24 h-24 mx-auto mb-4">
                                    <img src="{{ $c3['student_photo'] }}" alt="{{ $c3['student_name'] }}" class="w-full h-full rounded-full object-cover border-4 border-amber-600/70 ring-4 ring-amber-100 shadow-md">
                                    <span class="absolute bottom-0 right-0 w-7 h-7 bg-amber-800 text-white rounded-full flex items-center justify-center text-xs font-black shadow-sm">3</span>
                                </div>

                                <div class="text-center mb-4">
                                    <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 font-cinzel">{{ $c3['student_name'] }}</h3>
                                    <p class="text-xs text-slate-500 font-medium mt-0.5">{{ $c3['honors'] }}</p>
                                </div>

                                <div class="flex items-center justify-center flex-wrap gap-2 mb-5">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-blue-50 text-blue-800 border border-blue-200 shadow-xs">
                                        <i class="bi bi-mortarboard-fill text-blue-600"></i>
                                        <span>Class: <strong>{{ $c3['class_name'] }}</strong></span>
                                    </span>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-indigo-50 text-indigo-800 border border-indigo-200 shadow-xs">
                                        <i class="bi bi-people-fill text-indigo-600"></i>
                                        <span>Section: <strong>{{ $c3['section_name'] }}</strong></span>
                                    </span>
                                </div>

                                <div class="grid grid-cols-2 gap-3 bg-slate-50 rounded-2xl p-3.5 border border-slate-200/80 mb-4">
                                    <div class="text-center">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">GPA</span>
                                        <span class="text-base font-black text-slate-800">{{ $c3['gpa'] }}</span>
                                    </div>
                                    <div class="text-center border-l border-slate-200">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Percentage</span>
                                        <span class="text-base font-black text-slate-800">{{ $c3['percentage'] }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                                <span><i class="bi bi-journal-check mr-1"></i> {{ $c3['term_name'] }}</span>
                                <span class="font-bold text-slate-600">Honors</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            @endforeach
        @endif

        {{-- Explanatory / Merit Policy Footer Notice --}}
        <div class="mt-12 bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center shrink-0 text-xl font-bold border border-amber-200">
                    <i class="bi bi-shield-shaded"></i>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Automated Academic Merit Policy</h4>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Ranks are automatically evaluated according to Nepal Curriculum Development Centre (CDC) standards and terminal grade sheets.
                    </p>
                </div>
            </div>
            <a href="{{ $pageUrl('academics') }}" class="btn-primary-dark !text-xs !py-2 !px-4 shrink-0 flex items-center gap-1.5">
                <span>View Academic Programs</span> &rarr;
            </a>
        </div>

    </div>
</section>

{{-- Interactive Ranker Tab Switching Script --}}
<script>
function switchRankerClassTab(classId) {
    // Hide all panels
    document.querySelectorAll('.ranker-class-panel').forEach(panel => {
        panel.classList.add('hidden');
    });

    // Reset button styles
    document.querySelectorAll('.ranker-tab-pill').forEach(btn => {
        btn.className = "ranker-tab-pill px-4 py-2 rounded-full text-xs font-bold transition-all shrink-0 cursor-pointer bg-white text-slate-700 hover:bg-slate-100 border border-slate-200 shadow-xs";
    });

    // Activate selected panel
    const targetPanel = document.getElementById('ranker-panel-' + classId);
    if (targetPanel) {
        targetPanel.classList.remove('hidden');
    }

    // Activate button
    const targetBtn = document.getElementById('ranker-tab-btn-' + classId);
    if (targetBtn) {
        targetBtn.className = "ranker-tab-pill px-4 py-2 rounded-full text-xs font-bold transition-all shrink-0 cursor-pointer bg-[#8b151b] text-white shadow-md ring-2 ring-[#8b151b]/30";
    }
}
</script>
@endif

