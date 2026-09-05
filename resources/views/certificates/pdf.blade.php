<!DOCTYPE html>
<html lang="{{ $issued->language ?? 'en' }}">
<head>
<meta charset="UTF-8">
<style>
@page {
    /* DomPDF @page margins define the content box.
       All decorative chrome (borders, bars) is drawn
       inside this area using position:fixed.
       We give 0 margin so position:fixed elements
       can reach the physical page edges. */
    margin: 0mm;
    padding: 0mm;
}

* { margin:0; padding:0; box-sizing:border-box; }

html, body {
    font-family: 'DejaVu Serif', Georgia, serif;
    background: #fff;
    color: #1e293b;
    font-size: 10pt;
    margin: 0;
    padding: 0;
}

/* ═══════════════════════════════════════════
   BUILDER MODE
═══════════════════════════════════════════ */
.builder-page { width:100%; position:relative; padding:0; overflow:hidden; }
.builder-bg   { position:absolute; top:0; left:0; width:100%; height:100%; z-index:0; }
.builder-el   { position:absolute; z-index:2; word-break:break-word; line-height:1.25; overflow:hidden; }
.builder-el img { display:block; max-width:100%; max-height:100%; width:100%; height:100%; object-fit:contain; }
.builder-el .builder-line { display:block; width:100%; height:100%; }

/* ═══════════════════════════════════════════
   DECORATIVE CHROME  (position:fixed keeps
   them on the page without affecting flow)
═══════════════════════════════════════════ */

/* Top gradient bar */
.deco-top {
    position: fixed;
    top: 0; left: 0; right: 0;
    height: 5mm;
    z-index: 20;
}
/* Bottom solid bar */
.deco-bottom {
    position: fixed;
    bottom: 0; left: 0; right: 0;
    height: 2.5mm;
    z-index: 20;
}
/* Outer border frame */
.deco-outer {
    position: fixed;
    top: 6.5mm; left: 6.5mm; right: 6.5mm; bottom: 3.5mm;
    z-index: 10;
}
/* Inner border frame */
.deco-inner {
    position: fixed;
    top: 9.5mm; left: 9.5mm; right: 9.5mm; bottom: 6mm;
    z-index: 10;
}
/* Corner L-brackets */
.deco-corner {
    position: fixed;
    width: 12mm; height: 12mm;
    z-index: 15;
    border-style: solid;
}
.deco-corner.tl { top: 5mm;    left: 5mm;   border-width: 2pt 0 0 2pt; }
.deco-corner.tr { top: 5mm;    right: 5mm;  border-width: 2pt 2pt 0 0; }
.deco-corner.bl { bottom: 2mm; left: 5mm;   border-width: 0 0 2pt 2pt; }
.deco-corner.br { bottom: 2mm; right: 5mm;  border-width: 0 2pt 2pt 0; }
/* Modern side bars */
.deco-side-l {
    position: fixed;
    top: 5mm; bottom: 2.5mm; left: 0;
    width: 2.5mm;
    z-index: 10;
}
.deco-side-r {
    position: fixed;
    top: 5mm; bottom: 2.5mm; right: 0;
    width: 2.5mm;
    z-index: 10;
}
/* Diagonal watermark */
.deco-watermark {
    position: fixed;
    top: 30mm; left: 0; right: 0;
    text-align: center;
    font-size: 54pt; font-weight: 900;
    letter-spacing: 5pt; text-transform: uppercase;
    transform: rotate(-28deg);
    z-index: 0;
    white-space: nowrap;
}
/* Background image */
.deco-bg {
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    z-index: 0;
    object-fit: cover;
}
/* Revoked stamp */
.deco-revoked {
    position: fixed;
    top: 35%; left: 50%;
    transform: translate(-50%, -50%) rotate(-35deg);
    font-size: 48pt; font-weight: bold;
    color: rgba(239,68,68,0.13);
    text-transform: uppercase; letter-spacing: 6pt;
    z-index: 999; white-space: nowrap;
}

/* ═══════════════════════════════════════════
   CONTENT WRAPPER
   CRITICAL DomPDF behaviour: padding on a
   width:100% block is ADDED to the width,
   not subtracted. Use margin-left/right instead
   so DomPDF shrinks the block correctly.
   margin-left/right: 14mm each side keeps text
   safely inside the inner chrome border (9.5mm).
═══════════════════════════════════════════ */
.content-wrap {
    position: relative;
    z-index: 5;
    /* margin shrinks the block — padding would expand it */
    margin-top:    12mm;
    margin-bottom: 8mm;
    margin-left:   14mm;
    margin-right:  14mm;
}

/* ═══════════════════════════════════════════
   HEADER  (3-col table: logo | info | photo)
   No width:100% + padding — use width:100% only
   on the table (no padding on it, padding on cells)
═══════════════════════════════════════════ */
.hdr        { display: table; width: 100%; table-layout: fixed; margin-bottom: 4mm; padding-bottom: 3mm; }
.hdr-logo   { display: table-cell; width: 18mm; vertical-align: middle; overflow: hidden; padding-right: 2mm; }
.hdr-logo img { max-width: 17mm; max-height: 17mm; display: block; }
.hdr-center { display: table-cell; text-align: center; vertical-align: middle; padding: 0 2mm; word-break: break-word; overflow: hidden; }
.hdr-photo  { display: table-cell; width: 18mm; vertical-align: middle; text-align: right; overflow: hidden; padding-left: 2mm; }
.hdr-photo img { width: 17mm; height: 21mm; object-fit: cover; border-radius: 1mm; display: block; margin-left: auto; }

.school-name    { font-size: 12pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1.5pt; line-height: 1.2; }
.school-address { font-size: 7pt; color: #64748b; margin-top: 1mm; }

/* ═══════════════════════════════════════════
   TITLE BLOCK
═══════════════════════════════════════════ */
.of-label   { text-align: center; font-size: 7pt; letter-spacing: 4pt; text-transform: uppercase; font-style: italic; margin: 3mm 0 1.5mm; }
.cert-title { text-align: center; font-size: 20pt; font-weight: bold; letter-spacing: 2pt; text-transform: uppercase; line-height: 1.1; word-break: break-word; }
.cert-sub   { text-align: center; font-size: 6.5pt; letter-spacing: 3pt; text-transform: uppercase; color: #64748b; margin-top: 1mm; }

/* ═══════════════════════════════════════════
   ORNAMENTAL DIVIDER
═══════════════════════════════════════════ */
.orn        { display: table; width: 100%; table-layout: fixed; margin: 2.5mm 0; }
.orn-line   { display: table-cell; vertical-align: middle; }
.orn-rule   { height: 1pt; width: 100%; }
.orn-star   { display: table-cell; vertical-align: middle; text-align: center; width: 20mm; font-size: 9pt; white-space: nowrap; }

/* ═══════════════════════════════════════════
   PREAMBLE + NAME
═══════════════════════════════════════════ */
.preamble   { text-align: center; font-size: 8pt; color: #475569; letter-spacing: 2.5pt; text-transform: uppercase; font-style: italic; margin-bottom: 2mm; }
.student-nm { text-align: center; font-size: 22pt; font-weight: bold; line-height: 1.2; word-break: break-word; margin-bottom: 1mm; }
.name-rule  { width: 80mm; display: block; margin: 0 auto 3mm; }

/* ═══════════════════════════════════════════
   BODY TEXT  — center-aligned (safe in DomPDF)
═══════════════════════════════════════════ */
.body-text  { font-size: 9pt; color: #374151; line-height: 1.8; text-align: center; word-break: break-word; margin-bottom: 3mm; }

/* ═══════════════════════════════════════════
   META ROW  — real HTML table, not CSS table
   Avoids the DomPDF cellmap bug entirely.
═══════════════════════════════════════════ */
table.meta-tbl    { width: 100%; border-collapse: collapse; margin: 2mm 0; }
table.meta-tbl td { text-align: center; vertical-align: middle; overflow: hidden; word-break: break-word; font-size: 8pt; padding: 2mm 0; }
table.meta-tbl td.sep { width: 1pt; background: #e2e8f0; padding: 0; }
.ml { font-size: 5.5pt; color: #94a3b8; text-transform: uppercase; letter-spacing: 1.5pt; display: block; margin-bottom: 0.5mm; }
.mv { font-size: 8pt; font-weight: bold; color: #0f172a; display: block; }

/* ═══════════════════════════════════════════
   FOOTER CAPTION
═══════════════════════════════════════════ */
.foot-cap   { text-align: center; font-size: 7pt; color: #64748b; font-style: italic; margin-bottom: 3mm; }

/* ═══════════════════════════════════════════
   SIGNATURES ROW
═══════════════════════════════════════════ */
.sigs-wrap  { }
.sigs-rule  { height: 0.5pt; margin-bottom: 4mm; }

.sig-img    { max-height: 11mm; max-width: 28mm; display: block; margin: 0 auto 1mm; object-fit: contain; }
.sig-ln     { display: block; margin: 0 auto 1.5mm; height: 0; }
.sig-name   { font-size: 8pt; font-weight: bold; color: #0f172a; word-break: break-word; }
.sig-title  { font-size: 7pt; color: #64748b; }

.dt-label   { font-size: 5.5pt; color: #94a3b8; text-transform: uppercase; letter-spacing: 1pt; display: block; }
.dt-value   { font-size: 8pt; font-weight: bold; color: #0f172a; margin-top: 0.5mm; display: block; }
.dt-purpose { font-size: 6.5pt; color: #94a3b8; margin-top: 0.5mm; display: block; }

.qr-img     { width: 16mm; height: 16mm; display: block; margin: 0 0 1mm auto; }
.cn-label   { font-size: 5.5pt; color: #cbd5e1; text-transform: uppercase; letter-spacing: 1pt; display: block; }
.cn-value   { font-family: 'DejaVu Sans Mono', monospace; font-size: 5.5pt; color: #94a3b8; word-break: break-all; display: block; }
</style>
</head>
<body>

@php
/* ── Data ── */
$promotion   = $student?->promotions ? $student->promotions->sortByDesc('id')->first() : null;
$academic    = $student?->academic_info;
$logoUrl     = $template->logo_path             ? Storage::disk('public')->url($template->logo_path)             : null;
$sigImgUrl   = $template->signature_image_path  ? Storage::disk('public')->url($template->signature_image_path)  : null;
$bgImgUrl    = $template->background_image_path ? Storage::disk('public')->url($template->background_image_path) : null;
$photoUrl    = ($student && !empty($student->photo)) ? Storage::disk('public')->url($student->photo)              : null;

$admissionNo = $academic?->admission_no ?? $promotion?->id_card_number ?? '';
$classStr    = $promotion?->schoolClass?->class_name ?? '';
if ($classStr && ($promotion?->section?->section_name ?? ''))
    $classStr .= ' – ' . $promotion->section->section_name;
$fullName    = $student?->full_name ?? trim(($student?->first_name ?? '').' '.($student?->last_name ?? ''));
$birthDt     = $academic?->date_of_birth ? \Carbon\Carbon::parse($academic->date_of_birth) : null;
$academicYr  = $promotion?->session?->name ?? '';
$issuedDate  = (isset($issued) && $issued->issued_date) ? \Carbon\Carbon::parse($issued->issued_date) : now();
$purpose     = isset($issued) ? ($issued->purpose    ?? '') : '';
$extraNotes  = isset($issued) ? ($issued->extra_notes ?? '') : '';
$certNo      = isset($issued) ? $issued->certificate_number : 'CERT—PREVIEW';
$qrUrl       = isset($issued) ? $issued->qrCodeUrl() : '';
$issuerName  = '';
if (isset($issued) && !empty($issued->issuer))
    $issuerName = $issued->issuer->full_name ?? (($issued->issuer->first_name ?? '').' '.($issued->issuer->last_name ?? ''));
$parentInfo  = $student?->parent_info ?? null;

$tokenMap = [
    '{{student_name}}'  => $fullName,
    '{{first_name}}'    => $student?->first_name ?? '',
    '{{last_name}}'     => $student?->last_name  ?? '',
    '{{class}}'         => $classStr,
    '{{section}}'       => $promotion?->section?->section_name ?? '',
    '{{roll_no}}'       => $promotion?->roll_no ?? '',
    '{{admission_no}}'  => $admissionNo,
    '{{board_reg_no}}'  => $academic?->board_reg_no ?? '',
    '{{academic_year}}' => $academicYr,
    '{{birthday}}'      => $birthDt ? $birthDt->format('F j, Y') : '',
    '{{age}}'           => $birthDt ? $birthDt->age : '',
    '{{gender}}'        => ucfirst($student?->gender ?? ''),
    '{{father_name}}'   => $parentInfo?->father_name ?? '',
    '{{mother_name}}'   => $parentInfo?->mother_name ?? '',
    '{{address}}'       => $student?->address ?? '',
    '{{date}}'          => $issuedDate?->format('F j, Y'),
    '{{school_name}}'   => config('app.name'),
    '{{issued_by}}'     => $issuerName,
    '{{cert_no}}'       => $certNo,
    '{{purpose}}'       => $purpose,
    '{{extra_notes}}'   => $extraNotes,
    '{{character}}'     => 'good',
];
$bodyText = str_replace(array_keys($tokenMap), array_values($tokenMap), $bodyText);
$footerCaption = $template->footer_text
    ? str_replace(array_keys($tokenMap), array_values($tokenMap), $template->footer_text)
    : '';

$overrides = [];
if (isset($issued) && !empty($issued->field_overrides))
    $overrides = is_array($issued->field_overrides)
        ? $issued->field_overrides
        : (json_decode($issued->field_overrides, true) ?? []);

if (!function_exists('resolveField')) {
    function resolveField($key, $ctx, $overrides) {
        if (is_array($overrides) && isset($overrides[$key]) && strlen($overrides[$key])) return $overrides[$key];
        extract($ctx);
        switch ($key) {
            case 'student_name':  return $fullName;
            case 'first_name':    return $ctx['student']->first_name ?? '';
            case 'last_name':     return $ctx['student']->last_name  ?? '';
            case 'class':         return $classStr;
            case 'section':       return $ctx['promotion']?->section?->section_name ?? '';
            case 'roll_no':       return $ctx['promotion']?->roll_no ?? '';
            case 'admission_no':  return $admissionNo;
            case 'board_reg_no':  return $ctx['academic']?->board_reg_no ?? '';
            case 'academic_year': return $academicYr;
            case 'birthday':      return $birthDt ? $birthDt->format('F j, Y') : '';
            case 'age':           return $birthDt ? $birthDt->age : '';
            case 'gender':        return ucfirst($ctx['student']->gender ?? '');
            case 'father_name':   return $ctx['parentInfo']?->father_name ?? '';
            case 'mother_name':   return $ctx['parentInfo']?->mother_name ?? '';
            case 'address':       return $ctx['student']->address ?? '';
            case 'date':          return $issuedDate?->format('F j, Y');
            case 'school_name':   return config('app.name');
            case 'issued_by':     return $issuerName;
            case 'cert_no':       return $certNo;
            case 'purpose':       return $purpose;
            case 'extra_notes':   return $extraNotes;
            case 'custom_1': case 'custom_2': case 'custom_3': return $overrides[$key] ?? '';
            default: return '';
        }
    }
}
$ctx = compact('student','promotion','academic','parentInfo','classStr','admissionNo',
               'fullName','birthDt','academicYr','issuedDate','purpose','extraNotes','certNo','issuerName');

$pc  = '#1a3a6b';
$sc  = '#b8953e';
$bs  = 'ornate';

/* ── Builder mode ── */
$useBuilder  = $template->builder_mode && !empty($template->builder_layout);
$pageWmm     = $template->paperWidthMm();
$pageHmm     = $template->paperHeightMm();
$builderEls  = [];
if ($useBuilder) {
    $defSize = function($el) use ($pageWmm,$pageHmm){
        $t=(string)($el['type']??'text');$f=(float)($el['fontSize']??12);
        if($t==='line')  return [150,max(1,(float)($el['border']??1)/3.78)];
        if($t==='qr')    return [32,32];
        if($t==='student_photo') return [32,44];
        if($t==='image') return [42,28];
        return [max(30,$pageWmm-40), max(5,$f*0.45+2)];
    };
    foreach(collect($template->builder_layout)->sortBy('zIndex') as $el){
        [$dw,$dh]=$defSize($el);
        $w=isset($el['w'])&&$el['w']!==null?(float)$el['w']:$dw;
        $h=isset($el['h'])&&$el['h']!==null?(float)$el['h']:$dh;
        $x=max(0,(float)($el['x']??0));$y=max(0,(float)($el['y']??0));
        if($w>$pageWmm)$w=$pageWmm;if($h>$pageHmm)$h=$pageHmm;
        if($x+$w>$pageWmm)$x=max(0,$pageWmm-$w);
        if($y+$h>$pageHmm)$y=max(0,$pageHmm-$h);
        $el['x']=$x;$el['y']=$y;$el['w']=$w;$el['h']=$h;
        $builderEls[]=$el;
    }
}

/* ── School address ── */
try {
    $school = \App\Models\School::find(config('tenant.school_id'));
    $schoolAddr = trim(implode(', ', array_filter([$school?->address, $school?->city, $school?->district])));
} catch(\Throwable $e) { $schoolAddr = ''; }
@endphp

@if($useBuilder)
{{-- ══════════ BUILDER MODE ══════════ --}}
<div class="builder-page" style="width:{{ $pageWmm }}mm;height:{{ $pageHmm }}mm;position:relative;background:#fff;">
    @if($bgImgUrl)<img src="{{ $bgImgUrl }}" class="builder-bg" style="position:absolute;left:0;top:0;width:100%;height:100%;z-index:0;object-fit:cover;" alt="">@endif
    @foreach($builderEls as $el)
    @php
        $w=$el['w'];$h=$el['h'];$x=$el['x'];$y=$el['y'];
        $type=$el['type']??'text';$fs=(int)($el['fontSize']??12);
        $color=$el['color']??'#1e293b';$align=$el['align']??'left';
        $fw=$el['fontWeight']??'normal';$fst=$el['fontStyle']??'normal';
        $base="position:absolute;left:{$x}mm;top:{$y}mm;width:{$w}mm;height:{$h}mm;margin:0;padding:0;display:block;";
        $ts=$base."font-size:{$fs}pt;color:{$color};text-align:{$align};font-weight:{$fw};font-style:{$fst};line-height:1.2;";
    @endphp
    @if($type==='text')<div class="builder-el" style="{!! $ts !!}">{{ $el['content']??'' }}</div>
    @elseif($type==='field')@php $val=resolveField($el['field']??'',$ctx,$overrides);@endphp<div class="builder-el" style="{!! $ts !!}">{{ $val }}</div>
    @elseif($type==='line')<div class="builder-el" style="{{ $base }}"><div style="border-bottom:{{ (int)($el['border']??1) }}pt solid {{ $el['borderColor']??'#000' }};height:100%;display:block;width:100%;"></div></div>
    @elseif($type==='image')@php $src=($el['role']??'logo')==='signature'?$sigImgUrl:$logoUrl;@endphp<div class="builder-el" style="{{ $base }}">@if($src)<img src="{{ $src }}" style="width:100%;height:100%;object-fit:contain;" alt="">@endif</div>
    @elseif($type==='qr')<div class="builder-el" style="{{ $base }}">@if(!empty($qrUrl))<img src="{{ $qrUrl }}" style="width:100%;height:100%;" alt="">@endif</div>
    @elseif($type==='student_photo')<div class="builder-el" style="{{ $base }}">@if($photoUrl)<img src="{{ $photoUrl }}" style="width:100%;height:100%;object-fit:cover;" alt="">@endif</div>
    @endif
    @endforeach
    @if(isset($issued)&&$issued->status==='revoked')<div class="deco-revoked">REVOKED</div>@endif
</div>

@else
{{-- ══════════ CLASSIC / MODERN / ORNATE / MINIMAL ══════════ --}}

{{-- BG image --}}
@if($bgImgUrl)<img src="{{ $bgImgUrl }}" class="deco-bg" alt="">@endif

{{-- Watermark --}}
<div class="deco-watermark" style="color:{{ $pc }}07;">{{ strtoupper(config('app.name')) }}</div>

@if(isset($issued) && $issued->status==='revoked')
<div class="deco-revoked">REVOKED</div>
@endif

{{-- ── Top bar (all styles) ── --}}
<div class="deco-top" style="background:linear-gradient(90deg,{{ $pc }},{{ $sc }} 50%,{{ $pc }});"></div>

{{-- ── Bottom bar (all styles) ── --}}
<div class="deco-bottom" style="background:{{ $sc }};"></div>

{{-- ── Classic / Ornate: double border + corners ── --}}
@if($bs==='classic' || $bs==='ornate')
<div class="deco-outer" style="border:{{ $bs==='ornate'?'3.5pt double':'2.5pt solid' }} {{ $pc }};"></div>
<div class="deco-inner" style="border:0.8pt solid {{ $sc }}99;"></div>
@foreach(['tl','tr','bl','br'] as $c)
<div class="deco-corner {{ $c }}" style="border-color:{{ $sc }};
    {{ $c==='tl'?'border-width:2.5pt 0 0 2.5pt':($c==='tr'?'border-width:2.5pt 2.5pt 0 0':($c==='bl'?'border-width:0 0 2.5pt 2.5pt':'border-width:0 2.5pt 2.5pt 0')) }};"></div>
@endforeach
@endif

{{-- ── Modern: side bars ── --}}
@if($bs==='modern')
<div class="deco-side-l" style="background:{{ $pc }}44;"></div>
<div class="deco-side-r" style="background:{{ $pc }}44;"></div>
@endif

{{-- ════════════════════════════════════════
     CONTENT  (normal document flow)
════════════════════════════════════════ --}}
<div class="content-wrap">

    {{-- HEADER --}}
    <table width="100%" cellpadding="0" cellspacing="0"
           style="border-collapse:collapse;border-bottom:1pt solid {{ $sc }};margin-bottom:3mm;padding-bottom:2.5mm;table-layout:fixed;">
        <tr>
            <td width="24mm" style="vertical-align:middle;overflow:hidden;padding-right:2mm;">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" style="max-width:22mm;max-height:22mm;display:block;" alt="Logo">
                @else
                    <svg width="54" height="54" viewBox="0 0 54 54" style="display:block;">
                        <circle cx="27" cy="27" r="25" fill="{{ $pc }}" stroke="{{ $sc }}" stroke-width="2" />
                        <text x="27" y="24" font-family="DejaVu Sans, sans-serif" font-size="6.5" font-weight="bold" fill="{{ $sc }}" text-anchor="middle">SCHOOL</text>
                        <text x="27" y="34" font-family="DejaVu Sans, sans-serif" font-size="6.5" font-weight="bold" fill="{{ $sc }}" text-anchor="middle">LOGO</text>
                    </svg>
                @endif
            </td>
            <td style="text-align:center;vertical-align:middle;padding:0 2mm;word-break:break-word;overflow:hidden;">
                <div class="school-name" style="color:{{ $pc }};font-size:14pt;font-weight:bold;letter-spacing:2.5pt;">{{ config('app.name') }}</div>
                <div class="school-address" style="color:{{ $sc }};font-size:8pt;letter-spacing:1.5pt;font-style:italic;margin-top:1mm;">Excellence in Education</div>
            </td>
            <td width="24mm" style="vertical-align:middle;text-align:right;overflow:hidden;padding-left:2mm;">
                @if($template->show_student_photo && $photoUrl)
                    <img src="{{ $photoUrl }}" style="width:18mm;height:22mm;object-fit:cover;border-radius:1mm;border:1.5pt solid {{ $sc }};display:block;margin-left:auto;" alt="Photo">
                @else
                    <div style="width:18mm;height:22mm;border:1pt dashed #cbd5e1;border-radius:1mm;background:#fafafa;display:table;margin-left:auto;">
                        <div style="display:table-cell;vertical-align:middle;text-align:center;font-size:6pt;color:#94a3b8;line-height:1.1;">Student<br>Photo</div>
                    </div>
                @endif
            </td>
        </tr>
    </table>

    {{-- TITLE BLOCK --}}
    <div class="of-label" style="color:{{ $sc }};font-size:7.5pt;letter-spacing:4pt;text-transform:uppercase;font-style:italic;margin:3mm 0 1mm;text-align:center;">presents this</div>
    <div class="cert-title" style="color:{{ $pc }};font-size:24pt;font-weight:bold;letter-spacing:2pt;text-transform:uppercase;margin:0.5mm 0;text-align:center;">CERTIFICATE OF GOOD CONDUCT</div>
    <div class="cert-sub" style="color:{{ $sc }};font-size:8pt;letter-spacing:3.5pt;text-transform:uppercase;margin-top:0.5mm;text-align:center;">CHARACTER CERTIFICATE</div>

    {{-- DIVIDER --}}
    <div class="orn" style="margin:2.5mm 0;">
        <div class="orn-line"><div class="orn-rule" style="background:{{ $sc }};"></div></div>
        <div class="orn-star" style="color:{{ $sc }};font-size:9.5pt;width:24mm;text-align:center;">&#9670; &#9670; &#9670;</div>
        <div class="orn-line"><div class="orn-rule" style="background:{{ $sc }};"></div></div>
    </div>

    {{-- PREAMBLE --}}
    <p class="preamble" style="font-size:8.5pt;color:#475569;letter-spacing:2.5pt;text-transform:uppercase;font-style:italic;margin-bottom:2mm;text-align:center;">THIS IS TO PROUDLY CERTIFY THAT</p>

    {{-- STUDENT NAME --}}
    <div class="student-nm" style="color:{{ $pc }};font-size:24pt;font-weight:bold;margin-bottom:1.5mm;text-align:center;">{{ $fullName }}</div>
    <div class="name-rule" style="border-bottom:1.5pt solid {{ $sc }};width:90mm;margin:0 auto 3mm;"></div>

    {{-- BODY TEXT --}}
    <div class="body-text" style="font-size:9.5pt;color:#334155;line-height:1.8;text-align:center;max-width:200mm;margin:0 auto 3mm;">{!! nl2br(e($bodyText)) !!}</div>

    {{-- META ROW — real HTML table avoids DomPDF cellmap bug --}}
    <table class="meta-tbl" style="width:100%;border-collapse:collapse;border-top:0.5pt solid #cbd5e1;border-bottom:0.5pt solid #cbd5e1;background:#f8fafc;margin:3mm 0;">
        <tr>
            <td width="25%" style="text-align:center;padding:2.5mm 1mm;border-right:0.5pt solid #e2e8f0;">
                <span class="ml" style="font-size:6pt;color:#94a3b8;text-transform:uppercase;letter-spacing:1.5pt;display:block;margin-bottom:0.5mm;">CLASS</span>
                <span class="mv" style="font-size:9pt;font-weight:bold;color:{{ $pc }};display:block;">{{ $classStr ?: 'Grade X' }}</span>
            </td>
            <td width="25%" style="text-align:center;padding:2.5mm 1mm;border-right:0.5pt solid #e2e8f0;">
                <span class="ml" style="font-size:6pt;color:#94a3b8;text-transform:uppercase;letter-spacing:1.5pt;display:block;margin-bottom:0.5mm;">ADMISSION NO.</span>
                <span class="mv" style="font-size:9pt;font-weight:bold;color:{{ $pc }};display:block;">{{ $admissionNo ?: 'ADM-0001' }}</span>
            </td>
            <td width="25%" style="text-align:center;padding:2.5mm 1mm;border-right:0.5pt solid #e2e8f0;">
                <span class="ml" style="font-size:6pt;color:#94a3b8;text-transform:uppercase;letter-spacing:1.5pt;display:block;margin-bottom:0.5mm;">ACADEMIC YEAR</span>
                <span class="mv" style="font-size:9pt;font-weight:bold;color:{{ $pc }};display:block;">{{ $academicYr ?: '2081–82' }}</span>
            </td>
            <td width="25%" style="text-align:center;padding:2.5mm 1mm;">
                <span class="ml" style="font-size:6pt;color:#94a3b8;text-transform:uppercase;letter-spacing:1.5pt;display:block;margin-bottom:0.5mm;">ISSUE DATE</span>
                <span class="mv" style="font-size:9pt;font-weight:bold;color:{{ $pc }};display:block;">{{ isset($issued) && $issued->issued_date ? $issued->issued_date->format('d M Y') : now()->format('d M Y') }}</span>
            </td>
        </tr>
    </table>

    {{-- OFFICIAL SEAL --}}
    <div style="text-align:center;margin:2mm 0 1mm;">
        <svg width="56" height="56" viewBox="0 0 56 56" style="display:block;margin:0 auto;">
            <circle cx="28" cy="28" r="26" fill="none" stroke="{{ $sc }}" stroke-width="2" />
            <circle cx="28" cy="28" r="22" fill="none" stroke="{{ $sc }}" stroke-width="1" />
            <text x="28" y="25" font-family="DejaVu Sans, sans-serif" font-size="6.5" font-weight="bold" fill="{{ $sc }}" text-anchor="middle">OFFICIAL</text>
            <text x="28" y="36" font-family="DejaVu Sans, sans-serif" font-size="6.5" font-weight="bold" fill="{{ $sc }}" text-anchor="middle">SEAL</text>
        </svg>
        <div style="font-size:7pt;font-weight:bold;color:{{ $pc }};letter-spacing:1pt;margin-top:1mm;">No. {{ $certNo }}</div>
    </div>

    {{-- FOOTER CAPTION --}}
    @if($footerCaption)<p class="foot-cap" style="margin-bottom:2mm;">{{ $footerCaption }}</p>@endif

    {{-- SIGNATURES ROW — 2-column layout matching Design 1 --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin-top:3mm;">
        <tr>
            {{-- Left: Class Teacher --}}
            <td width="40%" style="text-align:left;vertical-align:bottom;padding-right:3mm;">
                <div style="border-top:0.8pt solid #cbd5e1;width:50mm;margin:0 0 1.5mm 0;height:0;display:block;"></div>
                <div class="sig-name" style="color:{{ $pc }};font-size:9pt;font-weight:bold;">Class Teacher</div>
                <div class="sig-title" style="color:#64748b;font-size:7.5pt;">Homeroom Instructor</div>
            </td>

            {{-- Center: QR Code if present --}}
            <td width="20%" style="text-align:center;vertical-align:bottom;">
                @if($template->show_qr_code && isset($issued) && $qrUrl)
                <img src="{{ $qrUrl }}" style="width:14mm;height:14mm;display:block;margin:0 auto 1mm;" alt="QR">
                @endif
            </td>

            {{-- Right: Principal --}}
            <td width="40%" style="text-align:right;vertical-align:bottom;padding-left:3mm;">
                @if($sigImgUrl)
                    <img src="{{ $sigImgUrl }}" class="sig-img" style="margin-left:auto;margin-right:0;" alt="Sig">
                @endif
                <div style="border-top:0.8pt solid #cbd5e1;width:50mm;margin:0 0 1.5mm auto;height:0;display:block;"></div>
                <div class="sig-name" style="color:{{ $pc }};font-size:9pt;font-weight:bold;">{{ $template->signature_name ?: 'Principal' }}</div>
                <div class="sig-title" style="color:#64748b;font-size:7.5pt;">{{ $template->signature_title ?: 'Head of Institution' }}</div>
            </td>
        </tr>
    </table>

</div>{{-- /content-wrap --}}
@endif

</body>
</html>
