@extends('layouts.app')

@section('content')
<div class="px-4 lg:px-8 py-6 max-w-5xl mx-auto space-y-6">

    {{-- Header & Breadcrumbs --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <span class="w-10 h-10 rounded-2xl bg-indigo-600/10 text-indigo-600 flex items-center justify-center font-bold text-xl shadow-xs">
                    <i class="bi bi-building-add"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">नयाँ कक्षाहरू सिर्जना गर्नुहोस् (Create Academic Classes)</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">एउटै फारमबाट एक वा एकैपटक धेरै कक्षाहरू (Multiple Classes) र सेक्सनहरू बनाउनुहोस्</p>
                </div>
            </div>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800/80 px-3 py-1.5 rounded-xl">
                <li><a href="{{route('home')}}" class="hover:text-indigo-600 transition">Home</a></li>
                <li class="text-slate-300 dark:text-slate-600">/</li>
                <li><a href="{{url('/classes')}}" class="hover:text-indigo-600 transition">Classes</a></li>
                <li class="text-slate-300 dark:text-slate-600">/</li>
                <li class="font-semibold text-slate-800 dark:text-slate-200">Create Classes</li>
            </ol>
        </nav>
    </div>

    @include('session-messages')

    {{-- Mode Selector Tabs --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-1">
        <button type="button" onclick="switchCreateMode('preset')" id="btn-mode-preset"
                class="mode-tab-btn active px-4 py-2.5 rounded-2xl text-xs font-bold transition flex items-center gap-2 bg-indigo-600 text-white shadow-xs">
            <i class="bi bi-magic"></i> १-क्लिक विद्यालय तहहरू (Quick Presets)
        </button>
        <button type="button" onclick="switchCreateMode('multiple')" id="btn-mode-multiple"
                class="mode-tab-btn px-4 py-2.5 rounded-2xl text-xs font-bold transition flex items-center gap-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200">
            <i class="bi bi-table"></i> धेरै कक्षाहरू तालिका (Multiple Rows)
        </button>
        <button type="button" onclick="switchCreateMode('bulk')" id="btn-mode-bulk"
                class="mode-tab-btn px-4 py-2.5 rounded-2xl text-xs font-bold transition flex items-center gap-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200">
            <i class="bi bi-textarea-t"></i> कमा / सूची पेस्ट (Bulk Comma List)
        </button>
        <button type="button" onclick="switchCreateMode('single')" id="btn-mode-single"
                class="mode-tab-btn px-4 py-2.5 rounded-2xl text-xs font-bold transition flex items-center gap-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200">
            <i class="bi bi-plus-square"></i> एकल कक्षा (Single Class)
        </button>
    </div>

    {{-- Main Container Card --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6 md:p-8">

        <form action="{{ route('school.class.create') }}" method="POST" id="create-class-form" class="space-y-6">
            @csrf
            <input type="hidden" name="session_id" value="{{ $current_school_session_id }}">

            {{-- ── MODE 1: 1-CLICK PRESETS ───────────────────────────────── --}}
            <div id="mode-panel-preset" class="space-y-6">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">नेपाल विद्यालय संरचना अनुसार १-क्लिकमा कक्षाहरू थप्नुहोस्</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">तलको कुनै पनि संरचना छान्नुहोस् र आवश्यक भएमा परिमार्जन गर्नुहोस्</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
                    {{-- Secondary School (PG to 10) --}}
                    <div onclick="applyPreset('pg-to-10')" class="preset-card p-4 rounded-2xl border-2 border-slate-200 dark:border-slate-800 hover:border-indigo-500 bg-slate-50/50 dark:bg-slate-800/30 cursor-pointer transition">
                        <div class="flex items-center justify-between mb-2">
                            <span class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-xs">
                                <i class="bi bi-mortarboard-fill"></i>
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800">१२ कक्षाहरू</span>
                        </div>
                        <h4 class="text-xs font-bold text-slate-800 dark:text-slate-100">माध्यमिक विद्यालय (PG to 10)</h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Playgroup, Nursery, LKG, UKG, Class 1 to Class 10</p>
                    </div>

                    {{-- Higher Secondary (PG to 12) --}}
                    <div onclick="applyPreset('pg-to-12')" class="preset-card p-4 rounded-2xl border-2 border-slate-200 dark:border-slate-800 hover:border-indigo-500 bg-slate-50/50 dark:bg-slate-800/30 cursor-pointer transition">
                        <div class="flex items-center justify-between mb-2">
                            <span class="w-8 h-8 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center font-bold text-xs">
                                <i class="bi bi-award-fill"></i>
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800">१४ कक्षाहरू</span>
                        </div>
                        <h4 class="text-xs font-bold text-slate-800 dark:text-slate-100">उच्च माध्यमिक (PG to 12)</h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Playgroup देखि Class 12 सम्म सम्पूर्ण कक्षाहरू</p>
                    </div>

                    {{-- Basic Level (Class 1 to 8) --}}
                    <div onclick="applyPreset('1-to-8')" class="preset-card p-4 rounded-2xl border-2 border-slate-200 dark:border-slate-800 hover:border-indigo-500 bg-slate-50/50 dark:bg-slate-800/30 cursor-pointer transition">
                        <div class="flex items-center justify-between mb-2">
                            <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-xs">
                                <i class="bi bi-backpack-fill"></i>
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">८ कक्षाहरू</span>
                        </div>
                        <h4 class="text-xs font-bold text-slate-800 dark:text-slate-100">आधारभूत तह (Class 1 to 8)</h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Class 1 देखि Class 8 सम्म</p>
                    </div>

                    {{-- Primary Level (ECD to 5) --}}
                    <div onclick="applyPreset('ecd-to-5')" class="preset-card p-4 rounded-2xl border-2 border-slate-200 dark:border-slate-800 hover:border-indigo-500 bg-slate-50/50 dark:bg-slate-800/30 cursor-pointer transition">
                        <div class="flex items-center justify-between mb-2">
                            <span class="w-8 h-8 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center font-bold text-xs">
                                <i class="bi bi-balloon-fill"></i>
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">६ कक्षाहरू</span>
                        </div>
                        <h4 class="text-xs font-bold text-slate-800 dark:text-slate-100">प्राथमिक तह (ECD to 5)</h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">ECD, Nursery, Class 1 देखि Class 5</p>
                    </div>

                    {{-- Secondary Level (9 to 12) --}}
                    <div onclick="applyPreset('9-to-12')" class="preset-card p-4 rounded-2xl border-2 border-slate-200 dark:border-slate-800 hover:border-indigo-500 bg-slate-50/50 dark:bg-slate-800/30 cursor-pointer transition">
                        <div class="flex items-center justify-between mb-2">
                            <span class="w-8 h-8 rounded-xl bg-cyan-100 text-cyan-600 flex items-center justify-center font-bold text-xs">
                                <i class="bi bi-book-fill"></i>
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-cyan-100 text-cyan-800">४ कक्षाहरू</span>
                        </div>
                        <h4 class="text-xs font-bold text-slate-800 dark:text-slate-100">मावि तथा उच्च तह (Class 9 to 12)</h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Class 9, Class 10, Class 11, Class 12</p>
                    </div>
                </div>

                {{-- Preset Preview List & Settings --}}
                <div id="preset-preview-box" class="p-4 rounded-2xl bg-indigo-50/50 dark:bg-slate-800/60 border border-indigo-100 dark:border-slate-700 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-200">छानिएका कक्षाहरूको सूची (Selected Classes Preview):</span>
                        <span id="preset-count-badge" class="text-xs font-bold text-indigo-600">१२ कक्षाहरू</span>
                    </div>
                    <textarea id="preset-classes-textarea" name="bulk_class_names" rows="3"
                              class="w-full text-xs font-medium p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 outline-none">Playgroup, Nursery, LKG, UKG, Class 1, Class 2, Class 3, Class 4, Class 5, Class 6, Class 7, Class 8, Class 9, Class 10</textarea>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">प्रत्येक कक्षाका प्रारम्भिक सेक्सनहरू (Initial Sections)</label>
                            <input type="text" name="initial_sections_str" value="A, B" placeholder="e.g. A, B"
                                   class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">सुरुवाती कोठा नम्बर (Starting Room No)</label>
                            <input type="text" name="default_room_no" value="101" placeholder="e.g. 101"
                                   class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100">
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── MODE 2: DYNAMIC ROWS TABLE ───────────────────────────── --}}
            <div id="mode-panel-multiple" class="space-y-4 hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">तालिकामा कक्षाहरू प्रविष्टि गर्नुहोस्</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">कक्षाको नाम, सेक्सनहरू र कोठा नम्बर छुट्टाछुट्टै भर्नुहोस्</p>
                    </div>
                    <button type="button" onclick="addClassRow()" class="px-3.5 py-1.5 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 text-xs font-bold rounded-xl border border-indigo-200 transition flex items-center gap-1.5">
                        <i class="bi bi-plus-lg"></i> कक्षा थप्नुहोस् (Add Row)
                    </button>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-700">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-semibold border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="p-3 w-12 text-center">#</th>
                                <th class="p-3">कक्षा / तहको नाम (Class Name) *</th>
                                <th class="p-3 w-48">सेक्सनहरू (Sections)</th>
                                <th class="p-3 w-32">कोठा नं (Room)</th>
                                <th class="p-3 w-16 text-center">हटाउनुहोस्</th>
                            </tr>
                        </thead>
                        <tbody id="dynamic-class-tbody" class="divide-y divide-slate-100 dark:divide-slate-800">
                            {{-- Row 1 --}}
                            <tr>
                                <td class="p-3 text-center text-slate-400 font-mono row-idx">1</td>
                                <td class="p-2">
                                    <input type="text" name="classes[0][class_name]" value="Class 1" placeholder="e.g. Class 1" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold">
                                </td>
                                <td class="p-2">
                                    <input type="text" name="classes[0][initial_sections]" value="A, B" placeholder="A, B" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                                </td>
                                <td class="p-2">
                                    <input type="text" name="classes[0][room_no]" value="101" placeholder="101" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                                </td>
                                <td class="p-2 text-center">
                                    <button type="button" onclick="removeClassRow(this)" class="text-slate-400 hover:text-rose-500 p-1"><i class="bi bi-trash3"></i></button>
                                </td>
                            </tr>
                            {{-- Row 2 --}}
                            <tr>
                                <td class="p-3 text-center text-slate-400 font-mono row-idx">2</td>
                                <td class="p-2">
                                    <input type="text" name="classes[1][class_name]" value="Class 2" placeholder="e.g. Class 2" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold">
                                </td>
                                <td class="p-2">
                                    <input type="text" name="classes[1][initial_sections]" value="A, B" placeholder="A, B" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                                </td>
                                <td class="p-2">
                                    <input type="text" name="classes[1][room_no]" value="102" placeholder="102" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                                </td>
                                <td class="p-2 text-center">
                                    <button type="button" onclick="removeClassRow(this)" class="text-slate-400 hover:text-rose-500 p-1"><i class="bi bi-trash3"></i></button>
                                </td>
                            </tr>
                            {{-- Row 3 --}}
                            <tr>
                                <td class="p-3 text-center text-slate-400 font-mono row-idx">3</td>
                                <td class="p-2">
                                    <input type="text" name="classes[2][class_name]" value="Class 3" placeholder="e.g. Class 3" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold">
                                </td>
                                <td class="p-2">
                                    <input type="text" name="classes[2][initial_sections]" value="A, B" placeholder="A, B" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                                </td>
                                <td class="p-2">
                                    <input type="text" name="classes[2][room_no]" value="103" placeholder="103" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                                </td>
                                <td class="p-2 text-center">
                                    <button type="button" onclick="removeClassRow(this)" class="text-slate-400 hover:text-rose-500 p-1"><i class="bi bi-trash3"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <button type="button" onclick="addClassRow()" class="w-full py-2.5 border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-2xl text-xs font-bold text-slate-500 hover:border-indigo-400 hover:text-indigo-600 transition flex items-center justify-center gap-1.5">
                    <i class="bi bi-plus-circle"></i> अर्को कक्षा थप्नुहोस् (Add Another Class Row)
                </button>
            </div>

            {{-- ── MODE 3: BULK TEXT LIST ───────────────────────────────── --}}
            <div id="mode-panel-bulk" class="space-y-4 hidden">
                <div>
                    <label for="bulk_names_input" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        कक्षाहरूको नामावली (कक्षा अनुसार कमा वा नयाँ लाइनमा राख्नुहोस्)
                    </label>
                    <textarea id="bulk_names_input" name="bulk_class_names" rows="5" placeholder="Nursery, LKG, UKG, Class 1, Class 2, Class 3, Class 4, Class 5, Class 6, Class 7, Class 8, Class 9, Class 10"
                              class="w-full text-xs font-medium p-3.5 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 outline-none"></textarea>
                    <p class="text-[11px] text-slate-400 mt-1">तपाईं Excel वा अन्य स्रोतबाट एकैपटक धेरै कक्षाहरूको सूची यहाँ पेस्ट गर्न सक्नुहुन्छ।</p>
                </div>
            </div>

            {{-- ── MODE 4: SINGLE CLASS ─────────────────────────────────── --}}
            <div id="mode-panel-single" class="space-y-4 hidden">
                <div>
                    <label for="single_class_name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        कक्षाको नाम (Class Name) *
                    </label>
                    <input type="text" id="single_class_name" name="class_name" placeholder="उदा: Class 10 वा Grade 1"
                           class="w-full text-xs px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
            </div>

            {{-- Form Submit & Actions --}}
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <a href="{{ url('/classes') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 text-xs font-semibold transition">
                    रद्द (Cancel)
                </a>
                <button type="submit" id="btn-submit-classes" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/25 transition flex items-center gap-2">
                    <i class="bi bi-check2-circle text-base"></i> कक्षाहरू सुरक्षित गर्नुहोस् (Create Classes Now)
                </button>
            </div>
        </form>

    </div>
</div>

<script>
    let currentRowCount = 3;

    const presetData = {
        'pg-to-10': {
            names: 'Playgroup, Nursery, LKG, UKG, Class 1, Class 2, Class 3, Class 4, Class 5, Class 6, Class 7, Class 8, Class 9, Class 10',
            count: '१४ कक्षाहरू'
        },
        'pg-to-12': {
            names: 'Playgroup, Nursery, LKG, UKG, Class 1, Class 2, Class 3, Class 4, Class 5, Class 6, Class 7, Class 8, Class 9, Class 10, Class 11, Class 12',
            count: '१६ कक्षाहरू'
        },
        '1-to-8': {
            names: 'Class 1, Class 2, Class 3, Class 4, Class 5, Class 6, Class 7, Class 8',
            count: '८ कक्षाहरू'
        },
        'ecd-to-5': {
            names: 'ECD, Nursery, Class 1, Class 2, Class 3, Class 4, Class 5',
            count: '७ कक्षाहरू'
        },
        '9-to-12': {
            names: 'Class 9, Class 10, Class 11, Class 12',
            count: '४ कक्षाहरू'
        }
    };

    function applyPreset(key) {
        const p = presetData[key];
        if (!p) return;
        document.getElementById('preset-classes-textarea').value = p.names;
        document.getElementById('preset-count-badge').textContent = p.count;

        // Highlight selected preset card
        document.querySelectorAll('.preset-card').forEach(c => c.classList.remove('border-indigo-600', 'bg-indigo-50/40'));
        event.currentTarget.classList.add('border-indigo-600', 'bg-indigo-50/40');
    }

    function switchCreateMode(mode) {
        ['preset', 'multiple', 'bulk', 'single'].forEach(m => {
            const panel = document.getElementById('mode-panel-' + m);
            const btn = document.getElementById('btn-mode-' + m);
            if (panel) panel.classList.toggle('hidden', m !== mode);
            if (btn) {
                if (m === mode) {
                    btn.className = 'mode-tab-btn active px-4 py-2.5 rounded-2xl text-xs font-bold transition flex items-center gap-2 bg-indigo-600 text-white shadow-xs';
                } else {
                    btn.className = 'mode-tab-btn px-4 py-2.5 rounded-2xl text-xs font-bold transition flex items-center gap-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200';
                }
            }
        });

        // Clear or disable unused inputs depending on mode
        if (mode === 'single') {
            document.getElementById('single_class_name').setAttribute('required', 'required');
            document.getElementById('single_class_name').focus();
        } else {
            document.getElementById('single_class_name').removeAttribute('required');
        }
    }

    function addClassRow() {
        const tbody = document.getElementById('dynamic-class-tbody');
        const idx = tbody.children.length;
        const nextRoom = 101 + idx;
        const nextClassNum = idx + 1;

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="p-3 text-center text-slate-400 font-mono row-idx">${idx + 1}</td>
            <td class="p-2">
                <input type="text" name="classes[${idx}][class_name]" value="Class ${nextClassNum}" placeholder="e.g. Class ${nextClassNum}" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold">
            </td>
            <td class="p-2">
                <input type="text" name="classes[${idx}][initial_sections]" value="A, B" placeholder="A, B" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
            </td>
            <td class="p-2">
                <input type="text" name="classes[${idx}][room_no]" value="${nextRoom}" placeholder="${nextRoom}" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
            </td>
            <td class="p-2 text-center">
                <button type="button" onclick="removeClassRow(this)" class="text-slate-400 hover:text-rose-500 p-1"><i class="bi bi-trash3"></i></button>
            </td>
        `;
        tbody.appendChild(tr);
        reindexRows();
    }

    function removeClassRow(btn) {
        const tr = btn.closest('tr');
        if (document.querySelectorAll('#dynamic-class-tbody tr').length > 1) {
            tr.remove();
            reindexRows();
        } else {
            alert('कम्तिमा एउटा कक्षाको पङ्क्ति रहनुपर्छ।');
        }
    }

    function reindexRows() {
        document.querySelectorAll('#dynamic-class-tbody tr').forEach((tr, i) => {
            tr.querySelector('.row-idx').textContent = i + 1;
            tr.querySelectorAll('input').forEach(inp => {
                const name = inp.getAttribute('name');
                if (name) {
                    inp.setAttribute('name', name.replace(/classes\[\d+\]/, `classes[${i}]`));
                }
            });
        });
    }
</script>
@endsection
