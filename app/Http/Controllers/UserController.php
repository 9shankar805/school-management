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
        $this->middleware(['can:view users']);

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
        try {
            $this->userRepository->createTeacher($request->validated());

            return back()->with('status', 'Teacher creation was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function getStudentList(Request $request) {
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
            return back()->withError($e->getMessage());
        }
    }


    public function showStudentProfile($id) {
        $student = $this->userRepository->findStudent($id);

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
        ]);

        // Gather attendance stats for the timeline
        $attendanceStats = [
            'total'   => \App\Models\Attendance::where('student_id', $id)->count(),
            'present' => \App\Models\Attendance::where('student_id', $id)->where('status', 'present')->count(),
        ];
        $attendanceStats['pct'] = $attendanceStats['total'] > 0
            ? round($attendanceStats['present'] / $attendanceStats['total'] * 100)
            : 0;

        // House assignment for current session
        $currentHouse = $student->houseAssignments
            ->where('session_id', $current_school_session_id)
            ->first();

        $data = [
            'student'           => $student,
            'promotion_info'    => $promotion_info,
            'attendanceStats'   => $attendanceStats,
            'currentHouse'      => $currentHouse,
        ];

        return view('students.profile', $data);
    }

    public function showTeacherProfile($id) {
        $teacher = $this->userRepository->findTeacher($id);

        $teacher->load([
            'assignedCourses.course','assignedCourses.section',
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

        return view('teachers.profile', compact('teacher','activeContract','attendanceStats'));
    }


    public function createStudent() {
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
        try {
            $this->userRepository->createStudent($request->validated());

            return back()->with('status', 'Student creation was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function editStudent($student_id) {
        $student = $this->userRepository->findStudent($student_id);
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
        try {
            $this->userRepository->updateStudent($request->toArray());

            return back()->with('status', 'Student update was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function editTeacher($teacher_id) {
        $teacher = $this->userRepository->findTeacher($teacher_id);

        $data = [
            'teacher'   => $teacher,
        ];

        return view('teachers.edit', $data);
    }
    public function updateTeacher(Request $request) {
        try {
            $this->userRepository->updateTeacher($request->toArray());

            return back()->with('status', 'Teacher update was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function getTeacherList(){
        $teachers = $this->userRepository->getAllTeachers();

        $data = [
            'teachers' => $teachers,
        ];

        return view('teachers.list', $data);
    }
}
