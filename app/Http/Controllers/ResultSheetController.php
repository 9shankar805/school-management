<?php

namespace App\Http\Controllers;

use App\Repositories\ResultRepository;
use App\Repositories\GradingSystemRepository;
use App\Repositories\GradeRuleRepository;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Section;
use App\Traits\SchoolSession;
use App\Interfaces\SchoolSessionInterface;
use App\Interfaces\SchoolClassInterface;
use App\Interfaces\SemesterInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Traits\AssignedTeacherCheck;

class ResultSheetController extends Controller
{
    use SchoolSession, AssignedTeacherCheck;

    protected $schoolSessionRepository;
    protected $schoolClassRepository;
    protected $semesterRepository;

    public function __construct(
        SchoolSessionInterface $schoolSessionRepository,
        SchoolClassInterface   $schoolClassRepository,
        SemesterInterface      $semesterRepository
    ) {
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolClassRepository   = $schoolClassRepository;
        $this->semesterRepository      = $semesterRepository;
        $this->middleware(['auth', 'can:view marks'])->except(['verifyMarksheet']);
    }

    // ── Selector form ─────────────────────────────────────────────────────────

    /**
     * GET /results
     * Form to pick class + semester + section.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $sessionId = $this->getSchoolCurrentSession();

        if ($user && $user->hasRole('student')) {
            // Veda Model: Defaulter Gate (withhold mark sheet if student has outstanding dues)
            $dueBalance = round(max((float) \App\Models\StudentLedger::currentBalance($user->id, $sessionId), (float) \App\Models\StudentLedger::currentBalance($user->id)), 2);
            if ($dueBalance > 0) {
                $semesters = $this->semesterRepository->getAll($sessionId);
                $semesterId = (int) $request->query('semester_id', $semesters->first()?->id ?? 0);
                $latestInvoice = \App\Models\Invoice::where('student_id', $user->id)
                    ->where('session_id', $sessionId)
                    ->whereIn('status', ['unpaid', 'partial'])
                    ->latest()
                    ->first();

                return view('results.fee-restricted', compact('dueBalance', 'user', 'semesters', 'semesterId', 'sessionId', 'latestInvoice'));
            }

            $promotionRepo = new \App\Repositories\PromotionRepository();
            $classInfo     = $promotionRepo->getStudentCurrentClass($user->id, $sessionId);

            $classId   = $classInfo?->class_id ?? \App\Models\Promotion::where('student_id', $user->id)->latest()->first()?->class_id ?? $user->academic_info?->class_id;
            $sectionId = $classInfo?->section_id ?? \App\Models\Promotion::where('student_id', $user->id)->latest()->first()?->section_id ?? $user->academic_info?->section_id ?? 0;

            $semesters = $this->semesterRepository->getAll($sessionId);
            $semesterId = (int) $request->query('semester_id', $semesters->first()?->id ?? 0);

            $result = null;

            if ($classId && $semesterId) {
                $repo   = new ResultRepository();
                $result = $repo->getStudentResult($user->id, $semesterId, $classId, $sectionId, $sessionId);

                request()->merge([
                    'class_id'    => $classId,
                    'section_id'  => $sectionId,
                    'semester_id' => $semesterId
                ]);
            }

            if ($result && isset($result['student'])) {
                return view('results.student', compact('result', 'semesters', 'semesterId', 'sessionId'));
            }

            return view('results.student-empty', compact('semesters', 'semesterId', 'sessionId'));
        }

        $classes   = $this->schoolClassRepository->getAllBySession($sessionId);
        $semesters = $this->semesterRepository->getAll($sessionId);

        return view('results.index', compact('classes', 'semesters', 'sessionId'));
    }

    // ── Class result sheet (web) ──────────────────────────────────────────────

    /**
     * GET /results/class
     * Full class result table with GPA + rankings.
     */
    public function classResult(Request $request)
    {
        $request->validate([
            'class_id'    => 'required|integer',
            'section_id'  => 'nullable|integer',
            'semester_id' => 'required|integer',
        ]);

        $sessionId  = $this->getSchoolCurrentSession();
        $classId    = (int) $request->class_id;
        $sectionId  = (int) $request->query('section_id', 0);
        $semesterId = (int) $request->semester_id;

        $repo      = new ResultRepository();
        $results   = $repo->getClassResults($semesterId, $classId, $sectionId, $sessionId);
        $class     = SchoolClass::find($classId);
        $section   = $sectionId > 0 ? Section::find($sectionId) : null;
        $semester  = Semester::find($semesterId);

        // Course names for the table header (from all students' courses)
        $courses   = $results->flatMap(function($r) {
            return collect($r['courses'] ?? [])->pluck('course');
        })->unique('id')->filter()->values();

        return view('results.class', compact(
            'results', 'class', 'section', 'semester',
            'courses', 'sessionId'
        ));
    }

    // ── Single student result ─────────────────────────────────────────────────

    /**
     * GET /results/student/{studentId}
     */
    public function studentResult(Request $request, int $studentId)
    {
        $request->validate([
            'class_id'    => 'nullable|integer',
            'section_id'  => 'nullable|integer',
            'semester_id' => 'nullable|integer',
        ]);

        // Students can only view their own results
        if (auth()->user()->hasRole('student') && auth()->id() !== $studentId) {
            abort(403);
        }

        $sessionId   = $this->getSchoolCurrentSession();
        $studentUser = \App\Models\User::findOrFail($studentId);

        $classId     = (int) $request->query('class_id', 0);
        $sectionId   = (int) $request->query('section_id', 0);
        $semesterId  = (int) $request->query('semester_id', 0);

        if (!$semesterId) {
            $semesters  = $this->semesterRepository->getAll($sessionId);
            $semesterId = $semesters->first()?->id ?? 0;
        }

        if (!$classId) {
            $promotionRepo = new \App\Repositories\PromotionRepository();
            $classInfo     = $promotionRepo->getStudentCurrentClass($studentId, $sessionId);
            $classId       = $classInfo?->class_id ?? $studentUser->academic_info?->class_id ?? 0;
            $sectionId     = $sectionId ?: ($classInfo?->section_id ?? $studentUser->academic_info?->section_id ?? 0);
        }

        $repo   = new ResultRepository();
        $result = $repo->getStudentResult($studentId, $semesterId, $classId, $sectionId, $sessionId);

        return view('results.student', compact('result', 'sessionId'));
    }

    // ── Merit list ────────────────────────────────────────────────────

    /**
     * GET /results/merit
     */
    public function meritList(Request $request)
    {
        $request->validate([
            'class_id'    => 'required|integer',
            'section_id'  => 'nullable|integer',
            'semester_id' => 'required|integer',
        ]);

        $sessionId  = $this->getSchoolCurrentSession();
        $classId    = (int) $request->class_id;
        $sectionId  = (int) $request->query('section_id', 0);
        $semesterId = (int) $request->semester_id;

        $repo     = new ResultRepository();
        $results  = $repo->getClassResults($semesterId, $classId, $sectionId, $sessionId);
        $class    = SchoolClass::find($classId);
        $section  = $sectionId > 0 ? Section::find($sectionId) : null;
        $semester = Semester::find($semesterId);

        return view('results.merit-list', compact(
            'results', 'class', 'section', 'semester', 'sessionId'
        ));
    }

    // ── PDF exports ───────────────────────────────────────────────────────────

    /**
     * GET /results/class/pdf
     * Class result sheet as PDF (landscape A4).
     */
    public function classResultPdf(Request $request)
    {
        $request->validate([
            'class_id'    => 'required|integer',
            'section_id'  => 'nullable|integer',
            'semester_id' => 'required|integer',
        ]);

        $sessionId  = $this->getSchoolCurrentSession();
        $classId    = (int) $request->class_id;
        $sectionId  = (int) $request->query('section_id', 0);
        $semesterId = (int) $request->semester_id;

        $school     = config('tenant.school') ?? \App\Models\School::first();
        $logoSrc    = $this->getSchoolLogoBase64($school);

        $repo     = new ResultRepository();
        $results  = $repo->getClassResults($semesterId, $classId, $sectionId, $sessionId);
        $class    = SchoolClass::find($classId);
        $section  = $sectionId > 0 ? Section::find($sectionId) : null;
        $semester = Semester::find($semesterId);
        $courses  = $results->flatMap(function($r) {
            return collect($r['courses'] ?? [])->pluck('course');
        })->unique('id')->filter()->values();

        $pdf = Pdf::loadView('results.pdf.class-sheet', compact(
            'results', 'class', 'section', 'semester', 'courses', 'school', 'logoSrc'
        ))
        ->setPaper('A4', 'landscape')
        ->setOptions(['defaultFont' => 'sans-serif', 'dpi' => 96, 'isRemoteEnabled' => true]);

        $secName = $section ? '-' . $section->section_name : '';
        if ($request->query('stream') == '1') {
            return $pdf->stream("result-sheet-{$class->class_name}{$secName}-{$semester->semester_name}.pdf");
        }
        return $pdf->download("result-sheet-{$class->class_name}{$secName}-{$semester->semester_name}.pdf");
    }

    /**
     * GET /results/student/{studentId}/report-card
     * Individual report card PDF.
     */
    public function reportCard(Request $request, int $studentId)
    {
        $request->validate([
            'class_id'    => 'nullable|integer',
            'section_id'  => 'nullable|integer',
            'semester_id' => 'required|integer',
        ]);

        if (auth()->user()->hasRole('student')) {
            if (auth()->id() !== $studentId) {
                abort(403);
            }
            $dueBalance = round(max((float) \App\Models\StudentLedger::currentBalance($studentId, $this->getSchoolCurrentSession()), (float) \App\Models\StudentLedger::currentBalance($studentId)), 2);
            if ($dueBalance > 0) {
                return redirect()->route('results.index')
                    ->with('error', '⚠️ Report Card Download Locked: Please clear your outstanding fee dues (Rs. ' . number_format($dueBalance, 2) . ') to download your official mark sheet.');
            }
        }

        $sessionId   = $this->getSchoolCurrentSession();
        $studentUser = \App\Models\User::findOrFail($studentId);

        $classId     = (int) $request->query('class_id', 0);
        $sectionId   = (int) $request->query('section_id', 0);
        $semesterId  = (int) $request->query('semester_id', 0);

        if (!$classId) {
            $promotionRepo = new \App\Repositories\PromotionRepository();
            $classInfo     = $promotionRepo->getStudentCurrentClass($studentId, $sessionId);
            $classId       = $classInfo?->class_id ?? $studentUser->academic_info?->class_id ?? 0;
            $sectionId     = $sectionId ?: ($classInfo?->section_id ?? $studentUser->academic_info?->section_id ?? 0);
        }

        $school      = config('tenant.school') ?? ($studentUser ? \App\Models\School::find($studentUser->school_id) : null) ?? \App\Models\School::first();
        $logoSrc     = $this->getSchoolLogoBase64($school);

        $verifyToken = $this->generateMarksheetToken($studentId, $semesterId, $classId, $sessionId);
        $verifyUrl   = route('results.verify', ['token' => $verifyToken]);
        $qrSrc       = $this->getQrCodeSrc('https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=' . urlencode($verifyUrl));

        $repo       = new ResultRepository();
        $result     = $repo->getStudentResult(
            $studentId,
            $semesterId,
            $classId,
            $sectionId,
            $sessionId
        );

        $pdf = Pdf::loadView('results.pdf.report-card', compact('result', 'verifyUrl', 'qrSrc', 'verifyToken', 'school', 'logoSrc'))
            ->setPaper('A4', 'portrait')
            ->setOptions(['defaultFont' => 'sans-serif', 'dpi' => 120, 'isRemoteEnabled' => true, 'chroot' => public_path()]);

        $stuName = $result['student']?->full_name ?? 'Student';
        if ($request->query('stream') == '1') {
            return $pdf->stream("report-card-{$stuName}.pdf");
        }
        return $pdf->download("report-card-{$stuName}.pdf");
    }

    /**
     * GET /results/class/report-cards
     * Bulk generate all individual report cards for all students in the class.
     */
    public function classReportCardsPdf(Request $request)
    {
        $request->validate([
            'class_id'    => 'required|integer',
            'semester_id' => 'required|integer',
            'section_id'  => 'nullable|integer',
        ]);

        $sessionId  = $this->getSchoolCurrentSession();
        $classId    = (int) $request->class_id;
        $sectionId  = (int) $request->query('section_id', 0);
        $semesterId = (int) $request->semester_id;

        $user = auth()->user();
        if (!$this->isMarksManagementUser($user)) {
            $isClassTeacher = $this->isAssignedClassTeacher($user, $sessionId, $classId, $sectionId);
            if (!$isClassTeacher) {
                abort(403, 'Access Restricted: Only designated class teachers (Class In-Charge) and school administrators can print individual report cards for the entire class.');
            }
        }

        $repo      = new ResultRepository();
        $results   = $repo->getClassResults($semesterId, $classId, $sectionId, $sessionId);
        $class     = SchoolClass::find($classId);
        $section   = $sectionId > 0 ? Section::find($sectionId) : null;
        $semester  = Semester::find($semesterId);
        $school    = config('tenant.school') ?? \App\Models\School::first();
        $logoSrc   = $this->getSchoolLogoBase64($school);

        if ($results->isEmpty()) {
            return back()->with('error', "No student results found for {$class->class_name} in {$semester->semester_name}.");
        }

        $cards = [];
        foreach ($results as $row) {
            $stuId = $row['student']->id;
            $res   = $repo->getStudentResult($stuId, $semesterId, $classId, $sectionId, $sessionId);
            $token = $this->generateMarksheetToken($stuId, $semesterId, $classId, $sessionId);
            $vUrl  = route('results.verify', ['token' => $token]);
            $qr    = $this->getQrCodeSrc('https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=' . urlencode($vUrl));

            $cards[] = [
                'result'      => $res,
                'verifyToken' => $token,
                'verifyUrl'   => $vUrl,
                'qrSrc'       => $qr,
            ];
        }

        $pdf = Pdf::loadView('results.pdf.class-report-cards', compact('cards', 'class', 'section', 'semester', 'school', 'logoSrc'))
            ->setPaper('A4', 'portrait')
            ->setOptions(['defaultFont' => 'sans-serif', 'dpi' => 120, 'isRemoteEnabled' => true, 'chroot' => public_path()]);

        $secName = $section ? '-' . $section->section_name : '';
        $filename = "all-report-cards-{$class->class_name}{$secName}-{$semester->semester_name}.pdf";

        if ($request->query('stream') == '1') {
            return $pdf->stream($filename);
        }
        return $pdf->download($filename);
    }

    public function getSchoolLogoBase64($school = null): string
    {
        $filePath = $this->getSchoolLogoPath($school);
        if ($filePath && file_exists($filePath)) {
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            
            // GD WebP to PNG conversion if GD is present
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

    public function generateMarksheetToken(int $studentId, int $semesterId, int $classId, int $sessionId): string
    {
        $payload = "{$studentId}:{$semesterId}:{$classId}:{$sessionId}";
        $hash    = substr(hash_hmac('sha256', $payload, config('app.key', 'school-hub-salt')), 0, 16);
        $token   = base64_encode("{$payload}:{$hash}");
        return str_replace(['+', '/', '='], ['-', '_', ''], $token);
    }

    /**
     * Public endpoint: Live scan & verify authenticity of an official Marksheet / Grade Sheet
     * GET /results/verify/{token}
     */
    public function verifyMarksheet(string $token)
    {
        $padded = str_pad(str_replace(['-', '_'], ['+', '/'], $token), strlen($token) % 4 === 0 ? strlen($token) : strlen($token) + (4 - strlen($token) % 4), '=', STR_PAD_RIGHT);
        $decoded = base64_decode($padded);

        if (!$decoded) {
            abort(404, 'Invalid Marksheet Verification Token.');
        }

        $parts = explode(':', $decoded);
        if (count($parts) !== 5) {
            abort(404, 'Malformed Marksheet Verification Token.');
        }

        [$studentId, $semesterId, $classId, $sessionId, $hash] = $parts;
        $expectedHash = substr(hash_hmac('sha256', "{$studentId}:{$semesterId}:{$classId}:{$sessionId}", config('app.key', 'school-hub-salt')), 0, 16);

        if (!hash_equals($expectedHash, $hash)) {
            abort(403, 'Verification failed: Marksheet signature mismatch or tampered QR code.');
        }

        $repo = new ResultRepository();
        $result = $repo->getStudentResult(
            (int)$studentId,
            (int)$semesterId,
            (int)$classId,
            0,
            (int)$sessionId
        );

        if (!$result || !isset($result['student'])) {
            abort(404, 'Official result record not found in institutional archives.');
        }

        $student  = $result['student'];
        $school   = \App\Models\School::find($student->school_id) ?? \App\Models\School::first();
        $class    = \App\Models\SchoolClass::find((int)$classId);
        $semester = \App\Models\Semester::find((int)$semesterId);
        $session  = \App\Models\SchoolSession::find((int)$sessionId);
        $section  = \App\Models\Section::find($student->academic_info?->section_id) ?? \App\Models\Section::find((int)($result['section_id'] ?? 0));

        return view('results.verify', compact('result', 'student', 'school', 'class', 'semester', 'session', 'section', 'token', 'studentId', 'semesterId', 'classId', 'sessionId'));
    }

    /**
     * GET /results/class/excel
     */
    public function classResultExcel(Request $request)
    {
        $request->validate([
            'class_id'    => 'required|integer',
            'section_id'  => 'required|integer',
            'semester_id' => 'required|integer',
        ]);

        $sessionId  = $this->getSchoolCurrentSession();
        $classId    = (int) $request->class_id;
        $sectionId  = (int) $request->section_id;
        $semesterId = (int) $request->semester_id;

        $repo     = new ResultRepository();
        $results  = $repo->getClassResults($semesterId, $classId, $sectionId, $sessionId);
        $class    = SchoolClass::findOrFail($classId);
        $semester = Semester::findOrFail($semesterId);

        return Excel::download(
            new \App\Exports\MarksReportExport($results->toArray(), $class, $semester),
            "results-{$class->class_name}-{$semester->semester_name}.xlsx"
        );
    }

    // ── Performance analytics ─────────────────────────────────────────────────

    /**
     * GET /results/analytics
     */
    public function analytics(Request $request)
    {
        $sessionId  = $this->getSchoolCurrentSession();
        $classes    = $this->schoolClassRepository->getAllBySession($sessionId);
        $semesters  = $this->semesterRepository->getAll($sessionId);

        $classId    = (int) $request->query('class_id',    $classes->first()?->id ?? 0);
        $semesterId = (int) $request->query('semester_id', $semesters->first()?->id ?? 0);

        $repo               = new ResultRepository();
        $subjectAverages    = $repo->getSubjectAverages($sessionId, $semesterId, $classId);
        $gradeDistribution  = $repo->getGradeDistribution($sessionId, $semesterId, $classId);
        $classResults       = $repo->getClassResults($semesterId, $classId, 0, $sessionId);

        $topStudents        = $classResults->take(5);
        $avgGpa             = $classResults->isNotEmpty()
            ? round($classResults->avg('gpa'), 2)
            : 0;

        return view('results.analytics', compact(
            'classes', 'semesters', 'classId', 'semesterId',
            'subjectAverages', 'gradeDistribution',
            'topStudents', 'avgGpa', 'sessionId'
        ));
    }
}
