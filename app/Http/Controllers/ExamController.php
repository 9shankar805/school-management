<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExamStoreRequest;
use App\Models\Exam;
use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Interfaces\SemesterInterface;
use App\Interfaces\SchoolClassInterface;
use App\Interfaces\SchoolSessionInterface;
use App\Repositories\AssignedTeacherRepository;
use App\Repositories\ExamRepository;

class ExamController extends Controller
{
    use SchoolSession;

    protected $schoolClassRepository;
    protected $semesterRepository;
    protected $schoolSessionRepository;

    public function __construct(SchoolSessionInterface $schoolSessionRepository, SchoolClassInterface $schoolClassRepository, SemesterInterface $semesterRepository)
    {
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolClassRepository = $schoolClassRepository;
        $this->semesterRepository = $semesterRepository;
    }
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $class_id = $request->query('class_id', 0);
            $semester_id = $request->query('semester_id', 0);

            $current_school_session_id = $this->getSchoolCurrentSession();

            $semesters = $this->semesterRepository->getAll($current_school_session_id);

            $school_classes = $this->schoolClassRepository->getAllBySession($current_school_session_id);

            $examRepository = new ExamRepository();

            $exams = $examRepository->getAll($current_school_session_id, $semester_id, $class_id);

            $assignedTeacherRepository = new AssignedTeacherRepository();

            $teacher_id = (auth()->user()->role == "teacher")?auth()->user()->id : 0;

            $teacherCourses = $assignedTeacherRepository->getTeacherCourses($current_school_session_id, $teacher_id, $semester_id);

            $data = [
                'current_school_session_id' => $current_school_session_id,
                'semesters'                 => $semesters,
                'classes'                   => $school_classes,
                'exams'                     => $exams,
                'teacher_courses'           => $teacherCourses,
            ];

            return view('exams.index', $data);
        } catch (\Exception $e) {
            return view('exams.index', [
                'current_school_session_id' => 0,
                'semesters' => collect(),
                'classes' => collect(),
                'exams' => collect(),
                'teacher_courses' => collect(),
                'error' => 'Unable to load exam data: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        try {
            $current_school_session_id = $this->getSchoolCurrentSession();

            $semesters = $this->semesterRepository->getAll($current_school_session_id);

            if(auth()->user()->role == "teacher") {
                $teacher_id = auth()->user()->id;
                $assigned_classes = $this->schoolClassRepository->getAllBySessionAndTeacher($current_school_session_id, $teacher_id);

                $school_classes = [];
                $i = 0;

                foreach($assigned_classes as $assigned_class) {
                    $school_classes[$i] = $assigned_class->schoolClass;
                    $i++;
                }
            } else {
                $school_classes = $this->schoolClassRepository->getAllBySession($current_school_session_id);
            }

            $data = [
                'current_school_session_id' => $current_school_session_id,
                'semesters'                 => $semesters,
                'classes'                   => $school_classes,
            ];

            return view('exams.create', $data);
        } catch (\Exception $e) {
            return view('exams.create', [
                'current_school_session_id' => 0,
                'semesters' => collect(),
                'classes' => collect(),
                'error' => 'Unable to load exam creation form: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  ExamStoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ExamStoreRequest $request)
    {
        try {
            $examRepository = new ExamRepository();
            $data = $request->validated();
            // Ensure school_id is stamped (HasTenant trait may not set it on
            // repositories that call Exam::create() directly with only validated fields)
            $data['school_id'] = auth()->user()->school_id ?? null;
            $examRepository->create($data);

            return back()->with('status', 'Exam creation was successful!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Exam $exam)
    {
        $this->authorize('view exams');

        return view('exams.show', ['exam' => $exam]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Exam $exam)
    {
        $this->authorize('edit exams');

        try {
            $current_school_session_id = $this->getSchoolCurrentSession();
            $semesters   = $this->semesterRepository->getAll($current_school_session_id);
            $school_classes = $this->schoolClassRepository->getAllBySession($current_school_session_id);

            return view('exams.edit', [
                'exam'                       => $exam,
                'current_school_session_id'  => $current_school_session_id,
                'semesters'                  => $semesters,
                'classes'                    => $school_classes,
            ]);
        } catch (\Exception $e) {
            return redirect()->route('exam.list.show')->with('error', 'Unable to load exam edit form: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Exam $exam)
    {
        $this->authorize('edit exams');

        $validated = $request->validate([
            'exam_name'   => 'required|string',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after:start_date',
            'semester_id' => 'required|integer|gt:0',
            'class_id'    => 'required|integer|gt:0',
            'course_id'   => 'required|integer|gt:0',
        ]);

        try {
            $exam->update($validated);
            return redirect()->route('exam.list.show')->with('status', 'Exam updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update exam: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $this->authorize('delete exams');

        try {
            $examRepository = new ExamRepository();
            $examRepository->delete($request->exam_id);

            return back()->with('status', 'Exam deletion was successful!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
