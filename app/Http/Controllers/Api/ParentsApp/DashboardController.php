<?php

namespace App\Http\Controllers\Api\ParentsApp;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Exam;
use App\Models\Homework;
use App\Models\Invoice;
use App\Models\Routine;
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
            ->whereDate('created_at', now()->toDateString())
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

    /**
     * GET /api/dashboard/quick-stats
     */
    public function quickStats(Request $request): JsonResponse
    {
        return $this->index($request);
    }

    /**
     * GET /api/dashboard/recent-activity
     */
    public function recentActivity(Request $request): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');

        $recentAttendance = Attendance::whereIn('student_id', $childIds)
            ->latest('created_at')
            ->take(5)
            ->get()
            ->map(function ($att) {
                return [
                    'type'        => 'attendance',
                    'title'       => 'Attendance marked: ' . $att->status,
                    'student_id'  => $att->student_id,
                    'created_at'  => $att->created_at,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data'   => $recentAttendance,
        ]);
    }

    /**
     * GET /api/dashboard/today-schedule
     */
    public function todaySchedule(Request $request): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');

        $classIds = StudentStatus::whereIn('student_id', $childIds)
            ->where('is_current', true)
            ->pluck('class_id');

        $todayWeekday = now()->isoWeekday();

        $schedule = Routine::whereIn('class_id', $classIds)
            ->where('weekday', $todayWeekday)
            ->with(['course', 'section', 'schoolClass'])
            ->orderBy('start')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $schedule,
        ]);
    }

    /**
     * GET /api/dashboard/upcoming-exams
     */
    public function upcomingExams(Request $request): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');

        $classIds = StudentStatus::whereIn('student_id', $childIds)
            ->where('is_current', true)
            ->pluck('class_id');

        $exams = Exam::whereIn('class_id', $classIds)
            ->where('start_date', '>=', now()->toDateString())
            ->with('course')
            ->orderBy('start_date')
            ->take(10)
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $exams,
        ]);
    }

    /**
     * GET /api/dashboard/bus-status
     */
    public function busStatus(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data'   => [
                'status'        => 'on_time',
                'current_stop'  => 'School Terminal',
                'next_stop'     => 'Route Stop 1',
                'eta_minutes'   => 12,
            ],
        ]);
    }
}
