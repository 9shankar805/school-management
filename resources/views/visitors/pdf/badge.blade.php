<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Visitor Pass - {{ $visitor->badge_number }}</title>
    <style>
        @page {
            margin: 8pt;
            size: 240pt 360pt portrait;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
        }
        body {
            background: #ffffff;
            color: #0f172a;
            margin: 0;
            padding: 0;
        }
        .pass-card {
            border: 1.5pt solid #0f172a;
            border-radius: 8pt;
            background: #ffffff;
            padding: 10pt 12pt 8pt 12pt;
            text-align: center;
            page-break-inside: avoid;
        }
        
        /* Top Header */
        .school-title {
            font-size: 10.5pt;
            font-weight: 800;
            text-transform: uppercase;
            color: #0f172a;
            letter-spacing: 0.5pt;
            line-height: 1.2;
        }
        .pass-subtitle {
            font-size: 7pt;
            font-weight: 700;
            color: #475569;
            letter-spacing: 1.5pt;
            text-transform: uppercase;
            margin-top: 2pt;
        }
        .header-divider {
            border-top: 1pt solid #0f172a;
            margin: 5pt 0 6pt 0;
        }

        /* Badge Number Box */
        .badge-box {
            border: 1pt solid #94a3b8;
            border-radius: 4pt;
            background: #f8fafc;
            padding: 3pt 6pt;
            margin: 0 auto 5pt auto;
            text-align: center;
        }
        .badge-code {
            font-size: 11.5pt;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 1pt;
            font-family: 'DejaVu Sans Mono', monospace, sans-serif;
        }
        .badge-status {
            font-size: 6pt;
            font-weight: 700;
            color: #334155;
            letter-spacing: 0.6pt;
            text-transform: uppercase;
            margin-top: 1pt;
        }

        /* Visitor Name */
        .visitor-name {
            font-size: 12pt;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
            margin-top: 2pt;
        }
        .visitor-org {
            font-size: 7.5pt;
            font-weight: 500;
            color: #64748b;
            margin-top: 1pt;
            margin-bottom: 4pt;
        }

        /* Details Table */
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 4pt 0;
            border-top: 0.75pt dashed #cbd5e1;
            border-bottom: 0.75pt dashed #cbd5e1;
            padding: 3pt 0;
        }
        .details-table td {
            padding: 2pt 1pt;
            font-size: 7pt;
            text-align: left;
            vertical-align: middle;
        }
        .label {
            color: #475569;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 6pt;
            letter-spacing: 0.3pt;
            width: 38%;
        }
        .val {
            color: #0f172a;
            font-weight: 700;
            width: 62%;
        }

        /* Signatures */
        .sign-table {
            width: 100%;
            margin-top: 8pt;
            border-collapse: collapse;
        }
        .sign-table td {
            width: 50%;
            vertical-align: bottom;
            padding: 0 4pt;
        }
        .sign-line {
            border-top: 0.75pt solid #475569;
            padding-top: 2pt;
            font-size: 6pt;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.4pt;
        }

        /* Footer */
        .footer-note {
            font-size: 5.5pt;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.4pt;
            margin-top: 6pt;
        }
    </style>
</head>
<body>
    <div class="pass-card">
        {{-- Header --}}
        <div class="school-title">{{ $school->name ?? 'GENESIS GLOBAL SCHOOL' }}</div>
        <div class="pass-subtitle">Official Visitor Pass</div>
        
        <div class="header-divider"></div>

        {{-- Badge Box --}}
        <div class="badge-box">
            <div class="badge-code">{{ $visitor->badge_number }}</div>
            <div class="badge-status">● Campus Visitor Access</div>
        </div>

        {{-- Visitor Name & Organization --}}
        <div class="visitor-name">{{ $visitor->name }}</div>
        <div class="visitor-org">
            @if($visitor->organization)
                {{ $visitor->organization }}
            @else
                Authorized Guest
            @endif
        </div>

        {{-- Details Grid --}}
        <table class="details-table" cellpadding="0" cellspacing="0">
            <tr>
                <td class="label">Phone:</td>
                <td class="val">{{ $visitor->phone }}</td>
            </tr>
            <tr>
                <td class="label">Meeting With:</td>
                <td class="val">{{ $visitor->person_to_meet }}</td>
            </tr>
            <tr>
                <td class="label">Purpose:</td>
                <td class="val">{{ ucwords(str_replace('_', ' ', $visitor->purpose)) }}</td>
            </tr>
            <tr>
                <td class="label">Check In:</td>
                <td class="val">{{ \Carbon\Carbon::parse($visitor->check_in_at)->format('M d, Y • h:i A') }}</td>
            </tr>
            @if($visitor->id_number)
            <tr>
                <td class="label">ID Verification:</td>
                <td class="val">{{ strtoupper($visitor->id_type ?? 'ID') }}: {{ $visitor->id_number }}</td>
            </tr>
            @endif
        </table>

        {{-- Signatures --}}
        <table class="sign-table" cellpadding="0" cellspacing="0">
            <tr>
                <td align="left">
                    <div style="height: 10pt;"></div>
                    <div class="sign-line" style="text-align: left;">Visitor Signature</div>
                </td>
                <td align="right">
                    <div style="height: 10pt;"></div>
                    <div class="sign-line" style="text-align: right;">Security Officer</div>
                </td>
            </tr>
        </table>

        {{-- Footer Note --}}
        <div class="footer-note">
            Please wear visibly &bull; Return to security upon departure
        </div>
    </div>
</body>
</html>
