<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Certificate – Achievement Gold</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'DejaVu Serif', Georgia, serif;
    background: #2d1a0e;
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
    background: #1c0a00;
    position: relative;
    overflow: hidden;
    box-shadow: 0 0 0 3mm #7b1e1e, 0 0 0 5mm #b8953e, 0 16px 60px rgba(0,0,0,0.6);
}

/* ── Radial glow background ── */
.cert-bg-glow {
    position: absolute;
    inset: 0;
    background:
        radial-gradient(ellipse 80% 60% at 50% 30%, #3d1a0022 0%, transparent 70%),
        radial-gradient(ellipse 60% 40% at 50% 80%, #b8953e0a 0%, transparent 60%);
    z-index: 0;
}

/* Linen texture overlay */
.cert-bg-texture {
    position: absolute;
    inset: 0;
    background-image:
        repeating-linear-gradient(0deg, rgba(184,149,62,0.03) 0px, rgba(184,149,62,0.03) 1px, transparent 1px, transparent 4px),
        repeating-linear-gradient(90deg, rgba(184,149,62,0.015) 0px, rgba(184,149,62,0.015) 1px, transparent 1px, transparent 6px);
    z-index: 0;
}

/* ── Outer maroon frame ── */
.frame-outer {
    position: absolute;
    top: 6mm; left: 6mm; right: 6mm; bottom: 6mm;
    border: 3pt solid #7b1e1e;
    z-index: 1;
}

/* ── Inner gold frame ── */
.frame-inner {
    position: absolute;
    top: 9.5mm; left: 9.5mm; right: 9.5mm; bottom: 9.5mm;
    border: 1.5pt solid #b8953e;
    z-index: 1;
}

/* ── Hairline inner-inner ── */
.frame-hairline {
    position: absolute;
    top: 11.5mm; left: 11.5mm; right: 11.5mm; bottom: 11.5mm;
    border: 0.5pt solid #b8953e44;
    z-index: 1;
}

/* ── Corner medallion dots ── */
.corner-dot {
    position: absolute;
    width: 5mm;
    height: 5mm;
    border-radius: 50%;
    background: radial-gradient(circle, #d4a84b 30%, #7b1e1e 100%);
    border: 1pt solid #b8953e;
    z-index: 3;
}
.corner-dot.tl { top: 4.5mm;  left: 4.5mm;  }
.corner-dot.tr { top: 4.5mm;  right: 4.5mm; }
.corner-dot.bl { bottom: 4.5mm; left: 4.5mm;  }
.corner-dot.br { bottom: 4.5mm; right: 4.5mm; }

/* ── Top decorative arch band ── */
.arch-band {
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4mm;
    background: repeating-linear-gradient(
        90deg,
        #7b1e1e 0px, #7b1e1e 6px,
        #b8953e 6px, #b8953e 8px,
        #7b1e1e 8px, #7b1e1e 14px
    );
    z-index: 2;
}
.arch-band-bottom {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 4mm;
    background: repeating-linear-gradient(
        90deg,
        #7b1e1e 0px, #7b1e1e 6px,
        #b8953e 6px, #b8953e 8px,
        #7b1e1e 8px, #7b1e1e 14px
    );
    z-index: 2;
}

/* ── Central medallion ── */
.medallion-wrap {
    display: flex;
    justify-content: center;
    margin-bottom: 4mm;
    position: relative;
    z-index: 5;
}
.medallion {
    width: 26mm;
    height: 26mm;
    border-radius: 50%;
    border: 2.5pt solid #b8953e;
    background: radial-gradient(circle, #2d1200 40%, #1c0a00 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    box-shadow: 0 0 0 1.5pt #7b1e1e, inset 0 0 8px rgba(184,149,62,0.3);
}
.medallion::before {
    content: '';
    position: absolute;
    inset: 2mm;
    border-radius: 50%;
    border: 0.5pt solid #b8953e66;
}
.medallion-star {
    font-size: 18pt;
    color: #d4a84b;
    text-shadow: 0 0 8px rgba(212,168,75,0.6);
    line-height: 1;
}

/* ── Content area ── */
.cert-content {
    position: relative;
    z-index: 5;
    padding: 14mm 18mm 16mm;
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
    gap: 6mm;
    width: 100%;
    margin-bottom: 3mm;
    padding-bottom: 3.5mm;
    border-bottom: 0.5pt solid #b8953e33;
}

.school-logo-ring {
    width: 18mm;
    height: 18mm;
    border-radius: 50%;
    border: 1.5pt solid #b8953e;
    background: #2d1200;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
    box-shadow: 0 0 0 0.5pt #7b1e1e;
}
.school-logo-ring img {
    width: 90%;
    height: 90%;
    object-fit: contain;
}
.school-logo-ring .logo-fallback {
    font-size: 6pt;
    font-weight: 800;
    color: #b8953e;
    text-align: center;
    line-height: 1.2;
    letter-spacing: 0.3pt;
}

.header-center {
    text-align: center;
    flex: 1;
}
.school-name {
    font-size: 13pt;
    font-weight: bold;
    color: #d4a84b;
    letter-spacing: 2.5pt;
    text-transform: uppercase;
    text-shadow: 0 1px 3px rgba(0,0,0,0.4);
}
.school-sub {
    font-size: 7pt;
    color: #b8953e88;
    letter-spacing: 1.5pt;
    margin-top: 1mm;
    text-transform: uppercase;
}

.student-photo-wrap {
    flex-shrink: 0;
}
.student-photo {
    width: 20mm;
    height: 24mm;
    object-fit: cover;
    border: 1.5pt solid #b8953e;
    display: block;
    box-shadow: 0 2px 8px rgba(0,0,0,0.5);
}
.photo-placeholder {
    width: 20mm;
    height: 24mm;
    border: 1pt dashed #b8953e44;
    background: #2d1200;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 6pt;
    color: #b8953e55;
    text-align: center;
}

/* ── Title ── */
.title-section {
    text-align: center;
    margin-bottom: 3mm;
}
.cert-of {
    font-size: 7.5pt;
    color: #b8953e;
    letter-spacing: 4pt;
    text-transform: uppercase;
    font-style: italic;
    margin-bottom: 1mm;
}
.cert-title {
    font-size: 26pt;
    font-weight: bold;
    color: #d4a84b;
    letter-spacing: 3pt;
    text-transform: uppercase;
    text-shadow: 0 2px 8px rgba(184,149,62,0.3);
    line-height: 1;
    margin-bottom: 1mm;
}
.cert-type {
    font-size: 8pt;
    color: #7b1e1e;
    letter-spacing: 2.5pt;
    text-transform: uppercase;
    background: #b8953e;
    padding: 1mm 5mm;
    display: inline-block;
    border-radius: 999px;
    margin-top: 1.5mm;
}

/* ── Gold divider with star ── */
.gold-divider {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 2mm;
    margin: 3.5mm 0;
    width: 100%;
}
.gold-divider .gline {
    flex: 1;
    max-width: 70mm;
    height: 0;
    border-top: 1pt solid #b8953e;
}
.gold-divider .gstar {
    color: #d4a84b;
    font-size: 9pt;
    line-height: 1;
}

/* ── Preamble ── */
.cert-preamble {
    font-size: 8.5pt;
    color: #b8953e99;
    letter-spacing: 2.5pt;
    text-transform: uppercase;
    font-style: italic;
    margin-bottom: 2mm;
    text-align: center;
}

/* ── Student name ── */
.student-name {
    font-size: 23pt;
    font-weight: bold;
    color: #d4a84b;
    text-align: center;
    letter-spacing: 1.5pt;
    text-shadow: 0 1px 6px rgba(184,149,62,0.25);
    margin-bottom: 1mm;
}
.name-rule {
    display: block;
    width: 85mm;
    border-bottom: 1pt solid #b8953e66;
    margin: 0 auto 4mm;
}

/* ── Body text ── */
.cert-body-text {
    font-size: 9.5pt;
    color: #c4a882;
    line-height: 1.8;
    text-align: center;
    max-width: 195mm;
    margin: 0 auto 4mm;
    font-style: italic;
}

/* ── Meta table ── */
.cert-meta {
    display: flex;
    justify-content: center;
    border-top: 0.5pt solid #b8953e33;
    border-bottom: 0.5pt solid #b8953e33;
    padding: 2mm 0;
    margin: 2mm 0 5mm;
    width: 100%;
}
.meta-cell {
    flex: 1;
    text-align: center;
    padding: 0 3mm;
    border-right: 0.5pt solid #b8953e22;
}
.meta-cell:last-child { border-right: none; }
.meta-label {
    display: block;
    font-size: 6pt;
    color: #b8953e66;
    text-transform: uppercase;
    letter-spacing: 1.5pt;
    margin-bottom: 0.5mm;
    font-family: 'DejaVu Sans', sans-serif;
}
.meta-value {
    display: block;
    font-size: 8.5pt;
    font-weight: bold;
    color: #d4a84b;
    font-family: 'DejaVu Sans', sans-serif;
}

/* ── Footer ── */
.cert-footer {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    width: 100%;
    margin-top: auto;
}

.sig-block {
    text-align: center;
    min-width: 52mm;
}
.sig-img {
    height: 11mm;
    max-width: 36mm;
    display: block;
    margin: 0 auto 1mm;
    object-fit: contain;
}
.sig-line {
    width: 46mm;
    border-top: 0.8pt solid #b8953e66;
    margin: 0 auto 1.5mm;
}
.sig-name  { font-size: 8.5pt; font-weight: bold; color: #d4a84b; font-family: 'DejaVu Sans', sans-serif; }
.sig-title { font-size: 7pt; color: #b8953e88; font-family: 'DejaVu Sans', sans-serif; }

.footer-center {
    text-align: center;
    flex: 1;
}
.seal-outer {
    width: 20mm;
    height: 20mm;
    border-radius: 50%;
    border: 1.5pt double #b8953e;
    background: radial-gradient(circle, #2d1200 50%, #1c0a00);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1mm;
    box-shadow: 0 0 6px rgba(184,149,62,0.2);
}
.seal-text {
    font-size: 5.5pt;
    color: #b8953e;
    text-transform: uppercase;
    letter-spacing: 0.8pt;
    font-weight: bold;
    text-align: center;
    line-height: 1.5;
    font-family: 'DejaVu Sans', sans-serif;
}
.cert-no {
    font-family: 'DejaVu Sans Mono', monospace;
    font-size: 5.5pt;
    color: #b8953e66;
    letter-spacing: 1pt;
    margin-top: 0.5mm;
}

/* ── Watermark ── */
.watermark {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-30deg);
    font-size: 60pt;
    font-weight: 900;
    color: rgba(184,149,62,0.04);
    letter-spacing: 6pt;
    text-transform: uppercase;
    pointer-events: none;
    z-index: 1;
    white-space: nowrap;
}
</style>
</head>
<body>

<div class="cert-wrap">
    <div class="cert-bg-glow"></div>
    <div class="cert-bg-texture"></div>

    {{-- Frames & borders --}}
    <div class="arch-band"></div>
    <div class="arch-band-bottom"></div>
    <div class="frame-outer"></div>
    <div class="frame-inner"></div>
    <div class="frame-hairline"></div>

    {{-- Corner medallion dots --}}
    <div class="corner-dot tl"></div>
    <div class="corner-dot tr"></div>
    <div class="corner-dot bl"></div>
    <div class="corner-dot br"></div>

    {{-- Watermark --}}
    <div class="watermark">{{ config('app.name', 'SCHOOL') }}</div>

    {{-- Content --}}
    <div class="cert-content">

        {{-- Header row --}}
        <div class="cert-header">
            <div class="school-logo-ring">
                @if(!empty($logoUrl))
                    <img src="{{ $logoUrl }}" alt="Logo">
                @else
                    <span class="logo-fallback">SCHOOL<br>LOGO</span>
                @endif
            </div>
            <div class="header-center">
                <div class="school-name">{{ $schoolName ?? config('app.name', 'NepalSchoolHub') }}</div>
                <div class="school-sub">{{ $schoolAddress ?? 'Established in Excellence' }}</div>
            </div>
            <div class="student-photo-wrap">
                @if(!empty($photoUrl))
                    <img src="{{ $photoUrl }}" class="student-photo" alt="Student">
                @else
                    <div class="photo-placeholder">Student<br>Photo</div>
                @endif
            </div>
        </div>

        {{-- Medallion --}}
        <div class="medallion-wrap">
            <div class="medallion">
                <span class="medallion-star">★</span>
            </div>
        </div>

        {{-- Title --}}
        <div class="title-section">
            <div class="cert-of">proudly presents the</div>
            <div class="cert-title">{{ $template->header_text ?? 'Certificate of Excellence' }}</div>
            <span class="cert-type">{{ $template->typeLabel() ?? 'Academic Achievement' }}</span>
        </div>

        {{-- Gold divider --}}
        <div class="gold-divider">
            <div class="gline"></div>
            <span class="gstar">✦</span>
            <span class="gstar" style="font-size:13pt;">★</span>
            <span class="gstar">✦</span>
            <div class="gline"></div>
        </div>

        {{-- Preamble --}}
        <p class="cert-preamble">This is to certify that</p>

        {{-- Student name --}}
        <div class="student-name">{{ $fullName ?? 'Student Full Name' }}</div>
        <span class="name-rule"></span>

        {{-- Body text --}}
        <div class="cert-body-text">
            {!! nl2br(e($bodyText ?? 'has achieved outstanding academic distinction and exemplary conduct, demonstrating the highest standards of scholarship and integrity throughout the academic year. This honour is awarded in recognition of their remarkable achievement.')) !!}
        </div>

        {{-- Meta --}}
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
                <span class="meta-label">Issued On</span>
                <span class="meta-value">{{ isset($issued) ? $issued->issued_date->format('d M Y') : now()->format('d M Y') }}</span>
            </div>
        </div>

        {{-- Footer --}}
        <div class="cert-footer">
            <div class="sig-block">
                <div style="height:11mm;"></div>
                <div class="sig-line"></div>
                <div class="sig-name">Class Teacher</div>
                <div class="sig-title">Homeroom Instructor</div>
            </div>

            <div class="footer-center">
                <div class="seal-outer">
                    <div class="seal-text">OFFICIAL<br>SEAL</div>
                </div>
                @if(isset($issued))
                    <div class="cert-no">{{ $issued->certificate_number }}</div>
                @endif
            </div>

            <div class="sig-block">
                @if(!empty($sigImgUrl))
                    <img src="{{ $sigImgUrl }}" class="sig-img" alt="Signature">
                @else
                    <div style="height:11mm;"></div>
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
