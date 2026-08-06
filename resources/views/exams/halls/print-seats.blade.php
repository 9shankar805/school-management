<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seating Plan — {{ $schedule->exam?->exam_name }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            font-size: 12px;
        }
        a { text-decoration: none; color: inherit; }

        /* ── Action bar (screen only) ── */
        .action-bar {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 10px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .btn-back {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 13px; font-weight: 600; color: #475569;
            padding: 6px 14px; border-radius: 8px;
            border: 1px solid #e2e8f0; background: #f8fafc;
            transition: .15s;
        }
        .btn-back:hover { background: #f1f5f9; color: #1e293b; }
        .btn-print {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 13px; font-weight: 700; color: #fff;
            padding: 8px 20px; border-radius: 10px; border: none; cursor: pointer;
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            box-shadow: 0 2px 8px rgba(99,102,241,.3);
            transition: .15s;
        }
        .btn-print:hover { background: linear-gradient(135deg,#4338ca,#4f46e5); }

        /* ── Page wrapper ── */
        .page {
            max-width: 1100px;
            margin: 28px auto;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,.07);
            overflow: hidden;
        }

        /* ── School header ── */
        .school-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);
            color: #fff;
            padding: 28px 36px 22px;
            text-align: center;
        }
        .school-header h1 {
            font-size: 22px; font-weight: 900; letter-spacing: .04em;
            text-transform: uppercase; color: #fff; margin-bottom: 6px;
        }
        .school-header .subtitle {
            font-size: 11px; font-weight: 700; letter-spacing: .12em;
            text-transform: uppercase; color: #a5b4fc; margin-bottom: 3px;
        }
        .school-header .tagline { font-size: 11px; color: rgba(255,255,255,.5); }

        /* ── Meta info strip ── */
        .meta-strip {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .meta-item {
            padding: 14px 20px;
            border-right: 1px solid #e2e8f0;
        }
        .meta-item:last-child { border-right: none; }
        .meta-label {
            font-size: 9px; font-weight: 800; letter-spacing: .1em;
            text-transform: uppercase; color: #94a3b8; margin-bottom: 4px;
        }
        .meta-value { font-size: 13px; font-weight: 700; color: #0f172a; }
        .meta-value.accent { color: #4f46e5; }

        /* ── Section headings ── */
        .section-heading {
            font-size: 11px; font-weight: 800; letter-spacing: .1em;
            text-transform: uppercase; color: #64748b;
            padding: 18px 28px 10px;
            display: flex; align-items: center; gap: 7px;
        }
        .section-heading i { font-size: 14px; color: #6366f1; }

        /* ── Visual bench grid ── */
        .bench-grid-wrap {
            padding: 0 28px 24px;
        }
        .columns-grid {
            display: grid;
            gap: 14px;
        }
        .column-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
        }
        .column-title {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            color: #fff;
            text-align: center;
            font-size: 10px; font-weight: 800; letter-spacing: .08em;
            text-transform: uppercase;
            padding: 6px 8px;
        }
        .benches-list { padding: 8px; display: flex; flex-direction: column; gap: 7px; }
        .bench-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
        }
        .bench-label {
            background: #f1f5f9;
            border-bottom: 1px solid #e2e8f0;
            font-size: 9px; font-weight: 700; color: #64748b;
            padding: 3px 8px; letter-spacing: .05em;
        }
        .bench-seats { display: flex; }
        .seat-slot {
            flex: 1;
            padding: 6px 4px;
            text-align: center;
            border-right: 1px dashed #e2e8f0;
            min-height: 46px;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
        }
        .seat-slot:last-child { border-right: none; }
        .seat-slot.filled { background: #eef2ff; }
        .seat-slot.empty { background: #fafafa; }
        .seat-pos-label { font-size: 8px; font-weight: 700; color: #94a3b8; margin-bottom: 2px; }
        .seat-roll { font-family: monospace; font-size: 10px; font-weight: 800; color: #4f46e5; }
        .seat-name { font-size: 10px; font-weight: 600; color: #1e293b; }
        .seat-empty-text { font-size: 9px; color: #cbd5e1; }

        /* ── Roster table ── */
        .roster-wrap { padding: 0 28px 28px; }
        .roster-table {
            width: 100%; border-collapse: collapse;
            border: 1px solid #e2e8f0; border-radius: 10px;
            overflow: hidden; font-size: 12px;
        }
        .roster-table thead tr {
            background: linear-gradient(135deg, #0f172a, #1e1b4b);
            color: #fff;
        }
        .roster-table th {
            padding: 10px 14px; font-size: 10px; font-weight: 700;
            letter-spacing: .06em; text-transform: uppercase; text-align: left;
        }
        .roster-table th.center { text-align: center; }
        .roster-table tbody tr { border-bottom: 1px solid #f1f5f9; transition: background .1s; }
        .roster-table tbody tr:nth-child(even) { background: #fafafa; }
        .roster-table tbody tr:hover { background: #f0f9ff; }
        .roster-table td { padding: 9px 14px; color: #334155; vertical-align: middle; }
        .bench-code {
            display: inline-block;
            font-family: monospace; font-size: 11px; font-weight: 800;
            color: #4f46e5; background: #eef2ff; border: 1px solid #c7d2fe;
            padding: 2px 7px; border-radius: 5px;
        }
        .roll-no { font-family: monospace; font-weight: 700; color: #1e293b; }
        .student-name { font-weight: 600; color: #0f172a; }
        .sign-cell { width: 130px; border-left: 1px solid #e2e8f0 !important; }

        /* ── Footer ── */
        .print-footer {
            border-top: 1px solid #e2e8f0;
            padding: 18px 28px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .footer-left p { color: #64748b; font-size: 11px; line-height: 1.7; }
        .footer-left strong { color: #1e293b; }
        .sig-line { border-bottom: 1.5px solid #94a3b8; width: 200px; margin-bottom: 5px; }
        .sig-label { font-size: 11px; font-weight: 700; color: #475569; text-align: right; }

        /* ── Stats bar ── */
        .stats-bar {
            display: flex; gap: 0;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
        }
        .stat-item {
            flex: 1; padding: 12px 20px; text-align: center;
            border-right: 1px solid #e2e8f0;
        }
        .stat-item:last-child { border-right: none; }
        .stat-num { font-size: 20px; font-weight: 900; color: #4f46e5; }
        .stat-lbl { font-size: 10px; color: #94a3b8; font-weight: 600; margin-top: 1px; }

        /* ── Print media ── */
        @media print {
            body { background: #fff; }
            .action-bar { display: none !important; }
            .page {
                max-width: 100%;
                margin: 0;
                border: none;
                border-radius: 0;
                box-shadow: none;
            }
            .roster-table tbody tr:hover { background: inherit; }
        }
    </style>
</head>
<body>

{{-- ── Action Bar (screen only) ── --}}
<div class="action-bar">
    <a href="{{ route('exam.hall.seats', $schedule->id) }}" class="btn-back">
        <i class="bi bi-arrow-left"></i> Back to Seating Manager
    </a>
    <button onclick="window.print()" class="btn-print">
        <i class="bi bi-printer-fill"></i> Print Seating Chart & Roster
    </button>
</div>

{{-- ── Printable Page ── --}}
<div class="page">

    {{-- School Header --}}
    <div class="school-header">
        @php $school = auth()->user()?->school; @endphp
        <h1>{{ $school?->name ?? 'Genesis Global School' }}</h1>
        <p class="subtitle">Official Examination Bench Seating Arrangement</p>
        <p class="tagline">{{ $school?->address ?? 'Promoting Excellence, Nurturing Potential' }}</p>
    </div>

    {{-- Meta Info Strip --}}
    <div class="meta-strip">
        <div class="meta-item">
            <div class="meta-label">Examination</div>
            <div class="meta-value">{{ $schedule->exam?->exam_name ?? '—' }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">Subject / Course</div>
            <div class="meta-value">{{ $schedule->course?->course_name ?? 'All Subjects' }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">Date &amp; Time</div>
            <div class="meta-value">
                {{ \Carbon\Carbon::parse($schedule->exam_date)->format('d M Y') }}
                &bull; {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}
            </div>
        </div>
        <div class="meta-item">
            <div class="meta-label">Hall / Room</div>
            <div class="meta-value accent">
                {{ $schedule->hall?->hall_name ?? 'Main Hall' }}
                @if($schedule->hall) ({{ $schedule->hall->students_per_bench ?? 2 }} / bench) @endif
            </div>
        </div>
    </div>

    {{-- Stats Bar --}}
    @php
        $numCols         = $schedule->hall?->columns          ?? 4;
        $benchesPerCol   = $schedule->hall?->benches_per_column ?? 5;
        $studPerBench    = $schedule->hall?->students_per_bench ?? 2;
        $totalCapacity   = $numCols * $benchesPerCol * $studPerBench;

        $allocatedByBench = [];
        foreach ($allocations as $a) {
            $cNo = $a->column_no      ?? 1;
            $bNo = $a->bench_no       ?? 1;
            $pNo = $a->bench_position ?? 1;
            $allocatedByBench["{$cNo}_{$bNo}_{$pNo}"] = $a;
        }

        $posLabels = [1 => 'Left', 2 => ($studPerBench == 3 ? 'Center' : 'Right'), 3 => 'Right'];
        if ($studPerBench == 1) { $posLabels = [1 => 'Single']; }
    @endphp
    <div class="stats-bar">
        <div class="stat-item">
            <div class="stat-num">{{ $allocations->count() }}</div>
            <div class="stat-lbl">Students Seated</div>
        </div>
        <div class="stat-item">
            <div class="stat-num">{{ $numCols }}</div>
            <div class="stat-lbl">Columns</div>
        </div>
        <div class="stat-item">
            <div class="stat-num">{{ $benchesPerCol }}</div>
            <div class="stat-lbl">Benches / Column</div>
        </div>
        <div class="stat-item">
            <div class="stat-num">{{ $studPerBench }}</div>
            <div class="stat-lbl">Students / Bench</div>
        </div>
        <div class="stat-item">
            <div class="stat-num">{{ $totalCapacity - $allocations->count() }}</div>
            <div class="stat-lbl">Empty Seats</div>
        </div>
    </div>

    {{-- Visual Bench Grid --}}
    <div class="section-heading">
        <i class="bi bi-grid-3x3-gap-fill"></i> Room Bench Visual Layout
    </div>

    <div class="bench-grid-wrap">
        @if($allocations->isEmpty())
        <div style="text-align:center;padding:40px;color:#94a3b8;background:#f8fafc;border-radius:12px;border:2px dashed #e2e8f0;">
            <i class="bi bi-grid-3x3" style="font-size:32px;display:block;margin-bottom:10px;opacity:.4;"></i>
            No seats have been allocated yet.
        </div>
        @else
        <div class="columns-grid" style="grid-template-columns: repeat({{ min($numCols, 4) }}, 1fr);">
            @for($c = 1; $c <= $numCols; $c++)
            <div class="column-card">
                <div class="column-title">Column {{ $c }}</div>
                <div class="benches-list">
                    @for($b = 1; $b <= $benchesPerCol; $b++)
                    <div class="bench-card">
                        <div class="bench-label">Bench {{ $b }}</div>
                        <div class="bench-seats">
                            @for($p = 1; $p <= $studPerBench; $p++)
                            @php
                                $alloc = $allocatedByBench["{$c}_{$b}_{$p}"] ?? null;
                                $label = $posLabels[$p] ?? $p;
                            @endphp
                            <div class="seat-slot {{ $alloc ? 'filled' : 'empty' }}">
                                <div class="seat-pos-label">{{ $label }}</div>
                                @if($alloc)
                                    <div class="seat-roll">{{ $alloc->student?->promotions?->first()?->id_card_number ?? '—' }}</div>
                                    <div class="seat-name">{{ $alloc->student?->first_name ?? '—' }}</div>
                                @else
                                    <div class="seat-empty-text">[ empty ]</div>
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

    {{-- Invigilator Roster Table --}}
    <div class="section-heading" style="padding-top:6px;">
        <i class="bi bi-table"></i> Invigilator Door &amp; Desk Roster
    </div>

    <div class="roster-wrap">
        <table class="roster-table">
            <thead>
                <tr>
                    <th style="width:100px;">Bench Code</th>
                    <th style="width:120px;">Roll No.</th>
                    <th>Student Name</th>
                    <th style="width:130px;">Class</th>
                    <th class="center sign-cell" style="width:130px;">Signature</th>
                </tr>
            </thead>
            <tbody>
                @forelse($allocations as $a)
                <tr>
                    <td><span class="bench-code">{{ $a->seat_number }}</span></td>
                    <td><span class="roll-no">{{ $a->student?->promotions?->first()?->id_card_number ?? '—' }}</span></td>
                    <td><span class="student-name">{{ $a->student?->full_name ?? '—' }}</span></td>
                    <td style="color:#64748b;">{{ $a->student?->promotions?->first()?->schoolClass?->class_name ?? '—' }}</td>
                    <td class="sign-cell"></td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;color:#94a3b8;padding:20px;">No seats recorded.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Footer ── --}}
    <div class="print-footer">
        <div class="footer-left">
            <p>Generated by: <strong>Academic Coordinator</strong></p>
            <p>Printed on: {{ now()->format('d M Y, h:i A') }}</p>
            <p style="margin-top:4px;color:#94a3b8;font-size:10px;">
                {{ $schedule->hall?->hall_name }} &bull;
                Total seats: {{ $allocations->count() }} /
                Capacity: {{ $totalCapacity }}
            </p>
        </div>
        <div>
            <div class="sig-line"></div>
            <div class="sig-label">Invigilator / Supervisor Signature</div>
        </div>
    </div>

</div>{{-- /page --}}

</body>
</html>
