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
            $class_id = (int) $request->query('class_id', 0);
            $semester_id = (int) $request->query('semester_id', 0);

            $current_school_session_id = $this->getSchoolCurrentSession();

            $semesters = $this->semesterRepository->getAll($current_school_session_id);
            $school_classes = $this->schoolClassRepository->getAllBySession($current_school_session_id);

            $examRepository = new ExamRepository();
            $exams = $examRepository->getAll($current_school_session_id, $semester_id, $class_id);

            $user = auth()->user();
            $isRestrictedTeacher = $user && $user->hasAnyRole(['teacher', 'class-teacher'])
                                 && !$user->hasAnyRole(['super-admin', 'admin', 'principal', 'vice-principal', 'academic-coordinator', 'exam-controller']);

            if ($isRestrictedTeacher) {
                $assignedTeacherRepository = new AssignedTeacherRepository();
                $teacherCourses = $assignedTeacherRepository->getTeacherCourses($current_school_session_id, $user->id, $semester_id);
                $courseIds = $teacherCourses->pluck('course_id')->filter()->unique()->toArray();
                $exams = $exams->filter(function($exam) use ($courseIds) {
                    return in_array($exam->course_id, $courseIds);
                })->values();
            }

            $data = [
                'current_school_session_id' => $current_school_session_id,
                'semesters'                 => $semesters,
                'classes'                   => $school_classes,
                'exams'                     => $exams,
                'class_id'                  => $class_id,
                'semester_id'               => $semester_id,
            ];

            return view('exams.index', $data);
        } catch (\Exception $e) {
            return view('exams.index', [
                'current_school_session_id' => 0,
                'semesters' => collect(),
                'classes' => collect(),
                'exams' => collect(),
                'class_id' => 0,
                'semester_id' => 0,
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

            $user = auth()->user();
            $isRestrictedTeacher = $user && $user->hasAnyRole(['teacher', 'class-teacher'])
                                 && !$user->hasAnyRole(['super-admin', 'admin', 'principal', 'vice-principal', 'academic-coordinator', 'exam-controller']);

            if ($isRestrictedTeacher) {
                $teacher_id = $user->id;
                $assigned_classes = $this->schoolClassRepository->getAllBySessionAndTeacher($current_school_session_id, $teacher_id);
                $school_classes = $assigned_classes->pluck('schoolClass')->filter()->unique('id')->values();
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

            return redirect()->route('exam.list.show')->with('status', 'Exam creation was successful!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Bulk create exams — one exam per subject for a whole class in one click.
     */
    public function bulkStore(Request $request)
    {
        $this->authorize('create exams');

        $data = $request->validate([
            'exam_name'                => 'required|string|max:255',
            'class_id'                 => 'required|exists:school_classes,id',
            'semester_id'              => 'required|exists:semesters,id',
            'start_date'               => 'required|date',
            'end_date'                 => 'required|date|after_or_equal:start_date',
            'course_ids'               => 'required|array|min:1',
            'course_ids.*'             => 'exists:courses,id',
            'total_marks'              => 'nullable|numeric|min:1|max:1000',
            'pass_marks'               => 'nullable|numeric|min:0',
            'marks_distribution_note'  => 'nullable|string|max:500',
        ]);

        try {
            $sessionId = $this->getSchoolCurrentSession();
            $schoolId  = auth()->user()->school_id;
            $created   = 0;
            $skipped   = 0;

            $totalMarks   = $data['total_marks'] ?? null;
            $passMarks    = $data['pass_marks']  ?? null;
            $distribution = $data['marks_distribution_note'] ?? null;

            foreach ($data['course_ids'] as $courseId) {
                $exists = \App\Models\Exam::where('exam_name',   $data['exam_name'])
                    ->where('class_id',    $data['class_id'])
                    ->where('course_id',   $courseId)
                    ->where('semester_id', $data['semester_id'])
                    ->exists();

                if ($exists) { $skipped++; continue; }

                $exam = \App\Models\Exam::create([
                    'exam_name'   => $data['exam_name'],
                    'class_id'    => $data['class_id'],
                    'course_id'   => $courseId,
                    'semester_id' => $data['semester_id'],
                    'session_id'  => $sessionId,
                    'school_id'   => $schoolId,
                    'start_date'  => $data['start_date'],
                    'end_date'    => $data['end_date'],
                ]);

                // Auto-create exam rule if marks provided
                if ($totalMarks && $passMarks !== null) {
                    \App\Models\ExamRule::create([
                        'exam_id'                  => $exam->id,
                        'session_id'               => $sessionId,
                        'school_id'                => $schoolId,
                        'total_marks'              => $totalMarks,
                        'pass_marks'               => $passMarks,
                        'marks_distribution_note'  => $distribution,
                    ]);
                }

                $created++;
            }

            $msg = "✅ {$created} exam(s) created successfully!";
            if ($skipped > 0) $msg .= " ({$skipped} already existed, skipped.)";
            return redirect()->route('exam.list.show')->with('status', $msg);
        } catch (\Exception $e) {
            return back()->with('error', 'Bulk create failed: ' . $e->getMessage())->withInput();
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
