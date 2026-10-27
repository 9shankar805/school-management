<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Promotion;
use App\Models\SchoolClass;
use App\Models\SchoolSession;
use App\Traits\SchoolSession as SchoolSessionTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StudentIdCardController extends Controller
{
    use SchoolSessionTrait;

    public function __construct()
    {
        $this->middleware(['can:view students']);
    }

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
     * Generate a single student ID card PDF.
     */
    public function generate(int $studentId)
    {
        $student   = User::with(['academic_info', 'parent_info', 'parents'])->findOrFail($studentId);
        $sessionId = $this->getSchoolCurrentSession();

        $schoolSession = SchoolSession::find($sessionId);
        $school        = config('tenant.school') ?? \App\Models\School::find($student->school_id) ?? \App\Models\School::first();

        $promotion = Promotion::with('schoolClass', 'section')
            ->where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->first();

        // Ensure student has a unique QR token for instant login
        if (!$student->qr_token) {
            $student->qr_token = Str::random(32);
            $student->saveQuietly();
        }

        // Direct Instant QR Login URL
        $loginUrl = route('student.qr-login', ['token' => $student->qr_token]);
        $qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=' . urlencode($loginUrl);
        $qrSrc    = $this->getQrCodeSrc($qrApiUrl);
        $logoSrc  = $this->getSchoolLogoBase64($school);

        $photoSrc = $this->getBase64Image(public_path('imgs/profile.webp'), true);
        if ($student->photo) {
            $p = storage_path('app/public/' . ltrim($student->photo, '/'));
            if (file_exists($p)) {
                $photoSrc = $this->getBase64Image($p, true);
            }
        }

        $pdf = Pdf::loadView('students.id-card', compact('student', 'promotion', 'qrSrc', 'photoSrc', 'logoSrc', 'schoolSession', 'school', 'loginUrl'))
            ->setPaper([0, 0, 153, 243]) // Vertical CR80 portrait card (54mm x 85.6mm)
            ->setOptions([
                'dpi' => 150, 
                'defaultFont' => 'sans-serif',
                'isRemoteEnabled' => true,
                'chroot' => public_path()
            ]);

        return $pdf->stream("id-card-{$student->id}.pdf");
    }

    /**
     * Bulk ID cards: Options generator page or download in Word/PDF format.
     */
    public function bulkGenerate(Request $request)
    {
        $this->authorize('view students');

        $sessionId     = $this->getSchoolCurrentSession();
        $schoolSession = SchoolSession::find($sessionId);
        $school        = config('tenant.school') ?? \App\Models\School::first();

        // If accessed directly from menu / browser without any export parameters, show the Options Generator UI
        if (!$request->has('format') && !$request->has('export') && !$request->has('layout') && !$request->has('class_id') && !$request->has('section_id')) {
            $classes = SchoolClass::where('session_id', $sessionId)->get();
            return view('students.id-cards-bulk-page', compact('classes', 'schoolSession', 'school'));
        }

        $query = Promotion::with([
            'student.academic_info',
            'student.parent_info',
            'student.parents',
            'schoolClass',
            'section'
        ])->where('session_id', $sessionId);

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }

        $limit = (int) $request->input('limit', 100);
        $promotions = $query->take(max(1, min($limit, 250)))->get();

        $format = strtolower($request->input('format', 'pdf'));
        $layout = strtolower($request->input('layout', 'dualsided')); // 'dualsided' or 'front'

        // ── WORD (DOCX) EXPORT ───────────────────────────────────────────────
        if (in_array($format, ['word', 'docx', 'doc'])) {
            return $this->exportDocx($promotions, $schoolSession, $school, $layout);
        }

        // ── PDF EXPORT ───────────────────────────────────────────────────────
        $logoSrc = $this->getSchoolLogoBase64($school);

        $students = $promotions->map(function ($p) {
            $st = $p->student;
            if (!$st->qr_token) {
                $st->qr_token = Str::random(32);
                $st->saveQuietly();
            }

            $loginUrl = route('student.qr-login', ['token' => $st->qr_token]);
            $qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=' . urlencode($loginUrl);
            $qrSrc    = $this->getQrCodeSrc($qrApiUrl);

            $photoSrc = $this->getBase64Image(public_path('imgs/profile.webp'), true);
            if ($st && $st->photo) {
                $pPath = storage_path('app/public/' . ltrim($st->photo, '/'));
                if (file_exists($pPath)) {
                    $photoSrc = $this->getBase64Image($pPath, true);
                }
            }
            
            return [
                'student'   => $st,
                'promotion' => $p,
                'qrSrc'     => $qrSrc,
                'photoSrc'  => $photoSrc,
                'loginUrl'  => $loginUrl,
            ];
        });

        $pdf = Pdf::loadView('students.id-card-bulk', compact('students', 'schoolSession', 'school', 'layout', 'logoSrc'))
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'dpi' => 120, 
                'defaultFont' => 'sans-serif',
                'isRemoteEnabled' => true,
                'chroot' => public_path()
            ]);

        $className = $request->filled('class_id') ? 'Class-' . $request->class_id : 'All-Classes';

        if ($request->query('stream') == '1') {
            return $pdf->stream("Bulk_Student_ID_Cards_{$className}.pdf");
        }
        return $pdf->download("Bulk_Student_ID_Cards_{$className}.pdf");
    }

    /**
     * Export Bulk ID Cards into a Microsoft Word (.docx) document using PhpWord.
     */
    private function exportDocx($promotions, $schoolSession, $school, $layout = 'dualsided')
    {
        $phpWord = new \PhpOffice\PhpWord\PhpWord();

        // Document properties
        $phpWord->getDocInfo()
            ->setTitle('Bulk Student ID Cards')
            ->setCompany($school?->name ?? config('app.name'))
            ->setDescription('Printable and editable Bulk Student ID Cards');

        // Section setup: A4 portrait, 0.4 inch (500 twips) margins
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
        $logoPath    = $this->getSchoolLogoPath($school);

        // Styles
        $headerBgColor   = '1E3A8A';
        $cardBorderColor = '94A3B8';
        
        $schTitleStyle = ['name' => 'Arial', 'size' => 10, 'bold' => true, 'color' => 'FFFFFF'];
        $schSubStyle   = ['name' => 'Arial', 'size' => 7.5, 'color' => 'DBEAFE'];
        $stNameStyle   = ['name' => 'Arial', 'size' => 10, 'bold' => true, 'color' => '0F172A'];
        $lblStyle      = ['name' => 'Arial', 'size' => 8, 'bold' => true, 'color' => '475569'];
        $valStyle      = ['name' => 'Arial', 'size' => 8, 'bold' => true, 'color' => '0F172A'];
        $ftStyle       = ['name' => 'Arial', 'size' => 7.5, 'color' => '64748B'];
        $centerAlign   = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER];

        // 2-column Grid Table for ID cards
        $tableStyle = [
            'alignment'  => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
            'cellMargin' => 60,
        ];
        $gridTable = $section->addTable($tableStyle);
        $cardCellWidth = 4750; // Twips (~83mm)

        $items = $promotions->values();
        for ($i = 0; $i < count($items); $i++) {
            $p  = $items[$i];
            $st = $p->student;

            if (!$st->qr_token) {
                $st->qr_token = Str::random(32);
                $st->saveQuietly();
            }

            $className   = $p->schoolClass?->class_name ?? '—';
            $sectionName = $p->section?->section_name   ?? '—';
            $idNumber    = $p->id_card_number ?: ('ST-' . date('Y') . '-' . str_pad($st->id, 4, '0', STR_PAD_LEFT));
            $bloodType   = $st->blood_type ?: 'A+';
            $dob         = $st->birthday ? \Carbon\Carbon::parse($st->birthday)->format('d M Y') : '—';
            $contact     = $st->phone ?: ($st->emergency_contact ?: $schoolPhone);

            // Parent name resolution
            $fatherName = $st->parent_info?->father_name;
            if (!$fatherName && $st->parents && $st->parents->isNotEmpty()) {
                foreach ($st->parents as $pr) {
                    if (strtolower($pr->pivot->relationship ?? $pr->relationship ?? '') === 'father') {
                        $fatherName = $pr->full_name;
                    }
                }
            }
            $fatherName = $fatherName ?: '—';

            $gridTable->addRow(null, ['cantSplit' => true]);

            // ── FRONT CARD CELL ───────────────────────────────────────────────
            $frontCell = $gridTable->addCell($cardCellWidth, [
                'borderSize'  => 8,
                'borderColor' => $cardBorderColor,
                'bgColor'     => 'FFFFFF',
                'valign'      => 'top',
            ]);

            // Front Header Banner
            $frontHeader = $frontCell->addTable(['cellMargin' => 40]);
            $frontHeader->addRow();
            $hCell = $frontHeader->addCell($cardCellWidth - 200, ['bgColor' => $headerBgColor]);
            
            $hCell->addText($schoolName, $schTitleStyle, $centerAlign);
            $hCell->addText("STUDENT IDENTITY CARD • {$sessionName}", $schSubStyle, $centerAlign);
            $hCell->addText("{$schoolAdd} • Tel: {$schoolPhone}", $schSubStyle, $centerAlign);

            $frontCell->addTextBreak(1);

            // Details Table: Left Column Photo, Right Column Fields
            $bodyTable = $frontCell->addTable(['cellMargin' => 30]);
            $bodyTable->addRow();
            
            // Photo Column
            $photoCell = $bodyTable->addCell(1300, ['valign' => 'top']);
            $localPhoto = null;
            if ($st->photo && file_exists(storage_path('app/public/' . ltrim($st->photo, '/')))) {
                $localPhoto = storage_path('app/public/' . ltrim($st->photo, '/'));
            } elseif (file_exists(public_path('imgs/profile.webp'))) {
                $localPhoto = public_path('imgs/profile.webp');
            }

            if ($localPhoto) {
                try {
                    $photoCell->addImage($localPhoto, [
                        'width'       => 55,
                        'height'      => 65,
                        'align'       => 'center',
                        'borderColor' => $headerBgColor,
                        'borderSize'  => 1,
                    ]);
                } catch (\Exception $e) {
                    $photoCell->addText('[Photo]', $lblStyle, $centerAlign);
                }
            } else {
                $photoCell->addText('[Photo]', $lblStyle, $centerAlign);
            }

            // Student Info Column
            $infoCell = $bodyTable->addCell(3100, ['valign' => 'top']);
            $infoCell->addText($st->full_name, $stNameStyle);
            $infoCell->addText("Roll / Reg: {$idNumber}", $lblStyle);
            $infoCell->addText("Class: {$className} ({$sectionName})", $valStyle);
            $infoCell->addText("Father: " . Str::limit($fatherName, 22), $valStyle);
            $infoCell->addText("DOB: {$dob} | Blood: {$bloodType}", $valStyle);
            $infoCell->addText("Contact: {$contact}", $valStyle);

            $frontCell->addTextBreak(1);

            // Front Footer
            $frontFtTable = $frontCell->addTable(['cellMargin' => 20]);
            $frontFtTable->addRow();
            $frontFtTable->addCell(2500)->addText("Valid Upto: {$sessionName}", $ftStyle);
            $frontFtTable->addCell(1900)->addText("Principal Sign: ________", $ftStyle, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT]);

            // ── BACK CARD CELL (If Dual Sided) ────────────────────────────────
            if ($layout === 'dualsided') {
                $backCell = $gridTable->addCell($cardCellWidth, [
                    'borderSize'  => 8,
                    'borderColor' => $cardBorderColor,
                    'bgColor'     => 'F8FAFC',
                    'valign'      => 'top',
                ]);

                // Back Header
                $backHeader = $backCell->addTable(['cellMargin' => 40]);
                $backHeader->addRow();
                $bhCell = $backHeader->addCell($cardCellWidth - 200, ['bgColor' => $headerBgColor]);
                $bhCell->addText("INSTANT QR LOGIN & TERMS", $schTitleStyle, $centerAlign);

                $backCell->addTextBreak(1);
                $backCell->addText("• 1. Scan QR code with any camera for Instant Portal Login.", $valStyle);
                $backCell->addText("• 2. Real-time official student verification online.", $valStyle);
                $backCell->addText("• 3. This card is non-transferable property of {$schoolName}.", $valStyle);
                $backCell->addText("• 4. If found, please return to school administration.", $valStyle);
                $backCell->addText("• Emergency Contact: {$schoolPhone}", ['name' => 'Arial', 'size' => 8, 'bold' => true, 'color' => 'DC2626']);

                $backCell->addTextBreak(1);
                $backCell->addText("Login Token: {$st->qr_token}", $ftStyle, $centerAlign);
            }

            // Pagination after every 4 students
            if (($i + 1) % 4 === 0 && $i + 1 < count($items)) {
                $section->addPageBreak();
                $gridTable = $section->addTable($tableStyle);
            }
        }

        $filename = 'Bulk_Student_ID_Cards.docx';
        $writer   = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $tmpFile  = tempnam(sys_get_temp_dir(), 'idcards_');
        $writer->save($tmpFile);

        return response()->download($tmpFile, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ])->deleteFileAfterSend(true);
    }
}
