<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: DejaVu Sans, sans-serif; }
  .grid { display: flex; flex-wrap: wrap; gap: 12pt; padding: 12pt; }
  .card {
    width: 242pt; height: 153pt;
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    border-radius: 8pt; padding: 12pt; color: #fff;
    position: relative; overflow: hidden; page-break-inside: avoid;
  }
  .card::before {
    content: '';
    position: absolute; top: -30pt; right: -30pt;
    width: 100pt; height: 100pt;
    background: rgba(255,255,255,0.07); border-radius: 50%;
  }
  .school-name { font-size: 9pt; font-weight: bold; border-bottom: 0.5pt solid rgba(255,255,255,0.3); padding-bottom: 6pt; margin-bottom: 7pt; }
  .body { display: flex; gap: 10pt; }
  .photo {
    width: 52pt; height: 64pt; border-radius: 4pt;
    border: 1.5pt solid rgba(255,255,255,0.5);
    object-fit: cover; background: #a5b4fc; flex-shrink: 0;
  }
  .info { flex: 1; }
  .name { font-size: 10pt; font-weight: bold; margin-bottom: 4pt; line-height: 1.2; }
  .row  { font-size: 7pt; opacity: 0.85; margin-bottom: 2pt; }
  .row span { opacity: 0.6; margin-right: 2pt; }
  .qr {
    position: absolute; bottom: 8pt; right: 10pt;
    width: 40pt; height: 40pt; background: #fff; padding: 2pt; border-radius: 2pt;
  }
  .id-no { position: absolute; bottom: 10pt; left: 12pt; font-size: 7pt; opacity: 0.7; }
</style>
</head>
<body>
<div class="grid">
  @foreach($students as $s)
  <div class="card">
    <div class="school-name">{{ config('app.name') }} · STUDENT ID</div>
    <div class="body">
      <img class="photo"
           src="{{ $s['student']->photo ? public_path('storage/'.$s['student']->photo) : public_path('imgs/profile.png') }}"
           alt="">
      <div class="info">
        <div class="name">{{ $s['student']->full_name }}</div>
        <div class="row"><span>Class:</span> {{ $s['promotion']->schoolClass?->class_name ?? '—' }}</div>
        <div class="row"><span>Section:</span> {{ $s['promotion']->section?->section_name ?? '—' }}</div>
        <div class="row"><span>Blood:</span> {{ $s['student']->blood_type ?? '—' }}</div>
      </div>
    </div>
    <div class="id-no">ID: {{ $s['promotion']->id_card_number ?? 'N/A' }}</div>
    <img class="qr" src="{{ $s['qrUrl'] }}" alt="QR">
  </div>
  @endforeach
</div>
</body>
</html>
