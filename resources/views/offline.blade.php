<!DOCTYPE html>
<html lang="en" class="h-full" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>अफलाइन फाइनान्स तथा ड्यासबोर्ड (Offline Finance & POS) — NepalSchoolHub</title>
    <link rel="icon" type="image/png" href="/favicon_io/favicon-32x32.png">

    {{-- Fonts & Icons --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <style>
        :root {
            --bg-canvas: #f8fafc;
            --surface: #ffffff;
            --border: #e2e8f0;
            --primary: #4f46e5;
            --text-main: #0f172a;
            --text-sub: #64748b;
        }
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
            color: var(--text-main);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
        }
        .offline-card {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(226, 232, 240, 0.85);
            border-radius: 1.25rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
        }
        .feature-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 1.15rem;
            display: flex;
            align-items: center;
            gap: 0.875rem;
            text-decoration: none;
            color: inherit;
            transition: all 0.2s ease;
        }
        .feature-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.1);
            border-color: #c7d2fe;
            color: #4338ca;
        }
        .feature-icon {
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }
        .pulse-offline {
            animation: pulse-red 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse-red {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(1.15); }
        }
        .nav-tab-btn.active {
            background: #4f46e5;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
        }
        .nav-tab-btn:not(.active) {
            background: #f1f5f9;
            color: #475569;
        }
        .nav-tab-btn:not(.active):hover {
            background: #e2e8f0;
            color: #1e293b;
        }
        @media print {
            body * {
                visibility: hidden;
            }
            #printable-receipt-area, #printable-receipt-area * {
                visibility: visible;
            }
            #printable-receipt-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                margin: 0;
                padding: 10px;
                background: white;
            }
        }
    </style>
</head>
<body class="py-6 px-3 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto space-y-6">

        {{-- Top Connection Status Bar --}}
        <div class="offline-card p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-600 flex items-center justify-center text-2xl flex-shrink-0">
                    <span id="conn-icon" class="pulse-offline"><i class="bi bi-cloud-slash-fill text-rose-500"></i></span>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-bold tracking-tight text-slate-800" id="conn-title">अफलाइन मोड (Offline Mode Active)</h1>
                        <span id="conn-badge" class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-rose-100 text-rose-700 border border-rose-200">
                            Disconnected
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5" id="conn-desc">
                        इन्टरनेट नभए पनि शुल्क असुली, रसिद प्रिन्ट, खर्च/आम्दानी दर्ता र रोजनाम्चा सम्पूर्ण रूपमा सुचारु छन्।
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                <button onclick="checkConnectionAndReload()" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl border border-slate-200 transition flex items-center gap-1.5">
                    <i class="bi bi-arrow-clockwise"></i> पुनः प्रयास (Retry)
                </button>
                <button onclick="forceSyncNow()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-xs transition flex items-center gap-1.5">
                    <i class="bi bi-arrow-repeat"></i> सिङ्क लाम (Sync Queue: <span id="dash-queue-count">0</span>)
                </button>
            </div>
        </div>

        {{-- Main Navigation Tabs --}}
        <div class="flex items-center gap-2 overflow-x-auto pb-1">
            <button onclick="switchTab('finance-cashier')" id="tab-btn-finance-cashier" class="nav-tab-btn active px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 whitespace-nowrap">
                <i class="bi bi-cash-stack text-sm"></i> शुल्क असुली / POS क्यासियर
            </button>
            <button onclick="switchTab('finance-daybook')" id="tab-btn-finance-daybook" class="nav-tab-btn px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 whitespace-nowrap">
                <i class="bi bi-journal-text text-sm"></i> दैनिक रोजनाम्चा (Day Book)
            </button>
            <button onclick="switchTab('finance-expenses')" id="tab-btn-finance-expenses" class="nav-tab-btn px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 whitespace-nowrap">
                <i class="bi bi-wallet2 text-sm"></i> खर्च / आम्दानी दर्ता
            </button>
            <button onclick="switchTab('modules-all')" id="tab-btn-modules-all" class="nav-tab-btn px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 whitespace-nowrap">
                <i class="bi bi-grid-fill text-sm"></i> अन्य मोड्युलहरू
            </button>
        </div>

        {{-- TAB 1: OFFLINE POS & FEE COLLECTION CASHIER --}}
        <div id="tab-content-finance-cashier" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                {{-- Left: Student Fee Search & Due Details --}}
                <div class="lg:col-span-7 space-y-4">
                    <div class="offline-card p-5">
                        <div class="flex items-center justify-between mb-3">
                            <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                <i class="bi bi-search text-indigo-600"></i> विद्यार्थी शुल्क खोजी (Search Student / Fee Dues)
                            </h2>
                            <span class="text-[11px] text-slate-400">IndexedDB Cache Live</span>
                        </div>

                        <div class="relative">
                            <input type="text" id="fee-student-search" placeholder="विद्यार्थीको नाम, रोल नं वा भर्ना नं टाइप गर्नुहोस्..."
                                   class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                   oninput="searchStudentsForFee(this.value)">
                            <i class="bi bi-search absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                        </div>

                        {{-- Search Results Dropdown --}}
                        <div id="fee-search-results" class="mt-2 space-y-1 max-h-48 overflow-y-auto hidden"></div>

                        {{-- Selected Student & Invoice Details Card --}}
                        <div id="selected-student-card" class="mt-4 p-4 rounded-xl bg-slate-50 border border-slate-200 hidden">
                            <div class="flex items-start justify-between border-b border-slate-200/80 pb-3 mb-3">
                                <div>
                                    <h3 class="font-bold text-slate-800 text-sm" id="card-student-name">Student Name</h3>
                                    <p class="text-xs text-slate-500" id="card-student-details">Class: - | Roll: - | Adm: -</p>
                                </div>
                                <span id="card-student-status-badge" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-amber-100 text-amber-800">
                                    Due
                                </span>
                            </div>

                            <div class="space-y-2">
                                <div class="flex justify-between text-xs text-slate-600">
                                    <span>कुल बिल रकम (Total Invoice):</span>
                                    <span class="font-semibold text-slate-800" id="card-invoice-total">Rs. 0.00</span>
                                </div>
                                <div class="flex justify-between text-xs text-slate-600">
                                    <span>पहिले भुक्तानी (Already Paid):</span>
                                    <span class="font-semibold text-emerald-600" id="card-invoice-paid">Rs. 0.00</span>
                                </div>
                                <div class="flex justify-between text-xs pt-1 border-t border-slate-200 font-bold">
                                    <span class="text-rose-600">बाँकी रकम (Net Due Balance):</span>
                                    <span class="text-rose-700 text-sm" id="card-invoice-due">Rs. 0.00</span>
                                </div>
                            </div>

                            {{-- Itemized breakdown --}}
                            <div id="card-items-breakdown" class="mt-3 pt-2 border-t border-dashed border-slate-200 text-[11px] text-slate-500 space-y-1">
                                <!-- Populated dynamically -->
                            </div>
                        </div>
                    </div>

                    {{-- Recent Offline Collected Receipts --}}
                    <div class="offline-card p-5">
                        <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="bi bi-clock-history text-indigo-500"></i> आज संकलित अफलाइन रसिदहरू (Today's Offline Receipts)
                            </h3>
                            <button onclick="loadRecentReceipts()" class="text-[11px] text-indigo-600 hover:text-indigo-800">
                                <i class="bi bi-arrow-clockwise"></i> ताजा गर्नुहोस्
                            </button>
                        </div>
                        <div id="recent-receipts-list" class="space-y-2 text-xs">
                            <div class="text-center py-4 text-slate-400">कुनै नयाँ अफलाइन रसिद छैन।</div>
                        </div>
                    </div>
                </div>

                {{-- Right: POS Collection Form --}}
                <div class="lg:col-span-5">
                    <div class="offline-card p-5 sticky top-6">
                        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                            <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                <i class="bi bi-receipt text-emerald-600"></i> शुल्क भुक्तानी दर्ता (Collect Fee POS)
                            </h2>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Instant Receipt</span>
                        </div>

                        <form id="offline-pos-form" onsubmit="handleOfflinePosSubmit(event)" class="space-y-3.5">
                            <input type="hidden" id="pos-student-id" value="">
                            <input type="hidden" id="pos-invoice-id" value="">

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">विद्यार्थी (Selected Student)</label>
                                <input type="text" id="pos-student-display" readonly placeholder="बायाँ तर्फबाट विद्यार्थी छान्नुहोस्"
                                       class="w-full px-3 py-2 bg-slate-50 rounded-xl border border-slate-200 text-xs font-medium text-slate-700">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">भुक्तानी रकम (Amount) *</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2 text-slate-400 text-xs font-bold">Rs.</span>
                                        <input type="number" id="pos-amount" step="any" required placeholder="0.00"
                                               class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">भुक्तानी माध्यम (Method)</label>
                                    <select id="pos-payment-method" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500">
                                        <option value="cash" selected>नगद (Cash)</option>
                                        <option value="esewa">eSewa</option>
                                        <option value="khalti">Khalti</option>
                                        <option value="bank_transfer">Bank Transfer / Cheque</option>
                                        <option value="pos_card">Card Swipe (POS)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">छुट/सहुलियत (Discount)</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2 text-slate-400 text-xs font-bold">Rs.</span>
                                        <input type="number" id="pos-discount" step="any" placeholder="0.00"
                                               class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-800 focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">मिति (Date)</label>
                                    <input type="date" id="pos-date" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">कैफियत / टिप्पणी (Remarks / Note)</label>
                                <input type="text" id="pos-note" placeholder="उदा: महिनाको मासिक शुल्क तथा परीक्षा शुल्क"
                                       class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <div class="pt-2">
                                <button type="submit" id="btn-collect-fee" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center justify-center gap-2">
                                    <i class="bi bi-printer-fill"></i> शुल्क असुली गर्नुहोस् र रसिद छाप्नुहोस्
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>

        {{-- TAB 2: OFFLINE DAY BOOK (रोजनाम्चा) --}}
        <div id="tab-content-finance-daybook" class="space-y-6 hidden">
            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="offline-card p-5">
                    <p class="text-xs text-slate-400 font-medium uppercase tracking-wider">सुरुवाती मौज्दात (Opening)</p>
                    <h3 class="text-xl font-bold text-slate-700 mt-1" id="daybook-opening-bal">Rs. 0.00</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Pre-cached balance</p>
                </div>
                <div class="offline-card p-5 bg-emerald-50/30 border-emerald-100">
                    <p class="text-xs text-emerald-600 font-medium uppercase tracking-wider">कुल असुली (Total Inflows)</p>
                    <h3 class="text-2xl font-bold text-emerald-700 mt-1" id="daybook-total-inflows">+ Rs. 0.00</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Fees & Other Income</p>
                </div>
                <div class="offline-card p-5 bg-rose-50/30 border-rose-100">
                    <p class="text-xs text-rose-600 font-medium uppercase tracking-wider">कुल खर्च (Total Outflows)</p>
                    <h3 class="text-2xl font-bold text-rose-700 mt-1" id="daybook-total-outflows">- Rs. 0.00</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Offline recorded expenses</p>
                </div>
                <div class="offline-card p-5 bg-indigo-50/40 border-indigo-100">
                    <p class="text-xs text-indigo-600 font-medium uppercase tracking-wider">हातमा रहेको नगद (Net Cash)</p>
                    <h3 class="text-2xl font-bold text-indigo-900 mt-1" id="daybook-net-cash">Rs. 0.00</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Available Cash in Hand</p>
                </div>
            </div>

            {{-- Day Book Live Register Table --}}
            <div class="offline-card p-5">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                            <i class="bi bi-journal-bookmark-fill text-indigo-600"></i> आजको दैनिक रोजनाम्चा खाता (Today's Transactions Register)
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">अफलाइन गरिएका सबै आम्दानी तथा खर्च विवरणहरू</p>
                    </div>
                    <button onclick="printDayBookSummary()" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl transition flex items-center gap-1.5">
                        <i class="bi bi-printer"></i> Day Book प्रिन्ट गर्नुहोस्
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                                <th class="py-2.5 px-3">समय / मिति</th>
                                <th class="py-2.5 px-3">प्रकार (Type)</th>
                                <th class="py-2.5 px-3">विवरण (Description)</th>
                                <th class="py-2.5 px-3">माध्यम (Method)</th>
                                <th class="py-2.5 px-3 text-right">आम्दानी (Inflow)</th>
                                <th class="py-2.5 px-3 text-right">खर्च (Outflow)</th>
                                <th class="py-2.5 px-3 text-center">स्थिति</th>
                            </tr>
                        </thead>
                        <tbody id="daybook-table-body" class="divide-y divide-slate-100 text-slate-700">
                            <tr>
                                <td colspan="7" class="py-6 text-center text-slate-400">कुनै कारोबार भेटिएन।</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- TAB 3: OFFLINE EXPENSES & INCOME LOGGERS --}}
        <div id="tab-content-finance-expenses" class="space-y-6 hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Record Expense Form --}}
                <div class="offline-card p-5">
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-rose-100 bg-rose-50/40 -mx-5 -mt-5 p-5 rounded-t-2xl">
                        <h2 class="text-sm font-bold text-rose-900 flex items-center gap-2">
                            <i class="bi bi-dash-circle-fill text-rose-600"></i> दैनिक खर्च प्रविष्टि (Record Expense)
                        </h2>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">Cash Outflow</span>
                    </div>

                    <form onsubmit="handleOfflineExpenseSubmit(event)" class="space-y-3.5">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">खर्च शिर्षक (Title / Purpose) *</label>
                            <input type="text" id="exp-title" required placeholder="उदा: स्टेशनरी खरिद, बिजुली महशुल..."
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-rose-500">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">रकम (Amount) *</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-slate-400 text-xs font-bold">Rs.</span>
                                    <input type="number" id="exp-amount" step="any" required placeholder="0.00"
                                           class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-rose-500">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">वर्ग (Category)</label>
                                <select id="exp-category" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-rose-500">
                                    <option value="Office & Admin">Office & Admin</option>
                                    <option value="Utilities (Electricity/Water)">Utilities</option>
                                    <option value="Stationery & Printing">Stationery</option>
                                    <option value="Maintenance & Repair">Maintenance</option>
                                    <option value="Staff Refreshment">Refreshment</option>
                                    <option value="Transport Fuel">Transport Fuel</option>
                                    <option value="Miscellaneous">Miscellaneous</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">भुक्तानी माध्यम</label>
                                <select id="exp-payment-method" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-rose-500">
                                    <option value="cash">नगद (Cash)</option>
                                    <option value="bank_transfer">Bank Transfer / Cheque</option>
                                    <option value="esewa">Digital / eSewa</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">मिति (Date)</label>
                                <input type="date" id="exp-date" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-rose-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">कैफियत / प्राप्तकर्ता (Paid To / Remarks)</label>
                            <input type="text" id="exp-note" placeholder="उदा: राम स्टोर, रसिद नं. १२४"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-rose-500">
                        </div>

                        <button type="submit" class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center justify-center gap-2">
                            <i class="bi bi-plus-circle"></i> खर्च दर्ता गर्नुहोस् (Save Expense Offline)
                        </button>
                    </form>
                </div>

                {{-- Record Other Income Form --}}
                <div class="offline-card p-5">
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-emerald-100 bg-emerald-50/40 -mx-5 -mt-5 p-5 rounded-t-2xl">
                        <h2 class="text-sm font-bold text-emerald-900 flex items-center gap-2">
                            <i class="bi bi-plus-circle-fill text-emerald-600"></i> अन्य आम्दानी प्रविष्टि (Record Other Income)
                        </h2>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Cash Inflow</span>
                    </div>

                    <form onsubmit="handleOfflineIncomeSubmit(event)" class="space-y-3.5">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">आम्दानी शिर्षक (Income Title) *</label>
                            <input type="text" id="inc-title" required placeholder="उदा: भर्ना फारम बिक्री, टाई/बेल्ट..."
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">रकम (Amount) *</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-slate-400 text-xs font-bold">Rs.</span>
                                    <input type="number" id="inc-amount" step="any" required placeholder="0.00"
                                           class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-emerald-500">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">वर्ग (Category)</label>
                                <select id="inc-category" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500">
                                    <option value="Admission Form Sale">Admission Form Sale</option>
                                    <option value="Prospectus Sale">Prospectus Sale</option>
                                    <option value="Uniform & Accessories">Uniform & Accessories</option>
                                    <option value="Hall / Ground Rent">Hall / Ground Rent</option>
                                    <option value="Donation / Grant">Donation / Grant</option>
                                    <option value="Miscellaneous Income">Miscellaneous Income</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">भुक्तानी माध्यम</label>
                                <select id="inc-payment-method" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500">
                                    <option value="cash">नगद (Cash)</option>
                                    <option value="esewa">eSewa</option>
                                    <option value="khalti">Khalti</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">मिति (Date)</label>
                                <input type="date" id="inc-date" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">बुझाउने व्यक्ति / कैफियत (Payer / Remarks)</label>
                            <input type="text" id="inc-note" placeholder="उदा: अभिभावक श्री श्याम शर्मा"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center justify-center gap-2">
                            <i class="bi bi-plus-circle"></i> आम्दानी दर्ता गर्नुहोस् (Save Income Offline)
                        </button>
                    </form>
                </div>

            </div>
        </div>

        {{-- TAB 4: ALL MODULES SHORTCUTS --}}
        <div id="tab-content-modules-all" class="space-y-4 hidden">
            <div class="flex items-center justify-between mb-1 px-1">
                <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wider">
                    <i class="bi bi-lightning-charge-fill text-amber-500"></i> अफलाइन उपलब्ध अन्य सुविधाहरू (Offline Modules)
                </h2>
                <span class="text-xs text-slate-400">IndexedDB v5 Fully Active</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                {{-- 1. Attendance --}}
                <a href="/attendances/take" class="feature-card">
                    <div class="feature-icon bg-emerald-50 text-emerald-600 border border-emerald-100">
                        <i class="bi bi-calendar2-check-fill"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">हाजिरी (Attendance)</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">विद्यार्थी हाजिरी लिने तथा स्थानीय रूपमा सुरक्षित राख्ने</p>
                    </div>
                </a>

                {{-- 2. Homework --}}
                <a href="/homework" class="feature-card">
                    <div class="feature-icon bg-indigo-50 text-indigo-600 border border-indigo-100">
                        <i class="bi bi-pencil-square"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">गृहकार्य (Homework)</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">गृहकार्य हेर्ने, नयाँ सिर्जना गर्ने र डायरी दस्तखत</p>
                    </div>
                </a>

                {{-- 3. Routine / Timetable --}}
                <a href="/routine/view" class="feature-card">
                    <div class="feature-icon bg-blue-50 text-blue-600 border border-blue-100">
                        <i class="bi bi-calendar4-week"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">दिनचर्या (Timetable)</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">कक्षा तालिका तथा शिक्षक समयतालिका हेर्ने</p>
                    </div>
                </a>

                {{-- 4. Notices & Circulars --}}
                <a href="/notices" class="feature-card">
                    <div class="feature-icon bg-amber-50 text-amber-600 border border-amber-100">
                        <i class="bi bi-megaphone-fill"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">सूचना पाटी (Notices)</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">विद्यालयका परिपत्र तथा सूचनाहरू अध्ययन गर्ने</p>
                    </div>
                </a>

                {{-- 5. Visitors / Front Desk --}}
                <a href="/visitors" class="feature-card">
                    <div class="feature-icon bg-purple-50 text-purple-600 border border-purple-100">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">आगन्तुक (Visitors)</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">गेटमा आगन्तुक दर्ता गर्ने र पास जारी गर्ने</p>
                    </div>
                </a>

                {{-- 6. Leave Applications --}}
                <a href="/leave" class="feature-card">
                    <div class="feature-icon bg-rose-50 text-rose-600 border border-rose-100">
                        <i class="bi bi-calendar-x-fill"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">बिदा निवेदन (Leave)</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">विद्यार्थी वा शिक्षक बिदा आवेदन दर्ता गर्ने</p>
                    </div>
                </a>

                {{-- 7. Student Roster --}}
                <a href="/students/view/list" class="feature-card">
                    <div class="feature-icon bg-cyan-50 text-cyan-600 border border-cyan-100">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">विद्यार्थी नामावली</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">कक्षा अनुसार विद्यार्थी विवरण तथा रोल नं</p>
                    </div>
                </a>

                {{-- 8. Syllabus --}}
                <a href="/syllabus/index" class="feature-card">
                    <div class="feature-icon bg-lime-50 text-lime-600 border border-lime-100">
                        <i class="bi bi-book-half"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">पाठ्यक्रम (Syllabus)</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">पाठ्यक्रम तथा शैक्षिक योजनाहरू हेर्ने</p>
                    </div>
                </a>

                {{-- 9. Feedback --}}
                <a href="/feedback/create" class="feature-card">
                    <div class="feature-icon bg-pink-50 text-pink-600 border border-pink-100">
                        <i class="bi bi-chat-heart-fill"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">गुनासो तथा मद्दत</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">सुझाव वा गुनासो दर्ता गर्ने</p>
                    </div>
                </a>
            </div>
        </div>

        {{-- Pending Offline Queue Section --}}
        <div class="offline-card p-5">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <i class="bi bi-clock-history text-indigo-600 font-bold text-base"></i>
                    <h2 class="text-sm font-bold text-slate-800">सिङ्क हुन बाँकी सूची (Pending Sync Queue)</h2>
                </div>
                <button onclick="forceSyncNow()" id="btn-force-sync" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                    <i class="bi bi-arrow-repeat"></i> अहिले सिङ्क गर्नुहोस्
                </button>
            </div>

            <div id="queue-container" class="space-y-2">
                <div class="text-center py-6 text-slate-400 text-xs" id="queue-empty-msg">
                    <i class="bi bi-check-circle text-emerald-500 text-2xl block mb-1"></i>
                    सिङ्क हुन बाँकी कुनै नयाँ विवरण छैन। All offline records are up to date.
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="text-center text-xs text-slate-400 py-2">
            NepalSchoolHub PWA Engine • Full Offline Finance & POS Cashier System Active
        </div>

    </div>

    {{-- PRINTABLE RECEIPT MODAL (80mm / A5 Cashier Voucher) --}}
    <div id="receipt-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden">
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden border border-slate-100 animate-in fade-in zoom-in duration-200">
            <div class="p-4 bg-slate-800 text-white flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider flex items-center gap-2">
                    <i class="bi bi-check2-circle text-emerald-400 text-base"></i> शुल्क असुली रसिद (Fee Receipt)
                </span>
                <button onclick="closeReceiptModal()" class="text-slate-400 hover:text-white text-lg">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            {{-- Printable slip area --}}
            <div id="printable-receipt-area" class="p-6 text-slate-800 text-xs font-mono space-y-3">
                <div class="text-center border-b pb-3">
                    <h2 class="text-base font-bold tracking-tight text-slate-900 uppercase">नेपाल स्कुल हब (NepalSchoolHub)</h2>
                    <p class="text-[11px] text-slate-500 mt-0.5">School Management & POS System</p>
                    <div class="mt-1.5 inline-block px-2 py-0.5 bg-slate-100 rounded text-[10px] font-bold text-slate-700">
                        OFFLINE CASHIER RECEIPT
                    </div>
                </div>

                <div class="space-y-1 text-[11px] border-b pb-2.5">
                    <div class="flex justify-between">
                        <span class="text-slate-500">रसिद नं (Receipt No):</span>
                        <strong id="rec-number" class="text-slate-900">OFF-REC-0000</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">मिति (Date):</span>
                        <span id="rec-date">2026-10-05</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">विद्यार्थी (Student):</span>
                        <strong id="rec-student-name">Student Name</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">कक्षा / रोल (Class/Roll):</span>
                        <span id="rec-student-class">Class - | Roll -</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">माध्यम (Payment Mode):</span>
                        <span id="rec-payment-mode" class="uppercase font-semibold">CASH</span>
                    </div>
                </div>

                <div class="space-y-1.5 py-1">
                    <div class="flex justify-between font-bold text-sm text-slate-900">
                        <span>असुली रकम (Paid Amount):</span>
                        <span id="rec-paid-amount">Rs. 0.00</span>
                    </div>
                    <div class="flex justify-between text-[11px] text-slate-500">
                        <span>छुट (Discount):</span>
                        <span id="rec-discount-amount">Rs. 0.00</span>
                    </div>
                    <div class="flex justify-between text-[11px] text-rose-600 font-bold border-t pt-1">
                        <span>बाँकी बक्यौता (Remaining Due):</span>
                        <span id="rec-remaining-due">Rs. 0.00</span>
                    </div>
                </div>

                <div class="border-t pt-3 mt-3 text-center text-[10px] text-slate-400 space-y-1">
                    <p>यो कम्प्युटर प्रणालीबाट जारी गरिएको आधिकारिक अफलाइन रसिद हो।</p>
                    <p>धन्यवाद ! (Thank You)</p>
                </div>
            </div>

            <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2">
                <button onclick="closeReceiptModal()" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-200">
                    बन्द गर्नुहोस् (Close)
                </button>
                <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow transition flex items-center gap-1.5">
                    <i class="bi bi-printer"></i> रसिद छाप्नुहोस् (Print Slip)
                </button>
            </div>
        </div>
    </div>

    {{-- Offline Scripts --}}
    <script src="{{ asset('js/offline-db.js') }}"></script>
    <script src="{{ asset('js/offline-seed.js') }}"></script>
    <script src="{{ asset('js/attendance-sync.js') }}"></script>
    <script src="{{ asset('js/offline-forms.js') }}"></script>
    <script src="{{ asset('js/offline-sync.js') }}"></script>

    <script>
        let cachedStudents = [];
        let cachedInvoices = [];
        let selectedStudent = null;
        let selectedInvoice = null;

        function switchTab(tabId) {
            ['finance-cashier', 'finance-daybook', 'finance-expenses', 'modules-all'].forEach(id => {
                const el = document.getElementById('tab-content-' + id);
                const btn = document.getElementById('tab-btn-' + id);
                if (el) el.classList.toggle('hidden', id !== tabId);
                if (btn) btn.classList.toggle('active', id === tabId);
            });
            if (tabId === 'finance-daybook') loadDayBookUI();
            if (tabId === 'finance-cashier') loadRecentReceipts();
        }

        async function initOfflineFinanceData() {
            try {
                if (typeof offlineDbGetAll === 'function') {
                    cachedStudents = (await offlineDbGetAll('students_cache')) || [];
                    cachedInvoices = (await offlineDbGetAll('invoices_cache')) || [];
                }
            } catch (e) {
                console.warn('[OfflineFinance] Cache load error:', e);
            }
            // Set default dates
            const todayStr = new Date().toISOString().slice(0, 10);
            if (document.getElementById('pos-date')) document.getElementById('pos-date').value = todayStr;
            if (document.getElementById('exp-date')) document.getElementById('exp-date').value = todayStr;
            if (document.getElementById('inc-date')) document.getElementById('inc-date').value = todayStr;

            loadRecentReceipts();
            loadDayBookUI();
            loadQueueUI();
        }

        // ── Student Fee Search ────────────────────────────────────────────────
        function searchStudentsForFee(q) {
            const resultsBox = document.getElementById('fee-search-results');
            if (!resultsBox) return;

            q = (q || '').trim().toLowerCase();
            if (!q) {
                resultsBox.classList.add('hidden');
                resultsBox.innerHTML = '';
                return;
            }

            const matched = cachedStudents.filter(s => {
                const name = `${s.first_name || ''} ${s.last_name || ''}`.toLowerCase();
                const roll = String(s.roll_no || '').toLowerCase();
                const adm  = String(s.admission_no || '').toLowerCase();
                return name.includes(q) || roll.includes(q) || adm.includes(q);
            }).slice(0, 8);

            if (matched.length === 0) {
                resultsBox.innerHTML = '<div class="p-3 text-xs text-slate-400 bg-white rounded-xl border border-slate-200">विद्यार्थी फेला परेन।</div>';
                resultsBox.classList.remove('hidden');
                return;
            }

            resultsBox.innerHTML = matched.map(s => `
                <div onclick="selectStudentForFee(${s.student_id || s.id})"
                     class="p-2.5 bg-white hover:bg-indigo-50 border border-slate-200 rounded-xl cursor-pointer flex items-center justify-between transition">
                    <div>
                        <strong class="text-xs text-slate-800">${s.first_name || ''} ${s.last_name || ''}</strong>
                        <div class="text-[10px] text-slate-400">Roll: ${s.roll_no || '-'} | Adm: ${s.admission_no || '-'}</div>
                    </div>
                    <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">Select</span>
                </div>
            `).join('');
            resultsBox.classList.remove('hidden');
        }

        function selectStudentForFee(studentId) {
            const s = cachedStudents.find(st => (st.student_id || st.id) == studentId);
            if (!s) return;

            selectedStudent = s;
            document.getElementById('fee-search-results').classList.add('hidden');
            document.getElementById('fee-student-search').value = `${s.first_name || ''} ${s.last_name || ''}`;

            // Find invoices for this student
            const studentInvs = cachedInvoices.filter(inv => (inv.student_id == studentId || inv.student_name === `${s.first_name || ''} ${s.last_name || ''}`));
            // Get most recent unpaid or due invoice, or latest
            selectedInvoice = studentInvs.find(inv => inv.status !== 'paid') || studentInvs[0] || null;

            const nameEl = document.getElementById('card-student-name');
            const detailsEl = document.getElementById('card-student-details');
            const totalEl = document.getElementById('card-invoice-total');
            const paidEl = document.getElementById('card-invoice-paid');
            const dueEl = document.getElementById('card-invoice-due');
            const badgeEl = document.getElementById('card-student-status-badge');
            const itemsBox = document.getElementById('card-items-breakdown');

            nameEl.textContent = `${s.first_name || ''} ${s.last_name || ''}`;
            detailsEl.textContent = `Roll: ${s.roll_no || '-'} | Adm: ${s.admission_no || '-'} | Class ID: ${s.class_id || '-'}`;

            let totalAmt = 0;
            let paidAmt = 0;
            let dueAmt = 0;

            if (selectedInvoice) {
                totalAmt = Number(selectedInvoice.total_amount || 0);
                paidAmt  = Number(selectedInvoice.paid_amount || 0);
                dueAmt   = Number(selectedInvoice.due_amount || (totalAmt - paidAmt));
                if (dueAmt < 0) dueAmt = 0;

                badgeEl.textContent = selectedInvoice.status || (dueAmt === 0 ? 'PAID' : 'DUE');
                badgeEl.className = dueAmt === 0 ? 'px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800' : 'px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-rose-100 text-rose-800';

                // Display items breakdown
                if (Array.isArray(selectedInvoice.items) && selectedInvoice.items.length) {
                    itemsBox.innerHTML = '<strong>Fee Breakdown:</strong><br>' + selectedInvoice.items.map(it => `
                        <div class="flex justify-between">
                            <span>• ${it.fee_type_name || it.fee_name || 'Fee Item'}</span>
                            <span>Rs. ${Number(it.amount || 0).toFixed(2)}</span>
                        </div>
                    `).join('');
                } else {
                    itemsBox.innerHTML = `<span>• Monthly Academic & Tuition Fees (Rs. ${totalAmt.toFixed(2)})</span>`;
                }
            } else {
                dueAmt = 0;
                badgeEl.textContent = 'NO INVOICE';
                badgeEl.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-slate-100 text-slate-600';
                itemsBox.innerHTML = '<span>Direct POS Cash Collection</span>';
            }

            totalEl.textContent = 'Rs. ' + totalAmt.toFixed(2);
            paidEl.textContent  = 'Rs. ' + paidAmt.toFixed(2);
            dueEl.textContent   = 'Rs. ' + dueAmt.toFixed(2);

            document.getElementById('selected-student-card').classList.remove('hidden');

            // Fill POS Form
            document.getElementById('pos-student-id').value = s.student_id || s.id;
            document.getElementById('pos-invoice-id').value = selectedInvoice ? selectedInvoice.id : '';
            document.getElementById('pos-student-display').value = `${s.first_name || ''} ${s.last_name || ''} (Roll: ${s.roll_no || '-'})`;
            document.getElementById('pos-amount').value = dueAmt > 0 ? dueAmt : '';
            document.getElementById('pos-amount').focus();
        }

        // ── POS Collection Submit ─────────────────────────────────────────────
        async function handleOfflinePosSubmit(e) {
            e.preventDefault();
            const studentId = document.getElementById('pos-student-id').value;
            const invoiceId = document.getElementById('pos-invoice-id').value;
            const amount = parseFloat(document.getElementById('pos-amount').value || '0');
            const discount = parseFloat(document.getElementById('pos-discount').value || '0');
            const method = document.getElementById('pos-payment-method').value || 'cash';
            const date = document.getElementById('pos-date').value || new Date().toISOString().slice(0, 10);
            const note = document.getElementById('pos-note').value || 'Offline Cashier Receipt';

            if (!studentId || !selectedStudent) {
                alert('कृपया पहिले विद्यार्थी छान्नुहोस्। (Please select a student first)');
                return;
            }
            if (amount <= 0) {
                alert('कृपया मान्य भुक्तानी रकम हाल्नुहोस्। (Please enter a valid amount)');
                return;
            }

            const receiptNo = 'OFF-REC-' + Date.now().toString().slice(-6);

            // 1. Queue in pending_sync
            const syncPayload = {
                student_id: studentId,
                invoice_id: invoiceId || null,
                amount: amount,
                discount: discount,
                payment_method: method,
                payment_date: date,
                reference_no: receiptNo,
                note: note,
            };

            let localQueueId = null;
            if (typeof offlineQueueAdd === 'function') {
                localQueueId = await offlineQueueAdd({
                    module: 'fee_collection',
                    action: 'store',
                    endpoint: invoiceId ? `/payments/${invoiceId}/pay` : '/payments',
                    method: 'POST',
                    payload: syncPayload,
                    label: `Fee: ${selectedStudent.first_name || ''} ${selectedStudent.last_name || ''} (Rs. ${amount})`,
                    meta: { receipt_no: receiptNo },
                });
            }

            // 2. Write to payments_cache
            const paymentRecord = {
                id: 'offline-' + (localQueueId || Date.now()),
                invoice_id: invoiceId ? Number(invoiceId) : null,
                student_id: Number(studentId),
                student_name: `${selectedStudent.first_name || ''} ${selectedStudent.last_name || ''}`,
                amount: amount,
                discount: discount,
                payment_method: method,
                payment_date: date,
                reference_no: receiptNo,
                status: 'completed',
                note: note,
                _offline: true,
                _created_at: new Date().toISOString(),
            };

            if (typeof offlineDbPut === 'function') {
                await offlineDbPut('payments_cache', paymentRecord);

                // Update invoice cache optimistically
                if (selectedInvoice && typeof offlineDbGet === 'function') {
                    const inv = await offlineDbGet('invoices_cache', Number(selectedInvoice.id));
                    if (inv) {
                        inv.paid_amount = (Number(inv.paid_amount) || 0) + amount + discount;
                        inv.due_amount  = Math.max(0, (Number(inv.total_amount) || 0) - inv.paid_amount);
                        inv.status      = inv.due_amount <= 0 ? 'paid' : 'partial';
                        await offlineDbPut('invoices_cache', inv);
                        // update in-memory
                        const idx = cachedInvoices.findIndex(i => i.id == inv.id);
                        if (idx !== -1) cachedInvoices[idx] = inv;
                    }
                }
            }

            // 3. Show Receipt Modal
            showReceiptModal({
                receiptNo: receiptNo,
                date: date,
                studentName: `${selectedStudent.first_name || ''} ${selectedStudent.last_name || ''}`,
                studentClass: `Roll: ${selectedStudent.roll_no || '-'} | Adm: ${selectedStudent.admission_no || '-'}`,
                paymentMode: method,
                paidAmount: amount,
                discount: discount,
                remainingDue: selectedInvoice ? Math.max(0, (selectedInvoice.due_amount || 0) - amount - discount) : 0,
            });

            // Reset form & reload UI
            document.getElementById('offline-pos-form').reset();
            document.getElementById('pos-date').value = new Date().toISOString().slice(0, 10);
            document.getElementById('selected-student-card').classList.add('hidden');
            selectedStudent = null;
            selectedInvoice = null;

            loadRecentReceipts();
            loadDayBookUI();
            loadQueueUI();
        }

        function showReceiptModal(data) {
            document.getElementById('rec-number').textContent = data.receiptNo;
            document.getElementById('rec-date').textContent = data.date;
            document.getElementById('rec-student-name').textContent = data.studentName;
            document.getElementById('rec-student-class').textContent = data.studentClass;
            document.getElementById('rec-payment-mode').textContent = data.paymentMode;
            document.getElementById('rec-paid-amount').textContent = 'Rs. ' + data.paidAmount.toFixed(2);
            document.getElementById('rec-discount-amount').textContent = 'Rs. ' + data.discount.toFixed(2);
            document.getElementById('rec-remaining-due').textContent = 'Rs. ' + data.remainingDue.toFixed(2);

            document.getElementById('receipt-modal').classList.remove('hidden');
        }

        function closeReceiptModal() {
            document.getElementById('receipt-modal').classList.add('hidden');
        }

        // ── Expense Submit ───────────────────────────────────────────────────
        async function handleOfflineExpenseSubmit(e) {
            e.preventDefault();
            const title = document.getElementById('exp-title').value.trim();
            const amount = parseFloat(document.getElementById('exp-amount').value || '0');
            const category = document.getElementById('exp-category').value;
            const method = document.getElementById('exp-payment-method').value;
            const date = document.getElementById('exp-date').value || new Date().toISOString().slice(0, 10);
            const note = document.getElementById('exp-note').value;

            if (amount <= 0) return alert('कृपया रकम खुलाउनुहोस्।');

            const payload = {
                title: title,
                name: title,
                amount: amount,
                category: category,
                payment_method: method,
                expense_date: date,
                description: note,
            };

            let localId = null;
            if (typeof offlineQueueAdd === 'function') {
                localId = await offlineQueueAdd({
                    module: 'expense',
                    action: 'store',
                    endpoint: '/finance/expenses',
                    method: 'POST',
                    payload: payload,
                    label: `Expense: ${title} (Rs. ${amount})`,
                });
            }

            if (typeof offlineDbPut === 'function') {
                await offlineDbPut('expenses_cache', {
                    id: 'offline-' + (localId || Date.now()),
                    ...payload,
                    _offline: true,
                    _created_at: new Date().toISOString(),
                });
            }

            alert('खर्च सफलतापूर्वक अफलाइन सुरक्षित गरियो। (Expense saved offline)');
            e.target.reset();
            document.getElementById('exp-date').value = new Date().toISOString().slice(0, 10);
            loadDayBookUI();
            loadQueueUI();
        }

        // ── Income Submit ────────────────────────────────────────────────────
        async function handleOfflineIncomeSubmit(e) {
            e.preventDefault();
            const title = document.getElementById('inc-title').value.trim();
            const amount = parseFloat(document.getElementById('inc-amount').value || '0');
            const category = document.getElementById('inc-category').value;
            const method = document.getElementById('inc-payment-method').value;
            const date = document.getElementById('inc-date').value || new Date().toISOString().slice(0, 10);
            const note = document.getElementById('inc-note').value;

            if (amount <= 0) return alert('कृपया रकम खुलाउनुहोस्।');

            const payload = {
                title: title,
                name: title,
                amount: amount,
                category: category,
                payment_method: method,
                income_date: date,
                description: note,
            };

            let localId = null;
            if (typeof offlineQueueAdd === 'function') {
                localId = await offlineQueueAdd({
                    module: 'income',
                    action: 'store',
                    endpoint: '/finance/income',
                    method: 'POST',
                    payload: payload,
                    label: `Income: ${title} (Rs. ${amount})`,
                });
            }

            if (typeof offlineDbPut === 'function') {
                await offlineDbPut('income_cache', {
                    id: 'offline-' + (localId || Date.now()),
                    ...payload,
                    _offline: true,
                    _created_at: new Date().toISOString(),
                });
            }

            alert('आम्दानी सफलतापूर्वक अफलाइन सुरक्षित गरियो। (Income saved offline)');
            e.target.reset();
            document.getElementById('inc-date').value = new Date().toISOString().slice(0, 10);
            loadDayBookUI();
            loadQueueUI();
        }

        // ── Load Recent Receipts List ─────────────────────────────────────────
        async function loadRecentReceipts() {
            const listEl = document.getElementById('recent-receipts-list');
            if (!listEl || typeof offlineDbGetAll !== 'function') return;

            try {
                const payments = (await offlineDbGetAll('payments_cache')) || [];
                const sorted = payments.reverse().slice(0, 5);

                if (sorted.length === 0) {
                    listEl.innerHTML = '<div class="text-center py-4 text-slate-400">कुनै नयाँ अफलाइन रसिद छैन।</div>';
                    return;
                }

                listEl.innerHTML = sorted.map(p => `
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                        <div>
                            <div class="font-bold text-slate-800">${p.student_name || 'Student Fee'}</div>
                            <div class="text-[10px] text-slate-500">${p.reference_no || 'OFFLINE'} • ${p.payment_method || 'cash'} • ${p.payment_date || ''}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-emerald-700">Rs. ${Number(p.amount || 0).toFixed(2)}</div>
                            <span class="text-[9px] font-bold uppercase px-1.5 py-0.2 rounded bg-amber-100 text-amber-800">Pending Sync</span>
                        </div>
                    </div>
                `).join('');
            } catch (e) {
                console.error(e);
            }
        }

        // ── Load Day Book UI ──────────────────────────────────────────────────
        async function loadDayBookUI() {
            if (typeof offlineDbGetAll !== 'function') return;

            try {
                const payments = (await offlineDbGetAll('payments_cache')) || [];
                const expenses = (await offlineDbGetAll('expenses_cache')) || [];
                const incomes  = (await offlineDbGetAll('income_cache')) || [];

                let feeInflow = 0;
                payments.forEach(p => feeInflow += Number(p.amount || 0));

                let otherInflow = 0;
                incomes.forEach(i => otherInflow += Number(i.amount || 0));

                let totalInflow = feeInflow + otherInflow;

                let totalOutflow = 0;
                expenses.forEach(e => totalOutflow += Number(e.amount || 0));

                let netCash = totalInflow - totalOutflow;

                if (document.getElementById('daybook-total-inflows')) {
                    document.getElementById('daybook-total-inflows').textContent = '+ Rs. ' + totalInflow.toFixed(2);
                }
                if (document.getElementById('daybook-total-outflows')) {
                    document.getElementById('daybook-total-outflows').textContent = '- Rs. ' + totalOutflow.toFixed(2);
                }
                if (document.getElementById('daybook-net-cash')) {
                    document.getElementById('daybook-net-cash').textContent = 'Rs. ' + netCash.toFixed(2);
                }

                // Table rows
                const tbody = document.getElementById('daybook-table-body');
                if (!tbody) return;

                const allTx = [];
                payments.forEach(p => allTx.push({
                    time: p.payment_date || 'Today',
                    type: 'Fee Collection',
                    desc: `${p.student_name || 'Student'} (${p.reference_no || ''})`,
                    method: p.payment_method || 'cash',
                    inflow: Number(p.amount || 0),
                    outflow: 0,
                    status: p._offline ? 'Offline' : 'Synced'
                }));
                incomes.forEach(i => allTx.push({
                    time: i.income_date || 'Today',
                    type: 'Other Income',
                    desc: i.title || i.name || 'Income',
                    method: i.payment_method || 'cash',
                    inflow: Number(i.amount || 0),
                    outflow: 0,
                    status: i._offline ? 'Offline' : 'Synced'
                }));
                expenses.forEach(e => allTx.push({
                    time: e.expense_date || 'Today',
                    type: 'Expense',
                    desc: `${e.title || e.name || 'Expense'} (${e.category || ''})`,
                    method: e.payment_method || 'cash',
                    inflow: 0,
                    outflow: Number(e.amount || 0),
                    status: e._offline ? 'Offline' : 'Synced'
                }));

                if (allTx.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" class="py-6 text-center text-slate-400">कुनै कारोबार भेटिएन।</td></tr>';
                    return;
                }

                tbody.innerHTML = allTx.map(tx => `
                    <tr class="hover:bg-slate-50">
                        <td class="py-2 px-3 text-slate-500">${tx.time}</td>
                        <td class="py-2 px-3 font-semibold ${tx.outflow > 0 ? 'text-rose-600' : 'text-emerald-600'}">${tx.type}</td>
                        <td class="py-2 px-3 text-slate-800 font-medium">${tx.desc}</td>
                        <td class="py-2 px-3 text-slate-500 uppercase">${tx.method}</td>
                        <td class="py-2 px-3 text-right font-bold text-emerald-600">${tx.inflow > 0 ? 'Rs. ' + tx.inflow.toFixed(2) : '-'}</td>
                        <td class="py-2 px-3 text-right font-bold text-rose-600">${tx.outflow > 0 ? 'Rs. ' + tx.outflow.toFixed(2) : '-'}</td>
                        <td class="py-2 px-3 text-center">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold ${tx.status === 'Offline' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'}">
                                ${tx.status}
                            </span>
                        </td>
                    </tr>
                `).join('');
            } catch (e) {
                console.error(e);
            }
        }

        function printDayBookSummary() {
            window.print();
        }

        // ── Load Queue ────────────────────────────────────────────────────────
        async function loadQueueUI() {
            if (typeof offlineQueueGetAll !== 'function') return;
            try {
                const items = await offlineQueueGetAll();
                const pending = items.filter(r => r.status !== 'synced');
                const countSpan = document.getElementById('dash-queue-count');
                if (countSpan) countSpan.textContent = pending.length;

                const container = document.getElementById('queue-container');
                const emptyMsg = document.getElementById('queue-empty-msg');

                if (pending.length === 0) {
                    if (emptyMsg) emptyMsg.style.display = 'block';
                    if (container) container.innerHTML = '<div class="text-center py-6 text-slate-400 text-xs"><i class="bi bi-check-circle text-emerald-500 text-2xl block mb-1"></i>सिङ्क हुन बाँकी कुनै नयाँ विवरण छैन। All records are up to date.</div>';
                    return;
                }
                if (emptyMsg) emptyMsg.style.display = 'none';

                container.innerHTML = '';
                pending.forEach(item => {
                    const el = document.createElement('div');
                    el.className = 'flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs';
                    el.innerHTML = `
                        <div class="flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                                <i class="bi bi-file-earmark-text"></i>
                            </span>
                            <div>
                                <p class="font-bold text-slate-800 m-0">${item.label || item.module}</p>
                                <p class="text-[10px] text-slate-400 m-0">${new Date(item.created_at).toLocaleString()}</p>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase ${item.status === 'failed' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-800'}">
                            ${item.status}
                        </span>
                    `;
                    container.appendChild(el);
                });
            } catch(e) {
                console.error(e);
            }
        }

        function checkConnectionAndReload() {
            if (navigator.onLine) {
                window.location.href = '/home';
            } else {
                alert('उपकरण अझै अफलाइन छ। कृपया इन्टरनेट जडान जाँच गर्नुहोस्। (Device is still offline)');
            }
        }

        function forceSyncNow() {
            if (!navigator.onLine) {
                alert('उपकरण अहिले पनि अफलाइन छ। इन्टरनेट जोडेपछि मात्र सिङ्क सम्भव हुन्छ। (Device is offline)');
                return;
            }
            if (typeof offlineSyncFlush === 'function') {
                offlineSyncFlush().then(() => {
                    loadQueueUI();
                    loadDayBookUI();
                    loadRecentReceipts();
                });
            }
        }

        function updateStatus() {
            const isOnline = navigator.onLine;
            const badge = document.getElementById('conn-badge');
            const title = document.getElementById('conn-title');
            const icon = document.getElementById('conn-icon');

            if (isOnline) {
                if (badge) {
                    badge.textContent = 'Online';
                    badge.className = 'px-2.5 py-0.5 text-xs font-bold rounded-full bg-emerald-100 text-emerald-700 border border-emerald-200';
                }
                if (title) title.textContent = 'अनलाइन जडान भयो (Back Online)';
                if (icon) icon.innerHTML = '<i class="bi bi-wifi text-emerald-500"></i>';
            } else {
                if (badge) {
                    badge.textContent = 'Disconnected';
                    badge.className = 'px-2.5 py-0.5 text-xs font-bold rounded-full bg-rose-100 text-rose-700 border border-rose-200';
                }
                if (title) title.textContent = 'अफलाइन मोड (Offline Mode Active)';
                if (icon) icon.innerHTML = '<i class="bi bi-cloud-slash-fill text-rose-500"></i>';
            }
            loadQueueUI();
        }

        window.addEventListener('online', updateStatus);
        window.addEventListener('offline', updateStatus);
        document.addEventListener('DOMContentLoaded', () => {
            updateStatus();
            initOfflineFinanceData();
        });
        document.addEventListener('offline-seed-complete', () => {
            initOfflineFinanceData();
        });
    </script>
</body>
</html>
