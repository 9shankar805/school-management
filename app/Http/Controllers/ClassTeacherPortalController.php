<?php

namespace App\Http\Controllers;

use App\Models\AssignedTeacher;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\Exam;
use App\Models\LeaveApplication;
use App\Models\Mark;
use App\Models\Notice;
use App\Models\Promotion;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use App\Traits\SchoolSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClassTeacherPortalController extends Controller
{
    use SchoolSession;

    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * Get the active class teacher assignment for the authenticated user.
     */
    protected function getInChargeAssignment()
    {
        $sessionId = $this->getSchoolCurrentSession();
        $teacherId = auth()->id();

        // If super-admin/admin/principal, allow viewing first class or specified section
        if (auth()->user()->hasRole(['super-admin', 'admin', 'principal'])) {
            $classId = request('class_id');
            $sectionId = request('section_id');

            if ($classId && $sectionId) {
                return (object) [
                    'class_id'    => (int) $classId,
                    'section_id'  => (int) $sectionId,
                    'schoolClass' => SchoolClass::find($classId),
                    'section'     => Section::find($sectionId),
                ];
            }

            $firstAssignment = AssignedTeacher::with(['schoolClass', 'section'])
                ->where('session_id', $sessionId)
                ->where('is_class_teacher', true)
                ->first();

            if ($firstAssignment) {
                return $firstAssignment;
            }

            $firstClass = SchoolClass::where('session_id', $sessionId)->first();
            $firstSection = $firstClass ? Section::where('class_id', $firstClass->id)->first() : null;

            return (object) [
                'class_id'    => $firstClass?->id,
                'section_id'  => $firstSection?->id,
                'schoolClass' => $firstClass,
                'section'     => $firstSection,
            ];
        }

        return AssignedTeacher::with(['schoolClass', 'section'])
            ->where('teacher_id', $teacherId)
            ->where('session_id', $sessionId)
            ->where('is_class_teacher', true)
            ->first();
    }

    /**
     * GET /class-incharge — Class In-charge Command Center
     */
    public function index(Request $request)
    {
        $sessionId = $this->getSchoolCurrentSession();
        $assignment = $this->getInChargeAssignment();

        if (!$assignment || !$assignment->class_id || !$assignment->section_id) {
            return view('class-teacher.unassigned');
        }

        $classId   = $assignment->class_id;
        $sectionId = $assignment->section_id;
        $today     = now()->toDateString();

        // 1. Enrolled Students
        $students = Promotion::with(['student.academic_info'])
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->where('session_id', $sessionId)
            ->orderBy('roll_no')
            ->get();

        $studentUserIds = $students->pluck('student_id')->filter()->all();

        // 2. Today's Attendance Overview
        $todayAttendances = Attendance::where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->whereDate('date', $today)
            ->get();

        $presentCount = $todayAttendances->where('status', 'on')->count();
        $absentCount  = $todayAttendances->where('status', 'off')->count();
        $attendanceTaken = $todayAttendances->isNotEmpty();

        // 3. Subject Teachers roster for this section
        $subjectTeachers = AssignedTeacher::with(['teacher', 'course'])
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->where('session_id', $sessionId)
            ->get();

        // 4. Pending Student Leaves in this section
        $pendingLeaves = LeaveApplication::whereIn('user_id', $studentUserIds)
            ->where('status', 'pending')
            ->with(['user', 'leaveType'])
            ->latest()
            ->get();

        // 5. Recent Notices / Circulars
        $classNotices = Notice::where(function ($q) {
                $q->where('audience', 'all')
                  ->orWhere('audience', 'students')
                  ->orWhere('audience', 'parents')
                  ->orWhereNull('audience');
            })
            ->where('session_id', $sessionId)
            ->latest()
            ->take(5)
            ->get();

        return view('class-teacher.dashboard', compact(
            'assignment', 'students', 'attendanceTaken',
            'presentCount', 'absentCount', 'subjectTeachers',
            'pendingLeaves', 'classNotices'
        ));
    }

    /**
     * GET /class-incharge/students — Full Student & Parent Dossier
     */
    public function students(Request $request)
    {
        $sessionId = $this->getSchoolCurrentSession();
        $assignment = $this->getInChargeAssignment();

        if (!$assignment) {
            return redirect()->route('class-incharge.dashboard');
        }

        $students = Promotion::with([
                'student.academic_info',
                'student.parent',
            ])
            ->where('class_id', $assignment->class_id)
            ->where('section_id', $assignment->section_id)
            ->where('session_id', $sessionId)
            ->orderBy('roll_no')
            ->get();

        return view('class-teacher.students', compact('assignment', 'students'));
    }

    /**
     * GET /class-incharge/gradebook — Consolidated Cross-Subject Marksheet & Remarks
     */
    public function gradebook(Request $request)
    {
        $sessionId = $this->getSchoolCurrentSession();
        $assignment = $this->getInChargeAssignment();

        if (!$assignment) {
            return redirect()->route('class-incharge.dashboard');
        }

        $classId   = $assignment->class_id;
        $sectionId = $assignment->section_id;

        $exams   = Exam::where('session_id', $sessionId)->orderByDesc('id')->get();
        $examId  = $request->query('exam_id', $exams->first()?->id);
        $selectedExam = $exams->firstWhere('id', $examId) ?? $exams->first();
        $courses = Course::where('class_id', $classId)->orWhere('session_id', $sessionId)->get();

        $students = Promotion::with('student')
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->where('session_id', $sessionId)
            ->orderBy('roll_no')
            ->get();

        $marks = [];
        if ($examId) {
            $marks = Mark::where('exam_id', $examId)
                ->where('class_id', $classId)
                ->where('section_id', $sectionId)
                ->where('session_id', $sessionId)
                ->get()
                ->groupBy('student_id');
        }

        return view('class-teacher.gradebook', compact(
            'assignment', 'exams', 'examId', 'selectedExam', 'courses', 'students', 'marks'
        ));
    }

    /**
     * POST /class-incharge/leaves/{id}/action — Approve/Reject Student Leave
     */
    public function handleLeave(Request $request, int $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'remarks'=> 'nullable|string|max:255',
        ]);

        $leave = LeaveApplication::findOrFail($id);
        $leave->status      = $request->status;
        $leave->reviewed_by = auth()->id();
        $leave->reviewed_at = now();
        $leave->rejection_reason = $request->remarks;
        $leave->save();

        return back()->with('status', 'Student leave request has been ' . $request->status . ' successfully.');
    }

    /**
     * POST /class-incharge/diary — Post section notice / diary entry
     */
    public function postDiary(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'notice_date' => 'nullable|date',
        ]);

        $assignment = $this->getInChargeAssignment();
        if (!$assignment) {
            return back()->with('error', 'You are not assigned as a Class Teacher.');
        }

        Notice::create([
            'title'        => $request->title,
            'notice'       => $request->description,
            'priority'     => 'normal',
            'audience'     => 'parents',
            'session_id'   => $sessionId,
            'published_by' => auth()->id(),
            'school_id'    => config('tenant.school')?->id ?? auth()->user()->school_id,
        ]);

        return back()->with('status', 'Daily diary / announcement published to parents successfully.');
    }
}
