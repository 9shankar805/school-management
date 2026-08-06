<?php

namespace App\Http\Controllers\Api\TeacherApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * GET /api/teacher-app/schedule/weekly
     */
    public function weekly(Request $request): JsonResponse
    {
        // Dummy weekly schedule data
        return response()->json([
            'schedule' => [
                'monday' => [
                    ['time' => '09:00', 'subject' => 'Math', 'class' => 'Grade 10 A'],
                    ['time' => '11:00', 'subject' => 'Science', 'class' => 'Grade 9 B'],
                ],
                'tuesday' => [
                    ['time' => '10:00', 'subject' => 'Math', 'class' => 'Grade 10 A'],
                ],
            ]
        ]);
    }

    /**
     * GET /api/teacher-app/schedule/daily/{date}
     */
    public function daily(Request $request, $date): JsonResponse
    {
        return response()->json([
            'date' => $date,
            'schedule' => [
                ['time' => '09:00', 'subject' => 'Math', 'class' => 'Grade 10 A', 'room' => '101'],
                ['time' => '11:00', 'subject' => 'Science', 'class' => 'Grade 9 B', 'room' => '102'],
            ]
        ]);
    }

    /**
     * GET /api/teacher-app/schedule/monthly/{month}
     */
    public function monthly(Request $request, $month): JsonResponse
    {
        // Month format e.g. "2026-08"
        return response()->json([
            'month' => $month,
            'events' => [
                ['date' => '2026-08-15', 'type' => 'exam', 'title' => 'Midterm Exams Begin'],
                ['date' => '2026-08-20', 'type' => 'holiday', 'title' => 'Public Holiday'],
            ]
        ]);
    }

    /**
     * GET /api/teacher-app/schedule/stats/{date}
     */
    public function stats(Request $request, $date): JsonResponse
    {
        return response()->json([
            'date' => $date,
            'stats' => [
                'total_classes_today' => 4,
                'total_hours' => 3.5,
                'free_periods' => 2
            ]
        ]);
    }
}
