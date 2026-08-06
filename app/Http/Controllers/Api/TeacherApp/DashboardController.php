<?php

namespace App\Http\Controllers\Api\TeacherApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * GET /api/teacher-app/dashboard/stats
     */
    public function stats(Request $request): JsonResponse
    {
        // TODO: Replace with real dynamic stats
        return response()->json([
            'stats' => [
                'total_classes'       => 5,
                'total_students'      => 120,
                'pending_assignments' => 12,
                'unread_messages'     => 4,
            ]
        ]);
    }

    /**
     * GET /api/teacher-app/dashboard/next-class
     */
    public function nextClass(Request $request): JsonResponse
    {
        // Dummy data
        return response()->json([
            'next_class' => [
                'id'         => 1,
                'subject'    => 'Mathematics',
                'class_name' => 'Grade 10 - Section A',
                'time'       => '10:00 AM - 10:45 AM',
                'room'       => 'Room 101',
            ]
        ]);
    }

    /**
     * GET /api/teacher-app/dashboard/timeline
     */
    public function timeline(Request $request): JsonResponse
    {
        // Dummy data
        return response()->json([
            'timeline' => [
                [
                    'time'        => '09:00 AM',
                    'title'       => 'Science',
                    'description' => 'Grade 9 - Section B',
                    'type'        => 'class'
                ],
                [
                    'time'        => '10:00 AM',
                    'title'       => 'Mathematics',
                    'description' => 'Grade 10 - Section A',
                    'type'        => 'class'
                ],
                [
                    'time'        => '11:00 AM',
                    'title'       => 'Staff Meeting',
                    'description' => 'Conference Room',
                    'type'        => 'meeting'
                ]
            ]
        ]);
    }

    /**
     * GET /api/teacher-app/dashboard/pending-assignments
     */
    public function pendingAssignments(Request $request): JsonResponse
    {
        // Dummy data
        return response()->json([
            'pending_assignments' => [
                [
                    'id'          => 1,
                    'title'       => 'Algebra Worksheet',
                    'class_name'  => 'Grade 10 - Section A',
                    'submissions' => 25,
                    'total'       => 30,
                    'due_date'    => 'Today'
                ]
            ]
        ]);
    }

    /**
     * GET /api/teacher-app/dashboard/upcoming-exam
     */
    public function upcomingExam(Request $request): JsonResponse
    {
        return response()->json([
            'upcoming_exam' => [
                'id'         => 1,
                'title'      => 'Midterm Mathematics',
                'class_name' => 'Grade 10',
                'date'       => '2026-08-15',
                'time'       => '09:00 AM - 11:00 AM'
            ]
        ]);
    }

    /**
     * GET /api/teacher-app/dashboard/low-attendance
     */
    public function lowAttendance(Request $request): JsonResponse
    {
        return response()->json([
            'low_attendance' => [
                [
                    'student_id'   => 1,
                    'student_name' => 'John Doe',
                    'class_name'   => 'Grade 10 - Section A',
                    'attendance'   => '65%'
                ]
            ]
        ]);
    }

    /**
     * GET /api/teacher-app/teaching-tips
     */
    public function teachingTips(Request $request): JsonResponse
    {
        $tips = [
            "Start your class with a brief review of the previous lesson.",
            "Encourage student participation by asking open-ended questions.",
            "Take short breaks during long lectures to maintain student focus."
        ];

        return response()->json([
            'tip' => $tips[array_rand($tips)]
        ]);
    }
}
