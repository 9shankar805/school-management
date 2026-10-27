<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Bulk Student ID Cards</title>
@php
  $t          = \App\Models\IdCardTemplate::active();
  $school     = config('tenant.school') ?? \App\Models\School::first();
  $schoolName = $school?->name ?? config('app.name', 'School Hub Management');
  $tagline    = $t->tagline_text ?? 'Student Identification Card';
  $address    = $school?->address ?? 'Kathmandu, Nepal';
  $phone      = $school?->phone ?? '+977-1-4412345';
  $headerBg   = $t->header_bg_color ?? '#1e3a8a';
  $layout     = $layout ?? 'dualsided';

  $logoSrc = '';
  if ($school?->logo) {
      $p = storage_path('app/public/' . ltrim($school->logo, '/'));
      if (file_exists($p)) {
          $m = mime_content_type($p) ?: 'image/png';
          $logoSrc = 'data:' . $m . ';base64,' . base64_encode(file_get_contents($p));
      }
  }
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

  /* FRONT CARD CONTAINER */
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
      color: #dbeafe;
      margin-top: 0.8pt;
  }
  .front-school-contact {
      font-size: 4pt;
      color: #bfdbfe;
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
  .student-name {
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
      color: #1e3a8a;
      font-weight: bold;
  }

  /* BACK CARD CONTAINER */
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
  table.back-table {
      width: 100%;
      border-collapse: collapse;
  }
  .qr-cell {
      width: 18mm;
      vertical-align: middle;
      text-align: center;
  }
  .qr-box {
      width: 16mm;
      height: 16mm;
      border: 0.8pt solid #cbd5e1;
      border-radius: 1.5mm;
      background: #ffffff;
      padding: 0.8mm;
      margin: 0 auto;
  }
  .qr-box img {
      width: 100%;
      height: 100%;
  }
  .qr-caption {
      font-size: 3.8pt;
      font-weight: bold;
      color: #1e3a8a;
      margin-top: 0.8pt;
  }
  .terms-cell {
      vertical-align: top;
      padding-left: 2mm;
      font-size: 4.4pt;
      color: #334155;
      line-height: 1.25;
  }
  .terms-cell ul {
      margin-left: 3mm;
      padding-left: 0;
  }
  .terms-cell li {
      margin-bottom: 0.8pt;
  }
  .emg-note {
      font-size: 4.6pt;
      color: #dc2626;
      font-weight: bold;
      margin-top: 1pt;
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
  $perPage = ($layout === 'front') ? 8 : 4; // 8 front cards per page OR 4 dual-sided pairs per page
  $chunks  = $students->chunk($perPage);
@endphp

@foreach($chunks as $chunkIndex => $pageStudents)
<div class="sheet">
  <table class="grid-table">
    @if($layout === 'front')
      {{-- 8 FRONT CARDS (2 Columns x 4 Rows) --}}
      @foreach($pageStudents->chunk(2) as $rowStudents)
      <tr>
        @foreach($rowStudents as $s)
          @php
            $st    = $s['student'];
            $promo = $s['promotion'];
            $className   = $promo->schoolClass?->class_name ?? '—';
            $sectionName = $promo->section?->section_name   ?? '—';
            $idNumber    = $promo?->id_card_number ?: ('ST-' . date('Y') . '-' . str_pad($st->id, 4, '0', STR_PAD_LEFT));
            $bloodType   = $st->blood_type ?: 'A+';
            $session     = $schoolSession?->session_name ?: (date('Y') . '-' . (date('Y')+1));
            $dob         = $st->birthday ? \Carbon\Carbon::parse($st->birthday)->format('d M Y') : '—';
            $contact     = $st->phone ?: ($st->emergency_contact ?: $phone);
            
            $fatherName = $st->parent_info?->father_name;
            if (!$fatherName && $st->parents && $st->parents->isNotEmpty()) {
                foreach ($st->parents as $pr) {
                    if (strtolower($pr->pivot->relationship ?? $pr->relationship ?? '') === 'father') {
                        $fatherName = $pr->full_name;
                    }
                }
            }
            $fatherName = $fatherName ?: '—';
          @endphp
          <td class="card-cell">
            <div class="card-front">
              <div class="front-header">
                <table style="width: 100%; border-collapse: collapse;">
                  <tr>
                    @if($logoSrc)
                    <td style="width: 12mm; vertical-align: middle; text-align: left; padding-right: 1.5mm;">
                      <div style="width: 10.5mm; height: 10.5mm; border-radius: 50%; background: #ffffff; padding: 1pt; overflow: hidden; display: inline-block;">
                        <img src="{{ $logoSrc }}" style="width: 100%; height: 100%; object-fit: contain; display: block;" alt="Logo">
                      </div>
                    </td>
                    @endif
                    <td style="vertical-align: middle; text-align: {{ $logoSrc ? 'left' : 'center' }};">
                      <div class="front-school-title">{{ Str::limit($schoolName, $logoSrc ? 22 : 26) }}</div>
                      <div class="front-school-sub">{{ $tagline }}</div>
                      <div class="front-school-contact">{{ Str::limit($address, $logoSrc ? 28 : 36) }} &bull; Tel: {{ $phone }}</div>
                    </td>
                  </tr>
                </table>
              </div>
              <div class="front-body">
                <table class="front-body-table">
                  <tr>
                    <td class="photo-container">
                      <div class="photo-box">
                        <img src="{{ $s['photoSrc'] }}" alt="Photo">
                      </div>
                    </td>
                    <td class="details-container">
                      <div class="student-name">{{ Str::limit($st->full_name, 22) }}</div>
                      <table class="info-subtable">
                        <tr><td class="lbl">ID / Reg No:</td><td class="val">{{ $idNumber }}</td></tr>
                        <tr><td class="lbl">Class & Sec:</td><td class="val">{{ $className }} ({{ $sectionName }})</td></tr>
                        <tr><td class="lbl">Father:</td><td class="val">{{ Str::limit($fatherName, 18) }}</td></tr>
                        <tr><td class="lbl">DOB / Blood:</td><td class="val">{{ $dob }} &bull; {{ $bloodType }}</td></tr>
                        <tr><td class="lbl">Phone:</td><td class="val">{{ $contact }}</td></tr>
                      </table>
                    </td>
                  </tr>
                </table>
              </div>
              <div class="front-footer">
                <table class="footer-table">
                  <tr>
                    <td class="valid-txt">Valid: {{ $session }}</td>
                    <td class="sig-txt">Principal Sign.</td>
                  </tr>
                </table>
              </div>
            </div>
          </td>
        @endforeach
        @if($rowStudents->count() == 1)
          <td class="card-cell"></td>
        @endif
      </tr>
      @endforeach

    @else
      {{-- DUAL SIDED (4 Students: Front + Back Side-by-Side per row) --}}
      @foreach($pageStudents as $s)
        @php
          $st    = $s['student'];
          $promo = $s['promotion'];
          $className   = $promo->schoolClass?->class_name ?? '—';
          $sectionName = $promo->section?->section_name   ?? '—';
          $idNumber    = $promo?->id_card_number ?: ('ST-' . date('Y') . '-' . str_pad($st->id, 4, '0', STR_PAD_LEFT));
          $bloodType   = $st->blood_type ?: 'A+';
          $session     = $schoolSession?->session_name ?: (date('Y') . '-' . (date('Y')+1));
          $dob         = $st->birthday ? \Carbon\Carbon::parse($st->birthday)->format('d M Y') : '—';
          $contact     = $st->phone ?: ($st->emergency_contact ?: $phone);
          
          $fatherName = $st->parent_info?->father_name;
          if (!$fatherName && $st->parents && $st->parents->isNotEmpty()) {
              foreach ($st->parents as $pr) {
                  if (strtolower($pr->pivot->relationship ?? $pr->relationship ?? '') === 'father') {
                      $fatherName = $pr->full_name;
                  }
              }
          }
          $fatherName = $fatherName ?: '—';
        @endphp
        <tr>
          {{-- FRONT CARD --}}
          <td class="card-cell">
            <div class="card-front">
              <div class="front-header">
                <table style="width: 100%; border-collapse: collapse;">
                  <tr>
                    @if($logoSrc)
                    <td style="width: 12mm; vertical-align: middle; text-align: left; padding-right: 1.5mm;">
                      <div style="width: 10.5mm; height: 10.5mm; border-radius: 50%; background: #ffffff; padding: 1pt; overflow: hidden; display: inline-block;">
                        <img src="{{ $logoSrc }}" style="width: 100%; height: 100%; object-fit: contain; display: block;" alt="Logo">
                      </div>
                    </td>
                    @endif
                    <td style="vertical-align: middle; text-align: {{ $logoSrc ? 'left' : 'center' }};">
                      <div class="front-school-title">{{ Str::limit($schoolName, $logoSrc ? 22 : 26) }}</div>
                      <div class="front-school-sub">{{ $tagline }}</div>
                      <div class="front-school-contact">{{ Str::limit($address, $logoSrc ? 28 : 36) }} &bull; Tel: {{ $phone }}</div>
                    </td>
                  </tr>
                </table>
              </div>
              <div class="front-body">
                <table class="front-body-table">
                  <tr>
                    <td class="photo-container">
                      <div class="photo-box">
                        <img src="{{ $s['photoSrc'] }}" alt="Photo">
                      </div>
                    </td>
                    <td class="details-container">
                      <div class="student-name">{{ Str::limit($st->full_name, 22) }}</div>
                      <table class="info-subtable">
                        <tr><td class="lbl">ID / Reg No:</td><td class="val">{{ $idNumber }}</td></tr>
                        <tr><td class="lbl">Class & Sec:</td><td class="val">{{ $className }} ({{ $sectionName }})</td></tr>
                        <tr><td class="lbl">Father:</td><td class="val">{{ Str::limit($fatherName, 18) }}</td></tr>
                        <tr><td class="lbl">DOB / Blood:</td><td class="val">{{ $dob }} &bull; {{ $bloodType }}</td></tr>
                        <tr><td class="lbl">Phone:</td><td class="val">{{ $contact }}</td></tr>
                      </table>
                    </td>
                  </tr>
                </table>
              </div>
              <div class="front-footer">
                <table class="footer-table">
                  <tr>
                    <td class="valid-txt">Valid: {{ $session }}</td>
                    <td class="sig-txt">Principal Sign.</td>
                  </tr>
                </table>
              </div>
            </div>
          </td>

          {{-- BACK CARD --}}
          <td class="card-cell">
            <div class="card-back">
              <div class="back-header">
                INSTANT QR LOGIN &bull; DIGITAL ID
              </div>
              <div class="back-body">
                <table class="back-table">
                  <tr>
                    <td class="qr-cell">
                      <div class="qr-box">
                        <img src="{{ $s['qrSrc'] }}" alt="Instant QR Login">
                      </div>
                      <div class="qr-caption">INSTANT LOGIN</div>
                    </td>
                    <td class="terms-cell">
                      <ul>
                        <li>1. Scan with phone camera for instant portal login.</li>
                        <li>2. Live student authenticity verification online.</li>
                        <li>3. Card is non-transferable property of institution.</li>
                        <li>4. If found, please return to school office.</li>
                      </ul>
                      <div class="emg-note">Emergency Tel: {{ $phone }}</div>
                    </td>
                  </tr>
                </table>
              </div>
              <div class="back-footer">
                Serial No: {{ $idNumber }} &bull; {{ Str::limit($schoolName, 30) }}
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
