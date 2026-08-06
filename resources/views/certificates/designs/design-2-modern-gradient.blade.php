<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Certificate – Modern Gradient</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    background: #e8eaf0;
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
    background: #ffffff;
    position: relative;
    overflow: hidden;
    box-shadow: 0 12px 60px rgba(0,0,0,0.18);
    border-radius: 3mm;
}

/* ── Gradient header band ── */
.header-band {
    width: 100%;
    height: 52mm;
    background: linear-gradient(135deg, #6c2eb9 0%, #8b5cf6 35%, #0ea5e9 70%, #06b6d4 100%);
    position: relative;
    overflow: hidden;
}

/* decorative circles inside header */
.header-band::before {
    content: '';
    position: absolute;
    top: -20mm;
    right: -20mm;
    width: 70mm;
    height: 70mm;
    border-radius: 50%;
    background: rgba(255,255,255,0.08);
}
.header-band::after {
    content: '';
    position: absolute;
    bottom: -15mm;
    left: 20mm;
    width: 50mm;
    height: 50mm;
    border-radius: 50%;
    background: rgba(255,255,255,0.06);
}

/* ── Ribbon / chevron at bottom of header ── */
.header-ribbon {
    position: absolute;
    bottom: -1px;
    left: 0;
    width: 100%;
    line-height: 0;
}
.header-ribbon svg {
    display: block;
    width: 100%;
}

/* ── Header content ── */
.header-content {
    position: absolute;
    inset: 0;
    z-index: 2;
    display: flex;
    align-items: center;
    padding: 0 16mm;
    gap: 6mm;
}

.school-logo-wrap {
    width: 18mm;
    height: 18mm;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    border: 2pt solid rgba(255,255,255,0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
}
.school-logo-wrap img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}
.school-logo-wrap .logo-text {
    font-size: 7pt;
    font-weight: 800;
    color: #fff;
    text-align: center;
    letter-spacing: 0.5pt;
    line-height: 1.2;
}

.header-titles {
    flex: 1;
}
.school-name {
    font-size: 13pt;
    font-weight: 800;
    color: #ffffff;
    letter-spacing: 2pt;
    text-transform: uppercase;
}
.school-tagline {
    font-size: 7.5pt;
    color: rgba(255,255,255,0.75);
    letter-spacing: 1pt;
    margin-top: 0.8mm;
}

.header-badge {
    flex-shrink: 0;
    text-align: center;
}
.header-badge-ring {
    width: 24mm;
    height: 24mm;
    border-radius: 50%;
    border: 2.5pt solid rgba(255,255,255,0.5);
    background: rgba(255,255,255,0.12);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    font-size: 5.5pt;
    color: rgba(255,255,255,0.9);
    letter-spacing: 0.5pt;
    text-transform: uppercase;
    font-weight: 700;
    line-height: 1.5;
    backdrop-filter: blur(4px);
}
.header-badge-ring .badge-icon {
    font-size: 12pt;
    display: block;
    margin-bottom: 0.5mm;
}

/* ── Student photo (overlaps header/body) ── */
.student-photo-float {
    position: absolute;
    top: 34mm;
    right: 16mm;
    z-index: 10;
}
.student-photo-float img {
    width: 24mm;
    height: 28mm;
    object-fit: cover;
    border-radius: 2mm;
    border: 3pt solid #ffffff;
    box-shadow: 0 4px 16px rgba(108,46,185,0.25);
    display: block;
}
.student-photo-float .photo-placeholder {
    width: 24mm;
    height: 28mm;
    border-radius: 2mm;
    border: 1.5pt dashed #cbd5e1;
    background: #f8fafc;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 6.5pt;
    color: #cbd5e1;
    text-align: center;
}

/* ── Body content ── */
.cert-body {
    padding: 8mm 16mm 6mm;
    position: relative;
}

/* ── Title section ── */
.title-section {
    text-align: center;
    margin-bottom: 5mm;
    margin-top: 2mm;
}

.cert-type-tag {
    display: inline-block;
    background: linear-gradient(90deg, #6c2eb9, #0ea5e9);
    color: #fff;
    font-size: 6.5pt;
    font-weight: 700;
    letter-spacing: 3pt;
    text-transform: uppercase;
    padding: 1.5mm 5mm;
    border-radius: 999px;
    margin-bottom: 2.5mm;
}

.cert-title {
    font-size: 24pt;
    font-weight: 800;
    color: #1e1b4b;
    letter-spacing: 2pt;
    text-transform: uppercase;
    line-height: 1.1;
}

/* ── Thin gradient underline ── */
.title-underline {
    height: 2.5pt;
    width: 50mm;
    margin: 2.5mm auto 0;
    border-radius: 999px;
    background: linear-gradient(90deg, #6c2eb9, #06b6d4);
}

/* ── Preamble ── */
.cert-preamble {
    text-align: center;
    font-size: 8.5pt;
    color: #64748b;
    letter-spacing: 2.5pt;
    text-transform: uppercase;
    margin: 4.5mm 0 2.5mm;
}

/* ── Student name ── */
.student-name {
    text-align: center;
    font-size: 22pt;
    font-weight: 700;
    color: #6c2eb9;
    margin-bottom: 1mm;
    letter-spacing: 1pt;
}
.name-line {
    display: block;
    width: 80mm;
    height: 1.5pt;
    background: linear-gradient(90deg, transparent, #0ea5e933, transparent);
    margin: 0 auto 4mm;
}

/* ── Achievement text ── */
.achievement-text {
    font-size: 9.5pt;
    color: #475569;
    line-height: 1.75;
    text-align: center;
    max-width: 180mm;
    margin: 0 auto 4mm;
}

/* ── Pill info row ── */
.info-pills {
    display: flex;
    justify-content: center;
    gap: 3mm;
    flex-wrap: wrap;
    margin-bottom: 5mm;
}
.info-pill {
    display: flex;
    align-items: center;
    gap: 1.5mm;
    background: #f1f5f9;
    border-radius: 999px;
    padding: 1.5mm 4mm;
    font-size: 7.5pt;
}
.pill-label {
    color: #94a3b8;
    font-size: 6.5pt;
    text-transform: uppercase;
    letter-spacing: 0.8pt;
}
.pill-value {
    font-weight: 700;
    color: #334155;
}
.pill-dot {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6c2eb9, #06b6d4);
    flex-shrink: 0;
}

/* ── Separator ── */
.separator {
    height: 1pt;
    background: linear-gradient(90deg, transparent, #e2e8f0 20%, #e2e8f0 80%, transparent);
    margin: 0 0 5mm;
}

/* ── Footer ── */
.cert-footer {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    padding: 0 16mm 8mm;
}

.sig-block {
    text-align: center;
    min-width: 50mm;
}
.sig-image {
    height: 11mm;
    max-width: 38mm;
    display: block;
    margin: 0 auto 1mm;
    object-fit: contain;
}
.sig-line {
    width: 48mm;
    border-top: 1pt solid #cbd5e1;
    margin: 0 auto 1.5mm;
}
.sig-name  { font-size: 8.5pt; font-weight: 700; color: #1e293b; }
.sig-title { font-size: 7pt; color: #94a3b8; }

/* ── QR + cert number ── */
.footer-qr-block {
    text-align: center;
}
.qr-img {
    width: 16mm;
    height: 16mm;
    display: block;
    margin: 0 auto 1mm;
}
.cert-no {
    font-family: 'DejaVu Sans Mono', monospace;
    font-size: 6pt;
    color: #94a3b8;
    letter-spacing: 1pt;
}

/* ── Bottom accent bar ── */
.bottom-bar {
    height: 2.5mm;
    background: linear-gradient(90deg, #6c2eb9 0%, #8b5cf6 35%, #0ea5e9 70%, #06b6d4 100%);
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
}

/* ── Left/right side thin lines ── */
.side-bar-left {
    position: absolute;
    top: 52mm;
    bottom: 2.5mm;
    left: 0;
    width: 2mm;
    background: linear-gradient(180deg, #6c2eb9, #06b6d4);
}
.side-bar-right {
    position: absolute;
    top: 52mm;
    bottom: 2.5mm;
    right: 0;
    width: 2mm;
    background: linear-gradient(180deg, #06b6d4, #6c2eb9);
}

/* ── Watermark ── */
.watermark {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-30deg);
    font-size: 64pt;
    font-weight: 900;
    color: rgba(108,46,185,0.035);
    text-transform: uppercase;
    letter-spacing: 6pt;
    pointer-events: none;
    z-index: 1;
    white-space: nowrap;
}
</style>
</head>
<body>

<div class="cert-wrap">

    {{-- Side accent bars --}}
    <div class="side-bar-left"></div>
    <div class="side-bar-right"></div>
    <div class="bottom-bar"></div>

    {{-- Watermark --}}
    <div class="watermark">{{ config('app.name', 'SCHOOL') }}</div>

    {{-- Gradient header --}}
    <div class="header-band">
        <div class="header-content">
            <div class="school-logo-wrap">
                @if(!empty($logoUrl))
                    <img src="{{ $logoUrl }}" alt="Logo">
                @else
                    <span class="logo-text">SCHOOL<br>LOGO</span>
                @endif
            </div>
            <div class="header-titles">
                <div class="school-name">{{ $schoolName ?? config('app.name', 'NepalSchoolHub') }}</div>
                <div class="school-tagline">{{ $schoolAddress ?? 'Inspiring Excellence, Shaping Futures' }}</div>
            </div>
            <div class="header-badge">
                <div class="header-badge-ring">
                    <span class="badge-icon">★</span>
                    AWARD<br>OF<br>MERIT
                </div>
            </div>
        </div>
        {{-- Wave ribbon --}}
        <div class="header-ribbon">
            <svg viewBox="0 0 1200 24" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0,0 C200,24 400,0 600,16 C800,32 1000,8 1200,16 L1200,24 L0,24 Z"
                      fill="#ffffff"/>
            </svg>
        </div>
    </div>

    {{-- Floating student photo --}}
    <div class="student-photo-float">
        @if(!empty($photoUrl))
            <img src="{{ $photoUrl }}" alt="Student Photo">
        @else
            <div class="photo-placeholder">Student<br>Photo</div>
        @endif
    </div>

    {{-- Body --}}
    <div class="cert-body">

        <div class="title-section">
            <span class="cert-type-tag">{{ $template->typeLabel() ?? 'Academic Certificate' }}</span>
            <div class="cert-title">{{ $template->header_text ?? 'Certificate of Achievement' }}</div>
            <div class="title-underline"></div>
        </div>

        <p class="cert-preamble">proudly presented to</p>

        <div class="student-name">{{ $fullName ?? 'Student Full Name' }}</div>
        <span class="name-line"></span>

        <div class="achievement-text">
            {!! nl2br(e($bodyText ?? 'In recognition of exceptional academic performance, dedication to learning, and outstanding contribution to the school community during the academic year. This certificate is awarded as a mark of distinction and excellence.')) !!}
        </div>

        <div class="info-pills">
            <div class="info-pill">
                <div class="pill-dot"></div>
                <span class="pill-label">Class &nbsp;</span>
                <span class="pill-value">{{ $classStr ?? 'Grade X' }}</span>
            </div>
            <div class="info-pill">
                <div class="pill-dot"></div>
                <span class="pill-label">Adm. No &nbsp;</span>
                <span class="pill-value">{{ $admissionNo ?? 'ADM-0001' }}</span>
            </div>
            <div class="info-pill">
                <div class="pill-dot"></div>
                <span class="pill-label">Year &nbsp;</span>
                <span class="pill-value">{{ $academicYr ?? '2081–82' }}</span>
            </div>
            <div class="info-pill">
                <div class="pill-dot"></div>
                <span class="pill-label">Date &nbsp;</span>
                <span class="pill-value">{{ isset($issued) ? $issued->issued_date->format('d M Y') : now()->format('d M Y') }}</span>
            </div>
        </div>

        <div class="separator"></div>
    </div>

    {{-- Footer --}}
    <div class="cert-footer">
        <div class="sig-block">
            <div style="height:11mm;"></div>
            <div class="sig-line"></div>
            <div class="sig-name">Class Teacher</div>
            <div class="sig-title">Homeroom Instructor</div>
        </div>

        <div class="footer-qr-block">
            @if(!empty($qrUrl) && isset($issued))
                <img src="{{ $qrUrl }}" class="qr-img" alt="Verify">
            @endif
            @if(isset($issued))
                <div class="cert-no">{{ $issued->certificate_number }}</div>
            @endif
        </div>

        <div class="sig-block">
            @if(!empty($sigImgUrl))
                <img src="{{ $sigImgUrl }}" class="sig-image" alt="Signature">
            @else
                <div style="height:11mm;"></div>
            @endif
            <div class="sig-line"></div>
            <div class="sig-name">{{ $template->signature_name ?? 'Principal' }}</div>
            <div class="sig-title">{{ $template->signature_title ?? 'Head of Institution' }}</div>
        </div>
    </div>

</div>

</body>
</html>
