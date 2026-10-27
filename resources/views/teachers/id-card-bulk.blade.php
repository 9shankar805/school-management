<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Bulk Faculty & Staff ID Cards</title>
@php
  $t          = \App\Models\IdCardTemplate::active();
  $school     = $school ?? config('tenant.school') ?? \App\Models\School::first();
  $schoolName = $school?->name ?? config('app.name', 'School Hub Management');
  $tagline    = $t->tagline_text ?? 'Faculty Identification Card';
  $address    = $school?->address ?? 'Kathmandu, Nepal';
  $phone      = $school?->phone ?? '+977-1-4412345';
  $headerBg   = '#0f172a'; // Dark slate for faculty
  $layout     = $layout ?? 'dualsided';
@endphp
<style>
  @page {
      size: A4 portrait;
      margin: 6mm 6mm;
  }
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: DejaVu Sans, sans-serif; background: #ffffff; color: #0f172a; }
  
  .sheet {
      width: 100%;
      page-break-inside: avoid;
  }
  .page-break {
      page-break-after: always;
  }

  table.grid-table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 5mm 4.5mm;
  }
  table.grid-table td.card-cell {
      width: 50%;
      vertical-align: top;
      padding: 0;
  }

  /* FRONT CARD */
  .card-front {
      width: 100%;
      height: 58.5mm;
      border: 0.8pt solid #cbd5e1;
      border-radius: 4.5mm;
      background: #ffffff;
      position: relative;
      overflow: hidden;
  }

  .front-header {
      background: {{ $headerBg }};
      color: #ffffff;
      text-align: center;
      padding: 2mm 2.5mm 1.5mm;
      height: 14.5mm;
  }
  .front-school-title {
      font-size: 7.2pt;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.3pt;
      line-height: 1.1;
      color: #ffffff;
  }
  .front-school-sub {
      font-size: 4.5pt;
      color: #cbd5e1;
      margin-top: 0.8pt;
  }
  .front-school-contact {
      font-size: 4pt;
      color: #94a3b8;
      margin-top: 0.5pt;
  }

  .front-body {
      padding: 1.5mm 2.5mm;
  }
  table.front-body-table {
      width: 100%;
      border-collapse: collapse;
  }
  .photo-container {
      width: 18mm;
      vertical-align: top;
      text-align: left;
  }
  .photo-box {
      width: 16mm;
      height: 19.5mm;
      border: 1pt solid {{ $headerBg }};
      border-radius: 2mm;
      overflow: hidden;
      background: #f8fafc;
      text-align: center;
  }
  .photo-box img {
      width: 100%;
      height: 100%;
      object-fit: cover;
  }

  .details-container {
      vertical-align: top;
      padding-left: 2mm;
  }
  .teacher-name {
      font-size: 7.8pt;
      font-weight: bold;
      color: #0f172a;
      line-height: 1.1;
      margin-bottom: 1pt;
  }
  table.info-subtable {
      width: 100%;
      border-collapse: collapse;
      font-size: 5pt;
  }
  table.info-subtable td {
      padding: 0.5pt 0;
      vertical-align: top;
  }
  .lbl {
      color: #64748b;
      font-weight: bold;
      width: 19mm;
  }
  .val {
      color: #0f172a;
      font-weight: bold;
  }

  .front-footer {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      height: 7mm;
      background: #f8fafc;
      border-top: 0.5pt solid #e2e8f0;
      padding: 1mm 2.5mm;
  }
  table.footer-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 4.5pt;
  }
  .valid-txt {
      color: #475569;
      font-weight: bold;
  }
  .sig-txt {
      text-align: right;
      color: #0f172a;
      font-weight: bold;
  }

  /* BACK CARD */
  .card-back {
      width: 100%;
      height: 58.5mm;
      border: 0.8pt solid #cbd5e1;
      border-radius: 4.5mm;
      background: #f8fafc;
      position: relative;
      overflow: hidden;
  }
  .back-header {
      background: {{ $headerBg }};
      color: #ffffff;
      text-align: center;
      padding: 1.8mm 2mm;
      font-size: 6pt;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.5pt;
  }
  .back-body {
      padding: 1.5mm 2.5mm;
  }
  .terms-box {
      font-size: 4.6pt;
      color: #334155;
      line-height: 1.3;
  }
  .terms-box ul {
      margin-left: 3.5mm;
      padding-left: 0;
  }
  .terms-box li {
      margin-bottom: 1pt;
  }
  .emg-note {
      font-size: 4.8pt;
      color: #dc2626;
      font-weight: bold;
      margin-top: 2pt;
  }
  .back-footer {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      height: 5.5mm;
      background: #e2e8f0;
      text-align: center;
      font-size: 4.2pt;
      color: #475569;
      padding-top: 1.2mm;
  }
</style>
</head>
<body>

@php
  $perPage = ($layout === 'front') ? 8 : 4;
  $chunks  = collect($teachers)->chunk($perPage);
@endphp

@foreach($chunks as $chunkIndex => $pageTeachers)
<div class="sheet">
  <table class="grid-table">
    @if($layout === 'front')
      @foreach($pageTeachers->chunk(2) as $rowTeachers)
      <tr>
        @foreach($rowTeachers as $item)
          @php
            $t = $item['teacher'];
            $desig = $t->designation ?: 'Faculty Member';
            $dept  = $t->department?->name ?: 'Academics';
            $empId = 'EMP-' . str_pad($t->id, 4, '0', STR_PAD_LEFT);
            $blood = $t->blood_type ?: 'O+';
            $phoneNo = $t->phone ?: $phone;
          @endphp
          <td class="card-cell">
            <div class="card-front">
              <div class="front-header">
                <table style="width: 100%; border-collapse: collapse;">
                  <tr>
                    @if(isset($logoSrc) && $logoSrc)
                    <td style="width: 12mm; vertical-align: middle; text-align: left; padding-right: 1.5mm;">
                      <div style="width: 10.5mm; height: 10.5mm; border-radius: 50%; background: #ffffff; padding: 1pt; overflow: hidden; display: inline-block;">
                        <img src="{{ $logoSrc }}" style="width: 100%; height: 100%; object-fit: contain; display: block;" alt="Logo">
                      </div>
                    </td>
                    @endif
                    <td style="vertical-align: middle; text-align: {{ isset($logoSrc) && $logoSrc ? 'left' : 'center' }};">
                      <div class="front-school-title">{{ Str::limit($schoolName, isset($logoSrc) && $logoSrc ? 22 : 26) }}</div>
                      <div class="front-school-sub">Faculty &amp; Staff Identification</div>
                      <div class="front-school-contact">{{ Str::limit($address, isset($logoSrc) && $logoSrc ? 28 : 36) }} &bull; Tel: {{ $phone }}</div>
                    </td>
                  </tr>
                </table>
              </div>
              <div class="front-body">
                <table class="front-body-table">
                  <tr>
                    <td class="photo-container">
                      <div class="photo-box">
                        <img src="{{ $item['photoSrc'] }}" alt="Photo">
                      </div>
                    </td>
                    <td class="details-container">
                      <div class="teacher-name">{{ Str::limit($t->full_name, 22) }}</div>
                      <table class="info-subtable">
                        <tr><td class="lbl">Employee ID:</td><td class="val">{{ $empId }}</td></tr>
                        <tr><td class="lbl">Designation:</td><td class="val">{{ Str::limit($desig, 20) }}</td></tr>
                        <tr><td class="lbl">Department:</td><td class="val">{{ Str::limit($dept, 20) }}</td></tr>
                        <tr><td class="lbl">Blood Group:</td><td class="val">{{ $blood }}</td></tr>
                        <tr><td class="lbl">Contact:</td><td class="val">{{ $phoneNo }}</td></tr>
                      </table>
                    </td>
                  </tr>
                </table>
              </div>
              <div class="front-footer">
                <table class="footer-table">
                  <tr>
                    <td class="valid-txt">Issued Session: {{ $schoolSession?->session_name }}</td>
                    <td class="sig-txt">Director / Head</td>
                  </tr>
                </table>
              </div>
            </div>
          </td>
        @endforeach
        @if($rowTeachers->count() == 1)
          <td class="card-cell"></td>
        @endif
      </tr>
      @endforeach

    @else
      @foreach($pageTeachers as $item)
        @php
          $t = $item['teacher'];
          $desig = $t->designation ?: 'Faculty Member';
          $dept  = $t->department?->name ?: 'Academics';
          $empId = 'EMP-' . str_pad($t->id, 4, '0', STR_PAD_LEFT);
          $blood = $t->blood_type ?: 'O+';
          $phoneNo = $t->phone ?: $phone;
        @endphp
        <tr>
          {{-- FRONT CARD --}}
          <td class="card-cell">
            <div class="card-front">
              <div class="front-header">
                <table style="width: 100%; border-collapse: collapse;">
                  <tr>
                    @if(isset($logoSrc) && $logoSrc)
                    <td style="width: 12mm; vertical-align: middle; text-align: left; padding-right: 1.5mm;">
                      <div style="width: 10.5mm; height: 10.5mm; border-radius: 50%; background: #ffffff; padding: 1pt; overflow: hidden; display: inline-block;">
                        <img src="{{ $logoSrc }}" style="width: 100%; height: 100%; object-fit: contain; display: block;" alt="Logo">
                      </div>
                    </td>
                    @endif
                    <td style="vertical-align: middle; text-align: {{ isset($logoSrc) && $logoSrc ? 'left' : 'center' }};">
                      <div class="front-school-title">{{ Str::limit($schoolName, isset($logoSrc) && $logoSrc ? 22 : 26) }}</div>
                      <div class="front-school-sub">Faculty &amp; Staff Identification</div>
                      <div class="front-school-contact">{{ Str::limit($address, isset($logoSrc) && $logoSrc ? 28 : 36) }} &bull; Tel: {{ $phone }}</div>
                    </td>
                  </tr>
                </table>
              </div>
              <div class="front-body">
                <table class="front-body-table">
                  <tr>
                    <td class="photo-container">
                      <div class="photo-box">
                        <img src="{{ $item['photoSrc'] }}" alt="Photo">
                      </div>
                    </td>
                    <td class="details-container">
                      <div class="teacher-name">{{ Str::limit($t->full_name, 22) }}</div>
                      <table class="info-subtable">
                        <tr><td class="lbl">Employee ID:</td><td class="val">{{ $empId }}</td></tr>
                        <tr><td class="lbl">Designation:</td><td class="val">{{ Str::limit($desig, 20) }}</td></tr>
                        <tr><td class="lbl">Department:</td><td class="val">{{ Str::limit($dept, 20) }}</td></tr>
                        <tr><td class="lbl">Blood Group:</td><td class="val">{{ $blood }}</td></tr>
                        <tr><td class="lbl">Contact:</td><td class="val">{{ $phoneNo }}</td></tr>
                      </table>
                    </td>
                  </tr>
                </table>
              </div>
              <div class="front-footer">
                <table class="footer-table">
                  <tr>
                    <td class="valid-txt">Issued Session: {{ $schoolSession?->session_name }}</td>
                    <td class="sig-txt">Director / Head</td>
                  </tr>
                </table>
              </div>
            </div>
          </td>

          {{-- BACK CARD --}}
          <td class="card-cell">
            <div class="card-back">
              <div class="back-header">
                INSTANT QR LOGIN &bull; FACULTY ID
              </div>
              <div class="back-body">
                <table style="width: 100%; border-collapse: collapse;">
                  <tr>
                    <td style="width: 18mm; vertical-align: middle; text-align: center;">
                      <div style="width: 16mm; height: 16mm; border: 0.8pt solid #cbd5e1; border-radius: 1.5mm; background: #ffffff; padding: 0.8mm; margin: 0 auto;">
                        <img src="{{ $item['qrSrc'] }}" style="width: 100%; height: 100%;" alt="QR Login">
                      </div>
                      <div style="font-size: 3.8pt; font-weight: bold; color: #0f172a; margin-top: 0.8pt;">INSTANT LOGIN</div>
                    </td>
                    <td style="vertical-align: top; padding-left: 2mm; font-size: 4.4pt; color: #334155; line-height: 1.25;">
                      <ul style="margin-left: 3mm; padding-left: 0;">
                        <li>1. Scan QR with camera to login to teacher portal.</li>
                        <li>2. Official faculty &amp; staff identification badge.</li>
                        <li>3. Card is non-transferable property of institution.</li>
                        <li>4. If found, please return to administrative desk.</li>
                      </ul>
                      <div class="emg-note">Emergency Tel: {{ $phone }}</div>
                    </td>
                  </tr>
                </table>
              </div>
              <div class="back-footer">
                Serial No: {{ $empId }} &bull; {{ Str::limit($schoolName, 30) }}
              </div>
            </div>
          </td>
        </tr>
      @endforeach
    @endif
  </table>
</div>
@if(!$loop->last)
<div class="page-break"></div>
@endif
@endforeach

</body>
</html>
