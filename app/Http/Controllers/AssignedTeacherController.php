<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Interfaces\SemesterInterface;
use App\Interfaces\SchoolSessionInterface;
use App\Http\Requests\TeacherAssignRequest;
use App\Repositories\AssignedTeacherRepository;

class AssignedTeacherController extends Controller
{
    use SchoolSession;
    protected $schoolSessionRepository;
    protected $semesterRepository;

    /**
    * Create a new Controller instance
    * 
    * @param SchoolSessionInterface $schoolSessionRepository
    * @return void
    */
    public function __construct(SchoolSessionInterface $schoolSessionRepository,
    SemesterInterface $semesterRepository) {
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->semesterRepository = $semesterRepository;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Display a listing of the resource.
     *
     * @param  CourseStoreRequest $request
     * @return \Illuminate\Http\Response
     */
    public function getTeacherCourses(Request $request)
    {
        $teacher_id = $request->query('teacher_id');
        $semester_id = $request->query('semester_id');

        if($teacher_id == null) {
            abort(404);
        }
        
        $current_school_session_id = $this->getSchoolCurrentSession();

        $semesters = $this->semesterRepository->getAll($current_school_session_id);

        $assignedTeacherRepository = new AssignedTeacherRepository();

        if($semester_id == null) {
            $courses = [];
        } else {
            $courses = $assignedTeacherRepository->getTeacherCourses($current_school_session_id, $teacher_id, $semester_id);
        }
        
        $data = [
            'courses'               => $courses,
            'semesters'             => $semesters,
            'selected_semester_id'  => $semester_id,
        ];

        return view('courses.teacher', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  TeacherAssignRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(TeacherAssignRequest $request)
    {
        try {
            $validated = $request->validated();
            $isClassTeacher = !empty($request->is_class_teacher);

            if ($isClassTeacher) {
                // Remove Class Teacher status from previous assignments for this section
                \App\Models\AssignedTeacher::where('session_id', $validated['session_id'])
                    ->where('class_id', $validated['class_id'])
                    ->where('section_id', $validated['section_id'])
                    ->update(['is_class_teacher' => false]);
            }

            $validated['is_class_teacher'] = $isClassTeacher;

            $assignedTeacherRepository = new AssignedTeacherRepository();
            $assignedTeacherRepository->assign($validated);

            $msg = $isClassTeacher 
                ? 'Teacher assigned as Class Teacher successfully!' 
                : 'Teacher subject assignment saved successfully!';

            return back()->with('status', $msg);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Toggle Class Teacher designation for an assignment.
     */
    public function toggleClassTeacher($id)
    {
        try {
            $assignment = \App\Models\AssignedTeacher::findOrFail($id);
            $newStatus = !$assignment->is_class_teacher;

            if ($newStatus) {
                // Clear existing Class Teacher for this class section
                \App\Models\AssignedTeacher::where('session_id', $assignment->session_id)
                    ->where('class_id', $assignment->class_id)
                    ->where('section_id', $assignment->section_id)
                    ->update(['is_class_teacher' => false]);
            }

            $assignment->is_class_teacher = $newStatus;
            $assignment->save();

            $msg = $newStatus
                ? 'Teacher designated as Class Teacher!'
                : 'Class Teacher designation removed.';

            return back()->with('status', $msg);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}

