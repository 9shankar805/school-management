@extends('layouts.app')

@section('title', 'हाम्रो विद्यालय क्यालेन्डर (Nepali Patro & School Calendar)')

@section('content')
<div class="p-3 sm:p-5 lg:p-6 space-y-5 max-w-[1560px] mx-auto">

    @include('session-messages')

    {{-- ── Hamro Patro Header Banner ─────────────────────────────────── --}}
    <div class="rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-md relative overflow-hidden text-white"
         style="background: linear-gradient(135deg, #991b1b 0%, #dc2626 45%, #4338ca 100%); color: #ffffff !important;">
        <div class="absolute -right-8 -bottom-8 opacity-10 text-8xl font-black select-none pointer-events-none" style="color: #ffffff;">पात्रो</div>
        
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 relative z-10">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0 text-2xl shadow-inner border border-white/30"
                     style="background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(8px);">
                    <i class="bi bi-calendar2-heart-fill" style="color: #fef08a;"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="text-xl sm:text-2xl font-black leading-tight tracking-tight" style="color: #ffffff !important;">
                            हाम्रो पात्रो तथा विद्यालय क्यालेन्डर
                        </h1>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold shadow-xs"
                              style="background: #facc15; color: #0f172a !important;">
                            नेपाली पात्रो (BS) &amp; English (AD)
                        </span>
                    </div>
                    <div class="flex items-center gap-2 sm:gap-3 text-xs mt-1 flex-wrap" style="color: #fecdd3;">
                        <span class="font-bold flex items-center gap-1.5" style="color: #ffffff !important;">
                            <i class="bi bi-geo-alt-fill" style="color: #fde047;"></i> आज: <span id="top-live-bs-today" style="color: #ffffff !important;">नेपाली मिति लोड हुँदैछ...</span>
                        </span>
                        <span style="color: rgba(255,255,255,0.4);">|</span>
                        <span id="top-live-ad-today" class="font-semibold" style="color: #f1f5f9 !important;"></span>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                {{-- Print Wall Calendar --}}
                <a href="{{ route('events.print') }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-extrabold shadow-sm transition hover:scale-102"
                   style="background: #ffffff; color: #0f172a !important;">
                    <i class="bi bi-printer-fill" style="color: #dc2626;"></i> भित्ते पात्रो प्रिन्ट (Print)
                </a>

                @can('create events')
                {{-- Auto-Generate Nepal Academic Presets --}}
                <form action="{{ route('events.presets.generate') }}" method="POST" onsubmit="return confirm('के तपाईं यस शैक्षिक सत्रको लागि नेपाल सरकारका बिदाहरू, त्रैमासिक परीक्षा तथा मुख्य शैक्षिक कार्यक्रमहरू स्वतः लोड गर्न चाहनुहुन्छ?');" class="inline">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-extrabold shadow-sm transition hover:scale-102"
                            style="background: #fef08a; color: #713f12 !important;">
                        <i class="bi bi-magic" style="color: #854d0e;"></i> नेपाल बिदा लोड (Presets)
                    </button>
                </form>

                {{-- New Event Modal Trigger --}}
                <button onclick="openCreateEventModal()"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black shadow-sm transition hover:scale-102"
                        style="background: #0f172a; color: #ffffff !important; border: 1px solid rgba(255,255,255,0.25);">
                    <i class="bi bi-plus-circle-fill" style="color: #facc15;"></i> नयाँ कार्यक्रम / बिदा थप्नुहोस्
                </button>
                @endcan
            </div>
        </div>
    </div>

    {{-- ── Main Two-Column Layout (Exact Hamro Patro Structure) ───────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

        {{-- Left: Hamro Patro Calendar Grid (8 Cols) --}}
        <div class="lg:col-span-8 bg-white rounded-3xl border border-slate-200/80 shadow-md p-3 sm:p-5 overflow-hidden">
            @include('components.events.event-calendar', ['editable' => 'true', 'selectable' => 'true'])
        </div>

        {{-- Right: Tabbed Events & Festivals Feed (4 Cols) --}}
        <div class="lg:col-span-4 bg-white rounded-3xl border border-slate-200/80 shadow-md overflow-hidden flex flex-col">
            
            {{-- Tab Header (पर्वहरू | साइतहरू | आगामी बिदा) --}}
            <div class="bg-[#fcf1ee] border-b border-rose-100 flex items-center justify-between px-2 pt-2">
                <button type="button" onclick="switchPatroTab('festivals')" id="tab-btn-festivals"
                        class="hp-tab-btn flex-1 py-2.5 px-3 text-center text-xs font-extrabold text-rose-600 border-b-2 border-rose-600 transition">
                    पर्वहरू
                </button>
                <button type="button" onclick="switchPatroTab('saits')" id="tab-btn-saits"
                        class="hp-tab-btn flex-1 py-2.5 px-3 text-center text-xs font-bold text-slate-600 hover:text-slate-900 transition">
                    साइतहरू
                </button>
                <button type="button" onclick="switchPatroTab('holidays')" id="tab-btn-holidays"
                        class="hp-tab-btn flex-1 py-2.5 px-3 text-center text-xs font-bold text-slate-600 hover:text-slate-900 transition">
                    आगामी बिदा
                </button>
            </div>

            {{-- Tab Content Area --}}
            <div class="p-4 sm:p-5 space-y-4 max-h-[820px] overflow-y-auto" id="patro-tab-content">
                {{-- Dynamically Populated by JavaScript from NepaliDateService & FullCalendar --}}
                <div class="text-center py-8 text-slate-400 text-xs">
                    <div class="animate-spin inline-block w-5 h-5 border-2 border-rose-500 border-t-transparent rounded-full mb-2"></div>
                    <p>कार्यक्रम तथा पर्वहरू लोड हुँदैछ...</p>
                </div>
            </div>

            {{-- School Events Summary Footer --}}
            <div class="p-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs text-slate-600">
                <div class="flex items-center gap-1.5 font-bold">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-600"></span>
                    <span>कुल बिदा: <span class="text-rose-600 font-extrabold">{{ $totalHolidays }}</span></span>
                </div>
                <div class="flex items-center gap-1.5 font-bold">
                    <span class="w-2.5 h-2.5 rounded-full bg-purple-600"></span>
                    <span>परीक्षा: <span class="text-purple-600 font-extrabold">{{ $totalExams }}</span></span>
                </div>
                <div class="flex items-center gap-1.5 font-bold">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                    <span>कार्यक्रम: <span class="text-indigo-600 font-extrabold">{{ $totalEvents }}</span></span>
                </div>
            </div>

        </div>

    </div>

    @include('layouts.footer')
</div>
@endsection

