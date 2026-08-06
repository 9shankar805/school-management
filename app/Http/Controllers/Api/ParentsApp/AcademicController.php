<?php

namespace App\Http\Controllers\Api\ParentsApp;

use App\Http\Controllers\Controller;
use App\Models\Mark;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AcademicController extends Controller
{
    /**
     * GET /api/grades
     */
    public function grades(Request $request): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');
        
        $marks = Mark::with(['course', 'exam', 'student'])
            ->whereIn('student_id', $childIds)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $marks,
        ]);
    }

    /**
     * GET /api/grades/child/{childId}
     */
    public function gradesByChild(Request $request, $childId): JsonResponse
    {
        // Verify child belongs to parent
        $request->user()->children()->findOrFail($childId);

        $marks = Mark::with(['course', 'exam'])
            ->where('student_id', $childId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $marks,
        ]);
    }

    /**
     * GET /api/grades/subject/{subjectId}
     */
    public function gradesBySubject(Request $request, $subjectId): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');

        $marks = Mark::with(['student', 'exam'])
            ->whereIn('student_id', $childIds)
            ->where('course_id', $subjectId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $marks,
        ]);
    }

    /**
     * GET /api/gpa/child/{childId}
     */
    public function gpaByChild(Request $request, $childId): JsonResponse
    {
        // Verify child belongs to parent
        $request->user()->children()->findOrFail($childId);

        $marks = Mark::where('student_id', $childId)->get();
        
        if ($marks->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'data'   => [
                    'child_id' => $childId,
                    'gpa'      => 0,
                    'scale'    => 4.0
                ]
            ]);
        }

        // Simple average calculation based on marks percentage (assuming marks out of 100)
        $averagePercentage = $marks->avg('marks');
        $gpa = ($averagePercentage / 100) * 4.0; // Dynamic rough GPA estimate

        return response()->json([
            'status' => 'success',
            'data'   => [
                'child_id'   => $childId,
                'gpa'        => round($gpa, 2),
                'percentage' => round($averagePercentage, 2),
                'scale'      => 4.0
            ]
        ]);
    }

    /**
     * GET /api/report-cards
     */
    public function reportCards(Request $request): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');
        
        // Dynamic report cards generated from Marks
        $reportCards = Mark::with(['course', 'exam', 'student'])
            ->whereIn('student_id', $childIds)
            ->get()
            ->groupBy(['student_id', 'exam_id'])
            ->map(function ($exams, $studentId) {
                return $exams->map(function ($marks, $examId) use ($studentId) {
                    $exam = $marks->first()->exam;
                    return [
                        'id'         => "rc_{$studentId}_{$examId}", // Synthetic ID
                        'student_id' => $studentId,
                        'exam'       => $exam,
                        'term'       => $exam ? $exam->name : 'N/A',
                        'marks'      => $marks,
                        'average'    => round($marks->avg('marks'), 2),
                    ];
                });
            })
            ->flatten(1)
            ->values();

        return response()->json([
            'status' => 'success',
            'data'   => $reportCards,
        ]);
    }

    /**
     * GET /api/report-cards/child/{childId}
     */
    public function reportCardsByChild(Request $request, $childId): JsonResponse
    {
        $request->user()->children()->findOrFail($childId);

        $reportCards = Mark::with(['course', 'exam'])
            ->where('student_id', $childId)
            ->get()
            ->groupBy('exam_id')
            ->map(function ($marks, $examId) use ($childId) {
                $exam = $marks->first()->exam;
                return [
                    'id'         => "rc_{$childId}_{$examId}",
                    'student_id' => $childId,
                    'exam'       => $exam,
                    'term'       => $exam ? $exam->name : 'N/A',
                    'marks'      => $marks,
                    'average'    => round($marks->avg('marks'), 2),
                ];
            })
            ->values();

        return response()->json([
            'status' => 'success',
            'data'   => $reportCards,
        ]);
    }

    /**
     * GET /api/report-cards/{id}
     */
    public function reportCardShow(Request $request, $id): JsonResponse
    {
        // Parse synthetic ID: rc_{studentId}_{examId}
        $parts = explode('_', $id);
        if (count($parts) !== 3) {
            return response()->json(['status' => 'error', 'message' => 'Invalid report card ID'], 400);
        }

        $childId = $parts[1];
        $examId = $parts[2];

        // Verify child
        $request->user()->children()->findOrFail($childId);

        $marks = Mark::with(['course', 'exam'])
            ->where('student_id', $childId)
            ->where('exam_id', $examId)
            ->get();

        if ($marks->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'Report card not found'], 404);
        }

        $exam = $marks->first()->exam;

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'         => $id,
                'student_id' => $childId,
                'exam'       => $exam,
                'term'       => $exam ? $exam->name : 'N/A',
                'marks'      => $marks,
                'average'    => round($marks->avg('marks'), 2),
            ]
        ]);
    }

    /**
     * GET /api/report-cards/term/{term}
     */
    public function reportCardsByTerm(Request $request, $term): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');
        
        // We find exams that match the term name loosely
        $reportCards = Mark::with(['course', 'exam', 'student'])
            ->whereIn('student_id', $childIds)
            ->whereHas('exam', function($q) use ($term) {
                $q->where('name', 'LIKE', "%{$term}%");
            })
            ->get()
            ->groupBy(['student_id', 'exam_id'])
            ->map(function ($exams, $studentId) {
                return $exams->map(function ($marks, $examId) use ($studentId) {
                    $exam = $marks->first()->exam;
                    return [
                        'id'         => "rc_{$studentId}_{$examId}",
                        'student_id' => $studentId,
                        'exam'       => $exam,
                        'term'       => $exam ? $exam->name : 'N/A',
                        'marks'      => $marks,
                        'average'    => round($marks->avg('marks'), 2),
                    ];
                });
            })
            ->flatten(1)
            ->values();

        return response()->json([
            'status' => 'success',
            'data'   => $reportCards,
        ]);
    }

    /**
     * GET /api/subjects
     */
    public function subjects(Request $request): JsonResponse
    {
        // Get all courses active for the parent's children
        $childIds = $request->user()->children()->pluck('users.id');
        
        // Find course IDs from Marks (or Attendance) to ensure they are relevant subjects
        $courseIds = Mark::whereIn('student_id', $childIds)->distinct()->pluck('course_id');
        
        $subjects = Course::whereIn('id', $courseIds)->get();

        return response()->json([
            'status' => 'success',
            'data'   => $subjects,
        ]);
    }

    /**
     * GET /api/subjects/{id}
     */
    public function subjectShow(Request $request, $id): JsonResponse
    {
        // For security, just ensure the course exists.
        $subject = Course::findOrFail($id);
        
        return response()->json([
            'status' => 'success',
            'data'   => collect($subject)->merge(['teacher_info' => 'TODO: Implement Teacher Details']),
        ]);
    }
}
