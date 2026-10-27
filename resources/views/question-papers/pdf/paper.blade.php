<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>{{ $paper->title }}</title>
<script>
window.MathJax = {
  tex: {
    inlineMath: [['$', '$'], ['\\(', '\\)']],
    displayMath: [['$$', '$$'], ['\\[', '\\]']],
    processEscapes: true
  }
};
</script>
<script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.css">
<script src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/contrib/auto-render.min.js"></script>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans+Devanagari:wght@400;600;700;800;900&display=swap">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Noto Sans Devanagari','DejaVu Sans',sans-serif;font-size:11px;color:#1e293b;padding:28px;}
h1{font-size:16px;font-weight:800;}h2{font-size:13px;font-weight:700;}h3{font-size:12px;font-weight:700;}
.watermark{position:fixed;top:50%;left:50%;transform:translate(-50%,-50%) rotate(-35deg);font-size:64px;color:rgba(79,70,229,.07);font-weight:900;letter-spacing:.08em;pointer-events:none;z-index:0;}
.header{text-align:center;margin-bottom:10px;}
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
/* Server-side LaTeX Math Styles for DomPDF */
.math-latex-inline { font-family: "DejaVu Sans", sans-serif !important; font-size: 1em; display: inline; }
.math-sym { font-family: "DejaVu Sans", sans-serif !important; font-weight: normal; }
/* Images from question content - show in full width and natural aspect ratio */
.tiptap-editor img, .q-body img, .question-img {
  max-width: 100% !important;
  width: auto !important;
  height: auto !important;
  max-height: none !important;
  object-fit: contain;
  border-radius: 4px;
  margin: 8px auto;
  display: block;
}
</style>
</head>
<body>

@if($paper->template?->show_watermark && $paper->template->watermark_text)
<div class="watermark">{{ strtoupper($paper->template->watermark_text) }}</div>
@endif

{{-- Resolve school info: template overrides > school profile > app name --}}
@php
  if (!function_exists('parseBraceContents')) {
      function parseBraceContents(string $str, int $startOffset): ?array {
          $len = strlen($str);
          if ($startOffset >= $len || $str[$startOffset] !== '{') return null;
          $depth = 0;
          for ($i = $startOffset; $i < $len; $i++) {
              if ($str[$i] === '{') $depth++;
              elseif ($str[$i] === '}') {
                  $depth--;
                  if ($depth === 0) {
                      return ['content' => substr($str, $startOffset + 1, $i - $startOffset - 1), 'end' => $i];
                  }
              }
          }
          return null;
      }
  }

  if (!function_exists('parseLatexExpression')) {
      function parseLatexExpression(string $latex): string {
          $html = str_replace(['\left(', '\right)', '\left[', '\right]', '\left\{', '\right\}', '\left', '\right'], ['(', ')', '[', ']', '{', '}', '', ''], $latex);
          $replacements = [
              '/\\\\parallel/'       => '<span style="font-weight:bold; margin:0 2px;">||</span>',
              '/\\\\angle/'          => '<span class="math-sym">&ang;</span>',
              '/\\\\times/'          => '&times;',
              '/\\\\div/'            => '&divide;',
              '/\\\\pm/'             => '&plusmn;',
              '/\\\\cdot/'           => '&middot;',
              '/\\\\le\b|\\\\leq\b/' => '&le;',
              '/\\\\ge\b|\\\\geq\b/' => '&ge;',
              '/\\\\neq/'            => '&ne;',
              '/\\\\approx/'         => '&approx;',
              '/\\\\alpha/'          => '&alpha;',
              '/\\\\beta/'           => '&beta;',
              '/\\\\theta/'          => '&theta;',
              '/\\\\pi/'             => '&pi;',
              '/\\\\infty/'          => '&infin;',
          ];
          $html = preg_replace(array_keys($replacements), array_values($replacements), $html);

          while (($pos = strpos($html, '\sqrt')) !== false) {
              $index = '';
              $afterSqrt = $pos + 5;
              if (isset($html[$afterSqrt]) && $html[$afterSqrt] === '[') {
                  $closeBracket = strpos($html, ']', $afterSqrt);
                  if ($closeBracket !== false) {
                      $index = substr($html, $afterSqrt + 1, $closeBracket - $afterSqrt - 1);
                      $afterSqrt = $closeBracket + 1;
                  }
              }
              $bracePos = strpos($html, '{', $afterSqrt);
              if ($bracePos === false) break;
              $b = parseBraceContents($html, $bracePos);
              if (!$b) break;

              $radicandHtml = parseLatexExpression($b['content']);
              $idxHtml = $index ? '<sup style="font-size:0.7em;">' . $index . '</sup>' : '';
              $sqrtHtml = $idxHtml . '<span class="math-sym" style="font-size:1.1em;">&radic;</span><span style="border-top:1px solid #000; padding-top:1px; display:inline-block;">' . $radicandHtml . '</span>';
              $html = substr_replace($html, $sqrtHtml, $pos, $b['end'] - $pos + 1);
          }

          while (($pos = strpos($html, '\frac')) !== false) {
              $brace1Pos = strpos($html, '{', $pos);
              if ($brace1Pos === false) break;
              $b1 = parseBraceContents($html, $brace1Pos);
              if (!$b1) break;

              $brace2Pos = strpos($html, '{', $b1['end'] + 1);
              if ($brace2Pos === false) break;
              $b2 = parseBraceContents($html, $brace2Pos);
              if (!$b2) break;

              $numHtml = parseLatexExpression($b1['content']);
              $denHtml = parseLatexExpression($b2['content']);

              $fracHtml = '<span style="display:inline-block; vertical-align:middle; text-align:center; margin:0 2px;"><span style="display:block; border-bottom:1px solid #000; padding:0 2px 1px; font-weight:bold; font-size:0.9em; line-height:1;">' . $numHtml . '</span><span style="display:block; padding:1px 2px 0; font-weight:bold; font-size:0.9em; line-height:1;">' . $denHtml . '</span></span>';
              $html = substr_replace($html, $fracHtml, $pos, $b2['end'] - $pos + 1);
          }

          $html = preg_replace('/\^\{([^{}]+)\}/', '<sup>$1</sup>', $html);
          $html = preg_replace('/\^([0-9a-zA-Z+-]+)/', '<sup>$1</sup>', $html);
          $html = preg_replace('/_\{([^{}]+)\}/', '<sub>$1</sub>', $html);
          $html = preg_replace('/_([0-9a-zA-Z+-]+)/', '<sub>$1</sub>', $html);

          return $html;
      }
  }

  if (!function_exists('renderLatexToHtmlForDomPdf')) {
      function renderLatexToHtmlForDomPdf(string $text): string {
          if (!str_contains($text, '\\') && !str_contains($text, '$')) {
              return $text;
          }
          // Normalize double backslashes from DB (\\( -> \( and \\) -> \))
          $textClean = str_replace(['\\\\(', '\\\\)', '\\\\[', '\\\\]'], ['\\(', '\\)', '\\[', '\\]'], $text);
          $textClean = str_replace(['\\left(', '\\right)'], ['(', ')'], $textClean);

          return preg_replace_callback('/\\\\\\(\\s*(.*?)\\s*\\\\\\)|\\\\\\[\\s*(.*?)\\s*\\\\\\]|\\$\\$\\s*(.*?)\\s*\\$\\$|\\$\\s*(.*?)\\s*\\$/s', function ($m) {
              $latex = trim($m[1] ?: $m[2] ?: $m[3] ?: $m[4] ?: '');
              if (empty($latex)) return '';
              return '<span class="math-latex-inline">' . parseLatexExpression($latex) . '</span>';
          }, $textClean);
      }
  }

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
          $schoolLogoImgTag = '<img src="' . $dataUri . '" width="85" height="85" style="object-fit:contain;" alt="' . e($schoolName) . '">';
      }
  }

  // Placeholder map — covers both {{lower}} and {{UPPER}} variants
  $placeholders = [
    '{{school_name}}'    => $schoolName,
    '{{SCHOOL_NAME}}'    => $schoolName,
    '{{school_address}}' => $schoolAddress,
    '{{SCHOOL_ADDRESS}}' => $schoolAddress,
    '{{exam_name}}'      => $paper->exam_name ?: ($paper->title ?: '3rd TERMINAL EXAMINATION 2082'),
    '{{EXAM_NAME}}'      => $paper->exam_name ?: ($paper->title ?: '3rd TERMINAL EXAMINATION 2082'),
    '{{author}}'         => $paper->creator?->full_name ?: ($paper->creator?->name ?: 'Sunil Yadav'),
    '{{AUTHOR}}'         => $paper->creator?->full_name ?: ($paper->creator?->name ?: 'Sunil Yadav'),
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
    '{{school_logo}}'    => $schoolLogoImgTag ? str_replace('<img ', '<img style="height:85px;width:85px;max-width:85px;max-height:85px;object-fit:contain;vertical-align:middle;" ', $schoolLogoImgTag) : '',
    '{{SCHOOL_LOGO}}'    => $schoolLogoImgTag ? str_replace('<img ', '<img style="height:85px;width:85px;max-width:85px;max-height:85px;object-fit:contain;vertical-align:middle;" ', $schoolLogoImgTag) : '',
  ];
@endphp

@php
  $isLandscape = ($paper->orientation === 'landscape');

  // If landscape mode, fill Column 1 (Page 1) completely before flowing to Column 2 (Page 2)
  if ($isLandscape) {
      $allFlatItems = [];
      foreach ($paper->sections as $section) {
          foreach ($section->questions as $qIndex => $q) {
              $allFlatItems[] = [
                  'section'      => $section,
                  'is_sec_first' => ($qIndex === 0),
                  'question'     => $q,
              ];
          }
      }

      $estSecHeaderH = function($sec) {
          $chars = strlen($sec->title ?? '') + strlen($sec->instructions ?? '');
          if ($chars > 120) return 42;
          if ($chars > 60) return 34;
          return 28;
      };

      $estQuestionH = function($q) {
          $textLen = strlen(strip_tags($q->question_text ?? ''));
          $lines = max(1, (int) ceil($textLen / 80));
          $h = ($lines * 14) + 9;

          if (!empty($q->options) && is_array($q->options)) {
              $optLen = 0;
              foreach ($q->options as $o) {
                  $optLen += strlen(is_array($o) ? ($o['text'] ?? '') : (string)$o) + 8;
              }
              $optLines = max(1, (int) ceil($optLen / 80));
              $h += ($optLines * 14) + 6;
          } elseif (($q->question_type ?? '') === 'true_false') {
              $h += 16;
          }

          if (str_contains($q->question_text ?? '', '<img')) {
              $h += 220;
          }
          return $h;
      };

      // Header height on Page 1 (Left column of Sheet 1)
      $headerHeight = ($template && $template->header_html) ? 140 : 70;
      $boxUsableHeight = 790;
      $footerReserve = 25;

      $columns = [];
      $currentColItems = [];
      $currentColHeight = 0;
      $currentSecId = null;

      $maxHeightForCol = $boxUsableHeight - $headerHeight;

      foreach ($allFlatItems as $item) {
          $sec = $item['section'];
          $q = $item['question'];
          $neededH = $estQuestionH($q);

          $isNewSecInCol = ($currentSecId !== $sec->id);
          if ($isNewSecInCol) {
              $neededH += $estSecHeaderH($sec);
          }

          if ($currentColHeight + $neededH > $maxHeightForCol && !empty($currentColItems)) {
              $columns[] = $currentColItems;
              $currentColItems = [];
              $currentColHeight = 0;
              $currentSecId = null;
              $maxHeightForCol = $boxUsableHeight - $footerReserve;

              $neededH = $estQuestionH($q) + $estSecHeaderH($sec);
          }

          $currentColItems[] = $item;
          $currentColHeight += $neededH;
          $currentSecId = $sec->id;
      }
      if (!empty($currentColItems)) {
          $columns[] = $currentColItems;
      }

      // Pair columns into sheets (2 columns per sheet: left and right)
      $sheets = [];
      $colCount = count($columns);
      for ($i = 0; $i < $colCount; $i += 2) {
          $leftCol = $columns[$i] ?? [];
          $rightCol = $columns[$i + 1] ?? [];
          $isLastSheet = ($i + 2 >= $colCount);
          $sheets[] = [
              'left'              => $leftCol,
              'right'             => $rightCol,
              'is_last_col_left'  => ($isLastSheet && empty($rightCol)),
              'is_last_col_right' => ($isLastSheet && !empty($rightCol)),
          ];
      }
      if (empty($sheets)) {
          $sheets[] = [
              'left'              => [],
              'right'             => [],
              'is_last_col_left'  => true,
              'is_last_col_right' => false,
          ];
      }
  }
@endphp

@if($isLandscape)
{{-- ══════════════════════════════════════════════════════════════════════════
     LANDSCAPE 2-COLUMN BOOKLET LAYOUT
     Header title & metadata appears ONLY ONCE on the Left Column (Top Left)
     Questions fill the first page completely before continuing to the second page
══════════════════════════════════════════════════════════════════════════ --}}
<style>
@page { size: A4 landscape; margin: 6mm 8mm 6mm 8mm; }
.landscape-page-box { border:2.5px double #000; padding:12px 14px; min-height:880px; height:880px; box-sizing:border-box; position:relative; }
.landscape-page-box .header > div[style*="double"] { border:none !important; padding:0 !important; margin-bottom:8px !important; }
</style>

@foreach($sheets as $sheetIndex => $sheet)
@if($sheetIndex > 0)
<div style="page-break-before:always;"></div>
@endif
<table width="100%" height="880" cellpadding="0" cellspacing="0" style="border-collapse:collapse; table-layout:fixed;">
  <tr>
    {{-- LEFT COLUMN: Wrapped in Full-Page Double-Border Outer Container --}}
    <td width="48.5%" valign="top" style="height:880px;">
      <div class="landscape-page-box">
        @if($sheetIndex === 0)
          @if($template && $template->header_html)
            <div class="header" style="margin-bottom:10px;">
              {!! $template->renderHeader($placeholders) !!}
            </div>
          @else
            <div style="padding-bottom:10px; margin-bottom:10px; border-bottom:1px solid #000;">
              <div style="font-size:16px; font-weight:900; text-align:center;">{{ $schoolName }}</div>
              <div style="font-size:10px; text-align:center;">{{ $schoolAddress }}</div>
              <div style="text-align:center; margin-top:4px; font-weight:700;">{{ $paper->exam_name }}</div>
            </div>
          @endif
        @endif

        @php $currentSecIdLeft = null; @endphp
        @foreach($sheet['left'] as $item)
          @php
            $sec = $item['section'];
            $q   = $item['question'];
          @endphp
          @if($currentSecIdLeft !== $sec->id)
            @php $currentSecIdLeft = $sec->id; @endphp
            <div style="text-align:center; margin:12px 0 8px 0;">
              <span style="background:#e2e8f0; border:1.5px solid #000; border-radius:4px; padding:2px 16px; font-weight:800; font-size:11.5px; color:#000; display:inline-block; font-family:sans-serif;">
                {!! renderLatexToHtmlForDomPdf($sec->title) !!}@if($sec->instructions) — {!! renderLatexToHtmlForDomPdf($sec->instructions) !!}@endif [{{ count($sec->questions) }} &times; {{ $sec->questions->first()?->allocated_marks ?? 1 }} = {{ $sec->total_marks }}]
              </span>
            </div>
          @endif

          <div class="question" style="margin-bottom:10px;">
            <span class="q-num" style="font-weight:700; color:#000; margin-right:4px;">{{ $q->numbering }}.</span>
            <span class="q-body" style="font-size:10.5px; color:#000;">{!! renderLatexToHtmlForDomPdf($q->question_text) !!}</span>
            @if($q->options)
            @php $romanMap = ['i', 'ii', 'iii', 'iv', 'v', 'vi', 'vii', 'viii']; @endphp
            <div style="margin:3px 0 5px 16px; font-size:10px; color:#000; display:flex; flex-wrap:wrap; gap:12px;">
              @foreach($q->options as $optIdx => $opt)
              <span style="margin-right:12px;">({{ $romanMap[$loop->index] ?? $loop->iteration }}) {!! renderLatexToHtmlForDomPdf(is_array($opt) ? ($opt['text'] ?? '') : (string)$opt) !!}</span>
              @endforeach
            </div>
            @elseif($q->question_type==='true_false')
            <div style="padding-left:16px;margin-top:3px;font-size:10px;color:#475569;">(i) True &nbsp;&nbsp; (ii) False</div>
            @endif
          </div>
        @endforeach

        @if($sheet['is_last_col_left'])
        <div style="position:absolute; bottom:12px; left:0; right:0; text-align:center; font-weight:800; font-size:11px; color:#000;">
          *** BEST OF LUCK ***
        </div>
        @endif
      </div>
    </td>

    {{-- GAP --}}
    <td width="3%"></td>

    {{-- RIGHT COLUMN: Wrapped in Full-Page Double-Border Outer Container --}}
    <td width="48.5%" valign="top" style="height:880px;">
      <div class="landscape-page-box">
        @php $currentSecIdRight = null; @endphp
        @foreach($sheet['right'] as $item)
          @php
            $sec = $item['section'];
            $q   = $item['question'];
          @endphp
          @if($currentSecIdRight !== $sec->id)
            @php $currentSecIdRight = $sec->id; @endphp
            <div style="text-align:center; margin:12px 0 8px 0;">
              <span style="background:#e2e8f0; border:1.5px solid #000; border-radius:4px; padding:2px 16px; font-weight:800; font-size:11.5px; color:#000; display:inline-block; font-family:sans-serif;">
                {!! renderLatexToHtmlForDomPdf($sec->title) !!}@if($sec->instructions) — {!! renderLatexToHtmlForDomPdf($sec->instructions) !!}@endif [{{ count($sec->questions) }} &times; {{ $sec->questions->first()?->allocated_marks ?? 1 }} = {{ $sec->total_marks }}]
              </span>
            </div>
          @endif

          <div class="question" style="margin-bottom:10px;">
            <span class="q-num" style="font-weight:700; color:#000; margin-right:4px;">{{ $q->numbering }}.</span>
            <span class="q-body" style="font-size:10.5px; color:#000;">{!! renderLatexToHtmlForDomPdf($q->question_text) !!}</span>
            @if($q->options)
            @php $romanMap = ['i', 'ii', 'iii', 'iv', 'v', 'vi', 'vii', 'viii']; @endphp
            <div style="margin:3px 0 5px 16px; font-size:10px; color:#000; display:flex; flex-wrap:wrap; gap:12px;">
              @foreach($q->options as $optIdx => $opt)
              <span style="margin-right:12px;">({{ $romanMap[$loop->index] ?? $loop->iteration }}) {!! renderLatexToHtmlForDomPdf(is_array($opt) ? ($opt['text'] ?? '') : (string)$opt) !!}</span>
              @endforeach
            </div>
            @elseif($q->question_type==='true_false')
            <div style="padding-left:16px;margin-top:3px;font-size:10px;color:#475569;">(i) True &nbsp;&nbsp; (ii) False</div>
            @endif
          </div>
        @endforeach

        @if($sheet['is_last_col_right'])
        <div style="position:absolute; bottom:12px; left:0; right:0; text-align:center; font-weight:800; font-size:11px; color:#000;">
          *** BEST OF LUCK ***
        </div>
        @endif
      </div>
    </td>
  </tr>
</table>
@endforeach

@else
{{-- ══════════════════════════════════════════════════════════════════════════
     PORTRAIT SINGLE-COLUMN LAYOUT
══════════════════════════════════════════════════════════════════════════ --}}
@if($template && $template->header_html)
<div class="header">
  {!! $template->renderHeader($placeholders) !!}
</div>
@else
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
    <td width="70" valign="middle" style="padding-bottom:10px;"></td>
  </tr>
{{-- Default Boxed Meta Table --}}
<table width="100%" cellpadding="0" cellspacing="0" style="border:1.5px solid #000; border-collapse:collapse; margin:10px 0 14px 0; font-size:11px; color:#000; font-family:sans-serif;">
  <tr>
    <td style="border:1px solid #000; padding:5px 8px; width:35%;"><strong>Sub:</strong> {{ $paper->subject ?? '—' }}</td>
    <td style="border:1px solid #000; padding:5px 8px; width:35%;"><strong>Time:</strong> {{ $paper->duration ?? '—' }}</td>
    <td style="border:1px solid #000; padding:5px 8px; width:30%;"><strong>F.M.:</strong> {{ $paper->full_marks ?: $paper->total_marks }}</td>
  </tr>
  <tr>
    <td style="border:1px solid #000; padding:5px 8px;"><strong>Class:</strong> {{ $paper->class_label ?? '—' }}</td>
    <td style="border:1px solid #000; padding:5px 8px; text-align:center;">Submitted by: {{ $paper->creator?->full_name ?: ($paper->creator?->name ?: 'Sunil Yadav') }}</td>
    <td style="border:1px solid #000; padding:5px 8px;"><strong>P.M.:</strong> {{ $paper->pass_marks ?? '—' }}</td>
  </tr>
</table>
@endif

{{-- Instructions --}}
@if($template?->instructions_html)
<div class="instructions">
  <strong>Instructions:</strong>
  {!! $template->instructions_html !!}
</div>
@endif

{{-- Sections & Questions --}}
@foreach($paper->sections as $section)
@if($template && (str_contains($template->name, 'Genesis') || str_contains($template->name, 'Terminal')))
<div style="text-align:center; margin:16px 0 10px 0;">
  <span style="background:#e2e8f0; border:1.5px solid #000; border-radius:4px; padding:3px 22px; font-weight:800; font-size:12.5px; color:#000; display:inline-block; font-family:sans-serif;">
    {!! renderLatexToHtmlForDomPdf($section->title) !!}@if($section->instructions) — {!! renderLatexToHtmlForDomPdf($section->instructions) !!}@endif [{{ count($section->questions) }} &times; {{ $section->questions->first()?->allocated_marks ?? 1 }} = {{ $section->total_marks }}]
  </span>
</div>
@else
<div class="section-header">
  <span>{!! renderLatexToHtmlForDomPdf($section->title) !!}@if($section->instructions) — {!! renderLatexToHtmlForDomPdf($section->instructions) !!}@endif</span>
  <span>[{{ $section->total_marks }} Marks]</span>
</div>
@endif

@foreach($section->questions as $q)
<div class="question" style="margin-bottom:12px;">
  @unless($template && (str_contains($template->name, 'Genesis') || str_contains($template->name, 'Terminal')))
  <span class="marks-float">[{{ $q->allocated_marks }} M]</span>
  @endunless
  <span class="q-num" style="font-weight:700; color:#000; margin-right:4px;">{{ $q->numbering }}.</span>
  <span class="q-body" style="font-size:11px; color:#000;">{!! renderLatexToHtmlForDomPdf($q->question_text) !!}</span>
  @if($q->options)
  @php $romanMap = ['i', 'ii', 'iii', 'iv', 'v', 'vi', 'vii', 'viii']; @endphp
  <div style="margin:4px 0 6px 20px; font-size:10.5px; color:#000; display:flex; flex-wrap:wrap; gap:16px;">
    @foreach($q->options as $optIdx => $opt)
    <span style="margin-right:16px;">({{ $romanMap[$loop->index] ?? $loop->iteration }}) {!! renderLatexToHtmlForDomPdf(is_array($opt) ? ($opt['text'] ?? '') : (string)$opt) !!}</span>
    @endforeach
  </div>
  @elseif($q->question_type==='true_false')
  <div style="padding-left:20px;margin-top:4px;font-size:10px;color:#475569;">(i) True &nbsp;&nbsp; (ii) False</div>
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
  @if($template?->footer_html)
    {!! $template->footer_html !!}
  @else
    {{ $schoolName }}
    &bull; {{ $paper->title }}
    @if($template?->signature_name) &bull; {{ $template->signature_name }}@if($template->signature_title), {{ $template->signature_title }}@endif @endif
  @endif
</div>
@endif

{{-- MathJax + KaTeX auto-render: convert LaTeX expressions in question text --}}
<script>
function renderPaperMath() {
  if (window.MathJax && window.MathJax.typesetPromise) {
    window.MathJax.typesetPromise();
  }
  if (typeof renderMathInElement !== 'undefined') {
    try {
      renderMathInElement(document.body, {
        delimiters: [
          {left: '$$', right: '$$', display: true},
          {left: '\\[', right: '\\]', display: true},
          {left: '$', right: '$', display: false},
          {left: '\\(', right: '\\)', display: false}
        ],
        throwOnError: false
      });
    } catch(e) {}
  }
}
document.addEventListener('DOMContentLoaded', renderPaperMath);
window.addEventListener('load', renderPaperMath);
setTimeout(renderPaperMath, 500);
</script>
</body>
</html>
