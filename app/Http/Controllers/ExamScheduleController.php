<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\User;
use App\Repositories\ExamHallRepository;
use App\Repositories\ExamRepository;
use App\Repositories\ExamScheduleRepository;
use App\Traits\SchoolSession;
use App\Interfaces\SchoolSessionInterface;
use App\Interfaces\SchoolClassInterface;
use App\Interfaces\SemesterInterface;
use Illuminate\Http\Request;

class ExamScheduleController extends Controller
{
    use SchoolSession;

    protected $schoolSessionRepository;
    protected $schoolClassRepository;
    protected $semesterRepository;

    public function __construct(
        SchoolSessionInterface $schoolSessionRepository,
        SchoolClassInterface   $schoolClassRepository,
        SemesterInterface      $semesterRepository
    ) {
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolClassRepository   = $schoolClassRepository;
        $this->semesterRepository      = $semesterRepository;
        $this->middleware(['auth', 'can:view exams']);
    }

    /**
     * GET /exam/schedule
     * Full exam timetable for the current session.
     */
    public function index(Request $request)
    {
        $sessionId  = $this->getSchoolCurrentSession();
        $classId    = (int) $request->query('class_id',    0);
        $semesterId = (int) $request->query('semester_id', 0);

        $scheduleRepo = new ExamScheduleRepository();
        $schedules    = $scheduleRepo->getTimetable($sessionId, $classId, $semesterId);

        $classes   = $this->schoolClassRepository->getAllBySession($sessionId);
        $semesters = $this->semesterRepository->getAll($sessionId);

        return view('exams.schedule.index', compact(
            'schedules', 'classes', 'semesters', 'sessionId', 'classId', 'semesterId'
        ));
    }

    /**
     * GET /exam/schedule/create
     * Form to add a schedule entry.
     */
    public function create(Request $request)
    {
        $this->authorize('create exams');

        $sessionId  = $this->getSchoolCurrentSession();
        $examRepo   = new ExamRepository();
        $hallRepo   = new ExamHallRepository();

        // Pre-select exam if passed via query string
        $selectedExamId = (int) $request->query('exam_id', 0);

        $classes   = $this->schoolClassRepository->getAllBySession($sessionId);
        $semesters = $this->semesterRepository->getAll($sessionId);
        $halls     = $hallRepo->getActive($sessionId);
        $invigilators = User::role(['teacher', 'class-teacher', 'admin', 'principal'])
            ->orderBy('first_name')
            ->get();

        return view('exams.schedule.create', compact(
            'sessionId', 'classes', 'semesters', 'halls', 'invigilators', 'selectedExamId'
        ));
    }

    /**
     * POST /exam/schedule
     */
    public function store(Request $request)
    {
        $this->authorize('create exams');

        $validated = $request->validate([
            'exam_id'        => 'required|integer|exists:exams,id',
            'hall_id'        => 'nullable|integer|exists:exam_halls,id',
            'exam_date'      => 'required|date',
            'start_time'     => 'required|date_format:H:i',
            'end_time'       => 'required|date_format:H:i|after:start_time',
            'invigilator_id' => 'nullable|integer|exists:users,id',
            'notes'          => 'nullable|string|max:500',
        ]);

        $sessionId = $this->getSchoolCurrentSession();

        // Conflict detection
        if (! empty($validated['hall_id'])) {
            $repo = new ExamScheduleRepository();
            if ($repo->hasConflict(
                $validated['hall_id'],
                $validated['exam_date'],
                $validated['start_time'] . ':00',
                $validated['end_time']   . ':00'
            )) {
                return back()->withInput()->withErrors([
                    'hall_id' => 'This hall is already booked during the selected time slot.',
                ]);
            }
        }

        (new ExamScheduleRepository())->create(array_merge($validated, [
            'session_id'  => $sessionId,
            'start_time'  => $validated['start_time'] . ':00',
            'end_time'    => $validated['end_time']   . ':00',
        ]));

        return redirect()->route('exam.schedule.index')
            ->with('status', 'Exam schedule entry created.');
    }

    /**
     * GET /exam/schedule/{id}/edit
     */
    public function edit(int $id)
    {
        $this->authorize('create exams');

        $sessionId    = $this->getSchoolCurrentSession();
        $scheduleRepo = new ExamScheduleRepository();
        $hallRepo     = new ExamHallRepository();

        $schedule     = $scheduleRepo->findById($id);
        $halls        = $hallRepo->getActive($sessionId);
        $invigilators = User::role(['teacher', 'class-teacher', 'admin', 'principal'])
            ->orderBy('first_name')
            ->get();

        return view('exams.schedule.edit', compact('schedule', 'halls', 'invigilators', 'sessionId'));
    }

    /**
     * PUT /exam/schedule/{id}
     */
    public function update(Request $request, int $id)
    {
        $this->authorize('create exams');

        $validated = $request->validate([
            'hall_id'        => 'nullable|integer|exists:exam_halls,id',
            'exam_date'      => 'required|date',
            'start_time'     => 'required|date_format:H:i',
            'end_time'       => 'required|date_format:H:i|after:start_time',
            'invigilator_id' => 'nullable|integer|exists:users,id',
            'notes'          => 'nullable|string|max:500',
        ]);

        if (! empty($validated['hall_id'])) {
            $repo = new ExamScheduleRepository();
            if ($repo->hasConflict(
                $validated['hall_id'],
                $validated['exam_date'],
                $validated['start_time'] . ':00',
                $validated['end_time']   . ':00',
                $id
            )) {
                return back()->withInput()->withErrors([
                    'hall_id' => 'This hall is already booked during the selected time slot.',
                ]);
            }
        }

        (new ExamScheduleRepository())->update($id, array_merge($validated, [
            'start_time' => $validated['start_time'] . ':00',
            'end_time'   => $validated['end_time']   . ':00',
        ]));

        return redirect()->route('exam.schedule.index')
            ->with('status', 'Schedule entry updated.');
    }

    /**
     * DELETE /exam/schedule/{id}
     */
    public function destroy(int $id)
    {
        $this->authorize('create exams');
        (new ExamScheduleRepository())->delete($id);
        return back()->with('status', 'Schedule entry deleted.');
    }

    /**
     * GET /exam/schedule/{examId}/by-exam
     * JSON: all schedules for a given exam (used in dynamic dropdowns).
     */
    public function byExam(int $examId)
    {
        $schedules = (new ExamScheduleRepository())->getByExam($examId);
        return response()->json($schedules);
    }

    /**
     * GET /exam/schedule/exams-for?class_id=&semester_id=
     * JSON: exams for a class+semester (used to populate create form dropdown).
     */
    public function examsForFilter(Request $request)
    {
        $sessionId  = $this->getSchoolCurrentSession();
        $classId    = (int) $request->query('class_id',    0);
        $semesterId = (int) $request->query('semester_id', 0);

        $exams = (new \App\Repositories\ExamRepository())->getAll($sessionId, $semesterId, $classId);
        return response()->json($exams->load('course'));
    }
}
