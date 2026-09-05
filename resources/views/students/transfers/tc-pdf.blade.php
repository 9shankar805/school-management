<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Transfer Certificate — {{ $transfer->tc_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11pt;
            color: #1e293b;
            background: #fff;
            padding: 0;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 18mm 20mm;
            position: relative;
        }

        /* Watermark */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-35deg);
            font-size: 72pt;
            font-weight: 900;
            color: rgba(99, 102, 241, 0.06);
            white-space: nowrap;
            pointer-events: none;
            z-index: 0;
        }

        .content { position: relative; z-index: 1; }

        /* Border */
        .border-frame {
            border: 3px double #6366f1;
            padding: 16px;
            min-height: 260mm;
        }

        /* Header */
        .school-header {
            text-align: center;
            border-bottom: 2px solid #6366f1;
            padding-bottom: 14px;
            margin-bottom: 14px;
        }

        .school-logo {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 8px;
            display: block;
        }

        .school-logo-placeholder {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: #6366f1;
            margin: 0 auto 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 28pt;
            font-weight: 700;
        }

        .school-name {
            font-size: 16pt;
            font-weight: 700;
            color: #1e1b4b;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .school-address {
            font-size: 9pt;
            color: #64748b;
            margin-top: 2px;
        }

        /* TC Title */
        .tc-title {
            text-align: center;
            margin: 16px 0 10px;
        }

        .tc-title h2 {
            font-size: 18pt;
            font-weight: 700;
            color: #4338ca;
            text-transform: uppercase;
            letter-spacing: 3px;
            border-bottom: 1px solid #c7d2fe;
            display: inline-block;
            padding-bottom: 4px;
        }

        .tc-number-badge {
            display: inline-block;
            background: #eef2ff;
            border: 1px solid #c7d2fe;
            border-radius: 20px;
            padding: 3px 14px;
            font-size: 9pt;
            color: #4338ca;
            font-weight: 600;
            margin-top: 6px;
        }

        /* Student info */
        .student-photo-wrap {
            float: right;
            margin: 0 0 10px 20px;
        }

        .student-photo {
            width: 90px;
            height: 110px;
            object-fit: cover;
            border: 2px solid #c7d2fe;
            border-radius: 4px;
        }

        .student-photo-placeholder {
            width: 90px;
            height: 110px;
            background: #f1f5f9;
            border: 2px solid #c7d2fe;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36pt;
            color: #94a3b8;
        }

        /* Certify block */
        .certify-block {
            font-size: 10.5pt;
            line-height: 1.9;
            color: #374151;
            margin: 16px 0;
            text-align: justify;
        }

        .certify-block .highlight {
            font-weight: 700;
            color: #1e1b4b;
            text-transform: capitalize;
        }

        /* Info table */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 14px 0;
        }

        .info-table td {
            padding: 5px 8px;
            font-size: 10pt;
            vertical-align: top;
            border-bottom: 1px dotted #e2e8f0;
        }

        .info-table td:first-child {
            width: 38%;
            color: #64748b;
            font-weight: 600;
        }

        .info-table td:last-child {
            color: #1e293b;
            font-weight: 500;
        }

        /* Documents carried */
        .docs-section {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 14px;
            margin: 14px 0;
        }

        .docs-section h4 {
            font-size: 9pt;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .doc-item {
            display: inline-block;
            background: #fff;
            border: 1px solid #c7d2fe;
            border-radius: 4px;
            padding: 2px 10px;
            font-size: 9pt;
            margin: 2px 4px;
            color: #3730a3;
        }

        /* Conduct */
        .conduct-box {
            text-align: center;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 10px;
            margin: 14px 0;
        }

        .conduct-box .conduct-value {
            font-size: 14pt;
            font-weight: 700;
            color: #1d4ed8;
        }

        .conduct-box .conduct-label {
            font-size: 9pt;
            color: #64748b;
        }

        /* QR + signatures */
        .footer-row {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .qr-block {
            text-align: center;
        }

        .qr-block .qr-label {
            font-size: 7.5pt;
            color: #64748b;
            margin-top: 5px;
        }

        .sig-block {
            text-align: center;
            min-width: 130px;
        }

        .sig-line {
            border-top: 1.5px solid #1e293b;
            margin-bottom: 4px;
        }

        .sig-block p {
            font-size: 9pt;
            color: #374151;
        }

        .sig-block .sig-title {
            font-weight: 600;
            font-size: 9pt;
        }

        /* Stamp circle */
        .stamp-circle {
            width: 80px;
            height: 80px;
            border: 2px dashed #94a3b8;
            border-radius: 50%;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8pt;
            color: #94a3b8;
            text-align: center;
        }

        /* Verification footer */
        .verify-bar {
            margin-top: 16px;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            text-align: center;
            font-size: 8pt;
            color: #94a3b8;
        }

        .clearfix::after { content: ''; display: table; clear: both; }
    </style>
</head>
<body>
<div class="page">
    <div class="watermark">ORIGINAL</div>
    <div class="content">
        <div class="border-frame">

            {{-- School Header --}}
            <div class="school-header">
                @if(!empty($school->logo))
                    <img src="{{ public_path('storage/' . $school->logo) }}" class="school-logo" alt="Logo">
                @else
                    <div class="school-logo-placeholder">{{ substr($school->name, 0, 1) }}</div>
                @endif
                <div class="school-name">{{ $school->name }}</div>
                <div class="school-address">
                    {{ $school->address ?? '' }}{{ $school->city ? ', ' . $school->city : '' }}{{ $school->district ? ', ' . $school->district : '' }}<br>
                    @if($school->phone) Tel: {{ $school->phone }} @endif
                    @if($school->email) &nbsp;|&nbsp; Email: {{ $school->email }} @endif
                </div>
            </div>

            {{-- TC Title --}}
            <div class="tc-title">
                <h2>Transfer Certificate</h2><br>
                <span class="tc-number-badge">TC No: {{ $transfer->tc_number }}</span>
            </div>

            {{-- Student photo + certify block --}}
            <div class="clearfix" style="margin-top:14px;">
                @if(!empty($student->photo))
                    <div class="student-photo-wrap">
                        <img src="{{ public_path('storage/' . $student->photo) }}" class="student-photo" alt="Student Photo">
                    </div>
                @endif

                <div class="certify-block">
                    This is to certify that
                    <span class="highlight">{{ $student->first_name }} {{ $student->last_name }}</span>,
                    son/daughter/ward of
                    <span class="highlight">{{ $guardianName }}</span>,
                    was a bonafide student of this institution
                    from <span class="highlight">{{ $student->admission_date ? \Carbon\Carbon::parse($student->admission_date)->format('F d, Y') : 'the time of admission' }}</span>
                    to <span class="highlight">{{ $transfer->transfer_date->format('F d, Y') }}</span>.
                    The student has been studying in
                    <span class="highlight">Class {{ $fromClass }}</span>
                    and has been granted this Transfer Certificate for the purpose of
                    admission to another institution.
                </div>
            </div>

            {{-- Info table --}}
            <table class="info-table">
                <tr>
                    <td>Student Full Name</td>
                    <td><strong>{{ strtoupper($student->first_name . ' ' . $student->last_name) }}</strong></td>
                </tr>
                <tr>
                    <td>Date of Birth</td>
                    <td>{{ $student->birthday ? \Carbon\Carbon::parse($student->birthday)->format('F d, Y') : 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Gender</td>
                    <td>{{ ucfirst($student->gender ?? 'N/A') }}</td>
                </tr>
                <tr>
                    <td>Nationality</td>
                    <td>{{ $student->nationality ?? 'Nepali' }}</td>
                </tr>
                <tr>
                    <td>Religion</td>
                    <td>{{ $student->religion ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Admission Number</td>
                    <td>{{ $student->admission_no ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Class at Time of Transfer</td>
                    <td>Class {{ $fromClass }}</td>
                </tr>
                <tr>
                    <td>Transfer Date</td>
                    <td>{{ $transfer->transfer_date->format('F d, Y') }}</td>
                </tr>
                <tr>
                    <td>Reason for Transfer</td>
                    <td>{{ $transfer->reason ?? 'Parental request' }}</td>
                </tr>
                @if($transfer->to_school || $transfer->toSchool)
                <tr>
                    <td>Destination School</td>
                    <td>{{ $transfer->destination_school_name }}</td>
                </tr>
                @endif
                <tr>
                    <td>School Dues Cleared</td>
                    <td>Yes — All dues cleared as of {{ $transfer->transfer_date->format('F d, Y') }}</td>
                </tr>
                <tr>
                    <td>Conduct & Character</td>
                    <td><strong style="color:#1d4ed8;">Good</strong></td>
                </tr>
            </table>

            {{-- Documents handed over --}}
            @if($transfer->carry_marksheet || $transfer->carry_character || $transfer->carry_birth_cert || $transfer->carry_medical)
            <div class="docs-section">
                <h4>Documents Issued / Handed Over</h4>
                @if($transfer->carry_marksheet)  <span class="doc-item">✓ Mark Sheet</span> @endif
                @if($transfer->carry_character)  <span class="doc-item">✓ Character Certificate</span> @endif
                @if($transfer->carry_birth_cert) <span class="doc-item">✓ Birth Certificate Copy</span> @endif
                @if($transfer->carry_medical)    <span class="doc-item">✓ Medical Records</span> @endif
            </div>
            @endif

            {{-- Footer: QR + Signatures --}}
            <div class="footer-row">

                {{-- QR code --}}
                <div class="qr-block">
                    <div style="width:75px;height:75px;background:#f8fafc;border:1px solid #c7d2fe;display:flex;align-items:center;justify-content:center;font-size:8pt;color:#6366f1;text-align:center;padding:4px;">
                        VERIFY<br>ONLINE
                    </div>
                    <div class="qr-label">Verify at: {{ config('app.url') }}</div>
                    <div class="qr-label" style="color:#6366f1;font-weight:600;">{{ $transfer->tc_number }}</div>
                </div>

                {{-- Class teacher signature --}}
                <div class="sig-block">
                    <div class="sig-line"></div>
                    <p class="sig-title">Class Teacher</p>
                    <p>Signature &amp; Seal</p>
                </div>

                {{-- Stamp circle --}}
                <div class="sig-block">
                    <div class="stamp-circle">Official<br>Seal</div>
                </div>

                {{-- Principal signature --}}
                <div class="sig-block">
                    <div class="sig-line"></div>
                    <p class="sig-title">Principal / Head of Institution</p>
                    <p>{{ $school->name }}</p>
                    <p>Date: {{ $transfer->tc_issued_at?->format('d M Y') }}</p>
                </div>
            </div>

            {{-- Verification bar --}}
            <div class="verify-bar">
                Verify this certificate online at:
                <strong>{{ config('app.url') }}/transfer/verify/{{ $transfer->tc_token }}</strong>
                &nbsp;&bull;&nbsp;
                Issued: {{ $transfer->tc_issued_at?->format('d F Y H:i') }}
                &nbsp;&bull;&nbsp;
                Token: {{ $transfer->tc_token }}
            </div>

        </div>{{-- .border-frame --}}
    </div>{{-- .content --}}
</div>{{-- .page --}}
</body>
</html>
