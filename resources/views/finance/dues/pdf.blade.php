<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Class-Wise Dues & Fee Defaulters Report - {{ $class?->class_name ?? 'All Classes' }}</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: DejaVu Sans, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 8.5pt; color: #0f172a; background: #fff; line-height: 1.4; }
.page { width: 210mm; margin: 0 auto; padding: 12mm 14mm; }

.header { border-bottom: 2pt solid #0d9488; padding-bottom: 4mm; margin-bottom: 5mm; display: table; width: 100%; }
.header-left { display: table-cell; vertical-align: top; width: 65%; }
.header-right { display: table-cell; vertical-align: top; width: 35%; text-align: right; }
.school-name { font-size: 14pt; font-weight: bold; color: #0f766e; text-transform: uppercase; letter-spacing: 0.5pt; }
.school-sub { font-size: 8pt; color: #64748b; margin-top: 1mm; }
.report-title { font-size: 11pt; font-weight: bold; color: #1e293b; text-transform: uppercase; letter-spacing: 1pt; }
.report-sub { font-size: 8pt; color: #0f766e; font-weight: bold; margin-top: 1mm; }

.meta-box { background: #f8fafc; border: 0.5pt solid #cbd5e1; border-radius: 4pt; padding: 3mm 4mm; margin-bottom: 5mm; display: table; width: 100%; }
.meta-col { display: table-cell; width: 33.33%; font-size: 8pt; }
.meta-label { color: #64748b; text-transform: uppercase; font-size: 7pt; }
.meta-value { font-weight: bold; color: #0f172a; margin-top: 0.5mm; }

.dues-table { width: 100%; border-collapse: collapse; margin-bottom: 5mm; font-size: 8pt; }
.dues-table th { background: #f1f5f9; color: #334155; padding: 2.5mm 3mm; font-weight: bold; text-align: left; border: 0.5pt solid #cbd5e1; text-transform: uppercase; font-size: 7.5pt; }
.dues-table td { padding: 2.5mm 3mm; border: 0.5pt solid #e2e8f0; vertical-align: middle; }
.dues-table tr:nth-child(even) td { background: #fafafa; }

.badge-locked { display: inline-block; padding: 1pt 5pt; border-radius: 3pt; font-size: 7pt; font-weight: bold; background: #fee2e2; color: #991b1b; }
.badge-active { display: inline-block; padding: 1pt 5pt; border-radius: 3pt; font-size: 7pt; font-weight: bold; background: #fef3c7; color: #92400e; }

.total-row td { background: #e6fffa !important; font-weight: bold; font-size: 9pt; color: #0f766e; border-top: 1.5pt solid #0d9488; }

.footer { border-top: 0.5pt solid #cbd5e1; padding-top: 6mm; margin-top: 6mm; display: table; width: 100%; font-size: 7.5pt; color: #64748b; }
.footer-col { display: table-cell; width: 33.33%; text-align: center; vertical-align: bottom; }
.sig-line { border-top: 0.5pt solid #475569; width: 45mm; margin: 0 auto 1.5mm auto; }
</style>
</head>
<body>
<div class="page">

    {{-- Official Header --}}
    <div class="header">
        <div class="header-left">
            <div class="school-name">{{ $school?->name ?? config('app.name', 'School Management') }}</div>
            <div class="school-sub">Finance Department · Class-Wise Dues & Defaulters Recovery Statement</div>
        </div>
        <div class="header-right">
            <div class="report-title">Dues Statement</div>
            <div class="report-sub">{{ $class ? $class->class_name : 'All Classes' }} {{ $section ? '(Sec '.$section->section_name.')' : '' }}</div>
            <div style="font-size:7.5pt; color:#64748b; margin-top:1mm;">Generated: {{ now()->format('d M, Y H:i') }}</div>
        </div>
    </div>

    {{-- Meta Overview --}}
    <div class="meta-box">
        <div class="meta-col">
            <div class="meta-label">Target Class / Section</div>
            <div class="meta-value">{{ $class ? $class->class_name : 'Institutional Wide' }} {{ $section ? '— Sec ' . $section->section_name : '' }}</div>
        </div>
        <div class="meta-col" style="text-align:center;">
            <div class="meta-label">Total Defaulters Listed</div>
            <div class="meta-value">{{ count($rows) }} Students</div>
        </div>
        <div class="meta-col" style="text-align:right;">
            <div class="meta-label">Total Overdue Dues</div>
            <div class="meta-value" style="color:#b91c1c; font-size:9.5pt;">NPR {{ number_format($totalDues, 2) }}</div>
        </div>
    </div>

    {{-- Dues Table --}}
    <table class="dues-table">
        <thead>
            <tr>
                <th style="width:5%; text-align:center;">SN</th>
                <th style="width:12%;">Adm. No</th>
                <th style="width:8%; text-align:center;">Roll</th>
                <th style="width:25%;">Student Name</th>
                <th style="width:15%;">Class & Sec</th>
                <th style="width:15%;">Guardian Phone</th>
                <th style="width:10%; text-align:center;">Admit Card</th>
                <th style="width:10%; text-align:right;">Outstanding (NPR)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $idx => $r)
            <tr>
                <td style="text-align:center;">{{ $idx + 1 }}</td>
                <td>{{ $r['admission_no'] }}</td>
                <td style="text-align:center;">{{ $r['roll_no'] }}</td>
                <td style="font-weight:600;">{{ $r['student_name'] }}</td>
                <td>{{ $r['class_section'] }}</td>
                <td>{{ $r['guardian_phone'] }}</td>
                <td style="text-align:center;">
                    @if($r['is_admit_card_locked'])
                    <span class="badge-locked">BARRED</span>
                    @else
                    <span class="badge-active">DUE LOCK</span>
                    @endif
                </td>
                <td style="text-align:right; font-weight:bold; color:#b91c1c;">
                    {{ number_format($r['due_balance'], 2) }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align:center; padding:5mm; color:#64748b;">No outstanding dues recorded for this class.</td>
            </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="7" style="text-align:right;">TOTAL OUTSTANDING RECOVERY BALANCE:</td>
                <td style="text-align:right; font-weight:800; color:#0f766e;">NPR {{ number_format($totalDues, 2) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Official Sign-off --}}
    <div class="footer">
        <div class="footer-col">
            <div class="sig-line"></div>
            <div>Accountant / Cashier</div>
            <div style="font-size:7pt; color:#94a3b8;">Finance Section</div>
        </div>
        <div class="footer-col">
            <div class="sig-line"></div>
            <div>Class Teacher</div>
            <div style="font-size:7pt; color:#94a3b8;">Academic Verification</div>
        </div>
        <div class="footer-col">
            <div class="sig-line"></div>
            <div>Principal / Director</div>
            <div style="font-size:7pt; color:#94a3b8;">Institutional Clearance</div>
        </div>
    </div>

</div>
</body>
</html>
