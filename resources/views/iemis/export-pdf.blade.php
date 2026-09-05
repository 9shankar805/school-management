<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 7px; color: #0f172a; margin: 0; padding: 10px; }
    h1 { font-size: 11px; font-weight: 700; margin: 0 0 2px 0; color: #1e3a8a; }
    p.sub { font-size: 8px; color: #64748b; margin: 0 0 8px 0; }
    table { width: 100%; border-collapse: collapse; }
    thead tr { background: #1e3a8a; color: #ffffff; }
    thead th { padding: 4px 5px; text-align: left; font-size: 6.5px; font-weight: 700; white-space: nowrap; }
    tbody tr:nth-child(even) { background: #f1f5f9; }
    tbody td { padding: 3px 5px; border-bottom: 1px solid #e2e8f0; white-space: nowrap; }
    .badge { display: inline-block; padding: 1px 5px; border-radius: 10px; font-size: 6px; font-weight: 700; }
    .badge-m { background: #dbeafe; color: #1d4ed8; }
    .badge-f { background: #fce7f3; color: #be185d; }
    .badge-y { background: #dcfce7; color: #166534; }
</style>
</head>
<body>
<h1>
    {{ $school?->name ?? 'School' }} — IEMIS Student Enrolment Report
</h1>
<p class="sub">
    Session: {{ $session?->session_name ?? 'N/A' }} &nbsp;|&nbsp;
    IEMIS Code: {{ $school?->iemis_code ?? 'N/A' }} &nbsp;|&nbsp;
    Total Students: {{ $rows->count() }} &nbsp;|&nbsp;
    Generated: {{ now()->format('d M Y, H:i') }}
</p>

<table>
    <thead>
        <tr>
            <th>SN</th>
            <th>Admission No</th>
            <th>Full Name</th>
            <th>DOB (BS)</th>
            <th>Gender</th>
            <th>Ethnicity</th>
            <th>Mother Tongue</th>
            <th>District</th>
            <th>Class</th>
            <th>Section</th>
            <th>Roll</th>
            <th>Father Name</th>
            <th>Mother Name</th>
            <th>Phone</th>
            <th>Board Reg No</th>
            <th>Scholarship</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
        <tr>
            <td>{{ $row['SN'] }}</td>
            <td>{{ $row['Admission No'] }}</td>
            <td>{{ $row['First Name'] }} {{ $row['Last Name'] }}</td>
            <td>{{ $row['Date of Birth (BS)'] }}</td>
            <td>
                @if(strtolower($row['Gender']) === 'male')
                <span class="badge badge-m">M</span>
                @elseif(strtolower($row['Gender']) === 'female')
                <span class="badge badge-f">F</span>
                @else
                {{ $row['Gender'] }}
                @endif
            </td>
            <td>{{ $row['Ethnicity / Caste'] }}</td>
            <td>{{ $row['Mother Tongue'] }}</td>
            <td>{{ $row['District'] }}</td>
            <td>{{ $row['Class / Grade'] }}</td>
            <td>{{ $row['Section'] }}</td>
            <td>{{ $row['Roll No'] }}</td>
            <td>{{ $row["Father's Name"] }}</td>
            <td>{{ $row["Mother's Name"] }}</td>
            <td>{{ $row['Phone'] }}</td>
            <td>{{ $row['Board Reg No'] }}</td>
            <td>
                @if($row['Scholarship'] === 'Yes')
                <span class="badge badge-y">Yes</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>
