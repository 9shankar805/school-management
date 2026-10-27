<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Examination Admit Card</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1e293b; background: #fff; }
    .page { padding: 24px; page-break-after: always; }
    .page:last-child { page-break-after: auto; }
    .card-border { border: 2px solid #1e3a8a; border-radius: 8px; padding: 18px; position: relative; }
    
    /* Header */
    .header { text-align: center; border-bottom: 2px solid #1e3a8a; padding-bottom: 10px; margin-bottom: 12px; }
    .header h1 { font-size: 16px; color: #1e3a8a; text-transform: uppercase; font-weight: bold; margin-bottom: 2px; }
    .header .subtitle { font-size: 9px; color: #475569; margin-bottom: 2px; }
    .header .iemis { font-size: 8px; color: #64748b; font-style: italic; }
    
    /* Title badge */
    .title-badge { text-align: center; margin: 8px 0 12px; }
    .title-badge span { background: #1e3a8a; color: #fff; font-size: 11px; font-weight: bold; letter-spacing: 1.5px; padding: 4px 16px; border-radius: 4px; text-transform: uppercase; }
    
    /* Student info */
    .info-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
    .info-table td { padding: 4px 6px; font-size: 9.5px; vertical-align: top; }
    .info-label { width: 18%; color: #64748b; font-weight: 600; }
    .info-value { width: 32%; color: #0f172a; font-weight: bold; }
    
    /* Stamp */
    .stamp-cleared { border: 2px solid #059669; color: #059669; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 9px; display: inline-block; text-transform: uppercase; }
    .stamp-pending { border: 2px solid #dc2626; color: #dc2626; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 9px; display: inline-block; text-transform: uppercase; }

    /* Timetable Table */
    .table-schedule { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
    .table-schedule th { background: #f1f5f9; border: 1px solid #cbd5e1; padding: 5px 6px; font-size: 8.5px; font-weight: bold; text-align: left; color: #334155; }
    .table-schedule td { border: 1px solid #cbd5e1; padding: 5px 6px; font-size: 8.5px; }
    .table-schedule tr:nth-child(even) { background: #f8fafc; }
    
    /* Instructions */
    .instructions { background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 4px; padding: 8px; margin-bottom: 16px; }
    .instructions h4 { font-size: 8.5px; color: #334155; margin-bottom: 4px; text-transform: uppercase; }
    .instructions ol { margin-left: 14px; font-size: 8px; color: #64748b; }
    .instructions li { margin-bottom: 2px; }
    
    /* Signatures */
    .sig-row { width: 100%; border-collapse: collapse; margin-top: 14px; }
    .sig-row td { width: 33.33%; text-align: center; vertical-align: bottom; font-size: 8.5px; color: #475569; padding-top: 24px; }
    .sig-line { border-top: 1px solid #64748b; width: 75%; margin: 0 auto 4px; }
</style>
</head>
<body>

@foreach($cards as $c)
<div class="page">
    <div class="card-border">
        {{-- Header --}}
        <div class="header">
            <h1>{{ $c['school']?->name ?? config('app.name') }}</h1>
            <p class="subtitle">{{ $c['school']?->address ?? 'Kathmandu, Nepal' }} &bull; Ph: {{ $c['school']?->phone ?? '—' }}</p>
            @if($c['school']?->iemis_code)
            <p class="iemis">EMIS Code: {{ $c['school']->iemis_code }}</p>
            @endif
        </div>

        {{-- Badge --}}
        <div class="title-badge">
            <span>Examination Admit Card (प्रवेश-पत्र)</span>
        </div>

        {{-- Student Details & Clearance Stamp --}}
        <table class="info-table">
            <tr>
                <td class="info-label">Student Name:</td>
                <td class="info-value">{{ $c['student']->full_name }}</td>
                <td class="info-label">Examination:</td>
                <td class="info-value">{{ $c['exam']->exam_name }}</td>
            </tr>
            <tr>
                <td class="info-label">Class & Section:</td>
                <td class="info-value">{{ $c['class']?->class_name ?? '—' }} &bull; Sec {{ $c['section']?->section_name ?? 'A' }}</td>
                <td class="info-label">Academic Session:</td>
                <td class="info-value">{{ $c['exam']->session?->session_name ?? '2026-2027' }}</td>
            </tr>
            <tr>
                <td class="info-label">Roll Number:</td>
                <td class="info-value">{{ $c['promotion']?->roll_no ?? $c['student']->academic_info?->roll_number ?? '—' }}</td>
                <td class="info-label">Fee Clearance:</td>
                <td>
                    @if($c['feeStatus'] === 'CLEARED')
                    <span class="stamp-cleared">&#10003; FEE CLEARED</span>
                    @else
                    <span class="stamp-pending">&#9888; DUES: Rs. {{ number_format($c['dueBalance'], 2) }}</span>
                    @endif
                </td>
            </tr>
        </table>

        {{-- Examination Schedule & Seating Table --}}
        <table class="table-schedule">
            <thead>
                <tr>
                    <th style="width: 28%;">Course / Subject</th>
                    <th style="width: 18%;">Exam Date</th>
                    <th style="width: 16%;">Time</th>
                    <th style="width: 22%;">Hall / Center</th>
                    <th style="width: 16%;">Seat No</th>
                </tr>
            </thead>
            <tbody>
                @forelse($c['schedules'] as $sch)
                @php
                    $seat = $c['allocations'][$sch->id] ?? null;
                @endphp
                <tr>
                    <td><b>{{ $c['exam']->course?->course_name ?? 'Course Paper' }}</b></td>
                    <td>{{ $sch->exam_date?->format('d M Y (D)') }}</td>
                    <td>{{ Carbon\Carbon::parse($sch->start_time)->format('h:i A') }} - {{ Carbon\Carbon::parse($sch->end_time)->format('h:i A') }}</td>
                    <td>{{ $sch->hall?->hall_name ?? 'Main Exam Hall' }}</td>
                    <td style="font-weight: bold; color: #1e3a8a;">{{ $seat ? $seat->seat_number : 'Assigned' }}</td>
                </tr>
                @empty
                @foreach($c['courses'] as $crs)
                <tr>
                    <td><b>{{ $crs->course_name }}</b></td>
                    <td>As per Class Routine</td>
                    <td>09:00 AM - 12:00 PM</td>
                    <td>Main Campus Center</td>
                    <td style="font-weight: bold; color: #1e3a8a;">Hall Desk</td>
                </tr>
                @endforeach
                @endforelse
            </tbody>
        </table>

        {{-- Candidate Instructions --}}
        <div class="instructions">
            <h4>Instructions to the Examinee:</h4>
            <ol>
                <li>Candidate must present this Admit Card at the entrance of the examination hall for every paper.</li>
                <li>Examinees must be present at least 15 minutes before the scheduled commencement of the exam.</li>
                <li>Possession of mobile phones, smartwatches, or unauthorized printed materials inside the hall is strictly prohibited.</li>
                <li>Candidate must maintain strict discipline and comply with the invigilator's instructions at all times.</li>
            </ol>
        </div>

        {{-- Signatures --}}
        <table class="sig-row">
            <tr>
                <td>
                    <div class="sig-line"></div>
                    Candidate's Signature
                </td>
                <td>
                    <div class="sig-line"></div>
                    Class Teacher / Coordinator
                </td>
                <td>
                    <div class="sig-line"></div>
                    <b>Controller of Examinations</b>
                </td>
            </tr>
        </table>
    </div>
</div>
@endforeach

</body>
</html>
