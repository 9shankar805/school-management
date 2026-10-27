{{-- FullCalendar v6 assets --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">

{{-- ── Hamro Patro Custom Top Bar ────────────────────────────────────────── --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3.5 mb-2 border-b border-slate-100 relative">
    {{-- Month Title & Dropdown Trigger --}}
    <div class="relative">
        <button type="button" onclick="togglePatroMonthPicker()" id="hp-month-dropdown-btn"
                class="flex items-baseline gap-2.5 text-left group focus:outline-none select-none cursor-pointer">
            <span id="hp-active-month-title" class="text-2xl sm:text-3xl font-black transition tracking-tight" style="color: #0f172a !important;">
                असोज २०८३
            </span>
            <span id="hp-active-ad-subtitle" class="text-xs sm:text-sm font-semibold transition" style="color: #64748b !important;">
                Ashwin - Sep/Oct 2026
            </span>
            <i class="bi bi-chevron-down text-sm transition" style="color: #94a3b8;"></i>
        </button>

        {{-- Month & Year Picker Popover --}}
        <div id="hp-month-picker-popover" class="hidden absolute top-full left-0 mt-2 z-[250] bg-white rounded-2xl shadow-2xl border border-slate-200 p-4 w-80 space-y-3" style="background: #ffffff !important;">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <span class="text-xs font-bold" style="color: #334155;">वर्ष छनौट (BS Year):</span>
                <div class="flex items-center gap-1">
                    @foreach([2081, 2082, 2083, 2084, 2085] as $y)
                    <button type="button" onclick="selectPatroYear({{ $y }})" class="hp-year-btn px-2 py-0.5 rounded text-xs font-bold transition {{ $y == 2083 ? 'bg-rose-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}" data-year="{{ $y }}">
                        {{ $y }}
                    </button>
                    @endforeach
                </div>
            </div>
            <div>
                <span class="text-xs font-bold block mb-2" style="color: #334155;">महिना (Month):</span>
                <div class="grid grid-cols-3 gap-1.5 text-xs font-bold">
                    @php
                        $mList = [
                            1 => 'बैशाख', 2 => 'जेठ', 3 => 'असार', 4 => 'श्रावण',
                            5 => 'भाद्र', 6 => 'असोज', 7 => 'कात्तिक', 8 => 'मंसिर',
                            9 => 'पौष', 10 => 'माघ', 11 => 'फाल्गुन', 12 => 'चैत्र'
                        ];
                    @endphp
                    @foreach($mList as $num => $m)
                    <button type="button" onclick="selectPatroMonth({{ $num }})" class="hp-month-btn py-2 px-2 rounded-xl text-center border border-slate-100 hover:border-rose-300 hover:bg-rose-50 hover:text-rose-700 transition" data-month="{{ $num }}" style="color: #1e293b;">
                        {{ $m }}
                    </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Controls: Today (आज), Prev (<), Next (>) --}}
    <div class="flex items-center gap-1.5 self-end sm:self-auto">
        <button type="button" onclick="goToPatroToday()"
                class="px-4 py-1.5 rounded-xl font-black text-xs shadow-xs hover:scale-102 active:scale-98 transition"
                style="background: #dc2626 !important; color: #ffffff !important;">
            आज
        </button>
        <button type="button" onclick="prevPatroMonth()"
                class="w-8 h-8 rounded-xl border border-slate-200 flex items-center justify-center text-sm shadow-xs transition hover:bg-slate-100"
                style="background: #ffffff; color: #334155;"
                title="अघिल्लो महिना (Previous)">
            <i class="bi bi-chevron-left"></i>
        </button>
        <button type="button" onclick="nextPatroMonth()"
                class="w-8 h-8 rounded-xl border border-slate-200 flex items-center justify-center text-sm shadow-xs transition hover:bg-slate-100"
                style="background: #ffffff; color: #334155;"
                title="पछिल्लो महिना (Next)">
            <i class="bi bi-chevron-right"></i>
        </button>
    </div>
</div>

{{-- Calendar wrapper --}}
<div id="fc-wrapper" class="rounded-2xl overflow-hidden" style="background:#fff">
    <div id="calendar-el"></div>
</div>

{{-- ── Event Detail / View Modal ─────────────────────────────────────── --}}
<div id="event-view-modal" class="fixed inset-0 z-[300] hidden items-center justify-center p-4" style="background:rgba(15,23,42,0.6);backdrop-filter:blur(5px)">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden border border-slate-100">
        <div id="evm-header" class="px-6 py-4 flex items-center justify-between text-white" style="background:#4f46e5">
            <div class="flex items-center gap-3 min-w-0">
                <span class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="bi bi-calendar-event text-white text-base"></i>
                </span>
                <div class="min-w-0">
                    <h3 id="evm-title" class="font-bold text-white text-base truncate"></h3>
                    <span id="evm-cat-badge" class="inline-block px-2 py-0.2 rounded text-[10px] font-bold uppercase bg-white/25 text-white mt-0.5"></span>
                </div>
            </div>
            <button onclick="closeEventViewModal()" class="w-8 h-8 rounded-xl bg-white/20 hover:bg-white/30 flex items-center justify-center transition">
                <i class="bi bi-x text-white text-xl leading-none"></i>
            </button>
        </div>

        <div class="px-6 py-5 space-y-3.5 text-xs">
            <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100">
                <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center flex-shrink-0">
                    <i class="bi bi-calendar3 text-sm"></i>
                </span>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">मिति (Date &amp; Schedule)</p>
                    <p id="evm-date" class="text-xs font-bold text-slate-800 mt-0.5"></p>
                    <p id="evm-bs-date" class="text-[11px] font-semibold text-indigo-600 mt-0.5"></p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">सत्र (Academic Term)</p>
                    <p id="evm-term" class="text-xs font-semibold text-slate-700 mt-0.5">-</p>
                </div>
                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">लक्षित समूह (Audience)</p>
                    <p id="evm-audience" class="text-xs font-semibold text-slate-700 mt-0.5 capitalize">-</p>
                </div>
            </div>

            <div id="evm-venue-row" class="flex items-center gap-2.5 p-2.5 rounded-xl bg-slate-50 border border-slate-100 hidden">
                <i class="bi bi-geo-alt-fill text-rose-500"></i>
                <span class="text-slate-500">स्थान (Venue):</span>
                <strong id="evm-venue" class="text-slate-800"></strong>
            </div>

            <div id="evm-desc-row" class="p-3 rounded-xl bg-slate-50 border border-slate-100 hidden">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">विवरण (Description)</p>
                <p id="evm-desc" class="text-xs text-slate-600 leading-relaxed"></p>
            </div>
        </div>

        <div id="evm-actions" class="px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex gap-2 justify-end hidden">
            <button id="evm-edit-btn" onclick="openEditFromView()" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition">
                <i class="bi bi-pencil"></i> सम्पादन (Edit)
            </button>
            <button id="evm-delete-btn" onclick="deleteEventFromView()" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-rose-50 text-rose-600 hover:bg-rose-100 transition">
                <i class="bi bi-trash3"></i> मेटाउनुहोस् (Delete)
            </button>
        </div>
    </div>
</div>

{{-- ── Create / Edit Event Modal ─────────────────────────────────────── --}}
<div id="event-form-modal" class="fixed inset-0 z-[300] hidden items-center justify-center p-4" style="background:rgba(15,23,42,0.6);backdrop-filter:blur(5px)">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden border border-slate-100">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center">
                    <i class="bi bi-calendar-plus text-sm"></i>
                </span>
                <h3 id="efm-title" class="font-bold text-slate-800 text-sm">नयाँ कार्यक्रम / बिदा थप्नुहोस्</h3>
            </div>
            <button onclick="closeEventFormModal()" class="w-7 h-7 rounded-lg hover:bg-slate-200 flex items-center justify-center transition text-slate-400 hover:text-slate-600">
                <i class="bi bi-x text-lg leading-none"></i>
            </button>
        </div>

        <form id="event-form" onsubmit="submitEventForm(event)" class="px-6 py-5 space-y-3.5 text-xs">
            <input type="hidden" id="efm-event-id">
            <input type="hidden" id="efm-mode" value="create">

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">कार्यक्रम / बिदाको शिर्षक (Event Title) <span class="text-rose-500">*</span></label>
                <input id="efm-event-title" type="text" placeholder="उदा: प्रथम त्रैमासिक परीक्षा / बडा दशैं बिदा / खेलकुद सप्ताह"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-300 focus:border-rose-400" required>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">वर्ग (Category) *</label>
                    <select id="efm-category" onchange="handleCategoryChange(this.value)" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-rose-300">
                        <option value="academic">शैक्षिक कार्यक्रम (Academic)</option>
                        <option value="holiday">सार्वजनिक / राष्ट्रिय बिदा (Holiday)</option>
                        <option value="vacation">लामो बिदा (Vacation Break)</option>
                        <option value="exam">परीक्षा तालिका (Examinations)</option>
                        <option value="sports">खेलकुद तथा ECA (Sports)</option>
                        <option value="celebration">उत्सव तथा जयन्ती (Celebrations)</option>
                        <option value="meeting">बैठक / PTM (Meetings)</option>
                        <option value="other">अन्य (General)</option>
                    </select>
                </div>
                <div class="flex items-center pt-5">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="efm-is-holiday" class="rounded text-rose-600 focus:ring-rose-500 w-4 h-4">
                        <span class="font-semibold text-rose-700">विद्यालय बन्द रहने बिदा (School Holiday)</span>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">सुरु मिति तथा समय (Start Date) <span class="text-rose-500">*</span></label>
                    <input id="efm-start" type="datetime-local"
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-800 focus:ring-2 focus:ring-rose-300" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">अन्तिम मिति (End Date)</label>
                    <input id="efm-end" type="datetime-local"
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-800 focus:ring-2 focus:ring-rose-300">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">सत्र (Term)</label>
                    <select id="efm-term" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-rose-300">
                        <option value="First Term">First Term (प्रथम त्रैमासिक)</option>
                        <option value="Mid Term">Mid Term (अर्धवार्षिक)</option>
                        <option value="Second Term">Second Term (दोस्रो त्रैमासिक)</option>
                        <option value="Final Term">Final Term (वार्षिक अन्तिम)</option>
                        <option value="Annual">Annual (वार्षिक)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">लक्षित समूह</label>
                    <select id="efm-audience" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-rose-300">
                        <option value="all">सबै (All)</option>
                        <option value="students">विद्यार्थी (Students)</option>
                        <option value="teachers">शिक्षक वर्ग (Teachers)</option>
                        <option value="parents">अभिभावक (Parents)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">स्थान (Venue)</label>
                    <input id="efm-venue" type="text" placeholder="उदा: सभाहल, खेलमैदान"
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-rose-300">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">थप विवरण (Description)</label>
                <textarea id="efm-description" rows="2" placeholder="कार्यक्रम सम्बन्धी जानकारी वा परिपत्र…"
                    class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-rose-300 resize-none"></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">रङ्ग (Theme Color)</label>
                <div class="flex items-center gap-2 flex-wrap">
                    @foreach(['#dc2626','#ea580c','#9333ea','#4f46e5','#16a34a','#db2777','#0891b2','#475569'] as $clr)
                    <button type="button" onclick="selectEventColor('{{ $clr }}')"
                        data-color="{{ $clr }}"
                        class="color-swatch w-6 h-6 rounded-full border-2 border-transparent transition hover:scale-110 focus:outline-none"
                        style="background:{{ $clr }}"></button>
                    @endforeach
                    <input id="efm-color-custom" type="color" value="#dc2626"
                        onchange="selectEventColor(this.value)"
                        class="w-6 h-6 rounded-full border border-slate-200 cursor-pointer p-0 overflow-hidden"
                        title="Custom color">
                </div>
                <input type="hidden" id="efm-color" value="#dc2626">
            </div>

            <div class="flex gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeEventFormModal()"
                    class="flex-1 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                    रद्द (Cancel)
                </button>
                <button type="submit" id="efm-submit-btn"
                    class="flex-1 py-2 rounded-xl text-xs font-bold text-white transition shadow-sm bg-rose-600 hover:bg-rose-700">
                    सुरक्षित गर्नुहोस् (Save Event)
                </button>
            </div>
        </form>
    </div>
</div>

{{-- FullCalendar v6 JS --}}
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

<style>
/* ── Exact Hamro Patro Style Sheet ────────────────────────────────────────── */
#fc-wrapper .fc { font-family: inherit; }
#fc-wrapper .fc-header-toolbar { display: none !important; } /* We use our custom Hamro Patro top bar */
#fc-wrapper .fc-scrollgrid { border: 1px solid #f1f5f9 !important; border-radius: 1rem; overflow: hidden; }
#fc-wrapper .fc-scrollgrid td, #fc-wrapper .fc-scrollgrid th { border-color: #f1f5f9 !important; }

/* Weekday header row */
#fc-wrapper .fc-col-header-cell { background: #fff; padding: 0.6rem 0; border-bottom: 1px solid #e2e8f0 !important; }
#fc-wrapper .fc-col-header-cell-cushion { font-size: 0.85rem; font-weight: 800; color: #475569; padding: 0.2rem 0; text-decoration: none !important; }
#fc-wrapper th.fc-day-sat .fc-col-header-cell-cushion { color: #dc2626 !important; font-weight: 900; }

/* Day Cells */
#fc-wrapper .fc-daygrid-day { cursor: pointer; min-height: 96px; transition: background 0.12s ease; position: relative; }
#fc-wrapper .fc-daygrid-day:hover { background: #fafafa; }
#fc-wrapper .fc-daygrid-day-frame { min-height: 96px; padding: 0.25rem 0.35rem; display: flex; flex-direction: column; justify-content: space-between; }
#fc-wrapper .fc-daygrid-day-top { flex-direction: row; justify-content: space-between; width: 100%; margin-bottom: 2px; }
#fc-wrapper .fc-daygrid-day-number { width: 100%; text-decoration: none !important; padding: 0 !important; }
#fc-wrapper .fc-daygrid-day-events { display: none !important; } /* Events are integrated directly in day cell text */

/* Hamro Patro Cell Elements */
.hp-cell-container { display: flex; flex-direction: column; justify-content: space-between; height: 100%; min-height: 86px; width: 100%; }
.hp-cell-top { display: flex; align-items: flex-start; justify-content: space-between; width: 100%; }
.hp-ad-num { font-size: 11px; font-weight: 600; color: #64748b; margin-left: auto; line-height: 1; }
.hp-event-text { font-size: 10px; line-height: 1.2; font-weight: 700; margin-top: 1px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; }
.hp-bs-num { font-size: 24px; font-weight: 900; line-height: 1.1; text-align: center; margin: 2px 0; }
.hp-tithi-text { font-size: 10px; font-weight: 500; text-align: center; line-height: 1; }

/* Today Cell (Green solid block matching screenshot) */
#fc-wrapper .fc-day-today { background: transparent !important; }
#fc-wrapper .fc-day-today .hp-cell-container { background: #15803d !important; border-radius: 0.75rem; padding: 0.35rem 0.35rem; color: #ffffff !important; box-shadow: 0 4px 12px rgba(21, 128, 61, 0.3); }
#fc-wrapper .fc-day-today .hp-ad-num { color: #dcfce7 !important; }
#fc-wrapper .fc-day-today .hp-event-text { color: #ffffff !important; font-weight: 800; }
#fc-wrapper .fc-day-today .hp-bs-num { color: #ffffff !important; }
#fc-wrapper .fc-day-today .hp-tithi-text { color: #bbf7d0 !important; }

/* Saturday styling */
#fc-wrapper td.fc-day-sat { background: #fffbfb; }

/* Modal helpers */
.modal-show { display: flex !important; animation: modalIn .18s ease; }
@keyframes modalIn { from { opacity:0; transform:scale(.96) } to { opacity:1; transform:scale(1) } }
.color-swatch.active { border-color: #1e293b !important; box-shadow: 0 0 0 2px #fff, 0 0 0 4px #1e293b; transform: scale(1.15); }
</style>

<script>
(function () {
    'use strict';

    var SITEURL = "{{ url('/') }}";
    var CSRF    = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var IS_EDITABLE = {{ ($editable ?? 'false') === 'true' ? 'true' : 'false' }};

    var calendarEl = document.getElementById('calendar-el');
    var calendar;
    var viewingEvent = null;
    var currentLoadedEvents = [];
    var currentPatroTab = 'festivals';
    var activeBsYear = 2083;
    var activeBsMonth = 6; // Default to Ashoj / current month

    // ── Nepali (Bikram Sambat) Engine & Festival Dataset ────────────────────
    var NepaliDateConverter = {
        bsData: [
            [30,32,31,32,31,30,30,30,29,30,29,31], // 2000
            [31,31,32,31,31,31,30,29,30,29,30,30],
            [31,31,32,32,31,30,30,29,30,29,30,30],
            [31,32,31,32,31,30,30,30,29,29,30,31],
            [30,32,31,32,31,30,30,30,29,30,29,31],
            [31,31,32,31,31,31,30,29,30,29,30,30],
            [31,31,32,32,31,30,30,29,30,29,30,30],
            [31,32,31,32,31,30,30,30,29,29,30,31],
            [31,31,31,32,31,31,29,30,30,29,29,31],
            [31,31,32,31,31,31,30,29,30,29,30,30],
            [31,31,32,32,31,30,30,29,30,29,30,30], // 2010
            [31,32,31,32,31,30,30,30,29,29,30,31],
            [31,31,31,32,31,31,29,30,30,29,30,30],
            [31,31,32,31,31,31,30,29,30,29,30,30],
            [31,31,32,32,31,30,30,29,30,29,30,30],
            [31,32,31,32,31,30,30,30,29,29,30,31],
            [31,31,31,32,31,31,29,30,30,29,30,30],
            [31,31,32,31,31,31,30,29,30,29,30,30],
            [31,31,32,32,31,30,30,29,30,29,30,30],
            [31,32,31,32,31,30,30,30,29,29,30,31],
            [31,31,31,32,31,31,29,30,30,29,30,30], // 2020
            [31,31,32,31,31,31,30,29,30,29,30,30],
            [31,31,32,32,31,30,30,29,30,29,30,30],
            [31,32,31,32,31,30,30,30,29,29,30,31],
            [31,31,31,32,31,31,29,30,30,29,30,30],
            [31,31,32,31,31,31,30,29,30,29,30,30],
            [31,31,32,32,31,30,30,29,30,29,30,30],
            [31,32,31,32,31,30,30,30,29,29,30,31],
            [31,31,31,32,31,31,29,30,30,29,30,30],
            [31,31,32,31,31,31,30,29,30,29,30,30],
            [31,31,32,32,31,30,30,29,30,29,30,30], // 2030
            [31,32,31,32,31,30,30,30,29,29,30,31],
            [31,31,31,32,31,31,29,30,30,29,30,30],
            [31,31,32,31,31,31,30,29,30,29,30,30],
            [31,31,32,32,31,30,30,29,30,29,30,30],
            [31,32,31,32,31,30,30,30,29,29,30,31],
            [31,31,31,32,31,31,29,30,30,29,30,30],
            [31,31,32,31,31,31,30,29,30,29,30,30],
            [31,31,32,32,31,30,30,29,30,29,30,30],
            [31,32,31,32,31,30,30,30,29,29,30,31],
            [31,31,31,32,31,31,29,30,30,29,30,30], // 2040
            [31,31,32,31,31,31,30,29,30,29,30,30],
            [31,31,32,32,31,30,30,29,30,29,30,30],
            [31,32,31,32,31,30,30,30,29,29,30,31],
            [31,31,31,32,31,31,29,30,30,29,30,30],
            [31,31,32,31,31,31,30,29,30,29,30,30],
            [31,31,32,32,31,30,30,29,30,29,30,30],
            [31,32,31,32,31,30,30,30,29,29,30,31],
            [31,31,31,32,31,31,29,30,30,29,30,30],
            [31,31,32,31,31,31,30,29,30,29,30,30],
            [31,31,32,32,31,30,30,29,30,29,30,30], // 2050
            [31,32,31,32,31,30,30,30,29,29,30,31],
            [31,31,31,32,31,31,29,30,30,29,30,30],
            [31,31,32,31,31,31,30,29,30,29,30,30],
            [31,31,32,32,31,30,30,29,30,29,30,30],
            [31,32,31,32,31,30,30,30,29,29,30,31],
            [31,31,31,32,31,31,29,30,30,29,30,30],
            [31,31,32,31,31,31,30,29,30,29,30,30],
            [31,31,32,32,31,30,30,29,30,29,30,30],
            [31,32,31,32,31,30,30,30,29,29,30,31],
            [31,31,31,32,31,31,29,30,30,29,30,30], // 2060
            [31,31,32,31,31,31,30,29,30,29,30,30],
            [31,31,32,32,31,30,30,29,30,29,30,30],
            [31,32,31,32,31,30,30,30,29,29,30,31],
            [31,31,31,32,31,31,29,30,30,29,30,30],
            [31,31,32,31,31,31,30,29,30,29,30,30],
            [31,31,32,32,31,30,30,29,30,29,30,30],
            [31,32,31,32,31,30,30,30,29,29,30,31],
            [31,31,31,32,31,31,29,30,30,29,30,30],
            [31,31,32,31,31,31,30,29,30,29,30,30],
            [31,31,32,32,31,30,30,29,30,29,30,30], // 2070
            [31,32,31,32,31,30,30,30,29,29,30,31],
            [31,31,31,32,31,31,29,30,30,29,30,30],
            [31,31,32,31,31,31,30,29,30,29,30,30],
            [31,31,32,32,31,30,30,29,30,29,30,30],
            [31,32,31,32,31,30,30,30,29,29,30,31],
            [31,31,31,32,31,31,29,30,30,29,30,30],
            [31,31,32,31,31,31,30,29,30,29,30,30],
            [31,31,32,32,31,30,30,29,30,29,30,30],
            [31,32,31,32,31,30,30,30,29,29,30,31],
            [31,31,31,32,31,31,29,30,30,29,30,30], // 2080
            [31,31,32,31,31,31,30,29,30,29,30,30], // 2081
            [31,31,32,32,31,30,30,29,30,29,30,30], // 2082
            [31,32,31,32,31,30,30,30,29,29,30,31], // 2083
            [31,31,31,32,31,31,29,30,30,29,30,30], // 2084
            [31,31,32,31,31,31,30,29,30,29,30,30], // 2085
            [31,31,32,32,31,30,30,29,30,29,30,30], // 2086
            [31,32,31,32,31,30,30,30,29,29,30,31], // 2087
            [31,31,31,32,31,31,29,30,30,29,30,30], // 2088
            [31,31,32,31,31,31,30,29,30,29,30,30], // 2089
            [31,31,32,32,31,30,30,29,30,29,30,30]  // 2090
        ],
        monthsNp: ['बैशाख','जेठ','असार','श्रावण','भाद्र','असोज','कात्तिक','मंसिर','पौष','माघ','फाल्गुन','चैत्र'],
        monthsEn: ['Baisakh','Jestha','Ashadh','Shrawan','Bhadra','Ashwin','Kartik','Mangsir','Poush','Magh','Falgun','Chaitra'],
        daysNp: ['आइतवार','सोमबार','मंगलबार','बुधबार','बिहिवार','शुक्रवार','शनिवार'],
        digitsNp: ['०','१','२','३','४','५','६','७','८','९'],

        tithis: [
            'प्रतिपदा', 'द्वितीया', 'तृतीया', 'चतुर्थी', 'पञ्चमी',
            'षष्ठी', 'सप्तमी', 'अष्टमी', 'नवमी', 'दशमी',
            'एकादशी', 'द्वादशी', 'त्रयोदशी', 'चतुर्दशी', 'पूर्णिमा',
            'प्रतिपदा', 'द्वितीया', 'तृतीया', 'चतुर्थी', 'पञ्चमी',
            'षष्ठी', 'सप्तमी', 'अष्टमी', 'नवमी', 'दशमी',
            'एकादशी', 'द्वादशी', 'त्रयोदशी', 'चतुर्दशी', 'औंसी'
        ],

        // Comprehensive Nepal Festivals & Events Dataset
        festivals: {
            1: { // Baisakh
                1: { title: 'नयाँ वर्ष / बिस्केट जात्रा', tithi: 'प्रतिपदा', is_holiday: true, icon: 'sun' },
                5: { title: 'मातातीर्थ औंसी (आमाको मुख हेर्ने)', tithi: 'औंसी', is_holiday: false, icon: 'heart' },
                11: { title: 'लोकतन्त्र दिवस', tithi: 'षष्ठी', is_holiday: true, icon: 'flag' },
                18: { title: 'मजदुर दिवस (May Day)', tithi: 'त्रयोदशी', is_holiday: true, icon: 'hammer' },
                25: { title: 'बुद्ध जयन्ती / उभौली पर्व', tithi: 'पूर्णिमा', is_holiday: true, icon: 'flower' }
            },
            2: { // Jestha
                11: { title: 'मोहिनी एकादशी व्रत', tithi: 'एकादशी', is_holiday: false, icon: 'sparkles' },
                15: { title: 'गणतन्त्र दिवस (Republic Day)', tithi: 'प्रतिपदा', is_holiday: true, icon: 'flag' },
                22: { title: 'विश्व वातावरण दिवस', tithi: 'सप्तमी', is_holiday: false, icon: 'tree' },
                30: { title: 'गंगा दशहरा / निर्जला एकादशी', tithi: 'एकादशी', is_holiday: false, icon: 'droplet' }
            },
            3: { // Ashadh
                8: { title: 'अन्तर्राष्ट्रिय योग दिवस', tithi: 'सप्तमी', is_holiday: false, icon: 'activity' },
                15: { title: 'राष्ट्रिय धान दिवस / असार १५', tithi: 'चतुर्दशी', is_holiday: false, icon: 'crop' },
                29: { title: 'भानु जयन्ती / गुरु पूर्णिमा', tithi: 'पूर्णिमा', is_holiday: false, icon: 'book' }
            },
            4: { // Shrawan
                1: { title: 'साउने संक्रान्ति', tithi: 'प्रतिपदा', is_holiday: false, icon: 'sun' },
                15: { title: 'खीर खाने दिन', tithi: 'पूर्णिमा', is_holiday: false, icon: 'cup' },
                20: { title: 'नाग पञ्चमी', tithi: 'पञ्चमी', is_holiday: false, icon: 'shield' },
                31: { title: 'जनै पूर्णिमा / रक्षा बन्धन / गाईजात्रा', tithi: 'पूर्णिमा', is_holiday: true, icon: 'star' }
            },
            5: { // Bhadra
                8: { title: 'श्रीकृष्ण जन्माष्टमी', tithi: 'अष्टमी', is_holiday: true, icon: 'moon' },
                15: { title: 'कुशे औंसी (बुवाको मुख हेर्ने)', tithi: 'औंसी', is_holiday: false, icon: 'heart' },
                28: { title: 'दर खाने दिन', tithi: 'द्वितीया', is_holiday: false, icon: 'gift' },
                29: { title: 'हरितालिका तीज (महिला बिदा)', tithi: 'तृतीया', is_holiday: true, icon: 'heart' },
                31: { title: 'ऋषि पञ्चमी / गणेश चतुर्थी', tithi: 'पञ्चमी', is_holiday: false, icon: 'star' }
            },
            6: { // Ashoj (Matching Screenshot)
                1: { title: 'विश्वकर्मा पूजा / कन्या संक्रान्ति', tithi: 'षष्ठी', is_holiday: false, icon: 'tools' },
                3: { title: 'संविधान दिवस / राष्ट्रिय दिवस', tithi: 'अष्टमी', is_holiday: true, icon: 'flag' },
                9: { title: 'इन्द्रजात्रा (उपत्यका बिदा)', tithi: 'चतुर्दशी', is_holiday: true, icon: 'star' },
                11: { title: 'सोह्र श्राद्ध प्रारम्भ', tithi: 'प्रतिपदा', is_holiday: false, icon: 'sun' },
                19: { title: 'विश्व शिक्षक दिवस / दशमी श्राद्ध', tithi: 'दशमी', is_holiday: false, icon: 'book' },
                22: { title: 'प्रदोष व्रत / विश्व दृष्टि दिवस', tithi: 'त्रयोदशी', is_holiday: false, icon: 'eye' },
                23: { title: 'चतुर्दशी श्राद्ध / विश्व हुलाक दिवस', tithi: 'चतुर्दशी', is_holiday: false, icon: 'envelope' },
                24: { title: 'पितृ विसर्जन / औंसी श्राद्ध / मानसिक स्वास्थ्य दिवस', tithi: 'औंसी', is_holiday: true, icon: 'shield' },
                25: { title: 'घटस्थापना व्रत / नवरात्र आरम्भ (दशैं सुरु)', tithi: 'प्रतिपदा', is_holiday: true, icon: 'flower' },
                27: { title: 'प्रकोप जोखिम न्यूनीकरण दिवस', tithi: 'तृतीया', is_holiday: false, icon: 'bell' },
                29: { title: 'विश्व हातधुने दिवस / ग्रामीण महिला दिवस', tithi: 'पञ्चमी', is_holiday: false, icon: 'hand' },
                30: { title: 'विश्व खाद्य दिवस', tithi: 'षष्ठी', is_holiday: false, icon: 'egg' },
                31: { title: 'फूलपाती / दशैं बिदा प्रारम्भ', tithi: 'सप्तमी', is_holiday: true, icon: 'star' }
            },
            7: { // Kartik
                1: { title: 'महाअष्टमी / कालरात्रि (दशैं बिदा)', tithi: 'अष्टमी', is_holiday: true, icon: 'shield' },
                2: { title: 'महानवमी / आयुध पूजा (दशैं बिदा)', tithi: 'नवमी', is_holiday: true, icon: 'award' },
                3: { title: 'विजयादशमी (दशैं टीका)', tithi: 'दशमी', is_holiday: true, icon: 'sun' },
                4: { title: 'पापांकुशा एकादशी (दशैं बिदा)', tithi: 'एकादशी', is_holiday: true, icon: 'gift' },
                8: { title: 'कोजाग्रत पूर्णिमा (दशैं समापन)', tithi: 'पूर्णिमा', is_holiday: false, icon: 'moon' },
                14: { title: 'काग तिहार / यमपञ्चक आरम्भ', tithi: 'त्रयोदशी', is_holiday: false, icon: 'star' },
                15: { title: 'कुकुर तिहार / नरक चतुर्दशी', tithi: 'चतुर्दशी', is_holiday: false, icon: 'heart' },
                16: { title: 'लक्ष्मी पूजा / सुखरात्रि (तिहार बिदा)', tithi: 'औंसी', is_holiday: true, icon: 'sun' },
                17: { title: 'गोवर्धन पूजा / म्ह पूजा / गाई पूजा', tithi: 'प्रतिपदा', is_holiday: true, icon: 'flower' },
                18: { title: 'भाइटीका / किजापूजा (तिहार बिदा)', tithi: 'द्वितीया', is_holiday: true, icon: 'gift' },
                22: { title: 'छठ पर्व (सूर्य षष्ठी व्रत)', tithi: 'षष्ठी', is_holiday: true, icon: 'sun' },
                27: { title: 'हरिबोधिनी एकादशी (तुलसी विवाह)', tithi: 'एकादशी', is_holiday: false, icon: 'flower' }
            },
            8: { // Mangsir
                10: { title: 'लैंगिक हिंसा विरुद्धको १६ दिने अभियान', tithi: 'दशमी', is_holiday: false, icon: 'shield' },
                15: { title: 'विश्व एड्स दिवस', tithi: 'पूर्णिमा', is_holiday: false, icon: 'heart' },
                18: { title: 'अन्तर्राष्ट्रिय अपाङ्गता दिवस', tithi: 'तृतीया', is_holiday: false, icon: 'person' },
                28: { title: 'उधौली पर्व / धान्य पूर्णिमा / योमरी पुन्ही', tithi: 'पूर्णिमा', is_holiday: true, icon: 'moon' }
            },
            9: { // Poush
                10: { title: 'क्रिसमस डे (Christmas Day)', tithi: 'एकादशी', is_holiday: true, icon: 'tree' },
                15: { title: 'तमु ल्होसार (गुरुङ नयाँ वर्ष)', tithi: 'प्रतिपदा', is_holiday: true, icon: 'star' },
                27: { title: 'पृथ्वी जयन्ती / राष्ट्रिय एकता दिवस', tithi: 'त्रयोदशी', is_holiday: true, icon: 'flag' }
            },
            10: { // Magh
                1: { title: 'माघे संक्रान्ति / माघी पर्व', tithi: 'प्रतिपदा', is_holiday: true, icon: 'sun' },
                16: { title: 'सहिद दिवस (Martyrs Day)', tithi: 'प्रतिपदा', is_holiday: true, icon: 'flower' },
                18: { title: 'सोनाम ल्होसार (तामाङ नयाँ वर्ष)', tithi: 'तृतीया', is_holiday: true, icon: 'star' },
                23: { title: 'श्रीपञ्चमी / सरस्वती पूजा (अक्षरारम्भ)', tithi: 'पञ्चमी', is_holiday: false, icon: 'book' }
            },
            11: { // Falgun
                7: { title: 'राष्ट्रिय प्रजातन्त्र दिवस', tithi: 'सप्तमी', is_holiday: true, icon: 'flag' },
                12: { title: 'ग्याल्पो ल्होसार (शेर्पा नयाँ वर्ष)', tithi: 'द्वादशी', is_holiday: true, icon: 'star' },
                17: { title: 'महाशिवरात्रि / सेना दिवस', tithi: 'चतुर्दशी', is_holiday: true, icon: 'moon' },
                24: { title: 'अन्तर्राष्ट्रिय महिला दिवस (८ मार्च)', tithi: 'सप्तमी', is_holiday: true, icon: 'heart' },
                29: { title: 'फागु पूर्णिमा / होली (पहाडी बिदा)', tithi: 'पूर्णिमा', is_holiday: true, icon: 'palette' },
                30: { title: 'होली पर्व (तराई बिदा)', tithi: 'प्रतिपदा', is_holiday: true, icon: 'palette' }
            },
            12: { // Chaitra
                15: { title: 'घोडेजात्रा (उपत्यका बिदा)', tithi: 'औंसी', is_holiday: true, icon: 'star' },
                24: { title: 'चैते दशैं / राम नवमी', tithi: 'नवमी', is_holiday: true, icon: 'sun' },
                30: { title: 'शैक्षिक सत्र समापन तथा परीक्षा नतिजा', tithi: 'पूर्णिमा', is_holiday: false, icon: 'award' }
            }
        },

        // Auspicious Saits for each month
        saits: {
            6: [
                { title: 'विवाह साइत (शुभ लग्न)', tithi: 'दशमी', bsDay: 19, adDate: '5 Oct 2026', desc: 'प्रातः ०८:१५ देखि ११:४५ सम्म' },
                { title: 'गृहप्रवेश साइत', tithi: 'त्रयोदशी', bsDay: 22, adDate: '8 Oct 2026', desc: 'अभिजीत मुहूर्त १०:३० बजे' },
                { title: 'पास्नी साइत (अन्नप्राशन)', tithi: 'प्रतिपदा', bsDay: 25, adDate: '11 Oct 2026', desc: 'शुभ बेला ०९:०० बजे' },
                { title: 'व्रतबन्ध साइत', tithi: 'सप्तमी', bsDay: 31, adDate: '17 Oct 2026', desc: 'अमृत बेला ११:१५ बजे' }
            ],
            7: [
                { title: 'विवाह साइत', tithi: 'एकादशी', bsDay: 4, adDate: '20 Oct 2026', desc: 'शुभ मुहूर्त' },
                { title: 'भाइटीका उत्तम साइत', tithi: 'द्वितीया', bsDay: 18, adDate: '3 Nov 2026', desc: '११:३७ बजे अभिजित मुहूर्त' }
            ]
        },

        toDevanagari: function(num) {
            return String(num).replace(/\d/g, function(d) {
                return NepaliDateConverter.digitsNp[parseInt(d, 10)];
            });
        },

        adToBS: function(adDateStr) {
            if (!adDateStr) return null;
            var parts = adDateStr.split('T')[0].split('-');
            if (parts.length < 3) return null;
            var adY = parseInt(parts[0], 10);
            var adM = parseInt(parts[1], 10);
            var adD = parseInt(parts[2], 10);

            var refAd = new Date(Date.UTC(1944, 0, 1));
            var curAd = new Date(Date.UTC(adY, adM - 1, adD));
            var totalDays = Math.round((curAd - refAd) / 86400000);

            if (totalDays < 0) return null;

            var bsY = 2000;
            var bsM = 9;
            var bsD = 17;

            while (totalDays > 0) {
                var yearIdx = bsY - 2000;
                if (!NepaliDateConverter.bsData[yearIdx]) return null;
                var daysInMonth = NepaliDateConverter.bsData[yearIdx][bsM - 1];
                var remaining = daysInMonth - bsD;

                if (totalDays <= remaining) {
                    bsD += totalDays;
                    totalDays = 0;
                } else {
                    totalDays -= (remaining + 1);
                    bsD = 1;
                    bsM++;
                    if (bsM > 12) {
                        bsM = 1;
                        bsY++;
                    }
                }
            }

            var pad = function(n) { return String(n).padStart(2, '0'); };
            return {
                year: bsY,
                month: bsM,
                day: bsD,
                formatted: bsY + '-' + pad(bsM) + '-' + pad(bsD),
                monthNameNp: NepaliDateConverter.monthsNp[bsM - 1],
                monthNameEn: NepaliDateConverter.monthsEn[bsM - 1],
                dayNp: NepaliDateConverter.toDevanagari(bsD),
                yearNp: NepaliDateConverter.toDevanagari(bsY)
            };
        },

        bsToAD: function(bsY, bsM, bsD) {
            if (bsY < 2000 || bsY > 2090) return null;
            var totalDays = 0;
            var refYearIdx = 0; // 2000 BS
            totalDays += NepaliDateConverter.bsData[refYearIdx][9 - 1] - 17;

            for (var m = 10; m <= 12; m++) {
                totalDays += NepaliDateConverter.bsData[refYearIdx][m - 1];
            }

            for (var y = 2001; y < bsY; y++) {
                var yIdx = y - 2000;
                for (var mm = 1; mm <= 12; mm++) {
                    totalDays += NepaliDateConverter.bsData[yIdx][mm - 1];
                }
            }

            if (bsY > 2000) {
                var targetYearIdx = bsY - 2000;
                for (var targetM = 1; targetM < bsM; targetM++) {
                    totalDays += NepaliDateConverter.bsData[targetYearIdx][targetM - 1];
                }
                totalDays += (bsD - 1);
            } else {
                totalDays = 0;
                var currentYearIdx = 0;
                if (bsM === 9) {
                    totalDays += (bsD - 17);
                } else {
                    totalDays += NepaliDateConverter.bsData[currentYearIdx][8] - 17;
                    for (var im = 10; im < bsM; im++) {
                        totalDays += NepaliDateConverter.bsData[currentYearIdx][im - 1];
                    }
                    totalDays += (bsD - 1);
                }
            }

            var refAd = new Date(Date.UTC(1944, 0, 1));
            var targetAd = new Date(refAd.getTime() + totalDays * 86400000);
            var pad = function(n) { return String(n).padStart(2, '0'); };
            return targetAd.getUTCFullYear() + '-' + pad(targetAd.getUTCMonth() + 1) + '-' + pad(targetAd.getUTCDate());
        },

        getFestivalInfo: function(bsMonth, bsDay) {
            var mData = NepaliDateConverter.festivals[bsMonth];
            if (mData && mData[bsDay]) {
                return mData[bsDay];
            }
            // Generate fallback tithi name
            var tithiIdx = (bsDay - 1) % 30;
            return {
                title: '',
                tithi: NepaliDateConverter.tithis[tithiIdx] || 'तिथि',
                is_holiday: false,
                icon: 'circle'
            };
        }
    };

    // ── Right Panel Tab Switcher & Renderer ─────────────────────────────────
    window.switchPatroTab = function(tabName) {
        currentPatroTab = tabName;
        document.querySelectorAll('.hp-tab-btn').forEach(function(btn) {
            btn.className = 'hp-tab-btn flex-1 py-2.5 px-3 text-center text-xs font-bold text-slate-600 hover:text-slate-900 transition';
        });
        var activeBtn = document.getElementById('tab-btn-' + tabName);
        if (activeBtn) {
            activeBtn.className = 'hp-tab-btn flex-1 py-2.5 px-3 text-center text-xs font-extrabold text-rose-600 border-b-2 border-rose-600 transition';
        }
        renderPatroRightPanel();
    };

    function getRelativeDaysText(targetDateStr) {
        var now = new Date();
        now.setHours(0,0,0,0);
        var target = new Date(targetDateStr);
        target.setHours(0,0,0,0);
        var diffTime = target - now;
        var diffDays = Math.round(diffTime / 86400000);

        if (diffDays === 0) return '<span class="text-emerald-700 font-extrabold bg-emerald-50 px-1.5 py-0.5 rounded">आज</span>';
        if (diffDays === 1) return '<span class="text-indigo-600 font-bold">भोलि</span>';
        if (diffDays === -1) return '<span class="text-slate-400">हिजो</span>';
        if (diffDays > 0) return '<span class="text-slate-400 font-medium">' + NepaliDateConverter.toDevanagari(diffDays) + ' दिन पछि</span>';
        return '<span class="text-slate-400 font-medium">' + NepaliDateConverter.toDevanagari(Math.abs(diffDays)) + ' दिन अगाडि</span>';
    }

    function renderPatroRightPanel() {
        var container = document.getElementById('patro-tab-content');
        if (!container) return;

        var mFestivals = NepaliDateConverter.festivals[activeBsMonth] || {};
        var mSaits = NepaliDateConverter.saits[activeBsMonth] || NepaliDateConverter.saits[6] || [];
        var monthNameNp = NepaliDateConverter.monthsNp[activeBsMonth - 1] || 'महिना';

        var html = '';

        if (currentPatroTab === 'festivals') {
            // ── Tab 1: पर्वहरू (Festivals & School Events) ──
            var todayIso = new Date().toISOString().split('T')[0];
            var todayBs = NepaliDateConverter.adToBS(todayIso);
            var isCurrentMonth = todayBs && todayBs.month === activeBsMonth && todayBs.year === activeBsYear;

            // Highlight Today Card
            if (isCurrentMonth) {
                var todayFest = mFestivals[todayBs.day] || { title: 'नियमित कक्षा सञ्चालन', tithi: todayBs.dayNp + ' गते' };
                html += '<div class="mb-4">' +
                    '<h3 class="text-xs font-black text-rose-600 uppercase tracking-wider mb-2 flex items-center gap-1.5">' +
                        '<i class="bi bi-geo-alt-fill text-rose-500"></i> आज (' + todayBs.dayNp + ' ' + monthNameNp + ')' +
                    '</h3>' +
                    '<div class="p-3.5 rounded-2xl bg-emerald-800 text-white flex items-center gap-3 shadow-md">' +
                        '<div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0 text-amber-300 text-lg shadow-inner">' +
                            '<i class="bi bi-calendar2-check-fill"></i>' +
                        '</div>' +
                        '<div class="min-w-0 flex-1">' +
                            '<p class="text-xs font-extrabold text-white truncate leading-tight">' + (todayFest.title || 'आजका मुख्य कार्यक्रम') + '</p>' +
                            '<p class="text-[11px] text-emerald-200 mt-0.5">' + (todayFest.tithi || '') + ' · ' + todayIso + '</p>' +
                        '</div>' +
                    '</div>' +
                '</div>';
            }

            // List of upcoming events / festivals for active month
            html += '<div class="space-y-3">';
            var dayKeys = Object.keys(mFestivals).map(function(k){ return parseInt(k,10); }).sort(function(a,b){ return a-b; });

            if (dayKeys.length === 0) {
                html += '<div class="text-center py-6 text-slate-400 text-xs">यस महिनाका कुनै मुख्य पर्व सूचीकृत छैन।</div>';
            } else {
                dayKeys.forEach(function(dayNum) {
                    var f = mFestivals[dayNum];
                    var adIso = NepaliDateConverter.bsToAD(activeBsYear, activeBsMonth, dayNum);
                    var adFmt = adIso ? new Date(adIso).toLocaleDateString('en-US', { day:'numeric', month:'short', year:'numeric' }) : '';
                    var relDays = adIso ? getRelativeDaysText(adIso) : '';
                    var isHoliday = f.is_holiday;

                    // Icon selection
                    var iconBg = isHoliday ? 'bg-rose-100 text-rose-600' : 'bg-indigo-50 text-indigo-600';
                    var iconClass = isHoliday ? 'bi-star-fill' : 'bi-calendar-event';

                    html += '<div class="flex items-center gap-3 p-2.5 rounded-2xl hover:bg-slate-50 transition border border-transparent hover:border-slate-100">' +
                        '<div class="w-10 h-10 rounded-full ' + iconBg + ' flex items-center justify-center flex-shrink-0 text-sm shadow-xs">' +
                            '<i class="bi ' + iconClass + '"></i>' +
                        '</div>' +
                        '<div class="min-w-0 flex-1">' +
                            '<p class="text-xs font-bold text-slate-800 leading-snug line-clamp-1 ' + (isHoliday ? 'text-rose-600 font-extrabold' : '') + '">' + f.title + '</p>' +
                            '<p class="text-[11px] text-slate-400 mt-0.5">' + f.tithi + '</p>' +
                        '</div>' +
                        '<div class="text-right flex-shrink-0">' +
                            '<p class="text-xs font-black ' + (isHoliday ? 'text-rose-600' : 'text-slate-800') + '">' + NepaliDateConverter.toDevanagari(dayNum) + ' ' + monthNameNp + '</p>' +
                            '<p class="text-[10px] text-slate-400">' + adFmt + '</p>' +
                            '<div class="text-[10px] mt-0.5">' + relDays + '</div>' +
                        '</div>' +
                    '</div>';
                });
            }
            html += '</div>';

        } else if (currentPatroTab === 'saits') {
            // ── Tab 2: साइतहरू (Auspicious Dates) ──
            html += '<div class="space-y-3">';
            mSaits.forEach(function(s) {
                html += '<div class="p-3.5 rounded-2xl bg-amber-50/60 border border-amber-200/70 flex items-start gap-3">' +
                    '<div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center flex-shrink-0 text-base">' +
                        '<i class="bi bi-brightness-high-fill"></i>' +
                    '</div>' +
                    '<div class="min-w-0 flex-1">' +
                        '<p class="text-xs font-extrabold text-amber-950">' + s.title + '</p>' +
                        '<p class="text-[11px] text-amber-800 mt-0.5">' + s.desc + '</p>' +
                    '</div>' +
                    '<div class="text-right flex-shrink-0">' +
                        '<p class="text-xs font-black text-amber-900">' + NepaliDateConverter.toDevanagari(s.bsDay) + ' ' + monthNameNp + '</p>' +
                        '<p class="text-[10px] text-amber-700">' + s.adDate + '</p>' +
                    '</div>' +
                '</div>';
            });
            html += '</div>';

        } else if (currentPatroTab === 'holidays') {
            // ── Tab 3: आगामी बिदा (Upcoming Holidays) ──
            html += '<div class="space-y-3">';
            var holidayDays = Object.keys(mFestivals).filter(function(d){ return mFestivals[d].is_holiday; });

            if (holidayDays.length === 0) {
                html += '<div class="text-center py-6 text-slate-400 text-xs">यस महिनामा सार्वजनिक बिदा छैन।</div>';
            } else {
                holidayDays.forEach(function(d) {
                    var h = mFestivals[d];
                    var adIso = NepaliDateConverter.bsToAD(activeBsYear, activeBsMonth, parseInt(d,10));
                    var adFmt = adIso ? new Date(adIso).toLocaleDateString('en-US', { day:'numeric', month:'short', year:'numeric' }) : '';
                    var relDays = adIso ? getRelativeDaysText(adIso) : '';

                    html += '<div class="p-3 rounded-2xl bg-rose-50/70 border border-rose-100 flex items-center justify-between gap-3">' +
                        '<div class="flex items-center gap-2.5 min-w-0 flex-1">' +
                            '<span class="w-8 h-8 rounded-xl bg-rose-600 text-white flex items-center justify-center flex-shrink-0 text-sm font-bold">' +
                                '<i class="bi bi-flag-fill"></i>' +
                            '</span>' +
                            '<div class="min-w-0">' +
                                '<p class="text-xs font-black text-rose-900 truncate">' + h.title + '</p>' +
                                '<span class="text-[10px] font-bold text-rose-600 bg-rose-100 px-1.5 py-0.2 rounded">सार्वजनिक बिदा</span>' +
                            '</div>' +
                        '</div>' +
                        '<div class="text-right flex-shrink-0">' +
                            '<p class="text-xs font-black text-rose-700">' + NepaliDateConverter.toDevanagari(d) + ' ' + monthNameNp + '</p>' +
                            '<p class="text-[10px] text-slate-400">' + adFmt + '</p>' +
                            '<div class="text-[10px] mt-0.5">' + relDays + '</div>' +
                        '</div>' +
                    '</div>';
                });
            }
            html += '</div>';
        }

        container.innerHTML = html;
    }

    // ── Top Bar Controls ───────────────────────────────────────────────────
    window.togglePatroMonthPicker = function() {
        var pop = document.getElementById('hp-month-picker-popover');
        if (pop) pop.classList.toggle('hidden');
    };

    window.selectPatroYear = function(year) {
        activeBsYear = year;
        document.querySelectorAll('.hp-year-btn').forEach(function(b){
            b.className = (parseInt(b.dataset.year, 10) === year)
                ? 'hp-year-btn px-2 py-0.5 rounded text-xs font-bold transition bg-rose-600 text-white'
                : 'hp-year-btn px-2 py-0.5 rounded text-xs font-bold transition bg-slate-100 text-slate-700 hover:bg-slate-200';
        });
        jumpToPatroMonthYear(activeBsYear, activeBsMonth);
    };

    window.selectPatroMonth = function(monthNum) {
        activeBsMonth = monthNum;
        var pop = document.getElementById('hp-month-picker-popover');
        if (pop) pop.classList.add('hidden');
        jumpToPatroMonthYear(activeBsYear, activeBsMonth);
    };

    function jumpToPatroMonthYear(bsY, bsM) {
        var targetAdIso = NepaliDateConverter.bsToAD(bsY, bsM, 1);
        if (targetAdIso && calendar) {
            calendar.gotoDate(targetAdIso);
        }
    }

    window.goToPatroToday = function() {
        if (!calendar) return;
        calendar.today();
    };

    window.prevPatroMonth = function() {
        if (!calendar) return;
        calendar.prev();
    };

    window.nextPatroMonth = function() {
        if (!calendar) return;
        calendar.next();
    };

    // ── Update Top Title & Header ──────────────────────────────────────────
    function updateHamroPatroHeader(bsInfo, viewTitle) {
        var titleEl = document.getElementById('hp-active-month-title');
        var subEl = document.getElementById('hp-active-ad-subtitle');
        if (titleEl && bsInfo) {
            titleEl.textContent = bsInfo.monthNameNp + ' ' + bsInfo.yearNp;
        }
        if (subEl && bsInfo) {
            subEl.textContent = bsInfo.monthNameEn + ' - ' + viewTitle;
        }

        activeBsYear = bsInfo ? bsInfo.year : 2083;
        activeBsMonth = bsInfo ? bsInfo.month : 6;

        // Sync month button highlight inside popover
        document.querySelectorAll('.hp-month-btn').forEach(function(btn) {
            var m = parseInt(btn.dataset.month, 10);
            if (m === activeBsMonth) {
                btn.className = 'hp-month-btn py-2 px-2 rounded-xl text-center border border-rose-600 bg-rose-600 text-white font-black shadow-xs';
            } else {
                btn.className = 'hp-month-btn py-2 px-2 rounded-xl text-center border border-slate-100 hover:border-rose-300 hover:bg-rose-50 hover:text-rose-700 transition font-bold text-slate-700';
            }
        });

        // Sync Live Today in Top Banner
        var now = new Date();
        var y = now.getFullYear();
        var m = String(now.getMonth() + 1).padStart(2, '0');
        var d = String(now.getDate()).padStart(2, '0');
        var todayBs = NepaliDateConverter.adToBS(y + '-' + m + '-' + d);
        if (todayBs) {
            var bsTodayEl = document.getElementById('top-live-bs-today');
            var adTodayEl = document.getElementById('top-live-ad-today');
            if (bsTodayEl) {
                var dayOfWeekNp = NepaliDateConverter.daysNp[now.getDay()];
                bsTodayEl.textContent = todayBs.yearNp + ' ' + todayBs.monthNameNp + ' ' + todayBs.dayNp + ' गते, ' + dayOfWeekNp;
            }
            if (adTodayEl) {
                adTodayEl.textContent = now.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            }
        }

        renderPatroRightPanel();
    }

    // ── Event Modal Handlers ───────────────────────────────────────────────
    function toInputDate(str) {
        if (!str) return '';
        var d = new Date(str);
        if (isNaN(d)) return '';
        var pad = function(n){ return String(n).padStart(2,'0'); };
        return d.getFullYear()+'-'+pad(d.getMonth()+1)+'-'+pad(d.getDate())+'T'+pad(d.getHours())+':'+pad(d.getMinutes());
    }

    function formatDisplayDate(start, end) {
        var opts = { weekday:'short', month:'short', day:'numeric', year:'numeric' };
        var s = new Date(start);
        var out = s.toLocaleDateString('en-US', opts);
        if (start && start.includes('T') && !start.endsWith('T00:00:00')) {
            out += ' · ' + s.toLocaleTimeString('en-US', {hour:'2-digit', minute:'2-digit'});
        }
        if (end) {
            var e = new Date(end);
            if (!isNaN(e) && e > s) {
                out += ' → ' + e.toLocaleDateString('en-US', {month:'short', day:'numeric'});
            }
        }
        return out;
    }

    window.handleCategoryChange = function(cat) {
        var isHolidayBox = document.getElementById('efm-is-holiday');
        if (cat === 'holiday' || cat === 'vacation') {
            isHolidayBox.checked = true;
        }
        var catColors = { 'holiday':'#dc2626', 'vacation':'#ea580c', 'exam':'#9333ea', 'academic':'#4f46e5', 'sports':'#16a34a', 'celebration':'#db2777' };
        if (catColors[cat]) {
            selectEventColor(catColors[cat]);
        }
    };

    window.selectEventColor = function (color) {
        document.getElementById('efm-color').value = color;
        document.getElementById('efm-submit-btn').style.background = color;
        document.querySelectorAll('.color-swatch').forEach(function (sw) {
            sw.classList.toggle('active', sw.dataset.color === color);
        });
        var custom = document.getElementById('efm-color-custom');
        if (custom) custom.value = color;
    };

    window.openCreateEventModal = function (prefillStart, prefillEnd) {
        document.getElementById('efm-mode').value = 'create';
        document.getElementById('efm-title').textContent = 'नयाँ कार्यक्रम / बिदा थप्नुहोस्';
        document.getElementById('efm-start').value = prefillStart || '';
        document.getElementById('efm-end').value   = prefillEnd   || '';
        document.getElementById('efm-event-title').value = '';
        document.getElementById('efm-description').value = '';
        document.getElementById('efm-venue').value = '';
        document.getElementById('efm-category').value = 'academic';
        document.getElementById('efm-is-holiday').checked = false;
        selectEventColor('#dc2626');
        var modal = document.getElementById('event-form-modal');
        modal.classList.add('modal-show');
        setTimeout(function(){ document.getElementById('efm-event-title').focus(); }, 80);
    };

    window.closeEventFormModal = function () {
        document.getElementById('event-form-modal').classList.remove('modal-show');
    };

    function openEventViewModal(fcEvent) {
        viewingEvent = fcEvent;
        var color = fcEvent.backgroundColor || '#dc2626';
        var props = fcEvent.extendedProps || {};

        document.getElementById('evm-header').style.background = color;
        document.getElementById('evm-title').textContent = props.raw_title || fcEvent.title;
        document.getElementById('evm-cat-badge').textContent = props.is_holiday ? '🎌 Holiday / बिदा' : (props.category || 'Academic');
        document.getElementById('evm-date').textContent  = formatDisplayDate(fcEvent.startStr, fcEvent.endStr);

        var bsEl = document.getElementById('evm-bs-date');
        if (props.start_bs) {
            bsEl.textContent = 'नेपाली मिति: ' + props.start_bs + (props.end_bs && props.end_bs !== props.start_bs ? ' देखि ' + props.end_bs : '');
            bsEl.style.display = 'block';
        } else {
            bsEl.style.display = 'none';
        }

        document.getElementById('evm-term').textContent = props.academic_term || '-';
        document.getElementById('evm-audience').textContent = props.audience || 'All';

        var venueRow = document.getElementById('evm-venue-row');
        if (props.venue) {
            document.getElementById('evm-venue').textContent = props.venue;
            venueRow.classList.remove('hidden');
        } else {
            venueRow.classList.add('hidden');
        }

        var descRow = document.getElementById('evm-desc-row');
        if (props.description) {
            document.getElementById('evm-desc').textContent = props.description;
            descRow.classList.remove('hidden');
        } else {
            descRow.classList.add('hidden');
        }

        var actionsEl = document.getElementById('evm-actions');
        if (IS_EDITABLE) {
            actionsEl.classList.remove('hidden');
        } else {
            actionsEl.classList.add('hidden');
        }

        document.getElementById('event-view-modal').classList.add('modal-show');
    }

    window.closeEventViewModal = function () {
        document.getElementById('event-view-modal').classList.remove('modal-show');
        viewingEvent = null;
    };

    window.openEditFromView = function () {
        if (!viewingEvent) return;
        var props = viewingEvent.extendedProps || {};
        closeEventViewModal();

        document.getElementById('efm-mode').value = 'edit';
        document.getElementById('efm-title').textContent = 'कार्यक्रम सम्पादन (Edit Event)';
        document.getElementById('efm-event-id').value = viewingEvent.id;
        document.getElementById('efm-event-title').value = props.raw_title || viewingEvent.title;
        document.getElementById('efm-category').value = props.category || 'academic';
        document.getElementById('efm-is-holiday').checked = !!props.is_holiday;
        document.getElementById('efm-term').value = props.academic_term || 'First Term';
        document.getElementById('efm-audience').value = props.audience || 'all';
        document.getElementById('efm-venue').value = props.venue || '';
        document.getElementById('efm-start').value = toInputDate(viewingEvent.startStr);
        document.getElementById('efm-end').value   = toInputDate(viewingEvent.endStr);
        document.getElementById('efm-description').value = props.description || '';
        selectEventColor(viewingEvent.backgroundColor || '#dc2626');

        document.getElementById('event-form-modal').classList.add('modal-show');
        setTimeout(function(){ document.getElementById('efm-event-title').focus(); }, 80);
    };

    window.deleteEventFromView = function () {
        if (!viewingEvent) return;
        var id = viewingEvent.id;
        var title = viewingEvent.title;
        closeEventViewModal();
        if (!confirm('के तपाईं "' + title + '" मेटाउन निश्चित हुनुहुन्छ?')) return;

        fetch(SITEURL + '/calendar-crud-ajax', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ id: id, type: 'delete' })
        }).then(function (r) { return r.json(); }).then(function () {
            var ev = calendar.getEventById(id);
            if (ev) ev.remove();
            alert('कार्यक्रम सफलतापूर्वक मेटाइयो।');
        });
    };

    window.submitEventForm = function (e) {
        e.preventDefault();
        var mode  = document.getElementById('efm-mode').value;
        var id    = document.getElementById('efm-event-id').value;
        var title = document.getElementById('efm-event-title').value.trim();
        var category = document.getElementById('efm-category').value;
        var isHoliday = document.getElementById('efm-is-holiday').checked;
        var term  = document.getElementById('efm-term').value;
        var audience = document.getElementById('efm-audience').value;
        var venue = document.getElementById('efm-venue').value.trim();
        var start = document.getElementById('efm-start').value;
        var end   = document.getElementById('efm-end').value;
        var color = document.getElementById('efm-color').value;
        var desc  = document.getElementById('efm-description').value.trim();

        if (!title || !start) return;

        var payload = {
            type: mode,
            title: title,
            category: category,
            is_holiday: isHoliday,
            academic_term: term,
            audience: audience,
            venue: venue,
            start: start,
            end: end || start,
            color: color,
            description: desc
        };
        if (mode === 'edit') payload.id = id;

        var btn = document.getElementById('efm-submit-btn');
        btn.disabled = true;
        btn.textContent = 'Saving…';

        fetch(SITEURL + '/calendar-crud-ajax', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify(payload)
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            closeEventFormModal();
            calendar.refetchEvents();
        })
        .catch(function () {
            alert('त्रुटि भयो। कृपया पुनः प्रयास गर्नुहोस्।');
        })
        .finally(function () {
            btn.disabled = false;
            btn.textContent = 'Save Event';
        });
    };

    // ── Init FullCalendar v6 (Day Grid with Hamro Patro Rendering) ─────────
    document.addEventListener('DOMContentLoaded', function () {
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: false, // We use custom Hamro Patro top bar
            dayHeaders: true,
            dayHeaderFormat: { weekday: 'long' },
            dayHeaderContent: function(arg) {
                var dayIdx = arg.date.getDay();
                return NepaliDateConverter.daysNp[dayIdx] || arg.text;
            },
            editable: IS_EDITABLE,
            selectable: IS_EDITABLE,
            selectMirror: true,

            dayCellContent: function(arg) {
                var d = arg.date;
                var pad = function(n){ return String(n).padStart(2,'0'); };
                var iso = d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
                var bs = NepaliDateConverter.adToBS(iso);

                if (bs) {
                    var festInfo = NepaliDateConverter.getFestivalInfo(bs.month, bs.day);
                    var isSat = d.getDay() === 6;
                    var isHol = festInfo.is_holiday;

                    // Match custom school event if any loaded for this date
                    var customEventText = '';
                    if (currentLoadedEvents && currentLoadedEvents.length) {
                        var match = currentLoadedEvents.find(function(ev) {
                            return ev.start && ev.start.startsWith(iso);
                        });
                        if (match) {
                            customEventText = match.title.replace('🎌 ', '');
                            if (match.extendedProps && match.extendedProps.is_holiday) isHol = true;
                        }
                    }

                    var eventTitle = customEventText || festInfo.title || '';
                    var eventClass = isHol ? 'text-rose-600 font-extrabold' : 'text-slate-700 font-bold';
                    var bsNumClass = (isSat || isHol) ? 'text-rose-600' : 'text-slate-800';
                    var tithiClass = isHol ? 'text-rose-400' : 'text-slate-400';

                    return {
                        html: '<div class="hp-cell-container">' +
                                '<div class="hp-cell-top">' +
                                    '<span class="hp-ad-num">' + d.getDate() + '</span>' +
                                '</div>' +
                                (eventTitle ? '<div class="hp-event-text ' + eventClass + '">' + eventTitle + '</div>' : '') +
                                '<div class="hp-bs-num ' + bsNumClass + '">' + bs.dayNp + '</div>' +
                                '<div class="hp-tithi-text ' + tithiClass + '">' + festInfo.tithi + '</div>' +
                              '</div>'
                    };
                }

                return { text: arg.dayNumberText };
            },

            datesSet: function(info) {
                // Central date for active view
                var midTime = (info.start.getTime() + info.end.getTime()) / 2;
                var midDate = new Date(midTime);
                var pad = function(n){ return String(n).padStart(2,'0'); };
                var iso = midDate.getFullYear() + '-' + pad(midDate.getMonth() + 1) + '-' + pad(midDate.getDate());
                var bs = NepaliDateConverter.adToBS(iso);

                updateHamroPatroHeader(bs, info.view.title);
            },

            events: function (info, successCallback, failureCallback) {
                var url = SITEURL + '/calendar-event?start=' + info.startStr + '&end=' + info.endStr;
                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (r) { return r.json(); })
                    .then(function (events) {
                        currentLoadedEvents = events || [];
                        successCallback(events);
                        renderPatroRightPanel();
                    })
                    .catch(function (err) { failureCallback(err); });
            },

            select: function (arg) {
                if (!IS_EDITABLE) return;
                var pad = function(n){ return String(n).padStart(2,'0'); };
                var d = new Date(arg.startStr);
                var defaultStart = d.getFullYear()+'-'+pad(d.getMonth()+1)+'-'+pad(d.getDate())+'T09:00';
                var defaultEnd   = d.getFullYear()+'-'+pad(d.getMonth()+1)+'-'+pad(d.getDate())+'T16:00';
                openCreateEventModal(defaultStart, defaultEnd);
                calendar.unselect();
            },

            eventClick: function (info) {
                openEventViewModal(info.event);
            }
        });

        calendar.render();
    });

    // Close modals on backdrop click
    ['event-view-modal', 'event-form-modal'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) {
            el.addEventListener('click', function (e) {
                if (e.target === this) this.classList.remove('modal-show');
            });
        }
    });

    // Close month picker popover on click outside
    document.addEventListener('click', function (e) {
        var pop = document.getElementById('hp-month-picker-popover');
        var btn = document.getElementById('hp-month-dropdown-btn');
        if (pop && btn && !pop.contains(e.target) && !btn.contains(e.target)) {
            pop.classList.add('hidden');
        }
    });

})();
</script>
