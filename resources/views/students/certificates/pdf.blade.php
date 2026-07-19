<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: DejaVu Sans, sans-serif; background: #fff; }
.cert {
    width: 277mm; height: 190mm;
    padding: 18mm 22mm;
    position: relative;
    border: 4mm solid #4f46e5;
    outline: 1mm solid #e0e7ff;
    outline-offset: -6mm;
}
.corner {
    position: absolute; width: 16mm; height: 16mm;
    border-color: #7c3aed; border-style: solid;
}
.corner.tl { top: 4mm; left: 4mm; border-width: 2mm 0 0 2mm; }
.corner.tr { top: 4mm; right: 4mm; border-width: 2mm 2mm 0 0; }
.corner.bl { bottom: 4mm; left: 4mm; border-width: 0 0 2mm 2mm; }
.corner.br { bottom: 4mm; right: 4mm; border-width: 0 2mm 2mm 0; }

.header-line { border-bottom: 0.5mm solid #e0e7ff; padding-bottom: 8mm; margin-bottom: 8mm; text-align: center; }
.school { font-size: 13pt; font-weight: bold; color: #4f46e5; letter-spacing: 1pt; text-transform: uppercase; }
.cert-type { font-size: 22pt; font-weight: bold; color: #1e1b4b; margin: 4mm 0; letter-spacing: 1pt; text-transform: uppercase; }
.header-sub { font-size: 9pt; color: #6b7280; letter-spacing: 1pt; text-transform: uppercase; }

.body-text { font-size: 11pt; color: #374151; line-height: 1.7; text-align: center; margin: 6mm 0; }
.student-name { font-size: 18pt; font-weight: bold; color: #4f46e5; margin: 4mm 0; text-align: center; border-bottom: 0.5mm solid #c7d2fe; padding-bottom: 3mm; display: inline-block; min-width: 120mm; }
.extra { font-size: 9pt; color: #6b7280; text-align: center; margin-top: 3mm; font-style: italic; }

.footer { position: absolute; bottom: 14mm; left: 22mm; right: 22mm; display: flex; justify-content: space-between; align-items: flex-end; }
.sig-block { text-align: center; }
.sig-line { border-top: 0.4mm solid #4b5563; width: 50mm; margin: 0 auto 2mm; }
.sig-name  { font-size: 9pt; font-weight: bold; color: #1f2937; }
.sig-title { font-size: 8pt; color: #6b7280; }
.date-block { text-align: center; font-size: 9pt; color: #6b7280; }
.seal { width: 20mm; height: 20mm; border-radius: 50%; background: #e0e7ff; display: flex; align-items: center; justify-content: center; }
</style>
</head>
<body>
<div class="cert">
    <div class="corner tl"></div><div class="corner tr"></div>
    <div class="corner bl"></div><div class="corner br"></div>

    <div class="header-line">
        <div class="school">{{ config('app.name') }}</div>
        <div class="cert-type">{{ $template->header_text ?: 'Certificate' }}</div>
        <div class="header-sub">{{ \App\Models\CertificateTemplate::TYPES[$template->type] ?? $template->type }}</div>
    </div>

    <div style="text-align:center; margin-bottom: 4mm;">
        <span class="header-sub">This is to certify that</span>
        <br>
        <span class="student-name">{{ $student->full_name }}</span>
    </div>

    <div class="body-text">{!! nl2br(e($bodyText)) !!}</div>

    @if($template->footer_text)
    <div class="extra">{{ $template->footer_text }}</div>
    @endif

    <div class="footer">
        <div class="date-block">
            <p>Date of Issue</p>
            <p style="font-weight:bold; color:#1f2937; margin-top:1mm;">{{ now()->format('F j, Y') }}</p>
        </div>

        @if($template->signature_name)
        <div class="sig-block">
            <div class="sig-line"></div>
            <div class="sig-name">{{ $template->signature_name }}</div>
            @if($template->signature_title)
            <div class="sig-title">{{ $template->signature_title }}</div>
            @endif
        </div>
        @endif

        <div class="date-block" style="font-size:8pt; color:#9ca3af;">
            Cert. ID: {{ strtoupper(substr(md5($student->id . $template->id . now()->toDateString()), 0, 10)) }}
        </div>
    </div>
</div>
</body>
</html>
