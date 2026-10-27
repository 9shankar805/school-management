<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>शैक्षिक क्यालेन्डर (Annual Academic Calendar) — {{ config('app.name', 'School') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Mukta:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        body {
            font-family: 'Mukta', 'Inter', system-ui, -apple-system, sans-serif;
            background: #f8fafc;
            color: #0f172a;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: #ffffff;
            padding: 35px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        .header h1 {
            font-size: 24px;
            font-weight: 800;
            margin: 0;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: -0.5px;
        }
        .header p {
            margin: 4px 0 0;
            color: #64748b;
            font-size: 13px;
        }
        .badge-bar {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-top: 12px;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }
        .badge-holiday { background: #fee2e2; color: #991b1b; }
        .badge-exam { background: #f3e8ff; color: #6b21a8; }
        .badge-total { background: #e0e7ff; color: #3730a3; }
        
        .month-block {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        .month-title {
            font-size: 16px;
            font-weight: 700;
            color: #4f46e5;
            border-left: 4px solid #4f46e5;
            padding-left: 10px;
            margin-bottom: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        th, td {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            text-align: left;
        }
        th {
            background: #f8fafc;
            color: #475569;
            font-weight: 700;
        }
        tr.holiday-row {
            background: #fff5f5;
        }
        tr.exam-row {
            background: #faf5ff;
        }
        .type-tag {
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .no-print-bar {
            max-width: 1000px;
            margin: 0 auto 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            background: #4f46e5;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }
        .btn:hover { background: #4338ca; }
        .btn-outline {
            background: transparent;
            color: #475569;
            border: 1px solid #cbd5e1;
        }
        .btn-outline:hover { background: #f1f5f9; }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .no-print-bar {
                display: none !important;
            }
            .container {
                box-shadow: none;
                padding: 0;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <a href="{{ route('events.show') }}" class="btn btn-outline">
            <i class="bi bi-arrow-left"></i> क्यालेन्डरमा फर्कनुहोस् (Back)
        </a>
        <button onclick="window.print()" class="btn">
            <i class="bi bi-printer"></i> प्रिन्ट / PDF सेभ गर्नुहोस् (Print Calendar)
        </button>
    </div>

    <div class="container">
        <div class="header">
            <h1>वार्षिक शैक्षिक क्यालेन्डर तथा कार्यक्रम तालिका</h1>
            <p>Annual Academic Calendar, Examination Schedules &amp; Public Holidays</p>
            <div class="badge-bar">
                <span class="badge badge-total"><i class="bi bi-calendar-event"></i> जम्मा कार्यक्रम: {{ $events->count() }}</span>
                <span class="badge badge-holiday"><i class="bi bi-flag-fill"></i> विदाहरू: {{ $totalHolidays }}</span>
                <span class="badge badge-exam"><i class="bi bi-pencil-square"></i> परीक्षा तालिका: {{ $totalExams }}</span>
            </div>
        </div>

        @if($groupedByMonth->isEmpty())
            <div style="text-align:center; padding: 40px; color:#94a3b8;">
                क्यालेन्डरमा कुनै कार्यक्रम प्रविष्टि गरिएको छैन।
            </div>
        @else
            @foreach($groupedByMonth as $monthName => $monthEvents)
                <div class="month-block">
                    <div class="month-title">
                        <i class="bi bi-calendar-month"></i> {{ $monthName }}
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 180px;">मिति (AD / BS Date)</th>
                                <th>कार्यक्रम / बिदाको विवरण (Event / Holiday Title)</th>
                                <th style="width: 140px;">वर्ग (Category)</th>
                                <th style="width: 130px;">सत्र (Term)</th>
                                <th style="width: 110px;">लक्षित समूह</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($monthEvents as $ev)
                                @php
                                    $isHoliday = (bool) $ev->is_holiday;
                                    $isExam = $ev->category === 'exam';
                                    $rowClass = $isHoliday ? 'holiday-row' : ($isExam ? 'exam-row' : '');
                                    $startDate = $ev->start ? \Carbon\Carbon::parse($ev->start) : null;
                                    $endDate = $ev->end ? \Carbon\Carbon::parse($ev->end) : null;
                                @endphp
                                <tr class="{{ $rowClass }}">
                                    <td>
                                        <strong>{{ $startDate ? $startDate->format('M d, Y') : '-' }}</strong>
                                        @if($startDate && $endDate && $startDate->toDateString() !== $endDate->toDateString())
                                            - {{ $endDate->format('M d') }}
                                        @endif
                                        <div style="color:#64748b; font-size:11px;">
                                            @if($ev->start_bs)
                                                ({{ $ev->start_bs }})
                                            @elseif($startDate)
                                                ({{ bsDate($startDate->format('Y-m-d'), true, 'short') }})
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <strong style="{{ $isHoliday ? 'color:#b91c1c;' : '' }}">{{ $ev->title }}</strong>
                                        @if($ev->description)
                                            <div style="color:#64748b; font-size:11px; margin-top:2px;">{{ $ev->description }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="type-tag" style="background:{{ $ev->color ?? '#6366f1' }}20; color:{{ $ev->color ?? '#4f46e5' }}">
                                            {{ $isHoliday ? '🎌 बिदा (Holiday)' : ucfirst($ev->category ?? 'Academic') }}
                                        </span>
                                    </td>
                                    <td style="color:#475569;">{{ $ev->academic_term ?? '-' }}</td>
                                    <td style="color:#475569; text-transform: capitalize;">{{ $ev->audience ?? 'All' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        @endif

        <div style="text-align:center; border-top:1px solid #e2e8f0; padding-top:15px; margin-top:30px; font-size:11px; color:#94a3b8;">
            विद्यालय प्रशासन • आधिकारिक शैक्षिक पात्रो तथा कार्यक्रम तालिका (NepalSchoolHub Academic Calendar Engine)
        </div>
    </div>

</body>
</html>
