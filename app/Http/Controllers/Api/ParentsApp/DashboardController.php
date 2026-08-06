<?php

namespace App\Http\Controllers\Api\ParentsApp;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Exam;
use App\Models\Homework;
use App\Models\Invoice;
use App\Models\StudentStatus;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * GET /api/dashboard
     */
    public function index(Request $request): JsonResponse
    {
        $parentId = $request->user()->id;
        $childIds = $request->user()->children()->pluck('users.id');
        
        $classIds = StudentStatus::whereIn('student_id', $childIds)
            ->where('is_current', true)
            ->pluck('class_id');

        // 1. Upcoming Exams (next 7 days)
        $upcomingExamsCount = Exam::whereIn('class_id', $classIds)
            ->whereBetween('start_date', [now(), now()->addDays(7)])
            ->count();

        // 2. Pending Assignments Count
        $pendingAssignmentsCount = Homework::whereIn('class_id', $classIds)
            ->whereDoesntHave('submissions', function($q) use ($childIds) {
                $q->whereIn('student_id', $childIds);
            })
            ->count();

        // 3. Unread Messages Count
        $unreadMessagesCount = Conversation::where('parent_id', $parentId)
            ->withCount(['messages as unread' => function($q) use ($parentId) {
                $q->where('sender_id', '!=', $parentId)->whereNull('read_at');
            }])
            ->get()
            ->sum('unread');

        // 4. Overdue Fees Sum
        $overdueFees = Invoice::whereIn('student_id', $childIds)
            ->where('status', 'unpaid')
            ->where('due_date', '<', now())
            ->sum('amount');

        // 5. Today's Attendance Overview
        $attendanceToday = Attendance::whereIn('student_id', $childIds)
            ->whereDate('attendance_date', now()->toDateString())
            ->get();

        $attendanceSummary = $childIds->map(function($childId) use ($attendanceToday) {
            $record = $attendanceToday->firstWhere('student_id', $childId);
            return [
                'student_id' => $childId,
                'status'     => $record ? $record->status : 'pending',
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => [
                'upcoming_exams_count'      => $upcomingExamsCount,
                'pending_assignments_count' => $pendingAssignmentsCount,
                'unread_messages_count'     => $unreadMessagesCount,
                'overdue_fees_total'        => $overdueFees,
                'attendance_today'          => $attendanceSummary,
            ],
        ]);
    }
}
