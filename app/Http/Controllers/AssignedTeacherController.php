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
                // Find previous class teachers for this section to sync their roles later
                $previousTeachers = \App\Models\AssignedTeacher::where('session_id', $validated['session_id'])
                    ->where('class_id', $validated['class_id'])
                    ->where('section_id', $validated['section_id'])
                    ->where('is_class_teacher', true)
                    ->pluck('teacher_id')
                    ->all();

                // Remove Class Teacher status from previous assignments for this section
                \App\Models\AssignedTeacher::where('session_id', $validated['session_id'])
                    ->where('class_id', $validated['class_id'])
                    ->where('section_id', $validated['section_id'])
                    ->update(['is_class_teacher' => false]);

                foreach ($previousTeachers as $prevId) {
                    $this->syncTeacherRole($prevId, $validated['session_id']);
                }
            }

            $validated['is_class_teacher'] = $isClassTeacher;

            $assignedTeacherRepository = new AssignedTeacherRepository();
            $assignedTeacherRepository->assign($validated);

            if ($isClassTeacher) {
                $this->syncTeacherRole($validated['teacher_id'], $validated['session_id']);
            }

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
            $sessionId = $assignment->session_id;

            if ($newStatus) {
                // Find previous teachers
                $previousTeachers = \App\Models\AssignedTeacher::where('session_id', $sessionId)
                    ->where('class_id', $assignment->class_id)
                    ->where('section_id', $assignment->section_id)
                    ->where('is_class_teacher', true)
                    ->pluck('teacher_id')
                    ->all();

                // Clear existing Class Teacher for this class section
                \App\Models\AssignedTeacher::where('session_id', $sessionId)
                    ->where('class_id', $assignment->class_id)
                    ->where('section_id', $assignment->section_id)
                    ->update(['is_class_teacher' => false]);

                foreach ($previousTeachers as $prevId) {
                    $this->syncTeacherRole($prevId, $sessionId);
                }
            }

            $assignment->is_class_teacher = $newStatus;
            $assignment->save();

            $this->syncTeacherRole($assignment->teacher_id, $sessionId);

            $msg = $newStatus
                ? 'Teacher designated as Class Teacher!'
                : 'Class Teacher designation removed.';

            return back()->with('status', $msg);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Directly assign or change the Class Teacher for a class & section (Principal/Admin 1-click action).
     */
    public function assignSectionClassTeacher(Request $request, $classId, $sectionId)
    {
        $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'course_id'  => 'nullable|exists:courses,id',
        ]);

        try {
            $sessionId = $this->getSchoolCurrentSession();
            $teacherId = (int) $request->teacher_id;
            $semesterId = \App\Models\Semester::where('session_id', $sessionId)->value('id') ?? 1;

            // Clear previous class teacher for this section
            $previousTeachers = \App\Models\AssignedTeacher::where('session_id', $sessionId)
                ->where('class_id', $classId)
                ->where('section_id', $sectionId)
                ->where('is_class_teacher', true)
                ->pluck('teacher_id')
                ->all();

            \App\Models\AssignedTeacher::where('session_id', $sessionId)
                ->where('class_id', $classId)
                ->where('section_id', $sectionId)
                ->update(['is_class_teacher' => false]);

            foreach ($previousTeachers as $prevId) {
                $this->syncTeacherRole($prevId, $sessionId);
            }

            // Check if teacher already has any record in this section
            $existing = \App\Models\AssignedTeacher::where('session_id', $sessionId)
                ->where('class_id', $classId)
                ->where('section_id', $sectionId)
                ->where('teacher_id', $teacherId)
                ->first();

            if ($existing) {
                $existing->is_class_teacher = true;
                $existing->save();
            } else {
                $courseId = $request->course_id ?: (\App\Models\Course::where('session_id', $sessionId)->value('id') ?? 1);
                \App\Models\AssignedTeacher::create([
                    'session_id'       => $sessionId,
                    'semester_id'      => $semesterId,
                    'class_id'         => $classId,
                    'section_id'       => $sectionId,
                    'teacher_id'       => $teacherId,
                    'course_id'        => $courseId,
                    'is_class_teacher' => true,
                ]);
            }

            $this->syncTeacherRole($teacherId, $sessionId);

            $teacherName = \App\Models\User::find($teacherId)?->full_name ?? 'Teacher';

            return back()->with('status', "{$teacherName} has been assigned as the Class Teacher successfully!");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to assign class teacher: ' . $e->getMessage());
        }
    }

    /**
     * Helper to keep the Spatie role 'class-teacher' strictly in sync with actual assignments.
     */
    protected function syncTeacherRole(int $teacherId, int $sessionId): void
    {
        try {
            $user = \App\Models\User::find($teacherId);
            if (!$user) return;

            $hasClassTeacherRole = \App\Models\AssignedTeacher::where('teacher_id', $teacherId)
                ->where('session_id', $sessionId)
                ->where('is_class_teacher', true)
                ->exists();

            if ($hasClassTeacherRole) {
                if (!$user->hasRole('class-teacher')) {
                    $user->assignRole('class-teacher');
                }
            } else {
                // If they don't have any active class teacher duties, remove role if present
                if ($user->hasRole('class-teacher')) {
                    $user->removeRole('class-teacher');
                }
            }
        } catch (\Throwable $t) {
            // Log or ignore gracefully
        }
    }
}

