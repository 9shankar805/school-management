<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: DejaVu Sans, sans-serif; background: #fff; }
  .card {
    width: 242pt; height: 153pt;
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    border-radius: 8pt;
    padding: 12pt;
    color: #fff;
    position: relative;
    overflow: hidden;
  }
  .card::before {
    content: '';
    position: absolute; top: -30pt; right: -30pt;
    width: 100pt; height: 100pt;
    background: rgba(255,255,255,0.07);
    border-radius: 50%;
  }
  .header {
    display: flex; align-items: center;
    border-bottom: 0.5pt solid rgba(255,255,255,0.3);
    padding-bottom: 8pt; margin-bottom: 8pt;
  }
  .school-name { font-size: 9pt; font-weight: bold; letter-spacing: 0.5pt; }
  .school-sub  { font-size: 6.5pt; opacity: 0.8; }
  .body { display: flex; gap: 10pt; }
  .photo {
    width: 52pt; height: 64pt; border-radius: 4pt;
    border: 1.5pt solid rgba(255,255,255,0.5);
    object-fit: cover; background: #a5b4fc;
    flex-shrink: 0;
  }
  .info { flex: 1; }
  .name { font-size: 11pt; font-weight: bold; line-height: 1.2; margin-bottom: 4pt; }
  .row  { font-size: 7pt; opacity: 0.85; margin-bottom: 2pt; }
  .row span { opacity: 0.6; margin-right: 2pt; }
  .footer {
    position: absolute; bottom: 10pt; right: 12pt;
    display: flex; flex-direction: column; align-items: center; gap: 2pt;
  }
  .footer img { width: 44pt; height: 44pt; background: #fff; padding: 2pt; border-radius: 3pt; }
  .id-no {
    position: absolute; bottom: 10pt; left: 12pt;
    font-size: 7pt; opacity: 0.7; letter-spacing: 0.5pt;
  }
  .badge {
    display: inline-block; background: rgba(255,255,255,0.2);
    border-radius: 10pt; padding: 1pt 6pt; font-size: 6.5pt;
    margin-top: 4pt; letter-spacing: 0.3pt;
  }
</style>
</head>
<body>
<div class="card">
  <div class="header">
    <div>
      <div class="school-name">{{ config('app.name', 'School Management') }}</div>
      <div class="school-sub">STUDENT IDENTIFICATION CARD</div>
    </div>
  </div>

  <div class="body">
    <img class="photo"
         src="{{ $student->photo ? public_path('storage/'.$student->photo) : public_path('imgs/profile.png') }}"
         alt="">
    <div class="info">
      <div class="name">{{ $student->full_name }}</div>
      <div class="row"><span>Class:</span> {{ $promotion?->schoolClass?->class_name ?? '—' }}</div>
      <div class="row"><span>Section:</span> {{ $promotion?->section?->section_name ?? '—' }}</div>
      <div class="row"><span>Gender:</span> {{ $student->gender ?? '—' }}</div>
      <div class="row"><span>Blood:</span> {{ $student->blood_type ?? '—' }}</div>
      <div class="row"><span>Phone:</span> {{ $student->phone ?? '—' }}</div>
      <div class="badge">STUDENT</div>
    </div>
  </div>

  <div class="id-no">ID: {{ $promotion?->id_card_number ?? 'N/A' }}</div>

  <div class="footer">
    <img src="{{ $qrUrl }}" alt="QR">
  </div>
</div>
</body>
</html>
