<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: DejaVu Sans, sans-serif; }
.cert {
    width:277mm; height:190mm; padding:16mm 20mm;
    border: 3mm solid #4f46e5;
    outline: 0.8mm solid #e0e7ff;
    outline-offset: -5mm;
    page-break-after: always;
    position: relative;
}
.school { font-size:12pt; font-weight:bold; color:#4f46e5; text-transform:uppercase; text-align:center; margin-bottom:2mm; }
.title   { font-size:20pt; font-weight:bold; color:#1e1b4b; text-align:center; text-transform:uppercase; margin-bottom:5mm; border-bottom: 0.5mm solid #e0e7ff; padding-bottom:4mm; }
.name    { font-size:17pt; font-weight:bold; color:#4f46e5; text-align:center; margin:4mm 0; }
.body    { font-size:10pt; color:#374151; line-height:1.7; text-align:center; }
.footer  { position:absolute; bottom:12mm; left:20mm; right:20mm; display:flex; justify-content:space-between; font-size:8pt; color:#6b7280; }
</style>
</head>
<body>
@foreach($items as $item)
<div class="cert">
    <div class="school">{{ config('app.name') }}</div>
    <div class="title">{{ $template->header_text ?: 'Certificate' }}</div>
    <p style="text-align:center; font-size:9pt; color:#6b7280;">This is to certify that</p>
    <div class="name">{{ $item['student']->full_name }}</div>
    <div class="body">{!! nl2br(e($item['bodyText'])) !!}</div>
    @if($template->footer_text)
    <p style="text-align:center; font-size:9pt; color:#6b7280; font-style:italic; margin-top:3mm;">{{ $template->footer_text }}</p>
    @endif
    <div class="footer">
        <span>Issued: {{ now()->format('F j, Y') }}</span>
        @if($template->signature_name)
        <span>{{ $template->signature_name }}{{ $template->signature_title ? ' · ' . $template->signature_title : '' }}</span>
        @endif
        <span>ID: {{ strtoupper(substr(md5($item['student']->id . $template->id), 0, 8)) }}</span>
    </div>
</div>
@endforeach
</body>
</html>
