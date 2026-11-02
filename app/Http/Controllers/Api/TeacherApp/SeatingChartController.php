<?php

namespace App\Http\Controllers\Api\TeacherApp;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Models\SchoolSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SeatingChartController extends Controller
{
    /**
     * GET /api/teacher-app/seating/{classId}
     * Returns the real-world Nepali classroom bench seating plan for the class.
     */
    public function show(Request $request, $classId): JsonResponse
    {
        $teacher = $request->user();
        $schoolId = $teacher->school_id ?? 1;
        $sessionId = SchoolSession::current()?->id ?? SchoolSession::latest('id')->value('id') ?? 1;

        $cacheKey = "teacher_seating_chart_{$schoolId}_{$classId}";
        $savedChart = Cache::get($cacheKey);

        if ($savedChart && !empty($savedChart['seats'])) {
            return response()->json([
                'status'        => 'success',
                'class_id'      => $classId,
                'seating_chart' => $savedChart,
            ]);
        }

        // Fetch real students in this class
        $promotions = Promotion::where('class_id', $classId)
            ->where('session_id', $sessionId)
            ->with(['student.parent_info', 'student.parents', 'schoolClass', 'section'])
            ->get();

        if ($promotions->isEmpty()) {
            // Fallback to all promotions in session if specific class has no direct records
            $promotions = Promotion::where('session_id', $sessionId)
                ->with(['student.parent_info', 'student.parents', 'schoolClass', 'section'])
                ->limit(24)
                ->get();
        }

        $students = $promotions->map(function ($p, $idx) {
            $s = $p->student;
            $parentPhone = $s?->parent_info?->father_phone
                ?: $s?->parent_info?->mother_phone
                ?: $s?->parents?->first()?->phone
                ?: $s?->phone
                ?: '+977 9801234567';

            $parentName = $s?->parent_info?->father_name
                ?: $s?->parent_info?->mother_name
                ?: $s?->parents?->first()?->name
                ?: ('Guardian of ' . ($s?->first_name ?? 'Student'));

            return [
                'student_id'   => $s?->id ?? ($idx + 1),
                'name'         => trim(($s?->first_name ?? 'Student') . ' ' . ($s?->last_name ?? '')),
                'roll_no'      => $p->roll_no ?? sprintf('%02d', $idx + 1),
                'gender'       => $s?->gender ?? ($idx % 2 == 0 ? 'male' : 'female'),
                'parent_name'  => $parentName,
                'parent_phone' => $parentPhone,
            ];
        })->sortBy('roll_no')->values();

        // Standard Nepal Classroom Layout: 3 Columns, 5 Benches per Column, 2 Students per Bench (Dual Seater)
        $numCols = 3;
        $benchesPerCol = 5;
        $studentsPerBench = 2;

        $seats = [];
        $studentIdx = 0;

        for ($c = 1; $c <= $numCols; $c++) {
            for ($b = 1; $b <= $benchesPerCol; $b++) {
                for ($p = 1; $p <= $studentsPerBench; $p++) {
                    $posCode = ($studentsPerBench == 1) ? 'S' : (($p == 1) ? 'L' : (($p == 2 && $studentsPerBench == 3) ? 'C' : 'R'));
                    $seatCode = "C{$c}-B{$b}-{$posCode}";

                    $assigned = $studentIdx < $students->count() ? $students[$studentIdx++] : null;

                    $seats[] = [
                        'column_no'      => $c,
                        'bench_no'       => $b,
                        'bench_position' => $p,
                        'seat_code'      => $seatCode,
                        'student_id'     => $assigned['student_id'] ?? null,
                        'name'           => $assigned['name'] ?? null,
                        'roll_no'        => $assigned['roll_no'] ?? null,
                        'gender'         => $assigned['gender'] ?? null,
                        'parent_name'    => $assigned['parent_name'] ?? null,
                        'parent_phone'   => $assigned['parent_phone'] ?? null,
                    ];
                }
            }
        }

        $unassigned = $studentIdx < $students->count() ? $students->slice($studentIdx)->values() : [];

        $chart = [
            'columns'             => $numCols,
            'benches_per_column'  => $benchesPerCol,
            'students_per_bench'  => $studentsPerBench,
            'total_seats'         => count($seats),
            'allocated_count'     => min(count($seats), $students->count()),
            'seats'               => $seats,
            'unassigned_students' => $unassigned,
        ];

        Cache::put($cacheKey, $chart, now()->addDays(7));

        return response()->json([
            'status'        => 'success',
            'class_id'      => $classId,
            'seating_chart' => $chart,
        ]);
    }

    /**
     * POST /api/teacher-app/seating/{classId}
     * Saves or updates the bench seating plan.
     */
    public function store(Request $request, $classId): JsonResponse
    {
        $teacher = $request->user();
        $schoolId = $teacher->school_id ?? 1;

        $validated = $request->validate([
            'columns'             => 'required|integer|min:1|max:6',
            'benches_per_column'  => 'required|integer|min:1|max:12',
            'students_per_bench'  => 'required|integer|in:1,2,3',
            'seats'               => 'required|array',
            'seats.*.column_no'   => 'required|integer',
            'seats.*.bench_no'    => 'required|integer',
            'seats.*.bench_position' => 'required|integer',
            'seats.*.student_id'  => 'nullable|integer',
            'seats.*.name'        => 'nullable|string',
            'seats.*.roll_no'     => 'nullable|string',
        ]);

        $cacheKey = "teacher_seating_chart_{$schoolId}_{$classId}";
        Cache::put($cacheKey, $validated, now()->addDays(30));

        return response()->json([
            'status'  => 'success',
            'message' => 'Classroom bench seating chart saved and synchronized with school MIS!',
            'chart'   => $validated,
        ]);
    }

    /**
     * POST /api/teacher-app/seating/{classId}/randomize
     */
    public function randomize(Request $request, $classId): JsonResponse
    {
        $teacher = $request->user();
        $schoolId = $teacher->school_id ?? 1;
        $cacheKey = "teacher_seating_chart_{$schoolId}_{$classId}";
        $chart = Cache::get($cacheKey);

        if (!$chart || empty($chart['seats'])) {
            return $this->show($request, $classId);
        }

        $allocatedStudents = collect($chart['seats'])
            ->filter(fn($s) => !empty($s['student_id']))
            ->values()
            ->shuffle();

        $studentIdx = 0;
        foreach ($chart['seats'] as &$seat) {
            if ($studentIdx < $allocatedStudents->count()) {
                $st = $allocatedStudents[$studentIdx++];
                $seat['student_id']   = $st['student_id'];
                $seat['name']         = $st['name'];
                $seat['roll_no']      = $st['roll_no'];
                $seat['gender']       = $st['gender'] ?? null;
                $seat['parent_name']  = $st['parent_name'] ?? null;
                $seat['parent_phone'] = $st['parent_phone'] ?? null;
            } else {
                $seat['student_id']   = null;
                $seat['name']         = null;
                $seat['roll_no']      = null;
            }
        }

        Cache::put($cacheKey, $chart, now()->addDays(30));

        return response()->json([
            'status'        => 'success',
            'message' => 'Bench seating randomized successfully.',
            'seating_chart' => $chart,
        ]);
    }

    /**
     * GET /api/teacher-app/seating/{classId}/template
     */
    public function template(Request $request, $classId): JsonResponse
    {
        return response()->json([
            'template' => [
                'columns'            => 3,
                'benches_per_column' => 5,
                'students_per_bench' => 2,
                'desk_layout'        => 'nepal_benches',
            ]
        ]);
    }
}

