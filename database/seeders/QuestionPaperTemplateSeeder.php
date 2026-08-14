<?php

namespace Database\Seeders;

use App\Models\QuestionPaperTemplate;
use Illuminate\Database\Seeder;

class QuestionPaperTemplateSeeder extends Seeder
{
    public function run(): void
    {
        // Delete all old templates
        QuestionPaperTemplate::query()->forceDelete();

        // ── 1. Genesis Terminal Examination (English) ──────────────────
        QuestionPaperTemplate::create([
            'name'           => 'Genesis Terminal Examination (Boxed Standard - English)',
            'description'    => 'Official Genesis Global School examination paper template in English: double outer border container, shaded rounded exam title badge, 6-cell boxed meta table, and Group-wise shaded pill section headers.',
            'school_name'    => 'GENESIS GLOBAL SCHOOL',
            'school_address' => 'SIRAHA, NEPAL',
            'header_html'    => <<<'HTML'
<div style="border:2.5px double #000; padding:12px 16px; margin-bottom:10px;">
  <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
    <tr>
      <td width="95" valign="middle" align="left">
        {{school_logo}}
      </td>
      <td valign="middle" align="center">
        <div style="font-size:19px; font-weight:900; color:#000; letter-spacing:0.5px; text-transform:uppercase; font-family:'Times New Roman', Georgia, serif;">{{school_name}}</div>
        <div style="font-size:11px; font-weight:700; color:#000; margin-top:2px; text-transform:uppercase;">{{school_address}}</div>
        <div style="margin-top:6px;">
          <span style="background:#e2e8f0; border:1px solid #000; border-radius:4px; padding:3px 18px; font-size:13px; font-weight:800; text-decoration:underline; display:inline-block; font-family:sans-serif;">{{exam_name}}</span>
        </div>
      </td>
      <td width="95" valign="middle" align="right"></td>
    </tr>
  </table>

  <table width="100%" cellpadding="0" cellspacing="0" style="border:1.5px solid #000; border-collapse:collapse; margin-top:10px; font-size:11px; color:#000; font-family:sans-serif;">
    <tr>
      <td style="border:1px solid #000; padding:5px 8px; width:35%;"><strong>Sub:</strong> {{subject}}</td>
      <td style="border:1px solid #000; padding:5px 8px; width:35%;"><strong>Time:</strong> {{time}}</td>
      <td style="border:1px solid #000; padding:5px 8px; width:30%;"><strong>F.M.:</strong> {{full_marks}}</td>
    </tr>
    <tr>
      <td style="border:1px solid #000; padding:5px 8px;"><strong>Class:</strong> {{class}}</td>
      <td style="border:1px solid #000; padding:5px 8px; text-align:center;">Submitted by: {{author}}</td>
      <td style="border:1px solid #000; padding:5px 8px;"><strong>P.M.:</strong> {{pass_marks}}</td>
    </tr>
  </table>

  <div style="text-align:center; font-size:14.5px; font-weight:800; text-decoration:underline; margin:10px 0 2px 0; font-family:sans-serif;">Attempt all the questions:</div>
</div>
HTML,
            'instructions_html' => null,
            'footer_html'       => '<div style="text-align:center; font-weight:800; font-size:12px; margin-top:16px; color:#000;">*** BEST OF LUCK ***</div>',
            'signature_name'    => null,
            'signature_title'   => null,
            'paper_size'        => 'A4',
            'orientation'       => 'portrait',
            'show_watermark'    => false,
            'watermark_text'    => null,
            'is_active'         => true,
            'created_by'        => null,
        ]);

        // ── 2. Genesis Terminal Examination (Nepali Medium) ──────────────────
        QuestionPaperTemplate::create([
            'name'           => 'Genesis Terminal Examination (Boxed Standard - Nepali)',
            'description'    => 'Official Genesis Global School examination paper template in Nepali medium: double outer border container, Devanagari labels, 6-cell boxed meta table, and Group-wise shaded pill section headers.',
            'school_name'    => 'जेनेसिस ग्लोबल स्कुल',
            'school_address' => 'सिरहा, नेपाल',
            'header_html'    => <<<'HTML'
<div style="border:2.5px double #000; padding:12px 16px; margin-bottom:10px; font-family:'Noto Sans Devanagari', 'DejaVu Sans', sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
    <tr>
      <td width="95" valign="middle" align="left">
        {{school_logo}}
      </td>
      <td valign="middle" align="center">
        <div style="font-size:20px; font-weight:900; color:#000; letter-spacing:0.5px; font-family:'Noto Sans Devanagari', 'DejaVu Sans', sans-serif;">{{school_name}}</div>
        <div style="font-size:12px; font-weight:700; color:#000; margin-top:2px; font-family:'Noto Sans Devanagari', 'DejaVu Sans', sans-serif;">{{school_address}}</div>
        <div style="margin-top:6px;">
          <span style="background:#e2e8f0; border:1px solid #000; border-radius:4px; padding:3px 18px; font-size:13px; font-weight:800; text-decoration:underline; display:inline-block; font-family:'Noto Sans Devanagari', 'DejaVu Sans', sans-serif;">{{exam_name}}</span>
        </div>
      </td>
      <td width="95" valign="middle" align="right"></td>
    </tr>
  </table>

  <table width="100%" cellpadding="0" cellspacing="0" style="border:1.5px solid #000; border-collapse:collapse; margin-top:10px; font-size:11.5px; color:#000; font-family:'Noto Sans Devanagari', 'DejaVu Sans', sans-serif;">
    <tr>
      <td style="border:1px solid #000; padding:5px 8px; width:35%;"><strong>विषय:</strong> {{subject}}</td>
      <td style="border:1px solid #000; padding:5px 8px; width:35%;"><strong>समय:</strong> {{time}}</td>
      <td style="border:1px solid #000; padding:5px 8px; width:30%;"><strong>पूर्णाङ्क:</strong> {{full_marks}}</td>
    </tr>
    <tr>
      <td style="border:1px solid #000; padding:5px 8px;"><strong>कक्षा:</strong> {{class}}</td>
      <td style="border:1px solid #000; padding:5px 8px; text-align:center;">पेश गर्ने: {{author}}</td>
      <td style="border:1px solid #000; padding:5px 8px;"><strong>उत्तीर्णाङ्क:</strong> {{pass_marks}}</td>
    </tr>
  </table>

  <div style="text-align:center; font-size:14.5px; font-weight:800; text-decoration:underline; margin:10px 0 2px 0; font-family:'Noto Sans Devanagari', 'DejaVu Sans', sans-serif;">सबै प्रश्नहरू अनिवार्य छन्:</div>
</div>
HTML,
            'instructions_html' => null,
            'footer_html'       => '<div style="text-align:center; font-weight:800; font-size:12px; margin-top:16px; color:#000; font-family:\'Noto Sans Devanagari\', \'DejaVu Sans\', sans-serif;">*** सफलताको हार्दिक शुभकामना ***</div>',
            'signature_name'    => null,
            'signature_title'   => null,
            'paper_size'        => 'A4',
            'orientation'       => 'portrait',
            'show_watermark'    => false,
            'watermark_text'    => null,
            'is_active'         => true,
            'created_by'        => null,
        ]);
    }
}
