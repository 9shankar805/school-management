<?php

namespace App\Http\Controllers\Api\StudentApp;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Exam;
use App\Models\Homework;
use App\Models\Invoice;
use App\Models\Routine;
use App\Models\StudentStatus;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class DashboardController extends Controller
{
    /**
     * GET /student-app/dashboard
     */
    public function index(Request $request): JsonResponse
    {
        $student   = $request->user();
        $studentId = $student->id;

        $status  = StudentStatus::where('student_id', $studentId)
            ->where('is_current', true)
            ->with('schoolClass')
            ->first();

        $classId = $status?->class_id;

        // 1. Attendance this month
        $monthStart  = Carbon::now()->startOfMonth()->toDateString();
        $monthEnd    = Carbon::now()->endOfMonth()->toDateString();
        $attRecords  = Attendance::where('student_id', $studentId)
            ->whereBetween('date', [$monthStart, $monthEnd])->get();
        $totalDays   = $attRecords->count();
        $presentDays = $attRecords->where('status', 'on')->count();

        // 2. Upcoming exams (next 7 days)
        $upcomingExams = $classId
            ? Exam::where('class_id', $classId)
                ->whereBetween('start_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
                ->orderBy('start_date')->take(5)->get()
                ->map(fn($e) => ['id' => $e->id, 'name' => $e->exam_name, 'start_date' => $e->start_date])
            : collect();

        // 3. Pending homework
        $pendingHomework = $classId
            ? Homework::where('class_id', $classId)
                ->whereDoesntHave('submissions', fn($q) => $q->where('student_id', $studentId))
                ->where('due_date', '>=', now()->toDateString())->count()
            : 0;

        // 4. Overdue fees (total_amount is the actual column)
        $overdueFees = Invoice::where('student_id', $studentId)
            ->where('status', 'unpaid')
            ->where('due_date', '<', now()->toDateString())
            ->sum('total_amount');

        // 5. Today's schedule
        $todayWeekday  = Carbon::now()->isoWeekday();
        $todaySchedule = $classId
            ? Routine::where('class_id', $classId)->where('weekday', $todayWeekday)
                ->with('course')->orderBy('start')->get()
                ->map(fn($r) => [
                    'subject' => $r->course?->course_name,
                    'start'   => $r->start,
                    'end'     => $r->end,
                    'room'    => $r->room,
                ])
            : collect();

        // 6. Unread notifications
        $unreadNotifications = DatabaseNotification::where('notifiable_id', $studentId)
            ->where('notifiable_type', get_class($student))
            ->whereNull('read_at')->count();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'student' => [
                    'name'         => $student->full_name,
                    'class'        => $status?->schoolClass?->class_name,
                    'admission_no' => $student->admission_no,
                    'roll_no'      => $student->roll_no,
                    'avatar'       => $student->avatar,
                ],
                'attendance_this_month' => [
                    'total'      => $totalDays,
                    'present'    => $presentDays,
                    'absent'     => $totalDays - $presentDays,
                    'percentage' => $totalDays > 0 ? round($presentDays / $totalDays * 100, 1) : 0,
                ],
                'upcoming_exams_count'   => $upcomingExams->count(),
                'upcoming_exams'         => $upcomingExams,
                'pending_homework_count' => $pendingHomework,
                'overdue_fees_total'     => (float) $overdueFees,
                'unread_notifications'   => $unreadNotifications,
                'today_schedule'         => $todaySchedule,
            ],
        ]);
    }

    /**
     * GET /student-app/dashboard/quick-stats
     */
    public function quickStats(Request $request): JsonResponse
    {
        $student   = $request->user();
        $studentId = $student->id;

        $totalAtt   = Attendance::where('student_id', $studentId)->count();
        $presentAtt = Attendance::where('student_id', $studentId)->where('status', 'on')->count();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'attendance_percentage' => $totalAtt > 0 ? round($presentAtt / $totalAtt * 100, 1) : 0,
                'pending_fees'          => (float) Invoice::where('student_id', $studentId)
                    ->where('status', 'unpaid')->sum('total_amount'),
                'unread_notifications'  => DatabaseNotification::where('notifiable_id', $studentId)
                    ->where('notifiable_type', get_class($student))->whereNull('read_at')->count(),
            ],
        ]);
    }

    /**
     * GET /student-app/dashboard/today-schedule
     */
    public function todaySchedule(Request $request): JsonResponse
    {
        $classId      = StudentStatus::where('student_id', $request->user()->id)
            ->where('is_current', true)->value('class_id');
        $todayWeekday = Carbon::now()->isoWeekday();

        $slots = $classId
            ? Routine::where('class_id', $classId)->where('weekday', $todayWeekday)
                ->with('course')->orderBy('start')->get()
                ->map(fn($r) => [
                    'subject' => $r->course?->course_name,
                    'start'   => $r->start,
                    'end'     => $r->end,
                    'room'    => $r->room,
                ])
            : collect();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'day'   => Carbon::now()->format('l'),
                'date'  => Carbon::now()->toDateString(),
                'slots' => $slots,
            ],
        ]);
    }

    /**
     * GET /student-app/dashboard/upcoming-exams
     */
    public function upcomingExams(Request $request): JsonResponse
    {
        $classId = StudentStatus::where('student_id', $request->user()->id)
            ->where('is_current', true)->value('class_id');

        $exams = $classId
            ? Exam::where('class_id', $classId)
                ->where('start_date', '>=', now()->toDateString())
                ->orderBy('start_date')->take(10)->get()
                ->map(fn($e) => ['id' => $e->id, 'name' => $e->exam_name, 'start_date' => $e->start_date, 'end_date' => $e->end_date])
            : collect();

        return response()->json([
            'status' => 'success',
            'data'   => $exams,
        ]);
    }
}
