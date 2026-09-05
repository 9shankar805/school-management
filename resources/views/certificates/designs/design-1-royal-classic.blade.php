<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Certificate – Royal Classic</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'DejaVu Serif', Georgia, serif;
    background: #f0ece4;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    padding: 20px;
}

/* ── Page wrapper ── */
.cert-wrap {
    width: 297mm;
    min-height: 210mm;
    background: #fffdf7;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 48px rgba(0,0,0,0.22);
}

/* ── Background texture stripes ── */
.cert-wrap::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image:
        repeating-linear-gradient(
            45deg,
            transparent,
            transparent 28px,
            rgba(184,149,62,0.04) 28px,
            rgba(184,149,62,0.04) 29px
        );
    z-index: 0;
}

/* ── Outer border ── */
.border-outer {
    position: absolute;
    top: 7mm; left: 7mm; right: 7mm; bottom: 7mm;
    border: 3.5pt solid #1a3a6b;
    z-index: 1;
}

/* ── Inner border ── */
.border-inner {
    position: absolute;
    top: 10mm; left: 10mm; right: 10mm; bottom: 10mm;
    border: 1pt solid #b8953e;
    z-index: 1;
}

/* ── Corner ornaments (SVG-drawn via CSS) ── */
.corner-ornament {
    position: absolute;
    width: 22mm;
    height: 22mm;
    z-index: 2;
}
.corner-ornament.tl { top: 4mm;  left: 4mm; }
.corner-ornament.tr { top: 4mm;  right: 4mm; transform: scaleX(-1); }
.corner-ornament.bl { bottom: 4mm; left: 4mm; transform: scaleY(-1); }
.corner-ornament.br { bottom: 4mm; right: 4mm; transform: scale(-1,-1); }

/* ── Top/bottom gold bars ── */
.bar-top {
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 5mm;
    background: linear-gradient(90deg, #1a3a6b 0%, #2a5298 40%, #b8953e 50%, #2a5298 60%, #1a3a6b 100%);
    z-index: 3;
}
.bar-bottom {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 5mm;
    background: linear-gradient(90deg, #1a3a6b 0%, #2a5298 40%, #b8953e 50%, #2a5298 60%, #1a3a6b 100%);
    z-index: 3;
}

/* ── Content area ── */
.cert-content {
    position: relative;
    z-index: 5;
    padding: 16mm 20mm 18mm;
    display: flex;
    flex-direction: column;
    align-items: center;
    min-height: 210mm;
}

/* ── Header ── */
.cert-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8mm;
    width: 100%;
    margin-bottom: 5mm;
    padding-bottom: 4mm;
    border-bottom: 1.5pt solid #b8953e44;
}

.school-logo {
    width: 20mm;
    height: 20mm;
    border-radius: 50%;
    border: 2pt solid #b8953e;
    object-fit: contain;
    background: #fff;
    padding: 2px;
}

.school-logo-placeholder {
    width: 20mm;
    height: 20mm;
    border-radius: 50%;
    border: 2pt solid #b8953e;
    background: #1a3a6b;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 9pt;
    font-weight: bold;
    color: #b8953e;
    letter-spacing: 0.5pt;
    text-align: center;
    line-height: 1.2;
}

.header-center {
    text-align: center;
    flex: 1;
}

.school-name {
    font-size: 14pt;
    font-weight: bold;
    color: #1a3a6b;
    letter-spacing: 2pt;
    text-transform: uppercase;
}

.school-sub {
    font-size: 7.5pt;
    color: #64748b;
    letter-spacing: 1.5pt;
    margin-top: 1mm;
}

.school-divider {
    display: block;
    width: 60mm;
    height: 1pt;
    background: linear-gradient(90deg, transparent, #b8953e, transparent);
    margin: 2mm auto 0;
}

/* ── Title block ── */
.title-block {
    text-align: center;
    margin: 5mm 0 3mm;
}

.cert-title-label {
    font-size: 7pt;
    letter-spacing: 5pt;
    color: #b8953e;
    text-transform: uppercase;
    font-style: italic;
}

.cert-title {
    font-size: 28pt;
    font-weight: bold;
    color: #1a3a6b;
    letter-spacing: 3pt;
    text-transform: uppercase;
    margin: 1mm 0;
    text-shadow: 0 1px 0 rgba(184,149,62,0.2);
}

.cert-subtitle {
    font-size: 9pt;
    color: #b8953e;
    letter-spacing: 2pt;
    text-transform: uppercase;
}

/* ── Gold ornamental divider ── */
.ornament-divider {
    display: flex;
    align-items: center;
    gap: 3mm;
    margin: 4mm 0;
    width: 100%;
    justify-content: center;
}
.ornament-divider .line {
    flex: 1;
    max-width: 60mm;
    height: 1pt;
    background: linear-gradient(90deg, transparent, #b8953e);
}
.ornament-divider .line.right {
    background: linear-gradient(90deg, #b8953e, transparent);
}
.ornament-divider .diamond {
    width: 6px;
    height: 6px;
    background: #b8953e;
    transform: rotate(45deg);
}
.ornament-divider .star {
    font-size: 10pt;
    color: #b8953e;
    line-height: 1;
}

/* ── Preamble & student name ── */
.cert-preamble {
    font-size: 9.5pt;
    color: #475569;
    letter-spacing: 2pt;
    text-transform: uppercase;
    margin-bottom: 3mm;
    text-align: center;
    font-style: italic;
}

.student-name {
    font-size: 26pt;
    font-weight: bold;
    color: #1a3a6b;
    text-align: center;
    margin-bottom: 1mm;
    letter-spacing: 1.5pt;
}

.student-name-underline {
    display: block;
    width: 90mm;
    margin: 0 auto 5mm;
    border-bottom: 1.5pt solid #b8953e;
}

/* ── Body text ── */
.cert-body {
    font-size: 10pt;
    color: #334155;
    line-height: 1.8;
    text-align: center;
    max-width: 200mm;
    margin: 0 auto 5mm;
}

/* ── Meta info row ── */
.cert-meta {
    display: flex;
    justify-content: center;
    gap: 0;
    border-top: 0.5pt solid #b8953e33;
    border-bottom: 0.5pt solid #b8953e33;
    padding: 2.5mm 0;
    margin: 3mm 0 6mm;
    width: 100%;
}
.meta-cell {
    flex: 1;
    text-align: center;
    padding: 0 4mm;
    border-right: 0.5pt solid #e2e8f0;
}
.meta-cell:last-child { border-right: none; }
.meta-label {
    display: block;
    font-size: 6.5pt;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 1.5pt;
    margin-bottom: 0.5mm;
}
.meta-value {
    display: block;
    font-size: 9pt;
    font-weight: bold;
    color: #1a3a6b;
}

/* ── Footer signatures ── */
.cert-footer {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    width: 100%;
    margin-top: auto;
    padding-top: 4mm;
}

.sig-block {
    text-align: center;
    min-width: 55mm;
}
.sig-image {
    height: 12mm;
    max-width: 40mm;
    display: block;
    margin: 0 auto 1mm;
    object-fit: contain;
}
.sig-line {
    width: 50mm;
    border-top: 0.8pt solid #94a3b8;
    margin: 0 auto 1.5mm;
}
.sig-name  { font-size: 9pt; font-weight: bold; color: #1e293b; }
.sig-title { font-size: 7.5pt; color: #64748b; }

.footer-center {
    text-align: center;
    flex: 1;
}

.seal-circle {
    width: 22mm;
    height: 22mm;
    border-radius: 50%;
    border: 2pt double #b8953e;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1mm;
    background: radial-gradient(circle, #fffdf7 60%, #f5edd9 100%);
    font-size: 6pt;
    color: #b8953e;
    letter-spacing: 1pt;
    text-transform: uppercase;
    font-weight: bold;
    text-align: center;
    line-height: 1.4;
}

.cert-number {
    font-size: 6.5pt;
    color: #94a3b8;
    font-family: 'DejaVu Sans Mono', monospace;
    letter-spacing: 1pt;
    margin-top: 1mm;
}

/* ── Watermark ── */
.watermark {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-30deg);
    font-size: 72pt;
    font-weight: 900;
    color: rgba(26, 58, 107, 0.04);
    letter-spacing: 6pt;
    text-transform: uppercase;
    pointer-events: none;
    z-index: 1;
    white-space: nowrap;
}

/* ── Student photo ── */
.student-photo {
    width: 22mm;
    height: 26mm;
    object-fit: cover;
    border: 2pt solid #b8953e44;
    border-radius: 1mm;
}
.student-photo-placeholder {
    width: 22mm;
    height: 26mm;
    border: 1pt dashed #cbd5e1;
    border-radius: 1mm;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 7pt;
    color: #cbd5e1;
    text-align: center;
}
</style>
</head>
<body>

<div class="cert-wrap">
    {{-- Background & borders --}}
    <div class="bar-top"></div>
    <div class="bar-bottom"></div>
    <div class="border-outer"></div>
    <div class="border-inner"></div>

    {{-- Ornate corner ornaments (inline SVG) --}}
    @foreach(['tl','tr','bl','br'] as $pos)
    <svg class="corner-ornament {{ $pos }}" viewBox="0 0 80 80" xmlns="http://www.w3.org/2000/svg">
        <path d="M4 4 L30 4 L30 8 L8 8 L8 30 L4 30 Z" fill="#1a3a6b"/>
        <path d="M4 4 L22 4 L22 6 L6 6 L6 22 L4 22 Z" fill="#b8953e"/>
        <circle cx="4" cy="4" r="3" fill="#b8953e"/>
        <path d="M14 4 Q20 14 30 14 Q20 14 14 24 Q8 14 14 4" fill="none" stroke="#b8953e" stroke-width="0.8" opacity="0.5"/>
    </svg>
    @endforeach

    {{-- Watermark --}}
    <div class="watermark">{{ config('app.name', 'SCHOOL') }}</div>

    {{-- Main content --}}
    <div class="cert-content">

        {{-- Header --}}
        <div class="cert-header">
            <div>
                @if(!empty($logoUrl))
                    <img src="{{ $logoUrl }}" class="school-logo" alt="Logo">
                @else
                    <div class="school-logo-placeholder">SCHOOL<br>LOGO</div>
                @endif
            </div>

            <div class="header-center">
                <div class="school-name">{{ $schoolName ?? config('app.name', 'NepalSchoolHub') }}</div>
                <div class="school-sub">{{ $schoolAddress ?? 'Excellence in Education' }}</div>
                <span class="school-divider"></span>
            </div>

            <div>
                @if(!empty($photoUrl))
                    <img src="{{ $photoUrl }}" class="student-photo" alt="Student">
                @else
                    <div class="student-photo-placeholder">Student<br>Photo</div>
                @endif
            </div>
        </div>

        {{-- Title --}}
        <div class="title-block">
            <div class="cert-title-label">presents this</div>
            <div class="cert-title">CERTIFICATE OF GOOD CONDUCT</div>
            <div class="cert-subtitle">CHARACTER CERTIFICATE</div>
        </div>

        {{-- Ornamental divider --}}
        <div class="ornament-divider">
            <div class="line"></div>
            <div class="diamond"></div>
            <div class="star">✦</div>
            <div class="diamond"></div>
            <div class="line right"></div>
        </div>

        {{-- Preamble --}}
        <p class="cert-preamble">This is to proudly certify that</p>

        {{-- Student name --}}
        <div class="student-name">{{ $fullName ?? 'Student Full Name' }}</div>
        <span class="student-name-underline"></span>

        {{-- Body text --}}
        <div class="cert-body">
            {!! nl2br(e($bodyText ?? 'has successfully demonstrated outstanding academic performance and exemplary conduct throughout the academic year, and is hereby recognized and awarded this certificate in testament of their dedication and merit.')) !!}
        </div>

        {{-- Meta row --}}
        <div class="cert-meta">
            <div class="meta-cell">
                <span class="meta-label">Class</span>
                <span class="meta-value">{{ $classStr ?? 'Grade X' }}</span>
            </div>
            <div class="meta-cell">
                <span class="meta-label">Admission No.</span>
                <span class="meta-value">{{ $admissionNo ?? 'ADM-0001' }}</span>
            </div>
            <div class="meta-cell">
                <span class="meta-label">Academic Year</span>
                <span class="meta-value">{{ $academicYr ?? '2081–82' }}</span>
            </div>
            <div class="meta-cell">
                <span class="meta-label">Issue Date</span>
                <span class="meta-value">{{ isset($issued) ? $issued->issued_date->format('d M Y') : now()->format('d M Y') }}</span>
            </div>
        </div>

        {{-- Footer --}}
        <div class="cert-footer">
            {{-- Left signature --}}
            <div class="sig-block">
                <div style="height:12mm;"></div>
                <div class="sig-line"></div>
                <div class="sig-name">Class Teacher</div>
                <div class="sig-title">Homeroom Instructor</div>
            </div>

            {{-- Centre seal --}}
            <div class="footer-center">
                <div class="seal-circle">
                    OFFICIAL<br>SEAL
                </div>
                @if(isset($issued))
                <div class="cert-number">No. {{ $issued->certificate_number }}</div>
                @endif
            </div>

            {{-- Right signature --}}
            <div class="sig-block">
                @if(!empty($sigImgUrl))
                    <img src="{{ $sigImgUrl }}" class="sig-image" alt="Signature">
                @else
                    <div style="height:12mm;"></div>
                @endif
                <div class="sig-line"></div>
                <div class="sig-name">{{ $template->signature_name ?? 'Principal' }}</div>
                <div class="sig-title">{{ $template->signature_title ?? 'Head of Institution' }}</div>
            </div>
        </div>

    </div>{{-- /cert-content --}}
</div>{{-- /cert-wrap --}}

</body>
</html>
