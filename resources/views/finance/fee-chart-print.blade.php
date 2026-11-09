<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Chart – {{ $selectedSession->session_name ?? '2083' }} – {{ $school->name ?? 'School' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800;900&family=Inter:wght@400;500;600;700;800&family=Tiro+Devanagari+Nepali:ital@0;1&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc;
            color: #0f172a;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .school-title {
            font-family: 'Cinzel', serif, 'Times New Roman', Georgia;
            letter-spacing: 0.05em;
        }

        .chart-table {
            border-collapse: collapse;
            width: 100%;
        }

        .chart-table th,
        .chart-table td {
            border: 1.5px solid #000;
            padding: 7px 5px;
            font-size: 13px;
        }

        .chart-table th {
            font-weight: 800;
            text-transform: uppercase;
            background-color: #f8fafc;
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            @page {
                size: A4 landscape;
                margin: 8mm 10mm;
            }
            .print-page {
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                padding: 4mm !important;
            }
            .chart-table th,
            .chart-table td {
                border: 1.5px solid #000 !important;
                font-size: 12px !important;
                padding: 6px 4px !important;
            }
        }
    </style>
</head>
<body class="p-4 md:p-8">

    {{-- Top Action Toolbar (Hidden during print) --}}
    <div class="no-print max-w-6xl mx-auto mb-6 bg-white border border-slate-200 rounded-2xl p-4 shadow-sm flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('finance.structures', ['session_id' => $selectedSession?->id]) }}" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                <i class="bi bi-arrow-left"></i> Back to Matrix
            </a>
            <div class="text-xs text-slate-500">
                Session: <strong class="text-slate-800">{{ $selectedSession->session_name ?? 'Current' }}</strong>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <form method="GET" action="{{ route('finance.structures.print-chart') }}" class="inline-flex items-center gap-2">
                <label class="text-xs font-medium text-slate-600">Session:</label>
                <select name="session_id" onchange="this.form.submit()" class="text-xs border border-slate-300 rounded-lg px-2.5 py-1.5 bg-white font-semibold">
                    @foreach(\App\Models\SchoolSession::orderByDesc('id')->get() as $s)
                        <option value="{{ $s->id }}" @selected(($selectedSession->id ?? 0) == $s->id)>{{ $s->session_name }}</option>
                    @endforeach
                </select>
            </form>

            <button onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                <i class="bi bi-printer-fill text-sm"></i> Print Fee Chart (A4 Landscape)
            </button>
        </div>
    </div>

    {{-- Printable Fee Chart Sheet --}}
    <div class="print-page max-w-6xl mx-auto bg-white border border-slate-300 rounded-2xl p-8 shadow-md">

        {{-- School Header --}}
        <div class="text-center relative pb-3 border-b-2 border-slate-900 mb-4">
            
            {{-- Logo --}}
            <div class="flex justify-center mb-1">
                @if($school && $school->logo_url)
                    <img src="{{ $school->logo_url }}" class="h-16 w-16 object-contain" alt="Logo">
                @else
                    <div class="w-14 h-14 rounded-full border-2 border-slate-900 flex items-center justify-center font-bold text-lg text-slate-800 uppercase">
                        {{ strtoupper(substr($school->name ?? 'HL', 0, 2)) }}
                    </div>
                @endif
            </div>

            {{-- School Name --}}
            <h1 class="school-title text-2xl md:text-3xl font-black text-slate-950 uppercase tracking-wide leading-tight">
                {{ $school->name ?? 'HAPPY LAND ENGLISH BOARDING SCHOOL' }}
            </h1>

            {{-- Address --}}
            <p class="text-sm md:text-base font-extrabold text-slate-800 tracking-wider uppercase mt-0.5">
                {{ $school->address ?? 'SIRAHA - 1' }}
            </p>

            {{-- Main Chart Title --}}
            <div class="mt-2 inline-block">
                <span class="text-base md:text-lg font-black uppercase text-slate-950 border-b-2 border-slate-900 pb-0.5 tracking-wider">
                    FEE CHART – {{ $selectedSession->session_name ?? '2083' }}
                </span>
            </div>

            {{-- Nepali Date (Top Right) --}}
            <div class="absolute right-0 bottom-2 text-right">
                <span class="text-xs md:text-sm font-bold text-slate-900">
                    Date – {{ $nepaliDate }}
                </span>
            </div>
        </div>

        {{-- The 2D Fee Table --}}
        <div class="overflow-x-auto">
            <table class="chart-table text-slate-900">
                <thead>
                    <tr class="text-center font-black">
                        <th class="text-left px-3 py-2 text-xs md:text-sm uppercase tracking-wider bg-slate-100" style="min-width: 150px;">
                            CLASS
                        </th>
                        @foreach($classes as $c)
                            <th class="px-2 py-2 text-xs md:text-sm tracking-wider uppercase font-black">
                                {{ strtoupper($c->class_name) }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $cat)
                    <tr>
                        <td class="font-extrabold uppercase text-xs md:text-sm px-3 py-2 bg-slate-50">
                            {{ $cat->name }}
                        </td>
                        @foreach($classes as $c)
                            @php
                                $val = $feeMatrix[$cat->id][$c->id] ?? null;
                            @endphp
                            <td class="text-center font-bold text-xs md:text-sm">
                                @if($val !== null && $val > 0)
                                    {{ rtrim(rtrim(number_format($val, 2), '0'), '.') }}
                                @else
                                    <span class="text-slate-500 font-semibold">- -</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ count($classes) + 1 }}" class="py-8 text-center text-slate-500">
                            No fee categories configured yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Bottom Footer with Principal Stamp & Signature Line --}}
        <div class="mt-8 flex items-end justify-between pt-4">
            <div class="text-[11px] text-slate-600 max-w-md">
                <p class="font-semibold text-slate-800">नोट / Terms:</p>
                <p>१. मासिक शुल्क प्रत्येक नेपाली महिनाको १० गतेभित्र बुझाइसक्नुपर्नेछ।</p>
                <p>२. वार्षिक शुल्क र भर्ना शुल्क शैक्षिक सत्रको प्रारम्भमा मात्र लागू हुनेछ।</p>
            </div>

            <div class="text-center pr-4">
                <div class="w-36 h-12 flex items-center justify-center mx-auto mb-1">
                    {{-- Stamp/Signature area --}}
                    <div class="text-[10px] text-red-500/80 font-bold border border-dashed border-red-300 px-3 py-1 rounded rotate-[-3deg]">
                        OFFICIAL SEAL
                    </div>
                </div>
                <div class="border-t-2 border-slate-900 w-44 mx-auto pt-1">
                    <span class="text-sm font-black uppercase text-slate-900 tracking-wider">
                        Principal
                    </span>
                </div>
            </div>
        </div>

    </div>

</body>
</html>

