<?php

namespace App\Http\Controllers\Api\ParentsApp;

use App\Http\Controllers\Controller;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\StudentStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    /**
     * GET /api/assignments
     */
    public function index(Request $request): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');
        
        $classIds = StudentStatus::whereIn('student_id', $childIds)
            ->where('is_current', true)
            ->pluck('class_id');
            
        $assignments = Homework::with(['course', 'teacher', 'submissions' => function($q) use ($childIds) {
            $q->whereIn('student_id', $childIds);
        }])
        ->whereIn('class_id', $classIds)
        ->orderBy('due_date', 'asc')
        ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $assignments,
        ]);
    }

    /**
     * GET /api/assignments/pending
     */
    public function pending(Request $request): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');
        
        $classIds = StudentStatus::whereIn('student_id', $childIds)
            ->where('is_current', true)
            ->pluck('class_id');

        // Pending = no submission exists for the child
        $assignments = Homework::with(['course', 'teacher'])
            ->whereIn('class_id', $classIds)
            ->whereDoesntHave('submissions', function($q) use ($childIds) {
                $q->whereIn('student_id', $childIds);
            })
            ->orderBy('due_date', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $assignments,
        ]);
    }

    /**
     * GET /api/assignments/completed
     */
    public function completed(Request $request): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');
        
        $classIds = StudentStatus::whereIn('student_id', $childIds)
            ->where('is_current', true)
            ->pluck('class_id');

        // Completed = submission exists for the child
        $assignments = Homework::with(['course', 'teacher', 'submissions' => function($q) use ($childIds) {
                $q->whereIn('student_id', $childIds);
            }])
            ->whereIn('class_id', $classIds)
            ->whereHas('submissions', function($q) use ($childIds) {
                $q->whereIn('student_id', $childIds);
            })
            ->orderBy('due_date', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $assignments,
        ]);
    }

    /**
     * GET /api/assignments/child/{childId}
     */
    public function byChild(Request $request, $childId): JsonResponse
    {
        $request->user()->children()->findOrFail($childId);

        $status = StudentStatus::where('student_id', $childId)
            ->where('is_current', true)
            ->first();

        if (!$status) {
            return response()->json(['status' => 'success', 'data' => []]);
        }

        $assignments = Homework::with(['course', 'teacher', 'submissions' => function($q) use ($childId) {
            $q->where('student_id', $childId);
        }])
        ->where('class_id', $status->class_id)
        ->orderBy('due_date', 'asc')
        ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $assignments,
        ]);
    }

    /**
     * GET /api/assignments/subject/{subjectId}
     */
    public function bySubject(Request $request, $subjectId): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');
        
        $classIds = StudentStatus::whereIn('student_id', $childIds)
            ->where('is_current', true)
            ->pluck('class_id');

        $assignments = Homework::with(['course', 'teacher', 'submissions' => function($q) use ($childIds) {
            $q->whereIn('student_id', $childIds);
        }])
        ->whereIn('class_id', $classIds)
        ->where('course_id', $subjectId)
        ->orderBy('due_date', 'asc')
        ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $assignments,
        ]);
    }

    /**
     * GET /api/assignments/{id}
     */
    public function show(Request $request, $id): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');
        
        $classIds = StudentStatus::whereIn('student_id', $childIds)
            ->where('is_current', true)
            ->pluck('class_id');

        $assignment = Homework::with(['course', 'teacher', 'submissions' => function($q) use ($childIds) {
            $q->whereIn('student_id', $childIds);
        }])
        ->whereIn('class_id', $classIds)
        ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $assignment,
        ]);
    }

    /**
     * PUT /api/assignments/{id}/status
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'child_id' => 'required|integer',
            'status'   => 'required|in:completed',
        ]);

        $childId = $validated['child_id'];
        
        // Verify child
        $request->user()->children()->findOrFail($childId);

        $status = StudentStatus::where('student_id', $childId)
            ->where('is_current', true)
            ->firstOrFail();

        // Verify assignment belongs to child's class
        $assignment = Homework::where('class_id', $status->class_id)->findOrFail($id);

        // Mark as completed by creating a submission
        $submission = HomeworkSubmission::firstOrCreate(
            ['homework_id' => $assignment->id, 'student_id' => $childId],
            [
                'school_id'    => $assignment->school_id,
                'status'       => 'submitted',
                'submitted_at' => now(),
            ]
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Assignment marked as completed',
            'data'    => $submission,
        ]);
    }
}
