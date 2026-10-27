<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceStoreRequest;
use App\Interfaces\AcademicSettingInterface;
use App\Interfaces\SchoolClassInterface;
use App\Interfaces\SchoolSessionInterface;
use App\Interfaces\SectionInterface;
use App\Interfaces\UserInterface;
use App\Jobs\SendAbsentParentNotification;
use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Repositories\AttendanceRepository;
use App\Repositories\CourseRepository;
use App\Traits\SchoolSession;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceController extends Controller
{
    use SchoolSession;

    protected $academicSettingRepository;
    protected $schoolSessionRepository;
    protected $schoolClassRepository;
    protected $sectionRepository;
    protected $userRepository;

    public function __construct(
        UserInterface            $userRepository,
        AcademicSettingInterface $academicSettingRepository,
        SchoolSessionInterface   $schoolSessionRepository,
        SchoolClassInterface     $schoolClassRepository,
        SectionInterface         $sectionRepository
    ) {
        $this->middleware(['can:view attendances']);

        $this->userRepository            = $userRepository;
        $this->academicSettingRepository = $academicSettingRepository;
        $this->schoolSessionRepository   = $schoolSessionRepository;
        $this->schoolClassRepository     = $schoolClassRepository;
        $this->sectionRepository         = $sectionRepository;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // ORIGINAL METHODS (unchanged)
    // ──────────────────────────────────────────────────────────────────────────

    public function index()
    {
        if (auth()->check() && auth()->user()->hasRole('student')) {
            return redirect()->route('student.attendance.show', auth()->id());
        }
        return redirect()->route('attendance.create.show');
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        if (!$user || $user->isStudent() || $user->isParent() || !$user->isStaff()) {
            abort(403, 'Unauthorized to take attendance.');
        }

        try {
            $academic_setting          = $this->academicSettingRepository->getAcademicSetting();
            $current_school_session_id = $this->getSchoolCurrentSession();
            $class_id                  = $request->query('class_id');
            $isRestrictedTeacher       = $user && ($user->hasRole('teacher') || $user->hasRole('class-teacher'))
                                         && !$user->hasAnyRole(['super-admin', 'admin', 'principal', 'vice-principal', 'academic-coordinator', 'attendance-officer']);

            if ($isRestrictedTeacher) {
                $assignedClassIds = \App\Models\AssignedTeacher::where('session_id', $current_school_session_id)
                    ->where('teacher_id', $user->id)
                    ->where('is_class_teacher', true)
                    ->pluck('class_id')
                    ->unique()
                    ->filter()
                    ->all();

                $school_classes = \App\Models\SchoolClass::with(['courses', 'syllabi', 'sections' => function($q) use ($current_school_session_id, $user) {
                    $inChargeSectionIds = \App\Models\AssignedTeacher::where('session_id', $current_school_session_id)
                        ->where('teacher_id', $user->id)
                        ->where('is_class_teacher', true)
                        ->pluck('section_id')
                        ->unique()
                        ->filter()
                        ->all();
                    $q->whereIn('id', $inChargeSectionIds);
                }, 'sections.promotions', 'promotions'])
                    ->where('session_id', $current_school_session_id)
                    ->whereIn('id', $assignedClassIds)
                    ->get();
            } else {
                $school_classes = $this->schoolClassRepository->getAllWithCoursesBySession($current_school_session_id);
            }

            if ($class_id === null) {
                return view('attendances.take', [
                    'current_school_session_id' => $current_school_session_id,
                    'academic_setting'          => $academic_setting,
                    'school_classes'            => $school_classes,
                    'student_list'              => null,
                    'school_class'              => null,
                    'school_section'            => null,
                    'attendance_count'          => 0,
                ]);
            }

            // Verify teacher is designated Class In-Charge of this class
            if ($isRestrictedTeacher && !in_array((int)$class_id, $assignedClassIds)) {
                return redirect()->route('attendance.create.show')
                    ->with('error', 'Only the designated Class In-Charge (Class Teacher) is authorized to take attendance for this class.');
            }

            $section_id = (int) $request->query('section_id', 0);
            $course_id  = $request->query('course_id');

            // Strictly verify teacher is the designated Class In-Charge of this specific section
            if ($isRestrictedTeacher) {
                $inChargeSectionIds = \App\Models\AssignedTeacher::where('session_id', $current_school_session_id)
                    ->where('teacher_id', $user->id)
                    ->where('class_id', $class_id)
                    ->where('is_class_teacher', true)
                    ->pluck('section_id')
                    ->unique()
                    ->filter()
                    ->values()
                    ->all();

                $classHasSections = \App\Models\Section::where('class_id', $class_id)->exists();

                if ($classHasSections) {
                    if (empty($inChargeSectionIds)) {
                        return redirect()->route('attendance.create.show')
                            ->with('error', 'Unauthorized: You are not assigned as Class In-Charge for any section of this class.');
                    }

                    if ($section_id === 0 && count($inChargeSectionIds) === 1) {
                        $section_id = (int) $inChargeSectionIds[0];
                    }

                    if (!in_array($section_id, $inChargeSectionIds, true)) {
                        $inChargeSectionNames = \App\Models\Section::whereIn('id', $inChargeSectionIds)->pluck('section_name')->implode(', ');
                        return redirect()->route('attendance.create.show')
                            ->with('error', "Unauthorized: You are only the Class In-Charge for Section {$inChargeSectionNames}. You cannot take attendance for other sections.");
                    }
                }
            }

            $student_list   = $this->userRepository->getAllStudents($current_school_session_id, $class_id, $section_id);
            $school_class   = $this->schoolClassRepository->findById($class_id);
            $school_section = $this->sectionRepository->findById($section_id);

            $attendanceRepository = new AttendanceRepository();

            if ($academic_setting->attendance_type === 'section') {
                $attendance_count = $attendanceRepository->getSectionAttendance($class_id, $section_id, $current_school_session_id)->count();
            } else {
                $attendance_count = $attendanceRepository->getCourseAttendance($class_id, $course_id, $current_school_session_id)->count();
            }

            $selected_date = $request->query('date', Carbon::today()->toDateString());
            $existing_attendances = \App\Models\Attendance::where('class_id', $class_id)
                ->when($section_id > 0, fn($q) => $q->where('section_id', $section_id))
                ->when($course_id > 0, fn($q) => $q->where('course_id', $course_id))
                ->where('session_id', $current_school_session_id)
                ->whereDate('date', $selected_date)
                ->get()
                ->keyBy('student_id');

            return view('attendances.take', [
                'current_school_session_id' => $current_school_session_id,
                'academic_setting'          => $academic_setting,
                'school_classes'            => $school_classes,
                'student_list'              => $student_list,
                'school_class'              => $school_class,
                'school_section'            => $school_section,
                'attendance_count'          => $attendance_count,
                'existing_attendances'      => $existing_attendances,
                'selected_date'             => $selected_date,
            ]);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function store(AttendanceStoreRequest $request)
    {
        $user = auth()->user();
        if (!$user || $user->isStudent() || $user->isParent() || !$user->isStaff()) {
            abort(403, 'Unauthorized to submit attendance.');
        }

        try {
            $validated = $request->validated();
            $current_school_session_id = $validated['session_id'] ?? $this->getSchoolCurrentSession();
            $isRestrictedTeacher = $user && ($user->hasRole('teacher') || $user->hasRole('class-teacher'))
                                 && !$user->hasAnyRole(['super-admin', 'admin', 'principal', 'vice-principal', 'academic-coordinator', 'attendance-officer']);

            if ($isRestrictedTeacher) {
                $classId = (int) ($validated['class_id'] ?? 0);
                $sectionId = (int) ($validated['section_id'] ?? 0);

                $inChargeSectionIds = \App\Models\AssignedTeacher::where('session_id', $current_school_session_id)
                    ->where('teacher_id', $user->id)
                    ->where('class_id', $classId)
                    ->where('is_class_teacher', true)
                    ->pluck('section_id')
                    ->unique()
                    ->filter()
                    ->values()
                    ->all();

                if (empty($inChargeSectionIds)) {
                    return back()->with('error', 'Unauthorized: You are not assigned as Class In-Charge for this class.');
                }

                $classHasSections = \App\Models\Section::where('class_id', $classId)->exists();
                if ($classHasSections) {
                    if ($sectionId === 0 && count($inChargeSectionIds) === 1) {
                        $sectionId = (int) $inChargeSectionIds[0];
                        $validated['section_id'] = $sectionId;
                    }

                    if (!in_array($sectionId, $inChargeSectionIds, true)) {
                        $inChargeSectionNames = \App\Models\Section::whereIn('id', $inChargeSectionIds)->pluck('section_name')->implode(', ');
                        return back()->with('error', "Unauthorized: You are only the Class In-Charge for Section {$inChargeSectionNames}. You cannot submit attendance for other sections.");
                    }
                }
            }

            (new AttendanceRepository())->saveAttendance($validated);

            // Queue absent-parent notifications (non-blocking)
            $date      = today()->toDateString();
            $sessionId = $validated['session_id'];
            SendAbsentParentNotification::dispatchForDate((int) $sessionId, $date);

            return back()->with('status', 'Attendance saved successfully!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function show(Request $request)
    {
        if (auth()->check() && auth()->user()->hasRole('student')) {
            return redirect()->route('student.attendance.show', auth()->id());
        }
        $current_school_session_id = $this->getSchoolCurrentSession();
        $class_id                  = $request->query('class_id');
        $user                      = auth()->user();
        $isRestrictedTeacher       = $user && ($user->hasRole('teacher') || $user->hasRole('class-teacher'))
                                     && !$user->hasAnyRole(['super-admin', 'admin', 'principal', 'vice-principal', 'academic-coordinator', 'attendance-officer']);

        if ($isRestrictedTeacher) {
            $assignedClassIds = \App\Models\AssignedTeacher::where('session_id', $current_school_session_id)
                ->where('teacher_id', $user->id)
                ->where('is_class_teacher', true)
                ->pluck('class_id')
                ->unique()
                ->filter()
                ->all();

            $school_classes = \App\Models\SchoolClass::with(['courses', 'syllabi', 'sections' => function($q) use ($current_school_session_id, $user) {
                $inChargeSectionIds = \App\Models\AssignedTeacher::where('session_id', $current_school_session_id)
                    ->where('teacher_id', $user->id)
                    ->where('is_class_teacher', true)
                    ->pluck('section_id')
                    ->unique()
                    ->filter()
                    ->all();
                $q->whereIn('id', $inChargeSectionIds);
            }, 'sections.promotions', 'promotions'])
                ->where('session_id', $current_school_session_id)
                ->whereIn('id', $assignedClassIds)
                ->get();
        } else {
            $school_classes = $this->schoolClassRepository->getAllWithCoursesBySession($current_school_session_id);
        }

        if ($class_id === null) {
            return view('attendances.view', [
                'attendances'               => null,
                'school_classes'            => $school_classes,
                'current_school_session_id' => $current_school_session_id,
            ]);
        }

        if ($isRestrictedTeacher && !in_array((int)$class_id, $assignedClassIds)) {
            return redirect()->route('attendance.list.show')
                ->with('error', 'You are only authorized to view attendance for your designated Class In-Charge sections.');
        }

        $section_id = (int) $request->query('section_id', 0);
        $course_id  = $request->query('course_id');

        if ($isRestrictedTeacher) {
            $inChargeSectionIds = \App\Models\AssignedTeacher::where('session_id', $current_school_session_id)
                ->where('teacher_id', $user->id)
                ->where('class_id', $class_id)
                ->where('is_class_teacher', true)
                ->pluck('section_id')
                ->unique()
                ->filter()
                ->values()
                ->all();

            $classHasSections = \App\Models\Section::where('class_id', $class_id)->exists();
            if ($classHasSections) {
                if ($section_id === 0 && count($inChargeSectionIds) === 1) {
                    $section_id = (int) $inChargeSectionIds[0];
                }

                if (!in_array($section_id, $inChargeSectionIds, true)) {
                    $inChargeSectionNames = \App\Models\Section::whereIn('id', $inChargeSectionIds)->pluck('section_name')->implode(', ');
                    return redirect()->route('attendance.list.show')
                        ->with('error', "Unauthorized: You are only the Class In-Charge for Section {$inChargeSectionNames}. You cannot view other sections.");
                }
            }
        }
        $repo       = new AttendanceRepository();

        try {
            $academic_setting = $this->academicSettingRepository->getAcademicSetting();

            if ($academic_setting->attendance_type === 'section') {
                $attendances = $repo->getSectionAttendance($class_id, $section_id, $current_school_session_id);
            } else {
                $attendances = $repo->getCourseAttendance($class_id, $course_id, $current_school_session_id);
            }

            return view('attendances.view', [
                'attendances'               => $attendances,
                'school_classes'            => $school_classes,
                'current_school_session_id' => $current_school_session_id,
            ]);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function showStudentAttendance(Request $request, int $id)
    {
        if (auth()->user()->hasRole('student') && auth()->id() !== $id) {
            abort(403);
        }

        $current_school_session_id = $this->getSchoolCurrentSession();
        $repo                      = new AttendanceRepository();
        $attendances               = $repo->getStudentAttendance($current_school_session_id, $id);
        $student                   = $this->userRepository->findStudent($id);

        $month = (int) $request->query('month', now()->month);
        $year  = (int) $request->query('year', now()->year);

        $totalAttendance = $attendances->count();
        $presentCount    = $attendances->filter(fn($a) => in_array(strtolower((string)$a->status), ['present', 'on', '1']))->count();
        $absentCount     = $attendances->filter(fn($a) => in_array(strtolower((string)$a->status), ['absent', 'off', '0']))->count();
        $attendancePct   = $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100) : 0;
        $shortageDeficit = max(0, (int) ceil(($totalAttendance * 0.75) - $presentCount));

        $promotionRepo = new \App\Repositories\PromotionRepository();
        $classInfo     = $promotionRepo->getStudentCurrentClass($id, $current_school_session_id);

        return view('attendances.attendance', compact(
            'attendances', 'student', 'totalAttendance', 'presentCount',
            'absentCount', 'attendancePct', 'shortageDeficit', 'classInfo',
            'month', 'year', 'current_school_session_id'
        ));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // ANALYTICS DASHBOARD
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * GET /attendances/analytics
     */
    public function analytics(Request $request)
    {
        $user = auth()->user();
        if (!$user || $user->isStudent() || $user->isParent() || !$user->isStaff()) {
            abort(403, 'Unauthorized to view attendance analytics.');
        }

        $this->authorize('view attendances');

        $sessionId = $this->getSchoolCurrentSession();
        $repo      = new AttendanceRepository();

        $classes   = SchoolClass::whereHas('promotions', fn($q) => $q->where('session_id', $sessionId))
            ->orderBy('class_name')
            ->get();

        $classId   = (int) $request->query('class_id', $classes->first()?->id ?? 0);
        $days      = (int) $request->query('days', 30);

        // KPI cards
        $todaySummary = $repo->getTodaySummary($sessionId);

        // 7/30-day trend for chart
        $trend = $repo->getDailyTrend($sessionId, $classId, $days);

        // Class-level table for today
        $classSummary = $repo->getClassSummaryForDate($sessionId, today()->toDateString());

        // Shortage list (below 75%)
        $shortageStudents = $repo->getShortageStudents($sessionId, 75.0);

        // Heatmap for current month
        $heatmap = $repo->getMonthlyHeatmap($sessionId, $classId, now()->year, now()->month);

        return view('attendances.analytics', compact(
            'todaySummary',
            'trend',
            'classSummary',
            'shortageStudents',
            'heatmap',
            'classes',
            'classId',
            'days',
            'sessionId'
        ));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // MONTHLY REPORT  (PDF + Excel)
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * GET /attendances/report
     * Selector form for month/class.
     */
    public function reportForm(Request $request)
    {
        $user = auth()->user();
        if (!$user || $user->isStudent() || $user->isParent() || !$user->isStaff()) {
            abort(403, 'Unauthorized.');
        }

        $this->authorize('view attendances');

        $sessionId = $this->getSchoolCurrentSession();
        $classes   = SchoolClass::whereHas('promotions', fn($q) => $q->where('session_id', $sessionId))
            ->orderBy('class_name')
            ->get();

        return view('attendances.report-form', compact('classes', 'sessionId'));
    }

    /**
     * GET /attendances/report/pdf
     * Generate and stream the monthly PDF.
     */
    public function monthlyReportPdf(Request $request)
    {
        $this->authorize('view attendances');

        $request->validate([
            'class_id' => 'required|integer|exists:school_classes,id',
            'month'    => 'required|integer|between:1,12',
            'year'     => 'required|integer|min:2000|max:2100',
        ]);

        $sessionId = $this->getSchoolCurrentSession();
        $classId   = (int) $request->class_id;
        $month     = (int) $request->month;
        $year      = (int) $request->year;

        $repo     = new AttendanceRepository();
        $rows     = $repo->getMonthlyStudentSummary($sessionId, $classId, $year, $month);
        $class    = SchoolClass::findOrFail($classId);
        $heatmap  = $repo->getMonthlyHeatmap($sessionId, $classId, $year, $month);

        $monthLabel = Carbon::createFromDate($year, $month, 1)->format('F Y');

        $pdf = Pdf::loadView('attendances.report-pdf', compact(
            'rows', 'class', 'monthLabel', 'heatmap', 'year', 'month'
        ))
        ->setPaper('A4', 'landscape')
        ->setOptions(['defaultFont' => 'sans-serif', 'dpi' => 96]);

        return $pdf->stream("attendance-report-{$class->class_name}-{$monthLabel}.pdf");
    }

    /**
     * GET /attendances/report/excel
     * Export monthly attendance as an Excel file.
     */
    public function monthlyReportExcel(Request $request)
    {
        $this->authorize('view attendances');

        $request->validate([
            'class_id' => 'required|integer|exists:school_classes,id',
            'month'    => 'required|integer|between:1,12',
            'year'     => 'required|integer|min:2000|max:2100',
        ]);

        $sessionId = $this->getSchoolCurrentSession();
        $classId   = (int) $request->class_id;
        $month     = (int) $request->month;
        $year      = (int) $request->year;

        $repo       = new AttendanceRepository();
        $rows       = $repo->getMonthlyStudentSummary($sessionId, $classId, $year, $month);
        $class      = SchoolClass::findOrFail($classId);
        $monthLabel = Carbon::createFromDate($year, $month, 1)->format('F_Y');

        return Excel::download(
            new \App\Exports\AttendanceMonthlyExport($rows, $class, $month, $year),
            "attendance-{$class->class_name}-{$monthLabel}.xlsx"
        );
    }

    // ──────────────────────────────────────────────────────────────────────────
    // SHORTAGE ALERTS
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * GET /attendances/shortage
     */
    public function shortage(Request $request)
    {
        $this->authorize('view attendances');

        $sessionId  = $this->getSchoolCurrentSession();
        $threshold  = (float) $request->query('threshold', 75);
        $repo       = new AttendanceRepository();
        $students   = $repo->getShortageStudents($sessionId, $threshold);

        return view('attendances.shortage', compact('students', 'threshold', 'sessionId'));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // BULK CSV IMPORT
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * GET /attendances/import
     * Upload form.
     */
    public function importForm()
    {
        $this->authorize('take attendances');

        $sessionId = $this->getSchoolCurrentSession();
        $classes   = SchoolClass::whereHas('promotions', fn($q) => $q->where('session_id', $sessionId))
            ->orderBy('class_name')
            ->get();

        return view('attendances.import', compact('classes', 'sessionId'));
    }

    /**
     * POST /attendances/import
     * Process uploaded CSV.
     *
     * Expected columns: student_id, date (YYYY-MM-DD), status (present/absent/on/off), late_minutes
     */
    public function import(Request $request)
    {
        $this->authorize('take attendances');

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
            'class_id' => 'required|integer|exists:school_classes,id',
        ]);

        $sessionId = $this->getSchoolCurrentSession();
        $classId   = (int) $request->class_id;

        $path = $request->file('csv_file')->store('imports/attendance');

        try {
            $handle = fopen(Storage::path($path), 'r');
            $header = null;
            $rows   = [];

            while (($line = fgetcsv($handle)) !== false) {
                if ($header === null) {
                    $header = array_map('trim', $line);
                    continue;
                }
                if (count($line) !== count($header)) continue;
                $rows[] = array_combine($header, array_map('trim', $line));
            }

            fclose($handle);
            Storage::delete($path);

        } catch (\Throwable $e) {
            Storage::delete($path);
            return back()->with('error', 'Failed to read CSV: ' . $e->getMessage());
        }

        if (empty($rows)) {
            return back()->with('error', 'The CSV file appears to be empty or has only a header row.');
        }

        $repo   = new AttendanceRepository();
        $result = $repo->bulkImportFromCsv($rows, $classId, $sessionId);

        $msg = "Import complete: {$result['imported']} rows imported, {$result['skipped']} skipped.";

        if (! empty($result['errors'])) {
            $msg .= ' Errors: ' . implode(' | ', array_slice($result['errors'], 0, 5));
            if (count($result['errors']) > 5) {
                $msg .= ' (and ' . (count($result['errors']) - 5) . ' more)';
            }
        }

        return back()->with('status', $msg);
    }
}
