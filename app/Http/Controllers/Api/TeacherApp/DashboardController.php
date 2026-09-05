<?php

namespace App\Http\Controllers\Api\TeacherApp;

use App\Http\Controllers\Controller;
use App\Models\AssignedTeacher;
use App\Models\Attendance;
use App\Models\Assignment;
use App\Models\Exam;
use App\Models\Mark;
use App\Models\Routine;
use App\Models\SchoolSession;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * GET /api/teacher-app/dashboard/stats
     * Real-time stats for the authenticated teacher.
     */
    public function stats(Request $request): JsonResponse
    {
        $teacher   = $request->user();
        $sessionId = $this->getCurrentSessionId();

        // Courses assigned to this teacher this session
        $assignedCourseIds = AssignedTeacher::where('teacher_id', $teacher->id)
            ->pluck('course_id');

        $totalClasses = $assignedCourseIds->count();

        // Total unique students across all assigned courses
        $totalStudents = \App\Models\Promotion::where('session_id', $sessionId)
            ->whereHas('schoolClass', function ($q) use ($teacher, $sessionId) {
                $q->whereHas('assignedTeachers', fn($aq) => $aq->where('teacher_id', $teacher->id));
            })
            ->distinct('student_id')
            ->count('student_id');

        // Pending (ungraded) assignments from this teacher's courses
        $pendingAssignments = Assignment::whereIn('course_id', $assignedCourseIds)
            ->where('session_id', $sessionId)
            ->where('status', 'active')
            ->whereHas('submissions', fn($q) => $q->whereNull('grade'))
            ->count();

        // Unread messages (parent-teacher chat)
        $unreadMessages = \App\Models\ParentTeacherMessage::where('receiver_id', $teacher->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'status' => 'success',
            'stats'  => [
                'total_classes'       => $totalClasses,
                'total_students'      => $totalStudents,
                'pending_assignments' => $pendingAssignments,
                'unread_messages'     => $unreadMessages,
            ],
        ]);
    }

    /**
     * GET /api/teacher-app/dashboard/next-class
     * The next scheduled class for the teacher today.
     */
    public function nextClass(Request $request): JsonResponse
    {
        $teacher   = $request->user();
        $sessionId = $this->getCurrentSessionId();
        $weekday   = now()->isoWeekday(); // 1=Mon..7=Sun
        $now       = now()->format('H:i:s');

        $nextSlot = Routine::where('teacher_id', $teacher->id)
            ->where('session_id', $sessionId)
            ->where('weekday', $weekday)
            ->where('start', '>=', $now)
            ->with('course', 'schoolClass', 'section')
            ->orderBy('start')
            ->first();

        if (! $nextSlot) {
            return response()->json(['status' => 'success', 'next_class' => null]);
        }

        return response()->json([
            'status'     => 'success',
            'next_class' => [
                'id'         => $nextSlot->id,
                'subject'    => $nextSlot->course?->course_name ?? '—',
                'class_name' => ($nextSlot->schoolClass?->class_name ?? '') .
                                ' - ' . ($nextSlot->section?->section_name ?? ''),
                'time'       => Carbon::parse($nextSlot->start)->format('h:i A') .
                                ' – ' . Carbon::parse($nextSlot->end)->format('h:i A'),
                'room'       => $nextSlot->room ?? '—',
            ],
        ]);
    }

    /**
     * GET /api/teacher-app/dashboard/timeline
     * Full today timetable for the teacher.
     */
    public function timeline(Request $request): JsonResponse
    {
        $teacher   = $request->user();
        $sessionId = $this->getCurrentSessionId();
        $weekday   = now()->isoWeekday();

        $slots = Routine::where('teacher_id', $teacher->id)
            ->where('session_id', $sessionId)
            ->where('weekday', $weekday)
            ->with('course', 'schoolClass', 'section')
            ->orderBy('start')
            ->get();

        $timeline = $slots->map(fn($slot) => [
            'time'        => Carbon::parse($slot->start)->format('h:i A'),
            'end_time'    => Carbon::parse($slot->end)->format('h:i A'),
            'title'       => $slot->course?->course_name ?? '—',
            'description' => ($slot->schoolClass?->class_name ?? '') . ' - ' . ($slot->section?->section_name ?? ''),
            'room'        => $slot->room ?? '—',
            'type'        => 'class',
        ]);

        return response()->json(['status' => 'success', 'timeline' => $timeline]);
    }

    /**
     * GET /api/teacher-app/dashboard/pending-assignments
     * Assignments from this teacher with ungraded submissions.
     */
    public function pendingAssignments(Request $request): JsonResponse
    {
        $teacher   = $request->user();
        $sessionId = $this->getCurrentSessionId();

        $courseIds = AssignedTeacher::where('teacher_id', $teacher->id)->pluck('course_id');

        $assignments = Assignment::whereIn('course_id', $courseIds)
            ->where('session_id', $sessionId)
            ->where('status', 'active')
            ->with(['course', 'schoolClass'])
            ->withCount([
                'submissions',
                'submissions as ungraded_count' => fn($q) => $q->whereNull('grade'),
            ])
            ->orderBy('due_date')
            ->take(10)
            ->get();

        $data = $assignments->map(fn($a) => [
            'id'          => $a->id,
            'title'       => $a->title,
            'class_name'  => $a->schoolClass?->class_name ?? '—',
            'subject'     => $a->course?->course_name ?? '—',
            'submissions' => $a->submissions_count,
            'ungraded'    => $a->ungraded_count,
            'due_date'    => $a->due_date?->format('d M Y') ?? '—',
            'is_overdue'  => $a->due_date?->isPast() ?? false,
        ]);

        return response()->json(['status' => 'success', 'pending_assignments' => $data]);
    }

    /**
     * GET /api/teacher-app/dashboard/upcoming-exam
     * Next upcoming exam for this teacher's courses.
     */
    public function upcomingExam(Request $request): JsonResponse
    {
        $teacher   = $request->user();
        $sessionId = $this->getCurrentSessionId();
        $today     = now()->toDateString();

        $courseIds = AssignedTeacher::where('teacher_id', $teacher->id)->pluck('course_id');

        $exam = Exam::whereIn('course_id', $courseIds)
            ->where('session_id', $sessionId)
            ->where('start_date', '>=', $today)
            ->with('course', 'schoolClass')
            ->orderBy('start_date')
            ->first();

        if (! $exam) {
            return response()->json(['status' => 'success', 'upcoming_exam' => null]);
        }

        return response()->json([
            'status' => 'success',
            'upcoming_exam' => [
                'id'         => $exam->id,
                'title'      => $exam->exam_name,
                'class_name' => $exam->schoolClass?->class_name ?? '—',
                'subject'    => $exam->course?->course_name ?? '—',
                'date_ad'    => Carbon::parse($exam->start_date)->format('d M Y'),
                'date_bs'    => \App\Services\NepaliDateService::formatBS(
                                    Carbon::parse($exam->start_date)->format('Y-m-d'), false, 'long'),
                'time'       => Carbon::parse($exam->start_date)->format('h:i A') .
                                ' – ' . Carbon::parse($exam->end_date)->format('h:i A'),
            ],
        ]);
    }

    /**
     * GET /api/teacher-app/dashboard/low-attendance
     * Students in the teacher's classes with attendance below 75%.
     */
    public function lowAttendance(Request $request): JsonResponse
    {
        $teacher   = $request->user();
        $sessionId = $this->getCurrentSessionId();

        $courseIds = AssignedTeacher::where('teacher_id', $teacher->id)->pluck('course_id');

        // Aggregate per student across teacher's courses
        $rows = Attendance::whereIn('course_id', $courseIds)
            ->where('session_id', $sessionId)
            ->select(
                'student_id',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(status='on') as present")
            )
            ->groupBy('student_id')
            ->havingRaw("(SUM(status='on') / COUNT(*)) * 100 < 75")
            ->with('student:id,first_name,last_name')
            ->orderByRaw("(SUM(status='on') / COUNT(*)) ASC")
            ->take(20)
            ->get();

        $data = $rows->map(fn($r) => [
            'student_id'   => $r->student_id,
            'student_name' => $r->student?->full_name ?? 'Unknown',
            'total'        => $r->total,
            'present'      => (int) $r->present,
            'attendance'   => $r->total > 0 ? round(($r->present / $r->total) * 100, 1) . '%' : '0%',
        ]);

        return response()->json(['status' => 'success', 'low_attendance' => $data]);
    }

    /**
     * GET /api/teacher-app/teaching-tips
     * Random teaching tip (static — no DB needed).
     */
    public function teachingTips(Request $request): JsonResponse
    {
        $tips = [
            "Start your class with a brief review of the previous lesson.",
            "Encourage student participation by asking open-ended questions.",
            "Take short breaks during long lectures to maintain student focus.",
            "Use real-life examples to explain abstract concepts.",
            "Provide timely feedback on assignments so students can improve.",
            "Check for understanding before moving to the next topic.",
            "Vary your teaching methods — visuals, discussion, and practice all help.",
        ];

        return response()->json(['status' => 'success', 'tip' => $tips[array_rand($tips)]]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function getCurrentSessionId(): int
    {
        return (int) (\App\Models\SchoolSession::where('is_current', true)->value('id')
            ?? \App\Models\SchoolSession::latest()->value('id')
            ?? 0);
    }
}
