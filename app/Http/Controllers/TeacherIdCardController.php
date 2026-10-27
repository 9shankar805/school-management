<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SchoolSession;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TeacherIdCardController extends Controller
{
    public function getSchoolLogoBase64($school = null): string
    {
        $school = $school ?? config('tenant.school') ?? \App\Models\School::first();
        $candidates = [];

        if ($school && $school->logo) {
            $clean = ltrim(str_replace('storage/', '', $school->logo), '/');
            $candidates[] = storage_path('app/public/' . $clean);
            $candidates[] = public_path('storage/' . $clean);
            $candidates[] = public_path($clean);
        }

        // Institutional fallbacks in public/imgs
        $candidates[] = public_path('imgs/logo_icon_192.webp');
        $candidates[] = public_path('imgs/logo_icon.webp');
        $candidates[] = public_path('imgs/schools.webp');

        foreach ($candidates as $filePath) {
            if ($filePath && file_exists($filePath) && is_file($filePath)) {
                $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                if ($ext === 'webp' && function_exists('imagecreatefromwebp')) {
                    $im = @imagecreatefromwebp($filePath);
                    if ($im) {
                        ob_start();
                        imagepng($im);
                        $pngData = ob_get_clean();
                        imagedestroy($im);
                        if ($pngData) {
                            return 'data:image/png;base64,' . base64_encode($pngData);
                        }
                    }
                }

                $data = @file_get_contents($filePath);
                if ($data) {
                    $mime = ($ext === 'png') ? 'image/png' : (($ext === 'svg') ? 'image/svg+xml' : 'image/jpeg');
                    return 'data:' . $mime . ';base64,' . base64_encode($data);
                }
            }
        }

        return '';
    }

    public function getSchoolLogoPath($school = null): ?string
    {
        $school = $school ?? config('tenant.school') ?? \App\Models\School::first();
        if ($school && $school->logo) {
            $clean = ltrim(str_replace('storage/', '', $school->logo), '/');
            if (file_exists(storage_path('app/public/' . $clean))) return storage_path('app/public/' . $clean);
            if (file_exists(public_path('storage/' . $clean))) return public_path('storage/' . $clean);
            if (file_exists(public_path($clean))) return public_path($clean);
        }
        if (file_exists(public_path('imgs/logo_icon_192.webp'))) return public_path('imgs/logo_icon_192.webp');
        if (file_exists(public_path('imgs/logo_icon.webp'))) return public_path('imgs/logo_icon.webp');
        return null;
    }

    private function getBase64Image($url, $isLocal = false)
    {
        try {
            if ($isLocal && file_exists($url)) {
                $ext = strtolower(pathinfo($url, PATHINFO_EXTENSION));
                if ($ext === 'webp' && function_exists('imagecreatefromwebp')) {
                    $im = @imagecreatefromwebp($url);
                    if ($im) {
                        ob_start();
                        imagepng($im);
                        $pngData = ob_get_clean();
                        imagedestroy($im);
                        if ($pngData) {
                            return 'data:image/png;base64,' . base64_encode($pngData);
                        }
                    }
                }

                $data = file_get_contents($url);
                return 'data:image/' . $ext . ';base64,' . base64_encode($data);
            }
        } catch (\Exception $e) {}
        
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';
    }

    private function getQrCodeSrc(string $dataUrl): string
    {
        try {
            $ctx = stream_context_create(['http' => ['timeout' => 2, 'user_agent' => 'SchoolHub/1.0']]);
            $img = @file_get_contents($dataUrl, false, $ctx);
            if ($img && strlen($img) > 100) {
                return 'data:image/png;base64,' . base64_encode($img);
            }
        } catch (\Exception $e) {}

        return $dataUrl;
    }

    /**
     * Download or preview a single double-sided Teacher ID Card.
     */
    public function single($teacherId)
    {
        $teacher = User::where('id', $teacherId)->firstOrFail();

        $schoolSession = SchoolSession::first();
        $school        = config('tenant.school') ?? \App\Models\School::find($teacher->school_id) ?? \App\Models\School::first();

        // Ensure teacher has qr_token
        if (!$teacher->qr_token) {
            $teacher->qr_token = Str::random(32);
            $teacher->saveQuietly();
        }

        $loginUrl = route('teacher.qr-login', ['token' => $teacher->qr_token]);
        $qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=' . urlencode($loginUrl);
        $qrSrc    = $this->getQrCodeSrc($qrApiUrl);
        $logoSrc  = $this->getSchoolLogoBase64($school);

        $photoSrc = $this->getBase64Image(public_path('imgs/profile.webp'), true);
        if ($teacher->photo) {
            $p = storage_path('app/public/' . ltrim($teacher->photo, '/'));
            if (file_exists($p)) {
                $photoSrc = $this->getBase64Image($p, true);
            }
        }

        $pdf = Pdf::loadView('teachers.id-card', compact('teacher', 'photoSrc', 'qrSrc', 'logoSrc', 'loginUrl', 'schoolSession', 'school'))
            ->setPaper([0, 0, 153, 243])
            ->setOptions([
                'dpi' => 150,
                'defaultFont' => 'sans-serif',
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
            ]);

        return $pdf->stream("Teacher_ID_Card_{$teacher->id}.pdf");
    }

    /**
     * Download or preview bulk printable PDF or Word grid for multiple teachers.
     */
    public function bulk(Request $request)
    {
        $schoolSession = SchoolSession::first();
        $school        = config('tenant.school') ?? \App\Models\School::first();

        // If accessed directly without export params, show options page
        if (!$request->has('format') && !$request->has('export') && !$request->has('layout') && !$request->has('department_id') && !$request->has('ids')) {
            return view('teachers.id-cards-bulk-page', compact('schoolSession', 'school'));
        }

        $teacherIds = $request->input('ids', []);
        
        $query = User::where(function($q) {
            $q->role(['teacher', 'class-teacher'])->orWhereIn('role', ['teacher', 'class-teacher']);
        });
        if (!empty($teacherIds)) {
            $query->whereIn('id', (array)$teacherIds);
        }
        if ($request->filled('department_id')) {
            $query->whereHas('departments', fn($q) => $q->where('departments.id', $request->department_id));
        }

        $limit = (int) $request->input('limit', 100);
        $teachers = $query->take(max(1, min($limit, 200)))->get();

        $format = strtolower($request->input('format', 'pdf'));
        $layout = strtolower($request->input('layout', 'dualsided'));

        // ── WORD (DOCX) EXPORT ───────────────────────────────────────────────
        if (in_array($format, ['word', 'docx', 'doc'])) {
            return $this->exportDocx($teachers, $schoolSession, $school, $layout);
        }

        // ── PDF EXPORT ───────────────────────────────────────────────────────
        $logoSrc = $this->getSchoolLogoBase64($school);
        $teacherCards = [];

        foreach ($teachers as $t) {
            if (!$t->qr_token) {
                $t->qr_token = Str::random(32);
                $t->saveQuietly();
            }

            $loginUrl = route('teacher.qr-login', ['token' => $t->qr_token]);
            $qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=' . urlencode($loginUrl);
            $qrSrc    = $this->getQrCodeSrc($qrApiUrl);

            $photoSrc = $this->getBase64Image(public_path('imgs/profile.webp'), true);
            if ($t->photo) {
                $p = storage_path('app/public/' . ltrim($t->photo, '/'));
                if (file_exists($p)) {
                    $photoSrc = $this->getBase64Image($p, true);
                }
            }

            $teacherCards[] = [
                'teacher'  => $t,
                'photoSrc' => $photoSrc,
                'qrSrc'    => $qrSrc,
                'loginUrl' => $loginUrl,
            ];
        }

        $pdf = Pdf::loadView('teachers.id-card-bulk', [
            'teachers'      => $teacherCards,
            'schoolSession' => $schoolSession,
            'school'        => $school,
            'logoSrc'       => $logoSrc,
            'layout'        => $layout,
        ])
        ->setPaper('a4', 'portrait')
        ->setOptions([
            'dpi' => 150,
            'defaultFont' => 'sans-serif',
            'isRemoteEnabled' => true,
        ]);

        if ($request->query('stream') == '1') {
            return $pdf->stream("Bulk_Teacher_ID_Cards.pdf");
        }
        return $pdf->download("Bulk_Teacher_ID_Cards.pdf");
    }

    /**
     * Export Bulk Teacher ID Cards into Microsoft Word (.docx) document.
     */
    private function exportDocx($teachers, $schoolSession, $school, $layout = 'dualsided')
    {
        $phpWord = new \PhpOffice\PhpWord\PhpWord();

        $phpWord->getDocInfo()
            ->setTitle('Bulk Faculty & Staff ID Cards')
            ->setCompany($school?->name ?? config('app.name'))
            ->setDescription('Printable and editable Bulk Teacher ID Cards');

        $section = $phpWord->addSection([
            'orientation'  => 'portrait',
            'paperSize'    => 'A4',
            'marginTop'    => 500,
            'marginBottom' => 500,
            'marginLeft'   => 500,
            'marginRight'  => 500,
        ]);

        $schoolName  = $school?->name ?? config('app.name', 'School Hub Management');
        $schoolAdd   = $school?->address ?? 'Kathmandu, Nepal';
        $schoolPhone = $school?->phone ?? '+977-1-4412345';
        $sessionName = $schoolSession?->session_name ?? (date('Y') . '-' . (date('Y')+1));

        $headerBgColor   = '0F172A'; // Slate-900 / dark theme for teachers
        $cardBorderColor = '94A3B8';

        $schTitleStyle = ['name' => 'Arial', 'size' => 10, 'bold' => true, 'color' => 'FFFFFF'];
        $schSubStyle   = ['name' => 'Arial', 'size' => 7.5, 'color' => 'CBD5E1'];
        $tNameStyle    = ['name' => 'Arial', 'size' => 10, 'bold' => true, 'color' => '0F172A'];
        $lblStyle      = ['name' => 'Arial', 'size' => 8, 'bold' => true, 'color' => '475569'];
        $valStyle      = ['name' => 'Arial', 'size' => 8, 'bold' => true, 'color' => '0F172A'];
        $ftStyle       = ['name' => 'Arial', 'size' => 7.5, 'color' => '64748B'];
        $centerAlign   = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER];

        $tableStyle = [
            'alignment'  => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
            'cellMargin' => 60,
        ];
        $gridTable = $section->addTable($tableStyle);

        $cardCellWidth = 4750;
        $items = $teachers->values();

        for ($i = 0; $i < count($items); $i++) {
            $t = $items[$i];

            if (!$t->qr_token) {
                $t->qr_token = Str::random(32);
                $t->saveQuietly();
            }

            $gridTable->addRow(null, ['cantSplit' => true]);

            // Front Card
            $frontCell = $gridTable->addCell($cardCellWidth, [
                'borderSize'  => 8,
                'borderColor' => $cardBorderColor,
                'bgColor'     => 'FFFFFF',
                'valign'      => 'top',
            ]);

            $frontHeader = $frontCell->addTable(['cellMargin' => 40]);
            $frontHeader->addRow();
            $hCell = $frontHeader->addCell($cardCellWidth - 200, ['bgColor' => $headerBgColor]);
            $hCell->addText($schoolName, $schTitleStyle, $centerAlign);
            $hCell->addText("FACULTY & STAFF IDENTITY CARD", $schSubStyle, $centerAlign);
            $hCell->addText("{$schoolAdd} • Tel: {$schoolPhone}", $schSubStyle, $centerAlign);

            $frontCell->addTextBreak(1);

            $bodyTable = $frontCell->addTable(['cellMargin' => 30]);
            $bodyTable->addRow();

            $photoCell = $bodyTable->addCell(1300, ['valign' => 'top']);
            $localPhoto = null;
            if ($t->photo && file_exists(storage_path('app/public/' . ltrim($t->photo, '/')))) {
                $localPhoto = storage_path('app/public/' . ltrim($t->photo, '/'));
            } elseif (file_exists(public_path('imgs/profile.webp'))) {
                $localPhoto = public_path('imgs/profile.webp');
            }

            if ($localPhoto) {
                try {
                    $photoCell->addImage($localPhoto, [
                        'width'  => 55,
                        'height' => 65,
                        'align'  => 'center',
                        'borderColor' => $headerBgColor,
                        'borderSize'  => 1,
                    ]);
                } catch (\Exception $e) {
                    $photoCell->addText('[Photo]', $lblStyle, $centerAlign);
                }
            } else {
                $photoCell->addText('[Photo]', $lblStyle, $centerAlign);
            }

            $infoCell = $bodyTable->addCell(3100, ['valign' => 'top']);
            $infoCell->addText($t->full_name, $tNameStyle);
            $infoCell->addText("Designation: " . ($t->designation ?: 'Faculty Member'), $valStyle);
            $infoCell->addText("Department: " . ($t->department?->name ?: 'Academics'), $valStyle);
            $infoCell->addText("Employee ID: EMP-" . str_pad($t->id, 4, '0', STR_PAD_LEFT), $lblStyle);
            $infoCell->addText("Blood Group: " . ($t->blood_type ?: 'O+'), $valStyle);
            $infoCell->addText("Phone: " . ($t->phone ?: $schoolPhone), $valStyle);

            $frontCell->addTextBreak(1);

            $frontFtTable = $frontCell->addTable(['cellMargin' => 20]);
            $frontFtTable->addRow();
            $frontFtTable->addCell(2500)->addText("Issued Session: {$sessionName}", $ftStyle);
            $frontFtTable->addCell(1900)->addText("Director / Head", $ftStyle, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT]);

            // Back Card
            if ($layout === 'dualsided') {
                $backCell = $gridTable->addCell($cardCellWidth, [
                    'borderSize'  => 8,
                    'borderColor' => $cardBorderColor,
                    'bgColor'     => 'F8FAFC',
                    'valign'      => 'top',
                ]);

                $backHeader = $backCell->addTable(['cellMargin' => 40]);
                $backHeader->addRow();
                $bhCell = $backHeader->addCell($cardCellWidth - 200, ['bgColor' => $headerBgColor]);
                $bhCell->addText("INSTANT QR LOGIN & TERMS", $schTitleStyle, $centerAlign);

                $backCell->addTextBreak(1);
                $backCell->addText("• 1. Scan QR code with any camera for Instant Faculty Login.", $valStyle);
                $backCell->addText("• 2. Official institutional credentials for campus facilities.", $valStyle);
                $backCell->addText("• 3. This card is non-transferable property of {$schoolName}.", $valStyle);
                $backCell->addText("• 4. If found, please return to the school administrative desk.", $valStyle);
                $backCell->addText("• Emergency Contact: {$schoolPhone}", ['name' => 'Arial', 'size' => 8, 'bold' => true, 'color' => 'DC2626']);

                $backCell->addTextBreak(1);
                $backCell->addText("Staff Login Token: {$t->qr_token}", $ftStyle, $centerAlign);
            }

            if (($i + 1) % 4 === 0 && $i + 1 < count($items)) {
                $section->addPageBreak();
                $gridTable = $section->addTable($tableStyle);
            }
        }

        $filename = 'Bulk_Teacher_ID_Cards.docx';
        $writer   = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $tmpFile  = tempnam(sys_get_temp_dir(), 'teacher_idcards_');
        $writer->save($tmpFile);

        return response()->download($tmpFile, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ])->deleteFileAfterSend(true);
    }
}
