<?php

namespace App\Http\Controllers\Api\StudentApp;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamSeatAllocation;
use App\Models\ExamSchedule;
use App\Models\StudentStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    /**
     * GET /student-app/exams
     * All exams for the student's current class.
     *
     * exams table: id, exam_name, class_id, session_id, start_date, end_date
     */
    public function index(Request $request): JsonResponse
    {
        $classId = $this->currentClassId($request);

        $exams = $classId
            ? Exam::where('class_id', $classId)
                ->orderByDesc('start_date')
                ->get()
                ->map(fn($e) => $this->formatExam($e))
            : collect();

        return response()->json([
            'status' => 'success',
            'data'   => $exams,
        ]);
    }

    /**
     * GET /student-app/exams/upcoming
     */
    public function upcoming(Request $request): JsonResponse
    {
        $classId = $this->currentClassId($request);

        $exams = $classId
            ? Exam::where('class_id', $classId)
                ->where('start_date', '>=', now()->toDateString())
                ->orderBy('start_date')
                ->get()
                ->map(fn($e) => $this->formatExam($e))
            : collect();

        return response()->json([
            'status' => 'success',
            'data'   => $exams,
        ]);
    }

    /**
     * GET /student-app/exams/past
     */
    public function past(Request $request): JsonResponse
    {
        $classId = $this->currentClassId($request);

        $exams = $classId
            ? Exam::where('class_id', $classId)
                ->where('end_date', '<', now()->toDateString())
                ->orderByDesc('end_date')
                ->get()
                ->map(fn($e) => $this->formatExam($e))
            : collect();

        return response()->json([
            'status' => 'success',
            'data'   => $exams,
        ]);
    }

    /**
     * GET /student-app/exams/{id}
     * Exam detail with schedule (dates, times, halls per subject).
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $classId = $this->currentClassId($request);

        $exam = Exam::where('class_id', $classId)->findOrFail($id);

        // Exam schedules link an exam to specific dates/halls
        // Note: exam_schedules has no course_id — subject comes from the parent exam
        $schedules = ExamSchedule::where('exam_id', $id)
            ->with(['hall'])
            ->orderBy('exam_date')
            ->get()
            ->map(fn($s) => [
                'date'        => $s->exam_date,
                'start_time'  => $s->start_time,
                'end_time'    => $s->end_time,
                'hall'        => $s->hall?->hall_name,
                'notes'       => $s->notes,
            ]);

        return response()->json([
            'status' => 'success',
            'data'   => array_merge($this->formatExam($exam), ['schedules' => $schedules]),
        ]);
    }

    /**
     * GET /student-app/exams/{id}/admit-card
     *
     * ExamSeatAllocation: id, schedule_id, student_id, seat_number, bench_no, school_id
     * We find allocations via ExamSchedule → exam_id.
     */
    public function admitCard(Request $request, int $examId): JsonResponse
    {
        $student = $request->user();

        // Find seat allocations for any schedule belonging to this exam
        $scheduleIds = ExamSchedule::where('exam_id', $examId)->pluck('id');

        if ($scheduleIds->isEmpty()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No exam schedules found for this exam.',
            ], 404);
        }

        $allocations = ExamSeatAllocation::whereIn('schedule_id', $scheduleIds)
            ->where('student_id', $student->id)
            ->with(['schedule.hall'])   // schedule has exam and hall, but no course
            ->get();

        if ($allocations->isEmpty()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Admit card not available. Seat not allocated yet.',
            ], 404);
        }

        $exam   = Exam::find($examId);
        $status = StudentStatus::where('student_id', $student->id)
            ->where('is_current', true)
            ->with(['schoolClass', 'section'])
            ->first();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'student_name'  => $student->full_name,
                'admission_no'  => $student->admission_no,
                'roll_no'       => $student->roll_no,
                'class'         => $status?->schoolClass?->class_name,
                'section'       => $status?->section?->section_name,
                'exam_name'     => $exam?->exam_name,
                'seats'         => $allocations->map(fn($a) => [
                    'date'        => $a->schedule?->exam_date,
                    'start_time'  => $a->schedule?->start_time,
                    'hall'        => $a->schedule?->hall?->hall_name,
                    'seat_number' => $a->seat_number,
                    'bench_no'    => $a->bench_no,
                    'notes'       => $a->notes,
                ]),
            ],
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function currentClassId(Request $request): ?int
    {
        return StudentStatus::where('student_id', $request->user()->id)
            ->where('is_current', true)
            ->value('class_id');
    }

    private function formatExam(Exam $exam): array
    {
        return [
            'id'         => $exam->id,
            'name'       => $exam->exam_name,   // actual column: exam_name
            'start_date' => $exam->start_date,
            'end_date'   => $exam->end_date,
            'class_id'   => $exam->class_id,
            'session_id' => $exam->session_id,
        ];
    }
}
