<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Interfaces\UserInterface;
use App\Interfaces\SectionInterface;
use App\Interfaces\SchoolClassInterface;
use App\Repositories\PromotionRepository;
use App\Http\Requests\StudentStoreRequest;
use App\Http\Requests\TeacherStoreRequest;
use App\Interfaces\SchoolSessionInterface;
use App\Repositories\StudentParentInfoRepository;

class UserController extends Controller
{
    use SchoolSession;
    protected $userRepository;
    protected $schoolSessionRepository;
    protected $schoolClassRepository;
    protected $schoolSectionRepository;

    public function __construct(UserInterface $userRepository, SchoolSessionInterface $schoolSessionRepository,
    SchoolClassInterface $schoolClassRepository,
    SectionInterface $schoolSectionRepository)
    {
        $this->userRepository = $userRepository;
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolClassRepository = $schoolClassRepository;
        $this->schoolSectionRepository = $schoolSectionRepository;
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  TeacherStoreRequest $request
     * @return \Illuminate\Http\Response
     */
    public function storeTeacher(TeacherStoreRequest $request)
    {
        $authUser = auth()->user();
        if (!$authUser || !$authUser->hasAnyRole(['super-admin', 'admin', 'principal', 'hr'])) {
            abort(403, 'Unauthorized to create teacher records.');
        }

        try {
            $this->userRepository->createTeacher($request->validated());

            return back()->with('status', 'Teacher creation was successful!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function getStudentList(Request $request) {
        $authUser = auth()->user();
        if (!$authUser || $authUser->isStudent() || $authUser->isParent() || !$authUser->isStaff()) {
            abort(403, 'Unauthorized to view student directory.');
        }

        $current_school_session_id = $this->getSchoolCurrentSession();

        $class_id = $request->query('class_id', 0);
        $section_id = $request->query('section_id', 0);

        try{

            $school_classes = $this->schoolClassRepository->getAllBySession($current_school_session_id);

            $studentList = $this->userRepository->getAllStudents($current_school_session_id, $class_id, $section_id);

            $data = [
                'studentList'       => $studentList,
                'school_classes'    => $school_classes,
            ];

            return view('students.list', $data);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }


    public function showStudentProfile($id) {
        $authUser = auth()->user();
        if (!$authUser) {
            abort(401, 'Unauthenticated');
        }

        // Authorization checks:
        // - Students can ONLY view their own profile
        // - Parents can ONLY view their linked children's profile
        // - Staff (teachers/admins) can view students in their school
        if ($authUser->isStudent()) {
            if ((int)$authUser->id !== (int)$id) {
                abort(403, 'Unauthorized. Students may only access their own profile.');
            }
        } elseif ($authUser->isParent()) {
            $isMyChild = $authUser->children()->where('users.id', $id)->exists();
            if (!$isMyChild) {
                abort(403, 'Unauthorized. Parents may only access their linked children\'s profile.');
            }
        } elseif (!$authUser->isStaff()) {
            abort(403, 'Unauthorized to view student profile.');
        }

        $student = $this->userRepository->findStudent($id);
        if (!$student) {
            abort(404, 'Student not found.');
        }

        $current_school_session_id = $this->getSchoolCurrentSession();
        $promotionRepository = new PromotionRepository();
        $promotion_info = $promotionRepository->getPromotionInfoById($current_school_session_id, $id);

        // Eager-load all extended relationships for the tabbed profile
        $student->load([
            'medicalRecord',
            'emergencyContacts',
            'studentDocuments',
            'disciplinaryRecords.reporter',
            'houseAssignments',
            'scholarships.awardedBy',
            'transfers.fromClass', 'transfers.toClass', 'transfers.fromSection', 'transfers.toSection',
            'marks.exam', 'marks.course',
            'parent_info',
            'academic_info',
            'achievements',
            'statusHistory.processor',
            'currentStatus',
            'invoices.items.feeType',
            'invoices.payments',
        ]);

        // Finance & Fee Stats
        $studentInvoices = \App\Models\Invoice::withoutGlobalScopes()
            ->where('student_id', $id)
            ->with(['items.feeType', 'payments'])
            ->latest()
            ->get();
        $totalInvoiced = (float) $studentInvoices->sum(fn($i) => $i->total_payable > 0 ? $i->total_payable : $i->amount);
        $totalPaid     = (float) $studentInvoices->sum('paid_amount');
        $totalDue      = max(0, $totalInvoiced - $totalPaid);

        $studentPayments = \App\Models\Payment::withoutGlobalScopes()
            ->whereHas('invoice', fn($q) => $q->withoutGlobalScopes()->where('student_id', $id))
            ->with('invoice')
            ->latest()
            ->get();

        // Student Ledger
        $studentLedgers = \App\Models\StudentLedger::withoutGlobalScopes()
            ->where('student_id', $id)
            ->orderBy('entry_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $ledgerBalance = $studentLedgers->last()?->balance ?? null;
        if ($totalInvoiced == 0 && $studentLedgers->isNotEmpty()) {
            $totalInvoiced = (float) $studentLedgers->sum('debit');
            $totalPaid     = (float) $studentLedgers->sum('credit');
            $totalDue      = max(0, $totalInvoiced - $totalPaid);
        }

        // Applicable Class Fee Structure
        $classId = $promotion_info?->class_id;
        $classFeeStructures = $classId
            ? \App\Models\FeeStructure::withoutGlobalScopes()
                ->where('class_id', $classId)
                ->where('is_active', true)
                ->with(['category', 'feeType'])
                ->get()
            : collect();

        // Student Fee Discounts
        $studentDiscounts = \App\Models\StudentFeeDiscount::withoutGlobalScopes()
            ->where('student_id', $id)
            ->with('feeDiscount')
            ->get();

        // Gather attendance stats for the timeline
        // Student Attendance model uses 'on'/'off' as canonical status values
        $attendanceStats = [
            'total'   => \App\Models\Attendance::where('student_id', $id)->count(),
            'present' => \App\Models\Attendance::where('student_id', $id)->whereIn('status', ['on', 'present'])->count(),
        ];
        $attendanceStats['pct'] = $attendanceStats['total'] > 0
            ? round($attendanceStats['present'] / $attendanceStats['total'] * 100)
            : 0;

        // House assignment for current session
        $currentHouse = $student->houseAssignments
            ->where('session_id', $current_school_session_id)
            ->first();

        $data = [
            'student'            => $student,
            'promotion_info'     => $promotion_info,
            'attendanceStats'    => $attendanceStats,
            'currentHouse'       => $currentHouse,
            'studentInvoices'    => $studentInvoices,
            'studentPayments'    => $studentPayments,
            'studentLedgers'     => $studentLedgers,
            'classFeeStructures' => $classFeeStructures,
            'studentDiscounts'   => $studentDiscounts,
            'ledgerBalance'      => $ledgerBalance,
            'totalInvoiced'      => $totalInvoiced,
            'totalPaid'          => $totalPaid,
            'totalDue'           => $totalDue,
        ];

        // Always fetch parent_info bypassing tenant scope so null school_id rows are visible
        $studentParentInfoRepository = new StudentParentInfoRepository();
        $data['parent_info'] = $studentParentInfoRepository->getParentInfo($id);

        return view('students.profile', $data);
    }

    public function showTeacherProfile($id) {
        $authUser = auth()->user();
        if (!$authUser) {
            abort(401, 'Unauthenticated');
        }

        // Students and Parents CANNOT access teacher HR/confidential profiles
        if ($authUser->isStudent() || $authUser->isParent() || !$authUser->isStaff()) {
            abort(403, 'Unauthorized. Staff profile details are restricted.');
        }

        // Teachers can view their own profile; only management can view other teachers' full HR profile
        if ($authUser->isTeacher() && !$authUser->hasAnyRole(['super-admin', 'organization-owner', 'admin', 'principal', 'vice-principal', 'academic-coordinator', 'hr-manager', 'hr'])) {
            if ((int)$authUser->id !== (int)$id) {
                abort(403, 'Unauthorized to view other staff profiles.');
            }
        }

        $teacher = $this->userRepository->findTeacher($id);

        if (!$teacher) {
            abort(404, 'Teacher not found.');
        }

        $teacher->load([
            'assignedCourses.course','assignedCourses.section','assignedCourses.schoolClass',
            'qualifications',
            'contracts',
            'activeContract',
            'teacherDocuments',
            'leaveApplications.leaveType','leaveApplications.reviewer',
            'leaveBalances.leaveType',
            'teacherAttendance',
            'performanceReviews.reviewer',
            'trainingRecords',
            'payrolls',
            'departments',
        ]);

        $activeContract = $teacher->activeContract;

        // This month attendance stats
        $monthRecords   = $teacher->teacherAttendance->filter(fn($r) => $r->date->month === now()->month && $r->date->year === now()->year);
        $total          = $monthRecords->count();
        $presentCount   = $monthRecords->where('status','present')->count();
        $attendanceStats = [
            'total'   => $total,
            'present' => $presentCount,
            'pct'     => $total > 0 ? round($presentCount / $total * 100) : 0,
        ];

        // ── Timetable (this session) ──────────────────────────────────────────
        $sessionId = $this->getSchoolCurrentSession();
        $timetableSlots = \App\Models\Routine::with(['course','schoolClass','section'])
            ->where('teacher_id', $id)
            ->where('session_id', $sessionId)
            ->orderBy('weekday')
            ->orderBy('start')
            ->get()
            ->groupBy('weekday');

        // Classes available for the timetable assignment form
        $allClasses = $this->schoolClassRepository->getAllBySession($sessionId);

        // Courses for dropdown (filtered by class via AJAX in view)
        $allCourses = \App\Models\Course::where('session_id', $sessionId)->get();

        return view('teachers.profile', compact(
            'teacher','activeContract','attendanceStats',
            'timetableSlots','allClasses','allCourses','sessionId'
        ));
    }


    public function createStudent() {
        $authUser = auth()->user();
        if (!$authUser || !$authUser->hasAnyRole(['super-admin', 'organization-owner', 'admin', 'principal', 'vice-principal', 'academic-coordinator', 'admission-officer', 'receptionist'])) {
            abort(403, 'Unauthorized to create student records.');
        }

        $current_school_session_id = $this->getSchoolCurrentSession();

        $school_classes = $this->schoolClassRepository->getAllBySession($current_school_session_id);

        $data = [
            'current_school_session_id' => $current_school_session_id,
            'school_classes'            => $school_classes,
        ];

        return view('students.add', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StudentStoreRequest $request
     * @return \Illuminate\Http\Response
     */
    public function storeStudent(StudentStoreRequest $request)
    {
        $authUser = auth()->user();
        if (!$authUser || !$authUser->hasAnyRole(['super-admin', 'organization-owner', 'admin', 'principal', 'vice-principal', 'academic-coordinator', 'admission-officer', 'receptionist'])) {
            abort(403, 'Unauthorized to create student records.');
        }

        try {
            $this->userRepository->createStudent($request->validated());

            return back()->with('status', 'Student creation was successful!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function editStudent($student_id) {
        $authUser = auth()->user();
        if (!$authUser || !$authUser->hasAnyRole(['super-admin', 'organization-owner', 'admin', 'principal', 'vice-principal', 'academic-coordinator', 'admission-officer'])) {
            abort(403, 'Unauthorized to edit student records.');
        }

        $student = $this->userRepository->findStudent($student_id);
        if (!$student) {
            abort(404, 'Student not found.');
        }

        $studentParentInfoRepository = new StudentParentInfoRepository();
        $parent_info = $studentParentInfoRepository->getParentInfo($student_id);
        $promotionRepository = new PromotionRepository();
        $current_school_session_id = $this->getSchoolCurrentSession();
        $promotion_info = $promotionRepository->getPromotionInfoById($current_school_session_id, $student_id);

        $data = [
            'student'       => $student,
            'parent_info'   => $parent_info,
            'promotion_info'=> $promotion_info,
        ];
        return view('students.edit', $data);
    }

    public function updateStudent(Request $request) {
        $authUser = auth()->user();
        if (!$authUser || !$authUser->hasAnyRole(['super-admin', 'organization-owner', 'admin', 'principal', 'vice-principal', 'academic-coordinator', 'admission-officer'])) {
            abort(403, 'Unauthorized to update student records.');
        }

        try {
            $this->userRepository->updateStudent($request->toArray());

            return back()->with('status', 'Student update was successful!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function editTeacher($teacher_id) {
        $authUser = auth()->user();
        if (!$authUser || !$authUser->hasAnyRole(['super-admin', 'organization-owner', 'admin', 'principal', 'hr-manager', 'hr'])) {
            abort(403, 'Unauthorized to edit teacher records.');
        }

        $teacher = $this->userRepository->findTeacher($teacher_id);
        if (!$teacher) {
            abort(404, 'Teacher not found.');
        }

        $data = [
            'teacher'   => $teacher,
        ];

        return view('teachers.edit', $data);
    }

    public function updateTeacher(Request $request) {
        $authUser = auth()->user();
        if (!$authUser || !$authUser->hasAnyRole(['super-admin', 'organization-owner', 'admin', 'principal', 'hr-manager', 'hr'])) {
            abort(403, 'Unauthorized to update teacher records.');
        }

        try {
            $this->userRepository->updateTeacher($request->toArray());

            return back()->with('status', 'Teacher update was successful!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function getTeacherList(Request $request){
        $authUser = auth()->user();
        if (!$authUser || $authUser->isStudent() || $authUser->isParent() || !$authUser->isStaff()) {
            abort(403, 'Unauthorized to view teacher directory.');
        }

        $search = $request->query('search');
        $deptId = $request->query('department_id');

        $query = \App\Models\User::role(['teacher','class-teacher'])
            ->with('assignedCourses.course','assignedCourses.section','departments');

        if ($search) {
            $query->where(fn($q) => $q
                ->where('first_name','like',"%$search%")
                ->orWhere('last_name','like',"%$search%")
                ->orWhere('email','like',"%$search%")
            );
        }

        if ($deptId) {
            $query->whereHas('departments', fn($q) => $q->where('departments.id', $deptId));
        }

        $teachers = $query->get();
        return view('teachers.list', compact('teachers'));
    }
}
