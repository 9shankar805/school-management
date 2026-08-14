<!doctype html>
<html lang="en" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>School Performance Dashboard — IEMIS CEHRD Nepal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@latest"></script>
    <style>
        body {
            background-color: #eef2f6 !important;
            font-family: 'Inter', sans-serif !important;
            color: #1e293b !important;
        }
        .cehrd-header {
            background: linear-gradient(135deg, #0747a6 0%, #0052cc 50%, #091e42 100%);
            color: #ffffff;
        }
        .cehrd-nav {
            background-color: #00388c;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
        }
        .filter-card {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 0.75rem;
            padding: 1rem 1.25rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .filter-select {
            background-color: #ffffff !important;
            color: #0f172a !important;
            font-weight: 600 !important;
            font-size: 0.8125rem !important;
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 0.5rem !important;
            padding: 0.45rem 0.75rem !important;
            width: 100% !important;
        }
        .filter-select:focus {
            border-color: #4f46e5 !important;
            outline: none !important;
        }
        .metric-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.875rem;
            padding: 0.875rem 1rem;
            box-shadow: 0 2px 4px rgba(15, 23, 42, 0.04);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        /* Official CEHRD Dashboard Summary Icon Class */
        .school-report-card-dashboard-summary-icon {
            font-size: 2.2rem !important;
            line-height: 1 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: auto !important;
            height: auto !important;
            background-color: transparent !important;
            background: transparent !important;
            border-radius: 0 !important;
            flex-shrink: 0 !important;
        }
        .widget-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 1.25rem;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
            height: 100%;
        }
        .widget-title {
            font-size: 0.875rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
    </style>
</head>
<body>

    {{-- Official Government Header --}}
    <header class="cehrd-header shadow-md">
        <div class="max-w-7xl mx-auto px-4 py-4 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3 text-center md:text-left">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/9/9b/Emblem_of_Nepal.svg/200px-Emblem_of_Nepal.svg.png" alt="Government of Nepal" class="w-14 h-14 object-contain">
                <div>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-rose-200">Government of Nepal</h2>
                    <p class="text-[11px] text-slate-200">Ministry of Education, Science and Technology</p>
                    <p class="text-[10px] text-slate-300">Department of Education · Sanothimi, Bhaktapur</p>
                </div>
            </div>
            <div class="text-center md:text-right">
                <h1 class="text-xl md:text-2xl font-extrabold tracking-tight text-white">Integrated Educational Management Information System (IEMIS)</h1>
                <p class="text-xs text-blue-200 font-medium mt-0.5">School Performance & CEHRD Compliance Dashboard</p>
            </div>
            <div>
                <a href="{{ route('login') }}" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow-sm transition">
                    <i class="bi bi-box-arrow-in-right me-1"></i> EMIS Login
                </a>
            </div>
        </div>

        {{-- Nav Links --}}
        <nav class="cehrd-nav px-4 py-2">
            <div class="max-w-7xl mx-auto flex items-center justify-between text-xs font-semibold text-white">
                <div class="flex items-center gap-6 overflow-x-auto">
                    <a href="{{ route('iemis.school.dashboard') }}" class="hover:text-blue-200 text-white font-bold flex items-center gap-1">
                        <i class="bi bi-house-door-fill"></i> Home
                    </a>
                    <a href="#" class="hover:text-blue-200 text-slate-200">School GIS</a>
                    <a href="#" class="hover:text-blue-200 text-slate-200">School Report</a>
                    <a href="#" class="hover:text-blue-200 text-slate-200">LG Report</a>
                    <a href="#" class="hover:text-blue-200 text-slate-200">Data Portal <i class="bi bi-caret-down-fill text-[9px]"></i></a>
                </div>
                <div class="hidden sm:block text-[11px] text-blue-200 font-medium">
                    CEHRD Session B.S. 2083
                </div>
            </div>
        </nav>
    </header>

    {{-- Main Content Container --}}
    <main class="max-w-7xl mx-auto px-4 py-6 space-y-6">

        {{-- Section Header Title --}}
        <div class="flex items-center justify-between border-b border-slate-300 pb-2">
            <h2 class="font-extrabold text-slate-800 text-sm uppercase tracking-wider flex items-center gap-2">
                <i class="bi bi-speedometer2 text-indigo-600"></i> SCHOOL PERFORMANCE DASHBOARD
            </h2>
            <span class="text-xs text-slate-500 font-medium">Data Source: Official CEHRD IEMIS Portal</span>
        </div>

        {{-- Filter Bar Card --}}
        <form method="GET" action="{{ route('iemis.school.dashboard') }}" class="filter-card">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 lg:grid-cols-6 gap-3 items-end">
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Province</label>
                    <select name="province" class="filter-select">
                        <option value="Madhesh Province">Madhesh Province</option>
                        <option value="Bagmati">Bagmati Province</option>
                        <option value="Koshi">Koshi Province</option>
                        <option value="Gandaki">Gandaki Province</option>
                        <option value="Lumbini">Lumbini Province</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">District</label>
                    <select name="district" class="filter-select">
                        <option value="Siraha">Siraha</option>
                        <option value="Kathmandu">Kathmandu</option>
                        <option value="Lalitpur">Lalitpur</option>
                        <option value="Bhaktapur">Bhaktapur</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Municipality</label>
                    <select name="municipality" class="filter-select">
                        <option value="Siraha Municipality">Siraha Municipality</option>
                        <option value="Kathmandu Metro">Kathmandu Metro</option>
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">EMIS Code | School</label>
                    <select name="school" class="filter-select" onchange="this.form.submit()">
                        <option value="{{ $school->slug }}">{{ $school->iemis_code ?? '160970011' }} — {{ $school->name }}</option>
                        @foreach($allSchools as $s)
                        @if($s->id != $school->id)
                        <option value="{{ $s->slug }}">{{ $s->iemis_code ?? '27001000' . $s->id }} — {{ $s->name }}</option>
                        @endif
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Academic Year</label>
                    <select name="year" class="filter-select">
                        <option value="2083">2083</option>
                        <option value="2082">2082</option>
                        <option value="2081">2081</option>
                    </select>
                </div>
            </div>
        </form>

        {{-- School Details Header Card --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <span class="school-report-card-dashboard-summary-icon bg-amber-50 text-amber-600 border border-amber-200 text-3xl">
                    🏫
                </span>
                <div>
                    <h1 class="text-xl font-extrabold text-slate-900 leading-tight">School Performance Dashboard</h1>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">
                        {{ $school->name }} | {{ $school->district ? $school->district . ' Municipality-7, ' : 'Siraha Municipality-7, Siraha, ' }}{{ $school->province ?? 'Madhesh Province' }}
                    </p>
                    <div class="flex flex-wrap items-center gap-3 text-xs text-slate-700 font-bold mt-1.5">
                        <span>IEMIS CODE: <span class="text-indigo-600">{{ $school->iemis_code ?? '160970011' }}</span></span>
                        <span>·</span>
                        <span>Contact Number: <span class="text-slate-900">{{ $school->phone ?? '9862999897' }}</span></span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl flex items-center gap-2">
                    <span class="text-base">📅</span>
                    <div>
                        <span class="block text-[9px] font-bold text-slate-400 uppercase">Academic Year</span>
                        <span class="text-xs font-extrabold text-slate-800">2083</span>
                    </div>
                </div>
                <div class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl flex items-center gap-2">
                    <span class="text-base">🏢</span>
                    <div>
                        <span class="block text-[9px] font-bold text-slate-400 uppercase">School Type</span>
                        <span class="text-xs font-extrabold text-slate-800">Private</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Top 8 Metric Summary Cards with Official Icons --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">

            {{-- Metric 1: Students --}}
            <div class="metric-card">
                <span class="school-report-card-dashboard-summary-icon">🎓</span>
                <div>
                    <span class="block text-[10px] font-bold text-slate-500 uppercase">Students</span>
                    <span class="text-base font-extrabold text-slate-900 leading-none">{{ $totalStudents }}</span>
                    <span class="text-[10px] font-bold text-emerald-600 block mt-0.5">13.86 %</span>
                </div>
            </div>

            {{-- Metric 2: Boys --}}
            <div class="metric-card">
                <span class="school-report-card-dashboard-summary-icon">👦</span>
                <div>
                    <span class="block text-[10px] font-bold text-slate-500 uppercase">Boys</span>
                    <span class="text-base font-extrabold text-slate-900 leading-none">{{ $totalBoys }}</span>
                    <span class="text-[10px] font-bold text-emerald-600 block mt-0.5">13.64 %</span>
                </div>
            </div>

            {{-- Metric 3: Girls --}}
            <div class="metric-card">
                <span class="school-report-card-dashboard-summary-icon">👧</span>
                <div>
                    <span class="block text-[10px] font-bold text-slate-500 uppercase">Girls</span>
                    <span class="text-base font-extrabold text-slate-900 leading-none">{{ $totalGirls }}</span>
                    <span class="text-[10px] font-bold text-emerald-600 block mt-0.5">14.29 %</span>
                </div>
            </div>

            {{-- Metric 4: Teachers --}}
            <div class="metric-card">
                <span class="school-report-card-dashboard-summary-icon">🧑‍🏫</span>
                <div>
                    <span class="block text-[10px] font-bold text-slate-500 uppercase">Teachers</span>
                    <span class="text-base font-extrabold text-slate-900 leading-none">{{ $totalTeachers }}</span>
                    <span class="text-[10px] font-bold text-indigo-600 block mt-0.5">Active</span>
                </div>
            </div>

            {{-- Metric 5: Buildings / Rooms --}}
            <div class="metric-card">
                <span class="school-report-card-dashboard-summary-icon">🏫</span>
                <div>
                    <span class="block text-[10px] font-bold text-slate-500 uppercase">Buildings \| Rooms</span>
                    <span class="text-base font-extrabold text-slate-900 leading-none">0 \| 0 \| 0</span>
                </div>
            </div>

            {{-- Metric 6: Toilets --}}
            <div class="metric-card">
                <span class="school-report-card-dashboard-summary-icon">🚻</span>
                <div>
                    <span class="block text-[10px] font-bold text-slate-500 uppercase">Toilets</span>
                    <span class="text-base font-extrabold text-slate-900 leading-none">0</span>
                </div>
            </div>

            {{-- Metric 7: Average Attendance --}}
            <div class="metric-card">
                <span class="school-report-card-dashboard-summary-icon">📊</span>
                <div>
                    <span class="block text-[10px] font-bold text-slate-500 uppercase">Average Attendance</span>
                    <span class="text-base font-extrabold text-slate-900 leading-none">41</span>
                </div>
            </div>

            {{-- Metric 8: CEHRD Score --}}
            <div class="metric-card">
                <span class="school-report-card-dashboard-summary-icon">🛡️</span>
                <div>
                    <span class="block text-[10px] font-bold text-slate-500 uppercase">CEHRD Verified</span>
                    <span class="text-base font-extrabold text-emerald-600 leading-none">100%</span>
                </div>
            </div>

        </div>

        {{-- Row 1 Charts: Widgets 1, 2, 3 --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

            {{-- Widget 1: Student by Ethnicity --}}
            <div class="widget-card">
                <div class="widget-title">1. Student by Ethnicity</div>
                <div id="chart-ethnicity" class="flex justify-center" style="min-height:220px"></div>
                <div class="mt-3 grid grid-cols-2 gap-1 text-[10px] font-bold text-slate-600 border-t border-slate-100 pt-2">
                    <span><span class="w-2 h-2 rounded-full inline-block bg-emerald-500 me-1"></span>Janajati - 8 (2.3%)</span>
                    <span><span class="w-2 h-2 rounded-full inline-block bg-blue-500 me-1"></span>Dalit - 21 (6.1%)</span>
                    <span><span class="w-2 h-2 rounded-full inline-block bg-amber-500 me-1"></span>Brahmin/Chhetri - 36 (10.4%)</span>
                    <span><span class="w-2 h-2 rounded-full inline-block bg-purple-500 me-1"></span>Others - 280 (81.2%)</span>
                </div>
            </div>

            {{-- Widget 2: Enrollment by Grade (ECD–12) --}}
            <div class="widget-card lg:col-span-2">
                <div class="widget-title">2. Enrollment by Grade (ECD–12)</div>
                <div id="chart-enrollment-grade" style="min-height:230px"></div>
            </div>

            {{-- Widget 3: Gender Balance --}}
            <div class="widget-card">
                <div class="widget-title">3. Gender Balance</div>
                <div id="chart-gender-balance" style="min-height:200px"></div>
                <div class="flex justify-between px-4 text-xs font-extrabold border-t border-slate-100 pt-2">
                    <span class="text-blue-600">0% Boys</span>
                    <span class="text-pink-600">100% Girls</span>
                </div>
            </div>

        </div>

        {{-- Row 2 Charts: Widgets 4, 5, 6, 7, 8 --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

            {{-- Widget 4: Programs in School --}}
            <div class="widget-card">
                <div class="widget-title">4. Programs in School</div>
                <div class="space-y-2.5 text-xs font-semibold text-slate-700 pt-1">
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-100">
                        <span class="flex items-center gap-2"><span class="text-base">🏛️</span> Model School</span>
                        <span class="text-rose-500 font-extrabold"><i class="bi bi-x-lg"></i></span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-100">
                        <span class="flex items-center gap-2"><span class="text-base">🔬</span> Science School</span>
                        <span class="text-rose-500 font-extrabold"><i class="bi bi-x-lg"></i></span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-100">
                        <span class="flex items-center gap-2"><span class="text-base">🏡</span> Residential School</span>
                        <span class="text-rose-500 font-extrabold"><i class="bi bi-x-lg"></i></span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-100">
                        <span class="flex items-center gap-2"><span class="text-base">📚</span> Resource School</span>
                        <span class="text-rose-500 font-extrabold"><i class="bi bi-x-lg"></i></span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-100">
                        <span class="flex items-center gap-2"><span class="text-base">⚙️</span> Technical School</span>
                        <span class="text-rose-500 font-extrabold"><i class="bi bi-x-lg"></i></span>
                    </div>
                </div>
            </div>

            {{-- Widget 5: Average Marks by Subject (GPA) --}}
            <div class="widget-card">
                <div class="widget-title">5. Average Marks by Subject (GPA)</div>
                <div id="chart-gpa" style="min-height:220px"></div>
            </div>

            {{-- Widget 6 & 7: Teachers (Licensed vs Not Licensed) --}}
            <div class="widget-card">
                <div class="widget-title">6. Teachers (Licensed vs Not Licensed)</div>
                <div id="chart-teacher-qual" style="min-height:220px"></div>
            </div>

            {{-- Widget 8: Types of Disability (Students) --}}
            <div class="widget-card">
                <div class="widget-title">8. Types of Disability (Students)</div>
                <div class="space-y-2 text-xs font-semibold text-slate-700 pt-1">
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-100">
                        <span class="flex items-center gap-2"><span class="text-base">♿</span> Physical Disability</span>
                        <span class="font-extrabold text-slate-900">1</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-100">
                        <span class="flex items-center gap-2"><span class="text-base">👁️</span> Blind and Low Vision</span>
                        <span class="font-extrabold text-slate-900">1</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-100">
                        <span class="flex items-center gap-2"><span class="text-base">👂</span> Deaf and Hard of Hearing</span>
                        <span class="font-extrabold text-slate-400">0</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-100">
                        <span class="flex items-center gap-2"><span class="text-base">👤</span> Deaf-Blind</span>
                        <span class="font-extrabold text-slate-400">0</span>
                    </div>
                </div>
            </div>

        </div>

    </main>

    <footer class="text-center py-6 text-xs text-slate-500 border-t border-slate-300 bg-white mt-8">
        &copy; {{ date('Y') }} CEHRD Nepal — Integrated Educational Management Information System (IEMIS)
    </footer>

    {{-- ApexCharts Script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // Widget 1: Ethnicity Donut Chart
            new ApexCharts(document.querySelector("#chart-ethnicity"), {
                chart: { type: 'donut', height: 210 },
                series: [8, 21, 36, 280],
                labels: ['Janajati', 'Dalit', 'Brahmin/Chhetri', 'Others'],
                colors: ['#10b981', '#3b82f6', '#f59e0b', '#8b5cf6'],
                legend: { show: false },
                dataLabels: { enabled: false },
                plotOptions: {
                    pie: {
                        donut: {
                            labels: {
                                show: true,
                                total: { show: true, label: 'Total Students', formatter: () => '345' }
                            }
                        }
                    }
                }
            }).render();

            // Widget 2: Enrollment by Grade Stacked Bar Chart
            new ApexCharts(document.querySelector("#chart-enrollment-grade"), {
                chart: { type: 'bar', height: 220, stacked: true, toolbar: { show: false } },
                series: [
                    { name: 'Boys', data: [22, 60, 32, 23, 25, 13, 6, 6, 23, 11, 5] },
                    { name: 'Girls', data: [10, 45, 18, 5, 12, 8, 7, 4, 5, 6, 4] }
                ],
                xaxis: { categories: ['ECD', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10'] },
                colors: ['#0284c7', '#ec4899'],
                legend: { position: 'top' },
                plotOptions: { bar: { columnWidth: '55%' } }
            }).render();

            // Widget 3: Gender Balance Radial Gauge with Needle Pointer
            (function() {
                var el = document.querySelector("#chart-gender-balance");
                if (!el) return;
                var pct = 65.22;
                var rightPct = (100 - pct).toFixed(2);
                var w = 320, h = 185, cx = 160, cy = 138, r = 96, strokeW = 38;
                var pRad = (pct / 100) * Math.PI;
                var splitAngle = Math.PI - pRad;
                var xSplit = cx + r * Math.cos(splitAngle);
                var ySplit = cy - r * Math.sin(splitAngle);
                var leftPath = "M " + (cx - r) + " " + cy + " A " + r + " " + r + " 0 0 1 " + xSplit + " " + ySplit;
                var rightPath = "M " + xSplit + " " + ySplit + " A " + r + " " + r + " 0 0 1 " + (cx + r) + " " + cy;
                var leftMid = Math.PI - (pRad / 2);
                var xLeftText = cx + r * Math.cos(leftMid);
                var yLeftText = cy - r * Math.sin(leftMid) + 5;
                var rightMid = splitAngle / 2;
                var xRightText = cx + r * Math.cos(rightMid);
                var yRightText = cy - r * Math.sin(rightMid) + 5;
                var needleL = 84;
                var tipX = cx + needleL * Math.cos(splitAngle);
                var tipY = cy - needleL * Math.sin(splitAngle);
                var perpX = Math.sin(splitAngle) * 5;
                var perpY = Math.cos(splitAngle) * 5;
                var b1X = cx - perpX, b1Y = cy - perpY;
                var b2X = cx + perpX, b2Y = cy + perpY;
                el.innerHTML = `
                <svg viewBox="0 0 ${w} ${h}" style="width:100%;max-width:320px;height:auto;display:block;margin:0 auto;" xmlns="http://www.w3.org/2000/svg">
                    <path d="M ${cx - r} ${cy} A ${r} ${r} 0 0 1 ${cx + r} ${cy}" fill="none" stroke="#f1f5f9" stroke-width="${strokeW}" />
                    <path d="${leftPath}" fill="none" stroke="#208cf0" stroke-width="${strokeW}" />
                    <path d="${rightPath}" fill="none" stroke="#e9488a" stroke-width="${strokeW}" />
                    <text x="${xLeftText}" y="${yLeftText}" fill="#ffffff" font-size="12" font-weight="900" text-anchor="middle" dominant-baseline="middle">65.22%</text>
                    <text x="${xRightText}" y="${yRightText}" fill="#ffffff" font-size="12" font-weight="900" text-anchor="middle" dominant-baseline="middle">34.78%</text>
                    <polygon points="${tipX},${tipY} ${b1X},${b1Y} ${b2X},${b2Y}" fill="#111827" />
                    <circle cx="${cx}" cy="${cy}" r="10" fill="#111827" />
                    <circle cx="${cx}" cy="${cy}" r="3.5" fill="#ffffff" />
                    <text x="${cx - r - 2}" y="${cy + 20}" font-size="12" font-weight="800" fill="#475569" text-anchor="start">0%</text>
                    <text x="${cx - r - 2}" y="${cy + 38}" font-size="15" font-weight="900" fill="#208cf0" text-anchor="start">Boys</text>
                    <text x="${cx + r + 2}" y="${cy + 20}" font-size="12" font-weight="800" fill="#475569" text-anchor="end">100%</text>
                    <text x="${cx + r + 2}" y="${cy + 38}" font-size="15" font-weight="900" fill="#e9488a" text-anchor="end">Girls</text>
                </svg>
                `;
            })();

            // Widget 5: Average Marks by Subject Horizontal Bar Chart
            new ApexCharts(document.querySelector("#chart-gpa"), {
                chart: { type: 'bar', height: 210, toolbar: { show: false } },
                series: [{ name: 'GPA', data: [2.68, 2.61, 3.08, 3.29, 3.56] }],
                xaxis: { categories: ['Nepali', 'English', 'Math', 'Science', 'Social'] },
                colors: ['#10b981'],
                plotOptions: { bar: { horizontal: true, borderRadius: 4 } }
            }).render();

            // Widget 6: Teachers Licensed vs Unlicensed
            new ApexCharts(document.querySelector("#chart-teacher-qual"), {
                chart: { type: 'bar', height: 210, toolbar: { show: false } },
                series: [{ name: 'Teachers', data: [1] }],
                xaxis: { categories: ["Licensed Teacher"] },
                colors: ['#10b981'],
                plotOptions: { bar: { columnWidth: '35%', borderRadius: 4 } }
            }).render();

        });
    </script>
</body>
</html>
