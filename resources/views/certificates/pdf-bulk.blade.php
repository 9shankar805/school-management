<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: DejaVu Sans, sans-serif; background: #fff; color: #1e293b; }

/* ── Builder page ─────────────────────────────────────────────────── */
.builder-page {
    width: 100%; min-height: 100%; position: relative; padding: 0; overflow: hidden;
    page-break-after: always;
}
.builder-page:last-child { page-break-after: avoid; }
.builder-bg { position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 0; }
.builder-el {
    position: absolute; z-index: 2; word-break: break-word; line-height: 1.25; overflow: hidden;
}
.builder-el img { display:block; max-width:100%; max-height:100%; width:100%; height:100%; object-fit: contain; }
.builder-el .builder-line { display:block; width:100%; height:100%; }

/* ── Classic page ─────────────────────────────────────────────────── */
.cert-page {
    width: 100%; min-height: 100%; padding: 13mm 17mm; position: relative;
    page-break-after: always;
}
.cert-page:last-child { page-break-after: avoid; }
.cert-bg-image { position: absolute; top:0; left:0; width:100%; height:100%; z-index:0; }
.cert-page > *:not(.cert-bg-image):not(.cert-outer-border):not(.cert-inner-border):not(.corner):not(.cert-bottom-bar):not(.cert-side-bar-left):not(.cert-side-bar-right) {
    position: relative; z-index: 2;
}
.border-classic .cert-outer-border { position: absolute; top: 5mm; left: 5mm; right: 5mm; bottom: 5mm; border: 3pt solid {{ $template->primary_color }}; }
.border-classic .cert-inner-border { position: absolute; top: 8mm; left: 8mm; right: 8mm; bottom: 8mm; border: 0.8pt solid {{ $template->secondary_color }}; }
.border-classic .corner { position: absolute; width: 10mm; height: 10mm; border-color: {{ $template->secondary_color }}; border-style: solid; }
.border-classic .corner.tl { top: 3.5mm; left: 3.5mm; border-width: 1.5pt 0 0 1.5pt; }
.border-classic .corner.tr { top: 3.5mm; right: 3.5mm; border-width: 1.5pt 1.5pt 0 0; }
.border-classic .corner.bl { bottom: 3.5mm; left: 3.5mm; border-width: 0 0 1.5pt 1.5pt; }
.border-classic .corner.br { bottom: 3.5mm; right: 3.5mm; border-width: 0 1.5pt 1.5pt 0; }
.border-modern .cert-outer-border { position: absolute; top: 0; left: 0; right: 0; height: 4.5mm; background: {{ $template->primary_color }}; }
.border-modern .cert-bottom-bar   { position: absolute; bottom: 0; left: 0; right: 0; height: 2mm; background: {{ $template->secondary_color }}; }
.border-modern .cert-side-bar-left  { position: absolute; top: 4.5mm; bottom: 2mm; left: 0; width: 1.5mm; background: {{ $template->primary_color }}33; }
.border-modern .cert-side-bar-right { position: absolute; top: 4.5mm; bottom: 2mm; right: 0; width: 1.5mm; background: {{ $template->primary_color }}33; }
.border-ornate .cert-outer-border { position: absolute; top: 4mm; left: 4mm; right: 4mm; bottom: 4mm; border: 3.5pt double {{ $template->primary_color }}; }
.border-ornate .cert-inner-border { position: absolute; top: 7.5mm; left: 7.5mm; right: 7.5mm; bottom: 7.5mm; border: 0.8pt solid {{ $template->secondary_color }}55; }
.cert-header { display: table; width: 100%; margin-bottom: 5mm; padding-bottom: 4mm; border-bottom: 0.8pt solid {{ $template->primary_color }}44; }
.cert-header-logo { display: table-cell; width: 20mm; vertical-align: middle; }
.cert-header-logo img { max-width: 16mm; max-height: 16mm; }
.cert-header-text { display: table-cell; text-align: center; vertical-align: middle; }
.cert-header-photo { display: table-cell; width: 20mm; vertical-align: middle; text-align: right; }
.cert-header-photo img { width: 18mm; height: 22mm; object-fit: cover; border: 1pt solid {{ $template->primary_color }}44; border-radius: 1mm; }
.school-name { font-size: 11.5pt; font-weight: bold; color: {{ $template->primary_color }}; letter-spacing: 0.8pt; text-transform: uppercase; }
.cert-title   { font-size: 17pt; font-weight: bold; color: #1e1b4b; text-transform: uppercase; letter-spacing: 1pt; margin: 1.5mm 0 0.8mm; }
.cert-type-label { font-size: 7.5pt; color: #64748b; letter-spacing: 2pt; text-transform: uppercase; }
.cert-preamble { text-align: center; font-size: 8.5pt; color: #475569; letter-spacing: 1.5pt; text-transform: uppercase; margin-bottom: 1.5mm; }
.cert-student-name { text-align: center; font-size: 20pt; font-weight: bold; color: {{ $template->primary_color }}; margin-bottom: 1mm; }
.cert-name-line { display: block; width: 75mm; margin: 0 auto 3.5mm; border-bottom: 0.8pt solid {{ $template->primary_color }}55; }
.cert-body-text { font-size: 9.5pt; color: #334155; line-height: 1.7; text-align: justify; text-align-last: center; margin-bottom: 3mm; }
.cert-meta { display: table; width: 100%; margin: 2mm 0 4mm; border-top: 0.4pt solid {{ $template->primary_color }}22; border-bottom: 0.4pt solid {{ $template->primary_color }}22; padding: 1.5mm 0; }
.cert-meta-cell { display: table-cell; text-align: center; font-size: 8pt; padding: 0 3mm; border-right: 0.4pt solid #e2e8f0; }
.cert-meta-cell:last-child { border-right: none; }
.cert-meta-label { color: #94a3b8; font-size: 6.5pt; text-transform: uppercase; letter-spacing: 1pt; display: block; }
.cert-meta-value { color: #1e293b; font-weight: bold; }
.cert-footer { position: absolute; bottom: 10mm; left: 17mm; right: 17mm; display: table; width: calc(100% - 34mm); }
.cert-footer-cell { display: table-cell; vertical-align: bottom; text-align: center; font-size: 7.5pt; color: #64748b; }
.sig-image { max-height: 10mm; max-width: 25mm; margin: 0 auto 1mm; display: block; }
.sig-line  { border-top: 0.7pt solid #94a3b8; width: 38mm; margin: 0 auto 1.5mm; }
.sig-name  { font-weight: bold; color: #1e293b; font-size: 8pt; }
.sig-title { color: #64748b; font-size: 7pt; }
.cert-no-block { font-family: 'DejaVu Sans Mono', monospace; font-size: 6.5pt; color: #94a3b8; }
.cert-no-label { font-size: 6pt; color: #cbd5e1; text-transform: uppercase; letter-spacing: 1pt; }
.qr-img { width: 16mm; height: 16mm; display: block; margin: 0 auto; }
</style>
</head>
<body>

@php
    $borderClass = 'border-' . ($template->border_style ?? 'classic');
    $logoUrl     = $template->logo_path && file_exists(public_path('storage/'.$template->logo_path))
        ? public_path('storage/'.$template->logo_path) : null;
    $sigImgUrl   = $template->signature_image_path && file_exists(public_path('storage/'.$template->signature_image_path))
        ? public_path('storage/'.$template->signature_image_path) : null;
    $bgImgUrl    = $template->background_image_path && file_exists(public_path('storage/'.$template->background_image_path))
        ? public_path('storage/'.$template->background_image_path) : null;

    $useBuilder = $template->builder_mode && !empty($template->builder_layout);
    $pageWmm = $template->paperWidthMm();
    $pageHmm = $template->paperHeightMm();

    // Pre-sanitize builder elements once per template.
    $builderElementsBulk = [];
    if ($useBuilder) {
        $_defSize = function ($el) use ($pageWmm, $pageHmm) {
            $type = $el['type'] ?? 'text';
            $f = (float)($el['fontSize'] ?? 12);
            switch ($type) {
                case 'line':          return [150, max(1, (float)($el['border'] ?? 1) / 3.7795275591)];
                case 'qr':            return [32, 32];
                case 'student_photo': return [32, 44];
                case 'image':         return [42, 28];
                default:
                    $w = isset($el['w']) ? (float)$el['w'] : null;
                    $h = isset($el['h']) ? (float)$el['h'] : null;
                    if ($w === null) $w = max(30, $pageWmm - 40);
                    if ($h === null) $h = max(5, $f * 0.45 + 2);
                    return [$w, $h];
            }
        };
        foreach (collect($template->builder_layout ?? [])->sortBy('zIndex') as $el) {
            [$defW, $defH] = $_defSize($el);
            $w = isset($el['w']) && $el['w'] !== null ? (float)$el['w'] : $defW;
            $h = isset($el['h']) && $el['h'] !== null ? (float)$el['h'] : $defH;
            $x = max(0, (float)($el['x'] ?? 0));
            $y = max(0, (float)($el['y'] ?? 0));
            if ($w > $pageWmm) $w = $pageWmm;
            if ($h > $pageHmm) $h = $pageHmm;
            if ($x + $w > $pageWmm) $x = max(0, $pageWmm - $w);
            if ($y + $h > $pageHmm) $y = max(0, $pageHmm - $h);
            $el['x'] = $x; $el['y'] = $y; $el['w'] = $w; $el['h'] = $h;
            $builderElementsBulk[] = $el;
        }
    }

    if (!function_exists('resolveField_bulk')) {
        function resolveField_bulk($key, $ctx, $overrides) {
            if (is_array($overrides) && isset($overrides[$key]) && strlen($overrides[$key])) {
                return $overrides[$key];
            }
            extract($ctx);
            switch ($key) {
                case 'student_name':  return $fullName;
                case 'first_name':    return $student->first_name ?? '';
                case 'last_name':     return $student->last_name ?? '';
                case 'class':         return $classStr;
                case 'section':       return $promotion?->section?->section_name ?? '';
                case 'roll_no':       return $promotion?->roll_no ?? '';
                case 'admission_no':  return $admissionNo;
                case 'board_reg_no':  return $academic?->board_reg_no ?? '';
                case 'academic_year': return $academicYr;
                case 'birthday':      return $birthDt ? $birthDt->format('F j, Y') : '';
                case 'age':           return $birthDt ? $birthDt->age : '';
                case 'gender':        return ucfirst($student->gender ?? '');
                case 'father_name':   return $parentInfo?->father_name ?? '';
                case 'mother_name':   return $parentInfo?->mother_name ?? '';
                case 'address':       return $student->address ?? '';
                case 'date':          return $issuedDate?->format('F j, Y');
                case 'school_name':   return config('app.name');
                case 'issued_by':     return $issuerName;
                case 'cert_no':       return $certNo;
                case 'purpose':       return $purpose;
                case 'extra_notes':   return $extraNotes;
                case 'custom_1': case 'custom_2': case 'custom_3':
                    return $overrides[$key] ?? '';
                default: return '';
            }
        }
    }
@endphp

@foreach($items as $item)
@php
    $student   = $item['student'];
    $issued    = $item['issued'];
    $bodyText  = $item['bodyText'];
    $promotion = $student->promotions->sortByDesc('id')->first();
    $academic  = $student->academic_info;
    $parentInfo = $student->parent_info ?? null;
    $photoUrl  = null;
    if (!empty($student->photo) && file_exists(public_path('storage/'.$student->photo))) {
        $photoUrl = public_path('storage/'.$student->photo);
    }
    $admissionNo = $academic?->admission_no ?? $promotion?->id_card_number ?? '';
    $classStr = $promotion?->schoolClass?->class_name ?? '';
    if ($classStr && ($promotion?->section?->section_name ?? '')) {
        $classStr .= ' – ' . $promotion->section->section_name;
    }
    $fullName  = $student->full_name ?? trim(($student->first_name ?? '').' '.($student->last_name ?? ''));
    $birthDt   = $academic?->date_of_birth ? \Carbon\Carbon::parse($academic->date_of_birth) : null;
    $academicYr= $promotion?->session?->name ?? '';
    $issuedDate= $issued->issued_date;
    $purpose   = $issued->purpose ?? '';
    $extraNotes= $issued->extra_notes ?? '';
    $certNo    = $issued->certificate_number;
    $qrUrl     = $issued->qrCodeUrl();
    $issuerName= $issued->issuer ? ($issued->issuer->full_name ?? (($issued->issuer->first_name ?? '').' '.($issued->issuer->last_name ?? ''))) : '';
    if (!empty($issued->field_overrides)) {
        if (is_array($issued->field_overrides)) {
            $overrides = $issued->field_overrides;
        } else {
            $decoded = json_decode($issued->field_overrides, true);
            $overrides = is_array($decoded) ? $decoded : [];
        }
    } else {
        $overrides = [];
    }
    $ctx = compact('student','promotion','academic','parentInfo','classStr','admissionNo','fullName','birthDt','academicYr','issuedDate','purpose','extraNotes','certNo','issuerName');
@endphp

@if($useBuilder)
    <div class="builder-page">
        @if($bgImgUrl)<img src="{{ $bgImgUrl }}" class="builder-bg" alt="">@endif
        @foreach(collect($template->builder_layout)->sortBy('zIndex') as $el)
            @php
                $w = isset($el['w']) ? (float)$el['w'] : null;
                $h = isset($el['h']) ? (float)$el['h'] : null;
                $x = (float)($el['x'] ?? 0);
                $y = (float)($el['y'] ?? 0);
                $type = $el['type'] ?? 'text';
                $fs   = (int)($el['fontSize'] ?? 12);
                $color= $el['color'] ?? '#1e293b';
                $align= $el['align'] ?? 'left';
                $fw   = $el['fontWeight'] ?? 'normal';
                $fst  = $el['fontStyle'] ?? 'normal';
                $style = "left:{$x}mm; top:{$y}mm; font-size:{$fs}pt; color:{$color}; text-align:{$align}; font-weight:{$fw}; font-style:{$fst};";
                if ($w !== null) $style .= " width:{$w}mm;";
                if ($h !== null) $style .= " height:{$h}mm;";
            @endphp

            @if($type === 'text')
                <div class="builder-el" style="{{ $style }}">{{ $el['content'] ?? '' }}</div>
            @elseif($type === 'field')
                <div class="builder-el" style="{{ $style }}">{{ resolveField_bulk($el['field'] ?? '', $ctx, $overrides) }}</div>
            @elseif($type === 'line')
                <div class="builder-el" style="left:{{$x}}mm; top:{{$y}}mm; width:{{$w ?? 100}}mm; height:{{$h ?? 1}}mm;">
                    <div class="builder-line" style="border-bottom:{{ (int)($el['border']??1) }}pt solid {{ $el['borderColor'] ?? '#000' }}; height:100%;"></div>
                </div>
            @elseif($type === 'image')
                @php
                    $role = $el['role'] ?? 'logo';
                    $src = $role === 'signature' ? $sigImgUrl : $logoUrl;
                    $iw = $w ?? ($role==='signature'?50:35);
                    $ih = $h ?? ($role==='signature'?18:28);
                @endphp
                <div class="builder-el" style="left:{{$x}}mm; top:{{$y}}mm; width:{{$iw}}mm; height:{{$ih}}mm;">
                    @if($src)<img src="{{ $src }}" alt="">@endif
                </div>
            @elseif($type === 'qr')
                <div class="builder-el" style="left:{{$x}}mm; top:{{$y}}mm; width:{{$w ?? 28}}mm; height:{{$h ?? 28}}mm;">
                    @if($qrUrl)<img src="{{ $qrUrl }}" alt="">@endif
                </div>
            @elseif($type === 'student_photo')
                <div class="builder-el" style="left:{{$x}}mm; top:{{$y}}mm; width:{{$w ?? 32}}mm; height:{{$h ?? 40}}mm;">
                    @if($photoUrl)<img src="{{ $photoUrl }}" alt="" style="object-fit:cover;">@endif
                </div>
            @endif
        @endforeach
    </div>

@else

    <div class="cert-page {{ $borderClass }}">
        @if($bgImgUrl)<img src="{{ $bgImgUrl }}" class="cert-bg-image" alt="">@endif
        @if($template->border_style === 'classic')
            <div class="cert-outer-border"></div><div class="cert-inner-border"></div>
            <div class="corner tl"></div><div class="corner tr"></div>
            <div class="corner bl"></div><div class="corner br"></div>
        @elseif($template->border_style === 'modern')
            <div class="cert-outer-border"></div><div class="cert-bottom-bar"></div>
            <div class="cert-side-bar-left"></div><div class="cert-side-bar-right"></div>
        @elseif($template->border_style === 'ornate')
            <div class="cert-outer-border"></div><div class="cert-inner-border"></div>
        @endif

        <div class="cert-header">
            <div class="cert-header-logo">@if($logoUrl)<img src="{{ $logoUrl }}" alt="Logo">@endif</div>
            <div class="cert-header-text">
                <div class="school-name">{{ config('app.name') }}</div>
                <div class="cert-title">{{ $template->header_text ?: $template->typeLabel() }}</div>
                <div class="cert-type-label">{{ $template->typeLabel() }}</div>
            </div>
            <div class="cert-header-photo">@if($template->show_student_photo && $photoUrl)<img src="{{ $photoUrl }}" alt="Photo">@endif</div>
        </div>

        <p class="cert-preamble">This is to certify that</p>
        <div class="cert-student-name">{{ $fullName }}<span class="cert-name-line"></span></div>
        <div class="cert-body-text">{!! nl2br(e($bodyText)) !!}</div>

        <div class="cert-meta">
            @if($classStr)
            <div class="cert-meta-cell"><span class="cert-meta-label">Class</span><span class="cert-meta-value">{{ $classStr }}</span></div>
            @endif
            @if($admissionNo)
            <div class="cert-meta-cell"><span class="cert-meta-label">Admission No.</span><span class="cert-meta-value">{{ $admissionNo }}</span></div>
            @endif
            <div class="cert-meta-cell"><span class="cert-meta-label">Issue Date</span><span class="cert-meta-value">{{ $issued->issued_date->format('d M Y') }}</span></div>
        </div>

        @if($template->footer_text)
        <p style="text-align:center; font-size:8pt; color:#64748b; font-style:italic; margin-bottom:2mm;">{{ $template->footer_text }}</p>
        @endif

        <div class="cert-footer">
            <div class="cert-footer-cell" style="text-align:left;">
                <span class="cert-no-label">Issued</span>
                <p style="font-weight:bold; color:#1e293b; font-size:8pt; margin-top:0.5mm;">{{ $issued->issued_date->format('F j, Y') }}</p>
            </div>
            <div class="cert-footer-cell">
                @if($sigImgUrl)<img src="{{ $sigImgUrl }}" class="sig-image" alt="Signature">
                @else<div style="height:10mm;"></div>@endif
                <div class="sig-line"></div>
                @if($template->signature_name)
                <div class="sig-name">{{ $template->signature_name }}</div>
                @if($template->signature_title)<div class="sig-title">{{ $template->signature_title }}</div>@endif
                @endif
            </div>
            <div class="cert-footer-cell" style="text-align:right;">
                @if($template->show_qr_code)<img src="{{ $issued->qrCodeUrl() }}" class="qr-img" alt="QR">@endif
                @if($template->show_serial_number)
                <p class="cert-no-label" style="margin-top:0.5mm;">Cert. No.</p>
                <p class="cert-no-block">{{ $issued->certificate_number }}</p>
                @endif
            </div>
        </div>
    </div>

@endif

@endforeach

</body>
</html>
