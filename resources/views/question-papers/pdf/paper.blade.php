<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>{{ $paper->title }}</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:sans-serif; font-size:11px; color:#1e293b; padding:28px; }
.watermark { position:fixed; top:50%; left:50%; transform:translate(-50%,-50%) rotate(-35deg); font-size:64px; color:rgba(79,70,229,.08); font-weight:900; letter-spacing:.08em; pointer-events:none; z-index:0; }
.header { text-align:center; border-bottom:2px solid #4f46e5; padding-bottom:12px; margin-bottom:14px; position:relative; z-index:1; }
.header h1 { font-size:16px; font-weight:800; color:#1e293b; }
.header p  { font-size:10px; color:#64748b; margin-top:2px; }
.meta-grid { display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:6px 16px; background:#f8fafc; border-radius:8px; padding:10px 14px; margin-bottom:14px; font-size:10px; }
.meta-grid .row { display:flex; justify-content:space-between; }
.meta-grid .row .lbl { color:#94a3b8; }
.meta-grid .row .val { font-weight:600; }
.instructions { border:1px solid #e2e8f0; border-radius:8px; padding:8px 12px; margin-bottom:14px; font-size:10px; color:#475569; }
.instructions p { font-weight:600; color:#1e293b; margin-bottom:4px; }
.section-header { background:#e0e7ff; color:#3730a3; padding:6px 12px; border-radius:6px; font-weight:700; font-size:11px; margin-bottom:8px; margin-top:14px; }
.question { margin-bottom:10px; padding-left:0; }
.q-num { font-weight:700; color:#4f46e5; }
.q-text { line-height:1.6; }
.options { margin-top:6px; padding-left:20px; }
.options li { margin-bottom:3px; font-size:10px; color:#475569; }
.answer-line { border-bottom:1px solid #cbd5e1; margin-top:8px; height:18px; }
.marks-badge { float:right; background:#e0e7ff; color:#3730a3; font-size:9px; font-weight:700; padding:2px 8px; border-radius:999px; }
.footer { text-align:center; font-size:9px; color:#94a3b8; border-top:1px solid #e2e8f0; padding-top:8px; margin-top:20px; }
</style>
</head>
<body>

@if($paper->template?->show_watermark && $paper->template->watermark_text)
<div class="watermark">{{ $paper->template->watermark_text }}</div>
@endif

{{-- Header --}}
<div class="header">
    @if($template)
    {!! $template->renderHeader([
        '{{school_name}}' => config('app.name'),
        '{{exam_name}}'   => $paper->exam_name ?? '',
        '{{subject}}'     => $paper->subject ?? '',
        '{{class}}'       => $paper->class_label ?? '',
        '{{time}}'        => $paper->duration ?? '',
        '{{full_marks}}'  => $paper->full_marks ?? '',
        '{{pass_marks}}'  => $paper->pass_marks ?? '',
        '{{date}}'        => $paper->exam_date?->format('d M Y') ?? '',
    ]) !!}
    @else
    <h1>{{ config('app.name') }}</h1>
    <p>{{ $paper->exam_name }}</p>
    @endif
</div>

{{-- Meta grid --}}
<div class="meta-grid">
    <div class="row"><span class="lbl">Subject:</span><span class="val">{{ $paper->subject ?? '—' }}</span></div>
    <div class="row"><span class="lbl">Class:</span><span class="val">{{ $paper->class_label ?? '—' }}</span></div>
    <div class="row"><span class="lbl">Time:</span><span class="val">{{ $paper->duration ?? '—' }}</span></div>
    <div class="row"><span class="lbl">Full Marks:</span><span class="val">{{ $paper->full_marks ?: $paper->total_marks }}</span></div>
    <div class="row"><span class="lbl">Pass Marks:</span><span class="val">{{ $paper->pass_marks ?? '—' }}</span></div>
    <div class="row"><span class="lbl">Date:</span><span class="val">{{ $paper->exam_date?->format('d M Y') ?? '—' }}</span></div>
</div>

{{-- Instructions --}}
@if($template?->instructions_html)
<div class="instructions">
    <p>Instructions:</p>
    {!! $template->instructions_html !!}
</div>
@endif

{{-- Sections & Questions --}}
@foreach($paper->sections as $si => $section)
<div class="section-header">{{ $section->title }}@if($section->instructions) &mdash; {{ $section->instructions }}@endif <span style="float:right">[{{ $section->total_marks }} Marks]</span></div>
@foreach($section->questions as $q)
<div class="question">
    <span class="q-num">{{ $q->numbering }}.</span>
    <span class="marks-badge">[{{ $q->allocated_marks }} M]</span>
    <span class="q-text">{!! $q->question_text !!}</span>
    @if($q->options)
    <ol class="options" type="A">
        @foreach($q->options as $opt)<li>{{ $opt }}</li>@endforeach
    </ol>
    @elseif(in_array($q->question_type, ['short_answer','long_answer','essay','numerical']))
    @for($l = 0; $l < ($q->question_type==='essay' ? 5 : 2); $l++)
    <div class="answer-line"></div>
    @endfor
    @endif
</div>
@endforeach
@endforeach

{{-- Footer --}}
<div class="footer">
    {{ config('app.name') }}
    @if($template?->footer_html) &bull; {!! $template->footer_html !!} @endif
    &bull; {{ $paper->title }}
    @if($template?->signature_name) &bull; Prepared by: {{ $template->signature_name }}@if($template->signature_title), {{ $template->signature_title }}@endif @endif
</div>

</body>
</html>
