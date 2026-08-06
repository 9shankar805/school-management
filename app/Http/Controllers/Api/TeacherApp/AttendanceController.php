<?php

namespace App\Http\Controllers\Api\TeacherApp;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * GET /api/teacher-app/attendance/classes
     */
    public function classes(Request $request): JsonResponse
    {
        // For now, return all classes, but in reality filter by teacher assigned classes
        $classes = SchoolClass::with('sections')->get();
        return response()->json(['classes' => $classes]);
    }

    /**
     * GET /api/teacher-app/attendance/{classId}/students
     */
    public function students(Request $request, $classId): JsonResponse
    {
        // Get students in this class
        $students = User::role('student')
            ->whereHas('academic_info', function($q) use ($classId) {
                $q->where('class_id', $classId);
            })
            ->select('id', 'first_name', 'last_name', 'photo', 'gender')
            ->get();

        return response()->json(['students' => $students]);
    }

    /**
     * GET /api/teacher-app/attendance/{classId}/date/{date}
     */
    public function attendanceByDate(Request $request, $classId, $date): JsonResponse
    {
        // Dummy data for existing attendance
        $attendance = [
            ['student_id' => 1, 'status' => 'present', 'remarks' => ''],
            ['student_id' => 2, 'status' => 'absent', 'remarks' => 'Sick leave'],
            ['student_id' => 3, 'status' => 'late', 'remarks' => 'Traffic'],
        ];

        return response()->json(['attendance' => $attendance, 'date' => $date]);
    }

    /**
     * POST /api/teacher-app/attendance/{classId}
     */
    public function store(Request $request, $classId): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.student_id' => 'required|integer',
            'attendance.*.status' => 'required|in:present,absent,late,excused',
        ]);

        // Process saving attendance logic here

        return response()->json([
            'status' => 'success',
            'message' => 'Attendance saved successfully.',
        ]);
    }

    /**
     * GET /api/teacher-app/attendance/{studentId}/history
     */
    public function history(Request $request, $studentId): JsonResponse
    {
        // Dummy data
        $history = [
            'summary' => [
                'total_days' => 100,
                'present' => 90,
                'absent' => 5,
                'late' => 5,
            ],
            'records' => [
                ['date' => '2026-08-01', 'status' => 'present'],
                ['date' => '2026-08-02', 'status' => 'absent'],
                ['date' => '2026-08-03', 'status' => 'late'],
            ]
        ];

        return response()->json(['history' => $history]);
    }
}
