<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
@php
  $t          = \App\Models\IdCardTemplate::active();
  $school     = config('tenant.school');
  $schoolName = $school?->name ?? config('app.name', 'Genesis Global School');
  $tagline    = $t->tagline_text ?? 'Faculty Identification Card';
  $address    = $school?->address ?? 'Siraha-7, Nepal';
  $phone      = $school?->phone ?? '+91 97110 00560';

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
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:DejaVu Sans,sans-serif; background:#ffffff; }
.page { padding:10pt; }
.cw { float:left; margin:6pt; page-break-inside:avoid; }

.card {
    width: 153pt; height: 238pt; position: relative; overflow: hidden;
    background: #ffffff; border-radius: 8pt; border: 0.75pt solid #cbd5e1;
}

.hd {
    position: absolute; top: 0; left: 0; right: 0; height: 50pt;
    background: {{ $t->header_bg_color ?? '#1e3a8a' }}; color: #ffffff;
    text-align: center; padding-top: 3.5pt;
}
.hd-curved-bar {
    position: absolute; bottom: -6pt; left: -10pt; right: -10pt; height: 12pt;
    background: {{ $t->header_bg_color ?? '#1e3a8a' }};
    border-bottom-left-radius: 50%; border-bottom-right-radius: 50%;
}
.logo-box {
    display: inline-block; vertical-align: middle; width: 13pt; height: 13pt;
    border-radius: 50%; background: #ffffff; padding: 1pt; margin-right: 2pt; overflow: hidden;
}
.logo-box img { width: 100%; height: 100%; object-fit: contain; }
.school-title { display: inline-block; vertical-align: middle; font-size: 7pt; font-weight: bold; color: #ffffff; text-transform: uppercase; }
.school-sub { font-size: 4pt; color: #93c5fd; margin-top: 0.5pt; font-weight: bold; }
.school-add { font-size: 3.6pt; color: #cbd5e1; margin-top: 0.5pt; }
.school-phone { font-size: 4pt; font-weight: bold; color: #ffffff; margin-top: 0.5pt; }

.photo-wrap { position: absolute; top: 48pt; left: 0; right: 0; text-align: center; z-index: 10; }
.photo-img {
    width: 42pt; height: 44pt; object-fit: cover; border-radius: 4pt;
    border: 1.5pt solid {{ $t->header_bg_color ?? '#1e3a8a' }}; background: #f8fafc;
}
.teacher-name { font-size: 8.5pt; font-weight: bold; color: #0f172a; margin-top: 1.5pt; text-align: center; }
.desig-badge {
    display: inline-block; font-size: 4.5pt; font-weight: bold;
    color: #1e3a8a; background: #dbeafe; padding: 1pt 4pt;
    border-radius: 3pt; margin-top: 1pt; text-transform: uppercase;
}

.info-wrap { position: absolute; top: 110pt; left: 8pt; right: 8pt; }
.info-tbl { width: 100%; border-collapse: collapse; font-size: 5.2pt; }
.info-tbl td { padding: 1.2pt 0; vertical-align: top; border-bottom: 0.4pt solid #f1f5f9; }
.lbl { color: #475569; font-weight: bold; white-space: nowrap; width: 1%; padding-right: 2pt; }
.colon { color: #475569; font-weight: bold; width: 4pt; text-align: center; padding-right: 4pt; }
.val { color: #0f172a; font-weight: bold; word-break: break-all; }
.add-box { font-size: 4.5pt; color: #334155; margin-top: 2pt; line-height: 1.2; }

.ft {
    position: absolute; bottom: 4pt; left: 8pt; right: 8pt; height: 20pt;
    border-top: 0.4pt solid #e2e8f0; padding-top: 2pt;
}
.ft-left { position: absolute; left: 0; bottom: 0; text-align: left; }
.faculty-badge { font-size: 5.8pt; font-weight: bold; color: #1e3a8a; }
.valid-badge { font-size: 4.2pt; font-weight: bold; color: #475569; margin-top: 1pt; }
.sig-box { position: absolute; right: 0; bottom: 0; text-align: center; }
.sig-lbl { font-size: 4pt; font-weight: bold; color: #475569; margin-top: 1pt; }

/* BACKSIDE (NO QR) */
.back-card {
    width: 153pt; height: 238pt; position: relative; overflow: hidden;
    background: #f8fafc; border-radius: 8pt; border: 0.75pt solid #cbd5e1;
    page-break-inside: avoid;
}
.back-hd {
    height: 32pt; background: {{ $t->header_bg_color ?? '#1e3a8a' }}; color: #ffffff;
    text-align: center; padding-top: 4pt;
}
.back-title { font-size: 7pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5pt; }
.back-sub { font-size: 4.2pt; opacity: 0.85; margin-top: 1pt; }

.crest-box { text-align: center; margin-top: 10pt; }
.crest-icon {
    display: inline-block; width: 36pt; height: 36pt; background: #dbeafe;
    border-radius: 50%; border: 1.5pt solid {{ $t->header_bg_color ?? '#1e3a8a' }};
    line-height: 34pt; text-align: center; color: #1e3a8a; font-size: 16pt; font-weight: bold;
}
.crest-label { font-size: 6pt; font-weight: bold; color: #0f172a; margin-top: 4pt; text-transform: uppercase; letter-spacing: 0.4pt; }
.crest-sub { font-size: 4.2pt; color: #475569; margin-top: 1pt; }

.instructions-box {
    margin: 8pt 8pt 0; background: #ffffff; border-radius: 4pt;
    border: 0.4pt solid #cbd5e1; padding: 6pt 6pt; text-align: left;
}
.inst-item { font-size: 4.2pt; color: #334155; margin-bottom: 3.5pt; line-height: 1.25; }
.inst-item strong { color: #0f172a; }

.back-ft {
    position: absolute; bottom: 4pt; left: 8pt; right: 8pt;
    height: 20pt; text-align: center; border-top: 0.4pt solid #cbd5e1; padding-top: 2pt;
}
.back-emg { font-size: 4.8pt; font-weight: bold; color: #dc2626; }
.back-copy { font-size: 3.8pt; color: #64748b; margin-top: 1pt; }
.cf:after { content:''; display:table; clear:both; }
</style>
</head>
<body>
<div class="page">
  @foreach($teachers as $tCard)
  @php
    $tch         = $tCard['teacher'];
    $empId       = $tch->employee_id ?? ('EMP-' . date('Y') . '-' . str_pad($tch->id, 3, '0', STR_PAD_LEFT));
    $designation = $tch->designation ?: 'Faculty Member';
    $department  = $tch->department ?: 'Academic Faculty';
    $bloodType   = $tch->blood_type ?? 'O+';
    $session     = $schoolSession?->session_name ?? (date('Y') . '-' . (date('Y')+1));
    $dob         = $tch->birthday ? \Carbon\Carbon::parse($tch->birthday)->format('d-m-Y') : '—';
    $contact     = $tch->phone ?? $tch->emergency_contact ?? $phone;
    $teacherAdd  = $tch->address ?: '';
  @endphp

  {{-- FRONT CARD --}}
  <div class="cw">
    <div class="card">
      <div class="hd">
        <div class="hd-curved-bar"></div>
        <div>
          @if($logoSrc)
            <span class="logo-box"><img src="{{ $logoSrc }}" alt=""></span>
          @endif
          <span class="school-title">{{ Str::limit($schoolName, 22) }}</span>
        </div>
        <div class="school-sub">FACULTY IDENTIFICATION CARD</div>
        <div class="school-add">{{ Str::limit($address, 40) }}</div>
        <div class="school-phone">Phone No.: {{ $phone }}</div>
      </div>

      <div class="photo-wrap">
        <img class="photo-img" src="{{ $tCard['photoSrc'] }}" alt="">
        <div class="teacher-name">{{ $tch->full_name }}</div>
        <div class="desig-badge">{{ Str::limit($designation, 25) }}</div>
      </div>

      <div class="info-wrap">
        <table class="info-tbl">
          <tr><td class="lbl">Emp ID</td><td class="colon">:</td><td class="val">{{ $empId }}</td></tr>
          <tr><td class="lbl">Department</td><td class="colon">:</td><td class="val">{{ $department }}</td></tr>
          <tr><td class="lbl">D.O.B.</td><td class="colon">:</td><td class="val">{{ $dob }}</td></tr>
          <tr><td class="lbl">Blood Group</td><td class="colon">:</td><td class="val">{{ $bloodType }}</td></tr>
          <tr><td class="lbl">Contact No.</td><td class="colon">:</td><td class="val">{{ $contact }}</td></tr>
        </table>
        @if($teacherAdd)
        <div class="add-box"><span style="font-weight:bold;color:#475569;">Add.:</span> {{ Str::limit($teacherAdd, 45) }}</div>
        @endif
      </div>

      <div class="ft">
        <div class="ft-left">
          <div class="faculty-badge">Faculty Member</div>
          <div class="valid-badge">Valid Upto: {{ $session }}</div>
        </div>
        <div class="sig-box">
          <svg width="30" height="9" viewBox="0 0 100 30" style="display:block;margin:0 auto;"><path d="M5 25 Q 20 5, 40 20 T 70 10 T 95 25" fill="none" stroke="#1e3a8a" stroke-width="3" /></svg>
          <div class="sig-lbl">Principal Sign.</div>
        </div>
      </div>
    </div>
  </div>

  {{-- BACK CARD (NO QR) --}}
  <div class="cw">
    <div class="back-card">
      <div class="back-hd">
        <div class="back-title">FACULTY TERMS & GUIDELINES</div>
        <div class="back-sub">{{ $schoolName }}</div>
      </div>
      <div class="crest-box">
        <div class="crest-icon">★</div>
        <div class="crest-label">OFFICIAL FACULTY CREDENTIAL</div>
        <div class="crest-sub">Academic Staff Identification</div>
      </div>
      <div class="instructions-box">
        <div class="inst-item"><strong>1. Display:</strong> Must be worn visibly on school premises at all times.</div>
        <div class="inst-item"><strong>2. Ownership:</strong> Official property of {{ Str::limit($schoolName, 20) }}.</div>
        <div class="inst-item"><strong>3. Non-Transferable:</strong> Card is strictly non-transferable.</div>
        <div class="inst-item"><strong>4. Return:</strong> Card must be returned to HR upon completion of service.</div>
      </div>
      <div class="back-ft">
        <div class="back-emg">Emergency Line: {{ $phone }}</div>
        <div class="back-copy">If found, please return to {{ Str::limit($address, 35) }}</div>
      </div>
    </div>
  </div>

  @endforeach
  <div class="cf"></div>
</div>
</body>
</html>
