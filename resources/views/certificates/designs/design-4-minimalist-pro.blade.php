<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Certificate – Minimalist Professional</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    background: #d1d5db;
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
    box-shadow: 0 4px 40px rgba(0,0,0,0.12);
    display: flex;
}

/* ── Left accent bar ── */
.left-bar {
    width: 10mm;
    min-height: 100%;
    background: #1e293b;
    flex-shrink: 0;
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

/* Vertical text on the bar */
.left-bar-text {
    color: rgba(255,255,255,0.12);
    font-size: 7pt;
    font-weight: 800;
    letter-spacing: 4pt;
    text-transform: uppercase;
    writing-mode: vertical-rl;
    transform: rotate(180deg);
    white-space: nowrap;
    user-select: none;
}

/* Thin accent strip inside the bar */
.left-bar-strip {
    position: absolute;
    right: 0;
    top: 0;
    bottom: 0;
    width: 2.5mm;
    background: linear-gradient(180deg, #3b82f6 0%, #1d4ed8 50%, #1e293b 100%);
}

/* ── Right content area ── */
.cert-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    padding: 12mm 14mm 12mm 10mm;
    position: relative;
}

/* ── Top right corner block ── */
.corner-accent {
    position: absolute;
    top: 0;
    right: 0;
    width: 22mm;
    height: 22mm;
    overflow: hidden;
}
.corner-accent::before {
    content: '';
    position: absolute;
    top: -11mm;
    right: -11mm;
    width: 22mm;
    height: 22mm;
    background: #1e293b;
    transform: rotate(45deg);
}

/* ── Header row ── */
.cert-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 7mm;
    padding-bottom: 5mm;
    border-bottom: 1.5pt solid #f1f5f9;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 4mm;
}

.school-logo {
    width: 14mm;
    height: 14mm;
    object-fit: contain;
    display: block;
}
.school-logo-placeholder {
    width: 14mm;
    height: 14mm;
    background: #1e293b;
    border-radius: 2mm;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 5.5pt;
    font-weight: 800;
    color: #fff;
    text-align: center;
    line-height: 1.2;
    flex-shrink: 0;
}
.school-info {
    flex: 1;
}
.school-name {
    font-size: 11pt;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: 1pt;
    text-transform: uppercase;
    line-height: 1.2;
}
.school-addr {
    font-size: 7pt;
    color: #94a3b8;
    margin-top: 0.5mm;
    letter-spacing: 0.3pt;
}

.header-right {
    text-align: right;
    flex-shrink: 0;
}

/* Issue info chip */
.issue-chip {
    display: inline-block;
    background: #f8fafc;
    border: 0.5pt solid #e2e8f0;
    border-radius: 1mm;
    padding: 1.5mm 3mm;
    font-size: 7pt;
    color: #64748b;
    text-align: right;
    line-height: 1.6;
}
.issue-chip strong {
    display: block;
    font-size: 8pt;
    color: #1e293b;
    font-weight: 700;
}

/* ── Blue rule divider ── */
.blue-rule {
    height: 2.5pt;
    background: linear-gradient(90deg, #1d4ed8 0%, #3b82f6 60%, #e0f2fe 100%);
    border-radius: 999px;
    margin-bottom: 7mm;
}

/* ── Title + type ── */
.title-row {
    display: flex;
    align-items: baseline;
    gap: 3mm;
    margin-bottom: 2mm;
}
.cert-type-tag {
    font-size: 7pt;
    font-weight: 700;
    letter-spacing: 2.5pt;
    text-transform: uppercase;
    color: #3b82f6;
    white-space: nowrap;
}
.title-divider-dot {
    width: 4px;
    height: 4px;
    background: #cbd5e1;
    border-radius: 50%;
    flex-shrink: 0;
    margin-bottom: 1mm;
}

.cert-title {
    font-size: 22pt;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: 1pt;
    text-transform: uppercase;
    line-height: 1.05;
    margin-bottom: 1mm;
}
.cert-title-thin {
    font-size: 22pt;
    font-weight: 300;
    color: #94a3b8;
    letter-spacing: 1pt;
    text-transform: uppercase;
    line-height: 1.05;
}

/* ── Preamble ── */
.cert-preamble {
    font-size: 8pt;
    color: #64748b;
    letter-spacing: 2pt;
    text-transform: uppercase;
    margin: 5mm 0 1.5mm;
}

/* ── Student name block ── */
.name-block {
    display: flex;
    align-items: flex-end;
    gap: 5mm;
    margin-bottom: 4mm;
    padding-bottom: 3mm;
    border-bottom: 1.5pt solid #1e293b;
}
.student-name {
    font-size: 24pt;
    font-weight: 800;
    color: #1d4ed8;
    letter-spacing: 0.5pt;
    flex: 1;
    line-height: 1.05;
}
.student-photo {
    width: 20mm;
    height: 24mm;
    object-fit: cover;
    display: block;
    border: 1pt solid #e2e8f0;
    flex-shrink: 0;
}
.photo-placeholder {
    width: 20mm;
    height: 24mm;
    background: #f8fafc;
    border: 1pt dashed #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 6.5pt;
    color: #cbd5e1;
    text-align: center;
    flex-shrink: 0;
}

/* ── Body text ── */
.cert-body {
    font-size: 9pt;
    color: #475569;
    line-height: 1.75;
    margin-bottom: 5mm;
    max-width: 190mm;
}

/* ── Meta grid (2-column) ── */
.meta-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 2mm 4mm;
    background: #f8fafc;
    border: 0.5pt solid #f1f5f9;
    border-radius: 2mm;
    padding: 3mm 4mm;
    margin-bottom: 6mm;
}
.meta-item {}
.meta-label {
    font-size: 6pt;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 1.2pt;
    display: block;
    margin-bottom: 0.3mm;
}
.meta-value {
    font-size: 8.5pt;
    font-weight: 700;
    color: #1e293b;
    display: block;
}

/* ── Footer ── */
.cert-footer {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    margin-top: auto;
    padding-top: 4mm;
    border-top: 0.5pt solid #f1f5f9;
}

.sig-block {
    text-align: left;
    min-width: 50mm;
}
.sig-image {
    height: 10mm;
    max-width: 36mm;
    display: block;
    margin-bottom: 1mm;
    object-fit: contain;
}
.sig-line {
    width: 46mm;
    border-top: 1pt solid #e2e8f0;
    margin-bottom: 1.5mm;
}
.sig-name  { font-size: 8.5pt; font-weight: 700; color: #0f172a; }
.sig-title { font-size: 7pt; color: #94a3b8; }

.footer-mid {
    text-align: center;
    flex: 1;
}
.qr-img {
    width: 15mm;
    height: 15mm;
    display: block;
    margin: 0 auto 1mm;
}
.cert-no-label {
    font-size: 6pt;
    color: #cbd5e1;
    text-transform: uppercase;
    letter-spacing: 1pt;
    display: block;
}
.cert-no {
    font-family: 'DejaVu Sans Mono', monospace;
    font-size: 6.5pt;
    color: #94a3b8;
    letter-spacing: 0.8pt;
}

.footer-right {
    text-align: right;
}

/* Verification chip */
.verify-chip {
    display: inline-flex;
    align-items: center;
    gap: 1.5mm;
    border: 0.5pt solid #dbeafe;
    background: #eff6ff;
    border-radius: 999px;
    padding: 1mm 3mm;
    font-size: 6.5pt;
    color: #1d4ed8;
    font-weight: 700;
    letter-spacing: 0.5pt;
    margin-bottom: 2mm;
}
.verify-dot {
    width: 4px;
    height: 4px;
    background: #3b82f6;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

/* ── Subtle dot-grid background ── */
.dot-grid {
    position: absolute;
    inset: 0;
    background-image: radial-gradient(circle, #e2e8f0 1px, transparent 1px);
    background-size: 6mm 6mm;
    background-position: 3mm 3mm;
    opacity: 0.35;
    z-index: 0;
    pointer-events: none;
}

/* ── Watermark ── */
.watermark {
    position: absolute;
    top: 50%;
    left: 55%;
    transform: translate(-50%, -50%) rotate(-20deg);
    font-size: 50pt;
    font-weight: 900;
    color: rgba(30,41,59,0.025);
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

    {{-- Left accent bar --}}
    <div class="left-bar">
        <div class="left-bar-text">{{ config('app.name', 'SCHOOL') }}</div>
        <div class="left-bar-strip"></div>
    </div>

    {{-- Main content --}}
    <div class="cert-main">

        {{-- Dot grid & watermark --}}
        <div class="dot-grid"></div>
        <div class="watermark">CERTIFIED</div>

        {{-- Top right corner accent --}}
        <div class="corner-accent"></div>

        {{-- Header --}}
        <div class="cert-header">
            <div class="header-left">
                @if(!empty($logoUrl))
                    <img src="{{ $logoUrl }}" class="school-logo" alt="Logo">
                @else
                    <div class="school-logo-placeholder">LOGO</div>
                @endif
                <div class="school-info">
                    <div class="school-name">{{ $schoolName ?? config('app.name', 'NepalSchoolHub') }}</div>
                    <div class="school-addr">{{ $schoolAddress ?? 'Excellence in Education' }}</div>
                </div>
            </div>
            <div class="header-right">
                <div class="issue-chip">
                    <strong>{{ isset($issued) ? $issued->issued_date->format('d M Y') : now()->format('d M Y') }}</strong>
                    Date of Issue
                </div>
            </div>
        </div>

        {{-- Blue rule --}}
        <div class="blue-rule"></div>

        {{-- Certificate title --}}
        <div class="title-row">
            <span class="cert-type-tag">{{ $template->typeLabel() ?? 'Academic' }}</span>
            <div class="title-divider-dot"></div>
        </div>
        <div>
            <span class="cert-title">Certificate</span>
            <span class="cert-title-thin"> of Achievement</span>
        </div>

        {{-- Preamble --}}
        <p class="cert-preamble">This is to certify that</p>

        {{-- Name + photo --}}
        <div class="name-block">
            <div class="student-name">{{ $fullName ?? 'Student Full Name' }}</div>
            @if(!empty($photoUrl))
                <img src="{{ $photoUrl }}" class="student-photo" alt="Student">
            @else
                <div class="photo-placeholder">Photo</div>
            @endif
        </div>

        {{-- Body text --}}
        <div class="cert-body">
            {!! nl2br(e($bodyText ?? 'has successfully completed the academic requirements and demonstrated outstanding performance, exemplary character, and a steadfast commitment to excellence during the academic year. This certificate is issued in formal recognition of their achievement.')) !!}
        </div>

        {{-- Meta grid --}}
        <div class="meta-grid">
            <div class="meta-item">
                <span class="meta-label">Class</span>
                <span class="meta-value">{{ $classStr ?? 'Grade X' }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Admission No.</span>
                <span class="meta-value">{{ $admissionNo ?? 'ADM-0001' }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Academic Year</span>
                <span class="meta-value">{{ $academicYr ?? '2081–82' }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Roll No.</span>
                <span class="meta-value">{{ $rollNo ?? $promotion?->roll_no ?? '—' }}</span>
            </div>
        </div>

        {{-- Footer --}}
        <div class="cert-footer">

            {{-- Left sig --}}
            <div class="sig-block">
                <div style="height:10mm;"></div>
                <div class="sig-line"></div>
                <div class="sig-name">Class Teacher</div>
                <div class="sig-title">Homeroom Instructor</div>
            </div>

            {{-- Centre QR / cert no --}}
            <div class="footer-mid">
                @if(!empty($qrUrl) && isset($issued))
                    <img src="{{ $qrUrl }}" class="qr-img" alt="Verify">
                @endif
                @if(isset($issued))
                    <span class="cert-no-label">Certificate No.</span>
                    <span class="cert-no">{{ $issued->certificate_number }}</span>
                @endif
            </div>

            {{-- Right sig --}}
            <div class="footer-right">
                <div class="verify-chip">
                    <span class="verify-dot"></span>
                    Digitally Verified
                </div>
                <div class="sig-block" style="text-align:right;">
                    @if(!empty($sigImgUrl))
                        <img src="{{ $sigImgUrl }}" class="sig-image" alt="Signature" style="margin-left:auto;">
                    @else
                        <div style="height:10mm;"></div>
                    @endif
                    <div class="sig-line" style="margin-left:auto; margin-right:0;"></div>
                    <div class="sig-name">{{ $template->signature_name ?? 'Principal' }}</div>
                    <div class="sig-title">{{ $template->signature_title ?? 'Head of Institution' }}</div>
                </div>
            </div>

        </div>

    </div>{{-- /cert-main --}}
</div>{{-- /cert-wrap --}}

</body>
</html>
