<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Academic Verification — {{ $student->full_name ?? 'Student' }} — {{ $school->name ?? config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f1f5f9;
        }
        .font-serif-header {
            font-family: 'Cinzel', Georgia, serif;
        }
        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }
        
        /* Subtle official security background pattern */
        .security-bg {
            background-color: #ffffff;
            background-image: radial-gradient(#cbd5e1 0.75px, transparent 0.75px), radial-gradient(#cbd5e1 0.75px, #ffffff 0.75px);
            background-size: 30px 30px;
            background-position: 0 0, 15px 15px;
        }

        /* Certificate border */
        .cert-border {
            border: 2px solid #1e3a8a;
            position: relative;
        }
        .cert-border::before {
            content: "";
            position: absolute;
            top: 4px;
            left: 4px;
            right: 4px;
            bottom: 4px;
            border: 1px solid #93c5fd;
            pointer-events: none;
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .cert-card {
                box-shadow: none !important;
                border: 1.5px solid #1e3a8a !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                border-radius: 0 !important;
            }
            .cert-border::before {
                border: 1px solid #cbd5e1 !important;
            }
        }
    </style>
</head>
<body class="min-h-screen text-slate-800 antialiased py-6 sm:py-10 px-3 sm:px-6 flex flex-col items-center justify-between">

    {{-- Top Action Toolbar (No Print) --}}
    <div class="max-w-4xl w-full mx-auto mb-4 flex flex-wrap items-center justify-between gap-3 no-print">
        <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-indigo-900 transition-colors bg-white px-3.5 py-2 rounded-lg border border-slate-200 shadow-sm">
            <i class="bi bi-arrow-left"></i>
            <span>Portal Home</span>
        </a>

        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="inline-flex items-center gap-2 text-xs font-bold text-slate-700 hover:text-slate-900 bg-white hover:bg-slate-50 px-3.5 py-2 rounded-lg border border-slate-300 shadow-sm transition-all cursor-pointer">
                <i class="bi bi-printer text-indigo-700 text-sm"></i>
                <span>Print Verification Slip</span>
            </button>

            @if(auth()->check() && Route::has('results.report-card'))
            <a href="{{ route('results.report-card', ['studentId' => $student->id, 'class_id' => $class->id ?? 0, 'section_id' => $section->id ?? 0, 'semester_id' => $semester->id ?? 0]) }}" class="inline-flex items-center gap-2 text-xs font-bold text-white bg-indigo-900 hover:bg-indigo-950 px-4 py-2 rounded-lg shadow-sm transition-all">
                <i class="bi bi-file-earmark-arrow-down"></i>
                <span>Download Official PDF</span>
            </a>
            @endif
        </div>
    </div>

    {{-- Official Institutional Verification Certificate Card --}}
    <div class="max-w-4xl w-full mx-auto bg-white rounded-2xl shadow-xl cert-border cert-card overflow-hidden">
        
        {{-- Top Official Tricolor Accent Strip --}}
        <div class="h-2 w-full bg-gradient-to-r from-blue-900 via-indigo-800 to-amber-600"></div>

        <div class="p-6 sm:p-10">

            {{-- Institutional Header --}}
            <div class="border-b-2 border-slate-200 pb-6 mb-6">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 text-center sm:text-left">
                    
                    {{-- School Logo / Crest --}}
                    <div class="flex items-center gap-4">
                        @if(!empty($school->logo))
                            <img src="{{ asset('storage/' . $school->logo) }}" alt="School Crest" class="h-20 w-20 object-contain rounded-lg border border-slate-200 p-1 bg-white flex-shrink-0 shadow-sm" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="hidden h-18 w-18 rounded-lg bg-blue-900 text-white font-serif-header text-2xl font-bold items-center justify-center border-2 border-amber-400 flex-shrink-0 shadow-md">
                                {{ strtoupper(substr($school->name ?? config('app.name'), 0, 2)) }}
                            </div>
                        @else
                            <div class="h-18 w-18 rounded-lg bg-blue-900 text-white font-serif-header text-2xl font-bold flex items-center justify-center border-2 border-amber-400 flex-shrink-0 shadow-md p-3">
                                {{ strtoupper(substr($school->name ?? config('app.name'), 0, 2)) }}
                            </div>
                        @endif

                        <div class="space-y-0.5">
                            <div class="text-[10px] font-bold text-amber-800 tracking-widest uppercase flex items-center gap-1.5 justify-center sm:justify-start">
                                <i class="bi bi-award-fill text-amber-600"></i>
                                <span>Ministry of Education &bull; CEHRD Recognized</span>
                            </div>
                            <h1 class="text-xl sm:text-2xl font-black text-slate-900 uppercase tracking-tight font-serif-header leading-tight">
                                {{ $school->name ?? config('app.name') }}
                            </h1>
                            <p class="text-xs text-slate-600 font-medium">
                                {{ $school->address ?? 'Nepal' }}
                                @if($school->phone) &bull; Tel: {{ $school->phone }} @endif
                                @if($school->email) &bull; Email: {{ $school->email }} @endif
                            </p>
                            <p class="text-[11px] text-slate-500 font-mono">
                                Institutional EMIS: <span class="font-bold text-slate-800">{{ $school->iemis_code ?? '27001-CEHRD' }}</span>
                            </p>
                        </div>
                    </div>

                    {{-- Verification Authentication Shield Badge --}}
                    <div class="flex-shrink-0 text-center sm:text-right border-t sm:border-t-0 border-slate-200 pt-3 sm:pt-0">
                        <div class="inline-flex flex-col items-center sm:items-end">
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 border border-emerald-300 text-emerald-800 rounded-full text-xs font-extrabold shadow-sm">
                                <i class="bi bi-shield-fill-check text-emerald-600"></i>
                                <span>AUTHENTIC &amp; VERIFIED</span>
                            </div>
                            <span class="text-[10px] text-slate-500 mt-1 font-mono">Verification Ref:</span>
                            <span class="text-xs font-mono font-bold text-slate-800">{{ substr($token, 0, 16) }}...</span>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Document Title Ribbon --}}
            <div class="text-center mb-6">
                <div class="inline-block bg-blue-900 text-white text-xs sm:text-sm font-bold tracking-wider uppercase px-5 py-1.5 rounded shadow-sm">
                    Official Academic Performance &amp; Grade Authentication Statement
                </div>
                <p class="text-[11px] text-slate-500 mt-1.5">Office of the Controller of Examinations &bull; Official Digital Ledger Verification</p>
            </div>

            {{-- Student & Examination Details Grid --}}
            <div class="bg-slate-50 rounded-xl border border-slate-200 p-4 sm:p-5 mb-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                    
                    <div class="space-y-1">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Student Name</span>
                        <p class="text-sm font-bold text-slate-900 uppercase">{{ $student->full_name }}</p>
                    </div>

                    <div class="space-y-1">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Roll / Symbol No.</span>
                        <p class="text-sm font-bold text-slate-900 font-mono">{{ $result['promotion']?->roll_no ?? $student->academic_info?->roll_number ?? '—' }}</p>
                    </div>

                    <div class="space-y-1">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Class &amp; Section</span>
                        <p class="text-sm font-bold text-slate-900">
                            {{ $class->class_name ?? '—' }}
                            <span class="text-slate-500 font-normal">({{ $section->section_name ?? 'Sec A' }})</span>
                        </p>
                    </div>

                    <div class="space-y-1">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Examination Term</span>
                        <p class="text-sm font-bold text-indigo-950">{{ $semester->semester_name ?? 'Terminal Examination' }}</p>
                    </div>

                    <div class="space-y-1 border-t border-slate-200 pt-2 sm:border-t-0 sm:pt-0">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Academic Session</span>
                        <p class="text-xs font-bold text-slate-800">{{ $session->session_name ?? 'Current Academic Session' }}</p>
                    </div>

                    <div class="space-y-1 border-t border-slate-200 pt-2 sm:border-t-0 sm:pt-0">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Evaluation Framework</span>
                        <p class="text-xs font-bold text-slate-800">CDC 2078 (8-Point Grading)</p>
                    </div>

                    <div class="space-y-1 border-t border-slate-200 pt-2 sm:border-t-0 sm:pt-0">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Verification Date (AD)</span>
                        <p class="text-xs font-bold text-slate-800">{{ now()->format('d M Y, H:i') }}</p>
                    </div>

                    <div class="space-y-1 border-t border-slate-200 pt-2 sm:border-t-0 sm:pt-0">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Date of Issue (BS)</span>
                        <p class="text-xs font-bold text-slate-800">
                            @if(function_exists('bsDate'))
                                {{ bsDate(now()->format('Y-m-d'), true, 'short') }}
                            @else
                                {{ now()->format('Y-m-d') }}
                            @endif
                        </p>
                    </div>

                </div>
            </div>

            {{-- Subject Performance Table --}}
            <div class="mb-6">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                        <i class="bi bi-table text-indigo-800"></i>
                        <span>Subject-Wise Performance &amp; Evaluation</span>
                    </h2>
                    <span class="text-[10px] text-slate-500 font-medium">Standard Credit Hours Evaluation</span>
                </div>

                <div class="overflow-x-auto rounded-lg border border-slate-300 shadow-sm">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-100 text-slate-700 font-bold border-b border-slate-300">
                                <th class="py-2.5 px-3 w-10 text-center">SN</th>
                                <th class="py-2.5 px-3">Subject / Course</th>
                                <th class="py-2.5 px-3 text-center">Marks Obtained</th>
                                <th class="py-2.5 px-3 text-center">Final Grade</th>
                                <th class="py-2.5 px-3 text-center">Grade Point</th>
                                <th class="py-2.5 px-3 text-center">Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($result['courses'] as $idx => $c)
                            <tr class="hover:bg-slate-50/70 transition-colors {{ $idx % 2 === 0 ? 'bg-white' : 'bg-slate-50/40' }}">
                                <td class="py-2.5 px-3 text-center text-slate-500 font-mono">{{ $idx + 1 }}</td>
                                <td class="py-2.5 px-3 font-bold text-slate-900">
                                    {{ $c['course']?->course_name ?? 'Course' }}
                                </td>
                                <td class="py-2.5 px-3 text-center font-mono font-semibold text-slate-800">
                                    {{ round($c['final_marks'], 1) }}
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    <span class="inline-block px-2 py-0.5 rounded font-extrabold text-xs {{ $c['passed'] ? 'bg-emerald-100 text-emerald-900 border border-emerald-200' : 'bg-rose-100 text-rose-900 border border-rose-200' }}">
                                        {{ $c['grade'] }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-center font-mono font-bold text-indigo-900">
                                    {{ number_format((float)$c['point'], 2) }}
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    @if($c['passed'])
                                    <span class="inline-flex items-center gap-1 text-emerald-700 text-[11px] font-bold">
                                        <i class="bi bi-check-circle-fill text-xs"></i>
                                        PASSED
                                    </span>
                                    @else
                                    <span class="inline-flex items-center gap-1 text-rose-700 text-[11px] font-bold">
                                        <i class="bi bi-exclamation-circle-fill text-xs"></i>
                                        NEEDS RETEST
                                    </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-slate-400 italic">No course marks registered in the official ledger for this term.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Cumulative Evaluation Summary Grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
                <div class="bg-blue-50/60 border border-blue-200 rounded-xl p-3 text-center">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-blue-800 block">Total Aggregate Marks</span>
                    <p class="text-lg font-black text-blue-950 font-mono mt-0.5">{{ round($result['totalMarks'], 1) }}</p>
                </div>

                <div class="bg-emerald-50/60 border border-emerald-200 rounded-xl p-3 text-center">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-800 block">Courses Passed</span>
                    <p class="text-lg font-black text-emerald-900 font-mono mt-0.5">{{ $result['passed'] }} / {{ count($result['courses']) }}</p>
                </div>

                <div class="bg-indigo-50/60 border border-indigo-200 rounded-xl p-3 text-center">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-800 block">Grade Point Average (GPA)</span>
                    <p class="text-xl font-black text-indigo-950 font-mono mt-0.5">{{ $result['gpa'] }}</p>
                </div>

                <div class="bg-amber-50/60 border border-amber-200 rounded-xl p-3 text-center">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-amber-800 block">Class Merit Standing</span>
                    <p class="text-lg font-black text-amber-950 mt-0.5">{{ $result['rank'] ? 'Rank #' . $result['rank'] : '—' }}</p>
                </div>
            </div>

            {{-- CDC Grading System Key Legend --}}
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 mb-6">
                <div class="text-[10px] font-bold text-slate-600 uppercase tracking-wider mb-2">Government of Nepal CDC 2078 Grading Scale Reference:</div>
                <div class="grid grid-cols-4 sm:grid-cols-8 gap-1.5 text-center text-[10px]">
                    <div class="bg-white border border-slate-200 rounded p-1">
                        <div class="font-extrabold text-slate-900">A+ (4.0)</div>
                        <div class="text-[9px] text-slate-500">90% - 100%</div>
                    </div>
                    <div class="bg-white border border-slate-200 rounded p-1">
                        <div class="font-extrabold text-slate-900">A (3.6)</div>
                        <div class="text-[9px] text-slate-500">80% - 89%</div>
                    </div>
                    <div class="bg-white border border-slate-200 rounded p-1">
                        <div class="font-extrabold text-slate-900">B+ (3.2)</div>
                        <div class="text-[9px] text-slate-500">70% - 79%</div>
                    </div>
                    <div class="bg-white border border-slate-200 rounded p-1">
                        <div class="font-extrabold text-slate-900">B (2.8)</div>
                        <div class="text-[9px] text-slate-500">60% - 69%</div>
                    </div>
                    <div class="bg-white border border-slate-200 rounded p-1">
                        <div class="font-extrabold text-slate-900">C+ (2.4)</div>
                        <div class="text-[9px] text-slate-500">50% - 59%</div>
                    </div>
                    <div class="bg-white border border-slate-200 rounded p-1">
                        <div class="font-extrabold text-slate-900">C (2.0)</div>
                        <div class="text-[9px] text-slate-500">40% - 49%</div>
                    </div>
                    <div class="bg-white border border-slate-200 rounded p-1">
                        <div class="font-extrabold text-slate-900">D (1.6)</div>
                        <div class="text-[9px] text-slate-500">35% - 39%</div>
                    </div>
                    <div class="bg-white border border-slate-200 rounded p-1">
                        <div class="font-extrabold text-rose-700">NG (0.0)</div>
                        <div class="text-[9px] text-rose-600">Below 35%</div>
                    </div>
                </div>
            </div>

            {{-- Institutional Authentication Signatures & Disclaimers --}}
            <div class="border-t border-slate-200 pt-6">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 text-center text-xs text-slate-600">
                    <div>
                        <div class="h-10 flex items-end justify-center">
                            <span class="text-xs font-serif-header italic text-slate-400">Electronic Record Validated</span>
                        </div>
                        <div class="border-t border-slate-400 mt-1 pt-1 font-bold text-slate-800">Prepared &amp; Verified By</div>
                        <div class="text-[10px] text-slate-400">Academic Records Section</div>
                    </div>

                    <div>
                        <div class="h-10 flex items-end justify-center">
                            <div class="inline-flex items-center gap-1 text-[11px] font-mono text-indigo-900 font-bold bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200">
                                <i class="bi bi-patch-check-fill text-indigo-600"></i>
                                <span>AUTHENTICATED</span>
                            </div>
                        </div>
                        <div class="border-t border-slate-400 mt-1 pt-1 font-bold text-slate-800">Controller of Examinations</div>
                        <div class="text-[10px] text-slate-400">Examination Directorate</div>
                    </div>

                    <div>
                        <div class="h-10 flex items-end justify-center">
                            <span class="text-xs font-serif-header italic text-slate-400">{{ $school->name ?? 'Head of Institution' }}</span>
                        </div>
                        <div class="border-t border-slate-400 mt-1 pt-1 font-bold text-slate-800">Principal / Headmaster</div>
                        <div class="text-[10px] text-slate-400">Institutional Seal</div>
                    </div>
                </div>
            </div>

            {{-- Digital Security Code & Legal Notice --}}
            <div class="mt-6 pt-4 border-t border-dashed border-slate-300 text-[10px] text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-2">
                <div class="flex items-center gap-1.5">
                    <i class="bi bi-shield-lock-fill text-slate-400"></i>
                    <span>Official Security Verification Token: <code class="font-mono font-bold text-slate-700">{{ $token }}</code></span>
                </div>
                <div class="text-slate-400 text-center sm:text-right">
                    Official Document &bull; Validated via {{ config('app.name', 'School Management System') }}
                </div>
            </div>

        </div>

        {{-- Bottom Institutional Footer --}}
        <div class="bg-slate-100 border-t border-slate-200 px-6 py-3 text-center text-[10px] text-slate-500">
            This verification record is an official electronic confirmation generated from the master examination records of <strong>{{ $school->name ?? config('app.name') }}</strong>. Any tampering or unauthorized alteration of this credential is strictly prohibited by law.
        </div>

    </div>

    {{-- System Footer (No Print) --}}
    <div class="mt-4 text-center text-xs text-slate-500 no-print">
        &copy; {{ date('Y') }} {{ $school->name ?? config('app.name') }}. All official academic records are authenticated by the institutional examination registry.
    </div>

</body>
</html>
