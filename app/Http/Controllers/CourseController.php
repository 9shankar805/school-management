<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Semester;
use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Interfaces\CourseInterface;
use App\Http\Requests\CourseStoreRequest;
use App\Interfaces\SchoolSessionInterface;
use App\Repositories\PromotionRepository;

class CourseController extends Controller
{
    use SchoolSession;
    protected $schoolCourseRepository;
    protected $schoolSessionRepository;

    /**
    * Create a new Controller instance
    * 
    * @param CourseInterface $schoolCourseRepository
    * @return void
    */
    public function __construct(SchoolSessionInterface $schoolSessionRepository, CourseInterface $schoolCourseRepository) {
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolCourseRepository = $schoolCourseRepository;
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
     * @param  CourseStoreRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(CourseStoreRequest $request)
    {
        try {
            $validated = $request->validated();
            $sessionId = (int) $validated['session_id'];
            $courseName = $validated['course_name'];
            $courseType = $validated['course_type'];
            $classIds = array_map('intval', $validated['class_ids']);
            $allTerms = !empty($validated['all_terms']);

            // Determine which semesters to create the course for
            if ($allTerms) {
                $semesterIds = Semester::where('session_id', $sessionId)->pluck('id')->all();
                if (empty($semesterIds)) {
                    return back()->with('error', 'No semesters/terms found for the current session. Please create semesters first.');
                }
            } else {
                $semesterIds = [(int) $validated['semester_id']];
            }

            $createdCount = 0;
            $skippedCount = 0;

            // Create course for each class × each semester combination
            foreach ($classIds as $classId) {
                foreach ($semesterIds as $semesterId) {
                    $course = Course::firstOrCreate([
                        'course_name' => $courseName,
                        'class_id'    => $classId,
                        'semester_id' => $semesterId,
                        'session_id'  => $sessionId,
                    ], [
                        'course_type' => $courseType,
                    ]);

                    if ($course->wasRecentlyCreated) {
                        $createdCount++;
                    } else {
                        $skippedCount++;
                    }
                }
            }

            $classNames = \App\Models\SchoolClass::whereIn('id', $classIds)->pluck('class_name')->all();
            $classStr = implode(', ', array_slice($classNames, 0, 5)) . (count($classNames) > 5 ? ' +' . (count($classNames) - 5) . ' more' : '');
            $termStr = $allTerms ? 'all terms/semesters' : 'selected semester';

            $msg = "Subject \"{$courseName}\" created for {$classStr} across {$termStr} — {$createdCount} new record(s).";
            if ($skippedCount > 0) {
                $msg .= " ({$skippedCount} already existed, skipped.)";
            }

            return back()->with('status', $msg);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getStudentCourses($student_id) {
        $current_school_session_id = $this->getSchoolCurrentSession();
        $promotionRepository = new PromotionRepository();
        $class_info = $promotionRepository->getPromotionInfoById($current_school_session_id, $student_id);
        $courses = $this->schoolCourseRepository->getByClassId($class_info->class_id);

        $data = [
            'class_info'    => $class_info,
            'courses'       => $courses,
        ];
        return view('courses.student', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  $course_id
     * @return \Illuminate\Http\Response
     */
    public function edit($course_id)
    {
        $current_school_session_id = $this->getSchoolCurrentSession();

        $course = $this->schoolCourseRepository->findById($course_id);

        $data = [
            'current_school_session_id' => $current_school_session_id,
            'course'                    => $course,
            'course_id'                 => $course_id,
        ];

        return view('courses.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $this->schoolCourseRepository->update($request);

            return back()->with('status', 'Course update was successful!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function destroy(Course $course)
    {
        //
    }

    /**
     * Delete a course (all semester copies for same name+class) via POST /course/delete/{id}
     */
    public function destroyById(int $id)
    {
        $this->authorize('create classes');
        $course = Course::findOrFail($id);

        // Delete all semester copies of this subject for the same class
        $deleted = Course::where('class_id', $course->class_id)
            ->where('course_name', $course->course_name)
            ->where('session_id', $course->session_id)
            ->count();

        Course::where('class_id', $course->class_id)
            ->where('course_name', $course->course_name)
            ->where('session_id', $course->session_id)
            ->delete();

        return back()->with('status', "Subject \"{$course->course_name}\" removed ({$deleted} term record(s) deleted).");
    }
}
