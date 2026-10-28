<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Mark;
use App\Models\Exam;
use App\Models\ExamRule;
use App\Models\Course;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\SchoolSession;
use App\Interfaces\UserInterface;
use App\Interfaces\CourseInterface;
use App\Interfaces\SectionInterface;
use App\Repositories\ExamRepository;
use App\Repositories\MarkRepository;
use App\Traits\AssignedTeacherCheck;
use App\Interfaces\SemesterInterface;
use App\Interfaces\SchoolClassInterface;
use App\Repositories\GradeRuleRepository;
use App\Interfaces\SchoolSessionInterface;
use App\Interfaces\AcademicSettingInterface;
use App\Repositories\GradingSystemRepository;

class MarkController extends Controller
{
    use SchoolSession, AssignedTeacherCheck;

    protected $academicSettingRepository;
    protected $userRepository;
    protected $schoolClassRepository;
    protected $schoolSectionRepository;
    protected $courseRepository;
    protected $semesterRepository;
    protected $schoolSessionRepository;

    public function __construct(
        AcademicSettingInterface $academicSettingRepository,
        UserInterface $userRepository,
        SchoolSessionInterface $schoolSessionRepository,
        SchoolClassInterface $schoolClassRepository,
        SectionInterface $schoolSectionRepository,
        CourseInterface $courseRepository,
        SemesterInterface $semesterRepository
    ) {
        $this->academicSettingRepository = $academicSettingRepository;
        $this->userRepository = $userRepository;
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolClassRepository = $schoolClassRepository;
        $this->schoolSectionRepository = $schoolSectionRepository;
        $this->courseRepository = $courseRepository;
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
            $section_id = $request->query('section_id', 0);
            $course_id = $request->query('course_id', 0);
            $semester_id = $request->query('semester_id', 0);

            $current_school_session_id = $this->getSchoolCurrentSession();

            $semesters = $this->semesterRepository->getAll($current_school_session_id);
            $school_classes = $this->schoolClassRepository->getAllBySession($current_school_session_id);

            $markRepository = new MarkRepository();
            $marks = $markRepository->getAllFinalMarks($current_school_session_id, $semester_id, $class_id, $section_id, $course_id);

            if(!$marks) {
                $marks = collect();
            }

            $gradingSystemRepository = new GradingSystemRepository();
            $gradingSystem = $gradingSystemRepository->getGradingSystem($current_school_session_id, $semester_id, $class_id);

            $gradingSystemRules = collect();
            if($gradingSystem) {
                $gradeRulesRepository = new GradeRuleRepository();
                $gradingSystemRules = $gradeRulesRepository->getAll($current_school_session_id, $gradingSystem->id) ?? collect();
            }

            foreach($marks as $mark_key => $mark) {
                foreach ($gradingSystemRules as $key => $gradingSystemRule) {
                    if($mark->final_marks >= $gradingSystemRule->start_at && $mark->final_marks <= $gradingSystemRule->end_at) {
                        $marks[$mark_key]['point'] = $gradingSystemRule->point;
                        $marks[$mark_key]['grade'] = $gradingSystemRule->grade;
                    }
                }
            }

            $data = [
                'current_school_session_id' => $current_school_session_id,
                'semesters'                 => $semesters,
                'classes'                   => $school_classes,
                'marks'                     => $marks,
                'grading_system_rules'      => $gradingSystemRules,
            ];

            return view('marks.results', $data);
        } catch (\Exception $e) {
            return view('marks.results', [
                'current_school_session_id' => 0,
                'semesters' => collect(),
                'classes' => collect(),
                'marks' => collect(),
                'grading_system_rules' => collect(),
                'error' => 'Unable to load marks: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        try {
            $class_id = $request->query('class_id');
            $section_id = (int) $request->query('section_id', 0);
            $course_id = $request->query('course_id', 0);
            $semester_id = (int) $request->query('semester_id', 0);
            $entry_mode = $request->query('entry_mode');

            $current_school_session_id = $this->getSchoolCurrentSession();
            
            if (!$current_school_session_id) {
                return view('marks.create', [
                    'academic_setting'          => null,
                    'semesters'                 => collect(),
                    'school_classes'            => collect(),
                    'exams'                     => collect(),
                    'students_with_marks'       => collect(),
                    'class_id'                  => null,
                    'section_id'                => 0,
                    'course_id'                 => 0,
                    'semester_id'               => 0,
                    'final_marks_submitted'     => false,
                    'sectionStudents'           => collect(),
                    'exam_rules_by_exam_id'     => collect(),
                    'current_school_session_id' => 0,
                    'isAllSubjectsMode'         => true,
                    'classCourses'              => collect(),
                    'classSections'             => collect(),
                    'marksMatrix'               => [],
                    'courseMaxMarks'            => [],
                    'selectedClass'             => null,
                    'selectedSection'           => null,
                    'selectedSemester'          => null,
                    'selectedCourse'            => null,
                    'error'                     => 'Unable to determine current school session. Please configure a session first.'
                ]);
            }

            $user = auth()->user();
            $isAdmin = $this->isMarksManagementUser($user);

            $schoolId = $user->school_id ?? null;
            $cacheBase = "marks_create_{$schoolId}_{$current_school_session_id}";

            // ── Cached lookups (5 min TTL) ────────────────────────────
            $academic_setting = \Illuminate\Support\Facades\Cache::remember("{$cacheBase}_acset", 300, fn() =>
                $this->academicSettingRepository ? $this->academicSettingRepository->getAcademicSetting() : null
            );

            $semesters = \Illuminate\Support\Facades\Cache::remember("{$cacheBase}_sems", 300, fn() =>
                $this->semesterRepository ? $this->semesterRepository->getAll($current_school_session_id) : collect()
            );

            // Classes list: Admins see all; teachers see only assigned classes
            if ($isAdmin) {
                $school_classes = \Illuminate\Support\Facades\Cache::remember("{$cacheBase}_classes", 300, fn() =>
                    $this->schoolClassRepository ? $this->schoolClassRepository->getAllWithCoursesBySession($current_school_session_id) : collect()
                );
            } else {
                $assignedClassIds = \App\Models\AssignedTeacher::where('teacher_id', $user->id)
                    ->where(function($q) use ($current_school_session_id) {
                        $q->where('session_id', $current_school_session_id)
                          ->orWhereNull('session_id')
                          ->orWhere('session_id', 0);
                    })
                    ->pluck('class_id')
                    ->unique()
                    ->filter()
                    ->toArray();
                $school_classes = \App\Models\SchoolClass::whereIn('id', $assignedClassIds)->orderBy('class_name')->get();
            }

            // Resolve selected semester
            $selectedSemester = $semester_id > 0 ? Semester::find($semester_id) : ($semesters->first() ?? null);
            if ($selectedSemester && $semester_id == 0) {
                $semester_id = $selectedSemester->id;
            }

            // Determine Class Teacher vs Subject Teacher vs Administrator authority
            $isClassTeacher = $class_id ? $this->isAssignedClassTeacher($user, $current_school_session_id, (int)$class_id, (int)$section_id) : false;
            $canEditAllSubjects = $isAdmin || $isClassTeacher;

            $teacherAssignedCourseIds = [];
            if (!$canEditAllSubjects) {
                // Regular Subject Teacher: restricted strictly to their assigned subjects
                $isAllSubjectsMode = false;

                if ($class_id) {
                    $teacherAssignedCourseIds = \App\Models\AssignedTeacher::where('teacher_id', $user->id)
                        ->where('class_id', $class_id)
                        ->where(function($q) use ($current_school_session_id) {
                            $q->where('session_id', $current_school_session_id)
                              ->orWhereNull('session_id')
                              ->orWhere('session_id', 0);
                        })
                        ->when($section_id > 0, function($q) use ($section_id) {
                            $q->where(function($sq) use ($section_id) {
                                $sq->where('section_id', (int)$section_id)
                                  ->orWhere('section_id', 0)
                                  ->orWhereNull('section_id');
                            });
                        })
                        ->pluck('course_id')
                        ->unique()
                        ->filter()
                        ->toArray();

                    if (!empty($teacherAssignedCourseIds)) {
                        $courseNames = Course::whereIn('id', $teacherAssignedCourseIds)->pluck('course_name')->unique()->filter()->all();
                        if (!empty($courseNames)) {
                            $allSameNameCourseIds = Course::where('class_id', $class_id)
                                ->whereIn('course_name', $courseNames)
                                ->pluck('id')
                                ->all();
                            $teacherAssignedCourseIds = array_values(array_unique(array_merge($teacherAssignedCourseIds, $allSameNameCourseIds)));
                        }
                    }

                    if (empty($teacherAssignedCourseIds)) {
                        $hasGeneralClassAssignment = \App\Models\AssignedTeacher::where('teacher_id', $user->id)
                            ->where('class_id', $class_id)
                            ->where(function($q) use ($current_school_session_id) {
                                $q->where('session_id', $current_school_session_id)
                                  ->orWhereNull('session_id')
                                  ->orWhere('session_id', 0);
                            })
                            ->exists();

                        if ($hasGeneralClassAssignment) {
                            $teacherAssignedCourseIds = Course::where('class_id', $class_id)->pluck('id')->toArray();
                        }
                    }

                    if (empty($teacherAssignedCourseIds)) {
                        return redirect()->route('course.mark.create')->with('error', 'Access Restricted: You are not assigned to teach any subjects in the selected class and section.');
                    }

                    // If teacher requested 'all', '0', or an unassigned subject, auto-select their first assigned subject
                    if (empty($course_id) || $course_id === 'all' || (int)$course_id === 0 || !in_array((int)$course_id, $teacherAssignedCourseIds)) {
                        $course_id = (int) reset($teacherAssignedCourseIds);
                    }
                }
            } else {
                // Class Teacher or Administrator: can toggle between master grade ledger or single subject
                $isAllSubjectsMode = empty($course_id) || $course_id === 'all' || (int)$course_id === 0 || $entry_mode === 'all_subjects';
            }

            // Resolve human-readable models
            $selectedClass = $class_id ? SchoolClass::find($class_id) : null;
            $selectedSection = $section_id > 0 ? Section::find($section_id) : null;
            $selectedCourse = ($course_id && $course_id !== 'all' && (int)$course_id > 0) ? Course::find($course_id) : null;

            if (!$class_id) {
                return view('marks.create', [
                    'academic_setting'          => $academic_setting,
                    'semesters'                 => $semesters,
                    'school_classes'            => $school_classes,
                    'exams'                     => collect(),
                    'students_with_marks'       => collect(),
                    'marksByStudentAndExam'     => [],
                    'class_id'                  => null,
                    'section_id'                => 0,
                    'course_id'                 => 0,
                    'semester_id'               => $semester_id,
                    'final_marks_submitted'     => false,
                    'sectionStudents'           => collect(),
                    'exam_rules_by_exam_id'     => collect(),
                    'current_school_session_id' => $current_school_session_id,
                    'isAllSubjectsMode'         => $canEditAllSubjects,
                    'classCourses'              => collect(),
                    'classSections'             => collect(),
                    'marksMatrix'               => [],
                    'courseMaxMarks'            => [],
                    'selectedClass'             => null,
                    'selectedSection'           => null,
                    'selectedSemester'          => $selectedSemester,
                    'selectedCourse'            => null,
                    'isAdmin'                   => $isAdmin,
                    'isClassTeacher'            => $isClassTeacher,
                    'canEditAllSubjects'        => $canEditAllSubjects,
                ]);
            }

            // Fetch students for this class and section
            $sectionStudents = $this->userRepository ? $this->userRepository->getAllStudents($current_school_session_id, (int)$class_id, (int)$section_id) : collect();

            // Fetch courses: For regular subject teachers, ONLY their assigned courses! For Admins and Class Teachers, ALL courses in class
            if (!$canEditAllSubjects) {
                $classCourses = Course::where('class_id', $class_id)
                    ->whereIn('id', $teacherAssignedCourseIds)
                    ->orderBy('course_name')
                    ->get();
            } else {
                // Admin/ClassTeacher: deduplicated courses for this class + semester
                $classCourses = Course::uniqueByName()
                    ->where('class_id', $class_id)
                    ->when($semester_id > 0, fn($q) => $q->where('semester_id', $semester_id))
                    ->where(function($q) use ($current_school_session_id) {
                        $q->where('session_id', $current_school_session_id)->orWhereNull('session_id');
                    })
                    ->orderBy('course_name')
                    ->get();
                if ($classCourses->isEmpty()) {
                    $classCourses = Course::uniqueByName()->where('class_id', $class_id)->orderBy('course_name')->get();
                }
            }

            // Fetch sections for this class
            if ($isAdmin || $isClassTeacher) {
                $classSections = Section::where('class_id', $class_id)->orderBy('section_name')->get();
            } else {
                $assignedSectionIds = \App\Models\AssignedTeacher::where('teacher_id', $user->id)
                    ->where('class_id', $class_id)
                    ->where(function($q) use ($current_school_session_id) {
                        $q->where('session_id', $current_school_session_id)
                          ->orWhereNull('session_id')
                          ->orWhere('session_id', 0);
                    })
                    ->pluck('section_id')
                    ->unique()
                    ->filter()
                    ->all();
                if (empty($assignedSectionIds) || in_array(0, $assignedSectionIds)) {
                    $classSections = Section::where('class_id', $class_id)->orderBy('section_name')->get();
                } else {
                    $classSections = Section::where('class_id', $class_id)->whereIn('id', $assignedSectionIds)->orderBy('section_name')->get();
                }
            }

            if ($isAllSubjectsMode) {
                // Check authorization
                if (!$isAdmin) {
                    $this->checkIfLoggedInUserIsAssignedTeacher($request, $current_school_session_id);
                }
                // Single query for exams
                $exams = Exam::where('session_id', $current_school_session_id)
                    ->when($semester_id > 0, fn($q) => $q->where('semester_id', $semester_id))
                    ->where('class_id', $class_id)
                    ->get();
                $examIds = $exams->pluck('id');
                // Single query for exam rules
                $examRulesByExamId = ExamRule::whereIn('exam_id', $examIds)
                    ->pluck('total_marks', 'exam_id');
                $examsByCourse = $exams->keyBy('course_id');
                $courseMaxMarks = [];
                foreach ($classCourses as $crs) {
                    $ex = $examsByCourse->get($crs->id);
                    $courseMaxMarks[$crs->id] = ($ex && isset($examRulesByExamId[$ex->id]))
                        ? (float)$examRulesByExamId[$ex->id] : 100;
                }
                // Single optimized marks query (only needed columns)
                $studentIds = $sectionStudents->map(fn($item) => $item->student_id ?? optional($item->student)->id)->filter()->unique()->values()->toArray();
                $existingMarks = Mark::where('session_id', $current_school_session_id)
                    ->where('class_id', $class_id)
                    ->whereIn('student_id', $studentIds)
                    ->whereIn('course_id', $classCourses->pluck('id'))
                    ->whereIn('exam_id', $examIds)
                    ->orderByDesc('updated_at')
                    ->get(['student_id', 'course_id', 'marks']);
                $marksMatrix = [];
                foreach ($existingMarks as $m) {
                    if (!isset($marksMatrix[$m->student_id][$m->course_id])) {
                        $marksMatrix[$m->student_id][$m->course_id] = $m->marks;
                    }
                }
                return view('marks.create', [
                    'academic_setting'          => $academic_setting,
                    'semesters'                 => $semesters,
                    'school_classes'            => $school_classes,
                    'exams'                     => $exams,
                    'students_with_marks'       => collect(),
                    'marksByStudentAndExam'     => [],
                    'class_id'                  => $class_id,
                    'section_id'                => $section_id,
                    'course_id'                 => 'all',
                    'semester_id'               => $semester_id,
                    'final_marks_submitted'     => false,
                    'sectionStudents'           => $sectionStudents,
                    'exam_rules_by_exam_id'     => $examRulesByExamId,
                    'current_school_session_id' => $current_school_session_id,
                    'isAllSubjectsMode'         => true,
                    'classCourses'              => $classCourses,
                    'classSections'             => $classSections,
                    'marksMatrix'               => $marksMatrix,
                    'courseMaxMarks'            => $courseMaxMarks,
                    'selectedClass'             => $selectedClass,
                    'selectedSection'           => $selectedSection,
                    'selectedSemester'          => $selectedSemester,
                    'selectedCourse'            => null,
                    'isAdmin'                   => $isAdmin,
                    'isClassTeacher'            => $isClassTeacher,
                    'canEditAllSubjects'        => $canEditAllSubjects,
                ]);
            } else {
                // Single Subject Entry Mode
                if (!$isAdmin) {
                    $this->checkIfLoggedInUserIsAssignedTeacher($request, $current_school_session_id);
                }

                $examRepository = new ExamRepository();
                $exams = $examRepository->getAll($current_school_session_id, $semester_id, $class_id, (int)$course_id);

                // If no exams found specifically for this course, check for class-level exams
                if ($exams->isEmpty()) {
                    $exams = Exam::where('session_id', $current_school_session_id)
                        ->where('semester_id', $semester_id)
                        ->where('class_id', $class_id)
                        ->where(function($q) use ($course_id) {
                            $q->where('course_id', (int)$course_id)
                              ->orWhere('course_id', 0)
                              ->orWhereNull('course_id');
                        })
                        ->get();
                }

                // Auto-create exam if still missing so teachers are never blocked
                if ($exams->isEmpty()) {
                    $semName = $selectedSemester ? $selectedSemester->semester_name : 'Terminal Examination';
                    $newExam = Exam::create([
                        'school_id'   => auth()->user()->school_id ?? null,
                        'exam_name'   => $semName,
                        'start_date'  => now(),
                        'end_date'    => now()->addDays(7),
                        'semester_id' => $semester_id,
                        'class_id'    => $class_id,
                        'course_id'   => (int)$course_id,
                        'session_id'  => $current_school_session_id,
                    ]);
                    ExamRule::create([
                        'school_id'   => auth()->user()->school_id ?? null,
                        'exam_id'     => $newExam->id,
                        'session_id'  => $current_school_session_id,
                        'total_marks' => 100,
                        'pass_marks'  => 40,
                        'marks_distribution_note' => 'Standard 100 marks',
                    ]);
                    $exams = collect([$newExam]);
                }

                $examRulesByExamId = ExamRule::where('session_id', $current_school_session_id)
                    ->whereIn('exam_id', $exams->pluck('id')->toArray())
                    ->pluck('total_marks', 'exam_id');

                $markRepository = new MarkRepository();
                $studentsWithMarks = $markRepository->getAll($current_school_session_id, $semester_id, $class_id, $section_id, (int)$course_id);

                // Build a structured lookup map: student_id => [ exam_id => mark ]
                $marksByStudentAndExam = [];
                foreach ($studentsWithMarks as $m) {
                    $marksByStudentAndExam[$m->student_id][$m->exam_id] = $m->marks;
                }

                $studentsWithMarksGrouped = $studentsWithMarks ? $studentsWithMarks->groupBy('student_id') : collect();

                $final_marks_submitted = false;
                $final_marks_submit_count = $markRepository->getFinalMarksCount($current_school_session_id, $semester_id, $class_id, $section_id, (int)$course_id);
                if ($final_marks_submit_count > 0) {
                    $final_marks_submitted = true;
                }

                return view('marks.create', [
                    'academic_setting'          => $academic_setting,
                    'semesters'                 => $semesters,
                    'school_classes'            => $school_classes,
                    'exams'                     => $exams,
                    'students_with_marks'       => $studentsWithMarksGrouped,
                    'marksByStudentAndExam'     => $marksByStudentAndExam,
                    'class_id'                  => $class_id,
                    'section_id'                => $section_id,
                    'course_id'                 => (int)$course_id,
                    'semester_id'               => $semester_id,
                    'final_marks_submitted'     => $final_marks_submitted,
                    'sectionStudents'           => $sectionStudents,
                    'exam_rules_by_exam_id'     => $examRulesByExamId,
                    'current_school_session_id' => $current_school_session_id,
                    'isAllSubjectsMode'         => false,
                    'classCourses'              => $classCourses,
                    'classSections'             => $classSections,
                    'marksMatrix'               => [],
                    'courseMaxMarks'            => [],
                    'selectedClass'             => $selectedClass,
                    'selectedSection'           => $selectedSection,
                    'selectedSemester'          => $selectedSemester,
                    'selectedCourse'            => $selectedCourse,
                    'isAdmin'                   => $isAdmin,
                    'isClassTeacher'            => $isClassTeacher,
                    'canEditAllSubjects'        => $canEditAllSubjects,
                ]);
            } // end single subject mode
        } catch (\Exception $e) {
            return view('marks.create', [
                'academic_setting'          => null,
                'semesters'                 => collect(),
                'school_classes'            => collect(),
                'exams'                     => collect(),
                'students_with_marks'       => collect(),
                'class_id'                  => null,
                'section_id'                => 0,
                'course_id'                 => 0,
                'semester_id'               => 0,
                'final_marks_submitted'     => false,
                'sectionStudents'           => collect(),
                'exam_rules_by_exam_id'     => collect(),
                'current_school_session_id' => 0,
                'isAllSubjectsMode'         => true,
                'classCourses'              => collect(),
                'classSections'             => collect(),
                'marksMatrix'               => [],
                'courseMaxMarks'            => [],
                'selectedClass'             => null,
                'selectedSection'           => null,
                'selectedSemester'          => null,
                'selectedCourse'            => null,
                'error'                     => 'Unable to load marks entry form: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function showFinalMark(Request $request)
    {
        try {
            $class_id = $request->query('class_id');
            $section_id = $request->query('section_id', 0);
            $course_id = $request->query('course_id');
            $semester_id = $request->query('semester_id', 0);

            $current_school_session_id = $this->getSchoolCurrentSession();

            $markRepository = new MarkRepository();
            $studentsWithMarks = $markRepository->getAll($current_school_session_id, $semester_id, $class_id, $section_id, $course_id);
            $studentsWithMarks = $studentsWithMarks ? $studentsWithMarks->groupBy('student_id') : collect();

            $sectionStudents = $this->userRepository ? $this->userRepository->getAllStudents($current_school_session_id, (int)$class_id, (int)$section_id) : collect();

            $data = [
                'students_with_marks'       => $studentsWithMarks,
                'sectionStudents'           => $sectionStudents,
                'class_id'                  => $class_id,
                'class_name'                => $request->query('class_name'),
                'section_id'                => $section_id,
                'section_name'              => $request->query('section_name'),
                'course_id'                 => $course_id,
                'course_name'               => $request->query('course_name'),
                'semester_id'               => $semester_id,
                'current_school_session_id' => $current_school_session_id,
            ];

            return view('marks.submit-final-marks', $data);
        } catch (\Exception $e) {
            return view('marks.submit-final-marks', [
                'students_with_marks'       => collect(),
                'sectionStudents'           => collect(),
                'class_id'                  => null,
                'class_name'                => null,
                'section_id'                => null,
                'section_name'              => null,
                'course_id'                 => null,
                'course_name'               => null,
                'semester_id'               => 0,
                'current_school_session_id' => 0,
                'error' => 'Unable to load final marks form: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        ini_set('memory_limit', '512M');
        $current_school_session_id = $this->getSchoolCurrentSession();
        $user = auth()->user();
        $isAdmin = $this->isMarksManagementUser($user);

        $class_id = (int)$request->input('class_id');
        $section_id = (int)$request->input('section_id', 0);
        $semester_id = (int)$request->input('semester_id', 0);

        $isClassTeacher = $class_id ? $this->isAssignedClassTeacher($user, $current_school_session_id, $class_id, $section_id) : false;
        $canEditAllSubjects = $isAdmin || $isClassTeacher;

        // Case 1: Master Grade Ledger (All Subjects Submitted at once)
        if ($request->has('student_course_mark')) {
            if (!$canEditAllSubjects) {
                abort(403, 'Access Restricted: Only designated class teachers (Class In-Charge) and school administrators can edit and submit marks across all subjects.');
            }
            $studentCourseMarks = $request->input('student_course_mark', []);

            if (empty($studentCourseMarks)) {
                return back()->with('error', 'No marks data received. Please enter at least one mark before saving.');
            }

            $semester = Semester::find($semester_id);
            $semesterName = $semester ? $semester->semester_name : 'Terminal Examination';

            // Get ALL unique course IDs submitted from the form
            $submittedCourseIds = collect($studentCourseMarks)
                ->flatMap(fn($courses) => array_keys($courses))
                ->unique()
                ->map(fn($id) => (int)$id)
                ->toArray();

            // Build exam cache keyed by course_id
            $examCache = [];
            foreach ($submittedCourseIds as $cId) {
                // Find exam for this course in this semester
                $exam = Exam::where('session_id', $current_school_session_id)
                    ->where('class_id', $class_id)
                    ->where('course_id', $cId)
                    ->when($semester_id > 0, fn($q) => $q->where('semester_id', $semester_id))
                    ->first();

                if (!$exam) {
                    $exam = Exam::create([
                        'school_id'   => auth()->user()->school_id ?? null,
                        'exam_name'   => $semesterName,
                        'start_date'  => now(),
                        'end_date'    => now()->addDays(7),
                        'semester_id' => $semester_id,
                        'class_id'    => $class_id,
                        'course_id'   => $cId,
                        'session_id'  => $current_school_session_id,
                    ]);
                }

                // Ensure ExamRule exists
                ExamRule::firstOrCreate(
                    ['exam_id' => $exam->id, 'session_id' => $current_school_session_id],
                    [
                        'school_id'                => auth()->user()->school_id ?? null,
                        'total_marks'              => 100,
                        'pass_marks'               => 40,
                        'marks_distribution_note'  => 'Standard 100 marks',
                    ]
                );

                $examCache[$cId] = $exam;
            }

            $studentSectionMap = \App\Models\Promotion::where('session_id', $current_school_session_id)
                ->where('class_id', $class_id)
                ->whereIn('student_id', array_keys($studentCourseMarks))
                ->pluck('section_id', 'student_id')
                ->toArray();

            $rows = [];
            foreach ($studentCourseMarks as $studentId => $coursesMarks) {
                $actualSectionId = $section_id > 0 ? $section_id : (int)($studentSectionMap[$studentId] ?? 0);
                foreach ($coursesMarks as $cId => $mark) {
                    if ($mark === null || $mark === '') continue;
                    $cId = (int)$cId;
                    if (isset($examCache[$cId])) {
                        $examId   = $examCache[$cId]->id;
                        $maxMarks = (float)($examRulesMap[$examId] ?? 100);
                        $rows[] = [
                            'school_id'  => auth()->user()->school_id ?? null,
                            'class_id'   => $class_id,
                            'student_id' => (int)$studentId,
                            'marks'      => (float)$mark,
                            'section_id' => $actualSectionId,
                            'course_id'  => (int)$cId,
                            'session_id' => $current_school_session_id,
                            'exam_id'    => $examId,
                        ];
                    }
                }
            }

            try {
                $markRepository = new MarkRepository();
                $markRepository->create($rows);

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'status'  => 'success',
                        'message' => 'Successfully recorded marks across all subjects for ' . count($rows) . ' entries!',
                        'count'   => count($rows),
                    ]);
                }

                return back()->with('status', 'Successfully recorded marks across all subjects for ' . count($rows) . ' entries!');
            } catch (\Exception $e) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Error saving marks: ' . $e->getMessage(),
                    ], 500);
                }
                return back()->with('error', 'Error saving marks: ' . $e->getMessage());
            }
        }

        // Case 2: Single Subject Entry (student_mark array)
        if (!$isAdmin) {
            $this->checkIfLoggedInUserIsAssignedTeacher($request, $current_school_session_id);
        }

        $validator = Validator::make($request->all(), [
            'student_mark' => 'required|array',
            'student_mark.*' => 'required|array',
            'student_mark.*.*' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $validator->errors()->first(),
                    'errors'  => $validator->errors(),
                ], 422);
            }
            return back()->withInput()->withErrors($validator);
        }

        $submittedExamIds = collect($request->student_mark)
            ->flatMap(function ($studentMarks) {
                return array_keys($studentMarks);
            })
            ->unique()
            ->values()
            ->toArray();

        $maxMarksByExamId = ExamRule::where('session_id', $current_school_session_id)
            ->whereIn('exam_id', $submittedExamIds)
            ->pluck('total_marks', 'exam_id');

        // Auto-create missing ExamRules so users are never blocked
        foreach ($submittedExamIds as $eId) {
            if (!$maxMarksByExamId->has($eId)) {
                ExamRule::create([
                    'school_id'   => auth()->user()->school_id ?? null,
                    'exam_id'     => $eId,
                    'session_id'  => $current_school_session_id,
                    'total_marks' => 100,
                    'pass_marks'  => 40,
                    'marks_distribution_note' => 'Standard 100 marks',
                ]);
                $maxMarksByExamId[$eId] = 100;
            }
        }

        $studentSectionMap = \App\Models\Promotion::where('session_id', $current_school_session_id)
            ->where('class_id', $class_id)
            ->whereIn('student_id', array_keys($request->student_mark))
            ->pluck('section_id', 'student_id')
            ->toArray();

        $rows = [];
        foreach ($request->student_mark as $id => $stm) {
            $actualSectionId = $section_id > 0 ? $section_id : (int)($studentSectionMap[$id] ?? 0);
            foreach ($stm as $exam => $mark) {
                if ($mark === null || $mark === '') {
                    continue;
                }

                $max = $maxMarksByExamId[$exam] ?? 100;
                if ((float)$mark > (float)$max) {
                    $errMsg = "Marks must be between 0 and {$max} for each exam.";
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json(['status' => 'error', 'message' => $errMsg], 422);
                    }
                    return back()->withInput()->with('error', $errMsg);
                }

                $rows[] = [
                    'class_id'   => $class_id,
                    'student_id' => (int)$id,
                    'marks'      => (float)$mark,
                    'section_id' => $actualSectionId,
                    'course_id'  => (int)$request->course_id,
                    'session_id' => (int)$request->session_id,
                    'exam_id'    => (int)$exam,
                ];
            }
        }

        if (empty($rows)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'info',
                    'message' => 'No marks entered to save. Please enter marks for at least one student.',
                ]);
            }
            return back()->with('info', 'No marks entered to save. Please enter marks for at least one student.');
        }

        try {
            $markRepository = new MarkRepository();
            $markRepository->create($rows);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Saving marks was successful! ' . count($rows) . ' mark entry(ies) recorded.',
                    'count'   => count($rows),
                ]);
            }

            return back()->with('status', 'Saving marks was successful! ' . count($rows) . ' mark entry(ies) recorded.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ], 500);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeFinalMark(Request $request) {
        $current_school_session_id = $this->getSchoolCurrentSession();

        $this->checkIfLoggedInUserIsAssignedTeacher($request, $current_school_session_id);
        $rows = [];
        foreach($request->calculated_mark as $id => $cmark) {
                $row = [];
                $row['class_id'] = $request->class_id;
                $row['student_id'] = $id;
                $row['calculated_marks'] = $cmark;
                $row['final_marks'] = $request->final_mark[$id];
                $row['note'] = $request->note[$id];
                $row['section_id'] = $request->section_id;
                $row['course_id'] = $request->course_id;
                $row['session_id'] = $request->session_id;
                $row['semester_id'] = $request->semester_id;

                $rows[] = $row;
        }
        try {
            $markRepository = new MarkRepository();
            $markRepository->storeFinalMarks($rows);

            return back()->with('status', 'Submitting final marks was successful!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function showCourseMark(Request $request)
    {
        try {
            $session_id = $request->query('session_id');
            $semester_id = $request->query('semester_id');
            $class_id = $request->query('class_id');
            $section_id = $request->query('section_id');
            $course_id = $request->query('course_id');
            $course_name = $request->query('course_name');
            $student_id = $request->query('student_id');
            $markRepository = new MarkRepository();
            $marks = $markRepository->getAllByStudentId($session_id, $semester_id, $class_id, $section_id, $course_id, $student_id);
            $finalMarks = $markRepository->getAllFinalMarksByStudentId($session_id, $student_id, $semester_id, $class_id, $section_id, $course_id);

            if(!$finalMarks) {
                $finalMarks = collect();
            }

            $gradingSystemRepository = new GradingSystemRepository();
            $gradingSystem = $gradingSystemRepository->getGradingSystem($session_id, $semester_id, $class_id);

            $gradingSystemRules = collect();
            if($gradingSystem) {
                $gradeRulesRepository = new GradeRuleRepository();
                $gradingSystemRules = $gradeRulesRepository->getAll($session_id, $gradingSystem->id) ?? collect();
            }

            foreach($finalMarks as $mark_key => $mark) {
                foreach ($gradingSystemRules as $key => $gradingSystemRule) {
                    if($mark->final_marks >= $gradingSystemRule->start_at && $mark->final_marks <= $gradingSystemRule->end_at) {
                        $finalMarks[$mark_key]['point'] = $gradingSystemRule->point;
                        $finalMarks[$mark_key]['grade'] = $gradingSystemRule->grade;
                    }
                }
            }

            $data = [
                'marks' => $marks,
                'final_marks'   => $finalMarks,
                'course_name' => $course_name,
            ];

            return view('marks.student', $data);
        } catch (\Exception $e) {
            return view('marks.student', [
                'marks' => collect(),
                'final_marks' => collect(),
                'course_name' => null,
                'error' => 'Unable to load course marks: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Mark  $mark
     * @return \Illuminate\Http\Response
     */
    public function edit(Mark $mark)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Mark  $mark
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Mark $mark)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Mark  $mark
     * @return \Illuminate\Http\Response
     */
    public function destroy(Mark $mark)
    {
        //
    }
}
