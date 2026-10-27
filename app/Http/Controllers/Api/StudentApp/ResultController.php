<?php

namespace App\Http\Controllers\Api\StudentApp;

use App\Http\Controllers\Controller;
use App\Models\Mark;
use App\Models\FinalMark;
use App\Models\Exam;
use App\Models\StudentStatus;
use App\Models\SchoolSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    /**
     * GET /student-app/results
     * All marks for the authenticated student.
     *
     * marks table: id, student_id, exam_id, course_id, class_id, session_id, marks, school_id
     */
    public function index(Request $request): JsonResponse
    {
        $marks = Mark::with(['exam', 'course'])
            ->where('student_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($m) => [
                'id'          => $m->id,
                'exam_id'     => $m->exam_id,
                'exam_name'   => $m->exam?->exam_name,  // actual column: exam_name
                'course_id'   => $m->course_id,
                'course_name' => $m->course?->course_name,
                'marks'       => $m->marks,             // actual column: marks
                'session_id'  => $m->session_id,
            ]);

        return response()->json([
            'status' => 'success',
            'data'   => $marks,
        ]);
    }

    /**
     * GET /student-app/results/exam/{examId}
     * Marks for a specific exam.
     */
    public function byExam(Request $request, int $examId): JsonResponse
    {
        $marks = Mark::with('course')
            ->where('student_id', $request->user()->id)
            ->where('exam_id', $examId)
            ->get()
            ->map(fn($m) => [
                'course_id'   => $m->course_id,
                'course_name' => $m->course?->course_name,
                'marks'       => $m->marks,
            ]);

        $exam = Exam::find($examId);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'exam'  => $exam ? [
                    'id'        => $exam->id,
                    'name'      => $exam->exam_name,    // actual column
                    'start'     => $exam->start_date,
                    'end'       => $exam->end_date,
                ] : null,
                'marks' => $marks,
            ],
        ]);
    }

    /**
     * GET /student-app/results/report-cards
     * Final marks grouped by session.
     *
     * final_marks table: id, student_id, course_id, class_id, session_id, final_marks, calculated_marks
     */
    public function reportCards(Request $request): JsonResponse
    {
        $finalMarks = FinalMark::with(['course'])
            ->where('student_id', $request->user()->id)
            ->orderByDesc('session_id')
            ->get();

        // Load session names in one query
        $sessionIds  = $finalMarks->pluck('session_id')->unique();
        $sessions    = \App\Models\SchoolSession::whereIn('id', $sessionIds)
            ->pluck('session_name', 'id');

        $grouped = $finalMarks->groupBy('session_id')->map(function ($items, $sessionId) use ($sessions) {
            return [
                'session_id'   => $sessionId,
                'session_name' => $sessions[$sessionId] ?? "Session {$sessionId}",
                'subjects'     => $items->map(fn($m) => [
                    'course_name'      => $m->course?->course_name,
                    'final_marks'      => $m->final_marks,
                    'calculated_marks' => $m->calculated_marks,
                ]),
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'data'   => $grouped,
        ]);
    }

    /**
     * GET /student-app/results/report-cards/{sessionId}
     * Report card for a specific session.
     */
    public function reportCardBySession(Request $request, int $sessionId): JsonResponse
    {
        $student = $request->user();

        $finalMarks = FinalMark::with('course')
            ->where('student_id', $student->id)
            ->where('session_id', $sessionId)
            ->get();

        if ($finalMarks->isEmpty()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No results found for this session.',
            ], 404);
        }

        $totalObtained = $finalMarks->sum('final_marks');

        $status = StudentStatus::where('student_id', $student->id)
            ->where('is_current', true)
            ->with('schoolClass')
            ->first();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'student' => [
                    'name'         => $student->full_name,
                    'admission_no' => $student->admission_no,
                    'roll_no'      => $student->roll_no,
                    'class'        => $status?->schoolClass?->class_name,
                ],
                'session_id'     => $sessionId,
                'subjects'       => $finalMarks->map(fn($m) => [
                    'course_name'      => $m->course?->course_name,
                    'final_marks'      => $m->final_marks,
                    'calculated_marks' => $m->calculated_marks,
                    'note'             => $m->note,
                ]),
                'total_obtained' => $totalObtained,
                'subject_count'  => $finalMarks->count(),
            ],
        ]);
    }

    /**
     * GET /student-app/results/gradebook
     * Current session marks summary per subject.
     */
    public function gradebook(Request $request): JsonResponse
    {
        $studentId = $request->user()->id;
        $sessionId = SchoolSession::latest('id')
            ->where('school_id', $request->user()->school_id)
            ->value('id');

        $marks = Mark::with(['exam', 'course'])
            ->where('student_id', $studentId)
            ->when($sessionId, fn($q) => $q->where('session_id', $sessionId))
            ->get()
            ->groupBy('course_id')
            ->map(function ($subjectMarks, $courseId) {
                $course     = $subjectMarks->first()?->course;
                $totalMarks = $subjectMarks->sum('marks');
                return [
                    'course_id'    => $courseId,
                    'course_name'  => $course?->course_name,
                    'total_marks'  => $totalMarks,
                    'exams_count'  => $subjectMarks->count(),
                    'average'      => $subjectMarks->count() > 0
                        ? round($totalMarks / $subjectMarks->count(), 1) : 0,
                ];
            })->values();

        return response()->json([
            'status' => 'success',
            'data'   => $marks,
        ]);
    }
}
