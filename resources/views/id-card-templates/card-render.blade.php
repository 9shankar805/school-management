@php
/*
 * Self-contained card renderer.
 * Works in TWO contexts:
 *   1) Browser HTML preview  — uses px, all CSS inlined here
 *   2) DomPDF generation     — caller passes $pdfMode=true, uses pt units
 *
 * Required vars:  $t (IdCardTemplate), $school, $dummyData, optional $logoSrc
 * Optional:       $pdfMode (bool, default false)
 */

$pdfMode = $pdfMode ?? false;
$u       = $pdfMode ? 'pt' : 'px';   // unit suffix

// ── Scale factor: 1pt ≈ 1.3333px ──────────────────────────────────
$scale = $pdfMode ? 1 : 1.3333;
$px = fn(int $pt) => $pdfMode ? "{$pt}pt" : round($pt * $scale) . 'px';

// ── School logo ────────────────────────────────────────────────────
if (!isset($logoSrc)) {
    $logoSrc = '';
    if ($school?->logo) {
        $p = storage_path('app/public/' . ltrim($school->logo, '/'));
        if (file_exists($p)) {
            $m = mime_content_type($p) ?: 'image/jpeg';
            $logoSrc = 'data:' . $m . ';base64,' . base64_encode(file_get_contents($p));
        }
    }
}

$schoolName = $school?->name ?? config('app.name', 'School');

// ── Derived measurements ───────────────────────────────────────────
$hdrH      = (int) $t->header_height;    // pt
$ftrH      = 28;                          // pt
$photoSz   = (int) $t->photo_size;       // pt
$photoColW = $photoSz + 16;              // pt
$isCircle  = ($t->photo_shape === 'circle');
$photoRadius = $isCircle ? '50%' : $px(3);
$photoTop    = 8;                         // pt, fixed top offset for photo

// Hex → rgba helper
$hex2rgb = function (string $hex): array {
    $hex = ltrim($hex, '#');
    return [hexdec(substr($hex,0,2)), hexdec(substr($hex,2,2)), hexdec(substr($hex,4,2))];
};
[$r,$g,$b]    = $hex2rgb($t->header_bg_color);
$primaryLight = "rgba({$r},{$g},{$b},0.12)";

$fields        = $t->visible_fields ?? \App\Models\IdCardTemplate::DEFAULT_FIELDS;
$allFieldLabels = \App\Models\IdCardTemplate::ALL_FIELDS;

// Card dimensions
$cardW = 242; $cardH = 153; // pt
@endphp

{{-- Scoped wrapper so CSS doesn't leak into the page --}}
<div class="idc-root" style="
  display:inline-block;
  width:{{ $px($cardW) }};
  height:{{ $px($cardH) }};
  position:relative;
">

<style>
/* ── Scoped to .idc-root ───────────────────────────────────── */
.idc-root { font-family: Arial, DejaVu Sans, sans-serif; }

.idc-root * { box-sizing: border-box; margin: 0; padding: 0; }

.idc-card {
  width:  {{ $px($cardW) }};
  height: {{ $px($cardH) }};
  position: relative;
  overflow: hidden;
  background: {{ $t->body_bg_color }};
  border-radius: {{ $px(10) }};
  border: 1px solid #d0d5dd;
}

/* ── HEADER ────────────────────────────────── */
.idc-hd {
  position: absolute;
  top: 0; left: 0; right: 0;
  height: {{ $px($hdrH) }};
  background: {{ $t->header_bg_color }};
  overflow: hidden;
}
.idc-hd-r1 {
  position: absolute;
  width: {{ $px(56) }}; height: {{ $px(56) }};
  border-radius: 50%;
  border: {{ $px(9) }} solid rgba(255,255,255,0.09);
  top: {{ $px(-14) }}; right: {{ $px(-8) }};
}
.idc-hd-r2 {
  position: absolute;
  width: {{ $px(28) }}; height: {{ $px(28) }};
  border-radius: 50%;
  border: {{ $px(5) }} solid rgba(255,255,255,0.07);
  top: {{ $px(18) }}; right: {{ $px(30) }};
}
.idc-hd-d1 {
  position: absolute; width: {{ $px(6) }}; height: {{ $px(6) }};
  border-radius: 50%; background: rgba(255,255,255,0.12);
  bottom: {{ $px(8) }}; left: {{ $px(14) }};
}
.idc-hd-d2 {
  position: absolute; width: {{ $px(4) }}; height: {{ $px(4) }};
  border-radius: 50%; background: rgba(255,255,255,0.08);
  bottom: {{ $px(18) }}; left: {{ $px(26) }};
}
.idc-hd-inner {
  position: absolute;
  top: 0; left: 0; right: 0; bottom: {{ $px(2) }};
  display: flex;
  align-items: center;
  padding: 0 {{ $px(12) }};
  gap: {{ $px(8) }};
}
.idc-logo-ring {
  width: {{ $px(26) }}; height: {{ $px(26) }};
  border-radius: 50%;
  background: rgba(255,255,255,0.18);
  border: {{ $px(2) }} solid rgba(255,255,255,0.40);
  overflow: hidden;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}
.idc-logo-ring img {
  width: 100%; height: 100%;
  object-fit: contain;
  display: block;
}
.idc-logo-initials {
  font-size: {{ $px(9) }};
  font-weight: 700;
  color: #fff;
}
.idc-hd-textcol {
  display: flex;
  flex-direction: column;
  min-width: 0;
  gap: {{ $px(1) }};
}
.idc-school-name {
  font-size: {{ $px(9) }};
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  line-height: 1.15;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.idc-school-sub {
  font-size: {{ $px(5) }};
  letter-spacing: 1.4px;
  text-transform: uppercase;
  opacity: 0.65;
}
.idc-hd-line {
  position: absolute; bottom: 0; left: 0; right: 0;
  height: {{ $px(3) }};
  background: {{ $t->accent_color }};
}

/* ── BODY ──────────────────────────────────── */
.idc-body {
  position: absolute;
  top: {{ $px($hdrH) }};
  left: 0; right: 0;
  bottom: {{ $px($ftrH) }};
  overflow: hidden;
}

/* Photo column */
.idc-photo-col {
  position: absolute;
  top: 0; left: 0; bottom: 0;
  width: {{ $px($photoColW) }};
  overflow: hidden;
}
.idc-photo-bg {
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  background: {{ $primaryLight }};
}
.idc-photo-frame {
  position: absolute;
  top: {{ $px($photoTop) }};
  left: {{ $px(8) }};
  width: {{ $px($photoSz) }};
  height: {{ $px($photoSz) }};
}
.idc-pf-tl {
  position: absolute; top: 0; left: 0;
  width: {{ $px(10) }}; height: {{ $px(10) }};
  border-top: {{ $px(2) }} solid {{ $t->accent_color }};
  border-left: {{ $px(2) }} solid {{ $t->accent_color }};
}
.idc-pf-tr {
  position: absolute; top: 0; right: 0;
  width: {{ $px(10) }}; height: {{ $px(10) }};
  border-top: {{ $px(2) }} solid {{ $t->accent_color }};
  border-right: {{ $px(2) }} solid {{ $t->accent_color }};
}
.idc-pf-bl {
  position: absolute; bottom: 0; left: 0;
  width: {{ $px(10) }}; height: {{ $px(10) }};
  border-bottom: {{ $px(2) }} solid {{ $t->accent_color }};
  border-left: {{ $px(2) }} solid {{ $t->accent_color }};
}
.idc-pf-br {
  position: absolute; bottom: 0; right: 0;
  width: {{ $px(10) }}; height: {{ $px(10) }};
  border-bottom: {{ $px(2) }} solid {{ $t->accent_color }};
  border-right: {{ $px(2) }} solid {{ $t->accent_color }};
}
.idc-pf-circle-ring {
  position: absolute;
  border-radius: 50%;
  border: {{ $px(2) }} solid {{ $t->accent_color }};
}
.idc-photo-img {
  position: absolute;
  top: {{ $px(3) }}; left: {{ $px(3) }};
  width: {{ $px($photoSz - 6) }};
  height: {{ $px($photoSz - 6) }};
  border-radius: {{ $photoRadius }};
  object-fit: cover;
  background: #e2e8f0;
  display: block;
}
.idc-blood-wrap {
  position: absolute;
  bottom: {{ $px(6) }};
  left: 0; right: 0;
  text-align: center;
}
.idc-blood-box {
  display: inline-block;
  background: {{ $t->header_bg_color }};
  border-radius: {{ $px(3) }};
  padding: {{ $px(2) }} {{ $px(8) }};
}
.idc-bl-lbl {
  font-size: {{ $px(5) }};
  color: rgba(255,255,255,0.70);
  letter-spacing: 0.8px;
  text-transform: uppercase;
  display: block;
}
.idc-bl-val {
  font-size: {{ $px(10) }};
  font-weight: 700;
  color: #fff;
  display: block;
  line-height: 1.1;
}

/* Info column */
.idc-info-col {
  position: absolute;
  top: 0; bottom: 0; right: 0;
  left: {{ $t->show_photo ? $px($photoColW) : '0' }};
  padding: {{ $px(8) }} {{ $px(10) }} {{ $px(6) }} {{ $px(10) }};
  overflow: hidden;
}
.idc-s-name {
  font-size: {{ $px($t->name_font_size) }};
  font-weight: 700;
  color: {{ $t->name_text_color }};
  {{ $t->name_uppercase ? 'text-transform:uppercase;' : '' }}
  letter-spacing: 0.3px;
  line-height: 1.2;
  margin-bottom: {{ $px(1) }};
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.idc-role-chip {
  display: inline-block;
  background: {{ $t->header_bg_color }};
  color: #fff;
  font-size: {{ $px(5) }};
  font-weight: 700;
  letter-spacing: 0.8px;
  text-transform: uppercase;
  padding: {{ $px(1) }} {{ $px(6) }};
  border-radius: {{ $px(20) }};
  margin-bottom: {{ $px(5) }};
}
.idc-divider {
  height: 1px;
  background: #e2e8f0;
  margin-bottom: {{ $px(5) }};
}
.idc-info-tbl {
  width: 100%;
  border-collapse: collapse;
}
.idc-info-tbl td {
  padding: 0 0 {{ $px(3) }} 0;
  vertical-align: top;
  font-size: {{ $px(7) }};
}
.idc-lbl {
  width: {{ $px(38) }};
  color: #94a3b8;
  font-size: {{ $px(6) }};
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.2px;
  padding-right: {{ $px(4) }};
  white-space: nowrap;
}
.idc-val {
  font-weight: 600;
  font-size: {{ $px(7) }};
  color: {{ $t->footer_text_color }};
}

/* ── FOOTER ────────────────────────────────── */
.idc-footer {
  position: absolute;
  bottom: 0; left: 0; right: 0;
  height: {{ $px($ftrH) }};
  background: {{ $t->footer_bg_color }};
  border-top: 1px solid #e2e8f0;
  overflow: hidden;
}
.idc-ft-band {
  position: absolute; top: 0; left: 0; right: 0;
  height: {{ $px(2) }};
  background: {{ $t->header_bg_color }};
  opacity: 0.45;
}
.idc-ft-id {
  position: absolute;
  left: {{ $px(12) }}; top: 0; bottom: 0;
  display: flex; align-items: center;
}
.idc-ft-id-lbl {
  font-size: {{ $px(5) }};
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  font-weight: 700;
}
.idc-ft-id-val {
  font-size: {{ $px(9) }};
  font-weight: 700;
  letter-spacing: 0.5px;
  font-family: monospace;
  color: {{ $t->footer_text_color }};
}
.idc-ft-center {
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  pointer-events: none;
}
.idc-ft-sess-lbl { font-size: {{ $px(5) }}; color: #cbd5e1; text-transform: uppercase; }
.idc-ft-sess-val { font-size: {{ $px(7) }}; font-weight: 700; color: #64748b; }
.idc-ft-ctext    { font-size: {{ $px(6) }}; color: #64748b; font-weight: 600; }
.idc-ft-qr {
  position: absolute;
  right: {{ $px(10) }}; top: {{ $px(5) }};
  width: {{ $px(18) }}; height: {{ $px(18) }};
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: {{ $px(2) }};
  padding: {{ $px(1) }};
}
.idc-ft-qr img { width: 100%; height: 100%; display: block; }
.idc-sig-line {
  position: absolute;
  right: {{ $px(38) }}; bottom: {{ $px(4) }};
  border-top: 1px solid #94a3b8;
  width: {{ $px(48) }};
  text-align: center;
}
.idc-sig-lbl { font-size: {{ $px(5) }}; color: #94a3b8; margin-top: {{ $px(1) }}; }
.idc-ft-left-text {
  position: absolute;
  left: 0; right: 0; bottom: 0;
  font-size: {{ $px(5) }}; color: #94a3b8;
  text-align: center; padding-bottom: {{ $px(2) }};
}
</style>

<div class="idc-card">

  {{-- HEADER --}}
  <div class="idc-hd">
    <div class="idc-hd-r1"></div>
    <div class="idc-hd-r2"></div>
    <div class="idc-hd-d1"></div>
    <div class="idc-hd-d2"></div>
    <div class="idc-hd-inner">
      @if($t->show_logo)
      <div class="idc-logo-ring">
        @if($logoSrc)
          <img src="{{ $logoSrc }}" alt="">
        @else
          <span class="idc-logo-initials">{{ strtoupper(substr($schoolName,0,2)) }}</span>
        @endif
      </div>
      @endif
      <div class="idc-hd-textcol">
        @if($t->show_school_name)
        <div class="idc-school-name" style="color:{{ $t->header_text_color }};">{{ $schoolName }}</div>
        @endif
        @if($t->show_tagline)
        <div class="idc-school-sub" style="color:{{ $t->header_text_color }};">{{ $t->tagline_text }}</div>
        @endif
      </div>
    </div>
    <div class="idc-hd-line"></div>
  </div>

  {{-- BODY --}}
  <div class="idc-body">

    @if($t->show_photo)
    <div class="idc-photo-col">
      <div class="idc-photo-bg"></div>
      <div class="idc-photo-frame">
        @if($t->show_photo_corner_marks && !$isCircle)
          <div class="idc-pf-tl"></div>
          <div class="idc-pf-tr"></div>
          <div class="idc-pf-bl"></div>
          <div class="idc-pf-br"></div>
        @elseif($isCircle)
          <div class="idc-pf-circle-ring"
            style="width:{{ $px($photoSz+6) }};height:{{ $px($photoSz+6) }};top:{{ $px(-3) }};left:{{ $px(-3) }};"></div>
        @endif
        <img class="idc-photo-img" src="{{ $dummyData['photoSrc'] }}" alt="">
      </div>
      @if($t->show_blood_type)
      <div class="idc-blood-wrap">
        <div class="idc-blood-box">
          <span class="idc-bl-lbl">Blood</span>
          <span class="idc-bl-val">{{ $dummyData['blood_type'] }}</span>
        </div>
      </div>
      @endif
    </div>
    @endif

    <div class="idc-info-col">
      <div class="idc-s-name">{{ $dummyData['name'] }}</div>
      @if($t->show_role_badge)
        <span class="idc-role-chip">Student</span>
      @endif
      <div class="idc-divider"></div>
      <table class="idc-info-tbl">
        @foreach($fields as $fk)
          @if(isset($dummyData[$fk]))
          <tr>
            <td class="idc-lbl">{{ $allFieldLabels[$fk] ?? $fk }}</td>
            <td class="idc-val">{{ $dummyData[$fk] }}</td>
          </tr>
          @endif
        @endforeach
      </table>
    </div>

  </div>

  {{-- FOOTER --}}
  <div class="idc-footer">
    <div class="idc-ft-band"></div>
    @if($t->show_id_number)
    <div class="idc-ft-id">
      <div>
        <div class="idc-ft-id-lbl">ID No.</div>
        <div class="idc-ft-id-val">{{ $dummyData['roll_no'] }}</div>
      </div>
    </div>
    @endif
    @if($t->show_session || $t->footer_center_text)
    <div class="idc-ft-center">
      @if($t->footer_center_text)
        <div class="idc-ft-ctext">{{ $t->footer_center_text }}</div>
      @elseif($t->show_session)
        <div style="text-align:center;">
          <div class="idc-ft-sess-lbl">Session</div>
          <div class="idc-ft-sess-val">{{ $dummyData['session'] }}</div>
        </div>
      @endif
    </div>
    @endif
    @if($t->show_signature_line)
    <div class="idc-sig-line">
      <div class="idc-sig-lbl">{{ $t->signature_label ?? 'Principal' }}</div>
    </div>
    @endif
    @if($t->show_qr)
    <div class="idc-ft-qr">
      <img src="{{ $dummyData['qrSrc'] }}" alt="QR">
    </div>
    @endif
    @if($t->footer_left_text)
    <div class="idc-ft-left-text">{{ $t->footer_left_text }}</div>
    @endif
  </div>

</div>{{-- /idc-card --}}
</div>{{-- /idc-root --}}
