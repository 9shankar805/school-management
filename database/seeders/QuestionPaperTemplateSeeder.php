<?php

namespace Database\Seeders;

use App\Models\QuestionPaperTemplate;
use Illuminate\Database\Seeder;

/**
 * Seeds three reusable question paper templates:
 *
 *  1. Classic School Header  — centred school block with ruled divider, standard instructions
 *  2. Formal Board Exam      — two-column header (logo left / marks right), confidential watermark
 *  3. Minimal Modern         — clean single-line header, no watermark, minimal footer
 */
class QuestionPaperTemplateSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Classic School Header ──────────────────────────────────────────
        QuestionPaperTemplate::updateOrCreate(
            ['name' => 'Classic School Header'],
            [
                'description'   => 'Traditional centred school header with double-rule divider, exam info row, and general instructions block.',
                'school_name'   => null,          // auto-filled from school profile at render time
                'school_address'=> null,
                'header_html'   => <<<'HTML'
<div style="text-align:center;border-bottom:3px double #1e293b;padding-bottom:10px;margin-bottom:8px;">
  <div style="margin-bottom:6px;">{{school_logo}}</div>
  <p style="font-size:10px;color:#64748b;letter-spacing:.08em;text-transform:uppercase;margin-bottom:3px;">{{school_address}}</p>
  <h1 style="font-size:17px;font-weight:900;color:#0f172a;margin:0 0 2px;">{{school_name}}</h1>
  <p style="font-size:11px;font-weight:700;color:#3730a3;margin:4px 0 0;">{{exam_name}}</p>
  <div style="display:flex;justify-content:space-between;margin-top:8px;font-size:10px;color:#475569;border-top:1px solid #e2e8f0;padding-top:6px;">
    <span><strong>Subject:</strong> {{subject}}</span>
    <span><strong>Class:</strong> {{class}}</span>
    <span><strong>Time:</strong> {{time}}</span>
    <span><strong>Full Marks:</strong> {{full_marks}}</span>
    <span><strong>Pass Marks:</strong> {{pass_marks}}</span>
    <span><strong>Date:</strong> {{date}}</span>
  </div>
</div>
HTML,
                'instructions_html' => <<<'HTML'
<strong>General Instructions:</strong>
<ol style="margin:4px 0 0 16px;padding:0;font-size:10px;color:#475569;">
  <li>Candidates must write their Roll Number, Class, and Subject clearly on the answer sheet.</li>
  <li>All questions are compulsory unless stated otherwise in a section.</li>
  <li>Read each question carefully before answering.</li>
  <li>Figures in brackets <strong>[ ]</strong> indicate full marks for that question.</li>
  <li>Use of mobile phones and electronic devices is strictly prohibited in the examination hall.</li>
</ol>
HTML,
                'footer_html'       => '<span>Examiner\'s Signature: _______________</span>&nbsp;&nbsp;&nbsp;<span>Invigilator\'s Signature: _______________</span>',
                'signature_name'    => 'Exam Controller',
                'signature_title'   => 'Academic Department',
                'paper_size'        => 'A4',
                'orientation'       => 'portrait',
                'show_watermark'    => false,
                'watermark_text'    => null,
                'is_active'         => true,
                'created_by'        => null,
            ]
        );

        // ── 2. Formal Board Exam ──────────────────────────────────────────────
        QuestionPaperTemplate::updateOrCreate(
            ['name' => 'Formal Board Exam'],
            [
                'description'   => 'Two-column header: school identity on the left, marks/time box on the right. CONFIDENTIAL watermark. Strict board-style instructions.',
                'school_name'   => null,
                'school_address'=> null,
                'header_html'   => <<<'HTML'
<table style="width:100%;border-bottom:2px solid #1e293b;padding-bottom:10px;margin-bottom:10px;border-collapse:collapse;">
  <tr>
    <td style="vertical-align:middle;width:65%;">
      <div style="margin-bottom:4px;">{{school_logo}}</div>
      <p style="font-size:9px;color:#64748b;letter-spacing:.1em;text-transform:uppercase;margin:0 0 2px;">{{school_address}}</p>
      <h1 style="font-size:16px;font-weight:900;color:#0f172a;margin:0 0 3px;">{{school_name}}</h1>
      <p style="font-size:11px;font-weight:700;color:#1e40af;margin:0 0 3px;">{{exam_name}}</p>
      <p style="font-size:10px;color:#475569;margin:0;"><strong>Subject:</strong> {{subject}} &nbsp;|&nbsp; <strong>Class:</strong> {{class}}</p>
    </td>
    <td style="vertical-align:middle;text-align:right;width:35%;">
      <table style="margin-left:auto;border:1px solid #cbd5e1;border-radius:6px;border-collapse:collapse;font-size:10px;">
        <tr style="background:#f1f5f9;">
          <td style="padding:4px 10px;border-bottom:1px solid #e2e8f0;color:#64748b;">Full Marks</td>
          <td style="padding:4px 10px;border-bottom:1px solid #e2e8f0;font-weight:700;color:#0f172a;">{{full_marks}}</td>
        </tr>
        <tr style="background:#f8fafc;">
          <td style="padding:4px 10px;border-bottom:1px solid #e2e8f0;color:#64748b;">Pass Marks</td>
          <td style="padding:4px 10px;border-bottom:1px solid #e2e8f0;font-weight:700;color:#0f172a;">{{pass_marks}}</td>
        </tr>
        <tr style="background:#f1f5f9;">
          <td style="padding:4px 10px;border-bottom:1px solid #e2e8f0;color:#64748b;">Time Allowed</td>
          <td style="padding:4px 10px;border-bottom:1px solid #e2e8f0;font-weight:700;color:#0f172a;">{{time}}</td>
        </tr>
        <tr style="background:#f8fafc;">
          <td style="padding:4px 10px;color:#64748b;">Date</td>
          <td style="padding:4px 10px;font-weight:700;color:#0f172a;">{{date}}</td>
        </tr>
      </table>
    </td>
  </tr>
</table>
HTML,
                'instructions_html' => <<<'HTML'
<strong>Important Instructions:</strong>
<ol style="margin:4px 0 0 16px;padding:0;font-size:10px;color:#475569;">
  <li>This question paper is <strong>CONFIDENTIAL</strong>. Do not disclose contents before the examination begins.</li>
  <li>Write your full name, roll number, class, and section on the answer booklet before starting.</li>
  <li>Attempt <strong>all</strong> questions. Marks are indicated in square brackets against each question.</li>
  <li>Begin each section on a fresh page. Draw margins on both sides of the answer sheet.</li>
  <li>Only blue or black ink pen is permitted. Pencil may be used for diagrams only.</li>
  <li>No extra time will be granted. Hand over the answer booklet to the invigilator before leaving.</li>
</ol>
HTML,
                'footer_html'       => '<span style="font-weight:700;">CONFIDENTIAL — For official use only</span> &nbsp;|&nbsp; <span>Question Paper Code: ______ </span>',
                'signature_name'    => 'Chief Examiner',
                'signature_title'   => 'Board of Examinations',
                'paper_size'        => 'A4',
                'orientation'       => 'portrait',
                'show_watermark'    => true,
                'watermark_text'    => 'CONFIDENTIAL',
                'is_active'         => true,
                'created_by'        => null,
            ]
        );

        // ── 3. Minimal Modern ─────────────────────────────────────────────────
        QuestionPaperTemplate::updateOrCreate(
            ['name' => 'Minimal Modern'],
            [
                'description'   => 'Clean single-line header with a thin accent bar. No watermark. Compact meta row, suitable for class tests and practice papers.',
                'school_name'   => null,
                'school_address'=> null,
                'header_html'   => <<<'HTML'
<div style="border-left:4px solid #6366f1;padding:6px 12px;margin-bottom:10px;background:#f8f9ff;display:flex;align-items:center;gap:10px;">
  <div>{{school_logo}}</div>
  <div>
    <h1 style="font-size:14px;font-weight:800;color:#1e293b;margin:0 0 2px;">{{school_name}}</h1>
    <p style="font-size:10px;color:#6366f1;font-weight:600;margin:0;">{{exam_name}} &mdash; {{subject}} &mdash; Class {{class}}</p>
  </div>
</div>
<div style="display:flex;gap:20px;font-size:10px;color:#475569;margin-bottom:8px;padding:0 4px;">
  <span><strong>Time:</strong> {{time}}</span>
  <span><strong>Full Marks:</strong> {{full_marks}}</span>
  <span><strong>Pass Marks:</strong> {{pass_marks}}</span>
  <span><strong>Date:</strong> {{date}}</span>
</div>
HTML,
                'instructions_html' => <<<'HTML'
<span style="font-style:italic;font-size:10px;color:#64748b;">
  Attempt all questions. Marks are shown in brackets. Write clearly and show all working where required.
</span>
HTML,
                'footer_html'       => null,
                'signature_name'    => null,
                'signature_title'   => null,
                'paper_size'        => 'A4',
                'orientation'       => 'portrait',
                'show_watermark'    => false,
                'watermark_text'    => null,
                'is_active'         => true,
                'created_by'        => null,
            ]
        );
    }
}
