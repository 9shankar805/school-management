<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>{{ $paper->title }}</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16/dist/katex.min.css">
<script src="https://cdn.jsdelivr.net/npm/katex@0.16/dist/katex.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/katex@0.16/dist/contrib/auto-render.min.js"></script>
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'DejaVu Sans',sans-serif;font-size:11px;color:#1e293b;padding:28px;}
h1{font-size:16px;font-weight:800;}h2{font-size:13px;font-weight:700;}h3{font-size:12px;font-weight:700;}
.watermark{position:fixed;top:50%;left:50%;transform:translate(-50%,-50%) rotate(-35deg);font-size:64px;color:rgba(79,70,229,.07);font-weight:900;letter-spacing:.08em;pointer-events:none;z-index:0;}
.header{text-align:center;border-bottom:2px solid #4f46e5;padding-bottom:12px;margin-bottom:14px;}
.header h1{font-size:16px;font-weight:800;color:#1e293b;}
.header p{font-size:10px;color:#64748b;margin-top:2px;}
/* Default header: uses inline HTML attributes for DomPDF — see table below */
.dh-body-cell h1{font-size:16px;font-weight:800;color:#1e293b;margin:0 0 2px;}
.dh-body-cell .addr{font-size:10px;color:#64748b;margin:0 0 2px;}
.dh-body-cell .exam{font-size:11px;font-weight:700;color:#3730a3;margin:4px 0 0;}
.meta-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:5px 14px;background:#f8fafc;border-radius:8px;padding:8px 12px;margin-bottom:12px;font-size:10px;}
.meta-row{display:flex;gap:4px;}
.lbl{color:#94a3b8;white-space:nowrap;}
.val{font-weight:600;}
.instructions{border:1px solid #e2e8f0;border-radius:8px;padding:7px 12px;margin-bottom:12px;font-size:10px;color:#475569;}
.instructions strong{display:block;color:#1e293b;margin-bottom:3px;}
.section-header{background:#e0e7ff;color:#3730a3;padding:5px 12px;border-radius:6px;font-weight:700;font-size:11px;margin:14px 0 8px;display:flex;justify-content:space-between;}
.question{margin-bottom:10px;padding-left:0;}
.q-num{font-weight:700;color:#4f46e5;margin-right:4px;}
.q-body{display:inline;}
.marks-float{float:right;background:#e0e7ff;color:#3730a3;font-size:9px;font-weight:700;padding:2px 8px;border-radius:999px;margin-left:8px;}
.options{margin:5px 0 0 20px;}
.options li{margin-bottom:3px;font-size:10px;color:#475569;}
.answer-line{border-bottom:1px solid #cbd5e1;margin-top:8px;height:18px;}
.footer{text-align:center;font-size:9px;color:#94a3b8;border-top:1px solid #e2e8f0;padding-top:8px;margin-top:20px;}
/* Math rendered via KaTeX inline */
.katex{font-size:1.1em;}
/* Images from question content */
.tiptap-editor img,.q-body img{max-width:220px;max-height:180px;object-fit:contain;border-radius:6px;margin:4px 0;}
</style>
</head>
<body>

@if($paper->template?->show_watermark && $paper->template->watermark_text)
<div class="watermark">{{ strtoupper($paper->template->watermark_text) }}</div>
@endif

{{-- Resolve school info: template overrides > school profile > app name --}}
@php
  $schoolName    = $template?->school_name    ?: ($school?->name    ?? config('app.name'));
  $schoolAddress = $template?->school_address ?: (trim(implode(', ', array_filter([
      $school?->address, $school?->city, $school?->district, $school?->province
  ]))) ?: '');

  // Convert school logo to base64 data URI — bypasses DomPDF chroot/remote restrictions entirely
  $schoolLogoImgTag = '';
  if ($school?->logo) {
      $absPath = storage_path('app/public/' . ltrim($school->logo, '/'));
      if (file_exists($absPath)) {
          $mime     = mime_content_type($absPath) ?: 'image/jpeg';
          $b64      = base64_encode(file_get_contents($absPath));
          $dataUri  = 'data:' . $mime . ';base64,' . $b64;
          $schoolLogoImgTag = '<img src="' . $dataUri . '" width="60" height="60" style="object-fit:contain;" alt="' . e($schoolName) . '">';
      }
  }

  // Placeholder map — covers both {{lower}} and {{UPPER}} variants
  $placeholders = [
    '{{school_name}}'    => $schoolName,
    '{{SCHOOL_NAME}}'    => $schoolName,
    '{{school_address}}' => $schoolAddress,
    '{{SCHOOL_ADDRESS}}' => $schoolAddress,
    '{{exam_name}}'      => $paper->exam_name   ?? '',
    '{{EXAM_NAME}}'      => $paper->exam_name   ?? '',
    '{{subject}}'        => $paper->subject     ?? '',
    '{{SUBJECT}}'        => $paper->subject     ?? '',
    '{{class}}'          => $paper->class_label ?? '',
    '{{CLASS}}'          => $paper->class_label ?? '',
    '{{time}}'           => $paper->duration    ?? '',
    '{{TIME}}'           => $paper->duration    ?? '',
    '{{full_marks}}'     => $paper->full_marks   ?? '',
    '{{FULL_MARKS}}'     => $paper->full_marks   ?? '',
    '{{pass_marks}}'     => $paper->pass_marks   ?? '',
    '{{PASS_MARKS}}'     => $paper->pass_marks   ?? '',
    '{{date}}'           => $paper->exam_date?->format('d M Y') ?? '',
    '{{DATE}}'           => $paper->exam_date?->format('d M Y') ?? '',
    '{{school_logo}}'    => $schoolLogoImgTag ? str_replace('<img ', '<img style="height:56px;width:56px;object-fit:contain;vertical-align:middle;" ', $schoolLogoImgTag) : '',
    '{{SCHOOL_LOGO}}'    => $schoolLogoImgTag ? str_replace('<img ', '<img style="height:56px;width:56px;object-fit:contain;vertical-align:middle;" ', $schoolLogoImgTag) : '',
  ];
@endphp

{{-- Header --}}
@if($template && $template->header_html)
{{-- Custom HTML header from template — all placeholders substituted --}}
<div class="header">
  {!! $template->renderHeader($placeholders) !!}
</div>
@else
{{-- Default header: logo pinned left, school name + address centred, spacer mirrors logo width --}}
<table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;border-bottom:2px solid #4f46e5;padding-bottom:10px;margin-bottom:14px;">
  <tr>
    <td width="70" valign="middle" align="left" style="padding-bottom:10px;">
      @if($schoolLogoImgTag)
        {!! $schoolLogoImgTag !!}
      @endif
    </td>
    <td valign="middle" align="center" style="padding-bottom:10px;">
      <div style="font-size:17px;font-weight:800;color:#0f172a;margin:0 0 2px;">{{ $schoolName }}</div>
      @if($schoolAddress)
        <div style="font-size:10px;color:#64748b;margin:0 0 2px;">{{ $schoolAddress }}</div>
      @endif
      @if($paper->exam_name)
        <div style="font-size:11px;font-weight:700;color:#3730a3;margin-top:4px;">{{ $paper->exam_name }}</div>
      @endif
    </td>
    <td width="70" valign="middle" style="padding-bottom:10px;"></td>{{-- balances logo column --}}
  </tr>
</table>
@endif

{{-- Meta grid --}}
<div class="meta-grid">
  <div class="meta-row"><span class="lbl">Subject:</span><span class="val">{{ $paper->subject ?? '—' }}</span></div>
  <div class="meta-row"><span class="lbl">Class:</span><span class="val">{{ $paper->class_label ?? '—' }}</span></div>
  <div class="meta-row"><span class="lbl">Date:</span><span class="val">{{ $paper->exam_date?->format('d M Y') ?? '—' }}</span></div>
  <div class="meta-row"><span class="lbl">Time:</span><span class="val">{{ $paper->duration ?? '—' }}</span></div>
  <div class="meta-row"><span class="lbl">Full Marks:</span><span class="val">{{ $paper->full_marks ?: $paper->total_marks }}</span></div>
  <div class="meta-row"><span class="lbl">Pass Marks:</span><span class="val">{{ $paper->pass_marks ?? '—' }}</span></div>
</div>

{{-- Instructions --}}
@if($template?->instructions_html)
<div class="instructions">
  <strong>Instructions:</strong>
  {!! $template->instructions_html !!}
</div>
@endif

{{-- Sections & Questions --}}
@foreach($paper->sections as $section)
<div class="section-header">
  <span>{{ $section->title }}@if($section->instructions) — {{ $section->instructions }}@endif</span>
  <span>[{{ $section->total_marks }} Marks]</span>
</div>
@foreach($section->questions as $q)
<div class="question">
  <span class="marks-float">[{{ $q->allocated_marks }} M]</span>
  <span class="q-num">{{ $q->numbering }}.</span>
  <span class="q-body">{!! $q->question_text !!}</span>
  @if($q->options)
  <ol class="options" type="A">
    @foreach($q->options as $opt)<li>{{ is_array($opt) ? ($opt['text'] ?? $opt) : $opt }}</li>@endforeach
  </ol>
  @elseif($q->question_type==='true_false')
  <div style="padding-left:20px;margin-top:4px;font-size:10px;color:#475569;">(a) True &nbsp;&nbsp; (b) False</div>
  @elseif(in_array($q->question_type,['short_answer','long_answer','essay','numerical','fill_blank']))
  @for($l=0;$l<($q->question_type==='essay'?5:($q->question_type==='long_answer'?3:1));$l++)
  <div class="answer-line"></div>
  @endfor
  @endif
</div>
@endforeach
@endforeach

{{-- Footer --}}
<div class="footer">
  {{ $schoolName }}
  @if($template?->footer_html) &bull; {!! $template->footer_html !!} @endif
  &bull; {{ $paper->title }}
  @if($template?->signature_name) &bull; {{ $template->signature_name }}@if($template->signature_title), {{ $template->signature_title }}@endif @endif
</div>

{{-- KaTeX auto-render: convert $...$ and $$...$$ in question text --}}
<script>
document.addEventListener('DOMContentLoaded',function(){
  renderMathInElement(document.body,{
    delimiters:[
      {left:'$$',right:'$$',display:true},
      {left:'$',right:'$',display:false},
      {left:'\\(',right:'\\)',display:false},
      {left:'\\[',right:'\\]',display:true}
    ],
    throwOnError:false
  });
});
</script>
</body>
</html>
